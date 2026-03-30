<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

if (!class_exists('MerchantModel')) {
	class MerchantModel extends PluginModel
	{
		private $child_centers = array();

		public function getInfo($openid)
		{
			global $_W;
			$setdata = pdo_fetch('select * from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
			$_obf_DUADOAgvNCoQFDwZPCMODzEOMTkhHiI_ = unserialize($setdata['sets']);
			$_obf_DRAhIzsfCxYnPwkQHD8KExI5AUAGJBE_ = $_obf_DUADOAgvNCoQFDwZPCMODzEOMTkhHiI_['trade'];
			$set = $this->getSet();
			$info = array();

			if (empty($openid)) {
				return NULL;
			}

			$center = $this->isCenter($openid);

			if (empty($center)) {
				return NULL;
			}

			$member = m('member')->getInfo($openid);

			if (!empty($set['limit_day'])) {
				$time = time();

				if (!empty($member['id'])) {
					$_obf_DRUpNydcNzwGOA0kExQJNwQCIjQTEiI_ = pdo_fetchcolumn('SELECT apply_time FROM ' . tablename('sz_yi_merchant_apply') . 'WHERE uniacid=' . $_W['uniacid'] . ' AND member_id=' . $member['id'] . ' ORDER BY id DESC LIMIT 1');

					if (!empty($_obf_DRUpNydcNzwGOA0kExQJNwQCIjQTEiI_)) {
						$last_time = $_obf_DRUpNydcNzwGOA0kExQJNwQCIjQTEiI_ + ($set['limit_day'] * 60 * 60 * 24);

						if ($time < $last_time) {
							$info['limit_day'] = true;
							$info['last_time'] = date('Y-m-d H:i:s', $last_time);
						}
					}
				}
			}

			$info['levelinfo'] = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_merchant_level') . ' WHERE uniacid=:uniacid AND id=:id', array(':uniacid' => $_W['uniacid'], ':id' => $center['level_id']));
			$this->child_centers = array();
			$_obf_DRs5FkAzMCghFgUVKDU4KBEEFw8WHRE_ = $this->getChildCenters($openid);
			$supplier_uids = $this->getChildSupplierUids($openid);

			if (empty($supplier_uids)) {
				$supplier_uids = 0;
			}

			$info['supplier_uids'] = $supplier_uids;
			$supplier_cond = ' AND o.supplier_uid in (' . $supplier_uids . ') ';

			if ($info['supplier_uids'] == 0) {
				$supplier_cond = ' AND o.supplier_uid < 0 ';
			}

			$info['ordercount'] = pdo_fetchcolumn('SELECT count(o.id) FROM ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . '  og on o.id=og.orderid left join ' . tablename('sz_yi_order_refund') . ' r on r.orderid=o.id AND ifnull(r.status,-1)<>-1 ' . ' WHERE o.uniacid=' . $_W['uniacid'] . ' ' . $supplier_cond . ' AND o.status>=1 ORDER BY o.createtime DESC,o.status DESC ');
			$info['centercount'] = count($_obf_DRs5FkAzMCghFgUVKDU4KBEEFw8WHRE_);
			$info['merchantcount'] = count($this->getCenterMerchants($center['id']));
			$info['commission_total'] = number_format(pdo_fetchcolumn('SELECT sum(money) FROM ' . tablename('sz_yi_merchant_apply') . ' WHERE uniacid=:uniacid AND member_id=:member_id AND iscenter=1', array(':uniacid' => $_W['uniacid'], ':member_id' => $member['id'])), 2);
			$info['commission_ok'] = 0;
			$apply_cond = '';
			$now_time = time();

			if (!empty($set['apply_day'])) {
				$apply_day = $now_time - ($set['apply_day'] * 60 * 60 * 24);
				$apply_cond .= ' AND o.finishtime<' . $apply_day . ' ';
			}

			$info['no_apply_money'] = 0;
			$no_apply_money = pdo_fetchall('SELECT so.money FROM ' . tablename('sz_yi_order') . ' o left join ' . tablename('sz_yi_merchant_order') . ' so on o.id=so.orderid left join ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id WHERE o.uniacid=' . $_W['uniacid'] . ' ' . $supplier_cond . ' AND o.center_apply_status=0 AND o.status=3 ');

			if (!empty($no_apply_money)) {
				foreach ($no_apply_money as $n) {
					$info['no_apply_money'] += $n['money'];
				}

				$info['no_apply_money'] = number_format(($info['no_apply_money'] * $info['levelinfo']['commission']) / 100, 2);
			}

			$merchant_orders = pdo_fetchall('SELECT so.*,o.id as oid FROM ' . tablename('sz_yi_order') . ' o left join ' . tablename('sz_yi_merchant_order') . ' so on o.id=so.orderid left join ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id WHERE o.uniacid=' . $_W['uniacid'] . ' ' . $supplier_cond . ' ' . $apply_cond . ' AND o.center_apply_status=0 AND o.status=3 ');

			if (!empty($merchant_orders)) {
				$info['commission_ok'] = 0;

				foreach ($merchant_orders as $o) {
					$info['commission_ok'] += $o['money'];
				}
			}

			$info['commission_ok'] = ($info['commission_ok'] * $info['levelinfo']['commission']) / 100;
			$info['order_total_price'] = number_format(pdo_fetchcolumn('SELECT sum(og.price) FROM ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . '  og on o.id=og.orderid left join ' . tablename('sz_yi_order_refund') . ' r on r.orderid=o.id AND ifnull(r.status,-1)<>-1 ' . ' WHERE o.uniacid=' . $_W['uniacid'] . ' ' . $supplier_cond . ' ' . $apply_cond . ' ORDER BY o.createtime DESC,o.status DESC '), 2);
			$order_ids = pdo_fetchall('SELECT o.id FROM ' . tablename('sz_yi_order') . ' o left join ' . tablename('sz_yi_merchant_order') . ' so on o.id=so.orderid left join ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id WHERE o.uniacid=' . $_W['uniacid'] . ' ' . $supplier_cond . ' ' . $apply_cond . ' AND o.center_apply_status=0 AND o.status=3 ORDER BY o.createtime DESC,o.status DESC ');
			$info['order_ids'] = $order_ids;
			return $info;
		}

		public function getOpenid($center_id)
		{
			global $_W;

			if (empty($center_id)) {
				return NULL;
			}

			$openid = pdo_fetchcolumn('SELECT openid FROM ' . tablename('sz_yi_merchant_center') . ' WHERE uniacid=:uniacid AND id=:id', array(':uniacid' => $_W['uniacid'], ':id' => $center_id));
			return $openid;
		}

		public function getChildCenters($openid)
		{
			global $_W;

			if (empty($openid)) {
				return NULL;
			}

			$center = $this->isCenter($openid);

			if (empty($center)) {
				return NULL;
			}

			$childs = pdo_fetchall('SELECT * FROM ' . tablename('sz_yi_merchant_center') . ' WHERE uniacid=:uniacid AND center_id=:center_id', array(':uniacid' => $_W['uniacid'], ':center_id' => $center['id']));

			if (!empty($childs)) {
				$data = array();

				foreach ($childs as $key => $value) {
					$this->child_centers[$value['id']] = $value;
				}

				foreach ($childs as $val) {
					return $this->getChildCenters($val['openid']);
				}

				return NULL;
			}

			return $this->child_centers;
		}

		public function getChildSupplierUids($openid)
		{
			global $_W;

			if (empty($openid)) {
				return NULL;
			}

			$member = m('member')->getInfo($openid);
			$center = $this->isCenter($openid);
			$child_centers = $this->getChildCenters($openid);

			if (!empty($child_centers)) {
				$ids = array();
				$_obf_DQoxDwUtWyMBGTlAFg8DKzQ2MjcPISI_ = '';
				$ismerchant = pdo_fetchall('SELECT * FROM ' . tablename('sz_yi_merchants') . ' WHERE uniacid=' . $_W['uniacid'] . ' AND openid=\'' . $openid . '\'');

				if (!empty($ismerchant)) {
					$_obf_DQoxDwUtWyMBGTlAFg8DKzQ2MjcPISI_ = '\'' . $openid . '\'';
				}

				foreach ($child_centers as $val) {
					$ids[] = $val['id'];

					if (!empty($_obf_DQoxDwUtWyMBGTlAFg8DKzQ2MjcPISI_)) {
						$_obf_DQoxDwUtWyMBGTlAFg8DKzQ2MjcPISI_ .= ',';
					}

					$_obf_DQoxDwUtWyMBGTlAFg8DKzQ2MjcPISI_ .= '\'' . $val['openid'] . '\'';
				}

				$_obf_DSQiCxAFLCkYLjIZIzwmEzsyFgkjGSI_ = '';

				if (!empty($_obf_DQoxDwUtWyMBGTlAFg8DKzQ2MjcPISI_)) {
					$_obf_DSQiCxAFLCkYLjIZIzwmEzsyFgkjGSI_ .= ' OR openid in (' . $_obf_DQoxDwUtWyMBGTlAFg8DKzQ2MjcPISI_ . ')';
				}

				$_obf_DTUSGR8NFw8iIj4KIjs2QCEYCwMjChE_ = implode(',', $ids);

				if (!empty($center)) {
					$_obf_DTUSGR8NFw8iIj4KIjs2QCEYCwMjChE_ .= ',' . $center['id'];
				}

				$supplier_uids = pdo_fetchall('SELECT distinct supplier_uid FROM ' . tablename('sz_yi_merchants') . ' WHERE uniacid=:uniacid AND (center_id in (' . $_obf_DTUSGR8NFw8iIj4KIjs2QCEYCwMjChE_ . ') ' . $_obf_DSQiCxAFLCkYLjIZIzwmEzsyFgkjGSI_ . ')', array(':uniacid' => $_W['uniacid']));
			}
			else if (!empty($center)) {
				$ismerchant = pdo_fetchall('SELECT * FROM ' . tablename('sz_yi_merchants') . ' WHERE uniacid=' . $_W['uniacid'] . ' AND openid=\'' . $openid . '\'');
				$_obf_DSQiCxAFLCkYLjIZIzwmEzsyFgkjGSI_ = '';

				if (!empty($ismerchant)) {
					$_obf_DSQiCxAFLCkYLjIZIzwmEzsyFgkjGSI_ = ' OR openid=\'' . $openid . '\'';
				}

				$supplier_uids = pdo_fetchall('SELECT distinct supplier_uid FROM ' . tablename('sz_yi_merchants') . ' WHERE uniacid=:uniacid AND center_id=:center_id ' . $_obf_DSQiCxAFLCkYLjIZIzwmEzsyFgkjGSI_, array(':uniacid' => $_W['uniacid'], ':center_id' => $center['id']));
			}
			else {
				$supplier_uids = pdo_fetchall('SELECT distinct supplier_uid FROM ' . tablename('sz_yi_merchants') . ' WHERE uniacid=:uniacid AND member_id=:member_id', array(':uniacid' => $_W['uniacid'], ':member_id' => $member['id']));
			}

			if (!empty($supplier_uids)) {
				$uids = array();

				foreach ($supplier_uids as $val) {
					$uids[] = $val['supplier_uid'];
				}

				$supplier_uids = implode(',', $uids);
			}

			if (empty($supplier_uids)) {
				$supplier_uids = 0;
			}

			return $supplier_uids;
		}

		public function getAllSupplierUids($member_id)
		{
			global $_W;
			global $_GPC;
			$supplier_uids = pdo_fetchall('select distinct supplier_uid from ' . tablename('sz_yi_merchants') . ' where uniacid=' . $_W['uniacid'] . ' and member_id=' . $member_id);
			$uids = '';

			foreach ($supplier_uids as $key => $value) {
				if ($key == 0) {
					$uids .= $value['supplier_uid'];
				}
				else {
					$uids .= ',' . $value['supplier_uid'];
				}
			}

			if (empty($uids)) {
				$uids = 0;
			}

			return $uids;
		}

		public function isCenter($openid)
		{
			global $_W;
			$center = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_merchant_center') . ' WHERE uniacid=:uniacid AND openid=:openid', array(':uniacid' => $_W['uniacid'], ':openid' => $openid));
			return $center;
		}

		public function getCenterMerchants($center_id)
		{
			global $_W;
			global $_GPC;

			if (empty($center_id)) {
				return '';
			}

			$merchants = pdo_fetchall('select * from ' . tablename('sz_yi_merchants') . ' where uniacid=' . $_W['uniacid'] . ' and center_id=:center_id ORDER BY id DESC', array(':center_id' => $center_id));

			foreach ($merchants as &$value) {
				$merchants_member = m('member')->getMember($value['openid']);
				$value['username'] = pdo_fetchcolumn('SELECT username FROM ' . tablename('sz_yi_perm_user') . ' WHERE uniacid=:uniacid AND uid=:uid', array(':uniacid' => $_W['uniacid'], ':uid' => $value['supplier_uid']));
				$value['avatar'] = $merchants_member['avatar'];
				$value['nickname'] = $merchants_member['nickname'];
				$value['realname'] = $merchants_member['realname'];
				$value['mobile'] = $merchants_member['mobile'];
			}

			unset($value);
			return $merchants;
		}

		public function getSet()
		{
			$set = parent::getSet();
			return $set;
		}

		public function sendMessage($openid = '', $data = array(), $message_type = '')
		{
			global $_W;
			global $_GPC;
			$set = $this->getSet();
			$templateid = $set['templateid'];
			$member = m('member')->getMember($openid);
			$usernotice = unserialize($member['noticeset']);

			if (!is_array($usernotice)) {
				$usernotice = array();
			}

			if ($message_type == TM_MERCHANT_APPLY) {
				$message = $set['merchant_applycontent'];
				$message = str_replace('[昵称]', $data['nickname'], $message);
				$message = str_replace('[时间]', date('Y-m-d H:i:s', $data['time']), $message);
				$msg = array(
					'keyword1' => array('value' => !empty($set['merchant_applytitle']) ? $set['merchant_applytitle'] : '提现申请通知', 'color' => '#73a68d'),
					'keyword2' => array('value' => $message, 'color' => '#73a68d')
					);

				if (!empty($templateid)) {
					m('message')->sendTplNotice($openid, $templateid, $msg);
				}
				else {
					m('message')->sendCustomNotice($openid, $msg);
				}
			}

			if ($message_type == TM_MERCHANT_PAY) {
				$message = $set['merchant_finishcontent'];
				$message = str_replace('[昵称]', $data['nickname'], $message);
				$message = str_replace('[时间]', date('Y-m-d H:i:s', $data['time']), $message);
				$msg = array(
					'keyword1' => array('value' => !empty($set['merchant_finishtitle']) ? $set['merchant_finishtitle'] : '提现申请完成通知', 'color' => '#73a68d'),
					'keyword2' => array('value' => $message, 'color' => '#73a68d')
					);

				if (!empty($templateid)) {
					m('message')->sendTplNotice($openid, $templateid, $msg);
					return NULL;
				}

				m('message')->sendCustomNotice($openid, $msg);
			}
		}

		public function perms()
		{
			return array(
	'merchant' => array(
		'text'     => $this->getName(),
		'isplugin' => true,
		'child'    => array(
			'cover'     => array('text' => '入口设置'),
			'merchants' => array('text' => '招商员', 'view' => '浏览')
			)
		)
	);
		}
	}
}

?>
