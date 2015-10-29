<?php

namespace PQD\DB;

class DB {

	private static $connections = array();

	/**
	 *
	 * @param string $dsn
	 * @param string $username
	 * @param string $passwd
	 * @param array $options
	 *
	 * @return number
	 */
	public static function setConnection($dsn, $username, $passwd, $options){
		try {

			$oPdo = new \PDO($dsn, $username, $passwd, $options);
			self::$connections[] = array(
				'dsn' => $dsn,
				'username' => $username,
				'passwd' => $passwd,
				'options' => $options,
				'pdo' => $oPdo
			);

			return count(self::$connections) -1;
		} catch (\Exception $e) {
			throw $e;
		}
	}

	/**
	 *
	 * @return \PDO
	 */
	public static function getConnection($indexCon = 0){

		if (is_string($indexCon) && DB::getInstance($indexCon) >= 0)
			return self::$connections[DB::getInstance($indexCon)]['pdo'];
		else if (isset(self::$connections[$indexCon]))
			return self::$connections[$indexCon]['pdo'];
		else
			throw new \Exception("Database not registered.");
	}

	/**
	 *
	 * @return number
	 */
	public static function countConn(){
		return count(self::$connections);
	}

	/**
	 *
	 * @param string $dsn
	 * @return number
	 */
	public static function getInstance($dsn){
		for ($i = 0; $i < count(self::$connections); $i++) {
			if (self::$connections[$i]['dsn'] == $dsn)
				return $i;
		}

		return -1;
	}
}