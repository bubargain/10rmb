<?php
namespace touch\controller;

use sprite\mvc\controller;

class apiController extends Controller{
	
	/*
	 * ajax 活动event数组
	 */
	public function newevents($request,$response){
		$page=$request->page;
		if($page)
		{
			$count = $request->count? $request->count : 12;
			$event= new \app\service\SearchSrv();
			$newEvents= $event->newEvents($page,$count);
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