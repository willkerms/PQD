<?php

namespace PQD\VIEW;

use PQD\PQDExceptions;
use PQD\PQDDb;

require_once 'ViewAttr.php';
require_once 'ViewScript.php';
require_once 'ViewTemplate.php';

class View extends ViewAttr {
	
	/**
	 * 
	 * @var array[ViewScript]
	 */
	private $scripts = array();
	
	/**
	 * 
	 * @var array[ViewTemplate]
	 */
	private $templates = array();
	
	/**
	 * 
	 * @var array[Field]
	 */
	private $fields = array();
	
	/**
	 * 
	 * @var Field
	 */
	private $parent;
	
	/**
	 * 
	 * @var array
	 */
	private $aObjs;
	
	/**
	 * 
	 * @var boolean
	 */
	private $preview = false;
	
	/**
	 * 
	 * @var string
	 */
	private $script = "";
	
	/**
	 * @return Field $parent
	 */
	public function getParent() {
		return $this->parent;
	}
	
	/**
	 * @return array $aObjs
	 */
	public function getAObjs() {
		return $this->aObjs;
	}

	/**
	 * @return the $scripts
	 */
	public function getScripts() {
		return $this->scripts;
	}

	/**
	 * @return the $templates
	 */
	public function getTemplates() {
		return $this->templates;
	}

	/**
	 * @return the $fields
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * @param boolean $preview
	 */
	public function setPreview($preview) {
		$this->preview = $preview;
	}
	
	/**
	 * @return boolean
	 */
	public function getPreview() {
		return $this->preview;
	}

	public function addScript($script) {
		return $this->script .= $script;
	}

	/**
	 * @param \PDO $connection
	 */
	public function setScripts(\PDO $connection) {
		$this->scripts = array();
		
		$sql = "SELECT * FROM pqd_viewScripts WHERE idPqdView = :idPqdView";
		$sth = $connection->prepare($sql);
		$sth->bindValue(":idPqdView", $this->getIdPqdView(), \PDO::PARAM_INT);
		
		if ($sth->execute())
			$this->scripts = $sth->fetchAll(\PDO::FETCH_CLASS, 'PQD\VIEW\ViewScript');
		else{
			$error = $sth->errorInfo();
			throw new \Exception($error[2], $error[1]);
		}
	}

	/**
	 * @param \PDO $connection
	 */
	public function setTemplates(\PDO $connection) {
		$this->templates = array();

		$sql = "SELECT * FROM pqd_viewTemplates WHERE idPqdView = :idPqdView";
		$sth = $connection->prepare($sql);
		$sth->bindValue(":idPqdView", $this->getIdPqdView(), \PDO::PARAM_INT);
		
		if ($sth->execute())
			$this->templates = $sth->fetchAll(\PDO::FETCH_CLASS, 'PQD\VIEW\ViewTemplate');
		else{
			$error = $sth->errorInfo();
			throw new \Exception($error[2], $error[1]);
		}
	}

	/**
	 * @param \PDO $connection
	 */
	public function setFields(\PDO $connection) {
		
		$this->fields = array();
		
		//Mysql
		$sqlMysql = "SELECT * FROM pqd_viewFields WHERE idPqdView = :idPqdView and idPqdViewFieldParent is null order by IFNULL(ordem, 1000), idPqdViewField";

		//SQL Server
		$sqlServer = "SELECT * FROM pqd_viewFields WHERE idPqdView = :idPqdView and idPqdViewFieldParent is null order by ISNULL(ordem, 1000), idPqdViewField";
		
		if($connection->getAttribute(\PDO::ATTR_DRIVER_NAME) == "mysql")
			$sth = $connection->prepare($sqlMysql);
		else
			$sth = $connection->prepare($sqlServer);
		
		$sth->bindValue(":idPqdView", $this->getIdPqdView(), \PDO::PARAM_INT);
		
		$sth->setFetchMode( \PDO::FETCH_CLASS, 'PQD\VIEW\Field', array($this));
		if ($sth->execute()) {
			while (($field = $sth->fetch(\PDO::FETCH_CLASS)) !== false) {
				//FIXME: não funciona no php 5.5 no Linux, verificar pq, foi adicionado o código abaixo.
				//$field->load($connection);
				$this->fields[] = $field;
			}
			foreach ($this->fields as $field)
				$field->load($connection);
		}
		else{
			$error = $sth->errorInfo();
			throw new \Exception($error[2], $error[1]);
		}
	}
	
	public function __construct($idOrSigla, array $aObjs = null, Field $parent = null, \PDO $connection = null){
		
		$this->parent = $parent;
		$this->aObjs = $aObjs;
		
		$connection = !is_null($connection) ? $connection : (new PQDDb(new PQDExceptions()))->getConnection();
		
		if (is_numeric($idOrSigla)) {
			$sql = "SELECT * FROM pqd_view WHERE idPqdView = :idPqdView";
			$sth = $connection->prepare($sql);
			$sth->bindParam(":idPqdView", $idOrSigla, \PDO::PARAM_INT);
		}
		else{
			$sql = "SELECT * FROM pqd_view WHERE sigla = :sigla";
			$sth = $connection->prepare($sql);
			$sth->bindParam(":sigla", $idOrSigla, \PDO::PARAM_STR);
		}
		
		if ($sth->execute()) {
			
			if(($result = $sth->fetch(\PDO::FETCH_NAMED)) !== false){
				
				$this->setIdPqdView($result['idPqdView']);
				$this->setSigla($result['sigla']);
				$this->setNamespace($result['namespace']);
				$this->setDescricao($result['descricao']);
				$this->setTabela($result['tabela']);
				
				$this->setScripts($connection);
				$this->setFields($connection);
				$this->setTemplates($connection);
			}
		}
		else{
			$error = $sth->errorInfo();
			throw new \Exception($error[2], $error[1]);
		}
	}
	
	public function json(){
		$array = parent::toArray();
		
		$ret = "{";
		foreach($array as $key => $value)
			$ret .= $key . ":" .json_encode($value) . ",";
		
		$ret .= "fields:[";
		for ($i = 0; $i< count($this->fields); $i++)
			$ret .= ($i==0 ? '': ',') . $this->fields[$i]->json();
		
		return $ret . "]};";
	}
		
	function __toString(){
		$ret = "";
		
		for ($i = 0; $i< count($this->fields); $i++)
			$ret .= $this->fields[$i];
		
		if ($this->script != "") {
			$ret .= str_repeat((IS_DEVELOPMENT ? "\t" : ""), Field::$tab) . '<script type="text/javascript">' . (IS_DEVELOPMENT ? PHP_EOL : "");
			$ret .= $this->script;
			$ret .= str_repeat((IS_DEVELOPMENT ? "\t" : ""), Field::$tab) . '</script>' . (IS_DEVELOPMENT ? PHP_EOL : "");
		}
		
		return $ret;
	}	
}