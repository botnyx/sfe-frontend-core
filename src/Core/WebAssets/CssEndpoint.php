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
		
		$this->assetProxy = new \Botnyx\Sfe\Frontend\Core\WebAssets\AssetProxy($container);
	}
	
	function get(ServerRequestInterface $request, ResponseInterface $response, array $args = []){
		
		//$parsedPath = $this->parsePath($args['path'],$request->getAttributes('route')['routeInfo']);
		
		//$this->assetProxy->get("/_/assets/js/".$args['path']);
		//$this->assetProxy->get("/_/assets/fonts/".$args['path']);
		
		try{
			$returnedData = $this->assetProxy->get("/_/assets/css/".$args['path']);		
		}catch(\Exception $e){
			if($e->getCode()==404){
				return $this->assetProxy->e404($response);
				//return $response->withStatus(404);
			}
			//$e->getCode();
			
		}
		
		
		$res = $response->write( $returnedData['html'] )->withHeader('Content-Type',$returnedData['Content-Type']);
		//$resWithExpires = $this->cache->withExpires($res, time() + 3600);
		$responseWithCacheHeader = $this->cacher->withExpires($res, time() + 3600);
		$responseWithCacheHeader = $this->cacher->withLastModified($responseWithCacheHeader, $returnedData['Last-Modified'] );
		return $responseWithCacheHeader;
		
	}
	
}

