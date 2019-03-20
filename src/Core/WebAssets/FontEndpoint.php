<?php


namespace Botnyx\Sfe\Frontend\Core\WebAssets;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\PredisCache;

class FontEndpoint{
	
	function __construct(ContainerInterface $container){
		$this->cacher = $container->get('cache');
		
		$this->assetProxy = new \Botnyx\Sfe\Shared\WebAssets\AssetProxy($container);
		
		$this->sfe =$container->get("sfe");
		$this->client_id = $this->sfe->clientid;
		
		$this->allowOrigin = "https://".$this->sfe->hosts->frontend;
		
		$this->expireTime = time() + (3600*24)*357;
	}
	
	function get(ServerRequestInterface $request, ResponseInterface $response, array $args = []){
		
		try{
			$res =  $this->assetProxy->get($response, $this->sfe->hosts->backend."/_/assets/fonts/".$args['path']);		
		}catch(\Exception $e){
			if($e->getCode()==404){
				return $this->assetProxy->e404($response)->withHeader('Access-Control-Allow-Origin',$this->allowOrigin);;
				//return $response->withStatus(404);
			}else{
				return $response->withStatus( $e->getCode() )->withHeader('Access-Control-Allow-Origin',$this->allowOrigin);;
			}
			//$e->getCode();
			
		}
		
		/*
			Set the cache headers.
		*/
		$res = $res->withHeader('Cache-Control','public');
		$res = $res->withHeader('Pragma','public');
		$res = $this->cacher->withExpires($res, $this->expireTime);
		
		
		
		return $res->withHeader('Access-Control-Allow-Origin',$this->allowOrigin);
		
	}
	
	
}

