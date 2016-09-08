<?php

namespace PQD;

class PQDCrypt {

	const CRYPT_KEY = "M@tr!zCryptK3y";

	public static function encrypt($value, $key = self::CRYPT_KEY){

		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
		$ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $value, MCRYPT_MODE_CBC, $iv);

		return base64_encode($iv . '_' . $ciphertext);
	}

	public static function decrypt($value, $key = self::CRYPT_KEY){

		$arr = preg_split("/_/", base64_decode($value));

		$iv = array_shift($arr);
		$crypt = join("", $arr);

		return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $crypt, MCRYPT_MODE_CBC, $iv);
	}
}