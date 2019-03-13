<?php


namespace Botnyx\Sfe\Frontend\Core\WebAssets;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\PredisCache;


class CssEndpoint{
	
	function __construct(ContainerInterface $container){
		$this->cacher = $container->get('cache');
		
		$this->assetProxy = new \Botnyx\Sfe\Shared\WebAssets\AssetProxy($container);
		
		$this->allowOrigin = '*';
		
	}
	
	function get(ServerRequestInterface $request, ResponseInterface $response, array $args = []){
		
		
		try{
			$res =  $this->assetProxy->get($response, _SETTINGS['sfeFrontend']['sfeBackend']."/_/assets/css/".$args['path']);		
		}catch(\Exception $e){
			if($e->getCode()==404){
				return $this->assetProxy->e404($response)->withHeader('Access-Control-Allow-Origin',$this->allowOrigin);;
				//return $response->withStatus(404);
			}else{
				return $response->withStatus( $e->getCode() )->withHeader('Access-Control-Allow-Origin',$this->allowOrigin);;
			}
			//$e->getCode();
			
		}
		
		
		
		return $res->withHeader('Access-Control-Allow-Origin',$this->allowOrigin);
		
		
	}
	
}

