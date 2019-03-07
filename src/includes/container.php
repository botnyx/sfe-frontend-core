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





    	return $frontEndConfig;
};
