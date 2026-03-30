<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Db;
use think\Request;
//use GuzzleHttp\Client;

class Defaultinfo extends Auth
{
    
    public function buyer()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            Db::name('default_buyer')->where('id',1)->update($data);
            return json(["code" => 1, "message" => "保存成功"]);
        }else{
            $infos = Db::name('default_buyer')->where('id',1)->find();
            $this->assign('infos', $infos);
            return view();
        }
    }

    public function exchange_rate()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            Db::execute("update ims_currency set rate=".$data['rate']." where code_value=".$data['code_value']);
            return json(["code" => 1, "message" => "保存成功"]);
        }else{
            return view();
        }
    }

    public function getCurrency()
    {
        $data = Db::name('currency')->select();
        return json(['code'=>0,'count'=>count($data),'data'=>$data]);
    }

    public function view_info(Request $request){
        if( $request->isAjax() ) {
            $type = $request->get('type');
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;

            if ($page != 0) {
                $page = $limit * $page;
            }
            $uid = 0;
            $where =['uid'=>$uid, 'is_delete'=>0];

            if($type=='enterprise')
            {
                $count = DB::name('decl_user_enterprise_buyer')->where($where)->count();
                $rows = DB::name('decl_user_enterprise_buyer')->where($where)
                    ->limit($limit)
                    ->order('id', 'desc')
                    ->select();
            }else if($type=='personal'){
                $count = DB::name('decl_user_personal_buyer')->where($where)->count();
                $rows = DB::name('decl_user_personal_buyer')->where($where)
                    ->limit($limit)
                    ->order('id', 'desc')
                    ->select();
            }else if($type=='enterprise_seller'){
                $count = DB::name('decl_user_enterprise_seller')->where($where)->count();
                $rows = DB::name('decl_user_enterprise_seller')->where($where)
                    ->limit($limit)
                    ->order('id', 'desc')
                    ->select();
            }else if($type=='personal_seller'){
                $count = DB::name('decl_user_personal_seller')->where($where)->count();
                $rows = DB::name('decl_user_personal_seller')->where($where)
                    ->limit($limit)
                    ->order('id', 'desc')
                    ->select();
            }
            $rows = $this->objectToArrays($rows);
            foreach ($rows as &$item) {
                $item['country_code'] = Db::name('country_code')->where('code_value',$item['country_code'])->value('code_name');
                $item['platform'] = $item['platform'] == 1 ? '平台买家' : '其他买家';
                $item['create_at']=date('Y-m-d H:i:s', $item['create_at']);
            }
            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }
    }

    public function add_enterprise(){
        if ( request()->isPost() || request()->isAjax()) {
            $data = input();
            try{
                $enterpriseId = $data['enterprise_id'];
                unset($data['isCustomsDeclUnit']);
                unset($data['enterprise_id']);
                // 创建商城买家数据
                $shop_mid = $this->createShopMember($data);
                $data['shop_mid'] = $shop_mid;//商城会员ID

                if( isset($data['company_file']) )
                {
                    $data['company_file'] = implode(',',$data['company_file']);
                }
                if( isset($data['contract_file']) )
                {
                    $data['contract_file'] = implode(',',$data['contract_file']);
                }
                if( isset($data['inquiry_file']) )
                {
                    $data['inquiry_file'] = implode(',',$data['inquiry_file']);
                }
                $data['uid'] = 0;
                $data['create_at'] = time();
                $data['type'] = 1;
                $data['enterprise_id'] = $enterpriseId;
                unset($data['file']);

                DB::name('decl_user_enterprise_buyer')->insert($data);
            }catch(\Expection $e){
                return json(['code' => 0, 'msg' => '新增企业买家失败！']);
            }
            return json(['code' => 1, 'msg' => '新增企业买家成功！']);
        }else{
            $country_code = $this->getCountryCode(); // 国家代码
            $enterprise_customslist = $this->getEnterpriseDeclUnit(); // 报关单位
            return view('',compact('country_code','enterprise_customslist'));
        }
    }

    public function add_personal(){
        if ( request()->isPost() || request()->isAjax()) {
            $data = input();
            try {
                $data['uid'] = 0;
                $data['type'] = 2;
                $data['create_at'] = time();

                unset($data['isCustomsDeclUnit']);

                DB::name('decl_user_personal_buyer')->insert($data);
            } catch (\Exception $e) {
                return json(['code' => 0, 'msg' => '新增买家失败！']);
            }
            return json(['code' => 1, 'msg' => '新增买家成功！']);
        }else{
            $country_code = $this->getCountryCode(); // 国家代码
            $enterprise_customslist = $this->getEnterpriseDeclUnit(); // 报关单位
            return view('',compact('country_code','enterprise_customslist'));
        }
    }

    public function add_enterprise_seller(){
        if ( request()->isPost() || request()->isAjax()) {
            $data = input();
            try{
                if( isset($data['company_file']) )
                {
                    $data['company_file'] = implode(',',$data['company_file']);
                }
                if( isset($data['contract_file']) )
                {
                    $data['contract_file'] = implode(',',$data['contract_file']);
                }
                if( isset($data['inquiry_file']) )
                {
                    $data['inquiry_file'] = implode(',',$data['inquiry_file']);
                }
                $data['uid'] = 0;
                $data['create_at'] = time();
                $data['type'] = 1;
                unset($data['file']);
//                print_r($data);die;
                DB::name('decl_user_enterprise_seller')->insert($data);
            }catch(\Expection $e){
                return json(['code' => 0, 'msg' => '新增企业卖家失败！']);
            }
            return json(['code' => 1, 'msg' => '新增企业卖家成功！']);
        }else{
            $country_code = $this->getCountryCode(); // 国家代码
            return view('',compact('country_code'));
        }
    }

    public function add_personal_seller(){
        if ( request()->isPost() || request()->isAjax()) {
            $data = input();
            try {
                $data['uid'] = 0;
                $data['type'] = 2;
                $data['create_at'] = time();

                DB::name('decl_user_personal_seller')->insert($data);
            } catch (\Exception $e) {
                return json(['code' => 0, 'msg' => '新增卖家失败！']);
            }
            return json(['code' => 1, 'msg' => '新增卖家成功！']);
        }else{
            $country_code = $this->getCountryCode(); // 国家代码
            return view('',compact('country_code'));
        }
    }

    public function personal_edit(Request $request){
        if( $request->isAjax() ) {
            $data = input();
            try {
                $id = $data['id'];
                unset($data['id']);
                unset($data['isCustomsDeclUnit']);

                DB::name('decl_user_personal_buyer')->where('id',$id)->update($data);
            } catch (\Exception $e) {
                return json(['code' => 0, 'msg' => '编辑买家失败！']);
            }
            return json(['code' => 1, 'msg' => '编辑买家成功！']);
        }else{
            $country_code = $this->getCountryCode(); // 国家代码
            $data = DB::name('decl_user_personal_buyer')->where('id',input('id'))->find();
            $enterprise_customslist = $this->getEnterpriseDeclUnit(); // 报关单位
            return view('',compact('country_code','data','enterprise_customslist'));
        }
    }

    public function enterprise_edit(Request $request){
        if( $request->isAjax() ) {
            $data = input();
            try {
                if( isset($data['company_file']) )
                {
                    $data['company_file'] = implode(',',$data['company_file']);
                }
                if( isset($data['contract_file']) )
                {
                    $data['contract_file'] = implode(',',$data['contract_file']);
                }
                if( isset($data['inquiry_file']) )
                {
                    $data['inquiry_file'] = implode(',',$data['inquiry_file']);
                }
                $id = $data['id'];
                unset($data['id']);
                unset($data['isCustomsDeclUnit']);

                DB::name('decl_user_enterprise_buyer')->where('id',$id)->update($data);
            } catch (\Exception $e) {
                return json(['code' => 0, 'msg' => '编辑买家失败！']);
            }
            return json(['code' => 1, 'msg' => '编辑买家成功！']);
        }else{
            $country_code = $this->getCountryCode(); // 国家代码
            $data = DB::name('decl_user_enterprise_buyer')->where('id',input('id'))->find();
            $data['contract_file'] = explode(",",$data['contract_file']);
            $data['inquiry_file'] = explode(",",$data['inquiry_file']);
            $data['company_file'] = explode(",",$data['company_file']);
            $enterprise_customslist = $this->getEnterpriseDeclUnit(); // 报关单位
            return view('',compact('country_code','data','enterprise_customslist'));
        }
    }

    public function personal_seller_edit(Request $request){
        if( $request->isAjax() ) {
            $data = input();
            try {
                $id = $data['id'];
                unset($data['id']);
                DB::name('decl_user_personal_seller')->where('id',$id)->update($data);
            } catch (\Exception $e) {
                return json(['code' => 0, 'msg' => '编辑卖家失败！']);
            }
            return json(['code' => 1, 'msg' => '编辑卖家成功！']);
        }else{
            $country_code = $this->getCountryCode(); // 国家代码
            $data = DB::name('decl_user_personal_seller')->where('id',input('id'))->find();
            return view('',compact('country_code','data'));
        }
    }

    public function enterprise_seller_edit(Request $request){
        if( $request->isAjax() ) {
            $data = input();
            try {
                if( isset($data['company_file']) )
                {
                    $data['company_file'] = implode(',',$data['company_file']);
                }
                if( isset($data['contract_file']) )
                {
                    $data['contract_file'] = implode(',',$data['contract_file']);
                }
                if( isset($data['inquiry_file']) )
                {
                    $data['inquiry_file'] = implode(',',$data['inquiry_file']);
                }
                $id = $data['id'];
                unset($data['id']);
                DB::name('decl_user_enterprise_seller')->where('id',$id)->update($data);
            } catch (\Exception $e) {
                return json(['code' => 0, 'msg' => '编辑卖家失败！']);
            }
            return json(['code' => 1, 'msg' => '编辑卖家成功！']);
        }else{
            $country_code = $this->getCountryCode(); // 国家代码
            $data = DB::name('decl_user_enterprise_seller')->where('id',input('id'))->find();
            $data['contract_file'] = explode(",",$data['contract_file']);
            $data['inquiry_file'] = explode(",",$data['inquiry_file']);
            $data['company_file'] = explode(",",$data['company_file']);
            return view('',compact('country_code','data'));
        }
    }

    public function enterprise_del(Request $request)
    {
        $id = $request->get('id');
        if( !$id )
        {
            return json(['code' => 0, 'msg' => '缺少参数！']);
        }
        Db::name('decl_user_enterprise_buyer')->where('id',$id)->update(['is_delete'=>1]);
        return json(['code' => 1, 'msg' => '删除成功！']);
    }

    public function personal_del(Request $request)
    {
        $id = $request->get('id');
        if( !$id )
        {
            return json(['code' => 0, 'msg' => '缺少参数！']);
        }
        Db::name('decl_user_personal_buyer')->where('id',$id)->update(['is_delete'=>1]);
        return json(['code' => 1, 'msg' => '删除成功！']);
    }

    public function enterprise_seller_del(Request $request)
    {
        $id = $request->get('id');
        if( !$id )
        {
            return json(['code' => 0, 'msg' => '缺少参数！']);
        }
        Db::name('decl_user_enterprise_seller')->where('id',$id)->update(['is_delete'=>1]);
        return json(['code' => 1, 'msg' => '删除成功！']);
    }

    public function personal_seller_del(Request $request)
    {
        $id = $request->get('id');
        if( !$id )
        {
            return json(['code' => 0, 'msg' => '缺少参数！']);
        }
        Db::name('decl_user_personal_seller')->where('id',$id)->update(['is_delete'=>1]);
        return json(['code' => 1, 'msg' => '删除成功！']);
    }

    //国家代码
    public function getCountryCode()
    {
        $country_code = Db::name('country_code')->where('code_value','<>','000')->field(['code_value','code_name'])->select();
        return $this->objectToArrays($country_code);
    }

    //报关单位
    public function getEnterpriseDeclUnit(){
        $EnterpriseDeclUnit = Db::name('enterprise_customslist')->where('info_id','<>','0')->select();
        return $this->objectToArrays($EnterpriseDeclUnit);
    }

    public function objectToArrays($object) {
        return json_decode(json_encode($object), true);
    }

    public function country_check(Request $request)
    {
        $country_code = $request->get('country_code');

        // 制裁国地
        $sanction_country = Db::query('select * from ims_sanction_country where FIND_IN_SET('.$country_code.',country_code) ');
        // 高风国地
        $highrisk_country = Db::query('select * from ims_highrisk_country where FIND_IN_SET('.$country_code.',country_code) ');

        if($sanction_country)
        {
            return json(['code' => 0, 'msg' => '该国家是制裁国家', 'type' => 'sanction_country']);
        }else{
            if($highrisk_country)
            {
                return json(['code' => 0, 'msg' => '该国家是高风险国家' ,'type' => 'highrisk_country']);
            }else{
                return json(['code' => 1, 'msg' => '该国家不是高风险国家']);
            }
        }
    }

    // 通用上传
    public function upload_file(Request $request)
    {
        set_time_limit(0);
        $data = input();

        $path = ROOT_PATH . 'public' . DS . 'uploads' . DS . $data['folder']. DS .$data['type'];

        $this->mkdirs($path);
        $file = request()->file('file');
        if( $file )
        {
            $info = $file->rule('uniqid')->move($path);
            if( $info )
            {
                return json(["code" => 1, "message" => "上传成功", "file_path" => 'foll/public/uploads/'.$data['folder'].'/'.$data['type'].'/'.$info->getSaveName() ]);
            }else{
                return json(["code" => 0, "message" => "上传失败", "path" => "" ]);
            }

        }else{
            return json(["code" => 0, "message" => "请先上传文件！"]);
        }
    }

    //判断文件夹是否存在，没有则新建。
    public function mkdirs($dir, $mode = 0777)
    {
        if (is_dir($dir) || @mkdir($dir, $mode)) {
            return true;
        }
        if (!mkdirs(dirname($dir), $mode)) {
            return false;
        }
        return @mkdir($dir, $mode);
    }

    // 创建商城买家数据
    public function createShopMember($data)
    {
        // 会员
        $member_id = Db::name('mc_members')->insertGetId([
            'uniacid' => 18,
            'mobile' => $data['company_email'],
            'email' => $data['company_email'],
            'password' => md5('888888'),
            'groupid' => 18,
            'credit1' => "0.00",
            'credit2' => "0.00",
            'credit3' => "0.00",
            'credit4' => "0.00",
            'credit5' => "0.00",
            'credit6' => "0.00",
            'createtime' => time(),
            'realname' => $data['company_name'],
            'nickname' => $data['company_name']
        ]);
        // 粉丝
        $fans_id = Db::name('mc_mapping_fans')->insertGetId([
            'acid' => 18,
            'uniacid' => 18,
            'uid' => $member_id,
            'openid' => $data['company_email'],
            'nickname' => $data['company_name'],
            'follow' => 1,
            'followtime' => 0,
            'unfollowtime' => 0,
        ]);
        // 商城会员
        $shop_mid = Db::name('sz_yi_member')->insertGetId([
            'uniacid' => 18,
            'uid' => $member_id,
            'openid' => $data['company_email'],
            'realname' => $data['company_name'],
            'mobile' => $data['company_email'],
            'pwd' => md5('888888'),
            'createtime' => time(),
            'nickname' => $data['company_name'],
            'avatar' => 'https://shop.gogo198.cn/attachment/headimg_3.jpg',
            'regtype' => 1
        ]);
        // 送货地址
        $shop_maddr_id = Db::name('sz_yi_member_address')->insertGetId([
            'uniacid' => 18,
            'openid' => $data['company_email'],
            'realname' => $data['company_name'],
            'mobile' => $data['area_code'].' '.$data['company_tel'],
            'address' => $data['company_address'],
            'isdefault' => 1
        ]);

        return $shop_mid;
    }

    //换汇机构
    public function exchange(Request $request){
        if( $request->isAjax() ) {
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $rows = Db::name('exchange_manage')->limit($limit)->order($order)->select();
            foreach($rows as $k => $v){
                $rows[$k]['manage'] = '<button type="button" onclick="editInfo('."'编辑','".Url('admin/defaultInfo/exchange_edit')."'".','."'".$v['id']."'".')" class="btn btn-primary btn-xs">编辑</button>';
                $rows[$k]['manage'] .= '<button type="button" onclick="delInfo('."'".$v['id']."'".')" class="btn btn-danger btn-xs">删除</button>';
                $connect_id = explode(',',$v['connect_id']);
                $connect_name = '';
                foreach($connect_id as $k2=>$v2){
                    $fem = Db::name('foreign_exchange_manage')->where('id',$v2)->field('name')->find();
                    $connect_name .= $fem['name'].',';
                }
                $rows[$k]['connect_name'] = substr($connect_name,0,strlen($connect_name)-1);

                $rows[$k]['create_at'] = date('Y-m-d H:i:s',$v['create_at']);
            }
            $count = DB::name('exchange_manage')->count();
            return json(['status'=>0,'message'=>'','total'=>$count,'rows'=>$rows]);
        }else{
            return view('');
        }
    }

    //新增换汇机构
    public function exchange_add(Request $request){
        if( $request->isAjax() ) {
            $data = input();
            try {
                $data['create_at'] = time();
                Db::name('exchange_manage')->insert($data);
            }catch (\Exception $e){
                return json(['code'=>0,'msg'=>'新增换汇机构失败']);
            }
            return json(['code'=>1,'msg'=>'新增换汇机构成功']);
        }else{
            $connect = Db::name('foreign_exchange_manage')->order('id','desc')->select();
            $connect = json_encode($connect);

            return view('',compact('connect'));
        }
    }

    //编辑换汇机构
    public function exchange_edit(Request $request){
        $data = input();
        if( $request->isAjax() ) {
            if($data['id']){
                try{
                    $id = $data['id'];
                    unset($data['id']);
                    Db::name('exchange_manage')->where('id',$id)->update($data);
                }catch(\Exception $e){
                    return json(['code'=>0,'msg'=>'编辑换汇机构失败']);
                }
                return json(['code'=>1,'msg'=>'编辑换汇机构成功']);
            }
        }else{
            $connect = Db::name('foreign_exchange_manage')->order('id','desc')->select();
            $connect = json_encode($connect);
            $dat = Db::name('exchange_manage')->where('id',$data['id'])->find();
            return view('',compact('dat','connect'));
        }
    }

    //删除换汇机构
    public function exchange_del(Request $request){
        if( $request->isAjax() ) {
            $data = input();
            try {
                Db::name('exchange_manage')->where('id',$data['id'])->del();
            }catch (\Exception $e){
                return json(['code'=>0,'msg'=>'删除换汇机构失败']);
            }
            return json(['code'=>1,'msg'=>'删除换汇机构成功']);
        }
    }

    //结汇机构
    public function foreign_exchange(Request $request){
        if( $request->isAjax() ) {
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $rows = Db::name('foreign_exchange_manage')->limit($limit)->order($order)->select();
            $count = Db::name('foreign_exchange_manage')->count();

            foreach($rows as $k => $v){
                $rows[$k]['manage'] = '<button type="button" onclick="editInfo('."'编辑','".Url('admin/defaultInfo/foreign_exchange_edit')."'".','."'".$v['id']."'".')" class="btn btn-primary btn-xs">编辑</button>';
                $rows[$k]['manage'] .= '<button type="button" onclick="delInfo('."'".$v['id']."'".')" class="btn btn-danger btn-xs">删除</button>';
                $rows[$k]['create_at'] = date('Y-m-d H:i:s',$v['create_at']);
            }

            return json(['status'=>0,'message'=>'','total'=>$count,'rows'=>$rows]);
        }else{
            return view();
        }
    }

    //新增结汇机构
    public function foreign_exchange_add(Request $request){
        if( $request->isAjax() ) {
            $data = input();
            try {
                $data['create_at'] = time();
                Db::name('foreign_exchange_manage')->insert($data);
            }catch (\Exception $e){
                return json(['code'=>0,'msg'=>'新增结汇机构失败']);
            }
            return json(['code'=>1,'msg'=>'新增结汇机构成功']);
        }else{
            return view('');
        }
    }

    //编辑结汇机构
    public function foreign_exchange_edit(Request $request){
        $data = input();
        if( $request->isAjax() ) {
            if($data['id']){
                try{
                    $id = $data['id'];
                    unset($data['id']);
                    Db::name('foreign_exchange_manage')->where('id',$id)->update($data);
                }catch(\Exception $e){
                    return json(['code'=>0,'msg'=>'编辑结汇机构失败']);
                }
                return json(['code'=>1,'msg'=>'编辑结汇机构成功']);
            }
        }else{
            $dat = Db::name('foreign_exchange_manage')->where('id',$data['id'])->find();
            return view('',compact('dat'));
        }
    }

    //删除结汇机构
    public function foreign_exchange_del(Request $request){
        if( $request->isAjax() ) {
            $data = input();
            try {
                Db::name('foreign_exchange_manage')->where('id',$data['id'])->del();
            }catch (\Exception $e){
                return json(['code'=>0,'msg'=>'删除结汇机构失败']);
            }
            return json(['code'=>1,'msg'=>'删除结汇机构成功']);
        }
    }

}