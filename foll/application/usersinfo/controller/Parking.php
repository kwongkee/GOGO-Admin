<?php
/**
 * 统计停车平台用户数据信息
 * @author 赵金如
 * @datetime 2018-07-25
 */
namespace app\usersinfo\controller;
use think\Controller;
use Util\data\Sysdb;
use think\Session;
use think\Request;
use think\Db;
use think\Loader;
use think\log;
use CURLFile;
use PHPExcel_IOFactory;
use PHPExcel;

class Parking extends BaseAdmin
{
	/**
	 * 停车平台用户数据
	 */
	public function index() {
		//分页配置
        $config = [
            'type' 		 =>'Layui',//分页类名
            'query'		 =>['s'=>'usersinfo/parking'],//url额外参数
            'var_page'	 =>'pages',//分页变量
            'newstyle'	 =>true,
        ];
        
        $start = strtotime(date('Y-m-d 00:00:00',time()));
        $end   = ($start+86399);
        
        //统计今日增长数用户条件
		$growhere = [
			'a.application'=>'parking',
			'b.create_time'=>[
				['egt',$start],
				['elt',$end]
			],
		];
		
        //停车平台数据
        if(empty($this->admin['order_prix']) && ($this->admin['platform'] == 'parking')){
        	
        	$info = $this->db->table('usersinfo a')
					->join(['parking_authorize b','a.uiqueid = b.unique_id'])
					->where(['a.application'=>'parking'])
					->field('a.uiqueid,a.application,b.mobile,b.openid')
					->order('create_time desc')
					->pagesJoin(10,$config);
			
			$info['GrowthNum'] = $this->db->table('usersinfo a')
					->join(['parking_authorize b','a.uiqueid = b.unique_id'])
					->where($growhere)
					->joincounts(); //统计今日增长数
					
        } else {//商户所属商户ID
        	return '暂无数据';die;
        }
		
		$info['title'] = '路内智能停车平台用户数据';
		$this->assign('order',$info);
		return view('index');
	}
	
	//查看停车实名认证信息
	public function Seeauth(){
		$opid = trim(input('get.m'));
		if(!isset($opid)){return '暂无数据';die;}
		$userinfo = $this->db->table('parking_verified')->where(['openid'=>$opid,'idcard'=>['neq','']])->field('idcard,uname,addr,time')->item();
		if(empty($userinfo)){
			return '该用户未实名认证';die;
		}
		$userinfo['title'] = '用户实名认证信息';
		$this->assign('info',$userinfo);
		return  view('seeauth');
	}
	
	//驾驶证信息
	public function Driving(){
		$opid = trim(input('get.m'));
		if(!isset($opid)){return '暂无数据';die;}
		$userinfo = $this->db->table('parking_verified')->where(['openid'=>$opid,'driverlicense'=>['neq','']])->field('driverlicense,uname,addr,time')->item();
		if(empty($userinfo)){
			return '该用户未认证驾驶证';die;
		}
		$userinfo['title'] = '用户驾驶认证信息';
		$this->assign('info',$userinfo);
		return  view('driving');
	}
	
	//行驶证信息
	public function Travel(){
		$opid = trim(input('get.m'));
		if(!isset($opid)){return '暂无数据';die;}
		$userinfo = $this->db->table('parking_verified')->where(['openid'=>$opid,'license'=>['neq','']])->field('license,uname,addr,time')->item();
		if(empty($userinfo)){
			return '该用户未认证行驶证';die;
		}
		$userinfo['title'] = '用户行驶认证信息';
		$this->assign('info',$userinfo);
		return  view('travel');
	}
	
	//订单详情
	public function Orderdetail() {
		
		$popid = trim(input('post.opid'));
		$gopid = trim(input('get.opid'));
		$openid = $gopid ? $gopid : $popid;
		if($openid==''){return '暂无订单数据';die;}		
		//分页配置
        $config = [
            'type' 		 =>'Layui',//分页类名
            'query'		 =>['s'=>'usersinfo/orderdetail','opid'=>$openid],//url额外参数
            'var_page'	 =>'pages',//分页变量
            'newstyle'	 =>true,
        ];
		$orderinfo = $this->db->table('foll_order a')
					->join(['parking_order b','a.ordersn = b.ordersn'])
					->where(['a.user_id'=>$openid])
					->field('a.ordersn,a.create_time,a.pay_money,b.status')
					->order('a.create_time desc')
					->pagesJoin(9,$config);
					
		if(empty($orderinfo['lists'])){return '暂无订单数据';die;}
		
		$orderinfo['title'] = '用户订单列表信息';
		$this->assign('info',$orderinfo);
		return  view('orderdetail');
	}
	
	//下载数据
	public function Uploads() {
		/**
		 * 只下载用户基本信息
		 */
		$email = $this->admin['user_email'];//商户邮箱
		if(!isset($email)){return json(['code'=>1,'msg'=>'您的电子邮箱为空，请联系管理员!']);}
		
		if(empty($this->admin['order_prix']) && ($this->admin['platform'] == 'parking')) {
			//查询停车平台用户数据
			$sendData = $this->db->table('parking_authorize')
						->field('unique_id,mobile,name,auth_status,auth_type,CarNo,CertNo,create_time')
						->lists();
			
			$res = $this->sendData($email,$sendData);
			if(!$res){
				return json(['code'=>1,'msg'=>'发送失败']);
			}
			
			return json(['code'=>0,'msg'=>'发送成功']);
			
		} else {
			return json(['code'=>1,'msg'=>'暂无数据']);
		}
	}
	
	//下载文件
	private function sendData($email,$send = []) {
		
		$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
  		$PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
  		$PHPSheet->setTitle('停车平台用户数据'); //给当前活动sheet设置名称
  		$PHPSheet->setCellValue('A1','用户ID')
  				 ->setCellValue('B1','用户姓名')
  				 ->setCellValue('C1','用户手机号')
  				 ->setCellValue('D1','用户身份证')
  				 ->setCellValue('E1','用户车牌号')
  				 ->setCellValue('F1','授权状态')
  				 ->setCellValue('G1','授权类型')
  				 ->setCellValue('H1','用户注册时间');
  		//给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue(‘A1’,’’);
  		$count = count($send)-1;
  		$num = 0;
  		for($i=0; $i <= $count; $i++) {
  			$num = 2+$i;
  			$type = '';
  			switch($send[$i]['auth_type']) {
  				case 'a:1:{s:2:"wx";s:7:"Fwechat";}':
  					$type = '微信免密授权';
  				break;
  				case 'a:1:{s:2:"sd";s:5:"FAgro";}':
  					$type = '顺德农商免密';
  				break;
  				case 'a:1:{s:2:"wg";s:11:"FCreditCard";}':
  					$type = '银联信用卡免密';
  				break;
  				default:
  					$type = '暂无授权';
  			}
  			
  			$PHPSheet->setCellValue("A".$num,$send[$i]['unique_id'])
  				 	->setCellValue('B'.$num,$send[$i]['name']?$send[$i]['name']:'')
  				 	->setCellValue('C'.$num,$send[$i]['mobile'])
  				 	->setCellValue('D'.$num,"\t".$send[$i]['CertNo']."\t")
  				 	->setCellValue('E'.$num,$send[$i]['CarNo'])
  				 	->setCellValue('F'.$num,($send[$i]['auth_status'])?'已授权':'未授权')
  				 	->setCellValue('G'.$num,$type)
  				 	->setCellValue('H'.$num,date('Y-m-d H:i:s',$send[$i]['create_time']));
  		}
  		
  		$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
  		$fileName  = "Parking_userinfos".date('Y-m-d',time()).'.xlsx';
  		$path      = dirname(__FILE__).'/'.$fileName;
		$PHPWriter->save($path);
		$Result    = $this->sendEmails($path,$email,'停车平台用户数据','提示：您好，你需要的用户数据已发送到您的邮箱！请注意查收！');
		unlink($path);
		return $Result;
	}
	
	//发送电子邮件给商户
	private function sendEmails($path,$email,$subject = '您有海关商户信息，请及时登录查看',$content = "提示：您有海关商户信息，请及时登录查看！") {
		$name    = '系统管理员';
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