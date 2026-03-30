<?php
// 模块LTD提供
global $_W;
global $_GPC;
$sets = $this->getSet();
$set = $sets['ranking'];
$sql = 'select dm.openid, dm.id from ' . tablename('sz_yi_member') . ' dm ' . ' left join ' . tablename('sz_yi_commission_level') . ' l on l.id = dm.agentlevel' . ' left join ' . tablename('mc_mapping_fans') . 'f on f.openid=dm.openid and f.uniacid=' . $_W['uniacid'] . ' where dm.uniacid = ' . $_W['uniacid'] . ' and dm.isagent =1  ' . $condition . ' ORDER BY dm.agenttime desc';
$list = pdo_fetchall($sql);

foreach ($list as $key => $value) {
	$info = p('commission')->getInfo($value['openid'], array('total', 'pay'));

	if (0 < $info['commission_total']) {
		$ranking = pdo_fetch('select * from ' . tablename('sz_yi_ranking') . ' where uniacid = \'' . $_W['uniacid'] . '\' and mid = \'' . $value['id'] . '\'');

		if ($ranking) {
			pdo_update('sz_yi_ranking', array('credit' => $info['commission_total']), array('mid' => $value['id'], 'uniacid' => $_W['uniacid']));
		}
		else {
			$data = array('uniacid' => $_W['uniacid'], 'mid' => $value['id'], 'credit' => $info['commission_total']);
			pdo_insert('sz_yi_ranking', $data);
		}
	}
}

if (!empty($_GPC['type'])) {
	message('更新佣金排名成功!', $this->createPluginWebUrl('ranking/set'), 'success');
	exit();
	return 1;
}

echo '<pre>';
print_r('更新佣金排名成功');
exit();

?>
