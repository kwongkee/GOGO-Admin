<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;
@session_start();
$operation = (empty($_GPC['op']) ? 'display' : $_GPC['op']);
$shopset = m('common')->getSysset('shop');
$this->yzShopSet = m('common')->getSysset('shop');

if(isset($_GPC['type']) && ($_GPC['type'] == 'wxlogin')) {
    new Cklein();
    // 2020-03-20 新增，微信扫码登陆
    // 解析数据
    $baseStr     = base64_decode($_GPC['info']);

    // 解析json
    $userData = json_decode($baseStr,true);
    // 查询用户是否存在

    $preUrl = $this->createMobileUrl('order');
    //$openid = $userData['openid'];
    // 使用用户唯一ID
    // $unionid = $userData['unionid'];

    // 查询用户信息
    // $info = pdo_fetch('select id,mobile,openid from ' . tablename('sz_yi_member') . ' where  unionid=:unionid and uniacid=:uniacid limit 1', array(':unionid'=>$unionid,':uniacid' => $_W['uniacid']));
    $info = pdo_fetch('select id,mobile,openid from ' . tablename('sz_yi_member') . ' where  unionid=:unionid and uniacid=:uniacid limit 1', array(':unionid'=>$userData['unionid'],':uniacid' => $_W['uniacid']));
    
    //$info = pdo_fetch('select id,mobile,openid from ' . tablename('sz_yi_member') . ' where  openid=:openid and uniacid=:uniacid limit 1', array(':openid'=>$openid,':uniacid' => $_W['uniacid']));
    if(!$info) {
        // 不存在用，进行注册，否则登陆

        // 模拟手机号码
        // $mobile = time() . mt_rand(1,9);
        $insData = [];
        $insData = array('uniacid' => $_W['uniacid'], 'uid' => 0, 'createtime' => time(), 'status' => 0, 'regtype' => 2);
        $insData['unionid']     = $userData['unionid'];
        $insData['openid']      = $userData['openid'];
        $insData['nickname']    = $userData['nickname'];
        $insData['avatar']      = $userData['headimgurl'];
        $insData['mobile']      = '';
        $insData['pwd']         = md5('123456');
        $insData['isblack']     = 0;
        $insData['realname']    = '';
        $insData['id_card']     = '';
        $insData['level']       = 1;
        pdo_insert('sz_yi_member', $insData);
        $m_id = pdo_insertid();

        pdo_insert('sz_yi_member_address', array(
            'uniacid'   => $_W['uniacid'],
            'openid'    => $userData['openid'],
            'realname'  => $userData['nickname'],
            'mobile'    => '',
            'province'  => $userData['province'],
            'city'      => $userData['city'],
            'address'   => '',
            'isdefault' => 1
        ));

        $lifeTime = 24 * 3600 * 3;
        session_set_cookie_params($lifeTime);
        $cookieid = '__cookie_sz_yi_userid_' . $_W['uniacid'];
         // 存入session 2019-06-25
        $_SESSION['level_id'] = 1;
        // 返回数据
        $infos = [
            'cookiedid'=>[
                $cookieid,base64_encode($userData['openid'])
            ],
            'member_mobile'=> '',
            'member_id'=> $m_id,
            'indexUrl' => 'https://shop.gogo198.cn/app/index.php?i='.$_W['uniacid'].'&c=entry&p=index&do=shop&m=sz_yi'
        ];
        // 返回数据
        show_json(1,$infos);
        exit();
    }

    $lifeTime = 24 * 3600 * 3;
    session_set_cookie_params($lifeTime);
    $cookieid = '__cookie_sz_yi_userid_' . $_W['uniacid'];
    // 返回数据
    $infos = [
        'cookiedid'=>[
            $cookieid,base64_encode($info['openid'])
        ],
        'member_mobile'=>$info['mobile'],
        'member_id'=> $info['id'],
        'indexUrl' => 'https://shop.gogo198.cn/app/index.php?i='.$_W['uniacid'].'&c=entry&p=index&do=shop&m=sz_yi'
    ];
    // 返回数据
    show_json(1,$infos);
    exit();

} else {

    if ($operation == 'display') {

        if (m('user')->islogin() != false) {
            header('location: ' . $this->createMobileUrl('member'));
        }

        if ($_W['isajax']) {

            if ($_W['ispost']) {
                $mc = $_GPC['memberdata'];

                $mobile = (!empty($mc['mobile']) ? $mc['mobile'] : show_json(0, '手机号不能为空！'));
                $password = (!empty($mc['password']) ? $mc['password'] : show_json(0, '密码不能为空！'));
                $info = pdo_fetch('select * from ' . tablename('sz_yi_member') . ' where  mobile=:mobile and uniacid=:uniacid and pwd=:pwd limit 1', array(':uniacid' => $_W['uniacid'], ':mobile' => $mobile, ':pwd' => md5($password)));

                if (isMobile()) {
                    $preUrl = ($_COOKIE['preUrl'] ? $_COOKIE['preUrl'] : $this->createMobileUrl('order'));
                }
                else {
                    $preUrl = ($_COOKIE['preUrl'] ? $_COOKIE['preUrl'] : $this->createMobileUrl('order'));
                }

                if ($info) {
                    if (is_app()) {
                        $lifeTime = 24 * 3600 * 3 * 100;
                    }
                    else {
                        $lifeTime = 24 * 3600 * 3;
                    }
                    session_set_cookie_params($lifeTime);
                    $cookieid = '__cookie_sz_yi_userid_' . $_W['uniacid'];
                    //20230620
                    if(empty($info['openid'])){
                        setcookie($cookieid, base64_encode($info['mobile']));
                    }else{
                        if (is_app()) {
                            setcookie($cookieid, base64_encode($info['openid']), time() + (3600 * 24 * 7));
                        }
                        else {
                            setcookie($cookieid, base64_encode($info['openid']));
                        }
                    }

                    // pdo_insert('sz_yi_member_login_log',[
                    // 	'uid'=>$info['id'],
                    // 	'login_ip'=>getRealIp(),
                    // 	'login_time'=>time(),
                    // 	'browser' =>getBrowser(),
                    // ]);
                    setcookie('member_mobile', $info['mobile']);
                    //lotodo 20170116
                    setcookie('member_id', $info['id']);

                    // 存入session 2019-06-25
                    $_SESSION['level_id'] = $info['level'];

                    if (!isMobile()) {
                        $openid = base64_decode($_COOKIE[$cookieid]);
                        //20230620
                        if(empty($openid)){
                            $member_info = pdo_fetch('select realname,nickname,mobile from ' . tablename('sz_yi_member') . ' where   uniacid=:uniacid and mobile=:mobile limit 1', array(':uniacid' => $_W['uniacid'], ':mobile' => $mobile));
                        }else{
                            $member_info = pdo_fetch('select realname,nickname,mobile from ' . tablename('sz_yi_member') . ' where   uniacid=:uniacid and openid=:openid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $openid));
                        }

                        $member_name = (!empty($member_info['realname']) ? $member_info['realname'] : $member_info['nickname']);
                        $member_name = (!empty($member_name) ? $member_name : '未知');
                        setcookie('member_name', base64_encode($member_name));
                    }

                    if (is_app()) {
                        show_json(1, array('preurl' => $preUrl, 'open_id' => $info['openid']));
                    }
                    else {
                        show_json(1, array('preurl' => $preUrl));
                    }
                }
                else {
                    show_json(0, '用户名或密码错误！');
                }
            }
        }
    }

}

function getBrowser(){
    $agent=$_SERVER["HTTP_USER_AGENT"];
    if(strpos($agent,'MSIE')!==false || strpos($agent,'rv:11.0')) //ie11判断
        return "ie";
    else if(strpos($agent,'Firefox')!==false)
        return "firefox";
    else if(strpos($agent,'Chrome')!==false)
        return "chrome";
    else if(strpos($agent,'Opera')!==false)
        return 'opera';
    else if((strpos($agent,'Chrome')==false)&&strpos($agent,'Safari')!==false)
        return 'safari';
    else
        return 'unknown';
}


include $this->template('member/login');

?>
