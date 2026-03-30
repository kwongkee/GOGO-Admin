<?php
// ģ��LTD�ṩ
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;

$appkey = '897ae1e4f30ae8c7';
$from = 'CNY';
$to = $_GPC['to'];
$amount = $_GPC['price'];
$url = "https://api.jisuapi.com/exchange/convert?appkey=".$appkey."&from=".$from."&to=$to&amount=".$amount;

$ch = curl_init();//初始化curl
     curl_setopt($ch, CURLOPT_URL,$url);//抓取指定网页
     curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
	 if($type){  //判断请求协议http或https
     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);  // 从证书中检查SSL加密算法是否存在
	 }
	 curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
	 
     $result = curl_exec($ch);//运行curl
     curl_close($ch);



$jsonarr = json_decode($result, true);

if($jsonarr['status'] != 0)
{
    show_json(0, $jsonarr['msg']);
    exit();
}
 
$results = $jsonarr['result'];
show_json(1, $results);