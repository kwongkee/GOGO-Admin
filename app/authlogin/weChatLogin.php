<?php


require_once('wxlogin.php');



weChatLogin();

function weChatLogin() {
    //$weixin = getModel("wxlogin");
    $weixin = new wxlogin('wx19ba77624e083e08','c1a56a5c4247dd44c320c9719c5ceb90');
    if (!isset($_GET["code"])){

//        print_r($_SERVER);
        //$redirect_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        $redirect_url = 'https://www.baidu.com/';
//        die;
        $jumpurl = $weixin->qrconnect($redirect_url, "snsapi_login", "123");
        Header("Location: $jumpurl");
    }else{
        //获取token
        $oauth2_info = $weixin->oauth2_access_token($_GET["code"]);

        //获取用户信息
        $userinfo = $weixin->oauth2_get_user_info($oauth2_info['access_token'], $oauth2_info['openid']);
        //参数样式$userinfo用户信息结构
        //array(10) { ["openid"]=> string(28) "oJDSV0lWOjfXE7VXk6C4_QuiUtQY" ["nickname"]=> string(6) "俊哥" ["sex"]=> int(1) ["language"]=> string(5) "zh_CN" ["city"]=> string(6) "成都" ["province"]=> string(6) "四川" ["country"]=> string(6) "中国" ["headimgurl"]=> string(129) "http://thirdwx.qlogo.cn/mmopen/vi_32/JuV6pCO123H8xHXGe7goSW9tFy0PKLL1zSH3uVuJ9QZ9omZeZ8TjfWGdtjtOBLAs82VqriajFecBys0pGjicVBow/132" ["privilege"]=> array(0) { } ["unionid"]=> string(28) "oAnnw0ix6eEYev5w7AUR29VoS-ow" }

        echo '<pre>';
        print_r($userinfo);

        die;
        //登录成功后跳转
        header("Location:************");
    }
}


?>