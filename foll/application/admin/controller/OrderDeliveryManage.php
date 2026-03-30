<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Request;
use think\Db;

class OrderDeliveryManage extends Auth
{
    /**
     * 提单列表
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function manifestList(Request $request)
    {
        $search = $request->has('billNum') ? $request->get('billNum') : null;
        $where = [];
        if (!empty($search)) {
            $where['a1.bill_num'] = $search;
        }
        $total = Db::name('decl_bol')->count();
        $res = Db::name('decl_bol')
            ->alias('a1')
            ->join('decl_user a2', 'a1.user_id=a2.id', 'left')
            ->field(['a1.bill_num', 'a2.user_name', 'a2.company_name'])
            ->where($where)
            ->order('a1.id', 'desc')
            ->paginate(8, $total, ['query' => ['s' => 'admin/OrderDeliveryManage/manifestList'], 'var_page' => 'page']);
        $page = $res->render();
        $data = $res->toArray()['data'];
        foreach ($data as &$val) {
            $isFail = Db::name('customs_query_delivery_refqueue')->where('bill_num', $val['bill_num'])->find();
            $val['isFail'] = empty($isFail) ? '否' : '是';
        }
        return view("orderdeliverymanage/manifest_list", ['title' => '快递路由查询失败列表', 'page' => $page, 'list' => $data]);
    }

    /**
     * 快递单查询失败列表
     * @param  Request  $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function expressOrderQueryFailList(Request $request)
    {
        if (!$request->has('billNum')) {
            return json(['code' => -1, 'message' => '参数错误']);
        }
        if (empty($request->get('billNum'))) {
            return json(['code' => -1, 'message' => '参数错误']);
        }
        $total = Db::name('customs_query_delivery_refqueue')->where('bill_num', $request->get('billNum'))->count();
        $res = Db::name('customs_query_delivery_refqueue')
            ->where('bill_num', $request->get('billNum'))
            ->order('id', 'desc')
            ->paginate(8, $total,
                ['query' => ['s' => 'admin/OrderDeliveryManage/expressOrderQueryFailList'], 'var_page' => 'page']);
        $page = $res->render();
        $list = $res->toArray()['data'];
        foreach ($list as &$value) {
            $value['data'] = explode(':', $value['data']);
        }
        return view("orderdeliverymanage/express_order_fail_list",
            ['title' => '查询失败订单列表', 'page' => $page, 'list' => $list]);
    }
}
