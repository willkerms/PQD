<?php
namespace PQD\SQL;

use PQD\PQDUtil;
/**
 *
 *
 * 2015-12-10 Alteração dos filtros para contemplar os fields e values para fazer joins
 *
 * @author Willker Moraes Sivla
 * @since 2012-08-08
 *
 */
class SQLWhere {

	const IN = ' IN(#)';
	const IN_STR = " IN('#')";
	const NOT_IN = ' NOT IN(#)';
	const NOT_IN_STR = " NOT IN('#')";

	const LIKE_LEFT 	= " LIKE '%#'";
	const LIKE_RIGHT 	= " LIKE '#%'";
	const LIKE 			= " LIKE '%#%'";
	const LIKE_NONE		= " LIKE '#'";

	const NOT_LIKE_LEFT 	= " NOT LIKE '%#'";
	const NOT_LIKE_RIGHT 	= " NOT LIKE '#%'";
	const NOT_LIKE 			= " NOT LIKE '%#%'";
	const NOT_LIKE_NONE 			= " NOT LIKE '#'";

	const BETWEEN			= " BETWEEN #";
	const NOT_BETWEEN		= " NOT BETWEEN #";

	const BETWEEN_STR			= " BETWEEN '#'";
	const NOT_BETWEEN_STR		= " NOT BETWEEN '#'";

	const IS			= " IS ";
	const IS_NOT		= " IS NOT ";

	const STRING = "'#'";
	const NUMBER = "#";

	private $filters = array();

	private $alias = null;

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
		$value = PQDUtil::escapeSQL(str_replace('*', '%', $value));
		//array_push($this->filters, $field . str_replace('#', $value, $typeLike));
		array_push($this->filters, array('field' => $field, 'value' => str_replace('#', $value, $typeLike)));
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
		//array_push($this->filters, $field . " = " . str_replace('#', $value, $type));
		$value = is_null($value) && $type == self::NUMBER ? 'NULL': $value;
		array_push($this->filters, array('field' => $field, 'value' => " = " . str_replace('#', $value, $type)));
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
		//array_push($this->filters, $field . " <> " . str_replace('#', $value, $type));
		$value = is_null($value) && $type == self::NUMBER ? 'NULL': $value;
		array_push($this->filters, array('field' => $field, 'value' => " <> " . str_replace('#', $value, $type)));
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
		//array_push($this->filters, $field . " > " . str_replace('#', $value, $type));
		$value = is_null($value) && $type == self::NUMBER ? 'NULL': $value;
		array_push($this->filters, array('field' => $field, 'value' => " > " . str_replace('#', $value, $type)));
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
		//array_push($this->filters, $field . " >= " . str_replace('#', $value, $type));
		$value = is_null($value) && $type == self::NUMBER ? 'NULL': $value;
		array_push($this->filters, array('field' => $field, 'value' => " >= " . str_replace('#', $value, $type)));
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
		//array_push($this->filters, $field . " < " . str_replace('#', $value, $type));
		$value = is_null($value) && $type == self::NUMBER ? 'NULL': $value;
		array_push($this->filters, array('field' => $field, 'value' => " < " . str_replace('#', $value, $type)));
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
		//array_push($this->filters, $field . " <= " . str_replace('#', $value, $type));
		$value = is_null($value) && $type == self::NUMBER ? 'NULL': $value;
		array_push($this->filters, array('field' => $field, 'value' => " <= " . str_replace('#', $value, $type)));
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

		if (count($value) > 0){
			$value = PQDUtil::escapeSQL($value);
			//array_push($this->filters, $field . str_replace('#', join(",", $value), $type));
			array_push($this->filters, array('field' => $field, 'value' => str_replace('#', join($type == self::IN_STR || $type == self::NOT_IN_STR ? "', '": ",", $value), $type)));
		}

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
		//array_push($this->filters, $field . str_replace('#', join(" AND ", $value), $type));
		if ($type == self::BETWEEN_STR || $type == self::NOT_BETWEEN_STR)
			array_push($this->filters, array('field' => $field, 'value' => str_replace('#', join("' AND '", $value), $type)));
		else
			array_push($this->filters, array('field' => $field, 'value' => str_replace('#', join(" AND ", $value), $type)));

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
		//array_push($this->filters, $field . $type . "NULL");
		array_push($this->filters, array('field' => $field, 'value' => $type . "NULL"));
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
	 * Clean the content of where
	 * @return SQLWhere
	 */
	public function cleanWhere(){
		$this->filters = array();
		return $this;
	}

	/**
	 * @return int
	 */
	public function count(){
		return count($this->filters);
	}

	/**
	 * @return array
	 */
	public function getFilters(){
		return $this->filters;
	}

	public function setAlias($alias){
		$this->alias = $alias;
	}

	public function getAlias(){
		return $this->alias;
	}

	/**
	 *
	 * @param array $filters
	 * @return self
	 */
	public function setFilters(array $filters){
		$this->filters = $filters;
		return $this;
	}

	/**
	 *
	 * @param array|string $filters
	 * @return self
	 */
	public function addFilter($filters){
		array_push($this->filters, $filters);
		return $this;
	}

	/**
	 * Return the WHERE clause
	 *
	 * @param boolean $where
	 * @return string
	 */
	public function getWhere($where = false){

		$sqlWhere = ($where === true && count($this->filters) > 0 ? "WHERE " : "");
		for ($i = 0, $espace = "", $alias = ""; $i < count($this->filters); $i++) {
			if (is_array($this->filters[$i])){
				if(!is_null($this->getAlias())){
					if(preg_match('/^[a-zA-Z]+\.[a-zA-Z_\-]+/', $this->filters[$i]['field']) !== 1)
						$alias = $this->getAlias() . ".";
				}

				$sqlWhere .= $espace . $alias . $this->filters[$i]['field'] . $this->filters[$i]['value'];
			}
			else
				$sqlWhere .= $espace . $this->filters[$i];

			$espace = " ";
			$alias = "";
		}

		return $sqlWhere;

		/*
		$sqlWhere = "";

		if (count($this->filters) > 0)
			$sqlWhere = ($where === true ? "WHERE " : "") . join(" " . PHP_EOL, $this->filters);

		return $sqlWhere;
		*/
	}
}