<?php
namespace critic\controller;


class criticController extends BaseController{
	

	
	
	public function index($request,$response){
	
		$goods_id = intval($request->id);
		$info = \app\dao\GoodsDao::getSlaveInstance()->getJudgeProduct();
		if($info)
		{
			
			$response->goods_id =$info['goods_id'];
			$response->goods_name =$info['goods_name'];
			$response->img1= $info['default_image'];
			$response->img2 = $info['default_thumb'];
			$this->layoutSmarty('index');
		}
		else{
			$this->showError("There is no more product waiting for judge now,thanks!",'index.php');
		}
		
		
	}
	
	/**
	 * 
	 * 添加评测结果
	 * @param array $request:
	 *  $request->size
	 *  $request->ms-sleeve
	 *  $re
	 * @param unknown_type $response
	 */
	public function addres($request,$response){
		//var_dump($_REQUEST);die();
		$user_id = $this->checkLogin();
		$goods_id= intval($request->goods_id);
		$size = $request->size;
		$occasion = $_REQUEST['ms-occasion'];
		$style= $_REQUEST['ms-style'];
		$pattern = $_REQUEST['ms-pattern'];
		$tagsrv = new \app\service\GoodsSrv();
		
		if($size)
			$tagsrv->setTag($goods_id,$size,$user_id); //插入商品属性表
		if($pattern)
			$tagsrv->setTag($goods_id,$pattern,$user_id);
		if($style){
			
			$tagsrv->setTag($goods_id,$style,$user_id);
		}
		if($occasion){
			$tagsrv->setTag($goods_id,$occasion,$user_id);
		}
		//修改商品状态“已上架”		
		\app\dao\GoodsDao::getMasterInstance()->productLaunch($goods_id);
		header('location:index.php?_c=critic');
	}
	
	
	
}
