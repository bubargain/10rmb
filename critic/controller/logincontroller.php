<?php
namespace critic\controller;

use \app\service\member;
use \app\service\UserSrv;
use \app\common\verify\ValidateCode;

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
			//验证码
		
			if(! $this->codeVerify($request->verifycode))
			{
				$this->showError('Verify code is not right,please try again','index.php?_c=login');
			}
			// 登陆处理
			try {
				$info = \app\dao\UserDao::getSlaveInstance ()->find ( array (
						'user_name' => $request->user_name 
				) );
				
				if (! $info || md5 ( $request->pwd ) != $info ['password']) {
					throw new \Exception ( 'USERNAME AND PASSWORD NOT MATCH', 4001 );
					//$this->showError ( '账户或密码错误' );
				}
				
				if(! self::checkLevel ( $info['user_id'] ))
				{
					throw new \Exception ( 'Your account dont have right to login', 4001 );
					//$this->showError ( '账户还没通过审核，请耐心等待' );
				}
		
				
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
	
   /**
      * 生成图片验证码
      */
    public function genVerifyCode($request,$response){
    	//获得验证码	
			session_start();
			$_vc = new ValidateCode();  //实例化一个对象
			$_vc->doimg();  			
			$_SESSION["validate"] = $_vc->getCode();//验证码保存到SESSION中
			
     }
	    
    /**
     *  验证码验证
     */
    private function codeVerify($validate){
    	session_start();
    	
		if($validate==$_SESSION["validate"]){		    
			//判断session值与用户输入的验证码是否一致;
			return true;
		}else{
			 return false;
		}
    	
    }
    
   
	
	
	private function checkPhoneNum($username)
	{
		//弱验证，是否为纯数字
        if(!preg_match('/^1[0-9]{10}$/', $username))
			return false;
		return true;
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
	* 0 unauthoried , 1 normal ,100 admin,2 critic
	*/
	private function checkLevel($user_id) {
		$user =\app\dao\AdminDao::getSlaveInstance () -> find($user_id);
		if ($user['level'] == 2)
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
