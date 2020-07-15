<?php
namespace PQD\LDAP;

class LDAPCon {
	
	private $userAccountControl = array(
		'512' => 'Enabled Account',
		'514' => 'Disabled Account',
		'544' => 'Enabled, Password Not Required',
		'546' => 'Disabled, Password Not Required',
		'66048' => "Enabled, Password Doesn't Expire",
		'66050' => "Disabled, Password Doesn't Expire",
		'66080' => "Enabled, Password Doesn't Expire & Not Required",
		'66082' => "Disabled, Password Doesn't Expire & Not Required",
		'262656' => 'Enabled, Smartcard Required',
		'262658' => 'Disabled, Smartcard Required',
		'262688' => 'Enabled, Smartcard Required, Password Not Required',
		'262690' => 'Disabled, Smartcard Required, Password Not Required',
		'328192' => "Enabled, Smartcard Required, Password Doesn't Expire",
		'328194' => "Disabled, Smartcard Required, Password Doesn't Expire",
		'328224' => "Enabled, Smartcard Required, Password Doesn't Expire & Not Required",
		'328226' => "Disabled, Smartcard Required, Password Doesn't Expire & Not Required"
	);
	
	private $con;
	
	private $server;
	
	private $port;
	
	private $user;
	
	private $password;

	/**
	 * Conecta a um servidor de AD
	 * Servidor, ex: 10.0.0.1 ou 10.0.0.1:389
	 * Descrição da base, Ex: OU=Empresa,DC=dominio,DC=matriz
	 * Usuário e senha.
	 * 
	 * @param string $server
	 * @param string $base
	 * @param string $user
	 * @param string $password
	 * @throws \Exception
	 */
	public function __construct($server, $user, $password){
		$server = preg_split("/[:]/", $server);
		
		$this->port = isset($server[1]) ? $server[1] : 389;
		$this->server = $server[0];
		$this->user = $user;
		$this->password = $password;
		
		if(($this->con = ldap_connect($this->server, $this->port)) === false)
			throw new \Exception("Erro ao conectar ao servidor LDAP!");
		
		if(ldap_bind($this->con, $this->user, $this->password) === false)
			throw new \Exception("Erro ao fazer login no servidor LDAP! (" .  ldap_error($this->con) . ")");
	}
	
	/**
	 * Busca todas as unidades organizacionais
	 * Base Ex: "OU=nameOU,DC=domain,DC=matriz";
	 * 
	 * @param string $base
	 * return array();
	 */
	public function fetchAllOU($base){
		$return = ldap_search($this->con, $base, "( &(objectClass=organizationalUnit) )", array("*"));
		
		return ldap_get_entries($this->con, $return);
	}
	
	/**
	 * Busca todos os usuários de um banco
	 * Base Ex: "OU=nameOU,DC=domain,DC=matriz";
	 * 
	 * @param string $base
	 * return array();
	 */
	public function fetchAllUsers($base){
		$return = ldap_search($this->con, $base, "( &(objectClass=organizationalPerson) (!(objectClass=computer)) )", array("*"));
		
		return ldap_get_entries($this->con, $return);
	}
	
	public function getUserAccountControl(){
		return $this->userAccountControl;
	}
	
	public function __destruct(){
		ldap_close($this->con); 
	}
}