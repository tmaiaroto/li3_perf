<?php
$total_vars = count($vars['view']);
$request_size = $this->li3perf->byteSize($this->li3perf->varSize($vars['request']), array('k' => 'Kb', 'm' => 'Mb', 'g' => 'Gb'));
$variable_size = $this->li3perf->byteSize($this->li3perf->varSize($vars['view']), array('k' => 'Kb', 'm' => 'Mb', 'g' => 'Gb'));
?>
<h2>Request Params</h2>
<?=$this->li3perf->printVars($vars['request']); ?>

<h2>View Template Variables</h2>
<p>There <?php echo ($total_vars == 1) ? 'is':'are'; ?> <span class="li3-perf-stat-value"><?=$total_vars; ?></span> variable<?php echo ($total_vars == 1) ? '':'s'; ?> occupying about <span class="li3-perf-stat-value"><?=$variable_size; ?></span> in memory.</p>
<?php
foreach($vars['view'] as $k => $v) {
	echo '<span class="li3-perf-stat-value">$' . $k . '</span><br />';
	echo $this->li3perf->printVars($v);
}
?>