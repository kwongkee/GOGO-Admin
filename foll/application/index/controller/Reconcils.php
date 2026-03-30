<?php
namespace app\index\controller;
use app\index\controller;
use think\Log;
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
		$config = [
			'host'	=> '127.0.0.1',
			'port'	=> '6379',
			'auth'	=> '123456',
		];
		$attr = [
			'timeout'=>300,
			'db_id'=>6,
		];
		$this->redis = null;
		$this->redis = Redis::getINstance($config,$attr);
		$this->rs = $this->redis->getRedis();
		$this->db = new Sysdb;
	}

	//重新对账
	public function Reconciliations()
	{
		$date = input('get.date')?input('get.date'):'';
		if(!$date) {
			echo "<script>alert('date is empty!')</script>";
			die;
		}
		return view("reconcils/reconciliations",['date'=>$date]);
	}

	//提交对账
	public function GoReconciliations()
	{
		$date = input('date');
		if(!$date)
		{
			return json(['code'=>0,'msg'=>'对账日期不能为空！']);
		}else{
			//查询该日期是否已经清算
			$where = ['pay_type'=>'wx','date'=>$date];
			$info = $this->db->table('parking_mer_summary')->where($where)->item();

			if( !$info )
			{
				return json(['code'=>0,'msg'=>'该日期还没到清算！']);
			}else{
				//下载对账文件
				if( $this->DownLoadPayWx($date) == false )
				{
					return json(['code'=>0,'msg'=>'对账文件下载失败']);
				}else{
					//写入微信免密数据
					if( $this->InsertAnalywx($date) == true )
					{
						//对账
						if( $this->GoAnalywxs($date) == true )
						{
							return json(['code'=>1,'msg'=>'重新对账成功']);
						}else{
							return json(['code'=>0,'msg'=>'重新对账失败']);
						}
					}else{
						return json(['code'=>0,'msg'=>'写入微信免密数据失败']);
					}
					
				}
			}
		}
	}
	
	//下载对账文件
	private function DownLoadPayWx($date) {
		$url = 'http://shop.gogo198.cn/payment/Frx/upload.php';
		$timestr = strtotime($date);
		$sendArr = [
			'Token' =>  'loadBill', //停车类型；
			'loaDate'=> $timestr,//查询日期
			'uniacid'=> 14,//公众号ID
		];
		$day  = date('Ymd',$timestr);
		//对账文件路径
		$path = "/www/web/default/crontab/wx/loadBill{$day}.txt";
		//删除重新下载
		if(file_exists($path)) {
			unlink($path);
		}
		$res = $this->PostUrl($url,$sendArr);
		$res = json_decode($res,true);
		if($res['status'] <= 0){
			return false;
		}
		return true;
	}

	private function InsertAnalywx($date) {
		$day = $date;
		//对账文件路径
		$path = '/www/web/default/crontab/wx/loadBill'.$day.'.txt';
		//先删除旧数据
		Db::name('parking_pay_wxsecret')->where(['date'=>$day])->delete();
		if(file_exists($path) && (filesize($path) != 0)) {

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
	        	}
	        	unset($temp);
	        	unset($temps);
        		unset($fileArr);
			}

			if(!empty($bodyArr)) {
        		$insertUnion = Db::name('parking_pay_wxsecret')->insertAll($bodyArr);
        	}
			
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
			
		}

		return true;
	}

	private function GoAnalywxs($date) {

		$keyaq = 'wx';//定义缓存key
		$keysq = 'wxs';//定义缓存key
		//删除释放数据
		$this->rs->del($keyaq);
		$this->rs->del($keysq);
		$day = $date;
		//删除对账数据
		Db::name('parking_pay_summary')->where(['date'=>$day,'pay_type'=>'wx'])->delete();
		Db::name('parking_mer_summary')->where(['date'=>$day,'pay_type'=>'wx'])->delete();
		
		//删除错误订单
		$where_error = [
			'pay_type'	 =>['eq','Fwechat'],
			'pay_time'   =>[['egt',strtotime($day)],['elt',(strtotime($day)+86399)]],
			'uniacid'    =>14,
			'user_id'    =>''
		];
		Db::name('foll_order')->where($where_error)->delete();
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
				$polyMoney = 0;
				$poly = Db::name('foll_order')->field('pay_account,upOrderId,ordersn')->where($where)->select();
				if(!empty($poly)) {

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
		return true;
	}

	public function CheckOk($type,$isok='no',$info=[]) {

		switch(trim($type))
		{
			case 'Fwechat'://微信免密
				$url = 'http://shop.gogo198.cn/foll/public/?s=mreconcil/index&type=fwechat';
				$payType	= '微信免密';
				$wxtype = 'fwechat';
			break;
		}

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
		return true;
	}

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

	public function TestPahts()
    {
	    //return ROOT_PATH.'public/send/';
	    //$path = ROOT_PATH.'public/send/BOJH20190113.xlsx';
	    // echo '<pre>';
	    // //
        // $fileName = 'test.log';
        // $text = '我的测试文件';
        // $path = ROOT_PATH.'public/send/'.$fileName;
        // file_put_contents($path,$text);
        // // 读取数据
        // $f = file_get_contents($path);
        // print_r($f);
		$day = '20200429';
		$type = 'sd';
		switch ($type) {
			case 'sd':
				$remark = '伦教停车【农商代扣】'.$day.'清算确认';
				break;
			case 'un':
				$remark = '伦教停车【银联无感】'.$day.'清算确认';
				break;
			case 'wx':
				$remark = '伦教停车【微信免密】'.$day.'清算确认';
				break;
			case 'aq':
				$remark = '伦教停车【聚合支付】'.$day.'清算确认';
				break;
		}

		//$this->SendFinance($type,$day,'账单清算确认',$remark,'wx6d1af256d76896ba','pages/user/carbill/carbill?type='.$type);


    }

	//聚合支付  异常对账  需手动平账  @qq.com
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
				//发送微信通知

				switch ($type) {
					case 'sd':
						$remark = '伦教停车【农商代扣】'.$day.'清算确认';
						break;
					case 'un':
						$remark = '伦教停车【银联无感】'.$day.'清算确认';
						break;
					case 'wx':
						$remark = '伦教停车【微信免密】'.$day.'清算确认';
						break;
					case 'aq':
						$remark = '伦教停车【聚合支付】'.$day.'清算确认';
						break;
				}
				//$this->SendMamage($type,$day,'平账通知，请提交清算！',$remark,'wx6d1af256d76896ba','pages/user/carbill/carbill?type='.$type.'&status=Ok');

				$msg = "提示：平账通知，请提交清算！,清算链接：【<a href='http://shop.gogo198.cn/foll/public/?s=Reconcils/OkAnalyaqs&type=".$type."'>点击查看</a>】";
				$this->SendEmailali($msg,$email='353453825@qq.com');//179078286@qq.com   @qq.com  353453825
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

		try{
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
                //echo json_encode(['code'=>1,'msg'=>'平账成功']);

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
                //echo json_encode(['code'=>1,'msg'=>'平账成功']);
            }

            // 平账成功发送电子邮件
            $wh = ['date'=>$day,'pay_type'=>$payType];
            $summaryOk = Db::name('parking_mer_summary')->field('sid,date,order_check')->where($wh)->order('sid asc')->find();
            if(!empty($summaryOk)) {
                $sendData['email'] = '353453825@qq.com';
                $sendData['sid']   = $summaryOk['sid'];
                $sendData['types'] = $payType;
                $sendData['dates'] = $summaryOk['date'];
                //$this->emailoks($sendData);
                $this->emailokd($sendData);
            }

        }catch (\Exception $e){
		    echo $e->getMessage().'=='.$e->getLine().'=='.$e->getFile();
        }

        echo json_encode(['code'=>1,'msg'=>'平账成功']);
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
		$sid   	 = input('post.sid')   ? trim(input('post.sid')):'';
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
		//更新数据  @qq.com
		$up = Db::name('parking_mer_summary')->where(['sid'=>$sid])->update(['order_check'=>'yes','user_check'=>0]);
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

		//发送boss通知
		switch ($payType) {
			case 'sd':
				$remark = '伦教停车【农商代扣】'.$day.'账单文件';
				break;
			case 'un':
				$remark = '伦教停车【银联无感】'.$day.'账单文件';
				break;
			case 'wx':
				$remark = '伦教停车【微信免密】'.$day.'账单文件';
				break;
			case 'aq':
				$remark = '伦教停车【聚合支付】'.$day.'账单文件';
				break;
		}
		//$this->SendMamage($payType,$day,'账单文件',$remark,'wx6d1af256d76896ba','pages/user/excelpage/excelpage?type='.$payType.'&date='.$day);

		// 2018-11-06
		//$this->SendExcel($infos,$day,'353453825@qq.com');
		$this->SendExcel($infos,$day,'xibo@gogo198.net');
		//发送附件
		$res = $this->SendExcel($infos,$day,$email,true);

		if($res) {
			echo json_encode(['code'=>1,'msg'=>'发送成功']);
		} else {
			echo json_encode(['code'=>0,'msg'=>'发送失败']);
		}

	}



    public function emailokd($info=[]) {
        //$day = date('Ymd',strtotime('-1 day'));
        //$day = '20181020';
        $day 	 = $info['dates'];
        $email 	 = $info['email'];
        $sid   	 = $info['sid'];
        $payType = $info['types'];
        //$rules   = '/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/';
        /*$strtime = strtotime($day);
        echo json_encode(['code'=>0,'msg'=>'操作失败','type'=>$strtime]);die;*/

        if($email =='' || $sid == '' || $payType == '') {
            echo json_encode(['code'=>0,'msg'=>'数据不能为空']);
            return false;
        }

        /*if(!preg_match($rules,$email)){
            echo json_encode(['code'=>0,'msg'=>'电子邮箱格式不正确']);
            return false;
        }*/
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

        // 2018-11-06
//        $this->SendExcel($infos,$day,'353453825@qq.com');
        $this->SendExcel($infos,$day,'xibo@gogo198.net');

        //发送附件
        $res = $this->SendExcel($infos,$day,$email,true);

		//发送boss通知
		// switch ($payType) {
		// 	case 'sd':
		// 		$remark = '伦教停车【农商代扣】'.$day.'账单文件';
		// 		break;
		// 	case 'un':
		// 		$remark = '伦教停车【银联无感】'.$day.'账单文件';
		// 		break;
		// 	case 'wx':
		// 		$remark = '伦教停车【微信免密】'.$day.'账单文件';
		// 		break;
		// 	case 'aq':
		// 		$remark = '伦教停车【聚合支付】'.$day.'账单文件';
		// 		break;
		// }
		//$this->SendMamage($payType,$day,'账单文件',$remark,'wx6d1af256d76896ba','pages/user/excelpage/excelpage?type='.$type.'&date='.$day);

        if($res) {
            echo json_encode(['code'=>1,'msg'=>'发送成功']);
        } else {
            echo json_encode(['code'=>0,'msg'=>'发送失败']);
        }

    }


	// 内部调用发送文件
    public function emailoks($sendData=[]) {

        $day 	 = trim($sendData['dates']);
        $email 	 = trim($sendData['email']);
        $sid   	 = trim($sendData['sid']);
        $payType = trim($sendData['types']);

        //$rules   = '/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/';
        /*$strtime = strtotime($day);
        echo json_encode(['code'=>0,'msg'=>'操作失败','type'=>$strtime]);die;*/

        if($email =='' || $sid == '' || $payType == '') {
            echo json_encode(['code'=>0,'msg'=>'数据不能为空']);
            return false;
        }

        /*if(!preg_match($rules,$email)){
            echo json_encode(['code'=>0,'msg'=>'电子邮箱格式不正确']);
            return false;
        }*/

        //更新数据
        $up = Db::name('parking_mer_summary')->where(['sid'=>$sid])->update(['order_check'=>'yes']);
        if(!$up){
            echo json_encode(['code'=>0,'msg'=>'操作失败']);
            die;
        }

        $where = ['date'=>$day,'pay_type'=>$payType,'order_check'=>'no','sid'=>$sid];
        //$where = ['sid'=>$sid,'pay_type'=>$payType];
        $infos = $this->db->table('parking_mer_summary')->where($where)->order('sid desc')->item();
        if(empty($infos)) {
            echo json_encode(['code'=>0,'msg'=>'没有数据可导出']);
            die;
        }

        // 2018-11-06
//        $this->SendExcel($infos,$day,'353453825@qq.com');
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
            $body = '';
			//账单日期，交易时间，订单编号，商户单号，交易金额，费用所属，对账状态
			foreach($poly as $key => $v) {
				if((($v['ref_auto'] == 2) || ($v['IsWrite'] == 103))) {
					$polyArrs[$key]['pay_account'] = sprintf("%.2f",($v['pay_account'] + $v['RefundMoney']));//交易金额
				} else {
					$polyArrs[$key]['pay_account'] = $v['pay_account'];//交易金额
				}

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

		$fileName  = $names.$day.'.xlsx';
        //$path      = dirname(__FILE__).'/'.$fileName;

        $path = ROOT_PATH.'public/send/'.$fileName;

        if(!file_exists($path)) {

            //账单日期，交易时间，订单编号，商户单号，交易金额，费用所属，对账状态
            $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
            $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
            $PHPSheet->setTitle('订单清算' . $day); //给当前活动sheet设置名称
            $PHPSheet->setCellValue('A1', '账单日期')
                ->setCellValue('B1', '订单时间')
                ->setCellValue('C1', '订单编号')
                ->setCellValue('D1', '商户单号')
                ->setCellValue('E1', '交易金额')
                ->setCellValue('F1', '费用所属')
                ->setCellValue('G1', '对账状态');

				// $PHPSheet->getColumnDimension('A')->setWidth('10');
				// $PHPSheet->getColumnDimension('B')->setWidth('20');
				// $PHPSheet->getColumnDimension('C')->setWidth('25');
				// $PHPSheet->getColumnDimension('D')->setWidth('25');
				// $PHPSheet->getColumnDimension('E')->setWidth('10');
				// $PHPSheet->getColumnDimension('F')->setWidth('10');
				// $PHPSheet->getColumnDimension('G')->setWidth('10');
				// $PHPSheet->getColumnDimension('H')->setWidth('10');

            $count = count($polyArrs) - 1;
            $num = 0;
            for ($i = 0; $i <= $count; $i++) {
                $num = 2 + $i;
                $PHPSheet->setCellValue("A" . $num, $polyArrs[$i]['date'])
                    ->setCellValue('B' . $num, $polyArrs[$i]['create_time'])//"\t".$polyArrs[$i]['low_order_id']."\t"
                    ->setCellValue('C' . $num, "\t" . $polyArrs[$i]['ordersn'] . "\t")
                    ->setCellValue('D' . $num, "\t" . $polyArrs[$i]['upOrderId'] . "\t")
                    ->setCellValue('E' . $num, sprintf("%.2f", $polyArrs[$i]['pay_account']))
                    ->setCellValue("F" . $num, $polyArrs[$i]['body'])
                    ->setCellValue("G" . $num, $polyArrs[$i]['status']);
            }

            $num += 2;
            $PHPSheet->setCellValue('A' . $num, '支付方式')
                ->setCellValue('B' . $num, '订单日期')
                ->setCellValue('C' . $num, '订单数量')
                ->setCellValue('D' . $num, '退款总数')
                ->setCellValue('E' . $num, '退款总额')
                ->setCellValue('F' . $num, '交易总额')
                ->setCellValue('G' . $num, '交易费用')
                ->setCellValue('H' . $num, '清算金额');
            $num += 1;
            $PHPSheet->setCellValue('A' . $num, $payType)
                ->setCellValue('B' . $num, $infos['date'])
                ->setCellValue('C' . $num, '共' . $infos['count'] . '笔')
                ->setCellValue('D' . $num, '共' . ($payRefund['refund_sum'] ? $payRefund['refund_sum'] : 0) . '笔')
                ->setCellValue('E' . $num, ($payRefund['refund_money'] ? $payRefund['refund_money'] : 0) . '元')
                ->setCellValue('F' . $num, $infos['pay_account'] . '元')
                ->setCellValue('G' . $num, $infos['pay_fee'] . '元')
                ->setCellValue('H' . $num, $infos['pay_money'] . '元');

            $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
            $PHPWriter->save($path);
        }

        $Send = [
            'fileNames'=> $fileName,
            'paths'    => $path,
            'Texts'    => $msg,
            'Emails'   => $email,
		];
		
		//发送给财务
		// switch ($infos['pay_type']) {
		// 	case 'sd':
		// 		$remark = '伦教停车【农商代扣】'.$day.'清算确认';
		// 		break;
		// 	case 'un':
		// 		$remark = '伦教停车【银联无感】'.$day.'清算确认';
		// 		break;
		// 	case 'wx':
		// 		$remark = '伦教停车【微信免密】'.$day.'清算确认';
		// 		break;
		// 	case 'aq':
		// 		$remark = '伦教停车【聚合支付】'.$day.'清算确认';
		// 		break;
		// }

		//$this->SendFinance($infos['pay_type'],$day,'账单清算确认',$remark,'wx6d1af256d76896ba','pages/user/carbill/carbill?type='.$infos['pay_type']);

        //Log::write($Send,'info');
		if($this->SendEmailali($msg,$email,$path)) {
			if($flag){
				//unlink($path);
			}
			return true;
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


    /**
     * @param $content
     * @param string $email
     * @param string $path
     * @param string $subject
     * @return bool
     * 使用阿里云邮箱发送
     */
    public function SendEmailali($content,$email='353453825@qq.com',$path='',$subject = '您有订单清算确认，请查看！') {
        $name    = '系统管理员';
        if(!empty($path)){
            $status  = send_mailAli($email,$name,$subject,$content,['0'=>$path]);
        } else {
            $status  = send_mailAli($email,$name,$subject,$content);
        }

        //$content = "提示：您有对账信息，请及时登录查看并确认！,您可请登录后台【<a href='".$url."'>点击前往查看</a>】";
        if($status) {
            return true;
        } else {
            return false;
        }
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

	public function SendMamage($type = '',$day = '',$msg,$remark,$appid = '',$pagepath = '')
	{
		//appid = wx6d1af256d76896ba
		//boss ooWwF0p_1SBnxknfhkMv5ux02U1E
		$manage_user = Db::name('mc_mapping_fans')->where(array('uniacid'=>3,'unionid'=>'ooWwF0p_1SBnxknfhkMv5ux02U1E'))->find();

		$this->SendWechat(json_encode([
		  'call'=>'send_pre_commit_notice',
		  'msg' =>$msg,
		  'name'=>$manage_user['nickname'],
		  'time'=>date('Y-m-d H:i:s',time()),
		  'openid'=>$manage_user['openid'],
		  'remark'=> $remark,
		  'uniacid'=>3,
		  'appid' => $appid,
		  'pagepath' => $pagepath
		]));
	}

	public function SendFinance($type = '',$day = '',$msg,$remark,$appid = '',$pagepath = '')
	{
		$userDatas = Db::name('smallwechat_user')->where('find_in_set(5,auth)')->select();
		foreach ($userDatas as $k => $v) {
			if($v['unionid'])
			{
				$manage_user = Db::name('mc_mapping_fans')->where(array('uniacid'=>3,'unionid'=>$v['unionid']))->find();
				if($manage_user)
				{
					$this->SendWechat(json_encode([
					  'call'=>'send_pre_commit_notice',
					  'msg' =>$msg,
					  'name'=>$manage_user['nickname'],
					  'time'=>date('Y-m-d H:i:s',time()),
					  'openid'=>$manage_user['openid'],
					  'remark'=> $remark,
					  'uniacid'=>3,
					  'appid' => $appid,
					  'pagepath' => $pagepath
					]));
				}
			}
		}
	}

}
?>
