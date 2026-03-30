<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Test_EweiShopV2Page extends WebPage
{
    public $init;
    public function main(){
        !isset($this->init)&&$this->init();
        echo $this->init;
    }
    public function init()
    {
        $this->init='12';
    }
}