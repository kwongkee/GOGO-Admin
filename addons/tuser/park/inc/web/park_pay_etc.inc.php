<?php

/**
 * 
 * 停车场收费规则
 */
global $_W, $_GPC;

$settings = $this->module['config'];


load()->web('tpl');
include $this->template('web/park_pay_etc');
?>
