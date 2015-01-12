<?php

namespace admin\controller;

use sprite\mvc\controller;
use \stdClass;

class ApiController extends BaseController {
	
	public function index($request,$response)
	{
		$response->title='API介绍文档';
		
		$sql = 'select api_id,name,loc,comments,author from api_intro';
		$api = \app\dao\UserDao::getSlaveInstance()->getpdo()->getRows($sql);
		
		$response->apiContent = $api;
		
		
		$this->setLayout('apifault');  //设置api的默认模板页
		$this->layoutSmarty('index');
	}
	
	/**
	 * 
	 * 查询运单号
	 * @id :  返利劵ID
	 */
	public function wuliusn($request,$response)
	{
		$user_id = $this->checkLogin();
		$id = $request->id;
		if($id)
		{
			$sql = "select logistic_sn from ym_logistic_sn where id= $id and user_id= $user_id";
			$ret = \app\dao\UserActionDao::getSlaveInstance()->getPdo()->getRow($sql);
			
			if($ret['logistic_sn'] && $ret['logistic_sn'] != null )
			{
				$this->renderJson(
					array('ret'=>'true','sn'=>$ret['logistic_sn'])
				);
			}
			else 
			{
				$this->renderJson(
					array('ret'=>'true','sn'=>"订单号还未分配，请耐心等待")
				);
			}
		}
		else{
			$this->renderJson(
				array('ret'=>'false','sn'=>"N/A")
			);
		}
		
	}

}