<?php
namespace PQD;

use PQD\SQL\SQLWhere;
use PQD\SQL\SQLSelect;

/**
 * Classe de abastração do banco de dados
 * 
 * @author Willker Moraes Silva
 * @since 2015-11-12
 */
abstract class PQDDAO extends SQLSelect{
	
	private $operation = null;
	
	private $cleanOperation = false;
	
	/**
	 * @var bool
	 */
	private $isAutoIncrement = true;
	
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
	
	/**
	 * @var SQLWhere
	 */
	private $defaultWhereOnDelete;
	
	/**
	 * @var bool
	 */
	private $skipNull = false;
	
	
	/**
	 * Prepara a inserção de um registro
	 * 
	 * @param PQDEntity $oEntity
	 * @param array $fields
	 * 
	 * @return \PDOStatement
	 */
	protected function prepareSQLInsert(PQDEntity $oEntity, array $fields = null){
		
		$this->sql = "INSERT INTO " . $this->getTable() . "(" . PHP_EOL;
		$comma = "";
		$fields = is_null($fields) ? $this->getFields() : $fields;
		$paramValues = array();
		
		foreach ($fields as $col => $value){
			
			//Para não inserir campos que estão nulos
			if($this->skipNull && !isset($this->fieldsDefaultValuesOnInsert[$col]) && is_null($oEntity->{'get' . ucwords($col)}()))
				continue;
			
			if(!isset($this->fieldsIgnoreOnInsert[$col]) && ($col != $this->getColPK() | ( $col == $this->getColPK() && !is_null($oEntity->{$this->getMethodGetPk()}() ) ) ) ){
				$this->sql .= $comma . "\t" . $col;
				$comma = "," . PHP_EOL;
			}
		}
		
		$this->sql .= PHP_EOL . ") VALUES (" . PHP_EOL;
		$comma = "";
		foreach ($fields as $col => $value){
			
			//Para não inserir campos que estão nulos
			if($this->skipNull && !isset($this->fieldsDefaultValuesOnInsert[$col]) && is_null($oEntity->{'get' . ucwords($col)}()))
				continue;
			
			if(!isset($this->fieldsIgnoreOnInsert[$col]) && ($col != $this->getColPK() | ( $col == $this->getColPK() && !is_null($oEntity->{$this->getMethodGetPk()}() ) ) ) ){
				
				if(isset($this->fieldsDefaultValuesOnInsert[$col]) && is_null($oEntity->{'get' . ucwords($col)}()))
					$this->sql .= $comma . "\t" . $this->fieldsDefaultValuesOnInsert[$col];
				else{
					$paramValues[$col] = $value;
					$this->sql .= $comma . "\t:" . $col;
				}
				
				$comma = "," . PHP_EOL;
			}
		}
		$this->sql .= PHP_EOL . ");";
		return $this->setParams($oEntity, $paramValues);
	}
	
	/**
	 * Prepara a atualização de um registro
	 * 
	 * @param PQDEntity $oEntity
	 * @param array $fields
	 * 
	 * @return \PDOStatement
	 */
	protected function prepareSQLUpdate(PQDEntity $oEntity, array $fields = null){
		
		$this->sql = "UPDATE " . $this->getTable() . " SET" . PHP_EOL;
		$comma = "";
		$fields = is_null($fields) ? $this->getFields() : $fields;
		$paramValues = array($this->getColPK() => $this->getField($this->getColPK()));
		
		foreach ($fields as $col => $value){
			
			//Para não atualizar campos que estão nulos
			if($this->skipNull && !isset($this->fieldsDefaultValuesOnUpdate[$col]) && is_null($oEntity->{'get' . ucwords($col)}()))
				continue;
			
			if($col != $this->getColPK() && !isset($this->fieldsIgnoreOnUpdate[$col])){
				
				if(isset($this->fieldsDefaultValuesOnUpdate[$col]) && is_null($oEntity->{'get' . ucwords($col)}()))
					$this->sql .= $comma . "\t" . $col . " = " . $this->fieldsDefaultValuesOnUpdate[$col];
				else{
					$paramValues[$col] = $value;
					$this->sql .= $comma . "\t" . $col . " = :" . $col;
				}
				
				$comma = "," . PHP_EOL;
			}
		}
		
		$strDefaultWhere = !is_null($this->defaultWhereOnDelete) ? " AND (" . $this->defaultWhereOnDelete->getWhere(false) . ")" : '';
		
		$this->sql .= PHP_EOL . "WHERE " . $this->getColPK() . " = :" . $this->getColPK() . ($this->getOperation() == "D" ? $strDefaultWhere : null) . ";";
		
		return $this->setParams($oEntity, $paramValues);
	}
	
	/**
	 * Prepara a exclusão de um registro
	 * 
	 * @param PQDEntity $oEntity
	 * @return \PDOStatement
	 */
	protected function prepareSQLDelete(PQDEntity $oEntity){
		
		$strDefaultWhere = !is_null($this->defaultWhereOnDelete) ? " AND (" . $this->defaultWhereOnDelete->getWhere(false) . ')' : '';
		
		$this->sql = "DELETE FROM " . $this->getTable() . " WHERE " . $this->getColPK() . " = :" . $this->getColPK() . $strDefaultWhere . ";";
		return $this->setParams($oEntity, array($this->getColPK() => $this->getField($this->getColPK())));
	}
	
	protected function save(PQDEntity &$oEntity){
		
		//Para forçar operações de insert mesmo quando a chave primária já está setada!
		if (is_null($this->getOperation())){
			if (is_null($oEntity->{$this->getMethodGetPk()}()))
				$this->setOperation("I");
			else
				$this->setOperation("U");
		}
		
		if ($this->getOperation() == "I") //INSERT
			$st = $this->prepareSQLInsert($oEntity);
		else
			$st = $this->prepareSQLUpdate($oEntity);//UPDATE
		
		if($st !== false && $st->execute() === true){
			$id = is_null($oEntity->{$this->getMethodGetPk()}()) ? $this->getConnection($this->getIndexCon())->lastInsertId() : $oEntity->{$this->getMethodGetPk()}();
			$oEntity = $this->retEntity($id);
			
			$this->cleanOperation();
			
			return true;
		}
		else {
			$this->cleanOperation();
			return false;
		}
	}
	
	private function cleanOperation(){
		if($this->cleanOperation)
			$this->operation = null;
	}
	
	/**
	 * 
	 * @param PQDEntity $oEntity
	 */
	protected function delete(PQDEntity $oEntity){
		$return = $this->prepareSQLDelete($oEntity)->execute();
		
		$this->cleanOperation();
		
		return $return;
	}
	
	/**
	 * Executa uma delete generico na tabela
	 * 
	 * @param SQLWhere $oWhere
	 */
	protected function deleteGeneric(SQLWhere $oWhere){
		
		if(is_null($this->getConnection($this->getIndexCon()))) return false;
		
		$strDefaultWhere = !is_null($this->defaultWhereOnDelete) ? " AND (" . $this->defaultWhereOnDelete->getWhere(false) . ')' : '';
		
		$this->sql = "DELETE FROM " . $this->getTable() . " " . $oWhere->getWhere(true) . $strDefaultWhere;
		
		$return = $this->getConnection($this->getIndexCon())->exec($this->sql) !== false;
		
		$this->cleanOperation();
		
		return $return;
	}
	
	/**
	 * @return bool $isAutoIncrement
	 */
	public function getIsAutoIncrement(){
		return $this->isAutoIncrement;
	}

	/**
	 * @return array $fieldsIgnoreOnUpdate
	 */
	public function getFieldsIgnoreOnUpdate(){
		return array_flip($this->fieldsIgnoreOnUpdate);
	}

	/**
	 * @return array $fieldsIgnoreOnInsert
	 */
	public function getFieldsIgnoreOnInsert(){
		return array_flip($this->fieldsIgnoreOnInsert);
	}

	/**
	 * @return array $fieldsDefaultValuesOnUpdate
	 */
	public function getFieldsDefaultValuesOnUpdate(){
		return $this->fieldsDefaultValuesOnUpdate;
	}

	/**
	 * @return array $fieldsDefaultValuesOnInsert
	 */
	public function getFieldsDefaultValuesOnInsert(){
		return $this->fieldsDefaultValuesOnInsert;
	}

	/**
	 * @param boolean $isAutoIncrement
	 */
	public function setIsAutoIncrement($isAutoIncrement){
		$this->isAutoIncrement = $isAutoIncrement;
	}

	/**
	 * @param array  $fieldsIgnoreOnUpdate
	 */
	public function setFieldsIgnoreOnUpdate(array $fieldsIgnoreOnUpdate){
		$this->fieldsIgnoreOnUpdate = array_flip($fieldsIgnoreOnUpdate);
	}

	/**
	 * @param string $fieldIgnoreOnUpdate
	 */
	public function addFieldIgnoreOnUpdate($fieldIgnoreOnUpdate){
		$this->fieldsIgnoreOnUpdate[$fieldIgnoreOnUpdate] = count($this->fieldsIgnoreOnUpdate);
	}

	/**
	 * @param array $fieldsIgnoreOnInsert
	 */
	public function setFieldsIgnoreOnInsert(array $fieldsIgnoreOnInsert){
		$this->fieldsIgnoreOnInsert = array_flip($fieldsIgnoreOnInsert);
	}

	/**
	 * @param string $fieldIgnoreOnInsert
	 */
	public function addFieldIgnoreOnInsert($fieldIgnoreOnInsert){
		$this->fieldsIgnoreOnInsert[$fieldIgnoreOnInsert] = count($this->fieldsIgnoreOnInsert);
	}

	/**
	 * @param array $fieldsDefaultValuesOnUpdate
	 */
	public function setFieldsDefaultValuesOnUpdate(array $fieldsDefaultValuesOnUpdate){
		$this->fieldsDefaultValuesOnUpdate = $fieldsDefaultValuesOnUpdate;
	}
	
	/**
	 * @param array $fieldDefaultValueOnUpdate
	 */
	public function addFieldDefaultValueOnUpdate(array $fieldDefaultValueOnUpdate){
		$this->fieldsDefaultValuesOnUpdate = $this->fieldsDefaultValuesOnUpdate + $fieldDefaultValueOnUpdate;
	}

	/**
	 * @param array $fieldsDefaultValuesOnInsert
	 */
	public function setFieldsDefaultValuesOnInsert(array $fieldsDefaultValuesOnInsert){
		$this->fieldsDefaultValuesOnInsert = $fieldsDefaultValuesOnInsert;
	}
	
	/**
	 * @param array $fieldDefaultValueOnInsert
	 */
	public function addFieldDefaultValueOnInsert(array $fieldDefaultValueOnInsert){
		$this->fieldsDefaultValuesOnInsert = $this->fieldsDefaultValuesOnInsert + $fieldDefaultValueOnInsert;
	}

	/**
	 * @return SQLWhere $defaultWhereOnDelete
	 */
	public function getDefaultWhereOnDelete(){
		return $this->defaultWhereOnDelete;
	}

	/**
	 * @param SQLWhere $defaultWhereOnDelete
	 */
	public function setDefaultWhereOnDelete( SQLWhere $defaultWhereOnDelete){
		$this->defaultWhereOnDelete = $defaultWhereOnDelete;
	}
	
	/**
	 * @return boolean $skipNull
	 */
	public function getSkipNull() {

		return $this->skipNull;
	}

	/**
	 * @param boolean $skipNull
	 */
	public function setSkipNull($skipNull) {

		$this->skipNull = $skipNull;
	}
	
	/**
	 * @return string $operation
	 */
	public function getOperation() {

		return $this->operation;
	}

	/**
	 * @param string $operation
	 */
	public function setOperation($operation) {

		$this->operation = $operation;
	}
	
	/**
	 * @return bool $cleanOperation
	 */
	public function getCleanOperation() {
		return $this->cleanOperation;
	}
	/**
	 * @param boolean $cleanOperation
	 */
	public function setCleanOperation($cleanOperation) {
		$this->cleanOperation = $cleanOperation;
	}
}