<?php

/**
 * 
 * 停车场收费规则
 */
global $_W, $_GPC;

$settings = $this->module['config'];

if (checksubmit()) {
    $settings['park_pay_type'] = (int) $_GPC['park_pay_type']; //1.按次收费 2.按时收费 3.微信现金
    $settings['park_pay_type_count_credit'] = (int) $_GPC['park_pay_type_count_credit']; //1.按次收费 消费积分

    $pars = array('module' => $this->modulename, 'uniacid' => $_W['uniacid']);
    $row = array();
    $row['settings'] = iserializer($settings);
    cache_build_account_modules($_W['uniacid']);
    if (pdo_fetchcolumn("SELECT module FROM " . tablename('uni_account_modules') . " WHERE module = :module AND uniacid = :uniacid", array(':module' => $this->modulename, ':uniacid' => $_W['uniacid']))) {
        $ret = pdo_update('uni_account_modules', $row, $pars) !== false;
    } else {
        $ret = pdo_insert('uni_account_modules', array('settings' => iserializer($settings), 'module' => $this->modulename, 'uniacid' => $_W['uniacid'], 'enabled' => 1)) !== false;
    }
    message('保存成功', $this->createWebUrl('park_pay_rule'));
}

load()->web('tpl');
include $this->template('web/park_pay_rule');
?>
