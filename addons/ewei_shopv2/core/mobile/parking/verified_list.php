<?php

if (!defined('IN_IA'))
{
    exit('Access Denied');
}

class Verified_list_EweiShopV2Page extends mobilePage
{
    public function main()
    {
        global $_W;
        global $_GPC;
        $title      = '认证列表';
        $userInfo   = pdo_get("parking_authorize",['openid' => $_W['openid']]);
        $verInfo    = pdo_get("parking_verified",['openid' => $_W['openid']]);
       include $this->template("parking/verified_list");
    }
}