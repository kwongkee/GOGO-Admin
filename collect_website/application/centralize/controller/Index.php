<?php
/**
 * 集运系统后台管理（基础配置+集运营销）
 * 2022-07-05
 */
namespace app\centralize\controller;

use think\Controller;
use think\Db;
use think\Request;
use Excel5;
use PHPExcel_IOFactory;
use PHPExcel;

class Index extends  Controller{

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

    //轮播图管理
    public function rotation(Request $request){
        if ( request()->isPost() || request()->isAjax()) {
            $dat = input();
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;

            $total = DB::name('centralize_rotate_chart')->count();
            $data = DB::name('centralize_rotate_chart')
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach ($data as &$item) {
                if($item['type']==1){
                    $item['type'] = '集运首页';
                }elseif($item['type']==2){
                    $item['type'] = '集运商城首页';
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    //保存&编辑
    public function rotation_save(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if ( request()->isPost() || request()->isAjax()) {
            if($id>0){
                $res = Db::name('centralize_rotate_chart')->where('id',$id)->update([
                    'title'=>trim($dat['title']),
                    'type'=>intval($dat['type']),
                    'img'=>$dat['img_file'][0],
                    'content'=>json_encode($dat['editorValue'],true),
                ]);
            }else{
                $res = Db::name('centralize_rotate_chart')->insert([
                    'title'=>trim($dat['title']),
                    'type'=>intval($dat['type']),
                    'img'=>$dat['img_file'][0],
                    'content'=>json_encode($dat['editorValue'],true),
                ]);
            }
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = ['title'=>'','type'=>0,];
            if($id>0){
                $data = Db::name('centralize_rotate_chart')->where('id',$id)->find();
                $data['content'] = json_decode($data['content'],true);
            }
            return view('',compact('id','data'));
        }
    }

    public function rotation_del(Request $request){
        $dat = input();
        if(isset($dat['id'])){
            $res = Db::name('centralize_rotate_chart')->where('id',$dat['id'])->delete();
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }
    }

    public function notice(Request $request){
        if ( request()->isPost() || request()->isAjax()) {
            $dat = input();
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;

            $total = DB::name('centralize_notice')->count();
            $data = DB::name('centralize_notice')
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    public function notice_save(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if ( request()->isPost() || request()->isAjax()) {
            if($id>0){
                $res = Db::name('centralize_notice')->where('id',$id)->update([
                    'title'=>trim($dat['title']),
                    'content'=>json_encode($dat['editorValue'],true),
                ]);
            }else{
                $res = Db::name('centralize_notice')->insert([
                    'title'=>trim($dat['title']),
                    'content'=>json_encode($dat['editorValue'],true),
                ]);
            }
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = ['title'=>''];
            if($id>0){
                $data = Db::name('centralize_notice')->where('id',$id)->find();
                $data['content'] = json_decode($data['content'],true);
            }
            return view('',compact(['id','data']));
        }
    }

    public function notice_del(Request $request){
        $dat = input();
        if(isset($dat['id'])){
            $res = Db::name('centralize_notice')->where('id',$dat['id'])->delete();
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }
    }

    public function embargo(Request $request){
        $data = input();
        if($request->isAjax()){
            $res = Db::name('centralize_system_setting')->where('type',1)->update(['content'=>json_encode($data['editorValue'],true)]);
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = Db::name('centralize_system_setting')->where('type',1)->find();
            $data['content'] = json_decode($data['content'],true);
            return view('',compact(['data']));
        }
    }

    public function transport_teaching(Request $request){
        $data = input();
        if($request->isAjax()){
            $res = Db::name('centralize_system_setting')->where('type',2)->update(['content'=>json_encode($data['editorValue'],true)]);
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = Db::name('centralize_system_setting')->where('type',2)->find();
            $data['content'] = json_decode($data['content'],true);
            return view('',compact(['data']));
        }
    }

    public function user_agreement(Request $request){
        $data = input();
        if($request->isAjax()){
            $res = Db::name('centralize_system_setting')->where('type',3)->update(['content'=>json_encode($data['editorValue'],true)]);
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = Db::name('centralize_system_setting')->where('type',3)->find();
            $data['content'] = json_decode($data['content'],true);
            return view('',compact(['data']));
        }
    }

    public function consolidation_agreement(Request $request){
        $data = input();
        if($request->isAjax()){
            $res = Db::name('centralize_system_setting')->where('type',4)->update(['content'=>json_encode($data['editorValue'],true)]);
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = Db::name('centralize_system_setting')->where('type',4)->find();
            $data['content'] = json_decode($data['content'],true);
            return view('',compact(['data']));
        }
    }

    public function instruction_order(Request $request){
        $data = input();
        if($request->isAjax()){
            $res = Db::name('centralize_system_setting')->where('type',5)->update(['content'=>json_encode($data['editorValue'],true)]);
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = Db::name('centralize_system_setting')->where('type',5)->find();
            $data['content'] = json_decode($data['content'],true);
            return view('',compact(['data']));
        }
    }

    public function about(Request $request){
        $data = input();
        if($request->isAjax()){
            $res = Db::name('centralize_system_setting')->where('type',6)->update(['content'=>json_encode($data['editorValue'],true)]);
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = Db::name('centralize_system_setting')->where('type',6)->find();
            $data['content'] = json_decode($data['content'],true);
            return view('',compact(['data']));
        }
    }

    public function news_type(Request $request){
        $dat = input();
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;

            $total = DB::name('centralize_news_category')->count();
            $data = DB::name('centralize_news_category')
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    public function news_type_save(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if ( request()->isPost() || request()->isAjax()) {
            if($id>0){
                $res = Db::name('centralize_news_category')->where('id',$id)->update([
                    'name'=>trim($dat['name']),
                ]);
            }else{
                $res = Db::name('centralize_news_category')->insert([
                    'name'=>trim($dat['name']),
                ]);
            }
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = ['name'=>''];
            if($id>0){
                $data = Db::name('centralize_news_category')->where('id',$id)->find();
            }
            return view('',compact(['id','data']));
        }
    }

    public function news_type_del(Request $request){
        $dat = input();
        if(isset($dat['id'])){
            $res = Db::name('centralize_news_category')->where('id',$dat['id'])->delete();
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }
    }

    public function news(Request $request){
        $dat = input();
        $pid=intval($dat['pid']);
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');
            $where = [];
            if(!empty($search)){
                $where['title'] = ['like','%'.$search.'%'];
            }
            $where['category_id'] = $pid;
            $total = DB::name('centralize_news')->where($where)->count();
            $data = DB::name('centralize_news')
                ->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach($data as $k=>$v){
                $data[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('',compact('pid'));
        }
    }

    public function news_save(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $pid = isset($dat['pid'])?$dat['pid']:0;
        if ( request()->isPost() || request()->isAjax()) {
            if($id>0){
                $res = Db::name('centralize_news')->where('id',$id)->update([
                    'title'=>trim($dat['title']),
                    'category_id'=>intval($dat['category_id']),
//                    'img'=>$dat['img_file'][0],
                    'content'=>json_encode($dat['editorValue'],true),
                ]);
            }else{
                $res = Db::name('centralize_news')->insert([
                    'title'=>trim($dat['title']),
                    'category_id'=>intval($dat['category_id']),
//                    'img'=>$dat['img_file'][0],
                    'content'=>json_encode($dat['editorValue'],true),
                    'createtime'=>time()
                ]);
            }
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = ['title'=>'','category_id'=>$pid];
            if($id>0){
                $data = Db::name('centralize_news')->where('id',$id)->find();
                $data['content'] = json_decode($data['content'],true);
            }
            $news_category = Db::name('centralize_news_category')->order('id','desc')->select();
            return view('',compact('id','data','news_category'));
        }
    }

    public function news_del(Request $request){
        $dat = input();
        if(isset($dat['id'])){
            $res = Db::name('centralize_news')->where('id',$dat['id'])->delete();
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }
    }

    public function warehouse(Request $request){
        $dat = input();
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');
            $where = [];
            if(!empty($search)){
                $where['warehouse_name'] = ['like','%'.$search.'%'];
            }
            $where['uid'] = 0;

            $total = DB::name('centralize_warehouse_list')->where($where)->count();
            $data = DB::name('centralize_warehouse_list')
                ->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach($data as $k=>$v){
                $data[$k]['country_code'] = Db::name('country_code')->where(['code_value'=>$v['country_code']])->find()['code_name'];
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    public function warehouse_save(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if ( request()->isPost() || request()->isAjax()) {
            unset($dat['id']);
            $ins_data = $dat;

            $ins_data['area_code'] = explode('__',$ins_data['area_code'])[0];
            $ins_data['postal_code'] = json_encode($ins_data['postal_code'],true);
            #仓库所在国家内的区域
            $area_codes = [];
            if(isset($ins_data['area1'])){$area_codes = array_merge($area_codes,['area1'=>$ins_data['area1']]);unset($ins_data['area1']);}
            if(isset($ins_data['area2'])){$area_codes = array_merge($area_codes,['area2'=>$ins_data['area2']]);unset($ins_data['area2']);}
            if(isset($ins_data['area3'])){$area_codes = array_merge($area_codes,['area3'=>$ins_data['area3']]);unset($ins_data['area3']);}
            if(isset($ins_data['area4'])){$area_codes = array_merge($area_codes,['area4'=>$ins_data['area4']]);unset($ins_data['area4']);}
            $ins_data['area_codes'] = json_encode($area_codes,true);

            if(empty($id)){
                $ins_data['createtime'] = time();
                $ins_data['uid'] = 0;
                $res = Db::name('centralize_warehouse_list')->insert($ins_data);
            }else{
                $res = Db::name('centralize_warehouse_list')->where(['id'=>$id,'uid'=>0])->update($ins_data);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $country_code = Db::name('centralize_diycountry_content')->where(['pid'=>5])->select();
            $area_code = Db::name('centralize_diycountry_content')->where(['pid'=>7])->select();

            $data = ['uid'=>0,'warehouse_name'=>'','warehouse_code'=>'','name'=>'','area_code'=>162,'mobile'=>'','postal_code'=>'','country_code'=>'','province_code'=>'','city_code'=>'','address1'=>'','addresss'=>'','status'=>0];
            if($id>0){
                $data = Db::name('centralize_warehouse_list')->where('id',$id)->find();

                if(!empty($data['addresss'])){
                    $data['addresss'] = json_decode($data['addresss'],true);
                }

                if(!empty($data['postal_code'])){
                    $suoxie = Db::name('centralize_diycountry_content')->where(['pid'=>5,'id'=>$data['country_code']])->find()['param5'];
                    $data['origin_postal_code'] = Db::name('centralize_diycountry_content')->where(['pid'=>4,'param1'=>$suoxie])->find()['param3'];
                    $data['postal_code'] = json_decode($data['postal_code'],true);
                }

                if(!empty($data['area_codes'])){
                    $data['area_codes'] = json_decode($data['area_codes'],true);
                    $data['area_info'] = '';
                    foreach($data['area_codes'] as $k=>$v){
                        if($k=='area1'){
                            $area = Db::name('centralize_adminstrative_area')->where(['id'=>$v])->field('code_name')->find()['code_name'];
                            $data['area_info'] = $area;
                        }

                        if($k=='area2'){
                            $area = Db::name('centralize_adminstrative_area')->where(['id'=>$v])->field('code_name')->find()['code_name'];
                            $data['area_info'] .= '-'.$area;
                        }

                        if($k=='area3'){
                            $area = Db::name('centralize_adminstrative_area')->where(['id'=>$v])->field('code_name')->find()['code_name'];
                            $data['area_info'] .= '-'.$area;
                        }

                        if($k=='area4'){
                            $area = Db::name('centralize_adminstrative_area')->where(['id'=>$v])->field('code_name')->find()['code_name'];
                            $data['area_info'] .= '-'.$area;
                        }
                    }
                }
            }

            return view('',compact('id','data','country_code','area_code'));
        }
    }

    public function get_country_info(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        #国家信息
        $country = Db::name('centralize_diycountry_content')->where(['id'=>$id])->find();

        #获取邮政编码格式
        $data['rule'] = Db::name('centralize_diycountry_content')->where(['pid'=>4,'param1'=>$country['param5']])->find();

        #获取当前国家的行政区域
        $data['addr'] = Db::name('centralize_adminstrative_area')->where(['country_id'=>$id,'pid'=>0])->field('id,code_name')->select();

        return json(['code'=>0,'list'=>$data]);
    }

    public function get_area_info(Request $request){
        $dat = input();
        $pid = intval($dat['id']);

        $addr = Db::name('centralize_adminstrative_area')->where(['pid'=>$pid])->field('id,code_name')->select();
        return json(['code'=>0,'list'=>$addr]);
    }

    public function warehouse_del(Request $request){
        $dat = input();
        if(isset($dat['id'])){
            $res = Db::name('centralize_warehouse_list')->where('id',$dat['id'])->delete();
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }
    }

    public function integral_rule(Request $request){
        $data = input();
        if($request->isAjax()){
            $data['content'] = ['register_send_points'=>intval($data['register_send_points']),'complete_centralize_order_points'=>intval($data['complete_centralize_order_points']),'promotion_offline_register_points'=>intval($data['promotion_offline_register_points'])];
            $res = Db::name('centralize_system_setting')->where('type',7)->update(['content'=>json_encode($data['content'],true)]);
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = Db::name('centralize_system_setting')->where('type',7)->find();
            if(!empty($data['content'])){
                $data['content'] = json_decode($data['content'],true);
            }else{
                $data['content'] = ['register_send_points'=>'','complete_centralize_order_points'=>'','promotion_offline_register_points'=>''];
            }

            return view('',compact(['data']));
        }
    }

    public function extra_value_line(Request $request){
        $dat = input();
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');
            $where = [];
            if(!empty($search)){
                $where['name'] = ['like','%'.$search.'%'];
            }

            $total = DB::name('centralize_extra_value_line')->where($where)->count();
            $data = DB::name('centralize_extra_value_line')
                ->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();
            foreach($data as $k=>$v){
                $data[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    public function extra_value_line_save(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if ( request()->isPost() || request()->isAjax()) {
            $arr = [];
            foreach($dat['service_name'] as $k=>$v){
                array_push($arr,['service_name'=>$v,'service_remark'=>$dat['service_remark'][$k],'service_fee'=>$dat['service_fee'][$k]]);
            }

            $dat['value_add_services'] = $arr;
            if($id>0){
                $res = Db::name('centralize_extra_value_line')->where('id',$id)->update([
                    'img'=>$dat['img_file'][0],
                    'name'=>trim($dat['name']),
                    'country_code'=>$dat['country_code'],
                    'bill_mode'=>trim($dat['bill_mode']),
                    'expect_time_limit'=>trim($dat['expect_time_limit']),
                    'first_kg'=>trim($dat['first_kg']),
                    'first_weight'=>trim($dat['first_weight']),
                    'continue_kg'=>trim($dat['continue_kg']),
                    'continue_weight'=>trim($dat['continue_weight']),
                    'accept_info'=>trim($dat['accept_info']),
                    'value_add_services'=>json_encode($dat['value_add_services'],true),
                    'content'=>json_encode($dat['editorValue'],true),
                ]);
            }else{
                $res = Db::name('centralize_extra_value_line')->insert([
                    'img'=>$dat['img_file'][0],
                    'name'=>trim($dat['name']),
                    'country_code'=>$dat['country_code'],
                    'bill_mode'=>trim($dat['bill_mode']),
                    'expect_time_limit'=>trim($dat['expect_time_limit']),
                    'first_kg'=>trim($dat['first_kg']),
                    'first_weight'=>trim($dat['first_weight']),
                    'continue_kg'=>trim($dat['continue_kg']),
                    'continue_weight'=>trim($dat['continue_weight']),
                    'accept_info'=>trim($dat['accept_info']),
                    'value_add_services'=>json_encode($dat['value_add_services'],true),
                    'content'=>json_encode($dat['editorValue'],true),
                    'createtime'=>time()
                ]);
            }
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = ['name'=>'','bill_mode'=>'','expect_time_limit'=>'','first_kg'=>'','first_weight'=>'','continue_kg'=>'','continue_weight'=>'','value_add_services'=>'','accept_info'=>'','country_code'=>''];
            if($id>0){
                $data = Db::name('centralize_extra_value_line')->where('id',$id)->find();
                $data['value_add_services'] = json_decode($data['value_add_services'],true);
                $data['content'] = json_decode($data['content'],true);
            }
            $goods_value = Db::name('centralize_goods_value')->select();
            $goods_value = json_encode($goods_value,true);

            $country_code = Db::name('country_code')->select();

            return view('',compact('id','data','goods_value','country_code'));
        }
    }

    public function complaint(Request $request){
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;

            $total = DB::name('centralize_complaint_list')->count();
            $data = DB::name('centralize_complaint_list')
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            if(!empty($data)){
                foreach($data as $k=>$v){
                    $data[$k]['name'] = Db::name('centralize_user')->where('id',$v['user_id'])->find()['name'];
                    $data[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
                }
            }

            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    public function complaint_info(Request $request){
        $dat = input();
        $data = Db::name('centralize_complaint_list')->where('id',$dat['id'])->find();
        $data['name'] = Db::name('centralize_user')->where('id',$data['user_id'])->find()['name'];
        if(!empty($data['img'])){
            $data['img'] = json_decode($data['img'],true);
        }

        return view('',compact('data'));
    }

    public function complaint_del(Request $request){
        $dat = input();
        if(isset($dat['id'])){
            $res = Db::name('centralize_complaint_list')->where('id',$dat['id'])->delete();
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }
    }

    public function coupon(Request $request){
        $dat = input();
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');
            $where = [];
            if(!empty($search)){
                $where['coupon_name'] = ['like','%'.$search.'%'];
            }

            $total = DB::name('centralize_coupon_list')->where($where)->count();
            $data = DB::name('centralize_coupon_list')
                ->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach($data as $k=>$v){
//                if($v['scope']==1){
//                    $data[$k]['scope'] = '集运订单';
//                }elseif($v['scope']==2){
//                    $data[$k]['scope'] = '商城订单';
//                }

                if($v['status']==1){
                    $data[$k]['status'] = '上架';
                }elseif($v['status']==2){
                    $data[$k]['status'] = '下架';
                }

                $data[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    public function coupon_save(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){
            if($id>0){
                $res = Db::name('centralize_coupon_list')->where('id',$id)->update([
                    'coupon_name'=>trim($dat['coupon_name']),
                    'price_value'=>trim($dat['price_value']),
                    'valid_date'=>trim($dat['valid_date']),
                    'require_points'=>trim($dat['require_points']),
//                    'scope'=>intval($dat['scope']),
                    'remark'=>trim($dat['remark']),
                    'full_minus_price'=>trim($dat['full_minus_price']),
                    'status'=>intval($dat['status']),
                ]);
            }else{
                $res = Db::name('centralize_coupon_list')->insert([
                    'coupon_name'=>trim($dat['coupon_name']),
                    'price_value'=>trim($dat['price_value']),
                    'valid_date'=>trim($dat['valid_date']),
                    'require_points'=>trim($dat['require_points']),
//                    'scope'=>intval($dat['scope']),
                    'remark'=>trim($dat['remark']),
                    'full_minus_price'=>trim($dat['full_minus_price']),
                    'status'=>intval($dat['status']),
                    'createtime'=>time()
                ]);
            }
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = ['coupon_name'=>'','price_value'=>'','valid_date'=>'','require_points'=>'','scope'=>'','remark'=>'','status'=>'','full_minus_price'=>''];
            if($id>0){
                $data = Db::name('centralize_coupon_list')->where('id',$id)->find();
            }
            return view('',compact('id','data'));
        }
    }

    public function coupon_del(Request $request){
        $dat = input();
        if(isset($dat['id'])){
            $res = Db::name('centralize_coupon_list')->where('id',$dat['id'])->delete();
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }
    }

    #余额充值配置
    public function balance(Request $request){
        $dat = input();
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
//            $search = $request->get('search');
//            $where = [];
//            if(!empty($search)){
//                $where['coupon_name'] = ['like','%'.$search.'%'];
//            }

            $total = DB::name('centralize_balance_list')->count();
            $data = DB::name('centralize_balance_list')
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('',compact(''));
        }
    }
    public function balance_save(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){
            if($id>0){
                $res = Db::name('centralize_balance_list')->where('id',$id)->update([
                    'recharge_money'=>trim($dat['recharge_money']),
                    'gift_money'=>trim($dat['gift_money']),
                ]);
            }else{
                $res = Db::name('centralize_balance_list')->insert([
                    'recharge_money'=>trim($dat['recharge_money']),
                    'gift_money'=>trim($dat['gift_money']),
                ]);
            }
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = ['recharge_money'=>'','gift_money'=>''];
            if($id>0){
                $data = Db::name('centralize_balance_list')->where('id',$id)->find();
            }
            return view('',compact('id','data'));
        }
    }

    public function balance_del(Request $request){
        $dat = input();
        if(isset($dat['id'])){
            $res = Db::name('centralize_balance_list')->where('id',$dat['id'])->delete();
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }
    }

    public function commission(Request $request){
        $data = input();
        if($request->isAjax()){
            $data['content'] = ['volume_of_trade'=>trim($data['volume_of_trade']),'commission_amount'=>trim($data['commission_amount'])];
            $res = Db::name('centralize_system_setting')->where('type',8)->update(['content'=>json_encode($data['content'],true)]);
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = Db::name('centralize_system_setting')->where('type',8)->find();
            if(!empty($data['content'])){
                $data['content'] = json_decode($data['content'],true);
            }else{
                $data['content'] = ['volume_of_trade'=>'','commission_amount'=>''];
            }

            return view('',compact(['data']));
        }
    }

    public function pay_param(Request $request){
        $data = input();
        if($request->isAjax()){
            $data['content'] = ['wechat_appid'=>trim($data['wechat_appid']),'wechat_mch_id'=>trim($data['wechat_mch_id']),'wechat_api_key'=>trim($data['wechat_api_key']),'wechat_cert_file'=>trim($data['wechat_cert_file']),'wechat_key_file'=>trim($data['wechat_key_file']),'wechat_root_file'=>trim($data['wechat_root_file']),'fps_mch_id'=>trim($data['fps_mch_id']),'fps_pwd'=>trim($data['fps_pwd']),'fps_pay_secret'=>trim($data['fps_pay_secret']),'fps_currency'=>trim($data['fps_currency']),'fps_rate'=>trim($data['fps_rate']),];
            $res = Db::name('centralize_system_setting')->where('type',9)->update(['content'=>json_encode($data['content'],true)]);
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = Db::name('centralize_system_setting')->where('type',9)->find();
            if(!empty($data['content'])){
                $data['content'] = json_decode($data['content'],true);
            }else{
                $data['content'] = ['wechat_appid'=>'','wechat_mch_id'=>'','wechat_api_key'=>'','wechat_cert_file'=>'','wechat_key_file'=>'','wechat_root_file'=>'','fps_mch_id'=>'','fps_pwd'=>'','fps_pay_secret'=>'','fps_currency'=>'','fps_rate'=>''];
            }

            return view('',compact(['data']));
        }
    }

    public function pick_up_point(Request $request){
        $dat = input();
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');
            $where = [];
            if(!empty($search)){
                $where['point_name'] = ['like','%'.$search.'%'];
            }
            $total = DB::name('centralize_self_lift_point')->where($where)->count();
            $data = Db::name('centralize_self_lift_point')
                ->where($where)
                ->limit($page,$limit)
                ->order('createtime', 'desc')
                ->select();

            if(!empty($data)){
                foreach($data as $k=>&$v){
                    $v['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
                    if($v['status']==1){
                        $v['status']='上架';
                    }elseif($v['status']==2){
                        $v['status']='下架';
                    }
                    $v['point_country_code'] = Db::name('country_code')->where('code_value',$v['point_country_code'])->find()['code_name'];
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    public function pick_up_point_save(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){
            if($id>0){
                $res = Db::name('centralize_self_lift_point')->where('id',$id)->update([
                    'point_name'=>trim($dat['point_name']),
                    'point_tel'=>trim($dat['point_tel']),
                    'point_person'=>trim($dat['point_person']),
                    'point_country_code'=>trim($dat['point_country_code']),
                    'point_address'=>trim($dat['point_address']),
                    'status'=>intval($dat['status']),
                ]);
            }else{
                $res = Db::name('centralize_self_lift_point')->insert([
                    'point_name'=>trim($dat['point_name']),
                    'point_tel'=>trim($dat['point_tel']),
                    'point_person'=>trim($dat['point_person']),
                    'point_country_code'=>trim($dat['point_country_code']),
                    'point_address'=>trim($dat['point_address']),
                    'status'=>intval($dat['status']),
                    'createtime'=>time()
                ]);
            }
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = ['point_name'=>'','point_tel'=>'','point_person'=>'','point_country_code'=>'','point_address'=>'','status'=>''];
            if($id>0){
                $data = Db::name('centralize_self_lift_point')->where('id',$id)->find();
            }
            $country_code = Db::name('country_code')->select();
            return view('',compact('id','data','country_code'));
        }
    }

    public function pick_up_point_del(Request $request){
        $dat = input();
        if(isset($dat['id'])){
            $res = Db::name('centralize_self_lift_point')->where('id',$dat['id'])->delete();
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }
    }

    public function collect_status(Request $request){
        $dat = input();

        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');

            $total = DB::name('centralize_consolidation_status')->count();
            $data = Db::name('centralize_consolidation_status')
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            if(!empty($data)){
                foreach($data as $k=>&$v){
                    if($v['status']==1){
                        $v['status']='上架';
                    }elseif($v['status']==2){
                        $v['status']='下架';
                    }
                }
            }
            return json(['msg' => "", 'code' => 0, 'count' => $total,  'data' => $data]);
        }else{
            return view('');
        }
    }

    public function add_colstatus(Request $request){
        $dat = input();

        if($request->isAjax()){
            $res = Db::name('centralize_consolidation_status')->insert([
                'pid'=>intval($dat['pid']),
                'name'=>trim($dat['name']),
                'code'=>trim($dat['code']),
                'status'=>intval($dat['status']),
            ]);
            if($res){
                return json(['msg'=>'添加成功','code'=>0]);
            }
        }else{
            return view('',['pid'=>intval($dat['pid'])]);
        }
    }

    public function edit_colstatus(Request $request){
        $dat = input();

        if($request->isAjax()){
            $res = Db::name('centralize_consolidation_status')->where('id',$dat['id'])->update([
                'name'=>trim($dat['name']),
                'code'=>trim($dat['code']),
                'status'=>intval($dat['status']),
            ]);
            if($res){
                return json(['msg'=>'修改成功','code'=>0]);
            }
        }else{
            $data = Db::name('centralize_consolidation_status')->where('id',$dat['id'])->find();
            return view('',['id'=>intval($dat['id']),'data'=>$data]);
        }
    }

    public function del_colstatus(Request $request){
        $dat = input();

        $is_have = Db::name('centralize_consolidation_status')->where('pid',$dat['id'])->find();
        if(!empty($is_have)){
            return json(['code'=>-1,'msg'=>'请先删除子状态']);
        }
        $res = Db::name('centralize_consolidation_status')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    public function set_country(Request $request){
        $dat = input();

        if($request->isAjax()){
            $result = $this->http_get("api/Country/Get");

            foreach($result as $k=>$v){
                $is_have = Db::name('destination_country')->where(['Base_placeId'=>$v['Base_placeId']])->find();

                if(empty($is_have['Base_placeId'])){
                    Db::name('destination_country')->insert([
                        'ShortName'=>$v['ShortName'],
                        'EnName'=>$v['EnName'],
                        'Cnname'=>$v['Cnname'],
                        'Base_placeId'=>$v['Base_placeId'],
                    ]);
                }
            }

            return json(['code'=>0]);
        }else{
            $list = Db::name('destination_country')->select();

            return view('',['title' => '上传目的地国家','list'=>$list]);
        }
    }

    public function set_line(Request $request){
        $dat = input();

        if($request->isAjax()){
            $result = $this->http_get("api/Channel/Get");
            $isnotice = 0;
            foreach($result as $k=>$v){
                $is_have = Db::name('shipping_channel')->where(['ChannelCode'=>$v['ChannelCode']])->find();

                if(empty($is_have['ChannelCode'])){
                    Db::name('shipping_channel')->insert([
                        'Base_ChannelInfoID'=>$v['base_Channelinfoid'],
                        'ChannelCode'=>$v['ChannelCode'],
                        'CnName'=>$v['CnName'],
                        'EnName'=>$v['enname'],
                        'RefTime'=>$v['reftime'],
                        'ShortenImage'=>$v['shortenimage'],
                    ]);
                    $isnotice=1;
                }

                #爬取线路介绍
                if(!empty($is_have['subChannelInfoID'])){
                    $content = file_get_contents('https://www.pfcexpress.com/webservice/APIWebService.asmx/ChannelInfo_sub?subChannelInfoID='.$is_have['subChannelInfoID']);
                    #解析xml
                    $content = json_decode(json_encode(simplexml_load_string($content),true),true)[0];
                    $res = Db::name('shipping_channel')->where(['ChannelCode'=>$v['ChannelCode']])->update(['content'=>$content]);
                }
            }
            if($isnotice==1){
                #通知管理员
                sendWechatMsg(json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'管理员你好，PFC皇家物流有新的渠道，请进入总后台配置子渠道代码！',
                    'keyword1' => '新渠道通知',
                    'keyword2' => '已通知',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '',
                    'url' => '',
                    'openid' => 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg',//ov3-bt8keSKg_8z9Wwi-zG1hRhwg ov3-bt5vIxepEjWc51zRQNQbFSaQ
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]));
            }

            return json(['code'=>0,'msg'=>'更新成功']);
        }else{
            $list = Db::name('shipping_channel')->select();
            if(isset($dat['keyword'])){
                if(!empty($dat['keyword'])){
                    $list = Db::name('shipping_channel')->where(['ChannelCode'=>trim($dat['keyword'])])->select();
                }
            }

            return view('',['title' => '上传线路资源','list'=>$list]);
        }
    }

    public function save_linepriceimg(Request $request){
        $data = input();
        $cnid = trim($data['subChannelInfoID']);
//        if(!empty($data['img_file'][0])){
//            $res = Db::name('shipping_channel')->where(['ChannelCode'=>$data['channelCode']])->update(['detail'=>$data['img_file'][0]]);
            $content = file_get_contents('https://www.pfcexpress.com/webservice/APIWebService.asmx/ChannelInfo_sub?subChannelInfoID='.$cnid);
            #解析xml
            $content = json_decode(json_encode(simplexml_load_string($content),true),true)[0];
            $res = Db::name('shipping_channel')->where(['ChannelCode'=>$data['channelCode']])->update(['subChannelInfoID'=>$cnid,'content'=>$content]);
            if($res){
                return json(['code'=>0,'msg'=>'操作成功！']);
            }
//        }
    }

    public function view_line(Request $request){
        $data = input();
        $res = Db::name('shipping_channel')->where(['ChannelCode'=>$data['channelcode']])->find();
//        $res = json_decode($res['content'],true);
//        print_r($res['content']);die;
        return json(['code'=>0,'content'=>$res['content']]);
    }
    
    #运单管理
    public function waybill_list(Request $request){
        $data = input();
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');

            $total = DB::name('centralize_waybill_list')->count();
            $data = Db::name('centralize_waybill_list')
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            if(!empty($data)){
                $_status = [0=>'已发出',1=>'已签收',2=>'数据已确认',3=>'费用已确认'];
                foreach($data as $k=>$v){
                    $data[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
                    $data[$k]['send_mobile'] = $v['send_mobile_area'].$v['send_mobile'];
                    $data[$k]['receive_mobile'] = $v['receive_mobile_area'].$v['receive_mobile'];
                    $data[$k]['status_name'] = $_status[$v['status']];
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }
    
    public function save_waybill(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){
//             dd($dat);
            if(empty($id)){
                $time = time();
                $data = [
                    #新增方式
                    'type'=>$dat['type'],
                    #发件人信息-------------------
                    #名称类型
                    'send_name_type'=>$dat['send_name_type'],
                    #名称
                    'send_name'=>trim($dat['send_name']),
                    #区号
                    'send_mobile_area'=>trim($dat['send_mobile_area']),
                    #电话
                    'send_mobile'=>trim($dat['send_mobile']),
                    #邮编国家
                    'send_postal_country'=>$dat['send_postal_country'],
                    #邮编
                    'send_postal'=>json_encode($dat['send_postal'],true),
                    #发件地址
                    'send_address'=>json_encode($dat['send_address'],true),
                    #收件人信息-------------------
                    #名称类型
                    'receive_name_type'=>$dat['receive_name_type'],
                    #名称
                    'receive_name'=>trim($dat['receive_name']),
                    #区号
                    'receive_mobile_area'=>trim($dat['receive_mobile_area']),
                    #电话
                    'receive_mobile'=>trim($dat['receive_mobile']),
                    #邮编国家
                    'receive_postal_country'=>$dat['receive_postal_country'],
                    #邮编
                    'receive_postal'=>json_encode($dat['receive_postal'],true),
                    #发件地址
                    'receive_address'=>json_encode($dat['receive_address'],true),
                    #集运商信息-------------------
                    #新增/选择集运商
                    'merchant_type'=>$dat['merchant_type'],
                    #选择集运商
                    'merchant_id'=>$dat['merchant_type']==1?$dat['merchant_id']:0,
                    #新增集运商
//                    'realname'=>$dat['merchant_type']==2?trim($dat['realname']):'',
                    #集运商手机
                    'phone'=>trim($dat['phone']),
                    #集运商仓库地址
                    'merchant_warehouse'=>json_encode($dat['merchant_warehouse'],true),
                    #货物信息-------------------
                    #货物清单--
                    'goods_list'=>json_encode($dat['goods_list'],true),
                    'origin_goods_list'=>json_encode($dat['goods_list'],true),
                    #货物材积--
                    'goods_volumn'=>json_encode($dat['goods_volumn'],true),
                    'origin_goods_volumn'=>json_encode($dat['goods_volumn'],true),
                    #物流渠道--
                    'logistics_channel'=>json_encode($dat['logistics_channel'],true),
                    'origin_logistics_channel'=>json_encode($dat['logistics_channel'],true),
                    #物流运单--
                    'logistics_waybill'=>json_encode($dat['logistics_waybill'],true),
                    'origin_logistics_waybill'=>json_encode($dat['logistics_waybill'],true),
                    'status'=>0,
                    'createtime'=>$time
                ];
//                dd($data);
                #插入运单表
                $waybill_id = Db::name('centralize_waybill_list')->insertGetId($data);
                #生成集运商
                if($dat['merchant_type']==2){
                    $insertid = Db::name('website_user')->insertGetId([
                        'realname'=>trim($dat['realname']),
                        'phone'=>trim($dat['phone']),
                        'merch_status'=>2,
                        'is_verify'=>1,
                        'createtime'=>$time
                    ]);
                    $res = Db::name('website_user')->where('id',$insertid)->update(['custom_id'=>'GG'.date('YmdHis',$time).str_pad($insertid, 3, '0', STR_PAD_LEFT)]);

                    Db::name('centralize_waybill_list')->where(['id'=>$waybill_id])->update([
                        'merchant_id'=>$insertid
                    ]);
                }
            }
            else{
                $data = [
                    #新增方式
                    'type'=>$dat['type'],
                    #发件人信息-------------------
                    #名称类型
                    'send_name_type'=>$dat['send_name_type'],
                    #名称
                    'send_name'=>trim($dat['send_name']),
                    #区号
                    'send_mobile_area'=>trim($dat['send_mobile_area']),
                    #电话
                    'send_mobile'=>trim($dat['send_mobile']),
                    #邮编国家
                    'send_postal_country'=>$dat['send_postal_country'],
                    #邮编
                    'send_postal'=>json_encode($dat['send_postal'],true),
                    #发件地址
                    'send_address'=>json_encode($dat['send_address'],true),
                    #收件人信息-------------------
                    #名称类型
                    'receive_name_type'=>$dat['receive_name_type'],
                    #名称
                    'receive_name'=>trim($dat['receive_name']),
                    #区号
                    'receive_mobile_area'=>trim($dat['receive_mobile_area']),
                    #电话
                    'receive_mobile'=>trim($dat['receive_mobile']),
                    #邮编国家
                    'receive_postal_country'=>$dat['receive_postal_country'],
                    #邮编
                    'receive_postal'=>json_encode($dat['receive_postal'],true),
                    #发件地址
                    'receive_address'=>json_encode($dat['receive_address'],true),
                    #集运商信息-------------------
                    #新增/选择集运商
                    'merchant_type'=>$dat['merchant_type'],
                    #选择集运商
                    'merchant_id'=>$dat['merchant_type']==1?$dat['merchant_id']:0,
                    #新增集运商
//                    'realname'=>$dat['merchant_type']==2?trim($dat['realname']):'',
                    #集运商手机
                    'phone'=>trim($dat['phone']),
                    #集运商仓库地址
                    'merchant_warehouse'=>json_encode($dat['merchant_warehouse'],true),
                    #货物信息-------------------
                    #货物清单--
                    'goods_list'=>json_encode($dat['goods_list'],true),
                    'origin_goods_list'=>json_encode($dat['goods_list'],true),
                    #货物材积--
                    'goods_volumn'=>json_encode($dat['goods_volumn'],true),
                    'origin_goods_volumn'=>json_encode($dat['goods_volumn'],true),
                    #物流渠道--
                    'logistics_channel'=>json_encode($dat['logistics_channel'],true),
                    'origin_logistics_channel'=>json_encode($dat['logistics_channel'],true),
                    #物流运单--
                    'logistics_waybill'=>json_encode($dat['logistics_waybill'],true),
                    'origin_logistics_waybill'=>json_encode($dat['logistics_waybill'],true),
                    #费用确认--
                    'sure_fee_list'=>json_encode(['true_weight'=>trim($dat['true_weight']),'first_weight'=>trim($dat['first_weight']),'first_price'=>trim($dat['first_price']),'second_weight'=>trim($dat['second_weight']),'second_price'=>trim($dat['second_price']),'unit'=>trim($dat['unit']),'currency'=>trim($dat['currency']),'insu_weight'=>trim($dat['insu_weight']),'cont_weight'=>trim($dat['cont_weight']),'fee_desc'=>$dat['fee_desc'],'fee_currency'=>$dat['fee_currency'],'fee_money'=>$dat['fee_money'],'total_money'=>$dat['total_money'],'total_fee_currency'=>$dat['total_fee_currency'],'total_fee_price'=>$dat['total_fee_price']],true)
                ];

                #跨境转运的费用记录在订单支付表
                if(isset($dat['sure_fee'])){
                    $service_price = $dat['total_money'];
                    foreach($dat['total_fee_price'] as $k=>$v){
                        $service_price+=$v;
                    }

                    $waybill_list = Db::name('centralize_waybill_list')->where(['id'=>$id])->find();
                    $order_fee_log = Db::name('centralize_order_fee_log')->where(['express_no'=>$waybill_list['express_no']])->order('id desc')->find();
                    Db::name('centralize_order_fee_log')->where(['id'=>$order_fee_log['id']])->update([
                        'order_status'=>intval($order_fee_log['order_status'])+1,
                        'service_price'=>$service_price
                    ]);
                    $parcel = Db::name('centralize_parcel_order_package')->where(['id'=>$waybill_list['parcel_id']])->update(['status2'=>intval($order_fee_log['order_status'])+1]);
                }
                #插入运单表
                Db::name('centralize_waybill_list')->where(['id'=>$id])->update($data);
                #生成集运商
                if($dat['merchant_type']==2){
                    $time = time();
                    $insertid = Db::name('website_user')->insertGetId([
                        'realname'=>trim($dat['realname']),
                        'phone'=>trim($dat['phone']),
                        'merch_status'=>2,
                        'is_verify'=>1,
                        'createtime'=>$time
                    ]);
                    $res = Db::name('website_user')->where('id',$insertid)->update(['custom_id'=>'GG'.date('YmdHis',$time).str_pad($insertid, 3, '0', STR_PAD_LEFT)]);

                    Db::name('centralize_waybill_list')->where(['id'=>$id])->update([
                        'merchant_id'=>$insertid
                    ]);
                }
            }
            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['type'=>1,'ordersn'=>'','send_name_type'=>'','send_name'=>'','send_mobile_area'=>'','send_mobile'=>'','send_postal_country'=>'','send_postal'=>'','send_address'=>['country'=>'','province_code'=>'','city_code'=>'','address1'=>'','address2'=>''],'receive_name_type'=>'','receive_name'=>'','receive_mobile_area'=>'','receive_mobile'=>'','receive_postal_country'=>'','receive_postal'=>'','receive_address'=>['country'=>'','province_code'=>'','city_code'=>'','address1'=>'','address2'=>''],'merchant_id'=>'','merchant_name'=>'','merchant_warehouse'=>['country'=>'','province_code'=>'','city_code'=>'','address1'=>'','address2'=>''],'merchant_phone'=>'','goods_list'=>'','goods_volumn'=>['long'=>'','width'=>'','height'=>'','volumn'=>'','grosswt'=>'','unit'=>1,'unit2'=>1],'logistics_channel'=>['name'=>'','line_name'=>''],'logistics_waybill'=>'','merchant_type'=>1,'phone'=>'','status'=>0];
            if($id>0){
                $data = Db::name('centralize_waybill_list')->where(['id'=>$id])->find();
                $merchant = Db::name('centralize_manage_person')->where(['id'=>$data['merchant_id']])->find();
                $data['merchant_phone'] = $merchant['tel'];
                $data['merchant_name'] = $merchant['name'];
                $data['send_postal'] = json_decode($data['send_postal'],true);
                $data['receive_postal'] = json_decode($data['receive_postal'],true);
                $data['send_address'] = json_decode($data['send_address'],true);
                $data['receive_address'] = json_decode($data['receive_address'],true);
                $data['merchant_warehouse'] = json_decode($data['merchant_warehouse'],true);
                $data['goods_list'] = json_decode($data['goods_list'],true);
                $data['goods_volumn'] = json_decode($data['goods_volumn'],true);
                $data['logistics_channel'] = json_decode($data['logistics_channel'],true);
                $data['logistics_waybill'] = json_decode($data['logistics_waybill'],true);
                if($data['status']>=3){
                    $data['sure_fee_list'] = json_decode($data['sure_fee_list'],true);
                    #查找物流运单
                    $all_express = Db::name('centralize_waybill_express_no')->where(['waybill_id'=>$id])->select();
                    if(!empty($all_express)){
                        $new_express['express'] = [];
                        $new_express['express_no'] = [];
                        foreach($all_express as $k=>$v){
                            array_push($new_express['express'],$v['express_id']);
                            array_push($new_express['express_no'],$v['express_no']);
                        }
                        $data['logistics_waybill']['express'] = array_merge($data['logistics_waybill']['express'],$new_express['express']);
                        $data['logistics_waybill']['express_no'] = array_merge($data['logistics_waybill']['express_no'],$new_express['express_no']);
                    }
                }

                foreach($data['goods_list']['valueid'] as $k=>$v){#[1,2],[1,2]
                    $value = explode(',',$v);
                    foreach($value as $k2=>$v3){
                        if(!empty($v3)){
                            $data['goods_list']['value_select'][$k][$k2] = Db::name('centralize_product_value')->where(['id'=>$v3])->find()['name'];
                        }
                    }
                }
            }
            $postal = Db::name('centralize_diycountry_content')->where(['pid'=>4])->select();
//            $country = Db::name('country_code')->where('code_name','<>','无')->select();
            $country = Db::name('centralize_diycountry_content')->where(['pid'=>5])->select();
            $merchant = Db::name('centralize_manage_person')->select();
            $unit = Db::name('unit')->select();
//            $express = Db::name('customs_express_company_code')->select();
            $category = Db::name('centralize_hscode_list')->select();
            $express = Db::name('centralize_diycountry_content')->where(['pid'=>6])->select();
            #奢侈品牌
//            $brand = Db::name('customs_travelexpress_brand')->select();
            $brand = Db::name('centralize_diycountry_content')->where(['pid'=>8])->select();
            #商品类别
            $goods_item = json_encode($this->get_gitem());
            #属性
//            $value = Db::name('centralize_gvalue_list')->where(['pid'=>0])->select();
            $value = json_encode($this->menu2(2),true);
//                json_encode([['id'=>1,'name'=>'是否带电池'],['id'=>2,'name'=>'是否有非液体化妆品']],true);
            #国家区域
            $area = Db::name('centralize_diycountry_content')->where(['pid'=>7])->select();
            $currency = Db::name('currency')->select();
//             dd($data['sure_fee_list']['total_fee_currency']);
            return view('',compact('data','id','postal','country','merchant','unit','express','category','brand','value','area','currency','goods_item'));
        }
    }

    #获取商品类别表信息
    public function get_gitem(){
        $data = Db::name('centralize_hscode_list')->where(['pid'=>0])->select();
        foreach($data as $k=>$v){
            $data[$k]['children'] = $this->get_gitem_child($v['id']);
        }
        return $data;
    }

    public function get_gitem_child($id){
        $data = Db::name('centralize_hscode_list')->where(['pid'=>$id])->select();
        if(!empty($data)){
            foreach($data as $k=>$v){
                $data[$k]['children'] = $this->get_gitem_child($v['id']);
            }
            return $data;
        }else{
            return $data;
        }
    }

    #菜单栏目-xmselect树形结构
    public function menu2($typ=0){
        $menu = Db::name('centralize_gvalue_list')->where(['pid'=>0])->field('id,name,country,channel,desc,keywords')->select();
        foreach($menu as $k=>$v){
            $menu[$k]['name'] = $v['name'];
            $menu[$k]['value'] = $v['id'];
            if($typ==1){
                $menu[$k]['children'] = $this->getDownMenu3($v['id']);
            }else{
                $menu[$k]['children'] = $this->getDownMenu2($v['id']);
//                dd($menu);
            }
        }
        return $menu;
    }

    #下级菜单
    public function getDownMenu2($id){
        $cmenu = Db::name('centralize_gvalue_list')->where(['pid'=>$id])->field('id,name,country,channel,desc,keywords')->select();
        foreach($cmenu as $k=>$v){
            $cmenu[$k]['name'] = $v['name'];
            $cmenu[$k]['value'] = $v['id'];
            $cmenu[$k]['children'] = Db::name('centralize_gvalue_list')->where(['pid'=>$v['id']])->field('id,name,country,channel,desc,keywords')->select();
            foreach($cmenu[$k]['children'] as $k2=>$v2){
                $cmenu[$k]['children'][$k2]['name'] = $v2['name'];
                $cmenu[$k]['children'][$k2]['value'] = $v2['id'];
                $cmenu[$k]['children'][$k2]['children'] = Db::name('centralize_gvalue_list')->where(['pid'=>$v2['id']])->field('id,name,country,channel,desc,keywords')->select();
                foreach($cmenu[$k]['children'][$k2]['children'] as $k3=>$v3){
                    $cmenu[$k]['children'][$k2]['children'][$k3]['name'] = $v3['name'];
                    $cmenu[$k]['children'][$k2]['children'][$k3]['value'] = $v3['id'];
                    $cmenu[$k]['children'][$k2]['children'][$k3]['children'] = Db::name('centralize_gvalue_list')->where(['pid'=>$v3['id']])->field('id,name,country,channel,desc,keywords')->select();
                    foreach($cmenu[$k]['children'][$k2]['children'][$k3]['children'] as $k4=>$v4){
                        $cmenu[$k]['children'][$k2]['children'][$k3]['children'][$k4]['name'] = $v4['name'];
                        $cmenu[$k]['children'][$k2]['children'][$k3]['children'][$k4]['value'] = $v4['id'];
                    }
                }
            }
        }
        return $cmenu;
    }

    #不要最下一层的菜单
    public function getDownMenu3($id){
        $cmenu = Db::name('website_navbar')->where(['pid'=>$id])->field('id,name')->select();
        foreach($cmenu as $k=>$v){
            $cmenu[$k]['name'] = json_decode($v['name'],true)['zh'];
            $cmenu[$k]['value'] = $v['id'];
//            $cmenu[$k]['children'] = Db::name('website_navbar')->where(['pid'=>$v['id']])->field('id,name')->select();
//            if(empty($cmenu[$k]['children'])){
//                unset($cmenu[$k]);
//            }
//            else{
//                foreach($cmenu[$k]['children'] as $k2=>$v2){
//                    $cmenu[$k]['children'][$k2]['name'] = json_decode($v2['name'],true)['zh'];
//                    $cmenu[$k]['children'][$k2]['value'] = $v2['id'];
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
//                }
//            }
        }
        return $cmenu;
    }

    public function export_waybill(Request $request){
        $dat = input();
        $id = $dat['id'];

        return view('',compact('id'));
    }

    public function export_waybill_pdf(Request $request){
        $dat = input();
        $id = $dat['id'];
        $type = $dat['type'];

        $pinfo = Db::name('centralize_waybill_list')->where(['id'=>$id])->find();
        #收件人国家
        $pinfo['receive_postal_country'] = Db::name('centralize_diycountry_content')->where(['id'=>$pinfo['receive_postal_country']])->find()['param2'];
        $pinfo['receive_postal'] = json_decode($pinfo['receive_postal'],true);
        $pinfo['receive_address'] = json_decode($pinfo['receive_address'],true);
        $pinfo['receive_address']['country'] = Db::name('centralize_diycountry_content')->where(['id'=>$pinfo['receive_address']['country']])->find()['param2'];
        $pinfo['receive_address']['province_code'] = Db::name('centralize_diycountry_content')->where(['id'=>$pinfo['receive_address']['province_code']])->find()['param2'];
        $pinfo['receive_address']['city_code'] = Db::name('centralize_diycountry_content')->where(['id'=>$pinfo['receive_address']['city_code']])->find()['param2'];
        $pinfo['true_receive_address'] = $pinfo['receive_address']['country'].$pinfo['receive_address']['province_code'].$pinfo['receive_address']['city_code'].'，'.$pinfo['receive_address']['address1'];
        if(!empty($pinfo['receive_address']['address2'])){
            foreach($pinfo['receive_address']['address2'] as $k=>$v){
                $pinfo['true_receive_address'] = $pinfo['true_receive_address'].'，'.$v;
            }
        }
        #发货人国家
        $pinfo['send_postal'] = json_decode($pinfo['send_postal'],true);
        $pinfo['send_postal_country'] = Db::name('centralize_diycountry_content')->where(['id'=>$pinfo['send_postal_country']])->find()['param2'];
        $pinfo['send_address'] = json_decode($pinfo['send_address'],true);
        $pinfo['send_address']['country'] = Db::name('centralize_diycountry_content')->where(['id'=>$pinfo['send_address']['country']])->find()['param2'];
        $pinfo['send_address']['province_code'] = Db::name('centralize_diycountry_content')->where(['id'=>$pinfo['send_address']['province_code']])->find()['param2'];
        $pinfo['send_address']['city_code'] = Db::name('centralize_diycountry_content')->where(['id'=>$pinfo['send_address']['city_code']])->find()['param2'];
        $pinfo['true_send_address'] = $pinfo['send_address']['country'].$pinfo['send_address']['province_code'].$pinfo['send_address']['city_code'].'，'.$pinfo['send_address']['address1'];
        if(!empty($pinfo['send_address']['address2'])){
            foreach($pinfo['send_address']['address2'] as $k=>$v){
                $pinfo['true_send_address'] = $pinfo['true_send_address'].'，'.$v;
            }
        }

        #集运商
        $pinfo['merchant_warehouse'] = json_decode($pinfo['merchant_warehouse'],true);
        $pinfo['merchant_name'] = Db::name('website_user')->where(['id'=>$pinfo['merchant_id']])->find()['realname'];
        $pinfo['merchant_warehouse']['country'] = Db::name('centralize_diycountry_content')->where(['id'=>$pinfo['merchant_warehouse']['country']])->find()['param2'];
        $pinfo['merchant_warehouse']['province_code'] = Db::name('centralize_diycountry_content')->where(['id'=>$pinfo['merchant_warehouse']['province_code']])->find()['param2'];
        $pinfo['merchant_warehouse']['city_code'] = Db::name('centralize_diycountry_content')->where(['id'=>$pinfo['merchant_warehouse']['city_code']])->find()['param2'];
        $pinfo['true_merchant_address'] = $pinfo['merchant_warehouse']['country'].$pinfo['merchant_warehouse']['province_code'].$pinfo['merchant_warehouse']['city_code'].'，'.$pinfo['merchant_warehouse']['address1'];
        if(!empty($pinfo['merchant_warehouse']['address2'])){
            foreach($pinfo['merchant_warehouse']['address2'] as $k=>$v){
                $pinfo['true_merchant_address'] = $pinfo['true_merchant_address'].'，'.$v;
            }
        }

        #货物属性
        $pinfo['goods_list'] = json_decode($pinfo['goods_list'],true);
        foreach($pinfo['goods_list']['valueid'] as $k=>$v){
            $now_gvalue = explode(',',$v);
            foreach($now_gvalue as $k2=>$v2){
                $now_gvalue[$k2] = Db::name('centralize_gvalue_list')->where(['id'=>$v2])->find()['name'];
            }
            $pinfo['goods_list']['valueid'][$k] = $now_gvalue;
            #单位
            $pinfo['goods_list']['unit'][$k] = Db::name('unit')->where(['code_value'=>$pinfo['goods_list']['unit'][$k]])->find()['code_name'];
        }
        #货物材积
        $pinfo['goods_volumn'] = json_decode($pinfo['goods_volumn'],true);
        if($pinfo['goods_volumn']['unit']==1){
           $pinfo['goods_volumn']['unit'] = 'CM'; 
        }elseif($pinfo['goods_volumn']['unit']==2){
            $pinfo['goods_volumn']['unit'] = 'M';
        }
        if($pinfo['goods_volumn']['unit2']==1){
           $pinfo['goods_volumn']['unit2'] = 'KGS'; 
        }elseif($pinfo['goods_volumn']['unit2']==2){
            $pinfo['goods_volumn']['unit2'] = 'GS';
        }
        #物流渠道
        $pinfo['logistics_channel'] = json_decode($pinfo['logistics_channel'],true);
        #物流运单
        $pinfo['logistics_waybill'] = json_decode($pinfo['logistics_waybill'],true);
        $all_express = Db::name('centralize_waybill_express_no')->where(['waybill_id'=>$id])->select();
        if(!empty($all_express)){
            $new_express['express'] = [];
            $new_express['express_no'] = [];
            foreach($all_express as $k=>$v){
                array_push($new_express['express'],$v['express_id']);
                array_push($new_express['express_no'],$v['express_no']);
            }
            $pinfo['logistics_waybill']['express'] = array_merge($pinfo['logistics_waybill']['express'],$new_express['express']);
            $pinfo['logistics_waybill']['express_no'] = array_merge($pinfo['logistics_waybill']['express_no'],$new_express['express_no']);
        }
        foreach($pinfo['logistics_waybill']['express'] as $k=>$v){
            $pinfo['logistics_waybill']['express'][$k] = Db::name('centralize_diycountry_content')->where(['id'=>$v])->find()['param3'];
        }
        #计费信息
        $pinfo['sure_fee_list'] = json_decode($pinfo['sure_fee_list'],true);
        if($pinfo['sure_fee_list']['unit']==1){
            $pinfo['sure_fee_list']['unit'] = 'KGS'; 
        }elseif($pinfo['sure_fee_list']['unit']==2){
            $pinfo['sure_fee_list']['unit'] = 'GS';
        }
        $pinfo['sure_fee_list']['currency'] = Db::name('currency')->where(['code_value'=>$pinfo['sure_fee_list']['currency']])->find()['code_name'];
        foreach($pinfo['sure_fee_list']['fee_currency'] as $k=>$v){
            $pinfo['sure_fee_list']['fee_currency'][$k] = Db::name('currency')->where(['code_value'=>$v])->find()['code_name'];
        }
        foreach($pinfo['sure_fee_list']['total_fee_currency'] as $k=>$v){
            $pinfo['sure_fee_list']['total_fee_currency'][$k] = Db::name('currency')->where(['code_value'=>$v])->find()['code_name'];
        }
        $parcel = Db::name('centralize_parcel_order_package')->where(['cross_id'=>$pinfo['id']])->find();
        // dd($pinfo['logistics_waybill']);
        return view('',compact('id','type','pinfo','parcel'));
    }

    public function export_waybill_exl(Request $request){
        $dat = input();
        $id = $dat['id'];
        $type = $dat['type'];

        return view('',compact('id','type'));
    }

    public function del_waybill(Request $request){
        $dat = input();
        $res = Db::name('centralize_waybill_list')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    public function get_postal_rule(Request $request){
        $dat = input();
        $postal = Db::name('centralize_diycountry_content')->where(['id'=>$dat['val']])->find();
        for($i=0;$i<strlen($postal['param3']);$i++){
            $postal['rule'][$i] = $postal['param3'][$i];
        }
        return json(['code'=>0,'data'=>$postal]);
    }

    public function share_waybill(Request $request){
        $dat = input();
        $id = $dat['id'];
        
        return view('',compact('id'));
    }
    
    public function get_qrcode(Request $request){
        $dat = input();
        $id = $dat['id'];
        
        $name = 'express_'.$dat['id'];
        $folder = $_SERVER['DOCUMENT_ROOT'].'/collect_website/public/express/qrcode/';

        #官网链接二维码
        $img = generate_code($name,'https://www.gogo198.net/?s=index/express&id='.intval($dat['id']),$folder);

        return json(['code'=>0,'img'=>$img.'?v='.time(),'msg'=>'生成成功']);
    }

    //集运流程管理-start
    public function gather_process_manage(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?$dat['pid']:0;
        $system_id = isset($dat['system_id'])?$dat['system_id']:0;
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');

            $total = DB::name('centralize_process_list')->where(['system_id'=>$system_id,'pid'=>$pid,'display'=>0])->count();
            $data = Db::name('centralize_process_list')
                ->where(['system_id'=>$system_id,'pid'=>$pid,'display'=>0])
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            if(!empty($data)){
                foreach($data as $k=>$v){
                    $data[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('',compact('pid','system_id'));
        }
    }

    public function save_process(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $pid = isset($dat['pid'])?$dat['pid']:0;
        $displayorders = isset($dat['displayorders'])?$dat['displayorders']+1:1;
        $system_id = isset($dat['system_id'])?$dat['system_id']:0;

        if($request->isAjax()){
            $level = intval($dat['level']);
            if(empty($dat['displayorders'])){
                return json(['code'=>-1,'msg'=>'请补充序号']);
            }

            if($level==2){
                if($pid>0){
                    $p_info = Db::name('centralize_process_list')->where(['system_id'=>$system_id,'id'=>$pid,'display'=>0])->find();
                    $pid = $p_info['pid'];
                }
            }

            #1、判断是否有重复序号
//            if($level==1){
                $ishave = Db::name('centralize_process_list')->where(['system_id'=>$system_id,'pid'=>$pid,'displayorders'=>$dat['displayorders'],'step'=>$dat['step'],'display'=>0])->find();

                if($ishave['id']>0 && $id!=$ishave['id']){
                    return json(['code'=>-1,'msg'=>'该序号或序号描述已重复']);
                }
//            }else{
//                $ishave = Db::name('centralize_process_list')->where(['pid'=>0,'displayorders'=>$dat['displayorders'],'step'=>$dat['step']])->find();
//                if($ishave['id']>0){
//                    return json(['code'=>-1,'msg'=>'该序号或序号描述已重复']);
//                }
//            }

            #2、判断该序号前面是否有缺号
            $step = trim($dat['step']);
            $step = explode('Step',$step);
            if(!isset($step[1])){
                return json(['code'=>-1,'msg'=>'请输入正确的序号：Step+序号']);
            }
            for($i=1;$i<$step[1];$i++){
                $ishave = Db::name('centralize_process_list')->where(['system_id'=>$system_id,'pid'=>$pid,'step'=>'Step'.$i,'display'=>0])->find();
                if(empty($ishave['id'])){
                    return json(['code'=>-2,'msg'=>'该序号前缺号（Step'.$i.'），正在为你补缺','step'=>$i]);
                }
            }

            if($id>0){
                $res = Db::name('centralize_process_list')->where(['system_id'=>$system_id,'id'=>$id])->update([
                    'step'=>trim($dat['step']),
//                    'title'=>trim($dat['title']),
                    'displayorders'=>trim($dat['displayorders']),
                    'content'=>trim($dat['content']),
                    'go_other'=>$dat['go_other'],
                    'link'=>$dat['go_other']==1?trim($dat['link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'other_pic'=>$dat['go_other']==3?trim($dat['other_pic']):'',
                    'other_msg'=>$dat['go_other']==4?trim($dat['other_msg']):'',
                    'icon'=>isset($dat['ico'][0])?$dat['ico'][0]:'',
                ]);
            }else{
                $res = Db::name('centralize_process_list')->insert([
                    'system_id'=>$system_id,
                    'pid'=>$pid,
                    'step'=>trim($dat['step']),
//                    'title'=>trim($dat['title']),
                    'displayorders'=>trim($dat['displayorders']),
                    'content'=>trim($dat['content']),
                    'go_other'=>$dat['go_other'],
                    'link'=>$dat['go_other']==1?trim($dat['link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'other_pic'=>$dat['go_other']==3?trim($dat['other_pic']):'',
                    'other_msg'=>$dat['go_other']==4?trim($dat['other_msg']):'',
                    'icon'=>isset($dat['ico'][0])?$dat['ico'][0]:'',
                    'createtime'=>time(),
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = ['step'=>'Step'.$displayorders,'title'=>'','icon'=>'','content'=>'','displayorders'=>$displayorders,'go_other'=>'','link'=>'','other_navbar'=>'','other_pic'=>'','other_msg'=>''];
            if($pid>0){
                $ishave = Db::name('centralize_process_list')->where(['system_id'=>$system_id,'pid'=>$pid,'display'=>0])->order('displayorders desc')->find();
                if($ishave['id']){
                    $num = intval($ishave['displayorders'])+1;
                    $data['step'] = 'Step'.$num;
                    $data['displayorders'] = $num;
                }
            }else{
                $ishave = Db::name('centralize_process_list')->where(['system_id'=>$system_id,'pid'=>$pid,'display'=>0])->order('displayorders desc')->find();
                if($ishave['id']){
                    $num = intval($ishave['displayorders'])+1;
                    $data['step'] = 'Step'.$num;
                    $data['displayorders'] = $num;
                }
            }
            if($id>0){
                $data = Db::name('centralize_process_list')->where(['system_id'=>$system_id,'id'=>$id,'display'=>0])->find();
                $pid=$data['pid'];
            }

            $list='';
            if($system_id==1){
                $list = $this->menu();
            }elseif($system_id==2){
                $list = $this->get_gather_process();
            }elseif($system_id==3){
                $list = Db::connect($this->config)->name('guide_frame')->where(['type'=>1])->select();
            }

            #图文链接
            $pic_list = Db::name('website_image_txt')->select();
            foreach($pic_list as $k=>$v){
                $pic_list[$k]['name'] = json_decode($v['name'],true)['zh'];
            }

            #消息链接
            $msg_list = Db::name('website_message_manage')->select();

            return view('',compact('id','pid','data','system_id','list','pic_list','msg_list'));
        }
    }

    public function del_process(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){
            $res = Db::name('centralize_process_list')->where(['id'=>$id])->delete();
            if($res){
                return json(['code'=>0,'msg'=>'删除成功']);
            }
        }
    }

    #获取集运流程
    public function get_gather_process($id=0){
        $list = Db::name('centralize_process_list')->where(['pid'=>0,'display'=>0])->order('displayorders','asc')->select();
        if(!empty($list)){
            foreach($list as $k=>$v){
                #2
                $list[$k]['children'] = Db::name('centralize_process_list')->where(['pid'=>$v['id'],'display'=>0])->order('displayorders','asc')->select();
                if(!empty($list[$k]['children'])){
                    foreach($list[$k]['children'] as $k2=>$v2) {
                        #3
                        $list[$k]['children'][$k2]['children'] = Db::name('centralize_process_list')->where(['pid' => $v2['id'],'display'=>0])->order('displayorders', 'asc')->select();
                        if(!empty($list[$k]['children'][$k2]['children'])){
                            foreach($list[$k]['children'][$k2]['children'] as $k3=>$v3) {
                                #4
                                $list[$k]['children'][$k2]['children'][$k3]['children'] = Db::name('centralize_process_list')->where(['pid' => $v3['id'],'display'=>0])->order('displayorders', 'asc')->select();
                            }
                        }
                    }
                }
            }
        }
        return $list;
    }

    #菜单栏目
    public function menu(){
        $menu = Db::name('website_navbar')->where(['pid'=>0])->field('id,name')->select();
        foreach($menu as $k=>$v){
            $menu[$k]['name'] = json_decode($v['name'],true)['zh'];
            $menu[$k]['childMenu'] = $this->getDownMenu($v['id']);
        }
        return $menu;
    }

    #下级菜单
    public function getDownMenu($id){
        $cmenu = Db::name('website_navbar')->where(['pid'=>$id])->field('id,name')->select();
        foreach($cmenu as $k=>$v){
            $cmenu[$k]['name'] = json_decode($v['name'],true)['zh'];
            $cmenu[$k]['childMenu'] = Db::name('website_navbar')->where(['pid'=>$v['id']])->field('id,name')->select();
            foreach($cmenu[$k]['childMenu'] as $k2=>$v2){
                $cmenu[$k]['childMenu'][$k2]['name'] = json_decode($v2['name'],true)['zh'];
                $cmenu[$k]['childMenu'][$k2]['childMenu'] = Db::name('website_navbar')->where(['pid'=>$v2['id']])->field('id,name')->select();
                foreach($cmenu[$k]['childMenu'][$k2]['childMenu'] as $k3=>$v3){
                    $cmenu[$k]['childMenu'][$k2]['childMenu'][$k3]['name'] = json_decode($v3['name'],true)['zh'];
                }
            }
        }
        return $cmenu;
    }
    //集运流程管理-end

    //资讯中心-start
    #集运动态管理-start
    public function enterprise_news_manage(Request $request){
        $dat = input();

        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('centralize_enterprise_news')->order($order)->count();
            $rows = DB::name('centralize_enterprise_news')
                ->limit($limit)
                ->order($order)
                ->select();
            foreach($rows as $k=>&$v){
                if(!empty($v['createtime'])){
                    $v['createtime'] = date('Y-m-d H:i',$v['createtime']);
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }
    public function save_enterprise_news(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $cate_id = isset($dat['cate_id'])?$dat['cate_id']:0;

        if($request->isAjax()){
            if($id>0){
                Db::name('centralize_enterprise_news')->where('id',$id)->update([
                    'name'=>trim($dat['name']),
                    'type'=>intval($dat['type']),
                    'origin_link'=>$dat['type']==1?trim($dat['origin_link']):'',
                    'content'=>$dat['type']==1?json_encode($dat['content'],true):'',
                    'social_id'=>$dat['type']==2?intval($dat['social_id']):'',
                    'social_link'=>$dat['type']==2?trim($dat['social_link']):'',
                    'seo_content'=>json_encode($dat['seo_content'],true),
                    'release_date'=>trim($dat['release_date']),
                ]);
            }else{
                $res=Db::name('centralize_enterprise_news')->insert([
                    'name'=>trim($dat['name']),
                    'type'=>intval($dat['type']),
                    'origin_link'=>$dat['type']==1?trim($dat['origin_link']):'',
                    'content'=>$dat['type']==1?json_encode($dat['content'],true):'',
                    'createtime'=>$dat['type']==1?time():'',
                    'social_id'=>$dat['type']==2?intval($dat['social_id']):'',
                    'social_link'=>$dat['type']==2?trim($dat['social_link']):'',
                    'seo_content'=>json_encode($dat['seo_content'],true),
                    'release_date'=>trim($dat['release_date']),
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功！']);
        }else{
            $data = ['name'=>'','type'=>1,'content'=>'','origin_link'=>'','social_id'=>'','social_link'=>'','seo_content'=>['title'=>'','keywords'=>'','desc'=>''],'release_date'=>''];
            if($id>0){
                $data = Db::name('centralize_enterprise_news')->where('id',$id)->find();

                if(!empty($data['content'])){
                    $data['content'] = json_decode($data['content'],true);
                }
                $data['seo_content'] = json_decode($data['seo_content'],true);
            }
            $social = Db::name('website_contact')->select();
            return view('',compact('data','id','social'));
        }
    }
    public function del_enterprise_news(Request $request){
        $dat = input();
        $res = Db::name('centralize_enterprise_news')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }
    #集运动态管理-end

    #集运新闻管理-start
    public function crossnews_manage(Request $request){
        $dat = input();
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        $id = isset($dat['id'])?intval($dat['id']):1;
        if( request()->isPost() || request()->isAjax()){
            if(!isset($dat['is_ai'])){
                $count = Db::name('centralize_crossborder_news')->where(['status'=>intval($dat['status'])])->order($order)->count();
                $rows = DB::name('centralize_crossborder_news')
                    ->where(['status'=>intval($dat['status'])])
                    ->limit($limit)
                    ->order($order)
                    ->select();

                foreach($rows as $k=>$v){
                    if(strstr($v['time'],':')){
                        $rows[$k]['time'] = explode(' ',$v['time'])[0];
                        Db::name('centralize_crossborder_news')->where(['id'=>$v['id']])->update(['time'=>$rows[$k]['time']]);
                    }
                }
                return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
            }else{
                $res = Db::name('centralize_aichat_question')->where(['id'=>$id])->update([
                    'question'=>$dat['question']
                ]);
                if($res){
                    return json(['msg'=>'修改成功','code'=>0]);
                }
            }
        }else{
            $question = Db::name('centralize_aichat_question')->where(['id'=>$id])->find();
            return view('',compact('question','id'));
        }
    }

    public function del_crossborder_news(Request $request){
        $dat = input();

        $res = Db::name('centralize_crossborder_news')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }

    public function save_crossborder_news(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):'';
        if($request->isAjax()){
            if($id>0){
                $res = Db::name('centralize_crossborder_news')->where(['id'=>$id])->update([
                    'time'=>trim($dat['time']),
                    'info_source'=>trim($dat['info_source']),
                    'title'=>trim($dat['title']),
                    'descs'=>trim($dat['descs']),
                    'link'=>trim($dat['link']),
                    'status'=>$dat['status']
                ]);
            }else{
                $res = Db::name('centralize_crossborder_news')->insert([
                    'time'=>trim($dat['time']),
                    'info_source'=>trim($dat['info_source']),
                    'title'=>trim($dat['title']),
                    'descs'=>trim($dat['descs']),
                    'link'=>trim($dat['link']),
                    'status'=>1,
                    'pid'=>46//菜单id
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存并上架成功']);
            }
        }else{
            $data = ['time'=>'','title'=>'','descs'=>'','link'=>'','info_source'=>''];
            if($id>0){
                $data = Db::name('centralize_crossborder_news')->where(['id'=>$id])->find();
            }
            return view('',compact('data','id'));
        }
    }

    public function shelf_crossborder_news(Request $request){
        $dat = input();
        require 'simple_html_dom.php';
        $ids = ltrim($dat['ids'],',');
        if(empty($ids)){
            return json(['code'=>-1,'msg'=>'请选择上架内容']);
        }else{
            if($ids==10000){
                #全部上架

                $list = Db::name('centralize_crossborder_news')->where(['status'=>0])->select();
                foreach($list as $k=>$v){
                    #1、去重
                    $recurring = Db::name('centralize_crossborder_news')->where(['title'=>$v['title']])->select();
                    if(count($recurring)>1){
                        foreach($recurring as $k2=>$v2){
                            if($k2!=0){
                                Db::name('centralize_crossborder_news')->where(['id'=>$v2['id']])->delete();
                            }
                        }
                    }
                    #2、清楚链接为空的
                    if(empty($v['link'])){
                        Db::name('centralize_crossborder_news')->where(['id'=>$v['id']])->delete();
                    }

                    #3、修改时间为yyyy-mm-dd格式
                    if(strstr($v['time'],':')){
                        $time = explode(' ',$v['time'])[0];
                        Db::name('centralize_crossborder_news')->where(['id'=>$v['id']])->update(['time'=>$time]);
                    }
                }

                #4、获取原文链接&上架
                $list2 = Db::name('centralize_crossborder_news')->where(['status'=>0])->select();
                foreach($list2 as $k=>$v){
                    // 创建一个新的 HTML DOM 对象并从给定 URL 加载页面内容
                    try{
                        $html = file_get_html($v['link']);
                    }catch (\Exception $e) {
                        $html = file_get_html(str_replace('https','http',$v['link']));
                    }

                    // 使用 CSS 选择器查找特定元素
                    $elements = $html->find('.target-url-content');
                    // 遍历找到的元素并输出它们的文本内容
                    foreach ($elements as $element) {
                        Db::name('centralize_crossborder_news')->where(['status'=>0,'id'=>$v['id']])->update([
                            'status'=>1,
                            'link'=>trim($element->plaintext),
                            'pid'=>46//菜单id
                        ]);
                    }
                    // 释放内存，清理资源
                    $html->clear();
                }
            }else{
                #手动执行
                $ids = explode(',',$ids);
                foreach($ids as $k=>$v){
                    $info = Db::name('centralize_crossborder_news')->where(['id'=>$v])->find();
                    #1、清楚链接为空的
                    if(empty($info['link'])){
                        Db::name('centralize_crossborder_news')->where(['id'=>$v['id']])->delete();
                        continue;
                    }
                    #2、获取原文链接&上架
//                    $html = file_get_html($info['link']);
                    try{
                        $html = file_get_html($info['link']);
                    }catch (\Exception $e) {
                        $html = file_get_html(str_replace('https','http',$info['link']));
                    }
                    $elements = $html->find('.target-url-content');
                    foreach ($elements as $element) {
                        Db::name('centralize_crossborder_news')->where(['status'=>0,'id'=>$v])->update([
                            'status'=>1,
                            'link'=>trim($element->plaintext),
                            'pid'=>46//菜单id
                        ]);
                    }
                    $html->clear();
                }
            }

            return json(['code'=>0,'msg'=>'全部上架成功']);
        }
    }
    #集运新闻管理-end

    #集运政策-start
    //新闻分类
    public function newscate_manage(Request $request){
        $dat = input();

        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){

            $count = Db::name('centralize_policy_category')->order($order)->count();
            $rows = DB::name('centralize_policy_category')
                ->limit($limit)
                ->order($order)
                ->select();
            foreach($rows as $k=>&$v){
                $v['category_name'] = json_decode($v['category_name'],true)['zh'];
                $v['show'] = $v['show']==1?'隐藏':'显示';
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('');
        }
    }

    public function save_newscate(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){

            if($id>0){
//                ,'category_img'=>$dat['category_img'][0]
                Db::name('centralize_policy_category')->where('id',$id)->update(['category_name'=>json_encode(['zh'=>trim($dat['category_name']['zh'])],true),'show'=>2]);
            }else{
                Db::name('centralize_policy_category')->insert(['category_name'=>json_encode(['zh'=>trim($dat['category_name']['zh'])],true),'createtime'=>time(),'show'=>2]);
            }

            return json(['code'=>0,'msg'=>'保存成功！']);
        }else{
            $data = ['category_name'=>['zh'=>'','cht'=>'','en'=>'']];
            if($id>0){
                $data = Db::name('centralize_policy_category')->where('id',$id)->find();
                $data['category_name'] = json_decode($data['category_name'],true);
            }
            return view('',compact('data','id'));
        }
    }

    public function del_newscate(Request $request){
        $dat = input();
//        $msg = $dat['typ']==1?'隐藏':'显示';
        $res = Db::name('centralize_policy_category')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }

    //新闻列表
    public function news_list(Request $request){
        $dat = input();
        $cate_id = $dat['id'];

        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('centralize_policy_list')->where(['cate_id'=>$cate_id])->order($order)->count();
            $rows = DB::name('centralize_policy_list')
                ->where(['cate_id'=>$cate_id])
                ->limit($limit)
                ->order($order)
                ->select();
            foreach($rows as $k=>&$v){
                $v['name'] = json_decode($v['name'],true)['zh'];
                $v['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('cate_id'));
        }
    }

    #导出exl
    public function out_news(Request $request){
        $dat = input();
        $rows = DB::name('centralize_policy_list')->order('release_date','desc')->select();
        foreach($rows as $k=>$v){
            $rows[$k]['issuing_authority'] = json_decode($v['issuing_authority'],true)['zh'];
            $rows[$k]['document_number'] = json_decode($v['document_number'],true)['zh'];
            $rows[$k]['name'] = json_decode($v['name'],true)['zh'];
            $rows[$k]['effect'] = json_decode($v['effect'],true)['zh'];
            $rows[$k]['file'] = json_decode($v['file'],true);
            if(!empty($rows[$k]['file'])){
                $rows[$k]['file'][0] = 'https://shop.gogo198.cn/'.$rows[$k]['file'][0];
            }
            $cate_name = Db::name('centralize_policy_category')->where(['id'=>$v['cate_id']])->find()['category_name'];
            $rows[$k]['cate_name'] = json_decode($cate_name,true)['zh'];
        }
        #输出excel表格
        $fileName = '集运政策数据['.date('Y-m-d H:i:s').'].xls';
        $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes';
        $dir2 = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
        require_once($dir."/PHPExcel.php");
        require_once($dir2."/IOFactory.php");
        $Excel = new PHPExcel();
        $PHPSheet = $Excel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('跨境政策'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue('A1', '分类名称');
        $PHPSheet->setCellValue('B1', '政策标题');
        $PHPSheet->setCellValue('C1', '发文机关');
        $PHPSheet->setCellValue('D1', '文号');
        $PHPSheet->setCellValue('E1', '效力');
        $PHPSheet->setCellValue('F1', '发布日期');
        $PHPSheet->setCellValue('G1', '生效日期');
        $PHPSheet->setCellValue('H1', '公布链接');
        $PHPSheet->setCellValue('I1', '文件链接');
        $PHPSheet->setCellValue('J1', '备注');
        $ExcelWrite = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');
        $n = 2;
        if (!empty($rows)) {
            foreach ($rows as $value) {
                $PHPSheet->setCellValue('A'.$n,"\t" .$value['cate_name']."\t")
                    ->setCellValue('B'.$n,"\t" .$value['name']."\t")
                    ->setCellValue('C'.$n,"\t" .$value['issuing_authority']."\t")
                    ->setCellValue('D'.$n,"\t" .$value['document_number']."\t")
                    ->setCellValue('E'.$n,"\t" .$value['effect']."\t")
                    ->setCellValue('F'.$n,"\t" .$value['release_date']."\t")
                    ->setCellValue('G'.$n,"\t" .$value['effective_date']."\t")
                    ->setCellValue('H'.$n,"\t" .$value['link']."\t")
                    ->setCellValue('I'.$n,"\t" .$value['file'][0]."\t")
                    ->setCellValue('J'.$n,"\t" .$value['remark']."\t");
                $n +=1;
            }
        }

        ob_end_clean();//清楚缓冲避免乱码
        header('pragma:public');
        //设置表头信息
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name='.$fileName);
        header("Content-Disposition:attachment;filename={$fileName}");//attachment新窗口打
        return $ExcelWrite->save('php://output');
    }

    public function save_news(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $cate_id = isset($dat['cate_id'])?$dat['cate_id']:0;
        $cate_name = Db::name('centralize_policy_category')->where(['id'=>$cate_id])->find()['category_name'];
        $cate_name = json_decode($cate_name,true)['zh'];

        if($request->isAjax()){
            if($dat['origin_type']==1){
                $ishave = Db::name('centralize_policy_list')->where(['link'=>trim($dat['link'])])->find();
                if(!empty($ishave)){
                    if($ishave['id']!=$id){
                        return json(['code'=>-2,'msg'=>'已存在该原文链接！','id'=>$ishave['id']]);
                    }
                }
            }
            if($dat['origin_type']==1 && empty($dat['link'])){
                return json(['code'=>-1,'msg'=>'请填写公布链接！']);
            }elseif($dat['origin_type']==2 && empty($dat['file'])){
                return json(['code'=>-1,'msg'=>'请上传文件！']);
            }
            if($id>0){
                Db::name('centralize_policy_list')->where('id',$id)->update([
                    'issuing_authority'=>json_encode(['zh'=>trim($dat['issuing_authority']['zh'])],true),
                    'document_number'=>json_encode(['zh'=>trim($dat['document_number']['zh'])],true),
                    'name'=>json_encode(['zh'=>trim($dat['name']['zh'])],true),
                    'release_date'=>trim($dat['release_date']),
                    'effective_date'=>trim($dat['effective_date']),
                    'effect'=>json_encode(['zh'=>trim($dat['effect']['zh'])],true),
                    'content'=>isset($dat['content_zh'])?json_encode(['zh'=>$dat['content_zh']],true):'',
                    'origin_type'=>$dat['origin_type'],
                    'link'=>$dat['origin_type']==1?trim($dat['link']):'',
                    'file'=>$dat['origin_type']==2?json_encode($dat['file'],true):'',
                    'avatar'=>isset($dat['avatar'][0])?$dat['avatar'][0]:'',
                    'avatar_location'=>$dat['avatar_location'],
                    'format'=>$dat['format'],
                    'color'=>$dat['format']==2?$dat['color']:'',
                    'color_word'=>$dat['format']==2?json_encode(['zh'=>trim($dat['color_word_zh'])]):'',
                    'word_color'=>$dat['format']==2?$dat['word_color']:'',
                    'seo_type'=>$dat['seo_type'],
                    'seo_content'=>$dat['seo_type']==2?json_encode($dat['seo_content'],true):'',
                    'url'=>trim($dat['url']),#我要服务链接
                    'remark'=>trim($dat['remark']),
                ]);
            }else{
                $res=Db::name('centralize_policy_list')->insert([
                    'cate_id'=>$cate_id,
                    'issuing_authority'=>json_encode(['zh'=>trim($dat['issuing_authority']['zh'])],true),
                    'document_number'=>json_encode(['zh'=>trim($dat['document_number']['zh'])],true),
                    'name'=>json_encode(['zh'=>trim($dat['name']['zh'])],true),
                    'release_date'=>trim($dat['release_date']),
                    'effective_date'=>trim($dat['effective_date']),
                    'effect'=>json_encode(['zh'=>trim($dat['effect']['zh'])],true),
                    'content'=>isset($dat['content_zh'])?json_encode(['zh'=>$dat['content_zh']],true):'',
                    'origin_type'=>$dat['origin_type'],
                    'link'=>$dat['origin_type']==1?trim($dat['link']):'',
                    'file'=>$dat['origin_type']==2?json_encode($dat['file'],true):'',
                    'avatar'=>isset($dat['avatar'][0])?$dat['avatar'][0]:'',
                    'avatar_location'=>$dat['avatar_location'],
                    'format'=>$dat['format'],
                    'color'=>$dat['format']==2?$dat['color']:'',
                    'color_word'=>$dat['format']==2?json_encode(['zh'=>trim($dat['color_word_zh'])]):'',
                    'word_color'=>$dat['format']==2?$dat['word_color']:'',
                    'seo_type'=>$dat['seo_type'],
                    'seo_content'=>$dat['seo_type']==2?json_encode($dat['seo_content'],true):'',
                    'url'=>trim($dat['url']),#我要服务链接
                    'remark'=>trim($dat['remark']),
                    'createtime'=>time(),
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功！']);
        }else{
            $data = ['issuing_authority'=>['zh'=>'','cht'=>'','en'=>''],'document_number'=>['zh'=>'','cht'=>'','en'=>''],'name'=>['zh'=>'','cht'=>'','en'=>''],'release_date'=>'','effective_date'=>'','effect'=>['zh'=>'','cht'=>'','en'=>''],'effect_statement'=>['zh'=>'','cht'=>'','en'=>''],'link'=>'','content'=>['zh'=>'','cht'=>'','en'=>''],'desc'=>['zh'=>'','cht'=>'','en'=>''],'seo_type'=>1,'seo_content'=>['title'=>['zh'=>'','cht'=>'','en'=>''],'keywords'=>['zh'=>'','cht'=>'','en'=>''],'desc'=>['zh'=>'','cht'=>'','en'=>'']],'color_word'=>['zh'=>'','cht'=>'','en'=>''],'url'=>'','avatar'=>'','avatar_location'=>'','format'=>'','color'=>'','word_color'=>'','remark'=>'','file'=>'','origin_type'=>1];
            if($id>0){
                $data = Db::name('centralize_policy_list')->where('id',$id)->find();
                $cate_name = Db::name('centralize_policy_category')->where(['id'=>$data['cate_id']])->find()['category_name'];
                $cate_name = json_decode($cate_name,true)['zh'];

                if($data['format']==2){
                    $data['color_word'] = json_decode($data['color_word'],true);
                }else{
                    $data['color_word'] = ['zh'=>'','cht'=>'','en'=>''];
                }
                $data['name'] = json_decode($data['name'],true);
                $data['issuing_authority'] = json_decode($data['issuing_authority'],true);
                $data['document_number'] = json_decode($data['document_number'],true);
                $data['effect'] = json_decode($data['effect'],true);
                $data['effect_statement'] = json_decode($data['effect_statement'],true);
                $data['content'] = json_decode($data['content'],true);
                $data['desc'] = json_decode($data['desc'],true);
                $data['seo_content'] = json_decode($data['seo_content'],true);
                $data['file'] = json_decode($data['file'],true);
            }
            return view('',compact('data','id','cate_id','cate_name'));
        }
    }

    public function news_check_link(Request $request){
        $dat = input();
        $ishave = Db::name('centralize_policy_list')->where(['link'=>$dat['link']])->where('id','<>',$dat['id'])->find();
        if(empty($ishave)){
            return json(['code'=>1,'msg'=>'链接无重']);
        }else{
            return json(['code'=>2,'msg'=>'链接重复']);
        }
    }

    public function del_news(Request $request){
        $dat = input();
        $res = Db::name('centralize_policy_list')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }
    #集运政策-end

    #服务描述-start
    public function services_manage(Request $request){
        $dat = input();
        $system_id = intval($dat['system_id']);
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){

            $count = Db::name('centralize_services_list')->where(['system_id'=>$system_id])->order($order)->count();
            $rows = DB::name('centralize_services_list')
                ->where(['system_id'=>$system_id])
                ->limit($limit)
                ->order($order)
                ->select();
            foreach($rows as $k=>&$v){
                $v['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{

            return view('',compact('system_id'));
        }
    }

    public function save_services(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $system_id = intval($dat['system_id']);

        if($request->isAjax()){
            if($dat['link_type']==1){
                $dat['origin_link'] = get_link($dat['function_id']);
            }
            

            $function_id = 0;
            if($system_id==2){
                $function_id = intval($dat['function_id']);
            }elseif($system_id==3){
                if($dat['link_type']==1){
                    $function_id = intval($dat['other_navbar']);
                }
                elseif($dat['link_type']==3){
                    $function_id = intval($dat['other_pic']);
                }
                elseif($dat['link_type']==4){
                    $function_id = intval($dat['other_msg']);
                }
            }

            if($id>0){
                Db::name('centralize_services_list')->where(['id'=>$id])->update([
                    'title'=>trim($dat['title']),
                    'desc'=>trim($dat['desc']),
//                    'content'=>json_encode($dat['content'],true),
                    'link_type'=>$dat['link_type'],
                    'origin_link'=>trim($dat['origin_link']),
                    'img'=>$dat['img'],
                    'function_id'=>$function_id,
                ]);
            }else{
                Db::name('centralize_services_list')->insert([
                    'system_id'=>$system_id,
                    'title'=>trim($dat['title']),
                    'desc'=>trim($dat['desc']),
//                    'content'=>json_encode($dat['content'],true),
                    'link_type'=>$dat['link_type'],
                    'origin_link'=>trim($dat['origin_link']),
                    'img'=>$dat['img'],
                    'function_id'=>$function_id,
                    'createtime'=>time()
                ]);
            }
            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['title'=>'','desc'=>'','content'=>'','origin_link'=>'','img'=>'','seo_content'=>['title'=>'','keywords'=>'','desc'=>''],'function_id'=>'','link_type'=>1];
            if($id>0){
                $data = Db::name('centralize_services_list')->where(['id'=>$id])->find();
                $data['seo_content'] = json_decode($data['seo_content'],true);
                $data['content'] = json_decode($data['content'],true);
            }
            $process=[];$list=[];$msg_list=[];$pic_list=[];
            if($system_id==2){
                $process = Db::name('centralize_process_list')->where(['pid'=>0,'display'=>0])->select();
                if(!empty($process)){
                    foreach($process as $k=>$v){
                        $process[$k]['children'] = Db::name('centralize_process_list')->where(['pid'=>$v['id'],'display'=>0])->select();
                        if(!empty($process[$k]['children'])){
                            foreach($process[$k]['children'] as $k2=>$v2) {
                                $process[$k]['children'][$k2]['children'] = Db::name('centralize_process_list')->where(['pid' => $v2['id'],'display'=>0])->select();
                            }
                        }
                    }
                }
            }
            elseif($system_id==3){
                #应用链接
                $list = Db::connect($this->config)->name('guide_frame')->where(['type'=>1])->select();

                #图文链接
                $pic_list = Db::name('website_image_txt')->select();
                foreach($pic_list as $k=>$v){
                    $pic_list[$k]['name'] = json_decode($v['name'],true)['zh'];
                }

                #消息链接
                $msg_list = Db::name('website_message_manage')->select();
            }

            $process = json_encode($process,true);
            return view('',compact('data','id','process','system_id','list','pic_list','msg_list'));
        }
    }

    public function del_services(Request $request){
        $dat = input();
        $res = Db::name('centralize_services_list')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }
    #服务描述-end

    #规则确认管理-start
    public function rule_manage(Request $request){
        $dat = input();
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){

            $count = Db::name('centralize_rule_list')->order($order)->count();
            $rows = DB::name('centralize_rule_list')
                ->limit($limit)
                ->order($order)
                ->select();
            foreach($rows as $k=>&$v){
                $v['createtime'] = date('Y-m-d H:i',$v['createtime']);
                $v['function_name'] = Db::name('centralize_process_list')->where(['id'=>$v['function_id']])->find()['content'];
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('');
        }
    }

    public function save_rule(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){
            if($id>0){
                Db::name('centralize_rule_list')->where(['id'=>$id])->update([
                    'system_id'=>$dat['system_id'],
                    'function_id'=>$dat['function_id'],
                    'confirm'=>json_encode($dat['confirm'],true),
                    'sure'=>json_encode($dat['sure'],true),
                    'knows'=>json_encode($dat['knows'],true),
                ]);
            }else{
                Db::name('centralize_rule_list')->insert([
                    'system_id'=>$dat['system_id'],
                    'function_id'=>$dat['function_id'],
                    'confirm'=>json_encode($dat['confirm'],true),
                    'sure'=>json_encode($dat['sure'],true),
                    'knows'=>json_encode($dat['knows'],true),
                    'createtime'=>time()
                ]);
            }
            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['system_id'=>0,'function_id'=>0,'confirm'=>['method'=>1,'second'=>'','title'=>[],'link'=>[]],'sure'=>['method'=>1,'second'=>'','title'=>[],'link'=>[]],'knows'=>['method'=>1,'second'=>'','title'=>[],'link'=>[]]];
            if($id>0){
                $data = Db::name('centralize_rule_list')->where(['id'=>$id])->find();
                $data['confirm'] = json_decode($data['confirm'],true);
                $data['sure'] = json_decode($data['sure'],true);
                $data['knows'] = json_decode($data['knows'],true);
            }
            $process = Db::name('centralize_process_list')->where(['pid'=>0,'display'=>0])->select();
            if(!empty($process)){
                foreach($process as $k=>$v){
                    $process[$k]['children'] = Db::name('centralize_process_list')->where(['pid'=>$v['id'],'display'=>0])->select();
                    if(!empty($process[$k]['children'])){
                        foreach($process[$k]['children'] as $k2=>$v2) {
                            $process[$k]['children'][$k2]['children'] = Db::name('centralize_process_list')->where(['pid' => $v2['id'],'display'=>0])->select();
                        }
                    }
                }
            }
            $process = json_encode($process,true);
            return view('',compact('data','id','process'));
        }
    }

    public function del_rule(Request $request){
        $dat = input();
        $res = Db::name('centralize_rule_list')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }

    public function rule_log(Request $request){
        $dat = input();
        $id=$dat['id'];
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){

            $count = Db::name('centralize_rule_log')->where(['rule_id'=>$dat['id']])->order($order)->count();
            $rows = DB::name('centralize_rule_log')
                ->where(['rule_id'=>$dat['id']])
                ->limit($limit)
                ->order($order)
                ->select();
            foreach($rows as $k=>&$v){
                $v['custom_id'] = Db::name('website_user')->where(['id'=>$v['uid']])->find()['custom_id'];
                $v['createtime'] = date('Y-m-d H:i',$v['createtime']);
//                $rules_name = json_decode($v['check_rules'],true);
//                $rows[$k]['check_rules'] = '';
//                foreach($rules_name as $k2=>$v2){
//                    $rows[$k]['check_rules'].='《'.$v2.'》';
//                }
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('id'));
        }
    }
    #规则确认管理-end


    #平台服务-start
    public function platform_services_list(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?$dat['pid']:0;
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');

            $total = DB::name('centralize_platform_service_list')->where(['pid'=>$pid])->count();
            $data = Db::name('centralize_platform_service_list')
                ->where(['pid'=>$pid])
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            if(!empty($data)){
                foreach($data as $k=>$v){
                    $data[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('',compact('pid'));
        }
    }
    public function save_platform_services_list(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $pid = isset($dat['pid'])?$dat['pid']:0;
        if($request->isAjax()){
            if($id>0){
                $res = Db::name('centralize_platform_service_list')->where(['id'=>$id])->update([
                    'title'=>trim($dat['title']),
                    'content'=>trim($dat['content']),
                    'link'=>trim($dat['link']),
                    'icon'=>$dat['ico'][0],
                ]);
            }else{
                $res = Db::name('centralize_platform_service_list')->insert([
                    'pid'=>$pid,
                    'title'=>trim($dat['title']),
                    'content'=>trim($dat['content']),
                    'link'=>trim($dat['link']),
                    'icon'=>$dat['ico'][0],
                    'createtime'=>time(),
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = ['title'=>'','icon'=>'','content'=>'','link'=>''];

            if($id>0){
                $data = Db::name('centralize_platform_service_list')->where(['id'=>$id])->find();
            }
            return view('',compact('id','pid','data'));
        }
    }
    public function del_platform_services_list(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){
            $res = Db::name('centralize_platform_service_list')->where(['id'=>$id])->delete();
            if($res){
                return json(['code'=>0,'msg'=>'删除成功']);
            }
        }
    }
    #平台服务-end
    //资讯中心-end

    #包裹管理-start
    public function package_manage(Request $request){
        $data = input();
        $manage = isset($data['manage'])?intval($data['manage']):1;
        $process_ids['process1'] = isset($data['process1'])?intval($data['process1']):16;
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $count = Db::name('centralize_parcel_order')
                ->count();
            $list = Db::name('centralize_parcel_order')
                ->limit($page . ',' . $limit)
                ->order('id asc')
                ->select();
            if($process_ids['process1']!=16){
                #非管理订仓
                $where = [];
                if($process_ids['process1']==19){
                    #管理预报
                    $where=[0];
                }elseif($process_ids['process1']==22){
                    #签收入库->确认信息
                    $where=[1,2,6,7,8,9,10,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38];
                }elseif($process_ids['process1']==25){
                    #仓库集货
                    $where=[];
                }
                $list2 = [];
                foreach ($list as $k => $v) {
                    $list[$k]['createtime'] = date('Y-m-d H:i:s', $v['createtime']);
                    $list[$k]['orderid'] = 0;

                    #找到该订仓单下面的包裹
                    $list3 = Db::name('centralize_parcel_order_package')->where(['orderid'=>$v['id']])->whereIn('status2',$where)->select();
                    if(empty($list3)){
                        unset($list[$k]);
                    }
                    foreach($list3 as $k2=>$v2){
                        $list3[$k2]['ordersn']='';
                        $list3[$k2]['createtime'] = date('Y-m-d H:i:s', $v2['createtime']);
                        $process_num = Db::name('centralize_process_list')->where(['id'=>$process_ids['process1']])->find()['step'];
                        $process_num = explode('Step',$process_num)[1];
                        $num=$k2+1;
                        $list3[$k2]['ordersn'] = '--'.$process_num.$num;
                    }
                    $list2 = array_merge($list2,$list3);
                }
                $list = array_merge($list,$list2);
                foreach($list as $k=>$v){
                    $list[$k]['oid'] = $v['id'];
                    if($v['orderid']!=0){
                        $list[$k]['id'] = 'p'.strval($k+1);#包裹id
                    }else{
                        $list[$k]['process_name'] = Db::name('centralize_process_list')->where(['id'=>$process_ids['process1']])->find()['content'];
                    }
                }
            }else{
                #管理订仓
                foreach ($list as $k => $v) {
                    $list[$k]['createtime'] = date('Y-m-d H:i:s', $v['createtime']);
                    $list[$k]['orderid'] = 0;
                    $list[$k]['oid'] = $v['id'];
                    $list[$k]['process_name'] = Db::name('centralize_process_list')->where(['id'=>$process_ids['process1']])->find()['content'];
                    #有预报就不显示
                    $list3 = Db::name('centralize_parcel_order_package')->where(['orderid'=>$v['id']])->select();
                    if(!empty($list3)){
                        unset($list[$k]);
                    }
                }
                $list = array_values($list);#返回的数组将使用数值键，从 0 开始且以 1 递增
            }
            return json(['code' => 0, 'msg'=>'','count' => $count, 'data' => $list]);
        }else{
            #集运流程
            $process =  Db::name('centralize_process_list')->where(['system_id'=>2,'pid'=>0,'display'=>0])->order('displayorders asc')->select();
            return view('',compact('manage','process','process_ids'));
        }
    }

    #修改订仓单
    public function edit_booking(Request $request){
        $data = input();
        $manage = isset($data['manage'])?intval($data['manage']):1;
        $process_ids['process1'] = isset($data['process1'])?intval($data['process1']):16;
        $id = isset($data['id'])?intval($data['id']):0;
        if($request->isAjax()){
            $res = Db::name('centralize_parcel_order')->where(['id'=>$data['orderid']])->update([
                'warehouse_id'=>$data['warehouse_id'],
                'prediction_id'=>$data['prediction_id'],
                'country'=>isset($data['country'])?$data['country']:'',
                'line_id'=>isset($data['line_id'])?$data['line_id']:'',
            ]);

            if($res){
                return json(['code'=>0,'msg'=>'修改成功']);
            }
        }else{
            #订仓单信息
            $order = Db::name('centralize_parcel_order')->where(['id'=>$id])->find();
            $user = Db::name('website_user')->where(['id'=>$order['user_id']])->find();
            #集运渠道
            $channel = Db::name('centralize_channel_list')->select();
            #国家
            $country = Db::name('centralize_diycountry_content')->where(['pid'=>5])->select();
            #仓库
            $warehouse = Db::name('centralize_warehouse_list')->where(['status'=>0])->order('id desc')->select();
            if(!empty($order['country'])){
                $country_name = Db::name('centralize_diycountry_content')->where(['id'=>$order['country']])->find()['param2'];
                $lines = json_encode($this->get_destination_country_route($country_name),true);
            }else{
                #目地国地是中国和对应集运渠道的线路（国际快递、国际邮政、国际专线）
                $lines = json_encode($this->get_destination_country_route('中国'),true);
            }
            #集运流程
            $process =  Db::name('centralize_process_list')->where(['system_id'=>2,'pid'=>0,'display'=>0])->order('displayorders asc')->select();
            return view('',compact('manage','process','process_ids','id','channel','country','warehouse','lines','order','user'));
        }
    }

    #获取目的国地下对应集运渠道的线路（国际快递、国际邮政、国际专线）
    public function get_destination_country_route($country){
        $menu = Db::name('centralize_line_channel')->field('id,name')->select();
        foreach($menu as $k=>$v){
            $menu[$k]['name'] = $v['name'];
            $menu[$k]['value'] = $v['id'];
            $menu[$k]['children'] = $this->get_this_line($v['id'],$country);
        }
        return $menu;
    }
    #获取该渠道下的线路
    public function get_this_line($id,$country){
        $cmenu = Db::name('centralize_line_list')
            ->alias('a')
            ->join('centralize_line_country b','b.pid=a.id')
            ->where(['b.country_code'=>$country,'a.channel_id'=>$id])
            ->field('a.id,a.name')
            ->group('a.id')
            ->select();

        if(empty($cmenu)){
            $cmenu[0]['name'] = '--暂无可选线路--';
            $cmenu[0]['value'] = '-1';
            $cmenu[0]['disabled'] = true;
        }else{
            foreach($cmenu as $k=>$v){
                $cmenu[$k]['name'] = $v['name'];
                $cmenu[$k]['value'] = $v['id'];
            }
        }
        return $cmenu;
    }
    #前端请求获取国地线路
    public function get_country_lines(Request $request){
        $dat = input();
        $country_name = Db::name('centralize_diycountry_content')->where(['id'=>$dat['val']])->find()['param2'];
        $lines = json_encode($this->get_destination_country_route($country_name),true);
        return json(['code'=>0,'msg'=>'获取线路成功','list'=>$lines]);
    }

    #包裹管理-end

    #增值服务-start
    public function service_list(Request $request){
        $dat = input();

        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');

            $total = DB::name('centralize_service_list')->count();
            $data = Db::name('centralize_service_list')
                ->limit($page,$limit)
                ->order('id','desc')
                ->select();

            if(!empty($data)){
                foreach($data as $k=>$v){
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('',compact(''));
        }
    }

    public function save_service(Request $request){
        $data = input();
        $id = isset($data['id'])?intval($data['id']):0;
        if($request->isAjax()){
            if($id>0){
                Db::name('centralize_service_list')->where(['id'=>$id])->update([
                   'name'=>trim($data['name']),
                   'currency'=>intval($data['currency']),
                    'price'=>trim($data['price'])
                ]);
            }else{
                Db::name('centralize_service_list')->insert([
                    'name'=>trim($data['name']),
                    'currency'=>intval($data['currency']),
                    'price'=>trim($data['price'])
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>'','currency'=>5,'price'=>''];
            if($id>0){
                $data = Db::name('centralize_service_list')->where(['id'=>$id])->find();
            }
            $currency = Db::name('centralize_currency')->select();

            return view('',compact('data','currency','id'));
        }
    }

    public function del_service(Request $request){
        $data = input();
        $id = isset($data['id'])?intval($data['id']):0;
        $res=Db::name('centralize_service_list')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
    #增值服务-end

    #赊账管理-start
    public function delaypay_list(Request $request){
        $dat = input();

        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');

            $total = DB::name('centralize_order_fee_log')->where('check_status','<>',0)->count();
            $data = Db::name('centralize_order_fee_log')
                ->alias('a')
                ->join('website_user b','a.user_id=b.id')
                ->where('a.check_status','<>',0)
                ->limit($page,$limit)
                ->order('a.id', 'desc')
                ->field(['a.*,b.realname'])
                ->select();

            if(!empty($data)){
                foreach($data as $k=>$v){
                    $data[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
                    if($v['check_status']==1){
                        $data[$k]['check_status2'] = '申请中';
                    }elseif($v['check_status']==2){
                        $data[$k]['check_status2'] = '已确认';
                    }elseif($v['check_status']==-1){
                        $data[$k]['check_status2'] = '已拒绝';
                    }
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('',compact(''));
        }
    }

    public function sure_delaypay(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){
            $res = Db::name('centralize_order_fee_log')->where(['id'=>$id])->update(['check_time'=>intval($dat['check_time']),'check_status'=>2]);
            if($res){
                return json(['code'=>0,'msg'=>'确认成功']);
            }
        }else{
            return view('',compact('id'));
        }
    }

    public function reject_delaypay(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){
            $res = Db::name('centralize_order_fee_log')->where(['id'=>$id])->update(['check_remark'=>trim($dat['check_remark']),'check_status'=>-1]);
            if($res){
                return json(['code'=>0,'msg'=>'拒绝成功']);
            }
        }else{
            return view('',compact('id'));
        }
    }
    #赊账管理-end

    #禁限类包裹管理-start
    public function prohibit_package_list(Request $request){
        $dat = input();

        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');

            $total = Db::name('centralize_parcel_order_package')->where('line_type','特殊货')->count();
            $data = Db::name('centralize_parcel_order_package')
                ->alias('a')
                ->join('website_user b','a.user_id=b.id')
                ->where('a.line_type','特殊货')
                ->limit($page,$limit)
                ->order('a.id', 'desc')
                ->field(['a.*,b.realname'])
                ->select();

            if(!empty($data)){
                foreach($data as $k=>$v){
                    $data[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
                    if($v['special_status']==1){
                        $data[$k]['special_status2'] = '允许寄运';
                    }elseif($v['special_status']==0){
                        $data[$k]['special_status2'] = '待审核';
                    }elseif($v['special_status']==-1){
                        $data[$k]['special_status2'] = '拒绝寄运';
                    }
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('',compact(''));
        }
    }

    #包裹详情
    public function package_info(Request $request){
        $dat = input();
        $id = $dat['id'];
        $manage = isset($data['manage'])?intval($dat['manage']):1;
        $process_ids['process1'] = isset($dat['process1'])?intval($dat['process1']):16;
        if($request->isAjax()){
//            $parcel = Db::name('centralize_parcel_order_package')->where(['id'=>$id])->find();
            if($dat['opera_status']==-1){
                #拒绝寄运
                Db::name('centralize_parcel_order_package')->where(['id'=>$id])->update([
                    'remark'=>trim($dat['remark']),
                    'special_status'=>$dat['opera_status'],
                    'status2'=>198,#拒绝寄运
                ]);
                Db::name('centralize_order_fee_log')->where(['good_id'=>$id])->update(['order_status'=>198]);
            }elseif($dat['opera_status']==1){
                #允许寄运
                Db::name('centralize_parcel_order_package')->where(['id'=>$id])->update([
                    'special_lineid'=>intval($dat['special_lineid']),
                    'special_status'=>$dat['opera_status']
                ]);
            }

            return json(['code'=>0,'msg'=>'操作成功']);
        }else{
            $parcel = Db::name('centralize_parcel_order_package')->where(['id'=>$id])->find();
            $goods = Db::name('centralize_parcel_order_goods')->where(['package_id'=>$parcel['id']])->select();
            $order = Db::name('centralize_parcel_order')->where(['id'=>$parcel['orderid']])->find();
            if($parcel['delivery_logistics']==1){
                #物流运仓
                #包裹运输企业
                if(!empty($parcel['express_id'])){
                    $parcel['express_name'] = Db::name('centralize_diycountry_content')->where(['id'=>$parcel['express_id']])->find()['param3'];
                }else{
                    $parcel['express_name'] = '自送入仓，无运输企业';
                }
            }elseif($parcel['delivery_logistics']==2){
                #上门提货，待做。。
            }
            #状况描述
            if(!empty($parcel['condition_file'])){
                $parcel['condition_file'] = json_decode($parcel['condition_file'],true);
            }
            #包裹验货视频
            if(!empty($parcel['pic_file'])){
                $parcel['pic_file'] = json_decode($parcel['pic_file'],true);
            }
            #包裹体积
            if(!empty($parcel['volumn'])){
                $parcel['volumn'] = explode('*',$parcel['volumn']);
            }
            if(!empty($goods)){
                foreach($goods as $k=>$v){
                    #商品类别
//                if(!empty($v['itemid'])){
//                    $goods[$k]['itemid'] = Db::name('centralize_hscode_list')->where(['id'=>$v['itemid']])->find()['name'];
//                }
                    #物品属性
                    if(!empty($v['valueid'])){
                        $gval = Db::name('centralize_gvalue_list')->where('id in ('.$v['valueid'].') ')->field('name')->find()['name'];
                        $goods[$k]['valueid2'] = $gval;
                        if($k==0){
                            #修改包裹时查找最上级id
                            $top_id = Db::name('centralize_gvalue_list')->where('id',$v['valueid'])->field('pid')->find()['pid'];
                            $top_id2 = Db::name('centralize_gvalue_list')->where('id',$top_id)->field('pid')->find()['pid'];
                            if($top_id2==0){
                                $goods[$k]['top_id'] = $top_id;
                            }else{
                                $top_id = Db::name('centralize_gvalue_list')->where('id',$top_id2)->field('pid')->find()['pid'];
                                $top_id2 = Db::name('centralize_gvalue_list')->where('id',$top_id)->field('pid')->find()['pid'];
                                if($top_id2==0){
                                    $goods[$k]['top_id'] = $top_id;
                                }else{
                                    $top_id = Db::name('centralize_gvalue_list')->where('id',$top_id2)->field('pid')->find()['pid'];
                                    $top_id2 = Db::name('centralize_gvalue_list')->where('id',$top_id)->field('pid')->find()['pid'];
                                    if($top_id2==0) {
                                        $goods[$k]['top_id'] = $top_id;
                                    }
                                }
                            }
                        }
                    }
                    #物品材质
                    if(!empty($v['good_package'])) {
                        $goods[$k]['good_package'] = Db::name('packing_type')->where(['code_value' => $v['good_package']])->find()['code_name'];
                    }
                    #物品单位
                    if(!empty($v['good_unit'])){
                        $goods[$k]['good_unit'] = Db::name('unit')->where(['code_value'=>$v['good_unit']])->find()['code_name'];
                    }
                    #物品币种
                    if(!empty($v['good_currency'])){
                        $goods[$k]['good_currency'] = Db::name('centralize_currency')->where(['id'=>$v['good_currency']])->find()['currency_symbol_standard'];
                    }else{
                        $goods[$k]['good_currency'] = Db::name('centralize_currency')->where(['id'=>5])->find()['currency_symbol_standard'];
                    }
                    #物品品牌类型
                }
            }

            #包裹状态
            $status2_name = Db::name('centralize_parcel_status')->where(['status_id'=>$parcel['status2']])->find();
            $status_name = Db::name('centralize_parcel_operation_status')->where(['status_id'=>$status2_name['pid']])->find();
            $parcel['status_name'] = '【'.$status_name['status_name'].'】'.$status2_name['status_name'];

            #查找该包裹最近的订单结算
            $order_fee_log = Db::name('centralize_order_fee_log')->where(['express_no'=>$parcel['express_no']])->order('id desc')->find();
            #集运流程
            $process = Db::name('centralize_process_list')->where(['system_id'=>2,'pid'=>0,'display'=>0])->order('displayorders asc')->select();
            #属性
            $value = json_encode($this->menu2(2),true);
            #币种
//            $currency = Db::name('currency')->select();
            $currency = Db::name('centralize_currency')->select();
            #快递公司
            $express = Db::name('centralize_diycountry_content')->where(['pid'=>6])->select();
            #奢侈品牌
            $brand = Db::name('centralize_diycountry_content')->where(['pid'=>8])->select();
            #包装材质
            $package = Db::name('packing_type')->select();
            #单位
            $unit = Db::name('unit')->select();
            return view('',compact('id','goods','order','link','order_fee_log','process','process_ids','parcel','value','currency','express','brand','package','unit','manage','process_ids'));
        }
    }
    #禁限类包裹管理-end

    public function http_get($acition){
        $serverurl = "http://api.pfcexpress.com:81/";
        $apikey = "aeae3d3c-bcaa-4442-8849-ec61bbf8def4125730";
        $headers=array('Authorization: '.'Bearer '.$apikey,'Content-type: application/json');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $serverurl.$acition);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $json = curl_exec($ch);
        curl_close($ch);
        $result=json_decode($json, true);

        return $result;
    }
}