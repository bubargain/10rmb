<?php
namespace shop\controller;

class PayController extends BaseController{
	
	
	public function index($request,$response){
		$this->checkLogin();
		if($request->status =='pay')
		{
			$where = "where status in (5,6)" ;
		}
		
		$sql = "select count(*) as no from ym_user_currency ".$where ." order by id desc ";
		$ret = \app\dao\EventDao::getSlaveInstance()->getpdo()->getRow($sql);
		$total=$ret['no'];

        $page_size = 20;
		// 当前页数
		$curPageNum = $request->page ? intval ( $request->page ) : 1;
		// url
		$url = preg_replace ( '/([?|&]page=\d+)/', '', $_SERVER ['REQUEST_URI'] );
		// 分页对象

		$page = new \app\common\util\SubPages( $url, $page_size, $total, $curPageNum );
		$limit = $page->GetLimit() ;
	
		
		$response->page = $page->GetPageHtml();

		$sql = "select A.*,B.user_name from ym_user_currency A left join ym_user B on A.user_id = B.user_id ".$where." order by A.id desc limit ".$limit;
		$ret = \app\dao\EventDao::getSlaveInstance()->getPdo()->getRows($sql);
		
		$response->tableCon = $ret;
		$response->_tag = $request->status;
		$this->layoutSmarty();
	}
	
	
	public function verify($request,$response){
		$this->checkLogin();
		if(!$this->isPost())
		{
			$id = (int)$request->id;
			$where = " where id= $id";
			$sql =  "select A.*,B.user_name from ym_user_currency A left join ym_user B on A.user_id = B.user_id ".$where;
			$ret = \app\dao\EventDao::getSlaveInstance()->getPdo()->getRow($sql); 
			if($ret)
				$response->info = $ret;
		}
		else{
			if($request->ispass == 0 )
			{
				\app\dao\UserCurrencyDao::getMasterInstance()->edit($request->id,
							array(
								'status'=>7,
								'comment' => $request->inputcomment
							)
				);
				$this->showError("success","index.php?_c=pay");
			}
			else if($request->id !=null && $request->inputPPsn != null)
			{
				$id= $request->id;
				$ppsn = $request->inputPPsn;
				$comment = $request->inputcomment;
				//订单状态修改 
				
				try{
					
					$order=\app\dao\UserCurrencyDao::getMasterInstance()->find($id);
				
					if($order['status']==5)
					{
						\app\dao\UserCurrencyDao::getMasterInstance()->beginTransaction();
						\app\dao\UserCurrencyDao::getMasterInstance()->edit($id,
							array(
								'status'=>6,
								'sn'=>$ppsn,
								'comment' => $comment
							)
						);
						if($order['unit']=='usd')
							$unit = "usd";
						else 
							$unit = 'rmb';
						$userInfo = \app\dao\UserInfoDao::getSlaveInstance()->find($order['user_id']);
						
							
						
						if($userInfo[$unit] < $order['amount'])
							$this->showError("amount don't have enough money","index.php?_c=pay");
						else {
								//用户账户扣钱
							$sql= "update ym_user_info set $unit = $unit - ".$order['amount']." where user_id = ".$order['user_id'];
							
							\app\dao\UserInfoDao::getSlaveInstance()->getPdo()->exec($sql);
						}
						
						\app\dao\UserCurrencyDao::getMasterInstance()->commit();
						$this->showError("success","index.php?_c=pay");
					}
					else {
						$this->showError("order status not right","index.php?_c=pay");
					}
				}catch(\Exception $e)
				{
					\app\dao\UserCurrencyDao::getMasterInstance()->commit();
					$this->showError($e->getMessgae(),"index.php?_c=pay");
				}
				
		
			
			}
			
		}
		$this->layoutSmarty();
	}
}