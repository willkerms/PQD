<?php
namespace PQD\SQL;

/**
 * Classe para gerenciar group by para sql's dinâmicos
 *
 * 
 * @author Willker Moraes Silva
 * @since 2016-02-22
 */
class SQLGroupBy{
	
	
	private $aFields = array();
	
	/**
	 * @var string
	 */
	private $alias = null;
	
	function __construct(array $aFields = array(), $alias = null){
		$this->aFields = $aFields;
		$this->alias = $alias;
	}
	
	/**
	 * @return array $aFields
	 */
	public function getFields() {
		return $this->aFields;
	}
	/**
	 * @param array $aFields
	 * @return SQLGroupBy
	 */
	public function setFields(array $aFields) {
		$this->aFields = $aFields;
		return $this;
	}
	/**
	 * 
	 * @param string $field
	 * @return SQLGroupBy
	 */
	public function addField($field){
		$this->aFields[] = $field;
		return $this;
	}
	
	/**
	 * @return SQLGroupBy
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
	
	/**
	 * @return string
	 */
	public function getGroupBy(){
		$return = "";
		
		if (count($this->aFields) > 0){
			if(!is_null($this->alias)){
				foreach ($this->aFields as &$field){
					if(preg_match('/^[a-zA-Z]+\.[a-zA-Z_\-]+/', $field) !== 1)
						$field = $this->alias . '.' . $field;
				}
			}
			
			$return .= " GROUP BY " . join(", ", $this->aFields);
		}
		
		return $return;
	}
}