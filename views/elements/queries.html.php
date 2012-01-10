<?php
$total_queries = count($queries);
$total_query_time = 0;
foreach($queries as $query) {
	$total_query_time += !empty($query['explain']['millis']) ?
		$query['explain']['millis'] :
		0;
}
?>
<h2>Queries</h2>
<p>There <?php echo ($total_queries == 1) ? 'is':'are'; ?> <span class="li3-perf-stat-value"><?=$total_queries; ?></span> quer<?php echo ($total_queries == 1) ? 'y':'ies'; ?> which took <span class="li3-perf-stat-value"><?=$total_query_time; ?>ms</span> to execute.</p>
<?=$this->li3perf->printVars($queries); ?>
