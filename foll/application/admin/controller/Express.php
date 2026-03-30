<?php

namespace app\admin\controller;
use think\Request;
use think\Validate;
use think\Db;
class Express extends Auth
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
            'query'=>['s'=>'admin/express/index'],//参数添加
            'var_page'=>'page',
            'newstyle'=>true,
        ];

        $data = null;
        // 链表查询数据
        $data = DB::name('customs_express')->where(['pid'=>0])->field('id,expName,createTime')->paginate(12,false,$config);

        return view('cross/express/index',[
            'data' =>$data->toArray(),
            'page' =>$data->render(),
            'title'=>'快递配置'
        ]);
    }


    // 添加快递企业
    public function add() {
        return view('cross/express/add',[]);
    }

    // 添加操作
    public function Doadd() {
        $data = $this->req->post();
        $method = $data['methods'];
        if($data['expName'] == '') {
            return json_encode(['code'=>0,'msg'=>'快递企业必填']);
        }

        if($method == 'add') {
            $das = [
                'expName'=>trim($data['expName']),
                'pid'=>0,
                'createTime'  =>time(),
            ];

            $isName = Db::name('customs_express')->where('expName',$data['expName'])->find();
            if(!empty($isName)) {
                return json_encode(['code'=>0,'msg'=>'添加失败，快递企业名称已存在！']);
            }

            Db::name('customs_express')->insert($das);
            return json_encode(['code'=>1,'msg'=>'添加成功']);

        } else if($method == 'edit') {
            $id = $data['id'];
            $das = [
                'logisticName'=>trim($data['logisticName']),
                'updateTime'  =>time(),
            ];
            Db::name('customs_express')->where(['id'=>$id])->update($das);
            return json_encode(['code'=>1,'msg'=>'更新成功']);
        }

        return json_encode(['code'=>0,'msg'=>'操作失败，请稍后重试！']);
        
    }

    // 删除快递企业
    public function Del() {
        $id = $this->req->post('id');
        Db::name('customs_express')->where('id',$id)->delete();
        Db::name('customs_express')->where('pid',$id)->delete();
        return json_encode(['code'=>1,'msg'=>'快递企业删除成功']);
    }


    // 省份
    public function Province(){

        return [
            'bj'=>'北京市',
            'tj'=>'天津市',
            'sh'=>'上海市',
            'cq'=>'重庆市',
            'hebei'=>'河北省',
            'shanx'=>'山西省',
            'ln'=>'辽宁省',
            'jl'=>'吉林省',
            'hlj'=>'黑龙江',
            'js'=>'江苏省',
            'zj'=>'浙江省',
            'ah'=>'安徽省',
            'fj'=>'福建省',
            'jx'=>'江西省',
            'sd'=>'山东省',
            'hn'=>'河南省',
            'gd'=>'广东省',
            'hain'=>'海南省',
            'sc'=>'四川省',
            'gz'=>'贵州省',
            'yn'=>'云南省',
            'sx'=>'陕西省',
            'gs'=>'甘肃省',
            'qh'=>'青海省',
            'tw'=>'台湾省',
            'nmg'=>'内蒙古自治区',
            'gx'=>'广西壮族自治区',
            'xz'=>'西藏自治区',
            'nx'=>'宁夏回族自治区',
            'xj'=>'新疆维吾尔自治区',
            'xg'=>'香港特别行政区',
            'om'=>'澳门特别行政区'
        ];
    }


    // 快递区域配置
    public function region() {

        $pid = $this->req->get('pid');

        $config = [
            'type'=>'Layui',
            'query'=>['s'=>'admin/express/region'],//参数添加
            'var_page'=>'page',
            'newstyle'=>true,
        ];

        $data = null;
        // 链表查询数据
        $data = DB::name('customs_express')->where(['pid'=>$pid])->paginate(12,false,$config);

        return view('cross/express/region',[
            'data'  =>$data->toArray(),
            'page'  =>$data->render(),
            'pid'   =>$pid,
            'title' =>'快递区域配置'
        ]);

    }

    // 给快递企业添加线路
    public function regionadd()
    {
        $pid = $this->req->get('pid');

        return view('cross/express/regionadd', [
            'title' => '添加计费区域',
            'exp'   =>$this->Province(),
            'pid'   =>$pid,
        ]);
    }



    // 编辑线路
    public function regionedit()
    {
        $id = $this->req->get('id');

        $data = Db::name('customs_express')->where('id',$id)->find();

        return view('cross/express/regionedit', [
            'title' => '添加计费区域',
            'data'  =>$data,
            'exp'   =>$this->Province(),
            'pid'    =>$id,
        ]);
    }


    // 保存线路
    public function regionDoadd() {

        $reqs = $this->req->post();

        $data = $reqs['data'];
        if(empty($data)) {
            return json_encode(['code'=>0,'msg'=>'提交数据不能为空']);
        }


        // 选择区域；
        $pro = $this->Province();

        // 将上传上来的变成数组；
        $Pros = explode(',',$data['province']);

        if(is_array($Pros)) {

            if(count($Pros) > 1) {
                $tmp = [];

                foreach($Pros as $v) {
                    $n = trim($v);
                    if(isset($pro[$n])) {
                        $tmp[$v] = $pro[$n];
                    }
                }

            } else {

                if(isset($pro[$Pros[0]])) {

                    $tmp[$Pros[0]] = $pro[$Pros[0]];
                }
            }
        }

        $province = json_encode($tmp,JSON_UNESCAPED_UNICODE);

        $rule = [
            'regionName'=>'require',
            'oneMoney'=>'require',
            'oneKg'=>'require',
            'twoMoney'=>'require',
            'twoKg'=>'require',
        ];

        $msg =  [
            'regionName.require'=>'区域名称必填',
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
                'province'=>$province,
                'regionName'=>trim($data['regionName']),
                'oneMoney'=>trim($data['oneMoney']),
                'oneKg'=>trim($data['oneKg']),
                'twoMoney'=>trim($data['twoMoney']),
                'twoKg'=>trim($data['twoKg']),
                'createTime'=>time(),
            ];

            $isRoute = Db::name('customs_express')->where(['pid'=>$data['pid'],'regionName'=>$data['regionName']])->find();
            if(!empty($isRoute)) {
                return json_encode(['code'=>0,'msg'=>'区域名称已存在，请重新输入！']);
            }

            Db::name('customs_express')->insert($ins);
            return json_encode(['code'=>1,'msg'=>'新增操作成功']);

        } else if($data['Method'] == 'edit') {

            $ins = [
                //'routeName'=>trim($data['routeName']),
                'province'=>$province,
                'oneMoney'=>trim($data['oneMoney']),
                'oneKg'=>trim($data['oneKg']),
                'twoMoney'=>trim($data['twoMoney']),
                'twoKg'=>trim($data['twoKg']),
                'updateTime'=>time(),
            ];

            Db::name('customs_express')->where('id',$data['pid'])->update($ins);

            return json_encode(['code'=>1,'msg'=>'编辑操作成功']);

        }

        return json_encode(['code'=>0,'msg'=>'操作失败，请稍后重试！']);
    }

    // 删除线路
    public function regionDel(){
        $id = $this->req->post('id');
        Db::name('customs_express')->where('id',$id)->delete();
        return json_encode(['code'=>1,'msg'=>'区域删除成功']);
    }
}