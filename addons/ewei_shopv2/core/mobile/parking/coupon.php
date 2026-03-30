<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

class Coupon_EweiShopV2Page extends mobilePage {
    /**
     * 活动页面
     */
    public function main() {
        global $_W;
        global $_GPC;
        $nowTime = time();
        $useType = ['满X元使用', '每X元使用', '无条件使用'];
        $appType  = $_GPC['app']==''?'parking':$_GPC['app'];
        $couponRes = pdo_fetchall("select * from " . tablename('foll_coupon') . " where coupon_status=1");
        foreach ($couponRes as $key => $val) {
            $couponRes[$key]['receiveNum'] = pdo_fetch("select count(id) as num from " . tablename('foll_cooupon_receive') . " where coupon_id=" . $val['id'])['num'];//已领取
//            $couponRes[$key]['overNum'] = $val['total'] - $couponRes[$key]['receiveNum'];
            $couponRes[$key]['isReceive'] = pdo_get('foll_cooupon_receive', ['coupon_id' => $val['id'], 'user_id' => $_W['openid']], ['id']);

            if (!empty($val['coupon_buisin'])) {
                $buisin = explode(',', $val['coupon_buisin']);
                if (!in_array($_W['uniacid'], $buisin)) {
                    unset($couponRes[$key]);
                    continue;
                }
            }

            if (!empty($val['coupon_app'])) {
                $app = explode(',', $val['coupon_app']);
                if (!in_array($appType, $app)) {
                    unset($couponRes[$key]);
                    continue;
                }
            }
        }
        if (empty($couponRes)){
            $this->message('暂时未有优惠券活动','','success');
        }
        include $this->template('parking/coupon/activity_index');
    }

    /**
     * 1、判断是否已领取过，
     * 2、判断是否领取数满了，
     * 3、判断是否已过领取时间
     * 4、保存商城卡卷
     * 领取操作
     */
    public function coupon_receive() {
        global $_W;
        global $_GPC;
        $id = $_GPC['id'];
        $nowTime = time();
        $couponRes = pdo_get('foll_coupon', ['coupon_num' => $id]);
        $isRec = pdo_fetchall("select count(id) as num from ".tablename('foll_cooupon_receive')." where coupon_id=".$couponRes['id']." and user_id='".$_W['openid']."'")[0];
        if ($couponRes['max_limit']!=0){
            if ($isRec['num']>=$couponRes['max_limit']) {
                show_json(0, '你已领取过');
            }
        }

        if ($couponRes['stock'] <= 0) {
            show_json(0, '已领完');
        }


        if (!empty($couponRes['coupon_get_stime'])&&$nowTime<$couponRes['coupon_get_stime']) {
            show_json(0, '活动时间还没开始');
        }

        if (!empty($couponRes['coupon_get_etime'])&&$nowTime>$couponRes['coupon_get_etime']) {
            show_json(0, '活动时间已结束');
        }

        $shopEwCouponId = pdo_get('ewei_shop_coupon',['coupon_num'=>$couponRes['coupon_num']]);
        $shopSzCouponId = pdo_get('sz_yi_coupon',['coupon_num'=>$couponRes['coupon_num']]);
        $ewParm = [
            'uniacid'=>$_W['uniacid'],
            'openid' => $_W['openid'],
            'couponid'=>$shopEwCouponId['id'],
            'gettype'=>1,
            'gettime'=>time(),
        ];
        $szParm = [
            'uniacid'=>$_W['uniacid'],
            'openid' => $_W['openid'],
            'couponid'=>$shopSzCouponId['id'],
            'gettype'=>1,
            'gettime'=>time(),
        ];

        $parm = [
            'coupon_id' => $couponRes['id'],
            'user_id' => $_W['openid'],
            'create_time' => time(),
            'busin_id' => $couponRes['busin_id'],
            'rece_num' => date('YmdHi',time()).mt_rand(100,999)
        ];
        pdo_begin();
        try {
            pdo_insert('foll_cooupon_receive', $parm);
            pdo_update('foll_coupon', ['stock' => $couponRes['stock']-1], ['id' => $couponRes['id']]);
            if (!empty($couponRes['coupon_app'])){
                $app = explode(',',$couponRes['coupon_app']);
                if (in_array('shop',$app)){
                    $this->insertEwShopCoupon($ewParm);
                    $this->insertSzShopCoupon($szParm);
                }
            }else{
                $this->insertEwShopCoupon($ewParm);
                $this->insertSzShopCoupon($szParm);
            }
            pdo_commit();
        } catch (Exception $e) {
            pdo_rollback();
            show_json(-1, '领取失败');
        }
        show_json(0, '领取成功');
    }


    /**
     * 插入商城卡卷领取表ims_sz_yi_coupon_data
     * @param $parm
     */
    public function insertSzShopCoupon($parm){
        pdo_insert('sz_yi_coupon_data',$parm);
    }

    /**
     * 插入商城卡卷领取表ims_ewei_shop_coupon_data
     * @param $parm
     */
    public function insertEwShopCoupon($parm){
        pdo_insert('ewei_shop_coupon_data',$parm);
    }

    /**
     *
     * 我的卷
     */
    public function my_coupon() {
        global $_W;
        global $_GPC;
        $status = ['未使用','已使用','已过期'];
        $myCoupon = pdo_fetchall("select 
                              a.status,a.rece_num,b.* from ".tablename('foll_cooupon_receive')." as a left join " .tablename('foll_coupon').
                                " as b on a.coupon_id=b.id where a.user_id='".$_W['openid']."' order by id desc limit 10"
        );
        include $this->template('parking/coupon/my_coupon');
    }


    /**
     * 优惠券详情
     */
    public function my_coupon_detail() {
        global $_W;
        global $_GPC;
        $id = $_GPC['id'];
        $status = ['未使用','已使用','已过期'];
        if ($id==''){
            $this->message('错误！','','error');
        }
        $res = pdo_get('foll_coupon',['id'=>$id]);
        $myCoupon = pdo_get('foll_cooupon_receive',['coupon_id'=>$res['id'],'user_id'=>$_W['openid']],['rece_num','status']);
        $res['rece_num'] = $myCoupon['rece_num'];
        $res['status']   = $myCoupon['status'];
        include $this->template('parking/coupon/my_coupon_detail');
    }


}