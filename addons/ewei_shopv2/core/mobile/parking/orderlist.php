<?php

/**
 *
 */
 if (!(defined('IN_IA'))) {
 	exit('Access Denied');
 }
class Orderlist_EweiShopV2Page extends mobilePage
{

    function main()
    {
        include $this->template('parking/order_list');
    }
}
