<?php
// 模块LTD提供
namespace app\api\controller\member;

@session_start();
class Register extends \app\api\YZ
{
	public function index()
	{
		$_obf_DS8LHAknHAxAJzghOAgkLDQaExgFLwE_ = $this->_validatePara();

		if (!empty($_obf_DS8LHAknHAxAJzghOAgkLDQaExgFLwE_)) {
			$this->returnError($_obf_DS8LHAknHAxAJzghOAgkLDQaExgFLwE_);
		}

		$para = $this->getPara();

		if (!empty($_obf_DSUYGRY0HQQoFxM7CSgwGxU0EQoDMxE_)) {
			$this->returnError($_obf_DSUYGRY0HQQoFxM7CSgwGxU0EQoDMxE_);
		}

		if (D('Member')->has(array_part('mobile,uniacid', $para))) {
			$this->returnError('该手机号已被注册！');
		}

		if (\app\api\Request::has('referral') && D('Sysset')->appReferral($para['uniacid'])) {
			$referral = D('Member')->has(array('uniacid' => $para['uniacid'], 'referralsn' => $para['referralsn']));

			if (empty($referral)) {
				$this->returnError('推荐码无效！');
			}
		}

		$open_id = $this->_createMember($para['uniacid'], $para['mobile'], $para['pwd']);

		if (!empty($referral)) {
			$this->_saveReferralInfo($para['uniacid'], $para['mobile'], $referral);
		}

		$this->_setSessionAndCookie($para['uniacid'], $para['mobile'], $open_id);
		$this->returnSuccess();
	}

	private function _validatePara()
	{
		$validate_fields = array(
			'mobile'   => array('type' => '', 'describe' => '手机号'),
			'pwd'      => array('type' => '', 'describe' => '密码'),
			'uniacid'  => array('type' => '', 'describe' => '公众号id'),
			'code'     => array('type' => '', 'describe' => '公众号id'),
			'referral' => array('required' => false, 'type' => '', 'describe' => '推荐码')
			);
		\app\api\Request::filter($validate_fields);
		$_obf_DS8LHAknHAxAJzghOAgkLDQaExgFLwE_ = \app\api\Request::validate($validate_fields);
		return $_obf_DS8LHAknHAxAJzghOAgkLDQaExgFLwE_;
	}

	private function _saveReferralInfo($uniacid, $mobile, $referral)
	{
		$member = pdo_fetch('select * from ' . tablename('sz_yi_member') . ' where mobile=:mobile and pwd!="" and uniacid=:uniacid limit 1', array(':uniacid' => $uniacid, ':mobile' => $mobile));

		if (!$member['agentid']) {
			$m_data = array('agentid' => $referral['id'], 'agenttime' => time(), 'status' => 1, 'isagent' => 1);

			if ($referral['id'] != 0) {
				$this->upgradeLevelByAgent($referral['id']);
			}

			pdo_update('sz_yi_member', $m_data, array('mobile' => $mobile, 'uniacid' => $uniacid));
			m('member')->responseReferral($this->yzShopSet, $referral, $member);
		}
	}

	private function _setSessionAndCookie($uniacid, $mobile, $openid)
	{
		$lifeTime = 24 * 3600 * 3;
		session_set_cookie_params($lifeTime);
		@session_start();
		$cookieid = '__cookie_sz_yi_userid_' . $uniacid;
		setcookie('member_mobile', $mobile);
		setcookie($cookieid, base64_encode($openid));
	}

	private function _createMember($uniacid, $mobile, $password)
	{
		$openid = pdo_fetchcolumn('select openid from ' . tablename('sz_yi_member') . ' where mobile=:mobile and uniacid=:uniacid limit 1', array(':uniacid' => $uniacid, ':mobile' => $mobile));

		if (empty($openid)) {
			$member_data = array('uniacid' => $uniacid, 'uid' => 0, 'openid' => 'u' . md5($mobile), 'mobile' => $mobile, 'pwd' => md5($password), 'createtime' => time(), 'status' => 0, 'regtype' => 2);

			if (is_app()) {
				$member_data['bindapp'] = 1;
			}

			if (!is_weixin()) {
				$member_data['nickname'] = $mobile;
				$member_data['avatar'] = 'http://' . $_SERVER['HTTP_HOST'] . '/addons/sz_yi/template/mobile/default/static/images/photo-mr.jpg';
			}

			pdo_insert('sz_yi_member', $member_data);
			$openid = $member_data['openid'];
		}
		else {
			$member_data = array('pwd' => md5($password), 'regtype' => 1, 'isbindmobile' => 1);
			pdo_update('sz_yi_member', $member_data, array('mobile' => $mobile, 'uniacid' => $uniacid));
		}

		return $openid;
	}

	private function _checkCode($code, $mobile)
	{
		$message = '';

		if (($_SESSION['codetime'] + (60 * 5)) < time()) {
			$message = '验证码已过期,请重新获取';
		}

		if ($_SESSION['code'] != $code) {
			$message = '验证码错误,请重新获取';
		}

		if ($_SESSION['code_mobile'] != $mobile) {
			$message = '注册手机号与验证码不匹配！';
		}

		return $message;
	}

	private function _setCookie($openid, $mobile)
	{
		global $_W;

		if (is_app()) {
			$lifeTime = 24 * 3600 * 3 * 100;
		}
		else {
			$lifeTime = 24 * 3600 * 3;
		}

		session_set_cookie_params($lifeTime);
		$cookieid = '__cookie_sz_yi_userid_' . $_W['uniacid'];

		if (is_app()) {
			setcookie($cookieid, base64_encode($openid), time() + (3600 * 24 * 7));
		}
		else {
			setcookie($cookieid, base64_encode($openid));
		}

		setcookie('member_mobile', $mobile);
	}
}

?>
