<?php
namespace shop\controller;

class LogisticController extends BaseController {
	
	public function index($request, $response)
	{
	
		$sql = "select * from ym_logistic_sn where logistic_sn is NULL";
		$info = \app\dao\LogisticSnDao::getSlaveInstance()->getPdo()->getRows($sql);
		if($info)
			$response->info = $info;
		$this->layoutSmarty();
	}
	
	public function updatesn($request,$response){

			$this->checkLogin();//必须登录用户
			
			$id= $request->id;
			$sn =$request->sn;
			//$query = \app\dao\LogisticSnDao::getMasterInstance()->find($id);
			
			/**
			 * 第一次给出时，需要扣运单费
			 * 目前暂不执行
			 * @author：daniel 
			 * @time : 2015-01-07
			 */
			
			\app\dao\LogisticSnDao::getMasterInstance()->edit($id,
			array('logistic_sn'=>$sn,'utime'=>strtotime('now'))
			);
			$this->renderjson(
				array('ret'=>'true')
			);
	
		
	}
}