<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class MerchantWeb extends Plugin
{
	protected $set;

	public function __construct()
	{
		parent::__construct('merchant');
		$this->set = $this->getSet();
	}

	public function index()
	{
		global $_W;

		if (cv('merchant.center')) {
			header('location: ' . $this->createPluginWebUrl('merchant/center'));
			exit();
		}
	}

	public function upgrade()
	{
		$this->_exec_plugin('upgrade');
	}

	public function merchants()
	{
		$this->_exec_plugin('merchants');
	}

	public function merchant_order()
	{
		$this->_exec_plugin('merchant_order');
	}

	public function set()
	{
		$this->_exec_plugin('set');
	}

	public function merchant_apply()
	{
		$this->_exec_plugin('merchant_apply');
	}

	public function merchant_apply_finish()
	{
		$this->_exec_plugin('merchant_apply_finish');
	}

	public function center()
	{
		$this->_exec_plugin('center');
	}

	public function level()
	{
		$this->_exec_plugin('level');
	}
}

?>
