<?php

namespace shop\controller;

class OtherController extends BaseController {
	
	public function index($request,$response){
		if($this->isPost())
		{
			$user_id = $request->suser_id;
			$info= \app\dao\UserInfoDao::getSlaveInstance()->find($user_id);
			$response->info =$info;
		}
		$this->layoutSmarty();
	}
	
	
	//商家加积分
	public function addpoint($request,$response)
	{
		if($this->checkLogin())
		{
			$user_id = $request->user_id;
			$point  = $request->points;
			if($user_id && $point)
			{
				$sql = "update ym_user_info set point=point+$point where user_id = $user_id";
				$ret = \app\dao\UserInfoDao::getMasterInstance()->getPdo()->exec($sql);
				$this->showError("添加已经成功","index.php?_c=other");
			}
			else{
				$this->showError("输入不正确");
			}
			
		}
	}
	
	//set merchant to be vip
	public function setvip($request,$response)
	{
		$user_id = $this->checkAdmin();
		if($user_id){
		$client_id = $request->v_user_id;
		$isvip = intval($request->v_isvip);
		\app\dao\UserInfoDao::getMasterInstance()->edit($client_id,array('isvip'=>$isvip));
		$this->showError("修改成功","index.php?_c=other");
		}
		else{
			$this->showError("你没有管理员权限");
		}
	}
	
	/*
	 * adjust money between merchant and user
	 */
	public function adjustamount($request,$response)
	{
		$user_id = $this->checkAdmin();
		if($user_id)  //is admin
		{
			$buyer_id =$request->add_user_id;
			$buyer_unit= $request->add_curr;
			
			$seller_id = $request->red_user_id;
			$seller_unit = $request->red_curr;
			
			$amountinusd = floatval($request->add_amount);
			$fexchangeRate = \app\dao\SettingDao::getSlaveInstance()->find(array('ukey'=>'exchange_rate'));
			$rate = floatval($fexchangeRate['uvalue']);
			
			$amountinrmb = round($amountinusd * $rate,2);
			//var_dump($amountinrmb);die();
			$sqlins =  \app\dao\SettingDao::getSlaveInstance();
			//try{
				$time = strtotime('now');
			//	$sqlins->beginTransaction();
				//buyer adjust
				if($buyer_unit == 1 ) //usd
				{
					$sql1 = "update ym_user_info set usd = usd + $amountinusd where user_id = $buyer_id";
					$sql2 = "insert into ym_user_currency(user_id,amount,unit,ctime,status,comment) values ($buyer_id,$amountinusd,'usd',$time,100,'管理员调整')";
				}
				else if($buyer_unit == 0 ) //rmb
				{
					$sql1 = "update ym_user_info set rmb = rmb + $amountinrmb where user_id = $buyer_id";
					$sql2 = "insert into ym_user_currency(user_id,amount,unit,ctime,status,comment) values ($buyer_id,$amountinrmb,'rmb',$time,100,'sys auto adjust')";
				}
				$sqlins->exec($sql1); 
				$sqlins->exec($sql2);
				
				
				//seller adjust
				if($seller_unit == 1 ) //usd
				{
					$sql3 = "update ym_user_info set usd = usd - $amountinusd where user_id = $seller_id";
					$sql4 = "insert into ym_user_currency(user_id,amount,unit,ctime,status,comment) values ($seller_id,$amountinusd*(-1),'usd',$time,100,'管理员调整')";
				}
				else if($seller_unit == 0 ) //rmb
				{
					$sql3 = "update ym_user_info set rmb = rmb - $amountinrmb where user_id = $seller_id";
					$sql4 = "insert into ym_user_currency(user_id,amount,unit,ctime,status,comment) values ($seller_id,$amountinrmb*(-1),'rmb',$time,100,'sys auto adjust')";
				}
				$sqlins->exec($sql3); 
				$sqlins->exec($sql4);
				
				$this->showMsg("调整完毕，可在资金流水中核对！", 'index.php?_c=other');
		//		$sqlins->commit();	
//			}catch (\Exception $e)
//			{
//				$sqlins ->rollBack();
//				echo $e->getMessage();
//			}
			
		}
	}
	
	
}