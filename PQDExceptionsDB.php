<?php

namespace PQD;

class PQDExceptionsDB extends \Exception {

	function __construct(array $dbError, $msg = ''){
		parent::__construct($msg . "\r\n" . $dbError[2], $dbError[1]);
	}
}