<?php

namespace app\index\controller;

use app\index\controller;
use think\Request;
use think\Loader;
use think\Db;
use Util\data\Sysdb;
use CURLFile;
use PHPExcel_IOFactory;
use PHPExcel;
/**
**  发票管理  查询
**/
class Invoice extends CommonController {
	
	public function index() {
		
		$this->db = new Sysdb;
		
		$order['title'] = '发票订单列表';
		$wh['uniacid'] = '14';
		$start_time    = !empty(input('post.start_time')) ? input('post.start_time') : input('get.start_time');
		$end_time      = !empty(input('post.end_time'))   ? input('post.end_time')   : input('get.end_time');
		$ordersn       = trim(input('post.ordersn'));
	   
		if(!empty($start_time) && !empty($end_time)) {
			$wh['create_date'] = ['between',[strtotime($start_time),strtotime($end_time)]];
		}
	   
		if(!empty($ordersn)){
		   $wh['DDH'] = $ordersn;
		}
		
		$config = [
            'type' =>'Layui',
            'query'=>['s'=>'Invoice/index','start_time'=>$start_time,'end_time'=>$end_time],
            'var_page'=>'page',
            'newstyle'=>true
        ];
		// 数据信息
		$opera = $this->db->table('invoices_ord')->field('id,uniacid,DDH,FP_HM,KPHJJE,PDF_URL,create_date,state,oids,openid')->where($wh)->order('create_date desc')->pages(6,$config);
		if(empty($opera['lists'])){
			echo '没有数据。。。';
		}
		
		$this->assign('order',$opera);
		return $this->fetch('invoice/index');
	}
	
	
	
	
	// 停车订单详细信息
	public function infos() {
		
		$this->db = new Sysdb;
		$gid = input('get.gid');
		if($gid != 'undefined' || !empty($gid)) {
			
			$ord = Db::table('ims_invoices_ord')->field('oids')->where(['id'=>$gid])->find();
			if(empty($ord)){
				exit('查询不到该数据！');
			}
			
			$sql = "SELECT a.ordersn,a.pay_account,a.pay_type,a.pay_time,a.create_time,b.CarNo,b.number,b.duration FROM ims_foll_order AS a LEFT JOIN ims_parking_order AS b ON a.ordersn=b.ordersn WHERE a.id IN(".$ord['oids'].")";
			//echo $sql;
			$order = Db::query($sql);
			if(empty($order)){
				exit('没有数据');
			}
			$payType = '';
			foreach($order as $key=>&$v){
				switch($v['pay_type']){
					case 'Parks':
						$payType = '银联免密';
					break;
					
					case 'wechat':
						$payType = '微信支付';
					break;
					
					case 'alipay':
						$payType = '支付宝支付';
					break;
					
					case 'FAgro':
						$payType = '农商免密';
					break;
					
					case 'Fwechat':
						$payType = '微信免密';
					break;
					
					case 'other':
						$payType = '其他支付';
					break;
				}
				
				$v['pay_type'] = $payType;
				$v['pay_time'] = date('Y-m-d H:i:s',$v['pay_time']);
				$v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
			}

			$this->assign('order',$order);
		} else {
			exit('没有数据');
		}
		return $this->fetch('invoice/lists');//模板渲染
		//return $this->fetch('invoice/edits');//模板渲染
	}



    // 开票人信息  2018-11-08
    public function infoss() {
        header("Content-Type:text/html;charset=utf-8");
        $this->db = new Sysdb;
        $gid = trim(input('get.opid'));
        if($gid != 'undefined' || !empty($gid)) {

            $ord = $this->db->table('parking_authorize')->field(['mobile','name','auth_status','auth_type','CarNo','create_time'])->where(['openid'=>$gid])->item();
            if(empty($ord)){
                exit('查询不到该数据！');
            }

            $ord['create_time'] = date('Y-m-d H:i:s',$ord['create_time']);
            if(!empty($ord['auth_type'])) {
                switch ($ord['auth_type']) {
                    case 'a:1:{s:2:"wg";s:11:"FCreditCard";}':
                        $ord['type'] = '银联免密';
                        break;
                    case 'a:1:{s:2:"wx";s:7:"Fwechat";}':
                        $ord['type'] = '微信免密';
                        break;
                    case 'a:1:{s:2:"sd";s:5:"FAgro";}':
                        $ord['type'] = '农商免密';
                        break;
                    default:
                        $ord['type'] = '未授权';
                        break;
                }
            } else {
                $ord['type'] = '未授权';
            }
            //print_r($ord);
            $this->assign('order',$ord);
        } else {
            exit('没有数据');
        }
        return $this->fetch('invoice/infoss');//模板渲染
    }
	
	
	// 订单导出
	public function espost() {
		$this->db = new Sysdb;
		$start_time    = input('post.start_time');
		$end_time      = input('post.end_time');
		$wh = [];
		if(!empty($start_time) && !empty($end_time)) {
			$wh['create_date'] = ['between',[strtotime($start_time),strtotime($end_time)]];
			$field = 'FPQQLSH,DDH,KP_NSRSBH,KP_NSRMC,GHF_NSRSBH,GHF_MC,GHF_SJ,XMMC,XMDJ,SL,SE,FP_HM,FP_DM,JYM,state,GHF_EMAIL,create_date';
			$data = $this->db->table('invoices_ord')->field($field)->where($wh)->lists();
			
		} else {
			//echo '没有订单导出';//json_encode(['code'=>0,'msg'=>'导出订单时间为空']);
			//header('');
			return $this->error('没有订单导出');
		}
		//echo json_encode(['code'=>1,'wh'=>$wh,'data'=>$data,'FILE'=>__FILE__]);
		$this->ExcelImplement($data);
		//$this->success('导出成功','Invoice/index');
	}
	
	// 执行导出Excel文件
	private function ExcelImplement($data) {
		
		//$PHPExcel = new PHPExcel();//实例化PHPExcel
		//$phpeSheet = $PHPExcel->getActiveSheet();//获得当前活动sheet的操作对象
		
		$PHPExcel = new PHPExcel();
		$PHPExcel->setActiveSheetIndex(0);
		$phpeSheet = $PHPExcel->getActiveSheet();
		$phpeSheet->setTitle('sheet1');
			
		$phpeSheet->setTitle('发票订单导出');
		$phpeSheet->setCellValue('A1','发票流水号')
				 ->setCellValue('B1','订单编号')
				 ->setCellValue('C1','开票方识别号')
				 ->setCellValue('D1','开票方名称')
				 ->setCellValue('E1','购货方识别号')
				 ->setCellValue('F1','购货方名称')
				 ->setCellValue('G1','购货方手机')
				 ->setCellValue('H1','项目名称')
				 ->setCellValue('I1','项目单价')
				 ->setCellValue('J1','税率')
				 ->setCellValue('K1','税额')
				 ->setCellValue('L1','发票号码')
				 ->setCellValue('M1','发票代码')
				 ->setCellValue('N1','检验码')
				 ->setCellValue('O1','开票状态')
				 ->setCellValue('P1','购货方邮箱')
				 ->setCellValue('Q1','发票日期');
				 
		$count  = count($data)-1;
		$num    = 0;
		for($i=0; $i<=$count; $i++){
			$num = 2+$i;
			$phpeSheet->setCellValue('A'.$num,"\t".$data[$i]['FPQQLSH']."\t")
				 ->setCellValue('B'.$num,"\t".$data[$i]['DDH']."\t")
				 ->setCellValue('C'.$num,"\t".$data[$i]['KP_NSRSBH']."\t")
				 ->setCellValue('D'.$num,$data[$i]['KP_NSRMC'])
				 ->setCellValue('E'.$num,$data[$i]['GHF_NSRSBH'])
				 ->setCellValue('F'.$num,$data[$i]['GHF_MC'])
				 ->setCellValue('G'.$num,$data[$i]['GHF_SJ'])
				 ->setCellValue('H'.$num,$data[$i]['XMMC'])
				 ->setCellValue('I'.$num,$data[$i]['XMDJ'])
				 ->setCellValue('J'.$num,$data[$i]['SL'])
				 ->setCellValue('K'.$num,$data[$i]['SE'])
				 ->setCellValue('L'.$num,$data[$i]['FP_HM'])
				 ->setCellValue('M'.$num,$data[$i]['FP_DM'])
				 ->setCellValue('N'.$num,$data[$i]['JYM'])
				 ->setCellValue('O'.$num,($data[$i]['state']==1 ? '开票成功':'开票失败'))
				 ->setCellValue('P'.$num,$data[$i]['GHF_EMAIL'])
				 ->setCellValue('Q'.$num,date("Y-m-d H:i:s",$data[$i]['create_date']));
			//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，
			$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');
			$fileName  = 'fpdc'.date('YmdHis',time()).'.xls';
			// 创建文件
			//$path      = dirname(__FILE__).'/'.$fileName;
			// 保存文件
			//$PHPWriter->save($path);
			/*header("Content-type: application/octet-stream");
            header("Accept-Ranges: bytes");
            header("Accept-Length: ".filesize($path));
            header("Content-Disposition: attachment; filename=" . $fileName);
			unset($path);*/
			
			/*header('Content-Type:application/vnd.ms-excel');
			header("Content-Disposition: attachment;filename=".$fileName);
			header('Cache-Control: max-age=0');
			$PHPWriter->save('php://output');
			unset($path);
			exit;*/
			ob_end_clean();
			ob_start();
			header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");
            header('Content-Disposition:attachment;filename="' . $fileName . '"');
            header("Content-Transfer-Encoding:binary");
            $PHPWriter->save('php://output');
			
		}
	}
}



?>