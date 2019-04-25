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
			  ),
			  1, // the TTL in seconds
			  new KeyValueHttpHeader(['Authorization']) // Optionnal - specify the headers that can change the cache key
			)
		  ),
		  'cache'
		);
		// Initialize the client with the handler option
		$this->client = new \GuzzleHttp\Client([
			/*'handler' => $this->_stack,*/
			'http_errors'=>true
		]);
		// Initialize the client with the handler option
		$this->cachedclient = new \GuzzleHttp\Client([
			'handler' => $this->_stack,
			'http_errors'=>true
		]);
		
		
		//['paths']['root']
		$this->errorPage = new \Botnyx\Sfe\Frontend\EndpointException( $this->sfe);

    }

	public function get(ServerRequestInterface $request, ResponseInterface $response, array $args = []){
		
		$token = $request->getAttribute('token');
		
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
		$extraoption =  [
			'headers' => [
				'Authorization' => $token,
			]
		];
		
		
		//die(_SETTINGS['sfeFrontend']['clientId']);
		error_log("yes!");
		error_log( $Backend.'/api/sfe/'.$Clientid.'/uri/'.$request->getAttribute('language').'/'.$theEndpointId."?".http_build_query($args) );
		
		try{
			$res = $this->client->request('GET', $Backend.'/api/sfe/'.$Clientid.'/uri/'.$request->getAttribute('language').'/'.$theEndpointId."?".http_build_query($args) ,$extraoption );

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
		
		
		$allGetVars['userState'];
			
		
		//userState=AuthenticatedNotRegistered
			
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
		
		//header("Location: ".$allGetVars['redirect_uri']."#access_token=".$x->access_token."&state=".urlencode($allGetVars['state']));

		
$clientid="b75d3c0b-ae2a-420b-9635-c910b3ec4ed8";
$clientsecret="dCHQiWosJn79u_UrFF1YmrTW-a2VbWCtjc5gNbcpGhY";


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://account.trustmaster.org/oauth2/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_USERPWD, $clientid . ":" . $clientsecret);  

$data = array(
    'grant_type' => 'authorization_code',
    'code' => $_GET['code'],
	'redirect_uri' => $_GET['redirect_uri']
);

curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
$output = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);	

$x = json_decode($output);

header("Location: ".$_GET['redirect_uri']."#access_token=".$x->access_token."&state=".urlencode($_GET['state']));

		
		
		
		$Backend = "https://".$this->sfe->hosts->backend;
		$Clientid = $this->sfe->clientid;


		$res = $this->client->request('GET', $Backend.'/api/sfe/'.$Clientid.'/ui/sw');


		return $response->write($res->getBody())->withHeader("content-type","application/javascript; charset=utf-8");


		//return $response->write("xx");
	}
	
	
	public function My(ServerRequestInterface $request, ResponseInterface $response, array $args = []){
		$token = $request->getAttribute('token');
		$language = $request->getAttribute('language');
		
		
		// Initialize the client with the handler option
		$client = new \GuzzleHttp\Client([
			/*'handler' => $this->_stack,*/
			'http_errors'=>true
		]);
		$extraoption =  [
			'headers' => [
				'Authorization' => $token,
			]
		];
		
		
		
		if($args['endpoint']=='userinfo'){
			try {
				//$client->request('GET', 'https://github.com/_abc_123_404');
				$res = $client->request('GET', "https://".$this->sfe->role->hosts->auth."/oauth2/userinfo" ,$extraoption );
			} catch (ClientException  $e) {
				//echo Psr7\str($e->getRequest());
				return $response->withStatus($e->getCode());
				
			}
			//$res->getStatusCode();
			//var_dump($res->getBody()->getContents() );
			
		}elseif($args['endpoint']=='registration'){
			
		}elseif($args['endpoint']=='settings'){
			
		}elseif($args['endpoint']=='menu'){
			
		}
		
		//die("___________");
		
		//$res->getStatus();
		$status = $res->getStatusCode();
		
		
		#print_r($status);
		#print_r($res);
		#die();
		
		
		#$args['endpoint'];
		/*
			'/my/registration'
			'/my/userinfo'	
			'/my/settings'
			'/my/menu',
		*/
		
		
		/*
		
		GET registration
			
		registration.applicationId
		registration.authenticationToken
		registration.cleanSpeakId
		registration.data
		registration.id
		registration.insertInstant
		registration.lastLoginInstant
		registration.preferredLanguages	
		registration.roles	
		registration.timezone	
		registration.tokens	
		registration.username	
		registration.usernameStatus	
		registration.verified
			
		PUT registration
			generateAuthenticationToken
			registration.applicationId
			registration.data
			registration.preferredLanguages
			registration.roles
			registration.timezone
			registration.username
			
			*/
		
		
		
		
		#$args['endpoint'];
		/*
			'/my/registration'
			'/my/userinfo'	
			'/my/settings'
			'/my/menu',
		*/
		#$out = array("x");
		#return $response->withJson(array($token,$language));
		
		
		
		
		//"https://".$this->sfe->role->hosts->auth."/oauth2/token";
		
		$result = json_decode($res->getBody()->getContents());
		//return $response->write($res->getBody()->getContents())->withStatus($status);
		
		return $response->withJson($result)->withStatus($status);
		
	}
}
