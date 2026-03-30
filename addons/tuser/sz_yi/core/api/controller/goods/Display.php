<?php
// 模块LTD提供
namespace api\controller\goods;

class Display extends \api\YZ
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$para = $this->getPara();
		$this->ca('shop.goods.view');
		$goods_list = $this->_getGoodsList($para);

		if (empty($goods_list)) {
			$this->returnSuccess($goods_list, '暂无数据');
		}

		dump($goods_list);
		$this->returnSuccess($goods_list);
	}

	public function getCateTree()
	{
		$para = $this->getPara();
		$goods_model = new \api\model\goods();
		$_obf_DSoYHBMtCwcnPyEIKTk1DQc0KgosAQE_ = $goods_model->getCateTree($para['uniacid']);
		dump($_obf_DSoYHBMtCwcnPyEIKTk1DQc0KgosAQE_);
		$this->returnSuccess($_obf_DSoYHBMtCwcnPyEIKTk1DQc0KgosAQE_);
	}

	private function _getGoodsList($para)
	{
		$goods_model = new \api\model\goods();
		$fields = 'title,thumb,marketprice,total,sales,id as goods_id,status';
		$goods_list = $goods_model->getList(array('id' => $para['goods_id'], 'uniacid' => $para['uniacid'], 'status' => $para['status'], 'uid' => $para['uid'], 'keyword' => $para['keyword'], 'pcate' => $para['pcate'], 'ccate' => $para['ccate']), $fields);
		return $goods_list;
	}
}

?>
