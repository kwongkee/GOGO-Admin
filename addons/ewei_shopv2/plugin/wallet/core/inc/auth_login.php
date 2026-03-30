<?php

if (!(defined('IN_IA'))) {
    exit('Access Denied');
}
/**
 *验证登录
 */
class LoginAuthMobilePage extends PluginMobilePage {
    public function __construct()
    {
       /*
        global $_W;
        global $_GPC;
        $Usertoken = $_COOKIE['UserToken'];
        if ($this->verifiedToken($Usertoken,$_W['openid'])) {
          //没登录
        }
       */
    }

    protected function verifiedToken($token,$openid)
    {
//        $UserRes=pdo_get("");
    }
}