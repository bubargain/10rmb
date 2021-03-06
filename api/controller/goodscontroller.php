<?php
namespace api\controller;
use sprite\mvc\controller;

class goodscontroller extends Controller{
	
	/**
	 * get_goods 
	 * 测试接口： 按商品ID获取商品的详细信息
	 * input:
	 * id: goods_id
	 * @author:daniel ma
	 * 2015-05-27
	 */
	public function get_goods($request,$response){
		$goods_id = $request->id;
		if(!$goods_id)
		{
			$this->renderJson(array(
				'error'=>"goods_id can't be null or goods is not exist"
			));
		}
		else if($goods_id == 1213){
			$this->renderJson(array(
				'goods_id'=> $goods_id,
				'goods_title' => "Fancy Sexy Dress for Young",
				'img_link' => "http://img0.bdstatic.com/img/image/70dddc10daaeab1ab987cedb1511d2621426747009.jpg",
				'img_detail_link' => "http://g01.a.alicdn.com/kf/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu/222109129/HTB1.Ob_GFXXXXcXXVXXq6xXFXXXu.jpg",
				'price' => '21.5',
				'color' => '#3e4f5e,#000,#9f3e4d',
				'size'  => 'S,M,L,XL',
				'has_coupon' => 'Y',
				'coupon' => array(
							'coupon'=>'10.0',
							'amount'=>'50',
							'left_amount'=>'21',
							'coupon_id'=>'12330',
							'expired_time'=>'1442728088'
							),
				'ctime' => '1432728088',
				'last_time' => '7776000', //商品持续时间，默认90天
				'brand_id' => '234',
				'brand_name' => 'Luies Voution',
			));
		}
		else{
	
			$this->renderJson(array(
				'goods_id'=> $goods_id,
				'goods_title' => "Fancy Sexy Dress for Young",
				'img_link' => "http://h.hiphotos.baidu.com/image/pic/item/1ad5ad6eddc451da161b06eab4fd5266d11632c5.jpg",
				'img_detail_link' => "http://h.hiphotos.baidu.com/image/pic/item/1ad5ad6eddc451da161b06eab4fd5266d11632c5.jpg",
				'price' => '1222.5',
				'color' => '#3e3f4e,#fff',
				'size'  => 'Only XL',
				'has_coupon' => 'Y',
				'coupon' => array(
							'coupon'=>'8.0',
							'amount'=>'40',
							'left_amount'=>'2',
							'coupon_id'=>'12330',
							'expired_time'=>'1442728088'
							),
				'ctime' => '1432728088',
				'last_time' => '7776000', //商品持续时间，默认90天
				'brand_id' => '122',
				'brand_name' => 'American Apperal',
			));
		
		}
	}

	/**
	 * set_goods_feedback
	 * 测试接口：设置商品测试反馈，like or nope
	 * Input:
	 * goods_id : goods id
	 * user_id  : user id
	 * fd       : feedback "like" or "nope"
	 * @author: daniel
	 * 2015-05-27
	 */
	public function set_goods_feedback ($request,$respone){
		$goods_id = $request->goods_id;
		$user_id  = $request->user_id;
		$fd = $request->fd == 'like' ? True : false;
		if($goods_id && user_id )
		{
			
				$this->renderJson(array(
					'status' => "success",
					'fd' => $fd
				));
			
		}
		else{
			$this->renderJson(array(
					'status' => "false",
					'error' => "you didnt set goods_id,user_id and fd"
				));
		}
	}
}