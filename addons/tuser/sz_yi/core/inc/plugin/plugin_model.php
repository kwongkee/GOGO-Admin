<?php
// Ä£¿éLTDÌá¹©
class PluginModel
{
	private $pluginname;

	public function __construct($name = '')
	{
		$this->pluginname = $name;
	}

	public function getSet()
	{
		global $_W;
		global $_GPC;
		$set = m('common')->getSetData();
		$allset = iunserializer($set['plugins']);
		if (is_array($allset) && isset($allset[$this->pluginname])) {
			return $allset[$this->pluginname];
		}

		return array();
	}

	public function updateSet($data = array())
	{
		global $_W;
		$uniacid = $_W['uniacid'];
		$set = pdo_fetch('select * from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $uniacid));

		if (empty($set)) {
			pdo_insert('sz_yi_sysset', array('uniacid' => $uniacid, 'sets' => iserializer(array()), 'plugins' => iserializer(array($this->pluginname => $data))));
		}
		else {
			$sets = unserialize($set['plugins']);
			$sets[$this->pluginname] = $data;
			pdo_update('sz_yi_sysset', array('plugins' => iserializer($sets)), array('uniacid' => $uniacid));
		}

		$set = pdo_fetch('select * from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $uniacid));
		m('cache')->set('sysset', $set);
	}

	public function getName()
	{
		return pdo_fetchcolumn('select name from ' . tablename('sz_yi_plugin') . ' where identity=:identity limit 1', array(':identity' => $this->pluginname));
	}
}

if (!defined('IN_IA')) {
	exit('Access Denied');
}

?>
