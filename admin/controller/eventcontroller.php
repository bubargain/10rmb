<?php
namespace admin\controller;

use sprite\mvc\controller;

class EventController extends BaseController {
	
	public function index($request,$response)
	{
		$response->storeTitle ="活动列表";
		$response->storeIntro ="管理你进行中的活动";
		$user_id = $this->current_user['user_id'];
		$events = \app\dao\EventDao::getSlaveInstance()->findAll (
			array("user_id" => $user_id)
		);
		$response->events = $events;

		$this->layoutSmarty('index');
	}
	
	//新建一个活动
	public function addevent($request,$response)
	{
		$user_id = $this->current_user['user_id'];
		if(!$user_id) //未登录，跳转到首页 ，强验证
		{
			header ( "Location:".ROOT_PATH );
			exit();
		}
		try{
			
			$event['event_name'] = $request->event_name;
			$event['product_link'] = $request->product_link;
			$event['price'] = (float)$request-> price;
			$event['amount'] = (int)$request->amount;
			$event['fanli'] =(float)$request->fanli;
			$event['noshipping'] = (int)$request->noshipping;  //是否支持免邮
			$event['user_id']=$user_id;
		
	
			//添加	
			$nevent= new \app\service\EventSrv();
			$status=$nevent->addEvent($event);
			if($status== true)
			{
				//$this->showError("您的活动已提交审核，请移步活动管理查看审核状态");
				header("Location: index.php?_c=event&s=1");
			}
			else {
				$this->showError("活动未添加成功");
			}
		}catch(\Exception $e)
		{
			$this->showError("提交信息，添加失败：".$e->getMessage());
		}

		
	}
	
	//状态改变
	public function status($request,$response)
	{
		try{
			$event_id = $request->eventid;
			if(!$event_id )
				self::renderjson(array('ret'=>'no event_id'));
			$status = $request->status;
			$cstatus = $status == 1 ? 2 : 1; // 状态1和2互换
			$user_id = $this->current_user['user_id'];
			$checkV= \app\dao\EventDao::getMasterInstance()->find (
				array(
					'event_id'=>$event_id,
					'user_id' => $user_id 
				)
			);
			if(!$checkV)   //没有修改权利
				self::renderjson(array('ret'=>'false'));
			
			 \app\dao\EventDao::getMasterInstance()->edit($event_id,array(
					'status' => $cstatus
				)
			);
			self::renderjson(array('ret'=>'true'));
		}catch(\Exception $e)
		{
			$this->showError($e->getMessage());
		}
	}
	
	
}