<?php


use Slim\Http;
use Slim\Views;



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




//die(_SETTINGS['sfeFrontend']['clientId']);

foreach($container['frontendconfig']->routes as $route){
	$app->get( $route->uri,$route->fnc );

}
//$frontEndConfig['routes'];

$app->get( '/a/js/[{path:.*}]',    '\\Botnyx\\Sfe\\Frontend\\Core\\WebAssets\\JsEndpoint:get' );
$app->get( '/a/css/[{path:.*}]',   '\\Botnyx\\Sfe\\Frontend\\Core\\WebAssets\\CssEndpoint:get' );
$app->get( '/a/fonts/[{path:.*}]', '\\Botnyx\\Sfe\\Frontend\\Core\\WebAssets\\FontEndpoint:get' );


$app->get('/expiretest',  function ( $request,  $response, array $args){

		// https://backend.devpoc.nl/api/cfg/myclientId

		$res = $response->write("justtesting");
		//$resWithExpires = $this->cache->withExpires($res, time() + 3600);
		$res = $this->cache->withExpires($res, time() + 3600);
		$resWithLastMod = $this->cache->withLastModified($res, time() - 3600);


		return $resWithLastMod;

})->add(new \Slim\HttpCache\Cache('public', 86400));;










$app->get('/robots.txt',  function ( $request,  $response, array $args){
  $res = "User-agent: *".PHP_EOL."Disallow: /";
  return $response->write($res);

});
