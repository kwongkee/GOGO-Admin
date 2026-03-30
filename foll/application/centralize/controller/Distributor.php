<?php
/**
 * 集运系统后台管理（分销商管理）
 * 2022-07-08
 */
namespace app\centralize\controller;

use think\Controller;
use think\Db;
use think\Request;

class Distributor extends Controller{

    public function lists(Request $request){
        $dat = input();
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');
            $where = [];
            $where['agent_status'] = ['neq',0];
            if(!empty($search)){
                $where['name'] = ['like','%'.$search.'%'];
            }
            $total = DB::name('centralize_user')->where($where)->count();
            $data = DB::name('centralize_user')
                ->where($where)
                ->limit($page,$limit)
                ->order('agent_time', 'desc')
                ->select();

            foreach($data as $k=>&$v){
                if(!empty($v['agentid'])){
                    $agent = Db::name('centralize_user')->where('id',$v['agentid'])->find();
                    $data[$k]['agent_name'] = $agent['name'].'(ID：'.$agent['id'].')';
                }else{
                    $v['agent_name'] = '平台';
                }
                if($v['agent_status']==1){
                    $v['agent_status']='待审核';
                }elseif($v['agent_status']==2){
                    $v['agent_status']='审核通过';
                }elseif($v['agent_status']==-1){
                    $v['agent_status']='审核不通过';
                }
                $v['agent_time'] = date('Y-m-d H:i:s',$v['agent_time']);
                if(empty($v['vip_grade'])){
                    $v['vip_grade'] = '普通会员';
                }else{
                    $v['vip_grade'] = Db::name('centralize_user_grade')->where('id',$v['vip_grade'])->find()['name'];
                }

                //无限查找下线（这里到时候要做）
                $v['child_num'] = Db::name('centralize_user')->where('agentid',$v['id'])->count();
            }

            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    public function check(Request $request){
        $dat = input();
        $id = $dat['id'];
        if($request->isAjax()){
            $res = Db::name('centralize_user')->where('id',$dat['id'])->update(['agent_status'=>$dat['agent_status'],'agent_remark'=>$dat['agent_remark']]);
            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            return view('',compact('id'));
        }
    }

    public function commission_detail(Request $request){
        $dat = input();
        $id = $dat['id'];
        $agent_name = $dat['name'];
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');
            $where = [];
            $where['agent_id'] = $id;
            if(!empty($search)){
                $where['order_no'] = ['like','%'.$search.'%'];
            }
            $total = DB::name('centralize_commission_detail')->where($where)->count();
            $data = DB::name('centralize_commission_detail')
                ->where($where)
                ->limit($page,$limit)
                ->order('agent_time', 'desc')
                ->select();

            foreach($data as $k=>&$v){
                if(!empty($v['buyer_id'])){
                    $buyer = Db::name('centralize_user')->where('id',$v['buyer_id'])->find();
                    $data[$k]['buyer_name'] = $buyer['name'].'(ID：'.$buyer['id'].')';
                }
                $v['agent_name'] = $agent_name;
                if($v['status']==1){
                    $v['status']='已分佣';
                }elseif(empty($v['status'])){
                    $v['status']='未分佣';
                }

                $v['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('',compact('id','agent_name'));
        }
    }

    public function child_detail(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        if($request->isAjax()){

        }else{
            return view('',compact(''));
        }
    }

    public function withdrawal_apply_list(Request $request){
        $dat = input();
        if($request->isAjax()) {
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');
            $where = [];
            if(!empty($search)){
                $where['agent_name'] = ['like','%'.$search.'%'];
            }

            $total = DB::name('centralize_commission_withdraw')->count();
            $data = DB::name('centralize_commission_withdraw')
                ->where($where)
                ->limit($page,$limit)
                ->order('createtime', 'desc')
                ->select();

            foreach($data as $k=>&$v){
                if($v['status']==1){
                    $v['status']='提现中';
                }elseif($v['status']==2){
                    $v['status']='提现成功';
                }elseif($v['status']==3){
                    $v['status']='提现失败';
                }

                $v['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    public function withdraw_apply(Request $request){
        $dat = input();
        $id = $dat['id'];
        if($request->isAjax()){
            $res = Db::name('centralize_commission_withdraw')->where('id',$dat['id'])->update(['status'=>$dat['status'],'remark'=>trim($dat['remark'])]);
            //将佣金充值到余额（待做）
            //...
            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            return view('',compact('id'));
        }
    }
}