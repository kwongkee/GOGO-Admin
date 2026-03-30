<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;
$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'index');

if ($operation == 'result') {
	$url = array('success' => $this->createMobileUrl('shop/message', array('op' => 'success')), 'fail' => $this->createMobileUrl('shop/message', array('op' => 'fail')));
	echo json_encode($url);
	return 1;
}

if ($operation == 'adv') {
	$qiniu_domain = 'http://7xwyfd.com1.z0.glb.clouddn.com/';
	$banner = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_banner') . '  ORDER BY `id` DESC');
	$file_info = pathinfo($banner['thumb']);
	$adv_img = array('android_src' => $qiniu_domain . $file_info['basename'], 'ios_src' => $qiniu_domain . $file_info['basename']);
	echo json_encode($adv_img);
}

?>
