<?php

namespace app\service;

use app\dao\EventDao;


//返利活动管理
class EventSrv extends BaseSrv {
	const UNPAY_ORDER = 0; //待付款
    const PAYED_ORDER = 1; //待发货
    const SHIPPING_ORDER = 2; //已发货
    const RECEIVED_ORDER = 4; //已收货
    const FINISHED_ORDER = 4; //已完成
    const CLOSED_ORDER = 99; //关闭订单
	
	//搜索商家活动列表
	public function searchEventList($merchant_id)
	{
		
		$sql = "select event_id, event_name from ym_event where status in (1,2,3,88) and mer_id = $merchant_id order by event_id desc";
        $list = \app\dao\UserEventDao::getSlaveInstance()->getpdo()->getRows($sql);
        return $list;
	}
	
	/**
	 * 
	 * 为新用户分配新手任务
	 * @param  $user_id
	 */
	public function signPrivilegeEvent($user_id){
		$events= \app\dao\EventDao::getSlaveInstance()->findAll(array('status'=>88));
		$time= strtotime('now');
		//var_dump($events);
		if($events)
		{
			
			try{
				
				\app\dao\UserEventDao::getMasterInstance()->beginTransaction();
				foreach($events as $event)
				{			
					
						//新手任务10buck
						$fanli = (float)$event['fanli'] * PROFITRATE;
						if($event['noshipping'])
							$totalfanli = $event['price'] + $fanli;
						else 
							$totalfanli = $fanli;
							
						\app\dao\UserEventDao::getMasterInstance()->add(
							array(
								'event_id' => $event['event_id'],
								'user_id' => $user_id,
								'price' =>  $event['price'],
								'fanli' =>  $fanli,
								'profit' => $event['fanli']-$fanli,
								'totalfanli'=> $totalfanli,
								'utime' => $time,
								'ctime' => $event['ctime'],
								'etime' => $time,
								'status'=>100,
								'livetime' => $event['livetime'],
								'noshipping' => $event['noshipping'],
								'store' => $event['store'],
								'event_name' => $event['event_name'],
								'product_link' => $event['product_link'],
								'pic_link' => $event['pic_link']
							)
							);
						//$sql= "update ym_event set applied = applied +1 where event_id =".$event['event_id'];
				}
				\app\dao\UserEventDao::getMasterInstance()->commit();
				//die();
			}catch(\Excetpion $e)
			{
				\app\dao\UserEventDao::getMasterInstance()->rollBack();
				throw $e;
			}
		}
		return true;
	}
	
	
	/**
	 * 商家活动结束（未通过），资金结算
	 * status  4 审核未过  3已完成
	 */
	public function refund($event_id)
	{
		if(!$event_id)
			return false;
		$info = \app\dao\EventDao::getSlaveInstance()->find($event_id);
		if($info['status'] == 3 || $info['status'] ==4 )
		{
			
			try{
				$dao= \app\dao\EventDao::getMasterInstance();
				$dao->beginTransaction();
				//查询总返利金额
				$sql= "select * from ym_user_event where event_id = $event_id and status in (1,2,3,4)";
				$orders= $dao->getPdo()->getRows($sql);
				
				$amount=0;
				if($orders)
				{
					foreach($orders as $order)
					{
						if($order['status'] != 4)
							throw new \Exception ('有用户订单处于待确认状态');
						else 
						{
							$amount += $order['totalfanli'];
							$amount += $order['profit'];
						}
					}
				}
				
				$sql = "select uvalue from ym_setting where ukey='exchange_rate'";
				$exchangerate =  $dao->getPdo()->getRow($sql);
				
				if(!$exchangerate)
				 	throw new \Exception ('实时汇率信息获取不到');
			
			//查询冻结金额
				
				$sql= "select * from ym_event where event_id = $event_id";
				$eventDetail = $dao->getPdo()->getRow($sql);
				
				$lockamount=0;
				if($eventDetail['noshipping']==0)
				{
					$lockamount =  floatval($eventDetail['fanli'])* floatval($eventDetail['amount']);		
				}
				else {
					$lockamount = (floatval($eventDetail['price']) + floatval($eventDetail['fanli']))* floatval($eventDetail['amount']);
				}
				
				$refunAmount = ($lockamount- $amount)* (float)$exchangerate['uvalue']; //返还的金额
				
				//日志记录
				 \app\dao\UserCurrencyDao::getMasterInstance()->add(
					array(
						'user_id'=> $eventDetail['mer_id'],
						'amount'=>$refunAmount,
						'unit'=>'rmb',
						'ctime'=>strtotime('now'),
						'status'=>3,
						'sn' => 'buck'.$event_id
					)
				);
				
				
				//修改event状态为5 已完成
				\app\dao\EventDao::getMasterInstance()->edit($event_id,array('status'=>5));
				
				//返还剩余资金
				$sql ="update ym_user_info set rmb = rmb + $refunAmount where user_id =".$eventDetail['mer_id'];
			
				
				 $dao->getPdo()->exec($sql);

				\app\dao\EventDao::getMasterInstance()->commit();
				
				
			}catch(\Exception $e)
			{
				\app\dao\EventDao::getMasterInstance()->rollBack();
				return false;
			}
			//邮件提醒
			try{
				$merchant = \app\dao\UserInfoDao::getSlaveInstance()->find(array('user_id'=>$eventDetail['mer_id']));
				if($merchant['email'])
				{
						$mail = new \app\service\MailSrv();
						$mail->sendMail($merchant['email'], "审核不通过，冻结资金已退还", "您的活动".$name." 未通过审核，冻结资金已返还<br/>10BUCK审核团队");
				}
			}catch(\Exception $e)
			{
					//
			}
			
			return true;
	}else 
	{
		return false;
	}
}
	
	
	/**
	 * 
	 * check whether some status has expired
	 * @param unknown_type $userEvent
	 */
	public function updateStatus($userEvent)
	{
		/*$_time = strtotime("now");
		
		try{
			\app\dao\EventDao::getMasterInstance()->beginTransaction();
			foreach($userEvent as $event)
			{
			
				//活动已经结束,用户未领劵
				if($event['status']==100 && $event['ctime'] + $event['livetime'] < $_time)
				{
					
					self::deleteEvent($event['event_id']);
			
					continue;
				}
				//用户已经领劵，但是超过24小时未付款
				else if ($event['status']==0 && $event['utime'] + 8*60*60 < $_time )
				{
					\app\dao\UserEventDao::getMasterInstance()->edit($event['id'],
						array('status'=>99,'etime'=>strtotime('now'))
					);
					
					//商家applied数减1
					// 已改为mysql触发器操作
					//$sql = "update ym_event set applied= applied -1 where event_id = ".$event['event_id'];
					//\app\dao\UserEventDao::getMasterInstance()->getPdo()->exec($sql);
	
				}			
				
			}
	
			\app\dao\EventDao::getMasterInstance()->commit();
			return true;
		}catch(\Exception $e){
			\app\dao\EventDao::getMasterInstance()->rollBack();
			throw $e;
		}*/
		
		return true;  //状态变更已经移至mysql 定时计划
		
	}
	
	/**
	 * 
	 * Event is expired
	 * @param unknown_type $id
	 */
	private function deleteEvent($id)
	{
		$_time = strtotime("now");
		//商家活动表中活动停止
		$_pdo=\app\dao\EventDao::getMasterInstance()->getPdo();
		$sql = 'update ym_event set status=3 where event_id='.$id;
		$_pdo->exec($sql);  
		
		//用户还未申请bcode的，终止申请
		$sql= 'delete from ym_user_event where status=100 and event_id='.$id;
		$_pdo->exec($sql);  
		//已经申请bcode但24小时还未使用的，停止使用
		$sql= "update ym_user_event set status=4 ,etime=$_time where etime + 24*60*60 < ".$_time." and status=0 and event_id=".$id; 
		$_pdo->exec($sql); 
		
		
	}
	
	
	/**
	 * user confirm paid by bcode
	 * $user_id : bcode owner id
	 * $id :  user event id
	 */
	public function confirmCodeUse($user_id,$id)
	{
		
		$info = \app\dao\UserEventDao::getMasterInstance()->find(
			array(
				'user_id'=> $user_id,
				'id' => $id,
			)
		);
		if(!$info)
			throw new \Exception('Unknown Error happened');
		else if($info['status']!= 0)		
			throw new \Exception('cant change status ');
		else{
			return \app\dao\UserEventDao::getMasterInstance()->edit($id,
				array('status'=>1,'etime'=>strtotime('now'))
			);
		}
		
	}
	
	
	
	//新增event
	public function addEvent($post){
			
		
		 $status = self::checkMerchant($post['user_id']);
		 
		 $checkpost = self::checkPost($post);
		 
		 if($status == false)
		 {
		 	throw new \Exception("Are you Hacker??? we are tracking you down",'100096');
		 }
		 
		 if($status == false)
		 {
		 	throw new \Exception("商家没有权限发布活动，详情请咨询客服",'100096');
		 }
 
		$lockamount=0;
		if($post['noshipping']==0)
		{
			$lockamount =  floatval($post['fanli'])* floatval($post['amount']);
						
		}
		else {
			$lockamount = (floatval($post['price']) + floatval($post['fanli']))* floatval($post['amount']);
		}
		if( $lockamount==0 )
			throw new \Exception("冻结金额异常,管理员将介入！",'100097');
		 
		/*
		 * 三步数据库操作
		 * 1用户表扣钱
		 * 2交易记录-锁定金额
		 * 3添加交易
		*/
			
		 try{
		 	
			 \app\dao\EventDao::getMasterInstance()->beginTransaction();
			 self::freezeMoney($post['user_id'],$lockamount);
			 self::recordEvent($post);
			 \app\dao\EventDao::getMasterInstance()->commit();
			 return true;
			 
		 }catch(\Exception $e)
		 {
		 	\app\dao\EventDao::getMasterInstance()->rollBack();
		 	throw new \Exception ($e->getMessage());
		 	return false;
		 }
		  
	}
	
	
	//double check 验证post信息是否正确
	private function checkPost($post)
	{
		if(!$post['user_id'] || !$post['event_name']|| !$post['price'])
		{
			return false;
		}
		
	}
	
	
	private function freezeMoney($user_id,$amount,$unit='美元')
	{
		
		$user= \app\dao\UserInfoDao::getSlaveInstance()->find(  array('user_id' => $user_id));
		$rate = \app\dao\SettingDao::getSlaveInstance()->find( array('ukey' => 'exchange_rate'));
		$amountLeft= $user['rmb'] - $amount * (float)$rate['uvalue'] ;
	
		if($amountLeft < 0 ){
			throw new \Exception ("冻结金额异常",'100098');
		}
		 \app\dao\UserInfoDao::getMasterInstance()->edit( array('user_id'=> $user_id) , array('rmb'=>$amountLeft)); //减用户账户总额
		
		  \app\dao\UserCurrencyDao::getMasterInstance()->add(
		 array('user_id'=>$user_id,'amount'=>$amount,'unit'=>$unit,'status'=>'2')
		);
		
		
	}
	
	
	//新建一条活动记录
	private function recordEvent($post)
	{
		
		\app\dao\EventDao::getMasterInstance()->add(
			array(
				'event_name' => $post['event_name'],
				'mer_id' => $post['user_id'],
				'product_link'=> $post['product_link'],
				'price' => $post['price'],
		
				'amount'=>$post['amount'],
				'fanli'=>$post['fanli'],
				'noshipping'=>$post['noshipping'],
				'status'=>0,
				'ctime'=>time()
			)
		);
	}
	
	
	//商家是否有权限发布活动，是否达到活动上限
	private function checkMerchant($user_id)
	{
		//check isAdmin
		$isAdmin = self::isAdmin($user_id);
		if($isAdmin == false)
		{
			throw new \Exception ("您还未成为商家，或者商家权限不够",'100097');
		}
		else if($isAdmin > 0 )
		{
			return true;
		}
		else 
		{
			//不同等级的商家可以发布的活动数不受限制
			return false;
		}
	}
	
	//是否在商家目录中
	private function isAdmin($user_id) {
		
		$admin= \app\dao\AdminDao::getSlaveInstance ()->find ( $user_id ) ;
		if(!$admin)
			return false;
		else 
			return $admin['level'];
	}
	
  /**
     * @param $buyer_id
     * @return array
     * @desc 返回订单列表
     */
    public function orders($buyer_id, $status = 0, $limit = '0, 20') {
        $ret = array();
        $ret['list'] = array();

            $list = \app\dao\UserEventDao::getSlaveInstance()->findAll(
             array('user_id'=>$buyer_id,'B.status'=>$status)
             );
             self::updateStatus($list); //检查和更新状态信息
             
       		$list = \app\dao\UserEventDao::getSlaveInstance()->findAll(
             array('user_id'=>$buyer_id,'B.status'=>$status)
             ); //更新后的订单信息
        return $list;
    }
    

    
	
	
}