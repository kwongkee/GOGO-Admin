<?php
// 模块LTD提供
global $_W;

if (!defined('IN_IA')) {
	exit('Access Denied');
}

ca('channel.upgrade');
$result = pdo_fetchcolumn('select id from ' . tablename('sz_yi_plugin') . ' where identity=:identity', array(':identity' => 'channel'));

if (empty($result)) {
	$displayorder_max = pdo_fetchcolumn('select max(displayorder) from ' . tablename('sz_yi_plugin'));
	$displayorder = $displayorder_max + 1;
	$sql = 'INSERT INTO ' . tablename('sz_yi_plugin') . ' (`displayorder`,`identity`,`name`,`version`,`author`,`status`,`category`) VALUES(' . $displayorder . ',\'channel\',\'渠道商\',\'1.0\',\'官方\',\'1\',\'biz\');';
	pdo_fetchall($sql);
}

$sql = "\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_channel_merchant') . " (\n  `id` INT(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` INT(11) NOT NULL,\n  `openid` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NULL COMMENT '我的openid',\n  `lower_openid` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NULL COMMENT '下级openid',\n  `commission` DECIMAL(10,2) NULL COMMENT '推荐员分红比例',\n  PRIMARY KEY (`id`))\nENGINE = MyISAM\nDEFAULT CHARACTER SET = utf8\nCOLLATE = utf8_general_ci\nCOMMENT = '渠道商推荐员';\n\n\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_channel_level') . " (\n  `id` INT(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` INT(11) NOT NULL,\n  `level_name` VARCHAR(45) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' NULL COMMENT '等级名称',\n  `level_num` INT(1) NULL COMMENT '等级权重',\n  `purchase_discount` VARCHAR(45) NULL COMMENT '进货折扣 %',\n  `min_price` DECIMAL(10,2) NULL COMMENT '最小进货量',\n  `profit_sharing` VARCHAR(45) NULL COMMENT '利润分成\n%',\n  `become` INT(11) NULL COMMENT '升级条件',\n  `team_count` INT(11) NULL COMMENT '团队人数',\n  `goods_id` INT(11) NULL COMMENT '指定商品id',\n  `order_money` INT(11) NULL COMMENT '订单累计金额',\n  `order_count` INT(11) NULL COMMENT '订单累计次数',\n  `createtime` INT(11) NULL COMMENT '创建时间',\n  `updatetime` INT(11) NULL COMMENT '更新时间',\n  PRIMARY KEY (`id`))\nENGINE = MyISAM\nDEFAULT CHARACTER SET = utf8\nCOLLATE = utf8_general_ci\nCOMMENT = '渠道商等级';\n\n\n\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_af_channel') . " (\n  `id` INT(11) NOT NULL AUTO_INCREMENT,\n  `mid` INT(11) NOT NULL,\n  `uniacid` INT(11) NOT NULL,\n  `openid` VARCHAR(50) NULL,\n  `realname` VARCHAR(45) NOT NULL COMMENT '真实姓名',\n  `mobile` VARCHAR(11) NULL COMMENT '电话号',\n  `diychannelid` INT(11) NULL,\n  `diychanneldataid` INT(11) NULL,\n  `diychannelfields` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,\n  `diychanneldata` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,\n  PRIMARY KEY (`id`))\nENGINE = MyISAM\nCOMMENT = '会员申请渠道商';\n\n\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_channel_apply') . " (\n  `id` INT(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` INT(11) NOT NULL,\n  `mid` INT(11) NOT NULL,\n  `openid` VARCHAR(50) NULL,\n  `applyno` VARCHAR(255) NULL,\n  `apply_money` DECIMAL(10,2) NULL COMMENT '申请金额',\n  `apply_time` INT(11) NULL COMMENT '申请时间',\n  `type` TINYINT(2) NULL COMMENT '提现类型',\n  `status` TINYINT(2) NULL COMMENT '申请状态',\n  `finish_time` INT(11) NULL COMMENT '完成时间',\n  PRIMARY KEY (`id`))\nENGINE = MyISAM\nCOMMENT = '渠道商申请提现';\n\n\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_channel_stock') . " (\n  `id` INT(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` INT(11) NOT NULL,\n  `openid` VARCHAR(50) NULL,\n  `goodsid` INT(11) NOT NULL COMMENT '商品ID',\n  `stock_total` INT(11) NOT NULL COMMENT '库存总数',\n  PRIMARY KEY (`id`))\nENGINE = MyISAM\nCOMMENT = '渠道商库存';\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_channel_order_goods_profit') . " (\n  `id` INT(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` INT(11) NOT NULL,\n  `order_goods_id` INT(11) NOT NULL COMMENT '商品ID',\n  `goods_price` DECIMAL(10,2) NULL COMMENT '商品总额',\n  `discount` DECIMAL(10,2) NULL COMMENT '折扣',\n  `profit_ratio` DECIMAL(10,2) NULL COMMENT '利润比例',\n  `profit` DECIMAL(10,2) NULL COMMENT '利润',\n  PRIMARY KEY (`id`))\nENGINE = MyISAM\nCOMMENT = '渠道商商品利润';\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_channel_stock_log') . " (\n  `id` INT(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` INT(11) NOT NULL,\n  `openid` VARCHAR(50) NULL,\n  `goodsid` INT(11) NULL COMMENT '商品ID',\n  `every_turn` INT(11) NULL COMMENT '每次进货量',\n  `every_turn_price` DECIMAL(10,2) NULL COMMENT '每次进货单价',\n  `every_turn_discount` DECIMAL(10,2) NULL COMMENT '每次进货当前折扣',\n  `goods_price` DECIMAL(10,2) NULL COMMENT '进货时商品单价',\n  PRIMARY KEY (`id`))\nENGINE = MyISAM\nCOMMENT = '渠道商进货记录'";
pdo_fetchall($sql);

if (!pdo_fieldexists('sz_yi_member', 'ischannel')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD `ischannel` INT(1) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_member', 'channel_level')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD `channel_level` INT(1) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_member', 'channeltime')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD `channeltime` INT(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order', 'ischannelself')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `ischannelself` INT(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order_goods', 'channel_id')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order_goods') . ' ADD `channel_id` INT(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order_goods', 'channel_apply_status')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order_goods') . ' ADD `channel_apply_status` tinyint(1) NOT NULL COMMENT \'0未提现1申请中2已提现\';');
}

if (!pdo_fieldexists('sz_yi_af_channel', 'status')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_af_channel') . ' ADD `status` tinyint(1) NOT NULL COMMENT \'0为申请1为通过\';');
}

if (!pdo_fieldexists('sz_yi_chooseagent', 'isopenchannel')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_chooseagent') . ' ADD `isopenchannel` tinyint(1) NOT NULL COMMENT \'0关闭1开启\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'isopenchannel')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `isopenchannel` tinyint(1) NOT NULL COMMENT \'0关闭1开启\';');
}

if (!pdo_fieldexists('sz_yi_order_goods', 'ischannelpay')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order_goods') . ' ADD `ischannelpay` tinyint(1) NOT NULL COMMENT \'0不是1渠道商采购订单\';');
}

if (!pdo_fieldexists('sz_yi_channel_apply', 'apply_ordergoods_ids')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_channel_apply') . ' ADD  `apply_ordergoods_ids` text;');
}

if (!pdo_fieldexists('sz_yi_channel_apply', 'apply_cmaorders_ids')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_channel_apply') . ' ADD  `apply_cmaorders_ids` text;');
}

if (!pdo_fieldexists('sz_yi_channel_apply', 'apply_selforders_ids')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_channel_apply') . ' ADD  `apply_selforders_ids` text;');
}

if (!pdo_fieldexists('sz_yi_channel_stock_log', 'paytime')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_channel_stock_log') . ' ADD  `paytime` INT(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_channel_stock_log', 'optionid')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_channel_stock_log') . ' ADD `optionid` INT(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_channel_stock', 'optionid')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_channel_stock') . ' ADD `optionid` INT(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order', 'iscmas')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `iscmas` INT(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_channel_stock_log', 'type')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_channel_stock_log') . ' ADD `type` INT(11) DEFAULT \'0\' COMMENT \'1.采购2.下级采购3.零售4.自提\';');
}

if (!pdo_fieldexists('sz_yi_channel_stock_log', 'order_goodsid')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_channel_stock_log') . ' ADD `order_goodsid` INT(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_channel_stock_log', 'surplus_stock')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_channel_stock_log') . ' ADD `surplus_stock` INT(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_channel_stock_log', 'mid')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_channel_stock_log') . ' ADD `mid` INT(11) DEFAULT \'0\';');
}

message('渠道商插件安装成功', $this->createPluginWebUrl('channel/index'), 'success');

?>
