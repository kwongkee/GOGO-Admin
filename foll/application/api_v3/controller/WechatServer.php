<?php

namespace app\api_v3\controller;

use think\Db;
use think\Request;


/**
 * 小程序接口
 * Class WechatServer
 * @package app\api_v3\controller
 */
class WechatServer
{


    public function __construct()
    {

        $wechat_config = Db::name('smallwechat_config')->where(array('id'=>1))->find();
        $this->appid = $wechat_config['appid'];
        $this->appsecret = $wechat_config['appsecret'];
        $this->Token = 'cklein';
        $this->EncodingAESKey = 'cgJFhv4hwktdDVE62ZqpDIpvhLdV2I4D80v4Prro75Z';
    }


    //绑定推送服务
    public function index()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce     = $_GET["nonce"];
        $str       = $_GET["echostr"];

        $tmpArr = array($this->Token, $timestamp, $nonce); sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            echo $str;
        }else{
            echo false;
        }
    }










}
