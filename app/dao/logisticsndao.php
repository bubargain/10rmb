<?php

namespace app\dao;

class LogisticSnDao extends YmallDao {
	protected static $_master; // 单例的主库dao getMasterInstance();
	protected static $_slave; // 单例的从库dao getSlaveInstance();
	public function getTableName() {
		return 'ym_logistic_sn';
	}
	public function getPKey() {
		return 'id';
	}
	
}