<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}
class Month_card_EweiShopV2Page extends mobilePage
{

    public function main()
    {
        global $_W;
        global $_GPC;
        $title = '月卡购买';
        $monthPlanList = pdo_getAll('parking_month_type',['uniacid'=>$_W['uniacid'],'is_check'=>1,'month_status'=>1]);
        if(!$monthPlanList){
            message('暂无');
        }

        $verUser = pdo_get('parking_verified',['openid'=>$_W['openid']]);

        if((empty($verUser)&&empty($verUser['idcard']))||empty($verUser['license'])){
            header("Location:".mobileUrl('parking/alert_message/monthMsg'));
            exit();
        }

        $userInfo = pdo_get('parking_authorize',['openid'=>$_W['openid']]);
        $cardApplyTime = pdo_get('parking_month_type',['id'=>$_GPC['type_id']]);
        if($userInfo['auth_status']==0){
            header("Location:".mobileUrl('parking/alert_message/monthMsg'));
            exit();
        }

        include $this->template('parking/month_card_index');
    }


    public function really_buy()
    {
        global $_W;
        global $_GPC;
        $url="http://shop.gogo198.cn/payment/monthCard/Card.php";
        $park_num = '000000-999999';
        $new_time = time();
        $money = 0.00;
        $card = pdo_get("parking_card_type",array('id'=>$_GPC['type_id']));
        $card_detail = pdo_fetchall("select * from ".tablename('parking_card_issue')." where type_id=".$_GPC['type_id']." order by id limit 1")[0];
        if(!empty($card_detail['park_start'])&&!empty($card_detail['park_end'])){
            $park_num = $card_detail['park_start'].'-'.$card_detail['park_end'];
        }
        $order_id = 'G99198' . '101570223660' . date('Ymd', time()) . mt_rand(1111, 9999);
        if (!empty($card_detail['c_discount'])){
            $money += (floor(($card_detail['lowest_end']-$card_detail['lowest_start'])/86400/30)/2)*$card['card_money'];
            $money += (floor(($card_detail['lowest_end']-$card_detail['lowest_start'])/86400/30)/2)*$card['card_money']*($card_detail['c_discount']/10);
        }else{
            $money = floor(($card_detail['lowest_end']-$card_detail['lowest_start'])/86400/30)*$card['card_money'];
        }
        try{
            $result = pdo_insert('parking_monthcard',[
                'uniacid'   => $_W['uniacid'],
                'user_id'   => $_W['openid'],
                'parspaces' => $park_num,
                'period'    => $card_detail['period_start'].'-'.$card_detail['period_end'],
                'money'     => $money,
                'endtime'   => $card['use_time'],
                'createtime'  =>time(),
                'orderid'   =>$order_id,
                'issue_id'  => $card_detail['id'],
                'month_num' => floor(($card_detail['lowest_end']-$card_detail['lowest_start'])/86400/30)
            ]);
        }catch (Exception $exception){
            message('支付失败');
        }
        $mid = pdo_insertid();
        $post_data=['token'=>'wechat','id'=>$mid];
        $payRes=$this->ihttp_post($url,$post_data);
        $payRes=json_decode($payRes,true);
        if($payRes['msg']=='success'){
            header("location:".$payRes['payurl']);
        }else{
            pdo_delete('parking_monthcard',array('id'=>$mid));
            message('支付失败:'.$payRes['msg']);
        }

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

    /*
    public function month_details()
    {
        global $_W;
        global $_GPC;
        $new_time =  time();
        $total_money = 0.00;
        if(isset($_GPC['type_id'])){
            $card_detail = pdo_fetchall("select * from ".tablename('parking_card_issue')." where type_id=".$_GPC['type_id']." order by id limit 1")[0];
            $card = pdo_get('parking_card_type',array('id'=>$_GPC['type_id']));
            if(!empty($card_detail)&&!empty($card)){
                if (!empty($card_detail['c_discount'])){
                    $total_money += (floor(($card_detail['lowest_end']-$card_detail['lowest_start'])/86400/30)/2)*$card['card_money'];
                    $total_money += (floor(($card_detail['lowest_end']-$card_detail['lowest_start'])/86400/30)/2)*$card['card_money']*($card_detail['c_discount']/10);
                }else{
                    $total_money = floor(($card_detail['lowest_end']-$card_detail['lowest_start'])/86400/30)*$card['card_money'];
                }
                $total_money = floatval($total_money);
                include $this->template('parking/month_card_detail');
            }
        }
    }
    */
    public function month_details()
    {
        global $_W;
        global $_GPC;
        $new_time =  time();
        $total_money = 0.00;
        if(isset($_GPC['type_id'])){
            $card_detail = pdo_get("parking_month_type",['id'=>$_GPC['type_id']]);
            $card_detail['region'] = json_decode($card_detail['region'],true);
            if($card_detail['fit_type'] == 0){
                $card_detail['addr'] = null;
                $n =null;
                foreach ($card_detail['region'] as $val)
                {
                    $card_detail['addr'] .= pdo_get('parking_deploy_district',['id'=>$val['province'].$n],['name'])['name'];
                    $card_detail['addr'].= pdo_get('parking_deploy_district',['id'=>$val['city'].$n],['name'])['name'];
                    $card_detail['addr'].= pdo_get('parking_deploy_district',['id'=>$val['area'].$n],['name'])['name'];
                    $card_detail['addr'].='-';
                    $n = 2;
                }
                $card_detail['addr'] = trim($card_detail['addr'],'-');
            }
            include $this->template('parking/month_card_detail');
        }
    }
}