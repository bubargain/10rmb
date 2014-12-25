<?php
/**
 * @author wanjilong@yoka.com
 * @desc
 */
/**
 * @author Daniel Ma
 * @time  2014-08-16
 * @desc
 */

namespace app\service;

//简单版搜索yinqing
//一个用户每24天只能看到固定10个商品
//一个用户一天内看到不同商家的产品
//每天00:00清除未被访问的bcode

class SearchSrv extends BaseSrv {
	
	public function search ($params, $amount)
	{
		/*$limit= ($page-1)*skip .','.$skip;
		$sql= "select * from ym_goods left join ym_goods_statistics on ym_goods.goods_id = ym_goods_statistics.goods_id where status=12 order by ym_goods.sale_type desc";
		//$list = \app\dao\GoodsDao::getSlaveInstance ()->getList($params,$limit);
		$list = \app\dao\GoodsDao::getSlaveInstance ()->getpdo()->getRows($sql);
		//var_dump($list);die();
		return array('count'=>count($list), 'list'=>$list);
		*/
		if(!$params['user_id'])
			throw new \Exception ('User not login',10021);
		$user_id=$params['user_id'];
		
		//小于1的user event
		$sql = "select * from ym_user_event where status in (0,100) and user_id = $user_id";
		
		$userEvent = \app\dao\UserEventDao::getSlaveInstance()->getPdo()->getRows($sql);
		
	
	
		
		if(self::checkDailyNewVisit($userEvent))
		//每天第一次访问，新建列表
		//上次访问超过24小时
		//if(!$userEvent || strtotime("now") - $userEvent[0]['ctime'] > 24*60*60 )
		{
			
			$this->newUserEvent($user_id,$amount);
		
		}
		/*else{//检查过期
			
			$uevent = new \app\service\EventSrv();
			$uevent->updateStatus($userEvent);

		}*/
		$userEvent = \app\dao\UserEventDao::getSlaveInstance()->findAll(
				array('user_id'=> $user_id,'B.status'=>100)
			);//重新查询
	
		if($userEvent)
			return $userEvent;
		else 
			return false;
	}
	

	
	
	
	/**
	 * 
	 * check whether it is the first time visit everyday
	 * @param unknown_type $userEvent
	 */
	private function checkDailyNewVisit($userEvent)
	{
		if(!$userEvent)
			return true;
		$maxetime =0;
		
		foreach ($userEvent as $event) {   //查询最晚一次的创建时间
			if($event['ctime']> strtotime('now')) //有创建于未来的订单，也就是新手任务
			{
				return false;
			}
			else if ($event['etime'] >$maxetime)
				$maxetime = $event['etime'];
		}
		if($maxetime +24*60*60 < strtotime("now")) //最后一次更新超过24小时
		{
			return true;
		}
		else
			return false;
	}
	
	
	/**
	 * 
	 * create new event list for user everyday
	 * @param int $user_id
	 * @param int $amount : total amount of events
	 */
	private function newUserEvent($user_id, $amount)
	{
		
		try{
		$time=strtotime("now");
		//到期时间超过一个小时的正常活动
		$sql= "select * from ym_event where status=1 and applied < amount order by RAND() limit 0,10"; 
		$_pdo = \app\dao\EventDao::getSlaveInstance ()->getpdo();
		$event_ids = $_pdo->getRows($sql);
	
		//insert into new user_event
		
			\app\dao\UserEventDao::getMasterInstance()-> beginTransaction();
			$sql2= "delete from ym_user_event where status =100 and user_id = ".$user_id;//删除旧记录
		
			$_pdo->exec($sql2);
			foreach($event_ids as $event)
			{
				$exist= \app\dao\UserEventDao::getSlaveInstance()->find(
					array('user_id'=>$user_id,'event_id'=>$event['event_id'])
				);
				if(!$exist) //同一活动不会被分配两次
				{
					
					$fanli = (float)$event['fanli'] * PROFITRATE;
				
					
					\app\dao\UserEventDao::getMasterInstance()->add(
						array(
							'event_id' => $event['event_id'],
							'user_id' => $user_id,
							'price' =>  $event['price'],
							'utime' => $time,
							'ctime' => $event['ctime'],
							'etime' => $time,
							'status'=>100,
							'fanli'=>$fanli,
							'profit'=>(float)$event['fanli'] - $fanli,
							'livetime' => $event['livetime'],
							'noshipping' => $event['noshipping'],
							'store' => $event['store'],
							'event_name' => $event['event_name'],
							'product_link' => $event['product_link'],
							'pic_link' => $event['pic_link']
						)
					);
				}
			}
			\app\dao\UserEventDao::getMasterInstance()->commit();
		}catch(Exception $e)
        {
        		\app\dao\UserEventDao::getMasterInstance()->rollBack();
        		var_dump($e->message());
        }
		
		
		
	}
	
}





//原始的搜索引擎


class SearchSrvBAK extends BaseSrv {
    const TIME_OUT = 5;
    const SEARCH_URL = 'http://10.0.1.136:8081/ymallsearch/ymall_apps';

    const SEARCH_FIELDS = 'id,goods_name,brand_name,brand_id,cate_id,cate_name,tags,default_thumb,default_image,cate_id_1,cate_id_2,price,market_price,stock,sale_type,if_codpay,wishes,views,orders,sales,weight';
    const SEARCH_Provider_URL = '/select/?facet=on&facet.field=brand_name_auto&facet.limit=200&facet.field=brand_id&fl=brand_id,cate_id_1,brand_name&wt=json&indent=on&rows=0';
    /**
     * @param $info 商品的信息
     * @param array $file_ids 对应的文件上传id
     * @throws \Exception
     */
    public function search($params, $sort = 'default', $page = 1, $skip = 20) {
        try{
            if($page < 1)
                $page = 1;

            $limit = ($page - 1) * $skip;

            $sort = self::getSort($sort);

            $r = self::doSearch($params, $sort, $limit, $skip);

            if($r) {
                $r =json_decode($r);

                if($r->response) {
                    //result count
                    $goods_count = $r->response->numFound;


                    $goodsSrv = new \app\service\GoodsSrv();
                    $type_arr = $goodsSrv->getSaleType();

                    //read result goods_data
                    if($goods_count) {
                        foreach($r->response->docs as $row) {
                            $list[] = array(
                                'goods_id'=> (string)$row->id,
                                'goods_name'=> $row->goods_name,
                                'brand_name'=> $row->brand_name,
                                'cate_name'=> $row->cate_name,
                                'sale_type'=> $row->sale_type,
                                'sale_type_info'=> $row->sale_type ? $type_arr[$row->sale_type] : array(),
                                'tags'=> $row->tags ? $row->tags[0] : null,
                                'price'=> (float)$row->price,
                                'market_price'=> (float)$row->market_price,
                                'stock'=> (int)$row->stock,
                                'wishes'=> (int)$row->wishes,
                                'weight'=> (int)$row->weight,
                                'default_thumb'=> preg_match('/^http:\/\//', $row->default_thumb) ? $row->default_thumb : CDN_YMALL . $row->default_thumb,
                                'default_image'=> preg_match('/^http:\/\//', $row->default_image) ? $row->default_image : CDN_YMALL . $row->default_image,
                            );
                        }
                    }
                }
            }
            return array('count'=>$goods_count, 'list'=>$list);
        }
        catch(\Exception $e) { throw $e; }
    }

    private function getSort($key) {
        $sortArr = array(
            'default'=>'weight desc ',
            'score'=>'score desc ',
        );
        return isset($sortArr[$key]) ? $sortArr[$key] : $sortArr['default'];
    }

    private function doSearch($params, $sort, $limit, $skip) {
        $qfs = array();

        if(isset($params['cate_id']) && intval($params['cate_id']) > 0) {
            $cate_id = intval($params['cate_id']);
            $qfs[] = "(cate_id:$cate_id OR cate_id_1:$cate_id OR cate_id_2:$cate_id)";
        }

        if(isset($params['cate_name'])) {
            $cate_name = trim($params['cate_name']);
            $qfs[] = "(cate_name:$cate_name OR cate_name_1:$cate_name OR cate_name_2:$cate_name)";
        }

        if(isset($params['brand_id']) && intval($params['brand_id']) > 0) {
            $brand_id = intval($params['brand_id']);
            $qfs[] = "(brand_id:$brand_id)";
        }

        if(isset($params['goods_name'])) {
            $goods_name = trim($params['goods_name']);
            $qfs[] = "(goods_name:$goods_name)";
        }
        if(isset($params['sale_type'])) {
            $sale_type = trim($params['sale_type']);
            $qfs[] = "(sale_type:$sale_type)";
        }

        if(isset($params['tags'])) {
            $tags = trim($params['tags']);
            $qfs[] = "(tags:$tags)";
        }

        if( isset($params['price']) ) {
            $_p = explode(':', $params['price']);
            if($_p[0] < $_p[1])
                $qfs[] = "( price:[".intval($_p[0])." TO ".intval($_p[1])." ] )";
            else
                $qfs[] = "( price:[".intval($_p[1])." TO ".intval($_p[0])." ] )";
        }
        //( price:[{$params['price']['min']} TO {$params['price']['max']} ] )

        if(isset($params['keyword'])) {
            $keyword = trim($params['keyword']);
            $qfs[] = '((goods_name:'.$keyword.')  OR ( cate_name:'.$keyword.' )  OR ( brand_name:'.$keyword.' ) OR  ( cate_name_1:'.$keyword.' ) OR ( cate_name_2:'.$keyword.' ) OR ( tags:' . $keyword .') OR (title_desc:'.$keyword.') )';
        }

        if(isset($params['ids'])) {
            $_ids = array();
            foreach($params['ids'] as $_id) {
                $_ids[] = "id:$_id";
            }
            $qfs[] = "(".implode(' OR ', $_ids).")";
        }

        if(!$qfs)
            $qfs[] = "*:*";

        $q = urlencode(implode(' AND ',$qfs));

        $sort = urlencode($sort);
        $search_query  = '/select?indent=on&wt=json&version=2.2&facet=on&q=' . $q . '&start='.$limit.'&rows='.$skip .'&sort='. $sort .'&fl=' . self::SEARCH_FIELDS;

        //send http request
        $ch = null;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIME_OUT);
        curl_setopt($ch, CURLOPT_URL, self::SEARCH_URL .$search_query);
        \sprite\lib\Debug::log('search_url', self::SEARCH_URL .$search_query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $str_json = curl_exec($ch);
        curl_close($ch);
        if (empty($str_json)) {
            return null;
        }

        return $str_json;
    }

    public function searchCnt($params) {
        return $this->doSearch($params, '', '', '');
    }

}