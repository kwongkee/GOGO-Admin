<?php
// 模块LTD提供
namespace admin\api\controller\order;

class Display extends \admin\api\YZ
{
	public function __construct()
	{
		parent::__construct();
		$this->ca('order.view.status_1|order.view.status0|order.view.status1|order.view.status2|order.view.status3|order.view.status4|order.view.status5');
	}

	public function index()
	{
		$para = $this->getPara();
		$order_model = new \admin\api\model\order();
		$order_list = $order_model->getList(array('id' => intval($para['order_id']), 'status' => $para['status'], 'paytype' => intval($para['paytype']), 'is_supplier_uid' => $this->isSupplier()));

		if (count($order_list) == 0) {
			$this->returnSuccess(array(), '暂无数据');
		}

		dump($order_list);
		$this->returnSuccess($order_list);
	}
}

?>
