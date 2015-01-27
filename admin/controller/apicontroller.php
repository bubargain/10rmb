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
	
	/*
	 * upload file
	 */
	public function uploadfile ($request,$response){
		define('ROOT', __DIR__);
		define('DS', DIRECTORY_SEPARATOR);
		
		
		include ROOT_PATH. "/lib/Qiniu/Autoload.php";
		
		\Autoload::addNamespace('Qiniu', dirname( __DIR__) .DS. '../lib/Qiniu');
		\Autoload::register();
		
		$accessKey = 'tksud8HCmLgmR2QrNzFyOMYR5RTsXSAx1cGA2CY1';
		$secretKey = 'INtHj8iUAX7VPxMBABK_byxsJnV-0mBrmhvCWd6K';
		
		$qiniu = new \Qiniu\Qiniu($accessKey, $secretKey);
		
		$bucket = $qiniu->getBucket('askkite');
		
		$bucket->setPolicy(array(
		    'returnBody' => '{
		        "key": $(key),
		        "name": $(fname)
		    }',
		    'expires' => 3600
		));
		
		if (!empty($_FILES)) {
		    // 上传文件函数
		    $file= '10buck'.strtotime('now');
		 
		    list($return, $error) = $bucket->put($_FILES['file1']['tmp_name'], $file.'.jpg', \Qiniu\Bucket::EXTR_OVERWRITE);
		    echo is_null($error) ? json_encode($return) : json_encode($error);
		}
				
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
