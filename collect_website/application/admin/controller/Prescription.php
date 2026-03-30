<?php
namespace app\admin\controller;

//use think\Controller;
use think\Request;
use think\Db;
use app\admin\controller;
use Excel5;
use PHPExcel;
use PHPExcel_IOFactory;
use think\Session;

class Prescription extends Auth
{
    public $config = [
        //数据库类型
        'type'     => 'mysql',
        //服务器地址
        'hostname' => 'rm-wz9mt4j79jrdh0p3z.mysql.rds.aliyuncs.com',
        //数据库名
        'database' => 'lrw',
        //用户名
        'username' => 'gogo198',
        //密码
        'password' => 'Gogo@198',
        //端口
        'hostport' => '3306',
        //表前缀
        'prefix'   => '',
    ];

    public function allergy_manage(Request $request)
    {
        $dat = input();
        $pid = isset($dat['pid'])?$dat['pid']:0;
        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $total = Db::name('prescription_allergy')->where(['pid' => $pid])->count();
            $data = Db::name('prescription_allergy')
                ->where(['pid' => $pid])
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {

            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        } else {
            return view('', ['title' => '','pid'=>$pid]);
        }
    }

    public function import_excel(Request $request){
        $dat = input();
        $type = isset($dat['type'])?$dat['type']:0;
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
                $allRow = $sheet->getHighestRow();

                for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                    if($type==1){
                        #过敏史
                        $pname = trim($PHPRead->getSheet($i)->getCell("A".$currentRow)->getValue());
                        $ishave = Db::name('prescription_allergy')->where(['name'=>$pname])->find();
                        if(empty($ishave['id'])){
                            $pid = Db::name('prescription_allergy')->insertGetId([
                                'name'=>$pname
                            ]);

                            $cname = trim($PHPRead->getSheet($i)->getCell("B".$currentRow)->getValue());
                            $ishave2 = Db::name('prescription_allergy')->where(['name'=>$cname])->find();
                            if(empty($ishave2['id'])){
                                Db::name('prescription_allergy')->insert([
                                    'pid'=>$pid,
                                    'name'=>$cname
                                ]);
                            }
                        }else{
                            $cname = trim($PHPRead->getSheet($i)->getCell("B".$currentRow)->getValue());
                            $ishave2 = Db::name('prescription_allergy')->where(['name'=>$cname])->find();
                            if(empty($ishave2['id'])){
                                Db::name('prescription_allergy')->insert([
                                    'pid'=>$ishave['id'],
                                    'name'=>$cname
                                ]);
                            }
                        }
                    }
                    elseif($type==2){
                        #处方用语
                        $pname = trim($PHPRead->getSheet($i)->getCell("A".$currentRow)->getValue());
                        $ishave = Db::name('prescription_language')->where(['name'=>$pname])->find();
                        if(empty($ishave['id'])){
                            $pid = Db::name('prescription_language')->insertGetId([
                                'name'=>$pname
                            ]);

                            $cname = trim($PHPRead->getSheet($i)->getCell("B".$currentRow)->getValue());
                            $ishave2 = Db::name('prescription_language')->where(['name'=>$cname])->find();
                            if(empty($ishave2['id'])){
                                Db::name('prescription_language')->insert([
                                    'pid'=>$pid,
                                    'name'=>$cname
                                ]);
                            }
                        }else{
                            $cname = trim($PHPRead->getSheet($i)->getCell("B".$currentRow)->getValue());
                            $ishave2 = Db::name('prescription_language')->where(['name'=>$cname])->find();
                            if(empty($ishave2['id'])){
                                Db::name('prescription_language')->insert([
                                    'pid'=>$ishave['id'],
                                    'name'=>$cname
                                ]);
                            }
                        }
                    }
                    elseif($type==3){
                        #药品剂型
                        $pname = trim($PHPRead->getSheet($i)->getCell("A".$currentRow)->getValue());
                        $ishave = Db::name('prescription_drug')->where(['name'=>$pname])->find();
                        if(empty($ishave['id'])){
                            $pid = Db::name('prescription_drug')->insertGetId([
                                'name'=>$pname
                            ]);
                        }
                    }
                }
            }

            return json(['code'=>0,'msg'=>'已导入']);
        }else{
            return view('',compact('type'));
        }
    }

    public function language_manage(Request $request)
    {
        $dat = input();
        $pid = isset($dat['pid'])?$dat['pid']:0;
        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $total = Db::name('prescription_language')->where(['pid' => $pid])->count();
            $data = Db::name('prescription_language')
                ->where(['pid' => $pid])
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {

            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        } else {
            return view('', ['title' => '','pid'=>$pid]);
        }
    }

    public function drug_manage(Request $request)
    {
        $dat = input();
        $pid = isset($dat['pid'])?$dat['pid']:0;
        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $total = Db::name('prescription_drug')->count();
            $data = Db::name('prescription_drug')
//                ->where(['pid' => $pid])
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {

            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        } else {
            return view('', ['title' => '','pid'=>$pid]);
        }
    }

    public function send_prescription(Request $request){
        $dat = input();

        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $conn = Db::connect($this->config);
            $total = $conn->name('user_prescription')->where('status=0 or status=1')->count();
            $data = $conn->name('user_prescription')
                ->where('status=0 or status=1')
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {
                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
                $item['name'] = $conn->name('patient')->where(['id'=>$item['patient_id']])->find()['name'];

                if($item['status']==0){
                    $item['status_name'] = '待分配';
                }elseif($item['status']==1){
                    $item['status_name'] = '已分配';
                }
            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        }else{
            return view('', ['title' => '']);
        }
    }

    public function done_prescription(Request $request){
        $dat = input();

        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $conn = Db::connect($this->config);
            $total = $conn->name('user_prescription')->where(['status'=>2])->count();
            $data = $conn->name('user_prescription')
                ->where(['status'=>2])
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {
                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
                $item['name'] = $conn->name('patient')->where(['id'=>$item['patient_id']])->find()['name'];
            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        }else{
            return view('', ['title' => '']);
        }
    }

    #处方详情
    public function prescription_detail(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        $conn = Db::connect($this->config);
        if ($request->isAjax()) {
            #修改为已分配未开具状态
            $conn->name('user_prescription')->where(['id'=>$id])->update([
                'status'=>1,
                'doctor_id'=>$dat['doctor_id']
            ]);

            #通知医生
            $user = Db::name('website_user')->where(['id'=>$dat['doctor_id']])->find();

            if(!empty($user['openid'])){
                #公众号通知
                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'处方待开具消息，请打开查看！',
                    'keyword1' => '处方待开具消息，请打开查看！',
                    'keyword2' => '已提交待分配',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '点击查看详情',
                    'url' => 'https://www.gogo198.cn/check_prescription?id='.$id,
                    'openid' => $user['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
                $res = httpRequest2('https://www.gogo198.cn/api/sendwechattemplatenotice.php', $post);
            }elseif(!empty($user['phone'])){
                #手机短信通知
                $post_data = [
                    'spid'=>'254560',
                    'password'=>'J6Dtc4HO',
                    'ac'=>'1069254560',
                    'mobiles'=>$user['phone'],
                    'content'=>'您有一则处方待开具消息，请打开链接进行操作：https://www.gogo198.cn/check_prescription?id='.$id.' 【GOGO】',
                ];
                $post_data = json_encode($post_data,true);
                $res = httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length:' . strlen($post_data),
                    'Cache-Control: no-cache',
                    'Pragma: no-cache'
                ));
            }

            return json(['code'=>0,'msg'=>'分配成功']);
        }else{

            $prescription = $conn->name('user_prescription')->where(['id'=>$id])->find();
            if($prescription['status']==2){
                $prescription['content'] = json_decode($prescription['content'],true);
            }

            $detail = $conn->name('patient')->where(['id'=>$prescription['patient_id']])->find();
            $detail['department'] = $conn->name('category')->where(['cat_id'=>$detail['department']])->find()['cat_name'];
            $detail['disease'] = $conn->name('category')->where(['cat_id'=>$detail['disease']])->find()['cat_name'];

            if($detail['is_allergy']==1){
                #有过敏史
                $detail['allergy_info'] = Db::name('prescription_allergy')->whereRaw('find_in_set(id,?)',[$detail['allergy_id']])->select();
                $detail['allergy'] = '';
                foreach($detail['allergy_info'] as $k=>$v){
                    $detail['allergy'] .= $v['name'].',';
                }
                $detail['allergy'] = rtrim($detail['allergy'],',');
            }else{
                $patient['allergy'] = '未发现';
            }

            #订单
            $order = Db::name('website_order_list')->where(['id'=>$prescription['order_id']])->find();
            $order['content'] = json_decode($order['content'],true);

            #商品
            $good = $conn->name('goods')->where(['goods_id'=>$order['content']['good_id']])->find();
            $good_unit = $conn->name('goods_sku')->where(['sku_id'=>$good['sku_id']])->find();
            $good_unit['sku_prices'] = json_decode($good_unit['sku_prices'],true);
            $good_unit['unit'] = Db::name('unit')->where(['code_value'=>$good_unit['sku_prices']['unit'][0]])->find()['code_name'];

            #科目
            $value = $conn->name('ssl_platform_value')->where(['cat_id'=>$good['cat_id1'],'cat_id1'=>$good['cat_id'],'cross_catId'=>$good['crossb_cate1']])->find();
            $value['drug'] = json_decode($value['drug'],true);
            $tag = '';
            if(!empty($value['drug']['value']['value2'])){
                if($value['drug']['value']['value2']==5){
                    $tag = '普通';
                }elseif($value['drug']['value']['value2']==6){
                    $tag = '儿科';
                }
            }else{
                if($value['drug']['value']['value']==7){
                    $tag = '麻';
                }elseif($value['drug']['value']['value']==8){
                    $tag = '精';
                }elseif($value['drug']['value']['value']==9){
                    $tag = '毒';
                }elseif($value['drug']['value']['value']==10){
                    $tag = '射';
                }elseif($value['drug']['value']['value']==11){
                    $tag = '外';
                }
            }

            #数量
            $unit['num_unit'] = Db::name('prescription_language')->where(['pid'=>16])->select();
            $unit['unit'] = Db::name('unit')->select();
            #间隔
            $unit['interval_unit'] = Db::name('prescription_language')->where(['pid'=>30])->select();
            #途径
            $unit['road_unit'] = Db::name('prescription_language')->where(['pid'=>49])->select();
            #服用时间
            $unit['eat_unit'] = Db::name('prescription_language')->where(['pid'=>44])->select();

            #医生信息
            if($prescription['status']<2){
                $doctor = Db::name('website_user')->where(['role'=>1])->select();
            }elseif($prescription['status']==2){
                #处方签署
                $doctor = Db::name('website_user')->where(['id'=>$prescription['doctor_id']])->find();
                $doctor['role_content'] = json_decode($doctor['role_content'],true);
                if($prescription['status']==2){
                    $image_data = file_get_contents("https://shop.gogo198.cn/".$doctor['role_content']['sign_file'][0]);
                    $doctor['role_content']['sign_file'][0] = "data:image/jpeg;base64,".base64_encode($image_data);
                }
            }


            return view('', compact('detail','prescription','id','tag','good','doctor','unit','good_unit','order'));
        }
    }

    #医生管理
    public function doctor_manage(Request $request){
        $dat = input();

        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $total = Db::name('website_user')->where(['role'=>1])->count();
            $data = Db::name('website_user')
                ->where(['role'=>1])
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {
                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        }else{
            return view('', ['title' => '']);
        }
    }

    #添加医生
    public function save_doctor(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;

        if ($request->isAjax()) {
            $custom_id = '';

            if($id==0){
                $res = Db::name('website_user')->insertGetId([
                    'custom_id'=>$custom_id,
                    'realname'=>trim($dat['realname']),
                    'phone'=>trim($dat['phone']),
                    'role'=>1,
                    'role_content'=>json_encode(['department'=>$dat['department'],'zige_file'=>$dat['zige_file'],'sign_file'=>$dat['sign_file']],true),
                    'createtime'=>time()
                ]);
            }else{
                $res = Db::name('website_user')->where(['id'=>$id])->update([
                    'custom_id'=>$custom_id,
                    'realname'=>trim($dat['realname']),
                    'phone'=>trim($dat['phone']),
                    'role_content'=>json_encode(['department'=>$dat['department'],'zige_file'=>$dat['zige_file'],'sign_file'=>$dat['sign_file']],true)
                ]);
            }

            if($res){
                if($id==0){
                    Db::name('website_user')->where('id',$res)->update(['custom_id'=>'9'.str_pad($res, 5, '0', STR_PAD_LEFT)]);
                }
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = ['realname'=>'','phone'=>'','role_content'=>['department'=>'','zige_file'=>[''],'sign_file'=>['']]];

            if($id>0){
                $data = Db::name('website_user')->where(['id'=>$id])->find();
                $data['role_content'] = json_decode($data['role_content'],true);
//                dd($data['role_content']);
            }
            return view('', compact('id','data'));
        }
    }
}