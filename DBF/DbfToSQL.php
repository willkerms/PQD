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

	public function generateTable(){
		$this->sql .= "CREATE TABLE $this->tableName(" . PHP_EOL;

		$virgula = "\t";

		switch ($this->db){
			case self::DB_SQLITE:
				$this->sql .=  $virgula . "$this->idField INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT";
			break;
			case self::DB_SQLSERVER:
				$this->sql .=  $virgula . "$this->idField INTEGER NOT NULL PRIMARY KEY IDENTITY";
			break;
			case self::DB_MYSQL:
				$this->sql .=  $virgula . "$this->idField INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT";
			break;
			case self::DB_PGSQL:
				$this->sql .=  $virgula . "$this->idField SERIAL";
			break;
		}

		$virgula = ',' . PHP_EOL . "\t";
		for ($i = 0; $i < $this->getNumFields(); $i++) {

			//Caracter
			if($this->header[$i]['type'] == 'character' || $this->header[$i]['type'] == 'date'){
				$this->sql .= $virgula . $this->header[$i]['name'] . ' VARCHAR(' . $this->header[$i]['length'] . ')';
			}

			//Int
			else if($this->header[$i]['type'] == 'number' && $this->header[$i]['precision'] == 0){
				$this->sql .= $virgula . $this->header[$i]['name'] . ' INTEGER ';
			}

			//Float
			else if($this->header[$i]['type'] == 'number' && $this->header[$i]['precision'] != 0){
				$this->sql .= $virgula . $this->header[$i]['name'] . ' REAL';
			}

			//Logical
			else if($this->header[$i]['type'] == 'boolean'){
				$this->sql .= $virgula . $this->header[$i]['name'] . ' BIT';
			}
		}

		$this->sql .=  $virgula . "$this->deleteField BIT NOT NULL DEFAULT 0";
		$this->sql .= PHP_EOL . " );" . PHP_EOL;
		$this->sql .= "CREATE INDEX {$this->tableName}_$this->idField ON $this->tableName ($this->idField);" . PHP_EOL;

		return $this;
	}

	/**
	 *
	 * @param int $top
	 */
	public function generateRecords($top = 0){

		for ($i = 1; $i <= $this->getNumRecords(); $i++) {

			$data = $this->getRecord($i, Dbf::FETCH_NUM);
			$deleted = array_pop($data);

			$data = array_map("trim", $data);

			$this->sql .= "INSERT INTO " . $this->tableName . "( ";
			for ($j = 0; $j < $this->getNumFields(); $j++)

				$this->sql .= $this->header[$j]['name'] . ", ";
				$this->sql .= "$this->deleteField) VALUES ( ";

				foreach ($data as $key => $value){
					if ($this->header[$key]['type'] == 'character' || $this->header[$key]['type'] == 'date')//Caracter
						$this->sql .= "'" . str_replace("'", "''", $value) . "', ";
					else if ($this->header[$key]['type'] == 'number' || $this->header[$key]['type'] == 'boolean')
						$this->sql .= $value . ", ";
				}

				$this->sql .= "$deleted);" . PHP_EOL;

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