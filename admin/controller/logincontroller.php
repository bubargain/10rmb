<?php

namespace admin\controller;

use \app\service\member;
use app\service\UserSrv;

class LoginController extends BaseController {
	public function index($request, $response) {
		$refer = $this->referer ( $request );
		if ($this->has_login) {
			header ( "Location: $refer" );
		}
		if (! $this->isPost ()) {
			// 显示登陆页
			$this->renderSmarty ();
		} else {
			// 登陆处理
			try {
				$info = \app\dao\UserDao::getSlaveInstance ()->find ( array (
						'user_name' => $request->user_name 
				) );
				if (! $info || md5 ( $request->pwd ) != $info ['password']) {
					throw new \Exception ( '账户或密码错误', 4001 );
					//$this->showError ( '账户或密码错误' );
				}
				
				if(! self::checkLevel ( $info['user_id'] ))
				{
					throw new \Exception ( '账户还没通过审核，请耐心等待', 4001 );
					//$this->showError ( '账户还没通过审核，请耐心等待' );
				}
				/*
				if (!self::isAdmin ( $info ['user_id'] )) {
					//throw new \Exception ( '账户不是管理员', 4002 );
					$this->showError ( '账户不是管理员' );
				}
				*/
				
				// 写cookie
				$user_info = array (
						'user_id' => $info ['user_id'],
						'user_name' => $info ['user_name'] 
				);
				
				$cookie_user_info = base64_encode ( serialize ( $user_info ) );
				setcookie ( 'admin_info', $cookie_user_info, time () + 15 * 60 );
				header ( "Location: $refer" );
				exit ();
			} catch ( \Exception $e ) {
				$response->warn = $e->getMessage ();
				$this->renderSmarty ('error');
			}
		}
	}
	
	//用户注册
	public function reg($request, $response) 
	{
		//提交注册信息
		if($this->isPost())
		{
			
			$user['user_name']=$request->inputPhone;
			$user['email']=$request->inputEmail;
			$user['password']=md5($request->inputPassword);
			$user['nick_name']=$request->inputName;
			
			$user['invite_code']=$request->inputInvite;
			
			try
			{
				if($user['invite_code'] == Null or $user['invite_code'] == '') //无邀请码
				{
					throw new \Exception ( '目前我们只支持邀请注册，详情请联系客服 ', 4002 );
				}
				else{
				
					$invite = \app\dao\UserDao::getSlaveInstance ()->find ( array (
							'invite_code' => $user['invite_code']
					) );
					
					if(!$invite){
						throw new \Exception ( '您好！目前我们只支持邀请注册，详情请联系客服 ', 4002 );
					}
					
					
					$info = \app\dao\UserDao::getSlaveInstance ()->find ( array (
							'user_name' => $user['user_name'] 
					) );
					
					if ($info ) {
						throw new \Exception ( '手机号已经存在，不能重复注册', 4001 );
					}
					
					
					
					if (! $user['password'])
					{
						throw new \Exception ( '密码不能为空', 4001 );
					}
					
					$userBehavior =new UserSrv();
	                $user['invite_by'] = $invite['user_id'];
	                $nuser = $userBehavior->addUser($user);
					
					throw new \Exception ( '您已成功提交注册信息，我们会尽快审核您的信息，请注意查收邮箱', 4001 );
				}
			} catch ( \Exception $e ) {
				$response->warn = $e->getMessage ();
				$this->renderSmarty ('error');
				
			}
			
		}
		else
		{
			$response->invite_code = $request->invite;
			$this->renderSmarty ();
		}
	}
	
	
	
	public function logout() {
		setcookie ( 'admin_info', '', time () - 3600 );
		header ( 'Location: index.php?_c=login' );
	}
	private function isAdmin($user_id) {
		return \app\dao\AdminDao::getSlaveInstance ()->find ( $user_id ) ? true : false;
	}
	
	/*
	*check user level
	* 0 unauthoried , 1 normal ,100 admin
	*/
	private function checkLevel($user_id) {
		$user =\app\dao\UserAdminDao::getSlaveInstance () -> find($user_id);
		if ($user['level'] == 1)
			return true;
		else 
			return false;
	}
	private function referer($request) {
		$refer = 'index.php';
		if (isset ( $request->refer )) {
			$refer = $request->refer;
		}
		
		$search = array (
				'_c=login' 
		);
		$refer = str_replace ( $search, '', $refer );
		
		return $refer;
	}
}