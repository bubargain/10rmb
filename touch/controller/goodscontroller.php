<?php

namespace touch\controller;
use \sprite\cache\CacheManager;
class goodscontroller extends BaseController {
	public function detail($request, $response) {
		
		$this->checkLogin ();
			
		
		$event_id = $request->id;
	   //cache index  
		$cache = CacheManager::getInstance ();
		
		$key = 'touch_index_page'.$event_id;
		
		$info = $cache->get ( $key );
		
		if (! $info) {		
			$user_id = $this->current_user['user_id'];	
			$sql = "select A.pic_link,A.product_link,B.price,B.fanli,B.totalfanli,B.id,B.noshipping from ym_event A left join ym_user_event B on A.event_id = B.event_id 
			where A.event_id = $event_id and A.status in (1,88) and B.user_id = $user_id
			";
			$info = \app\dao\EventDao::getSlaveInstance()->getPdo()->getRow($sql);
			if(!$info)
			{
				$this->showError("Event doest exit or expire");
			}
			// memcache 赋值
			$cache->set ( $key, $info, 1, 5 * 60 );
		}

		
		$response->pic_link=$info['pic_link'];
		$response->product_link = $info['product_link'];
		$response->id = $info['id'];
		$response->price = $info['price'];
		$response->totalfanli =$info['price']+$info['fanli'];
		$response->fanli = $info['fanli'];
		$response->noshipping = $info['noshipping'];
		$action_template = $this->_controller .'/details.html';
		$smarty =  new \sprite\mvc\SmartyView($this->_response);
		$smarty->render(strtolower($action_template));
		
		
		
	}
	
	/**
	* show bcode
	* 
	*/
	/**
	 * 
	 * update about : 如果申请已满，则不能再取
	 * @param unknown_type $request
	 * @param unknown_type $response
	 */
	
	public function about($request,$response)
	{
		$user_id=$this->checkLogin();
		if($this->isPost())
		{
			$info = \app\dao\UserEventDao::getSlaveInstance()->find(
				array(
					'user_id'=>$user_id,
					'id' => $request->id
				)
			);
			$bcode= \app\dao\EventDao::getSlaveInstance()->find($info['event_id']);
			if($bcode and $bcode['applied'] < $bcode['amount'])
			{
				if($info['bcode']==null)
					$firsttimeapply = true;
				
				$noshipping = $request->selectshipping;
				
				$_time = strtotime("now");
				$info['bcode'] =  $user_id.substr($_time,4);
				
				$fanli = round( (float)$bcode['fanli'] * PROFITRATE , 2);  // 扣除佣金后的返利
				$profit = (float)$bcode['fanli'] - $fanli; //平台佣金
				if($noshipping)
						$totalfanli = $fanli + $bcode['price'];
				else 
						$totalfanli = $fanli;
				
				
				\app\dao\UserEventDao::getMasterInstance()->edit($info['id'], 
				array(
				'fanli'=>$fanli,
				'profit'=>$profit,
				'totalfanli'=>$totalfanli,
				'bcode'=>$info['bcode'],
				'utime'=>$_time,
				'noshipping'=>$noshipping,
				'status'=>0)
				
				);
				
				if($firsttimeapply)
				//只有用户第一次申请的时候，活动申请总数+1
				$sql = "update ym_event set applied= applied+1 where event_id = ".$info['event_id'];
				
				
				\app\dao\UserEventDao::getMasterInstance()->getPdo()->exec($sql);
				$response->bcode= $info['bcode'];
				$response->id = $info['id'];
				$response->product_link = $info['product_link'];
				$this->layoutSmarty ( 'about' );
			}
			else {
				$this->showError('You are too late!All bcodes sent out');
			}
		}
		else{
		
			if(!$request->id)
				$this->showError('you are not allowed to view this page',"index.php");
			$info = \app\dao\UserEventDao::getSlaveInstance()->find(
				array(
					'user_id'=>$user_id,
					'id' => $request->id
				)
			);
			if(!$info)
				$this->showError("you have no right to view this page","index.php");
		
			$response->bcode= $info['bcode'];
			$response->id = $info['id'];
			$response->product_link = $info['product_link'];
			
			
		}
		$this->layoutSmarty ( 'about' );
	}
	
	/*
	public function search($request, $response) {
		$sort = $request->get ( 'sort', '' );
		$page = $request->get ( 'page', 1 );
		$size = $request->get ( 'size', 10 );
		
		$params = array ();
		if ($request->cate_id)
			$params ['cate_id'] = intval ( $request->cate_id );
		
		if ($request->cate_name)
			$params ['cate_name'] = $request->cate_name;
		
		if ($request->tags)
			$params ['tags'] = $request->tags;
		
		if ($request->keyword)
			$params ['keyword'] = $request->keyword;
		
		if ($request->price) {
			$params ['price'] = $request->price;
		}
		$from = $request->get ( 'from', '' );
		
		if ($from == 'search') // 保护词转换
			$params = self::getAlia ( $params );
		$searchSrv = new \app\service\SearchSrv ();
		$ret = $searchSrv->search ( $params, $sort, $page, $size );
		if ($this->has_login && $ret ['list']) {
			foreach ( $ret ['list'] as $k => $r ) {
				$ret ['list'] [$k] ['liked'] = false;
				$ids [] = $r ['goods_id'];
			}
			$_tmp = \app\dao\LoveDao::getSlaveInstance ()->getMyListByGoodsIds ( $ids, $this->current_user ['user_id'] );
			if ($_tmp) {
				foreach ( $_tmp as $r ) {
					$_t [$r ['goods_id']] = true;
				}
				foreach ( $ret ['list'] as $k => $v ) {
					$ret ['list'] [$k] ['liked'] = isset ( $_t [$v ['goods_id']] ) ? true : false;
				}
			}
		}
		
		$ret ['cur_page'] = $page;
		$ret ['pages'] = ceil ( $ret ['count'] / $size );
		$ret ['prev'] = ($page <= 1) ? false : $page - 1;
		$ret ['next'] = ($ret ['pages'] <= $page) ? false : $page + 1;
		
		$url = preg_replace ( '/([?|&]page=\d+)/', '', $_SERVER ['REQUEST_URI'] );
		$response->cur_url = $url;
		
		$params ['sort'] = $sort;
		$response->params = $params;
		$response->ret = $ret;
		$response->title = $request->tags ? $request->tags : '全部礼物';
		$this->layoutSmarty ( 'search' );
		
	} */
}