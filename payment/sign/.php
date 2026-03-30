<?php
// 签约银行卡服务 异步回调
define('IN_MOBILE', true);
require '../../framework/bootstrap.inc.php';
require '../../app/common/bootstrap.app.inc.php';
load()->app('common');
load()->app('template');
load()->func('diysend');

file_put_contents('./logs/kfnotify1.txt', print_r($_POST,TRUE),FILE_APPEND);

if (!empty($_POST)) {
	//解析XML数据变成对象
	$obj = isimplexml_load_string($_POST['PACKET'], 'SimpleXMLElement', LIBXML_NOCDATA);
	//XML对象转换成Json格式，把Json格式转换成数组。
	$data = json_decode(json_encode($obj), true);
	//判断当前订单状态。
	if($data['Message']['Plain']['Result'] == '00') {
		try{
		
			pdo_begin();//开启事务
			
			$user_info = pdo_get('parking_authorize', array('mobile' => $data['Message']['Plain']['Tel']), array('openid', 'auth_status'));
			//授权成功修改auth_status 状态为1  parking_authorize
			$updata = [
				'auth_status' => 1,//签约状态：1签约成功，0签约失败；
				'CustId' => $data['Message']['Plain']['CustId'],//签约成功返回用户唯一标识
			];
			pdo_update('parking_authorize', $updata, array('mobile' => $user_info['mobile']));
			pdo_commit();//提交事务
			
		}catch(PDOException $e){
			pdo_rollback();//执行失败，事务回滚
		}
		
	}

	file_put_contents('./logs/kfnotify1.txt', print_r($data,TRUE),FILE_APPEND);
	
	echo 'success';
	
} else {
	
	echo 'error';
}
?>