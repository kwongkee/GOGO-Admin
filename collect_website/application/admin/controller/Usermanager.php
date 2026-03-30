<?php
namespace app\admin\controller;

//use think\Controller;
use think\Request;
use think\Db;
use app\admin\controller;
use think\Session;

class Usermanager extends Auth
{
    public function index(Request $request)
    {
        $dat = input();

        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $total = DB::name('foll_user')->count();
            $data = DB::name('foll_user')
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {
                $item['create_time'] = date('Y-m-d H:i', $item['create_time']);
                if(!empty($item['role'])){
                    $item['rolename'] = Db::name('centralize_backstage_role')->where(['id'=>$item['role']])->field(['name'])->find()['name'];
                }
                if(empty($item['user_status'])){
                    $item['user_status'] = '禁用';
                }else{
                    $item['user_status'] = '正常';
                }
            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        } else {
            return view('');
        }
    }

    public function save(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if ($request->isAjax()) {
            if(empty(session::get('staff_yzm')) || empty(session::get('admin_yzm'))){
                return json(['code'=>-1,'msg'=>'请发送验证码！']);
            }

            if(session::get('staff_yzm')!=trim($dat['code'])){
                return json(['code'=>-1,'msg'=>'请输入正确的验证码！']);
            }

            if(session::get('admin_yzm')!=trim($dat['admin_code'])){
                return json(['code'=>-1,'msg'=>'请输入正确的管理员验证码！']);
            }

            if($id>0){
                $res = Db::name('foll_user')->where(['id'=>$id])->update([
                    'tel'=>trim($dat['tel']),
                    'username'=>trim($dat['username']),
//                    'user_status'=>intval($dat['user_status']),
                ]);
            }else{
                $res = Db::name('foll_user')->insert([
                    'tel'=>trim($dat['tel']),
                    'username'=>trim($dat['username']),
                    'user_status'=>0,
                    'create_time'=>time()
                ]);
            }

            if($res){
                Session::set("admin_yzm",'');
                Session::set("staff_yzm",'');
                return json(['code'=>0,'msg'=>'保存成功！']);
            }
        }else{
//            $role = Db::name('centralize_backstage_role')->select();
            $data = ['tel'=>'','username'=>''];
            if($data){
                $data = Db::name('foll_user')->where(['id'=>$id])->find();
            }
            return view('',compact('data','id'));
        }
    }

    public function del(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $res = Db::name('foll_user')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    //发送验证码给聚梦
    public function sendCode(Request $request){
        $dat = input();

        $tel = $request->get("tel");
        $admincode = $request->get("admincode");
        if(verifCode($tel)) {//验证用户名(手机)
            $code = mt_rand(11, 99) . mt_rand(11, 99) . mt_rand(11, 99);
            $content = '';
            if($admincode==1){
                //管理员验证码
                Session::set("admin_yzm",$code);
                $content = '管理员正在添加GOGO管理人员，手机验证码为：';
            }elseif($admincode==0){
                //员工验证码
                Session::set("staff_yzm",$code);
                $content = '管理员正在配置您成为GOGO管理人员，手机验证码为：';
            }

            //发送短信start
            $post_data = [
                'spid'=>'254560',
                'password'=>'J6Dtc4HO',
                'ac'=>'1069254560',
                'mobiles'=>$tel,
                'content'=>$content.$code.'【GOGO】',
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

    public function setting_role(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if ($request->isAjax()) {
            $res = Db::name('foll_user')->where(['id'=>$id])->update([
                'role'=>intval($dat['role'])
            ]);

            if($res){
                return json(['code'=>0,'msg'=>'配置角色成功！']);
            }
        }else{
            $role = Db::name('centralize_backstage_role')->select();
            $staff = Db::name('foll_user')->where(['id'=>$id])->find();

            return view('',compact('role','id','staff'));
        }
    }
}