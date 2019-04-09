<?php

namespace Botnyx\Sfe\Frontend\Logic;

use Botnyx\Sfe\Frontend\Config as Config;

class Container {
	
	public function get($container){
		
		//print_r($container->get('sfe'));
		//die();
		#$container->get('sfe')->clientid;
		#$container->get('sfe')->paths->templates;
		#$container->get('sfe')->paths->temp;
		#$container->get('sfe')->paths->root;
		
		#$container->get('sfe')->hosts->backend;
		#$container->get('sfe')->debug;
		
		
		
		$container['cache'] = function ($c) {
			return new \Slim\HttpCache\CacheProvider();
		};

		
		$container['frontendconfig'] = function($c){
			$cfg = new Config\RemoteConfig($c);
			return $cfg->get();
			
		};

		
		
		
		
		return $container;
	}
	
	
	
}

