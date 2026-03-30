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
    $condition = ' and openid=:openid and deleted=0 and  `uniacid` = :uniacid  ';
    $params = array(':uniacid' => $_W['uniacid'], ':openid' => $openid);
    $sql = 'SELECT * FROM ' . tablename('sz_yi_member_address') . ' where 1 ' . $condition . ' ORDER BY `id` DESC ';
    $list = pdo_fetchall($sql, $params);
    include $this->template('member/travel_express/address');
}else if ($operation == 'new') {
    include $this->template('member/travel_express/address_add');
}else if ($operation == 'get') {
    $id = intval($_GPC['id']);
	$data = pdo_fetch('select * from ' . tablename('sz_yi_member_address') . ' where id=:id and deleted=0 and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $id));
    include $this->template('member/travel_express/address_edit');
}else{
    //地址提交新增
    if (($operation == 'submit') && $_W['ispost']) {
        $id = intval($_GPC['id']);
        $data['realname'] = $_GPC['realname'];
        $data['mobile'] = $_GPC['mobile'];
        $data['idcard'] = $_GPC['idcard'];
        $data['province'] = $_GPC['province'];
        $data['city'] = $_GPC['city'];
        $data['area'] = $_GPC['area'];
        $data['address'] = $_GPC['address'];
        $data['zipcode'] = $_GPC['zipcode'];
        $data['openid'] = $openid;
        $data['uniacid'] = $_W['uniacid'];
        $data['province_code'] = $_GPC['province_code'];
        $data['city_code'] = $_GPC['city_code'];
        $data['area_code'] = $_GPC['area_code'];

        if (empty($id)) {
            $addresscount = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('sz_yi_member_address') . ' where openid=:openid and deleted=0 and `uniacid` = :uniacid ', array(':uniacid' => $_W['uniacid'], ':openid' => $openid));

            if ($addresscount <= 0) {
                $data['isdefault'] = 1;
            }

            pdo_insert('sz_yi_member_address', $data);
            $id = pdo_insertid();
        }
        else {
            pdo_update('sz_yi_member_address', $data, array('id' => $id, 'uniacid' => $_W['uniacid'], 'openid' => $openid));
        }

        show_json(1, array('addressid' => $id,'msg' => '保存成功'));
    }else{
        if (($operation == 'remove') && $_W['ispost']) {
            $id = intval($_GPC['id']);
            $data = pdo_fetch('select id,isdefault from ' . tablename('sz_yi_member_address') . ' where  id=:id and openid=:openid and deleted=0 and uniacid=:uniacid  limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $openid, ':id' => $id));

            if (empty($data)) {
                show_json(0, '地址未找到');
            }

            pdo_update('sz_yi_member_address', array('deleted' => 1), array('id' => $id));

            if ($data['isdefault'] == 1) {
                pdo_update('sz_yi_member_address', array('isdefault' => 0), array('uniacid' => $_W['uniacid'], 'openid' => $openid, 'id' => $id));
                $data2 = pdo_fetch('select id from ' . tablename('sz_yi_member_address') . ' where openid=:openid and deleted=0 and uniacid=:uniacid order by id desc limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $openid));

                if (!empty($data2)) {
                    pdo_update('sz_yi_member_address', array('isdefault' => 1), array('uniacid' => $_W['uniacid'], 'openid' => $openid, 'id' => $data2['id']));
                    show_json(1, array('defaultid' => $data2['id'], 'message' => '删除成功'));
                }
            }

            show_json(1,array('message' => '删除成功'));
        }
    }
}
