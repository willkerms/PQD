<?php
namespace PQD\DBF;
require_once 'DbfToSQL.php';
/**
 *
 * @author Willker Moraes Silva
 * @since 2012-08-01
 *
 */
class DbfToSQLite{

	/**
	 * @var PDOStatement
	 */
	private $sqliteDb;

	/**
	 *
	 * @var array[DbfToSQL]
	 */
	private $dbfs = array();

	/**
	 *
	 * @param string $SQLiteFile
	 */
	public function __construct($SQLiteFile) {
		$this->sqliteDb = new PDO('sqlite:' . $SQLiteFile);
	}

	/**
	 * @param bool $records
	 * @param int $top
	 * @return DbfToSQLite
	 */
	public function toSQLite($records = true, $top = 0){
		if (count($this->dbfs) > 0) {
			foreach ($this->dbfs as $oDbfToSql){
				$oDbfToSql->setDb(DbfToSQL::DB_SQLITE);
				$this->sqliteDb->exec($oDbfToSql->generateSql($records, $top)->getSql());
			}
		}
		else
			throw new Exception("None dbf set");

		return $this;
	}

	/**
	 * @return the $dbfs
	 */
	public function getDbfs() {
		return $this->dbfs;
	}

	/**
	 * @param array $dbfs
	 *
	 */
	public function setDbfs(array $dbfs) {
		$this->dbfs = $dbfs;

		return $this;
	}

	/**
	 * @param string $dbf
	 * @param string $table
	 *
	 */
	public function setDbf($dbf, $tableName, $id = "id", $delete = "deleted") {
		$this->dbfs[] = new DbfToSQL($dbf, $tableName, $id, $delete, DbfToSQL::DB_SQLITE);

		return $this;
	}
}