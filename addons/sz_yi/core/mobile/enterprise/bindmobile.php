<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
$preUrl = $_COOKIE['preUrl'];

if ($_W['isajax']) {
    if ($_W['ispost']) {
        $mc = $_GPC['memberdata'];

        //更新微信记录里的手机号等
        pdo_update('enterprise_members',
            array(
                'mobile' => $mc['mobile'],
                'idcard' => trim($mc['idcard']),
                'realname' => trim($mc['realname']),
                'reg_type' => trim($mc['reg_type']),
            ),
            array(
                'openid' => $openid,
                'uniacid' => $_W['uniacid']
            )
        );
        if($reg_type==2){
            show_json(1, array(
                'preurl' => './index.php?i=3&c=entry&do=enterprise&m=sz_yi&p=finish&op=finish'
            ));
        }
        show_json(1, array(
            'preurl' => $preUrl
        ));
    }
}

