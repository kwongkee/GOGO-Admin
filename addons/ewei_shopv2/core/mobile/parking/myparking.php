<?php


if (!defined('IN_IA'))
{
    exit('Access Denied');
}


class Myparking_EweiShopV2Page extends mobilePage
{
    public  function main()
    {
        global $_W;
        global $_GPC;
        $wx=$_W['account']['jssdkconfig'];
        $wxurl=($_W['account']['level']==4)?'http://'.$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']:$row['share_url'];
        include $this->template("parking/myparking");
    }
}