<?php

namespace touch\controller;

use app\dao\RegionDao;
use app\dao\AddressDao;

class orderController extends BaseController {
	public function __construct($request, $response) {
		parent::__construct ( $request, $response );
	}
	public function cash($request, $response) {
		$user_id= $this->checkLogin();
		if(!$this->isPost())
		{
			$response->user_name = $this->current_user['user_name'];
			$info = \app\dao\UserInfoDao::getSlaveInstance()->find($user_id);	
			$response->amount = $info['usd'];
			$this->layoutSmarty ( 'cash' );
		}
		else {
			
			 
			try{
					$refund = new \app\service\RefundSrv();
					$refund->apply($user_id,$request->applyamount,'usd');
					$this->showMsg("Success","index.php?_c=order");
			 }catch(\Exception $e)
			 {
			 	$this->showMsg($e->getMessage(),"index.php?_c=order");
			 }
		}
		
	}
	
	// 用户中心
	public function index($request, $response) {
		//$this->checkLogin ();
		$response->title = 'MY ACCOUNT';
		$response->user_name = $this->current_user ['user_name'];
		$user = \app\dao\UserInfoDao::getSlaveInstance()->find($this->current_user['user_id']);
		
		$response->saving = $user['usd'];
	
		$this->layoutSmarty ( 'index' );
	}
	
	//解锁订单
	public function unlock($request,$response)
	{
		$user_id=$this->checkLogin();
		try{
			$id =$request->id;
			if(!$id)
				$this->showError ("id is null" );
			else{
				$info= \app\dao\UserEventDao::getSlaveInstance()->find(
					array(
					'user_id' => $user_id,
					'id' => $id
					)
				);
				if($info) //校验通过
				{
					\app\dao\UserEventDao::getMasterInstance()->edit( $id,
						array('status' => 0)
					);
					$this->showMsg("Your Bcode has been unlocked","index.php?_c=order&_a=orderList&status=unpay");
				}
			}
			
		}catch(\Exception $e)
		{
			$this->showError ( $e->getMessage() );
		}
	}
	
	
	// 订单确认
	public function confirm($request, $response) {
		$user_id=$this->checkLogin ( 'index.php?_c=order&_a=confirm&id=' . $request->id . '&reBack=1' );
		try {
			
			$id =$request->id;
			$user_id= $user_id;
			
			$usersrv= new \app\service\EventSrv();
			$ret = $usersrv->confirmCodeUse($user_id,$id);
			
			if ($ret) {
				$this->showError("SUCCEED!", "index.php?_c=order&_a=orderList&status=payed");
			} else {
				$this->showError('change status failed',"index.php?_c=order&_a=orderList&status=unpay");
			}
		
		} catch ( \Exception $e ) {
			//$message = ($e->getCode () == 50002) ? '太火爆，卖完了！' : $e->getMessage ();
			$this->showError ( $e->getMessage() );
		}
	}
	
	/**
	 * 
	 * input order sn for puchased order
	 * @param unknown_type $request
	 * @param unknown_type $response
	 */
	public function ordersn($request,$response)
	{
		$id = $request->id;
		$user_id = $this->checkLogin();
		$order_sn = $request->order_sn;
		if($this->isPost())
		{
			
			if($id && $order_sn)
			{	
				$where = " id=$id and user_id=$user_id";
				 \app\dao\UserEventDao::getMasterInstance()->editByWhere(
				array('order_sn'=>$order_sn),$where
				
				);
				$this->redirect('index.php?_c=order&_a=orderList&status=payed');
			}
			else {
				$this->showError('submit failed');
			}
		}
		else{
			$response->id = $id; 
			$this->layoutSmarty('payed_detail');
		}
	}
	
	
	
	// 订单提交
	public function submit($request, $response) {
		$this->checkLogin ();
		try {
			$post ['goods_id'] = $request->id;
			$post ['quantity'] = $request->quantity;
			$post ['user_id'] = $this->current_user ['user_id'];
			$post ['type'] = 'touch';
			
			if (! $post ['goods_id'])
				throw new \Exception ( '请传商品id', 50000 );
			
			$post ['address'] ['user_id'] = $post ['user_id'];
			$post ['addr_id'] = $request->post ( 'addr_id', 0 );
			$post ['address'] ['consignee'] = $request->consignee;
			$post ['address'] ['region_id'] = $request->region_id;
			$post ['address'] ['region_name'] = $request->region_name;
			$post ['address'] ['address'] = $request->address;
			$post ['address'] ['phone_mob'] = $request->phone_mob;
			
			$post ['ucpn_id'] = $request->post ( 'ucpn_id', 0 );
			
			self::checkAddress ( $post ['address'] );
			
			$orderSrv = new \app\service\OrderSrv ();
			$order = $orderSrv->submit ( $post );
			
			// 订单生成后支付
			header ( "Location:index.php?_c=payment&_a=payForm&type=alipay&id=" . $order ['order_id'] );
		} catch ( \Exception $e ) {
			$this->showError ( $e->getMessage () );
		}
	}
	// 取消订单
	public function cancel($request, $response) {
		$this->checkLogin ();
		try {
			$ret = \app\service\OrderSrv::cancel ( $request->order_id, $this->current_user ['user_id'], '买家取消订单' );
			$this->renderJson ( array (
					'ret' => array (
							'status' => 200,
							'data' => '订单取消成功' 
					) 
			) );
		} catch ( \Exception $e ) {
			$this->renderJson ( array (
					'ret' => array (
							'status' => 10000,
							'data' => '取消订单操作内部错误' 
					) 
			) );
		}
	}
	// 订单列表
	public function orderList($request, $response) {
		$user_id=$this->checkLogin ();
		$status = $request->status ? $request->status : 'unpay';
		$ret = self::getPageByStatus ( $status );
		$data = \app\service\EventSrv::orders ( $user_id, $ret ['status'],100 );
		
		if($data)
		{
			$response->params =$data;
		}
		$this->layoutSmarty ( $ret ['html'] );
	}
	
	// 订单详情
	public function orderDetail($request, $response) {
		$this->checkLogin ();
		$response->refer = $this->getBackUrl ( 'order_detail', '_c=order&_a=orderDetail', $request->reBack );
		$order_id = $request->order_id;
		if (! $order_id) {
			$this->showError ( '订单id有误' );
		}
		$info = \app\service\OrderSrv::info ( $order_id );
		if (! $info) {
			$this->showError ( '获取订单详情失败' );
		}
		$response->info = $info;
		//var_dump($info);
		$ret = self::getPageByStatus ( $request->status, '_detail' );
		$this->layoutSmarty ( $ret ['html'] );
	}
	private function checkAddress($info) {
		if (! $info ['consignee'] || ! $info ['phone_mob'] || ! $info ['address'] || ! $info ['region_name'])
			throw new \Exception ( '收货信息不完整，请完善', 5000 );
		
		if (! preg_match ( '/1[0-9]{10}/', $info ['phone_mob'] ))
			throw new \Exception ( '手机号码不正确', 5000 );
	}
	// 根据不同的订单状态，加载不同的订单列表页面
	private function getPageByStatus($status, $type = '') {
		switch ($status) {
			// 待付款订单
			case 'unpay' :
				$status = 0;
				$html = 'unpay';
				break;
			// 待发货订单
			case 'payed' :
				$status = 1;
				$html = 'payed';
				break;
			// 已发货订单
			case 'shipped' :
				$status = 4;
				$html = 'shipped';
				break;
			// 已完成
			case 'finished' :
				$status = 4;
				$html = 'finished';
				break;
			// 已取消订单
			case 'closed' :
				$status = 99;
				$html = 'closed';
				break;
			default :
				$this->showError ( 'order URL not correct' );
		}
		if ($type) {
			$html .= $type;
		}
		return array (
				'status' => $status,
				'html' => $html 
		);
	}
}