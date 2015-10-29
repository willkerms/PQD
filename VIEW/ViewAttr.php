<?php

namespace PQD\VIEW;

class ViewAttr {
	/**
	 *
	 * @var int
	 */
	protected $idPqdView;
	
	/**
	 *
	 * @var string
	 */
	protected $sigla;
	
	/**
	 *
	 * @var string
	 */
	protected $namespace;
	
	/**
	 *
	 * @var string
	 */
	protected $descricao;
	/**
	 *
	 * @var string
	 */
	protected $tabela;
	
	/**
	 * @return the $idPqdView
	 */
	public function getIdPqdView() {
		return $this->idPqdView;
	}
	
	/**
	 * @return the $sigla
	 */
	public function getSigla() {
		return $this->sigla;
	}
	
	/**
	 * @return the $namespace
	 */
	public function getNamespace() {
		return $this->namespace;
	}
	
	/**
	 * @return string
	 */
	public function getDescricao() {
		return $this->descricao;
	}
	
	/**
	 * @param number $idPqdView
	 */
	public function setIdPqdView($idPqdView) {
		$this->idPqdView = $idPqdView;
	}
	
	/**
	 * @param string $sigla
	 */
	public function setSigla($sigla) {
		$this->sigla = $sigla;
	}
	
	/**
	 * @param string $namespace
	 */
	public function setNamespace($namespace) {
		$this->namespace = $namespace;
	}
	
	/**
	 * @param string $descricao
	 */
	public function setDescricao($descricao) {
		$this->descricao = $descricao;
	}
	/**
	 * @return string $tabela
	 */
	public function getTabela() {
		return $this->tabela;
	}

	/**
	 * @param string $tabela
	 */
	public function setTabela($tabela) {
		$this->tabela = $tabela;
	}
	
	public function toArray(){
		$arr = get_class_vars(__CLASS__);
	
		foreach ($arr as $key => $value)
			$arr[$key] = $this->{$key};
	
		return $arr;
	}
}