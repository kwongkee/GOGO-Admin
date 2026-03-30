<?php
namespace app\centralize\controller;

use think\Controller;
use think\Db;
use think\Request;

class Website extends  Controller{
    //基础配置start
    public function base_setting(Request $request){
        $dat = input();
        $gather_id = intval($dat['id']);
        if($request->isAjax()){

            $res = Db::name('centralize_website_basic')->where('gather_id',$gather_id)->update([
                'mobile'=>trim($dat['mobile']),
                'email'=>trim($dat['email']),
                'address'=>trim($dat['address']),
                'logo'=>$dat['logo_file'][0],
                'copyright'=>$dat['editorValue'],
            ]);

            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = Db::name('centralize_website_basic')->where('gather_id',$gather_id)->find();
            return view('',compact('data','gather_id'));
        }
    }
    //基础配置end

    //菜单start
    //菜单栏列表
    public function menu_list(Request $request){
        $dat = input();
        $gather_id = intval($dat['id']);

        return view('',compact('gather_id'));
    }

    //获取角色权限列表功能
    public function get_menu(Request $request){
        $dat = input();
        $gather_id = intval($dat['gather_id']);

        $_status = ['显示', '隐藏'];
        $_type = ['栏目', '内容'];
        $list = Db::name('centralize_website_menu')->where(['gather_id'=>$gather_id])->select();
        foreach ($list as &$item) {
            $item['type'] = $_type[$item['type']];
            $item['status'] = $_status[$item['status']];
        }
        return json(['code' => 0, 'msg' => '', 'count' => count($list), 'data' => $list,'gather_id'=>$gather_id]);
    }

    //保存菜单
    public function save_menu(Request $request){
        $dat = input();
        if(isset($dat['id'])){
            $id = intval($dat['id']);
        }else{
            $id = 0;
        }

        $gather_id = intval($dat['gather_id']);//网站id

        if(isset($dat['pid'])){
            $pid = intval($dat['pid']);
        }else{
            $pid = 0;
        }

        if($request->isAjax()) {
            $content = [];
            if($dat['type']==1){
                foreach($dat['content_title'] as $k=>$v){
                    $content[$k]['title'] = trim($v);
                    $content[$k]['title_content'] = $dat['cont'][$k];
                }
            }
            
            if($id>0){
                #修改
                $res = Db::name('centralize_website_menu')->where(['id'=>$id])->update([
                    'title'=>trim($dat['title']),
                    'status'=>intval($dat['status']),
                    'type'=>intval($dat['type']),
                    'description'=>trim($dat['description']),

                    'showindex'=>intval($dat['showindex']),
                    'showtype'=>intval($dat['showtype']),
                    'word'=>intval($dat['showindex'])==1?trim($dat['word']):'',
                    'color'=>intval($dat['showindex'])==1?$dat['color']:'',
                    'imgword'=>isset($dat['imgword'])?$dat['imgword'][0]:'',

                    'inner_banner'=>$dat['type']==1?$dat['inner_banner'][0]:'',
                    'content'=>$dat['type']==1?json_encode($content,true):'',
                ]);
            }else{
                #新增
                $res = Db::name('centralize_website_menu')->insert([
                    'gather_id'=>$gather_id,
                    'title'=>trim($dat['title']),
                    'pid'=>$pid,
                    'status'=>intval($dat['status']),
                    'type'=>intval($dat['type']),
                    'description'=>trim($dat['description']),

                    'showindex'=>intval($dat['showindex']),
                    'showtype'=>intval($dat['showtype']),
                    'word'=>intval($dat['showindex'])==1?trim($dat['word']):'',
                    'color'=>intval($dat['showindex'])==1?$dat['color']:'',
                    'imgword'=>isset($dat['imgword'])?$dat['imgword'][0]:'',

                    'inner_banner'=>$dat['type']==1?$dat['inner_banner'][0]:'',
                    'content'=>$dat['type']==1?json_encode($content,true):'',
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功！']);
            }
        }else{
            $data = ['id'=>$id,'title'=>'','status'=>0,'is_menu'=>0,'pid'=>0,'type'=>0,'content'=>'','description'=>'','inner_banner'=>'','showtype'=>0,'showindex'=>0,'word'=>'','imgword'=>'','color'=>''];
            if($id>0){
                $data = Db::name('centralize_website_menu')->where(['id'=>$id])->find();
                if($data['type']==1){
                    $data['content'] = json_decode($data['content'],true);    
                }
            }
            return view('',['data'=>$data,'pid'=>$pid,'gather_id'=>$gather_id]);
        }
    }

    //删除菜单
    public function del_menu(Request $request){
        $dat = input();
        if($request->isAjax()) {
            $res = Db::name('centralize_website_menu')->where(['id'=>intval($dat['id'])])->delete();
            if($res){
                return json(['code'=>0,'msg'=>'删除成功']);
            }
        }
    }
    //菜单end

    //轮播图start
    public function rotate_list(Request $request){
        $dat = input();
        $gather_id = $dat['id'];
        if ( request()->isPost() || request()->isAjax()) {

            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;

            $total = DB::name('centralize_website_rotate')->where(['gather_id'=>$gather_id])->count();
            $data = DB::name('centralize_website_rotate')
                ->where(['gather_id'=>$gather_id])
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('',compact('gather_id'));
        }
    }

    //保存&编辑
    public function rotation_save(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $gather_id = $dat['gather_id'];
        if ( request()->isPost() || request()->isAjax()) {

            $content = [];
            foreach($dat['content_title'] as $k=>$v){
                $content[$k]['title'] = trim($v);
                $content[$k]['title_content'] = $dat['cont'][$k];
            }
            if($id>0){
                $res = Db::name('centralize_website_rotate')->where('id',$id)->update([
                    'title'=>trim($dat['title']),
                    'description'=>trim($dat['description']),
                    'banner'=>$dat['banner'][0],
                    'inner_banner'=>$dat['inner_banner'][0],
                    'content'=>json_encode($content,true),
                ]);
            }else{
                $res = Db::name('centralize_website_rotate')->insert([
                    'gather_id'=>$gather_id,
                    'title'=>trim($dat['title']),
                    'description'=>trim($dat['description']),
                    'banner'=>$dat['banner'][0],
                    'inner_banner'=>$dat['inner_banner'][0],
                    'content'=>json_encode($content,true),
                ]);
            }
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = ['title'=>'','type'=>0,'description'=>''];
            if($id>0){
                $data = Db::name('centralize_website_rotate')->where('id',$id)->find();
                $data['content'] = json_decode($data['content'],true);
//                print_r($data['content']);die;
//                foreach($data['content'] as $k=>$v){
//                    print_r($v['title'].'    ');
//                }
//                die;
            }
            return view('',compact('id','data','gather_id'));
        }
    }

    public function rotation_del(Request $request){
        $dat = input();
        if(isset($dat['id'])){
            $res = Db::name('centralize_website_rotate')->where('id',$dat['id'])->delete();
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }
    }
    //轮播图end

    //爬虫官网列表start
    public function py_website_list(Request $request){
        $dat = input();

        if ( request()->isPost() || request()->isAjax()) {
            $dat = input();
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;

            $total = Db::name('centralize_python_website_list')->count();
            $data = DB::name('centralize_python_website_list')
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach($data as $k=>&$v){
                $v['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{

            return view('',compact(''));
        }
    }

    public function save_py_website(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){
            if($id>0){
                $res = Db::name('centralize_python_website_list')->where('id',$id)->update([
                    'title'=>trim($dat['title']),
                    'url'=>trim($dat['url']),
                    'module'=>trim($dat['module']),
                ]);
            }else{
                $res = Db::name('centralize_python_website_list')->insert([
                    'title'=>trim($dat['title']),
                    'url'=>trim($dat['url']),
                    'module'=>trim($dat['module']),
                    'createtime'=>time()
                ]);
            }
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = ['title'=>'','url'=>'','module'=>''];
            if($id>0){
                $data = Db::name('centralize_python_website_list')->where('id',$id)->find();
            }
            return view('',compact('id','data'));
        }
    }

    public function del_py_website(Request $request){
        $dat = input();
        if(isset($dat['id'])){
            $res = Db::name('centralize_python_website_list')->where('id',$dat['id'])->delete();
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }
    }
    //爬虫官网列表end

    //集运官网管理start
    public function gather_website_list(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            $dat = input();
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;

            $total = Db::name('centralize_gather_website_list')->count();
            $data = DB::name('centralize_gather_website_list')
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();
            foreach($data as $k=>$v){
                $data[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{

            return view('',compact(''));
        }
    }

    public function save_gather_website(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){
            if($id>0){
                $res = Db::name('centralize_gather_website_list')->where('id',$id)->update([
                    'title'=>trim($dat['title']),
                    'keywords'=>trim($dat['keywords']),
                    'description'=>trim($dat['description']),
                    'domain_name'=>trim($dat['domain_name']),
                    'remark'=>trim($dat['remark']),
                ]);
            }else{
                $res = Db::name('centralize_gather_website_list')->insert([
                    'title'=>trim($dat['title']),
                    'keywords'=>trim($dat['keywords']),
                    'description'=>trim($dat['description']),
                    'domain_name'=>trim($dat['domain_name']),
                    'remark'=>trim($dat['remark']),
                    'createtime'=>time()
                ]);
            }
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = ['domain_name'=>'','remark'=>'','title'=>'','keywords'=>'','description'=>''];
            if($id>0){
                $data = Db::name('centralize_gather_website_list')->where('id',$id)->find();
            }
            return view('',compact('id','data'));
        }
    }

    public function del_gather_website(Request $request){
        $dat = input();
        if(isset($dat['id'])){
            $res = Db::name('centralize_gather_website_list')->where('id',$dat['id'])->delete();
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }
    }

    #我们的服务
    public function services_list(Request $request){
        $dat = input();
        $id = $dat['id'];
        if($request->isAjax()){
            $services_id = '';
            foreach($dat['menu_id'] as $k=>$v){
                $services_id .= $v.',';
            }
            $services_id = rtrim($services_id,',');
            if(empty($dat['aid'])){
                $res = Db::name('centralize_website_services')->insert([
                    'gather_id'=>$dat['id'],
                    'services_id'=>$services_id
                ]);
            }else{
                $res = Db::name('centralize_website_services')->where(['id'=>$dat['aid']])->update([
                    'gather_id'=>$dat['id'],
                    'services_id'=>$services_id
                ]);
            }
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = Db::name('centralize_website_services')->where(['gather_id'=>$id])->find();
            $menu = Db::name('centralize_website_menu')->where(['gather_id'=>$id])->select();

            $services = explode(',',$data['services_id']);
            return view('',compact('id','data','menu','services'));
        }
    }

    #关于我们
    public function aboutus_list(Request $request){
        $dat = input();
        $id = $dat['id'];
        if($request->isAjax()){
            if(empty($dat['aid'])){
                $res = Db::name('centralize_website_aboutus')->insert([
                    'gather_id'=>$dat['id'],
                    'menu_id'=>$dat['menu_id']
                ]);
            }else{
                $res = Db::name('centralize_website_aboutus')->where(['id'=>$dat['aid']])->update([
                    'gather_id'=>$dat['id'],
                    'menu_id'=>$dat['menu_id']
                ]);
            }
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = Db::name('centralize_website_aboutus')->where(['gather_id'=>$id])->find();
            $menu = Db::name('centralize_website_menu')->where(['gather_id'=>$id])->select();
            return view('',compact('id','data','menu'));
        }
    }
    //集运官网管理end

    //Pfc爬虫列表start
    public function pfc_list(Request $request){
        $dat = input();
        $gather_id = $dat['id'];

        if ( request()->isPost() || request()->isAjax()) {
            $dat = input();
            // 排序
            // $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $total = DB::name('centralize_pfc_menu_list')->where(['py_id'=>$gather_id])->count();
            $data = DB::name('centralize_pfc_menu_list')
                // ->order($order)
                ->where(['py_id'=>$gather_id])
                ->limit($limit)
                ->select();

//            foreach ($data as &$item) {
//
//            }

            return json(['message' => "", 'status' => 0, 'total' => $total,'rows' => $data]);
        }else{
            return view('',compact('gather_id'));
        }
    }
    
    public function pfc_children_list(Request $request){
        $dat = input();
        $pid = $dat['pid'];
        
        if ( request()->isPost() || request()->isAjax()) {
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $total = DB::name('centralize_pfc_list')->where(['pid'=>$pid])->count();
            $data = DB::name('centralize_pfc_list')
                ->where(['pid'=>$pid])
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {
                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
                if(!empty($item['updatetime'])){
                    $item['updatetime'] = date('Y-m-d H:i',$item['updatetime']);
                }
                if(!empty($item['shelftime'])){
                    $item['shelftime'] = date('Y-m-d H:i',$item['shelftime']);
                }
            }


            return json(['message' => "", 'status' => 0, 'total' => $total,'rows' => $data]);
        }else{
            $website_list = Db::name('centralize_gather_website_list')->select();
            $website_list = json_encode($website_list,true);

            return view('',compact('pid','website_list'));
        }
    }

    public function get_shelf_detail(Request $request){
        $dat = input();

        if ( request()->isPost() || request()->isAjax()) {
            $res = Db::name('centralize_pfc_list')->where(['id'=>$dat['id']])->find();
            $initValue = $res['shelf_website'];

            return json(['code'=>0,'data'=>$initValue]);
        }
    }

    public function pfc_detail(Request $request){
        $dat = input();

        if($request->isAjax()){
            $res = Db::name('centralize_pfc_list')->where(['id'=>$dat['id']])->update(['title'=>$dat['title'],'update_content'=>json_encode($dat['editorValue'],true),'updatetime'=>time()]);
            if($res){
                return json(['code'=>0,'msg'=>'操作成功！']);
            }
        }else{
            $data = Db::name('centralize_pfc_list')->where('id',$dat['id'])->find();
            if(!empty($data['update_content'])){
                $data['content'] = json_decode($data['update_content'],true);
            }else{
                $data['content'] = json_decode($data['content'],true);
            }
            return view('',compact('data'));
        }
    }

    #对比内容
    public function contrast_pfc_detail(Request $request){
        $dat = input();
        $ids = explode(',',ltrim($dat['ids'],','));
        $html = [];
        foreach($ids as $k=>$v){
            $html[$k] = Db::name('centralize_pfc_list')->where(['id'=>$v])->find();
            $html[$k]['content'] = json_decode($html[$k]['content'],true);
        }

        $dir = $_SERVER['DOCUMENT_ROOT'].'/foll/vendor/htmldiff';
        require_once($dir."/html_diff.php");

        $con = html_diff($html[1]['content'],$html[0]['content'],true);
        return view('',compact('con'));
    }
    //Pfc爬虫列表end

    //集运内容管理start
    public function gather_content_list(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $total = DB::name('centralize_content_template')->count();
            $data = DB::name('centralize_content_template')
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {
                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
                if(!empty($item['shelftime'])){
                    $item['shelftime'] = date('Y-m-d H:i',$item['shelftime']);
                }
            }


            return json(['message' => "", 'status' => 0, 'total' => $total,'rows' => $data]);
        }else{
            $website_list = Db::name('centralize_gather_website_list')->select();
            return view('',compact('website_list'));
        }
    }

    public function save_gather_content(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isAjax()){
            $content = [];
            foreach($dat['content_title'] as $k=>$v){
                $content[$k]['title'] = trim($v);
                $content[$k]['title_content'] = $dat['cont'][$k];
            }

            if($id>0){
                #修改
                $res = Db::name('centralize_content_template')->where(['id'=>$id])->update([
                    'title'=>trim($dat['title']),
                    'description'=>trim($dat['description']),
                    'inner_banner'=>$dat['inner_banner'][0],
                    'content'=>json_encode($content,true),
                ]);
            }else{
                #新增
                $res = Db::name('centralize_content_template')->insert([
                    'title'=>trim($dat['title']),
                    'description'=>trim($dat['description']),
                    'inner_banner'=>$dat['inner_banner'][0],
                    'content'=>json_encode($content,true),
                    'createtime'=>time()
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功！']);
            }

        }else{
            $data = ['title'=>'','description'=>'','inner_banner'=>'','content'=>''];
            if($id>0){
                $data = Db::name('centralize_content_template')->where('id',$id)->find();
                $data['content'] = json_decode($data['content'],true);
            }
            return view('',compact('id','data'));
        }
    }

    #获取网站下的菜单栏目
    public function get_module(Request $request){
        $dat = input();

        $data = Db::name('centralize_website_menu')->where(['gather_id'=>$dat['wid'],'status'=>0,'pid'=>0])->select();
        return json(['code'=>0,'data'=>$data]);
    }

    #子菜单栏目
    public function get_child_module(Request $request){
        $dat = input();

        $data = Db::name('centralize_website_menu')->where(['status'=>0,'pid'=>intval($dat['pid'])])->select();
        return json(['code'=>0,'data'=>$data]);
    }
    
    //替换指定关键字
    public function gather_module_replace(Request $request){
        $dat = input();
        $rep_id = intval($dat['template_id2']);
        $content = Db::name('centralize_content_template')->where(['id'=>$rep_id])->find();
        $content['content'] = json_decode($content['content'],true);
        foreach($content['content'] as $k=>&$v){
            foreach($dat['replace'] as $k2=>$v2){
                $v['title'] = str_replace($v2,$dat['renew'][$k2],$v['title']);
                $v['title_content'] = str_replace($v2,$dat['renew'][$k2],$v['title_content']);
            }
        }
        
        $res = Db::name('centralize_content_template')->where(['id'=>$rep_id])->update([
            'content'=>json_encode($content['content'],true)
        ]);
        if($res){
            return json(['code'=>0,'msg'=>'替换成功!']);
        }
    }

    //上架到指定官网
    public function gather_module_shelf(Request $request){
        $dat = input();

        if ( request()->isPost() || request()->isAjax()) {
            $temp = Db::name('centralize_content_template')->where(['id'=>$dat['template_id']])->find();
            Db::name('centralize_content_template')->where(['id'=>$dat['template_id']])->update(['shelftime'=>time()]);

            if(isset($dat['module_1'])){
                foreach($dat['module_1'] as $k=>$v){
                    if(isset($dat['module_4'][$k])){
                        Db::name('centralize_website_menu')->where(['id'=>$dat['module_4'][$k]])->update([
                            'title'=>$temp['title'],
                            'description'=>$temp['description'],
                            'inner_banner'=>$temp['inner_banner'],
                            'content'=>$temp['content'],
                        ]);
                    }else{
                        if(isset($dat['module_3'][$k])){
                            Db::name('centralize_website_menu')->where(['id'=>$dat['module_3'][$k]])->update([
                                'title'=>$temp['title'],
                                'description'=>$temp['description'],
                                'inner_banner'=>$temp['inner_banner'],
                                'content'=>$temp['content'],
                            ]);
                        }else{
                            if(isset($dat['module_2'][$k])){
                                Db::name('centralize_website_menu')->where(['id'=>$dat['module_2'][$k]])->update([
                                    'title'=>$temp['title'],
                                    'description'=>$temp['description'],
                                    'inner_banner'=>$temp['inner_banner'],
                                    'content'=>$temp['content'],
                                ]);
                            }else{
                                Db::name('centralize_website_menu')->where(['id'=>$v])->update([
                                    'title'=>$temp['title'],
                                    'description'=>$temp['description'],
                                    'inner_banner'=>$temp['inner_banner'],
                                    'content'=>$temp['content'],
                                ]);
                            }
                        }
                    }
                }
            }

            return json(['code' => 0, 'msg' => '上架成功！']);
        }
    }
    //集运内容管理end
}