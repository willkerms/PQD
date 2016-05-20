<?php
namespace PQD;
/**
 * @author Willker Moraes Silva
 * @since 2012-03-30
 */
class PQDView {

	/**
	 * @var string
	 */
	private $view = "";

	/**
	 * @var bool
	 */
	private $autoRender = true;

	/**
	 * @var array
	 */
	private $values = array();

	/**
	 *
	 * @var PQDExceptions
	 */
	private  $exceptions;
	
	/**
	 * 
	 * @var boolean
	 */
	private $requireHeaderAndFooter = true;

	/**
	 * 
	 * @var string
	 */
	private $tplHeader = null;
	
	/**
	 * 
	 * @var string
	 */
	private $tplFooter = null;
	
	/**
	 * @var array $fields
	 */
	private $fields = array();
	
	/**
	 * @param string $view
	 */
	function __construct ($view, PQDExceptions $exceptions = null) {

		$this->setViewFile($view);

		$this->exceptions = $exceptions;
		
		if(defined('APP_TEMPLATE_HEAD'))
			$this->tplHeader = APP_TEMPLATE_HEAD;
		
		if(defined('APP_TEMPLATE_FOOTER'))
			$this->tplFooter =  APP_TEMPLATE_FOOTER;
	}

	private function render(){
		
		if ($this->requireHeaderAndFooter){

			if(!IS_DEVELOPMENT){
				ob_start();
				
				if(!is_null($this->tplHeader) && trim($this->tplHeader) != "" && !IS_CLI)
					require_once $this->tplHeader;
				
				require $this->view;
				
				if(!is_null($this->tplFooter) && trim($this->tplFooter) != "" && !IS_CLI)
					require_once $this->tplFooter;
				
				echo PQDUtil::withoutSpaces(ob_get_clean(), true, true);
			}
			else{
				if(!is_null($this->tplHeader) && trim($this->tplHeader) != "" && !IS_CLI)
					require_once $this->tplHeader;
				
				require $this->view;
				
				if(!is_null($this->tplFooter) && trim($this->tplFooter) != "" && !IS_CLI)
					require_once $this->tplFooter;
			}
		}
		else
			require $this->view;
		
	}

	public function getRender(){
		ob_start();
		$this->render();
		return ob_get_clean();
	}

	/**
	 * @return bool $autoRender
	 */
	public function getAutoRender () {
		return $this->autoRender;
	}

	/**
	 * @param bool $autoRender
	 * @return self
	 */
	public function setAutoRender ($autoRender) {
		$this->autoRender = $autoRender;
		return $this;
	}

	/**
	 * @return the $view
	 */
	public function getView() {
		return $this->view;
	}
	
	/**
	 * @return string $file
	 */
	public function setViewFile($file) {
		$file = str_replace("\\", "/", $file);
		if(is_file($file))
			$this->view = realpath($file);
		else
			throw new \Exception("Erro ao Carregar View: " . $file, 3);
	}

	/**
	 * @return PQDExceptions $exceptions
	 */
	public function getExceptions() {
		return $this->exceptions;
	}

	/**
	 * @param PQDExceptions $exceptions
	 */
	public function setExceptions(PQDExceptions $exceptions) {
		$this->exceptions = $exceptions;
	}
	
	/**
	 * 
	 * @param booelan $requireHeaderAndFooter
	 * @return self
	 */
	public function setRequireHeaderAndFooter($requireHeaderAndFooter){
		$this->requireHeaderAndFooter = $requireHeaderAndFooter;
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	public function getRequireHeaderAndFooter(){
		return $this->requireHeaderAndFooter;
	}

	/**
	 * 
	 * @param string $tplHeader
	 * @return self
	 */
	public function setTplHeader($tplHeader){
		$this->tplHeader = $tplHeader;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getTplHeader(){
		return $this->tplHeader;
	}

	/**
	 * 
	 * @param string $tplFooter
	 * @return self
	 */
	public function setTplFooter($tplFooter){
		$this->tplFooter = $tplFooter;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getTplFooter(){
		return $this->tplFooter;
	}
	
	/**
	 * @param array $fields
	 * @return self
	 */
	public function setFields(array $fields){
		$this->fields = $fields;
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getFields(){
		return $this->fields;
	}
	
	/**
	 * 
	 * @param string $field
	 * @return string
	 */
	public function e($field){
		if(isset($this->fields[$field]['description']))
			return PQDUtil::escapeHtml($this->fields[$field]['description']);
		else 
			return 'Without Description';
	}
	
	/**
	 * @param string $name
	 */
	function __unset($name){
		unset($this->values[$name]);
	}

	/**
	 * @param string $name
	 */
	function __isset($name){
		return isset($this->values[$name]);
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	function __set($name, $value){
		$this->values[$name] = $value;
	}

	/**
	 * @param string $name
	 */
	function __get($name){
		return isset($this->values[$name]) ? $this->values[$name] : null;
	}

	function __destruct () {
		if ($this->autoRender && !(defined("APP_DEBUG_VIEW") && APP_DEBUG_VIEW))
			$this->render();
	}
}