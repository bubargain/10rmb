<?php

namespace touch\controller;

class HelpController extends BaseController {
	// 查看使用条款和隐私政策
	public function agreement($request, $response)  {
		try {
			$result = \app\dao\SettingDao::getSlaveInstance ()->find ( 'agreement' );
			$response->title = '使用条款和隐私政策';
			$response->agreement = nl2br($result ['uvalue']);
			$this->layoutSmarty ( 'agreement' );
		} catch ( \Exception $e ) {
			$this->showError ( $e->getMessage () );
		}
	}
	
	public function forgetpass($request,$response){
			$email = $request->em;
		
			
			if($this->checkEmail($email))
			{
				$info = \app\dao\UserInfoDao::getSlaveInstance()->find(
					array(
					'user_name'=>$email
				
					));
				if($info)
				{
					\app\dao\UserInfoDao::getMasterInstance()->edit($info['user_id'],array('utime'=>strtotime('now')));
					$mail = new \app\service\MailSrv();
					$mail->sendMail($email, "[10BUCK] Password Reset", "Dear 10buckr,<br/> Please click Link below to reset your password：<br/><a href='http://shop.10buck.com/index.php?_c=login&_a=repass2&phone=$email&token=".$info['token']."'>Password Reset Link</a><br/>Thanks<br/><br/>10BUCK Support Team");
					$this->showError("Reset email has sent, please check your mail box.", 'index.php');
				}
				else{
					$this->showError("Sorry,Email not found", 'index.php');
				}
			}
			else{
				$this->showError("Email not found", 'index.php');
			}
	}
	//check email input
	private function checkEmail($email)
	{
	    $pregEmail = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[0-9a-z]+(\\.[a-z]{2})?)$/i";
	    return preg_match($pregEmail,$email); 
	}
	
}