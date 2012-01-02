<!--[if lt IE 9]><?php echo $this->html->script('/li3_perf/js/excanvas.js'); ?><![endif]-->
<?php
echo $this->html->script(array('/li3_perf/js/jquery.1.7.1.min.js', '/li3_perf/js/li3-perf.js', '/li3_perf/js/jquery.jqplot.min.js'));
echo $this->html->style(array('/li3_perf/css/li3-perf.css', '/li3_perf/css/ccze.css', '/li3_perf/css/jquery.jqplot.min.css'));
echo $this->content();
?>