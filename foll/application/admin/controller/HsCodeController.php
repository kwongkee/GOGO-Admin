<?php


namespace app\admin\controller;


use think\Db;
use think\Request;
use app\admin\model\HsCodeSpecialCategoryModel;

class HsCodeController extends Auth
{


    /**
     *特殊类别管理
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    public function specialCategoryManage(Request $request)
    {
        return view('CustomsSystem/hscode/special_category_manage', ['title' => 'hscode特殊类别管理']);
    }

    /**
     * 获取特殊类别数据
     * @param Request $request
     * @return \think\response\Json
     */
    public function getSpecialCategory(Request $request)
    {
        $limit = $request->get('limit');
        $page = $request->get('page');
        $code = $request->get('code');
        $cate = $request->get('cate');
        $where = [];
        if ($code) {
            $where = ['hscode_prefix' => $where];
        }
        if ($cate) {
            $where['cate_code'] = $cate;
        }
        $page = ($page - 1) * $limit;
        $count = HsCodeSpecialCategoryModel::count();
        $res = HsCodeSpecialCategoryModel::where($where)->limit($page, $limit)->order('id', 'desc')->select();
        return json([
            "code" => 0, //解析接口状态
            "msg" => "完成", //解析提示文本
            "count" => $count, //解析数据长度
            "data" => $res,
        ]); //解析数据列表]);
    }

    /**
     * 添加特殊类别
     * @param Request $request
     * @return \think\response\Json
     */
    public function addSpecialCategory(Request $request)
    {
        $post = $request->post('data');
        $post = json_decode($post);
        if (empty($post)) {
            return json(['code' => 1, 'msg' => '请填写完整']);
        }
        HsCodeSpecialCategoryModel::insert([
            'hscode_prefix' => $post->hscode_prefix,
            'cate_name' => $post->cate_name,
            'cate_code' => $post->cate_code,
            'create_time' => time(),
        ]);
        return json(['code' => 0, 'msg' => '完成']);
    }


    /**
     * 更新
     * @param Request $request
     * @return \think\response\Json
     */
    public function editSpecialCategory(Request $request)
    {
        $post = $request->post('data');
        $post = json_decode($post);
        if (empty($post)) {
            return json(['code' => 1, 'msg' => '请填写完整']);
        }
        HsCodeSpecialCategoryModel::where('id', $post->id)->update([
            'hscode_prefix' => $post->hscode_prefix,
            'cate_name' => $post->cate_name,
            'cate_code' => $post->cate_code,
            'update_time' => time(),
        ]);
        return json(['code' => 0, 'msg' => '完成']);
    }

    /**
     * 删除
     * @param Request $request
     * @return \think\response\Json
     */
    public function delSpecialCategory(Request $request)
    {
        $post = $request->post('data');
        $post = json_decode($post);
        if (empty($post)) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }
        HsCodeSpecialCategoryModel::where('id', $post->id)->delete();
        return json(['code' => 0, 'msg' => '完成']);
    }

    /**
     * hscode自定义风险类型范围
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    public function hscodeRisk(Request $request)
    {
        return view('CustomsSystem/hscode/hscode_risk', ['title' => '自定义风险', 'page' => '']);
    }


    /**
     * 获取hscode自定义风险类型范围
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getHscodeRiskList(Request $request)
    {

        $limit = $request->get('limit');
        $page = ($request->get('page') - 1) * $limit;
        $count = Db::name('customs_hscode_customize_risk')->count();
        $data = Db::name('customs_hscode_customize_risk')->order('id','desc')->limit($page, $limit)->select();
        return json([
            "code" => 0, //解析接口状态
            "msg" => "完成", //解析提示文本
            "count" => $count, //解析数据长度
            "data" => $data,
        ]); //解析数据列表]);
    }


    /**
     * 添加hscode自定义风险类型范围
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\Json|\think\response\View
     */
    public function addHsCodeRisk(Request $request)
    {
        if ($request->isGet()) {
            return view('CustomsSystem/hscode/hscode_risk_add', ['title' => '添加']);
        } else {
            foreach ($request->post() as $index => $item) {
                if ($item == "") {
                    return json(['code' => 1, 'msg' => '请填写完整']);
                }
            }
            Db::name('customs_hscode_customize_risk')->insert([
                'hscode_start' => $request->post('hscode_start'),
                'hscode_end' => $request->post('hscode_end'),
                'hscode_remark' => $request->post('hscode_remark'),
                'create_time' => time()
            ]);
            return json(['code' => 0, 'msg' => '完成']);
        }
    }


    public function upCustomizeHsCodeRisk(Request $request)
    {
        foreach ($request->post() as $index => $item) {
            if ($item == "") {
                return json(['code' => 1, 'msg' => '请填写完整']);
            }
        }
        Db::name('customs_hscode_customize_risk')
            ->where('id',$request->post('id'))
            ->update([
                'hscode_start' => $request->post('hscode_start'),
                'hscode_end' => $request->post('hscode_end'),
                'hscode_remark' => $request->post('hscode_remark'),
            ]);
        return json(['code'=>0,'msg'=>'更新完成']);
    }

    /**
     * 删除hscode自定义风险类型范围
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delCustomizeHsCodeRisk(Request $request)
    {
        if (!is_numeric($request->get('id'))){
            return json(['code'=>1,'msg'=>'参数错误']);
        }
        Db::name('customs_hscode_customize_risk')->where('id',$request->get('id'))->delete();
        return json(['code'=>0,'msg'=>'删除完成']);
    }
}