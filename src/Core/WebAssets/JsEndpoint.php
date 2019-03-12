<?php


namespace Botnyx\Sfe\Frontend\Core\WebAssets;


use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;


class JsEndpoint{
	
	function __construct(ContainerInterface $container){
		$this->assetProxy = new Botnyx\Sfe\Frontend\Core\WebAssets\AssetProxy($container);
	}
	
	function get(ServerRequestInterface $request, ResponseInterface $response, array $args = []){
		
	}
	
}

