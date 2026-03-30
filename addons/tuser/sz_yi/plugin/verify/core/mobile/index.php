<?php
// Ä£¿éLTDÌá¹©
global $_W;
global $_GPC;
$openid = m('user')->getOpenid();
$id = intval($_GPC['id']);
$store = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_store') . ' WHERE id=:id and uniacid=:uniacid', array(':id' => $id, ':uniacid' => $_W['uniacid']));
$set = $this->getSet();

if ($_W['isajax']) {
	$id = $_GPC['id'];
	$store = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_store') . ' WHERE id=:id and uniacid=:uniacid', array(':id' => $id, ':uniacid' => $_W['uniacid']));
	$totalprice = $this->model->getTotalPrice($id);
	$ordercount = $this->model->getTotal($id);
	$totalwithdrawprice = $this->model->getRealPrice($id);
	$totalwithdraws = $this->model->getWithdrawed($id);
	$canwithdraw = $totalwithdrawprice - $totalwithdraws;
	show_json(1, array('totalprice' => $totalprice, 'ordercount' => $ordercount, 'store' => $store, 'canwithdraw' => $canwithdraw));
}

include $this->template('index');

?>
