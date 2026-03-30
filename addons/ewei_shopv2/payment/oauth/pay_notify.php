<?php
	//header('Content-Type:text/html;charset=utf-8');
error_reporting(0);
define('IN_MOBILE', true);
require dirname(__FILE__) . '/../../../../framework/bootstrap.inc.php';
require IA_ROOT . '/addons/ewei_shopv2/defines.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/functions.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/plugin_model.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/com_model.php';
global $_W;
global $_GPC;

file_put_contents('./pay_code.txt', print_r($_GPC,TRUE),FILE_APPEND);

if(!empty($_GPC["code"])) {
	
	$paramss = cache_load('params');//读取缓存数据
	$params = unserialize($paramss);//反序列化数据
	
	$appid = 'wx76d541cc3e471aeb';
	$secret = '3e3d16ccb63672a059d387e43ec67c95';
    $code = $_GET["code"];
    $get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';

    $ch = curl_init();  
    curl_setopt($ch,CURLOPT_URL,$get_token_url);  
    curl_setopt($ch,CURLOPT_HEADER,0);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );  
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);  
    $res = curl_exec($ch);  
    curl_close($ch);  
    $json_obj = json_decode($res,true);

	file_put_contents('./pay_notify.txt', print_r($json_obj,TRUE),FILE_APPEND);
		
	if(isset($json_obj['expires_in'])) {
			
		$user_data = array(
			'uniacid' =>'14',
			'realname'=>'GOGO商城会员',
			'mobile'=>'13888888888',
			'createtime'=>time(),
			'nickname'=>'GOGO商城会员',
			'province'=>'广东',
			'city'=>'佛山',
			'newOpenid'=>$json_obj['openid'],
		);
		
		
		$package = [
			'fee'=>$params['fee'],//订单金额
			'tid'=>'RN'.date('Y-m-dHis',time()).randS(),//订单编号
			'title'=>$params['title'],//订单描述
			'openid'=>$json_obj['openid'],
			'createtime'=>time(),//创建时间
			'pay_type'=>'wechat',
		];
		
		$config = [
			'mchid'=>'101540254006',//商户号
			'key'=>'f8ee27742a68418da52de4fca59b999e',//秘钥
		];
		
		$res = pdo_insert('pay_money',$package);
		
		if(!empty($res)) {
			
			$uid = pdo_insertid();
			//请求支付微信支付
			$tgwechat_public = m('common')->pay_wechat($package, $config);
			
			file_put_contents('./pay_wechat.txt', print_r($tgwechat_public,TRUE),FILE_APPEND);
			
			if($tgwechat_public['status'] == '100' && $tgwechat_public['message'] == '获取成功') {	
												
				if(isset($tgwechat_public['pay_url'])) {
					//清除缓存
					cache_delete('params');
					//返回支付链接
					$codeurl = $tgwechat_public['pay_url'];
				}						
			}
			
//			echo $codeurl;
			//返回支付链接直接跳转
			header("Location:".$codeurl);
		}
	}
}


function randS()
{
	$ychar="0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z";
	$list=explode(",",$ychar);
	
	for($i=0;$i<6;$i++){
		
		$randnum=rand(0,35); // 10+26;
		
		$authnum.=$list[$randnum];
	}
	
	return $authnum;
}



?>