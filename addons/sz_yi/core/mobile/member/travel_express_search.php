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
    $condition = ' and openid=:openid and uniacid=:uniacid ';
    $params = array('openid'=>$openid, 'uniacid'=>$_W['uniacid']);
    $sql = 'SELECT * FROM ' . tablename('customs_travelexpress_order_info') . ' where 1 ' . $condition . ' ORDER BY `id` ASC ';
    $order = pdo_fetchall($sql, $params);
    foreach ($order as $k => $v)
    {
        switch($v['status']){
            case 0:
                $order[$k]['status'] = '审核中';
                break;
            case 1:
                $order[$k]['status'] = '审核通过';
                break; 
            case 2:
                $order[$k]['status'] = '审核不通过';
                break; 
            case 3:
                $order[$k]['status'] = '已确认';
                break;            
        }
    }
    include $this->template('member/travel_express/search');
}
