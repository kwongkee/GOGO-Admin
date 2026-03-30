<?php
//www.taomadao.com 独家解密

defined ('IN_IA')or exit ('Access Denied');class Netbuffer_followtipModule extends WeModule{public function settingsDisplay($settings){global $_GPC, $_W;load()-> func('file');if (checksubmit()){$cfg =array('nbf_followtip_usr' => $_GPC['nbf_followtip_usr'], 'nbf_followtip_usr_startcount' => $_GPC['nbf_followtip_usr_startcount'] );if ($this -> saveSettings($cfg)){message('保存成功', 'refresh');}}include $this -> template('settings');}}