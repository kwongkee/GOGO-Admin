<?php

/**
 * 页面相关
 */
class Page {

    public static $table = 'wxz_shoppingmall_page';

    /**
     * 店铺分类
     * @var type 
     */
    public static $types = array(
        1 => '商场地址',
        2 => '咨询电话',
        3 => '商场首页底部导航',
        4 => '营业时间',
        5 => '会员卡图片(440x270)',
        6 => '会员卡图片(未注册440x270)',
        7 => '注册页背景图(1080x830)',
        8 => '完善资料页背景图(1080x830)',
        9 => '商户首页导航',
    );

    /**
     * svg图标
     * @var type 
     */
    public static $index2PageNav = array(
        'svg_icon' => array(
            'icon_member_1' => '会员',
            'icon_register_1' => '注册',
            'icon_qiandao_1' => '签到',
            'icon_balance_1' => '余额',
            'icon_event_1' => '活动',
            'icon_gift_1' => '礼物',
            'icon_diyongquan_1' => '抵用券',
            'icon_tuanGou_1' => '团购',
            'icon_service_1' => '服务',
            'icon_wifi_1' => 'Wifi',
            'icon_tool_1' => '工具',
            'icon_map_1' => '地图',
            'icon_parking_1' => '停车',
            'icon_floor_1' => '楼层',
            'icon_shop_1' => '店铺',
            'icon_shoppingCart_2' => '购物车',
            'icon_office_1' => '办公楼',
            'icon_hotel_1' => '酒店',
            'icon_toilet_1' => '厕所',
            'icon_food_1' => '美食',
            'icon_child_1' => '儿童',
            'icon_group_1' => '用户组',
            'icon_kuaidi_1' => '快递',
            'icon_didiDaChe_1' => '叫车',
            'icon_star_3' => '五角星',
            'icon_heka_1' => '贺卡',
        ),
        'color' => array(
            'link1' => 'background:#009e42;',
            'link2' => 'background:#e60021;',
            'link3' => 'background:#00418e;',
            'link4' => 'background:#21c063;',
            'link5' => 'background:#ea156f;',
            'link6' => 'background:#186acb;',
            'link7' => 'background:#2bca6d;',
            'link8' => 'background:#e3364f;',
            'link9' => 'background:#0f5eae;',
            'link10' => 'background:#3bd487;',
            'link11' => 'background:#206cc8;',
            'link12' => 'background:#e30e2a;',
            'link13' => 'background:#b5384a;',
            'link14' => 'background:#3484e1;',
            'link15' => 'background:#2ebe67;',
            'link16' => 'background:#0e58a8;',
            'link17' => 'background:#186acb;',
            'link18' => 'background:#e60021;',
            'link19' => 'background:#3bd487;',
            'link20' => 'background:#f55f01;',
        ),
    );

    /**
     * 获取页面配置类型
     * @return type
     */
    public static function getPageTypes() {
        return self::$types;
    }

    /**
     * 获取页面配置
     * @param type $type 数组或数字
     */
    public static function getPage($type, $field = '*') {
        global $_W;
        $result = array();

        if (!$type) {
            return FALSE;
        }

        $condition = "uniacid={$_W['uniacid']} AND isdel=0";
        if (is_numeric($type)) {
            $condition .= " AND type='{$type}'";
        } else if (is_array($type)) {
            $condition .= " AND type in (" . implode(',', $type) . ")";
        }

        $sql = "SELECT {$field} FROM " . tablename('wxz_shoppingmall_page') . " WHERE {$condition}";

        $list = pdo_fetchall($sql);
        foreach ($list as $row) {
            $result[$row['type']] = $row;
        }
        return $result;
    }

    /**
     * 初始化页面配置
     */
    public static function initPages() {
        global $_W;
        $pageTypes = self::getPageTypes();
        foreach ($pageTypes as $type => $pageType) {
            $page = self::getPage($type, 'id');
            if (!$page) {
                $insertData = array(
                    'uniacid' => $_W['uniacid'],
                    'type' => $type,
                    'create_at' => time(),
                );
                pdo_insert(self::$table, $insertData);
            }
        }
    }

}
