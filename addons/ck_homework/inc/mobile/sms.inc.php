<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "SignatureHelper.php";

//ini_set("display_errors", "on"); // 显示错误提示，仅用于测试时排查问题
//set_time_limit(0); // 防止脚本超时，仅用于测试使用，生产环境请按实际情况设置
header("Content-Type: text/plain; charset=utf-8"); // 输出为utf-8的文本格式，仅用于测试

session_start();

//获取配置信息
$config = pdo_get('onljob_config', array('weid' => $_W['uniacid']));

$mobile = $_POST['mobile'];
$send_code = $_POST['send_code'];

$mobile_code = random(6,1);

$RegExp = '/^1[34578]\d{9}$/';
if(!preg_match($RegExp,$mobile)){
	exit('请输入有效的手机号码!');
}

if(empty($_SESSION['send_code']) or $send_code!=$_SESSION['send_code']){
	//防用户恶意请求
	exit('请求超时，请刷新页面后重试');
}



$params = array ();

// *** 需用户填写部分 ***

// fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
$accessKeyId = $config['sms_name'];
$accessKeySecret = $config['sms_password'];

// fixme 必填: 短信接收号码
$params["PhoneNumbers"] = $mobile;

// fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
$params["SignName"] = $config['sms_signsame'];

// fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
$params["TemplateCode"] = $config['sms_templatecode'];

// fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
$params['TemplateParam'] = Array (
	"code" => $mobile_code,
	"product" => $config['webtitle']
);

// fixme 可选: 设置发送短信流水号
$params['OutId'] = "";

// fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
$params['SmsUpExtendCode'] = "";


// *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
	$params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
}

// 初始化SignatureHelper实例用于设置参数，签名以及发送请求
$helper = new SignatureHelper();

// 此处可能会抛出异常，注意catch
$content = $helper->request(
	$accessKeyId,
	$accessKeySecret,
	"dysmsapi.aliyuncs.com",
	array_merge($params, array(
		"RegionId" => "cn-hangzhou",
		"Action" => "SendSms",
		"Version" => "2017-05-25",
	))
);

$get =  json_decode(json_encode($content),true);
if($get['Code'] == 'OK'){
	$_SESSION['mobile'] = $mobile;
	$_SESSION['mobile_code'] = $mobile_code;
}

echo $get['Message'];
?>