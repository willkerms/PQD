<?php
namespace PQD;

use PQD\REPORT\ReportAttr;

use PQD\REPORT\Report;

class PQDDynamicReport {
	
	public static function createReport(ReportAttr $report, \PDO $connection = null){
		
		$connection = !is_null($connection) ? $connection : (new PQDDb(new PQDExceptions()))->getConnection();
		
		$st = $connection->prepare("
			insert into pqd_report values(
				default,
				:sigla,
				:descricao,
				:SQL,
				:tpRelatorio,
				:idPqdServerDB
			);
		");
		$st->bindValue(":sigla", $report->getSigla(), \PDO::PARAM_STR);
		$st->bindValue(":descricao", $report->getDescricao(), \PDO::PARAM_STR);
		$st->bindValue(":SQL", $report->getSQL(), \PDO::PARAM_STR);
		$st->bindValue(":tpRelatorio", $report->getTpRelatorio(), \PDO::PARAM_INT);
		$st->bindValue(":idPqdServerDB", $report->getIdPqdServerDB(), \PDO::PARAM_INT);
		
		if(!$st->execute()){
			$error = $st->errorInfo();
			throw new \Exception($error[2], $error[1]);
		}
		
		$report->setIdPqdReport($connection->lastInsertId());
		return new Report($report->getIdPqdReport(), $connection);
	}
	
	public static function retJson($idOrSigla, \PDO $connection = null){
		$connection = !is_null($connection) ? $connection : (new PQDDb(new PQDExceptions()))->getConnection();
		
		return (new Report($idOrSigla, $connection))->json();
	}
	
	public static function report($idOrSigla, \PDO $connection = null){
		$connection = !is_null($connection) ? $connection : (new PQDDb(new PQDExceptions()))->getConnection();
		echo new Report($idOrSigla, $connection);
	}
}