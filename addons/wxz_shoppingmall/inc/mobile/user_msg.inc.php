<?php

//用户消息列表
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';

$modulePublic = '../addons/' . $_GPC['m'] . '/static/';

if (!$user['mobile']) {
    header('Location: ' . $this->createMobileUrl('reg'));
    exit;
}


//用户消息

$table = tablename('wxz_shoppingmall_msg');
$table2 = tablename('wxz_shoppingmall_fans_msg');

//用户自己的消息
$sql = "select $table.title,$table.desc,$table.create_at from $table2 left join $table on $table2.msg_id=$table.id where $table2.fans_id={$user['uid']}  AND $table2.uniacid={$_W['uniacid']} AND $table.isdel=0 AND $table.type!=1 ORDER BY $table.id DESC";
$list1 = pdo_fetchall($sql, $pars);//系统消息

$condition = "uniacid={$_W['uniacid']} AND isdel=0 AND type=1";

$sql = "SELECT * FROM $table  WHERE {$condition} ORDER BY id DESC";
$list = pdo_fetchall($sql, $pars);//系统消息

$list = array_merge($list1,$list);

array_multisort(array_column($list, 'create_at'),SORT_DESC,$list);

include $this->template('user_msg');
?>
