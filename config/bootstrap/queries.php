<?php
use \lithium\data\Connections;
use li3_perf\extensions\util\Data;
use lithium\action\Dispatcher;

Dispatcher::applyFilter('_callable', function($self, $params, $chain) {
	
	$filter_start = microtime(true);
	Connections::get("default")->applyFilter("read", function($self, $params, $chain) use (&$MongoDb) { 
		
		$result = $chain->next($self, $params, $chain);
		
		if (method_exists($result, 'data')) {
			
			$query = array(
				'time' => microtime(true),
				'explain' => $result->result()->resource()->explain(),
				'query' => $result->result()->resource()->info()
			);
			Data::append('queries', array($query));
		
			//	var_dump($params['query']->export($MongoDb) + array('result' => $result->data()));
		}
		
		return $result;
	});
	
	Data::append('timers', array('_filter_for_queries' => microtime(true) - $filter_start));
	return $chain->next($self, $params, $chain);
});

?>