<?php
// 模块LTD提供
class Sz_DYi_Finance
{
	public function getHttpResponseGET($url, $cacert_url)
	{
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_NOBODY, 1);
		@curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curl, CURLOPT_CAINFO, $cacert_url);
		$info = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
		curl_close($curl);
		return $info;
	}

	public function alipay_build($openid = '', $paytype = 0, $money = 0, $trade_no = '', $desc = '', $alipay = '', $alipayname = '', $applyid = '')
	{
		global $_W;
		$setting = uni_setting($_W['uniacid'], array('payment'));

		if (is_array($setting['payment'])) {
			$options = $setting['payment']['alipay'];

			if (!empty($options)) {
				$partner = $options['partner'];
				$secret = $options['secret'];
			}
			else {
				$partner = '';
				$secret = '';
			}
		}

		$setdata = pdo_fetch('select * from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
		$setpay = unserialize($setdata['sets']);

		if (!empty($setpay['pay'])) {
			$email = $setpay['pay']['alipay_number'];
			$account_name = $setpay['pay']['alipay_name'];
		}
		else {
			$email = '';
			$account_name = '';
		}

		$pay_date = date('Ymd', time());
		$set = array();
		$set['partner'] = $partner;
		$set['service'] = 'batch_trans_notify';
		$set['_input_charset'] = 'utf-8';
		$set['sign_type'] = 'MD5';
		$set['notify_url'] = $_W['siteroot'] . 'addons/sz_yi/payment/alipay/notify_alipay' . $_W['uniacid'] . '.php';
		$set['email'] = $email;
		$set['account_name'] = $account_name;
		$set['pay_date'] = $pay_date;
		$set['batch_no'] = m('common')->createNO('member_log', 'batch_no', '');
		$set['batch_fee'] = $money;
		$set['batch_num'] = 1;
		$set['detail_data'] = $set['batch_no'] . '^' . $alipay . '^' . $alipayname . '^' . $money . '^佣金提现';
		$prepares = array();

		foreach ($set as $key => $value) {
			if (($key != 'sign') && ($key != 'sign_type')) {
				$prepares[] = $key . '=' . $value;
			}
		}

		sort($prepares);
		$string = implode($prepares, '&');
		$cert = IA_ROOT . '/addons/sz_yi/cert/cacert.pem';

		if (!file_exists($cert)) {
			message('缺少支付宝证书文件!', '', 'error');
		}

		if (empty($set['email']) || empty($set['account_name'])) {
			message('未填写完整的支付宝付款账号或付款账户名，请到【系统设置】->【支付设置】中设置!', '', 'error');
		}

		if (empty($alipay) || empty($alipayname)) {
			message('未填写完整的收款人支付宝账号或姓名!', '', 'error');
		}

		$string .= $secret;
		$set['sign'] = md5($string);
		$url = 'https://mapi.alipay.com/gateway.do' . '?' . http_build_query($set, '', '&');
		$resp = $this->getHttpResponseGET($url, $cert);
		header('Location:' . $resp);
		$apply = array('status' => '3', 'batch_no' => $set['batch_no'], 'paytime' => time());
		pdo_update('sz_yi_commission_apply', $apply, array('id' => $applyid));
		file_put_contents(dirname(__FILE__) . ' . \'/../../payment/alipay/notify_alipay' . $_W['uniacid'] . '.php', '<?php include(\'notify_alipay.php\'); ?>');
		exit();
	}

	public function alipay_finance($money = 0, $alipay = '', $alipayname = '', $logid = '')
	{
		global $_W;
		$setting = uni_setting($_W['uniacid'], array('payment'));

		if (is_array($setting['payment'])) {
			$options = $setting['payment']['alipay'];

			if (!empty($options)) {
				$partner = $options['partner'];
				$secret = $options['secret'];
			}
			else {
				$partner = '';
				$secret = '';
			}
		}

		$setdata = pdo_fetch('select * from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
		$setpay = unserialize($setdata['sets']);

		if (!empty($setpay['pay'])) {
			$email = $setpay['pay']['alipay_number'];
			$account_name = $setpay['pay']['alipay_name'];
		}
		else {
			$email = '';
			$account_name = '';
		}

		$pay_date = date('Ymd', time());
		$set = array();
		$set['partner'] = $partner;
		$set['service'] = 'batch_trans_notify';
		$set['_input_charset'] = 'utf-8';
		$set['sign_type'] = 'MD5';
		$set['notify_url'] = $_W['siteroot'] . 'addons/sz_yi/payment/alipay/notify_finance' . $_W['uniacid'] . '.php';
		$set['email'] = $email;
		$set['account_name'] = $account_name;
		$set['pay_date'] = $pay_date;
		$set['batch_no'] = m('common')->createNO('member_log', 'batch_no', '');
		$set['batch_fee'] = $money;
		$set['batch_num'] = 1;
		$set['detail_data'] = $set['batch_no'] . '^' . $alipay . '^' . $alipayname . '^' . $money . '^余额提现';
		$prepares = array();

		foreach ($set as $key => $value) {
			if (($key != 'sign') && ($key != 'sign_type')) {
				$prepares[] = $key . '=' . $value;
			}
		}

		sort($prepares);
		$string = implode($prepares, '&');
		$cert = IA_ROOT . '/addons/sz_yi/cert/cacert.pem';

		if (!file_exists($cert)) {
			message('缺少支付宝证书文件!', '', 'error');
		}

		if (empty($set['email']) || empty($set['account_name'])) {
			message('未填写完整的支付宝付款账号或付款账户名，请到【系统设置】->【支付设置】中设置!', '', 'error');
		}

		$string .= $secret;
		$set['sign'] = md5($string);
		$url = 'https://mapi.alipay.com/gateway.do' . '?' . http_build_query($set, '', '&');
		$resp = $this->getHttpResponseGET($url, $cert);
		header('Location:' . $resp);
		$apply = array('batch_no' => $set['batch_no']);
		pdo_update('sz_yi_member_log', $apply, array('id' => $logid));
		file_put_contents(dirname(__FILE__) . ' . \'/../../payment/alipay/notify_finance' . $_W['uniacid'] . '.php', '<?php include(\'notify_finance.php\'); ?>');
		exit();
	}

	public function pay($openid = '', $paytype = 0, $money = 0, $trade_no = '', $desc = '', $alipay = '', $alipayname = '', $applyid = '')
	{
		global $_W;
		global $_GPC;

		if (empty($openid)) {
			return error(-1, 'openid不能为空');
		}

		$member = m('member')->getInfo($openid);

		if (empty($member)) {
			return error(-1, '未找到用户');
		}

		if (empty($paytype)) {
			m('member')->setCredit($openid, 'credit2', $money, array(0, $desc));
			return true;
		}

		$setting = uni_setting($_W['uniacid'], array('payment'));

		if (!is_array($setting['payment'])) {
			return error(1, '没有设定支付参数');
		}

		$pay = m('common')->getSysset('pay');

		if ($paytype == 3) {
			$this->alipay_build($openid, $paytype, $money, $trade_no, $desc, $alipay, $alipayname, $applyid);
		}

		$wechat = $setting['payment']['wechat'];
		$sql = 'SELECT `key`,`secret` FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid limit 1';
		$row = pdo_fetch($sql, array(':uniacid' => $_W['uniacid']));
		$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
		$pars = array();
		$pars['mch_appid'] = $row['key'];
		$pars['mchid'] = $wechat['mchid'];
		$pars['nonce_str'] = random(32);
		$pars['partner_trade_no'] = empty($trade_no) ? time() . random(4, true) : $trade_no;
		$pars['openid'] = $openid;
		$pars['check_name'] = 'NO_CHECK';
		$pars['amount'] = $money;
		$pars['desc'] = empty($desc) ? '佣金提现' : $desc;
		$pars['spbill_create_ip'] = gethostbyname($_SERVER['HTTP_HOST']);
		ksort($pars, SORT_STRING);
		$string1 = '';

		foreach ($pars as $k => $v) {
			$string1 .= $k . '=' . $v . '&';
		}

		$string1 .= 'key=' . $wechat['apikey'];
		$pars['sign'] = strtoupper(md5($string1));
		$xml = array2xml($pars);
		$extras = array();
		$sec = m('common')->getSec();
		$certs = iunserializer($sec['sec']);

		if (is_array($certs)) {
			if (empty($certs['cert']) || empty($certs['key']) || empty($certs['root'])) {
				message('未上传完整的微信支付证书，请到【系统设置】->【支付方式】中上传!', '', 'error');
			}

			$certfile = IA_ROOT . '/addons/sz_yi/cert/' . random(128);
			file_put_contents($certfile, $certs['cert']);
			$keyfile = IA_ROOT . '/addons/sz_yi/cert/' . random(128);
			file_put_contents($keyfile, $certs['key']);
			$_obf_DSkqPBgwDh5AGgkkCxUUHQgTEyYULwE_ = IA_ROOT . '/addons/sz_yi/cert/' . random(128);
			file_put_contents($_obf_DSkqPBgwDh5AGgkkCxUUHQgTEyYULwE_, $certs['root']);
			$extras['CURLOPT_SSLCERT'] = $certfile;
			$extras['CURLOPT_SSLKEY'] = $keyfile;
			$extras['CURLOPT_CAINFO'] = $_obf_DSkqPBgwDh5AGgkkCxUUHQgTEyYULwE_;
		}
		else {
			message('未上传完整的微信支付证书，请到【系统设置】->【支付方式】中上传!', '', 'error');
		}

		load()->func('communication');
		$resp = ihttp_request($url, $xml, $extras);
		@unlink($certfile);
		@unlink($keyfile);
		@unlink($_obf_DSkqPBgwDh5AGgkkCxUUHQgTEyYULwE_);

		if (is_error($resp)) {
			return error(-2, $resp['message']);
		}

		if (empty($resp['content'])) {
			return error(-2, '网络错误');
		}

		$arr = json_decode(json_encode((array) simplexml_load_string($resp['content'])), true);
		$xml = '<?xml version="1.0" encoding="utf-8"?>' . $resp['content'];
		$dom = new DOMDocument();

		if ($dom->loadXML($xml)) {
			$xpath = new DOMXPath($dom);
			$code = $xpath->evaluate('string(//xml/return_code)');
			$ret = $xpath->evaluate('string(//xml/result_code)');
			if ((strtolower($code) == 'success') && (strtolower($ret) == 'success')) {
				return true;
			}

			if ($xpath->evaluate('string(//xml/return_msg)') == $xpath->evaluate('string(//xml/err_code_des)')) {
				$error = $xpath->evaluate('string(//xml/return_msg)');
			}
			else {
				$error = $xpath->evaluate('string(//xml/return_msg)') . '<br/>' . $xpath->evaluate('string(//xml/err_code_des)');
			}

			return error(-2, $error);
		}

		return error(-1, '未知错误');
	}

	public function sendredpack($openid, $money, $orderid, $desc = '', $act_name = '', $remark = '')
	{
		global $_W;
		$_W['account']['name'] = pdo_fetchcolumn('SELECT name FROM ' . tablename('uni_account') . 'WHERE uniacid = \'' . $_W['uniacid'] . '\'');

		if (empty($openid)) {
			return error(-1, 'openid不能为空');
		}

		$member = m('member')->getInfo($openid);

		if (empty($member)) {
			return error(-1, '未找到用户');
		}

		$setting = uni_setting($_W['uniacid'], array('payment'));

		if (!is_array($setting['payment'])) {
			return error(1, '没有设定支付参数');
		}

		$pay = m('common')->getSysset('pay');
		$wechat = $setting['payment']['wechat'];
		$sql = 'SELECT `key`,`secret` FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid limit 1';
		$row = pdo_fetch($sql, array(':uniacid' => $_W['uniacid']));
		$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
		$post = array('wxappid' => $row['key'], 'mch_id' => $wechat['mchid'], 'mch_billno' => $wechat['mchid'] . date('YmdHis') . rand(1000, 9999), 'client_ip' => gethostbyname($_SERVER['HTTP_HOST']), 're_openid' => $openid, 'total_amount' => $money, 'total_num' => 1, 'send_name' => $_W['account']['name'], 'wishing' => empty($desc) ? '佣金提现红包' : $desc, 'act_name' => empty($act_name) ? '佣金提现红包' : $act_name, 'remark' => empty($remark) ? '佣金提现红包' : $remark, 'nonce_str' => $this->createNonceStr());
		$_obf_DQM8DxU0KRoHGDAHLwckXAVcDDsQNyI_ = $this->formatQuery($post, false);
		$stringSignTemp = $_obf_DQM8DxU0KRoHGDAHLwckXAVcDDsQNyI_ . '&key=' . $wechat['apikey'];
		$post['sign'] = strtoupper(md5($stringSignTemp));
		$postXml = array2xml($post, 1);
		$sec = m('common')->getSec();
		$certs = iunserializer($sec['sec']);

		if (is_array($certs)) {
			if (empty($certs['cert']) || empty($certs['key']) || empty($certs['root'])) {
				message('未上传完整的微信支付证书，请到【系统设置】->【支付方式】中上传!', '', 'error');
			}

			$certfile = IA_ROOT . '/addons/sz_yi/cert/' . random(128);
			file_put_contents($certfile, $certs['cert']);
			$keyfile = IA_ROOT . '/addons/sz_yi/cert/' . random(128);
			file_put_contents($keyfile, $certs['key']);
			$_obf_DSkqPBgwDh5AGgkkCxUUHQgTEyYULwE_ = IA_ROOT . '/addons/sz_yi/cert/' . random(128);
			file_put_contents($_obf_DSkqPBgwDh5AGgkkCxUUHQgTEyYULwE_, $certs['root']);
			$extras = array('CURLOPT_SSLCERT' => $certfile, 'CURLOPT_SSLKEY' => $keyfile, 'CURLOPT_CAINFO' => $_obf_DSkqPBgwDh5AGgkkCxUUHQgTEyYULwE_);
		}
		else {
			message('未上传完整的微信支付证书，请到【系统设置】->【支付方式】中上传!', '', 'error');
		}

		load()->func('communication');
		$resp = ihttp_request($url, $postXml, $extras);
		@unlink($certfile);
		@unlink($keyfile);
		@unlink($_obf_DSkqPBgwDh5AGgkkCxUUHQgTEyYULwE_);

		if (is_error($resp)) {
			return error(-2, $resp['message']);
		}

		if (empty($resp['content'])) {
			return error(-2, '网络错误');
		}

		$arr = json_decode(json_encode((array) simplexml_load_string($resp['content'])), true);
		$xml = '<?xml version="1.0" encoding="utf-8"?>' . $resp['content'];
		$dom = new DOMDocument();

		if ($dom->loadXML($xml)) {
			$xpath = new DOMXPath($dom);
			$code = $xpath->evaluate('string(//xml/return_code)');
			$ret = $xpath->evaluate('string(//xml/result_code)');
			if ((strtolower($code) == 'success') && (strtolower($ret) == 'success')) {
				return true;
			}

			if ($xpath->evaluate('string(//xml/return_msg)') == $xpath->evaluate('string(//xml/err_code_des)')) {
				$error = $xpath->evaluate('string(//xml/return_msg)');
			}
			else {
				$error = $xpath->evaluate('string(//xml/return_msg)') . '<br/>' . $xpath->evaluate('string(//xml/err_code_des)');
			}

			if (!empty($orderid) && ($orderid != 0)) {
				$sql = 'SELECT `ordersn` FROM ' . tablename('sz_yi_order') . ' WHERE `id`=:orderid limit 1';
				$row = pdo_fetch($sql, array(':orderid' => $orderid));

				if (!empty($row)) {
					$msg = array(
						'keyword1' => array('value' => '购买商品发送红包失败', 'color' => '#73a68d'),
						'keyword2' => array('value' => '【订单编号】' . $row['ordersn'], 'color' => '#73a68d'),
						'remark'   => array('value' => '购物赠送红包发送失败！失败原因：' . $error)
						);
					pdo_update('sz_yi_order', array('redstatus' => $error), array('id' => $orderid));
					m('message')->sendCustomNotice($openid, $msg);
				}
			}

			return error(-2, $error);
		}

		return error(-1, '未知错误');
	}

	public function refund($openid, $out_trade_no, $out_refund_no, $totalmoney, $refundmoney = 0)
	{
		global $_W;
		global $_GPC;

		if (empty($openid)) {
			return error(-1, 'openid不能为空');
		}

		$member = m('member')->getInfo($openid);

		if (empty($member)) {
			return error(-1, '未找到用户');
		}

		$setting = uni_setting($_W['uniacid'], array('payment'));

		if (!is_array($setting['payment'])) {
			return error(1, '没有设定支付参数');
		}

		$pay = m('common')->getSysset('pay');
		$wechat = $setting['payment']['wechat'];
		$set = m('common')->getSysset(array('shop', 'pay'));
		$sql = 'SELECT `key`,`secret` FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid limit 1';
		$row = pdo_fetch($sql, array(':uniacid' => $_W['uniacid']));

		if (!empty($set['pay']['weixin_jie'])) {
			$wechat = array('version' => 1, 'apikey' => $set['pay']['weixin_jie_apikey'], 'appid' => $set['pay']['weixin_jie_appid'], 'mchid' => $set['pay']['weixin_jie_mchid']);
			$row['key'] = $set['pay']['weixin_jie_appid'];
		}

		$url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
		$pars = array();
		$pars['appid'] = $row['key'];
		$pars['mch_id'] = $wechat['mchid'];
		$pars['nonce_str'] = random(8);
		$pars['out_trade_no'] = $out_trade_no;
		$pars['out_refund_no'] = $out_refund_no;
		$pars['total_fee'] = $totalmoney;
		$pars['refund_fee'] = $refundmoney;
		$pars['op_user_id'] = $wechat['mchid'];
		ksort($pars, SORT_STRING);
		$string1 = '';

		foreach ($pars as $k => $v) {
			$string1 .= $k . '=' . $v . '&';
		}

		$string1 .= 'key=' . $wechat['apikey'];
		$pars['sign'] = strtoupper(md5($string1));
		$xml = array2xml($pars);
		$extras = array();
		$sec = m('common')->getSec();
		$certs = iunserializer($sec['sec']);

		if (!empty($set['pay']['weixin_jie'])) {
			$certs['cert'] = $certs['jie_cert'];
			$certs['key'] = $certs['jie_key'];
			$certs['root'] = $certs['jie_root'];
		}

		if (is_array($certs)) {
			if (empty($certs['cert']) || empty($certs['key']) || empty($certs['root'])) {
				message('未上传完整的微信支付证书，请到【系统设置】->【支付方式】中上传!', '', 'error');
			}

			$certfile = IA_ROOT . '/addons/sz_yi/cert/' . random(128);
			file_put_contents($certfile, $certs['cert']);
			$keyfile = IA_ROOT . '/addons/sz_yi/cert/' . random(128);
			file_put_contents($keyfile, $certs['key']);
			$_obf_DSkqPBgwDh5AGgkkCxUUHQgTEyYULwE_ = IA_ROOT . '/addons/sz_yi/cert/' . random(128);
			file_put_contents($_obf_DSkqPBgwDh5AGgkkCxUUHQgTEyYULwE_, $certs['root']);
			$extras['CURLOPT_SSLCERT'] = $certfile;
			$extras['CURLOPT_SSLKEY'] = $keyfile;
			$extras['CURLOPT_CAINFO'] = $_obf_DSkqPBgwDh5AGgkkCxUUHQgTEyYULwE_;
		}
		else {
			message('未上传完整的微信支付证书，请到【系统设置】->【支付方式】中上传!', '', 'error');
		}

		load()->func('communication');
		$resp = ihttp_request($url, $xml, $extras);
		@unlink($certfile);
		@unlink($keyfile);
		@unlink($_obf_DSkqPBgwDh5AGgkkCxUUHQgTEyYULwE_);

		if (is_error($resp)) {
			return error(-2, $resp['message']);
		}

		if (empty($resp['content'])) {
			return error(-2, '网络错误');
		}

		$arr = json_decode(json_encode((array) simplexml_load_string($resp['content'])), true);
		$xml = '<?xml version="1.0" encoding="utf-8"?>' . $resp['content'];
		$dom = new DOMDocument();

		if ($dom->loadXML($xml)) {
			$xpath = new DOMXPath($dom);
			$code = $xpath->evaluate('string(//xml/return_code)');
			$ret = $xpath->evaluate('string(//xml/result_code)');
			if ((strtolower($code) == 'success') && (strtolower($ret) == 'success')) {
				return true;
			}

			if ($xpath->evaluate('string(//xml/return_msg)') == $xpath->evaluate('string(//xml/err_code_des)')) {
				$error = $xpath->evaluate('string(//xml/return_msg)');
			}
			else {
				$error = $xpath->evaluate('string(//xml/return_msg)') . '<br/>' . $xpath->evaluate('string(//xml/err_code_des)');
			}

			return error(-2, $error);
		}

		return error(-1, '未知错误');
	}

	public function alipayrefund($openid, $trade_no, $out_refund_no, $refundmoney = 0)
	{
		global $_W;
		$setting = uni_setting($_W['uniacid'], array('payment'));

		if (is_array($setting['payment'])) {
			$options = $setting['payment']['alipay'];

			if (!empty($options)) {
				$partner = $options['partner'];
				$secret = $options['secret'];
				$email = $options['account'];
			}
			else {
				$partner = '';
				$secret = '';
				$email = '';
			}
		}

		$setdata = pdo_fetch('select * from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
		$setpay = unserialize($setdata['sets']);
		$set = array();
		$set['service'] = 'refund_fastpay_by_platform_pwd';
		$set['partner'] = $partner;
		$set['_input_charset'] = 'utf-8';
		$set['sign_type'] = 'MD5';
		$set['notify_url'] = $_W['siteroot'] . 'addons/sz_yi/payment/alipay/refund_alipay' . $_W['uniacid'] . '.php';
		$set['seller_email'] = $email;
		$set['seller_user_id'] = $partner;
		$set['refund_date'] = date('Y-m-d H:i:s', time());
		$set['batch_no'] = m('common')->createNO('member_log', 'batch_no', '');
		$set['batch_fee'] = $refundmoney;
		$set['batch_num'] = 1;
		$set['detail_data'] = $trade_no . '^' . $refundmoney . '^订单退款';
		$prepares = array();

		foreach ($set as $key => $value) {
			if (($key != 'sign') && ($key != 'sign_type')) {
				$prepares[] = $key . '=' . $value;
			}
		}

		sort($prepares);
		$string = implode($prepares, '&');
		$string .= $secret;
		$set['sign'] = md5($string);
		$cert = IA_ROOT . '/addons/sz_yi/cert/cacert.pem';

		if (!file_exists($cert)) {
			message('缺少支付宝证书文件!', '', 'error');
		}

		if (empty($set['seller_email'])) {
			message('未填写完整的支付宝付款账号或付款账户名，请到【系统设置】->【支付设置】中设置!', '', 'error');
		}

		$url = 'https://mapi.alipay.com/gateway.do' . '?' . http_build_query($set, '', '&');
		$resp = $this->getHttpResponseGET($url, $cert);
		header('Location:' . $resp);
		$refund = array('batch_no' => $set['batch_no'], 'returntime' => time());
		pdo_update('sz_yi_order_refund', $refund, array('refundno' => $out_refund_no));
		file_put_contents(dirname(__FILE__) . ' . \'/../../payment/alipay/refund_alipay' . $_W['uniacid'] . '.php', '<?php include(\'refund_alipay.php\'); ?>');
		exit();
	}

	public function yeepayrefund($type, $openid, $trade_no, $out_refund_no, $refundmoney = 0)
	{
		global $_W;
		$setdata = pdo_fetch('select * from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
		$set = unserialize($setdata['sets']);
		$merchantaccount = $set['pay']['merchantaccount'];
		$merchantPublicKey = $set['pay']['merchantPublicKey'];
		$merchantPrivateKey = $set['pay']['merchantPrivateKey'];
		$yeepayPublicKey = $set['pay']['yeepayPublicKey'];
		$p1_MerId = $set['pay']['merchantaccount'];
		$merchantKey = $set['pay']['merchantKey'];

		if ($type == 26) {
			include IA_ROOT . '/addons/sz_yi/core/inc/plugin/vendor/yeepay/wy/yeepayCommon.php';
			$data = array();
			$data['p0_Cmd'] = 'RefundOrd';
			$data['p1_MerId'] = $p1_MerId;
			$data['p2_Order'] = $out_refund_no;
			$data['pb_TrxId'] = $trade_no;
			$data['p3_Amt'] = $refundmoney;
			$data['p4_Cur'] = 'CNY';
			$data['p5_Desc'] = '';
			$_obf_DSwXAxU9IxkDIgodOygsLxorFQYSCyI_ = HmacMd5(implode($data), $merchantKey);
			$data['hmac'] = $_obf_DSwXAxU9IxkDIgodOygsLxorFQYSCyI_;
			$respdata = HttpClient::quickPost($OrderURL_onLine, $data);
			$arr = getresp($respdata);
			$arr1 = array('r0_Cmd' => $arr['r0_Cmd'], 'r1_Code' => $arr['r1_Code'], 'r2_TrxId' => $arr['r2_TrxId'], 'r3_Amt' => $arr['r3_Amt'], 'r4_Cur' => $arr['r4_Cur']);
			$hmacLocal = HmacLocal($arr1);
			$safeLocal = gethamc_safe($arr1);

			if ($arr1['r1_Code'] != 1) {
				echo '退款失败';
				return NULL;
			}
		}
		else {
			include IA_ROOT . '/addons/sz_yi/core/inc/plugin/vendor/yeepay/yeepay/yeepayMPay.php';
			$yeepay = new yeepayMPay($merchantaccount, $merchantPublicKey, $merchantPrivateKey, $yeepayPublicKey);
			$order_id = trim($out_refund_no);
			$amount = intval($refundmoney * 100);
			$currency = 156;
			$origyborder_id = trim($trade_no);
			$cause = '退款';
			$data = $yeepay->refund($amount, $order_id, $origyborder_id, $currency, $cause);
		}
	}

	public function apprefund($type, $openid, $trade_no, $out_refund_no, $refundmoney = 0)
	{
		global $_W;
		$setdata = m('cache')->get('sysset');
		$set = unserialize($setdata['sets']);
		$setting = uni_setting($_W['uniacid'], array('payment'));
		$pay = $setting['payment'];
		require_once '../addons/sz_yi/plugin/pingpp/init.php';
		$api_key = $pay['ping']['secret'];
		\Pingpp\Pingpp::setApiKey($api_key);
		$ch = \Pingpp\Charge::retrieve($trade_no);

		try {
			$re = $ch->refunds->create(array('amount' => $refundmoney * 100, 'description' => 'APP端支付退款'));
		}
		catch (Exception $e) {
			message($e->getMessage(), referer(), 'error');
		}

		if ($re) {
			if ($type == 28) {
				if (($re->succeed == false) && ($re->status == 'pending')) {
					$url = substr($re->failure_msg, strpos($re->failure_msg, ':') + 1);
					header('location:' . $url);
					exit();
					return NULL;
				}

				message('退款失败!', referer(), 'error');
				return NULL;
			}

			if ($type == 27) {
				if (($re->status != 'succeeded') && ($re->status != 'pending')) {
					message('退款失败!', referer(), 'error');
				}

				if ($re->status == 'pending') {
					return NULL;
				}

				return NULL;
			}
		}
		else {
			message('退款失败!', referer(), 'error');
		}
	}

	public function downloadbill($starttime, $endtime, $type = 'ALL')
	{
		global $_W;
		global $_GPC;
		$dates = array();
		$startdate = date('Ymd', $starttime);
		$enddate = date('Ymd', $endtime);

		if ($startdate == $enddate) {
			$dates = array($startdate);
		}
		else {
			$days = (double) ($endtime - $starttime) / 86400;
			$d = 0;

			while ($d < $days) {
				$dates[] = date('Ymd', strtotime($startdate . '+' . $d . ' day'));
				++$d;
			}
		}

		if (empty($dates)) {
			message('对账单日期选择错误!', '', 'error');
		}

		$setting = uni_setting($_W['uniacid'], array('payment'));

		if (!is_array($setting['payment'])) {
			return error(1, '没有设定支付参数');
		}

		$wechat = $setting['payment']['wechat'];
		$sql = 'SELECT `key`,`secret` FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid limit 1';
		$row = pdo_fetch($sql, array(':uniacid' => $_W['uniacid']));
		$content = '';

		foreach ($dates as $date) {
			$dc = $this->downloadday($date, $row, $wechat, $type);
			if (is_error($dc) || strexists($dc, 'CDATA[FAIL]')) {
				continue;
			}

			$content .= $date . " 账单\r\n\r\n";
			$content .= $dc . "\r\n\r\n";
		}

		$file = time() . '.csv';
		header('Content-type: application/octet-stream ');
		header('Accept-Ranges: bytes ');
		header('Content-Disposition: attachment; filename=' . $file);
		header('Expires: 0 ');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0 ');
		header('Pragma: public ');
		exit($content);
	}

	private function downloadday($date, $row, $wechat, $type)
	{
		$url = 'https://api.mch.weixin.qq.com/pay/downloadbill';
		$pars = array();
		$pars['appid'] = $row['key'];
		$pars['mch_id'] = $wechat['mchid'];
		$pars['nonce_str'] = random(8);
		$pars['device_info'] = 'sz_yi';
		$pars['bill_date'] = $date;
		$pars['bill_type'] = $type;
		ksort($pars, SORT_STRING);
		$string1 = '';

		foreach ($pars as $k => $v) {
			$string1 .= $k . '=' . $v . '&';
		}

		$string1 .= 'key=' . $wechat['apikey'];
		$pars['sign'] = strtoupper(md5($string1));
		$xml = array2xml($pars);
		$extras = array();
		load()->func('communication');
		$resp = ihttp_request($url, $xml, $extras);

		if (strexists($resp['content'], 'No Bill Exist')) {
			return error(-2, '未搜索到任何账单');
		}

		if (is_error($resp)) {
			return error(-2, $resp['message']);
		}

		if (empty($resp['content'])) {
			return error(-2, '网络错误');
		}

		return $resp['content'];
	}

	public function closeOrder($out_trade_no = '')
	{
		global $_W;
		global $_GPC;
		$setting = uni_setting($_W['uniacid'], array('payment'));

		if (!is_array($setting['payment'])) {
			return error(1, '没有设定支付参数');
		}

		$wechat = $setting['payment']['wechat'];
		$sql = 'SELECT `key`,`secret` FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid limit 1';
		$row = pdo_fetch($sql, array(':uniacid' => $_W['uniacid']));
		$url = 'https://api.mch.weixin.qq.com/pay/closeorder';
		$pars = array();
		$pars['appid'] = $row['key'];
		$pars['mch_id'] = $wechat['mchid'];
		$pars['nonce_str'] = random(8);
		$pars['out_trade_no'] = $out_trade_no;
		ksort($pars, SORT_STRING);
		$string1 = '';

		foreach ($pars as $k => $v) {
			$string1 .= $k . '=' . $v . '&';
		}

		$string1 .= 'key=' . $wechat['apikey'];
		$pars['sign'] = strtoupper(md5($string1));
		$xml = array2xml($pars);
		load()->func('communication');
		$resp = ihttp_post($url, $xml);

		if (is_error($resp)) {
			return error(-2, $resp['message']);
		}

		if (empty($resp['content'])) {
			return error(-2, '网络错误');
		}

		$arr = json_decode(json_encode((array) simplexml_load_string($resp['content'])), true);
		$xml = '<?xml version="1.0" encoding="utf-8"?>' . $resp['content'];
		$dom = new DOMDocument();

		if ($dom->loadXML($xml)) {
			$xpath = new DOMXPath($dom);
			$code = $xpath->evaluate('string(//xml/return_code)');
			$ret = $xpath->evaluate('string(//xml/result_code)');
			$trade_state = $xpath->evaluate('string(//xml/trade_state)');
			if ((strtolower($code) == 'success') && (strtolower($ret) == 'success') && (strtolower($trade_state) == 'success')) {
				return true;
			}

			if ($xpath->evaluate('string(//xml/return_msg)') == $xpath->evaluate('string(//xml/err_code_des)')) {
				$error = $xpath->evaluate('string(//xml/return_msg)');
			}
			else {
				$error = $xpath->evaluate('string(//xml/return_msg)') . '<br/>' . $xpath->evaluate('string(//xml/err_code_des)');
			}

			return error(-2, $error);
		}

		return error(-1, '未知错误');
	}

	public function isWeixinPay($out_trade_no)
	{
		global $_W;
		global $_GPC;
		$setting = uni_setting($_W['uniacid'], array('payment'));

		if (!is_array($setting['payment'])) {
			return error(1, '没有设定支付参数');
		}

		$wechat = $setting['payment']['wechat'];
		$sql = 'SELECT `key`,`secret` FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid limit 1';
		$row = pdo_fetch($sql, array(':uniacid' => $_W['uniacid']));
		$set = m('common')->getSysset(array('shop', 'pay'));

		if (!empty($set['pay']['weixin_jie'])) {
			$wechat = array('version' => 1, 'apikey' => $set['pay']['weixin_jie_apikey'], 'appid' => $set['pay']['weixin_jie_appid'], 'mchid' => $set['pay']['weixin_jie_mchid']);
			$row['key'] = $set['pay']['weixin_jie_appid'];
		}

		$url = 'https://api.mch.weixin.qq.com/pay/orderquery';
		$pars = array();
		$pars['appid'] = $row['key'];
		$pars['mch_id'] = $wechat['mchid'];
		$pars['nonce_str'] = random(8);
		$pars['out_trade_no'] = $out_trade_no;
		ksort($pars, SORT_STRING);
		$string1 = '';

		foreach ($pars as $k => $v) {
			$string1 .= $k . '=' . $v . '&';
		}

		$string1 .= 'key=' . $wechat['apikey'];
		$pars['sign'] = strtoupper(md5($string1));
		$xml = array2xml($pars);
		load()->func('communication');
		$resp = ihttp_post($url, $xml);

		if (is_error($resp)) {
			return error(-2, $resp['message']);
		}

		if (empty($resp['content'])) {
			return error(-2, '网络错误');
		}

		$arr = json_decode(json_encode((array) simplexml_load_string($resp['content'])), true);
		$xml = '<?xml version="1.0" encoding="utf-8"?>' . $resp['content'];
		$dom = new DOMDocument();

		if ($dom->loadXML($xml)) {
			$xpath = new DOMXPath($dom);
			$code = $xpath->evaluate('string(//xml/return_code)');
			$ret = $xpath->evaluate('string(//xml/result_code)');
			$trade_state = $xpath->evaluate('string(//xml/trade_state)');
			if ((strtolower($code) == 'success') && (strtolower($ret) == 'success') && (strtolower($trade_state) == 'success')) {
				return true;
			}

			if ($xpath->evaluate('string(//xml/return_msg)') == $xpath->evaluate('string(//xml/err_code_des)')) {
				$error = $xpath->evaluate('string(//xml/return_msg)');
			}
			else {
				$error = $xpath->evaluate('string(//xml/return_msg)') . '<br/>' . $xpath->evaluate('string(//xml/err_code_des)');
			}

			return error(-2, $error);
		}

		return error(-1, '未知错误');
	}

	public function isAlipayNotify($gpc)
	{
		global $_W;
		$notify_id = trim($gpc['notify_id']);
		$_obf_DVsWFxkaHlsyQDcBKj8TGjsaCzISDRE_ = trim($gpc['sign']);
		if (empty($notify_id) || empty($_obf_DVsWFxkaHlsyQDcBKj8TGjsaCzISDRE_)) {
			return false;
		}

		$setting = uni_setting($_W['uniacid'], array('payment'));

		if (!is_array($setting['payment'])) {
			return false;
		}

		$alipay = $setting['payment']['alipay'];
		$params = array();

		foreach ($gpc as $key => $value) {
			if (in_array($key, array('sign', 'sign_type', 'i', 'm', 'openid', 'c', 'do', 'p', 'op')) || empty($value)) {
				continue;
			}

			$params[$key] = $value;
		}

		ksort($params, SORT_STRING);
		$string1 = '';

		foreach ($params as $k => $v) {
			$string1 .= $k . '=' . $v . '&';
		}

		$string1 = rtrim($string1, '&') . $alipay['secret'];
		$sign = strtolower(md5($string1));

		if ($_obf_DVsWFxkaHlsyQDcBKj8TGjsaCzISDRE_ != $sign) {
			return false;
		}

		$_obf_DSYnHAUjOC8JOxQTWy8rJwYeLSE2LSI_ = array(
			'ssl' => array('verify_peer' => false, 'verify_peer_name' => false)
			);
		$url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&partner=' . $alipay['partner'] . '&notify_id=' . $notify_id;
		$resp = file_get_contents($url, false, stream_context_create($_obf_DSYnHAUjOC8JOxQTWy8rJwYeLSE2LSI_));
		return preg_match('/true$/i', $resp);
	}

	protected function createNonceStr()
	{
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$str = '';
		$i = 0;

		while ($i < 32) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
			++$i;
		}

		return $str;
	}

	protected function formatQuery($querys, $urlencode = false)
	{
		if (!is_array($querys) || empty($querys)) {
			return NULL;
		}

		ksort($querys);
		$params = array();

		foreach ($querys as $key => $val) {
			if (($key != 'sign') && ($val != NULL) && ($val != 'null')) {
				if ($urlencode) {
					$val = urlencode($val);
				}

				$params[] = $key . '=' . $val;
			}
		}

		return implode('&', $params);
	}
}

if (!defined('IN_IA')) {
	exit('Access Denied');
}

?>
