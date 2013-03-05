<?php
/**
 * This file includes all the requirements
 * Include this to use the Cereal library
 */ 

require_once 'class.cerealinterface.php';
require_once 'class.cerealmysql.php';
require_once 'class.cerealxml.php';
require_once 'class.fileutils.php';
require_once 'class.objectutils.php';
require_once 'class.xmlutils.php';

/* Include Pear Libraries */
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/lib/PEAR');
require_once "XML/Serializer.php";
require_once "XML/Unserializer.php";
require_once "XML/Util.php";

date_default_timezone_set('UTC');
