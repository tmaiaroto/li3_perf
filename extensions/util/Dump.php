<?php
/**
* Copyright (c) 2011, Leon Sorokin
* All rights reserved.
*
* dump_r.php
* better than print_r()
* better than var_dump()
* for browsers
*/
function dump_r($input, $exp_lvls = 1000)
{
	// get the input arg passed to the function
	$src = debug_backtrace();
	$src = (object)$src[0];
	$file = file($src->file);
	$line = $file[$src->line - 1];
	preg_match('/dump_r\((.+?)(?:,|\);)/', $line, $m);

	echo dump_r::go($input, $m[1], $exp_lvls);
}

class dump_r
{
	// indicator for injecting css/js on first dump
	public static $initial = true;
	public static $css;
	public static $js;
	public static $self = '*RECURSION*';
	public static $hooks = array();

	public static function go($inp, $key = 'root', $exp_lvls = 1000, $st = true)
	{
		$inject = '';
		if (self::$initial) {
			$inject = self::$css . self::$js;
			self::$initial = false;
		}

		$buf = '';
		$buf .= $st ? "{$inject}<pre class=\"dump_r\"><ul>" : '';
		$t = self::checkType($inp);
		$disp = htmlspecialchars($t->disp);
		$len = !is_null($t->length) ? "<div class=\"len\">{$t->length}</div>" : '';
		$sub = !is_null($t->subtype) && !is_bool($inp) ? "<div class=\"sub\">{$t->subtype}</div>" : '';
		$excol = is_array($t->children) && !empty($t->children) ? '<div class="excol"></div>' : '';
		$exp_state = $excol ? ($exp_lvls > 0 ? ' expanded' : ' collapsed') : '';
		$empty = empty($inp) ? ' empty' : '';
		$numeric = is_numeric($inp) ? ' numeric' : '';
		$t->subtype = $t->subtype ? ' ' . $t->subtype : $t->subtype;
		$buf .= "<li class=\"{$t->type}{$t->subtype}{$numeric}{$empty}{$exp_state}\">{$excol}<div class=\"lbl\"><div class=\"key\">{$key}</div><div class=\"val\">{$disp}</div><div class=\"typ\">({$t->type})</div>{$sub}{$len}</div>";
		if ($t->children) {
			$buf .= '<ul>';
			foreach ($t->children as $k => $v) {
				if ($v === $inp)
					$v = self::$self;
				$buf .= self::go($v, $k, $exp_lvls - 1, false);
			}
			$buf .= '</ul>';
		}
		$buf .= '</li>';
		$buf .= $st ? '</ul></pre>' : '';

		return $buf;
	}

	public static function checkType($input)
	{
		$type = (object)array(
			'type'			=> null,
			'disp'			=> $input,
			'subtype'		=> null,
			'length'		=> null,
			'children'		=> null
		);
		// avoid detecting strings with names of global functions as callbacks
		if (is_callable($input) && !(is_string($input) && function_exists($input))) {
			$type->type		= 'function';
			$type->disp		= 'fn()';
		}
		else if (is_array($input)) {
			$type->type		= 'array';
			$type->disp		= '[ ]';
			$type->children	= $input;
			$type->length	= count($type->children);
		}
		else if (is_resource($input)) {
			$type->type		= 'resource';
			$type->subtype	= get_resource_type($input);
			preg_match('/#\d+/', (string)$input, $matches);
			$type->disp		= $matches[0];
		}
		else if (is_object($input)) {
			$type->type		= 'object';
			$type->disp		= '{ }';
			$type->subtype	= get_class($input);
			$type->children	= array();

			$childs	= (array)$input;		// hacks access to protected and private props
			foreach ($childs as $k => $v) {
				// clean up odd chars left in private/protected names
				$k = preg_replace("/[^\w]?(?:{$type->subtype})?[^\w]?/", '', $k);
				$type->children[$k] = $v;
			}
		}
		else if (is_int($input))
			$type->type		= 'integer';
		else if (is_float($input))
			$type->type		= 'float';
		else if (is_string($input)) {
			$type->type		= 'string';
			$type->length	= strlen($input);
		}
		else if (is_bool($input)) {
			$type->type		= 'boolean';
			$type->disp		= $input ? 'true' : 'false';
		}
		else if (is_null($input)) {
			$type->type		= 'null';
			$type->disp		= 'null';
		}
		else
			$type->type		= gettype($input);

		if (array_key_exists($type->type, self::$hooks))
			self::proc_hooks($type->type, $input, $type);

		return $type;
	}

	public static function proc_hooks($key, $input, $type)
	{
		foreach(self::$hooks[$key] as $fn) {
			if ($fn($input, $type))
				return true;
		}
		return false;
	}

	// hook_string, hook_resource
	public static function __callStatic($name, $args)
	{
		if (substr($name, 0, 5) == 'hook_') {
			$hookey = substr($name, 5);
			if (count($args) == 2)
				self::$hooks[$hookey][$args[1]] = $args[0];
			else
				self::$hooks[$hookey][] = $args[0];
		}
	}
}

// util functions for hooks
class dump_r_lib
{
	public static function rel_date($datetime) {
		$rel_date = '';
		$timestamp = is_string($datetime) ? strtotime($datetime) : $datetime;
		$diff = time()-$timestamp;
		$dir = '-';
		if ($diff < 0) {
			$diff *= -1;
			$dir = '+';
		}
		$yrs = floor($diff/31557600);
		$diff -= $yrs*31557600;
		$mhs = floor($diff/2592000);
		$diff -= $mhs*2419200;
		$wks = floor($diff/604800);
		$diff -= $wks*604800;
		$dys = floor($diff/86400);
		$diff -= $dys*86400;
		$hrs = floor($diff/3600);
		$diff -= $hrs*3600;
		$mins = floor($diff/60);
		$diff -= $mins*60;
		$secs = $diff;

		if		($yrs > 0)	$rel_date .= $yrs.'y' . ($mhs > 0 ? ' '.$mhs.'m' : '');
		elseif	($mhs > 0)	$rel_date .= $mhs.'m' . ($wks > 0 ? ' '.$wks.'w' : '');
		elseif	($wks > 0)	$rel_date .= $wks.'w' . ($dys > 0 ? ' '.$dys.'d' : '');
		elseif	($dys > 0)	$rel_date .= $dys.'d' . ($hrs > 0 ? ' '.$hrs.'h' : '');
		elseif	($hrs > 0)	$rel_date .= $hrs.'h' . ($mins > 0 ? ' '.$mins.'m' : '');
		elseif	($mins > 0)	$rel_date .= $mins.'m';
		else				$rel_date .= $secs.'s';

		return $dir . $rel_date;
	}
}

dump_r::hook_string(function($input, $type) {
	if ($input === dump_r::$self) {
		$type->type		= 'self';
		$type->length	= null;

		return true;
	}

	return false;
}, 'is_recursion');

dump_r::hook_string(function($input, $type) {
	if (substr($input, 0, 5) == '<?xml') {
		// strip namespaces
		$input = preg_replace('/<(\/?)[\w-]+?:/', '<$1', preg_replace('/\s+xmlns:.*?=".*?"/', '', $input));

		if ($xml = simplexml_load_string($input)) {
			$type->subtype	= 'XML';
			$type->children = (array)$xml;
			// dont show length, or find way to detect uniform subnodes and treat as XML [] vs XML {}
			$type->length = null;

			return true;
		}

		return false;
	}

	return false;
}, 'is_xml');

dump_r::hook_string(function($input, $type) {
	if ($type->length > 0 && ($input{0} == '{' || $input{0} == '[') && ($json = json_decode($input))) {
		// maybe set subtype as JSON [] or JSON {}, will screw up classname
		$type->subtype	= 'JSON';
		$type->children = (array)$json;
		// dont show length of objects, only arrays
		$type->length = $input{0} == '[' ? count($type->children) : null;

		return true;
	}

	return false;
}, 'is_json');

dump_r::hook_string(function($input, $type) {
	if (strlen($input) > 5 && ($ts = strtotime($input)) !== false) {
		$type->subtype = 'datetime';
		$type->length = dump_r_lib::rel_date($ts);

		return true;
	}

	return false;
}, 'is_datetime');

// js
ob_start();
?>
<script>
	(function(){
		function toggle(e) {
			if (e.which != 1) return;

			if (e.target.className.indexOf("excol") !== -1) {
				e.target.parentNode.className = e.target.parentNode.className.replace(/\bexpanded\b|\bcollapsed\b/, function(m) {
					return m == "collapsed" ? "expanded" : "collapsed";
				});
			}
		}
		document.addEventListener("click", toggle, false);
	})();
</script>
<?php
dump_r::$js = ob_get_contents();
ob_end_clean();