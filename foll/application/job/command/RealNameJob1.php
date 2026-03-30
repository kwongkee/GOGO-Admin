<?php

namespace app\job\command;

use think\Controller;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use Util\data\Helpay;
use Util\data\Redis;
use Util\data\Sysdb;
use think\Session;
use think\Request;
use think\Loader;
use think\CURLFile;
use PHPExcel_IOFactory;
use PHPExcel;

class RealNameJob1 extends Command
{
    
    protected function configure ()
    {
        $this->setName('RealNameJob1')->setDescription('身份验证');
    }
    
    protected function execute ( Input $input, Output $output )
    {
        $output->writeln('Date Crontab job start...');
//      @file_put_contents(getcwd().'/runtime/log/test.txt',date('H:i:s',time())."\n",FILE_APPEND);
        $this->getRedis();
        $output->writeln('Date Crontab job end...');
    }
    
    
    protected function getRedis ()
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
        $flag = true;
        $lgo  = 'no';
        $email = '';
        $pici  = '';
        
        $this->db = new Sysdb();
    
        $keys = 'RealNames';//缓存key
		try {
			echo '<hr>';
			echo '开始执行时间：'.date('Y-m-d H:i:s',time());
			echo '<br>';
			while ($flag) {
				
				$res = $this->redis->lRange($keys,1,-1);
				//print_r($res);
				if (empty($res)) {
                    $flag = false;
                    $lgo  = 'ok';
                    continue;
                }
                
				$dataArr = json_decode($res[0],true);
				$email   = $dataArr['remark'];
				$pici    = $dataArr['title'];
				//先判断数据库有没有存在数据
				$where = [
					'userName'=>$dataArr['userName'],
					'userId'  =>$dataArr['userId'],
					//'resultCode'=>'0000'//只要存在这个用户信息就不提交验证
				];
				
				$mysqlData = $this->db->table('foll_realname_general')->field('completeTime,fee,resultCode,resultMsg')->where($where)->item();
				if(!empty($mysqlData) && !empty($mysqlData['resultCode'])) {//判断认证代码不为空
					
					$update = [
						'completeTime'	=>	$mysqlData['completeTime'],
						'fee'			=>	$mysqlData['fee'],
						'resultCode'	=>	$mysqlData['resultCode'],
						'resultMsg'		=>	$mysqlData['resultMsg'],
					];
					
					$this->db->table('foll_realname_general')->where(['orderId'=>$dataArr['orderId']])->update($update);
					$update1 = [
						'completeTime'	=>	$mysqlData['completeTime'],
						'resultCode'	=>	$mysqlData['resultCode'],
						'resultMsg'		=>	$mysqlData['resultMsg'],
						'status'	    => 'A',//A  本地查询，B 接口查询
					];
					//Db::name('foll_realname_error')->where(['orderId'=>$dataArr['orderId']])->update($update1);
					$this->db->table('foll_realname_error')->where(['orderId'=>$dataArr['orderId']])->update($update1);
					
					//出栈
					$this->rs->lpop($keys);
					
				} else {
					
					$re = $this->VeriNames($dataArr);
					if($re) {
						//出栈
						$this->rs->lpop($keys);
					}
				}
            }
			
			//执行完成   速度更新   要执行啊
			if($lgo == 'ok' && isset($email)) {
				$this->Excelport($pici,$email);
				//send_mail($email, '身份验证','身份验证完成', '验证完成，批次号:'.$pici);
				echo '执行完成时间：'.date('Y-m-d H:i:s',time());
				echo '<hr>';
			}
			
        } catch (\Exception $exception) {
        	
            throw new \Exception($exception->getMessage());
        }
    }
    
    //提交身份验证
    protected function VeriNames ( $data )
    {
    	try{
    		
    		$pay = Helpay::instance();
        	$pay->ImportAuthName($data);
        	
        	return true;
        	
    	} catch (\Exception $exception) {
	        	
	        throw new \Exception($exception->getMessage());
	    }
        
    }
    
    //执行导出
    protected function Excelport($pici,$email) {
    	$order = $this->db->table('foll_realname_error')->where(['title'=>$pici,'resultCode'=>['neq','0000'],'resultMsg'=>['neq','信息一致，认证成功']])->lists();
    	if(empty($order)) {
    		send_mail($email, '身份验证','身份验证完成,', '身份验证完成、全部通过！批次号:'.$pici);
    		return true;
    	}
    	
    	$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
  		$PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
  		$PHPSheet->setTitle('身份验证信息'); //给当前活动sheet设置名称
  		$PHPSheet->setCellValue('A1','导入批次号')
  				 ->setCellValue('B1','请求订单编号')
  				 ->setCellValue('C1','提交时间')
  				 ->setCellValue('D1','用户姓名')
  				 ->setCellValue('E1','用户身份证')
  				 ->setCellValue('F1','物流订单编号')
  				 ->setCellValue('G1','返回结果码')
  				 ->setCellValue('H1','返回信息')
  				 ->setCellValue('I1','验证类型');
  		//给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue(‘A1’,’’);
  		$count = count($order)-1;
  		$num = 0;
  		for($i=0; $i <= $count; $i++) {
  			$num = 2+$i;
  			$PHPSheet->setCellValue("A".$num,"\t".$order[$i]['title']."\t")
  				 ->setCellValue('B'.$num,"\t".$order[$i]['orderId']."\t")
  				 ->setCellValue('C'.$num,date('Y-m-d H:i:s',strtotime($order[$i]['submitTime'])))
  				 ->setCellValue('D'.$num,$order[$i]['userName'])
  				 ->setCellValue('E'.$num,"\t".$order[$i]['userId']."\t")
  				 ->setCellValue('F'.$num,"\t".$order[$i]['WaybillNo']."\t")
  				 ->setCellValue('G'.$num,$order[$i]['resultCode'])
  				 ->setCellValue('H'.$num,$order[$i]['resultMsg'])
  				 ->setCellValue('I'.$num,($order[$i]['status']=='A'?'平台本地验证':'平台接口验证'));
  		}
  		
  		$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
  		$fileName  = "UserInfo".date('Y-m-d',time()).'.xlsx';
  		$path      = dirname(__FILE__).'/'.$fileName;
		$PHPWriter->save($path);
		$Result    = $this->sendEmail($path,$email);
		unlink($path);
		if(!$Result) {
			return json(['code'=>1,'msg'=>'发送失败']);
		}
	    return json(['code'=>0,'data'=>'发送成功']);
    }
    
    	//发送电子邮件给商户
	protected function sendEmail($path,$email,$subject = '您有身份验证信息') {
		$name    = '系统管理员';
		$time = date('Y-m-d H:i:s',time());
		$content = "提示：您有身份验证失败信息!导出时间：{$time}</a>";
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