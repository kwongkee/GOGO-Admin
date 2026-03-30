<?php

defined('IN_IA') or exit('Access Denied');

global $_W;
global $_GPC;

$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');
$openid = m('user')->getOpenid();
$popenid = m('user')->islogin();
$openid = ($openid ? $openid : $popenid);
$member = m('member')->getMember($openid);

if ($operation == 'display') {
    if( $_GPC['uid'] == '' )
    {
        echo '缺少参数';
    }else{
        // 仓库管理员
        $manager = pdo_fetch('select * from ' . tablename('warehouse_manager') . ' where  id='.intval($_GPC['uid']));
        $job = '';
        switch($manager['type']){
            case 1:
                $job = '国内仓库管理员';
                break;
            case 2:
                $job = '香港仓库管理员';
                break;
            case 3:
                $job = '国外仓库管理员';
                break;
        }
        $warehouse_name = pdo_fetchcolumn('select warehouse_name from '.tablename('centralize_warehouse_list').' where id=:id',[':id'=>$manager['warehouse_id']]);
        include $this->template('member/warehouse_account_check/check');
    }
}