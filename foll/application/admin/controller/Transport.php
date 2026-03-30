<?php

namespace app\admin\controller;
use think\Request;
use think\Validate;
use think\Db;
class Transport extends Auth
{
    public  $req;
    public function __construct(Request $request)
    {
        $this->req = $request;
    }

    /*
     * 快递配置
     */
    public function index()
    {
        $config = [
            'type'=>'Layui',
            'query'=>['s'=>'admin/transport/index'],//参数添加
            'var_page'=>'page',
            'newstyle'=>true,
        ];

        $data = null;
        // 链表查询数据
        $data = DB::name('customs_transportbill')->field('id,getDate,getExpress,expNames,isReceive')->paginate(12,false,$config);

        return view('cross/transport/index',[
            'data' =>$data->toArray(),
            'page' =>$data->render(),
            'title'=>'快递配置'
        ]);
    }

    // 获取快递企业
    private function getExpress() {
        $data = Db::name('customs_express')->where('pid',0)->field('id,expName')->select();
        return $data;
    }

    // 获取运单配置
    private function getTransport() {
         $data = Db::name('customs_transportbill')->field('expNames,getExpress')->select();
        return $data;
    }


    // 添加快递企业
    public function add() {

        return view('cross/transport/add',['exp'=>$this->getExpress()]);
    }

    // 添加操作
    public function Doadd() {
        $data = $this->req->post();
        $method = $data['Method'];

        if($method == 'add') {

            $das = [
                'getDate'=>strtotime($data['getDate']),
                'getExpress'=>trim($data['getExpress']),
                'expNums'=>trim($data['expNums']),
                'expNumd'=>trim($data['expNumd']),
                'expNames'  =>$data['expNums'].'-'.$data['expNumd'],
                'createTime'  =>time(),
            ];

            $isName = Db::name('customs_transportbill')->where(['expNums'=>$data['expNums'],'expNumd'=>$data['expNumd']])->find();
            if(!empty($isName)) {
                return json_encode(['code'=>0,'msg'=>'添加失败，号码段已存在！']);
            }

            Db::name('customs_transportbill')->insert($das);
            return json_encode(['code'=>1,'msg'=>'领取成功']);

        } else if($method == 'edit') {
            $id = $data['id'];
            $das = [
                'logisticName'=>trim($data['logisticName']),
                'updateTime'  =>time(),
            ];
            Db::name('customs_transportbill')->where(['id'=>$id])->update($das);
            return json_encode(['code'=>1,'msg'=>'更新成功']);
        }

        return json_encode(['code'=>0,'msg'=>'操作失败，请稍后重试！']);

    }

    // 删除新领运单
    public function Del() {
        $id = $this->req->post('id');
        Db::name('customs_transportbill')->where('id',$id)->delete();
        return json_encode(['code'=>1,'msg'=>'删除成功']);
    }




    // 分配运单列表
    public function distri()
    {
        $config = [
            'type' => 'Layui',
            'query' => ['s' => 'admin/transport/distri'],//参数添加
            'var_page' => 'page',
            'newstyle' => true,
        ];

        $data = null;
        // 链表查询数据
        $data = DB::name('customs_distribution')->paginate(12, false, $config);

        return view('cross/transport/distri', [
            'data' => $data->toArray(),
            'page' => $data->render(),
            'title' => '分配运单列表'
        ]);
    }




    // 分配运单
    public function distriAdd(){

        return view('cross/transport/distriadd', [
            'account' => $this->getAccount(),
            'express' => $this->getExpress(),
            'title' => '分配运单'
        ]);
    }


    private function show($data) {
        echo '<pre>';
        print_r($data);
        die;
    }

    // 分配操作
    public function distriDoadd() {
        $data = $this->req->post();
        if(empty($data)) {
            return json_encode(['code'=>0,'msg'=>'数据不能为空']);
        }

        if($data['Method'] == 'add') {

            $ins = [
                'publicId' =>trim($data['publicId']),
                'merchatId'=>trim($data['merchatId']),
                'expressId'=>trim($data['expressId']),
                'expPargId'=>trim($data['expPargId']),
                'createTime'=>time(),
            ];

            $isRec = Db::name('customs_transportbill')->where('id',$data['expPargId'])->field('isReceive')->find();
            if($isRec['isReceive'] == 1) {
                return json_encode(['code'=>0,'msg'=>'该号段已分配给其他商户，请重新选择！']);
            }

            $ind = Db::name('customs_distribution')->insertGetId($ins);
            if($ind) {
                // 写入成功，记录更新已分配
                Db::name('customs_transportbill')->where('id',$data['expPargId'])->update(['isReceive'=>1]);
                return json_encode(['code'=>1,'msg'=>'号段分配成功！']);
            }
        }

        return json_encode(['code'=>0,'msg'=>'操作类型不正确！']);

    }


    // 获取商户信息
    public function getMerchat() {
        $uniacid = $this->req->post('uniacid');
        $data = Db::name('sz_yi_perm_user')->where('uniacid',$uniacid)->field('uid,username')->select();
        if(!empty($data)) {
            return json_encode(['code'=>1,'msg'=>'Success','data'=>$data]);
        }

        return json_encode(['code'=>0,'msg'=>'Error','data'=>'没有数据，请配置']);
    }



    // 获取运单分配号码段
    public function getTrans() {
        $id = $this->req->post('id');
        $data = Db::name('customs_transportbill')->where(['getExpress'=>$id,'isReceive'=>0])->field('id,expNames')->select();
        if(!empty($data)) {
            return json_encode(['code'=>1,'msg'=>'Success','data'=>$data]);
        }
        return json_encode(['code'=>0,'msg'=>'Error','data'=>'没有数据，请配置']);
    }



    // 获取所有的公众号
    private function getAccount() {
        return Db::name('account_wechats')->field('uniacid,name')->select();
    }


    // 删除线路
    public function transDel(){
        $id = $this->req->post('id');
        $bill = $this->req->post('bill');// 如果删除用于释放号段；
        // 更新状态
        Db::name('customs_transportbill')->where('id',$bill)->update(['isReceive'=>0]);
        // 删除数据
        Db::name('customs_distribution')->where('id',$id)->delete();
        return json_encode(['code'=>1,'msg'=>'分配运单删除成功']);
    }
}