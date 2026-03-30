<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require_once 'model.php';
class CouponWeb extends Plugin
{
	public function __construct()
	{
		parent::__construct('coupon');
	}

	public function index()
	{
		if (cv('coupon.coupon.view')) {
			header('location: ' . $this->createPluginWebUrl('coupon/coupon'));
			exit();
			return NULL;
		}

		if (cv('coupon.category.view')) {
			header('location: ' . $this->createPluginWebUrl('coupon/category'));
			exit();
			return NULL;
		}

		if (cv('coupon.center.view')) {
			header('location: ' . $this->createPluginWebUrl('coupon/center'));
			exit();
			return NULL;
		}

		if (cv('coupon.set.view')) {
			header('location: ' . $this->createPluginWebUrl('coupon/set'));
			exit();
		}
	}

	public function coupon()
	{
		$this->_exec_plugin('coupon');
	}

	public function center()
	{
		$this->_exec_plugin('center');
	}

	public function category()
	{
		$this->_exec_plugin('category');
	}

	public function send()
	{
		$this->_exec_plugin('send');
	}

	public function log()
	{
		$this->_exec_plugin('log');
	}

	public function set()
	{
		$this->_exec_plugin('set');
	}
}

?>
