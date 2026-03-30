<?php 
defined('IN_IA') or die('Access Denied');
class sunshine_huayueModuleProcessor extends WeModuleProcessor
{
    public function respond()
    {
        $content = $this->message['content'];
    }
}