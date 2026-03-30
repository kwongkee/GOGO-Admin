<?php

//活动列表
global $_W, $_GPC;

$modulePublic = '../addons/' . $_GPC['m'] . '/static/';

switch ($_GPC['t']) {
    case 'help':
        include $this->template('page/help');
        break;
    case 'legal':
        include $this->template('page/legal');
        break;
    case 'traffic':
        include $this->template('page/traffic');
        break;
    case 'aboutup':
        include $this->template('page/aboutup');
        break;
}
?>
