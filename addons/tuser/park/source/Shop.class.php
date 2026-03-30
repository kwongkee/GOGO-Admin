<?php

/**
 * 店铺相关类
 */
class Shop {

    /**
     * 店铺分类
     * @var type 
     */
    public static $categorys = array(
        1 => '服装服饰',
        2 => '运动品牌',
        3 => '百货超市',
        4 => '休闲娱乐',
        5 => '珠宝钟表钢笔',
        6 => '餐饮美食',
        7 => '箱包靴鞋',
        8 => '生活服务',
        9 => '数码家居',
        10 => '其他商户',
    );

    public static function getCategorys() {
        return self::$categorys;
    }

    /**
     * 活动状态
     * @var type 
     */
    public static $activityDateStatus = array(
        1 => '未开始',
        2 => '已结束',
    );

    /**
     * 根据时间获取店铺活动状态
     * @param type $expiry_date_start
     * @param type $expiry_date_end
     */
    public static function getDateStatus($expiry_date_start, $expiry_date_end) {
        $now = time();
        if ($expiry_date_start != '0000-00-00') {
            $expiry_date_start_time = strtotime($expiry_date_start);
            if ($now < $expiry_date_start_time) {
                return 1;
            }
        }
        if ($expiry_date_end != '0000-00-00') {
            $expiry_date_end_time = strtotime($expiry_date_end);
            if ($now > $expiry_date_end_time) {
                return 2;
            }
        }
    }

    /**
     * 获取商铺列表
     * @create 2017-02-20
     * @return array
     */
    public static function getShopList() {
        global $_W;
        $sql = "SELECT id,name FROM " . tablename('wxz_shoppingmall_shop') . " WHERE uniacid={$_W['uniacid']} AND isdel=0";
        return pdo_fetchall($sql);
    }

}
