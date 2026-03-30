<?php

namespace app\api_v3\controller;

use think\Controller;
use think\Request;
use think\Response;
use think\Db;
use think\Log;
use think\Cache;

class WarehouseAccountCheck extends Controller
{

    public function check(Request $request)
    {
        $data = $request->post();

        $merchant = Db::name('warehouse_manager')->where('id',$data['id'])->find();

        if( $merchant['status'] == 1 )
        {
            return json(['code' => 1,'message' => '该员工已审核，请勿重复提交！']);
        }else{
            // 审核账户
            $status = '';
            if ($data['status']==1) {
                Db::name('warehouse_manager')->where('id',$data['id'])->update(['status' => 1]);
                $status = '已通过审核';
            }elseif ($data['status']==2){
                Db::name('warehouse_manager')->where('id',$data['id'])->update(['status' => 2]);
                $status = '审核不通过';
            }
            $job = '';
            switch($merchant['type']){
                case 1:
                    $job = '国内仓库管理员';
                    break;
                case 2:
                    $job = '香港仓库管理员';
                    break;
                case 3:
                    $job = '国外仓库管理员';
                    break;
            }

            sendWechatMsg(json_encode([
                'call'=>'confirmCollectionNotice',
                'first' =>'您好，您申请的岗位['.$job.']'.$status.'！',
                'keyword1' => '['.$job.']岗位',
                'keyword2' => $status,
                'keyword3' => date('Y-m-d H:i:s',time()),
                'remark' => '',
                'url' => 'http://shop.gogo198.cn/app/index.php?i=3&c=entry&p=login&do=warehouse&m=sz_yi&op=display',
                'openid' => $merchant['openid'],
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]));

            return json(['code' => 1,'message' => '操作成功！']);
        }
    }

}