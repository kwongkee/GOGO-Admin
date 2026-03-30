<?php

/**
 * 用户注册
 */
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';
$modulePublic = '../addons/' . $_GPC['m'] . '/static/';
if (!$user['mobile']) {
    header('Location: ' . $this->createMobileUrl('reg'));
    exit;
}

//车牌号
$plateNumbers = array(
    '皖', '苏', '浙', '京', '津', '沪', '渝', '冀', '豫', '云', '辽', '黑', '湘','川', '皖', '鲁', '湘', '新', '赣', '桂', '甘', '晋', '蒙', '陕',
    '吉', '闽', '贵', '粤', '青', '藏', '琼'
);
//字母
$alphabets = range('A', 'Z');
$userPlateNumbers = explode('-', $user['plate_number']);
require_once WXZ_SHOPPINGMALL . '/source/Page.class.php';
$pageContents = Page::getPage(array(8));
include $this->template('reg2');
?>
