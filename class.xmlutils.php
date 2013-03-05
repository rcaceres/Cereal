<?php

class XmlUtils {
	public static function GetXML($object, $raw = null, $cdata = false) 
	{
		/*
		 * Serialize to string
		 */ 
		$options = array(
			XML_SERIALIZER_OPTION_INDENT		  => '	',
			XML_SERIALIZER_OPTION_RETURN_RESULT => false,
			XML_SERIALIZER_OPTION_CDATA_SECTIONS => $cdata,
		);

		if($raw == null) 
		{
			$raw = false;
		}

		if($raw == true) 
		{
			$options[XML_SERIALIZER_OPTION_ENTITIES] = XML_SERIALIZER_ENTITIES_NONE;
		}

		// $options = array(
		//	 "indent"		   => " ",
		//	 "linebreak"	   => "\n",
		//	 "typeHints"	   => true,
		//	 "addDecl"		   => true,
		//	 "encoding"		   => "UTF-8",
		//	 //"rootName"		 => "rdf:RDF",
		//	 //"rootAttributes"	 => array("version" => "0.91"),
		//	 //"defaultTagName"	 => "item",
		//	 //"attributesArray" => "_attributes"
		//	 );

		$serializer = new XML_Serializer($options);
		//var_dump($serializer);
		
		$result = $serializer->serialize($object);
		if($result !== true) 
		{
		 	return false;
		}
		return $serializer->getSerializedData();
		
	}
	
	
	public static function UnserializeXmlString($str) 
	{
		$ser = new XML_Unserializer();
		$ser->setOption('complexType', 'object');
		$ser->unserialize($xmlstr);
		$result_obj = $ser->getUnserializedData();
	}
	
	public static function UnserializeXmlFile($file_path) 
	{
		$options = array(
			XML_UNSERIALIZER_OPTION_WHITESPACE => XML_UNSERIALIZER_WHITESPACE_KEEP
		);
		$ser = new XML_Unserializer($options);
		$ser->setOption('complexType', 'object');
		$ser->unserialize($file_path, TRUE);
		return $ser->getUnserializedData();
	}
}