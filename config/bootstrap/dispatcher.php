<?php
use lithium\action\Dispatcher;
use lithium\template\View;
use li3_perf\extensions\util\Data;
use lithium\core\Libraries;
use lithium\net\http\Router;

Dispatcher::applyFilter('_callable', function($self, $params, $chain) {
	// At this point, the routing has completed. In order to call _callable, it's routed.
	// So this is ever so slightly off actually.
	Data::append('timers', array('li3_perf_has_route' => microtime(true)));

	$result = $chain->next($self, $params, $chain);

	// Now that we know whether or not the request is callable, it's going to be called.
	// This is esentially right before the code in the controller action is executed.
	// So mark the time.
	Data::append('timers', array('li3_perf_start_call' => microtime(true)));

	return $result;
});

Dispatcher::applyFilter('_call', function($self, $params, $chain) {

	$result = $chain->next($self, $params, $chain);

	// At this point the controller action has been called and now a response will be returned.
	// $result here contains the response and we've been setting timers all along the way...
	// The next time we'll be working with the same response is under the next filter below on
	// run() AFTER $result = $chain->next() is called... That's the end of the dispatch cycle.
	// The $result = part below is actually before this filter and the filter on _callable() above.
	Data::append('timers', array('li3_perf_end_call' => microtime(true)));

	return $result;
});

// Apply a filter that will render the toolbar and mark some timers.
Dispatcher::applyFilter('run', function($self, $params, $chain) {
	if(substr($params['request']->url, 0, 17) == '/li3_perf/profile') {
		return $chain->next($self, $params, $chain);
	}

	Data::append('timers', array('li3_perf_start_dispatch' => microtime(true)));

	$result = $chain->next($self, $params, $chain);

	// Mark the end of li3_perf.
	// Note: The time it takes to render the toolbar will not be included.
	Data::append('timers', array('li3_perf_end' => microtime(true)));

	// Render the toolbar (unless it's an asset from the li3_perf library)
	// Why? See li3_perf\extensions\util\Asset
	$content_type = isset($result->headers['Content-Type']) ? join('', explode(';', $result->headers['Content-Type'], 1)) : '';
	if(
		!isset($params['request']->params['asset_type']) &&
		(!$content_type || $content_type == 'text/html')
	) {
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

		if ($skip || !isset($result->body[0])) {
			return $result;
		}

		$timers = Data::get('timers') + array(
			'li3_perf_start' => 0,
			'li3_perf_end' => 0,
			'li3_perf_start_dispatch' => 0,
			'li3_perf_has_route' => 0,
			'li3_perf_start_call' => 0,
			'li3_perf_end_call' => 0,
			'_filter_for_variables' => 0,
			'_filter_for_queries' => 0
		);

		$View = new View(array(
			'paths' => array(
				'template' => '{:library}/views/elements/{:template}.{:type}.php',
				'layout'   => '{:library}/views/layouts/{:layout}.{:type}.php',
			)
		));
		$toolbar = $View->render('all',
			array(
				'timers' => $timers += array(
					'dispatch_cycle' => $timers['li3_perf_end'] - $timers['li3_perf_start_dispatch'],
					'routing' => $timers['li3_perf_has_route'] - $timers['li3_perf_start_dispatch'],
					'call' => isset($timers['li3_perf_end_call']) && isset($timers['li3_perf_start_call']) ?
						$timers['li3_perf_end_call'] - $timers['li3_perf_start_call'] :
						0,
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

		if (preg_match('/<!--\s*LI3_PERF_TOOLBAR\s*-->/si', $result->body[0], $match)) {
			$result->body[0] = str_replace($match[0], $toolbar, $result->body[0]);
		} else {
			$result->body[0] = $toolbar . $result->body[0];
		}
	}

	return $result;
});
?>
