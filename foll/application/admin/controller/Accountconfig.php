<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Db;
use think\Request;


class Accountconfig extends Auth
{

    public function file_upload(Request $request)
    {
        $path = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'account';
        $file = request()->file('file');
        if( $file )
        {
            $info = $file->rule('uniqid')->move($path);
            if( $info )
            {
                return json(["code" => 1, "message" => "上传成功", "path" => '/foll/public/uploads/account/'.$info->getSaveName() ]);
            }else{
                return json(["code" => 0, "message" => "上传失败", "path" => "" ]);
            }
            
        }else{
            return json(["code" => 0, "message" => "请先上传文件！"]);
        }
        
    }

    // 离岸账户
    public function offshore_account(Request $request)
    {
        if ( request()->isPost() || request()->isAjax())
        {
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $list = Db::name('offshore_account')->order($order)->limit($limit)->select();
            foreach ($list as $k => $v) {
                $list[$k]['account_types'] = $v['account_types'] == 1 ? '全球账户' : '本地账户';
                $list[$k]['create_at'] = date('Y-m-d H:i:s',$v['create_at']);
                $list[$k]['manage'] = '<button type="button" onclick="editInfo('."'信息编辑','".Url('admin/accountConfig/offshore_account_edit')."'".','."'".$v['id']."'".')" class="btn btn-primary btn-xs">信息编辑</button>';
                $list[$k]['manage'] .= '<button type="button" onclick="delInfo('."'".$v['id']."'".')" class="btn btn-danger btn-xs">信息删除</button>';
            }
            $total = Db::name('offshore_account')->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }else{
            return view();
        }
    }

    public function offshore_account_add()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            unset($data['file']);
            $data['create_at'] = time();
            if( Db::name('offshore_account')->insert($data) )
            {
                return json(["status" => 1, "message" => "新增成功"]);
            }else{
                return json(["status" => 0, "message" => "新增失败"]);
            }
        }else{
            return view();
        }
    }

    public function offshore_account_edit()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            unset($data['file']);
            if( Db::name('offshore_account')->update($data) )
            {
                return json(["status" => 1, "message" => "修改成功"]);
            }else{
                return json(["status" => 0, "message" => "修改失败"]);
            }
        }else{
            $data = Db::name('offshore_account')->where('id',input('id'))->find();
            $this->assign('data', $data);
            return view();
        }
    }

    public function offshore_account_del()
    {
        $id = input('id');
        if( Db::name('offshore_account')->where('id',$id)->delete() )
        {
            return json(["status" => 1, "message" => "删除成功"]);
        }else{
            return json(["status" => 0, "message" => "删除失败"]);
        }
    }

    // 在岸账户
    public function onshore_account(Request $request)
    {
        if ( request()->isPost() || request()->isAjax())
        {
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $list = Db::name('onshore_account')->order($order)->limit($limit)->select();
            foreach ($list as $k => $v) {
                $list[$k]['type'] = $v['type'] == 1 ? '结汇提现' : '离岸转账';
                $list[$k]['account_type'] = $v['account_type'] == 1 ? '境内公户' : '境内私户';
                $list[$k]['create_at'] = date('Y-m-d H:i:s',$v['create_at']);
                $list[$k]['manage'] = '<button type="button" onclick="editInfo('."'信息编辑','".Url('admin/accountConfig/onshore_account_edit')."'".','."'".$v['id']."'".')" class="btn btn-primary btn-xs">信息编辑</button>';
                $list[$k]['manage'] .= '<button type="button" onclick="delInfo('."'".$v['id']."'".')" class="btn btn-danger btn-xs">信息删除</button>';
            }
            $total = Db::name('onshore_account')->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }else{
            return view();
        }
    }

    public function onshore_account_add()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            $data['create_at'] = time();
            if( Db::name('onshore_account')->insert($data) )
            {
                return json(["status" => 1, "message" => "新增成功"]);
            }else{
                return json(["status" => 0, "message" => "新增失败"]);
            }
        }else{
            return view();
        }
    }

    public function onshore_account_edit()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            if( Db::name('onshore_account')->update($data) )
            {
                return json(["status" => 1, "message" => "修改成功"]);
            }else{
                return json(["status" => 0, "message" => "修改失败"]);
            }
        }else{
            $data = Db::name('onshore_account')->where('id',input('id'))->find();
            $this->assign('data', $data);
            return view();
        }
    }

    public function onshore_account_del()
    {
        $id = input('id');
        if( Db::name('onshore_account')->where('id',$id)->delete() )
        {
            return json(["status" => 1, "message" => "删除成功"]);
        }else{
            return json(["status" => 0, "message" => "删除失败"]);
        }
    }


    // //商户银行账户审核
    // public function business_account()
    // {
    //     if ( request()->isPost() || request()->isAjax())
    //     {
    //         // 排序
    //         $order = input('sort').' '.input('order');
    //         // 分页
    //         $limit = input('offset').','.input('limit');

    //         $list = Db::name('decl_user_account')->order($order)->limit($limit)->select();
    //         foreach ($list as $k => $v) {
    //             switch ($v['status']) {
    //                 case 0:
    //                     $list[$k]['status'] = '审核中';
    //                 break;
    //                 case 1:
    //                     $list[$k]['status'] = '使用中';
    //                 break;
    //                 case 2:
    //                     $list[$k]['status'] = '审核不通过';
    //                 break;
    //                 case 3:
    //                     $list[$k]['status'] = '已冻结';
    //                 break;
    //             }
    //             $account_type = $v['account_type'] == 1 ? '结汇提现账户' : '外币转账账户';
    //             $account_type2 = $v['account_type2'] == 1 ? '法人账户' : '企业账户';
    //             $list[$k]['user_name'] = Db::name('decl_user')->where('id',$v['uid'])->value('user_name');
    //             $list[$k]['account_type_text'] =  $account_type.'-'. $account_type2;
    //             $list[$k]['create_at'] = date('Y-m-d H:i:s',$v['create_at']);
    //             $list[$k]['manage'] = '<button type="button" onclick="seeInfo('."'账户审核','".Url('admin/abroadConfig/bussiness_account_check')."'".','."'".$v['id']."'".')" class="btn btn-primary btn-xs">账户审核</button>';
    //             $list[$k]['manage'] .= '<button type="button" onclick="delInfo('."'".$v['id']."'".')" class="btn btn-danger btn-xs">账户冻结</button>';
    //         }
    //         $total = Db::name('decl_user_account')->count();
    //         return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
    //     }else{
    //         return view();
    //     }
    // }

    // //审核
    // public function bussiness_account_check()
    // {
    //     if ( request()->isPost() || request()->isAjax())
    //     {
    //         $data = input();
    //         if( Db::name('decl_user_account')->update($data) )
    //         {
    //             return json(["status" => 1, "message" => "审核成功"]);
    //         }else{
    //             return json(["status" => 0, "message" => "提交失败"]);
    //         }
    //     }else{
    //         $data = Db::name('decl_user_account')->where('id',input('id'))->find();
    //         $data['user_name'] = Db::name('decl_user')->where('id',$data['uid'])->value('user_name');
    //         switch ($data['status']) {
    //             case 0:
    //                 $status = '审核中';
    //             break;
    //             case 1:
    //                 $status = '使用中';
    //             break;
    //             case 2:
    //                 $status = '审核不通过';
    //             break;
    //             case 3:
    //                 $status = '已冻结';
    //             break;
    //         }
    //         $data['status'] = $status;
    //         $this->assign('data', $data);
    //         return view();
    //     }
    // }

    // //冻结
    // public function business_account_frozen()
    // {
    //     $id = input('id');
    //     if( Db::name('decl_user_account')->where('id',$id)->update(['status'=>3]) )
    //     {
    //         return json(["status" => 1, "message" => "已冻结"]);
    //     }else{
    //         return json(["status" => 0, "message" => "提交失败"]);
    //     }
    // }
}