<?php


if (!(defined('IN_IA'))) {
	exit('Access Denied');
}
/**
 *
 */
class Address_EweiShopV2Page extends Page
{
    public  $data=null;
    function main()
    {
        return false;
    }

    public function province(){
        global $_W;
        global $_GPC;
        $this->data=cache_load('province');
        if(empty($this->data)){
            load()->func('communication');
            $url="http://api02.aliyun.venuscn.com/area/all?level=0&page=0&size=50";
            $extra=array();
            $APPCODE="504fd5f6a735437c97cd117e61cb4a24";
            array_push($extra,"Authorization:APPCODE ".$APPCODE);
            $this->data=getAddress($url,$extra);
            cache_write('province', $this->data);
        }
        echo $this->data;
    }

	public function City(){
		global $_W;
        global $_GPC;
		$city='';
		load()->func('communication');
		$url="http://api02.aliyun.venuscn.com/area/query?parent_id=".$_GPC['pid'];
		$extra=array();
		$APPCODE="504fd5f6a735437c97cd117e61cb4a24";
		array_push($extra,"Authorization:APPCODE ".$APPCODE);
		$city=getAddress($url,$extra);
		show_json($city);
	}
	public function Area(){
		global $_W;
		global $_GPC;
		load()->func('communication');
		$url="http://api02.aliyun.venuscn.com/area/query?parent_id=".$_GPC['pid'];
		$extra=array();
		$APPCODE="504fd5f6a735437c97cd117e61cb4a24";
		array_push($extra,"Authorization:APPCODE ".$APPCODE);
		echo getAddress($url,$extra);
	}
}
