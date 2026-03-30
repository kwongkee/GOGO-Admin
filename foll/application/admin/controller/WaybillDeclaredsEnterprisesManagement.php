<?php

namespace app\admin\controller;

use think\Curl;
use think\Db;
use think\Request;


/**
 * w物流申报企业管理
 * Class DeclaredsEnterprisesManagement
 * @package app\admin\controller
 */
class WaybillDeclaredsEnterprisesManagement extends Auth
{
    
    public function index(Request $request)
    {
        return view('waybillldeclare_enterprises_manage/index', ['title' => '物流申报企业管理']);
    }
    
    
    public function select(Request $request)
    {
        $limit = $request->get('limit');
        $page  = ($request->get('page') - 1) * $limit;
        $count = Db::name('customs_waybill_declare_enterprise')->count();
        $data  = Db::name('customs_waybill_declare_enterprise')->limit($page, $limit)->select();
        $declType=['1'=>'在线申报','2'=>'接口申报','3'=>'联合申报'];
        foreach ($data as &$item){
            $item['create_time']=date('Y-m-d H:i:s',$item['create_time']);
            $item['decl_type']=$declType[$item['decl_type']];
        }
        return json(["code" => 0, "msg" => "完成", "count" => $count, "data" => $data]);
    }
    
    public function create(Request $request)
    {
        $data     = $request->post();
        $fileList = $request->file();
        foreach ($data as $item) {
            if ($item == "") {
                return json(['code' => 1, 'msg' => '请填写完整数据']);
            }
        }
        
        if (!empty($fileList)) {
            if (count($fileList) < 2) {
                return json(['code' => 1, 'msg' => '公私钥必须一起上传']);
            }
            $path               = ROOT_PATH.'public/uploads';
            $publicKey          = $fileList['public_key_path']->move($path);
            $publicKeyFileName  = $publicKey->getSaveName();
            $privateKey         = $fileList['private_key_path']->move($path);
            $privateKeyFileName = $privateKey->getSaveName();
            //上传文件到申报服务器那边并返回文件路径
            $curl = new Curl();
            $rep  = $curl->post('http://declare.gogo198.cn/api/UploadSignKey/save', [
                $data['decl_enter_code'].'_public_key'  => base64_encode(file_get_contents($path.'/'.$publicKeyFileName)),
                $data['decl_enter_code'].'_private_key' => base64_encode(file_get_contents($path.'/'.$privateKeyFileName)),
            ]);
            $body = json_decode($rep->response, true);
            if (empty($body)) {
                return json(['code' => 1, 'msg' => '上传文件失败']);
            }
            @unlink($path.'/'.$publicKeyFileName);
            @unlink($path.'/'.$privateKeyFileName);
            $data['public_key_path']  = $body['data'].$data['decl_enter_code'].'_public_key.key';
            $data['private_key_path'] = $body['data'].$data['decl_enter_code'].'_private_key.key';
        }
        if (isset($data['id'])&&is_numeric($data['id'])){
            $id= $data['id'];
            unset($data['id']);
            Db::name('customs_waybill_declare_enterprise')->where('id',$id)->update($data);
            return json(['code'=>0,'msg'=>'更新成功']);
        }else{
            $data['create_time'] = time();
            Db::name('customs_waybill_declare_enterprise')->insert($data);
            return json(['code'=>0,'msg'=>'添加成功']);
        }
      
    }
    
    public function delete(Request $request){
        $id=$request->get('id');
        if (!is_numeric($id)){
            return json(['code'=>1,'msg'=>'参数错误']);
        }
        Db::name('customs_waybill_declare_enterprise')->where('id',$id)->delete();
        return json(['code'=>0,'msg'=>'删除成功']);
    }

    public function add_license(Request $request){
        $id = $request->get('waybill_id');
        if ( request()->isPost() || request()->isAjax()){
            $list = Db::name('customs_waybill_license_info')->where('enterprise_id',$id)->select();
            return json(['code'=>1,'list'=>$list]);
        }else{
            return view('waybillldeclare_enterprises_manage/add_license',['title' => '物流申报企业管理','enterprise_id'=>$id]);
        }


    }
}