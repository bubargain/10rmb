<?php

namespace admin\controller;
use app\common\util\SubPages;

class LogisticController extends BaseController {

	/*
	 * 查询商家申请的运单号
	 */
	public function index($request,$response){
		
		$response->storeTitle ="运单列表";
		$response->storeIntro ="查看您申请的运单信息";
		
		$user_id = $this->checkLogin();
		
		
		//翻页类
		$sql="select count(*) as no from ym_logistic_sn where user_id=$user_id";
        $ret= \app\dao\LogisticSnDao::getSlaveInstance()->getPdo()->getRow($sql);
        $total=$ret['no'];

        $page_size = 20;
		// 当前页数
		$curPageNum = $request->page ? intval ( $request->page ) : 1;
		// url
		$url = preg_replace ( '/([?|&]page=\d+)/', '', $_SERVER ['REQUEST_URI'] );
		// 分页对象

		$page = new SubPages( $url, $page_size, $total, $curPageNum );
		$limit = $page->GetLimit() ;
	
		
		$response->page = $page->GetPageHtml();
		
		
		
		
		$sql = "select * from ym_logistic_sn where user_id=$user_id order by ctime desc limit ".$limit;
		$info = \app\dao\LogisticSnDao::getSlaveInstance()->getPdo()->getRows($sql);
		$response->info =$info;
		if($info) $response->hasSn = 1;
		$this->layoutSmarty();
	}
	
	
	//申请物流单号
	public function applysn($request,$response){
    	$user_id= $this->checkLogin();
    	$coupon_id =$request->event_id;
    	$snCountry =$request->snCountry;
    	
    	
    	if($user_id && $coupon_id && snCountry)
    	{
    		
    		
    		$query = \app\dao\LogisticSnDao::getSlaveInstance()->find($coupon_id);
    		
    		if($query && $query['logistic_sn'] != null)//非第一提交,且运单号已分配
    		{
    			$this->showError("运单号已分配，不能重复申请","index.php?_c=order"); die();
    		}
    		else if(!$query) //第一提交
    		{
    			try{
    			\app\dao\GoodsDao::getMasterInstance()->beginTransaction();
    			$info = \app\dao\UserEventDao::getMasterInstance()->find($coupon_id);
    			\app\dao\LogisticSnDao::getMasterInstance()->add(
	    			array(
	    				'id'      => $coupon_id,
	    				'user_id' => $user_id,
	    				'country' => $snCountry,
	    				'event_id'=> $info['event_id'],
	    				'channel' => $info['store'],
	    				'ctime'   => strtotime('now'),           //运单申请创建时间
	    				'utime'   => strtotime('now')
	    		));
	    		//扣款
	    		$fee= EXPRESS_ORDER_FEE;
	    		$sql = "update ym_user_info set rmb = rmb - $fee where user_id = $user_id";
	    		 \app\dao\UserEventDao::getMasterInstance()->getpdo()->exec($sql);
	    		
	    		//扣款日志
	    		\app\dao\UserCurrencyDao::getMasterInstance()->add(
	    			array(
	    				'user_id' => $user_id,
	    				'amount'  => $fee,
	    				'unit'    => "rmb",
	    				'status'  => 8,  //运单申请
	    				'ctime' => strtotime('now')
	    			)
	    		);
	    		
	    		//
	    		\app\dao\UserEventDao::getMasterInstance()->edit($coupon_id,
	    			array('apply_logistic_sn'=>1)
	    		); //标记已申请运单
    			
    			\app\dao\GoodsDao::getMasterInstance()->commit();
    		 }
    		  catch(Exception $e)
       	 	 {
        		\app\dao\GoodsDao::getMasterInstance()->rollBack();
        		var_dump($e->message());
       		 }
    		}
    		else {
    		
	    		\app\dao\LogisticSnDao::getSlaveInstance()->edit($coupon_id,
	    			array(
	    				'country' => $snCountry,
	    				'utime'   => strtotime('now')
	    		));
    		}
    		header('Location:index.php?_c=order');
    	}
    	else {
    		$this->showError("信息输入出错","index.php?_c=order");
    	}
    	
    	
    	
    }
}