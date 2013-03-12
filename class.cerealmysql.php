<?php
/*
 * The Mysql Adaptor for Cereal
 * 
 * todo
 * - finish implementing this
 * - change it so it doesn't use ids, but uses generic keys like cereal xml
 */ 
class CerealMysql implements CerealInterface {

	/*
	 * The db connection
	 */ 
	protected $db = null;
	protected $db_host = null;
	protected $db_name = null;
	protected $db_user = null;
	protected $db_pass = null;
	protected $allow_globs = null;

	/** 
	 * Construct a new CerealMysql Intance
	 * @param $connection_string - localhost:databasename
	 */  
    public function __construct($connection_string, $user = null, $password = null) 
	{
		list($this->db_host, $this->db_name) = split($connection_string, ':');
	}
    
	/**
	 * Use this to grab a single object or directory
	 * 
	 * @param $file_path the file path query. It should not have an extension
	 * @return mixed Array of objects
	 */ 
	public function get($query) 
	{
		
	}
	
	 
	/**
	 * This function saves or updates the object
	 * @param $key
	 * @param $value
	 */ 
	public function set($key, $value) 
	{
		
	}
	
	public function ls($query){}
	
	public function delete($key) 
	{
		return;
	}
	
	public function config($key, $value) 
	{
		if($key == 'allow_globs') {
			$this->allow_globs = $value;
		}
	}
	
	/**
	 * use this to insert a new object into the table
	 * 
	 * @param String $table - the name of the table
	 * @param Object $obj - any object to be put in the table
	 */
	protected function insert_object($table, $obj) 
	{
	    // we have to get the number of rows to generate an id
	    //$result = mysql_query("SELECT * FROM `$table`");
	    ///$id = mysql_num_rows($result);
	    //$obj->uid = $id + 1;
	    //serialize the object
	    $table = addslashes($table);
	    $objstr = base64_encode(serialize($obj));
	    $insert_query = "INSERT INTO `$table` (`id`, `obj`) 
	            VALUES ('', '".$objstr."');";
	    $insert_result = mysql_query($insert_query, $this->db);
	    $obj->uid = mysql_insert_id();
	    update_object($table, $obj);
	}
	/**
	 * use this to update an object. make sure it has uid defined
	 * 
	 * @param String $table - the name of the table
	 * @param Object $obj - any object to be put in the table
	 */
	protected function update_object($table, $obj) 
	{
	    $table = addslashes($table);
	    if (!isset($obj->uid)) return false;
	    $objstr = base64_encode(serialize($obj));    
	    $update_query = "UPDATE `$table` SET `obj` = '".$objstr."' 
	            WHERE `id` = {$obj->uid} LIMIT 1;";
	    $update_result = mysql_query($update_query, $this->db);
	}
	/**
	 * use this to delete an object. make sure it has uid defined
	 * 
	 * @param String $table - the name of the table
	 * @param int $uid - any object to be put in the table
	 */
	protected function delete_object($table, $uid) 
	{
	    $uid = addslashes($uid);
	    $sql = "DELETE FROM `$table` WHERE `id` = $uid LIMIT 1;";
	    $result = mysql_query($sql, $this->db);
	}
	
	/**
	 * use this to retreive an object
	 * 
	 * @param String $table - the name of the table
	 * @param Int $id
	 */
	protected function get_object($table, $id=1) 
	{
	    $qtable = addslashes($table);
	    $qid = addslashes($id);
	    $sql = "SELECT * FROM `$qtable` WHERE id = $qid LIMIT 1;";
	    $select_result = mysql_query($sql, $this->db);
	    if ($select_result == false) {
	        return false;
	    }
	    $row = mysql_fetch_assoc($select_result);
	    if ($row == false) {
	        return false;
	    } else {
	        $obj = unserialize(base64_decode($row['obj']));
	        //var_dump($obj);die();
	        return $obj;
	    }
	}
	/**
	 * use this to get all objects from a table
	 * 
	 * @param String $table
	 */
	protected function get_objects($table) 
	{
	    $table = addslashes($table);
	    $sql = "SELECT * FROM `$table`";
	    $select_result = mysql_query($sql, $this->db);
	    $objs = array();
	    while ($row = mysql_fetch_assoc($select_result)) {
	        $objs[] = unserialize(base64_decode($row['obj']));
	    }
	    return $objs;
	}

	/**
	 * use this helper function to create a new table
	 * 
	 * @param String $table
	 */
	protected function create_table($table) 
	{
	    $sql = "CREATE TABLE IF NOT EXISTS `$table` (`id` int(11) NOT NULL 
	        auto_increment, `obj` text character set utf8 collate utf8_bin 
	        NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	    $result = mysql_query($sql, $this->db);
	}


	/**
	 * use this to connect to the database
	 */
	protected function connect_db() 
	{
	    if($this->db == null) {
	        $this->db = mysql_connect($this->db_host, $this->db_user, $this->db_pass) 
	                or die ('Could not connect to DB host');
	        mysql_select_db($this->db_name) or die ('Could not connect to DB');
	    }
	}
	
	/**
	 * use this to connect close the database
	 */
	protected function close_db() 
	{
	    if ($this->db !== null) {
	        mysql_close($this->db);
	    }
	    $this->db = null;
	}
	
}