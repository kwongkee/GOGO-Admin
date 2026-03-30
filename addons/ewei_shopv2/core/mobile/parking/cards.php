<?php
if ( !defined('IN_IA') ) {
    exit('Access Denied');
}

class Cards_EweiShopV2Page extends mobilePage
{
    
    public function __construct ()
    {
        parent::__construct();
        load()->func("common");
        isUserReg();
    }
    
    public function main ()
    {
        global $_GPC;
        global $_W;
        $title = '上传所需文件';
        $timer = time();
        if ( empty($_GPC['type_id']) || !isset($_GPC['type_id']) ) {
            $this->message('请选择方案', mobileUrl('parking/month_card'),'error');
        }
        $verUser = pdo_get('parking_verified', ['openid' => $_W['openid']]);

        if ( (empty($verUser) && empty($verUser['idcard'])) || empty($verUser['license']) ) {
            header("Location:" . mobileUrl('parking/alert_message/monthMsg') . "&mid=" . $_GPC['type_id']);
            exit();
        }
        $userInfo      = pdo_get('parking_authorize', ['openid' => $_W['openid']]);
        $cardApplyTime = pdo_get('parking_month_type', ['id' => $_GPC['type_id']]);
        
        if (empty($cardApplyTime)){
            $this->message('未有该月卡方案','','error');
        }
        
        $applyCount      = pdo_fetch("select count(id) as num FROM ".tablename('parking_apply')." where is_accept=1 and m_id=".$_GPC['type_id']);
        if ( $userInfo['auth_status'] == 0 ) {
            header("Location:" . mobileUrl('parking/alert_message/monthMsg') . "&mid=" . $_GPC['type_id']);
            exit();
        }
    
      
        if ( $applyCount['num'] >= $cardApplyTime['month_num'] ) {
            if ( $timer <= $cardApplyTime['apply_start'] || $timer >= $cardApplyTime['apply_end'] ) {
                $this->message('不在申请期','','error');
            }
        }
        
    
        
        $userCard = pdo_fetch('select * from ' . tablename('parking_apply') . ' where user_id="' . $_W['openid'] . '" and is_done="N" order by id desc');
        $list  = pdo_getall("parking_cre", ['uniacid' => $_W['uniacid']]);
        $buyCard = pdo_get('parking_month_pay', ['user_id' => $_W['openid'],'status'=>'A']);
        $lists = null;
        foreach ($list as $value) {
            $lists .= $value['name'] . ',';
        }
        $lists = trim($lists, ',');
        if (!empty($userCard)){
            if ( $userCard['is_done'] != 'Y' ) {
                $this->message('已有申请月卡','','error');
            }
        }
        if ( !empty($buyCard)) {
            $this->message('已有月卡！',mobileUrl('parking/user_month'),'error');
        }
    
        include $this->template("parking/cards");
    
    
    
    }
    
    public function UploadImageSave ()
    {
        global $_GPC;
        global $_W;
        load()->func('file');
        $img = $_GPC['images'];
        if ( preg_match('/^(data:\s*image\/(\w+);base64,)/', $img, $result) ) {
            $imgs = str_replace($result['1'], '', $img);
        }
        $name  = mt_rand(0000, 9999) . mt_rand(0000, 9999) . time();
        $names = $_COOKIE['imagename'];
        if ( isset($names) ) {
            setcookie("imagename", $names .= $name . ',');
        } else {
            setcookie("imagename", $names = $name . ',');
        }
        $imgs = base64_decode($imgs);
        @file_put_contents("../attachment/images/verifed/" . $name . ".jpg", $imgs);
        unset($img, $imgs);
        show_json(1);
    }
    
    public function hasApply ( $openid )
    {
        return pdo_get('parking_apply', ['user_id' => $openid, 'is_done' => 'N']);
    }
    
    public function inserData ()
    {
        global $_GPC;
        global $_W;
        $errCode = null;
        $msg     = null;
        $data    = [];
        $name    = $_COOKIE['imagename'];
        
        if ( is_null($name) ) {
            show_json(0, '请上传审核资料');
        }
        
        
        if ( !empty($this->hasApply($_W['openid'])) ) {
            show_json(0, '已申请,切勿重复提交');
        }
        $isMonth = pdo_get('parking_month_pay', ['user_id' => $_W['openid'],'status'=>'A']);
        if(!empty($isMonth)){
            show_json(0, '已存在月卡');
        }
        
        $cardName = pdo_get('parking_month_type', ['id' => $_GPC['mid']], ['month_name']);
        $user     = pdo_get('parking_verified', ['openid' => $_W['openid']]);
        $data     = ['user_id' => $_W['openid'], 'user_name' => $user['uname'], 'm_id' => $_GPC['mid'], 'sub_info' => $name, 'create_at' => time(), 'uniacid' => $_W['uniacid']];
        pdo_begin();
        try {
            pdo_insert('parking_apply', $data);
            pdo_commit();
            setcookie('imagename', null);
            $errCode = 1;
            $this->sendApplyWxMsg($_W['openid'], $cardName['month_name']);
        } catch (Exception $exception) {
            pdo_rollback();
            $errCode = 0;
            $msg     = $exception->getMessage();
        }
        //show_json(0, $exception->getMessage());
        show_json($errCode, $msg);
        
    }
    
    public function sendApplyWxMsg ( $openid, $cardType )
    {
        load()->classs('weixin.account');
        load()->func('logging');
        $template    = ['first' => ['value' => "您的月卡申请已经提交，运营中心将尽快处理",], 'keyword1' => ['value' => '受理中',], 'keyword2' => ['value' => date('Y-m-d H:i:s', time()),], 'keyword3' => ['value' => "申请卡类：" . $cardType,], 'remark' => ['value' => "",]];//消息模板
        $account_api = WeAccount::create();
        $status      = $account_api->sendTplNotice($openid, 'nXuU7WtKzVn-WZJoroWaO8Dev6yi__lEHB-gP__PTr0', $template);
        logging_run($status);
    }
}