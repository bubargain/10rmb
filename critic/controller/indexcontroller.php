<?php

namespace critic\controller;

class IndexController extends BaseController {
	public function index($request, $response) {
		$response->storeTitle = 'WELCOME BACK, JUDGE!';
		$response->storeIntro = "Let's keep leading the trends";
		$this->layoutSmarty ( 'index' );
	}
	// 错误信息页面
	public function error($request, $response) {
		$response->title = '错误页面';
		$response->showNav = 'no';
		$response->msg = $request->msg;
		$this->layoutSmarty ( 'error' );
	}
	
}