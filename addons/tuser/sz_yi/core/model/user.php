<?php
// 模块LTD提供
class Sz_DYi_User
{
	private $sessionid;

	public function __construct()
	{
		global $_W;
		$this->sessionid = '__cookie_sz_yi_201507200000_' . $_W['uniacid'];
	}

	public function getOpenid()
	{
		$userinfo = $this->getInfo(false, true);
		return $userinfo['openid'];
	}

	public function getPerOpenid()
	{
		global $_W;
		global $_GPC;
		$lifeTime = 24 * 3600 * 3;
		session_set_cookie_params($lifeTime);
		@session_start();
		$cookieid = '__cookie_sz_yi_userid_' . $_W['uniacid'];
		$openid = base64_decode($_COOKIE[$cookieid]);

		if (!empty($openid)) {
			return $openid;
		}

		load()->func('communication');
		$appId = $_W['account']['key'];
		$appSecret = $_W['account']['secret'];
		$access_token = '';
		$code = $_GPC['code'];
		$url = $_W['siteroot'] . 'app/index.php?' . $_SERVER['QUERY_STRING'];

		if (empty($code)) {
			$authurl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appId . '&redirect_uri=' . urlencode($url) . '&response_type=code&scope=snsapi_base&state=123#wechat_redirect';
			header('location: ' . $authurl);
			exit();
		}
		else {
			$tokenurl = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $appId . '&secret=' . $appSecret . '&code=' . $code . '&grant_type=authorization_code';
			$resp = ihttp_get($tokenurl);
			$token = @json_decode($resp['content'], true);
			if (!empty($token) && is_array($token) && ($token['errmsg'] == 'invalid code')) {
				$authurl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appId . '&redirect_uri=' . urlencode($url) . '&response_type=code&scope=snsapi_base&state=123#wechat_redirect';
				header('location: ' . $authurl);
				exit();
			}

			if (is_array($token) && !empty($token['openid'])) {
				$access_token = $token['access_token'];
				$openid = $token['openid'];
				setcookie($cookieid, base64_encode($openid));
			}
			else {
				$querys = explode('&', $_SERVER['QUERY_STRING']);
				$_obf_DTYiQCIvKTUjLz4HNDFcCgItDz4LHCI_ = array();

				foreach ($querys as $q) {
					if (!strexists($q, 'code=') && !strexists($q, 'state=') && !strexists($q, 'from=') && !strexists($q, 'isappinstalled=')) {
						$_obf_DTYiQCIvKTUjLz4HNDFcCgItDz4LHCI_[] = $q;
					}
				}

				$rurl = $_W['siteroot'] . 'app/index.php?' . implode('&', $_obf_DTYiQCIvKTUjLz4HNDFcCgItDz4LHCI_);
				$authurl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appId . '&redirect_uri=' . urlencode($rurl) . '&response_type=code&scope=snsapi_base&state=123#wechat_redirect';
				header('location: ' . $authurl);
				exit();
			}
		}

		return $openid;
	}

	public function isLogin()
	{
		global $_W;
		global $_GPC;
		@session_start();
		$cookieid = '__cookie_sz_yi_userid_' . $_W['uniacid'];
		$openid = base64_decode($_COOKIE[$cookieid]);
		if (empty($_SERVER['HTTP_USER_AGENT']) && empty($openid) && $_GPC['token']) {
			$openid = $_GPC['token'];
		}

		if (!empty($openid)) {
			if (is_app()) {
				$result = pdo_fetch('select id from ' . tablename('sz_yi_member') . ' WHERE  openid=:openid and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $openid));

				if (empty($result)) {
					setcookie($cookieid, '', time() - 1);
					header('Location:/app/index.php?i=' . $_W['uniacid'] . '&c=entry&p=login&do=member&m=sz_yi');
					exit();
				}
			}

			return $openid;
		}

		return false;
	}

	public function getUserInfo()
	{
		global $_W;
		global $_GPC;
		if (($_GPC['p'] == 'return') && ($_GPC['method'] == 'task')) {
			return NULL;
		}

		if (($_GPC['p'] == 'recharge') && ($_GPC['method'] == 'mobile_data_back')) {
			return NULL;
		}

		if (($_GPC['p'] == 'yunbi') && ($_GPC['method'] == 'task')) {
			return NULL;
		}

		if (($_GPC['p'] == 'ranking') && ($_GPC['method'] == 'commission')) {
			return NULL;
		}

		if (($_GPC['p'] == 'area') && ($_GPC['method'] == 'area_list')) {
			return NULL;
		}

		if (($_GPC['p'] == 'area') && ($_GPC['method'] == 'area')) {
			return NULL;
		}

		if (($_GPC['p'] == 'area') && ($_GPC['method'] == 'area_detail')) {
			return NULL;
		}

		if (($_GPC['p'] == 'article') && ($_GPC['method'] == 'article_pc')) {
			return NULL;
		}

		if (($_GPC['p'] == 'verify') && ($_GPC['method'] == 'store_index')) {
			return NULL;
		}

		if (($_GPC['p'] == 'verify') && ($_GPC['method'] == 'store_list')) {
			return NULL;
		}

		if (($_GPC['p'] == 'verify') && ($_GPC['method'] == 'store_detail')) {
			return NULL;
		}

		$_obf_DQ42KlscNTU8MwY_EQsoDUA2N1wlHBE_ = array('address', 'commission', 'cart');
		$noLoginList = array('category', 'login', 'receive', 'close', 'designer', 'register', 'sendcode', 'bindmobile', 'forget', 'home', 'fund', 'discuz');
		$_obf_DTM9PTgqDDQuNTknKRANKw85IjwqOCI_ = array('shop', 'login', 'register');
		if (!$_GPC['p'] && ($_GPC['do'] == 'shop')) {
			return NULL;
		}

		if (!empty($_GPC['is_helper']) && ($_GPC['p'] == 'article')) {
			return NULL;
		}

		if ((!in_array($_GPC['p'], $noLoginList) && !in_array($_GPC['do'], $_obf_DTM9PTgqDDQuNTknKRANKw85IjwqOCI_)) || in_array($_GPC['p'], $_obf_DQ42KlscNTU8MwY_EQsoDUA2N1wlHBE_)) {
			if (($_GPC['method'] != 'myshop') || ($_GPC['c'] != 'entry')) {
				$openid = $this->isLogin();
				if (!$openid && ($_GPC['p'] != 'cart')) {
					if ($_GPC['do'] != 'runtasks') {
						setcookie('preUrl', $_W['siteurl']);
					}

					$mid = ($_GPC['mid'] ? '&mid=' . $_GPC['mid'] : '');
					$url = '/app/index.php?i=' . $_W['uniacid'] . '&c=entry&p=login&do=member&m=sz_yi' . $mid;
					redirect($url);
					return NULL;
				}

				$userinfo = array('openid' => $openid, 'headimgurl' => '');
				return $userinfo;
			}
		}
		else {
			if (is_app() && ($_GPC['p'] == 'index') && ($_GPC['do'] == 'shop')) {
				$openid = $this->isLogin();
				$userinfo = array('openid' => $openid, 'headimgurl' => '');
				return $userinfo;
			}
		}
	}

	public function getInfo($base64 = false, $debug = false)
	{
		global $_W;
		global $_GPC;
		if (!is_weixin() && !is_app_api()) {
			return $this->getUserInfo();
		}

		$userinfo = array();

		if (SZ_YI_DEBUG) {
			$userinfo = array('openid' => 'oVwSVuJXB7lGGc93d0gBXQ_h-czc', 'nickname' => '小萝莉', 'headimgurl' => '', 'province' => '香港', 'city' => '九龙');
		}
		else {
			if (empty($_GPC['directopenid'])) {
				$userinfo = mc_oauth_userinfo();
			}
			else {
				$userinfo = array('openid' => $this->getPerOpenid());
			}

			$need_openid = false;

			if ($_W['container'] != 'wechat') {
				if (($_GPC['do'] == 'order') && ($_GPC['p'] == 'pay')) {
					$need_openid = false;
				}

				if (($_GPC['do'] == 'member') && ($_GPC['p'] == 'recharge')) {
					$need_openid = false;
				}

				if (($_GPC['do'] == 'plugin') && ($_GPC['p'] == 'article') && ($_GPC['preview'] == '1')) {
					$need_openid = false;
				}
			}
		}

		if ($base64) {
			return urlencode(base64_encode(json_encode($userinfo)));
		}

		return $userinfo;
	}

	public function oauth_info()
	{
		global $_W;
		global $_GPC;

		if ($_W['container'] != 'wechat') {
			if (($_GPC['do'] == 'order') && ($_GPC['p'] == 'pay')) {
				return array();
			}

			if (($_GPC['do'] == 'member') && ($_GPC['p'] == 'recharge')) {
				return array();
			}
		}

		$lifeTime = 24 * 3600 * 3;
		session_set_cookie_params($lifeTime);
		@session_start();
		$sessionid = '__cookie_sz_yi_201507100000_' . $_W['uniacid'];
		$session = json_decode(base64_decode($_SESSION[$sessionid]), true);
		$openid = (is_array($session) ? $session['openid'] : '');
		$nickname = (is_array($session) ? $session['openid'] : '');

		if (!empty($openid)) {
			return $session;
		}

		load()->func('communication');
		$appId = $_W['account']['key'];
		$appSecret = $_W['account']['secret'];
		$access_token = '';
		$code = $_GPC['code'];
		$url = $_W['siteroot'] . 'app/index.php?' . $_SERVER['QUERY_STRING'];

		if (empty($code)) {
			$authurl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appId . '&redirect_uri=' . urlencode($url) . '&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect';
			header('location: ' . $authurl);
			exit();
		}
		else {
			$tokenurl = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $appId . '&secret=' . $appSecret . '&code=' . $code . '&grant_type=authorization_code';
			$resp = ihttp_get($tokenurl);
			$token = @json_decode($resp['content'], true);
			if (!empty($token) && is_array($token) && ($token['errmsg'] == 'invalid code')) {
				$authurl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appId . '&redirect_uri=' . urlencode($url) . '&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect';
				header('location: ' . $authurl);
				exit();
			}

			if (empty($token) || !is_array($token) || empty($token['access_token']) || empty($token['openid'])) {
				exit('获取token失败,请重新进入!');
			}
			else {
				$access_token = $token['access_token'];
				$openid = $token['openid'];
			}
		}

		$infourl = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $access_token . '&openid=' . $openid . '&lang=zh_CN';
		$resp = ihttp_get($infourl);
		$userinfo = @json_decode($resp['content'], true);

		if (isset($userinfo['nickname'])) {
			$_SESSION[$sessionid] = base64_encode(json_encode($userinfo));
			return $userinfo;
		}

		exit('获取用户信息失败，请重新进入!');
	}

	public function followed($openid = '')
	{
		global $_W;
		$followed = !empty($openid);

		if ($followed) {
			$mf = pdo_fetch('select follow from ' . tablename('mc_mapping_fans') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':openid' => $openid, ':uniacid' => $_W['uniacid']));
			$followed = $mf['follow'] == 1;
		}

		return $followed;
	}
}

if (!defined('IN_IA')) {
	exit('Access Denied');
}

?>
