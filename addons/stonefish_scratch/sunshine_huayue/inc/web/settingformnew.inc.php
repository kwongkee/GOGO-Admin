<?php 
global $_GPC, $_W;
$data = array();
$data['uniacid'] = $_W['uniacid'];
$data['name'] = $_GPC['settings_name'];
$data['value'] = $_GPC['settings_value'];
$data['add_time'] = date('Y-m-d H:i:s');
$save_type = $_GPC['save_type'];
if ($save_type === 'file') {
    $path = dirname(dirname(dirname(preg_replace('@\\(.*\\(.*$@', '', __FILE__))));
    $dirpath = $path . "/configs/{$_W['uniacid']}/";
    if (!is_dir($dirpath)) {
        mkdir($dirpath, 511, true);
    }
    $fh = fopen($dirpath . '/' . $data['name'], 'w+');
    fwrite($fh, $data['value']);
    echoJson(array('res' => '100', 'msg' => 'success'));
} else {
    $r = pdo_fetch('select * from ' . tablename('sunshine_huayue_setting') . " where uniacid='{$_W['uniacid']}' and name='{$data['name']}'");
    if ($r) {
        pdo_update('sunshine_huayue_setting', $data, array('uniacid' => $_W['uniacid'], 'name' => $data['name']));
    } else {
        pdo_insert('sunshine_huayue_setting', $data);
    }
    echoJson(array('res' => '100', 'msg' => 'success'));
}