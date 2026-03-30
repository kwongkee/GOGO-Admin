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
$type = isset($_GET['type'])  ? trim($_GET['type']) : '';

$accurl = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".APPID."&secret=".APPSECRET."&code=".$code."&grant_type=authorization_code";
// 获取数据
$access = file_get_contents($accurl);
// 解析数据
$accessArr = json_decode($access,true);
file_put_contents('./login_log.txt', $access);
if(isset($accessArr['openid'])) {
     $info = pdo_get('mc_mapping_fans',array('unionid'=>$accessArr["unionid"],'uniacid'=>3));
//    $info = pdo_fetch("SELECT * FROM ".tablename('mc_mapping_fans')." WHERE unionid=:unionid and uniacid=:uniacid and follow=1", array(':unionid' => $accessArr["unionid"],':uniacid' => 3));
//    if (empty($info)) {
//        echo "<div style='text-align:center;margin-bottom:10px;'><img src='https://shop.gogo198.cn/collect_website/public/uploads/centralize/website_index/64d357f7c4a70.jpg' style='width:150px;'></div><div style='text-align: center;font-size: 20px;margin-bottom:10px;'>购购网提醒您：</div><div style='font-size:1rem;text-align:center;'>用户不存在（请先使用手机/邮箱注册后关注“<a href='https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzA4MDQyMTMxMQ==&scene=110#wechat_redirect'>Gogo購購网</a>”公众号）后重试。<a href='javascript:history.back(-1);'>点击返回</a></div>";
//        return false;
//    }
    if ($type=="1") {
        // 进口微信扫码授权
        $loginUrl = "https://decl.gogo198.cn/customs/wxqrlogin?openid=".$info["openid"]."&unionid=".$info["unionid"];
    }elseif ($type=="2") {
        // 出口微信扫码授权
        $loginUrl = "https://decl.gogo198.cn/mobile/wxqrlogin?openid=".$info["openid"]."&unionid=".$info["unionid"];
    }elseif ($type=="3") {
        // 总后台微信扫码授权
        // $declinfo = pdo_get('decl_user',array('openid'=>$info["openid"]));
//        $declinfo = pdo_fetch("SELECT * FROM ".tablename('decl_user')." WHERE openid=:openid", array(':openid' => $info["openid"]));
//        $data = json_decode(json_encode($declinfo),true);
        $data = pdo_fetch("SELECT * FROM ".tablename('website_user')." WHERE openid=:openid", array(':openid' => $info["openid"]));
        $loginUrl = "https://shop.gogo198.cn/collect_website/public/?s=admin/wxqrlogin&openid=".$info["openid"]."&type=mobile&phone=".$data["phone"];
    }elseif ($type=="4") {
        // 供应商微信扫码授权
        $loginUrl = "https://shop.gogo198.cn/app/index.php?c=entry&do=shop&m=sz_yi&p=login&op=wxlogin&openid=".$info["openid"];
    }elseif($type=="5"){
        #商户中心微信扫码授权
        $loginUrl = "https://decl.gogo198.cn/mobile/wxqrlogin_centralizer?openid=".$info["openid"]."&unionid=".$info["unionid"];
    }elseif($type=="6"){
        #官网微信扫码登录
        $loginUrl = "https://www.gogo198.net/?s=gather/getminiprogramcode&pa=3&type=".$type."&openid=".$accessArr['openid']."&unionid=".$accessArr["unionid"];
    }elseif($type=="7"){
        #医讯网微信扫码登录
        $loginUrl = "https://www.gogo198.net/?s=gather/getminiprogramcode&pa=3&type=".$type."&openid=".$info["openid"]."&unionid=".$info["unionid"];
    }
	header("Location:".$loginUrl);
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