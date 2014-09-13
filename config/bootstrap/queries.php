<?php
use li3_perf\extensions\util\Data;
use lithium\action\Dispatcher;
use lithium\data\Connections;
use lithium\data\source\database\Result as DatabaseResult;
// FIXME: remove this once a MySqlResult is a DatabaseResult
use lithium\data\source\database\adapter\my_sql\Result as MySqlResult;
use lithium\data\source\mongo_db\Result as MongoDBResult;

Dispatcher::applyFilter('_callable', function($self, $params, $chain) {
	$filter_start = microtime(true);

	Connections::get('default')->applyFilter('read', function($self, $params, $chain) {
		$start = microtime(true);
		$result = $chain->next($self, $params, $chain);

		if (method_exists($result, 'data')) {
			$db_result = $result->result();
			$time = microtime(true);
			$query = compact('time');

			if ($db_result instanceof MongoDBResult) {
				$query += array(
					'explain' => $db_result->resource()->explain(),
					'query' => $db_result->resource()->info()
				);
			} else if ($db_result instanceof DatabaseResult ||
			           $db_result instanceof MySqlResult) {
				$query += array(
					'explain' => array('millis' => $time - $start),
					'query' => $db_result->resource()->queryString
				);
			} else {
				throw new \Exception('This kind of result is not supported.');
			}

			Data::append('queries', array($query));
		}

		return $result;
	});

	Data::append('timers', array('_filter_for_queries' => microtime(true) - $filter_start));
	return $chain->next($self, $params, $chain);
});

?>