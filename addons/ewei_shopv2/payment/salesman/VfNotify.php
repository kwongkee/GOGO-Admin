<?php
error_reporting(0);
define('IN_MOBILE', true);
define('IN_IA',true);
require dirname(__FILE__) . '/../../../../framework/bootstrap.inc.php';
require dirname(__FILE__) . '/../../../../framework/function/diysend.func.php';
load()->app('common');
require IA_ROOT . '/addons/ewei_shopv2/defines.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/functions.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/plugin_model.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/com_model.php';
$curlpost = new Curl;
global $_W;
global $_GPC;


date_default_timezone_set('Asia/Shanghai');

$input = file_get_contents('php://input');
//接收后台回调参数，并转换json格式
$receive = json_decode($input,TRUE);

file_put_contents('./log/vflog.txt', print_r($receive,TRUE),FILE_APPEND);
/**
   * [sign] => 4A654996F755DAC09994BFDBECBADAFB
    [payMoney] => 0.01
    [orderDesc] => 支付成功
    [state] => 0
    [upOrderId] => 9938666984466415616
    [account] => 101570223660
    [openid] => o-Rj7wKcdzw97DMkBEs_n94qcN8g
    [merchantId] => 617112200019682
    [payTime] => 2017-12-07 15:10:33
    [lowOrderId] => G99198商务号20171207300468061
   */

if (!empty($receive)) {
	
	//组装数据返回给支付平台
	$answer = array(
		'lowOrderId'=>$receive['lowOrderId'],//下游系统流水号，必须唯一
		'merchantId'=>$receive['merchantId'],//商户进件账号
		'upOrderId'=>$receive['upOrderId'],//上游流水号
	);
	
	/**
	 * 验证后台返回操作步骤：
	 * 判断支付结果；
	 * 1、按照订单号查询该笔订单的用户信息（身份证，姓名,uid）
	 * 2、根据查到的用户信息，请求查询身份证：
	 * 3、查询成功，更新验证状态；
	 */
	
	//如果返回的数据，支付状态为：0 并且：orderDesc 订单描述= 支付成功
	if ($receive['state'] == 0 && $receive['orderDesc'] == '支付成功') {//支付成功
			
			//获取订单表 foll_vfold 中的 uid:用户id,username:验证人名称;idcard：身份证号码
			$vfold = pdo_get('foll_vfold',array('tid' => $receive['lowOrderId']),array('uid','username','idcard','fee','title','openid','uniacid'));
			if(!empty($vfold)) {
				
				/**
				 * 请求验证身份证
				 * 1、组装数据
				 * 2、查询数据
				 * 3、查询数据成功，修改验证状态；
				 */
				//查询数据； 判断名称，身份证是否为空；
				
				if(!empty($vfold['idcard']) && !empty($vfold['username'])) {
					
					$idCard = $vfold['idcard'];//身份证号码
					$realname = $vfold['username'];//姓名；
					//查询验证拓展员信息；
				    $host = "http://idcardpho.market.alicloudapi.com";
				    $path = "/idcardinfo/photo";
				    $method = "GET";
				    $appcode = "504fd5f6a735437c97cd117e61cb4a24";
				    $headers = array();
				    array_push($headers, "Authorization:APPCODE " . $appcode);
				    $querys = "idcard=$idCard&realname=$realname";
				    $bodys = "";
				    $url = $host . $path . "?" . $querys;
				
				    $curl = curl_init();
				    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
				    curl_setopt($curl, CURLOPT_URL, $url);
				    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
				    curl_setopt($curl, CURLOPT_FAILONERROR, false);
				    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				    curl_setopt($curl, CURLOPT_HEADER, false);
				    if (1 == strpos("$".$host, "https://"))
				    {
				        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
				    }
					$res = curl_exec($curl);
					//数据转换json对象
					$rData = json_decode($res,true);
					
					file_put_contents('./log/RidCard.txt', print_r($rData,TRUE),FILE_APPEND);
					
					if($rData['data']['code'] == '1000') {//查询成功
						
						//是否接收到回调  SUCCESS表示成功  发送消息模板；
						
						$sendArr = [
							'uid' =>   $vfold['uid'],//验证用户ID
							'uName' => $vfold['username'],//验证名称
							'code' => $rData['data']['code'],//验证状态
							'msg' => $rData['data']['message'],//验证有效性
							'sex' => $rData['idCardInfo']['sex'],//性别
							'brithday' => $rData['idCardInfo']['birthday'],//生日
							'area' => $rData['idCardInfo']['area'],//所属地区
						];
						
						$json = json_encode($sendArr);
						$key = md5($json);
						cache_write($key, $json);
						
						//数组生成 URL-encode
//						$urlParams = http_build_query($sendArr);
						//跳转地址
						$Reurl = "http://shop.gogo198.cn/app/index.php?i=3&c=entry&m=ewei_shopv2&do=mobile&r=money.salesman.ReurlIdcard&key=".base64_encode($key);
						
						$msgArr = array(
							'Reurl' => $Reurl,//详细链接url
							'first' => '您好，您的身份实名验证通过',//开头
							'template_id' => 'WcvDClChgUbLfWHu5jQw5c7hYLVrpiN8FrRreRB6gzI',//模板id
							'touser' => $vfold['openid'],//接收消息的用户
							'fee' =>$vfold['fee'],//交易金额
							'uniacid' =>$vfold['uniacid'],//公众号ID
							'xmmc' => $vfold['title'],//商品描述
							'time' => date('Y-m-d H:i:s',time()),//消费时间
							'remark' => '请点击详情，确认验证信息',//详细
						);
						
					}else {//查询失败；
						
						$msgArr = array(
							'Reurl' => '',//详细链接url
							'first' => '您好，您的身份实名验证不通过',//开头
							'template_id' => 'WcvDClChgUbLfWHu5jQw5c7hYLVrpiN8FrRreRB6gzI',//模板id
							'touser' => $vfold['openid'],//接收消息的用户
							'fee' =>$vfold['fee'],//交易金额
							'uniacid' =>$vfold['uniacid'],//公众号ID
							'xmmc' => $vfold['title'],//商品描述
							'time' => date('Y-m-d H:i:s',time()),//消费时间
							'remark' => '请检查您的身份信息或联系管理员',//详细
						);
						//发送消息模板
//						sendVFmsg($msgArr);
						
					}
					
					//发送消息模板
					sendVFmsg($msgArr);
					
				}
				
				//是否接收到回调  SUCCESS表示成功
				try {
					
					pdo_begin();//开启事务
		
					//拓展员验证信息表；支付状态；pay_status == 1 支付成功！
					pdo_update('foll_verified_salesman', array('pay_status'=>1), array('uid' => $vfold['uid']));
					
					pdo_update('foll_vfold', array('payStatus'=>100), array('tid' => $receive['lowOrderId']));
					
					pdo_commit();//提交事务
					
				}catch(PDOException $e){
					pdo_rollback();//执行失败，事务回滚
				}
				
			}
		
		$answer['finished'] = 'SUCCESS';

	} else if ($receive['state'] == 1) {//支付失败
		
		$answer['finished'] = 'FAIL';
	}
		
	//拼接字串
	$str = tostring($answer);
	
	//字符串拼接加密
	$str .= '&key=f8ee27742a68418da52de4fca59b999e';
	$answer['sign'] = strtoupper(md5($str));
	file_put_contents('./log/vfjsons.txt', print_r($answer,TRUE),FILE_APPEND);
	//将数据转换成json数据返回
	echo json_encode($answer);
	
} else {
	$answer['finished'] = 'FAIL';
	echo json_encode($answer);
}

/**
 * 字符串拼接
 */
function tostring($arrs) {
	ksort($arrs, SORT_STRING);
	$str = '';
	foreach ($arrs as $key => $v ) {
		if (empty($v)) {
			continue;
		}
		$str .= $key . '=' . $v . '&';
	}
	$str = trim($str,'&');
	return $str;
}

?>