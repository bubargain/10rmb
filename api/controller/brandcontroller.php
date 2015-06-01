<?php
namespace api\controller;

use sprite\mvc\controller;

class brandcontroller extends controller{
	
	/**
	 * get_runway_list
	 * 测试接口：获取品牌的所有runway信息
	 * @param array $request
	 * @param array $response
	 */
	public function get_runway_list($request, $response){
		$this->renderJson(array(
			'amount' => '3',
			'utime' => '1433163223',
			'list' => array(
				'1' => array(
						'pic_link' =>'http://a.hiphotos.baidu.com/image/pic/item/bf096b63f6246b6034eb897ae9f81a4c500fa2d2.jpg',
						'event_id' => 1211,
						'ctime' => '1433163223'
					),
				'2' => array(
						'pic_link' =>'http://img.hb.aicdn.com/1ba27011d8beebd9a76bb192de9c7d7c0f38b95666e0-nZVlSu_fw658',
						'event_id' => 1212,
						'ctime' => '1433163233'
					),
				'3' => array(
						'pic_link' =>'http://image226-c.poco.cn/best_pocoers/20140213/9992014021311581994402811.jpg',
						'event_id' => 1213,
						'ctime' => '1433163243'
					)	
			)
		));
	}
}