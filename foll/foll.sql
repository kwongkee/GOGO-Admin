-- 用户表
CREATE TABLE `ims_foll_member`(
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `unique_id` VARCHAR(64) NOT NULL,
    `mobile` CHAR(11) NOT NULL,
    `name` VARCHAR(10) DEFAULT '' COMMENT '名称',
    `passwd` VARCHAR(100) DEFAULT '',
    `blance` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '总余额',
    `avatar` VARCHAR(100) DEFAULT '' COMMENT '头像',
    `userSex` TINYINT(2) DEFAULT 2 COMMENT '性别 0女 1男 2未知',
    `token` VARCHAR(100) DEFAULT '' COMMENT '登录验证token',
    `credit_accout` VARCHAR(30) DEFAULT '' COMMENT '信用卡号',
    `Union_account` VARCHAR(30) DEFAULT '' COMMENT '银联',
    `auth_status` TINYINT(2) NOT NULL DEFAULT 0 COMMENT '0:未授权，1已授权',
    `auth_type` VARCHAR(100) DEFAULT '' COMMENT 'cloud,Credit_Card,wx,ali,cloudpay json',
    `CustId` VARCHAR(26) DEFAULT NULL COMMENT '无感签约用户唯一标识',
    `lockMoney` DECIMAL(11,2) NOT NULL DEFAULT 0.00 COMMENT '冻结金额',
    `uniacid` INT(10) NOT NULL DEFAULT 0,
    `openid` VARCHAR(64) NOT NULL DEFAULT 0,
    `pay_user` VARCHAR(20) DEFAULT '' COMMENT '支付账户',
    `pay_passwd` VARCHAR(20) DEFAULT '' COMMENT '支付密码',
    `parking_account` VARCHAR(30) DEFAULT '' COMMENT '停车卡号',
    `CarNo` VARCHAR(20) DEFAULT '' COMMENT '停车卡号',
    `color` VARCHAR(10) DEFAULT '' COMMENT '车票颜色',
    `CertNo` VARCHAR(64) DEFAULT '' COMMENT '证件号',
    `create_time` INT(11) NOT NULL,
    index `index_auth` (`unique_id`,`mobile`,`auth_status`,`openid`)
)ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- CREATE TABLE `ims_foll_wechatdata`(
--     `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
--     `unique_id` VARCHAR(64) NOT NULL,
--     `uniacid` INT(10) NOT NULL DEFAULT 0,
--     `openid` VARCHAR(64) NOT NULL,
--     `pay_user` VARCHAR(20) DEFAULT '' COMMENT '支付账户'
-- )ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- 前台认证表
CREATE TABLE `ims_foll_verified` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(64) NOT NULL,
  `idcard` varchar(20) NOT NULL COMMENT '身份证',
  `driverlicense` varchar(50) DEFAULT '' COMMENT '驾驶证号',
  `CarNo` varchar(20) DEFAULT '' COMMENT '绑定车牌号',
  `CertNo` varchar(50) DEFAULT '' COMMENT '证件号',
  `uname` varchar(10) NOT NULL COMMENT '实名姓名',
  `sex` tinyint(1) DEFAULT NULL,
  `color` varchar(10) DEFAULT NULL COMMENT '车票颜色',
  `addr` varchar(200) DEFAULT NULL COMMENT '地址',
  `deednum` varchar(50) DEFAULT NULL COMMENT '房产证',
  `audit_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 未审核。1审核',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 未通过。1通过',
  `time` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `index_id` (`openid`,`idcard`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;


-- 后台表
CREATE TABLE IF NOT EXISTS `ims_foll_user`(
    `id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uniacid` INT(10) NOT NULL DEFAULT 0,
    `openid` VARCHAR(64) NOT NULL DEFAULT '0',
    `tel` CHAR(11) NOT NULL,
    `username` VARCHAR(20) NOT NULL,
    `role` TINYINT(4) NOT NULL DEFAULT 10 COMMENT '0 超级管理,1 管理员,2 拓展员,3 商户',
    `pid` INT(10) NOT NULL DEFAULT 0 COMMENT '父级id',
    `create_time` int(10) NOT NULL
)ENGINE=Innodb DEFAULT CHARSET=utf8;

-- 拓展员认证表
CREATE TABLE `ims_foll_verified_salesman` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `idcard` varchar(20) NOT NULL COMMENT '身份证号',
  `carNo` varchar(20) NOT NULL DEFAULT '0' COMMENT '收钱卡号',
  `pay_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未付款，1已付款',
  `category` tinyint(2) DEFAULT NULL COMMENT '10 兼职，20 员工，30外包',
  `unit` varchar(100) DEFAULT NULL COMMENT '单位',
  `commission` varchar(20) NOT NULL DEFAULT '0.00' COMMENT '提成%',
  `cost` varchar(20) NOT NULL DEFAULT '0.00' COMMENT '费用',
  `create_time` int(10) NOT NULL,
  `isCheck` tinyint(2) NOT NULL DEFAULT '2' COMMENT '1、已验证，2、未验证',
  `code` varchar(10) DEFAULT NULL COMMENT '身份认证状态、1000查询成功',
  `msg` varchar(30) DEFAULT NULL COMMENT '状态详细',
  `area` varchar(255) DEFAULT NULL COMMENT '所属地区',
  `brithday` int(10) DEFAULT NULL COMMENT '生日',
  `sex` varchar(5) DEFAULT NULL COMMENT '性别',
  `uName` varchar(20) DEFAULT NULL COMMENT '验证人名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;


-- 商家认证表
CREATE TABLE IF NOT EXISTS `ims_foll_verified_business`(
    `id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uid` INT(10) NOT NULL,
    `company_name` VARCHAR(100) NOT NULL COMMENT '企业名称',
    `person_name` VARCHAR(20) NOT NULL COMMENT '企业法人',
    `person_tel` CHAR(11) NOT NULL COMMENT '法人手机',
    `business_class` VARCHAR(10) DEFAULT '' COMMENT '商家分类',
    `credit_code` VARCHAR(64) NOT NULL COMMENT '信用代码',
    `account_type` TINYINT(1) NOT NULL COMMENT '1 企业账户,2 法人账户',
    `account` VARCHAR(64) NOT NULL COMMENT '账户',
    `bank` VARCHAR(20) NOT NULL COMMENT '所属银行',
    `addr` VARCHAR(200) NOT NULL COMMENT '商家地址',
    `isCheck` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '验证状态，2未验证,1已验证',
    `registerNum` varchar(30) DEFAULT NULL COMMENT '工商注册号',
    `establishDate` varchar(20) DEFAULT NULL COMMENT '注册时间',
    `serviceStartTime` DATETIME COMMENT '营业开始时间',
    `serviceEndTime` DATETIME COMMENT '营业结束时间',
    `shopAtive` TINYINT(4) NOT NULL DEFAULT 1 COMMENT '店铺营业状态	1:营业中 0：休息中',
    `shopName` VARCHAR(100) DEFAULT '' COMMENT '门店名称',
    `shopTel` VARCHAR(20) DEFAULT '' COMMENT '门店电话',
    `isInvoice` TINYINT(2) NOT NULL DEFAULT 0 COMMENT '	能否开发票	1:能 0:不能',
    `invoiceRemarks` VARCHAR(255) DEFAULT '' COMMENT '发票说明',
    `create_time` INT(10) NOT NULL
)ENGINE=Innodb DEFAULT CHARSET=utf8;


-- 店铺分类表
-- CREATE TABLE `ims_shops_cats`(
--     `id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,

-- )

-- 总订单表
 CREATE TABLE `ims_foll_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ordersn` varchar(64) NOT NULL COMMENT '订单id',
  `user_id` varchar(64) NOT NULL COMMENT '用户id',
  `business_id` int(10) NOT NULL COMMENT '商家id',
  `uniacid` int(10) NOT NULL COMMENT '公众号id',
  `application` varchar(10) NOT NULL COMMENT '应用 停车：parking，商城：shop，自助：food，预约：reservation，分时：fenshi',
  `goods_name` varchar(100) NOT NULL COMMENT '商品名称',
  `goods_price` decimal(10,2) NOT NULL COMMENT '商品价格',
  `pay_type` varchar(10) DEFAULT '' COMMENT '支付类型',
  `pay_status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0未支付，1已支付，2支付失败',
  `pay_time` int(10) NOT NULL DEFAULT '0' COMMENT '支付时间',
  `path_oid` int(10) NOT NULL DEFAULT '0' COMMENT '父级订单id',
  `family_Paystatus` tinyint(2) DEFAULT '3' COMMENT '0未支付，1已支付，2支付失败 3.未发起代付',
  `pay_account` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠后的金额',
  `pay_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '代付金额',
  `invoice_iskp` tinyint(3) NOT NULL DEFAULT '0' COMMENT '发票开票：0未开票的订单，1已开票，2开票失败',
  `body` varchar(100) DEFAULT '' COMMENT '商品描述：停车服务',
  `returnUrl` varchar(255) DEFAULT '' COMMENT '前端返回URL，同步返回',
  `business_name` varchar(100) NOT NULL COMMENT '商家名称',
  `create_time` int(10) NOT NULL,
  `total` decimal(10,2) DEFAULT NULL COMMENT '总金额',
  `balance_status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0未使用，1使用',
  `card_status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0未使用，1使用',
  `couponid` int(11) NOT NULL DEFAULT 0 COMMENT '优惠券id',
  `deleted` tinyint(2) NOT NULL DEFAULT 0 COMMENT '假删除，0 NO 1 YES',
  `nickname` varchar(20) DEFAULT null  COMMENT '昵称',
  `isRefund` tinyint(2) NOT NULL DEFAULT 0 COMMENT '是否退款	0:否 1：是,2待补',
  PRIMARY KEY (`id`),
  KEY `index_order` (`ordersn`,`user_id`,`uniacid`,`application`,`pay_status`,`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- 停车订单
CREATE TABLE `ims_parking_order`(
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `ordersn` VARCHAR(64) NOT NULL COMMENT '订单id',
    `CarNo` varchar(20) DEFAULT '' COMMENT '车牌编号',
    `number` varchar(10) NOT NULL COMMENT '车位编号',
    `starttime` int(11) unsigned NOT NULL COMMENT '进车时间',
    `endtime` int(11) unsigned NOT NULL COMMENT '结束时间',
    `moncard` tinyint(1) DEFAULT 0 COMMENT '0，不使用月卡，1使用',
    `duration` varchar(10) DEFAULT '0' COMMENT '总使用时间分',
    `status` CHAR(3) NOT NULL DEFAULT '已停车' COMMENT '已停车，正计费,已出账，未结算，已结算',
    `charge_type` TINYINT(2) DEFAULT 1 COMMENT '0预付费 1 后付费'
)ENGINE=Innodb DEFAULT CHARSET=utf8;

-- 自助订单
CREATE TABLE `ims_self_order`(
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `ordersn` VARCHAR(64) NOT NULL,
    `number` int(10) NOT NULL DEFAULT 0 COMMENT '总数量'
)ENGINE=Innodb DEFAULT CHARSET=utf8;



-- 优惠券
-- CREATE TABLE `ims_foll_coupon`(
--     `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
--     `uniacid` INT(10) NOT NULL DEFAULT 0,
--     `business_id` INT(10) DEFAULT 0 COMMENT '商家id',
--     `code` VARCHAR(30) NOT NULL COMMENT '优惠券代码',
--     `coupon_name` VARCHAR(30) NOT NULL COMMENT '优惠券名称',
--     `getmax` VARCHAR(10) NOT NULL DEFAULT 1 COMMENT '最大领取张数',
--     `coupon_type` TINYINT(3) DEFAULT '0' COMMENT '优惠方式 0 立减 1 返利',
--     `enough` DECIMAL(10,2) DEFAULT '0.00' COMMENT '使用条件 满多少使用',
--     `s_time` int(11) DEFAULT '0' COMMENT '开始时间',
--     `e_time` int(11) DEFAULT '0' COMMENT '过期结束时间',
--     `amount` DECIMAL(10,2) DEFAULT '0.00' COMMENT '面额',
--     `total` INT(11) DEFAULT 0 COMMENT '发放卡数',
--     `status` TINYINT(3) DEFAULT 0 COMMENT '0正常，1过期,2禁用',
--     `respdesc` TEXT COMMENT '推送说明',
--     `resptitle` VARCHAR(255) DEFAULT '' COMMENT '推送标题',
--     `coupan_class` INT(10) NOT NULL COMMENT '分类id',
--     `application` VARCHAR(10) NOT NULL COMMENT '应用',
--     `discount_rate` VARCHAR(10) NOT NULL DEFAULT 0 COMMENT "折扣率",
--     `fees` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '发放手续费',
--     `create_time` INT(10) NOT NULL
-- )ENGINE=Innodb DEFAULT CHARSET=utf8;

/*优惠券*/
create table  `ims_foll_coupon`(
  `id`  int(10) unsigned not null auto_increment primary key ,
  `uniacid` int(10) not null default 0,
  `c_code` varchar(64)  not null comment '卡卷代码',
  `c_name` varchar(100) not null comment '卡卷名称',
  `c_business` varchar(100) not null comment '适用商户',
  `c_application`  varchar(100) not null comment '适用业务',
  `get_max` int(6) not null default 1 comment '领用限制',
  `use_type` tinyint(1) not null default 2 comment '1：主动使用，2：被动使用',
  `s_time`  int(10) not null comment '使用开始',
  `e_time` int(10) not null comment '使用结束时间',
  `c_type` tinyint(2) not null comment '使用条件1.满X元使用；2：每X元适用',
  `enough` decimal(10,2) not null default 0.00 comment '条件金额',
  `c_amount` decimal(10,2) not null default 0.00 comment '可抵扣优惠金额',
  `c_total` int(10) not null comment '发放数量',
  `remark`  varchar(255) not null comment '备注',
  `issued_type` tinyint(3) not null comment '1.按发行金额X%,2,按发行数量：Y元/张,3：收取一次性费用',
  `issued_price` decimal(10,2) not null default 0.00 comment '发行收费标准根据收取类型条件',
  `apply_status`  tinyint(1) not null default 2 comment '申请状态0:完成,1:未提交，2：已提交，3:已关闭',
  `check_status` tinyint(1) not null default 0 comment  '审核状态0:待审核。1：已通过，2:拒绝',
  `c_status`  tinyint(1) not null default 1 comment '卡卷状态，1有效，2核销,3失效',
  `platform_sett` tinyint(1) not null default 0 comment '平台结算0未结算，1已结算 ',
  `pay_status` tinyint(1) not null default 0 comment '支付状态，0未支付，1已支付，2支付失败',
  `pay_totals`  decimal(10,2) not null default 0.00 comment '支付金额',
  `order_id`  varchar(100) not null default 0 comment '订单编号',
  `create_time` int(10) not null
)ENGINE=Innodb DEFAULT CHARSET=utf8;

/*领取表*/
create table `ims_foll_receive_coupon`(
  `user_id` varchar(64) not null comment '用户id',
  `c_id` int(10) not null comment '优惠券表id',
  `count` int(10) not null comment '领用数量',
  `status` tinyint(1) not null default 1 comment '1有效,2 核销,3失效',
  `create_time` int(10) not null comment '领用时间'
)ENGINE=Innodb DEFAULT CHARSET=utf8;

/*优惠券核销表*/
create table `ims_foll_coupon_cancel`(
  `id` int(10) unsigned not null auto_increment primary key,
  `user_id` varchar(100) not null,
  `c_id` int(10) not null comment '优惠券表id',
  `receive_time` int(10) not null comment '使用时间',
  `receive_merchants` int(10) not null comment '核销商户',
  `c_name` varchar(100) not null comment '卡卷名称',
  `f_merchants` int(10) not null comment '发行商户',
  `receive_type` tinyint(1) not null comment '核销方式',
  `receive_order` varchar(100) not null comment '核销订单',
  `deductible_money` decimal(10,2) not null comment '抵扣金额',
  `create_time` int(10) not null
)ENGINE=Innodb DEFAULT CHARSET=utf8;

/*结算管理*/
-- create table `﻿ims_foll_coupon_sett_manage`(
--   `id` int(10) unsigned not null auto_increment primary key,
-- )


-- 优惠券领取表
-- CREATE TABLE `ims_foll_receivecoupon`(
--     `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
--     `user_id` VARCHAR(64) NOT NULL,
--     `uniacid` INT(10) NOT NULL DEFAULT 0,
--     `business_id` INT(10) NOT NULL COMMENT '商家id',
--     `card_id` INT(10) NOT NULL COMMENT '优惠券表id',
--     `application` VARCHAR(10) NOT NULL COMMENT '应用',
--     `status` TINYINT(2) NOT NULL DEFAULT 1 COMMENT '1未使用，2，已使用,3已过期',
--     `create_time` INT(10) NOT NULL
-- )ENGINE=Innodb DEFAULT CHARSET=utf8;

-- 广告营销表
CREATE TABLE `ims_foll_advertising`(
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uniacid` INT(10) NOT NULL DEFAULT 0,
    `business_id` INT(10) NOT NULL COMMENT '商家id',
    `image` TEXT NOT NULL COMMENT '广告图片json,key图片名:valurl',
    `position` TINYINT(2) NOT NULL COMMENT '1:轮播，2:瀑布流',
    `s_time` INT(10) NOT NULL COMMENT '投放开始时间',
    `e_time` INT(10) NOT NULL COMMENT '投放结束时间',
    `money` DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT '每天基本广告流量费',
    `total_money` DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT '计算流量费总金额',
    `status` TINYINT(3) NOT NULL DEFAULT 10 COMMENT '10:未审批，20:已审批',
    `expiration` TINYINT(2) NOT NULL DEFAULT 0 COMMENT '广告:0:未过期,1:已过期',
    `type` VARCHAR(20) NOT NULL COMMENT '类别',
    `create_time` INT(10) NOT NULL
)ENGINE=Innodb DEFAULT CHARSET=utf8;

-- 广告点击率->缓存+数据库同步//写进缓存计算好更新表
CREATE TABLE `ims_foll_advertisingclick_data`(
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `gid` INT(10) NOT NULL COMMENT '广告表id',
    `num` INT(10) NOT NULL DEFAULT 0 COMMENT '点击数',
    `num_day` VARCHAR(10) NOT NULL COMMENT '记录每天数据20170301',
    `multiple` INT(3) NOT NULL DEFAULT 0 COMMENT '计算倍数',
    `rate`  VARCHAR(3) NOT NULL DEFAULT 10 COMMENT '每天计算的增长率10%'
)ENGINE=Innodb DEFAULT CHARSET=utf8;


-- 商家开通功能表

CREATE TABLE `ims_foll_features`(
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uniacid` INT(10) NOT NULL,
    `business_id` INT(10) NOT NULL,
    `features_name` VARCHAR(10) NOT NULL COMMENT '功能名称'
)ENGINE=Innodb DEFAULT CHARSET=utf8;


-- 余额收支明细
CREATE TABLE IF NOT EXISTS `ims_money_fullback_log`(
    `id` INT(11)  NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT(11) NOT NULL COMMENT '用户id',
    `uniacid` INT(11) NOT NULL DEFAULT 0 COMMENT '商家公众号id',
    `business_id` INT(10) NOT NULL COMMENT '商家id',
    `orderid` VARCHAR(64) NOT NULL COMMENT '订单id',
    `change_money` DECIMAL(10,2) NOT NULL COMMENT '变动的钱',
    `action_status` TINYINT(2) NOT NULL COMMENT '动作(10充值,20支出,30返利)',
    `application` VARCHAR(10) NULL DEFAULT '' COMMENT '应用',
    `remarks` VARCHAR(20) NOT NULL COMMENT '备注',
    `operating_time` int(10) NOT NULL
)ENGINE=Innodb DEFAULT CHARSET=utf8;


/*返利流水表*/
CREATE TABLE IF NOT EXISTS `ims_foll_rebate_water`(
    `id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(64) NOT NULL,
    `business_id` INT(10) NOT NULL COMMENT '商家id',
    `old` VARCHAR(32) NOT NULL DEFAULT 0 COMMENT '流水号',
    `uniacid`  INT(10) NOT NULL COMMENT '商家公众号id',
    `goods` VARCHAR(20) NOT NULL COMMENT '商品名称',
    `goods_price` DECIMAL(10,2) NOT NULL COMMENT '商品金额',
    `rebate_money` DECIMAL(10,2) NOT NULL COMMENT '返利金额',
    `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 没划拨，1划拨',
    `body` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '备注',
    `create_time` INT(10) NOT NULL
)ENGINE=Innodb DEFAULT CHARSET=utf8;

-- 钱包表
CREATE TABLE `ims_foll_wallet`(
    `id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(64) NOT NULL,
    `business_id` INT(10) NOT NULL COMMENT '商家id',
    `uniacid`  INT(10) NOT NULL COMMENT '商家公众号id',
    `money` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '商家总的返利金额',
    `up_time` INT(10) NOT NULL DEFAULT 0 COMMENT '更新时间',
    `create_time` INT(10) NOT NULL
)ENGINE=Innodb DEFAULT CHARSET=utf8;

-- 公司
create table ims_foll_company(
	`id` INT(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`uniacid` INT(10) DEFAULT 0 COMMENT '公众号id',
    `business_id` INT(10) NOT NULL DEFAULT 0 COMMENT '商家id',
    `name` VARCHAR(50) NOT NULL COMMENT '公司名',
    `project_name` VARCHAR(100) NOT NULL COMMENT '项目名称',
    `short_title` VARCHAR(100) NOT NULL COMMENT '企业简称',
    `tel` CHAR(11) NOT NULL COMMENT '电话',
	`num` INT(11) DEFAULT 0 COMMENT '车位数量'
)ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 商品分类表
CREATE TABLE `ims_foll_cats`(
    `catId` INT(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `parentId` INT(11) NOT NULL DEFAULT 0 COMMENT '父级id',
    `catName` VARCHAR(20) NOT NULL COMMENT '分类名称'
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 商品表
CREATE TABLE `ims_foll_goods`(
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uniacid` INT(10) NOT NULL,
    `business_id` INT(10) NOT NULL COMMENT '商家id',
    `goodsSn` VARCHAR(20) NOT NULL COMMENT '商品编号',
    `goods_name` VARCHAR(255) NOT NULL COMMENT '商品名',
    `goods_price` DECIMAL(10,2) NOT NULL COMMENT '商品价格',
    `image` TEXT NOT NULL COMMENT '商品图片以及url缩略图等，json格式存进',
    `goodsStock` INT(11) NOT NULL DEFAULT 0 COMMENT '商品总库存',
    `saleCount` INT(11) DEFAULT 0 COMMENT '销售量',
    `isBook` TINYINT(4) NOT NULL DEFAULT 00 COMMENT '是否预订00:否，01，是',
    `bookQuantity` INT(11) NOT NULL DEFAULT 0 COMMENT '预订量',
    `goodsUnit` CHAR(10) NOT NULL COMMENT '单位',
    `goodsSpec` TEXT DEFAULT NULL COMMENT '规格',
    `isSale` TINYINT(4) NOT NULL DEFAULT 1 COMMENT '是否上架0:不上架 1:上架',
    `isBest` TINYINT(4) NOT NULL DEFAULT 1 COMMENT '是否精品0:否 1:是',
    `isHot` TINYINT(4) NOT NULL DEFAULT 1 COMMENT '是否热销产品	0:否 1:是',
    `isNew` TINYINT(4) NOT NULL DEFAULT 1 COMMENT '	是否新品	0:否 1:是',
    `isShopRecomm` TINYINT(4) NOT NULL DEFAULT 0 COMMENT '是否店铺推荐	0:不推荐 1：推荐',
    `recommDesc` VARCHAR(255) DEFAULT '' COMMENT '促销信息',
    `goodsCatId1` INT(11) NOT NULL COMMENT '顶级商品分类ID',
    `goodsCatId2` INT(11) NOT NULL COMMENT '第二商品分类ID',
    `goodsCatId3` INT(11) NOT NULL COMMENT '第三商品分类ID',
    `goodsDesc` text NOT NULL COMMENT '商品描述',
    `isIndexRecomm` TINYINT(4) NOT NULL DEFAULT 0 COMMENT '是否首页推荐0否，1是',
    `isInnerRecomm` TINYINT(4) NOT NULL DEFAULT 0 COMMENT '是否内页推荐0 否，1是',
    `goodsStatus` TINYINT(4) NOT NULL DEFAULT 0 COMMENT '商品状态	-1:禁售 0:未审核 1:已审核',
    `saleTime` DATETIME COMMENT '上架时间',
    `statusRemarks` VARCHAR(255) DEFAULT '' COMMENT '状态说明	一般用于说明拒绝原因',
    `createTime` INT(11) NOT NULL COMMENT '创建时间',
    `commission` INT(11) DEFAULT 0 COMMENT '佣金'
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 订单投诉表
CREATE TABLE `ims_foll_order_complains`(
    `id` INT(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `orderId` INT(11),
    `complainType` TINYINT(4) NOT NULL DEFAULT 1 COMMENT '投诉类型	1:承诺的没有做到 2:未按约定时间发货 3:未按成交价格进行交易 4:恶意骚扰',
    `complainTargetId` INT(11) NOT NULL COMMENT '投诉人ID	会员Id',
    `respondTargetId` INT(11) NOT NULL COMMENT '应诉人ID	店铺ID',
    `needRespond` TINYINT(4) NOT NULL DEFAULT 0 COMMENT '是否需要应付	0:不需要 1:需要',
    `deliverRespondTime` DATETIME COMMENT '移交应诉时间',
    `complainContent` TEXT COMMENT '投诉内容',
    `complainStatus` TINYINT(4) NOT NULL DEFAULT 0 COMMENT '投诉状态	0:新投诉 1:转给应诉人 2:应诉人回应 3:等待仲裁 4:已仲裁',
    `complainTime` INT(11) NOT NULL COMMENT '创建时间',
    `respondContent` TEXT DEFAULT NULL COMMENT '应诉内容',
    `respondAnnex` VARCHAR(255) DEFAULT '' COMMENT '应诉附件',
    `respondTime` DATETIME DEFAULT NULL COMMENT '应诉时间',
    `finalResult` TEXT DEFAULT NULL COMMENT '仲裁结果',
    `finalResultTime` DATETIME DEFAULT NULL COMMENT '仲裁时间',
    `finalHandleStaffId` INT(10) DEFAULT 0 COMMENT '仲裁id'
)ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 车位信息表



-- 公告表

CREATE TABLE `ims_foll_announcement`(
    `id` INT(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(25) NOT NULL COMMENT '标题',
    `msg` varchar(255) NOT NULL COMMENT '内容',
    `create_time` int(11) NOT NULL
)ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*
用户异常表
 */







/*
泊位管理后台
 */

CREATE TABLE `ims_foll_business_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '管理员自增ID',
  `uniacid` int(11) NOT NULL COMMENT '绑定商家表id',
  `user_name` varchar(255) DEFAULT NULL COMMENT '用户名',
  `user_mobile` char(11)  NOT NULL COMMENT '用户手机号码',
  `user_password` varchar(255) DEFAULT NULL COMMENT '管理员的密码',
  `user_nicename` varchar(255) DEFAULT NULL COMMENT '管理员的简称',
  `user_status` int(11) DEFAULT '1' COMMENT '用户状态 0：禁用； 1：正常 ；',
  `user_email` varchar(255) DEFAULT '' COMMENT '邮箱',
  `last_login_ip` varchar(16) DEFAULT NULL COMMENT '最后登录ip',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
  `create_time` datetime DEFAULT NULL COMMENT '注册时间',
  `role` varchar(255) DEFAULT NULL COMMENT '角色ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='后台管理员表';


CREATE TABLE `ims_foll_business_authrole` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL COMMENT '角色名称',
  `pid` smallint(6) DEFAULT '0' COMMENT '父角色ID',
  `status` tinyint(1) unsigned DEFAULT NULL COMMENT '状态',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `listorder` int(3) NOT NULL DEFAULT '0' COMMENT '排序字段',
  PRIMARY KEY (`id`),
  KEY `parentId` (`pid`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='角色表';


CREATE TABLE `ims_foll_business_authroleuser` (
  `role_id` int(11) unsigned DEFAULT '0' COMMENT '角色 id',
  `user_id` int(11) DEFAULT '0' COMMENT '用户id',
  KEY `group_id` (`role_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户角色对应表';


CREATE TABLE `ims_foll_business_authrule` (
  `menu_id` int(11) NOT NULL COMMENT '后台菜单 ID',
  `module` varchar(20) NOT NULL COMMENT '规则所属module',
  `type` varchar(30) NOT NULL DEFAULT '1' COMMENT '权限规则分类，请加应用前缀,如admin_',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '规则唯一英文标识,全小写',
  `url_param` varchar(255) DEFAULT NULL COMMENT '额外url参数',
  `title` varchar(20) NOT NULL DEFAULT '' COMMENT '规则中文描述',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否有效(0:无效,1:有效)',
  `rule_param` varchar(300) NOT NULL DEFAULT '' COMMENT '规则附加条件',
  `nav_id` int(11) DEFAULT '0' COMMENT 'nav id',
  PRIMARY KEY (`menu_id`),
  KEY `module` (`module`,`status`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='权限规则表';


CREATE TABLE `ims_foll_business_menu` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `parent_id` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '父级ID',
  `app` char(20) NOT NULL COMMENT '应用名称app',
  `model` char(20) NOT NULL COMMENT '控制器',
  `action` char(20) NOT NULL COMMENT '操作名称',
  `url_param` char(50) NOT NULL COMMENT 'url参数',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '菜单类型  1：权限认证+菜单；0：只作为菜单',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态，1显示，0不显示',
  `name` varchar(50) NOT NULL COMMENT '菜单名称',
  `icon` varchar(50) NOT NULL COMMENT '菜单图标',
  `remark` varchar(255) NOT NULL COMMENT '备注',
  `list_order` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '排序ID',
  `rule_param` varchar(255) NOT NULL COMMENT '验证规则',
  `nav_id` int(11) DEFAULT '0' COMMENT 'nav ID ',
  `request` varchar(255) NOT NULL COMMENT '请求方式（日志生成）',
  `log_rule` varchar(255) NOT NULL COMMENT '日志规则',
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `model` (`model`),
  KEY `parent_id` (`parent_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='后台菜单表';

 CREATE TABLE `ims_foll_business_authaccess` (
  `role_id` mediumint(8) unsigned NOT NULL COMMENT '角色',
  `rule_name` varchar(255) NOT NULL COMMENT '规则唯一英文标识,全小写',
  `type` varchar(30) DEFAULT NULL COMMENT '权限规则分类，请加应用前缀,如admin_',
  `menu_id` int(11) DEFAULT NULL COMMENT '后台菜单ID',
  KEY `role_id` (`role_id`),
  KEY `rule_name` (`rule_name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='权限授权表';

CREATE TABLE `ims_foll_business_rwaccess` (
  `role_id` int(11) unsigned DEFAULT '0' COMMENT '角色 id',
  `read` tinyint(2) NOT NULL DEFAULT 0 COMMENT '0不可读，1可读',
  `write` tinyint(2) NOT NULL DEFAULT 0 COMMENT '0不可写，1可写'
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='读写权限授权表';


CREATE TABLE `ims_foll_business_actionlog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '执行用户id',
  `action_ip` int(20) NOT NULL COMMENT '执行行为者ip',
  `log` longtext NOT NULL COMMENT '日志备注',
  `log_url` varchar(255) NOT NULL COMMENT '执行的URL',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行行为的时间',
  `username` varchar(255) NOT NULL COMMENT '执行者',
  `title` varchar(255) NOT NULL COMMENT '标题',
  PRIMARY KEY (`id`),
  KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='行为日志表';

CREATE TABLE  `ims_parking_api_url`(
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uniacid` int(10) NOT NULL COMMENT '所属公众号',
  `url`   varchar(100) NOT NULL COMMENT '接口地址',
  `remark` varchar (100) NOT NULL COMMENT '说明',
  `alias`   varchar (20) NOT NULL COMMENT '别名'
   PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8  COMMENT='api接口地址';


create table `ims_parking_card_type` (
  `id` int(10) unsigned not null auto_increment primary key ,
  `uniacid` int(10) not null comment '公众号id',
  `card_name` varchar(100) not null comment '名称',
  `use_time` varchar(6) not null comment '可用天数',
  `card_status` tinyint(1) not null default 0 comment '状态：0启用，1禁用',
  `card_money` decimal (19,2) not null default 0.00 comment '购买金额',
  `card_type` tinyint(1) not null default 1 comment '1：全日，2：夜间，3：白天',
  `receive_type` tinyint(1) not null comment '领取类型1:购买，0：免费',
  `create_at` int(10) not null comment '时间'
)engine=InnoDB default charset=utf8 comment='月卡类型表';

create table `ims_parking_card_issue` (
  `id` int(10) unsigned not null auto_increment primary key ,
  `uniacid` int(10) not null,
  `type_id` int(10) not null comment '类型表id',
  `period_start` varchar(10) not null comment '开始时间段',
  `period_end`  varchar(10) not null comment '结束时间段',
  `park_start` varchar(10)   comment '适用车位段开始',
  `park_end`  varchar(10)   comment '适用车位段结束',
  `lowest_start` int(10) not null comment '最低购买开始时间',
  `lowest_end`  int(10) not null comment '最低购买结束时间',
  `discount_start` int(10) not null comment '购买月卡折扣开始时间',
  `discount_end` int(10) not null comment '购买月卡折扣到期时间',
  `c_discount`  int(10) not null comment '月卡购买折扣',
  `sell_count`  int(10) not null default 0 comment '卖总数',
  `c_note`  varchar(255) comment '说明',
  `create_at` int(10) not null
)engine=InnoDB default charset=utf8 comment='发放表';

create table `ims_parking_card_receives`(
  `id` int(10) unsigned not null auto_increment primary key ,
  `user_id` varchar(64) not null,
  `issue_id` int(10) not null comment '发放表id',
  `pay_time` int(10) null comment '支付时间',
  `month_num` varchar(10) not null comment '购买月数',
  `pay_status` tinyint(1) not null default 0 comment '支付状态0未支付，1已支付',
  `card_status` tinyint(1) not null default 0 comment '失效。0：未到期，1已过期'
)engine=InnoDB default charset=utf8 comment='月卡领取表';


 CREATE TABLE `ims_foll_goodsreglist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `head_id` int(10) NOT NULL COMMENT '头id',
  `Seq` int(3) NOT NULL COMMENT '商品序号',
  `EntGoodsNo` varchar(20) DEFAULT NULL COMMENT '企业的商品货号，不可重复',
  `CIQGoodsNo` varchar(36) DEFAULT NULL COMMENT '检验检疫商品备案编号',
  `CusGoodsNo` varchar(50) DEFAULT NULL COMMENT '海关正式备案编号',
  `EmsNo` varchar(255) DEFAULT NULL COMMENT '账册号',
  `ItemNo` varchar(255) DEFAULT NULL COMMENT '保税账册里的项号',
  `ShelfGName` varchar(255) NOT NULL COMMENT '在电商平台上的商品名称',
  `NcadCode` varchar(8) NOT NULL COMMENT '商品综合分类表(NCAD)',
  `HSCode` varchar(10) NOT NULL COMMENT 'HS编码',
  `BarCode` varchar(20) DEFAULT NULL COMMENT '商品条形码',
  `GoodsName` varchar(255) NOT NULL COMMENT '商品名称',
  `GoodsStyle` varchar(255) NOT NULL COMMENT '商品规格',
  `Brand` varchar(50) NOT NULL COMMENT '商品品牌',
  `GUnit` varchar(3) NOT NULL COMMENT '计量单位',
  `StdUnit` varchar(3) NOT NULL COMMENT '第一法定计量单位',
  `SecUnit` varchar(3) DEFAULT NULL COMMENT '第二法定计量单位',
  `RegPrice` decimal(19,5) NOT NULL COMMENT '单价',
  `GiftFlag` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0-是，1-否，默认否',
  `OriginCountry` varchar(3) NOT NULL COMMENT '原产国',
  `Quality` varchar(100) NOT NULL COMMENT '商品品质及说明',
  `QualityCertify` varchar(100) DEFAULT NULL COMMENT '品质证明说明',
  `Manufactory` varchar(255) NOT NULL COMMENT '生产厂家或供应商',
  `NetWt` decimal(19,5) NOT NULL COMMENT '净重',
  `GrossWt` decimal(19,5) NOT NULL COMMENT '毛重',
  `Notes` varchar(1000) DEFAULT NULL COMMENT '备注',
  `CIQGRegStatus` char(1) DEFAULT NULL COMMENT 'C-成功备案，成功修改，成功取消备案；N-备案不成功',
  `CIQNotes` varchar(1000) DEFAULT NULL COMMENT '国检审核备注',
  `OpType` tinyint(1) DEFAULT NULL COMMENT '1-新增；2-变更；3-删除',
  `OpTime` datetime DEFAULT NULL COMMENT '操作时间',
  `DeclEntNo` varchar(20) DEFAULT NULL COMMENT '对应跨境平台备案后所获得的企业编号',
  `EPortGoodsNo` varchar(60) DEFAULT NULL COMMENT '商品备案申请号',
  PRIMARY KEY (`id`),
  KEY `index_list` (`HSCode`,`GoodsStyle`,`Brand`,`OriginCountry`,`Manufactory`,`GrossWt`) USING HASH
  ) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8 COMMENT='商品备案信息记录表'



create table `ims_foll_order_electmp`(
`id` int(11) NOT NULL AUTO_INCREMENT,
`WaybillNo` varchar(100) not null comment '物流单号',
`is_queue` tinyint(3) not  null default 0 comment '0待处理，1已处理，2处理失败',
`h_conten` longtext not  null comment '订单头内容',
`ord_body` longtext not null  comment '订单内容'
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='订单申报队列存放表';


CREATE TABLE `ims_foll_elec_order_detail` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `head_id` int(10) NOT NULL,
  `EntOrderNo` varchar(60) NOT NULL COMMENT '交易平台原始id',
  `goodsNo` varchar(255) NOT NULL COMMENT '商品编号',
  `GoodsName` varchar(250) NOT NULL COMMENT '商品名称',
  `OrderStatus` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0-订单确认 1-订单完成 2-订单取消',
  `PayStatus` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0-已付款 1-未付款2-失败',
  `elecStatus` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未申报，1已申报,2申报完成,3,已转发,4，入库失败',
  `wayStatus` tinyint(1) DEFAULT '0' COMMENT '运单报文，0 已申报，1 完成',
  `OrderGoodTotal` decimal(19,5) NOT NULL DEFAULT '0.00000' COMMENT '订单商品总额',
  `OrderGoodTotalCurr` char(3) NOT NULL COMMENT '订单商品总额币制',
  `Freight` decimal(19,5) NOT NULL DEFAULT '0.00000' COMMENT '订单运费',
  `Tax` decimal(19,5) NOT NULL DEFAULT '0.00000' COMMENT '税款',
  `OtherPayment` decimal(19,5) NOT NULL DEFAULT '0.00000' COMMENT '抵付金额',
  `OtherPayNotes` varchar(1000) DEFAULT NULL COMMENT '抵付说明',
  `OtherCharges` decimal(19,5) NOT NULL DEFAULT '0.00000' COMMENT '其它费用',
  `ActualAmountPaid` decimal(19,5) NOT NULL DEFAULT '0.00000' COMMENT '实际支付金额',
  `RecipientName` varchar(100) NOT NULL COMMENT '收货人姓名',
  `RecipientAddr` varchar(200) NOT NULL COMMENT '收货人地址',
  `RecipientTel` varchar(50) NOT NULL COMMENT '收货人电话',
  `RecipientCountry` varchar(8) NOT NULL COMMENT '收货人所在国',
  `RecipientProvincesCode` varchar(6) NOT NULL COMMENT '收货人行政区代码',
  `OrderDocAcount` varchar(60) NOT NULL COMMENT '下单人账户',
  `OrderDocName` varchar(60) NOT NULL COMMENT '下单人姓名',
  `OrderDocType` char(2) NOT NULL DEFAULT '01' COMMENT '下单人证件类型',
  `OrderDocId` varchar(60) NOT NULL COMMENT '下单人证件号',
   `OrderDocTel` varchar(50) NOT NULL COMMENT '下单人电话',
   `OrderDate` datetime DEFAULT NULL COMMENT '订单日期',
   `BatchNumbers` varchar(100) DEFAULT NULL COMMENT '商品批次号',
   `InvoiceType` tinyint(1) DEFAULT NULL COMMENT '1- 电子发票； 2- 普通发票（纸质）； 3- 专用发票（纸质）； 0- 其它',
    `InvoiceNo` varchar(20) DEFAULT NULL COMMENT '发票编号',
    `InvoiceTitle` varchar(100) DEFAULT NULL COMMENT '发票抬头',
    `InvoiceIdentifyID` varchar(30) DEFAULT NULL COMMENT '纳税人标识',
    `InvoiceDesc` varchar(200) DEFAULT NULL COMMENT '发票内容',
    `InvoiceAmount` decimal(19,5) NOT NULL DEFAULT '0.00000' COMMENT '发票金额',
    `InvoiceDate` varchar(10) NOT NULL DEFAULT '0' COMMENT '开票日期',
    `Notes` varchar(1000) DEFAULT NULL COMMENT '备注',
    `EHSEntNo` varchar(18) DEFAULT NULL COMMENT '物流企业代码',
    `EHSEntName` varchar(100) DEFAULT NULL COMMENT '物流企业名称',
    `WaybillNo` varchar(30) DEFAULT NULL COMMENT '电子运单编号',
    `PayEntNo` varchar(18) DEFAULT NULL COMMENT '支付企业代码',
    `PayEntName` varchar(100) DEFAULT NULL COMMENT '支付企业名称',
     `PayNo` varchar(60) DEFAULT NULL COMMENT '支付交易编号',
     `Qty` int(10) NOT NULL COMMENT '数量',
      `insuredFree` decimal(19,5) NOT NULL DEFAULT '0.00000' COMMENT '保价费',
      `senderName` varchar(50) NOT NULL COMMENT '发货人姓名',
      `senderTel` varchar(100) NOT NULL COMMENT '发货人电话',
       `senderAddr` varchar(200) NOT NULL COMMENT '发货人地址',
       `senderCountry` varchar(8) NOT NULL COMMENT '发货人所在国',
       `senderProvincesCode` varchar(6) NOT NULL COMMENT '发货人省市代码',
       `weight` varchar(10) NOT NULL COMMENT '毛重',
       `grossWeight` varchar(10) NOT NULL COMMENT '净重',
        `packageType` varchar(30) DEFAULT NULL COMMENT '包装类型',
         `unit` varchar(10) NOT NULL COMMENT '单位',
           `GoodsStyle` varchar(30) NOT NULL COMMENT '型号规格',
           `Province` varchar(30) DEFAULT NULL COMMENT '省',
            `city` varchar(30) DEFAULT NULL COMMENT '市',
             `county` varchar(30) DEFAULT NULL COMMENT '区县',
             `BarCode` varchar(20) DEFAULT NULL COMMENT '条形码',
             `create_at` int(10) NOT NULL,
              `OriginCountry` varchar(4) NOT NULL COMMENT '原产国',
              `Price` decimal(19,5) NOT NULL COMMENT '单价',
              PRIMARY KEY (`id`),
              KEY `index_o` (`head_id`,`EntOrderNo`,`goodsNo`)
              ) ENGINE=InnoDB AUTO_INCREMENT=1297 DEFAULT CHARSET=utf8


create  table ims_foll_err_order_elec(
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `uid` int(10) not null comment '用户id',
 `batch_num` varchar(100) not null comment '批次',
 `WaybillNo` varchar(100) not null comment '物流单号',
 `order_id` varchar(100) not null comment '订单号',
 `user_name` varchar(100)  null comment '用户名称',
 `err_msg` varchar(255) null comment '错误信息',
 `context` LONGTEXT null  comment '申报的内容',
 `context_had` LONGTEXT null comment '申报的内容头',
 `create_at` int(10) not null,
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 comment='申报错误表';


create table ims_foll_elec_count(
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT primary  key ,
 `uid` int(10) not null,
 `batch_num` varchar(100) not null,
 `total_count` int(10) not null default 0,
 `batchCount` int(10) not null default 0,
 `errSub` int(10) not null default 0,
 `batchErrorSub` int(10) not null default 0,
 `payCount` int(10) not null default 0,
 `batchPayCount` int(10) not null default 0,
 `elecCount` int(10) not null default 0,
 `batchElecCount` int(10) not null default 0,
 `errPay`  int(10) not null default 0,
 `batchErrPay` int(10) not null default 0,
 `errElec` int(10) not null default 0,
 `batchErrELec` int(10) not null default 0,
 index index_num(`uid`,`batch_num`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 comment='提交总数表';


create table ims_parking_violation(
`id` int(10) unsigned NOT NULL AUTO_INCREMENT primary  key ,
`ordersn` varchar(64) not null comment '订单',
`park_code` varchar(6) not null  comment '泊位号',
`picture`  varchar(255)  null comment '图片',
`warn_time` int(10) null comment '告警时间',
`law_time` int(10) null comment '执法时间',
`stime` int(10) not null comment '进场时间',
`etime` int(10) not null comment '离场时间',
`cardNo` varchar(10) not null comment '车牌号',
index index_vio(`ordersn`,`park_code`,`cardNo`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 comment='用户违规信息';


create table ims_parking_data(
	`id` int(10) not null AUTO_INCREMENT PRIMARY key,
	`time` varchar(100) not null comment '日期',
	`stopIn` int(10) not null default 0 comment '停入车次当天',
	`wxConfirm` int(10) not null default 0 comment '微信确认当天',
	`devPay` int(10) not null default 0 comment '咪表缴费当天',
	`timeOut` int(10) not null default 0 comment '确认超时当天',
	`excepOrder` int(10) not null default 0 comment '异免离开当天',
	index index_time(`time`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 comment='停车数据';