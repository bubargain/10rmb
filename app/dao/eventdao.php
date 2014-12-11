<?php 
namespace app\dao;
use sprite\db\SqlUtil;
use app\dao\YmallDao;

class EventDao extends YmallDao {
	protected static $_master;
	protected static $_slave;
	public function getTableName() {
		return 'ym_event';
	}
	public function getPKey() {
		return 'event_id';
	}
	
/*
	 * 寻找所有匹配记录
	 * $where : 查询条件
	 * $start:  起始页
	 * $numberPerTime: 每次取得个数
	 */
	public function findALL($where,$limit=20)
	{
		if($where)
			$sql = "select * from {$this->getTableName()} where {$this->getPkeyWhere($where)}  order by event_id desc ";
		else 
			$sql = "select * from {$this->getTableName()} order by event_id desc ";
		if($limit != 0)
		{
			$sql = $sql."limit $limit";
		}
		return $this->_pdo->getRows($sql);
	}
}