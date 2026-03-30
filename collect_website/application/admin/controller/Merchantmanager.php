<?php
namespace app\admin\controller;

//use think\Controller;
use think\Request;
use think\Db;
use app\admin\controller;
use think\Session;

class Merchantmanager extends Auth
{
    #商户列表
    public function index(Request $request)
    {
        $dat = input();

        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $keywords = input('search');
            $total = DB::name('website_user')->where(['merch_status'=>2])->where('realname','like','%'.$keywords.'%')->count();
            $data = DB::name('website_user')
                ->where(['merch_status'=>2])
                ->where('realname','like','%'.$keywords.'%')
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {
                $item['createtime'] = date('Y-m-d H:i', $item['createtime']);
//                if($item['role_id']>0){
//                    $item['role_id'] = Db::name('centralize_backstage_role')->where('id',$item['role_id'])->field(['name'])->find()['name'];
//                }
            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        } else {
            return view('', compact(''));
        }
    }

    #注册文件
    public function approval(Request $request){
        $dat = input();

        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $keywords = input('search');
            $total = DB::name('website_user_company')->whereRaw('country_id != 162 and company like "%'.$keywords.'%"')->count();
            $data = DB::name('website_user_company')
//                ->where(['reg_method'=>2])
//                ->where('realname','like','%'.$keywords.'%')
                ->whereRaw('country_id != 162 and company like "%'.$keywords.'%"')
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {
                $item['createtime'] = date('Y-m-d H:i', $item['createtime']);
//                if($item['role_id']>0){
//                    $item['role_id'] = Db::name('centralize_backstage_role')->where('id',$item['role_id'])->field(['name'])->find()['name'];
//                }
                if($item['status']==-1){
                    $item['status_name'] = '待认证';
                }elseif($item['status']==0){
                    $item['status_name'] = '正常';
                }elseif($item['status']==1){
                    $item['status_name'] = '注销';
                }elseif($item['status']==-2){
                    $item['status_name'] = '拒绝关联';
                }elseif($item['status']==-3){
                    $item['status_name'] = '退回关联';
                }
            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        } else {
            return view('', compact(''));
        }
    }

    public function save(Request $request){
        $dat = input();
        if ($request->isAjax()) {
            if(intval($dat['type'])==1 && Session::get("merchant_yzm")!=trim($dat['code'])){
                return json(['code'=>-1,'msg'=>'手机验证码错误！']);
            }elseif(intval($dat['type'])==2 && Session::get("merchant_yzm")!=trim($dat['emailcode'])){
                return json(['code'=>-1,'msg'=>'邮箱验证码错误！']);
            }

            $res = Db::name('centralize_manage_person')->insertGetId([
                'name'=>'待配置',
                'agent_type'=>intval($dat['agent_type']),
                'type'=>intval($dat['type']),
                'country_code'=>intval($dat['type'])==1?trim($dat['country_code']):'',
                'tel'=>intval($dat['type'])==1?trim($dat['tel']):'',
                'email'=>intval($dat['type'])==2?trim($dat['email']):'',
            ]);
            if($res){
//                $this->createCodeInfo($res);
                return json(['code'=>0,'msg'=>'新增成功！']);
            }
        }else{
            return view('', compact(''));
        }
    }

    //创建用户代码集信息
    private function createCodeInfo($person_id){
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

    //发送验证码给聚梦
    public function sendCode(Request $request){
        $dat = input();

        $tel = trim($request->get("tel"));
        if(verifCode($tel)) {//验证用户名(手机)
            $code = mt_rand(11, 99) . mt_rand(11, 99) . mt_rand(11, 99);
            Session::set("merchant_yzm",$code);

            //发送短信start
            $post_data = [
                'spid'=>'254560',
                'password'=>'J6Dtc4HO',
                'ac'=>'1069254560',
                'mobiles'=>$tel,
                'content'=>'管理员正在配置您成为GOGO集运商户，手机验证码为：'.$code.'【GOGO】',
            ];
            $post_data = json_encode($post_data,true);
            $res = httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length:' . strlen($post_data),
                'Cache-Control: no-cache',
                'Pragma: no-cache'
            ));// 必须声明请求头);
            //发送短信end

            return json(['status'=>true,'code'=>0,'msg'=>'发送成功']);
        }else{
            return json(['code'=>-1,'msg'=>'手机格式错误']);
        }
    }

    //发送验证码给指定邮箱
    public function sendemailcode(Request $request){
        $dat = input();

        if($request->isAjax()){
            $code = mt_rand(11, 99) . mt_rand(11, 99) . mt_rand(11, 99);
            Session::set("merchant_yzm",$code);

            $res = cklein_mailAli(trim($dat['email']), '尊敬的商户', '正在为您配置商户信息', 'GOGO正在为您配置商户，邮箱验证码：'.$code);

            if($res){
                return json(['code'=>0,'msg'=>'发送成功！']);
            }
        }
    }

    public function save_info(Request $request){
        $dat = input();
        $id = intval($dat['id'])>0?$dat['id']:0;

        if($request->isAjax()){
            $res = Db::name('centralize_manage_person')->where(['id'=>$id])->update([
                'name'=>trim($dat['name']),
                'register_num'=>trim($dat['register_num']),
            ]);

            if($res){
                return json(['code'=>0,'msg'=>'配置商户信息成功！']);
            }
        }else{
            $data = ['name'=>'','register_num'=>''];
            if($id>0){
                $data = Db::name('centralize_manage_person')->where(['id'=>$id])->find();
                if($data['name']=='待配置'){
                    $data['name']='';
                }
            }
            return view('',compact('data','id'));
        }
    }

    public function save_auth(Request $request){
        $dat = input();
        $id = intval($dat['id'])>0?$dat['id']:0;
        if($request->isAjax()){
            $res = Db::name('centralize_manage_person')->where(['id'=>$id])->update([
                'role_id'=>$dat['role_id']
            ]);

            if($res){
                return json(['code'=>0,'msg'=>'配置商户权限成功！']);
            }
        }else{
            $role = Db::name('centralize_backstage_role')->select();
            return view('',compact('role','id'));
        }
    }

    public function log(Request $request){
        $data = input();
        if(isset($data['pa'])) {
            #获取昨天的起止时间
            $yesterday_timestamp = strtotime("-1 day");
            $yesterday_startDate = strtotime(date("Y-m-d 00:00:00", $yesterday_timestamp));
            $yesterday_endDate = strtotime(date("Y-m-d 23:59:59", $yesterday_timestamp));

            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count = Db::name('system_log')->whereRaw('createtime>='.$yesterday_startDate.' and createtime<='.$yesterday_endDate)->field('ip')->group('ip')->limit(1000)->count();
            #查找昨天的IP
            $ip_info = Db::name('system_log')->whereRaw('createtime>='.$yesterday_startDate.' and createtime<='.$yesterday_endDate)->field('ip,count(*) as count')->group('ip')->orderRaw('count desc')->limit($limit)->select();
            foreach ($ip_info as $k => $v) {
                $ip_info[$k]['times'] = Db::name('system_log')->where(['ip' => $v['ip']])->limit(1000)->count();
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $ip_info]);
        }else{
            $yesterday = date('Y-m-d',strtotime("-1 day"));
            return view('',compact('yesterday'));
        }
    }

    public function log_detail(Request $request){
        $data = input();
        $ip = trim($data['ip']);

        if(isset($data['pa'])) {
            #获取昨天的起止时间
            $yesterday_timestamp = strtotime("-1 day");
            $yesterday_startDate = strtotime(date("Y-m-d 00:00:00", $yesterday_timestamp));
            $yesterday_endDate = strtotime(date("Y-m-d 23:59:59", $yesterday_timestamp));

            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count = Db::name('system_log')->where('ip',$ip)->whereRaw('(createtime>='.$yesterday_startDate.' and createtime<='.$yesterday_endDate.')')->count();

            #查找昨天的IP
            $ip_info = Db::name('system_log')->where('ip',$ip)->whereRaw('(createtime>='.$yesterday_startDate.' and createtime<='.$yesterday_endDate.')')->limit($limit)->orderRaw('createtime asc')->select();
            foreach ($ip_info as $k => $v) {
                $info = explode('@@',$v['content']);
                $ip_info[$k]['user'] = $info[0];
                $ip_info[$k]['device'] = $info[2];
                $ip_info[$k]['time'] = $info[3];
//                $ip_info[$k]['url'] = '<a href="'.$info[4].'" target="_blank" class="layui-btn layui-btn-normal layui-btn-xs">打开链接</a>';
                $ip_info[$k]['url'] = $info[4];
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $ip_info]);
        }else{
            $yesterday = date('Y-m-d',strtotime("-1 day"));

            return view('',compact('yesterday','ip'));
        }
    }

    public function ip_index(Request $request){
        $data = input();
        if(isset($data['pa'])) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count = Db::name('system_sameip')->count();
            $rows = Db::name('system_sameip')->limit($limit)->orderRaw('id desc')->select();

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    public function save_ip(Request $request){
        $data = input();
        $id = intval($data['id'])>0?$data['id']:0;
        if($request->isAjax()){
            if($id>0){
                $res = Db::name('system_sameip')->where(['id'=>$id])->update([
                    'ip'=>trim($data['ip']),
                    'minutes'=>intval($data['minutes']),
                    'times'=>intval($data['times']),
                ]);
            }else{
                $res = Db::name('system_sameip')->insert([
                    'ip'=>trim($data['ip']),
                    'minutes'=>intval($data['minutes']),
                    'times'=>intval($data['times']),
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功！']);
            }
        }else{
            $data = ['ip'=>'','minutes'=>'','times'=>''];
            if($id>0){
                $data = Db::name('system_sameip')->where(['id'=>$id])->find();
            }
            return view('',compact('data','id'));
        }
    }

    public function del_ip(Request $request){
        $data = input();
        $id = intval($data['id'])>0?$data['id']:0;

        $res = Db::name('system_sameip')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }

    public function ip_same(Request $request){
        $data = input();
        $id = 1;
        if($request->isAjax()){
            $res = Db::name('system_setting')->where(['id'=>$id])->update([
               'minutes'=>intval($data['minutes']),
               'times'=>intval($data['times'])
            ]);

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = Db::name('system_setting')->where(['id'=>$id])->find();
            return view('',compact('data','id'));
        }
    }

    public function data_manage(Request $request){
        $data = input();
        $id = intval($data['id']);

        if($request->isAjax()){
            $res = Db::name('system_setting')->where(['id'=>$id])->update([
                'times'=>intval($data['times'])
            ]);

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = Db::name('system_setting')->where(['id'=>$id])->find();
            return view('',compact('data','id'));
        }
    }

    #商户关联管理
    public function connect_manage(Request $request){
        $dat = input();
        $uid = $dat['id'];

        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $keywords = input('search');
            $total = DB::name('website_user_company')->where(['user_id'=>$uid])->count();
            $data = DB::name('website_user_company')
                ->where(['user_id'=>$uid])
                ->order($order)
                ->limit($limit)
                ->select();


            foreach ($data as &$item) {
                $item['createtime'] = date('Y-m-d H:i', $item['createtime']);

                #商户账户类别
                if($item['type']==1){
                    $item['typename'] = '服务商';
                }elseif($item['type']==2){
                    $item['typename'] = '销售商';
                }

                if($item['type2']==1){
                    $item['typename'] .= '-物流服务';
                }elseif($item['type2']==2){
                    $item['typename'] .= '-仓储服务';
                }elseif($item['type2']==3){
                    $item['typename'] .= '-支付服务';
                }elseif($item['type2']==4){
                    $item['typename'] .= '-平台供货商';
                }elseif($item['type2']==5){
                    $item['typename'] .= '-供货与自营';
                }

                #商户账户状态
                if($item['status']==-3){
                    $item['statusname'] = '退回关联';
                }elseif($item['status']==-2){
                    $item['statusname'] = '拒绝关联';
                }elseif($item['status']==-1){
                    $item['statusname'] = '待认证';
                }elseif($item['status']==0){
                    $item['statusname'] = '正常';
                }elseif($item['status']==1){
                    $item['statusname'] = '已注销';
                }
            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        } else {
            return view('', compact('uid'));
        }
    }

    #审核商户
    public function audit_merchant(Request $request){
        $dat = input();
        $id = $dat['id'];

        if($request->isAjax()){
            $status = intval($dat['status']);
            $company = Db::name('website_user_company')->where(['id'=>$id])->find();
            $user = Db::name('website_user')->where(['id'=>$company['user_id']])->find();
            $status_name = '已审批已通过';
            $status_url = '';
            if($status==0){
                #允许关联企业
                Db::name('website_user_company')->where(['id'=>$id])->update(['status'=>$status,'admin_role'=>$dat['role_id']]);
                $ishave = Db::name('centralize_manage_person')->where(['company_id'=>$id])->find();
                if(empty($ishave)){
                    $pid=0;
                    if($company['role']==1){
                        #不是第一个人注册企业
                        $pcompany = Db::name('website_user_company')->where(['company'=>$company['company'],'role'=>0])->find();
                        $pid = Db::name('centralize_manage_person')->where(['company_id'=>$pcompany['id']])->find()['id'];
                    }
                    $time = time();
                    $manage_personid = Db::name('centralize_manage_person')->insertGetId([
                        'company_id'=>$id,
                        'name'=>$company['realname'],
                        'agent_type'=>1,
                        'type'=>1,
                        'tel'=>$company['mobile'],
                        'email'=>$user['email'],
                        'idcard'=>$company['idcard'],
                        'role_id'=>$dat['role_id'],
                        'status'=>1,
                        'pid'=>$pid,
                        'gogo_id'=>$company['user_id'],
                        'createtime'=>$time,
                    ]);

                    #生成当前企业商户的系统商户相关表
                    $post_data = json_encode(['realname'=>$company['realname'],'nickname'=>$user['nickname'],'phone'=>$company['mobile'],'email'=>$user['email'],'manage_personid'=>$manage_personid,'company_id'=>$company['id'],'company'=>$company['company'],'time'=>$time,'custom_id'=>$user['custom_id']],true);
                    httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=api/func/generate_merchant',$post_data,array(
                        'Content-Type: application/json; charset=utf-8',
                        'Content-Length:' . strlen($post_data),
                        'Cache-Control: no-cache',
                        'Pragma: no-cache'
                    ));// 必须声明请求头);
                }
                $status_url = 'https://rte.gogo198.cn';
            }
            elseif($status==-2){
                #拒绝关联
                Db::name('website_user_company')->where(['id'=>$id])->update(['status'=>$status]);
                $status_name = '已审批不通过';
            }
            elseif($status==-3){
                #退回关联
                Db::name('website_user_company')->where(['id'=>$id])->update(['status'=>$status,'remark'=>trim($dat['remark'])]);
                $status_name = '已审批不通过';
            }

            #通知商户
            $post = json_encode([
                'call'=>'confirmCollectionNotice',
                'first' =>'审批结果',
                'keyword1' => '审批结果',
                'keyword2' => $status_name,
                'keyword3' => date('Y-m-d H:i:s',time()),
                'remark' => '点击查看详情',
                'url' => $status_url,
                'openid' => $user['openid'],
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]);
            httpRequest('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post,'',1);

            return json(['code'=>0,'msg'=>'审批成功，已通知用户']);
        }else{
            $company = DB::name('website_user_company')->where(['id'=>$id])->find();
            #角色
            $role = Db::name('centralize_backstage_role')->select();
            return view('', compact('id','company','role'));
        }
    }
}