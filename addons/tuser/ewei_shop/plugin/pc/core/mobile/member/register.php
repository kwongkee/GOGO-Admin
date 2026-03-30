<?php
if (!(defined("IN_IA"))) 
{
	exit("Access Denied");
}
require EWEI_SHOPV2_PLUGIN . "pc/core/page_login_mobile.php";
class Register_EweiShopV2Page extends PcMobileLoginPage 
{
	public function main() 
	{
		global $_W;
		global $_GPC;
		$_GPC['type'] = intval($_GPC['type']);
		$nav_link_list = array( array('link' => mobileUrl('pc'), 'title' => '首页'), array('link' => mobileUrl('pc.member'), 'title' => '我的商城'), array('title' => '招商入驻') );
		$ice_menu_array = array( array('menu_key' => 'index', 'menu_name' => '招商入驻', 'menu_url' => mobileUrl('pc.member.register')) );
		$all_list = $this->get_list();
		$list = $all_list['list'];
		$pindex = max(1, intval($_GPC['page']));
		$pager = fenye($all_list['total'], $pindex, $all_list['psize']);
		
		include $this->template();
	}
	public function get_post() 
	{
		global $_W;
		global $_GPC;
		$type = intval($_GPC['type']);
		echo '<pre>';
		var_dump($_GPC);die();
	}
}
?>