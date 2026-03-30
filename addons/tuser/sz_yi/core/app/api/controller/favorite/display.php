<?php
// 模块LTD提供
namespace app\api\controller\favorite;

@session_start();
class Display extends \app\api\YZ
{
	public function index()
	{
		$this->_validatePara();
		$openid = m('user')->isLogin();
		$uniacid = \app\api\Request::input('uniacid');
		$favorite_id = \app\api\Request::input('favorite_id');
		$total = $this->_getCount($openid, $uniacid);
		$list = $this->_getList($openid, $uniacid, $favorite_id);
		$this->returnSuccess(array('total' => $total, 'list' => $list));
	}

	private function _getCount($openid, $uniacid)
	{
		$where = array('uniacid' => $uniacid);
		$where[] = 'deleted=0';
		$total = D('MemberFavorite')->where($where)->count();
		return $total;
	}

	private function _getList($openid, $uniacid, $favorite_id)
	{
		$fields = 'f.id,f.goodsid,g.title,g.thumb,g.marketprice,g.productprice';
		$where = array('f.uniacid' => $uniacid);

		if (!empty($favorite_id)) {
			$where['f.id'] = array('lt', $favorite_id);
		}

		$where[] = 'f.deleted=0';
		$list = D('MemberFavorite')->alias('f')->field($fields)->where($where)->join(tablename('sz_yi_goods') . ' as g ON g.id = f.goodsid')->order('f.id desc')->limit('0,10')->select();
		$list = set_medias($list, 'thumb');
		return $list;
	}

	private function _validatePara()
	{
		$validate_fields = array(
			'uniacid'     => array('type' => 'required', 'describe' => '手机号'),
			'favorite_id' => array('type' => 'required', 'describe' => '收藏id', 'required' => false)
			);
		\app\api\Request::filter($validate_fields);
		$_obf_DS8LHAknHAxAJzghOAgkLDQaExgFLwE_ = \app\api\Request::validate($validate_fields);
		return $_obf_DS8LHAknHAxAJzghOAgkLDQaExgFLwE_;
	}
}

?>
