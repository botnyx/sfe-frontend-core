<?php

namespace Botnyx\Sfe\Frontend\Core;


use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;


class SlimLogic {
	
	
	public function getContainer($container){
		
		
		
		
		
		$container['cache'] = function ($c) {
			return new \Slim\HttpCache\CacheProvider();
		};

		
		$container['frontendconfig'] = function($c){
				
			$sfeSettings = $c->get('settings')['sfe'];
			$sfePaths = $c->get('settings')['paths'];
			#print_r($Settings);

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
							new \Doctrine\Common\Cache\FilesystemCache($sfePaths['temp'].'/guzl')
						  )
						)
					  ),
					  'cache'
					);
				
				
				#echo "<pre>";
				#print_r( $sfePaths );
				#print_r( $sfeSettings->clientid );
				
			
			
				$headers = ['referer' => 'https://'.$sfeSettings->hosts->frontend,'origin' => 'https://'.$sfeSettings->hosts->frontend ];

				$cachedClient = new \GuzzleHttp\Client([
					'headers' => $headers,
					'handler' => $stack
				]);

				try{
					$res = $cachedClient->request('GET', $sfeSettings->hosts->backend.'/api/cfg/'.$sfeSettings->clientid);
					
					$frontEndConfig = json_decode($res->getBody());
				}catch(Exception $e){
					die($e->getMessage());
				}

				#echo "<pre>";
				#var_dump($frontEndConfig);
				//die($res->getBody());

			#die("frontend/slimlogic.php");
				
				return $frontEndConfig->data;
		};

		
		
		
		
		return $container;
	}
	
	public function getMiddleware($app,$container){
		
		
		return $app;
	}
	
	public function getRoutes($app,$container){
		
		
		
		foreach($container['frontendconfig']->routes as $route){
			$app->get( $route->uri,$route->fnc );

		}
		
		return $app;
	}

}