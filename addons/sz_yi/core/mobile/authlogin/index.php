<?php


// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;


echo '<pre>';
print_r($_GPC);



$html = <<<EOX
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>测试微信登陆</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>
    <p id="Content"></p>
    <span id="login_container"></span>
    <script src="http://res.wx.qq.com/connect/zh_CN/htmledition/js/wxLogin.js"></script>
    <script>
        var obj = new WxLogin({
            id: "login_container",
            appid: "wxed782be999f86e0e",
            scope: "snsapi_login",
            redirect_uri: encodeURIComponent("http://" + window.location.host + "/login.php"),
            state: Math.ceil(Math.random()*1000),
            style: "black",
            href: ""});
    </script>
</body>
</html>
EOX;



?>