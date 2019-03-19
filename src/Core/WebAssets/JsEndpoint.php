<?php


namespace Botnyx\Sfe\Frontend\Core\WebAssets;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\PredisCache;



class JsEndpoint{


	function __construct(ContainerInterface $container){
		$this->cacher = $container->get('cache');
		$this->settings=$container->get("settings");
		#echo "<pre>";
		#print_r( $container->get("settings") );
		#die();
		
		$this->assetProxy = new \Botnyx\Sfe\Shared\WebAssets\AssetProxy($container);

		$this->client_id = $this->settings['sfe']->clientId;

		$this->allowOrigin = "https://".$this->settings['sfe']->hosts->frontend;

		$this->expireTime = time() + (3600*24)*357;
	}

	function get(ServerRequestInterface $request, ResponseInterface $response, array $args = []){

		//print_r($this->settings['sfe']['clientId']);
		//die();

		try{
			$res =  $this->assetProxy->get($response,'https'.$this->settings['sfe']->hosts->backend."/_/assets/".$this->client_id."/js/".$args['path']);
		}catch(\Exception $e){
			if($e->getCode()==404){
				return $this->assetProxy->e404($response)->withHeader('Access-Control-Allow-Origin',$this->allowOrigin);;
				//return $response->withStatus(404);
			}else{
				return $this->assetProxy->e500($response)->withHeader('Access-Control-Allow-Origin',$this->allowOrigin);;

				//die();
				//return $this->assetProxy->e404($response)->withHeader('Access-Control-Allow-Origin',$this->allowOrigin);;
				//throw new \Exception( $e->getMessage(), $e->getCode() );
				//print_r($e->getBody());
				#print_r($e->getCode());
				#print_r($e->getMessage());
				//$response = \Botnyx\Sfe\Shared\ExceptionResponse::get($response,$e->getCode(),'Cdn reports: 404 Not Found');
				//return $response;
				//die("xxx");
				//

				//return $response->withStatus( $e->getCode() );//->withHeader('Access-Control-Allow-Origin',$this->allowOrigin);;
			}


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
