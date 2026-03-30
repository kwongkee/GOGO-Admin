<?php
/**
 * Created by PhpStorm.
 * User: HeJunXin
 * Date: 2021/10/19
 * Time: 14:15
 */

header('Access-Control-Allow-Origin: *');

// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;
@session_start();
setcookie('preUrl', $_W['siteurl']);
$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');
$openid = m('user')->getOpenid();
$popenid = m('user')->islogin();
$openid = ($openid ? $openid : $popenid);
$member = m('member')->getMember($openid);
$uniacid = $_W['uniacid'];
$this->yzShopSet = m('common')->getSysset('shop');

$enterprise_members = pdo_fetch('select * from ' . tablename('enterprise_members') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));

if(empty($enterprise_members['id'])){
    //还没创建用户，则跳转到商户注册页面
    $url = 'Location:/app/index.php?i=' . $_W['uniacid'] . '&c=entry&do=enterprise&m=sz_yi&p=register';
    return header($url);
}else{
    $user = pdo_fetch('select id,user_tel from ' . tablename('decl_user') . ' where openid=:openid and uniacid=:uni limit 1',array(':uni'=>$_W['uniacid'],':openid'=>$_W['openid']));

    //1、判断该商户有无运单号
    $canUseWaybill = pdo_fetchall('select c.number from '.tablename('decl_user').' as a left join '.tablename('enterprise_basicinfo').' as b on a.enterprise_id=b.member_id left join '.tablename('waybill_numberassign_list').' as c on b.id=c.merchant_id where a.id=:uid and c.status=0',array(':uid'=>$user['id']));
    foreach($canUseWaybill as $k => $v){
        $canUseWaybill[$k] = $v['number'];
    }
    $waybillNum = count($canUseWaybill);
    $canUseWaybill = array_shift($canUseWaybill).'-'.array_pop($canUseWaybill);
    if($canUseWaybill=='-' || $waybillNum<=10){
        //没有运单号
        echo '<h1>请联系管理员添加运单号！</h1>';exit;
    }

    //2、获取商家上传的商品
    $allgoods = pdo_fetchall('select b.goodssn from '.tablename('customs_goods_shelf_linked').' as a left join '.tablename('sz_yi_goods').' as b on a.uniacid=b.supplier_uid where a.user_id=:uid and b.status=1',array(':uid'=>$user['id']));

    //3、获取计量单位
    $unit = pdo_fetchall('select * from '.tablename('unit').' where 1');

    include $this->template('distribution/online');
}
