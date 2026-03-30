<?php
// 模块LTD提供
namespace app\api\controller\address;

@session_start();
class Get extends \app\api\YZ
{
	public function index()
	{
		$this->_validatePara();
		$openid = m('user')->isLogin();
		$member = m('member')->getMember($openid);
		$id = \app\api\Request::query('id');
		$uniacid = \app\api\Request::query('uniacid');
		$data = D('MemberAddress')->where(array('uniacid' => $uniacid, 'openid' => $openid, 'id' => $id) + array('deleted=0'))->find();

		if (empty($data)) {
			$this->returnError('找不到该地址');
		}

		$info = array('address' => $data, 'member' => $member);
		$this->returnSuccess($info);
	}

	private function _validatePara()
	{
		$validate_fields = array(
			'uniacid' => array('type' => 'required', 'describe' => ''),
			'id'      => array('type' => 'required', 'describe' => '手机号', 'required' => false)
			);
		\app\api\Request::filter($validate_fields);
		$_obf_DS8LHAknHAxAJzghOAgkLDQaExgFLwE_ = \app\api\Request::validate($validate_fields);
		return $_obf_DS8LHAknHAxAJzghOAgkLDQaExgFLwE_;
	}
}

?>
