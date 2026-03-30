<?php
// 模块LTD提供
namespace admin\api\model;

class order
{
	protected $name_map = array(
		'pay_type' => array(0 => '未支付', 1 => '余额支付', 11 => '后台付款', 2 => '在线支付', 21 => '微信支付', 22 => '支付宝支付', 23 => '银联支付', 3 => '货到付款'),
		'status'   => array(-1 => '已关闭', 0 => '待付款', 1 => '待发货', 2 => '待收货', 3 => '已完成'),
		'r_type'   => array('退款', '退货退款', '换货')
		);

	public function __construct()
	{
	}

	public function getPayTypeName()
	{
		return $this->name_map['pay_type'];
	}

	public function getInfo($para, $fields = '*')
	{
		$order_info = pdo_fetch('SELECT ' . $fields . ' FROM ' . tablename('sz_yi_order') . ' WHERE id = :id and uniacid=:uniacid', array(':id' => $para['id'], ':uniacid' => $para['uniacid']));
		$order_info = $this->_formatOrderInfo($order_info);
		return $order_info;
	}

	public function getList($para)
	{
		global $_W;
		$condition[] = ' 1';

		if ($para['status'] !== '') {
			$condition['status'] = $this->_getStatusCondition((int) $para['status']);
		}

		if ((int) $para['pay_type']) {
			$condition['pay_type'] = $this->_getPayTypeCondition($para['pay_type']);
		}

		if ($para['is_supplier_uid']) {
			$condition['supplier'] = $this->_getSupplierCondition($_W['uid']);
		}

		if (!empty($para['id'])) {
			$condition['id'] = 'AND o.id < ' . $para['id'];
		}

		$condition['other'] = 'AND o.uniacid = :uniacid and o.deleted=0';
		$paras = array(':uniacid' => $_W['uniacid']);
		$condition_str = implode(' ', $condition);
		$sql = "select o.ordersn,o.status,o.price ,o.id as order_id,o.changedispatchprice,o.changeprice,r.rtype,r.status as rstatus,o.isverify,o.isvirtual,o.addressid\nfrom " . tablename('sz_yi_order') . ' o' . " \nleft join " . tablename('sz_yi_order_refund') . ' r on r.id =o.refundid ' . " \nleft join " . tablename('sz_yi_member') . ' m on m.openid=o.openid and m.uniacid =  o.uniacid ' . " \nleft join " . tablename('sz_yi_dispatch') . ' d on d.id = o.dispatchid ' . " \nleft join " . tablename('sz_yi_member') . ' sm on sm.openid = o.verifyopenid and sm.uniacid=o.uniacid' . " \nleft join " . tablename('sz_yi_saler') . ' s on s.openid = o.verifyopenid and s.uniacid=o.uniacid' . "  \nwhere " . $condition_str . ' ORDER BY o.id DESC LIMIT 0,10 ';
		$list = pdo_fetchall($sql, $paras);

		foreach ($list as &$order_item) {
			$order_item = $this->_formatOrderInfo($order_item);
		}

		return $list;
	}

	private function _formatOrderInfo($order_info)
	{
		global $_W;
		$pay_type = $order_info['paytype'];
		$order_status = $order_info['status'];
		$order_info['status_name'] = $this->_getStatusName($order_info['status'], $pay_type, $order_info);
		$refund_name = $this->_getRefundName($order_status, $order_info['rstatus'], $order_info['refundtime'], $order_info['rtype']);
		$order_info['status_name'] = !empty($refund_name) ? $refund_name : $order_info['status_name'];
		$order_info['pay_type_name'] = $this->_getPayTypeName($pay_type);
		$order_info['button_info'] = $this->_getButton($pay_type, $order_status, $order_info['addressid'], $order_info['isverify'], $order_info['redstatus']);
		$order_goods = $this->getOrderGoods($order_info['order_id'], $_W['uniacid']);
		$order_info['goods'] = $order_goods;
		return $order_info;
	}

	private function _getStatusName($order_status, $pay_type, $order_info)
	{
		$_obf_DTdcHhVbGhQWKjgFBC0GOB44KwUUHwE_ = $this->name_map['status'];
		$status_name = $_obf_DTdcHhVbGhQWKjgFBC0GOB44KwUUHwE_[$order_status];

		switch ($order_status) {
		case '0':
			if ($pay_type == 3) {
				$status_name = '待发货';
			}

			break;

		case '1':
			if ($order_info['isverify'] == 1) {
				$status_name = '待使用';
			}
			else {
				if (empty($order_info['addressid'])) {
					$status_name = '待取货';
				}
			}

			break;

		case '3':
			if (empty($order_info['iscomment'])) {
				$status_name = '待评价';
			}

			break;
		}

		return $status_name;
	}

	private function _getRefundName($order_status, $refund_status, $refund_time, $refund_type)
	{
		$_obf_DQIfWwwPPTUCLhMDXCIEEhomBhg7BTI_ = $this->name_map['r_type'];

		if ($order_status == -1) {
			if (!empty($refund_time)) {
				if ($refund_status == 1) {
					$refund_name = '已' . $_obf_DQIfWwwPPTUCLhMDXCIEEhomBhg7BTI_[$refund_type];
				}
			}
		}

		return $refund_name;
	}

	private function _getButton($pay_type, $order_status, $address_id, $is_verify, $red_status = 0)
	{
		$_obf_DSQRDiceNwcbNScOXAYcPBE5IhIrMBE_ = array('' => '', '确认付款' => 1, '确认发货' => 2, '确认核销' => 3, '确认取货' => 4, '确认收货' => 5, '取消发货' => 6, '补发红包' => 7, '查看物流' => 8);
		$button_name = '';

		if (empty($order_status)) {
			if (cv('order.op.pay')) {
				if ($pay_type == 3) {
					$button_name = '确认发货';
				}
				else {
					$button_name = '确认付款';
				}
			}
		}
		else if ($order_status == 1) {
			if (!empty($address_id)) {
				if (cv('order.op.send')) {
					$button_name = '确认发货';
				}
			}
			else if ($is_verify) {
				if (cv('order.op.verify')) {
					$button_name = '确认核销';
				}
			}
			else {
				if (cv('order.op.fetch')) {
					$button_name = '确认取货';
				}
			}
		}
		else if ($order_status == 2) {
			if (!empty($address_id)) {
				$button_name = '查看物流';
			}
		}
		else {
			if ($order_status == 3) {
				if (!empty($address_id)) {
					$button_name = '查看物流';
				}
			}
		}

		$value = $_obf_DSQRDiceNwcbNScOXAYcPBE5IhIrMBE_[$button_name];
		$name = $button_name;
		$button = array('name' => $name, 'value' => $value);
		return $button;
	}

	private function _getPayTypeName($pay_type)
	{
		$_obf_DSxcHgcvLQQ0LgwUMxIDBiM_CCgcGxE_ = $this->name_map['pay_type'];
		$pay_type_name = $_obf_DSxcHgcvLQQ0LgwUMxIDBiM_CCgcGxE_[$pay_type];
		return $pay_type_name;
	}

	public function getRefundInfo($orer_id, $uniacid)
	{
		$refund = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_order_refund') . ' WHERE orderid = :orderid and uniacid=:uniacid order by id desc', array(':orderid' => $orer_id, ':uniacid' => $uniacid));

		if (!empty($refund)) {
			if (!empty($refund['imgs'])) {
				$refund['imgs'] = iunserializer($refund['imgs']);
			}

			$refund['refundtype'] = array('name' => $this->name_map['r_type'][$refund['refundtype']], 'value' => $refund['refundtype']);
			if (($refund['status'] == 0) || (3 <= $refund['status'])) {
				$refund['refund_name'] = '处理申请';
			}
			else if ($refund['status'] == -1) {
				$refund['refund_name'] = '已拒绝';
			}
			else if ($refund['status'] == -2) {
				$refund['refund_name'] = '客户取消';
			}
			else {
				if ($refund['status'] == 1) {
					$refund['refund_name'] = '已完成';
				}
			}
		}

		if (empty($refund)) {
			$refund = array(
				0            => 'imgs',
				'refundtype' => array('name' => '', 'value' => ''),
				1            => 'status',
				2            => 'refund_name'
				);
		}

		return $refund;
	}

	public function getPriceInfo($order_info)
	{
		$_obf_DS8rEQYaLzg0MTA9PiwsMygSKBcIDwE_ = array('goodsprice' => $order_info['goodsprice'], 'olddispatchprice' => $order_info['olddispatchprice'], 'price' => $order_info['price'], 'deductenough' => $order_info['deductenough'], 'changeprice' => $order_info['changeprice'], 'changedispatchprice' => $order_info['changedispatchprice']);
		array_map(function($item) {
			return number_format($item, 2);
		}, $_obf_DS8rEQYaLzg0MTA9PiwsMygSKBcIDwE_);
		return $_obf_DS8rEQYaLzg0MTA9PiwsMygSKBcIDwE_;
	}

	public function getOrderGoods($order_id, $uniacid)
	{
		$plugin_diyform = p('diyform');
		$order_goods = pdo_fetchall('select g.id as goods_id,g.title,g.thumb,g.goodssn,og.goodssn as option_goodssn, g.productsn,og.productsn as option_productsn, og.total,og.price,og.optionname as optiontitle, og.realprice from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid ', array(':uniacid' => $uniacid, ':orderid' => $order_id));

		foreach ($order_goods as &$goods_item) {
			$goods = '' . $goods_item['title'] . '';

			if (!empty($goods_item['optiontitle'])) {
				$goods .= ' 规格: ' . $goods_item['optiontitle'];
			}

			if (!empty($goods_item['option_goodssn'])) {
				$goods_item['goodssn'] = $goods_item['option_goodssn'];
			}

			if (!empty($goods_item['option_productsn'])) {
				$goods_item['productsn'] = $goods_item['option_productsn'];
			}

			if (!empty($goods_item['goodssn'])) {
				$goods .= ' 商品编号: ' . $goods_item['goodssn'];
			}

			if (!empty($goods_item['productsn'])) {
				$goods .= ' 商品条码: ' . $goods_item['productsn'];
			}

			$goods .= ' 单价: ' . ($goods_item['price'] / $goods_item['total']) . ' 折扣后: ' . ($goods_item['realprice'] / $goods_item['total']) . ' 数量: ' . $goods_item['total'] . ' 总价: ' . $goods_item['price'] . ' 折扣后: ' . $goods_item['realprice'] . '';
			if ($plugin_diyform && !empty($goods_item['diyformfields']) && !empty($goods_item['diyformdata'])) {
				$diyformdata_array = $plugin_diyform->getDatas(iunserializer($goods_item['diyformfields']), iunserializer($goods_item['diyformdata']));
				$diyformdata = '';

				foreach ($diyformdata_array as $da) {
					$diyformdata .= $da['name'] . ': ' . $da['value'] . '';
				}

				$goods_item['goods_diyformdata'] = $diyformdata;
			}

			$goods_item['goods_attribute'] = $goods;
			$goods_item = array_part('goods_id,thumb,title,price,total,goods_attribute', $goods_item);
		}

		$order_goods = set_medias($order_goods, 'thumb');
		return $order_goods;
	}

	protected function _getSupplierCondition($uid)
	{
		' and o.supplier_uid=' . $uid . ' ';
	}

	protected function _getPayTypeCondition($pay_type)
	{
		if ($pay_type == '2') {
			$condition = ' AND ( o.paytype =21 or o.paytype=22 or o.paytype=23 )';
		}
		else {
			$condition = ' AND o.paytype =' . intval($pay_type);
		}

		return $condition;
	}

	protected function _getStatusCondition($status)
	{
		switch ($status) {
		case '-1':
			$condition = ' AND o.status=-1 and o.refundtime=0';
			break;

		case '4':
			$condition = ' AND o.refundstate>=0 AND o.refundid<>0';
			break;

		case '5':
			$condition = ' AND o.refundtime<>0';
			break;

		case '1':
			$condition = ' AND ( o.status = 1 or (o.status=0 and o.paytype=3) )';
			break;

		case '0':
			$condition = ' AND o.status = 0 and o.paytype<>3';
			break;

		default:
			$condition = ' AND o.status = ' . intval($status);
		}

		return $condition;
	}
}

if (!defined('IN_IA')) {
	exit('Access Denied');
}

?>
