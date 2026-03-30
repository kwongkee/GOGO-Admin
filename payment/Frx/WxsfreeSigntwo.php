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
	if(!empty($_GPC) && isset($_GPC['token'])) {
		
		//file_put_contents('./log/getDatatwo.txt', print_r($_POST,TRUE),FILE_APPEND);
		
		$data['Tel']	=	trim($_GPC['Tel']);
		$data['UserName']=	trim($_GPC['UserName']);
		$data['CertNo']	=	trim($_GPC['CertNo']);
		$data['CarNo']	=	trim($_GPC['CarNo']);
		$data['token']	=	trim($_GPC['token']);
		$data['Color']	=	trim($_GPC['Color']);
		$data['openid']	=	trim($_GPC['openid']);
		
		$sign = Sign::getInstance();
		if($_GPC['token'] == 'Sign') {
			
			$res = $sign::Sign($data);
			switch($res['status']) {
				case 1:
					$path = 'http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.info';//签约成功
					echo json_encode(['msg'=>'success','info'=>$res['info'],'path'=>$path]);
				break;
				case 2:
					echo json_encode(['msg'=>'success','info'=>$res['info'],'path'=>($res['path']?$res['path']:'')]);
				break;
				case 3:
					echo json_encode(['msg'=>'error','info'=>$res['info']]);
				break;
				case 4:
					echo json_encode(['msg'=>'error','info'=>$res['info']]);
				break;
				case 5:
					echo json_encode(['msg'=>'error','info'=>$res['info']]);
				break;
			}

			file_put_contents('./log/getDatatwos.txt', print_r($res,TRUE),FILE_APPEND);
		}
		
	}else {
		echo json_encode(['status'=>0,'msg'=>'no data']);
	}
	
	// 签约授权
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
			$CarNo = strtoupper(trim($sendArr['CarNo']));
			if(!self::isLicensePlate($CarNo)) {
				return ['info'=>'车牌号码有误，请检查！','status'=>5];
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
			// 检测授权平台中是否存在该车牌号
			$user_carno = pdo_get('parking_authorize', array('CarNo' => trim($sendArr['CarNo'],' '),'auth_status' => '1'), array('id'));
			if($user_carno) {
				return ['info'=>'该车牌号码已绑定，请重新绑定！','status'=>4];
			} else {// 没有该车牌号绑定
                /*
                ** 请求查询用户是否签约
                */
                $sendData['Token'] = 'CheckCarNoSign';
                $sendData['inType'] = 'PARKING';
                $sendData['CarNo'] = $sendArr['CarNo'];
                $sendData['openid'] = $sendArr['openid'];
                // 查询车主状态
                $resa = self::CheckCarNo($sendData);
                // 2020-06-23  11
                $ptmps = json_encode($resa);
                $m = date('Ym',time());
                file_put_contents('./log/CarNoSign'.$m.'.log', $ptmps."\r\n",FILE_APPEND);

                //查询签约手机
                $user_info = pdo_get('parking_authorize', array('mobile' => trim($sendArr['Tel'], ' ')), array('id', 'openid', 'mobile', 'CarNo'));
                if ($user_info) {
                    /*
                    *	状态：NORMAL 正常
                              PAUSED 暂停
                              OVERDUE 已开通，但欠费
                              UNAUTHORIZED  未授权	452424199003141634
                    */
                    if (!empty($resa['path']) && $resa['userState'] == 'UNAUTHORIZED') {// 未授权

                        //更新用户数据到表中，签约
                        pdo_update('parking_authorize', $upData, array('id' => $user_info['id']));

                        //发送用户ID，车牌号  2018-10-11
                        $sendUrl = "http://shop.gogo198.cn/foll/public/?s=api/wechat/sendVioOrederTempl&user_id={$user_info['openid']}&carNo={$CarNo}";
                        self::GetUrl($sendUrl);

                        return ['info' => '正在跳转签约链接请耐心等待', 'status' => 2, 'path' => $resa['path']];

                    } else if($resa['userState'] == 'NORMAL' && $resa['path'] != '') {
                        // 2020-04-09
                        //更新用户数据到表中，签约
                        pdo_update('parking_authorize', $upData, array('id' => $user_info['id']));

                        //发送用户ID，车牌号  2018-10-11
                        $sendUrl = "http://shop.gogo198.cn/foll/public/?s=api/wechat/sendVioOrederTempl&user_id={$user_info['openid']}&carNo={$CarNo}";
                        self::GetUrl($sendUrl);
                        return ['info' => '签约成功,正在跳转停车页面!', 'status' => 2,'path' => $resa['path']];

                    } else if ($resa['userState'] == 'NORMAL' && $resa['path'] == '') {
                        //更新用户数据到表中，签约
                        pdo_update('parking_authorize', $upData, array('id' => $user_info['id']));

                        //发送用户ID，车牌号  2018-10-11
                        $sendUrl = "http://shop.gogo198.cn/foll/public/?s=api/wechat/sendVioOrederTempl&user_id={$user_info['openid']}&carNo={$CarNo}";
                        self::GetUrl($sendUrl);
                        return ['info' => '签约成功,正在跳转停车页面!', 'status' => 1];
                    }

                } else {
                    return ['info' => '你尚未注册，请前往注册！', 'status' => 3];
                }
            }
			
		}
		
		// 查询车主签约服务状态
		static function CheckCarNo($sendData) {
			$url = 'http://shop.gogo198.cn/payment/Frx/Frx.php';
			$curlpost = new Curl;//实例化
			$res = $curlpost->post($url,$sendData);
			//$res = self::PostData($url,$sendData);
			// 返回数据
			$res = json_decode($res->response,true);
			return $res;
		}
		
		// POST 请求数据
		static function PostData($url,&$data){
			// 初始化
			$curl = curl_init();
			// 设置捉取的url
			curl_setopt($curl,CURLOPT_URL,$url);
			// 设置头文件的信息作为数据输出流
			curl_setopt($curl,CURLOPT_HEADER,1);
			// 设置后去的信息以文件流的形式返回，而不是直接输出
			curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
			// 设置post方式提交
			curl_setopt($curl,CURLOPT_POST,1);
			// 设置post数据
			//$post_data = $data;
			curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
			// 执行命令
			$res = curl_exec($curl);
			// 关闭请求
			curl_close($curl);
			// 返回数据
			return json_decode($res,true);
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