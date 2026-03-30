<?php
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;
$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');

$pack_ordersn = $_GPC['pack_ordersn'];
$collect_user = $_GPC['collect_user'];
$pack_id = $_GPC['pack_id'];
$openid = m('user')->getOpenid();
$uniacid = $_W['uniacid'];

if ($_W['isajax']) {
    if ($operation == 'display') {

    }
}
else {
  if($operation == 'display')
  {
    $user = mc_fetch($_W['fans']['uid']);
    if($openid != $collect_user || $user['groupid'] != 18)
    {
      message('您不是收货员,不能核销此包裹',$this->createMobileUrl('member/center'),'error');
    }

    $orderdata = pdo_fetchall('select * from ' . tablename('customs_packing_list') . ' where pack_ordersn = '.$pack_ordersn);
    foreach ($orderdata as $key => &$row) {
      $condition = ' and o.ordersn = :ordersn ';
      $params = array(':ordersn' => $row['shop_ordersn']);

      $orderdata[$key]['good_list'] = pdo_fetchall("select og.*,g.title from " . tablename('sz_yi_order') . " as o inner join ".tablename('sz_yi_order_goods')." as og on o.id = og.orderid inner join ".tablename('sz_yi_goods')." as g on og.goodsid = g.id where 1 " . $condition, $params);

    }
    unset($row);
    include $this->template('member/easydeliver_collect_code');
  }
  elseif($operation == 'check'){
    //先完善资料
    mc_require($_W['fans']['uid'], array('realname', 'mobile'));
    $pack_data = pdo_get('customs_packing_list',array('pack_ordersn'=>$pack_ordersn));

    if($pack_data['is_collect']==1)
    {
      message('该包裹已经核销！',$this->createMobileUrl('member/easydeliver_collect_user_manage'));
    }
    $data = array(
        'is_collect' => 1,
        'collect_time' => time()
    );

    pdo_update('customs_packing_list', $data , array('id'=>$pack_data['id']));
    //更新操作记录
    $logdata = array(
      'status' => 1,
    );
    pdo_update('package_collect_people_log', $logdata ,array('pack_ordersn'=>$pack_ordersn));

    //判断该订单下的包裹是否全部揽收完毕
    $packdata = pdo_get('package_collect',array('id'=>$pack_id));
    $pack_array = explode(",",$packdata['collect_order']);
    $all_pack_num = count($pack_array);

    $is_collect_num = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('customs_packing_list'). " where pack_ordersn in(".$packdata['collect_order'].") and is_collect = 1 ");
    if($is_collect_num==$all_pack_num)
    {
      $okdata = array(
          'status' => '揽收完成'
      );
      pdo_update('package_collect', $okdata , array('id'=>$pack_id));
    }

    $select_user = pdo_get('mc_mapping_fans',['uid'=>$packdata['select_user']],['openid']);
    //插入分拣列表
    $selectdata = array(
      'pack_ordersn' => $pack_ordersn,
      'openid' => $select_user['openid'],
      'create_time' => time(),
      'status' => 0,
      'is_change' =>0
    );
    pdo_insert('package_select', $selectdata);

    //消息通知
    message('包裹核销完成！',$this->createMobileUrl('member/easydeliver_collect_user_manage'));
  }
}




?>
