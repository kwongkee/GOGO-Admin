<?php

namespace app\index\controller;
use app\index\controller;
use think\Db;
use think\Log;
use think\Request;
use think\Session;
use think\Loader;
use PHPExcel;
use PHPExcel_IOFactory;

class Charge extends CommonController
{
    protected $Excel;
    public function index(){
        $type=[0=>'停车时长满24小时',1=>'停入当天23:59前',2=>'未选择类别'];
        $chargeResult = Db::table("ims_parking_charge")->select();
//        foreach ($chargeResult as &$value){
//            $value['payPeriod']=json_decode($value['payPeriod'],true);
//        }
        foreach ($chargeResult as &$val){
            $val['period_rand']=json_decode($val['period_rand'],true);
        }
        return view("charge/index",['list'=>$chargeResult,'type'=>$type]);
    }

    public function addCharge()
    {
        return view("charge/charge_add");
    }
    public function saveCharge(Request $request)
    {
        $periodTime=json_decode($request->post('periodTime'),true);
        if(empty($periodTime)){
            return json(['code'=>10001,'status'=>false,'msg'=>'时间段不能为空']);
        }
        if(empty($request->post('plan'))){
            return json(['code'=>10001,'status'=>false,'msg'=>'方案名称不能空']);
        }
        $uid=Session::get('UserResutlt');
        try{
            Db::table("ims_parking_charge")->insert([
                    'payPeriod'     =>json_encode($periodTime),
                    'ChargeClass'   =>$request->post('plan'),
                    'Allcapped'     =>$request->post('Allcapped'),
                    'uniacid'       =>$uid['uniacid'],
                    'allClass'      =>$request->post('allClass'),
                    'period_rand'   =>json_encode(['period_rand'=>$request->post('period_rand'),'period_rand2'=>$request->post('period_rand2')]),
                    'period_limit'  =>$request->post('period_limit')
                ]);
            return json(['code'=>10000,'status'=>true,'msg'=>'添加成功']);
        }catch (Exception $e){
            return json(['code'=>10001,'status'=>true,'msg'=>$e->getMessage()]);
        }

    }

    public function editCharge(Request $request)
    {
        $chargeModel = Loader::model("Charge","model");
        if($request->isGet()){
            return view("charge/charge_edit",['list'=>$chargeModel->getSingleChargeInfo($request->get('id'))]);
        }
        if($request->isPost()) {
          $result = $chargeModel->chargeUpdate($request);
          if($result['status']){
              return json(['code'=>10000,'msg'=>$result['msg']]);
          }
            return json(['code'=>10001,'msg'=>$result['msg']]);
        }
    }


    public function deleteCharge(Request $request)
    {
        if(empty($request->get('id'))){
            $this->error('失败');
        }
        Db::table("ims_parking_charge")->where("id",$request->get("id"))->delete();
        $this->success("删除成功",Url("index/charge_list"));
    }

    public function sendChargeEMAIL(Request $request){
       switch ($request->post('type')){
           case 1:
               return $this->getCheckIdData($request->post('data'),$request->post('email'));
               break;
           case 2:
               return $this->getAllData($request->post('email'));
               break;
           default:
               return json(['code'=>10001,'msg'=>'异常']);
       }
    }

    protected function getCheckIdData($data,$email){
        $DecodeData= json_decode($data,true);
        $id =null;
        foreach ($DecodeData as $val){
            $id.=$val.',';
        }
        $chargeInfo  = Db::name("parking_charge")->where('id','in',trim($id,','))->select();
        if(empty($chargeInfo)){return json(['code'=>10000,'msg'=>'异常']);}
        $PHPSheet = $this->getExcelObject();
        $this->setExcelValu($chargeInfo,$PHPSheet);
        $ExcelWrite = PHPExcel_IOFactory::createWriter($this->Excel,'Excel2007');
        $path='./Chargeinformation.xls';
        $ExcelWrite->save($path);
        $this->sendMail($path,$email);
        return json(['code'=>10000,'msg'=>'成功']);
    }

    protected function getAllData($email){
        $chargeInfo  = Db::name("parking_charge")->select();
        if(empty($chargeInfo)){return json(['code'=>10000,'msg'=>'异常']);}
        $PHPSheet = $this->getExcelObject();
        $this->setExcelValu($chargeInfo,$PHPSheet);
        $ExcelWrite = PHPExcel_IOFactory::createWriter($this->Excel,'Excel2007');
        $path='./Chargeinformation.xls';
        $ExcelWrite->save($path);
        $this->sendMail($path,$email);
        return json(['code'=>10000,'msg'=>'成功']);
    }
    protected function setExcelValu($data,$Sheet){
        $n=2;
        $type = [0=>'停车时长满24小时',1=>'停入当天23:59前'];
        foreach ($data as $val){
            $val['period_rand'] =json_decode($val['period_rand'],true);
            $val['payPeriod'] = json_decode($val['payPeriod'],true);
            $Sheet->setCellValue('A'.$n,$val['ChargeClass'])
                ->setCellValue('B'.$n,$val['Allcapped'].'元')
                ->setCellValue('C'.$n,$type[$val['allClass']])
                ->setCellValue('D'.$n,$val['period_rand']['period_rand'].'-'.$val['period_rand']['period_rand2'])
                ->setCellValue('E'.$n,$val['period_limit'].'元');
            foreach ($val['payPeriod'] as $k=>$v){
                $Sheet->setCellValue('F'.($n+$k),$v['period_name'])
                    ->setCellValue('G'.($n+$k),$v['starTime'])
                    ->setCellValue('H'.($n+$k),$v['endTime'])
                    ->setCellValue('I'.($n+$k),$v['free'].'分钟')
                    ->setCellValue('J'.($n+$k),$v['price'].'元-'.$v['minute'].'分钟')
                    ->setCellValue('K'.($n+$k),$v['capped'].'元')
                    ->setCellValue('L'.($n+$k),$v['y_minute_new'].'分钟首-'.$v['addMinus'].'分钟加');
            }
            $n+=2;
        }
        unset($data,$Sheet);
    }
    protected function sendMail($path,$email){
        $name = '系统管理员';
        $subject = '泊位信息';
        $content = '发送成功，请查收';
        $status = send_mail($email,$name,$subject,$content,['0'=>$path]);
        if($status){
            unlink($path);
        }
    }
    protected function getExcelObject(){
        $this->Excel =new PHPExcel();
        $PHPSheet = $this->Excel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('收费方案'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue("A1","方案名称");
        $PHPSheet->setCellValue("B1","全天封顶");
        $PHPSheet->setCellValue("C1","封顶分类");
        $PHPSheet->setCellValue("D1","时段范围");
        $PHPSheet->setCellValue("E1","跨段上限");
        $PHPSheet->setCellValue("F1","时段名称");
        $PHPSheet->setCellValue("G1","开始时间");
        $PHPSheet->setCellValue("H1","结束时间");
        $PHPSheet->setCellValue("I1","免费时间");
        $PHPSheet->setCellValue("J1","时段收费");
        $PHPSheet->setCellValue("K1","时段封顶");
        $PHPSheet->setCellValue("L1","预付详情");
        return $PHPSheet;
    }
}