<?php
namespace li3_perf\extensions\helper;

class Li3perf extends \lithium\template\helper\Html {
	
	public function printVars($var) {
		$html = "<pre>";
		
		if (@is_array($var)) {
			$html .= var_dump($var);
		} else {
			$html .= $var;
		}
		
		$html .= "</pre>";
		
		return $html;
	}
	
	/**
	 * Formats a size in bytes as kb, mb, or gb.
	 * 
	 * @param int $bytes
	 * @param array $size_labels Keyed for 'k', 'm', and 'g' the labels for the sizes.
	 * @return type string
	*/
	public function byteSize($bytes, $size_labels=array()) { 
		$size_labels += array('k' => 'K', 'm' => 'M', 'g' => 'G');
		
		$size = $bytes / 1024; 
		if($size < 1024) 
			{ 
			$size = number_format($size, 2); 
			$size .= $size_labels['k']; 
			}  
		else  
			{ 
			if($size / 1024 < 1024)  
				{ 
				$size = number_format($size / 1024, 2); 
				$size .= $size_labels['m']; 
				}  
			else if ($size / 1024 / 1024 < 1024)   
				{ 
				$size = number_format($size / 1024 / 1024, 2); 
				$size .= $size_labels['g']; 
				}  
			} 
		return $size; 
    }
	
	/**
	 * Calculates the size (memory use) of a variable.
	 * NOTE: This is not 100% accurate by any means, but should give a good ballpark.
	 * It works by recording the current memory usage and then getting the memory usage
	 * after copying a variable. The difference between these two values is approximately the
	 * memory usage. There is no PHP function to do this otherwise.
	 * 
	 * There's several ways people calculate the size of a variable and it all depends on if the
	 * variable is an array, object, string, or integer. This is the most accurate way I've seen
	 * when using APC as a control. APC stores variables in an optimized fashion and even a null
	 * value stored in APC takes up 584 bytes. So even that isn't an accurate measure...However,
	 * the values that this method returns are slightly higher than APC in general.
	 * The value is a lot lower when trying to determine the size of an integer and it should be.
	 * How accurate is this method or even APC's memory usage compared to PHP's?
	 * Likely not accurate at all...But this is the best ballpark guess that we have.
	 * 
	 * Xdebug profiling may be another real good way to determine memory usage of the overall 
	 * application, but I saw no functions that return memory size of invidual variabes.
	 * It may be possible with Xdebug...But this method doesn't require it to be installed.
	 * 
	 * Another note, a string of 'hello' may be assumed to be 5 bytes but in PHP it can be more.
	 * Not only can strings be multi-byte encoded, but PHP also carries extra overhead due to 
	 * it's non-strict type nature. PHP is not efficient at storing things in memory and that's ok.
	 * But let's let PHP tell us it's memory usage instead of assuming based on how much space 
	 * something takes up in a database or an assumption of a character being equal to one byte.
	 * 
	 * @param mixed $var The variable you want to know the size for
	 * @return int Size in bytes
	*/
	public function varSize($var=null) {
		
		$current_mem = memory_get_usage();
		$tmp_var = $this->_varCopy($var);
		$variable_size = memory_get_usage() - $current_mem;
		unset($tmp_var);
		return $variable_size;
		
	}
	
	/**
	 * Used in determining the size of a variable.
	 * 
	 * @see varSize()
	 * @param mixed $src The variable
	 * @return mixed A copy of the variable
	*/
	private function _varCopy($src=0) {
		if (is_string($src)) {
			return str_replace('SOME_NEVER_OCCURING_VALUE_145645645734534523', 'XYZ', $src);
		}

		if (is_numeric($src)) {
			return ($src + 0);
		}

		if (is_bool($src)) {
			return ($src?TRUE:FALSE);
		}
		if (is_null($src)) {
			return NULL;
		}

		if (is_object($src)) {
			$new = (object) array();
			foreach ($src as $key => $val) {
			$new->$key = $this->_varCopy($val);
			}
			return $new;
		}

		if (!is_array($src)) {
			print_r(gettype($src) . "\n");
			return $src;
		}

		$new = array();

		foreach ($src as $key => $val) {
			$new[$key] = $this->_varCopy($val);
		}
		
		return $new;
	}
	
}
?>