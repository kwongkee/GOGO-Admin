<?php

if (!defined('IN_IA'))
{
    exit('Access Denied');
}
class Parking_orderdetails_EweiShopV2Page extends mobilePage
{
    protected $limit=8;
    public function main()
    {
        global $_W;
        global $_GPC;
        load()->classs("head");
        $title = '订单状态';
        $announcement = Head::announcement($_W['uniacid']);//公告
        $carousel = Head::carousel($_W['uniacid'],1);//广告
        $payType = !isset($_GPC['isPayType'])&&empty($_GPC['isPayType'])?1:$_GPC['isPayType'];
        $orderRes = [];
        switch ($payType){
            case 0:
                $orderRes = $this->getPrepaidOrder($_W['openid']);
                $html = $this->generatePrepaidHTML($orderRes);
                break;
            case 1:
				// 订单查询
                $orderRes = $this->getRearOrder($_W['openid']);			
                $html = $this->generateRearHTML($orderRes);
                break;
        }

        if(!empty($orderRes)){
            $this->updateOrderStatus($_W['openid']);
        }

        include $this->template("parking/parking_details");
    }



    /**
     * 获取预付费订单
     * @param $openid
     * @return mixed
     */
    public function getPrepaidOrder($openid)
    {
        $sql="select totalOrder.*,parkingOrder.number,parkingOrder.starttime,parkingOrder.endtime,parkingOrder.duration,
                parkingOrder.status,parkingOrder.charge_type,parkingOrder.charge_status,parkingOrder.card_time,position.Road,position.Road_num from ".tablename("foll_order")." as totalOrder left join ".tablename("parking_order")
            . " as parkingOrder on parkingOrder.ordersn=totalOrder.ordersn left join "
            .tablename("parking_space")." as space on space.park_code=parkingOrder.number left join"
            .tablename("parking_position")." as position on position.id=space.pid where totalOrder.user_id='".$openid
            ."' and totalOrder.application='parking' and parkingOrder.charge_type=0 and totalOrder.path_oid=0  order by totalOrder.id desc limit 0,".$this->limit;
       return  pdo_fetchall($sql);
    }


    /**获取后付费订单
     * @param $openid
     * @return mixed
     */
    public function getRearOrder($openid)
    {
        $sql="select totalOrder.*,parkingOrder.number,parkingOrder.starttime,parkingOrder.endtime,parkingOrder.duration,
                parkingOrder.status,parkingOrder.charge_type,parkingOrder.charge_status,parkingOrder.card_time,position.Road,position.Road_num from ".tablename("foll_order")." as totalOrder left join ".tablename("parking_order")
            . " as parkingOrder on parkingOrder.ordersn=totalOrder.ordersn left join "
            .tablename("parking_space")." as space on space.park_code=parkingOrder.number left join"
            .tablename("parking_position")." as position on position.id=space.pid where totalOrder.user_id='".$openid
            ."' and totalOrder.application='parking' and parkingOrder.charge_type=1  order by totalOrder.id desc limit 0,".$this->limit;
        return  pdo_fetchall($sql);
    }

    /**生成预付费订单列表
     * @param $order_Res
     * @return null|string
     */
    protected function generatePrepaidHTML($order_Res)
    {
		/*echo '<pre>';
		print_r($order_Res);*/
		
        $conten = null;
        if(empty($order_Res))return $conten='<li style="padding:50px;"><h4 style="text-align:center;">没有订单</h4></li>';
        foreach ($order_Res as $key => &$val){
            switch ($val['status']){
                case '已停车':
                    $conten .='<li><dl class="order_list_details box_orange"><dt><span>订单号<i>'.$val['ordersn'].'</i></span><em>已停车，待计费</em></dt><dd>';
                    $conten .= '<h5>'.$val["Road"].$val["number"].'号泊位</h5><p class="order_list_times">'.date("Y-m-d H:i:s",$val["starttime"]).'<span class="blueBg">进入</span></p>';
                    $conten .='<p class="order_list_font01">实计时长：<span>'.floor((time()-$val['starttime'])/60).'分</span></p>';
                    if($val['pay_status']==0||$val['pay_status']==2){
                        $conten .= '<a href="'. mobileUrl('parking/pay').'&orderid='.$val["ordersn"].'"class="chakan redBg">缴费</a>';
                    }else{
                        $conten .= '<a href="'.mobileUrl('parking/orderdetails').'&orderid='.$val['ordersn'] .'"class="chakan blueBg">详情</a>';
                    }

                    $conten .= '</dd></dl></li>';
                    break;
                case '已结算':
                    $conten .='<li><dl class="order_list_details box_orange"><dt><span>订单号<i>'.$val['ordersn'].'</i></span><em>已结算，待开票</em></dt><dd>';
                    $conten .='<h5>'.$val['Road'].$val['number'].'号泊位</h5><p class="order_list_times">'.date("Y-m-d H:i:s",$val['starttime']).'<span class="blueBg">进入</span>';
                    $conten .='</p><p class="order_list_times">'.date("Y-m-d H:i:s",$val['endtime']).'<span class="greenBg">离开</span></p>';
                    $conten .='<p class="order_list_font01">停车时长：<span>'.ceil(($val['endtime']-$val['starttime'])/60).' 分钟</span></p>';
                    $conten .='<p class="order_list_font01">实计时长：<span>'.$val['duration'].' 分钟</span></p>';
//                  $conten .='<p class="order_list_font01">月卡抵扣：<span>'.$val['card_time'].'分</span></p>';
                    $conten .='<p class="order_list_font01">应付金额：CNY <span>'.$val['total'].'</span> 元</p>';
                    $conten .='<p class="order_list_font01">实付金额：CNY <span>'.$val['pay_account'].'</span> 元</p>';
                
                    if ($val['charge_status']==0){
                        $conten .= '<a href="'.mobileUrl('parking/orderdetails').'&orderid='.$val['ordersn'] .'"class="chakan blueBg">详情</a>';
                    }else{
						
						if(($val['invoice_iskp'] == '0') && ($val['pay_account'] > 0) && ($val['IsWrite'] == '101')) {
							$conten .='<a href="'.mobileUrl('parking/invoicelist/ParkSn').'&ordersn='.$val["ordersn"].'"class="chakan redBg">开票</a>';
						} else {
							$conten .='<a href="'.mobileUrl('parking/msg').'" class="chakan greenBg">评价</a>';
							//$conten .='<a href="'.mobileUrl('parking/invoicelist/park').'&ordersn='.$val["ordersn"].'"class="chakan redBg">开票</a>';
						}
                    }
                    $conten .= '</dd></dl></li>';

                    break;
            }
        }
        return $conten;
    }


    /**
     * 生成后付费订单列表
     * @param $order_Res
     * @return null|string
     */
    protected function generateRearHTML($order_Res)
    {
        $conten = null;
        if(empty($order_Res))return $conten='<li style="padding:50px;"><h4 style="text-align:center;">没有订单</h4></li>';
        foreach ($order_Res as $key =>$val){
            switch ($val['status']){
                case '已停车':
                    $conten .='<li><dl class="order_list_details box_bule"><dt><span>订单号<i>'.$val['ordersn'].'</i></span><em>已停车，待计费</em></dt><dd>';
                    $conten .= '<h5>'.$val["Road"].$val["number"].'号泊位</h5><p class="order_list_times">'.date("Y-m-d H:i:s",$val["starttime"]).'<span class="blueBg">进入</span></p>';
                    $conten .='<p class="order_list_font01">实计时长：<span>'.floor((time()-$val['starttime'])/60).' 分钟</span></p>';
                    $conten .= '<a href="'.mobileUrl('parking/orderdetails').'&orderid='.$val['ordersn'] .'"class="chakan blueBg">详情</a>';
                    $conten .= '</dd></dl></li>';
                    break;
                case '正计费':
                    $conten .='<li><dl class="order_list_details box_bule"><dt><span>订单号<i>'.$val['ordersn'].'</i></span><em>正计费，待离开</em></dt><dd>';
                    $conten .= '<h5>'.$val["Road"].$val["number"].'号泊位</h5><p class="order_list_times">'.date("Y-m-d H:i:s",$val["starttime"]).'<span class="blueBg">进入</span></p>';
                    $conten .='<p class="order_list_font01 green">已经计费：'.floor((time()-$val['starttime'])/60).' 分钟';
                    $conten .= '<a href="'.mobileUrl('parking/orderdetails').'&orderid='.$val['ordersn'] .'"class="chakan blueBg">详情</a>';
                    $conten .= '</dd></dl></li>';
                    break;
                case '已出账':
                    $conten .='<li><dl class="order_list_details box_bule"><dt><span>订单号<i>'.$val['ordersn'].'</i></span><em>已出账，待缴费</em></dt><dd>';
                    $conten .='<h5>'.$val['Road'].$val['number'].'号泊位</h5><p class="order_list_times">'.date("Y-m-d H:i:s",$val['starttime']).'<span class="blueBg">进入</span>';
                    $conten .='</p><p class="order_list_times">'.date("Y-m-d H:i:s",$val['endtime']).'<span class="greenBg">离开</span></p>';
                    $conten .='<p class="order_list_font01">停车时长：<span>'.ceil(($val['endtime']-$val['starttime'])/60).' 分钟</span></p>';
                    $conten .='<p class="order_list_font01">实计时长：<span>'.$val['duration'].' 分钟</span></p>';
//                  $conten .='<p class="order_list_font01">月卡抵扣：<span>'.$val['card_time'].'分</span></p>';
                    $conten .='<p class="order_list_font01">应付金额：CNY <span>'.$val['total'].'</span> 元</p>';
                    $conten .='<p class="order_list_font01">实付金额：CNY <span>'.$val['pay_account'].'</span> 元</p>';
                    $conten .='<a href="'.mobileUrl('parking/pay').'&orderid='.$val['ordersn'].'" class="chakan blueBg">缴费</a>';
                    $conten .= '</dd></dl></li>';
                    break;
                case '未结算':
                    $conten .='<li><dl class="order_list_details box_bule"><dt><span>订单号<i>'.$val['ordersn'].'</i></span><em>未结算</em></dt><dd>';
                    $conten .='<h5>'.$val['Road'].$val['number'].'号泊位</h5><p class="order_list_times">'.date("Y-m-d H:i:s",$val['starttime']).'<span class="blueBg">进入</span>';
                    $conten .='</p><p class="order_list_times">'.date("Y-m-d H:i:s",$val['endtime']).'<span class="greenBg">离开</span></p>';
                    $conten .='<p class="order_list_font01">停车时长：<span>'.ceil(($val['endtime']-$val['starttime'])/60).' 分钟</span></p>';
                    $conten .='<p class="order_list_font01">实计时长：<span>'.$val['duration'].' 分钟</span></p>';
//                  $conten .='<p class="order_list_font01">月卡抵扣：<span>'.$val['card_time'].'分</span></p>';
                    $conten .='<p class="order_list_font01">应付金额：CNY <span>'.$val['total'].'</span> 元</p>';
                    $conten .='<p class="order_list_font01">实付金额：CNY <span>'.$val['pay_account'].'</span> 元</p>';
                    $conten .='<a href="'.mobileUrl('parking/pay').'&orderid='.$val['ordersn'].'" class="chakan blueBg">缴费</a>';
                    $conten .= '</dd></dl></li>';
                    break;
                case '已结算':
                    $conten .='<li><dl class="order_list_details box_bule"><dt><span>订单号<i>'.$val['ordersn'].'</i></span><em>已结算，待开票</em></dt><dd>';
                    $conten .='<h5>'.$val['Road'].$val['number'].'号泊位</h5><p class="order_list_times">'.date("Y-m-d H:i:s",$val['starttime']).'<span class="blueBg">进入</span>';
                    $conten .='</p><p class="order_list_times">'.date("Y-m-d H:i:s",$val['endtime']).'<span class="greenBg">离开</span></p>';
                    $conten .='<p class="order_list_font01">停车时长：<span>'.ceil(($val['endtime']-$val['starttime'])/60).' 分钟</span></p>';
                    $conten .='<p class="order_list_font01">实计时长：<span>'.$val['duration'].' 分钟</span></p>';
//                  $conten .='<p class="order_list_font01">月卡抵扣：<span>'.$val['card_time'].'分</span></p>';
                    $conten .='<p class="order_list_font01">应付金额：CNY <span>'.$val['total'].'</span> 元</p>';
                    $conten .='<p class="order_list_font01">实付金额：CNY <span>'.$val['pay_account'].'</span> 元</p>';
					
					if(($val['invoice_iskp'] == '0') && ($val['pay_account'] > 0) && ($val['IsWrite'] == '101')){
						$conten .='<a href="'.mobileUrl('parking/invoicelist/ParkSn').'&ordersn='.$val["ordersn"].'"class="chakan redBg">开票</a>';
					} else {
						$conten .='<a href="'.mobileUrl('parking/msg').'" class="chakan greenBg">评价</a>';
					}
					//$conten .='<a href="'.mobileUrl('parking/invoicelist/park').'&ordersn='.$val["ordersn"].'"class="chakan redBg">开票</a>';
                    $conten .= '</dd></dl></li>';
                    break;
            }
        }
        return $conten;
    }

    protected function updateOrderStatus($openid){
            $notPayOrder=pdo_fetchall("select tb2.* from ".tablename("foll_order")." as tb1 left join ".tablename("parking_order")." as tb2 on tb2.ordersn=tb1.ordersn where tb1.application='parking' and tb1.user_id='".$openid."' and tb1.pay_status=0 ");
            $space=pdo_get("parking_space",array('park_code'=>$notPayOrder[0]['number']),array('cid'));
            $newTime=floor((time() - $notPayOrder[0]['starttime'])/60);
            if($newTime>$this->obtainFreeTime($space['cid'],$notPayOrder[0]['starttime'])&&$notPayOrder[0]['status']=='已停车'){
                pdo_update("parking_order",array('status'=>'正计费'),array('id'=>$notPayOrder[0]['id']));
            }
    }


    protected function obtainFreeTime($id,$stime)
    {
        $TollTimePrepaid=pdo_get("parking_charge",array("id"=>$id));
        if(!empty($TollTimePrepaid)){
            $TollTimePrepaid['payPeriod']=json_decode($TollTimePrepaid['payPeriod'],true);
            $freeParkingTime=0;
            $stime=date("H:i",$stime);
            foreach($TollTimePrepaid['payPeriod'] as $key=>$value){
                if($stime>=$value['starTime']||$stime<=$value['endTime']){
                    $freeParkingTime=$value['free'];
                    break;
                }
            }
        }
        return $freeParkingTime;
    }


    public function details()
    {
        global $_W;
        global $_GPC;
        if(!empty($_GPC['aid'])){
            $adminRes=pdo_get("parking_admin_user",array('id'=>$_GPC['aid']));
        }
        include $this->template("parking/parking_admindetails");
    }
    
    public function violi() {
    	global $_W;
    	global $_GPC;
    	$title  = '用户违规车牌查询';
    	$openid = trim($_W['openid']);
    	
    	include $this->template("parking/management/violition");
    }
    
    
    /**
     *违规订单 
     * 2018-09-13
     */
    public function violition() {
    	global $_W;
    	global $_GPC;
    	$title  = '用户违规车牌查询';
    	$openid = trim($_W['openid']);
    	$user   = pdo_get('parking_authorize',['openid'=>$openid],['id']);
    	if(empty($user)) {
			$url = mobileUrl('parking/reg');
			$this->message('您尚未注册会员，请前往注册',$url,'error');
    	}
    	$postUrl  = mobileUrl('parking/pay');
    	$upOpenid = mobileUrl('parking/parking_orderdetails/violitionUpdate');
    	
    	if($_W['ispost']) {
    		$title  = '用户违规车牌查询';
    		$CarNo = strtoupper(trim($_GPC['CarNo']));
    		setcookie('CarNo',$CarNo,time()+3600);
    		
    		//$CarNo = strtoupper(trim($_GPC['CarNo']));
    		$field = 'a.ordersn,a.number,a.starttime,a.endtime,a.duration,a.status,a.CarNo,b.total,b.pay_account,b.pay_status,b.create_time,b.pay_time,b.pay_type,b.user_id';
//			$find = array(':CarNo' => $CarNo,':pay_status'=>0,':paystatus'=>2,':status'=>'未结算');
			$find = array(':CarNo' => $CarNo,':pay_status'=>0,':paystatus'=>2,':is_violation'=>1);
//			$info = pdo_fetchall("SELECT ".$field." FROM ".tablename('parking_order')." a LEFT JOIN ".tablename('foll_order')." b ON a.ordersn = b.ordersn WHERE a.CarNo = :CarNo AND (b.pay_status = :pay_status OR b.pay_status=:paystatus) AND a.status = :status",$find);
			$info = pdo_fetchall("SELECT ".$field." FROM ".tablename('parking_order')." a LEFT JOIN ".tablename('foll_order')." b ON a.ordersn = b.ordersn WHERE a.CarNo = :CarNo AND (b.pay_status = :pay_status OR b.pay_status=:paystatus) AND a.is_violation=:is_violation and a.endtime!=0",$find);
    		if(empty($info)) {
    			setcookie('CarNo',' ',time()-3600);
    			//$url = mobileUrl('parking/parking_orderdetails/violition');
				//$this->message('您没有违规订单',$url,'error');
    		}
    		
    	} else if(isset($_COOKIE['CarNo'])) {
    		
    		$title  = '用户违规车牌查询';
    		$CarNo = $_COOKIE['CarNo'];
    		$field = 'a.ordersn,a.number,a.starttime,a.endtime,a.duration,a.status,a.CarNo,b.total,b.pay_account,b.pay_status,b.create_time,b.pay_time,b.pay_type,b.user_id';
			$find = array(':CarNo' => $CarNo,':pay_status'=>0,':paystatus'=>2,':is_violation'=>1);
			$info = pdo_fetchall("SELECT ".$field." FROM ".tablename('parking_order')." a LEFT JOIN ".tablename('foll_order')." b ON a.ordersn = b.ordersn WHERE a.CarNo = :CarNo AND (b.pay_status = :pay_status OR b.pay_status=:paystatus) AND a.is_violation=:is_violation and a.endtime!=0",$find);
    		if(empty($info)) {
    			setcookie('CarNo',' ',time()-3600);
    			//$url = mobileUrl('parking/parking_orderdetails/violition');
				//$this->message('您没有违规订单',$url,'success');
    		}
    		
    	}
    	//渲染数据
    	include $this->template("parking/management/violitiontest");
//    	include $this->template("parking/management/violition");
//    	include $this->template("parking/management/ttes");
    }

    
    
    /*
     * 更新用户绑定openid;
     */
    public function violitionUpdate(){
    	global $_W;
    	global $_GPC;
    	
    	if($_W['isajax']) {
    		
    		$ordersn = trim($_GPC['ordersn']);//作为更新条件，更新当前数据openid
    		$openid  = trim($_W['openid']);
    		$uniacid = trim($_GPC['i']);	  //当前公众号id
//    		$up = false;
    		$postUrl  = mobileUrl('parking/pay').'&orderid='.$ordersn;
    		$info    = pdo_get('foll_order',['ordersn'=>$ordersn],['id','user_id','uniacid']);
//    		$orderCard = pdo_get('parking_order',['ordersn'=>$ordersn],['CarNo']);
    		if(empty($info)) {
				echo json_encode(['code'=>0,'info'=>$info,'msg'=>'post data']);die;
    		}
    		//如果用户ID等于空也去更新数据
    		if($info['user_id'] == ''||$info['user_id']==0) {
//    		    $userLice = pdo_get('parking_authorize',['openid'=>$openid],['CarNo']);
//    		    if (!empty($userLice)&&$userLice['CarNo']==$orderCard['CarNo']){
//                    $up = pdo_update('foll_order',['user_id'=>$openid,'uniacid'=>$uniacid],['id'=>$info['id']]);
//                }else{
//                    $userLice = pdo_get('parking_verified',['openid'=>$openid],['license']);
//                    if (!empty($userLice)&&$userLice['license']==$orderCard['CarNo']){
//                        $up = pdo_update('foll_order',['user_id'=>$openid,'uniacid'=>$uniacid],['id'=>$info['id']]);
//                    }
//                }
    			$up = pdo_update('foll_order',['user_id'=>$openid,'uniacid'=>$uniacid],['id'=>$info['id']]);
    			/*if(!$up) {
    				echo json_encode(['code'=>0,'msg'=>'更新失败!']);die;
    			}*/
				
      			echo json_encode(['code'=>1,'msg'=>'更新成功','info'=>$postUrl]);die;
				
    		} else if(!empty($info['user_id'])) {
    			//如果用户ID 相等直接可以使用
    			if(($info['user_id'] == $openid) && ($info['uniacid']==$uniacid)) {
      				echo json_encode(['code'=>1,'msg'=>'更新成功','info'=>$postUrl]);die;
    			}else{
                    pdo_update('foll_order',['user_id'=>$openid,'uniacid'=>$uniacid],['id'=>$info['id']]);
      				echo json_encode(['code'=>1,'msg'=>'更新成功','info'=>$postUrl]);die;
                }
//    		else {//否则更新用户ID
//    				$up = pdo_update('foll_order',['user_id'=>$openid,'uniacid'=>$uniacid],['id'=>$info['id']]);
//      				echo json_encode(['code'=>1,'msg'=>'更新成功','info'=>$postUrl]);die;
//    			}
    		}
			
    		echo json_encode(['code'=>1,'msg'=>'不用更新','info'=>$postUrl]);die;
    	}
    	echo json_encode(['code'=>0,'msg'=>'','isa'=>$_W['isajax']]);
    }
    
    
   /*订单状态-改版*/    

	public function parkdetailmain()
    {
        global $_W;
        global $_GPC;
        load()->classs("head");
        $title = '订单状态';
        $announcement = Head::announcement($_W['uniacid']);//公告
        $carousel = Head::carousel($_W['uniacid'],1);//广告
        $payType = !isset($_GPC['isPayType'])&&empty($_GPC['isPayType'])?1:$_GPC['isPayType'];
        $orderRes = [];
        switch ($payType){
            case 0:
                $orderRes = $this->getPrepaidOrder($_W['openid']);
                $html = $this->testgeneratePrepaidHTML($orderRes);
                break;
            case 1:
				// 订单查询
                $orderRes = $this->getRearOrder($_W['openid']);			
                $html = $this->testgenerateRearHTML($orderRes);
                break;
        }

        if(!empty($orderRes)){
            $this->updateOrderStatus($_W['openid']);
        }

        include $this->template("parking/parking_details_gai");
    }
    
   /**生成预付费订单列表-改
     * @param $order_Res
     * @return null|string
     */
    protected function testgeneratePrepaidHTML($order_Res)
    {
		/*echo '<pre>';
		print_r($order_Res);*/
		
        $conten = null;
        if(empty($order_Res))return $conten='<li style="padding:50px;"><h4 style="text-align:center;">没有订单</h4></li>';
        foreach ($order_Res as $key => &$val){
            switch ($val['status']){
                case '已停车':
                    $conten .='<li><dl class="order_list_details box_orange"><dt style="border-bottom: 1px solid #ecc47e;"><span>订单号<i>'.$val['ordersn'].'</i></span><em>已停车，待计费</em></dt><dd>';
                    $conten .= '<h5>'.$val["Road"].$val["number"].'号泊位</h5><p class="order_list_times">'.date("Y-m-d H:i:s",$val["starttime"]).'<span class="blueBg">进入</span></p>';
                    $conten .='<p class="order_list_font01">实计时长：<span>'.floor((time()-$val['starttime'])/60).'分</span></p>';
                    if($val['pay_status']==0||$val['pay_status']==2){
                        $conten .= '<a href="'. mobileUrl('parking/pay').'&orderid='.$val["ordersn"].'"class="chakan redBg">缴费</a>';
                    }else{
                        $conten .= '<a href="'.mobileUrl('parking/orderdetails').'&orderid='.$val['ordersn'] .'"class="chakan blueBg">详情</a>';
                    }

                    $conten .= '</dd></dl></li>';
                    break;
                case '已结算':
                    $conten .='<li><dl class="order_list_details box_orange"><dt style="border-bottom: 1px solid #ecc47e;"><span>订单号<i>'.$val['ordersn'].'</i></span><em>已结算，待开票</em></dt><dd>';
                    $conten .='<h5>'.$val['Road'].$val['number'].'号泊位</h5><p class="order_list_times">'.date("Y-m-d H:i:s",$val['starttime']).'<span class="blueBg">进入</span>';
                    $conten .='</p><p class="order_list_times">'.date("Y-m-d H:i:s",$val['endtime']).'<span class="greenBg">离开</span></p>';
                    $conten .='<p class="order_list_font01">停车时长：<span>'.ceil(($val['endtime']-$val['starttime'])/60).' 分钟</span></p>';
                    $conten .='<p class="order_list_font01">实计时长：<span>'.$val['duration'].' 分钟</span></p>';
//                  $conten .='<p class="order_list_font01">月卡抵扣：<span>'.$val['card_time'].'分</span></p>';
                    $conten .='<p class="order_list_font01">应付金额：CNY <span>'.$val['total'].'</span> 元</p>';
                    $conten .='<p class="order_list_font01">实付金额：CNY <span>'.$val['pay_account'].'</span> 元</p>';
                
                    if ($val['charge_status']==0){
                        $conten .= '<a href="'.mobileUrl('parking/orderdetails').'&orderid='.$val['ordersn'] .'"class="chakan blueBg">详情</a>';
                    }else{
						
						if(($val['invoice_iskp'] == '0') && ($val['pay_account'] > 0) && ($val['IsWrite'] == '101')) {
							$conten .='<a href="'.mobileUrl('parking/invoicelist/ParkSn').'&ordersn='.$val["ordersn"].'"class="chakan redBg">开票</a>';
						} else {
							$conten .='<a href="'.mobileUrl('parking/msg/commentmain').'" class="chakan greenBg">评价</a>';
							//$conten .='<a href="'.mobileUrl('parking/invoicelist/park').'&ordersn='.$val["ordersn"].'"class="chakan redBg">开票</a>';
						}
                    }
                    $conten .= '</dd></dl></li>';

                    break;
            }
        }
        return $conten;
    }


    /**
     * 生成后付费订单列表-改
     * @param $order_Res
     * @return null|string
     */
    protected function testgenerateRearHTML($order_Res)
    {
        $conten = null;
        if(empty($order_Res))return $conten='<li style="padding:50px;"><h4 style="text-align:center;">没有订单</h4></li>';
        foreach ($order_Res as $key =>$val){
            switch ($val['status']){
                case '已停车':
                    $conten .='<li><dl class="order_list_details box_bule"><dt><span>订单号<i>'.$val['ordersn'].'</i></span><em>已停车，待计费</em></dt><dd>';
                    $conten .= '<h5>'.$val["Road"].$val["number"].'号泊位</h5><p class="order_list_times">'.date("Y-m-d H:i:s",$val["starttime"]).'<span class="blueBg">进入</span></p>';
                    $conten .='<p class="order_list_font01">实计时长：<span>'.floor((time()-$val['starttime'])/60).' 分钟</span></p>';
                    $conten .= '<a href="'.mobileUrl('parking/orderdetails').'&orderid='.$val['ordersn'] .'"class="chakan blueBg">详情</a>';
                    $conten .= '</dd></dl></li>';
                    break;
                case '正计费':
                    $conten .='<li><dl class="order_list_details box_bule"><dt><span>订单号<i>'.$val['ordersn'].'</i></span><em>正计费，待离开</em></dt><dd>';
                    $conten .= '<h5>'.$val["Road"].$val["number"].'号泊位</h5><p class="order_list_times">'.date("Y-m-d H:i:s",$val["starttime"]).'<span class="blueBg">进入</span></p>';
                    $conten .='<p class="order_list_font01 green">已经计费：'.floor((time()-$val['starttime'])/60).' 分钟';
                    $conten .= '<a href="'.mobileUrl('parking/orderdetails').'&orderid='.$val['ordersn'] .'"class="chakan blueBg">详情</a>';
                    $conten .= '</dd></dl></li>';
                    break;
                case '已出账':
                    $conten .='<li><dl class="order_list_details box_bule"><dt><span>订单号<i>'.$val['ordersn'].'</i></span><em>已出账，待缴费</em></dt><dd>';
                    $conten .='<h5>'.$val['Road'].$val['number'].'号泊位</h5><p class="order_list_times">'.date("Y-m-d H:i:s",$val['starttime']).'<span class="blueBg">进入</span>';
                    $conten .='</p><p class="order_list_times">'.date("Y-m-d H:i:s",$val['endtime']).'<span class="greenBg">离开</span></p>';
                    $conten .='<p class="order_list_font01">停车时长：<span>'.ceil(($val['endtime']-$val['starttime'])/60).' 分钟</span></p>';
                    $conten .='<p class="order_list_font01">实计时长：<span>'.$val['duration'].' 分钟</span></p>';
//                  $conten .='<p class="order_list_font01">月卡抵扣：<span>'.$val['card_time'].'分</span></p>';
                    $conten .='<p class="order_list_font01">应付金额：CNY <span>'.$val['total'].'</span> 元</p>';
                    $conten .='<p class="order_list_font01">实付金额：CNY <span>'.$val['pay_account'].'</span> 元</p>';
                    $conten .='<a href="'.mobileUrl('parking/pay').'&orderid='.$val['ordersn'].'" class="chakan blueBg">缴费</a>';
                    $conten .= '</dd></dl></li>';
                    break;
                case '未结算':
                    $conten .='<li><dl class="order_list_details box_bule"><dt><span>订单号<i>'.$val['ordersn'].'</i></span><em>未结算</em></dt><dd>';
                    $conten .='<h5>'.$val['Road'].$val['number'].'号泊位</h5><p class="order_list_times">'.date("Y-m-d H:i:s",$val['starttime']).'<span class="blueBg">进入</span>';
                    $conten .='</p><p class="order_list_times">'.date("Y-m-d H:i:s",$val['endtime']).'<span class="greenBg">离开</span></p>';
                    $conten .='<p class="order_list_font01">停车时长：<span>'.ceil(($val['endtime']-$val['starttime'])/60).' 分钟</span></p>';
                    $conten .='<p class="order_list_font01">实计时长：<span>'.$val['duration'].' 分钟</span></p>';
//                  $conten .='<p class="order_list_font01">月卡抵扣：<span>'.$val['card_time'].'分</span></p>';
                    $conten .='<p class="order_list_font01">应付金额：CNY <span>'.$val['total'].'</span> 元</p>';
                    $conten .='<p class="order_list_font01">实付金额：CNY <span>'.$val['pay_account'].'</span> 元</p>';
                    $conten .='<a href="'.mobileUrl('parking/pay').'&orderid='.$val['ordersn'].'" class="chakan blueBg">缴费</a>';
                    $conten .= '</dd></dl></li>';
                    break;
                case '已结算':
                    $conten .='<li><dl class="order_list_details box_bule"><dt><span>订单号<i>'.$val['ordersn'].'</i></span><em>已结算，待开票</em></dt><dd>';
                    $conten .='<h5>'.$val['Road'].$val['number'].'号泊位</h5><p class="order_list_times">'.date("Y-m-d H:i:s",$val['starttime']).'<span class="blueBg">进入</span>';
                    $conten .='</p><p class="order_list_times">'.date("Y-m-d H:i:s",$val['endtime']).'<span class="greenBg">离开</span></p>';
                    $conten .='<p class="order_list_font01">停车时长：<span>'.ceil(($val['endtime']-$val['starttime'])/60).' 分钟</span></p>';
                    $conten .='<p class="order_list_font01">实计时长：<span>'.$val['duration'].' 分钟</span></p>';
//                  $conten .='<p class="order_list_font01">月卡抵扣：<span>'.$val['card_time'].'分</span></p>';
                    $conten .='<p class="order_list_font01">应付金额：CNY <span>'.$val['total'].'</span> 元</p>';
                    $conten .='<p class="order_list_font01">实付金额：CNY <span>'.$val['pay_account'].'</span> 元</p>';
					
					if(($val['invoice_iskp'] == '0') && ($val['pay_account'] > 0) && ($val['IsWrite'] == '101')){
						$conten .='<a href="'.mobileUrl('parking/invoicelist/ParkSn').'&ordersn='.$val["ordersn"].'"class="chakan redBg">开票</a>';
					} else {
						$conten .='<a href="'.mobileUrl('parking/msg/commentmain').'" class="chakan greenBg">评价</a>';
					}
					//$conten .='<a href="'.mobileUrl('parking/invoicelist/park').'&ordersn='.$val["ordersn"].'"class="chakan redBg">开票</a>';
                    $conten .= '</dd></dl></li>';
                    break;
            }
        }
        return $conten;
    } 
        
    
    /**
     *违规订单 
     * 2018-09-13
     */
    public function testviolition() {
    	global $_W;
    	global $_GPC;
    	$title  = '用户违规车牌查询';
    	$openid = trim($_W['openid']);
    	$user   = pdo_get('parking_authorize',['openid'=>$openid],['id']);
    	if(empty($user)) {
			$url = mobileUrl('parking/reg');
			$this->message('您尚未注册会员，请前往注册',$url,'error');
    	}
    	$postUrl  = mobileUrl('parking/pay');
    	$upOpenid = mobileUrl('parking/parking_orderdetails/violitionUpdate');
    	
    	if($_W['ispost']) {
    		$title  = '用户违规车牌查询';
    		$CarNo = strtoupper(trim($_GPC['CarNo']));
    		setcookie('CarNo',$CarNo,time()+3600);
    		
    		//$CarNo = strtoupper(trim($_GPC['CarNo']));
    		$field = 'a.ordersn,a.number,a.starttime,a.endtime,a.duration,a.status,a.CarNo,b.total,b.pay_account,b.pay_status,b.create_time,b.pay_time,b.pay_type,b.user_id';
//			$find = array(':CarNo' => $CarNo,':pay_status'=>0,':paystatus'=>2,':status'=>'未结算');
			$find = array(':CarNo' => $CarNo,':pay_status'=>0,':paystatus'=>2,':is_violation'=>1);
//			$info = pdo_fetchall("SELECT ".$field." FROM ".tablename('parking_order')." a LEFT JOIN ".tablename('foll_order')." b ON a.ordersn = b.ordersn WHERE a.CarNo = :CarNo AND (b.pay_status = :pay_status OR b.pay_status=:paystatus) AND a.status = :status",$find);
			$info = pdo_fetchall("SELECT ".$field." FROM ".tablename('parking_order')." a LEFT JOIN ".tablename('foll_order')." b ON a.ordersn = b.ordersn WHERE a.CarNo = :CarNo AND (b.pay_status = :pay_status OR b.pay_status=:paystatus) AND a.is_violation=:is_violation and a.endtime!=0",$find);
    		if(empty($info)) {
    			setcookie('CarNo',' ',time()-3600);
    			//$url = mobileUrl('parking/parking_orderdetails/violition');
				//$this->message('您没有违规订单',$url,'error');
    		}
    		
    	} else if(isset($_COOKIE['CarNo'])) {
    		
    		$title  = '用户违规车牌查询';
    		$CarNo = $_COOKIE['CarNo'];
    		$field = 'a.ordersn,a.number,a.starttime,a.endtime,a.duration,a.status,a.CarNo,b.total,b.pay_account,b.pay_status,b.create_time,b.pay_time,b.pay_type,b.user_id';
			$find = array(':CarNo' => $CarNo,':pay_status'=>0,':paystatus'=>2,':is_violation'=>1);
			$info = pdo_fetchall("SELECT ".$field." FROM ".tablename('parking_order')." a LEFT JOIN ".tablename('foll_order')." b ON a.ordersn = b.ordersn WHERE a.CarNo = :CarNo AND (b.pay_status = :pay_status OR b.pay_status=:paystatus) AND a.is_violation=:is_violation and a.endtime!=0",$find);
    		if(empty($info)) {
    			setcookie('CarNo',' ',time()-3600);
    			//$url = mobileUrl('parking/parking_orderdetails/violition');
				//$this->message('您没有违规订单',$url,'success');
    		}
    		
    	}
    	//渲染数据
    	include $this->template("parking/management/violitiontest_gai");
//    	include $this->template("parking/management/violition");
//    	include $this->template("parking/management/ttes");
    }
    
}