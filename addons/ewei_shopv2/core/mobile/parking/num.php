<?php
if (!defined('IN_IA'))
{
    exit('Access Denied');
}


class Num_EweiShopV2Page extends mobilePage{
   public function main()
    {
        global $_W;
        global $_GPC;
        $title="车位输入";
        include $this->template("parking/num");
    }
}