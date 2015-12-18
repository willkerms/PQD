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
	
	protected function prepareSQLInsert(PQDEntity $oEntity, array $fields = null){
		
		$this->sql = "INSERT INTO " . $this->getTable() . "(" . PHP_EOL;
		$comma = "";
		$fields = is_null($fields) ? $this->getFields() : $fields;
		$paramValues = array();
		
		foreach ($fields as $col => $value){
			
			//Para não inserir campos que estão nulos
			if($this->skipNull && !isset($this->fieldsDefaultValuesOnInsert[$col]) && is_null($oEntity->{'get' . ucwords($col)}()))
				continue;
			
			if(!isset($this->fieldsIgnoreOnInsert[$col]) && ($col != $this->getColPK() | ($col == $this->getColPK() && !$this->isAutoIncrement))){
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
			
			if(!isset($this->fieldsIgnoreOnInsert[$col]) && ($col != $this->getColPK() | ($col == $this->getColPK() && !$this->isAutoIncrement))){
				
				if(isset($this->fieldsDefaultValuesOnInsert[$col]) && is_null($oEntity->{'get' . ucwords($col)}()))
					$this->sql .= $comma . "\t" . $this->fieldsDefaultValuesOnInsert[$col];
				else{
					$paramValues[$col] = $value;
					$this->sql .= $comma . "\t:" . $col;
				}
				
				$comma = "," . PHP_EOL;
			}
		}
		$this->sql .= ");";
		return $this->setParams($oEntity, $paramValues);
	}
	
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
			
		$this->sql .= PHP_EOL . "WHERE " . $this->getColPK() . " = :" . $this->getColPK() . ";";
		
		return $this->setParams($oEntity, $paramValues);
	}
	
	protected function prepareSQLDelete(PQDEntity $oEntity){
		
		$strDefaultWhere = !is_null($this->defaultWhereOnDelete) ? " " . $this->defaultWhereOnDelete->getWhere(false) : '';
		
		$this->sql = "DELETE FROM " . $this->getTable() . " WHERE " . $this->getColPK() . " = :" . $this->getColPK() . $strDefaultWhere . ";";
		return $this->setParams($oEntity, array($this->getColPK() => $this->getField($this->getColPK())));
	}
	
	protected function save(PQDEntity &$oEntity){
		
		if (is_null($oEntity->{$this->getMethodGetPk()}())) //INSERT
			$st = $this->prepareSQLInsert($oEntity);
		else
			$st = $this->prepareSQLUpdate($oEntity);//UPDATE
		
		if($st->execute()){
			$id = is_null($oEntity->{$this->getMethodGetPk()}()) ? $this->getConnection($this->getIndexCon())->lastInsertId() : $oEntity->{$this->getMethodGetPk()}();
			$oEntity = $this->retEntity($id);
			return true;
		}
		else 
			return false;
	}
	
	/**
	 * 
	 * @param PQDEntity $oEntity
	 */
	protected function delete(PQDEntity $oEntity){
		return $this->prepareSQLDelete($oEntity)->execute();
	}
	
	/**
	 * Executa uma delete generico na tabela
	 * 
	 * @param SQLWhere $oWhere
	 */
	protected function deleteGeneric(SQLWhere $oWhere){
		$this->sql = "DELETE FROM " . $this->getTable() . " " . $oWhere->getWhere(true);
		return $this->getConnection($this->getIndexCon())->exec($this->sql) !== false;
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
		$this->fieldsIgnoreOnUpdate[$fieldIgnoreOnUpdate] = count($this->fieldsIgnoreOnUpdate[$fieldIgnoreOnUpdate]) - 1;
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
		$this->fieldsIgnoreOnInsert[$fieldIgnoreOnInsert] = count($this->fieldsIgnoreOnInsert[$fieldIgnoreOnInsert]) - 1;
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
}