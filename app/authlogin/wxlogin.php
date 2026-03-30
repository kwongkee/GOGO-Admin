<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2020/3/5
 * Time: 16:01
 */


class wxlogin
{
    public $appid;
    public $appsecret;

    //构造函数，获取Access Token
    public function __construct($appid = NULL, $appsecret = NULL)
    {
        $this->appid = $appid;
        $this->appsecret = $appsecret;
        //扫码登录不需要该Access Token, 语义理解需要
        //1. 本地写入
        $res = @file_get_contents('./access_token.json');
        $result = json_decode($res, true);
        $this->expires_time = $result["expires_time"];
        $this->access_token = $result["access_token"];

        if (time() > ($this->expires_time + 3600)) {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->appid . "&secret=" . $this->appsecret;
            $res = $this->http_request($url);
            $result = json_decode($res, true);
            $this->access_token = $result["access_token"];
            $this->expires_time = time();
            file_put_contents('access_token.json', '{"access_token": "' . $this->access_token . '", "expires_time": ' . $this->expires_time . '}');
        }
    }


    //生成扫码登录的URL
    public function qrconnect($redirect_url, $scope, $state = NULL)
    {
        $url = "https://open.weixin.qq.com/connect/qrconnect?appid=" . $this->appid . "&redirect_uri=" . urlencode($redirect_url) . "&response_type=code&scope=" . $scope . "&state=" . $state . "#wechat_redirect";
        return $url;
    }


    //生成OAuth2的Access Token
    public function oauth2_access_token($code)
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $this->appid . "&secret=" . $this->appsecret . "&code=" . $code . "&grant_type=authorization_code";
        $res = $this->http_request($url);
        return json_decode($res, true);
    }


    //获取用户基本信息（OAuth2 授权的 Access Token 获取 未关注用户，Access Token为临时获取）
    public function oauth2_get_user_info($access_token, $openid)
    {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $access_token . "&openid=" . $openid . "&lang=zh_CN";
        $res = $this->http_request($url);
        return json_decode($res, true);
    }


    public function http_request($url){
        return file_get_contents($url);
    }
}
