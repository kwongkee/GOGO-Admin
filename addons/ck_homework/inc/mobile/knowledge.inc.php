<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;

require "common.php";

$op = trim($_GPC['op']);
$urlt = $this->createMobileUrl('knowledge');
$urlt .= '&op='.$op;

//分类
$category = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and type = 'zsd' ORDER BY listorder ASC, cid DESC", array(), 'cid');
if (!empty($category)) {
	$children = '';
	foreach ($category as $cid => $cate) {
		if (!empty($cate['pid'])) {
			$children[$cate['pid']][$cate['cid']] = array($cate['cid'], $cate['name']);
		}
	}
}

//组卷---------------
if($op == 'chongzuo'){
	
	$parentid = empty($_GPC['parentid'])?0:intval($_GPC['parentid']);
	$type = empty($_GPC['type'])?0:intval($_GPC['type']);
	
	//判断身份是否正确
	if(empty($user_show) || $user_show['type'] == '1'){
		message_app('抱歉！您不是学生不能组卷！', '', 'error');
	}
	
	//组成练习题------------------
	if(checksubmit('add_submit')){
		
		$urlt .= '&parentid='.$parentid;
		if($type > 0){
			$urlt .= '&type='.$type;
		} 
	
		if (empty ($_GPC['titlename'])) {
			message_app('作业名称不能为空！', '', 'error');
		}
		
		$data = array(
			'weid' => $_W['uniacid'],
			'uid' => $_W['member']['uid'],
			'type' => 1,
			'titlename' => trim($_GPC['titlename'])
		);
		
		//添加
		$result = pdo_insert('onljob_practice_fen', $data, true);
		$fid = pdo_insertid();
		if (!empty($result) && !empty($fid)) {
			
			$numbpl = intval($_GPC['numbpl']);
			$kil = 0;
			$rs = pdo_fetchall("SELECT id FROM ".tablename('onljob_questions')."  WHERE  weid = '{$_W['uniacid']}' and type < 5 AND parentid = '{$parentid}' and status = '3' ORDER BY rand()");
			foreach ($rs as $bb => $age) {
				$work_que = pdo_get('onljob_practice_answer', array('qid' => $age['id'],'fid' => $fid,'uid' => $_W['member']['uid'],'weid' => $_W['uniacid']));
				if(empty($work_que) && $numbpl > $kil){
					pdo_insert('onljob_practice_answer', array('fid' => intval($fid),'weid' => $_W['uniacid'],'uid' => $_W['member']['uid'],'qid' => intval($age['id'])), true);
					$kil++;
				}
			}
			
			$urltk = $this->createMobileUrl('m_chongzuo_fen') . '&fid='.$fid;
			header("location: $urltk");
			exit();	
		}else{
			message_app('保存失败', array($urlt), 'error');
		}
	}
	//----------------------
	
	if($type == 0){
		
		//错题重做
		$_GPC['titlename'] = '自组错题重做' . date('Y-m-d', time());
		
		$data = array(
			'weid' => $_W['uniacid'],
			'uid' => $_W['member']['uid'],
			'type' => 1,
			'titlename' => $_GPC['titlename']
		);
		
		//添加
		$result = pdo_insert('onljob_practice_fen', $data, true);
		$fid = pdo_insertid();
		if (!empty($result) && !empty($fid)) {
			
			$list = pdo_fetchall("SELECT a.qid FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and b.parentid = '{$parentid}' and a.stateh = '1' and b.type < 5 GROUP BY a.qid ORDER BY b.id DESC");
			foreach ($list as $cid => $cate) {
				pdo_insert('onljob_practice_answer', array('fid' => intval($fid),'weid' => $_W['uniacid'],'uid' => $_W['member']['uid'],'qid' => intval($cate['qid'])), true);
			}
			
			$urltk = $this->createMobileUrl('m_chongzuo_fen') . '&fid='.$fid;
			header("location: $urltk");
			exit();	
		}else{
			message_app('保存失败', '', 'error');
		}
		
	}
	
	$titlename = '自组随机组题';
	$total_probl = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and type < 5 AND parentid = '{$parentid}' and status = '3'");
	include template_app('knowledge_chongzuo');
	exit();
}
//------------------

if($op == 'show'){

	//显示
	$id = empty($_GPC['id'])?0:intval($_GPC['id']);
	$zid = empty($_GPC['zid'])?0:intval($_GPC['zid']);
	$srdb = pdo_get('onljob_knowledge', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message_app('不存在或是已经被删除！', '', 'error');
	}
	
	//显示条数
	$shownumb = 3;
	$sosourl = $this->createMobileUrl('questionsoso');
	
	//存入浏览次数
	pdo_query("UPDATE ".tablename('onljob_knowledge')." SET readnum=readnum+1 WHERE weid = '{$_W['uniacid']}' and id='".$id."'");
	
	//单选题
	$total_problem1 = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and type = '1' AND parentid = '{$id}' and status = '3'");
	$list_problem1 = pdo_fetchall("SELECT * FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and type = '1' AND parentid = '{$id}' and status = '3' ORDER BY id DESC LIMIT 0,3");
	
	//多选题
	$total_problem2 = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and type = '2' AND parentid = '{$id}' and status = '3'");
	$list_problem2 = pdo_fetchall("SELECT * FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and type = '2' AND parentid = '{$id}' and status = '3' ORDER BY id DESC LIMIT 0,3");
	
	//填空题
	$total_problem3 = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and type = '3' AND parentid = '{$id}' and status = '3'");
	$list_problem3 = pdo_fetchall("SELECT * FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and type = '3' AND parentid = '{$id}' and status = '3' ORDER BY id DESC LIMIT 0,3");
	
	//判断题
	$total_problem4 = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and type = '4' AND parentid = '{$id}' and status = '3'");
	$list_problem4 = pdo_fetchall("SELECT * FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and type = '4' AND parentid = '{$id}' and status = '3' ORDER BY id DESC LIMIT 0,3");
	
	//主观题
	$total_problem5 = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and type = '5' AND parentid = '{$id}' and status = '3'");
	$list_problem5 = pdo_fetchall("SELECT * FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and type = '5' AND parentid = '{$id}' and status = '3' ORDER BY id DESC LIMIT 0,3");
	
	//作文题
	$total_problem6 = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and type = '6' AND parentid = '{$parentid}' and status = '3'");
	$list_problem6 = pdo_fetchall("SELECT * FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and type = '6' AND parentid = '{$parentid}' and status = '3' ORDER BY id DESC LIMIT 0,3");
	
	//知识点错题
	$list_cuowu = pdo_fetchall("SELECT b.* FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and b.parentid = '{$id}' and a.stateh = '1' GROUP BY a.qid ORDER BY b.id DESC");
	
	//VIP权限
	if(!empty($srdb['vipview'])){
		$vipview  = json_decode($srdb['vipview'], true);  /* 免费学习的VIP等级 */
		if(!empty($vipview)&&in_array($user_show['groupsid'], $vipview)){
			$usergrou = 1;
		}else{
			$usergrou = 0;
		}
	}else{ $usergrou = 0; }
	
	//章节列表
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_knowledge_son')."  WHERE weid = '{$_W['uniacid']}' and parentid = '{$srdb['id']}'");
	$list = pdo_fetchall("SELECT * FROM ".tablename('onljob_knowledge_son')." WHERE weid = '{$_W['uniacid']}' and parentid = '{$srdb['id']}' ORDER BY displayorder ASC,id DESC ");
	
	//视频、音频章节显示
	if (!empty($zid)) {
		$srdb_son = pdo_get('onljob_knowledge_son', array('id' => $zid,'weid' => $_W['uniacid']));
		if (empty($srdb_son)) {
			message_app('该章节不存在或是已经被删除！', '', 'error');
		}
		
		//内容
		$content = html_entity_decode($srdb_son['content']);
		
		//判断是否需要付费
		if($srdb_son['is_free'] < 1 && $srdb['paymoney'] > '0.00' && $usergrou == 0){
			$srdb_order = pdo_get('onljob_pay_order', array('parentid' => $srdb['id'],'uid' => $_W['member']['uid'],'type' => 'zsd','status' => 1,'weid' => $_W['uniacid']));
			if (empty($srdb_order)) {
				message_app('您还未购买该支付知识点，不能学习快去支付吧！', array($this->createMobileUrl('pay') . '&type=zsd&id=' . $id, $urlt . '&id=' . $id), 'error', array('我要购买', '返回'));
			}
		}
		
	}
	
	if($srdb_son['sectiontype'] == '2'){
		include template_app('knowledge_show2');
	}else{
		include template_app('knowledge_show');
	}
	exit();
	
}elseif($op == 'showq'){
	
	//显示问题
	$id = intval($_GPC['id']);
	$parentid = intval($_GPC['parentid']);
	$srdb = pdo_get('onljob_questions', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message_app('不存在或是已经被删除！', '', 'error');
	}
	
	//知识点名称
	$srdb_knowle = pdo_get('onljob_knowledge', array('id' => $srdb['parentid'],'weid' => $_W['uniacid']));
	
	$answer_arll = explode('\n',$srdb['answer']);
	
	if($srdb['type'] == 2){
		$zqanswerarr = explode('、',$srdb['zqanswer']);
	}
	
	include template_app('question_show');
	exit();
	
}elseif($op == 'lsit'){

	//列表-------------------------
	$tab = 1;
	$craid1 = empty($_GPC['craid1'])?0:intval($_GPC['craid1']);
	$craid2 = empty($_GPC['craid2'])?0:intval($_GPC['craid2']);
	$craid3 = empty($_GPC['craid3'])?0:intval($_GPC['craid3']);
	$craid4 = empty($_GPC['craid4'])?0:intval($_GPC['craid4']);
	$craid5 = empty($_GPC['craid5'])?0:intval($_GPC['craid5']);
	
	//分类名称
	if(!empty($craid1)){  		$cid = $craid1;  $tab = 1;
	}elseif(!empty($craid2)){	$cid = $craid2;  $tab = 2;
	}elseif(!empty($craid3)){	$cid = $craid3;  $tab = 3;
	}elseif(!empty($craid4)){	$cid = $craid4;  $tab = 4;
	}elseif(!empty($craid5)){	$cid = $craid5;  $tab = 5;
	}
	
	if(!empty($cid)){
		$srdb = pdo_get('onljob_class', array('cid' => $cid,'weid' => $_W['uniacid']));
		
		//分类列表
		if(!empty($children[$cid])){
			$classlist = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and type = 'zsd' and pid = '{$cid}' ORDER BY listorder ASC, cid DESC");
			$tab = $tab + 1;
		}else{
			$classlist = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and type = 'zsd' and pid = '{$srdb['pid']}' ORDER BY listorder ASC, cid DESC");
			$tab = $tab - 1;
		}
		
	}
	
	//列表-------------------
	$pindex = max(1, intval($_GPC['page']));
	$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
	if(!in_array($psize, array(20,50,100))) $psize = 20;
	
	$where = '';
	if (!empty($craid1)) {
		$where .= " AND craid1 = '{$craid1}'";
	}
	if (!empty($craid2)) {
		$where .= " AND craid2 = '{$craid2}'";
	}
	if (!empty($craid3)) {
		$where .= " AND craid3 = '{$craid3}'";
	}
	if (!empty($craid4)) {
		$where .= " AND craid4 = '{$craid4}'";
	}
	if (!empty($craid5)) {
		$where .= " AND craid5 = '{$craid5}'";
	}
	
	if (!empty($_GPC['keyword'])) {
		$where .= " AND titlename LIKE '%{$_GPC['keyword']}%'";
	}
	
	if(intval($_GPC['statep']) == 1){ //未掌握的知识点
		$where .= " AND id in (SELECT parentid FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and id in (SELECT qid FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and stateh = '1') GROUP BY parentid)";
	}elseif(intval($_GPC['statep']) == 2){ //已掌握的知识点
		$where .= " AND id in (SELECT parentid FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and id in (SELECT qid FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and stateh = '0') GROUP BY parentid)";
	}
	
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_knowledge')." WHERE weid = '{$_W['uniacid']}' and state = '1' {$where}");
	if($total){
		$list = pdo_fetchall("SELECT * FROM ".tablename('onljob_knowledge')." WHERE weid = '{$_W['uniacid']}' and state = '1' {$where} ORDER BY listorder ASC,id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
		if (!empty($list)) {
			foreach ($list as $cid => $cate) {
				//总题数
				$total_quz[$cate['id']]  = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' AND parentid = '{$cate['id']}' and status = '3'");
				
				//已做题数
				$total_quyz[$cate['id']]  = pdo_fetchcolumn("SELECT COUNT(*) FROM (SELECT qid FROM ".tablename('onljob_work_answer')." where weid = '{$_W['uniacid']}' GROUP BY qid) AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE b.weid = '{$_W['uniacid']}' AND b.parentid = '{$cate['id']}'");
				
				//做错题数
				$total_quycwz[$cate['id']]  = pdo_fetchcolumn("SELECT COUNT(*) FROM (SELECT qid FROM ".tablename('onljob_work_answer')." where weid = '{$_W['uniacid']}' and stateh = '1' GROUP BY qid) AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE b.weid = '{$_W['uniacid']}' AND b.parentid = '{$cate['id']}'");
				
			}
		}
	}
		
	$pager = pagination($total, $pindex, $psize,'', array('before' => 0, 'after' => 0));
		
	include template_app('knowledge_lsit');
	exit();
	
}else{
	
	//分类列表
	$nextid = empty($_GPC['nextid'])?0:intval($_GPC['nextid']);
	$nextall = $nextid + 1;

	$wheresql = '';
	$pid = empty($_GPC['pid'])?0:intval($_GPC['pid']);
	$wheresql .= " and pid = '{$pid}'";
	$list_class = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and type = 'zsd' $wheresql ORDER BY listorder ASC, cid DESC");

	include template_app('knowledge_class');
}