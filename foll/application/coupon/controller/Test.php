<?php


namespace app\coupon\Controller;

use think\Request;
use think\Controller;
use PHPExcel;
use PHPExcel_IOFactory;
use think\Db;
use Util\data\Sysdb;
class Test extends Controller{



    public function index(Request $request) {
		$this->db = new Sysdb;
        if ($request->isGET()){
            return view('test/index');
        }else{
            $file = $request->file('file');
            if ( !$file ) {
                return json(['code' => -1, 'msg' => '错误']);
            }
            $fileObj  = $file->move(ROOT_PATH . 'public/uploads/xls');
            $fileName = $fileObj->getSaveName();
            $fileName = ROOT_PATH.'public/uploads/xls/'.$fileName;
            $objReader = PHPExcel_IOFactory::createReader( PHPExcel_IOFactory::identify($fileName));
            $PHPExcel = $objReader->load($fileName);
            $curSheet = $PHPExcel->getSheet(0);
            $rowCount = $curSheet->getHighestRow();
            $data = [];
            $n= 0;
            for($i=2;$i<=$rowCount;$i++){
                $data[$n]['order_id']=trim($curSheet->getCell('A'.$i)->getValue(),' ');
                $data[$n]['upOrderId']= trim($curSheet->getCell('B'.$i)->getValue());
                $data[$n]['pay_time']   = trim($curSheet->getCell('C'.$i)->getValue());
                $n++;
            }
            //Db::startTrans();
			$this->db->startTranss();
            try{
				
                foreach ($data as $val) {
                    //Db::name('foll_order')->where('ordersn='.$val['order_id'].' and upOrderId <>""')->update([
					/*Db::name('foll_order')->where('ordersn='.$val['order_id'].' and pay_status=2')->update([
                        'pay_status'=>1,
                        'pay_time'=>strtotime($val['pay_time']),
                        'upOrderId'=>$val['upOrderId']
                    ]);
                    Db::name('parking_order')->where('ordersn',$val['order_id'])->update(['status'=>'已结算']);*/
					
					$dat = [
						'pay_status'=>1,
                        'pay_time'=>strtotime($val['pay_time']),
                        'upOrderId'=>$val['upOrderId']
					];
					$this->db->table('foll_order')->where(['ordersn'=>$val['order_id'],'pay_type'=>'Fwechat'])->update($dat);
					
					$this->db->table('parking_order')->where(['ordersn'=>$val['order_id']])->update(['status'=>'已结算']);
					
					/*$d = $this->db->table('foll_order')->field('ordersn,pay_status,upOrderId')->where(['pay_status'=>'2','ordersn'=>$val['order_id']])->item();
					echo '<pre>';
					print_r($d);*/
					echo '<br>订单号：'.$val['order_id'];
                }
                //Db::commit();
				$this->db->commits();
            }catch (\Exception $exception){
                //Db::rollback();
				$this->db->rollbacks();
                throw new \Exception($exception->getMessage().'行号：'.$exception->getLine());
            }
            @unlink($fileName);
			
			/*$i = 1;
			foreach ($data as $val){				
				$data = Db::name('foll_order')->where(['pay_status'=>'2','ordersn'=>$val['order_id']])->field('pay_status,upOrderId,ordersn')->find();
				echo '<pre>';
				print_r($data);
				echo '<br>数量'.$i;
				$i++;
			}
			
            @unlink($fileName);*/
            echo '完成';
        }

    }
}