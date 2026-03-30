<?php

/**
 * 积分日志相关
 */
class CreditLog {

    /**
     * 日志类型列表
     * @var type 
     */
    public static $eventTypes = array(
        1 => '兑换奖品',
        2 => '兑换优惠券',
        3 => '转赠积分',
        4 => '后台导入',
        5 => '拍摄小票',
        6 => '完善资料送积分',
        7 => '后台积分操作',
        8 => '微信扫码停车抵用',
        9 => '停车抵用',
    );

    /**
     * 用户完善资料注册积分
     * @param type $userInfo
     */
    public function reg2AddAward($userInfo) {
        global $_W;
        $uid = $userInfo['uid'];
        //查询是否已经领取过
        $sql = "select id from " . tablename('wxz_shoppingmall_credit_log') . " where uniacid={$_W['uniacid']} AND fans_id=$uid AND event_type=6 limit 1";
        $exists = pdo_fetchcolumn($sql);
        if ($exists) {
            return;
        }

        if (in_array(1, $_W['module_setting']['credit_reg2_credit'])) {
            //送积分
            $this->reg2AddCredit($uid);
        }


        //是否符合送优惠券时间
        $reg_time = $userInfo['reg_time'];
        $canAward = true;
        if ($_W['module_setting']['reg2_credit_coupon_start_date']) {
            $reg2_credit_coupon_start_time = strtotime($_W['module_setting']['reg2_credit_coupon_start_date']);
            if ($reg2_credit_coupon_start_time > $reg_time) {
                $canAward = false;
            }
        }
        if ($_W['module_setting']['reg2_credit_coupon_end_date']) {
            $reg2_credit_coupon_end_time = strtotime($_W['module_setting']['reg2_credit_coupon_end_date']);
            if ($reg2_credit_coupon_end_time < $reg_time) {
                $canAward = false;
            }
        }

        if (in_array(2, $_W['module_setting']['credit_reg2_credit']) && $canAward) {
            //送优惠券
            $this->reg2AddCoupon($uid);
        }
    }

    /**
     * 送优惠券
     * @param type $uid
     */
    public function reg2AddCoupon($uid) {
        global $_W;
        $award_id = $_W['module_setting']['credit_reg2_credit_coupon_id'];
        $award_info_sql = "SELECT id,name,num,credit FROM " . tablename('wxz_shoppingmall_coupon') . " WHERE id={$award_id} AND uniacid={$_W['uniacid']}";
        $award_info = pdo_fetch($award_info_sql);
        if (!$award_info || $award_info['num'] <= 1) {
            return;
        }

        $insert_exchange_data = array(
            'uniacid' => $_W['uniacid'],
            'fans_id' => $uid,
            'award_coupon_id' => $award_id,
            'event_desc' => "完善资料赠送优惠券{$award_info['name']}",
            'type' => 1,
            'operate' => 1,
            'event_type' => 2,
            'status' => 1,
            'num' => $award_info['credit'],
            'create_at' => time(),
        );

        $ret = pdo_insert('wxz_shoppingmall_credit_log', $insert_exchange_data);

        //优惠券减库存
        if ($ret) {
            //更新库存
            $update_award = "UPDATE  " . tablename('wxz_shoppingmall_coupon') . " set num=num-1,cashed=cashed+1 where id={$award_info['id']}"; //消耗积分
            $ret = pdo_query($update_award);
        }
        return $ret;
    }

    /**
     * 完善资料奖励积分
     * @param type $uid
     */
    public function reg2AddCredit($uid) {
        global $_W;
        $credit = $_W['module_setting']['credit_reg2_credit_num'];
        //赠送积分
        $update_fans = "UPDATE  " . tablename('wxz_shoppingmall_fans') . " set credit=credit+{$credit},left_credit=left_credit+{$credit} where uid='{$uid}'"; //添加积分
        $ret = pdo_query($update_fans);

        //插入日志
        if ($ret) {
            $credit_log_data = array(
                'uniacid' => $_W['uniacid'],
                'fans_id' => $uid,
                'type' => 1,
                'operate' => 1,
                'event_type' => 6,
                'event_desc' => '完善资料赠送积分',
                'num' => $credit,
                'status' => 2,
                'send_time' => time(),
                'create_at' => time(),
            );
            $ret = pdo_insert('wxz_shoppingmall_credit_log', $credit_log_data);
        } else {
            return false;
        }

        return $ret;
    }

    /**
     * 添加积分日志
     * @param type $data
     * @return type
     */
    public function addCreditLog($data) {
        if (!$data) {
            return;
        }
        
        pdo_insert('wxz_shoppingmall_credit_log', $data);
        return pdo_insertid();
    }

}
