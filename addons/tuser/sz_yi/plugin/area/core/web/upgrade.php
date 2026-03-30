<?php
// 模块LTD提供
global $_W;

if (!defined('IN_IA')) {
	exit('Access Denied');
}

$result = pdo_fetchcolumn('select id from ' . tablename('sz_yi_plugin') . ' where identity=:identity', array(':identity' => 'area'));

if (empty($result)) {
	$displayorder_max = pdo_fetchcolumn('select max(displayorder) from ' . tablename('sz_yi_plugin'));
	$displayorder = $displayorder_max + 1;
	$sql = 'INSERT INTO ' . tablename('sz_yi_plugin') . ' (`displayorder`,`identity`,`name`,`version`,`author`,`status`,`category`)VALUES(' . $displayorder . ',\'area\',\'商品区域\',\'1.0\',\'官方\',\'1\',\'sale\');';
	pdo_query($sql);
}

$sql = "\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_category_area') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0' COMMENT '所属帐号',\n  `name` varchar(50) DEFAULT NULL COMMENT '分类名称',\n  `thumb` varchar(255) DEFAULT NULL COMMENT '分类图片',\n  `parentid` int(11) DEFAULT '0' COMMENT '上级分类ID,0为第一级',\n  `isrecommand` int(10) DEFAULT '0',\n  `description` varchar(500) DEFAULT NULL COMMENT '分类介绍',\n  `displayorder` tinyint(3) unsigned DEFAULT '0' COMMENT '排序',\n  `enabled` tinyint(1) DEFAULT '1' COMMENT '是否开启',\n  `ishome` tinyint(3) DEFAULT '0',\n  `advimg` varchar(255) DEFAULT '',\n  `advurl` varchar(500) DEFAULT '', \n  `level` tinyint(3) DEFAULT '0',\n  `advimg_pc` varchar(255) DEFAULT NULL,\n  `advurl_pc` varchar(500) DEFAULT NULL,\n  `supplier_uid` int(11) DEFAULT '0',\n  `detail` text DEFAULT NULL,\n  `times` int(11) DEFAULT '0',\n  `create_time` varchar(255) DEFAULT NULL,\n  PRIMARY KEY (`id`),\n  KEY `idx_uniacid` (`uniacid`),\n  KEY `idx_displayorder` (`displayorder`),\n  KEY `idx_enabled` (`enabled`),\n  KEY `idx_parentid` (`parentid`),\n  KEY `idx_isrecommand` (`isrecommand`),\n  KEY `idx_ishome` (`ishome`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商品区域分类';\n\n";
pdo_query($sql);

if (!pdo_fieldexists('sz_yi_goods', 'pcate_area')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `pcate_area` int(11) NOT NULL DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'ccate_area')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `ccate_area` int(11) NOT NULL DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'tcate_area')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `tcate_area` int(11) NOT NULL DEFAULT \'0\';');
}

message('微销商品区域插件安装成功', $this->createPluginWebUrl('area'), 'success');

?>
