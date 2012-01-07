<?php
use lithium\net\http\Router;
use lithium\action\Response;
use lithium\core\Environment;
use li3_perf\extensions\util\Asset;
use li3_perf\extensions\webgrind\library\Webgrind;
use li3_perf\extensions\webgrind\library\FileHandler;

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

Router::connect('/{:library}/profile/trace/{:file}', array('file' => 'latest'), function($request) {
	// If for some reason a profile session is active, do NOT run the code in this route.
	// It would cause some pretty big issues =)
	if(isset($_GET['XDEBUG_PROFILE']) || isset($_COOKIE['XDEBUG_PROFILE'])) {
		echo 'You can\'t attempt to get the latest profile information while the profiler is active.';
		return false;
	}
	
	// webgrind bootstrap/config process
	require LITHIUM_APP_PATH . '/libraries/li3_perf/extensions/webgrind/library/bootstrap.php';

	$trace_files = FileHandler::getInstance()->getTraceList(1);
	if(is_array($trace_files)) {
		$dataFile = $trace_files[0]['filename'];
		// I've seen this work before and then sometimes not...Sometimes it needs the slash. Weird.
		if(!file_exists(Webgrind::$config->xdebugOutputDir . $dataFile)){
			$dataFile = '/' . $dataFile;
		}
		$costFormat = Webgrind::$config->defaultCostformat;		
		$reader = FileHandler::getInstance()->getTraceReader(
			$dataFile, $costFormat
		);
		
		$result = array();
		$functions = array();
		$shownTotal = 0;
		$breakdown = array('internal' => 0, 'procedural' => 0, 'class' => 0, 'include' => 0);
		$functionCount = $reader->getFunctionCount();
		
		$result['moodpik'] = array('function_calls' => 0, 'total_cost' => 0);
		for ($i = 0; $i < $functionCount; $i++) {
			$functionInfo = $reader->getFunctionInfo($i);
			//var_dump($functionInfo['functionName']);
			if(strstr($functionInfo['functionName'], 'moodpik\\')) {
				$result['moodpik']['function_calls']++;
				$result['moodpik']['total_cost'] += $functionInfo['summedSelfCost'];
			}

			$isInternal = (strpos($functionInfo['functionName'], 'php::') !== false);

			if ($isInternal) {
				if (get('hideInternals', false)) {
					continue;
				}

				$breakdown['internal'] += $functionInfo['summedSelfCost'];
				$humanKind = 'internal';
			} elseif (false !== strpos($functionInfo['functionName'], 'require_once::') ||
					  false !== strpos($functionInfo['functionName'], 'require::') ||
					  false !== strpos($functionInfo['functionName'], 'include_once::') ||
					  false !== strpos($functionInfo['functionName'], 'include::')) {
				$breakdown['include'] += $functionInfo['summedSelfCost'];
				$humanKind = 'include';
			} elseif (false !== strpos($functionInfo['functionName'], '->') ||
					  false !== strpos($functionInfo['functionName'], '::')) {
					$breakdown['class'] += $functionInfo['summedSelfCost'];
					$humanKind = 'class';
			} else {
				$breakdown['procedural'] += $functionInfo['summedSelfCost'];
				$humanKind = 'procedural';
			}

			$shownTotal += $functionInfo['summedSelfCost'];
			$functions[$i] = $functionInfo;
			$functions[$i]['nr'] = $i;
			$functions[$i]['humanKind'] = $humanKind;
		}

		usort($functions, 'costCmp');

		$remainingCost = $shownTotal * get('showFraction');

		$result['functions'] = array();
		foreach ($functions as $function) {

			$remainingCost -= $function['summedSelfCost'];
			$function['file'] = urlencode($function['file']);
			$result['functions'][] = $function;
			if ($remainingCost < 0) {
				break;
			}
		}

		$result['summedInvocationCount'] = $reader->getFunctionCount();
		$result['summedRunTime'] = $reader->formatCost($reader->getHeader('summary'), 'msec');
		$result['dataFile'] = $dataFile;
		$result['invokeUrl'] = $reader->getHeader('cmd');
		$result['runs'] = $reader->getHeader('runs');
		$result['breakdown'] = $breakdown;
		$result['mtime'] = date(Webgrind::$config->dateFormat, filemtime(Webgrind::$config->xdebugOutputDir.'/'.$dataFile));

		$creator = preg_replace('/[^0-9\.]/', '', $reader->getHeader('creator'));
		$result['linkToFunctionLine'] = version_compare($creator, '2.1') > 0;


		var_dump($result);
		exit();
	}
});
?>