<?php

/**
 * @author wanjilong@yoka.com
 * @desc
 */
namespace app\service\payment\alipay;

use \app\service\payment\BasePaymentSrv;

require_once (ROOT_PATH . "/lib/alipay/alipay_submit.class.php");
require_once (ROOT_PATH . "/lib/alipay/alipay_notify.class.php");
require_once (ROOT_PATH . "/lib/alipay/alipay_core.function.php");
require_once (ROOT_PATH . "/lib/alipay/alipay_rsa.function.php");
class AlipayPaymentSrv extends BasePaymentSrv {
	var $_gateway = '';
	/* 支付方式唯一标识 */
	var $_code = '';
	public function __construct($config) {
		parent::__construct ( $config );
		
		$this->_config ['partner'] = array (
				'partner' => ALIPAY_ID,
				'seller' => 'ceo@kitetea.com'
		);
	}
	
	
	
	/**
	 * 验证支付结果,参照 支付宝网页支付
	 */
	public function verifyNotify($_POST, $alipay_config) {
		if(empty($_POST)) {//判断POST来的数组是否为空
			return false;
		}
		else {
			//生成签名结果
			$isSign = $this->getSignVeryfy($_POST, $_POST["sign"]);
			//获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
			$responseTxt = 'true';
			if (! empty($_POST["notify_id"])) {$responseTxt = $this->getResponse($_POST["notify_id"]);}
			
			//写日志记录
			//if ($isSign) {
			//	$isSignStr = 'true';
			//}
			//else {
			//	$isSignStr = 'false';
			//}
			//$log_text = "responseTxt=".$responseTxt."\n notify_url_log:isSign=".$isSignStr.",";
			//$log_text = $log_text.createLinkString($_POST);
			//logResult($log_text);
			
			//验证
			//$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
			//isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
			if (preg_match("/true$/i",$responseTxt) && $isSign) {
				return true;
			} else {
				return false;
			}
		}
		/*
		 * $buffer = decrypt($POST['notify_data']); $xml =
		 * simplexml_load_string($buffer); $array =
		 * json_decode(json_encode((array) $xml), 1); $notify_data =
		 * array($xml->getName() => $array); if(!isset($notify_data["notify"]))
		 * throw new \Exception('认证失败', 5000); return array(
		 * 'order_sn'=>$notify_data['out_trade_no'],
		 * 'discount'=>$notify_data['discount'], 'price'=>$notify_data['price'],
		 * 'quantity'=>$notify_data['quantity'],
		 * 'trade_status'=>$notify_data['trade_status'],
		 * 'total_fee'=>$notify_data['total_fee'], );
		 */
	}
	
   /**
     * 针对return_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
	function verifyReturn($_GET, $alipay_config){
		if(empty($_GET)) {//判断POST来的数组是否为空
			return false;
		}
		else {
			//生成签名结果
			$isSign = $this->getSignVeryfy($_GET, $_GET["sign"]);
			//获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
			$responseTxt = 'true';
			if (! empty($_GET["notify_id"])) {$responseTxt = $this->getResponse($_GET["notify_id"]);}
			
			if (preg_match("/true$/i",$responseTxt) && $isSign) {
				return true;
			} else {
				return false;
			}
		}
	}
	public function getPayForm($order, $alipay_config) {
	
		// **req_data详细信息**
		
		if ($_SERVER ['SERVER_NAME'] == TOUCH_OAK  ) {
			// 服务器异步通知页面路径
			$notify_url = $_SERVER ['ROOT_DOMAIN'] . "/api/payment/webnotify";
			// 需http://格式的完整路径，不允许加?id=123这类自定义参数
			// 页面跳转同步通知页面路径
			// 需http://格式的完整路径，不允许加?id=123这类自定义参数
			$return_url = $_SERVER ['ROOT_DOMAIN'] . "/api/payment/webcallback";
		} else {
			// 服务器异步通知页面路径
			$notify_url = TOUCH_OAK . "/api/payment/webnotify";
			// 需http://格式的完整路径，不允许加?id=123这类自定义参数
			// $call_back_url = 'touch.ymall.com'. "/api/payment/webcallback";
			$return_url = TOUCH_OAK . "/api/payment/webcallback";
		}
		
		// 卖家支付宝帐户
		$seller_email = SELLER_EMAIL;
		// 必填
		
		// 商户订单号
		$out_trade_no = $order ['order_sn'];
		// 商户网站订单系统中唯一订单号，必填
		
		// 订单名称
		$subject = '10Buck充值订单：'.$order ['order_sn'];
		// 必填
		
		// 付款金额
		$total_fee = $order ['order_amount'];
		// 必填
	
		
		 //支付类型
        $payment_type = "1";
        //必填，不能修
     
        //必填        //订单描述        $body = $_POST['WIDbody'];
        //商品展示地址
        $show_url = "http://www.10buck.com";
        //需以http://开头的完整路径，例如：http://www.商户网址.com/myorder.html        //防钓鱼时间戳
        $anti_phishing_key = "";
        //若要使用请调用类文件submit中的query_timestamp函数        //客户端的IP地址
        $exter_invoke_ip = "";
        //非局域网的外网IP地址，如：221.0.0.1


		/************************************************************/
		
		//构造要请求的参数数组，无需改动
		$parameter = array(
				"service" => "create_direct_pay_by_user",
				"partner" => trim($alipay_config['partner']),
				"payment_type"	=> $payment_type,
				"notify_url"	=> $notify_url,
				"return_url"	=> $return_url,
				"seller_email"	=> $seller_email,
				"out_trade_no"	=> $out_trade_no,
				"subject"	=> $subject,
				"total_fee"	=> $total_fee,
				"body"	=> $body,
				"show_url"	=> $show_url,
				"anti_phishing_key"	=> $anti_phishing_key,
				"exter_invoke_ip"	=> $exter_invoke_ip,
				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);
		
		// 建立请求
		$alipaySubmit = new \AlipaySubmit ( $alipay_config );
		$html_text = $alipaySubmit->buildRequestForm ( $parameter, 'get', '确认' );
		// echo $call_back_url;die();
		return $html_text;
	}
/**
     * 获取返回时的签名验证结果
     * @param $para_temp 通知返回来的参数数组
     * @param $sign 返回的签名结果
     * @return 签名验证结果
     */
	function getSignVeryfy($para_temp, $sign) {
		//除去待签名参数数组中的空值和签名参数
		$para_filter = paraFilter($para_temp);
		
		//对待签名参数数组排序
		$para_sort = argSort($para_filter);
		
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = createLinkstring($para_sort);
		
		$isSgin = false;
		switch (strtoupper(trim($this->alipay_config['sign_type']))) {
			case "MD5" :
				$isSgin = md5Verify($prestr, $sign, $this->alipay_config['key']);
				break;
			default :
				$isSgin = false;
		}
		
		return $isSgin;
	}

    /**
     * 获取远程服务器ATN结果,验证返回URL
     * @param $notify_id 通知校验ID
     * @return 服务器ATN结果
     * 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空 
     * true 返回正确信息
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
	function getResponse($notify_id) {
		$transport = strtolower(trim($this->alipay_config['transport']));
		$partner = trim($this->alipay_config['partner']);
		$veryfy_url = '';
		if($transport == 'https') {
			$veryfy_url = $this->https_verify_url;
		}
		else {
			$veryfy_url = $this->http_verify_url;
		}
		$veryfy_url = $veryfy_url."partner=" . $partner . "&notify_id=" . $notify_id;
		$responseTxt = getHttpResponseGET($veryfy_url, $this->alipay_config['cacert']);
		
		return $responseTxt;
	}
}
}