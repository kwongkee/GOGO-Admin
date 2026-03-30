<?php

namespace app\admin\controller;

use think\Db;
use think\Request;
use Excel5;
use PHPExcel_IOFactory;

/**
 * 运单管理
 */
class Waybill extends Auth
{
    //运单号码分配
    public function numberassign(Request $request){
        if($request->isPost() || $request->isAjax()){

        }else{
            $data = Db::name('waybill_number_assign')
                    ->alias('a')
                    ->join('enterprise_members b','a.member_id = b.id','left')
                    ->join('enterprise_basicinfo c','b.id = c.member_id','left')
                    ->where('a.type','2')
                    ->field(['a.id,a.createtime,c.name as merchat_name,b.nickname'])
                    ->select();
            $this->assign('data',$data);
            return view();
        }
    }

    //商家运单号码列表
    public function numberassign_list(Request $request){
        $inp = input();
        if($request->isPost() || $request->isAjax()){

        }else {
            $status = ['未使用','已使用'];

            $merch = Db::name('waybill_number_assign')
            ->alias('a')
            ->join('enterprise_basicinfo b','a.merchant_id = b.id','left')
            ->where('a.id',$inp['id'])
            ->where('a.type',2)
            ->field(['b.name,a.merchant_id'])
            ->find();

            $count =Db::name('waybill_numberassign_list')->where('merchant_id',$merch['merchant_id'])->count();
            $user = Db::name('waybill_numberassign_list')->where('merchant_id',$merch['merchant_id'])->order('id', 'desc')->paginate(10, $count, ['query' => ['s' => 'admin/waybill/numberassign_list&id='.$inp['id']], 'var_page' => 'page', 'type' => 'Layui', 'newstyle' => true]);

            $page = $user->render();
            $data = $user->toArray()['data'];

            $this->assign('merch',$merch);
            $this->assign('data',$data);
            $this->assign('page',$page);
            $this->assign('status',$status);
            return view();
        }
    }

    //添加商家运单号码
    public function numberassign_add(Request $request){
        if($request->isPost() || $request->isAjax()){
            $inp = input();
            $data = [];
            $time = time();

            $inp['getMerch'] = explode('-',$inp['getMerch']);
            $data['merchant_id'] = $inp['getMerch'][0];
            $data['member_id'] = $inp['getMerch'][1];
            $data['createtime'] = $time;

            $isHaveLog = Db::name('waybill_number_assign')->where('merchant_id',$data['merchant_id'])->find();

            if(empty($isHaveLog['id'])){
                $res = Db::name('waybill_number_assign')->insert($data);

            }
            $data2 = [];
            $inp2['getLogi'] = explode('-',$inp['getLogi']);
            //开始生成运单号码前缀，规则：198+商家编号后两位（regNo）+流水号（0001-9999）
            $data2['merchant_id'] = $inp['getMerch'][0];
            $data2['status'] = 0;
            $data2['createtime'] = $time;
            $data2['logistics_id'] = $inp2['getLogi'][0];
            $logiStr = substr($inp2['getLogi'][1],-2);
            $two = Db::name('enterprise_basicinfo')->where('id',$data2['merchant_id'])->field('regNo')->find();
            $str = substr($two['regNo'],-2);
//            0001-0999,1000-1999,2000-2999
            for($i=$inp['expNums'];$i<=$inp['expNumd'];$i++){
                $data2['number'] = '198'.$logiStr.$str.str_pad($i, 4, '0', STR_PAD_LEFT);
                //判断表中有无已有同样运单号
                $isHaveId = Db::name('waybill_numberassign_list')->where(['merchant_id'=>$data2['merchant_id'],'number'=>$data2['number']])->find();
                if(empty($isHaveId['id'])){
                    Db::name('waybill_numberassign_list')->insert($data2);
                }else{
                    continue;
                }
            }

            return json_encode(['code'=>1,'msg'=>'分配成功']);
        }else{
            //获取商家
            $merch = Db::name('enterprise_members')
                ->alias('a')
                ->join('enterprise_basicinfo b','a.id = b.member_id','left')
                ->where(1)
                ->field(['a.nickname,a.id as mid,b.id,b.name,b.regNo'])
                ->select();

            //物流企业
            $logistics = Db::name('customs_waybill_declare_enterprise')->select();

            $this->assign('merch',$merch);
            $this->assign('logistics',$logistics);
            return view();
        }
    }

    /**进口-运单号码分配
     * @param Request $request
     * @return \think\response\View
     */
    public function numberassign2(Request $request){
        if($request->isPost() || $request->isAjax()){

        }else{
            $data2 = Db::name('hjxssl_assign_device_express')
                ->alias('a')
                ->join('decl_user b','a.uid=b.id','left')
                ->where(1)
                ->order('a.id','desc')
                ->field(['a.express_id,b.user_name,a.uid'])
                ->select();
            
            $data = [];
            $i=0;
            foreach($data2 as $k=>$v){
                $express = explode(',',$v['express_id']);        
                foreach($express as $kk=>$vv){
                    $data[$i]['name'] = Db::name('hjxssl_express')
                                        ->alias('a')
                                        ->join('customs_express_company_code b','a.code = b.code')->where('a.id',$vv)->field('b.name')->find()['name'];
                    $data[$i]['user_name'] = $v['user_name'];
                    $data[$i]['uid'] = $v['uid'];
                    $i++;
                }
            }
          
            $this->assign('data',$data);
            return view();
        }
    }

    public function numberassign_list2(Request $request){
        $inp = input();
        if($request->isPost() || $request->isAjax()){

        }else {
            $status = ['未使用','已使用'];
            
            $merch = Db::name('enterprise_members')
                ->alias('a')
                ->join('decl_user c','a.mobile = c.user_tel','left')
                ->where(['c.id'=>$inp['uid']])
                ->field(['c.user_name'])
                ->find();
            
            $count = Db::name('hjxssl_number_assign')
                ->alias('a')
                ->join('enterprise_members b','a.member_id = b.id','left')
                ->join('decl_user c','b.mobile = c.user_tel','left')
                ->join('customs_express_company_code d','a.express_id = d.id','left')
                ->where(['c.id'=>$inp['uid'],'d.name'=>$inp['name']])
                ->count();
                
            $user = Db::name('hjxssl_number_assign')
                ->alias('a')
                ->join('enterprise_members b','a.member_id = b.id','left')
                ->join('decl_user c','b.mobile = c.user_tel','left')
                ->join('customs_express_company_code d','a.express_id = d.id','left')
                ->where(['c.id'=>$inp['uid'],'d.name'=>$inp['name']])
                ->order('a.id', 'desc')->paginate(15, $count, ['query' => ['s' => 'admin/waybill/numberassign_list2&uid='.$inp['uid'].'&name='.$inp['name']], 'var_page' => 'page', 'type' => 'Layui', 'newstyle' => true]);

            $page = $user->render();
            $data = $user->toArray()['data'];

            $this->assign('merch',$merch);
            $this->assign('data',$data);
            $this->assign('page',$page);
            $this->assign('status',$status);
            return view();
        }
    }

    //进口-查看该快递公司起始和截止运单号
    public function getThisExpWayNum(Request $request){
        $inp = input();
        $data = Db::name('hjxssl_express_waybill')->where(['express_id'=>$inp['express_id'],'status'=>0])->field('waybill_no')->select();
        if(empty($data)){
            return json(['code'=>-1,'msg'=>'当前快递公司没有运单号！']);
        }
        $start = 0;
        $end = 0;
        foreach($data as $k=>$v){
            if($k==0){
//                $start = substr($v['waybill_no'],9,4);
                $start = $v['waybill_no'];
            }
//            $end = substr($v['waybill_no'],9,4);
            $end = $v['waybill_no'];
        }

        return json(['code'=>1,'str'=>$start.'-'.$end,'num'=>count($data)]);
    }

    //进口-分配商家运单号
    public function numberassign_add2(Request $request){
        if($request->isPost() || $request->isAjax()){
            $inp = input();
            $data = [];
            $time = time();

            $inp['getMerch'] = explode('-',trim($inp['getMerch']));

            $data['member_id'] = $inp['getMerch'][1];
            $data['express_id'] = trim($inp['getLogi']);
            $data['createtime'] = $time;

            //查询快递公司运单号数据表
            $waybill_no = Db::name('hjxssl_express_waybill')->where(['express_id'=>$data['express_id'],'status'=>0])->limit(trim($inp['num']))->field('waybill_no')->select();

            foreach($waybill_no as $k=>$v){
                //根据分配数量进行按顺序分配
                Db::name('hjxssl_number_assign')->insert([
                    'member_id'=>$data['member_id'],
                    'express_id'=>$data['express_id'],
                    'waybill_no'=>$v['waybill_no'],
                    'createtime'=>time()
                ]);
                Db::name('hjxssl_express_waybill')->where(['express_id'=>$data['express_id'],'status'=>0,'waybill_no'=>$v['waybill_no']])->update(['status'=>1]);
            }

            return json_encode(['code'=>1,'msg'=>'分配运单号成功']);
        }else{
            //获取商家
            $merch = Db::name('enterprise_members')
                ->alias('a')
                ->join('enterprise_basicinfo b','a.id = b.member_id','left')
                ->where(1)
                ->field(['a.nickname,a.id as mid,b.id,b.name,b.regNo'])
                ->select();

            //快递公司
            $logistics = Db::name('customs_express_company_code')->select();

            $this->assign('merch',$merch);
            $this->assign('logistics',$logistics);
            return view();
        }
    }

    //进口-导入快递公司运单号码
    public function numberassign_insert(Request $request){
        if($request->isPost() || $request->isAjax()){
            $inp = input();

            $fileName = $inp['filename'];
            $data = [];
            $inputFileType = PHPExcel_IOFactory::identify($fileName);
            $objRead = PHPExcel_IOFactory::createReader($inputFileType);
            $objRead->setReadDataOnly(true);
            $PHPRead = $objRead->load($fileName);
            $sheet = $PHPRead->getSheet(0);
            $allRow = $sheet->getHighestRow();
            $err = '';
            for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
//                array_push($data, $PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue());
                $waybill_no = $PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue();
//                $err = Db::name('hjxssl_express_waybill')->where(['waybill_no'=>$waybill_no,'express_id'=>$inp['logistics']])->field('waybill_no')->find();
                $exist = Db::name('hjxssl_express_waybill')->where(['waybill_no'=>$waybill_no,'express_id'=>$inp['logistics']])->field('waybill_no')->find();
                if(empty($exist)){
                    Db::name('hjxssl_express_waybill')->insert([
                        'express_id'=>$inp['logistics'],
                        'waybill_no'=>$waybill_no,
                    ]);
                }else{
                    $err .= $exist['waybill_no'].',';
                }

            }
            if($err){
                return json(['code'=>-1,'message'=>'导入失败!运单号已存在：'.$err]);
            }
            return json(['code'=>1,'message'=>'导入成功!']);
        }else{
            //快递企业
            $logistics = Db::name('customs_express_company_code')->select();

            $this->assign('logistics',$logistics);
            return view();
        }
    }

    //进口-上传运单EXL
    public function number_upload(Request $request){
        $file = $request->file('file');
        if (!$file) {
            return json(['code' => -1, 'message' => '请上传文件']);
        }
        $path = ROOT_PATH.'public'.DS.'uploads';
        $saveResult = $file->validate(['ext' => 'xls,csv,xlsx'])->move($path);
        if (!$saveResult) {
            return json(['code' => -1, 'message' => $file->getError()]);
        }
        $fileName = $path.'/'.$saveResult->getSaveName();
        $data = [];
        $inputFileType = PHPExcel_IOFactory::identify($fileName);
        $objRead = PHPExcel_IOFactory::createReader($inputFileType);
        $objRead->setReadDataOnly(true);
//        $PHPRead = $objRead->load($fileName);
//        $sheet = $PHPRead->getSheet(0);
//        $allRow = $sheet->getHighestRow();
//        for ($currentRow = 3; $currentRow <= $allRow; $currentRow++) {
//            array_push($data, [
//                'name' => $PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue(),
//                'code' => $PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue()
//            ]);
//        }
//        Db::name('customs_express_company_code')->insertAll($data);
//        @unlink($fileName);
        return json(['code'=>0,'message'=>'上传成功!','filename'=>$fileName]);
    }
}