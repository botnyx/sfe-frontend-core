<?php


use Kevinrob\GuzzleCache;
use Kevinrob\GuzzleCache\Storage;
use Kevinrob\GuzzleCache\Strategy;

use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\PredisCache;

use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy;
use Kevinrob\GuzzleCache\Strategy\PublicCacheStrategy;
use Kevinrob\GuzzleCache\Storage\DoctrineCacheStorage;

//use Kevinrob\GuzzleCache\KeyValueHttpHeader;
//use Kevinrob\GuzzleCache\Strategy\GreedyCacheStrategy;

$container['cache'] = function () {
    return new \Slim\HttpCache\CacheProvider();
};


$container['frontendconfig'] = function($c){

    	/*
    		if frontend is enabled, serve it...  else 403
    	*/
    	//if(array_key_exists('sfeFrontend',_SETTINGS)){
    	// frontend remote-Config
    	// Create default HandlerStack
    	$stack = GuzzleHttp\HandlerStack::create();
    	$stack->push(
    		  new \Kevinrob\GuzzleCache\CacheMiddleware(
    			new \Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy(
    			  new \Kevinrob\GuzzleCache\Storage\DoctrineCacheStorage(
    				new \Doctrine\Common\Cache\FilesystemCache(_SETTINGS['paths']['temp'].'/guzl')
    			  )
    			)
    		  ),
    		  'cache'
    		);

    	$headers = ['referer' => 'https://'._SETTINGS['paths']['fdqn'],'origin' => 'https://'._SETTINGS['paths']['fdqn'] ];

    	$cachedClient = new GuzzleHttp\Client([
    		'headers' => $headers,
    		'handler' => $stack
    	]);

    	try{
    		$res = $cachedClient->request('GET', _SETTINGS['sfeFrontend']['sfeBackend'].'/api/cfg/'._SETTINGS['sfeFrontend']['clientId']);
    		$frontEndConfig = json_decode($res->getBody());
    	}catch(Exception $e){
    		die($e->getMessage());
    	}
		
		#echo "<pre>";
		#print_r($frontEndConfig);
		#die();


    	return $frontEndConfig->data;
};



//Override the default Not Found Handler before creating App
$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        $view = new \Slim\Views\Twig(_SETTINGS['paths']['root'].'/vendor/botnyx/sfe-shared-core/templates/errorPages', [
			'cache' => false
		]);
		return $view->render($response, 'HTTP404.html', [
			'name' => $args['name']
		]);
		//return $response->withStatus(404)->withHeader('Content-Type', 'text/html')->write('CUSTOM Page not found');
    };
};

$container['phpErrorHandler'] = function ($c) {
    return function ($request, $response, $error) use ($c) {
        
		
		
		$frontException = new \Botnyx\Sfe\Frontend\EndpointException(_SETTINGS['paths']['root']);
		
		
		
		
		return $frontException->phpErrorHandler($response,$error);
		
		
		
		
		var_dump($error->getMessage());
		var_dump($error->getCode());
		var_dump($error->getLine());
		var_dump($error->getFile());
		return $response->withStatus(500)->withHeader('Content-Type', 'text/html')->write('CUSTOM Something went wrong!');
    };
};
/*
*/









