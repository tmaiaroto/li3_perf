<div id="li3-perf-toolbar">
	<div id="li3-perf-toolbar-content-wrapper" style="background: url('85bg.png') repeat;">
	
		<?=$this->html->image('/li3_perf/img/clock.png', array('title' => 'execution time', 'alt' => 'execution time')) . ' ' . number_format($timers['complete_load'], 2) . 's'; ?>

		<?=$this->html->image('/li3_perf/img/ekg.png', array('title' => 'memory usage', 'alt' => 'memory usage')) . ' ' . $this->li3perf->byteSize(memory_get_usage(true)) . ' / ' . ini_get('memory_limit'); ?>

		<div id="li3-perf-toolbar-links">
			<?=$this->html->link($this->html->image('/li3_perf/img/stats.png') . 'Queries', '#', array('id' => 'lp-queries', 'class' => 'li3-perf-link', 'escape' => false)); ?>
			
			<?=$this->html->link($this->html->image('/li3_perf/img/line-chart.png') . 'Graph', '#', array('id' => 'lp-perf-graph', 'class' => 'li3-perf-link', 'escape' => false)); ?>
			
			<?=$this->html->link($this->html->image('/li3_perf/img/stopwatch.png') . 'Time', '#', array('id' => 'lp-timing', 'class' => 'li3-perf-link', 'escape' => false)); ?>
			
			<?=$this->html->link($this->html->image('/li3_perf/img/puzzle.png') . 'Vars', '#', array('id' => 'lp-variables', 'class' => 'li3-perf-link', 'escape' => false)); ?>
			
			<?=$this->html->link($this->html->image('/li3_perf/img/chat-2.png') . 'Log', '#', array('id' => 'lp-log', 'class' => 'li3-perf-link', 'escape' => false)); ?>

			<?=$this->html->link($this->html->image('/li3_perf/img/minimize.png'), '#', array('id' => 'lp-minimize', 'class' => 'li3-perf-link', 'escape' => false, 'style' => 'margin-top: 4px;')); ?>
		</div>
		
		<div id="li3-perf-content">
			<div id="li3-perf-queries">
				<?php
				echo $this->view()->render('all', 
					array(
						'queries' => $queries
					), 
					array(
						'library' => 'li3_perf',
						'template' => 'queries',
						'layout' => 'empty'
				));
				?>
			</div>
			
			<div id="li3-perf-graph">
				<?php
				echo $this->view()->render('all', 
					array(
						'timers' => $timers
					), 
					array(
						'library' => 'li3_perf',
						'template' => 'perf_graph',
						'layout' => 'empty'
				));
				?>
			</div>
			
			<div id="li3-perf-timing">
				<?php
				echo $this->view()->render('all', 
					array(
						'timers' => $timers
					), 
					array(
						'library' => 'li3_perf',
						'template' => 'timers',
						'layout' => 'empty'
				));
				?>
			</div>
			
			<div id="li3-perf-vars">
				<?php
				echo $this->view()->render('all', 
					array(
						'vars' => $vars
					), 
					array(
						'library' => 'li3_perf',
						'template' => 'variables',
						'layout' => 'empty'
				));
				?>
			</div>
			
			<div id="li3-perf-log">
				<h2>Application Log</h2>
				<div id="error-log"></div>
			</div>
		</div>
	</div>
</div>
<?php
	//var_dump($timers);
?>