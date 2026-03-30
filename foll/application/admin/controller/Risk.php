<?php

namespace app\admin\controller;
use think\Request;
use think\Db;

class Risk extends Auth{

    // 配置风控管理
    public function Configs()
    {
        $type = ['batch'=>'单批次','month'=>'月次','year'=>'年次','history'=>'历史累计'];
        // 百分比：Percen
        $data = DB::name('customs_riskconfig')->select();
        $this->assign('data',$data);
        $this->assign('type',$type);
        return view('elec/riskconfig',['title'=>'风控配置']);
    }

    // 编辑峰值 get
    public function Edit()
    {
        $id = input('ids');
        $data = DB::name('customs_riskconfig')->where(['id'=>$id])->find();
        $this->assign('data',$data);
        return view('elec/riskedit',['title'=>'风控配置']);
    }

    // 保存设置 post
    public function Store(Request $request)
    {
        if(!$request->isPost()){
            return json_encode(['msg'=>'请求方式不正确','code'=>2]);
        }

        $data = $request->post();
        $id   = $data['id'];
        unset($data['id']);
        foreach($data as $v) {
            if(empty($v)) {
                return json_encode(['msg'=>'数据不能为空','code'=>2]);
                break;
            }
        }
        $data['up_time'] = date('Y-m-d H:i:s',time());

        DB::name('customs_riskconfig')->where(['id'=>$id])->update($data);

        return json_encode(['msg'=>'编辑成功！','code'=>1]);
    }

}



?>