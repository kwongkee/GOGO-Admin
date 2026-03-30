<?php
// 模块LTD提供
global $_W;
global $_GPC;
$set = $this->getSet();
$set['commission'] = json_decode($set['commission'], true);
$set['member'] = json_decode($set['member'], true);
$member_levels = m('member')->getLevels();
$distributor_levels = p('commission')->getLevels();

if (checksubmit('submit')) {
	$_GPC['setdata']['commission'] = json_encode($_GPC['setdata']['commission'], true);
	$_GPC['setdata']['member'] = json_encode($_GPC['setdata']['member'], true);
	$data = (is_array($_GPC['setdata']) ? array_merge($set, $_GPC['setdata']) : array());
	$this->updateSet($data);
	m('cache')->set('template_' . $this->pluginname, $data['style']);
	plog('return.commission', '修改分销商等级返现比例');
	message('设置保存成功!', referer(), 'success');
}

load()->func('tpl');
include $this->template('level');

?>
