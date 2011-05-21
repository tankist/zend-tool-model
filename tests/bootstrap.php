<?php
// Define path to application directory
defined('TESTS_PATH')
    || define('TESTS_PATH', realpath(dirname(__FILE__)));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(TESTS_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Loader/Autoloader.php';
$autoload = Zend_Loader_Autoloader::getInstance();
$autoload->registerNamespace('Skaya_');

$cache = Zend_Cache::factory(
	'Core',
	'File',
	array(
		
	),
	array(
		'cache_dir' => realpath(TESTS_PATH . '/../cache'),
		'read_control_type' => 'adler32'
	)
);

Zend_Registry::set('cache', $cache);