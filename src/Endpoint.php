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
        $this->settings = $container->get('settings');
		$this->cacher = $container->get('cache');
		$this->sfe = $container->get('sfe');
		$this->fecfg = $container->get('frontendconfig');
		$this->router = $container->get("router");
		
		//$this->sfe->clientid
		//$this->sfe->paths->temp
		//$this->sfe->hosts->backend
		
		#print_r($container->get('sfe'));
		#die();
		#$container->get('sfe')->clientid;
		#$container->get('sfe')->paths->templates;
		#$container->get('sfe')->paths->temp;
		#$container->get('sfe')->paths->root;
		
		#$container->get('sfe')->hosts->backend;
		#$container->get('sfe')->debug;
		
		
		
		
		$cacheDirectory = $this->sfe->paths->temp."/endpointcache";
		//$this->settings['sfe']->hosts;
		
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
		$this->errorPage = new \Botnyx\Sfe\Frontend\EndpointException( $this->sfe);

    }

	public function get(ServerRequestInterface $request, ResponseInterface $response, array $args = []){
		
		$Backend = "https://".$this->sfe->hosts->backend;
		$Clientid = $this->sfe->clientid;
		$theEndpointId = (int)str_replace('endpoint-','',$request->getAttribute("route")->getName());
		
		
		//echo $Backend.'/api/sfe/'.$Clientid.'/uri/'.$theEndpointId."?".http_build_query($args);
		
		
		$r = $request->getQueryParams(); // get
		$request->getParsedBody(); // post
		
		$request->getQueryParams();
		$request->getUri()->getPath();

		//print_r($this->settings);
		
		
		//echo $Backend.'/api/sfe/'.$Clientid.'/uri/'.$request->getAttribute('language').'/'.$theEndpointId."?".http_build_query($args);
		//die();
		
		
		//$foo = $request->getAttribute('language');
		
		
		
		//die(_SETTINGS['sfeFrontend']['clientId']);
		//error_log("yes!");
		error_log( $Backend.'/api/sfe/'.$Clientid.'/uri/'.$request->getAttribute('language').'/'.$theEndpointId."?".http_build_query($args) );
		
		try{
			$res = $this->client->request('GET', $Backend.'/api/sfe/'.$Clientid.'/uri/'.$request->getAttribute('language').'/'.$theEndpointId."?".http_build_query($args) );

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

		$Backend = "https://".$this->sfe->hosts->backend;
		$Clientid = $this->sfe->clientid;


		$res = $this->client->request('GET', $Backend.'/api/sfe/'.$Clientid.'/ui/sw');


		return $response->write($res->getBody())->withHeader("content-type","application/javascript; charset=utf-8");


		//return $response->write("xx");
	}

	
	public function oauthProxy(ServerRequestInterface $request, ResponseInterface $response, array $args = []){
		
		$allGetVars = $request->getQueryParams();
		
		// Initialize the client with the handler option
		$client = new \GuzzleHttp\Client([
			/*'handler' => $this->_stack,*/
			'http_errors'=>true
		]);
		
		
		//var_dump( $this->sfe->role->hosts->auth );
		
		$extraoption =  [
			'auth' => [$this->sfe->role->clientid, $this->sfe->role->clientsecret],
			'form_params' => [
				'grant_type' => 'authorization_code',
				'code' => $allGetVars['code'],
				'redirect_uri' => $allGetVars['redirect_uri']
			]
		];
		//"https://".$this->sfe->role->hosts->auth."/oauth2/token";
		$res = $this->client->request('POST', "https://".$this->sfe->role->hosts->auth."/oauth2/token" ,$extraoption );
		
		$result = json_decode($res->getBody()->getContents());
		
		//var_dump($res->getStatusCode() );
		
		//var_dump($res->getBody()->getContents());
		//die();
		
		$redirect = $allGetVars['redirect_uri']."#access_token=".$result->access_token."&state=".urlencode($allGetVars['state']);
		
		return $response->withStatus(302)->withHeader('Location', $redirect);
		
	}
		
	
}
