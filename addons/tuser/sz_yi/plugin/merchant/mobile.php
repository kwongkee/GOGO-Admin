<?php
// Ä£¿éLTDÌá¹©
function sortByCreateTime($a, $b)
{
	if ($a['createtime'] == $b['createtime']) {
		return 0;
	}

	return $a['createtime'] < $b['createtime'] ? 1 : -1;
}

if (!defined('IN_IA')) {
	exit('Access Denied');
}

class MerchantMobile extends Plugin
{
	protected $set;

	public function __construct()
	{
		parent::__construct('merchant');
		$this->set = $this->getSet();
		global $_GPC;
	}

	public function logg()
	{
		$this->_exec_plugin('logg', false);
	}

	public function applyg()
	{
		$this->_exec_plugin('applyg', false);
	}

	public function orderj()
	{
		$this->_exec_plugin('orderj', false);
	}

	public function team()
	{
		$this->_exec_plugin('team', false);
	}

	public function teamc()
	{
		$this->_exec_plugin('teamc', false);
	}

	public function index()
	{
		$this->_exec_plugin('index', false);
	}
}

?>
