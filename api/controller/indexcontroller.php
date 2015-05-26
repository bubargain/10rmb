<?php

namespace api\controller;

use sprite\mvc\controller;
use \sprite\cache\CacheManager;


class indexcontroller extends Controller {
	public function __construct($request, $response) {
		
		//parent::__construct ( $request, $response );
	}
	
	public function index($request,$response){
		 $this->renderJson(array(
			'title'=>'Welcome to use API of MODE ,please follow the official docs to integrate',
			'ctime'=>time()
		 ));
	}
	
}