<?php

namespace admin\controller;

use app\dao\GcategoryDao;
use app\dao\OrderDao;
use app\dao\OrderExtmDao;
use app\dao\OrderGoodsDao;
use app\service\OrderSrv;
use app\common\util\SubPages;

class HistoryController extends BaseController {
	// 订单列表,没有做翻页
	public function index($request, $response) {
		$user_id  = $this->checkLogin();
		$response->title = '10BUCK历史快照';
		$response->storeTitle ="历史快照";
		$response->storeIntro ="查看过往活动中的完成订单记录，如需对进行中的活动进行操作请点击“交易记录”";
		$event= new \app\service\EventSrv();
		$response->events = $event->searchEventList( $this->current_user['user_id']);
		$event_id = $request->sevent;
		$response->event_id = $event_id;
		if($event_id )
		{
			$tmp=\app\dao\EventDao::getSlaveInstance()->find(
				array('mer_id'=>$this->current_user['user_id'] , 'event_id'=>$event_id)
			);
			if(!$tmp)
				$this->showError('警告！该行为可导致您被封号，有问题请联系管理员');
			$sql="select * from ym_user_event where event_id = $event_id and status in (1,4) order by id";
			$eventInfo = \app\dao\UserEventDao::getSlaveInstance()->getPdo()->getRows($sql);	
			$response->eventInfo = $eventInfo;
		}


		
		$this->layoutSmarty();
	
		
	}
}