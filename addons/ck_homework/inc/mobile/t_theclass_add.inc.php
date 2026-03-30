<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
load()->func('tpl');

require "common.php";
require "public.php";
require "t_common.php";

$urltk = $this->createMobileUrl('t_theclass_add');

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
		'dateline' => time(),
		'kxtimes' => strtotime($_GPC['kxtimes']),
		'number' => intval($_GPC['number']),
		'price' => round($_GPC['price'], 2),	
		'imgurl' => trim($_GPC['imgurl']),
        'code'=>trim($_GPC['code']),
        'school_id'=>$_GPC['schools']
	);
	
	$result = pdo_insert('onljob_theclass', $data);
	if (!empty($result)) {
		$id = pdo_insertid();
		$numberlent = strlen($id);
		$canumb = 9 - $numberlent;
		$numberid = random($canumb, true).$_GPC['code'];
		pdo_update('onljob_theclass', array('numberid' => $numberid), array('id' => $id,'uid' => $_W['member']['uid'],'weid' => $_W['uniacid']));
		
		message_app('当前班级创建成功，您可以添加学生了！', array($this->createMobileUrl('t_theclass_add'), $this->createMobileUrl('t_theclass')), 'success', array('继续添加', '查看班级'));
	}else{
		message_app('保存失败', array($urltk), 'error');
	}
	
}


include template_app('t_theclass_add');