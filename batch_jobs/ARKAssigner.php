<?php

require_once 'Zend/Config/Xml.php';
require_once 'Zend/Log/Writer/Stream.php';
require_once 'Zend/Log.php';
require_once 'Zend/Registry.php';

class ARKAssigner {
	
	private $http;
	private $xml;
	private $url;
	
	function __construct() {
		$args = func_get_args();
		$this->xml = $args[0];
		
		$config = new Zend_Config_Xml('config.xml', 'production');
		$this->url = $config->ezid->host . $config->ezid->shoulder;
		$loginURL = $config->ezid->host . '/ezid/login';
		$user = $config->ezid->username;
		$pass = $config->ezid->password;
		
		// set up our ezid client
		$this->http = new Zend_Http_Client();
		$this->http->setCookieJar();
		$this->http->setAuth($user, $pass, Zend_Http_Client::AUTH_BASIC);
		$this->http->setUri($loginURL);
		$response = $this->http->request(Zend_Http_Client::GET);
		
		// login and test ezid connection
		if (!($response->isSuccessful())) {
			throw new Zend_Http_Client_Exception("Couldn't log into EZID");
		}
	}
	
	function mint_ark($metadata = array(), $aTryCount = 0) {
		$this->http->setUri($this->url);
		$data = '';
		
		// EZID needs 'text/plain' POSTs; Zend's setParameterPost() won't work
		foreach ($metadata as $part) {
			$data = $data . $part[0] . ': ' . $part[1] . PHP_EOL;
		}

		try {
			$this->http->setRawData($data, 'text/plain');
			$response = $this->http->request(Zend_Http_Client::POST);
	
			if ($response->isSuccessful()) {
				$result = $response->getBody();
	
				if (strpos($result, 'success: ') === 0) {
					return substr($result, 9);
				}
				else if ($aTryCount <= 5) {
					return $this->mint_ark($metadata, ++$aTryCount);
				}
				else {
					throw new Exception("Couldn't mint an ARK as expected");
				}
			}
			else {
				$code = $response->getStatus();
				throw new Exception("Unsuccessful HTTP connection: " . $code);
			}
		}
		catch (Zend_Http_Client_Adapter_Exception $details) {
			return $this->mint_ark($metadata, ++$aTryCount);
		}
	}
	
	function assign_arks($fHandle) {
		$config = new Zend_Config_Xml('config.xml', 'production');
		$writer = new Zend_Log_Writer_Stream($config->logfile);
		Zend_Registry::set('logger', new Zend_Log($writer));
		Zend_Registry::get('logger')->log('Minting...', Zend_Log::DEBUG);
		
		foreach ($this->xml->xpath('/metadata/record') as $record) {
			if ((string) $record->ark == '') {
				$creator = (string) $record->creato;
				$title = (string) $record->title;
				$id = (string) $record->cdmid;
				
				// Everything should have a title and CDM ID
				$metadata = array(
					array('dc.title', $title),
					array('dc.identifier', $id)
				);
				
				// Most, though not all, things have creators
				if ($creator != '') {
					array_push($metadata, array('dc.creator', $creator));
				}
				
				$ark = $this->mint_ark($metadata);
				
				Zend_Registry::get('logger')->log($id . ' ' . $ark, Zend_Log::DEBUG);
				
				fputcsv($fHandle, array($id, 'ark', $ark));
			}
		}
	}
}