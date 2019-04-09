<?php

namespace Botnyx\Sfe\Frontend\Core;


use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Botnyx\Sfe\Frontend\Logic as Logic;

class SlimLogic {
		function __construct(){
		
		$this->middleware = new Logic\Middleware();
		$this->container = new Logic\Container();
		$this->routes = new Logic\Routes();
		
	}
	
	public function getContainer($container){
		return $this->container->get($container);
	}
	
	
	
	
	public function getMiddleware($app,$container){
		return $this->middleware->get($app,$container);

	}
	
	
	
	
	public function getRoutes($app,$container){
		return $this->routes->get($app,$container);
		
	}

	
}