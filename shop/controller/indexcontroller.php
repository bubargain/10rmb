<?php

namespace shop\controller;

use \app\service\appcountsrv;


class IndexController extends BaseController {
	public function index($request, $response) {

        //\sprite\lib\Debug::log('user', $this->current_user);
        $response->current_user = $this->current_user;
        $sql = "select * from ym_brand where if_show=0";
        $brands = \app\Dao\UserDao::getSlaveInstance()->getpdo()->getRows($sql);
        $response->brands= $brands;
        $this->layoutSmarty();
	}

}