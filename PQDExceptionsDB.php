<?php

namespace PQD;

class PQDExceptionsDB extends \Exception {

	function __construct(array $dbError, $msg = '', \Throwable $previous = null){
		parent::__construct($dbError[2] . PHP_EOL . $msg, $dbError[1], $previous);
	}
}