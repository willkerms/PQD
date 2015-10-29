<?php

namespace PQD\REPORT;

class ServerDB {
	
	/**
	 * @var int
	 */
	private $idPqdServerDB;
	
	/**
	 * @var string
	 */
	private $driver;
	
	/**
	 * @var string
	 */
	private $host;
	
	/**
	 * @var string
	 */
	private $db;
	
	/**
	 * @var string
	 */
	private $user;
	
	/**
	 * @var string
	 */
	private $pwd;
	
	/**
	 * @return int
	 */
	public function getIdPqdServerDB() {
		return $this->idPqdServerDB;
	}

	/**
	 * @param number $idPqdServerDB
	 */
	public function setIdPqdServerDB($idPqdServerDB) {
		$this->idPqdServerDB = $idPqdServerDB;
	}

	/**
	 * @return string
	 */
	public function getDriver() {
		return $this->driver;
	}

	/**
	 * @return string
	 */
	public function getHost() {
		return $this->host;
	}

	/**
	 * @return string
	 */
	public function getDb() {
		return $this->db;
	}

	/**
	 * @return string
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @return string
	 */
	public function getPwd() {
		return $this->pwd;
	}

	/**
	 * @param string $driver
	 */
	public function setDriver($driver) {
		$this->driver = $driver;
	}

	/**
	 * @param string $host
	 */
	public function setHost($host) {
		$this->host = $host;
	}

	/**
	 * @param string $db
	 */
	public function setDb($db) {
		$this->db = $db;
	}

	/**
	 * @param string $user
	 */
	public function setUser($user) {
		$this->user = $user;
	}

	/**
	 * @param string $pwd
	 */
	public function setPwd($pwd) {
		$this->pwd = $pwd;
	}
}