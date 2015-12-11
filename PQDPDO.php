<?php
namespace PQD;

class PQDPDO extends \PDO{
	public function __construct($dsn, $username, $passwd, $options){
		try{
			parent::__construct($dsn, $username, $passwd, $options);
			$this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array('PQD\PQDStatement'));
		}
		catch(PQDExceptionsDev $e){
			PQDApp::getApp()->getExceptions()->setException( $e );
		}
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
			PQDApp::getApp()->getExceptions()->setException( new PQDExceptionsDB($this->errorInfo(), "Erro na busca: " . $statement));
		
		return $result;
	}
}