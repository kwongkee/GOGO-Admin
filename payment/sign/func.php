<?php
define('IN_MOBILE', true);
define('PDO_DEBUG', true);
require '../../framework/bootstrap.inc.php';
require '../../app/common/bootstrap.app.inc.php';
//load()->app('common');
//load()->app('template');
//load()->class('db');
//加载发送消息函数  diysend
load()->func('diysend');
$curlpost = new Curl;//实例化

date_default_timezone_set ('Asia/Shanghai');

//header('Content-Type:text/xml;charset=gbk');

//if(!empty($_POST)){
	
	$dataArr = array(
		'Message' =>array(
			'Plain'=>array(
				'TransId'=>   '60001',//业务代码：固定：60001，每个交易类型的唯一标识
				'MerDate'=>   date('Ymd',time()),//停车场接入方交易日期：交易记录日期，如：YYYYMMDD
				'OutTradeNo'=>'GGPgogo198'.date('Ymd',time()).rand(1, time()),//停车场接入方交易流水号：每笔交易记录的唯一标识，由停车场系统生成维护
//				'CarNo'=>   'A88S92',//车牌号码：如：粤A88S92
				'CarNo'=>   '粤YGB098',//车牌号码：如：粤A88S92
				'ParkCode'=>'1000000000000009',//商户代码：由银联无感支付平台分配，商户唯一标识

//				'InTime'=>  date('c'),//进停车场时间：如：YYYY-MM-DDTHH:mm:ss
//				'OutTime'=> date('c'),//出停车场时间：如：YYYY-MM-DDTHH:mm:ss

				'InTime'=>  mb_substr( date('c',time()), 0, strpos(date('c',time()),"+")),//进停车场时间：如：YYYY-MM-DDTHH:mm:ss
				'OutTime'=> mb_substr( date('c',time()+11), 0, strpos(date('c',time()+11),"+")),//出停车场时间：如：YYYY-MM-DDTHH:mm:ss

				'Amount'=>  1,//优惠前停车费金额
				'PayAmount'=> 1,//优惠后停车费金额：实际需要缴纳的金额
				'NoticeUrl'=> 'http://shop.gogo198.cn/payment/sign/kfnotify.php',//通知地址：扣款成功后，需要通知停车场
				'ParkType'=>  '00',//停车场代码类别： 00：银联无感支付平台的停车场代码  01：接入方的停车场代码
				'ParkNum'=>   '1000000000000009',//停车场代码
//				'Remark' =>   '备注',//备注  原样返回！
			),
			'Signature'=>array(
				'SignatureValue' =>'1',
			),			
		),
	);
	
	$key = 'kBL1dICpPBNxomAR';	
	//数组转换XML  头标签Plain 加密部分
	$Plain = charsetToGBK($dataArr['Message']['Plain']);
	$Tostring = arrayToXmls($Plain);
	//对xml数据拼接.key。  转换GBK编码
	$byte = charsetToGBK($Tostring.$key);
	// sha1加密返回字节
	$byte = sha1($byte,TRUE);	
	//base64 安全的URL编码
	$sign = urlsafe_b64encode($byte);	
	//转码后的字符串，赋值给SignatureValue
	$dataArr['Message']['Signature']['SignatureValue'] = $sign;	
	//把所有数组转换成 XML报文
	$Togrants = arrayToXml($dataArr);	
	//对所有xml报文进行，base64
//	$data['PACKET'] = base64_encode($Togrants);
	$data['PACKET'] = $Togrants;
	$url = 'http://ilazypay.com:8080/access/park';
	//发送报文
//	$result = postXml($url,$data);
	$result = $curlpost->post($url,$data);
	
	$obj = isimplexml_load_string($result->response, 'SimpleXMLElement', LIBXML_NOCDATA);
	
	echo json_encode($obj);
	
	file_put_contents('./logs/packet.txt', print_r($result->response,TRUE),FILE_APPEND);
	
//}



// UTF-8转GBK编码
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
?>