<?php

namespace shop\controller;

use app\common\util\SubPages;

class EventController extends BaseController {
	
	public function index($request,$response)
	{
		
		
		$sql = "select count(*) as no from ym_event order by event_id desc ";
		$ret = \app\dao\EventDao::getSlaveInstance()->getpdo()->getRow($sql);
		$total=$ret['no'];

        $page_size = 30;
		// 当前页数
		$curPageNum = $request->page ? intval ( $request->page ) : 1;
		// url
		$url = preg_replace ( '/([?|&]page=\d+)/', '', $_SERVER ['REQUEST_URI'] );
		// 分页对象

		$page = new \app\common\util\SubPages( $url, $page_size, $total, $curPageNum );
		$limit = $page->GetLimit() ;
	
		
		$response->page = $page->GetPageHtml();

		$sql = "select * from ym_event order by event_id desc limit ".$limit;
		$ret = \app\dao\EventDao::getSlaveInstance()->getPdo()->getRows($sql);
		
		$response->tableCon = $ret;
		$response->_tag = $request->status;
		$this->layoutSmarty();
	}
	
	public function detail ($request,$response)
	{
		
		if(!$this->isPost())
		{
			$event_id = $request->id;
			$response->event = \app\dao\EventDao::getSlaveInstance()->find($event_id);
			$this->layoutSmarty();
		}else{
			$event_id = $request->eid;
			
			$name= $request->inputName;
			$link= $request->inputLink;
			$pic = $request->inputPic;
			$store=$request->store;
			$comment = $request->inputComment;
			if($request->iverify == 1 )
				$status= 1;
			else 
				$status=4;
			if($event_id == null)
				$this->showError("lost id");
			
			if(!$link || !pic)
				$this->showError("links can't be empty");
		
			\app\dao\EventDao::getMasterInstance()->edit($event_id,
				array(
					'event_name'=>$name,
					'product_link'=>$link,
					'pic_link'=>$pic,
					'comment'=>$comment,
					'status' => $status,
					'store' =>$store
				)
			);
			$this->success("index.php?_c=event","success");
			
		}
		
		
		
	}
	
	
}