<?php
// 模块LTD提供
global $_W;

if (!defined('IN_IA')) {
	exit('Access Denied');
}

ca('bonus.upgrade');
$result = pdo_fetchcolumn('select id from ' . tablename('sz_yi_plugin') . ' where identity=:identity', array(':identity' => 'bonus'));

if (empty($result)) {
	$displayorder_max = pdo_fetchcolumn('select max(displayorder) from ' . tablename('sz_yi_plugin'));
	$displayorder = $displayorder_max + 1;
	$sql = 'INSERT INTO ' . tablename('sz_yi_plugin') . ' (`displayorder`,`identity`,`name`,`version`,`author`,`status`,`category`) VALUES(' . $displayorder . ',\'bonus\',\'微销分红\',\'1.0\',\'官方\',\'1\',\'biz\');';
	pdo_fetchall($sql);
}

$sql = 'CREATE TABLE IF NOT EXISTS ' . tablename('sz_yi_bonus_goods') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `ordergoodid` int(11) DEFAULT '0',\n  `orderid` int(11) DEFAULT '0',\n  `total` int(11) DEFAULT '0',\n  `optionname` varchar(100) DEFAULT '',\n  `mid` int(11) DEFAULT '0' COMMENT '所有人，分佣者',\n  `levelid` int(11) DEFAULT '0' COMMENT '级别id',\n  `level` int(11) DEFAULT '0' COMMENT '1/2/3哪一级',\n  `money` decimal(10,2) DEFAULT '0.00' COMMENT '应得佣金',\n  `status` tinyint(3) DEFAULT '0' COMMENT '申请状态，-2删除，-1无效，0未申请，1申请，2审核通过 3已打款',\n  `content` text,\n  `applytime` int(11) DEFAULT '0',\n  `checktime` int(11) DEFAULT '0',\n  `paytime` int(11) DEFAULT '0',\n  `invalidtime` int(11) DEFAULT '0',\n  `deletetime` int(11) DEFAULT '0',\n  `createtime` int(11) DEFAULT '0',\n  PRIMARY KEY (`id`),\n  KEY `idx_uniacid` (`uniacid`)\n) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='分红单商品表' AUTO_INCREMENT=1 ;\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_bonus_level') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `levelname` varchar(50) DEFAULT '',\n  `agent_money` decimal(10,2) DEFAULT '0.00',\n  `pcommission` decimal(10,2) DEFAULT '0.00',\n  `commissionmoney` decimal(10,2) DEFAULT '0.00',\n  `ordermoney` decimal(10,2) DEFAULT '0.00',\n  `downcount` int(10) DEFAULT '0',\n  `ordercount` int(10) DEFAULT '0',\n  `downcountlevel1` int(10) DEFAULT '0',\n  `type` int(11) DEFAULT '0' COMMENT '1为区域代理',\n  `level` int(10) DEFAULT '0' COMMENT '等级权重',\n  `premier` tinyint(1) DEFAULT '0' COMMENT '0 普通级别 1 最高级别',\n  `content` text DEFAULT '' COMMENT '微信消息提醒追加内容',\n  PRIMARY KEY (`id`),\n  KEY `idx_uniacid` (`uniacid`)\n) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='分红代理等级表' AUTO_INCREMENT=1 ;\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_bonus') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `send_bonus_sn` int(11) DEFAULT '0',\n  `money` decimal(10,2) DEFAULT '0.00',\n  `total` int(11) DEFAULT '0',\n  `status` tinyint(1) DEFAULT '0',\n  `type` tinyint(1) DEFAULT '0' COMMENT '0 手动 1 自动',\n  `paymethod` tinyint(1) DEFAULT '0',\n  `sendmonth` tinyint(1) DEFAULT '0',\n  `isglobal` tinyint(1) DEFAULT '0',\n  `sendpay_error` tinyint(1) DEFAULT '0',\n  `utime` int(11) DEFAULT '0',\n  `ctime` int(11) DEFAULT '0',\n  PRIMARY KEY (`id`),\n  KEY `idx_uniacid` (`uniacid`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='分红明细';\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_bonus_log') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `openid` varchar(255) DEFAULT '',\n  `uid` int(11) DEFAULT '0',\n  `money` decimal(10,2) DEFAULT '0.00',\n  `logno` varchar(255) DEFAULT '',\n  `send_bonus_sn` int(11) DEFAULT '0',\n  `paymethod` tinyint(1) DEFAULT '0',\n  `type` tinyint(1) DEFAULT '0',\n  `isglobal` tinyint(1) DEFAULT '0',\n  `status` tinyint(1) DEFAULT '0',\n  `sendpay` tinyint(1) DEFAULT '0',\n  `ctime` int(11) DEFAULT '0',\n  PRIMARY KEY (`id`),\n  KEY `idx_uniacid` (`uniacid`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='分红日志';\n";
pdo_fetchall($sql);

if (!pdo_fieldexists('sz_yi_member', 'bonuslevel')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD `bonuslevel` INT DEFAULT \'0\' AFTER `agentlevel`, ADD `bonus_status` TINYINT(1) DEFAULT \'0\' AFTER `bonuslevel`;');
}

if (!pdo_fieldexists('sz_yi_bonus_log', 'money')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_bonus_log') . ' ADD `money` DEFAULT(10,2) DEFAULT \'0.00\';');
}

if (!pdo_fieldexists('sz_yi_member', 'bonus_area')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD `bonus_area` TINYINT(1) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_member', 'bonus_province')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD `bonus_province` varchar(50) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_member', 'bonus_city')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD `bonus_city` varchar(50) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_member', 'bonus_district')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD `bonus_district` varchar(50) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_member', 'bonus_area_commission')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD `bonus_area_commission` DECIMAL(10,2) DEFAULT \'0.00\'');
}

if (!pdo_fieldexists('sz_yi_goods', 'bonusmoney')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `bonusmoney` DECIMAL(10,2) DEFAULT \'0.00\' AFTER `costprice`;');
}

if (!pdo_fieldexists('sz_yi_bonus_goods', 'bonus_area')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_bonus_goods') . ' ADD `bonus_area` TINYINT(1) DEFAULT \'0\' AFTER `levelid`;');
}

if (!pdo_fieldexists('sz_yi_bonus_level', 'msgtitle')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_bonus_level') . ' ADD `msgtitle` varchar(100) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_bonus_level', 'msgcontent')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_bonus_level') . ' ADD `msgcontent` varchar(255) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_bonus_level', 'downcountlevel2')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_bonus_level') . ' ADD `downcountlevel2` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_bonus_level', 'downcountlevel3')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_bonus_level') . ' ADD `downcountlevel3` int(11) DEFAULT \'0\';');
}

pdo_fetchall('CREATE TABLE IF NOT EXISTS ' . tablename('sz_yi_bonus_apply') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `applyno` varchar(255) DEFAULT '',\n  `mid` int(11) DEFAULT '0' COMMENT '会员ID',\n  `type` tinyint(3) DEFAULT '0' COMMENT '0 余额 1 微信',\n  `orderids` text,\n  `commission` decimal(10,2) DEFAULT '0.00',\n  `commission_pay` decimal(10,2) DEFAULT '0.00',\n  `content` text,\n  `status` tinyint(3) DEFAULT '0' COMMENT '-1 无效 0 未知 1 正在申请 2 审核通过 3 已经打款',\n  `applytime` int(11) DEFAULT '0',\n  `checktime` int(11) DEFAULT '0',\n  `paytime` int(11) DEFAULT '0',\n  `invalidtime` int(11) DEFAULT '0',\n  PRIMARY KEY (`id`),\n  KEY `idx_uniacid` (`uniacid`),\n  KEY `idx_mid` (`mid`),\n  KEY `idx_checktime` (`checktime`),\n  KEY `idx_paytime` (`paytime`),\n  KEY `idx_applytime` (`applytime`),\n  KEY `idx_status` (`status`),\n  KEY `idx_invalidtime` (`invalidtime`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
message('微销分红插件安装成功', $this->createPluginWebUrl('bonus/agent'), 'success');

?>
