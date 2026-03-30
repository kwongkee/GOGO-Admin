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

class Warehouse extends Auth
{
    //仓库管理
    public function warehouse_list(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $keywords = isset($dat['search'])?trim($dat['search']):'';
            $where = 'warehouse_name like "%'.$keywords.'%" ';

            $count = Db::name('centralize_warehouse_list')->whereRaw($where)->where(['status'=>0])->count();
            $rows = DB::name('centralize_warehouse_list')
                ->whereRaw($where)
                ->where(['status'=>0])
                ->limit($limit)
                ->order('id desc')
                ->select();
            foreach($rows as $k=>$v){
                if($v['warehouse_form']==1){
                    $rows[$k]['warehouse_formname'] = '发货仓库';
                }elseif($v['warehouse_form']==2){
                    $rows[$k]['warehouse_formname'] = '代发仓库';
                }

                $rows[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    //保存仓库
    public function save_warehouse(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){

            $have_postal_code = intval($dat['have_postal_code']);
            $postal_code = $pre_address = '';
            $province_code = $city_code = $district_code = $town_code = $village_code = '';
            if($dat['country_code']!=162){
                //其他国地
                $postal_code = trim($dat['postal_code']);
                if(empty($postal_code)){
                    return json(['code'=>-1,'msg'=>'邮政编码不可为空']);
                }
                $pre_address = trim($dat['pre_address']);
            }

            //中国行政区域
            if($have_postal_code==1 && $dat['country_code']==162){
                //有邮政编码
                $postal_code = trim($dat['postal_code']);
                if(empty($postal_code)){
                    return json(['code'=>-1,'msg'=>'邮政编码不可为空']);
                }
                $pre_address = trim($dat['pre_address']);
            }
            elseif($have_postal_code==2 && $dat['country_code']==162){
                //无邮政编码
                if(isset($dat['province_code'])){
                    $province_code = intval($dat['province_code']);
                }else{
                    return json(['code'=>-1,'msg'=>'请选择省份']);
                }
                if(isset($dat['city_code'])) {
                    $city_code = intval($dat['city_code']);
                }else{
                    return json(['code'=>-1,'msg'=>'请选择城市']);
                }
                if(isset($dat['district_code'])) {
                    $district_code = intval($dat['district_code']);
                }
                if(isset($dat['town_code'])) {
                    $town_code = intval($dat['town_code']);
                }
                if(isset($dat['village_code'])) {
                    $village_code = intval($dat['village_code']);
                }
            }

            if($id>0){
                Db::name('centralize_warehouse_list')->where(['id'=>$id])->update([
                    'uid'=>intval($dat['uid']),
                    'warehouse_form'=>intval($dat['warehouse_form']),
                    'warehouse_name'=>trim($dat['warehouse_name']),
                    'warehouse_code'=>trim($dat['warehouse_code']),
                    'desc'=>trim($dat['desc']),
                    'pic'=>json_encode($dat['pic'],true),
                    'warehouse_type'=>intval($dat['warehouse_type']),
                    'warehouse_structure'=>intval($dat['warehouse_structure']),
                    'warehouse_mode'=>intval($dat['warehouse_mode']),
                    'warehouse_temperature'=>intval($dat['warehouse_temperature']),
                    'warehouse_equipment'=>intval($dat['warehouse_equipment']),
                    'country_code'=>intval($dat['country_code']),
                    'have_postal_code'=>intval($dat['have_postal_code']),//有无邮政编码
                    'province_code'=>$province_code,//省
                    'city_code'=>$city_code,//市
                    'district_code'=>$district_code,//区
                    'town_code'=>$town_code,//镇
                    'village_code'=>$village_code,//村
                    'postal_code'=>$postal_code,
                    'pre_address'=>$pre_address,
                    'address1'=>trim($dat['address1']),
                    'name'=>trim($dat['name']),
                    'area_code'=>trim($dat['area_code']),
                    'mobile'=>trim($dat['mobile']),
                    'email'=>trim($dat['email']),

                    'process_time_type' => isset($dat['process_time_type']) ? intval($dat['process_time_type']) : 0,
                    'process_time_config' => isset($dat['process_time_config']) ? $dat['process_time_config'] : '',
                    'platform_time_type' => isset($dat['platform_time_type']) ? intval($dat['platform_time_type']) : 0,
                    'platform_time_config' => isset($dat['platform_time_config']) ? $dat['platform_time_config'] : '',
                ]);
            }else{
                Db::name('centralize_warehouse_list')->insert([
                    'uid'=>intval($dat['uid']),
                    'warehouse_form'=>intval($dat['warehouse_form']),
                    'warehouse_name'=>trim($dat['warehouse_name']),
                    'warehouse_code'=>trim($dat['warehouse_code']),
                    'desc'=>trim($dat['desc']),
                    'pic'=>json_encode($dat['pic'],true),
                    'warehouse_type'=>intval($dat['warehouse_type']),
                    'warehouse_structure'=>intval($dat['warehouse_structure']),
                    'warehouse_mode'=>intval($dat['warehouse_mode']),
                    'warehouse_temperature'=>intval($dat['warehouse_temperature']),
                    'warehouse_equipment'=>intval($dat['warehouse_equipment']),
                    'country_code'=>intval($dat['country_code']),
                    'have_postal_code'=>intval($dat['have_postal_code']),//有无邮政编码
                    'province_code'=>$province_code,//省
                    'city_code'=>$city_code,//市
                    'district_code'=>$district_code,//区
                    'town_code'=>$town_code,//镇
                    'village_code'=>$village_code,//村
                    'postal_code'=>$postal_code,
                    'pre_address'=>$pre_address,
                    'address1'=>trim($dat['address1']),
                    'name'=>trim($dat['name']),
                    'area_code'=>trim($dat['area_code']),
                    'mobile'=>trim($dat['mobile']),
                    'email'=>trim($dat['email']),

                    'process_time_type' => isset($dat['process_time_type']) ? intval($dat['process_time_type']) : 0,
                    'process_time_config' => isset($dat['process_time_config']) ? $dat['process_time_config'] : '',
                    'platform_time_type' => isset($dat['platform_time_type']) ? intval($dat['platform_time_type']) : 0,
                    'platform_time_config' => isset($dat['platform_time_config']) ? $dat['platform_time_config'] : '',

                    'status'=>0,
                    'createtime'=>time(),
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }
        else{
            $data = ['uid'=>0,'warehouse_form'=>1,'warehouse_name'=>'','warehouse_code'=>'','desc'=>'','pic'=>[],'warehouse_type'=>0,'warehouse_structure'=>0,'warehouse_mode'=>0,'warehouse_temperature'=>0,'warehouse_equipment'=>0,'postal_code'=>'','have_postal_code'=>1,'country_code'=>'','province_code'=>'','city_code'=>'','district_code'=>'','town_code'=>'','village_code'=>'','pre_address'=>'','address1'=>'','name'=>'','area_code'=>'','mobile'=>'','email'=>'','process_time_type'=>0,'process_time_config'=>[],'platform_time_type'=>0,'platform_time_config'=>[]];

            if($id>0){
                $data = Db::name('centralize_warehouse_list')->where(['id'=>$id])->find();
                $data['pic'] = json_decode($data['pic'],true);

                //判断是否无邮政编码，若无邮政编码，则获取所有省市区镇村的选项框内容
                if($data['have_postal_code']==2){
                    #省份
                    $data['province_list'] = Db::name('centralize_country_areas')->where(['country_id'=>$data['country_code'],'pid'=>0])->select();
                    #城市
                    $data['city_list'] = Db::name('centralize_country_areas')->where(['country_id'=>$data['country_code'],'pid'=>$data['province_code']])->select();
                    #区域
                    $data['district_list'] = Db::name('centralize_country_areas')->where(['country_id'=>$data['country_code'],'pid'=>$data['city_code']])->select();
                    #镇街
                    $data['town_list'] = Db::name('centralize_country_areas')->where(['country_id'=>$data['country_code'],'pid'=>$data['district_code']])->select();
                    #村委
                    $data['village_list'] = Db::name('centralize_country_areas')->where(['country_id'=>$data['country_code'],'pid'=>$data['town_code']])->select();
                }

                // 解析截单时间配置
                if(!empty($data['process_time_config'])){
                    $data['process_time_config'] = json_decode($data['process_time_config'], true);
                } else {
                    $data['process_time_config'] = [];
                }

                if(!empty($data['platform_time_config'])){
                    $data['platform_time_config'] = json_decode($data['platform_time_config'], true);
                } else {
                    $data['platform_time_config'] = [];
                }
            }

            $country = Db::name('centralize_diycountry_content')->where(['pid'=>5])->select();

            #商家
            $merchant = Db::name('website_user_company')->where(['status'=>0,'om_id'=>0])->field('id,company')->select();

            $hours = range(0, 23); // 生成0-23的数组
            $days = range(1, 31);  // 生成1-31的数组

            return view('',compact('country','data','id','hours','days','merchant'));
        }
    }

    //删除仓库
    public function del_warehouse(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;

        $res = Db::name('centralize_warehouse_list')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    //终端列表
    public function terminal_list(Request $request){
        $dat = input();
        $warehouse_id = isset($dat['warehouse_id'])?intval($dat['warehouse_id']):0;
        $type = isset($dat['type'])?intval($dat['type']):0;

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $keywords = isset($dat['search'])?trim($dat['search']):'';
            $where = '';

            if(!empty($warehouse_id)){
                $where = 'warehouse_id='.$warehouse_id;
            }else{
                if($type==1){
                    #已配仓库
                    $where = 'warehouse_id<>0';
                }elseif($type==2){
                    #未配仓库
                    $where = 'warehouse_id=0';
                }
            }
            $where .= ' and name like "%'.$keywords.'%" ';

            $count = Db::name('centralize_warehouse_printer')->whereRaw($where)->count();
            $rows = DB::name('centralize_warehouse_printer')
                ->whereRaw($where)
                ->limit($limit)
                ->select();

            $type = ['1'=>'国内电商','2'=>'跨境电商'];
            foreach($rows as $k=>$v){
                $rows[$k]['warehouse_name'] = Db::name('centralize_warehouse_list')->where(['id'=>$v['warehouse_id']])->field('warehouse_name')->find()['warehouse_name'];
                $rows[$k]['typename'] = $type[$v['type']];
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('warehouse_id','type'));
        }
    }

    #添加终端
    public function save_terminal(Request $request){
        $dat = input();
        $warehouse_id = intval($dat['warehouse_id']);
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            if(isset($dat['printer_id'])){
                #选择打印机
                $printer_id = intval($dat['printer_id']);

                Db::name('centralize_warehouse_printer')->where(['id'=>$printer_id])->update([
                    'warehouse_id'=>$warehouse_id
                ]);
                return json(['code'=>0,'msg'=>'保存成功','printer_id'=>0]);
            }
            else{
                if($id>0){
                    Db::name('centralize_warehouse_printer')->where(['id'=>$id])->update([
                        'name'=>trim($dat['name']),
                        'type'=>intval($dat['type']),
                        'order_type'=>intval($dat['order_type']),
                        'key'=>trim($dat['key']),
                        'secret'=>trim($dat['secret']),
                        'siid'=>trim($dat['siid']),
                    ]);

                    if(intval($dat['order_type'])==0){
                        #仓库面单（线上）
                        foreach($dat['express_id'] as $k=>$v){
                            if(isset($dat['express_product_id'][$k])){
                                Db::name('centralize_warehouse_express')->where(['printer_id'=>$id,'id'=>intval($dat['express_product_id'][$k])])->update([
                                    'partnerName'=>trim($dat['partnerName'][$k]),
                                    'partnerId'=>trim($dat['partnerId'][$k]),
                                    'partnerKey'=>trim($dat['partnerKey'][$k]),
                                    'partnerSecret'=>trim($dat['partnerSecret'][$k]),
                                    'code'=>trim($dat['code'][$k]),
                                    'net'=>trim($dat['net'][$k]),
                                    'tempId'=>trim($dat['tempId'][$k]),
                                ]);
                            }else{
                                Db::name('centralize_warehouse_express')->insert([
                                    'printer_id'=>$id,
                                    'express_id'=>$v,
                                    'partnerName'=>trim($dat['partnerName'][$k]),
                                    'partnerId'=>trim($dat['partnerId'][$k]),
                                    'partnerKey'=>trim($dat['partnerKey'][$k]),
                                    'partnerSecret'=>trim($dat['partnerSecret'][$k]),
                                    'code'=>trim($dat['code'][$k]),
                                    'net'=>trim($dat['net'][$k]),
                                    'express_type'=>trim($dat['express_type'][$k]),
                                    'tempId'=>trim($dat['tempId'][$k]),
                                ]);
                            }
                        }
                    }
                }else{
                    $printer_id = Db::name('centralize_warehouse_printer')->insertGetId([
                        'warehouse_id'=>$warehouse_id,
                        'name'=>trim($dat['name']),
                        'type'=>intval($dat['type']),
                        'order_type'=>intval($dat['order_type']),
                        'key'=>trim($dat['key']),
                        'secret'=>trim($dat['secret']),
                        'siid'=>trim($dat['siid']),
                    ]);
                    if(intval($dat['order_type'])==0) {
                        #仓库面单（线上）
                        $id = $printer_id;
                        if ($printer_id) {
                            foreach ($dat['express_id'] as $k => $v) {
                                Db::name('centralize_warehouse_express')->insert([
                                    'printer_id' => $printer_id,
                                    'express_id' => $v,
                                    'partnerName' => trim($dat['partnerName'][$k]),
                                    'partnerId' => trim($dat['partnerId'][$k]),
                                    'partnerKey' => trim($dat['partnerKey'][$k]),
                                    'partnerSecret' => trim($dat['partnerSecret'][$k]),
                                    'code' => trim($dat['code'][$k]),
                                    'net' => trim($dat['net'][$k]),
                                    'express_type' => trim($dat['express_type'][$k]),
                                    'tempId' => trim($dat['tempId'][$k]),
                                ]);
                            }
                        }
                    }
                }
                return json(['code'=>0,'msg'=>'保存成功','printer_id'=>$id]);
            }
        }
        else{
            $terminal_list = [];
            $data = ['name'=>'','type'=>1,'order_type'=>0,'key'=>'','express'=>[],'secret'=>'','siid'=>''];
            if($warehouse_id>0){
                #选择未添加仓库的终端
                $terminal_list = Db::name('centralize_warehouse_printer')->whereRaw('warehouse_id=0')->select();
            }else{
                #添加终端
                if($id>0){
                    $data = Db::name('centralize_warehouse_printer')->where(['id'=>$id])->find();
                    $data['express'] = Db::name('centralize_warehouse_express')->where(['printer_id'=>$id])->select();
                    foreach($data['express'] as $k=>$v){
                        $ishave_freight = Db::name('centralize_freight_config')->where(['printer_id'=>$id,'express_id'=>$v['id'],'express_type'=>$v['express_type']])->find();
                        $data['express'][$k]['ishave_freight'] = empty($ishave_freight)?0:1;
                    }
                    $warehouse_id = $data['warehouse_id'];//配置已有仓库id
                }
            }

            $express = Db::name('centralize_express_product')->select();

            return view('',compact('data','id','warehouse_id','express','terminal_list'));
        }
    }

    #添加运费配置
    public function save_freight_config(Request $request){
        $dat = input();
        $warehouse_id = isset($dat['warehouse_id'])?intval($dat['warehouse_id']):0;
        $printer_id = intval($dat['printer_id']);
        $express_id = intval($dat['express_id']);
        $express_type = trim($dat['express_type']);

        if($request->isAjax()){
            try {
                // 处理配置数据 - 收集表单中的所有 content 数据
                $configData = [];

                //分泡方式
                if($dat['content']['fenpao'][0]==-1 && !empty($dat['diy_fenpao'][0])){
                    $ishave = Db::name('centralize_lines_strict')->where(['type'=>4,'name'=>trim($dat['diy_fenpao'][0])])->find();

                    if($ishave['id']>0){
                        return json(['code'=>-1,'msg'=>'系统已存在该分泡方式名称']);
                    }else{
                        $dat['content']['fenpao'][0] = Db::name('centralize_lines_strict')->insertGetId([
                            'name'=>trim($dat['diy_fenpao'][0]),
                            'type'=>4,
                        ]);
                    }
                }
                elseif($dat['content']['fenpao'][0]==-1 && empty($dat['diy_fenpao'][0])){
                    return json(['code'=>-1,'msg'=>'请输入分泡方式名称']);
                }

                // 收集最低消费配置
                $configData['mini_cost'] = isset($dat['content']['mini_cost']) ? $dat['content']['mini_cost'] : [];
                $configData['mini_num'] = isset($dat['content']['mini_num']) ? $dat['content']['mini_num'] : [];
                $configData['minicost_unit'] = isset($dat['content']['minicost_unit']) ? $dat['content']['minicost_unit'] : [];

                // 收集计费区间数据
                $configData['qj1'] = isset($dat['content']['qj1']) ? $dat['content']['qj1'] : [];
                $configData['qj2_method'] = isset($dat['content']['qj2_method']) ? $dat['content']['qj2_method'] : [];
                $configData['qj2'] = isset($dat['content']['qj2']) ? $dat['content']['qj2'] : [];
                $configData['unit'] = isset($dat['content']['unit']) ? $dat['content']['unit'] : [];
                $configData['jinjie'] = isset($dat['content']['jinjie']) ? $dat['content']['jinjie'] : [];

                // 收集计费方式数据
                $configData['jf_method'] = isset($dat['content']['jf_method']) ? $dat['content']['jf_method'] : [];
                $configData['shouzhong'] = isset($dat['content']['shouzhong']) ? $dat['content']['shouzhong'] : [];
                $configData['shouzhong_money'] = isset($dat['content']['shouzhong_money']) ? $dat['content']['shouzhong_money'] : [];
                $configData['xuzhong'] = isset($dat['content']['xuzhong']) ? $dat['content']['xuzhong'] : [];
                $configData['xuzhong_money'] = isset($dat['content']['xuzhong_money']) ? $dat['content']['xuzhong_money'] : [];
                $configData['anliang'] = isset($dat['content']['anliang']) ? $dat['content']['anliang'] : [];
                $configData['anliang_money'] = isset($dat['content']['anliang_money']) ? $dat['content']['anliang_money'] : [];
                $configData['currency'] = isset($dat['content']['currency']) ? $dat['content']['currency'] : [];

                // 收集分段计费数据
                $configData['fenduan_num1'] = isset($dat['content']['fenduan_num1']) ? $dat['content']['fenduan_num1'] : [];
                $configData['fenduan_method'] = isset($dat['content']['fenduan_method']) ? $dat['content']['fenduan_method'] : [];
                $configData['fenduan_num2'] = isset($dat['content']['fenduan_num2']) ? $dat['content']['fenduan_num2'] : [];
                $configData['fenduan_money'] = isset($dat['content']['fenduan_money']) ? $dat['content']['fenduan_money'] : [];
                $configData['fenduan_currency'] = isset($dat['content']['fenduan_currency']) ? $dat['content']['fenduan_currency'] : [];

                // 体积算法
                $configData['rate'] = isset($dat['content']['rate']) ? $dat['content']['rate'] : [];
                $configData['fenpao'] = isset($dat['content']['fenpao']) ? $dat['content']['fenpao'] : [];

                if($warehouse_id==0){
                    $warehouse_id = Db::name('centralize_warehouse_printer')->where(['id'=>$printer_id])->value('warehouse_id');
                }

                // 准备保存的数据
                $saveData = [
                    'warehouse_id' => $warehouse_id,
                    'printer_id' => $printer_id,
                    'express_id' => $express_id,
                    'express_type' => $express_type,
                    'config_data' => json_encode($configData, JSON_UNESCAPED_UNICODE),
                ];

                // 检查是否已存在相同配置
                $where = [
                    'warehouse_id' => $warehouse_id,
                    'printer_id' => $printer_id,
                    'express_id' => $express_id,
                    'express_type' => $express_type,
                ];

                $existingConfig = Db::name('centralize_freight_config')->where($where)->find();

                if ($existingConfig) {
                    // 更新现有配置
                    Db::name('centralize_freight_config')->where(['id' => $existingConfig['id']])->update($saveData);
                    $configId = $existingConfig['id'];
                } else {
                    // 新增配置
                    $configId = Db::name('centralize_freight_config')->insertGetId($saveData);
                }

                return json([
                    'code' => 0,
                    'msg' => '运费配置保存成功',
                    'data' => ['id' => $configId]
                ]);

            } catch (\Exception $e) {
                return json(['code' => -1, 'msg' => '保存失败: ' . $e->getMessage()]);
            }
        }
        else{
            #币种
            $currency = Db::name('centralize_currency')->select();
            #计量单位
            $unit = Db::name('unit')->select();
            foreach($unit as $k=>$v){
                if($v['code_name']=='千克'){
                    $origin = $unit[0];
                    $unit[0] = $v;
                    $unit[$k] = $origin;
                }
                if($v['code_name']=='立方米'){
                    $origin = $unit[1];
                    $unit[1] = $v;
                    $unit[$k] = $origin;
                }
            }
            #分泡
            $fenpao = Db::name('centralize_lines_strict')->where(['type'=>4])->select();

            $value = ['unit'=>$unit,'currency'=>$currency,'start_currency'=>['id'=>5],'fenpao'=>$fenpao];

            $data = Db::name('centralize_freight_config')->where(['printer_id'=>$printer_id,'express_id'=>$express_id,'express_type'=>$express_type])->find();
            if(!empty($data)){
                $data['config_data'] = json_decode($data['config_data'],true);

                // 计费计量单位
                foreach($data['config_data']['unit'] as $k=>$v){
                    foreach($v as $k2 => $v2){
                        $data['config_data']['unit_name'][$k][$k2] = Db::name('unit')->where(['code_value'=>$v2])->value('code_name');
                    }
                }
            }

            return view('',compact('data','express_id','warehouse_id','printer_id','express_type','value'));
        }
    }

    //添加终端仓库
    public function save_printer_warehouse(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            $res = Db::name('centralize_warehouse_printer')->where(['id'=>$id])->update(['warehouse_id'=>intval($dat['warehouse_id'])]);

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $warehouse = Db::name('centralize_warehouse_list')->where(['status'=>0,'warehouse_form'=>1])->select();
            return view('',compact('warehouse','id'));
        }
    }

    //删除终端
    public function del_terminal(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $warehouse_id = isset($dat['warehouse_id'])?intval($dat['warehouse_id']):0;

        if($warehouse_id==0){
            $res = Db::name('centralize_warehouse_printer')->where(['id'=>$id])->delete();
            if($res){
                Db::name('centralize_warehouse_express')->where(['printer_id'=>$id])->delete();
                return json(['code'=>0,'msg'=>'删除成功']);
            }
        }else{
            $res = Db::name('centralize_warehouse_printer')->where(['id'=>$id])->update(['warehouse_id'=>0]);
            if($res){
                return json(['code'=>0,'msg'=>'已从此仓库移除该打印机']);
            }
        }
    }

    //删除打印机的快递企业
    public function del_printer_express(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $res = Db::name('centralize_warehouse_express')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    //添加“代发”仓库的快递企业
    public function save_warehouse_express(Request $request){
        $dat = input();
        $warehouse_id = intval($dat['warehouse_id']);
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            foreach($dat['express_id'] as $k=>$v){
                if(isset($dat['express_product_id'][$k])){
                    Db::name('centralize_warehouse_express')->where(['warehouse_id'=>$warehouse_id,'id'=>intval($dat['express_product_id'][$k])])->update([
                        'partnerName'=>trim($dat['partnerName'][$k]),
                        'partnerId'=>trim($dat['partnerId'][$k]),
                        'partnerKey'=>trim($dat['partnerKey'][$k]),
                        'partnerSecret'=>trim($dat['partnerSecret'][$k]),
                        'code'=>trim($dat['code'][$k]),
                        'net'=>trim($dat['net'][$k]),
                        'tempId'=>trim($dat['tempId'][$k]),
                    ]);
                }else{
                    Db::name('centralize_warehouse_express')->insert([
                        'warehouse_id'=>$warehouse_id,
                        'express_id'=>$v,
                        'partnerName'=>trim($dat['partnerName'][$k]),
                        'partnerId'=>trim($dat['partnerId'][$k]),
                        'partnerKey'=>trim($dat['partnerKey'][$k]),
                        'partnerSecret'=>trim($dat['partnerSecret'][$k]),
                        'code'=>trim($dat['code'][$k]),
                        'net'=>trim($dat['net'][$k]),
                        'express_type'=>trim($dat['express_type'][$k]),
                        'tempId'=>trim($dat['tempId'][$k]),
                    ]);
                }
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }
        else{
            $express_list = [];
            $data = ['printer_id'=>0,'express_id'=>0,'partnerName'=>'','partnerId'=>'','partnerKey'=>'','partnerSecret'=>'','code'=>'','net'=>'','express_type'=>'','tempId'=>'','express'];
            if($warehouse_id>0){
                #配置当前仓库的快递企业
                $express_list = Db::name('centralize_warehouse_express')->where(['warehouse_id'=>$warehouse_id])->select();
                foreach($express_list as $k=>$v){
                    $ishave_freight = Db::name('centralize_freight_config')->where(['printer_id'=>$id,'express_id'=>$v['id'],'express_type'=>$v['express_type']])->find();
                    $express_list[$k]['ishave_freight'] = empty($ishave_freight)?0:1;
                }
            }

            $express = Db::name('centralize_express_product')->select();

            return view('',compact('data','id','warehouse_id','express','express_list'));
        }
    }

    //快递查询次数配置
    public function express_inquiry(Request $request){
        $dat = input();

        if($request->isAjax()){
            $data = Db::name('centralize_express_set')->find();
            if(empty($data)){
                Db::name('centralize_express_set')->insert([
                    'type'=>$dat['type'],
                    'type2'=>$dat['type']==2?$dat['type2']:'',
                    'times'=>1,
                    'printed_type'=>$dat['printed_type'],
                    'printed_type2'=>$dat['printed_type']==2?$dat['printed_type2']:'',
                    'printed_times'=>1
                ]);
            }else{
                Db::name('centralize_express_set')->update([
                    'type'=>$dat['type'],
                    'type2'=>$dat['type']==2?$dat['type2']:'',
                    'times'=>1,
                    'printed_type'=>$dat['printed_type'],
                    'printed_type2'=>$dat['printed_type']==2?$dat['printed_type2']:'',
                    'printed_times'=>1
                ]);
            }
            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = Db::name('centralize_express_set')->find();
            return view('',compact('data'));
        }
    }

    //线路管理（废弃，商家操作）
    public function line_list(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';

            $count = Db::name('centralize_lines')->where('name', 'like', '%'.$keyword.'%')->count();
            $rows = DB::name('centralize_lines')
                ->where('name', 'like', '%'.$keyword.'%')
                ->limit($limit)
                ->order('id desc')
                ->select();
            foreach($rows as $k=>$v){
                $rows[$k]['channel_name'] = Db::name('centralize_channel_list')->where(['id'=>$v['channel_id']])->find()['name'];
                $rows[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    #删除线路
    public function del_line(Request $request){
        $data = input();
        if($data['id']>0){
            $res = Db::name('centralize_lines')->where(['id'=>intval($data['id'])])->delete();
            if($res){
                return json(['code'=>0,'msg'=>'删除成功']);
            }
        }
    }
}