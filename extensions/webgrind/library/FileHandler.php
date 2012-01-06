<?php
//require 'Reader.php';
//require 'Preprocessor.php';
/**
 * Class handling access to data-files(original and preprocessed) for webgrind.
 * @author Jacob Oettinger
 * @author Joakim Nygård
 */

namespace li3_perf\extensions\webgrind\library;

class FileHandler {
	
	private static $singleton = null;
	
	/**
	 * @return Singleton instance of the filehandler
	 */
	public static function getInstance() {
		if (!isset(self::$singleton)) {
			self::$singleton = new self();
        }
        
		return self::$singleton;
	}
		
	private function __construct() {
		// Get list of files matching the defined format
		$files = self::getFiles(Webgrind::$config->xdebugOutputFormat, Webgrind::$config->xdebugOutputDir);
		
		// Get list of preprocessed files
        $prepFiles = self::getFiles('#\\'.Webgrind::$config->preprocessedSuffix.'$#', Webgrind::$config->storageDir, true);
        
		// Loop over the preprocessed files.		
		foreach($prepFiles as $fileName => $prepFile) {
			$fileName = str_replace(Webgrind::$config->preprocessedSuffix, '', $fileName);
			
			// If it is older than its corrosponding original: delete it.
			// If it's original does not exist: delete it
			if (!isset($files[$fileName]) || $files[$fileName]['mtime'] > $prepFile['mtime']) {
				unlink($prepFile['absoluteFilename']);
			} else {
				$files[$fileName]['preprocessed'] = true;
            }
		}
		// Sort by mtime
		uasort($files, function($a, $b) {
    		if ($a['mtime'] == $b['mtime']) {
    		    return 0;
            }
    
    		return ($a['mtime'] > $b['mtime']) ? -1 : 1;
    	});
		
		$this->files = $files;
	}
	
	/**
	 * Get the value of the cmd header in $file
	 *
	 * @return void string
	 */	
	private static function getInvokeUrl($file) {
	    if (preg_match('/.webgrind$/', $file)) {
	        return 'Webgrind internal';
        }

		// Grab name of invoked file. 
	    $fp = fopen($file, 'r');
        while (($line = fgets($fp)) !== false) {
            if (preg_match('#^cmd: (.+)$#', $line, $parts)) {
                $invokeUrl = trim($parts[1]);
                break;
            }
        }
        
        fclose($fp);
        
        if (empty($invokeUrl)) {
            return 'Unknown!';
        }

		return $invokeUrl;
	}
	
	/**
	 * List of files in $dir whose filename has the format $format
	 *
	 * @return array Files
	 */
	private static function getFiles($format, $dir, $preprocessed = false) {
		$list = preg_grep($format, scandir($dir));
		$files = array();
		
		// If current script under profiler, get it's output file name for exclusion
		if (function_exists('xdebug_get_profiler_filename') &&
            ($profiler_filename = xdebug_get_profiler_filename()) !== false) {
		    $selfFile = realpath($profiler_filename);
		}
		
		foreach($list as $file) {
			$absoluteFilename = realpath($dir.'/'.$file);

            // Make sure that script never parses the profile currently being generated. (infinite loop)
			if (!empty($selfFile) && $selfFile === $absoluteFilename) {
				continue;
            }

            if (!$preprocessed) {
    			// Exclude preprocessed files
    			if (endsWith($absoluteFilename, Webgrind::$config->preprocessedSuffix)) {
    			    continue;
                }
    			
                // If set in config, exclude Webgrind files profiles
                $invokeUrl = self::getInvokeUrl($absoluteFilename);
    			if (Webgrind::$config->hideWebgrindProfiles && startsWith($invokeUrl, WEBGRIND_ROOT)) {
    			    continue;
                }
            }

			$files[$file] = array(
                'absoluteFilename' => $absoluteFilename, 
                'mtime' => filemtime($absoluteFilename), 
                'preprocessed' => $preprocessed, 
                'filesize' => self::bytestostring(filesize($absoluteFilename))
            );
            
            if (!$preprocessed) {
                $files[$file]['invokeUrl'] = $invokeUrl;
            }
		}
        
		return $files;
	}
	
	/**
	 * Get list of available trace files. Optionally including traces of the webgrind script it self
	 *
	 * @return array Files
	 */
	public function getTraceList($limit = 0) {
		$result = array();
        $use_limit = ($limit > 0);
        
		foreach($this->files as $fileName=>$file) {
			$result[] = array(
                'filename'  => $fileName, 
                'invokeUrl' => str_replace($_SERVER['DOCUMENT_ROOT'].'/', '', $file['invokeUrl']),
                'filesize'  => $file['filesize'],
                'mtime'     => date(Webgrind::$config->dateFormat, $file['mtime'])
            );
            if ($use_limit && --$limit <= 0) {
                break;
            }
		}
		return $result;
	}
	
	/**
	 * Get a trace reader for the specific file.
	 * 
	 * If the file has not been preprocessed yet this will be done first.
	 *
	 * @param string File to read
	 * @param Cost format for the reader
	 * @return Webgrind_Reader Reader for $file
	 */
	public function getTraceReader($file, $costFormat) {
		$prepFile = Webgrind::$config->storageDir.'/'.$file.Webgrind::$config->preprocessedSuffix;
        
		try {
			$r = new Reader($prepFile, $costFormat);
		} catch (Exception $e) {
			// Preprocessed file does not exist or other error
			Preprocessor::parse(Webgrind::$config->xdebugOutputDir.'/'.$file, $prepFile);
			$r = new Reader($prepFile, $costFormat);
		}
		return $r;
	}
	
	/**
	 * Present a size (in bytes) as a human-readable value
	 *
	 * @param int    $size        size (in bytes)
	 * @param int    $precision    number of digits after the decimal point
	 * @return string
	 */
	private static function bytestostring($size, $precision = 0) {
   		$sizes = array('YB', 'ZB', 'EB', 'PB', 'TB', 'GB', 'MB', 'KB', 'B');
		$total = count($sizes);

		while($total-- && $size > 1024) {
		    $size /= 1024;
		}
        
		return round($size, $precision).$sizes[$total];
    }
}
?>