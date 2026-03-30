<?php

global $_W, $_GPC;
$user = $this->auth();

//判断是否关注

if ($user) {
    $pars[':uniacid'] = $_W['uniacid'];
    $pars[':openid'] = $user['openid'];
    $sql = 'SELECT * FROM ' . tablename('mc_mapping_fans') . ' WHERE `uniacid`=:uniacid AND `openid`=:openid';
    $wx_fans = pdo_fetch($sql, $pars);
}

//判断是否关注订阅号
if (!$wx_fans || $wx_fans['follow'] != 1) {
    //没有关注
    if ($this->module['config']['force_follow'] == 1) {
        message('请先关注.', $this->module['config']['force_follow_url']);
    }
}

//获取图片域名
if ($this->module['config']['attach_url']) {
    $attach_url = $this->module['config']['attach_url'];
} else {
    $attach_url = $_W['siteroot'] . $_W['config']['upload']['attachdir'];
}

//分享数据
require_once WXZ_SHOPPINGMALL . '/func/common.func.php';
require_once WXZ_SHOPPINGMALL . '/func/common_two.func.php';
$shareData = getShareData();
?>
