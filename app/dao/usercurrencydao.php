<?php
namespace app\dao;

use sprite\db\SqlUtil;
use app\dao\YmallDao;

class UserCurrencyDao extends YmallDao {
	protected static $_master;
	protected static $_slave;
	public function getTableName() {
		return 'ym_user_currency';
	}
	public function getPKey() {
		return 'id';
	}

  
}