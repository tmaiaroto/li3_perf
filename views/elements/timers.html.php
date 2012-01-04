<h2>Time to Load</h2>
<?=$this->li3perf->printVars($timers); ?>

<div id="holder" style="height: 900px;"></div>
    
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

	var r = Raphael("holder");
	r.g.txtattr.font = "12px 'Fontin Sans', Fontin-Sans, sans-serif";

	// first chart
	///r.g.text(225, 25, "Chart Title").attr({"font-size": 20});


};
</script>