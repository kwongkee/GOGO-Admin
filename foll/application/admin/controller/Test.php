<?php
/**
 * @author 赵金如
 * @date   2018-09-05
 * 测试文件
 */
namespace app\admin\controller;
use think\Db;
use Util\data\Redis;
use Util\data\Sysdb;

class Test// extends Auth
{
    public $rs;
    /*public function __construct(){
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
    }*/

	public function index(){
		echo '开始';
		$userId   = trim('452424199003141634');//身份证
		$realname = urldecode('赵金如');//姓名
		$method = "GET";
		$headers = array();
	    array_push($headers, "Authorization:504fd5f6a735437c97cd117e61cb4a24");
	    $querys = "idcard={$userId}&realname={$realname}";
	    $bodys = "";
	    $host = "http://api.nuozhengtong.com/idcardinfo/server";
	    $url = $host . '?' . $querys;
	
	    $curl = curl_init();
	    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($curl, CURLOPT_FAILONERROR, false);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_HEADER, false);
	    if (1 == strpos('http://api.nuozhengtong.com', "https://"))
	    {
	        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	    }
	    $res = curl_exec($curl);
	    curl_close($curl);
	  	print_r($res);
	  	echo 'end';
	}

    //订单对账  聚合支付
    public function Analyaqs() {

        echo '聚合支付,测试调试对账';
	    die;
        $keyaq = 'aq';//上游数据
        $keysq = 'sq';//本地数据
        $this->rs->del($keyaq);
        $this->rs->del($keysq);

        $startTime = !empty(input('get.date')) ? input('get.date') :date('Ymd',strtotime('-1 day'));
        //$startTime = '2020-04-02';
        //$startTime = date('Ymd',strtotime('-1 day'));
        //$startTime = '20181007';
        $startTimes = strtotime($startTime.'00:00:00');//开始日期
        $endTime    = strtotime($startTime.'23:59:59');//结束日期

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
                    $allMoney = sprintf("%.2f",($val['pay_account'] + $val['RefundMoney']));
                    $orderArr[$key]['pay_account'] = $allMoney;
                    $pay_money += $allMoney;//实付金额
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
                $this->SendEmailali($url,'353453825@qq.com',$startTime.'您有聚合支付，对账！请登录');
            } else {
                //发送平账通知
                //$url = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/OkAnalyaqs&type=aq';

                $where = ['pay_type'=>'aq','order_check'=>'no'];
                $mer = Db::name('parking_mer_summary')->field('sid,date')->where($where)->order('sid asc')->find();
                if(!empty($mer)) {
                    $sendData['email'] = '353453825@qq.com';
                    $sendData['sid']   = $mer['sid'];
                    $sendData['types'] = 'aq';
                    $sendData['dates'] = $mer['date'];
                    $url = 'http://shop.gogo198.cn/foll/public/?s=Reconcils/emailok';
                    $this->PostData($url,$sendData);
                }
                //$this->SendEmailali($url,'353453825@qq.com',$startTime.'您有聚合支付，对账！请登录');
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

            $summary['pay_account'] = $pay_money;//$polyMoney交易总额 交易总额需要加上退款部分；
            $summary['fee']   	  	= 0.006;//交易费率
            $summary['pay_fee']   	= sprintf("%.2f",$pay_money * 0.006);//交易费用
            $summary['pay_money'] 	= ($pay_money-$summary['pay_fee']);//清算金额
            $summary['pay_type']  	= 'aq';//支付通道
            $summary['order_check'] = 'no';//订单清算确认，no:未确认，yes:确认

            $insertUnion = Db::name('parking_mer_summary')->insert($summary);
            $msg   = '长款';
            //发送异常通知
            $url = 'http://shop.gogo198.cn/foll/public/index.php?s=Reconcils/Analyaqs&type=aq';
            $this->SendEmailali($url,'353453825@qq.com',$startTime.'您有聚合支付，对账！请登录');

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
            $this->SendEmailali($url,'353453825@qq.com',$startTime.'您有聚合支付，对账！请登录');
        }

        echo '聚合支付订单对账：'.$msg.'《》订单时间：'.$startTime;
        //释放缓存
        $this->rs->del($keyaq);
        $this->rs->del($keysq);
    }


}