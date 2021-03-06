<?php
use Snails\Core\Snail;
define("DS", DIRECTORY_SEPARATOR);
define("ROOTDIR", dirname(__FILE__) . DS);
define("APPNAME", 'app');
define("MODULES" , 'modules');
define("MODULE_EXT", 'Module');
define("DB_KEY", 'db');
define("REDIS_KEY", 'redis');
error_reporting(1);

/**
 * load autoloader
 */
if (file_exists(ROOTDIR . 'Snails/vendor/autoload.php')) {
	require ROOTDIR . 'Snails/vendor/autoload.php';
} else {
	throw new Exception('Load Class File not Found!');
}

//加载配置文件
require_once ROOTDIR . 'config/comm.conf.php';

//引入核心加载类
$argv = $_SERVER['argv'];
unset($argv[0]);
Snail::run($argv);
