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

		if(is_file($view))
			$this->view = realpath($view);
		else
			throw new \Exception("Erro ao Carregar View: " . $view, 3);

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
				
				if(!is_null($this->tplHeader) && trim($this->tplHeader) != "")
					require_once $this->tplHeader;
				
				require $this->view;
				
				if(!is_null($this->tplFooter) && trim($this->tplFooter) != "")
					require_once $this->tplFooter;
				
				echo PQDUtil::withoutSpaces(ob_get_clean(), true, true);
			}
			else{
				if(!is_null($this->tplHeader) && trim($this->tplHeader) != "")
					require_once $this->tplHeader;
				
				require $this->view;
				
				if(!is_null($this->tplFooter) && trim($this->tplFooter) != "")
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
	 */
	public function setAutoRender ($autoRender) {
		$this->autoRender = $autoRender;
	}

	/**
	 * @return the $view
	 */
	public function getView() {
		return $this->view;
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
	 */
	public function setRequireHeaderAndFooter($requireHeaderAndFooter){
		$this->requireHeaderAndFooter = $requireHeaderAndFooter;
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
	 */
	public function setTplHeader($tplHeader){
		$this->tplHeader = $tplHeader;
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
	 */
	public function setTplFooter($tplFooter){
		$this->tplFooter = $tplFooter;
	}
	
	/**
	 * @return string
	 */
	public function getTplFooter(){
		return $this->tplFooter;
	}
	
	/**
	 * @param array $fields
	 */
	public function setFields(array $fields){
		$this->fields = $fields;
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
		if ($this->autoRender)
			$this->render();
	}
}