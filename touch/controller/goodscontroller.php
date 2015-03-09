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
		
			$sql= "select * from ym_event where event_id = $event_id and status=1";
			$info = \app\dao\EventDao::getSlaveInstance()->getPdo()->getRow($sql);
			
			if(!$info)
			{
	
				$this->showError("obz~ product sold out!","index.php");
			}
			
			
			if($info['noshipping']==1)//刷单模式，需要验证用户已分配该任务
			{
					$sql = "select A.pic_link,A.product_link,B.price,B.fanli,B.totalfanli,B.id,B.noshipping from ym_event A left join ym_user_event B on A.event_id = B.event_id 
					where A.event_id = $event_id and A.status in (1,88) and B.user_id = $user_id
					";
					$info = \app\dao\EventDao::getSlaveInstance()->getPdo()->getRow($sql);
			}
			if(!$info)
			{
				$this->showError("Event doest exit or expire","index.php");
			}
			
			// memcache 赋值
			$cache->set ( $key, $info, 1, 5 * 60 );
		}
	
		if(preg_match('/qiniudn\.com/i', $info['pic_link']))
		{	
					
			$response->pic_link = $info['pic_link']."?imageView/2/w/320";  //图片转化成320宽
		}
		
		else{
		$response->pic_link=$info['pic_link'];
		}
		$response->restamount =$info['amount'] > $info['applied']?1:0;
		$response->product_link = $info['product_link'];
		$response->id = $info['id'];
		$response->event_id = $info['event_id'];
		$response->price = $info['price'];
		$response->totalfanli =$info['price']+$info['fanli'];
		$response->fanli = round($info['fanli']*PROFITRATE ,1);
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
			if(intval($request->noshipping)) //刷单模式
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
					
					//$noshipping = $request->selectshipping;
					
					$_time = strtotime("now");
					$info['bcode'] =  $user_id.substr($_time,4);
					
					$fanli = round( (float)$bcode['fanli'] * PROFITRATE , 2);  // 扣除佣金后的返利
					$profit = (float)$bcode['fanli'] - $fanli; //平台佣金
					$totalfanli = $fanli + $bcode['price'];
					
					
					\app\dao\UserEventDao::getMasterInstance()->edit($info['id'], 
					array(
					'fanli'=>$fanli,
					'profit'=>$profit,
					'totalfanli'=>$totalfanli,
					'bcode'=>$info['bcode'],
					'utime'=>$_time,
					'noshipping'=>$info['noshipping'],
					'status'=>0)
					
					);
					
					/*if($firsttimeapply)
					{
					//只有用户第一次申请的时候，活动申请总数+1
					$sql = "update ym_event set applied= applied+1 where event_id = ".$info['event_id'];
					
					
					\app\dao\UserEventDao::getMasterInstance()->getPdo()->exec($sql);
					}*/
					$response->bcode= $info['bcode'];
					$response->id = $info['id'];
					$response->product_link = $info['product_link'];
					$this->layoutSmarty ( 'about' );
				} //end 刷单模式
				else  //真实返利模式，开放申请
				{
					$event_id =intval($request->event_id);
					$info = \app\dao\EventDao::getSlaveInstance()->find($event_id);
					
				}
			}
			else { //真实返利模式
				$event_id =intval($request->event_id);
			
				if($event_id) 
				{
					$info = \app\dao\UserEventDao::getSlaveInstance()->find(
						array(
							'user_id'=>$user_id,
							'event_id' => $event_id
						)
					);
					//var_dump($info);die();
					if($info) //已经申请过
					{
						if($info['status']==99)//超时重新申请，重置状态
						{
							$sql = "update ym_user_event set status=0 where id =".$info['id'];
							\app\dao\EventDao::getMasterInstance()->getPdo()->exec($sql);
						}
						$response->bcode = $info['bcode'];
						$response->id = $info['id'];
						$response->product_link = $info['product_link'];
						$this->layoutSmarty ( 'about' );
					}
					else{
						
				
							$info = \app\dao\EventDao::getSlaveInstance()->find($event_id);
							if(!$info['noshipping'] && $info['applied'] < $info['amount'])//可以申请	
							{
									$_time = strtotime("now");
									$bcode =  $user_id.substr($_time,4);
					
									$fanli = round( (float)$info['fanli'] * PROFITRATE , 2);  // 扣除佣金后的返利
									$profit = (float)$info['fanli'] - $fanli; //平台佣金
									
									$id=\app\dao\UserEventDao::getMasterInstance()->add( 
									array(
									'fanli'=>$fanli,
									'profit'=>$profit,
									'price' =>$info['price'],
									'totalfanli'=>$fanli,
									'bcode'=>$bcode,
									'ctime'=>$info['ctime'],
									'profit'=>$profit,
									'event_name'=>$info['event_name'],
									'bcode'=>$bcode,
									'livetime'=>$info['livetime'],
									'pic_link'=>$info['pic_link'],
									'product_link'=>$info['product_link'],
									'event_id'=>$info['event_id'],
									'user_id' =>$user_id,
									'utime'=>$_time,
									'noshipping'=>$info['noshipping'],
									'status'=>0)
								
									);
									$sql = "update ym_event set applied=applied+1 where event_id=".$info['event_id']; //申请数加1
									\app\dao\EventDao::getMasterInstance()->getPdo()->exec($sql);
									$response->bcode = $bcode ;
									$response->id = $id;
									$response->product_link = $info['product_link'];
									$this->layoutSmarty ( 'about' );
							
							}
					}
				}
				else{
					$this->showError('You are too late!All bcodes sent out');
				}
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
			$this->layoutSmarty ( 'about' );
			
		}
		
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