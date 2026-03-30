<?php
// Ä£¿éLTDÌá¹©
function getresp($respdata)
{
	$result = explode("\n", urldecode($respdata));
	$output = array();

	foreach ($result as $data) {
		$arr = explode('=', $data);
		$output[$arr[0]] = $arr[1];
	}

	return $output;
}

function HmacLocal($data)
{
	$text = '';
	global $merchantKey;

	while (list($key, $value) = each($data)) {
		if (isset($key) && ($key != 'hmac') && ($key != 'hmac_safe')) {
			$text .= $value;
		}
	}

	return HmacMd5($text, $merchantKey);
}

function gethamc_safe($data)
{
	$text = '';
	global $merchantKey;
	global $p1_MerId;

	while (list($key, $value) = each($data)) {
		if (($key != 'hmac') && ($key != 'hmac_safe') && ($value != NULL)) {
			$text .= $value . '#';
		}
	}

	$text1 = rtrim(trim($text), '#');
	return HmacMd5($text1, $merchantKey);
}

function HmacMd5($data, $key)
{
	$key = iconv('GBK', 'UTF-8', $key);
	$data = iconv('GBK', 'UTF-8', $data);
	$b = 64;

	if ($b < strlen($key)) {
		$key = pack('H*', md5($key));
	}

	$key = str_pad($key, $b, chr(0));
	$ipad = str_pad('', $b, chr(54));
	$opad = str_pad('', $b, chr(92));
	$k_ipad = $key ^ $ipad;
	$k_opad = $key ^ $opad;
	return md5($k_opad . pack('H*', md5($k_ipad . $data)));
}

include 'HttpClient.class.php';
date_default_timezone_set('PRC');
$reqURL_onLine = 'https://www.yeepay.com/app-merchant-proxy/node';
$OrderURL_onLine = 'https://cha.yeepay.com/app-merchant-proxy/command';
echo " \r\n";

?>
