<?php
use lithium\net\http\Media;
use li3_perf\extensions\util\Data;

// Apply a filter that will gather all the variables available to the view template.
Media::applyFilter('render', function($self, $params, $chain) {
	
	$filter_start = microtime(true);
	$view_vars = array();
	foreach($params['data'] as $k => $v) {
		if(is_object($v) && method_exists($v,'data')) {
			$view_vars[$k] = $v->data();
		} else {
			$view_vars[$k] = $v;
		}
	}
	
	Data::append('view_vars', $view_vars);
	
	Data::append('timers', array('_filter_for_variables' => microtime(true) - $filter_start));
	return $chain->next($self, $params, $chain);
});
?>