<?php
// 模块LTD提供
class Sz_DYi_Common
{
	public function dataMove()
	{
		$dbprefix = 'ewei_shop';
		$_obf_DTgCBTMJFRkqNjQWDwgPJTwnOB8HORE_ = 'sz_yi';
		$result = pdo_fetchall('SHOW TABLES LIKE \'%' . $_obf_DTgCBTMJFRkqNjQWDwgPJTwnOB8HORE_ . '%\'');

		if (!$result) {
			return false;
		}

		foreach ($result as $tables) {
			foreach ($tables as $tablename) {
				$sql = 'drop table `' . $tablename . '`';
				pdo_query($sql);
			}
		}

		$result = pdo_fetchall('SHOW TABLES LIKE \'%' . $dbprefix . '%\'');

		if (!$result) {
			return false;
		}

		foreach ($result as $tables) {
			foreach ($tables as $tablename) {
				$sql = 'rename table `' . $tablename . '` to `' . str_replace($dbprefix, $_obf_DTgCBTMJFRkqNjQWDwgPJTwnOB8HORE_, $tablename) . '`';
				pdo_query($sql);
			}
		}

		if (!pdo_fieldexists('sz_yi_member', 'regtype')) {
			pdo_query('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD    `regtype` tinyint(3) DEFAULT \'1\';');
		}

		if (!pdo_fieldexists('sz_yi_member', 'isbindmobile')) {
			pdo_query('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD    `isbindmobile` tinyint(3) DEFAULT \'0\';');
		}

		if (!pdo_fieldexists('sz_yi_member', 'isjumpbind')) {
			pdo_query('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD    `isjumpbind` tinyint(3) DEFAULT \'0\';');
		}

		if (!pdo_fieldexists('sz_yi_member', 'pwd')) {
			pdo_query('ALTER TABLE  ' . tablename('sz_yi_member') . ' CHANGE  `pwd`  `pwd` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;');
		}

		if (!pdo_fieldexists('sz_yi_goods', 'cates')) {
			pdo_query('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD     `cates` text;');
		}
	}

	public function getSetData($uniacid = 0)
	{
		global $_W;

		if (empty($uniacid)) {
			$uniacid = $_W['uniacid'];
		}

		$set = m('cache')->getArray('sysset', $uniacid);

		if (empty($set)) {
			$set = pdo_fetch('select * from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $uniacid));

			if (empty($set)) {
				$set = array();
			}

			m('cache')->set('sysset', $set, $uniacid);
		}

		return $set;
	}

	public function getSysset($key = '', $uniacid = 0)
	{
		global $_W;
		global $_GPC;
		$set = $this->getSetData($uniacid);
		$allset = unserialize($set['sets']);
		$retsets = array();

		if (!empty($key)) {
			if (is_array($key)) {
				foreach ($key as $k) {
					$retsets[$k] = isset($allset[$k]) ? $allset[$k] : array();
				}
			}
			else {
				$retsets = (isset($allset[$key]) ? $allset[$key] : array());
			}

			return $retsets;
		}

		return $allset;
	}
	/***	 * @params 支付订单信息	 * @alipay 支付配置信息	 * @type   支付类型	 * @openid 用户openid	 */
	public function alipay_build($params, $alipay = array(), $type = 0, $openid = '')
	{
		global $_W;
		$tid = $params['tid'];
		$set = array();
		$set['partner'] = $alipay['partner'];
		$set['seller_id'] = $alipay['account'];

		if (!isMobile()) {
			$set['seller_id'] = $alipay['partner'];
			$set['service'] = 'create_direct_pay_by_user';
		}
		else {
			$set['service'] = 'alipay.wap.create.direct.pay.by.user';
		}

		$set['_input_charset'] = 'utf-8';
		$set['sign_type'] = 'MD5';

		if (empty($type)) {
			$set['notify_url'] = $_W['siteroot'] . 'addons/sz_yi/payment/alipay/notify.php';
			$set['return_url'] = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=order&p=pay&op=return&openid=' . $openid;
		}
		else {
			$set['notify_url'] = $_W['siteroot'] . 'addons/sz_yi/payment/alipay/notify.php';
			$set['return_url'] = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=member&p=recharge&op=return&openid=' . $openid;
		}

		$set['out_trade_no'] = $tid;
		$set['subject'] = $params['title'];
		$set['total_fee'] = $params['fee'];
		$set['payment_type'] = 1;
		$set['body'] = $_W['uniacid'] . ':' . $type;
		$prepares = array();

		foreach ($set as $key => $value) {
			if (($key != 'sign') && ($key != 'sign_type')) {
				$prepares[] = $key . '=' . $value;
			}
		}

		sort($prepares);
		$string = implode($prepares, '&');
		$string .= $alipay['secret'];
		$set['sign'] = md5($string);
		return array('url' => ALIPAY_GATEWAY . '?' . http_build_query($set, '', '&'));
	}


	//通莞微信公众号支付
	public function tgwechat($params, $config){
		global $_W;
		$wOpt = array();
		$package = array();
		$package['account'] = $config['account'];
		$package['payMoney'] = $params['fee'];
		$package['lowOrderId'] = $params['tid'];
		$package['body'] = $params['title'];
		$package['notifyUrl'] = 'http://shop.gogo198.cn/payment/wechat/tgwechatnotify.php';
		$package['openId'] = $params['user'];
		//转换key=value&key=value;
		$str = $this->tostrings($package);
		//拼接加密字串
		$str .= '&key=' . $config['key'];
		//MD5加密字串
		$sign = md5($str);
		//返回加密字串转换成大写字母
		$package['sign'] = strtoupper($sign);
		//数据包转换成json格式
		$data =  json_encode($package);
		//数据请求地址，post形式传输
		$url = 'http://tgjf.833006.biz/tgPosp/payApi/wxJspay';
		//数据请求地址，post形式传输
		$response = $this->ihttp_posts($url,$data);
		//解析json数据
		$response = json_decode($response);
		//直接返回支付URL地址
		return $response->pay_url;
	}


	/**
	 * @数据请求提交POST json
	 * @$url:请求地址
	 * @post_data:请求数据
	 */
	public function ihttp_posts($url,$post_data){
		//初始化	 
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($post_data)));
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	
	/**
	 * 字符串拼接
	 * @arrs :数组数据
	 */
	public function tostrings($arrs){
		ksort($arrs, SORT_STRING);
		$str = '';
		foreach ($arrs as $key => $v ) {
			if ($v=='' || $v == null) {
				continue;
			}
			$str .= $key . '=' . $v . '&';
		}
		$str = trim($str,'&');
		return $str;
	}



	//微信支付
	public function wechat_build($params, $wechat, $type = 0)
	{
		global $_W;
		load()->func('communication');
		if (empty($wechat['version']) && !empty($wechat['signkey'])) {
			$wechat['version'] = 1;
		}

		$wOpt = array();

		if ($wechat['version'] == 1) {
			$wOpt['appId'] = $wechat['appid'];
			$wOpt['timeStamp'] = TIMESTAMP . '';
			$wOpt['nonceStr'] = random(8) . '';
			$package = array();
			$package['bank_type'] = 'WX';
			$package['body'] = urlencode($params['title']);
			$package['attach'] = $_W['uniacid'] . ':' . $type;
			$package['partner'] = $wechat['partner'];
			$package['device_info'] = 'sz_yi';
			$package['out_trade_no'] = $params['tid'];
			$package['total_fee'] = $params['fee'] * 100;
			$package['fee_type'] = '1';
			$package['notify_url'] = $_W['siteroot'] . 'addons/sz_yi/payment/wechat/notify.php';
			$package['spbill_create_ip'] = CLIENT_IP;
			$package['input_charset'] = 'UTF-8';
			ksort($package);
			$string1 = '';

			foreach ($package as $key => $v) {
				if (empty($v)) {
					continue;
				}

				$string1 .= $key . '=' . $v . '&';
			}

			$string1 .= 'key=' . $wechat['key'];
			$sign = strtoupper(md5($string1));
			$string2 = '';

			foreach ($package as $key => $v) {
				$v = urlencode($v);
				$string2 .= $key . '=' . $v . '&';
			}

			$string2 .= 'sign=' . $sign;
			$wOpt['package'] = $string2;
			$string = '';
			$keys = array('appId', 'timeStamp', 'nonceStr', 'package', 'appKey');
			sort($keys);

			foreach ($keys as $key) {
				$v = $wOpt[$key];

				if ($key == 'appKey') {
					$v = $wechat['signkey'];
				}

				$key = strtolower($key);
				$string .= $key . '=' . $v . '&';
			}

			$string = rtrim($string, '&');
			$wOpt['signType'] = 'SHA1';
			$wOpt['paySign'] = sha1($string);
			return $wOpt;
		}

		$package = array();
		$package['appid'] = $wechat['appid'];
		$package['mch_id'] = $wechat['mchid'];
		$package['nonce_str'] = random(8) . '';
		$package['body'] = $params['title'];
		$package['device_info'] = 'sz_yi';
		$package['attach'] = $_W['uniacid'] . ':' . $type;
		$package['out_trade_no'] = $params['tid'];
		$package['total_fee'] = $params['fee'] * 100;
		$package['spbill_create_ip'] = CLIENT_IP;
		$package['notify_url'] = $_W['siteroot'] . 'addons/sz_yi/payment/wechat/notify.php';
		$package['trade_type'] = $params['trade_type'] == 'NATIVE' ? 'NATIVE' : 'JSAPI';
		$package['openid'] = $_W['fans']['from_user'];
		ksort($package, SORT_STRING);
		$string1 = '';

		foreach ($package as $key => $v) {
			if (empty($v)) {
				continue;
			}

			$string1 .= $key . '=' . $v . '&';
		}

		$string1 .= 'key=' . $wechat['signkey'];
		$package['sign'] = strtoupper(md5($string1));
		$dat = array2xml($package);
		$response = ihttp_request('https://api.mch.weixin.qq.com/pay/unifiedorder', $dat);

		if (is_error($response)) {
			return $response;
		}

		$xml = @simplexml_load_string($response['content'], 'SimpleXMLElement', LIBXML_NOCDATA);

		if (strval($xml->return_code) == 'FAIL') {
			return error(-1, strval($xml->return_msg));
		}

		if (strval($xml->result_code) == 'FAIL') {
			return error(-1, strval($xml->err_code) . ': ' . strval($xml->err_code_des));
		}

		$prepayid = $xml->prepay_id;
		$wOpt['appId'] = $wechat['appid'];
		$wOpt['timeStamp'] = TIMESTAMP . '';
		$wOpt['nonceStr'] = random(8) . '';
		$wOpt['package'] = 'prepay_id=' . $prepayid;
		$wOpt['signType'] = 'MD5';

		if ($params['trade_type'] == 'NATIVE') {
			$code_url = (array) $xml->code_url;
			$wOpt['code_url'] = $code_url[0];
		}

		ksort($wOpt, SORT_STRING);

		foreach ($wOpt as $key => $v) {
			$string .= $key . '=' . $v . '&';
		}

		$string .= 'key=' . $wechat['signkey'];
		$wOpt['paySign'] = strtoupper(md5($string));
		return $wOpt;
	}

	public function getAccount()
	{
		global $_W;
		load()->model('account');

		if (!empty($_W['acid'])) {
			return WeAccount::create($_W['acid']);
		}

		$acid = pdo_fetchcolumn('SELECT acid FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid LIMIT 1', array(':uniacid' => $_W['uniacid']));
		return WeAccount::create($acid);
	}

	public function shareAddress()
	{
		global $_W;
		global $_GPC;
		$appid = $_W['account']['key'];
		$secret = $_W['account']['secret'];
		load()->func('communication');
		$url = $_W['siteroot'] . 'app/index.php?' . $_SERVER['QUERY_STRING'];

		if (empty($_GPC['code'])) {
			$oauth2_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appid . '&redirect_uri=' . urlencode($url) . '&response_type=code&scope=snsapi_base&state=123#wechat_redirect';
			header('location: ' . $oauth2_url);
			exit();
		}

		$code = $_GPC['code'];
		$token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $appid . '&secret=' . $secret . '&code=' . $code . '&grant_type=authorization_code';
		$resp = ihttp_get($token_url);
		$token = @json_decode($resp['content'], true);
		if (empty($token) || !is_array($token) || empty($token['access_token']) || empty($token['openid'])) {
			return false;
		}

		$package = array('appid' => $appid, 'url' => $url, 'timestamp' => time() . '', 'noncestr' => random(8, true) . '', 'accesstoken' => $token['access_token']);
		ksort($package, SORT_STRING);
		$addrSigns = array();

		foreach ($package as $k => $v) {
			$addrSigns[] = $k . '=' . $v;
		}

		$string = implode('&', $addrSigns);
		$addrSign = strtolower(sha1(trim($string)));
		$data = array('appId' => $appid, 'scope' => 'jsapi_address', 'signType' => 'sha1', 'addrSign' => $addrSign, 'timeStamp' => $package['timestamp'], 'nonceStr' => $package['noncestr']);
		return $data;
	}

	public function createNO($table, $field, $prefix)
	{
		$billno = date('YmdHis') . random(6, true);

		while (1) {
			$count = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_' . $table) . ' where ' . $field . '=:billno limit 1', array(':billno' => $billno));

			if ($count <= 0) {
				break;
			}

			$billno = date('YmdHis') . random(6, true);
		}

		return $prefix . $billno;
	}

	public function html_images($detail = '')
	{
		$detail = htmlspecialchars_decode($detail);
		preg_match_all('/<img.*?src=[\\\'| "](.*?(?:[\\.gif|\\.jpg|\\.png|\\.jpeg]?))[\\\'|"].*?[\\/]?>/', $detail, $imgs);
		$images = array();

		if (isset($imgs[1])) {
			foreach ($imgs[1] as $img) {
				$im = array('old' => $img, 'new' => save_media($img));
				$images[] = $im;
			}
		}

		foreach ($images as $img) {
			$detail = str_replace($img['old'], $img['new'], $detail);
		}

		return $detail;
	}

	public function getSec($uniacid = 0)
	{
		global $_W;

		if (empty($uniacid)) {
			$uniacid = $_W['uniacid'];
		}

		$set = pdo_fetch('select sec from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $uniacid));

		if (empty($set)) {
			$set = array();
		}

		return $set;
	}

	public function paylog($log = '')
	{
		global $_W;
		$_obf_DQwmGRMGLQoRDCciPUABBTATGBM_IwE_ = m('cache')->getString('paylog', 'global');

		if (!empty($_obf_DQwmGRMGLQoRDCciPUABBTATGBM_IwE_)) {
			$path = IA_ROOT . '/addons/sz_yi/data/paylog/' . $_W['uniacid'] . '/' . date('Ymd');

			if (!is_dir($path)) {
				load()->func('file');
				@mkdirs($path, '0777');
			}

			$file = $path . '/' . date('H') . '.log';
			file_put_contents($file, $log, FILE_APPEND);
		}
	}

	public function checkClose()
	{
		if (strexists($_SERVER['REQUEST_URI'], '/web/')) {
			return NULL;
		}

		$shop = $this->getSysset('shop');

		if (!empty($shop['close'])) {
			if (!empty($shop['closeurl'])) {
				header('location: ' . $shop['closeurl']);
				exit();
			}

			exit("<!DOCTYPE html>\n                    <html>\n                        <head>\n                            <meta name='viewport' content='width=device-width, initial-scale=1, user-scalable=0'>\n                            <title>抱歉，商城暂时关闭</title><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1, user-scalable=0'><link rel='stylesheet' type='text/css' href='https://res.wx.qq.com/connect/zh_CN/htmledition/style/wap_err1a9853.css'>\n                        </head>\n                        <body>\n                        <style type='text/css'>\n                        body { background:#fbfbf2; color:#333;}\n                        img { display:block; width:100%;}\n                        .header {\n                        width:100%; padding:10px 0;text-align:center;font-weight:bold;}\n                        </style>\n                        <div class='page_msg'>\n                        \n                        <div class='inner'><span class='msg_icon_wrp'><i class='icon80_smile'></i></span>" . $shop['closedetail'] . "</div></div>\n                        </body>\n                    </html>");
		}
	}

	public function mylink()
	{
		global $_W;
		$mylink['designer'] = p('designer');
		$mylink['categorys'] = pdo_fetchall('SELECT * FROM ' . tablename('sz_yi_article_category') . ' WHERE uniacid=:uniacid ', array(':uniacid' => $_W['uniacid']));

		if ($mylink['designer']) {
			$mylink['diypages'] = pdo_fetchall('SELECT id,pagetype,setdefault,pagename FROM ' . tablename('sz_yi_designer') . ' WHERE uniacid=:uniacid order by setdefault desc  ', array(':uniacid' => $_W['uniacid']));
		}

		$mylink['article_sys'] = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_article_sys') . ' WHERE uniacid=:uniacid limit 1 ', array(':uniacid' => $_W['uniacid']));
		$mylink['article_sys']['article_area'] = json_decode($mylink['article_sys']['article_area'], true);
		$mylink['area_count'] = sizeof($mylink['article_sys']['article_area']);

		if ($mylink['area_count'] == 0) {
			$mylink['article_sys']['article_area'][0]['province'] = '';
			$mylink['article_sys']['article_area'][0]['city'] = '';
			$mylink['area_count'] = 1;
		}

		$mylink['goodcates'] = pdo_fetchall('SELECT id,name,parentid FROM ' . tablename('sz_yi_category') . ' WHERE enabled=:enabled and uniacid= :uniacid  ', array(':uniacid' => $_W['uniacid'], ':enabled' => '1'));
		return $mylink;
	}

	public function wechat_native_build($params, $wechat, $type = 0)
	{
		global $_W;
		load()->func('communication');
		$package = array();
		$package['appid'] = $wechat['appid'];
		$package['mch_id'] = $wechat['mchid'];
		$package['nonce_str'] = random(8) . '';
		$package['body'] = $params['title'];
		$package['device_info'] = isset($params['device_info']) ? 'sz_yi:' . $params['device_info'] : 'sz_yi';
		$package['attach'] = (isset($params['uniacid']) ? $params['uniacid'] : $_W['uniacid']) . ':' . $type;
		$package['out_trade_no'] = $params['tid'];
		$package['total_fee'] = $params['fee'] * 100;
		$package['spbill_create_ip'] = CLIENT_IP;
		$package['product_id'] = $params['goods_id'];

		if (!empty($params['goods_tag'])) {
			$package['goods_tag'] = $params['goods_tag'];
		}

		$package['time_start'] = date('YmdHis', TIMESTAMP);
		$package['time_expire'] = date('YmdHis', TIMESTAMP + 3600);
		$package['notify_url'] = empty($params['notify_url']) ? $_W['siteroot'] . 'addons/sz_yi/payment/wechat/notify.php' : $params['notify_url'];
		$package['trade_type'] = 'NATIVE';
		ksort($package, SORT_STRING);
		$string1 = '';

		foreach ($package as $key => $v) {
			if (empty($v)) {
				continue;
			}

			$string1 .= $key . '=' . $v . '&';
		}

		$string1 .= 'key=' . $wechat['apikey'];
		$package['sign'] = strtoupper(md5($string1));
		$dat = array2xml($package);
		$response = ihttp_request('https://api.mch.weixin.qq.com/pay/unifiedorder', $dat);

		if (is_error($response)) {
			return $response;
		}

		$xml = simplexml_load_string($response['content'], 'SimpleXMLElement', LIBXML_NOCDATA);

		if (strval($xml->return_code) == 'FAIL') {
			return error(-1, strval($xml->return_msg));
		}

		if (strval($xml->result_code) == 'FAIL') {
			return error(-1, strval($xml->err_code) . ': ' . strval($xml->err_code_des));
		}

		libxml_disable_entity_loader(true);
		$result = json_decode(json_encode($xml), true);
		return $result;
	}
}

if (!defined('IN_IA')) {
	exit('Access Denied');
}

?>
