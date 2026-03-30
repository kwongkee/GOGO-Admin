<?php 
defined('IN_IA') or die('Access Denied');
class sunshine_huayueModule extends WeModule
{
    public function fieldsFormDisplay($rid = 0)
    {
    }
    public function fieldsFormValidate($rid = 0)
    {
        return '';
    }
    public function fieldsFormSubmit($rid)
    {
    }
    public function ruleDeleted($rid)
    {
    }
    public function settingsDisplay($settings)
    {
        global $_W, $_GPC;
        if (checksubmit()) {
            $this->saveSettings($dat);
        }
        include $this->template('setting');
    }
}