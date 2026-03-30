<?php
if(!pdo_fieldexists('share_redpacket_setting', 'sp_bg')) {
    pdo_query("ALTER TABLE ".tablename('share_redpacket_setting')." ADD `sp_bg` varchar(255) DEFAULT '0';");
}

if(!pdo_fieldexists('share_redpacket_setting', 'ydfx')) {
    pdo_query("ALTER TABLE ".tablename('share_redpacket_setting')." ADD `ydfx` varchar(255) DEFAULT '0';");
}

if(!pdo_fieldexists('share_redpacket_setting', 'getip')) {
    pdo_query("ALTER TABLE ".tablename('share_redpacket_setting')." ADD `getip` tinyint(1) DEFAULT '0';");
}

if(!pdo_fieldexists('share_redpacket_setting', 'getip_addr')) {
    pdo_query("ALTER TABLE ".tablename('share_redpacket_setting')." ADD `getip_addr` text NOT NULL COMMENT '限制地区ip';");
}

if(!pdo_fieldexists('share_redpacket_red_packet', 'pool_amount')) {
    pdo_query("ALTER TABLE ".tablename('share_redpacket_red_packet')." ADD `pool_amount` int(10) DEFAULT '0';");
}

if(!pdo_fieldexists('share_redpacket_red_packet', 'send_amount')) {
    pdo_query("ALTER TABLE ".tablename('share_redpacket_red_packet')." ADD `send_amount` int(10) DEFAULT '0';");
}

if(!pdo_fieldexists('share_redpacket_user', 'ispay')) {
    pdo_query("ALTER TABLE ".tablename('share_redpacket_user')." ADD `ispay` tinyint(1) DEFAULT '0';");
}