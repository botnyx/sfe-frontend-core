<?php


namespace Botnyx\Sfe\Frontend;



class EndpointException {
	
	var $errorpagesfolder = "vendor/sfe-shared-core/templates/errorPages";
	var $debug = false;
	
	
	function __construct($rootdir){
		
		$this->debug = (int)_SETTINGS['twig']['debug'];
		$this->rootdir = _SETTINGS['paths']['root'];
		
		$this->view = new \Slim\Views\Twig( $rootdir.'/vendor/botnyx/sfe-shared-core/templates/errorPages', [
			'cache' => false
		]);
		
	}
	
	function phpErrorHandler($response,$error){
		
		$errorArray = array(
			"code"=>$error->getCode(),
			"message"=>$error->getMessage(),
			"file"=>$error->getFile(),
			"line"=>$error->getLine()
		);
		
		return $this->renderError($response,500,$errorArray);
	}
	
	function TransferException($response,$error){
		$_XX = explode(':',$error,2 );
		$curlErrorNo = str_replace('cURL error ','',$_XX[0]);
		#print_r($curlErrorNo);
		#print_r($_XX);
		
		$errorArray = array(
			"code"=>$curlErrorNo,
			"message"=>$error,
			"file"=>"",
			"line"=>""
		);
		
		return $this->renderError($response,502,$errorArray);
		
	}
	
	function renderError($response,$errorcode,$errorArray){
		return $this->view->render($response, 'HTTP'.$errorcode.'.html', [
			'debug'=>$this->debug,
			'error' => $errorArray
		]);
	}
	
	
	
	
}