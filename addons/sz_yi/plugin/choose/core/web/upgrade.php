<?php
// 模块LTD提供
global $_W;

if (!defined('IN_IA')) {
	exit('Access Denied');
}

$result = pdo_fetchcolumn('select id from ' . tablename('sz_yi_plugin') . ' where identity=:identity', array(':identity' => 'choose'));

if (empty($result)) {
	$displayorder_max = pdo_fetchcolumn('select max(displayorder) from ' . tablename('sz_yi_plugin'));
	$displayorder = $displayorder_max + 1;
	$sql = 'INSERT INTO ' . tablename('sz_yi_plugin') . ' (`displayorder`,`identity`,`name`,`version`,`author`,`status`) VALUES(' . $displayorder . ',\'choose\',\'快速选购\',\'1.0\',\'官方\',\'1\');';
	pdo_query($sql);
}

$sql = "\nCREATE TABLE IF NOT EXISTS `ims_sz_yi_chooseagent` (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uid` int(11) DEFAULT NULL,\n  `agentname` varchar(255) DEFAULT NULL,\n  `isopen` int(11) DEFAULT NULL COMMENT '0为关闭,1为开启',\n  `createtime` varchar(255) DEFAULT NULL,\n  `savetime` varchar(255) DEFAULT NULL,\n  `uniacid` int(11) DEFAULT NULL,\n  `pcate` int(11) DEFAULT NULL,\n  `ccate` int(11) DEFAULT NULL,\n  `tcate` int(11) DEFAULT NULL,\n  `pagename` varchar(255) DEFAULT NULL,\n  `color` varchar(255) DEFAULT NULL,\n  `detail` int(11) DEFAULT  '0',\n  `allgoods` int(11) DEFAULT  '0',\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=utf8;\n";
pdo_query($sql);
message('微销快速选购插件安装成功', $this->createPluginWebUrl('choose/index'), 'success');

?>
