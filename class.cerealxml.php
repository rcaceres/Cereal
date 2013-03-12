<?php
/*
,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,

CerealXml is a physical object database. XML files can either be generated from
PHP objects or written by hand (!). The goal is to be simple and available for 
many creative applications. Sometimes it is nice to know exactly where and how 
your data is stored, and this simplicity allows a lot of flexibility

Furthermore, CerealXml supports custom files with an .xml.php extension. These
can be used to generate content dynamically within the xml format. 

This is a component that has been used for running an object-based personal site, 
that is dynamically populated with generated and handwritten xml-data. Example
class types on the site are, Node, Image, Post, Sound, Video, and Html, Html
represents a raw html page.

Note Requires PEAR XML Serialization classes

Here's an example on how to load the xml classes
   set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/PEAR');
   require_once "XML/Serializer.php";
   require_once "XML/Unserializer.php";

,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,

 * 
 * @author Richard Caceres <rchrd.net>
 * @license MIT
 */ 

class CerealXml implements CerealInterface
{
	protected $data_path;
	protected $allow_globs = true;
	protected $cdata = false;
	
	
	/** 
	 * Construct a new CerealXml
	 * @param $connection_string - the path to the data directory
	 */  
	public function __construct($connection_string, $user = null, $password = null) 
	{
		/*
		 * Note the connection string should just be a path to the directory.
		 * But to be compatible with the Cereal interface we'll handle connetion
		 * strings of the format server:path
		 */ 
		if( strstr($connection_string, ':') ) {
			list($server_not_used, $data_path) = split($connection_string, ':');
		} else {
			$data_path = $connection_string;
		}
		
		$this->data_path = $data_path;
	}

	
	/**
	 * Use this to grab a single object or directory
	 * 
	 * @param $file_path the file path query. It should not have an extension
	 * @return mixed Array of objects
	 */ 
	public function get($query) 
	{
		$result = array();
		/*
		 * We remove any extension
		 */ 
		//$query = preg_replace('/(?:\.xml|xml\.php)$/i', '', $query);
		
		$query_path = $this->data_path . DIRECTORY_SEPARATOR . $query;
		
		if($this->allow_globs == true && file_exists($query_path) && is_dir($query_path))
		{
			$result = $this->_get_dir($query_path);
		}
		else 
		{
			if(file_exists($query_path . '.xml.php'))
			{
				$result = $this->_get_single($query_path . '.xml.php');

			}
			else if(file_exists($query_path . '.xml'))
			{
				$result = $this->_get_single($query_path . '.xml');
			}
		}
		//var_dump($result);
		return $result;
	}
	

	/**
	 * This function saves or updates the object
	 * @todo add support for adding non-existent directories
	 * @todo figure out when to add CDDATA tags
	 * @todo allow $key == null to autogenerate keys
	 */ 
	public function set($key, $object)
	{
		/* Generate xml for object first */
		
		$result = XmlUtils::GetXML($object, null, $this->cdata);
		if($result === false) 
		{
			return false;	
		}


		/*
		 * Write file
		 */ 
		$file_path = $this->data_path . DIRECTORY_SEPARATOR . $key . '.xml';

		/*
		 * Verify Directory exists. Make if needed
		 */ 
		if( ! is_dir( dirname($file_path) ) ) 
		{
			mkdir( dirname($file_path), 0777 &~ umask(), true );
		}


		$put_result = file_put_contents($file_path, $result);
		
		if($put_result === false) 
		{
			return false;
		}
		
		return true;
	}	
	
	public function ls($query) 
	{
		$result = array();
		/*
		 * We remove any extension
		 */ 
		
		$query_path = $this->data_path . DIRECTORY_SEPARATOR . $query;
		$query_path = rtrim($query_path, DIRECTORY_SEPARATOR);
		
		/*
		 * This returns raw directory order
		 */ 
		$files           = FileUtils::ReadDirectory($query_path);
		
		/*
		 * This sort by the directory time
		 */ 
		//$files        = $this->_scandir_by_mtime($query_path);

		
		//var_dump($files); exit;

		foreach($files as $key => $file)
		{
			$file_extension = pathinfo($file, PATHINFO_EXTENSION);
			
			if( ! in_array($file, array('.', '..')) 
					&& ($file_extension == 'xml' || ($file_extension == 'php' && strstr($file, '.xml.php') != false)))
			{
				$result[] = pathinfo($file, PATHINFO_FILENAME);
			}
		}
		
		return $result;
	}
	
	public function delete($key) 
	{
		$key_full = $this->data_path . DIRECTORY_SEPARATOR . $key;
		if(file_exists($key_full . '.xml.php'))
		{
			$result = @unlink($key_full . '.xml.php');
		}
		else if(file_exists($key_full . '.xml'))
		{
			$result = @unlink($key_full . '.xml');
		}
		
		FileUtils::UnlinkEmptyParents(dirname($key_full), $this->data_path);
		
		return $result;
	}
	
	
	public function config($key, $value) 
	{
		if($key == 'allow_globs') {
			$this->allow_globs = $value;
		} else if($key == 'cdata') {
			$this->cdata = $value;
		}
	}
	
	/**
	 * Grab the contents of this directory
	 * @todo sort by xml date with fallback to mdate instead of only by mdate
	 */ 
	protected function _get_dir($query_path) 
	{
		$return_objects = array();
		$query_path     = rtrim($query_path, DIRECTORY_SEPARATOR);
		
		/*
		 * This returns raw directory order
		 */ 
		$files           = FileUtils::ReadDirectory($query_path);
		
		/*
		 * This sort by the directory time
		 */ 
		//$files        = $this->_scandir_by_mtime($query_path);

		
		//var_dump($files); exit;

		foreach($files as $key => $file)
		{
			$file_extension = pathinfo($file, PATHINFO_EXTENSION);
			
			if( ! in_array($file, array('.', '..')) 
					&& ($file_extension == 'xml' || ($file_extension == 'php' && strstr($file, '.xml.php') != false)))
			{
				$result_object = $this->_get_single($query_path . DIRECTORY_SEPARATOR . $file);
				//$result_object = $this->_get_single($file);
				//var_dump($query_path . DIRECTORY_SEPARATOR . $file);
				//var_dump($result_object);

				$return_objects = array_merge($return_objects, $result_object);
			}
		}
		
		//exit;
		//var_dump($return_objects);exit;
		ObjectUtils::SortObjects($return_objects, 'date', true);
		//var_dump($return_objects);
		
		return $return_objects;
	}
	

	
	/**
	 * Grab a single file
	 */ 
	protected function _get_single($query_path) 
	{
		$return_objects = array();
		
		//echo $query_path ."<br>\n";

		$file_extension = pathinfo($query_path, PATHINFO_EXTENSION);

		if($file_extension == 'php' && strstr($query_path, '.xml.php') != false)
		{
			/*
			 * Here we actually render any php within pxml files
			 */ 
			ob_start();
			include $query_path;
			$xmlstr = ob_get_contents();
			@ob_end_clean();
			echo $xmlstr;
			$result_obj = XmlUtils::UnserializeXmlString($xmlstr);
		}
		else if($file_extension == 'xml')
		{
			$result_obj = XmlUtils::UnserializeXmlFile($query_path);
			//var_dump($result_obj);
		}

		
		if(isset($result_obj)) 
		{
			/*
			 * Set a date if one is not set
			 */
			if(is_object($result_obj) && $result_obj->date == NULL)
			{
				$modtime = @filectime($query_path);
				$result_obj->date = $modtime != FALSE ? date('r', $modtime) : 0;
			}
			//var_dump($result_obj);

			$return_objects[] = $result_obj;
		}
		
		
		//var_dump($return_objects);

		//var_dump($ser);
		//echo serialize($ser->getRootName());
		/*
		 * Here we decide if its an array of nodes or just a single node
		 */ 
		//print_r($ser->_unserializedData);
		//print_r( $ser->getUnserializedData());exit;

		// if(in_array(strtolower($ser->getRootName()), 'root', 'collection')) 
		// {
		//	$result_objects_return = $ser->getUnserializedData();
		//	foreach($result_objects_array as $key => $objects) 
		//	{
		//		//foreach($objects as
		//		if(is_object($result_objects_return[$key])) 
		//		{
		//			$return_objects[] = $result_objects_return[$key];
		//		}
		//		else if(is_array($result_objects_return[$key]))
		//		{
		//			foreach($result_objects_return[$key] as $index => $object) 
		//			{
		//				$return_objects[] = $object;
		//			}
		//		}
		//	}
		// }
		// else
		// {

		// }
		
		
		return $return_objects;
	}
}