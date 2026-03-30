<?php

namespace app\api\controller;
use think\Controller;
use think\Db;
use think\Log;
use think\Request;
use think\Cache;

class WxAccessToken extends Controller
{
    public function updateAccessToken(Request $request)
    {
        $time = time();
        $allInfo = $this->getAllWechat();
        $result = $this->getAccessToken($allInfo[0]['key'],$allInfo[0]['secret']);
        foreach ($allInfo as $value){
            $result =null;
            $result = $this->getAccessToken($value['key'],$value['secret']);
            try{
                $result['token'] = $result['access_token'];
                $result['expire'] = $time+$result['expires_in']-200;
                $keys = 'accesstoken:'.$value['uniacid'];
                Cache::set($keys,$result['access_token'],7000);
                unset($result['access_token'],$result['expires_in']);
//                Db::name('core_cache')->insert(['key'=>$keys,'value'=>serialize($result)]);

                Db::name('core_cache')->where('key','accesstoken:'.$value['uniacid'])->update(['value'=>serialize($result)]);
            }catch (\Exception $e){
                Log::write($e->getMessage());
            }
        }
        return json(['code'=>'1']);
    }

    protected function getAllWechat()
    {
        return Db::name("account_wechats")->field(['uniacid','key','secret'])->select();
    }

    protected function getAccessToken($k,$sec)
    {
        $ASSESS_TOKEN = array();
        $ASSESS_TOKEN = file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $k. '&secret=' . $sec);
        $ASSESS_TOKEN = json_decode($ASSESS_TOKEN, true);
        return $ASSESS_TOKEN;
    }
}