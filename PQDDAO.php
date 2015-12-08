<?php
namespace PQD;

use PQD\SQL\SQLWhere;

/**
 * Classe de abastração do banco de dados
 * 
 * @author Willker Moraes Silva
 * @since 2015-11-12
 */
abstract class PQDDAO extends PQDDb{
	
	/**
	 * @var bool
	 */
	private $isAutoIncrement = true;
	
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
	private $methodGetPk;
	
	/**
	 * @var string
	 */
	private $methodSetPk;
	
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
	
	/**
	 * @var SQLWhere
	 */
	private $defaultWhereOnSelect;
	
	/**
	 * @var SQLWhere
	 */
	private $defaultWhereOnDelete;
	
	/**
	 * @var int
	 */
	private $indexCon = 0;
	
	/**
	 * @var bool
	 */
	private $skipNull = true;
	
	public function __construct($table, array $fields, $colPk, $clsEntity, PQDExceptions $exceptions, $view = null, $clsView = null, $indexCon = 0){
		
		$this->table = $table;
		$this->fields = $fields;
		$this->colPK = $colPk;
		$this->clsEntity = $clsEntity;
		
		$this->methodGetPk = ucwords($this->colPK);
		$this->methodSetPk = 'set' . $this->methodGetPk;
		$this->methodGetPk = 'get' . $this->methodGetPk;
		
		$this->view = $view;
		$this->clsView = $clsView;
		
		$this->indexCon = $indexCon;
		
		parent::__construct($exceptions);
	}
	
	protected function prepareSQLInsert(PQDEntity $oEntity, array $fields = null){
		
		$this->sql = "INSERT INTO " . $this->table . "(" . PHP_EOL;
		$comma = "";
		$fields = is_null($fields) ? $this->fields : $fields;
		$paramValues = array();
		
		foreach ($fields as $col => $value){
			
			//Para não inserir campos que estão nulos
			if($this->skipNull && !isset($this->fieldsDefaultValuesOnInsert[$col]) && is_null($oEntity->{'get' . ucwords($col)}()))
				continue;
			
			if(!isset($this->fieldsIgnoreOnInsert[$col]) && ($col != $this->colPK | ($col == $this->colPK && !$this->isAutoIncrement))){
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
			
			if(!isset($this->fieldsIgnoreOnInsert[$col]) && ($col != $this->colPK | ($col == $this->colPK && !$this->isAutoIncrement))){
				
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
		
		$this->sql = "UPDATE " . $this->table . " SET" . PHP_EOL;
		$comma = "";
		$fields = is_null($fields) ? $this->fields : $fields;
		$paramValues = array($this->colPK => $this->fields[$this->colPK]);
		
		foreach ($fields as $col => $value){
			
			//Para não atualizar campos que estão nulos
			if($this->skipNull && !isset($this->fieldsDefaultValuesOnUpdate[$col]) && is_null($oEntity->{'get' . ucwords($col)}()))
				continue;
			
			if($col != $this->colPK && !isset($this->fieldsIgnoreOnUpdate[$col])){
				
				if(isset($this->fieldsDefaultValuesOnUpdate[$col]) && is_null($oEntity->{'get' . ucwords($col)}()))
					$this->sql .= $comma . "\t" . $col . " = " . $this->fieldsDefaultValuesOnUpdate[$col];
				else{
					$paramValues[$col] = $value;
					$this->sql .= $comma . "\t" . $col . " = :" . $col;
				}
				
				$comma = "," . PHP_EOL;
			}
		}
			
		$this->sql .= PHP_EOL . "WHERE " . $this->colPK . " = :" . $this->colPK . ";";
		
		return $this->setParams($oEntity, $paramValues);
	}
	
	protected function prepareSQLDelete(PQDEntity $oEntity){
		
		$strDefaultWhere = !is_null($this->defaultWhereOnDelete) ? " " . $this->defaultWhereOnDelete->getWhere() : '';
		
		$this->sql = "DELETE FROM " . $this->table . " WHERE " . $this->colPK . " = :" . $this->colPK . $strDefaultWhere . ";";
		return $this->setParams($oEntity, array($this->colPK => $this->fields[$this->colPK]));
	}
	
	protected function retParamType($col){
		if(is_array($col))
			$type = $col['type'];
		else
			$type = $col;
		
		switch ($type) {
			case 'int':
				return PQDPDO::PARAM_INT;
			break;
			
			case 'bool':
				return PQDPDO::PARAM_BOOL;
			break;
			
			default:
				return PQDPDO::PARAM_STR;
			break;
		}
	}
	
	private function setParams(PQDEntity $oEntity, array $params){
		$st = $this->getConnection($this->indexCon)->prepare($this->sql);
		$this->setParamValues($st, $oEntity, $params);
		return $st;
	}
	
	protected function setParamValues(PQDStatement $st, PQDEntity $oEntity, array $aFields){
		
		foreach ($aFields as $col => $value){
			$bind = ":" . $col;
			$method = 'get' . ucwords($col);
			
			$st->bindValue($bind, $oEntity->{$method}(), $this->retParamType($value));
		}
	}
	
	protected function save(PQDEntity &$oEntity){
		
		if (is_null($oEntity->{$this->methodGetPk}())) //INSERT
			$st = $this->prepareSQLInsert($oEntity);
		else
			$st = $this->prepareSQLUpdate($oEntity);//UPDATE
		
		if($st->execute()){
			$id = is_null($oEntity->{$this->methodGetPk}()) ? $this->getConnection($this->indexCon)->lastInsertId() : $oEntity->{$this->methodGetPk}();
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
	
	protected function retEntity($id, $fetchClass = true){
		
		$table = !is_null($this->view) ? $this->view: $this->table;
		$clsFetch = !is_null($this->clsView) ? $this->clsEntity: $this->clsEntity;
		
		$this->sql = "SELECT * FROM " . $table . " WHERE " . $this->colPK . " = :" . $this->colPK . ";";
		
		$oEntity = new $clsFetch();
		$oEntity->{$this->methodSetPk}($id);
		
		$st = $this->setParams($oEntity, array($this->colPK => $this->fields[$this->colPK]));
		
		if($st->execute()){
			if($fetchClass){
				$data = $st->fetchAll(PQDPDO::FETCH_CLASS, $clsFetch);
				if(count($data) == 1)
					return $data[0];
				else
					return new $clsFetch();
			}
			else{
				$data = $st->fetchAll(PQDPDO::FETCH_NAMED);
				
				if(count($data) == 1)
					return $data[0];
				else
					return array();
			}
		}
		else{
			PQDApp::getApp()->getExceptions()->setException( new \Exception("Erro ao retornar Entidade!"));
			return new $this->clsEntity();
		}
	}
	
	private function query($fetchClass = true, $clsFetch, $setException = true){
		
		$data = array();
		if(($st = $this->getConnection($this->indexCon)->query($this->sql)) !== false){
			if($fetchClass)
				$data = $st->fetchAll(PQDPDO::FETCH_CLASS, $clsFetch);
			else
				$data = $st->fetchAll(PQDPDO::FETCH_NAMED);
		}
		else if($setException){
			PQDApp::getApp()->getExceptions()->setException( new \Exception("Erro ao Executar Consulta!"));
		}
		return $data;
	}
	
	public function fetchAll(array $fields = null, $fetchClass = true, array $orderBy = null, $asc = true){
		$table = !is_null($this->view) ? $this->view: $this->table;
		$clsFetch = !is_null($this->clsView) ? $this->clsEntity: $this->clsEntity;
		
		if(!is_null($fields))
			$sqlFields = join(', ', $fields);
		else
			$sqlFields = '*';
		
		$this->sql = "SELECT ". $sqlFields ." FROM " . $table . (!is_null($this->defaultWhereOnSelect) ? " " . $this->defaultWhereOnSelect->getWhere(true) : '');
		
		if (!is_null($orderBy) && count($orderBy) > 0)
			$this->sql .= " ORDER BY " . join(", ", $orderBy) . ($asc === true ? ' ASC': ' DESC') . ";";
		else
			$this->sql .= ";";
		
		return $this->query($fetchClass, $clsFetch);
	}
	
	public function retNumReg(SQLWhere $oWhere){
		
		$table = !is_null($this->view) ? $this->view: $this->table;
		$this->sql = "SELECT COUNT(*) AS numReg FROM " . $table . " " . $oWhere->getWhere(true);
		$data = $this->query(false, null, false);
		
		return count($data) == 1 ? $data[0]['numReg'] : 0;
	}
	
	public function genericSearch(SQLWhere $oWhere, array $fields = null, $fetchClass = true, array $orderBy = null, $asc = true, $limit = null, $page = 0, array $groupBy = null){
		$table = !is_null($this->view) ? $this->view: $this->table;
		$clsFetch = !is_null($this->clsView) ? $this->clsEntity: $this->clsEntity;
		
		$this->sql = "";
		
		if(!is_null($fields))
			$sqlFields = join(', ', $fields);
		else
			$sqlFields = '*';
		
		$page = !is_null($limit) && $page <= 0 ? 1 : $page;

		//Versões anteriores ao 2012 do SQLServer
		$rowNumber = "";
		if(!is_null($limit) && ($this->getConnection()->getAttribute(PQDPDO::ATTR_DRIVER_NAME) == "mssql" || $this->getConnection()->getAttribute(PQDPDO::ATTR_DRIVER_NAME) == "sqlsrv") && version_compare($this->getConnection($this->indexCon)->getAttribute(PQDPDO::ATTR_SERVER_VERSION), '11') < 0){
			
			if (is_null($orderBy) || count($orderBy) == 0)
				$orderBy = array($this->colPK);
			
			$rowNumber = "ROW_NUMBER() OVER ( ORDER BY " . join(", ", $orderBy) . " ) AS RowNum, ";
			$orderBy = null;
			
			$this->sql .= "SELECT " . $sqlFields . " FROM (";
		}
		
		if(!is_null($this->defaultWhereOnSelect)){
			if($oWhere->count() > 0)
				$oWhere->setAnd();
			
			$oWhere->setSQL($this->defaultWhereOnSelect->getWhere(false));
		}

		$this->sql .= "SELECT " . $rowNumber . $sqlFields . " FROM " . $table . " " . $oWhere->getWhere(true);
		
		if (!is_null($groupBy) && count($groupBy) > 0)
			$this->sql .= " GROUP BY " . join(", ", $groupBy);
		
		if (!is_null($orderBy) && count($orderBy) > 0)
			$this->sql .= " ORDER BY " . join(", ", $orderBy) . ($asc === true ? ' ASC': ' DESC');
		
		if(!is_null($limit) && $this->getConnection()->getAttribute(PQDPDO::ATTR_DRIVER_NAME) == "mysql"){
			$limit = $limit <= 0 ? 12 : $limit;
			$this->sql .= " LIMIT " . (($page - 1) * $limit) . "," . $limit;
		}
		//SQLServer 2012
		else if(!is_null($limit) && ($this->getConnection()->getAttribute(PQDPDO::ATTR_DRIVER_NAME) == "mssql" || $this->getConnection()->getAttribute(PQDPDO::ATTR_DRIVER_NAME) == "sqlsrv") && version_compare($this->getConnection($this->indexCon)->getAttribute(PQDPDO::ATTR_SERVER_VERSION), '11') >= 0){
			
			//No SQL Server é necessário ORDER BY
			if (is_null($orderBy) || count($orderBy) == 0)
				$this->sql .= " ORDER BY " . $this->colPK . ($asc === true ? ' ASC': ' DESC');

			$limit = $limit <= 0 ? 12 : $limit;
			$this->sql .= " OFFSET " . (($page - 1) * $limit) . " ROWS FETCH NEXT " . $limit . " ROWS ONLY";
		}
		//Versões anteriores ao 2012 do SQLServer
		else if(!is_null($limit) && ($this->getConnection()->getAttribute(PQDPDO::ATTR_DRIVER_NAME) == "mssql" || $this->getConnection()->getAttribute(PQDPDO::ATTR_DRIVER_NAME) == "sqlsrv") && version_compare($this->getConnection($this->indexCon)->getAttribute(PQDPDO::ATTR_SERVER_VERSION), '11') < 0)
				$this->sql .= ") as vw WHERE RowNum BETWEEN " . ((($page - 1) * $limit) + 1) . " AND " . ((($page - 1) * $limit) + $limit);
		
		return $this->query($fetchClass, $clsFetch);
	}
	
	/**
	 * @return bool $isAutoIncrement
	 */
	public function getIsAutoIncrement(){
		return $this->isAutoIncrement;
	}

	/**
	 * @return string $table
	 */
	public function getTable(){
		return $this->table;
	}

	/**
	 * @return string $view
	 */
	public function getView(){
		return $this->view;
	}

	/**
	 * @return string $colPK
	 */
	public function getColPK(){
		return $this->colPK;
	}

	/**
	 * @return string $clsEntity
	 */
	public function getClsEntity(){
		return $this->clsEntity;
	}

	/**
	 * @return string $clsView
	 */
	public function getClsView(){
		return $this->clsView;
	}

	/**
	 * @return array $field
	 */
	public function getField($field){
		return isset($this->fields[$field]) ? $this->fields[$field] : array();
	}
	
	/**
	 * @return array $fields
	 */
	public function getFields(){
		return $this->fields;
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
	 * @return int $indexCon
	 */
	public function getIndexCon(){
		return $this->indexCon;
	}

	/**
	 * @param boolean $isAutoIncrement
	 */
	public function setIsAutoIncrement($isAutoIncrement){
		$this->isAutoIncrement = $isAutoIncrement;
	}

	/**
	 * @param string $table
	 */
	public function setTable($table){
		$this->table = $table;
	}

	/**
	 * @param string $view
	 */
	public function setView($view){
		$this->view = $view;
	}

	/**
	 * @param string $colPK
	 */
	public function setColPK($colPK){
		$this->colPK = $colPK;
	}

	/**
	 * @param string $clsEntity
	 */
	public function setClsEntity($clsEntity){
		$this->clsEntity = $clsEntity;
	}

	/**
	 * @param string $clsView
	 */
	public function setClsView($clsView){
		$this->clsView = $clsView;
	}

	/**
	 * @param array $fields
	 */
	public function setFields(array $fields){
		$this->fields = $fields;
	}

	/**
	 * @param array  $fieldsIgnoreOnUpdate
	 */
	public function setFieldsIgnoreOnUpdate(array $fieldsIgnoreOnUpdate){
		$this->fieldsIgnoreOnUpdate = array_flip($fieldsIgnoreOnUpdate);
	}

	/**
	 * @param array $fieldsIgnoreOnInsert
	 */
	public function setFieldsIgnoreOnInsert(array $fieldsIgnoreOnInsert){
		$this->fieldsIgnoreOnInsert = array_flip($fieldsIgnoreOnInsert);
	}

	/**
	 * @param array $fieldsDefaultValuesOnUpdate
	 */
	public function setFieldsDefaultValuesOnUpdate(array $fieldsDefaultValuesOnUpdate){
		$this->fieldsDefaultValuesOnUpdate = $fieldsDefaultValuesOnUpdate;
	}

	/**
	 * @param array $fieldsDefaultValuesOnInsert
	 */
	public function setFieldsDefaultValuesOnInsert(array $fieldsDefaultValuesOnInsert){
		$this->fieldsDefaultValuesOnInsert = $fieldsDefaultValuesOnInsert;
	}

	/**
	 * @param number $indexCon
	 */
	public function setIndexCon($indexCon){
		$this->indexCon = $indexCon;
	}
	
	/**
	 * @return string $methodGetPk
	 */
	public function getMethodGetPk(){
		return $this->methodGetPk;
	}

	/**
	 * @return string $methodSetPk
	 */
	public function getMethodSetPk(){
		return $this->methodSetPk;
	}
	
	/**
	 * @return SQLWhere $defaultWhereOnSelect
	 */
	public function getDefaultWhereOnSelect(){
		return $this->defaultWhereOnSelect;
	}

	/**
	 * @return SQLWhere $defaultWhereOnDelete
	 */
	public function getDefaultWhereOnDelete(){
		return $this->defaultWhereOnDelete;
	}

	/**
	 * @param \PQD\SQL\SQLWhere $defaultWhereOnSelect
	 */
	public function setDefaultWhereOnSelect($defaultWhereOnSelect){
		$this->defaultWhereOnSelect = $defaultWhereOnSelect;
	}

	/**
	 * @param \PQD\SQL\SQLWhere $defaultWhereOnDelete
	 */
	public function setDefaultWhereOnDelete($defaultWhereOnDelete){
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