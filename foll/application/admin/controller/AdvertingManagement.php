<?php
namespace app\admin\controller;
use app\admin\controller;
use think\Db;
use think\Loader;
use think\Request;
use think\Session;

class AdvertingManagement extends Auth
{
	public function index(){
		$adv_model=Loader::model('AdvertingManagement','model');
		$config = [
			'path'=>'',
            'query'=>['s'=>'advertingManagement/index'],
            'var_page'=>'page'
        ];
		$where='1 = 1';
		$total=Db::table("ims_foll_advertising_order")->count();
		$data=Db::table('ims_foll_advertising_order')
		->alias('a')
		->join('ims_foll_advertising b','a.adv_id = b.id')
		->join('ims_foll_business_admin c','a.business_id = c.id')
		->field('a.*,b.adv_name,c.user_name')
		->paginate(10,$total,$config);//每页显示数量,总数,分页配置
		$datas=$data->toArray()['data'];
		foreach($datas as $k=>$v){
			$adv=Db::table('ims_foll_advertising_content')->where(['order_id'=>$v['id']])->find();
			if($adv){
				$datas[$k]['is_adv']='1';//已上传广告 ,编辑广告内容
			}else{
				$datas[$k]['is_adv']='0';//未上传广告,添加广告内容
			}
		}
//		print_r($datas);die;
		return view('advertingmanagement/list',[
		'title'=>'广告管理',
		'data'=>$datas,
		'page'=>$data->render()
		]);
	}
	
	public function add(Request $requst){
		$adv_model=Loader::model('AdvertingManagement','model');
		return view('advertingmanagement/add',[
			'title'=>'添加广告',
			'data'=>$adv_model->order_one($requst->get('id')),
		]);
	}
	
	public function edit(Request $requst){
		$adv_model=Loader::model('AdvertingManagement','model');
		return view('advertingmanagement/edit',[
			'title'=>'添加广告',
			'data'=>$adv_model->order_ones($requst->get('id')),
		]);
	}
	
	public function upload_save(Request $requst){
		$adv_model=Loader::model('AdvertingManagement','model');
		$data=[
			'order_id'=>$requst->post('order_id'),
			'content'=>$requst->post('content'),
			'url'=>$requst->post('url'),
			'min_times'=>$requst->post('min_times'),
			'create_time'=>time(),
			'status'=>$requst->post('status'),
			'uniacid'=>$requst->post('uniacid'),
		];
		
		$add=$adv_model->upload_adv($data);
		if($add){
			$this->success('添加成功', Url("admin/AdvertingManagement"));
		}else{
			$this->error('添加失败');
		}
	}
	
	public function upload_edit(Request $requst){
		$adv_model=Loader::model('AdvertingManagement','model');
		$order_data=Db::table('ims_foll_advertising_content')->where(['order_id'=>$requst->post('order_id')])->find();
		$update = Db::table('ims_foll_advertising_content')
		->where('id',$order_data['id'])
		->update([
			'status'=>$requst->post('status'),
			'content'=>$requst->post('content'),
			'url'=>$requst->post('url'),
			'min_times'=>$requst->post('min_times'),
			'uniacid'=>$requst->post('uniacid')
		]
		);
		if($update){
			$this->success('编辑成功', Url("admin/AdvertingManagement"));
		}else{
			$this->error('编辑失败');
		}
	}
	
	public function del(){
		
	}
	
	public function invoice(){
		$adv_model=Loader::model('AdvertingManagement','model');
		$Status=['status'=>[0=>'已作废',1=>'申请中',2=>'开票中',3=>'已开票',4=>'已寄送',5=>'拒绝申请']];
		return view('advertingmanagement/invoice_list',[
			'title'=>'发票管理',
			'Status'=>$Status,
			'data'=>$adv_model->get_invoice(),
		]);
	}
	
	//对申请发票的订单做处理
	public function invoiceStatus(Request $requst){
		$id = $requst->post('id');
		$status = $requst->post('status');
		$invoice=Db::table('ims_foll_advertising_invoice')->where(['id'=>$id])->find();
		$Status=['status'=>[0=>'已作废',1=>'申请中',2=>'开票中',3=>'已开票',4=>'已寄送',5=>'拒绝申请']];
		if($invoice['status'] != '1'){
			return json(['status'=>false,'code'=>'12','msg'=>'该发票'.$Status['status'][$invoice['status']]]);
		}else{
			$edit_status=Db::table('ims_foll_advertising_invoice')
			->where(['id'=>$id])
			->update(['status'=>$status]);
			if($edit_status){
				$make_invoice = $this->make_invoice($id);
				return json(['status'=>true,'code'=>'1','msg'=>'确认开票']);
			}else{
				return json(['status'=>false,'code'=>'101','msg'=>'请勿重复操作']);
			}
		}
	}
	
	public function make_invoice($id){
		$admin_data=Session::get('UserResutlt');
		$order_invoice=Db::table('ims_foll_advertising_invoice')
		->alias('a')
		->join('ims_foll_business_admin b','a.business_id = b.id')
		->field('a.*,b.user_name')
		->where('a.id = '.$id)
		->find();
		
		
//		$data1=Db::query('SELECT * FROM ims_ewei_shop_order WHERE `pay_status` = 1 and `business_id` = '.$admin_data['id'].' and id in ('.trim($invoice_id,',').')');
//		$order=Db::table('ims_foll_advertising_order')
//		->alias('a')
//		->join('ims_foll_business_admin b','a.business_id = b.id')
//		->field('a.*,b.user_name')
//		->where('a.id','exp',' IN ('.$order_invoice['order_id'].') ')
//		->select();
//		echo '<pre>';
//		echo Db::table('ims_foll_advertising_invoice')->getLastSql();
//		echo '<br>';
//		print_r($order_invoice);
//		echo '<br>';
//		print_r($order);
//		echo '<br>';
//		echo Db::table('ims_foll_advertising_order')->getLastSql();
//		die;
		$data=$this->ihttp_post('http://shop.gogo198.cn/sendMsg/send.php',$order_invoice);
		print_r($data);
		/*
				//获取所有要开票的信息；
//				$upInvoice = pdo_fetchall("SELECT id,ordersn,price FROM " . tablename('foll_advertising_order') . " WHERE invoice_iskp = 0 and uniacid = ". $_W['uniacid']. " and openid = '".$_W['openid']."' and status = 1 and id in(".trim($_GPC['parkCheck'],',').")");				
				if(!empty($order)) {
					
					$sendArr = [
						'uniacid'=> $order['uniacid'],
						'first'=>'您好,您有一份订单开票申请！',
						'ordersn'=>$order['ordersn'],
						'name'=>$order_invoice['user_name'],
						'xmmc'=>'商城开票服务',
						'c_date'=>date('Y-m-d H:i:s',time()),
						'remark'=>'您有新的开票申请，请点击详情完成开票！',
						'touser'=>'oR-IB0t4Yc9zmV-K-_5NRB-u5k4U',
//						'touser' => 'oR-IB0h7w3lGAxFTeeVAR3LraBZI',//接收处理人Openid
						//$_GPC['parkCheck']:ewei_shop_order 表中所有开票的自增ID，$head['id']：开票人的抬头信息自增ID；
						'Reurl'=>'http://shop.gogo198.cn/app/index.php?i='.$order['uniacid'].'&c=entry&m=ewei_shopv2&do=mobile&r=parking.invoicelist.check_invoiceShop&kpid='.$_GPC['parkCheck'].'&headid='.$head['id'],			
					];
					
					$senRes = sendInvoices($sendArr);
					if($senRes == '发送成功') {
						$res = [
							'statu'=> 'success',
							'pdfurl'=> '',
						];
						echo json_encode($res);	
					}else {
						$res = [
							'statu'=> 'error',
							'pdfurl'=> '',
						];
						echo json_encode($res);	
					}
					
				}
				*/

		
	}
	
	//通过curl模拟post的请求；  
	function SendDataByCurl($url,$data=array()){
        //对空格进行转义  
//	    $url = str_replace(' ','+',$url);  
	    $ch = curl_init();  
	    //设置选项，包括URL  
	    curl_setopt($ch, CURLOPT_URL,$url);  
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
	    curl_setopt($ch, CURLOPT_HEADER, 0);  
	    curl_setopt($ch,CURLOPT_TIMEOUT,3);  //定义超时3秒钟    
	     // POST数据  
	    curl_setopt($ch, CURLOPT_POST, 1);  
	    // 把post的变量加上  
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);    //所需传的数组用http_bulid_query()函数处理一下，就ok了  
	      
	    //执行并获取url地址的内容  
	    $output = curl_exec($ch);  
	    $errorCode = curl_errno($ch);  
	    //释放curl句柄  
	    curl_close($ch);  
//	    if(0 !== $errorCode) {  
//	        return false;  
//	    }  
	    return $output;
    }
    
    function ihttp_post($url,$post_data) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	
	public function material(Request $requst){
		return view('advertingmanagement/material',['title'=>'素材']);
	}
	
	public function material_save(Request $requst){
		
	}
}
?>