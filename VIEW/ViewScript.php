<?php

namespace PQD\VIEW;

class ViewScript {
	
	private $idPqdViewScript;
	private $idPqdView;
	private $tpScript;
	private $iniFim;
	private $pqd_viewScriptscol;
	private $include;
	private $script;
	
	/**
	 * @return the $idPqdViewScript
	 */
	public function getIdPqdViewScript() {
		return $this->idPqdViewScript;
	}

	/**
	 * @return the $idPqdView
	 */
	public function getIdPqdView() {
		return $this->idPqdView;
	}

	/**
	 * @return the $tpScript
	 */
	public function getTpScript() {
		return $this->tpScript;
	}

	/**
	 * @return the $iniFim
	 */
	public function getIniFim() {
		return $this->iniFim;
	}

	/**
	 * @return the $pqd_viewScriptscol
	 */
	public function getPqd_viewScriptscol() {
		return $this->pqd_viewScriptscol;
	}

	/**
	 * @return the $include
	 */
	public function getInclude() {
		return $this->include;
	}

	/**
	 * @return the $script
	 */
	public function getScript() {
		return $this->script;
	}

	/**
	 * @param field_type $idPqdViewScript
	 */
	public function setIdPqdViewScript($idPqdViewScript) {
		$this->idPqdViewScript = $idPqdViewScript;
	}

	/**
	 * @param field_type $idPqdView
	 */
	public function setIdPqdView($idPqdView) {
		$this->idPqdView = $idPqdView;
	}

	/**
	 * @param field_type $tpScript
	 */
	public function setTpScript($tpScript) {
		$this->tpScript = $tpScript;
	}

	/**
	 * @param field_type $iniFim
	 */
	public function setIniFim($iniFim) {
		$this->iniFim = $iniFim;
	}

	/**
	 * @param field_type $pqd_viewScriptscol
	 */
	public function setPqd_viewScriptscol($pqd_viewScriptscol) {
		$this->pqd_viewScriptscol = $pqd_viewScriptscol;
	}

	/**
	 * @param field_type $include
	 */
	public function setInclude($include) {
		$this->include = $include;
	}

	/**
	 * @param field_type $script
	 */
	public function setScript($script) {
		$this->script = $script;
	}

	
	
}