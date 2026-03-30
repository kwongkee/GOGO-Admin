<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Db;
use think\Request;

// 风控管理
class Riskconfig extends Auth
{

    // 制裁国家配置
    // public function sanction_country()
    // {
    //     if ( request()->isPost() || request()->isAjax())
    //     {
    //         // 排序
    //         $order = input('sort').' '.input('order');
    //         // 分页
    //         $limit = input('offset').','.input('limit');

    //         $list = Db::name('sanction_country')->order($order)->limit($limit)->select();
    //         foreach ($list as $k => $v) {
                
    //             $list[$k]['create_at'] = date('Y-m-d H:i:s',$v['create_at']);
    //             $list[$k]['manage'] = '<button type="button" onclick="editInfo('."'编辑','".Url('admin/riskConfig/sanction_country_edit')."'".','."'".$v['id']."'".')" class="btn btn-primary btn-xs">编辑</button>';
    //             $list[$k]['manage'] .= '<button type="button" onclick="delInfo('."'".$v['id']."'".')" class="btn btn-danger btn-xs">删除</button>';
    //         }
    //         $total = Db::name('sanction_country')->count();
    //         return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
    //     }else{
    //         return view();
    //     }
    // }

    public function sanction_country()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            if( Db::name('sanction_country')->where('id',1)->update($data) )
            {
                return json(["status" => 1, "message" => "保存成功"]);
            }else{
                return json(["status" => 0, "message" => "保存失败"]);
            }
        }else{
            // 国家列表
            $country = Db::name('country_code')->where('code_value','neq','000')->select();
            $this->assign('country', json_encode($country));
            $datas = Db::name('sanction_country')->where('id',1)->value('country_code');
            $this->assign('datas', $datas);
            return view();
        }
    }

    public function sanction_bank()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            if( Db::name('sanction_bank')->where('id',1)->update($data) )
            {
                return json(["status" => 1, "message" => "保存成功"]);
            }else{
                return json(["status" => 0, "message" => "保存失败"]);
            }
        }else{
            // 银行列表
            $bank = Db::name('bank_list')->select();
            $this->assign('bank', json_encode($bank));
            $datas = Db::name('sanction_bank')->where('id',1)->value('bank_code');
            $datas = explode(",", $datas);
            $result = '';
            foreach ($datas as $k => $v) {
                if( ($k+1) == count($datas) )
                {
                    $result .= "'".$v."'";
                }else{
                    $result .= "'".$v."',";
                }
            } 

            $this->assign('datas', $result );
            return view();
        }
    }

    public function sanction_country_add()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            $data['create_at'] = time();
            if( Db::name('sanction_country')->insert($data) )
            {
                return json(["status" => 1, "message" => "新增成功"]);
            }else{
                return json(["status" => 0, "message" => "新增失败"]);
            }
        }else{
            return view();
        }
    }

    public function sanction_country_edit()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            if( Db::name('sanction_country')->update($data) )
            {
                return json(["status" => 1, "message" => "修改成功"]);
            }else{
                return json(["status" => 0, "message" => "修改失败"]);
            }
        }else{
            $data = Db::name('sanction_country')->where('id',input('id'))->find();
            $this->assign('data', $data);
            return view();
        }
    }

    public function sanction_country_del()
    {
        $id = input('id');
        if( Db::name('sanction_country')->where('id',$id)->delete() )
        {
            return json(["status" => 1, "message" => "删除成功"]);
        }else{
            return json(["status" => 0, "message" => "删除失败"]);
        }
    }

    // 高风国地配置
    public function highrisk_country()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            if( Db::name('highrisk_country')->where('id',1)->update($data) )
            {
                return json(["status" => 1, "message" => "保存成功"]);
            }else{
                return json(["status" => 0, "message" => "保存失败"]);
            }
        }else{
            // 国家列表
            $country = Db::name('country_code')->where('code_value','neq','000')->select();
            $this->assign('country', json_encode($country));
            $datas = Db::name('highrisk_country')->where('id',1)->value('country_code');
            $this->assign('datas', $datas);
            return view();
        }
    }

    // 三涉税号
    public function three_tax()
    {
        if ( request()->isPost() || request()->isAjax())
        {

        }else{
            return view();
        }
    }

    
    

}