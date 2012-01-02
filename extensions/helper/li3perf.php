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
	
	/**
	 * Prints the specified object as collapsable/expandable table like cfdump function in ColdFusion.
	 * Feel free to modify and share with this comment and yours.
	 * by Zafer Altun, 2011 ©
	 * Ex: 
	 * $testObj = array("name"=>"zafer", "data"=>(object)array("product"=>"boomboss", "version"=>"1.0 b"), "age"=>"27", "skills"=>array("art"=>array("music"=>9, "drawing"=>6), "coding"=>array("php"=>6, "as3"=>10, "c++"=>3)));
	 * prettyDump($testObj);
	 * 
	 * 
	*/
	public function prettyDump($object, $level = 0) {
		$rgbVal = 255 - max(128 + $level * 16, 0);
		$nodeBgColor = sprintf("rgb(%s, %s, %s)", $rgbVal, $rgbVal, $rgbVal);
		echo '<div style="width:auto; height:auto; font-family:Tahoma; font-size:11px">';

		if (is_array($object) || is_object($object))
		{
			if ($level == 0) echo '<b>' . $object . ' (' . count($object) . ')</b><br/><br>';
			echo '<table ' . (($level == 0) ? 'style="border:#999999 solid 1px"': '') . ' cellspacing="0" cellpadding="0">';
			$dataIndex = 0;
			$dataCount = count($object);
			foreach($object as $item=>$n)
			{
				$randID = "dataContainer" . mt_rand(0, 1000000);

				echo '<tr style="vertical-align:top; overflow:hidden; height:20px"><td style="text-align:right; background-color:' . $nodeBgColor . '; color:#EEEEEE; border-bottom:#CCCCCC solid 1px; padding-left:5px; padding-right:2px; padding-top:2px';
				echo (is_array($n) || is_object($n)) ? '; cursor:pointer" collapsed="false" prevHTML="" onclick="if (this.attributes.collapsed.value != \'true\'){ this.attributes.prevHTML.value = this.ownerDocument.getElementById(\'' . $randID . '\').innerHTML; this.ownerDocument.getElementById(\'' . $randID . '\').innerHTML = \'...\'; this.attributes.collapsed.value = \'true\' } else { this.ownerDocument.getElementById(\'' . $randID . '\').innerHTML = this.attributes.prevHTML.value; this.attributes.collapsed.value = \'false\' }">': '">';
				echo '<b>' . $item . '</b></td></td><td style="' . (($dataIndex < $dataCount - 1) ? 'border-bottom:#CCCCCC solid 1px; ': ' ') . 'height:20px; padding-left:5px; padding-right:2px; padding-top:2px" id="' . $randID . '">';
				if (is_array($n) || is_object($n))
				{
					$this->prettyDump($n, $level + 1);
				} else {
					echo $n;
				}
				echo '</td></tr>';

				$dataIndex++;
			}
			echo '</table></div>';
		} else {
			echo $object . "</div>";
		}
	}
}
?>