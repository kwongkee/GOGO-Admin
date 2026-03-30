<?php
// 模块LTD提供
$sql = "\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_member_log') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `openid` varchar(255) DEFAULT '',\n  `type` tinyint(3) DEFAULT NULL COMMENT '0 充值 1 提现',\n  `logno` varchar(255) DEFAULT '',\n  `title` varchar(255) DEFAULT '',\n  `createtime` int(11) DEFAULT '0',\n  `status` int(11) DEFAULT '0' COMMENT '0 生成 1 成功 2 失败',\n  `money` decimal(10,2) DEFAULT '0.00',\n  `rechargetype` varchar(255) DEFAULT '' COMMENT '充值类型',\n  `gives` decimal(10,2) DEFAULT NULL,\n  `couponid` int(11) DEFAULT '0',\n  PRIMARY KEY (`id`),\n  KEY `idx_uniacid` (`uniacid`),\n  KEY `idx_openid` (`openid`),\n  KEY `idx_type` (`type`),\n  KEY `idx_createtime` (`createtime`),\n  KEY `idx_status` (`status`)\n) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
pdo_fetchall($sql);
$sql = "\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_exhelper_express') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `type` int(1) NOT NULL DEFAULT '1' COMMENT '单据分类 1为快递单 2为发货单',\n  `expressname` varchar(255) DEFAULT '',\n  `expresscom` varchar(255) NOT NULL DEFAULT '',\n  `express` varchar(255) NOT NULL DEFAULT '',\n  `width` decimal(10,2) DEFAULT '0.00',\n  `datas` text,\n  `height` decimal(10,2) DEFAULT '0.00',\n  `bg` varchar(255) DEFAULT '',\n  `isdefault` tinyint(3) DEFAULT '0',\n  PRIMARY KEY (`id`),\n  KEY `idx_uniacid` (`uniacid`),\n  KEY `idx_isdefault` (`isdefault`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_category2') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0' COMMENT '所属帐号',\n  `name` varchar(50) DEFAULT NULL COMMENT '分类名称',\n  `thumb` varchar(255) DEFAULT NULL COMMENT '分类图片',\n  `parentid` int(11) DEFAULT '0' COMMENT '上级分类ID,0为第一级',\n  `isrecommand` int(10) DEFAULT '0',\n  `description` varchar(500) DEFAULT NULL COMMENT '分类介绍',\n  `displayorder` tinyint(3) unsigned DEFAULT '0' COMMENT '排序',\n  `enabled` tinyint(1) DEFAULT '1' COMMENT '是否开启',\n  `ishome` tinyint(3) DEFAULT '0',\n  `advimg` varchar(255) DEFAULT '',\n  `advurl` varchar(500) DEFAULT '',\n  `level` tinyint(3) DEFAULT '0',\n  PRIMARY KEY (`id`),\n  KEY `idx_uniacid` (`uniacid`),\n  KEY `idx_displayorder` (`displayorder`),\n  KEY `idx_enabled` (`enabled`),\n  KEY `idx_parentid` (`parentid`),\n  KEY `idx_isrecommand` (`isrecommand`),\n  KEY `idx_ishome` (`ishome`)\n) ENGINE=MyISAM AUTO_INCREMENT=61 DEFAULT CHARSET=utf8;\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_hotel_room') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `goodsid` int(11) DEFAULT '0',\n  `title` varchar(255) DEFAULT '',\n  `thumb` varchar(255) DEFAULT '',\n  `oprice` decimal(10,2) DEFAULT '0.00',\n  `cprice`  decimal(10,2) DEFAULT '0.00',\n  `deposit` decimal(10,2) DEFAULT '0.00',\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci;\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_book') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `uid` int(11) DEFAULT '0',\n  `mobile` varchar(30) DEFAULT '',\n  `time` varchar(255) DEFAULT '',\n  `contact` text,\n  `goods` int(11) DEFAULT '0',\n  `message` text,\n  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n  `type` int(11) DEFAULT '0',\n  `status` int(1) DEFAULT '0',\n  `delete` int(1) DEFAULT '0',\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci;\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_print_list') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `name` varchar(45) DEFAULT '',\n  `key` varchar(30) DEFAULT '',\n  `print_no` varchar(30) DEFAULT '',\n  `type` int(1) DEFAULT '0',\n  `status` int(3) DEFAULT '0',\n  `member_code` varchar(50) DEFAULT '',\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='酒店房间价格表' AUTO_INCREMENT=1 ;\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_hotel_room_price') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `roomid` int(11) DEFAULT '0',\n  `roomdate` int(11) DEFAULT '0',\n  `thisdate` varchar(255) DEFAULT '',\n  `oprice`  decimal(10,2) DEFAULT '0.00',\n  `cprice`  decimal(10,2) DEFAULT '0.00',\n  `mprice`  decimal(10,2) DEFAULT '0.00',\n  `num` varchar(255) DEFAULT '',\n  `status` int(11) DEFAULT '0',\n\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci;\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_order_room') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `orderid` int(11) DEFAULT '0',\n  `roomdate` int(11) DEFAULT '0',\n  `thisdate` varchar(255) DEFAULT '',\n  `oprice` decimal(10,2) DEFAULT '0.00',\n  `cprice` decimal(10,2) DEFAULT '0.00',\n  `mprice` decimal(10,2) DEFAULT '0.00',\n  `roomid` int(11) DEFAULT '0',\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci;\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_book') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `uid` int(11) DEFAULT '0',\n  `mobile` varchar(30) DEFAULT '',\n  `time` varchar(255) DEFAULT '',\n  `contact` text,\n  `goods` int(11) DEFAULT '0',\n  `message` text,\n  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n  `type` int(11) DEFAULT '0',\n  `status` int(1) DEFAULT '0',\n  `delete` int(1) DEFAULT '0',\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci;\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_print_list') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `name` varchar(45) DEFAULT '',\n  `key` varchar(30) DEFAULT '',\n  `print_no` varchar(30) DEFAULT '',\n  `type` int(1) DEFAULT '0',\n  `status` int(3) DEFAULT '0',\n  `member_code` varchar(50) DEFAULT '',\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='酒店房间价格表' AUTO_INCREMENT=1 ;\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_coupon') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `catid` int(11) DEFAULT '0',\n  `couponname` varchar(255) DEFAULT '',\n  `gettype` tinyint(3) DEFAULT '0',\n  `getmax` int(11) DEFAULT '0',\n  `usetype` tinyint(3) DEFAULT '0' COMMENT '消费方式 0 付款使用 1 下单使用',\n  `returntype` tinyint(3) DEFAULT '0' COMMENT '退回方式 0 不可退回 1 取消订单(未付款) 2.退款可以退回',\n  `bgcolor` varchar(255) DEFAULT '',\n  `enough` decimal(10,2) DEFAULT '0.00',\n  `timelimit` tinyint(3) DEFAULT '0' COMMENT '0 领取后几天有效 1 时间范围',\n  `coupontype` tinyint(3) DEFAULT '0' COMMENT '0 优惠券 1 充值券',\n  `timedays` int(11) DEFAULT '0',\n  `timestart` int(11) DEFAULT '0',\n  `timeend` int(11) DEFAULT '0',\n  `discount` decimal(10,2) DEFAULT '0.00' COMMENT '折扣',\n  `deduct` decimal(10,2) DEFAULT '0.00' COMMENT '抵扣',\n  `backtype` tinyint(3) DEFAULT '0',\n  `backmoney` varchar(50) DEFAULT '' COMMENT '返现',\n  `backcredit` varchar(50) DEFAULT '' COMMENT '返积分',\n  `backredpack` varchar(50) DEFAULT '',\n  `backwhen` tinyint(3) DEFAULT '0',\n  `thumb` varchar(255) DEFAULT '',\n  `desc` text,\n  `createtime` int(11) DEFAULT '0',\n  `total` int(11) DEFAULT '0' COMMENT '数量 -1 不限制',\n  `status` tinyint(3) DEFAULT '0' COMMENT '可用',\n  `money` decimal(10,2) DEFAULT '0.00' COMMENT '购买价格',\n  `respdesc` text COMMENT '推送描述',\n  `respthumb` varchar(255) DEFAULT '' COMMENT '推送图片',\n  `resptitle` varchar(255) DEFAULT '' COMMENT '推送标题',\n  `respurl` varchar(255) DEFAULT '',\n  `credit` int(11) DEFAULT '0',\n  `usecredit2` tinyint(3) DEFAULT '0',\n  `remark` varchar(1000) DEFAULT '',\n  `descnoset` tinyint(3) DEFAULT '0',\n  `pwdkey` varchar(255) DEFAULT '',\n  `pwdsuc` text,\n  `pwdfail` text,\n  `pwdurl` varchar(255) DEFAULT '',\n  `pwdask` text,\n  `pwdstatus` tinyint(3) DEFAULT '0',\n  `pwdtimes` int(11) DEFAULT '0',\n  `pwdfull` text,\n  `pwdwords` text,\n  `pwdopen` tinyint(3) DEFAULT '0',\n  `pwdown` text,\n  `pwdexit` varchar(255) DEFAULT '',\n  `pwdexitstr` text,\n  `displayorder` int(11) DEFAULT '0',\n  PRIMARY KEY (`id`),\n  KEY `idx_uniacid` (`uniacid`),\n  KEY `idx_coupontype` (`coupontype`),\n  KEY `idx_timestart` (`timestart`),\n  KEY `idx_timeend` (`timeend`),\n  KEY `idx_timelimit` (`timelimit`),\n  KEY `idx_status` (`status`),\n  KEY `idx_givetype` (`backtype`),\n  KEY `idx_catid` (`catid`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8;\n\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_coupon_category') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `name` varchar(255) DEFAULT '',\n  `displayorder` int(11) DEFAULT '0',\n  `status` int(11) DEFAULT '0',\n  PRIMARY KEY (`id`),\n  KEY `idx_uniacid` (`uniacid`),\n  KEY `idx_displayorder` (`displayorder`),\n  KEY `idx_status` (`status`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8;\n\n\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_coupon_data') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `openid` varchar(255) DEFAULT '',\n  `couponid` int(11) DEFAULT '0',\n  `gettype` tinyint(3) DEFAULT '0' COMMENT '获取方式 0 发放 1 领取 2 积分商城',\n  `used` int(11) DEFAULT '0',\n  `usetime` int(11) DEFAULT '0',\n  `gettime` int(11) DEFAULT '0' COMMENT '获取时间',\n  `senduid` int(11) DEFAULT '0',\n  `ordersn` varchar(255) DEFAULT '',\n  `back` tinyint(3) DEFAULT '0',\n  `backtime` int(11) DEFAULT '0',\n  PRIMARY KEY (`id`),\n  KEY `idx_couponid` (`couponid`),\n  KEY `idx_gettype` (`gettype`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8;\n\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_coupon_guess') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `couponid` int(11) DEFAULT '0',\n  `openid` varchar(255) DEFAULT '',\n  `times` int(11) DEFAULT '0',\n  `pwdkey` varchar(255) DEFAULT '',\n  `ok` tinyint(3) DEFAULT '0',\n  PRIMARY KEY (`id`),\n  KEY `idx_uniacid` (`uniacid`),\n  KEY `idx_couponid` (`couponid`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8;\n\n\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_coupon_log') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `logno` varchar(255) DEFAULT '',\n  `openid` varchar(255) DEFAULT '',\n  `couponid` int(11) DEFAULT '0',\n  `status` int(11) DEFAULT '0',\n  `paystatus` tinyint(3) DEFAULT '0',\n  `creditstatus` tinyint(3) DEFAULT '0',\n  `createtime` int(11) DEFAULT '0',\n  `paytype` tinyint(3) DEFAULT '0',\n  `getfrom` tinyint(3) DEFAULT '0' COMMENT '0 发放 1 中心 2 积分兑换',\n  PRIMARY KEY (`id`),\n  KEY `idx_uniacid` (`uniacid`),\n  KEY `idx_couponid` (`couponid`),\n  KEY `idx_status` (`status`),\n  KEY `idx_paystatus` (`paystatus`),\n  KEY `idx_createtime` (`createtime`),\n  KEY `idx_getfrom` (`getfrom`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8;\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_member_aging_rechange') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `openid` varchar(255) DEFAULT '',\n  `paymethod` tinyint(1) DEFAULT '0',\n  `sendmonth` tinyint(1) DEFAULT '0',\n  `sendtime` tinyint(2) DEFAULT '0',\n  `ratio` decimal(10,2) DEFAULT '0.00',\n  `num` decimal(10,2) DEFAULT '0.00',\n  `qnum` int(11) DEFAULT '0',\n  `phase` int(11) DEFAULT '0',\n  `qtotal` decimal(10,2) DEFAULT '0.00',\n  `sendpaytime` int(11) DEFAULT '0',\n  `status` tinyint(1) DEFAULT '0',\n  `createtime` int(11) DEFAULT '0',\n  PRIMARY KEY (`id`),\n  KEY `idx_uniacid` (`uniacid`),\n  KEY `idx_openid` (`openid`)\n) ENGINE=MyISAM  DEFAULT CHARSET=utf8;\n\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_exhelper_senduser') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `sendername` varchar(255) DEFAULT '' COMMENT '发件人',\n  `sendertel` varchar(255) DEFAULT '' COMMENT '发件人联系电话',\n  `sendersign` varchar(255) DEFAULT '' COMMENT '发件人签名',\n  `sendercode` int(11) DEFAULT NULL COMMENT '发件地址邮编',\n  `senderaddress` varchar(255) DEFAULT '' COMMENT '发件地址',\n  `sendercity` varchar(255) DEFAULT NULL COMMENT '发件城市',\n  `isdefault` tinyint(3) DEFAULT '0',\n  PRIMARY KEY (`id`),\n  KEY `idx_uniacid` (`uniacid`),\n  KEY `idx_isdefault` (`isdefault`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;\n\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_exhelper_sys') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) NOT NULL DEFAULT '0',\n  `ip` varchar(20) NOT NULL DEFAULT 'localhost',\n  `port` int(11) NOT NULL DEFAULT '8000',\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;\n\n\nCREATE TABLE IF NOT EXISTS  " . tablename('sz_yi_express') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `express_name` varchar(50) DEFAULT '',\n  `displayorder` int(11) DEFAULT '0',\n  `express_price` varchar(10) DEFAULT '',\n  `express_area` varchar(100) DEFAULT '',\n  `express_url` varchar(255) DEFAULT '',\n  PRIMARY KEY (`id`),\n  KEY `idx_uniacid` (`uniacid`),\n  KEY `idx_displayorder` (`displayorder`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_diyform_category') . " (\n`id`  int(11) NOT NULL AUTO_INCREMENT ,\n`uniacid`  int(11) NULL DEFAULT 0 COMMENT '所属帐号' ,\n`name`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '分类名称' ,\nPRIMARY KEY (`id`),\nINDEX `idx_uniacid` USING BTREE (`uniacid`) \n) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci;\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_diyform_data') . " (\n`id`  int(11) NOT NULL AUTO_INCREMENT ,\n`uniacid`  int(11) NOT NULL DEFAULT 0 ,\n`typeid`  int(11) NOT NULL DEFAULT 0 COMMENT '类型id' ,\n`cid`  int(11) NULL DEFAULT 0 COMMENT '关联id' ,\n`diyformfields`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,\n`fields`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '字符集' ,\n`openid`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '使用者openid' ,\n`type`  tinyint(2) NULL DEFAULT 0 COMMENT '该数据所属模块' ,\nPRIMARY KEY (`id`),\nINDEX `idx_uniacid` USING BTREE (`uniacid`),\nINDEX `idx_typeid` USING BTREE (`typeid`) ,\nINDEX `idx_cid` USING BTREE (`cid`)\n) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci;\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_diyform_temp') . " (\n`id`  int(11) NOT NULL AUTO_INCREMENT ,\n`uniacid`  int(11) NOT NULL DEFAULT 0 ,\n`typeid`  int(11) NULL DEFAULT 0 ,\n`cid`  int(11) NOT NULL DEFAULT 0 COMMENT '关联id' ,\n`diyformfields`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,\n`fields`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '字符集' ,\n`openid`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '使用者openid' ,\n`type`  tinyint(1) NULL DEFAULT 0 COMMENT '类型' ,\n`diyformid`  int(11) NULL DEFAULT 0 ,\n`diyformdata`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,\nPRIMARY KEY (`id`),\nINDEX `idx_uniacid` USING BTREE (`uniacid`) ,\nINDEX `idx_cid` USING BTREE (`cid`)\n) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci;\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_diyform_type') . " (\n`id`  int(11) NOT NULL AUTO_INCREMENT ,\n`uniacid`  int(11) NOT NULL DEFAULT 0 ,\n`cate`  int(11) NULL DEFAULT 0 ,\n`title`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '分类名称' ,\n`fields`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '字段集' ,\n`usedata`  int(11) NOT NULL DEFAULT 0 COMMENT '已用数据' ,\n`alldata`  int(11) NOT NULL DEFAULT 0 COMMENT '全部数据' ,\n`status`  tinyint(1) NULL DEFAULT 1 COMMENT '状态' ,\nPRIMARY KEY (`id`),\nINDEX `idx_uniacid` USING BTREE (`uniacid`) ,\nINDEX `idx_cate` USING BTREE (`cate`)\n) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci;\n";
pdo_fetchall($sql);
pdo_fetchall('ALTER TABLE  ' . tablename('sz_yi_member') . ' CHANGE  `pwd`  `pwd` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;');

if (!pdo_fieldexists('sz_yi_goods', 'cates')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD     `cates` text;');
}

if (!pdo_fieldexists('sz_yi_goods', 'diyformtype')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `diyformtype` tinyint(3) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'manydeduct')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `manydeduct` tinyint(1) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'dispatchtype')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `dispatchtype` tinyint(1) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'dispatchid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `dispatchid` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'dispatchprice')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `dispatchprice`  decimal(10,2) DEFAULT \'0.00\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'deduct2')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `deduct2`  decimal(10,2) DEFAULT \'0.00\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'edmoney')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `edmoney`  decimal(10,2) DEFAULT \'0.00\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'ednum')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `ednum` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'edareas')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `edareas` text DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'diyformid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `diyformid` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'diymode')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `diymode` tinyint(3) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_member', 'regtype')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD    `regtype` tinyint(3) DEFAULT \'1\';');
}

if (!pdo_fieldexists('sz_yi_member', 'isbindmobile')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD    `isbindmobile` tinyint(3) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_member', 'isjumpbind')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD    `isjumpbind` tinyint(3) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_store', 'realname')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_store') . ' ADD `realname` varchar(255) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_store', 'mobile')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_store') . ' ADD `mobile` varchar(255) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_store', 'fetchtime')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_store') . ' ADD `fetchtime` varchar(255) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_store', 'type')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_store') . ' ADD `type` tinyint(1) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_member', 'diymemberid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD    `diymemberid` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_member', 'isblack')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD    `isblack` tinyint(3) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_member', 'diymemberdataid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD    `diymemberdataid` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_member', 'diycommissionid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD    `diycommissionid` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_member', 'diycommissiondataid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD    `diycommissiondataid` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_member', 'diymemberfields')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD    `diymemberfields` text NULL;');
}

if (!pdo_fieldexists('sz_yi_member', 'diymemberdata')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD    `diymemberdata` text NULL;');
}

if (!pdo_fieldexists('sz_yi_member', 'diycommissionfields')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD    `diycommissionfields` text NULL;');
}

if (!pdo_fieldexists('sz_yi_member', 'diycommissiondata')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD    `diycommissiondata` text NULL;');
}

if (!pdo_fieldexists('sz_yi_member_cart', 'diyformdata')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member_cart') . ' ADD    `diyformdata` text NULL;');
}

if (!pdo_fieldexists('sz_yi_member_cart', 'diyformfields')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member_cart') . ' ADD    `diyformfields` text NULL;');
}

if (!pdo_fieldexists('sz_yi_member_cart', 'diyformdataid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member_cart') . ' ADD    `diyformdataid` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_member_cart', 'diyformid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member_cart') . ' ADD    `diyformid` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order', 'couponprice')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `couponprice`  decimal(10,2) DEFAULT \'0.00\';');
}

if (!pdo_fieldexists('sz_yi_order', 'diyformid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD    `diyformid` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order_goods', 'openid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_goods') . ' ADD    `openid` varchar(255) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_order', 'storeid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD    `storeid` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order', 'diyformid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD    `diyformid` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order', 'diyformdata')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD    `diyformdata` text NULL;');
}

if (!pdo_fieldexists('sz_yi_order', 'diyformfields')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD    `diyformfields` text NULL;');
}

if (!pdo_fieldexists('sz_yi_order', 'couponid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD    `couponid` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order', 'couponprice')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `couponprice`  decimal(10,2) DEFAULT \'0.00\';');
}

if (!pdo_fieldexists('sz_yi_order_goods', 'diyformdataid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_goods') . ' ADD    `diyformdataid` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order_goods', 'diyformid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_goods') . ' ADD    `diyformid` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order_goods', 'diyformdata')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_goods') . ' ADD    `diyformdata` text NULL;');
}

if (!pdo_fieldexists('sz_yi_order_goods', 'diyformfields')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_goods') . ' ADD    `diyformfields` text NULL;');
}

$info = pdo_fetch('select * from ' . tablename('sz_yi_plugin') . ' where identity= "exhelper"  order by id desc limit 1');

if (!$info) {
	$sql = 'INSERT INTO ' . tablename('sz_yi_plugin') . ' (`displayorder`, `identity`, `name`, `version`, `author`, `status`, `category`) VALUES(0, \'exhelper\', \'快递助手\', \'1.0\', \'官方\', 1, \'tool\');';
	pdo_fetchall($sql);
}

$info = pdo_fetch('select * from ' . tablename('sz_yi_plugin') . ' where identity= "yunpay"  order by id desc limit 1');

if (!$info) {
	$sql = 'INSERT INTO ' . tablename('sz_yi_plugin') . ' (`displayorder`, `identity`, `name`, `version`, `author`, `status`, `category`) VALUES(0, \'yunpay\', \'云支付\', \'1.0\', \'云支付\', 1, \'tool\');';
	pdo_fetchall($sql);
}

$info = pdo_fetch('select * from ' . tablename('sz_yi_plugin') . ' where identity= "supplier"  order by id desc limit 1');

if (!$info) {
	$sql = 'INSERT INTO ' . tablename('sz_yi_plugin') . ' (`displayorder`, `identity`, `name`, `version`, `author`, `status`, `category`) VALUES(0, \'supplier\', \'供应商\', \'1.0\', \'官方\', 1, \'biz\');';
	pdo_fetchall($sql);
}

$info = pdo_fetch('select * from ' . tablename('sz_yi_plugin') . ' where identity= "diyform"  order by id desc limit 1');

if (!$info) {
	$sql = 'INSERT INTO ' . tablename('sz_yi_plugin') . ' (`displayorder`, `identity`, `name`, `version`, `author`, `status`, `category`) VALUES(0, \'diyform\', \'自定义表单\', \'1.0\', \'官方\', 1, \'help\');';
	pdo_fetchall($sql);
}

$info = pdo_fetch('select * from ' . tablename('sz_yi_plugin') . ' where identity= "system"  order by id desc limit 1');

if (!$info) {
	$sql = 'INSERT INTO ' . tablename('sz_yi_plugin') . ' (`displayorder`, `identity`, `name`, `version`, `author`, `status`, `category`) VALUES(0, \'system\', \'系统工具\', \'1.0\', \'官方\', 1, \'help\');';
	pdo_fetchall($sql);
}
else {
	$sql = 'update ' . tablename('sz_yi_plugin') . ' set `name` = \'系统工具\' where `identity` = \'system\';';
	pdo_fetchall($sql);
}

if (!pdo_fieldexists('sz_yi_goods', 'shorttitle')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD  `shorttitle`  VARCHAR( 500 ) DEFAULT NULL;');
}

if (!pdo_fieldexists('sz_yi_goods', 'commission_level_id')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD  `commission_level_id`  int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order', 'printstate')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD  `printstate`  tinyint(3) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order', 'printstate2')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD  `printstate2`  tinyint(3) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order_goods', 'printstate')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_goods') . ' ADD  `printstate`  tinyint(3) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order_goods', 'printstate2')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_goods') . ' ADD  `printstate2`  tinyint(3) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_dispatch', 'isdefault')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_dispatch') . ' ADD  `isdefault`  tinyint(1) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_dispatch', 'calculatetype')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_dispatch') . ' ADD  `calculatetype`  tinyint(1) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_dispatch', 'firstnumprice')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_dispatch') . ' ADD  `firstnumprice`  decimal(10,2) DEFAULT \'0.00\';');
}

if (!pdo_fieldexists('sz_yi_dispatch', 'secondnumprice')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_dispatch') . ' ADD  `secondnumprice`  decimal(10,2) DEFAULT \'0.00\';');
}

if (!pdo_fieldexists('sz_yi_dispatch', 'firstnum')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_dispatch') . ' ADD  `firstnum`  int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_dispatch', 'secondnum')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_dispatch') . ' ADD  `secondnum`  int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_dispatch', 'supplier_uid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_dispatch') . ' ADD  `supplier_uid`  int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_article_sys', 'article_area')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_article_sys') . ' ADD  `article_area`  TEXT NULL COMMENT \'文章阅读地区\';');
}

if (!pdo_fieldexists('sz_yi_article', 'article_rule_money_total')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_article') . ' ADD  `article_rule_money_total`  DECIMAL( 10, 2 ) NOT NULL DEFAULT \'0\' COMMENT \'最高累计奖金\' AFTER `article_rule_money`;');
}

if (!pdo_fieldexists('sz_yi_article', 'article_rule_userd_money')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_article') . ' ADD  `article_rule_userd_money` DECIMAL( 10, 2 ) NOT NULL DEFAULT \'0\' COMMENT \'截止目前累计奖励金额\' ');
}

if (pdo_tableexists('sz_yi_af_supplier')) {
	if (!pdo_fieldexists('sz_yi_af_supplier', 'status')) {
		pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_af_supplier') . ' ADD `status` TINYINT( 3 ) NOT NULL COMMENT \'0申请1驳回2通过\' AFTER `productname`;');
	}
}

if (pdo_tableexists('sz_yi_supplier_apply')) {
	if (!pdo_fieldexists('sz_yi_perm_role', 'status1')) {
		pdo_query('ALTER TABLE ' . tablename('sz_yi_perm_role') . ' ADD `status1` tinyint(3) NOT NULL COMMENT \'1：供应商开启\';');
	}

	if (!pdo_fieldexists('sz_yi_perm_user', 'status1')) {
		pdo_query('ALTER TABLE ' . tablename('sz_yi_perm_user') . ' ADD `status1` tinyint(3) NOT NULL COMMENT \'1：供应商开启\';');
	}

	if (!pdo_fieldexists('sz_yi_perm_user', 'openid')) {
		pdo_query('ALTER TABLE ' . tablename('sz_yi_perm_user') . ' ADD `openid` VARCHAR( 255 ) NOT NULL;');
	}

	if (!pdo_fieldexists('sz_yi_perm_user', 'username')) {
		pdo_query('ALTER TABLE ' . tablename('sz_yi_perm_user') . ' ADD `username` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;');
	}

	if (!pdo_fieldexists('sz_yi_perm_user', 'password')) {
		pdo_query('ALTER TABLE ' . tablename('sz_yi_perm_user') . ' ADD `username` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;');
	}

	if (!pdo_fieldexists('sz_yi_perm_user', 'brandname')) {
		pdo_query('ALTER TABLE ' . tablename('sz_yi_perm_user') . ' ADD `brandname` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;');
	}

	if (!pdo_fieldexists('sz_yi_supplier_apply', 'apply_money')) {
		pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_supplier_apply') . ' ADD `apply_money` DECIMAL( 10, 2 ) NOT NULL DEFAULT \'0.00\' COMMENT \'申请提现金额\';');
	}

	if (pdo_fieldexists('sz_yi_supplier_apply', 'apply_money')) {
		pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_supplier_apply') . ' CHANGE `apply_money` `apply_money` DECIMAL( 10, 2 ) NOT NULL DEFAULT \'0.00\' COMMENT \'申请提现金额\';');
	}

	if (!pdo_fieldexists('sz_yi_supplier_apply', 'uniacid')) {
		pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_supplier_apply') . ' ADD `uniacid` int(11) NOT NULL DEFAULT \'0\';');
	}

	$suppliers = pdo_fetchall('select uniacid,uid from ' . tablename('sz_yi_perm_user') . ' where status=1 and roleid=(select id from ' . tablename('sz_yi_perm_role') . ' where status=1 and status1=1 )');

	if (!empty($suppliers)) {
		foreach ($suppliers as $value) {
			$now_sup_apply_ids = pdo_fetchall('select id from ' . tablename('sz_yi_supplier_apply') . ' where uid=' . $value['uid']);

			if (!empty($now_sup_apply_ids)) {
				foreach ($now_sup_apply_ids as $val) {
					pdo_update('sz_yi_supplier_apply', array('uniacid' => $value['uniacid']), array('id' => $val['id']));
				}
			}
		}
	}
}

if (!pdo_fieldexists('sz_yi_adv', 'thumb_pc')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_adv') . ' ADD `thumb_pc` VARCHAR( 255 ) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_notice', 'desc')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_notice') . ' ADD `desc` VARCHAR( 255 ) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_order_goods', 'goods_op_cost_price')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_goods') . ' ADD `goods_op_cost_price` DECIMAL(10,2) DEFAULT \'0.00\';');
}

if (!pdo_fieldexists('sz_yi_store', 'myself_support')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_store') . ' ADD `myself_support` tinyint(1) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_store', 'verity_support')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_store') . ' ADD `verity_support` tinyint(1) DEFAULT \'0\';');
}

if (pdo_tableexists('sz_yi_bonus_level')) {
	if (!pdo_fieldexists('sz_yi_bonus_level', 'msgtitle')) {
		pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_bonus_level') . ' ADD `msgtitle` varchar(100) DEFAULT \'\';');
	}

	if (!pdo_fieldexists('sz_yi_bonus_level', 'msgcontent')) {
		pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_bonus_level') . ' ADD `msgcontent` varchar(255) DEFAULT \'\';');
	}

	if (!pdo_fieldexists('sz_yi_bonus_level', 'status')) {
		pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_bonus_level') . ' ADD `status` tinyint(1) DEFAULT \'0\';');
	}
}

if (pdo_tableexists('sz_yi_bonus')) {
	if (!pdo_fieldexists('sz_yi_bonus', 'sendmonth')) {
		pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_bonus') . ' ADD `sendmonth` tinyint(1) DEFAULT \'0\';');
	}
}

pdo_fetchall('CREATE TABLE IF NOT EXISTS ' . tablename('sz_yi_adpc') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `advname` varchar(50) DEFAULT '',\n  `link` varchar(255) DEFAULT '',\n  `thumb` varchar(255) DEFAULT '',\n  `displayorder` int(11) DEFAULT '0',\n  `enabled` int(11) DEFAULT '0',\n  `thumb_pc` varchar(255) DEFAULT '',\n  `location` varchar(50) DEFAULT '',\n  PRIMARY KEY (`id`),\n  KEY `idx_uniacid` (`uniacid`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

if (!pdo_fieldexists('sz_yi_perm_user', 'username')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_perm_user') . ' ADD `username` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;');
}

if (!pdo_fieldexists('sz_yi_perm_user', 'password')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_perm_user') . ' ADD `password` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;');
}

if (!pdo_fieldexists('sz_yi_exhelper_express', 'uid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_exhelper_express') . ' ADD  `uid`  INT(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_exhelper_senduser', 'uid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_exhelper_senduser') . ' ADD  `uid`  INT(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_exhelper_sys', 'uid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_exhelper_sys') . ' ADD  `uid`  INT(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_af_supplier', 'username')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_af_supplier') . ' ADD `username` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;');
}

if (!pdo_fieldexists('sz_yi_af_supplier', 'password')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_af_supplier') . ' ADD `password` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;');
}

if (!pdo_fieldexists('sz_yi_goods', 'redprice')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `redprice` varchar(50) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_goods_option', 'redprice')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods_option') . ' ADD `redprice` varchar(50) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_order', 'redprice')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `redprice` varchar(50) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_order', 'refundstate')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD  `refundstate` tinyint(3) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order_refund', 'applyprice')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_refund') . ' ADD  `applyprice`  DECIMAL(10,2) DEFAULT \'0.00\';');
}

if (!pdo_fieldexists('sz_yi_order_refund', 'orderprice')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_refund') . ' ADD  `orderprice`  DECIMAL(10,2) DEFAULT \'0.00\';');
}

if (!pdo_fieldexists('sz_yi_order_refund', 'rtype')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_refund') . ' ADD  `rtype` tinyint(1) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order_refund', 'imgs')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_refund') . ' ADD  `imgs` text DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_order_refund', 'refundtime')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_refund') . ' ADD  `refundtime` INT(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order_refund', 'refundaddress')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_refund') . ' ADD  `refundaddress` text DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_order_refund', 'message')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_refund') . ' ADD  `message` text DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_order_refund', 'express')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_refund') . ' ADD  `express` varchar(100) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_order_refund', 'expresscom')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_refund') . ' ADD  `expresscom` varchar(100) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_order_refund', 'expresssn')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_refund') . ' ADD  `expresssn` varchar(100) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_order_refund', 'operatetime')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_refund') . ' ADD  `operatetime` INT(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order_refund', 'sendtime')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_refund') . ' ADD  `sendtime` INT(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order_refund', 'returntime')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_refund') . ' ADD  `returntime` INT(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order_refund', 'rexpress')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_refund') . ' ADD  `rexpress` varchar(100) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_order_refund', 'rexpresscom')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_refund') . ' ADD  `rexpresscom` varchar(100) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_order_refund', 'rexpresssn')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_refund') . ' ADD  `rexpresssn` varchar(100) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_order_refund', 'refundaddressid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_refund') . ' ADD  `refundaddressid` INT(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order_refund', 'endtime')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order_refund') . ' ADD  `endtime` INT(11) DEFAULT \'0\';');
}

pdo_fetchall('CREATE TABLE IF NOT EXISTS ' . tablename('sz_yi_refund_address') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `title` varchar(20) DEFAULT '',\n  `name` varchar(20) DEFAULT '',\n  `tel` varchar(20) DEFAULT '',\n  `mobile` varchar(11) DEFAULT '',\n  `province` varchar(30) DEFAULT '',\n  `city` varchar(30) DEFAULT '',\n  `area` varchar(30) DEFAULT '',\n  `address` varchar(300) DEFAULT '',\n  `isdefault` tinyint(1) DEFAULT '0',\n  `zipcode` varchar(255) DEFAULT '',\n  `content` text,\n  `deleted` tinyint(1) DEFAULT '0',\n  PRIMARY KEY (`id`),\n  KEY `idx_uniacid` (`uniacid`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

if (!pdo_fieldexists('sz_yi_member', 'referralsn')) {
	pdo_fetchall('ALTER TABLE  ' . tablename('sz_yi_member') . ' ADD  `referralsn` VARCHAR( 255 ) NOT NULL');
}

if (!pdo_fieldexists('sz_yi_article_sys', 'article_text')) {
	pdo_fetchall('ALTER TABLE  ' . tablename('sz_yi_article_sys') . ' ADD  `article_text` VARCHAR( 255 ) NOT NULL AFTER  `article_keyword`');
}

if (!pdo_fieldexists('sz_yi_article_sys', 'isarticle')) {
	pdo_fetchall('ALTER TABLE  ' . tablename('sz_yi_article_sys') . ' ADD  `isarticle` TINYINT( 1 ) NOT NULL');
}

if (!pdo_fieldexists('sz_yi_goods', 'pcate1')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `pcate1` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'ccate1')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `ccate1` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'tcate1')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `tcate1` int(11) DEFAULT \'0\';');
}

pdo_fetchall('CREATE TABLE IF NOT EXISTS ' . tablename('sz_yi_system_copyright') . " (\n`id`  int(11) NOT NULL AUTO_INCREMENT ,\n`uniacid`  int(11) NULL DEFAULT NULL ,\n`copyright`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,\n`bgcolor`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' ,\nPRIMARY KEY (`id`),\nINDEX `idx_uniacid` USING BTREE (`uniacid`) \n) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci;");

if (!pdo_fieldexists('sz_yi_article_category', 'm_level')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_article_category') . ' ADD `m_level` INT(11) NOT NULL DEFAULT \'0\'');
}

if (!pdo_fieldexists('sz_yi_article_category', 'd_level')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_article_category') . ' ADD `d_level` INT(11) NOT NULL DEFAULT \'0\'');
}

if (!pdo_fieldexists('sz_yi_order', 'redstatus')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `redstatus` varchar(100) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'nobonus')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `nobonus` tinyint(1) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'returns')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `returns` TEXT DEFAULT \'\';');
}

pdo_fetchall('CREATE TABLE IF NOT EXISTS ' . tablename('sz_yi_return_log') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `mid` int(11) DEFAULT '0',\n  `openid` varchar(255) DEFAULT '',\n  `money` decimal(10,2) DEFAULT '0.00',\n  `status` tinyint(2) DEFAULT '0',\n  `returntype` tinyint(2) DEFAULT '0',\n  `create_time` int(11) DEFAULT '0', \n  PRIMARY KEY (`id`),\n  KEY `idx_uniacid` (`uniacid`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

if (!pdo_fieldexists('sz_yi_coupon', 'supplier_uid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_coupon') . ' ADD `supplier_uid` INT(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order', 'cashier')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `cashier` tinyint(1) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order', 'realprice')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `realprice` decimal(10,2) DEFAULT \'0.00\';');
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

if (pdo_tableexists('sz_yi_return_log')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_return_log') . ' DROP `id`;');
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_return_log') . ' ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);');
}

pdo_fetchall('CREATE TABLE IF NOT EXISTS ' . tablename('sz_yi_banner') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `advname` varchar(50) DEFAULT '',\n  `link` varchar(255) DEFAULT '',\n  `thumb` varchar(255) DEFAULT '',\n  `displayorder` int(11) DEFAULT '0',\n  `enabled` int(11) DEFAULT '0',\n  `thumb_pc` varchar(500) DEFAULT '',\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
pdo_fetchall('CREATE TABLE IF NOT EXISTS ' . tablename('sz_yi_message') . " (\n   `id` int(11) NOT NULL AUTO_INCREMENT,\n  `openid` varchar(255) NOT NULL COMMENT '用户openid',\n  `title` varchar(255) NOT NULL COMMENT '标题',\n  `contents` text NOT NULL COMMENT '内容',\n  `status` set('0','1') NOT NULL DEFAULT '0' COMMENT '0-未读；1-已读',\n  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '日期',\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
pdo_fetchall('CREATE TABLE IF NOT EXISTS ' . tablename('sz_yi_push') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `name` varchar(50) DEFAULT '',\n  `description` varchar(255) DEFAULT NULL,\n  `content` text,\n  `time` int(11) DEFAULT NULL,\n  `status` int(1) DEFAULT '0',\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

if (!pdo_fieldexists('sz_yi_member', 'bindapp')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD `bindapp` tinyint(4) NOT NULL DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_order', 'ordersn_general')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `ordersn_general` varchar(255) NOT NULL DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_order', 'pay_ordersn')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `pay_ordersn` varchar(255) NOT NULL DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'isverifysend')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `isverifysend` tinyint(1) NOT NULL DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'supplier_uid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `supplier_uid` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'isreturn')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `isreturn` tinyint(1) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'isreturnqueue')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `isreturnqueue` tinyint(1) DEFAULT \'0\';');
}

pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' CHANGE `realprice` `realprice` decimal(10,2) DEFAULT \'0\';');

if (!pdo_fieldexists('sz_yi_order', 'address_send')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `address_send` varchar(2000) NOT NULL DEFAULT \'\';');
}

pdo_fetchall('CREATE TABLE IF NOT EXISTS ' . tablename('sz_yi_cashier_store_waiter') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `sid` int(11) DEFAULT NULL,\n  `realname` varchar(255) DEFAULT NULL,\n  `mobile` varchar(255) DEFAULT NULL,\n  `member_id` int(11) DEFAULT NULL,\n  `uniacid` int(11) DEFAULT NULL,\n  `createtime` varchar(255) DEFAULT NULL,\n  `savetime` varchar(255) DEFAULT NULL,\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
$sql = "\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_cashier_order') . " (\n  `order_id` int(11) NOT NULL,\n  `uniacid` int(11) NOT NULL,\n  `cashier_store_id` int(11) NOT NULL,\n  PRIMARY KEY (`order_id`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='收银台商户订单';\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_cashier_store') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) NOT NULL DEFAULT '0',\n  `name` varchar(100) DEFAULT NULL COMMENT '店名',\n  `thumb` varchar(255) NOT NULL,\n  `contact` varchar(100) DEFAULT NULL COMMENT '联系人',\n  `mobile` varchar(30) DEFAULT NULL COMMENT '电话',\n  `address` varchar(500) DEFAULT NULL COMMENT '地址',\n  `member_id` int(11) DEFAULT '0' COMMENT '绑定的会员微信号',\n  `deduct_credit1` decimal(10,2) DEFAULT '0.00' COMMENT '抵扣设置,允许使用的积分百分比',\n  `deduct_credit2` decimal(10,2) DEFAULT '0.00' COMMENT '抵扣设置,允许使用的余额百分比',\n  `settle_platform` decimal(10,2) DEFAULT '0.00' COMMENT '结算比例,平台比例',\n  `settle_store` decimal(10,2) DEFAULT '0.00' COMMENT '结算比例,商家比例',\n  `commission1_rate` decimal(10,2) DEFAULT '0.00' COMMENT '佣金比例,一级分销,消费者在商家用收银台支付后，分销商获得的佣金比例',\n  `commission2_rate` decimal(10,2) DEFAULT '0.00' COMMENT '佣金比例,二级分销',\n  `commission3_rate` decimal(10,2) DEFAULT '0.00' COMMENT '佣金比例,三级分销',\n  `credit1` decimal(10,2) DEFAULT '0.00' COMMENT '消费者在商家支付完成后，获得的积分奖励百分比',\n  `redpack_min` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '最小消费金额，才会发红包',\n  `redpack` decimal(10,2) DEFAULT '0.00' COMMENT '消费者在商家支付完成后，获得的红包奖励百分比',\n  `coupon_id` int(11) NOT NULL DEFAULT '0' COMMENT '优惠卷',\n  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n  `deredpack` tinyint(1) NOT NULL DEFAULT '0' COMMENT '扣除红包金额',\n  `decommission` tinyint(1) NOT NULL DEFAULT '0' COMMENT '扣除佣金金额',\n  `decredits` tinyint(1) NOT NULL DEFAULT '0' COMMENT '扣除奖励余额金额',\n  `creditpack` decimal(10,2) DEFAULT '0.00' COMMENT '消费者在商家支付完成后，获得的余额奖励百分比',\n  `iscontact` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否填写联系人信息',\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;\n\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_cashier_withdraw') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) NOT NULL,\n  `withdraw_no` varchar(255) NOT NULL,\n  `openid` varchar(50) DEFAULT NULL,\n  `cashier_store_id` int(11) NOT NULL,\n  `money` decimal(10,2) NOT NULL,\n  `status` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '提现状态 0 生成 1 成功 2 失败',\n  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='收银台商户提现表';\n";
pdo_fetchall($sql);
pdo_fetchall("\nCREATE TABLE IF NOT EXISTS `ims_sz_yi_af_supplier` (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `openid` varchar(255) CHARACTER SET utf8 NOT NULL,\n  `uniacid` int(11) NOT NULL,\n  `realname` varchar(55) CHARACTER SET utf8 NOT NULL,\n  `mobile` varchar(255) CHARACTER SET utf8 NOT NULL,\n  `weixin` varchar(255) CHARACTER SET utf8 NOT NULL,\n  `productname` varchar(255) CHARACTER SET utf8 NOT NULL,\n  `username` varchar(255) CHARACTER SET utf8 NOT NULL,\n  `password` varchar(255) CHARACTER SET utf8 NOT NULL,\n  `status` tinyint(3) NOT NULL COMMENT '1审核成功2驳回',\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;\nCREATE TABLE IF NOT EXISTS `ims_sz_yi_supplier_apply` (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uid` int(11) NOT NULL COMMENT '供应商id',\n  `uniacid` int(11) NOT NULL,\n  `type` int(11) NOT NULL COMMENT '1手动2微信',\n  `applysn` varchar(255) NOT NULL COMMENT '提现单号',\n  `apply_money` int(11) NOT NULL COMMENT '申请金额',\n  `apply_time` int(11) NOT NULL COMMENT '申请时间',\n  `status` tinyint(3) NOT NULL COMMENT '0为申请状态1为完成状态',\n  `finish_time` int(11) NOT NULL COMMENT '完成时间',\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

if (!pdo_fieldexists('sz_yi_perm_user', 'banknumber')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_perm_user') . ' ADD `banknumber` varchar(255) NOT NULL COMMENT \'银行卡号\';');
}

if (!pdo_fieldexists('sz_yi_perm_user', 'accountname')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_perm_user') . ' ADD `accountname` varchar(255) NOT NULL COMMENT \'开户名\';');
}

if (!pdo_fieldexists('sz_yi_perm_user', 'accountbank')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_perm_user') . ' ADD `accountbank` varchar(255) NOT NULL COMMENT \'开户行\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'supplier_uid')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `supplier_uid` INT NOT NULL COMMENT \'供应商ID\';');
}

if (!pdo_fieldexists('sz_yi_order', 'supplier_uid')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `supplier_uid` INT NOT NULL COMMENT \'供应商ID\';');
}

if (!pdo_fieldexists('sz_yi_order_goods', 'supplier_uid')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order_goods') . ' ADD `supplier_uid` INT NOT NULL COMMENT \'供应商ID\';');
}

if (!pdo_fieldexists('sz_yi_order_goods', 'supplier_apply_status')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order_goods') . ' ADD `supplier_apply_status` tinyint(4) NOT NULL COMMENT \'1为供应商已提现\';');
}

$info = pdo_fetch('select * from ' . tablename('sz_yi_plugin') . ' where identity= "supplier"  order by id desc limit 1');
$result = pdo_fetch('select * from ' . tablename('sz_yi_perm_role') . ' where status1=1');

if (!pdo_fieldexists('sz_yi_category', 'advimg_pc')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_category') . ' ADD `advimg_pc` varchar(255) NOT NULL DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_category', 'advurl_pc')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_category') . ' ADD `advurl_pc` varchar(500) NOT NULL DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_supplier_apply', 'apply_ordergoods_ids')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_supplier_apply') . ' ADD  `apply_ordergoods_ids` text;');
}

$result = pdo_fetchcolumn('select id from ' . tablename('sz_yi_plugin') . ' where identity=:identity', array(':identity' => 'app'));

if (empty($result)) {
	$displayorder_max = pdo_fetchcolumn('select max(displayorder) from ' . tablename('sz_yi_plugin'));
	$displayorder = $displayorder_max + 1;
	$sql = 'INSERT INTO ' . tablename('sz_yi_plugin') . ' (`displayorder`,`identity`,`name`,`version`,`author`,`status`, `category`) VALUES(' . $displayorder . ',\'app\',\'APP客户端\',\'1.0\',\'官方\',\'1\', \'biz\');';
	pdo_fetchall($sql);
}

$sql = "\nCREATE TABLE IF NOT EXISTS `ims_sz_yi_banner` (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `advname` varchar(50) DEFAULT '',\n  `link` varchar(255) DEFAULT '',\n  `thumb` varchar(255) DEFAULT '',\n  `displayorder` int(11) DEFAULT '0',\n  `enabled` int(11) DEFAULT '0',\n  `thumb_pc` varchar(500) DEFAULT '',\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;\n";
pdo_fetchall($sql);
$sql = "\nCREATE TABLE IF NOT EXISTS `ims_sz_yi_message` (\n  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',\n  `openid` varchar(255) NOT NULL COMMENT '用户openid',\n  `title` varchar(255) NOT NULL COMMENT '标题',\n  `contents` text NOT NULL COMMENT '内容',\n  `status` set('0','1') NOT NULL DEFAULT '0' COMMENT '0-未读；1-已读',\n  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '日期',\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;\n";
pdo_fetchall($sql);
$sql = "\nCREATE TABLE IF NOT EXISTS `ims_sz_yi_push` (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `name` varchar(50) DEFAULT '',\n  `description` varchar(255) DEFAULT NULL,\n  `content` text,\n  `time` int(11) DEFAULT NULL,\n  `status` int(1) DEFAULT '0',\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;\n";
pdo_fetchall($sql);

if (!pdo_fieldexists('sz_yi_member', 'bindapp')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD `bindapp` tinyint(4) NOT NULL DEFAULT \'0\';');
}

$plugins = pdo_fetchall('select * from ' . tablename('sz_yi_plugin') . ' order by displayorder asc');
m('cache')->set('plugins', $plugins, 'global');

if (pdo_tableexists('sz_yi_return')) {
	if (!pdo_fieldexists('sz_yi_return', 'last_money')) {
		pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_return') . ' ADD `last_money` DECIMAL(10,2) NOT NULL AFTER `return_money`;');
	}

	if (!pdo_fieldexists('sz_yi_return', 'updatetime')) {
		pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_return') . ' ADD `updatetime` VARCHAR(255) NOT NULL AFTER `create_time`;');
	}

	if (!pdo_fieldexists('sz_yi_return', 'delete')) {
		pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_return') . ' ADD `delete` TINYINT(1) NULL DEFAULT \'0\';');
	}
}

$plugins = pdo_fetchall('select * from ' . tablename('sz_yi_plugin') . ' order by displayorder asc');
m('cache')->set('plugins', $plugins, 'global');

if (!pdo_fieldexists('sz_yi_member', 'credit20')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD `credit20` DECIMAL(10,2) NOT NULL DEFAULT \'0\';');
}

if (!pdo_fieldexists('mc_members', 'credit20')) {
	pdo_fetchall('ALTER TABLE ' . tablename('mc_members') . ' ADD `credit20` DECIMAL(10,2) NOT NULL DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_commission_apply', 'credit20')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_commission_apply') . ' ADD `credit20` DECIMAL(10,2) NOT NULL DEFAULT \'0\';');
}

$sql = "\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_member_transfer_log') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) NOT NULL,\n  `openid` varchar(255) NOT NULL,\n  `tosell_id` int(11) DEFAULT NULL COMMENT '出让人id',\n  `assigns_id` int(11) DEFAULT NULL COMMENT '受让人id',\n  `createtime` int(11) NOT NULL,\n  `status` tinyint(3) NOT NULL COMMENT '-1 失败 0 进行中 1 成功',\n  `money` decimal(10,2) DEFAULT NULL,\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;\n";
pdo_fetchall($sql);

if (pdo_tableexists('sz_yi_bonus_level')) {
	if (!pdo_fieldexists('sz_yi_bonus_level', 'downcountlevel2')) {
		pdo_query('ALTER TABLE ' . tablename('sz_yi_bonus_level') . ' ADD `downcountlevel2` int(11) DEFAULT \'0\';');
	}

	if (!pdo_fieldexists('sz_yi_bonus_level', 'downcountlevel3')) {
		pdo_query('ALTER TABLE ' . tablename('sz_yi_bonus_level') . ' ADD `downcountlevel3` int(11) DEFAULT \'0\';');
	}
}

if (!pdo_fieldexists('sz_yi_article', 'article_state_wx')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_article') . ' ADD `article_state_wx` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_coupon', 'getcashier')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_coupon') . ' ADD `getcashier` tinyint(1) NOT NULL DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_coupon', 'usetype')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_coupon') . ' ADD `usetype` tinyint(1) NOT NULL DEFAULT \'1\';');
}

if (!pdo_fieldexists('sz_yi_coupon', 'cashiersids')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_coupon') . ' ADD `cashiersids` text NULL ;');
}

if (!pdo_fieldexists('sz_yi_coupon', 'cashiersnames')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_coupon') . ' ADD `cashiersnames` text NULL ;');
}

if (!pdo_fieldexists('sz_yi_coupon', 'categoryids')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_coupon') . ' ADD `categoryids` text NULL ;');
}

if (!pdo_fieldexists('sz_yi_coupon', 'categorynames')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_coupon') . ' ADD `categorynames` text NULL ;');
}

if (!pdo_fieldexists('sz_yi_coupon', 'goodsnames')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_coupon') . ' ADD `goodsnames` text NULL ;');
}

if (!pdo_fieldexists('sz_yi_coupon', 'goodsids')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_coupon') . ' ADD `goodsids` text NULL ;');
}

if (!pdo_fieldexists('sz_yi_goods', 'deposit')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `deposit` decimal(10,2) DEFAULT \'0.00\' AFTER `isreturnqueue`;');
}

if (!pdo_fieldexists('sz_yi_goods', 'print_id')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `print_id` INT(11) DEFAULT \'0\' AFTER `deposit`;');
}

if (!pdo_fieldexists('sz_yi_order', 'checkname')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `checkname` varchar(255) DEFAULT \'\' AFTER `ordersn_general`;');
}

if (!pdo_fieldexists('sz_yi_order', 'realmobile')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `realmobile` varchar(255) DEFAULT \'\' AFTER `checkname`;');
}

if (!pdo_fieldexists('sz_yi_order', 'realsex')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `realsex` INT(1) DEFAULT \'0\' AFTER `realmobile`;');
}

if (!pdo_fieldexists('sz_yi_order', 'invoice')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `invoice`  INT(1) DEFAULT \'0\'  AFTER `realsex`;');
}

if (!pdo_fieldexists('sz_yi_order', 'invoiceval')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `invoiceval` INT(1) DEFAULT \'0\' AFTER `invoice`;');
}

if (!pdo_fieldexists('sz_yi_order', 'invoicetext')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `invoicetext` varchar(255) DEFAULT \'\' AFTER `invoiceval`;');
}

if (!pdo_fieldexists('sz_yi_order', 'num')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `num` INT(1) DEFAULT \'0\' AFTER `invoicetext`;');
}

if (!pdo_fieldexists('sz_yi_order', 'btime')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `btime` INT(11) DEFAULT \'0\' AFTER `num`;');
}

if (!pdo_fieldexists('sz_yi_order', 'etime')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `etime` INT(11) DEFAULT \'0\' AFTER `btime`;');
}

if (!pdo_fieldexists('sz_yi_order', 'depositprice')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `depositprice` decimal(10,2) DEFAULT \'0.00\' AFTER `etime`;');
}

if (!pdo_fieldexists('sz_yi_order', 'returndepositprice')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `returndepositprice`  decimal(10,2) DEFAULT \'0.00\' AFTER `depositprice`;');
}

if (!pdo_fieldexists('sz_yi_order', 'depositpricetype')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `depositpricetype` INT(1) DEFAULT \'0\' AFTER `returndepositprice`;');
}

if (!pdo_fieldexists('sz_yi_order', 'room_number')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `room_number` varchar(11) DEFAULT \'\' AFTER `depositpricetype`;');
}

if (!pdo_fieldexists('sz_yi_order', 'roomid')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `roomid` INT(11) DEFAULT \'0\' AFTER `room_number`;');
}

if (!pdo_fieldexists('sz_yi_order', 'order_type')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `order_type`  INT(11) DEFAULT \'0\' AFTER `roomid`;');
}

if (!pdo_fieldexists('sz_yi_order', 'days')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `days`  INT(11) DEFAULT \'0\' AFTER `order_type`;');
}

if (!pdo_fieldexists('sz_yi_commission_level', 'withdraw_proportion')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_commission_level') . ' ADD `withdraw_proportion`  DECIMAL( 10, 2 ) DEFAULT \'0.00\';');
}

if (!pdo_fieldexists('sz_yi_commission_level', 'level')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_commission_level') . ' ADD `level`  INT(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_commission_level', 'downcount')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_commission_level') . ' ADD `downcount`  INT(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_commission_level', 'ordercount')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_commission_level') . ' ADD `ordercount`  INT(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_member', 'check_imgs')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD `check_imgs` text DEFAULT \'\';');
}

pdo_query('update ' . tablename('sz_yi_order') . ' set ordersn_general = ordersn where ordersn_general=""');

if (!pdo_fieldexists('sz_yi_goods', 'pcates')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `pcates` text DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'pcates2')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `pcates2` text DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'ccates')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `ccates` text DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'ccates2')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `ccates2` text DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'tcates')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `tcates` text DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'tcates2')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `tcates2` text DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_cashier_store', 'condition')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_cashier_store') . ' ADD `condition` decimal(10,2) DEFAULT \'0.00\';');
}

if (!pdo_fieldexists('sz_yi_coupon', 'storeids')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_coupon') . ' ADD `storeids` text DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_coupon', 'storenames')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_coupon') . ' ADD `storenames` text DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_coupon', 'getstore')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_coupon') . ' ADD `getstore` tinyint(1) NOT NULL DEFAULT \'0\'');
}

if (!pdo_fieldexists('sz_yi_return_log', 'credittype')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_return_log') . ' ADD `credittype` VARCHAR(60) NOT NULL AFTER `openid`;');
}

if (!pdo_fieldexists('sz_yi_saler', 'salername')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_saler') . ' ADD `salername` VARCHAR(255) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_member', 'bonuslevel')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD `bonuslevel` INT DEFAULT \'0\' AFTER `agentlevel`, ADD `bonus_status` TINYINT(1) DEFAULT \'0\' AFTER `bonuslevel`;');
}

if (!pdo_fieldexists('sz_yi_creditshop_log', 'couponid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_creditshop_log') . ' ADD `couponid` INT(11) DEFAULT \'0\' ;');
}

if (pdo_tableexists('sz_yi_chooseagent')) {
	if (!pdo_fieldexists('sz_yi_chooseagent', 'isopenchannel')) {
		pdo_query('ALTER TABLE ' . tablename('sz_yi_chooseagent') . ' ADD `isopenchannel` tinyint(1) NOT NULL COMMENT \'0关闭1开启\';');
	}

	if (!pdo_fieldexists('sz_yi_chooseagent', 'detail')) {
		pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_chooseagent') . ' ADD `detail` INT(11) DEFAULT \'0\' ;');
	}

	if (!pdo_fieldexists('sz_yi_chooseagent', 'allgoods')) {
		pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_chooseagent') . ' ADD `allgoods` INT(11) DEFAULT \'0\' ;');
	}
}

if (!pdo_fieldexists('sz_yi_coupon', 'supplier_uid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_coupon') . ' ADD  `supplier_uid`  int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_category', 'supplier_uid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_category') . ' ADD  `supplier_uid`  int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'discounttype')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `discounttype` TINYINT DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'discounts2')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `discounts2` TEXT  DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'returns2')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `returns2` TEXT DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'returntype')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `returntype` TINYINT DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'discountway')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `discountway` TINYINT DEFAULT \'0\';');
}

pdo_fetchall('UPDATE ' . tablename('sz_yi_goods') . ' SET  `discounttype` =1,`returntype` =1,`discountway` =1 WHERE discounttype = 0 AND returntype = 0 AND discountway = 0;');
pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_message') . ' CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT \'编号\'');
pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_banner') . ' CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT \'编号\'');
pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_push') . ' CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT \'编号\'');

if (!pdo_fieldexists('sz_yi_member', 'alipay')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD `alipay`  varchar(255) DEFAULT \'\' AFTER `credit20`;');
}

if (!pdo_fieldexists('sz_yi_member', 'alipayname')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD `alipayname`  varchar(255) DEFAULT \'\' AFTER `alipay`;');
}

if (!pdo_fieldexists('sz_yi_commission_apply', 'alipay')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_commission_apply') . ' ADD `alipay`  varchar(255) DEFAULT \'\' AFTER `credit20`;');
}

if (!pdo_fieldexists('sz_yi_commission_apply', 'alipayname')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_commission_apply') . ' ADD `alipayname`  varchar(255) DEFAULT \'\' AFTER `alipay`;');
}

if (!pdo_fieldexists('sz_yi_commission_apply', 'batch_no')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_commission_apply') . ' ADD `batch_no`  varchar(255) DEFAULT \'\' AFTER `alipayname`;');
}

if (!pdo_fieldexists('sz_yi_commission_apply', 'finshtime')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_commission_apply') . ' ADD `finshtime`  int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_member_log', 'batch_no')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_member_log') . ' ADD `batch_no`  varchar(255) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_plugin', 'desc')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_plugin') . ' ADD `desc` varchar(800) NULL');
}

$plugins_desc = array('supplier' => '厂家入驻，平台统一销售', 'commission' => '客户下单后上线获得返现奖励', 'system' => '分销商关系调整、数据管理', 'creditshop' => '积分兑换礼品或抽奖', 'article' => '一键转发，隐形锁粉，赚奖励', 'yunpay' => '微信支付，支付宝，银联，信用卡', 'exhelper' => '快速打印快递单、发货单，一键发货', 'verify' => '线上下单门店提货，配送核销', 'qiniu' => '高效的附件存储方案', 'taobao' => '一键批量导入淘宝商品', 'choose' => '快速购买多件商品', 'tmessage' => '微信无限制模板消息群发', 'coupon' => '设置多种使用范围的优惠券', 'diyform' => '高效灵活收集信息', 'perm' => '让员工各尽其职', 'poster' => '海报锁粉，获得奖励', 'postera' => '限时不限量，高效锁粉', 'designer' => 'DIY店铺首页、专题、导航菜单', 'app' => '苹果+安卓双版本，无限消息推送', 'sale' => '积分、余额抵扣，满额优惠，充值满减', 'return' => '排队全返、订单全返、订单满额返、会员等级返现', 'virtual' => '下单自动发送虚拟卡密', 'ranking' => '消费金额、佣金、积分排行', 'fans' => '解决粉丝头像、昵称获取异常', 'hotel' => '房态、房价管理，酒店、会议、餐饮预订', 'bonus' => '代理级差分红、全球分红、区域分红', 'customer' => 'kehu', 'merchant' => '招募供应商获得销售分红', 'channel' => '虚拟库存，人、货、钱一体化管理', 'cashier' => '能分销、分红、全返，奖励红包的收银台');
$sql = 'select * from ' . tablename('sz_yi_plugin');
$plugin_list = pdo_fetchall($sql);

foreach ($plugin_list as $pl) {
	if (($pl['identity'] == 'cashier') && ($pl['category'] == 0)) {
		$data = array('category' => 'biz');
		pdo_update('sz_yi_plugin', $data, array('identity' => $pl['identity']));
	}

	if (($pl['identity'] == 'choose') && ($pl['category'] == 0)) {
		$data = array('category' => 'biz');
		pdo_update('sz_yi_plugin', $data, array('identity' => $pl['identity']));
	}

	if ($pl['desc'] == '') {
		$data = array('desc' => $plugins_desc[$pl['identity']]);
		pdo_update('sz_yi_plugin', $data, array('identity' => $pl['identity']));
	}
}

if (!pdo_fieldexists('sz_yi_category', 'supplier_uid')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_category') . ' ADD  `supplier_uid`  int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_member_log', 'aging_id')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member_log') . ' ADD `aging_id` int(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_member_log', 'paymethod')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member_log') . ' ADD `paymethod` tinyint(1) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_article_category', 'loveshow')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_article_category') . ' ADD `loveshow` tinyint(1) NOT NULL DEFAULT \'0\'');
}

if (!pdo_fieldexists('sz_yi_article', 'love_money')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_article') . ' ADD  `love_money`  DECIMAL( 10, 2 ) NOT NULL DEFAULT \'0.00\' COMMENT \'事业基金金额\';');
}

if (!pdo_fieldexists('sz_yi_article', 'love_log_id')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_article') . ' ADD  `love_log_id`  int( 11 ) NOT NULL DEFAULT \'0\' COMMENT \'爱心基金记录id\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'love_money')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD  `love_money`  DECIMAL( 10, 2 ) NOT NULL DEFAULT \'0.00\' COMMENT \'事业基金金额\';');
}

pdo_fetchall('CREATE TABLE IF NOT EXISTS ' . tablename('sz_yi_bonus_log') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) DEFAULT '0',\n  `applyid` int(11) DEFAULT '0',\n  `mid` int(11) DEFAULT '0',\n  `commission` decimal(10,2) DEFAULT '0.00',\n  `createtime` int(11) DEFAULT '0',\n  `commission_pay` decimal(10,2) DEFAULT '0.00',\n  PRIMARY KEY (`id`),\n  KEY `idx_uniacid` (`uniacid`),\n  KEY `idx_applyid` (`applyid`),\n  KEY `idx_mid` (`mid`),\n  KEY `idx_createtime` (`createtime`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

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

if (!pdo_fieldexists('sz_yi_goods', 'isopenchannel')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `isopenchannel` tinyint(1) NOT NULL COMMENT \'0关闭1开启\';');
}

if (!pdo_fieldexists('sz_yi_order_goods', 'ischannelpay')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order_goods') . ' ADD `ischannelpay` tinyint(1) NOT NULL COMMENT \'0不是1渠道商采购订单\';');
}

if (!pdo_fieldexists('sz_yi_order', 'iscmas')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `iscmas` INT(11) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_member', 'isagency')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD    `isagency` tinyint(1) DEFAULT \'0\';');
}

$result = pdo_fetch('select * from ' . tablename('sz_yi_perm_role') . ' where status1=1');

if (empty($result)) {
	$sql = "\nINSERT INTO " . tablename('sz_yi_perm_role') . " (`rolename`, `status`, `status1`, `perms`, `deleted`) VALUES\n('供应商', 1, 1, 'shop,shop.goods,shop.goods.view,shop.goods.add,shop.goods.edit,shop.goods.delete,shop.dispatch,shop.dispatch.view,shop.dispatch.add,shop.dispatch.edit,shop.dispatch.delete,order,order.view,order.view.status_1,order.view.status0,order.view.status1,order.view.status2,order.view.status3,order.view.status4,order.view.status5,order.view.status9,order.op,order.op.pay,order.op.send,order.op.sendcancel,order.op.finish,order.op.verify,order.op.fetch,order.op.close,order.op.refund,order.op.export,order.op.changeprice,exhelper,exhelper.print,exhelper.print.single,exhelper.print.more,exhelper.exptemp1,exhelper.exptemp1.view,exhelper.exptemp1.add,exhelper.exptemp1.edit,exhelper.exptemp1.delete,exhelper.exptemp1.setdefault,exhelper.exptemp2,exhelper.exptemp2.view,exhelper.exptemp2.add,exhelper.exptemp2.edit,exhelper.exptemp2.delete,exhelper.exptemp2.setdefault,exhelper.senduser,exhelper.senduser.view,exhelper.senduser.add,exhelper.senduser.edit,exhelper.senduser.delete,exhelper.senduser.setdefault,exhelper.short,exhelper.short.view,exhelper.short.save,exhelper.printset,exhelper.printset.view,exhelper.printset.save,exhelper.dosend,taobao,taobao.fetch', 0);";
	pdo_query($sql);
}
else {
	$gysdata = array('perms' => 'shop,shop.goods,shop.goods.view,shop.goods.add,shop.goods.edit,shop.goods.delete,shop.dispatch,shop.dispatch.view,shop.dispatch.add,shop.dispatch.edit,shop.dispatch.delete,order,order.view,order.view.status_1,order.view.status0,order.view.status1,order.view.status2,order.view.status3,order.view.status4,order.view.status5,order.view.status9,order.op,order.op.pay,order.op.send,order.op.sendcancel,order.op.finish,order.op.verify,order.op.fetch,order.op.close,order.op.refund,order.op.export,order.op.changeprice,exhelper,exhelper.print,exhelper.print.single,exhelper.print.more,exhelper.exptemp1,exhelper.exptemp1.view,exhelper.exptemp1.add,exhelper.exptemp1.edit,exhelper.exptemp1.delete,exhelper.exptemp1.setdefault,exhelper.exptemp2,exhelper.exptemp2.view,exhelper.exptemp2.add,exhelper.exptemp2.edit,exhelper.exptemp2.delete,exhelper.exptemp2.setdefault,exhelper.senduser,exhelper.senduser.view,exhelper.senduser.add,exhelper.senduser.edit,exhelper.senduser.delete,exhelper.senduser.setdefault,exhelper.short,exhelper.short.view,exhelper.short.save,exhelper.printset,exhelper.printset.view,exhelper.printset.save,exhelper.dosend,taobao,taobao.fetch');
	pdo_update('sz_yi_perm_role', $gysdata, array('rolename' => '供应商', 'status1' => 1));
}

if (!pdo_fieldexists('sz_yi_member_transfer_log', 'tosell_current_credit')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_member_transfer_log') . ' ADD `tosell_current_credit` DECIMAL(10,2) NOT NULL AFTER `money`;');
}

if (!pdo_fieldexists('sz_yi_member_transfer_log', 'assigns_current_credit')) {
	pdo_query('ALTER TABLE ' . tablename('sz_yi_member_transfer_log') . ' ADD `assigns_current_credit` DECIMAL(10,2) NOT NULL AFTER `tosell_current_credit`;');
}

if (!pdo_fieldexists('sz_yi_member', 'bank')) {
	pdo_fetchall('ALTER TABLE  ' . tablename('sz_yi_member') . ' ADD  `bank` VARCHAR( 255 ) DEFAULT \'\' COMMENT \'开户行\';');
}

if (!pdo_fieldexists('sz_yi_member', 'bank_num')) {
	pdo_fetchall('ALTER TABLE  ' . tablename('sz_yi_member') . ' ADD  `bank_num` VARCHAR( 100 ) DEFAULT \'\' COMMENT \'银行卡号\';');
}

if (!pdo_fieldexists('sz_yi_cashier_store', 'isreturn')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_cashier_store') . ' ADD `isreturn` tinyint(1) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_goods', 'yunbi_consumption')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `yunbi_consumption` DECIMAL(5,3) NOT NULL AFTER `isopenchannel`;');
}

if (!pdo_fieldexists('sz_yi_goods', 'isyunbi')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `isyunbi` TINYINT(1) NOT NULL DEFAULT \'0\' AFTER `yunbi_consumption`;');
}

if (!pdo_fieldexists('sz_yi_goods', 'yunbi_deduct')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD `yunbi_deduct` DECIMAL(10,2) NOT NULL AFTER `isyunbi`;');
}

if (!pdo_fieldexists('sz_yi_member', 'virtual_currency')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD `virtual_currency` DECIMAL(10,2) NOT NULL AFTER `isagency`;');
}

if (!pdo_fieldexists('sz_yi_member', 'last_money')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD `last_money` DECIMAL(10,2) NOT NULL AFTER `virtual_currency`;');
}

if (!pdo_fieldexists('sz_yi_member', 'updatetime')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD `updatetime` VARCHAR(255) NOT NULL AFTER `last_money`;');
}

if (!pdo_fieldexists('sz_yi_order', 'deductyunbimoney')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `deductyunbimoney` DECIMAL(10,2) NOT NULL AFTER `deductenough`;');
}

if (!pdo_fieldexists('sz_yi_order', 'deductyunbi')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_order') . ' ADD `deductyunbi` DECIMAL(10,2) NOT NULL AFTER `deductyunbimoney`;');
}

pdo_fetchall('CREATE TABLE IF NOT EXISTS ' . tablename('sz_yi_yunbi_log') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) NOT NULL,\n  `mid` int(11) NOT NULL,\n  `openid` varchar(255) NOT NULL,\n  `credittype` varchar(60) NOT NULL,\n  `money` decimal(10,2) NOT NULL,\n  `status` tinyint(2) NOT NULL DEFAULT '0',\n  `returntype` tinyint(2) NOT NULL DEFAULT '0',\n  `create_time` int(11) NOT NULL,\n  `remark` text NOT NULL,\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
echo '完成虚拟币添加数据库！';

?>
