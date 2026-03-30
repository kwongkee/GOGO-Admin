<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class HotelWeb extends Plugin
{
	protected $set;

	public function __construct()
	{
		parent::__construct('hotel');
		$this->set = $this->getSet();
	}

	public function index()
	{
		global $_W;

		if (cv('hotel.room_status')) {
			header('location: ' . $this->createPluginWebUrl('hotel/room_status'));
			exit();
			return NULL;
		}

		if (cv('hotel.room_price')) {
			header('location: ' . $this->createPluginWebUrl('hotel/room_price'));
			exit();
		}
	}

	public function upgrade()
	{
		$this->_exec_plugin('upgrade');
	}

	public function room_status()
	{
		$this->_exec_plugin('room_status');
	}

	public function room_price()
	{
		$this->_exec_plugin('room_price');
	}

	public function meet()
	{
		$this->_exec_plugin('meet');
	}

	public function rest()
	{
		$this->_exec_plugin('rest');
	}

	public function prints()
	{
		$this->_exec_plugin('prints');
	}
}

?>
