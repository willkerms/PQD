<?php
namespace PQD;

class PQDPDO extends \PDO{
	public function __construct($dsn, $username, $passwd, $options){
		parent::__construct($dsn, $username, $passwd, $options);
		$this->setAttribute(PQDPDO::ATTR_STATEMENT_CLASS, array('PQD\PQDStatement'));
	}
	
	public function query($statement){
		
		$result = parent::query($statement);
		
		if($result === false)
			PQDApp::getApp()->getExceptions()->setException( new PQDExceptionsDB($this->errorInfo(), "Erro na busca: " . $statement));
		
		return $result;
	}
	
	public function exec($statement){
		
		$result = parent::exec($statement);
		
		if($result === false)
			PQDApp::getApp()->getExceptions()->setException( new PQDExceptionsDB($this->errorInfo(), "Erro ao executar SQL: " . $statement));
		
		return $result;
	}
}