<?php
namespace PQD;

class PQDPDO extends \PDO{

	public function __construct($dsn, $username, $passwd, $options){
		parent::__construct($dsn, $username, $passwd, $options);
		$this->setAttribute(PQDPDO::ATTR_STATEMENT_CLASS, array('PQD\PQDStatement'));
	}

	/**
	 * @return PQDStatement|false
	 */
	public function query(string $statement, ?int $fetchMode = null, ...$fetchModeArgs):\PDOStatement|false{

		try{
			$result = parent::query($statement, $fetchMode, $fetchModeArgs);

			if($result === false)
				PQDApp::getApp()->getExceptions()->setException( new PQDExceptionsDB($this->errorInfo(), "Erro na busca: " . $statement));
			else if(defined("APP_DEBUG_SQL") && APP_DEBUG_SQL === true)
				PQDApp::getApp()->getExceptions()->setException( new PQDExceptionsDB($this->errorInfo(), "Debug SQL: " . $statement));

			return $result;
		}
		catch(\Exception $e){
			PQDApp::getApp()->getExceptions()->setException( new PQDExceptionsDev($e->getMessage(), $e->getCode(), $e) );
		}

		return false;
	}

	/**
	 * @return int|false
	 */
	public function exec(string $statement): int|false{
		try{

			$result = parent::exec($statement);

			if($result === false)
				PQDApp::getApp()->getExceptions()->setException( new PQDExceptionsDB($this->errorInfo(), "Erro ao executar SQL: " . $statement));
			else if(defined("APP_DEBUG_SQL") && APP_DEBUG_SQL === true)
				PQDApp::getApp()->getExceptions()->setException( new PQDExceptionsDB($this->errorInfo(), "Debug SQL: " . $statement));

			return $result;
		}
		catch(\Exception $e){
			PQDApp::getApp()->getExceptions()->setException( new PQDExceptionsDev($e->getMessage(), $e->getCode(), $e) );
		}

		return false;
	}
}