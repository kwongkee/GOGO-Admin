<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
    }
class Monthcardpay_EweiShopV2Page extends mobilePage{
    public function main()
    {
        global $_W;
        global $_GPC;
        if(!isset($_GPC['mid'])||empty($_GPC['mid'])){
           message('错误操作');
        }

        $card_detail = pdo_get('parking_month_type',['id'=>$_GPC['mid'],'month_status'=>1]);
        if(empty($card_detail)){
            message('月卡已失效');
        }
        
        $time = time();
        $isWin = pdo_get('parking_apply',['user_id'=>$_W['openid'],'is_win'=>1,'is_done'=>'N','is_out'=>0]);
        $id=pdo_get("parking_month_pay",array("user_id"=>$_W['openid'],"status"=>'A','pay_status'=>0),array('id'));
        if (empty($isWin)){
            message('暂无');
        }
        
        if ($isWin['is_up']=='Y'){
            message('所属候补人员,不能支付');
        }
      
        if($time<$card_detail['lottery_pay']||$time > $card_detail['lottery_pay2']){
            if(is_null($isWin['delay_paytime'])){
                
                message('不在支付期');
            }
            $payTime = explode('/',$isWin['delay_paytime']);
            if ($time<strtotime(trim($payTime[0]))&&$time>strtotime(trim($payTime[1]))){
                message('不在支付期');
            }
        }
//        $hasUp =  pdo_get('parking_apply',['user_id'=>$_W['openid'],'m_id'=>$_GPC['mid'],'is_up'=>'Y']);
        include $this->template("parking/monthcardpay");

    }

    public function updateAlternateStu($openid)
    {
        
    }
    
    public function WechatPay()
    {
        global $_W;
        global $_GPC;
        $id = null;
        $url="http://shop.gogo198.cn/payment/monthCard/Card.php";
        $id=pdo_get("parking_month_pay",array("user_id"=>$_W['openid'],"status"=>'A','pay_status'=>0),array('id','ordersn'));
        $orderid = pdo_get('foll_order',['ordersn'=>$id['ordersn']],['id']);
//        $applyId = pdo_get('parking_apply',['user_id'=>$_W['openid'],'is_done'=>'N']);
        $millisecond = round(explode(" ", microtime())[0]*1000);
        $oid = 'G99198'  . date('Ymd', time()) .str_pad($millisecond,3,'0',STR_PAD_RIGHT).mt_rand(111, 999).mt_rand(111,999);
        if(!empty($id)){
            pdo_update("parking_month_pay",array("ordersn"=>$oid),['id'=>$id['id']]);
            pdo_update("foll_order",['ordersn'=>$oid],['id'=>$orderid['id']]);
        }else{
            $month = pdo_get("parking_month_type",['id'=>$_GPC['mid']]);
            $hasUp = pdo_get('parking_apply',['user_id'=>$_W['openid'],'m_id'=>$_GPC['mid'],'is_up'=>'Y']);
            $money = empty($hasUp)?$month['month_money']:$hasUp['up_money'];
            $data = [
                'user_id' => $_W['openid'],
                'm_id' =>$_GPC['mid'],
                'create_at' => time(),
                'pay_money'=> $money,
                'ordersn' => $oid,
                'uniacid' => $_W['uniacid']
            ];
            $order = [
                'ordersn' => $oid,
                'user_id' => $_W['openid'],
                'business_id'=>$_W['uniacid'],
                'uniacid' => $_W['uniacid'],
                'application'=>'monthCard',
                'goods_name'=>'月卡',
                'goods_price'=>$money,
                'pay_type' =>'wechat',
                'pay_account' =>$money,
                'body' =>'月卡购买',
                'create_time'=>time(),
                'total' => $money
            ];
            pdo_begin();
            try{
                pdo_insert('parking_month_pay',$data);
                pdo_insert('foll_order',$order);
//                pdo_update('parking_apply',['is_done'=>'Y'],['id'=>$applyId['id']]);
//                pdo_delete('parking_lotter_win',['user_id'=>$_W['openid']]);
                pdo_commit();
            }catch (Exception $exception){
                pdo_rollback();
                message('支付失败');
            }
        }

        $post_data=['token'=>'wechat','id'=>$oid];

        $payRes=$this->ihttp_post($url,$post_data);

        $payRes=json_decode($payRes,true);
        if($payRes['msg']=='success') {
            // 请求支付参数
            $pay_info = json_decode($payRes['pay_info'],true);
            // 支付唤起地址
            $urlTo = 'http://shop.gogo198.cn/addons/ewei_shopv2/payment/wechat/pay.php';
            // 跳转唤起支付
            $rs = $this->formPost($urlTo,$pay_info);

            echo $rs;
            //header("location:".$payRes['payurl']);
        }else{
           message($payRes['msg']);
           //message('维护中,请耐心等待...');
        }
    }


    // 模拟表单请求
    public function formPost($uri, $data)
    {
        $str = '<form action="' . $uri . '" method="post" name="formPost">';
        foreach ($data as $k => $v) {
            $str .= '<input type="hidden" name="' . $k . '" value="' . $v . '">';
        }
        $str .= '</form>';
        return $str . "<script>document.forms['formPost'].submit();</script>";
    }


    public function ihttp_post($url,$post_data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
}