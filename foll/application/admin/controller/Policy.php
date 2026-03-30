<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Db;
use think\Request;


class Policy extends Auth
{
    public function datalist()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $map = array();

            $search = input('search');
            if($search)
            {
                $map['tite'] = ['like','%'.$search.'%'];
            }

            $list = Db::name('policies')->where($map)->order($order)->limit($limit)->select();
            foreach ($list as $k => $v) {
                switch ($v['type']) {
                    case 1 :
                        $list[$k]['type'] = '跨境結算';
                    break;
                    case 2 :
                        $list[$k]['type'] = '跨境物流';
                    break;
                    case 3 :
                        $list[$k]['type'] = '跨境通關';
                    break;
                    case 4 :
                        $list[$k]['type'] = '跨境銷售';
                    break;
                }
                $list[$k]['create_at'] = date('Y-m-d H:i:s',$v['create_at']);
                $list[$k]['manage'] = '<button type="button" style="margin-right: 5px;" onclick="edit('."'编辑','".Url('admin/policy/edit')."'".','."'".$v['id']."'".')" class="btn btn-primary btn-xs">编辑</button>';
                $list[$k]['manage'] .= '<button type="button" onclick="del('."'".$v['id']."'".')" class="btn btn-danger btn-xs">删除</button>';
                
            }
            $total = Db::name('policies')->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }else{
            return view();
        }
    }

    public function add()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            $data['create_at'] = time();
            if( Db::name('policies')->insert($data) )
            {
                return json(['status'=>1,'message'=>'新增成功']);
            }else{
                return json(['status'=>0,'message'=>'新增失败']);
            }
        }else{
            return view();
        }
    }

    public function edit()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            if( Db::name('policies')->update($data) )
            {
                return json(['status'=>1,'message'=>'编辑成功']);
            }else{
                return json(['status'=>0,'message'=>'编辑失败']);
            }
        }else{
            $id = input('id');
            $data = Db::name('policies')->where('id',$id)->find();
            $this->assign('data', $data);
            return view();
        }
    }

    public function del()
    {
        $id = input('id');
        if( Db::name('policies')->where('id',$id)->delete() )
        {
            return json(['status'=>1,'message'=>'删除成功']);
        }else{
            return json(['status'=>0,'message'=>'删除失败']);
        }
    }
}