<?php

defined('IN_IA') or exit('Access Denied');
define('WXZ_SHOPPINGMALL', IA_ROOT . '/addons/wxz_shoppingmall');

class Wxz_shoppingmallModuleSite extends WeModuleSite {

    protected function auth() {
        global $_W, $_GPC;
        session_start();
        if (getip() == '127.0.0.1') {
            $_SESSION['__:proxy:openid'] = 'btyLPTGwIduBvEXdiGSnpUK4';
			//o5YC3t6MD1CjD2U_3dJQkMUjDQBA1
        }
        $openid = $_SESSION['__:proxy:openid'];
        require_once WXZ_SHOPPINGMALL . '/source/Fans.class.php';

        $f = new Fans();
        if (!empty($openid)) {
            $exists = $f->getOne($openid, true);
            if (!empty($exists)) {
                return $exists;
            }
        }
		
        //查询appid和appsecret
        $api = $this->module['config']['api'];
        $callback = $_W['siteroot'] . "app/index.php?i={$_GPC['i']}&c=entry&do=auth&m={$_GPC['m']}";
        $callback = urlencode($callback);
        $state = $_SERVER['REQUEST_URI'];
        $stateKey = substr(md5($state), 0, 8);
        $_SESSION['__:proxy:forward'] = $state;
        //$forward = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$_W['account']['jssdkconfig']['appId']}&redirect_uri={$callback}&response_type=code&scope=snsapi_userinfo&state={$stateKey}#wechat_redirect";
		$forward = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$_W['account']['key']}&redirect_uri={$callback}&response_type=code&scope=snsapi_userinfo&state={$stateKey}#wechat_redirect";
        header('Location: ' . $forward);
        exit;
    }

    public function doMobileFrom() {
        global $_W, $_GPC;
        $yobyurl = "http://" . $_SERVER['HTTP_HOST'] . "/addons/wxz_shoppingmall_fans";
        $attachurl = $_W['attachurl'];
        $settings = $this->module['config'];
        //这个操作被定义用来呈现 功能封面
        include $this->template('index');
    }

}

?>