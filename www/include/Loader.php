<?php 
define('IN_APP', true);

class Loader {
	public static function errnoToString($errno) {
		switch ($errno) {
			case E_NOTICE:
			case E_USER_NOTICE:
				return "Notice";
				break;
			case E_WARNING:
			case E_USER_WARNING:
				return "Warning";
				break;
			case E_ERROR:
			case E_USER_ERROR:
				return "Fatal Error";
				break;
			default:
				return "Error";
				break;
		}
	}
	
	public static function setErrorHandler() {
		$flags = (E_ALL | E_STRICT)/* & ~E_NOTICE*/;
		$errorHandler = function($errno, $errstr, $errfile, $errline) {
			global $isInErrorHandler;
			if (!$isInErrorHandler && class_exists('Config') && isset(Config::$config)) {
				$isInErrorHandler = true;
				try {
					DataBase::query("INSERT INTO ".Config::$config->dataBase->prefix."log ".
						"(time, message) VALUES(NOW(), '".DataBase::escape($errstr)."')");
				} catch (Exception $e) { }
				$isInErrorHandler = false;
			}
			
			// If failed to load config, show error message
			if (!class_exists('Config') || !isset(Config::$config) || !Config::$config->general->isDebugMode) {
				if ($errno & (E_ERROR | E_CORE_ERROR | E_ERROR | E_USER_ERROR))
					exit;
				else
					return;
			}
			echo "<b>".Loader::errnoToString($errno).":</b> ".$errfile.':'.$errline.': '.$errstr.'<br />';
			echo '<pre>';
			debug_print_backtrace();
			echo '</pre>';
		};
		
		set_error_handler($errorHandler, $flags);
	}
	
	public static function initAutoloader() {
		// __DIR__ not available on all servers
		if (!defined('ROOT_PATH'))
			define('ROOT_PATH', dirname(__FILE__).'/../');
		
		$callback = function($className) {
			$paths = array('include', 'models', 'controllers');
			
			foreach ($paths as $path) {
				$fileName = ROOT_PATH . $path. '/' . $className . '.php';
				if (file_exists($fileName)) {
					require_once($fileName);
					return;
				}
			}
		};
		spl_autoload_register($callback);
	}
	
	public static function loadConfig() {
	}
	
	public static function init() {
		self::setErrorHandler();
		self::initAutoloader();
	}
	
	public static function load() {
		self::init();
		self::loadConfig();
	}
}
