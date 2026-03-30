<?php
	namespace app\home\controller;
	use think\Controller;
	use think\Request;
	use think\Db;
	use PHPExcel_IOFactory;
	use PHPExcel;
//	use PHPMailer;
	class Order extends Controller{
		//订单列表  停车订单列表；
		public function orderlist()
		{
			//如果page 有值取它的值，没有就赋值为1；
			$page = is_numeric(input('page'))?input('page'):'1';
			
			//当前页码-1 * 每页显示条数；
			$offet = 8;//每页显示多少条；
			
			$time = input('times');//开始时间
			$config = [
	        	'query'=>['s'=>'order/orderlist&times='.$time],//额外参数
	        	'var_page'=>'page',//分页变量
	        ];
			if($time) {
				$time = strtotime($time);//开始时间		
				$endtime = ($time) + 86399;//结束时间
				//查询总条数
				$count = Db('parking_order')->where('starttime','>=',$time)->where('starttime','<=',$endtime)->where('uniacid',14)->count();//查询多条数据
				$res = Db('parking_order')->where('starttime','>=',$time)->where('starttime','<=',$endtime)->where('uniacid',14)->order('id desc')->paginate($offet,$count,$config);
				
			}else {
				//查询总条数     uniacid 可变参数；
				$count = Db('parking_order')->where('uniacid',14)->count();//查询多条数据
				$res = Db('parking_order')->where('uniacid',14)->order('id desc')->paginate($offet,$count,$config);
				
			}
			$pages = $res->render();
			$resArr = [
				'results'=>$res->toArray(),
				'count'=>$count,
				'page'=>$pages,
			];
			$this->assign($resArr);
			return $this->fetch('order/orderlist');
		}
		
		//导出按钮20180323
		public function export()
		{
			return $this->fetch();
		}
		
		//到处execl 表格
		public function getExecl($bool=false)
		{
			$start = strtotime(input('startTime'));//开始日期
			$end = strtotime(input('endTime'))+86399;//结束日期			
			$email = input('email');//电子邮箱
			//查询数据库中的数据
			$result = Db('parking_order')->where('starttime','>=',$start)->where('starttime','<=',$end)->select();

	 		$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
	  		$PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
	  		$PHPSheet->setTitle('demo'); //给当前活动sheet设置名称
	  		
	  		$PHPSheet->setCellValue('A1','序号')
	  				 ->setCellValue('B1','用户ID')
	  				 ->setCellValue('C1','公众号ID')
	  				 ->setCellValue('D1','订单编号')
	  				 ->setCellValue('E1','上游订单')
	  				 ->setCellValue('F1','车牌编号')
	  				 ->setCellValue('G1','车位编号')
	  				 ->setCellValue('H1','进入时间')
	  				 ->setCellValue('I1','使离时间')	  				  
	  				 ->setCellValue('J1','总金额')
	  				 ->setCellValue('K1','优惠后的金额')
	  				 ->setCellValue('L1','服务描述')
	  				 ->setCellValue('M1','支付方式')
	  				 ->setCellValue('N1','支付时间')
	  				 ->setCellValue('O1','支付状态')
	  				 ->setCellValue('P1','订单时间')
	  				 ->setCellValue('Q1','前端返回URL')
	  				 ->setCellValue('R1','使用月卡')
	  				 ->setCellValue('S1','停车总时长')
	  				 ->setCellValue('T1','付费方式（0预付费，1后付费）')
	  				 ->setCellValue('U1','发票开具（0未开票，1已开票，2开票失败）')
	  				 ->setCellValue('V1','停车状态')
	  				 ->setCellValue('W1','预付费状态（0未付费，1已付费）'); 
	  		//给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue(‘A1’,’’);
	  		for( $i=0 ;$i<=count($result)-1;$i++) {
	  			
	  			$num = 2+$i;
	  			
	  			$PHPSheet->setCellValue("A".$num,$result[$i]['id'])
	  				 ->setCellValue('B'.$num,$result[$i]['openid'])
	  				 ->setCellValue('C'.$num,$result[$i]['uniacid'])
	  				 ->setCellValue('D'.$num,$result[$i]['ordersn'])
	  				 ->setCellValue('E'.$num,$result[$i]['upOrderId'])
	  				 ->setCellValue('F'.$num,$result[$i]['CarNo'])
	  				 ->setCellValue('G'.$num,$result[$i]['number'])
	  				 ->setCellValue('H'.$num,date('Y-m-d H:i:s',$result[$i]['starttime']))
	  				 ->setCellValue('I'.$num,date('Y-m-d H:i:s',$result[$i]['endtime']))
	  				 ->setCellValue('J'.$num,$result[$i]['total'])
	  				 ->setCellValue('K'.$num,$result[$i]['PayAmount'])
	  				 ->setCellValue('L'.$num,$result[$i]['body'])
	  				 ->setCellValue('M'.$num,$result[$i]['pay_type'])
	  				 ->setCellValue('N'.$num,date('Y-m-d H:i:s',$result[$i]['paytime']))
	  				 ->setCellValue('O'.$num,$result[$i]['pay_status'])
	  				 ->setCellValue('P'.$num,date('Y-m-d H:i:s',$result[$i]['create_time']))
	  				 ->setCellValue('Q'.$num,$result[$i]['returnUrl'])
	  				 ->setCellValue('R'.$num,$result[$i]['moncard'])
	  				 ->setCellValue('S'.$num,$result[$i]['duration'])
	  				 ->setCellValue('T'.$num,$result[$i]['charge_type'])
	  				 ->setCellValue('U'.$num,$result[$i]['invoice_iskp'])
	  				 ->setCellValue('V'.$num,$result[$i]['status'])
	  				 ->setCellValue('W'.$num,$result[$i]['charge_status']);
	  		}
	  		
	  		$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
	  		$fileName = "order".date('Y-m-d',time()).'.xlsx';
//	  		if(!$bool){
//	  			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
//				header('Content-Disposition: attachment;filename='.$fileName);//告诉浏览器输出浏览器名称
//	        	header('Cache-Control: max-age=0');//禁止缓存
//	        	$PHPWriter->save("php://output");
//	  		}else {
//	  			$path=dirname(__FILE__).'/'.$fileName;
//	  			$PHPWriter->save($path);
//	  			return $path;
//	  		}
	  		
	  		if(!empty($email)){//发送邮件
	  			$path=dirname(__FILE__).'/'.$fileName;
	  			$PHPWriter->save($path);
	  			$this->sendEmail($path);
	  		}else {//导出数据
	  			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
				header('Content-Disposition: attachment;filename='.$fileName);//告诉浏览器输出浏览器名称
	        	header('Cache-Control: max-age=0');//禁止缓存
	        	$PHPWriter->save("php://output");
	  		}
	  					
		}
		
		//发送电子邮件
		public function sendEmail($path){
			$toemail = input('email');//kali20@126.com  805929498@qq.com
			$name    = '系统管理员';
			$subject = '停车订单';
			$content = '发送成功，请查收';
//			$patfile = $this->getExecl(true);//common.php 文件中的send_mail
        	$status = send_mail($toemail,$name,$subject,$content,['0'=>$path]);
			if($status){
//				unlink($path);
				echo '发送成功，请注意查收';
			}else{
				echo '发送失败，请重新发送';
			}
		}
	
		
	}
?>