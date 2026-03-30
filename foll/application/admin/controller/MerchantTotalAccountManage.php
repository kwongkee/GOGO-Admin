<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Request;
use think\Db;

class MerchantTotalAccountManage extends Auth
{

    protected $model;
    public function __construct()
    {
        $this->model = model("MerchantTotalAccountModel", "model");
    }

    public function index(Request $request)
    {
        $status = ['已审核','未审核','不通过'];
        $count =Db::name('total_merchant_account')->order('id', 'desc')->count();
        $user = Db::name('total_merchant_account')->order('id', 'desc')->paginate(10, $count, ['query' => ['s' => 'buss/account/list'], 'var_page' => 'page', 'type' => 'Layui', 'newstyle' => true]);

        $app = Db::name('total_system_menu')->select();
        $page = $user->render();
        $data = $user->toArray()['data'];

        if ($data){
            foreach ($data as &$value){
                $appId = [];
                $appLink = Db::name('total_merchant_rule')->where('user_id',$value['id'])->select();
                if ($appLink){
                    foreach ($appLink as $val){
                        array_push($appId, $val['system_link_id']);
                    }
                }
                $value['app'] = $appId;
            }
        }
        return view('buss/index', ['title'=>'商户总账户管理','page'=>$page,'data'=>$data,'status'=>$status,'app'=>$app]);
    }


    
    /**
     * 审核商户
     *
     * @param  mixed $request
     *
     * @return void
     */
    public function reviewMerchant(Request $request)
    {
        if ($request->post('status')==0) {
            return $this->model->updateMerchantAccountStatusById($request->post());
        }elseif ($request->post('status')==2){
            return $this->model->delMerchantAccount($request->post('id'));
        }
    }

    /**
     * 更新应用权限
     * @param Request $request
     * @return mixed
     */
    public function updateMerchantAppRule(Request $request)
    {
        return $this->model->updateMerchantRule($request->post());
    }
}
