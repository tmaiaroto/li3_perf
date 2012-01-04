<?php
use lithium\action\Dispatcher;
use lithium\template\View;
use li3_perf\extensions\util\Data;
use lithium\core\Libraries;

// Apply a filter that will render the toolbar and mark some timers.
Dispatcher::applyFilter('run', function($self, $params, $chain) {
	Data::append('timers', array('li3_perf_start_dispatch' => microtime(true)));
	
	$result = $chain->next($self, $params, $chain);
	
	// Mark the end of li3_perf. 
	// Note: The time it takes to render the toolbar will not be included.
	Data::append('timers', array('li3_perf_end' => microtime(true)));
	
	// Render the toolbar (unless it's an asset from the li3_perf library)
	// Why? See li3_perf\extensions\util\Asset
	if(!isset($params['request']->params['asset_type'])) {
		$View = new View(array(
			'paths' => array(
				'template' => '{:library}/views/elements/{:template}.{:type}.php',
				'layout'   => '{:library}/views/layouts/{:layout}.{:type}.php',
			)
		));
		
		$timers = Data::get('timers');
		
		$toolbar = $View->render('all', 
			array(
				'timers' => $timers += array(
					'dispatch_cycle' => $timers['li3_perf_end'] - $timers['li3_perf_start_dispatch'],
					'complete_load_with_li3_perf' => microtime(true) - $timers['li3_perf_start'],
					'complete_load' => ($timers['li3_perf_end'] - $timers['li3_perf_start']) - $timers['_filter_for_variables'] - $timers['_filter_for_queries']
				),
				'vars' => array(
					'request' => $params['request']->params,
					'view' => Data::get('view_vars')
				),
				'queries' => Data::get('queries')
			), 
			array(
				'library' => 'li3_perf',
				'template' => 'toolbar',
				'layout' => 'default'
		));
		
		// Add the toolbar to the body of the current page. Don't just echo it out now.
		// There are sometimes issues with the headers already being sent otherwise.
		// TODO: IF proper HTML were to be desired, perhaps insert $toolbar into the body in the
		// proper spot within the HTML.
		$skip = false;
		$li3_perf = Libraries::get('li3_perf');
		if(isset($li3_perf['skip'])) {
			$controller = isset($params['request']->params['controller']) ? $params['request']->params['controller']:null;
			$action = isset($params['request']->params['action']) ? $params['request']->params['action']:null;
			$library = isset($params['request']->params['library']) ? $params['request']->params['library']:null;
			
			// Check to see if the toolbar should be shown for this library
			if(isset($li3_perf['skip']['library'])) {
				if(in_array($library, $li3_perf['skip']['library'])) {
					$skip = true;
				}
			}
			
			// Check to see if the toolbar should be shown for this controller
			if(isset($li3_perf['skip']['controller'])) {
				if(in_array($controller, $li3_perf['skip']['controller'])) {
					$skip = true;
				}
			}
			
			// Check to see if the toolbar should be shown for this action
			if(isset($li3_perf['skip']['action'])) {
				if(in_array($action, $li3_perf['skip']['action'])) {
					$skip = true;
				}
			}
		}
		
		if(isset($result->body[0]) && !$skip) {
			$result->body[0] = $toolbar . $result->body[0];
		}
	}
	
	return $result;
});
?>