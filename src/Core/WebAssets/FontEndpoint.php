<?php


namespace Botnyx\Sfe\Frontend\Core\WebAssets;


class FontEndpoint{
	
	function __construct(ContainerInterface $container){
		$this->assetProxy = new Botnyx\Sfe\Frontend\Core\WebAssets\AssetProxy($container);
	}
	
	function get(ServerRequestInterface $request, ResponseInterface $response, array $args = []){
		
	}
	
}

