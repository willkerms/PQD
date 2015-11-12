<?php
namespace PQD;
/**
 *
 * @author Willker Moraes Silva
 * @since 2012-08-10
 *
 */
class PQDEntity {
	
	public function toArray(){
		return get_object_vars($this);
	}
	
	public function toJSON(){
		return PQDUtil::json_encode($this->toArray());
	}
}