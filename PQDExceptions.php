<?php
namespace PQD;
/**
 *
 * @author Willker Moraes Silva
 * @since 2012-07-26
 *
 */
class PQDExceptions {
	/**
	 * @var array[\Exception]
	 */
	private static $exceptions = array();

	private function ignoreOnProduction(\Exception $e, $development = IS_DEVELOPMENT){
		return !$development && (($e instanceof PQDExceptionsDB) || ($e instanceof PQDExceptionsDev) || ($e instanceof \PDOException));
	}

	/**
	 * @return array[\Exception] $exceptions
	 */
	public function getExceptions() {
		return self::$exceptions;
	}

	/**
	 *
	 * @param boolean $development
	 * @return array
	 */
	public function getArrayExceptions($development = IS_DEVELOPMENT, $isJSON = false){

		$aExceptions = $this->getExceptions();
		$aExceptionsJSON = array();

		foreach ($aExceptions as $e){

			if( $this->ignoreOnProduction($e, $development) )
				continue;

				if ($development === true) {

					$aException = array(
						'code' => $e->getCode(),
						'message' => $e->getMessage(),
						'file' => $e->getFile() . ":" . $e->getLine(),
						'traceString' => $e->getTraceAsString()
					);

					if(!$isJSON)
						$aException['trace'] = $e->getTrace();

					$aExceptionsJSON[] = $aException;
				}
				else{
					$aExceptionsJSON[] = array(
						'message' => $e->getMessage()
					);
				}
		}

		return $aExceptionsJSON;
	}
	/**
	 *
	 * @param boolean $development
	 * @return string
	 */
	public function getJsonExceptions($development = IS_DEVELOPMENT, $utf8 = false){
		return PQDUtil::json_encode($this->getArrayExceptions($development, true), null, $utf8);
	}

	/**
	 *
	 * @param boolean $development
	 * @return string
	 */
	public function getHtmlExceptions($development = IS_DEVELOPMENT, $charset = null){

		$aExceptions = $this->getExceptions();
		$html = '';

		foreach ($aExceptions as $key => $e){

			if( $this->ignoreOnProduction($e, $development) )
				continue;

			if ($development === true) {

				$pre = '<pre>';
				$pre .= 'code => '. $e->getCode() . PHP_EOL;
				$pre .= 'message => ' . PQDUtil::escapeHtml($e->getMessage(), $charset) . PHP_EOL;
				$pre .= 'file => ' . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
				$pre .= 'trace => ' . $e->getTraceAsString();
				$pre .= '</pre>';

				$html .= '<tr>';
				$html .= '<td class="errors-count">' . $key . '</td>';
				$html .= '<td class="errors-msg">';
				$html .= $pre;
				$html .= '</td>';
				$html .= '</tr>';
			}
			else{
				$html .= '<tr>';
				$html .= '<td class="errors-count">' . $key . '</td>';
				$html .= '<td class="errors-msg">' . PQDUtil::escapeHtml($e->getMessage(), $charset) . '</td>';
				$html .= '</tr>';
			}
		}

		if($html != ''){

			if($development)
				$html = '<div class="table-responsive"><table class="table table-striped table-bordered table-condensed table-errors"><tr class="danger"><th>&nbsp;</th><th>Erro(s)</th></tr>' . $html;
			else
				$html = '<div class="table-responsive"><table class="table table-striped table-bordered table-errors">' . $html;

			$html .= '</table></div>';
		}

		return $html;
	}

	/**
	 *
	 * @param boolean $development
	 * @return string
	 */
	public function getTXTExceptions($development = IS_DEVELOPMENT){

		$aExceptions = $this->getExceptions();
		$txt = '';

		foreach ($aExceptions as $key => $e){

			if( $this->ignoreOnProduction($e, $development) )
				continue;

			if ($development === true) {

				$txt .= 'code => '. $e->getCode();
				$txt .= ', message => '. $e->getMessage() . PHP_EOL;
				$txt .= 'file => '. $e->getFile() . ":" . $e->getLine() . PHP_EOL;
				$txt .= "trace String: " . PHP_EOL . "\t" . join("\n\t", explode("\n", $e->getTraceAsString()))  . PHP_EOL;
			}
			else{
				$txt .= $e->getMessage() . PHP_EOL;
			}
		}

		return $txt;
	}

	/**
	 * @param \Exception $exception
	 */
	public function setException(\Exception $exception) {
		self::$exceptions[] = $exception;
	}

	/**
	 * @param array[\Exception] $exceptions
	 */
	public function setExceptions(array $exceptions) {
		self::$exceptions = $exceptions;
	}

	/**
	 * @return int
	 */
	public function count($development = IS_DEVELOPMENT) {
		if($development)
			return count(self::$exceptions);
		else{
			$count = 0;

			$aExceptions = $this->getExceptions();

			foreach ($aExceptions as $e){

				if( $this->ignoreOnProduction($e, $development) )
					continue;

				$count++;
			}

			return $count;
		}
	}
}