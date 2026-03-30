<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class ChooseMobile extends Plugin
{
	public function __construct()
	{
		parent::__construct('choose');
	}

	public function index()
	{
		$this->_exec_plugin('index', false);
	}

	public function list_category()
	{
		$this->_exec_plugin('list_category', false);
	}

	public function list_goods()
	{
		$this->_exec_plugin('list_goods', false);
	}

	public function cart()
	{
		$this->_exec_plugin('cart', false);
	}
}

?>
