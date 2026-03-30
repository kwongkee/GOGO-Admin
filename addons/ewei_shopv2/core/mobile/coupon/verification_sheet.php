<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

/**
 * 核销
 * Class Verification_sheet_EweiShopV2Page
 */
class Verification_sheet_EweiShopV2Page extends mobilePage {


    public function __construct() {
        parent::__construct();
        if (isset($_COOKIE['busin_admin']) && !empty($_COOKIE['busin_admin'])) {
            $userRes = pdo_get('foll_seller_member',['busin_num'=>$_COOKIE['busin_admin']]);
            $_SESSION['busin_admin'] = $userRes;
        } elseif (!isset($_SESSION['busin_admin']) && empty($_SESSION['busin_admin'])) {
            $this->message('请登录！',mobileUrl('coupon/login'), 'error');
        }
    }

    /*
     * 商家主动核销卡卷
     * 页面待新增点击事件唤起扫码事件
     */
    public function main() {
        global $_W;
        global $_GPC;
        include $this->template('coupon/verifcation_index');
    }

    /**
     * 输入消费总金额
     */
    public function consumerMoney() {
        global $_W;
        global $_GPC;
        if ($_GPC['rece_num'] == '') {
            $this->message('错误！', '', 'error');
        }

        $num = $_GPC['rece_num'];
        include $this->template('coupon/consumer_money');
    }


    /**
     * 卡卷核销
     */
    public function verifica_coupon() {
        global $_W;
        global $_GPC;
        $num = $_GPC['rece_num'];
        $totalMoney = $_GPC['totalmoney'];
        $nowTime = time();
        if ($num == '' && $totalMoney == '') {
            $this->message('参数错误！', '', 'error');
        }

        $receRes = pdo_get('foll_cooupon_receive', ['rece_num' => $num]);
        $couponRes = pdo_get('foll_coupon', ['id' => $receRes['coupon_id']]);
        //是否该商家
        if ($couponRes['busin_id'] != $_SESSION['busin_admin']['id']) {
            $this->message('请在该卷商家消费！', '', 'error');
        }

        if ($nowTime < $couponRes['coupon_stime']) {
            $this->message('该卷还没生效！', '', 'success');
        }

        if ($nowTime > $couponRes['coupon_etime']) {
            pdo_update('foll_cooupon_receive', ['status' => 2], ['id' => $receRes['id']]);
            $this->message('已过期', '', 'success');
        }

        //是否有效期内
        if ($receRes['status'] != 0) {
            $this->message('无效卡卷！', '', 'error');
        }
        pdo_update('foll_cooupon_receive', ['status' => 1], ['id' => $receRes['id']]);
        pdo_insert('foll_coupon_use', [
            'coupon_id' => $receRes['coupon_id'],
            'user_id' => $receRes['user_id'],
            'original_amout' => $totalMoney,
            'discount_amout' => $totalMoney-($totalMoney-$couponRes['coupon_money']),
            'create_time' => time(),
            'busin_id' => $receRes['busin_id'],
            'use_busin_name' => '',
            'application' => 'offline'
        ]);
        $this->message('完成！', '', 'success');
    }
}