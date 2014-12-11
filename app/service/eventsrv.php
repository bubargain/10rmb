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
		
		$sql = "select event_id, event_name from ym_event where status < 4 order by event_id desc";
        $list = \app\dao\UserEventDao::getSlaveInstance()->getpdo()->getRows($sql);
        return $list;
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
				array('status'=>1)
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
		 	//return false;
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
				'user_id' => $post['user_id'],
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
       
        return $list;
    }
    

    
	
	
}