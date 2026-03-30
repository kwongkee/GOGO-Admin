<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.php";
require "public.php";

$where = '';

if ($_GPC['labeid'] == '1') {     $where .= " AND craid1 = '{$_GPC['cid']}'";
}elseif($_GPC['labeid'] == '2'){   $where .= " AND craid2 = '{$_GPC['cid']}'";
}elseif($_GPC['labeid'] == '3'){   $where .= " AND craid3 = '{$_GPC['cid']}'";
}elseif($_GPC['labeid'] == '4'){   $where .= " AND craid4 = '{$_GPC['cid']}'";
}elseif($_GPC['labeid'] == '5'){   $where .= " AND craid5 = '{$_GPC['cid']}'";
}

//编码
$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_knowledge')."  WHERE weid = '{$_W['uniacid']}' {$where}");
if($total){
	$list = pdo_fetchall("SELECT id,titlename FROM ".tablename('onljob_knowledge')." WHERE weid = '{$_W['uniacid']}' {$where} ORDER BY listorder ASC,id DESC");
	foreach($list as $key=>$value){
		$return[$key] = array($value['id'], $value['titlename']);
	}
}

echo json_encode(array('msg'=>'ok','info'=>$return));
exit();
?>