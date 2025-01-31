<?php
namespace PQD;

require_once 'PQDDb.php';
require_once 'PQDExceptions.php';

class PQDUtil {

	const IS_CEP_WITH_MASK = '/^[0-9]{2,2}\.[0-9]{3,3}-[0-9]{3,3}$/';
	const IS_CEP_WITHOUT_MASK = '/^[0-9]{8,8}$/';

	const IS_CPF_WITH_MASK = '/^[0-9]{3,3}\.[0-9]{3,3}\.[0-9]{3,3}-[0-9]{2,2}$/';
	const IS_CPF_WITHOUT_MASK = '/^[0-9]{11,11}$/';

	const IS_CNPJ_WITH_MASK = '/^[0-9]{2,2}\.[0-9]{3,3}\.[0-9]{3,3}\/[0-9]{4,4}-[0-9]{2,2}$/';
	const IS_CNPJ_WITHOUT_MASK = '/^[0-9]{14,14}$/';

	const IS_FONE_WITH_DDD = '/^\([0-9]{2,2}\) [0-9]{4,4}-[0-9]{4,5}$/';
	const IS_FONE_WITHOUT_DDD = '/^[0-9]{4,4}-[0-9]{4,5}$/';

	const IS_DATE_BR = '/^[0-9]{2,2}\/[0-9]{2,2}\/[0-9]{4,4}$/';
	const IS_DATE_EN = '/^[0-9]{4,4}-[0-9]{2,2}-[0-9]{2,2}$/';

	const IS_DATE_BR_WITH_TIME = '/^[0-9]{2,2}\/[0-9]{2,2}\/[0-9]{4,4} [0-9]{2,2}:[0-9]{2,2}:[0-9]{2,2}$/';
	const IS_DATE_EN_WITH_TIME = '/^[0-9]{4,4}-[0-9]{2,2}-[0-9]{2,2} [0-9]{2,2}:[0-9]{2,2}:[0-9]{2,2}$/';

	const IS_EMAIL = '/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z]{2,3})+$/';

	const IS_FLOAT_BR = '/^([0-9]{1,3}+\.)*([0-9]{1,3}){1,1}(\,[0-9]+)*$/';
	const IS_FLOAT = '/^[0-9]+(\.[0-9]+)*$/';

	const IS_INT = '/^[0-9]+$/';

	const FORMAT_DB_DATE = 'Y-m-d';
	const FORMAT_DB_DATETIME = 'Y-m-d H:i:s';

	const FORMAT_VIEW_DATE = 'd/m/Y';
	const FORMAT_VIEW_DATETIME = 'd/m/Y H:i:s';

	/**
	 * Escape $data as SQL Server string
	 *
	 * @param mixed $data
	 * @return mixed
	 */
	public static function escapeSQL($data){
		return self::recursive($data, function($data){
			//return str_replace("'", "''", stripslashes($data));
			return str_replace("'", "''", $data);
		});
	}

	/**
	 * verify if values is valid according the reg mask
	 * @param mixed $value
	 * @param string $mask
	 * @return bool
	 */
	public static function isValid($value, $mask){
		return preg_match($mask, $value) !== 0;
	}

	/**
	 * verify if values is between a range
	 *
	 * @author Willker Moraes Silva
	 * @since 2015-11-17
	 * @param mixed $value
	 * @param mixed $ini
	 * @param mixed $end
	 * @return bool
	 */
	public static function isBetween($value, $ini, $end){
		return ($value >= $ini && $value <= $end);
	}


	/**
	 * Dá Trim nos valores,
	 * Cria a instancia da classe passada e seta os valores de acordo com o namepace informado
	 * caso os valores sejam vazios não seta no objeto.
	 *
	 * @author Willker Moraes Silva
	 * @since 2014-07-17
	 * @param string $class
	 * @param \stdClass|array $oData
	 * @param string $nameSpace
	 * @return mixed
	 */
	public static function setObjs($class, $oData, $nameSpace = null){

		$objRet = null;
		if(substr($nameSpace, -2) == "[]"){
			$objRet = array();
			$nameSpace = substr($nameSpace, 0, -2);
		}

		foreach ($oData as $key => $value){

			if(!is_null($nameSpace) && !empty($nameSpace))
				$obj = explode(":", $key);
			else
				$obj =  array(null, $key);

			if (count($obj) > 0 && $obj[0] == $nameSpace) {

				if(is_array($objRet)){
					
					if(is_array($value) || is_object($value)){
						foreach ($value as $k => $v){
							if(!isset($objRet[$k]))
								$objRet[$k] = new $class();

							//Não seta string vazia
							if (is_string($v)){
								$v = trim($v);
								if($v == "")
									continue;
							}

							if (method_exists($objRet[$k], "set" . ucwords($obj[1])))
								$objRet[$k]->{"set" . ucwords($obj[1])}($v);
						}
					}
				}
				else{
					if(is_null($objRet))
						$objRet = new $class();

					//Não seta string vazia
					if (is_string($value)){
						$value = trim($value);

						if($value == "")
							continue;
					}

					if (method_exists($objRet, "set" . ucwords($obj[1])))
						$objRet->{"set" . ucwords($obj[1])}($value);
				}
			}
		}

		return is_null($objRet) ? new $class() : $objRet;
	}

	/**
	 * Quando returnNull = true, caso o número não seja maior que zero retorna NULL
	 * Quando returnNull = false, formata o número do jeito que foi enviado independentemente do valor
	 *
	 * @param float $number
	 * @param int $precision
	 * @param boolean $returnNull
	 *
	 * @return null|string
	 */
	public static function formatNumberXML($number, $precision = 2, $returnNull = false){
		if($returnNull)
			return !is_null($number) && $number > 0 ? number_format($number, $precision, '.', '') : null;
		else
			return number_format($number, $precision, '.', '');
	}

	/**
	 * Formata um número no padrão Brasileiro
	 *
	 * @param int $number
	 * @param int $decimal
	 * @param bool $returnNull
	 * @return string
	 */
	public static function formatNumberView($number, $decimal = 2, $returnNull = false){

		if( !is_null($number) && trim($number) != '' )
			return number_format($number, $decimal, ",", ".");

		if( $returnNull )
			return null;
	
		return $number;
	}

	/**
	 * Formata um número no padrão do Banco de Dados
	 *
	 * @param string $number
	 * @return float|null
	 */
	public static function formatNumberDb($number, $returnNull = false){
		if($returnNull)
			return !is_null($number) && trim($number) != '' ? (float)str_replace(array('.', ','), array('', '.'), $number) : null;
		else
			return (float)str_replace(array('.', ','), array('', '.'), $number);
	}

	/**
	 * Obtem o timestamp atual
	 * 
	 * @return int
	 */
	public static function time(){
		return self::date('U');
	}

	/**
	 * Retorna uma data no formato passado, caso o timestamp não seja passada retorna a data atual
	 * 
	 * @param string $format
	 * @param int $timestamp
	 * 
	 * @return string
	 */
	public static function date($format, int $timestamp = null){

		$timestamp = is_null($timestamp) ? ( new \DateTimeImmutable() )->format('U') : $timestamp;

		return self::formatDate($timestamp, 'U', $format);
	}

	/**
	 * Converte uma data em timestamp
	 * 
	 * @param string $date
	 * @param string $format
	 * 
	 * @return int
	 */
	public static function toTime($date, $format = self::FORMAT_DB_DATE){
		return self::formatDate($date, $format, 'U');
	}

	/**
	 * Formata uma data de acordo com o formato passado
	 * 
	 * @param string $date
	 * @param string $from
	 * @param string $to
	 * 
	 * @return string
	 */
	public static function formatDate($date = null, $from = self::FORMAT_VIEW_DATETIME, $to = self::FORMAT_DB_DATETIME){
		
		if( !empty($date) || ($date >= 0 && $from == 'U')){
			
			$oDateTimeImmutable = self::getDateObj($date, $from);

			if($oDateTimeImmutable instanceof \DateTimeImmutable)
				return $oDateTimeImmutable->format($to);
			else
				return $date;
		}

		return $date;
	}

	/**
	 * Formata uma data no padrão de visualização
	 * 
	 * @param string $date
	 * @param string $formatVW
	 * @param string $formatDB
	 * 
	 * @return string
	 */
	public static function formatDateView($date = null, $formatVW = self::FORMAT_VIEW_DATE, $formatDB = self::FORMAT_DB_DATE){
		return self::formatDate($date, $formatDB, $formatVW);
	}

	/**
	 * Formata uma data e hora no padrão de visualização
	 * 
	 * @param string $date
	 * @param string $formatVW
	 * @param string $formatDB
	 * @return string
	 */
	public static function formatDateTimeView($date = null, $formatVW = self::FORMAT_VIEW_DATETIME, $formatDB = self::FORMAT_DB_DATETIME){
		return self::formatDate($date, $formatDB, $formatVW);
	}

	/**
	 * Formata uma data no padrão do Banco de Dados
	 * 
	 * @param string $date
	 * @param string $formatVW
	 * @param string $formatDB
	 * @return string
	 */
	public static function formatDateDB($date = null, $formatVW = self::FORMAT_VIEW_DATE, $formatDB = self::FORMAT_DB_DATE){
		return self::formatDate($date, $formatVW, $formatDB);
	}

	/**
	 * Formata uma data e hora no padrão do Banco de Dados
	 * 
	 * @param string $date
	 * @param string $formatVW
	 * @param string $formatDB
	 * @return string
	 */
	public static function formatDateTimeDB($date = null, $formatVW = self::FORMAT_VIEW_DATETIME, $formatDB = self::FORMAT_DB_DATETIME){
		return self::formatDate($date, $formatVW, $formatDB);
	}

	/**
	 * Obtem o objeto DateTimeImmutable de uma string de data e um formato
	 * 
	 * @param string $date
	 * @param string $format
	 * 
	 * @return \DateTimeImmutable
	 */
	public static function getDateObj($date, $format){

		$timeZone = new \DateTimeZone( date_default_timezone_get() );

		//Caso a data venha com informaçõe a mais do que o formato passado Ex: "2015-11-11 00:00:00.000000 -03:00" remove as informações a mais, formato: "Y-m-d H:i:s". 
		$dateTemp = substr($date, 0, strlen(date($format)));

		try{
			$oDateTimeImmutable = \DateTimeImmutable::createFromFormat('!' . $format, $dateTemp, $timeZone);
		}
		catch (\Exception $e){
			$oDateTimeImmutable = $date;
			PQDApp::getApp()->getExceptions()->setException( $e );
		}

		//Caso a data seja um timestamp, seta o timezone
		if( $format == 'U' && $oDateTimeImmutable instanceof \DateTimeImmutable)
			$oDateTimeImmutable = $oDateTimeImmutable->setTimezone( $timeZone );

		return $oDateTimeImmutable;
	}

	public static function onlyNumbers($string){
		if(!is_null($string))
			return preg_replace("/[^0-9]/", "", $string);
		else
			return $string;
	}

	public static function formatCpf($cpf){
		if (strlen($cpf) == 11) {
			return substr($cpf, 0, 3) . "." . substr($cpf, 3, 3) . "." . substr($cpf, 6, 3) . "-" . substr($cpf, 9);
		}
	}

	public static function formatCnpj($cnpj){
		if (strlen($cnpj) == 14) {
			return substr($cnpj, 0, 2) . "." . substr($cnpj, 2, 3) . "." . substr($cnpj, 5, 3) . "/" . substr($cnpj, 8, 4) . "-" . substr($cnpj, 12);
		}
	}

	public static function json_encode($value, $options = null, $utf8 = false){
		return json_encode( $utf8 ? self::utf8_encode($value) : $value, $options);
	}

	public static function json_decode($json, $assoc = true, $depth = 512, $options = 0){

		$return = json_decode( $json, $assoc, $depth, $options);

		if (is_null($return)){
			//echo $json;
			switch (json_last_error()) {
				case JSON_ERROR_NONE:
					PQDApp::getApp()->getExceptions()->setException( new \Exception('JSON - No errors', 10));
				break;
				case JSON_ERROR_DEPTH:
					PQDApp::getApp()->getExceptions()->setException( new \Exception('JSON - Maximum stack depth exceeded', 10));
				break;
				case JSON_ERROR_STATE_MISMATCH:
					PQDApp::getApp()->getExceptions()->setException( new \Exception('JSON - Underflow or the modes mismatch', 10));
				break;
				case JSON_ERROR_CTRL_CHAR:
					PQDApp::getApp()->getExceptions()->setException( new \Exception('JSON - Unexpected control character found', 10));
				break;
				case JSON_ERROR_SYNTAX:
					PQDApp::getApp()->getExceptions()->setException( new \Exception('JSON - Syntax error, malformed JSON', 10));
				break;
				case JSON_ERROR_UTF8:
					PQDApp::getApp()->getExceptions()->setException( new \Exception('JSON - Malformed UTF-8 characters, possibly incorrectly encoded', 10));
				break;
				default:
					PQDApp::getApp()->getExceptions()->setException( new \Exception('JSON - Unknown error', 10));
				break;
			}
		}

		return $return;
	}

	public static function recursive($data, $function, array $args = null){

		if ( is_array($data)){
			foreach ($data as $key => $value)
				$data[$key] = self::recursive($value, $function, $args);
		}
		else if(is_object($data)){
			foreach ($data as $key => $value)
				$data->{$key} = self::recursive($value, $function, $args);

			$oReflection = new \ReflectionObject($data);
			$aMethods = $oReflection->getMethods();

			foreach ($aMethods as $oReflectionMethod ){
				if (substr($oReflectionMethod->name, 0, 3) == "set" && method_exists($data, "get" . substr($oReflectionMethod->name, 3)))
					$data->{$oReflectionMethod->name}(self::recursive($data->{"get" . substr($oReflectionMethod->name, 3)}(), $function, $args));
			}
		}
		else if(!is_null($data) && is_string($data)){

			if (is_null($args))
				$data = $function($data);
			else {
				array_unshift($args, $data);
				$data = call_user_func_array($function, $args);
			}
		}

		return $data;
	}

	/**
	 * Escape $data as XML string
	 * 
	 * @param string $data
	 * 
	 * @return mixed
	 */
	public static function escapeXML($data){
		return self::recursive($data, function($data){
			return htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE | ENT_XML1);
		});
	}

	/**
	 * @author Willker Moraes Silva
	 * @since 2015-11-11
	 * @param mixed $data
	 */
	public static function escapeJS($data){
		return self::recursive($data, function($data){
			
			//A função ord não aceita UTF-8, devendo ser ISO-8859-1
			$data = mb_detect_encoding($data) == 'UTF-8' ? self::utf8_decode($data) : $data;

			$return = "";
			for ($i=0, $return = ""; $i<strlen($data); $i++)
				$return .= '\x' . dechex(ord(substr($data, $i, 1)));

			return $return;
		});
	}

	public static function utf8_encode($data){
		return self::recursive($data, "utf8_encode");
	}

	public static function utf8_decode($data){
		return self::recursive($data, "utf8_decode");
	}


	public static function strtoupper($data){
		return self::recursive($data, "mb_strtoupper");
	}

	public static function strtolower($data){
		return self::recursive($data, "mb_strtolower");
	}

	public static function trim($data){
		return self::recursive($data, "trim");
	}

	public static function escapeHtml($data, $charset = null, $flags = null){

		$charset = is_null($charset) ? ( defined('PQD_CHARSET') ? PQD_CHARSET : 'UTF-8' ) : $charset;

		return self::recursive($data, "htmlentities", array(
			$flags,
			$charset
		));
	}

	public static function html_entity_decode($data, $quote_style = ENT_COMPAT, $charset = null){

		$charset = is_null($charset) ? ( defined('PQD_CHARSET') ? PQD_CHARSET : 'UTF-8' ) : $charset;

		return self::recursive($data, "html_entity_decode", array( $quote_style, $charset ));
	}

	/**
	 * @param string $cnpj
	 * @return boolean
	 */
	public static function isCnpj($cnpj){
		$cnpj = self::onlyNumbers($cnpj);

		if( strlen($cnpj) != 14 )
			return false;
		/*
		if(strlen($cnpj) != 14 || self::isValid($cnpj, "/(^0{14}$)|(^1{14}$)|(^2{14}$)|(^3{14}$)|(^4{14}$)|(^5{14}$)|(^6{14}$)|(^7{14}$)|(^8{14}$)|(^9{14}$)/"))
			return false;
		*/


		for ($i = 0, $sum = 0; $i < 12; $i++)
			$sum += (int)substr($cnpj, $i, 1) * ($i < 4 ? 5-$i:13-$i);

		$firstDig = $sum % 11 < 2 ? 0 : 11-($sum%11);

		for ($i = 0, $sum = 0; $i < 13; $i++)
			$sum += (int)substr($cnpj, $i, 1) * ($i < 5 ? 6-$i:14-$i);

			$secondDig = $sum % 11 < 2 ? 0 : 11-($sum%11);

		return substr($cnpj, 12, 1) == $firstDig && substr($cnpj, 13, 1) == $secondDig;
	}

	public static function isCpf($cpf){
		$cpf = self::onlyNumbers($cpf);

		if( strlen($cpf) != 11 )
			return false;
		/*
		if(strlen($cpf) != 11 || self::isValid($cpf, "/(^0{11}$)|(^1{11}$)|(^2{11}$)|(^3{11}$)|(^4{11}$)|(^5{11}$)|(^6{11}$)|(^7{11}$)|(^8{11}$)|(^9{11}$)/"))
			return false;
		*/

		for ($i = 0, $sum = 0; $i < 9; $i++)
			$sum += (int)substr($cpf, $i, 1) * (10-$i);

		$firstDig = $sum % 11 < 2 ? 0 : 11-($sum%11);

		for ($i = 0, $sum = 0; $i < 10; $i++)
			$sum += (int)substr($cpf, $i, 1) * (11-$i);

		$secondDig = $sum % 11 < 2 ? 0 : 11-($sum%11);

		return substr($cpf, 9, 1) == $firstDig && substr($cpf, 10, 1) == $secondDig;
	}

	/**
	 * @param mixed $var
	 * @param bool $dump
	 */
	public static function print_pre($var, $dump = false){
		echo "<pre>";

		print_r($var);
		if ($dump)
			var_dump($var);

		echo "</pre>";
	}

	public static function withoutSpaces($string, $commentsJS = true, $commentsHTML = false, $escape = false){
		$aSearch = array();
		$aReplace = array();

		if ($escape){
			$aSearch[] = '/\"/';
			$aReplace[] = '\"';
		}

		//Without Spaces
		$aSearch[] = "/[\r\n\t]|[ ]{2,}/";
		$aReplace[] = "";

		//Without Comments /**/ //
		if($commentsJS){
			//FIXME: [^\xff] essa expressão está errada, o certo seria .|\s
			$aSearch[] = '/(\/\*([^\xff])*?(\*\/))/'; //Comments /* */
			$aSearch[] = "/\/\/.*$/"; // Comments //

			//Retirando JSON
			//FIXME: [^\xff] essa expressão está errada, o certo seria .|\s
			$matchJSON = '/(\/\*JSON_START\*\/)([^\xff]*)?(\/\*JSON_END\*\/)/i';
			preg_match_all($matchJSON, $string, $json);
			foreach ($json[0] as $key => $text)
				$string = str_replace($text, "__##JSON_" . $key . "##__", $string);
		}

		//Without HTML Comments <!-- -->
		if($commentsHTML){

			if($commentsJS)
				array_pop($aSearch); //remove comments from one line because when we have http:// it removes all code

			//FIXME: [^\xff] essa expressão está errada, o certo seria .|\s
			$aSearch[] = '/(\<\!\-\-([^\xff])*?(\-\-\>))/';

			//Retirando TEXTAREAS
			//FIXME: [^\xff] essa expressão está errada, o certo seria .|\s
			$matchTextArea = '/(\<textarea)([^\xff])*?(\<\/textarea\>)/i';
			preg_match_all($matchTextArea, $string, $textareas);

			foreach ($textareas[0] as $key => $text)
				$string = str_replace($text, "__##TEXTAREA_" . $key . "##__", $string);
		}

		$string = preg_replace($aSearch, $aReplace, $string);

		if($commentsHTML){
			foreach ($textareas[0] as $key => $text)
				$string = str_replace("__##TEXTAREA_" . $key . "##__", $text, $string);
		}

		if($commentsJS){
			foreach ($json[0] as $key => $text)
				$string = str_replace("__##JSON_" . $key . "##__", $text, $string);

			$string = str_replace(array('/*JSON_START*/', '/*JSON_END*/'), "", $string);
		}

		return $string;
	}

	public static function getFileWithoutSpaces($file, $commentsJS = true, $commentsHTML = false, $escape = false){
		if(IS_DEVELOPMENT)
			return file_get_contents($file);
		else
			return self::withoutSpaces(file_get_contents($file), $commentsJS, $commentsHTML, $escape);
	}

	public static function captcha($largura, $altura, $tamanho_fonte, $quantidade_letras, $pathFont){

		$imagem = imagecreate($largura,$altura); // define a largura e a altura da imagem
		$fonte = $pathFont; //voce deve ter essa ou outra fonte de sua preferencia em sua pasta
		$preto = imagecolorallocate($imagem,0,0,0); // define a cor preta
		$branco = imagecolorallocate($imagem,255,255,255); // define a cor branca

		// define a palavra conforme a quantidade de letras definidas no parametro $quantidade_letras
		$palavra = substr(str_shuffle("AaBbCcDdEeFfGgHhIiJjKkLlMmNnPpQqRrSsTtUuVvYyXxWwZz23456789"),0,($quantidade_letras));
		$_SESSION["captcha"] = $palavra; // atribui para a sessao a palavra gerada

		for($i = 1; $i <= $quantidade_letras; $i++)
			imagettftext($imagem,$tamanho_fonte,rand(-25,25),($tamanho_fonte*$i),($tamanho_fonte + 10), $branco, $fonte, substr($palavra,($i-1),1)); // atribui as letras a imagem

		imagejpeg($imagem); // gera a imagem
		imagedestroy($imagem); // limpa a imagem da memoria
	}

	public static function hex2rgb($hex) {
		$hex = str_replace("#", "", $hex);

		if(strlen($hex) == 3) {
			$r = hexdec(substr($hex,0,1).substr($hex,0,1));
			$g = hexdec(substr($hex,1,1).substr($hex,1,1));
			$b = hexdec(substr($hex,2,1).substr($hex,2,1));
		} else {
			$r = hexdec(substr($hex,0,2));
			$g = hexdec(substr($hex,2,2));
			$b = hexdec(substr($hex,4,2));
		}
		$rgb = array($r, $g, $b);
		//return implode(",", $rgb); // returns the rgb values separated by commas
		return $rgb; // returns an array with the rgb values
	}

	public static function addZeros($str, $length, $type = STR_PAD_LEFT){
		return str_pad($str, $length, "0", $type);
	}

	public static function rgb2hex($rgb) {
		$hex = "#";
		$hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);

		return $hex; // returns the hex value including the number sign (#)
	}

	public static function contentType($type = 'json', $fileName = null, $forceDownload = true, $charset = null){

		if(IS_CLI)
			return;

		$contentType = 'text/html';

		switch ($type) {
			case 'css':
				$contentType = 'text/css';
			break;
			case 'js':
				$contentType = 'text/javascript';
			break;
			case 'htm':
			case 'html':
				$contentType = 'text/html';
			break;
			case 'json':
				$contentType = 'application/json';
			break;
			case 'img':
			case 'jpeg':
			case 'jpg':
				$contentType = 'image/jpeg';
			break;
			case 'gif':
				$contentType = 'image/gif';
			break;
			case 'png':
				$contentType = 'image/png';
			break;
			case 'xml':
				$contentType = 'text/xml';
			break;
			case 'svg':
				$contentType = 'image/svg+xml';
			break;
			case 'txt':
			case 'text':
				$contentType = 'text/plain';
			break;
			case 'xls':
			case 'xlsx':

				$contentType = $type == 'xls' ? 'application/vnd.ms-excel' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

				header('Cache-Control: max-age=0');
				// If you're serving to IE 9, then the following may be needed
				header('Cache-Control: max-age=1');
				// If you're serving to IE over SSL, then the following may be needed
				header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
				header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
				header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
				header ('Pragma: public'); // HTTP/1.0
			break;
			case 'pdf':
				// Redirect output to a client web browser (PDF)
				$contentType = 'application/pdf';
				header('Cache-Control: max-age=0');
			break;
		}

		header('Content-Type: ' . $contentType . (!is_null($charset) ? ';charset=' . $charset: ''), true);

		if (!is_null($fileName))
			header('Content-Disposition: ' . ($forceDownload ? 'attachment;': 'inline;') . 'filename="' . $fileName . '"');

		if ($type == 'json' && defined("PQD_FIRST_LINE_JSON"))
			echo PQD_FIRST_LINE_JSON;
	}

	/**
	 * Retorna um DOMDocument para o array dado
	 *
	 * @param array $data
	 * @param \DOMNode $node
	 * @param \DOMDocument $document
	 */
	public static function dom_encode(array $data, \DOMNode &$node = null, \DOMDocument &$document = null, $utf8 = false){

		if(is_null($document)){
			$data = $utf8 ? self::utf8_encode($data) : $data;
			$document =  new \DOMDocument("1.0");
			$node = $document->createElement("data");
			$document->appendChild($node);
		}

		foreach($data as $chave => $item) {

			if(is_array($item)) {
				$prefixo = (is_numeric($chave)) ? 'item' : '';
				$child = $document->createElement($prefixo.$chave);
				$node->appendChild($child);
				self::dom_encode($item, $child, $document);
			}
			else {
				$prefixo = (is_numeric($chave)) ? 'item' : '';
				$child = $document->createElement($prefixo.$chave);
				$node->appendChild($child);
				$child->appendChild($document->createTextNode($item));
			}
		}

		return $document;
	}

	/**
	 * Retorna um array para o Elemento DOM dado
	 * @param \DOMNode $root
	 * @return array
	 */
	public static function dom_to_array($root){
		$result = array();

		if ($root->hasAttributes()){
			$attrs = $root->attributes;

			foreach ($attrs as $i => $attr)
				$result[$attr->name] = $attr->value;
		}

		$children = $root->childNodes;

		if ($children->length == 1){
			$child = $children->item(0);

			if ($child->nodeType == XML_TEXT_NODE){
				$result['_value'] = $child->nodeValue;

				if (count($result) == 1)
					return $result['_value'];
				else
					return $result;
			}
		}

		$group = array();

		for($i = 0; $i < $children->length; $i++){
			$child = $children->item($i);

			if (!isset($result[$child->nodeName]))
				$result[$child->nodeName] = self::dom_to_array($child);
			else{
				if (!isset($group[$child->nodeName])){
					$tmp = $result[$child->nodeName];
					$result[$child->nodeName] = array($tmp);
					$group[$child->nodeName] = 1;
				}

				$result[$child->nodeName][] = self::dom_to_array($child);
			}
		}

		return $result;
	}

	/**
	 * Obtem um confirmação quando executando em CLI
	 *
	 * @param string $msg
	 * @param array $result
	 * @return boolean
	 */
	public static function confirmCLI($msg, array $result = null){

		if(!IS_CLI)
			return true;

		$msg .= PHP_EOL . "y = yes, n = no";
		if (!is_null($result))
			$msg .= ", v=view data" . PHP_EOL . "(" . count($result) . ") registro(s)!";
		$msg .= PHP_EOL;

		$resp = "";
		$count = 0;
		while($resp != "y" && $resp != "n"){
			echo $msg;
			$resp = self::strtolower( trim(fgets(STDIN)) );
			if($resp == "v")
				print_r($result);

			$count++;

			if( $count > 3 ){
				echo "Você não respondeu corretamente!" . PHP_EOL;
				exit(1);
			}
		}

		return $resp == "y";
	}

	/**
	 * Obtem um input no terminal
	 *
	 * @param string $msg
	 * @param string $default
	 * @return string|NULL
	 */
	public static function inputCLI($msg, $default = null){

		$input = "";

		while($input == ""){

			echo $msg;
			$input = trim(fgets(STDIN));

			if (!is_null($default) && $input == "")
				return $default;
		}

		return $input;
	}


	/**
	 * Apresenta um choice no cli
	 *
	 * @param string $msg
	 * @param array $choices
	 * @return string
	 */
	public static function choiceCLI($msg = "Escolha um item:", array $choices, $default = null, $caseSensitive = false){

		if(!$caseSensitive)
			$choices = array_change_key_case($choices, CASE_LOWER);

		$resp = null;
		while (!isset($choices[$resp])){
			echo $msg . PHP_EOL;
			foreach ($choices as $k => $v)
				echo "\t" . $k . '=>' . $v . PHP_EOL;

			$resp = trim(fgets(STDIN));

			if (!is_null($default) && empty($resp))
				return $default;

			$resp = !$caseSensitive ? self::strtolower($resp) : $resp;
		}

		return $resp;
	}

	/**
	 * Formata um número
	 *
	 * @param int $number
	 * @param int $decimals
	 * @param string $dec_point
	 * @param string $thousands_sep
	 */
	public static function number_format($number, $decimals = 2, $dec_point = ',', $thousands_sep = '.'){

		if(!is_null($number))
			return number_format($number, $decimals, $dec_point, $thousands_sep);
		else
			return $number;
	}

	/**
	 * Separa uma string de acordo com o tamanho e caracter de separação passado
	 *
	 * @param string $str
	 * @param int $len
	 * @param string $separation
	 *
	 * @return string
	 */
	public static function str_separate($str, $len = 8, $separation = " "){
		return implode($separation, str_split($str, $len));
	}

	/**
	 * Converte um array para XML
	 *
	 * @param array $data
	 * @return string
	 */
	public static function toXML(array $data) {
		return self::dom_encode($data)->saveXML();
	}

	/**
	 * Converte um array para HTML
	 *
	 * @param array $data
	 * @return string
	 */
	public static function toHTML(array $data) {
		return self::dom_encode($data)->saveHTML();
	}
	
	/**
	 * Retorna o valor padrão caso não exista a chave no parâmetro
	 * 
	 * @return mixed
	 */
	public static function retDefault($param, $key, $default = null){
		return isset($param[$key]) ? $param[$key] : $default;
	}

	/**
	 * Seta valores padrões em uma variavel
	 * 
	 * @var array $aParams
	 * @var array $aDefault
	 * 
	 * @return array
	 */
	public static function setDefault(array $aParams, array $aDefault){

		foreach($aDefault as $key => $value){
			if(is_array($value))
				$aParams[$key] = self::setDefault(self::retDefault($aParams, $key, array()), $value);
			else
				$aParams[$key] = self::retDefault($aParams, $key, $value);	
		}

		return $aParams;
	}

	/**
	 * Processa um template de texto e substitui as váriaveis 
	 * 
	 * As várieaveis são: array({@variavel} => valor)
	 * E ifs: array('begin' => {@seAlgo}, 'else' => '{@elseAlgo}', 'end' => {@fimSeAlgo}, 'bool' => false)
	 * 
	 * @param string $tpl
	 * @param array $aReplace
	 * @param array $aIfs
	 * 
	 * @return string
	 */
	public static function procTplText($tpl, array $aReplace, array $aIfs = array()){

		$search = array_keys($aReplace);
		$replace = array_values($aReplace);
		unset($aReplace);

		//Processando ifs
		foreach($aIfs as $if){
			
			$aMatches = array();

			if( preg_match_all('/(' . $if['begin'] . ')(.|\s)*?(' . $if['end'] . ')/', $tpl, $aMatches) !== false ){

				foreach($aMatches[0] as $txt){

					$aClearText = array($if['begin'], $if['end']);
					if( isset($if['else']) )
						$aClearText[] = $if['else'];
					
					$txtResult = $txt;

					//Limpando if/else
					if( $if['bool'] ){

						//Limpando else
						if( isset($if['else']) ){

							$aMatchesElse = array();
							
							if( preg_match_all('/(' . $if['else'] . ')(.|\s)*?(' . $if['end'] . ')/', $txt, $aMatchesElse) !== false ){
								foreach($aMatchesElse[0] as $txtElse)
									$txtResult = str_replace($txtElse, '', $txtResult);
							}
						}						
					}
					else{

						//Limpando if
						$end = isset($if['else']) ? 'else' : 'end';

						$aMatchesIf = array();
						
						if( preg_match_all('/(' . $if['begin'] . ')(.|\s)*?(' . $if[ $end ] . ')/', $txt, $aMatchesIf) !== false ){
							foreach($aMatchesIf[0] as $txtIf)
								$txtResult = str_replace($txtIf, '', $txtResult);
						}
					}

					$txtResult = str_replace($aClearText, '', $txtResult);
					$tpl = str_replace($txt, str_replace($search, $replace, $txtResult), $tpl);
				}
			}
		}

		return str_replace($search, $replace, $tpl);
	}
}