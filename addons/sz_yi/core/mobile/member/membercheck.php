<?php

// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;
if ($_W['isajax']) {
    $id = isset($_GPC['id']) && is_numeric($_GPC['id']) ?$_GPC['id']:show_json(0, '参数错误');
    $status = isset($_GPC['status'])&&!empty($_GPC['status'])?$_GPC['status']: show_json(0, '参数错误');
    $openid = pdo_get('sz_yi_member', array('id' => $id), array('openid','mobile'));
    if ($status == 'no') {
        pdo_delete('sz_yi_member', array('id' => $id));
        pdo_delete('sz_yi_member_address', array('openid' => $openid['openid']));
        send_reg_check_alidayu($openid['mobile'],"不通过");
    } else if ($status == 'yes') {
        $result = pdo_update("sz_yi_member",array('isblack'=>0),array('id'=>$id));
        if (empty($result)) {
            show_json(0, '操作失败');
        }
        send_reg_check_alidayu($openid['mobile'],"通过");
    }
    show_json(1, '操作成功');
} else {
    $total = pdo_fetchall("select count(id) as total from " . tablename('sz_yi_member') . " where isblack=1")['total'];
    $pageindex = isset($_GPC['page']) ? intval($_GPC['page'], 1) : 1;
    $pagesize = 8;
    $pager = pagination($total, $pageindex, $pagesize);
    $p = ($pageindex - 1) * $pagesize;
    $list = pdo_fetchall("select a1.id as uid,a1.realname,a1.mobile,a1.id_card,a2.* from " . tablename('sz_yi_member') . " as a1 left join " . tablename('sz_yi_member_address') . " as a2 on a1.openid=a2.openid where isblack=1 limit " . $p . "," . $pagesize);
    include $this->template('member/membercheck');
}
