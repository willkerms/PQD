<?php
namespace PQD;
/**
 *
 * @author Willker Moraes Silva
 * @since 2012-08-10
 *
 */
class PQDEntity {

	private static $methodsOfTheClass = array();

	/**
	 * Escape the Properties to HTML format
	 */
	public function escapeHTML(){

		$oReflection = new \ReflectionObject($this);
		$aMethods = $oReflection->getMethods();

		foreach ($aMethods as $oReflectionMethod ){
			if (substr($oReflectionMethod->name, 0, 3) == "set" && method_exists($this, "get" . substr($oReflectionMethod->name, 3)))
				$this->{$oReflectionMethod->name}(htmlentities($this->{"get" . substr($oReflectionMethod->name, 3)}()));
		}
	}

	/**
	 * Escape the Properties to SQL format
	 */
	public function escapeSQL(){

		$oReflection = new \ReflectionObject($this);
		$aMethods = $oReflection->getMethods();

		foreach ($aMethods as $oReflectionMethod ){
			if (substr($oReflectionMethod->name, 0, 3) == "set" && method_exists($this, "get" . substr($oReflectionMethod->name, 3))){

				if (is_null($this->{"get" . substr($oReflectionMethod->name, 3)}()) || trim($this->{"get" . substr($oReflectionMethod->name, 3)}()) === "")
					$this->{$oReflectionMethod->name}("NULL");
				else if(preg_match('/@param[^\$]*/', $oReflectionMethod->getDocComment(), $matches)){
					$dataType = strtolower(trim(substr($matches[0], 6)));

					if($dataType == "string")
						$this->{$oReflectionMethod->name}("'" . trim(str_replace("'", "''", $this->{"get" . substr($oReflectionMethod->name, 3)}())) . "'");
					else if ($dataType == "bool" || $dataType == "boolean")
						$this->{$oReflectionMethod->name}((int)$this->{"get" . substr($oReflectionMethod->name, 3)}());
				}
			}
		}
	}

	/**
	 *
	 * @param mixed $data
	 */
	public function __construct($data = null) {
		if (!is_null($data)) {

			$class = get_class($this);
			if(!isset(self::$methodsOfTheClass[$class])){

				$oReflection = new \ReflectionObject($this);
				$aReflectionMethods = $oReflection->getMethods();

				$aMethods = array();
				foreach ($aReflectionMethods as $oReflectionMethod )
					$aMethods[$oReflectionMethod->name] = true;

				self::$methodsOfTheClass[$class] = $aMethods;
			}
			else
				$aMethods = self::$methodsOfTheClass[$class];

			foreach ($data as $key => $value){

				$field = preg_split('/_/', $key);

				if (count($field) > 3)
					$field = ucwords(strtolower(join("", $field)));
				else{
					foreach ($field as $keyField => $valueField)
						$field[$keyField] = ucwords(strtolower($valueField));

					$field = join("", $field);
				}

				if (isset($aMethods["set".$field]))
					$this->{"set".$field}($value);
			}
		}
	}
}