<?php
namespace PQD;

class PQDStatement extends \PDOStatement{
	
	/**
	 * @return bool
	 */
	public function execute(?array $params = null): bool{

		try{

			$return = parent::execute($params);
			
			if($return === false)
				PQDApp::getApp()->getExceptions()->setException(new PQDExceptionsDB($this->errorInfo(), 'Erro ao executar SQL:' . PHP_EOL . $this->queryString));
			else if(defined("APP_DEBUG_SQL") && APP_DEBUG_SQL === true)
				PQDApp::getApp()->getExceptions()->setException( new PQDExceptionsDB($this->errorInfo(), "Debug SQL: " . $this->queryString));

			return $return;
		}
		catch(\Exception $e){
			PQDApp::getApp()->getExceptions()->setException( new PQDExceptionsDev($e->getMessage(), $e->getCode(), $e) );
		}

		return false;
	}
}