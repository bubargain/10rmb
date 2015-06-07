<?php
/**
     * 本配置文件保存各开发者本地的配置数据
     * 在本配置文件中设置的值，将覆盖全局YOKA-ENV.php的配置，因此请勿提交svn
     * 重要： 请勿在本配置文件中增加YOKA-ENV.PHP中未定义的配置项
     * User: xwarrior
     * Date: 12-11-8
     * Time: 下午4:34
     */
/*
 * 开发人员可以对自己的配置文件中做任意修改提交 服务器使用软链接加载该文件，创建软链的脚本build.sh在/config 目录下 重要：
 * 如果要增加新的全局配置，请将配置值提交开发管理员jujianhua@yoka.com，以便放入开发服务器全局的YOKA-ENV.php中，否则会影响其他开发人员
 */

// ---------------YEPF框架级别常量覆盖定义----------------------------------
$_SERVER ['SPRITE_PATH'] = ROOT_PATH . '/lib/sprite2.0/sprite';
$_SERVER['SMS_PATH'] =ROOT_PATH . '/lib/sms';

// 是否默认打开调试模式
define ( 'YEPF_IS_DEBUG', true ); // 系统默认：'yoka-inc'
// 自定义错误级别,只有在调试模式下生效（YEPF_IS_DEBUG）
define ( 'YEPF_ERROR_LEVEL', E_ALL & ~ E_NOTICE ); // 系统默认值： E_ALL & ~E_NOTICE
                                                   
// 定义使用YEPF内置的Firebug控制台显示错误，还是将错误直接显示在页面上，默认为true,即显示在页面上
define ( 'YEPF_THROW_ERROR', true );

define ( 'YEPF_DEBUG_PASS', 'yoka-inc' );

define ('buck_merchant_level1',2);
define ('buck_merchant_level2',4);
define ('buck_merchant_level3',6);
define ('EXPRESS_ORDER_FEE',0.5);

define ('PROFITRATE',0.8);//平台佣金比例

$_SERVER['MEMCACHE_SERVER1_HOST'] = '127.0.0.1';
$_SERVER['MEMCACHE_SERVER1_PORT'] = 12111;


//主库配置
$_SERVER['mobile_mdb']['dsn'] = "mysql:host=127.0.0.1;dbname=10buck";
$_SERVER['mobile_mdb']['user'] = 'root';
$_SERVER['mobile_mdb']['password'] = '';
$_SERVER['mobile_mdb']['charset'] = 'utf8';


//从库配置
$_SERVER['mobile_sdb']['dsn'] = "mysql:host=127.0.0.1;dbname=10buck";
$_SERVER['mobile_sdb']['user'] = 'root';
$_SERVER['mobile_sdb']['password'] = '';
$_SERVER['mobile_sdb']['charset'] = 'utf8';

//address
$_SERVER ['ROOT_DOMAIN'] = 'http://www.10buck.com';
$_SERVER ['APP_SITE_URL'] = 'http://127.0.0.1';

define ( 'TOUCH_OAK', 'http://shop.10buck.com' ); //后台地址
define ( 'TOUCH_BUCK', 'http://m.10buck.com' ); //TOUCH地址
define ( 'ADMIN_ADDR', 'http://shop.10buck.com' );

define ('ALIPAY_ID','2088311424843552');
define ('SELLER_EMAIL','ceo@kitetea.com');
define('ERP_SITE_URL', 'http://127.0.0.1');
define ( 'LOG_PATH', ROOT_PATH . '/../tmp/log' );
//此项目上传文件配置
define ( 'CDN_MODE',  '/var/www/cdn/' );
define ('CDN_LINK',  'http://cdn.10buck.com');
define ( 'DEFAULT_IMAGE', '/mobile/default.jpeg' );
define ( 'APP_VERSION', '2.0.1' );

/*
 * SMS 发送接口配置
 */
$_SERVER['sms']['accountSid']='aaf98f8947d7c8680147e24caaa90dc8'; //主帐号
$_SERVER['sms']['accountToken']='fbab8047563f4b76b77166fb3fe016da';//主帐号Token
$_SERVER['sms']['appId']='8a48b55147d7c67d0147e254d0a30d32';//应用Id
$_SERVER['sms']['serverIP']='app.cloopen.com';  //请求地址，格式如下，不需要写https://
$_SERVER['sms']['serverPort']='8883';
$_SERVER['sms']['softVersion']='2013-12-26';


/*
 * mail server
 */
define("BUCK_MAIL_HOST",'contact@10buck.com');
define('BUCK_MAIL_SMTP','smtp.exmail.qq.com');
define('BUCK_MAIL_PASS','kitetea2013');
define('BUCK_MAIL_PORT',465);


