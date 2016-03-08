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
	private $aFinalClasses = array();
	
	/**
	 * @var array
	 */
	private $aHostsEnv = array();
	
	/**
	 * @var PQDDb
	 */
	private $PQDDb;
	
	/**
	 * @var PQDExceptions
	 */
	private $exceptions;
	
	
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
	
	
	private function __construct($appPath, $environments = 'admin', $environmentDefault = 'admin'){
		
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
		
		return (new self($appPath, $environments, $environmentDefault))->iniApp();
	}
	
	/**
	 * @return PQDExceptions
	 */
	public function getExceptions(){
		return $this->exceptions = is_null($this->exceptions) ? new PQDExceptions() : $this->exceptions;
	}
	
	/**
	 * @return PQDDb
	 */
	public function getDb(){
		return $this->PQDDb = is_null($this->PQDDb) ? new PQDDb($this->getExceptions()): $this->PQDDb;
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

	/**
	 * Seta templates que serão utilizados na view
	 * 
	 * @param string $head
	 * @param string $footer
	 * @return PQDApp
	 */
	public function setTemplates($head = 'templates/tpl.head.php', $footer = 'templates/tpl.footer.php'){
		if (!is_null($head) && !empty($head))
			define('APP_TEMPLATE_HEAD', $head);
		
		if (!is_null($footer) && !empty($footer))
			define('APP_TEMPLATE_FOOTER', $footer);
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getIniClasses(){
		return $this->aIniClasses;
	}
	
	/**
	 * Classes que deverão ser inicializadas antes do método view(), essas classes devem conter o metodo estatico run como public
	 * 
	 * @param array|string $classes
	 * @return PQDApp
	 */
	public function setIniClasses($classes){
		$this->aIniClasses = is_array($classes) ? $classes : array($classes);
		return $this;
	}
	
	/**
	 * Adiciona uma classe a ser inicializada
	 * 
	 * @param string $classes
	 * @return PQDApp
	 */
	public function addIniClasses($classes){
		array_push($this->aIniClasses, $classes);
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getFinalClasses(){
		return $this->aFinalClasses;
	}
	
	/**
	 * Classes que deverão ser inicializadas após a execução
	 * 
	 * @param array|string $classes
	 * @return PQDApp
	 */
	public function setFinalClasses($classes){
		$this->aFinalClasses = is_array($classes) ? $classes : array($classes);
		return $this;
	}
	
	/**
	 * Adiciona uma classe a ser inicializada após a execução
	 * 
	 * @param string $classes
	 * @return PQDApp
	 */
	public function addFinalClasses($classes){
		array_push($this->aFinalClasses, $classes);
		return $this;
	}
	
	/**
	 * Ambientes que exigem atutenticação
	 * 
	 * @param array|string $environments
	 * @return PQDApp
	 */
	public function setSecureEnv($environments){
		$this->secureEnv = is_array($environments) ? array_flip($environments) : array($environments => 0);
		return $this;
	}
	/**
	 * Adiciona um ambientes que exige atutenticação
	 * 
	 * @param string $environments
	 * @return PQDApp
	 */
	public function addSecureEnv($environments){
		$this->secureEnv[$environments] = count($this->secureEnv);
		return $this;
	}
	
	/**
	 * Mapeamento de hosts aos ambientes exemplo: array('lotus.com.br' => 'admin')
	 * 
	 * @param array $aHostsEnv
	 * @return PQDApp
	 */
	public function setHostsEnv(array $aHostsEnv){
		$this->aHostsEnv = $aHostsEnv;
		return $this;
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
		return $this;
	}
	
	public function getEnvironments(){
		return array_keys($this->environments);
	}
	
	private function runClasses($aClasses){
		foreach ($aClasses as $class){
			if( class_exists($class) &&  method_exists($class, 'run'))
				$class::run();
		}
	}
	
	private function isSafePath(){
		//Quando o ambiente e mapeado por host, e este ambiente esta acessando outro ambiente, a url a ser considerada é a do ambiente acessado.
		if(!IS_CLI && isset($this->aHostsEnv[$_SERVER['HTTP_HOST']]) && APP_ENVIRONMENT != $this->aHostsEnv[$_SERVER['HTTP_HOST']])
			$url = APP_ENVIRONMENT . '/' . APP_URL;
		else 
			$url = APP_URL;
		
		return 
			isset($this->secureEnv[APP_ENVIRONMENT]) && 
			!isset($_SESSION[APP_ENVIRONMENT]) && 
			substr(APP_URL, 0, 5) != 'login' && 
			!isset($this->aFreePaths[$url]);
	}
	
	/**
	 * Executa a aplicação!
	 * 
	 */
	public function exec(){
		
		$this->setConstants();
		$this->runClasses($this->aIniClasses);
		
		if ($this->isSafePath() && !IS_CLI){
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
			if (!IS_CLI && !isset($this->aFreePaths[APP_URL]) && $modulo != $this->environments[APP_ENVIRONMENT] . "login" && $modulo != $this->environments[APP_ENVIRONMENT] . "home" && isset($this->secureEnv[APP_ENVIRONMENT]) && !isset($_SESSION[APP_ENVIRONMENT]['acessos'][APP_URL]) && !isset($this->aFreePaths[APP_ENVIRONMENT . '/' . APP_URL]))
				$this->httpError(403);
			else{
				$ctrl = ucwords(basename(APP_PATH . 'modulos/' . $modulo)) . 'Ctrl';
				$file = APP_PATH . 'modulos/' . $modulo . '/' . $ctrl . '.php';
				$ctrl = str_replace('/', "\\", '/modulos/' . $modulo . '/' . $ctrl);
		
				if(file_exists($file)){
					require_once $file;
					if(class_exists($ctrl)){
						
						$obj = new $ctrl($_POST, $_GET, $_SESSION, $this->exceptions, $_FILES);
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
						$this->exceptions->setException(new \Exception("Classe (". $ctrl .") não encontrada!"));
						$this->httpError(500);
					}
				}
				else{
					$this->exceptions->setException(new \Exception("Arquivo (". $file .".php) não encontrado!"));
					$this->httpError(500);
				}
			}
		}
		else
			$this->httpError(404);
	}
	
	public function httpError($httpError){
		
		http_response_code($httpError);
		$oView = new PQDView('templates/' . $httpError. '.php', $this->exceptions);
		
		switch ($httpError) {
			case 403:
				$oView->title = '403 Acesso Proibido(Forbidden)';
			break;
			case 404:
				$oView->title = '404 N&atilde;o Encontrado(Not Found)';
			break;
			case 500:
				$oView->title = '500 Erro interno do Servidor(Internal Server Error)';
			break;
		}
		
		if(isset($_GET['rst'])){
			switch ($_GET['rst']){
				case 'json':
					$oView->setAutoRender(false);
					
					$title = html_entity_decode(str_replace(": ", "", $oView->title));
					$this->getExceptions()->setException( new \Exception($title));
					
					echo '{"result": "' . Util::utf8_encode($title) . '", "errors": ' . $this->getExceptions()->getJsonExceptions() . '}';
				break;
			}
		}
		
		if(IS_CLI){
			$oView->setAutoRender(false);
			echo $oView->title . PHP_EOL;
		}
	}
	
	/**
	 * Seta o diretório para a raiz do projeto e inicializa a função de include das classes
	 * 
	 * @return PQDApp
	 */
	private function iniApp() {
		define("APP_CWD", getcwd());
		chdir(APP_PATH);
		$this->setIncludePath();
		return $this;
	}
	private function setSapiConstants(){
		switch (php_sapi_name()) {
			case "cli":
				define('IS_APACHE', false);
				define('IS_CGI', false);
				define('IS_CLI', true);
			break;
			case "apache":
				define('IS_APACHE', true);
				define('IS_CGI', false);
				define('IS_CLI', false);
			break;
			case "cgi":
				define('IS_APACHE', false);
				define('IS_CGI', true);
				define('IS_CLI', false);
			break;
			default:
				define('IS_APACHE', false);
				define('IS_CGI', false);
				define('IS_CLI', false);
			break;
		}
	}
	
	private function setConstants() {
		
		$this->setSapiConstants();
		
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
				if(isset($_SERVER['HTTP_HOST']) && isset($this->aHostsEnv[$_SERVER['HTTP_HOST']]) && isset($this->environments[$this->aHostsEnv[$_SERVER['HTTP_HOST']]]))
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
		$this->runClasses($this->aFinalClasses);
		if (file_exists(APP_PATH . 'logs/')){
			
			$log = array(
	 			'environment' => APP_ENVIRONMENT,
				'date' => time(),
				'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '::1',
				'http_user' => isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null,
				'user_id' => isset($_SESSION['user']['idUsuario']) ? $_SESSION['user']['idUsuario'] : null,
				'user' => isset($_SESSION['user']['login']) ? $_SESSION['user']['login'] : null,
				'request_uri' => $_SERVER['REQUEST_URI'],
				'host' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST']: null,
				'controller' => $this->logController,
				'action' => $this->logAction,
				'app_url' => APP_URL,
				'app_url_public' => APP_URL_PUBLIC,
				'method' => isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null,
				'http_response' => http_response_code()
			);
			
			$f = fopen(APP_PATH . 'logs/access-log-' . date('y-m-W') . '.log', 'a');
			if($f === false){
				throw new \Exception("Erro ao Criar arquivo de LOG!", 7);
			}
			else{
				fwrite($f, Util::json_encode($log) . PHP_EOL);
				fclose($f);
				
				if ($this->exceptions->count() > 0) {
					$f = fopen(APP_PATH . 'logs/error-log-' . date('y-m-W') . '.log', 'a');
					fwrite($f, '{"date": ' . time() . ', "exceptions": ' . $this->exceptions->getJsonExceptions(true) . '}' . PHP_EOL);
					fclose($f);
				}
			}
		}
	}
}