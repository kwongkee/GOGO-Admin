<?php

if (!defined('IN_IA')) {

	exit('Access Denied');
}





require_once IA_ROOT . '/addons/ewei_shopv2/version.php';
require_once IA_ROOT . '/addons/ewei_shopv2/defines.php';
require_once EWEI_SHOPV2_INC . 'functions.php';

class Ewei_shopv2ModuleSite extends WeModuleSite
{
    // 检测用户是否有操作权限 2019-10-21
    public function __construct()
    {
        global $_GPC;
        // 解析登陆用户信息
        $session = json_decode(base64_decode($_GPC['__session']),true);
        // 判断用户权限
        if($session['sysTypes'] == 'system') {
            // 是原平台用户就直接返回
            return true;
        }
        // 获取权限
        $roleid = trim($session['roleid']);
        $role = pdo_get('merchat_role',['id'=>$roleid],['rolePermiss']);
        // 判断用户权限
        if(!empty($role) && ($role['rolePermiss'] == 'n')){
            //message('您无权限操作，请联系管理员！',referer(), 'info');
            message('您无权限操作，请联系管理员！');
        }
        // 通过
        return true;
    }

	public function getMenus()
	{
		global $_W;
		return array(
	array('title' => '管理后台', 'icon' => 'fa fa-shopping-cart', 'url' => webUrl())
	);
	}

	public function doWebWeb()
	{
		m('route')->run();
		
	}

	public function doMobileMobile()
	{
		m('route')->run(false);
	}

	public function payResult($params)
	{
		return m('order')->payResult($params);
	}
}


?>