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

class Reconcils extends CommonController
{
	public function __construct(){
		$this->db = new Sysdb;
	}
	
	//聚合支付  异常对账  需手动平账  805929498@qq.com
	public function Analyaqs() {
		//$day  = date('Ymd',strtotime('-1 day'));
		$type = input('get.type')?input('get.type'):'';
		if(!$type) {
			echo "<script>alert('数据类型为空')</script>";
			die;
		}
		//$day = '20181007';
		//$where = ['date'=>$day,'pay_type'=>$type,'is_ok'=>'no'];
		$where1 = ['pay_type'=>$type,'is_ok'=>'no'];
		$infoa = $this->db->table('parking_mer_mistake')->field('date')->where($where1)->order('date asc')->item();
		$day   = $infoa['date'];
		$where = ['date'=>$day,'pay_type'=>$type,'is_ok'=>'no'];
		$info = $this->db->table('parking_mer_mistake')->where($where)->order('date asc')->lists();
		if($info) {
			$flag = true;
		} else {
			$flag = false;
			$wheres = ['date'=>$day,'pay_type'=>$type,'is_ok'=>'yes'];
			$infos = $this->db->table('parking_mer_mistake')->where($wheres)->lists();
			if($infos) {
				$msg = "提示：平账通知，请提交清算！,清算链接：【<a href='http://shop.gogo198.cn/foll/public/?s=Reconcils/OkAnalyaqs&type=".$type."'>点击查看</a>】";
				$this->SendEmail($msg,$email='353453825@qq.com');//179078286@qq.com   353453825@qq.com
				header('Location:http://shop.gogo198.cn/foll/public/?s=Reconcils/OkAnalyaqs&type='.$type);
				exit;
			}
		}
		return view("reconcils/analyaqs",['data'=>$info,'flag'=>$flag]);
	}
	
	
	//平账操作
	public function CheckAnalyaqs() {
		$mid     = input('post.mid') ? trim(input('post.mid')):'';
		$payType = input('post.payType') ? trim(input('post.payType')):'';
		$day     = input('post.dates');
		$strtime = strtotime($day);
		//$day = date('Ymd',strtotime('-1 day'));
		//$day = '20181007';
		if( $mid == '' || $payType == '') {
			echo json_encode(['code'=>0,'msg'=>'数据不能为空']);
			return false;
		}
		
		$mis = $this->db->table('parking_mer_mistake')->where(['mid'=>$mid,'is_ok'=>'no'])->item();
		if(!$mis){
			echo json_encode(['code'=>0,'msg'=>'没有查到该数据']);
			return false;
		}
		
		Db::name('parking_mer_mistake')->where(['mid'=>$mid])->update(['is_ok'=>'yes']);
		
		$payTypes = null;
		switch($payType) {
			case 'aq':
				$payTypes = 'wechat';
			break;
			case 'wx':
				$payTypes = 'Fwechat';
			break;
			case 'un':
				$payTypes = 'Parks';
			break;
			case 'sd':
				$payTypes = 'FAgro';
			break;
		}
		
		//短款
		if($mis['is_sort'] == 'short') {
			
			$ordersn = 'G99198'.date('YmdHis',time()).mt_rand(010,999);
			//$ordersn = 'G9919820181008939287189';
			$foll['ordersn'] 		= $ordersn;
			$foll['pay_account'] 	= $mis['pay_account'];
			$foll['uniacid']     	= '14';
			$foll['application']    = 'parking';
			$foll['pay_type']     	= $payTypes;
			$foll['pay_status']     = '1';
			$foll['create_time']	= strtotime(date('YmdHis',(strtotime('-1 day')+500)));
			$foll['pay_time']       = strtotime(date('YmdHis',(strtotime('-1 day')+500)));
			$foll['business_name']  = '伦教停车';
			$foll['upOrderId']     	= $mis['upOrderId'];
			$foll['body']			= $mis['upOrderId'].'短款冲销';
			
			$parking['ordersn']		= $ordersn;
			$parking['status']		= '已结算';
			//写入数据到foll_order表中
			Db::name('foll_order')->insert($foll);
			//写入对应
			Db::name('parking_order')->insert($parking);
			Db::name('parking_mer_summary')->where(['date'=>$day,'pay_type'=>$payType])->update(['short_num'=>0,'check'=>'Yes']);
			echo json_encode(['code'=>1,'msg'=>'平账成功']);
			
		} else if($mis['is_sort'] == 'long') {//长款
			
			$ordersn = 'G99198'.date('YmdHis',time()).mt_rand(010,999);
			//$ordersn = 'G9919820181008939287189';
			$foll['ordersn'] 		= $ordersn;
			$foll['pay_account'] 	= -$mis['pay_account'];
			$foll['uniacid']     	= '14';
			$foll['application']    = 'parking';
			$foll['pay_type']     	= $payTypes;
			$foll['pay_status']     = '1';
			$foll['create_time']	= strtotime(date('YmdHis',(strtotime('-1 day')+500)));
			$foll['pay_time']       = strtotime(date('YmdHis',(strtotime('-1 day')+500)));
			$foll['business_name']  = '伦教停车';
			$foll['upOrderId']     	= $mis['upOrderId'];
			$foll['body']			= $mis['upOrderId'].'长款冲销';
			
			$parking['ordersn']		= $ordersn;
			$parking['status']		= '已结算';
			//写入数据到foll_order表中
			Db::name('foll_order')->insert($foll);
			//写入对应
			Db::name('parking_order')->insert($parking);
			Db::name('parking_mer_summary')->where(['date'=>$day,'pay_type'=>$payType])->update(['long_num'=>0,'check'=>'Yes']);
			echo json_encode(['code'=>1,'msg'=>'平账成功']);
		}
	}
	
	
	/*
	 * 订单清单确认
	 */
	public function OkAnalyaqs() {
		//$day = date('Ymd',strtotime('-1 day'));
		//$day = '20181020';
		
		$type = input('get.type')?input('get.type'):'';
		if(!$type) {
			echo "<script>alert('数据类型为空')</script>";
			die;
		}
		$types  = '';
		switch($type) {
			case 'aq':
				$types = '聚合支付';
			break;
			case 'wx':
				$types = '微信免密';
			break;
			case 'un':
				$types = '银联无感';
			break;
			case 'sd':
				$types = '农商代扣';
			break;
		}
		
		//$day = '20181007';
		//$where = ['date'=>$day,'pay_type'=>$type,'order_check'=>'no'];
		$where = ['pay_type'=>$type,'order_check'=>'no'];
		$info = $this->db->table('parking_mer_summary')->where($where)->order('sid asc')->item();
		if($info) {
			$flag = true;
		}else {
			$flag = false;
		}
		return view("reconcils/okanalyaqs",['info'=>$info,'flag'=>$flag,'title'=>$types]);
	}
	
	
	/**
	 * 订单清算确认
	 * 发送订单数据
	 */
	public function emailok() {
		//$day = date('Ymd',strtotime('-1 day'));
		//$day = '20181020';
		$day 	 = trim(input('post.dates'));
		$email 	 = input('post.email') ? trim(input('post.email')):'';
		$sid   	 = input('post.sid') ? trim(input('post.sid')):'';
		$payType = input('post.types') ? trim(input('post.types')):'';
		$rules   = '/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/';
		
		/*$strtime = strtotime($day);
		echo json_encode(['code'=>0,'msg'=>'操作失败','type'=>$strtime]);die;*/
		
		if($email =='' || $sid == '' || $payType == '') {
			echo json_encode(['code'=>0,'msg'=>'数据不能为空']);
			return false;
		}
		
		if(!preg_match($rules,$email)){
			echo json_encode(['code'=>0,'msg'=>'电子邮箱格式不正确']);
			return false;
		}
		//更新数据  353453825@qq.com
		$up = Db::name('parking_mer_summary')->where(['sid'=>$sid])->update(['order_check'=>'yes']);
		if(!$up){
			echo json_encode(['code'=>0,'msg'=>'操作失败']);
			die;
		}
		
		//$where = ['date'=>$day,'pay_type'=>$payType,'order_check'=>'no','sid'=>$sid];
		$where = ['sid'=>$sid,'pay_type'=>$payType];
		$infos = $this->db->table('parking_mer_summary')->where($where)->order('sid desc')->item();
		if(empty($infos)) {
			echo json_encode(['code'=>0,'msg'=>'没有数据可导出']);
			die;
		}
		
		$this->SendExcel($infos,$day,'xibo@gogo198.net');
		//发送附件
		$res = $this->SendExcel($infos,$day,$email,true);
		if($res) {
			echo json_encode(['code'=>1,'msg'=>'发送成功']);
		} else {
			echo json_encode(['code'=>0,'msg'=>'发送失败']);
		}
		
	}
	
	
	//发送附件  2018-10-09
	public function SendExcel($infos,$day,$email,$flag='false') {
		//$day 		= '20181007';
		$startTimes = strtotime($day.'00:00:00');//开始日期
		$endTime    = strtotime($day.'23:59:59');//结束日期
		$payType = '';
		$tp1	 = '';
		$names   = '';
		$p		 = '';
		switch($infos['pay_type']) {
			case 'aq':
				$payType = '聚合支付';
				$tp  = 'wechat';
				$tp1 = 'alipay';
				$names = 'BOJH';
				$p		= 'aq';
			break;
			case 'wx':
				$payType = '微信免密';
				$tp		= 'Fwechat';
				$names = 'BOWX';
				$p		= 'wx';
			break;
			case 'un':
				$payType = '银联无感';
				$tp		= 'Parks';
				$names = 'BOUP';
				$p		= 'union';
			break;
			case 'sd':
				$payType = '农商代扣';
				$tp		= 'FAgro';
				$names = 'BOSB';
				$p		= 'sde';
			break;
		}
		
		$where['pay_time'] 		= ['between',"{$startTimes},{$endTime}"];
		$where['pay_status']	= 1;
		$where['upOrderId']		= ['neq',''];
		if($tp1 != '') {
			$where['pay_type'] = [['eq',$tp],['eq',$tp1],'or'];
		} else {
			$where['pay_type'] = $tp;
		}
		//统计对应支付部分的退款金额与笔数
		$payRefund = $this->db->table('parking_pay_summary')->field('refund_sum,refund_money')->where(['date'=>$day,'pay_type'=>$p])->item();
		
		$poly = Db::name('foll_order')->where($where)->field(['pay_time,create_time,pay_account,upOrderId,application,ordersn,RefundMoney,IsWrite,ref_auto'])->select();
		$polyArrs = [];
		if(empty($poly)) {
			$polyArrs[0]['upOrderId']	= 0;		//商户单号
			$polyArrs[0]['ordersn']  	= 0;  		//订单编号
			$polyArrs[0]['body']	    = 0;     	//费用所属
			$polyArrs[0]['create_time']	   = 0;		//交易时间
			$polyArrs[0]['date']	   	   = $day;  //账单日期
			$polyArrs[0]['pay_account']	   = 0;		//交易金额
			$polyArrs[0]['status']	       = '对账成功';//对账状态
			
		} else {
			//账单日期，交易时间，订单编号，商户单号，交易金额，费用所属，对账状态
			foreach($poly as $key => $v) {
				if((($v['ref_auto'] == 2) || ($v['IsWrite'] == 103))) {
					$polyArrs[$key]['pay_account'] = sprintf("%.2f",($v['pay_account'] + $v['RefundMoney']));//交易金额
				} else {
					$polyArrs[$key]['pay_account'] = $v['pay_account'];//交易金额
				}
				
				$body = '';
				switch($v['application']){
					case 'parking':
						$body = '路内停车';
					break;
					case 'monthCard':
						$body = '月卡服务';
					break;
					default:
						$body = '其他服务';
					break;
				}
				
				$polyArrs[$key]['upOrderId']= $v['upOrderId'];//商户单号
				$polyArrs[$key]['ordersn']  = $v['ordersn'];  //订单编号
				$polyArrs[$key]['body']	    = $body;     //费用所属
				$polyArrs[$key]['create_time']	   = date("Y-m-d H:i:s",$v['create_time']);//交易时间
				$polyArrs[$key]['date']	   		   = $day;    //账单日期
				$polyArrs[$key]['status']	       = '对账成功';//对账状态
			}
		}
				
		$msg = "你好,［{$day}］的［{$payType}］订单已经完成，请确认以下清算信息：<br>";
		$msg .= '支付方式：'.$payType.'<br>';
		$msg .= '订单日期：'.$infos['date'].'<br>';
		$msg .= '订单数量：共'.$infos['count'].'笔<br>';
		$msg .= '交易总额：'.$infos['pay_account'].'元<br>';
		$msg .= '交易费用：'.$infos['pay_fee'].'元<br>';
		$msg .= '清算金额：'.$infos['pay_money'].'元<br>';
		$msg .= '下载电邮附件，查看订单明细，并以“回复全部”的方式，回邮确认正确与否，以便我司清算订单费用予贵司银行账户。';
		
		//账单日期，交易时间，订单编号，商户单号，交易金额，费用所属，对账状态
		$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
  		$PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
  		$PHPSheet->setTitle('订单清算'.$day); //给当前活动sheet设置名称
  		$PHPSheet->setCellValue('A1','账单日期')
  				 ->setCellValue('B1','订单时间')
  				 ->setCellValue('C1','订单编号')
  				 ->setCellValue('D1','商户单号')
  				 ->setCellValue('E1','交易金额')
  				 ->setCellValue('F1','费用所属')
  				 ->setCellValue('G1','对账状态');
  		
  		$count = count($polyArrs)-1;
  		$num = 0;
  		for($i=0; $i <= $count; $i++) {
  			$num = 2+$i;
  			$PHPSheet->setCellValue("A".$num,$polyArrs[$i]['date'])
  				 ->setCellValue('B'.$num,$polyArrs[$i]['create_time'])//"\t".$polyArrs[$i]['low_order_id']."\t"
  				 ->setCellValue('C'.$num,"\t".$polyArrs[$i]['ordersn']."\t")
  				 ->setCellValue('D'.$num,"\t".$polyArrs[$i]['upOrderId']."\t")
  				 ->setCellValue('E'.$num,sprintf("%.2f",$polyArrs[$i]['pay_account']))
  				 ->setCellValue("F".$num,$polyArrs[$i]['body'])
  				 ->setCellValue("G".$num,$polyArrs[$i]['status']);
  		}
  		
  		$num += 2;
  		$PHPSheet->setCellValue('A'.$num,'支付方式')
  				 ->setCellValue('B'.$num,'订单日期')
  				 ->setCellValue('C'.$num,'订单数量')
				 ->setCellValue('D'.$num,'退款总数')
				 ->setCellValue('E'.$num,'退款总额')
  				 ->setCellValue('F'.$num,'交易总额')
  				 ->setCellValue('G'.$num,'交易费用')
  				 ->setCellValue('H'.$num,'清算金额');
  		$num += 1;
		$PHPSheet->setCellValue('A'.$num,$payType)
				 ->setCellValue('B'.$num,$infos['date'])
				 ->setCellValue('C'.$num,'共'.$infos['count'].'笔')
				 ->setCellValue('D'.$num,'共'.($payRefund['refund_sum']?$payRefund['refund_sum']:0).'笔')
				 ->setCellValue('E'.$num,($payRefund['refund_money']?$payRefund['refund_money']:0).'元')
				 ->setCellValue('F'.$num,$infos['pay_account'].'元')
				 ->setCellValue('G'.$num,$infos['pay_fee'].'元')
				 ->setCellValue('H'.$num,$infos['pay_money'].'元');
  		
  		$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
  		$fileName  = $names.$day.'.xlsx';
  		$path      = dirname(__FILE__).'/'.$fileName;
		$PHPWriter->save($path);
		if($this->SendEmail($msg,$email,$path)) {
			if($flag){
				unlink($path);
			}
			return true;
		} else {
			if($flag){
				unlink($path);
			}
			return false;
		}
	}
	
	//发送电子邮件
	public function SendEmail($content,$email='353453825@qq.com',$path='',$subject = '您有订单清算确认，请查看！') {
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
}
?>