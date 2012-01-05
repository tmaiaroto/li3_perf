<?php
use lithium\net\http\Router;
use lithium\action\Response;
use lithium\core\Environment;
use li3_perf\extensions\util\Asset;

// Assets (can't symlink in the VM because Windows host)
Router::connect("/{:library}/{:asset_type:js|img|css}/{:args}", array(), function($request) { 
	// If not production, then return the asset with a "no-cache" cache-control, there won't be any 304's, etc.
	$no_cache = !Environment::is('production');
	return Asset::render($request->params['library'], $request->params['asset_type'], join('/', $request->params['args']), compact('no_cache'));
});

// This one is cool. It outputs the last few lines of a log file.
// If CCZE is installed the log output will be in HTML and styles can be altered easily with CSS.
Router::connect('/li3_perf/tail/{:file}/{:lines}', array('lines' => 25, 'file' => LITHIUM_APP_PATH . '/resources/tmp/logs/debug.log'), function($request) {
		$lines = $request->params['lines'];
		$logfile = $request->params['file'];
		
		header("Content-type: text/html");
		// echo `tail -n 50 /var/log/php-fpm/www-error.log | ccze -h`;
		
		$options = '-n ' . $lines;
		$command = 'tail ' . $options . ' ' . $logfile . ' | ccze -h';
		$output = shell_exec($command);
		
		if($output == null) {
			$output = '';
			$command = 'tail ' . $options . ' ' . $logfile;
			$lines = explode("\n", shell_exec($command));
			if(!empty($lines)) {
				foreach($lines as $line) {
					$output .= $line . "<br />";
				}
			}
		}
		
		echo $output;
		exit();
	});
?>