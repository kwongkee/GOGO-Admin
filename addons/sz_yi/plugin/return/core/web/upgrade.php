<?php
// 模块LTD提供
global $_W;
$result = pdo_fetchcolumn('select id from ' . tablename('sz_yi_plugin') . ' where identity=:identity', array(':identity' => 'return'));

if (empty($result)) {
	$displayorder_max = pdo_fetchcolumn('select max(displayorder) from ' . tablename('sz_yi_plugin'));
	$displayorder = $displayorder_max + 1;
	$sql = 'INSERT INTO ' . tablename('sz_yi_plugin') . ' (`displayorder`,`identity`,`name`,`version`,`author`,`status`,`category`) VALUES(' . $displayorder . ',\'return\',\'全返系统\',\'1.0\',\'官方\',\'1\',\'sale\');';
	pdo_fetchall($sql);
}

$sql = ' CREATE TABLE IF NOT EXISTS ' . tablename('sz_yi_return') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) NOT NULL,\n  `mid` int(11) NOT NULL,\n  `money` decimal(10,2) NOT NULL,\n  `return_money` decimal(10,2) NOT NULL,\n  `create_time` varchar(60) NOT NULL,\n  `status` tinyint(2) NOT NULL DEFAULT '0',\n  `returnrule` tinyint(1) NOT NULL,\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_return_money') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) NOT NULL,\n  `mid` int(11) NOT NULL,\n  `money` decimal(10,2) NOT NULL,\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_order_goods_queue') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) NOT NULL,\n  `openid` varchar(255) NOT NULL,\n  `goodsid` int(11) NOT NULL,\n  `orderid` int(11) NOT NULL,\n  `price` decimal(10,2) NOT NULL,\n  `queue` int(11) NOT NULL,\n  `returnid` int(11) DEFAULT NULL,\n  `status` tinyint(1) NOT NULL DEFAULT '0',\n  `create_time` INT( 11 ) NOT NULL,\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;\n\nALTER TABLE  " . tablename('sz_yi_goods') . " ADD  `isreturn` TINYINT( 1 ) NOT NULL ,\nADD  `isreturnqueue` TINYINT( 1 ) NOT NULL;";
pdo_query($sql);
message('全返插件安装成功', $this->createPluginWebUrl('return/set'), 'success');

?>
