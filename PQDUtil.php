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
	
	const IS_FONE_WITH_DDD = '/^[0-9]{2,2}-[0-9]{4,4}-[0-9]{4,4}$/';
	const IS_FONE_WITHOUT_DDD = '/^[0-9]{4,4}-[0-9]{4,4}$/';
	
	const IS_DATE_BR = '/^[0-9]{2,2}\/[0-9]{2,2}\/[0-9]{4,4}$/';
	const IS_DATE_EN = '/^[0-9]{4,4}-[0-9]{2,2}-[0-9]{2,2}$/';
	
	const IS_DATE_BR_WITH_TIME = '/^[0-9]{2,2}\/[0-9]{2,2}\/[0-9]{4,4} [0-9]{2,2}:[0-9]{2,2}:[0-9]{2,2}$/';
	const IS_DATE_EN_WITH_TIME = '/^[0-9]{4,4}-[0-9]{2,2}-[0-9]{2,2} [0-9]{2,2}:[0-9]{2,2}:[0-9]{2,2}$/';
	
	const IS_EMAIL = '/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z]{2,3})+$/';
	
	const IS_FLOAT_BR = '/^([0-9]{1,3}+\.)*([0-9]{1,3}){1,1}(\,[0-9]+)*$/';
	const IS_FLOAT = '/^[0-9]+(\.[0-9]+)*$/';
	
	const IS_INT = '/^[0-9]+$/';
	

	/**
	 * Escape $data as SQL Server string
	 *
	 * @param mixed $data
	 * @return mixed
	 */
	public static function escapeSQLServer($data){
		if (is_string($data))
			$data = str_replace("'", "''", stripslashes($data));
		else if ( is_array($data)){
			foreach ($data as $key => $value)
				$data[$key] = self::escapeSQLServer($value);
		}
		else if(is_object($data)){
			foreach ($data as $key => $value)
				$data->{$key} = self::escapeSQLServer($value);
		}

		return $data;
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
	 * D� Trim nos valores, 
	 * Cria a instancia da classe passada e seta os valores de acordo com o namepace informado
	 * caso os valores sejam vazios n�o seta no objeto.
	 * 
	 * @author Willker Moraes Silva
	 * @since 2014-07-17
	 * @param string $class
	 * @param \stdClass $array
	 * @param string $nameSpace
	 * @return mixed
	 */
	public static function setObjs($class, \stdClass $array, $nameSpace){
	
		$objRet = null;
		if(substr($nameSpace, -2) == "[]"){
			$objRet = array();
			$nameSpace = substr($nameSpace, 0, -2);
		}
	
		foreach ($array as $key => $value){

			$obj = explode(":", $key);
			
			if (count($obj) > 0 && $obj[0] == $nameSpace) {
	
				if(is_array($objRet)){
					foreach ($value as $k => $v){
						if(!isset($objRet[$k]))
							$objRet[$k] = new $class();
	
						//N�o seta string vazia
						if (is_string($v)){
							$v = trim($v);
							if($v == "")
								continue;
						}

						if (method_exists($objRet[$k], "set" . ucwords($obj[1])))
							$objRet[$k]->{"set" . ucwords($obj[1])}($v);
					}
				}
				else{
					if(is_null($objRet))
						$objRet = new $class();
						
					//N�o seta string vazia
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
	
	public static function getDateView($date = null){
		if(!empty($date)){
			
			$nDate = \DateTime::createFromFormat(PQD_FORMAT_DB_DATE, $date);
			
			if($nDate instanceof \DateTime)
				return $nDate->format(PQD_FORMAT_VIEW_DATE);
			else
				return $date;
		}
		
		return $date;
	}
	
	public static function getDateTimeView($date = null){
		
		if(!empty($date)){
			$nDate = \DateTime::createFromFormat(PQD_FORMAT_DB_DATETIME, $date);
			
			if($nDate instanceof \DateTime)
				return $nDate->format(PQD_FORMAT_VIEW_DATETIME);
			else 
				return $date;
		}
		
		return $date;
	}
	
	public static function getDateDB($date = null){

		if(!empty($date)){
			$nDate = \DateTime::createFromFormat(PQD_FORMAT_VIEW_DATE, $date);

			if($nDate instanceof \DateTime)
				return $nDate->format(PQD_FORMAT_DB_DATE);
			else
				return $date;
		}
		
		return $date;
	}
	
	public static function getDateTimeDB($date = null){

		if(!empty($date)){
			$nDate = \DateTime::createFromFormat(PQD_FORMAT_VIEW_DATETIME, $date);
			
			if($nDate instanceof \DateTime)
				return $nDate->format(PQD_FORMAT_DB_DATETIME);
			else
				return $date;
		}
		
		return $date;
	}
	
	public static function getDateObj($date = null, $format){
		return \DateTime::createFromFormat($format, $date);
	}
	
	public static function onlyNumbers($string){
		return preg_replace("/[^0-9]/", "", $string);
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
	
	public static function json_encode($value, $options = null){
		return json_encode( self::utf8_encode($value), $options);
	}
	
	public static function json_decode($json, $assoc = false, $depth = 512, $options = 0){
		
		$return = json_decode( $json, $assoc, $depth, $options);
		
		if (is_null($return)){
			switch (json_last_error()) {
				case JSON_ERROR_NONE:
					PQDApp::getExceptions()->setException( new \Exception('JSON - No errors', 10));
				break;
				case JSON_ERROR_DEPTH:
					PQDApp::getExceptions()->setException( new \Exception('JSON - Maximum stack depth exceeded', 10));
				break;
				case JSON_ERROR_STATE_MISMATCH:
					PQDApp::getExceptions()->setException( new \Exception('JSON - Underflow or the modes mismatch', 10));
				break;
				case JSON_ERROR_CTRL_CHAR:
					PQDApp::getExceptions()->setException( new \Exception('JSON - Unexpected control character found', 10));
				break;
				case JSON_ERROR_SYNTAX:
					PQDApp::getExceptions()->setException( new \Exception('JSON - Syntax error, malformed JSON', 10));
				break;
				case JSON_ERROR_UTF8:
					PQDApp::getExceptions()->setException( new \Exception('JSON - Malformed UTF-8 characters, possibly incorrectly encoded', 10));
				break;
				default:
					PQDApp::getExceptions()->setException( new \Exception('JSON - Unknown error', 10));
				break;
			}
		}
		
		return $return;
	}
	
	public static function utf8_encode($data){
		if ( is_array($data)){
			foreach ($data as $key => $value)
				$data[$key] = self::utf8_encode($value);
		}
		else if(is_object($data)){
			foreach ($data as $key => $value)
				$data->{$key} = self::utf8_encode($value);
			
			$oReflection = new \ReflectionObject($data);
			$aMethods = $oReflection->getMethods();
				
			foreach ($aMethods as $oReflectionMethod ){
				if (substr($oReflectionMethod->name, 0, 3) == "set" && method_exists($data, "get" . substr($oReflectionMethod->name, 3)))
					$data->{$oReflectionMethod->name}(self::utf8_encode($data->{"get" . substr($oReflectionMethod->name, 3)}()));
			}
		}
		else if(!is_null($data) && trim($data) != '')
			$data = utf8_encode($data);
		
		return $data;
	}
	
	public static function escapeHtml($data, $charset = 'UTF-8'){
		if ( is_array($data)){
			foreach ($data as $key => $value)
				$data[$key] = self::escapeHtml($value, $charset);
		}
		else if(is_object($data)){
			foreach ($data as $key => $value)
				$data->{$key} = self::escapeHtml($value, $charset);
			
			$oReflection = new \ReflectionObject($data);
			$aMethods = $oReflection->getMethods();
				
			foreach ($aMethods as $oReflectionMethod ){
				if (substr($oReflectionMethod->name, 0, 3) == "set" && method_exists($data, "get" . substr($oReflectionMethod->name, 3)))
					$data->{$oReflectionMethod->name}(self::escapeHtml($data->{"get" . substr($oReflectionMethod->name, 3)}(), $charset));
			}
		}
		else if(!is_null($data) && trim($data) != '')
			$data = htmlentities($data, null, defined('PQD_CHARSET') ? PQD_CHARSET : $charset);
		
		return $data;
	}
	
	/**
	 * @param string $cnpj
	 * @return boolean
	 */
	public static function isCnpj($cnpj){
		$cnpj = self::onlyNumbers($cnpj);
	
		if(strlen($cnpj) != 14 || self::isValid($cnpj, "/(^0{14}$)|(^1{14}$)|(^2{14}$)|(^3{14}$)|(^4{14}$)|(^5{14}$)|(^6{14}$)|(^7{14}$)|(^8{14}$)|(^9{14}$)/")) 
			return false;
				
		
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
		
		if(strlen($cpf) != 11 || self::isValid($cpf, "/(^0{11}$)|(^1{11}$)|(^2{11}$)|(^3{11}$)|(^4{11}$)|(^5{11}$)|(^6{11}$)|(^7{11}$)|(^8{11}$)|(^9{11}$)/"))
			return false;
		
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
	public static function print_pre($var, $dump = true){
		echo "<pre>";

		print_r($var);
		if ($dump)
			var_dump($var);

		echo "</pre>";
	}
	
	public static function setParam($param, $value, $descricao = null, $tpValue = 0, $obs = null, $idSysModulo = 1){
	
		$result = self::getParam($param);
		
		$exceptions = PQDApp::getExceptions();
		$countExceptions = $exceptions->count();
		
		$db = PQDApp::getDb();
		$con = $db->getConnection();
	
		$return = false;
	
		if(!is_null($con)){
			
			if (is_null($result))
				$st = $db->getConnection()->prepare("INSERT INTO parametros (parametro, descricao, tipoValor, valor, obs, idSysModulo) VALUES(:parametro, :descricao, :tipoValor, :valor, :obs, :idSysModulo)");
			else
				$st = $db->getConnection()->prepare("UPDATE parametros SET descricao = :descricao, tipoValor = :tipoValor, valor = :valor, obs = :obs, idSysModulo = :idSysModulo WHERE parametro = :parametro");
			
			$st->bindParam(":parametro", $param, \PDO::PARAM_STR);
			$st->bindParam(":descricao", $descricao, \PDO::PARAM_STR);
			$st->bindParam(":tipoValor", $tpValue, \PDO::PARAM_INT);
			$st->bindParam(":valor", $value, \PDO::PARAM_STR);
			$st->bindParam(":obs", $obs, \PDO::PARAM_STR);
			$st->bindParam(":idSysModulo", $idSysModulo, \PDO::PARAM_INT);
			
			$return = $st->execute();
		}
	
		/*
		if(IS_DEVELOPMENT && $exceptions->count() > $countExceptions)
			echo $db->getExceptions()->getHtmlExceptions();
		*/
	
		return $return;
	}
	
	
	public static function getParam($param, $default = null){
		
		if(substr($param, strlen(APP_ENVIRONMENT) * -1) != strtoupper(APP_ENVIRONMENT)){
			$return = self::getParam($param . '_' . strtoupper(APP_ENVIRONMENT), null);
			
			if(!is_null($return))
				return $return;
		}
		
		$return = $default;
			
		$db = PQDApp::getDb();
		$con = $db->getConnection();
		
		if(!is_null($con)){
			
			$st = $db->getConnection()->prepare("SELECT valor, tipoValor FROM parametros WHERE parametro = :parametro");
			
			$st->bindParam(":parametro", $param, \PDO::PARAM_STR);
			if($st->execute() !== false){
				$result = $st->fetch(\PDO::FETCH_NAMED);
				
				if($result !== false){
					switch ($result['tipoValor']){
						case 1:
							$return = (int)$result['valor'];
						break;
						case 2:
							$return = (float)$result['valor'];
						break;
						case 3:
							$return = (boolean)$result['valor'];
						break;
						case 4:
							$return = explode(';', $result['valor']);
						break;
						default:
							$return = $result['valor'];
					}
				}
			}
		}
		
		return $return;
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
			$aSearch[] = '/(\/\*(.|\s)*?(\*\/))/'; //Comments /* */
			$aSearch[] = "/\/\/.*$/"; // Comments //
		}
		
		//Without HTML Comments <!-- -->
		if($commentsHTML){
			
			if($commentsJS)
				array_pop($aSearch); //remove comments from one line because when we have http:// it removes all code

			$aSearch[] = '/(\<\!\-\-(.|\s)*?(\-\-\>))/';
		}
		
		return preg_replace($aSearch, $aReplace, $string);
	}
	
	public static function getFileWithoutSpaces($file, $commentsJS = true, $commentsHTML = false, $escape = false){
		if(IS_DEVELOPMENT)
			return file_get_contents($file);
		else
			return self::withoutSpaces(file_get_contents($file), $commentsJS = true, $commentsHTML = false, $escape = false);
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
	
	public static function rgb2hex($rgb) {
		$hex = "#";
		$hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);
	
		return $hex; // returns the hex value including the number sign (#)
	}
	
	public static function contentType($type = 'json'){
		
		$contentType = 'text/html';
		
		switch ($type) {
			case 'json':
				$contentType = 'application/json';
			break;
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
			case 'text':
				$contentType = 'text/plain';
			break;
		}
		
		header('Content-Type: ' . $contentType, true);
	}
}