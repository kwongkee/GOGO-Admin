<?php

global $_W, $_GPC;
$do = (string) trim($_GPC['sdo']);
session_start();

/**
 * 执行动作
 */
require_once WXZ_SHOPPINGMALL . '/source/Fans.class.php';
$f = new Fans();
$openid = $_SESSION['__:proxy:openid'];
$user = $f->getOne($openid, true);
$_W['module_setting'] = $this->module['config'];

if ($do == 'regmsg') {
    require_once WXZ_SHOPPINGMALL . '/func/common.func.php';
    $result = array(
        'error_code' => 0,
        'error_msg' => '系统错误',
    );

    if ($user['mobile'] && $user['birthday']) {
        $result = array(
            'error_code' => 3,
            'error_msg' => '已注册,不能重复注册',
        );
        echo json_encode($result);
        exit;
    }

    if ($user['mobile'] && !$user['birthday']) {
        $result = array(
            'error_code' => 2,
            'error_msg' => '已注册,请去完善资料',
        );
        echo json_encode($result);
        exit;
    }

    $data['username'] = $_GPC['username'];
    $data['mobile'] = $_GPC['mobile'];
    $verify_code = $_GPC['verify_code'];

    $pattern = "/^((\d{3}-\d{8}|\d{4}-\d{7,8})|(1[3|5|7|8][0-9]{9}))$/";
    $s = preg_match($pattern, $data['mobile']);


    if (!$data['username']) {
        $result = array(
            'error_code' => 0,
            'error_msg' => '用户名不能为空',
        );
        echo json_encode($result);
        exit;
    }

    if (!$data['mobile']) {
        $result = array(
            'error_code' => 0,
            'error_msg' => '手机号不能为空',
        );
        echo json_encode($result);
        exit;
    }
    if ($s == 0) {
        $result = array(
            'error_code' => 0,
            'error_msg' => '手机号不正确',
        );
        echo json_encode($result);
        exit;
    }

    $phone_count = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('wxz_shoppingmall_fans') . ' WHERE `uniacid`=:uniacid AND `mobile`=:mobile', array(':uniacid' => $_W['uniacid'], ':mobile' => $data['mobile']));
    if ($phone_count) {
        $result = array(
            'error_code' => 0,
            'error_msg' => '该手机号已经注册',
        );
        echo json_encode($result);
        exit;
    }

    $check_res = check_verify_code($user, $verify_code);
    if ($check_res !== true) {
        $result = array(
            'error_code' => 0,
            'error_msg' => $check_res['message'],
        );
        echo json_encode($result);
        exit;
    }

    $data['reg_time'] = time();
    $ret = pdo_update('wxz_shoppingmall_fans', $data, array('uniacid' => $_GPC['i'], 'uid' => $user['uid']));
    if ($ret) {
        $result = array(
            'error_code' => 1,
            'error_msg' => '注册成功',
        );
        echo json_encode($result);
        exit;
    } else {
        $result = array(
            'error_code' => 0,
            'error_msg' => '注册失败',
        );
        echo json_encode($result);
        exit;
    }
} else if ($do == 'mobile_code') {
    $mobile = $_GPC['mobile'];
    $_W['module_setting'] = $this->module['config'];
    $pattern = "/^((\d{3}-\d{8}|\d{4}-\d{7,8})|(1[3|5|7|8][0-9]{9}))$/";
    $s = preg_match($pattern, $mobile);
    if ($s == 0) {
        echo '手机号不正确';
        exit;
    }
    require_once WXZ_SHOPPINGMALL . '/func/common.func.php';
    //短信验证码
    $ret = send_code($mobile, $user);
    if ($ret === true) {
        echo 'ok';
    } else {
        echo $ret['message'];
    }
} else if ($do == 'regmsg2') {
    //完善资料
    require_once WXZ_SHOPPINGMALL . '/func/common.func.php';
    $_W['module_setting'] = $this->module['config'];

    $data['birthday'] = trim($_GPC['birthday']);
    $data['sex'] = $_GPC['sex'];

    if ($_GPC['plate_number0'] && $_GPC['plate_number1'] && $_GPC['plate_number2']) {
        $data['plate_number'] = $_GPC['plate_number0'] . '-' . $_GPC['plate_number1'] . '-' . $_GPC['plate_number2'];
    }

    //完善资料送积分
    require_once WXZ_SHOPPINGMALL . '/source/CreditLog.class.php';
    $mCreditLog = new CreditLog();

    //生日不能修改
    if ($user['birthday'] !== '0000-00-00' && $user['birthday'] != $data['birthday']) {
        myAjaxReturn(0, '生日不能修改，需要修改请联系管理员');
    }

    if ($data['birthday'] && $data['sex']) {
        $mCreditLog->reg2AddAward($user);
    }

    $ret = pdo_update('wxz_shoppingmall_fans', $data, array('uniacid' => $_GPC['i'], 'uid' => $user['uid']));

    $result = array(
        'error_code' => 1,
        'error_msg' => '更新成功',
    );
    echo json_encode($result);
    exit;
} elseif ($do == 'do_exchange') {
    //发放密码
    $award_id = $_GPC['award_id'];
    $exchange_psw = $_GPC['exchange_psw'];
    if ($exchange_psw != 'xindahui') {
        echo '密码错误，请联系管理员核销';
        die;
    }
    $data = array(
        'password' => $exchange_psw,
        'status' => 1,
    );
    $ret = pdo_update('wxz_shoppingmall_exchange', $data, array('id' => $award_id, 'uniacid' => $_GPC['i'], 'fans_id' => $user['uid']));
    if ($ret) {
        echo 'ok';
    } else {
        echo '发放失败';
    }
} elseif ($do == 'verification') {
    $type = $_GPC['type'];
    $code = $_GPC['code'];
    $id = $_GPC['id'];
    $condition = "id={$id} AND uniacid={$_W['uniacid']} AND status=1 AND fans_id={$user['uid']}";
    $log_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_credit_log') . " WHERE id={$id} AND uniacid={$_W['uniacid']} AND status=1 AND fans_id={$user['uid']}";
    $log_info = pdo_fetch($log_sql);

    if (!$log_info) {
        echo '优惠券或者产品不存在或已经被核销';
        exit;
    }

    switch ($type) {
        case 'credit':
            $countAward = pdo_fetchcolumn("SELECT COUNT(*) num FROM " . tablename('wxz_shoppingmall_award') . " WHERE  `id`={$log_info['award_credit_id']} AND password='{$code}'");
            break;
        case 'coupon':
            $countAward = pdo_fetchcolumn("SELECT COUNT(*) num FROM " . tablename('wxz_shoppingmall_coupon') . " WHERE  `id`={$log_info['award_coupon_id']} AND password='{$code}'");
            break;
    }

    if (!$countAward) {
        echo '核销码错误';
        exit;
    }

    $update_sql = "UPDATE " . tablename("wxz_shoppingmall_credit_log") . " set `status`=2 where $condition";
    $ret = pdo_query($update_sql);
    if ($ret) {
        echo 'ok';
    } else {
        echo '核销失败';
    }
} elseif ($do == 'credit_pass') {
    //处理转赠逻辑
    $mobileFans = new Fans();
    $fans = $mobileFans->getByMobile($_GPC['mobile']);

    if (!$fans) {
        myAjaxReturn(0, '用户不存在');
    }
    if ($fans['uid'] == $user['uid']) {
        myAjaxReturn(0, '不能转赠给自己');
    }
    $username = '*' . mb_substr($fans['username'], 1, strlen($fans['username']), 'utf-8');
    myAjaxReturn(1, $username);
} else if ($do == 'do_credit_pass') {
    //处理转赠逻辑
    $mobileFans = new Fans();
    $fans = $mobileFans->getByMobile($_GPC['mobile']);

    if (!$fans) {
        myAjaxReturn(0, '用户不存在');
    }

    if ($fans['uid'] == $user['uid']) {
        myAjaxReturn(0, '不能转赠给自己');
    }

    //判断积分是否足够
    if ($user['left_credit'] < $_GPC['credit']) {
        myAjaxReturn(0, '积分不足');
    }
    $credit = $_GPC['credit'];
    //转赠积分
    $update_fans = "UPDATE  " . tablename('wxz_shoppingmall_fans') . " set credit=credit+{$credit},left_credit=left_credit+{$credit} where uid='{$fans['uid']}'"; //添加积分
    $update_user = "UPDATE  " . tablename('wxz_shoppingmall_fans') . " set left_credit=left_credit-{$credit},use_credit=use_credit+{$credit} where uid='{$user['uid']}'"; //减少积分
    $ret1 = pdo_query($update_fans);
    $ret2 = pdo_query($update_user);
    if ($ret1 && $ret2) {
        //添加日志
        //插入日志
        $credit_log_data = array(
            'uniacid' => $_W['uniacid'],
            'fans_id' => $user['uid'],
            'pass_fans_id' => $fans['uid'],
            'type' => 1,
            'operate' => 2,
            'event_type' => 3,
            'event_desc' => '转赠积分',
            'num' => $credit,
            'remark' => $_GPC['remark'],
            'status' => 2,
            'send_time' => time(),
            'create_at' => time(),
        );
        $ret = pdo_insert('wxz_shoppingmall_credit_log', $credit_log_data);
        require_once WXZ_SHOPPINGMALL . '/source/Msg.class.php';
        //添加转赠消息
        $leftCredit = $fans['left_credit'] + $credit;
        $title = "你的好友向你转赠了积分";
        $desc = "你的好友{$user['username']}，向你转赠{$credit}积分成功，现在剩余积分{$leftCredit}。";
        Msg::addMsg($fans['uid'], 2, $title, $desc);
        myAjaxReturn(1, '转赠成功');
    }
    myAjaxReturn(0, '转赠失败');
} else if ($do == 'activity_sign') {
    //报名
    $shop_activity_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_shop_activity') . " WHERE uniacid={$_W['uniacid']} AND id={$_GPC['acid']} AND isdel=0";
    $shop_activity_info = pdo_fetch($shop_activity_info_sql);

    $sign_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_activity_sign') . " WHERE fans_id={$user['uid']} AND activity_id={$_GPC['acid']}"; //店面列表
    $sign_info = pdo_fetch($sign_info_sql);
    if ($sign_info) {
        myAjaxReturn(0, '已报名');
    }
    if (!$shop_activity_info) {
        myAjaxReturn(0, '活动不存在或已删除');
    }
    $starttime = strtotime($shop_activity_info['expiry_date_start']);
    $endtime = strtotime($shop_activity_info['expiry_date_end']);
    if (!$shop_activity_info) {
        myAjaxReturn(0, '活动不存在或已删除');
    }

    if (($endtime && time() > $endtime) || ($starttime && time() < $starttime)) {
        myAjaxReturn(0, '活动未开始或已结束');
    }
    if ($shop_activity_info['sign_num'] >= $shop_activity_info['can_sign_num']) {
        myAjaxReturn(0, '报名已满');
    }
    //报名
    $update_sql = "UPDATE  " . tablename('wxz_shoppingmall_shop_activity') . " set sign_num=sign_num+1 where id='{$_GPC['acid']}'"; //添加积分
    $ret = pdo_query($update_sql);
    if ($ret) {
        //插入报名表
        $sign_data = array(
            'uniacid' => $_W['uniacid'],
            'fans_id' => $user['uid'],
            'activity_id' => $_GPC['acid'],
            'username' => $_GPC['username'],
            'mobile' => $_GPC['mobile'],
            'remark' => $_GPC['remark'],
            'create_at' => time(),
        );
        $ret = pdo_insert('wxz_shoppingmall_activity_sign', $sign_data);
        myAjaxReturn(1, '报名成功');
    } else {
        myAjaxReturn(0, '报名失败');
    }
} else if ($do == 'upload_ticket') {
    //上传小票
    $img = wxzUploadWxImg($_GPC['serverId']);
    //插入日志
    $credit_log_data = array(
        'uniacid' => $_W['uniacid'],
        'fans_id' => $user['uid'],
        'type' => 1,
        'ticket_img' => $img,
        'operate' => 1,
        'event_type' => 5,
        'event_desc' => '拍小票',
        'remark' => $_GPC['serverId'],
        'status' => 1,
        'create_at' => time(),
    );
    $ret = pdo_insert('wxz_shoppingmall_credit_log', $credit_log_data);
    if ($ret) {
        myAjaxReturn(1, '上传成功');
    } else {
        myAjaxReturn(0, '上传失败');
    }
} elseif ($do == 'award_exchange_credit') {
    //领取积分商品
    $award_id = $_GPC['id'];

    $award_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_award') . " WHERE id={$award_id} AND uniacid={$_W['uniacid']}";
    $award_info = pdo_fetch($award_info_sql);
    if (!$award_info) {
        myAjaxReturn(0, '商品不存在');
    }

    $starttime = strtotime($award_info['expiry_date_start']);
    $endtime = strtotime($award_info['expiry_date_end']);
    if ((time() > $endtime) || (time() < $starttime)) {
        myAjaxReturn(0, '请在有效期内兑换');
    }

    //未兑换
    if ($award_info['num'] <= 0) {
        myAjaxReturn(0, '商品已领完');
    }

    if ($user['left_credit'] < $award_info['credit']) {
        myAjaxReturn(0, '积分不足');
    }

    //每人最大领取数量限制
    if ($award_info['max_exchange_num']) {
        //获取用户领取数量
        $condition = "uniacid={$_W['uniacid']} AND fans_id={$user['uid']} AND award_credit_id={$award_id}";
        $sql = "select count(1) num from " . tablename('wxz_shoppingmall_credit_log') . " where {$condition}";
        $exchangeNumRet = pdo_fetch($sql);
        $exchangeNum = $exchangeNumRet['num'];
        if ($exchangeNum >= $award_info['max_exchange_num']) {
            myAjaxReturn(0, "该商品每个人最多可兑换{$award_info['max_exchange_num']}个");
        }
    }

    $insert_exchange_data = array(
        'uniacid' => $_W['uniacid'],
        'fans_id' => $user['uid'],
        'award_credit_id' => $award_id,
        'type' => 2,
        'event_desc' => "兑换积分商品-{$award_info['credit']}积分",
        'event_type' => 1,
        'num' => $award_info['credit'],
        'create_at' => time(),
    );

    pdo_insert('wxz_shoppingmall_credit_log', $insert_exchange_data);

    //更新用户积分
    $update_mem = "UPDATE  " . tablename('wxz_shoppingmall_fans') . " set left_credit=left_credit-{$award_info['credit']},use_credit=use_credit+{$award_info['credit']} where uid={$user['uid']}"; //消耗积分
    $ret = pdo_query($update_mem);

    if ($ret) {
        //更新库存
        $update_award = "UPDATE  " . tablename('wxz_shoppingmall_award') . " set num=num-1,cashed=cashed+1 where id={$award_info['id']}"; //消耗积分
        $ret2 = pdo_query($update_award);
    }
    myAjaxReturn(1, '兑换成功');
} else if ($do == 'award_exchange_coupon') {
    $award_id = $_GPC['id'];

    $award_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_coupon') . " WHERE id={$award_id} AND uniacid={$_W['uniacid']}";
    $award_info = pdo_fetch($award_info_sql);
    if (!$award_info) {
        myAjaxReturn(0, '优惠券不存在');
    }

    //判断优惠券是否在有效期内
    $starttime = strtotime($award_info['expiry_date_start']);
    $endtime = strtotime($award_info['expiry_date_end']);
    if ((time() > $endtime) || (time() < $starttime)) {
        myAjaxReturn(0, '请在有效期内兑换');
    }

    if ($award_info['level'] > $user['level']) {
        myAjaxReturn(0, '会员等级不够，不能兑换');
    }

    //未兑换
    if ($award_info['num'] <= 0) {
        myAjaxReturn(0, '优惠券已发完');
    }

    if ($user['left_credit'] < $award_info['credit']) {
        myAjaxReturn(0, '积分不足');
    }

    $insert_exchange_data = array(
        'uniacid' => $_W['uniacid'],
        'fans_id' => $user['uid'],
        'award_coupon_id' => $award_id,
        'type' => 2,
        'event_desc' => "兑换积分优惠券-{$award_info['credit']}积分",
        'event_type' => 2,
        'num' => $award_info['credit'],
        'create_at' => time(),
    );

    pdo_insert('wxz_shoppingmall_credit_log', $insert_exchange_data);

    //更新用户积分
    $update_mem = "UPDATE  " . tablename('wxz_shoppingmall_fans') . " set left_credit=left_credit-{$award_info['credit']},use_credit=use_credit+{$award_info['credit']} where uid={$user['uid']}"; //消耗积分
    $ret = pdo_query($update_mem);

    if ($ret) {
        //更新库存
        $update_award = "UPDATE  " . tablename('wxz_shoppingmall_coupon') . " set num=num-1,cashed=cashed+1 where id={$award_info['id']}"; //消耗积分
        $ret2 = pdo_query($update_award);
    }

    myAjaxReturn(1, '兑换成功');
} elseif ($do == 'like_shop') {
    //商铺加赞
    $sql = "SELECT COUNT(*) FROM " . tablename('wxz_shoppingmall_shop_good_record') . " WHERE `uniacid`={$_W['uniacid']} AND fans_id={$user['uid']} AND shop_id={$_GPC['id']}";
    $exist = pdo_fetchcolumn($sql);

    if ($exist) {
        myAjaxReturn(0, '已点过赞');
    }
    $update = "UPDATE  " . tablename('wxz_shoppingmall_shop') . " set good_num=good_num+1 where id={$_GPC['id']}";
    pdo_query($update);
    $insertData = array(
        'uniacid' => $_W['uniacid'],
        'fans_id' => $user['uid'],
        'shop_id' => $_GPC['id'],
        'create_at' => time(),
    );
    pdo_insert('wxz_shoppingmall_shop_good_record', $insertData);
    myAjaxReturn(1, '点赞成功');
}

/**
 * ajax返回格式化
 * @param type $error_code
 * @param type $error_msg
 */
function myAjaxReturn($error_code, $error_msg) {
    $result = array(
        'error_code' => $error_code,
        'error_msg' => $error_msg,
    );
    echo json_encode($result);
    exit;
}

/**
 * 上传图片
 * @global type $_W
 * @global type $_GPC
 * @param type $serverId
 * @return type
 */
function wxzUploadWxImg($serverId) {
    global $_W, $_GPC;

    $filePath = ATTACHMENT_ROOT . "/";
    if (!file_exists($filePath)) {
        mkdir($filePath, true);
    }
    $accessToken = $_W['account']['access_token']['token'];
    $fileName = date('YmdHis') . uniqid() . '.jpg';
    $targetName = $filePath . $fileName;
    $url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$accessToken}&media_id={$serverId}";
    $source = file_get_contents($url);
    $jsonRet = json_decode($source, true);
    //token失效手动获取token
    if ($jsonRet && $jsonRet['errcode'] > 0) {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$_W['account']['key']}&secret={$_W['account']['secret']}";
        $resp = file_get_contents($url);
        $auth = @json_decode($resp, true);

        $record = array();
        $record['token'] = $auth['access_token'];
        $record['expire'] = TIMESTAMP + $auth['expires_in'] - 200;
        $row = array();
        $row['access_token'] = iserializer($record);
        pdo_update('account_wechats', $row, array('acid' => $_W['account']['acid']));
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$record['token']}&media_id={$serverId}";
        $source = file_get_contents($url);
    }
    file_put_contents($targetName, $source);
    return $_W['siteroot'] . "/" . $_W['config']['upload']['attachdir'] . "/" . $fileName;
}

?>
