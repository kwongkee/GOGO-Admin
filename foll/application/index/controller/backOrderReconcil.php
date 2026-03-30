<?php
namespace app\index\controller;
use app\index\controller;
use think\Request;
use think\Loader;
use think\Db;
use Util\data\Sysdb;
use Util\data\Redis;
use PHPExcel;
use PHPExcel_IOFactory;

class OrderReconcil extends CommonController
{
	public function __construct(){
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
		$this->rs    = $this->redis->getRedis();
		//实例化数据库
		$this->db    = new Sysdb;
		//	179078286@qq.com
	}
	
	//测试文件   2018-10-10
	public function test() {
		echo '测试方法';
		/*$keyaq = 'wxaq';//上游数据
		$keysq = 'wxsq';//本地数据
		$this->rs->del($keyaq);
		$this->rs->del($keysq);
		$startTime = date('Ymd',strtotime('-1 day'));
		echo 'start';
		$order[0]['pay_account'] =5.11;
		$order[0]['upOrderId']   ='gg115465486151';
		$order[0]['ordersn']     ='gg995577155483';
		
		$order[1]['pay_account'] =2.11;
		$order[1]['upOrderId']   ='gg115465486d151';
		$order[1]['ordersn']     ='gg9955771554348';
		$ret = $this->rs->sMembers($keysq);
		foreach($order as $k=>$v) {
			$temp = json_encode($v);
			//写入缓存	 	将平台数据写入缓存
			if(empty($ret)) {
				//写入缓存中
				$this->rs->sAdd($keysq,$temp);
			}
		}
		
		$polyArr[0]['pay_account'] = 53.2;
		$polyArr[0]['upOrderId']   = 'ssd5645487874548';
		$polyArr[0]['ordersn']	   = '554896545sss87df';
		
		$rets = $this->rs->sMembers($keyaq);
		foreach($polyArr as $k=>$v) {
			$temps = json_encode($v);
			//写入缓存	 	将平台数据写入缓存
			if(empty($rets)) {
				//写入缓存中
				$this->rs->sAdd($keyaq,$temps);
			}
		}
		
		$numaq = $this->rs->scard($keyaq);//上游数据  数据总数
		$numsq = $this->rs->scard($keysq);//本地数据  数据总数
		
		//以上游数据为准   上游对下游
		$aq = $this->rs->sDiff($keyaq,$keysq);
		//下游为准
		$sq = $this->rs->sDiff($keysq,$keyaq);
		echo '<pre>';
		$Arraq[] = json_decode($aq[0],true);
		print_r($Arraq);
		
		echo '11微信免密订单对账：'.'《》订单时间：'.$startTime;
		//释放缓存
		$this->rs->del($keyaq);
		$this->rs->del($keysq);*/
		
		
		//发送平账通知
		/*$url = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/OkAnalyaqs&type=aq';
		$this->SendEmail($url,'805929498@qq.com','测试您有聚合支付，对账！请登录');
		echo '执行发送成功：'.date('Y-m-d H:i:s',time());*/
	}
	
	//订单对账  聚合支付
	public function Analyaqs() {
		
		$keyaq = 'aq';//上游数据
		$keysq = 'sq';//本地数据
		$this->rs->del($keyaq);
		$this->rs->del($keysq);
		$startTime = !empty(input('get.date')) ? input('get.date') :date('Ymd',strtotime('-1 day'));
		//$startTime = date('Ymd',strtotime('-1 day'));		
		//$startTime = '20181007';
		$startTimes = strtotime($startTime.'00:00:00');//开始日期
		$endTime    = strtotime($startTime.'23:59:59');//结束日期
		
		$summaryOk = $this->db->table('parking_mer_summary')->where(['date'=>$startTime,'pay_type'=>'aq'])->item();
		if(!empty($summaryOk)) {//订单已对账
			$url = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/OkAnalyaqs&type=aq';
			// 179078286@qq.com 
			$this->SendEmail($url,'353453825@qq.com',$startTime.'您当天的订单对账已完成，请勿重复执行！');//179078286
			echo '执行时间：'.date('Y-m-d H:i:s',time());
			die;
		}
		
		$where = [
			'a.pay_time'=>['between',"{$startTimes},{$endTime}"],
			'a.pay_status'=>1,
			'a.upOrderId'=>['neq',''],
			'a.pay_type'=>[['eq','wechat'],['eq','alipay'],'or']
		];
		
		$pay_money = 0;//成功交易总额（除去退款部分）
		//本地数据
		$order = Db::name('foll_order')->alias('a')->join('parking_order b','a.ordersn=b.ordersn','LEFT')->where($where)->field(['a.pay_time,a.pay_account,a.upOrderId,a.ordersn,a.RefundMoney,a.IsWrite,a.ref_auto,b.charge_type'])->order('a.pay_time desc')->select();
		if(!empty($order)) {
			$orderArr = [];
			foreach($order as $key=>$val) {
				//预付费
				if(($val['charge_type'] == 0) && (($val['ref_auto'] == 2) || ($val['IsWrite'] == 103))) {
					$orderArr[$key]['pay_account'] = sprintf("%.2f",($val['pay_account'] + $val['RefundMoney']));
					$pay_money += sprintf("%.2f",$val['pay_account']);//实付金额
				} else {
					$orderArr[$key]['pay_account'] = $val['pay_account'];
					$pay_money += sprintf("%.2f",$val['pay_account']);//实付金额
				}
				$orderArr[$key]['upOrderId']= $val['upOrderId'];
				$orderArr[$key]['ordersn']  = $val['ordersn'];
			}
			
			$polyMoneys = 0;
			//将数据库中查到的数据进行格式处理
			$ret = $this->rs->sMembers($keysq);
			foreach($orderArr as $k=>$v) {
				//本地数据总金额
				$polyMoneys += $v['pay_account'];
	    		$temp = json_encode($v);
	    		//写入缓存	 	将平台数据写入缓存
	    		if(empty($ret)) {
	    			//写入缓存中
	    			$this->rs->sAdd($keysq,$temp);
	    		}
			}
		}
		
		//银行数据
		$poly = $this->db->table('parking_pay_poly')->field('pay_money,low_order_id,order_id')->where(['date'=>$startTime])->order('pay_time desc')->lists();
		if(!empty($poly)) {
			$polyArr = [];
			foreach($poly as $key=>$val) {
				$polyArr[$key]['pay_account'] = sprintf("%.2f",$val['pay_money']);
				$polyArr[$key]['upOrderId']   = $val['order_id'];
				$polyArr[$key]['ordersn']     = $val['low_order_id'];
			}
			$polyMoney = 0;
			$rets = $this->rs->sMembers($keyaq);
			foreach($polyArr as $k=>$v) {
				//本地数据总金额
				$polyMoney += $v['pay_account'];
	    		$temps = json_encode($v);
	    		//写入缓存	 	将平台数据写入缓存
	    		if(empty($rets)) {
	    			//写入缓存中
	    			$this->rs->sAdd($keyaq,$temps);
	    		}
			}
		}
		
		$numaq = $this->rs->scard($keyaq);//上游数据  数据总数
		$numsq = $this->rs->scard($keysq);//本地数据  数据总数
		
		//以上游数据为准   上游对下游
		$aq = $this->rs->sDiff($keyaq,$keysq);
		//下游为准
		$sq = $this->rs->sDiff($keysq,$keyaq);
		$short_num = 0;//短款数
		$long_num  = 0;//长款数
		$msg = '平账';
		//数据总数平账
		if($numaq == $numsq) {
			$flag	   = 0;//是否平账标识   0平账，1长短款
			$flagArr   = [];
			if(!empty($aq) && !empty($sq)) {
				$counts = count($aq);
				//金额差错
				if($counts > 1) {//错误数据大于1时需要循环处理
					$inserArr=[];
					for($i=0;$i<$counts;$i++) {
						$Arraq[] = json_decode($aq[$i],true);
						$Arrsq[] = json_decode($sq[$i],true);
						
						if($Arraq[$i]['pay_account'] > $Arrsq[$i]['pay_account']) {
							$inserArr[$i]['money']  = sprintf("%.2f",($Arraq[$i]['pay_account'] - $Arrsq[$i]['pay_account']));
							$inserArr[$i]['is_sort'] = 'short';
							$short_num +=1;
						} else {
							$inserArr[$i]['money'] = sprintf("%.2f",($Arrsq[$i]['pay_account']-$Arraq[$i]['pay_account']));
							$inserArr[$i]['is_sort'] = 'long';
							$long_num +=1;
						}
						
						$inserArr[$i]['ordersn'] 	 = $Arraq[$i]['ordersn'];
						$inserArr[$i]['upOrderId'] 	 = $Arraq[$i]['upOrderId'];
						$inserArr[$i]['pay_account'] = $Arraq[$i]['pay_account'];
						$inserArr[$i]['pay_type'] 	 = 'aq';
						$inserArr[$i]['date'] 	 	 = $startTime;
						$inserArr[$i]['is_ok'] 	 	 = 'no';
					}
					$flag = 1;
					/*echo '<pre>';
					print_r($inserArr);
					die;*/
					$insertUnion = Db::name('parking_mer_mistake')->insertAll($inserArr);
					$msg   = '长款1';
					
				} else {
					
					$Arraq[] = json_decode($aq[0],true);
					$Arrsq[] = json_decode($sq[0],true);					
					$inserArr = [];
					if($Arraq[0]['pay_account'] > $Arrsq[0]['pay_account']) {
						//$inserArr[$i]['money']  = sprintf("%.2f",($Arraq[$i]['pay_account'] - $Arrsq[$i]['pay_account']));
						$inserArr['money']  = sprintf("%.2f",($Arraq[0]['pay_account'] - $Arrsq[0]['pay_account']));
						$inserArr['is_sort'] = 'short';
						$short_num +=1;
					} else {
						//$inserArr[$i]['money'] = sprintf("%.2f",($Arrsq[$i]['pay_account']-$Arraq[$i]['pay_account']));
						$inserArr['money']   = sprintf("%.2f",($Arrsq[0]['pay_account']- $Arraq[0]['pay_account']));
						$inserArr['is_sort'] = 'long';
						$long_num +=1;
					}
					
					$inserArr['ordersn'] 	 = $Arraq[0]['ordersn'];
					$inserArr['upOrderId'] 	 = $Arraq[0]['upOrderId'];
					$inserArr['pay_account'] = $Arraq[0]['pay_account'];
					$inserArr['pay_type'] 	 = 'aq';
					$inserArr['date'] 	 	 = $startTime;
					$inserArr['is_ok'] 	 	 = 'no';
					$flag = 1;
					//把对应的差额数据写入差错表中
					//print_r($inserArr);die;
					$insertUnion = Db::name('parking_mer_mistake')->insert($inserArr);
					$msg   = '长款2';
				}
			} else {
				$msg   = '平账';
				$flag = 0;
			}
			
			//实付交易总额（除去退款部分）
			$pay_money  		  = sprintf("%.2f",$pay_money);
			$summary['date'] 	  = $startTime;//交易时间
			$summary['count'] 	  = $numaq;//交易总数
			$summary['ok_num']	  = $numsq;//平账数
			$summary['long_num']  = $long_num;//长款数
			$summary['short_num'] = $short_num;//短款数
			
			$summary['pay_account'] = $pay_money;//$polyMoney;//交易总额
			$summary['fee']   	    = 0.006;//交易费率
			$summary['pay_fee']     = sprintf("%.2f",$pay_money * 0.006);//交易费用
			$summary['pay_money']   = ($pay_money-$summary['pay_fee']);//清算金额
			$summary['pay_type']    = 'aq';//支付通道
			$summary['order_check'] = 'no';//订单清算确认，no:未确认，yes:确认
			//parking_mer_summary
			$insertUnion = Db::name('parking_mer_summary')->insert($summary);
			//print_r($summary);
			
			/**
			 * 如果flag ==1 表示有长款  0表示平账
			 */
			if($flag == 1 ) {
				//发送异常通知
				$url = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/Analyaqs&type=aq';
				//	179078286@qq.com    
				$this->SendEmail($url,'353453825@qq.com',$startTime.'您有聚合支付，对账！请登录');
			} else {
				//发送平账通知
				$url = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/OkAnalyaqs&type=aq';
				
				
				
				$this->SendEmail($url,'353453825@qq.com',$startTime.'您有聚合支付，对账！请登录');
			}
		
		} else if($numaq < $numsq) {
			//数据本地大于上游数据为：平台长款
			
			if(!empty($sq)) {
				$counts = count($sq);
				$inserArr = [];
				//金额差错
				if($counts > 1) {//错误数据大于1时需要循环处理
					for($i=0;$i<$counts;$i++) {
						$Arrsq[] = json_decode($sq[$i],true);
						$inserArr[$i]['money'] 		 = sprintf("%.2f",$Arrsq[$i]['pay_account']);
						$inserArr[$i]['is_sort'] 	 = 'long';						
						$inserArr[$i]['ordersn'] 	 = $Arrsq[$i]['ordersn'];
						$inserArr[$i]['upOrderId'] 	 = $Arrsq[$i]['upOrderId'];
						$inserArr[$i]['pay_account'] = $Arrsq[$i]['pay_account'];
						$inserArr[$i]['pay_type'] 	 = 'aq';
						$inserArr[$i]['date'] 	 	 = $startTime;
						$inserArr[$i]['is_ok'] 	 	 = 'no';
						$long_num += 1;
					}
					//写入差错表，批量
					//print_r($inserArr);
					$insertUnion = Db::name('parking_mer_mistake')->insertAll($inserArr);
				} else {
					$Arrsq = json_decode($sq[0],true);
					$inserArr['money']   	 = sprintf("%.2f",$Arrsq['pay_account']);
					$inserArr['is_sort'] 	 = 'long';//长款				
					$inserArr['ordersn'] 	 = $Arrsq['ordersn'];
					$inserArr['upOrderId'] 	 = $Arrsq['upOrderId'];
					$inserArr['pay_account'] = $Arrsq['pay_account'];
					$inserArr['pay_type'] 	 = 'aq';
					$inserArr['date'] 	 	 = $startTime;
					$inserArr['is_ok'] 	 	 = 'no';
					$long_num += 1;
					//把对应的差额数据写入差错表中1
//					print_r($inserArr);   parking_mer_mistake
					$insertUnion = Db::name('parking_mer_mistake')->insert($inserArr);
				}
			}
			
			//实付交易总额（除去退款部分）
			$pay_money  		  = sprintf("%.2f",$pay_money);
			$summary['date'] 	  = $startTime;//交易时间
			$summary['count'] 	  = $numaq;//交易总数
			$summary['ok_num']	  = $numaq;//平账数
			$summary['long_num']  = $long_num;//长款数
			$summary['short_num'] = $short_num;//短款数
			
			$summary['pay_account'] = $pay_money;//$polyMoney交易总额
			$summary['fee']   	  	= 0.006;//交易费率
			$summary['pay_fee']   	= sprintf("%.2f",$pay_money * 0.006);//交易费用
			$summary['pay_money'] 	= ($pay_money-$summary['pay_fee']);//清算金额
			$summary['pay_type']  	= 'aq';//支付通道
			$summary['order_check'] = 'no';//订单清算确认，no:未确认，yes:确认
			
			$insertUnion = Db::name('parking_mer_summary')->insert($summary);
			$msg   = '长款';
			//发送异常通知
			$url = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/Analyaqs&type=aq';
			$this->SendEmail($url,'353453825@qq.com',$startTime.'您有聚合支付，对账！请登录');
			
		}else if($numaq > $numsq) {
			//上游数据大于本地 = 短款
			if(!empty($aq)) {
				$counts = count($aq);
				$inserArr = [];
				//金额差错
				if($counts > 1) {//错误数据大于1时需要循环处理
					for($i=0;$i<$counts;$i++) {
						$Arraq[] = json_decode($aq[$i],true);
						$inserArr[$i]['money'] = sprintf("%.2f",$Arraq[$i]['pay_account']);
						$inserArr[$i]['is_sort'] = 'short';						
						$inserArr[$i]['ordersn'] 	 = $Arraq[$i]['ordersn'];
						$inserArr[$i]['upOrderId'] 	 = $Arraq[$i]['upOrderId'];
						$inserArr[$i]['pay_account'] = $Arraq[$i]['pay_account'];
						$inserArr[$i]['pay_type'] 	 = 'aq';
						$inserArr[$i]['date'] 	 	 = $startTime;
						$inserArr[$i]['is_ok'] 	 	 = 'no';
						$short_num +=1;
					}
					$insertUnion = Db::name('parking_mer_mistake')->insertAll($inserArr);
					//写入差错表，批量
					//print_r($inserArr);
				} else {
					
					$Arraq = json_decode($aq[0],true);					
					$inserArr['money']   	 = sprintf("%.2f",$Arraq['pay_account']);
					$inserArr['is_sort'] 	 = 'short';//长款				
					$inserArr['ordersn'] 	 = $Arraq['ordersn'];
					$inserArr['upOrderId'] 	 = $Arraq['upOrderId'];
					$inserArr['pay_account'] = $Arraq['pay_account'];
					$inserArr['pay_type'] 	 = 'aq';
					$inserArr['date'] 	 	 = $startTime;
					$inserArr['is_ok'] 	 	 = 'no';
					$short_num +=1;
					$insertUnion = Db::name('parking_mer_mistake')->insert($inserArr);
					//把对应的差额数据写入差错表中
					//print_r($inserArr);
				}
			}
			
			//实付交易总额（除去退款部分）
			$pay_money  		  = sprintf("%.2f",$pay_money);
			//数据写入汇总表
			$summary['date'] 	  = $startTime;//交易时间
			$summary['count'] 	  = $numaq;//交易总数
			$summary['ok_num']	  = $numaq;//平账数
			$summary['long_num']  = $long_num;//长款数
			$summary['short_num'] = $short_num;//短款数	
					
			$summary['pay_account'] = $pay_money;//$polyMoney;//交易总额
			$summary['fee']   	  	= 0.006;//交易费率
			$summary['pay_fee']   	= sprintf("%.2f",$pay_money * 0.006);//交易费用
			$summary['pay_money'] 	= ($pay_money-$summary['pay_fee']);//清算金额
			$summary['pay_type']  	= 'aq';//支付通道
			$summary['order_check'] = 'no';//订单清算确认，no:未确认，yes:确认
			$insertUnion = Db::name('parking_mer_summary')->insert($summary);
			$msg   = '短款';
			//发送异常通知       20181007040000882815
			$url = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/Analyaqs&type=aq';
			$this->SendEmail($url,'353453825@qq.com',$startTime.'您有聚合支付，对账！请登录');
		}
		
		echo '聚合支付订单对账：'.$msg.'《》订单时间：'.$startTime;
		//释放缓存
		$this->rs->del($keyaq);
		$this->rs->del($keysq);
	}
	
	
	
	/**
	 * 微信支付订单对账
	 * 费率：0.01
	 */
	public function Analywxs() {
		
		$keyaq = 'wxaq';//上游数据
		$keysq = 'wxsq';//本地数据
		$this->rs->del($keyaq);
		$this->rs->del($keysq);
		//$startTime = date('Ymd',strtotime('-1 day'));
		$startTime = !empty(input('get.date')) ? input('get.date') :date('Ymd',strtotime('-1 day'));
		//$startTime = '20181007';
		$startTimes = strtotime($startTime.'00:00:00');//开始日期
		$endTime    = strtotime($startTime.'23:59:59');//结束日期
		
		$summaryOk = $this->db->table('parking_mer_summary')->where(['date'=>$startTime,'pay_type'=>'wx'])->item();
		if(!empty($summaryOk)) {//订单已对账
			$url = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/OkAnalyaqs&type=wx';
			$this->SendEmail($url,'353453825@qq.com',$startTime.'您当天的订单对账已完成，请勿重复执行！');//179078286
			echo '执行时间：'.date('Y-m-d H:i:s',time());
			die;
		}
		
		$where = [
			'pay_time'=>['between',"{$startTimes},{$endTime}"],
			'pay_status'=>1,
			'upOrderId'=>['neq',''],
			'pay_type'=>['eq','Fwechat']
		];
		
		//本地数据
		//$order = Db::name('foll_order')->alias('a')->join('parking_order b','a.ordersn=b.ordersn','LEFT')->where($where)->field(['a.pay_time,a.pay_account,a.upOrderId,a.ordersn,a.RefundMoney,a.IsWrite,a.ref_auto,b.charge_type'])->order('a.pay_time desc')->select();
		$order = Db::name('foll_order')->field('pay_account,upOrderId,ordersn')->where($where)->select();
		if(!empty($order)) {
			$polyMoneys = 0;
			//将数据库中查到的数据进行格式处理
			$ret = $this->rs->sMembers($keysq);
			foreach($order as $k=>$v) {
				//本地数据总金额
				$polyMoneys += $v['pay_account'];
	    		$temp = json_encode($v);
	    		//写入缓存	 	将平台数据写入缓存
	    		if(empty($ret)) {
	    			//写入缓存中
	    			$this->rs->sAdd($keysq,$temp);
	    		}
			}
		}
		
		
		//银行数据
		$poly = $this->db->table('parking_pay_wxsecret')->field('pay_money,order_id,low_order_id')->where(['date'=>$startTime])->order('pay_time desc')->lists();
		if(!empty($poly)) {
			$polyArr = [];
			foreach($poly as $key=>$val) {
				$polyArr[$key]['pay_account'] = sprintf("%.2f",$val['pay_money']);
				$polyArr[$key]['upOrderId']   = $val['order_id'];
				$polyArr[$key]['ordersn']	  = $val['low_order_id'];
			}
			$polyMoney = 0;
			$rets = $this->rs->sMembers($keyaq);
			foreach($polyArr as $k=>$v) {
				//本地数据总金额
				$polyMoney += $v['pay_account'];
	    		$temps = json_encode($v);
	    		//写入缓存	 	将平台数据写入缓存
	    		if(empty($rets)) {
	    			//写入缓存中
	    			$this->rs->sAdd($keyaq,$temps);
	    		}
			}
		}
		
		$numaq = $this->rs->scard($keyaq);//上游数据  数据总数
		$numsq = $this->rs->scard($keysq);//本地数据  数据总数
		
		//以上游数据为准   上游对下游
		$aq = $this->rs->sDiff($keyaq,$keysq);
		//下游为准
		$sq = $this->rs->sDiff($keysq,$keyaq);		
		
		/*echo '<pre>';
		print_r($aq);
		echo '<hr>';
		print_r($sq);
		
		$this->rs->del($keyaq);
		$this->rs->del($keysq);
		die;*/
		
		$short_num = 0;//短款数
		$long_num  = 0;//长款数
		$msg = '平账';
		//数据总数平账
		if($numaq == $numsq) {			
			$flag	   = 0;//是否平账标识   0平账，1长短款
			$flagArr   = [];
			if(!empty($aq) && !empty($sq)) {
				$counts = count($aq);
				//金额差错
				if($counts > 1) {//错误数据大于1时需要循环处理
					$inserArr=[];
					for($i=0;$i<$counts;$i++) {
						//$ordersn = 'G99198'.date('YmdHis',time()).mt_rand(010,999);
						
						$Arraq[] = json_decode($aq[$i],true);
						$Arrsq[] = json_decode($sq[$i],true);
						
						if($Arraq[$i]['pay_account'] > $Arrsq[$i]['pay_account']) {
							
							$inserArr[$i]['money']  = sprintf("%.2f",($Arraq[$i]['pay_account'] - $Arrsq[$i]['pay_account']));
							$inserArr[$i]['is_sort'] = 'short';
							$short_num +=1;
							
						} else if($Arraq[$i]['pay_account'] < $Arrsq[$i]['pay_account']) {
							
							$inserArr[$i]['money'] = sprintf("%.2f",($Arrsq[$i]['pay_account']-$Arraq[$i]['pay_account']));
							$inserArr[$i]['is_sort'] = 'long';
							$long_num +=1;
							
						}
						
						$inserArr[$i]['ordersn'] 	 = $Arraq[$i]['ordersn'];//$ordersn;
						$inserArr[$i]['upOrderId'] 	 = $Arraq[$i]['upOrderId'];
						$inserArr[$i]['pay_account'] = $Arraq[$i]['pay_account'];
						$inserArr[$i]['pay_type'] 	 = 'wx';
						$inserArr[$i]['date'] 	 	 = $startTime;
						$inserArr[$i]['is_ok'] 	 	 = 'no';
					}
					$flag = 1;
					/*echo '<pre>';
					print_r($inserArr);
					die;*/
					$insertUnion = Db::name('parking_mer_mistake')->insertAll($inserArr);
					$msg   = '长款1';
					
				} else {
					//$ordersn = 'G99198'.date('YmdHis',time()).mt_rand(010,999);
					$Arraq[] = json_decode($aq[0],true);
					$Arrsq[] = json_decode($sq[0],true);					
					$inserArr = [];
					if($Arraq[0]['pay_account'] > $Arrsq[0]['pay_account']) {
						$inserArr['money']  = sprintf("%.2f",($Arraq[0]['pay_account'] - $Arrsq[0]['pay_account']));
						$inserArr['is_sort'] = 'short';
						$short_num +=1;
					} else {
						$inserArr['money']   = sprintf("%.2f",($Arrsq[0]['pay_account']- $Arraq[0]['pay_account']));
						$inserArr['is_sort'] = 'long';
						$long_num +=1;
					}
					
					$inserArr['ordersn'] 	 = $Arraq[0]['ordersn'];
					$inserArr['upOrderId'] 	 = $Arraq[0]['upOrderId'];
					$inserArr['pay_account'] = $Arraq[0]['pay_account'];
					$inserArr['pay_type'] 	 = 'wx';
					$inserArr['date'] 	 	 = $startTime;
					$inserArr['is_ok'] 	 	 = 'no';
					$flag = 1;
					//把对应的差额数据写入差错表中
					//print_r($inserArr);die;
					$insertUnion = Db::name('parking_mer_mistake')->insert($inserArr);
					$msg   = '长款2';
				}
			} else {
				$msg   = '平账';
				$flag = 0;
			}
			
			$summary['date'] 	  = $startTime;//交易时间
			$summary['count'] 	  = $numaq;//交易总数
			$summary['ok_num']	  = $numsq;//平账数
			$summary['long_num']  = $long_num;//长款数
			$summary['short_num'] = $short_num;//短款数
			
			$summary['pay_account'] = $polyMoney;//交易总额
			$summary['fee']   	  = 0.01;//交易费率
			$summary['pay_fee']   = sprintf("%.2f",$polyMoney * 0.01);//交易费用
			$summary['pay_money'] = ($polyMoney - $summary['pay_fee']);//清算金额
			$summary['pay_type']  = 'wx';//支付通道
			$summary['order_check'] = 'no';//订单清算确认，no:未确认，yes:确认
			//parking_mer_summary
			$insertUnion = Db::name('parking_mer_summary')->insert($summary);
			//print_r($summary);
			
			/**
			 * 如果flag ==1 表示有长款  0表示平账
			 */
			if($flag ==1 ) {
				//发送异常通知
				$url = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/Analyaqs&type=wx';
				$this->SendEmail($url,'353453825@qq.com',$startTime.'您有微信免密，对账！请登录');
			} else {
				//发送平账通知
				$url = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/OkAnalyaqs&type=wx';
				$this->SendEmail($url,'353453825@qq.com',$startTime.'您有微信免密，对账！请登录');
			}
		
		} else if($numaq < $numsq) {
			//数据本地大于上游数据为：平台长款
			
			if(!empty($sq)) {
				$counts = count($sq);
				$inserArr = [];
				//金额差错
				if($counts > 1) {//错误数据大于1时需要循环处理
					for($i=0;$i<$counts;$i++) {
						//$ordersn = 'G99198'.date('YmdHis',time()).mt_rand(010,999);
						$Arrsq[] = json_decode($sq[$i],true);
						$inserArr[$i]['money'] 		 = sprintf("%.2f",$Arrsq[$i]['pay_account']);
						$inserArr[$i]['is_sort'] 	 = 'long';						
						$inserArr[$i]['ordersn'] 	 = $Arrsq[$i]['ordersn'];//$ordersn;
						$inserArr[$i]['upOrderId'] 	 = $Arrsq[$i]['upOrderId'];
						$inserArr[$i]['pay_account'] = $Arrsq[$i]['pay_account'];
						$inserArr[$i]['pay_type'] 	 = 'wx';
						$inserArr[$i]['date'] 	 	 = $startTime;
						$inserArr[$i]['is_ok'] 	 	 = 'no';
						$long_num += 1;
					}
					//写入差错表，批量
					//print_r($inserArr);
					$insertUnion = Db::name('parking_mer_mistake')->insertAll($inserArr);
				} else {
					$ordersn = 'G99198'.date('YmdHis',time()).mt_rand(010,999);
					$Arrsq[] = json_decode($sq[0],true);
					$inserArr['money']   	 = sprintf("%.2f",$Arrsq[0]['pay_account']);
					$inserArr['is_sort'] 	 = 'long';//长款				
					$inserArr['ordersn'] 	 = $Arrsq[0]['ordersn'];//$ordersn;
					$inserArr['upOrderId'] 	 = $Arrsq[0]['upOrderId'];
					$inserArr['pay_account'] = $Arrsq[0]['pay_account'];
					$inserArr['pay_type'] 	 = 'wx';
					$inserArr['date'] 	 	 = $startTime;
					$inserArr['is_ok'] 	 	 = 'no';
					$long_num += 1;
					//把对应的差额数据写入差错表中1
//					print_r($inserArr);   parking_mer_mistake
					$insertUnion = Db::name('parking_mer_mistake')->insert($inserArr);
				}
			}
			
			$summary['date'] 	  = $startTime;//交易时间
			$summary['count'] 	  = $numaq;//交易总数
			$summary['ok_num']	  = $numaq;//平账数
			$summary['long_num']  = $long_num;//长款数
			$summary['short_num'] = $short_num;//短款数
			$summary['pay_account'] = $polyMoney;//交易总额
			$summary['fee']   	  = 0.01;//交易费率
			$summary['pay_fee']   = sprintf("%.2f",$polyMoney * 0.01);//交易费用
			$summary['pay_money'] = sprintf("%.2f",($polyMoney - $summary['pay_fee']));//清算金额
			$summary['pay_type']  = 'wx';//支付通道
			$summary['order_check'] = 'no';//订单清算确认，no:未确认，yes:确认
			
			$insertUnion = Db::name('parking_mer_summary')->insert($summary);
			$msg   = '长款';
			//发送异常通知
			$url = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/Analyaqs&type=wx';
			$this->SendEmail($url,'353453825@qq.com',$startTime.'您有微信免密，对账！请登录');
			
		}else if($numaq > $numsq) {
			//上游数据大于本地 = 短款
			if(!empty($aq)) {
				$counts = count($aq);
				$inserArr = [];
				//金额差错
				if($counts > 1) {//错误数据大于1时需要循环处理
					for($i=0;$i<$counts;$i++) {
						//$ordersn = 'G99198'.date('YmdHis',time()).mt_rand(010,999);
						$Arraq[] = json_decode($aq[$i],true);
						$inserArr[$i]['money'] = sprintf("%.2f",$Arraq[$i]['pay_account']);
						$inserArr[$i]['is_sort'] = 'short';						
						$inserArr[$i]['ordersn'] 	 = $Arraq[$i]['ordersn'];
						$inserArr[$i]['upOrderId'] 	 = $Arraq[$i]['upOrderId'];
						$inserArr[$i]['pay_account'] = $Arraq[$i]['pay_account'];
						$inserArr[$i]['pay_type'] 	 = 'wx';
						$inserArr[$i]['date'] 	 	 = $startTime;
						$inserArr[$i]['is_ok'] 	 	 = 'no';
						$short_num +=1;
					}
					$insertUnion = Db::name('parking_mer_mistake')->insertAll($inserArr);
					//写入差错表，批量
					//print_r($inserArr);
				} else {
					//$ordersn = 'G99198'.date('YmdHis',time()).mt_rand(010,999);
					$Arraq[] = json_decode($aq[0],true);					
					$inserArr['money']   	 = sprintf("%.2f",$Arraq[0]['pay_account']);
					$inserArr['is_sort'] 	 = 'short';//长款				
					$inserArr['ordersn'] 	 = $Arraq[0]['ordersn'];
					$inserArr['upOrderId'] 	 = $Arraq[0]['upOrderId'];
					$inserArr['pay_account'] = $Arraq[0]['pay_account'];
					$inserArr['pay_type'] 	 = 'wx';
					$inserArr['date'] 	 	 = $startTime;
					$inserArr['is_ok'] 	 	 = 'no';
					$short_num +=1;
					$insertUnion = Db::name('parking_mer_mistake')->insert($inserArr);
					//把对应的差额数据写入差错表中
					//print_r($inserArr);
				}
			}
			
			//数据写入汇总表
			$summary['date'] 	  = $startTime;//交易时间
			$summary['count'] 	  = $numaq;//交易总数
			$summary['ok_num']	  = $numaq;//平账数
			$summary['long_num']  = $long_num;//长款数
			$summary['short_num'] = $short_num;//短款数			
			$summary['pay_account'] = $polyMoney;//交易总额
			$summary['fee']   	  = 0.01;//交易费率
			$summary['pay_fee']   = sprintf("%.2f",($polyMoney * 0.01));//交易费用
			$summary['pay_money'] = sprintf("%.2f",($polyMoney-$summary['pay_fee']));//清算金额
			$summary['pay_type']  = 'wx';//支付通道
			$summary['order_check'] = 'no';//订单清算确认，no:未确认，yes:确认
			$insertUnion = Db::name('parking_mer_summary')->insert($summary);
			$msg   = '短款';
			//发送异常通知       20181007040000882815
			$url = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/Analyaqs&type=wx';
			$this->SendEmail($url,'353453825@qq.com',$startTime.'您有微信免密，对账！请登录');
		}
		
		echo '微信免密订单对账：'.$msg.'《》订单时间：'.$startTime;
		//释放缓存
		$this->rs->del($keyaq);
		$this->rs->del($keysq);
	}
	
	
	/**
	 * 订单对账银联无感
	 * 费率：0.01
	 */
	public function AnalyUnions() {
		$keyaq = 'unionaq';//上游数据
		$keysq = 'unionsq';//本地数据
		//释放数据
		$this->rs->del($keyaq);
		$this->rs->del($keysq);
		//$startTime = date('Ymd',strtotime('-1 day'));
		$startTime = !empty(input('get.date')) ? input('get.date') :date('Ymd',strtotime('-1 day'));
		//$startTime = '20181007';
		$startTimes = strtotime($startTime.'00:00:00');//开始日期
		$endTime    = strtotime($startTime.'23:59:59');//结束日期
		
		$summaryOk = $this->db->table('parking_mer_summary')->where(['date'=>$startTime,'pay_type'=>'un'])->item();
		if(!empty($summaryOk)) {//订单已对账
			$url = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/OkAnalyaqs&type=un';
			$this->SendEmail($url,'353453825@qq.com',$startTime.'您当天的订单对账已完成，请勿重复执行！');//179078286
			echo '执行时间：'.date('Y-m-d H:i:s',time());
			die;
		}
		
		$where = [
			'pay_time'=>['between',"{$startTimes},{$endTime}"],
			'pay_status'=>1,
			'upOrderId'=>['neq',''],
			'pay_type'=>['eq','Parks'],
		];
		//本地数据
		$orderArr = Db::name('foll_order')->field('pay_account,upOrderId,ordersn')->where($where)->select();
		if(!empty($orderArr)) {
			//$orderArr = [];
			foreach($orderArr as $key=>$val) {
				$orderArr[$key]['pay_account'] = sprintf("%.2f",($val['pay_account']));
			}
			$polyMoneys = 0;
			//将数据库中查到的数据进行格式处理
			$ret = $this->rs->sMembers($keysq);
			foreach($orderArr as $k=>$v) {
				//本地数据总金额
				$polyMoneys += $v['pay_account'];
	    		$temp = json_encode($v);
	    		//写入缓存	 	将平台数据写入缓存
	    		if(empty($ret)) {
	    			//写入缓存中
	    			$this->rs->sAdd($keysq,$temp);
	    		}
			}
		}
		
		//银行数据
		$poly = $this->db->table('parking_pay_unionsecret')->field('pay_money,order_id,low_order_id')->where(['date'=>$startTime])->lists();
		if(!empty($poly)) {
			$polyArr = [];
			foreach($poly as $key=>$val) {
				$polyArr[$key]['pay_account'] = sprintf("%.2f",($val['pay_money']));
				$polyArr[$key]['upOrderId']   = $val['order_id'];
				$polyArr[$key]['ordersn']     = $val['low_order_id'];
			}
			$polyMoney = 0;
			$rets = $this->rs->sMembers($keyaq);
			foreach($polyArr as $k=>$v) {
				//本地数据总金额
				$polyMoney += $v['pay_account'];
	    		$temps = json_encode($v);
	    		//写入缓存	 	将平台数据写入缓存
	    		if(empty($rets)) {
	    			//写入缓存中
	    			$this->rs->sAdd($keyaq,$temps);
	    		}
			}
		}
		
		$numaq = $this->rs->scard($keyaq);//上游数据  数据总数
		$numsq = $this->rs->scard($keysq);//本地数据  数据总数
		
		//以上游数据为准   上游对下游
		$aq = $this->rs->sDiff($keyaq,$keysq);
		//下游为准
		$sq = $this->rs->sDiff($keysq,$keyaq);
		
		/*echo '<pre>';
		print_r($aq);
		echo '<hr>';
		print_r($sq);
		die;*/
		$short_num = 0;//短款数
		$long_num  = 0;//长款数
		$msg = '平账';
		//数据总数平账
		if($numaq == $numsq) {			
			$flag	   = 0;//是否平账标识   0平账，1长短款
			$flagArr   = [];
			if(!empty($aq) && !empty($sq)) {
				$counts = count($aq);
				//金额差错
				if($counts > 1) {//错误数据大于1时需要循环处理
					$inserArr=[];
					for($i=0;$i<$counts;$i++) {
						$Arraq[] = json_decode($aq[$i],true);
						$Arrsq[] = json_decode($sq[$i],true);
						
						if($Arraq[$i]['pay_account'] > $Arrsq[$i]['pay_account']) {
							$inserArr[$i]['money']  = sprintf("%.2f",($Arraq[$i]['pay_account'] - $Arrsq[$i]['pay_account']));
							$inserArr[$i]['is_sort'] = 'short';
							$short_num +=1;
						} else {
							$inserArr[$i]['money'] = sprintf("%.2f",($Arrsq[$i]['pay_account']-$Arraq[$i]['pay_account']));
							$inserArr[$i]['is_sort'] = 'long';
							$long_num +=1;
						}
						
						$inserArr[$i]['ordersn'] 	 = $Arraq[$i]['ordersn'];
						$inserArr[$i]['upOrderId'] 	 = $Arraq[$i]['upOrderId'];
						$inserArr[$i]['pay_account'] = $Arraq[$i]['pay_account'];
						$inserArr[$i]['pay_type'] 	 = 'un';
						$inserArr[$i]['date'] 	 	 = $startTime;
						$inserArr[$i]['is_ok'] 	 	 = 'no';
					}
					$flag = 1;
					/*echo '<pre>';
					print_r($inserArr);
					die;*/
					$insertUnion = Db::name('parking_mer_mistake')->insertAll($inserArr);
					$msg   = '长款1';
					
				} else {
					
					$Arraq[] = json_decode($aq[0],true);
					$Arrsq[] = json_decode($sq[0],true);					
					$inserArr = [];
					if($Arraq[0]['pay_account'] > $Arrsq[0]['pay_account']) {
						//$inserArr[$i]['money']  = sprintf("%.2f",($Arraq[$i]['pay_account'] - $Arrsq[$i]['pay_account']));
						$inserArr['money']  = sprintf("%.2f",($Arraq[0]['pay_account'] - $Arrsq[0]['pay_account']));
						$inserArr['is_sort'] = 'short';
						$short_num +=1;
					} else {
						//$inserArr[$i]['money'] = sprintf("%.2f",($Arrsq[$i]['pay_account']-$Arraq[$i]['pay_account']));
						$inserArr['money']   = sprintf("%.2f",($Arrsq[0]['pay_account']- $Arraq[0]['pay_account']));
						$inserArr['is_sort'] = 'long';
						$long_num +=1;
					}
					
					$inserArr['ordersn'] 	 = $Arraq[0]['ordersn'];
					$inserArr['upOrderId'] 	 = $Arraq[0]['upOrderId'];
					$inserArr['pay_account'] = $Arraq[0]['pay_account'];
					$inserArr['pay_type'] 	 = 'un';
					$inserArr['date'] 	 	 = $startTime;
					$inserArr['is_ok'] 	 	 = 'no';
					$flag = 1;
					//把对应的差额数据写入差错表中
					//print_r($inserArr);die;
					$insertUnion = Db::name('parking_mer_mistake')->insert($inserArr);
					$msg   = '长款2';
				}
			} else {
				$msg   = '平账';
				$flag = 0;
			}
			
			$summary['date'] 	  = $startTime;//交易时间
			$summary['count'] 	  = $numaq;//交易总数
			$summary['ok_num']	  = $numsq;//平账数
			$summary['long_num']  = $long_num;//长款数
			$summary['short_num'] = $short_num;//短款数
			
			$summary['pay_account'] = $polyMoney;//交易总额
			$summary['fee']   	  = 0.01;//交易费率
			$summary['pay_fee']   = sprintf("%.2f",($polyMoney * 0.01));//交易费用
			$summary['pay_money'] = sprintf("%.2f",($polyMoney-$summary['pay_fee']));//清算金额
			$summary['pay_type']  = 'un';//支付通道
			$summary['order_check'] = 'no';//订单清算确认，no:未确认，yes:确认
			//parking_mer_summary
			$insertUnion = Db::name('parking_mer_summary')->insert($summary);
			//print_r($summary);
			
			/**
			 * 如果flag ==1 表示有长款  0表示平账
			 */
			if($flag ==1 ) {
				//发送异常通知
				$url = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/Analyaqs&type=un';
				$this->SendEmail($url,'353453825@qq.com',$startTime.'您有银联无感，对账！请登录');
			} else {
				//发送平账通知
				$url = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/OkAnalyaqs&type=un';
				$this->SendEmail($url,'353453825@qq.com',$startTime.'您有银联无感，对账！请登录');
			}
		
		} else if($numaq < $numsq) {
			//数据本地大于上游数据为：平台长款
			
			if(!empty($sq)) {
				$counts = count($sq);
				$inserArr = [];
				//金额差错
				if($counts > 1) {//错误数据大于1时需要循环处理
					for($i=0;$i<$counts;$i++) {
						$Arrsq[] = json_decode($sq[$i],true);
						$inserArr[$i]['money'] 		 = sprintf("%.2f",$Arrsq[$i]['pay_account']);
						$inserArr[$i]['is_sort'] 	 = 'long';						
						$inserArr[$i]['ordersn'] 	 = $Arrsq[$i]['ordersn'];
						$inserArr[$i]['upOrderId'] 	 = $Arrsq[$i]['upOrderId'];
						$inserArr[$i]['pay_account'] = $Arrsq[$i]['pay_account'];
						$inserArr[$i]['pay_type'] 	 = 'un';
						$inserArr[$i]['date'] 	 	 = $startTime;
						$inserArr[$i]['is_ok'] 	 	 = 'no';
						$long_num += 1;
					}
					//写入差错表，批量
					//print_r($inserArr);
					$insertUnion = Db::name('parking_mer_mistake')->insertAll($inserArr);
				} else {
					$Arrsq = json_decode($sq[0],true);
					$inserArr['money']   	 = sprintf("%.2f",$Arrsq['pay_account']);
					$inserArr['is_sort'] 	 = 'long';//长款				
					$inserArr['ordersn'] 	 = $Arrsq['ordersn'];
					$inserArr['upOrderId'] 	 = $Arrsq['upOrderId'];
					$inserArr['pay_account'] = $Arrsq['pay_account'];
					$inserArr['pay_type'] 	 = 'un';
					$inserArr['date'] 	 	 = $startTime;
					$inserArr['is_ok'] 	 	 = 'no';
					$long_num += 1;
					//把对应的差额数据写入差错表中1
//					print_r($inserArr);   parking_mer_mistake
					$insertUnion = Db::name('parking_mer_mistake')->insert($inserArr);
				}
			}
			
			$summary['date'] 	  = $startTime;//交易时间
			$summary['count'] 	  = $numaq;//交易总数
			$summary['ok_num']	  = $numaq;//平账数
			$summary['long_num']  = $long_num;//长款数
			$summary['short_num'] = $short_num;//短款数
			$summary['pay_account'] = $polyMoney;//交易总额
			$summary['fee']   	  = 0.01;//交易费率
			$summary['pay_fee']   = sprintf("%.2f",$polyMoney * 0.01);//交易费用
			$summary['pay_money'] = ($polyMoney-$summary['pay_fee']);//清算金额
			$summary['pay_type']  = 'un';//支付通道
			$summary['order_check'] = 'no';//订单清算确认，no:未确认，yes:确认
			
			$insertUnion = Db::name('parking_mer_summary')->insert($summary);
			$msg   = '长款';
			//发送异常通知
			$url = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/Analyaqs&type=un';
			$this->SendEmail($url,'353453825@qq.com',$startTime.'您有银联无感，对账！请登录');
			
		}else if($numaq > $numsq) {
			//上游数据大于本地 = 短款
			if(!empty($aq)) {
				$counts = count($aq);
				$inserArr = [];
				//金额差错
				if($counts > 1) {//错误数据大于1时需要循环处理
					for($i=0;$i<$counts;$i++) {
						$Arraq[] = json_decode($aq[$i],true);
						$inserArr[$i]['money'] = sprintf("%.2f",$Arraq[$i]['pay_account']);
						$inserArr[$i]['is_sort'] = 'short';						
						$inserArr[$i]['ordersn'] 	 = $Arraq[$i]['ordersn'];
						$inserArr[$i]['upOrderId'] 	 = $Arraq[$i]['upOrderId'];
						$inserArr[$i]['pay_account'] = $Arraq[$i]['pay_account'];
						$inserArr[$i]['pay_type'] 	 = 'un';
						$inserArr[$i]['date'] 	 	 = $startTime;
						$inserArr[$i]['is_ok'] 	 	 = 'no';
						$short_num +=1;
					}
					$insertUnion = Db::name('parking_mer_mistake')->insertAll($inserArr);
					//写入差错表，批量
					//print_r($inserArr);
				} else {
					
					$Arraq = json_decode($aq[0],true);
					//print_r($Arraq);
					$inserArr['money']   	 = sprintf("%.2f",$Arraq['pay_account']);
					$inserArr['is_sort'] 	 = 'short';//长款
					$inserArr['ordersn'] 	 = $Arraq['ordersn'];
					$inserArr['upOrderId'] 	 = $Arraq['upOrderId'];
					$inserArr['pay_account'] = $Arraq['pay_account'];
					$inserArr['pay_type'] 	 = 'un';
					$inserArr['date'] 	 	 = $startTime;
					$inserArr['is_ok'] 	 	 = 'no';
					$short_num +=1;
					$insertUnion = Db::name('parking_mer_mistake')->insert($inserArr);
					//把对应的差额数据写入差错表中
					//print_r($inserArr);
				}
			}
			
			//数据写入汇总表
			$summary['date'] 	  = $startTime;//交易时间
			$summary['count'] 	  = $numaq;//交易总数
			$summary['ok_num']	  = $numaq;//平账数
			$summary['long_num']  = $long_num;//长款数
			$summary['short_num'] = $short_num;//短款数			
			$summary['pay_account'] = $polyMoney;//交易总额
			$summary['fee']   	  = 0.01;//交易费率
			$summary['pay_fee']   = sprintf("%.2f",$polyMoney * 0.01);//交易费用
			$summary['pay_money'] = ($polyMoney-$summary['pay_fee']);//清算金额
			$summary['pay_type']  = 'un';//支付通道
			$summary['order_check'] = 'no';//订单清算确认，no:未确认，yes:确认
			$insertUnion = Db::name('parking_mer_summary')->insert($summary);
			$msg   = '短款';
			//发送异常通知       20181007040000882815
			$url = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/Analyaqs&type=un';
			$this->SendEmail($url,'353453825@qq.com',$startTime.'您有银联无感，对账！请登录');
		}
		
		echo '银联无感订单对账: '.$msg.'《》订单时间：'.$startTime;
		//释放缓存
		$this->rs->del($keyaq);
		$this->rs->del($keysq);
	}
	
	
	/**
	 * 顺德农商订单对账
	 * 费率：0
	 */
	public function AnalySdes() {
		$keyaq = 'sdeaq';//上游数据
		$keysq = 'sdesq';//本地数据
		//释放缓存
		$this->rs->del($keyaq);
		$this->rs->del($keysq);
		//$startTime = date('Ymd',strtotime('-1 day'));
		$startTime = !empty(input('get.date')) ? input('get.date') :date('Ymd',strtotime('-1 day'));
		//$startTime = '20181007';
		$startTimes = strtotime($startTime.'00:00:00');//开始日期
		$endTime    = strtotime($startTime.'23:59:59');//结束日期
		
		$summaryOk = $this->db->table('parking_mer_summary')->where(['date'=>$startTime,'pay_type'=>'sd'])->item();
		if(!empty($summaryOk)) {//订单已对账
			$url = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/OkAnalyaqs&type=sd';
			$this->SendEmail($url,'353453825@qq.com',$startTime.'您当天的订单对账已完成，请勿重复执行！');//179078286
			echo '执行时间：'.date('Y-m-d H:i:s',time());
			die;
		}
		
		$where = [
			//'pay_time'=>['between',"{$startTimes},{$endTime}"],
			'PlatDate' =>$startTime,
			'pay_status'=>1,
			'upOrderId'=>['neq',''],
			'pay_type'=>['eq','FAgro'],
		];
		//本地数据
		$orderArr = Db::name('foll_order')->field('pay_account,upOrderId,ordersn,PlatDate')->where($where)->select();
		if(!empty($orderArr)) {
			//$orderArr = [];
			foreach($orderArr as $key=>$val) {
				$orderArr[$key]['pay_account']  = sprintf("%.2f",($val['pay_account']));
				$orderArr[$key]['upOrderId']  	= trim($val['upOrderId']);
				$orderArr[$key]['ordersn']  	= trim($val['ordersn']);
				$orderArr[$key]['PlatDate']  	= trim($val['PlatDate']);
			}
			$polyMoneys = 0;
			//将数据库中查到的数据进行格式处理
			$ret = $this->rs->sMembers($keysq);
			foreach($orderArr as $k=>$v) {
				//本地数据总金额
				$polyMoneys += $v['pay_account'];
	    		$temp = json_encode($v);
	    		//写入缓存	 	将平台数据写入缓存
	    		if(empty($ret)) {
	    			//写入缓存中
	    			$this->rs->sAdd($keysq,$temp);
	    		}
			}
		}
		
		//银行数据
		$poly = $this->db->table('parking_pay_sdesecret')->field('pay_money,bank_orderid,pay_ordersn,bank_date')->where(['date'=>$startTime])->lists();
		if(!empty($poly)) {
			$polyArr = [];
			foreach($poly as $key=>$val) {
				$polyArr[$key]['pay_account']   = sprintf("%.2f",($val['pay_money']));
				$polyArr[$key]['upOrderId']		= trim($val['bank_orderid']);
				$polyArr[$key]['ordersn'] 		= trim($val['pay_ordersn']);
				$polyArr[$key]['PlatDate'] 		= trim($val['bank_date']);
			}
			$polyMoney = 0;
			$rets = $this->rs->sMembers($keyaq);
			foreach($polyArr as $k=>$v) {
				//本地数据总金额
				$polyMoney += $v['pay_account'];
	    		$temps = json_encode($v);
	    		//写入缓存	 	将平台数据写入缓存
	    		if(empty($rets)) {
	    			//写入缓存中
	    			$this->rs->sAdd($keyaq,$temps);
	    		}
			}
		}
		
		$numaq = $this->rs->scard($keyaq);//上游数据  数据总数
		$numsq = $this->rs->scard($keysq);//本地数据  数据总数
		
		//以上游数据为准   上游对下游
		$aq = $this->rs->sDiff($keyaq,$keysq);
		//下游为准
		$sq = $this->rs->sDiff($keysq,$keyaq);
		
		$short_num = 0;//短款数
		$long_num  = 0;//长款数
		$msg = '平账';
		//数据总数平账
		if($numaq == $numsq) {			
			$flag	   = 0;//是否平账标识   0平账，1长短款
			$flagArr   = [];
			if(!empty($aq) && !empty($sq)) {
				$counts = count($aq);
				//金额差错
				if($counts > 1) {//错误数据大于1时需要循环处理
					$inserArr=[];
					for($i=0;$i<$counts;$i++) {
						$Arraq[] = json_decode($aq[$i],true);
						$Arrsq[] = json_decode($sq[$i],true);
						
						if($Arraq[$i]['pay_account'] > $Arrsq[$i]['pay_account']) {
							$inserArr[$i]['money']  = sprintf("%.2f",($Arraq[$i]['pay_account'] - $Arrsq[$i]['pay_account']));
							$inserArr[$i]['is_sort'] = 'short';
							$short_num +=1;
						} else {
							$inserArr[$i]['money'] = sprintf("%.2f",($Arrsq[$i]['pay_account']-$Arraq[$i]['pay_account']));
							$inserArr[$i]['is_sort'] = 'long';
							$long_num +=1;
						}
						
						$inserArr[$i]['ordersn'] 	 = $Arraq[$i]['ordersn'];
						$inserArr[$i]['upOrderId'] 	 = $Arraq[$i]['upOrderId'];
						$inserArr[$i]['pay_account'] = $Arraq[$i]['pay_account'];
						$inserArr[$i]['pay_type'] 	 = 'sd';
						$inserArr[$i]['date'] 	 	 = $startTime;
						$inserArr[$i]['is_ok'] 	 	 = 'no';
					}
					$flag = 1;
					/*echo '<pre>';
					print_r($inserArr);
					die;*/
					$insertUnion = Db::name('parking_mer_mistake')->insertAll($inserArr);
					$msg   = '长款1';
					
				} else {
					
					$Arraq[] = json_decode($aq[0],true);
					$Arrsq[] = json_decode($sq[0],true);					
					$inserArr = [];
					if($Arraq[0]['pay_account'] > $Arrsq[0]['pay_account']) {
						//$inserArr[$i]['money']  = sprintf("%.2f",($Arraq[$i]['pay_account'] - $Arrsq[$i]['pay_account']));
						$inserArr['money']  = sprintf("%.2f",($Arraq[0]['pay_account'] - $Arrsq[0]['pay_account']));
						$inserArr['is_sort'] = 'short';
						$short_num +=1;
					} else {
						//$inserArr[$i]['money'] = sprintf("%.2f",($Arrsq[$i]['pay_account']-$Arraq[$i]['pay_account']));
						$inserArr['money']   = sprintf("%.2f",($Arrsq[0]['pay_account']- $Arraq[0]['pay_account']));
						$inserArr['is_sort'] = 'long';
						$long_num +=1;
					}
					
					$inserArr['ordersn'] 	 = $Arraq[0]['ordersn'];
					$inserArr['upOrderId'] 	 = $Arraq[0]['upOrderId'];
					$inserArr['pay_account'] = $Arraq[0]['pay_account'];
					$inserArr['pay_type'] 	 = 'sd';
					$inserArr['date'] 	 	 = $startTime;
					$inserArr['is_ok'] 	 	 = 'no';
					$flag = 1;
					//把对应的差额数据写入差错表中
					//print_r($inserArr);die;    G9919820181007477478386
					$insertUnion = Db::name('parking_mer_mistake')->insert($inserArr);
					$msg   = '长款2';
				}
			} else {
				$msg   = '平账';
				$flag = 0;
			}
			
			$summary['date'] 	  = $startTime;//交易时间
			$summary['count'] 	  = $numaq;//交易总数
			$summary['ok_num']	  = $numsq;//平账数
			$summary['long_num']  = $long_num;//长款数
			$summary['short_num'] = $short_num;//短款数
			
			$summary['pay_account'] = $polyMoney;//交易总额
			$summary['fee']   	  = 0.00;//交易费率
			$summary['pay_fee']   = 0.00;//交易费用
			$summary['pay_money'] = ($polyMoney-$summary['pay_fee']);//清算金额
			$summary['pay_type']  = 'sd';//支付通道
			$summary['order_check'] = 'no';//订单清算确认，no:未确认，yes:确认
			//parking_mer_summary
			$insertUnion = Db::name('parking_mer_summary')->insert($summary);
			//print_r($summary);
			
			/**
			 * 如果flag ==1 表示有长款  0表示平账
			 */
			if($flag ==1 ) {
				//发送异常通知
				$url = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/Analyaqs&type=sd';
				$this->SendEmail($url,'353453825@qq.com',$startTime."您有农商代扣，对账！请登录");
			} else {
				//发送平账通知
				$url = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/OkAnalyaqs&type=sd';
				$this->SendEmail($url,'353453825@qq.com',$startTime.'您有农商代扣，对账！请登录');
			}
		
		} else if($numaq < $numsq) {
			//数据本地大于上游数据为：平台长款
			
			if(!empty($sq)) {
				$counts = count($sq);
				$inserArr = [];
				//金额差错
				if($counts > 1) {//错误数据大于1时需要循环处理
					for($i=0;$i<$counts;$i++) {
						$Arrsq[] = json_decode($sq[$i],true);
						$inserArr[$i]['money'] 		 = sprintf("%.2f",$Arrsq[$i]['pay_account']);
						$inserArr[$i]['is_sort'] 	 = 'long';						
						$inserArr[$i]['ordersn'] 	 = $Arrsq[$i]['ordersn'];
						$inserArr[$i]['upOrderId'] 	 = $Arrsq[$i]['upOrderId'];
						$inserArr[$i]['pay_account'] = $Arrsq[$i]['pay_account'];
						$inserArr[$i]['pay_type'] 	 = 'sd';
						$inserArr[$i]['date'] 	 	 = $startTime;
						$inserArr[$i]['is_ok'] 	 	 = 'no';
						$long_num += 1;
					}
					//写入差错表，批量
					//print_r($inserArr);
					$insertUnion = Db::name('parking_mer_mistake')->insertAll($inserArr);
				} else {
					$Arrsq = json_decode($sq[0],true);
					$inserArr['money']   	 = sprintf("%.2f",$Arrsq['pay_account']);
					$inserArr['is_sort'] 	 = 'long';//长款				
					$inserArr['ordersn'] 	 = $Arrsq['ordersn'];
					$inserArr['upOrderId'] 	 = $Arrsq['upOrderId'];
					$inserArr['pay_account'] = $Arrsq['pay_account'];
					$inserArr['pay_type'] 	 = 'sd';
					$inserArr['date'] 	 	 = $startTime;
					$inserArr['is_ok'] 	 	 = 'no';
					$long_num += 1;
					//把对应的差额数据写入差错表中1
//					print_r($inserArr);   parking_mer_mistake
					$insertUnion = Db::name('parking_mer_mistake')->insert($inserArr);
				}
			}
			
			$summary['date'] 	  = $startTime;//交易时间
			$summary['count'] 	  = $numaq;//交易总数
			$summary['ok_num']	  = $numaq;//平账数
			$summary['long_num']  = $long_num;//长款数
			$summary['short_num'] = $short_num;//短款数
			$summary['pay_account'] = $polyMoney;//交易总额
			$summary['fee']   	  = 0.00;//交易费率
			$summary['pay_fee']   = 0.00;//交易费用
			$summary['pay_money'] = ($polyMoney-$summary['pay_fee']);//清算金额
			$summary['pay_type']  = 'sd';//支付通道
			$summary['order_check'] = 'no';//订单清算确认，no:未确认，yes:确认
			
			$insertUnion = Db::name('parking_mer_summary')->insert($summary);
			$msg   = '长款';
			//发送异常通知
			$url = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/Analyaqs&type=sd';
			$this->SendEmail($url,'353453825@qq.com',$startTime.'您有农商代扣，对账！请登录');
			
		}else if($numaq > $numsq) {
			//上游数据大于本地 = 短款
			if(!empty($aq)) {
				$counts = count($aq);
				$inserArr = [];
				//金额差错
				if($counts > 1) {//错误数据大于1时需要循环处理
					for($i=0;$i<$counts;$i++) {
						$Arraq[] = json_decode($aq[$i],true);
						$inserArr[$i]['money'] = sprintf("%.2f",$Arraq[$i]['pay_account']);
						$inserArr[$i]['is_sort'] = 'short';						
						$inserArr[$i]['ordersn'] 	 = $Arraq[$i]['ordersn'];
						$inserArr[$i]['upOrderId'] 	 = $Arraq[$i]['upOrderId'];
						$inserArr[$i]['pay_account'] = $Arraq[$i]['pay_account'];
						$inserArr[$i]['pay_type'] 	 = 'sd';
						$inserArr[$i]['date'] 	 	 = $startTime;
						$inserArr[$i]['is_ok'] 	 	 = 'no';
						$short_num +=1;
					}
					$insertUnion = Db::name('parking_mer_mistake')->insertAll($inserArr);
					//写入差错表，批量
					//print_r($inserArr);
				} else {
					
					$Arraq = json_decode($aq[0],true);					
					$inserArr['money']   	 = sprintf("%.2f",$Arraq['pay_account']);
					$inserArr['is_sort'] 	 = 'short';//长款				
					$inserArr['ordersn'] 	 = $Arraq['ordersn'];
					$inserArr['upOrderId'] 	 = $Arraq['upOrderId'];
					$inserArr['pay_account'] = $Arraq['pay_account'];
					$inserArr['pay_type'] 	 = 'sd';
					$inserArr['date'] 	 	 = $startTime;
					$inserArr['is_ok'] 	 	 = 'no';
					$short_num +=1;
					$insertUnion = Db::name('parking_mer_mistake')->insert($inserArr);
					//把对应的差额数据写入差错表中
					//print_r($inserArr);
				}
			}
			
			//数据写入汇总表
			$summary['date'] 	  = $startTime;//交易时间
			$summary['count'] 	  = $numaq;//交易总数
			$summary['ok_num']	  = $numaq;//平账数
			$summary['long_num']  = $long_num;//长款数
			$summary['short_num'] = $short_num;//短款数			
			$summary['pay_account'] = $polyMoney;//交易总额
			$summary['fee']   	  = 0.00;//交易费率
			$summary['pay_fee']   = 0.00;//交易费用
			$summary['pay_money'] = ($polyMoney-$summary['pay_fee']);//清算金额
			$summary['pay_type']  = 'sd';//支付通道
			$summary['order_check'] = 'no';//订单清算确认，no:未确认，yes:确认
			$insertUnion = Db::name('parking_mer_summary')->insert($summary);
			$msg   = '短款';
			//发送异常通知       20181007040000882815
			$url   = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/Analyaqs&type=sd';
			$this->SendEmail($url,'353453825@qq.com',$startTime.'您有农商代扣，对账！请登录');
		}
		
		echo '农商行订单对账: '.$msg.'《》订单时间：'.$startTime;
		//释放缓存
		$this->rs->del($keyaq);
		$this->rs->del($keysq);
	}

	
	/**
	 * 支付汇总
	 */
	public function PaymentSummary($day='')
	{
		$startTime = date('Ymd',strtotime('-1 day'));
		//$startTime = $day;
		//$startTime = '20181009';
		/**
		控制发送    判断parking_bill  有数据代表已经发送过，没有数据代表还没发送过
		**/
		$bill		= Db::name('parking_bill')->where(['date'=>$startTime])->select();
		if(!empty($bill)){//有数据不允许发送
			return '数据已发送，不允再次发送 时间：'.$startTime;
		}
		
		//没有数据发送一次，并生成发送记录
		$MerSummary = $this->db->table('parking_mer_summary')->field('count,pay_money,pay_fee,pay_type')->where(['date'=>$startTime,'order_check'=>'yes'])->order('pay_type desc')->lists();
		$PaySummary = $this->db->table('parking_pay_summary')->field('pay_money,pay_fee,pay_type,total_money')->where(['date'=>$startTime])->order('pay_type desc')->lists();
		if(empty($MerSummary) && empty($PaySummary)){			
			return '暂无数据 时间：'.$startTime;
		}
		
		ksort($MerSummary);//数组排序
		ksort($PaySummary);//数组排序
		
		$NewArrs = [];
		$counts  = count($MerSummary);
		for($i=0;$i<$counts;$i++) {
			
			if(($MerSummary[$i]['pay_type']=='wx') && ($PaySummary[$i]['pay_type']=='wx')) {
				$NewArrs[$i]['count'] 	   = $MerSummary[$i]['count'];//交易笔数：
				$NewArrs[$i]['mpay_money'] = sprintf("%.2f",($MerSummary[$i]['pay_money']));//应付商户清算
				$NewArrs[$i]['mpay_fee']   = sprintf("%.2f",($MerSummary[$i]['pay_fee']));//应收商户手费
				$NewArrs[$i]['pay_type']   = '微信免密';//支付通道
				
				$NewArrs[$i]['pay_money']  = sprintf("%.2f",($PaySummary[$i]['pay_money']-$PaySummary[$i]['pay_fee']));//应收支付清算
				$NewArrs[$i]['pay_fee']    = sprintf("%.2f",($PaySummary[$i]['pay_fee']));//应付支付手费
				$NewArrs[$i]['summary']    = sprintf("%.2f",($PaySummary[$i]['total_money']));//交易总额：
				$NewArrs[$i]['date']	   = $startTime;//账单日期
			}
			
			if(($MerSummary[$i]['pay_type']=='un') && ($PaySummary[$i]['pay_type']=='union')) {
				$NewArrs[$i]['count'] 	   = $MerSummary[$i]['count'];//交易笔数：
				$NewArrs[$i]['mpay_money'] = sprintf("%.2f",($MerSummary[$i]['pay_money']));//应付商户清算
				$NewArrs[$i]['mpay_fee']   = sprintf("%.2f",($MerSummary[$i]['pay_fee']));//应收商户手费
				$NewArrs[$i]['pay_type']   = '银联无感';//支付通道
				
				$NewArrs[$i]['pay_money']  = sprintf("%.2f",($PaySummary[$i]['pay_money']-$PaySummary[$i]['pay_fee']));//应收支付清算
				$NewArrs[$i]['pay_fee']    = sprintf("%.2f",($PaySummary[$i]['pay_fee']));//应付支付手费
				$NewArrs[$i]['summary']    = sprintf("%.2f",($PaySummary[$i]['total_money']));//交易总额：
				$NewArrs[$i]['date']	   = $startTime;//账单日期
			}
			
			if(($MerSummary[$i]['pay_type']=='aq') && ($PaySummary[$i]['pay_type']=='aq')) {
				$NewArrs[$i]['count'] 	   = $MerSummary[$i]['count'];//交易笔数：
				$NewArrs[$i]['mpay_money'] = sprintf("%.2f",($MerSummary[$i]['pay_money']));//应付商户清算
				$NewArrs[$i]['mpay_fee']   = sprintf("%.2f",($MerSummary[$i]['pay_fee']));//应收商户手费
				$NewArrs[$i]['pay_type']   = '聚合支付';//支付通道
				
				$NewArrs[$i]['pay_money']  = sprintf("%.2f",($PaySummary[$i]['pay_money']-$PaySummary[$i]['pay_fee']));//应收支付清算
				$NewArrs[$i]['pay_fee']    = sprintf("%.2f",($PaySummary[$i]['pay_fee']));//应付支付手费
				$NewArrs[$i]['summary']    = sprintf("%.2f",($PaySummary[$i]['total_money']));//交易总额：
				$NewArrs[$i]['date']	   = $startTime;//账单日期
			}
			
			if(($MerSummary[$i]['pay_type']=='sd') && ($PaySummary[$i]['pay_type']=='sde')) {
				$NewArrs[$i]['count'] 	   = $MerSummary[$i]['count'];//交易笔数：
				$NewArrs[$i]['mpay_money'] = sprintf("%.2f",($MerSummary[$i]['pay_money']));//应付商户清算
				$NewArrs[$i]['mpay_fee']   = sprintf("%.2f",($MerSummary[$i]['pay_fee']));//应收商户手费
				$NewArrs[$i]['pay_type']   = '农商代扣';//支付通道
				
				$NewArrs[$i]['pay_money']  = sprintf("%.2f",($PaySummary[$i]['pay_money']-$PaySummary[$i]['pay_fee']));//应收支付清算
				$NewArrs[$i]['pay_fee']    = sprintf("%.2f",($PaySummary[$i]['pay_fee']));//应付支付手费
				$NewArrs[$i]['summary']    = sprintf("%.2f",($PaySummary[$i]['total_money']));//交易总额：
				$NewArrs[$i]['date']	   = $startTime;//账单日期
			}
			
			echo '订单支付类型：'.$MerSummary[$i]['pay_type'].'<>银企支付类型：'.$PaySummary[$i]['pay_type'].'<br>';
			
		}
		
		unset($MerSummary);
		unset($PaySummary);
		
		if(!empty($NewArrs)) {
			//数据写入 清单汇总表 
			Db::name('parking_pay_listsummary')->insertAll($NewArrs);
			
			//Db::name('parking_bill')->where(['date'=>$startTime])->update(['is_send'=>'yes']);
			$insert = ['date'=>$startTime];
			$this->db->table('parking_bill')->insert($insert);
			
			$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
	  		$PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
	  		$PHPSheet->setTitle('订单汇总'.$startTime); //给当前活动sheet设置名称
	  		$PHPSheet->setCellValue('A1','支付通道')
	  				 ->setCellValue('B1','交易笔数')
	  				 ->setCellValue('C1','交易总额')
	  				 ->setCellValue('D1','应付支付手费')
	  				 ->setCellValue('E1','应收支付清算')
	  				 ->setCellValue('F1','应收商户手费')
	  				 ->setCellValue('G1','应付商户清算')
	  				 ->setCellValue('H1','账单日期');
	  		
	  		$count = count($NewArrs);
	  		$num = 0;
	  		for($i=0; $i < $count; $i++) {
	  			$num = 2+$i;
	  			$PHPSheet->setCellValue("A".$num,$NewArrs[$i]['pay_type'])
	  				 ->setCellValue('B'.$num,$NewArrs[$i]['count'])//"\t".$polyArrs[$i]['low_order_id']."\t"
	  				 ->setCellValue('C'.$num,$NewArrs[$i]['summary'])
	  				 ->setCellValue('D'.$num,$NewArrs[$i]['pay_fee'])
	  				 ->setCellValue('E'.$num,$NewArrs[$i]['pay_money'])
	  				 ->setCellValue("F".$num,$NewArrs[$i]['mpay_fee'])
	  				 ->setCellValue("G".$num,$NewArrs[$i]['mpay_money'])
	  				 ->setCellValue("H".$num,$startTime);
	  		}
	  		
	  		$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
	  		$fileName  = $startTime.'.xlsx';
	  		$path      = dirname(__FILE__).'/'.$fileName;
			$PHPWriter->save($path);
			$msg	   = '您好，您有'.$startTime.'日停车清算日结，请下载查看！';
			$subject   = '您有'.$startTime.'日停车清算日结，请查看';
			if($this->SendEmails($msg,'198@gogo198.net',$path,$subject)) {
			//if($this->SendEmails($msg,'353453825@qq.com',$path,$subject)) {
				unlink($path);
				echo '订单导出成功！ 时间：'.$startTime;
			} else {
				unlink($path);
				echo '订单导出失败！ 时间：'.$startTime;
			}
			
		}
		
		/*
		 *  支付通道：
			交易笔数：
			交易总额：
			
			应付支付手费
			应收支付清算
			
			应收商户手费
			应付商户清算
		 */
		
		/**
		 * 开发分析：
		 * 通过订单日期查询：如20181010   查银企对账汇总
		 * 				如20181010    查银订单对账汇总
		 * 银企通道标识：微信免密(wx)、银联无感(union)、农商代扣(sde)、聚合支付(aq)  共同字段：date
		 * 订单通道标识：微信免密(wx)、银联无感(un)、农商代扣(sd)、聚合支付(aq)
		 * 开发思路：
		 * 1、使用订单日分别查查两个汇总表的数据
		 * 2、parking_mer_summary(date,count,pay_account,pay_fee,pay_money);
		 *    parking_pay_summary(date,pay_sum,total_money,pay_fee)
		 */
		
	}

	//发送电子邮件
	public function SendEmails($content,$email='353453825@qq.com',$path='',$subject = '您有订单汇总导出，请查看！') {
		$name    = '系统管理员';
		if(!empty($path)){
			$status  = send_mail($email,$name,$subject,$content,['0'=>$path]);
		} else {
			$status  = send_mail($email,$name,$subject,$content);
		}
		//$content = "提示：您有对账信息，请及时登录查看并确认！,您可请登录后台【<a href='".$url."'>点击前往查看</a>】";
		if($status) {
			return true;
		} else {
			return false;
		}
	}
	
	//发送电子邮件
	public function SendEmail($url,$email='353453825@qq.com',$subject = '您有对账信息，请登录查看！',$path='true') {
		$name    = '系统管理员';
		$content = "提示：您有对账信息，请及时登录查看并确认！,您可请登录后台【<a href='".$url."'>点击前往查看</a>】";
    	if($path == 'true'){//没有数据发送
    		$status  = send_mail($email,$name,$subject,$content);
    	} else {
    		$status  = send_mail($email,$name,$subject,$content,['0'=>$path]);
    	}
		if($status) {
			return true;
		} else {
			return false;
		}
	}
	
}

?>