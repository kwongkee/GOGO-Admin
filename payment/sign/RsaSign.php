<?php
	//顺德农商银行签约加密
define('IN_MOBILE', true);
define('PDO_DEBUG', true);
require_once '../../framework/bootstrap.inc.php';
require_once '../../app/common/bootstrap.app.inc.php';
require_once "../Ras/Rsa.php";
load()->app('common');
load()->app('template');
load()->func('diysend');
global $_W;
global $_GPC;
	
//设置时区
date_default_timezone_set('Asia/Shanghai');
if(isset($_GPC['token']))
{	
	$gpc = $_GPC;
	file_put_contents('./logs/RsaSingstring1.txt', json_encode($gpc)."\r\n",FILE_APPEND);
	unset($gpc);
	//分支判断
	switch($_GPC['token']) 
	{
		case 'Sign':
			//查询签约手机
			$user_info = pdo_get('parking_authorize', array('mobile' => trim($_GPC['Tel'],' '),'auth_status' => 0), array('id','openid','mobile','auth_type','unique_id'));
			//数据域
			$Datas = $data = [
				'BkEntrNo'		=>'04000000050',//单位编号
				'BkAcctNo'		=>'801101000927634235',//委托单位账号  801101000588712434  801101000961488367 801101000497298668
				'BkType1'		=>'114021',//中间业务代码
				'BkAcctNo1'		=>$user_info['unique_id'],//客户编号	发起方唯一健值  客户注册手机号
				//'BkAcctNo1'=>'13676207400',//客户编号	发起方唯一健值  客户注册手机号
				'BkAcctName1'	=>$_GPC['UserName'],//客户姓名
				'BkCertType'	=>'601',//$_GPC['CertType'],//证件类型
				'BkCertNo'		=>$_GPC['CertNo'],//证件号码
				'BkAcctNo2'		=>$_GPC['CardNo'],//银行卡号
				'BkMobPho'		=>$_GPC['Phone'],//手机号码 Phone//签约手机号		
				'Bk255str1'		=>$_W['openid']?$_W['openid']:'1',//预留1
				//'Bk255str2'=>'',//预留2
				// 2018-04-19  现地址
				//'BkCallUrl'=>'http://172.18.68.73/payment/Ras/notify.php',//通知回调地址  http://shop.gogo198.cn/payment/Ras/notify.php
				//'BackUrl'=>'http://172.18.68.73/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.auth',//qian tai hui diao
				//原地址；
				'BkCallUrl'=>'http://shop.gogo198.cn/payment/Ras/notify.php',//通知回调地址  http://shop.gogo198.cn/payment/Ras/notify.php
				//'BackUrl'=>'http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.auth',//qian tai hui diao
				//跳转停车输码页面   农商行用于返回用户页面
				//'BackUrl'=> 'http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.info',
				'BackUrl'  => 'http://shop.gogo198.cn/payment/sign/Togrands.php', 
			];
			
			//$public_key = file_get_contents("rsa_public_key.pem");
			file_put_contents('./logs/RsaSingstringData1.txt',json_encode($Datas)."\r\n",FILE_APPEND);
			
			/**
			 * 开发步骤：
			 * 1.判断ims_parking_authorize 表中是否存在用户手机号（无不能授权）
			 * 2.组装数据（按手机号更新到表中）
			 * 3、授权成功，状态改为1;
			 */
			//$auth = unserialize($user_info['auth_type']);
			if(!empty($user_info)) 
			{
				//$auth = unserialize($user_info['auth_type']);
				$upData = [
					'credit_accout' => trim($_GPC['CardNo'],' '),		//信用卡号
					'CarNo' 		=> strtoupper(trim($_GPC['CarNo'])),//车牌号
					'color' 		=> trim($_GPC['Color'],' '),		//车牌颜色
					'name' 			=> trim($_GPC['UserName'],' '),		//姓名；
					'CertNo' 		=> trim($_GPC['CertNo'],' '),		//证件号
					'auth_status' 	=> '0',								//授权状态：0 未授权 1已授权;
					'auth_type' 	=> serialize([
						'sd'=>'FAgro',
					]),
				];				
				
				try{
					pdo_begin();//开启事务
						//更新用户数据到表中，签约  授权成功修改auth_status 状态为1  parking_authorize
						pdo_update('parking_authorize', $upData, array('id' => $user_info['id']));
					pdo_commit();//提交事务
					
				}catch(PDOException $e) {
					pdo_rollback();//执行失败，事务回滚
				}
				
				$rsa = new RSA("rsa_public_key.pem");
				//数组数据转URL  进行加密;
				$sign = $rsa->encrypt(http_build_query($data));
				unset($Datas);
				$resData = [
					'msg'=>'error',
					'sign'=>$sign,
				];
				echo json_encode($resData);
				
			} else {//已签约；
				$resData = [
					'msg'=>'success'
				];
				echo json_encode($resData);
			}
		break;
	}
	
}else {
	
	$resData = [
		'error'=>'NoData',
	];
	
	echo json_encode($resData);
}


function postCurl($sign)
{
	//初始化
	$curl = curl_init();
	//设置捉取
	curl_setopt($curl,CURLOPT_URL,"http://192.168.251.10/ajax.php/ParkingPay/subscribe");
	//设置头文件的信息作为数据流输出
	curl_setopt($curl, CURLOPT_HEADER, 1);
	//设置获取的信息以文件流的形式返回，而不是直接输出。
	curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
	//设置post方式提交
	curl_setopt($curl, CURLOPT_POST, 1);
	 //设置post数据
	$post_data = array(
        "forward" => $sign);
     curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
	//执行命令
	$data = curl_exec($curl);
	//关闭URL请求
	curl_close($curl);
	//显示获得的数据
//	print_r($data);
	return $data;
}

function getCurl($sign)
{
	//初始化
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, 'http://192.168.251.10/ajax.php/ParkingPay/subscribe?forward='.$sign);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 1);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER,0);
    //执行命令
    $data = curl_exec($curl);
    //关闭URL请求
    curl_close($curl);
    //显示获得的数据
    print_r($data);
}

?>