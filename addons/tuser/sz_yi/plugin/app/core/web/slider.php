<?php
// 模块LTD提供
global $_W;
global $_GPC;
require IA_ROOT . '/addons/sz_yi/core/inc/plugin/vendor/qiniu/src/autoload.php';
$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');

if ($operation == 'display') {
	ca('shop.banner.view');

	if (!empty($_GPC['displayorder'])) {
		ca('shop.banner.edit');

		foreach ($_GPC['displayorder'] as $id => $displayorder) {
			pdo_update('sz_yi_banner', array('displayorder' => $displayorder), array('id' => $id));
		}

		plog('shop.banner.edit', '批量修改广告的排序');
		message('分类排序更新成功！', $this->createWebUrl('plugin/app', array('method' => 'slider', 'op' => 'display')), 'success');
	}

	$list = pdo_fetchall('SELECT * FROM ' . tablename('sz_yi_banner') . ' WHERE uniacid = \'' . $_W['uniacid'] . '\' ORDER BY displayorder DESC');
}
else if ($operation == 'post') {
	$id = intval($_GPC['id']);

	if (empty($id)) {
		ca('shop.banner.add');
	}
	else {
		ca('shop.banner.edit|shop.banner.view');
	}

	if (checksubmit('submit')) {
		$data = array('uniacid' => $_W['uniacid'], 'advname' => trim($_GPC['advname']), 'link' => trim($_GPC['link']), 'enabled' => intval($_GPC['enabled']), 'displayorder' => intval($_GPC['displayorder']), 'thumb' => $_GPC['thumb']);

		if (!empty($id)) {
			pdo_update('sz_yi_banner', $data, array('id' => $id));
			plog('shop.banner.edit', '修改广告 ID: ' . $id);
		}
		else {
			pdo_insert('sz_yi_banner', $data);
			$id = pdo_insertid();
			plog('shop.banner.add', '添加广告 ID: ' . $id);
		}

		$accessKey = 's6oWzmS-dB32i-GikfHLrsXzsWNCiApx8FVfamWg';
		$secretKey = 'izjmJVHjsfE8fSyHD_wnZZsO7XLm8b5bB9SAqJl3';
		$auth = new \Qiniu\Auth($accessKey, $secretKey);
		$bucket = 'yunzshop';
		$expire = 3600 * 24 * 365 * 50;
		$token = $auth->uploadToken($bucket, NULL, $expire);
		$filePath = '../attachment/' . $_GPC['thumb'];
		$file_info = pathinfo($filePath);
		$key = $file_info['basename'];
		$uploadMgr = new \Qiniu\Storage\UploadManager();
		list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);

		if ($err !== NULL) {
			message('抱歉,广告上传失败！', $this->createWebUrl('plugin/app', array('method' => 'slider', 'op' => 'display')), 'error');
		}
		else {
			message('更新广告成功！', $this->createWebUrl('plugin/app', array('method' => 'slider', 'op' => 'display')), 'success');
		}
	}

	$item = pdo_fetch('select * from ' . tablename('sz_yi_banner') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
}
else {
	if ($operation == 'delete') {
		ca('shop.banner.delete');
		$id = intval($_GPC['id']);
		$item = pdo_fetch('SELECT id,advname FROM ' . tablename('sz_yi_banner') . ' WHERE id = \'' . $id . '\' AND uniacid=' . $_W['uniacid'] . '');

		if (empty($item)) {
			message('抱歉，广告不存在或是已经被删除！', $this->createWebUrl('plugin/app', array('method' => 'slider', 'op' => 'display')), 'error');
		}

		pdo_delete('sz_yi_banner', array('id' => $id));
		plog('shop.banner.delete', '删除广告 ID: ' . $id . ' 标题: ' . $item['advname'] . ' ');
		message('广告删除成功！', $this->createWebUrl('plugin/app', array('method' => 'slider', 'op' => 'display')), 'success');
	}
}

load()->func('tpl');
include $this->template('slider');

?>
