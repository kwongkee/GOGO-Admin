<?php

namespace app\api\logic;
use think\Db;
use think\Cache;
use think\Model;
class WechatLoigc extends Model
{
    public function sendTplNotice($data)
    {
        $data = json_decode(json_encode($data),true);

        $token    = $this->getTokenFromUrl($data['uniacid']);
        $wxResult = $this->send($data,$token);
        if($wxResult['errcode'] != 0){
            $this->send($data,$this->getTokenFromUrl($data['uniacid']));
        }
        return $wxResult;
    }


    /**
     * @发送消息模板待做
     * 车牌号查询违规订单绑定用户并发送wx消息模板
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function searOrderUserByCard($data){
        if (empty($data)){
            return false;
        }
        if (empty($data['carNo'])) {
            return false;
        }

        $oid = null;
        //select * from ims_parking_order as a left join ims_foll_order as b on a.ordersn=b.ordersn where CarNo='粤X1118' and (b.user_id ='0' or b.user_id='')
        $orderRes =Db::query("select a.ordersn from ims_parking_order as a left join ims_foll_order as b on a.ordersn=b.ordersn where a.CarNo='".$data['carNo']."' and (b.user_id='' or b.user_id='0')");

        if (empty($orderRes)){
            return false;
        }
        foreach ($orderRes as $val){
            $oid .= $val['ordersn'].',';
        }
        $oid = trim($oid,',');
        Db::startTrans();
        try{
            Db::name('foll_order')->where('ordersn','in',$oid)->update(['user_id'=>$data['user_id']]);
            Db::commit();
        }catch (\Exception $exception){
            Db::rollback();
            throw new \Exception($exception->getMessage());
        }
    }


    public  function getAccessToken ( $uniacid)
    {
        $uniacid = $uniacid?$uniacid:14;
        $key = 'accesstoken:'.$uniacid;
        if (Cache::has($key)){
            return Cache::get($key);
        }
        return $this->getTokenFromUrl($uniacid);
    }

    /**
     * 从数据库获取微信token
     * @param $uniacid
     * @return mixed
     */
    public function getTokenFromUrl($uniacid)
    {

        return RequestAccessToken($uniacid);
//        $key = 'accesstoken:'.$uniacid;
//        Cache::rm($key);
//        $account = Db::name("account_wechats")->where('uniacid', $uniacid)->find();
//        $ASSESS_TOKEN = file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $account['key'] . '&secret=' . $account['secret']);
//        $ASSESS_TOKEN = json_decode($ASSESS_TOKEN, true);
//        Cache::set($key,$ASSESS_TOKEN['access_token'],$ASSESS_TOKEN['expires_in']-200);
//        return $ASSESS_TOKEN['access_token'];
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function send ( $data ,$token)
    {
        $hosts    = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $token;
        $d        = unserialize($data['template']);
        $wxResult = httpRequest($hosts, json_encode($d));
        @file_put_contents('../runtime/log/wx/wxtemplate.log','返回数据：'.$wxResult."\n",FILE_APPEND);
        $wxResult = json_decode($wxResult, true);
        return $wxResult;
    }
}
