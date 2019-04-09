<?php

namespace Botnyx\Sfe\Frontend\Logic;



class Middleware {
	
		
	
	public function get($app,$container){
		
		$app->add(function ($request, $response, $next) {
			
			
			if ($request->hasHeader('Accept-Language')) {
				// Do something
				// nl-NL,nl;q=0.9,en-US;q=0.8,en;q=0.7
			}
			if ($request->hasHeader('Authorization')) {
				// Do something
				
			}
			if ($request->hasHeader('Accept')) {
				// Do something
				// text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8
			}
			if ($request->hasHeader('DNT')) {
				// Do something
				// 1
			}
			if ($request->hasHeader('Cookie')) {
				// Do something
				
			}
			if ($request->hasHeader('Cid')) {
				// Do something
				// clientID header.
			}
			
			
			
			
			
			$request = $request->withAttribute('sfeRequest', 'bar');
				
			
			//$response->getBody()->write('BEFORE');
			$response = $next($request, $response);
			//$response->getBody()->write('AFTER');

			return $response;
		});
		
		return $app;
	}
	
	
	
}

