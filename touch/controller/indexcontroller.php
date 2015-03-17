<?php

namespace touch\controller;

use \sprite\cache\CacheManager;
use \app\common\mail;

class indexcontroller extends BaseController {
	public function __construct($request, $response) {
		parent::__construct ( $request, $response );
	}
	public function mianu($request, $response) {
		
		 $user_id=$this->checkLogin ();	
		
		
		$goods = self::SelectGoods ( $user_id,10 );
		
		$response->title = 'TEN BUCK';
		$response->cdn_buck = CDN_BUCK;
		$response->currency_rate = EUROTORMB;
		//$response->focusMap_imageLink = $ret ['focuseMap_imageLink'];
		$response->live_deals = $goods;
		

		//$response->giveHer_textLink = $ret ['getTextLinks'];
		//$response->giveHer_imageLink = $ret ['giveHer_imageLink'];

        //$this->layoutSmarty ( 'index' );

		
		$action_template = $this->_controller .'/mianu.html';
		$smarty =  new \sprite\mvc\SmartyView($this->_response);
		$smarty->render(strtolower($action_template));
		
	}
	
	/***
	* 真实购买返利页
	*/
	
	public function newlaunch($request,$response){
		try{
			
			$cate = intval($request->cate);
			$hot = intval($request->hot);
			//获取数据
			if($cate>0)
			{
				$event = new \app\service\SearchSrv();
				$newEvent = $event->newEvents(1,12,$cate);			
				for($i=0;$i<count($newEvent);$i++)
				{
					
					if(preg_match('/qiniudn\.com/i', $newEvent[$i]['pic_link']))
					{	
					
						$newEvent[$i]['pic_link'] = $newEvent[$i]['pic_link']."?imageView/2/w/160";  //图片转化成160宽
						
					}
					$newEvent[$i]['fanli'] = round($newEvent[$i]['fanli'] * PROFITRATE,1);
				}
				
				
				$response->newEvent = $newEvent;	
				$response->cate = $cate;		
			}	
			else if($hot >1){  //热门活动版块
				
				
			}
				$action_template = $this->_controller .'/newlaunch.html';
				$smarty =  new \sprite\mvc\SmartyView($this->_response);
				$smarty->render(strtolower($action_template));
			
		}catch (\Exception $e)
		{
			echo $e->getMessage();
		}
		
	}
	
	public function index($request,$response){
		 $user_id=$this->checkLogin ();	
		$response->username="Daniel";
		$action_template = $this->_controller .'/index.html';
		$smarty =  new \sprite\mvc\SmartyView($this->_response);
		$smarty->render(strtolower($action_template));
	}
	
	/**
	 * 
	 * 每日活动专区
	 */
	public function event($request,$response){
		$hot=intval($request->hot);
		if($hot>0)
		{
			$sql = "select * from ym_event where hot=$hot and status =1 and noshipping =0 and applied <= amount order by utime desc";
			$res = \app\dao\SearchAliaDao::getSlaveInstance()->getPdo()->getRows($sql);
			if($res)
			{
				$response->live_deals=$res;
			}
		}
		if($hot ==1)
			$response->title= "TODAY'S SPECIAL";
		else if ($hot ==2 )
			$response->title= "New Customer Rewards";
		$action_template = $this->_controller .'/event.html';
		$smarty =  new \sprite\mvc\SmartyView($this->_response);
		$smarty->render(strtolower($action_template));
		
	}
	
	
	
	
	public function about($request, $response) {
		$response->title = 'CONTACT';
		$this->layoutSmarty ( 'about' );
	}
	
	// 页头的广告图片
	public function getImageLinks($ukey, $len = 2) {
		$info = \app\dao\CmsLocationDao::getSlaveInstance ()->findByField ( 'ukey', $ukey );
		if (! $info [0] ['status']) {
			return array ();
		}
		$list = \app\dao\CmsImagelinkDao::getSlaveInstance ()->getAllBySort ( array (
				'loc_id=' . $info [0] ['loc_id'],
				'status=1' 
		), $len );
		if (! $list) {
			return $list;
		}
		foreach ( $list as $key => $val ) {
			$extra = json_decode ( $val ['extra'], true );
			if ($extra ['goods_id']) { // 如果是商品而非上传的图片
				$goods_ids [] = $extra ['goods_id'];
				$list [$key] ['goods_id'] = $extra ['goods_id'];
				$list [$key] ['brand_name'] = $extra ['brand_name'];
				$list [$key] ['cate_name'] = $extra ['cate_name'];
			}
		}
		if ($goods_ids) {
			$list = self::formatGoods ( $list, $goods_ids );
		}
		return $list;
	}
	public function getTextLinks($ukey) {
		$info = \app\dao\CmsLocationDao::getSlaveInstance ()->findByField ( 'ukey', $ukey );
		if (! $info [0] ['status']) {
			return array ();
		}
		$ret = \app\dao\CmsTextlinkDao::getSlaveInstance ()->getAllBySort ( array (
				'loc_id=' . $info [0] ['loc_id'],
				'status=1' 
		) );
		foreach ( $ret as $val ) {
			if ($val ['sort'] == 1) {
				$list ['left'] = $val;
			}
			if ($val ['sort'] == 2) {
				$list ['right'] = $val;
			}
		}
		return $list;
	}
	private function formatGoods($list, $goods_ids) {
		if (! $goods_ids)
			return $list;
		
		$goods = \app\dao\GoodsDao::getSlaveInstance ()->getInfoByGoodsIds ( $goods_ids );
		$counts = \app\dao\GoodsStatisticsDao::getSlaveInstance ()->getStatisticsByGoodsIds ( $goods_ids );
		$likes = array ();
		if ($this->has_login) {
			$_tmp = \app\dao\LoveDao::getSlaveInstance ()->getMyListByGoodsIds ( $goods_ids, $this->current_user ['user_id'] );
			if ($_tmp) {
				foreach ( $_tmp as $r ) {
					$likes [$r ['goods_id']] = true;
				}
			}
		}
		// 获取角标信息
		$type_arr = \app\service\GoodsSrv::getSaleType ();
		//
		foreach ( $list as $k => $row ) {
			$list [$k] ['liked'] = isset ( $likes [$row ['goods_id']] ) ? true : false;
			$list [$k] ['wishes'] = isset ( $counts [$row ['goods_id']] ) ? $counts [$row ['goods_id']] ['wishes'] : 0;
			$list [$k] ['tags'] = '';
			$list [$k] ['sale_type_info'] = $goods [$row ['goods_id']] ['sale_type'] ? $type_arr [$goods [$row ['goods_id']] ['sale_type']] : array ();
			if (isset ( $goods [$row ['goods_id']] )) {
				$tmp = explode ( ' ', $goods [$row ['goods_id']] ['tags'] );
				$list [$k] ['tags'] = $tmp [0];
			}
			if (! $row ['url'])
				$list [$k] ['url'] = 'index.php?_c=goods&_a=detail&id=' . $row ['goods_id'];
		}
		return $list;
	}
	
	//展示商品
	public function SelectGoods($user_id,$len=10) {
		
		
		$searchSrv = new \app\service\SearchSrv ();
		$ret = $searchSrv->search ( array ('user_id'=>$user_id), 10 );
	
		/*// 获取角标信息
		$type_arr = \app\service\GoodsSrv::getSaleType ();
		//如果用户登录，则优先展示没like过的商品
		if ($this->has_login && $ret ['list']) {
			foreach ( $ret ['list'] as $k => $r ) {
				$ret ['list'] [$k] ['url'] = 'index.php?_c=goods&_a=detail&id=' . $r ['goods_id'];
				$ret ['list'] [$k] ['liked'] = false;
				// $ret ['list'] [$k] ['sale_type_info'] = $ret ['list'] [$row
				// ['goods_id']] ['sale_type'] ? $type_arr [$goods [$row
				// ['goods_id']] ['sale_type']] : array ();
				$ids [] = $r ['goods_id'];
			}
			
			$_tmp = \app\dao\LoveDao::getSlaveInstance ()->getMyListByGoodsIds ( $ids, $this->current_user ['user_id'] );
			if ($_tmp) {
				foreach ( $_tmp as $r ) {
					$_t [$r ['goods_id']] = true;
				}
				foreach ( $ret ['list'] as $k => $v ) {
					$ret ['list'] [$k] ['liked'] = isset ( $_t [$v ['goods_id']] ) ? true : false;
				}
			}
		}
		return $ret ['list']; */
		return $ret;
		
	}
	
	public function notice($request,$response){
		$response->notice = $request->txt ?$request->txt :"10BUCK took a break!";
		$response->url = urldecode($request->url);
		$this->layoutSmarty('notice');
	}
	
	
	/**
	 * 
	 * send email notification to user
	 * @param unknown_type $request
	 * @param unknown_type $response
	 */
	public function mail($request,$response)
	{
		$user_id = $this->checkLogin();
		if($request->type=="bcode")
		{
			$id = $request->id;
			$info= \app\dao\UserEventDao::getSlaveInstance()->find($id);
			$to = $this->current_user['user_name'];
			$title="10BUCK Payment Notification";
			$headers="contact@10buck.com";
			$mailtmp=\app\dao\SettingDao::getSlaveInstance()->find(array('ukey'=>'mail_tmp'));
			$txt = $mailtmp['uvalue'];
			try{
				require_once( ROOT_PATH. '/app/common/mail/class.phpmailer.php');
				$mail= new \PHPMailer;
				$mail->isSMTP();                                      // Set mailer to use SMTP
				$mail->Host = BUCK_MAIL_SMTP;  // Specify main and backup SMTP servers
				$mail->SMTPAuth = true;                               // Enable SMTP authentication
				$mail->Username = BUCK_MAIL_HOST;                 // SMTP username
				$mail->Password = BUCK_MAIL_PASS;                           // SMTP password
				$mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
				$mail->Port = 465;                                    // TCP port to connect to
				
				$mail->From = BUCK_MAIL_HOST;
				$mail->FromName = '10buck';
				//$mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
				$mail->addAddress($to);               // Name is optional
				//$mail->addReplyTo('info@example.com', 'Information');
				//$mail->addCC('cc@example.com');
				//$mail->addBCC('bcc@example.com'); 
				
				//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
				//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
				$mail->isHTML(true);                                  // Set email format to HTML
				
				$mail->Subject = $title;
				$txt= sprintf($txt,$to,$info['bcode'],$info['product_link'],$info['product_link']);;
				
				$mail->Body    = sprintf($txt,$info['bcode'],$info['product_link']);
				//$mail->Body    = $txt;
				$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
				
				if(!$mail->send()) {
				    $this->showError( 'Message could not be sent.');
				    
				} else {
				   $this->showMsg( 'Message has been sent','index.php');
			}
			}catch(\Exception $e)
			{
				var_dump($e->getMessage());
				$this->showError($e->getMessage());
			}
			
		}
	}
}