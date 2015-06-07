<?php
namespace shop\controller;


class brandcontroller extends BaseController{
	
	public function verify($request,$response){
		$brand_id = $request->id;
		$pass =$request->pass == 'tongguo' ? 1 : 2; //1 代表通过，2代表驳回
		if($brand_id)
		{
			\app\dao\BrandDao::getMasterInstance()->edit($brand_id,
				array('if_show'=>$pass));
		}
		header('location:index.php');
	}
}