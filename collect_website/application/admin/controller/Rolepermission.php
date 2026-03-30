<?php
namespace app\admin\controller;

//use think\Controller;
use think\Request;
use think\Db;
use app\admin\controller;

class Rolepermission extends Auth
{
    public function index(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $total = DB::name('centralize_backstage_role')->count();
            $data = DB::name('centralize_backstage_role')
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {
                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $total,'rows' => $data]);
        }else{
            return view('',['title'=>'角色权限']);
        }
    }

    public function save(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){
            if($id>0){
                #修改
                $res = Db::name('centralize_backstage_role')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'description'=>trim($dat['description']),
                ]);
            }else{
                #新增
                $res = Db::name('centralize_backstage_role')->insert([
                    'name'=>trim($dat['name']),
                    'description'=>trim($dat['description']),
                    'createtime'=>time()
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功！']);
            }
        }else{
            $data = ['name'=>'','description'=>''];
            if($id>0){
                $data = Db::name('centralize_backstage_role')->where(['id'=>$id])->find();
            }
            return view('',compact('data','id'));
        }
    }

    public function permission_setting(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){
            $res = Db::name('centralize_backstage_role')->where(['id'=>$id])->update([
                'auth_type'=>intval($dat['auth_type']),
                'authList'=>rtrim($dat['authList'],','),
            ]);

            if($res){
                return json(['code'=>0,'msg'=>'保存成功！']);
            }
        }else{
            $data = ['auth_type'=>1];

            if($id>0){
                $data = Db::name('centralize_backstage_role')->where(['id'=>$id])->find();
            }
            return view('',compact('data','id'));
        }
    }

    public function del(Request $request){
        $dat = input();
        $res = Db::name('centralize_backstage_role')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }
    
    #全选
    //获取角色权限列表功能
    public function get_menu(Request $request){
        $dat = input();

        if($request->isAjax()){
            $_status = ['显示', '隐藏'];
            $list = Db::name('centralize_manage_menu')->where(['status'=>0])->order('sort asc')->select();
            foreach ($list as &$item) {
                $item['status'] = $_status[$item['status']];
                if($item['auth_type']==1){
                    $item['auth_type']='总后台';
                }elseif($item['auth_type']==2){
                    $item['auth_type']='旧的商户端';
                }elseif($item['auth_type']==3){
                    $item['auth_type']='商户应用';
                }elseif($item['auth_type']==4){
                    $item['auth_type']='客服应用';
                }elseif($item['auth_type']==5){
                    $item['auth_type']='独立站应用';
                }
            }
            return json(['code' => 0, 'msg' => '', 'count' => count($list), 'data' => $list]);
        }else{
            return view('',['title'=>'系统管理']);
        }
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
                    'auth_type'=>$dat['auth_type'],
                    'url'=>trim($dat['url']),
                    'sort'=>intval($dat['sort']),
                    'status'=>intval($dat['status']),
                    'is_menu'=>intval($dat['is_menu']),
                    'up_notice'=>intval($dat['up_notice']),
                    'down_notice'=>intval($dat['down_notice']),
                    'icon_type'=>intval($dat['icon_type']),
                    'icon'=>intval($dat['icon_type'])==1?$dat['icon'][0]:'',
                ]);


                #所有下级显示/隐藏
                $list = Db::name('centralize_manage_menu')->where(['pid'=>$id])->field('id')->select();
                if(!empty($list)){
                    Db::name('centralize_manage_menu')->where(['pid'=>$id])->update(['status'=>$dat['status']]);
                    foreach($list as $k=>$v){
                        $list2 = Db::name('centralize_manage_menu')->where(['pid'=>$v['id']])->field('id')->select();
                        if(!empty($list2)){
                            Db::name('centralize_manage_menu')->where(['pid'=>$v['id']])->update(['status'=>$dat['status']]);
                            foreach($list2 as $k2=>$v2){
                                $list3 = Db::name('centralize_manage_menu')->where(['pid'=>$v2['id']])->field('id')->select();
                                if(!empty($list3)){
                                    Db::name('centralize_manage_menu')->where(['pid'=>$v2['id']])->update(['status'=>$dat['status']]);
                                    foreach($list3 as $k3=>$v3){
                                        $list4 = Db::name('centralize_manage_menu')->where(['pid'=>$v3['id']])->field('id')->select();
                                        if(!empty($list4)) {
                                            Db::name('centralize_manage_menu')->where(['pid' => $v3['id']])->update(['status' => $dat['status']]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

            }else{
                #新增
                $res = Db::name('centralize_manage_menu')->insert([
                    'title'=>trim($dat['title']),
                    'auth_type'=>$dat['auth_type'],
                    'url'=>trim($dat['url']),
                    'pid'=>$pid,
                    'sort'=>intval($dat['sort']),
                    'status'=>intval($dat['status']),
                    'is_menu'=>intval($dat['is_menu']),
                    'up_notice'=>intval($dat['up_notice']),
                    'down_notice'=>intval($dat['down_notice']),
                    'icon_type'=>intval($dat['icon_type']),
                    'icon'=>intval($dat['icon_type'])==1?$dat['icon'][0]:'',
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功！']);
            }
        }else{
            $data = ['id'=>0,'title'=>'','url'=>'','status'=>0,'sort'=>0,'level'=>0,'is_menu'=>0,'pid'=>0,'auth_type'=>'','up_notice'=>0,'down_notice'=>0,'icon_type'=>0,'icon'=>''];
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
    
    //功能菜单列表
    public function get_auth(Request $request){
        $data = input();

        $level_id = isset($data['level_id'])?intval($data['level_id']):0;

//        $levelData = Db::name('centralize_backstage_role')->where(['id'=>$level_id])->field(['authList'])->find();
        $levelData = Db::name('website_user_company')->where(['id'=>$level_id])->field(['authList'])->find();
        if($level_id)
        {
            $level_menus = ",".$levelData['authList'].",";
        }else{
            $level_menus = "";
        }

        $list = Db::name('centralize_manage_menu')->where(['status'=>0])->whereRaw('auth_type in ('.$data['auth_type'].')')->order('sort asc')->select();
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

    #网站管理
    public function website_manage(Request $request){
        $dat = input();
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if(request()->isPost() || request()->isAjax()){
            $count = Db::name('website_list')->order($order)->count();
            $rows = DB::name('website_list')
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>$v){

            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('');
        }
    }

    public function save_website(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if(request()->isPost() || request()->isAjax()){
            if($id>0){
                Db::name('website_list')->where('id',$id)->update([
                    'name'=>trim($dat['name']),
                    'link'=>trim($dat['link']),
                    'thumb'=>$dat['thumb'][0],
                ]);
            }else{
                Db::name('website_list')->insert([
                    'name'=>trim($dat['name']),
                    'link'=>trim($dat['link']),
                    'thumb'=>$dat['thumb'][0],
                ]);
            }
            return json(['code'=>0,'msg'=>'保存成功！']);
        }else{
            $data = ['name'=>'','link'=>'','thumb'=>''];
            if($id>0){
                $data = Db::name('website_list')->where(['id'=>$id])->find();
            }
            return view('',compact('data','id'));
        }
    }

    #运营管理============================================================START
    public function customer_features(Request $request){
        $dat = input();
        $type = intval($dat['type']);

        if($request->isAjax()){
            $_status = ['显示', '隐藏'];
            $list = Db::name('website_menu')->where(['type'=>$type])->order('id asc')->select();
            foreach ($list as &$item) {
                $item['status'] = $_status[$item['status']];
            }
            return json(['code' => 0, 'msg' => '', 'count' => count($list), 'data' => $list]);
        }else{
            $typename = '';
            if($type==1){
                $typename = '买家中心';
            }elseif($type==2){
                $typename = '卖家管理';
            }elseif($type==3){
                $typename = '服务商家';
            }
            return view('',['title'=>'系统管理','type'=>$type,'typename'=>$typename]);
        }
    }

    #保存功能菜单
    public function save_customer_features(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        $type = isset($dat['type'])?intval($dat['type']):0;

        if($request->isAjax()) {
            $func_type = intval($dat['func_type']);
            $url = '';
            $thumb = '';
            if($func_type==1 || $func_type==2){
                $url = trim($dat['url']);
            }
            elseif($func_type==3){
                $thumb = $dat['thumb'][0];
            }
            if($id>0){
                #修改
                $res = Db::name('website_menu')->where(['id'=>$id])->update([
                    'title'=>trim($dat['title']),
                    'type'=>$dat['type'],
                    'func_type'=>$func_type,
                    'url'=>$url,
                    'thumb'=>$thumb,
                    'status'=>intval($dat['status']),
                ]);
            }else{
                #新增
                $res = Db::name('website_menu')->insert([
                    'title'=>trim($dat['title']),
                    'type'=>$type,
                    'func_type'=>$func_type,
                    'url'=>$url,
                    'thumb'=>$thumb,
                    'pid'=>$pid,
                    'status'=>intval($dat['status']),
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功！']);
            }
        }else{
            $data = ['id'=>0,'title'=>'','url'=>'','status'=>0,'func_type'=>1,'thumb'=>'','msg'=>''];
            if($id>0){
                $data = Db::name('website_menu')->where(['id'=>$id])->find();
            }
            return view('',['data'=>$data,'pid'=>$pid,'type'=>$type]);
        }
    }

    #删除功能菜单
    public function del_customer_features(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        if($request->isAjax()) {
            $res = Db::name('website_menu')->where(['id'=>$id])->delete();
            if($res){
                Db::name('website_menu')->where(['pid'=>$id])->delete();
                return json(['code'=>0,'msg'=>'删除成功']);
            }
        }
    }
    #运营管理============================================================END
}