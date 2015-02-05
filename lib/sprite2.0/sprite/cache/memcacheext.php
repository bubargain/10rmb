<?php
namespace sprite\cache;

use \Memcached;
use \sprite\lib\Debug;

/**
 * 扩展了的pdo
 *
 */
class Memcacheext extends \Memcached {
    private $_hosts = array();
    public function addServers($list) {
        foreach($list as $row) {
            $this->_hosts[] = $row['host'];
            parent::addServer($row['host'], $row['port']);
            if($row['username'] )//设置OCS帐号密码进行鉴权
			{
				parent::setOption(Memcached::OPT_COMPRESSION, false); //关闭压缩功能
				parent::setOption(Memcached::OPT_BINARY_PROTOCOL, true);//使用binary二进制协议
            	parent::setSaslAuthData($row['username'], $row['pass']); 
            }
            
        }
    }

    public function set($key, $var, $flag = MEMCACHE_COMPRESSED, $expire = 30) {
        $begin_microtime = Debug::getTime();
        $ret = parent::set($key, $var, $expire);
        Debug::cache($this->_hosts, $key, Debug::getTime() - $begin_microtime, $ret);
        return $ret;
    }

    public function get($keys) {
        $begin_microtime = Debug::getTime();
        $ret = parent::get($keys);
        Debug::cache($this->_hosts, $keys, Debug::getTime() - $begin_microtime, $ret);
        return $ret;
    }
}
