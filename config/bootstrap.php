<?php
use li3_perf\extensions\util\Data;

// Start the timer, note: li3_perf should be added before all other libraries (and after lithium).
//$li3_perf_start = microtime(true);
Data::set('timers', array('li3_perf_start' => microtime(true)));

// Include webgrind
require LITHIUM_APP_PATH . '/libraries/li3_perf/extensions/webgrind/library/bootstrap.php';

// Get the queries
require __DIR__ . '/bootstrap/queries.php';

// Get the view variables
require __DIR__ . '/bootstrap/variables.php';

// li3_perf will apply a filter on the Dispatcher class that renders the toolbar and times things.
require __DIR__ . '/bootstrap/dispatcher.php';

// require LITHIUM_APP_PATH . '/libraries/li3_perf/extensions/util/Dump.php';
?>