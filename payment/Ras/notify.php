<?php
	//农商行签约回调地址
	define('IN_MOBILE', true);
	define('PDO_DEBUG', true);
	require_once '../../framework/bootstrap.inc.php';
	require_once '../../app/common/bootstrap.app.inc.php';
	require_once "Rsa.php";
	load()->app('common');
	load()->app('template');
	load()->func('diysend');
	global $_W;
	global $_GPC;
	
	$msg = 'nodata';//声明变量  提示状态变量	
	header('Content-Type:text/html;charset=utf-8');
	if(!empty($_POST) && is_array($_POST)) {
		file_put_contents('./logs/RsaSingNotify.txt', print_r($_POST,TRUE),FILE_APPEND);
		//获取get 数据;
		$getData = $_POST;
		//数据验签
		$verifys = [
			'SignedResult'=>$getData['SignedResult'],//签约结果
			'BkAcctNo1'=>$getData['BkAcctNo1'],//客户唯一编号
			'timestamp'=>$getData['timestamp'],//时间搓
			'nonce'=>$getData['nonce'],//随机字符串
		];
		
		$data = http_build_query($verifys);
		//实例化
		$rsa = new RSA("../signfile/rsa_public_key.pem");
		$ret = $rsa->verify($data,$getData['signature']);//数据验签
		if(!$ret){//验签失败
			$msg = 'verify';//验签失败
		}
		
		/**
		 * 查询表中的字段：openid,uniacid,credit_account,
		 * 条件：unique_id = $getData['BkAcctNo1']
		 */
		$filed = ['openid','uniacid','credit_accout','CarNo'];
		$user_info = pdo_get('parking_authorize',array('unique_id'=>trim($getData['BkAcctNo1'])),$filed);
		
		$number = trim($user_info['credit_accout']);
		$str    = substr($number,-8,-4);
		$account = str_replace($str,'伦教停车',$number);
		
		if($getData['SignedResult'] == 1) {//签约成功
			
			try{
				pdo_begin();//开启事务
				
				//授权成功修改auth_status 状态为1  parking_authorize
				$updata = [
					'auth_status' => 1,//签约状态：1签约成功，0签约失败；
					'CustId' 	  => $getData['BkAcctNo1'],//签约成功返回用户唯一标识
				];
//				pdo_update('parking_authorize', $updata, array('mobile' => $getData['BkAcctNo1']));
				//2018-04-19
				pdo_update('parking_authorize', $updata, array('unique_id' => $getData['BkAcctNo1']));  
    
				pdo_commit();//提交事务
				
			}catch(PDOException $e){
				pdo_rollback();//执行失败，事务回滚
			}
			//发送消息给前端通知；
			postCredit($number);
			
			//发送用户ID，车牌号  2018-10-11
			$sendUrl = "http://shop.gogo198.cn/foll/public/?s=api/wechat/sendVioOrederTempl&user_id={$user_info['openid']}&carNo={$user_info['CarNo']}";
			GetUrl($sendUrl);
			
			$sendArr = [
				'template_id'=>'Op42hvd9-hG57HVdIpWWMz7qYDKqhvRPyyx0XnDziTA',//模板ID
				'touser' => $user_info['openid'],//接收用户
				'Reurl' => "http://shop.gogo198.cn/app/index.php?i={$user_info['uniacid']}&c=entry&m=ewei_shopv2&do=mobile&r=parking.auth",
				'uniacid' =>$user_info['uniacid'],//所属公众号
				'first' => '您好，您的停车扣费授权已经成功！',//成功提示
				'remark' => '您可点击详情，查询或变更您的授权',//详情提示
				'keyword1' => '农商行免密支付',//扣费方案
				'keyword2' => $account,//扣费账号
				'keyword3' => '授权免密代扣',//扣费方式
			];
			
			$msg = 'signok';//签约成功
			
		}else{//签约失败
			
			$sendArr = [
				'template_id'=>'Op42hvd9-hG57HVdIpWWMz7qYDKqhvRPyyx0XnDziTA',//模板ID
				'touser' => $user_info['openid'],//接收用户
				'Reurl' => "http://shop.gogo198.cn/app/index.php?i={$user_info['uniacid']}&c=entry&m=ewei_shopv2&do=mobile&r=parking.auth",
				'uniacid' =>$user_info['uniacid'],//所属公众号
				'first' => '您好，您的停车扣费授权失败！',//成功提示
				'remark' => '您可点击详情，继续完成授权',//详情提示
				'keyword1' => '农商行免密支付',//扣费方案
				'keyword2' => $account,//扣费账号
				'keyword3' => '授权免密代扣',//扣费方式
			];
			$msg = 'signno';//签约失败
		}
		
		//授权成功通知 2018-04-20
		sendSing($sendArr);	
		
	}else{//没有接收到数据值
		$msg = 'nodata';//签约失败
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
	
	function postCredit($credit) {
		$postData = [
			'credit_accout'=>$credit
		];
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "http://shop.gogo198.cn/foll/public/?s=api/pullParkingCardBindStatusApi",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => json_encode($postData),
		  CURLOPT_HTTPHEADER => array(
		    "Cache-Control: no-cache",
		    "Content-Type: application/json",
		  ),
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
	}
	
?>

<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<title>顺德农商行签约结果通知</title>
	<style type="text/css">
		.content {
			width: 100%;
			height: 25px;
			margin: 5px auto;
		}
		.msg {
			width: 100%;
			display: inline-block;
			height: 18px;
			font-size: 55px;
			font-weight: bold;
			text-align: center;
			margin-Top:35%;
		}
		.sp {
			width: 100%;
			display: inline-block;
			height: 18px;
			font-size: 35px;
			font-weight: bold;
			text-align: center;
			margin-Top:15%;
			color: darkgrey;
		}
	</style>
</head>
<body>
	
	<div class="content">
		<?php switch($msg){ case 'verify':  ?>
				<p class="msg" style="color:red;">验签失败</p>
		<?php	break;?>			
		<?php	case 'signok':	?>	
				<p class="msg" style="color:green;">签约成功</p>
		<?php	break;?>			
		<?php	case 'signno':	?>
				<p class="msg" style="color:red;">签约失败</p>
		<?php	break;?>			
		<?php	case 'nodata':	?>
				<p class="msg" style="color:red;">无效数据</p>
		<?php	break;}?>
		
		<span id="sp" class="sp"></span>
		<input type="hidden" name="urlType" id="urlType" value="<?php echo $msg?>" />	
	</div>
	
</body>
<script type="text/javascript" src="../sign/js/jquery.min.js"></script>

<script type="text/javascript">
	onload = function(){
		setInterval(go, 1000); 		
	}
	var x = 4;
	var urlType = document.getElementById('urlType').value;	
	function go(){
		x--;
		if(x > 0){
			document.getElementById('sp').innerHTML = x+'秒后跳转';
		}else {			
			if(urlType == 'signok'){
				
//				window.location.href = "http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.myparking";
				window.location.href = "http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.info";
			}else {
				window.location.href = "http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.auth";
			}
		}		
	}
	
</script>

</html>