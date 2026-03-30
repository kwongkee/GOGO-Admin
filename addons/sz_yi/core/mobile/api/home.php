<?php
// Ä£¿éLTDÌá¹©
global $_W;
global $_GPC;
require IA_ROOT . '/addons/sz_yi/core/inc/interface.php';
$uniacid = $_W['uniacid'];
$advs = pdo_fetchall('select id,advname,link,thumb,thumb_pc from ' . tablename('sz_yi_adv') . ' where uniacid=:uniacid and enabled=1 order by displayorder desc', array(':uniacid' => $uniacid));

foreach ($advs as $key => $adv) {
	if (!empty($advs[$key]['thumb'])) {
		$adv[] = $advs[$key];
	}

	if (!empty($advs[$key]['thumb_pc'])) {
		$adv_pc[] = $advs[$key];
	}
}

$advs = set_medias($advs, 'thumb,thumb_pc');
$category = pdo_fetchall('select id,name,thumb,parentid,level from ' . tablename('sz_yi_category') . ' where uniacid=:uniacid and ishome=1 and enabled=1 order by displayorder desc', array(':uniacid' => $uniacid));
$category = set_medias($category, 'thumb');

foreach ($category as &$c) {
	$c['thumb'] = tomedia($c['thumb']);

	if ($c['level'] == 3) {
		$c['url'] = $this->createMobileUrl('shop/list', array('tcate' => $c['id']));
	}
	else {
		if ($c['level'] == 2) {
			$c['url'] = $this->createMobileUrl('shop/list', array('ccate' => $c['id']));
		}
	}
}

$args = array('page' => $_GPC['page'], 'pagesize' => 8, 'isrecommand' => 1, 'order' => 'displayorder desc,createtime desc', 'by' => '');
$goods = m('goods')->getList($args);

foreach ($goods as &$g) {
	$g['url'] = $this->createMobileUrl('shop/detail', array('id' => $g['id']));
}

$app_interface = new InterfaceController();
$res = array('advs' => $advs, 'category' => $category, 'goods' => $goods, 'search' => $this->createMobileUrl('shop/list'), 'msglist' => $this->createMobileUrl('member/messagelist'), 'more' => $this->createMobileUrl('shop/list3'), 'groups' => $this->createMobileUrl('shop/list', array('order' => 'sales', 'by' => 'shop')), 'distribution' => $this->createPluginMobileUrl('commission'), 'bag' => $this->createMobileUrl('shop/cart'));
echo json_encode($res);

?>
