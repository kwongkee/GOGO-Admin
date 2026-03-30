<?php
// Ä£¿éLTDÌá¹©
namespace Pingpp;

class WxpubOAuth
{
	static public function getOpenid($app_id, $app_secret, $code)
	{
		$url = WxpubOAuth::_createOauthUrlForOpenid($app_id, $app_secret, $code);
		$res = self::_getRequest($url);
		$data = json_decode($res, true);
		return $data['openid'];
	}

	static public function createOauthUrlForCode($app_id, $redirect_url, $more_info = false)
	{
		$urlObj = array();
		$urlObj['appid'] = $app_id;
		$urlObj['redirect_uri'] = $redirect_url;
		$urlObj['response_type'] = 'code';
		$urlObj['scope'] = $more_info ? 'snsapi_userinfo' : 'snsapi_base';
		$urlObj['state'] = 'STATE' . '#wechat_redirect';
		$queryStr = http_build_query($urlObj);
		return 'https://open.weixin.qq.com/connect/oauth2/authorize?' . $queryStr;
	}

	static private function _createOauthUrlForOpenid($app_id, $app_secret, $code)
	{
		$urlObj = array();
		$urlObj['appid'] = $app_id;
		$urlObj['secret'] = $app_secret;
		$urlObj['code'] = $code;
		$urlObj['grant_type'] = 'authorization_code';
		$queryStr = http_build_query($urlObj);
		return 'https://api.weixin.qq.com/sns/oauth2/access_token?' . $queryStr;
	}

	static private function _getRequest($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$res = curl_exec($ch);
		curl_close($ch);
		return $res;
	}

	static public function getJsapiTicket($app_id, $app_secret)
	{
		$urlObj = array();
		$urlObj['appid'] = $app_id;
		$urlObj['secret'] = $app_secret;
		$urlObj['grant_type'] = 'client_credential';
		$queryStr = http_build_query($urlObj);
		$accessTokenUrl = 'https://api.weixin.qq.com/cgi-bin/token?' . $queryStr;
		$resp = self::_getRequest($accessTokenUrl);
		$resp = json_decode($resp, true);
		if (!is_array($resp) || isset($resp['errcode'])) {
			return $resp;
		}

		$urlObj = array();
		$urlObj['access_token'] = $resp['access_token'];
		$urlObj['type'] = 'jsapi';
		$queryStr = http_build_query($urlObj);
		$_obf_DT0xPj08GykmGDwoNB8lEVwDNAcNAwE_ = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?' . $queryStr;
		$resp = self::_getRequest($_obf_DT0xPj08GykmGDwoNB8lEVwDNAcNAwE_);
		return json_decode($resp, true);
	}

	static public function getSignature($charge, $jsapi_ticket, $url = NULL)
	{
		if (!isset($charge['credential']) || !isset($charge['credential']['wx_pub'])) {
			return null;
		}

		$credential = $charge['credential']['wx_pub'];
		$_obf_DSFALjsPLTNcHEAkFA1cIiRbPxU4IiI_ = array();
		$_obf_DSFALjsPLTNcHEAkFA1cIiRbPxU4IiI_[] = 'jsapi_ticket=' . $jsapi_ticket;
		$_obf_DSFALjsPLTNcHEAkFA1cIiRbPxU4IiI_[] = 'noncestr=' . $credential['nonceStr'];
		$_obf_DSFALjsPLTNcHEAkFA1cIiRbPxU4IiI_[] = 'timestamp=' . $credential['timeStamp'];

		if (!$url) {
			$requestUri = explode('#', $_SERVER['REQUEST_URI']);
			$scheme = (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) !== 'off') ? 'https' : 'http'));
			$url = $scheme . '://' . $_SERVER['HTTP_HOST'] . $requestUri[0];
		}

		$_obf_DSFALjsPLTNcHEAkFA1cIiRbPxU4IiI_[] = 'url=' . $url;
		return sha1(implode('&', $_obf_DSFALjsPLTNcHEAkFA1cIiRbPxU4IiI_));
	}
}


?>
