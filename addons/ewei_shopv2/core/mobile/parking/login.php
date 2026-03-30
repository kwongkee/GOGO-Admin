<?php

if (!(defined('IN_IA'))) {
	exit('Access Denied');
}
/**
 *
 */
class Login_EweiShopV2Page extends mobilePage
{
	
	 
    function main()
    {
		// include $this->template('parking/login');
        global $_W;
        echo '<pre>';
        print_r($_W['setting']);
        echo '</pre>';
    }
}
