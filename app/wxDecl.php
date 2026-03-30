<?php

require '../framework/bootstrap.inc.php';
load()->app('common');
load()->model('app');
require IA_ROOT . '/app/common/bootstrap.app.inc.php';
global $_W;
global $_GPC;


define('APPID',        "wx56acdff97cef4e2b");
define('APPSECRET',    "890b65d1eaac88349f3d033c31f10f93");

$log = './auth.json';
$data = json_encode($_GET);
file_put_contents($log,$data);

$code  = isset($_GET['code'])   ? trim($_GET['code'])  : '';
$state = isset($_GET['state'])  ? trim($_GET['state']) : '';


// 获取 access_token
// $access_token = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.APPID.'&secret='.APPSECRET.'&code='.$code.'&grant_type=authorization_code';
// 获取数据
// $access = file_get_contents($access_token);
// 解析数据
// $accessArr = json_decode($access,true);
// //$accessArr['expires_time'] = (time() + 3600);
$jsonFile = './access_token.json';
$access = file_get_contents($jsonFile);
$accessArr = json_decode($access,true);
// @file_put_contents($jsonFile,$access);

/*$jsonFile = './access_token.json';
$access = file_get_contents($jsonFile);*/
// 解析成为数组
//$accessArr = json_decode($access,true);
/*if(isset($accessArr['expires_time']) && (time() > $accessArr['expires_time'])) {
    // 数据过期需要重新获取 access_token
    $access_token = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.APPID.'&secret='.APPSECRET.'&code='.$code.'&grant_type=authorization_code';
    // 获取数据
    $access    = file_get_contents($access_token);

    $accessArr = json_decode($access,true);
    $accessArr['expires_time'] = (time() + 3600);

    $files = json_encode($accessArr);
    @file_put_contents($jsonFile,$files);
}*/

$userInfoUri = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$accessArr['access_token'].'&openid='.$accessArr['openid'];
// 请求获取用户信息
$userInfo = file_get_contents($userInfoUri);print_r($userInfo);

$jsonInfo = './userinfo.json';
// 写入记录
file_put_contents($jsonInfo,$userInfo);

// 解析用户信息
$userInfos = json_decode($userInfo,true);
if(isset($userInfos['openid'])) {
    var_dump($userInfos['openid']);
    // 跳转登陆，先判断是否注册，已注册直接登陆，否则先注册，再登陆；
    $uniacid = isset($_GPC['__uniacid']) ? $_GPC['__uniacid'] : '3';

    $postData = [];
    $postData['info'] = $info = base64_encode($userInfo);var_dump($postData);

    $url = 'http://declare.gogo198.cn/wechatlogin';
    //$url     = 'https://shop.gogo198.cn/app/index.php?i='.$uniacid.'&c=entry&p=login&do=member&m=sz_yi&type=wxlogin';

    $login     = postWxlogin($url,$postData);
    print_r($login);
    // 解析数据
    $jsonData = json_decode($login,true);
    print_r($jsonData);
    die;
    // 跳转登陆成功；
    $loginOk = $jsonData['indexUrl'];
    // 登陆成功，跳转
    header("Location:".$loginOk);
    exit();


    /*$loginJson = json_decode($login,true);
    if(!$loginJson['status']) {
        // 登陆失败
        $loginUrl = 'https://shop.gogo198.cn/app/index.php?i='.$uniacid.'&c=entry&p=login&do=member&m=sz_yi';
        header("Location:".$loginUrl);
        exit();
    }

    // 登陆注册成功，进行跳转；
    $loginOk = 'https://shop.gogo198.cn/app/index.php?i='.$uniacid.'&c=entry&do=order&m=sz_yi';
    // 登陆成功，跳转
    header("Location:".$loginJson['result']['preurl']);
    exit();*/
}


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


?>