<?php

namespace app\service;

use \app\dao\OrderDao;
use \app\dao\OrderExtmDao;
use \app\dao\RefundDao;
use \app\dao\UserInfoDao;

/**
 * 退款服务
 *
 * @author daniel
 *        
 */
class RefundSrv extends BaseSrv {
	
	/**
	 * 申请提现
	 *
	 *
	 * @throws \Exception
	 * @throws Exception
	 */
	public function apply($user_id,$amount,$unit,$comment=null) {
		
		
		$res=\app\dao\UserCurrencyDao::getMasterInstance()->find(
			array(
				'user_id' =>$user_id,
				'status' => 5
			)
		);
		
		if($res)
			throw new \Exception('We are handling your former request,please wait');//有未完成的申请，不允许修改
		else{
			$query = \app\dao\UserInfoDao::getSlaveInstance()->find($user_id);
			
			$moneyleft =$query[$unit];
			if($moneyleft < $amount )
				throw new \Exception('you dont have enough money');
			\app\dao\UserCurrencyDao::getMasterInstance()->add(
					array(
						'user_id'=> $user_id,
						'amount' => $amount,
						'unit'  => $unit,
						'ctime' => strtotime('now'),
						'status' => 5,
						'comment'=>$comment
					)
				);
		//邮件提醒
		try{
				
			
				$mail = new \app\service\MailSrv();
				$mail->sendMail("contact@kitetea.com", "[10BUCK] someone want refund!", "有人申请提现哦，请尽快登录系统处理 <br/> 10BUCK Team");
			
			}catch(\Exception $e)
			{
					//
			}
			return true;
		}
	}
		
		
		
//		if (! $data ['card_no'] || ! $data ['order_id'])
//			throw new \Exception ( '请完善退款资料', '5000' );
//		
//		$order = OrderDao::getSlaveInstance ()->find ( $data ['order_id'] );
//		$order_extm = OrderExtmDao::getSlaveInstance ()->find ( $data ['order_id'] );
//		
//		if (! $order || $order ['buyer_id'] != $data ['user_id'])
//			throw new \Exception ( '只能对自己订单申请退款', '5001' );
//		
//		if ($order ['refund_status'] != 0)
//			throw new \Exception ( '已经提交退款申请，请等待客服处理', '5001' );
//		
//		$data ['order_sn'] = $order ['order_sn'];
//		$data ['seller_id'] = $order ['seller_id'];
//		$data ['refund_status'] = RefundDao::REFUND_ACCEPT;
//		$data ['user_id'] = $order ['buyer_id'];
//		$data ['refund_money'] = $data ['order_amount'] = $order ['order_amount'];
//		$data ['consignee'] = $order_extm ['consignee'];
//		$data ['phone_mob'] = $order_extm ['phone_mob'];
//		
//		$data ['ctime'] = $order ['utime'] = time ();
//		try {
//			RefundDao::getMasterInstance ()->beginTransaction (); // 开启事务
//			$id = RefundDao::getMasterInstance ()->add ( $data );
//			OrderDao::getMasterInstance ()->edit ( $data ['order_id'], array (
//					'refund_status' => $data ['refund_status'] 
//			) );
//			UserInfoDao::getMasterInstance ()->edit ( $data ['user_id'], array (
//					'alipay_no' => $data ['card_no'] 
//			) );
//			RefundDao::getMasterInstance ()->commit ();
//			return array (
//					'refund_id' => $id 
//			);
//		} catch ( \Exception $e ) {
//			RefundDao::getMasterInstance ()->rollBack ();
//			throw $e;
//		}
//	}
}