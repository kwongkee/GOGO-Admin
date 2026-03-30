<?php
if (!defined('IN_IA'))
{
    exit('Access Denied');
}


class OrderDetails_EweiShopV2Page extends mobilePage
{
    public function main()
    {
        global $_W;
        global $_GPC;
        $authType = ['wx'=>'微信','wg'=>'信用卡','sd'=>'农商'];
        $data = array();
        $authInfo = null;
        if(isset($_GPC['orderid'])&&!is_null($_GPC['orderid'])){
            $data['order_data'] = pdo_get('parking_order',array('ordersn'=>$_GPC['orderid']));
            $pid = pdo_get('parking_space',array('park_code'=>$data['order_data']['number']),array('pid'));
            $data['addr_data'] = pdo_get('parking_position',array('id'=>$pid['pid']));
            $data['auth_data'] = pdo_get('parking_authorize',array('openid'=>$_W['openid']));
            if($data['auth_data']['auth_status']==1){
                $t = unserialize($data['auth_data']['auth_type']);
                foreach ($t as $k =>$v){
                    $data['auth_data']['auth_type'] = $authType[$k];
                }
                unset($t);
            }else{
                $data['auth_data']['auth_type'] = '无';
            }
        }
        include $this->template('parking/order_details');
    }
}