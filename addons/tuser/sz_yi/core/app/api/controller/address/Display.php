<?php
// 模块LTD提供
namespace app\api\controller\address;

@session_start();
class Display extends \app\api\YZ
{
	public function index()
	{
		$this->_validatePara();
		$openid = m('user')->isLogin();
		$uniacid = \app\api\Request::input('uniacid');
		$address_id = \app\api\Request::input('address_id');
		$total = $this->_getCount($openid, $uniacid);
		$list = $this->_getList($openid, $uniacid, $address_id);
		$this->returnSuccess(array('total' => $total, 'list' => $list));
	}

	private function _getCount($openid, $uniacid)
	{
		$where = array('openid' => $openid, 'uniacid' => $uniacid);
		$where[] = 'deleted=0';
		$total = D('MemberAddress')->where($where)->count();
		return $total;
	}

	private function _getList($openid, $uniacid, $address_id)
	{
		$fields = '*';
		$where = array('openid' => $openid, 'uniacid' => $uniacid);

		if (!empty($address_id)) {
			$where['a.id'] = array('lt', $address_id);
		}

		$where[] = 'deleted=0';
		$list = D('MemberAddress')->alias('a')->field($fields)->where($where)->order('id desc')->limit('0,10')->select();
		return $list;
	}

	private function _validatePara()
	{
		$validate_fields = array(
			'uniacid'    => array('type' => 'required', 'describe' => ''),
			'address_id' => array('type' => 'required', 'describe' => '手机号', 'required' => false)
			);
		\app\api\Request::filter($validate_fields);
		$_obf_DS8LHAknHAxAJzghOAgkLDQaExgFLwE_ = \app\api\Request::validate($validate_fields);
		return $_obf_DS8LHAknHAxAJzghOAgkLDQaExgFLwE_;
	}
}

?>
