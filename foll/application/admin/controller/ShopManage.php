<?php

namespace app\admin\controller;


use think\Db;
use think\Request;


/**
 * 商城管理
 * Class ShopManage
 * @package app\admin\controller
 */
class ShopManage extends Auth
{

    /**
     * 商品购物车下单不可拼单管理
     * @param  Request  $request
     * @return \think\response\Json
     */
    public function splitOrderManage(Request $request)
    {
        if ($request->isGet()) {
            $total = Db::name('sz_yi_dismantling_conditions')->count();
            $res = Db::name('sz_yi_dismantling_conditions')->paginate(10, $total, [
                'type' => 'Layui',
                'query' => ['s' => 'admin/shop/splitOrderManage'],
                'var_page' => 'page',
                'newstyle' => true
            ]);;
            return $this->fetch(
                'shop_manage/split_order_manage', [
                'title' => '商品购买拆单管理',
                'list' => $res->toArray()['data'],
                'page' => $res->render()
            ]);
        } else {
            Db::name('sz_yi_dismantling_conditions')->insert(['hs_code' => trim($request->post('hscode'))]);
            return json(['code' => 0, 'message' => '已保存']);
        }
    }


    /**
     * 删除商城下单不允许拼单条件
     * @param  Request  $request
     * @return \think\response\Json
     */
    public function delSplitOrderConditions(Request $request)
    {
        if ($request->get('id') == "" || !is_numeric($request->get('id'))) {
            return json(['code'=>-1,'message'=>'参数错误']);
        }
        Db::name('sz_yi_dismantling_conditions')->where('id', $request->get('id'))->delete();
        return json(['code' => 0, 'message' => '已删除']);
    }
}