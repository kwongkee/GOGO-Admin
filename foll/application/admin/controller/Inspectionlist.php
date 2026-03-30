<?php

namespace app\admin\controller;
use think\Request;
use think\Db;
use Util\data\Sysdb;

class Inspectionlist extends Auth{

    // 查验风险批次；
    public function index()
    {
        $list = Db::name('customs_elec_order_tmp')
            ->where(['status'=>3])
            ->group('batch_num')
            ->field(['batch_num','EntOrderNo'])
            ->paginate(12, true,[
                'query' => ['s' => 'insps/index'],
                'var_page' => 'page',
                'type'  => 'Layui',
                'newstyle' => true
            ]);

        // 分页
        $page = $list->render();

        $this->assign('order',$list);
        $this->assign('page',$page);

        return view('insps/index',['title'=>'查验风控']);
    }


    // 风险列表
    public function insplist(Request $req) {

        $batch_num = $req->get('batch_num');

        $list = Db::name('customs_elec_riskdeclarations')
            ->where(['batch_num'=>$batch_num])
            ->paginate(12, true,[
                'query' => ['s' => 'insps/insplist'],
                'var_page' => 'page',
                'type'  => 'Layui',
                'newstyle' => true
            ]);

        // 分页
        $page = $list->render();

        $this->assign('order',$list);
        $this->assign('page',$page);
        $this->assign('batch_num',$batch_num);

        return view('insps/insplist',['title'=>'风险列表']);
    }


    // 更新状态;
    public function updates(Request $req) {

        $data = $req->post();
        if(!empty($data)) {
            // 更新条件
            $ordersn = trim($data['ordersn']);
            // 退回
            if($data['type'] == 'no') {

                $up = DB::name('customs_elec_order_tmp')->where('EntOrderNo',$ordersn)->update(['status'=>2]);
                $ups = DB::name('customs_elec_riskdeclarations')->where('ordersn',$ordersn)->update(['status'=>2]);

            } else { // 通过
                $up = DB::name('customs_elec_order_tmp')->where('EntOrderNo',$ordersn)->update(['status'=>1]);
                $ups = DB::name('customs_elec_riskdeclarations')->where('ordersn',$ordersn)->update(['status'=>1]);
            }

            if($up && $ups) {
                return json_encode(['status'=>1,'msg'=>'数据更新成功']);
            }

            return json_encode(['status'=>0,'msg'=>'数据更新失败']);
        }

        return json_encode(['status'=>0,'msg'=>'无数据需要更新']);

    }


    public function checkAll(Request $req) {

        $batch_num = $req->post('batch_num');
        // 检测是否还有状态==3的，没有则更新，否则更新失败
        $data = DB::name('customs_elec_order_tmp')->where(['batch_num'=>$batch_num,'status'=>3])->find();
        if(!empty($data)) {
            return json_encode(['status'=>0,'msg'=>'更新失败，你还有未处理的订单！']);
        }

        // 没有数据了，更新状态；检测是否有购买风险，有则更新为购买风险，否则更新为预提审核；4购买风险，6查验风险
        $riskd = DB::name('customs_elec_order_risk')->where('batch_num',$batch_num)->find();
        if(!empty($riskd)) {
            $upd = DB::name('customs_batch')->where('batch_num',$batch_num)->update(['check_status'=>4]);
        } else {
            $upd = DB::name('customs_batch')->where('batch_num',$batch_num)->update(['check_status'=>1]);
        }

        if($upd) {
            return json_encode(['status'=>1,'msg'=>'更新完成']);
        }

        return json_encode(['status'=>0,'msg'=>'更新失败']);
    }

}

?>