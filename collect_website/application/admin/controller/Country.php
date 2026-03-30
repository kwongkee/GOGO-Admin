<?php
namespace app\admin\controller;

//use think\Controller;
use app\admin\controller;
use think\Request;
use think\Db;
use Excel5;
use PHPExcel_IOFactory;
use PHPExcel;

class Country extends Auth
{
    //代码配置start
    public function code_list(Request $request)
    {
        $dat = input();
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if ($request->isAjax()) {
            $count = Db::name('centralize_diycountry_category')->count();
            $rows = DB::name('centralize_diycountry_category')
                ->limit($limit)
                ->order('id desc')
                ->select();

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('');
        }
    }

    public function save_code(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if ($request->isAjax()) {
            if($id>0){
                $res = Db::name('centralize_diycountry_category')->where(['id'=>$id])->update(['name'=>trim($dat['name'])]);
            }else{
                $res = Db::name('centralize_diycountry_category')->insert(['name'=>trim($dat['name'])]);
            }
            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = ['name'=>''];
            if($id>0){
                $data = Db::name('centralize_diycountry_category')->where(['id'=>$id])->find();
            }
            return view('',compact('id','data'));
        }
    }

    public function del_code(Request $request){
        $dat = input();
        $res = Db::name('centralize_diycountry_category')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    public function content_list(Request $request)
    {
        $dat = input();
        $pid = $dat['pid'];
        $state_id = isset($dat['state_id'])?$dat['state_id']:'';

        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if ($request->isAjax()) {
//            $search = isset($dat['search'])?trim($dat['search']):'';
            $where = ['pid'=>$pid];
            if(!empty($state_id)){
                $where = array_merge(['state_id'=>$state_id]);
            }
            $count = Db::name('centralize_diycountry_content')->where($where)->count();
            $rows = DB::name('centralize_diycountry_content')
                ->where($where)
                ->limit($limit)
                ->order('id desc')
                ->select();

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('pid','state_id'));
        }
    }

    #属性配置--start
    public function value_list(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        $level = isset($dat['level'])?intval($dat['level']):0;
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if ($request->isAjax()) {
            $count = Db::name('centralize_gvalue_list')->where(['pid'=>$pid])->count();
            $rows = DB::name('centralize_gvalue_list')
                ->where(['pid'=>$pid])
                ->limit($limit)
                ->order('id desc')
                ->select();

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('pid','level'));
        }
    }

    public function save_value(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        $level = isset($dat['level'])?intval($dat['level']):0;
        if ($request->isAjax()) {
            $img = '';
            if(isset($dat['img'])){
                $img = json_encode($dat['img'],true);
            }
            $channel = explode(',',$dat['channel']);
            $merchants = [];
            foreach($channel as $k=>$v){
                if(!empty($v) && isset($dat['merchant'.$v])){
                    array_push($merchants,$dat['merchant'.$v]);
                }
            }
            $ins_data = [
                'pid'=>$pid,
                'name'=>trim($dat['name']),
                'country'=>trim($dat['country']),
                'channel'=>trim($dat['channel']),
                'desc'=>trim($dat['desc']),
                'keywords'=>trim($dat['keywords']),
                'merchants'=>json_encode($merchants,true),
                'link'=>trim($dat['link']),
                'img'=>$img,
            ];

            if($id>0){
                $res = Db::name('centralize_gvalue_list')->where(['id'=>$id])->update($ins_data);
            }else{
                $res = Db::name('centralize_gvalue_list')->insert($ins_data);
            }
            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = ['name'=>'','desc'=>'','keywords'=>'','img'=>'','country'=>'','link'=>'','channel'=>''];
            if($id>0){
                $data = Db::name('centralize_gvalue_list')->where(['id'=>$id])->find();
                $data['img'] = json_decode($data['img'],true);
                if(!empty($data['channel'])){
                    $data['channel2'] = explode(',',$data['channel']);
                    $data['select_merchant'] = [];
                    foreach($data['channel2'] as $k=>$v){
                        #找到该渠道全部商户
                        $merchant = Db::query('select * from ims_website_user where FIND_IN_SET('.$v.',merch_channel) ');
                        $data['select_merchant'][$k] = json_encode($merchant,true);
                    }
                }
                if(!empty($data['merchants'])){
                    $data['merchants'] = json_decode($data['merchants'],true);
                }
            }

            $country = Db::name('centralize_diycountry_content')->where(['pid'=>5])->select();
            $country = json_encode($country,true);

            #适用渠道
            $channel = Db::name('centralize_channel_list')->select();
            $channel_name = $channel;
            $channel = json_encode($channel,true);

            return view('',compact('id','data','pid','level','country','channel','channel_name'));
        }
    }

    public function get_merchant(Request $request){
        $dat = input();
        $channel_id = isset($dat['channel_id'])?$dat['channel_id']:0;
        if($request->isAjax()){
            #商户
            $merchant = Db::query('select * from ims_website_user where FIND_IN_SET('.$channel_id.',merch_channel) ');
            foreach($merchant as $k=>$v){
                $merchant[$k]['selected'] = true;
            }
            return json(['code'=>0,'data'=>$merchant]);
        }
    }

    public function del_value(Request $request){
        $dat = input();
        $res = Db::name('centralize_gvalue_list')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
    #属性配置--end

    #各国限制物品-start
    public function prohibit_list(Request $request){
        $dat = input();
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if ($request->isAjax()) {
            $count = Db::name('centralize_prohibit_list')->count();
            $rows = DB::name('centralize_prohibit_list')
                ->limit($limit)
                ->order('id desc')
                ->select();

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact());
        }
    }

    public function save_prohibit(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if ($request->isAjax()) {
            $ins_data = [
                'country_id'=>$dat['country_id'],
                'desc'=>json_encode($dat['desc'],true),
                'keywords'=>json_encode($dat['keywords'],true),
                'prohibit_method'=>json_encode($dat['prohibit_method'],true),
            ];

            if($id>0){
                $res = Db::name('centralize_prohibit_list')->where(['id'=>$id])->update($ins_data);
            }else{
                $res = Db::name('centralize_prohibit_list')->insert($ins_data);
            }
            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = ['country_id'=>'','desc'=>'','keywords'=>'','prohibit_method'=>''];
            if($id>0){
                $data = Db::name('centralize_prohibit_list')->where(['id'=>$id])->find();
                $data['desc'] = json_decode($data['desc'],true);
                $data['keywords'] = json_decode($data['keywords'],true);
                $data['prohibit_method'] = json_decode($data['prohibit_method'],true);
            }

            $country = Db::name('country_code')->select();
            return view('',compact('id','data','country'));
        }
    }

    public function del_prohibit(Request $request){
        $dat = input();
        $res = Db::name('centralize_gvalue_list')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
    #各国限制物品-end

    public function upload_file(Request $request){
        $dat = input();
        $pid = intval($dat['pid']);
        $id = intval($dat['id']);
        if($request->isAjax()){
            $res = Db::name('centralize_diycountry_content')->where(['pid'=>$pid,'id'=>$id])->update([
                'param2'=>$dat['img_file'][0]
            ]);

            if($res){
                return json(['code'=>0,'msg'=>'上传成功']);
            }
        }else{
            return view('',compact('pid','id'));
        }
    }

    public function upload_code(Request $request){
        $dat = input();
        $pid = intval($dat['pid']);

        if($request->isAjax()){
            $file = $request->file('file');
            if (!$file) {
                return json(['code' => -1, 'msg' => '请上传文件']);
            }
            $path = ROOT_PATH.'public'.DS.'static'.DS.'home'.DS.'uploads';

            $saveResult = $file->validate(['ext' => 'xls,csv,xlsx'])->move($path);
            if (!$saveResult) {
                return json(['code' => -1, 'msg' => $file->getError()]);
            }
            $fileName = $path.'/'.$saveResult->getSaveName();
            $data = [];
            $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
            require_once($dir."/IOFactory.php");
            $inputFileType = PHPExcel_IOFactory::identify($fileName);
            $objRead = PHPExcel_IOFactory::createReader($inputFileType);
            $objRead->setReadDataOnly(true);
            $PHPRead = $objRead->load($fileName);
            $sheet = $PHPRead->getSheet(0);
            $allRow = $sheet->getHighestRow();

            for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                if($pid==10){
                    array_push($data, [
                        'param1' => $PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue().'邮政',
                        'param2' => $PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue(),
                        'param3' => $PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue(),
                        'param4' => $PHPRead->getActiveSheet()->getCell("C".$currentRow)->getValue(),
                        'param5' => $PHPRead->getActiveSheet()->getCell("D".$currentRow)->getValue(),
                        'param6' => $PHPRead->getActiveSheet()->getCell("E".$currentRow)->getValue(),
                        'param7' => $PHPRead->getActiveSheet()->getCell("F".$currentRow)->getValue(),
                        'param8' => $PHPRead->getActiveSheet()->getCell("G".$currentRow)->getValue(),
                        'param9' => $PHPRead->getActiveSheet()->getCell("H".$currentRow)->getValue(),
                        'param10' => $PHPRead->getActiveSheet()->getCell("I".$currentRow)->getValue(),
                        'param11' => $PHPRead->getActiveSheet()->getCell("J".$currentRow)->getValue(),
                        'param12' => $PHPRead->getActiveSheet()->getCell("K".$currentRow)->getValue(),
                        'param13' => $PHPRead->getActiveSheet()->getCell("L".$currentRow)->getValue(),
                        'param14' => $PHPRead->getActiveSheet()->getCell("M".$currentRow)->getValue(),
                        'param15' => $PHPRead->getActiveSheet()->getCell("N".$currentRow)->getValue(),
                        'param16' => $PHPRead->getActiveSheet()->getCell("O".$currentRow)->getValue(),
                        'param17' => $PHPRead->getActiveSheet()->getCell("P".$currentRow)->getValue(),
                        'param18' => $PHPRead->getActiveSheet()->getCell("Q".$currentRow)->getValue(),
                        'param19' => $PHPRead->getActiveSheet()->getCell("S".$currentRow)->getValue(),
                        'param20' => $PHPRead->getActiveSheet()->getCell("T".$currentRow)->getValue(),
                        'param21' => $PHPRead->getActiveSheet()->getCell("U".$currentRow)->getValue(),
                        'param22' => $PHPRead->getActiveSheet()->getCell("V".$currentRow)->getValue(),
                        'param23' => $PHPRead->getActiveSheet()->getCell("W".$currentRow)->getValue(),
                        'param24' => $PHPRead->getActiveSheet()->getCell("X".$currentRow)->getValue(),
                        'param25' => $PHPRead->getActiveSheet()->getCell("Y".$currentRow)->getValue(),
                        'param26' => $PHPRead->getActiveSheet()->getCell("Z".$currentRow)->getValue(),
                    ]);
                }else{
                    array_push($data, [
                        'param1' => $PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue(),
                        'param2' => $PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue(),
                        'param3' => $PHPRead->getActiveSheet()->getCell("C".$currentRow)->getValue(),
                        'param4' => $PHPRead->getActiveSheet()->getCell("D".$currentRow)->getValue(),
                        'param5' => $PHPRead->getActiveSheet()->getCell("E".$currentRow)->getValue(),
                        'param6' => $PHPRead->getActiveSheet()->getCell("F".$currentRow)->getValue(),
                        'param7' => $PHPRead->getActiveSheet()->getCell("G".$currentRow)->getValue(),
                        'param8' => $PHPRead->getActiveSheet()->getCell("H".$currentRow)->getValue(),
                        'param9' => $PHPRead->getActiveSheet()->getCell("I".$currentRow)->getValue(),
                        'param10' => $PHPRead->getActiveSheet()->getCell("J".$currentRow)->getValue(),
                        'param11' => $PHPRead->getActiveSheet()->getCell("K".$currentRow)->getValue(),
                        'param12' => $PHPRead->getActiveSheet()->getCell("L".$currentRow)->getValue(),
                        'param13' => $PHPRead->getActiveSheet()->getCell("M".$currentRow)->getValue(),
                        'param14' => $PHPRead->getActiveSheet()->getCell("N".$currentRow)->getValue(),
                        'param15' => $PHPRead->getActiveSheet()->getCell("O".$currentRow)->getValue(),
                        'param16' => $PHPRead->getActiveSheet()->getCell("P".$currentRow)->getValue(),
                        'param17' => $PHPRead->getActiveSheet()->getCell("Q".$currentRow)->getValue(),
                        'param18' => $PHPRead->getActiveSheet()->getCell("R".$currentRow)->getValue(),
                        'param19' => $PHPRead->getActiveSheet()->getCell("S".$currentRow)->getValue(),
                        'param20' => $PHPRead->getActiveSheet()->getCell("T".$currentRow)->getValue(),
                        'param21' => $PHPRead->getActiveSheet()->getCell("U".$currentRow)->getValue(),
                        'param22' => $PHPRead->getActiveSheet()->getCell("V".$currentRow)->getValue(),
                        'param23' => $PHPRead->getActiveSheet()->getCell("W".$currentRow)->getValue(),
                        'param24' => $PHPRead->getActiveSheet()->getCell("X".$currentRow)->getValue(),
                        'param25' => $PHPRead->getActiveSheet()->getCell("Y".$currentRow)->getValue(),
                        'param26' => $PHPRead->getActiveSheet()->getCell("Z".$currentRow)->getValue(),
                    ]);
                }
            }

            $origin = Db::name('centralize_diycountry_content')->select();

            foreach($data as $k=>$v){
                $ishave = Db::name('centralize_diycountry_content')->where(['param1'=>$v['param1'],'pid'=>$pid])->find();
                if(!empty($ishave['param1'])){
                    Db::name('centralize_diycountry_content')->where(['param1'=>$v['param1'],'pid'=>$pid])->update([
                        'param1'=>trim($v['param1']),
                        'param2'=>trim($v['param2']),
                        'param3'=>trim($v['param3']),
                        'param4'=>trim($v['param4']),
                        'param5'=>trim($v['param5']),
                        'param6'=>trim($v['param6']),
                        'param7'=>trim($v['param7']),
                        'param8'=>trim($v['param8']),
                        'param9'=>trim($v['param9']),
                        'param10'=>trim($v['param10']),
                        'param11'=>trim($v['param11']),
                        'param12'=>trim($v['param12']),
                        'param13'=>trim($v['param13']),
                        'param14'=>trim($v['param14']),
                        'param15'=>trim($v['param15']),
                        'param16'=>trim($v['param16']),
                        'param17'=>trim($v['param17']),
                        'param18'=>trim($v['param18']),
                        'param19'=>trim($v['param19']),
                        'param20'=>trim($v['param20']),
                        'param21'=>trim($v['param21']),
                        'param22'=>trim($v['param22']),
                        'param23'=>trim($v['param23']),
                        'param24'=>trim($v['param24']),
                        'param25'=>trim($v['param25']),
                        'param26'=>trim($v['param26']),
                    ]);
                }else{
                    Db::name('centralize_diycountry_content')->insert([
                        'pid'=>$pid,
                        'param1'=>trim($v['param1']),
                        'param2'=>trim($v['param2']),
                        'param3'=>trim($v['param3']),
                        'param4'=>trim($v['param4']),
                        'param5'=>trim($v['param5']),
                        'param6'=>trim($v['param6']),
                        'param7'=>trim($v['param7']),
                        'param8'=>trim($v['param8']),
                        'param9'=>trim($v['param9']),
                        'param10'=>trim($v['param10']),
                        'param11'=>trim($v['param11']),
                        'param12'=>trim($v['param12']),
                        'param13'=>trim($v['param13']),
                        'param14'=>trim($v['param14']),
                        'param15'=>trim($v['param15']),
                        'param16'=>trim($v['param16']),
                        'param17'=>trim($v['param17']),
                        'param18'=>trim($v['param18']),
                        'param19'=>trim($v['param19']),
                        'param20'=>trim($v['param20']),
                        'param21'=>trim($v['param21']),
                        'param22'=>trim($v['param22']),
                        'param23'=>trim($v['param23']),
                        'param24'=>trim($v['param24']),
                        'param25'=>trim($v['param25']),
                        'param26'=>trim($v['param26']),
                    ]);
                }
            }

            @unlink($fileName);
            return json(['code'=>0,'msg'=>'已上传']);
        }else{
            return view('',compact('pid'));
        }
    }

    public function upload_code2(Request $request){
        $dat = input();
        $pid = intval($dat['pid']);

        if($request->isAjax()){
            $file = $request->file('file');
            if (!$file) {
                return json(['code' => -1, 'msg' => '请上传文件']);
            }
            $path = ROOT_PATH.'public'.DS.'static'.DS.'home'.DS.'uploads';

            $saveResult = $file->validate(['ext' => 'xls,csv,xlsx'])->move($path);
            if (!$saveResult) {
                return json(['code' => -1, 'msg' => $file->getError()]);
            }
            $fileName = $path.'/'.$saveResult->getSaveName();
            $data = [];
            $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
            require_once($dir."/IOFactory.php");
            $inputFileType = PHPExcel_IOFactory::identify($fileName);
            $objRead = PHPExcel_IOFactory::createReader($inputFileType);
            $objRead->setReadDataOnly(true);
            $PHPRead = $objRead->load($fileName);
            $sheet = $PHPRead->getSheet(0);
            $allRow = $sheet->getHighestRow();

            $ins_data = [];
            for ($currentRow = 1; $currentRow <= $allRow; $currentRow++) {
                array_push($data, [
                    'param1' => $PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue(),
                    'param2' => trim($PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue()),
                ]);
                array_push($ins_data,trim($PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue()));
            }

            #系统没有的国地，exl表有
            $not_exists = [];
            foreach($data as $k=>$v){
                $ishave = Db::name('centralize_diycountry_content')->where(['param2'=>$v['param2'],'pid'=>$pid])->find();
                if(empty($ishave)){
                    array_push($not_exists,trim($v['param2']));
                    continue;
                }
            }

            $id = 0;
            if(!empty($not_exists)){
                $id = Db::name('centralize_currency_log')->insertGetId(['empt'=>json_encode($not_exists,true)]);
            }

            #系统有，exl表无
            $no_exists2 = [];
            $origin = Db::name('centralize_diycountry_content')->where(['pid'=>$pid])->select();
            foreach($origin as $k=>$v){
                if(!in_array($v['param2'],$ins_data)){
                    array_push($no_exists2,trim($v['param2']));
                }
            }
            $id2 = 0;
            if(!empty($no_exists2)){
                $id2 = Db::name('centralize_currency_log')->insertGetId(['empt'=>json_encode($no_exists2,true)]);
            }

            @unlink($fileName);
            return json(['code'=>0,'msg'=>'已上传','a1'=>$id,'a2'=>$id2]);
        }else{
            return view('',compact('pid'));
        }
    }

    public function export_cname1(Request $request){
        $empty_name1 = input();
        $empty_name1 = Db::name('centralize_currency_log')->where(['id'=>$empty_name1['id']])->find();
        $empty_name1 = json_decode($empty_name1['empt'],true);
        #输出excel表格
        $fileName = '系统缺少国地['.date('Y-m-d H:i:s').'].xls';
        $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes';
        $dir2 = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
        require_once($dir."/PHPExcel.php");
        require_once($dir2."/IOFactory.php");
        $Excel = new PHPExcel();
        $PHPSheet = $Excel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('缺少的国地信息'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue('A1', '国地名称');
        $ExcelWrite = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');
        $n = 2;

        foreach ($empty_name1 as $value) {
            $PHPSheet->setCellValue('A'.$n,"\t" .$value."\t");
            $n +=1;
        }

        ob_end_clean();//清楚缓冲避免乱码
        header('pragma:public');
        //设置表头信息
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name='.$fileName);
        header("Content-Disposition:attachment;filename={$fileName}");//attachment新窗口打
        return $ExcelWrite->save('php://output');
    }

    public function export_cname2(Request $request){
        $empty_name1 = input();
        $empty_name1 = Db::name('centralize_currency_log')->where(['id'=>$empty_name1['id']])->find();
        $empty_name1 = json_decode($empty_name1['empt'],true);
        #输出excel表格
        $fileName = '系统多余国地['.date('Y-m-d H:i:s').'].xls';
        $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes';
        $dir2 = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
        require_once($dir."/PHPExcel.php");
        require_once($dir2."/IOFactory.php");
        $Excel = new PHPExcel();
        $PHPSheet = $Excel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('多余的国地信息'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue('A1', '国地名称');
        $ExcelWrite = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');
        $n = 2;

        foreach ($empty_name1 as $value) {
            $PHPSheet->setCellValue('A'.$n,"\t" .$value."\t");
            $n +=1;
        }

        ob_end_clean();//清楚缓冲避免乱码
        header('pragma:public');
        //设置表头信息
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name='.$fileName);
        header("Content-Disposition:attachment;filename={$fileName}");//attachment新窗口打
        return $ExcelWrite->save('php://output');
    }

    public function upload_code2_phone(Request $request){
        $dat = input();
        $pid = intval($dat['pid']);

        if($request->isAjax()){
            $file = $request->file('file');
            if (!$file) {
                return json(['code' => -1, 'msg' => '请上传文件']);
            }
            $path = ROOT_PATH.'public'.DS.'static'.DS.'home'.DS.'uploads';

            $saveResult = $file->validate(['ext' => 'xls,csv,xlsx'])->move($path);
            if (!$saveResult) {
                return json(['code' => -1, 'msg' => $file->getError()]);
            }
            $fileName = $path.'/'.$saveResult->getSaveName();
            $data = [];
            $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
            require_once($dir."/IOFactory.php");
            $inputFileType = PHPExcel_IOFactory::identify($fileName);
            $objRead = PHPExcel_IOFactory::createReader($inputFileType);
            $objRead->setReadDataOnly(true);
            $PHPRead = $objRead->load($fileName);
            $sheet = $PHPRead->getSheet(0);
            $allRow = $sheet->getHighestRow();
            @unlink($fileName);

            $ins_data = [];
            for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                array_push($data, [
                    'param1' => trim($PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue()),#国家和地区
                    'param2' => trim($PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue()),#电话代码
                    'param3' => trim($PHPRead->getActiveSheet()->getCell("C".$currentRow)->getValue()),#时差
                ]);
                array_push($ins_data,trim($PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue()));

                #将“电话代码”和“时差”插入对应国家里
                Db::name('centralize_diycountry_content')->where(['param2'=>trim($PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue()),'pid'=>$pid])->update([
                    'param8'=>trim($PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue()),
                    'param9'=>trim($PHPRead->getActiveSheet()->getCell("C".$currentRow)->getValue()),
                ]);
            }

            #系统没有的国地，exl表有
            $not_exists = [];
            foreach($data as $k=>$v){
                $ishave = Db::name('centralize_diycountry_content')->where(['param2'=>$v['param1'],'pid'=>$pid])->find();
                if(empty($ishave)){
                    array_push($not_exists,trim($v['param1']));
                    continue;
                }
            }

            $id = 0;
            if(!empty($not_exists)){
                $id = Db::name('centralize_currency_log')->insertGetId(['empt'=>json_encode($not_exists,true)]);
            }

            #系统有，exl表无
            $no_exists2 = [];
            $origin = Db::name('centralize_diycountry_content')->where(['pid'=>$pid])->select();
            foreach($origin as $k=>$v){
                if(!in_array($v['param2'],$ins_data)){
                    array_push($no_exists2,trim($v['param2']));
                }
            }
            $id2 = 0;
            if(!empty($no_exists2)){
                $id2 = Db::name('centralize_currency_log')->insertGetId(['empt'=>json_encode($no_exists2,true)]);
            }


            return json(['code'=>0,'msg'=>'已上传','a1'=>$id,'a2'=>$id2]);
        }else{
            $res = Db::name('centralize_diycountry_content')->where(['pid'=>$pid])->select();
            foreach($res as $k=>$v){
                if($v['param9']!='无固定时区'){
                    if(strpos($v['param9'], 'UTC') == false){
                        Db::name('centralize_diycountry_content')->where(['id'=>$v['id'],'pid'=>$pid])->update(['param9'=>'UTC'.$v['param9']]);
                    }
                }
            }
            return view('',compact('pid'));
        }
    }

    public function upload_code3(Request $request){
        $dat = input();
        $pid = intval($dat['pid']);

        if($request->isAjax()){
            $file = $request->file('file');
            if (!$file) {
                return json(['code' => -1, 'msg' => '请上传文件']);
            }
            $path = ROOT_PATH.'public'.DS.'static'.DS.'home'.DS.'uploads';

            $saveResult = $file->validate(['ext' => 'xls,csv,xlsx'])->move($path);
            if (!$saveResult) {
                return json(['code' => -1, 'msg' => $file->getError()]);
            }
            $fileName = $path.'/'.$saveResult->getSaveName();
            $data = [];
            $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
            require_once($dir."/IOFactory.php");
            $inputFileType = PHPExcel_IOFactory::identify($fileName);
            $objRead = PHPExcel_IOFactory::createReader($inputFileType);
            $objRead->setReadDataOnly(true);
            $PHPRead = $objRead->load($fileName);
            $sheets = $PHPRead->getSheetCount();#获取所有工作表单
            @unlink($fileName);

            $empty_arr = [];#查找不到的国地
            for($i=0;$i<$sheets;$i++){
                $sheet = $PHPRead->getSheet($i);
                $sheet_name = trim($PHPRead->getSheet($i)->getTitle());
                $state = Db::name('centralize_diycountry_content')->where(['pid'=>9,'param1'=>$sheet_name])->find();
                if(empty($state)){
                    #找不到指定州则进行下一个循环
                    continue;
                }
                $allRow = $sheet->getHighestRow();
                for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                    $country_name = trim($PHPRead->getSheet($i)->getCell("A".$currentRow)->getValue());
                    $is_state = Db::name('centralize_diycountry_content')->where(['pid'=>5,'param2'=>$country_name])->find();
                    if($is_state['state_id']==''){
                        $res = Db::name('centralize_diycountry_content')->where(['pid'=>5,'param2'=>$country_name])->update(['state_id'=>$state['id']]);
                    }else{
                        continue;
                    }

                    if(empty($res)){
                        #找不到的国地，另外导出
                        array_push($empty_arr,$country_name);
                    }
                }
            }

            #系统没有的国地，exl表有
            $id = 0;
            if(!empty($empty_arr)){
                $id = Db::name('centralize_currency_log')->insertGetId(['empt'=>json_encode($empty_arr,true)]);
            }


            return json(['code'=>0,'msg'=>'已关联','a1'=>$id]);
        }else{
            return view('',compact('pid'));
        }
    }

    public function export_cname3(Request $request){
        $empty_name1 = input();
        $empty_name1 = Db::name('centralize_currency_log')->where(['id'=>$empty_name1['id']])->find();
        $empty_name1 = json_decode($empty_name1['empt'],true);
        #输出excel表格
        $fileName = '系统空缺国地['.date('Y-m-d H:i:s').'].xls';
        $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes';
        $dir2 = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
        require_once($dir."/PHPExcel.php");
        require_once($dir2."/IOFactory.php");
        $Excel = new PHPExcel();
        $PHPSheet = $Excel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('空缺国地信息'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue('A1', '国地名称');
        $ExcelWrite = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');
        $n = 2;

        foreach ($empty_name1 as $value) {
            $PHPSheet->setCellValue('A'.$n,"\t" .$value."\t");
            $n +=1;
        }

        ob_end_clean();//清楚缓冲避免乱码
        header('pragma:public');
        //设置表头信息
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name='.$fileName);
        header("Content-Disposition:attachment;filename={$fileName}");//attachment新窗口打
        return $ExcelWrite->save('php://output');
    }

    public function importexl_language(Request $request){
        $dat = input();
        $pid = intval($dat['pid']);

        if($request->isAjax()){
            $file = $request->file('file');
            if (!$file) {
                return json(['code' => -1, 'msg' => '请上传文件']);
            }
            $path = ROOT_PATH.'public'.DS.'static'.DS.'home'.DS.'uploads';

            $saveResult = $file->validate(['ext' => 'xls,csv,xlsx'])->move($path);
            if (!$saveResult) {
                return json(['code' => -1, 'msg' => $file->getError()]);
            }
            $fileName = $path.'/'.$saveResult->getSaveName();
            $data = [];
            $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
            require_once($dir."/IOFactory.php");
            $inputFileType = PHPExcel_IOFactory::identify($fileName);
            $objRead = PHPExcel_IOFactory::createReader($inputFileType);
            $objRead->setReadDataOnly(true);
            $PHPRead = $objRead->load($fileName);
            $sheet = $PHPRead->getSheet(0);
            $allRow = $sheet->getHighestRow();
            @unlink($fileName);

            $ins_data = [];
            for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                $country_id = trim($PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue());
                $country_language = trim($PHPRead->getActiveSheet()->getCell("C".$currentRow)->getValue());
                $country_language = explode(',',$country_language);

                #先查询有无重复的语言
                $ishave = Db::name('website_language')->where(['country_id'=>intval($country_id),'flag'=>trim($country_language[0]),'en_name'=>trim($country_language[1]),'zh_name'=>trim($country_language[2])])->find();
                if(empty($ishave)){
                    Db::name('website_language')->insert([
                        'country_id'=>intval($country_id),
                        'flag'=>trim($country_language[0]),
                        'en_name'=>trim($country_language[1]),
                        'zh_name'=>trim($country_language[2])
                    ]);
                }
            }


            return json(['code'=>0,'msg'=>'已上传']);
        }else{
            $res = Db::name('website_language')->select();

            return view('',compact('pid'));
        }
    }

    # 导出国家信息表
    public function exportexl_country(Request $request){
        $country = Db::name('centralize_diycountry_content')->where(['pid'=>5])->select();

        #输出excel表格
        $fileName = '系统国地['.date('Y-m-d H:i:s').'].xls';
        $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes';
        $dir2 = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
        require_once($dir."/PHPExcel.php");
        require_once($dir2."/IOFactory.php");
        $Excel = new PHPExcel();
        $PHPSheet = $Excel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('系统国地'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue('A1', '国地ID');
        $PHPSheet->setCellValue('B1', '国地名称');
        $ExcelWrite = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');
        $n = 2;

        foreach ($country as $value) {
            $PHPSheet->setCellValue('A'.$n,"\t" .$value['id']."\t");
            $PHPSheet->setCellValue('B'.$n,"\t" .$value['param2']."\t");
            $n +=1;
        }

        ob_end_clean();//清楚缓冲避免乱码
        header('pragma:public');
        //设置表头信息
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name='.$fileName);
        header("Content-Disposition:attachment;filename={$fileName}");//attachment新窗口打
        return $ExcelWrite->save('php://output');
    }

    //各国邮政
    public function upload_code4(Request $request){
        $dat = input();
        $pid = intval($dat['pid']);

        if($request->isAjax()){
            $file = $request->file('file');
            if (!$file) {
                return json(['code' => -1, 'msg' => '请上传文件']);
            }
            $path = ROOT_PATH.'public'.DS.'static'.DS.'home'.DS.'uploads';

            $saveResult = $file->validate(['ext' => 'xls,csv,xlsx'])->move($path);
            if (!$saveResult) {
                return json(['code' => -1, 'msg' => $file->getError()]);
            }
            $fileName = $path.'/'.$saveResult->getSaveName();
            $data = [];
            $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
            require_once($dir."/IOFactory.php");
            $inputFileType = PHPExcel_IOFactory::identify($fileName);
            $objRead = PHPExcel_IOFactory::createReader($inputFileType);
            $objRead->setReadDataOnly(true);
            $PHPRead = $objRead->load($fileName);
            $sheets = $PHPRead->getSheetCount();#获取所有工作表单
            @unlink($fileName);

            $empty_arr = [];#查找不到的国地
            for($i=0;$i<$sheets;$i++){
                $sheet = $PHPRead->getSheet($i);
                $sheet_name = trim($PHPRead->getSheet($i)->getTitle());
                $state = Db::name('centralize_diycountry_content')->where(['pid'=>9,'param1'=>$sheet_name])->find();
                if(empty($state)){
                    #找不到指定州则进行下一个循环
                    continue;
                }
                $allRow = $sheet->getHighestRow();
                for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                    $country_name = trim($PHPRead->getSheet($i)->getCell("A".$currentRow)->getValue());
                    $is_state = Db::name('centralize_diycountry_content')->where(['pid'=>5,'param2'=>$country_name])->find();
                    if($is_state['state_id']==''){
                        $res = Db::name('centralize_diycountry_content')->where(['pid'=>5,'param2'=>$country_name])->update(['state_id'=>$state['id']]);
                    }else{
                        continue;
                    }

                    if(empty($res)){
                        #找不到的国地，另外导出
                        array_push($empty_arr,$country_name);
                    }
                }
            }

            #系统没有的国地，exl表有
            $id = 0;
            if(!empty($empty_arr)){
                $id = Db::name('centralize_currency_log')->insertGetId(['empt'=>json_encode($empty_arr,true)]);
            }


            return json(['code'=>0,'msg'=>'已关联','a1'=>$id]);
        }else{
            return view('',compact('pid'));
        }
    }

    //检查各国邮政有无空缺
    public function check_post(Request $request){
        $dat = input();
        $pid = $dat['pid'];

        $allcountry = Db::name('centralize_diycountry_content')->where(['pid'=>5])->select();
        $ins_data2 = [];
        foreach($allcountry as $k=>$v){
            array_push($ins_data2,$v['param2']);
        }
        $allpost = Db::name('centralize_diycountry_content')->where(['pid'=>$pid])->select();
        $ins_data = [];
        foreach($allpost as $k=>$v){
            array_push($ins_data,$v['param2']);
        }

        #系统没有的邮政，国家表有
        $not_exists = [];
        foreach($allcountry as $k=>$v){
            $ishave = Db::name('centralize_diycountry_content')->where(['param2'=>$v['param2'],'pid'=>$pid])->find();
            if(empty($ishave)){
                array_push($not_exists,trim($v['param2']));
                continue;
            }
        }

        $id = 0;
        if(!empty($not_exists)){
            $id = Db::name('centralize_currency_log')->insertGetId(['empt'=>json_encode($not_exists,true)]);
        }

        #系统有，国家表无
        $no_exists2 = [];

        foreach($allpost as $k=>$v){
            if(!in_array($v['param2'],$ins_data2)){
                array_push($no_exists2,trim($v['param2']));
            }
        }
        $id2 = 0;
        if(!empty($no_exists2)){
            $id2 = Db::name('centralize_currency_log')->insertGetId(['empt'=>json_encode($no_exists2,true)]);
        }

        return json(['code'=>0,'msg'=>'已检查','a1'=>$id,'a2'=>$id2]);
    }
}