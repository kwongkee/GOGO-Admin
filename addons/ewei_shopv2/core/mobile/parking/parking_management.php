<?php

if (!defined('IN_IA'))
{
    exit('Access Denied');
}
class Parking_management_EweiShopV2Page extends mobilePage
{
	public function __construct(){
		
		load()->classs("head");
        $this->announcement = Head::announcement($_W['uniacid']);//公告
        $this->carousel 	= Head::carousel($_W['uniacid'],1);//广告
	}
	
	//管理首页-改版
    public function testmain()//Management
    {
    	$announcement = $this->announcement;//公告
    	$carousel 	  = $this->carousel;//广告
    	$title		  = '管理首页';
    	include $this->template("parking/management/main_gai");
    }
	
	//管理首页
    public function main()//Management
    {
    	$announcement = $this->announcement;//公告
    	$carousel 	  = $this->carousel;//广告
    	$title		  = '管理首页';
    	include $this->template("parking/management/main");
    }
    
    //服务启用-改版
    public function testServerstart(){
    	$announcement = $this->announcement;//公告
    	$carousel 	  = $this->carousel;//广告
    	$title		  = '服务启用';
    	include $this->template("parking/management/Serverstart_gai");
    }
    
    //服务启用
    public function Serverstart(){
    	$announcement = $this->announcement;//公告
    	$carousel 	  = $this->carousel;//广告
    	$title		  = '服务启用';
    	include $this->template("parking/management/Serverstart");
    }
    
    //订单管理-改版
    public function testOrdermanage(){
    	$announcement = $this->announcement;//公告
    	$carousel 	  = $this->carousel;//广告
    	$title		  = '订单管理';
    	include $this->template("parking/management/Ordermanage_gai");
    }
    
    //订单管理
    public function Ordermanage(){
    	$announcement = $this->announcement;//公告
    	$carousel 	  = $this->carousel;//广告
    	$title		  = '订单管理';
    	include $this->template("parking/management/Ordermanage");
    }
    
    //会员中心-改版
    public function testMember(){
    	$announcement = $this->announcement;//公告
    	$carousel 	  = $this->carousel;//广告
    	$title		  = '会员中心';
    	include $this->template("parking/management/Member_gai");
    }
    
    //会员中心
    public function Member(){
    	$announcement = $this->announcement;//公告
    	$carousel 	  = $this->carousel;//广告
    	$title		  = '会员中心';
    	include $this->template("parking/management/Member");
    }
    
    //增值服务-改版
    public function testAddserver(){
    	$announcement = $this->announcement;//公告
    	$carousel 	  = $this->carousel;//广告
    	$title		  = '增值服务';
    	include $this->template("parking/management/Addserver_gai");
    }
    
    //增值服务
    public function Addserver(){
    	$announcement = $this->announcement;//公告
    	$carousel 	  = $this->carousel;//广告
    	$title		  = '增值服务';
    	include $this->template("parking/management/Addserver");
    }
}
?>