<?php
namespace PQD;

class PQDStatement extends \PDOStatement{
	
	public function execute($input_parameters = null){
		
		$return = parent::execute($input_parameters);
		
		if($return === false)
			PQDApp::getApp()->getExceptions()->setException(new PQDExceptionsDB($this->errorInfo(), 'Erro ao executar SQL:' . PHP_EOL . $this->queryString));
		else if(defined("APP_DEBUG_SQL") && APP_DEBUG_SQL === true)
			PQDApp::getApp()->getExceptions()->setException( new PQDExceptionsDB($this->errorInfo(), "Debug SQL: " . $this->queryString));

		return $return;
	}
}