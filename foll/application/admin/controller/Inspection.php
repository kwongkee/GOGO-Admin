<?php

namespace app\admin\controller;
use think\Request;
use think\Db;
use Util\data\Sysdb;

class Inspection extends Auth{

    // 配置风控管理
    public function index()
    {
        $this->db = new Sysdb;

        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'insp/index'],
            'var_page'=>'page',
            'newstyle'=>true
        ];

        $opera = $this->db->table('customs_elec_riskdeclaration')->order('id desc')->pages(12,$config);

        $cate = DB::name('customs_elec_riskcategory')->select();

        $cateName = [];
        foreach ($cate as $cv) {
            $cateName[$cv['id']] = $cv['cateName'];
        }

        $this->assign('order',$opera);
        $this->assign('cate',$cate);
        $this->assign('cateNames',$cateName);

        return view('insp/verif',['title'=>'查验风控']);
    }

    // 添加配置
    public function add(Request $req) {
        // 接收数据
        $data = $req->post();

        if(!empty($data)) {

            if($data['hscode'] == '' && $data['brand'] == '' && $data['goodname'] == '') {
                return json_encode(['status'=>0,'msg'=>'至少填一项']);
            }

            if($data['desc'] == '') {
                return json_encode(['status'=>0,'msg'=>'风险描述不能为空']);
            }

            $keyword = '';
            if($data['hscode'] != '') {
                $keyword.=$data['hscode'];
            }

            if($data['brand'] != '') {
                $keyword.=strtoupper($data['brand']);
            }

            if($data['goodname'] != '') {
                $keyword.=$data['goodname'];
            }

            $tmp = [
                'hscode'    =>  $data['hscode'],
                'brand'     =>  strtoupper($data['brand']),
                'goodname'  =>  $data['goodname'],
                'desc'      =>  $data['desc'],
                'keyword'   =>  $keyword,
                'cate'      =>  $data['cate'],
                'create_time'=>time(),
            ];
            $ins = DB::name('customs_elec_riskdeclaration')->insertGetId($tmp);
            if($ins) {
                return json_encode(['status'=>1,'msg'=>'数据添加成功']);
            }
        } else {
            return json_encode(['status'=>0,'msg'=>'数据不能为空']);
        }

    }


    // 编辑峰值 get
    public function Edit(Request $req)
    {
        $id = $req->get('id');
        $cate = DB::name('customs_elec_riskcategory')->select();
        $data = DB::name('customs_elec_riskdeclaration')->where(['id'=>$id])->find();
        $this->assign('data',$data);
        $this->assign('cate',$cate);
        return view('insp/edit',['title'=>'编辑风险']);
    }

    // 保存设置 post
    public function doEdit(Request $request)
    {
        if(!$request->isPost()){
            return json_encode(['status'=>0,'msg'=>'请求方式不正确']);
        }

        $data = $request->post();
        $id   = $data['id'];
        unset($data['id']);

        if($data['hscode'] == '' && $data['brand'] == '' && $data['goodname'] == '') {
            return json_encode(['status'=>0,'msg'=>'至少填一项']);
        }

        if($data['desc'] == '') {
            return json_encode(['status'=>0,'msg'=>'风险描述不能为空']);
        }


        $keyword = '';
        if($data['hscode'] != '') {
            $keyword.=$data['hscode'];
        }

        if($data['brand'] != '') {
            $keyword.=strtoupper($data['brand']);
        }

        if($data['goodname'] != '') {
            $keyword.=$data['goodname'];
        }

        $tmp = [
            'hscode'    =>  $data['hscode'],
            'brand'     =>  strtoupper($data['brand']),
            'goodname'  =>  $data['goodname'],
            'desc'      =>  $data['desc'],
            'cate'      =>  $data['cate'],
            'keyword'   =>  $keyword,
            'create_time'=>time(),
        ];

        $edit = DB::name('customs_elec_riskdeclaration')->where(['id'=>$id])->update($tmp);
        if($edit) {
            return json_encode(['status'=>1,'msg'=>'编辑成功']);
        }
        return json_encode(['status'=>0,'msg'=>'编辑失败']);

    }

    // 删除
    public function del(Request $req) {
        $id = $req->post('id');
        if($id == '') {
            return json_encode(['status'=>0,'msg'=>'数据不能为空']);
        }

        $del = DB::name('customs_elec_riskdeclaration')->where('id',$id)->delete();
        if($del) {
            return json_encode(['status'=>1,'msg'=>'数据删除成功']);
        }

        return json_encode(['status'=>0,'msg'=>'数据删除失败！']);
    }


    /**
     * 分类
     */
    public function cate(){
        return view('insp/cate',[]);
    }

    // 添加分类
    public function docate(Request $req) {

        $data = $req->post('cateName');
        if(!isset($data)) {
            return json_encode(['status'=>0,'msg'=>'分类名称不能为空']);
        }
        $tmp['cateName'] = $data;
        $ins = DB::name('customs_elec_riskcategory')->insertGetId($tmp);
        if($ins) {
            return json_encode(['status'=>1,'msg'=>'分类添加成功']);
        }

        return json_encode(['status'=>0,'msg'=>'分类添加失败']);
    }

    // 分类列表
    public function catelist() {

        $list = Db::name('customs_elec_riskcategory')
            ->paginate(5, true,[
                'query' => ['s' => 'insp/catelist'],
                'var_page' => 'page',
                'type'  => 'Layui',
                'newstyle' => true
            ]);

        // 分页
        $page = $list->render();

        $this->assign('orders',$list);
        $this->assign('pages',$page);

        return view('insp/catelist',['title'=>'分类列表']);
    }

    // 删除分类
    public function catedel(Request $req) {

        $id = $req->post('id');
        if(!isset($id)) {
            return json_encode(['status'=>0,'msg'=>'分类不可删除']);
        }

        $del = DB::name('customs_elec_riskcategory')->where('id',$id)->delete();
        if($del) {
            return json_encode(['status'=>1,'msg'=>'分类删除成功']);
        }
        return json_encode(['status'=>0,'msg'=>'分类删除失败']);
    }

    // 编辑
    public function catedit(Request $req) {
        $id = $req->get('id');
        $data = DB::name('customs_elec_riskcategory')->where('id',$id)->find();
        $this->assign('data',$data);
        return view('insp/catedit',[]);
    }

    // 编辑操作
    public function docatedit(Request $req) {
        $id = $req->post('id');
        $cateName = $req->post('cateName');

        $ins = DB::name('customs_elec_riskcategory')->where('id',$id)->update(['cateName'=>$cateName]);
        if($ins) {
            return json_encode(['status'=>1,'msg'=>'编辑成功']);
        }
        return json_encode(['status'=>0,'msg'=>'编辑失败']);
    }

}

?>