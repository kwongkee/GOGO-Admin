<?php

//会员认证
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;
load()->func('communication');

class SaveAuthInfo
{

    public static function verifField($data)
    {
        if ($data['name'] == "") {
            return '姓名不能为空';
        }
        if (!preg_match("/^1[3456789]\d{9}$/", $data['phone'])) {
            return '手机格式不正确';
        }
      
        if ($data['province']==""){
            return '请选择省市区';
        }
        if ($data['city']==""){
            return '请选择省市区';
        }
        if ($data['area']==""){
            return '请选择省市区';
        }
        if ($data['address'] == "") {
            return '地址不能为空';
        }
        // preg_match('/(.*?(省|自治区|北京市|天津市))+(.*?(市|自治州|地区|区划|县))+(.*?(区|县|镇|乡|街道))/', $data['address'], $matches);
        if (($_SESSION['codetime'] + (60 * 5)) < time()) {
            return '验证码已过期,请重新获取';
        }
        if ($_SESSION['code'] != $data['code']) {
            return '验证码错误,请重新获取';
        }
        // $isPhoneReal = verif_phone_realname($data['name'], $data['phone']);
        // if (empty($isPhoneReal)) {
        //     return '验证失败';
        // }
        // if ($isPhoneReal['data']['verifyCode'] != '0') {
        //     return '手机号实名姓名与填写不匹配';
        // }

        $isIDCardResult = verifIDCard(json_encode(['uname' => $data['name'], 'idCard' => $data['idcard']]));
        $isIDCardResult = json_decode($isIDCardResult, true);
        if (empty($isIDCardResult)) {
            return '身份验证失败';
        }
        if ($isIDCardResult['code'] != "01") {
            return "身份验证:" . $isIDCardResult['msg'];
        }
        return null;
    }

    /**
     * 是否已认证过
     * @param $unionId
     * @return mixed
     */
    public static function isUser($unionId)
    {
        return pdo_get('member',['unionid'=>$unionId]);
    }

    /**
     * @param $data 表单数据
     * @return string
     */
    public static function save($data, $unionid,$openid)
    {
        pdo_begin();
        try {
            $uid = self::insertMemberTable($data, $unionid);
            self::updateAppMemberTable($data, $unionid,$openid);
            if ($uid){
                self::addFamily($data, $uid);
            }
            pdo_commit();
        } catch (\Exception $exception) {
            pdo_rollback();
            return $exception->getMessage();
        }
        return '认证成功';
    }


    /**
     * 插入总用户表
     * @param $data
     * @param $unionid
     * @return mixed
     */
    private static function insertMemberTable($data, $unionid)
    {
        if (self::isUser($unionid)){
            return false;
        }
        pdo_insert('member', [
            'unionid' => $unionid,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'create_time' => time(),
            'pid' => 0
        ]);
        return pdo_insertid();
    }

    /**
     * 更新应用用户表
     * @param $data
     */
    private static function updateAppMemberTable($data, $unionid,$openid)
    {
        $pwd = pdo_get('sz_yi_member',['openid'=>$openid],['pwd']);
        $_data = [
            'unionid' => $unionid,
            'level' => 1,
            'realname' => $data['name'],
            'mobile' => $data['phone'],
            'id_card' => $data['idcard']
        ];
        if (empty($pwd)||empty($pwd['pwd'])){
            $_data['pwd']=md5(trim($data['phone']));
        }
        pdo_update('sz_yi_member',$_data,['openid'=>$openid]);
    }

    /**
     * 添加家庭组
     * @param $data
     * @param $uid
     */
    private static function addFamily($data, $uid)
    {

        $birthday = strlen($data['idcard']) == 15 ? ('19' . substr($data['idcard'], 6, 6)) : substr($data['idcard'], 6, 8);
        pdo_insert('member_family', [
            'uid' => $uid,
            'name' => $data['name'],
            'bind' => '自己',
            'sex' => '保密',
            'birth_date' => $birthday,
            'idcard' => $data['idcard'],
            'phone' => $data['phone'],
            'address' => $data['province'].$data['city'].$data['area'].$data['address']
        ]);
    }
}


if ($_W['ispost']) {
    $err = SaveAuthInfo::verifField($_GPC);
    if (!is_null($err)) {
        show_json(0, $err);
    }
    $account_api = WeAccount::create();
    $fans_info = $account_api->fansQueryInfo($_W['openid']);
    // $user = pdo_get('member',['unionid'=>$fans_info['unionid']]);
    // if (!empty(SaveAuthInfo::isUser($fans_info['unionid']))){
    //     show_json(0, '无需重复认证');
    // }

    show_json(1, SaveAuthInfo::save($_GPC, $fans_info['unionid'],$_W['openid']));
} else {
    $title = '会员认证';
    include $this->template('member/member_auth');
}
