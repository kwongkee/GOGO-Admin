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
  if($operation == 'display')
  {
    $list = pdo_fetchall('select * from ' . tablename('package_select') . ' where openid = "'.$openid.'" ');

    foreach ($list as $key => &$row) {
      $list[$key]['create_time'] = date('Y-m-d H:i:s',$row['create_time']);
    }
    unset($row);
    include $this->template('member/easydeliver_select_user_manage');
  }
}




?>
