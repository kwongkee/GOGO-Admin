<?php

namespace app\index\controller;
use app\index\controller;
use MongoDB\Driver\ReadConcern;
use think\Db;
use think\Loader;
use think\Request;
use think\Session;
use think\Validate;
use PHPExcel;
use PHPExcel_IOFactory;

class ParkManage extends CommonController
{
    protected $Excel;
    protected $n=1;
    public function parkIndex(Request $request)
    {
        $config = [
            'query'=>['s'=>'index/park_index'],
            'var_page'=>'page'
        ];
        $status=[
            0=>'<span ecs-run-status="" item="item" status="item.status" class="run-status" style="color:#090;"><i style="background-position:-61px -4px;float:left;width:16px;height:16px;display:inline-block;background-repeat:no-repeat;background-image:url(https://g.alicdn.com/aliyun/ecs2/2.29.5/styles/images/ecs-icons.png)"></i><div style="float:left;margin-top:-1px;">可用</div></span>',
            1=>'<span ecs-run-status="" item="item" status="item.status" class="run-status" style="color:red;"><i style="background-position:-61px -28px;float:left;width:16px;height:16px;display:inline-block;background-repeat:no-repeat;background-image:url(https://g.alicdn.com/aliyun/ecs2/2.29.5/styles/images/ecs-icons.png)"></i><div style="float:left;margin-top:-1px;">已进入</div></span>',
            2=>'<span ecs-run-status="" item="item" status="item.status" class="run-status" style="color:red;"><i style="background-position:-61px -4px;float:left;width:16px;height:16px;display:inline-block;background-repeat:no-repeat;background-image:url(https://g.alicdn.com/aliyun/ecs2/2.29.5/styles/images/ecs-icons.png)"></i><div style="float:left;margin-top:-1px;">占用</div></span>',
            3=>'<span ecs-run-status="" item="item" status="item.status" class="run-status" style="color:#090;"><i style="background-position:-61px -4px;float:left;width:16px;height:16px;display:inline-block;background-repeat:no-repeat;background-image:url(https://g.alicdn.com/aliyun/ecs2/2.29.5/styles/images/ecs-icons.png)"></i><div style="float:left;margin-top:-1px;">已确认</div></span>',
            4=>'<span ecs-run-status="" item="item" status="item.status" class="run-status" style="color:red;"><i style="background-position:-61px -28px;float:left;width:16px;height:16px;display:inline-block;background-repeat:no-repeat;background-image:url(https://g.alicdn.com/aliyun/ecs2/2.29.5/styles/images/ecs-icons.png)"></i><div style="float:left;margin-top:-1px;">占用</div></span>',
        ];
        if($request->isPost()){
            if(!empty($request->post('search'))){
                $Result =array();
                $parResult=null;
                $code =trim($request->post('search'));
                if(!empty($code)){
                    $parResult =Db::table("ims_parking_space")
                        ->alias("a1")
                        ->join("ims_parking_position a2","a1.pid=a2.id")
                        ->join("ims_parking_charge a3","a1.cid=a3.id")
                        ->where("a1.park_code={$code} or a1.numbers={$code}")
                        ->field(['a1.park_code','a1.numbers','a1.status','a2.*','a1.id as sid','a3.ChargeClass','a1.up_time'])
                        ->find();
                }
                $Result['data'][0]=$parResult;
                unset($parResult);
                return view("park/park_index",['page'=>'','list'=>$Result,'status'=>$status]);
            }
        }
            $total=Db::table("ims_parking_space")->count();
            $parResult =Db::table("ims_parking_space")
                ->alias("a1")
                ->join("ims_parking_position a2","a1.pid=a2.id")
                ->join("ims_parking_charge a3","a1.cid=a3.id")
                ->field(['a1.park_code','a1.numbers','a1.status','a2.*','a1.id as sid','a3.ChargeClass','a1.up_time'])
                ->order("a1.id","desc")
                ->paginate(10,$total,$config);
            return view("park/park_index",['page'=>$parResult->render(),'list'=>$parResult->toArray(),'status'=>$status]);
    }

    public function parkAdd()
    {
        $addr =Db::table("ims_parking_deploy_district")->where("pid",0)->field(['id','name'])->select();
        $charge = Db::table("ims_parking_charge")->field(['id','ChargeClass'])->select();
//        dump($addr);
        $unid=Session::get('UserResutlt')['company_id'];
        return view("park/park_add",['addr'=>$addr,'charge'=>$charge,'unid'=>$unid]);
    }

    public function parkSave(Request $request)
    {
        $data=array();
        $validate =new Validate([
            'ChargeClass'=>'require',
            'provin'=>'require',
            'citys'=>'require',
            'area'=>'require',
            'town'=>'require',
            'committee'=>'require',
            'road'=>'require',
            'road_num'=>'require|min:2|max:2',
            'devCode'=>'require|min:6|max:6',
            'ParkCode','require|min:3|max:3',
            'customCode'=>'require|min:3|max:3'
        ],[
            'ChargeClass.require' =>'请选择计费方案',
            'provin.require'      =>'地址参数缺少',
            'citys.require'       =>'地址参数缺少',
            'area.require'        =>'地址参数缺少',
            'town.require'                =>'地址参数缺少',
            'committee.require'   =>'地址参数缺少',
            'road.require' => '缺少必须参数',
            'road_num.require' => '缺少必须参数',
            'road_num.max'        =>'道路编号不能大于2位',
            'road_num.min'        =>'道路编号不能少于2位',
            'devCode.max'         => '设备编码不能超过6位数',
            'devCode.min'         => '设备编码不能少于6位',
            'ParkCode.max'        =>'不能超3位',
            'ParkCode.min'        =>'不能低于3位',
            'customCode.require'  => '泊位自定义不能空',
            'customCode.min'  => '泊位自定义不能少于3位',
            'customCode.max'      => '泊位自定义不能大于3位',
        ]);
        $data=[
            'ChargeClass'   =>$request->post('data.ChargeClass'),
            'provin'        =>$request->post('data.provin'),
            'citys'         =>$request->post('data.citys'),
            'area'          =>$request->post('data.area'),
            'town'          =>$request->post('data.town'),
            'committee'     =>$request->post('data.committee'),
            'road'          =>$request->post('data.road'),
            'road_num'      =>$request->post('data.road_num'),
            'devCode'       =>$request->post('data.devCode'),
            'ParkCode'      =>$request->post('data.ParkCode'),
            'customCode'    =>$request->post('data.customCode'),
        ];
        if(!$validate->check($data)){
            return json(['status'=>false,'code'=>10001,'msg'=>$validate->getError()]);
        }
        if (!preg_match('/^[\x{4e00}-\x{9fa5}]+/u', $data['road'])) {
            return json(['status'=>false,'code'=>10001,'msg'=>'道路名称只能中文']);
        }
        if(Session::get("UserResutlt")['write']!=1){
            return json(['status'=>false,'code'=>10001,'msg'=>'没有权限']);
        }
        $data['numArray']=$request->post('data.num');
        $ParkModel = Loader::model('ParkManage','model');
        $bool=$ParkModel->dataSave($data,Session::get("UserResutlt")['uniacid']);
       if($bool['status']){
           return json(['status'=>true,'code'=>10000,'msg'=>'添加成功']);
       }
        return json(['status'=>false,'code'=>10001,'msg'=>$bool['msg']]);
    }

    public function postion(Request $request)
    {
        $addr = Db::table('ims_parking_deploy_district')->where(['pid'=>explode(":",$request->get('pid'))['0']])->field(['id','name'])->select();
        return json_encode($addr);
    }

    public function parkDel(Request $request)
    {
        Db::table("ims_parking_space")->where("id",$request->get("id"))->delete();
        $this->success("删除成功",Url('index/park_index'));
    }

    public function sendParkNumberMail(Request $request)
    {
        if($request->isPost()){
           switch ($request->post("type")){
               case 1:
                   return $this->sendCheckData(json_decode($request->post('data'),true),$request->post('email'));//发送选择的数据
               break;
               case 2:
                   return $this->sendAllData($request->post('email'));//发送全部数据
               break;
               default:
                   return json(['code'=>10001,'msg'=>'异常']);
                   break;
           }
        }
    }

    protected function sendAllData($email){
        $space_data = Db::name("parking_space")
                        ->alias("a1")
                        ->join("parking_position a2","a2.id=a1.pid")
                        ->field(["a1.park_code","a1.numbers","a2.*"])
                        ->order('a1.id','desc')
                        ->select();
        if(empty($space_data)){ return json(['code'=>10001,'msg'=>'异常']);}
        $PHPSheet = $this->getExcelObject();
        foreach ($this->xrange($space_data) as $value){
            $this->n+=1;
            $PHPSheet->setCellValue('A'.$this->n,$value['park_code'])
                ->setCellValue('B'.$this->n,$value['numbers'])
                ->setCellValue('C'.$this->n,$value['Road'])
                ->setCellValue('D'.$this->n,$value['City'].$value['Area'].$value['Town'].$value['Committee']);
        }
        $ExcelWrite = PHPExcel_IOFactory::createWriter($this->Excel,'Excel2007');
        $path='./parkingCodeInfo.xls';
        $ExcelWrite->save($path);
        $this->sendMail($path,$email);
        return json(['code'=>10000,'msg'=>'成功']);
    }
    protected function xrange($data){
        foreach ($data as $val){
            yield $val;
        }
    }
    protected function sendCheckData($data,$email){
        if(empty($data)){ return json(['code'=>10001,'msg'=>'异常']);}
        $PHPSheet = $this->getExcelObject();
        $vals=null;
        foreach ($data as $val){
            $this->n+=1;
            $vals =json_decode($val,true);
            $PHPSheet->setCellValue("A".$this->n,$vals['park_code'])
                ->setCellValue('B'.$this->n,$vals['dev_code'])
                ->setCellValue('C'.$this->n,$vals['road'])
                ->setCellValue('D'.$this->n,$vals['addr']);
        }
        $ExcelWrite = PHPExcel_IOFactory::createWriter($this->Excel,'Excel2007');
        $path='./parkingCodeInfo.xls';
        $ExcelWrite->save($path);
        $this->sendMail($path,$email);
        return json(['code'=>10000,'msg'=>'成功']);
    }

    protected function getExcelObject(){
        $this->Excel =new PHPExcel();
        $PHPSheet = $this->Excel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('泊位编号'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue("A1","泊位编号");
        $PHPSheet->setCellValue("B1","设备编号");
        $PHPSheet->setCellValue("C1","道路名称");
        $PHPSheet->setCellValue("D1","地址");
        return $PHPSheet;
    }

    protected function sendMail($path,$email){
        $name = '系统管理员';
        $subject = '泊位信息';
        $content = '发送成功，请查收';
        $status = send_mailAli($email,$name,$subject,$content,['0'=>$path]);
        if($status){
            unlink($path);
        }
    }
}