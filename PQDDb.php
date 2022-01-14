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

	public function getDriverDB($indexCon = 0, $original = false){

		if($original)
			return $this->getConnection($indexCon)->getAttribute(PQDPDO::ATTR_DRIVER_NAME);

		switch ($this->getConnection($indexCon)->getAttribute(PQDPDO::ATTR_DRIVER_NAME)) {
			case "dblib":
			case "sqlsrv":
				return "mssql";
			break;
			default:
				return $this->getConnection($indexCon)->getAttribute(PQDPDO::ATTR_DRIVER_NAME);
			break;
		}
	}

	/**
	 * @param number $index
	 * @return PQDPDO
	 */
	public function getConnection($indexCon = 0){

		if (count(self::$dbs) == 0){
			$this->exceptions->setException(new \Exception("Configurações de conexão com o Banco de Dados não indefinidas!", 2));
			return null;
		}

		//Somente para não tentar conectar em bancos que já não conectaram dá primeira tentativa
		if(isset(self::$exceptionsDbs[$indexCon]))
			return null;

		if(!isset(self::$connections[$indexCon])){
			try {

				if (!isset(self::$dbs[$indexCon]))
					throw new \Exception("Parâmetros não setados para a conexão!", 12);

				$options = self::$dbs[$indexCon]['options'];
				$port = !is_null(self::$dbs[$indexCon]['port']) ? ";port=" . self::$dbs[$indexCon]['port'] : "";

				//Caso o cliente queira, setar na váriavel $options
				//if(self::$dbs[$indexCon]['driver'] != "dblib")
				//	$options[PQDPDO::ATTR_TIMEOUT] = 5;	

				if( !is_null(self::$dbs[$indexCon]['dsn']) )
					self::$connections[$indexCon] = new PQDPDO(self::$dbs[$indexCon]['dsn'], self::$dbs[$indexCon]['user'], self::$dbs[$indexCon]['pwd'], $options);
				else if(self::$dbs[$indexCon]['driver'] == "mssql" && extension_loaded('pdo_sqlsrv')){

					$port = !is_null(self::$dbs[$indexCon]['port']) ? ", " . self::$dbs[$indexCon]['port'] : $port;
	
					$options['ReturnDatesAsStrings'] = PQDUtil::retDefault($options, 'ReturnDatesAsStrings', true);

					self::$connections[$indexCon] = new PQDPDO('sqlsrv:Server=' . self::$dbs[$indexCon]['host'] . $port . ';Database=' . self::$dbs[$indexCon]['db'], self::$dbs[$indexCon]['user'], self::$dbs[$indexCon]['pwd'], $options);
				}
				else if(self::$dbs[$indexCon]['driver'] == "sqlite")
					self::$connections[$indexCon] = new PQDPDO(self::$dbs[$indexCon]['driver'] . ":" . self::$dbs[$indexCon]['db'], null, null, $options);
				else if(self::$dbs[$indexCon]['driver'] == "oci"){
					//echo self::$dbs[$indexCon]['port'];
					$port = !is_null(self::$dbs[$indexCon]['port']) ? self::$dbs[$indexCon]['port'] : 1521;
					$tns = "
					(DESCRIPTION =
							(ADDRESS_LIST =
								(ADDRESS = (PROTOCOL = TCP)(HOST = " . self::$dbs[$indexCon]['host'] . ")(PORT = " . $port . "))
							)
							(CONNECT_DATA =
								(SERVICE_NAME = " . self::$dbs[$indexCon]['db'] . ")
							)
						)
					";

					self::$connections[$indexCon] = new PQDPDO(self::$dbs[$indexCon]['driver'] . ":dbname=" . $tns, self::$dbs[$indexCon]['user'], self::$dbs[$indexCon]['pwd'], $options);
				}
				else{
					//Caso o cliente queira, setar na váriavel $options
					//if(self::$dbs[$indexCon]['driver'] == "mysql")
					//	$options[\PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES latin1;';

					self::$connections[$indexCon] = new PQDPDO(self::$dbs[$indexCon]['driver'] . ":" . "dbname=" . self::$dbs[$indexCon]['db'] . ";host=" . self::$dbs[$indexCon]['host'] . $port, self::$dbs[$indexCon]['user'], self::$dbs[$indexCon]['pwd'], $options);
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
	public static function setDbConnection($driver, $host, $dbName, $user, $password, $port = null, array $options = array(), $dsn = null){

		self::$dbs[] = array(
			'driver' => $driver,
			'host' => $host,
			'db' => $dbName,
			'user' => $user,
			'pwd' => $password,
			'port' => $port,
			'options' => $options,
			'dsn' => $dsn
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