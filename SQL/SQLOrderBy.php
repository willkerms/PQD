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
			
			if(!is_null($this->alias)){
				foreach ($this->aFields as &$field){
					if(preg_match('/^[a-zA-Z]+\.[a-zA-Z_\-]+/', $field) !== 1)
						$field = $this->alias . '.' . $field;
				}
			}
			
			$return .= " ORDER BY " . join(", ", $this->aFields) . ($this->asc === true ? ' ASC': ' DESC');
		}
		
		return $return;
	}
}