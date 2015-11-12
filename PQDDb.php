<?php
namespace PQD;

/**
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
	protected $exceptions;

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
	public function getExceptions(){
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
				$options = array();
				$port = "";
				if (!is_null(self::$dbs[$indexCon]['port']))
					$port = ":" . self::$dbs[$indexCon]['port'];

				if(self::$dbs[$indexCon]['driver'] == "mssql" && version_compare(phpversion(), '5.3', '>=') && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
					self::$connections[$indexCon] = new PQDPDO('sqlsrv:Server=' . self::$dbs[$indexCon]['host'] . $port . ';Database=' . self::$dbs[$indexCon]['db'], self::$dbs[$indexCon]['user'], self::$dbs[$indexCon]['pwd'], array('ReturnDatesAsStrings' => true));
				else{
					
					if(self::$dbs[$indexCon]['driver'] == "mysql")
						$options[\PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES latin1;';
					
					self::$connections[$indexCon] = new PQDPDO(self::$dbs[$indexCon]['driver'] . ":" . "dbname=" . self::$dbs[$indexCon]['db'] . ";host=" . self::$dbs[$indexCon]['host']. self::$dbs[$indexCon]['port'], self::$dbs[$indexCon]['user'], self::$dbs[$indexCon]['pwd'], $options);
				}
			}
			catch (PQDExceptionsDev $e) {
				
				$this->exceptions->setException(new \Exception("Erro ao Conectar ao Banco de Dados(" . self::$dbs[$indexCon]['db'] . ")!", 1));
				
				$this->exceptions->setException($e);
				
				self::$exceptionsDbs[$indexCon] = $e;
				
				return null;
			}
		}

		return self::$connections[$indexCon];
	}
	
	protected function log($tabela, $colPk, $pk, $indexCon = 0){
		
		$st = $this->getConnection($indexCon)->prepare("call sp_log(:usuario, :tabela, :colPk, :pk, :ip);");
		
		$usuario = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : -2;
		
		$st->bindParam(":usuario", $usuario, \PDO::PARAM_INT);
		$st->bindParam(":tabela", $tabela, \PDO::PARAM_STR);
		$st->bindParam(":colPk", $colPk, \PDO::PARAM_STR);
		$st->bindParam(":pk", $pk, \PDO::PARAM_INT);
		$st->bindParam(":ip", $_SERVER['REMOTE_ADDR'], \PDO::PARAM_STR);
		
		$st->execute();
	}
	
	/**
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
}