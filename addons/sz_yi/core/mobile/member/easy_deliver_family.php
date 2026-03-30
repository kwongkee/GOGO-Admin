<?php

// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;


class familyBind extends Core {

    public function main(){
        global $_GPC;
        global $_W;

        $this->verifForm($_GPC);
        //$this->verifPhoneName($_GPC['name'],$_GPC['phone']);
        $this->verifIdCard($_GPC['name'],$_GPC['idcard']);
        $uid = $this->_getUidByUnionid($_W['fans']['unionid']);
        $this->_save($_GPC,$uid['id']);
        return '绑定成功，请到电脑端登录！';
    }


    public function verifForm($formParams)
    {
        if ($formParams['name'] == "") {
            throw new \Exception('姓名不能为空');
        }
        if (!preg_match("/^1[3456789]\d{9}$/", $formParams['phone'])) {
            throw new \Exception('手机格式不正确');
        }
        if ($formParams['address'] == "") {
            throw new \Exception('地址不能为空');
        }
        preg_match('/(.*?(省|自治区|北京市|天津市))+(.*?(市|自治州|地区|区划|县))+(.*?(区|县|镇|乡|街道))/', $formParams['address'], $matches);
        if (empty($matches)){
            throw new \Exception('地址省市区县街道请填写正确');
        }
        
        if($formParams['nocheck'] == 0 )
        {
            if (($_SESSION['codetime'] + (60 * 5)) < time()) {
                throw new \Exception('验证码已过期,请重新获取');
            }
            if ($_SESSION['code'] != $formParams['code']) {
                throw new \Exception('验证码错误,请重新获取');
            }
        }
    }

    public function verifPhoneName($name,$phone)
    {
        $isPhoneReal = verif_phone_realname($name, $phone);
        if (empty($isPhoneReal)) {
            throw new \Exception('验证失败');
        }
        if ($isPhoneReal['data']['verifyCode'] != '0') {
            throw new \Exception('手机号实名姓名与填写不匹配');
        }
    }

    public function verifIdCard($name,$idcard){
        $isIDCardResult = verifIDCard(json_encode(['uname' => $name, 'idCard' => $idcard]));
        $isIDCardResult = json_decode($isIDCardResult, true);
        if (empty($isIDCardResult)) {
            throw new \Exception('身份验证失败');
        }
        if ($isIDCardResult['code'] != "01") {
            throw new \Exception( "身份验证:" . $isIDCardResult['msg']);
        }
    }

    private function _getUidByUnionid($unionid)
    {
        return pdo_get('member',['unionid'=>$unionid],['id']);
    }

    private function _save($data,$uid)
    {
        global $_GPC;
        global $_W;
        $birthday = strlen($data['idcard']) == 15 ? ('19' . substr($data['idcard'], 6, 6)) : substr($data['idcard'], 6, 8);

        pdo_insert('member_family', [
            'uid' => $uid,
            'name' => $data['name'],
            'bind' => $data['bind'],
            'sex' => '保密',
            'birth_date' => $birthday,
            'idcard' => $data['idcard'],
            'phone' => $data['phone'],
            'address' => $data['address']
        ]);

        $m_data = array();
        $m_data['mobile'] = $data['phone'];
        $m_data['realname'] = $data['name'];
        $m_data['id_card'] = $data['idcard'];
        $m_data['unionid'] = $_W['fans']['unionid'];
        $m_data['pwd']     = md5($data['pwd']);
        pdo_update('sz_yi_member', $m_data, array('openid' => $_W['openid'], 'uniacid' => $_W['uniacid']));
    }
}


if ($_W['isajax']){
    try{
        $result = (new familyBind())->main();
    }catch (\Exception $exception){
        show_json(1,$exception->getMessage());
    }
    show_json(0,$result);
}else{
    global $_W;
    //var_dump($_W['fans']['unionid']);
    new familyBind();
    
    $title = '完善信息';
    $mobile = $_GPC['mobile'] ? $_GPC['mobile'] : '';
    $pwd = $_GPC['pwd'] ? $_GPC['pwd'] : '';
    
    include $this->template('member/easy_deliver_family');
}
