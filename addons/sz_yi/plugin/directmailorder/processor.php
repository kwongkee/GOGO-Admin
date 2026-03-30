<?php
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

require IA_ROOT.'/addons/sz_yi/defines.php';
require SZ_YI_INC.'plugin/plugin_processor.php';

class DirectmailorderProcessor extends PluginProcessor
{
    public function __construct()
    {
        parent::__construct('directmailorder');
    }
}

?>
