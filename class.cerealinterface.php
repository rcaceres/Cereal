<?php

interface CerealInterface {

	/** 
	 * Construct a new Cereal Db
	 * @param $connection_string - server:databasename, or just path/to/xmldb/dir
	 */  
    public function __construct($connection_string, $login = null, $password = null);
    
	/**
	 * Use this to grab a single object or directory
	 * 
	 * @param $file_path the file path query. It should not have an extension
	 * @return mixed Array of objects
	 */ 
	public function get($query);
	 
	 
	/**
	 * This function saves or updates the object
	 * @param $key
	 * @param $value
	 */ 
	public function set($key, $value);

	/**
	 * Use this to list keys
	 * 
	 * @param $query
	 * @return array of keys
	 */ 
	public function ls($query);

	/**
	 * maybe use this to delete an object
	 */ 
	public function delete($key);
	
	
	public function config($key, $value);
}