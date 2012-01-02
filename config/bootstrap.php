<?php
use li3_perf\extensions\util\Data;

// Start the timer, note: li3_perf should be added before all other libraries (and after lithium).
//$li3_perf_start = microtime(true);
Data::set('timers', array('li3_perf_start' => microtime(true)));

// Get the queries
require __DIR__ . '/bootstrap/queries.php';

// Get the view variables
require __DIR__ . '/bootstrap/variables.php';

// li3_perf will apply a filter on the Dispatcher class that renders the toolbar and times things.
require __DIR__ . '/bootstrap/dispatcher.php';
?>