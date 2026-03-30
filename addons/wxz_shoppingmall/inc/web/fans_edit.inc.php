<?php

global $_W, $_GPC;

$id = $_GPC['id'];
$fans_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_fans') . " WHERE uid={$id}";
$fans_info = pdo_fetch($fans_info_sql);
if (!$fans_info) {
    message('会员不存在', $this->createWebUrl('fans_list'));
}

require_once WXZ_SHOPPINGMALL . '/source/Fans.class.php';
$_W['module_setting'] = $this->module['config'];
$levels = Fans::getLevels();

list($fans_info['plate_number0'], $fans_info['plate_number1'], $fans_info['plate_number2']) = explode('-', $fans_info['plate_number']);

load()->web('tpl');
if (checksubmit()) {
    //字段验证, 并获得正确的数据$dat
    $data = array(
        'username' => $_GPC['username'],
        'mobile' => $_GPC['mobile'],
        'account' => $_GPC['account'],
    );

    if (pdo_update('wxz_shoppingmall_fans', $data, array('uid' => $id))) {
        message('更新成功', $this->createWebUrl('fans_list'));
    } else {
        message('更新失败', $this->createWebUrl('fans_list', array('id' => $id)));
    }
}

//车牌号
$plateNumbers = array(
    '皖', '苏', '浙', '京', '津', '沪', '渝', '冀', '豫', '云', '辽', '黑', '湘', '皖', '鲁', '湘', '新', '赣', '桂', '甘', '晋', '蒙', '陕',
    '吉', '闽', '贵', '粤', '青', '藏', '琼'
);
//字母
$alphabets = range('A', 'Z');

include $this->template('web/fans_edit');
?>
