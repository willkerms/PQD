<?php
namespace PQD\DBF;
/**
 *
 * @author Willker Moraes Silva
 * @since 2012-08-01
 *
 */
class Dbf {

	const MODE_READ_ONLY = 0;
	const MODE_WRITE_ONLY = 1;
	const MODE_READ_WRITE = 2;

	const FETCH_BOTH = 0;
	const FETCH_NAMED = 1;
	const FETCH_NUM = 2;

	private $db;

	/**
	 * @var int
	 */
	private $numFields;

	/**
	 * @var int
	 */
	private $numRecords;

	/**
	 *
	 * @var int
	 */
	private $mode;

	/**
	 * @var string
	 */
	private $dbfFile;

	/**
	 * @var array
	 */
	protected $header;

	/**
	 *
	 * @param string $dbf
	 * @param int $mode
	 */
	function __construct($dbf, $mode = self::MODE_READ_ONLY) {
		$this->db = dbase_open($dbf, $mode);
		$this->mode = $mode;
		$this->dbfFile = $dbf;

		if (!$this->db)
			throw new Exception("Error while open file: " . $dbf);
		else{
			$this->numFields	= dbase_numfields($this->db);
			$this->numRecords	= dbase_numrecords($this->db);
			$this->header		= dbase_get_header_info($this->db);
		}
	}

	private function canWrite(){
		return !($this->mode === self::MODE_READ_ONLY);
	}

	private function exceptionForWrite(){
		if (!$this->canWrite())
			throw new Exception("DBF file open for read only!");
	}

	/**
	 * @param string $filename
	 * @param array $fields
	 * @throws Exception
	 */
	public static function createDbf($filename , array $fields){
		/*
		$fields = array(
			array("date",     "D"),
			array("name",     "C",  50),
			array("age",      "N",   3, 0),
			array("payment",      "N",   5, 2),
			array("email",    "C", 128),
			array("ismember", "L")
		);
		*/
		if (!dbase_create($filename, $fields)){
			trigger_error("Error creating file: " . $filename, E_USER_WARNING);
			return false;
		}
		else
			return true;
	}

	/**
	 * @param int $recordNumber
	 */
	public function deleteRecord($recordNumber) {
		$this->exceptionForWrite();
		return dbase_delete_record ($this->db, $recordNumber);
	}

	/**
	 * @param int $record
	 */
	public function appendRecord(array $record) {
		$this->exceptionForWrite();
		/*
		$fields = array(
			'20120101',
			'Sample Name',
			23,
			1588.88,
			'sample@email.com',
			'T'
		);
		*/
		return dbase_add_record ( $this->db , $record);
	}

	/**
	 * @param array $record
	 * @param int $recordNumber
	 */
	public function updateRecord(array $record, $recordNumber) {
		$this->exceptionForWrite();
		return dbase_replace_record ( $this->db, $record , $recordNumber);
	}

	/**
	 * @param int $recordNumber
	 * @param int $fetchStyle
	 * @throws Exception
	 * @return array
	 */
	public function getRecord($recordNumber, $fetchStyle = self::FETCH_BOTH) {
		$data = array();
		$return = array();

		switch ($fetchStyle) {
			case self::FETCH_BOTH:
				$data = dbase_get_record($this->db, $recordNumber);
				if ($data !== false) {
					$return = array();

					for ($i = 0; $i < count($this->header); $i++){
						$return[] = $data[$i];
						$return[$this->header[$i]['name']] = $data[$i];
					}

					$return[] = $data['deleted'];
					$return['deleted'] = $data['deleted'];
					$data = $return;
				}
			break;

			case self::FETCH_NAMED:
				$data = dbase_get_record_with_names($this->db, $recordNumber);
			break;
			case self::FETCH_NUM:
				$data = dbase_get_record($this->db, $recordNumber);
			break;
			default:
				throw new Exception("Error on fetch style: " . $fetchStyle);
			break;
		}

		if ($data === false)
			trigger_error("Read error on recording number: " . $recordNumber, E_USER_WARNING);

		return $data;
	}

	/**
	 * @param int $fetchStyle
	 * @return array
	 */
	public function fetchAll($fetchStyle = self::FETCH_BOTH) {
		$return = array();
		for ($i = 1; $i <= $this->numRecords; $i++)
			$return[] = $this->getRecord($i, $fetchStyle);

		return $return;
	}

	/**
	 * @return the $numFields
	 */
	public function getNumFields() {
		return $this->numFields;
	}

	/**
	 * @return the $numRecords
	 */
	public function getNumRecords() {
		return $this->numRecords;
	}

	/**
	 * @return the $header
	 */
	public function getHeader() {
		return $this->header;
	}

	/**
	 * @return the $dbfFile
	 */
	public function getDbfFile() {
		return $this->dbfFile;
	}

	function __destruct(){
		dbase_close($this->db);
	}
}