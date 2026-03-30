<?php

namespace app\api_v3\model;

use think\Model;
use think\Db;

class MerchantTotalAccountModel extends Model
{
    public function updateMerchantAccountStatusById($data)
    {
        $menu_id = [];
        $app_id = null;
        foreach ($data['select'] as $val) {
            array_push($menu_id, ["user_id"=>$data['id'],"system_link_id"=>$val['value']]);
            $app_id .= $val['value'].',';
        }
        $domain_pix = Db::name('total_system_menu')->where('id', 'in', trim($app_id, ","))->field("domain_prefix")->select();
        $user_info = Db::name('total_merchant_account')->where('id', $data['id'])->find();
        Db::startTrans();
        try {
            Db::name('total_merchant_account')->where('id', $data['id'])->update(['status'=>$data['status']]);
            Db::name('total_merchant_rule')->insertAll($menu_id);
            foreach ($domain_pix as $val) {
                if($val['domain_prefix'] != '')
                {
                    $model = model(ucwords($val['domain_prefix'])."UserModel", "model");
                    $uid = $model->createUser($user_info);
                    Db::name('decl_user')->where('id',$uid)->update(['user_status'=>0, 'menus'=>'1,2,3,6,21,22,54,7,8,9,23,24,47,55,56,65,66,86,15,16,17,30,31,18,32,33,57,19,20,28,25,26,27,29,51,52,53,252,34,35,36,38,37,44,46,48,235,', 'business_type'=>'1,']);
                    Db::name('decl_user_role')->insert(['role_id' => 2, 'user_id' => $uid]);
                }
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code'=>-1,'message'=>$e->getMessage()]);
        }
        return json(['code'=>0,'message'=>'操作成功']);
    }


    public function delMerchantAccount($id)
    {
        $tel = Db::name('total_merchant_account')->where('id',$id)->field('mobile')->find();
        //发送不通过消息
        sendReg([
            'SingnName'     =>  'Gogo购购网',
            'submittime'    =>  date('Y-m-d H:i:s',time()),
            'status'        =>  "不通过",
            'tel'           =>  $tel['mobile'],
            'TemplateCode'  =>  'SMS_165412505',//'SMS_35030091'
        ]);
        //删除信息
        Db::name('total_merchant_account')->where('id',$id)->delete();
        return json(['code'=>0,'message'=>'操作成功']);
    }

    public function updateMerchantRule($data)
    {
        if ($data['select']==""){
            return json(['code'=>-1,'message'=>'请选择应用']);
        }
        if (!is_numeric($data['id'])){
            return json(['code'=>-1,'message'=>'参数错误']);
        }
        $menu_id = [];
        $app_id = null;
        foreach ($data['select'] as $val) {
            array_push($menu_id, ["user_id"=>$data['id'],"system_link_id"=>$val['value']]);
        }
        foreach ($data['select'] as $val) {
            $app_id .= $val['value'].',';
        }
        $domain_pix = Db::name('total_system_menu')->where('id', 'in', trim($app_id, ","))->field("domain_prefix")->select();
        $user_info = Db::name('total_merchant_account')->where('id', $data['id'])->find();
        foreach ($domain_pix as $val) {
            if($val['domain_prefix'])
            {
                $model = model(ucwords($val['domain_prefix'])."UserModel", "model");
                $model->createUser($user_info);
            }
        }
        Db::name('total_merchant_rule')->where('user_id',$data['id'])->delete();
        Db::name('total_merchant_rule')->insertAll($menu_id);
        return json(['code'=>0,'message'=>'操作完成']);
    }
}
