<?php
header( 'Content-Type:text/html;charset=utf-8 ');
// 模块LTD提供
error_reporting(0);
define('IN_MOBILE', true);
require_once '../../../../framework/bootstrap.inc.php';
require_once '../../../../addons/sz_yi/defines.php';
require_once '.\./../../../addons/sz_yi/core/inc/functions.php';
require_once '../../../../addons/sz_yi/core/inc/plugin/plugin_model.php';
global $_W;

echo $_W['container'].'<br>';
echo $_W['OS'];


$inData = file_get_contents("php://input");

var_dump($inData);

// 实例化对象
$instan = Newpay::getInstance();

$instan->getInfo('abc');
var_dump($instan);


/**
**  新生支付  2018-11-04
**/
class Newpay{
	static private $instance;
	// 构造函数
	private function __construct(){
		
	}
	// 防止克隆
	private function __clone(){
		
	}
	
	// 单例模式
	static public function getInstance(){
		if(!self::$instance instanceof self){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	// 测试一下
	public function getInfo($str = '') {
		echo $str;
	}
}

?>