<?php
namespace app\admin\controller;

//use think\Controller;
use think\Request;
use think\Db;
use app\admin\controller;
use think\Session;

class Business extends Auth
{
    public function overdue_setting(Request $request){
        $dat = input();
        if($request->isPost() || $request->isAjax()){
            $res = DB::name('website_business_payset')->where(['id'=>1])->update([
                'pay_term'=>trim($dat['pay_term']),
                'overdue_rate'=>trim($dat['overdue_rate']),
            ]);
            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $payset = DB::name('website_business_payset')->where(['id'=>1])->find();
            return view('',compact('payset'));
        }
    }

    public function buss_list(Request $request)
    {
        $dat = input();

        if($request->isPost() || $request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('website_business_type')->order($order)->count();
            $rows = DB::name('website_business_type')
                ->limit($limit)
                ->order($order)
                ->select();

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    #新增业务
    public function save_buss(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isPost() || $request->isAjax()){
            if($id>0){
                $res = Db::name('website_business_type')->where(['id'=>$id])->update(['name'=>trim($dat['name'])]);
            }else{
                $res = Db::name('website_business_type')->insert(['name'=>trim($dat['name'])]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $data = Db::name('website_business_type')->where(['id'=>$id])->find();
            return view('',compact('id','data'));
        }
    }

    #删除业务
    public function del_buss(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $res = Db::name('website_business_type')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'操作成功']);
        }
    }

    #保存业务账单表头
    public function save_head(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;//业务id
//        dd(json_encode(['物流线路'],true));
        if($request->isPost() || $request->isAjax()){
            #保存账单业务表头
            $content = [];

            $label_name = $dat['content']['label_name'];
            foreach($label_name as $k=>$v){
                array_push($content,[
                    'label_name'=>$v,
                    'label_value'=>$dat['content']['label_value'][$k],#元素id
                    'label_select'=>$dat['content']['label_select'][$k],#选择框
                    'label_rand'=>$dat['content']['label_rand'][$k],#时间框
                    'label_introduce'=>$dat['content']['label_introduce'][$k],#介绍框
                ]);
            }
            $ishave = Db::name('website_business_head')->where(['pid'=>$id])->find();
            if(empty($ishave)){
                $res = Db::name('website_business_head')->insert([
                    'pid'=>$id,
                    'content'=>json_encode($content,true),
                ]);
            }else{
                $res = Db::name('website_business_head')->where(['pid'=>$id])->update([
                    'content'=>json_encode($content,true),
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $data = Db::name('website_business_head')->where(['pid'=>$id])->find();
            if(empty($data)){
                $data['content'] = '';
            }else{
                $data['content'] = json_decode($data['content'],true);
                foreach($data['content'] as $k=>$v){
                    if($v['label_value']==7){
                        $data['content'][$k]['label_select2'] = explode(',',$v['label_select']);
                        if(!empty($v['label_introduce'])){
                            $data['content'][$k]['label_introduce2'] = explode('、',rtrim($v['label_introduce'],'、'));
                        }
                    }
                }
//                dd($data['content']);
            }
            return view('',compact('id','data'));
        }
    }

    #获取指定格式的数据
    public function gettableinfo(Request $request){
        $dat = input();
        $list = '';
        if($dat['id']==1){
            #各国地区
            if(session('country_list')==''){
                $country = get_country();//州-国
                $list = get_country_area($country);
                session('country_list',$list);
            }else{
                $list = session('country_list');
            }
        }elseif($dat['id']==2){
            #各国线路
            if(session('line_list')==''){
                $country = get_country();//州-国
                $list = get_country_line($country);
                session('line_list',$list);
            }else{
                $list = session('line_list');
            }
        }elseif($dat['id']==3){
            #货物类别
            if(session('value_list')==''){
                $list = get_product();//州-国
                session('value_list',$list);
            }else{
                $list = session('value_list');
            }
        }

        return json(['code'=>0,'list'=>$list]);
    }
}