<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}
require EWEI_SHOPV2_PLUGIN . 'wallet/core/inc/auth_login.php';

class Index_EweiShopV2Page extends LoginAuthMobilePage {
    public function main()
    {
        include $this->template();
    }
}