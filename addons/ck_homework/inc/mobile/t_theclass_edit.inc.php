<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
load()->func('tpl');

require "common.php";
require "public.php";
require "t_common.php";

$id = intval($_GPC['id']);

$urltk = $this->createMobileUrl('t_theclass_edit');
$url_list = $this->createMobileUrl('t_theclass');

$result = pdo_get('onljob_theclass', array('id'=>$id,'weid'=>$_W['uniacid']));
if(empty($result)){
    message_app('找不到对应的信息。', '', 'error');
}
//科目
$category = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'xk' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//年级
$category_nj = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'nj' ORDER BY listorder ASC, cid DESC", array(), 'cid');
//学校
$school = pdo_fetchall("SELECT * from ".tablename('onljob_school')." where weid={$_W['uniacid']} order by id desc");
//提交处理
if(checksubmit('save_submit')){

    if (empty ($_GPC['titlename'])) {
        message_app('班级名称不能为空！', '', 'error');
    }

    $data = array(
        'weid' => $_W['uniacid'],
        'uid' => $_W['member']['uid'],
        'titlename' => trim($_GPC['titlename']),
        'njid' => intval($_GPC['njid']),
        'xkid' => intval($_GPC['xkid']),
        'kxtimes' => strtotime($_GPC['kxtimes']),
        'number' => intval($_GPC['number']),
        'price' => intval($_GPC['price']),
        'imgurl' => trim($_GPC['imgurl']),
        'school_id'=>$_GPC['schools']
    );

    $result = pdo_update('onljob_theclass', $data, array('id' => $id));
    if (!empty($result)) {
        message_app('修改成功！', array($url_list), 'success');
    }else{
        message_app('保存失败', array($urltk), 'error');
    }

}
include template_app('t_theclass_edit');