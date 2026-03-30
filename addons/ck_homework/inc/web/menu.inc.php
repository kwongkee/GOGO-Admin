<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

load()->func('tpl');
$op = $_GPC['op'];

$urlt = $this->createWebUrl('menu');

//修改
if(checksubmit('add_submit') || checksubmit('edit_submit')){
	
	if (empty($_GPC['titlename'])) {
		message('请输入名称！');
	}
	
	$data = array(
		'lx_type' => $_GPC['lx_type'],
		'titlename' => $_GPC['titlename'],
		'icon_img' => $_GPC['icon_img'],
		'showt' => intval($_GPC['showt']),
		'listorder' => $_GPC['listorder'],
		'turl' => $_GPC['turl'],
		'message' => $_GPC['message']
	);
	
	//添加
	if(checksubmit('add_submit')){
		$data['weid'] = $_W['uniacid'];
		$result = pdo_insert('onljob_menu', $data);
		if (!empty($result)) {
			$idd = pdo_insertid();
			message('保存成功!', $urlt, 'success');
		}else{
			message('添加失败', $urlt, 'error');
		}
		 
	}
	
	//修改
	if(checksubmit('edit_submit')){
		
		pdo_update('onljob_menu', $data, array('id' => $_GPC['id'],'weid' => $_W['uniacid']));
		message('修改成功', $urlt, 'success');
		
	}
	
}

//读取
if($op == 'edit'){
	
	$id = intval($_GPC['id']);
	$srdb = pdo_get('onljob_menu', array('id' => $id));
	if (empty($srdb)) {
		message('不存在或是已经被删除！');
	}
	
}

//删除
if($op == 'delete'){
	$id = intval($_GPC['id']);
	if($id){
		pdo_delete('onljob_menu', array('id' => $id,'weid' => $_W['uniacid']));
		message('删除成功', $urlt, 'success');
	}else{
		message('删除失败', $urlt, 'error');
	}
}

//批量删除
if (checksubmit('listsubmit')) {
	if($_GPC['ids'] && is_array($_GPC['ids']) && $_GPC['optype']) {
		switch ($_GPC['optype']) {
			case '1':
				$ids = $_GPC['ids'];
				for($i=0;$i < count($ids); $i++){
					pdo_delete('onljob_menu', array('id' => $ids[$i],'weid' => $_W['uniacid']));
				}
				
				break;
			case '2':
				$ids = $_GPC['ids'];
				$listorder = $_GPC['listorder'];
				for($i=0;$i < count($ids); $i++){
					pdo_query("UPDATE ".tablename('onljob_menu')." SET listorder = '{$listorder[$ids[$i]]}' WHERE id = '{$ids[$i]}' and weid = '{$_W['uniacid']}'");
				}
				
				break;
		}
		message('操作成功', $urlt, 'success');
	}else{
		message('操作失败', $urlt, 'error');
	}
}


//列表-------------------------
$pindex = max(1, intval($_GPC['page']));
$psize = 12;
$condition = '';
if (!empty($_GPC['titlename'])) {
	$condition .= " AND titlename LIKE '%{$_GPC['titlename']}%'";
}

$list = pdo_fetchall("SELECT * FROM " . tablename('onljob_menu') . " WHERE weid = '{$_W['uniacid']}' $condition ORDER BY listorder ASC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('onljob_menu') . " WHERE weid = '{$_W['uniacid']}' $condition");
$pager = pagination($total, $pindex, $psize);


include $this->template('menu');
?>