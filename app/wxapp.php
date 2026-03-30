<?php
require '../framework/bootstrap.inc.php';
load()->app('common');
load()->model('app');
require IA_ROOT . '/app/common/bootstrap.app.inc.php';
global $_W;
global $_GPC;


define('APPID',        "wx76d541cc3e471aeb");
define('APPSECRET',    "3e3d16ccb63672a059d387e43ec67c95");

$code  = isset($_GET['code'])   ? trim($_GET['code'])  : '';
$state = isset($_GET['state'])  ? trim($_GET['state']) : '';
$type = isset($_GET['type'])  ? trim($_GET['type']) : '';

$accurl = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".APPID."&secret=".APPSECRET."&code=".$code."&grant_type=authorization_code";
// 获取数据
$access = file_get_contents($accurl);
// 解析数据
$accessArr = json_decode($access,true);

// print_r($accessArr);
if(isset($accessArr['openid'])) {
	// $info = pdo_fetch('select id,mobile,openid from ' . tablename('sz_yi_member') . ' where  openid=:openid limit 1', array(':openid'=>$accessArr['openid']));
    if ($type=="1") {
        // 进口微信app授权
        $loginUrl = "http://declare.gogo198.cn/customs/wxlogin?openid=".$accessArr["openid"];
    }else if ($type=="2") {
        // 出口微信app授权
        $loginUrl = "http://declare.gogo198.cn/mobile/wxlogin?openid=".$accessArr["openid"];
    }else if ($type=="3") {
        // 总后台微信app授权
        $info = pdo_fetch('select id,mobile,openid from ' . tablename('sz_yi_member') . ' where  openid=:openid limit 1', array(':openid'=>$accessArr['openid']));
        if (empty($info)) {
            echo "<div style='font-size:1rem;text-align:center;'>用户不存在</div>";
            return false;
        }
        $declinfo = pdo_fetch('SELECT * FROM ' . tablename('decl_user') . ' where  openid=:openid limit 1', array(':openid'=>$info['openid']));
        // $declinfo = pdo_get('decl_user',array('openid'=>$info["openid"]));
        $data = json_decode(json_encode($declinfo),true);
        $loginUrl = "http://shop.gogo198.cn/foll/public/?s=admin/wxqrlogin&phone=".$data["user_tel"];
    }else if ($type=="4") {
        // 供应商微信扫码授权
        $loginUrl = "https://shop.gogo198.cn/app/index.php?c=entry&do=shop&m=sz_yi&p=login&op=wxlogin&openid=".$accessArr["openid"];
    }else if ($type=="5") {
        // 出口-拖车微信扫码授权
        $loginUrl = "https://decl.gogo198.cn/mobile/trailer/freight?openid=".$accessArr["openid"];
    }
	// echo json_encode($result);
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