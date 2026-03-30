<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Request;
use think\Db;
use Excel5;
use PHPExcel_IOFactory;

class ExportRisk extends Auth
{
    //数源配置
    public function config(Request $request){
        if ( request()->isPost() || request()->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $total = Db::name('export_risk_data_source')->count();
            $data = Db::name('export_risk_data_source')->limit($page, $limit)->select();
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{

            return view('',compact('data'));
        }
    }

    public function config_save(){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()){
            if(empty($dat['id'])){
                //新增
                $res = Db::name('export_risk_data_source')->insert(['name'=>trim($dat['name']),'url'=>trim($dat['url'])]);
                if($res){
                    return json(['code' => 1, 'msg' => '新增成功！']);
                }
            }else{
                //修改
                $res = Db::name('export_risk_data_source')->where('id',$dat['id'])->update(['name'=>trim($dat['name']),'url'=>trim($dat['url'])]);
                if($res){
                    return json(['code' => 1, 'msg' => '修改成功！']);
                }
            }
        }else{
            if(empty($dat)){
                $data['name'] = '';
                $data['url'] = '';
                $data['id'] = '';
            }else{
                $data = Db::name('export_risk_data_source')->where('id',$dat['id'])->find();
            }
            return view('',compact('data'));
        }
    }

    //第次调整幅度
    public function adjust(){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()){
            $res = Db::name('export_risk_gross_adjust')->where('id',$dat['id'])->update([
                'percent_up'=>trim($dat['percent_up']),
                'percent_down'=>trim($dat['percent_down']),
                'value_up'=>trim($dat['value_up']),
                'value_down'=>trim($dat['value_down']),
            ]);

            if($res){
                return json(['code' => 1, 'msg' => '修改成功！']);
            }
        }else{
            $data = Db::name('export_risk_gross_adjust')->where('id',1)->find();
            return view('',compact('data'));
        }
    }

    //商品单价限值
    public function price_limit(){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()){
            $res = Db::name('export_risk_gross_adjust')->where('id',$dat['id'])->update([
                'goodsValue_up'=>trim($dat['goodsValue_up']),
                'goodsValue_down'=>trim($dat['goodsValue_down']),
            ]);

            if($res){
                return json(['code' => 1, 'msg' => '修改成功！']);
            }
        }else{
            $data = Db::name('export_risk_gross_adjust')->where('id',1)->find();
            return view('',compact('data'));
        }
    }

    //店铺销量配置
    public function store_sales(){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()){
            $res = Db::name('export_risk_gross_adjust')->where('id',$dat['id'])->update([
                'daily_sales'=>trim($dat['daily_sales']),
                'month_sales'=>trim($dat['month_sales']),
                'sold'=>trim($dat['sold']),
            ]);

            if($res){
                return json(['code' => 1, 'msg' => '修改成功！']);
            }
        }else{
            $data = Db::name('export_risk_gross_adjust')->where('id',1)->find();
            return view('',compact('data'));
        }
    }

    //HS编码归类
    public function import_info(Request $request){
        $dat = input();
        if(request()->isPost() || request()->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset'));
            $keyword = $request->get('search');
            if(!empty($keyword)){
                $keyword = explode(' ',rtrim($keyword,' '));
                $keywords_fenci = '';
                foreach($keyword as $k=>$v){
                    $keywords_fenci .= '(`name` like "%' . $v. '%" or hscode like "%'.$v.'%") or ';
                }
                $keywords_fenci = substr($keywords_fenci,0,-3);
                $total = Db::query('select count(id) as t from ims_customs_hscode_tariffschedule_ssl where '.$keywords_fenci);
                $total = $total[0]['t'];
                $data = Db::query('select * from ims_customs_hscode_tariffschedule_ssl where '.$keywords_fenci.' limit '.$page.','.$limit);
//                $total = Db::name('customs_hscode_tariffschedule_ssl')->where('name','like','%'.$keyword.'%')->whereOr('hscode','like','%'.$keyword.'%')->count();
//                $data = Db::name('customs_hscode_tariffschedule_ssl')->where('name','like','%'.$keyword.'%')->whereOr('hscode','like','%'.$keyword.'%')->limit($page, $limit)->select();
            }else{
                $total = Db::name('customs_hscode_tariffschedule_ssl')->count();
            
                $data = Db::name('customs_hscode_tariffschedule_ssl')->limit($page, $limit)->select();
            }
            
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('',compact('data'));
        }
    }

    //HS编码信息详情
    public function hscode_info(){
        $dat = input();
        if(request()->isPost() || request()->isAjax()){

        }else{
            $id=intval($dat['id']);
            $list = Db::name('customs_hscode_tariffschedule_ssl')->where('id',$id)->find();
            $title = $list['name'];
            //基本信息
            $list['basic_info'] = json_decode($list['basic_info'],true);
            $list['tax_info'] = json_decode($list['tax_info'],true);
            $list['declaration_elements'] = json_decode($list['declaration_elements'],true);
            $list['regulatory_conditions'] = json_decode($list['regulatory_conditions'],true);
            $list['inspect_quarantine'] = json_decode($list['inspect_quarantine'],true);
            $list['treaty_tax_rate'] = json_decode($list['treaty_tax_rate'],true);
            $list['rcep_tax_rate'] = json_decode($list['rcep_tax_rate'],true);
            $list['ciq_code_info'] = json_decode($list['ciq_code_info'],true);
            $list['chapter_info'] = json_decode($list['chapter_info'],true);
//            print_r($list);die;
            return view('',compact('list','title'));
        }
    }

    //HS编码信息详情
    public function import_excel(Request $request){
        $dat = input();
        if(request()->isPost() || request()->isAjax()){
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
            for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                //基本信息
                $basic_info = explode('@|@',rtrim($PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue(),'@|@'));
                $two = substr($basic_info[1],0,2);
                $four = substr($basic_info[1],0,4);
                $five = substr($basic_info[1],0,5);
                $six = substr($basic_info[1],0,6);
                $seven = substr($basic_info[1],0,7);
                $eight = substr($basic_info[1],0,8);
                $basic_info_arr = json_encode([
                    [$basic_info[0],$basic_info[1]],
                    [$basic_info[2],$basic_info[3]],
                    [$basic_info[4],$basic_info[5]],
                    [$basic_info[6],$basic_info[7]],
                    [$basic_info[8],$basic_info[9]],
                    [$basic_info[10],$basic_info[11]],
                    [$basic_info[12],$basic_info[13]]
                ],true);

                //税率信息
                $tax_info = explode('@|@',rtrim($PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue(),'@|@'));
                $tax_info_arr = [];
                $tax_info_twoarr = [];
                foreach($tax_info as $k2=>$v2){
                    array_push($tax_info_twoarr,rtrim($v2,'['));
                    if(count($tax_info_twoarr)==2){
                        array_push($tax_info_arr,$tax_info_twoarr);
                        $tax_info_twoarr=[];
                    }
                }
                $tax_info_arr = json_encode($tax_info_arr,true);
                //申报要素
                $declaration_elements = explode('@|@',rtrim($PHPRead->getActiveSheet()->getCell("C".$currentRow)->getValue(),'@|@'));
                $declaration_elements_arr = [];
                $declaration_elements_twoarr = [];
                foreach($declaration_elements as $k2=>$v2){
                    array_push($declaration_elements_twoarr,rtrim($v2,'['));
                    if(count($declaration_elements_twoarr)==2){
                        array_push($declaration_elements_arr,$declaration_elements_twoarr);
                        $declaration_elements_twoarr=[];
                    }
                }
                $declaration_elements_arr = json_encode($declaration_elements_arr,true);

                //监管条件
                $regulatory_conditions = explode('@|@',rtrim($PHPRead->getActiveSheet()->getCell("D".$currentRow)->getValue(),'@|@'));
                $regulatory_conditions_arr = [];
                $regulatory_conditions_twoarr = [];
                foreach($regulatory_conditions as $k2=>$v2){
                    array_push($regulatory_conditions_twoarr,rtrim($v2,'['));
                    if(count($regulatory_conditions_twoarr)==2){
                        array_push($regulatory_conditions_arr,$regulatory_conditions_twoarr);
                        $regulatory_conditions_twoarr=[];
                    }
                }
                $regulatory_conditions_arr = json_encode($regulatory_conditions_arr,true);

                //检验检疫类别
                $inspect_quarantine = explode('@|@',rtrim($PHPRead->getActiveSheet()->getCell("E".$currentRow)->getValue(),'@|@'));
                $inspect_quarantine_arr = [];
                $inspect_quarantine_twoarr = [];
                foreach($inspect_quarantine as $k2=>$v2){
                    array_push($inspect_quarantine_twoarr,rtrim($v2,'['));
                    if(count($inspect_quarantine_twoarr)==2){
                        array_push($inspect_quarantine_arr,$inspect_quarantine_twoarr);
                        $inspect_quarantine_twoarr=[];
                    }
                }
                $inspect_quarantine_arr = json_encode($inspect_quarantine_arr,true);

                //协定税率
                $treaty_tax_rate = explode('@|@',$PHPRead->getActiveSheet()->getCell("F".$currentRow)->getValue());
                array_splice($treaty_tax_rate, -1,1);//移除最后一个空数组
                $num = count($treaty_tax_rate)/2;
                $firstArray=[];
                $secondArray=[];
                $treaty_tax_rate_arr=[];
                for($i=0;$i<$num;$i++){
                    array_push($firstArray,$treaty_tax_rate[$i]);
                }
                for($i=$num;$i<($num*2);$i++){
                    array_push($secondArray,$treaty_tax_rate[$i]);
                }
                for($i=0;$i<count($firstArray);$i++){
                    array_push($treaty_tax_rate_arr,[$firstArray[$i],$secondArray[$i]]);
                }
                $treaty_tax_rate_arr = json_encode($treaty_tax_rate_arr,true);

                //RCEP税率
                $rcep_tax_rate = explode('@|@',$PHPRead->getActiveSheet()->getCell("G".$currentRow)->getValue());
                array_splice($rcep_tax_rate, -1,1);//移除最后一个空数组
                $num = count($rcep_tax_rate)/2;
                $firstArray=[];
                $secondArray=[];
                $rcep_tax_rate_arr=[];
                for($i=0;$i<$num;$i++){
                    array_push($firstArray,$rcep_tax_rate[$i]);
                }
                for($i=$num;$i<($num*2);$i++){
                    array_push($secondArray,$rcep_tax_rate[$i]);
                }
                for($i=0;$i<count($firstArray);$i++){
                    array_push($rcep_tax_rate_arr,[$firstArray[$i],$secondArray[$i]]);
                }
                $rcep_tax_rate_arr = json_encode($rcep_tax_rate_arr,true);

                //CIQ代码表(13位海关编码)
                $ciq_code_info = explode('@|@',rtrim($PHPRead->getActiveSheet()->getCell("H".$currentRow)->getValue(),'@|@'));
                array_splice($ciq_code_info,0,2);
                $ciq_code_info_arr = [];
                $ciq_code_info_twoarr = [];
                foreach($ciq_code_info as $k2=>$v2){
                    array_push($ciq_code_info_twoarr,rtrim($v2,'['));
                    if(count($ciq_code_info_twoarr)==2){
                        array_push($ciq_code_info_arr,$ciq_code_info_twoarr);
                        $ciq_code_info_twoarr=[];
                    }
                }
                $ciq_code_info_arr = json_encode($ciq_code_info_arr,true);

                //所属章节信息
                $chapter_info = explode('@|@',rtrim($PHPRead->getActiveSheet()->getCell("I".$currentRow)->getValue(),'@|@'));
                $chapter_info_arr = [];
                $chapter_info_twoarr = [];
                foreach($chapter_info as $k2=>$v2){
                    array_push($chapter_info_twoarr,rtrim($v2,'['));
                    if(count($chapter_info_twoarr)==2){
                        array_push($chapter_info_arr,$chapter_info_twoarr);
                        $chapter_info_twoarr=[];
                    }
                }
                $chapter_info_arr = json_encode($chapter_info_arr,true);

                array_push($data, [
                    'two' => $two,
                    'four' => $four,
                    'five' => $five,
                    'six' => $six,
                    'seven' => $seven,
                    'eight' => $eight,
                    'hscode' => $basic_info[1],
                    'name' => $basic_info[3],
                    'basic_info' => $basic_info_arr,
                    'tax_info' => $tax_info_arr,
                    'declaration_elements' => $declaration_elements_arr,
                    'regulatory_conditions' => $regulatory_conditions_arr,
                    'inspect_quarantine' => $inspect_quarantine_arr,
                    'treaty_tax_rate' => $treaty_tax_rate_arr,
                    'rcep_tax_rate' => $rcep_tax_rate_arr,
                    'ciq_code_info' => $ciq_code_info_arr,
                    'chapter_info' => $chapter_info_arr,
                ]);
            }
            Db::name('customs_hscode_tariffschedule_ssl')->insertAll($data);
            @unlink($fileName);
            return json(['code'=>0,'message'=>'已保存']);
        }else{
            return view();
        }
    }
}