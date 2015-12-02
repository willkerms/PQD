<?php
namespace PQD;
/**
 *
 * @author Willker Moraes Silva
 * @since 2012-07-26
 *
 */
abstract class PQDBo implements IPQDBo{
	
	/**
	 * @var PQDDAO
	 */
	protected $oDAO;
	
	/**
	 *
	 * @var array
	 */
	private $session;

	/**
	 *
	 * @var PQDExceptions
	 */
	private $exceptions;

	public function __construct(\stdClass $oSession, PQDExceptions $oExceptions){

		$this->session = $oSession;
		$this->exceptions = $oExceptions;
	}

	/**
	 * @return the $session
	 */
	public function getSession() {
		return $this->session;
	}

	/**
	 * @return PQDExceptions
	 */
	public function getExceptions() {
		return $this->exceptions;
	}
}