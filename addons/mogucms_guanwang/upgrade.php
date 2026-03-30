<?php
//升级数据表
pdo_query("CREATE TABLE IF NOT EXISTS `ims_mogucms_guanwang_case` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(300) NOT NULL DEFAULT '',
  `image` varchar(300) NOT NULL DEFAULT '',
  `erweima` varchar(300) NOT NULL DEFAULT '',
  `domainid` int(11) NOT NULL,
  `addtime` int(10) NOT NULL,
  `category` int(11) NOT NULL COMMENT '类别',
  `ord` int(11) DEFAULT '0',
  `myurl` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('mogucms_guanwang_case','id')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_case')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('mogucms_guanwang_case','title')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_case')." ADD   `title` varchar(300) NOT NULL DEFAULT ''");}
if(!pdo_fieldexists('mogucms_guanwang_case','image')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_case')." ADD   `image` varchar(300) NOT NULL DEFAULT ''");}
if(!pdo_fieldexists('mogucms_guanwang_case','erweima')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_case')." ADD   `erweima` varchar(300) NOT NULL DEFAULT ''");}
if(!pdo_fieldexists('mogucms_guanwang_case','domainid')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_case')." ADD   `domainid` int(11) NOT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_case','addtime')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_case')." ADD   `addtime` int(10) NOT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_case','category')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_case')." ADD   `category` int(11) NOT NULL COMMENT '类别'");}
if(!pdo_fieldexists('mogucms_guanwang_case','ord')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_case')." ADD   `ord` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('mogucms_guanwang_case','myurl')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_case')." ADD   `myurl` varchar(300) DEFAULT NULL");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_mogucms_guanwang_case_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoryname` varchar(90) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '分类名',
  `domainid` int(11) NOT NULL,
  `ord` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('mogucms_guanwang_case_category','id')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_case_category')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('mogucms_guanwang_case_category','categoryname')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_case_category')." ADD   `categoryname` varchar(90) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '分类名'");}
if(!pdo_fieldexists('mogucms_guanwang_case_category','domainid')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_case_category')." ADD   `domainid` int(11) NOT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_case_category','ord')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_case_category')." ADD   `ord` int(10) NOT NULL DEFAULT '0'");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_mogucms_guanwang_cert` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `domainid` bigint(11) NOT NULL,
  `name` varchar(60) DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `addtime` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('mogucms_guanwang_cert','id')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_cert')." ADD 
  `id` bigint(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('mogucms_guanwang_cert','domainid')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_cert')." ADD   `domainid` bigint(11) NOT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_cert','name')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_cert')." ADD   `name` varchar(60) DEFAULT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_cert','image')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_cert')." ADD   `image` varchar(500) DEFAULT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_cert','addtime')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_cert')." ADD   `addtime` int(10) NOT NULL");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_mogucms_guanwang_customer` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `domainid` bigint(11) NOT NULL,
  `title` varchar(60) DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `addtime` int(10) NOT NULL,
  `dizhi` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('mogucms_guanwang_customer','id')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_customer')." ADD 
  `id` bigint(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('mogucms_guanwang_customer','domainid')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_customer')." ADD   `domainid` bigint(11) NOT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_customer','title')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_customer')." ADD   `title` varchar(60) DEFAULT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_customer','image')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_customer')." ADD   `image` varchar(500) DEFAULT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_customer','addtime')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_customer')." ADD   `addtime` int(10) NOT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_customer','dizhi')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_customer')." ADD   `dizhi` varchar(300) DEFAULT NULL");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_mogucms_guanwang_domain` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `domain` varchar(100) NOT NULL,
  `username` varchar(30) DEFAULT NULL,
  `isfounder` smallint(1) NOT NULL DEFAULT '0',
  `val` longtext,
  `addtime` int(10) NOT NULL,
  `loginset` text,
  `banner` longtext,
  `ourservice` longtext,
  `development` longtext,
  `cando` longtext,
  `youshi` longtext,
  `title` longtext,
  `menu` longtext,
  `daili` longtext,
  `module` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('mogucms_guanwang_domain','id')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_domain')." ADD 
  `id` int(10) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('mogucms_guanwang_domain','domain')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_domain')." ADD   `domain` varchar(100) NOT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_domain','username')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_domain')." ADD   `username` varchar(30) DEFAULT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_domain','isfounder')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_domain')." ADD   `isfounder` smallint(1) NOT NULL DEFAULT '0'");}
if(!pdo_fieldexists('mogucms_guanwang_domain','val')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_domain')." ADD   `val` longtext");}
if(!pdo_fieldexists('mogucms_guanwang_domain','addtime')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_domain')." ADD   `addtime` int(10) NOT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_domain','loginset')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_domain')." ADD   `loginset` text");}
if(!pdo_fieldexists('mogucms_guanwang_domain','banner')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_domain')." ADD   `banner` longtext");}
if(!pdo_fieldexists('mogucms_guanwang_domain','ourservice')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_domain')." ADD   `ourservice` longtext");}
if(!pdo_fieldexists('mogucms_guanwang_domain','development')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_domain')." ADD   `development` longtext");}
if(!pdo_fieldexists('mogucms_guanwang_domain','cando')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_domain')." ADD   `cando` longtext");}
if(!pdo_fieldexists('mogucms_guanwang_domain','youshi')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_domain')." ADD   `youshi` longtext");}
if(!pdo_fieldexists('mogucms_guanwang_domain','title')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_domain')." ADD   `title` longtext");}
if(!pdo_fieldexists('mogucms_guanwang_domain','menu')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_domain')." ADD   `menu` longtext");}
if(!pdo_fieldexists('mogucms_guanwang_domain','daili')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_domain')." ADD   `daili` longtext");}
if(!pdo_fieldexists('mogucms_guanwang_domain','module')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_domain')." ADD   `module` varchar(30) DEFAULT NULL");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_mogucms_guanwang_help` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(300) NOT NULL DEFAULT '',
  `image` varchar(300) NOT NULL DEFAULT '',
  `erweima` varchar(300) NOT NULL DEFAULT '',
  `domainid` int(11) NOT NULL,
  `addtime` int(10) NOT NULL,
  `category` int(11) NOT NULL COMMENT '类别',
  `ord` int(11) DEFAULT '0',
  `myurl` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('mogucms_guanwang_help','id')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_help')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('mogucms_guanwang_help','title')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_help')." ADD   `title` varchar(300) NOT NULL DEFAULT ''");}
if(!pdo_fieldexists('mogucms_guanwang_help','image')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_help')." ADD   `image` varchar(300) NOT NULL DEFAULT ''");}
if(!pdo_fieldexists('mogucms_guanwang_help','erweima')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_help')." ADD   `erweima` varchar(300) NOT NULL DEFAULT ''");}
if(!pdo_fieldexists('mogucms_guanwang_help','domainid')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_help')." ADD   `domainid` int(11) NOT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_help','addtime')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_help')." ADD   `addtime` int(10) NOT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_help','category')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_help')." ADD   `category` int(11) NOT NULL COMMENT '类别'");}
if(!pdo_fieldexists('mogucms_guanwang_help','ord')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_help')." ADD   `ord` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('mogucms_guanwang_help','myurl')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_help')." ADD   `myurl` varchar(300) DEFAULT NULL");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_mogucms_guanwang_help_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoryname` varchar(90) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '分类名',
  `domainid` int(11) NOT NULL,
  `ord` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('mogucms_guanwang_help_category','id')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_help_category')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('mogucms_guanwang_help_category','categoryname')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_help_category')." ADD   `categoryname` varchar(90) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '分类名'");}
if(!pdo_fieldexists('mogucms_guanwang_help_category','domainid')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_help_category')." ADD   `domainid` int(11) NOT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_help_category','ord')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_help_category')." ADD   `ord` int(10) NOT NULL DEFAULT '0'");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_mogucms_guanwang_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(300) NOT NULL DEFAULT '' COMMENT '新闻标题',
  `content` mediumtext COMMENT '新闻内容',
  `domainid` int(11) NOT NULL,
  `addtime` int(10) NOT NULL,
  `category` int(11) NOT NULL COMMENT '类别',
  `abstract` text COMMENT '摘要',
  `keywords` varchar(900) DEFAULT NULL,
  `description` varchar(900) DEFAULT NULL,
  `num` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('mogucms_guanwang_news','id')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_news')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('mogucms_guanwang_news','title')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_news')." ADD   `title` varchar(300) NOT NULL DEFAULT '' COMMENT '新闻标题'");}
if(!pdo_fieldexists('mogucms_guanwang_news','content')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_news')." ADD   `content` mediumtext COMMENT '新闻内容'");}
if(!pdo_fieldexists('mogucms_guanwang_news','domainid')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_news')." ADD   `domainid` int(11) NOT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_news','addtime')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_news')." ADD   `addtime` int(10) NOT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_news','category')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_news')." ADD   `category` int(11) NOT NULL COMMENT '类别'");}
if(!pdo_fieldexists('mogucms_guanwang_news','abstract')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_news')." ADD   `abstract` text COMMENT '摘要'");}
if(!pdo_fieldexists('mogucms_guanwang_news','keywords')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_news')." ADD   `keywords` varchar(900) DEFAULT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_news','description')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_news')." ADD   `description` varchar(900) DEFAULT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_news','num')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_news')." ADD   `num` int(11) DEFAULT '0'");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_mogucms_guanwang_news_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoryname` varchar(90) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '分类名',
  `domainid` int(11) NOT NULL,
  `ord` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('mogucms_guanwang_news_category','id')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_news_category')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('mogucms_guanwang_news_category','categoryname')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_news_category')." ADD   `categoryname` varchar(90) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '分类名'");}
if(!pdo_fieldexists('mogucms_guanwang_news_category','domainid')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_news_category')." ADD   `domainid` int(11) NOT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_news_category','ord')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_news_category')." ADD   `ord` int(10) NOT NULL DEFAULT '0'");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_mogucms_guanwang_rukou` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `domainid` bigint(11) NOT NULL,
  `title` varchar(60) DEFAULT NULL,
  `abstract` varchar(120) DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `addtime` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('mogucms_guanwang_rukou','id')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_rukou')." ADD 
  `id` bigint(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('mogucms_guanwang_rukou','domainid')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_rukou')." ADD   `domainid` bigint(11) NOT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_rukou','title')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_rukou')." ADD   `title` varchar(60) DEFAULT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_rukou','abstract')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_rukou')." ADD   `abstract` varchar(120) DEFAULT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_rukou','image')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_rukou')." ADD   `image` varchar(500) DEFAULT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_rukou','addtime')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_rukou')." ADD   `addtime` int(10) NOT NULL");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_mogucms_guanwang_solve` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(300) NOT NULL DEFAULT '',
  `content` mediumtext COMMENT '新闻内容',
  `domainid` int(11) NOT NULL,
  `addtime` int(10) NOT NULL,
  `category` int(11) NOT NULL COMMENT '类别',
  `count` int(11) NOT NULL,
  `keywords` varchar(900) DEFAULT NULL,
  `description` varchar(900) DEFAULT NULL,
  `image` varchar(600) DEFAULT NULL,
  `erweima` varchar(600) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('mogucms_guanwang_solve','id')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_solve')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('mogucms_guanwang_solve','title')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_solve')." ADD   `title` varchar(300) NOT NULL DEFAULT ''");}
if(!pdo_fieldexists('mogucms_guanwang_solve','content')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_solve')." ADD   `content` mediumtext COMMENT '新闻内容'");}
if(!pdo_fieldexists('mogucms_guanwang_solve','domainid')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_solve')." ADD   `domainid` int(11) NOT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_solve','addtime')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_solve')." ADD   `addtime` int(10) NOT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_solve','category')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_solve')." ADD   `category` int(11) NOT NULL COMMENT '类别'");}
if(!pdo_fieldexists('mogucms_guanwang_solve','count')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_solve')." ADD   `count` int(11) NOT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_solve','keywords')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_solve')." ADD   `keywords` varchar(900) DEFAULT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_solve','description')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_solve')." ADD   `description` varchar(900) DEFAULT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_solve','image')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_solve')." ADD   `image` varchar(600) DEFAULT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_solve','erweima')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_solve')." ADD   `erweima` varchar(600) DEFAULT NULL");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_mogucms_guanwang_team` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `domainid` bigint(11) NOT NULL,
  `name` varchar(60) DEFAULT NULL,
  `zhiwei` varchar(60) DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `addtime` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('mogucms_guanwang_team','id')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_team')." ADD 
  `id` bigint(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('mogucms_guanwang_team','domainid')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_team')." ADD   `domainid` bigint(11) NOT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_team','name')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_team')." ADD   `name` varchar(60) DEFAULT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_team','zhiwei')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_team')." ADD   `zhiwei` varchar(60) DEFAULT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_team','image')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_team')." ADD   `image` varchar(500) DEFAULT NULL");}
if(!pdo_fieldexists('mogucms_guanwang_team','addtime')) {pdo_query("ALTER TABLE ".tablename('mogucms_guanwang_team')." ADD   `addtime` int(10) NOT NULL");}
