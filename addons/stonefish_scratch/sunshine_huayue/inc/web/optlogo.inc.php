<?php 
global $_GPC, $_W;
if (!$_W['isfounder']) {
    echoJson(array('res' => '111', 'msg' => '无权限！请联系站长管理员'));
}
if ($_GPC['dataType'] != 'opt_logo' || $_GPC['setval'] != 'handled2') {
    echoJson(array('res' => '101', 'msg' => '无权限！请联系站长管理员!'));
}
pdo_begin();
$data = array();
$data['name'] = 'opt_logo';
$data['value'] = 'handled2';
$data['add_time'] = date('Y-m-d H:i:s');
$info = pdo_insert('sunshine_huayue_setting', $data);
if (!$info) {
    pdo_rollback();
    echoJson(array('res' => '103', 'msg' => '无权限！请联系站长管理员'));
}
$list = pdo_fetchall('select * from ' . tablename('sunshine_huayue_chatroom'));
foreach ($list as $item) {
    $url = tomedia($item['room_logo']);
    pdo_update('sunshine_huayue_chatroom', array('room_logo' => $url), array('id' => $item['id']));
}
pdo_commit();
echoJson(array('res' => '100', 'msg' => 'success'));