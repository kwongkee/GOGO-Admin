<?php
// 模块LTD提供
namespace app\api\controller\member;

@session_start();
class SentCode extends \app\api\YZ
{
	public function index()
	{
		$_obf_DS8LHAknHAxAJzghOAgkLDQaExgFLwE_ = $this->_validatePara();

		if (!empty($_obf_DS8LHAknHAxAJzghOAgkLDQaExgFLwE_)) {
			$this->returnError($_obf_DS8LHAknHAxAJzghOAgkLDQaExgFLwE_);
		}

		$para = $this->getPara();

		if (D('Member')->has($para)) {
			$this->returnError('该手机号已被注册！不能获取验证码。');
		}

		$mobile = $para['mobile'];
		$code = rand(1000, 9999);
		$_SESSION['codetime'] = time();
		$_SESSION['code'] = $code;
		$_SESSION['code_mobile'] = $mobile;
		$issendsms = $this->sendSms($mobile, $code);
		$set = m('common')->getSysset();

		if ($set['sms']['type'] == 1) {
			if ($issendsms['SubmitResult']['code'] == 2) {
				$this->returnSuccess();
				return NULL;
			}

			$this->returnError($issendsms['SubmitResult']['msg']);
			return NULL;
		}

		if (isset($issendsms['result']['success'])) {
			$this->returnSuccess();
			return NULL;
		}

		$this->returnError($issendsms['msg']);
	}

	private function _validatePara()
	{
		$validate_fields = array(
			'mobile'  => array('type' => 'required', 'describe' => '手机号'),
			'uniacid' => array('type' => 'required', 'describe' => '公众号id')
			);
		\app\api\Request::filter($validate_fields);
		$_obf_DS8LHAknHAxAJzghOAgkLDQaExgFLwE_ = \app\api\Request::validate($validate_fields);
		return $_obf_DS8LHAknHAxAJzghOAgkLDQaExgFLwE_;
	}
}

?>
