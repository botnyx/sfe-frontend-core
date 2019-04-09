<?php

namespace Botnyx\Sfe\Frontend\Config;



class RemoteConfig {
	
	function __construct($container){
		
		$this->sfeSettings = $container->get('settings')['sfe'];
		$this->sfePaths = $container->get('settings')['paths'];
		
		
	}
	function get(){
		
			#die();
			#print_r($this);
		#die();
				#print_r($c->get('settings')['sfe']);
			
				/*
					if frontend is enabled, serve it...  else 403
				*/
				//if(array_key_exists('sfeFrontend',_SETTINGS)){
				// frontend remote-Config
				// Create default HandlerStack
				$stack = \GuzzleHttp\HandlerStack::create();
				$stack->push(
					  new \Kevinrob\GuzzleCache\CacheMiddleware(
						new \Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy(
						  new \Kevinrob\GuzzleCache\Storage\DoctrineCacheStorage(
							new \Doctrine\Common\Cache\FilesystemCache($this->sfePaths['temp'].'/remoteconfig')
						  )
						)
					  ),
					  'cache'
					);
				
				
				#echo "<pre>";
				#print_r( $sfePaths );
				#print_r( $sfeSettings->clientid );
				
			
			
				$headers = ['referer' => 'https://'.$this->sfeSettings->hosts->frontend,'origin' => 'https://'.$this->sfeSettings->hosts->frontend ];

				$cachedClient = new \GuzzleHttp\Client([
					'headers' => $headers,
					'handler' => $stack
				]);
				
			
			
			
				/*
				
'{"code":200,"status":"ok","statusmsg":"ok","data":{"routes":[{"id":1,"uri":"\/","fnc":"\\Botnyx\\Sfe\\Frontend\\Endpoint:get","tmpl":"botnyx\/freelancer.html","client_id":"909b6bb0-servenow-website"},{"id":2,"uri":"\/newspaper\/edition\/{edition}","fnc":"\\Botnyx\\Sfe\\Frontend\\Endpoint:get","tmpl":"botnyx\/freelancer.html","client_id":"909b6bb0-servenow-website"},{"id":3,"uri":"\/sw.js","fnc":"\\Botnyx\\Sfe\\Frontend\\Endpoint:getServiceWorker","tmpl":null,"client_id":"909b6bb0-servenow-website"},{"id":4,"uri":"\/newspaper","fnc":"\\Botnyx\\Sfe\\Frontend\\Endpoint:get","tmpl":"botnyx\/newspaper","client_id":"909b6bb0-servenow-website"}],"menus":[{"id":1,"link":"#c\/home","text":"home","icon":"entypo-direction","parent":0,"menu":"sfe-nav-main","scopes":null,"sortorder":0,"linkattribute":null,"clientId":"909b6bb0-servenow-website"}],"config":{"client_id":"909b6bb0-servenow-website","template":"blackrockdigital\/startbootstrap-sb-admin-2-master","allowedorigin":"*","htmlstamp":12345,"languages":"en-UK,nl-NL","disabled":0,"disabledreason":null,"defaultpage":"index.html","cdnhostname":"cdn.servenow.nl","backendhostname":"backend.servenow.nl"}}}';
				*/
				try{
					$res = $cachedClient->request('GET', $this->sfeSettings->hosts->backend.'/api/cfg/'.$this->sfeSettings->clientid);
					
					$frontEndConfig = json_decode($res->getBody());
				}catch(Exception $e){
					die($e->getMessage());
				}

				#echo "<pre>";
				#var_dump($frontEndConfig);
				//die($res->getBody());

			#die("frontend/slimlogic.php");
				
				return $frontEndConfig->data;
	}
	
}