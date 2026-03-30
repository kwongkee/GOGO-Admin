<?php
class SmsComponent {

	static function sendCaptcha($mobile,$captcha,$openid = '') {

		include dirname(dirname(__FILE__)).'/plugin/alidayu/TopSdk.php';

		$settings_product = sunshine_huayueModuleSite::$_SET['alidayu_product'];
		$settings_product = $settings_product ? $settings_product : '快来租我';

		$c = new TopClient;
		$c->appkey = sunshine_huayueModuleSite::$_SET['alidayu_ak'];
		$c->secretKey = sunshine_huayueModuleSite::$_SET['alidayu_sk'];
		$req = new AlibabaAliqinFcSmsNumSendRequest;
		$req->setExtend($openid);
		$req->setSmsType("normal");
		$req->setSmsFreeSignName(sunshine_huayueModuleSite::$_SET['alidayu_sign_name']);
		$req->setSmsParam("{\"code\":\"$captcha\",\"product\":\"[$settings_product]\"}");
		$req->setRecNum($mobile);
		$req->setSmsTemplateCode(sunshine_huayueModuleSite::$_SET['alidayu_tpl_id']);
		$resp = $c->execute($req);

		WeUtility::logging('sunshine_huayue_SmsComponent', var_export($resp, true));

		if((string)$resp->result->success == 'true') {
			return true;
		}else {
			return false;
		}
	}
}