<?php

defined('IN_IA') or exit('Access Denied');
require_once dirname(__FILE__) . '/init.php';
class sunshine_huayueModuleSite extends WeModuleSite{
    public $tree = array();
    public $userinfo;
    static $_SET;
    static $openid;
    public $super_title;
    public $super_follow = false;
    public $gps_key = 'close';
    public $menu ;
    public $vip;
    public function __construct(){
        global $_W, $_GPC;
        if(!$_W['username']){
            load() -> model('mc');
            $userinfo = mc_oauth_userinfo($_W['account']['acid']);
            self :: $openid = $userinfo['openid'];
            if(!$_COOKIE['refresh_uinfo_key_huayue'] || $_COOKIE['refresh_uinfo_key_huayue'] != $userinfo['openid']){
                if($userinfo['openid']){
                    MemberModel :: appMember($userinfo);
                    setcookie("refresh_uinfo_key_huayue", $userinfo['openid'], time() + 3600 * 1);
                }
            }
            MemberModel :: flushNoticeTimes(self :: $openid);
            $this -> super_follow = $_W['fans']['follow'] == 1 ? true : false;
            if(!ForbidComponent :: isAllow(self :: $openid)){
                self :: $openid = '';
            }
            if($_GPC['debugger']){
                self :: $openid = 'osU8iwcRGotMSJ3nca4m-POARyxQ';
            }
            $vip = new VipComponent(self :: $openid);
            $this -> vip = $vip -> isVip();
            $this -> superpoint_menu = MenuModel :: getListByType('superpoint');
        }
        if($_GPC['debugger']){
            self :: $openid = 'osU8iwcRGotMSJ3nca4m-POARyxQ';
        }
        $this -> settings = SettingsModel :: all();
        self :: $_SET = $this -> settings;
        $this -> super_title = $this -> settings['super_title']?$this -> settings['super_title']:'超级粉丝社区';
        $this -> share_title = $this -> settings['share_title'];
        $this -> share_content = $this -> settings['share_content'];
        $this -> remind_key = true;
    }
    public function doMobileIndex(){
        global $_GPC, $_W;
        if(!ForbidComponent :: isAllow(self :: $openid)){
            include $this -> template('forbid');
            exit;
        }
        $userinfo = MemberModel :: info(self :: $openid);
        $this -> share_img = $userinfo['headimgurl'];
        $count_all = MemberModel :: sumCounts();
        $growth_level = S :: userLevel($userinfo['growth_score']);
        load() -> model('mc');
        $credit = mc_credit_fetch($_W['member']['uid'], array('credit1'));
        GrowthModel :: add(self :: $openid, 1, 'Index用户登录');
        $signin_list = CreditModel :: signLog(self :: $openid);
        $newmomentslist = MomentsModel :: getNewList(0, 5);
        $hotmomentslist = MomentsModel :: getHotList(0, 5);
        include $this -> template('index_new');
    }
    public function doMobileHall(){
        global $_GPC, $_W;
        $this -> gps_key = 'open';
        $this -> menu = 'hall';
        $userinfo = MemberModel :: info(self :: $openid);
        $choose_type = $_GPC['choose_type'];
        switch ($choose_type){
        case '1': break;
        case '2': break;
        default: $choose_type = 'all';
            break;
        }
        $today_num = MemberModel :: todayAddNum();
        $sum_num = MemberModel :: sumCounts();
        include $this -> template('hall');
    }
    public function doMobileloadNewList(){
        global $_GPC, $_W;
        $page = $_GPC['page'];
        !$page && $page = 1;
        $pagesize = 32;
        $begin = ($page-1) * $pagesize;
        $choose_type = $_GPC['choose_type'];
        $sex = $choose_type == 'all' ? '' : $choose_type;
        $list = MemberModel :: userList(self :: $openid, $sex, $begin, $pagesize);
        if(!$list){
            echoJson(array('res' => '101', 'msg' => 'success', 'list' => $list));
        }
        $info = MemberModel :: info(self :: $openid);
        foreach ($list as $key => & $value){
            if(!$info['lng'] || !$info['lat'] || !$value['lng'] || !$value['lat']){
                $value['distance'] = '未知';
                $value['unit'] = '';
            }else{
                $l = S :: GetShortDistance($value['lng'], $value['lat'], $info['lng'], $info['lat']);
                $convert_l = S :: ConvertDistance($l);
                $value['distance'] = $convert_l['distance'];
                $value['unit'] = $convert_l['unit'];
            }
            $last_time = S :: timeDiff($value['update_time']);
            $value['last_time'] = $last_time['str'] . $last_time['unit'];
        }
        echoJson(array('res' => '100', 'msg' => 'success', 'list' => $list));
    }
    public function doMobileUpdatelnglat(){
        global $_W, $_GPC;
        if(!$_GPC['lng'] || !$_GPC['lat']){
            echoJson(array('res' => '101', 'msg' => 'can not get your position,please retry!' . $_GPC['lng'] . $_GPC['lat']));
        }
        $data = array();
        $data['lng'] = $_GPC['lng'];
        $data['lat'] = $_GPC['lat'];
        $r = MemberModel :: update($data, array('openid' => self :: $openid, 'uniacid' => $_W['uniacid']));
        if($r === false){
            echoJson(array('res' => '101', 'msg' => 'update error'));
        }
        echoJson(array('res' => '100', 'msg' => 'success'));
    }
    public function doMobileList(){
        global $_GPC, $_W;
        $this -> gps_key = 'open';
        $this -> super_title = '附近的人';
        $this -> menu = 'list';
        include $this -> template('list');
    }
    public function doMobileListload(){
        global $_W, $_GPC;
        $info = MemberModel :: info(self :: $openid);
        if(!$info){
            echoJson(array('res' => '101', 'msg' => 'info empty'));
        }
        $page = $_GPC['page'];
        $page || $page = 1;
        $pagesize = 20;
        $begin = ($page-1) * $pagesize;
        $list = MemberModel :: nearbyList(self :: $openid, $begin, $pagesize);
        if(empty($list)){
            echoJson(array('res' => '102', 'msg' => 'empty'));
        }
        foreach ($list as $key => & $value){
            $l = S :: GetShortDistance($value['lng'], $value['lat'], $info['lng'], $info['lat']);
            $convert_l = S :: ConvertDistance($l);
            $value['distance'] = $convert_l['distance'];
            $value['unit'] = $convert_l['unit'];
            $last_time = S :: timeDiff($value['update_time']);
            $value['last_time'] = $last_time['str'] . $last_time['unit'];
        }
        echoJson(array('res' => '100', 'msg' => 'success', 'list' => $list));
    }
    public function doMobileDistance(){
        global $_GPC, $_W;
        $lng_1 = $_GPC['lng_1'];
        $lat_1 = $_GPC['lat_1'];
        $lng_2 = $_GPC['lng_2'];
        $lat_2 = $_GPC['lat_2'];
        if(!$lng_1 || !$lat_1 || !$lng_2 || !$lat_2){
            echoJson(array('res' => 101, 'msg' => 'param error'));
        }
        $l = S :: GetShortDistance($lng_1, $lat_1, $lng_2, $lat_2);
        $res = S :: ConvertDistance($l);
        echoJson(array('res' => 100, 'msg' => 'success', 'data' => $res));
    }
    public function doMobileUsercenter(){
        global $_GPC, $_W;
        $this -> super_title = '个人中心';
        $this -> menu = 'usercenter';
        $userinfo = MemberModel :: info(self :: $openid);
        if(!$userinfo){
            exit("访问错误，用户信息error");
        }
        include $this -> template('usercenter');
    }
    public function doMobileUserset(){
        global $_GPC, $_W;
        $this -> super_title = '个人资料设置';
        $this -> menu = 'usercenter';
        $userinfo = MemberModel :: info(self :: $openid);
        if(!$userinfo){
            exit("访问错误，用户信息error");
        }
        include $this -> template('userset_new');
    }
    public function doMobileUserupdate(){
        global $_GPC, $_W;
        $nickname = $_GPC['nickname'];
        $sign = $_GPC['usersign'];
        $age = $_GPC['age'];
        $sex = $_GPC['sex'];
        $choose_sex = $_GPC['choose_sex'];
        $work = $_GPC['work'];
        if(!self :: $openid){
            echoJson(array('res' => '101', 'msg' => 'openid is error'));
        }
        if(!$nickname){
            echoJson(array('res' => '101', 'msg' => '昵称不能为空'));
        }
        if(mb_strlen($nickname, 'utf8') > 20){
            echoJson(array('res' => '101', 'msg' => '昵称不能超过20个字符'));
        }
        if(mb_strlen($sign, 'utf8') > 20){
            echoJson(array('res' => '101', 'msg' => '签名不能超过20个字符'));
        }
        if(!$age){
            echoJson(array('res' => '101', 'msg' => '年龄不能为空'));
        }
        if(!is_numeric($age)){
            echoJson(array('res' => '101', 'msg' => '年龄只能是数字'));
        }
        if($age < 0 || $age > 200){
            echoJson(array('res' => '101', 'msg' => '年龄只能是0-200'));
        }
        if(!$sex){
            echoJson(array('res' => '101', 'msg' => '性别不能为空'));
        }
        if(!$choose_sex){
            echoJson(array('res' => '101', 'msg' => '请选择查看对象'));
        }
        if(!$work){
            echoJson(array('res' => '101', 'msg' => '职业不能为空'));
        }
        if(!is_numeric($age)){
            echoJson(array('res' => '101', 'msg' => '年龄必须是数字'));
        }
        if(!in_array($sex, array('男', '女'))){
            echoJson(array('res' => '101', 'msg' => '请选择性别'));
        }
        if($sex == '男'){
            $sex = 1;
        }elseif($sex == '女'){
            $sex = 2;
        }
        if(!in_array($choose_sex, array('帅哥', '美女'))){
            echoJson(array('res' => '101', 'msg' => '请选择查看性别'));
        }
        if($choose_sex == '帅哥'){
            $choose_sex = 1;
        }elseif($choose_sex == '美女'){
            $choose_sex = 2;
        }
        $data = array();
        $data['nickname'] = trim($nickname);
        $data['sign'] = trim($sign);
        $data['age'] = $age;
        $data['sex'] = $sex;
        $data['choose_sex'] = $choose_sex;
        $data['work'] = $work;
        $res = MemberModel :: update($data, array('openid' => self :: $openid, 'uniacid' => $_W['uniacid']));
        if($res === false){
            echoJson(array('res' => '101', 'msg' => '更新数据库失败'));
        }
        echoJson(array('res' => '100', 'msg' => 'success'));
    }
    public function doMobileVisible(){
        global $_GPC, $_W;
        if(!self :: $openid){
            echoJson(array('res' => '101', 'msg' => 'openid is error'));
        }
        if(!$_GPC['isvisible']){
            echoJson(array('res' => '101', 'msg' => 'param is error'));
        }
        if(!in_array($_GPC['isvisible'], array('open', 'close'))){
            echoJson(array('res' => '101', 'msg' => 'params is error'));
        }
        $res = MemberModel :: update(array('isvisible' => $_GPC['isvisible']), array('openid' => self :: $openid, 'uniacid' => $_W['uniacid']));
        if($res === false){
            echoJson(array('res' => '101', 'msg' => 'update error'));
        }
        echoJson(array('res' => '100', 'msg' => 'success'));
    }
    public function doMobileChangeNotice(){
        global $_GPC, $_W;
        if(!self :: $openid){
            echoJson(array('res' => '101', 'msg' => 'openid is error'));
        }
        if(!$_GPC['is_notice']){
            echoJson(array('res' => '101', 'msg' => 'param is error'));
        }
        if(!in_array($_GPC['is_notice'], array('y', 'n'))){
            echoJson(array('res' => '101', 'msg' => 'params is error'));
        }
        $res = MemberModel :: update(array('is_notice' => $_GPC['is_notice']), array('openid' => self :: $openid, 'uniacid' => $_W['uniacid']));
        if($res === false){
            echoJson(array('res' => '101', 'msg' => 'update error'));
        }
        echoJson(array('res' => '100', 'msg' => 'success'));
    }
    public function doMobileDetail(){
        global $_GPC, $_W;
        $detail_openid = $_GPC['detail_openid'];
        if(!$detail_openid){
            exit('非法访问');
        }
        $info = MemberModel :: info($detail_openid);
        if(!$info){
            exit('不存在的用户');
        }
        $this -> share_img = $info['headimgurl'];
        $info['address'] = $info['country'] . $info['province'] . $info['city'];
        $commentcount = pdo_fetch("select count(*) as num from " . tablename("sunshine_huayue_comment") . " where comment_openid='{$info['openid']}' and is_del='n' and uniacid={$_W['uniacid']}");
        $albumlist = AlbumModel :: getSliceList($info['openid'], 7);
        $login_info = MemberModel :: info(self :: $openid);
        $l = S :: GetShortDistance($login_info['lng'], $login_info['lat'], $info['lng'], $info['lat']);
        $convert_l = S :: ConvertDistance($l);
        if(!$info['lng'] || !$info['lat'] || !$login_info['lng'] || !$login_info['lat']){
            $convert_l['distance'] = '未知';
            $convert_l['unit'] = '';
        }
        $info['distance'] = $convert_l['distance'];
        $info['unit'] = $convert_l['unit'];
        $last_time = S :: timeDiff($info['update_time']);
        $info['last_refresh_time'] = $last_time['str'] . $last_time['unit'];
        $momentlist = MomentsModel :: loadMoments($info['openid']);
        $this -> super_title = $info['nickname'];
        $defriend_status = DefriendModel :: status(self :: $openid, $info['openid']);
        include $this -> template('detail');
    }
    public function doMobileCommentlist(){
        global $_W, $_GPC;
        if(!$_GPC['comment_openid']){
            exit('非法访问');
        }
        $comment_openid = $_GPC['comment_openid'];
        $info = MemberModel :: info($comment_openid);
        $this -> super_title = "收到的点评";
        $commentlist = CommentModel :: getList($comment_openid, 1, 10000);
        include $this -> template('commentlist');
    }
    public function doMobiledocomment(){
        global $_W, $_GPC;
        $userinfo = MemberModel :: info(self :: $openid);
        if(!$userinfo){
            echoJson(array("res" => "102", 'msg' => 'get userinfo fail'));
        }
        $mid = $_GPC['mid'];
        if(!$mid){
            echoJson(array("res" => "105", 'msg' => 'error'));
        }
        $defriend_status = DefriendModel :: status(self :: $openid, $_GPC['comment_openid']);
        if($defriend_status){
            echoJson(array("res" => "106", 'msg' => '对方屏蔽了你的消息，您不能对其动态进行点评~'));
        }
        $content = $_GPC['comment_content'];
        $user_openid = self :: $openid;
        $comment_openid = $_GPC['comment_openid'];
        if(!$user_openid || !$comment_openid){
            echoJson(array("res" => "103", 'msg' => 'openid is error'));
        }
        if(!$content){
            echoJson(array("res" => "103", 'msg' => '点评内容不能为空'));
        }
        $reply_openid = $_GPC['reply_openid'];
        $data = array();
        $data['uniacid'] = $_W['uniacid'];
        $data['mid'] = $mid;
        $data['comment_openid'] = $comment_openid;
        $data['content'] = $content;
        $data['user_openid'] = $user_openid;
        $data['reply_openid'] = $reply_openid;
        $data['add_time'] = date("Y-m-d H:i:s");
        $r = pdo_insert('sunshine_huayue_comment', $data);
        if($r === false){
            echoJson(array("res" => "102", 'msg' => '点评发布失败'));
        }
        if (self :: $openid != $comment_openid && $comment_openid != $reply_openid){
            $this -> sendNoticeTpl($comment_openid, '[新点评]' . $content, $_W['siteroot'] . 'app/' . $this -> createMobileUrl('momentdetail', array('mid' => $mid)));
        }
        if($reply_openid){
            $this -> sendNoticeTpl($reply_openid, '[新回复]' . $content, $_W['siteroot'] . 'app/' . $this -> createMobileUrl('momentdetail', array('mid' => $mid)));
        }
        echoJson(array("res" => "100", 'msg' => 'success'));
    }
    public function doMobilemycommentmanage(){
        global $_W, $_GPC;
        if(!self :: $openid){
            exit('非法访问');
        }
        $this -> super_title = "留言管理";
        $commentlist = CommentModel :: getList(self :: $openid, 1, 1000);
        include $this -> template('mycomment');
    }
    public function doMobilemycommentdelete(){
        global $_W, $_GPC;
        if(!self :: $openid){
            exit("error");
        }
        if(!$_GPC['comment_id']){
            echoJson(array("res" => "102", 'msg' => '缺少参数'));
        }
        $cid = $_GPC['comment_id'];
        CommentModel :: update(array('is_del' => 'y', 'del_time' => date('Y-m-d H:i:s')), array('comment_openid' => self :: $openid, 'id' => $cid, 'uniacid' => $_W['uniacid']));
        echoJson(array("res" => "100", 'msg' => 'success'));
    }
    public function doMobileHeadimgset(){
        global $_GPC, $_W;
        $this -> super_title = '修改头像';
        if(!self :: $openid){
            echoJson(array("res" => "101", 'msg' => '非法访问'));
        }
        $info = MemberModel :: info(self :: $openid);
        if(!$info){
            echoJson(array("res" => "101", 'msg' => 'info is empty'));
        }
        include $this -> template('headimgset');
    }
    public function doMobileMessagebox(){
        global $_W, $_GPC;
        $this -> super_title = '好友';
        $this -> menu = 'chat';
        $chatlist = pdo_fetchall("select * from " . tablename('sunshine_huayue_chat') . " where (user_openid=:user_openid or to_openid=:to_openid) and status<>'deny' and uniacid={$_W['uniacid']} group by talk_sign order by refresh_time desc", array(':user_openid' => self :: $openid, ':to_openid' => self :: $openid));
        foreach ($chatlist as $key => $value){
            if($value['to_openid'] == self :: $openid){
                $openid = $value['user_openid'];
            }else{
                $openid = $value['to_openid'];
            }
            $info = MemberModel :: info($openid);
            $chatlist[$key]['to_openid'] = $openid;
            $chatlist[$key]['headimgurl'] = $info['headimgurl'];
            $chatlist[$key]['sex'] = $info['sex'];
            $chatlist[$key]['nickname'] = $info['nickname'];
            $chatlist[$key]['province'] = $info['province'];
            $chatlist[$key]['city'] = $info['city'];
            $chatlist[$key]['remindchat'] = ChatMessageModel :: remindChatMessage($openid, self :: $openid);
        }
        include $this -> template('messagebox');
    }
    public function doMobileChat(){
        global $_GPC, $_W;
        $chat_openid = $_GPC['chat_openid'];
        if(!$chat_openid || !self :: $openid){
            exit("page error ,the chat_openid is empty");
        }
        if($chat_openid == self :: $openid){
            exit("非法聊天逻辑，不能和自己聊天");
        }
        $user_info = MemberModel :: info(self :: $openid);
        $to_user_info = MemberModel :: info($chat_openid);
        $this -> super_title = $to_user_info['nickname'];
        $this -> remind_key = false;
        include $this -> template('chat');
    }
    public function doMobileChatsend(){
        global $_W, $_GPC;
        $chat_message = $_GPC['chat_message'];
        $chat_openid = $_GPC['chat_openid'];
        if(!$_GPC['chat_message']){
            echoJson(array('res' => '101', 'msg' => 'the message can not be empty'));
        }
        if(!$_GPC['chat_openid']){
            echoJson(array('res' => '101', 'msg' => 'the chat openid is error'));
        }
        $init = ChatModel :: chatInit($chat_openid, self :: $openid);
        if($_GPC['chat_openid'] != 'i_robot'){
            if ($init['res'] == '103'){
                echoJson(array('res' => '101', 'msg' => '已达到三条记录'));
            }elseif($init['res'] == '102'){
                echoJson(array('res' => '101', 'msg' => '对方屏蔽了你的消息'));
            }elseif($init['res'] == '104'){
                echoJson(array('res' => '101', 'msg' => $init['msg']));
            }
        }
        $type = $_GPC['type'];
        if($type == 'album'){
            $media_ids = $chat_message;
            $files = S :: downloadFromWxServer($media_ids, $this -> settings);
            foreach($files as $file){
                $msg = array();
                $msg['uniacid'] = $_W['uniacid'];
                $msg['talk_sign'] = ChatModel :: talkSign(self :: $openid, $chat_openid);
                $msg['send_openid'] = self :: $openid;
                $msg['chat_message'] = $file['name'];
                $msg['type'] = 'album';
                $msg['add_time'] = date('Y-m-d H:i:s');
                $res = pdo_insert('sunshine_huayue_chatmessage', $msg);
            }
        }elseif($type == 'text'){
            $filter = FilterComponent :: init($_GPC['chat_message']);
            if($filter){
                echoJson(array('res' => '104', 'msg' => '包含敏感词'));
            }
            $msg = array();
            $msg['uniacid'] = $_W['uniacid'];
            $msg['talk_sign'] = ChatModel :: talkSign(self :: $openid, $chat_openid);
            $msg['send_openid'] = self :: $openid;
            $msg['chat_message'] = $_GPC['chat_message'];
            $msg['type'] = 'text';
            $msg['add_time'] = date('Y-m-d H:i:s');
            $res = pdo_insert('sunshine_huayue_chatmessage', $msg);
        }elseif($type == 'voice'){
            $voice_serverid = $_GPC['chat_message'];
            $msg = array();
            $msg['uniacid'] = $_W['uniacid'];
            $msg['talk_sign'] = ChatModel :: talkSign(self :: $openid, $chat_openid);
            $msg['send_openid'] = self :: $openid;
            $msg['chat_message'] = $voice_serverid;
            $msg['type'] = 'voice';
            $msg['add_time'] = date('Y-m-d H:i:s');
            $res = pdo_insert('sunshine_huayue_chatmessage', $msg);
        }
        if($_GPC['chat_openid'] == 'i_robot'){
            $robot = new ChatRobotComponent($this -> settings);
            $robot -> chat($chat_message, self :: $openid);
        }
        if($res){
            pdo_update('sunshine_huayue_chat', array('refresh_time' => date("Y-m-d H:i:s")), array('uniacid' => $_W['uniacid'], 'talk_sign' => $msg['talk_sign']));
            $this -> sendNoticeTpl($chat_openid, $msg['chat_message'], $_W['siteroot'] . 'app/' . $this -> createMobileUrl('messagebox'));
            echoJson(array('res' => '100', 'msg' => 'success'));
        }
    }
    public function doMobileChatget(){
        global $_GPC, $_W;
        $chat_openid = $_GPC['chat_openid'];
        if(!$chat_openid){
            echoJson(array('res' => '101', 'msg' => 'the chat_openid is error'));
        }
        $talk_sign = ChatModel :: talkSign(self :: $openid, $chat_openid);
        $chat_list = ChatMessageModel :: getChatList($talk_sign, $chat_openid);
        foreach($chat_list as $key => $item){
            $chat_list[$key]['add_time'] = timeShortHandle($item['add_time']);
        }
        if($chat_list){
            echoJson(array('res' => '100', 'msg' => 'list success', 'list' => $chat_list));
        }else{
            echoJson(array('res' => '101', 'msg' => 'list empty'));
        }
    }
    function doMobilechatHistory(){
        global $_GPC, $_W;
        $chat_openid = $_GPC['chat_openid'];
        $chat_logid = $_GPC['prev_logid'];
        $sign = ChatModel :: talkSign(self :: $openid, $chat_openid);
        $list = ChatMessageModel :: historyList($sign, $chat_logid);
        foreach ($list as $key => & $value){
            if($value['send_openid'] == self :: $openid){
                $value['send_type'] = 'self';
            }else{
                $value['send_type'] = 'other';
                $value['add_time'] = timeShortHandle($value['add_time']);
            }
        }
        reset($list);
        $prev_logid = current($list);
        $prev_logid = $prev_logid['id'];
        if(!$chat_logid){
            ChatMessageModel :: setReadStatus($list, $sign, $chat_openid);
        }
        if(!$list){
            echoJson(array('res' => '101', 'msg' => 'empty'));
        }
        echoJson(array('res' => '100', 'msg' => '', 'list' => $list, 'prev_logid' => $prev_logid));
    }
    function doMobileIconUpload(){
        global $_GPC, $_W;
        $media_id = $_GPC['media_id'];
        $file = S :: downloadFromWxServer($media_id, $this -> settings);
        if($file){
            $filename = $file[0]['name'];
            $upload_way = $file[0]['type'];
            $headimgurl = $filename;
            $res = pdo_update('sunshine_huayue_member', array('headimgurl' => $headimgurl), array('openid' => self :: $openid, 'uniacid' => $_W['uniacid']));
            if($res === false){
                echoJson(array("res" => '101', 'msg' => 'update headimgurl error'));
            }else{
                echoJson(array("res" => '100', 'msg' => 'success'));
            }
        }else{
            echoJson(array("res" => '101', 'msg' => 'error'));
        }
    }
    function doMobileDongtaiSave(){
        global $_GPC, $_W;
        $media_ids = $_GPC['media_ids'];
        $filelist = S :: downloadFromWxServer($media_ids, $this -> settings);
        if($filelist){
            foreach ($filelist as $key => $value){
                $data = array();
                $data['uniacid'] = $_W['uniacid'];
                $data['openid'] = self :: $openid;
                $data['remark'] = '';
                $data['img_url'] = $value['name'];
                $data['add_time'] = date("Y-m-d H:i:s");
                $data['upload_way'] = $value['type'];
                $data['type'] = 'album';
                $res = pdo_insert("sunshine_huayue_album", $data);
            }
            echoJson(array("res" => '100', 'msg' => 'success'));
        }else{
            echoJson(array("res" => '101', 'msg' => 'dongtai  filelist error'));
        }
    }
    function doMobileMomentAddAjax(){
        global $_GPC, $_W;
        if(!MemberComponent :: isVerifyMobile(self :: $openid)){
            echoJson(array('res' => '101', 'msg' => '发帖失败，请先进行手机号认证'));
        }
        $media_ids = $_GPC['media_ids'];
        $moments_remark = $_GPC['dongtai_content'];
        if(!$media_ids && !$moments_remark){
            echoJson(array('res' => '101', 'msg' => '请填写内容或上传图片'));
        }
        if($moments_remark){
            $filter = FilterComponent :: init($moments_remark);
            if($filter){
                echoJson(array('res' => '104', 'msg' => '包含敏感词'));
            }
        }
        $filelist = array();
        if($media_ids){
            $filelist = S :: downloadFromWxServer($media_ids, $this -> settings);
        }
        $moments_type = $filelist ? 'image':'text';
        $data = array();
        $data['uniacid'] = $_W['uniacid'];
        $data['openid'] = self :: $openid;
        $data['remark'] = $moments_remark;
        $data['type'] = $moments_type;
        $data['add_time'] = date("Y-m-d H:i:s");
        $data['is_del'] = 'n';
        $r = pdo_insert('sunshine_huayue_moments', $data);
        if(!$r){
            echoJson(array("res" => '101', 'msg' => 'error'));
        }
        $mid = pdo_insertid();
        if($filelist && $mid){
            foreach ($filelist as $key => $value){
                $data = array();
                $data['uniacid'] = $_W['uniacid'];
                $data['mid'] = $mid;
                $data['openid'] = self :: $openid;
                $data['remark'] = '';
                $data['img_url'] = $value['name'];
                $data['add_time'] = date("Y-m-d H:i:s");
                $data['upload_way'] = $value['type'];
                $data['type'] = 'album';
                $res = pdo_insert("sunshine_huayue_album", $data);
            }
        }
        if($mid){
            echoJson(array("res" => '100', 'msg' => 'success'));
        }else{
            echoJson(array("res" => '101', 'msg' => 'other error'));
        }
    }
    function doMobileloadRemindMessage(){
        global $_W, $_GPC;
        $chat_id = $_GPC['chat_id'];
        !$chat_id && $chat_id = 0;
        $openid = self :: $openid;
        if(!$openid){
            echoJson(array('res' => '101', 'msg' => 'openid is error'));
        }
        $unreadinfo = pdo_fetch("select id,send_openid,chat_message from " . tablename("sunshine_huayue_chatmessage") . " where talk_sign like '%{$openid}%' and readed='n' and send_openid <> '{$openid}' and uniacid={$_W['uniacid']} order by id desc limit 1");
        if($chat_id >= $unreadinfo['id']){
            echoJson(array('res' => '101', 'msg' => 'num is none'));
        }else{
            $uinfo = pdo_fetch("select headimgurl,nickname from " . tablename('sunshine_huayue_member') . " where openid='{$unreadinfo['send_openid']}' and uniacid={$_W['uniacid']}");
            $data = array();
            $data['headimgurl'] = $uinfo['headimgurl'];
            $data['nickname'] = $uinfo['nickname'];
            $data['chat_message'] = $unreadinfo['chat_message'];
            $data['chat_openid'] = $unreadinfo['send_openid'];
            echoJson(array('res' => '100', 'msg' => 'success', 'data' => $data));
        }
        echoJson(array('res' => '101', 'msg' => 'error'));
    }
    function doMobileSignIn(){
        global $_W, $_GPC;
        if($this -> settings['signin_key'] != 'open'){
            echoJson(array('res' => '101', 'msg' => '签到功能未开启'));
        }
        if(!self :: $openid){
            echoJson(array('res' => '101', 'msg' => 'error'));
        }
        $credit = $this -> settings['signin_credit'];
        $add_date = date("Y-m-d");
        $is_sign = CreditModel :: isSign(self :: $openid, $add_date);
        if($is_sign){
            echoJson(array('res' => '101', 'msg' => '已经签过到了'));
        }
        pdo_begin();
        $prev_log = CreditModel :: lastSignLog(self :: $openid);
        $prev_add_date = date('Y-m-d', strtotime($add_date . ' -1 days'));
        if($prev_add_date == $prev_log['add_date'] && $prev_log && $prev_log['sid'] < 7){
            $sid = $prev_log['sid'] + 1;
            if($this -> settings['signin_credit_grow']){
                $credit = $this -> settings['signin_credit_grow'] * ($sid-1) + $credit;
            }
        }else{
            $sid = 1;
        }
        $data = array();
        $data['uniacid'] = $_W['uniacid'];
        $data['openid'] = self :: $openid;
        $data['sid'] = $sid;
        $data['credit'] = $credit;
        $data['type'] = '普通签到';
        $data['add_date'] = date('Y-m-d');
        $data['add_time'] = date('Y-m-d H:i:s');
        $res = pdo_insert('sunshine_huayue_credit', $data);
        if(!$res){
            pdo_rollback();
            echoJson(array('res' => '101', 'msg' => '签到失败'));
        }
        $update_log = array();
        $update_log[] = 'sunshine_huayue';
        $update_log[] = $data['type'] . '增加会员积分';
        load() -> model('mc');
        $result = mc_credit_update($_W['member']['uid'], 'credit1', $credit, $update_log);
        if(!$result){
            pdo_rollback();
            echoJson(array('res' => '101', 'msg' => '会员积分操作失败'));
        }
        pdo_commit();
        echoJson(array('res' => '100', 'msg' => 'success', 'credit' => $credit));
    }
    public function doMobileRanking(){
        global $_GPC, $_W;
        $this -> super_title = '排行榜';
        $type = $_GPC['ranking_type'];
        !$type && $type = 'growth';
        $list = GrowthModel :: getGrowthRanking(0, 100);
        include $this -> template('ranking');
    }
    public function doMobileSignInHistory(){
        global $_GPC, $_W;
        if(!self :: $openid){
            exit('error');
        }
        $this -> super_title = '我的积分记录';
        $list = CreditModel :: history(self :: $openid);
        include $this -> template('timeline');
    }
    public function doMobileChatRoomList(){
        global $_GPC, $_W;
        $this -> super_title = '聊天室';
        $this -> menu = 'chatroom';
        $list = ChatroomModel :: getFrontList();
        $lvb = new LiveVideoComponent();
        foreach($list as $key => $item){
            $info = ChatroomLogModel :: unReadNums($item['id'], self :: $openid);
            if($info){
                if($info['num'] > 300){
                    $info['num'] = rand(200, 300);
                }
                $list[$key]['unread'] = $info['num'];
            }else{
                $list[$key]['unread'] = 0;
            }
            if($item['room_type'] == 'lvb'){
                $r = $lvb -> DescribeLVBChannel($item['lvb_channel_id']);
                $c_status = $r -> channelInfo[0] -> channel_status;
                if($c_status === '1'){
                    $list[$key]['lvb_status'] = '正在直播';
                }else{
                    $list[$key]['lvb_status'] = '主播不在';
                }
            }elseif($item['room_type'] == 'letv'){
                $letv_com = new LivePlayComponent();
                $r = $letv_com -> getMachineStatus($item['lvb_channel_id']);
                $c_status = $r -> lives[0] -> status;
                if($c_status == 1){
                    $list[$key]['lvb_status'] = '正在直播';
                }else{
                    $list[$key]['lvb_status'] = '主播不在';
                }
            }
        }
        include $this -> template('chatroom_list_new');
    }
    public function doMobileChatRoom(){
        global $_GPC, $_W;
        if(!self :: $openid || !$_GPC['room_id']){
            exit('非法访问2');
        }
        $online = new OnlineComponent('chatroom_' . $_GPC['room_id'], self :: $openid);
        $online_num = $online -> request();
        $userinfo = MemberModel :: info(self :: $openid);
        $userinfo['isAdmin'] = AdminModel :: isAdmin(self :: $openid);
        $info = ChatroomModel :: info($_GPC['room_id']);
        if($info['creator'] == self :: $openid){
            $userinfo['isAdmin'] = true;
        }
        $userinfo['isDefriend'] = ChatroomDefriendModel :: isDefriend(self :: $openid, $_GPC['room_id']);
        if(!$info){
            exit('房间不存在或者已被删除');
        }
        if($info['is_approve'] != 'allow'){
            exit("没有该房间");
        }
        if($info['room_type'] == 'lvb'){
            $lvb = new LiveVideoComponent();
            $c_info = $lvb -> DescribeLVBChannel($info['lvb_channel_id']);
            $c_status = $c_info -> channelInfo[0] -> channel_status;
            if($c_status === '0'){
                $lvb_status = '主播不在';
            }elseif($c_status === '1'){
                $lvb_status = '正在直播';
            }elseif($c_status === '2'){
                $lvb_status = '数据异常';
            }elseif($c_status === '3'){
                $lvb_status = '频道已关闭';
            }
        }elseif($info['room_type'] == 'letv'){
            $letv_com = new LivePlayComponent();
            $r = $letv_com -> getMachineStatus($info['lvb_channel_id']);
            $c_status = $r -> lives[0] -> status;
            if($c_status == 0){
                $lvb_status = '主播不在';
            }elseif($c_status == 1){
                $lvb_status = '正在直播';
            }else{
                $lvb_status = '数据异常';
            }
        }
        $gift_list = GiftUserModel :: getListByOpenid(self :: $openid);
        foreach($gift_list as $k => $v){
            $gift_list[$k]['info'] = GiftModel :: info($v['gift_id']);
        }
        $this -> super_title = $info['room_name'];
        $this -> remind_key = false;
        $this -> share_img = tomedia($info['room_logo']);
        $this -> share_title .= '-' . $info['room_name'];
        $this -> share_content = $info['room_desc'];
        $h_info = pdo_fetch("select * from " . tablename('sunshine_huayue_mychatroom_history') . " where uniacid={$_W['uniacid']} and room_id='{$_GPC['room_id']}' and openid=:openid", array(':openid' => self :: $openid));
        $is_admin = AdminModel :: isAdmin(self :: $openid);
        if($info['in_type'] == 'money' && self :: $openid != $info['creator'] && !$is_admin){
            $rewardsinfo = RewardsModel :: getLastOne($info['id'], self :: $openid);
            if(strtotime($rewardsinfo['add_time']) + $info['room_money_day'] * 24 * 60 * 60 > time()){
                $h_info = true;
            }else{
                include $this -> template('chatroom_money');
                exit;
            }
        }
        if(self :: $openid == $info['creator'] || $is_admin){
            $h_info = true;
        }
        if($h_info){
            if($info['room_type'] == 'lvb' || $info['room_type'] == 'letv'){
                include $this -> template('chatroom_lvb');
            }else{
                include $this -> template('chatroom');
            }
        }else{
            if($info['in_type'] == 'secret'){
                if(isset($_GPC['chatroom_secret'])){
                    if(checksubmit() && $_GPC['chatroom_secret'] == $info['room_secret']){
                        $this -> refreshMyChatRoomHistory($_GPC['room_id']);
                        echoJson(array('res' => '100', 'msg' => '校验成功'));
                    }else{
                        echoJson(array('res' => '101', 'msg' => '校验失败'));
                    }
                }else{
                    include $this -> template('chatroom_secret');
                }
            }elseif($info['in_type'] == 'money'){
                include $this -> template('chatroom_money');
            }else{
                if($info['room_type'] == 'lvb' || $info['room_type'] == 'letv'){
                    include $this -> template('chatroom_lvb');
                }else{
                    include $this -> template('chatroom');
                }
            }
        }
    }
    function doMobileGetOnlineNum(){
        global $_W, $_GPC;
        $online = new OnlineComponent('chatroom_' . $_GPC['room_id'], self :: $openid);
        $online_num = $online -> request();
        echoJson(array('res' => 100, 'msg' => 'success', 'online_num' => $online_num));
    }
    function doMobileChatRoomMoney(){
        global $_W, $_GPC;
        $room_id = $_GPC['room_id'];
        $rinfo = ChatroomModel :: info($room_id);
        $money = floatval($rinfo['room_money']);
        $to_openid = $rinfo['creator'];
        if(!is_numeric($money)){
            $money = 0.01;
        }elseif($money < 0.01 || $money > 1000){
            $err[] = '请填写金额在0.01 到 1000之间，精确到分';
        }elseif(strpos($money, '.') !== false){
            $money_suffix = substr($money, strpos($money, '.') + 1);
            if(strlen($money_suffix) > 2){
                $err[] = '只能精确到0.01';
            }
        }
        if(!$room_id){
            $err[] = '缺少房号参数';
        }
        if(!$to_openid){
            $err[] = '缺少打赏人openid';
        }
        if(!self :: $openid){
            $err[] = '非法访问';
        }
        if($err){
            include $this -> template('payerror');
            exit;
        }
        $order_id = RewardsModel :: addItem(self :: $openid, $to_openid, $money, $room_id, 'room_money');
        if(!$order_id){
            $err[] = '写入记录失败';
            include $this -> template('payerror');
            exit;
        }
        $this -> myPay($money, $order_id, $room_id);
    }
    public function doMobileChatRoomHistory(){
        global $_GPC, $_W;
        if(!self :: $openid || !$_GPC['room_id']){
            echoJson(array('res' => '101', 'msg' => 'fail'));
        }
        $room_id = $_GPC['room_id'];
        $list = ChatroomLogModel :: history($room_id, $_GPC['prev_logid']);
        if(!$list){
            echoJson(array('res' => '101', 'msg' => 'success', 'list' => array(), 'logid' => 0, 'prev_logid' => 0));
        }
        foreach($list as $key => $item){
            if($item['openid'] == self :: $openid){
                $item['send_type'] = 'self';
            }else{
                $item['add_time'] = timeShortHandle($item['add_time']);
                $item['send_type'] = 'other';
            }
            $chatlist[$key] = $item;
            $chatlist[$key]['userinfo'] = MemberModel :: info($item['openid']);
            $chatlist[$key]['isDefriend'] = ChatroomDefriendModel :: isDefriend($item['openid'], $room_id);
            if($item['type'] == 'redpack'){
                $packInfo = explode('|', $item['content']);
                $chatlist[$key]['redpack']['money'] = $packInfo[0];
                $chatlist[$key]['redpack']['orderid'] = $packInfo[1];
                $chatlist[$key]['redpack']['openid'] = $packInfo[2];
                $chatlist[$key]['redpack']['uinfo'] = MemberModel :: info($packInfo[2]);
            }
            if($item['type'] == 'room_money'){
                $packInfo = explode('|', $item['content']);
                $chatlist[$key]['redpack']['money'] = $packInfo[0];
                $chatlist[$key]['redpack']['orderid'] = $packInfo[1];
                $chatlist[$key]['redpack']['openid'] = $packInfo[2];
                $chatlist[$key]['redpack']['uinfo'] = MemberModel :: info($packInfo[2]);
            }
            if($item['type'] == 'gift'){
                $packInfo = explode('|', $item['content']);
                $chatlist[$key]['gift']['ugid'] = $packInfo[0];
                $chatlist[$key]['gift']['gift_id'] = $packInfo[1];
                $chatlist[$key]['gift']['gift_price'] = $packInfo[2];
                $chatlist[$key]['gift']['info'] = GiftModel :: info($packInfo[1]);
            }
        }
        reset($list);
        $pre_logid = current($list);
        reset($list);
        $enditem = end($list);
        $logid = $enditem['id'];
        echoJson(array('res' => '100', 'msg' => 'success', 'list' => $chatlist, 'logid' => $logid, 'prev_logid' => $pre_logid['id']));
    }
    public function doMobileChatRoomSend(){
        global $_GPC, $_W;
        $chat_message = $_GPC['chat_message'];
        $room_id = $_GPC['room_id'];
        $type = $_GPC['type'];
        if(!$room_id || !self :: $openid){
            exit("非法访问");
        }
        if(ChatroomDefriendModel :: isDefriend(self :: $openid, $room_id)){
            echoJson(array('res' => '102', 'msg' => '已被禁言，发送消息无效！如有疑问请联系管理员'));
        }
        if(!trim($chat_message)){
            echoJson(array('res' => '101', 'msg' => '不能为空'));
        }
        if($type == 'text'){
            $filter = FilterComponent :: init($chat_message);
            if($filter){
                echoJson(array('res' => '104', 'msg' => '包含敏感词'));
            }
            $data = array();
            $data['uniacid'] = $_W['uniacid'];
            $data['rid'] = $room_id;
            $data['openid'] = self :: $openid;
            $data['content'] = $chat_message;
            $data['type'] = $type;
            $data['add_time'] = date('Y-m-d H:i:s');
            $res = pdo_insert('sunshine_huayue_chatroom_log', $data);
            $robot = new ChatRobotComponent($this -> settings);
            $robot -> think($chat_message, $room_id);
        }elseif($type == 'album'){
            $media_ids = $chat_message;
            $files = S :: downloadFromWxServer($media_ids, $this -> settings);
            foreach($files as $file){
                $message = $file['name'];
                $data = array();
                $data['uniacid'] = $_W['uniacid'];
                $data['rid'] = $room_id;
                $data['openid'] = self :: $openid;
                $data['content'] = $message;
                $data['type'] = $type;
                $data['add_time'] = date('Y-m-d H:i:s');
                $res = pdo_insert('sunshine_huayue_chatroom_log', $data);
            }
        }elseif($type == 'voice'){
            $voice_serverid = $chat_message;
            $data = array();
            $data['uniacid'] = $_W['uniacid'];
            $data['rid'] = $room_id;
            $data['openid'] = self :: $openid;
            $data['content'] = $voice_serverid;
            $data['type'] = $type;
            $data['add_time'] = date('Y-m-d H:i:s');
            $res = pdo_insert('sunshine_huayue_chatroom_log', $data);
            if($this -> settings['voice_download'] == 'open'){
                list($a, $media_id) = explode('::', $voice_serverid);
                $file = S :: downloadFromWxServer($media_id, $this -> settings);
                VoiceLogMode :: addItem(self :: $openid, pdo_insertid(), $file[0]['name']);
            }
        }
        if($res){
            if($_GPC['at_openid']){
                $this -> sendNoticeTpl($_GPC['at_openid'], '有人@了你：' . $chat_message, $_W['siteroot'] . 'app/' . $this -> createMobileUrl('chatroom', array('room_id' => $room_id)));
            }
            $this -> refreshMyChatRoomHistory($room_id);
            echoJson(array('res' => '100', 'msg' => '写入成功'));
        }
        echoJson(array('res' => '101', 'msg' => 'fail'));
    }
    public function doMobileChatRoomGet(){
        global $_GPC, $_W;
        $room_id = $_GPC['room_id'];
        $logid = $_GPC['logid'];
        !$logid && $logid = 0;
        if(!self :: $openid || !$room_id){
            exit("非法访问");
        }
        $online = new OnlineComponent('chatroom_' . $_GPC['room_id'], self :: $openid);
        $online_num = $online -> request();
        $list = ChatroomLogModel :: getUnreadList($room_id, self :: $openid, $logid);
        if($list){
            foreach($list as $key => $item){
                if($item['openid'] == self :: $openid){
                    $item['send_type'] = 'self';
                }else{
                    $item['send_type'] = 'other';
                }
                $chatlist[$key] = $item;
                $chatlist[$key]['userinfo'] = MemberModel :: info($item['openid']);
                $chatlist[$key]['isDefriend'] = ChatroomDefriendModel :: isDefriend($item['openid'], $room_id);
                if($item['type'] == 'redpack'){
                    $packInfo = explode('|', $item['content']);
                    $chatlist[$key]['redpack']['money'] = $packInfo[0];
                    $chatlist[$key]['redpack']['orderid'] = $packInfo[1];
                    $chatlist[$key]['redpack']['openid'] = $packInfo[2];
                    $chatlist[$key]['redpack']['uinfo'] = MemberModel :: info($packInfo[2]);
                }
                if($item['type'] == 'room_money'){
                    $packInfo = explode('|', $item['content']);
                    $chatlist[$key]['redpack']['money'] = $packInfo[0];
                    $chatlist[$key]['redpack']['orderid'] = $packInfo[1];
                    $chatlist[$key]['redpack']['openid'] = $packInfo[2];
                    $chatlist[$key]['redpack']['uinfo'] = MemberModel :: info($packInfo[2]);
                }
                if($item['type'] == 'gift'){
                    $packInfo = explode('|', $item['content']);
                    $chatlist[$key]['gift']['ugid'] = $packInfo[0];
                    $chatlist[$key]['gift']['gift_id'] = $packInfo[1];
                    $chatlist[$key]['gift']['gift_price'] = $packInfo[2];
                    $chatlist[$key]['gift']['info'] = GiftModel :: info($packInfo[1]);
                }
            }
            $enditem = end($list);
            $logid = $enditem['id'];
            echoJson(array('res' => '100', 'msg' => 'list success', 'list' => $chatlist, 'logid' => $logid));
        }else{
            echoJson(array('res' => '101', 'msg' => 'list empty'));
        }
    }
    public function doMobileChatRoomApply(){
        global $_GPC, $_W;
        if(!$this -> vip){
            exit('非会员身份禁止访问');
        }
        $this -> super_title = "创建房间";
        $this -> menu = "mychatroom_create";
        $userinfo = MemberModel :: info(self :: $openid);
        $err_msg = array('res' => '100', 'msg' => 'ok');
        include $this -> template("chatroom_apply_new");
    }
    public function doMobileChatRoomApplySubmit(){
        global $_W, $_GPC;
        if(!self :: $openid){
            exit('非法访问');
        }
        if(!$this -> vip){
            exit('非会员身份禁止访问');
        }
        if(!MemberComponent :: isVerifyMobile(self :: $openid)){
            echoJson(array('res' => '101', 'msg' => '请先进行手机号认证'));
        }
        $room_name = $_GPC['room_name'];
        $room_desc = $_GPC['room_desc'];
        $room_logo_media_ids = $_GPC['room_logo_media_ids'];
        $is_public = $_GPC['is_public'];
        $is_secret = $_GPC['is_secret'];
        $is_money = $_GPC['is_money'];
        $room_secret = $_GPC['room_secret'];
        $room_type = $_GPC['room_type'];
        if($is_secret == 'y' && $is_money == 'y'){
            echoJson(array('res' => '101', 'msg' => '口令和付费只能选择一种'));
        }
        if($is_secret == 'y'){
            $in_type = 'secret';
        }elseif($is_money == 'y'){
            $in_type = 'money';
        }else{
            $in_type = '';
        }
        $room_money = $_GPC['room_money'];
        $room_money_day = $_GPC['room_money_day'];
        if($room_type == 'lvb' || $room_type == 'letv'){
            $r = ChatroomModel :: getListByOpenid(self :: $openid);
            if($r){
            }
        }
        if(!$room_name || !$room_desc || !$room_logo_media_ids || !$room_type){
            echoJson(array('res' => '101', 'msg' => '信息不完整'));
        }
        if(($is_public != 'y' && $is_public != 'n') || ($is_secret != 'y' && $is_secret != 'n')){
            echoJson(array('res' => '101', 'msg' => '参错错误'));
        }
        if($is_secret == 'y' && !$room_secret){
            echoJson(array('res' => '101', 'msg' => '请填写口令'));
        }
        if($is_money == 'y'){
            if(!$room_money_day){
                echoJson(array('res' => '101', 'msg' => '请填写付费有效天数'));
            }
            if(!$room_money){
                echoJson(array('res' => '101', 'msg' => '请填写付费金额'));
            }
            if(!is_numeric($room_money)){
                echoJson(array('res' => '101', 'msg' => '请填写整数类型的天数'));
            }
            if(!is_numeric($room_money_day)){
                echoJson(array('res' => '101', 'msg' => '请填写数字类型的金额'));
            }
        }
        $file = S :: downloadFromWxServer($room_logo_media_ids, $this -> settings);
        if(!$file){
            echoJson(array('res' => '101', 'msg' => 'logo上传失败'));
        }
        $room_logo = $file[0]['name'];
        $adminlist = AdminModel :: getList();
        if($adminlist){
            $notice = new NoticeComponent();
            foreach ($adminlist as $key => $value){
                $uinfo = MemberModel :: info(self :: $openid);
                $first = $uinfo['nickname'] . "-申请创建“{$room_name}“聊天室";
                $key_1 = "未审核";
                $video1 = $room_type == 'lvb' ? '腾讯云直播' : '';
                $video2 = $room_type == 'letv' ? '乐视云直播' : '';
                $video = $video1 . $video2;
                $public = $is_public == "y" ? '公开':"非公开";
                $secret = $is_secret == 'y' ? '有口令' . $room_secret:'无口令';
                $money = $is_money == 'y' ? '收费' . $room_money . '元/' . $room_money_day . '天': '';
                $remark = "房间属性：" . $video . '|' . $public . "|" . $secret . "|" . $money . "-请及时到后台进行审核";
                $notice -> sendApproveTpl($value['openid'], $first, $key_1, $remark, $_W['siteroot'] . 'app/' . $this -> createMobileUrl('index'));
            }
        }
        $r = ChatroomModel :: createChatRoom($room_name, $room_desc, $room_logo, self :: $openid, $is_public, $in_type, $room_secret, $room_type, $room_money, $room_money_day);
        echoJson($r);
    }
    public function doMobileUploadLogoByUser(){
        global $_W, $_GPC;
        $media_id = $_GPC['media_ids'];
        $file = S :: downloadFromWxServer($media_id, $this -> settings);
        echoJson(array('res' => '100', 'msg' => 'success', 'imgurl' => $file[0]['name']));
    }
    public function doMobilebindadmin(){
        global $_GPC, $_W;
        if(!self :: $openid){
            $err_msg = array();
            $err_msg['res'] = '101';
            $err_msg['msg'] = '非法访问';
        }
        if($_GPC['qrcodetoken'] != $this -> settings['bind_admin_qrcode_token']){
            $err_msg = array();
            $err_msg['res'] = '101';
            $err_msg['msg'] = '该二维码已失效';
        }elseif(AdminModel :: isAdmin(self :: $openid)){
            $err_msg = array();
            $err_msg['res'] = '101';
            $err_msg['msg'] = '你已经是管理员身份，请勿重复添加';
        }else{
            $data = array();
            $data['uniacid'] = $_W['uniacid'];
            $data['openid'] = self :: $openid;
            $data['add_time'] = date("Y-m-d H:i:s");
            pdo_insert('sunshine_huayue_admin', $data);
            pdo_update('sunshine_huayue_setting', array('value' => md5(time())), array('name' => 'bind_admin_qrcode_token', 'uniacid' => $_W['uniacid']));
            $err_msg = array();
            $err_msg['res'] = '100';
            $err_msg['msg'] = '管理员绑定成功';
        }
        $uinfo = MemberModel :: info(self :: $openid);
        $this -> super_title = "管理员绑定";
        include $this -> template('bindadmin');
    }
    public function doMobileMyChatRoomHistory(){
        global $_W, $_GPC;
        $this -> super_title = "参与的聊天室";
        $this -> menu = 'mychatroom_history';
        $h_list = pdo_fetchall("select room_id from " . tablename('sunshine_huayue_mychatroom_history') . " where openid=:openid and uniacid={$_W['uniacid']} order by update_time desc", array(':openid' => self :: $openid));
        foreach ($h_list as $key => $item){
            $info = ChatroomModel :: info($item['room_id']);
            if($info){
                $list[] = $info;
            }
        }
        include $this -> template("mychatroom_history");
    }
    public function refreshMyChatRoomHistory($room_id){
        global $_W;
        if(!$room_id || !self :: $openid){
            return false;
        }
        $info = pdo_fetch("select * from " . tablename('sunshine_huayue_mychatroom_history') . " where uniacid={$_W['uniacid']} and room_id='{$room_id}' and openid=:openid", array(':openid' => self :: $openid));
        if($info){
            pdo_update('sunshine_huayue_mychatroom_history', array('update_time' => date("Y-m-d H:i:s")), array('uniacid' => $_W['uniacid'], 'room_id' => $room_id, 'openid' => self :: $openid));
        }else{
            $data = array();
            $data['uniacid'] = $_W['uniacid'];
            $data['openid'] = self :: $openid;
            $data['room_id'] = $room_id;
            $data['update_time'] = date("Y-m-d H:i:s");
            $data['add_time'] = date("Y-m-d H:i:s");
            pdo_insert('sunshine_huayue_mychatroom_history', $data);
        }
    }
    public function doMobileMyChatRoomMine(){
        global $_W, $_GPC;
        $this -> super_title = "我的聊天室";
        $this -> menu = 'mychatroom_mine';
        $list = pdo_fetchall("select * from " . tablename('sunshine_huayue_chatroom') . " where uniacid={$_W['uniacid']} and creator=:creator and room_status='normal' order by is_approve='allow' desc", array(':creator' => self :: $openid));
        include $this -> template('mychatroom_mine');
    }
    public function doMobilemyChatroomDetail(){
        global $_GPC, $_W;
        $rid = $_GPC['room_id'];
        if(!$rid){
            exit('非法访问');
        }
        $rinfo = ChatroomModel :: info($rid);
        if(!$rinfo){
            exit('房间信息错误');
        }
        if($rinfo['creator'] != self :: $openid){
            exit('非法访问2');
        }
        if($rinfo['room_type'] == 'lvb'){
            $rinfo['lvb_info'] = LvbModel :: info($rinfo['lvb_channel_id'], $rid);
        }elseif($rinfo['room_type'] == 'letv'){
            $rinfo['letv_info'] = LetvModel :: info($rinfo['lvb_channel_id'], $rid);
        }
        include $this -> template('mychatroomdetail');
    }
    public function doMobileMyMoments(){
        global $_GPC, $_W;
        $this -> super_title = '我的动态';
        $page_type = 'self';
        $openid = $_GPC['user_openid'];
        $openid = $openid ? $openid : self :: $openid;
        $uinfo = MemberModel :: info($openid);
        if($openid != self :: $openid){
            $page_type = 'other';
            $this -> super_title = $uinfo['nickname'];
        }
        $list = MomentsModel :: loadMomentList(self :: $openid, 1, 10, $openid);
        include $this -> template('mymoments');
    }
    public function doMobileloadMomentList(){
        global $_W, $_GPC;
        $page = $_GPC['page'];
        $openid = $_GPC['user_openid'];
        $openid = $openid ? $openid : self :: $openid;
        $list = MomentsModel :: loadMomentList(self :: $openid, $page, 10, $openid);
        if($list){
            echoJson(array("res" => "100", 'msg' => 'success', 'list' => $list));
        }
        echoJson(array("res" => "101", 'msg' => 'empty'));
    }
    public function doMobileMoments(){
        global $_W, $_GPC;
        $this -> super_title = '最新动态';
        $this -> menu = 'moments';
        include $this -> template('moments');
    }
    public function doMobileloadAllMoments(){
        global $_W, $_GPC;
        $page = $_GPC['page'];
        $list = MomentsModel :: loadMomentList(self :: $openid, $page);
        foreach($list as & $value){
            $uinfo = MemberModel :: info($value['openid']);
            $value['uinfo'] = $uinfo;
        }
        if($list){
            echoJson(array("res" => "100", 'msg' => 'success', 'list' => $list));
        }
        echoJson(array("res" => "101", 'msg' => 'empty'));
    }
    public function doMobilemymomentsdelete(){
        global $_GPC, $_W;
        if(!self :: $openid){
            echoJson(array("res" => "101", 'msg' => '非法请求'));
        }
        $mid = $_GPC['mid'];
        if(!is_numeric($mid)){
            echoJson(array("res" => "101", 'msg' => 'mid is error'));
        }
        $info = MomentsModel :: info($mid);
        if($info['openid'] != self :: $openid){
            echoJson(array("res" => "101", 'msg' => '非法请求'));
        }
        $r = pdo_update('sunshine_huayue_moments', array('is_del' => 'y', 'del_time' => date('Y-m-d H:i:s')), array('id' => $mid, 'openid' => self :: $openid, 'uniacid' => $_W['uniacid']));
        if($r){
            echoJson(array("res" => "100", 'msg' => 'success'));
        }
        echoJson(array("res" => "101", 'msg' => '删除失败'));
    }
    function doMobileMomentAdd(){
        global $_GPC, $_W;
        $this -> super_title = '发布新帖';
        include $this -> template('momentadd');
    }
    function doMobileMomentDetail(){
        global $_GPC, $_W;
        $this -> super_title = '详情';
        $mid = $_GPC['mid'];
        $info = MomentsModel :: detail($mid);
        $page_type = 'self';
        $uinfo = MemberModel :: info($info['openid']);
        if($openid != self :: $openid){
            $page_type = 'other';
        }
        $commentlist = CommentModel :: getList($info['openid'], 1, 20, $mid);
        include $this -> template('moment_detail');
    }
    public function doMobileDefriend(){
        global $_W, $_GPC;
        $defriend_openid = $_GPC['defriend_openid'];
        $status = $_GPC['status'] ? $_GPC['status'] : 'y';
        if(!$defriend_openid){
            echoJson(array('res' => '101', 'msg' => '非法参数'));
        }
        if($status == 'y'){
            $r = DefriendModel :: defriend(self :: $openid, $defriend_openid);
        }else{
            $r = DefriendModel :: relieve(self :: $openid, $defriend_openid);
        }
        if($r){
            echoJson(array('res' => '100', 'msg' => 'success'));
        }
        echoJson(array('res' => '101', 'msg' => 'error'));
    }
    public function doMobileDefriendList(){
        global $_GPC, $_W;
        $this -> super_title = "黑名单";
        $list = MemberModel :: listJoin(DefriendModel :: lists(self :: $openid), 'defriend_openid');
        include $this -> template('defriend_list');
    }
    public function doMobileChatroomMessageDel(){
        global $_W, $_GPC;
        if(!self :: $openid){
            echoJson(array('res' => '101', 'msg' => '非法访问'));
        }
        if(!AdminModel :: isAdmin(self :: $openid)){
            echoJson(array('res' => '101', 'msg' => '非法访问：非管理员身份'));
        }
        $id = $_GPC['id'];
        $room_id = $_GPC['room_id'];
        $r = ChatroomLogModel :: del($id, $room_id);
        if($r){
            echoJson(array('res' => '100', 'msg' => '删除成功'));
        }
        echoJson(array('res' => '101', 'msg' => '删除失败'));
    }
    public function doMobilechatroomdefriend(){
        global $_W, $_GPC;
        $room_id = $_GPC['room_id'];
        $defriend_openid = $_GPC['defriend_openid'];
        if(!self :: $openid){
            echoJson(array('res' => '101', 'msg' => '非法访问'));
        }
        if(!AdminModel :: isAdmin(self :: $openid) && !ChatroomModel :: isCreator(self :: $openid, $room_id)){
            echoJson(array('res' => '101', 'msg' => '非法访问：非管理员身份'));
        }
        if(AdminModel :: isAdmin($defriend_openid) || ChatroomModel :: isCreator($defriend_openid, $room_id)){
            echoJson(array('res' => '101', 'msg' => '对方身份为管理员，无法禁言'));
        }
        $r = ChatroomDefriendModel :: defriend($room_id, $defriend_openid, self :: $openid);
        if($r){
            echoJson(array('res' => '100', 'msg' => '踢出成功'));
        }
        echoJson(array('res' => '101', 'msg' => '踢出失败'));
    }
    public function doMobilechatroomrelieve(){
        global $_W, $_GPC;
        $room_id = $_GPC['room_id'];
        $relieve_openid = $_GPC['relieve_openid'];
        if(!self :: $openid){
            echoJson(array('res' => '101', 'msg' => '非法访问'));
        }
        if(!AdminModel :: isAdmin(self :: $openid) && !ChatroomModel :: isCreator(self :: $openid, $room_id)){
            echoJson(array('res' => '101', 'msg' => '非法访问：非管理员身份'));
        }
        $r = ChatroomDefriendModel :: relieve($room_id, $relieve_openid, self :: $openid);
        if($r){
            echoJson(array('res' => '100', 'msg' => '解除限制成功'));
        }
        echoJson(array('res' => '101', 'msg' => '解除限制失败'));
    }
    public function doMobileUserCheckMobile(){
        global $_GPC, $_W;
        $info = MemberModel :: info(self :: $openid);
        if($info['mobile_status'] == 'y'){
            exit("禁止访问");
        }
        include $this -> template('usercheckmobile');
    }
    public function doMobileUserCheckMobileSendCaptcha(){
        global $_GPC, $_W;
        $info = MemberModel :: info(self :: $openid);
        $mobile = $_GPC['mobile'];
        if(!$mobile){
            echoJson(array('res' => '101', 'msg' => '请填写手机号'));
        }
        if($info['mobile_status'] == 'y'){
            echoJson(array('res' => '101', 'msg' => '手机号已验证无需重复验证'));
        }
        if($info['mobile_captcha_send_time'] && (time() - strtotime($info['mobile_captcha_send_time']) < 60)){
            echoJson(array('res' => '101', 'msg' => '您请求的太频繁了，请稍后重试'));
        }
        $captcha = mt_rand(100000, 999999);
        $r = MemberComponent :: updateCaptcha(self :: $openid, $mobile, $captcha);
        if(!$r){
            echoJson(array('res' => '101', 'msg' => '发送验证码失败，写入失败'));
        }
        $r = SmsComponent :: sendCaptcha($mobile, $captcha, self :: $openid);
        if(!$r){
            echoJson(array('res' => '101', 'msg' => '发送验证码失败'));
        }
        echoJson(array('res' => '100', 'msg' => 'success'));
    }
    public function doMobileUserCheckMobileVerifyCaptcha(){
        global $_W, $_GPC;
        $mobile = $_GPC['mobile'];
        $captcha = $_GPC['captcha'];
        if(!$mobile){
            echoJson(array('res' => '101', 'msg' => '请填写手机号'));
        }
        if(!$captcha){
            echoJson(array('res' => '101', 'msg' => '请填写验证码'));
        }
        $info = MemberModel :: info(self :: $openid);
        if($info['mobile_captcha'] != $captcha){
            echoJson(array('res' => '101', 'msg' => '验证码错误'));
        }
        $r = MemberComponent :: captchaOk(self :: $openid);
        if($r){
            echoJson(array('res' => '100', 'msg' => '认证成功'));
        }
        echoJson(array('res' => '101', 'msg' => '身份验证失败，请重试'));
    }
    function doMobileRedPackReward(){
        global $_W, $_GPC;
        $money = floatval($_GPC['redpack_money']);
        $to_openid = $_GPC['redpack_to_openid'];
        $room_id = $_GPC['room_id'];
        if(!is_numeric($money)){
            $money = 0.01;
        }elseif($money < 0.01 || $money > 200){
            $err[] = '请填写金额在0.01 到 200之间，精确到分';
        }elseif(strpos($money, '.') !== false){
            $money_suffix = substr($money, strpos($money, '.') + 1);
            if(strlen($money_suffix) > 2){
                $err[] = '只能精确到0.01';
            }
        }
        if(!$room_id){
            $err[] = '缺少房号参数';
        }
        if(!$to_openid){
            $err[] = '缺少打赏人openid';
        }
        if(!self :: $openid){
            $err[] = '非法访问';
        }
        if($err){
            include $this -> template('payerror');
            exit;
        }
        $order_id = RewardsModel :: addItem(self :: $openid, $to_openid, $money, $room_id, 'room_rewards');
        if(!$order_id){
            $err[] = '写入预打赏记录失败';
            include $this -> template('payerror');
            exit;
        }
        $this -> myPay($money, $order_id, $room_id);
    }
    function myPay($fee, $order_id, $room_id){
        global $_W, $_GPC;
        if($fee <= 0){
            message('支付错误, 金额小于0');
        }
        $tid = time() . rand(1000000, 9999999);
        $params = array('tid' => $tid . '-' . $order_id . '-' . $room_id, 'ordersn' => $tid, 'title' => '金额支付', 'fee' => $fee, 'user' => $_W['member']['uid'],);
        $this -> pay($params);
    }
    public function payResult($params){
        global $_W;
        $resArr = explode('-', $params['tid']);
        $order_id = $resArr[1];
        $room_id = $resArr[2];
        WeUtility :: logging('huayue_payresult_setsuccess', var_export($params, true));
        if ($params['result'] == 'success' && $params['from'] == 'notify'){
            if($resArr[2] == 'giftorder'){
                $this -> GiftPayHandle($order_id);
            }else{
                $this -> ChatroomPayHandle($order_id);
            }
        }
        if($params['result'] == 'success' && $params['from'] == 'return'){
            if($resArr[2] == 'giftorder'){
                header("location:" . $_W['siteroot'] . 'app/' . $this -> createMobileUrl('mygiftorderlist'));
            }else{
                header("location:" . $_W['siteroot'] . 'app/' . $this -> createMobileUrl('chatroom', array('room_id' => $room_id)));
            }
        }
    }
    public function ChatroomPayHandle($order_id){
        $oinfo = RewardsModel :: info($order_id);
        if($oinfo['money_type'] == 'room_rewards'){
            ChatroomLogModel :: addItem($oinfo['room_id'], $oinfo['openid'], $oinfo['money'] . "|" . $order_id . "|" . $oinfo['to_openid'], 'redpack');
        }else{
            ChatroomLogModel :: addItem($oinfo['room_id'], $oinfo['openid'], $oinfo['money'] . "|" . $order_id . "|" . $oinfo['to_openid'], 'room_money');
        }
        $r = RewardsModel :: setSuccess($order_id);
        if(!$r){
            WeUtility :: logging('huayue_payresult_setsuccess', var_export($r, true));
            exit('更新支付日志失败');
        }else{
            MemberModel :: addMoney($oinfo['to_openid'], $oinfo['money']);
            $uinfo = MemberModel :: info($oinfo['openid']);
            $notice = new NoticeComponent();
            $to_user_tpl['name'] = '来自：' . $uinfo['nickname'];
            $to_user_tpl['content'] = '收入金额：' . $oinfo['money'];
            $to_user_tpl['url'] = $_W['siteroot'] . 'app/' . $this -> createMobileUrl('chatroom', array('room_id' => $oinfo['room_id']));
            $r = $notice -> sendToRewardTpl($oinfo['to_openid'], $to_user_tpl['name'], $to_user_tpl['content'], $to_user_tpl['url']);
            $to_uinfo = MemberModel :: info($oinfo['to_openid']);
            $user_tpl['name'] = $to_uinfo['nickname'];
            $user_tpl['content'] = '支出金额：' . $oinfo['money'];
            $user_tpl['url'] = $_W['siteroot'] . 'app/' . $this -> createMobileUrl('chatroom', array('room_id' => $oinfo['room_id']));
            $r = $notice -> sendRewardTpl($oinfo['openid'], $user_tpl['name'], $user_tpl['content'], $user_tpl['url']);
        }
    }
    public function doMobileUserWallet(){
        global $_W, $_GPC;
        $info = MemberModel :: info(self :: $openid);
        if($this -> settings['draw_percent']){
            $draw_percent = $this -> settings['draw_percent'];
        }else{
            $draw_percent = 20;
        }
        include $this -> template('userwallet');
    }
    public function doMobileUserDrawMoney(){
        global $_W, $_GPC;
        $info = MemberModel :: info(self :: $openid);
        $drawinfo = DrawLogModel :: getLastDrawLog(self :: $openid);
        $cold_time_arr = DrawLogComponent :: getColdTime(self :: $openid);
        $cold_time = $cold_time_arr['str'] . $cold_time_arr['unit'];
        if($this -> settings['draw_percent']){
            $draw_percent = $this -> settings['draw_percent'];
        }else{
            $draw_percent = 20;
        }
        include $this -> template('userdrawmoney');
    }
    public function doMobileConfirmDrawMoney(){
        global $_W, $_GPC;
        if(!checksubmit()){
            exit("非法访问");
        }
        if(!MemberComponent :: isVerifyMobile(self :: $openid)){
            echoJson(array('res' => '101', 'msg' => '提现失败，请先进行手机号认证'));
        }
        $info = MemberModel :: info(self :: $openid);
        if(!$info){
            exit("非法访问");
        }
        $money = $_GPC['money'];
        if(!is_numeric($money)){
            exit("错误参数");
        }
        if($money < 10 || $money > 1000){
            exit("错误的额度范围");
        }
        if($money > $info['avaliable_money']){
            exit("错误的提现额度");
        }
        if(DrawLogComponent :: isCanDraw(self :: $openid) === false){
            exit('冷却中，禁止禁止提现');
        }
        if($this -> settings['draw_percent']){
            $draw_percent = $this -> settings['draw_percent'] / 100;
        }else{
            $draw_percent = 0.2;
        }
        $commision = floor($money * $draw_percent);
        pdo_begin();
        $r = DrawLogModel :: addItem(self :: $openid, $money, $commision);
        if(!$r){
            $err[] = "写入日志表失败";
            pdo_rollback();
        }else{
            $r = MemberModel :: drawMoney(self :: $openid, $money);
            if(!$r){
                $err[] = "更新资金失败";
                pdo_rollback();
            }else{
                NoticeBusiness :: sendApplyDrawNotice(self :: $openid, $money, $commision, $_W['siteroot'] . 'app/' . $this -> createMobileUrl('admin'));
                pdo_commit();
            }
        }
        include $this -> template('userdrawmoneyresult');
    }
    public function doMobileUserDrawLogList(){
        global $_GPC, $_W;
        $this -> super_title = "提现记录";
        $list = DrawLogModel :: getListByOpenid(self :: $openid);
        include $this -> template('userdrawloglist');
    }
    public function doMobileUserWalletLog(){
        global $_W, $_GPC;
        $this -> super_title = "收入记录";
        $list = RewardsModel :: getList(self :: $openid);
        $list = MemberModel :: listJoin($list);
        include $this -> template('userwalletlog');
    }
    public function doMobileUserRewardLog(){
        global $_W, $_GPC;
        $this -> super_title = "打赏记录";
        $list = RewardsModel :: getToOtherList(self :: $openid);
        $list = MemberModel :: listJoin($list, 'to_openid');
        include $this -> template('userrewardlog');
    }
    public function doMobileFeedback(){
        global $_W, $_GPC;
        include $this -> template("feedback");
    }
    public function doMobileFeedbackAjax(){
        global $_W, $_GPC;
        $content = $_GPC['content'];
        $openid = self :: $openid;
        if(!$content){
            echoJson(array('res' => '101', 'msg' => '内容不能为空'));
        }
        $c_len = mb_strlen($content, 'utf8');
        if($c_len < 15 || $c_len > 200){
            echoJson(array('res' => '101', 'msg' => '请填写15-200个字'));
        }
        $r = FeedbackModel :: addItem($openid, $content);
        if($r){
            NoticeBusiness :: sendFeedbackNotice($openid, $content, $_W['siteroot'] . 'app/' . $this -> createMobileUrl('index'));
            echoJson(array('res' => '100', 'msg' => '提交成功'));
        }else{
            echoJson(array('res' => '101', 'msg' => '意见提交失败'));
        }
    }
    public function doMobileGiftMall(){
        global $_W, $_GPC;
        $this -> super_title = "礼物商城";
        $list = GiftModel :: getList();
        include $this -> template('gift_mall');
    }
    public function doMobileGiftBuy(){
        global $_W, $_GPC;
        if(!self :: $openid){
            exit("非法访问");
        }
        $json_data = $_POST['json_data'];
        $sum_price = $_GPC['sum_price_data'];
        if($sum_price <= 0 || !$sum_price){
            exit("非法金额");
        }
        if(!$json_data){
            exit("非法数据访问");
        }
        $jsonArr = json_decode($json_data, true);
        $price = 0;
        foreach($jsonArr as $id => $item){
            $ginfo = GiftModel :: info($id);
            if($ginfo['gift_price'] != $item['price']){
                exit("非法的单价数据" . $id . '-' . $item['price']);
            }
            $price += $item['price'] * $item['num'];
        }
        if(!$price){
            exit("校验错误，非法金额");
        }
        if($price != $sum_price){
            exit("校验失败，非法金额");
        }
        $data = array();
        $data['gift_data'] = serialize($jsonArr);
        $data['pay_money'] = $price;
        $data['openid'] = self :: $openid;
        if(strlen($data['gift_data']) > 1900){
            exit("数据超出范围，请分批次提交订单");
        }
        $order_id = GiftOrderModel :: addItem($data);
        if(!$order_id){
            exit("创建订单失败，请重试");
        }
        $this -> myGiftPay($price, $order_id);
    }
    function myGiftPay($fee, $order_id){
        global $_W, $_GPC;
        if($fee <= 0){
            message('支付错误, 金额小于0');
        }
        $tid = time() . rand(1000000, 9999999);
        $params = array('tid' => $tid . '-' . $order_id . '-giftorder', 'ordersn' => $tid, 'title' => '礼物订单支付', 'fee' => $fee, 'user' => $_W['member']['uid'],);
        $this -> pay($params);
    }
    public function GiftPayHandle($order_id){
        WeUtility :: logging('huayue_payresult_order_id', var_export($order_id, true));
        $oinfo = GiftOrderModel :: info($order_id);
        $gift_data = unserialize($oinfo['gift_data']);
        WeUtility :: logging('huayue_payresult_gift_data', var_export($gift_data, true));
        foreach($gift_data as $item){
            $r = GiftModel :: saleNumAdd($item['id']);
            $r = GiftUserModel :: pushItem($oinfo['openid'], $item['id'], $item['num']);
            WeUtility :: logging('huayue_payresult_error', var_export($r, true));
            if($r !== true){
            }
        }
        $notice = new NoticeComponent();
        $to_uinfo = MemberModel :: info($oinfo['openid']);
        $user_tpl['content'] = '支出金额：' . $oinfo['pay_money'];
        $user_tpl['url'] = $_W['siteroot'] . 'app/' . $this -> createMobileUrl('giftmall');
        $r = $notice -> sendGiftPayTpl($oinfo['openid'], '礼物订单支付成功', $user_tpl['content'], $user_tpl['url']);
    }
    public function doMobileMyGiftOrderList(){
        global $_GPC, $_W;
        $this -> super_title = "礼物购买记录";
        $list = GiftOrderModel :: getList(self :: $openid);
        foreach($list as $k => $v){
            $list[$k]['data'] = unserialize($v['gift_data']);
            foreach($list[$k]['data'] as $k1 => $v1){
                $list[$k]['data'][$k1]['gift_info'] = GiftModel :: info($v1['id']);
            }
        }
        include $this -> template('gift_order_list');
    }
    public function doMobileGiftPresent(){
        global $_GPC, $_W;
        $rid = $_GPC['room_id'];
        $user_gift_id = $_GPC['user_gift_id'];
        if(!$rid || !$user_gift_id){
            echoJson(array('res' => '101', 'msg' => '参数错误'));
        }
        if(!self :: $openid){
            echoJson(array('res' => '101', 'msg' => '访问错误'));
        }
        $rinfo = ChatroomModel :: info($rid);
        if(!$rinfo){
            echoJson(array('res' => '101', 'msg' => '房间错误'));
        }
        if($rinfo['creator'] == 'system'){
            echoJson(array('res' => '101', 'msg' => '房间类型错误'));
        }
        $owner = $rinfo['creator'];
        $user_gift_info = GiftUserModel :: getInfo($user_gift_id, self :: $openid);
        $gift_info = GiftModel :: info($user_gift_info['gift_id']);
        if(!$user_gift_info){
            echoJson(array('res' => '101', 'msg' => '尚未购买该礼物'));
        }
        if($user_gift_info['gift_num'] <= 0){
            echoJson(array('res' => '101', 'msg' => '礼物数量不足，请重新购买'));
        }
        pdo_begin();
        $r = GiftUserModel :: presentItem(self :: $openid, $user_gift_info['gift_id']);
        if($r !== true){
            pdo_rollback();
            echoJson(array('res' => '101', 'msg' => $r['msg']));
        }
        $r = ChatroomLogModel :: addItem($rid, self :: $openid, $user_gift_info['id'] . '|' . $user_gift_info['gift_id'] . '|' . $gift_info['gift_price'], 'gift');
        if(!$r){
            pdo_rollback();
            echoJson(array('res' => '101', 'msg' => '写入聊天表失败'));
        }
        $data = array();
        $data['openid'] = self :: $openid;
        $data['to_openid'] = $rinfo['creator'];
        $data['rid'] = $rid;
        $data['gift_id'] = $user_gift_info['gift_id'];
        $data['gift_price'] = $gift_info['gift_price'];
        $r = GiftPresentLogModel :: addItem($data);
        if(!$r){
            pdo_rollback();
            echoJson(array('res' => '101', 'msg' => '写入赠送记录表失败'));
        }
        $r = MemberModel :: addMoneyForGift($owner, $gift_info['gift_price']);
        if(!$r){
            pdo_rollback();
            echoJson(array('res' => '101', 'msg' => '更新用户资金表失败'));
        }
        pdo_commit();
        echoJson(array('res' => '100', 'msg' => '赠送成功'));
    }
    public function dowebtest(){
        echo tomedia('images/huayue/20160823215919_2037475672_8378.amr');
    }
    public function doWebManage(){
        global $_W, $_GPC;
       // $dog = new WatchDogComponent($this -> module);
        //$dog -> spy();
        include $this -> template('manage');
    }
    public function doWebSettings(){
        global $_W, $_GPC;
        $settings = $this -> settings;
        include $this -> template('settings');
    }
    function sendNoticeTpl($to_openid, $content, $url, $first = ''){
        global $_W;
        $to_userinfo = MemberModel :: info($to_openid);
        $notice_time = $this -> settings['notice_time'] ? $this -> settings['notice_time'] : 30;
        $t = time() - strtotime($to_userinfo['update_time']);
        if($t >= $notice_time && $to_userinfo['notice_times'] < 1 && $to_userinfo['is_notice'] == 'y'){
            $userinfo = MemberModel :: info(self :: $openid);
            $notice = new NoticeComponent;
            $notice -> sendNewNoticeTpl($to_openid, $userinfo['nickname'], $content, $url, $first);
            pdo_update('sunshine_huayue_member', array('notice_times' => $to_userinfo['notice_times'] + 1), array('openid' => $to_openid, 'uniacid' => $_W['uniacid']));
        }
    }
    function doWebnoticeset(){
        global $_W, $_GPC;
        include $this -> template('set_notice');
    }
    function doWebSignInSet(){
        global $_W, $_GPC;
        include $this -> template('set_signin');
    }
    function doWebfilterset(){
        global $_GPC, $_W;
        $words = FilterComponent :: readFilterInc();
        $words_list = explode('，', trim($words, '，'));
        include $this -> template('filterset');
    }
    public function doWebChatRoomManage(){
        global $_GPC, $_W;
        $list = ChatroomModel :: getList();
        include $this -> template('chatroom_manage');
    }
    public function doWebAddChatRoom(){
        global $_GPC, $_W;
        $res = ChatroomModel :: createChatRoom($_GPC['room_name'], $_GPC['room_desc'], tomedia($_GPC['room_logo']));
        echoJson($res);
    }
    public function doWebChatRoomApprove(){
        global $_GPC, $_W;
        $list = ChatroomModel :: getList('wait');
        foreach($list as $key => $item){
            if($item['creator'] != 'system'){
                $list[$key]['uinfo'] = MemberModel :: info($item['creator']);
            }else{
                $list[$key]['uinfo']['nickname'] = 'system';
            }
        }
        include $this -> template('chatroom_approve');
    }
    public function doWebChatRoomDeny(){
        global $_GPC, $_W;
        $list = ChatroomModel :: getList('deny');
        foreach($list as $key => $item){
            if($item['creator'] != 'system'){
                $list[$key]['uinfo'] = MemberModel :: info($item['creator']);
            }else{
                $list[$key]['uinfo']['nickname'] = 'system';
            }
        }
        include $this -> template('chatroom_deny');
    }
    public function doWebChatRoomDeleteTpl(){
        global $_GPC, $_W;
        $list = ChatroomModel :: getListByStatus('delete');
        foreach($list as $key => $item){
            if($item['creator'] != 'system'){
                $list[$key]['uinfo'] = MemberModel :: info($item['creator']);
            }else{
                $list[$key]['uinfo']['nickname'] = 'system';
            }
        }
        include $this -> template('chatroom_delete');
    }
    public function doWebChatroomApproveHandle(){
        global $_GPC, $_W;
        $cid = $_GPC['cid'];
        $status = $_GPC['status'];
        if(!is_numeric($cid) || ($status != 'allow' && $status != 'deny')){
            echoJson(array('res' => '101', 'msg' => '非法参数'));
        }
        $info = ChatroomModel :: info($cid);
        $uinfo = MemberModel :: info($info['creator']);
        if($status == 'allow'){
            if($info['room_type'] == 'lvb'){
                $lvb = new LiveVideoComponent();
                $r = $lvb -> CreateLVBChannel($info['room_name'], $info['room_desc'], 3, $uinfo['nickname'], 1);
                if($r -> code){
                    echoJson(array('res' => '101', 'msg' => '直播频道开通失败' . $r -> message));
                }
                $data = array();
                $data['rid'] = $info['id'];
                $data['openid'] = $info['creator'];
                $data['channel_id'] = $r -> channel_id;
                $data['protocol'] = $r -> channelInfo -> protocol;
                $data['upstream_address'] = $r -> channelInfo -> upstream_address;
                $data['rate_type'] = $r -> channelInfo -> downstream_address[0] -> rate_type;
                $data['rtmp_downstream_address'] = $r -> channelInfo -> downstream_address[0] -> rtmp_downstream_address;
                $data['flv_downstream_address'] = $r -> channelInfo -> downstream_address[0] -> flv_downstream_address;
                $data['hls_downstream_address'] = $r -> channelInfo -> downstream_address[0] -> hls_downstream_address;
                $r = LvbModel :: addItem($data);
                if(!$r){
                    echoJson(array('res' => '101', 'msg' => '写入频道信息表失败'));
                }
                $lvb_channel_id = $data['channel_id'];
            }elseif($info['room_type'] == 'letv'){
                $letv_buss = new LetvPlayBusiness();
                $r_buss = $letv_buss -> createActivity($info['room_name'], $info['room_logo'], $info['room_desc']);
                if(!$r_buss){
                    echoJson(array('res' => '101', 'msg' => '乐视云直播频道开通失败' . var_export($r_buss, true)));
                }
                $letv_com = new LivePlayComponent();
                $letv_com -> setSafe($r_buss -> activityId);
                $r_com = $letv_com -> getPushAddr($r_buss -> activityId);
                if(!$r_com -> lives[0] -> pushUrl){
                    echoJson(array('res' => '101', 'msg' => '乐视云获取推流地址失败' . var_export($r_com, true)));
                }
                $data = array();
                $data['rid'] = $info['id'];
                $data['openid'] = $info['creator'];
                $data['activity_id'] = $r_buss -> activityId;
                $data['push_url'] = $r_com -> lives[0] -> pushUrl;
                $r = LetvModel :: addItem($data);
                if(!$r){
                    echoJson(array('res' => '101', 'msg' => '写入频道信息表失败'));
                }
                $lvb_channel_id = $data['activity_id'];
            }
        }
        pdo_update("sunshine_huayue_chatroom", array('is_approve' => $status, 'lvb_channel_id' => $lvb_channel_id), array('id' => $cid, 'uniacid' => $_W['uniacid']));
        if($info){
            $first = "您申请创建的聊天室“{$info['room_name']}”已经进行审核";
            $status = $status == 'allow' ? '审核通过' : '审核未通过';
            $key_1 = $status;
            $remark = "如有问题请联系管理员";
            $notice = new NoticeComponent();
            $notice -> sendApproveTpl($info['creator'], $first, $key_1, $remark, $_W['siteroot'] . 'app/' . $this -> createMobileUrl('index'));
        }
        echoJson(array('res' => '100', 'msg' => 'success'));
    }
    public function doWebChatRoomSet(){
        global $_W, $_GPC;
        $room_id = $_GPC['room_id'];
        if(!$room_id){
            exit("非法访问");
        }
        $info = ChatroomModel :: info($room_id);
        if(!$info){
            exit("房间不存在");
        }
        $list = ChatroomLogModel :: history($room_id, '', 300);
        if($list){
            foreach($list as $key => $item){
                $chatlist[$key] = $item;
                $chatlist[$key]['userinfo'] = MemberModel :: info($item['openid']);
                if($item['type'] == 'voice'){
                    $chatlist[$key]['voice_info'] = VoiceLogMode :: getInfo($item['id']);
                }
            }
        }
        include $this -> template('chatroomset');
    }
    public function doWebChatRoomUpdate(){
        global $_W, $_GPC;
        $room_id = $_GPC['room_id'];
        $room_name = $_GPC['room_name'];
        $room_desc = $_GPC['room_desc'];
        $room_logo = $_GPC['room_logo'];
        $sort_id = $_GPC['sort_id'];
        $data = array();
        $data['room_name'] = $room_name;
        $data['room_desc'] = $room_desc;
        $data['room_logo'] = tomedia($room_logo);
        $data['sort_id'] = $sort_id;
        $res = pdo_update('sunshine_huayue_chatroom', $data, array('id' => $room_id, 'uniacid' => $_W['uniacid']));
        echoJson(array('res' => '100', 'msg' => '更新成功'));
    }
    public function doWebChatRoomDelete(){
        global $_W, $_GPC;
        $room_id = $_GPC['room_id'];
        if(!is_numeric($room_id)){
            echoJson(array('res' => '101', 'msg' => '非法访问'));
        }
        $res = pdo_update('sunshine_huayue_chatroom', array('room_status' => 'delete'), array('id' => $room_id, 'uniacid' => $_W['uniacid']));
        if($res){
            echoJson(array('res' => '100', 'msg' => '更新成功'));
        }
        echoJson(array('res' => '101', 'msg' => 'fail'));
    }
    public function doWebMultisendtpl(){
        global $_GPC, $_W;
        $list = pdo_fetchall("select * from " . tablename('sunshine_huayue_multisend') . " where uniacid={$_W['uniacid']} order by id desc");
        include $this -> template('multi_send');
    }
    public function doWebMultisend(){
        global $_GPC, $_W;
        $content = $_GPC['send_content'];
        if(!$content){
            echoJson(array('res' => '104', 'msg' => '内容不能为空！未发送'));
        }
        $filter = FilterComponent :: init($content);
        if($filter){
            echoJson(array('res' => '104', 'msg' => '包含敏感词' . join(',', $filter)));
        }
        $data = array();
        $data['uniacid'] = $_W['uniacid'];
        $data['creator'] = 'system';
        $data['content'] = $content;
        $data['add_time'] = date("Y-m-d H:i:s");
        pdo_begin();
        $res = pdo_insert('sunshine_huayue_multisend', $data);
        if(!$res){
            pdo_rollback();
            echoJson(array('res' => '101', 'msg' => '写入数据库失败，未发送'));
        }
        $list = pdo_fetchall("select * from " . tablename('sunshine_huayue_member') . " where uniacid={$_W['uniacid']}");
        if(!$list){
            pdo_rollback();
            echoJson(array('res' => '101', 'msg' => '获取用户群失败，未发送'));
        }
        $i = 0;
        $multi_send_url = $this -> settings['multi_send_url'] ? $this -> settings['multi_send_url'] : $_W['siteroot'] . 'app/' . $this -> createMobileUrl('index');
        foreach($list as $item){
            $this -> sendNoticeTpl($item['openid'], $content, $multi_send_url);
            $i ++;
        }
        $res = array();
        $res['res'] = '100';
        $res['msg'] = '发送成功：总人数' . count($list) . ';发送成功人数' . $i;
        pdo_commit();
        echoJson($res);
    }
    public function doWebsetopt(){
        global $_W, $_GPC;
        include $this -> template('set_opt');
    }
    public function doWebsetadmin(){
        global $_GPC, $_W;
        $list = AdminModel :: getList();
        foreach ($list as $key => $value){
            $list[$key]['uinfo'] = MemberModel :: info($value['openid']);
        }
        $token = md5(time());
        $set['uniacid'] = $_W['uniacid'];
        $set['name'] = 'bind_admin_qrcode_token';
        $set['value'] = $token;
        if($this -> settings['bind_admin_qrcode_token']){
            pdo_update('sunshine_huayue_setting', array('value' => $token), array('name' => 'bind_admin_qrcode_token', 'uniacid' => $_W['uniacid']));
        }else{
            pdo_insert('sunshine_huayue_setting', $set);
        }
        $qrcodeUrl = createQrcode($_W['siteroot'] . 'app/' . $this -> createMobileUrl('bindadmin', array('qrcodetoken' => $token)));
        include $this -> template('set_admin');
    }
    public function doWebRemoveadmin(){
        global $_W, $_GPC;
        $openid = $_GPC['openid'];
        pdo_update('sunshine_huayue_admin', array('is_del' => 'y'), array('uniacid' => $_W['uniacid'], 'openid' => $openid));
        echoJson(array('res' => '100', 'msg' => '移除成功'));
    }
    public function doWebsetvip(){
        global $_W, $_GPC;
        include $this -> template('set_vip');
    }
    public function doWebCommentManage(){
        global $_W, $_GPC;
        $page = $_GPC['page'];
        $pagesize = 30;
        $page <= 0 && $page = 1;
        $begin = $page ? ($page-1) * $pagesize:0;
        $list = CommentModel :: getAll($begin, $pagesize);
        foreach($list as $key => $item){
            $list[$key]['uinfo'] = MemberModel :: info($item['user_openid']);
        }
        $count = CommentModel :: sumNum();
        include $this -> template('comment_manage');
    }
    public function doWebcommentdelete(){
        global $_W, $_GPC;
        if(!$_GPC['comment_id']){
            echoJson(array('res' => '101', 'msg' => 'error'));
        }
        $cid = $_GPC['comment_id'];
        pdo_update('sunshine_huayue_comment', array('is_del' => 'y', 'del_time' => date('Y-m-d H:i:s')), array('id' => $cid, 'uniacid' => $_W['uniacid']));
        echoJson(array("res" => "100", 'msg' => 'success'));
    }
    public function doWebSetPageIndex(){
        global $_W, $_GPC;
        include $this -> template('set_page_index');
    }
    public function doWebSetPageHall(){
        global $_W, $_GPC;
        include $this -> template('set_page_hall');
    }
    public function doWebSetPageMenu(){
        global $_W, $_GPC;
        include $this -> template('set_page_menu');
    }
    public function doWebSetRobot(){
        global $_W;
        $info = MemberModel :: info('i_robot');
        $chatroomlist = ChatroomModel :: getFrontList();
        include $this -> template('set_robot');
    }
    public function doWebChatroomSetRobot(){
        global $_GPC, $_W;
        $id = $_GPC['id'];
        $is_robot = $_GPC['is_robot'];
        ChatroomModel :: setRobot($id, $is_robot);
        echoJson(array('res' => 100, 'msg' => 'succes'));
    }
    public function doWebMomentsManageList(){
        global $_W, $_GPC;
        $page = $_GPC['page'];
        !$page && $page = 1;
        $pagesize = 30;
        $list = MomentsModel :: loadMomentList('', $page, $pagesize);
        $list = MemberModel :: listJoin($list);
        $count = MomentsModel :: sumNums();
        include $this -> template('moments_manage_list');
    }
    public function doWebMomentDeleteAjax(){
        global $_GPC, $_W;
        $mid = $_GPC['mid'];
        if(!$mid){
            echoJson(array('res' => 101, 'msg' => 'mid is error'));
        }
        $res = MomentsModel :: delete($mid);
        if($res){
            echoJson(array('res' => 100, 'msg' => 'success'));
        }else{
            echoJson(array('res' => 101, 'msg' => 'other error'));
        }
    }
    public function doWebMomentsDeleteList(){
        global $_W, $_GPC;
        $page = $_GPC['page'];
        !$page && $page = 1;
        $pagesize = 30;
        $list = MomentsModel :: loadMomentList('', $page, $pagesize, '', 'y');
        $list = MemberModel :: listJoin($list);
        $count = MomentsModel :: sumNums('y');
        include $this -> template('moments_delete_list');
    }
    public function doWebMomentRestoreAjax(){
        global $_GPC, $_W;
        $mid = $_GPC['mid'];
        if(!$mid){
            echoJson(array('res' => 101, 'msg' => 'mid is error'));
        }
        $res = MomentsModel :: restore($mid);
        if($res){
            echoJson(array('res' => 100, 'msg' => 'success'));
        }else{
            echoJson(array('res' => 101, 'msg' => 'other error'));
        }
    }
    public function doWebUserManageList(){
        if(checksubmit('keyword', true)){
        }
        global $_W, $_GPC;
        $pagesize = 30;
        $keyword = $_GPC['keyword'];
        $page = $_GPC['page'] ? $_GPC['page'] : 1;
        $begin = ($page-1) * $pagesize;
        $sex = $_GPC['sex'];
        $vip_level = $_GPC['vip_level'];
        $mobile_status = $_GPC['mobile_status'];
        $forbid_status = $_GPC['forbid_status'];
        $sort_type = $_GPC['sort_type'];
        $sort_val = $_GPC['sort_val'];
        $cond = array();
        if($sex === '1' or $sex === '2'){
            $cond['sex'] = $sex;
        }
        if($vip_level === '1' or $vip_level === '0'){
            $cond['vip_level'] = $vip_level;
        }
        if($mobile_status === 'y' or $mobile_status === 'n'){
            $cond['mobile_status'] = $mobile_status;
        }
        if($forbid_status === 'y' or $forbid_status === 'n'){
            $cond['forbid_status'] = $forbid_status;
        }
        $list = MemberModel :: searchList($keyword, $cond, $begin, $pagesize, $sort_type, $sort_val);
        $count = MemberModel :: searchListCounts($keyword, $cond, $begin, $pagesize);
        include $this -> template('user_manage_list');
    }
    public function doWebOpenVipByAdmin(){
        global $_W, $_GPC;
        $openid = $_GPC['user_id'];
        if(!$openid){
            echoJson(array('res' => '101', 'msg' => 'openid is error'));
        }
        $info = MemberModel :: info($openid);
        if(!$info){
            echoJson(array('res' => '101', 'msg' => 'get userinfo error'));
        }
        $vip_handle = new VipComponent($openid);
        $res = $vip_handle -> updateToVip();
        if($res){
            echoJson(array('res' => '100', 'msg' => '开通会员成功，有效期-' . date('Y-m-d H:i:s')));
        }else{
            if($vip_handle -> isVip()){
                echoJson(array('res' => '101', 'msg' => '开通会员失败，对方可能已经是会员身份'));
            }else{
                echoJson(array('res' => '101', 'msg' => '开通会员失败，未知错误'));
            }
        }
    }
    public function doWebRemoveVipByAdmin(){
        global $_W, $_GPC;
        $openid = $_GPC['user_id'];
        if(!$openid){
            echoJson(array('res' => '101', 'msg' => 'openid is error'));
        }
        $info = MemberModel :: info($openid);
        if(!$info){
            echoJson(array('res' => '101', 'msg' => 'get userinfo error'));
        }
        $vip_handle = new VipComponent($openid);
        $res = $vip_handle -> dropVip($openid);
        if($res){
            echoJson(array('res' => '100', 'msg' => '取消会员状态成功'));
        }else{
            echoJson(array('res' => '101', 'msg' => '取消会员状态失败'));
        }
    }
    public function doWebRemoveOutFromSystem(){
        global $_W, $_GPC;
        $openid = $_GPC['user_id'];
        if(!$openid){
            echoJson(array('res' => '101', 'msg' => 'openid is error'));
        }
        $res = ForbidComponent :: remove($openid);
        if($res){
            echoJson(array('res' => '100', 'msg' => '踢出系统成功'));
        }
        echoJson(array('res' => '101', 'msg' => '踢出系统失败，未知错误'));
    }
    public function doWebRestoreFromSystem(){
        global $_W, $_GPC;
        $openid = $_GPC['user_id'];
        if(!$openid){
            echoJson(array('res' => '101', 'msg' => 'openid is error'));
        }
        $res = ForbidComponent :: restore($openid);
        if($res){
            echoJson(array('res' => '100', 'msg' => '恢复用户系统权限成功'));
        }
        echoJson(array('res' => '101', 'msg' => '恢复用户系统权限失败，未知错误'));
    }
    public function doWebSetSms(){
        global $_W, $_GPC;
        include $this -> template('set_alidayu');
    }
    public function doWebSetCert(){
        global $_W, $_GPC;
        include $this -> template('set_cert');
    }
    public function doWebSaveClientCert(){
        SaveCertComponent :: save('appclient_cert_path', $this -> createWebUrl('setcert'));
    }
    public function doWebSaveClientKey(){
        SaveCertComponent :: save('appclient_key_path', $this -> createWebUrl('setcert'));
    }
    public function doWebSaveRootCa(){
        SaveCertComponent :: save('rootca_path', $this -> createWebUrl('setcert'));
    }
    public function doWebUserDrawManage(){
        global $_W, $_GPC;
        $page = $_GPC['page'] ? $_GPC['page'] : 1;
        $pagesize = 20;
        $begin = ($page-1) * $pagesize;
        $list = DrawLogModel :: getList('wait', $begin, $pagesize);
        $list = MemberModel :: listJoin($list);
        $count = DrawLogModel :: getListCount('wait');
        include $this -> template('user_draw_manage');
    }
    public function doWebUserDrawManageHistory(){
        global $_W, $_GPC;
        $page = $_GPC['page'] ? $_GPC['page'] : 1;
        $pagesize = 20;
        $begin = ($page-1) * $pagesize;
        $list = DrawLogModel :: getList('handle', $begin, $pagesize);
        $list = MemberModel :: listJoin($list);
        $count = DrawLogModel :: getListCount('handle');
        include $this -> template('user_draw_manage_history');
    }
    public function doWebUserDrawConfirm(){
        global $_W, $_GPC;
        $draw_id = $_GPC['draw_id'];
        if(!$draw_id){
            echoJson(array('res' => '101', 'msg' => '错误参数'));
        }
        if(!checksubmit()){
            echoJson(array('res' => '101', 'msg' => '非法提交'));
        }
        if($this -> settings['redpack_key'] !== 'open'){
            echoJson(array('res' => '101', 'msg' => '尚未开启现金红包开关，请到支付设置中进行配置'));
        }
        $drawinfo = DrawLogModel :: info($draw_id);
        if($drawinfo['status'] !== 'wait'){
            echoJson(array('res' => '101', 'msg' => '非法状态'));
        }
        pdo_begin();
        $r = DrawLogModel :: setHandle($draw_id);
        if(!$r){
            pdo_rollback();
            echoJson(array('res' => '101', 'msg' => '更改日志状态失败'));
        }
        $redPack = new RedPackComponent($this -> settings);
        $r = $redPack -> sendRedPack($drawinfo['act_draw'], $drawinfo['openid']);
        if($r){
            pdo_commit();
            echoJson(array('res' => '100', 'msg' => '提现成功'));
        }
        pdo_rollback();
        echoJson(array('res' => '101', 'msg' => '提现失败'));
    }
    public function doWebFeedbackList(){
        global $_W, $_GPC;
        $status = $_GPC['status'] ? $_GPC['status'] : 'wait';
        $page = $_GPC['page'] ? $_GPC['page'] : 1;
        $pagesize = 50;
        $begin = ($page-1) * 50;
        $count = FeedbackModel :: getCountByStatus($status);
        $list = FeedbackModel :: getListByStatus($status, $begin, $pagesize);
        $list = MemberModel :: listJoin($list);
        include $this -> template('feedback_list');
    }
    public function doWebFeedbackHandle(){
        global $_W, $_GPC;
        if(!$_GPC['feedback_id']){
            echoJson(array('res' => '101', 'msg' => '失败'));
        }
        $r = FeedbackModel :: setHandle($_GPC['feedback_id']);
        if($r){
            echoJson(array('res' => '100', 'msg' => '成功'));
        }
        echoJson(array('res' => '101', 'msg' => '修改失败'));
    }
    public function doWebSuperPoint(){
        global $_W, $_GPC;
        $list = MenuModel :: getListByType('superpoint');
        include $this -> template('set_superpoint');
    }
    public function doWebSuperPointAdd(){
        global $_W, $_GPC;
        $superpoint_order_id = $_GPC['superpoint_order_id'];
        $superpoint_name = $_GPC['superpoint_name'];
        $superpoint_url = $_GPC['superpoint_url'];
        if(!$superpoint_order_id || !$superpoint_name || !$superpoint_url){
            echoJson(array('res' => '101', 'msg' => '添加失败，不能为空'));
        }
        $data['uniacid'] = $_W['uniacid'];
        $data['type'] = 'superpoint';
        $data['order_id'] = $superpoint_order_id;
        $data['name'] = $superpoint_name;
        $data['url'] = $superpoint_url;
        $data['intro'] = '';
        $r = MenuModel :: addItem($data);
        if($r){
            echoJson(array('res' => '100', 'msg' => 'success'));
        }else{
            echoJson(array('res' => '101', 'msg' => '添加失败'));
        }
    }
    public function doWebSuperPointEdit(){
        global $_W, $_GPC;
        $superpoint_id = $_GPC['superpoint_id'];
        $superpoint_order_id = $_GPC['superpoint_order_id'];
        $superpoint_name = $_GPC['superpoint_name'];
        $superpoint_url = $_GPC['superpoint_url'];
        $superpoint_is_del = $_GPC['superpoint_is_del'];
        if(!$superpoint_order_id || !$superpoint_name || !$superpoint_url){
            echoJson(array('res' => '101', 'msg' => '更新失败，不能为空'));
        }
        $data['uniacid'] = $_W['uniacid'];
        $data['type'] = 'superpoint';
        $data['order_id'] = $superpoint_order_id;
        $data['name'] = $superpoint_name;
        $data['url'] = $superpoint_url;
        $data['intro'] = '';
        $data['is_del'] = $superpoint_is_del == 'y' ? $superpoint_is_del : 'n';
        $where['id'] = $superpoint_id;
        $where['uniacid'] = $_W['uniacid'];
        $r = MenuModel :: updateItem($data, $where);
        if($r){
            echoJson(array('res' => '100', 'msg' => 'success'));
        }else{
            echoJson(array('res' => '101', 'msg' => '更新失败'));
        }
    }
    public function doWebSet_lvbsecret(){
        global $_W, $_GPC;
        include $this -> template('set_lvbsecret');
    }
    public function doWebLvbManage(){
        global $_W, $_GPC;
        if($_GPC['channelStatus'] == 'all'){
            $channelStatus = '';
        }else{
            $channelStatus = $_GPC['channelStatus'];
        }
        $page = $_GPC['page'] ? $_GPC['page'] : 1;
        $pagesize = 20;
        $lvb = new LiveVideoComponent();
        $res = $lvb -> DescribeLVBChannelList($channelStatus, $page, $pagesize);
        $count = $res -> all_count;
        if($res -> code !== 0){
            exit("有报错：错误码：{$res->code},错误信息：{$res->message}");
        }
        include $this -> template('lvb_manage');
    }
    public function doWebsetLetv(){
        global $_W, $_GPC;
        include $this -> template('set_letv');
    }
    public function doWebGiftMall(){
        global $_W, $_GPC;
        $list = GiftModel :: getListAll();
        include $this -> template('gift_mall');
    }
    public function doWebGiftAdd(){
        global $_W, $_GPC;
        include $this -> template('gift_add');
    }
    public function doWebGiftAddSubmit(){
        global $_GPC, $_W;
        $data = array();
        $data['gift_name'] = $_GPC['gift_name'];
        $data['sort_id'] = $_GPC['sort_id'];
        $data['gift_price'] = $_GPC['gift_price'];
        $data['gift_pic'] = tomedia($_GPC['gift_pic']);
        $r = GiftModel :: isNameRepeat($data['gift_name']);
        if($r){
            echoJson(array('res' => '101', 'msg' => '礼物名称已存在，请填写别的名称'));
        }
        $r = GiftModel :: addItem($data);
        if(!$r){
            echoJson(array('res' => '101', 'msg' => '添加礼物失败'));
        }
        echoJson(array('res' => '100', 'msg' => '添加礼物成功'));
    }
    public function doWebGiftUpdate(){
        global $_GPC, $_W;
        $id = $_GPC['gift_id'];
        if(!$id){
            echoJson(array('res' => '101', 'msg' => '保存失败id'));
        }
        $update_type = $_GPC['update_type'];
        if($update_type == 'normal'){
            $data = array();
            $data['gift_name'] = $_GPC['gift_name'];
            $data['sort_id'] = $_GPC['sort_id'];
            $data['gift_price'] = $_GPC['gift_price'];
            $r = GiftModel :: isNameRepeat($data['gift_name']);
            if($r){
                echoJson(array('res' => '101', 'msg' => '礼物名称已存在，请填写别的名称'));
            }
        }elseif($update_type == 'is_del'){
            $data = array();
            $data['is_del'] = $_GPC['is_del'] == 'y' ? 'y' :'n';
        }elseif($update_type == 'pic'){
            $data = array();
            $data['gift_pic'] = tomedia($_GPC['gift_pic']);
        }
        $r = GiftModel :: update($id, $data);
        if(!$r){
            echoJson(array('res' => '101', 'msg' => 'update失败'));
        }
        echoJson(array('res' => '100', 'msg' => '更新成功'));
    }
    public function doWebGiftPicChange(){
        global $_W, $_GPC;
        $id = $_GPC['id'];
        if(!$id){
            exit(' id is error ');
        }
        $info = GiftModel :: info($id);
        include $this -> template('gift_pic_change');
    }
    protected function pay($params = array(), $mine = array()){
        global $_W;
        if(!$this -> inMobile){
            message('支付功能只能在手机上使用');
        }
        $params['module'] = $this -> module['name'];
        $pars = array();
        $pars[':uniacid'] = $_W['uniacid'];
        $pars[':module'] = $params['module'];
        $pars[':tid'] = $params['tid'];
        if($params['fee'] <= 0){
            $pars['from'] = 'return';
            $pars['result'] = 'success';
            $pars['type'] = '';
            $pars['tid'] = $params['tid'];
            $site = WeUtility :: createModuleSite($pars[':module']);
            $method = 'payResult';
            if (method_exists($site, $method)){
                exit($site -> $method($pars));
            }
        }
        $sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid';
        $log = pdo_fetch($sql, $pars);
        if (empty($log)){
            $log = array('uniacid' => $_W['uniacid'], 'acid' => $_W['acid'], 'openid' => $_W['member']['uid'], 'module' => $this -> module['name'], 'tid' => $params['tid'], 'fee' => $params['fee'], 'card_fee' => $params['fee'], 'status' => '0', 'is_usecard' => '0',);
            pdo_insert('core_paylog', $log);
        }
        if($log['status'] == '1'){
            message('这个订单已经支付成功, 不需要重复支付.');
        }
        $setting = uni_setting($_W['uniacid'], array('payment', 'creditbehaviors'));
        if(!is_array($setting['payment'])){
            message('没有有效的支付方式, 请联系网站管理员.');
        }
        $pay = $setting['payment'];
        if (empty($_W['member']['uid'])){
            $pay['credit']['switch'] = false;
        }
        if (!empty($pay['credit']['switch'])){
            $credtis = mc_credit_fetch($_W['member']['uid']);
        }
        $you = 0;
        if($pay['card']['switch'] == 2 && !empty($_W['openid'])){
            if($_W['card_permission'] == 1 && !empty($params['module'])){
                $cards = pdo_fetchall('SELECT a.id,a.card_id,a.cid,b.type,b.title,b.extra,b.is_display,b.status,b.date_info FROM ' . tablename('coupon_modules') . ' AS a LEFT JOIN ' . tablename('coupon') . ' AS b ON a.cid = b.id WHERE a.acid = :acid AND a.module = :modu AND b.is_display = 1 AND b.status = 3 ORDER BY a.id DESC', array(':acid' => $_W['acid'], ':modu' => $params['module']));
                $flag = 0;
                if(!empty($cards)){
                    foreach($cards as $temp){
                        $temp['date_info'] = iunserializer($temp['date_info']);
                        if($temp['date_info']['time_type'] == 1){
                            $starttime = strtotime($temp['date_info']['time_limit_start']);
                            $endtime = strtotime($temp['date_info']['time_limit_end']);
                            if(TIMESTAMP < $starttime || TIMESTAMP > $endtime){
                                continue;
                            }else{
                                $param = array(':acid' => $_W['acid'], ':openid' => $_W['openid'], ':card_id' => $temp['card_id']);
                                $num = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('coupon_record') . ' WHERE acid = :acid AND openid = :openid AND card_id = :card_id AND status = 1', $param);
                                if($num <= 0){
                                    continue;
                                }else{
                                    $flag = 1;
                                    $card = $temp;
                                    break;
                                }
                            }
                        }else{
                            $deadline = intval($temp['date_info']['deadline']);
                            $limit = intval($temp['date_info']['limit']);
                            $param = array(':acid' => $_W['acid'], ':openid' => $_W['openid'], ':card_id' => $temp['card_id']);
                            $record = pdo_fetchall('SELECT addtime,id,code FROM ' . tablename('coupon_record') . ' WHERE acid = :acid AND openid = :openid AND card_id = :card_id AND status = 1', $param);
                            if(!empty($record)){
                                foreach($record as $li){
                                    $time = strtotime(date('Y-m-d', $li['addtime']));
                                    $starttime = $time + $deadline * 86400;
                                    $endtime = $time + $deadline * 86400 + $limit * 86400;
                                    if(TIMESTAMP < $starttime || TIMESTAMP > $endtime){
                                        continue;
                                    }else{
                                        $flag = 1;
                                        $card = $temp;
                                        break;
                                    }
                                }
                            }
                            if($flag){
                                break;
                            }
                        }
                    }
                }
                if($flag){
                    if($card['type'] == 'discount'){
                        $you = 1;
                        $card['fee'] = sprintf("%.2f", ($params['fee'] * ($card['extra'] / 100)));
                    }elseif($card['type'] == 'cash'){
                        $cash = iunserializer($card['extra']);
                        if($params['fee'] >= $cash['least_cost']){
                            $you = 1;
                            $card['fee'] = sprintf("%.2f", ($params['fee'] - $cash['reduce_cost']));
                        }
                    }
                    load() -> classs('coupon');
                    $acc = new coupon($_W['acid']);
                    $card_id = $card['card_id'];
                    $time = TIMESTAMP;
                    $randstr = random(8);
                    $sign = array($card_id, $time, $randstr, $acc -> account['key']);
                    $signature = $acc -> SignatureCard($sign);
                    if(is_error($signature)){
                        $you = 0;
                    }
                }
            }
        }
        if($pay['card']['switch'] == 3 && $_W['member']['uid']){
            $cards = array();
            if(!empty($params['module'])){
                $cards = pdo_fetchall('SELECT a.id,a.couponid,b.type,b.title,b.discount,b.condition,b.starttime,b.endtime FROM ' . tablename('activity_coupon_modules') . ' AS a LEFT JOIN ' . tablename('activity_coupon') . ' AS b ON a.couponid = b.couponid WHERE a.uniacid = :uniacid AND a.module = :modu AND b.condition <= :condition AND b.starttime <= :time AND b.endtime >= :time  ORDER BY a.id DESC', array(':uniacid' => $_W['uniacid'], ':modu' => $params['module'], ':time' => TIMESTAMP, ':condition' => $params['fee']), 'couponid');
                if(!empty($cards)){
                    foreach($cards as $key => & $card){
                        $has = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('activity_coupon_record') . ' WHERE uid = :uid AND uniacid = :aid AND couponid = :cid AND status = 1' . $condition, array(':uid' => $_W['member']['uid'], ':aid' => $_W['uniacid'], ':cid' => $card['couponid']));
                        if($has > 0){
                            if($card['type'] == '1'){
                                $card['fee'] = sprintf("%.2f", ($params['fee'] * $card['discount']));
                                $card['discount_cn'] = sprintf("%.2f", $params['fee'] * (1 - $card['discount']));
                            }elseif($card['type'] == '2'){
                                $card['fee'] = sprintf("%.2f", ($params['fee'] - $card['discount']));
                                $card['discount_cn'] = $card['discount'];
                            }
                        }else{
                            unset($cards[$key]);
                        }
                    }
                }
            }
            if(!empty($cards)){
                $cards_str = json_encode($cards);
            }
        }
        include $this -> template('paycenter');
    }
}

?>