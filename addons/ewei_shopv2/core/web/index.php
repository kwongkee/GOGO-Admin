<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}

class Index_EweiShopV2Page extends WebPage 
{
	public function main() 
	{
		echo '<script>window.location.href="index.php?c=site&a=entry&m=ewei_shopv2&do=web&r=shop"</script>';
		exit();
	}
}
?>