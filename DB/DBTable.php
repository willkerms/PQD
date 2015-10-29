<?php
namespace PQD\DB;

require_once 'DBColumn.php';

/**
 *
 * @author Willker Moraes Silva
 * @since 2012-08-06
 *
 */
class DBTable {

	/**
	 *
	 * @var string
	 */
	private $tableName;

	/**
	 *
	 * @var string
	 */
	private $dbName;

	/**
	 *
	 * @var array[DBColumn]
	 */
	private $pk = array();

	/**
	 *
	 * @var array[DBColumn]
	 */
	private $columns = array();

	/**
	 *
	 * @param string $tableName
	 * @param string $dbName
	 */
	public function __construct($tableName, $dbName) {
		$this->setTableName($tableName);
		$this->setDbName($dbName);
	}

	/**
	 * @return the $tableName
	 */
	public function getTableName() {
		return $this->tableName;
	}

	/**
	 * @return the $dbName
	 */
	public function getDbName() {
		return $this->dbName;
	}

	/**
	 * @return the $pk
	 */
	public function getPk() {
		return $this->pk;
	}

	/**
	 * @return the $columns
	 */
	public function getColumns() {
		return $this->columns;
	}

	/**
	 * @param string $tableName
	 */
	public function setTableName($tableName) {
		$this->tableName = $tableName;
	}

	/**
	 * @param string $dbName
	 */
	public function setDbName($dbName) {
		$this->dbName = $dbName;
	}

	/**
	 * @param DBColumn  $pk
	 */
	public function setPk(DBColumn $pk) {
		$this->pk[] = $pk;
	}

	/**
	 * @param DBColumn  $column
	 */
	public function setColumn(DBColumn $column) {
		$this->columns[] = $column;

		if ($column->getIsPk() == true)
			$this->setPk($column);
	}

	/**
	 * @param array[DBColumn]  $columns
	 */
	public function setColumns(array $columns) {
		$this->columns = $columns;

		for ($i = 0; $i < count($this->columns); $i++) {
			if ($this->columns[$i]->getIsPk() == true)
				$this->setPk($this->columns[$i]);
		}
	}
}