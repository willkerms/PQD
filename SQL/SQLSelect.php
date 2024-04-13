<?php
namespace PQD\SQL;

use PQD\PQDDb;
use PQD\PQDExceptions;
use PQD\PQDPDO;
use PQD\PQDEntity;
use PQD\PQDStatement;
use PQD\SQL\SQLWhere;
use PQD\SQL\SQLJoin;

/**
 * Classe para fazer selects
 *
 * @author Willker Moraes Silva
 * @since 2015-12-16
 */
abstract class SQLSelect extends PQDDb{

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
	 * @var SQLGroupBy
	 */
	private $defaultGroupBy;
	/**
	 * @var SQLOrderBy
	 */
	private $defaultOrderBy;

	/**
	 * @var SQLWhere
	 */
	private $defaultWhereOnSelect;

	/**
	 * @var array
	 */
	private $fieldsDefaultOnSelect = array();


	/**
	 * @var int
	 */
	private $indexCon = 0;

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

	/**
	 * Mapeia os tipos primarios para a PDO
	 *
	 * @param mixed $col
	 * @return int
	 */
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

	/**
	 * Prepara e seta um sql
	 *
	 * @param PQDEntity $oEntity
	 * @param array $params
	 * @return \PDOStatement
	 */
	protected function setParams(PQDEntity $oEntity, array $params){
		if(is_null($this->getConnection($this->getIndexCon()))) return false;

		$st = $this->getConnection($this->getIndexCon())->prepare($this->sql);
		$this->setParamValues($st, $oEntity, $params);
		return $st;
	}

	/**
	 * Seta parametros em um Statement
	 *
	 * @param PQDStatement $st
	 * @param PQDEntity $oEntity
	 * @param array $aFields
	 */
	protected function setParamValues(PQDStatement $st, PQDEntity $oEntity, array $aFields){

		foreach ($aFields as $col => $value){
			$bind = ":" . $col;

			if(PQD_ORM_FORMAT_FIELD)
				$col = join("", array_map("ucwords", explode("_", $col)));

			$method = 'get' . $col;

			$st->bindValue($bind, $oEntity->{$method}(), $this->retParamType($value));
		}
	}

	private function retAliasFields(array $fields, $alias = null){

		if(empty($alias))
			return join(', ', $fields);

		foreach ($fields as &$field ){
			if(preg_match('/^[a-zA-Z0-9*_]+$/', $field) === 1)
				$field = $alias . "." . $field;
		}

		return join(', ', $fields);
	}

	/**
	 * Retorna uma entidade
	 *
	 * @param int $id
	 * @param string $fetchClass
	 * @return object
	 */
	public function retEntity($id, $fetchClass = true, array $fields = null){

		$table = !is_null($this->view) ? $this->view: $this->table;
		$clsFetch = !is_null($this->clsView) ? $this->clsView: $this->clsEntity;
		$joins = $this->getDefaultWhereOnSelect() instanceof SQLJoin ? ' ' . $this->getDefaultWhereOnSelect()->getJoins(true): '';
		$alias = $this->getDefaultWhereOnSelect() instanceof SQLWhere && !is_null($this->getDefaultWhereOnSelect()->getAlias()) ? $this->getDefaultWhereOnSelect()->getAlias() . ".": '';
		$where = $this->getDefaultWhereOnSelect() instanceof SQLWhere && $this->getDefaultWhereOnSelect()->count() > 0 ? ' AND ' . $this->getDefaultWhereOnSelect()->getWhere(false): '';

		$this->sql = "SELECT " . (is_null($fields) ? $this->retFieldsSelect() : $this->retAliasFields($fields, ($this->getDefaultWhereOnSelect() instanceof SQLWhere ? $this->getDefaultWhereOnSelect()->getAlias() : null))) . " FROM " . $table . $joins . " WHERE (" . $alias . $this->colPK . " = :" . $this->colPK . ')' . $where . ";";

		$oEntity = new $clsFetch();
		$oEntity->{$this->methodSetPk}($id);

		$st = $this->setParams($oEntity, array($this->colPK => $this->fields[$this->colPK]));

		if($st->execute()){

			if($fetchClass)
				$data = $st->fetchAll(PQDPDO::FETCH_CLASS, $clsFetch);
			else
				$data = $st->fetchAll(PQDPDO::FETCH_NAMED);

			if(count($data) == 1)
				return $data[0];
			else
				return $this->retEmpty($fetchClass);
		}
		else{
			$this->getExceptions()->setException( new \Exception("Erro ao retornar Entidade!"));
			return new $this->clsEntity();
		}
	}

	/**
	 * Retorna uma consulta vazia
	 *
	 * @param string $fetchClass
	 * @return mixed PQDEntity|array
	 */
	protected function retEmpty($fetchClass = true){
		$clsFetch = !is_null($this->getClsView()) ? $this->getClsView(): $this->getClsEntity();

		if ($fetchClass)
			return new $clsFetch();
		else
			return array();
	}

	private function retFieldsSelect(){
		if(count($this->fieldsDefaultOnSelect) > 0){
			return $this->retAliasFields($this->fieldsDefaultOnSelect, ($this->getDefaultWhereOnSelect() instanceof SQLWhere ? $this->getDefaultWhereOnSelect()->getAlias() : null) );
		}
		else
			return '*';
	}

	private function retGroupBy(SQLWhere $oWhere = null, SQLGroupBy $oGroupBy = null){

		$oGroupBy = is_null($oGroupBy) ? $this->getDefaultGroupBy() : $oGroupBy;
		$oWhere = is_null($oWhere) ? $this->getDefaultWhereOnSelect() : $oWhere;

		if (is_null($oGroupBy))
			return "";

		$return = "";

		if(is_null($oGroupBy->getAlias()))
			$oGroupBy->setAlias($oWhere->getAlias());

		$return = $oGroupBy->getGroupBy();

		return $return;
	}

	private function retOrderBy(SQLWhere $oWhere = null, SQLOrderBy $oOrderBy = null){

		$oOrderBy = is_null($oOrderBy) ? $this->getDefaultOrderBy() : $oOrderBy;
		$oWhere = is_null($oWhere) ? $this->getDefaultWhereOnSelect() : $oWhere;

		if(is_null($oOrderBy))
			return "";

		$return = "";

		if(is_null($oOrderBy->getAlias()))
			$oOrderBy->setAlias($oWhere->getAlias());

		$return = $oOrderBy->getOrderBy();

		return $return;
	}

	private function retWhere(SQLWhere $oWhere){

		$oWhere2 = clone $oWhere;

		if(!is_null($this->getDefaultWhereOnSelect())){

			$oWhereDefault = clone $this->getDefaultWhereOnSelect();
			if( $oWhere2 instanceof SQLJoin ){

				if($oWhereDefault instanceof SQLJoin ){
					$oWhere2->setJoins(array_merge($oWhereDefault->getJoins(), $oWhere2->getJoins()));
					$oWhereDefault->clearJoins()->setAlias($oWhere2->getAlias());
				}
				else
					$oWhereDefault->setAlias($oWhere2->getAlias());

				if($oWhereDefault->count() > 0){
					if($oWhere2->count() > 0)
						$oWhere2->setAnd();

					$oWhere2->setSQL(trim($oWhereDefault->getWhere(false)));
				}
			}
			//else if($this->getDefaultWhereOnSelect() instanceof SQLJoin){
			else{
				if($oWhere2->count() > 0 ){

					if($oWhereDefault->count() > 0)
						$oWhereDefault->setAnd();

					if($oWhereDefault instanceof SQLJoin )
						$oWhere2->setAlias($oWhereDefault->getAlias());

					$oWhere2 = $oWhereDefault->setSQL($oWhere2->getWhere(false));
				}

				$oWhere2 = $oWhereDefault;
			}
		}

		return $oWhere2;
	}

	/**
	 * Executa uma query
	 *
	 * @param string $fetchClass
	 * @param string $clsFetch
	 * @param boolean $setException
	 * @return array
	 */
	private function query($fetchClass = true, $clsFetch, $setException = true){

		$data = array();

		if(is_null($this->getConnection($this->getIndexCon()))) return $data;

		if(($st = $this->getConnection($this->getIndexCon())->query($this->sql)) !== false){
			if($fetchClass)
				$data = $st->fetchAll(PQDPDO::FETCH_CLASS, $clsFetch);
			else
				$data = $st->fetchAll(PQDPDO::FETCH_NAMED);
		}
		else if($setException){
			$this->getExceptions()->setException( new \Exception("Erro ao Executar Consulta!"));
		}

		return $data;
	}

	/**
	 * Busca todos os registros da tabela
	 *
	 * @param array $fields
	 * @param string $fetchClass
	 * @param SQLOrderBy $oOrderBy
	 * @return array
	 */
	public function fetchAll(array $fields = null, $fetchClass = true, SQLOrderBy $oOrderBy = null, SQLGroupBy $oGroupBy = null){
		$table = !is_null($this->view) ? $this->view: $this->table;
		$clsFetch = !is_null($this->clsView) ? $this->clsView: $this->clsEntity;

		if(!is_null($fields))
			$sqlFields = $this->retAliasFields($fields, !is_null($this->defaultWhereOnSelect) ? $this->defaultWhereOnSelect->getAlias() : null);
		else
			$sqlFields = $this->retFieldsSelect();

		$this->sql = "SELECT ". $sqlFields ." FROM " . $table . (!is_null($this->defaultWhereOnSelect) ? " " . $this->getDefaultWhereOnSelect()->getWhere(true) : '');

		$this->sql .= $this->retGroupBy(null, $oGroupBy);

		$this->sql .= $this->retOrderBy(null, $oOrderBy);

		$this->sql .= ";";

		return $this->query($fetchClass, $clsFetch);
	}

	/**
	 * Retorna o numero de registros dá busca generica
	 *
	 * @param SQLWhere $oWhere
	 * @return int
	 */
	public function retNumReg(SQLWhere $oWhere){

		$table = !is_null($this->view) ? $this->view: $this->table;
		$oWhere = $this->retWhere($oWhere);

		$this->sql = 'SELECT COUNT(*) AS "numReg" FROM ' . $table . " " . $oWhere->getWhere(true) . ";";
		$data = $this->query(false, null, false);

		return count($data) == 1 ? $data[0]['numReg'] : 0;
	}

	/**
	 * Executa uma busca generica na tabela
	 *
	 * @param SQLWhere $oWhere
	 * @param array $fields
	 * @param string $fetchClass
	 * @param SQLOrderBy $oOrderBy
	 * @param int $limit
	 * @param number $page
	 * @param SQLGroupBy $groupBy
	 * @return array
	 */
	public function genericSearch(SQLWhere $oWhere, array $fields = null, $fetchClass = true, SQLOrderBy $oOrderBy = null, $limit = null, $page = 0, SQLGroupBy $oGroupBy = null){

		$table = !is_null($this->view) ? $this->view: $this->table;
		$clsFetch = !is_null($this->clsView) ? $this->clsView: $this->clsEntity;

		$oWhere = $this->retWhere($oWhere);

		$sOrderBy = $this->retOrderBy($oWhere, $oOrderBy);
		if(empty($sOrderBy) && !is_null($limit) && $this->getDriverDB($this->indexCon) == "mssql")
			$sOrderBy = $this->retOrderBy($oWhere, new SQLOrderBy(array($this->getColPK())));;

		$this->sql = "";

		//Versões anteriores ao 2012 do SQLServer
		$rowNumber = "";
		$isLessSqlSrv11 = false;
		/*
		 Todos os tratamentos para OrderBy estão acima
		if($this->getDriverDB($this->getIndexCon()) == "mssql" && is_null($this->getDefaultOrderBy()) && is_null($oOrderBy))
			$oOrderBy = new SQLOrderBy(array($this->getColPK()));
		*/

		//Limit para Versões anteriores ao 2012
		if(!is_null($limit) && ($this->getDriverDB($this->getIndexCon()) == "mssql")){
			if($this->getDriverDB($this->getIndexCon(), true) == "dblib" || version_compare($this->getConnection($this->getIndexCon())->getAttribute(PQDPDO::ATTR_SERVER_VERSION), '11') < 0){
				$isLessSqlSrv11 = true;

				$rowNumber = "ROW_NUMBER() OVER ( " . $sOrderBy . " ) AS RowNum, ";

				$this->sql .= "SELECT * FROM (";
			}
		}


		if(!is_null($fields))
			$sqlFields = $this->retAliasFields($fields, $oWhere->getAlias());
		else
			$sqlFields = $this->retFieldsSelect();

		$this->sql .= "SELECT " . $rowNumber . $sqlFields . " FROM " . $table . " " . $oWhere->getWhere(true);

		//Group By
		$this->sql .= $this->retGroupBy($oWhere, $oGroupBy);

		//ORDER BY
		if(!$isLessSqlSrv11)
			$this->sql .= $sOrderBy;

		//Limit
		if(!is_null($limit)){
			$page = !is_null($limit) && $page <= 0 ? 1 : intval($page);
			$limit = $limit <= 0 ? 12 : intval($limit);

			if($this->getDriverDB($this->getIndexCon()) == "mysql")
				$this->sql .= " LIMIT " . (($page - 1) * $limit) . "," . $limit . ";";
			else if($this->getDriverDB($this->getIndexCon()) == "pgsql")
				$this->sql .= " LIMIT " . $limit . " OFFSET " . (($page - 1) * $limit) . ";";
			else if(!is_null($limit) && $this->getDriverDB($this->getIndexCon()) == "mssql"){
				//SQLServer 2012
				if($this->getDriverDB($this->getIndexCon(), true) != "dblib" && version_compare($this->getConnection($this->getIndexCon())->getAttribute(PQDPDO::ATTR_SERVER_VERSION), '11') >= 0)
					$this->sql .= " OFFSET " . (($page - 1) * $limit) . " ROWS FETCH NEXT " . $limit . " ROWS ONLY;";
				else//Versões anteriores ao 2012 do SQLServer
					$this->sql .= ") as vw WHERE RowNum BETWEEN " . ((($page - 1) * $limit) + 1) . " AND " . ((($page - 1) * $limit) + $limit) . ";";
			}
		}
		else
			$this->sql .= ";";

		return $this->query($fetchClass, $clsFetch);
	}

	/**
	 * Executa uma busca generica em uma tabela
	 * 
	 * @param string $table
	 * @param SQLWhere $oWhere
	 * @param array $fields
	 * @param SQLOrderBy $oOrderBy
	 * @param int $limit
	 * @param number $page
	 * @param SQLGroupBy $groupBy
	 * @return array
	 */
	public function genericSearchTable($table, SQLWhere $oWhere = null, array $fields = null, SQLOrderBy $oOrderBy = null, $limit = null, $page = 0, SQLGroupBy $oGroupBy = null){

		$oWhere = is_null($oWhere) ? new SQLWhere() : $oWhere;

		if($oWhere instanceof SQLJoin && !is_null($oOrderBy))
			$oOrderBy->setAlias($oWhere->getAlias());
			
		$sOrderBy = is_null($oOrderBy) ? "" : $oOrderBy->getOrderBy();

		if(empty($sOrderBy) && !is_null($limit) && $this->getDriverDB($this->indexCon) == "mssql"){
			
			$oOrderBy = new SQLOrderBy(array($this->getColPK()));

			if($oWhere instanceof SQLJoin && !is_null($oOrderBy))
				$oOrderBy->setAlias($oWhere->getAlias());

			$sOrderBy = $oOrderBy->getOrderBy();
		}

		$this->sql = "";

		if(!is_null($fields))
			$sqlFields = join(', ', $fields);
		else
			$sqlFields = '*';

		//Versões anteriores ao 2012 do SQLServer
		$rowNumber = "";
		$isLessSqlSrv11 = false;
		/*
		 Todos os tratamentos para OrderBy estão acima
		if($this->getDriverDB($this->getIndexCon()) == "mssql" && is_null($this->getDefaultOrderBy()) && is_null($oOrderBy))
			$oOrderBy = new SQLOrderBy(array($this->getColPK()));
		*/

		//Limit para Versões anteriores ao 2012
		if(!is_null($limit) && ($this->getDriverDB($this->getIndexCon()) == "mssql")){
			if($this->getDriverDB($this->getIndexCon(), true) == "dblib" || version_compare($this->getConnection($this->getIndexCon())->getAttribute(PQDPDO::ATTR_SERVER_VERSION), '11') < 0){
				$isLessSqlSrv11 = true;

				$rowNumber = "ROW_NUMBER() OVER ( " . $sOrderBy . " ) AS RowNum, ";

				$this->sql .= "SELECT * FROM (";
			}
		}

		$this->sql .= "SELECT " . $rowNumber . $sqlFields . " FROM " . $table . " " . $oWhere->getWhere(true);

		//Group By
		if(!is_null($oGroupBy)){

			if($oWhere instanceof SQLJoin)
				$oGroupBy->setAlias($oWhere->getAlias());

			$this->sql .= $oGroupBy->getGroupBy();
		}

		//ORDER BY
		if(!$isLessSqlSrv11)
			$this->sql .= $sOrderBy;

		//Limit
		if(!is_null($limit)){
			$page = !is_null($limit) && $page <= 0 ? 1 : intval($page);
			$limit = $limit <= 0 ? 12 : intval($limit);

			if($this->getDriverDB($this->getIndexCon()) == "mysql")
				$this->sql .= " LIMIT " . (($page - 1) * $limit) . "," . $limit . ";";
			else if($this->getDriverDB($this->getIndexCon()) == "pgsql")
				$this->sql .= " LIMIT " . $limit . " OFFSET " . (($page - 1) * $limit) . ";";
			else if(!is_null($limit) && $this->getDriverDB($this->getIndexCon()) == "mssql"){
				//SQLServer 2012
				if($this->getDriverDB($this->getIndexCon(), true) != "dblib" && version_compare($this->getConnection($this->getIndexCon())->getAttribute(PQDPDO::ATTR_SERVER_VERSION), '11') >= 0)
					$this->sql .= " OFFSET " . (($page - 1) * $limit) . " ROWS FETCH NEXT " . $limit . " ROWS ONLY;";
				else//Versões anteriores ao 2012 do SQLServer
					$this->sql .= ") as vw WHERE RowNum BETWEEN " . ((($page - 1) * $limit) + 1) . " AND " . ((($page - 1) * $limit) + $limit) . ";";
			}
		}
		else
			$this->sql .= ";";

		return $this->query(false, '');		
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
		return isset($this->fields[$field]) ? $this->fields[$field] : null;
	}

	/**
	 * @return array $fields
	 */
	public function getFields(){
		return $this->fields;
	}

	/**
	 * @return int $indexCon
	 */
	public function getIndexCon(){
		return $this->indexCon;
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
	 * @param SQLOrderBy $oOrderBy
	 */
	public function setDefaultOrderBy(SQLOrderBy $oOrderBy){
		$this->defaultOrderBy = $oOrderBy;
	}

	/**
	 * @param SQLGroupBy $oGroupBy
	 */
	public function setDefaultGroupBy(SQLGroupBy $oGroupBy){
		$this->defaultGroupBy = $oGroupBy;
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
	 * @param string $methodGetPk
	 */
	public function setMethodGetPk($methodGetPk){
		$this->methodGetPk = $methodGetPk;
	}

	/**
	 * @param string $methodSetPk
	 */
	public function setMethodSetPk($methodSetPk){
		$this->methodSetPk = $methodSetPk;
	}

	/**
	 * @return SQLWhere $defaultWhereOnSelect
	 */
	public function getDefaultWhereOnSelect(){
		return $this->defaultWhereOnSelect;
	}

	/**
	 * @param SQLWhere $defaultWhereOnSelect
	 */
	public function setDefaultWhereOnSelect(SQLWhere $defaultWhereOnSelect){
		$this->defaultWhereOnSelect = $defaultWhereOnSelect;
	}

	/**
	 * @return SQLOrderBy $defaultOrderBy
	 */
	public function getDefaultOrderBy(){
		return $this->defaultOrderBy;
	}

	/**
	 * @return SQLGroupBy $defaultGroupBy
	 */
	public function getDefaultGroupBy(){
		return $this->defaultGroupBy;
	}

	/**
	 * @return array $fieldsDefaultOnSelect
	 */
	public function getFieldsDefaultOnSelect() {
		return $this->fieldsDefaultOnSelect;
	}

	/**
	 *
	 * @param array $fieldsDefaultOnSelect
	 */
	public function setFieldsDefaultOnSelect(array $fieldsDefaultOnSelect) {
		$this->fieldsDefaultOnSelect = $fieldsDefaultOnSelect;
	}

	/**
	 * @param $fieldsDefaultOnSelect
	 */
	public function addFieldDefaultOnSelect($fieldDefaultOnSelect) {
		$this->fieldsDefaultOnSelect[] = $fieldDefaultOnSelect;
	}

	/**
	 * Quando o indice da conexão não é passado pega a conexão padrão do DAO,
	 * se não foi informado a conexão padrão no construtor do DAO pega a conexão padrão com o indice 0
	 *
	 * Quando o indice da conexão é passado pega a conexão solicitada.
	 *
	 * {@inheritDoc}
	 * @see \PQD\PQDDb::getConnection()
	 * @author Willker Moraes Silva
	 * @since 2017-08-18
	 *
	 * @return PQDPDO
	 */
	public function getConnection($indexCon = null) {
		return parent::getConnection(is_null($indexCon) ? $this->getIndexCon() : $indexCon);
	}
}