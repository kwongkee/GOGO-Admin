<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Db;
use think\Request;
use Excel5;
use PHPExcel_IOFactory;

class Account extends Auth
{
    public function tax(){
        $list = Db::name('customs_account_tax_code')->order('id','desc')->select();
        return $this->fetch('account/tax', ['title' => '上传税收分类编码','list'=>$list]);
    }

    //保存税收编号
    public function tax_upload(Request $request)
    {
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
        $PHPRead = $objRead->load($fileName);
        $sheet = $PHPRead->getSheet(0);
        $allRow = $sheet->getHighestRow();
//        for ($currentRow = 1; $currentRow <= $allRow; $currentRow++) {
//            array_push($data, [
//                'code_value' => $PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue(),
//                'code_name' => $PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue(),
//            ]);
//        }
//        Db::name('language')->insertAll($data);
        for ($currentRow = 3; $currentRow <= $allRow; $currentRow++) {
            array_push($data, [
                'num1' => $PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue(),
                'num2' => $PHPRead->getActiveSheet()->getCell("C".$currentRow)->getValue(),
                'num3' => $PHPRead->getActiveSheet()->getCell("D".$currentRow)->getValue(),
                'num4' => $PHPRead->getActiveSheet()->getCell("E".$currentRow)->getValue(),
                'num5' => $PHPRead->getActiveSheet()->getCell("F".$currentRow)->getValue(),
                'num6' => $PHPRead->getActiveSheet()->getCell("G".$currentRow)->getValue(),
                'num7' => $PHPRead->getActiveSheet()->getCell("H".$currentRow)->getValue(),
                'num8' => $PHPRead->getActiveSheet()->getCell("I".$currentRow)->getValue(),
                'num9' => $PHPRead->getActiveSheet()->getCell("J".$currentRow)->getValue(),
                'num10' => $PHPRead->getActiveSheet()->getCell("K".$currentRow)->getValue(),
                'merge_num' => $PHPRead->getActiveSheet()->getCell("L".$currentRow)->getValue(),
                'name' => $PHPRead->getActiveSheet()->getCell("M".$currentRow)->getValue(),
                'short_name' => $PHPRead->getActiveSheet()->getCell("N".$currentRow)->getValue(),
                'description' => $PHPRead->getActiveSheet()->getCell("O".$currentRow)->getValue(),
            ]);
        }
        Db::name('customs_account_tax_code')->insertAll($data);
        @unlink($fileName);
        return json(['code'=>0,'message'=>'已保存']);
    }

    public function subject(){
        $list = Db::name('customs_account_subject_code')->order('id','desc')->select();
        return $this->fetch('account/subject', ['title' => '上传会计科目编码','list'=>$list]);
    }

    //保存会计科目编号
    public function subject_upload(Request $request)
    {
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
        $PHPRead = $objRead->load($fileName);
        $sheet = $PHPRead->getSheet(0);
        $allRow = $sheet->getHighestRow();
        for ($currentRow = 4; $currentRow <= $allRow; $currentRow++) {
            array_push($data, [
                'subject' => $PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue(),
                'account' => $PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue(),
                'credit' => $PHPRead->getActiveSheet()->getCell("C".$currentRow)->getValue(),
                'cash' => $PHPRead->getActiveSheet()->getCell("D".$currentRow)->getValue(),
                'foreign_currency' => $PHPRead->getActiveSheet()->getCell("E".$currentRow)->getValue(),
            ]);
        }
        Db::name('customs_account_subject_code')->insertAll($data);
        @unlink($fileName);
        return json(['code'=>0,'message'=>'已保存']);
    }

    //商城会员
    public function member(Request $request){
        if ( request()->isPost() || request()->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $list = Db::name('mc_mapping_fans')->where('uniacid','3')->where('follow',1)->order('fanid','desc')->limit($limit)->select();
            foreach ($list as $k => $v) {
                $list[$k]['is_accounting'] = $v['is_accounting'] == 1 ? '是' : '否';
                $list[$k]['followtime'] = date('Y-m-d H:i:s',$v['followtime']);
                if($v['is_accounting']==1){
                    $list[$k]['manage'] = '<button type="button" style="background:#ff5555;border-color: #ff5555;" onclick="editInfo('."'取消会计身份','".Url('admin/account/set_member_to_account')."'".','."'".$v['fanid']."'".')" class="btn btn-primary btn-xs">取消会计身份</button>';
                }else{
                    $list[$k]['manage'] = '<button type="button" onclick="editInfo('."'配置为会计身份','".Url('admin/account/set_member_to_account')."'".','."'".$v['fanid']."'".')" class="btn btn-primary btn-xs">配置为会计身份</button>';
                }

            }
            $total = Db::name('mc_mapping_fans')->where('uniacid','3')->where('follow',1)->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }else{
            return $this->fetch('account/member', ['title' => '会员配置会计']);
        }
    }

    //会员设置为会计
    public function set_member_to_account(Request $request){
        if ( request()->isPost() || request()->isAjax()) {
            $id = input('fanid');
            if(!empty($id)){
                $data = Db::name('mc_mapping_fans')->where('fanid',intval($id))->find();
                if($data['is_accounting']==1){
                    $res = Db::name('mc_mapping_fans')->where('fanid',intval($id))->update(['is_accounting'=>0]);
                    if($res){
                        return json(["status" => 1, "message" => "取消身份成功"]);
                    }
                }else{
                    $res = Db::name('mc_mapping_fans')->where('fanid',intval($id))->update(['is_accounting'=>1]);
                    if($res){
                        return json(["status" => 1, "message" => "配置成功"]);
                    }
                }
            }
        }
    }
}