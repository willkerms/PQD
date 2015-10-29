<?php
namespace PQD\DB;
/**
 *
 * @author Willker Moraes Silva
 * @since 2012-08-06
 *
 */
class DBColumn {

	/**
	 *
	 * @var DBTable
	 */
	private $dbTable;

	/**
	 *
	 * @var string
	 */
	private $fieldName;

	/**
	 *
	 * @var int
	 */
	private $ordinalPosition;

	/**
	 *
	 * @var boolean
	 */
	private $isNull;

	/**
	 *
	 * @var string
	 */
	private $dataType;

	/**
	 *
	 * @var int
	 */
	private $length;

	/**
	 *
	 * @var int
	 */
	private $precision;

	/**
	 *
	 * @var int
	 */
	private $precisionRadix;

	/**
	 *
	 * @var boolean
	 */
	private $isPk;

	function __construct(DBTable $dbTable, array $data = null){

		$this->dbTable = $dbTable;

		if (!is_null($data)) {
			foreach ($data as $key => $value){
				if (property_exists(get_class($this), $key))
					$this->{"set".ucwords($key)}($value);
			}
		}
	}

	/**
	 * @return the $table
	 */
	public function getTable() {
		return $this->table;
	}

	/**
	 * @return the $fieldName
	 */
	public function getFieldName() {
		return $this->fieldName;
	}

	/**
	 * @return the $ordinalPosition
	 */
	public function getOrdinalPosition() {
		return $this->ordinalPosition;
	}

	/**
	 * @return the $isNull
	 */
	public function getIsNull() {
		return $this->isNull;
	}

	/**
	 * @return the $dataType
	 */
	public function getDataType() {
		return $this->dataType;
	}

	/**
	 * @return the $length
	 */
	public function getLength() {
		return $this->length;
	}

	/**
	 * @return the $precision
	 */
	public function getPrecision() {
		return $this->precision;
	}

	/**
	 * @return the $precisionRadix
	 */
	public function getPrecisionRadix() {
		return $this->precisionRadix;
	}

	/**
	 * @return the $isPk
	 */
	public function getIsPk() {
		return $this->isPk;
	}

	/**
	 * @param string $table
	 */
	public function setTable($table) {
		$this->table = $table;
	}

	/**
	 * @param string $fieldName
	 */
	public function setFieldName($fieldName) {
		$this->fieldName = $fieldName;
	}

	/**
	 * @param number $ordinalPosition
	 */
	public function setOrdinalPosition($ordinalPosition) {
		$this->ordinalPosition = $ordinalPosition;
	}

	/**
	 * @param boolean $isNull
	 */
	public function setIsNull($isNull) {
		$this->isNull = $isNull;
	}

	/**
	 * @param string $dataType
	 */
	public function setDataType($dataType) {
		$this->dataType = $dataType;
	}

	/**
	 * @param number $length
	 */
	public function setLength($length) {
		$this->length = $length;
	}

	/**
	 * @param number $precision
	 */
	public function setPrecision($precision) {
		$this->precision = $precision;
	}

	/**
	 * @param number $precisionRadix
	 */
	public function setPrecisionRadix($precisionRadix) {
		$this->precisionRadix = $precisionRadix;
	}

	/**
	 * @param boolean $isPk
	 */
	public function setIsPk($isPk) {
		$this->isPk = $isPk;
	}
}