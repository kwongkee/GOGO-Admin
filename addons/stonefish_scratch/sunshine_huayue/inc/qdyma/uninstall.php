<?php

$sql =<<<EOF
DROP TABLE IF EXISTS `ims_sunshine_huayue_admin`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_album`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_chat`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_chatmessage`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_chatroom`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_chatroom_defriend`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_chatroom_log`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_comment`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_credit`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_defriend`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_draw_log`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_feedback`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_gift`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_gift_order`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_gift_present_log`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_gift_user`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_greets`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_growth`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_letv`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_lvb`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_member`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_menu`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_moments`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_multisend`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_mychatroom_history`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_rewards`;
DROP TABLE IF EXISTS `ims_sunshine_huayue_setting`;
EOF;
pdo_run($sql);