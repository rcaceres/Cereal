<?php
/*
 * This is a general purpose api to aid in Cereal production
 * 
 * You can define different configs in ./config. You then pass the name of the
 * config along with your query. if you don't pass a config name, 'default' is 
 * used as the default config name.
 */ 
error_reporting(E_ERROR | E_PARSE);
#error_reporting(E_ERROR | E_PARSE | E_ALL);

/* Include Cereal */
require dirname(__FILE__).'/../cereal.bootstrap.php';
require dirname(__FILE__).'/class.cerealapiconfig.php';


/* Load the config for this api request */
if(isset($_GET['config'])) {
	$config_name = $_GET['config'];
} else {
	$config_name = 'default';
}

$config = XmlUtils::UnserializeXmlFile(dirname(__FILE__) . '/config/'.$config_name.'.xml');
//var_dump($config);

/* Instantiate Cereal instance */

if($config->adaptor == 'xml') {

	$cereal = new CerealXml( $config->data_dir );
	$cereal->config('allow_globs', $config->allow_globs);

}


/* Helper functions */
function make_result($type, $results) {
	if($type == 'json') {
		echo json_encode($results);
	} else if($type == 'raw') {
		echo $results;
	} else if($type == 'raw_body') {
		$result = array_pop($results);
		echo $result->body;
	}
}




/* Handle Requests */

if(isset($_GET['format'])) {
	$format = $_GET['format'];
} else {
	$format = 'json';
}

if(isset($_GET['action'])) {
	$action = $_GET['action'];
} else {
	$action = 'get';
}

if(isset($_GET['fields'])) {
	$fields = $_GET['fields'];
} else {
	$fields = null;
}

if(isset($_GET['offset'])) {
	$offset = $_GET['offset'];
} else {
	$offset = 0;
}

if(isset($_GET['limit'])) {
	$limit = $_GET['limit'];
} else {
	$limit = 10;
}


/**
 * Use this to grab a single object or directory
 * 
 * @param $file_path the file path query. It should not have an extension
 * @return mixed Array of objects
 * public function get($query);
 */

if($action == 'get') {
	
	$query = isset($_GET['q']) ? $_GET['q'] : null;
	
	if($query == null) {
		die('no query');
	}
	
	$results = $cereal->get($query);
	//var_dump($results);
	
	make_result($format, $results);
	exit;
	
}
 

/**
 * Use this to list keys
 * 
 * @param $query
 * @return array of keys
 */ 
if($action == 'ls') {
	
	$query = isset($_GET['q']) ? $_GET['q'] : null;
	
	if($query == null) {
		die('no query');
	}
	
	$results = $cereal->ls($query);
	//var_dump($results);
	
	make_result($format, $results);
	exit;
	
}

/**
 * This function saves or updates the object
 * @param $key
 * @param $value
 * public function set($key, $value);
 */

/**
 * maybe use this to delete an object
 * public function delete($key);
 */