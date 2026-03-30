<?php
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

class Userinfo extends BaseAdmin
{
	
	public function __construct()
	{
		parent::__construct();
		//实例化数据库
		$this->db = new Sysdb;
		$this->admin = session('admin');//登录数据信息
	}
	/**
	 * @author 赵金如
	 * @date   2018-07-24
	 */
	public function index(){
		//分页配置
        $config = [
            'type' 		 =>'Layui',//分页类名
            'query'		 =>['s'=>'Userinfo/index'],//url额外参数
            'var_page'	 =>'pages',//分页变量
            'newstyle'	 =>true,
        ];
        
        $start = strtotime(date("Y-m-d H:i:s",time()));
	    $end   = ($start+86399);
	    
        if($this->admin['role'] == 1){//管理员模式
        	
        	//$countData = Db::table('ims_foll_elec_order_head_copy')->alias('a')->join('ims_foll_elec_order_detail_copy b','a.id = b.head_id')->where(['b.id'=>['in',$confirmId]])->select();
        	
        	$data = $this->db->table('ims_usersinfo a')->join('ims_sz_yi_member b')->where([])->pagesJoin(15,$config);
        	echo '<pre>';
        	print_r($data);
        	die;
        	
        	
        	//获取用户表数据
        	/*$userinfo = $this->db->table('sz_yi_member')->where(['uniacid'=>3])->field('openid,realname,mobile,uuid,application,is_flag,createtime')->pagess(16,$config);
        	//查用户实名信息     循环
	        foreach($userinfo['lists'] as $key=>$val){
	        	$auth = $this->db->table('foll_payment_userinfo')->where(['phone'=>$val['mobile']])->field('id as aid,userId')->item();
	        	$userinfo['lists'][$key]['aid'] = $auth['aid']?$auth['aid']:'No';
	        	$userinfo['lists'][$key]['createtime'] = date("Y-m-d H:i:s",$val['createtime']);
	        }
	        
	        //支付订单号以手机号关联
	        
	        //统计总数 $userinfo['total']
	        //统计增长数   获取当天凌晨时间，结束时间
	        
	        $where = [
	        'application'=>'Cross-border',
	        'uniacid'=>3,
	        	'createtime'=>[
	        		['elt',$start],
	        		['egt',$end]
	        	]
	        ];
	        //计算增长数
	        $useraddnum  = Db::table('ims_sz_yi_member')->where($where)->count();*/
	        
	        
	        
        } else {//商户模式
        	/*$userinfo = $this->db->table('sz_yi_member')->where(['uuid'=>$this->admin['id'],'application'=>'Cross-border','uniacid'=>3])->field('openid,realname,mobile,uuid,application,is_flag,createtime')->pagess(16,$config);
        	//查用户实名信息     循环
	        foreach($userinfo['lists'] as $key=>$val){
	        	$auth = $this->db->table('foll_payment_userinfo')->where(['phone'=>$val['mobile']])->field('id as aid,userId')->item();
	        	$userinfo['lists'][$key]['aid'] = $auth['aid'];
	        	$userinfo['lists'][$key]['createtime'] = date("Y-m-d H:i:s",$val['createtime']);
	        }
	        
	        $where = [
	        	'application'=>'Crossborder',
	        	'uniacid'=>3,
	        	'uuid'=>$this->admin['id'],
	        	'createtime'=>[
	        		['elt',$start],
	        		['egt',$end]
	        	]
	        ];
	        //计算增长数
	        $useraddnum  = Db::table('ims_sz_yi_member')->where($where)->count();
        }
        
		/*print_r($userinfo);
		die;
		$this->assign('num',$useraddnum);
		$userinfo['title'] = '用户信息查询';
		$this->assign('order',$userinfo);
		return view('index');*/
	}
}
	
	//XML数据转数组
	public function xmlToArray($xml)
	{
		libxml_disable_entity_loader(true);
		$xmlstring = simplexml_load_string($xml,'SimpleXMLElement',LIBXML_NOCDATA);
		$val = json_decode(json_encode($xmlstring),true);
		return $val;
	}
	
	/**
	 * 将数组转换为xml
	 * @param array $data    要转换的数组
	 * @param bool $root     是否要根节点
	 * @return string         xml字符串
	 * @author Json
	 */
	public function ArrToXmls($data,$root=true)
	{
		$str = '';
		if($root) $str .= "<xml>";
		foreach($data as $key=>$val)
		{
			if(is_array($val))
			{
				$child = $this->ArrToXmls($val,false);
				$str .= "<$key>$child</$key>";
			} else {
				$str .= "<$key>[$val]</$key>";
			}
		}
		if($root) $str .= "</xml>";
		return $str;
	}
	
	//curl post 请求
	public function submitPost($url,$param)
	{
		try{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
			$result = curl_exec($ch);
			curl_close($ch);
			echo $result;
		}catch(Exception $e){
			file_put_contents('./paylog/MyError.txt', print_r($e,TRUE),FILE_APPEND);
		}
	}
	
	//post 请求
    public function doPost($url,$post_data)
    {
    	$ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        // 执行后不直接打印出来
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        // 设置请求方式为post
        curl_setopt($ch,CURLOPT_POST,true);
        // post的变量
        curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
    
    //文件上传 2018-05-28
    /**
     * $url  请求地址
     * $file  需上传文件路径与文件名称
     * $fileName  文件名称
     * $post_dat  上传参数
     */
    public function upload($url,$file,$fileName,$post_data)
    {
    	$obj = new CurlFile($file);
    	$obj->setMImeType('txt');//设置后缀
    	$obj->setPostFilename($fileName);//设置文件名
    	$post_data['fileObj'] = $obj;
    	$ch = curl_init();
    	curl_setopt($ch,CURLOPT_URL,$url);
    	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    	curl_setopt($ch,CURLOPT_POST,1);
    	curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data);
    	$output = curl_exec($ch);
    	curl_close($ch);
    	return $output;
    }
    
    
    //发送文件
    /** php 发送流文件
	* @param  String  $url  接收的路径
	* @param  String  $file 要发送的文件
	* @return boolean
	*/
    public function sendStreamFile($url,$file)
    {
    	if(file_exists($file))
    	{
    		$opts = [
    			'http'=>[
    				'method'=>'POST',
    				'header'=>'content-type:application/x-www-form-urlencoded',
    				'content'=>file_get_contents($file)
    			],
    		];
    		$context = stream_context_create($opts);
    		$res = fopen($url,'rb',false,$context);
    		$response = file_get_contents($url,false,$context);
    		$ret = json_decode($response,true);
			return $res;
    	} else {
    		return false;
    	}
    }
	
}
?>