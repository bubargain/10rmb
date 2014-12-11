<?php
namespace app\dao;

use sprite\db\SqlUtil;
use app\dao\YmallDao;

class UserEventDao extends YmallDao {
	protected static $_master; // 单例的主库dao getMasterInstance();
	protected static $_slave; // 单例的从库dao getSlaveInstance();
	public function getTableName() {
		return 'ym_user_event';
	}
	public function getPKey() {
		return 'id';
	}
	
	//查看商家的交易总数
	public function getListCnt($mer_id)
	{
		
	}
	
	//
	public function findAll($where,$limit=20)
	{
		if($where)
			$sql = "select A.product_link,B.*,A.status from ym_event A left join ym_user_event B on A.event_id = B.event_id
			 where {$this->getPkeyWhere($where)}  order by ctime desc ";
		else 
			$sql = "select * from {$this->getTableName()} order by ctime desc ";
		if($limit != 0)
		{
			$sql = $sql."limit $limit";
		}
		return $this->_pdo->getRows($sql);
	}
}