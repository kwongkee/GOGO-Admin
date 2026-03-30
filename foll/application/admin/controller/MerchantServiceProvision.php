<?php

namespace app\admin\controller;

use app\admin\model\DeclUserModel;
use think\Request;
use app\admin\model\CustomsMerchantServiceModel;
use think\Validate;

/**
 * 商户提供的服务
 * Class MerchantServiceProvision
 * @package app\admin\controller
 */
class MerchantServiceProvision extends Auth
{
    
    public $model;
    
    public function __construct(CustomsMerchantServiceModel $customsMerchantServiceModel)
    {
        parent::__construct();
        $this->model = $customsMerchantServiceModel;
    }
    
    public function index(Request $request)
    {
        $title = '商户服务';
        $id    = $request->get('id');
        $name  = (new DeclUserModel())->findUserInfoById($id);
        $res   = $this->model->where('m_id', $id)->paginate(10, true, [
            'query'    => ['s' => 'admin/MerchantServiceProvision/index&id='.$id],
            'var_page' => 'page',
            'type'     => 'Layui',
            'newstyle' => true,
        ]);
        $page  = $res->render();
        $data  = $res->toArray()['data'];
        foreach ($data as &$item) {
            $item['company_name'] = $name['company_name'];
        }
        return view('CustomsSystem/merchantserviceprovision/index', compact('title', 'id', 'page', 'data'));
    }
    
    public function create(Request $request)
    {
        $data     = $request->post();
        $validate = new Validate([
            'mid'             => 'require|number',
            'serviceName'     => 'require',
            'billingType'     => 'require',
            'billingStandard' => 'require',
        ]);
        if (!$validate->check($data)) {
            return json(['code' => -1, 'message' => $validate->getError()]);
        }
        $this->model->insert([
            'm_id'             => $data['mid'],
            'service_name'     => $data['serviceName'],
            'billing_type'     => $data['billingType'],
            'billing_standard' => $data['billingStandard'],
            'create_time'      => time(),
        ]);
        return json(['code' => 0, 'message' => '保存成功']);
    }
    
    public function update(Request $request)
    {
        $data = $request->post();
        $validate = new Validate([
            'id'             => 'require|number',
            'serviceName'     => 'require',
            'billingType'     => 'require',
            'billingStandard' => 'require',
        ]);
        if (!$validate->check($data)) {
            return json(['code' => -1, 'message' => $validate->getError()]);
        }
        $this->model->where('id',$data['id'])->update([
            'service_name'     => $data['serviceName'],
            'billing_type'     => $data['billingType'],
            'billing_standard' => $data['billingStandard']
        ]);
        return json(['code' => 0, 'message' => '更新成功']);
    }
    
    public function delete(Request $request)
    {
        $id = $request->get('id');
        if (!is_numeric($id)){
            return json(['code'=>-1,'message'=>'参数错误']);
        }
        $this->model->where('id',$id)->delete();
        return json(['code'=>0,'message'=>'完成']);
    }
}