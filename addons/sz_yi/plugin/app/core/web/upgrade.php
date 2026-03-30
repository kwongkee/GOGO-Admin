<?php
// 模块LTD提供
global $_W;

if (!defined('IN_IA')) {
	exit('Access Denied');
}

$result = pdo_fetchcolumn('select id from ' . tablename('sz_yi_plugin') . ' where identity=:identity', array(':identity' => 'app'));

if (empty($result)) {
	$displayorder_max = pdo_fetchcolumn('select max(displayorder) from ' . tablename('sz_yi_plugin'));
	$displayorder = $displayorder_max + 1;
	$sql = 'INSERT INTO ' . tablename('sz_yi_plugin') . ' (`displayorder`,`identity`,`name`,`version`,`author`,`status`, `category`) VALUES(' . $displayorder . ',\'app\',\'APP客户端\',\'1.0\',\'官方\',\'1\', \'biz\');';
	pdo_query($sql);
}

$sql = "\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_banner') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `advname` varchar(50) DEFAULT '',\n  `link` varchar(255) DEFAULT '',\n  `thumb` varchar(255) DEFAULT '',\n  `displayorder` int(11) DEFAULT '0',\n  `enabled` int(11) DEFAULT '0',\n  `thumb_pc` varchar(500) DEFAULT '',\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;\n";
pdo_query($sql);
$sql = "\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_message') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',\n  `openid` varchar(255) NOT NULL COMMENT '用户openid',\n  `title` varchar(255) NOT NULL COMMENT '标题',\n  `contents` text NOT NULL COMMENT '内容',\n  `status` set('0','1') NOT NULL DEFAULT '0' COMMENT '0-未读；1-已读',\n  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '日期',\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;\n";
pdo_query($sql);
$sql = "\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_push') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `name` varchar(50) DEFAULT '',\n  `description` varchar(255) DEFAULT NULL,\n  `content` text,\n  `time` int(11) DEFAULT NULL,\n  `status` int(1) DEFAULT '0',\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;\n";
pdo_query($sql);
$sql = "\nALTER TABLE " . tablename('sz_yi_member') . " ADD `bindapp` tinyint(4) NOT NULL DEFAULT '0';\n";

if (!pdo_fieldexists('sz_yi_member', 'bindapp')) {
	pdo_query($sql);
}

message('微销APP客户端插件安装成功', $this->createPluginWebUrl('app/index'), 'success');

?>
