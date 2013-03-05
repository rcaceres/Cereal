<?php

class ObjectUtils {

   /** 
	* helper function for sorting array of objects based off key
	* 
	* modified from http://www.php.net/manual/en/function.sort.php#87032
	* 
	* @param Array $data -- the array 
	* @param String $key -- the key
	* @param Bool $reverse -- sort reverse
	* @param Bool $case_i -- case insensitive?
	*/
	public static function SortObjects(&$data, $key, $reverse = false, $case_i = true) 
	{
		$is_date = strtolower($key) == 'date';
	
		for($i = count($data) - 1; $i >= 0; $i--) {
			$swapped = false;
			for($j = 0; $j < $i; $j++) {
				if($is_date == true) {
					$cp1 = strtotime($data[$j]->date);
					$cp2 = strtotime($data[$j + 1]->date);
				} else if ($case_i == true) {
					$cp1 = strtolower($data[$j]->$key);
					$cp2 = strtolower($data[$j + 1]->$key);
				} else {
					$cp1 = $data[$j]->$key;
					$cp2 = $data[$j + 1]->$key;
				}
				if(($reverse && $cp1 < $cp2) || ( ! $reverse && $cp1 > $cp2)) {
					$tmp = $data[$j];
					$data[$j] = $data[$j + 1];
					$data[$j + 1] = $tmp;
					$swapped = true;
				}
			}
			if ( ! $swapped) {
				return;
			}
		}
	}
	
	
	/** 
	 * helper function filtering objects
	 * 
	 * modified from http://www.php.net/manual/en/function.sort.php#87032
	 * 
	 * @param Array $data -- the array 
	 * @param String $key -- the key
	 * @param $val
	 */

	public static function FilterObjects(&$data, $key, $value) {
	    $newdata = array();
	    foreach($data as $d) {
	        if (isset($d->$key) && $d->$key == $value) {
	            $newdata[] = $d;
	        }
	    }
	    $data = $newdata;
	}
}