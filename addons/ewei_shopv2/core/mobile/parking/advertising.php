<?php
if ( !defined('IN_IA') ) {
    exit('Access Denied');
}


class Advertising_EweiShopV2Page extends mobilePage
{
	public function __construct ()
    {
        parent::__construct();
        load()->func("common");
        isUserReg();
        load()->classs("head");
        load()->classs("curl");
        load()->classs('des');
    }


    public function main ()
    {
    	global $_W;
        global $_GPC;
       	$title = '广告记录';
       	
       	$condition = [
			'openid' => $_W['openid'],
			'uniacid'=> $_W['uniacid'],
		];
       	//查询总条数
		$count = pdo_fetch('SELECT COUNT(*) as count FROM ' . tablename('foll_advertising_view').' WHERE uniacid = :uniacid and openid = :openid', $condition);
		$total = $count['count'];
		//获取页数
		$pageindex = intval($_GPC['page'])?intval($_GPC['page']):'1';
		//每页显示条数
        $pagesize = 5;
        $pager = pagination($total, $pageindex, $pagesize);
        //limit
		$p = ($pageindex-1) * $pagesize;
		
		$view=pdo_fetchall("SELECT a.*,b.url,b.min_times FROM ".tablename('foll_advertising_view').' AS a LEFT JOIN '.tablename('foll_advertising_content').' AS b ON a.adv_id = b.id WHERE a.uniacid = '.$_W['uniacid'].' AND a.openid = "'.$_W['openid'].'" ORDER BY a.create_time asc LIMIT '.$p.','.$pagesize);
		foreach($view as $k=>$v){
			$view[$k]['view_time']=$this->time_change($v['view_times']);
			$view[$k]['min_time']=$this->time_change($v['min_times']);
		}
//		echo '<pre>';
//		print_r($view);die;
//      $shop_history = pdo_fetchall("SELECT id,create_date,XMMC,XMJE,PDF_URL FROM " . tablename('invoices_ord') . " WHERE invoice_type='mall' and uniacid = ". $_W['uniacid']. " and openid = '".$_W['openid']."' and state = 1 ORDER BY `id` DESC LIMIT " . $p . "," . $pagesize); 
        
       	include $this->template('parking/advertising');
    }
    
     public function time_change($times){
     	if($times<60){
     		//一分钟以内
     		$data=$times.'秒';
     	}else if(60<=$times && $times<3600){
     		//一小时以内
     		$data=floor(($times/'60')).'分'.floor(($times%'60')).'秒';
     		
     	}elseif(3600<=$times && $times <=86400){
     		//一天以内
     			$data=floor($times/3600).'时'.floor(floor(($times%3600)/60)).'分'.floor(floor(($times%3600)%60)).'秒';
     	}
     	return $data;
     }
}
?>