<?php
namespace touch\controller;

use sprite\mvc\controller;

class apiController extends Controller{
	
	/*
	 * ajax 活动event数组
	 */
	public function newevents($request,$response){
		$page=intval($request->page);
		if($page)
		{
			$cate = intval($request->cate);
			$count = $request->count? $request->count : 8;
			$event= new \app\service\SearchSrv();
			$newEvents= $event->newEvents($page,$count,$cate);
			$this->renderJson(array(
				'count' =>count($newEvents),
				'events'=>$newEvents
			));
		}
		else{
			$this->renderJson(array(
				'count'=>0
			));
		}
	
	}
	
	
	/*
	 * add wish for event
	 */
	public function wish($request,$response){
		 	
	}
	
	
}