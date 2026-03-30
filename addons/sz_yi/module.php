<?php
// Ä£¿éLTDÌá¹©
defined('IN_IA') || exit('Access Denied');
class Sz_yiModule extends WeModule
{
	public function settingsDisplay($settings)
	{
		global $_W;
		global $_GPC;

		if (checksubmit()) {
			$this->saveSettings($dat);
		}

		include $this->template('setting');
	}
}

?>
