<?php
/**
 * 优惠卷模块
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

class Go_couponModuleSite extends WeModuleSite {


    /**
     * 内容管理
     */
    public function doMobileIndex() {
        global $_GPC;
        // 验证用户注册, 注册后方能进如活动
        checkauth();
        require 'inc/mobile/'.$_GPC['do'].'/'.$_GPC['p'].'.inc.php';
    }
}