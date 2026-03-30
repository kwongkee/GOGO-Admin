<?php
namespace app\admin\controller;

use think\Db;
use think\Request;
use think\Response;
use Excel5;
use PHPExcel_IOFactory;

class Supplier extends Auth{
    //对接管理
    public function lists(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $map = array();
            $list = Db::name('supplier_job_list')->order($order)->limit($limit)->select();
            foreach ($list as $k => $v) {
                if($v['msg_type']==1){
                    $list[$k]['msg_type']='支持';
                }elseif($v['msg_type']==2){
                    $list[$k]['msg_type']='不支持';
                }
                $list[$k]['manage'] = '<button type="button" onclick="edit('."'编辑','".Url('admin/supplier/edit_lists')."'".','."'".$v['id']."'".')" class="btn btn-primary btn-xs" style="margin-right: 10px;">编辑</button>';
                $list[$k]['manage'] .= '<button type="button" onclick="del('."'".$v['id']."'".')" class="btn btn-danger btn-xs">删除</button>';
            }
            $total = Db::name('supplier_job_list')->where($map)->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }else{
            return view('supplier/lists');
        }
    }

    public function add_lists(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            if( Db::name('supplier_job_list')->insert($dat) )
            {
                return json(['status'=>1,'message'=>'新增成功']);
            }else{
                return json(['status'=>0,'message'=>'新增失败']);
            }
        }else{
            return view('supplier/add_lists');
        }
    }

    public function edit_lists(Request $request)
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            if( Db::name('supplier_job_list')->where('id',$data['id'])->update($data) )
            {
                return json(['status'=>1,'message'=>'修改成功']);
            }else{
                return json(['status'=>0,'message'=>'修改失败']);
            }
        }else{
            $info = Db::name('supplier_job_list')->where('id',input('id'))->find();
            return view("supplier/edit_lists",[
                'info' => $info
            ]);
        }
    }

    public function del_lists(Request $request)
    {
        $id = input('id');
        Db::name('supplier_job_list')->where('id',$id)->delete();
        return json(['status'=>1,'message'=>'删除成功']);
    }

    //平台仓库（列表）
    public function warehouse_list(Request $request){
        $dat = input();

        if ( request()->isPost() || request()->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;

            $data = Db::name('supplier_platform_warehouse_list')->limit($page, $limit)->order('id','desc')->select();
            foreach($data as $k=>$v){
                $data[$k]['country'] = Db::name('country_code')->where('code_value',$v['country'])->find()['code_name'];
            }
            $total = Db::name('supplier_platform_warehouse_list')->count();
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    //添加平台仓库
    public function warehouse_save(Request $request){
        $dat = input();
        $id = $dat['id'];
        if ( request()->isPost() || request()->isAjax()){
            if($id>0){
                unset($dat['id']);
                $res = Db::name('supplier_platform_warehouse_list')->where('id',$id)->update($dat);
            }else{
                $dat['createtime'] = time();
                $res = Db::name('supplier_platform_warehouse_list')->insert($dat);
            }
            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $country = Db::name('country_code')->select();
            $data = [];

            if(isset($dat['id'])){
                $data = Db::name('supplier_platform_warehouse_list')->where('id',$dat['id'])->find();
            }

            return view('',compact('country','data','id'));
        }
    }

    //删除平台仓库
    public function del_warehouse(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()){
            $res = Db::name('supplier_platform_warehouse_list')->where('id',$dat['id'])->delete();
            if($res){
                return json(['code'=>0,'msg'=>'删除成功']);
            }
        }
    }

    //供应管理（列表）
    public function index(Request $request){
        if ( request()->isPost() || request()->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $keyword = $request->get('search');
            $where = [];
            if(!empty($keyword)){
                $where['gnum|gItemNo'] = $keyword;
            }

            $total = Db::name('supplier_goods_list')->count();
            $data = Db::name('supplier_goods_list')->where($where)->limit($page, $limit)->order('id','desc')->select();
            foreach($data as $k=>$v){
                $data[$k]['user_name'] = Db::name('decl_user')->where('id',$v['uid'])->find()['user_name'];
                $data[$k]['reg_name'] = Db::name('supplier_trademark_list')->where('id',$v['trademark_id'])->find()['reg_name'];
                if(empty($v['status'])){
                    $data[$k]['status'] = '未处理';
                }elseif($v['status']==1){
                    $data[$k]['status'] = '已处理';
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else {
            return view('');
        }
    }

    public function info(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()){

        }else{
            //产品信息
            $data['ginfo'] = Db::name('supplier_goods_list')->where('id',$dat['id'])->find();
            $data['user'] = Db::name('decl_user')->where('id',$data['ginfo']['uid'])->find();
            $quality_file = json_decode($data['ginfo']['quality_file'],true);
            $cert_file = json_decode($data['ginfo']['cert_file'],true);
//            $data['ginfo']['sales_unit'] = Db::name('unit')->where('code_value',$data['ginfo']['sales_unit'])->find()['code_name'];
            $data['ginfo']['gCateIdOne'] = Db::name('jd_goods_category')->where('id',$data['ginfo']['gCateIdOne'])->find()['catName'];
            $data['ginfo']['gCateIdTwo'] = Db::name('jd_goods_category')->where('id',$data['ginfo']['gCateIdTwo'])->find()['catName'];
            $data['ginfo']['gCateIdThree'] = Db::name('jd_goods_category')->where('id',$data['ginfo']['gCateIdThree'])->find()['catName'];

            //商标信息
            $logo_file = $auth_file = $reg_file = $other_auth_file= '';
            if($data['ginfo']['trademark_id']>0){
                $data['trademark'] = Db::name('supplier_trademark_list')->where('id',$data['ginfo']['trademark_id'])->find();
                $data['trademark']['reg_language'] = Db::name('language')->where('code_value',$data['trademark']['reg_language'])->find()['code_name'];
                $data['trademark']['reg_country'] = Db::name('country_code')->where('code_value',$data['trademark']['reg_country'])->find()['code_name'];
                $data['trademark']['reg_category'] = Db::name('jd_register_category')->where('code_value',$data['trademark']['reg_category'])->find()['code_name'];
                $data['trademark']['reg_type'] = Db::name('jd_register_type')->where('code_value',$data['trademark']['reg_type'])->find()['code_name'];
                if($data['trademark']['business_type']==1){
                    $data['trademark']['business_type'] = '自主品牌';
                }elseif($data['trademark']['business_type']==2){
                    $data['trademark']['business_type'] = '代理品牌';
                }
                $logo_file = json_decode($data['trademark']['logo_file'],true);
                $auth_file = json_decode($data['trademark']['auth_file'],true);
                $reg_file = json_decode($data['trademark']['reg_file'],true);
                $other_auth_file = json_decode($data['trademark']['other_auth_file'],true);
            }

            //仓库信息
            $data['warehouse'] = Db::name('supplier_warehouse_list')->where('good_num',$data['ginfo']['gnum'])->select();
            foreach($data['warehouse'] as $k=>$v){
//                $data['warehouse'][$k]['country_id'] = Db::name('country_code')->where('code_value',$v['country_id'])->find()['code_name'];
                $data['warehouse'][$k]['origin_able_country'] = '';
                if($v['start_country']==1) {
                    $data['warehouse'][$k]['start_country'] = '中国';
                    $data['warehouse'][$k]['origin_able_country_in'] = explode(',',$v['origin_able_country_in']);
                    foreach($data['warehouse'][$k]['origin_able_country_in'] as $kk=>$vv){
                        if($vv==1){
                            $data['warehouse'][$k]['origin_able_country'].='内地,';
                        }elseif($vv==2){
                            $data['warehouse'][$k]['origin_able_country'].='香港,';
                        }elseif($vv==3){
                            $data['warehouse'][$k]['origin_able_country'].='澳门,';
                        }elseif($vv==4){
                            $data['warehouse'][$k]['origin_able_country'].='台湾,';
                        }
                    }
                }elseif($v['start_country']==2) {
                    $data['warehouse'][$k]['start_country'] = '海外';
                    $data['warehouse'][$k]['origin_able_country_out'] = explode(',',$v['origin_able_country_out']);
                    foreach($data['warehouse'][$k]['origin_able_country_out'] as $kk=>$vv){
                        if($vv==1){
                            $data['warehouse'][$k]['origin_able_country'].='亚洲,';
                        }elseif($vv==2){
                            $data['warehouse'][$k]['origin_able_country'].='欧洲,';
                        }elseif($vv==3){
                            $data['warehouse'][$k]['origin_able_country'].='美洲,';
                        }elseif($vv==4){
                            $data['warehouse'][$k]['origin_able_country'].='非洲,';
                        }elseif($vv==5){
                            $data['warehouse'][$k]['origin_able_country'].='大洋洲,';
                        }
                    }
                }

                //仓库id
                $data['warehouse'][$k]['able_country_in2'] = Db::name('supplier_platform_warehouse_list')->whereIn('id',$v['able_country_in'])->select();

                //买家收货地址
                $data['warehouse'][$k]['buyer_able_country2'] = '';
                $country2 = Db::name('country_code')->whereIn('code_value',$data['warehouse'][$k]['buyer_able_country'])->select();
                foreach($country2 as $k2=>$v2){
                    $data['warehouse'][$k]['buyer_able_country2'].=$v2['code_name'].',';
                }
            }

            //运费规则信息
            $data['freight'] = Db::name('supplier_freight_rules_list')->where('good_num',$data['ginfo']['gnum'])->select();
            foreach($data['freight'] as $k=>$v){
                if($data['freight'][$k]['rule_id']==1){
                    $data['freight'][$k]['rule_id'] = '电商包邮';
                }elseif($data['freight'][$k]['rule_id']==2) {
                    $data['freight'][$k]['rule_id'] = '工厂包邮';
                }

                $data['freight'][$k]['able_country'] = Db::name('country_code')->where('code_value',$data['freight'][$k]['able_country'])->find()['code_name'];
            }

            //商品详情信息
            $data['ginfo']['goods_info'] = json_decode($data['ginfo']['goods_info'],true);
            if($data['ginfo']['goods_info']['step_price']==1){
                $data['ginfo']['goods_info']['step_price']='不开启';
            }else{
                $data['ginfo']['goods_info']['step_price']='开启';
            }
            $data['ginfo']['goods_info']['goods_ladder_info'] = json_decode($data['ginfo']['goods_info']['goods_ladder_info'],true);
            $data['ginfo']['goods_info']['origin_country'] = Db::name('country_code')->where('code_value',$data['ginfo']['goods_info']['origin_country'])->find()['code_name'];
            $data['ginfo']['goods_info']['gunit'] = Db::name('unit')->where('code_value',$data['ginfo']['goods_info']['gunit'])->find()['code_name'];
            $data['ginfo']['goods_info']['std_unit'] = Db::name('unit')->where('code_value',$data['ginfo']['goods_info']['std_unit'])->find()['code_name'];
            $data['ginfo']['goods_info']['sec_unit'] = Db::name('unit')->where('code_value',$data['ginfo']['goods_info']['sec_unit'])->find()['code_name'];
            $data['ginfo']['goods_info']['curr_code'] = Db::name('currency')->where('code_value',$data['ginfo']['goods_info']['curr_code'])->find()['code_name'];


            return view('',compact('data','logo_file','reg_file','auth_file','quality_file','cert_file','other_auth_file'));
        }
    }

    public function update(Request $request){
        $dat = input();
        $res = Db::name('supplier_goods_list')->where('id',$dat['id'])->update(['status'=>1]);
        if($res){
            return json(['code'=>0,'message'=>'处理成功']);
        }
    }

    //订单管理（列表）
    public function order_list(Request $request){
        if ( request()->isPost() || request()->isAjax()) {
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $keyword = $request->get('search');
            $where = [];
            if (!empty($keyword)) {
                $where['orderno'] = $keyword;
            }
            $total = Db::name('supplier_order_list')->count();
            $data = Db::name('supplier_order_list')->where($where)->limit($page, $limit)->order('id','desc')->select();
            foreach($data as $k=>$v){
                $data[$k]['user_name'] = Db::name('decl_user')->where('id',$v['user_id'])->find()['user_name'];
                $data[$k]['currency'] = Db::name('currency')->where('code_value',$v['currency'])->find()['code_name'];
                $data[$k]['order_time'] = date('Y-m-d H:i:s',$v['order_time']);
                $data[$k]['pay_time'] = date('Y-m-d H:i:s',$v['pay_time']);
                if($v['status']==1){
                    $data[$k]['status'] = '待发货';
                }elseif($v['status']==2){
                    $data[$k]['status'] = '待收货';
                }elseif($v['status']==3){
                    $data[$k]['status'] = '已完成';
                }elseif($v['status']==4){
                    $data[$k]['status'] = '已取消';
                }elseif($v['status']==5){
                    $data[$k]['status'] = '处理中';
                }

                if($v['check_status']==0){
                    $data[$k]['check_status'] = '待确认';
                }elseif($v['check_status']==1){
                    $data[$k]['check_status'] = '确认订单';
                }elseif($v['check_status']==-1){
                    $data[$k]['check_status'] = '拒绝订单';
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    //获取商户配置的商品
    public function get_user_goods(Request $request){
        $dat = input();
        $goods = Db::name('supplier_goods_list')->where('uid',$dat['user_id'])->select();
        return json(['code'=>0,'list'=>$goods]);
    }

    //新增
    public function add_order(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            $dat['orderno'] = $this->createGogoOrderSn().'@'.trim($dat['orderno']);
            $dat['order_time'] = strtotime($dat['order_time']);
            $dat['pay_time'] = strtotime($dat['pay_time']);
            $g_info = [];
            foreach($dat['goods_id'] as $k=>$v){
                $g_info[$k] = [
                    'goods_id'=>$v
                    ,'goods_currency'=>$dat['goods_currency'][$k]
                    ,'goods_price'=>$dat['goods_price'][$k]
                    ,'goods_num'=>$dat['goods_num'][$k]
                ];
            }
            unset($dat['goods_id']);unset($dat['goods_currency']);unset($dat['goods_price']);unset($dat['goods_num']);
            $dat['goods_info'] = json_encode($g_info,true);
            $res = Db::name('supplier_order_list')->insert($dat);
            if($res){
                //通知供应商户
                $decl_user = Db::name('decl_user')->where('id',$dat['user_id'])->find();
                sendWechatMsg(json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'供应商您好！您有一张订单['.$dat['orderno'].']待发货，请在电脑端登录客户端进行处理！',
                    'keyword1' => '订单待发货',
                    'keyword2' => '订单待发货',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '登录后，进入供应商管理进行操作。',
                    'url' => '',
                    'openid' => $decl_user['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]));

                return json(['code'=>0,'msg'=>'新增成功']);
            }
        }else{
            $decl_user = Db::name('decl_user')->where('user_status',0)->whereNotNull('openid')->order('id','desc')->select();
            $currency = Db::name('currency')->select();

            return view('',compact('decl_user','currency'));
        }
    }

    //编辑订单
    public function edit_order(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            $dat['order_time'] = strtotime($dat['order_time']);
            $dat['pay_time'] = strtotime($dat['pay_time']);
            $g_info = [];
            foreach($dat['goods_id'] as $k=>$v){
                $g_info[$k] = [
                    'goods_id'=>$v
                    ,'goods_currency'=>$dat['goods_currency'][$k]
                    ,'goods_price'=>$dat['goods_price'][$k]
                    ,'goods_num'=>$dat['goods_num'][$k]
                ];
            }
            $id = $dat['id'];unset($dat['id']);
            unset($dat['goods_id']);unset($dat['goods_currency']);unset($dat['goods_price']);unset($dat['goods_num']);
            $dat['goods_info'] = json_encode($g_info,true);
            $res = Db::name('supplier_order_list')->where('id',$id)->update($dat);
            if($res){
                return json(['code'=>0,'msg'=>'编辑成功']);
            }
        }else{
            $decl_user = Db::name('decl_user')->where('user_status',0)->whereNotNull('openid')->order('id','desc')->select();
            $currency = Db::name('currency')->select();

            $data = Db::name('supplier_order_list')->where('id',$dat['id'])->find();
            $data['goods_info'] = json_decode($data['goods_info'],true);
            $data['order_time'] = date('Y-m-d H:i:s',$data['order_time']);
            $data['pay_time'] = date('Y-m-d H:i:s',$data['pay_time']);

            $goods_list = Db::name('supplier_goods_list')->where('uid',$data['user_id'])->select();

            $rules='';
            if($data['deliver_method']>0){
                $rules = Db::name('supplier_freight_rules_list a')
                    ->join('supplier_warehouse_list b','a.warehouse_id=b.id','left')
                    ->where('a.id',$data['deliver_method'])
                    ->field(['a.*','b.zn_address','b.able_country_in','b.buyer_able_country'])
                    ->find();

                if($rules['rule_id']==1){
                    $rules['rule_id'] = '电商包邮';
                }elseif($rules['rule_id']==2){
                    $rules['rule_id'] = '工厂包邮';
                }
                if(empty($rules['zn_address'])){
                    $rules['zn_address'] = '无';
                }

                $country = Db::name('country_code')->where('code_value',$rules['able_country'])->find();
                $rules['able_country'] = $country['code_name']?$country['code_name']:'无';

                //平台转运仓库
                if(!empty($rules['able_country_in'])){
                    $rules['able_country_in'] = explode(',',$rules['able_country_in']);
                    foreach($rules['able_country_in'] as $k=>$v){
                        $rules['able_country_in'][$k] = Db::name('supplier_platform_warehouse_list')->where('id',$v)->find();
                    }
                }

                //买家收货国地
                if(!empty($rules['buyer_able_country'])){
                    $rules['buyer_able_country'] = explode(',',$rules['buyer_able_country']);
                    foreach($rules['buyer_able_country'] as $k=>$v) {
                        $rules['buyer_able_country'][$k] = Db::name('country_code')->where('code_value',$v)->field(['code_name'])->find();
                    }
                }
            }elseif($data['deliver_method']==-1){
                $rules = '平台发货';
            }

            return view('',compact('decl_user','currency','data','goods_list','rules'));
        }
    }

    //生成企业唯一编号
    private function createGogoOrderSn()
    {
        $millisecond = round(explode(" ", microtime())[0] * 1000);
        $ordersn = 'GG' . date('Ymd', time()) . str_pad($millisecond, 3, '0', STR_PAD_RIGHT) . mt_rand(111,
                999) . mt_rand(111, 999);
        return $ordersn;
    }

    public function edit_order_status(Request $request){
        $dat = input();
        $id = $dat['id'];
        $user_id = $dat['user_id'];
        $orderno = $dat['orderno'];
        if ( request()->isPost() || request()->isAjax()) {
            $sta = $dat['status'];
            if($dat['status']==5){
                if(empty($dat['refund_currency']) || empty($dat['refund_money']) || empty($dat['apply_time']) || empty($dat['outbound_status'])){
                    return json(['code'=>-1,'msg'=>'请输入售后信息！']);
                }

                $dat['order_id'] = $dat['id'];
                unset($dat['orderno']);unset($dat['id']);
                $dat['refund_no'] = $this->createGogoOrderSn();
                $dat['createtime'] = time();
                $dat['apply_time'] = strtotime($dat['apply_time']);
                $dat['status'] = 0;

                //生成售后订单
                Db::name('supplier_aftersales_order_list')->insert($dat);
                $dat['status'] = $sta;
            }

            $res = Db::name('supplier_order_list')->where('id',$id)->update(['status'=>$dat['status']]);
            if($res){
                //通知供应商户
                $decl_user = Db::name('decl_user')->where('id',$dat['user_id'])->find();
                $status = '';
                switch($dat['status']){
                    case $dat['status']==1:
                        $status='待发货';
                        break;
                    case $dat['status']==2:
                        $status='待收货';
                        break;
                    case $dat['status']==3:
                        $status='已完成';
                        break;
                    case $dat['status']==4:
                        $status='已取消';
                        break;
                    case $dat['status']==5:
                        $status='退款处理中';
                        break;
                }
                sendWechatMsg(json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'供应商您好！您有一张订单['.$orderno.']'.$status.'，请在电脑端登录客户端进行查看！',
                    'keyword1' => '订单状态['.$status.']',
                    'keyword2' => '订单状态['.$status.']',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '登录后，进入供应商管理进行查看。',
                    'url' => '',
                    'openid' => $decl_user['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]));

                return json(['code'=>0,'msg'=>'修改成功，已通知商户！']);
            }
        }else{
            $currency = Db::name('currency')->select();
            $data = Db::name('supplier_order_list')->where('id',$dat['id'])->find();
            $data['goods_info'] = json_decode($data['goods_info'],true);
            $goods = [];
            foreach($data['goods_info'] as $k=>$v){
                $goods[$k] = Db::name('supplier_goods_list')->where('id',$v['goods_id'])->find();
            }
            $goods = json_encode($goods,true);
            return view('',compact('data','id','user_id','orderno','currency','goods'));
        }
    }

    //导入订单
    public function export_order(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {

        }else{
            return view('');
        }
    }
    //上传订单模板
    public function upload(Request $request)
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
        //第一个工作表
        $sheet = $PHPRead->getSheet(0);
        $allRow = $sheet->getHighestRow();
        for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
            array_push($data, [
                'user_id' => $PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue(),
                'orderno' => $this->createGogoOrderSn().'@'.$PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue(),
                'order_time' => strtotime($PHPRead->getActiveSheet()->getCell("C".$currentRow)->getValue()),
                'pay_time' => strtotime($PHPRead->getActiveSheet()->getCell("D".$currentRow)->getValue()),
                'currency' => $PHPRead->getActiveSheet()->getCell("E".$currentRow)->getValue(),
                'order_price' => $PHPRead->getActiveSheet()->getCell("F".$currentRow)->getValue(),
                'seller_remark' => $PHPRead->getActiveSheet()->getCell("G".$currentRow)->getValue(),
                'status' => $PHPRead->getActiveSheet()->getCell("H".$currentRow)->getValue(),
                'is_more_supplier' => $PHPRead->getActiveSheet()->getCell("I".$currentRow)->getValue(),
            ]);
        }

        //第二个工作表
        $sheet2 = $PHPRead->getSheet(1);
        $allRow2 = $sheet2->getHighestRow();
        $k = 0;
        for ($currentRow = 2; $currentRow <= $allRow2; $currentRow++) {
            $data[$k]['buyer_name'] = $PHPRead->getSheet(1)->getCell("A".$currentRow)->getValue();
            $data[$k]['buyer_address'] = $PHPRead->getSheet(1)->getCell("B".$currentRow)->getValue();
            $data[$k]['buyer_mobile'] = $PHPRead->getSheet(1)->getCell("C".$currentRow)->getValue();
            $data[$k]['buyer_tel'] = $PHPRead->getSheet(1)->getCell("D".$currentRow)->getValue();
            $data[$k]['buyer_remark'] = $PHPRead->getSheet(1)->getCell("E".$currentRow)->getValue();
            $data[$k]['buyer_email'] = $PHPRead->getSheet(1)->getCell("F".$currentRow)->getValue();
            $k++;
        }

        //第三个工作表
        $sheet3 = $PHPRead->getSheet(2);
        $allRow3 = $sheet3->getHighestRow();
        $goods_info = [];
        for ($currentRow = 2; $currentRow <= $allRow3; $currentRow++) {
            $gid = Db::name('supplier_goods_list')->where('gItemNo',$PHPRead->getSheet(2)->getCell("A".$currentRow)->getValue())->field(['id'])->find();
            array_push($goods_info,[
                'goods_id'=>$gid['id'],
                'goods_currency'=>$PHPRead->getSheet(2)->getCell("B".$currentRow)->getValue(),
                'goods_price'=>$PHPRead->getSheet(2)->getCell("C".$currentRow)->getValue(),
                'goods_num'=>$PHPRead->getSheet(2)->getCell("D".$currentRow)->getValue(),
            ]);
        }
        for($i=0;$i<count($data);$i++){
            $data[$i]['goods_info'] = json_encode($goods_info,true);
        }
        @unlink($fileName);

        foreach($data as $k=>$v){
            //1：先查询有无相同订单编号
            $ishave = Db::name('supplier_order_list')->where(['user_id'=>$v['user_id'],'orderno'=>$v['orderno']])->find();
            if(empty($ishave)){
                $res = Db::name('supplier_order_list')->insert([
                    'user_id'=>trim($v['user_id']),
                    'orderno'=>trim($v['orderno']),
                    'order_time'=>trim($v['order_time']),
                    'pay_time'=>trim($v['pay_time']),
                    'currency'=>trim($v['currency']),
                    'order_price'=>trim($v['order_price']),
                    'seller_remark'=>trim($v['seller_remark']),
                    'status'=>intval($v['status']),
                    'buyer_name'=>trim($v['buyer_name']),
                    'buyer_address'=>trim($v['buyer_address']),
                    'buyer_mobile'=>trim($v['buyer_mobile']),
                    'buyer_tel'=>trim($v['buyer_tel']),
                    'buyer_remark'=>trim($v['buyer_remark']),
                    'buyer_email'=>trim($v['buyer_email']),
                    'goods_info'=>$v['goods_info'],
                    'is_more_supplier'=>intval($v['is_more_supplier']),
                ]);
                if($res){
                    //通知供应商
                    $decl_user = Db::name('decl_user')->where('id',$v['user_id'])->find();
                    sendWechatMsg(json_encode([
                        'call'=>'confirmCollectionNotice',
                        'first' =>'供应商您好！您有一张订单['.$v['orderno'].']待发货，请在电脑端登录客户端进行处理！',
                        'keyword1' => '订单待发货',
                        'keyword2' => '订单待发货',
                        'keyword3' => date('Y-m-d H:i:s',time()),
                        'remark' => '登录后，进入供应商管理进行操作。',
                        'url' => '',
                        'openid' => $decl_user['openid'],
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]));

                    return json(['code'=>0,'message'=>'导入成功，已通知商户！']);
                }
            }else{
                return json(['code'=>0,'message'=>'已有相同订单编号，请勿重复导入！']);
            }
        }
    }

    //售后订单管理-列表
    public function aftersales_order_list(Request $request){
        if ( request()->isPost() || request()->isAjax()) {
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $keyword = $request->get('search');
            $where = [];
            if (!empty($keyword)) {
                $where['refund_no'] = $keyword;
            }
            $total = Db::name('supplier_aftersales_order_list')->count();
            $data = Db::name('supplier_aftersales_order_list')->where($where)->limit($page, $limit)->order('id','desc')->select();
            foreach($data as $k=>$v){
                $data[$k]['user_name'] = Db::name('decl_user')->where('id',$v['user_id'])->find()['user_name'];
                $data[$k]['refund_currency'] = Db::name('currency')->where('code_value',$v['refund_currency'])->find()['code_name'];
                $data[$k]['apply_time'] = date('Y-m-d H:i:s',$v['apply_time']);
                if($v['status']==0){
                    $data[$k]['status2'] = '待审核';
                }elseif($v['status']==1){
                    $data[$k]['status2'] = '待退款';
                }elseif($v['status']==2){
                    $data[$k]['status2'] = '退款成功';
                }elseif($v['status']==3){
                    $data[$k]['status2'] = '退款失败';
                }

                if($data[$k]['outbound_status']==1){
                    $data[$k]['outbound_status']='未出库';
                }elseif($data[$k]['outbound_status']==2){
                    $data[$k]['outbound_status']='已出库';
                }

                $order_info = Db::name('supplier_order_list')->where('id',$data[$k]['order_id'])->find();
                $data[$k]['order_id'] = $order_info['orderno'];
                $data[$k]['buyer_name'] = $order_info['buyer_name'];

            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    //售后订单详情
    public function edit_aftersales_order(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {

        }else {
            $aftersales_data = Db::name('supplier_aftersales_order_list')->where('id', $dat['id'])->find();
            $aftersales_data['refund_currency'] = Db::name('currency')->where('code_value', $aftersales_data['refund_currency'])->find()['code_name'];
            $aftersales_data['apply_time'] = date('Y-m-d H:i:s', $aftersales_data['apply_time']);
            switch ($aftersales_data['outbound_status']) {
                case 1:
                    $aftersales_data['outbound_status'] = '未出库';
                    break;
                case 2:
                    $aftersales_data['outbound_status'] = '已出库';
                    break;
            }
            if ($aftersales_data['status'] == 0) {
                $aftersales_data['status2'] = '待审核';
            } elseif ($aftersales_data['status'] == 1) {
                $aftersales_data['status2'] = '待退款';
            } elseif ($aftersales_data['status'] == 2) {
                $aftersales_data['status2'] = '退款成功';
            } elseif ($aftersales_data['status'] == 3) {
                $aftersales_data['status2'] = '退款失败';
            }

            if($aftersales_data['check_remark']==0){
                $aftersales_data['check_remark'] = '暂无';
            }elseif($aftersales_data['check_remark']==1){
                $aftersales_data['check_remark'] = '商品配送中，无法取消';
            }elseif($aftersales_data['check_remark']==2){
                $aftersales_data['check_remark'] = '商品已签收，无法取消';
            }elseif($aftersales_data['check_remark']==3){
                $aftersales_data['check_remark'] = '国际站保税区订单，已报关';
            }elseif($aftersales_data['check_remark']==4){
                $aftersales_data['check_remark'] = '已电话沟通客户，客户同意签收商品';
            }elseif($aftersales_data['check_remark']==5){
                $aftersales_data['check_remark'] = '其他';
            }

            $decl_user = Db::name('decl_user')->where('id',$aftersales_data['user_id'])->find();

            $data = Db::name('supplier_order_list')->where('id',$aftersales_data['order_id'])->find();
            $data['pay_time'] = date('Y-m-d H:i:s',$data['pay_time']);
            $data['order_time'] = date('Y-m-d H:i:s',$data['order_time']);
            $data['currency'] = Db::name('currency')->where('code_value',$data['currency'])->find()['code_name'];

            if($data['status']==1){
                $data['status']='待发货';
            }elseif($data['status']==2){
                $data['status']='待收货';
            }elseif($data['status']==3){
                $data['status']='已完成';
            }elseif($data['status']==4){
                $data['status']='已取消';
            }elseif($data['status']==5){
                $data['status']='处理中';
            }

            //售后商品信息
            $goods_info = [];
            $aftersales_data['goods_id'] =explode(',',$aftersales_data['goods_id']);
            $data['goods_info'] = json_decode($data['goods_info'],true);
            foreach($aftersales_data['goods_id'] as $k=>$v){
                if($v==$data['goods_info'][$k]['goods_id']){
                    $goods = Db::name('supplier_goods_list')->where('id',$v)->find();
                    $goods_info[$k]['goods_id'] = $goods['gZnName']."(".$goods['gEnName'].")";
                    $goods_info[$k]['goods_price'] = $data['goods_info'][$k]['goods_price'];
                    $goods_info[$k]['goods_num'] = $data['goods_info'][$k]['goods_num'];
                }
            }

            $rules='';
            if($data['deliver_method']>0){
                $rules = Db::name('supplier_freight_rules_list a')
                    ->join('supplier_warehouse_list b','a.warehouse_id=b.id','left')
                    ->where('a.id',$data['deliver_method'])
                    ->field(['a.*','b.zn_address','b.able_country_in','b.buyer_able_country'])
                    ->find();

                if($rules['rule_id']==1){
                    $rules['rule_id'] = '电商包邮';
                }elseif($rules['rule_id']==2){
                    $rules['rule_id'] = '工厂包邮';
                }
                if(empty($rules['zn_address'])){
                    $rules['zn_address'] = '无';
                }

                $country = Db::name('country_code')->where('code_value',$rules['able_country'])->find();
                $rules['able_country'] = $country['code_name']?$country['code_name']:'无';

                //平台转运仓库
                if(!empty($rules['able_country_in'])){
                    $rules['able_country_in'] = explode(',',$rules['able_country_in']);
                    foreach($rules['able_country_in'] as $k=>$v){
                        $rules['able_country_in'][$k] = Db::name('supplier_platform_warehouse_list')->where('id',$v)->find();
                    }
                }

                //买家收货国地
                if(!empty($rules['buyer_able_country'])){
                    $rules['buyer_able_country'] = explode(',',$rules['buyer_able_country']);
                    foreach($rules['buyer_able_country'] as $k=>$v) {
                        $rules['buyer_able_country'][$k] = Db::name('country_code')->where('code_value',$v)->field(['code_name'])->find();
                    }
                }
            }elseif($data['deliver_method']==-1){
                $rules = '平台发货';
            }

            return view('',compact('decl_user','data','goods_info','rules','aftersales_data'));
        }
    }

    //修改售后订单状态
    public function edit_aftersales_order_status(Request $request){
        $dat = input();
        $id = $dat['id'];
        $user_id = $dat['user_id'];
        $refund_no = $dat['refund_no'];
        if ( request()->isPost() || request()->isAjax()) {
            $opera = '';
            if($dat['status']==2){
                $opera='退款成功！';
            }elseif($dat['status']==3){
                $opera='退款失败！';
            }
            $res = Db::name('supplier_aftersales_order_list')->where('id',$id)->update(['status'=>$dat['status']]);
            if($res){
                //修改订单状态为已取消
                $aftersales_order = Db::name('supplier_aftersales_order_list')->where('id',$id)->find();
                if($dat['status']==2){
                    Db::name('supplier_order_list')->where('id',$aftersales_order['order_id'])->update(['status'=>4]);
                }

                //通知供应商户
                $decl_user = Db::name('decl_user')->where('id',$dat['user_id'])->find();
                sendWechatMsg(json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'供应商您好！您有一张售后订单订单['.$refund_no.']'.$opera.'，请在电脑端登录客户端进行查看！',
                    'keyword1' => '订单状态['.$opera.']',
                    'keyword2' => '订单状态['.$opera.']',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '登录后，进入供应商管理进行查看。',
                    'url' => '',
                    'openid' => $decl_user['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]));

                return json(['code'=>0,'msg'=>'修改成功，已通知商户！']);
            }
        }else{
            $aftersales_data = Db::name('supplier_aftersales_order_list')->where('id',$id)->find();
            return view('',compact('aftersales_data','user_id','refund_no','id'));
        }
    }
}