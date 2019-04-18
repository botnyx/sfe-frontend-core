<?php

namespace Botnyx\Sfe\Frontend\Logic;

use Aura\Accept\AcceptFactory;



class Middleware {
	
		
	
	public function get($app,$container){
		
		
		$app->add(function ($request, $response, $next) {
			
			$fecfg = $this->get("frontendconfig");
			$available_languages = explode( ",",$fecfg->config->languages);
			
			
			if ($request->hasHeader('Cookie')  && isset($_COOKIE['language'])) {
				// Do something
				$language = $_COOKIE['language'];
				
				$request = $request->withAttribute('language', $language );
				
			}else{
				// Do something
				// nl-NL,nl;q=0.9,en-US;q=0.8,en;q=0.7
				// language negotiation
				$accept_factory = new AcceptFactory(
					array( "HTTP_ACCEPT_LANGUAGE" => $request->getHeader('Accept-Language') )
				);
				$accept = $accept_factory->newInstance();
				$language = $accept->negotiateLanguage($available_languages);
				
				if( is_bool($language) ){
					$language = $available_languages[0];
				}else{
					$language = $language->getValue();
				}
				$request = $request->withAttribute('language', $language );
				
			}
			
			
			
			
			
			if ( $request->hasHeader('Authorization') ) {
				// Do something
				//print_r( $request->getHeaders() );
				//var_dump( $request->getHeader('Authorization')[0] 
				$request = $request->withAttribute('token', $request->getHeader('Authorization')[0] );
				//die();
			}
			if ($request->hasHeader('Accept')) {
				// Do something
				// text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8
			}
			if ($request->hasHeader('DNT')) {
				// Do something
				// 1
			}
			
			if ($request->hasHeader('Cid')) {
				// Do something
				// clientID header.
			}
			
			
			
			
			#echo "<pre>";
			#var_dump( $language->getValue() );
			#var_dump($this->get("sfe"));
			//$request = $request->withAttribute('language', $language );
			
			//$request = $request->withAttribute('sfe', [$this->get("sfe"),$this->get("frontendconfig")]);
				
			
			#$response->getBody()->write('BEFORE');
			$response = $next($request, $response);
			#$response->getBody()->write('AFTER');

			return $response;
		});
		
		return $app;
	}
	
	
	
}

