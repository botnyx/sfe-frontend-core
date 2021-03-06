<?php

namespace Botnyx\Sfe\Frontend\Logic;



class Routes {
	
	
	public function get($app,$container){
		
		$endpointAuth = new \Slim\HttpCache\Cache('public', 86400);
		
		$app->options('/{routes:.+}', function ($request, $response, $args) {
			return $response;
		})->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization, Referrer,User-Agent')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
			->withHeader('Access-Control-Allow-Origin', '*');
		
		
		foreach($container['frontendconfig']->routes as $route){
			
			//$app->map(['GET', 'POST'], $route->uri, $route->fnc )->setName('endpoint-'.$route->id);
			
			
			
				if(strtoupper($route->method)=='POST'){
					
					if( $route->auth==1 ){
						$app->post( $route->uri,$route->fnc )->setName('endpoint-'.$route->id)->add($endpointAuth);
					}else{
						$app->post( $route->uri,$route->fnc )->setName('endpoint-'.$route->id);
					}	
					
				}else{
					if( $route->auth==1 ){
						$app->get ( $route->uri,$route->fnc )->setName('endpoint-'.$route->id)->add($endpointAuth);
					}else{
						$app->get ( $route->uri,$route->fnc )->setName('endpoint-'.$route->id);
					}
					
				}
			
			
		}
		//$frontEndConfig['routes'];

		$app->get( '/a/js/[{path:.*}]',   '\\Botnyx\\Sfe\\Frontend\\Core\\WebAssets\\JsEndpoint:get' );
		$app->get( '/a/css/[{path:.*}]',  '\\Botnyx\\Sfe\\Frontend\\Core\\WebAssets\\CssEndpoint:get' );
		$app->get( '/a/fonts/[{path:.*}]','\\Botnyx\\Sfe\\Frontend\\Core\\WebAssets\\FontEndpoint:get' );
		
		
#enable in db		$app->get( '/my/{endpoint}','\\Botnyx\\Sfe\\Frontend\\Core\\WebAssets\\MyEndpoint:get' );
		
		$app->get( '/assets/[{path:.*}]','\\Botnyx\\Sfe\\Frontend\\Core\\WebAssets\\AssetsEndpoint:get' );
		
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

