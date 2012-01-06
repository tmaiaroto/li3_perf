<?php
// require_once LIBPATH.'/Config.php';
namespace li3_perf\extensions\webgrind\library;

class Webgrind {
	
	public static $version = '1.1';
	public static $config;
	
	public static function init($config_file) {
		self::$config = new Config($config_file);
		
		// Make sure we have a timezone for date functions.
		if (ini_get('date.timezone') == '') {
			date_default_timezone_set(self::$config->defaultTimezone);
		}
		
		set_time_limit(0);
	}
}
?>