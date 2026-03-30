<?php
namespace app\admin\controller;

//use think\Controller;
use app\admin\controller;
use think\Request;
use think\Db;
use Excel5;
use PHPExcel;
use PHPExcel_IOFactory;
use WebSocket\Client;

class Basic extends Auth
{
    //集运网网站信息
    public function base_info(Request $request){
        $dat = input();
        if($request->isAjax()){
            $res = Db::name('website_basic')->where('id',2)->update([
                'name'=>json_encode(['zh'=>trim($dat['name']['zh'])]),
                'desc'=>json_encode(['zh'=>trim($dat['desc']['zh'])]),
                'keywords'=>json_encode(['zh'=>trim($dat['keywords']['zh'])]),
                'mobile'=>trim($dat['mobile']),
                'email'=>trim($dat['email']),
                'slogo'=>$dat['slogo_file'][0],
                'logo'=>$dat['logo_file'][0],
//                'banner'=>json_encode($dat['banner_file']),
                'inpic'=>$dat['inpic_file'][0],
                'color'=>$dat['color'],
                'color_inner'=>$dat['color_inner'],
                'color_word'=>$dat['color_word'],
                'copyright'=>json_encode(['zh'=>$dat['copyright_zh']],true),
            ]);

//            if($res){
                return json(['code' => 0, 'msg' => '保存成功！']);
//            }
        }else{
//            $data = Db::name('centralize_basicinfo')->where(['id'=>1])->find();
            $data = Db::name('website_basic')->where('id',2)->find();
            $data['name'] = json_decode($data['name'],true);
            $data['desc'] = json_decode($data['desc'],true);
            $data['keywords'] = json_decode($data['keywords'],true);
            $data['copyright'] = json_decode($data['copyright'],true);
            $data['banner'] = json_decode($data['banner'],true);
            return view('',compact('data'));
        }
    }

    //通用查询（物品属性）
    public function search_info(Request $request){
        set_time_limit(0);
        $data = input();
        $list = [];
        if($data['type']==1){
            #属性查询
            $list = Db::name('centralize_gvalue_list')->where('keywords','like','%'.trim($data['keywords']).'%')->select();
            foreach($list as $k=>$v){
                if($v['pid']==20){
                    $list[$k]['limit_product'] = 1;
                }else{
                    $list[$k]['limit_product'] = 0;
                }
                $parent = Db::name('centralize_gvalue_list')->where(['id'=>$v['pid']])->field('id,name,pid')->find();
                $list[$k]['parent_name'] = $parent['name'];
                if(!empty($parent['pid'])){
                    $parent2 = Db::name('centralize_gvalue_list')->where(['id'=>$parent['pid']])->field('id,name,pid')->find();
                    $list[$k]['parent_name'] = $parent2['name'];
                    if($parent2['id']==20){
                        $list[$k]['limit_product'] = 1;
                    }
                    if(!empty($parent2['pid'])){
                        $parent3 = Db::name('centralize_gvalue_list')->where(['id'=>$parent2['pid']])->field('id,name,pid')->find();
                        if($parent3['id']==20){
                            $list[$k]['limit_product'] = 1;
                        }
                        $list[$k]['parent_name'] = $parent3['name'];
                    }
                }
            }
        }
        return json(['code'=>0,'list'=>$list]);
    }
    //信息内容
    public function view_info(Request $request){
        $data = input();
        $id = $data['id'];
        $type = $data['type'];
        $info = [];
        if($type==1){
            $info = Db::name('centralize_gvalue_list')->where(['id'=>$id])->find();
            $info['country'] = explode(',',$info['country']);
            foreach($info['country'] as $k=>$v){
                $info['country'][$k] = Db::name('centralize_diycountry_content')->where(['id'=>$v])->find();
            }
            $info['channel'] = explode(',',$info['channel']);
            $channel = '';
            foreach($info['channel'] as $k=>$v){
                if($v==1){
                    $channel .= '国际快递，';
                }elseif($v==2){
                    $channel .= '国际邮政，';
                }elseif($v==3){
                    $channel .= '国际专线，';
                }
            }
            $info['channel'] = rtrim($channel,',');
        }
        return view('',compact('id','type','info'));
    }

    //行业配置start
    public function code_list(Request $request){
        $dat = input();
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if ($request->isAjax()) {
            $count = 0;
            $code = [];
            $search = input('search');
            switch($dat['pa']){
                case 1:
                    $count = Db::name('national_region_code')->count();
                    $rows = DB::name('national_region_code')
                        ->limit($limit)
                        ->select();
                    break;
                case 2:
                    $count = Db::name('centralize_diycountry_content')->where(['pid'=>5])->where('param2','like','%'.$search.'%')->count();
                    $rows = DB::name('centralize_diycountry_content')
                        ->where(['pid'=>5])
                        ->where('param2','like','%'.$search.'%')
                        ->limit($limit)
                        ->select();
                    break;
                case 3:
                    $count = Db::name('unit')->count();
                    $rows = DB::name('unit')
                        ->limit($limit)
                        ->select();
                    break;
                case 4:
                    $count = Db::name('customs_express_company_code')->count();
                    $rows = DB::name('customs_express_company_code')
                        ->limit($limit)
                        ->select();
                    break;
                case 5:
                    $count = Db::name('custom_district')->count();
                    $rows = DB::name('custom_district')
                        ->limit($limit)
                        ->select();
                    break;
                case 6:
                    $count = Db::name('inspection')->count();
                    $rows = DB::name('inspection')
                        ->limit($limit)
                        ->select();
                    break;
                case 7:
                    $count = Db::name('country_code')->count();
                    $rows = DB::name('country_code')
                        ->limit($limit)
                        ->select();
                    break;
                case 8:
                    $count = Db::name('port_code')->count();
                    $rows = DB::name('port_code')
                        ->limit($limit)
                        ->select();
                    break;
                case 9:
                    $count = Db::name('loctcode')->count();
                    $rows = DB::name('loctcode')
                        ->limit($limit)
                        ->select();
                    break;
                case 10:
                    $count = Db::name('regulatory_point')->count();
                    $rows = DB::name('regulatory_point')
                        ->limit($limit)
                        ->select();
                    break;
                case 11:
                    $count = Db::name('port')->count();
                    $rows = DB::name('port')
                        ->limit($limit)
                        ->select();
                    break;
                case 12:
                    $count = Db::name('transport')->count();
                    $rows = DB::name('transport')
                        ->limit($limit)
                        ->select();
                    break;
                case 13:
                    $count = Db::name('packing_type')->count();
                    $rows = DB::name('packing_type')
                        ->limit($limit)
                        ->select();
                    break;
                case 14:
                    $count = Db::name('currency')->count();
                    $rows = DB::name('currency')
                        ->limit($limit)
                        ->select();
                    break;
                case 15:
                    $count = Db::name('tradeway')->count();
                    $rows = DB::name('tradeway')
                        ->limit($limit)
                        ->select();
                    break;
                case 16:
                    $count = Db::name('purpose_code')->count();
                    $rows = DB::name('purpose_code')
                        ->limit($limit)
                        ->select();
                    break;
                case 17:
                    $count = Db::name('dealway')->count();
                    $rows = DB::name('dealway')
                        ->limit($limit)
                        ->select();
                    break;
                case 18:
                    $count = Db::name('taxway')->count();
                    $rows = DB::name('taxway')
                        ->limit($limit)
                        ->select();
                    break;
                case 19:
                    $count = Db::name('centralize_hscode_list')->where('pid',0)->count();
                    $rows = DB::name('centralize_hscode_list')
                        ->where('pid',0)
                        ->limit($limit)
                        ->select();
                    break;
                case 20:
                    $count = Db::name('customs_travelexpress_brand')->count();
                    $rows = DB::name('customs_travelexpress_brand')
                        ->alias('a')
                        ->join('customs_travelexpress_cates b','b.id=a.cate_id')
                        ->limit($limit)
                        ->field('a.*,b.name as cate_name')
                        ->select();
                    break;
                case 'table_area':
                    $count = Db::name('centralize_adminstrative_area')->where(['country_id'=>$dat['country_id']])->count();
                    $rows = DB::name('centralize_adminstrative_area')
                        ->where(['country_id'=>$dat['country_id']])
                        ->limit($limit)
                        ->select();
                    break;
                case 'table_currency':
                    $count = Db::name('centralize_currency')->count();
                    $rows = DB::name('centralize_currency')
                        ->alias('a')
                        ->join('centralize_diycountry_content b','b.id=a.country_id')
                        ->limit($limit)
                        ->field('a.*,b.param2 as country_name')
                        ->select();
                    break;
                case 'table_tax':
                    $count = Db::name('centralize_tax_relate')->count();
                    $rows = DB::name('centralize_tax_relate')
                        ->alias('a')
                        ->join('centralize_diycountry_content b','b.id=a.country_id')
                        ->limit($limit)
                        ->field('a.*,b.param2 as country_name')
                        ->select();
                    break;
                case 'table_product':
                    $count = Db::name('centralize_product_num')->count();
                    $rows = DB::name('centralize_product_num')
                        ->limit($limit)
                        ->select();
                    foreach($rows as $k=>$v){
                        $rows[$k]['level1'] = Db::name('centralize_gvalue_list')->where(['id'=>$v['level1']])->find()['name'];
                        $rows[$k]['level2'] = Db::name('centralize_gvalue_list')->where(['id'=>$v['level2']])->find()['name'];
                        $rows[$k]['declare_value'] = $v['declare_value_equal'].$v['declare_value'];
                    }
                    break;
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('');
        }
    }

    public function areacode_list(Request $request){
        $dat = input();
        $country_id = $dat['country_id'];
        $pid = isset($dat['pid'])?$dat['pid']:0;
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if($request->isAjax()){
            $count = 0;
            $code = [];
            $search = input('search');
            $count = Db::name('centralize_country_areas')->where(['country_id'=>$dat['country_id'],'pid'=>$pid])->count();
            $rows = DB::name('centralize_country_areas')
                ->where(['country_id'=>$dat['country_id'],'pid'=>$pid])
                ->limit($limit)
                ->select();
            foreach($rows as $k=>$v){
                if($v['pid']!=0){
                    $rows[$k]['pname'] = Db::name('centralize_country_areas')->where(['id'=>$v['pid']])->find()['name'];
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('country_id','pid'));
        }
    }

    #新增法币
    public function save_currency(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        if($request->isAjax()){
//            dd($dat);
            if($id>0){
                $res = Db::name('centralize_currency')->where(['id'=>$id])->update([
                    'country_id'=>$dat['country_id'],
                    'code_zhname'=>trim($dat['code_zhname']),
                    'code_enname'=>trim($dat['code_enname']),
                    'currency_symbol_origin'=>trim($dat['currency_symbol_origin']),
                    'currency_symbol_standard'=>trim($dat['currency_symbol_standard']),
                    'token_carry'=>trim($dat['token_carry']),
                ]);
            }else{
                $res = Db::name('centralize_currency')->insert([
                    'country_id'=>$dat['country_id'],
                    'code_zhname'=>trim($dat['code_zhname']),
                    'code_enname'=>trim($dat['code_enname']),
                    'currency_symbol_origin'=>trim($dat['currency_symbol_origin']),
                    'currency_symbol_standard'=>trim($dat['currency_symbol_standard']),
                    'token_carry'=>trim($dat['token_carry']),
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $data = ['country_id'=>'','code_zhname'=>'','code_enname'=>'','currency_symbol_origin'=>'','currency_symbol_standard'=>'','token_carry'=>''];
            if($id>0){
                $data = Db::name('centralize_currency')->where(['id'=>$id])->find();
            }
            $country = Db::name('centralize_diycountry_content')->where(['pid'=>5])->select();
            return view('',compact('id','data','country'));
        }
    }

    #导入法币
    public function import_currency(Request $request){
        $dat = input();
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

            for ($currentRow = 4; $currentRow <= $allRow; $currentRow++) {
                array_push($data, [
                    'country_name' => $PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue(),
                    'code_zhname' => $PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue(),
                    'code_enname' => $PHPRead->getActiveSheet()->getCell("C".$currentRow)->getValue(),
                    'currency_symbol_origin' => $PHPRead->getActiveSheet()->getCell("D".$currentRow)->getValue(),
                    'currency_symbol_standard' => $PHPRead->getActiveSheet()->getCell("E".$currentRow)->getValue(),
                    'token_carry' => $PHPRead->getActiveSheet()->getCell("F".$currentRow)->getValue(),
                ]);
            }

//            $origin = Db::name('centralize_currency')->select();
            $empty_name1 = [];#有国地，无货币
            $empty_name2 = [];#有货币，无国地
            foreach($data as $k=>$v){
                $country = Db::name('centralize_diycountry_content')->where(['param2'=>trim($v['country_name']),'pid'=>5])->find();
                if(empty($country)){
                    #有货币，无国地
                    array_push($empty_name2,$v['country_name']);
                    continue;
                }

                $ishave = Db::name('centralize_currency')->where(['country_id'=>$country['id']])->find();
                if(!empty($ishave['id'])){
                    Db::name('centralize_currency')->where(['country_id'=>$country['id']])->update([
                        'country_id'=>$country['id'],
                        'code_zhname'=>trim($v['code_zhname']),
                        'code_enname'=>trim($v['code_enname']),
                        'currency_symbol_origin'=>trim($v['currency_symbol_origin']),
                        'currency_symbol_standard'=>trim($v['currency_symbol_standard']),
                        'token_carry'=>trim($v['token_carry']),
                    ]);
                }else{
                    Db::name('centralize_currency')->insert([
                        'country_id'=>$country['id'],
                        'code_zhname'=>trim($v['code_zhname']),
                        'code_enname'=>trim($v['code_enname']),
                        'currency_symbol_origin'=>trim($v['currency_symbol_origin']),
                        'currency_symbol_standard'=>trim($v['currency_symbol_standard']),
                        'token_carry'=>trim($v['token_carry']),
                    ]);
                }
            }

            $country_list = Db::name('centralize_diycountry_content')->where(['pid'=>5])->select();
            foreach($country_list as $k=>$v){
                $ishave = Db::name('centralize_currency')->where(['country_id'=>$v['id']])->find();
                if(empty($ishave['id'])){
                    #有国地，无货币
                    array_push($empty_name1,$v['param2']);
                    continue;
                }
            }
            if(!empty($empty_name1)){
                $empty_name1 = Db::name('centralize_currency_log')->insertGetId(['empt'=>json_encode($empty_name1,true)]);
            }
            if(!empty($empty_name2)){
                $empty_name2 = Db::name('centralize_currency_log')->insertGetId(['empt'=>json_encode($empty_name2,true)]);
            }


            @unlink($fileName);
            return json(['code'=>0,'msg'=>'已上传','a1'=>$empty_name1,'a2'=>$empty_name2]);
        }else{
            return view('',compact(''));
        }
    }

    #国家地区表中缺少的国地货币之国地名称-有国地，无货币
    public function export_name1(Request $request){
        $empty_name1 = input();
        $empty_name1 = Db::name('centralize_currency_log')->where(['id'=>$empty_name1['id']])->find();
        $empty_name1 = json_decode($empty_name1['empt'],true);
        #输出excel表格
        $fileName = '有国地，无货币['.date('Y-m-d H:i:s').'].xls';
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

    #导入表中缺少的国地名称-有货币，无国地
    public function export_name2(Request $request){
        $empty_name2 = input();
        $empty_name2 = Db::name('centralize_currency_log')->where(['id'=>$empty_name2['id']])->find();
        $empty_name2 = json_decode($empty_name2['empt'],true);
        #输出excel表格
        $fileName = '有货币，无国地['.date('Y-m-d H:i:s').'].xls';
        $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes';
        $dir2 = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
        require_once($dir."/PHPExcel.php");
        require_once($dir2."/IOFactory.php");
        $Excel = new PHPExcel();
        $PHPSheet = $Excel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('缺少的货币信息'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue('A1', '国地名称');
        $PHPSheet->setCellValue('B1', '中文名称');
        $PHPSheet->setCellValue('C1', '英文名称');
        $PHPSheet->setCellValue('D1', '原旧符号');
        $PHPSheet->setCellValue('E1', '标准符号');
        $PHPSheet->setCellValue('F1', '辅币进位制');
        $ExcelWrite = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');
        $n = 2;

        foreach ($empty_name2 as $value) {
            $PHPSheet->setCellValue('A'.$n,"\t" .$value."\t")
                ->setCellValue('B'.$n,"\t" .''."\t")
                ->setCellValue('C'.$n,"\t" .''."\t")
                ->setCellValue('D'.$n,"\t" .''."\t")
                ->setCellValue('E'.$n,"\t" .''."\t")
                ->setCellValue('F'.$n,"\t" .''."\t");
            $n +=1;
        }

        ob_end_clean();//清楚缓冲避免乱码
        header('pragma:public');
        //设置表头信息
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name='.$fileName);
        header("Content-Disposition:attachment;filename={$fileName}");//attachment新窗口打
        return $ExcelWrite->save('php://output');
    }

    #个人自用提示
    #申报货值
    public function declare_value_list(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):1;
        if($request->isAjax()){
//            dd($dat);
            $tips = [];
            foreach($dat['text_tips'] as $k=>$v){
                $tips[$k]['text_tips'] = trim($v);
                foreach($dat['operation_name'][$k] as $kk=>$vv) {
                    $tips[$k]['operation_name'][$kk] = trim($vv);
                    $tips[$k]['operation_select'][$kk] = $dat['operation_select'][$k][$kk];
                    $tips[$k]['operation_url'][$kk] = $dat['operation_select'][$k][$kk]==1?trim($dat['operation_url'][$k][$kk]):'';
                    $tips[$k]['system_urls_value'][$kk] = $dat['operation_select'][$k][$kk]==2?trim($dat['system_urls_value'][$k][$kk]):'';
                }
            }
            $res = Db::name('centralize_person_declare_value')->where(['id'=>$id])->update([
                'declare_value_equal'=>$dat['declare_value_equal'],
                'declare_value'=>trim($dat['declare_value']),
                'tips'=>json_encode($tips,true),
            ]);
            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $data = Db::name('centralize_person_declare_value')->where(['id'=>$id])->find();
            if(!empty($data['tips'])){
                $data['tips'] = json_decode($data['tips'],true);
            }
//            $value = Db::name('centralize_gvalue_list')->where(['pid'=>0])->select();
            $list = json_encode($this->menu2(1),true);
            return view('',compact('id','value','data','list'));
        }
    }

    #物品数量(新增)
    public function declare_num_list(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        if($request->isAjax()){
//            dd($dat);
            $tips = [];
            foreach($dat['text_tips'] as $k=>$v){
                $tips[$k]['text_tips'] = trim($v);
                foreach($dat['operation_name'][$k] as $kk=>$vv) {
                    $tips[$k]['operation_name'][$kk] = trim($vv);
                    $tips[$k]['operation_select'][$kk] = $dat['operation_select'][$k][$kk];
                    $tips[$k]['operation_url'][$kk] = $dat['operation_select'][$k][$kk]==1?trim($dat['operation_url'][$k][$kk]):'';
                    $tips[$k]['system_urls_value'][$kk] = $dat['operation_select'][$k][$kk]==2?trim($dat['system_urls_value'][$k][$kk]):'';
                }
            }
            if($id>0){
                $res = Db::name('centralize_product_num')->where(['id'=>$id])->update([
                    'level1'=>$dat['level1'],
                    'level2'=>$dat['level2'],
                    'declare_value_equal'=>$dat['declare_value_equal'],
                    'declare_value'=>trim($dat['declare_value']),
                    'tips'=>json_encode($tips,true),
                ]);
            }else{
                $res = Db::name('centralize_product_num')->insert([
                    'level1'=>$dat['level1'],
                    'level2'=>$dat['level2'],
                    'declare_value_equal'=>$dat['declare_value_equal'],
                    'declare_value'=>trim($dat['declare_value']),
                    'tips'=>json_encode($tips,true),
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $data = ['level1'=>'','level2'=>'','level2_name'=>'','declare_value_equal'=>'','declare_value'=>'','tips'=>''];
            if($id>0){
               $data = Db::name('centralize_product_num')->where(['id'=>$id])->find();
                if(!empty($data['tips'])){
                    $data['tips'] = json_decode($data['tips'],true);
                }
                $data['level2_name'] = Db::name('centralize_gvalue_list')->where(['id'=>$data['level2']])->find()['name'];
            }

            $value = Db::name('centralize_gvalue_list')->where(['pid'=>0])->select();
            $list = json_encode($this->menu2(1),true);
            return view('',compact('id','value','data','list'));
        }
    }

    #获取二级类别
    public function get_next_value(Request $request){
        $dat = input();
        $list = Db::name('centralize_gvalue_list')->where(['pid'=>$dat['id']])->select();
        return json(['code'=>0,'msg'=>'获取下级货物类别成功','list'=>$list]);
    }

    #包裹毛重
    public function declare_grosswt_list(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):2;
        if($request->isAjax()){
//            dd($dat);
            $tips = [];
            foreach($dat['text_tips'] as $k=>$v){
                $tips[$k]['text_tips'] = trim($v);
                foreach($dat['operation_name'][$k] as $kk=>$vv) {
                    $tips[$k]['operation_name'][$kk] = trim($vv);
                    $tips[$k]['operation_select'][$kk] = $dat['operation_select'][$k][$kk];
                    $tips[$k]['operation_url'][$kk] = $dat['operation_select'][$k][$kk]==1?trim($dat['operation_url'][$k][$kk]):'';
                    $tips[$k]['system_urls_value'][$kk] = $dat['operation_select'][$k][$kk]==2?trim($dat['system_urls_value'][$k][$kk]):'';
                }
            }
            $res = Db::name('centralize_person_declare_value')->where(['id'=>$id])->update([
                'declare_value_equal'=>$dat['declare_value_equal'],
                'declare_value'=>trim($dat['declare_value']),
                'tips'=>json_encode($tips,true),
            ]);
            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $data = Db::name('centralize_person_declare_value')->where(['id'=>$id])->find();
            if(!empty($data['tips'])){
                $data['tips'] = json_decode($data['tips'],true);
            }
//            $value = Db::name('centralize_gvalue_list')->where(['pid'=>0])->select();
            $list = json_encode($this->menu2(1),true);
            return view('',compact('id','value','data','list'));
        }
    }

    #包裹体积
    public function declare_volumn_list(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):3;
        if($request->isAjax()){
//            dd($dat);
            $tips = [];
            foreach($dat['text_tips'] as $k=>$v){
                $tips[$k]['text_tips'] = trim($v);
                foreach($dat['operation_name'][$k] as $kk=>$vv) {
                    $tips[$k]['operation_name'][$kk] = trim($vv);
                    $tips[$k]['operation_select'][$kk] = $dat['operation_select'][$k][$kk];
                    $tips[$k]['operation_url'][$kk] = $dat['operation_select'][$k][$kk]==1?trim($dat['operation_url'][$k][$kk]):'';
                    $tips[$k]['system_urls_value'][$kk] = $dat['operation_select'][$k][$kk]==2?trim($dat['system_urls_value'][$k][$kk]):'';
                }
            }
            $res = Db::name('centralize_person_declare_value')->where(['id'=>$id])->update([
                'declare_value_equal'=>$dat['declare_value_equal'],
                'declare_value'=>trim($dat['declare_value']),
                'tips'=>json_encode($tips,true),
            ]);
            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $data = Db::name('centralize_person_declare_value')->where(['id'=>$id])->find();
            if(!empty($data['tips'])){
                $data['tips'] = json_decode($data['tips'],true);
            }
//            $value = Db::name('centralize_gvalue_list')->where(['pid'=>0])->select();
            $list = json_encode($this->menu2(1),true);
            return view('',compact('id','value','data','list'));
        }
    }

    #一址多邮
    public function declare_post1_list(Request $request){
//        $date = gmdate('D, d M Y H:i:s').' GMT';
//        $appid = '3a8d3d36';
//        $apisecret = 'MjU0MTgwYWUyY2Q1OWY5Y2E0Y2E3ZTJm';
//        $apikey = '98f52ae7f736acf64cdbfd4e53ec6fc8';
//        $tmp = "host: " . "spark-api.xf-yun.com" . "\n";
//        $tmp .= "date: " . $date . "\n";
//        $tmp .= "GET " . "/v3.1/chat" . " HTTP/1.1";
//        $tmp_sha = hash_hmac('sha256', $tmp, $apisecret);
//        $signature = base64_encode(hash('sha256', $tmp_sha, true));
//        $authorization_origin = "api_key='".$apikey."', algorithm='hmac-sha256', headers='host date request-line', signature='".$signature."''";
//        $v = array(
//            "authorization" => $authorization_origin, // 上方鉴权生成的authorization
//            "date" => $date, // 步骤1生成的date
//            "host" => "spark-api.xf-yun.com" // 请求的主机名，根据具体接口替换
//        );
//        $url = "wss://spark-api.xf-yun.com/v3.1/chat?" . http_build_query($v);
//
//        $ch = curl_init($url);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_HEADER, false);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//
//        $response = curl_exec($ch);
//        curl_close($ch);

//        $news = new Getnews();
//        $response = $news->xfyun();
//        dd($response);

        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):4;
        if($request->isAjax()){
//            dd($dat);
            $tips = [];
            foreach($dat['text_tips'] as $k=>$v){
                $tips[$k]['text_tips'] = trim($v);
                foreach($dat['operation_name'][$k] as $kk=>$vv) {
                    $tips[$k]['operation_name'][$kk] = trim($vv);
                    $tips[$k]['operation_select'][$kk] = $dat['operation_select'][$k][$kk];
                    $tips[$k]['operation_url'][$kk] = $dat['operation_select'][$k][$kk]==1?trim($dat['operation_url'][$k][$kk]):'';
                    $tips[$k]['system_urls_value'][$kk] = $dat['operation_select'][$k][$kk]==2?trim($dat['system_urls_value'][$k][$kk]):'';
                }
            }
            $res = Db::name('centralize_person_declare_value')->where(['id'=>$id])->update([
                'declare_value_equal'=>$dat['declare_value_equal'],
                'declare_value'=>trim($dat['declare_value']),
                'tips'=>json_encode($tips,true),
            ]);
            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $data = Db::name('centralize_person_declare_value')->where(['id'=>$id])->find();
            if(!empty($data['tips'])){
                $data['tips'] = json_decode($data['tips'],true);
            }
//            $value = Db::name('centralize_gvalue_list')->where(['pid'=>0])->select();
            $list = json_encode($this->menu2(1),true);
            return view('',compact('id','value','data','list'));
        }
    }

    #分票多邮
    public function declare_post2_list(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):5;
        if($request->isAjax()){
//            dd($dat);
            $tips = [];
            foreach($dat['text_tips'] as $k=>$v){
                $tips[$k]['text_tips'] = trim($v);
                foreach($dat['operation_name'][$k] as $kk=>$vv) {
                    $tips[$k]['operation_name'][$kk] = trim($vv);
                    $tips[$k]['operation_select'][$kk] = $dat['operation_select'][$k][$kk];
                    $tips[$k]['operation_url'][$kk] = $dat['operation_select'][$k][$kk]==1?trim($dat['operation_url'][$k][$kk]):'';
                    $tips[$k]['system_urls_value'][$kk] = $dat['operation_select'][$k][$kk]==2?trim($dat['system_urls_value'][$k][$kk]):'';
                }
            }
            $res = Db::name('centralize_person_declare_value')->where(['id'=>$id])->update([
                'declare_value_equal'=>$dat['declare_value_equal'],
                'declare_value'=>trim($dat['declare_value']),
                'tips'=>json_encode($tips,true),
            ]);
            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $data = Db::name('centralize_person_declare_value')->where(['id'=>$id])->find();
            if(!empty($data['tips'])){
                $data['tips'] = json_decode($data['tips'],true);
            }
//            $value = Db::name('centralize_gvalue_list')->where(['pid'=>0])->select();
            $list = json_encode($this->menu2(1),true);
            return view('',compact('id','value','data','list'));
        }
    }

    #分日多邮
    public function declare_post3_list(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):6;
        if($request->isAjax()){
//            dd($dat);
            $tips = [];
            foreach($dat['text_tips'] as $k=>$v){
                $tips[$k]['text_tips'] = trim($v);
                foreach($dat['operation_name'][$k] as $kk=>$vv) {
                    $tips[$k]['operation_name'][$kk] = trim($vv);
                    $tips[$k]['operation_select'][$kk] = $dat['operation_select'][$k][$kk];
                    $tips[$k]['operation_url'][$kk] = $dat['operation_select'][$k][$kk]==1?trim($dat['operation_url'][$k][$kk]):'';
                    $tips[$k]['system_urls_value'][$kk] = $dat['operation_select'][$k][$kk]==2?trim($dat['system_urls_value'][$k][$kk]):'';
                }
            }
            $res = Db::name('centralize_person_declare_value')->where(['id'=>$id])->update([
//                'declare_value_equal'=>$dat['declare_value_equal'],
                'declare_value'=>trim($dat['declare_value']),
                'tips'=>json_encode($tips,true),
            ]);
            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $data = Db::name('centralize_person_declare_value')->where(['id'=>$id])->find();
            if(!empty($data['tips'])){
                $data['tips'] = json_decode($data['tips'],true);
            }
//            $value = Db::name('centralize_gvalue_list')->where(['pid'=>0])->select();
            $list = json_encode($this->menu2(1),true);
            return view('',compact('id','value','data','list'));
        }
    }

    #新增涉税
    public function save_tax(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        if($request->isAjax()){
//            dd($dat);
            $tax_amount = [];
            $tips = [];

            foreach($dat['regulatory_method'] as $k=>$v){
                $tax_amount[$k]['regulatory_method'] = $v;
                $tax_amount[$k]['transport_method'] = $dat['transport_method'][$k];
                $tax_amount[$k]['line_method'] = $dat['transport_method'][$k]=='国际专线'?$dat['line_method'][$k]:'';
                foreach($dat['taxmethod_select'][$k] as $k2=>$v2){
                    $tax_amount[$k]['taxmethod_select'][$k2] = $v2;
                    $tax_amount[$k]['times'][$k2] = $dat['times'][$k][$k2];
                    $tax_amount[$k]['declare_value_equal'][$k2] = $dat['declare_value_equal'][$k][$k2];
                    $tax_amount[$k]['declare_value_currency'][$k2] = $dat['declare_value_currency'][$k][$k2];
                    $tax_amount[$k]['declare_value'][$k2] = $dat['declare_value'][$k][$k2];
                    $tax_amount[$k]['default_value_equal'][$k2] = $dat['default_value_equal'][$k][$k2];
                    $tax_amount[$k]['default_value'][$k2] = $dat['default_value'][$k][$k2];
                    $tax_amount[$k]['tax_introduce'][$k2] = trim($dat['tax_introduce'][$k][$k2]);
                    foreach($dat['taxes_catename'][$k][$k2] as $k3=>$v3){
                        $tax_amount[$k]['taxes_catename'][$k2][$k3] = trim($v3);
                        $tax_amount[$k]['taxes_burden'][$k2][$k3] = trim($dat['taxes_burden'][$k][$k2][$k3]);
                    }
                    #匹配提示
                    $tips[$k][$k2]['text_tips'] = trim($dat['text_tips'][$k][$k2]);
                    foreach($dat['operation_name'][$k][$k2] as $k3=>$v3){
                        $tips[$k][$k2]['operation_name'][$k3] = trim($v3);
                        $tips[$k][$k2]['operation_select'][$k3] = trim($dat['operation_select'][$k][$k2][$k3]);
                        $tips[$k][$k2]['operation_url'][$k3] = trim($dat['operation_url'][$k][$k2][$k3]);
                        $tips[$k][$k2]['system_urls'][$k3] = trim($dat['system_urls'][$k][$k2][$k3]);
                    }
                }
            }
//            dd($tax_amount);
            if($id>0){
                $res = Db::name('centralize_tax_relate')->where(['id'=>$id])->update([
                    'country_id'=>$dat['country_id'],
                    'legal_currency'=>$dat['legal_currency'],
                    'tax_amount'=>json_encode($tax_amount,true),
                    'tips'=>json_encode($tips,true),
                ]);
            }else{
                $res = Db::name('centralize_tax_relate')->insert([
                    'country_id'=>$dat['country_id'],
                    'legal_currency'=>$dat['legal_currency'],
                    'tax_amount'=>json_encode($tax_amount,true),
                    'tips'=>json_encode($tips,true),
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $data = ['country_id'=>'','legal_currency'=>'','tax_amount'=>'','tips'=>''];
            if($id>0){
                $data = Db::name('centralize_tax_relate')->where(['id'=>$id])->find();
                $data['tax_amount'] = json_decode($data['tax_amount'],true);
                $data['tips'] = json_decode($data['tips'],true);
//                dd($data);
            }
            $country = Db::name('centralize_diycountry_content')->where(['pid'=>5])->select();
            $list = json_encode($this->menu2(1),true);
            $currency = Db::name('centralize_currency')->select();
            return view('',compact('id','data','country','list','currency'));
        }
    }

    public function import_taxs(Request $request){
        $dat = input();
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
            $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
            require_once($dir."/IOFactory.php");
            $inputFileType = PHPExcel_IOFactory::identify($fileName);
            $objRead = PHPExcel_IOFactory::createReader($inputFileType);
            $objRead->setReadDataOnly(true);
            $PHPRead = $objRead->load($fileName);
            $sheets = $PHPRead->getSheetCount();#获取所有工作表单
            @unlink($fileName);

            for($i=0;$i<$sheets;$i++){
                $data = [];
                $sheet = $PHPRead->getSheet($i);
                $sheet_name = trim($PHPRead->getSheet($i)->getTitle());
                $country = Db::name('centralize_diycountry_content')->where(['pid'=>5,'param2'=>$sheet_name])->find();
                if(empty($country)){
                    #找不到指定国家则进行下一个循环
                    continue;
                }
                $currency = Db::name('centralize_currency')->where(['country_id'=>$country['id']])->find();
                $allRow = $sheet->getHighestRow();

                $regulatory_method = '';$transport_method = '';$line_method = '';$tax_method = '';
                for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                    if($PHPRead->getSheet($i)->getCell("A".$currentRow)->getValue()!=''){
                        #处理合并问题
                        $regulatory_method = trim($PHPRead->getSheet($i)->getCell("A".$currentRow)->getValue());
                        $transport_method = trim($PHPRead->getSheet($i)->getCell("B".$currentRow)->getValue());
                        $line_method = trim($PHPRead->getSheet($i)->getCell("C".$currentRow)->getValue());
                    }
                    if($PHPRead->getSheet($i)->getCell("E".$currentRow)->getValue()!='' && $PHPRead->getSheet($i)->getCell("E".$currentRow)->getValue()!='税负方式'){
                        $taxmethod_select = trim($PHPRead->getSheet($i)->getCell("E".$currentRow)->getValue());
                    }
                    if($PHPRead->getSheet($i)->getCell("D".$currentRow)->getValue()!='序号'){
                        array_push($data, [
                            'regulatory_method' => $regulatory_method,
                            'transport_method' => $transport_method,
                            'line_method' => $line_method,
                            'tax_burden_num' => $PHPRead->getSheet($i)->getCell("D".$currentRow)->getValue(),
                            'taxmethod_select' => $taxmethod_select,
                            'times' => $PHPRead->getSheet($i)->getCell("F".$currentRow)->getValue(),
                            'declare_value_equal' => $PHPRead->getSheet($i)->getCell("G".$currentRow)->getValue(),
                            'declare_value_currency' => $PHPRead->getSheet($i)->getCell("H".$currentRow)->getValue(),
                            'declare_value' => $PHPRead->getSheet($i)->getCell("I".$currentRow)->getValue(),
//                    'declare_value_equal' => $PHPRead->getActiveSheet()->getCell("J".$currentRow)->getValue(),
                            'default_value_equal' => $PHPRead->getSheet($i)->getCell("K".$currentRow)->getValue(),
                            'default_value' => $PHPRead->getSheet($i)->getCell("L".$currentRow)->getValue(),
                            'tax_introduce' => $PHPRead->getSheet($i)->getCell("M".$currentRow)->getValue(),
                            'taxes_num' => $PHPRead->getSheet($i)->getCell("N".$currentRow)->getValue(),
                            'taxes_catename' => $PHPRead->getSheet($i)->getCell("O".$currentRow)->getValue(),
                            'taxes_burden' => $PHPRead->getSheet($i)->getCell("P".$currentRow)->getValue(),
                            'tips_num' => $PHPRead->getSheet($i)->getCell("Q".$currentRow)->getValue(),
                            'text_tips' => $PHPRead->getSheet($i)->getCell("R".$currentRow)->getValue(),
                            'operation_num' => $PHPRead->getSheet($i)->getCell("S".$currentRow)->getValue(),
                            'operation_name' => $PHPRead->getSheet($i)->getCell("T".$currentRow)->getValue(),
                            'operation_select' => $PHPRead->getSheet($i)->getCell("U".$currentRow)->getValue(),
                            'operation_url' => $PHPRead->getSheet($i)->getCell("V".$currentRow)->getValue(),
                            'system_urls' => $PHPRead->getSheet($i)->getCell("W".$currentRow)->getValue(),
                        ]);
                    }
                }

                $real_data = [];
                #整理数组=监管方式、物流方式、线路方式
                foreach($data as $k=>$v){
                    if($k>0){
                        if(($v['regulatory_method']!=$data[$k-1]['regulatory_method'] || $v['transport_method']!=$data[$k-1]['transport_method']) && empty($v['line_method'])){
                            #无线路方式
                            array_push($real_data,[
                                'regulatory_method'=>trim($v['regulatory_method']),
                                'transport_method'=>trim($v['transport_method']),
                                'line_method'=>trim($v['line_method'])
                            ]);
                        }elseif(($v['regulatory_method']!=$data[$k-1]['regulatory_method'] || $v['transport_method']!=$data[$k-1]['transport_method']) && !empty($v['line_method'])){
                            #有线路方式
                            if($v['line_method']!=$data[$k-1]['line_method']){
                                array_push($real_data,[
                                    'regulatory_method'=>trim($v['regulatory_method']),
                                    'transport_method'=>trim($v['transport_method']),
                                    'line_method'=>trim($v['line_method'])
                                ]);
                            }
                        }
                    }else{
                        array_push($real_data,[
                            'regulatory_method'=>trim($v['regulatory_method']),
                            'transport_method'=>trim($v['transport_method']),
                            'line_method'=>trim($v['line_method'])
                        ]);
                    }
                }

                #整理数组=税负方式和限额
                list($tax_burden_num,$taxmethod_select,$times,$declare_value_equal,$declare_value_currency,$declare_value,$default_value_equal,$default_value,$tax_introduce) = ['','','','','','','','',''];
                foreach($data as $k=>$v){
                    if($k>0){
                        foreach($real_data as $k2=>$v2){
                            if($v2['regulatory_method']==$data[$k]['regulatory_method'] && $v2['transport_method']==$data[$k]['transport_method'] && $v2['line_method']==$data[$k]['line_method']){
                                if((empty($v['tax_burden_num']) && $tax_burden_num!=$v['tax_burden_num'])){
                                    #税负序号等于空
                                    if(!isset($real_data[$k2]['taxmethod_select'])) {
                                        $real_data[$k2]['taxmethod_select'] = [$taxmethod_select];
                                        $real_data[$k2]['times'] = [$times];
                                        $real_data[$k2]['declare_value_equal'] = [$declare_value_equal];
                                        $real_data[$k2]['declare_value_currency'] = [$declare_value_currency];
                                        $real_data[$k2]['declare_value'] = [$declare_value];
                                        $real_data[$k2]['default_value_equal'] = [$default_value_equal];
                                        $real_data[$k2]['default_value'] = [$default_value];
                                        $real_data[$k2]['tax_introduce'] = [$tax_introduce];
                                        $real_data[$k2]['taxes_catename'] = [];
                                        $real_data[$k2]['taxes_burden'] = [];
                                    }
                                }else{
                                    #下一个税负序号
                                    $tax_burden_num = trim($v['tax_burden_num']);
                                    $taxmethod_select = trim($v['taxmethod_select']);
                                    $times = trim($v['times']);
                                    $declare_value_equal = trim($v['declare_value_equal']);
                                    $declare_value_currency = trim($v['declare_value_currency']);
                                    $declare_value = trim($v['declare_value']);
                                    $default_value_equal = trim($v['default_value_equal']);
                                    $default_value = trim($v['default_value']);
                                    $tax_introduce = trim($v['tax_introduce']);
                                    if(!isset($real_data[$k2]['taxmethod_select'])){
                                        $real_data[$k2]['taxmethod_select']=[$taxmethod_select];
                                        $real_data[$k2]['times']=[$times];
                                        $real_data[$k2]['declare_value_equal']=[$declare_value_equal];
                                        $real_data[$k2]['declare_value_currency']=[$declare_value_currency];
                                        $real_data[$k2]['declare_value']=[$declare_value];
                                        $real_data[$k2]['default_value_equal']=[$default_value_equal];
                                        $real_data[$k2]['default_value']=[$default_value];
                                        $real_data[$k2]['tax_introduce']=[$tax_introduce];
                                        $real_data[$k2]['taxes_catename']=[];
                                        $real_data[$k2]['taxes_burden']=[];
                                    }else{
                                        $real_data[$k2]['taxmethod_select']=array_merge($real_data[$k2]['taxmethod_select'],[$taxmethod_select]);
                                        $real_data[$k2]['times']=array_merge($real_data[$k2]['times'],[$times]);
                                        $real_data[$k2]['declare_value_equal']=array_merge($real_data[$k2]['declare_value_equal'],[$declare_value_equal]);
                                        $real_data[$k2]['declare_value_currency']=array_merge($real_data[$k2]['declare_value_currency'],[$declare_value_currency]);
                                        $real_data[$k2]['declare_value']=array_merge($real_data[$k2]['declare_value'],[$declare_value]);
                                        $real_data[$k2]['default_value_equal']=array_merge($real_data[$k2]['default_value_equal'],[$default_value_equal]);
                                        $real_data[$k2]['default_value']=array_merge($real_data[$k2]['default_value'],[$default_value]);
                                        $real_data[$k2]['tax_introduce']=array_merge($real_data[$k2]['tax_introduce'],[$tax_introduce]);
                                        $real_data[$k2]['taxes_catename']=[];
                                        $real_data[$k2]['taxes_burden']=[];
                                    }
                                }
                            }
                        }
                    }else{
                        $tax_burden_num = trim($v['tax_burden_num']);
                        $taxmethod_select = trim($v['taxmethod_select']);
                        $times = trim($v['times']);
                        $declare_value_equal = trim($v['declare_value_equal']);
                        $declare_value_currency = trim($v['declare_value_currency']);
                        $declare_value = trim($v['declare_value']);
                        $default_value_equal = trim($v['default_value_equal']);
                        $default_value = trim($v['default_value']);
                        $tax_introduce = trim($v['tax_introduce']);
                    }
                }

                #整理数组=税负配置+匹配提示配置
                $tips = [];
                foreach($real_data as $k=>$v){
                    foreach($v['taxmethod_select'] as $k2=>$v2){
                        #循环税负方式数组
                        foreach($data as $k3=>$v3){
                            if($v['regulatory_method']==$v3['regulatory_method'] && $v['transport_method']==$v3['transport_method'] && $v['line_method']==$v3['line_method']){
                                #在同一监管方式、物流方式和线路方式下进行

                                #税负配置
                                if($v['taxmethod_select'][$k2]==$v3['taxmethod_select']){
                                    if($v3['taxes_num']!='序号' && $v3['taxes_num']!='' && !isset($real_data[$k]['taxes_catename'][$k2])){
                                        $real_data[$k]['taxes_catename'][$k2] = [trim($v3['taxes_catename'])];
                                        $real_data[$k]['taxes_burden'][$k2] = [trim($v3['taxes_burden'])];
                                    }elseif($v3['taxes_num']!='序号' && $v3['taxes_num']!='' && isset($real_data[$k]['taxes_catename'][$k2])){
                                        $real_data[$k]['taxes_catename'][$k2] = array_merge($real_data[$k]['taxes_catename'][$k2],[trim($v3['taxes_catename'])]);
                                        $real_data[$k]['taxes_burden'][$k2] = array_merge($real_data[$k]['taxes_burden'][$k2],[trim($v3['taxes_burden'])]);
                                    }

                                    #匹配提示=提示
                                    if($v3['tips_num']!='序号' && $v3['tips_num']!=''){
                                        $tips[$k][$k2]['text_tips'] = trim($v3['text_tips']);
                                        $tips[$k][$k2]['operation_name'] = [];
                                        $tips[$k][$k2]['operation_select'] = [];
                                        $tips[$k][$k2]['operation_url'] = [];
                                        $tips[$k][$k2]['system_urls'] = [];
                                    }

                                    #匹配提示=操作
                                    $sel='';
                                    if(trim($v3['operation_select'])=='URL'){
                                        $sel=1;
                                    }elseif(trim($v3['operation_select'])=='系统菜单'){
                                        $sel=2;
                                    }
                                    if($v3['operation_num']!='序号' && $v3['operation_num']!='' && empty($tips[$k][$k2]['operation_name'])){
                                        $tips[$k][$k2]['operation_name'] = [trim($v3['operation_name'])];
                                        $tips[$k][$k2]['operation_select'] = [$sel];
                                        $tips[$k][$k2]['operation_url'] = [trim($v3['operation_url'])];
                                        if(!empty($v3['system_urls'])){
                                            $tips[$k][$k2]['system_urls'] = [trim(explode(':',$v3['system_urls'])[1])];
                                        }else{
                                            $tips[$k][$k2]['system_urls'] = [trim($v3['system_urls'])];
                                        }
                                    }elseif($v3['operation_num']!='序号' && $v3['operation_num']!='' && !empty($tips[$k][$k2]['operation_name'])){
                                        $tips[$k][$k2]['operation_name'] = array_merge($tips[$k][$k2]['operation_name'],[trim($v3['operation_name'])]);
                                        $tips[$k][$k2]['operation_select'] = array_merge($tips[$k][$k2]['operation_select'],[$sel]);
                                        $tips[$k][$k2]['operation_url'] = array_merge($tips[$k][$k2]['operation_url'],[trim($v3['operation_url'])]);
                                        if(!empty($v3['system_urls'])){
                                            $tips[$k][$k2]['system_urls'] = array_merge($tips[$k][$k2]['system_urls'],[trim(explode(':',$v3['system_urls'])[1])]);
                                        }else{
                                            $tips[$k][$k2]['system_urls'] = array_merge($tips[$k][$k2]['system_urls'],[trim($v3['system_urls'])]);
                                        }

                                    }
                                }
                            }
                        }
                    }
                }
//                if($i==1){
//                    dd($real_data);
//                }else{
//                    continue;
//                }
                $ishave = Db::name('centralize_tax_relate')->where(['country_id'=>$country['id']])->find();
                if($ishave['id']>0){
                    Db::name('centralize_tax_relate')->where(['id'=>$ishave['id']])->update([
                        'tax_amount'=>json_encode($real_data,true),
                        'tips'=>json_encode($tips,true),
                    ]);
                }else{
                    Db::name('centralize_tax_relate')->insert([
                        'country_id'=>$country['id'],
                        'legal_currency'=>$currency['currency_symbol_standard'],
                        'tax_amount'=>json_encode($real_data,true),
                        'tips'=>json_encode($tips,true),
                    ]);
                }
            }

            return json(['code'=>0,'msg'=>'已导入']);
        }else{
            return view('');
        }
    }

    public function del_risk(Request $request){
        $dat = input();
//        dd($dat);
        if($dat['type']==1){
            $res = Db::name('centralize_currency')->where(['id'=>$dat['id']])->delete();
        }elseif($dat['type']==2){
            $res = Db::name('centralize_tax_relate')->where(['id'=>$dat['id']])->delete();
        }elseif($dat['type']==3){
            $res = Db::name('centralize_product_num')->where(['id'=>$dat['id']])->delete();
        }

        if($res){
            return json(['code'=>0,'msg'=>'操作成功']);
        }else{
            return json(['code'=>0,'msg'=>'该行数据已删除，请刷新表格']);
        }
    }

    public function get_currency(Request $request){
        $dat = input();
        $res = Db::name('centralize_currency')->where(['country_id'=>$dat['val']])->find();
        if($res){
            return json(['code'=>0,'msg'=>'获取法币成功','data'=>$res['currency_symbol_standard']]);
        }else{
            return json(['code'=>-1,'msg'=>'获取法币失败，请先添加当前国地法币']);
        }
    }

    #菜单栏目-xmselect树形结构
    public function menu2($typ=0){
        $menu = Db::name('centralize_process_list')->where(['pid'=>0])->field('id,content')->select();
        foreach($menu as $k=>$v){
            $menu[$k]['name'] = $v['content'];
            $menu[$k]['value'] = $v['id'];
            if($typ==1){
                $menu[$k]['children'] = $this->getDownMenu3($v['id']);
            }
        }
        return $menu;
    }

    #不要最下一层的菜单
    public function getDownMenu3($id){
        $cmenu = Db::name('centralize_process_list')->where(['pid'=>$id])->field('id,content')->select();
        foreach($cmenu as $k=>$v){
            $cmenu[$k]['name'] = $v['content'];
            $cmenu[$k]['value'] = $v['id'];
            $cmenu[$k]['children'] = Db::name('centralize_process_list')->where(['pid'=>$v['id']])->field('id,content')->select();
            if(!empty($cmenu[$k]['children'])){
                foreach($cmenu[$k]['children'] as $k2=>$v2){
                    $cmenu[$k]['children'][$k2]['name'] = $v2['content'];
                    $cmenu[$k]['children'][$k2]['value'] = $v2['id'];
//                    $cmenu[$k]['children'][$k2]['children'] = Db::name('website_navbar')->where(['pid'=>$v2['id']])->field('id,name')->select();
//                    if(empty($cmenu[$k]['children'][$k2]['children'])){
//                        unset($cmenu[$k]['children'][$k2]);
//                    }else{
//                        foreach($cmenu[$k]['children'][$k2]['children'] as $k3=>$v3){
//                            $cmenu[$k]['children'][$k2]['children'][$k3]['name'] = json_decode($v3['name'],true)['zh'];
//                            $cmenu[$k]['children'][$k2]['children'][$k3]['value'] = $v3['id'];
//                            $cmenu[$k]['children'][$k2]['children'][$k3]['children'] = Db::name('website_navbar')->where(['pid'=>$v3['id']])->field('id,name')->select();
//                            if(empty($cmenu[$k]['children'][$k2]['children'][$k3]['children'])){
//                                unset($cmenu[$k]['children'][$k2]['children'][$k3]);
//                            }else{
//                                foreach($cmenu[$k]['children'][$k2]['children'][$k3]['children'] as $k4=>$v4){
//                                    $cmenu[$k]['children'][$k2]['children'][$k3]['children'][$k4]['name'] = json_decode($v4['name'],true)['zh'];
//                                    $cmenu[$k]['children'][$k2]['children'][$k3]['children'][$k4]['value'] = $v4['id'];
//                                }
//                            }
//                        }
//                    }
                }
            }
        }
        return $cmenu;
    }

    //代码配置start
    public function code_list_backup(Request $request)
    {
        $dat = input();
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if ($request->isAjax()) {
            $count = 0;
            $code = [];
            switch(intval($dat['pa'])){
                case 1:
                    $count = Db::name('national_region_code')->count();
                    $rows = DB::name('national_region_code')
                        ->limit($limit)
                        ->select();
//                    $count = Db::name('administrative_code')->count();
//                    $rows = DB::name('administrative_code')
//                        ->limit($limit)
//                        ->select();
                    break;
                case 2:
                    $count = Db::name('centralize_manage_oversea_region')->count();
                    $rows = DB::name('centralize_manage_oversea_region')
                        ->limit($limit)
                        ->select();
                    break;
                case 3:
                    $count = Db::name('unit')->count();
                    $rows = DB::name('unit')
                        ->limit($limit)
                        ->select();
                    break;
                case 4:
                    $count = Db::name('customs_express_company_code')->count();
                    $rows = DB::name('customs_express_company_code')
                        ->limit($limit)
                        ->select();
                    break;
                case 5:
                    $count = Db::name('custom_district')->count();
                    $rows = DB::name('custom_district')
                        ->limit($limit)
                        ->select();
                    break;
                case 6:
                    $count = Db::name('inspection')->count();
                    $rows = DB::name('inspection')
                        ->limit($limit)
                        ->select();
                    break;
                case 7:
                    $count = Db::name('country_code')->count();
                    $rows = DB::name('country_code')
                        ->limit($limit)
                        ->select();
                    break;
                case 8:
                    $count = Db::name('port_code')->count();
                    $rows = DB::name('port_code')
                        ->limit($limit)
                        ->select();
                    break;
                case 9:
                    $count = Db::name('loctcode')->count();
                    $rows = DB::name('loctcode')
                        ->limit($limit)
                        ->select();
                    break;
                case 10:
                    $count = Db::name('regulatory_point')->count();
                    $rows = DB::name('regulatory_point')
                        ->limit($limit)
                        ->select();
                    break;
                case 11:
                    $count = Db::name('port')->count();
                    $rows = DB::name('port')
                        ->limit($limit)
                        ->select();
                    break;
                case 12:
                    $count = Db::name('transport')->count();
                    $rows = DB::name('transport')
                        ->limit($limit)
                        ->select();
                    break;
                case 13:
                    $count = Db::name('packing_type')->count();
                    $rows = DB::name('packing_type')
                        ->limit($limit)
                        ->select();
                    break;
                case 14:
                    $count = Db::name('currency')->count();
                    $rows = DB::name('currency')
                        ->limit($limit)
                        ->select();
                    break;
                case 15:
                    $count = Db::name('tradeway')->count();
                    $rows = DB::name('tradeway')
                        ->limit($limit)
                        ->select();
                    break;
                case 16:
                    $count = Db::name('purpose_code')->count();
                    $rows = DB::name('purpose_code')
                        ->limit($limit)
                        ->select();
                    break;
                case 17:
                    $count = Db::name('dealway')->count();
                    $rows = DB::name('dealway')
                        ->limit($limit)
                        ->select();
                    break;
                case 18:
                    $count = Db::name('taxway')->count();
                    $rows = DB::name('taxway')
                        ->limit($limit)
                        ->select();
                    break;
                case 19:
                    $count = Db::name('centralize_hscode_list')->where('pid',0)->count();
                    $rows = DB::name('centralize_hscode_list')
                        ->where('pid',0)
                        ->limit($limit)
                        ->select();
                    break;
                case 20:
                    $count = Db::name('customs_travelexpress_brand')->count();
                    $rows = DB::name('customs_travelexpress_brand')
                        ->alias('a')
                        ->join('customs_travelexpress_cates b','b.id=a.cate_id')
                        ->limit($limit)
                        ->field('a.*,b.name as cate_name')
                        ->select();
                    break;
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('');
        }
    }

    public function upload_code(Request $request){
        $dat = input();
        $type = $dat['type'];

        if($request->isAjax()){
            ini_set('memory_limit', '512M');  // 或更高，如 '1024M'
            set_time_limit(600);  // 无时间限制
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
//            dd($type);
            if($type==1 || $type==2 || $type==6){
                for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                    array_push($data, [
                        'code_name' => $PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue(),
                        'code_value' => $PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue(),
                        'code_path_name' => $PHPRead->getActiveSheet()->getCell("C".$currentRow)->getValue(),
                        'code_path_value' => $PHPRead->getActiveSheet()->getCell("D".$currentRow)->getValue(),
                    ]);
                }

                if($type==1){
                    #国内行政区域
                    $origin = Db::name('administrative_code')->select();
                    if(!empty($origin)){
                        foreach($data as $k=>$v){
                            $ishave = Db::name('administrative_code')->where(['code_value'=>$v['code_value']])->find();
                            if(!empty($ishave['code_name'])){
                                Db::name('administrative_code')->where(['code_value'=>$v['code_value']])->update([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                    'code_path_name'=>$v['code_path_name'],
                                    'code_path_value'=>$v['code_path_value'],
                                ]);
                            }else{
                                Db::name('administrative_code')->insert([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                    'code_path_name'=>$v['code_path_name'],
                                    'code_path_value'=>$v['code_path_value'],
                                ]);
                            }
                        }
                    }else{
                        Db::name('administrative_code')->insertAll($data);
                    }

                }
                elseif($type==2){
                    $origin = Db::name('centralize_manage_oversea_region')->where(['uid'=>0])->select();
                    if(!empty($origin)){
                        foreach($data as $k=>$v){
                            $ishave = Db::name('centralize_manage_oversea_region')->where(['code_value'=>$v['code_value']])->find();
                            if(!empty($ishave['code_name'])){
                                Db::name('centralize_manage_oversea_region')->where(['code_value'=>$v['code_value']])->update([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                    'p_code_name'=>$v['code_path_name'],
                                    'p_code_value'=>$v['code_path_value'],
                                ]);
                            }else{
                                Db::name('centralize_manage_oversea_region')->insert([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                    'p_code_name'=>$v['code_path_name'],
                                    'p_code_value'=>$v['code_path_value'],
                                ]);
                            }
                        }
                    }else{
                        foreach($data as $k=>$v) {
                            Db::name('centralize_manage_oversea_region')->insert([
                                'code_name'=>$v['code_name'],
                                'code_value'=>$v['code_value'],
                                'p_code_name'=>$v['code_path_name'],
                                'p_code_value'=>$v['code_path_value'],
                            ]);
                        }
                    }
                }
                elseif($type==6){
                    $origin = Db::name('inspection')->select();
                    if(!empty($origin)){
                        foreach($data as $k=>$v){
                            $ishave = Db::name('inspection')->where(['code_value'=>$v['code_value']])->find();
                            if(!empty($ishave['code_name'])){
                                Db::name('inspection')->where(['code_value'=>$v['code_value']])->update([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                    'code_path_name'=>$v['code_path_name'],
                                    'code_path_value'=>$v['code_path_value'],
                                ]);
                            }else{
                                Db::name('inspection')->insert([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                    'code_path_name'=>$v['code_path_name'],
                                    'code_path_value'=>$v['code_path_value'],
                                ]);
                            }
                        }
                    }else{
                        Db::name('inspection')->insertAll($data);
                    }
                }
            }

            if($type=='table_area'){
                for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                    array_push($data, [
                        'province_code' => trim($PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue()),
                        'province_name' => trim($PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue()),
                        'city_code' => trim($PHPRead->getActiveSheet()->getCell("C".$currentRow)->getValue()),
                        'city_name' => trim($PHPRead->getActiveSheet()->getCell("D".$currentRow)->getValue()),
                        'country_code' => trim($PHPRead->getActiveSheet()->getCell("E".$currentRow)->getValue()),
                        'country_name' => trim($PHPRead->getActiveSheet()->getCell("F".$currentRow)->getValue()),
                        'town_code' => trim($PHPRead->getActiveSheet()->getCell("G".$currentRow)->getValue()),
                        'town_name' => trim($PHPRead->getActiveSheet()->getCell("H".$currentRow)->getValue()),
                        'village_code' => trim($PHPRead->getActiveSheet()->getCell("I".$currentRow)->getValue()),
                        'village_name' => trim($PHPRead->getActiveSheet()->getCell("J".$currentRow)->getValue()),
                    ]);
                }

                foreach($data as $k=>$v){
//                    $country = Db::name('centralize_diycountry_content')->where(['pid'=>5,'param2'=>$v['country_name']])->find();
                    $country['id'] = 162;
                    #一级
                    $ishave = Db::name('centralize_country_areas')->where(['country_id'=>$country['id'],'pid'=>0,'code'=>$v['province_code'],'name'=>$v['province_name']])->find();
                    if($ishave['id']>0){
                        Db::name('centralize_country_areas')->where(['id'=>$ishave['id']])->update([
                            'pid'=>0,
                            'code'=>$v['province_code'],
                            'name'=>$v['province_name'],
                        ]);
                    }else{
                        $ishave['id'] = Db::name('centralize_country_areas')->insertGetId([
                            'country_id'=>$country['id'],
                            'pid'=>0,
                            'code'=>$v['province_code'],
                            'name'=>$v['province_name']
                        ]);
                    }

                    #二级
                    $ishave2 = Db::name('centralize_country_areas')->where(['country_id'=>$country['id'],'pid'=>$ishave['id'],'code'=>$v['city_code'],'name'=>$v['city_name']])->find();
                    if($ishave2['id']>0){
                        Db::name('centralize_country_areas')->where(['id'=>$ishave2['id']])->update([
                            'pid'=>$ishave['id'],
                            'code'=>$v['city_code'],
                            'name'=>$v['city_name'],
                        ]);
                    }else{
                        $ishave2['id'] = Db::name('centralize_country_areas')->insertGetId([
                            'country_id'=>$country['id'],
                            'pid'=>$ishave['id'],
                            'code'=>$v['city_code'],
                            'name'=>$v['city_name'],
                        ]);
                    }

                    #三级
                    $ishave3 = [];
                    if(!empty($v['country_name'])){
                        $ishave3 = Db::name('centralize_country_areas')->where(['country_id'=>$country['id'],'pid'=>$ishave2['id'],'code'=>$v['country_code'],'name'=>$v['country_name']])->find();
                        if($ishave3['id']>0){
                            Db::name('centralize_country_areas')->where(['id'=>$ishave3['id']])->update([
                                'pid'=>$ishave2['id'],
                                'code'=>$v['country_code'],
                                'name'=>$v['country_name'],
                            ]);
                        }else{
                            $ishave3['id'] = Db::name('centralize_country_areas')->insertGetId([
                                'country_id'=>$country['id'],
                                'pid'=>$ishave2['id'],
                                'code'=>$v['country_code'],
                                'name'=>$v['country_name'],
                            ]);
                        }
                    }


                    #四级
                    $ishave4 = [];
                    if(!empty($v['town_name'])){
                        $ishave4 = Db::name('centralize_country_areas')->where(['country_id'=>$country['id'],'pid'=>$ishave3['id'],'code'=>$v['town_code'],'name'=>$v['town_name']])->find();
                        if($ishave4['id']>0){
                            Db::name('centralize_country_areas')->where(['id'=>$ishave4['id']])->update([
                                'pid'=>$ishave3['id'],
                                'code'=>$v['town_code'],
                                'name'=>$v['town_name'],
                            ]);
                        }else{
                            $ishave4['id'] = Db::name('centralize_country_areas')->insertGetId([
                                'country_id'=>$country['id'],
                                'pid'=>$ishave3['id'],
                                'code'=>$v['town_code'],
                                'name'=>$v['town_name'],
                            ]);
                        }
                    }


                    #五级
                    if(!empty($v['village_name'])) {
                        $ishave5 = Db::name('centralize_country_areas')->where(['country_id' => $country['id'],'pid'=>$ishave4['id'],'code'=>$v['village_code'], 'name' => $v['village_name']])->find();
                        if ($ishave5['id'] > 0) {
                            Db::name('centralize_country_areas')->where(['id' => $ishave5['id']])->update([
                                'pid'=>$ishave4['id'],
                                'code'=>$v['village_code'],
                                'name'=>$v['village_name'],
                            ]);
                        } else {
                           Db::name('centralize_country_areas')->insert([
                                'country_id' => $country['id'],
                                'pid'=>$ishave4['id'],
                                'code'=>$v['village_code'],
                                'name'=>$v['village_name'],
                            ]);
                        }
                    }

                }
            }

            if($type=='0'){
                #国内地区
                for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
//                    array_push($data, [
//                        'level' => $PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue(),
//                        'code_value' => $PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue(),
//                        'code_name' => $PHPRead->getActiveSheet()->getCell("C".$currentRow)->getValue(),
//                        'pcode_value' => $PHPRead->getActiveSheet()->getCell("D".$currentRow)->getValue(),
//                        'route' => $PHPRead->getActiveSheet()->getCell("E".$currentRow)->getValue(),
//                    ]);
                    Db::name('national_region_code')->insert([
                        'level' => $PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue(),
                        'code_value' => $PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue(),
                        'code_name' => $PHPRead->getActiveSheet()->getCell("C".$currentRow)->getValue(),
                        'pcode_value' => $PHPRead->getActiveSheet()->getCell("D".$currentRow)->getValue(),
                        'route' => $PHPRead->getActiveSheet()->getCell("E".$currentRow)->getValue(),
                    ]);
                }

//                $origin = Db::name('national_region_code')->select();
//                if(!empty($origin)){
//                    foreach($data as $k=>$v){
//                        $ishave = Db::name('national_region_code')->where(['code_value'=>$v['code_value']])->find();
//                        if(!empty($ishave['code_name'])){
//                            Db::name('national_region_code')->where(['code_value'=>$v['code_value']])->update([
//                                'code_name'=>$v['code_name'],
//                                'code_value'=>$v['code_value'],
//                                'level'=>$v['level'],
//                                'pcode_value'=>$v['pcode_value'],
//                                'route'=>$v['route'],
//                            ]);
//                        }else{
//                            Db::name('national_region_code')->insert([
//                                'code_name'=>$v['code_name'],
//                                'code_value'=>$v['code_value'],
//                                'level'=>$v['level'],
//                                'pcode_value'=>$v['pcode_value'],
//                                'route'=>$v['route'],
//                            ]);
//                        }
//                    }
//                }else{
//                    Db::name('national_region_code')->insertAll($data);
//                }
            }

            if($type==3 || $type==4 || $type==5 || $type==7 || $type==8 || $type==9 || $type==10 || $type==11 || $type==12 || $type==13 || $type==14 || $type==15 || $type==16 || $type==17 || $type==18){
                for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                    array_push($data, [
                        'code_name' => $PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue(),
                        'code_value' => $PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue(),
                    ]);
                }

                if($type==3){
                    $origin = Db::name('unit')->select();
                    if(!empty($origin)){
                        foreach($data as $k=>$v){
                            $ishave = Db::name('unit')->where(['code_value'=>$v['code_value']])->find();
                            if(!empty($ishave['code_name'])){
                                Db::name('unit')->where(['code_value'=>$v['code_value']])->update([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }else{
                                Db::name('unit')->insert([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }
                        }
                    }else{
                        Db::name('unit')->insertAll($data);
                    }
                }
                elseif($type==4){
                    $origin = Db::name('customs_express_company_code')->select();
                    if(!empty($origin)){
                        foreach($data as $k=>$v){
                            $ishave = Db::name('customs_express_company_code')->where(['code_value'=>$v['code_value']])->find();
                            if(!empty($ishave['name'])){
                                Db::name('customs_express_company_code')->where(['code_value'=>$v['code_value']])->update([
                                    'name'=>$v['code_name'],
                                    'code'=>$v['code_value'],
                                ]);
                            }else{
                                Db::name('customs_express_company_code')->insert([
                                    'name'=>$v['code_name'],
                                    'code'=>$v['code_value'],
                                ]);
                            }
                        }
                    }else{
                        foreach($data as $k=>$v) {
                            Db::name('customs_express_company_code')->insert([
                                'name'=>$v['code_name'],
                                'code'=>$v['code_value'],
                            ]);
                        }
                    }
                }
                elseif($type==5){
                    $origin = Db::name('custom_district')->select();
                    if(!empty($origin)){
                        foreach($data as $k=>$v){
                            $ishave = Db::name('custom_district')->where(['code_value'=>$v['code_value']])->find();
                            if(!empty($ishave['code_name'])){
                                Db::name('custom_district')->where(['code_value'=>$v['code_value']])->update([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }else{
                                Db::name('custom_district')->insert([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }
                        }
                    }else{
                        Db::name('custom_district')->insertAll($data);
                    }
                }
                elseif($type==7){
                    $origin = Db::name('country_code')->select();
                    if(!empty($origin)){
                        foreach($data as $k=>$v){
                            $ishave = Db::name('country_code')->where(['code_value'=>$v['code_value']])->find();
                            if(!empty($ishave['code_name'])){
                                Db::name('country_code')->where(['code_value'=>$v['code_value']])->update([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }else{
                                Db::name('country_code')->insert([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }
                        }
                    }else{
                        Db::name('country_code')->insertAll($data);
                    }
                }
                elseif($type==8){
                    $origin = Db::name('port_code')->select();
                    if(!empty($origin)){
                        foreach($data as $k=>$v){
                            $ishave = Db::name('port_code')->where(['code_value'=>$v['code_value']])->find();
                            if(!empty($ishave['code_name'])){
                                Db::name('port_code')->where(['code_value'=>$v['code_value']])->update([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }else{
                                Db::name('port_code')->insert([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }
                        }
                    }else{
                        Db::name('port_code')->insertAll($data);
                    }
                }
                elseif($type==9){
                    $origin = Db::name('loctcode')->select();
                    if(!empty($origin)){
                        foreach($data as $k=>$v){
                            $ishave = Db::name('loctcode')->where(['code_value'=>$v['code_value']])->find();
                            if(!empty($ishave['code_name'])){
                                Db::name('loctcode')->where(['code_value'=>$v['code_value']])->update([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }else{
                                Db::name('loctcode')->insert([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }
                        }
                    }else{
                        Db::name('loctcode')->insertAll($data);
                    }
                }
                elseif($type==10){
                    $origin = Db::name('port_code')->select();
                    if(!empty($origin)){
                        foreach($data as $k=>$v){
                            $ishave = Db::name('regulatory_point')->where(['code_value'=>$v['code_value']])->find();
                            if(!empty($ishave['code_name'])){
                                Db::name('regulatory_point')->where(['code_value'=>$v['code_value']])->update([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }else{
                                Db::name('regulatory_point')->insert([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }
                        }
                    }else{
                        Db::name('regulatory_point')->insertAll($data);
                    }
                }
                elseif($type==11){
                    $origin = Db::name('port_code')->select();
                    if(!empty($origin)){
                        foreach($data as $k=>$v){
                            $ishave = Db::name('port')->where(['code_value'=>$v['code_value']])->find();
                            if(!empty($ishave['code_name'])){
                                Db::name('port')->where(['code_value'=>$v['code_value']])->update([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }else{
                                Db::name('port')->insert([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }
                        }
                    }else{
                        Db::name('port')->insertAll($data);
                    }
                }
                elseif($type==12){
                    $origin = Db::name('transport')->select();
                    if(!empty($origin)){
                        foreach($data as $k=>$v){
                            $ishave = Db::name('transport')->where(['code_value'=>$v['code_value']])->find();
                            if(!empty($ishave['code_name'])){
                                Db::name('transport')->where(['code_value'=>$v['code_value']])->update([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }else{
                                Db::name('transport')->insert([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }
                        }
                    }else{
                        Db::name('transport')->insertAll($data);
                    }
                }
                elseif($type==13){
                    $origin = Db::name('packing_type')->select();
                    if(!empty($origin)){
                        foreach($data as $k=>$v){
                            $ishave = Db::name('packing_type')->where(['code_value'=>$v['code_value']])->find();
                            if(!empty($ishave['code_name'])){
                                Db::name('packing_type')->where(['code_value'=>$v['code_value']])->update([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }else{
                                Db::name('packing_type')->insert([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }
                        }
                    }else{
                        Db::name('packing_type')->insertAll($data);
                    }
                }
                elseif($type==14){
                    $origin = Db::name('currency')->select();
                    if(!empty($origin)){
                        foreach($data as $k=>$v){
                            $ishave = Db::name('currency')->where(['code_value'=>$v['code_value']])->find();
                            if(!empty($ishave['code_name'])){
                                Db::name('currency')->where(['code_value'=>$v['code_value']])->update([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }else{
                                Db::name('currency')->insert([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }
                        }
                    }else{
                        Db::name('currency')->insertAll($data);
                    }
                }
                elseif($type==15){
                    $origin = Db::name('tradeway')->select();
                    if(!empty($origin)){
                        foreach($data as $k=>$v){
                            $ishave = Db::name('tradeway')->where(['code_value'=>$v['code_value']])->find();
                            if(!empty($ishave['code_name'])){
                                Db::name('tradeway')->where(['code_value'=>$v['code_value']])->update([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }else{
                                Db::name('tradeway')->insert([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }
                        }
                    }else{
                        Db::name('tradeway')->insertAll($data);
                    }
                }
                elseif($type==16){
                    $origin = Db::name('purpose_code')->select();
                    if(!empty($origin)){
                        foreach($data as $k=>$v){
                            $ishave = Db::name('purpose_code')->where(['code_value'=>$v['code_value']])->find();
                            if(!empty($ishave['code_name'])){
                                Db::name('purpose_code')->where(['code_value'=>$v['code_value']])->update([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }else{
                                Db::name('purpose_code')->insert([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }
                        }
                    }else{
                        Db::name('purpose_code')->insertAll($data);
                    }
                }
                elseif($type==17){
                    $origin = Db::name('dealway')->select();
                    if(!empty($origin)){
                        foreach($data as $k=>$v){
                            $ishave = Db::name('dealway')->where(['code_value'=>$v['code_value']])->find();
                            if(!empty($ishave['code_name'])){
                                Db::name('dealway')->where(['code_value'=>$v['code_value']])->update([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }else{
                                Db::name('dealway')->insert([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }
                        }
                    }else{
                        Db::name('dealway')->insertAll($data);
                    }
                }
                elseif($type==18){
                    $origin = Db::name('taxway')->select();
                    if(!empty($origin)){
                        foreach($data as $k=>$v){
                            $ishave = Db::name('taxway')->where(['code_value'=>$v['code_value']])->find();
                            if(!empty($ishave['code_name'])){
                                Db::name('taxway')->where(['code_value'=>$v['code_value']])->update([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }else{
                                Db::name('taxway')->insert([
                                    'code_name'=>$v['code_name'],
                                    'code_value'=>$v['code_value'],
                                ]);
                            }
                        }
                    }else{
                        Db::name('taxway')->insertAll($data);
                    }
                }
            }

            @unlink($fileName);
            return json(['code'=>0,'msg'=>'已上传']);
        }else{
            return view('',compact('type'));
        }
    }

    public function save_duty_paragraph(Request $request){
        $dat = input();

        if ($request->isAjax()) {
            $res = Db::name('centralize_hscode_list')->insert([
                'hscode'=>trim($dat['hscode']),
                'name'=>trim($dat['name']),
                'unit'=>trim($dat['unit']),
                'price'=>trim($dat['price']),
                'tax'=>trim($dat['tax']),
            ]);

            if($res){
                return json(['code'=>0,'msg'=>'新增成功']);
            }
        }else{
            return view('',compact(''));
        }
    }
    //代码配置end

    //产品配置start
    public function product_list(Request $request){
        $dat = input();
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');

        if ($request->isAjax()) {
            $count = Db::name('centralize_product_list')->where(['type'=>$dat['type']])->count();
            $rows = DB::name('centralize_product_list')
                ->where(['type'=>$dat['type']])
                ->order($order)
                ->limit($limit)
                ->select();
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    public function add_product_list(Request $request){
        $dat = input();

        if ($request->isAjax()) {
            if($dat['setting_method']==2){
                #手动配置
                if(empty($dat['code']) || empty($dat['supplier']) || empty($dat['img'][0]) || empty($dat['editorValue'])){
                    return json(['code'=>-1,'msg'=>'请配置信息']);
                }
                $res = Db::name('centralize_product_list')->insert([
                    'type'=>$dat['type'],
                    'setting_method'=>$dat['setting_method'],
                    'code'=>trim($dat['code']),
                    'supplier'=>trim($dat['supplier']),
                    'name'=>trim($dat['name']),
                    'img'=>$dat['img'][0],
                    'desc'=>json_encode($dat['editorValue'],true),
                ]);

            }else{
                #接口配置
                if($dat['get_method']==1){
                    #API获取
                    #ready_for_code...

                }
            }
            return json(['code'=>0,'msg'=>'配置成功']);
        }else{
            return view('',compact(''));
        }
    }

    public function see_product(Request $request){
        $dat = input();

        if($request->isAjax()){
            $res = Db::name('centralize_product_list')->where(['id'=>$dat['id']])->update([
                'code'=>trim($dat['code']),
                'supplier'=>trim($dat['supplier']),
                'name'=>trim($dat['name']),
                'img'=>$dat['img'][0],
                'desc'=>json_encode($dat['editorValue'],true),
            ]);

            if($res){
                return json(['code'=>0,'msg'=>'配置成功']);
            }
        }else{
            $data = Db::name('centralize_product_list')->where(['id'=>$dat['id']])->find();
            $data['desc'] = json_decode($data['desc'],true);
            return view('',compact('data'));
        }
    }

    public function del_product(Request $request){
        $dat = input();

        $res = Db::name('centralize_product_list')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
    //产品配置end

    //属性配置start
    public function value_list(Request $request){
        $dat = input();
        if($request->isAjax()){
            $count = 0;
            $rows = [];

            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            if ($request->isAjax()) {
                $count = Db::name('centralize_value_list')->where(['type'=>$dat['type'],'uid'=>0])->count();
                $rows = DB::name('centralize_value_list')
                    ->where(['type'=>$dat['type'],'uid'=>0])
                    ->order($order)
                    ->limit($limit)
                    ->select();
                foreach($rows as $k=>$v){
                    $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
                    $rows[$k]['desc'] = json_decode($v['desc'],true);
                }
                return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
            }
        }else{
            return view('',compact(''));
        }
    }

    public function save_value_list(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        if($request->isAjax()){
            if($id>0){
                Db::name('centralize_value_list')->where(['id'=>$id])->update([
                    'type'=>intval($dat['type']),
//                    'value'=>intval($dat['value']),
                    'names'=>json_encode($dat['names'],true),
                    'name'=>trim($dat['name']),
                    'desc'=>json_encode($dat['desc'],true),
                ]);
            }else{
                Db::name('centralize_value_list')->insert([
                    'type'=>intval($dat['type']),
//                    'value'=>intval($dat['value']),1
                    'names'=>json_encode($dat['names'],true),
                    'name'=>trim($dat['name']),
                    'desc'=>json_encode($dat['desc'],true),
                    'createtime'=>time()
                ]);
            }

            return json(['code'=>0,'msg'=>'操作成功']);
        }else{
            $data = ['type'=>0,'value'=>0,'desc'=>'','names'=>'','name'=>''];
            if($id>0){
                $data = Db::name('centralize_value_list')->where(['id'=>$id])->find();
                $data['desc'] = json_decode($data['desc'],true);
                $data['names'] = json_decode($data['names'],true);
            }
            return view('',compact('id','data'));
        }
    }

    public function del_value(Request $request){
        $dat = input();

        $res = Db::name('centralize_value_list')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
    //属性配置end

    //须知配置start
    public function guide_list(Request $request){
        $dat = input();
        if($request->isAjax()){
            $count = 0;
            $rows = [];

            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            if ($request->isAjax()) {
                $count = Db::name('centralize_guide_list')->count();
                $rows = DB::name('centralize_guide_list')
                    ->order($order)
                    ->limit($limit)
                    ->select();
                return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
            }
        }else{
            return view('',compact(''));
        }
    }

    public function save_guide(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        if($request->isAjax()){
            if($id>0){
                Db::name('centralize_guide_list')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'content'=>json_encode($dat['content'],true)
                ]);
            }else{
                Db::name('centralize_guide_list')->insert([
                    'name'=>trim($dat['name']),
                    'content'=>json_encode($dat['content'],true)
                ]);
            }

            return json(['code'=>0,'msg'=>'操作成功']);
        }else{
            $data = ['name'=>'','content'=>''];
            if($id>0){
                $data = Db::name('centralize_guide_list')->where(['id'=>$id])->find();
                $data['content'] = json_decode($data['content'],true);
            }
            return view('',compact('id','data'));
        }
    }
    //须知配置end

    #与探数对比币种（错误的币种和多了的币种）
    public function contrast_currency(Request $request){
        #探数给我的
        $currency1 = Db::name('website_exchange_rate')->select();

        #我们自己的
        $currency2 = Db::name('centralize_currency')->select();

        $error = [];#错误的币种
        foreach($currency2 as $k=>$v){
            foreach($currency1 as $k2=>$v2){
                if($v['code_zhname']==$v2['name']){
                    if($v['currency_symbol_standard'] != $v2['symbol']){
                        array_push($error,[$v['code_zhname'],$v['currency_symbol_standard'],$v2['symbol']]);
                    }
                }
            }
        }

        $surplus = [];#多余的币种（探数没有的）
        foreach($currency2 as $k=>$v){
            $ishave = Db::name('website_exchange_rate')->where(['name'=>$v['code_zhname']])->find();
            if(empty($ishave)){
                $ishave = Db::name('website_exchange_rate')->where(['symbol'=>$v['currency_symbol_standard']])->find();
                if(empty($ishave)) {
                    array_push($surplus, $v['code_zhname']);
                }
            }
        }

        #输出excel表格
        $fileName = '币种对比['.date('Y-m-d H:i:s').'].xls';
        $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes';
        $dir2 = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
        require_once($dir."/PHPExcel.php");
        require_once($dir2."/IOFactory.php");
        $Excel = new PHPExcel();
        $PHPSheet = $Excel->getActiveSheet(); //获得当前活动sheet的操作对象
//        $PHPSheet->setTitle('币种错误'); //给当前活动sheet设置名称
        $PHPSheet->setTitle('多余币种'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue('A1', '币种名称');#surplus只选这个
//        $PHPSheet->setCellValue('B1', '错误币种');
//        $PHPSheet->setCellValue('C1', '正确币种');
        $PHPSheet->setCellValue('B1', '币种');
        $ExcelWrite = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');
        $n = 2;

//        foreach ($error as $value) {
//            $PHPSheet->setCellValue('A'.$n,"\t" .$value[0]."\t");
//            $PHPSheet->setCellValue('B'.$n,"\t" .$value[1]."\t");
//            $PHPSheet->setCellValue('C'.$n,"\t" .$value[2]."\t");
//            $n +=1;
//        }

//        foreach ($surplus as $value) {
//            $PHPSheet->setCellValue('A'.$n,"\t" .$value."\t");
//            $n +=1;
//        }

        foreach ($currency1 as $value) {
            $PHPSheet->setCellValue('A'.$n,"\t" .$value['name']."\t");
            $PHPSheet->setCellValue('B'.$n,"\t" .$value['symbol']."\t");
            $n +=1;
        }

        ob_end_clean();//清楚缓冲避免乱码
        header('pragma:public');
        //设置表头信息
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name='.$fileName);
        header("Content-Disposition:attachment;filename={$fileName}");//attachment新窗口打
        return $ExcelWrite->save('php://output');
    }

    // 通用上传
    public function upload_file(Request $request)
    {
        set_time_limit(0);
        $data = input();
        $path = ROOT_PATH . 'public' . DS . 'uploads' . DS . $data['folder']. DS .$data['type'];
        $this->mkdirs($path);
        $file = request()->file('file');
        if( $file )
        {
            $save_file = '';
            if(isset($data['is_file_name'])){
                #采用文件的名称
                $filename = $_FILES['file']['name']; // 假设这是上传文件的名称
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $first_name = explode('.',$filename)[0];
                $return_name = $first_name.'.'.$ext;
                $filename = $first_name.'.'.$ext;

                $info = $file->rule('uniqid')->move($path,$filename);
                $files = 'uploads/'.$data['folder'].'/'.$filename;
                $save_file = $filename;
            }else{
                $info = $file->rule('uniqid')->move($path);
                $save_file = $info->getSaveName();
            }

            if( $info )
            {
                return json(["code" => 1, "message" => "上传成功", "file_name"=>$save_file,"file_path" => 'collect_website/public/uploads/'.$data['folder'].'/'.$data['type'].'/'.$save_file ]);
            }else{
                return json(["code" => 0, "message" => "上传失败", "path" => "" ]);
            }

        }else{
            return json(["code" => 0, "message" => "请先上传文件！"]);
        }
    }

    //判断文件夹是否存在，没有则新建。
    public function mkdirs($dir, $mode = 0777)
    {
        if (is_dir($dir) || @mkdir($dir, $mode)) {
            return true;
        }
        if (!mkdirs(dirname($dir), $mode)) {
            return false;
        }
        return @mkdir($dir, $mode);
    }
}