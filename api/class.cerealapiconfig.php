<?php

class CerealApiConfig {
	public $adaptor = 'xml';
	public $allow_globs = true;
	
	/* properties for xml */
	public $data_dir = '';
	public $cdata = false;
	
	/* properties for mysql */
	public $user;
	public $pass;
	public $name;
}