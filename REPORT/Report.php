<?php

namespace PQD\REPORT;

use PQD\PQDExceptions;
use PQD\PQDDb;


/**
 * 
 * @author Willker
 * @since 2014-08-04
 */
class Report extends ReportAttr{
	
	/**
	 * @var array
	 */
	private $data = array();
	
	/**
	 * 
	 * @var ServerDB
	 */
	private $server;
	
	public function __construct($idOrSigla, \PDO $connection = null){
		$connection = !is_null($connection) ? $connection : (new PQDDb(new PQDExceptions()))->getConnection();
		
		if (is_numeric($idOrSigla)) {
			$sql = "SELECT * FROM pqd_report WHERE idPqdReport = :idPqdReport";
			$sth = $connection->prepare($sql);
			$sth->bindParam(":idPqdReport", $idOrSigla, \PDO::PARAM_INT);
		}
		else{
			$sql = "SELECT * FROM pqd_report WHERE sigla = :sigla";
			$sth = $connection->prepare($sql);
			$sth->bindParam(":sigla", $idOrSigla, \PDO::PARAM_STR);
		}
		
		if ($sth->execute()) {
			if($result = $sth->fetch(\PDO::FETCH_NAMED)){
				
				$this->setIdPqdReport($result['idPqdReport']);
				$this->setSigla($result['sigla']);
				$this->setTpRelatorio($result['tpRelatorio']);
				$this->setDescricao($result['descricao']);
				$this->setSQL($result['SQL']);
				$this->loadData($connection);
				
				if($this->getIdPqdServerDB() > 0 ){
					
					$st = $connection->prepare("SELECT * FROM pqd_serversDB WHERE idPqdServerDB = :idPqdServerDB");
					$st->bindValue($this->getIdPqdServerDB(), \PDO::PARAM_INT);
					$st->setFetchMode( \PDO::FETCH_CLASS, '\PQD\REPORT\ServerDB');
					$st->execute();
					
					$this->server = $st->fetch(\PDO::FETCH_CLASS);
				}
				
				/*
				$this->setScripts($connection);
				$this->setFields($connection);
				$this->setTemplates($connection);
				*/
			}
		}
		else{
			$error = $sth->errorInfo();
			throw new \Exception($error[2], $error[1]);
		}
	}
	
	private function loadData(\PDO $connection = null){
		
		if($this->getTpRelatorio() == 0 && $this->getSQL() != null){
			$connection = !is_null($connection) ? $connection : (new PQDDb(new PQDExceptions()))->getConnection();
			$st = $connection->query($this->getSQL());
			$this->data = $st->fetchAll(\PDO::FETCH_NAMED);
		}
	}
	
	public function json(){
		return json_encode(parent::toArray());
	}
	
	public function getData(){
		throw new \Exception("TODO: getData()");
	}
	
	public function getReport(){
		throw new \Exception("TODO: getReport()");
	}
	
	public function __toString(){
		echo "<pre>";
		return print_r($this->data, true);
	}
}