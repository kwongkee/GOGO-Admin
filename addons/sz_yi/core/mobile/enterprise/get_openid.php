<?php
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

if( $_W['openid'] ) {
    if (empty($openid)) {
        //当用户没有关注公众号要先关注
        header('Location:https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzA4MDQyMTMxMQ==&scene=110#wechat_redirect');
    }

//    if (empty($_GPC['id'])) {
//        exit('<h1>参数错误</h1>');
//    }

    if($_W['ispost']){
        $data = $_GPC;
        #查询是否有这个手机号
        $is_have = pdo_fetch('select * from ' . tablename('centralize_manage_person') . ' where tel=:tel', [':tel' => trim($data['tel'])]);
        if(empty($is_have['id'])){
            echo json_encode(['code'=>-1,'msg'=>'查无记录！']);exit;
        }

        $enterprise_members = pdo_fetch('select * from ' . tablename('enterprise_members') . ' where centralizer_id=:cen_id', [':cen_id' => $is_have['id']]);
        if (empty($enterprise_members['openid'])) {
            pdo_begin();
            try {
                pdo_update('centralize_manage_person', ['openid' => $openid], ['id' => $is_have['id']]);
                pdo_update('enterprise_members', ['openid' => $openid], ['id' => $enterprise_members['id']]);
                pdo_update('decl_user', ['openid' => $openid], ['user_tel' => $enterprise_members['mobile']]);
                pdo_update('total_merchant_account', ['openid' => $openid], ['enterprise_id' => $enterprise_members['id'], 'mobile' => $enterprise_members['mobile']]);
                pdo_commit();
            } catch (\Exception $e) {
                pdo_rollback();
                echo json_encode(['code'=>-1,'msg'=>'系统错误！']);exit;
            }
        }
        echo json_encode(['code'=>0,'msg'=>'绑定成功！']);exit;
    }else{
        $enterprise_members = pdo_fetch('select * from ' . tablename('enterprise_members') . ' where openid=:openid', [':openid' => $openid]);
        include $this->template('enterprise/get_openid');
    }
//    $id = intval(base64_decode($_GPC['id']));
//    $enterprise_members = pdo_fetch('select * from ' . tablename('enterprise_members') . ' where centralizer_id=:cen_id', [':cen_id' => $id]);
//    if (empty($enterprise_members['openid'])) {
//        pdo_begin();
//        try {
//            pdo_update('centralize_manage_person', ['openid' => $openid], ['id' => $id]);
//            pdo_update('enterprise_members', ['openid' => $openid], ['id' => $enterprise_members['id']]);
//            pdo_update('decl_user', ['openid' => $openid], ['user_tel' => $enterprise_members['mobile']]);
//            pdo_update('total_merchant_account', ['openid' => $openid], ['enterprise_id' => $enterprise_members['id'], 'mobile' => $enterprise_members['mobile']]);
//            pdo_commit();
//        } catch (\Exception $e) {
//            pdo_rollback();
//            exit('<h1>系统错误</h1>');
//        }
//    }


}else{
    message('请在微信打开！','','error');
}

