<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

define('TM_SUPPLIER_PAY', 'supplier_pay');

if (!class_exists('SupplierModel')) {
	class SupplierModel extends PluginModel
	{
		public function getSupplierName($supplier_uid)
		{
			global $_W;

			if (m('cache')->get('supplier_' . $supplier_uid)) {
				return m('cache')->get('supplier_' . $supplier_uid);
			}

			$supplierName = pdo_fetchcolumn('select username from ' . tablename('sz_yi_perm_user') . ' where uniacid=' . $_W['uniacid'] . ' and uid=' . $supplier_uid);
			m('cache')->set('supplier_' . $supplier_uid, $supplierName);
			return $supplierName;
		}

		public function getSupplierMerchants($uid)
		{
			global $_W;
			global $_GPC;

			if (empty($uid)) {
				return array();
			}

			$merchants = pdo_fetchall('select * from ' . tablename('sz_yi_merchants') . ' where uniacid=' . $_W['uniacid'] . ' and supplier_uid=' . $uid . ' ORDER BY id DESC');

			foreach ($merchants as &$value) {
				$merchants_member = m('member')->getMember($value['openid']);
				$value['avatar'] = $merchants_member['avatar'];
				$value['nickname'] = $merchants_member['nickname'];
				$value['realname'] = $merchants_member['realname'];
				$value['mobile'] = $merchants_member['mobile'];
			}

			unset($value);
			return $merchants;
		}

		public function getRoleId()
		{
			global $_W;
			global $_GPC;
			$roleid = pdo_fetchcolumn('select id from ' . tablename('sz_yi_perm_role') . ' where status1=1');
			return $roleid;
		}

		public function AllSuppliers()
		{
			global $_W;
			global $_GPC;
			$roleid = $this->getRoleId();
			$all_suppliers = pdo_fetchall('SELECT * FROM ' . tablename('sz_yi_perm_user') . ' WHERE uniacid=:uniacid AND roleid=:roleid', array(':uniacid' => $_W['uniacid'], ':roleid' => $roleid));
			return $all_suppliers;
		}

		public function getSupplierInfo($uid)
		{
			global $_W;
			global $_GPC;
			$supplierinfo = array();
			$set = $this->getSet();

			if (!empty($set['limit_day'])) {
				$time = time();

				if (!empty($uid)) {
					$_obf_DRUpNydcNzwGOA0kExQJNwQCIjQTEiI_ = pdo_fetchcolumn('SELECT apply_time FROM ' . tablename('sz_yi_supplier_apply') . 'WHERE uniacid=' . $_W['uniacid'] . ' AND uid=' . $uid . ' ORDER BY id DESC LIMIT 1');

					if (!empty($_obf_DRUpNydcNzwGOA0kExQJNwQCIjQTEiI_)) {
						$last_time = $_obf_DRUpNydcNzwGOA0kExQJNwQCIjQTEiI_ + ($set['limit_day'] * 60 * 60 * 24);

						if ($time < $last_time) {
							$supplierinfo['limit_day'] = true;
							$supplierinfo['last_time'] = date('Y-m-d H:i:s', $last_time);
						}
					}
				}
			}

			$supplierinfo['ordercount'] = 0;
			$supplierinfo['commission_total'] = 0;
			$supplierinfo['costmoney'] = 0;
			$supplierinfo['totalmoney'] = 0;
			$supplierinfo['ordercount'] = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_order') . ' where supplier_uid=' . $uid . ' and userdeleted=0 and deleted=0 and uniacid=' . $_W['uniacid'] . ' ');
			$supplierinfo['commission_total'] = pdo_fetchcolumn('select sum(apply_money) from ' . tablename('sz_yi_supplier_apply') . ' where uniacid=' . $_W['uniacid'] . ' and uid=' . $uid . ' and status=1');
			$supplierinfo['sp_goods'] = array();
			$supplierinfo['costmoney'] = 0;
			$supplierinfo['costmoney_total'] = 0;
			$supplierinfo['expect_money'] = '0.00';
			$apply_cond = '';
			$_obf_DTUOHzAOKS0YDC0yKDsHBx4UBh8JCjI_ = '';
			$now_time = time();

			if (!empty($set['apply_day'])) {
				$apply_day = $now_time - ($set['apply_day'] * 60 * 60 * 24);
				$apply_cond .= ' AND o.finishtime<' . $apply_day . ' ';
				$_obf_DTUOHzAOKS0YDC0yKDsHBx4UBh8JCjI_ = ' AND o.finishtime>' . $apply_day . ' ';
				$supplierinfo['expect_money'] = pdo_fetchcolumn('SELECT sum(so.money) FROM ' . tablename('sz_yi_supplier_order') . ' so left join ' . tablename('sz_yi_order') . ' o on o.id=so.orderid left join ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id where o.uniacid=' . $_W['uniacid'] . ' and o.supplier_uid=' . $uid . ' and o.status=3 and og.supplier_apply_status=0 ' . $_obf_DTUOHzAOKS0YDC0yKDsHBx4UBh8JCjI_);
			}

			$costmoney_total = pdo_fetchall('SELECT so.money FROM ' . tablename('sz_yi_supplier_order') . ' so left join ' . tablename('sz_yi_order') . ' o on o.id=so.orderid left join ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id where o.uniacid=' . $_W['uniacid'] . ' and o.supplier_uid=' . $uid . ' and o.status=3 and og.supplier_apply_status=0 GROUP BY so.id');

			if (!empty($costmoney_total)) {
				foreach ($costmoney_total as $c) {
					$supplierinfo['costmoney_total'] += $c['money'];
				}
			}

			$_obf_DRQUOQgUKx8IED0FOBksXAc3GhoRIxE_ = pdo_fetchall('SELECT so.*,o.id as oid,og.id as ogid FROM ' . tablename('sz_yi_supplier_order') . ' so left join ' . tablename('sz_yi_order') . ' o on o.id=so.orderid left join ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id where o.uniacid=' . $_W['uniacid'] . ' and o.supplier_uid=' . $uid . ' and o.status=3 and og.supplier_apply_status=0 ' . $apply_cond);

			if (!empty($_obf_DRQUOQgUKx8IED0FOBksXAc3GhoRIxE_)) {
				$supplierinfo['sp_goods'] = $_obf_DRQUOQgUKx8IED0FOBksXAc3GhoRIxE_;
				$supplierinfo['costmoney'] = 0;

				foreach ($_obf_DRQUOQgUKx8IED0FOBksXAc3GhoRIxE_ as $o) {
					$supplierinfo['costmoney'] += $o['money'];
				}
			}

			$supplierinfo['totalmoney'] = pdo_fetchcolumn('select sum(apply_money) from ' . tablename('sz_yi_supplier_apply') . ' where uniacid=' . $_W['uniacid'] . ' and uid=' . $uid);
			return $supplierinfo;
		}

		public function getSupplierUidAndUsername($openid)
		{
			global $_W;
			global $_GPC;
			$supplieruser = pdo_fetch('select uid,username from ' . tablename('sz_yi_perm_user') . ' where openid=\'' . $openid . '\' and uniacid=' . $_W['uniacid']);
			return $supplieruser;
		}

		public function isSupplier($openid)
		{
			global $_W;
			global $_GPC;
			$issupplier = pdo_fetch('select * from ' . tablename('sz_yi_perm_user') . ' where openid=\'' . $openid . '\' and uniacid=' . $_W['uniacid'] . ' and roleid=(select id from ' . tablename('sz_yi_perm_role') . ' where status1=1)');
			return $issupplier;
		}

		public function getSupplierPermId()
		{
			global $_W;
			global $_GPC;
			$perms = pdo_fetch('select * from ' . tablename('sz_yi_perm_role') . ' where status1 = 1');
			$_obf_DS08CDcrOzcfHh8wHg8PEgcHECUYDCI_ = 'shop,shop.goods,shop.goods.view,shop.goods.add,shop.goods.edit,shop.goods.delete,order,order.view,order.view.status_1,order.view.status0,order.view.status1,order.view.status2,order.view.status3,order.view.status4,order.view.status5,order.view.status9,order.op,order.op.pay,order.op.send,order.op.sendcancel,order.op.finish,order.op.verify,order.op.fetch,order.op.close,order.op.refund,order.op.export,order.op.changeprice,exhelper,exhelper.print,exhelper.print.single,exhelper.print.more,exhelper.exptemp1,exhelper.exptemp1.view,exhelper.exptemp1.add,exhelper.exptemp1.edit,exhelper.exptemp1.delete,exhelper.exptemp1.setdefault,exhelper.exptemp2,exhelper.exptemp2.view,exhelper.exptemp2.add,exhelper.exptemp2.edit,exhelper.exptemp2.delete,exhelper.exptemp2.setdefault,exhelper.senduser,exhelper.senduser.view,exhelper.senduser.add,exhelper.senduser.edit,exhelper.senduser.delete,exhelper.senduser.setdefault,exhelper.short,exhelper.short.view,exhelper.short.save,exhelper.printset,exhelper.printset.view,exhelper.printset.save,exhelper.dosen,taobao,taobao.fetch';

			if (empty($perms)) {
				$data = array('rolename' => '供应商', 'status' => 1, 'status1' => 1, 'perms' => $_obf_DS08CDcrOzcfHh8wHg8PEgcHECUYDCI_, 'deleted' => 0);
				pdo_insert('sz_yi_perm_role', $data);
				$permid = pdo_insertid();
			}
			else {
				$permid = $perms['id'];
			}

			return $permid;
		}

		public function verifyUserIsSupplier($uid)
		{
			global $_W;
			global $_GPC;
			$roleid = pdo_fetchcolumn('select roleid from' . tablename('sz_yi_perm_user') . ' where uid=' . $uid . ' and uniacid=' . $_W['uniacid']);

			if ($roleid != 0) {
				$perm_role = pdo_fetchcolumn('select status1 from' . tablename('sz_yi_perm_role') . ' where id=' . $roleid);
				return $perm_role;
			}
		}

		public function getSet()
		{
			$set = parent::getSet();
			return $set;
		}

		public function sendMessage($openid = '', $data = array(), $becometitle = '')
		{
			global $_W;
			global $_GPC;
			$member = m('member')->getMember($openid);

			if ($becometitle == TM_SUPPLIER_PAY) {
				$message = '恭喜您，您的提现将通过 [提现方式] 转账提现金额为[金额]已在[时间]转账到您的账号，敬请查看';
				$message = str_replace('[时间]', date('Y-m-d H:i:s', time()), $message);
				$message = str_replace('[金额]', $data['money'], $message);
				$message = str_replace('[提现方式]', $data['type'], $message);
				$msg = array(
					'keyword1' => array('value' => '供应商打款通知', 'color' => '#73a68d'),
					'keyword2' => array('value' => $message, 'color' => '#73a68d')
					);
				m('message')->sendCustomNotice($openid, $msg);
			}
		}

		public function sendSupplierInform($openid = '', $status = '')
		{
			global $_W;
			global $_GPC;

			if ($status == 1) {
				$resu = '驳回';
			}
			else {
				$resu = '通过';
			}

			$set = $this->getSet();
			$tm = $set['tm'];
			$message = $tm['commission_become'];
			$message = str_replace('[状态]', $resu, $message);
			$message = str_replace('[时间]', date('Y-m-d H:i', time()), $message);

			if (!empty($tm['commission_becometitle'])) {
				$title = $tm['commission_becometitle'];
			}
			else {
				$title = '会员申请供应商通知';
			}

			$msg = array(
				'keyword1' => array('value' => $title, 'color' => '#73a68d'),
				'keyword2' => array('value' => $message, 'color' => '#73a68d')
				);
			m('message')->sendCustomNotice($openid, $msg);
		}

		public function order_split($orderid)
		{
			global $_W;

			if (empty($orderid)) {
				return NULL;
			}

			$_obf_DTgDKAcdFDYmIhsuIyIEXCocA0A_DCI_ = pdo_fetchall('select distinct supplier_uid from ' . tablename('sz_yi_order_goods') . ' where orderid=:orderid and uniacid=:uniacid', array(':orderid' => $orderid, ':uniacid' => $_W['uniacid']));

			if (count($_obf_DTgDKAcdFDYmIhsuIyIEXCocA0A_DCI_) == 1) {
				pdo_update('sz_yi_order', array('supplier_uid' => $_obf_DTgDKAcdFDYmIhsuIyIEXCocA0A_DCI_[0]['supplier_uid']), array('id' => $orderid, 'uniacid' => $_W['uniacid']));
				return NULL;
			}

			$_obf_DSgNBAwLARsxNQwLLwYSECk4Jh4zLTI_ = pdo_fetchall('select supplier_uid, id from ' . tablename('sz_yi_order_goods') . ' where orderid=:orderid and uniacid=:uniacid ', array(':orderid' => $orderid, ':uniacid' => $_W['uniacid']));
			$orderdata = pdo_fetch('select * from ' . tablename('sz_yi_order') . ' where  id=:id and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $orderid));
			$_obf_DTMREhIzEx8VLyIYKyQlDj8vA0AeCwE_ = ture;
			$datas = array();

			foreach ($_obf_DSgNBAwLARsxNQwLLwYSECk4Jh4zLTI_ as $key => $value) {
				$datas[$value['supplier_uid']][]['id'] = $value['id'];
			}

			$num = false;
			unset($orderdata['id']);
			unset($orderdata['uniacid']);
			$dispatchprice = $orderdata['dispatchprice'];
			$olddispatchprice = $orderdata['olddispatchprice'];
			$changedispatchprice = $orderdata['changedispatchprice'];

			if (!empty($datas)) {
				foreach ($datas as $key => $value) {
					$order = $orderdata;
					$price = 0;
					$realprice = 0;
					$oldprice = 0;
					$changeprice = 0;
					$goodsprice = 0;
					$couponprice = 0;
					$discountprice = 0;
					$deductprice = 0;
					$deductcredit2 = 0;

					foreach ($value as $v) {
						$resu = pdo_fetch('select price,realprice,oldprice,supplier_uid from ' . tablename('sz_yi_order_goods') . ' where id=:id and uniacid=:uniacid ', array(':id' => $v['id'], ':uniacid' => $_W['uniacid']));
						$price += $resu['price'];
						$realprice += $resu['realprice'];
						$oldprice += $resu['oldprice'];
						$goodsprice += $resu['price'];
						$supplier_uid = $key;
						$changeprice += $resu['changeprice'];
						$scale = $resu['price'] / $order['goodsprice'];
						$couponprice += round($scale * $order['couponprice'], 2);
						$discountprice += round($scale * $order['discountprice'], 2);
						$deductprice += round($scale * $order['deductprice'], 2);
						$deductcredit2 += round($scale * $order['deductcredit2'], 2);
					}

					$order['oldprice'] = $oldprice;
					$order['goodsprice'] = $goodsprice;
					$order['supplier_uid'] = $supplier_uid;
					$order['couponprice'] = $couponprice;
					$order['discountprice'] = $discountprice;
					$order['deductprice'] = $deductprice;
					$order['deductcredit2'] = $deductcredit2;
					$order['changeprice'] = $changeprice;
					$order['dispatchprice'] = round($dispatchprice / count($_obf_DSgNBAwLARsxNQwLLwYSECk4Jh4zLTI_), 2);
					$order['olddispatchprice'] = round($olddispatchprice / count($_obf_DSgNBAwLARsxNQwLLwYSECk4Jh4zLTI_), 2);
					$order['changedispatchprice'] = round($changedispatchprice / count($_obf_DSgNBAwLARsxNQwLLwYSECk4Jh4zLTI_), 2);
					$order['price'] = ($realprice - $couponprice - $discountprice - $deductprice - $deductcredit2) + $order['dispatchprice'];

					if ($num == false) {
						pdo_update('sz_yi_order', $order, array('id' => $orderid, 'uniacid' => $_W['uniacid']));
						$num = ture;
					}
					else {
						$order['uniacid'] = $_W['uniacid'];
						$ordersn = m('common')->createNO('order', 'ordersn', 'SH');
						$order['ordersn'] = $ordersn;
						pdo_insert('sz_yi_order', $order);
						$logid = pdo_insertid();
						$oid = array('orderid' => $logid);

						foreach ($value as $val) {
							pdo_update('sz_yi_order_goods', $oid, array('id' => $val['id'], 'uniacid' => $_W['uniacid']));
						}
					}
				}
			}
		}
	}
}

?>
