<?php

namespace app\api_v3\controller;

use think\Controller;
use think\Request;
use think\Response;
use think\Db;
use think\Log;
use think\Cache;

class AccountCheck extends Controller
{

    protected $model;
    public function __construct()
    {
        $this->model = model("MerchantTotalAccountModel", "model");
    }

    public function check(Request $request)
    {
        $data = $request->post();
        $data['select'] = array(
            array('value'=> '2' ),
        );

        $merchant = Db::name('total_merchant_account')->where('id',$data['id'])->find();
        if( $merchant['status'] == 0 )
        {
            return json(['code' => 1,'message' => '该账户已审核，请勿重复提交！']);
        }else{
            // 审核账户
            if ($data['status']==0) {
                Db::name('total_merchant_account')->where('id',$data['id'])->update(['agents_id' => $data['agents_id']]);
                $result = $this->model->updateMerchantAccountStatusById($data);
                //设置可读可写功能
                Db::name('enterprise_members')->where(['mobile'=>$merchant['mobile']])->update(['authType'=>implode(',',$data['authType'])]);
                // 发消息给代理商
                $agents = Db::name('customs_agents_admin')->where('id',$data['agents_id'])->find();
                $decl_uid = Db::name('decl_user')->where('user_tel',$merchant['mobile'])->value('id');
                if( $agents['openid'] && $decl_uid )
                {
                    sendAgentsTempls([
                        'first' =>'该商户已审核，请设置对账费用',
                        'keyword1' => $merchant['user_name'],
                        'keyword2' => $merchant['mobile'],
                        'keyword3' => date('Y-m-d H:i:s',time()),
                        'remark' => '点击设置对账费用！',
                        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&p=account_check&do=member&m=sz_yi&uid='.$decl_uid.'&op=agents_set',
                        'openid' => $agents['openid']
                    ]);
                }
                return $result;
            }elseif ($data['status']==2){
                Db::name('total_merchant_account')->where('id',$data['id'])->update(['status' => 2]);
                return json(['code' => 1,'message' => '操作成功！']);
            }
        }
    }

}