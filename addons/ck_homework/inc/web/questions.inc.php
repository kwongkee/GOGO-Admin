<?php
/**
 * 模块微站定义
 *
 * @author wujinxin
 * @url http://bbs.we7.cc/
 **/
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

//题库管理
load()->func('tpl');
$op = $_GPC['op'];
//知识点编号
$parentid = empty($_GPC['parentid'])?0:intval($_GPC['parentid']);
// $type_arr = array('1'=>'单选题','2'=>'多选题','3'=>'填空题','4'=>'判断题','5'=>'主观题','6'=>'作文题','7'=>'阅读题');
$type_arr = array('1'=>'单选题','2'=>'多选题','3'=>'填空题','4'=>'判断题','5'=>'主观题','6'=>'作文题');
$level_arr = array('1'=>'1级','2'=>'2级','3'=>'3级','4'=>'4级','5'=>'5级','6'=>'6级');
$arraytp =  array('A','B','C','D','E','F');

$urlt = $this->createWebUrl('questions');
//获取配置
$config = pdo_get('onljob_config', array('weid' => $_W['uniacid']));

//知识点分类
$category = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and type = 'zsd' ORDER BY listorder ASC, cid DESC", array(), 'cid');
if (!empty($category)) {
	$children = '';
	foreach ($category as $cid => $cate) {
		if (!empty($cate['pid'])) {
			$children[$cate['pid']][$cate['cid']] = array($cate['cid'], $cate['name']);
		}
	}
}

//操作
if(checksubmit('add_submit') || checksubmit('edit_submit')){

	$type = intval($_GPC['type']);
	if($type == 1){
		$zqanswer = $_GPC['zqanswer1'];
		$answer = implode('\n', $_GPC['answer']);
	}elseif($type == 2){
		$zqanswer = implode('、', $_GPC['zqanswer2']);
		$answer = implode('\n', $_GPC['answer']);
	}elseif($type == 3){
		$zqanswer = $_GPC['zqanswer3'];
	}elseif($type == 4){
		$zqanswer = $_GPC['zqanswer4'];
	}elseif($type == 5){
		$zqanswer = $_GPC['zqanswer5'];
	}elseif($type == 6){
		$zqanswer = $_GPC['zqanswer6'];
	}
	
	$data = array(
		'type' => intval($_GPC['type']),
		'level' => intval($_GPC['level']),
		'parentid' => intval($_GPC['parentid']),
		'titlename' => $_GPC['titlename'],
		'answer' => $answer,
		'zqanswer' => $zqanswer,
		'content' => $_GPC['content'],
		'addtime' => time()
	);
	
	if (empty ($data['type'])) {
		message('请选择题型！', '', 'error');
	}
	if (empty ($data['level'])) {
		message('请选择题目难度！', '', 'error');
	}
	if (empty ($data['parentid'])) {
		message('请选择知识点！', '', 'error');
	}
	if (empty ($data['titlename'])) {
		message('题目不能为空！', '', 'error');
	}
	if (empty ($data['titlename'])) {
		message('题目不能为空！', '', 'error');
	}
	if (empty ($data['answer']) && $type < 3) {
		message('选项不能为空！', '', 'error');
	}
	if (empty ($data['zqanswer'])) {
		message('答案不能为空！', '', 'error');
	}
	if (empty ($data['content'])) {
		message('答案解析不能为空！', '', 'error');
	}
	
	//添加
	if(checksubmit('add_submit')){
		$data['weid'] = $_W['uniacid'];
		$data['status'] = 3;
		$result = pdo_insert('onljob_questions', $data, true);
		if (!empty($result)) {
			message('添加成功！', $urlt, 'success');
		}else{
			message('保存失败！', '', 'error');
		}
	}
	
	//修改
	if(checksubmit('edit_submit')){
		if(!empty($_GPC['id'])){
			
			//审核
			if(!empty($_GPC['status']) && !empty($_GPC['uid'])){
				$data['status'] = intval($_GPC['status']);  
				if($_GPC['status'] == 2){//未通过    
					$data['message'] = $_GPC['message'];
				}elseif($_GPC['status'] == 3 && !empty($config['reward_money'])){ //通过
					
					if($config['reward_type'] == 1){ //余额
						//修改余额
						pdo_query("UPDATE ".tablename('mc_members')." SET credit2 = credit2+{$config['reward_money']} WHERE uniacid = '{$_W['uniacid']}' and uid = '".$_GPC['uid']."'");
						$data['message'] = "奖励 " .$config['reward_money']. " 余额";
					}elseif($config['reward_type'] == 0){ //积分
						//修改积分
						pdo_query("UPDATE ".tablename('mc_members')." SET credit1 = credit1+{$config['reward_money']} WHERE uniacid = '{$_W['uniacid']}' and uid = '".$_GPC['uid']."'");
						$data['message'] = "奖励 " .$config['reward_money']. " 积分";
					}
					
					$moneydesc = "上传题目 获得 " . $data['message'];
					
					//存入账目-------------	
					$datap = array(
						'weid' => $_W['uniacid'],
						'dateline' => time(),
						'uid' => intval($_GPC['uid']),
						'moneytype' => 'qjl',
						'moneydesc' => $moneydesc,
						'money' => $config['reward_money'],
						'accounttype' => 2
					);
					pdo_insert('onljob_accounts', $datap, true);
					//----------------------
						
				}
			}
			
			pdo_update('onljob_questions', $data, array('id' => $_GPC['id'],'weid' => $_W['uniacid']));
			message('保存成功', $urlt, 'success');
			
		}else{
			message('保存失败！', '', 'error');
		}
	}
	
}

//导入-----------------------------
if(checksubmit('import_submit')){
	
	if (empty ($_GPC['parentid'])) {
		message('请选择知识点！', '', 'error');
	}
	
	$filename = $_FILES['file']['tmp_name'];
	if (empty ($filename)) {
		message('请选择要导入的CSV文件！', '', 'error');
	}
	 
	$handle = fopen($filename, 'r');
	$result = input_csv($handle); //解析csv
	$len_result = count($result);
	if($len_result==0){
		message('抱歉！CSV文件没有任何数据！', '', 'error');
	}
	
	$data_values = '';
	for ($i = 1; $i < $len_result; $i++) { //循环获取各字段值
		
		$type = iconv('gb2312','utf-8', $result[$i][0]);
		$titlename = iconv('gb2312','utf-8', $result[$i][1]);
		$level = iconv('gb2312','utf-8', $result[$i][2]);
		$answer = iconv('gb2312','utf-8', $result[$i][3]);
		$zqanswer = iconv('gb2312','utf-8', $result[$i][4]);
		if($type == 2){
			$zqanswer = implode('、', explode('，', $zqanswer));
		}
		$content = iconv('gb2312','utf-8', $result[$i][5]);
		
		$data = array(
			'type' => intval($type),
			'level' => intval($level),
			'parentid' => intval($_GPC['parentid']),
			'titlename' => $titlename,
			'answer' => $answer,
			'zqanswer' => $zqanswer,
			'content' => $content,
			'addtime' => time(),
			'status' => 3,
			'weid' => $_W['uniacid']
		);
		
		$resultp = pdo_insert('onljob_questions', $data, true);
	
	}

	if($resultp){
		message('导入成功！', $urlt, 'success');
	}else{
		message('导入失败！', '', 'error');
	}
	
}

function input_csv($handle) {
	$out = array ();
	$n = 0;
	while ($data = fgetcsv($handle, 10000)) {
		$num = count($data);
		for ($i = 0; $i < $num; $i++) {
			$out[$n][$i] = $data[$i];
		}
		$n++;
	}
	return $out;
}
//------------------------------------------

//读取
if($op == 'edit'){

	$id = intval($_GPC['id']);
	$srdb = pdo_get('onljob_questions', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message('不存在或是已经被删除！', '', 'error');
	}
	
	$answer_arll = explode('\n',$srdb['answer']);
	
	if($srdb['type'] == 2){
		$zqanswerarr = explode('、',$srdb['zqanswer']);
	}
	
	$parentid = $srdb['parentid'];
}

if($parentid > 0){
	//知识点名称
	$srdb_knowle = pdo_get('onljob_knowledge', array('id' => $parentid,'weid' => $_W['uniacid']));
	$_GPC['zstitlename'] = $srdb_knowle['titlename'];
}

//删除
if($op == 'delete'){
	$id = intval($_GPC['id']);
	if($id){
		pdo_delete('onljob_questions', array('id' => $id,'weid' => $_W['uniacid']));
		message('删除成功', $urlt, 'success');
	}else{
		message('删除失败', $urlt, 'error');
	}
}

//批量删除
if (checksubmit('deletesubmit')) {
	if($_GPC['ids'] && is_array($_GPC['ids'])) {

		$ids = $_GPC['ids'];
		for($i=0;$i < count($ids); $i++){
			pdo_delete('onljob_questions', array('id' => $ids[$i],'weid' => $_W['uniacid']));
		}
		message('批量删除成功', $urlt, 'success');
	}else{
		message('批量删除失败', $urlt, 'error');
	}
}

//列表-------------------------
$pindex = max(1, intval($_GPC['page']));
$psize = 10;
$where = '';
if (!empty($_GPC['titlename'])) {
	$where .= " AND a.titlename LIKE '%{$_GPC['titlename']}%'";
}

if (!empty($_GPC['zstitlename'])) {
	$where .= " AND b.titlename LIKE '%{$_GPC['zstitlename']}%'";
	$urlt .= "&zstitlename=".$_GPC['zstitlename'];
}

if (!empty($_GPC['parentid'])) {
	$where .= " AND a.parentid = '{$_GPC['parentid']}'";
	$urlt .= "&parentid=".$_GPC['parentid'];
}

if (!empty($_GPC['type'])) {
	$where .= " AND a.type = '{$_GPC['type']}'";
}

if (!empty($_GPC['statusd'])) {
	$where .= " and a.status = '{$_GPC['statusd']}'";
}

$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_questions')." AS a LEFT JOIN ".tablename('onljob_knowledge')." AS b ON a.parentid = b.id  WHERE a.weid = '{$_W['uniacid']}' and a.status > 0 {$where}");
if($total){
	$list = pdo_fetchall("SELECT a.*,b.titlename as zsdname FROM ".tablename('onljob_questions')." AS a LEFT JOIN ".tablename('onljob_knowledge')." AS b ON a.parentid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.status > 0 {$where} ORDER BY a.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	foreach($list as $key=>$value){
		$user_name = pdo_get('onljob_user', array('uid' => $value['uid'], 'weid' => $_W['uniacid']), array('name'));
		if (!empty($user_name['name'])) {
			$username[$value['id']] = $user_name['name'];
		}
	}
}
$pager = pagination($total, $pindex, $psize);

if($op == 'add' || $op == 'edit'){
	include $this->template('questions_add');
}else{
	include $this->template('questions');
}
?>