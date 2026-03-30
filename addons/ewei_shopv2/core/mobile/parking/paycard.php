<?php

if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Paycard_EweiShopV2Page extends mobilePage
{
    public function main(){
        global $_W;
        global $_GPC;
        $cardData=pdo_get("parking_monthcard",array('user_id'=>$_W['openid'],'pay_status'=>0));
        if(!empty($cardData)){
            $orderid='G99198' . '101570223660' . date('Ymd',time()) . mt_rand(1111,9999);
            pdo_update("parking_monthcard",array('orderid'=>$orderid),array('id'=>$cardData['id']));//更新id
            //请求支付
            $old=[
                'payMoney'=>$cardData['money'],
                'ordersn'=>$orderid,
                'payType'=>'wechat',
                'body'=>'购买月卡',
                'openid'=>$cardData['openid'],
                'create_time'=>time()
            ];
            pdo_insert("pay_old",$old);
            $param=[
                'fee'=>$cardData['money'],
                'tid'=>$orderid,
                'title'=>'月卡',
                'openid'=>$cardData['openid'],
                'notifyUrl'=>'http://shop.gogo198.cn/addons/ewei_shopv2/payment/oauth/paycardnotify.php'
            ];

            //查找当前配置
            $config = pdo_get('pay_config', array('uniacid' =>$_W['uniacid']), array('config'));
            //反序列化
            $key = unserialize($config['config']);
            $paramConfig=[
                'mchid'=>$key['tg']['mchid'],
                'key'=>$key['tg']['key']
            ];
            $result=m("common")->pay_wechat($param,$paramConfig);
            if($result['status']!=100){
                exit('失败');
            }
            header("location:" .$result['pay_url']);
            exit();
        }
    }
}