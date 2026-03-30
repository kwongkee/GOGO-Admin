<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->func('tpl');
$op = $_GPC['op'];

$urlt = $this->createWebUrl('jz_list');

$newtimes = time();

/* VIP等级列表 */
$level_list = pdo_fetchall("SELECT * FROM " . tablename('onljob_vip_level') . " WHERE weid = '{$_W['uniacid']}' ORDER BY sort ASC, id DESC", array(), 'id');

//修改
if(checksubmit('edit_submit')){
	
	if (empty ($_GPC['name'])) {
		message('姓名不能为空！', '', 'error');
	}
	/*
	if (empty ($_GPC['sex'])) {
		message('请选着性别！', '', 'error');
	}
	if (empty ($_GPC['birthday'])) {
		message('请选择出生日期！', '', 'error');
	}
	if (empty ($_GPC['address'])) {
		message('现居住地址不能为空！', '', 'error');
	}
	*/

	//判断手机号是否不注册
	$phone = trim($_GPC['phone']);
	if (!empty($phone)) {
		$mc_members = pdo_get('onljob_user', array('weid' => $_W['uniacid'],'phone' => $phone));
		if(!empty($mc_members) && $mc_members['uid'] != $_GPC['uid']){
			message('抱歉！你输入手机号已经被注册，请从新更换一个！', '', 'error');
		}
	}
	
	$data = array(
		'headimg' => $_GPC['headimg'],
		'name' => trim($_GPC['name']),
		'sex' => intval($_GPC['sex']),
		'birthday' => trim($_GPC['birthday']),
		'phone' => trim($_GPC['phone']),
		'email' => trim($_GPC['email']),
		'address' => trim($_GPC['address']),
		'qq' => trim($_GPC['qq'])
	);
	
	//会员权限
	if (!empty($_GPC['groupsid'])) {
		if($_GPC['groupsid'] != $_GPC['oldgroupsid']){
			//获取VIP时间
			$srdb_vip = pdo_get('onljob_vip_level', array('id' => $_GPC['groupsid'],'weid' => $_W['uniacid']));
			$data['endtime'] = $srdb_vip['level_validity'] * 86400 + $newtimes;
			$data['groupsid'] = intval($_GPC['groupsid']);
		}else{
			if($_GPC['endtime'] < $newtimes){
				$data['endtime'] = '';
				$data['groupsid'] = '0';
			}
		}
	}
	
	$password = trim($_GPC['password']);
	if(!empty($password)) {
		if(strlen($password) < 6) {
			message('密码不能少于6位', '', 'error');
		}
		
		$salt = random(8);
		$password = md5($password . $salt . $_W['config']['setting']['authkey']);
		
		$data['password'] = $password;
		$data['salt'] = $salt;
	}
	
	//修改
	if(!empty($_GPC['id'])){
		pdo_update('onljob_user', $data, array('id' => $_GPC['id'],'weid' => $_W['uniacid']));
		message('修改成功！', $urlt, 'success');
	}else{
		message('修改失败！', $urlt, 'error');
	}
	
}

//读取
if($op == 'edit' || $op == 'hzuser'){
	
	$id = intval($_GPC['id']);
	$srdb = pdo_get('onljob_user', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message('不存在或是已经被删除！', $urlt, 'error');
	}
	
	//获取孩子列表---------------------
	if(!empty($srdb['hzuid'])){
		$list_hzuser = pdo_fetchall("SELECT headimg,uid,name FROM ".tablename('onljob_user')." WHERE weid = '{$_W['uniacid']}' and id in ('{$srdb['hzuid']}') ORDER BY id DESC");
	}

}

//删除---------------
if($op == 'delete'){
	$id = intval($_GPC['id']);
	if($id){
		pdo_delete('onljob_user', array('id' => $id,'weid' => $_W['uniacid']));
		message('删除成功', $urlt, 'success');
	}else{
		message('删除失败', $urlt, 'error');
	}
}

//批量删除
if (checksubmit('deletesubmit')) {
	if($_GPC['ids'] && is_array($_GPC['ids'])) {
		
		$ids = $_GPC['ids'];
		for($i=0; $i < count($ids); $i++){
			pdo_delete('onljob_user', array('id' => $ids[$i],'weid' => $_W['uniacid']));
		}
		message('批量删除成功', $urlt, 'success');
		
	}else{
		message('批量删除失败', $urlt, 'error');
	}
}
//批量处理到期VIP
if (checksubmit('vipsubmit')) {
	if($_GPC['ids'] && is_array($_GPC['ids'])) {
		
		$ids = $_GPC['ids'];
		$endtime = $_GPC['endtime'];
		for($i=0; $i < count($ids); $i++){
			if($endtime[$ids[$i]] < $newtimes){
				pdo_update('onljob_user', array('endtime' => '','groupsid' => 0), array('id' => $ids[$i],'weid' => $_W['uniacid']));
			}
		}
		message('批量处理成功', $urlt, 'success');
		
	}else{
		message('批量处理失败', $urlt, 'error');
	}
}

//列表-------------------------
//排序
$ordersc = array($_GPC['ordersc']=>' selected');
if($_GPC['ordersc']){
	$ordersql = "ORDER BY id ".$_GPC['ordersc'];
}else{
	$ordersql = "ORDER BY id DESC";
}

$pindex = max(1, intval($_GPC['page']));
$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
if(!in_array($psize, array(20,50,100))) $psize = 20;
$perpages = array($psize => ' selected');

$where = '';

if (!empty($_GPC['name'])) {
	$where .= " AND name LIKE '%{$_GPC['name']}%'";
}

if (!empty($_GPC['phone'])) {
	$where .= " AND phone LIKE '%{$_GPC['phone']}%'";
}

if($_GPC['statep']==1){
	$where .= " AND uid != '0'";
}elseif($_GPC['statep']==2){
	$where .= " AND uid = '0'";
}

$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_user')."  WHERE weid = '{$_W['uniacid']}' AND type = '2' {$where}");
if($total){
	$list = pdo_fetchall("SELECT * FROM ".tablename('onljob_user')." WHERE weid = '{$_W['uniacid']}' AND type = '2' {$where} {$ordersql} LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	if (!empty($list)) {
		$fasshow = '';
		foreach ($list as $k => $value) {
			$fans = pdo_get('mc_mapping_fans', array('uid' => $value['uid'],'uniacid' => $_W['uniacid']));
			if(!empty($fans)){
				$fasshow[$value['id']] = 1;
			}
		}
	}

}
$pager = pagination($total, $pindex, $psize);

include $this->template('jz_list');
?>