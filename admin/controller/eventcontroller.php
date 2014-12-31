<?php
namespace admin\controller;

use sprite\mvc\controller;

class EventController extends BaseController {
	
	public function index($request,$response)
	{
		$user_id=$this->checkLogin();
		$response->storeTitle ="活动列表";
		$response->storeIntro ="管理你进行中的活动";
		$user_id = $this->current_user['user_id'];
		$events = \app\dao\EventDao::getSlaveInstance()->findAll (
			array("mer_id" => $user_id)
		);
		$response->events = $events;

		$this->layoutSmarty('index');
	}
	//商家申请结算活动余额
	public function refund($request,$response)
	{
		$user_id = $this->checkLogin();
		$event_id = $request->event;
		
		if($event_id )
		{
			//check ctime and status
			$eventInfo=\app\dao\EventDao::getSlaveInstance()->find(
			array('event_id'=>$event_id,'status'=>3));
			if($eventInfo)
			{
				$targtime = $eventInfo['ctime'] + 3*24*60*60;
				$_time = strtotime('now');
				$sql = "select count(*) as num from ym_user_event where event_id = $event_id and status in (1,3)";
				$ret = \app\dao\UserEventDao::getSlaveInstance()->getPdo()->getRow($sql);
				$num = $ret['num'];
				if($num > 0 )
				{
					$this->showError("有".$num."个用户已确认付款，但是您未确认交易,请处理后再申请");
				}
				else if($targtime > $_time) //活动结束超过3天
				{
					$this->showError("用户在活动结束1天内仍有可能领劵，还需等待". ($_time-$targtime)."秒哦");
				}
				else{
					try{
						$refund= new \app\service\EventSrv();
						$status=$refund->refund($event_id);
						//mail 状态
						
						if($status = true)
							$this->showError("结算成功");
						else
							$this->showError("结算成功");
					}catch(\Exception $e)
					{
						$this->showError($e->getMessage());
					}
				}
			}
			else {
				$this->showError("该活动还不能结算或者已经结算成功");
			}
			
		}else{
			$this->showError("活动状态异常，请联系管理员");
		}
	}
	
	//新建一个活动
	public function addevent($request,$response)
	{
		$user_id = $this->checkLogin();
		try{
			
			$event['event_name'] = $request->event_name;
			$event['product_link'] = $request->product_link;
			$event['price'] = (float)$request-> price;
			$event['amount'] = (int)$request->amount;
			$event['fanli'] =(float)$request->fanli;
			$event['noshipping'] = (int)$request->noshipping;  //是否支持免邮
			$event['user_id']=$user_id;
			$event['duringtime'] =(int)$request->duringtime;
			
		
		
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
					'mer_id' => $user_id 
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