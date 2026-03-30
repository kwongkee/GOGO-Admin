<?php
// ģ��LTD�ṩ
if (!defined('IN_IA')) {
    exit('Access Denied');
}

class apiMobile extends Plugin
{
    public function __construct()
    {
        parent::__construct('api');
    }
    public function sendPreCommitNotice()
    {
        //发送预提通知
        echo '不登录';
        
        // $this->_exec_plugin('sendPreCommitNotice', false);
    }
}

?>
