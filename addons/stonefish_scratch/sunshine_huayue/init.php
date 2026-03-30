<?php
/*
初始化文件
用于加载扩展类，和数据库操作
*/
// 扩展
require_once dirname(__FILE__).'/Exception/Function.php';
require_once dirname(__FILE__).'/Exception/S.class.php';


// 数据库操作
require_once dirname(__FILE__).'/Model/DefriendModel.class.php';
require_once dirname(__FILE__).'/Model/MemberModel.class.php';
require_once dirname(__FILE__).'/Model/AdminModel.class.php';
require_once dirname(__FILE__).'/Model/ChatroomLogModel.class.php';
require_once dirname(__FILE__).'/Model/ChatroomDefriendModel.class.php';
require_once dirname(__FILE__).'/Model/ChatroomModel.class.php';
require_once dirname(__FILE__).'/Model/GrowthModel.class.php';
require_once dirname(__FILE__).'/Model/CreditModel.class.php';
require_once dirname(__FILE__).'/Model/AlbumModel.class.php';
require_once dirname(__FILE__).'/Model/CommentModel.class.php';
require_once dirname(__FILE__).'/Model/SettingsModel.class.php';
require_once dirname(__FILE__).'/Model/ChatModel.class.php';
require_once dirname(__FILE__).'/Model/ChatMessageModel.class.php';
require_once dirname(__FILE__).'/Model/MomentsModel.class.php';
require_once dirname(__FILE__).'/Model/RewardsModel.class.php';
require_once dirname(__FILE__).'/Model/DrawLogModel.class.php';
require_once dirname(__FILE__).'/Model/FeedbackModel.class.php';
require_once dirname(__FILE__).'/Model/MenuModel.class.php';
require_once dirname(__FILE__).'/Model/LvbModel.class.php';
require_once dirname(__FILE__).'/Model/LetvModel.class.php';
require_once dirname(__FILE__).'/Model/GiftModel.class.php';
require_once dirname(__FILE__).'/Model/GiftUserModel.class.php';
require_once dirname(__FILE__).'/Model/GiftOrderModel.class.php';
require_once dirname(__FILE__).'/Model/GiftPresentLogModel.class.php';
require_once dirname(__FILE__).'/Model/VoiceLogModel.class.php';

// 组件
require_once dirname(__FILE__).'/Component/SmsComponent.class.php';
require_once dirname(__FILE__).'/Component/MemberComponent.class.php';
require_once dirname(__FILE__).'/Component/SaveCertComponent.class.php';
require_once dirname(__FILE__).'/Component/NoticeComponent.class.php';
require_once dirname(__FILE__).'/Component/FilterComponent.class.php';
require_once dirname(__FILE__).'/Component/ForbidComponent.class.php';
require_once dirname(__FILE__).'/Component/ChatRobotComponent.class.php';
require_once dirname(__FILE__).'/Component/VipComponent.class.php';
require_once dirname(__FILE__).'/Component/DrawLogComponent.class.php';
require_once dirname(__FILE__).'/Component/RedPackComponent.class.php';
require_once dirname(__FILE__).'/Component/LiveVideoComponent.class.php';
require_once dirname(__FILE__).'/Component/LivePlayComponent.class.php';
require_once dirname(__FILE__).'/Component/OnlineComponent.class.php';

// 业务逻辑
require_once dirname(__FILE__).'/Business/NoticeBusiness.class.php';
require_once dirname(__FILE__).'/Business/LetvPlayBusiness.class.php';