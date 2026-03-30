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
                    $preUrl = ($_COOKIE['preUrl'] ? $_COOKIE['preUrl'] : $this->createMobileUrl('member'));
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

                    if (is_app()) {
                        setcookie($cookieid, base64_encode($info['openid']), time() + (3600 * 24 * 7));
                    }
                    else {
                        setcookie($cookieid, base64_encode($info['openid']));
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
                        $member_info = pdo_fetch('select realname,nickname,mobile from ' . tablename('sz_yi_member') . ' where   uniacid=:uniacid and openid=:openid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $openid));
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
