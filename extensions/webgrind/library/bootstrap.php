<?php
/**
 * Bootstrap process for webgrind tools.
 * 
*/
define('WEBGRIND_ROOT', realpath(dirname(__FILE__).'/..'));
define('WEBGRIND_LIBPATH', WEBGRIND_ROOT.'/library');

use li3_perf\extensions\webgrind\library\Webgrind;

//require_once 'Webgrind.php';
require_once WEBGRIND_LIBPATH.'/functions.php';
//require_once WEBGRIND_LIBPATH.'/FileHandler.php';

Webgrind::init(WEBGRIND_ROOT.'/config.php');
?>