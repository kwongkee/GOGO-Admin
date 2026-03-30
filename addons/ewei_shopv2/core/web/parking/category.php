<?php

if (!(defined('IN_IA'))) {
	exit('Access Denied');
}
class Category_EweiShopV2Page extends WebPage
{
    function main()
    {
        global $_W;
        global $_GPC;
        $all=pdo_fetchall("SELECT `id` FROM".tablename('parking_charge'));
        $total=count($all);
        $pageindex=$_GPC['page']<1?1:intval($_GPC['page']);
        $pagesize=10;
        $pager=pagination($total,$pageindex,$pagesize);
        $p=($pageindex-1)*$pagesize;
        $participators=pdo_fetchall("select * from ".tablename('parking_charge')."where uniacid={$_W['uniacid']}"." LIMIT ".$p.','.$pagesize);
		foreach ($participators as $key => &$value) {
			$value['payPeriod']=json_decode($value['payPeriod'],true);
		}
        include $this->template('parking/category_list');
    }

    public function del()
    {
        global $_W;
        global $_GPC;
        $result = pdo_delete('parking_Charge', array('id' => $_GPC['id'],'uniacid'=>$_W['uniacid']));
        if (!empty($result)) {
            show_json('1');
        }

    }
    public function add()
    {
        global $_W;
        global $_GPC;
        include $this->template("parking/category_add");
    }
    public function save()
    {
        global $_W;
        global $_GPC;
		// load()->func("logging");
		$Per=cache_load('period');
        $charge=[
			'payPeriod'=>$Per,
			'ChargeClass'=>$_GPC['ChargeClass'],
			'Allcapped'=>$_GPC['allcapped'],
            'uniacid'=>$_W['uniacid'],
            'allClass'=>$_GPC['modes']
        ];
        $res=pdo_insert("parking_Charge",$charge);
        if(!empty($res)){
			cache_delete("period");
            show_json('1','添加成功');
        }else{
            show_json('0','添加失败');
        }
    }
	public function Time(){
		global $_W;
		global $_GPC;
		// cache_delete("period");
		$res=[];
		$period=array('starTime'=>$_GPC['stime'],'endTime'=>$_GPC['etime'],'price'=>$_GPC['price'],'minute'=>$_GPC['minute'],'free'=>$_GPC['free'],'capped'=>$_GPC['capped'],'y_minute'=>$_GPC['y_minute'],'y_price'=>$_GPC['y_price']);
		$result=cache_load('period');
		if(!empty($result)){
			$res=json_decode($result,true);
			array_push($res,$period);
			cache_write('period',json_encode($res));
		}else{
			array_push($res,$period);
			cache_write('period',json_encode($res));
		}
		echo '添加成功,如需再添加从新输入新值即可';
	}
}
