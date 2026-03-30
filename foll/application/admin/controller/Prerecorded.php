<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Request;
use think\Db;
use app\admin\model\DeclUserModel;
use app\admin\model\CustomsExportBeforehandDetailedlist;
use app\admin\model\CustomsExportBeforehandDeclarationlist;
use app\admin\model\CustomsExportBeforehandTransferlist;

class Prerecorded extends Auth
{

    // 清单列表
    public function detailedlist()
    {
        return view('prerecorded/detailedlist');
    }
    // 物流列表
    public function logisticslist()
    {
        return view('prerecorded/logisticslist');
    }
    // 物流离境列表
    public function departurelist()
    {
        return view('prerecorded/departurelist');
    }
    // 报关单列表
    public function declarationlist()
    {
        return view('prerecorded/declarationlist');
    }
    // 转关单列表
    public function transferlist()
    {
        return view('prerecorded/transferlist');
    }
    
    public function getlogisticslist(Request $request)
    {
        $limit = $request->get('offset').','.$request->get('limit');
        $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
        $total = Db::name('customs_export_beforehand_logisticslist_batch')->count();
        $data = Db::name('customs_export_beforehand_logisticslist_batch')->order(trim($request->get('sort')), trim($request->get('order')))->limit($limit)->select();
        $user = new DeclUserModel();
        foreach ($data as &$item) {
            $item['create_at'] = date('Y-m-d H:i:s', $item['create_at']);
            $item['user_name'] = $user->getUserNameById($item['uid'])['user_name'];
            $item['manage'] = '<button style="margin-right: 5px;" type="button" onclick="data_add('."'".$item['ylordersn']."'".')" class="btn btn-primary btn-xs">信息反馈</button>';
        }
        return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
    }
    public function getdeparturelist(Request $request)
    {
        $limit = $request->get('offset').','.$request->get('limit');
        $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
        $total = Db::name('customs_export_beforehand_departurelist_batch')->count();
        $data = Db::name('customs_export_beforehand_departurelist_batch')->order(trim($request->get('sort')), trim($request->get('order')))->limit($limit)->select();
        $user = new DeclUserModel();
        foreach ($data as &$item) {
            $item['create_at'] = date('Y-m-d H:i:s', $item['create_at']);
            $item['user_name'] = $user->getUserNameById($item['uid'])['user_name'];
            $item['manage'] = '<button style="margin-right: 5px;" type="button" onclick="data_add('."'".$item['ylordersn']."'".')" class="btn btn-primary btn-xs">信息反馈</button>';
        }
        return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
    }
    //获取列表
    public function getlist(Request $request)
    {
        $model = $this->getObjecType($request->get('type'));

        if (empty($request->get('search'))) {
            //$limit = $request->get('limit');
            $limit = $request->get('offset').','.$request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $total = $model->count();
            $data = $model->order(trim($request->get('sort')), trim($request->get('order')))->limit($limit)->select();
        } else {
            $total = 1;
            $data = $model->where('ordersn', trim($request->get('search')))->select();
        }
        $user = new DeclUserModel();
        $appType = [1 => '新增', 2 => '变更', 3 => '删除'];
        foreach ($data as &$item) {
            $item['create_at'] = date('Y-m-d H:i:s', $item['create_at']);
            $item['user_name'] = $user->getUserNameById($item->uid)['user_name'];
        }
        return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
    }

    public function logisticsresult()
    {
        $ylordersn = input('ylordersn');
        return view('prerecorded/logisticsresult',[
            'ylordersn' => $ylordersn
        ]);
    }

    public function departureresult()
    {
        $ylordersn = input('ylordersn');
        return view('prerecorded/departureresult',[
            'ylordersn' => $ylordersn
        ]);
    }

    
    public function getlogisticsresult(Request $request)
    {
        $limit = $request->get('offset').','.$request->get('limit');
        $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
        $total = Db::name('customs_export_beforehand_result')->where(array('type'=>'CEB505'))->count();
        $data = Db::name('customs_export_beforehand_result')->where(array('type'=>'CEB505','ylordersn'=>$request->get('ylordersn')))->order(trim($request->get('sort')), trim($request->get('order')))->limit($limit)->select();
        foreach ($data as &$item) {
            $item['create_at'] = date('Y-m-d H:i:s', $item['create_at']);
            $item['manage'] = '<button style="margin-right: 5px;" type="button" onclick="data_add('."'".$item['ylordersn']."'".')" class="btn btn-primary btn-xs">修改</button>';
        }
        return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
    }

    public function getdepartureresult(Request $request)
    {
        $limit = $request->get('offset').','.$request->get('limit');
        $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
        $total = Db::name('customs_export_beforehand_result')->where(array('type'=>'CEB509'))->count();
        $data = Db::name('customs_export_beforehand_result')->where(array('type'=>'CEB509','ylordersn'=>$request->get('ylordersn')))->order(trim($request->get('sort')), trim($request->get('order')))->limit($limit)->select();
        foreach ($data as &$item) {
            $item['create_at'] = date('Y-m-d H:i:s', $item['create_at']);
            $item['manage'] = '<button style="margin-right: 5px;" type="button" onclick="data_add('."'".$item['ylordersn']."'".')" class="btn btn-primary btn-xs">修改</button>';
        }
        return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
    }

    public function addlogisticsresult(Request $request)
    {
        $ylordersn = $request->get('ylordersn');
        return view('prerecorded/addlogisticsresult',[
            'ylordersn' => $ylordersn
        ]);
    }

    public function adddepartureresult(Request $request)
    {
        $ylordersn = $request->get('ylordersn');
        return view('prerecorded/adddepartureresult',[
            'ylordersn' => $ylordersn
        ]);
    }

    public function addsavelogisticsresult()
    {
        $data = input('');
        $data['type'] = 'CEB505';
        $data['create_at'] = time();

        if( Db::name('customs_export_beforehand_result')->insert($data) )
        {
            //更新状态
            $batch = Db::name('customs_export_beforehand_logisticslist_batch')->where(array('ylordersn'=>$data['ylordersn']))->find();
            if($batch)
            {
                if($data['status'] == 4)
                {
                    Db::name('customs_export_beforehand_logisticslist_batch')->where(array('id'=>$batch['id']))->update(array('batch_status'=>$data['status'],'success_num'=>$batch['total_num']));
                }else{
                    Db::name('customs_export_beforehand_logisticslist_batch')->where(array('id'=>$batch['id']))->update(array('batch_status'=>$data['status']));
                }
                
            }
            return json(['status'=>1,'message'=>'新增反馈成功']);
        }else{
            return json(['status'=>0,'message'=>'新增反馈失败']);
        }
    }

    public function addsavedepartureresult()
    {
        $data = input('');
        $data['type'] = 'CEB509';
        $data['create_at'] = time();

        if( Db::name('customs_export_beforehand_result')->insert($data) )
        {
            //更新状态
            $batch = Db::name('customs_export_beforehand_departurelist_batch')->where(array('ylordersn'=>$data['ylordersn']))->find();
            if($batch)
            {
                if($data['status'] == 4)
                {
                    Db::name('customs_export_beforehand_departurelist_batch')->where(array('id'=>$batch['id']))->update(array('batch_status'=>$data['status'],'success_num'=>$batch['total_num']));
                }else{
                    Db::name('customs_export_beforehand_departurelist_batch')->where(array('id'=>$batch['id']))->update(array('batch_status'=>$data['status']));
                }
                
            }
            return json(['status'=>1,'message'=>'新增反馈成功']);
        }else{
            return json(['status'=>0,'message'=>'新增反馈失败']);
        }
    }

    protected function getObjecType($type)
    {
        switch ($type) {
            case 'CEB603':
                return new CustomsExportBeforehandDetailedlist();
            case 'baoguan':
                return new CustomsExportBeforehandDeclarationlist(); 
            case 'zhuanguan':
                return new CustomsExportBeforehandTransferlist();          
        }
    }

    
}