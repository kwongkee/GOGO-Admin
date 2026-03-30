<?php
namespace app\index\controller;
use think\Request;
use think\Db;
//defind("KEY","test");

class Index
{
    public function index(Request $request)
    {
//        $couponId   = Db::table("ims_foll_receivecoupon")
//            ->where([
//                "user_id"   =>'G99198商务号20180416964604213',
//                'status'    =>1,
//                'application'=>'parking'
//            ])->find();
//        $couponRes  = Db::table("ims_foll_coupon")
//            ->where(["id"=>$couponId['card_id'], 'status'=>0])
//            ->where('s_time',"<=",time())
//            ->where('e_time','>=',time())
//            ->find();
        Db::table("ims_parking_authorize")->where("openid",'oR-IB0h7w3lGAxFTeeVAR3LraBZI')->setInc('blance',4);
    }


    public function refultMoney(){
        $url = 'http://shop.gogo198.cn/payment/wechat/refund.php';
        $postdata = array(
            'token' => 'refund',
            'ordersn' => 'G99198商务号201804161445499763',
            'refundMoney' => 1,
        );
        $res = $this->httpRequest($url, $postdata);
        dump($res);
    }

    protected  function httpRequest($url,$data)
    {
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        $output=curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}
