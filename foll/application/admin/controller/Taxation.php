<?php
namespace app\admin\controller;

use think\Db;
use think\Request;
use Util\data\Sysdb;
use think\Session;
use think\loader;

/**
 * Class Finas
 * @package app\admin\controller
 * 财务预警管理
 */
class Taxation extends Auth {

    private $req;
    public function __construct(Request $request)
    {
        parent::__construct();
        $this->req = $request->param();
    }

    // 税率列表
    public function lists()
    {
        // 获取分类列表
        $cate = Db::name('customs_taxcate')->order('type asc')->select();
        // 把key 与值保存为一维数组
        $cates = [];
        foreach ($cate as $kv)
        {
            $cates[$kv['type']] = $kv['title'];
        }

        // 默认显示其他
        $type = isset($this->req['cate']) ? $this->req['cate'] : 'A';
        $config = [
            'query' => ['s' => 'admin/taxation/lists','cate'=>$type],
            'var_page' => 'page',
            'type' => 'Layui',
            'newstyle' => true
        ];
        // 查询 分页
        $list = Db::name('customs_taxnums')->where(['type'=>$type])->paginate(13, true,$config);
        // 分页
        $page = $list->render();

        return view('taxation/taxdis/index',[
                'title'=>'税率管理',
                'cate'=>$cate,
                'lists'=>$list,
                'page'=>$page,
                'cates'=>$cates,
                'type' =>$type
            ]);
    }


    // 降税优惠列表  customs_taxdislist
    public function taxDislist() {

        $config = [
            'query' => ['s' => 'admin/taxation/taxDislist'],
            'var_page' => 'page',
            'type' => 'Layui',
            'newstyle' => true
        ];
        // 查询 分页
        $taxdis = Db::name('customs_taxdislist')->paginate(13, true,$config);

        // 分页
        $page = $taxdis->render();

        $type = [
            'dis'=>'打折',
            'drop'=>'降税',
        ];

        // 加载前端页面
        return view('taxation/taxdis/taxdis',[
            'title'=>'降税优惠',
            'taxdis'=>$taxdis,
            'page'=>$page,
            'type'=>$type,
        ]);
    }

    // 更新添加降税优惠
    public function taxAdd(Request $req) {
        $data = $req->post();
        $formData = $data['formData'];
        // 新增，全部更新；
        if($formData['isAdd'] == 'yes') {

            // 获取所有数据，判断是否已经存在；存在不允许重复添加
            $taxdis = DB::name('customs_taxdislist')->where('original',$formData['original'])->find();
            // 判断是否存在
            if($taxdis) {
                echo json_encode(['code'=>0,'msg'=>'原税费已存在，请重新输入！']);
                exit();
            }

            // 新增
            $insData = [
                'original'=>$formData['original'],
                'news'=>$formData['news'],
                'type'=>$formData['type'],
                'in_time'=>time(),
                'up_time'=>time(),
            ];

            // 更新数据
            $up = DB::name('customs_taxdislist')->insert($insData);
            if(!$up) {
                echo json_encode(['code'=>0,'msg'=>'添加失败，请稍后再操作！']);
                exit();
            }

            echo json_encode(['code'=>1,'msg'=>'添加成功']);
            exit();

        } else { // 更新全部数据

            print_r($formData);
        }
    }

    // 删除
    public function taxDels(Request $req){
        $data = $req->post();
        $id   = $data['id'];

        $up = DB::name('customs_taxdislist')->where('id',$id)->delete();
        if(!$up) {
            echo json_encode(['code'=>0,'msg'=>'删除失败，请稍后再操作！']);
            exit();
        }
        echo json_encode(['code'=>1,'msg'=>'删除成功']);
        exit();
    }

    // 更新降税优惠
    public function taxEdit(Request $req) {
        $data = $req->post();
        $id   = $data['id'];
        $upData = [
            $data['name'] => $data['val']
        ];

        $up = DB::name('customs_taxdislist')->where('id',$id)->update($upData);
        if(!$up) {
            echo json_encode(['code'=>0,'msg'=>'更新失败，请稍后再操作！']);
            exit();
        }

        echo json_encode(['code'=>1,'msg'=>'更新成功']);
        exit();
    }

    // 导入操作
    public function imps(Request $request) {

        set_time_limit(0);

        if (!$request->file('file')) {
            return json(['code' => -1, 'msg' => '请选择文件上传']);
        }
        $path = ROOT_PATH . 'public' . DS . 'uploads';
        // 验证后缀并移动文件
        $file = $request->file('file')->validate(['ext' => ['xls','xlsx']])->move($path);
        if (!$file) {
            return json(['code' => -1, 'msg' => $file]);
        }

        try{
            // 加载模块；logic/Taxation
            $model = model('Taxation', 'logic');

            $fil = $model->saveCodeListTable($model->getFile($path.'/'.$file->getSaveName()));

            @unlink($path . '/' . $file->getSaveName());

            if(!$fil) {
                return json(['code' => -1, 'msg' => '上传失败',$fil]);
            }

            return json(['code' => 1, 'msg' => '上传完成',$fil]);

        } catch (\Exception $exception) {
            @unlink($path.'/'.$file->getSaveName());
            return json([
                'code'    => -1,
                'msg' => $exception->getMessage() . ':' . $exception->getLine() . ':' . $exception->getFile(),
            ]);
        }
    }

    // 编辑
    public function edit() {
        $id = $this->req['id'];
        $data = Db::name('customs_taxnums')->where(['id'=>$id])->find();
        return view('taxation/taxdis/edit',['data'=>$data]);
    }

    // 保存编辑
    public function edits() {
        $res = $this->req;
        $hscode = $res['hscode'];
        $id     = $res['id'];
        unset($res['hscode']);
        unset($res['id']);
        $data = [];
        foreach($res as $k=>$vs) {
            $data[$k] = sprintf("%.2f",$vs);
        }
        $data['hscode'] = $hscode;
        $data['up_time'] = time();

        // 更新数据
        $up = Db::name('customs_taxnums')->where(['id'=>$id])->update($data);
        if(!$up) {
            return ['code'=>0,'msg'=>'更新失败'];
        }

        return ['code'=>1,'msg'=>'更新成功'];
    }


    // 删除
    public function del()
    {
        // 删除条件
        $id = $this->req['id'];
        // 删除操作
        $del = Db::name('customs_taxnums')->where(['id'=>$id])->delete();
        if(!$del) {
            return ['code'=>0,'msg'=>'删除失败'];
        }
        return ['code'=>1,'msg'=>'删除成功'];
    }


    // 导入更新
    public function imptaxup(Request $request)
    {
        set_time_limit(0);
        // 获取导入的数据，
        /**
         *  hscode != hscode = 不更新
         */

        if (!$request->file('file')) {
            return json(['code' => -1, 'msg' => '请选择文件上传']);
        }
        $path = ROOT_PATH . 'public' . DS . 'uploads';
        // 验证后缀并移动文件
        $file = $request->file('file')->validate(['ext' => ['xls','xlsx']])->move($path);
        if (!$file) {
            return json(['code' => -1, 'msg' => $file]);
        }

        try{
            // 加载模块；logic/Taxation
            $model = model('Taxation', 'logic');

            $fil = $model->upFile($model->getFile($path.'/'.$file->getSaveName()));

            @unlink($path . '/' . $file->getSaveName());

            $msg = '';
            switch ($fil['code']) {
                case 1:
                    $msg = $fil['msg'].' 不存在的HScode: '.$fil['hscode'];
                    break;
                case 2:
                case 3:
                    $msg = $fil['msg'];
                    break;
            }

            // 返回数据
            return json(['code' => 1, 'msg' => $msg]);

        } catch (\Exception $exception) {
            @unlink($path.'/'.$file->getSaveName());
            return json([
                'code'    => -1,
                'msg' => $exception->getMessage() . ':' . $exception->getLine() . ':' . $exception->getFile(),
            ]);
        }

    }


    // 查看商户税费
    public function show()
    {
        // 获取里面的用户
        $customs = Db::name('customs_tax')->field('uid')->select();
        $cates = [];
        if(!empty($customs)) {
            foreach ($customs as $cv) {
                $cates[$cv['uid']] = $cv['uid'];
            }
        }

        $uids = '';
        if(isset($this->req['uids'])) {
            $uids = $this->req['uids'];
            $config = [
                'query'=>['s'=>'admin/taxation/show','uids'=>$uids],
                'var_page'=>'page',
                'type'=>'Layui',
                'newstyle'=>true
            ];
            // 查询所有用户税费
            $tax = Db::name('customs_tax')->where('uid',$uids)->order('add_time desc')->paginate(10,true,$config);

        } else {
            $config = [
                'query'=>['s'=>'admin/taxation/show'],
                'var_page'=>'page',
                'type'=>'Layui',
                'newstyle'=>true
            ];
            // 查询所有用户税费
            $tax = Db::name('customs_tax')->order('add_time desc')->paginate(10,true,$config);
        }
        
        $title = '商户税费';
        // 分页
        $page = $tax->render();

        // 账单状态；1:待对账，2：待清算，3：待核查，4：已清算
        $status = [
          '',
          '待对账','待清算','待核查','已清算'
        ];

        return view('taxation/taxfee/index',[
            'tax'   =>  $tax,
            'page'  =>  $page,
            'title' =>  $title,
            'cates'  =>  $cates,
            'check'  =>  $uids,
            'status'  =>  $status
        ]);
    }


    // 导入计算税费
    public function exportTax() {
        $title = '导入计算税费';
        $userInfo = DB::name('decl_user')->field(['id','user_name'])->select();
        return view('taxation/taxfee/export',[
            'title' =>  $title,
            'userInfo'=>$userInfo,
        ]);
    }

    // 根据uid  获取对应的提单编号
    public function getbol(Request $req) {
        $uid = $req->post('uid');
        // 获取提单编号
        $boll = DB::name('decl_bol')->where(['user_id'=>$uid])->field(['bill_num'])->select();
        if(empty($boll)) {
            // 查找父级的uid,再查询一遍；
            $fuid = DB::name('decl_user')->where(['id'=>$uid])->field(['parent_id'])->find();
            if(!empty($fuid)){
                $boll = DB::name('decl_bol')->where(['user_id'=>$fuid['parent_id']])->field(['bill_num'])->select();
            }
        }
        if(empty($boll)) {
            return json_encode(['code'=>0,'msg'=>'该商户没有提单号,请重新选择！']);
        }
        return json_encode(['code'=>1,'data'=>$boll]);
    }

    // 设置税费清算
    public function setTaxLiqui(Request $req) {
        $id = $req->post('id');
        $status = $req->post('status');
        $up = DB::name('customs_tax')->where('id',$id)->update(['status'=>$status]);
        if(!$up) {
            return json_encode(['code'=>0,'msg'=>'核查失败，请稍后重试！']);
        }
        return json_encode(['code'=>1,'msg'=>'核查成功']);
    }

}