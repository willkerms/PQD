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
	 * @return the $exceptions
	 */
	public function getExceptions() {
		return self::$exceptions;
	}
	
	/**
	 *
	 * @param boolean $development
	 * @return string
	 */
	public function getJsonExceptions($development = IS_DEVELOPMENT){
	
		$aExceptions = $this->getExceptions();
		$aExceptionsJSON = array();
	
		foreach ($aExceptions as $e){
			
			if( $this->ignoreOnProduction($e, $development) )
				continue;
					
				if ($development === true) {
					$aExceptionsJSON[] = array(
						'code' => $e->getCode(),
						'file' => $e->getFile() . ":" . $e->getLine(),
						'traceString' => $e->getTraceAsString(),
						'trace' => $e->getTrace(),
						'message' => $e->getMessage()
					);
				}
				else{
					$aExceptionsJSON[] = array(
						'message' => $e->getMessage()
					);
				}
		}
	
		return PQDUtil::json_encode($aExceptionsJSON);
	}
	
	/**
	 *
	 * @param boolean $development
	 * @return string
	 */
	public function getHtmlExceptions($development = IS_DEVELOPMENT){
	
		$aExceptions = $this->getExceptions();
		$html = '';
	
		foreach ($aExceptions as $key => $e){
			
			if( $this->ignoreOnProduction($e, $development) )
				continue;
					
				if ($development === true) {
	
					$pre = '<pre>';
					$pre .= 'code => '. $e->getCode() . PHP_EOL;
					$pre .= 'file => ' . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
					$pre .= 'message => ' . PQDUtil::escapeHtml($e->getMessage()) . PHP_EOL;
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
					$html .= '<td class="errors-msg">' . PQDUtil::escapeHtml($e->getMessage()) . '</td>';
					$html .= '</tr>';
				}
		}
	
		if($html != ''){
			
			if($development)
				$html = '<table class="table table-striped table-bordered table-condensed table-errors"><tr class="danger"><th>&nbsp;</th><th>Erro(s)</th></tr>' . $html;
			else
				$html = '<table class="table table-striped table-bordered table-errors">' . $html;
			
			$html .= '</table>';
		}
	
		return $html;
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