<?php

namespace app\admin\controller;
use app\admin\controller;
use think\Request;
use think\Loader;
class CustomDeclaration extends Auth
{
    /*
     * 商品备案审核列表
     */
    public function goodsRegIndex(Request $request)
    {
        $list = Loader::model("GoodsReg",'logic')->getGHead();
        return view('custom/goods_index',['title'=>'商品备案审核','list'=>$list->toArray(),'page'=>$list->render()]);
    }

    /*
     * 审核通过
     */

    public function checkGoodsDec(Request $request)
    {
        if($request->get('id')!==null){
            Loader::model("GoodsReg","logic")->updateGoodsCheckStatus($request->get('id'),$request->get('check'));
            $this->success("成功",Url("admin/goods_reg_index"));
        }
    }

    public function goodsDetails(Request $request)
    {
        $list = Loader::model('GoodsReg','logic')->getGlist($request->get('hid'));
        return view('custom/detail_index',['title'=>'详情','page'=>$list->render(),'list'=>$list->toArray()]);
    }

    public function goodsDetailEdit(Request $request)
    {
        if($request->isGET()){
            $info = Loader::model('GoodsReg','logic')->getSingleInfo($request->get('id'));
            return view("custom/goods_detail_edit",['title'=>'编辑','data'=>$info]);
        }else if($request->isPOST()){
            $head_id = $request->post('hid');
            Loader::model('GoodsReg','logic')->updateDetailTable($request->post(),$head_id);
            Redirects('admin/goods_details&hid='.$head_id);
        }
    }
}