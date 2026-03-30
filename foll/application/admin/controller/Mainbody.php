<?php
namespace app\admin\controller;
use think\Request;
use Util\data\Sysdb;
use think\Session;
use think\Db;
use think\loader;

// 主体配置列表
class Mainbody extends Auth{

    // 主体配置列表
    public function index() {

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
                $map['platform'] = ['like','%'.$search.'%'];
            }

            $list = Db::name('customs_platform_list')->where($map)->order($order)->limit($limit)->select();
            $ebpType=['1'=>'广州','2'=>'汕头','3'=>'佛山'];
            foreach ($list as $k => $v) {
                $list[$k]['ebp_type'] =$ebpType[$v['ebp_type']];
                $list[$k]['manage'] = '<button type="button" onclick="Edit('."'".$v['id']."'".')" class="btn btn-primary btn-xs">编辑</button>';
                $list[$k]['manage'] .= '<button type="button" onclick="Del('."'".$v['id']."'".')" class="btn btn-primary btn-xs">删除</button>';
            }
            $total = Db::name('customs_platform_list')->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }else {
            return view('mainbody/index',
                ['title'=>'申报主体']
            );
        }
    }

    // 添加主体
    public function add() {
        return $this->fetch();
    }

    // 操作添加
    public function doadd(Request $request) {
        $data = $request->post();
        if(empty($data)) {
            return json_encode(['code'=>0,'msg'=>'数据不能为空']);
        }

        // 添加操作
        $data['create_time'] = time();
        $ins = Db::name('customs_platform_list')->insert($data);
        if(!$ins) {
            return json_encode(['code'=>0,'msg'=>'添加失败，请稍后重试']);
        }

        return json_encode(['code'=>1,'msg'=>'数据添加成功']);
    }

    // 编辑主体
    public function edit(Request $req) {
        $id = $req->get('id');
        $data = Db::name('customs_platform_list')->where(['id'=>$id])->find();
        $this->assign('data',$data);
        return view('mainbody/edit',['title'=>'编辑主体信息']);
    }

    // 编辑操作
    public function doedit(Request $req) {
        $data = $req->post();
        if(empty($data)) {
            return json_encode(['code'=>0,'msg'=>'数据不能为空']);
        }
        $id = $data['id'];
        unset($data['id']);

        $data['up_time'] = time();
        $up = Db::name('customs_platform_list')->where(['id'=>$id])->update($data);
        if(!$up) {
            return json_encode(['code'=>0,'msg'=>'数据编辑失败，请稍后重试']);
        }
        return json_encode(['code'=>1,'msg'=>'数据编辑成功']);
    }

    // 删除操作
    public function del(Request $req) {
        $id = $req->post('id');
        if(empty($id)) {
            return json_encode(['code'=>0,'msg'=>'条件不能为空']);
        }

        if($id == 1) {
            return json_encode(['code'=>0,'msg'=>'此数据不允许删除']);
        }

        $del = Db::name('customs_platform_list')->where(['id'=>$id])->delete();
        if(!$del) {
            return json_encode(['code'=>0,'msg'=>'数据删除失败，请稍后重试']);
        }
        return json_encode(['code'=>1,'msg'=>'数据删除成功']);
    }
}

?>