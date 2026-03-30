<?php
if(!defined("IN_IA")){
    exit("Access Denied");

}

class Order_EweiShopV2Page extends WebPage{
    public function main()
    {
        global $_W;
        global $_GPC;
        $status=['0'=>'未支付','1'=>'已支付','2'=>'支付失败'];
        $pstatus=['alipay'=>'支付宝','wechat'=>'微信','Parks'=>'信用卡'];
        $data=$this->OrderList();
        include $this->template("parking/order_list");
    }
    public function OrderList(){
        global $_W;
        global $_GPC;
        $total=pdo_fetchall("SELECT count(id)as num FROM".tablename('parking_order')."WHERE uniacid=".$_W['uniacid'])['0'];
        $pageindex=$_GPC['page']<1?1:intval($_GPC['page']);
        $pager=pagination($total['num'],$pageindex,10);
        $p=($pageindex-1)*10;
        if(!empty($_GPC['start'])&&!empty($_GPC['end'])){
            $start=strtotime($_GPC['start']);
            $end=strtotime($_GPC['end']);
            $res=pdo_fetchall("SELECT t1.*,t2.mobile FROM ".tablename('parking_order')." t1
              LEFT JOIN ".tablename('parking_authorize')." t2
                ON t2.openid=t1.openid
            WHERE t1.openid IN (SELECT openid FROM ".tablename('parking_order')." WHERE create_time>{$start} AND create_time<{$end})
            LIMIT {$p},10");
        }else if(!empty($_GPC['contrller'])){
            $sid=intval($_GPC['contrller'])==3?0:intval($_GPC['contrller']);
            $res=pdo_fetchall("SELECT t1.*,t2.mobile FROM ".tablename('parking_order')." t1 LEFT JOIN ".tablename('parking_authorize')." t2 ON t2.openid=t1.openid WHERE t1.uniacid={$_W['uniacid']} and t1.pay_status={$sid} LIMIT {$p},10");
        }else if(!empty($_GPC['paytype'])){
            $res=pdo_fetchall("SELECT t1.*,t2.mobile FROM ".tablename('parking_order')." t1 LEFT JOIN ".tablename('parking_authorize')." t2 ON t2.openid=t1.openid WHERE t1.uniacid={$_W['uniacid']} and t1.pay_type='".$_GPC['paytype']."' LIMIT {$p},10");
        }else if(!empty($_GPC['ordernum'])){
            $oderid=trim($_GPC['ordernum']);
            $res=pdo_fetchall("SELECT t1.*,t2.mobile FROM ".tablename('parking_order')." t1 LEFT JOIN ".tablename('parking_authorize')." t2 ON t2.openid=t1.openid WHERE t1.uniacid={$_W['uniacid']} and t1.ordersn='".$oderid."' LIMIT {$p},10");
        }else{
            $res=pdo_fetchall("SELECT t1.*,t2.mobile FROM ".tablename('parking_order')." t1 LEFT JOIN ".tablename('parking_authorize')." t2 ON t2.openid=t1.openid WHERE t1.uniacid={$_W['uniacid']} LIMIT {$p},10");
        }
        return ['page'=>$pager,'data'=>$res,'total'=>$total];
    }
    public function delete(){
        global $_W;
        global $_GPC;
        $result = pdo_delete('parking_order', array('id' => $_GPC['did'],'uniacid'=>$_W['uniacid']));
        if (!empty($result)) {
           echo '1';
        }
    }
    public function alldel(){
        global $_W;
        global $_GPC;
        $id=$_GPC['aId'];
        $did='';
        foreach ($id as $key=>$value){
            $did.=$value.',';
        }
        $did=trim($did,',');
        $result=pdo_query("DELETE FROM ".tablename('parking_order')." WHERE id in({$did}) and uniacid={$_W['uniacid']}");
        if(!empty($result)){
            echo '1';
        }
    }
}