<?php

namespace app\admin\controller;


use think\Request;
use app\admin\model\GoodsImportTypeModel;
use think\Db;

/**
 * 商品进口类型检测
 * Class GoodsImportType
 * @package app\admin\controller
 */
class GoodsImportType extends Auth {
    public function index(Request $request) {
        return view('CustomsSystem/goods_import_type/index', ['title' => '商品进口类型建议']);
    }

    public function getGoodsList(Request $request) {
        $limit  = $request->get('limit');
        $page   = ($request->get('page') - 1) * $limit;
        $where = $request->get('goodssn');
        if ($where!=''){
            $where='goodssn="'.$where.'"';
        }
        $total  = GoodsImportTypeModel::where($where)->count();
        $result = GoodsImportTypeModel::where($where)->limit($page, $limit)->select();
        return json([
            "code"  => 0, //解析接口状态
            "msg"   => "完成", //解析提示文本
            "count" => $total, //解析数据长度
            "data"  => $result
        ]); //解析数据列表]);
    }

    /**
     * 上传申报检测申报方式
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    public function goodsFileUpload(Request $request) {
        return view('CustomsSystem/goods_import_type/goods_file_upload', ['title' => '上传申报检测申报方式']);
    }


    /**
     * 保存文件
     * @param Request $request
     * @return \think\response\Json
     */
    public function saveUploadGoodsFile(Request $request) {
        try {
            $logic    = model('GoodsImportTypeLogic', 'logic');
            $fileName = $logic->saveFile($request);
            $data     = $logic->readFileContent($fileName);
            Db::startTrans();
            foreach ($data as $value) {
                $logic->save($logic->countRate($value));
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            @unlink($fileName);
            return json(['code' => 1, 'msg' => $e->getMessage() . $e->getLine()]);
        }
        @unlink($fileName);
        return json(['code' => 0, 'msg' => '上传成功']);
    }
}