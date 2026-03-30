<?php
// 模块LTD提供
global $_W;
$sql = "\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_merchants') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `openid` varchar(255) CHARACTER SET utf8 NOT NULL,\n  `uniacid` int(11) NOT NULL,\n  `supplier_uid` int(11) NOT NULL,\n  `member_id` int(11) NOT NULL,\n  `commissions` decimal(10,2) DEFAULT '0.00',\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_merchant_apply') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) NOT NULL,\n  `applysn` varchar(255) NOT NULL COMMENT '提现单号',\n  `member_id` int(11) NOT NULL,\n  `type` tinyint(3) DEFAULT '0' ,\n  `money` decimal(10,2) DEFAULT '0.00',\n  `status` tinyint(3) DEFAULT '0' ,\n  `apply_time` int(11) NOT NULL,\n  `finish_time` int(11) NOT NULL,\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_merchant_level') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) NOT NULL,\n  `level_name` varchar(100) CHARACTER SET utf8 NOT NULL COMMENT '等级名称',\n  `commission` decimal(10,2) DEFAULT '0.00' COMMENT '比例',\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_merchant_center') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) NOT NULL,\n  `openid` varchar(255) CHARACTER SET utf8 NOT NULL,\n  `realname` varchar(50) CHARACTER SET utf8 NOT NULL,\n  `mobile` varchar(50) CHARACTER SET utf8 NOT NULL,\n  `level_id` int(11) NOT NULL,\n  `center_id` int(11) NOT NULL,\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_merchant_order') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) NOT NULL,\n  `orderid` int(11) NOT NULL,\n  `money` decimal(10,2) DEFAULT '0.00' COMMENT '金额',\n  `isopenbonus` int(11) NOT NULL,\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
pdo_query($sql);
$info = pdo_fetch('select * from ' . tablename('sz_yi_plugin') . ' where identity= "merchant"  order by id desc limit 1');

if (!pdo_fieldexists('sz_yi_order', 'merchant_apply_status')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD     `merchant_apply_status` tinyint(3) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_merchants', 'center_id')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_merchants') . ' ADD `center_id` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_merchant_apply', 'iscenter')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_merchant_apply') . ' ADD `iscenter` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order', 'center_apply_status')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `center_apply_status` tinyint(3) DEFAULT \'0\';');
}

if (!$info) {
	$sql = 'INSERT INTO ' . tablename('sz_yi_plugin') . ' (`displayorder`, `identity`, `name`, `version`, `author`, `status`, `category`) VALUES(0, \'merchant\', \'招商员\', \'1.0\', \'官方\', 1, \'biz\');';
	pdo_query($sql);
}

message('微销招商员插件安装成功', $this->createPluginWebUrl('merchant/merchants'), 'success');

?>
