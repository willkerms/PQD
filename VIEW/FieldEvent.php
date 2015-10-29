<?php

namespace PQD\VIEW;

class FieldEvent {
	
	private $idPqdViewFieldEvent;
	private $idPqdViewField;
	private $event;
	private $script;
	
	/**
	 * @return the $idPqdViewFieldEvent
	 */
	public function getIdPqdViewFieldEvent() {
		return $this->idPqdViewFieldEvent;
	}

	/**
	 * @return the $idPqdViewField
	 */
	public function getIdPqdViewField() {
		return $this->idPqdViewField;
	}

	/**
	 * @return the $event
	 */
	public function getEvent() {
		return $this->event;
	}

	/**
	 * @return the $script
	 */
	public function getScript() {
		return $this->script;
	}

	/**
	 * @param field_type $idPqdViewFieldEvent
	 */
	public function setIdPqdViewFieldEvent($idPqdViewFieldEvent) {
		$this->idPqdViewFieldEvent = $idPqdViewFieldEvent;
	}

	/**
	 * @param field_type $idPqdViewField
	 */
	public function setIdPqdViewField($idPqdViewField) {
		$this->idPqdViewField = $idPqdViewField;
	}

	/**
	 * @param field_type $event
	 */
	public function setEvent($event) {
		$this->event = $event;
	}

	/**
	 * @param field_type $script
	 */
	public function setScript($script) {
		$this->script = $script;
	}
	
	public function toArray(){
		return get_object_vars($this);
	}
}