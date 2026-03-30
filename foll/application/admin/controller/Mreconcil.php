<?php
/**
 * @author 赵金如
 * @date   2018-09-08
 * 对账差错修改路径
 */
namespace app\admin\controller;
use think\Controller;
use think\Db;
use Util\data\Redis;
use Util\data\Sysdb;
use PHPExcel_IOFactory;
use PHPExcel;

class Mreconcil extends Controller
{
	public function test(){
		echo '银企对账，平账测试地址：'.date("Y-m-d H:i:s");
		echo "<script>alert('数据类型为空')</script>";
	}

	public function index() {

		header('Content-type:text/html;charset=utf8');
		$title = '暂无数据';
		$summary['msg'] = '平账';//默认平账
		$type  = trim(input('get.type'));
		$de    = input('get.date');
		//$dates = date('Ymd',1536657817);
		//$dates = date('Ymd',strtotime('-1 day'));
		$dates = !empty($de) ? $de : date('Ymd',strtotime('-1 day'));

		/*echo '<pre>';
		print_r([$type,$de,$dates]);die;*/

		$msg   = '平账';
		$inArr = ['tgpay','fwechat','fagro','union'];
		if(in_array($type,$inArr)) {

			switch($type) {
				case 'tgpay':
					$title = '聚合支付';
				break;
				case 'fwechat':
					$title = '微信免密';
				break;
				case 'fagro':
					$title = '农商代扣';
				break;
				case 'union':
					$title = '银联无感';
				break;
			}

			//$data = Db::name('parking_check_mistake')->where(['type'=>$type,'date'=>$dates])->order('upOrderId')->select();
			$stime = Db::name('parking_check_mistake')->field('date')->where(['type'=>$type,'checkOk'=>'No'])->order('mid','asc')->find();
			$data  = Db::name('parking_check_mistake')->where(['type'=>$type,'checkOk'=>'No','date'=>$stime['date']])->order('upOrderId')->select();
			if(empty($data)) {// 没有数据
				$this->assign('msg',$msg);
				$this->assign('sum',$summary);
				$this->assign('type',$type);
				$this->assign('title',$title);
				$this->assign('dates',$dates);
				return view("mreconcil/index");
			}
			//  当前订单有No的时间
			$dates = $stime['date'];
			$msg = $data[0]['msg'] ? $data[0]['msg'] : $msg;
			$this->assign('msg',$msg);
			$this->assign('sum',$summary);
			$this->assign('data',$data);
			$this->assign('type',$type);
			$this->assign('title',$title);
			$this->assign('dates',$dates);
		}

		$this->assign('msg',$msg);
		$this->assign('sum',$summary);
		$this->assign('type',$type);
		$this->assign('title',$title);
		$this->assign('dates',$dates);
		return view("mreconcil/index");
	}


	/*
	 * 数据导出，只能导出昨天的数据
	 */
	public function Excels(){

		$type = trim(input('post.type'));
		$day  = trim(input('post.dates'));
		//$day  = date('Ymd',strtotime('-1 day'));
		//$day  = '20181006';
		switch($type) {
			case 'tgpay'://聚合支付
				$up = Db::name('parking_pay_poly')->field('pay_money,order_id,low_order_id,r_state,date')->where(['date'=>$day])->select();
				if(empty($up)) {
					echo json_encode(['code'=>0,'msg'=>'没有对账数据可导出！','type'=>$type]);die;
				}
				foreach($up as $key=>$val) {
					$up[$key]['r_state'] = $val['r_state']=='success'?'对账成功':'对账有误';
				}

				$ep = $this->ExcelsPost($up,$day,$type);
				/*$cp = $this->ExpostCount($type);
				if(!$ep && !$cp) {*/
				if(!$ep) {
					echo json_encode(['code'=>0,'msg'=>'数据导出失败']);die;
				}
				echo json_encode(['code'=>1,'msg'=>'数据导出成功']);
			break;
			case 'fwechat'://微信免密
				$up = Db::name('parking_pay_wxsecret')->field('pay_money,order_id,r_state,date,low_order_id')->where(['date'=>$day])->select();
				if(empty($up)) {
					echo json_encode(['code'=>0,'msg'=>'没有查到数据！','type'=>$type]);die;
				}
				foreach($up as $key=>$val) {
					$up[$key]['r_state'] = $val['r_state']=='success'?'对账成功':'对账有误';
					$up[$key]['low_order_id'] = $val['low_order_id'] ? $val['low_order_id'] : 'No';
				}
				$ep = $this->ExcelsPost($up,$day,$type);
				/*$cp = $this->ExpostCount($type);
				if(!$ep && !$cp) {*/
				if(!$ep) {
					echo json_encode(['code'=>0,'msg'=>'数据导出失败']);die;
				}
				echo json_encode(['code'=>1,'msg'=>'数据导出成功']);
			break;
			case 'fagro'://农商免密
				$up = Db::name('parking_pay_sdesecret')->field('pay_money,pay_orderid,pay_ordersn,r_state,date')->where(['date'=>$day])->select();
				if(empty($up)) {
					echo json_encode(['code'=>0,'msg'=>'没有查到数据！','type'=>$type]);die;
				}
				foreach($up as $key=>$val) {
					$up[$key]['r_state'] 	  = $val['r_state']=='success'?'对账成功':'对账有误';
					$up[$key]['order_id'] 	  = $val['pay_orderid'] ? $val['pay_orderid'] : 'No';
					$up[$key]['low_order_id'] = $val['pay_ordersn'] ? $val['pay_ordersn'] : 'No';
				}
				$ep = $this->ExcelsPost($up,$day,$type);
				/*$cp = $this->ExpostCount($type);
				if(!$ep && !$cp) {*/
				if(!$ep) {
					echo json_encode(['code'=>0,'msg'=>'数据导出失败']);die;
				}
				echo json_encode(['code'=>1,'msg'=>'数据导出成功']);
			break;
			case 'union'://银联
				$up = Db::name('parking_pay_unionsecret')->field('pay_money,order_id,low_order_id,r_state,date')->where(['date'=>$day])->select();
				if(empty($up)) {
					echo json_encode(['code'=>0,'msg'=>'没有查到数据！','type'=>$type]);die;
				}

				foreach($up as $key=>$val) {
					$up[$key]['r_state'] 	  = $val['r_state']=='success'?'对账成功':'对账有误';
				}

				$ep = $this->ExcelsPost($up,$day,$type);
				/*$cp = $this->ExpostCount($type);
				if(!$ep && !$cp) {*/
				if(!$ep) {
					echo json_encode(['code'=>0,'msg'=>'数据导出失败']);die;
				}
				echo json_encode(['code'=>1,'msg'=>'数据导出成功']);
			break;
		}
	}

	/*
	 * 数据导出执行
	 */
	private function ExcelsPost($order,&$day,$type='') {

		//$day  = date('Ymd',strtotime('-1 day'));
		//$day  = $day;
		//$day  = '20181006';
		$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
  		$PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
  		$PHPSheet->setTitle('银企对账数据'.$day); //给当前活动sheet设置名称
  		$PHPSheet->setCellValue('A1','交易金额')
  				 ->setCellValue('B1','平台订单')
  				 ->setCellValue('C1','商户单号')
  				 ->setCellValue('D1','对账状态')
  				 ->setCellValue('E1','账单日期');
  		//给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue(‘A1’,’’);
  		$count = count($order)-1;
  		$num = 0;
  		for($i=0; $i <= $count; $i++) {
  			$num = 2+$i;
  			$PHPSheet->setCellValue("A".$num,sprintf("%.2f",$order[$i]['pay_money']))
  				 ->setCellValue('B'.$num,"\t".$order[$i]['low_order_id']."\t")
  				 ->setCellValue('C'.$num,"\t".$order[$i]['order_id']."\t")
  				 ->setCellValue('D'.$num,($order[$i]['r_state']?$order[$i]['r_state']:0))
  				 ->setCellValue('E'.$num,($order[$i]['date']?$order[$i]['date']:0));
  		}
  		$fname = '';
  		switch($type) {
  			case 'tgpay':
  				$tmsg = '聚合支付';
  				$payType = 'aq';
  				$fname   = 'BAJH'.$day;
  			break;
  			case 'fwechat':
  				$tmsg = '微信免密';
  				$payType = 'wx';
  				$fname   = 'BAWX'.$day;
  			break;
  			case 'fagro':
  				$tmsg = '农商代扣';
  				$payType = 'sde';
  				$fname   = 'BASB'.$day;
  			break;
  			case 'union':
  				$tmsg = '银联无感';
  				$payType = 'union';
  				$fname   = 'BAUP'.$day;
  			break;
  		}
  		//查询汇总数据
  		$sum = Db::name('parking_pay_summary')->where(['pay_type'=>$payType,'date'=>$day])->find();
  		if(empty($sum)){
  			return false;
  		}
  		$num += 2;
  		$PHPSheet->setCellValue('A'.$num,'商户号')
  				 ->setCellValue('B'.$num,'账单日期')
  				 ->setCellValue('C'.$num,'订单总数')
				 ->setCellValue('D'.$num,'退款总数')
				 ->setCellValue('E'.$num,'退款总额')
  				 ->setCellValue('F'.$num,'交易总额')
  				 ->setCellValue('G'.$num,'手续费额')
  				 ->setCellValue('H'.$num,'清算金额')
  				 ->setCellValue('I'.$num,'交易类型')
  				 ->setCellValue('J'.$num,'对账状态');
  		$num += 1;
  		$PHPSheet->setCellValue("A".$num,"\t".($sum['code']?$sum['code']:0)."\t")
  				 ->setCellValue('B'.$num,"\t".($sum['date']?$sum['date']:0)."\t")
  				 ->setCellValue('C'.$num,'共'.($sum['pay_sum']?$sum['pay_sum']:0).'笔')
				 ->setCellValue('D'.$num,'共'.($sum['refund_sum']?$sum['refund_sum']:0).'笔')
				 ->setCellValue('E'.$num,($sum['refund_money']?$sum['refund_money']:0).'元')
  				 ->setCellValue('F'.$num,($sum['pay_money']?$sum['pay_money']:0).'元')
  				 ->setCellValue('G'.$num,($sum['pay_fee']?$sum['pay_fee']:0).'元')
  				 ->setCellValue('H'.$num,($sum['pay_money'] - $sum['pay_fee']).'元')
  				 ->setCellValue('I'.$num,$tmsg)
  				 ->setCellValue('J'.$num,($sum['msg']?$sum['msg']:0));

  		$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
  		//$fileName  = "Reconcils".date('Y-m-d',time()).'.xlsx';
  		$fileName  = $fname.'账单.xlsx';
  		$path      = dirname(__FILE__).'/'.$fileName;
		$PHPWriter->save($path);
		$Result    = $this->sendEmail($path,'179078286@qq.com');//'179078286@qq.com'
		unlink($path);
		return $Result;
	}


	/**
	 * 一键平账
	 * 2018-09-17
	 */
	public function OneBalance() {

		$type = trim(input('post.type'));
		$isls = trim(input('post.isls'));
		$dates = trim(input('post.dates'));
		$timestr = strtotime($dates);
		//$dates = date('Ymd',$timestr);
		$inArr = ['tgpay','fwechat','fagro','union'];
		if(in_array($type,$inArr)) {
			//（聚合支付：tgpay，微信免密：Fwechat、农商免密：fagro、银联：union
			switch($type) {
				case 'tgpay':
					// 日期的订单是否存在长短款的数据
					$mistake = Db::name('parking_check_mistake')->where(['type'=>'tgpay','date'=>$dates])->select();
					if(!empty($mistake)) {

						$flag   = false;
						if($isls == '短款') {
							$foll    = [];
							$parking = [];
							foreach($mistake as $k=>$v) {
								$ordersn = 'G99198'.date('YmdHis',time()).mt_rand(010,999);
								$foll[$k]['ordersn'] 		= $v['ordersn']?$v['ordersn']:$ordersn;
								$foll[$k]['pay_account'] 	= $v['pay_account'];
								$foll[$k]['uniacid']     	= '14';
								$foll[$k]['application']    = 'parking';
								$foll[$k]['pay_type']     	= 'wechat';
								$foll[$k]['pay_status']     = '1';
								$foll[$k]['create_time']    = ($timestr+100);
								$foll[$k]['pay_time']       = $timestr;
								$foll[$k]['business_name']  = '伦教停车';
								$foll[$k]['upOrderId']     	= $v['upOrderId'];
								$foll[$k]['body']			= $v['upOrderId'].'短款冲销';
								$parking[$k]['ordersn']		= $v['ordersn']?$v['ordersn']:$ordersn;
								$parking[$k]['status']		= '已结算';

								$up = [
									'r_state'=>'success',
									//错误原因赋值
									'msg'	 => '平账'
								];
								//更新   parking_pay_wxsecret  G9919820180918174343632
								Db::name('parking_pay_poly')->where(['order_id'=>$v['upOrderId']])->update($up);
								//删除差错表中的数据
								Db::name('parking_check_mistake')->where(['upOrderId'=>$v['upOrderId'],'date'=>$v['date']])->delete();
							}

							//写入数据到foll_order表中
							Db::name('foll_order')->insertAll($foll);
							//写入对应
							Db::name('parking_order')->insertAll($parking);
							$flag	= true;//平账标识
							echo json_encode(['code'=>11,'msg'=>'操作成功！']);

						} else if($isls == '长款') {
							$foll    = [];
							$parking = [];
							foreach($mistake as $k=>$v) {
								$ordersn = 'G99198'.date('YmdHis',time()).mt_rand(010,999);
								$foll[$k]['ordersn'] 		= $v['ordersn']?$v['ordersn']:$ordersn;
								$foll[$k]['pay_account'] 	= -$v['pay_account'];
								$foll[$k]['uniacid']     	= '14';
								$foll[$k]['application']    = 'parking';
								$foll[$k]['pay_type']     	= 'wechat';
								$foll[$k]['pay_status']     = '1';
								$foll[$k]['create_time']    = ($timestr+100);
								$foll[$k]['pay_time']       = $timestr;
								$foll[$k]['business_name']  = '伦教停车';
								$foll[$k]['upOrderId']     	= $v['upOrderId'];
								$foll[$k]['body']			= $v['upOrderId'].'长款冲销';

								$parking[$k]['ordersn']		= $v['ordersn']?$v['ordersn']:$ordersn;
								$parking[$k]['status']		= '已结算';

								$up = [
									'r_state'=>'success',
									//错误原因赋值
									'msg'	 => '平账'
								];
								//更新   parking_pay_wxsecret  G9919820180918174343632
								Db::name('parking_pay_poly')->where(['order_id'=>$v['upOrderId']])->update($up);
								//删除差错表中的数据
								Db::name('parking_check_mistake')->where(['upOrderId'=>$v['upOrderId'],'date'=>$v['date']])->delete();
							}
							//写入数据到foll_order表中
							Db::name('foll_order')->insertAll($foll);
							//写入对应
							Db::name('parking_order')->insertAll($parking);
							$flag	= true;//平账标识
							echo json_encode(['code'=>11,'msg'=>'操作成功！']);
						}
						/**
						 * 更新统计表的状态
						 */
						//$dateW  = $mistake[0]['date'];
                        $dateW  = $dates;
						Db::name('parking_pay_summary')->where(['date'=>$dateW,'pay_type'=>'aq'])->update(['msg'=>'平账']);

						//if($flag) {//已平账   发送请求平账
							$urld = 'http://shop.gogo198.cn/foll/public/index.php?s=OrderReconcils/Analyaqs&date='.$dates;
							$this->GetUrl($urld);
						//}

					} else {
						echo json_encode(['code'=>10,'msg'=>'操作失败,没有差错数据']);
					}
				break;

				case 'fwechat':
					$mistake = Db::name('parking_check_mistake')->where(['type'=>'fwechat','date'=>$dates])->select();
					if(!empty($mistake)) {
						$flag  = false;
						if($isls == '短款') {//短款直接不齐对应的表中添加字段，并删除差错表的数据，更新汇总表记录
							$foll  = [];
							$parking = [];
							foreach($mistake as $k=>$v) {
								$ordersn = 'G99198'.date('YmdHis',$timestr).mt_rand(010,999);
								$foll[$k]['ordersn'] 		= $v['ordersn']?$v['ordersn']:$ordersn;
								$foll[$k]['pay_account'] 	= $v['pay_account'];//$v['checkMoney'];
								$foll[$k]['uniacid']     	= '14';
								$foll[$k]['application']    = 'parking';
								$foll[$k]['pay_type']     	= 'Fwechat';
								$foll[$k]['pay_status']     = '1';
								$foll[$k]['create_time']    = ($timestr+100);
								$foll[$k]['pay_time']       = $timestr;
								$foll[$k]['business_name']  = '伦教停车';
								$foll[$k]['upOrderId']     	= $v['upOrderId'];
								$foll[$k]['body']			= $v['upOrderId'].'短款冲销';

								$parking[$k]['ordersn']		= $v['ordersn']?$v['ordersn']:$ordersn;
								$parking[$k]['status']		= '已结算';

								$up = [
									'r_state'=>'success',
									//错误原因赋值
									'msg'	 => '平账'
								];
								//更新   parking_pay_wxsecret  G9919820180918174343632
								Db::name('parking_pay_wxsecret')->where(['order_id'=>$v['upOrderId']])->update($up);
								//删除差错表中的数据
								Db::name('parking_check_mistake')->where(['upOrderId'=>$v['upOrderId'],'date'=>$v['date']])->delete();
							}
							//写入数据到foll_order表中
							Db::name('foll_order')->insertAll($foll);
							//写入对应
							Db::name('parking_order')->insertAll($parking);
							$flag = true;//平账标识
							echo json_encode(['code'=>11,'msg'=>'操作成功！']);

						} else if($isls == '长款') {
							$foll  = [];
							$parking = [];
							foreach($mistake as $k=>$v) {
								$ordersn = 'G99198'.date('YmdHis',$timestr).mt_rand(010,999);
								$foll[$k]['ordersn'] 		= $v['ordersn']?$v['ordersn']:$ordersn;
								$foll[$k]['pay_account'] 	= -$v['pay_account'];//-$v['checkMoney'];
								$foll[$k]['uniacid']     	= '14';
								$foll[$k]['application']    = 'parking';
								$foll[$k]['pay_type']     	= 'Fwechat';
								$foll[$k]['pay_status']     = '1';
								$foll[$k]['create_time']    = ($timestr+100);
								$foll[$k]['pay_time']       = $timestr;
								$foll[$k]['business_name']  = '伦教停车';
								$foll[$k]['upOrderId']     	= $v['upOrderId'];
								$foll[$k]['body']			= $v['upOrderId'].'长款冲销';

								$parking[$k]['ordersn']		= $v['ordersn']?$v['ordersn']:$ordersn;
								$parking[$k]['status']		= '已结算';

								$up = [
									'r_state'=>'success',
									//错误原因赋值
									'msg'	 => '平账'
								];
								//更新   parking_pay_wxsecret  G9919820180918174343632
								Db::name('parking_pay_wxsecret')->where(['order_id'=>$v['upOrderId']])->update($up);
								//删除差错表中的数据
								Db::name('parking_check_mistake')->where(['upOrderId'=>$v['upOrderId'],'date'=>$v['date']])->delete();
							}
							//写入数据到foll_order表中
							Db::name('foll_order')->insertAll($foll);
							//写入对应
							Db::name('parking_order')->insertAll($parking);
							$flag  = true;//平账标识
							echo json_encode(['code'=>11,'msg'=>'操作成功！']);
						}

						/**
						 * 更新统计表的状态
						 */
						//$dateW  = $mistake[0]['date'];
                        $dateW  = $dates;
						Db::name('parking_pay_summary')->where(['date'=>$dateW,'pay_type'=>'wx'])->update(['msg'=>'平账']);
						if($flag) {//已平账
							$urld = 'http://shop.gogo198.cn/foll/public/index.php?s=OrderReconcils/Analywxs&date='.$dates;
							$this->GetUrl($urld);
						}
					} else {
						echo json_encode(['code'=>10,'msg'=>'操作失败,没有差错数据']);
					}
				break;

				case 'fagro':
					$mistake = Db::name('parking_check_mistake')->where(['type'=>'FAgro','date'=>$dates])->select();
					if(!empty($mistake)) {
						$flag  =  false;
						if($isls == '短款') {
							$foll  = [];
							$parking = [];
							foreach($mistake as $k=>$v) {
								$ordersn = 'G99198'.date('YmdHis',time()).mt_rand(010,999);
								$foll[$k]['ordersn'] 		= $v['ordersn']?$v['ordersn']:$ordersn;
								$foll[$k]['pay_account'] 	= $v['pay_account'];//$v['checkMoney'];
								$foll[$k]['uniacid']     	= '14';
								$foll[$k]['application']    = 'parking';
								$foll[$k]['pay_type']     	= 'FAgro';
								$foll[$k]['pay_status']     = '1';
								$foll[$k]['create_time']    = ($timestr+100);
								$foll[$k]['pay_time']       = $timestr;
								$foll[$k]['business_name']  = '伦教停车';
								$foll[$k]['upOrderId']     	= $v['upOrderId'];
								$foll[$k]['body']			= $v['upOrderId'].'短款冲销';

								$parking[$k]['ordersn']		= $v['ordersn']?$v['ordersn']:$ordersn;
								$parking[$k]['status']		= '已结算';

								$up = [
									'r_state'=>'success',
									//错误原因赋值
									'msg'	 => '平账'
								];
								//更新   parking_pay_wxsecret  G9919820180918174343632
								Db::name('parking_pay_sdesecret')->where(['order_id'=>$v['upOrderId']])->update($up);
								//删除差错表中的数据
								Db::name('parking_check_mistake')->where(['upOrderId'=>$v['upOrderId'],'date'=>$v['date']])->delete();
							}
							//写入数据到foll_order表中
							Db::name('foll_order')->insertAll($foll);
							//写入对应
							Db::name('parking_order')->insertAll($parking);
							$flag  =  true;//平账标识
							echo json_encode(['code'=>11,'msg'=>'操作成功！']);

						} else if($isls == '长款') {
							$foll  = [];
							$parking = [];
							foreach($mistake as $k=>$v) {
								$ordersn = 'G99198'.date('YmdHis',time()).mt_rand(010,999);
								$foll[$k]['ordersn'] 		= $v['ordersn']?$v['ordersn']:$ordersn;
								$foll[$k]['pay_account'] 	= -$v['pay_account'];//$v['checkMoney'];
								$foll[$k]['uniacid']     	= '14';
								$foll[$k]['application']    = 'parking';
								$foll[$k]['pay_type']     	= 'FAgro';
								$foll[$k]['pay_status']     = '1';
								$foll[$k]['create_time']    = ($timestr+100);
								$foll[$k]['pay_time']       = $timestr;
								$foll[$k]['business_name']  = '伦教停车';
								$foll[$k]['upOrderId']     	= $v['upOrderId'];
								$foll[$k]['body']			= $v['upOrderId'].'长款冲销';

								$parking[$k]['ordersn']		= $v['ordersn']?$v['ordersn']:$ordersn;
								$parking[$k]['status']		= '已结算';

								$up = [
									'r_state'=>'success',
									//错误原因赋值
									'msg'	 => '平账'
								];
								//更新   parking_pay_wxsecret  G9919820180918174343632
								Db::name('parking_pay_sdesecret')->where(['order_id'=>$v['upOrderId']])->update($up);
								//删除差错表中的数据
								Db::name('parking_check_mistake')->where(['upOrderId'=>$v['upOrderId'],'date'=>$v['date']])->delete();
							}
							//写入数据到foll_order表中
							Db::name('foll_order')->insertAll($foll);
							//写入对应
							Db::name('parking_order')->insertAll($parking);
							$flag  =  true;//平账标识
							echo json_encode(['code'=>11,'msg'=>'操作成功！']);
						}

						/**
						 * 更新统计表的状态
						 */
						//$dateW  = $mistake[0]['date'];
                        $dateW  = $dates;
						Db::name('parking_pay_summary')->where(['date'=>$dateW,'pay_type'=>'sde'])->update(['msg'=>'平账']);
						if($flag) {//已平账
							$urld = 'http://shop.gogo198.cn/foll/public/index.php?s=OrderReconcils/AnalySdes&date='.$dates;
							$this->GetUrl($urld);
						}
					} else {
						echo json_encode(['code'=>10,'msg'=>'操作失败,没有差错数据']);
					}

				break;

				case 'union':
					$mistake = Db::name('parking_check_mistake')->where(['type'=>'union','date'=>$dates])->select();
					if(!empty($mistake)) {
						$flag  =  false;
						if($isls == '短款') {
							$foll 	 = [];
							$parking = [];
							foreach($mistake as $k=>$v) {
								$ordersn = 'G99198'.date('YmdHis',time()).mt_rand(010,999);
								$foll[$k]['ordersn'] 		= $v['ordersn']?$v['ordersn']:$ordersn;//$ordersn;
								$foll[$k]['pay_account'] 	= $v['pay_account'];//$v['checkMoney'];
								$foll[$k]['uniacid']     	= '14';
								$foll[$k]['application']    = 'parking';
								$foll[$k]['pay_type']     	= 'Parks';
								$foll[$k]['pay_status']     = '1';
								$foll[$k]['create_time']    = ($timestr+100);
								$foll[$k]['pay_time']       = $timestr;
								$foll[$k]['business_name']  = '伦教停车';
								$foll[$k]['upOrderId']     	= $v['upOrderId'];
								$foll[$k]['body']			= $v['upOrderId'].'短款冲销';

								$parking[$k]['ordersn']		= $v['ordersn']?$v['ordersn']:$ordersn;
								$parking[$k]['status']		= '已结算';

								$up = [
									'r_state'=>'success',
									//错误原因赋值
									'msg'	 => '平账'
								];

								//更新   parking_pay_wxsecret  G9919820180918174343632
								Db::name('parking_pay_unionsecret')->where(['order_id'=>$v['upOrderId']])->update($up);
								//删除差错表中的数据
								Db::name('parking_check_mistake')->where(['upOrderId'=>$v['upOrderId'],'date'=>$v['date']])->delete();
							}
							//写入数据到foll_order表中
							Db::name('foll_order')->insertAll($foll);
							//写入对应
							Db::name('parking_order')->insertAll($parking);
							$flag  =  true;
							echo json_encode(['code'=>11,'msg'=>'操作成功！']);
						} else if($isls == '长款') {
							$foll = [];
							$parking = [];
							foreach($mistake as $k=>$v) {
								$ordersn = 'G99198'.date('YmdHis',time()).mt_rand(010,999);
								$foll[$k]['ordersn'] 		= $v['ordersn']?$v['ordersn']:$ordersn;
								$foll[$k]['pay_account'] 	= -$v['pay_account'];//$v['checkMoney'];
								$foll[$k]['uniacid']     	= '14';
								$foll[$k]['application']    = 'parking';
								$foll[$k]['pay_type']     	= 'Parks';
								$foll[$k]['pay_status']     = '1';
								$foll[$k]['create_time']    = ($timestr+100);
								$foll[$k]['pay_time']       = $timestr;
								$foll[$k]['business_name']  = '伦教停车';
								$foll[$k]['upOrderId']     	= $v['upOrderId'];
								$foll[$k]['body']			= $v['upOrderId'].'长款冲销';

								$parking[$k]['ordersn']		= $v['ordersn']?$v['ordersn']:$ordersn;
								$parking[$k]['status']		= '已结算';

								$up = [
									'r_state'=>'success',
									//错误原因赋值
									'msg'	 => '平账'
								];
								//更新   parking_pay_wxsecret  G9919820180918174343632
								Db::name('parking_pay_unionsecret')->where(['order_id'=>$v['upOrderId']])->update($up);
								//删除差错表中的数据
								Db::name('parking_check_mistake')->where(['upOrderId'=>$v['upOrderId'],'date'=>$v['date']])->delete();
							}
							//写入数据到foll_order表中
							Db::name('foll_order')->insertAll($foll);
							//写入对应
							Db::name('parking_order')->insertAll($parking);
							$flag = true;
							echo json_encode(['code'=>11,'msg'=>'操作成功！']);
						}

						/**
						 * 更新统计表的状态
						 */
						//$dateW  = $mistake[0]['date'];
                        $dateW  = $dates;
						Db::name('parking_pay_summary')->where(['date'=>$dateW,'pay_type'=>'union'])->update(['msg'=>'平账']);
						if($flag) {//已平账
							$urld = 'http://shop.gogo198.cn/foll/public/index.php?s=OrderReconcils/AnalyUnions&date='.$dates;
							$this->GetUrl($urld);
						}
					} else {
						echo json_encode(['code'=>10,'msg'=>'操作失败,没有差错数据']);
					}
				break;
			}
		}
	}


		/**
	 * 2018-09-09
	 * ajax 请求修改数据
	 */
	public function Ajax_update() {
		//$info = $_POST;
		$payMoney = trim(input('post.payMoney'));
		$uporderid = trim(input('post.uporderid'));
		$upper = Db::name('parking_check_mistake')->where(['upOrderId'=>$uporderid,'data_type'=>'upper'])->field('pay_account,type')->find();
		if(!empty($upper)) {

			if($payMoney == $upper['pay_account']) {
				//分支更新数据
				switch($upper['type']) {
					case 'tgpay'://聚合支付
						$update=[
							'msg'	   =>'',
							'pay_money'=>$payMoney,
							'r_state'  =>'success'
						];
						$up = Db::name('parking_pay_poly')->where(['order_id'=>$uporderid])->update($update);
						if($up) {
							Db::name('parking_check_mistake')->where(['upOrderId'=>$uporderid])->delete();
							echo json_encode(['code'=>1,'msg'=>'数据更改成功！']);die;
						}
					break;
					case 'fwechat'://微信免密
						$update=[
							'msg'	   =>'',
							'pay_money'=>$payMoney,
							'r_state'  =>'success'
						];
						$up = Db::name('parking_pay_wxsecret')->where(['low_order_id'=>$uporderid])->update($update);
						if($up) {
							Db::name('parking_check_mistake')->where(['upOrderId'=>$uporderid])->delete();
							echo json_encode(['code'=>1,'msg'=>'数据更改成功！']);die;
						}
					break;
					case 'fagro'://农商免密
						$update=[
							'msg'	   =>'',
							'pay_money'=>$payMoney,
							'r_state'  =>'success'
						];
						$up = Db::name('parking_pay_sdesecret')->where(['order_id'=>$uporderid])->update($update);
						if($up) {
							Db::name('parking_check_mistake')->where(['upOrderId'=>$uporderid])->delete();
							echo json_encode(['code'=>1,'msg'=>'数据更改成功！']);die;
						}
					break;
					case 'union'://银联
						$update=[
							'msg'	   =>'',
							'pay_money'=>$payMoney,
							'r_state'  =>'success'
						];
						$up = Db::name('parking_pay_unionsecret')->where(['pay_orderid'=>$uporderid])->update($update);
						if($up) {
							Db::name('parking_check_mistake')->where(['upOrderId'=>$uporderid])->delete();
							echo json_encode(['code'=>1,'msg'=>'数据更改成功！']);die;
						}
					break;
				}
			}
			echo json_encode(['code'=>1,'msg'=>'交易金额不一致']);die;
		}
		echo json_encode(['code'=>1,'msg'=>'没有数据更新']);die;
	}


	//发送电子邮件给商户
	public function sendEmail($path,$email,$subject = '请及时登录查看') {
		$name    = '系统管理员';
		$time = date('Y-m-d H:i:s',time());
		$content = "提示：您昨日对账文件导出  时间：{$time}</a>";
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
