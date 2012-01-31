<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'Zend/Http/Client.php';
require_once 'Zend/Config/Xml.php';
require_once 'Zend/Log/Writer/Stream.php';
require_once 'Zend/Log.php';
require_once 'Zend/Registry.php';
require_once 'Zend/Exception.php';

function setPostParameters($http, $htmlForm) {
	$doc = new DOMDocument();
	$doc->strictErrorChecking = FALSE;
	@$doc->loadHTML($htmlForm);
	$xml = simplexml_import_dom($doc);
	$http->resetParameters(true);
	
	// Just use the CDM name as the field name; we'll map in the XSLT
	foreach ($xml->xpath('//input') as $input) {
		$name = (string) $input->attributes()->name;

		if (strcmp($name, 'CISODB') != 0 && strcmp($name, 'CISOTYPE') != 0
		&& strcmp($name, 'CISOPTRLIST') != 0 && strcmp($name, 'CISOPAGE') != 0) {
			$http->setParameterPost($name, $name);
		}
	}
	
	$http->setParameterPost('CISODB', $_GET['collId']);
	$http->setParameterPost('CISOTYPE', 'standard');
	$http->setParameterPost('CISOPAGE', '0');
	$http->setParameterPost('CISOPTRLIST', '');
}

$config = new Zend_Config_Xml('config.xml', 'production');
$host = 'https://' . $config->webhost;
$getURL = $host . '/cgi-bin/admin/exportxmlh2.exe?CISOTYPE=standard&CISODB=';
$postURL = $host . '/cgi-bin/admin/exportxml.exe';
$fileURL = $host . '/cgi-bin/admin/getfile.exe?CISOMODE=1&CISOFILE=';
$collId = $_GET['collId'];

$writer = new Zend_Log_Writer_Stream($config->logfile);
Zend_Registry::set('logger', new Zend_Log($writer));

$http = new Zend_Http_Client();
$http->setConfig(array('timeout' => 600, 'keepalive' => true, 'storeresponse' => false));
$http->setCookieJar();
$http->setAuth($config->user, $config->password);

if (!empty($collId)) {
	$http->setUri($getURL . $collId);
	$response = $http->request();

	if ($response->isSuccessful()) {
		setPostParameters($http, $response->getBody());
		$http->setUri($postURL);
		$response = $http->request(Zend_Http_Client::POST);

		if ($response->isSuccessful()) {
			$http->setStream('export.xml');
			$http->resetParameters();
			$http->setUri($fileURL . $collId . '/index/description/export.xml');
			
			if ($http->request(Zend_Http_Client::GET)->isSuccessful()) {
				$xml = simplexml_load_file('export.xml');
				$db = simplexml_load_file('export.db');
			}
			else echo 'dead';
		}
		else if ($response->isError()) {
  			echo $response->getStatus() . " " . $response->getMessage();
		}
		else echo 'not successful';
	}
	else {
		echo 'failed';
	}
}
else {
	echo '<div>Error: Request is missing the collId parameter.</div>';
}
