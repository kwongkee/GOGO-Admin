<?php

/**
 * 
 * 微信支付参数配置
 */
global $_W, $_GPC;

$settings = $this->module['config'];

if (checksubmit()) {
    $settings['pay'] = array(
        'appid' => trim($_GPC['appid']),
        'appscret' => trim($_GPC['appscret']),
        'mchid' => trim($_GPC['mchid']),
        'key' => trim($_GPC['key']),
    );

    $pars = array('module' => $this->modulename, 'uniacid' => $_W['uniacid']);
    $row = array();
    $row['settings'] = iserializer($settings);
    cache_build_account_modules($_W['uniacid']);
    if (pdo_fetchcolumn("SELECT module FROM " . tablename('uni_account_modules') . " WHERE module = :module AND uniacid = :uniacid", array(':module' => $this->modulename, ':uniacid' => $_W['uniacid']))) {
        $ret = pdo_update('uni_account_modules', $row, $pars) !== false;
    } else {
        $ret = pdo_insert('uni_account_modules', array('settings' => iserializer($settings), 'module' => $this->modulename, 'uniacid' => $_W['uniacid'], 'enabled' => 1)) !== false;
    }
    message('保存成功', $this->createWebUrl('wx_pay_setting'));
}
include $this->template('web/wx_pay_setting');
?>
