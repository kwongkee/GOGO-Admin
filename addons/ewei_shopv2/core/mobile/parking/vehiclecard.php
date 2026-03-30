<?php

if (!defined('IN_IA'))
{
    exit('Access Denied');
}
class Vehiclecard_EweiShopV2Page extends mobilePage{
    public function __construct () {
        parent::__construct();
        load()->func("common");
        isUserReg();
    }

    public function main(){
        global $_W;
        global $_GPC;
        $title = '关联车牌';
        $uname=pdo_get("parking_verified",array("openid"=>$_W['openid']),array('uname'));
        
        if(empty($uname)){
            header("Location:".mobileUrl('parking/verified'));
            exit();
        }

       include $this->template("parking/uploadvehicle");
    }
}