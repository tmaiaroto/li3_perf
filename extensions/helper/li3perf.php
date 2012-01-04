<?php
namespace li3_perf\extensions\helper;

class Li3perf extends \lithium\template\helper\Html {
	
	public function printVars($array) {
		$html = "<pre>";
		
		if (@is_array($array)) {
			$html .= var_dump($array);
		}
		
		$html .= "</pre>";
		
		return $html;
	}
	
	public function byteSize($bytes) { 
		$size = $bytes / 1024; 
		if($size < 1024) 
			{ 
			$size = number_format($size, 2); 
			$size .= 'K'; 
			}  
		else  
			{ 
			if($size / 1024 < 1024)  
				{ 
				$size = number_format($size / 1024, 2); 
				$size .= 'M'; 
				}  
			else if ($size / 1024 / 1024 < 1024)   
				{ 
				$size = number_format($size / 1024 / 1024, 2); 
				$size .= 'G'; 
				}  
			} 
		return $size; 
    }
	
}
?>