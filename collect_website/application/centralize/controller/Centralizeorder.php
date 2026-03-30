<?php
/**
 * 集运系统后台管理（集运订单管理）
 * 2022-07-12
 */
namespace app\centralize\controller;

use think\Controller;
use think\Db;
use think\Request;

class Centralizeorder extends Controller{
    public function no_warehouse(Request $request){
        $dat = input();
        if($request->isAjax()) {
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');
            $where['status'] = 0;
            if(!empty($search)){
                $where['express_no'] = ['like','%'.$search.'%'];
            }
            $total = DB::name('centralize_parcel_order')->where($where)->count();
            $data = Db::name('centralize_parcel_order')
                ->where($where)
                ->limit($page,$limit)
                ->order('createtime', 'desc')
                ->select();
            if(!empty($data)){
                foreach($data as $k=>&$v){
                    $user_name = Db::name('centralize_user')->where(['id'=>$v['user_id']])->find()['name'];
                    $v['member_info'] = $user_name.'（ID：'.$v['user_id'].'）';
                    $v['country_name'] = Db::name('country_code')->where(['code_value'=>$v['country_code']])->find()['code_name'];
                    $v['warehouse_name'] = Db::name('centralize_warehouse_list')->where(['id'=>$v['warehouse_id']])->find()['warehouse_name'];
                    $v['express_name'] = Db::name('customs_express_company_code')->where(['id'=>$v['express_id']])->find()['name'];
                    $v['createtime'] = date('Y-m-d H:i',$v['createtime']);
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    public function no_warehouse_check(Request $request){
        $dat = input();
        $id = $dat['id'];
        if($request->isAjax()){
            $res = Db::name('centralize_parcel_order')->where('id',$dat['id'])->update(['status'=>1]);
            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            return view('',compact('id'));
        }
    }

    public function parcel_order_detail(Request $request){
        $dat = input();
        $id = $dat['id'];
        if($request->isAjax()){

        }else{
            $data = Db::name('centralize_parcel_order')->where('id',$id)->find();

            $user_info = Db::name('centralize_user')->where(['id'=>$data['user_id']])->find();
            $country_info = Db::name('country_code')->select();
            $warehouse_info = Db::name('centralize_warehouse_list')->select();
            $express_info = Db::name('customs_express_company_code')->select();
            $jd_goods_category = Db::name('jd_goods_category')->select();
            $good_item = Db::name('centralize_goods_value')->select();
            $good_item = json_encode($good_item,true);

            return view('',compact('data','user_info','country_info','warehouse_info','express_info','jd_goods_category','good_item'));
        }
    }

    public function in_warehouse(Request $request){
        $dat = input();
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');
            $where['status'] = 1;
            if(!empty($search)){
                $where['express_no'] = ['like','%'.$search.'%'];
            }
            $total = DB::name('centralize_parcel_order')->where($where)->count();
            $data = Db::name('centralize_parcel_order')
                ->where($where)
                ->limit($page,$limit)
                ->order('createtime', 'desc')
                ->select();
            if(!empty($data)){
                foreach($data as $k=>&$v){
                    $user_name = Db::name('centralize_user')->where(['id'=>$v['user_id']])->find()['name'];
                    $v['member_info'] = $user_name.'（ID：'.$v['user_id'].'）';
                    $v['country_name'] = Db::name('country_code')->where(['code_value'=>$v['country_code']])->find()['code_name'];
                    $v['warehouse_name'] = Db::name('centralize_warehouse_list')->where(['id'=>$v['warehouse_id']])->find()['warehouse_name'];
                    $v['express_name'] = Db::name('customs_express_company_code')->where(['id'=>$v['express_id']])->find()['name'];
                    $v['createtime'] = date('Y-m-d H:i',$v['createtime']);
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    public function packing(Request $request){
        $dat = input();
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');
            $where['status'] = 0;
            if(!empty($search)){
                $where['package_no'] = ['like','%'.$search.'%'];
            }
            $total = DB::name('centralize_parcel_merge_order')->where($where)->count();
            $data = Db::name('centralize_parcel_merge_order')
                ->where($where)
                ->limit($page,$limit)
                ->order('createtime', 'desc')
                ->select();

            if(!empty($data)){
                foreach($data as $k=>&$v){
                    $user_name = Db::name('centralize_user')->where(['id'=>$v['user_id']])->find()['name'];
                    $v['member_info'] = $user_name.'（ID：'.$v['user_id'].'）';
                    $line_info = Db::name('centralize_extra_value_line')->where(['id'=>$v['line_id']])->find();
                    $v['line_info'] = $line_info['name'].'（ID：'.$v['line_id'].'）';
                    $v['createtime'] = date('Y-m-d H:i',$v['createtime']);
                    if($v['method_id']==1){
                        $v['method_id'] = '配送上门';
                    }elseif($v['method_id']==2){
                        $v['method_id'] = '定点自提';
                    }elseif($v['method_id']==3){
                        $v['method_id'] = '仓库自提';
                    }
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    public function packing_check(Request $request){
        $dat = input();
        $id = $dat['id'];
        if($request->isAjax()){
            $res = Db::name('centralize_parcel_merge_order')->where('id',$dat['id'])->update(['status'=>1]);
            //修改待打包包裹下的快递订单信息为待付款状态
            //。。。（待做）

            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            return view('',compact('id'));
        }
    }

    public function parcel_merge_order_detail(Request $request){
        $dat = input();
        $id = $dat['id'];
        if($request->isAjax()){

        }else{
            $data = Db::name('centralize_parcel_merge_order')->where('id',$id)->find();
            $parcel_ids = explode(',',$data['parcel_ids']);
            foreach($parcel_ids as $k=>$v){
                if(!empty($v)){
                    $data['parcel_order'][$k] = Db::name('centralize_parcel_order')->where('id',$v)->find();
                }
            }

            $user_name = Db::name('centralize_user')->where(['id'=>$data['user_id']])->find()['name'];
            $data['member_info'] = $user_name.'（ID：'.$data['user_id'].'）';
            $line_info = Db::name('centralize_extra_value_line')->where(['id'=>$data['line_id']])->find();
            $data['line_info'] = $line_info['name'].'（ID：'.$data['line_id'].'）';

            if($data['method_id']==1){
                $data['method_name'] = '配送上门';
                $data['address_info'] = Db::name('centralize_user_address')->where('id',$data['address_id'])->find();
                $data['address_info']['country_id'] = Db::name('country_code')->where('code_value',$data['address_info']['country_id'])->find()['code_name'];
                $data['address_info']['country_id2'] = Db::name('centralize_hongkong_code')->where('id',$data['address_info']['country_id2'])->find()['code_name'];
                $data['address_info']['country_id3'] = Db::name('centralize_hongkong_code')->where('id',$data['address_info']['country_id3'])->find()['code_name'];
                if($data['address_type']==1){
                    $data['address_type'] = '住宅专线';
                }elseif($data['address_type']==2){
                    $data['address_type'] = '工商专线';
                }elseif($data['address_type']==3){
                    $data['address_type'] = '特惠专线';
                }
            }elseif($data['method_id']==2){
                $data['method_name'] = '定点自提';
                $data['pick_point_id'] = Db::name('centralize_self_lift_point')->where('id',$data['pick_point_id'])->find();
            }elseif($data['method_id']==3){
                $data['method_name'] = '仓库自提';
                $data['warehouse_id'] = Db::name('centralize_warehouse_list')->where('id',$data['warehouse_id'])->find();
            }

            if(!empty($data['pay_time'])){
                $data['pay_time'] = date('Y-m-d H:i:s',$data['pay_time']);
            }

            return view('',compact('id','data'));
        }
    }

    //待付款、待发货、已发货、待评价（已完成）
    public function all_order(Request $request){
        $dat = input();
        $op = $dat['op'];

        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');
            $where['status'] = $op;
            if(!empty($search)){
                $where['package_no'] = ['like','%'.$search.'%'];
            }
            $total = DB::name('centralize_parcel_merge_order')->where($where)->count();
            $data = Db::name('centralize_parcel_merge_order')
                ->where($where)
                ->limit($page,$limit)
                ->order('createtime', 'desc')
                ->select();

            if(!empty($data)){
                foreach($data as $k=>&$v){
                    $user_name = Db::name('centralize_user')->where(['id'=>$v['user_id']])->find()['name'];
                    $v['member_info'] = $user_name.'（ID：'.$v['user_id'].'）';
                    $line_info = Db::name('centralize_extra_value_line')->where(['id'=>$v['line_id']])->find();
                    $v['line_info'] = $line_info['name'].'（ID：'.$v['line_id'].'）';
                    $v['createtime'] = date('Y-m-d H:i',$v['createtime']);
                    if($v['method_id']==1){
                        $v['method_id'] = '配送上门';
                    }elseif($v['method_id']==2){
                        $v['method_id'] = '定点自提';
                    }elseif($v['method_id']==3){
                        $v['method_id'] = '仓库自提';
                    }
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('',compact('op'));
        }
    }

    public function deliver_goods(Request $request){
        $dat = input();
        $id = $dat['id'];
        if($request->isAjax()){
            $res = Db::name('centralize_parcel_merge_order')->where(['id'=>$id])->update(['express_id'=>$dat['express_id'],'express_no'=>trim($dat['express_no'])]);
            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $express = Db::name('customs_express_company_code')->select();
            return view('',compact('express','id'));
        }
    }

    public function return_goods_apply(Request $request){
        $dat = input();
        $id = $dat['id'];
        if($request->isAjax()){
            $res = Db::name('centralize_parcel_merge_order')->where('id',$dat['id'])->update(['return_status'=>$dat['status'],'return_remark'=>trim($dat['return_remark'])]);
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            return view('',compact('id'));
        }
    }

    //无件主认领列表
    public function no_main_part(Request $request){
        $dat = input();
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');
            $where = [];
            if(!empty($search)){
                $where['express_no'] = ['like','%'.$search.'%'];
            }
            $total = DB::name('centralize_no_main_part_list')->where($where)->count();
            $data = Db::name('centralize_no_main_part_list')
                ->where($where)
                ->limit($page,$limit)
                ->order('createtime', 'desc')
                ->select();

            if(!empty($data)){
                foreach($data as $k=>&$v){
//                    $user_name = Db::name('centralize_user')->where(['id'=>$v['user_id']])->find()['name'];
//                    $v['member_info'] = $user_name.'（ID：'.$v['user_id'].'）';
                    $v['createtime'] = date('Y-m-d H:i',$v['createtime']);
                    if(empty($v['status'])){
                        $v['status'] = '未认领';
                    }elseif($v['status']==1){
                        $v['status'] = '已认领';
                    }
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    public function no_main_part_save(Request $request){
        $dat = input();
        if($request->isAjax()){
            $res = Db::name('centralize_no_main_part_list')->insert([
                'express_id'=>intval($dat['express_id']),
                'express_no'=>trim($dat['express_no']),
                'status'=>0,
                'createtime'=>time()
            ]);
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
//            $express = Db::name('customs_express_company_code')->select();
            $express = Db::name('centralize_diycountry_content')->where(['pid'=>6])->select();
            return view('',compact('express'));
        }
    }

    public function no_main_part_del(Request $request){
        $dat = input();
        if(isset($dat['id'])){
            $res = Db::name('centralize_no_main_part_list')->where('id',$dat['id'])->delete();
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }
    }
}