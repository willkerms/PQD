<?php
namespace PQD;

/**
 * Classe para pegar as anotações das entidades
 * 
 * @author Willker Moraes Silva
 * @since 2015-11-13
 *
 */
class PQDAnnotation{
	
	private static $annotation = array();
	
	private $class;
	
	/**
	 * @param string $classFile
	 */
	function __construct($classFile) {
		
		$this->class = $classFile;
		
		if(!is_file($classFile)){
			PQDApp::getApp()->getExceptions()->setException(new \Exception("Classe não encontrada!", 11) );
			self::$annotation[$this->class] = array();
		}
	}
	/**
	 * Retorna valores dentro dos parenteses 
	 * 
	 * @param string $nameAttr
	 * @param string $str
	 * @return array
	 */
	private function retValues($nameAttr, $str){
		
		$annotations = explode(", ", substr($str, strlen($nameAttr)+1, -1));
		$col = array();
		
		foreach ($annotations as $description){
			$values = explode("=", $description);
			if(count($values) == 2)
				$col[$values[0]] = $values[1];
			else
				$col[] = $values[0];
		}
		
		return $col;
	}
	/**
	 * retorna nome dá coluna chave primaria
	 * 
	 * @return string
	 */
	public function getPk(){
		if(isset(self::$annotation[$this->class]['pk']))
			return self::$annotation[$this->class]['pk'];
		else{
			$this->getFields();
			return self::$annotation[$this->class]['pk'];
		}
	}
	/**
	 * retorna os filtros
	 * 
	 * @return array
	 */
	public function getFilters(){
		if(isset(self::$annotation[$this->class]['filters']))
			return self::$annotation[$this->class]['filters'];
		else{
			$this->getFields();
			return self::$annotation[$this->class]['filters'];
		}
	}
	/**
	 * retorna FK's
	 * 
	 * @return string
	 */
	public function getFks(){
		if(isset(self::$annotation[$this->class]['fks']))
			return self::$annotation[$this->class]['fks'];
		else{
			$this->getFields();
			return self::$annotation[$this->class]['fks'];
		}
	}
	
	/**
	 * retorna valores setados no na anotação @table
	 * 
	 * @return array
	 */
	public function getTable(){
			
		if(isset(self::$annotation[$this->class]['table']))
			return self::$annotation[$this->class]['table'];
		else{
			self::$annotation[$this->class]['table'] = array();
			
			preg_match('/\@table\([^)]*\)/', file_get_contents($this->class), $matches);
				
			if(isset($matches[0])){
				
				$values = $this->retValues('@table', $matches[0]);
				$table = isset($values[0]) ? array('name' => $values[0]) : $values;
				
				self::$annotation[$this->class]['table'] = $table;
			}
				
			return self::$annotation[$this->class]['table'];
		}
	}
	
	/**
	 * retorna os campos anotados na anotação @field
	 * 
	 * @return array
	 */
	public function getFields(){
		
		if(isset(self::$annotation[$this->class]['fields']))
			return self::$annotation[$this->class]['fields'];
		else{
			self::$annotation[$this->class]['fields'] = array();
			self::$annotation[$this->class]['pk'] = null;
			self::$annotation[$this->class]['fks'] = array();
			self::$annotation[$this->class]['filters'] = array();
			
			preg_match_all('/\@field\([^)]*\)/', file_get_contents($this->class), $matches);
			
			if(isset($matches[0])){
				
				$fields = array();
				$pk = null;
				foreach($matches[0] as $key => $field){
					
					$col = $this->retValues('@field', $field);
					
					if(isset($col['name']))
						$fields[$col['name']] = $col;
					else
						$fields[] = $col;
					
					if(isset($col['isPk']) && $col['isPk'] == 'true')
						$pk = $col['name'];
					
					if(isset($col['isFilter']) && $col['isFilter'] == 'true')
						self::$annotation[$this->class]['filters'][] = $col;
					
					if(isset($col['fk']))
						self::$annotation[$this->class]['fks'][] = array($col['name'] => $col['fk']);
				}
				
				self::$annotation[$this->class]['fields'] = $fields;
				self::$annotation[$this->class]['pk'] = $pk;
			}
			
			return self::$annotation[$this->class]['fields'];
		}
	}
}