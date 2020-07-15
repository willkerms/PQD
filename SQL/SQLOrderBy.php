<?php
namespace PQD\SQL;

/**
 * Classe para gerenciar order by para sql's dinâmicos
 *
 *
 * @author Willker Moraes Silva
 * @since 2016-02-22
 */
class SQLOrderBy{

	private $aFields = array();

	private $asc = true;

	private $alias = null;

	function __construct(array $aFields = array(), $asc = true, $alias = null){
		$this->aFields = $aFields;
		$this->asc = $asc;
		$this->alias = $alias;
	}

	/**
	 * @return array $aFields
	 */
	public function getFields() {
		return $this->aFields;
	}

	/**
	 * @return bool $asc
	 */
	public function getAsc() {
		return $this->asc;
	}

	/**
	 * @param array $aFields
	 * @return SQLOrderBy
	 */
	public function setFields(array $aFields) {
		$this->aFields = $aFields;
		return $this;
	}

	/**
	 * @param boolean $asc
	 * @return SQLOrderBy
	 */
	public function setAsc($asc) {
		$this->asc = $asc;
		return $this;
	}

	/**
	 *
	 * @param string $field
	 * @return SQLOrderBy
	 */
	public function addField($field){
		$this->aFields[] = $field;
		return $this;
	}

	/**
	 *
	 * @param string $sql
	 * @return SQLOrderBy
	 */
	public function addSQL($sql){
		$this->aFields[] = array('sql' => $sql);
		return $this;
	}

	/**
	 * @return SQLOrderBy
	 */
	public function cleanField(){
		$this->aFields = array();
		return $this;
	}

	/**
	 * @return string $alias
	 */
	public function getAlias() {
		return $this->alias;
	}

	/**
	 * @param string $alias
	 * @return SQLOrderBy
	 */
	public function setAlias($alias) {
		$this->alias = $alias;
		return $this;
	}

	/**
	 * @return number
	 */
	public function count() {
		return count($this->aFields);
	}

	public function getOrderBy(){
		$return = "";

		if (count($this->aFields) > 0){

			$return .= " ORDER BY";
			foreach ($this->aFields as $key => $field){

				$return .= $key > 0 ? ',' : '';

				if(is_array($field))
					$return .= ' ' . $field['sql'];
				else{
					if(!is_null($this->alias)){
						if(preg_match('/^[a-zA-Z0-9]+\.[a-zA-Z_\-0-9]+/', $field) !== 1)
							$field = $this->alias . '.' . $field;
					}

					$return .= ' ' . $field;
				}
			}

			$return .= $this->asc === true ? ' ASC': ' DESC';
		}

		return $return;
	}
}