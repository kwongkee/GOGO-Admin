<?php
/**
 * User: 绑定集运用户
 * Date: 2022/7/25
 * Time: 14:11
 */
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;
$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';
$openid = $_W['openid'];

if($op=='display'){
    //查看是否已绑定用户
    $is_bind = pdo_fetch('select id from '.tablename('centralize_user').' where openid=:openid',[':openid'=>$openid]);
    if(!empty($is_bind['id'])){
        print_r('<h1>您已绑定集运用户，感谢您的支持！</h1>');die;
    }
    include $this->template('warehouse/bind_user/index');
}elseif($op=='bind_user'){
    $data = $_GPC;

    //1、查询是否有此用户
    $centralize_user = pdo_fetch('select * from '.tablename('centralize_user').' where email=:email and mobile=:mobile and id=:id',[':email'=>trim($data['email']),':mobile'=>trim($data['mobile']),':id'=>intval($data['user_id'])]);
    if(empty($centralize_user['id'])){
        show_json(-1,['msg'=>'绑定失败，暂无此用户！']);
    }

    pdo_update('centralize_user',['openid'=>$openid],['email'=>trim($data['email']),'mobile'=>trim($data['mobile']),'id'=>intval($data['user_id'])]);

    //2、查询是否已有sz_yi_member商城用户
    $is_have_member = pdo_fetch('select id from '.tablename('sz_yi_member').' where openid=:openid',[':openid'=>$openid]);
    if(empty($is_have_member['id'])){
        pdo_insert('sz_yi_member',[
            'uniacid'=>$_W['uniacid'],
            'openid'=>$openid,
            'realname'=>$centralize_user['realname'],
            'nickname'=>$centralize_user['name'],
            'mobile'=>trim($data['mobile']),
            'pwd'=>md5('888888'),
            'createtime'=>TIMESTAMP,
        ]);
    }

    show_json(0,['msg'=>'绑定成功，正在刷新页面...']);
}
elseif($op=='warehouse_manager'){
    //查看是否已绑定用户
    $is_bind = pdo_fetch('select id from '.tablename('warehouse_manager').' where openid=:openid',[':openid'=>$openid]);
    if(!empty($is_bind['id'])){
        print_r('<h1>您已绑定仓储管理员，感谢您的支持！</h1>');die;
    }
    $warehouse_list = pdo_fetchall('select * from '.tablename('centralize_warehouse_list').' where 1');
    include $this->template('warehouse/bind_user/warehouse_index');
}
elseif($op=='bind_manager'){
    $data = $_GPC;

    //1、查询是否有此用户
    $warehouse_user = pdo_fetch('select * from '.tablename('warehouse_manager').' where warehouse_id=:warehouse_id and mobile=:mobile',[':warehouse_id'=>intval($data['warehouse_id']),':mobile'=>trim($data['mobile'])]);
    if(empty($warehouse_user['id'])){
        show_json(-1,['msg'=>'绑定失败，暂无此仓库的管理员！']);
    }

    pdo_update('warehouse_manager',['openid'=>$openid],['warehouse_id'=>trim($data['warehouse_id']),'mobile'=>trim($data['mobile'])]);

    //2、查询是否已有sz_yi_member商城用户
    $is_have_member = pdo_fetch('select id from '.tablename('sz_yi_member').' where openid=:openid',[':openid'=>$openid]);
    if(empty($is_have_member['id'])){
        pdo_insert('sz_yi_member',[
            'uniacid'=>$_W['uniacid'],
            'openid'=>$openid,
            'realname'=>$warehouse_user['name'],
            'nickname'=>$warehouse_user['name'],
            'mobile'=>trim($data['mobile']),
            'pwd'=>md5('888888'),
            'createtime'=>TIMESTAMP,
        ]);
    }

    show_json(0,['msg'=>'绑定成功，正在刷新页面...']);
}