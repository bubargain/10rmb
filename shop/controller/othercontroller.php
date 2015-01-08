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
}