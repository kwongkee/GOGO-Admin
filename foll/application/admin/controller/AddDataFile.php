<?php
namespace app\admin\controller;
//use app\admin\controller;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use think\Request;
use think\Loader;
use PHPExcel_IOFactory;
use PHPExcel;
class AddDataFile extends Auth
{
	public function __construct() {
		$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
	}
	//丰端祥接口   微信免密
    public function apiFortune(Request $request)
    {
       $file = Loader::model("File","model");
       $file_url_s = "../../crontab/wx/loadBill".date("Ymd",strtotime("-1 day")).".txt";
       //$file_url_s = "../../crontab/wx/loadBill20180426.txt";
       if(file_exists($file_url_s)){
       	 if(filesize($file_url_s) == 0){
       	 	die;
       	 }
         $content = file_get_contents($file_url_s,'r');
         $contents= explode("\n",$content);//explode()函数以","为标识符进行拆分
        if(!empty($contents))
        {
	        $data = array();
	        foreach($contents as $k=>$v)
	        {
	        	$v = substr($v,0,-1);
	        	$n = explode(",",$v);
	       	 	if(!empty($n[1])) {
		       		$data[] = array(
		       	      'op_order_id' => $n[0],//订单号
		       	      'flow_number' => $n[0],//流水号
		       	      'deal_type'   => $n[1],//交易类型 1支付5退款
		       	      'pay_money'   => $n[2],//金额
		       	      'time'        => strtotime($n[3]),//交易时间 转成时间戳
		       	      'pay_type'    => $n[4],//支付方式
		       	      'poundage'    => $n[5],//商户手续费
		       	      'api_type'    => 1,// 接口(1丰端祥2银联无感)
		        	); 
	        	}
	        }
//	      	print_r($data);die;
	       	$file->addEnterprise($data);
       }
       }
    }
    
    
    //银联无感接口
    public function apiSense(){
       $file = Loader::model("File","model");
       $file_url_s = "../../crontab/".date("Ymd",strtotime("-1 day"))."/7000000000000049".date("Ymd",strtotime("-1 day"))."01.TXT";
//     echo $file_url_s;
       if(file_exists($file_url_s)){
       	$content = file_get_contents($file_url_s,'r');
        $contents= explode("\n",$content);//explode()函数以","为标识符进行拆分
        unset($contents[0]);
        $data_1 = array(
          'code'            => substr($contents[1],0,16),   //接入代码
          'date'            => strtotime(substr($contents[1],16,8)),//对账日期
          'pay_sum'         => (int)(substr($contents[1],24,6)),//支付总笔数
          'pay_money'       => (float)(substr($contents[1],30,18)),//支付总金额
          'refund_sum'      => (int)(substr($contents[1],48,6)),//退款总笔数
          'refund_money'    => (float)(substr($contents[1],54,18)),//退款总金额
          'sum_procedures'  => (float)(substr($contents[1],72,18)),//总手续费
          'receivable_money'=> (float)(substr($contents[1],90,18)),//应收款金额
        );
        $file->addSummaryEnterprise($data_1);
        unset($contents[1]);
        if(!empty($contents)){
          $data_2 = array();
          foreach($contents as $k=>$v){
       	   $data_2 = array(
       	     'number'         => substr($v,0,8),    //序号
       	     'deal_type'      => (int)(substr($v,8,1)),//交易类型
       	     'op_order_id'    => substr($v,9,20),//平台订单号
       	     'flow_number'    => substr($v,29,32),//停车场接入方交易流水号
       	     'start_date'     => strtotime(substr($v,61,8)),//停车场接入方交易日期
       	     'pay_money'      => (float)(substr($v,69,18)),//交易金额
       	     'poundage'       => (float)(substr($v,87,8)),//手续费
       	     'time'           => strtotime((int)(substr($v,95,8))),//结算日期
       	     'merchant_id'    => (int)(substr($v,103,16)),//商户号
       	     'merchant_name'  => substr($v,119,255),//商户名称
       	     'api_type'       => 2,// 接口(1丰端祥2银联无感)
          );
          }
          $file->addEnterprise($data_2);
        }
       }
    }
    
    
    //农商银行
    public function apiBank(){
    	$file = Loader::model("File","model");
//  	$file_url_s="../../../../../home/sdebank/TRANYGK04000000050".date('Ymd',strtotime("-1 day")).".txt";
    	$file_url_s="../../../../../home/sdebank/TRANYGK0400000005020180710.txt";
    	if(file_exists($file_url_s)){
    		$content = file_get_contents($file_url_s,'r');
            $contents= explode("\n",$content);//explode()函数以","为标识符进行拆分
            if(!empty($contents)){
            	$data_1 =array(
                   'pay_sum'         => (int)(substr($contents[0],0,8)),//支付总笔数
                   'pay_money'       => (float)(substr($contents[0],8,17)),//支付总金额
                );
                $file->addSummaryEnterprise($data_1);
            }
               unset($contents[0]);
            if(!empty($contents)) {
            	$data_2 = array();
            	foreach($contents as $k=>$v) {
            	  if(!empty($v)){
            	  $data_2[] = array(
            	     'time'         => strtotime(substr($v,0,8)),    //银行日期
       	             'flow_number'      => (int)(substr($v,8,12)),//银行流水
       	             'start_date'    => strtotime(substr($v,20,8)),//发起方日期
//     	             'd'    => substr($v,28,20),//发起方流水
       	             'op_order_id'     => substr($v,48,30),//订单编号
//     	             'f'      => substr($v,78,30),//客户编号（发起方唯一健值）
//     	             'g'       => substr($v,108,32),//银行卡号
       	             'pay_money'           => (float)(substr($v,140,17)),//交易金额
//     	             'w'    => substr($v,157,98),//停车位置
//     	             'v'  => substr($v,255,14),//停车开始时间
//     	             'v'  => substr($v,269,14),//停车结束时间
       	             'api_type'       => 3,// 接口(1丰端祥2银联无感)
            	  );
            	  }
            	}
//          	print_r($data_2);die;
//          	$file->addEnterprise($data_2);
            }	
    	}
    }
    
    
    //聚合支付
    public function apiPayment(){
    	$file = Loader::model("File","model");
        $file_url_s = "../../crontab/aq/loadBill".date("Ymd",strtotime("-1 day")).".txt";
//      $file_url_s = "../../crontab/aq/20180524.txt";
        if(file_exists($file_url_s)){
       	 if(filesize($file_url_s) == 0){
       	 	die;
       	 }
         $content = file_get_contents($file_url_s,'r');
         $contents= explode("\n",$content);//explode()函数以","为标识符进行拆分
         	if(!empty($contents)){
	         	$data = array();
	         	foreach($contents as $k=>$v){
	        		$n = explode(",",$v);
		       	 	if(!empty($n[1])){
			       		$data[] = array(
			       	      'time' 		=> strtotime($n[0]),//交易时间
			       	      'pay_money' 	=> (float)($n[1]),//交易金额
			       	      'flow_number' => $n[2],//流水号
			       	      'op_order_id' => $n[3], //交易类型 1支付5退款
			       	      'api_type'    => 4,// 接口(1丰端祥2银联无感)
			        	); 
			        }
	        	}
	//      print_r($data);die;
	       	$file->addEnterprise($data);
	       }
       }
    }
    
    
    //丰瑞祥接口请求生成文件
    public function get_file_info(){
	    $loaDate = time()-3600*24;//昨天的时间
	//  $loaDate = 1524672000;//昨天的时间
	    $Token = "loadBill";//昨天的时间
	    $uniacid = 14;//公众号
	    $postUrl ="http://shop.gogo198.cn/payment/Frx/Frx.php"; 
	    $curlPost = [
	       'Token' => $Token, //停车类型；
	       'loaDate' => $loaDate,
	       'uniacid' => $uniacid,
	    ];
	    $ch = curl_init();//初始化curl
	    curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
	    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
	    curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
	    $data = curl_exec($ch);//运行curl
	    curl_close($ch);
	        
	    return $data;
    }
    //超级管理员修改单边账   到时有加权限
    public function ReviseAccounts(Request $request){
    	$file = Loader::model("File","model");
//  	$api_type = $request->get('api_type');
//  	//商户数据 
//  	$flow_number = $request->get('flow_number');
//  	if($flow_number){
//  		
//  	}
//  	//接口数据
//  	if($api_type){
//  		$api_data = $file->api_data($api_type);
//  	    $gogo_data = $file->gogo_data($api_type);
//  	}
    	$api_data = $file->api_data(3);
    	$gogo_data = $file->gogo_data(3);
    	//如果前面两个都不存在显现昨天所有数据   要自己写了
    	$data = array();
    	for( $i=0 ;$i<=count($api_data)-1;$i++) {
    		$data[$i]['flow_number_1'] = $api_data[$i]['flow_number'];
    		$data[$i]['flow_number_2'] = $gogo_data[$i]['upOrderId'];
    		$data[$i]['pay_time_1'] = $api_data[$i]['time'];
    		$data[$i]['pay_time_2'] = $gogo_data[$i]['pay_time'];
    		$data[$i]['ordersn_1'] = $api_data[$i]['op_order_id'];
    		$data[$i]['ordersn_2'] = $gogo_data[$i]['ordersn'];
    		$data[$i]['pay_money_1'] = $api_data[$i]['pay_money'];
    		$data[$i]['pay_money_2'] = $gogo_data[$i]['pay_account'];
    		$data[$i]['account'] = ($api_data[$i]['pay_money'] == $gogo_data[$i]['pay_account']?'平款':($api_data[$i]['pay_money']<$gogo_data[$i]['pay_account']?'长款':'短款'));
    		//单边账用红色标记
    		$data[$i]['money_color'] = $api_data[$i]['pay_money'] != $gogo_data[$i]['pay_account']?1:0;    		
    	}
//  	print_r($data);die;
    	return view("account/list",[
            'title'=>'银企对账',
            'data' =>$data,
        ]);    	
    }
    
    
    
    //把接口对账发给超级管理员
    public function apiLink(){
    	$file = Loader::model("File","model");
    	//昨天的开始时间
    	$start_time = strtotime(date("Ymd",time()-24*3600));
    	//昨天的结束时间
    	$end_time = $start_time+24*3600-1;
        $start_time = 1524898368;
    	$end_time = 1527245475;
    	$api_type_id = $file->api_type_id($start_time,$end_time);
//  	print_r($api_type_id);die;
//  	//当没有商户停止
    	if(empty($api_type_id)){
    		die;
    	}
    	foreach($api_type_id as $k=>$v){
            //当前商户信息
            $url = "http://shop.gogo198.cn/foll/public/?s=admin/ReviseAccounts&api_type=".$v['api_type'];
            $this->sendEmail($url,"805929498@qq.com");  //超级管理员的邮箱
    	}   	
    }
    
    
    
    //把对账发给商户
    public function businessLink(){
    	$file = Loader::model("File","model");
    	//昨天的开始时间
    	$start_time = strtotime(date("Ymd",time()-24*3600));
    	//昨天的结束时间
    	$end_time = $start_time+24*3600-1;
        $start_time = 1524898368;
    	$end_time   = 1527245475;
    	$business_info = $file->businessID($start_time,$end_time);
    	//当没有商户停止
    	if(empty($business_info)){
    		die;
    	}
    	foreach($business_info as $k=>$v){
            //当前商户信息
            $url = "http://shop.gogo198.cn/foll/public/?s=admin/business&uniacid=".$v['uniacid'];
            $this->sendEmail($url,"805929498@qq.com");  //商户的邮箱
    	}
//  	print_r($business_info);die;    	
    }
    
    
    //商户对账
    public function business(Request $request) {
    	$file = Loader::model("File","model");
    	$uniacid = $request->get('uniacid');
    	if($request->isPost()) {
    		
        	$flow_number = $_POST['flow_number'];
        	$uniacid = $_POST['uniacid'];
        	$flow_number = implode(',',$flow_number);
        	$url = "http://shop.gogo198.cn/foll/public/?s=admin/ReviseAccounts&flow_number=".$flow_number;
        	$this->sendEmail($url,"805929498@qq.com");
        	$this->success("成功",Url("admin/business")."&uniacid=".$uniacid);
//      	print_r($url);die;
     	}
    	//对应接口类型
    	$statusType=['1'=>'丰端祥接口','2'=>'银无感接口','3'=>'农商行接口','4'=>'聚合支付接口'];
    	//昨天的开始时间
    	$start_time = strtotime(date("Ymd",time()-24*3600));
    	//昨天的结束时间
    	$end_time = $start_time+24*3600-1;
        $start_time = 1524898368;
    	$end_time = 1527245475;
    	$data = array();
    	if($uniacid) {
    		
    	  $business_info	= $file->businessInfo($uniacid,$start_time,$end_time);
    	  $apiBusiness_info = $file->apiBusinessInfo($uniacid,$start_time,$end_time);
    	  
    	  if($apiBusiness_info) {
    	  	for( $i=0 ;$i<=count($apiBusiness_info)-1;$i++) {
	    		$data[$i]['flow_number_1']  = $apiBusiness_info[$i]['flow_number'];
	    		$data[$i]['flow_number_2']  = $business_info[$i]['upOrderId'];
	    		$data[$i]['pay_time_1'] 	= $apiBusiness_info[$i]['time'];
	    		$data[$i]['pay_time_2'] 	= $business_info[$i]['pay_time'];
	    		$data[$i]['ordersn_1'] 		= $apiBusiness_info[$i]['op_order_id'];
	    		$data[$i]['ordersn_2'] 		= $business_info[$i]['ordersn'];
	    		$data[$i]['pay_money_1'] 	= $apiBusiness_info[$i]['pay_money'];
	    		$data[$i]['pay_money_2'] 	= $business_info[$i]['pay_account'];
	    		$data[$i]['account'] 		=  ($apiBusiness_info[$i]['pay_money'] == $business_info[$i]['pay_account'] ? '平款': ($apiBusiness_info[$i]['pay_money'] < $business_info[$i]['pay_account'] ? '长款':'短款') );
	    		//单边账用红色标记
	    		$data[$i]['money_color'] 	= $apiBusiness_info[$i]['pay_money'] != $business_info[$i]['pay_account']?1:0;
	    		$data[$i]['api_type']    	= $apiBusiness_info[$i]['api_type'];
    	    }
    	  }
    	  $path = $this->ExcelBusiness($apiBusiness_info,$business_info,$uniacid);
    	}
//  	echo $path;die;
    	return view("account/business_list",[
            'title'=>'商户对账',
            'path'=>$path,
            'uniacid'=>$uniacid,
            'statusType'=>$statusType,
            'data' =>$data,
        ]); 

//  	print_r($data);
//  	set_time_limit(0);

//  	//昨天的开始时间
//  	$start_time = strtotime(date("Ymd",time()-24*3600));
//  	//昨天的结束时间
//  	$end_time = $start_time+24*3600-1;
//      //拿到昨天所胡支付成功的商户ID
//  	$business = $file->businessID($start_time,$end_time);
//  	print_r($business);die;
//  	//昨天没数据为停止
//  	if(empty($business)){
//  		die;
//  	}
//  	foreach($business as $k=>$v){
//  		//当前商户相同数据
//  		$alike_api = $file->alike_api_busin($business[$k]['business_id']);
//  		if(!empty($alike_api)){
//  			$alike_gogo = $file->alike_gogo_busin($business[$k]['business_id']);
//  		    $this->ExcelBusiness($alike_api,$alike_gogo,"P","business");
//  		}
//  		
//          //当前商户不相同数据
//  		$on_alike_api = $file->on_alike_api_busin($business[$k]['business_id']);
//  		if(!empty($on_alike_api)){
//  		    $on_alike_gogo = $file->on_alike_gogo_busin($business[$k]['business_id']);
//  		    $this->ExcelBusiness($on_alike_api,$on_alike_gogo,"D","business");
//  		}
//     echo '<pre>';
//	   dump($on_alike_api);echo '<br>';
//	   dump($on_alike_gogo);
//     echo '成功';
//  	}
    }
    //商户对账   生成Excel文件$api_array api生成的数据,$gogo_array gogo商城生成的数据,$type平账P  单边账D $business_id接口名称和对应的文件夹名
    public function ExcelBusiness($api_array,$gogo_array,$uniacid) {
    	
    	$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
	  	$PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
	  	$PHPSheet->setTitle('对账文件'); //给当前活动sheet设置名称
	  	$PHPSheet->setCellValue('C2','银企数据')
	  	         ->setCellValue('G2','gogo商城数据');
	  	$PHPSheet->setCellValue('A3','订单号')
	  	         ->setCellValue('B3','流水号')
	  	         ->setCellValue('C3','交易金额')
	  	         ->setCellValue('D3','时间')
	  	         ->setCellValue('E3','对账结果')
	  	         ->setCellValue('F3','订单号')
	  	         ->setCellValue('G3','流水号')
	  	         ->setCellValue('H3','交易金额')
	  			 ->setCellValue('I3','时间');	  
         //给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue(‘A1’,’’);
        $long = 0;//长账总数
	  	$short = 0;//短账总数
	  	for( $i=0 ;$i<=count($api_array)-1;$i++) {
	  		
	  		$num = 4+$i;
	  		
	  		$PHPSheet->setCellValue("A".$num,$api_array[$i]['op_order_id'])
	  			 ->setCellValue('B'.$num,$api_array[$i]['flow_number'])
	  			 ->setCellValue('C'.$num,$api_array[$i]['pay_money'])
	  			 ->setCellValue('D'.$num,$api_array[$i]['time'])
	  			 ->setCellValue('E'.$num,$api_array[$i]['pay_money'] == $gogo_array[$i]['pay_account']?'平款':($api_array[$i]['pay_money']<$gogo_array[$i]['pay_account']?"长款":"短款"))
	  			 ->setCellValue('F'.$num,$gogo_array[$i]['ordersn'])
	  			 ->setCellValue('G'.$num,$gogo_array[$i]['upOrderId'])
	  			 ->setCellValue('H'.$num,$gogo_array[$i]['pay_account'])
	  			 ->setCellValue('I'.$num,$gogo_array[$i]['pay_time']);
	  			
	  		//算出长账短账各自的总数  以GOGO为对照  收多的为长款 收少的为短款
	  	    if($api_array[$i]['pay_money']<$gogo_array[$i]['pay_account']){
	  	    	$long++;
	  	    }else{
	  	    	$short++;
	  	    }
	  	}
	  	$PHPSheet->setCellValue('A1','单边账总数:')
	  	         ->setCellValue('B1',count($api_array));
//	  	if($type == 'D'){
//	  		$PHPSheet->setCellValue('C1','长账总数:')
//	  		         ->setCellValue('D1',$long)
//	  		         ->setCellValue('E1','短账总数:')
//	  		         ->setCellValue('F1',$short);
//	  	}
	  	$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
	  	$fileName = $uniacid.date('Ymd',time()).'.xlsx';
	  	$path = "../accounts_excel/business/business".$uniacid;
    	if(!is_dir($path)){
          mkdir($path,0777,true);
        }	
	  	$path=$path."/".$fileName;
	  	if($PHPWriter->save($path)){
	  	  return $path;
	  	}else{
	  	  return $path;
	  	}
	  	
    }
    
    //生成Excel文件$api_array api生成的数据,$gogo_array gogo商城生成的数据,$type平账P  单边账D $name接口名称和对应的文件夹名
    public function Excel($api_array,$gogo_array,$type,$name){
//  	$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
	  	$PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
	  	$PHPSheet->setTitle('对账文件'); //给当前活动sheet设置名称
	  	$PHPSheet->setCellValue('C2','银企数据')
	  	         ->setCellValue('G2','gogo商城数据');
	  	$PHPSheet->setCellValue('A3','订单号')
	  	         ->setCellValue('B3','流水号')
	  	         ->setCellValue('C3','交易金额')
	  	         ->setCellValue('D3','时间')
	  	         ->setCellValue('E3',$type == 'P'?'平账':'单边账')
	  	         ->setCellValue('F3','订单号')
	  	         ->setCellValue('G3','流水号')
	  	         ->setCellValue('H3','交易金额')
	  			 ->setCellValue('I3','时间');	  
         //给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue(‘A1’,’’);
        $long = 0;//长账总数
	  	$short = 0;//短账总数
	  	for( $i=0 ;$i<=count($api_array)-1;$i++) {
	  		
	  		$num = 4+$i;
	  			
	  		$PHPSheet->setCellValue("A".$num,$api_array[$i]['op_order_id'])
	  			 ->setCellValue('B'.$num,$api_array[$i]['flow_number'])
	  			 ->setCellValue('C'.$num,$api_array[$i]['pay_money'])
	  			 ->setCellValue('D'.$num,$api_array[$i]['time'])
	  			 ->setCellValue('E'.$num,$type == 'P'?'平账':($api_array[$i]['pay_money']<$gogo_array[$i]['pay_account']?"长账":"短账"))
	  			 ->setCellValue('F'.$num,$gogo_array[$i]['ordersn'])
	  			 ->setCellValue('G'.$num,$gogo_array[$i]['upOrderId'])
	  			 ->setCellValue('H'.$num,$gogo_array[$i]['pay_account'])
	  			 ->setCellValue('I'.$num,$gogo_array[$i]['pay_time']);
	  		//算出长账短账各自的总数  以GOGO为对照  收多的为长款 收少的为短款
	  	    if($api_array[$i]['pay_money']<$gogo_array[$i]['pay_account']){
	  	    	$long++;
	  	    }else{
	  	    	$short++;
	  	    }
	  	}
	  	$PHPSheet->setCellValue('A1',$type == 'P'?'平账总数:':'单边账总数:')
	  	         ->setCellValue('B1',count($api_array));
	  	if($type == 'D'){
	  		$PHPSheet->setCellValue('C1','长账总数:')
	  		         ->setCellValue('D1',$long)
	  		         ->setCellValue('E1','短账总数:')
	  		         ->setCellValue('F1',$short);
	  	}
	  	$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
	  	$fileName = $name.date('Ymd',time()).$type.'.xlsx';	
	  	$path="../accounts_excel/".$name.'/'.$fileName;
	  	$PHPWriter->save($path);
    }
    //发送电子邮件
	public function sendEmail($url,$toemail){
//      $path = "../accounts_excel/fortune/fortune20180523D.xlsx";
//		$toemail = input('email');//kali20@126.com  805929498@qq.com
//      $toemail = "707319046@qq.com";
		$name = '系统管理员';
		$subject = '对账文件';
		$content = "<a href=".$url.">".$url."</a>";
//		$patfile = $this->getExecl(true);//common.php 文件中的send_mail
        $status = send_mail($toemail,$name,$subject,$content);
		if($status){
			echo '发送成功，请注意查收';
		}else{
			echo '发送失败，请重新发送';
		}
	}
}
