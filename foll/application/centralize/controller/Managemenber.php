<?php
/**
 * 集运系统后台管理（集运商用户管理）
 * 2022-10-28
 */
namespace app\centralize\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Session;

class Managemenber extends Controller{

    /**
     * @function1 权限
     */
    //权限菜单列表
    public function menu_list(Request $request){
        $dat = input();

        return view('');
    }

    //获取角色权限列表功能
    public function get_menu(Request $request){
        $dat = input();
        $_status = ['显示', '隐藏'];
        $list = Db::name('centralize_manage_menu')->select();
        foreach ($list as &$item) {
            $item['status'] = $_status[$item['status']];
        }
        return json(['code' => 0, 'msg' => '', 'count' => count($list), 'data' => $list]);
    }

    //保存权限
    public function save_menu(Request $request){
        $dat = input();
        if(isset($dat['id'])){
            $id = intval($dat['id']);
        }else{
            $id = 0;
        }

        if(isset($dat['pid'])){
            $pid = intval($dat['pid']);
        }else{
            $pid = 0;
        }
        
        if($request->isAjax()) {
            if($id>0){
                #修改
                $res = Db::name('centralize_manage_menu')->where(['id'=>$id])->update([
                    'title'=>trim($dat['title']),
                    'url'=>trim($dat['url']),
                    'sort'=>intval($dat['sort']),
                    'status'=>intval($dat['status']),
                ]);
            }else{
                #新增
                $res = Db::name('centralize_manage_menu')->insert([
                    'title'=>trim($dat['title']),
                    'url'=>trim($dat['url']),
                    'pid'=>$pid,
                    'sort'=>intval($dat['sort']),
                    'status'=>intval($dat['status']),
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功！']);
            }
        }else{
            $data = ['id'=>0,'title'=>'','url'=>'','status'=>0,'sort'=>0,'level'=>0,'is_menu'=>0,'pid'=>0];
            if($id>0){
                $data = Db::name('centralize_manage_menu')->where(['id'=>$id])->find();
            }
            return view('',['data'=>$data,'pid'=>$pid]);
        }
    }

    //删除权限
    public function del_menu(Request $request){
        $dat = input();
        if($request->isAjax()) {
            $res = Db::name('centralize_manage_menu')->where(['id'=>intval($dat['id'])])->delete();
            if($res){
                return json(['code'=>0,'msg'=>'删除成功']);
            }
        }
    }

    /**
     * @function2 角色
     */
    //角色列表
    public function role_lists(Request $request){
        $dat = input();
        if($request->isAjax()) {
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');
            $where['status'] = 1;
            if (!empty($search)) {
                $where['name'] = ['like', '%' . $search . '%'];
            }
            $total = DB::name('centralize_manage_role')->where($where)->count();
            $data = Db::name('centralize_manage_role')
                ->where($where)
                ->limit($page,$limit)
                ->order('createtime', 'desc')
                ->select();
            $status = ['禁用','启用'];
            if(!empty($data)) {
                foreach ($data as $k => &$v) {
                    $v['status'] = $status[$v['status']];
                    $v['createtime'] = date('Y-m-d H:i',$v['createtime']);
                }
            }

            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    //保存角色信息
    public function save_role(Request $request){
        $dat = input();
        if(isset($dat['id'])){
            $id = intval($dat['id']);
        }else{
            $id = 0;
        }
        if($request->isAjax()) {
            if($dat['pa']==1){
//                $auth_list = [];
//                foreach($dat['auth_id'] as $k=>$v){
//                    $auth_list[] = ['auth_id'=>$v,'auth_type'=>$dat['auth_select'][$k]];
//                }
//                $auth_list = json_encode($auth_list,true);

                if($id>0){
                    #编辑
                    $res = Db::name('centralize_manage_role')->where(['id'=>$id])->update([
                        'name'=>trim($dat['name']),
                        'desc'=>trim($dat['desc']),
                        'authList'=>$dat['authList'],
                        'distribution_auth'=>intval($dat['distribution_auth']),
                        'data'=>json_encode($dat['data'],true),
                        'connect'=>json_encode($dat['connect'],true),
                        'commission_type'=>intval($dat['commission_type']),
                        'status'=>intval($dat['status']),
                    ]);
                }else{
                    #新增
                    $res = Db::name('centralize_manage_role')->insert([
                        'name'=>trim($dat['name']),
                        'desc'=>trim($dat['desc']),
                        'authList'=>$dat['authList'],
                        'status'=>intval($dat['status']),
                        'distribution_auth'=>intval($dat['distribution_auth']),
                        'data'=>json_encode($dat['data'],true),
                        'connect'=>json_encode($dat['connect'],true),
                        'commission_type'=>intval($dat['commission_type']),
                        'createtime'=>time(),
                    ]);
                }

                if($res){
                    return json(['code'=>1,'msg'=>'保存成功']);
                }
            }
        }else{
            $data = ['name'=>'','desc'=>'','status'=>0,'authList'=>'','distribution_auth'=>'','data'=>['data_auth'=>'','view_up'=>'','view_down'=>''],'connect'=>['connect_auth'=>'','connect_up'=>'','connect_down'=>''],'commission_type'=>''];

            if($id>0){
                #角色信息
                $data = Db::name('centralize_manage_role')->where(['id'=>$id])->find();

                $data['authList'] = json_decode($data['authList'],true);
                $data['data'] = json_decode($data['data'],true);
                $data['connect'] = json_decode($data['connect'],true);
            }

            return view('',['data'=>$data,'id'=>$id]);
        }
    }

    public function get_menus(Request $request){
        $data = input();
        if(isset($data['level_id'])){
            $level_id = $data['level_id'];
        }else{
            $level_id = 0;
        }

        $levelData = Db::name('centralize_manage_role')->where(['id'=>$level_id])->field('authList')->find();
        if($level_id)
        {
            $level_menus = ",".$levelData['authList'];
        }else{
            $level_menus = "";
        }

        $list = Db::name('centralize_manage_menu')->where(['status'=>0])->select();

        $newList = [];
        foreach ($list as $item) {
            array_push($newList, ['id' => $item['id'], 'pId' => $item['pid'], 'title' => $item['title'] , 'checked' => strpos($level_menus,",".$item['id'].",") !== false ? true : false ]);
            foreach ($list as $value) {
                if ($item['id'] == $value['pid'] && !$value['pid'] != 0) {
                    array_push($newList, ['id' => $value['id'], 'pId' => $value['pid'], 'title' => $value['title'], 'checked' => strpos($level_menus,",".$value['id'].",") !== false ? true : false ]);
                }
            }
        }

        $data['menuList'] = $newList;
        return json(['code'=>0,'data'=>$data]);
    }

    //保存角色分配人员
    public function save_role_person(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        if($request->isAjax()) {
            //插入/修改数据库
            if($id>0){
                $res = Db::name('centralize_manage_role')->where(['id'=>$id])->update(['decl_ids'=>$dat['decl_ids']]);
            }
            return json(['code'=>1,'msg'=>'保存成功']);
        }else{
            //获取该角色配置的人员
            $list = Db::name('centralize_manage_role')->where(['id'=>$id])->find();
            $list['decl_ids'] = json_decode($list['decl_ids'],true);

            return view('',['list'=>$list]);
        }
    }

    //删除角色
    public function del_role(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        if($id>0){
            $res = Db::name('centralize_manage_role')->where(['id'=>$id])->delete();
            if($res){
                return json(['code'=>1,'msg'=>'删除角色成功！']);
            }
        }
    }

    /**
     * @function3 用户
     */
    //用户列表
    public function person_lists(Request $request){
        $dat = input();
        if($request->isAjax()) {
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');
            $where = [];
            if (!empty($search)) {
                $where['name'] = ['like', '%' . $search . '%'];
            }
            $total = DB::name('centralize_manage_person')->where($where)->count();
            $data = Db::name('centralize_manage_person')
                ->where($where)
                ->limit($page,$limit)
                ->order('createtime', 'desc')
                ->select();
            $status = ['已发送待认证','已发送已认证'];
            if(!empty($data)) {
                foreach ($data as $k => &$v) {
                    $v['status'] = $status[$v['status']];
                    $v['createtime'] = date('Y-m-d H:i',$v['createtime']);
                    $v['role_id'] = Db::name('centralize_manage_role')->where(['id'=>$v['role_id']])->find()['name'];
                }
            }

            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    //保存用户信息
    public function save_person(Request $request){
        $dat = input();
        $dat['type2'] = 1;
        if(isset($dat['id'])){
            $id = intval($dat['id']);
        }else{
            $id = 0;
        }
        if($request->isAjax()) {
            #判断有无新增过
            $user = Session::get("myUser");
            if($id>0){
                $res = Db::name('centralize_manage_person')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'type'=>intval($dat['type']),
                    'tel'=>intval($dat['type'])==1?trim($dat['tel']):'',
                    'email'=>intval($dat['type'])==2?trim($dat['email']):'',
                    'ent_id'=>intval($dat['ent_id']),
                    'status'=>intval($dat['status']),
                ]);
            }else{
                if(intval($dat['type'])==1){
                    $is_have = Db::name('centralize_manage_person')->where('tel',trim($dat['tel']))->find();
                    if($is_have['id']>0){
                        return json(['code'=>-1,'msg'=>"该手机号已存在!"]);
                    }
                }elseif(intval($dat['type'])==2){
                    $is_have = Db::name('centralize_manage_person')->where('email',trim($dat['email']))->find();
                    if($is_have['id']>0){
                        return json(['code'=>-1,'msg'=>"该邮箱号已存在!"]);
                    }
                }

                $time = time();
                $res = Db::name('centralize_manage_person')->insertGetId([
                    'name'=>trim($dat['name']),
                    'type'=>intval($dat['type']),
                    'tel'=>intval($dat['type'])==1?trim($dat['tel']):'',
                    'email'=>intval($dat['type'])==2?trim($dat['email']):'',
                    'ent_id'=>intval($dat['ent_id']),
                    'status'=>0,
                    'createtime'=>$time,
                ]);
                //企业信息
                $ent_info = Db::name('customs_enterprise_info')->where(['id'=>$dat['ent_id']])->find();
                if($res){
                    $em_id = Db::name('enterprise_members')->insertGetId([
                        'uniacid'=>3,
                        'nickname'=>trim($dat['name']),
                        'realname'=>trim($dat['name']),
                        'mobile'=>intval($dat['type'])==1?trim($dat['tel']):'',
                        'reg_type'=>1,
                        'create_at'=>$time,
                        'centralizer_id'=>$res,
                        'is_verify'=>0
                    ]);
                    Db::name('centralize_manage_person')->where(['id'=>$res])->update([
                        'enterprise_id'=>$em_id
                    ]);
                    Db::name('enterprise_basicinfo')->insertGetId([
                        'member_id'=>$em_id,
                        'name' => trim($dat['name']),
                        'operName' => $ent_info['legal_name'],
                        'orgNo' => $ent_info['orgNo'],
                        'create_at' => $time,
                    ]);
                    $unique_id = '';
                    if($dat['type']==1){$unique_id = md5((trim($dat['tel']).date('YmdHis')));}
                    elseif($dat['type']==2){$unique_id = md5((trim($dat['email']).date('YmdHis')));}
                    Db::name('total_merchant_account')->insert([
                        'unique_id'=>$unique_id,
                        'mobile'=>trim($dat['tel']),
                        'password'=>password_hash(trim($dat['tel']), PASSWORD_DEFAULT),
                        'uniacid' => 3,
                        'user_name' => trim($dat['name']),
                        'company_name' =>$ent_info['enterprise_name'],
                        'create_time'=>$time,
                        'desc' =>'',
                        'status'=>0,
                        'user_email'=>'',
                        'address'=>'',
                        //'address'=>$basic_info['address'],
                        'company_tel'=>'',
                        'account_type'=>2,
                        'openid' => '',
                        'enterprise_id' => $em_id
                    ]);
                    Db::name('decl_user')->insert([
                        'user_name'=>$dat['name'],
                        'user_tel'=>$dat['tel'],
                        'user_email'=>$dat['email'],
                        'user_password'=>md5('888888'),
                        'uniacid'=>3,
                        'plat_id'=>1,
                        'created_at'=>date('Y-m-d H:i:s'),
                        'user_status'=>0,
                        'buss_id'=>3,
                        'company_name'=>$ent_info['enterprise_name'],
                        'company_num'=>'',#不知道是什么
                        'address'=>'',
                        'enterprise_id'=>$em_id,
                    ]);

                    //创建代码集信息
                    $this->createCodeInfo($res);
                    
                    $title = '恭喜您成为Gogo集运商';
                    $link = '?pre_id='.base64_encode($res);
//                    MQ==
                    #1、判断是手机注册还是邮箱注册
                    if($dat['type']==1){
                        #1.1、手机,发送链接去手机打开认证
                        if(intval($dat['type2'])==1){
                            #国内
                            $post_data = [
                                'spid'=>'254560',
                                'password'=>'J6Dtc4HO',
                                'ac'=>'1069254560',
                                'mobiles'=>trim($dat['tel']),
                                'content'=>'您好！恭喜您成为Gogo集运商用户。请点击链接进行验证：https://gather.gogo198.cn/centralize/index/attestation'.$link.'，认证成功后可登陆集运综合管理系统后台：https://gather.gogo198.cn/centralizer_manage/login 登陆方式：手机，账号:'.trim($dat['tel']).'。 【GOGO】',
                                // '您好！恭喜您成为Gogo集运商。请点击集运后台链接进行登录：https://gather.gogo198.cn/centralizer_manage/login【GOGO】',
                                    
                            ];
                        }elseif(intval($dat['type2'])==2){
                            #国外
                            $post_data = [
                                'spid'=>'254561',
                                'password'=>'zvHcNStS',
                                'ac'=>'1069254561',
                                'mobiles'=>trim($dat['tel']),
                                'content'=>'
您好! 恭喜您註冊成為Gogo集運商用戶。請點擊鏈接進行驗證：https://gather.gogo198.cn/centralize/index/attestation'.$link.'，认证成功后可登陆集运综合管理系统后台：https://gather.gogo198.cn/centralizer_manage/login 登陆方式：手机，账号:'.trim($dat['tel']).' 【Gogo】',
                            ];
                        }
                        $post_data = json_encode($post_data,true);
                        $res = httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                            'Content-Type: application/json; charset=utf-8',
                            'Content-Length:' . strlen($post_data),
                            'Cache-Control: no-cache',
                            'Pragma: no-cache'
                        ));// 必须声明请求头);
                    }elseif($dat['type']==2){
                        #1.2、邮箱,发送链接去邮箱打开注册
                        $res = cklein_mailAli(trim($dat['email']), '尊敬的客户', $title, '您好！恭喜您成为Gogo集运商用户。请点击链接进行验证：https://gather.gogo198.cn/centralize/index/attestation'.$link.'，认证成功后可登陆集运综合管理系统后台：https://gather.gogo198.cn/centralizer_manage/login 登陆方式：邮箱，账号:'.trim($dat['email']));
                    }
                }
            }
            return json(['code'=>1,'msg'=>"保存成功"]);
        }else{
            $data = ['name'=>'','type'=>0,'tel'=>'','email'=>'','ent_id'=>''];
//            $role = Db::name('centralize_manage_role')->order('id','desc')->select();
            if($id>0){
                $data = Db::name('centralize_manage_person')->where(['id'=>$id])->find();
            }
            $enterprise = Db::name('customs_enterprise_info')->order('id','desc')->select();

            return view('',['data'=>$data,'id'=>$id,'enterprise'=>$enterprise]);
        }
    }

    //创建用户代码集信息
    public function createCodeInfo($person_id){
        $time = time();
        #1、创建角色表
        $role = Db::name('centralize_manage_role')->select();
        foreach($role as $k=>$v){
            Db::name('centralize_manage_level')->insert([
                'uid'=>$person_id,
                'name'=>$v['name'],
                'desc'=>$v['desc'],
                'authList'=>$v['authList'],
                'distribution_auth'=>$v['distribution_auth'],
                'data'=>$v['data'],
                'connect'=>$v['connect'],
                'commission_type'=>$v['commission_type'],
                'createtime'=>$time
            ]);
        }
        #2、创建快递表

        #3、创建货物属性表
        $goods_value = Db::name('centralize_goods_value')->select();
        foreach($goods_value as $k=>$v){
            Db::name('centralize_goods_value_agent')->insert([
                'uid'=>$person_id,
                'title'=>$v['title'],
                'remark'=>$v['remark'],
            ]);
        }
    }

    //删除用户信息
    public function del_person(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        if($id>0){
            $res = Db::name('centralize_manage_person')->where(['id'=>$id])->delete();
            if($res){
                Db::name('enterprise_members')->where(['centralizer_id'=>$id])->delete();
                return json(['code'=>1,'msg'=>'删除用户成功！']);
            }
        }
    }

    //货物属性列表
    public function cargo_lists(Request $request){
        $dat = input();
        if($request->isAjax()) {
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');
            $where = [];
            if (!empty($search)) {
                $where['title'] = ['like', '%' . $search . '%'];
            }
            $total = DB::name('centralize_goods_value')->where($where)->count();
            $data = Db::name('centralize_goods_value')
                ->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    //保存货物属性信息
    public function save_cargo(Request $request){
        $dat = input();
        if(isset($dat['id'])){
            $id = intval($dat['id']);
        }else{
            $id = 0;
        }
        if($request->isAjax()) {
            if($id>0){
                $res = Db::name('centralize_goods_value')->where(['id'=>$id])->update([
                    'title'=>trim($dat['title']),
                    'remark'=>preg_replace("/(\r\n|\n|\r|\t)/i", '', $dat['remark']),
                ]);
            }else {
                $res = Db::name('centralize_goods_value')->insertGetId([
                    'title'=>trim($dat['title']),
                    'remark'=>preg_replace("/(\r\n|\n|\r|\t)/i", '', $dat['remark']),
                ]);
            }
            return json(['code'=>1,'msg'=>"保存成功"]);
        }else{
            $data = ['title'=>'','remark'=>''];
            if($id>0){
                $data = Db::name('centralize_goods_value')->where(['id'=>$id])->find();
            }
            return view('',['id'=>$id,'data'=>$data]);
        }
    }

    //删除货物属性信息
    public function del_cargo(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        if($id>0){
            $res = Db::name('centralize_goods_value')->where(['id'=>$id])->delete();
            if($res){
                return json(['code'=>1,'msg'=>'删除货物属性成功！']);
            }
        }
    }
}