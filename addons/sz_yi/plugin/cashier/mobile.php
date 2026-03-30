<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class CashierMobile extends Plugin
{
	public function __construct()
	{
		parent::__construct('cashier');
	}

	public function order_confirm()
	{
		$this->_exec_plugin('order_confirm', false);
	}

	public function order_pay()
	{
		$this->_exec_plugin('order_pay', false);
	}

	public function store_set()
	{
		$this->_exec_plugin('store_set', false);
	}

	public function withdraw()
	{
		$this->_exec_plugin('withdraw', false);
	}

	public function create_qrcode()
	{
		$this->_exec_plugin('create_qrcode', false);
	}

	public function qrcode_list()
	{
		$this->_exec_plugin('qrcode_list', false);
	}

	public function statistics()
	{
		$this->_exec_plugin('statistics', false);
	}
}

?>
