<?php


namespace Botnyx\Sfe\Frontend;



class EndpointException {
	
	var $errorpagesfolder = "vendor/sfe-shared-core/templates/errorPages";
	var $debug = false;
	
	
	function __construct($sfe ){
		
		$this->sfe =$sfe;
		
		$this->sfe->hosts->backend;
		$this->sfe->paths->root;
			
		
		
		$this->debug = 1;
		#$this->rootdir = _SETTINGS['paths']['root'];
		
		$this->view = new \Slim\Views\Twig( $this->sfe->paths->root.'/vendor/botnyx/sfe-shared-core/templates/errorPages', [
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
	
	function TransferException($response,$error,$file=""){
		$_XX = explode(':',$error,2 );
		$curlErrorNo = str_replace('cURL error ','',$_XX[0]);
		#print_r($curlErrorNo);
		#print_r($_XX);
		
		$errorArray = array(
			"code"=>$curlErrorNo,
			"message"=>$error,
			"file"=>$file,
			"line"=>""
		);
		#print_r($errorArray);
		#die();
		return $this->renderError($response,502,$errorArray);
		
	}
	
	function renderError($response,$errorcode,$errorArray){
		return $this->view->render($response, 'HTTP'.$errorcode.'.html', [
			'debug'=>$this->debug,
			'error' => $errorArray
		])->withStatus($errorcode);
	}
	
	function backendException($response,$remote_error){
		;
		$remote_error->status;
		$remote_error->statusmsg;
		$remote_error->data;
		$errorArray = array(
			"code"=>$remote_error->code,
			"message"=>$remote_error->statusmsg,
			"file"=>$remote_error->data,
			"line"=>""
		);
		#print_r($errorArray);
		#die();
		return $this->renderError($response,503,$errorArray);
		
	}
	
	
}