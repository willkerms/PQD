<?php

namespace PQD\REPORT;

class ReportAttr {

	/**
	 * @var int
	 */
	protected $idPqdReport;
	/**
	 * @var string
	 */
	protected $sigla;
	/**
	 * @var string
	 */
	protected $descricao;
	/**
	 * @var string
	 */
	protected $SQL;
	/**
	 * @var int
	 */
	protected $tpRelatorio;
	
	/**
	 * @var int
	 */
	protected $idPqdServerDB;
	
	/**
	 * @return the $idPqdReport
	 */
	public function getIdPqdReport() {
		return $this->idPqdReport;
	}
	
	/**
	 * @return the $sigla
	 */
	public function getSigla() {
		return $this->sigla;
	}
	
	/**
	 * @return the $descricao
	 */
	public function getDescricao() {
		return $this->descricao;
	}
	
	/**
	 * @return the $SQL
	 */
	public function getSQL() {
		return $this->SQL;
	}
	
	/**
	 * @return the $tpRelatorio
	 */
	public function getTpRelatorio() {
		return $this->tpRelatorio;
	}
	
	/**
	 * @param number $idPqdReport
	 */
	public function setIdPqdReport($idPqdReport) {
		$this->idPqdReport = $idPqdReport;
	}
	
	/**
	 * @param string $sigla
	 */
	public function setSigla($sigla) {
		$this->sigla = $sigla;
	}
	
	/**
	 * @param string $descricao
	 */
	public function setDescricao($descricao) {
		$this->descricao = $descricao;
	}
	
	/**
	 * @param string $SQL
	 */
	public function setSQL($SQL) {
		$this->SQL = $SQL;
	}
	
	/**
	 * @param number $tpRelatorio
	 */
	public function setTpRelatorio($tpRelatorio) {
		$this->tpRelatorio = $tpRelatorio;
	}
	
	/**
	 * @return int
	 */
	public function getIdPqdServerDB() {
		return $this->idPqdServerDB;
	}

	/**
	 * @param number $idPqdServerDB
	 */
	public function setIdPqdServerDB($idPqdServerDB) {
		$this->idPqdServerDB = $idPqdServerDB;
	}

	/**
	 * @return array
	 */
	public function toArray(){
		$arr = get_class_vars(__CLASS__);
		
		foreach ($arr as $key => $value)
			$arr[$key] = $this->{$key};
		
		return $arr;
	}
}