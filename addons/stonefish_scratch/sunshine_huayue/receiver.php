<?php 
defined('IN_IA') or die('Access Denied');
class sunshine_huayueModuleReceiver extends WeModuleReceiver
{
    public function receive()
    {
        $type = $this->message['type'];
    }
}