<?php


namespace app\admin\controller;

use app\admin\controller;
use think\Request;
use \app\admin\model\CustomsCommonAddressControl;


/**
 * 常用申报收件地址管理
* Class CommonAddressManage
 * @package app\admin\controller
 */
class CommonAddressManage extends Auth
{
    
    public $model;
    
    
    public function __construct(CustomsCommonAddressControl $commonAddressControl)
    {
        parent::__construct();
        $this->model = $commonAddressControl;
    }
    
    public function addrList(Request $request)
    {
        $title = '常用地址管理';
        return view('CustomsSystem/common_address_manage/list',compact('title'));
    }
    
    public function getAddrList(Request $request)
    {
        $limit = $request->get('limit');
        $page = $request->get('page');
        $page = ($page-1)*$limit;
        $where = [];
        if ($request->get('phone')!=""){
            $where['phone']=trim($request->get('phone'));
        }
        $count = $this->model->where($where)->count();
        $data = $this->model->where($where)->limit($page,$limit)->select();
        return json(['code' => 0, 'msg' => '', 'count' => $count, 'data' => $data]);
    }
    
    public function create(Request $request)
    {
        $data = $request->post();
        if ($data['phone']==""||$data['address']==""){
            return json(['code'=>1,'msg'=>'请填写完整']);
        }
        $data['create_time']=time();
        $this->model->create($data);
        return json(['code'=>0,'msg'=>'添加成功']);
    }
    
    public function delete(Request $request)
    {
        $id = $request->get('id');
        if (!is_numeric($id)){
            return json(['code'=>1,'msg'=>'参数错误']);
        }
        $this->model->where('id',$id)->delete();
        return json(['code'=>0,'msg'=>'删除成功']);
    }
}