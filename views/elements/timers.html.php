<?php
$percentages = array();
$total = $timers['complete_load_with_li3_perf'];
$percentages['complete_load'] = ($timers['complete_load'] / $total) * 100;
$percentages['dispatch_cycle'] = ($timers['dispatch_cycle'] / $total) * 100;
$percentages['routing'] = ($timers['routing'] / $total) * 100;
$percentages['call'] = ($timers['call'] / $total) * 100;
?>
<h2>Time to Load</h2>

<?php //var_dump(xdebug_time_index()); ?>
<div id="holder" style="height: 205px;"></div>
<p>Note: The time to route and call the code for the request should just about equal the complete dispatch cycle. If not, something is wrong.</p>

<script type="text/javascript" charset="utf-8"> 
window.onload = function () {
	var hover_function = function () {
		this.sector.stop();
		this.sector.scale(1.1, 1.1, this.cx, this.cy);
		if (this.label) {
			this.label[0].stop();
			this.label[0].scale(1.5);
			this.label[1].attr({"font-weight": 800});
		}
	};

	var hover_function_animate = function () {
		this.sector.animate({scale: [1, 1, this.cx, this.cy]}, 500, "bounce");
		if (this.label) {
			this.label[0].animate({scale: 1}, 500, "bounce");
			this.label[1].attr({"font-weight": 400});
		}
	};
	
	var fin = function () {
		this.flag = r.g.popup(this.bar.x, this.bar.y, this.bar.value || "0").insertBefore(this);
	};
	var fout = function () {
		this.flag.animate({opacity: 0}, 300, function () {this.remove();});
	};
	var fin2 = function () {
		var y = [], res = [];
		for (var i = this.bars.length; i--;) {
			y.push(this.bars[i].y);
			res.push(this.bars[i].value || "0");
		}
		this.flag = r.g.popup(this.bars[0].x, Math.min.apply(Math, y), res.join(", ")).insertBefore(this);
	};
	var	fout2 = function () {
		this.flag.animate({opacity: 0}, 300, function () {this.remove();});
	};
		
	var r = Raphael("holder");
					
	r.g.txtattr.font = "11px 'Fontin Sans', Fontin-Sans, sans-serif";
	r.g.txtattr.fill = "white";
	
	// r.g.hbarchart(330, 10, 300, 220, [[55, 20, 13, 32, 5, 1, 2, 10], [10, 2, 1, 5, 32, 13, 20, 55]], {stacked: true}).hover(fin, fout);
	// 
	//var data2 = [[55, 20, 13, 32, 5, 1, 2, 10], [10, 2, 1, 5, 32, 13, 20, 55], [12, 20, 30]];
	//r.g.barchart(330, 10, 300, 220, data2, {stacked: true});
	
	var timer_labels = [
		["Routing - <?=number_format($timers['routing'], 2); ?>s"],
		["Call (controller, action, etc.) - <?=number_format($timers['call'], 2); ?>s"],
		["Complete Dispatch Cycle - <?=number_format($timers['dispatch_cycle'], 2); ?>s"],
		["Complete Load - <?=number_format($timers['complete_load'], 2); ?>s"],
		["Complete Load (with li3_perf toolbar) - <?=number_format($timers['complete_load_with_li3_perf'], 2); ?>s"]
	];
	var timer_colors = [
		"#4ddb49", 
		"#00A5F5",
		"#00A5F5",
		"#00A5F5",
		"#444444"
	];
	
	//r.g.text(225, 25, "Time to Load").attr({"font-size": 20});
	
	r.g.hbarchart(0, 0, 400, 200, [[<?=$percentages['routing']; ?>],[<?=$percentages['call']; ?>],[<?=$percentages['dispatch_cycle']; ?>],[<?=$percentages['complete_load']; ?>],[100]], {stacked: false, colors: timer_colors}).label(timer_labels);
	// 
	//r.g.hbarchart(10, 25, 300, 25, [[100], [200], [300]], {stacked: true}).hover(fin, fout);
	


};
</script>
<?php // echo $this->li3perf->printVars($timers); ?>