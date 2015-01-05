<?php

namespace shop\controller;

class XishuashuaController extends BaseController {
	
	//显示某一活动的申请状态
	public function index($request,$response)
	{
		if($request->searchContent)
		{
			$event_id = $request->searchContent;
			$sql= "select * from ym_user_event where event_id =$event_id and status <100 order by id desc";
			$res = \app\dao\UserEventDao::getSlaveInstance()->getPdo()->getRows($sql);
			$response->info=$res;
		}
		$this->LayoutSmarty();
	}
	
	//分配一个新劵给管理员用户
	public function newcoupon($request,$response)
	{
		$event_id=$request->event_id;
		if(!$event_id)
			$this->showError('没有输入活动ID');
		$user_id = 1; //分配劵给用户1， danielma@kitetea.com
		$time=strtotime('now');

		$event= \app\dao\EventDao::getSlaveInstance()->find($event_id);
		if($event)
		{
			$fanli = (float)$event['fanli'] * PROFITRATE;
			\app\dao\UserEventDao::getMasterInstance()->add(
						array(
							'event_id' => $event['event_id'],
							'user_id' => $user_id,
							'price' =>  $event['price'],
							'utime' => $time,
							'ctime' => $event['ctime'],
							'etime' => $time,
							'status'=>1,
							'fanli'=>$fanli,
							'bcode'=>substr(strtotime('now'),2),
							'totalfanli'=>$fanli+$event['price'],
							'profit'=>(float)$event['fanli'] - $fanli,
							'livetime' => $event['livetime'],
							'noshipping' => $event['noshipping'],
							'store' => $event['store'],
							'event_name' => $event['event_name'],
							'product_link' => $event['product_link'],
							'pic_link' => $event['pic_link']
						)
					);
			$sql="update ym_event set applied = applied +1 where event_id=".$event['event_id'];
			\app\dao\UserEventDao::getMasterInstance()->getPdo()->exec($sql);
			header("Location: index.php?_c=xishuashua&searchContent=$event_id");
		}
		else
		{
			$this->showError('活动号不对');
		}
	}
	
	
	
	//更新返利劵对应的订单sn
	public function updatesn($request,$response){
		$user_id = $this->checkLogin();
		if($user_id)
		{
			$event_id =$request->event_id;
			$sn = $request->sn;
			if($event_id && $sn)
			{
				\app\dao\UserEventDao::getMasterInstance()->edit($event_id,array('order_sn'=>$sn,'status'=>1));
				$this->renderJson(array('status'=>true));die();
			}
		}
		$this->renderJson(array('status'=>false));
	}
	
	
	//取消返利劵
	public function cancel($request,$response){
		$user_id = $this->checkLogin();
		if($user_id)
		{
			$event_id =$request->event_id;
			if($event_id )
			{
				\app\dao\UserEventDao::getMasterInstance()->edit($event_id,array('status'=>99));
				$this->renderJson(array('status'=>true));die();
			}
		}
		$this->renderJson(array('status'=>false));
	}
	
}