<?php
// 模块LTD提供
namespace app\api\controller\member;

@session_start();
class Login extends \app\api\YZ
{
	public function index()
	{
		$_obf_DS8LHAknHAxAJzghOAgkLDQaExgFLwE_ = $this->_validatePara();

		if (!empty($_obf_DS8LHAknHAxAJzghOAgkLDQaExgFLwE_)) {
			$this->returnError($_obf_DS8LHAknHAxAJzghOAgkLDQaExgFLwE_);
		}

		$para = $this->getPara();
		$info = D('Member')->field('id,openid,nickname,mobile')->where($para)->find();

		if (empty($info)) {
			$this->returnError('用户名或密码错误');
		}

		$this->_setCookie($info['openid'], $info['mobile']);
		$this->returnSuccess($info);
	}

	private function _validatePara()
	{
		$validate_fields = array(
			'mobile'  => array('type' => 'required', 'describe' => '手机号'),
			'pwd'     => array('type' => 'required', 'describe' => '密码'),
			'uniacid' => array('type' => 'required', 'describe' => '公众号id')
			);
		\app\api\Request::filter($validate_fields);
		$_obf_DS8LHAknHAxAJzghOAgkLDQaExgFLwE_ = \app\api\Request::validate($validate_fields);
		return $_obf_DS8LHAknHAxAJzghOAgkLDQaExgFLwE_;
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
			setcookie($cookieid, base64_encode($openid), time() + (3600 * 24 * 7), '/');
		}
		else {
			setcookie($cookieid, base64_encode($openid), 0, '/');
		}

		setcookie('member_mobile', $mobile);
	}
}

?>
