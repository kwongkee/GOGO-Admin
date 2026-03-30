<?php
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;

$openid=$_W['openid'];
$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';

if($op=='add_port'){
    $customs_codes = pdo_fetchall('select * from '.tablename('customs_codes').' where 1');
    foreach ($customs_codes as $k => $v) {
        $vs = explode(":",$v['AreaCode']);
        $customs_codes[$k]['value_code'] = $vs[0];
    }
    $fans = pdo_fetch('select fanid,openid from '.tablename('mc_mapping_fans').' where openid=:openid',[':openid'=>$openid]);
    include $this->template('declare/portplatform/add_port');
}