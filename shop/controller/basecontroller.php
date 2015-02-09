<?php

namespace shop\controller;

use sprite\mvc\controller;
use \stdClass;

// admin base controller
class BaseController extends Controller {
    protected $current_user = array('user_id'=>0, 'user_name'=>'guest');
    protected $has_login = false;
    public function __construct($request, $response) {
		parent::__construct ( $request, $response );
		/* 身份处理 */

        if(isset($_COOKIE['user_info']))
            $info = unserialize(base64_decode($_COOKIE['user_info']));

		if ($this->_controller != 'login' && !$info) {
			header('Location: index.php?_c=login');
            exit();
		}

        if($info['user_id'] && $info['user_name']) {
            $this->has_login = true;
            $this->current_user = $info;

            setcookie('user_info', $_COOKIE['user_info'], time () + 15 * 60 );
        }

		header ( "Content-type: text/html; charset=utf-8" );
	}
	public function checkLogin($url = '') {
		if (! $this->has_login) {
			$goto = TOUCH_BUCK . '/index.php?_c=login';
			if ($url)
				$goto .= '&refer=' . urlencode ( $url );
			$this->redirect ( $goto );
		}
		else
			 return $this->current_user['user_id']; 
	}

	//check if user is admin
	public function checkAdmin($url=''){
		if (! $this->has_login) {
			$goto = TOUCH_BUCK . '/index.php?_c=login';
			if ($url)
				$goto .= '&refer=' . urlencode ( $url );
			$this->redirect ( $goto );
		}
		else{
			$user_id = $this->current_user['user_id'];
			$info= \app\dao\AdminDao::getSlaveInstance()->find(array('user_id'=>$user_id));
			if($info)
				return $user_id ;
			else
				return 0;
		}
	}
	public function showMsg($msg,$url = ''){
		$this->showError($msg,$url);
	}
	
	
	// 错误信息的js弹窗
	public function showError($msg, $url = '') {
		echo "<script language=\"javascript\">";
		echo "alert('" . $msg . "');";
		if (! empty ( $url )) {
			echo "window.location.href='" . $url . "';";
		} else {
			echo "history.back();";
		}
		echo "</script>";
		exit ();
	}
    public function befor() {
        parent::befor();
        $t = $this->initMenu();
        $this->_response->menu_list = $t;
    }

    protected function initMenu() { }

    public function error($url, $msg) {
        echo 'error:'.$msg;
        echo $url;

    }

    public function success($url, $msg) {

        /*
        echo 'ok:' . $msg;
        echo $url;
        */
        header("Location: $url");
    }
}