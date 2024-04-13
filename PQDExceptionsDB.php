<?php

namespace PQD;

class PQDExceptionsDB extends \Exception {

	function __construct(array $dbError, $msg = '', \Throwable $previous = null){
		parent::__construct($dbError[2] . PHP_EOL . $msg, 0, $previous);
	}
}