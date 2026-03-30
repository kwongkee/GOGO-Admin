<?php
if (!defined('IN_IA'))
{
    exit('Access Denied');
}
class MobileHeadPage extends MobilePage
{
    public function __construct()
    {
        global $_W;
        global $_GPC;
        parent::__construct();
    }
}
?>