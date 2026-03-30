<?php
// 无感免密：签约银行卡服务 异步回调
define('IN_MOBILE', true);
require '../../framework/bootstrap.inc.php';
require '../../app/common/bootstrap.app.inc.php';
load()->app('common');
load()->app('template');
load()->func('diysend');

if (!empty($_POST)) {

	//解析XML数据变成对象
	$obj = isimplexml_load_string($_POST['PACKET'], 'SimpleXMLElement', LIBXML_NOCDATA);
	//XML对象转换成Json格式，把Json格式转换成数组。
	$data = json_decode(json_encode($obj), true);
	//2018-07-13
	$data['time'] = date('Y-m-d H:i:s',time());
	file_put_contents('./logs/getSign.txt', print_r($data,TRUE),FILE_APPEND);
	//查询授权表数据
	$user_info = pdo_get('parking_authorize', array('mobile' => $data['Message']['Plain']['Tel']), array('id','uniacid','openid','credit_accout','mobile','CarNo'));
	//判断当前订单状态。
	if($data['Message']['Plain']['Result'] == '00') {
		
		try{
			pdo_begin();//开启事务
				
				//授权成功修改auth_status 状态为1  parking_authorize
				$updata = [
					'auth_status' => 1,//签约状态：1签约成功，0签约失败；
					'CustId' 	  => $data['Message']['Plain']['CustId'],//签约成功返回用户唯一标识
				];
				
				pdo_update('parking_authorize', $updata, array('id' => $user_info['id']));
			
			pdo_commit();//提交事务
			
		}catch(PDOException $e){
			pdo_rollback();//执行失败，事务回滚
		}
		
		/**
		 * 2018-3-13  
		 * sendSing()
		 * 授权签约模板
		 * template_id ： 模板消息
		 * touser ： 接收信息用户openid
		 * Reurl ： 详情链接地址
		 * uniacid : 所属公众号id
		 * first : 通知结果；
		 * keyword1 ： 扣费方案
		 * keyword2 : 扣费账号
		 * keyword3 ： 扣费方式
		 * remark ：详细引导
		 */
		
		$sendArr = [
			'template_id'=>'Op42hvd9-hG57HVdIpWWMz7qYDKqhvRPyyx0XnDziTA',//模板ID
			'touser' => $user_info['openid'],//接收用户
			//'Reurl' => 'http://shop.gogo198.cn/app/index.php?i='.$user_info['uniacid'].'&c=entry&p=designer&pageid=117&m=sz_yi&do=plugin',//详情地址，跳转签约页面；
			'Reurl' => "http://shop.gogo198.cn/app/index.php?i=".$user_info['uniacid'].'&c=entry&m=ewei_shopv2&do=mobile&r=parking.auth',
			'uniacid' =>$user_info['uniacid'],//所属公众号
			'first' => '您好，您的停车扣费授权已经成功！',//成功提示
			'remark' => '您可点击详情，查询或变更您的授权',//详情提示
			'keyword1' => '银联无感支付',//扣费方案
			'keyword2' => $user_info['credit_accout'],//扣费账号
			'keyword3' => '授权免密代扣',//扣费方式
		];

	}else {
		
		$sendArr = [
			'template_id'=>'Op42hvd9-hG57HVdIpWWMz7qYDKqhvRPyyx0XnDziTA',//模板ID
			'touser' => $user_info['openid'],//接收用户
			//'Reurl' => 'http://shop.gogo198.cn/app/index.php?i='.$user_info['uniacid'].'&c=entry&p=designer&pageid=117&m=sz_yi&do=plugin',//详情地址，跳转签约页面；
			'Reurl' => "http://shop.gogo198.cn/app/index.php?i=".$user_info['uniacid'].'&c=entry&m=ewei_shopv2&do=mobile&r=parking.auth',
			'uniacid' =>$user_info['uniacid'],//所属公众号
			'first' => '您好，您的停车扣费授权失败！',//成功提示
			'remark' => '您可点击详情，继续完成授权',//详情提示
			'keyword1' => '银联无感支付',//扣费方案
			'keyword2' => $user_info['credit_accout'],//扣费账号
			'keyword3' => '授权免密代扣',//扣费方式
		];
	}

	//授权成功通知 2018-03-13
	sendSing($sendArr);	
	
	//发送用户ID，车牌号  2018-10-11
	$sendUrl = "http://shop.gogo198.cn/foll/public/?s=api/wechat/sendVioOrederTempl&user_id={$user_info['openid']}&carNo={$user_info['CarNo']}";
	GetUrl($sendUrl);
	
	echo 'success';
	
} else {
	
	echo 'error';
}

	//Curl Get请求
	function GetUrl($url) {
		//初始化
		$curl = curl_init();
		//设置捉取URL
		curl_setopt($curl,CURLOPT_URL,$url);
		//设置获取的信息以文件流的形式返回，而不是直接输出。
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		//执行命令
		$res = curl_exec($curl);
		//关闭Curl请求
		curl_close($curl);
		//print_r($res);
		return $res;
	}

?>