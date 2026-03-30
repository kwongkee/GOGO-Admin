<?php

namespace app\admin\controller;
use think\Request;
use think\Validate;
use think\Db;

/**
 * Class Packages
 * @package app\admin\controller
 * 包材分配
 */
class Packages extends Auth
{
    public  $req;
    public function __construct(Request $request)
    {
        $this->req = $request;
    }

    /*
     * 包材供应企业
     */
    public function index()
    {
        $config = [
            'type'=>'Layui',
            'query'=>['s'=>'admin/packages/index'],//参数添加
            'var_page'=>'page',
            'newstyle'=>true,
        ];

        $data = null;
        // 链表查询数据
        $data = DB::name('customs_packaging')->where('pid',0)->field('id,merchatName,createTime')->paginate(12,false,$config);

        return view('cross/package/index',[
            'data' =>$data->toArray(),
            'page' =>$data->render(),
            'title'=>'包材供应商'
        ]);
    }

    // 添加
    public function add() {

        return view('cross/package/add',['exp'=>$this->getExpress()]);
    }

    // 添加操作
    public function Doadd() {
        $data = $this->req->post();
        $method = $data['Method'];

        if($method == 'add') {

            $das = [
                'merchatName'=>$data['merchatName'],// 供应企业名称
                'createTime'  =>time(),
            ];

            $isName = Db::name('customs_packaging')->where(['merchatName'=>$data['merchatName']])->find();
            if(!empty($isName)) {
                return json_encode(['code'=>0,'msg'=>'添加失败，供应商已存在！']);
            }

            Db::name('customs_packaging')->insert($das);
            return json_encode(['code'=>1,'msg'=>'供应商添加成功']);

        } else if($method == 'edit') {
            $id = $data['id'];
            $das = [
                'logisticName'=>trim($data['logisticName']),
                'updateTime'  =>time(),
            ];
            Db::name('customs_packaging')->where(['id'=>$id])->update($das);
            return json_encode(['code'=>1,'msg'=>'更新成功']);
        }

        return json_encode(['code'=>0,'msg'=>'操作失败，请稍后重试！']);
    }


    // 删除
    public function Del() {
        $id = $this->req->post('id');
        Db::name('customs_packaging')->where('id',$id)->delete();
        return json_encode(['code'=>1,'msg'=>'删除成功']);
    }



    // 新购包材列表
    public function newList() {

        $config = [
            'type'=>'Layui',
            'query'=>['s'=>'admin/packages/index'],//参数添加
            'var_page'=>'page',
            'newstyle'=>true,
        ];

        $pid = $this->req->get('pid');
        $data = null;
        // 链表查询数据
        $data = DB::name('customs_packaging')->where('pid',$pid)->paginate(12,false,$config);

        return view('cross/package/newList',[
            'data' =>$data->toArray(),
            'page' =>$data->render(),
            'pid'  =>$pid,
            'title'=>'新购包材列表'
        ]);
    }


    // 新增包材
    public function newAdd() {

        $pid = $this->req->get('pid');

        $name = Db::name('customs_packaging')->where('id',$pid)->field('merchatName')->find();

        return view('cross/package/newAdd',[
            'merchatName'=>$name['merchatName'],
            'pid'=>$pid,
        ]);
    }


    // 新增包材操作
    public function newDoadd() {

        $data = $this->req->post();

        if(empty($data)) {
            return json_encode(['code'=>0,'msg'=>'数据不能为空']);
        }

        // 新增操作
        if($data['Method'] == 'add') {

            // 查看是否已存在该商品；
            $isPack = Db::name('customs_packaging')->where(['pid'=>$data['pid'],'packgeName'=>$data['packgeName']])->field('id')->find();
            if(!empty($isPack)) {
                // 存在该包材，更新新增
                Db::name('customs_packaging')->where('id',$isPack['id'])->setInc('num',$data['num']);
                // 更新产品时间
                Db::name('customs_packaging')->where('id',$isPack['id'])->update([
                    'speci'=>$data['speci'],
                    'price'=>$data['price'],
                    'updateTime'=>time(),
                ]);
                return json_encode(['code'=>1,'msg'=>'新购包材添加成功！']);
            }

            $ins = [
                'pid'=>$data['pid'],
                'packgeName'=>$data['packgeName'],
                'speci'=>$data['speci'],
                'price'=>$data['price'],
                'num'=>$data['num'],
                'createTime'=>time(),
            ];

            $ind = Db::name('customs_packaging')->insertGetId($ins);
            if($ind) {
                return json_encode(['code'=>1,'msg'=>'新购包材添加成功！']);
            }
            return json_encode(['code'=>0,'msg'=>'新购包材添加失败！']);
        }
        // 操作失败
        return json_encode(['code'=>0,'msg'=>'新增操作失败，请稍后重试！']);
    }



    // 获取包材列表
    private function getPackage() {
        $data = Db::name('customs_packaging')->where('pid',0)->field('id,merchatName')->select();
        return $data;
    }

    // 获取运单配置
    private function getTransport() {
        $data = Db::name('customs_transportbill')->field('expNames,getExpress')->select();
        return $data;
    }


    // 获取快递企业
    private function getExpress() {
        $data = Db::name('customs_express')->where('pid',0)->field('id,expName')->select();
        return $data;
    }


    // 分配包材
    public function distri()
    {
        $config = [
            'type' => 'Layui',
            'query' => ['s' => 'admin/packages/distri'],//参数添加
            'var_page' => 'page',
            'newstyle' => true,
        ];

        $data = null;
        // 链表查询数据
        $data = DB::name('customs_packagedistri')->paginate(12, false, $config);

        return view('cross/package/distri', [
            'data' => $data->toArray(),
            'page' => $data->render(),
            'title' => '包材分配'
        ]);
    }


    // 分配运单
    public function distriAdd(){

        return view('cross/package/distriadd', [
            'account' => $this->getAccount(),
            'package' => $this->getPackage(),
            'title' => '分配运单'
        ]);
    }


    // 分配操作
    public function distriDoadd() {
        $data = $this->req->post();
        if(empty($data)) {
            return json_encode(['code'=>0,'msg'=>'数据不能为空']);
        }

        if($data['Method'] == 'add') {

            $ins = [
                'publicId'  =>trim($data['publicId']),
                'merchatId' =>trim($data['merchatId']),
                'packageName'=>trim($data['packageName']),
                'packgeId'  =>trim($data['packgeId']),
                'distriNum' =>trim($data['distriNum']),
                'createTime'=>time(),
            ];

            // 查看分配数量是否可以分配这么多； 包材ID
            $dis = Db::name('customs_packaging')->where('id',$data['packgeId'])->field('num,use')->find();
            if(!empty($dis) && (($dis['use']+$data['distriNum']) > $dis['num'])) {
                return json_encode(['code'=>0,'msg'=>'抱歉，只能分配('.($dis['num']-$dis['use']).')个']);
            }

            $ind = Db::name('customs_packagedistri')->insertGetId($ins);
            if($ind) {
                // 分配完成，更新分配数量
                Db::name('customs_packaging')->where('id',$data['packgeId'])->setInc('use',$data['distriNum']);
                return json_encode(['code'=>1,'msg'=>'包材分配成功！']);
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


    // 获取包材
    public function getTrans() {
        $id = $this->req->post('id');
        $data = Db::name('customs_packaging')->where(['pid'=>$id])->field('id,packgeName,num,use')->select();
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
        // 删除分配，把分配的数量减回去
        $num = Db::name('customs_packagedistri')->where('id',$id)->field('packgeId,distriNum')->find();
        if(!empty($num)) {
            // 删除
            $del = Db::name('customs_packagedistri')->where('id',$id)->delete();
            if($del) {
                // 自减
                Db::name('customs_packaging')->where('id',$num['packgeId'])->setDec('use',$num['distriNum']);
                return json_encode(['code'=>1,'msg'=>'删除成功']);
            }
            return json_encode(['code'=>0,'msg'=>'删除失败，请稍后重试!']);
        }
        return json_encode(['code'=>0,'msg'=>'删除失败，请稍后重试!']);
    }
}