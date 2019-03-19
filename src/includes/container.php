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




//Override the default Not Found Handler before creating App
/*
$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        $view = new \Slim\Views\Twig(_SETTINGS['paths']['root'].'/vendor/botnyx/sfe-shared-core/templates/errorPages', [
			'cache' => false
		]);
		return $view->render($response, 'HTTP404.html', [
			'name' => $args['name']
		])->withStatus(404);
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

*/









