<?php
namespace  api\controller;

use sprite\mvc\controller;

class runwaycontroller extends Controller{

	/**
	 * get_new
	 * 测试接口： 新建一组秀场
	 * INPUT：
	 * user_id : user id
	 * mode    : 秀场类型 occasion or brand or type
	 * mode_val: 具体选择的类型
	 * @author ：  daniel
	 * @since  :  20150527
	 */
	public function get_new ($request,$response){
		$user_id = $request->user_id;
		$mode = $request->mode;
		$mode_val = $request->mode_val;
	
		$this->renderJson(array(
			'amount'=> 12,
			'gtime'=> '1432728088', //generate time
			'user_id' => 1, //generate for whom,
			'intro_title'   => "This is the runway into Manhatun's elite life" ,
			'intro_desc'    => "We choose the fresh launch products for you, choose the ones you like then you may find suprise waiting",
			'items'=> array(
						'1'=> array(
								'goods_id' => 1213,
								'img_link' => "http://ecx.images-amazon.com/images/I/81IySnoisEL._UX522_.jpg",
								'brand_name' => 'louts voution',
								'has_coupon' => 'true',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								),
						'2'=> array(
								'goods_id' => 1214,
								'img_link' => "http://ecx.images-amazon.com/images/I/912OKw7dZvL._UY679_.jpg",
								'brand_name' => 'plaza',
								'has_coupon' => 'false',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								),
						'3'=> array(
								'goods_id' => 1235,
								'img_link' => "http://ecx.images-amazon.com/images/I/81GxAX44DUL._UX522_.jpg",
								'brand_name' => 'flora',
								'has_coupon' => 'false',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								),
						'4'=> array(
								'goods_id' => 1234,
								'img_link' => "http://ecx.images-amazon.com/images/I/81GrOC4-0-L._UX522_.jpg",
								'brand_name' => 'zoe',
								'has_coupon' => 'false',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								),
						'5'=> array(
								'goods_id' => 1215,
								'img_link' => "http://ecx.images-amazon.com/images/I/71MTLdirNML._UX522_.jpg",
								'brand_name' => 'louts voution',
								'has_coupon' => 'true',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								),
						'6'=> array(
								'goods_id' => 1216,
								'img_link' => "http://ecx.images-amazon.com/images/I/71w1V28oOZL._UX522_.jpg",
								'brand_name' => 'louts voution',
								'has_coupon' => 'true',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								),
						'7'=> array(
								'goods_id' => 1217,
								'img_link' => "http://g01.a.alicdn.com/kf/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu/222109129/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu.jpg",
								'brand_name' => 'louts voution',
								'has_coupon' => 'true',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								),
						'8'=> array(
								'goods_id' => 1218,
								'img_link' => "http://ecx.images-amazon.com/images/I/81l42-ZL5ZL._UX522_.jpg",
								'brand_name' => 'louts voution',
								'has_coupon' => 'true',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								),
						'9'=> array(
								'goods_id' => 1219,
								'img_link' => "http://g01.a.alicdn.com/kf/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu/222109129/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu.jpg",
								'brand_name' => 'louts voution',
								'has_coupon' => 'true',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								),
						'10'=> array(
								'goods_id' => 1220,
								'img_link' => "http://ecx.images-amazon.com/images/I/81GxAX44DUL._UX522_.jpg",
								'brand_name' => 'louts voution',
								'has_coupon' => 'true',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								),
						'11'=> array(
								'goods_id' => 1221,
								'img_link' => "http://g01.a.alicdn.com/kf/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu/222109129/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu.jpg",
								'brand_name' => 'louts voution',
								'has_coupon' => 'true',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								),
						'12'=> array(
								'goods_id' => 1222,
								'img_link' => "http://ecx.images-amazon.com/images/I/912OKw7dZvL._UY679_.jpg",
								'brand_name' => 'louts voution',
								'has_coupon' => 'false',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								)
								
					)
					
		));
		
	}
}