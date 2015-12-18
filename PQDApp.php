<?php
namespace PQD;

require_once 'PQDExceptions.php';
require_once 'PQDDb.php';

use PQD\PQDUtil as Util;

session_name('APP');
session_start();

/**
 * @author Willker Moraes Silva
 * @since 2015-09-25
 *
 */
class PQDApp {
	
	/**
	 * @var PQDApp
	 */
	private static $oPQDApp = null;
	
	/**
	 * @var array
	 */
	private $environments = array('admin' => '');
	
	/**
	 * @var string
	 */
	private $envDefault = 'admin';
	
	/**
	 * @var array
	 */
	private $secureEnv = array('admin' => 0);
	
	/**
	 * @var array
	 */
	private $aIniClasses = array();
	
	/**
	 * @var array
	 */
	private $aHostsEnv = array();
	
	/**
	 * @var PQDDb
	 */
	private static $PQDDb;
	
	/**
	 * @var PQDExceptions
	 */
	private static $exceptions;
	
	
	/**
	 * @var array
	 */
	private $aUrlRequest;
	
	/**
	 * @var array
	 */
	private $aUrlRequestPublic;
	
	/**
	 * @var array
	 */
	private $aFreePaths = array();
	
	private $logController;
	
	private $logAction;
	
	
	public function __construct($appPath, $environments = 'admin', $environmentDefault = 'admin'){
		
		if(!is_null(self::$oPQDApp))
			throw new \Exception("Aplicação já Iniciada!", 10);
		
		$this->getExceptions();
		$this->getDb();
		
		$this->environments = is_array($environments) ? $environments : array($environments => '');
		$this->envDefault = $environmentDefault;
		
		define('APP_PATH', $appPath);
		
		if(!defined('APP_DEBUG'))
			define('APP_DEBUG', false);
		
		self::$oPQDApp = $this;
		
		if (!file_exists(APP_PATH . 'logs/')){
			if(mkdir(APP_PATH . 'logs/', 0777, true) === false)
				$this->getExceptions()->setException(new \Exception("Erro ao Criar diretório de LOG!", 6));
		}
	}
	
	public static function getApp(){
		return self::$oPQDApp;
	}
	
	public static function run($appPath, $environments, $environmentDefault){
		
		if (IS_DEVELOPMENT)
			ini_set("display_errors", "On");
		else
			ini_set("display_errors", "Off");
		
		$oApp = new self($appPath, $environments, $environmentDefault);
		$oApp->iniApp();
		
		return $oApp;
	}
	
	/**
	 * @return PQDExceptions
	 */
	public function getExceptions(){
		
		if(is_null(self::$exceptions))
			self::$exceptions = new PQDExceptions();
			
		return self::$exceptions;
		//return self::$exceptions = is_null(self::$exceptions) ? new PQDExceptions(): self::$exceptions;
	}
	
	/**
	 * @return PQDDb
	 */
	public function getDb(){
		return self::$PQDDb = is_null(self::$PQDDb) ? new PQDDb($this->getExceptions()): self::$PQDDb;
	}
	
	/**
	 * @param string $driver
	 * @param string $host
	 * @param string $dbName
	 * @param string $user
	 * @param string $password
	 * @param string $port
	 * @return number $index
	 */
	public function setDbConnection($driver, $host, $dbName, $user, $password, $port = null){
		return PQDDb::setDbConnection($driver, $host, $dbName, $user, $password, $port = null);
	}
	
	public function setTemplates($head = 'templates/tpl.head.php', $footer = 'templates/tpl.footer.php'){
		if (!is_null($head) && !empty($head))
			define('APP_TEMPLATE_HEAD', $head);
		
		if (!is_null($footer) && !empty($footer))
			define('APP_TEMPLATE_FOOTER', $footer);
	}
	
	/**
	 * Classes que deverão ser inicializadas antes do método view(), essas classes devem conter o metodo estatico run como public
	 * 
	 * @param array|string $classes
	 */
	public function setIniClasses($classes){
		$this->aIniClasses = is_array($classes) ? $classes : array($classes);
	}
	
	/**
	 * Ambientes que exigem atutenticação
	 * 
	 * @param array|string $environments
	 */
	public function setSecureEnv($environments){
		$this->secureEnv = is_array($environments) ? array_flip($environments) : array($environments => 0);
	}
	
	/**
	 * Mapeamento de hosts aos ambientes exemplo: array('lotus.com.br' => 'admin')
	 * 
	 * @param array $aHostsEnv
	 */
	public function setHostsEnv(array $aHostsEnv){
		$this->aHostsEnv = $aHostsEnv;
	}
	
	/**
	 * @return array
	 */
	public function getHostsEnv(){
		return $this->aHostsEnv;
	}
	
	/**
	 * Caminhoes livres que podem ser acessados de qualquer ambiente
	 * 
	 * @param array $paths
	 */
	public function setFreePaths(array $paths){
		$this->aFreePaths = array_flip($paths);
	}
	
	public function getEnvironments(){
		return array_keys($this->environments);
	}
	
	private function iniClasses(){
		foreach ($this->aIniClasses as $class){
			if( class_exists($class) &&  method_exists($class, 'run'))
				$class::run();
		}
	}
	public function view(){
		
		$this->setConstants();
		$this->iniClasses();
		
		if (isset($this->secureEnv[APP_ENVIRONMENT]) && !isset($_SESSION[APP_ENVIRONMENT]) && substr(APP_URL, 0, 5) != 'login'){
			header('Location: ' . APP_URL_ENVIRONMENT . 'login/' . APP_URL . (($_SERVER['QUERY_STRING'] != '') ? '?' . $_SERVER['QUERY_STRING'] : ''));
			exit();
		}
		
		//Requisitando arquivos necessários
		
		if (APP_URL == '')
			$modulo = "home";
		else{
			if( isset($this->secureEnv[APP_ENVIRONMENT]) && $this->aUrlRequestPublic[0] == "login")
				$modulo = "login";
			else
				$modulo = APP_URL;
		}
		
		//Mapeando ambientes, somente não mapea para as urls livres
		if(!isset($this->aFreePaths[APP_URL]))
			$modulo = $this->environments[APP_ENVIRONMENT] . $modulo;

		//Para aceitar modulos como "cadastre-se", "quem-somos"
		if(strstr($modulo, '-')){
			$modulo  = preg_split("[-]", $modulo);
			$modulo = $modulo[0] . join('', array_map("ucwords", array_slice($modulo, 1)));
		}

		if(isset($_GET['rst']))
			Util::contentType($_GET['rst']);
		
		if (is_dir(APP_PATH . 'modulos/' . $modulo)){
			if (!isset($this->aFreePaths[APP_URL]) && $modulo != $this->environments[APP_ENVIRONMENT] . "login" && $modulo != $this->environments[APP_ENVIRONMENT] . "home" && isset($this->secureEnv[APP_ENVIRONMENT]) && !isset($_SESSION[APP_ENVIRONMENT]['acessos'][APP_URL]))
				$this->httpError(403);
			else{
				$ctrl = ucwords(basename(APP_PATH . 'modulos/' . $modulo)) . 'Ctrl';
				$file = APP_PATH . 'modulos/' . $modulo . '/' . $ctrl . '.php';
				$ctrl = str_replace('/', "\\", '/modulos/' . $modulo . '/' . $ctrl);
		
				if(file_exists($file)){
					require_once $file;
					if(class_exists($ctrl)){
						
						$obj = new $ctrl($_POST, $_GET, $_SESSION, self::$exceptions, $_FILES);
						$this->logController = $ctrl;
						$act = isset($_GET['act']) ? $_GET['act'] : 'view';
						
						if(count($this->aUrlRequestPublic) > 1 && $this->aUrlRequestPublic[0] == 'login' && !method_exists($obj, $act))
							$act = 'view';
						
						if(!method_exists($obj, $act))
							$this->httpError(500);
						else{
							$this->logAction = $act;
							$obj->{$act}();
						}
					}
					else {
						self::$exceptions->setException(new \Exception("Classe (". $ctrl .") não encontrada!"));
						$this->httpError(500);
					}
				}
				else{
					self::$exceptions->setException(new \Exception("Arquivo (". $file .".php) não encontrado!"));
					$this->httpError(500);
				}
			}
		}
		else
			$this->httpError(404);
	}
	
	public function httpError($httpError){
		
		http_response_code($httpError);
		$oView = new PQDView('templates/' . $httpError. '.php', self::$exceptions);
		
		switch ($httpError) {
			case 403:
				$oView->title = ': 403 Acesso Proibido(Forbidden)';
			break;
			case 404:
				$oView->title = ': 404 N&atilde;o Encontrado(Not Found)';
			break;
			case 500:
				$oView->title = ': 500 Erro interno do Servidor(Internal Server Error)';
			break;
		}
		if(isset($_GET['rst'])){
			switch ($_GET['rst']){
				case 'json':
					$oView->setAutoRender(false);
					echo '{"result": "' . Util::utf8_encode(html_entity_decode(str_replace(": ", "", $oView->title))) . '", "errors": ' . $this->getExceptions()->getJsonExceptions() . '}';
				break;
			}
		}
	}
	
	private function iniApp() {
		chdir(APP_PATH);
		$this->setIncludePath();
	}
	
	private function setConstants() {
		
		//Verificando se está rodando a partir dá pasta /public
		if(strstr($_SERVER['REQUEST_URI'], basename(APP_PATH) . '/public')){
			$url = preg_split('/' . basename(APP_PATH) . '\/public/', $_SERVER['REQUEST_URI']);
			define('APP_URL_PUBLIC', $url[0] . basename(APP_PATH) . '/public/');
			$url = str_replace('?' . $_SERVER['QUERY_STRING'], '', $url[1]);
		}
		else{
			define('APP_URL_PUBLIC', '/');
			$url = str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
		}
		
		$path = array_values(array_filter(explode("/", $url)));
		$pathPublic = $path;//Caminho a partir dá pasta /public
		if (count($path) > 0) {
		
			if(isset($this->environments[$path[0]])){
				$pathPublic = array_slice($path, 1);
				$pathString = join('/', $pathPublic);
			}
			else{
				$pathString = join('/', $path);
				$pathPublic = $path;
			}
		
			define('APP_URL', $pathString);
		
			//Setando ambiente de trabalho
			if(isset($this->environments[$path[0]])){
				define("APP_ENVIRONMENT", $path[0]);
				define('APP_URL_ENVIRONMENT', APP_URL_PUBLIC . APP_ENVIRONMENT . '/');
			}
			else{
				//Quando o ambiente é mapeando em algum host
				if(isset($this->aHostsEnv[$_SERVER['HTTP_HOST']]) && isset($this->environments[$this->aHostsEnv[$_SERVER['HTTP_HOST']]]))
					define("APP_ENVIRONMENT", $this->aHostsEnv[$_SERVER['HTTP_HOST']]);
				else
					define("APP_ENVIRONMENT", $this->envDefault);
				
				define('APP_URL_ENVIRONMENT', APP_URL_PUBLIC);
			}
		}
		else{
			//Quando o ambiente é mapeando em algum host
			if(isset($this->aHostsEnv[$_SERVER['HTTP_HOST']]) && isset($this->environments[$this->aHostsEnv[$_SERVER['HTTP_HOST']]]))
				define("APP_ENVIRONMENT", $this->aHostsEnv[$_SERVER['HTTP_HOST']]);
			else
				define("APP_ENVIRONMENT", $this->envDefault);
			
			define('APP_URL', '');
			define('APP_URL_ENVIRONMENT', APP_URL_PUBLIC);
		}
		
		$this->aUrlRequest = $path;
		$this->aUrlRequestPublic = $pathPublic;
	}
		
	private function setIncludePath() {
		//Default Includes
		set_include_path(
			get_include_path() . PATH_SEPARATOR .
			APP_PATH . "/libs/" . PATH_SEPARATOR .
			APP_PATH
		);
	
		spl_autoload_extensions(".php");
		spl_autoload_register(function($class){
			$class = str_replace('\\', '/', $class);
			$found = stream_resolve_include_path($class . ".php");
	
			if($found !== false)
				require_once $found;
		});
	}
	
	public function __destruct() {
		
		if (file_exists(APP_PATH . 'logs/')){
			
			$log = array(
	 			'environment' => APP_ENVIRONMENT,
				'date' => time(),
				'ip' => $_SERVER['REMOTE_ADDR'],
				'http_user' => isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null,
				'user_id' => isset($_SESSION['user']['idUsuario']) ? $_SESSION['user']['idUsuario'] : null,
				'user' => isset($_SESSION['user']['login']) ? $_SESSION['user']['login'] : null,
				'request_uri' => $_SERVER['REQUEST_URI'],
				'controller' => $this->logController,
				'action' => $this->logAction,
				'app_url' => APP_URL,
				'app_url_public' => APP_URL_PUBLIC,
				'method' => $_SERVER['REQUEST_METHOD'],
				'http_response' => http_response_code()
			);
			
			$f = fopen(APP_PATH . 'logs/access-log-' . date('y-m-W') . '.log', 'a');
			if($f === false){
				throw new \Exception("Erro ao Criar arquivo de LOG!", 7);
			}
			else{
				fwrite($f, Util::json_encode($log) . PHP_EOL);
				fclose($f);
				
				if (self::$exceptions->count() > 0) {
					$f = fopen(APP_PATH . 'logs/error-log-' . date('y-m-W') . '.log', 'a');
					fwrite($f, '{"date": ' . time() . ', "exceptions": ' . self::$exceptions->getJsonExceptions(true) . '}' . PHP_EOL);
					fclose($f);
				}
			}
		}
	}
}