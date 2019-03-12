<?php


namespace Botnyx\Sfe\Frontend;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\PredisCache;

use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy;
use Kevinrob\GuzzleCache\Strategy\PublicCacheStrategy;
use Kevinrob\GuzzleCache\Storage\DoctrineCacheStorage;

use Kevinrob\GuzzleCache\KeyValueHttpHeader;
use Kevinrob\GuzzleCache\Strategy\GreedyCacheStrategy;



class Endpoint {

	function __construct(ContainerInterface $container){
        
		$this->cacher = $container->get('cache');
		//$this->frontEndConfig =  $container->get('frontEndConfig');

		$cacheDirectory = sys_get_temp_dir();


		// Create default HandlerStack
		$this->_stack = \GuzzleHttp\HandlerStack::create();
		$this->_stack->push(
		  new CacheMiddleware(
			new PrivateCacheStrategy(
			  new DoctrineCacheStorage(
				new FilesystemCache( $cacheDirectory )
			  )
			)
		  ),
		  'cache'
		);
		// Initialize the client with the handler option
		$this->client = new \GuzzleHttp\Client([
			'handler' => $this->_stack,
			'http_errors'=>false
		]);


    }

	public function get(ServerRequestInterface $request, ResponseInterface $response, array $args = []){


		#$request->getQueryParams();
		#$request->getUri()->getPath();

		#var_dump($request->getUri()->getPath());
		#var_dump($request->getUri()->getQuery());
		#var_dump($request->getUri()->getFragment());


		//die(_SETTINGS['sfeFrontend']['clientId']);
		//error_log("yes!");
		error_log( _SETTINGS['sfeFrontend']['sfeBackend'].'/api/sfe/'._SETTINGS['sfeFrontend']['clientId'].'/uri'.$request->getUri()->getPath()."?".http_build_query($args) );
		
		try{
			$res = $this->client->request('GET', _SETTINGS['sfeFrontend']['sfeBackend'].'/api/sfe/'._SETTINGS['sfeFrontend']['clientId'].'/uri'.$request->getUri()->getPath()."?".http_build_query($args) );

		} catch (GuzzleHttp\Exception\ClientException $e) {
			//echo Psr7\str($e->getRequest());
			//echo Psr7\str($e->getResponse());
		}

		$status = $res->getStatusCode();
		
		error_log($status);
		if( $status == 404){
			return $response->withStatus(404);
		}
		
		//error_log($res->getBody());
		$res = $response->write($res->getBody());
		//$resWithExpires = $this->cache->withExpires($res, time() + 3600);
		$responseWithCacheHeader = $this->cacher->withExpires($res, time() + 3600);
		$responseWithCacheHeader = $this->cacher->withLastModified($responseWithCacheHeader, time() - 3600);
		return $responseWithCacheHeader;


	}

	public function getServiceWorker(ServerRequestInterface $request, ResponseInterface $response, array $args = []){




		$res = $this->client->request('GET', _SETTINGS['sfeFrontend']['sfeBackend'].'/api/sfe/'._SETTINGS['sfeFrontend']['clientId'].'/ui/sw');


		return $response->write($res->getBody())->withHeader("content-type","application/javascript; charset=utf-8");


		//return $response->write("xx");
	}

}
