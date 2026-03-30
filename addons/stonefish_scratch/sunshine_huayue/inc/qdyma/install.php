<?php

$sql =<<<EOF

CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_admin` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT '',
  `add_time` datetime NOT NULL,
  `is_del` enum('y','n') NOT NULL DEFAULT 'n',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_album` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `mid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT '',
  `img_url` varchar(200) NOT NULL DEFAULT '',
  `remark` varchar(100) NOT NULL DEFAULT '',
  `upload_way` varchar(50) NOT NULL DEFAULT '',
  `type` varchar(50) NOT NULL DEFAULT 'album',
  `add_time` datetime NOT NULL,
  `is_del` enum('y','n') DEFAULT 'n',
  `del_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_chat` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `talk_sign` varchar(100) NOT NULL DEFAULT '' COMMENT '聊天标记，按照大小合并',
  `user_openid` varchar(50) NOT NULL DEFAULT '' COMMENT '作为当前登录人的user_openid',
  `to_openid` varchar(50) NOT NULL DEFAULT '' COMMENT '对方的',
  `refresh_time` datetime NOT NULL,
  `add_time` datetime NOT NULL,
  `status` enum('delete','deny','allow') DEFAULT 'allow',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_to` (`user_openid`,`to_openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_chatmessage` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `talk_sign` varchar(100) NOT NULL DEFAULT '' COMMENT '聊天标记，按照大小合并',
  `send_openid` varchar(50) NOT NULL DEFAULT '' COMMENT '发送消息的人的openid',
  `chat_message` varchar(200) NOT NULL DEFAULT '',
  `type` enum('text','voice','album') DEFAULT 'text' COMMENT '消息类型',
  `readed` enum('y','n') DEFAULT 'n' COMMENT '是否已读',
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_chatroom` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `creator` varchar(30) NOT NULL DEFAULT 'system',
  `room_name` varchar(50) NOT NULL DEFAULT '',
  `room_desc` varchar(100) NOT NULL DEFAULT '',
  `room_logo` varchar(100) NOT NULL DEFAULT '',
  `room_type` enum('normal','lvb','letv') NOT NULL DEFAULT 'normal',
  `lvb_channel_id` varchar(50) NOT NULL DEFAULT '',
  `is_public` enum('y','n') NOT NULL DEFAULT 'y' COMMENT '开放类型 公共or隐私',
  `is_secret` enum('y','n') NOT NULL DEFAULT 'n' COMMENT '进入类型  开放还是口令',
  `room_secret` varchar(50) NOT NULL DEFAULT '',
  `sort_id` int(10) NOT NULL,
  `is_approve` enum('allow','deny','wait') DEFAULT 'wait' COMMENT '审核',
  `add_date` date NOT NULL,
  `add_time` datetime NOT NULL,
  `room_status` enum('normal','delete','close') NOT NULL DEFAULT 'normal',
  `is_robot` enum('y','n') DEFAULT 'n',
  `in_type` enum('secret','money','no_type') NOT NULL DEFAULT 'no_type' COMMENT '是否需要口令或者付费',
  `room_money` decimal(5,2) NOT NULL COMMENT '费用',
  `room_money_day` int(10) NOT NULL COMMENT '天数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_chatroom_defriend` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `room_id` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT '',
  `creator` varchar(50) NOT NULL DEFAULT 'system',
  `add_time` datetime NOT NULL,
  `status` enum('y','n') NOT NULL DEFAULT 'n' COMMENT 'y defrend n relieve',
  PRIMARY KEY (`id`),
  UNIQUE KEY `oo` (`openid`,`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_chatroom_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `rid` int(10) NOT NULL COMMENT '聊天室ID',
  `openid` varchar(50) NOT NULL DEFAULT '',
  `content` varchar(200) NOT NULL DEFAULT '',
  `type` enum('text','voice','album','redpack','gift') NOT NULL DEFAULT 'text',
  `add_time` datetime NOT NULL,
  `is_del` enum('y','n') DEFAULT 'n',
  `complain_times` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_comment` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `mid` int(10) unsigned NOT NULL,
  `comment_openid` varchar(50) NOT NULL DEFAULT '',
  `user_openid` varchar(50) NOT NULL DEFAULT '' COMMENT '记录创建人',
  `reply_openid` varchar(50) NOT NULL DEFAULT '' COMMENT '被回复的人',
  `content` varchar(500) NOT NULL DEFAULT '',
  `add_time` datetime NOT NULL,
  `is_del` enum('y','n') DEFAULT 'n',
  `del_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_credit` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT '',
  `sid` int(10) NOT NULL,
  `credit` int(10) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT '',
  `add_date` date NOT NULL,
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_defriend` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT '',
  `defriend_openid` varchar(50) NOT NULL DEFAULT '',
  `add_time` datetime NOT NULL,
  `status` enum('y','n') NOT NULL DEFAULT 'n' COMMENT 'y defrend n relieve',
  PRIMARY KEY (`id`),
  UNIQUE KEY `oo` (`openid`,`defriend_openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_draw_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT '',
  `money` decimal(8,2) NOT NULL,
  `commision` decimal(8,2) NOT NULL,
  `act_draw` decimal(8,2) NOT NULL,
  `status` enum('wait','handle') DEFAULT 'wait' COMMENT '提现状态',
  `add_time` datetime NOT NULL,
  `update_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_feedback` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT '',
  `content` varchar(300) NOT NULL DEFAULT '',
  `status` enum('wait','handle') DEFAULT 'wait' COMMENT '处理状态',
  `add_time` datetime NOT NULL,
  `update_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_gift` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `sort_id` int(10) unsigned NOT NULL,
  `gift_name` varchar(50) NOT NULL DEFAULT '',
  `gift_price` decimal(8,2) NOT NULL,
  `gift_pic` varchar(200) NOT NULL DEFAULT '',
  `sale_num` int(10) unsigned NOT NULL,
  `use_num` int(10) unsigned NOT NULL,
  `is_del` enum('y','n') DEFAULT 'n',
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_gift_order` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT '',
  `gift_data` varchar(2000) NOT NULL DEFAULT '',
  `pay_money` decimal(8,2) NOT NULL,
  `status` enum('wait','payed') NOT NULL DEFAULT 'wait',
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_gift_present_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT '',
  `to_openid` varchar(50) NOT NULL DEFAULT '',
  `rid` int(10) NOT NULL,
  `gift_id` varchar(50) NOT NULL DEFAULT '',
  `gift_price` decimal(8,2) NOT NULL,
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_gift_user` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT '',
  `gift_id` varchar(50) NOT NULL DEFAULT '',
  `gift_num` int(10) NOT NULL,
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_greets` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `start_openid` varchar(50) NOT NULL DEFAULT '',
  `to_openid` varchar(50) NOT NULL DEFAULT '',
  `add_time` datetime NOT NULL,
  `readed` enum('y','n') DEFAULT 'n',
  PRIMARY KEY (`id`),
  UNIQUE KEY `start_to` (`start_openid`,`to_openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_growth` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT '',
  `score` int(10) NOT NULL,
  `intro` varchar(100) NOT NULL DEFAULT '',
  `add_date` datetime NOT NULL,
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `openid_add_date` (`openid`,`add_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_letv` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `rid` int(10) NOT NULL COMMENT '聊天室ID',
  `openid` varchar(50) NOT NULL DEFAULT '',
  `activity_id` varchar(50) NOT NULL DEFAULT '',
  `push_url` varchar(200) NOT NULL DEFAULT '',
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_lvb` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `rid` int(10) NOT NULL COMMENT '聊天室ID',
  `openid` varchar(50) NOT NULL DEFAULT '',
  `channel_id` varchar(50) NOT NULL DEFAULT '',
  `protocol` varchar(10) NOT NULL DEFAULT '',
  `upstream_address` varchar(200) NOT NULL DEFAULT '',
  `rate_type` varchar(10) NOT NULL DEFAULT '',
  `rtmp_downstream_address` varchar(200) NOT NULL DEFAULT '',
  `flv_downstream_address` varchar(200) NOT NULL DEFAULT '',
  `hls_downstream_address` varchar(200) NOT NULL DEFAULT '',
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_member` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `openid` varchar(50) NOT NULL DEFAULT '',
  `uniacid` varchar(50) NOT NULL DEFAULT '',
  `acid` varchar(50) NOT NULL DEFAULT '',
  `account` varchar(50) NOT NULL DEFAULT '',
  `use_times` int(10) NOT NULL DEFAULT '0',
  `add_time` datetime NOT NULL,
  `nickname` varchar(50) NOT NULL DEFAULT '',
  `sex` varchar(20) NOT NULL DEFAULT '',
  `province` varchar(50) NOT NULL DEFAULT '',
  `city` varchar(50) NOT NULL DEFAULT '',
  `country` varchar(50) NOT NULL DEFAULT '',
  `headimgurl` varchar(200) NOT NULL DEFAULT '',
  `privilege` varchar(50) NOT NULL DEFAULT '',
  `unionid` varchar(50) NOT NULL DEFAULT '',
  `position` varchar(50) NOT NULL DEFAULT '',
  `update_time` datetime NOT NULL,
  `bechecked` int(11) NOT NULL DEFAULT '0',
  `lng` varchar(50) NOT NULL DEFAULT '' COMMENT '经度',
  `lat` varchar(50) NOT NULL DEFAULT '' COMMENT '纬度',
  `choose_sex` varchar(50) NOT NULL DEFAULT '' COMMENT '查看性别',
  `age` varchar(10) NOT NULL DEFAULT '0' COMMENT '年龄',
  `sign` varchar(50) NOT NULL DEFAULT '' COMMENT '个人签名',
  `isvisible` varchar(10) NOT NULL DEFAULT 'close',
  `is_notice` enum('y','n') NOT NULL DEFAULT 'y',
  `notice_times` int(10) NOT NULL,
  `growth_score` int(10) NOT NULL,
  `vip_level` int(10) NOT NULL,
  `vip_add_time` datetime NOT NULL,
  `vip_end_time` datetime NOT NULL,
  `forbid_status` enum('y','n') DEFAULT 'n' COMMENT '系统移除状态',
  `forbid_add_time` datetime NOT NULL,
  `forbid_end_time` datetime NOT NULL,
  `mobile` varchar(50) NOT NULL DEFAULT '',
  `mobile_status` enum('y','n') DEFAULT 'n' COMMENT '手机号验证',
  `mobile_captcha` int(6) NOT NULL,
  `mobile_captcha_send_time` datetime NOT NULL,
  `work` varchar(20) NOT NULL DEFAULT '',
  `avaliable_money` decimal(8,2) NOT NULL,
  `draw_money` decimal(8,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_menu` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT '',
  `order_id` varchar(50) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `url` varchar(200) NOT NULL DEFAULT '',
  `intro` varchar(50) NOT NULL DEFAULT '',
  `is_del` enum('y','n') DEFAULT 'n',
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_moments` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT '',
  `remark` varchar(200) NOT NULL DEFAULT '' COMMENT '想法',
  `type` enum('image','text') DEFAULT 'image' COMMENT '类型，决定是否去查询图片表',
  `add_time` datetime NOT NULL,
  `is_del` enum('y','n') DEFAULT 'n',
  `del_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_multisend` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `creator` varchar(50) NOT NULL DEFAULT '',
  `content` varchar(500) NOT NULL DEFAULT '',
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_mychatroom_history` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT '',
  `room_id` int(10) unsigned NOT NULL,
  `update_time` datetime NOT NULL,
  `add_time` datetime NOT NULL,
  `is_del` enum('y','n') NOT NULL DEFAULT 'n',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_rewards` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `room_id` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT '',
  `to_openid` varchar(50) NOT NULL DEFAULT 'system',
  `status` enum('y','n') NOT NULL DEFAULT 'n',
  `money` varchar(20) NOT NULL,
  `add_time` datetime NOT NULL,
  `update_time` datetime NOT NULL,
  `money_type` varchar(20) NOT NULL DEFAULT 'room_rewards',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_setting` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `name` varchar(200) NOT NULL DEFAULT '',
  `value` varchar(200) NOT NULL DEFAULT '',
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOF;
pdo_run($sql);
