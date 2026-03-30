<?php
namespace app\usersinfo\controller;
use think\Controller;
use Util\data\Sysdb;

class BaseAdmin extends Controller
{
	public function __construct(){
		parent::__construct();
		$this->_admin = session('plats');
		// 未登录的用户不允许访问
		if(!$this->_admin){
			Redirects('usersinfo/login');
		}
		$this->admin = session('plats');//登录数据信息
		$this->assign('admin',$this->_admin);//渲染用户信息
		$this->db = new Sysdb;
		// 判断用户是否有权限
	}
	
	/**
	 * 2018-07-16
	 * @author 赵金如
	 * 生成昨日账单，并发送至商户邮箱
	 */
	public function Generatingbill()
	{
		echo date('Y-m-d H:i:s',time());
		/**
		 * 步骤：获取商户信息，生成对账日期，循环添加数据；
		 * 循环商户数据发送电子邮箱
		 */
		//商户信息
		$getUserinfo = $this->db->table('foll_business_admin')->where(['order_prix'=>['neq','']])->lists();
		
		//生成对账时间
		$startDate = date("Y-m-d 00:00:00",strtotime("-1 day"));//昨天开始时间
		$endDate   = date("Y-m-d H:i:s",(strtotime($startDate)+86399));//昨日结束时间
		
		//定义对账类型   商品备案  支付订单，实名验证
		$type = ['goods','pay','auth'];
		$count = count($type);
		$getCount = count($getUserinfo);
		$updatas = [];
		$updata  = [];
		for($n=0;$n<$count;$n++) {//外层3次  对账次数
			for($i=0;$i<$getCount;$i++) {//内层4次  商户数据
				$updata[$i]['uid'] 	      = $getUserinfo[$i]['id'];//用户ID
				$updata[$i]['user_name']  = $getUserinfo[$i]['user_name'];//商户名称
				$updata[$i]['user_email'] = $getUserinfo[$i]['user_email'];//商户邮箱
				$updata[$i]['startDate']  = $startDate;//昨日开始时间
				$updata[$i]['endDate'] 	  = $endDate;//昨日结束时间
				$updata[$i]['status'] 	  = 3;//对账状态：1、确认对账，2、有误账单，3、未对账
				$updata[$i]['c_time'] 	  = date('Y-m-d H:i:s',time());//创建时间
				$updata[$i]['type'] 	  = $type[$n];//对账类型
				$updata[$i]['accountDay'] = date("Y-m-d",strtotime("-1 day"));//对账日  昨天
			}
			array_push($updatas,$updata);//末尾添加数据
		}
		
		//循环生成数据
		foreach($updatas as $key=>$val) {
			foreach($val as $k=>$v) {
				//$this->db->table('foll_goodconfirm')->insert($v);
				echo '<pre>';
				print_r($v);
			}
		}
		
		//循环发送邮件
		foreach($getUserinfo as $key=>$val){
			echo '发送电子邮件: '.$val['user_email'].'<br>';
		}
		
		
		$this->sendEmail('true','805929498@qq.com');
		
		echo '生成成功';
		echo date('Y-m-d H:i:s',time());
	}
	
	
	//发送电子邮件给商户
	public function sendEmail($path,$email,$subject = '您有海关商户信息，请及时登录查看') {
		$name    = '系统管理员';
		$content = "提示：您有海关商户信息，请及时登录查看！,您可请登录后台<a href='http://shop.gogo198.cn/foll/public/?s=account/login'>点击前往登录后台</a>";
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