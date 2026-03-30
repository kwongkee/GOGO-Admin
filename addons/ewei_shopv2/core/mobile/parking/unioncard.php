<?php



if (!(defined('IN_IA'))) {
	exit('Access Denied');
}

class Unioncard_EweiShopV2Page extends mobilePage
{

    function main()
    {
        $title='银联卡签约';
        include $this->template('parking/unionauth');
    }
}
