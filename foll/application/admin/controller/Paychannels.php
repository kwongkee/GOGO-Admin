<?php

namespace app\admin\controller;
use think\Db;
use think\Request;

class Paychannels extends Auth
{
    // 支付渠道列表
    public function paylist() {

        $list = Db::name('customs_pay_list')->select();

        $this->assign('list', $list);
        return view('paychannels/index',
            ['title'=>'']
        );
    }

    // 编辑
    public function payedit(Request $req) {

        $id = $req->get('id');
        if(!$id) {
            return view('paychannels/add');
        }

        // 查询数据
        $info = Db::name('customs_pay_list')->where('id',$id)->find();
        $this->assign('info',$info);

        return view('paychannels/edit');
    }


    // 编辑/添加
    public function payDoadd(Request $req) {
        // 保存数据
        $data = $req->post();
        if(empty($data)) {
            return json(['code'=>0,'msg'=>'请求错误！']);
        }

        $method = $data['method'];
        unset($data['method']);

        if($method == 'edit') {
            $id = $data['id'];
            unset($data['id']);
            $up = Db::name('customs_pay_list')->where('id',$id)->update($data);
        } else if($method == 'add') {
            $up = Db::name('customs_pay_list')->insert($data);
        } else {
            return json(['code'=>0,'msg'=>'处理失败!']);
        }

        if(!$up) {
            return json(['code'=>0,'msg'=>'数据处理失败!']);
        }

        // 成功返回
        return json(['code'=>1,'msg'=>'数据处理成功']);
    }

    // 删除支付通道
    public function paydel(Request $req) {

        $id = $req->post('id');
        if(!$id) {
            return json(['code'=>0,'msg'=>'操作失败']);
        }

        $del = Db::name('customs_pay_list')->where('id',$id)->delete();
        if(!$del){
            return json(['code'=>0,'msg'=>'删除失败!']);
        }

        return json(['code'=>1,'msg'=>'删除成功']);
    }




    // 商户号渠道列表
    public function merlist() {

        // 获取商户号列表
        $info = Db::name('customs_pay_mer')->paginate(8);

        // 支付渠道列表
        $cates = $this->getPaycase();

        $this->assign('cates',$cates);
        $this->assign('info',$info);

        return view('paychannels/mer/index');
    }


    // 编辑
    public function meredit(Request $req) {

        $id = $req->get('id');
        // 支付渠道列表
        $cates = $this->getPaycase();

        if(!$id) {
            $this->assign('cates',$cates);
            // 渲染页面
            return view('paychannels/mer/add');
        }

        // 查询数据   2020-03-09  商户配置编辑还未作；配合查询pay_list
        $info = Db::name('customs_pay_mer')->where('id',$id)->find();

        $this->assign('cates',$cates);
        $this->assign('info',$info);

        return view('paychannels/mer/edit');
    }

    // 编辑添加
    public function merDoadd(Request $req) {
        // 保存数据
        $data = $req->post();
        if(empty($data)) {
            return json(['code'=>0,'msg'=>'请求错误！']);
        }

        $method = $data['method'];
        unset($data['method']);

        if($method == 'edit') {

            $id = $data['id'];
            unset($data['id']);
            $data['utime'] = time();
            $up = Db::name('customs_pay_mer')->where('id',$id)->update($data);

        } else if($method == 'add') {
            $data['atime'] = time();
            $up = Db::name('customs_pay_mer')->insert($data);
        } else {
            return json(['code'=>0,'msg'=>'处理失败!']);
        }

        if(!$up) {
            return json(['code'=>0,'msg'=>'数据处理失败!']);
        }

        // 成功返回
        return json(['code'=>1,'msg'=>'数据处理成功']);
    }


    // 删除支付通道
    public function merdel(Request $req) {

        $id = $req->post('id');
        if(!$id) {
            return json(['code'=>0,'msg'=>'操作失败']);
        }

        $del = Db::name('customs_pay_mer')->where('id',$id)->delete();
        if(!$del){
            return json(['code'=>0,'msg'=>'删除失败!']);
        }

        return json(['code'=>1,'msg'=>'删除成功']);
    }



    // 获取支付列表
    protected function getPaycase() {
        // 获取支付渠道名称
        $paylist = Db::name('customs_pay_list')->select();
        foreach ($paylist as $k=>$v) {
            $cates[$v['id']] = $v['PayEntName'].'-'.$v['remark'];
        }
        return $cates;
    }
}