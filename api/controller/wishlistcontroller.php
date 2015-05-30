<?php
namespace api\controller;

use sprite\mvc\controller;

class wishlistcontroller extends Controller{
	
	
	/**
	 * get_wishlists
	 * 测试接口：  获取用户的wishlists列表
	 * @param unknown_type $request
	 * @param unknown_type $response
	 */
	public function get_wishlists($request,$response){
		$user_id = $request->user_id;
		if($user_id)
		{
		$this->renderJson(array(
			'amount' => 4,
			'utime' => '1432728088',
			'lists'=> array(
					'1' =>array(
							'wishlist_id' => 121211,
							'comments' => 'i like all of them ,which should i choose',
							'img_link' => "http://g01.a.alicdn.com/kf/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu/222109129/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu.jpg",
							'ctime' => '1432728088',
						),
						
					'2' =>array(
							'wishlist_id' => 121211,
							'comments' => 'i like all of them ,which should i choose',
							'img_link' => "http://g01.a.alicdn.com/kf/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu/222109129/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu.jpg",
							'ctime' => '1432728087',
						),
				
					'3' =>array(
							'wishlist_id' => 121211,
							'comments' => 'i like all of them ,which should i choose',
							'img_link' => "http://g01.a.alicdn.com/kf/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu/222109129/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu.jpg",
							'ctime' => '1432728078',
						),
				
					'4' =>array(
							'wishlist_id' => 121211,
							'comments' => 'i like all of them ,which should i choose',
							'img_link' => "http://g01.a.alicdn.com/kf/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu/222109129/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu.jpg",
							'ctime' => '1432728068',
						)
				
				)
		));
		}
		else
		{
			$this->renderJson(array('error'=>'No user_id given'));
		}
	}

	/**
	 * get_wishlist_by_id 
	 * 测试接口：按ID获取用户wishlist
	 * 
	 */
	public function get_wishlist_by_id($request,$response)
	{
		$wishlist_id = $request->wishlist_id;
		$user_id = $request->user_id;
		if($wishlist_id && $user_id){
			$this->renderJson(array(
				'amount' => 9,
				'gtime'  => '1432728088' ,
				'items' => array(
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
								'goods_id' => 1215,
								'img_link' => "http://ecx.images-amazon.com/images/I/81GxAX44DUL._UX522_.jpg",
								'brand_name' => 'flora',
								'has_coupon' => 'false',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								),
						'4'=> array(
								'goods_id' => 1236,
								'img_link' => "http://ecx.images-amazon.com/images/I/81GrOC4-0-L._UX522_.jpg",
								'brand_name' => 'zoe',
								'has_coupon' => 'false',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								),
						'5'=> array(
								'goods_id' => 1217,
								'img_link' => "http://ecx.images-amazon.com/images/I/71MTLdirNML._UX522_.jpg",
								'brand_name' => 'louts voution',
								'has_coupon' => 'true',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								),
						'6'=> array(
								'goods_id' => 1218,
								'img_link' => "http://ecx.images-amazon.com/images/I/71w1V28oOZL._UX522_.jpg",
								'brand_name' => 'louts voution',
								'has_coupon' => 'false',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								),
						'7'=> array(
								'goods_id' => 1219,
								'img_link' => "http://g01.a.alicdn.com/kf/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu/222109129/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu.jpg",
								'brand_name' => 'louts voution',
								'has_coupon' => 'true',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								),
						'8'=> array(
								'goods_id' => 1220,
								'img_link' => "http://ecx.images-amazon.com/images/I/81l42-ZL5ZL._UX522_.jpg",
								'brand_name' => 'louts voution',
								'has_coupon' => 'false',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								),
						'9'=> array(
								'goods_id' => 1221,
								'img_link' => "http://g01.a.alicdn.com/kf/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu/222109129/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu.jpg",
								'brand_name' => 'louts voution',
								'has_coupon' => 'false',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								)
				)
			));
		}else{
			$this->renderJson(array('error'=>'Parameters not given'));
		}
	}
	
	
/**
	 * get_newest_wishlist 
	 * 测试接口：获取用户最新的wishlist
	 * 
	 */
	public function get_newest_wishlist($request,$response)
	{
		
		$user_id = $request->user_id;
		if($user_id){
			$this->renderJson(array(
				'amount' => 9,
				'gtime'  => '1432728088' ,
				'items' => array(
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
								'goods_id' => 1215,
								'img_link' => "http://ecx.images-amazon.com/images/I/81GxAX44DUL._UX522_.jpg",
								'brand_name' => 'flora',
								'has_coupon' => 'false',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								),
						'4'=> array(
								'goods_id' => 1236,
								'img_link' => "http://ecx.images-amazon.com/images/I/81GrOC4-0-L._UX522_.jpg",
								'brand_name' => 'zoe',
								'has_coupon' => 'false',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								),
						'5'=> array(
								'goods_id' => 1217,
								'img_link' => "http://ecx.images-amazon.com/images/I/71MTLdirNML._UX522_.jpg",
								'brand_name' => 'louts voution',
								'has_coupon' => 'true',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								),
						'6'=> array(
								'goods_id' => 1218,
								'img_link' => "http://ecx.images-amazon.com/images/I/71w1V28oOZL._UX522_.jpg",
								'brand_name' => 'louts voution',
								'has_coupon' => 'false',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								),
						'7'=> array(
								'goods_id' => 1219,
								'img_link' => "http://g01.a.alicdn.com/kf/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu/222109129/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu.jpg",
								'brand_name' => 'louts voution',
								'has_coupon' => 'true',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								),
						'8'=> array(
								'goods_id' => 1220,
								'img_link' => "http://ecx.images-amazon.com/images/I/81l42-ZL5ZL._UX522_.jpg",
								'brand_name' => 'louts voution',
								'has_coupon' => 'false',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								),
						'9'=> array(
								'goods_id' => 1221,
								'img_link' => "http://g01.a.alicdn.com/kf/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu/222109129/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu.jpg",
								'brand_name' => 'louts voution',
								'has_coupon' => 'false',
								'brand_img_link' => 'http://120.26.107.42/img/logo7.jpg' 
								)
				)
			));
		}else{
			$this->renderJson(array('error'=>'Parameters not given'));
		}
	}
}