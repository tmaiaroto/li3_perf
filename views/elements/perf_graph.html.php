<h2>Performance Graph</h2>
<p>
	This page displays the performance over time.
</p>
<?php
use li3_perf\extensions\webgrind\library\Webgrind;
use li3_perf\extensions\webgrind\library\FileHandler;

$trace_files = FileHandler::getInstance()->getTraceList(1);
if(is_array($trace_files)) {
	$dataFile = $trace_files[0]['filename'];
    
	$costFormat = Webgrind::$config->defaultCostformat;
    $reader = FileHandler::getInstance()->getTraceReader(
        $dataFile, $costFormat
    );
    
    $functions = array();
    $shownTotal = 0;
    $breakdown = array('internal' => 0, 'procedural' => 0, 'class' => 0, 'include' => 0);
    $functionCount = $reader->getFunctionCount();
	
	for ($i = 0; $i < $functionCount; $i++) {
        $functionInfo = $reader->getFunctionInfo($i);
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
		
}
?>