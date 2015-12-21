<?php
namespace PQD;

/**
 * Classe gerenciadora de conexões com o Banco de Dados
 * 
 * @author Willker Moraes Silva
 * @since 2012-03-30
 */
class PQDDb{
	
	/**
	 * @var array
	 */
	private static $dbs = array();

	/**
	 * 
	 * @var array[PQDPDO]
	 */
	private static $connections = array();
	
	/**
	 * 
	 * @var array[\Exception]
	 */
	private static $exceptionsDbs = array();
	
	/**
	 * 
	 * @var PQDExceptions
	 */
	private $exceptions;

	/**
	 * @var string
	 */
	protected $sql = "";
	
	
	function __construct(PQDExceptions $exceptions){
		
		$this->exceptions = $exceptions;
	}
	
	/**
	 * @return PQDExceptions
	 */
	protected function getExceptions(){
		return $this->exceptions;
	}

	/**
	 * @param number $index
	 * @return PQDPDO
	 */
	public function getConnection($indexCon = 0){
		
		if (count(self::$dbs) == 0){
			$this->exceptions->setException(new \Exception("Configurações de conexão com o Banco de Dados não indefinidas!", 2));
			return;
		}
		
		//Somente para não tentar conectar em bancos que já não conectaram dá primeira tentativa
		if(isset(self::$exceptionsDbs[$indexCon]))
			return null;
		
		if(!isset(self::$connections[$indexCon])){
			try {
				$options = array(
					PQDPDO::ATTR_TIMEOUT => 5
				);
				
				$port = "";
				if (!is_null(self::$dbs[$indexCon]['port']))
					$port = ":" . self::$dbs[$indexCon]['port'];

				if(self::$dbs[$indexCon]['driver'] == "mssql" && version_compare(phpversion(), '5.3', '>=') && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){
					$options['ReturnDatesAsStrings'] = true;
					self::$connections[$indexCon] = new PQDPDO('sqlsrv:Server=' . self::$dbs[$indexCon]['host'] . $port . ';Database=' . self::$dbs[$indexCon]['db'], self::$dbs[$indexCon]['user'], self::$dbs[$indexCon]['pwd'], $options);
					self::$connections[$indexCon]->setAttribute(PQDPDO::SQLSRV_ATTR_ENCODING, PQDPDO::SQLSRV_ENCODING_SYSTEM);
				}
				else{
					
					if(self::$dbs[$indexCon]['driver'] == "mysql")
						$options[\PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES latin1;';
					
					self::$connections[$indexCon] = new PQDPDO(self::$dbs[$indexCon]['driver'] . ":" . "dbname=" . self::$dbs[$indexCon]['db'] . ";host=" . self::$dbs[$indexCon]['host']. self::$dbs[$indexCon]['port'], self::$dbs[$indexCon]['user'], self::$dbs[$indexCon]['pwd'], $options);
				}
			}
			catch (\PDOException $e) {
				
				$this->exceptions->setException(new \Exception("Erro ao Conectar ao Banco de Dados(" . self::$dbs[$indexCon]['db'] . ")!", 1));
				
				$this->exceptions->setException($e);
				
				self::$exceptionsDbs[$indexCon] = $e;
				
				return null;
			}
		}

		return self::$connections[$indexCon];
	}
	
	/**
	 * Seta uma string de conexão com o banco, retorna o identificador de conexão com este banco
	 * 
	 * @param string $driver
	 * @param string $host
	 * @param string $dbName
	 * @param string $user
	 * @param string $password
	 * @param string $port
	 * @return number $index
	 */
	public static function setDbConnection($driver, $host, $dbName, $user, $password, $port = null){
		
		self::$dbs[] = array(
			'driver' => $driver,
			'host' => $host,
			'db' => $dbName,
			'user' => $user,
			'pwd' => $password,
			'port' => $port
		);
		
		return count(self::$dbs) -1;
	}
	
	/**
	 * Retorna o SQL 
	 * 
	 * @return string
	 */
	public function getSql(){
		return $this->sql;
	}
}