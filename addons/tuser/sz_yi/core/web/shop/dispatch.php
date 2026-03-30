<?php
// 模块LTD提供
global $_W;
global $_GPC;
$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');
$mt = mt_rand(5, 35);



$supplier_uid = 0;
$supplier_cond = '';

if (p('supplier')) {
	$perm_role = p('supplier')->verifyUserIsSupplier($_W['uid']);

	if ($perm_role == 1) {
		$supplier_uid = $_W['uid'];
	}

	if (empty($_W['isfounder'])) {
		$supplier_cond = ' AND supplier_uid = ' . $supplier_uid;
	}
}

if ($operation == 'display') {
	ca('shop.dispatch.view');

	if (!empty($_GPC['displayorder'])) {
		ca('shop.dispatch.edit');

		foreach ($_GPC['displayorder'] as $id => $displayorder) {
			pdo_update('sz_yi_dispatch', array('displayorder' => $displayorder), array('id' => $id));
		}

		plog('shop.dispatch.edit', '批量修改配送方式排序');
		message('分类排序更新成功！', $this->createWebUrl('shop/dispatch', array('op' => 'display')), 'success');
	}

	$list = pdo_fetchall('SELECT * FROM ' . tablename('sz_yi_dispatch') . ' WHERE uniacid = \'' . $_W['uniacid'] . '\' and dispatchtype=0 ' . $supplier_cond . ' ORDER BY id asc');

	foreach ($list as $key => &$value) {
		$value['supplier_name'] = pdo_fetchcolumn('select username from' . tablename('sz_yi_perm_user') . 'where uniacid =:uniacid and  uid = ' . $value['supplier_uid'], array(':uniacid' => $_W['uniacid']));
	}
}
else if ($operation == 'post') {
	$id = intval($_GPC['id']);

	if (empty($id)) {
		ca('shop.dispatch.add');
	}
	else {
		ca('shop.dispatch.edit|shop.dispatch.view');
	}

	if (checksubmit('submit')) {
		$areas = array();
		$randoms = $_GPC['random'];

		if (is_array($randoms)) {
			foreach ($randoms as $random) {
				$citys = trim($_GPC['citys'][$random]);

				if (empty($citys)) {
					continue;
				}

				$areas[] = array('citys' => $_GPC['citys'][$random], 'firstprice' => $_GPC['firstprice'][$random], 'firstweight' => $_GPC['firstweight'][$random], 'secondprice' => $_GPC['secondprice'][$random], 'secondweight' => $_GPC['secondweight'][$random], 'firstnumprice' => $_GPC['firstnumprice'][$random], 'firstnum' => $_GPC['firstnum'][$random], 'secondnumprice' => $_GPC['secondnumprice'][$random], 'secondnum' => $_GPC['secondnum'][$random]);
			}
		}

		$carriers = array();
		$addresses = $_GPC['address'];

		if (is_array($addresses)) {
			foreach ($addresses as $key => $address) {
				$carriers[] = array('address' => $_GPC['address'][$key], 'realname' => $_GPC['realname'][$key], 'mobile' => $_GPC['mobile'][$key], 'content' => $_GPC['content'][$key]);
			}
		}

		$data = array('uniacid' => $_W['uniacid'], 'displayorder' => intval($_GPC['displayorder']), 'dispatchtype' => intval($_GPC['dispatchtype']), 'isdefault' => intval($_GPC['isdefault']), 'dispatchname' => trim($_GPC['dispatchname']), 'express' => trim($_GPC['express']), 'calculatetype' => trim($_GPC['calculatetype']), 'firstprice' => trim($_GPC['default_firstprice']), 'firstweight' => trim($_GPC['default_firstweight']), 'secondprice' => trim($_GPC['default_secondprice']), 'secondweight' => trim($_GPC['default_secondweight']), 'firstnumprice' => trim($_GPC['default_firstnumprice']), 'firstnum' => trim($_GPC['default_firstnum']), 'secondnumprice' => trim($_GPC['default_secondnumprice']), 'secondnum' => trim($_GPC['default_secondnum']), 'areas' => iserializer($areas), 'carriers' => iserializer($carriers), 'enabled' => intval($_GPC['enabled']));

		if ($data['isdefault']) {
			pdo_update('sz_yi_dispatch', array('isdefault' => 0), array('uniacid' => $_W['uniacid'], 'supplier_uid' => $supplier_uid));
		}

		if (!empty($id)) {
			plog('shop.dispatch.edit', '修改配送方式 ID: ' . $id);
			pdo_update('sz_yi_dispatch', $data, array('id' => $id, 'supplier_uid' => $supplier_uid));
		}
		else {
			$data['supplier_uid'] = $supplier_uid;
			pdo_insert('sz_yi_dispatch', $data);
			$id = pdo_insertid();
			plog('shop.dispatch.add', '添加配送方式 ID: ' . $id);
		}

		message('更新配送方式成功！', $this->createWebUrl('shop/dispatch', array('op' => 'display')), 'success');
	}

	$dispatch = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_dispatch') . ' WHERE id = \'' . $id . '\' and uniacid = \'' . $_W['uniacid'] . '\'');

	if (!empty($dispatch)) {
		$dispatch_areas = unserialize($dispatch['areas']);
		$dispatch_carriers = unserialize($dispatch['carriers']);
	}

	$areas = m('cache')->getArray('areas', 'global');

	if (!is_array($areas)) {
		require_once SZ_YI_INC . 'json/xml2json.php';
		$file = IA_ROOT . '/addons/sz_yi/static/js/dist/area/Area.xml';
		$content = file_get_contents($file);
		$json = xml2json::transformXmlStringToJson($content);
		$areas = json_decode($json, true);
		m('cache')->set('areas', $areas, 'global');
	}
}
else if ($operation == 'delete') {
	ca('shop.dispatch.delete');
	$id = intval($_GPC['id']);
	$dispatch = pdo_fetch('SELECT id,dispatchname FROM ' . tablename('sz_yi_dispatch') . ' WHERE id = \'' . $id . '\' AND uniacid=' . $_W['uniacid'] . '');

	if (empty($dispatch)) {
		message('抱歉，配送方式不存在或是已经被删除！', $this->createWebUrl('shop/dispatch', array('op' => 'display')), 'error');
	}

	pdo_delete('sz_yi_dispatch', array('id' => $id, 'supplier_uid' => $supplier_uid));
	plog('shop.dispatch.delete', '删除配送方式 ID: ' . $id . ' 名称: ' . $dispatch['dispatchname'] . ' ');
	message('配送方式删除成功！', $this->createWebUrl('shop/dispatch', array('op' => 'display')), 'success');
}
else if ($operation == 'tpl') {
	$random = random(16);
	ob_clean();
	ob_start();
	include $this->template('web/shop/tpl/dispatch');
	$contents = ob_get_contents();
	ob_clean();
	exit(json_encode(array('random' => $random, 'html' => $contents)));
}
else {
	if ($operation == 'tpl1') {
		$random = random(16);
		ob_clean();
		ob_start();
		include $this->template('web/shop/tpl/carrier');
		$contents = ob_get_contents();
		ob_clean();
		exit(json_encode(array('random' => $random, 'html' => $contents)));
	}
}

include $this->template('web/shop/dispatch');
echo "\n";

?>
