<?php

namespace Botnyx\Sfe\Frontend\Core\Objects\config;


class Frontend {
	
	var $clientid="";
	var $clientsecret="";
	
	function __construct($settings){
		
		
		if(!array_key_exists('clientId',$settings['sfeFrontend'])){
			throw new \Exception("Fatal Error in Configuration.ini : Missing `clientId` in the `sfeFrontend` section.");
		}
		
		$this->clientId = $settings['sfeFrontend']['clientId'];
		
		
		if(array_key_exists('clientSecret',$settings['sfeFrontend'])){
			$this->clientsecret = new \Botnyx\Sfe\Shared\ProtectedValue($settings['sfeFrontend']['clientSecret']);
		}
		
		
				$hosts = new \Botnyx\Sfe\Shared\Objects\config\SfeHosts();
			
		if(!array_key_exists('sfeCdn',$settings['sfeFrontend'])){
			throw new \Exception("Fatal Error in Configuration.ini : Missing `sfeCdn` in the `sfeFrontend` section.");
		}
		$hosts->cdn = $settings['sfeFrontend']['sfeCdn'];
		
		
		
		if(!array_key_exists('sfeBackend',$settings['sfeFrontend'])){
			throw new \Exception("Fatal Error in Configuration.ini : Missing `sfeBackend` in the `sfeFrontend` section.");
		}
		$hosts->backend = $settings['sfeFrontend']['sfeBackend'];
		
		
		
		if(!array_key_exists('sfeAuth',$settings['sfeFrontend'])){
			throw new \Exception("Fatal Error in Configuration.ini : Missing `sfeAuth` in the `sfeFrontend` section.");
		}
		$hosts->auth = $settings['sfeFrontend']['sfeAuth'];
		

	}
	
	
}