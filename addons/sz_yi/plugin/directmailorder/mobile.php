<?php
// ģ��LTD�ṩ
if (!defined('IN_IA')) {
    exit('Access Denied');
}

class DirectmailorderMobile extends Plugin
{
    public function __construct()
    {
        parent::__construct('directmailorder');
    }
    
    public function detail()
    {
        $this->_exec_plugin('detail', false);
    }
    public function addcart()
    {
        $this->_exec_plugin('addcart', false);
    }
    
    public function generatesmallticket()
    {
        $this->_exec_plugin('generatesmallticket',false);
    }
    public function uploadIdCard()
    {
        $this->_exec_plugin('UploadIdCard',false);
    }
}

?>
