<?php
//提交XML信息
public function postXml($arrs,$url){
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

//数组转XML
public function arrayToXmls($arr)
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


//数组转换XML
public function arrayToXml($arr,$dom=0,$item=0){ 
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


//将UTF8编码转为GBK编码
public function charsetToGBK($mixed)
{
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


// 发送消息方法
public function sendMessagess($sendArr){
//	$touser = 'oR-IB0t4Yc9zmV-K-_5NRB-u5k4U';
//	$template_id = 'nTvUH_Pyld7lZr43NzyuvVSFOv7Ksl6li1lo9yvO2NQ';
	$template_id = 'trXaMaikj3VTVCmx4l9urCvridhOrD_95q2Z1NG3ae0';//支付结果通知
	$postdata = array(
			'first'=>array(
					'value'=>'您好，您的车费扣收失败！',
					'color'=>'#173177'),

			'keyword1'=>array(
					'value'=> $sendArr['body'],
					'color'=>'#436EEE'),

			'keyword2'=>array(
					'value'=>'￥'.$sendArr['payMoney'].'元',
					'color'=>'#173177'),

			'keyword3'=>array(
					'value'=> date('Y年m月d日 h:i:s',$sendArr['paytime']),
					'color'=>'#173177'),

			'remark'=>array(
					'value'=>'请点击详情，继续完成支付！',
					'color'=>'#00EE00'));

	$sql = 'SELECT * FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid';
	$account = pdo_fetch($sql, array(':uniacid' => $sendArr['uniacid']));
	$urls = 'http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.pay';
	$abs = new WeiXinAccount($account);
	$status = $abs->sendTplNotice($sendArr['touser'], $template_id, $postdata, $sendArr['payurl'], $topcolor = '#FF683F');
	if (is_error($status)) {
		return '发送失败,原因为'.$status['message'];
	}else {
		return '发送成功';
	}
}



?>