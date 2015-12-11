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
	
	private $aliasTabela;
	
	private $joins = array();
	
	/**
	 * 
	 * @param string $aliasTabela
	 */
	public function __construct($aliasTabela){
		$this->aliasTabela = $aliasTabela;
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
	 * @return string
	 */
	public function getJoins(){
		return $this->aliasTabela . PHP_EOL . join(" " . PHP_EOL, $this->joins);
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
	
	/**
	 * 
	 * @param string $aliasTabela
	 * @return SQLJoin
	 */
	public function setAlias($aliasTabela){
		$this->aliasTabela = $aliasTabela;
		return $this;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getAlias(){
		return $this->aliasTabela;
	}
	
	public function getWhere($where = false){
		return $this->getJoins() . " " . PHP_EOL . parent::getWhere($where);
	}
}