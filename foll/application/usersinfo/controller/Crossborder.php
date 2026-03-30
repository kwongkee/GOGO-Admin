<?php
/**
 * 统计跨境电商用户数据信息
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

class Crossborder extends BaseAdmin
{
	
	public function __construct()
	{
		parent::__construct();
		//实例化数据库
		$this->db = new Sysdb;
		$this->admin = session('plats');//登录数据信息
	}

	
	/**
	 * 跨境电商用户数据
	 */
	public function index() {
		
		//分页配置
        $config = [
            'type' 		 =>'Layui',//分页类名
            'query'		 =>['s'=>'usersinfo/crossborder'],//url额外参数
            'var_page'	 =>'pages',//分页变量
            'newstyle'	 =>true,
        ];
        
        $start = strtotime(date('Y-m-d 00:00:00',time()));
        $end   = ($start+86399);
        //统计今日增长数用户条件
		$growhere = [
			'a.application'=>'Crossborder',
			'b.createtime'=>[
				['egt',$start],
				['elt',$end]
			],
		];
        //管理员
        if($this->admin['role'] == 1){
        	$info = $this->db->table('usersinfo a')
					->join(['sz_yi_member b','a.uiqueid = b.uiqueids'])
					->where(['a.application'=>'Crossborder'])
					->field('a.uiqueid,a.application,b.mobile,b.openid,b.is_flag,b.uuid')
					->pagesJoin(15,$config);
			
			$info['GrowthNum'] = $this->db->table('usersinfo a')
					->join(['sz_yi_member b','a.uiqueid = b.uiqueids'])
					->where($growhere)
					->joincounts(); //统计今日增长数
        } else {//商户所属商户ID
        	$info = $this->db->table('usersinfo a')
					->join(['sz_yi_member b','a.uiqueid = b.uiqueids'])
					->where(['a.application'=>'Crossborder','b.uuid'=>$this->admin['id']])
					->field('a.uiqueid,a.application,b.mobile,b.openid,b.is_flag,b.uuid')
					->pagesJoin(15,$config);
					
			$growhere['b.uuid'] = $this->admin['id'];
			$info['GrowthNum'] = $this->db->table('usersinfo a')
					->join(['sz_yi_member b','a.uiqueid = b.uiqueids'])
					->where($growhere)
					->joincounts(); //统计今日增长数
        }
		
		$info['title'] = '跨境电商用户数据';
		$this->assign('order',$info);
		return view('index');
	}
	
	//实名认证查询
	public function Realname(){
		//手机号
		$mobile = trim(input('get.m'));
		if(!isset($mobile)){return '暂无数据';die;}
		
		if($this->admin['role'] ==1 ){
			$where = [
				'phone'=>$mobile
			];
			$userinfo = $this->db->table('foll_payment_userinfo')->where($where)->field('orderId,userName,userId,fee,completeTime,resultMsg')->item();
		} else {
			$where = [
				'uid'=>$this->admin['id'],
				'phone'=>$mobile
			];
			$userinfo = $this->db->table('foll_payment_userinfo')->where($where)->field('orderId,userName,userId,fee,completeTime,resultMsg')->item();
		}
		$userinfo['title'] = '用户实名认证信息';
		$this->assign('info',$userinfo);
		return  view('realname');
	}
	
	/**
	 * 订单详细
	 */
	public function orderEdit(){
		
		$popenid = trim(input('post.opid'));
		$gopenid = trim(input('get.opid'));
		//用户openid
		$openid  = $gopenid ? $gopenid : $popenid;
		if(!isset($openid)){return '暂无数据...';die;}
		
		//分页配置
        $config = [
            'type' 		 =>'Layui',//分页类名
            'query'		 =>['s'=>'usersinfo/orderedit','opid'=>$openid],//url额外参数
            'var_page'	 =>'pages',//分页变量
            'newstyle'	 =>true,
        ];
        $field = 'ordersn,price,status,createtime';
        if(($this->admin['role'] == 1) && isset($this->admin['order_prix'])) {
        	$orders = $this->db->table('sz_yi_order')->where(['openid'=>$openid])->field($field)->order('createtime desc')->pages(6,$config);
        	if(empty($orders['lists'])){
        		return '暂无数据';die;
        	}
        	
        } else if(isset($this->admin['order_prix'])) {
        	$orders = $this->db->table('sz_yi_order')->where(['openid'=>$openid])->field($field)->order('createtime desc')->pages(6,$config);
        	if(empty($orders['lists'])){
        		return '暂无数据';die;
        	}
        }
        
        $orders['title'] = '用户订单数据列表';
        $this->assign('order',$orders);
		return view('orderedit');
	}
	
	//下载用户数据 2018-07-27
	public function Uploads()
	{
		$email = $this->admin['user_email'];//商户邮件
		if(!isset($email)){return json(['code'=>1,'msg'=>'您的电子邮箱为空，请联系管理员!']);}
		
		if(!empty($this->admin['order_prix']) && $this->admin['role'] == 1) {//管理员
			
			$sendData = Db::name('usersinfo')->alias('a')
				        ->join('sz_yi_member b','a.uiqueid = b.uiqueids')
				        ->join('foll_payment_userinfo c','a.phone=c.phone')
				        ->field(['a.uiqueid','a.phone','a.ctime','b.realname','b.is_flag','c.userId'])
				        ->where(['a.application'=>'Crossborder'])
				        ->select();
				        
			if(empty($sendData)){return json(['code'=>1,'msg'=>'暂无数据']);}
			
			$res = $this->sendData($email,$sendData);
			if(!$res){
				return json(['code'=>1,'msg'=>'发送失败']);
			}
			return json(['code'=>0,'msg'=>'发送成功']);
			
		} else if(!empty($this->admin['order_prix']) && $this->admin['role'] > 1) {//商户
			
			$sendData = Db::name('usersinfo')->alias('a')
				        ->join('sz_yi_member b','a.uiqueid = b.uiqueids')
				        ->join('foll_payment_userinfo c','a.phone = c.phone')
				        ->field(['a.uiqueid','a.phone','a.ctime','b.realname','b.is_flag','c.userId'])
						->where(['a.application'=>'Crossborder','b.uuid'=>$this->admin['id']])
						->select();
			
			if(empty($sendData)){return json(['code'=>1,'msg'=>'暂无数据']);}
			
			$res = $this->sendData($email,$sendData);
			if(!$res){
				return json(['code'=>1,'msg'=>'发送失败']);
			}
			return json(['code'=>0,'msg'=>'发送成功']);
		}
	}
	
	//生成Excel数据
	private function sendData($email,$send=[]) {
		
		$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
  		$PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
  		$PHPSheet->setTitle('跨境电商用户数据'); //给当前活动sheet设置名称
  		$PHPSheet->setCellValue('A1','用户ID')
  				 ->setCellValue('B1','用户姓名')
  				 ->setCellValue('C1','用户手机号')
  				 ->setCellValue('D1','用户身份证')
  				 ->setCellValue('E1','用户注册时间');
  		//给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue(‘A1’,’’);
  		$count = count($send)-1;
  		$num = 0;
  		for($i=0; $i <= $count; $i++) {
  			$num = 2+$i;
  			$PHPSheet->setCellValue("A".$num,$send[$i]['uiqueid'])
  				 	->setCellValue('B'.$num,$send[$i]['realname']?$send[$i]['realname']:'')
  				 	->setCellValue('C'.$num,$send[$i]['phone'])
  				 	->setCellValue('D'.$num,"\t".$send[$i]['userId']."\t")
  				 	->setCellValue('E'.$num,date('Y-m-d H:i:s',$send[$i]['ctime']));
  		}
  		
  		$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
  		$fileName  = "Crossborder_userinfos".date('Y-m-d',time()).'.xlsx';
  		$path      = dirname(__FILE__).'/'.$fileName;
		$PHPWriter->save($path);
		$Result    = $this->sendEmails($path,$email,'跨境电商用户数据','提示：您好，你需要的用户数据已发送到您的邮箱！请注意查收！');
		unlink($path);
		return $Result;
	}
	
	//发送电子邮件
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