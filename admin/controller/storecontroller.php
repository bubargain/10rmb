<?php

namespace admin\controller;

use sprite\mvc\controller;
use app\common\util\SubPages;
use \stdClass;
use \app\dao\PaymentDao;

class StoreController extends BaseController {
	private $alipay_config = Array ();
	
	public function config()
	{
		$this->alipay_config ['partner'] = ALIPAY_ID;
		
		// 安全检验码，以数字和字母组成的32位字符
		// 如果签名方式设置为“MD5”时，请设置该参数
		$this->alipay_config ['key'] = 'yqq6zyvp9wi0s1xn3socm3g93ewlnw25';
		
		// 商户的私钥（后缀是.pen）文件相对路径
		// 如果签名方式设置为“0001”时，请设置该参数
		//$this->alipay_config ['private_key_path'] = ROOT_PATH . '/app/service/payment/alipay/ali-key/rsa_private_key.pem';
		
		// 支付宝公钥（后缀是.pen）文件相对路径
		// 如果签名方式设置为“0001”时，请设置该参数
		//$this->alipay_config ['ali_public_key_path'] = ROOT_PATH . '/app/service/payment/alipay/ali-key/alipay_public_key.pem';
		
		// ↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
		
		// 签名方式 不需修改
		$this->alipay_config ['sign_type'] = "MD5";
		
		// $alipay_config['sign_type'] ="RSA";
		
		// 字符编码格式 目前支持 gbk 或 utf-8
		$this->alipay_config ['input_charset'] = 'utf-8';
		
		// ca证书路径地址，用于curl中ssl校验
		// 请保证cacert.pem文件在当前文件夹目录中
		$this->alipay_config ['cacert'] = ROOT_PATH . '/app/service/payment/alipay/ali-key/cacert.pem';
		
		// 访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
		$this->alipay_config ['transport'] = 'http';
	}
	
	
	// 店铺列表
	public function index($request, $response) {
		
		$user_id =$this->current_user['user_id'];
		
		//获取邀请码
		$userinfo = \app\dao\UserDao::getSlaveInstance ()->find ( array (
						'user_id' => $user_id
				) );
		
		$response->invite_code=$userinfo['invite_code'];
		
		$response->title = '10buck全球新品发布汇 -商家后台';
		$response->list = \app\dao\StoreDao::getSlaveInstance ()->getList ();
		$response->storeTitle ="全球新品发布汇";
		$response->storeIntro ="10Buck支持商家向海外目标消费者发放定量的现金返还劵。<br/>一旦用户使用该劵并成功交易，就会获得现金返还。";
		$this->layoutSmarty ( 'index' );
	}
	
	public function newevent($request,$response)
	{
		$user_id =$this->checkLogin();
		$info = \app\dao\UserInfoDao::getSlaveInstance()->find(array('user_id'=>$user_id));
		$response->isvip = $info['isvip']>0?1:0;
		$response->storeTitle ="活动说明";
		$response->storeIntro ="10BUCK平台可以快速的让新品获得曝光和排名提升，帮助商家甄别爆款";
		$this->layoutSmarty('newevent');
	}
	
	//锁定活动金额
	public function lock($request,$response)
	{
		if($this->isPost())
		{
			try{
				
				
					$event['event_name'] = $request->event_name;
					$event['product_link'] = $request->product_link;
					$event['price'] = floatval($request-> sale_price);
					$event['amount'] = $request->amount;
					$event['fanli_percentage'] = floatval($request->fanli);
					$event['fanli'] = round($event['price'] * $event['fanli_percentage'] /100,1);
					$event['noshipping'] = $request->noshipping;  //是否支持免邮
					$event['cate'] = $request->cate;
					$event['duringtime']=$request->duringtime;
					$event['pic_link']=$request->pic_link;
					
					//var_dump($event);die();
					$response->event_name = $event['event_name'];
					$response->product_link =$event['product_link'];
					$response->price   =$event['price'];
					$response->amount  =$event['amount'];
					$response->fanli   =$event['fanli'];
					$response->fanli_percentage   =$event['fanli_percentage'];
					$response->cate =$event['cate'];
					$response->duringtime   =$event['duringtime'];
					$response->pic_link = $event['pic_link'];
				
					if($event['noshipping']==0)
					{
						/***
						*  试行真实返利商家不需要预充值
						* 2015-03-27 
						* daniel ma
						*/
						 //$lockamount =  floatval($event['fanli'])* floatval($event['amount']);
						$lockamount = 0;
					}
					else {
						$lockamount = (floatval($event['price']) + floatval($event['fanli']))* floatval($event['amount']);
					}
					
				
					//查询用户账户余额
					$userinfo = \app\dao\UserInfoDao::getSlaveInstance ()->find ( array (
						'user_id' => $this->current_user['user_id']
					) );
					
					if(!$userinfo)
					{
						$response->user_amount = 0;
					}
					else{
						$response->user_amount = $userinfo['rmb'];
					}
					//查询汇率
					$exchange_rate= \app\dao\SettingDao::getSlaveInstance ()->find ( array (
						'ukey' => 'exchange_rate'
					) );
					
					$response->user_amount_usd=floatval($userinfo['rmb'])/floatval($exchange_rate['uvalue']);
				
					
		
			}catch (\Exception $e)
			{
				$this->showError($e->getMessage());
			}
			$response->lock_amount = $lockamount ? $lockamount : 0;
			$response->noshipping=$event['noshipping'];
			$response->storeTitle ="资金锁定说明";
			$response->storeIntro ="您的账户余额需高于本次活动可能需要的总返利金额<br/>注：如果支持免邮，则需要金额足以支付本利总和";
			
			$this->layoutSmarty('lock');
		}
		else 
		{
			$this->showError("本页不允许直接访问哦");
		}
	}
	
	
	
	public function asset($request,$response)
	{
		$user_id= $this->current_user['user_id'];
		if($user_id)
		{
			$response->storeTitle ="资金管理";
			$response->storeIntro ="查看您的账户余额和活动冻结金额";
			$user=\app\dao\UserInfoDao::getSlaveInstance()->find(array(
				'user_id' => $user_id
			));
			$response->money = $user['rmb'];
			
			//翻页类
		$sql="select count(*) as no from ym_user_currency where user_id=$user_id ";
        $ret= \app\dao\EventDao::getSlaveInstance()->getPdo()->getRow($sql);
        $total=$ret['no'];

        $page_size = 15;
		// 当前页数
		$curPageNum = $request->page ? intval ( $request->page ) : 1;
		// url
		$url = preg_replace ( '/([?|&]page=\d+)/', '', $_SERVER ['REQUEST_URI'] );
		// 分页对象

		$page = new SubPages( $url, $page_size, $total, $curPageNum );
		$limit = $page->GetLimit() ;
		$response->page = $page->GetPageHtml();
		
	
			$sql= "select * from ym_user_currency where user_id = $user_id  order by id desc limit ".$limit;
			$res = \app\dao\UserCurrencyDao::getSlaveInstance()->getPdo()->getRows($sql);
			$response->currencyFlow = $res;
			
		
		}
		$this->layoutSmarty('asset');
	}
	
	//提现申请
	public function releasemoney($request,$response)
	{
		$user= $this->current_user['user_id'];
		if($this->isPost() && $user)
		{
			if(!$request->channel || !$request->amount)
			{
				$this->showError("输入信息不全");
			}
			else{
				try{
				
					$refund = new \app\service\RefundSrv();
					$refund->apply($user, $request->amount, 'rmb',$request->channel);
					$this->showError("申请已成功",'index.php?_c=store&_a=asset');
				}catch(\Exception $e)
				{
					$this->showError($e->getMessage());
				}
			}
			
		}
		
		$this->layoutSmarty('releasemoney');
	}
	
	//充值
	public function addmoney($request,$response)
	{
		
		if($this->isPost())
		{
			$amount = $request->amount;
				$neworder = new \app\service\OrderSrv();
				$post['payment_code'] = "alipay";
				$post['payment_name'] = "alipay";
				$post['user_id'] = $this->current_user['user_id'];
				$post['amount'] = $amount;
			$orderid=$neworder->quickSubmit($post);
			$info = PaymentDao::getSlaveInstance ()->find ( array (
					'payment_code' => $request->type,
					'enabled' => 1 
			) );
			$payment = "\\app\\service\\payment\\" . $info ['payment_code'] . '\\' . $info ['payment_code'] . 'paymentSrv';
			
			if (! class_exists ( $payment ))
				throw new \Exception ( "no payment called $payment " );
			
			$paymentSrv = new $payment ( $info );
			$order = \app\dao\OrderDao::getSlaveInstance()->find($orderid);
			self::config();
	
			$form = $paymentSrv->getPayForm ( $order, $this->alipay_config );
			// 统计埋点
			/*self::userLog ( array (
					'type' => 'touchpayment',
					'action' => 'payform',
					'item_id' => $request->id,
					'user_id' => $order ['buyer_id'] 
			) ); */
		echo $form;
		
			
			
		}
		else{
			$response->storeTitle ="充值";
			$response->storeIntro ="随充随取，没有任何限制";
			$this->layoutSmarty('addmoney');
		}
		
	}
	//商家信息
	public function merchant($request,$response)
	{
		$user_id =$this->current_user['user_id'];
		
		$userinfo = \app\dao\UserInfoDao::getSlaveInstance ()->find ( array (
						'user_id' => $user_id
				) );
		$userlevel = \app\dao\AdminDao::getSlaveInstance()->find(
			array(
			'user_id'=>$user_id
			) );
		//查询邀请商家数量
		$sql = "select count(*) as num from ym_user where invite_by = $user_id";
		$num =   \app\dao\UserInfoDao::getSlaveInstance ()->getPdo()->getRow($sql);
		

		
		$response->nick_name = $userinfo['nick_name'];
		$response->phone     = $userinfo['user_name'];
		$response->email     = $userinfo['email'];
		$response->level     = $userlevel['level'];
		$response->ctime     = $userinfo['ctime'];
		$response->isvip     = $userinfo['isvip'];
		$response->invite    = $num['num'];
		$response->point    = $userinfo['point'];
		
		$response->storeTitle ="商家信息";
		$response->storeIntro ="";
		$this->layoutSmarty('merchant');
	}
	
	
	
	
	
	// 添加店铺
	public function add($request, $response) {
		$response->title = '添加店铺';
		// 保存新增
		if ($request->type == 'saveStore') {
			if ($request->store_name) {
				$state = intval ( $request->state );
				$store_id = $request->store_id ? intval ( $request->store_id ) : 1;
				$close_reason = trim ( $request->close_reason );
				// 若店铺关闭，则须填写关闭原因
				if (! $state && empty ( $close_reason )) {
					$this->showError ( '关闭店铺请填写原因' );
				}
				// 若店铺关闭，则关闭原因置空
				if ($state && ! empty ( $close_reason )) {
					$close_reason = '';
				}
				// 获取表单变量
				$params = array (
						'store_id' => $store_id,
						'store_name' => trim ( $request->store_name ),
						'address' => trim ( $request->address ),
						'zipcode' => trim ( $request->zipcode ),
						'tel' => trim ( $request->tel ),
						'credit_value' => intval ( $request->credit_value ),
						'praise_rate' => floatval ( $request->praise_rate ),
						'state' => $state,
						'close_reason' => $close_reason,
						'add_time' => 0,
						'end_time' => 0,
						'last_update' => 0,
						'sort_order' => intval ( $request->sort_order ),
						'description' => trim ( $request->description ),
						'auto_closed_time' => intval ( $request->auto_closed_time ),
						'if_codpay' => intval ( $request->if_codpay ) 
				);
				// 保存
				$result = \app\dao\StoreDao::getMasterInstance ()->save ( $request->store_id, $params, $request->isEdit );
				if (! $result) {
					$this->showError ( '保存信息失败' );
				}
				header ( "Location: index.php?_c=store&_a=index" );
			} else {
				$this->showError ( '提交信息不完整或有误' );
			}
		} else {
			$this->layoutSmarty ( 'add.form' );
		}
	}
	// 修改店铺
	public function edit($request, $response) {
		$response->title = '修改店铺';
		$store_id = intval ( $request->store_id );
		// 获取记录
		$info = \app\dao\StoreDao::getSlaveInstance ()->find ( $store_id );
		$response->info = $info;
		$response->isEdit = true;
		$this->layoutSmarty ( 'add.form' );
	}
	// 查看店铺
	public function detail($request, $response) {
		$response->title = '查看店铺';
		$store_id = intval ( $request->store_id );
		// 获取记录
		$info = \app\dao\StoreDao::getSlaveInstance ()->find ( $store_id );
		$response->info = $info;
		$this->layoutSmarty ( 'detail' );
	}
	// 删除店铺
	public function delete($request, $response) {
		$store_id = intval ( $request->store_id );
		$result = \app\dao\StoreDao::getMasterInstance ()->delete ( $store_id );
		if ($result) {
			header ( "Location: index.php?_c=store&_a=index" );
		} else {
			$this->showError ( '删除店铺失败' );
		}
	}
}