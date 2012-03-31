<?php
/**
 * This class returns all assets for static projects.
 * 
 * This is helpful for situations where the li3_perf webroot is not symlinked to the main
 * project's webroot. This is most useful for Windows systems where symlinks are not well
 * supported, especially in the case of a Windows host running a Linux guest VM.
 *
 * It's also helpful for when people using the li3_perf library do not wish to or can't make 
 * a symlink or are not aware that they need to.
*/
namespace li3_perf\extensions\util;

use lithium\action\Response;
use \finfo;

class Asset {
	
	/**
	 * Renders a static asset.
	*/
	public static function render($library=null, $asset_type=null, $asset=null, $options=array()) {
		$defaults = array(
			'expires' => '+2 day',
			'max_age' => '3600',
			// could also be 'proxy-revalidate' for example
			'revalidate' => 'must-revalidate',
			// setting this to true will not use the max-age setting
			'no_cache' => false
		);
		$options += $defaults;
		
		$headers = array($_SERVER['SERVER_PROTOCOL'].' 404 Not Found' => null, 'Content-Type' => null, 'Status' => '404 Not Found');
		$body = '';
		if (strpos(__DIR__, LITHIUM_LIBRARY_PATH) !== FALSE) {
			$full_asset_path = LITHIUM_LIBRARY_PATH . '/' . $library . '/webroot/' . $asset_type . '/' . $asset;
		} else {
			$full_asset_path = LITHIUM_APP_PATH . '/libraries/' . $library . '/webroot/' . $asset_type . '/' . $asset;
		}
		if(file_exists($full_asset_path)) {
			// get the full content-type, default to text/plain
			$finfo = new finfo(FILEINFO_MIME, null);
			$finfo_content_type = $finfo->file($full_asset_path);
			$content_type = ($finfo_content_type) ? $finfo_content_type:'text/plain; charset=UTF-8';
			
			// css thinks the content-type is "text/plain" ... i don't think that's correct so...
			$asset_pieces = explode('.', $full_asset_path);
			if(is_array($asset_pieces) && strtolower(end($asset_pieces)) == 'css') {
				$content_type = 'text/css; charset=UTF-8';
			}
			
			// Getting it for js files as well
			if(is_array($asset_pieces) && strtolower(end($asset_pieces)) == 'js') {
				$content_type = 'text/javascript; charset=UTF-8';
			}
			
			// by default we set a max-age unless told not to cache
			$cache_control = 'max-age=' . (string)$options['max_age'];
			if($options['no_cache'] === true) {
				$cache_control = 'no-cache';
				// also, if not caching, set an expires header in the past
				$options['expires'] = '-1 week';
			}
			// add the revlidate method
			$cache_control .= ', ' . $options['revalidate'];
			
			$headers = array(
				'Content-Type' => $content_type,
				'Cache-Control' => $cache_control,
				// gmdate(DATE_RFC822) ... should work but gives a timezone of +0000 which is GMT but I'm not sure it's valid for HTTP expires header, I think it needs to say GMT
				'Expires' => gmdate('D, d M Y H:i:s T',strtotime($options['expires'])),
				'Last-Modified' => gmdate('D, d M Y H:i:s T', filemtime($full_asset_path))
			);
			
			/**
			 * Return 304 Not Modified if the file hasn't changed AND if the "no_cache" option is set to false.
			 * This will help reduce wasteful data transfer.
			 * The headers above (on a first time request or cleared cache) will say to hold for 3600 sconds (1 hour).
			 * So this 304 should not be returned again after an hour. It should return 200 OK with the headers above
			 * and transfer the file data, but then go back to this 304 status.
			 * This is doubly imporant because it's not straight up Apache serving the asset data, it's going through
			 * PHP with the file_get_contents() call which will use more RAM.
			 * This is all assuming the browser is sending the HTTP_IF_MODIFIED_SINCE request header...If not, we'll
			 * always return the 200 status with data.
			*/
			if(array_key_exists('HTTP_IF_MODIFIED_SINCE', $_SERVER) && $options['no_cache'] === false) {
				$if_modified_since=strtotime(preg_replace('/;.*$/','',$_SERVER['HTTP_IF_MODIFIED_SINCE']));
				if($if_modified_since >= filemtime($full_asset_path)) {
					header('HTTP/1.0 304 Not Modified');
					exit();
				}
			}
			
			//php file needs to be parsed
			if(is_array($asset_pieces) && strtolower(end($asset_pieces)) == 'php') {
				$body = include $full_asset_path;
				$body = substr($body, 0, -1);
			} else {
				$body = file_get_contents($full_asset_path);
			}
		}
		
		// return the asset or 404
		return new Response(array(
			'headers' => $headers,
			'body' => $body
		));
	}
	
}
?>