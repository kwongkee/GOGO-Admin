<?php 
pdo_query('CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_admin` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT \'\',
  `add_time` datetime NOT NULL,
  `is_del` enum(\'y\',\'n\') NOT NULL DEFAULT \'n\',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_album` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `mid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT \'\',
  `img_url` varchar(200) NOT NULL DEFAULT \'\',
  `remark` varchar(100) NOT NULL DEFAULT \'\',
  `upload_way` varchar(50) NOT NULL DEFAULT \'\',
  `type` varchar(50) NOT NULL DEFAULT \'album\',
  `add_time` datetime NOT NULL,
  `is_del` enum(\'y\',\'n\') DEFAULT \'n\',
  `del_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_chat` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `talk_sign` varchar(100) NOT NULL DEFAULT \'\' COMMENT \'聊天标记，按照大小合并\',
  `user_openid` varchar(50) NOT NULL DEFAULT \'\' COMMENT \'作为当前登录人的user_openid\',
  `to_openid` varchar(50) NOT NULL DEFAULT \'\' COMMENT \'对方的\',
  `refresh_time` datetime NOT NULL,
  `add_time` datetime NOT NULL,
  `status` enum(\'delete\',\'deny\',\'allow\') DEFAULT \'allow\',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_to` (`user_openid`,`to_openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_chatmessage` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `talk_sign` varchar(100) NOT NULL DEFAULT \'\' COMMENT \'聊天标记，按照大小合并\',
  `send_openid` varchar(50) NOT NULL DEFAULT \'\' COMMENT \'发送消息的人的openid\',
  `chat_message` varchar(200) NOT NULL DEFAULT \'\',
  `type` enum(\'text\',\'voice\',\'album\') DEFAULT \'text\' COMMENT \'消息类型\',
  `readed` enum(\'y\',\'n\') DEFAULT \'n\' COMMENT \'是否已读\',
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_chatroom` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `creator` varchar(30) NOT NULL DEFAULT \'system\',
  `room_name` varchar(50) NOT NULL DEFAULT \'\',
  `room_desc` varchar(100) NOT NULL DEFAULT \'\',
  `room_logo` varchar(100) NOT NULL DEFAULT \'\',
  `room_type` enum(\'normal\',\'lvb\',\'letv\') NOT NULL DEFAULT \'normal\',
  `lvb_channel_id` varchar(50) NOT NULL DEFAULT \'\',
  `is_public` enum(\'y\',\'n\') NOT NULL DEFAULT \'y\' COMMENT \'开放类型 公共or隐私\',
  `in_type` enum(\'secret\',\'money\',\'no_type\') NOT NULL DEFAULT \'no_type\' COMMENT \'是否需要口令或者付费\',
  `room_secret` varchar(50) NOT NULL DEFAULT \'\',
  `room_money` decimal(5,2) NOT NULL COMMENT \'费用\',
  `room_money_day` int(10) NOT NULL COMMENT \'天数\',
  `sort_id` int(10) NOT NULL,
  `is_approve` enum(\'allow\',\'deny\',\'wait\') DEFAULT \'wait\' COMMENT \'审核\',
  `add_date` date NOT NULL,
  `add_time` datetime NOT NULL,
  `room_status` enum(\'normal\',\'delete\',\'close\') NOT NULL DEFAULT \'normal\',
  `is_robot` enum(\'y\',\'n\') DEFAULT \'n\',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_chatroom_defriend` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `room_id` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT \'\',
  `creator` varchar(50) NOT NULL DEFAULT \'system\',
  `add_time` datetime NOT NULL,
  `status` enum(\'y\',\'n\') NOT NULL DEFAULT \'n\' COMMENT \'y defrend n relieve\',
  PRIMARY KEY (`id`),
  UNIQUE KEY `oo` (`openid`,`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_chatroom_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `rid` int(10) NOT NULL COMMENT \'聊天室ID\',
  `openid` varchar(50) NOT NULL DEFAULT \'\',
  `content` varchar(200) NOT NULL DEFAULT \'\',
  `type` enum(\'text\',\'voice\',\'album\',\'redpack\',\'room_money\',\'gift\') DEFAULT \'text\' COMMENT \'消息类型\',
  `add_time` datetime NOT NULL,
  `is_del` enum(\'y\',\'n\') DEFAULT \'n\',
  `complain_times` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_comment` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `mid` int(10) unsigned NOT NULL,
  `comment_openid` varchar(50) NOT NULL DEFAULT \'\',
  `user_openid` varchar(50) NOT NULL DEFAULT \'\' COMMENT \'记录创建人\',
  `reply_openid` varchar(50) NOT NULL DEFAULT \'\' COMMENT \'被回复的人\',
  `content` varchar(500) NOT NULL DEFAULT \'\',
  `add_time` datetime NOT NULL,
  `is_del` enum(\'y\',\'n\') DEFAULT \'n\',
  `del_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_credit` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT \'\',
  `sid` int(10) NOT NULL,
  `credit` int(10) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT \'\',
  `add_date` date NOT NULL,
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_defriend` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT \'\',
  `defriend_openid` varchar(50) NOT NULL DEFAULT \'\',
  `add_time` datetime NOT NULL,
  `status` enum(\'y\',\'n\') NOT NULL DEFAULT \'n\' COMMENT \'y defrend n relieve\',
  PRIMARY KEY (`id`),
  UNIQUE KEY `oo` (`openid`,`defriend_openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_draw_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT \'\',
  `money` decimal(8,2) NOT NULL,
  `commision` decimal(8,2) NOT NULL,
  `act_draw` decimal(8,2) NOT NULL,
  `status` enum(\'wait\',\'handle\') DEFAULT \'wait\' COMMENT \'提现状态\',
  `add_time` datetime NOT NULL,
  `update_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_feedback` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT \'\',
  `content` varchar(300) NOT NULL DEFAULT \'\',
  `status` enum(\'wait\',\'handle\') DEFAULT \'wait\' COMMENT \'处理状态\',
  `add_time` datetime NOT NULL,
  `update_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_gift` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `sort_id` int(10) unsigned NOT NULL,
  `gift_name` varchar(50) NOT NULL DEFAULT \'\',
  `gift_price` decimal(8,2) NOT NULL,
  `gift_pic` varchar(200) NOT NULL DEFAULT \'\',
  `sale_num` int(10) unsigned NOT NULL,
  `use_num` int(10) unsigned NOT NULL,
  `is_del` enum(\'y\',\'n\') DEFAULT \'n\',
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_gift_order` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT \'\',
  `gift_data` varchar(2000) NOT NULL DEFAULT \'\',
  `pay_money` decimal(8,2) NOT NULL,
  `status` enum(\'wait\',\'payed\') NOT NULL DEFAULT \'wait\',
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_gift_present_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT \'\',
  `to_openid` varchar(50) NOT NULL DEFAULT \'\',
  `rid` int(10) NOT NULL,
  `gift_id` varchar(50) NOT NULL DEFAULT \'\',
  `gift_price` decimal(8,2) NOT NULL,
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_gift_user` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT \'\',
  `gift_id` varchar(50) NOT NULL DEFAULT \'\',
  `gift_num` int(10) NOT NULL,
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_greets` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `start_openid` varchar(50) NOT NULL DEFAULT \'\',
  `to_openid` varchar(50) NOT NULL DEFAULT \'\',
  `add_time` datetime NOT NULL,
  `readed` enum(\'y\',\'n\') DEFAULT \'n\',
  PRIMARY KEY (`id`),
  UNIQUE KEY `start_to` (`start_openid`,`to_openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_growth` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT \'\',
  `score` int(10) NOT NULL,
  `intro` varchar(100) NOT NULL DEFAULT \'\',
  `add_date` datetime NOT NULL,
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `openid_add_date` (`openid`,`add_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_letv` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `rid` int(10) NOT NULL COMMENT \'聊天室ID\',
  `openid` varchar(50) NOT NULL DEFAULT \'\',
  `activity_id` varchar(50) NOT NULL DEFAULT \'\',
  `push_url` varchar(200) NOT NULL DEFAULT \'\',
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_lvb` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `rid` int(10) NOT NULL COMMENT \'聊天室ID\',
  `openid` varchar(50) NOT NULL DEFAULT \'\',
  `channel_id` varchar(50) NOT NULL DEFAULT \'\',
  `protocol` varchar(10) NOT NULL DEFAULT \'\',
  `upstream_address` varchar(200) NOT NULL DEFAULT \'\',
  `rate_type` varchar(10) NOT NULL DEFAULT \'\',
  `rtmp_downstream_address` varchar(200) NOT NULL DEFAULT \'\',
  `flv_downstream_address` varchar(200) NOT NULL DEFAULT \'\',
  `hls_downstream_address` varchar(200) NOT NULL DEFAULT \'\',
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_member` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `openid` varchar(50) NOT NULL DEFAULT \'\',
  `uniacid` varchar(50) NOT NULL DEFAULT \'\',
  `acid` varchar(50) NOT NULL DEFAULT \'\',
  `account` varchar(50) NOT NULL DEFAULT \'\',
  `use_times` int(10) NOT NULL DEFAULT \'0\',
  `add_time` datetime NOT NULL,
  `nickname` varchar(50) NOT NULL DEFAULT \'\',
  `sex` varchar(20) NOT NULL DEFAULT \'\',
  `province` varchar(50) NOT NULL DEFAULT \'\',
  `city` varchar(50) NOT NULL DEFAULT \'\',
  `country` varchar(50) NOT NULL DEFAULT \'\',
  `headimgurl` varchar(200) NOT NULL DEFAULT \'\',
  `privilege` varchar(50) NOT NULL DEFAULT \'\',
  `unionid` varchar(50) NOT NULL DEFAULT \'\',
  `position` varchar(50) NOT NULL DEFAULT \'\',
  `update_time` datetime NOT NULL,
  `bechecked` int(11) NOT NULL DEFAULT \'0\',
  `lng` varchar(50) NOT NULL DEFAULT \'\' COMMENT \'经度\',
  `lat` varchar(50) NOT NULL DEFAULT \'\' COMMENT \'纬度\',
  `choose_sex` varchar(50) NOT NULL DEFAULT \'\' COMMENT \'查看性别\',
  `age` varchar(10) NOT NULL DEFAULT \'0\' COMMENT \'年龄\',
  `sign` varchar(50) NOT NULL DEFAULT \'\' COMMENT \'个人签名\',
  `isvisible` varchar(10) NOT NULL DEFAULT \'close\',
  `is_notice` enum(\'y\',\'n\') NOT NULL DEFAULT \'y\',
  `notice_times` int(10) NOT NULL,
  `growth_score` int(10) NOT NULL,
  `vip_level` int(10) NOT NULL,
  `vip_add_time` datetime NOT NULL,
  `vip_end_time` datetime NOT NULL,
  `forbid_status` enum(\'y\',\'n\') DEFAULT \'n\' COMMENT \'系统移除状态\',
  `forbid_add_time` datetime NOT NULL,
  `forbid_end_time` datetime NOT NULL,
  `mobile` varchar(50) NOT NULL DEFAULT \'\',
  `mobile_status` enum(\'y\',\'n\') DEFAULT \'n\' COMMENT \'手机号验证\',
  `mobile_captcha` int(6) NOT NULL,
  `mobile_captcha_send_time` datetime NOT NULL,
  `work` varchar(20) NOT NULL DEFAULT \'\',
  `avaliable_money` decimal(8,2) NOT NULL,
  `draw_money` decimal(8,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=400 DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_menu` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT \'\',
  `order_id` varchar(50) NOT NULL DEFAULT \'\',
  `name` varchar(50) NOT NULL DEFAULT \'\',
  `url` varchar(200) NOT NULL DEFAULT \'\',
  `intro` varchar(50) NOT NULL DEFAULT \'\',
  `is_del` enum(\'y\',\'n\') DEFAULT \'n\',
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_moments` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT \'\',
  `remark` varchar(200) NOT NULL DEFAULT \'\' COMMENT \'想法\',
  `type` enum(\'image\',\'text\') DEFAULT \'image\' COMMENT \'类型，决定是否去查询图片表\',
  `add_time` datetime NOT NULL,
  `is_del` enum(\'y\',\'n\') DEFAULT \'n\',
  `del_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_multisend` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `creator` varchar(50) NOT NULL DEFAULT \'\',
  `content` varchar(500) NOT NULL DEFAULT \'\',
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_mychatroom_history` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT \'\',
  `room_id` int(10) unsigned NOT NULL,
  `update_time` datetime NOT NULL,
  `add_time` datetime NOT NULL,
  `is_del` enum(\'y\',\'n\') NOT NULL DEFAULT \'n\',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_rewards` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `room_id` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT \'\',
  `to_openid` varchar(50) NOT NULL DEFAULT \'system\',
  `status` enum(\'y\',\'n\') NOT NULL DEFAULT \'n\',
  `money` varchar(20) NOT NULL,
  `money_type` varchar(20) NOT NULL DEFAULT \'money_rewards\',
  `add_time` datetime NOT NULL,
  `update_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_setting` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `name` varchar(200) NOT NULL DEFAULT \'\',
  `value` varchar(200) NOT NULL DEFAULT \'\',
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniacid_name` (`uniacid`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_sunshine_huayue_voice_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL DEFAULT \'\',
  `r_log_id` int(10) NOT NULL,
  `voice_path` varchar(100) NOT NULL DEFAULT \'\',
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
');
if (pdo_tableexists('sunshine_huayue_admin')) {
    if (!pdo_fieldexists('sunshine_huayue_admin', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_admin') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_admin')) {
    if (!pdo_fieldexists('sunshine_huayue_admin', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_admin') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_admin')) {
    if (!pdo_fieldexists('sunshine_huayue_admin', 'openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_admin') . ' ADD `openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_admin')) {
    if (!pdo_fieldexists('sunshine_huayue_admin', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_admin') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_admin')) {
    if (!pdo_fieldexists('sunshine_huayue_admin', 'is_del')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_admin') . ' ADD `is_del` enum(\'y\',\'n\') NOT NULL  DEFAULT n COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_album')) {
    if (!pdo_fieldexists('sunshine_huayue_album', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_album') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_album')) {
    if (!pdo_fieldexists('sunshine_huayue_album', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_album') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_album')) {
    if (!pdo_fieldexists('sunshine_huayue_album', 'mid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_album') . ' ADD `mid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_album')) {
    if (!pdo_fieldexists('sunshine_huayue_album', 'openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_album') . ' ADD `openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_album')) {
    if (!pdo_fieldexists('sunshine_huayue_album', 'img_url')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_album') . ' ADD `img_url` varchar(200) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_album')) {
    if (!pdo_fieldexists('sunshine_huayue_album', 'remark')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_album') . ' ADD `remark` varchar(100) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_album')) {
    if (!pdo_fieldexists('sunshine_huayue_album', 'upload_way')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_album') . ' ADD `upload_way` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_album')) {
    if (!pdo_fieldexists('sunshine_huayue_album', 'type')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_album') . ' ADD `type` varchar(50) NOT NULL  DEFAULT album COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_album')) {
    if (!pdo_fieldexists('sunshine_huayue_album', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_album') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_album')) {
    if (!pdo_fieldexists('sunshine_huayue_album', 'is_del')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_album') . ' ADD `is_del` enum(\'y\',\'n\')   DEFAULT n COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_album')) {
    if (!pdo_fieldexists('sunshine_huayue_album', 'del_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_album') . ' ADD `del_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chat')) {
    if (!pdo_fieldexists('sunshine_huayue_chat', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chat') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chat')) {
    if (!pdo_fieldexists('sunshine_huayue_chat', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chat') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chat')) {
    if (!pdo_fieldexists('sunshine_huayue_chat', 'talk_sign')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chat') . ' ADD `talk_sign` varchar(100) NOT NULL   COMMENT \'聊天标记，按照大小合并\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chat')) {
    if (!pdo_fieldexists('sunshine_huayue_chat', 'user_openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chat') . ' ADD `user_openid` varchar(50) NOT NULL   COMMENT \'作为当前登录人的user_openid\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chat')) {
    if (!pdo_fieldexists('sunshine_huayue_chat', 'to_openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chat') . ' ADD `to_openid` varchar(50) NOT NULL   COMMENT \'对方的\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chat')) {
    if (!pdo_fieldexists('sunshine_huayue_chat', 'refresh_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chat') . ' ADD `refresh_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chat')) {
    if (!pdo_fieldexists('sunshine_huayue_chat', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chat') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chat')) {
    if (!pdo_fieldexists('sunshine_huayue_chat', 'status')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chat') . ' ADD `status` enum(\'delete\',\'deny\',\'allow\')   DEFAULT allow COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatmessage')) {
    if (!pdo_fieldexists('sunshine_huayue_chatmessage', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatmessage') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatmessage')) {
    if (!pdo_fieldexists('sunshine_huayue_chatmessage', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatmessage') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatmessage')) {
    if (!pdo_fieldexists('sunshine_huayue_chatmessage', 'talk_sign')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatmessage') . ' ADD `talk_sign` varchar(100) NOT NULL   COMMENT \'聊天标记，按照大小合并\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatmessage')) {
    if (!pdo_fieldexists('sunshine_huayue_chatmessage', 'send_openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatmessage') . ' ADD `send_openid` varchar(50) NOT NULL   COMMENT \'发送消息的人的openid\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatmessage')) {
    if (!pdo_fieldexists('sunshine_huayue_chatmessage', 'chat_message')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatmessage') . ' ADD `chat_message` varchar(200) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatmessage')) {
    if (!pdo_fieldexists('sunshine_huayue_chatmessage', 'type')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatmessage') . ' ADD `type` enum(\'text\',\'voice\',\'album\')   DEFAULT text COMMENT \'消息类型\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatmessage')) {
    if (!pdo_fieldexists('sunshine_huayue_chatmessage', 'readed')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatmessage') . ' ADD `readed` enum(\'y\',\'n\')   DEFAULT n COMMENT \'是否已读\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatmessage')) {
    if (!pdo_fieldexists('sunshine_huayue_chatmessage', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatmessage') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom', 'creator')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom') . ' ADD `creator` varchar(30) NOT NULL  DEFAULT system COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom', 'room_name')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom') . ' ADD `room_name` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom', 'room_desc')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom') . ' ADD `room_desc` varchar(100) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom', 'room_logo')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom') . ' ADD `room_logo` varchar(100) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom', 'room_type')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom') . ' ADD `room_type` enum(\'normal\',\'lvb\',\'letv\') NOT NULL  DEFAULT normal COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom', 'lvb_channel_id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom') . ' ADD `lvb_channel_id` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom', 'is_public')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom') . ' ADD `is_public` enum(\'y\',\'n\') NOT NULL  DEFAULT y COMMENT \'开放类型 公共or隐私\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom', 'in_type')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom') . ' ADD `in_type` enum(\'secret\',\'money\',\'no_type\') NOT NULL  DEFAULT no_type COMMENT \'是否需要口令或者付费\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom', 'room_secret')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom') . ' ADD `room_secret` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom', 'room_money')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom') . ' ADD `room_money` decimal(5,2) NOT NULL   COMMENT \'费用\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom', 'room_money_day')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom') . ' ADD `room_money_day` int(10) NOT NULL   COMMENT \'天数\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom', 'sort_id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom') . ' ADD `sort_id` int(10) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom', 'is_approve')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom') . ' ADD `is_approve` enum(\'allow\',\'deny\',\'wait\')   DEFAULT wait COMMENT \'审核\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom', 'add_date')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom') . ' ADD `add_date` date NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom', 'room_status')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom') . ' ADD `room_status` enum(\'normal\',\'delete\',\'close\') NOT NULL  DEFAULT normal COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom', 'is_robot')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom') . ' ADD `is_robot` enum(\'y\',\'n\')   DEFAULT n COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom_defriend')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom_defriend', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom_defriend') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom_defriend')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom_defriend', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom_defriend') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom_defriend')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom_defriend', 'room_id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom_defriend') . ' ADD `room_id` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom_defriend')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom_defriend', 'openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom_defriend') . ' ADD `openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom_defriend')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom_defriend', 'creator')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom_defriend') . ' ADD `creator` varchar(50) NOT NULL  DEFAULT system COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom_defriend')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom_defriend', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom_defriend') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom_defriend')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom_defriend', 'status')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom_defriend') . ' ADD `status` enum(\'y\',\'n\') NOT NULL  DEFAULT n COMMENT \'y defrend n relieve\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom_log')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom_log', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom_log') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom_log')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom_log', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom_log') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom_log')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom_log', 'rid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom_log') . ' ADD `rid` int(10) NOT NULL   COMMENT \'聊天室ID\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom_log')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom_log', 'openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom_log') . ' ADD `openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom_log')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom_log', 'content')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom_log') . ' ADD `content` varchar(200) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom_log')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom_log', 'type')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom_log') . ' ADD `type` enum(\'text\',\'voice\',\'album\',\'redpack\',\'room_money\',\'gift\')   DEFAULT text COMMENT \'消息类型\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom_log')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom_log', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom_log') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom_log')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom_log', 'is_del')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom_log') . ' ADD `is_del` enum(\'y\',\'n\')   DEFAULT n COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_chatroom_log')) {
    if (!pdo_fieldexists('sunshine_huayue_chatroom_log', 'complain_times')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_chatroom_log') . ' ADD `complain_times` int(10) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_comment')) {
    if (!pdo_fieldexists('sunshine_huayue_comment', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_comment') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_comment')) {
    if (!pdo_fieldexists('sunshine_huayue_comment', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_comment') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_comment')) {
    if (!pdo_fieldexists('sunshine_huayue_comment', 'mid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_comment') . ' ADD `mid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_comment')) {
    if (!pdo_fieldexists('sunshine_huayue_comment', 'comment_openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_comment') . ' ADD `comment_openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_comment')) {
    if (!pdo_fieldexists('sunshine_huayue_comment', 'user_openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_comment') . ' ADD `user_openid` varchar(50) NOT NULL   COMMENT \'记录创建人\';');
    }
}
if (pdo_tableexists('sunshine_huayue_comment')) {
    if (!pdo_fieldexists('sunshine_huayue_comment', 'reply_openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_comment') . ' ADD `reply_openid` varchar(50) NOT NULL   COMMENT \'被回复的人\';');
    }
}
if (pdo_tableexists('sunshine_huayue_comment')) {
    if (!pdo_fieldexists('sunshine_huayue_comment', 'content')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_comment') . ' ADD `content` varchar(500) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_comment')) {
    if (!pdo_fieldexists('sunshine_huayue_comment', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_comment') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_comment')) {
    if (!pdo_fieldexists('sunshine_huayue_comment', 'is_del')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_comment') . ' ADD `is_del` enum(\'y\',\'n\')   DEFAULT n COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_comment')) {
    if (!pdo_fieldexists('sunshine_huayue_comment', 'del_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_comment') . ' ADD `del_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_credit')) {
    if (!pdo_fieldexists('sunshine_huayue_credit', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_credit') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_credit')) {
    if (!pdo_fieldexists('sunshine_huayue_credit', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_credit') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_credit')) {
    if (!pdo_fieldexists('sunshine_huayue_credit', 'openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_credit') . ' ADD `openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_credit')) {
    if (!pdo_fieldexists('sunshine_huayue_credit', 'sid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_credit') . ' ADD `sid` int(10) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_credit')) {
    if (!pdo_fieldexists('sunshine_huayue_credit', 'credit')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_credit') . ' ADD `credit` int(10) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_credit')) {
    if (!pdo_fieldexists('sunshine_huayue_credit', 'type')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_credit') . ' ADD `type` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_credit')) {
    if (!pdo_fieldexists('sunshine_huayue_credit', 'add_date')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_credit') . ' ADD `add_date` date NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_credit')) {
    if (!pdo_fieldexists('sunshine_huayue_credit', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_credit') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_defriend')) {
    if (!pdo_fieldexists('sunshine_huayue_defriend', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_defriend') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_defriend')) {
    if (!pdo_fieldexists('sunshine_huayue_defriend', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_defriend') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_defriend')) {
    if (!pdo_fieldexists('sunshine_huayue_defriend', 'openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_defriend') . ' ADD `openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_defriend')) {
    if (!pdo_fieldexists('sunshine_huayue_defriend', 'defriend_openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_defriend') . ' ADD `defriend_openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_defriend')) {
    if (!pdo_fieldexists('sunshine_huayue_defriend', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_defriend') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_defriend')) {
    if (!pdo_fieldexists('sunshine_huayue_defriend', 'status')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_defriend') . ' ADD `status` enum(\'y\',\'n\') NOT NULL  DEFAULT n COMMENT \'y defrend n relieve\';');
    }
}
if (pdo_tableexists('sunshine_huayue_draw_log')) {
    if (!pdo_fieldexists('sunshine_huayue_draw_log', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_draw_log') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_draw_log')) {
    if (!pdo_fieldexists('sunshine_huayue_draw_log', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_draw_log') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_draw_log')) {
    if (!pdo_fieldexists('sunshine_huayue_draw_log', 'openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_draw_log') . ' ADD `openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_draw_log')) {
    if (!pdo_fieldexists('sunshine_huayue_draw_log', 'money')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_draw_log') . ' ADD `money` decimal(8,2) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_draw_log')) {
    if (!pdo_fieldexists('sunshine_huayue_draw_log', 'commision')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_draw_log') . ' ADD `commision` decimal(8,2) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_draw_log')) {
    if (!pdo_fieldexists('sunshine_huayue_draw_log', 'act_draw')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_draw_log') . ' ADD `act_draw` decimal(8,2) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_draw_log')) {
    if (!pdo_fieldexists('sunshine_huayue_draw_log', 'status')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_draw_log') . ' ADD `status` enum(\'wait\',\'handle\')   DEFAULT wait COMMENT \'提现状态\';');
    }
}
if (pdo_tableexists('sunshine_huayue_draw_log')) {
    if (!pdo_fieldexists('sunshine_huayue_draw_log', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_draw_log') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_draw_log')) {
    if (!pdo_fieldexists('sunshine_huayue_draw_log', 'update_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_draw_log') . ' ADD `update_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_feedback')) {
    if (!pdo_fieldexists('sunshine_huayue_feedback', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_feedback') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_feedback')) {
    if (!pdo_fieldexists('sunshine_huayue_feedback', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_feedback') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_feedback')) {
    if (!pdo_fieldexists('sunshine_huayue_feedback', 'openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_feedback') . ' ADD `openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_feedback')) {
    if (!pdo_fieldexists('sunshine_huayue_feedback', 'content')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_feedback') . ' ADD `content` varchar(300) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_feedback')) {
    if (!pdo_fieldexists('sunshine_huayue_feedback', 'status')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_feedback') . ' ADD `status` enum(\'wait\',\'handle\')   DEFAULT wait COMMENT \'处理状态\';');
    }
}
if (pdo_tableexists('sunshine_huayue_feedback')) {
    if (!pdo_fieldexists('sunshine_huayue_feedback', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_feedback') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_feedback')) {
    if (!pdo_fieldexists('sunshine_huayue_feedback', 'update_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_feedback') . ' ADD `update_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift')) {
    if (!pdo_fieldexists('sunshine_huayue_gift', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift')) {
    if (!pdo_fieldexists('sunshine_huayue_gift', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift')) {
    if (!pdo_fieldexists('sunshine_huayue_gift', 'sort_id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift') . ' ADD `sort_id` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift')) {
    if (!pdo_fieldexists('sunshine_huayue_gift', 'gift_name')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift') . ' ADD `gift_name` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift')) {
    if (!pdo_fieldexists('sunshine_huayue_gift', 'gift_price')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift') . ' ADD `gift_price` decimal(8,2) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift')) {
    if (!pdo_fieldexists('sunshine_huayue_gift', 'gift_pic')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift') . ' ADD `gift_pic` varchar(200) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift')) {
    if (!pdo_fieldexists('sunshine_huayue_gift', 'sale_num')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift') . ' ADD `sale_num` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift')) {
    if (!pdo_fieldexists('sunshine_huayue_gift', 'use_num')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift') . ' ADD `use_num` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift')) {
    if (!pdo_fieldexists('sunshine_huayue_gift', 'is_del')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift') . ' ADD `is_del` enum(\'y\',\'n\')   DEFAULT n COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift')) {
    if (!pdo_fieldexists('sunshine_huayue_gift', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift_order')) {
    if (!pdo_fieldexists('sunshine_huayue_gift_order', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift_order') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift_order')) {
    if (!pdo_fieldexists('sunshine_huayue_gift_order', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift_order') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift_order')) {
    if (!pdo_fieldexists('sunshine_huayue_gift_order', 'openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift_order') . ' ADD `openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift_order')) {
    if (!pdo_fieldexists('sunshine_huayue_gift_order', 'gift_data')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift_order') . ' ADD `gift_data` varchar(2000) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift_order')) {
    if (!pdo_fieldexists('sunshine_huayue_gift_order', 'pay_money')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift_order') . ' ADD `pay_money` decimal(8,2) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift_order')) {
    if (!pdo_fieldexists('sunshine_huayue_gift_order', 'status')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift_order') . ' ADD `status` enum(\'wait\',\'payed\') NOT NULL  DEFAULT wait COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift_order')) {
    if (!pdo_fieldexists('sunshine_huayue_gift_order', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift_order') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift_present_log')) {
    if (!pdo_fieldexists('sunshine_huayue_gift_present_log', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift_present_log') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift_present_log')) {
    if (!pdo_fieldexists('sunshine_huayue_gift_present_log', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift_present_log') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift_present_log')) {
    if (!pdo_fieldexists('sunshine_huayue_gift_present_log', 'openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift_present_log') . ' ADD `openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift_present_log')) {
    if (!pdo_fieldexists('sunshine_huayue_gift_present_log', 'to_openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift_present_log') . ' ADD `to_openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift_present_log')) {
    if (!pdo_fieldexists('sunshine_huayue_gift_present_log', 'rid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift_present_log') . ' ADD `rid` int(10) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift_present_log')) {
    if (!pdo_fieldexists('sunshine_huayue_gift_present_log', 'gift_id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift_present_log') . ' ADD `gift_id` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift_present_log')) {
    if (!pdo_fieldexists('sunshine_huayue_gift_present_log', 'gift_price')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift_present_log') . ' ADD `gift_price` decimal(8,2) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift_present_log')) {
    if (!pdo_fieldexists('sunshine_huayue_gift_present_log', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift_present_log') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift_user')) {
    if (!pdo_fieldexists('sunshine_huayue_gift_user', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift_user') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift_user')) {
    if (!pdo_fieldexists('sunshine_huayue_gift_user', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift_user') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift_user')) {
    if (!pdo_fieldexists('sunshine_huayue_gift_user', 'openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift_user') . ' ADD `openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift_user')) {
    if (!pdo_fieldexists('sunshine_huayue_gift_user', 'gift_id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift_user') . ' ADD `gift_id` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift_user')) {
    if (!pdo_fieldexists('sunshine_huayue_gift_user', 'gift_num')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift_user') . ' ADD `gift_num` int(10) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_gift_user')) {
    if (!pdo_fieldexists('sunshine_huayue_gift_user', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_gift_user') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_greets')) {
    if (!pdo_fieldexists('sunshine_huayue_greets', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_greets') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_greets')) {
    if (!pdo_fieldexists('sunshine_huayue_greets', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_greets') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_greets')) {
    if (!pdo_fieldexists('sunshine_huayue_greets', 'start_openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_greets') . ' ADD `start_openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_greets')) {
    if (!pdo_fieldexists('sunshine_huayue_greets', 'to_openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_greets') . ' ADD `to_openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_greets')) {
    if (!pdo_fieldexists('sunshine_huayue_greets', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_greets') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_greets')) {
    if (!pdo_fieldexists('sunshine_huayue_greets', 'readed')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_greets') . ' ADD `readed` enum(\'y\',\'n\')   DEFAULT n COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_growth')) {
    if (!pdo_fieldexists('sunshine_huayue_growth', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_growth') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_growth')) {
    if (!pdo_fieldexists('sunshine_huayue_growth', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_growth') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_growth')) {
    if (!pdo_fieldexists('sunshine_huayue_growth', 'openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_growth') . ' ADD `openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_growth')) {
    if (!pdo_fieldexists('sunshine_huayue_growth', 'score')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_growth') . ' ADD `score` int(10) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_growth')) {
    if (!pdo_fieldexists('sunshine_huayue_growth', 'intro')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_growth') . ' ADD `intro` varchar(100) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_growth')) {
    if (!pdo_fieldexists('sunshine_huayue_growth', 'add_date')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_growth') . ' ADD `add_date` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_growth')) {
    if (!pdo_fieldexists('sunshine_huayue_growth', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_growth') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_letv')) {
    if (!pdo_fieldexists('sunshine_huayue_letv', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_letv') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_letv')) {
    if (!pdo_fieldexists('sunshine_huayue_letv', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_letv') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_letv')) {
    if (!pdo_fieldexists('sunshine_huayue_letv', 'rid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_letv') . ' ADD `rid` int(10) NOT NULL   COMMENT \'聊天室ID\';');
    }
}
if (pdo_tableexists('sunshine_huayue_letv')) {
    if (!pdo_fieldexists('sunshine_huayue_letv', 'openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_letv') . ' ADD `openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_letv')) {
    if (!pdo_fieldexists('sunshine_huayue_letv', 'activity_id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_letv') . ' ADD `activity_id` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_letv')) {
    if (!pdo_fieldexists('sunshine_huayue_letv', 'push_url')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_letv') . ' ADD `push_url` varchar(200) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_letv')) {
    if (!pdo_fieldexists('sunshine_huayue_letv', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_letv') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_lvb')) {
    if (!pdo_fieldexists('sunshine_huayue_lvb', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_lvb') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_lvb')) {
    if (!pdo_fieldexists('sunshine_huayue_lvb', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_lvb') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_lvb')) {
    if (!pdo_fieldexists('sunshine_huayue_lvb', 'rid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_lvb') . ' ADD `rid` int(10) NOT NULL   COMMENT \'聊天室ID\';');
    }
}
if (pdo_tableexists('sunshine_huayue_lvb')) {
    if (!pdo_fieldexists('sunshine_huayue_lvb', 'openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_lvb') . ' ADD `openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_lvb')) {
    if (!pdo_fieldexists('sunshine_huayue_lvb', 'channel_id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_lvb') . ' ADD `channel_id` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_lvb')) {
    if (!pdo_fieldexists('sunshine_huayue_lvb', 'protocol')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_lvb') . ' ADD `protocol` varchar(10) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_lvb')) {
    if (!pdo_fieldexists('sunshine_huayue_lvb', 'upstream_address')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_lvb') . ' ADD `upstream_address` varchar(200) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_lvb')) {
    if (!pdo_fieldexists('sunshine_huayue_lvb', 'rate_type')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_lvb') . ' ADD `rate_type` varchar(10) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_lvb')) {
    if (!pdo_fieldexists('sunshine_huayue_lvb', 'rtmp_downstream_address')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_lvb') . ' ADD `rtmp_downstream_address` varchar(200) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_lvb')) {
    if (!pdo_fieldexists('sunshine_huayue_lvb', 'flv_downstream_address')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_lvb') . ' ADD `flv_downstream_address` varchar(200) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_lvb')) {
    if (!pdo_fieldexists('sunshine_huayue_lvb', 'hls_downstream_address')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_lvb') . ' ADD `hls_downstream_address` varchar(200) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_lvb')) {
    if (!pdo_fieldexists('sunshine_huayue_lvb', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_lvb') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `uniacid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'acid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `acid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'account')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `account` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'use_times')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `use_times` int(10) NOT NULL  DEFAULT 0 COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'nickname')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `nickname` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'sex')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `sex` varchar(20) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'province')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `province` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'city')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `city` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'country')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `country` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'headimgurl')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `headimgurl` varchar(200) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'privilege')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `privilege` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'unionid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `unionid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'position')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `position` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'update_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `update_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'bechecked')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `bechecked` int(11) NOT NULL  DEFAULT 0 COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'lng')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `lng` varchar(50) NOT NULL   COMMENT \'经度\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'lat')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `lat` varchar(50) NOT NULL   COMMENT \'纬度\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'choose_sex')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `choose_sex` varchar(50) NOT NULL   COMMENT \'查看性别\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'age')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `age` varchar(10) NOT NULL  DEFAULT 0 COMMENT \'年龄\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'sign')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `sign` varchar(50) NOT NULL   COMMENT \'个人签名\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'isvisible')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `isvisible` varchar(10) NOT NULL  DEFAULT close COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'is_notice')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `is_notice` enum(\'y\',\'n\') NOT NULL  DEFAULT y COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'notice_times')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `notice_times` int(10) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'growth_score')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `growth_score` int(10) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'vip_level')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `vip_level` int(10) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'vip_add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `vip_add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'vip_end_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `vip_end_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'forbid_status')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `forbid_status` enum(\'y\',\'n\')   DEFAULT n COMMENT \'系统移除状态\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'forbid_add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `forbid_add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'forbid_end_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `forbid_end_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'mobile')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `mobile` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'mobile_status')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `mobile_status` enum(\'y\',\'n\')   DEFAULT n COMMENT \'手机号验证\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'mobile_captcha')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `mobile_captcha` int(6) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'mobile_captcha_send_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `mobile_captcha_send_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'work')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `work` varchar(20) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'avaliable_money')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `avaliable_money` decimal(8,2) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_member')) {
    if (!pdo_fieldexists('sunshine_huayue_member', 'draw_money')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_member') . ' ADD `draw_money` decimal(8,2) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_menu')) {
    if (!pdo_fieldexists('sunshine_huayue_menu', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_menu') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_menu')) {
    if (!pdo_fieldexists('sunshine_huayue_menu', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_menu') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_menu')) {
    if (!pdo_fieldexists('sunshine_huayue_menu', 'type')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_menu') . ' ADD `type` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_menu')) {
    if (!pdo_fieldexists('sunshine_huayue_menu', 'order_id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_menu') . ' ADD `order_id` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_menu')) {
    if (!pdo_fieldexists('sunshine_huayue_menu', 'name')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_menu') . ' ADD `name` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_menu')) {
    if (!pdo_fieldexists('sunshine_huayue_menu', 'url')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_menu') . ' ADD `url` varchar(200) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_menu')) {
    if (!pdo_fieldexists('sunshine_huayue_menu', 'intro')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_menu') . ' ADD `intro` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_menu')) {
    if (!pdo_fieldexists('sunshine_huayue_menu', 'is_del')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_menu') . ' ADD `is_del` enum(\'y\',\'n\')   DEFAULT n COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_menu')) {
    if (!pdo_fieldexists('sunshine_huayue_menu', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_menu') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_moments')) {
    if (!pdo_fieldexists('sunshine_huayue_moments', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_moments') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_moments')) {
    if (!pdo_fieldexists('sunshine_huayue_moments', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_moments') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_moments')) {
    if (!pdo_fieldexists('sunshine_huayue_moments', 'openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_moments') . ' ADD `openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_moments')) {
    if (!pdo_fieldexists('sunshine_huayue_moments', 'remark')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_moments') . ' ADD `remark` varchar(200) NOT NULL   COMMENT \'想法\';');
    }
}
if (pdo_tableexists('sunshine_huayue_moments')) {
    if (!pdo_fieldexists('sunshine_huayue_moments', 'type')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_moments') . ' ADD `type` enum(\'image\',\'text\')   DEFAULT image COMMENT \'类型，决定是否去查询图片表\';');
    }
}
if (pdo_tableexists('sunshine_huayue_moments')) {
    if (!pdo_fieldexists('sunshine_huayue_moments', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_moments') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_moments')) {
    if (!pdo_fieldexists('sunshine_huayue_moments', 'is_del')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_moments') . ' ADD `is_del` enum(\'y\',\'n\')   DEFAULT n COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_moments')) {
    if (!pdo_fieldexists('sunshine_huayue_moments', 'del_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_moments') . ' ADD `del_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_multisend')) {
    if (!pdo_fieldexists('sunshine_huayue_multisend', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_multisend') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_multisend')) {
    if (!pdo_fieldexists('sunshine_huayue_multisend', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_multisend') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_multisend')) {
    if (!pdo_fieldexists('sunshine_huayue_multisend', 'creator')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_multisend') . ' ADD `creator` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_multisend')) {
    if (!pdo_fieldexists('sunshine_huayue_multisend', 'content')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_multisend') . ' ADD `content` varchar(500) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_multisend')) {
    if (!pdo_fieldexists('sunshine_huayue_multisend', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_multisend') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_mychatroom_history')) {
    if (!pdo_fieldexists('sunshine_huayue_mychatroom_history', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_mychatroom_history') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_mychatroom_history')) {
    if (!pdo_fieldexists('sunshine_huayue_mychatroom_history', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_mychatroom_history') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_mychatroom_history')) {
    if (!pdo_fieldexists('sunshine_huayue_mychatroom_history', 'openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_mychatroom_history') . ' ADD `openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_mychatroom_history')) {
    if (!pdo_fieldexists('sunshine_huayue_mychatroom_history', 'room_id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_mychatroom_history') . ' ADD `room_id` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_mychatroom_history')) {
    if (!pdo_fieldexists('sunshine_huayue_mychatroom_history', 'update_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_mychatroom_history') . ' ADD `update_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_mychatroom_history')) {
    if (!pdo_fieldexists('sunshine_huayue_mychatroom_history', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_mychatroom_history') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_mychatroom_history')) {
    if (!pdo_fieldexists('sunshine_huayue_mychatroom_history', 'is_del')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_mychatroom_history') . ' ADD `is_del` enum(\'y\',\'n\') NOT NULL  DEFAULT n COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_rewards')) {
    if (!pdo_fieldexists('sunshine_huayue_rewards', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_rewards') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_rewards')) {
    if (!pdo_fieldexists('sunshine_huayue_rewards', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_rewards') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_rewards')) {
    if (!pdo_fieldexists('sunshine_huayue_rewards', 'room_id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_rewards') . ' ADD `room_id` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_rewards')) {
    if (!pdo_fieldexists('sunshine_huayue_rewards', 'openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_rewards') . ' ADD `openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_rewards')) {
    if (!pdo_fieldexists('sunshine_huayue_rewards', 'to_openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_rewards') . ' ADD `to_openid` varchar(50) NOT NULL  DEFAULT system COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_rewards')) {
    if (!pdo_fieldexists('sunshine_huayue_rewards', 'status')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_rewards') . ' ADD `status` enum(\'y\',\'n\') NOT NULL  DEFAULT n COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_rewards')) {
    if (!pdo_fieldexists('sunshine_huayue_rewards', 'money')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_rewards') . ' ADD `money` varchar(20) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_rewards')) {
    if (!pdo_fieldexists('sunshine_huayue_rewards', 'money_type')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_rewards') . ' ADD `money_type` varchar(20) NOT NULL  DEFAULT money_rewards COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_rewards')) {
    if (!pdo_fieldexists('sunshine_huayue_rewards', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_rewards') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_rewards')) {
    if (!pdo_fieldexists('sunshine_huayue_rewards', 'update_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_rewards') . ' ADD `update_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_setting')) {
    if (!pdo_fieldexists('sunshine_huayue_setting', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_setting') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_setting')) {
    if (!pdo_fieldexists('sunshine_huayue_setting', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_setting') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_setting')) {
    if (!pdo_fieldexists('sunshine_huayue_setting', 'name')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_setting') . ' ADD `name` varchar(200) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_setting')) {
    if (!pdo_fieldexists('sunshine_huayue_setting', 'value')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_setting') . ' ADD `value` varchar(200) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_setting')) {
    if (!pdo_fieldexists('sunshine_huayue_setting', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_setting') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_voice_log')) {
    if (!pdo_fieldexists('sunshine_huayue_voice_log', 'id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_voice_log') . ' ADD `id` int(10) NOT NULL auto_increment  COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_voice_log')) {
    if (!pdo_fieldexists('sunshine_huayue_voice_log', 'uniacid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_voice_log') . ' ADD `uniacid` int(10) unsigned NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_voice_log')) {
    if (!pdo_fieldexists('sunshine_huayue_voice_log', 'openid')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_voice_log') . ' ADD `openid` varchar(50) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_voice_log')) {
    if (!pdo_fieldexists('sunshine_huayue_voice_log', 'r_log_id')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_voice_log') . ' ADD `r_log_id` int(10) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_voice_log')) {
    if (!pdo_fieldexists('sunshine_huayue_voice_log', 'voice_path')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_voice_log') . ' ADD `voice_path` varchar(100) NOT NULL   COMMENT \'\';');
    }
}
if (pdo_tableexists('sunshine_huayue_voice_log')) {
    if (!pdo_fieldexists('sunshine_huayue_voice_log', 'add_time')) {
        pdo_query('ALTER TABLE ' . tablename('sunshine_huayue_voice_log') . ' ADD `add_time` datetime NOT NULL   COMMENT \'\';');
    }
}