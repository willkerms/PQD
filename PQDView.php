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
	 * @param string $view
	 */
	function __construct ($view, PQDExceptions $exceptions = null) {

		if(is_file($view))
			$this->view = realpath($view);
		else
			throw new \Exception("Erro ao Carregar View: " . $view, 3);

		$this->exceptions = $exceptions;
	}

	private function render(){
		
		if ($this->requireHeaderAndFooter){

			if(!IS_DEVELOPMENT){
				ob_start();
				
				if(defined('APP_TEMPLATE_HEAD'))
					require_once 'templates/head.php';
				
				require $this->view;
				
				if(defined('APP_TEMPLATE_FOOTER'))
					require_once 'templates/footer.php';
				
				echo PQDUtil::withoutSpaces(ob_get_clean(), true, true);
			}
			else{
				if(defined('APP_TEMPLATE_HEAD'))
					require_once 'templates/head.php';
				
				require $this->view;
				
				if(defined('APP_TEMPLATE_FOOTER'))
					require_once 'templates/footer.php';
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