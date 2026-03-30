<?php

namespace app\admin\controller;

use think\Db;
use think\Request;
use Util\data\Sysdb;

class Agents extends Auth
{
    public function __construct()
    {
        $this->db = new Sysdb;
    }

    // 管理员管理    =======================================================
    public function  admin(Request $request)
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $pid = $request->param('pid') ? $request->param('pid') : 0;

            $limit = $request->param('limit');
            $page = $request->param('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }

            $limits = $page.','.$limit;
            $list = Db::name('customs_agents_admin')->where(['pid'=>$pid])->limit($limits)->select();
            foreach ($list as $k => $v) {
                $list[$k]['c_time'] = date('Y-m-d H:i:s',$v['c_time']);
                $list[$k]['g_id'] = getRole($v['g_id']);
                switch($v['status']) {
                    case 1:
                        $list[$k]['status'] = '<div class="layui-btn layui-btn-xs layui-btn-warm">待审核</div>';
                    break;
                    case 2:
                        $list[$k]['status'] = '<div class="layui-btn layui-btn-xs layui-btn-normal">审核通过</div>';
                    break;
                    default:
                        $list[$k]['status'] = '<div class="layui-btn layui-btn-xs layui-btn-danger">审核拒绝</div>';
                    break;
                }
            }
            $total = Db::name('customs_agents_admin')->count();
            return json(["code" => 0, "message" => "", "count" => $total, "data" => $list]);

        }else{
            $config = [
                'type' =>'Layui',
                'query'=>['s'=>'agents/admin/list'],
                'var_page'  =>'page',
                'newstyle'  =>true
            ];
    
            $pid = $request->param('pid') ? $request->param('pid') : 0;
            // 获取所有跟目录菜单
            $data = $this->db->table('customs_agents_admin')->where(['pid'=>$pid])->pages(6,$config);
            // 获取子菜单
            if($pid > 0) {
    
                $parent = $this->db->table('customs_agents_admin')->where(['id'=>$pid])->item();
                $data['backs_id'] = $parent['pid'];
            }
    
            return view('agents/admin/index',[
                'data'=>$data,'pid'=>$pid
            ]);
        }
    }

    // 编辑管理员
    public function aedit(Request $request)
    {
        $id = $request->param('id');
        $data = $this->db->table('customs_agents_admin')->where(['id'=>$id])->item();

        $group = $this->db->table('customs_agents_group')->lists();

        return view('agents/admin/edit',['data'=>$data,'group'=>$group]);
    }

    // 编辑操作
    public function Doedit(Request $request)
    {
        $data = $request->param();
        $id = $data['id'];
        unset($data['id']);

        if(!isset($data['status'])) {
            return ['code'=>0,'msg'=>'请选择通过或者拒绝'];
        }

        $up = $this->db->table('customs_agents_admin')->where(['id'=>$id])->update($data);
        if(!$up) {
            return ['code'=>0,'msg'=>'数据更新失败'];
        }

        $msg = '';
        // 通过审核
        if($data['status'] == 2) {
            $msg = '恭喜，您的代理商注册管理员审核通过！';
            // 发送短信给商户
        } else if($data['status'] == 3) {
            $msg = '您的代理商注册管理员审核不通过，请与相关人员联系！';
        }

        // 发送短信
        if($msg != '') {
            $send = $this->sendMsg($data['uphone'],$msg);
            if($send['status']<=0) {
                return ['code'=>0,'msg'=>'手机号码格式不正确，发送失败！'];
            }
        }

        // 发送微信通知

        return ['code'=>1,'msg'=>'数据更新成功'];

    }


    // 删除操作
    public function adel(Request $request)
    {
        $id = $request->param('id');
        $this->db->table('customs_agents_admin')->where(['id'=>$id])->delete();
        return ['code'=>1,'msg'=>'数据删除成功'];
    }



    // 发送短信消息
    private function sendMsg($phone,$msg)
    {
        // 验证手机号码格式是否正确
        if(!$this->Mobile($phone)) {
            return ['status'=>0,'msg'=>'手机号码格式不正确'];
        }

        $config=[
            'SingnName'     =>  'Gogo购购网',
            'submittime'    =>  date('Y-m-d H:i:s',time()),
            'status'        =>  $msg,
            'tel'           =>  $phone,
            'TemplateCode'  =>  'SMS_165412505',//'SMS_35030091'
        ];

        //Session::set("yzm",$code);
        sendReg($config);

        return ['status'=>1,'msg'=>'发送成功'];

    }

    // 验证手机号码
    private function Mobile($mobile)
    {
        if(!is_numeric($mobile)) {
            return false;
        }
        return preg_match('/^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$/',$mobile) ? true : false;
    }





    //菜单管理  ==========================================================
    public function menu(Request $request)
    {
        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'agents/admin/list'],
            'var_page'  =>'page',
            'newstyle'  =>true
        ];

        $pid = $request->param('pid') ? $request->param('pid') : 0;
        // 获取所有跟目录菜单
        $data = $this->db->table('customs_agents_menu')->where(['pid'=>$pid])->pages(6,$config);
        // 获取子菜单
        if($pid > 0) {

            $parent = $this->db->table('customs_agents_menu')->where(['mid'=>$pid])->item();
            $data['backs_id'] = $parent['pid'];
        }
        return view('agents/menu/index',['data'=>$data,'pid'=>$pid]);
    }

    // 菜单添加
    public function madd()
    {
        $pid  = (int)input('get.pid');
        $data = $this->db->table('customs_agents_menu')->where(['mid'=>$pid])->item();
        return view('agents/menu/add',['menu'=>$data]);
    }

    // 编辑页面
    public function medits()
    {
        $pid  = (int)input('get.mid');
        $data = $this->db->table('customs_agents_menu')->where(['mid'=>$pid])->item();
        return view('agents/menu/edit',['menu'=>$data]);
    }

    // 添加编辑
    public function medit(Request $request)
    {
        $data = $request->param();
        if(isset($data['pid']) && $data['pid'] > 0 && $data['title'] == '') {
            return ['code'=>0,'msg'=>'菜单名称不能为空'];
        }
        if(isset($data['pid']) && $data['pid'] > 0 && $data['controller'] == '') {
            return ['code'=>0,'msg'=>'控制器名称不能为空'];
        }
        if(isset($data['pid']) && $data['pid'] > 0 && $data['method'] == '') {
            return ['code'=>0,'msg'=>'方法名称不能为空'];
        }

        if(isset($data['mid']) && $data['mid'] > 0) {
            $mid = $data['mid'];
            $data['ishidden'] = isset($data['ishidden']) ? $data['ishidden'] : 0;
            unset($data['mid']);
            $ins = $this->db->table('customs_agents_menu')->where(['mid'=>$mid])->update($data);
        } else {
            $ins = $this->db->table('customs_agents_menu')->insert($data);
        }

        if(!$ins) {
            return ['code'=>0,'msg'=>'数据保存失败，请稍后重试'];
        }
        return ['code'=>1,'msg'=>'数据保存成功'];
    }


    // 删除菜单
    public function domDel(Request $request)
    {
        $mid = $request->param('mid');
        $del = $this->db->table('customs_agents_menu')->where(['mid'=>$mid])->delete();
        if(!$del) {
            return ['code'=>0,'msg'=>'数据删除失败'];
        }
        return ['code'=>1,'msg'=>'数据删除成功'];
    }



    // 角色管理 ========================================================
    //角色列表
    public function role()
    {
        $lists = $this->db->table('customs_agents_group')->lists();
        return view('agents/role/index',['lists'=>$lists]);
    }


    // 添加方法
    public function radd()
    {
        // 获取权限组的数据
        $gid = (int)input('get.gid');
        // 获取组数据
        $group = $this->db->table('customs_agents_group')->where(['gid'=>$gid])->item();
        if($group) {
            $group['rights'] = json_decode($group['rights']);
        }

        // 读取菜单列表
        $menu_list = $this->db->table('customs_agents_menu')->where(['ishidden'=>0])->cates('mid');

        $menu = $this->gettreeitems($menu_list);
        $results = [];
        foreach ($menu as $value)
        {
            $value['children'] = isset($value['children']) ? $this->fomatMenus($value['children']):false;
            $results[] = $value;
        }

        // 渲染视图
        return view('agents/role/add',['menus_list'=>$results,'group'=>$group]);
    }


    // 处理菜单数据
    private function gettreeitems($items)
    {
        // 存放数组
        $tree = [];
        foreach($items as $item) {
            // 上级菜单是否存在
            if(isset($items[$item['pid']])) {
                $items[$item['pid']]['children'][] = &$items[$item['mid']];
            } else {
                $tree[] = &$items[$item['mid']];
            }
        }
        return $tree;
    }


    // 查询子菜单
    private function fomatMenus($items,&$res = [])
    {
        foreach ($items as $item)
        {
            if(!isset($item['children'])) {
                $res[] = $item;
            } else {
                $tmp = $item['children'];
                unset($item['children']);
                $res[] = $item;
                $this->fomatMenus($tmp,$res);
            }
        }
        return $res;
    }

    // 保存角色
    public function rsave()
    {
        // 获取组id
        $gid = (int)input('post.gid');
        // 接收角色名称
        $data['title'] = trim(input('post.title'));
        // 菜单
        $menus = input('post.menu/a');
        if(!$data['title']) {
            return ['code'=>0,'msg'=>'角色名称不能为空'];
        }
        $menus && $data['rights'] = json_encode(array_keys($menus));
        // 如果有gid，则更新；否则添加
        if($gid) {
            // 写入中
            $ins = $this->db->table('customs_agents_group')->where(['gid'=>$gid])->update($data);
        } else {
            // 写入中
            $ins = $this->db->table('customs_agents_group')->insert($data);
        }
        // 执行判断
        if(!$ins) {
            return ['code'=>0,'msg'=>'角色保存失败'];
        }
        return ['code'=>1,'msg'=>'角色保存成功'];
    }


    // 删除角色
    public function doDel()
    {
        // 获取删除条件
        $gid = (int)input('post.gid');
        $del = $this->db->table('customs_agents_group')->where(['gid'=>$gid])->delete();
        if(!$del) {
            return ['code'=>0,'msg'=>'角色删除失败'];
        }
        return ['code'=>1,'msg'=>'角色删除成功'];
    }




    //配置商户  =====================================================
    public function config()
    {
        $admin = [];
        $admin = $this->db->table('customs_agents_admin')->where(['pid'=>0,'status'=>2])->lists();
        return view('agents/config/index',['admin'=>$admin]);
    }

    // 配置商户
    public function cedit(Request $request)
    {
        $id = $request->param('id');
        $admin = $this->db->table('customs_agents_admin')->where(['id'=>$id])->item();

        $rights = json_decode($admin['uid']);

        $decls = [];
        $decls = $this->db->table('decl_user')->where(['parent_id'=>0])->field('id,user_name')->lists();

        return view('agents/config/edit',['admin'=>$admin,'decls'=>$decls,'rights'=>$rights]);
    }

    // 保存商户配置
    public function csave()
    {
        $id  = input('post.id');
        $uid = input('post.menu/a');
        if(!$uid) {
            return ['code'=>0,'msg'=>'请选择需要配置的商户'];
        }
        $key = array_keys($uid);
        $menus = json_encode($key);
        // 循环数据判断费率表是否存在该用户数据，存在则不写入，否则写入新的费率
        // 条件，当前用户ID+商户ID
        foreach($key as $v) {
            $isFee = $this->db->table('customs_agents_rate')->where(['did'=>$id,'uid'=>$v])->item();
            if(!$isFee) {// 不存在数据就写入数据，否则不操作
                $data = [
                    'uid'     =>$v,
                    'did'     =>$id,
                    'verfee'  =>0.6,
                    'sbfee'   =>1.00,
                    'payfee'  =>0.006,
                    'payhfee' =>0.9,
                    'isCharge'=>1,
                    'c_time'  =>time(),
                ];
                // 写表操作
                $this->db->table('customs_agents_rate')->insert($data);
            }
        }

        // 更新数据
        $re = $this->db->table('customs_agents_admin')->where(['id'=>$id])->update(['uid'=>$menus]);
        if(!$re){
            return ['code'=>0,'msg'=>'配置商户失败'];
        }
        return ['code'=>1,'msg'=>'配置商户成功'];
    }

    //配置费率
    public function fee(Request $request)
    {
        /**
         * 获取代理商ID，查询费率表
         */
        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'agents/fee/list'],
            'var_page'  =>'page',
            'newstyle'  =>true
        ];

        $id  = $request->param('id');
        $uid = $request->param('uid');

        // 获取所有跟目录菜单
        $data = $this->db->table('customs_agents_rate')->where([
            'did'=>$id,
            'uid'=>['in',$uid]
        ])->pages(6,$config);

        return view('agents/fee/index',['data'=>$data]);
    }

    // 编辑费率
    public function fedit(Request $request)
    {
        $rid = $request->param('rid');
        $data = $this->db->table('customs_agents_rate')->where(['rid'=>$rid])->item();
        return view('agents/fee/edit',['data'=>$data]);
    }

    // 编辑操作
    public function fdedit(Request $request)
    {
        // 获取表单数据
        $data = $request->param();
        $rid  = $data['rid'];
        unset($data['rid']);
        // 更新数据
        $up = $this->db->table('customs_agents_rate')->where(['rid'=>$rid])->update($data);
        if(!$up) {
            return ['code'=>0,'msg'=>'编辑失败'];
        }
        return ['code'=>1,'msg'=>'编辑成功'];
    }

    // 删除操作
    public function fdel(Request $request)
    {
        $rid = $request->param('rid');
        $del = $this->db->table('customs_agents_rate')->where(['rid'=>$rid])->delete();
        if(!$del) {
            return ['code'=>0,'msg'=>'删除失败'];
        }
        return ['code'=>1,'msg'=>'删除成功'];
    }


    // 扣费项目
    public function cost(Request $request)
    {
        if ( request()->isPost() || request()->isAjax())
        {
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $map = array();
            $map['is_delete'] = 0;
            $list = Db::name('customs_agents_cost')->where($map)->order($order)->limit($limit)->select();
            foreach ($list as $k => $v) {
                // switch ($v['type']) {
                //     case
                // }
                $list[$k]['create_at'] = date('Y-m-d H:i:s',$v['create_at']);
                $list[$k]['manage'] = '<button type="button" onclick="edit('."'编辑','".Url('agents/config/cost_edit')."'".','."'".$v['id']."'".')" class="btn btn-primary btn-xs" style="margin-right: 10px;">编辑</button>';
                $list[$k]['manage'] .= '<button type="button" onclick="del('."'".$v['id']."'".')" class="btn btn-danger btn-xs">删除</button>';
            }
            $total = Db::name('customs_agents_cost')->where($map)->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }else {
            return view("agents/cost/list",[
                'title' => '代理商扣费项目列表',
            ]);
        }
    }

    public function cost_add(Request $request)
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            $data['create_at'] = time();
            if( Db::name('customs_agents_cost')->insert($data) )
            {
                return json(['status'=>1,'message'=>'新增成功']);
            }else{
                return json(['status'=>0,'message'=>'新增失败']);
            }
        }else{
            return view("agents/cost/cost_add",[
                'title' => '代理商扣费项目列表',
            ]);
        }
    }

    public function cost_edit(Request $request)
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            if( Db::name('customs_agents_cost')->update($data) )
            {
                return json(['status'=>1,'message'=>'修改成功']);
            }else{
                return json(['status'=>0,'message'=>'修改失败']);
            }
        }else{
            $info = Db::name('customs_agents_cost')->where('id',input('id'))->find();
            return view("agents/cost/cost_edit",[
                'title' => '代理商扣费项目列表',
                'info' => $info
            ]);
        }
    }

    public function cost_del(Request $request)
    {
        $id = input('id');
        Db::name('customs_agents_cost')->where('id',$id)->update(['is_delete' => 1]);
        return json(['status'=>1,'message'=>'删除成功']);
    }
}


?>