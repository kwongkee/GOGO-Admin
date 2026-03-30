<?php

//decode by http://www.yunlu99.com/
defined("IN_IA") or exit("Access Denied");
//$ckurl = "ck163";
function file_get_content($url)
{
	if (function_exists("file_get_contents")) {
		$result = @file_get_contents($url);
	}
	if ($result == '') {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_REFERER, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$result = curl_exec($ch);
		curl_close($ch);
	}
	return $result;
}
// $ckurla = ".com";
// $dqurl = $_SERVER["HTTP_HOST"];
// $dattp = "//" . $dqurl . "/";
// $md5url = md5($dqurl);
// $md5durl = md5($dattp);
// $ckurlq = "http://";
// $md5key = file_get_contents('' . $ckurlq . '' . $ckurl . '' . $ckurla . "/mkpass/ck_ketang/{$md5url}.txt");
// $info = file_get_content($porturl);