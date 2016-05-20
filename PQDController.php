<?php
namespace PQD;
require_once 'PQDExceptions.php';

/**
 * @author Willker Moraes Silva
 * @since 2012-03-30
 */
abstract class PQDController implements IPQDController{
	/**
	 * 
	 * @var PQDBo
	 */
	protected $oBO;
	
	/**
	 * @var PQDView
	 */
	private $view;

	/**
	 * @var \stdClass
	 */
	protected $post;

	/**
	 * @var \stdClass
	 */
	protected $get;

	/**
	 * @var \stdClass
	 */
	protected $session;

	/**
	 * @var \stdClass
	 */
	protected $files;

	/**
	 * @var PQDExceptions
	 */
	protected $exceptions;

	/**
	 * @return PQDView $view
	 */
	protected function getView () {
		return $this->view;
	}

	/**
	 * @param unknown $view
	 * @return PQDView
	 */
	protected function setView ($view) {

		//Quando sobre escreve a view não deixa a view antiga ser exibida ao ser limpada pelo garbage collection!
		if (!is_null($this->getView()))
			$this->getView()->setAutoRender(false);
			
		if (is_object($view) && $view instanceof PQDView)
			$this->view = $view;
		else
			$this->view = new PQDView($view, $this->exceptions);
		
		return $this->getView();
	}

	/**
	 *
	 * @param array $post
	 * @param array $get
	 * @param array $session
	 * @param array $files
	 */
	function __construct(array $post, array $get, array $session, PQDExceptions $exceptions, array $files = null){
		
		$this->post = (object)$post;
		$this->get = (object)$get;
		$this->session = (object)$session;
		$this->files = (object)$files;
		
		$this->exceptions = $exceptions;
	}

	/**
	 *
	 * @param string $location
	 */
	protected function redirect($location){
		header('Location: ' . $location, true);
		exit();
	}
	
	/**
	 * @return array
	 */
	public function getGet(){
		return (array)$this->get;
	}
	
	/**
	 * @return array
	 */
	public function getPost(){
		return (array)$this->post;
	}

	/**
	 * @return array
	 */
	public function getSession(){
		return (array)$this->session;
	}
	
	/**
	 * @return array
	 */
	public function getFiles(){
		return (array)$this->files;
	}
	
	/**
	 * @return PQDExceptions
	 */
	public function getExceptions(){
		return $this->exceptions;
	}
}