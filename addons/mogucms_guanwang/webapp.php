<?php
defined('IN_IA') or exit('Access Denied');

class Mogucms_guanwangModuleWebapp extends WeModuleWebapp {
    public function doPageFengmian(){
		include $this->template('index');
	}
	public function doMobileFengmian(){
		die("Webapp");
	}
}