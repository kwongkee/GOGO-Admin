<?php

namespace app\admin\controller;
use think\Request;
use think\Loader;
use think\Validate;
use think\Db;
class CrossBorder extends Auth
{
    public  $req;
    public function __construct(Request $request)
    {
        $this->req = $request;
    }

    /*
     * 跨境物流配置
     */
    public function logistics()
    {
        $config = [
                'type'=>'Layui',
                'query'=>['s'=>'admin/cross/logistics'],//参数添加
                'var_page'=>'page',
                'newstyle'=>true,
        ];

        $data = null;
        // 链表查询数据
        $data = DB::name('customs_logistics')->where(['pid'=>0])->field('id,logisticName,createTime')->paginate(12,false,$config);

        return view('cross/logistics/index',[
            'data' =>$data->toArray(),
            'page' =>$data->render(),
            'title'=>'跨境物流配置'
        ]);
    }

    // 添加物流企业名称
    public function logGetname(){
        return view('cross/logistics/addname',['title'=>'添加物流企业']);

    }
    // 保存物流企业名称
    public function logDoname() {
        $data = $this->req->post();
        $method = $data['methods'];
        if($data['logisticName'] == '') {
            return json_encode(['code'=>0,'msg'=>'物流企业必填']);
        }

        if($method == 'add') {
            $das = [
                'logisticName'=>trim($data['logisticName']),
                'pid'=>0,
                'createTime'  =>time(),
            ];

            $isName = Db::name('customs_logistics')->where('logisticName',$data['logisticName'])->find();
            if(!empty($isName)) {
                return json_encode(['code'=>0,'msg'=>'添加失败，物流企业名称已存在！']);
            }

            Db::name('customs_logistics')->insert($das);
            return json_encode(['code'=>1,'msg'=>'添加成功']);

        } else if($method == 'edit') {
            $id = $data['id'];
            $das = [
                'logisticName'=>trim($data['logisticName']),
                'updateTime'  =>time(),
            ];
            Db::name('customs_logistics')->where(['id'=>$id])->update($das);
            return json_encode(['code'=>1,'msg'=>'更新成功']);
        }

        return json_encode(['code'=>0,'msg'=>'操作失败，请稍后重试！']);

    }

    // 删除线路
    public function Del() {
        $id = $this->req->post('id');
        Db::name('customs_logistics')->where('id',$id)->delete();
        Db::name('customs_logistics')->where('pid',$id)->delete();
        return json_encode(['code'=>1,'msg'=>'物流企业删除成功']);
    }



    // 物流线路
    public function logisticLine() {

        $pid = $this->req->get('pid');

        $config = [
            'type'=>'Layui',
            'query'=>['s'=>'admin/cross/logisticLine'],//参数添加
            'var_page'=>'page',
            'newstyle'=>true,
        ];

        $data = null;
        // 链表查询数据
        $data = DB::name('customs_logistics')->where(['pid'=>$pid])->paginate(12,false,$config);

        return view('cross/logistics/logline',[
            'data' =>$data->toArray(),
            'page' =>$data->render(),
            'pid'  =>$pid,
            'title'=>'承运线路'
        ]);

    }

    // 给物流企业添加线路
    public function logisticsadd()
    {
        $pid = $this->req->get('pid');

        return view('cross/logistics/add', [
            'title' => '添加线路',
            'pid'   =>$pid,
        ]);
    }

    // 编辑线路
    public function logisticsedit()
    {
        $id = $this->req->get('id');

        $data = Db::name('customs_logistics')->where('id',$id)->find();

        return view('cross/logistics/logedit', [
            'title' => '编辑线路',
            'data'  =>$data,
            'id'    =>$id,
        ]);
    }

    // 保存线路
    public function logDoadd() {

        $data = $this->req->post();

        $rule = [
            'routeName'=>'require',
            'addrStart'=>'require',
            'addrEnd'=>'require',
            'oneMoney'=>'require',
            'oneKg'=>'require',
            'twoMoney'=>'require',
            'twoKg'=>'require',
        ];

        $msg =  [
            'routeName.require'=>'路线名称必填',
            'addrStart.require'=>'起始路线名称必填',
            'addrEnd.require'=>'终止路线名称必填',
            'oneMoney.require'=>'首重多少元必填',
            'oneKg.require'=>'首重多少公斤必填',
            'twoMoney.require'=>'续重多少元必填',
            'twoKg.require'=>'续重多少元必填',
        ];

        $validate = new Validate($rule,$msg);
        if(!$validate->check($data)) {
            return json_encode(['code'=>0,'msg'=>$validate->getError()]);
        }

        if($data['Method'] == 'add') {
            $ins = [
                'pid'=>trim($data['pid']),
                'routeName'=>trim($data['routeName']),
                'addrStart'=>trim($data['addrStart']),
                'addrEnd'=>trim($data['addrEnd']),
                'oneMoney'=>trim($data['oneMoney']),
                'oneKg'=>trim($data['oneKg']),
                'twoMoney'=>trim($data['twoMoney']),
                'twoKg'=>trim($data['twoKg']),
                'createTime'=>time(),
            ];

            $isRoute = Db::name('customs_logistics')->where(['pid'=>$data['pid'],'routeName'=>$data['routeName']])->find();
            if(!empty($isRoute)) {
                return json_encode(['code'=>0,'msg'=>'路线名称已存在，请重新输入！']);
            }

            Db::name('customs_logistics')->insert($ins);
            return json_encode(['code'=>1,'msg'=>'新增操作成功']);

        } else if($data['Method'] == 'edit') {

            $ins = [
                //'routeName'=>trim($data['routeName']),
                'addrStart'=>trim($data['addrStart']),
                'addrEnd'=>trim($data['addrEnd']),
                'oneMoney'=>trim($data['oneMoney']),
                'oneKg'=>trim($data['oneKg']),
                'twoMoney'=>trim($data['twoMoney']),
                'twoKg'=>trim($data['twoKg']),
                'updateTime'=>time(),
            ];

            Db::name('customs_logistics')->where('id',$data['id'])->update($ins);

            return json_encode(['code'=>1,'msg'=>'编辑操作成功']);

        }

        return json_encode(['code'=>0,'msg'=>'操作失败，请稍后重试！']);
    }

    // 删除线路
    public function logDel(){
        $id = $this->req->post('id');
        Db::name('customs_logistics')->where('id',$id)->delete();
        return json_encode(['code'=>1,'msg'=>'线路删除成功']);
    }
}