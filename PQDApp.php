<?php
namespace PQD;

require_once 'PQDExceptions.php';
require_once 'PQDDb.php';

use PQD\PQDUtil as Util;

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
	 * @var array
	 */
	private $aEvnTranslateFile = array();

	/**
	 * @var array
	 */
	private $aAlias = array();

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

	private $logModulo;
	
	/**
	 * @var array
	 */
	private $aCharModifyUrl = array(
		'-'
	);

	/**
	 * POST que será passado ao controller
	 * 
	 * @var array
	 */
	private $_POST = null;
	
	/**
	 * GET que será passado ao controller
	 * 
	 * @var array
	 */
	private $_GET = null;
	
	/**
	 * SESSION que será passado ao controller
	 * 
	 * @var array
	 */
	private $_SESSION = null;
	
	/**
	 * FILES que será passado ao controller
	 * 
	 * @var array
	 */
	private $_FILES = null;
	
	/**
	 * Ação que será passada procurado no get para traduzir em um metodo no controller
	 * 
	 * @var string
	 */
	private $action = 'act';

	/**
	 * Ação Padrão
	 * 
	 * @var string
	 */
	private $defaultAction = 'view';

	/**
	 * Inicia a sessão automaticamente
	 * 
	 * @var bool
	 */
	private $sessionAutoStart = true;

	/**
	 * Nome do cookie de sessão
	 * 
	 * @var string
	 */
	private $sessionName = 'APP';

	/**
	 * Source Folder
	 * 
	 * @var string
	 */
	private $sourceFolder = 'modulos/';

	/**
	 * Passar os caminhos absolutos das pastas
	 *
	 * @param string $appPath
	 * @param array|string $environments
	 * @param string $environmentDefault
	 * @param string $publicPath
	 * @throws \Exception
	 */
	private function __construct($appPath, $environments = 'admin', $environmentDefault = 'admin', $publicPath = 'public/'){

		if(!is_null(self::$oPQDApp))
			throw new \Exception("Aplicação já Iniciada!", 10);

		$this->getExceptions();
		$this->getDb();

		$this->environments = is_array($environments) ? $environments : array($environments => '');
		$this->envDefault = $environmentDefault;

		define('APP_PATH', substr($appPath, -1) != '/' ? $appPath . '/' : $appPath);
		define('APP_PATH_PUBLIC', $publicPath == 'public/' ? APP_PATH . $publicPath : $publicPath);

		if(!defined('APP_DEBUG'))
			define('APP_DEBUG', false);

		if(!defined('PQD_ORM_FORMAT_FIELD'))
			define('PQD_ORM_FORMAT_FIELD', true);

		self::$oPQDApp = $this;
	}

	public static function getApp(){
		return self::$oPQDApp;
	}

	/**
	 * Passar os caminhos absolutos nas variáveis $appPath, $publicPath
	 *
	 * @param string $appPath
	 * @param string|array $environments
	 * @param string $environmentDefault
	 * @param string $publicPath
	 *
	 * @return \PQD\PQDApp
	 */
	public static function run($appPath, $environments, $environmentDefault, $publicPath = 'public/'){

		if (substr($appPath, -1) != '/' && substr($appPath, -1) != '\\')
			$appPath .= '/';

		if(!defined('IS_DEVELOPMENT'))
			define('IS_DEVELOPMENT', false);

		if (IS_DEVELOPMENT)
			ini_set("display_errors", "On");
		else
			ini_set("display_errors", "Off");

		if(!isset($_SERVER['QUERY_STRING']))
			$_SERVER['QUERY_STRING'] = null;

		return (new self($appPath, $environments, $environmentDefault, $publicPath))->iniApp();
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
	public function setDbConnection($driver, $host, $dbName, $user, $password, $port = null, array $options = array(), $dsn = null){
		return PQDDb::setDbConnection($driver, $host, $dbName, $user, $password, $port, $options, $dsn);
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
	 * Ambientes para a tradução de arquivos para diretorio/controllers
	 *
	 * @param array|string $classes
	 * @return PQDApp
	 */
	public function setEvnTranslateFile($environments){
		$this->aEvnTranslateFile = is_array($environments) ? array_flip($environments) : array($environments => 0);
		return $this;
	}

	/**
	 * Seta os alias de url
	 *
	 * @param array $aAlias
	 * @return PQDApp
	 */
	public function setAlias(array $aAlias){
		$this->aAlias = $aAlias;
		return $this;
	}

	/**
	 * Adiciona um alias a uma url.
	 *
	 * Exemplo: se a url requisitada for admin pode-se mapear para sys/admin
	 *
	 * @param string $origin
	 * @param string $dest
	 * @return PQDApp
	 */
	public function addAlias($origin, $dest){
		$this->aAlias[$origin] = $dest;
		return $this;
	}

	/**
	 * Adiciona um ambiente para a tradução de arquivos para diretorio/controllers
	 *
	 * @param string $classes
	 * @return PQDApp
	 */
	public function addEvnTranslateFile($environment){
		$this->aEvnTranslateFile[$environment] = count($this->aEvnTranslateFile);
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

	/**
	 * Adiciona caminhos livres que podem ser acessados de qualquer ambiente
	 *
	 * @param string $paths
	 */
	public function addFreePaths($paths){
		$this->aFreePaths[$paths] = count($this->aFreePaths);
		return $this;
	}

	public function addEnvironment($environment, $path){
		$this->environments[$environment] = $path;
		return $this;
	}

	public function getEnvironments(){
		return array_keys($this->environments);
	}

	public function getEnvironmentsPaths(){
		return $this->environments;
	}

	public function getCharModifyUrl(){
		return $this->aCharModifyUrl;
	}

	public function addCharModifyUrl($char){
		$this->aCharModifyUrl[] = $char;
		return $this;
	}

	public function setCharModifyUrl(array $aChars){
		$this->aCharModifyUrl = $aChars;
		return $this;
	}

	/**
	 * Retor os alias setados
	 */
	public function getAlias(){
		return $this->aAlias;
	}

	public function getLogController(){
		return $this->logController;
	}

	public function getLogAction(){
		return $this->logAction;
	}

	public function getLogModulo(){
		return $this->logModulo;
	}

	public function getUrlRequest(){
		return $this->aUrlRequest;
	}

	public function getUrlRequestPublic(){
		return $this->aUrlRequestPublic;
	}

	private function runClasses($aClasses){
		foreach ($aClasses as $class){
			if( class_exists($class) &&  method_exists($class, 'run'))
				$class::run();
			else
				$this->getExceptions()->setException(new PQDExceptionsDev("Classe(" . $class . ") ou metodo(run) não encontrado!"));
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

		if( $this->sessionAutoStart ){
			session_name($this->getSessionName());
			session_start();
		}

		$this->setConstants();//Seta as contantes
		$this->runClasses($this->aIniClasses);//Inicia as classes que devem ser iniciadas antes da aplicação

		if ($this->isSafePath() && !IS_CLI){ //Verifica se é uma url que deve estar autenticado
			header('Location: ' . APP_URL_ENVIRONMENT . 'login/' . APP_URL . (($_SERVER['QUERY_STRING'] != '') ? '?' . $_SERVER['QUERY_STRING'] : ''));
			exit();
		}

		if (APP_URL == '')//Quando não requisita nenhuma url aponta para o home
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
		if(count($this->aCharModifyUrl) > 0){
			foreach($this->aCharModifyUrl as $char){
				if(strstr($modulo, $char)){
					$modulo  = explode($char, $modulo);
					$modulo = $modulo[0] . join('', array_map("ucwords", array_slice($modulo, 1)));
				}
			}
		}

		if(isset($_GET['rst']) && !IS_CLI)
			Util::contentType($_GET['rst']);

		if(isset($this->aEvnTranslateFile[APP_ENVIRONMENT]) && !is_dir(APP_PATH . $this->getSourceFolder() . $modulo)){
			$pathinfo = pathinfo($modulo);

			if(is_dir(APP_PATH . $this->getSourceFolder() . $pathinfo['dirname'] . '/' . $pathinfo['filename']))
				$modulo = $pathinfo['dirname'] . '/' . $pathinfo['filename'];
		}

		/**
		 * Melhoria para poder fazer o redirecionamento de qualquer url, inclusive o home!
		 * @since 2019-08-24
		 */
		$homeEnv = $this->environments[APP_ENVIRONMENT] . "home";
		if(isset($this->aAlias[$modulo])){
			if ($modulo == $homeEnv)
				$homeEnv = $this->aAlias[$modulo];

			$modulo = $this->aAlias[$modulo];
		}

		$this->logModulo = $modulo;
		if (is_dir(APP_PATH . $this->getSourceFolder() . $modulo)){
			if (!IS_CLI && !isset($this->aFreePaths[APP_URL]) && $modulo != $this->environments[APP_ENVIRONMENT] . "login" && $modulo != $homeEnv && isset($this->secureEnv[APP_ENVIRONMENT]) && !isset($_SESSION[APP_ENVIRONMENT]['acessos'][APP_URL]) && !isset($this->aFreePaths[APP_ENVIRONMENT . '/' . APP_URL]))
				$this->httpError(403);
			else{
				$ctrl = ucwords(basename(APP_PATH . $this->getSourceFolder() . $modulo)) . 'Ctrl';
				$file = APP_PATH . $this->getSourceFolder() . $modulo . '/' . $ctrl . '.php';
				$ctrl = str_replace('/', "\\", '/' . $this->getSourceFolder() . $modulo . '/' . $ctrl);

				$this->execClass($file, $ctrl);//Executa a classe
			}
		}
		else{
			$this->exceptions->setException(new PQDExceptionsDev('(' . $this->getSourceFolder() . $modulo . ") não encontrado!"));
			$this->httpError(404);
		}
	}

	/**
	 * Executa o controller requisitado
	 *
	 * @param string $file
	 * @param string $ctrl
	 * @param string $modulo
	 */
	private function execClass($file, $ctrl){

		if(file_exists($file)){
			
			require_once $file;

			if(class_exists($ctrl)){

				$session = isset($_SESSION) ? $_SESSION : [];
				$session = is_null($this->_SESSION) ? $session : $this->getSession();

				$obj = new $ctrl((is_null($this->_POST) ? $_POST : $this->getPost()), (is_null($this->_GET) ? $_GET : $this->getGet()), $session, $this->exceptions, (is_null($this->_FILES) ? $_FILES : $this->getFiles()));

				$this->logController = $ctrl;
				$act = isset($_GET[$this->getAction()]) ? $_GET[$this->getAction()] : $this->getDefaultAction();

				//Para aceitar acoes do tipo: "?a=cadastre-se", "?a=quem-somos", "?a=quem-somos"
				if( count($this->aCharModifyUrl) > 0 ){
					foreach($this->aCharModifyUrl as $char){
						if(strstr($act, $char)){
							$act  = explode($char, $act);
							$act = $act[0] . join('', array_map("ucwords", array_slice($act, 1)));
						}
					}
				}

				if(count($this->aUrlRequestPublic) > 1 && $this->aUrlRequestPublic[0] == 'login' && !method_exists($obj, $act))
					$act = $this->getDefaultAction();

					if(!method_exists($obj, $act)){
						$this->exceptions->setException(new PQDExceptionsDev("Metodo não existe: $ctrl::$act!"));
						$this->httpError(500);
					}
					else{
						$this->logAction = $act;
						$obj->{$act}();
					}
			}
			else {
				$this->exceptions->setException(new PQDExceptionsDev("Classe (". $ctrl .") não encontrada!"));
				$this->httpError(500);
			}
		}
		else{
			$this->exceptions->setException(new PQDExceptionsDev("Arquivo (". $file .".php) não encontrado!"));
			$this->httpError(500);
		}
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
		define("APP_CWD", getcwd() . '/');
		chdir(APP_PATH);
		$this->setIncludePath();
		return $this;
	}

	private function setSapiConstants(){
		if(!defined('IS_APACHE')){
			switch (php_sapi_name()) {

				case "cli":
					define('IS_APACHE', false);
					define('IS_CGI', false);
					define('IS_CLI', true);
				break;

				case "apache":
				case "apache2handler":
				case "apache2filter":
					define('IS_APACHE', true);
					define('IS_CGI', false);
					define('IS_CLI', false);
				break;

				case "cgi-fcgi":
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
	}

	/**
	 * Seta constante IS_MOBILE, que verifica quando o sistema está sendo acessado via dispositivos moveis.
	 *
	 */
	private function setMobileConstants(){

		if (isset($_SERVER['HTTP_USER_AGENT']) && !empty($_SERVER['HTTP_USER_AGENT'])){
			$useragent = $_SERVER['HTTP_USER_AGENT'];
			if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
				define('IS_MOBILE', true);
			else
				define('IS_MOBILE', false);
		}
		else
			define('IS_MOBILE', false);
	}

	private function setConstants() {

		$this->setSapiConstants();

		$this->setMobileConstants();

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

		$path = array_values(array_filter(explode("/", $url), function($str){
			return trim($str) != "";
		}));
		
		$pathPublic = $path;//Caminho a partir dá pasta /public
		if (count($path) > 0) {

			if(isset($this->environments[$path[0]]) && !$this->isHostEnv()){
				$pathPublic = array_slice($path, 1);
				$pathString = join('/', $pathPublic);
			}
			else{
				$pathString = join('/', $path);
				$pathPublic = $path;
			}

			define('APP_URL', $pathString);

			//Setando ambiente de trabalho
			if(isset($this->environments[$path[0]]) && !$this->isHostEnv()){
				define("APP_ENVIRONMENT", $path[0]);
				define('APP_URL_ENVIRONMENT', APP_URL_PUBLIC . APP_ENVIRONMENT . '/');
			}
			else{
				//Quando o ambiente é mapeando em algum host
				if($this->isHostEnv())
					define("APP_ENVIRONMENT", $this->aHostsEnv[$_SERVER['HTTP_HOST']]);
				else
					define("APP_ENVIRONMENT", $this->envDefault);

				define('APP_URL_ENVIRONMENT', APP_URL_PUBLIC);
			}
		}
		else{
			//Quando o ambiente é mapeando em algum host
			if($this->isHostEnv())
				define("APP_ENVIRONMENT", $this->aHostsEnv[$_SERVER['HTTP_HOST']]);
			else
				define("APP_ENVIRONMENT", $this->envDefault);

			define('APP_URL', '');
			define('APP_URL_ENVIRONMENT', APP_URL_PUBLIC);
		}

		$this->aUrlRequest = $path;
		$this->aUrlRequestPublic = $pathPublic;
	}

	private function isHostEnv(){
		return isset($_SERVER['HTTP_HOST']) && isset($this->aHostsEnv[$_SERVER['HTTP_HOST']]) && isset($this->environments[$this->aHostsEnv[$_SERVER['HTTP_HOST']]]);
	}

	private function setIncludePath() {
		//Default Includes
		set_include_path(
			get_include_path() . PATH_SEPARATOR .
			APP_PATH . "libs/" . PATH_SEPARATOR .
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

	/**
	 * Seta o ambiente padrão
	 * 
	 * @param string $env
	 * 
	 * @return $this
	 */
	public function setDefaultEnv($env){

		if( isset( $this->environments[$env] ) )
			$this->envDefault = $env;
		
		return $this;
	}

	/**
	 * @param array $post
	 * 
	 * @return $this
	 */
	public function setPost($post){
		$this->_POST = $post;
		return $this;
	}
	
	/**
	 * @param array $get
	 * 
	 * @return $this
	 */
	public function setGet($get){
		$this->_GET = $get;
		return $this;
	}

	/**
	 * @param array $session
	 * 
	 * @return $this
	 */
	public function setSession($session){
		$this->_SESSION = $session;
		return $this;
	}

	/**
	 * @param array $files
	 * 
	 * @return $this
	 */
	public function setFiles($files){
		$this->_FILES = $files;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getPost(){
		return $this->_POST;
	}

	/**
	 * @return array
	 */
	public function getGet(){
		return $this->_GET;
	}

	/**
	 * @return array
	 */
	public function getSession(){
		return $this->_SESSION;
	}

	/**
	 * @return array
	 */
	public function getFiles(){
		return $this->_FILES;
	}

	/**
	 * @param string $action
	 * 
	 * @return $this
	 */
	public function setAction($action){
		$this->action = $action;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAction(){
		return $this->action;
	}

	/**
	 * @param string $defaultAction
	 * 
	 * @return $this
	 */
	public function setDefaultAction($defaultAction){
		$this->defaultAction = $defaultAction;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDefaultAction(){
		return $this->defaultAction;
	}

	/**
	 * @param bool $sessionAutoStart
	 * 
	 * @return $this
	 */
	public function setSessionAutoStart($sessionAutoStart){
		$this->sessionAutoStart = $sessionAutoStart;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getSessionAutoStart(){
		return $this->sessionAutoStart;
	}

	/**
	 * @param string $sessionName
	 * 
	 * @return $this
	 */
	public function setSessionName($sessionName){
		$this->sessionName = $sessionName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSessionName(){
		return $this->sessionName;
	}

	/**
	 * @param string $sourceFolder
	 * 
	 * @return $this
	 */
	public function setSourceFolder($sourceFolder){
		$this->sourceFolder = $sourceFolder;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSourceFolder(){
		return $this->sourceFolder;
	}

	public function __destruct() {
		$this->runClasses($this->aFinalClasses);
	}
}