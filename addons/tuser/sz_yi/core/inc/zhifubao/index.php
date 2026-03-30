<?php
header("Content-type:text/html;charset=utf-8");
date_default_timezone_set('PRC');
require_once 'aop/AopClient.php';
require_once 'aop/request/AlipayFundTransToaccountTransferRequest.php';

$aop = new AopClient ();


$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
$aop->appId = '2017031706256276';   //2088411997697889  	
//$aop->appId = '2088411997697889';   //2088411997697889

//$aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEA5O+xpBiEU5RIrQkNNjIQKCYzZRv7DhdCpImym0PIr8pv8RMkTAF7zXbItlYARZpwuBstso7qjUsBnA+ap9BM2vlaxNuK/yGD1HMwYGdrlyAqHklTqYpy0LPcDoKDKjRGVzpsOJOkPIL7w9JolYWFnjAr1rJUgYw+gXfwN0J4zs0zx9kkFrUgC5E3kv8P8jbgd/46EKIbyc8dBI6l49GPbObxHVyS+GbboVXk7m1R7LQdg3Prokxy6lrgBc9iJRnRQ+qRpG13RzK7fhAlAI/hI4uLOwCERvw5lXjxwl6IgXnn5Df06eNrjVKWB7R25j1MyRGhKO1Lojj0SSw//l1vnQIDAQABAoIBAHlIHkS/lEKu2L2KgQxIA1Uxv6J960dwvSZrqEom48d1KE5/hIgbdRnJJtWpw+Ubx0FRbKkem1WU+dpSTe0/JagP161YXU+B0tQW3fcEcvQR3x5CXGcB6Id18Utiitgt72HAmppyZNyFy5jW+/7SSJIgFOldefVTdb2i64hq15M+E//+lbeHFR/1sFWcvx8x4Dwhg5co5Um6FqK0rNnL+nvOW5F4+SWCJ2kFycFC9vWc5yTPCC/+yODdCZC6msNB0pY8lSw4AQ972vEEZUPelfid8avbQLlTRen6U/5N4cN6V+wFnRTaYRmlj7uNeSG4FAlXn2Cztkf5gaZCFet+UgECgYEA9CmWEcIdOTmkb9KwqbwNxvv2xveEQjXDtl7mn6jJTo13pMt3JJDK4lihVZyE/bnsDVp5cuTCvhm8e1OcBtQuJKsd+j+MyLJDTBere+rzPjPRKzNAXA3FtK2turZfnJxol+Kkbap2FBnKk7cdjBHgpBHb3cyfyIJjo2IZj3sziIECgYEA8AkhA/7dBBAdQFtoKgeVDxZ7u0PKbi3NEBdeMIV7yzka2Mscv/0gZvLTKzeX+JYAB5geHXa86KGIQynIU7NHUCIIgVsuI2sdDyPcGfpWpCMGptT6NEwHteSdMoPO9r0Q0MgHl/Pf/+fdgHWjx2uvuSA9jTURXASH48yF5h0CeR0CgYAS+Hg0gQSMQbJJDfGz/myDnWgbJXgdPNgr/0uj9BVQCSXWpAhpyuY4l/JRGIwsuplgoDr3dla7NnyyiFiDH1FGgBUgMHfb0B3yd5RXWHX1y0jhNmY1wMwvsZ7h9vGO1Yg65N0puCjcfvSCbaPaEjmGBe0zWoa/qYHcW+7oewYSAQKBgQDDRmn5mimr2IAzjylap/h34c6fNjNFFzWwVZJm3vErDzXsELE+72qg1gM9MWkM7trvUq2NQr/EcUUtfpxem4b0hfttYQRUBBwL6RxyddpuhaAvsSwrx36uV2IbamfBC2bWsySaVehxg+wLSakH32+Bp9zrN7T7qyPyCr0Ty53rcQKBgQCyHs+ciFarhByyW9JBR/mjh++M+gQm8oh9FWbKXC2L28Nz3v10fV6jiBc7n+X4+WqN5rQw1lnGuBZMT/+eJwAo5NY+gGEBvM7coUwOR3x1JDkqGSgbAzA8mY0rTU4oCMIoTJQfm0PhU5PWuV3dFgDwv4zYf8mteg2SytOci+Yw+w==';
//$aop->alipayrsaPublicKey="MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA5O+xpBiEU5RIrQkNNjIQKCYzZRv7DhdCpImym0PIr8pv8RMkTAF7zXbItlYARZpwuBstso7qjUsBnA+ap9BM2vlaxNuK/yGD1HMwYGdrlyAqHklTqYpy0LPcDoKDKjRGVzpsOJOkPIL7w9JolYWFnjAr1rJUgYw+gXfwN0J4zs0zx9kkFrUgC5E3kv8P8jbgd/46EKIbyc8dBI6l49GPbObxHVyS+GbboVXk7m1R7LQdg3Prokxy6lrgBc9iJRnRQ+qRpG13RzK7fhAlAI/hI4uLOwCERvw5lXjxwl6IgXnn5Df06eNrjVKWB7R25j1MyRGhKO1Lojj0SSw//l1vnQIDAQAB";
$aop->apiVersion = '1.0';
$aop->signType = 'RSA2';
$aop->postCharset='UTF-8';
$aop->format='json';


$request = new AlipayFundTransToaccountTransferRequest ();
$request->setBizContent("{" .
"    \"out_biz_no\":\"10101012\"," .
"    \"payee_type\":\"ALIPAY_LOGONID\"," .
"    \"payee_account\":\"13544306795\"," .
"    \"amount\":\"0.1\"," .
//"    \"payer_real_name\":\"佛山市禅城区浩广网络产品销售有限公司\"," .
//"    \"payer_show_name\":\"佛山市禅城区浩广网络产品销售有限公司\"," .
//"    \"payee_real_name\":\"吴杰鹏\"," .
"    \"remark\":\"转账备注\"," .
"    \"ext_param\":\"{\\\"order_title\\\":\\\"测试001\\\"}\"" .
"  }");


$result = $aop->execute ( $request); 

 
$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
$resultCode = $result->$responseNode->code;
if(!empty($resultCode)&&$resultCode == 10000){
echo "成功";
} else {
echo "失败";

}