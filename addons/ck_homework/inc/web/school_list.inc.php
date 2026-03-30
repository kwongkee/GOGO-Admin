<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->func('tpl');
$op = $_GPC['op'];
$urlt = $this->createWebUrl('theclass_list');
$urlt1 = $this->createWebUrl('school_list');

if ($op == 'add') {
    pdo_insert('onljob_school', [
        'school_code' => trim($_GPC['school_code']),
        'school_name' => trim($_GPC['school_name']),
        'school_addr' => trim($_GPC['school_addr']),
        'create_time' => time(),
        'weid' => $_W['uniacid']
    ]);
    exit(json_encode(['code' => 0, 'message' => '添加成功']));
} elseif ($op == 'delete') {
    if (isset($_GPC['id'])) {
        pdo_delete('onljob_school', ['id' => intval($_GPC['id'])]);
    } else {
        foreach ($_GPC['ids'] as $val) {
            pdo_delete('onljob_school', ['id' => $val]);
        }
    }
    message('删除成功', $urlt1, 'success');
}


$pindex = max(1, intval($_GPC['page']));
$psize = 10;
$where = '';
if ($_GPC['school_code']!=''){
    $where = ' and school_code="'.$_GPC['school_code'].'"';
}
if ($_GPC['school_name']!=""){
    $where = ' and school_name="'.$_GPC['school_name'].'"';
}
$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('onljob_school') . " WHERE weid = {$_W['uniacid']} {$where}");
if ($total) {
    $list = pdo_fetchall("SELECT * FROM " . tablename('onljob_school') . " WHERE weid = '{$_W['uniacid']}' {$where} LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
}
$pager = pagination($total, $pindex, $psize);

include $this->template('school_list');