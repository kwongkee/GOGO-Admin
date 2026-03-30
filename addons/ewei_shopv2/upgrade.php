<?php

$sqlup="

DROP TABLE IF EXISTS `ims_ewei_shop_plugin`;
CREATE TABLE IF NOT EXISTS `ims_ewei_shop_plugin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `displayorder` int(11) DEFAULT '0',
  `identity` varchar(50) DEFAULT '',
  `category` varchar(255) DEFAULT '',
  `name` varchar(50) DEFAULT '',
  `version` varchar(10) DEFAULT '',
  `author` varchar(20) DEFAULT '',
  `status` int(11) DEFAULT '0',
  `thumb` varchar(255) DEFAULT '',
  `desc` text,
  `iscom` tinyint(3) DEFAULT '0',
  `deprecated` tinyint(3) DEFAULT '0',
  `isv2` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_displayorder` (`displayorder`),
  KEY `idx_identity` (`identity`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=42 ;

INSERT INTO `ims_ewei_shop_plugin` (`id`, `displayorder`, `identity`, `category`, `name`, `version`, `author`, `status`, `thumb`, `desc`, `iscom`, `deprecated`, `isv2`) VALUES
(1, 1, 'qiniu', 'tool', '七牛存储', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/qiniu.jpg', NULL, 1, 0, 0),
(2, 2, 'taobao', 'tool', '商品助手', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/taobao.jpg', '', 0, 0, 0),
(3, 3, 'commission', 'biz', '人人分销', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/commission.jpg', '', 0, 0, 0),
(4, 4, 'poster', 'sale', '超级海报', '1.2', '官方', 1, '../addons/ewei_shopv2/static/images/poster.jpg', '', 0, 0, 0),
(5, 5, 'verify', 'biz', 'O2O核销', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/verify.jpg', NULL, 1, 0, 0),
(6, 6, 'tmessage', 'tool', '会员群发', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/tmessage.jpg', NULL, 1, 0, 0),
(7, 7, 'perm', 'help', '分权系统', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/perm.jpg', NULL, 1, 0, 0),
(8, 8, 'sale', 'sale', '营销宝', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/sale.jpg', NULL, 1, 0, 0),
(9, 9, 'designer', 'help', '店铺装修V1', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/designer.jpg', NULL, 0, 1, 0),
(10, 10, 'creditshop', 'biz', '积分商城', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/creditshop.jpg', '', 0, 0, 0),
(11, 11, 'virtual', 'biz', '虚拟物品', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/virtual.jpg', NULL, 1, 0, 0),
(12, 11, 'article', 'help', '文章营销', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/article.jpg', '', 0, 0, 0),
(13, 13, 'coupon', 'sale', '超级券', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/coupon.jpg', NULL, 1, 0, 0),
(14, 14, 'postera', 'sale', '活动海报', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/postera.jpg', '', 0, 0, 0),
(15, 15, 'system', 'help', '系统工具', '1.0', '官方', 0, '../addons/ewei_shopv2/static/images/system.jpg', NULL, 0, 1, 0),
(16, 16, 'diyform', 'help', '自定表单', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/diyform.jpg', '', 0, 0, 0),
(17, 17, 'exhelper', 'help', '快递助手', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/exhelper.jpg', '', 0, 0, 0),
(18, 18, 'groups', 'biz', '人人拼团', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/groups.jpg', '', 0, 0, 0),
(19, 19, 'diypage', 'help', '店铺装修', '2.0', '官方', 1, '../addons/ewei_shopv2/static/images/designer.jpg', '', 0, 0, 0),
(20, 20, 'globonus', 'biz', '全民股东', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/globonus.jpg', '', 0, 0, 0),
(21, 21, 'merch', 'biz', '多商户', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/merch.jpg', '', 0, 0, 1),
(22, 22, 'qa', 'help', '帮助中心', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/qa.jpg', '', 0, 0, 1),
(23, 23, 'sms', 'tool', '短信提醒', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/sms.jpg', '', 1, 0, 1),
(24, 24, 'sign', 'tool', '积分签到', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/sign.jpg', '', 0, 0, 1),
(25, 25, 'sns', 'sale', '全民社区', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/sns.jpg', '', 0, 0, 1),
(26, 26, 'wap', 'tool', '全网通', '1.0', '官方', 1, '', '', 1, 0, 1),
(27, 27, 'h5app', 'tool', 'H5APP', '1.0', '官方', 1, '', '', 1, 0, 1),
(28, 28, 'abonus', 'biz', '区域代理', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/abonus.jpg', '', 0, 0, 1),
(29, 29, 'printer', 'tool', '小票打印机', '1.0', '官方', 1, '', '', 1, 0, 1),
(30, 30, 'bargain', 'tool', '砍价活动', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/bargain.jpg', '', 0, 0, 1),
(31, 31, 'task', 'sale', '任务中心', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/task.jpg', '', 0, 0, 1),
(32, 32, 'cashier', 'biz', '收银台', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/cashier.jpg', '', 0, 0, 1),
(33, 33, 'messages', 'tool', '消息群发', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/messages.jpg', '', 0, 0, 1),
(34, 34, 'seckill', 'sale', '整点秒杀', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/seckill.jpg', '', 0, 0, 1),
(35, 35, 'exchange', 'biz', '兑换中心', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/exchange.jpg', '', 0, 0, 1),
(36, 36, 'lottery', 'sale', '游戏营销', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/lottery.jpg', '', 0, 0, 1),
(37, 37, 'wxcard', 'sale', '微信卡券', '1.0', '官方', 1, '', '', 1, 0, 1),
(38, 38, 'quick', 'biz', '快速购买', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/quick.jpg', '', 0, 0, 1),
(39, 39, 'mmanage', 'tool', '手机端商家管理中心', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/mmanage.jpg', '', 0, 0, 1),
(40, 40, 'pc', 'tool', 'PC端', '1.0', '二开', 1, '../addons/ewei_shopv2/static/images/pc.jpg', '', 0, 0, 0),
(41, 41, 'live', 'sale', '互动直播', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/live.jpg', '', 0, 0, 1),
(42, 42, 'app', 'sale', '小程序', '1.0', '官方', 1, '../addons/ewei_shopv2/static/images/app.jpg', '', 0, 0, 1);

";
pdo_run($sqlup);


if(!pdo_tableexists('ims_ewei_shop_system_plugingrant_adv')){
$sql1="
CREATE TABLE IF NOT EXISTS `ims_ewei_shop_system_plugingrant_adv` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `advname` varchar(50) DEFAULT '',
  `link` varchar(255) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `displayorder` int(11) DEFAULT '0',
  `enabled` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_enabled` (`enabled`),
  KEY `idx_displayorder` (`displayorder`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
pdo_run($sql1);
}

if(!pdo_tableexists('ims_ewei_shop_system_plugingrant_log')){
$sql2="
CREATE TABLE IF NOT EXISTS `ims_ewei_shop_system_plugingrant_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `logno` varchar(50) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `uniacid` int(11) NOT NULL DEFAULT '0',
  `pluginid` int(11) NOT NULL DEFAULT '0',
  `identity` varchar(50) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `month` int(10) NOT NULL DEFAULT '0',
  `permendtime` int(10) NOT NULL DEFAULT '0',
  `permlasttime` int(10) NOT NULL DEFAULT '0',
  `isperm` tinyint(3) NOT NULL DEFAULT '0',
  `createtime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
pdo_run($sql2);
}

if(!pdo_tableexists('ims_ewei_shop_system_plugingrant_order')){
$sql3="
CREATE TABLE IF NOT EXISTS `ims_ewei_shop_system_plugingrant_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `logno` varchar(50) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `uniacid` int(11) NOT NULL DEFAULT '0',
  `username` varchar(255) DEFAULT NULL,
  `pluginid` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `month` int(11) NOT NULL DEFAULT '0',
  `createtime` int(10) NOT NULL DEFAULT '0',
  `paystatus` tinyint(3) NOT NULL DEFAULT '0',
  `paytime` int(10) NOT NULL DEFAULT '0',
  `paytype` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
pdo_run($sql3);
}

if(!pdo_tableexists('ims_ewei_shop_system_plugingrant_package')){
$sql4="
CREATE TABLE IF NOT EXISTS `ims_ewei_shop_system_plugingrant_package` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pluginid` varchar(255) NOT NULL DEFAULT '',
  `text` varchar(255) DEFAULT NULL,
  `thumb` varchar(1000) DEFAULT NULL,
  `data` text NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `rec` tinyint(3) NOT NULL DEFAULT '0',
  `desc` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `displayorder` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
pdo_run($sql4);
}

if(!pdo_tableexists('ims_ewei_shop_system_plugingrant_plugin')){
$sql4="
CREATE TABLE IF NOT EXISTS `ims_ewei_shop_system_plugingrant_plugin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pluginid` int(11) NOT NULL DEFAULT '0',
  `thumb` varchar(1000) NOT NULL,
  `data` text,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `sales` int(11) NOT NULL DEFAULT '0',
  `createtime` int(10) NOT NULL DEFAULT '0',
  `displayorder` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
pdo_run($sql4);
}

if(!pdo_tableexists('ims_ewei_shop_system_plugingrant_setting')){
$sql5="
CREATE TABLE IF NOT EXISTS `ims_ewei_shop_system_plugingrant_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `com` varchar(1000) NOT NULL DEFAULT '',
  `adv` varchar(1000) NOT NULL,
  `plugin` varchar(1000) NOT NULL,
  `customer` varchar(50) NOT NULL DEFAULT '0',
  `contact` text NOT NULL,
  `servertime` varchar(255) DEFAULT NULL,
  `weixin` tinyint(3) NOT NULL DEFAULT '0',
  `appid` varchar(255) DEFAULT NULL,
  `mchid` varchar(255) DEFAULT NULL,
  `apikey` varchar(255) DEFAULT NULL,
  `alipay` tinyint(3) NOT NULL,
  `account` varchar(255) DEFAULT NULL,
  `partner` varchar(255) DEFAULT NULL,
  `secret` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
pdo_run($sql5);
}

if(!pdo_tableexists('ims_ewei_shop_order')){
$sql6="
CREATE TABLE IF NOT EXISTS `ims_ewei_shop_order` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `openid` varchar(50) DEFAULT '',
  `agentid` int(11) DEFAULT '0',
  `ordersn` varchar(30) DEFAULT '',
  `price` decimal(10,2) DEFAULT '0.00',
  `goodsprice` decimal(10,2) DEFAULT '0.00',
  `discountprice` decimal(10,2) DEFAULT '0.00',
  `status` tinyint(3) DEFAULT '0',
  `paytype` tinyint(1) DEFAULT '0',
  `transid` varchar(30) DEFAULT '0',
  `remark` varchar(1000) DEFAULT '',
  `addressid` int(11) DEFAULT '0',
  `dispatchprice` decimal(10,2) DEFAULT '0.00',
  `dispatchid` int(10) DEFAULT '0',
  `createtime` int(10) DEFAULT NULL,
  `dispatchtype` tinyint(3) DEFAULT '0',
  `carrier` text,
  `refundid` int(11) DEFAULT '0',
  `iscomment` tinyint(3) DEFAULT '0',
  `creditadd` tinyint(3) DEFAULT '0',
  `deleted` tinyint(3) DEFAULT '0',
  `userdeleted` tinyint(3) DEFAULT '0',
  `finishtime` int(11) DEFAULT '0',
  `paytime` int(11) DEFAULT '0',
  `expresscom` varchar(30) NOT NULL DEFAULT '',
  `expresssn` varchar(50) NOT NULL DEFAULT '',
  `express` varchar(255) DEFAULT '',
  `sendtime` int(11) DEFAULT '0',
  `fetchtime` int(11) DEFAULT '0',
  `cash` tinyint(3) DEFAULT '0',
  `canceltime` int(11) DEFAULT NULL,
  `cancelpaytime` int(11) DEFAULT '0',
  `refundtime` int(11) DEFAULT '0',
  `isverify` tinyint(3) DEFAULT '0',
  `verified` tinyint(3) DEFAULT '0',
  `verifyopenid` varchar(255) DEFAULT '',
  `verifycode` varchar(255) DEFAULT '',
  `verifytime` int(11) DEFAULT '0',
  `verifystoreid` int(11) DEFAULT '0',
  `deductprice` decimal(10,2) DEFAULT '0.00',
  `deductcredit` int(10) DEFAULT '0',
  `deductcredit2` decimal(10,2) DEFAULT '0.00',
  `deductenough` decimal(10,2) DEFAULT '0.00',
  `virtual` int(11) DEFAULT '0',
  `virtual_info` text,
  `virtual_str` text,
  `address` text,
  `sysdeleted` tinyint(3) DEFAULT '0',
  `ordersn2` int(11) DEFAULT '0',
  `changeprice` decimal(10,2) DEFAULT '0.00',
  `changedispatchprice` decimal(10,2) DEFAULT '0.00',
  `oldprice` decimal(10,2) DEFAULT '0.00',
  `olddispatchprice` decimal(10,2) DEFAULT '0.00',
  `isvirtual` tinyint(3) DEFAULT '0',
  `couponid` int(11) DEFAULT '0',
  `couponprice` decimal(10,2) DEFAULT '0.00',
  `diyformdata` text,
  `diyformfields` text,
  `diyformid` int(11) DEFAULT '0',
  `storeid` int(11) DEFAULT '0',
  `printstate` tinyint(1) DEFAULT '0',
  `printstate2` tinyint(1) DEFAULT '0',
  `address_send` text,
  `refundstate` tinyint(3) DEFAULT '0',
  `closereason` text,
  `remarksaler` text,
  `remarkclose` text,
  `remarksend` text,
  `ismr` int(1) NOT NULL DEFAULT '0',
  `isdiscountprice` decimal(10,2) DEFAULT '0.00',
  `isvirtualsend` tinyint(1) DEFAULT '0',
  `virtualsend_info` text,
  `verifyinfo` text,
  `verifytype` tinyint(1) DEFAULT '0',
  `verifycodes` text,
  `invoicename` varchar(255) DEFAULT '',
  `merchid` int(11) DEFAULT '0',
  `ismerch` tinyint(1) DEFAULT '0',
  `parentid` int(11) DEFAULT '0',
  `isparent` tinyint(1) DEFAULT '0',
  `grprice` decimal(10,2) DEFAULT '0.00',
  `merchshow` tinyint(1) DEFAULT '0',
  `merchdeductenough` decimal(10,2) DEFAULT '0.00',
  `couponmerchid` int(11) DEFAULT '0',
  `isglobonus` tinyint(3) DEFAULT '0',
  `merchapply` tinyint(1) DEFAULT '0',
  `isabonus` tinyint(3) DEFAULT '0',
  `isborrow` tinyint(3) DEFAULT '0',
  `borrowopenid` varchar(100) DEFAULT '',
  `merchisdiscountprice` decimal(10,2) DEFAULT '0.00',
  `apppay` tinyint(3) NOT NULL DEFAULT '0',
  `coupongoodprice` decimal(10,2) DEFAULT '1.00',
  `buyagainprice` decimal(10,2) DEFAULT '0.00',
  `authorid` int(11) DEFAULT '0',
  `isauthor` tinyint(1) DEFAULT '0',
  `ispackage` tinyint(3) DEFAULT '0',
  `packageid` int(11) DEFAULT '0',
  `taskdiscountprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `seckilldiscountprice` decimal(10,2) DEFAULT '0.00',
  `verifyendtime` int(11) NOT NULL DEFAULT '0',
  `willcancelmessage` tinyint(1) DEFAULT '0',
  `sendtype` tinyint(3) NOT NULL DEFAULT '0',
  `lotterydiscountprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `contype` tinyint(1) DEFAULT '0',
  `wxid` int(11) DEFAULT '0',
  `wxcardid` varchar(50) DEFAULT '',
  `wxcode` varchar(50) DEFAULT '',
  `dispatchkey` varchar(30) NOT NULL DEFAULT '',
  `quickid` int(11) NOT NULL DEFAULT '0',
  `istrade` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_openid` (`openid`),
  KEY `idx_shareid` (`agentid`),
  KEY `idx_status` (`status`),
  KEY `idx_createtime` (`createtime`),
  KEY `idx_refundid` (`refundid`),
  KEY `idx_paytime` (`paytime`),
  KEY `idx_finishtime` (`finishtime`),
  KEY `idx_merchid` (`merchid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
pdo_run($sql6);
}

if(!pdo_tableexists('ims_ewei_shop_live')){
$sql7="
CREATE TABLE IF NOT EXISTS `ims_ewei_shop_live` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) NOT NULL DEFAULT '0',
  `merchid` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `livetype` tinyint(3) NOT NULL DEFAULT '0',
  `liveidentity` varchar(50) NOT NULL,
  `screen` tinyint(3) NOT NULL DEFAULT '0',
  `goodsid` varchar(255) NOT NULL,
  `category` int(11) NOT NULL DEFAULT '0',
  `url` varchar(1000) NOT NULL,
  `thumb` varchar(1000) NOT NULL,
  `hot` tinyint(3) NOT NULL DEFAULT '0',
  `recommend` tinyint(3) NOT NULL DEFAULT '0',
  `living` tinyint(3) NOT NULL DEFAULT '0',
  `status` tinyint(3) NOT NULL DEFAULT '0',
  `displayorder` int(11) NOT NULL DEFAULT '0',
  `livetime` int(10) NOT NULL DEFAULT '0',
  `lastlivetime` int(11) NOT NULL DEFAULT '0',
  `createtime` int(10) NOT NULL DEFAULT '0',
  `introduce` text NOT NULL,
  `packetmoney` decimal(10,2) NOT NULL DEFAULT '0.00',
  `packettotal` int(11) NOT NULL DEFAULT '0',
  `packetprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `packetdes` varchar(255) NOT NULL,
  `couponid` varchar(255) NOT NULL,
  `share_title` varchar(255) NOT NULL,
  `share_icon` varchar(1000) NOT NULL,
  `share_desc` text NOT NULL,
  `share_url` varchar(1000) NOT NULL DEFAULT '',
  `subscribe` int(11) NOT NULL DEFAULT '0',
  `subscribenotice` tinyint(3) NOT NULL DEFAULT '0',
  `visit` int(11) NOT NULL DEFAULT '0',
  `video` varchar(1000) NOT NULL DEFAULT '',
  `covertype` tinyint(3) NOT NULL DEFAULT '0',
  `cover` varchar(1000) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_merchid` (`merchid`),
  KEY `idx_category` (`category`),
  KEY `idx_hot` (`hot`),
  KEY `idx_recommend` (`recommend`),
  KEY `idx_living` (`living`),
  KEY `idx_status` (`status`),
  KEY `idx_livetime` (`livetime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
pdo_run($sql7);
}

if(!pdo_tableexists('ims_ewei_shop_live_adv')){
$sql8="
CREATE TABLE IF NOT EXISTS `ims_ewei_shop_live_adv` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `merchid` int(11) NOT NULL DEFAULT '0',
  `advname` varchar(50) DEFAULT '',
  `link` varchar(255) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `displayorder` int(11) DEFAULT '0',
  `enabled` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_enabled` (`enabled`),
  KEY `idx_displayorder` (`displayorder`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
pdo_run($sql8);
}

if(!pdo_tableexists('ims_ewei_shop_live_category')){
$sql9="
CREATE TABLE IF NOT EXISTS `ims_ewei_shop_live_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `name` varchar(50) DEFAULT NULL,
  `thumb` varchar(255) DEFAULT NULL,
  `displayorder` tinyint(3) unsigned DEFAULT '0',
  `enabled` tinyint(1) DEFAULT '1',
  `advimg` varchar(255) DEFAULT '',
  `advurl` varchar(500) DEFAULT '',
  `isrecommand` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_displayorder` (`displayorder`),
  KEY `idx_enabled` (`enabled`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
pdo_run($sql9);
}

if(!pdo_tableexists('ims_ewei_shop_live_coupon')){
$sql10="
CREATE TABLE IF NOT EXISTS `ims_ewei_shop_live_coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) NOT NULL DEFAULT '0',
  `roomid` int(11) NOT NULL DEFAULT '0',
  `couponid` int(11) NOT NULL DEFAULT '0',
  `coupontotal` int(11) NOT NULL DEFAULT '0',
  `couponlimit` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_roomid` (`roomid`),
  KEY `idx_couponid` (`couponid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
pdo_run($sql10);
}

if(!pdo_tableexists('ims_ewei_shop_live_favorite')){
$sql11="
CREATE TABLE IF NOT EXISTS `ims_ewei_shop_live_favorite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) NOT NULL DEFAULT '0',
  `roomid` int(11) NOT NULL DEFAULT '0',
  `openid` tinytext NOT NULL,
  `deleted` tinyint(3) NOT NULL DEFAULT '0',
  `createtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_roomid` (`roomid`),
  KEY `idx_deleted` (`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
pdo_run($sql11);
}

if(!pdo_tableexists('ims_ewei_shop_live_setting')){
$sql12="
CREATE TABLE IF NOT EXISTS `ims_ewei_shop_live_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) NOT NULL DEFAULT '0',
  `ismember` tinyint(3) NOT NULL DEFAULT '0',
  `share_title` varchar(255) NOT NULL,
  `share_icon` varchar(1000) NOT NULL,
  `share_desc` varchar(255) NOT NULL,
  `share_url` varchar(255) NOT NULL,
  `livenoticetime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_ismember` (`ismember`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
pdo_run($sql12);
}

if(!pdo_tableexists('ims_ewei_shop_live_view')){
$sql13="
CREATE TABLE IF NOT EXISTS `ims_ewei_shop_live_view` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) NOT NULL DEFAULT '0',
  `openid` varchar(50) NOT NULL,
  `roomid` int(11) NOT NULL DEFAULT '0',
  `viewing` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_roomid` (`roomid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
pdo_run($sql13);
}

if(!pdo_fieldexists('ewei_shop_exchange_group', 'repeat')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_exchange_group')." add `repeat` TINYINT(1) NOT NULL DEFAULT '0';");
}

if(!pdo_fieldexists('ewei_shop_exchange_group', 'koulingstart')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_exchange_group')." add `koulingstart` VARCHAR(255) NOT NULL;");
}

if(!pdo_fieldexists('ewei_shop_exchange_group', 'koulingend')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_exchange_group')." add `koulingend` VARCHAR(255) NOT NULL;");
}

if(!pdo_fieldexists('ewei_shop_exchange_group', 'kouling')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_exchange_group')." add `kouling` TINYINT(1) NOT NULL DEFAULT '0';");
}

if(!pdo_fieldexists('ewei_shop_exchange_group', 'chufa')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_exchange_group')." add `chufa` VARCHAR(255) NOT NULL;");
}

if(!pdo_fieldexists('ewei_shop_exchange_group', 'chufaend')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_exchange_group')." add `chufaend` VARCHAR(255) NOT NULL;");
}

if(!pdo_fieldexists('ewei_shop_order_peerpay_payinfo', 'tid')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_order_peerpay_payinfo')." add `tid` VARCHAR(255) NOT NULL;");
}

if(!pdo_fieldexists('ewei_shop_order_peerpay_payinfo', 'openid')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_order_peerpay_payinfo')." add `openid` VARCHAR(255) NOT NULL;");
}

if(!pdo_fieldexists('ewei_shop_system_plugingrant_plugin', 'name')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_system_plugingrant_plugin')." add `name` VARCHAR(255) NOT NULL;");
}

if(!pdo_fieldexists('ewei_shop_system_plugingrant_plugin', 'plugintype')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_system_plugingrant_plugin')." add `plugintype` TINYINT(3) NOT NULL DEFAULT '0';");
}

if(!pdo_fieldexists('ewei_shop_coupon', 'quickget')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_coupon')." add `quickget` TINYINT(1) DEFAULT 0 ;");
}

if(!pdo_fieldexists('ewei_shop_merch_user', 'maxgoods')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_merch_user')." add `maxgoods` INT(11) NOT NULL;");
}

if(!pdo_fieldexists('ewei_shop_order', 'isnewstore')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_order')." add `isnewstore` TINYINT(3) NOT NULL;");
}


if(!pdo_fieldexists('ewei_shop_goods', 'islive')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_goods')." add `islive` INT(11) NOT NULL;");
}

if(!pdo_fieldexists('ewei_shop_goods', 'liveprice')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_goods')." add `liveprice` decimal(10,2) NOT NULL DEFAULT '0';");
}

if(!pdo_fieldexists('ewei_shop_goods_option', 'islive')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_goods_option')." add `islive` INT(11) NOT NULL;");
}

if(!pdo_fieldexists('ewei_shop_goods_option', 'liveprice')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_goods_option')." add `liveprice` decimal(10,2) NOT NULL DEFAULT '0';");
}

if(!pdo_fieldexists('ewei_shop_member', 'membercardid')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_member')." add `membercardid` VARCHAR(255) NOT NULL;");
}

if(!pdo_fieldexists('ewei_shop_member', 'membercardcode')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_member')." add `membercardcode` VARCHAR(255) NOT NULL;");
}

if(!pdo_fieldexists('ewei_shop_member', 'membershipnumber')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_member')." add `membershipnumber` VARCHAR(255) NOT NULL;");
}

if(!pdo_fieldexists('ewei_shop_member', 'membercardactive')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_member')." add `membercardactive` VARCHAR(255) NOT NULL;");
}

if(!pdo_fieldexists('ewei_shop_order', 'liveid')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_order')." add `liveid` INT(11) NOT NULL;");
}
