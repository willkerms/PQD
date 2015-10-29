<?php
namespace PQD;
require_once 'PQDExceptions.php';

/**
 * @author Willker Moraes Silva
 * @since 2012-03-30
 */
abstract class PQDController{

	/**
	 * @var PQD\PQDView
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
	 * @return PQD\PQDView $view
	 */
	protected function getView () {
		return $this->view;
	}

	/**
	 * @param mixed $view
	 */
	protected function setView ($view) {

		if (is_object($view) && $view instanceof PQDView)
			$this->view = $view;
		else
			$this->view = new PQDView($view, $this->exceptions);
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
	 * @return PQD\PQDExceptions
	 */
	public function getExceptions(){
		return $this->exceptions;
	}
}