<?php
namespace PQD;

use PQD\SQL\SQLWhere;

/**
 * @author Willker Moraes Silva
 * @since 2015-11-12
 */
class PQDDAO extends PQDDb{
	
	/**
	 * @var bool
	 */
	private $isAutoIncrement = true;
	
	/**
	 * @var string
	 */
	private $sql;
	
	/**
	 * @var string
	 */
	private $table;
	
	/**
	 * @var string
	 */
	private $view;
	
	/**
	 * @var string
	 */
	private $colPK;
	
	/**
	 * @var string
	 */
	private $methodPk;
	
	/**
	 * @var string
	 */
	private $clsEntity;
	
	/**
	 * @var string
	 */
	private $clsView;
	
	/**
	 * @var array
	 */
	private $fields = array();
	
	/**
	 * @var array
	 */
	private $fieldsIgnoreOnUpdate = array();
	
	/**
	 * @var array
	 */
	private $fieldsIgnoreOnInsert = array();
	
	/**
	 * @var array
	 */
	private $fieldsDefaultValuesOnUpdate = array();
	
	/**
	 * @var array
	 */
	private $fieldsDefaultValuesOnInsert = array();
	
	public function __construct($table, array $fields, $colPk, $clsEntity, PQDExceptions $exceptions, $view = null, $clsView = null){
		
		$this->table = $table;
		$this->fields = $fields;
		$this->colPK = $colPk;
		$this->clsEntity = $clsEntity;
		$this->methodPk = 'get' . ucwords($this->colPK);
		
		$this->view = $view;
		$this->clsView = $clsView;
		
		parent::__construct($exceptions);
	}
	
	public function retSQLInsert(){
		
		$sql = "INSERT INTO " . $this->table . "(" . PHP_EOL;
		$comma = "";
		
		foreach ($this->fields as $col => $type){
			if(!isset($this->fieldsIgnoreOnInsert[$col]) && ($col != $this->colPK | ($col == $this->colPK && !$this->isAutoIncrement))){
				$sql .= $comma . "\t" . $col;
				$comma = "," . PHP_EOL;
			}
		}
		
		$sql .= ") VALUES (";
		
		foreach ($this->fields as $col => $type){
			
			if(!isset($this->fieldsIgnoreOnInsert[$col]) && ($col != $this->colPK | ($col == $this->colPK && !$this->isAutoIncrement))){
				
				if(isset($this->fieldsDefaultValuesOnInsert[$col]))
					$sql .= $comma . "\t" . $this->fieldsDefaultValuesOnInsert[$col];
				else
					$sql .= $comma . "\t:" . $col;
				
				$comma = "," . PHP_EOL;
			}
		}
		$sql .= ");";
	}
	
	public function retSQLUpdate(){
		
		$sql = "UPDATE " . $this->table . " SET" . PHP_EOL;
		$comma = "";
		
		foreach ($this->fields as $col => $type){
			
			if($col != $this->colPK && !isset($this->fieldsIgnoreOnUpdate[$col])){
				
				if(isset($this->fieldsDefaultValuesOnUpdate))
					$sql .= $comma . "\t" . $col . " = " . $this->fieldsDefaultValuesOnUpdate;
				else
					$sql .= $comma . "\t" . $col . " = :" . $col;
				
				$comma = "," . PHP_EOL;
			}
		}
			
		$sql .= "WHERE " . $this->colPK . " = :" . $this->colPK . ";";
	}
	
	private function setValues($setPk = true, PQDStatement $st, PQDEntity $oEntity){
		foreach ($this->fields as $col => $value){
			
			if(is_array($value))
				$type = $value['type'];
			else
				$type = $value;
			
			$method = 'get' . ucwords($col);
			
			$st->setAttribute(":" . $col, $oEntity->{$method}(), PQDPDO::PARAM_STR);
		}
	}
	
	public function save(PQDEntity $oEntity){
		
		if (!is_null($oEntity->{$this->methodPk}())) {
			//UPDATE
			$this->sql = $this->retSQLUpdate();
		}
		else{
			//INSERT 
			$this->sql = $this->retSQLInsert();
		}
		$sth = $this->getConnection()->prepare($this->sql);
	}
	
	public function delete(PQDEntity $oEntity){
		
	}
	
	public function retEntity($id){
		
	}
	
	public function fetchAll(){
		
	}
	
	public function genericSearch(SQLWhere $where, array $fields = null, $fetchClass = true, array $orderBy = null, $asc = true, $limit = null, $page = 0){
		
	}
}