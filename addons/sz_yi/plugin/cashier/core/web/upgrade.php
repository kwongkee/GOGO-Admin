<?php
// 模块LTD提供
global $_W;

if (!defined('IN_IA')) {
	exit('Access Denied');
}

$result = pdo_fetchcolumn('select id from ' . tablename('sz_yi_plugin') . ' where identity=:identity', array(':identity' => 'cashier'));

if (empty($result)) {
	$displayorder_max = pdo_fetchcolumn('select max(displayorder) from ' . tablename('sz_yi_plugin'));
	$displayorder = $displayorder_max + 1;
	$sql = 'INSERT INTO ' . tablename('sz_yi_plugin') . ' (`displayorder`,`identity`,`name`,`version`,`author`,`status`) VALUES(' . $displayorder . ',\'cashier\',\'收银台\',\'1.0\',\'官方\',\'1\');';
	pdo_query($sql);
}

$sql = "\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_cashier_order') . " (\n  `order_id` int(11) NOT NULL,\n  `uniacid` int(11) NOT NULL,\n  `cashier_store_id` int(11) NOT NULL,\n  PRIMARY KEY (`order_id`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='收银台商户订单';\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_cashier_store') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) NOT NULL DEFAULT '0',\n  `name` varchar(100) DEFAULT NULL COMMENT '店名',\n  `thumb` varchar(255) NOT NULL,\n  `contact` varchar(100) DEFAULT NULL COMMENT '联系人',\n  `mobile` varchar(30) DEFAULT NULL COMMENT '电话',\n  `address` varchar(500) DEFAULT NULL COMMENT '地址',\n  `member_id` int(11) DEFAULT '0' COMMENT '绑定的会员微信号',\n  `deduct_credit1` decimal(10,2) DEFAULT '0.00' COMMENT '抵扣设置,允许使用的积分百分比',\n  `deduct_credit2` decimal(10,2) DEFAULT '0.00' COMMENT '抵扣设置,允许使用的余额百分比',\n  `settle_platform` decimal(10,2) DEFAULT '0.00' COMMENT '结算比例,平台比例',\n  `settle_store` decimal(10,2) DEFAULT '0.00' COMMENT '结算比例,商家比例',\n  `commission1_rate` decimal(10,2) DEFAULT '0.00' COMMENT '佣金比例,一级分销,消费者在商家用收银台支付后，分销商获得的佣金比例',\n  `commission2_rate` decimal(10,2) DEFAULT '0.00' COMMENT '佣金比例,二级分销',\n  `commission3_rate` decimal(10,2) DEFAULT '0.00' COMMENT '佣金比例,三级分销',\n  `credit1` decimal(10,2) DEFAULT '0.00' COMMENT '消费者在商家支付完成后，获得的积分奖励百分比',\n  `redpack_min` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '最小消费金额，才会发红包',\n  `redpack` decimal(10,2) DEFAULT '0.00' COMMENT '消费者在商家支付完成后，获得的红包奖励百分比',\n  `coupon_id` int(11) NOT NULL DEFAULT '0' COMMENT '优惠卷',\n  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n  `deredpack` tinyint(1) NOT NULL DEFAULT '0' COMMENT '扣除红包金额',\n  `decommission` tinyint(1) NOT NULL DEFAULT '0' COMMENT '扣除佣金金额',\n  `decredits` tinyint(1) NOT NULL DEFAULT '0' COMMENT '扣除奖励余额金额',\n  `creditpack` decimal(10,2) DEFAULT '0.00' COMMENT '消费者在商家支付完成后，获得的余额奖励百分比',\n  `condition` decimal(10,2) DEFAULT '0.00' COMMENT '使用优惠券条件',\n  `iscontact` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否填写联系人信息',\n  `isreturn` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否加入全返',\n  `centercan` tinyint(1) NOT NULL DEFAULT '1' COMMENT '会员中心是够可以编辑',\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_cashier_withdraw') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) NOT NULL,\n  `withdraw_no` varchar(255) NOT NULL,\n  `openid` varchar(50) DEFAULT NULL,\n  `cashier_store_id` int(11) NOT NULL,\n  `money` decimal(10,2) NOT NULL,\n  `status` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '提现状态 0 生成 1 成功 2 失败',\n  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='收银台商户提现表';\n";
pdo_query($sql);

if (!pdo_fieldexists('sz_yi_order', 'cashier')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `cashier` tinyint(1) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order', 'realprice')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `realprice` decimal(10) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order', 'deredpack')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `deredpack` tinyint(1) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order', 'decommission')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `decommission` tinyint(1) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order', 'decredits')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `decredits` tinyint(1) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order', 'cashierid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `cashierid` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_cashier_store', 'bonus')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_cashier_store') . ' ADD `bonus` decimal(10,2) DEFAULT NULL;');
}

pdo_fetchall('CREATE TABLE IF NOT EXISTS ' . tablename('sz_yi_cashier_store_waiter') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `sid` int(11) DEFAULT NULL,\n  `realname` varchar(255) DEFAULT NULL,\n  `mobile` varchar(255) DEFAULT NULL,\n  `member_id` int(11) DEFAULT NULL,\n  `uniacid` int(11) DEFAULT NULL,\n  `createtime` varchar(255) DEFAULT NULL,\n  `savetime` varchar(255) DEFAULT NULL,\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
message('微销收银台插件安装成功', $this->createPluginWebUrl('cashier/index'), 'success');

?>
