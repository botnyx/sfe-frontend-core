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
	}
	
	function get(ServerRequestInterface $request, ResponseInterface $response, array $args = []){
		
		
		try{
			return  $this->assetProxy->get($response, _SETTINGS['sfeFrontend']['sfeBackend']."/_/assets/js/".$args['path']);		
		}catch(\Exception $e){
			if($e->getCode()==404){
				return $this->assetProxy->e404($response);
				//return $response->withStatus(404);
			}else{
				return $response->withStatus( $e->getCode() );
			}
			//$e->getCode();
			
		}
		
		
		
	}
	
}

