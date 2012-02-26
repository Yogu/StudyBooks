<?php 
define('IN_APP', true);

/* error handling */
set_error_handler("error", (E_ALL | E_STRICT)/* & ~E_NOTICE*/);
function errnoToString($errno) {
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

function error($errno, $errstr, $errfile, $errline) {
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
	echo "<b>".errnoToString($errno).":</b> ".$errfile.':'.$errline.': '.$errstr.'<br />';
	echo '<pre>';
	debug_print_backtrace();
	echo '</pre>';
}

// __DIR__ not available on all servers
define('ROOT_PATH', dirname(__FILE__).'/../');
define('ROOT_URL', substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/') + 1));

/* autoload */
function autoload($className) {
	$paths = array('include', 'models', 'controllers');
	
	foreach ($paths as $path) {
		$fileName = ROOT_PATH . $path. '/' . $className . '.php';
		if (file_exists($fileName)) {
			require_once($fileName);
			return;
		}
	}
}
spl_autoload_register(autoload);

/* undo magic quotes */
if (get_magic_quotes_gpc()) {
    $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
    while (list($key, $val) = each($process)) {
        foreach ($val as $k => $v) {
            unset($process[$key][$k]);
            if (is_array($v)) {
                $process[$key][stripslashes($k)] = $v;
                $process[] = &$process[$key][stripslashes($k)];
            } else {
                $process[$key][stripslashes($k)] = stripslashes($v);
            }
        }
    }
    unset($process);
}

function load() {
	Config::load();
	Language::load();
	setlocale(LC_ALL, Config::$config->general->locale);
}

if (!defined('NO_LOAD') || !NO_LOAD) {
	load();
}
