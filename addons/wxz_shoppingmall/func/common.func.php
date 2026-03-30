<?php

global $_W;

/**
 * 发送短信
 * @global type $_W
 * @param type $mobile
 * @return boolean
 */
function send_code($mobile, $fan_info) {
    global $_W;
    require_once WXZ_SHOPPINGMALL . '/source/Fans.class.php';
    $fan = new Fans();
    $max_send_num = $_W['module_setting']['sms_max_send_msg_num'] ? $_W['module_setting']['sms_max_send_msg_num'] : 3; //一天最多发送短信次数
    if (!$mobile) {
        return error(-1, "手机号不能为空");
    }
    if (!$fan_info) {
        return error(-2, "获取公众号信息错误");
    }
    $fans_update_data = array();
    //是否超过限制
    $today_begin = strtotime(date("Y-m-d 00:00:00"));
    $today_end = strtotime(date("Y-m-d 23:59:59"));
    $update_verify_count = 0; //今天是否发送过验证码
    if ($fan_info['mobile_verify']) {
        $verify_info = explode("_", $fan_info['mobile_verify']);
        $verify_time = $verify_info[2];
        $verify_count = $verify_info[3];
        //一天一个用户只能发送短信三次
        if ($verify_time >= $today_begin && $verify_time <= $today_end) {
            if ($verify_count >= $max_send_num) {
                return error(-4, "今日发送短息验证码已经超过限制");
            }
            $update_verify_count = $verify_count;
        }
    }

    //发送短息
    require_once WXZ_SHOPPINGMALL . '/func/sms_t.php';
    $sms = new sms_t($_W['module_setting']);
    $sms_res = $sms->send_code($mobile);
    if (!$sms_res) {
        return error(-4, "发送短息错误");
    }
    $verify_code = $sms->get_send_code();
    $fans_update_data['mobile_verify'] = $mobile . "_" . $verify_code . "_" . time() . "_" . ($update_verify_count + 1);
    $fan->update_by_id($fan_info['uid'], $fans_update_data);
    return true;
}

/**
 * 检验短信验证码
 * @param type $code
 */
function check_verify_code($fan_info, $code) {
    global $_W;
    require_once WXZ_SHOPPINGMALL . '/source/Fans.class.php';
    $fan = new Fans();
    if (!$fan_info) {
        return error(-2, "获取公众号信息错误");
    }
    $verify_info = explode("_", $fan_info['mobile_verify']);
    $verify_code = $verify_info[1];
    $verify_time = $verify_info[2];
    if ($verify_time + 3600 <= time()) {
        return error(-2, "短信验证码已过期");
    }
    if (!$verify_code || $verify_code != $code) {
        return error(-2, "短信验证码错误");
    }
    $verify_info[1] = '';
    $fans_update_data['mobile_verify'] = implode("_", $verify_info);
    $fan->update_by_id($fan_info['uid'], $fans_update_data);
    return true;
}

/**
 * 获取自定义分享数据
 */
function getShareData() {
    global $_W, $_GPC;
    $info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_share') . " WHERE uniacid={$_W['uniacid']} AND type='{$_GPC['do']}'";
    $info = pdo_fetch($info_sql);
    if ($info) {
        return $info;
    }

    $info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_share') . " WHERE uniacid={$_W['uniacid']} AND type='default'";
    $info = pdo_fetch($info_sql);
    return $info;
}

if (!function_exists('array_column')) {

    function array_column($input, $column_key, $index_key = null) {
        $arr = array_map(function($d) use ($column_key, $index_key) {
            if (!isset($d[$column_key])) {
                return null;
            }
            if ($index_key !== null) {
                return array($d[$index_key] => $d[$column_key]);
            }
            return $d[$column_key];
        }, $input);

        if ($index_key !== null) {
            $tmp = array();
            foreach ($arr as $ar) {
                $tmp[key($ar)] = current($ar);
            }
            $arr = $tmp;
        }
        return $arr;
    }

}