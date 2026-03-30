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

class Index extends  Controller{

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
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');
            $where = [];
            if(!empty($search)){
                $where['title'] = ['like','%'.$search.'%'];
            }

            $total = DB::name('centralize_news')->where($where)->count();
            $data = DB::name('centralize_news')
                ->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach($data as $k=>$v){
                $data[$k]['category_name'] = Db::name('centralize_news_category')->where('id',$v['category_id'])->find()['name'];
            }

            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    public function news_save(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if ( request()->isPost() || request()->isAjax()) {
            if($id>0){
                $res = Db::name('centralize_news')->where('id',$id)->update([
                    'title'=>trim($dat['title']),
                    'category_id'=>intval($dat['category_id']),
                    'img'=>$dat['img_file'][0],
                    'content'=>json_encode($dat['editorValue'],true),
                ]);
            }else{
                $res = Db::name('centralize_news')->insert([
                    'title'=>trim($dat['title']),
                    'category_id'=>intval($dat['category_id']),
                    'img'=>$dat['img_file'][0],
                    'content'=>json_encode($dat['editorValue'],true),
                    'createtime'=>time()
                ]);
            }
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = ['title'=>'','category_id'=>''];
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

            $total = DB::name('centralize_warehouse_list')->where($where)->count();
            $data = DB::name('centralize_warehouse_list')
                ->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach($data as $k=>$v){
                if($v['type']==1){
                    $data[$k]['type']='国内仓库';
                }elseif($v['type']==2){
                    $data[$k]['type']='海外仓库';
                }
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
            if($id>0){
                $res = Db::name('centralize_warehouse_list')->where('id',$id)->update([
                    'warehouse_name'=>trim($dat['warehouse_name']),
                    'name'=>trim($dat['name']),
                    'mobile'=>trim($dat['mobile']),
                    'postal_code'=>intval($dat['postal_code']),
                    'address'=>trim($dat['address']),
                    'type'=>intval($dat['type']),
                    'country_code'=>$dat['country_code'],
                ]);
            }else{
                $res = Db::name('centralize_warehouse_list')->insert([
                    'warehouse_name'=>trim($dat['warehouse_name']),
                    'name'=>trim($dat['name']),
                    'mobile'=>trim($dat['mobile']),
                    'postal_code'=>intval($dat['postal_code']),
                    'address'=>trim($dat['address']),
                    'type'=>intval($dat['type']),
                    'country_code'=>$dat['country_code'],
                ]);
            }
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = ['warehouse_name'=>'','name'=>'','mobile'=>'','postal_code'=>'','address'=>'','type'=>'','country_code'=>''];
            if($id>0){
                $data = Db::name('centralize_warehouse_list')->where('id',$id)->find();
            }
            $country_code = Db::name('country_code')->where(' code_name != "无" ')->select();
            return view('',compact('id','data','country_code'));
        }
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
                if($v['scope']==1){
                    $data[$k]['scope'] = '集运订单';
                }elseif($v['scope']==2){
                    $data[$k]['scope'] = '商城订单';
                }

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
                    'scope'=>intval($dat['scope']),
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
                    'scope'=>intval($dat['scope']),
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