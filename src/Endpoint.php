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

use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use	GuzzleHttp\Exception\TransferException;

class Endpoint {

	function __construct(ContainerInterface $container){
        
		$this->cacher = $container->get('cache');
		$this->settings = $container->get('settings');
		//$this->frontEndConfig =  $container->get('frontEndConfig');

		
		
		
		$cacheDirectory = $this->settings['paths']['temp']."/endpointcache";
		$this->settings['sfe']->hosts;
		
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
			/*'handler' => $this->_stack,*/
			'http_errors'=>true
		]);
		
		
		
		//['paths']['root']
		$this->errorPage = new \Botnyx\Sfe\Frontend\EndpointException( $this->settings);

    }

	public function get(ServerRequestInterface $request, ResponseInterface $response, array $args = []){
		$Backend = "https://".$this->settings['sfe']->hosts->backend;
		$Clientid = $this->settings['sfe']->clientid;
		
		
		
		#$request->getQueryParams();
		#$request->getUri()->getPath();

		#var_dump($request->getUri()->getPath());
		#var_dump($request->getUri()->getQuery());
		#var_dump($request->getUri()->getFragment());


		//die(_SETTINGS['sfeFrontend']['clientId']);
		//error_log("yes!");
		error_log( $Backend.'/api/sfe/'.$Clientid.'/uri'.$request->getUri()->getPath()."?".http_build_query($args) );
		try{
			$res = $this->client->request('GET', $Backend.'/api/sfe/'.$Clientid.'/uri'.$request->getUri()->getPath()."?".http_build_query($args) );

		} catch (ClientException $e) {
			
			echo Psr7\str($e->getRequest());
			echo Psr7\str($e->getResponse());
			
		}catch (TransferException $e) {
			
			//echo Psr7\str($e->getRequest());
			/* 
				A error occured.
				
			*/
			if ($e->hasResponse()) {
				/*
					the error has a response body.
				*/
				if ( strpos($e->getResponse()->getHeader('Content-Type')[0],'json' ) ){
					/*
						the reponse body is json!
					
					*/
					$remote_error = json_decode( (string) $e->getResponse()->getBody() );
					
					
					return $this->errorPage->backendException($response,$remote_error);
					
				}else{
					/*
						the response body is html/text
					*/
					return $response->write( (string) $e->getResponse()->getBody()  );
				}
				
			}else{
				/*
					the error has no response.
				*/
				

				return $this->errorPage->TransferException($response,$e->getMessage(),__FILE__);
			}
			
			
			
		}catch(\Exception $e){
			var_dump($e->getMessage());
			var_dump($e->getCode());
		}
		//die("x");
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

		$Backend = "https://".$this->settings['sfe']->hosts->backend;
		$Clientid = $this->settings['sfe']->clientid;


		$res = $this->client->request('GET', $Backend.'/api/sfe/'.$Clientid.'/ui/sw');


		return $response->write($res->getBody())->withHeader("content-type","application/javascript; charset=utf-8");


		//return $response->write("xx");
	}

}
