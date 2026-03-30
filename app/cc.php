<?php

require '../framework/bootstrap.inc.php';
load()->app('common');
load()->model('app');
require IA_ROOT . '/app/common/bootstrap.app.inc.php';
global $_W;
global $_GPC;


define('APPID',        "wx56acdff97cef4e2b");
define('APPSECRET',    "890b65d1eaac88349f3d033c31f10f93");

$code  = isset($_GET['code'])   ? trim($_GET['code'])  : '';
$state = isset($_GET['state'])  ? trim($_GET['state']) : '';

$accurl = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".APPID."&secret=".APPSECRET."&code=".$code."&grant_type=authorization_code";
// 获取数据
$access = file_get_contents($accurl);
// 解析数据
$accessArr = json_decode($access,true);
print_r($accessArr);
if(isset($accessArr['openid'])) {
	$info = pdo_fetch('select id,mobile,openid from ' . tablename('sz_yi_member') . ' where  openid=:openid limit 1', array(':openid'=>$accessArr['openid']));
	if (empty($info)) {
		die("用户不存在");
	}
	$result = [];
	$result["code"] = 1;
	$result["msg"] = "success";
	$result["data"] = $info;
	$loginUrl = "http://declare.gogo198.cn/mobile/wxlogin?openid=".$info["openid"];
	echo json_encode($result);
	// $lifeTime = 24 * 3600 * 3;
    // $cookieid = '__cookie_sz_yi_userid_' . $_W['uniacid'];
    // session_set_cookie_params($lifeTime);
    // setcookie($cookieid, base64_encode($info['openid']));
    // $myUser = "";
    // session_set_cookie_params($myUser);
	// $loginUrl="http://shop.gogo198.cn/foll/public/?s=admin/index";
	// $loginUrl="https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=shop&m=sz_yi";
	header("Location:".$loginUrl);
	// Array
// (
//     [id] => 6
//     [uniacid] => 14
//     [openid] => 0
//     [tel] => 18029291779
//     [username] => 李诗棋
//     [role] => 1
//     [pid] => 0
//     [create_time] => 2018-05-28 16:29:38
//     [user_status] => 1
// )
function postWxlogin($url,$post_data=[]) {
    //初始化
    $curl = curl_init();
    //设置捉取Url
    curl_setopt($curl,CURLOPT_URL,$url);
    //设置头文件的信息
    curl_setopt($curl,CURLOPT_HEADER,0);
    //设置获取的信息以文件流的形式返回，而不是直接输出
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
    //设置超时
    curl_setopt($curl,CURLOPT_TIMEOUT,65);
    //设置post方式提交
    curl_setopt($curl,CURLOPT_POST,1);
    //设置post数据
    //设置请求参数
    curl_setopt($curl,CURLOPT_POSTFIELDS,$post_data);
    //执行命令  并返回结果
    $res = curl_exec($curl);
    //关闭连接
    curl_close($curl);
    //返回数据
    return $res;
}
}
?>