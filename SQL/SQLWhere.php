<?php
namespace PQD\SQL;

use PQD\PQDUtil;
/**
 *
 * @author Willker Moraes Sivla
 * @since 2012-08-08
 *
 */
class SQLWhere {

	const IN = ' IN(#)';
	const NOT_IN = ' NOT IN(#)';

	const LIKE_LEFT 	= " LIKE '%#'";
	const LIKE_RIGHT 	= " LIKE '#%'";
	const LIKE 			= " LIKE '%#%'";

	const NOT_LIKE_LEFT 	= " NOT LIKE '%#'";
	const NOT_LIKE_RIGHT 	= " NOT LIKE '#%'";
	const NOT_LIKE 			= " NOT LIKE '%#%'";

	const BETWEEN			= " BETWEEN #";
	const NOT_BETWEEN		= " NOT BETWEEN #";

	const IS			= " IS ";
	const IS_NOT		= " IS NOT ";

	const STRING = "'#'";
	const NUMBER = "#";

	private $filters = array();
	private $orderBy = "";

	/**
	 * Add AND on WHERE
	 * @return SQLWhere
	 */
	public function setAnd(){
		array_push($this->filters, 'AND');
		return $this;
	}

	/**
	 * Add OR on WHERE
	 * @return SQLWhere
	 */
	public function setOr(){
		array_push($this->filters, 'OR');
		return $this;
	}

	/**
	 * Add Parentheses on WHERE
	 * @param boolean $close
	 * @return SQLWhere
	 */
	public function setParentheses($close = false){
		array_push($this->filters, $close === false ? '(': ')');
		return $this;
	}

	/**
	 * Add one LIKE on WHERE
	 *
	 * @param string $field
	 * @param string $value
	 * @param string $typeLike
	 * @return SQLWhere
	 */
	public function setLike($field, $value, $typeLike = self::LIKE){
		$value = PQDUtil::escapeSQL($value);
		array_push($this->filters, $field . str_replace('#', $value, $typeLike));
		return $this;
	}

	/**
	 * Add one = on WHERE
	 *
	 * @param string $field
	 * @param string $value
	 * @param string $type
	 * @return SQLWhere
	 */
	public function setEqual($field, $value, $type = self::STRING){
		$value = PQDUtil::escapeSQL($value);
		array_push($this->filters, $field . " = " . str_replace('#', $value, $type));
		return $this;
	}

	/**
	 * Add one <> on WHERE
	 *
	 * @param string $field
	 * @param string $value
	 * @param string $type
	 * @return SQLWhere
	 */
	public function setDiff($field, $value, $type = self::STRING){
		$value = PQDUtil::escapeSQL($value);
		array_push($this->filters, $field . " <> " . str_replace('#', $value, $type));
		return $this;
	}

	/**
	 * Add one > on WHERE
	 *
	 * @param string $field
	 * @param string $value
	 * @param string $type
	 * @return SQLWhere
	 */
	public function setMore($field, $value, $type = self::STRING){
		$value = PQDUtil::escapeSQL($value);
		array_push($this->filters, $field . " > " . str_replace('#', $value, $type));
		return $this;
	}

	/**
	 * Add one >= on WHERE
	 *
	 * @param string $field
	 * @param string $value
	 * @param string $type
	 * @return SQLWhere
	 */
	public function setMoreEqual($field, $value, $type = self::STRING){
		$value = PQDUtil::escapeSQL($value);
		array_push($this->filters, $field . " >= " . str_replace('#', $value, $type));
		return $this;
	}

	/**
	 * Add one < on WHERE
	 *
	 * @param string $field
	 * @param string $value
	 * @param string $type
	 * @return SQLWhere
	 */
	public function setLess($field, $value, $type = self::STRING){
		$value = PQDUtil::escapeSQL($value);
		array_push($this->filters, $field . " < " . str_replace('#', $value, $type));
		return $this;
	}

	/**
	 * Add one <= on WHERE
	 *
	 * @param string $field
	 * @param string $value
	 * @param string $type
	 * @return SQLWhere
	 */
	public function setLessEqual($field, $value, $type = self::STRING){
		$value = PQDUtil::escapeSQL($value);
		array_push($this->filters, $field . " <= " . str_replace('#', $value, $type));
		return $this;
	}

	/**
	 * Add one IN on WHERE
	 *
	 * @param string $field
	 * @param array $value
	 * @param string $type
	 * @return SQLWhere
	 */
	public function setIn($field, array $value, $type = self::IN){
		$value = PQDUtil::escapeSQL($value);
		array_push($this->filters, $field . str_replace('#', join(",", $value), $type));
		return $this;
	}

	/**
	 * Add one BETWEEN on WHERE
	 *
	 * @param string $field
	 * @param array $value
	 * @param string $type
	 * @return SQLWhere
	 */
	public function setBetween($field, array $value, $type = self::BETWEEN){
		$value = PQDUtil::escapeSQL($value);
		array_push($this->filters, $field . str_replace('#', join(" AND ", $value), $type));
		return $this;
	}

	/**
	 * Add one "is null" on WHERE
	 *
	 * @param string $field
	 * @param string $type
	 * @return SQLWhere
	 */
	public function setIsNull($field, $type = self::IS){
		array_push($this->filters, $field . $type . "NULL");
		return $this;
	}

	/**
	 * Add one SQL clause on WHERE
	 * @param string $sql
	 * @return SQLWhere
	 */
	public function setSQL($sql){
		array_push($this->filters, $sql);
		return $this;
	}

	/**
	 * Add one ORDER BY after WHERE
	 *
	 * @param array $fields
	 * @param boolean $asc
	 * @return SQLWhere
	 */
	public function setOrderBy(array $fields, $asc = true){
		$this->orderBy = " ORDER BY " . join(", ", $fields) . ($asc === false ? ' DESC' : ' ASC');
		return $this;
	}

	/**
	 * Clean the content of where
	 * @return SQLWhere
	 */
	public function cleanWhere(){
		$this->filters = array();
		$this->orderBy = "";
		return $this;
	}

	/**
	 * Return the WHERE clause
	 *
	 * @param boolean $where
	 * @return string
	 */
	public function getWhere($where = false){
		$sqlWhere = "";

		if (count($this->filters) > 0)
			$sqlWhere = ($where === true ? "WHERE " : "") . join(" ", $this->filters);

		if (!empty($this->orderBy))
			$sqlWhere .= $this->orderBy;

		return $sqlWhere;
	}
}