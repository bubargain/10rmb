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
}