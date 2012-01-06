<?php
function get($param, $default = false) {
    return (isset($_GET[$param]) ? $_GET[$param] : $default);
}

function post($param, $default = false) {
    return (isset($_POST[$param]) ? $_POST[$param] : $default);
}

function sendJsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
}

function sendError($message) {
    sendJsonResponse(array('error' => $message));
}

function costCmp($a, $b) {
    $a = $a['summedSelfCost'];
    $b = $b['summedSelfCost'];

    if ($a == $b) {
        return 0;
    }

    return ($a > $b) ? -1 : 1;
}

function createGraph($input_file, $output_file, $fraction) {
    $python = Webgrind::$config->pythonExecutable;
    $dot = Webgrind::$config->dotExecutable;
    $gprof2dot = WEBGRIND_LIBPATH.'/gprof2dot.py';
    
    exec(
        "${python} ${gprof2dot} -n ${fraction} -f callgrind ${input_file} | ${dot} -Tpng -o ${output_file}",
        $unused,
        $status
    );
    
    return ($status === 0 && file_exists($output_file));
}


/**
 * Check if given string starts with value 
 * 
 * @param string $haystack  string to search in
 * @param string $needle    value to find
 * 
 * @return bool
 */
function startsWith($haystack, $needle)
{
    return (substr($haystack, 0, strlen($needle)) === $needle);
}


/**
 * Check if given string ends with value 
 * 
 * @param string $haystack  string to search in
 * @param string $needle    value to find
 * 
 * @return bool
 */
function endsWith($haystack, $needle)
{
    return (substr($haystack, (-1 * strlen($needle))) === $needle);
}
?>