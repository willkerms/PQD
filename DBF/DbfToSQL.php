<?php
namespace PQD\DBF;
require_once 'Dbf.php';
/**
 *
 * @author Willker Moraes Silva
 * @since 2012-08-01
 *
 */
class DbfToSQL extends Dbf {

	const DB_SQLSERVER = 0;

	const DB_SQLITE = 1;

	const DB_MYSQL = 2;

	const DB_PGSQL = 3;

	/**
	 * @var string
	 */
	private $sql = "";

	/**
	 * @var string
	 */
	private $tableName;

	/**
	 * @var string
	 */
	private $idField;

	/**
	 * @var string
	 */
	private $deleteField;

	/**
	 *
	 * @var int
	 */
	private $db;



	public function __construct($dbf, $tableName, $id = "id", $delete = "deleted", $db = self::DB_SQLSERVER){
		parent::__construct($dbf);

		$this->tableName = $tableName;
		$this->idField = $id;
		$this->deleteField = $delete;
		$this->db = $db;
	}

	public function retSQLTable(){

		$sql = "CREATE TABLE $this->tableName(" . PHP_EOL;

		$virgula = "\t";

		switch ($this->db){
			case self::DB_SQLITE:
				$sql .=  $virgula . "$this->idField INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT";
			break;
			case self::DB_SQLSERVER:
				$sql .=  $virgula . "$this->idField INTEGER NOT NULL PRIMARY KEY IDENTITY";
			break;
			case self::DB_MYSQL:
				$sql .=  $virgula . "$this->idField INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT";
			break;
			case self::DB_PGSQL:
				$sql .=  $virgula . "$this->idField SERIAL";
			break;
		}

		$virgula = ',' . PHP_EOL . "\t";
		for ($i = 0; $i < $this->getNumFields(); $i++) {

			//Caracter
			if($this->header[$i]['type'] == 'character' || $this->header[$i]['type'] == 'date'){
				$sql .= $virgula . $this->header[$i]['name'] . ' VARCHAR(' . $this->header[$i]['length'] . ')';
			}

			//Int
			else if($this->header[$i]['type'] == 'number' && $this->header[$i]['precision'] == 0){
				$sql .= $virgula . $this->header[$i]['name'] . ' INTEGER ';
			}

			//Float
			else if($this->header[$i]['type'] == 'number' && $this->header[$i]['precision'] != 0){
				$sql .= $virgula . $this->header[$i]['name'] . ' REAL';
			}

			//Logical
			else if($this->header[$i]['type'] == 'boolean'){
				$sql .= $virgula . $this->header[$i]['name'] . ' BIT';
			}
		}

		$sql .=  $virgula . "$this->deleteField BIT NOT NULL DEFAULT 0";
		$sql .= PHP_EOL . " );" . PHP_EOL;
		$sql .= "CREATE INDEX {$this->tableName}_$this->idField ON $this->tableName ($this->idField);" . PHP_EOL;

		return $sql;
	}

	public function generateTable(){

		$this->sql .= $this->retSQLTable();

		return $this;
	}

	public function retSQLRecord($numRecord){

		$data = $this->getRecord($numRecord, Dbf::FETCH_NUM);
		$deleted = array_pop($data);

		$data = array_map("trim", $data);

		$sql = "INSERT INTO " . $this->tableName . "( ";
		for ($j = 0; $j < $this->getNumFields(); $j++)
			$sql .= $this->header[$j]['name'] . ", ";

		$sql .= "$this->deleteField) VALUES ( ";

		foreach ($data as $key => $value){
			if ($this->header[$key]['type'] == 'character' || $this->header[$key]['type'] == 'date')//Caracter
				$sql .= "'" . str_replace("'", "''", $value) . "', ";
			else if ($this->header[$key]['type'] == 'number' || $this->header[$key]['type'] == 'boolean')
				$sql .= $value . ", ";
		}

		$sql .= "$deleted);" . PHP_EOL;

		return $sql;
	}

	/**
	 *
	 * @param int $top
	 */
	public function generateRecords($top = 0){

		for ($i = 1; $i <= $this->getNumRecords(); $i++) {
			$this->sql .= $this->retSQLRecord($i);

			if($i == $top)
				break;
		}

		return $this;
	}

	/**
	 * @param bool $records
	 * @param int $top
	 */
	public function generateSql($records = true, $top = 0){
		$this->generateTable();

		if($records)
			$this->generateRecords($top);

		return $this;
	}

	/**
	 * @return the $sql
	 */
	public function getSql() {
		return $this->sql;
	}

	/**
	 * @return the $tableName
	 */
	public function getTableName() {
		return $this->tableName;
	}

	/**
	 * @return the $idField
	 */
	public function getIdField() {
		return $this->idField;
	}

	/**
	 * @return the $deleteField
	 */
	public function getDeleteField() {
		return $this->deleteField;
	}

	/**
	 * @param string $tableName
	 */
	public function setTableName($tableName) {
		$this->tableName = $tableName;
	}

	/**
	 * @param string $idField
	 */
	public function setIdField($idField) {
		$this->idField = $idField;
	}

	/**
	 * @param string $deleteField
	 */
	public function setDeleteField($deleteField) {
		$this->deleteField = $deleteField;
	}

	/**
	 * @return the $db
	 */
	public function getDb() {
		return $this->db;
	}

	/**
	 * @param number $db
	 */
	public function setDb($db) {
		$this->db = $db;
	}

}