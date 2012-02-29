<?php

require_once 'Zend/Config/Xml.php';
require_once 'Zend/Log/Writer/Stream.php';
require_once 'Zend/Log.php';
require_once 'Zend/Registry.php';

class CSVConverter {

	private $xml;
	
	function __construct() {
		$args = func_get_args();
		$this->xml = $args[0];
	
		$config = new Zend_Config_Xml('config.xml', 'production');
	}

	function assign_arks($fHandle) {
		
	}
}