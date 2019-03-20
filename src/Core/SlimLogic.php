<?php

namespace Botnyx\Sfe\Frontend\Core;


use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;


class SlimLogic {
	
	
	public function getContainer($container){
		
		#print_r($container->get('sfe'));
		#die();
		#$container->get('sfe')->clientid;
		#$container->get('sfe')->paths->templates;
		#$container->get('sfe')->paths->temp;
		#$container->get('sfe')->paths->root;
		
		#$container->get('sfe')->hosts->backend;
		#$container->get('sfe')->debug;
		
		
		
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
							new \Doctrine\Common\Cache\FilesystemCache($c->get('sfe')->paths->temp.'/guzl')
						  )
						)
					  ),
					  'cache'
					);
				
				
				#echo "<pre>";
				#print_r( $sfePaths );
				#print_r( $sfeSettings->clientid );
				
			
			
				$headers = ['referer' => 'https://'.$c->get('sfe')->hosts->frontend,'origin' => 'https://'.$c->get('sfe')->hosts->frontend ];

				$cachedClient = new \GuzzleHttp\Client([
					'headers' => $headers,
					'handler' => $stack
				]);

				try{
					$res = $cachedClient->request('GET', $c->get('sfe')->hosts->backend.'/api/cfg/'.$c->get('sfe')->clientid);
					
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
		//$frontEndConfig['routes'];

		$app->get( '/a/js/[{path:.*}]',    '\\Botnyx\\Sfe\\Frontend\\Core\\WebAssets\\JsEndpoint:get' );
		$app->get( '/a/css/[{path:.*}]',   '\\Botnyx\\Sfe\\Frontend\\Core\\WebAssets\\CssEndpoint:get' );
		$app->get( '/a/fonts/[{path:.*}]', '\\Botnyx\\Sfe\\Frontend\\Core\\WebAssets\\FontEndpoint:get' );

/*
		$app->get('/expiretest',  function ( $request,  $response, array $args){

				// https://backend.devpoc.nl/api/cfg/myclientId

				$res = $response->write("justtesting");
				//$resWithExpires = $this->cache->withExpires($res, time() + 3600);
				$res = $this->cache->withExpires($res, time() + 3600);
				$resWithLastMod = $this->cache->withLastModified($res, time() - 3600);


				return $resWithLastMod;

		})->add(new \Slim\HttpCache\Cache('public', 86400));;










		$app->get('/robots.txt',  function ( $request,  $response, array $args){
		  $res = "User-agent: *".PHP_EOL."Disallow: /";
		  return $response->write($res);

		});
		
		*/
		return $app;
	}

}