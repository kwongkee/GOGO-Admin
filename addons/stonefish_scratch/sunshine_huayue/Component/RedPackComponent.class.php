<?php
/*
现金红包操作类
a7aa1c160e1fcc5540ac260d4b236441
a7aa1c160e1fcc5540ac260d4b236441
*/
class RedPackComponent {

	private $url;
	private $settings;

	function __construct($settings) {
		// 请求Url
		$this->url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
		$this->settings = $settings;

		load()-> model('account');
		$r = uni_setting_load(array('payment'),$_W['uniacid']);
		$this->mch_id = $r['payment']['wechat']['mchid'];
		$this->apikey = $r['payment']['wechat']['apikey'];
	}
	/*
	发送红包
	*/
	function sendRedPack($money,$openid) {
		$paramArr = $this->createParam($money,$openid);
		$paramArr['sign'] = $this->createSign($paramArr);
		$paramXml = array2xml($paramArr);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $paramXml);
		curl_setopt($ch, CURLOPT_SSLCERTTYPE,'PEM');
		curl_setopt($ch, CURLOPT_CAINFO, $this->settings['rootca_path']);
		curl_setopt($ch, CURLOPT_SSLCERT, $this->settings['appclient_cert_path']);
		curl_setopt($ch, CURLOPT_SSLKEY, $this->settings['appclient_key_path']);

		$return = curl_exec($ch);
		curl_close($ch);

		$r = $this->handleXml($return);
		var_dump($r);
		return $r;
	}
	/*
	创建参数
	*/
	function createParam($money,$openid) {
		global $_W;
		// 随机字符串
		$p['nonce_str'] = md5(rand(10000,99999));
		// 签名
		// $p['sign'] = '';
		// 商户订单号
		$p['mch_billno'] = $this->mch_id.date("Ymd").substr(md5($openid),0,4).date("His");
		// 商户号
		$p['mch_id'] = $this->mch_id;
		// 公众账号appid
		$p['wxappid'] = $_W['account']['key'];
		// 商户名称
		$p['send_name'] = $_W['account']['name'];
		// 用户openid
		$p['re_openid'] = $openid;
		// 付款金额
		$p['total_amount'] = $money*100;
		// 红包发放总人数
		$p['total_num'] = 1;
		// 红包祝福语
		$p['wishing'] = "社区提现成功$money元";
		// Ip地址
		$p['client_ip'] = $_W['clientip'];
		// 活动名称
		$p['act_name'] = "社区提现";
		// 备注
		$p['remark'] = "本次提现$money";

		ksort($p,SORT_STRING);

		return $p;
	}
	/*
	创建签名
	*/
	function createSign($paramArr) {

		foreach($paramArr as $k=>$v ) {
			$paramStr[] = $k.'='.$v;
		}
		// & + md5 + upper
		$sign = strtoupper(md5(join($paramStr,'&')."&key=".$this->apikey));
		return $sign;
	}

	/*
	解析结果
	*/
	public function  handleXml($xml) {
		$xml = "<?xml version='1.0' encoding='utf-8'?>".$xml;
		$obj = simplexml_load_string($xml);
		if((string)$obj->return_code == 'SUCCESS') {
			if((string)$obj->result_code == 'SUCCESS') {
				return true;
			}
		}
		return false;
	}
}