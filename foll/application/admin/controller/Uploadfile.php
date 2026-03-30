<?php
/**
 * @author 赵金如
 * @date   2018-08-06
 * 定时执行下载对账文件
 */
namespace app\admin\controller;
use think\Db;
use Util\data\Redis;
use Util\data\Sysdb;

class Uploadfile// extends Auth
{
	public function __construct()
	{
		$config = [
			'host'	=> '127.0.0.1',
			'port'	=> '6379',
			'auth'	=> '123456',
		];
		$attr = [
			//连接超时时间，redis配置文件中默认为300秒
			'timeout'=>300,
			//选择数据库
			'db_id'=>6,
		];

		$this->redis = null;
		//实例化 Redis 缓存类
		$this->redis = Redis::getINstance($config,$attr);
		$this->rs = $this->redis->getRedis();
		//实例化数据库
		$this->db = new Sysdb;
	}

	//测试用  2018-10-10
	public function test() {

		try {

			// $ddd = json_encode([
			// 	'call' =>'sendTextToFans',
			// 	'touser' =>'ov3-btxV-ENpabbyd0c7grgz5RP4',
			// 	'msg' =>'对账文件下载失败，请处理！',
			// 	'uniacid'=>3
			// ]);
			// var_dump($ddd );
			// $this->SendWechat($ddd);

			// echo '111';

            /**
             * 关联表：
			 * wx :   parking_pay_wxsecret;
			 * 平账表：parking_pay_summary
			 * 出错表：parking_mer_mistake
			 * 订单汇总表：parking_mer_summary();  parking_mer_summary
			 * 平账错误表：parking_check_mistake
			 * // aq
			 * shop.gogo198.cn/foll/public/index.php?s=upload/test&type=1&times=1587620441&project=wx
			 * 重新对账地址
			 * http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/Reconciliations&date=20200826
			 *
			 *1562311481
			 *
			 * 银企对账：使用中  我司与支付企业的对账  用来处理短款长款；
			 * http://shop.gogo198.cn/foll/public/?s=mreconcil/index&type=union
			 * http://shop.gogo198.cn/foll/public/?s=mreconcil/index&type=fagro
			 * http://shop.gogo198.cn/foll/public/?s=mreconcil/index&type=fwechat
			 * http://shop.gogo198.cn/foll/public/?s=mreconcil/index&type=tgpay
			 * http://shop.gogo198.cn/foll/public/?s=mreconcil/index&type=fagro&date=20190704
			 *
			 *
			 * 订单对账
			 * // 顺德
			 * http://shop.gogo198.cn/foll/public/?s=OrderReconcils/AnalySdes&date=20190609
			 * // 微信免密
			 * http://shop.gogo198.cn/foll/public/?s=OrderReconcils/Analywxs&date=20190909
			 * // 聚合支付
			 * http://shop.gogo198.cn/foll/public/?s=Reconcils/Analyaqs&date=20190609
			 * // 银联
			 * http://shop.gogo198.cn/foll/public/?s=OrderReconcils/AnalyUnions&date=20190903
			 *
			 * // 发送邮件
			 * http://shop.gogo198.cn/foll/public/?s=OrderReconcils/test
			 *
			 *商户对账：用于长短款平账  tgpay fwechat  fagro  union
			 * http://shop.gogo198.cn/foll/public/?s=mreconcil/index&type=fwechat
			 *
			 *
			 * // 订单对账：订单对账，我司与伦教停车的对账；
			 * http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/Analyaqs
			 * http://shop.gogo198.cn/foll/public/index.php?s=OrderReconcils/Analywxs
			 * http://shop.gogo198.cn/foll/public/index.php?s=OrderReconcils/AnalyUnions
			 * http://shop.gogo198.cn/foll/public/index.php?s=OrderReconcils/AnalySdes
			 *
             */

            $inp   = input('get.type');
            $times = trim(input('get.times'));// 时间搓
			$times = isset($times) ? $times : strtotime("-1 day");
			$project = input('get.project');
            if (!empty($inp)) {
                switch ($inp) {
                    case '1':
						//微信免密对账下载
						if($project == 'wx')
						{
							$res1 = $this->Paywx($times);
							print_r($res1.'wx');
						}
                        // 聚合支付
						if($project == 'aq')
						{
							$res1 = $this->Payaq($times);
							print_r($res1.'aq');
						}

                        // 银联无感
						if($project == 'un')
						{
							$res1 = $this->ftp_upload($times);
							print_r($res1.'un');
						}

                        // 顺德农商
						if($project == 'sd')
						{
							$res1 = $this->sdebank($times);
							print_r($res1.'sd');
						}
                        
                        break;

                    case '2':

						if($project == 'wx')
						{
							$ins = $this->Analywx($times);//0.006
							print_r($ins.'wx');
						}

                        //聚合支付对账文件
						if($project == 'aq')
						{
							$ins = $this->Analyaq($times);//0.006
							print_r($ins.'aq');
						}
                        

                        if($project == 'un')
						{
							$ins = $this->AnalyUnion($times);//0.0032
							print_r($ins.'un');
						}

						if($project == 'sd')
						{
							$ins = $this->AnalySde($times);
							print_r($ins.'sd');
						}

                        
                        break;
                    case '3':
                        //微信免密支付对账
						if($project == 'wx')
						{
							$rs = $this->Analywxs($times);
							print_r($rs.'wx');
						}

						// 聚合支付
						if($project == 'aq')
						{
							$rs = $this->Analyaqs($times);
							print_r($rs.'aq');
						}

						// 银联无感
						if($project == 'un')
						{
							$rs = $this->AnalyUnions($times);
							print_r($rs.'un');
						}

                        // 顺德农商
						if($project == 'sd')
						{
							$rs = $this->AnalySdes($times);
							print_r($rs.'sd');
						}
                        
                        
                        break;
                }
            } else {
                echo '请传参数';
            }

			echo $inp . '<>' . $times;
			
			

        }catch (\Exception $e) {
            echo $e->getMessage();
        }

	}


	/**
	 * 对账文件下载
	 * 第一步
	 */
	public function index() {
		//聚合支付对账下载
		$res = $this->Payaq();
		echo $res ? '下载成功！<br>':'下载失败!<br>';
		sleep(5);
		//微信免密对账下载
		$res1 = $this->Paywx();
		echo $res1 ? '下载成功！<br>':'下载失败!<br>';
		sleep(5);
		//银联无感对账文件下载
		$res2 = $this->ftp_upload();
		echo $res2 ? '下载成功！<br>':'下载失败!<br>';
		sleep(5);
		//顺德农商银行对账下载
		$res3 = $this->sdebank();
		echo $res3 ? '下载成功！<br>':'下载失败!<br>';
	}

	/**
	 * 第二步：
	 * 解析文件并存入数据库
	 */
	public function Analysis() {
		//聚合支付对账文件
		$this->Analyaq();//0.006
		echo '<hr>';
		// 1538393873 1538480273  1538566673	1538653073	1538739473	1538825873	1538912273
		$this->Analywx();//0.006
		echo '<hr>';
		$this->AnalyUnion();//0.0032
		echo '<hr>';
		$this->AnalySde();
	}


	/**
	 * 获取数据库中的数据与上游对账单对账
	 * 第三步
	 */
	public function Reconciliation() {
		//聚合支付对账文件   测试聚合支付
		$this->Analyaqs();
		echo 'Aq_ok1';
		echo '<hr>';
		//微信免密支付对账
		$this->Analywxs();
		echo 'Wx_ok2';
		echo '<hr>';
		//银联无感支付对账
		$this->AnalyUnions();//'1536634801'  2018-09-11
		echo 'Union_ok3';
		echo '<hr>';
		$this->AnalySdes();
		echo 'Sde_ok4';
	}


	//************************************ 对账单入库  结束 *************************************
	//解析聚合支付对账文件并入库
	private function Analyaq($time = 0) {
		$day = $time > 0 ? date('Ymd',$time):date('Ymd',strtotime('-1 day'));
		//对账文件路径
		$path = "/www/web/default/crontab/aq/{$day}.txt";
		if(file_exists($path) && (filesize($path) != 0)) {//文件存在，并且不为空

			$files    = file_get_contents($path,'r');
        	$filesArr = explode("\n",$files);//explode() 先把数据按行拆分
        	foreach($filesArr as $key=>$v){

        		if(empty($v)) {
        			continue;
        		}

        		$arrs = explode(",",$v);
        		$bodyArr[$key] = [
        			'pay_status'	=>trim($arrs[0]),//支付状态
        			'pay_time'		=>strtotime(trim($arrs[1])),//支付时间
        			'pay_money'		=>sprintf("%.2f",trim($arrs[2])),//支付金额
        			'order_id'		=>trim($arrs[3]),//上游订单号
        			'low_order_id'	=>trim($arrs[4]),//平台订单号
        			'refund_money'	=>sprintf("%.2f",trim($arrs[5])),//退款金额
        			'date'			=>$day,
        		];

        	}
        	//释放数组资源
        	unset($arrs);
        	unset($filesArr);

        	if(!empty($bodyArr)) {
        		//插入汇总表
        		/*$totalMoney  = Db::name('parking_pay_summary')->insert($headerArr);
        		//插入数据到聚合对账表中*/
        		$insertUnion = Db::name('parking_pay_poly')->insertAll($bodyArr);
        	}
        	echo '聚合支付写入数据库完成<br>';

		} else {
			$bodyArr = [
				'pay_status'	=>0,//支付状态
				'pay_time'		=>0,//支付时间
				'pay_money'		=>0.00,//支付金额
				'order_id'		=>0,//上游订单号
				'low_order_id'	=>0,//平台订单号
				'refund_money'	=>0,//退款金额
				'date'			=>$day,
			];
			$insertUnion = Db::name('parking_pay_poly')->insert($bodyArr);
			echo '聚合支付暂无对账数据  对账文件时间：'.$day;
		}
	}



	//解析微信免密对账文件入库
	private function Analywx($time=0) {
		$day = $time > 0 ? date('Ymd',$time):date('Ymd',strtotime('-1 day'));
		//对账文件路径
		$path = '/www/web/default/crontab/wx/loadBill'.$day.'.txt';
		if(file_exists($path) && (filesize($path) != 0)) {//文件存在，并且不为空

			$files = file_get_contents($path,'r');
			//变成新的数组
			$fileArr = explode("\n",$files);//以回车换行分割
			if(is_array($fileArr) && !empty($fileArr)) {
				$bodyArr = [];
	        	foreach($fileArr as $key=>$v){
	        		//跳过空数据
	        		if(empty($v)){continue;}

	        		$temp  = rtrim($v,',');
	        		$temps = explode(',',$temp);
	        		$bodyArr[$key] = array(
			       	    'order_id'      	=> trim($temps[0]),    					//订单号
			       	    'pay_status'     	=> trim($temps[1]),						//交易类型    1支付5退款
			       	    'pay_money'       	=> sprintf("%.2f",(trim($temps[2])/100)),//金额
			       	    'pay_time'   		=> strtotime(trim($temps[3])),			//交易时间
			       	    'pay_type'   		=> trim($temps[4]), 					//支付方式
			       	    'merchant_charges'  => sprintf("%.2f",(trim($temps[5])/100)),//商户手续费
			       	    'original_no'       => !empty($temps[6])? trim($temps[6]):'',//原始订单号
			       	    'low_order_id'      => trim($temps[7]),//原始订单号
			       	    'date'				=> $day,
		          	);
		          	//499423626043326464,1,400,2018-10-10 03:31:16,12,2,,G9919820181009278314542
		          	/*'484423460722835456,上游订单号
		          	1,		交易类型：1支付，5退款
		          	750,	交易金额：分为单位
		          	2018-08-29 18:05:58,	交易时间
		          	12,	商户手续费：
		          	5,	支付方式';*/
	        	}
	        	unset($temp);
	        	unset($temps);
        		unset($fileArr);
			}

			if(!empty($bodyArr)) {
        		//插入汇总表
        		/*$totalMoney  = Db::name('parking_pay_summary')->insert($headerArr);
        		//插入数据到聚合对账表中*/
        		$insertUnion = Db::name('parking_pay_wxsecret')->insertAll($bodyArr);
        	}
			echo '微信免密写入数据库完成<br>';
		} else {

			$bodyArr = array(
	       	    'order_id'      	=> 0,    				//订单号
	       	    'pay_status'     	=> 0,					//交易类型    1支付5退款
	       	    'pay_money'       	=> 0.00,				//金额
	       	    'pay_time'   		=> 0,			        //交易时间
	       	    'pay_type'   		=> 0, 					//支付方式
	       	    'merchant_charges'  => 0.00, 				//商户手续费
	       	    'original_no'       => 0,//原始订单号
	       	    'low_order_id'      => 0,//订单号
	       	    'date'				=>$day,
          	);
          	$insertUnion = Db::name('parking_pay_wxsecret')->insert($bodyArr);
			echo '微信免密暂无对账数据';
		}
	}


	//解析银联无感对账文件入库
	private function AnalyUnion($time=0) {

		$day = $time > 0 ? date('Ymd',$time):date('Ymd',strtotime('-1 day'));
		//对账文件路径
		$path = "/www/web/default/crontab/wg/7000000000000049{$day}01.txt";
		if(file_exists($path) && (filesize($path) != 0)) {//文件存在，并且不为空
			$files    = file_get_contents($path,'r');
        	$filesArr = explode("\n",$files);//explode()函数以","为标识符进行拆分
        	array_shift($filesArr);//去掉数组第一个元素

        	$headerArr = array(
	          	'code'            => substr($filesArr[0],0,16),   			//接入代码
	          	'date'            => substr($filesArr[0],16,8),				//对账日期
	          	'pay_sum'         => (int)(substr($filesArr[0],24,6)),		//支付总笔数
	          	'pay_money'       => (float)(substr($filesArr[0],30,18)/100),//支付总金额 单位为分
	          	'refund_sum'      => (int)(substr($filesArr[0],48,6)),		//退款总笔数
	          	'refund_money'    => (float)(substr($filesArr[0],54,18)/100),//退款总金额  单位为分
	          	'fee_sum'  		  => (float)(substr($filesArr[0],72,18)/100),//总手续费  单位为分
	          	'total_money'	  => (float)(substr($filesArr[0],90,18)/100),//应收款金额  单位为分
	          	'pay_type'	      => 'union',
	        );
	        //去掉数组第一个元素
	        array_shift($filesArr);
	        if(!empty($filesArr[0])) {
	        	$bodyArr = [];
	        	foreach($filesArr as $key=>$v) {
	        		//退款的订单不写入表
	        		$status = (int)(substr($v,8,1));
	        		if(empty($v) || $status==2) {
	        			continue;
	        		}

	        		$bodyArr[$key] = array(
			       	    'number'         => (int)substr($v,0,8),    		//序号
			       	    'pay_status'     => (int)(substr($v,8,1)),			//交易类型    1支付，2退款
			       	    'order_id'       => substr($v,9,20),				//平台订单号
			       	    'low_order_id'   => trim(substr($v,29,32)),			//停车场接入方交易流水号
			       	    's_date'   		 => substr($v,61,8), 				//停车场接入方交易日期
			       	    'pay_money'      => (float)(substr($v,69,18)/100),  //交易金额
			       	    'pay_fee'        => (float)(substr($v,87,8)/100),	//手续费
			       	    'e_date'         => (int)(substr($v,95,8)),			//结算日期
			       	    'merchant_no'    => (int)(substr($v,103,16)),		//商户号
			       	    'date'			 => $day,
		          	);
	        	}
	        	//插入数据到银联无感对账表中*/
	        	$insertUnion = Db::name('parking_pay_unionsecret')->insertAll($bodyArr);
	        } else {
	        	$bodyArr = array(
		       	    'number'         => 0,    		//序号
		       	    'pay_status'     => 1,			//交易类型    1支付，2退款
		       	    'order_id'       => 0,				//平台订单号
		       	    'low_order_id'   => 0,			//停车场接入方交易流水号
		       	    's_date'   		 => 0, 				//停车场接入方交易日期
		       	    'pay_money'      => 0.00,  //交易金额
		       	    'pay_fee'        => 0,	//手续费
		       	    'e_date'         => 0,			//结算日期
		       	    'merchant_no'    => '1000000000000007',		//商户号
		       	    'date'			 => $day,
	          	);
	          	$insertUnion = Db::name('parking_pay_unionsecret')->insert($bodyArr);
	        }
        	unset($headerArr);
    		unset($filesArr);
    		unset($bodyArr);
			echo '银联无感写入数据库完成<br>';

		} else {

			$bodyArr = array(
	       	    'number'         => 0,    		//序号
	       	    'pay_status'     => 1,			//交易类型    1支付，2退款
	       	    'order_id'       => 0,				//平台订单号
	       	    'low_order_id'   => 0,			//停车场接入方交易流水号
	       	    's_date'   		 => 0, 				//停车场接入方交易日期
	       	    'pay_money'      => 0.00,  //交易金额
	       	    'pay_fee'        => 0,	//手续费
	       	    'e_date'         => 0,			//结算日期
	       	    'merchant_no'    => '1000000000000007',		//商户号
	       	    'date'			 => $day,
          	);
          	$insertUnion = Db::name('parking_pay_unionsecret')->insert($bodyArr);
			echo '银联无感暂无对账数据';
		}
	}

	//解析顺德农商免密对账文件入库
	private function AnalySde($time = 0){
		$day = $time>0?date('Ymd',$time):date('Ymd',strtotime('-1 day'));
		//对账文件路径
		$path = "/home/sdebank/TRANYGK04000000050{$day}.txt";
		if(file_exists($path) && (filesize($path) != 0)) {//文件存在，并且不为空
			$content = file_get_contents($path,'r');
            $filesArr= explode("\n",$content);//explode()函数以","为标识符进行拆分
            if(!empty($filesArr[0])) {
            	$headerArr = array(
                   'pay_sum'         => (int)(substr($filesArr[0],0,8)),//支付总笔数
                   'pay_money'       => (float)(substr($filesArr[0],8,17)),//支付总金额
                );
            }

            //去掉数组第一个元素
	        array_shift($filesArr);
	        if(!empty($filesArr[0])) {

	        	$bodyArr = [];
            	foreach($filesArr as $key=>$v) {

            	  	if(empty($v)){continue;}

            	  	$bodyArr[$key] = array(
	            	    'bank_date'    => substr($v,0,8),    			//银行日期
	       	            'bank_orderid' => trim((int)(substr($v,8,12))),		//银行流水
	       	            'pay_orderid'  => trim(substr($v,28,20)),				//发起方流水
	       	            'pay_ordersn'  => trim(substr($v,48,30)),				//订单编号
	       	            'pay_money'    => (float)(substr($v,140,17)),	//交易金额
	       	            's_time'  	   => strtotime(substr($v,255,14)),//停车开始时间
	       	            'e_time'  	   => strtotime(substr($v,269,14)),//停车结束时间
	       	            'date'		   => $day,
            	  	);
            	}
            	//插入数据到聚合对账表中*/
            	$insertUnion = Db::name('parking_pay_sdesecret')->insertAll($bodyArr);

	        } else {

	        	$bodyArr = array(
	        	    'bank_date'    => $day,//银行日期
	   	            'bank_orderid' => 0,//银行流水
	   	            'pay_orderid'  => 0,//发起方流水
	   	            'pay_ordersn'  => 0,//订单编号
	   	            'pay_money'    => 0.00,	//交易金额
	   	            's_time'  	   => 0,//停车开始时间
	   	            'e_time'  	   => 0,//停车结束时间
	   	            'date'		   => $day,
	    	  	);

	    	  	$insertUnion = Db::name('parking_pay_sdesecret')->insert($bodyArr);
	        }

            if(!empty($bodyArr)) {
        		//插入汇总表
        		/*$totalMoney  = Db::name('parking_pay_summary')->insert($headerArr);
        		//插入数据到聚合对账表中*/
        		//$insertUnion = Db::name('parking_pay_sdesecret')->insertAll($bodyArr);
        	}

        	unset($headerArr);
    		unset($filesArr);
    		unset($bodyArr);

            /*'20180820		银行日期8
                 5753216	银行流水12
            20180820		发起方日期8
           	20180820163700253617	发起方流水20
           	G99198101570223660201808201739	订单编号30
           	u4795575b20b89aed6721528871066	客户编号30
           	6223228801818522                银行卡号32
           	             4.50		交易金额17
           	�㶫ʡ��ɽ��˳�����׽ֵ̽����´����������ί�������·99��	停车位置98
           	20180820043634		驶入时间14
           	20180820053634		驶离时间14';*/

			echo '顺德农商写入数据库完成<br>';
		} else {

			$bodyArr = array(
        	    'bank_date'    => 0,    			//银行日期
   	            'bank_orderid' => 0,		//银行流水
   	            'pay_orderid'  => 0,				//发起方流水
   	            'pay_ordersn'  => 0,				//订单编号
   	            'pay_money'    => 0.00,	//交易金额
   	            's_time'  	   => 0,//停车开始时间
   	            'e_time'  	   => 0,//停车结束时间
   	            'date'		   => $day,
    	  	);
    	  	$insertUnion = Db::name('parking_pay_sdesecret')->insert($bodyArr);
			echo '顺德农商免密暂无对账数据';
		}
	}
	//************************************ 对账单入库  结束 *************************************



	//************************************ 对账下载  开始 *************************************
	/**
	 * 聚合支付对账下载
	 */
	private function Payaq($time=0) {
		$url = 'http://shop.gogo198.cn/payment/wechat/loadBill.php';
		$day = $time >0 ? date('Ymd',$time) : date('Ymd',strtotime('-1 day'));
		//对账文件路径
		$path = "/www/web/default/crontab/aq/{$day}.txt";
		if(!file_exists($path)) {
			//$res = $this->GetUrl($url.'?times='.$time);
			$res = $this->GetUrl($url);
			return $res;
		}
		return true;
	}

	/**
	 * 微信免密对账文件下载
	 */
	private function Paywx($times=0) {

		$url = 'http://shop.gogo198.cn/payment/Frx/upload.php';
		$timestr = $times > 0 ? $times : strtotime(date('Y-m-d',strtotime('-1 day')));
		$sendArr = [
			'Token' =>  'loadBill', //停车类型；
			'loaDate'=> $timestr,//查询日期
			'uniacid'=> 14,//公众号ID
		];
		$day  = date('Ymd',$timestr);
		//对账文件路径
		$path = "/www/web/default/crontab/wx/loadBill{$day}.txt";
		if(!file_exists($path)) {

			$res = $this->PostUrl($url,$sendArr);

			$res = json_decode($res,true);
			if($res['status'] <= 0){
				//发送提醒
				$temp['openid']	  = 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg';
				$temp['url']	  = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/Reconciliations&date='.$day;
				$temp['first']	  = '您好，'.$day.'微信免密对账文件下载失败';
				$temp['money']    = '0.00元';
				$temp['nums']  = '0条';
				$temp['type']  = '微信免密';
				$temp['dates'] = date('Y-m-d H:i:s',time());
				$temp['remark']   = '点击重新下载账单文件对账';
				$this->sendErrorTempl($temp);
				return false;
			}

			return '获取成功';
		} else {
            return '已存在';
		}



		//return true;
	}


	/**
	 * 银联无感对账文件下载
	 * 从ftp下载文件
	 * 2018-08-06
	 */
	private function ftp_upload($time=0) {

		$host  = '183.62.232.62:21';
		$uname = 'qs';
		$upwd  = 'ylink!1qaz';
		$port  = 21;
		//对账日期
		//$day = date("Ymd",strtotime("-1 day"));
		$day = $time > 0 ? date('Ymd',$time):date('Ymd',strtotime('-1 day'));
		//$day = date('Ymd',1527663712);
		//本地保存路径    对账文件路径
		$fileSave = "/www/web/default/crontab/wg/7000000000000049{$day}01.txt";
		//ftp 路径
		$path     = "/checkFile/access/7000000000000049/{$day}/7000000000000049{$day}01.TXT";
		//$path     = "/checkFile/access/7000000000000049/{$day}/7000000000000049{$day}01ArrearPay.TXT";

		$curl = curl_init();
		curl_setopt($curl,CURLOPT_URL,"ftp://{$host}/{$path}");
		curl_setopt($curl,CURLOPT_HEADER,0);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,0);
		//设置超时
		curl_setopt($curl,CURLOPT_TIMEOUT,2000);
		//设置用户名密码
		curl_setopt($curl,CURLOPT_USERPWD,"{$uname}:{$upwd}");

		if(file_exists($fileSave)){
			return true;
		}

		//创建并以读写方式打开
		$outfile = fopen($fileSave,'x+');
		curl_setopt($curl,CURLOPT_FILE,$outfile);

		$rtn = curl_exec($curl);
		if(curl_errno($curl)){
			witeLog('Curl error: '.curl_error($curl));
		}
		//关闭文件流
		fclose($outfile);

		curl_close($curl);
		if($rtn == 1){
			return true;
		} else {
			unlink($fileSave);
			return false;
		}
	}


	/**
	 * 顺德农商对账文件下载
	 * 文件存在/home/sdebank/下
	 * 不存在则重新获取
	 */
	private function sdebank() {
		//对账日
		$day     = date("Ymd",strtotime("-1 day"));
		$timestr = strtotime(date('Y-m-d',strtotime('-1 day')));
		//对账文件路径  //对账文件路径
		$path = "/home/sdebank/TRANYGK04000000050{$day}.txt";
		if(!file_exists($path)){//如果文件不存在则手动获取
			$data = [
				'Token'   => 'Reconciliation',
				'OldDate' => $timestr,
			];

			$url = 'http://shop.gogo198.cn/payment/agro/Fagro.php';
			$result = $this->PostUrl($url,$data);
			return true;
		}
		return true;
	}
	//************************************ 对账下载  结束 *************************************




	//************************************ 对账文件与数据库数据对账  开始 *************************************
	//解析聚合支付对账文件并对账
	private function Analyaqs($time = 0) {

		$keyaq = 'aq';//上游数据
		$keysq = 'sq';//本地数据
		//释放缓存
		$this->rs->del($keyaq);
		$this->rs->del($keysq);
		//声明变量
        //$bodyHeard = [];

		$day = $time > 0 ? date('Ymd',$time):date('Ymd',strtotime('-1 day'));
		//aq:聚合支付，wx:微信免密，union:银联无感，sde:顺德农商
		$sum = Db::name('parking_pay_summary')->field('sid')->where(['date'=>$day,'pay_type'=>'aq'])->find();
		if(!empty($sum)) {
			//当日无数据，
			$info['payMoney'] = '0.00';
			$info['result']   = '昨日账单已对';
			$this->CheckOk('tgpay','no',$info);
			return false;
		}

		//对账文件路径
		$path = "/www/web/default/crontab/aq/{$day}.txt";
		$MoneyCount = 0;
		if(file_exists($path) && (filesize($path) != 0)) {//文件存在，并且不为空
			$files    = file_get_contents($path,'r');
        	$filesArr = explode("\n",$files);//explode() 先把数据按行拆分
        	//获取对应key的缓存数据
        	$ret = $this->rs->sMembers($keyaq);
        	//汇总数据表
        	$headerArr=[
        		'code'	 =>'101570223660',
        		'date'	 =>$day,
        		'pay_sum'=>(count($filesArr)-1),
        	];
        	$refund_money = 0;//退款总额
        	$refund_sum   = 0;//退款总条数
        	foreach($filesArr as $key=>$v) {
        		if(empty($v)) {continue;}
        		$arrs = explode(",",$v);

        		//计算支付总金额
        		$MoneyCount += trim($arrs[2]);

        		if($arrs[5] > 0) {
        			$refund_money += trim($arrs[5]);
        			$refund_sum   += 1;
        		}

        		$bodyArr[$key] = [
        			//'pay_time'		=>strtotime(trim($arrs[1])),//支付时间
        			'pay_account'	=>sprintf("%.2f",trim($arrs[2])),//支付金额
        			'upOrderId'		=>trim($arrs[3]),//上游订单号
        			'ordersn'		=>trim($arrs[4]),//平台订单号
        		];

        		$temp = json_encode($bodyArr[$key]);
        		//写入缓存   数据为空就写入
        		if(empty($ret)) {
        			$this->rs->sAdd($keyaq,$temp);
        		}
        	}

        	$MoneyCount   = sprintf("%.2f",$MoneyCount);//交易总额
        	$refund_money = sprintf("%.2f",$refund_money);//退款总额
        	$pay_money	  = sprintf("%.2f",($MoneyCount-$refund_money));//实际交易金额= （交易总额-退款总额）

        	$headerArr['total_money'] 	= $MoneyCount;//交易总额
        	$headerArr['refund_money'] 	= $refund_money;//退款总额
        	$headerArr['refund_sum'] 	= $refund_sum;//退款总笔数
        	$headerArr['pay_money'] 	= $pay_money;//实际交易金额  = （交易总额-退款总额）
        	$headerArr['fee']	        = 0.006;	 //费率
        	$headerArr['pay_fee']		= sprintf("%.2f",($pay_money*0.006));//手续费
        	$headerArr['pay_type']  	= 'aq';

        	//释放数据
        	unset($filesArr);
        	//unset($bodyHeard);
        	unset($arrs);

			//查询条件
			$where = [
				'a.pay_type'	=>[['eq','wechat'],['eq','alipay'],'or'],//支付类型为wechat 或 alipay
				//'a.pay_time'	=>[['egt',strtotime($day)],['elt',(strtotime($day)+86399)]],//大于等于开始时间，小于等于结束时间
				//'a.pay_time'	=>['between',[strtotime($day),(strtotime($day)+86399)]],//大于等于开始时间，小于等于结束时间  时间计算
				'a.pay_status'  =>1,
				'a.upOrderId'	=>['neq',' '],//上级订单号不能为空
			];
			//获取对账日的平台订单数据
			$polyArr = [];
			$polyMoney = 0;
			$poly = Db::name('foll_order')->alias('a')->join('parking_order b','a.ordersn=b.ordersn','LEFT')->whereTime('a.pay_time','between',[strtotime($day),(strtotime($day)+86399)])->where($where)->field(['a.pay_time,a.pay_account,a.upOrderId,a.ordersn,a.RefundMoney,a.IsWrite,a.ref_auto,b.charge_type'])->select();
			if(!empty($poly)) {//平台没有当日订单，退出
				/*echo '聚合支付昨日订单数据已对';
				//当日无数据，
				$info['payMoney'] = '0.00';
				$info['result']   = '没有查询到数据';
				$this->CheckOk('tgpay','no',$info);
				return false;*/

				foreach($poly as $key=>$val) {
					//预付费
					if(($val['charge_type'] == 0) && (($val['ref_auto'] == 2) || ($val['IsWrite'] == 103))) {
						$polyArr[$key]['pay_account'] = sprintf("%.2f",($val['pay_account'] + $val['RefundMoney']));
					} else {
						$polyArr[$key]['pay_account'] = $val['pay_account'];
					}
					//$polyArr[$key]['pay_time'] = $poly[$key]['pay_time'];
					$polyArr[$key]['upOrderId']= $val['upOrderId'];
					$polyArr[$key]['ordersn']  = $val['ordersn'];
				}

				//将数据库中查到的数据进行格式处理
				$ret = $this->rs->sMembers($keysq);
				foreach($polyArr as $k=>$v) {
					//本地数据总金额
					$polyMoney += $v['pay_account'];
		    		$temp = json_encode($v);
		    		//写入缓存	 	将平台数据写入缓存
		    		if(empty($ret)) {
		    			//写入缓存中
		    			$this->rs->sAdd($keysq,$temp);
		    		}
				}

			}


			/**
			 * 步骤说明：11
			 * $keyaq 	上级流水数据
			 * $keysq	本地流水数据
			 * 如果数据相等：平账
			 * 如果本地大于上游数据：平台长款
			 * 如果上游数据大于本地：上游短款
			 */
			//计算差额
			$checkMoney = $MoneyCount-$polyMoney;
			//获取缓存数据
			$numaq = $this->rs->scard($keyaq);//上游数据
			$numsq = $this->rs->scard($keysq);//本地数据
			$msg   = '平账';
			//数据相等为平账
			if($numaq == $numsq) {

				$msg   = '平账';

			} else if($numaq < $numsq) {

				//数据本地大于上游数据为：平台长款
				$msg   = '长款';
				//以平台数据为准，对比上游数据
				$sq = $this->rs->sDiff($keysq,$keyaq);
				if(!empty($sq)) {
					//解析数据
					foreach($sq as $k=>$v) {
						$sq[$k] = json_decode($v,true);
					}

					$errs = null;
					foreach($sq as $k=>$v) {
						//组装数据条件
						$where = ['upOrderId' => $v['upOrderId']];//上级订单号不能为空
						//获取对账日的平台订单数据
						$polys = Db::name('pay_old')->field('update_time,payMoney,upOrderId,ordersn')->where($where)->find();
						if(empty($poly)) {
							$info['payMoney'] = $MoneyCount;
							$info['result']   = '没有查询到数据';
							$this->CheckOk('tgpay','no',$info);
							echo '没有查询到数据';
							return false;
						}

						//循环遍历差错池中的数据出错原因
						foreach($v as $kk=>$vv) {
							//检查差错原因
							if(!in_array($vv,$polys)) {
								switch($kk) {
									case 'pay_time':
										$errs = '平台支付时间错误';
									break;
									case 'pay_account':
										$errs = '平台交易金额有误';
									break;
									case 'upOrderId':
										$errs = '平台上游订单号有误';
									break;
									case 'ordersn':
										$errs = '平台订单号有误';
									break;
								}
							}
						}

						//出错数据所属上下级
						$sq[$k]['type'] 	 = 'tgpay';
						$sq[$k]['data_type'] = 'down';
						$sq[$k]['msg'] 		 = '长款';
						$sq[$k]['checkMoney'] = sprintf("%.2f",$checkMoney);
						$sq[$k]['date'] 	  = $day;
						//获取对账日的平台订单数据
						$up = [
							'r_state'=>'errors',
							//错误原因赋值
							'msg'	 => $errs
						];
						$where = ['order_id' => $v['upOrderId']];//上级订单号不能为空
						//更新数据
						Db::name('parking_pay_poly')->where($where)->update($up);
					}
					//批量写入差错表
					Db::name('parking_check_mistake')->insertAll($sq);

					$info['payMoney'] = $MoneyCount;
					$info['result']   = $msg;
					//yes  有数据，no 没有数据
					$this->CheckOk('tgpay','yes',$info);
				}

			} else if($numaq > $numsq) {//上游数据大于本地 = 短款
				//数据本地小于上游数据为：上游短款
				$msg   = '短款';
				//上游数据 》 本地数据 = 短款
				$aq = $this->rs->sDiff($keyaq,$keysq);
				if(!empty($aq)) {
					//解析数据
					foreach($aq as $k=>$v) {
						$aq[$k] = json_decode($v,true);
					}

					//更新数据写入差错表
					foreach($aq as $k=>$v) {
						$aq[$k]['type'] 	 = 'tgpay';
						$aq[$k]['data_type'] = 'upper';
						$aq[$k]['msg'] 	     = '短款';
						$aq[$k]['checkMoney'] = sprintf("%.2f",$checkMoney);
						$aq[$k]['date'] 	  = $day;
					}
					//批量写入差错表
					Db::name('parking_check_mistake')->insertAll($aq);
				}
			}

			$headerArr['msg'] = $msg;
			//写入汇总数据
	        $insertUnion = Db::name('parking_pay_summary')->insert($headerArr);
			//删除释放数据
			unset($headerArr);

			//释放缓存
			$this->rs->del($keyaq);
			$this->rs->del($keysq);

			//没有对账数据
			$info['payMoney'] = $MoneyCount;
			$info['result']   = $msg;
			$this->CheckOk('tgpay','yes',$info);

			if($msg == '平账') {
				//前往订单对账
				$this->GetUrl('http://shop.gogo198.cn/foll/public/index.php?s=OrderReconcils/Analyaqs&date='.$day);
			}

		} else { // 没有对账数据

			//查询条件
			$where = [
				'a.pay_type'	=>[['eq','wechat'],['eq','alipay'],'or'],//支付类型为wechat 或 alipay'a.pay_status'  =>1,
				'a.upOrderId'	=>['neq',' '],//上级订单号不能为空
			];
			//获取对账日的平台订单数据
			$polyArr = [];
			$polyMoney = 0;
			$poly = Db::name('foll_order')->alias('a')->join('parking_order b','a.ordersn=b.ordersn','LEFT')->whereTime('a.pay_time','between',[strtotime($day),(strtotime($day)+86399)])->where($where)->field(['a.pay_time,a.pay_account,a.upOrderId,a.ordersn,a.RefundMoney,a.IsWrite,a.ref_auto,b.charge_type'])->select();
			if(!empty($poly)) {//有数据标记为长款

				foreach($poly as $key=>$val) {
					//预付费
					if(($val['charge_type'] == 0) && (($val['ref_auto'] == 2) || ($val['IsWrite'] == 103))) {
						$polyMoney += sprintf("%.2f",($val['pay_account'] + $val['RefundMoney']));
						$polyArr[$key]['pay_account'] = sprintf("%.2f",($val['pay_account'] + $val['RefundMoney']));

					} else {

						$polyArr[$key]['pay_account'] = $val['pay_account'];
						$polyMoney += $val['pay_account'];
					}
					$polyArr[$key]['pay_time'] 		= $poly[$key]['pay_time'];
					$polyArr[$key]['upOrderId']		= $val['upOrderId'];
					$polyArr[$key]['ordersn']  		= $val['ordersn'];
					$polyArr[$key]['type']  		= 'tgpay';
					$polyArr[$key]['data_type']		= 'down';
					$polyArr[$key]['msg']  			= '长款';
					$polyArr[$key]['date']  		= $day;
					$polyArr[$key]['checkMoney']  	= -$polyMoney;
				}

				Db::name('parking_check_mistake')->insertAll($polyArr);

				//没有对账数据
				$info['payMoney'] = -$polyMoney;
				$info['result']   = '长款';
				$this->CheckOk('tgpay','yes',$info);

			} else {//没有数据标记为平账

				//汇总数据表
				$headerArr['code'] 			='101570223660';
				$headerArr['date']	 		= $day;
				$headerArr['pay_sum']		= 0;
				$headerArr['pay_money'] 	= 0;
				$headerArr['total_money'] 	= 0;
				$headerArr['fee']	        = 0.006;
				$headerArr['pay_fee']		= 0;
				$headerArr['pay_type']  	= 'aq';
				$headerArr['msg'] 			= '平账';
				Db::name('parking_pay_summary')->insert($headerArr);

				echo '聚合支付昨日订单数据已对1';
				//没有对账数据
				$info['payMoney'] = $MoneyCount;
				$info['result']   = '昨日无交易';
				$this->CheckOk('tgpay','no',$info,$day);
				//前往订单对账
				$this->GetUrl('http://shop.gogo198.cn/foll/public/index.php?s=OrderReconcils/Analyaqs&date='.$day);
			}

		}

	}


	//解析微信免密支付对账文件并入库
	private function Analywxs($time = 0) {

        /*$day = $time > 0 ? date('Ymd',$time):date('Ymd',strtotime('-1 day'));
        //查询条件
        $where = [
            'pay_type'	 =>['eq','Fwechat'],//支付类型为wechat 或 alipay
            'pay_time'   =>[['egt',strtotime($day)],['elt',(strtotime($day)+86399)]],//大于等于开始时间，小于等于结束时间
            'pay_status' =>1,
            'upOrderId'	 =>['neq',' '],//上级订单号不能为空
        ];

        //获取对账日的平台订单数据	 查询平台数据
        //$poly = Db::name('foll_order')->field('pay_time,pay_account,upOrderId')->where($where)->select();
        $polyMoney = 0;
        $poly = Db::name('foll_order')->field('pay_account,upOrderId,ordersn')->where($where)->select();

        echo '<pre>';
        print_r($poly);
        die;*/

		$keyaq = 'wx';//定义缓存key
		$keysq = 'wxs';//定义缓存key
		//删除释放数据
		$this->rs->del($keyaq);
		$this->rs->del($keysq);
		$day = $time > 0 ? date('Ymd',$time):date('Ymd',strtotime('-1 day'));
		//判断当日是否已经对账
		//aq:聚合支付，wx:微信免密，union:银联无感，sde:顺德农商
		$sum = Db::name('parking_pay_summary')->field('sid')->where(['date'=>$day,'pay_type'=>'wx'])->find();
		if(!empty($sum)){
			echo '微信免密昨日订单数据已对';
			//当日无数据，
			$info['payMoney'] = '0.00';
			$info['result']   = '昨日账单已对';
			$this->CheckOk('Fwechat','no',$info);
			return false;
		}

		//对账文件路径
		$path = "/www/web/default/crontab/wx/loadBill{$day}.txt";
		if(file_exists($path) && (filesize($path) != 0)) {//文件存在，并且不为空
			$files = file_get_contents($path,'r');
			//变成新的数组
			$fileArr = explode("\n",$files);//以回车换行分割
			if(is_array($fileArr) && !empty($fileArr)) {
				//获取对应key的缓存数据
        		$ret = $this->rs->sMembers($keyaq);
				//汇总数据表
	        	$headerArr=[
	        		'code'	 =>'000201507100239351',
	        		'date'	 =>$day,
	        		'pay_sum'=>(count($fileArr)-1),//支付总笔数
	        	];

				$bodyArr = [];
				$MoneyCount	= 0;
				$fee_sum	= 0;
	        	foreach($fileArr as $key=>$v) {
	        		//跳过空数据
	        		if(empty($v)){continue;}

	        		$tmp  = rtrim($v,',');
	        		$temps = explode(',',$tmp);
	        		if(empty($temps[6])){//没有退款金额的总额
						//计算支付总金额
						$MoneyCount += sprintf("%.2f",(trim($temps[2])/100));
					}
        			$fee_sum	+= sprintf("%.2f",(trim($temps[5])/100));//费率总额
		          	$bodyArr[$key] = [
	        			//'pay_time'		=> strtotime(trim($temps[3])),//支付时间
	        			'pay_account'	=> sprintf("%.2f",(trim($temps[2])/100)),//支付金额
	        			'upOrderId'		=> trim($temps[0]),//上游订单号
						'ordersn'		=> trim($temps[7]),//订单号
	        		];

		          	$temp = json_encode($bodyArr[$key]);
	        		//写入缓存   数据为空就写入
	        		if(empty($ret)) {
	        			$this->rs->sAdd($keyaq,$temp);
	        		}

        			$temp = null;
	        	}

	        	$MoneyCount  	=  sprintf("%.2f",$MoneyCount);
	        	$headerArr['fee_sum'] 		= $fee_sum;
	        	$headerArr['pay_money'] 	= $MoneyCount;
	        	$headerArr['total_money']   = $MoneyCount;
	        	$headerArr['fee']	        = 0.006;
        		$headerArr['pay_fee']		= sprintf("%.2f",($MoneyCount*0.006));
	        	$headerArr['pay_type']  	= 'wx';
	        	//写入汇总数据
	        	//$insertUnion = Db::name('parking_pay_summary')->insert($bodyHeard);
	        	unset($temp);
	        	unset($temps);
        		unset($fileArr);

				//查询条件
				$where = [
					'pay_type'	 =>['eq','Fwechat'],//支付类型为wechat 或 alipay
					'pay_time'   =>[['egt',strtotime($day)],['elt',(strtotime($day)+86399)]],//大于等于开始时间，小于等于结束时间
					'pay_status' =>1,
					'upOrderId'	 =>['neq',' '],//上级订单号不能为空
				];

				//获取对账日的平台订单数据	 查询平台数据
				//$poly = Db::name('foll_order')->field('pay_time,pay_account,upOrderId')->where($where)->select();
				$polyMoney = 0;
				$poly = Db::name('foll_order')->field('pay_account,upOrderId,ordersn')->where($where)->select();
				if(!empty($poly)) {

					//当日无数据，
					/*$info['payMoney'] = '0.00';
					$info['result']   = '昨日没有交易';
					$this->CheckOk('Fwechat','no',$info);
					echo '没有查询到数据';return false;*/

					//获取缓存值
					$ret = $this->rs->sMembers($keysq);
					foreach($poly as $key=>$val) {
						$polyMoney += sprintf("%.2f",$val['pay_account']);
			    		$temp = json_encode($val);
			    		//写入缓存	 	将平台数据写入缓存
			    		if(empty($ret)) {
			    			$this->rs->sAdd($keysq,$temp);
			    		}
					}

				}


				/**
				 * 步骤说明：
				 * $keyaq 	上级流水数据
				 * $keysq	本地流水数据
				 * 如果数据相等：平账
				 * 如果本地大于上游数据：平台长款
				 * 如果上游数据大于本地：上游短款
				 */
				//计算差额
				$checkMoney = ($MoneyCount - $polyMoney);

				$numaq = $this->rs->scard($keyaq);
				$numsq = $this->rs->scard($keysq);
				$msg	=  '';
				//数据相等为平账
				if($numaq == $numsq) {
					$msg	= '平账';
				} else if($numaq < $numsq) {
					//数据本地大于上游数据为：平台长款
					$msg	= '长款';
					//以平台数据为准，对比上游数据
					$sq = $this->rs->sDiff($keysq,$keyaq);
					if(!empty($sq)) {
						//解析数据
						foreach($sq as $k=>$v) {
							$sq[$k] = json_decode($v,true);
						}

						$errs = null;
						foreach($sq as $k=>$v) {
							//组装数据条件
							$where = ['upOrderId' => $v['upOrderId']];//上级订单号不能为空
							//获取对账日的平台订单数据
							$polys = Db::name('pay_old')->field('update_time,payMoney,upOrderId,ordersn')->where($where)->find();
							if(empty($poly)) {
								echo '没有查询到数据';return false;
							}

							//循环遍历差错池中的数据出错原因
							foreach($v as $kk=>$vv) {
								//检查差错原因
								if(!in_array($vv,$polys)) {
									switch($kk) {
										case 'pay_time':
											$errs .= '平台支付时间错误';
										break;
										case 'pay_account':
											$errs .= '平台交易金额有误';
										break;
										case 'upOrderId':
											$errs .= '平台上游订单号有误';
										break;
										case 'ordersn':
											$errs .= '平台订单号有误';
										break;
									}
								}
							}

							//出错数据所属上下级
							$sq[$k]['type'] 	 = 'Fwechat';
							$sq[$k]['data_type'] = 'down';
							$sq[$k]['msg'] 		 = '长款';
							$sq[$k]['checkMoney'] = sprintf("%.2f",$checkMoney);
							$sq[$k]['date']  	  = $day;
							//获取对账日的平台订单数据
							$up = [
								'r_state'=>'errors',
								//错误原因赋值
								'msg'	 => $errs
							];
							$where = ['order_id' => $v['upOrderId']];//上级订单号不能为空
							//更新数据
							Db::name('parking_pay_wxsecret')->where($where)->update($up);
						}
                        $errs = '';
						//批量写入差错表
						Db::name('parking_check_mistake')->insertAll($sq);
					}
				}else if($numaq > $numsq) {
					//数据本地小于上游数据为：上游短款
					$msg	= '短款';
					//以银行为准   银行数据》本地数据 = 短款
					$aq = $this->rs->sDiff($keyaq,$keysq);
					if(!empty($aq)) {
						//解析数据
						foreach($aq as $k=>$v) {
							$aq[$k] = json_decode($v,true);
						}

						//更新数据写入差错表
						foreach($aq as $k=>$v) {
							$aq[$k]['type'] 	 = 'Fwechat';
							$aq[$k]['data_type'] = 'upper';
							$aq[$k]['msg'] 		 = '短款';
							$aq[$k]['checkMoney'] = sprintf("%.2f",$checkMoney);
							$aq[$k]['date'] 	  = $day;
						}
						//批量写入差错表
						Db::name('parking_check_mistake')->insertAll($aq);
					}

				}

				$headerArr['msg'] = $msg;
				//写入汇总数据
		        $insertUnion = Db::name('parking_pay_summary')->insert($headerArr);
				//删除释放数据
				unset($headerArr);


				//删除释放数据
				$this->rs->del($keyaq);
				$this->rs->del($keysq);
				//对账结束
				//发送差错对账链接给管理员
				$info['payMoney'] = $MoneyCount;
				$info['result']   = $msg;
				$this->CheckOk('Fwechat','yes',$info);

				if($msg == '平账') {
					//前往订单对账
					$this->GetUrl('http://shop.gogo198.cn/foll/public/index.php?s=OrderReconcils/Analywxs&date='.$day);
				}
			}

		} else {

			$headerArr['code']	 		='000201507100239351';
			$headerArr['date']	 		= $day;
			$headerArr['pay_sum'] 		= 0;//支付总笔数
			$headerArr['fee_sum'] 		= 0;
        	$headerArr['pay_money'] 	= 0;
        	$headerArr['total_money']   = 0;
        	$headerArr['fee']	        = 0.006;
    		$headerArr['pay_fee']		= 0;
        	$headerArr['pay_type']  	= 'wx';
        	$headerArr['msg']  			= '平账';
			Db::name('parking_pay_summary')->insert($headerArr);
			echo '微信免密，昨日无订单数据';
			$info['payMoney'] = '0.00';
			$info['result']   = '昨日无对账数据';
			$this->CheckOk('Fwechat','no',$info);

			//前往订单对账
			$this->GetUrl('http://shop.gogo198.cn/foll/public/index.php?s=OrderReconcils/Analywxs&date='.$day);
		}

	}


	/*
	 * 银联无感对账	新增条件pay_account > 0
	 * @param $time string 对账时间时间搓
	 */
	private function AnalyUnions($time = 0) {

		$keyaq = 'Union';//定义缓存key
		$keysq = 'Unions';//定义缓存key
		//删除释放数据
		$this->rs->del($keyaq);
		$this->rs->del($keysq);
		$day = $time > 0 ? date('Ymd',$time):date('Ymd',strtotime('-1 day'));
		//aq:聚合支付，wx:微信免密，union:银联无感，sde:顺德农商
		$sum = Db::name('parking_pay_summary')->field('sid')->where(['date'=>$day,'pay_type'=>'union'])->find();
		if(!empty($sum)) {
			echo '银联无感昨日订单已对';
			//当日无数据，
			$info['payMoney'] = '0.00';
			$info['result']   = '昨日账单已对';
			$this->CheckOk('Parks','no',$info);
			return false;
		}
		try{
            //对账文件路径
            $path = "/www/web/default/crontab/wg/7000000000000049{$day}01.txt";
            if(file_exists($path) && (filesize($path) != 0)) {//文件存在，并且不为空
                $files    = file_get_contents($path,'r');
                $filesArr = explode("\n",$files);//explode()函数以","为标识符进行拆分
                array_shift($filesArr);//去掉数组第一个元素

                $days = substr($filesArr[0],16,8);
                $dayy = !empty($days) ? $days : $day;
                $headerArr = array(
                    'code'            => substr($filesArr[0],0,16),   			//接入代码
                    'date'            => $dayy,									//对账日期
                    'pay_sum'         => (int)(substr($filesArr[0],24,6)),		//支付总笔数
                    'pay_money'       => (float)(substr($filesArr[0],30,18)/100),//支付总金额 单位为分
                    'refund_sum'      => (int)(substr($filesArr[0],48,6)),		//退款总笔数
                    'refund_money'    => (float)(substr($filesArr[0],54,18)/100),//退款总金额  单位为分
                    'fee_sum'  		  => (float)(substr($filesArr[0],72,18)/100),//总手续费  单位为分
                    'total_money'	  => (float)(substr($filesArr[0],90,18)/100),//应收款金额  单位为分
                    'pay_type'	      => 'union',
                );
                $headerArr['fee']	        = 0.0032;
                $headerArr['pay_fee']		= sprintf("%.2f",($headerArr['pay_money'] * 0.0032));

                //去掉数组第一个元素
                array_shift($filesArr);

                if(!empty($filesArr)) {
                    //获取对应key的缓存数据
                    $ret = $this->rs->sMembers($keyaq);

                    $bodyArr = [];
                    foreach($filesArr as $key=>$v) {
                        //跳过退款订单，只拿支付成功订单；
                        $status = (int)(substr($v,8,1));
                        if(empty($v) || $status==2) {continue;}

                        $bodyArr[$key] = [
                            //'pay_time'		=>strtotime(trim($temps[3])),//支付时间
                            'pay_account'	=>sprintf("%.2f",(substr($v,69,18)/100)),//支付金额
                            'upOrderId'		=>trim(substr($v,9,20)),//上游订单号
                            'ordersn'		=>trim(substr($v,29,32)),
                        ];

                        $temp = json_encode($bodyArr[$key]);
                        //写入缓存   数据为空就写入
                        if(empty($ret)) {
                            $this->rs->sAdd($keyaq,$temp);
                        }
                        $temp = null;
                    }

                }

                //插入汇总表
                //$totalMoney  = Db::name('parking_pay_summary')->insert($headerArr);

                //unset($headerArr);
                unset($filesArr);
                unset($bodyArr);

                //查询条件
                $where = [
                    'pay_type'	 	=>['eq','Parks'],//支付类型为wechat 或 alipay
                    //'pay_account'	=>[['egt',0.1]],// 交易金额大于0
                    'pay_time'		=>[['egt',strtotime($day)],['elt',(strtotime($day)+86399)]],//大于等于开始时间，小于等于结束时间
                    'pay_status' 	=>1,
                    'upOrderId'	 	=>['neq',' '],//上级订单号不能为空
                ];

                //获取对账日的平台订单数据	 查询平台数据
                $polyMoney = 0;
                $poly = Db::name('foll_order')->field('pay_account,upOrderId,ordersn')->where($where)->where('pay_account','>',0.1)->select();

                if(!empty($poly)) {

                    /*$headerArr['code']	 		='7000000000000049';
                    $headerArr['date']	 		= $day;
                    $headerArr['pay_sum'] 		= 0;//支付总笔数
                    $headerArr['fee_sum'] 		= 0;
                    $headerArr['pay_money'] 	= 0;
                    $headerArr['total_money']   = 0;
                    $headerArr['fee']	        = 0.003;
                    $headerArr['pay_fee']		= 0;
                    $headerArr['pay_type']  	= 'union';
                    $headerArr['msg']  			= '平账';
                    Db::name('parking_pay_summary')->insert($headerArr);

                    //当日无数据
                    $info['payMoney'] = 0.00;
                    $info['result'] = '暂无数据';
                    $this->CheckOk('Parks','no',$info);
                    echo '没有查询到数据';return false;*/
                    //获取缓存值
                    $ret = $this->rs->sMembers($keysq);
                    foreach($poly as $key=>$val) {
                        $polyMoney += $val['pay_account'];
                        $temp = json_encode($val);
                        //写入缓存	 	将平台数据写入缓存
                        if(empty($ret)) {
                            $this->rs->sAdd($keysq,$temp);
                        }
                    }
                }

                /**
                 * 步骤说明：
                 * $keyaq 	上级流水数据
                 * $keysq	本地流水数据
                 * 如果数据相等：平账
                 * 如果本地大于上游数据：平台长款
                 * 如果上游数据大于本地：上游短款
                 */
                $checkMoney = ($headerArr['pay_money'] - $polyMoney);
                //
                $numaq = $this->rs->scard($keyaq);//1
                $numsq = $this->rs->scard($keysq);//2

                //数据相等为平账
                if($numaq == $numsq) {
                    $msg = '平账';
                } else if($numaq < $numsq) {
                    //数据本地大于上游数据为：平台长款
                    $msg =  '长款';
                    //以平台数据为准，对比上游数据
                    $sq = $this->rs->sDiff($keysq,$keyaq);
                    if(!empty($sq)) {
                        //解析数据
                        foreach($sq as $k=>$v) {
                            $sq[$k] = json_decode($v,true);
                        }

                        $errs = null;
                        foreach($sq as $k=>$v) {
                            //组装数据条件
                            $where = ['upOrderId' => $v['upOrderId']];//上级订单号不能为空
                            //获取对账日的平台订单数据
                            $polys = Db::name('pay_old')->field('update_time,payMoney,upOrderId,ordersn')->where($where)->find();
                            if(empty($poly)) {
                                echo '没有查询到数据';return false;
                            }

                            //循环遍历差错池中的数据出错原因
                            foreach($v as $kk=>$vv) {
                                //检查差错原因
                                if(!in_array($vv,$polys)) {
                                    switch($kk) {
                                        case 'pay_time':
                                            $errs .= '平台支付时间错误';
                                            break;
                                        case 'pay_account':
                                            $errs .= '平台交易金额有误';
                                            break;
                                        case 'upOrderId':
                                            $errs .= '平台上游订单号有误';
                                            break;
                                        case 'ordersn':
                                            $errs .= '平台订单号有误';
                                            break;
                                    }
                                }
                            }

                            //出错数据所属上下级
                            $sq[$k]['type'] 	 = 'union';
                            $sq[$k]['data_type'] = 'down';
                            $sq[$k]['msg'] 		 = '长款';
                            $sq[$k]['checkMoney'] = sprintf("%.2f",$checkMoney);
                            $sq[$k]['date'] 	  = $day;
                            //获取对账日的平台订单数据
                            $up = [
                                'r_state'=>'errors',
                                //错误原因赋值
                                'msg'	 => $errs
                            ];
                            $where = ['order_id' => $v['upOrderId']];//上级订单号不能为空
                            //更新数据
                            Db::name('parking_pay_unionsecret')->where($where)->update($up);
                        }
                        //批量写入差错表
                        Db::name('parking_check_mistake')->insertAll($sq);
                    }
                }else if($numaq > $numsq) {
                    //数据本地小于上游数据为：上游短款
                    $msg = '短款';
                    $aq = $this->rs->sDiff($keyaq,$keysq);
                    if(!empty($aq)) {
                        //解析数据
                        foreach($aq as $k=>$v) {
                            $aq[$k] = json_decode($v,true);
                        }

                        //更新数据写入差错表
                        foreach($aq as $k=>$v) {
                            $aq[$k]['type'] 	 = 'union';
                            $aq[$k]['data_type'] = 'upper';
                            $aq[$k]['msg']		 = '短款';
                            $aq[$k]['checkMoney'] = sprintf("%.2f",$checkMoney);
                            $aq[$k]['date'] 	  = $day;
                        }
                        //批量写入差错表
                        Db::name('parking_check_mistake')->insertAll($aq);
                    }
                }

                $headerArr['msg'] = $msg;
                //写入汇总数据
                $insertUnion = Db::name('parking_pay_summary')->insert($headerArr);
                //删除释放数据
                $this->rs->del($keyaq);
                $this->rs->del($keysq);
                //对账结束
                //发送差错对账链接给管理员
                $info['payMoney'] = $headerArr['pay_money'];
                $info['result']   = $msg;
                $this->CheckOk('Parks','yes',$info);
                //删除释放数据
                unset($headerArr);

                if($msg == '平账') {
                    //前往订单对账
                    $this->GetUrl('http://shop.gogo198.cn/foll/public/index.php?s=OrderReconcils/AnalyUnions&date='.$day);
                }

            } else {

                $headerArr['code']	 		='7000000000000049';
                $headerArr['date']	 		= $day;
                $headerArr['pay_sum'] 		= 0;//支付总笔数
                $headerArr['fee_sum'] 		= 0;
                $headerArr['pay_money'] 	= 0;
                $headerArr['total_money']   = 0;
                $headerArr['fee']	        = 0.003;
                $headerArr['pay_fee']		= 0;
                $headerArr['pay_type']  	= 'union';
                $headerArr['msg']  			= '平账';
                Db::name('parking_pay_summary')->insert($headerArr);

                echo '银联无感昨日订单数据已对';
                $info['payMoney'] = 0.00;
                $info['result'] = '暂无数据';
                $this->CheckOk('Parks','no',$info);

                //前往订单对账
                $this->GetUrl('http://shop.gogo198.cn/foll/public/index.php?s=OrderReconcils/AnalyUnions&date='.$day);
            }
		}catch (\Exception $e){
			$msgs = $e->getMessage().'>>'.$e->getLine();
			return $msgs;
		}
	}


	/*
	 * 顺德农商对账
	 * @param $time string 对账时间时间搓
	 */
	private function AnalySdes($time = 0) {

		$keyaq = 'Sde';//定义缓存key
		$keysq = 'Sdes';//定义缓存key
		//删除释放数据
		$this->rs->del($keyaq);
		$this->rs->del($keysq);

		$day = $time > 0 ? date('Ymd',$time):date('Ymd',strtotime('-1 day'));
		//aq:聚合支付，wx:微信免密，union:银联无感，sde:顺德农商
		$sum = Db::name('parking_pay_summary')->field('sid')->where(['date'=>$day,'pay_type'=>'sde'])->find();
		if(!empty($sum)){
			echo '顺德农商昨日订单数据已对';
			//当日无数据，
			$info['payMoney'] = '0.00';
			$info['result']   = '该天已对账';
			$this->CheckOk('FAgro','no',$info);
			return false;
		}
		//对账文件路径
		$path = "/home/sdebank/TRANYGK04000000050{$day}.txt";
		if(file_exists($path) && (filesize($path) != 0)) {//文件存在，并且不为空
			$content = file_get_contents($path,'r');
            $filesArr= explode("\n",$content);//explode()函数以","为标识符进行拆分
            $headerArr = [];
            if(!empty($filesArr[0])) {
            	$headerArr = [
            		'code'		 =>'801101000927634235',
            		'date'		 => $day,
                    'pay_sum'    => (int)trim(substr($filesArr[0],0,8)),//支付总笔数
                    'pay_money'  => sprintf("%.2f",trim(substr($filesArr[0],8,17))),//支付总金额
                   	'total_money'=> sprintf("%.2f",trim(substr($filesArr[0],8,17))),//支付总金额
                   	'pay_type'	 =>'sde',
                   	'fee'		 => 0,
                   	'pay_fee'	 => 0,
                   	'msg'		 => '平账',
                   	'fee_sum'	 => 0,
                ];

                //$totalMoney  = Db::name('parking_pay_summary')->insert($headerArr);
            }
            //去掉数组第一个元素
	        array_shift($filesArr);
	        if(!empty($filesArr)) {

	        	//获取对应key的缓存数据
        		$ret = $this->rs->sMembers($keyaq);

	        	$bodyArr = [];
            	foreach($filesArr as $key=>$v) {

            	  	if(empty($v)){continue;}
            	  	$bodyArr[$key] = [
	        			'pay_account'	=>sprintf("%.2f",(substr($v,140,17))),//支付金额
	        			'upOrderId'		=>trim(substr($v,8,12)),  //银行流水
	        			'ordersn'		=>trim(substr($v,48,30)), //订单编号
	        			'PlatDate'		=>trim(substr($v,0,8)),	  //对账日期
	        		];

		          	$temp = json_encode($bodyArr[$key]);
	        		//写入缓存   数据为空就写入
	        		if(empty($ret)) {
	        			$this->rs->sAdd($keyaq,$temp);
	        		}
	        		$temp = null;
            	}
	        }

        	//插入汇总表
          	//$totalMoney  = Db::name('parking_pay_summary')->insert($headerArr);

        	//unset($headerArr);
    		unset($filesArr);
    		unset($bodyArr);

			//查询条件
			$where = [
				'pay_type'	 => ['eq','FAgro'],//支付类型为wechat 或 alipay
				//'pay_time'=>[['egt',strtotime($day)],['elt',(strtotime($day)+86399)]],//大于等于开始时间，小于等于结束时间
				'PlatDate'   => $day,
				'pay_status' => 1,
				'upOrderId'	 => ['neq',' '],//上级订单号不能为空
				'IsWrite'	 => ['neq','100'],//没有退款的订单
			];

			//获取对账日的平台订单数据	 查询平台数据
			$polyMoney = 0;
			$poly = Db::name('foll_order')->field('pay_account,upOrderId,ordersn,PlatDate')->where($where)->select();
			if(!empty($poly)) {

				//没有数据写入汇总
				/*$headerArr['code']	 		='801101000927634235';
				$headerArr['date']	 		= $day;
				$headerArr['pay_sum'] 		= 0;//支付总笔数
				$headerArr['fee_sum'] 		= 0;
	        	$headerArr['pay_money'] 	= 0;
	        	$headerArr['total_money']   = 0;
	        	$headerArr['fee']	        = 0.003;
	    		$headerArr['pay_fee']		= 0;
	        	$headerArr['pay_type']  	= 'sde';
	        	$headerArr['msg']  			= '平账';
				Db::name('parking_pay_summary')->insert($headerArr);*/

				//当日无数据
				/*$info['payMoney'] = 0.00;
				$info['result']   = '暂无数据';
				$this->CheckOk('FAgro','no',$info);
				echo '平台订单没有查询到数据';
				die;*/

				//获取缓存值
				$ret = $this->rs->sMembers($keysq);
				foreach($poly as $key=>$val) {
					$polyMoney += $val['pay_account'];
		    		$temp = json_encode($val);
		    		//写入缓存	 	将平台数据写入缓存
		    		if(empty($ret)) {
		    			$this->rs->sAdd($keysq,$temp);
		    		}
				}

			}
			/**
			 * 步骤说明：
			 * $keyaq 	上级流水数据
			 * $keysq	本地流水数据
			 * 如果数据相等：平账
			 * 如果本地大于上游数据：平台长款
			 * 如果上游数据大于本地：上游短款
			 */
			$checkMoney = ($headerArr['pay_money'] - $polyMoney);

			$numaq = $this->rs->scard($keyaq);
			$numsq = $this->rs->scard($keysq);
			//数据相等为平账
			if($numaq == $numsq) {

				$headerArr['msg']	= '平账';
				$msg				= '平账';

				/*if(!empty($headerArr)) {
	            	Db::name('parking_pay_summary')->insert($headerArr);
	          }*/

			} else if($numaq < $numsq) {

				//数据本地大于上游数据为：平台长款
				$headerArr['msg']	= '长款';
				$msg				= '长款';
				//以平台数据为准，对比上游数据
				$sq = $this->rs->sDiff($keysq,$keyaq);
				if(!empty($sq)) {
					//解析数据
					foreach($sq as $k=>$v) {
						$sq[$k] = json_decode($v,true);
					}

					$errs = null;
					foreach($sq as $k=>$v) {
						//echo '$v:'.$v['upOrderId'];
						//组装数据条件
						$where = ['SeqNo' => $v['upOrderId']];//上级订单号不能为空
						//获取对账日的平台订单数据
						$polys = Db::name('pay_old')->field('PlatDate,payMoney,SeqNo,ordersn')->where($where)->find();
						if(empty($poly)) {
							echo '没有查询到数据';
							return false;
						}

						//循环遍历差错池中的数据出错原因
						foreach($v as $kk=>$vv) {
							//检查差错原因
							if(!in_array($vv,$polys)) {
								switch($kk) {
									case 'pay_time':
										$errs .= '平台支付时间错误';
									break;
									case 'pay_account':
										$errs .= '平台交易金额有误';
									break;
									case 'upOrderId':
										$errs .= '平台上游订单号有误';
									break;
									case 'ordersn':
										$errs .= '平台订单号有误';
									break;
								}
							}
						}

						//出错数据所属上下级
						$mis[$k]['pay_account']  = $v['pay_account'];
						$mis[$k]['upOrderId'] 	 = $v['upOrderId'];
						$mis[$k]['ordersn'] 	 = $v['ordersn'];
						$mis[$k]['type'] 	 	 = 'FAgro';
						$mis[$k]['data_type'] 	 = 'down';
						$mis[$k]['msg'] 		 = '长款';
						$mis[$k]['checkMoney'] = sprintf("%.2f",$checkMoney);
						$mis[$k]['date'] 	  = $day;
						//获取对账日的平台订单数据
						$up = [
							'r_state'=>'errors',
							//错误原因赋值
							'msg'	 => $errs
						];
						//$where = ['order_id' => $v['upOrderId']];//上级订单号不能为空
						$where = ['bank_orderid' => $v['upOrderId']];//上级订单号不能为空
						//更新数据
						Db::name('parking_pay_sdesecret')->where($where)->update($up);
					}
					//print_r($mis);die;
					//批量写入差错表
					Db::name('parking_check_mistake')->insertAll($mis);
				}

			} else if($numaq > $numsq) {

				//数据本地小于上游数据为：上游短款
				$headerArr['msg']	= '短款';
				$msg				= '短款';
				//以银行数据为准   银行数据大于 本地数据= 短款
				$aq = $this->rs->sDiff($keyaq,$keysq);
				if(!empty($aq)) {
					//解析数据
					foreach($aq as $k=>$v) {
						$aq[$k] = json_decode($v,true);
					}

					$mis = [];
					//更新数据写入差错表
					foreach($aq as $k=>$v) {
						$mis[$k]['type'] 	 = 'FAgro';
						$mis[$k]['data_type'] = 'upper';
						$mis[$k]['msg']		  = '短款';
						$mis[$k]['checkMoney'] = sprintf("%.2f",$checkMoney);
						$mis[$k]['date'] 	  = $day;
						$mis[$k]['pay_account']  = $v['pay_account'];
						$mis[$k]['upOrderId'] 	 = $v['upOrderId'];
						$mis[$k]['ordersn'] 	 = $v['ordersn'];
					}
					//批量写入差错表
					Db::name('parking_check_mistake')->insertAll($mis);
				}
			}

			//写入汇总数据
	        $insertUnion = Db::name('parking_pay_summary')->insert($headerArr);
			//删除释放数据
			$this->rs->del($keyaq);
			$this->rs->del($keysq);
			//对账结束
			//发送差错对账链接给管理员
		    $info['payMoney'] = $headerArr['pay_money'];
			$info['result']   = $msg;
			$this->CheckOk('FAgro','yes',$info);
			//删除释放数据
			unset($headerArr);

			if($msg == '平账') {
				//前往订单对账
				$this->GetUrl('http://shop.gogo198.cn/foll/public/index.php?s=OrderReconcils/AnalySdes&date='.$day);
			}

		} else {

			$headerArr['code']	 		='801101000927634235';
			$headerArr['date']	 		= $day;
			$headerArr['pay_sum'] 		= 0;//支付总笔数
			$headerArr['fee_sum'] 		= 0;
        	$headerArr['pay_money'] 	= 0;
        	$headerArr['total_money']   = 0;
        	$headerArr['fee']	        = 0.003;
    		$headerArr['pay_fee']		= 0;
        	$headerArr['pay_type']  	= 'sde';
        	$headerArr['msg']  			= '平账';

			Db::name('parking_pay_summary')->insert($headerArr);
			unset($headerArr);
			echo '顺德农商昨日订单数据已对';
			$info['payMoney'] = 0.00;
			$info['result']   = '暂无数据';
			$this->CheckOk('FAgro','no',$info);

			//前往订单对账
			$this->GetUrl('http://shop.gogo198.cn/foll/public/index.php?s=OrderReconcils/AnalySdes&date='.$day);
		}

	}


/*
//将一个元素加入集合，已经存在集合中的元素则忽略。若集合不存在则先创建，若key不是集合类型则返回false，若元素已存在返回0，插入成功返回1。
$ret = $redis->sAdd('myset', 'hello');
//返回集合中所有成员。
$ret = $redis->sMembers('myset');
//判断指定元素是否是指定集合的成员，是返回true，否则返回false。
$ret = $redis->sismember('myset', 'hello');
//返回集合中元素的数量。
$ret = $redis->scard('myset');
//移除并返回集合中的一个随机元素。
$ret = $redis->sPop('myset');
//返回集合中的一个或多个随机成员元素，返回元素的数量和情况由函数的第二个参数count决定：
//如果count为正数，且小于集合基数，那么命令返回一个包含count个元素的数组，数组中的元素各不相同。
//如果count大于等于集合基数，那么返回整个集合。
//如果count为负数，那么命令返回一个数组，数组中的元素可能会重复出现多次，而数组的长度为count的绝对值。
$ret = $redis->sRandMember('myset', 2);
//移除集合中指定的一个元素，忽略不存在的元素。删除成功返回1，否则返回0。
$ret = $redis->srem('myset', 'hello');
//迭代集合中的元素。
//参数：key，迭代器变量，匹配模式，每次返回元素数量（默认为10个）
$ret = $redis->sscan('myset', $it, 'a*', 5);
//将指定成员从一个源集合移动到一个目的集合。若源集合不存在或不包含指定元素则不做任何操作，返回false。
//参数：源集合，目标集合，移动元素
$ret = $redis->sMove('myset', 'myset2', 'aaa');
//返回所有给定集合之间的差集，不存在的集合视为空集。
$ret = $redis->sDiff('myset', 'myset2', 'myset3');
//将所有给定集合之间的差集存储在指定的目的集合中。若目的集合已存在则覆盖它。返回差集元素个数。
//参数：第一个参数为目标集合，存储差集。
$ret = $redis->sDiffStore('myset3', 'myset', 'myset2');
//返回所有给定集合的交集，不存在的集合视为空集。
$ret = $redis->sInter('myset', 'myset2', 'myset3');
//将所有给定集合的交集存储在指定的目的集合中。若目的集合已存在则覆盖它。返回交集元素个数。
//参数：第一个参数为目标集合，存储交集。
$ret = $redis->sInterStore('myset4', 'myset', 'myset2', 'myset3');
//返回所有给定集合的并集，不存在的集合视为空集。
$ret = $redis->sUnion('myset', 'myset2', 'myset3');
//将所有给定集合的并集存储在指定的目的集合中。若目的集合已存在则覆盖它。返回并集元素个数。
//参数：第一个参数为目标集合，存储并集。
$ret = $redis->sUnionStore('myset4', 'myset', 'myset2', 'myset3');*/



	/**
	 * 对账完成发送模板消息
	 * 2018-09-03
	 */
	public function CheckOk($type,$isok='no',$info=[]) {

		switch(trim($type))
		{
			case 'tgpay'://聚合支付
				$url = 'http://shop.gogo198.cn/foll/public/?s=mreconcil/index&type=tgpay';
				$payType	= '聚合支付';
				$wxtype = 'tgpay';
			break;
			case 'Fwechat'://微信免密
				$url = 'http://shop.gogo198.cn/foll/public/?s=mreconcil/index&type=fwechat';
				$payType	= '微信免密';
				$wxtype = 'fwechat';
			break;
			case 'FAgro'://顺德农商
				$url = 'http://shop.gogo198.cn/foll/public/?s=mreconcil/index&type=fagro';
				$payType    = '农商代扣';
				$wxtype = 'fagro';
			break;
			case 'Parks'://银联无感
				$url = 'http://shop.gogo198.cn/foll/public/?s=mreconcil/index&type=union';
				$payType    = '银联无感';
				$wxtype = 'union';
			break;
		}

		$day = date('Y-m-d',time());
		//yes  代表有数据    no没有数据 不带url链接
		/*if($isok != 'yes') {//no   没有数据
			$str =  '没有对账错误数据';
			file_put_contents("./paylog/detail/{$day}.txt", $str."\r\n",FILE_APPEND);

			$temp['openid']	  = 'ov3-btyLPTGwIduBvEXdiGSnpUK4';
			$temp['url']	  = '';
			$temp['first']	  = '您好，昨日银企交易，系统已经完成对账，具体如下：';
			$temp['dates']    = date('Y-m-d H:i:s',time());
			$temp['payType']  = $payType;
			$temp['ordersn']  = '0000';
			$temp['payMoney'] = $info['payMoney']?$info['payMoney']:'0.00';
			$temp['result']	  = $info['result'];
			$temp['remark']   = '对账详情，请点击查看或下载';
			$this->sendTempl($temp);
			return false;
		}*/

		//yes  有数据
		$str = '对账完成，有错误';
		//file_put_contents("./paylog/detail/{$day}.txt", $str."\r\n",FILE_APPEND);
		$payMoney = $info['payMoney']?$info['payMoney']:'0.00';

		$temp['openid']	  = 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg';//'ov3-btyLPTGwIduBvEXdiGSnpUK4';
		$temp['url']	  = $url;
		$temp['first']	  = '您好，昨日银企交易，系统已经完成对账，具体如下：';
		$temp['dates']    = date('Y-m-d H:i:s',time());
		$temp['payType']  = $payType;
		$temp['ordersn']  = '0000';
		$temp['payMoney'] = $payMoney.'元';
		$temp['result']	  = $info['result'];
		$temp['remark']   = '对账详情，请点击查看或下载';
		$this->sendTempl($temp);
		//$this->SendMamage($temp,'wx6d1af256d76896ba','pages/user/mreconcil/datalist/datalist?type='.$wxtype.'&date='.$date);
		return true;
	}

	//发送微信通知
	public function SendWechat($data)
	{
		$url = 'http://shop.gogo198.cn/api/sendwechattemplatenotice.php';
        $client = new \GuzzleHttp\Client();
        try {
            //正常请求
            $promise = $client->request('post', $url, ["headers" => ['Content-Type' => 'application/json'],'body'=>$data]);
        } catch (GuzzleHttpExceptionClientException $exception) {
            //捕获异常 输出错误
            return $this->error($exception->getMessage());
        }
	}

	public function SendMamage($temp,$appid = '',$pagepath = '')
	{
		//appid = wx6d1af256d76896ba
		//boss ooWwF0p_1SBnxknfhkMv5ux02U1E
		$manage_user = Db::name('mc_mapping_fans')->where(array('uniacid'=>3,'unionid'=>'ooWwF0p_1SBnxknfhkMv5ux02U1E'))->find();

		$this->SendWechat(json_encode([
		  'call'=>'send_pre_commit_notice_d',
		  'first' =>$temp['first'],
		  'dates' =>$temp['dates'],
		  'payType' =>$temp['payType'],
		  'ordersn' =>$temp['ordersn'],
		  'payMoney' =>$temp['payMoney'],
		  'result' => $temp['result'],
		  'remark' => $temp['remark'],
		  'openid'=>$manage_user['openid'],
		  'uniacid'=>3,
		  'appid' => $appid,
		  'pagepath' => $pagepath
		]));

		$manage_user_d = Db::name('mc_mapping_fans')->where(array('uniacid'=>3,'unionid'=>'ooWwF0jxz1FJf8wotqCD1hELXhQY'))->find();

		$this->SendWechat(json_encode([
		  'call' =>'send_pre_commit_notice_d',
		  'first' =>$temp['first'],
		  'dates' =>$temp['dates'],
		  'payType' =>$temp['payType'],
		  'ordersn' =>$temp['ordersn'],
		  'payMoney' =>$temp['payMoney'],
		  'result' => $temp['result'],
		  'remark' => $temp['remark'],
		  'openid'=>$manage_user_d['openid'],
		  'uniacid'=>3,
		  'appid' => $appid,
		  'pagepath' => $pagepath
		]));
	}


	/*
	 * 发送异常对账提醒
	 */
	private function sendErrorTempl($temp) {
		$templdate=[
			'touser'	 =>$temp['openid'],
			'template_id'=>'5rlOMSLNLHmvAwh06B8JOrR0isNcOZL76rOs1d6QlDA',
			'url'		 =>$temp['url']?$temp['url']:'',
			'data'=>[
				'first'=>[
					'value'=>$temp['first'],//'',
					'color'=>'#f00000',
				],
				'keyword1'=>[
					'value'=>$temp['money'],
					'color'=>'#436EEE',
				],
				'keyword2'=>[
					'value'=>$temp['nums'],
					'color'=>'#436EEE',
				],
				'keyword3'=>[
					'value'=>$temp['type'],
					'color'=>'#436EEE',
				],
				'keyword4'=>[
					'value'=>$temp['dates'],
					'color'=>'#436EEE',
				],
				'remark'=>[
					'value'=>$temp['remark'],
					'color'=>'#808080'
				],
			]
		];

		$postUrl = 'http://shop.gogo198.cn/foll/public/?s=api/wechat/template';
		$tmp = ['template'=>serialize($templdate),'uniacid'=>3];
		$res = $this->postJson($postUrl,$tmp,true);
		return $res;
	}


	/*
	 * 发送模板消息
	 */
	private function sendTempl($temp) {
		$templdate=[
			'touser'	 =>$temp['openid'],
			'template_id'=>'f35Y6je6nrC1gxp-PJYp-agppwEy25WOqFR1oBD_wIo',
			'url'		 =>$temp['url']?$temp['url']:'',
			'data'=>[
				'first'=>[
					'value'=>$temp['first'],//'',
					'color'=>'#173177',
				],
				'keyword1'=>[
					'value'=>$temp['dates'],
					'color'=>'#436EEE',
				],
				'keyword2'=>[
					'value'=>$temp['payType'],
					'color'=>'#436EEE',
				],
				'keyword3'=>[
					'value'=>$temp['ordersn'],
					'color'=>'#436EEE',
				],
				'keyword4'=>[
					'value'=>$temp['payMoney'],
					'color'=>'#436EEE',
				],
				'keyword5'=>[
					'value'=>$temp['result'],
					'color'=>'#436EEE',
				],
				'remark'=>[
					'value'=>$temp['remark'],
					'color'=>'#808080'
				],
			]
		];

		$postUrl = 'http://shop.gogo198.cn/foll/public/?s=api/wechat/template';
		$tmp = ['template'=>serialize($templdate),'uniacid'=>3];
		$res = $this->postJson($postUrl,$tmp,true);
		return $res;
	}


	/**
	 * 发送post请求  json 数据；
	 * CURL post
	 * @param $url: 请求地址
	 * @param $data: 请求数据
	 * @param $json: 是否json 数据请求；
	 */
	public function postJson($url,$data = null,$json=false)
	{
		$curl = curl_init();
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
		curl_setopt($curl,CURLOPT_HEADER,0); //头文件信息做数据流输出
		curl_setopt($curl,CURLOPT_URL,$url);
		if(!empty($data)) {

			if($json && is_array($data)){
				$data = json_encode($data);
			}

			curl_setopt($curl,CURLOPT_POST,1);
			curl_setopt($curl,CURLOPT_POSTFIELDS,$data);

			if($json) {//发送JSON数据；

				curl_setopt($curl,CURLOPT_HEADER,0);
				curl_setopt($curl,CURLOPT_HTTPHEADER,array(
					//'Content-Type:text/html;charset=utf-8',
					'Content-Type:application/json;charset=utf-8',
					'Content-Length:'.strlen($data)
				));
			}
		}
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		$res = curl_exec($curl);
		$errorno = curl_errno($curl);
		if($errorno) {//错误
			return ['errorno'=>false,'errmsg'=>$errorno];
		}
		curl_close($curl);
		return json_decode($res,true);
	}


	//Curl post 请求；
	public function PostUrl($url,$post_data=[]){
		//初始化
		$curl = curl_init();
		//设置捉取Url
		curl_setopt($curl,CURLOPT_URL,$url);
		//设置头文件的信息
		curl_setopt($curl,CURLOPT_HEADER,0);
		//设置获取的信息以文件流的形式返回，而不是直接输出
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		//设置超时
		curl_setopt($curl,CURLOPT_TIMEOUT,65);
		//设置post方式提交
		curl_setopt($curl,CURLOPT_POST,1);
		//设置post数据
		//设置请求参数
		curl_setopt($curl,CURLOPT_POSTFIELDS,$post_data);
		//执行命令  并返回结果
		$res = curl_exec($curl);
		//关闭连接
		curl_close($curl);
		//返回数据
		return $res;
	}

	//Curl Get请求
	public function GetUrl($url) {
		//初始化
		$curl = curl_init();
		//设置捉取URL
		curl_setopt($curl,CURLOPT_URL,$url);
		//设置获取的信息以文件流的形式返回，而不是直接输出。
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		//执行命令
		$res = curl_exec($curl);
		//关闭Curl请求
		curl_close($curl);
		//print_r($res);
		return $res;
	}
}
?>
