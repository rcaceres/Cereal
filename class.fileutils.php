<?php

class FileUtils {
	public static function SanitizeFileName($string, $force_lowercase = true, $anal = false) 
	{
		$strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
		               "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
		               "â€”", "â€“", ",", "<", ".", ">", "/", "?");
		$clean = trim(str_replace($strip, "", strip_tags($string)));
		$clean = preg_replace('/\s+/', "-", $clean);
		$clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
		return ($force_lowercase) ?
		    (function_exists('mb_strtolower')) ?
		        mb_strtolower($clean, 'UTF-8') :
		        strtolower($clean) :
		    $clean;
	}
	
	/*
	 * This helper sorts by file creation time.
	 * @note this function is based off some code found in a comment in the 
	 *		php documentation.
	 */ 
	public static function ScanDirByCtime($folder) 
	{
		$files = scandir($folder, 1);
		$arr   = array();
		foreach($files as $filename) 
		{
			if($filename != '.' && $filename != '..') 
			{
				$dat = date("YmdHis", filectime($folder . '/' . $filename));
				$arr[$dat] = $filename;
			}
		}		
		if( ! ksort($arr)) 
		{
			return false;
		}
		return array_reverse($arr);
	}
	
	/*
	 * http://stackoverflow.com/a/5173338/260550
	 */ 
	public static function ReadDirectory($directory)
	{
	    if(is_dir($directory) === false)
	    {
	        return false;
	    }

	    try
	    {
	        $Resource = opendir($directory);
	        $Found = array();

	        while(false !== ($Item = readdir($Resource)))
	        {
	            if($Item == "." || $Item == "..")
	            {
	                continue;
	            }

	            if( ! is_dir($Item))
	            {
	                $Found[] = $Item;
	            }
	        }
	    }
		catch(Exception $e)
	    {
			//var_dump($e);
	        return false;
	    }

	    return $Found;
	}
	

	/**
	 * Helper for reading a directory recursively
	 */ 
	public static function ReadDirectoryRecursive($directory) 
	{
		$Found = array();
		$directory = rtrim($directory, '/') . '/';
		if(is_dir($directory)) 
		{
			try {
				$Resource = opendir($directory);
				while(false !== ($Item = readdir($Resource))) 
				{
					$preg_result = array();
					if($Item == "." || $Item == ".." || preg_match_all('/^[\.-]+.*$/i', $Item, $preg_result, PREG_SET_ORDER) > 0) 
					{
						continue;
					}
					if(is_dir($directory . $Item)) 
					{
						$Found[] = $directory . $Item;
						$Found[] = self::ReadDirectoryRecursive($directory . $Item);
					} 
					else 
					{
						$Found[] = $directory . $Item;
					}
				}
			} catch(Exception $e) {}		
		}
		return $Found;		
	}
	
	/**
	 * This cleans up a directory trea by removing empty parent directories
	 * It walks up the tree in reverse from a starting node. It stops once it
	 * reaches the stop path.
	 * 
	 * @param $path 
	 * @param $stop_path
	 */ 
	public static function UnlinkEmptyParents($path, $stop_path) {
		//var_dump(array($path, $stop_path));
		
		/*
		 * Verify that this is in the $stop_path
		 */ 
		if( ! strstr($path, $stop_path) ) {
			return;
		}
	
		$dir_rel = str_replace($stop_path . DIRECTORY_SEPARATOR, '', $path);
	
		$dir_parts = explode(DIRECTORY_SEPARATOR, $dir_rel);
	
		//var_dump(array($dir_rel,$dir_parts));

		$dirs_to_remove = array();
		$dir_curr = '';
	
		for($i = 0; $i < count($dir_parts); $i++) {
			if( $i != 0 ) {
				$dir_curr .= DIRECTORY_SEPARATOR;
			}
			$dir_curr .= $dir_parts[$i];
			$dirs_to_remove[] = $dir_curr;
		}
	
		//var_dump($dirs_to_remove);	
	
	
		/*
		 * We have to reverse the order so we can empty depth first
		 */ 
		$dirs_to_remove = array_reverse($dirs_to_remove);
		foreach($dirs_to_remove as $dir_to_remove) {
			/*
			 * Don't keep trying if the first one fails.
			 */ 
			
			//echo "Removing dir: " . $stop_path . DIRECTORY_SEPARATOR . $dir_to_remove . "<br>\n";
			
			if( ! @rmdir($stop_path . DIRECTORY_SEPARATOR . $dir_to_remove) ) {
				return;
			}
		
		}
	}

}