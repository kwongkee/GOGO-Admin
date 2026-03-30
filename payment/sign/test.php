<?php
require_once '../../framework/bootstrap.inc.php';
require_once '../../app/common/bootstrap.app.inc.php';
load()->app('common');
load()->app('template');
load()->func('diysend');
$curlpost = new Curl;//实例化
define('IN_IA',TRUE);
define('IN_MOBILE',TRUE);
global $_W;
global $_GPC;

//保留两位小数
//echo sprintf('%.2f',1.33);

//
//$CardNo = pdo_get('parking_authorize',array('unique_id'=>'u4293755ad07129095021523609897'),array('credit_accout'));
//var_dump($CardNo['credit_accout']);die;

	/* 无感退款
	 */
//	$data = [
//		'token' => 'Refund',
//		'ordersn'=>'G99198101570223660201805158569',
////		'Money'=> 4.22,//单元
//	];
	
	/**
	 * 退款查询
	 */
//	$data = [
//		'token' => 'RefundQuery',
//		'ordersn'=>'G99198101570223660201805158569',
//	];
	
//	$url = 'http://shop.gogo198.cn/payment/sign/Togrand.php';
//	$curlpost = new Curl;//实例化
//	$res = $curlpost->post($url,$data);
//	var_dump($res->response);
//	echo '<pre>';
//	$json = json_decode($res->response,TRUE);
//	print_r($json);
//	die;
	
	
	
//	$data = [
//		'token' => 'wechat',
//		'ordersn'=>'G99198101570223660201805158569',
//		'orderId'=>'G99198101570223660201805158568',
//	];
//	
//	$url = 'http://shop.gogo198.cn/payment/wechat/Tgpay-2.php';
//	$curlpost = new Curl;//实例化
//	$res = $curlpost->post($url,$data);
//	var_dump($res->response);
//	echo '<pre>';
//	$json = json_decode($res->response,TRUE);
//	print_r($json);
//	die;
	
	
/**
 * "{"Message":
 * {"Plain":{
 * 	"TransId":"60202",
 * "Result":{"ResultCode":"00","ResultMsg":"\u5904\u7406\u6210\u529f"},
 * "MerDate":"20180514",
 * "OutTradeNo":"G99198101570223660201805143726",
 * "OrgTradeNo":"20180514040000226876",
 * "TradeNo":"20180514050000226963",
 * "Money":"500",
 * "CheckDate":"20180514",
 * "Remark":"6228838802072005"
 * },
 * "Signature":{
 * 	"SignatureValue":"Aj26KufGlxu6g9Y14yKtSXqyzxI"
 * }
 * }
 * }"
 */



//	$data = [
//		'token' =>'Surrender',//'Surrender',FeeDeduction
//		'user' =>'Jason',
//	];
//	$url = 'http://shop.gogo198.cn/payment/agro/Fagro.php';
//	$curlpost->setHeader("Content-type","application/json");
//	$result = $curlpost->post($url,$data);
//	echo '<pre>';
//	print_r($result->response);
//	$json = json_decode($result->response,TRUE);
//	print_r($json);



//$number = "6223228802600499";
//$str = substr($number,-8,-4);
//echo $str;
//echo '<br>';
//$account = str_replace($str,'****',$number);
//echo $account;


/**
 * 签约查询
 */
//	$data = [
//		'token' =>'CheckSigns',
//		'CarNo' => '粤YGB098',
//	];
//	$url = 'http://shop.gogo198.cn/payment/sign/Togrand.php';
//	$result = $curlpost->post($url,$data);
//	$json = json_decode($result->response,true);
//	echo '<pre>';
//	print_r($json);
//	
//	echo $json['Message']['Plain']['Result']['ResultMsg'];

//	echo '<pre>';
//	print_r($result);
//	echo $result['Result']['ResultMsg'];


/*优惠券查询
	$data = [
		'token' =>'COUPON',
		'mobile' => '13702613232',
	];
	$url = 'http://shop.gogo198.cn/payment/sign/Togrand.php';
//	$curlpost->setHeader("Content-type","application/json");
	$result = $curlpost->post($url,$data);
	$json = json_decode($result->response,TRUE);
	echo '<pre>';
	print_r($json);
*/
	
	
//优惠券查询
//	$data = [
//		'token' =>'CheckSign',
//		'CarNo' => '粤YGB098',
//	];
//	$url = 'http://shop.gogo198.cn/payment/sign/Togrand.php';
//	$curlpost->setHeader("Content-type","application/json");
//	$result = $curlpost->post($url,$data);
	
//	$json = json_decode($result->response,TRUE);
//	echo '<pre>';
//	print_r($result->response);
//	echo '<pre>';
//	print_r($result);



//绑定车牌号
//	$data = [
//		'token' =>'Binding',
//		'CarNo' => '粤A88S92',//粤YGB098
//		'mobile' => '13044221462',
//		'CarNo' => '粤YGB098',//粤YGB098
//		'mobile' => '13809703680',
//	];
//	$url = 'http://shop.gogo198.cn/payment/sign/Togrand.php';
//	$result = $curlpost->post($url,$data);
//	$res = $result->response;//返回绑定结果；
//	echo '<pre>';
//	print_r($res);
	/**
	 * Array
	(
	    [TransId] => 60502
	    [Result] => Array
	        (
	            [ResultCode] => 07
	            [ResultMsg] => 用户不存在
	        )
	
	)
	 */
	 
	 
	 
//解绑车牌号
//	$data = [
//		'token' =>'Unbundling',
//		'mobile' => '13044221462',
//		'CarNo' => '粤YGB098',//粤YGB098  粤A88S92
//		'mobile' => '13809703680',// http://shop.gogo198.cn/payment/sign/test.php
//	];
//	$url = 'http://shop.gogo198.cn/payment/sign/Togrand.php';
//	$result = $curlpost->post($url,$data);
//	$res = $result->response;//返回绑定结果；
//	echo '<pre>';
//	print_r($res);
	 
	 
	 
	//绑定车牌号
//	 $data = [
//		'token' =>'Binding',
//		'token' =>'Unbundling',
//		'CarNo' => '粤A88S92',//粤YGB098
//		'mobile' => '13044221462',
//		'CarNo' => '粤YGB098',//粤YGB098
//		'mobile' => '13809703680',
//	];
//	$url = 'http://shop.gogo198.cn/payment/sign/Togrand.php';
//	$result = $curlpost->post($url,$data);
//	$res = $result->response;//返回绑定结果；
//	echo '<pre>';
//	print_r($res);




//扣费业务
/*$data = [
	'token' => 'Parks',
	
	'MerDate'=> time(),//停车场接入方交易日期：交易记录日期，如：YYYYMMDD
	
	'OutTradeNo'=>'GGPgogo198'.date('Ymd',time()).rand(1, time()),//停车场接入方交易流水号：每笔交易记录的唯一标识，由停车场系统生成维护
	
	'CarNo'=>'粤A88S92',//车牌号码：如：粤A88S92
//	'CarNo'=>'粤YGB098',//车牌号码：如：粤A88S92
	
	'InTime'=>time(),//进停车场时间：如：YYYY-MM-DDTHH:mm:ss
	
	'OutTime'=>time()+11,//出停车场时间：如：YYYY-MM-DDTHH:mm:ss
	
	'Amount'=>10,//优惠前停车费金额
	
	'PayAmount'=>9,//优惠后停车费金额：实际需要缴纳的金额
	
	'uniacid'=>'14',
];
$url = 'http://shop.gogo198.cn/payment/sign/Togrand.php';
$curlpost = new Curl;//实例化
$res = $curlpost->post($url,$data);
var_dump($res->response);*/

//
//$str = '20180427160623';
//echo $times = strtotime($str);
//
//echo '<br>';
//echo date('Y-m-d H:i:s',$times);


/** 更新配置；*/
//$data = [
//	'token' => 'upConfig',
//	'uniacid' => '14',
//	'name'=>'wx',
//	'data'=>[
//		'mchid'=>'003020051110012',
//		'openkey'=>'ZROXL6DLtFBHEsIJuutl*%dByyTT@EnL',
//	],
//];


//	$configs = pdo_get('pay_config',array('uniacid'=>'14'),array('config'));
//	
//	if(!$configs) {
//		exit(json_encode(['code'=>0,'msg'=>'配置信息不正确，请检查!']));
//	}
//	
//	$str = 'a:3:{
//		s:2:"tg";a:2:{
//			s:5:"mchid";s:12:"101570223660";
//			s:3:"key";s:32:"b4f16b4526b046c580e363fcfcd07c82";}
//			
//	s:2:"wg";a:4:{
//		s:10:"AccessCode";s:16:"7000000000000049";
//		s:8:"ParkCode";s:16:"1000000000000007";
//		s:7:"ParkNum";s:16:"PN00000700000002";
//		s:3:"key";s:32:"a98ff5ff2c424e13f630800913eaa155";
//		}
//		
//	s:2:"wx";a:2:{
//		s:5:"mchid";s:15:"000201507100239351";
//		s:7:"openkey";s:32:"da7c3ab0a510b873dd4159d1823460a9";}}';
//	
//	//反序列获取数组；
//	$config = unserialize($str);
//	echo '<pre>';
//	print_r($config['wg']);
//	
//	echo 'abc';
//	die;


/*$data = [
	'token' => 'upConfig',
	'uniacid' => '3',
	'name'=>'tg',
	'data'=>[
		'mchid'=>'101540254006',
		'key'=>'f8ee27742a68418da52de4fca59b999e',
	],
];
$url = 'http://shop.gogo198.cn/payment/sign/Togrand.php';
$curlpost = new Curl;//实例化
$res = $curlpost->post($url,$data);
var_dump($res->response);
die;*/


//a:3:{s:2:"tg";
//a:2:{s:5:"mchid";s:12:"101570223660";s:
//3:"key";s:32:"b4f16b4526b046c580e363fcfcd07c82";}
//s:2:"wg";a:4:{s:10:"AccessCode";s:16:"7000000000000049";s:8:"ParkCode";s:16:"1000000000000007";s:7:"ParkNum";s:16:"PN00000700000002";s:3:"key";s:32:"a98ff5ff2c424e13f630800913eaa155";}s:2:"wx";a:2:{s:5:"mchid";s:15:"003020051110012";s:7:"openkey";s:32:"ZROXL6DLtFBHEsIJuutl*%dByyTT@EnL";}}

/** 更新配置；*/

//$data = [
//	'token' => 'checkConfig',
//	'uniacid' => '14',
//];
//$url = 'http://shop.gogo198.cn/payment/sign/Togrand.php';
//$curlpost = new Curl;//实例化
//$res = $curlpost->post($url,$data);
//var_dump($res->response);
//die;


//	$ss = 'a:3:{s:2:"tg";a:2:{s:5:"mchid";s:12:"101570223660";s:3:"key";s:32:"b4f16b4526b046c580e363fcfcd07c82";}s:2:"wg";a:4:{s:10:"AccessCode";s:16:"7000000000000049";s:8:"ParkCode";s:16:"1000000000000007";s:7:"ParkNum";s:16:"PN00000700000002";s:3:"key";s:32:"a98ff5ff2c424e13f630800913eaa155";}s:2:"wx";a:2:{s:5:"mchid";s:15:"000201507100239351";s:7:"openkey";s:32:"da7c3ab0a510b873dd4159d1823460a9";}}';

	/*$update=[
		'tg' =>[
			'mchid'=>'101540254006',
			'key'=>'f8ee27742a68418da52de4fca59b999e',
		],*/
//		'wg' =>[
//			'AccessCode' => '7000000000000049',//接入方代码
//			'ParkCode' =>'1000000000000007',//商户代码
//			'ParkNum' => 'PN00000700000002',//停车场代码
//			'key' =>'a98ff5ff2c424e13f630800913eaa155',//秘钥
//		],
//		'wx' =>[
//			'mchid' => '000201507100239351',
//			'openkey' =>'da7c3ab0a510b873dd4159d1823460a9'
//		],
//	];
	// json。
	/*$str = json_encode($update);
	$datas = [
		'config' => $str
	];*/
	
//	$res = pdo_update('pay_config', $datas, array('uniacid' => 3));
//	if($res) {
//		echo "更新成功！";
//	}else {
//		echo '更新失败！';
//	}

	/*echo '<pre>';
	$configInfo = pdo_get('pay_config',['uniacid'=>3],['config']);
	$conf = json_decode($configInfo['config'],true);
	print_r($conf);
	echo 'end';
	die;*/
	
	
	
	$data = [
		'token'  => 'Parks',
		'CarNo'  => '粤YGB998',
		'ordersn'=> 'G99198101570223660201808273828',
	];
	$url = 'http://shop.gogo198.cn/payment/sign/Togrand.php';
	//数据请求
	$result = $curlpost->post($url,$data);
	$json = json_decode($result->response,true);
	echo '<pre>';
	print_r($json);
	
	
	

/**
 * 方法工具
 */
//字符串转换编码，UTF-8 转 GKB;
function charsetToGBK($mixed) {
    if (is_array($mixed)) {
        foreach ($mixed as $k => $v) {
            if (is_array($v)) {
                $mixed[$k] = charsetToGBK($v);
            } else {
                $encode = mb_detect_encoding($v, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
                if ($encode == 'UTF-8') {
                    $mixed[$k] = iconv('UTF-8', 'GBK', $v);
                }
            }
        }
    } else {
        $encode = mb_detect_encoding($mixed, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
        //var_dump($encode);
        if ($encode == 'UTF-8') {
            $mixed = iconv('UTF-8', 'GBK', $mixed);
        }
    }
    return $mixed;
}

//base64 安全的URL编码
function urlsafe_b64encode($string) {
	$data = base64_encode($string);
	$data = str_replace(array('+','/','='), array('-','_',''), $data);
	return $data;
}


//数组转 XML ： MessageSuit
function arrayToXml($arr,$dom=0,$item=0){
	if (!$dom){
		$dom = new DOMDocument('1.0','GBK');
	}
	if(!$item){
		$item = $dom->createElement("MessageSuit");
		$dom->appendChild($item);
	}
	foreach ($arr as $key=>$val) {

		$itemx = $dom->createElement(is_string($key)?$key:"item");

		$item->appendChild($itemx);

		if (!is_array($val)){
			$text = $dom->createTextNode($val);
			$itemx->appendChild($text);
		}else {
			arrayToXml($val,$dom,$itemx);
		}
	}
	return $dom->saveXML();
}


//数组转XML
function arrayToXmls($arr)
{
    $xml = "<Plain>";
    foreach ($arr as $key=>$val)
    {
        if (is_numeric($val)){
            $xml.="<".$key.">".$val."</".$key.">";
        }else{
             $xml.="<".$key.">".$val."</".$key.">";
        }
    }
    $xml.="</Plain>";
    return $xml;
}


//提交XML信息
function postXml($arrs,$url){
	$ch = curl_init();
	$timeout = 30;
	$header = array(
		'Content-Type:text/xml;charset=gbk',
		'Content-Length:'.strlen($arrs),
	);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, $header);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $arrs);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}
?>