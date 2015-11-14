<?php
namespace PQD;

class PQDAnnotation{
	
	private static $annotation = array();
	
	private $class;
	
	function __construct($classFile) {
		
		$this->class = $classFile;
		
		if(!is_file($classFile)){
			PQDApp::getApp()->getExceptions()->setException(new \Exception("Classe não encontrada!", 11) );
			self::$annotation[$this->class] = array();
		}
	}
	
	public function getFields(){
		
		if(isset(self::$annotation[$this->class]))
			return self::$annotation[$this->class];
		else{
			self::$annotation[$this->class] = array();
			
			preg_match_all('/\@field\([^)]*\)/', file_get_contents($this->class), $matches);
			
			if(isset($matches[0])){
				$fields = array();
				foreach($matches[0] as $key => $field){
					$annotations = explode(", ", substr(str_replace('@field(', "", $field), 0, -1));
					$col = array();
						
					foreach ($annotations as $description){
						list($k, $v) = explode("=", $description);
						$col[$k] = $v;
					}
					
					if(isset($col['name']))
						$fields[$col['name']] = $col;
					else
						$fields[] = $col;
				}
				
				self::$annotation[$this->class] = $fields;
			}
			
			return self::$annotation[$this->class];
		}
	}
}