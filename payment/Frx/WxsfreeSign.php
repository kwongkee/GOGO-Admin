<?php
	define('IN_MOBILE', true);
	define('PDO_DEBUG', true);
	require_once '../../framework/bootstrap.inc.php';
	require_once '../../app/common/bootstrap.app.inc.php';
	load()->app('common');
	load()->app('template');
	load()->func('diysend');
	global $_W;
	global $_GPC;
	/**  FCreditCard
	 * 聚合支付：wechat：微信,alipay：支付宝,UnionPay：银联，翼支付：bestpay
	 * 免密授权：Fwechat:微信,Falipay：支付宝,FCreditCard：信用卡,FAgro：农商行
	 * 
	 * 微信：wechat,支付宝：alipay,银联闪付：unionpay，翼支付：bestpay,无感信用卡：park  2018-04-04
	 * 
	 * 无感支付操作页面；
	 */
	//设置时区
	date_default_timezone_set('Asia/Shanghai');
	if(!empty($_POST) && isset($_POST['token'])) {
		file_put_contents('./log/getData.txt', print_r($_POST,TRUE),FILE_APPEND);	
		$sign = Sign::getInstance();
		if($_POST['token'] == 'Sign') {
			
			$res = $sign::Sign($_POST);
			if($res['status']) {
				echo json_encode(['status'=>1,'msg'=>'success']);
			} else {
				echo json_encode(['status'=>0,'msg'=>'error','info'=>$res['info']]);
			}
		}
	}else {
		echo json_encode(['status'=>0,'msg'=>'no data']);
	}
	
	
	
	class Sign
	{
		static private $_instance = null;
		
		public function __construct(){}
		static public function getInstance() {
			if(!self::$_instance instanceof Sign) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}
		
		static public function Sign($sendArr = [])
		{
			$CarNo = trim($sendArr['CarNo'],' ');
			if(!self::isLicensePlate($CarNo)) {
				return ['info'=>'车牌号码有误，请检查！','status'=>0];
			}
			
			$upData = [
				'name' 		=> $sendArr['UserName'],//用户名
				'CarNo' 	=> strtoupper($CarNo),// 车牌号
				'color' 	=> trim($sendArr['Color'],' '),// 车牌颜色
				'CertNo' 	=> trim($sendArr['CertNo'],' '),// 证件号
				'auth_status' => '1',//授权状态：0 未授权 1已授权;
				'auth_type'   => serialize([
					'wx'=>'Fwechat',//授权类型
				]),
			];
			
			$user_carno = pdo_get('parking_authorize', array('CarNo' => trim($sendArr['CarNo'],' '),'auth_status' => '1'), array('id'));
			if($user_carno) {
				return ['info'=>'该车牌号码已绑定，请重新绑定！','status'=>0];
			} else {
				
				//查询签约手机
				$user_info = pdo_get('parking_authorize', array('mobile' => trim($sendArr['Tel'],' ')), array('id','openid','mobile','CarNo'));
				if($user_info) {
					//更新用户数据到表中，签约
					$res = pdo_update('parking_authorize', $upData, array('id' => $user_info['id']));
					if($res || !empty($user_info)) {
						
						//发送用户ID，车牌号  2018-10-11
						$sendUrl = "http://shop.gogo198.cn/foll/public/?s=api/wechat/sendVioOrederTempl&user_id={$user_info['openid']}&carNo={$CarNo}";
						self::GetUrl($sendUrl);
			
						return ['info'=>'数据签约填写成功！','status'=>1];
					} else {
						return ['info'=>'数据签约失败，请重新填写！','status'=>0];
					}
				} else {
					return ['info'=>'你尚未注册，请前往注册！','status'=>0];
				}
				
			}
		}
		
		//Curl Get请求
		static  function GetUrl($url) {
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
		
		static public function isLicensePlate($str) {
			$regular = "/^(([京津沪渝冀豫云辽黑湘皖鲁新苏浙赣鄂桂甘晋蒙陕吉闽贵粤青藏川宁琼使领][A-Z](([0-9]{5}[DF])|([DF]([A-HJ-NP-Z0-9])[0-9]{4})))|([京津沪渝冀豫云辽黑湘皖鲁新苏浙赣鄂桂甘晋蒙陕吉闽贵粤青藏川宁琼使领][A-Z][A-HJ-NP-Z0-9]{4}[A-HJ-NP-Z0-9挂学警港澳使领]))$/u";
		    if (preg_match($regular,$str)) {
		        return true;
		    } else {
		    	return false;
		    }
		}
		
	}
?>