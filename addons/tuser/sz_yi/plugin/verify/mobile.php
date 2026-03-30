<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class VerifyMobile extends Plugin
{
	public function __construct()
	{
		parent::__construct('verify');
	}

	public function check()
	{
		$this->_exec_plugin('check', false);
	}

	public function complete()
	{
		$this->_exec_plugin('complete', false);
	}

	public function qrcode()
	{
		$this->_exec_plugin('qrcode', false);
	}

	public function detail()
	{
		$this->_exec_plugin('detail', false);
	}

	public function index()
	{
		$this->_exec_plugin('index', false);
	}

	public function mystore()
	{
		$this->_exec_plugin('mystore', false);
	}

	public function add()
	{
		$this->_exec_plugin('add', false);
	}

	public function order()
	{
		$this->_exec_plugin('order', false);
	}

	public function withdraw()
	{
		$this->_exec_plugin('withdraw', false);
	}

	public function log()
	{
		$this->_exec_plugin('log', false);
	}

	public function my_pocket()
	{
		$this->_exec_plugin('my_pocket', false);
	}

	public function ranking()
	{
		$this->_exec_plugin('ranking', false);
	}

	public function select_category()
	{
		$this->_exec_plugin('select_category', false);
	}

	public function select_goods()
	{
		$this->_exec_plugin('select_goods', false);
	}

	public function store_index()
	{
		$this->_exec_plugin('store_index', false);
	}

	public function store_list()
	{
		$this->_exec_plugin('store_list', false);
	}

	public function store_detail()
	{
		$this->_exec_plugin('store_detail', false);
	}
}

?>
