<?php
/**
 * 商户对账
 * @author 赵金如
 * @date   2018-06-12
 * 对账文件显示与导出
 */
namespace app\declares\controller;
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

class Reconcil extends BaseAdmin
{
	public function __construct()
	{
		parent::__construct();
		//实例化数据库
		$this->db = new Sysdb;
		$this->admin = session('admin');//登录数据信息
		/**
		 * 对账表  foll_goodconfirm  type 有三种类型;
		 * 1、备案商品  type: goods
		 * 2、实名验证  type: auth
		 * 3、支付订单  type: pay
		 */
	}
	
	//实名认证对账 2018-07-10   开始*******************************
	public function Auth()
	{
		//分页配置
        $config = [
            'type' 		 =>'Layui',//分页类名
            'query'		 =>['s'=>'reconcil/auth'],//url额外参数
            'var_page'	 =>'pages',//分页变量
            'newstyle'	 =>true,
        ];
        //2018-07-23
		$order['queryOk'] = $this->db->table('foll_goodconfirm')->where(['uid'=>$this->admin['id'],'type'=>'auth'])->order('accountDay asc')->pagess(16,$config);
		//$order['queryOk'] = $this->db->table('foll_goodconfirm')->where(['uid'=>$this->admin['id'],'type'=>'auth','status'=>3])->order('accountDay desc')->item();
		foreach($order['queryOk']['lists'] as $key=>$val){
			if($val['status'] == 3){
				$order['queryOk']['lists'][$key]['no'] = 'No';
				break;//返回
			}
			//加多字段
			$order['queryOk']['lists'][$key]['no'] = 'yes';
		}
		//重新排序   
		$order['queryOk']['lists'] = $this->arraySort($order['queryOk']['lists'],'accountDay','desc');
		$order['newDate'] = date('Y-m',time());//当前月份
		$order['title']   = "实名认证对账";
		$this->assign('order',$order);
		return view('reconcil/Auth');
	}
	
	private function arraySort($arr, $keys, $type = 'asc') {
        $keysvalue = $new_array = array();
        foreach ($arr as $k => $v){
            $keysvalue[$k] = $v[$keys];
        }
        $type == 'asc' ? asort($keysvalue) : arsort($keysvalue);
        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
           $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }
    
	//获取对账信息；查看账单
	public function getAuth()
	{
		$inData['id'] = input('get.ids')?input('get.ids'):1;
		$start = input('get.startDate')?input('get.startDate'):1;
		$end   = input('get.endDate')?input('get.endDate'):1;
		
		$start = strtotime($start);
		$end   = strtotime($end);
		
		$page  = input('get.page')?input('get.page'):1;
		$limit = input('get.limit')?input('get.limit'):1;
		$page  = ($page-1)*$limit;
		$config = [
            'type' 		 =>'Layui',//分页类名
            'query'		 =>['s'=>'reconcil/auth'],//url额外参数
            'var_page'	 =>'page',//分页变量
            'newstyle'	 =>true,
        ];
        $where = [
        	'uid'=>$this->admin['id'],
	       	'submitTime'=>[
        		['egt',date("YmdHis",$start)],
        		['elt',date("YmdHis",$end)]
        	]
        ];
        
		$queryOk = Db::name('foll_payment_userinfo')->where($where)->limit($page,$limit)->select();
		
		foreach($queryOk as $key=>$val){
			$queryOk[$key]['fee'] = ($val['fee']/100);
		}
		$count = Db::name('foll_payment_userinfo')->where($where)->count('id');
			
		return json(['code'=>0,'msg'=>'','count'=>$count,'data'=>$queryOk]);
	}
	
	
	//获取对账信息；确认对账2018-07-11  点击对账
	public function getAuths()
	{
		//$inData['id'] = input('post.ids')?input('post.ids'):1;
		$start1 = input('post.startDate')?input('post.startDate'):date('Y-m-d 00:00:00',(time()-86400));
		$end1   = input('post.endDate')?input('post.endDate'):date('Y-m-d H:i:s',(strtotime(date('Y-m-d 00:00:00',time()))-1));
		
		$start  = strtotime($start1);//开始时间
		$end    = strtotime($end1);//结束时间
		$pages  = input('post.page') ? input('post.page')  : 1;//接收页码
		$limit  = input('post.limit') ? input('post.limit')  : 20;//每页条数
		$page   = ($pages-1)*$limit;//分页数
		
		//查询条件
        $where = [
        	'uid'=>$this->admin['id'],
	       	'submitTime'=>[
        		['egt',date("YmdHis",$start)],
        		['elt',date("YmdHis",$end)]
        	]
        ];
        
		//数据总数 
		//$count   = Db::name('foll_payment_userinfo')->where($where)->count('id');
		//用于统计费用
		$jiesuan	 = $this->db->table('foll_payment_userinfo')->where($where)->lists();
		$count 	 	 = count($jiesuan);
        //计算总页数 
		$pageNum = ($count % $limit) == 0 ? (int)($count / $limit) : ceil($count / $limit);
		//商户配置    用于计算费用
		$businessConfig = $this->db->table('foll_business_settlement')->where(['uid'=>$this->admin['id']])->item();
		//验核数据
		$flagInfo	 = 2;//没有数据
		$peopleNum   = 0;//验证人数，默认0人;
		$ServerMoney = 0;//服务费用
		$money		 = 0;//验核费用
		//数据集合
        $queryOk = Db::name('foll_payment_userinfo')->where($where)->limit($page,$limit)->select();
		if(!empty($jiesuan)) {
			$flagInfo = 1;//有数据
			$money 	   = sprintf("%.2f",($count * $businessConfig['authCopyMoney']));
			if($businessConfig['cserMothod'] == 'Percentage'){//按订单处理金额的 XX%
				$sum = 0;//订单总额
				foreach($jiesuan as $key=>$val) {//循环对账日数据
					$sum += ($val['fee']/100);//分为单位
				}
				$ServerMoney = ($sum * $businessConfig['cserMoney']);
				
			} else if($businessConfig['cserMothod'] == 'Element') {//按订单处理订单数：XX 元/订单
				$ServerMoney = ($count * $businessConfig['cserMoney']);
			}
			
		} else {//没有数据
			
			$order['ServerMoney'] = 0.00;//服务费用
			$order['Money']	   	  = 0.00;//验核费用
			$order['Total']	   	  = 0.00;//合计总额
			$order['count']	   	  = 0;//统计条数  人数
			$order['flagInfo'] 	  = $flagInfo;//是否有数据标识
		
			return json([
				'code'=>2,
				'count'=>$count,
				'data' =>$queryOk,
				'pages'=>$pages ? $pages : '0',
				'tongji'=>$order,
				'limit'=>$limit,
				'pageNum' =>$pageNum ? $pageNum : '0',
				'startDate'=>$start1,
				'endDate'  =>$end1
			]);
		}
		
		$order['email']		  = $this->admin['user_email'];//商户邮箱
		$order['ServerMoney'] = $ServerMoney;//服务费用
		$order['Money']	   	  = $money;//验核费用
		$order['Total']	   	  = ($ServerMoney + $money);//合计总额
		$order['count']	   	  = $count;//统计条数  人数
		$order['flagInfo'] 	  = $flagInfo;//是否有数据标识
        
		return json([
			'code'=>1,
			'count'=>$count,
			'data' =>$queryOk,
			'pages'=>$pages,
			'tongji'=>$order,
			'limit'=>$limit,
			'pageNum' =>$pageNum,
			'startDate'=>$start1,
			'endDate'  =>$end1
		]);
	}
	
	
	//确认无误      实名验证 2018-07-12
	public function Authuploads()
	{
		$uid = $this->admin['id'];//当前商户ID
		$infoId = $_POST['checkId'];//当前确认页的数据ID
		$infoId1 = count($infoId) > 1 ? implode(',',$infoId):$infoId;//数组转字符串
		$data   = $_POST;
		$flag   = false;
		$where  = ['startDate'=>$data['start'],'endDate'=>$data['end'],'uid'=>$uid,'type'=>'auth'];
		$confirm = $this->db->table('foll_goodconfirm')->where($where)->item();
		if(!empty($confirm) && ($confirm['confirmId']!=null)) {
			$accout    = explode(',',$confirm['confirmId']);//把数据转为数组；比较传入的数组
			$confirmids = array_diff($infoId,$accout);//计算出不相等的数据更新到数据库中
			$confirmid =  $confirm['confirmId'].','.implode(',',$confirmids);
			/**
			 * 如果有数据，则拼接在后面，没有数据则直接更新
			 */
			$updata['confirmId'] = $confirmid;
			if(isset($data['checkOk']) && ($data['checkOk'] == 'ok')) 
			{
				$updata['status'] = 1;
				$updata['number'] = $data['peoples'];//验核数量
				$updata['serverfee'] = $data['checkingfees'];//服务费用
				$updata['checkingfees'] = $data['checkingfees'];//验核费用
				$updata['totalfees'] = $data['totalfees'];//应付总额
			}
			$flag = $this->db->table('foll_goodconfirm')->where($where)->update($updata);
			
		} else {
			$updata['confirmId'] = $infoId1[0];
			if(isset($data['checkOk']) && ($data['checkOk'] == 'ok'))
			{
				$updata['status'] = 1;
				$updata['number'] = $data['peoples'];//验核数量
				$updata['serverfee'] = $data['checkingfees'];//服务费用
				$updata['checkingfees'] = $data['checkingfees'];//验核费用
				$updata['totalfees'] = $data['totalfees'];//应付总额
			}
			$flag = $this->db->table('foll_goodconfirm')->where($where)->update($updata);
		}
		
		if(!$flag) {
			return json(['code'=>0,'msg'=>'对账失败，请重新对!','info'=>$updata]);
		}
		
		if(isset($data['checkOk'])) {
			return json(['code'=>1,'msg'=>'对账成功!','checkOk'=>$data['checkOk']]);
		} else {
			return json(['code'=>1,'msg'=>'请点击下一页对账!','checkOk'=>'no']);
		}
		
		/**
		 * 操作步骤:  先查看当前条件的对账记录中是否存在记录
		 * 有  先查出来，在后面拼接上新的对账id
		 * 没有  直接把新的对账ID更新到表中
		 * Nike blazer X Offwhite studio mid Black
		 */
	}
	
	//有误订单   实名验证2018-07-12
	public function Autherror()
	{
		$uid = $this->admin['id'];//当前商户ID
		$infoId = $_POST['checkId'];//当前确认页的数据ID
		$infoId1 = count($infoId) > 1 ? implode(',',$infoId):$infoId[0];//数组转字符串
		$data   = $_POST;
		$flag   = false;
		$where  = ['startDate'=>$data['start'],'endDate'=>$data['end'],'uid'=>$uid,'type'=>'auth'];
		$updata = [
			'status'=>2,
			'confirmId'=>$infoId1
		];
		$up = $this->db->table('foll_goodconfirm')->where($where)->update($updata);
		if(!empty($up)) {
			$this->sendEmail('true','805929498@qq.com','海关平台，商户实名验证对账有误!');
			return json(['code'=>1,'msg'=>'有误账单已提交给管理员，请耐心等待!','checkOk'=>'ok']);
		}
		return json(['code'=>0,'msg'=>'有误订单提交失败，请重新提交!']);
	}
	//实名验证对账结束**********2018-07-13*********************
	
	
	
	
	//备案商品开始 *************2018-07-13*********************
	/**
	 * 对账表  foll_goodconfirm  type 有三种类型;
	 * 1、备案商品  type: goods
	 * 2、实名验证  type: auth
	 * 3、支付订单  type: pay
	 */
	public function Statistics()
	{
		//分页配置
        $config = [
            'type' 		 =>'Layui',//分页类名
            'query'		 =>['s'=>'reconcil/auth'],//url额外参数
            'var_page'	 =>'pages',//分页变量
            'newstyle'	 =>true,
        ];
		$order['queryOk'] = $this->db->table('foll_goodconfirm')->where(['uid'=>$this->admin['id'],'type'=>'goods'])->order('accountDay asc')->pagess(16,$config);
		
		foreach($order['queryOk']['lists'] as $key=>$val){
			if($val['status'] == 3){
				$order['queryOk']['lists'][$key]['no'] = 'No';
				break;//返回
			}
			//加多字段
			$order['queryOk']['lists'][$key]['no'] = 'yes';
		}
		//重新排序   
		$order['queryOk']['lists'] = $this->arraySort($order['queryOk']['lists'],'accountDay','desc');
		
		//商户配置    用于计算费用
		$businessConfig = $this->db->table('foll_business_settlement')->where(['uid'=>$this->admin['id']])->item();
		$order['setMothod'] = $businessConfig['setMothod'];//计算方式
		$order['newDate'] = date('Y-m',time());//当前月份
		$order['title']   = '商品备案对账';
		$this->assign('order',$order);
		return view('reconcil/Statistics');
	}
	
	//获取对账信息；查看账单
	public function getStatistic()
	{
		$start = input('get.startDate')?input('get.startDate'):date('Y-m-d 00:00:00',(time()-86400));
		$end   = input('get.endDate')?input('get.endDate'):date('Y-m-d H:i:s',(strtotime(date('Y-m-d 00:00:00',time()))-1));
		$page  = input('get.page')?input('get.page'):1;
		
		$limit = 5;
		$page  = ($page-1)*$limit;
		//使用时间查出对应的订单ID
		
		$confirmData =  $this->db->table('foll_goodconfirm')->where(['startDate'=>['egt',$start],'endDate'=>['elt',$end]])->item();
		if(empty($confirmData)){
			return json(['code'=>1,'msg'=>'没有对账数据']);
		}
		//商户配置    用于计算费用
		$businessConfig = $this->db->table('foll_business_settlement')->where(['uid'=>$this->admin['id']])->item();
		//按商品数
		if(!empty($businessConfig) && $businessConfig['setMothod'] == 'good'){
			//查询条件
	        $where = [
	        	'a.uid'=>$this->admin['id'],
		       	'a.InputDate'=>[
	        		['egt',$start],
	        		['elt',$end]
	        	]
	        ];
			$dataList = Db::table('ims_foll_goodsreghead')->alias('a')->join('ims_foll_goodsreglist b','a.id = b.head_id')->where($where)->limit($page,$limit)->select();
			foreach($dataList as $key=>$val){
				$dataList[$key]['RegPrice'] = sprintf("%.2f",$val['RegPrice']);
			}
			$count = Db::table('ims_foll_goodsreghead')->alias('a')->join('ims_foll_goodsreglist b','a.id = b.head_id')->where($where)->count('Seq');
				
			return json(['code'=>0,'msg'=>'good','count'=>$count,'data'=>$dataList]);

		//按批量数
		} elseif(!empty($businessConfig) && $businessConfig['setMothod'] == 'pici'){
			//查询条件
	        $where = [
	        	'a.uid'=>$this->admin['id'],
		       	'a.InputDate'=>[
	        		['egt',$start],
	        		['elt',$end]
	        	]
	        ];
			$dataList = Db::table('ims_foll_goodsreghead')->alias('a')->join('ims_foll_goodsreglist b','a.id = b.head_id')->where($where)->limit($page,$limit)->select();
			foreach($dataList as $key=>$val){
				$dataList[$key]['RegPrice'] = sprintf("%.2f",$val['RegPrice']);
			}
			$count = Db::table('ims_foll_goodsreghead')->alias('a')->join('ims_foll_goodsreglist b','a.id = b.head_id')->where($where)->count('Seq');
				
			return json(['code'=>0,'msg'=>'good','count'=>$count,'data'=>$dataList]);
		}
	}
	
	
	//获取对账信息；确认对账 2018-07-14
	public function getStatistics()
	{
		$start1 = input('post.startDate')?input('post.startDate'):date('Y-m-d 00:00:00',(time()-86400));
		$end1   = input('post.endDate')?input('post.endDate'):date('Y-m-d H:i:s',(strtotime(date('Y-m-d 00:00:00',time()))-1));
		$pages  = input('post.page') ? input('post.page')  : 1;//接收页码
		$limit  = 8;//每页条数
		$page   = ($pages-1)*$limit;//分页数
        
		//验核数据
		$flagInfo	 = 2;//没有数据
		$peopleNum   = 0;//验证人数，默认0人;
		$ServerMoney = 0;//服务费用 
		$money		 = 0;//验核费用
		
		//商户配置    用于计算费用
		$businessConfig = $this->db->table('foll_business_settlement')->where(['uid'=>$this->admin['id']])->item();
		//结算方式  以商品为基数
		//需要查询出所有的商品  进行计算合计
		if(!empty($businessConfig) && $businessConfig['setMothod'] == 'good')
		{
			//查询条件
	        $where = [
	        	'a.uid'=>$this->admin['id'],
		       	'a.InputDate'=>[
	        		['egt',$start1],
	        		['elt',$end1]
	        	]
	        ];
			//连表查询   $where 作为头的条件(foll_goodsreghead) a   对应列表文件   foll_goodsreglist b 条件 a.id = b.head_id
			$jiesuans = Db::table('ims_foll_goodsreghead')->alias('a')->join('ims_foll_goodsreglist b','a.id = b.head_id')->where($where)->select();
			$count 	 = count($jiesuans);//备案商品总数
			//数据总页数
			$pageNum = ($count % $limit) == 0 ? (int)($count / $limit) : ceil($count / $limit);
			//按备案商品计算   需连表查询
			$jiesuan = Db::table('ims_foll_goodsreghead')->alias('a')->join('ims_foll_goodsreglist b','a.id = b.head_id')->where($where)->limit($page,$limit)->select();
			if(!empty($jiesuan)) {
				
				foreach($jiesuan as $key=>$val){
					$jiesuan[$key]['RegPrice'] = sprintf("%.2f",$val['RegPrice']);
				}
				
				$flagInfo = 1;//有数据
				//备案商品总数 * 配置金额；
				$money 	   = sprintf("%.2f",($count * $businessConfig['banMoney']));
				//按订单处理金额的 XX% 需要查出订单的所有金额;
				if($businessConfig['cserMothod'] == 'Percentage') {
					
					$sum = 0;//订单总额
					foreach($jiesuan as $key=>$val) {//循环对账日数据
						$sum += $val['RegPrice'];	//分为单位
						$jiesuan[$key]['RegPrice'] = sprintf("%.2f",$val['RegPrice']);
					}
					//计算服务费用     订单总金额  * 设置基数
					$ServerMoney = ($sum * $businessConfig['cserMoney']);
					
				} else if($businessConfig['cserMothod'] == 'Element') {//按订单处理订单数：XX 元/订单
					//计算服务费用   备案订单总数量乘以基数
					$ServerMoney = ($count * $businessConfig['cserMoney']);
				}
				
				$order['ServerMoney'] = $ServerMoney;//服务费用
				$order['Money']	   	  = $money;//验核费用
				$order['Total']	   	  = ($ServerMoney + $money);//合计总额
				$order['count']	   	  = $count;//统计条数  人数
				$order['flagInfo'] 	  = $flagInfo;//是否有数据标识
				
				return json([
					'code'=>1,
					'count'=>$count,
					'data' =>$jiesuan,
					'pages'=>$pages ? $pages : '0',
					'tongji'=>$order,
					'limit'=>$limit,
					'pageNum' =>$pageNum ? $pageNum : '0',
					'startDate'=>$start1,
					'endDate'  =>$end1,
					'type'     =>'good'
				]);
				
			} else {//没有数据
				
				$order['ServerMoney'] = 0.00;//服务费用
				$order['Money']	   	  = 0.00;//验核费用
				$order['Total']	   	  = 0.00;//合计总额
				$order['count']	   	  = 0;//统计条数  人数
				$order['flagInfo'] 	  = $flagInfo;//是否有数据标识
			
				return json([
					'code'=>2,
					'count'=>$count,
					'data' =>$jiesuan,
					'pages'=>$pages ? $pages : '0',
					'tongji'=>$order,
					'limit'=>$limit,
					'pageNum' =>$pageNum ? $pageNum : '0',
					'startDate'=>$start1,
					'endDate'  =>$end1
				]);
			}
				
			
		} else if(!empty($businessConfig) && $businessConfig['setMothod'] == 'pici') {//以批次为基数
			//查询条件
	        $where = [
	        	'uid'=>$this->admin['id'],
		       	'InputDate'=>[
	        		['egt',$start1],
	        		['elt',$end1]
	        	]
	        ];
			//作为结算基数
			$jiesuans = $this->db->table('foll_goodsreghead')->where($where)->lists();
			$count    = count($jiesuans);//订单总数
			//数据总页数
			$pageNum = ($count % $limit) == 0 ? (int)($count / $limit) : ceil($count / $limit);
			//分页查询
			$jiesuan = Db::table('ims_foll_goodsreghead')->where($where)->limit($page,$limit)->select();
			if(!empty($jiesuan)) {
				
				foreach($jiesuan as $key=>$val){
					$jiesuan[$key]['RegPrice'] = sprintf("%.2f",$val['RegPrice']);
				}
				
				$flagInfo = 1;//有数据
				//备案商品总数 * 配置金额；  批次总数
				$money 	   = sprintf("%.2f",($count * $businessConfig['banMoney']));
				//计算服务费    按订单处理金额的 XX% 需要查出订单的所有金额;
				if($businessConfig['cserMothod'] == 'Percentage') {
					//批次不支持   订单总额！
					$sum = 0;//订单总额
					//计算服务费用     订单总金额  * 设置基数
					$ServerMoney = ($sum * $businessConfig['cserMoney']);
					
				} else if($businessConfig['cserMothod'] == 'Element') {//按订单处理订单数：XX 元/订单
					//计算服务费用   备案订单总数量乘以基数
					$ServerMoney = ($count * $businessConfig['cserMoney']);
				}
				
				$order['ServerMoney'] = $ServerMoney;//服务费用
				$order['Money']	   	  = $money;//验核费用
				$order['Total']	   	  = ($ServerMoney + $money);//合计总额
				$order['count']	   	  = $count;//统计条数  人数
				$order['flagInfo'] 	  = $flagInfo;//是否有数据标识
				
				return json([
					'code'=>1,
					'count'=>$count,
					'data' =>$jiesuan,
					'pages'=>$pages ? $pages : '0',
					'tongji'=>$order,
					'limit'=>$limit,
					'pageNum' =>$pageNum ? $pageNum : '0',
					'startDate'=>$start1,
					'endDate'  =>$end1,
					'type'     =>'pici'
				]);
				
			} else {//没有数据
				
			}
		}
	}
	
	
	//确认无误      实名验证 2018-07-12
	public function Statisuploads()
	{
		$uid = $this->admin['id'];//当前商户ID
		$infoId = $_POST['checkId'];//当前确认页的数据ID
		$infoId1 = count($infoId) > 1 ? implode(',',$infoId):$infoId[0];//数组转字符串
		$data   = $_POST;//接收数据		
		$flag   = false;
		//更新条件
		$where  = ['startDate'=>$data['start'],'endDate'=>$data['end'],'uid'=>$uid,'type'=>'goods'];
		$confirm = $this->db->table('foll_goodconfirm')->where($where)->item();
		if(!empty($confirm) && ($confirm['confirmId']!=null)) {
			$accout    = explode(',',$confirm['confirmId']);//把数据转为数组；比较传入的数组
			$confirmids = array_diff($infoId,$accout);//计算出不相等的数据更新到数据库中
			$confirmid =  $confirm['confirmId'].','.implode(',',$confirmids);
			/**
			 * 如果有数据，则拼接在后面，没有数据则直接更新
			 */
			$updata['confirmId'] = $confirmid;
			if(isset($data['checkOk']) && ($data['checkOk'] == 'ok')) 
			{
				$updata['status']       = 1;
				$updata['number']       = $data['peoples'];//验核数量
				$updata['serverfee']    = $data['checkingfees'];//服务费用
				$updata['checkingfees'] = $data['checkingfees'];//验核费用
				$updata['totalfees']    = $data['totalfees'];//应付总额
			}
			$flag = $this->db->table('foll_goodconfirm')->where($where)->update($updata);
			
		} else {
			
			$updata['confirmId'] = $infoId1;
			if(isset($data['checkOk']) && ($data['checkOk'] == 'ok'))
			{
				$updata['status'] = 1;
				$updata['number']  = $data['peoples'];//验核数量
				$updata['serverfee'] = $data['checkingfees'];//服务费用
				$updata['checkingfees'] = $data['checkingfees'];//验核费用
				$updata['totalfees'] = $data['totalfees'];//应付总额
			}
			$flag = $this->db->table('foll_goodconfirm')->where($where)->update($updata);
		}
		
		if(!$flag) {//更新失败
			return json(['code'=>0,'msg'=>'对账失败，请重新对!','info'=>$updata]);
		}
		
		if(!empty($data['checkOk'])) {//最后一条对账
			return json(['code'=>1,'msg'=>'对账成功!','checkOk'=>$data['checkOk']]);
		} else {//有下一页
			return json(['code'=>1,'msg'=>'请点击下一页对账!','checkOk'=>'no']);
		}
		
		/**
		 * 操作步骤:  先查看当前条件的对账记录中是否存在记录
		 * 有  先查出来，在后面拼接上新的对账id
		 * 没有  直接把新的对账ID更新到表中
		 * Nike blazer X Offwhite studio mid Black
		 */
	}
	
	//有误订单   实名验证2018-07-12
	public function Statiserror()
	{
		$uid = $this->admin['id'];//当前商户ID
		$infoId = $_POST['checkId'];//当前确认页的数据ID
		$infoId1 = count($infoId) > 1 ? implode(',',$infoId):$infoId[0];//数组转字符串
		$data   = $_POST;
		$flag   = false;
		$where  = ['startDate'=>$data['start'],'endDate'=>$data['end'],'uid'=>$uid,'type'=>'goods'];
		$updata = [
			'status'=>2,
			'confirmId'=>$infoId1
		];
		$up = $this->db->table('foll_goodconfirm')->where($where)->update($updata);
		if(!empty($up)) {
			$this->sendEmail('true','805929498@qq.com','海关平台，商户商品备案对账有误!');
			return json(['code'=>1,'msg'=>'有误账单已提交给管理员，请耐心等待!','checkOk'=>'ok']);
		}
		return json(['code'=>0,'msg'=>'有误订单提交失败，请重新提交!']);
	}
	
	//备案商品结束 **************2018-07-13********************
	
	
	
	
	
	//报关订单表  支付订单 ********2018-07-16********************开始
	//报关订单表  支付订单
	public function Orders()
	{
		//分页配置
        $config = [
            'type' 		 =>'Layui',//分页类名
            'query'		 =>['s'=>'reconcil/Orders'],//url额外参数
            'var_page'	 =>'pages',//分页变量
            'newstyle'	 =>true,
        ];
		$order['queryOk'] = $this->db->table('foll_goodconfirm')->where(['uid'=>$this->admin['id'],'type'=>'pay'])->order('accountDay asc')->pagess(16,$config);
		foreach($order['queryOk']['lists'] as $key=>$val){
			if($val['status'] == 3){
				$order['queryOk']['lists'][$key]['no'] = 'No';
				break;//返回
			}
			//加多字段
			$order['queryOk']['lists'][$key]['no'] = 'yes';
		}
		//重新排序   
		$order['queryOk']['lists'] = $this->arraySort($order['queryOk']['lists'],'accountDay','desc');
		//商户配置    用于计算费用
		$businessConfig = $this->db->table('foll_business_settlement')->where(['uid'=>$this->admin['id']])->item();
		$order['setMothod'] = $businessConfig['setMothod'];//计算方式
		$order['newDate'] = date('Y-m',time());//当前月份
		$order['title']   = '支付订单对账';
		$this->assign('order',$order);
		return view('reconcil/Orders');
	}
	
	//获取对账信息；查看账单
	public function getOrder()
	{
		$start = input('get.startDate')?input('get.startDate'):date('Y-m-d 00:00:00',(time()-86400));
		$end   = input('get.endDate')?input('get.endDate'):date('Y-m-d H:i:s',(strtotime(date('Y-m-d 00:00:00',time()))-1));		
		$start = strtotime($start);
		$end   = strtotime($end);
		$page  = input('get.page')?input('get.page'):1;
		$limit = input('get.limit')?input('get.limit'):1;
		$page  = ($page-1)*$limit;
		
        $where = [
        	'uid'=>$this->admin['id'],
	       	'submitTime'=>[
        		['egt',date("YmdHis",$start)],
        		['elt',date("YmdHis",$end)]
        	]
        ];
        
		$queryOk = Db::name('foll_payment_order')->where($where)->limit($page,$limit)->select();
		
		foreach($queryOk as $key=>$val){
			$queryOk[$key]['payAmount'] = sprintf("%.2f",($val['payAmount']/100)).'元';
		}
		$count = Db::name('foll_payment_order')->where($where)->count('id');
			
		return json(['code'=>0,'msg'=>'','count'=>$count,'data'=>$queryOk]);
		
		die;
	}
	
	
	
	//获取支付订单对账信息；确认对账 2018-07-14
	public function getOrders()
	{
		$start1 = input('post.startDate')?input('post.startDate'):date('Y-m-d 00:00:00',(time()-86400));
		$end1   = input('post.endDate')  ?input('post.endDate')  :date('Y-m-d H:i:s',(strtotime(date('Y-m-d 00:00:00',time()))-1));
		$start  = strtotime($start1);//开始时间
		$end    = strtotime($end1);//结束时间
		$pages  = input('post.page') ? input('post.page')  : 1;//接收页码
		$limit  = input('post.limit') ? input('post.limit')  : 15;//每页条数
		$page   = ($pages-1) * $limit;//分页数
		
		//查询条件
        $where = [
        	'uid'=>$this->admin['id'],
	       	'submitTime'=>[
        		['egt',date("YmdHis",$start)],
        		['elt',date("YmdHis",$end)]
        	]
        ];
        
		//用于统计费用
		$jiesuan = $this->db->table('foll_payment_order')->where($where)->lists();
		//数据总数
		$count 	 = count($jiesuan);
        //计算总页数 
		$pageNum = ($count % $limit) == 0 ? (int)($count / $limit) : ceil($count / $limit);
		//商户配置    用于计算费用
		$businessConfig = $this->db->table('foll_business_settlement')->where(['uid'=>$this->admin['id']])->item();
		//验核数据
		$flagInfo	 = 2;//没有数据
		$peopleNum   = 0;//验证人数，默认0人;
		$ServerMoney = 0;//服务费用
		$money		 = 0;//验核费用
		//数据集合
        $queryOk = Db::name('foll_payment_order')->where($where)->limit($page,$limit)->select();
		if(!empty($queryOk)) {
			$flagInfo = 1;//有数据
			$money 	   = sprintf("%.2f",($count * $businessConfig['payCopyMoney']));
			
			$sum = 0;//订单总额
			foreach($jiesuan as $key=>$val) {//循环对账日数据
				$sum += ($val['payAmount']/100);//分为单位
			}
			foreach($queryOk as $ky=>$vl){
				$queryOk[$ky]['payAmount'] = sprintf("%.2f",($vl['payAmount']/100));
			}
			$ServerMoney = ($sum * $businessConfig['payCopyMoney']);
			
		} else {//没有数据
			
			$order['ServerMoney'] = 0.00;//服务费用
			$order['Money']	   	  = 0.00;//验核费用
			$order['Total']	   	  = 0.00;//合计总额
			$order['count']	   	  = 0;//统计条数  人数
			$order['flagInfo'] 	  = $flagInfo;//是否有数据标识
		
			return json([
				'code'=>2,
				'count'=>$count,
				'data' =>$queryOk,
				'pages'=>$pages ? $pages : '0',
				'tongji'=>$order,
				'limit'=>$limit,
				'pageNum' =>$pageNum ? $pageNum : '0',
				'startDate'=>$start1,
				'endDate'  =>$end1
			]);
		}
		
		$order['ServerMoney'] = $ServerMoney;//服务费用
		$order['Money']	   	  = $money;//验核费用
		$order['Total']	   	  = ($ServerMoney + $money);//合计总额
		$order['count']	   	  = $count;//统计条数  人数
		$order['flagInfo'] 	  = $flagInfo;//是否有数据标识
        
		return json([
			'code'=>1,
			'count'=>$count,
			'data' =>$queryOk,
			'pages'=>$pages,
			'tongji'=>$order,
			'limit'=>$limit,
			'pageNum' =>$pageNum,
			'startDate'=>$start1,
			'endDate'  =>$end1
		]);
	}
	
	
	//确认无误      支付订单 2018-07-12
	public function Orderuploads()
	{
		$uid = $this->admin['id'];//当前商户ID
		$infoId = $_POST['checkId'];//当前确认页的数据ID
		$infoId1 = count($infoId) > 1 ? implode(',',$infoId):$infoId[0];//数组转字符串
		$data   = $_POST;//接收数据
		$flag   = false;
		//更新条件
		$where  = ['startDate'=>$data['start'],'endDate'=>$data['end'],'uid'=>$uid,'type'=>'pay'];
		$confirm = $this->db->table('foll_goodconfirm')->where($where)->item();
		if(!empty($confirm) && ($confirm['confirmId']!=null)) {
			$accout    = explode(',',$confirm['confirmId']);//把数据转为数组；比较传入的数组
			$confirmids = array_diff($infoId,$accout);//计算出不相等的数据更新到数据库中
			$confirmid =  $confirm['confirmId'].','.implode(',',$confirmids);
			/**
			 * 如果有数据，则拼接在后面，没有数据则直接更新
			 */
			$updata['confirmId'] = $confirmid;
			if(isset($data['checkOk']) && ($data['checkOk'] == 'ok')) 
			{
				$updata['status']       = 1;
				$updata['number']       = $data['peoples'];//验核数量
				$updata['serverfee']    = $data['checkingfees'];//服务费用
				$updata['checkingfees'] = $data['checkingfees'];//验核费用
				$updata['totalfees']    = $data['totalfees'];//应付总额
			}
			$flag = $this->db->table('foll_goodconfirm')->where($where)->update($updata);
			
		} else {
			
			$updata['confirmId'] = $infoId1;
			if(isset($data['checkOk']) && ($data['checkOk'] == 'ok'))
			{
				$updata['status'] = 1;
				$updata['number']  = $data['peoples'];//验核数量
				$updata['serverfee'] = $data['checkingfees'];//服务费用
				$updata['checkingfees'] = $data['checkingfees'];//验核费用
				$updata['totalfees'] = $data['totalfees'];//应付总额
			}
			$flag = $this->db->table('foll_goodconfirm')->where($where)->update($updata);
		}
		
		if(!$flag) {//更新失败
			return json(['code'=>0,'msg'=>'对账失败，请重新对!','info'=>$updata]);
		}
		
		if(isset($data['checkOk'])) {//最后一条对账
			return json(['code'=>1,'msg'=>'对账成功!','checkOk'=>$data['checkOk']]);
		} else {//有下一页
			return json(['code'=>1,'msg'=>'请点击下一页对账!','checkOk'=>'no']);
		}
		
		/**
		 * 操作步骤:  先查看当前条件的对账记录中是否存在记录
		 * 有  先查出来，在后面拼接上新的对账id
		 * 没有  直接把新的对账ID更新到表中
		 * Nike blazer X Offwhite studio mid Black
		 */
	}
	
	//有误订单   实名验证2018-07-12
	public function Ordererror()
	{
		$uid = $this->admin['id'];//当前商户ID
		$infoId = $_POST['checkId'];//当前确认页的数据ID
		$infoId1 = count($infoId) > 1 ? implode(',',$infoId):$infoId[0];//数组转字符串
		$data   = $_POST;
		$flag   = false;
		$where  = ['startDate'=>$data['start'],'endDate'=>$data['end'],'uid'=>$uid,'type'=>'pay'];
		$updata = [
			'status'=>2,
			'confirmId'=>$infoId1
		];
		$up = $this->db->table('foll_goodconfirm')->where($where)->update($updata);
		if(!empty($up)) {
			$this->sendEmail('true','805929498@qq.com','海关平台，商户支付订单对账有误!');
			return json(['code'=>1,'msg'=>'有误账单已提交给管理员，请耐心等待!','checkOk'=>'ok']);
		}
		return json(['code'=>0,'msg'=>'有误订单提交失败，请重新提交!']);
	}
	
	
	//报关订单表  支付订单 ********2018-07-16********************结束
	
	
	
	
	
	//电子订单开始 *************2018-07-19*********************
	/**
	 * 对账表  foll_goodconfirm  type 有四种类型;
	 * 1、备案商品  type: goods
	 * 2、实名验证  type: auth
	 * 3、支付订单  type: pay
	 * 4、电子订单  type: order
	 */
	public function Copy()
	{
		//分页配置
        $config = [
            'type' 		 =>'Layui',//分页类名
            'query'		 =>['s'=>'reconcil/auth'],//url额外参数
            'var_page'	 =>'pages',//分页变量
            'newstyle'	 =>true,
        ];
		$order['queryOk'] = $this->db->table('foll_goodconfirm')->where(['uid'=>$this->admin['id'],'type'=>'orders'])->order('accountDay asc')->pagess(16,$config);
		
		foreach($order['queryOk']['lists'] as $key=>$val){
			if($val['status'] == 3){
				$order['queryOk']['lists'][$key]['no'] = 'No';
				break;//返回
			}
			//加多字段
			$order['queryOk']['lists'][$key]['no'] = 'yes';
		}
		//重新排序   
		$order['queryOk']['lists'] = $this->arraySort($order['queryOk']['lists'],'accountDay','desc');
		
		//商户配置    用于计算费用
		$businessConfig = $this->db->table('foll_business_settlement')->where(['uid'=>$this->admin['id']])->item();
		$order['setMothod'] = $businessConfig['setMothod'];//计算方式
		$order['newDate'] = date('Y-m',time());//当前月份
		$order['title']   = '电子订单对账';
		$this->assign('order',$order);
		return view('reconcil/copy');
	}
	
	//获取对账信息；查看账单 2018-07-19
	public function getCopy()
	{
		$start = input('get.startDate')?input('get.startDate'):date('Y-m-d 00:00:00',(time()-86400));
		$end   = input('get.endDate')?input('get.endDate'):date('Y-m-d H:i:s',(strtotime(date('Y-m-d 00:00:00',time()))-1));
		$page  = input('get.page')?input('get.page'):1;
		
		$limit = 5;
		$page  = ($page-1)*$limit;
		//使用时间查出对应的订单ID
		
		$confirmData =  $this->db->table('foll_goodconfirm')->where(['startDate'=>['egt',$start],'endDate'=>['elt',$end]])->item();
		if(empty($confirmData)){
			return json(['code'=>1,'msg'=>'没有对账数据']);
		}
		//商户配置    用于计算费用
		$businessConfig = $this->db->table('foll_business_settlement')->where(['uid'=>$this->admin['id']])->item();
		//订单金额的百分比 计算
		if(!empty($businessConfig) && $businessConfig['cserMothod'] == 'Percentage'){
			//查询条件
	        $where = [
	        	'a.uid'=>$this->admin['id'],
		       	'a.DeclTime'=>[
	        		['egt',$start],
	        		['elt',$end]
	        	]
	        ];
			$dataList = Db::table('ims_foll_elec_order_head_copy')->alias('a')->join('ims_foll_elec_order_detail_copy b','a.id = b.head_id')->where($where)->limit($page,$limit)->select();
			foreach($dataList as $key=>$val){
				$dataList[$key]['Price'] = sprintf("%.2f",$val['Price']);
			}
			$count = Db::table('ims_foll_elec_order_head_copy')->alias('a')->join('ims_foll_elec_order_detail_copy b','a.id = b.head_id')->where($where)->count('goodsNo');
				
			return json(['code'=>0,'msg'=>'Percentage','count'=>$count,'data'=>$dataList]);

		//按订单数量计算
		} elseif(!empty($businessConfig) && $businessConfig['cserMothod'] == 'Element'){
			//查询条件
	        $where = [
	        	'a.uid'=>$this->admin['id'],
		       	'a.DeclTime'=>[
	        		['egt',$start],
	        		['elt',$end]
	        	]
	        ];
			$dataList = Db::table('ims_foll_elec_order_head_copy')->alias('a')->join('ims_foll_elec_order_detail_copy b','a.id = b.head_id')->where($where)->limit($page,$limit)->select();
			foreach($dataList as $key=>$val){
				$dataList[$key]['Price'] = sprintf("%.2f",$val['Price']);
			}
			$count = Db::table('ims_foll_elec_order_head_copy')->alias('a')->join('ims_foll_elec_order_detail_copy b','a.id = b.head_id')->where($where)->count('goodsNo');
				
			return json(['code'=>0,'msg'=>'Element','count'=>$count,'data'=>$dataList]);
		}
	}
	
	
	//获取对账信息；确认对账 2018-07-19
	public function getCopys()
	{
		$start1 = input('post.startDate')?input('post.startDate'):date('Y-m-d 00:00:00',(time()-86400));
		$end1   = input('post.endDate')?input('post.endDate'):date('Y-m-d H:i:s',(strtotime(date('Y-m-d 00:00:00',time()))-1));
		$pages  = input('post.page') ? input('post.page')  : 1;//接收页码
		$limit  = 8;//每页条数
		$page   = ($pages-1)*$limit;//分页数
        
		//验核数据
		$flagInfo	 = 2;//没有数据
		$peopleNum   = 0;//验证人数，默认0人;
		$ServerMoney = 0;//服务费用 
		$money		 = 0;//验核费用
		
		//商户配置    用于计算费用
		$businessConfig = $this->db->table('foll_business_settlement')->where(['uid'=>$this->admin['id']])->item();
		//查询条件
        $where = [
        	'a.uid'=>$this->admin['id'],
	       	'a.DeclTime'=>[
        		['egt',$start1],
        		['elt',$end1]
        	]
        ];
		//连表查询   $where 作为头的条件(foll_goodsreghead) a   对应列表文件   foll_goodsreglist b 条件 a.id = b.head_id
		$jiesuans = Db::table('ims_foll_elec_order_head_copy')->alias('a')->join('ims_foll_elec_order_detail_copy b','a.id = b.head_id')->where($where)->select();
		//电子订单总数
		$count 	 = count($jiesuans);
		//数据总页数
		$pageNum = ($count % $limit) == 0 ? (int)($count / $limit) : ceil($count / $limit);
		//按备案商品计算   需连表查询
		$jiesuan = Db::table('ims_foll_elec_order_head_copy')->alias('a')->join('ims_foll_elec_order_detail_copy b','a.id = b.head_id')->where($where)->limit($page,$limit)->select();
		if(!empty($jiesuan)) {
			$flagInfo = 1;//有数据
			//按订单处理金额的 XX% 需要查出订单的所有金额;
			if($businessConfig['cserMothod'] == 'Percentage') {
				
				$sum = 0;//订单总额
				foreach($jiesuan as $key=>$val) {//循环对账日数据
					//分为单位
					$sum += $val['Price'];	
					$jiesuan[$key]['Price'] = sprintf("%.2f",$val['Price']);
				}
				//计算服务费用     订单总金额  * 设置基数
				$ServerMoney = ($sum * $businessConfig['cserMoney']);
				
			  //按订单处理订单数：XX 元/订单
			} else if($businessConfig['cserMothod'] == 'Element') {
				foreach($jiesuan as $key=>$val) {//循环对账日数据
					$jiesuan[$key]['Price'] = sprintf("%.2f",$val['Price']);
				}
				//计算服务费用   备案订单总数量乘以基数
				$ServerMoney = ($count * $businessConfig['cserMoney']);
			}
			
			$order['ServerMoney'] = $ServerMoney;//服务费用
			$order['Money']	   	  = $money = 0;//验核费用
			$order['Total']	   	  = ($ServerMoney + $money);//合计总额
			$order['count']	   	  = $count;//统计条数  人数
			$order['flagInfo'] 	  = $flagInfo;//是否有数据标识
			
			return json([
				'code'=>1,
				'count'=>$count,
				'data' =>$jiesuan,
				'pages'=>$pages ? $pages : '0',
				'tongji'=>$order,
				'limit'=>$limit,
				'pageNum' =>$pageNum ? $pageNum : '0',
				'startDate'=>$start1,
				'endDate'  =>$end1,
				'type'     =>'good'
			]);
			
		} else {//没有数据
			
			$order['ServerMoney'] = 0.00;//服务费用
			$order['Money']	   	  = 0.00;//验核费用
			$order['Total']	   	  = 0.00;//合计总额
			$order['count']	   	  = 0;//统计条数  人数
			$order['flagInfo'] 	  = $flagInfo;//是否有数据标识
		
			return json([
				'code'=>2,
				'count'=>$count,
				'data' =>$jiesuan,
				'pages'=>$pages ? $pages : '0',
				'tongji'=>$order,
				'limit'=>$limit,
				'pageNum' =>$pageNum ? $pageNum : '0',
				'startDate'=>$start1,
				'endDate'  =>$end1
			]);
		}
	}
	
	
	//确认无误      实名验证  2018-07-19
	public function Copyuploads()
	{
		$uid = $this->admin['id'];//当前商户ID
		$infoId = $_POST['checkId'];//当前确认页的数据ID
		$infoId1 = count($infoId) > 1 ? implode(',',$infoId):$infoId[0];//数组转字符串
		$data   = $_POST;//接收数据
		$flag   = false;
		//更新条件
		$where  = ['startDate'=>$data['start'],'endDate'=>$data['end'],'uid'=>$uid,'type'=>'orders'];
		$confirm = $this->db->table('foll_goodconfirm')->where($where)->item();
		if(!empty($confirm) && ($confirm['confirmId']!=null)) {
			$accout    = explode(',',$confirm['confirmId']);//把数据转为数组；比较传入的数组
			$confirmids = array_diff($infoId,$accout);//计算出不相等的数据更新到数据库中
			$confirmid =  $confirm['confirmId'].','.implode(',',$confirmids);
			/**
			 * 如果有数据，则拼接在后面，没有数据则直接更新
			 */
			$updata['confirmId'] = $confirmid;
			if(isset($data['checkOk']) && ($data['checkOk'] == 'ok')) 
			{
				$updata['status']       = 1;
				$updata['number']       = $data['peoples'];//验核数量
				$updata['serverfee']    = $data['checkingfees'];//服务费用
				$updata['checkingfees'] = $data['checkingfees'];//验核费用
				$updata['totalfees']    = $data['totalfees'];//应付总额
			}
			$flag = $this->db->table('foll_goodconfirm')->where($where)->update($updata);
			
		} else {
			
			$updata['confirmId'] = $infoId1;
			if(isset($data['checkOk']) && ($data['checkOk'] == 'ok'))
			{
				$updata['status'] = 1;
				$updata['number']  = $data['peoples'];//验核数量
				$updata['serverfee'] = $data['checkingfees'];//服务费用
				$updata['checkingfees'] = $data['checkingfees'];//验核费用
				$updata['totalfees'] = $data['totalfees'];//应付总额
			}
			$flag = $this->db->table('foll_goodconfirm')->where($where)->update($updata);
		}
		
		if(!$flag) {//更新失败
			return json(['code'=>0,'msg'=>'对账失败，请重新对!','info'=>$updata]);
		}
		
		if(isset($data['checkOk'])) {//最后一条对账
			return json(['code'=>1,'msg'=>'对账成功!','checkOk'=>$data['checkOk']]);
		} else {//有下一页
			return json(['code'=>1,'msg'=>'请点击下一页对账!','checkOk'=>'no']);
		}
		
		/**
		 * 操作步骤:  先查看当前条件的对账记录中是否存在记录
		 * 有  先查出来，在后面拼接上新的对账id
		 * 没有  直接把新的对账ID更新到表中
		 * Nike blazer X Offwhite studio mid Black
		 */
	}
	
	//有误订单   实名验证   2018-07-19
	public function Copyerror()
	{
		$uid = $this->admin['id'];//当前商户ID
		$infoId = $_POST['checkId'];//当前确认页的数据ID
		$infoId1 = count($infoId) > 1 ? implode(',',$infoId):$infoId[0];//数组转字符串
		$data   = $_POST;
		$flag   = false;
		$where  = ['startDate'=>$data['start'],'endDate'=>$data['end'],'uid'=>$uid,'type'=>'orders'];
		$updata = [
			'status'=>2,
			'confirmId'=>$infoId1
		];
		$up = $this->db->table('foll_goodconfirm')->where($where)->update($updata);
		if(!empty($up)) {
			$this->sendEmail('true','805929498@qq.com','海关平台，商户商品备案对账有误!');
			return json(['code'=>1,'msg'=>'有误账单已提交给管理员，请耐心等待!','checkOk'=>'ok']);
		}
		return json(['code'=>0,'msg'=>'有误订单提交失败，请重新提交!']);
	}
	
	//备案商品结束 **************2018-07-19********************
	
	
	
	
	
	
	
	
	//报关订单表  支付订单
	public function Ordersdd()
	{
		/**
		 * 如果是超级管理员，可查看全部订单，
		 * 用户只能看自己的订单；
		 */
        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'reconcil/Orders'],
            'var_page'=>'page',
            'newstyle'=>true
        ];
        
		if($this->admin['role'] == 1){
			$order = $this->db->table('foll_payment_order')->pages(5,$config);
		} else {
			$order = $this->db->table('foll_payment_order')->where(['uid'=>$this->admin['id']])->pages(5,$config);
		}
		
		$this->assign('order',$order);
		return view('reconcil/Orders');
	}
	
	
	
	/**
	 * 商品详细
	 */
	public function Statistic() {
		$id = (int)input('get.id');
		// 商品详细
		$data['item'] = $this->db->table('foll_goodsreglist')->where(['id'=>$id])->item();
		$this->assign('data',$data['item']);
		return view('reconcil/Statistics');
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
	
	
	//点击有误按钮
	public function Mistaken()
	{
		$info['uid']    	= $this->admin['id'];
		$info['user_name']  = $this->admin['user_name'];
		$info['user_email'] = $this->admin['user_email'];
		
		//昨天的开始时间搓；从0000开始至235959结束
		$begin = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
		$end   = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
		
		$info['startDate'] = date('Y-m-d H:i:s',$begin);//对账开始时间
		$info['endDate']   = date('Y-m-d H:i:s',$end);//对账结束时间
		$info['status']	   = '2';//1、已确认，2、有误，3、未对账
		$info['c_time']	   = date('Y-m-d H:i:s',time());
		$info['type']	   = input('post.token');//计算类型：商品数量good，批次数pici,企业订单order
		//记录商户已对账
		$res = $this->db->table('foll_goodconfirm')->insert($info);
		if(!$res){
			//1有误
			return json(['code'=>1,'msg'=>'数据写入有误！']);
		}
		$this->sendAdmin();//发送邮件给管理员
		//0正确
		return json(['code'=>0,'msg'=>'已通知管理员,请耐心等待！']);
	}
	
	//备案批次详细
	public function piciInfo()
	{
		$id = (int)input('get.id');
		// 商品详细
		$data['item'] = $this->db->table('foll_goodsreghead')->where(['id'=>$id])->item();
		$this->assign('data',$data['item']);
		return view('reconcil/piciInfo');
	}
	
	//发送邮件给管理员
	public function sendAdmin()
	{
		$toemail = '805929498@qq.com';//'198@gogo198.net';
		$name    = '系统管理员';
		$subject = '商户对账有误';
		$content = "提示：商户对账有误,请登录后台核查：<a href='http://shop.gogo198.cn/foll/public/?s=account/login'>点击前往登录核查</a>";
    	$status  = send_mail($toemail,$name,$subject,$content);
	}
	
	//下载批量统计页面
	public function Confirma()
	{
		$data['user_id']    = $this->admin['id'];
		$data['user_name']  = $this->admin['user_name'];
		$data['user_email'] = $this->admin['user_email'];
		$this->assign('data',$data);
		return view('reconcil/Confirma');
	}
	
	//批量统计  2018-06-29  确认下载；
	public function batch() {
		/**
		 * 查出对账日的数据；还有配置计算
		 */
		$userId    = $this->admin['id'];
		$userEmail = $this->admin['user_email'];
		$info['uid']    	= (int)input('post.uid');
		$info['user_name']  = trim(input('post.user_name'));
		$info['user_email'] = trim(input('post.user_email'));
		
		//昨天的开始时间搓；从0000开始至235959结束
		$begin = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
		$end   = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
		
		$info['startDate'] = date('Y-m-d H:i:s',$begin);//对账开始时间
		$info['endDate']   = date('Y-m-d H:i:s',$end);//对账结束时间
		$info['status']	   = '1';
		$info['c_time']	   = date('Y-m-d H:i:s',time());
		$info['type']	   = 'pici';
		
		$ConWhere = [
			'uid'		=> $userId,
			'user_email'=> $userEmail,
			'startDate' => date('Y-m-d H:i:s',$begin),
			'endDate' 	=> date('Y-m-d H:i:s',$end),
			'type'		=> 'pici',
		];
		//检查是否已经对账
		$Confirm = $this->db->table('foll_goodconfirm')->where($ConWhere)->order('id desc')->item();
		if(!empty($Confirm)) {
			$order['status'] = $Confirm['status'];
		} else {
			$order['status'] = 3;//状态：1、已确认，2、有误、3、未对账（显示两个按钮）
		}
		
		/**
		 * 查相关数据
		 * 先查当前用户的配置数据   计算配置数据；
		 */
		//配置数据
		$payConfig = $this->db->table('foll_business_settlement')->where(['uid'=>$userId])->item();
		
		//备案商品数量   暂时先查一条数据
		$goodWhere = [
			'uid'		=> $userId,
			'InputDate'	=> [
				['egt',date('Y-m-d H:i:s',$begin)],//大于等于开始时间
				['elt',date('Y-m-d H:i:s',$end)],//并且小于等于结束时间
			]
		];
		$goodInfo = $this->db->table('foll_goodsreghead')->where($goodWhere)->lists();
		//批次
		$Number   = count($goodInfo);
		//计算批次服务费
		$Money    = ($Number*$payConfig['banMoney']);
		//统计；提交日期，批次数，应收，应付；
		
		$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
  		$PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
  		$PHPSheet->setTitle('备案批次统计'); 	//给当前活动sheet设置名称
  		$PHPSheet->setCellValue('A1','提交日期')
  				 ->setCellValue('B1','批次数')
  				 ->setCellValue('C1','应收服务费')
  				 ->setCellValue('D1','应付服务费');
  		//给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue(‘A1’,’’);
  		$count = count($goodInfo)-1;
  		$num = 0;
  		for($i=0; $i <= $count; $i++) {
  			$num = 2+$i;
  			$PHPSheet->setCellValue("A".$num,$goodInfo[$i]['InputDate'])
  				 ->setCellValue('B'.$num,'1')
  				 ->setCellValue('C'.$num,sprintf("%.2f",($payConfig['banMoney'] * 1)).'元')
  				 ->setCellValue('D'.$num,sprintf("%.2f",($payConfig['banMoney'] * 1)).'元');
  		}
  			$PHPSheet->setCellValue('A'.($num + 1),'统计')
  				 ->setCellValue('B'.($num + 1),"总批次数：{$Number}次")
  				 ->setCellValue('C'.($num + 1),"应收服务费总额：".sprintf("%.2f",$Money).'元')
  				 ->setCellValue('D'.($num + 1),"应付服务费总额：".sprintf("%.2f",$Money).'元');
  		
  		$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
  		$fileName  = "HeadList".date('Y-m-d',time()).'.xlsx';
  		$path      = dirname(__FILE__).'/'.$fileName;
		$PHPWriter->save($path);
		$Result    = $this->sendEmail($path,$info['user_email']);
		unlink($path);
		if(!$Result) {
			$info['status']	   = '3';
			//记录商户已对账
			$this->db->table('foll_goodconfirm')->insert($info);
			return json(['code'=>1,'msg'=>'发送失败']);
		}
		//记录商户已对账
		$this->db->table('foll_goodconfirm')->insert($info);
	    return json(['code'=>0,'data'=>'发送成功']);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
	 * 订单统计与收付结算   旧文件
	 */
	//企业应付
	public function Copys() {
		
		$userId    = $this->admin['id'];
		$userEmail = $this->admin['user_email'];
		//查询配置   用于计算收费；
		$payConfig = $this->db->table('foll_business_settlement')->where(['uid'=>$userId])->item();
		
		//昨天的开始时间搓；从0000开始至235959结束
		$begin = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
		$end   = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
		//统计计算服务费  按电子订单数据计算
		$elecWhere = [
			'uid'=>$userId,
			'DeclTime'=>[
				['egt',date('Y-m-d H:i:s',$begin)],//大于等于开始时间
				['elt',date('Y-m-d H:i:s',$end)]//并且小于等于结束时间
			],
		];
		
		$elecMoney = 0;//计算所有订单总额
		$number    = 0;//订单数量
		$sunNum	   = 0;//服务费用
		//获取电子订单  计算服务费用
		$elec  = $this->db->table('foll_elec_order_head a')->join(['foll_elec_order_detail b','a.id=b.head_id'])->field('a.DeclTime,b.OrderGoodTotal')->where($elecWhere)->listsJoin();
		if(!empty($elec)) {//有数据
			foreach($elec as $key=>$val) {
				$elecMoney += $val['OrderGoodTotal'];
				$number++;
			}
		
			if($payConfig['cserMothod'] == 'Percentage'){//按订单金额计算
				
				$sunNum = ($elecMoney * $payConfig['cserMoney']);
				
			} else if($payConfig['cserMothod'] == 'Element'){//按订单数量结算
				
				$sunNum = ($number * $payConfig['cserMoney']);
			}
			//应付金额
			$sunNum = sprintf("%.2f",$sunNum);
		}
		
		$Server['sunNum'] = $sunNum;//服务费用
		
		//验核条件
		$realWhere = [
			'uid'=>$userId,
			'submitTime'=>[
				['egt',date('YmdHis',$begin)],//大于等于开始时间
				['elt',date('YmdHis',$end)]//并且小于等于结束时间
			],
			
		];
		$RealNum   = 0;//验核人数
		$RealMoney = 0;//验核费用
		//获取实名认证订单，计算服务费用
		$RealName = $this->db->table('foll_payment_userinfo')->where($realWhere)->field('id')->lists();
		if(!empty($RealName)) {
			$RealNum   = count($RealName);
			$RealMoney = sprintf("%.2f",($RealNum * $payConfig['authCopyMoney']));
		}
		$Server['RealMoney'] = $RealMoney;//验核费用
		
		//支付单费用
		$payWhere = [
			'uid'=>$userId,
			'submitTime'=>[
				['egt',date('YmdHis',$begin)],
				['elt',date('YmdHis',$end)]
			],
		];
		$payMoney = 0;//交易费用
		$payCount = 0;//订单总额
		//订单时间、订单编号、订单金额、订单买家、服务费用、验核费用、交易费用、应付总额
		$fild     = 'id,submitTime,orderNo,payAmount,payerName';
		$config = [
            'type' =>'Layui',
            'query'=>['s'=>'reconcil/Copy'],
            'var_page'=>'page',
            'newstyle'=>true
        ];
        
		$Server['info'] = $payOrder = $this->db->table('foll_payment_order')->where($payWhere)->field($fild)->pages(6,$config);
		if(!empty($payOrder['lists'])) {
			foreach($payOrder['lists'] as $key=>$val) {
				//$payCount += $val['payAmount'];
				$Server['info']['lists'][$key]['submitTime'] = date('Y-m-d H:i:s',strtotime($val['submitTime']));
				$Server['info']['lists'][$key]['payAmount']  = sprintf('%.2f',($val['payAmount']/100));
			}
			$payMoney = sprintf("%.2f",(($payCount/100) * $payConfig['payCopyMoney']));
		}
		
		$payOrders = $this->db->table('foll_payment_order')->where($payWhere)->field($fild)->lists();
		if(!empty($payOrders)) {
			foreach($payOrders as $key=>$val) {
				$payCount += $val['payAmount'];
			}
			$payMoney = sprintf("%.2f",(($payCount/100) * $payConfig['payCopyMoney']));
		}
		
		$Server['payMoney'] = $payMoney;//交易费用
		//应付总额
		$Total = ($sunNum+$RealMoney+$payMoney);
		$Server['Total'] = $Total;//应付总额
		
		
		$ConWhere = [
			'uid'		=> $userId,
			'user_email'=> $userEmail,
			'startDate' => date('Y-m-d H:i:s',$begin),
			'endDate' 	=> date('Y-m-d H:i:s',$end),
		];
		//检查是否已经对账
		$Confirm = $this->db->table('foll_goodconfirm')->where($ConWhere)->order('id desc')->item();
		if(!empty($Confirm)) {
			$Server['status'] = $Confirm['status'];
		} else {
			$Server['status'] = 3;//状态：1、已确认，2、有误、3、未对账（显示两个按钮）
		}
		
		$this->assign('data',$Server);
		return view('reconcil/copy');
	}
	
	
	//查看详情  订单统计与收付结算的
	public function Checkdetail() {
		$id = input('get.id');
		$detail = $this->db->table('foll_payment_order')->where(['id'=>$id])->item();
		
		$this->assign('data',$detail);
		return view('reconcil/Checkdetail');
	}
	
	//确认订单按钮
	public function CheckBtn(){
		$info['user_id']	= $this->admin['id'];
		$info['user_name']  = $this->admin['user_name'];
		$info['user_email'] = $this->admin['user_email'];
		
		$this->assign('data',$info);
		return view('reconcil/Checkbtn');
	}
	
	//确认下载
	public function Checkok() {
		
		$userId    = $this->admin['id'];
		$userEmail = $this->admin['user_email'];
		$userName  = trim(input('post.user_name'));
		$userEmails = trim(input('post.user_email')) ? trim(input('post.user_email')) : $this->admin['user_email'];
		//查询配置   用于计算收费；
		$payConfig = $this->db->table('foll_business_settlement')->where(['uid'=>$userId])->item();
		
		//昨天的开始时间搓；从0000开始至235959结束
		$begin = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
		$end   = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
		
		$ConWhere = [
			'uid'		=> $userId,
			'user_email'=> $userEmail,
			'startDate' => date('Y-m-d H:i:s',$begin),
			'endDate' 	=> date('Y-m-d H:i:s',$end),
			'type'		=> 'order',
		];
		//检查是否已经对账
		$Confirm = $this->db->table('foll_goodconfirm')->where($ConWhere)->order('id desc')->item();
		if(!empty($Confirm) && $Confirm['status'] == 1) {
			exit('您的账单已确认，请勿重复操作!');			
		}
		
		//统计计算服务费  按电子订单数据计算
		$elecWhere = [
			'uid'=>$userId,
			'DeclTime'=>[
				['egt',date('Y-m-d H:i:s',$begin)],//大于等于开始时间
				['elt',date('Y-m-d H:i:s',$end)]//并且小于等于结束时间
			],
		];
		
		$elecMoney = 0;//计算所有订单总额
		$number    = 0;//订单数量
		$sunNum	   = 0;//服务费用
		//获取电子订单  计算服务费用
		$elec  = $this->db->table('foll_elec_order_head a')->join(['foll_elec_order_detail b','a.id=b.head_id'])->field('a.DeclTime,b.OrderGoodTotal')->where($elecWhere)->listsJoin();
		if(!empty($elec)) {//有数据
			foreach($elec as $key=>$val) {
				$elecMoney += $val['OrderGoodTotal'];
				$number++;
			}
		
			if($payConfig['cserMothod'] == 'Percentage'){//按订单金额计算
				
				$sunNum = ($elecMoney * $payConfig['cserMoney']);
				
			} else if($payConfig['cserMothod'] == 'Element'){//按订单数量结算
				
				$sunNum = ($number * $payConfig['cserMoney']);
			}
			//应付金额
			$sunNum = sprintf("%.2f",$sunNum);
		}
		
		$Server['sunNum'] = $sunNum;//服务费用
		
		//验核条件
		$realWhere = [
			'uid'=>$userId,
			'submitTime'=>[
				['egt',date('YmdHis',$begin)],//大于等于开始时间
				['elt',date('YmdHis',$end)]//并且小于等于结束时间
			],
			
		];
		$RealNum   = 0;//验核人数
		$RealMoney = 0;//验核费用
		//获取实名认证订单，计算服务费用
		$RealName = $this->db->table('foll_payment_userinfo')->where($realWhere)->field('id')->lists();
		if(!empty($RealName)) {
			$RealNum   = count($RealName);
			$RealMoney = sprintf("%.2f",($RealNum * $payConfig['authCopyMoney']));
		}
		$Server['RealMoney'] = $RealMoney;//验核费用
		
		//支付单费用
		$payWhere = [
			'uid'=>$userId,
			'submitTime'=>[
				['egt',date('YmdHis',$begin)],
				['elt',date('YmdHis',$end)]
			],
		];
		$payMoney = 0;//交易费用
		$payCount = 0;//订单总额
		//订单时间、订单编号、订单金额、订单买家、服务费用、验核费用、交易费用、应付总额
		$fild     = 'id,submitTime,orderNo,payAmount,payerName';
		$config = [
            'type' =>'Layui',
            'query'=>['s'=>'reconcil/Copy'],
            'var_page'=>'page',
            'newstyle'=>true
        ];
        
		$Server['info'] = $payOrder = $this->db->table('foll_payment_order')->where($payWhere)->field($fild)->lists();
		if(!empty($payOrder)) {
			foreach($payOrder as $key=>$val) {
				$payCount += $val['payAmount'];
				$Server['info'][$key]['submitTime'] = date('Y-m-d H:i:s',strtotime($val['submitTime']));
				$Server['info'][$key]['payAmount']  = sprintf('%.2f',($val['payAmount']/100));
			}
			$payMoney = sprintf("%.2f",(($payCount/100) * $payConfig['payCopyMoney']));
		}
		
		$Server['payMoney'] = $payMoney;//交易费用
		//应付总额
		$Server['Total'] = $Total = ($sunNum+$RealMoney+$payMoney);
		
		$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
  		$PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
  		$PHPSheet->setTitle('订单统计与收付结算'); //给当前活动sheet设置名称
  		$PHPSheet->setCellValue('A1','订单时间')
  				 ->setCellValue('B1','订单编号')
  				 ->setCellValue('C1','订单金额')
  				 ->setCellValue('D1','订单买家');
  		//给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue(‘A1’,’’);
  		$count = count($Server['info'])-1;
  		$num = 0;
  		for($i=0; $i <= $count; $i++) {
  			$num = 2+$i;
  			$PHPSheet->setCellValue("A".$num,$Server['info'][$i]['submitTime'])
  				 ->setCellValue('B'.$num,"\t".$Server['info'][$i]['orderNo']."\t")
  				 ->setCellValue('D'.$num,$Server['info'][$i]['payerName'])
  				 ->setCellValue('C'.$num,sprintf("%.2f",($Server['info'][$i]['payAmount'] * 1)).'元');
  		}
  			$PHPSheet->setCellValue('A'.($num + 1),'服务费用:'.sprintf("%.2f",$sunNum).'元')
  				 ->setCellValue('B'.($num + 1),'验核费用'.sprintf("%.2f",$RealMoney).'元')
  				 ->setCellValue('C'.($num + 1),"交易费用：".sprintf("%.2f",$payMoney).'元')
  				 ->setCellValue('D'.($num + 1),"应付总额：".sprintf("%.2f",$Total).'元');
  		
  		$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
  		$fileName  = "PortOrder".date('Y-m-d',time()).'.xlsx';
  		$path      = dirname(__FILE__).'/'.$fileName;
		$PHPWriter->save($path);
		$Result    = $this->sendEmail($path,$userEmails,'订单统计与收付结算');
		unlink($path);
		//记录商户确认账单
		$ConWhere['user_email'] = $userEmails;
		$ConWhere['user_name']  = $userName;
		$ConWhere['c_time']		= date('Y-m-d H:i:s',time());
		$ConWhere['status']	    = '1';
		if(!$Result) {
			$ConWhere['status'] = '3';
			//记录商户已对账
			$this->db->table('foll_goodconfirm')->insert($ConWhere);
			return json(['code'=>1,'msg'=>'发送失败']);
		}
		//记录商户已对账
		$this->db->table('foll_goodconfirm')->insert($ConWhere);
	    return json(['code'=>0,'data'=>'发送成功']);
	}
	
	
	/**
	 * 2018-07-03
	 * @author 赵金如
	 * 管理员：修改统计数据 
	 */
	public function Errors()
	{
		$config = [
            'type' 		=>'Layui',
            'query'		=>['s'=>'reconcil/Errors'],
            'var_page'	=>'page',
            'newstyle'	=>true
        ];
		$ErrorOrder = $this->db->table('foll_goodconfirm')->where(['status'=>2])->pages(8,$config);
		
		$this->assign('data',$ErrorOrder);
		return view('reconcil/Errors');
	}
	
}
?>