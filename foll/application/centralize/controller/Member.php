<?php
/**
 * 集运系统后台管理（会员管理）
 * 2022-07-05
 */
namespace app\centralize\controller;

use think\Controller;
use think\Db;
use think\Request;

class Member extends  Controller{

    public function lists(Request $request){
        $dat = input();
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');
            $where = [];
            if(!empty($search)){
                $where['name'] = ['like','%'.$search.'%'];
            }
            $total = DB::name('centralize_user')->where($where)->count();
            $data = DB::name('centralize_user')
                ->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach($data as $k=>&$v){
                if(!empty($v['agentid'])){
                    $agent = Db::name('centralize_user')->where('id',$v['agentid'])->find();
                    $data[$k]['agent_name'] = $agent['name'].'(ID：'.$agent['id'].')';
                }else{
                    $v['agent_name'] = '平台';
                }
                if($v['status']==1){
                    $v['status']='禁用中';
                }elseif(empty($v['status'])){
                    $v['status']='使用中';
                }
                $v['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
                if(empty($v['vip_grade'])){
                    $v['vip_grade'] = '普通会员';
                }else{
                    $v['vip_grade'] = Db::name('centralize_user_grade')->where('id',$v['vip_grade'])->find()['name'];
                }
            }

            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    public function member_info(Request $request){
        $dat = input();
        $id = $dat['id'];
        if($request->isAjax()){
            $res = Db::name('centralize_user')->where('id',$id)->update([
                'vip_grade'=>intval($dat['vip_grade']),
                'balance'=>intval($dat['balance']),
                'points'=>trim($dat['points']),
                'status'=>intval($dat['status']),
            ]);
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = Db::name('centralize_user')->where('id',$id)->find();
            $level = Db::name('centralize_user_grade')->select();
            return view('',compact('level','data'));
        }
    }

    public function member_data_detail(Request $request){
        $dat = input();
        $name = trim($dat['name']);
        $id = intval($dat['id']);
        $type = intval($dat['type']);
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $total = DB::name('centralize_balance_points_detail')->where(['user_id'=>$id,'type'=>$type])->count();
            $data = DB::name('centralize_balance_points_detail')
                ->where(['user_id'=>$id,'type'=>$type])
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach($data as $k=>&$v){
                if($v['type']==1){
                    $v['type']='余额';
                }elseif($v['type']==2){
                    $v['type']='积分';
                }
                $v['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
                $v['name'] = $name;
            }

            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('',compact('id','type','name'));
        }
    }

    public function member_del(Request $request){
        $dat = input();
        if(isset($dat['id'])){
            $res = Db::name('centralize_user')->where('id',$dat['id'])->delete();
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }
    }

    public function member_grade_list(Request $request){
        $dat = input();
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');
            $where = [];
            if(!empty($search)){
                $where['name'] = ['like','%'.$search.'%'];
            }
            $total = DB::name('centralize_user_grade')->where($where)->count();
            $data = DB::name('centralize_user_grade')
                ->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach($data as $k=>&$v){
                $v['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    public function member_grade_save(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){
            if($id>0){
                $res = Db::name('centralize_user_grade')->where('id',$id)->update([
                    'name'=>trim($dat['name']),
                    'discount'=>trim($dat['discount']),
                    'price'=>trim($dat['price']),
                ]);
            }else{
                $res = Db::name('centralize_user_grade')->insert([
                    'name'=>trim($dat['name']),
                    'discount'=>trim($dat['discount']),
                    'price'=>trim($dat['price']),
                    'createtime'=>time()
                ]);
            }
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = ['name'=>'','discount'=>'','price'=>''];
            if($id>0){
                $data = Db::name('centralize_user_grade')->where('id',$id)->find();
            }
            return view('',compact('id','data'));
        }
    }

    public function member_grade_del(Request $request){
        $dat = input();
        if(isset($dat['id'])){
            $res = Db::name('centralize_user_grade')->where('id',$dat['id'])->delete();
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }
    }

    public function member_grade_detail(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;

            $total = DB::name('centralize_user_grade_detail')->where('grade_id',$id)->count();
            $data = DB::name('centralize_user_grade_detail')
                ->where('grade_id',$id)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach($data as $k=>&$v){
                if($v['type']==1){
                    $v['type'] = '余额支付';
                }else{
                    $v['type'] = '线上支付';
                }
                $v['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
                $user = Db::name('centralize_user')->where('id',$v['user_id'])->find();
                $v['name'] = $user['name'].'（ID：'.$user['id'].'）';
            }

            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('',compact('id'));
        }
    }

}