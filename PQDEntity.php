<?php
namespace PQD;
/**
 *
 * @author Willker Moraes Silva
 * @since 2012-08-10
 *
 */
class PQDEntity {

	/**
	 * @return array
	 */
	public function toArray(){
		return get_object_vars($this);
	}

	/**
	 * @return string
	 */
	public function toJSON(){
		return PQDUtil::json_encode($this->toArray());
	}

	/**
	 * @return self
	 */
	public function escapeHTML(){
		return PQDUtil::escapeHtml($this);
	}

	/**
	 * @return self
	 */
	public function utf8_encode(){
		return PQDUtil::utf8_encode($this);
	}

	/**
	 * @return self
	 */
	public function utf8_decode(){
		return PQDUtil::utf8_decode($this);
	}

	/**
	 * @return self
	 */
	public function upperCase(){
		return PQDUtil::strtoupper($this);
	}

	/**
	 * @return self
	 */
	public function lowerCase(){
		return PQDUtil::strtolower($this);
	}

	/**
	 * @return self
	 */
	public function escapeJS(){
		return PQDUtil::escapeJS($this);
	}

	/**
	 * @return self
	 */
	public function trim(){
		return PQDUtil::trim($this);
	}
}