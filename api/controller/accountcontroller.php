<?php
namespace api\controller;

use sprite\mvc\controller;


class accountController extends Controller{
	
	
	/**
	 * signup
	 * 测试接口： 用户注册
	 * @param unknown_type $request
	 * @param unknown_type $response
	 */
	public function signup ($request,$response)
	{
		if($this->isPost())
		{
		$nickname = $request->nickname;
		$email = $request-> email;
		$password = $request->password;
		$gender = $request->gender== 'f'?'f':'m'; //default to be 'female'
		
			if($nickname && $email && $password)
			{
				$this->renderJson( array(
						'user_id' => 12,
						'token' => '12ddsfo3323wewedfdseefe', //32位
						'utime' => '1433167313',
								
				));
			}
			else{
				$this->renderJson(array(
					'error'=>"Params not given"				
				));
			}
		}else{
			$this->renderJson(array(
				'error'=>"This is a post interface, you can't use get method to deliver data"			
			));
		}
		
	}
}