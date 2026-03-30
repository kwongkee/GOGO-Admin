<?php

global $_W, $_GPC;
$do = (string) trim($_GPC['sdo']);
$_W['module_setting'] = $this->module['config'];

if ($do == 'send_work_credit') {
    //投稿
    $credit = $_GPC['credit'];
    $is_check = $_GPC['is_check'];
    $log_id = $_GPC['log_id'];
    $now = time();
    $credit_log_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_credit_log') . " WHERE id={$log_id} and status=2";
    $credit_log_info = pdo_fetch($credit_log_sql);
    if ($is_check == 1) {
        //审核通过
        if (!$credit_log_info) {
            echo "审核id错误";
            die;
        }
        $fans_credit = $credit;
        $update_mem = "UPDATE  " . tablename('wxz_shoppingmall_fans') . " set credit=credit+{$fans_credit},left_credit=left_credit+{$fans_credit} where uid={$credit_log_info['fans_id']}"; //添加积分
        $ret = pdo_query($update_mem);
        if ($ret) {
            $update_credit = "UPDATE  " . tablename('wxz_shoppingmall_credit_log') . " set num={$fans_credit},event_desc='投稿审核通过获取{$fans_credit}积分',status=1,send_time={$now},ischeck=1 where id={$credit_log_info['id']}"; //添加积分
            $ret = pdo_query($update_credit);
        }
        echo 'ok';
        die;
    } else {
        $fans_credit = 50; //审核不通过
        $update_mem = "UPDATE  " . tablename('wxz_shoppingmall_fans') . " set credit=credit+{$fans_credit},left_credit=left_credit+{$fans_credit} where uid={$credit_log_info['fans_id']}"; //添加积分
        $ret = pdo_query($update_mem);
        if ($ret) {
            $update_credit = "UPDATE  " . tablename('wxz_shoppingmall_credit_log') . " set num={$fans_credit},event_desc='投稿审核不通过获取{$fans_credit}积分',status=1,send_time={$now},ischeck=2 where id={$credit_log_info['id']}"; //添加积分
            $ret = pdo_query($update_credit);
        }
        echo 'ok';
        die;
    }
} else if ($do == 'check_fans') {
    $is_check = $_GPC['is_check'];
    $fans_id = $_GPC['fans_id'];
    $member_type = 1;
    if ($is_check == 1) {
        $member_type = 2;
    }
    $update_sql = "UPDATE " . tablename("wxz_shoppingmall_fans") . " set `ischeck`={$is_check},`member_type`={$member_type} where uid={$fans_id}";
    $ret = pdo_query($update_sql);
    echo "ok";
    die;
} else if ($do == 'del_coupon') {
    $id = $_GPC['award_id'];
    $update_sql = "UPDATE " . tablename("wxz_shoppingmall_coupon") . " set `isdel`=1 where id={$id}";
    $ret = pdo_query($update_sql);
    echo "ok";
    die;
} else if ($do == 'del_shop') {
    $id = $_GPC['id'];
    $update_sql = "UPDATE " . tablename("wxz_shoppingmall_shop") . " set `isdel`=1 where id={$id}";
    $ret = pdo_query($update_sql);
    echo "ok";
    die;
} else if ($do == 'del_fans') {
    $id = $_GPC['id'];
    $del = pdo_delete('wxz_shoppingmall_fans', array('uid' => $id));
    echo "ok";
    die;
} else if ($do == 'del_share') {
    $id = $_GPC['id'];
    $del = pdo_delete('wxz_shoppingmall_share', array('id' => $id));
    echo "ok";
    die;
} else if ($do == 'del_msg') {
    $id = $_GPC['id'];
    $update_sql = "UPDATE " . tablename("wxz_shoppingmall_msg") . " set `isdel`=1 where id={$id}";
    $ret = pdo_query($update_sql);
    echo "ok";
    die;
} else if ($do == 'del_shop_activity') {
    $id = $_GPC['id'];
    $update_sql = "UPDATE " . tablename("wxz_shoppingmall_shop_activity") . " set `isdel`=1 where id={$id}";
    $ret = pdo_query($update_sql);
    echo "ok";
    die;
} else if ($do == 'del_award') {
    $id = $_GPC['award_id'];
    $update_sql = "UPDATE " . tablename("wxz_shoppingmall_award") . " set `isdel`=1 where id={$id}";
    $ret = pdo_query($update_sql);
    echo "ok";
    die;
} else if ($do == 'del_page') {
    $id = $_GPC['id'];
    $update_sql = "UPDATE " . tablename("wxz_shoppingmall_page") . " set `isdel`=1 where id={$id}";
    $ret = pdo_query($update_sql);
    echo "ok";
    die;
} elseif ($do == 'recommen_bug') {
    //推荐到访操作
    $credit_status1 = '100'; //到访积分
    $credit_status2 = '0'; //签约积分
    $id = $_GPC['id'];
    $status = $_GPC['status'];
    $credit_log_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_credit_log') . " WHERE id={$id}";
    $credit_log_info = pdo_fetch($credit_log_sql);
    if ($status == 1) {
        $fans_credit = $credit_status1;
    } else if ($status == 2) {
        $fans_credit = $credit_status2;
    }

    $update_sql = "UPDATE " . tablename("wxz_shoppingmall_credit_log") . " set `visit_status`={$status} where id={$id}";
    $ret = pdo_query($update_sql);
    if ($ret) {
        //添加积分
        $update_mem = "UPDATE  " . tablename('wxz_shoppingmall_fans') . " set credit=credit+{$fans_credit},left_credit=left_credit+{$fans_credit} where uid={$credit_log_info['fans_id']}"; //添加积分
        $ret = pdo_query($update_mem);
    }
    echo "ok";
    die;
} elseif ($do == 'level') {
    $id = $_GPC['fans_id'];
    $level = $_GPC['level'];
    $update_sql = "UPDATE " . tablename("wxz_shoppingmall_fans") . " set `level`=$level where uid={$id}";
    $ret = pdo_query($update_sql);
    echo "ok";
    die;
} elseif ($do == 'credit_operate') {
    $fans_credit = $_GPC['credit'];
    if ($fans_credit <= 0) {
        echo '积分必须大于0';
        die;
    }
    //积分日志
    $credit_log_data = array(
        'uniacid' => $_W['uniacid'],
        'fans_id' => $_GPC['fans_id'],
        'type' => 1,
        'event_type' => 6,
        'num' => $fans_credit,
        'status' => 2,
        'send_time' => time(),
        'create_at' => time(),
    );
    if ($_GPC['type'] == 1) {
        //增加积分  
        $update_mem = "UPDATE  " . tablename('wxz_shoppingmall_fans') . " set credit=credit+{$fans_credit},left_credit=left_credit+{$fans_credit} where uid={$_GPC['fans_id']}"; //添加积分
        $ret = pdo_query($update_mem);
        $credit_log_data['event_desc'] = '后台用户增加积分';
        $credit_log_data['remark'] = $_GPC['desc'];
    } elseif ($_GPC['type'] == 2) {
        //减少积分
        $credit_log_data['operate'] = 2;
        $update_mem = "UPDATE  " . tablename('wxz_shoppingmall_fans') . " set credit=credit-{$fans_credit},left_credit=left_credit-{$fans_credit} where uid={$_GPC['fans_id']}"; //添加积分
        $ret = pdo_query($update_mem);
        $credit_log_data['event_desc'] = '后台用户减少积分';
        $credit_log_data['remark'] = $_GPC['desc'];
    }
    $ret = pdo_insert('wxz_shoppingmall_credit_log', $credit_log_data);
    echo 'ok';
} else if ($do == 'send_update_ticket_credit') {
    require_once WXZ_SHOPPINGMALL . '/source/Credit.class.php';
    $modelCredit = new Credit();

    //拍小票
    $credit = (int) $_GPC['credit'];
    $is_check = $_GPC['is_check'];
    $log_id = $_GPC['log_id'];
    $remark = $_GPC['remark'];
    $now = time();
    $credit_log_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_credit_log') . " WHERE id={$log_id} and `ischeck`=0";
    $credit_log_info = pdo_fetch($credit_log_sql);

    if (!$credit_log_info) {
        echo "记录不存在或已经处理";
        die;
    }

    if ($is_check == 1) {
        //审核通过
        if ($credit <= 0) {
            echo "审核通过，赠送积分不能小于0";
            die;
        }

        $fans_credit = $credit;
        $ret = $modelCredit->userAddCredit($credit_log_info['fans_id'], $fans_credit);

        if ($ret) {
            $update_credit = "UPDATE  " . tablename('wxz_shoppingmall_credit_log') . " set num={$fans_credit},event_desc='拍小票审核通过获取{$fans_credit}积分',status=1,send_time={$now},ischeck=1,remark='{$remark}' where id={$credit_log_info['id']}"; //添加积分
            $ret = pdo_query($update_credit);

            //给用户发审核通过消息
            require_once WXZ_SHOPPINGMALL . '/source/Msg.class.php';
            $title = "小票审核通过";
            $date = date('Y年m月d日', $credit_log_info['create_at']);
            $desc = "您（{$date}）拍摄的小票审核通过，获得{$fans_credit}积分。欢迎再次光顾消费";
            Msg::addMsg($credit_log_info['fans_id'], 3, $title, $desc);
        }
        echo 'ok';
        die;
    } else {
        $update_credit = "UPDATE  " . tablename('wxz_shoppingmall_credit_log') . " set event_desc='拍小票审核不通过',status=1,send_time={$now},ischeck=2,remark='{$remark}' where id={$credit_log_info['id']}"; //添加积分
        $ret = pdo_query($update_credit);
        echo 'ok';
        die;
    }
} elseif ($do == 'save_shop_order') {
    //保存商铺排序
    $ids = $_GPC['ids'];
    $orders = $_GPC['orders'];

    foreach ($ids as $k => $id) {
        $data = array(
            'order' => $orders[$k],
        );
        pdo_update('wxz_shoppingmall_shop', $data, array('id' => $id));
    }
    echo 'ok';
    die;
}
?>
