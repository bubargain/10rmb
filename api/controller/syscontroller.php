<?php
namespace api\controller;

use sprite\mvc\controller;

class syscontroller extends Controller{
	
/*
 * 接口名称： brand_list
 * 测试接口
 * 功能：返回当前有新品发布汇的品牌列表
 * @user：Daniel
 * 2015-05-25
 */
	public function brand_list($request,$response){
		$this->renderJson(array(
			'amount'=>'6',
			'utime' => '1432545706',
			'brands' => array(
				'1' => array('name'=>'zara','pic_link'=>'http://120.26.107.42/img/logo1.jpg','event_id'=>12) ,
				'2' => array('name'=>'h&m','pic_link'=>'http://120.26.107.42/img/logo2.jpg','event_id'=>13),
				'3' => array('name'=>'LV','pic_link'=>'http://120.26.107.42/img/logo3.jpg','event_id'=>14),
				'4' => array('name'=>'GUESS','pic_link'=>'http://120.26.107.42/img/logo4.jpg','event_id'=>15),
				'5' => array('name'=>'LAFLOARIN','pic_link'=>'http://120.26.107.42/img/logo5.jpg','event_id'=>17),
				'6'=> array('name'=>'DNANIEL&KATE','pic_link'=>'http://120.26.107.42/img/logo6.jpg','event_id'=>18),
			)
		));
	}
	
/*
 * 接口名称： occasion_list
 * 测试接口
 * 功能：返回按场景进行划分的分类
 * @user：Daniel
 * 2015-05-25
 */
	public function occasion_list($request,$response){
		$this->renderJson(array(
			'amount'=>'7',
			'utime' => '1432545706',
			'brands' => array(
				'1' => array('name'=>'CASUAL','pic_link'=>'http://120.26.107.42/img/logo7.jpg','event_id'=>12,'amount'=>200) ,
				'2' => array('name'=>'OFFICIAL','pic_link'=>'http://120.26.107.42/img/logo6.jpg','event_id'=>13,'amount'=>22),
				'3' => array('name'=>'PARTY','pic_link'=>'http://120.26.107.42/img/logo5.jpg','event_id'=>14,'amount'=>11),
				'4' => array('name'=>'OUTDOOR','pic_link'=>'http://120.26.107.42/img/logo4.jpg','event_id'=>15,'amount'=>9),
				'5' => array('name'=>'SCHOOOL','pic_link'=>'http://120.26.107.42/img/logo3.jpg','event_id'=>17,'amount'=>200),
				'6'=> array('name'=>'BEACH','pic_link'=>'http://120.26.107.42/img/logo2.jpg','event_id'=>18,'amount'=>232),
				'7'=> array('name'=>'PROM','pic_link'=>'http://120.26.107.42/img/logo2.jpg','event_id'=>18,'amount'=>203),		
		)
		));
	}
	
/*
 * 接口名称： style_list
 * 测试接口
 * 功能：返回按风格进行划分的分类
 * @user：Daniel
 * 2015-05-25
 */
	public function style_list($request,$response){
		$this->renderJson(array(
			'amount'=>'7',
			'utime' => '1432545706',	
			'brands' => array(
				'1' => array('name'=>'CUTE','pic_link'=>'http://120.26.107.42/img/logo7.jpg','event_id'=>12,'amount'=>200) ,
				'2' => array('name'=>'BRIEF','pic_link'=>'http://120.26.107.42/img/logo5.jpg','event_id'=>13,'amount'=>22),
				'3' => array('name'=>'STREET','pic_link'=>'http://120.26.107.42/img/logo2.jpg','event_id'=>14,'amount'=>11),
				'4' => array('name'=>'SPORTY','pic_link'=>'http://120.26.107.42/img/logo4.jpg','event_id'=>15,'amount'=>9),
				'5' => array('name'=>'SEXY','pic_link'=>'http://120.26.107.42/img/logo3.jpg','event_id'=>17,'amount'=>200),
				'6'=> array('name'=>'PREPPY','pic_link'=>'http://120.26.107.42/img/logo3.jpg','event_id'=>18,'amount'=>232),
				'7'=> array('name'=>'WORK','pic_link'=>'http://120.26.107.42/img/logo1.jpg','event_id'=>18,'amount'=>203),		
		)
		));
	}
	
}