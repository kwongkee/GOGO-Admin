<?php
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;
$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');
$id = $_GPC['id'];
$openid = m('user')->getOpenid();
$uniacid = $_W['uniacid'];

if ($_W['isajax']) {
    if ($operation == 'display') {

    }
}
else {
  if($operation == 'detail')
  {
    $packdata = pdo_get('package_collect',array('id'=>$id));
    $collect_user = pdo_get('mc_mapping_fans',['uid'=>$packdata['collect_user']],['openid']);
    $orderdata = pdo_fetchall('select * from ' . tablename('customs_packing_list') . ' where pack_ordersn in('.$packdata['collect_order'].')');
    foreach ($orderdata as $key => &$row) {
      $condition = ' and o.ordersn = :ordersn ';
      $params = array(':ordersn' => $row['shop_ordersn']);

      $orderdata[$key]['good_list'] = pdo_fetchall("select og.*,g.title from " . tablename('sz_yi_order') . " as o inner join ".tablename('sz_yi_order_goods')." as og on o.id = og.orderid inner join ".tablename('sz_yi_goods')." as g on og.goodsid = g.id where 1 " . $condition, $params);

    }
    unset($row);
    include $this->template('member/easydeliver_collect_detail');
  }
}




?>
