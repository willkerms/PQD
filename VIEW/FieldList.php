<?php

namespace PQD\VIEW;

class FieldList {
	
	private $idViewFieldList;
	private $idPqdViewField;
	private $tpList;
	private $ordem;
	private $chave;
	private $valor;
	private $classe;
	private $metodo;
	private $checked;
	
	/**
	 * @return the $idViewFieldList
	 */
	public function getIdViewFieldList() {
		return $this->idViewFieldList;
	}

	/**
	 * @return the $idPqdViewField
	 */
	public function getIdPqdViewField() {
		return $this->idPqdViewField;
	}

	/**
	 * @return the $tpList
	 */
	public function getTpList() {
		return $this->tpList;
	}

	/**
	 * @return the $ordem
	 */
	public function getOrdem() {
		return $this->ordem;
	}

	/**
	 * @return the $chave
	 */
	public function getChave() {
		return $this->chave;
	}

	/**
	 * @return the $valor
	 */
	public function getValor() {
		return $this->valor;
	}

	/**
	 * @return the $classe
	 */
	public function getClasse() {
		return $this->classe;
	}

	/**
	 * @return the $metodo
	 */
	public function getMetodo() {
		return $this->metodo;
	}

	/**
	 * @param field_type $idViewFieldList
	 */
	public function setIdViewFieldList($idViewFieldList) {
		$this->idViewFieldList = $idViewFieldList;
	}

	/**
	 * @param field_type $idPqdViewField
	 */
	public function setIdPqdViewField($idPqdViewField) {
		$this->idPqdViewField = $idPqdViewField;
	}

	/**
	 * @param field_type $tpList
	 */
	public function setTpList($tpList) {
		$this->tpList = $tpList;
	}

	/**
	 * @param field_type $ordem
	 */
	public function setOrdem($ordem) {
		$this->ordem = $ordem;
	}

	/**
	 * @param field_type $chave
	 */
	public function setChave($chave) {
		$this->chave = $chave;
	}

	/**
	 * @param field_type $valor
	 */
	public function setValor($valor) {
		$this->valor = $valor;
	}

	/**
	 * @param field_type $classe
	 */
	public function setClasse($classe) {
		$this->classe = $classe;
	}

	/**
	 * @param field_type $metodo
	 */
	public function setMetodo($metodo) {
		$this->metodo = $metodo;
	}
	
	/**
	 * @return bool $checked
	 */
	public function getChecked() {
		return $this->checked;
	}

	/**
	 * @param bool $checked
	 */
	public function setChecked($checked) {
		$this->checked = $checked;
	}

	public function toArray(){
		return get_object_vars($this);
	}
}