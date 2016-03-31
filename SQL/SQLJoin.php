<?php
namespace PQD\SQL;

/**
 * 
 * Classe para fazer joins em buscas genericas
 * 
 * @author Willker Moraes Sivla
 * @since 2015-12-10
 *
 */
class SQLJoin extends SQLWhere {
	
	private $joins = array();
	
	/**
	 * 
	 * @param string $aliasTable
	 */
	public function __construct($aliasTable){
		$this->setAlias($aliasTable);
	}
	
	/**
	 * 
	 * @param string $table
	 * @param string $on
	 * @return SQLJoin
	 */
	public function setLeftJoin($table, $on){
		array_push($this->joins, "LEFT JOIN " . $table . " ON " . $on);
		return $this;
	}
	
	/**
	 * 
	 * @param string $table
	 * @param string $on
	 * @return SQLJoin
	 */
	public function setRightJoin($table, $on){
		array_push($this->joins, "RIGHT JOIN " . $table . " ON " . $on);
		return $this;
	}
	
	/**
	 * 
	 * @param string $table
	 * @param string $on
	 * @return SQLJoin
	 */
	public function setInnerJoin($table, $on){
		array_push($this->joins, "INNER JOIN " . $table . " ON " . $on);
		return $this;
	}

	/**
	 * 
	 * @return mixed array|string
	 */
	public function getJoins($string = true){
		if($string)
			return $this->getAlias() . " " . join(" " . PHP_EOL, $this->joins);
		else 
			return $this->joins;
	}

	/**
	 * 
	 * @return int
	 */
	public function countJoins(){
		return count($this->joins);
	}
	
	public function clearJoins(){
		$this->joins = array();
		return $this;
	}
	
	public function setJoins(array $joins){
		$this->joins = $joins;
	}
	
	public function addJoins(array $joins){
		$this->joins = array_merge($this->joins, $joins);
		return $this;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getWhere($where = true){
		return ($where ? $this->getJoins() . " " : '') . parent::getWhere($where);
	}
}