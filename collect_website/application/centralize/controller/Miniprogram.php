<?php
/**
 * 直邮易小程序
 * 2023-12-05
 */
namespace app\centralize\controller;

use think\Controller;
use think\Db;
use think\Request;
use Excel5;
use PHPExcel_IOFactory;
use PHPExcel;

class Miniprogram extends  Controller{
    #小程序基本信息配置
    public function basic_info(Request $request){
        $dat = input();
        $id = 1;
        if(request()->isPost() || request()->isAjax()){
            $res = Db::name('miniprogram_basicinfo')->where(['id'=>$id])->update([
                'logo'=>isset($dat['logo'][0])?$dat['logo'][0]:'',
                'head_background'=>$dat['head_background']
            ]);

            return json(['code'=>0,'msg'=>'保存成功']);
        }
        else{
            $data = ['logo'=>'','head_background'=>''];

            $data = Db::name('miniprogram_basicinfo')->where(['id'=>$id])->find();

            return view('', compact('id','data'));
        }
    }

    #菜单页配置
    public function menu_list(Request $request){
        $dat = input();
        $system_id = 1;

        if ($request->isAJAX()) {
            $list = Db::name('miniprogram_navbar')->where(['system_id'=>$system_id])->order('displayorder,id asc')->select();
            foreach($list as $k=>$v){

            }
            return json(['code' => 0, 'msg' => '', 'count' => count($list), 'data' => $list]);
        }else{
            return view('',compact('system_id'));
        }
    }

    #保存菜单
    public function save_menu(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $pid = isset($dat['pid'])?intval($dat['pid']):0;

        if(request()->isPost() || request()->isAjax()){

            if($id>0){
                Db::name('miniprogram_navbar')->where(['system_id'=>1,'id'=>$id])->update([
                    'displayorder'=>intval($dat['displayorder2']),
                    'name'=>trim($dat['name']),
                    'desc'=>trim($dat['desc']),
                    'tz_type'=>intval($dat['tz_type']),
                    'menu_link'=>intval($dat['menu_link']),
                    'pictxt_link'=>intval($dat['pictxt_link']),
                    'link'=>trim($dat['link']),
                    'thumb'=>isset($dat['thumb'])?$dat['thumb'][0]:'',
                    'color_background'=>trim($dat['color_background']),
                    'color_word'=>trim($dat['color_word']),
                ]);
            }else{
                Db::name('miniprogram_navbar')->insert([
                    'system_id'=>1,
                    'pid'=>$pid,
                    'displayorder'=>intval($dat['displayorder2']),
                    'name'=>trim($dat['name']),
                    'desc'=>trim($dat['desc']),
                    'tz_type'=>intval($dat['tz_type']),
                    'menu_link'=>intval($dat['menu_link']),
                    'pictxt_link'=>intval($dat['pictxt_link']),
                    'link'=>trim($dat['link']),
                    'thumb'=>isset($dat['thumb'])?$dat['thumb'][0]:'',
                    'color_background'=>trim($dat['color_background']),
                    'color_word'=>trim($dat['color_word']),
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['displayorder'=>'','name'=>'','desc'=>'详情','thumb'=>'','tz_type'=>0,'menu_link'=>'','pictxt_link'=>'','link'=>'','color_background'=>'','color_word'=>''];

            if($id>0){
                $data = Db::name('miniprogram_navbar')->where(['id'=>$id])->find();
            }

            #购购物菜单
            $menu = Db::name('website_navbar')->where(['system_id'=>1,'company_id'=>0])->order('displayorder,id asc')->select();
            foreach($menu as $k=>$v){
                $menu[$k]['name'] = json_decode($v['name'],true)['zh'];
            }

            #图文链接
            $pictxt = Db::name('website_image_txt')->select();
            foreach($pictxt as $k=>$v){
                $pictxt[$k]['name'] = json_decode($v['name'],true)['zh'];
            }

            return view('', compact('id','data','pid','menu','pictxt'));
        }
    }

    #删除菜单
    public function del_menu(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        $ishave = Db::name('miniprogram_navbar')->where(['pid'=>$id])->find();
        if(empty($ishave)){
            Db::name('miniprogram_navbar')->where(['id'=>$id])->delete();
            return json(['code'=>0,'msg'=>'删除成功']);
        }else{
            return json(['code'=>-1,'msg'=>'下级菜单存在，删除失败']);
        }
    }

    #菜单页轮播管理（375px*300px）
    public function rotate_list(Request $request){
        $dat = input();
        $system_id = isset($dat['system_id']) ? intval($dat['system_id']) : 1;
        if ( request()->isPost() || request()->isAjax()) {

            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;

            $total = DB::name('miniprogram_rotate')->where(['system_id'=>$system_id])->count();
            $data = DB::name('miniprogram_rotate')
                ->where(['system_id'=>$system_id])
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach($data as $k=>$v){
                $data[$k]['menu_name'] = Db::name('miniprogram_navbar')->where(['system_id'=>$system_id,'id'=>$v['menu_id']])->field('name')->find()['name'];
            }

            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('',compact('system_id'));
        }
    }

    #保存菜单页轮播
    public function save_rotate(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $system_id = isset($dat['system_id'])?$dat['system_id']:1;

        if ( request()->isPost() || request()->isAjax()) {
            if($id>0){
                $res = Db::name('miniprogram_rotate')->where('id',$id)->update([
                    'title'=>trim($dat['title']),
                    'menu_id'=>$dat['menu_id'],
                    'thumb'=>$dat['thumb'][0],
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'other_pic'=>$dat['go_other']==3?trim($dat['other_pic']):'',
                    'other_msg'=>$dat['go_other']==4?trim($dat['other_msg']):'',
                    'other_keywords'=>$dat['go_other']==5?trim($dat['other_keywords']):'',
                ]);
            }else{
                $res = Db::name('miniprogram_rotate')->insert([
                    'system_id'=>$system_id,
                    'title'=>trim($dat['title']),
                    'menu_id'=>$dat['menu_id'],
                    'thumb'=>$dat['thumb'][0],
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'other_pic'=>$dat['go_other']==3?trim($dat['other_pic']):'',
                    'other_msg'=>$dat['go_other']==4?trim($dat['other_msg']):'',
                    'other_keywords'=>$dat['go_other']==5?trim($dat['other_keywords']):'',
                ]);
            }
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = ['title'=>'','type'=>0,'thumb'=>0,'go_other'=>0,'other_link'=>'','other_pic'=>'','other_msg'=>'','other_keywords'=>'','menu_id'=>''];
            if($id>0){
                $data = Db::name('miniprogram_rotate')->where('id',$id)->find();
            }

            #图文链接
            $pic_list = Db::name('website_image_txt')->select();
            foreach($pic_list as $k=>$v){
                $pic_list[$k]['name'] = json_decode($v['name'],true)['zh'];
            }

            #消息链接
            $msg_list = Db::name('website_message_manage')->select();

            #小程序菜单
            $menu_list = Db::name('miniprogram_navbar')->where(['system_id'=>$system_id,'pid'=>0])->select();

            return view('',compact('id','data','system_id','pic_list','msg_list','menu_list'));
        }
    }

    #删除菜单页轮播
    public function del_rotate(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        $res = Db::name('miniprogram_rotate')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #动态管理
    public function enterprise_news_manage(Request $request){
        $dat = input();

        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('miniprogram_enterprise_news')->order($order)->count();
            $rows = DB::name('miniprogram_enterprise_news')
                ->limit($limit)
                ->order($order)
                ->select();
            foreach($rows as $k=>&$v){
                if(!empty($v['createtime'])){
                    $v['createtime'] = date('Y-m-d H:i',$v['createtime']);
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    #保存动态
    public function save_enterprise_news(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isAjax()){
            if($id>0){
                Db::name('miniprogram_enterprise_news')->where('id',$id)->update([
                    'name'=>trim($dat['name']),
                    'type'=>intval($dat['type']),
                    'origin_link'=>$dat['type']==1?trim($dat['origin_link']):'',
                    'content'=>$dat['type']==1?json_encode($dat['content'],true):'',
                    'social_id'=>$dat['type']==2?intval($dat['social_id']):'',
                    'social_link'=>$dat['type']==2?trim($dat['social_link']):'',
                    'seo_content'=>json_encode($dat['seo_content'],true),
                    'release_date'=>trim($dat['release_date']),
                ]);
            }else{
                $res=Db::name('miniprogram_enterprise_news')->insert([
                    'name'=>trim($dat['name']),
                    'type'=>intval($dat['type']),
                    'origin_link'=>$dat['type']==1?trim($dat['origin_link']):'',
                    'content'=>$dat['type']==1?json_encode($dat['content'],true):'',
                    'createtime'=>$dat['type']==1?time():'',
                    'social_id'=>$dat['type']==2?intval($dat['social_id']):'',
                    'social_link'=>$dat['type']==2?trim($dat['social_link']):'',
                    'seo_content'=>json_encode($dat['seo_content'],true),
                    'release_date'=>trim($dat['release_date']),
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功！']);
        }else{
            $data = ['name'=>'','type'=>1,'content'=>'','origin_link'=>'','social_id'=>'','social_link'=>'','seo_content'=>['title'=>'','keywords'=>'','desc'=>''],'release_date'=>''];
            if($id>0){
                $data = Db::name('miniprogram_enterprise_news')->where('id',$id)->find();

                if(!empty($data['content'])){
                    $data['content'] = json_decode($data['content'],true);
                }
                $data['seo_content'] = json_decode($data['seo_content'],true);
            }
            $social = Db::name('website_contact')->select();
            return view('',compact('data','id','social'));
        }
    }

    #删除动态
    public function del_enterprise_news(Request $request){
        $dat = input();
        $res = Db::name('miniprogram_enterprise_news')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }

    public function message(Request $request){
        if ( request()->isPost() || request()->isAjax()) {
            $dat = input();
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;

            $total = DB::name('centralize_miniprogram_message')->count();
            $data = DB::name('centralize_miniprogram_message')
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach ($data as &$item) {
//                $item['page_name'] = Db::name('centralize_miniprogram_page')->where(['id'=>$item['page_id']])->find()['name'];
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    public function save_message(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        if ( request()->isPost() || request()->isAjax()) {
//            dd($dat);
            if($id>0){
                Db::name('centralize_miniprogram_message')->where(['id'=>$id])->update([
                    'tmp_code'=>trim($dat['tmp_code']),
                    'tmp_name'=>$dat['tmp_name'],
                ]);
            }else{
                Db::name('centralize_miniprogram_message')->insert([
                    'tmp_code'=>trim($dat['tmp_code']),
                    'tmp_name'=>$dat['tmp_name'],
                ]);
            }
            return json(['code'=>0,'msg'=>'提交成功']);
        }else{
            $data = ['tmp_code'=>'','tmp_name'=>'','navlink'=>''];
            if($id>0){
                $data = DB::name('centralize_miniprogram_message')->where(['id'=>$id])->find();
            }
            return view('',compact('data','id'));
        }
    }

    public function del_message(Request $request){
        $dat = input();
        $res = DB::name('centralize_miniprogram_message')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    public function index(Request $request){
        if ( request()->isPost() || request()->isAjax()) {
            $dat = input();
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;

            $total = DB::name('centralize_miniprogram_index')->count();
            $data = DB::name('centralize_miniprogram_index')
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach ($data as &$item) {
                $item['page_name'] = Db::name('centralize_miniprogram_menupage')->where(['id'=>$item['page_id']])->find()['name'];
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    public function save_index(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        if ( request()->isPost() || request()->isAjax()) {
//            dd($dat);
            foreach($dat['nav_type'] as $k=>$v){
                if($v==2){
                    #小程序
                    $dat['navlink'][$k] = Db::name('centralize_miniprogram_page')->where(['id'=>$dat['nav_page'][$k]])->find()['url'];
                }
            }
            if($id>0){
                Db::name('centralize_miniprogram_index')->where(['id'=>$id])->update([
                    'page_id'=>$dat['page_id'],
                    'content'=>isset($dat['content'])?json_encode($dat['content'],true):'',
                    'navlink'=>isset($dat['navlink'])?json_encode($dat['navlink'],true):'',
                    'navtype'=>isset($dat['nav_type'])?json_encode($dat['nav_type'],true):'',
                    'navpage'=>isset($dat['nav_page'])?json_encode($dat['nav_page'],true):'',
                ]);
            }else{
                Db::name('centralize_miniprogram_index')->insert([
                    'page_id'=>$dat['page_id'],
                    'content'=>isset($dat['content'])?json_encode($dat['content'],true):'',
                    'navlink'=>isset($dat['navlink'])?json_encode($dat['navlink'],true):'',
                    'navtype'=>isset($dat['nav_type'])?json_encode($dat['nav_type'],true):'',
                    'navpage'=>isset($dat['nav_page'])?json_encode($dat['nav_page'],true):'',
                    'createtime'=>time()
                ]);
            }
            return json(['code'=>0,'msg'=>'提交成功']);
        }else{
            $page = Db::name('centralize_miniprogram_menupage')->select();
            $data = ['page_id'=>'','content'=>'','navlink'=>'','navtype'=>'','navpage'=>''];
            if($id>0){
                $data = DB::name('centralize_miniprogram_index')->where(['id'=>$id])->find();
                $data['content'] = json_decode($data['content'],true);
                $data['navlink'] = json_decode($data['navlink'],true);
                $data['navtype'] = json_decode($data['navtype'],true);
                $data['navpage'] = json_decode($data['navpage'],true);
//                dd($data);
            }
            $allpage = Db::name('centralize_miniprogram_page')->where(['isshow'=>0])->select();
            return view('',compact('page','data','id','allpage'));
        }
    }

    public function del_index(Request $request){
        $dat = input();
        $res = DB::name('centralize_miniprogram_index')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    public function agreement(Request $request){
        if ( request()->isPost() || request()->isAjax()) {
            $dat = input();
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;

            $total = DB::name('centralize_miniprogram_agreement')->count();
            $data = DB::name('centralize_miniprogram_agreement')
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach ($data as &$item) {
                $item['page_id'] = Db::name('centralize_miniprogram_page')->where(['id'=>$item['page_id']])->find()['name'];
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    public function save_agreement(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        if ( request()->isPost() || request()->isAjax()) {
            $content = $dat['content'];
            if($id>0){
                $res = DB::name('centralize_miniprogram_agreement')->where(['id'=>$id])->update([
                   'page_id'=>$dat['page_id'],
                    'content'=>json_encode($content,true)
                ]);
            }else{
                $res = DB::name('centralize_miniprogram_agreement')->insert([
                    'page_id'=>$dat['page_id'],
                    'content'=>json_encode($content,true)
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $page = Db::name('centralize_miniprogram_page')->where(['isshow'=>0])->select();
            $data = ['page_id'=>'','content'=>''];
            if($id>0){
                $data = DB::name('centralize_miniprogram_agreement')->where(['id'=>$id])->find();
                $data['content'] = json_decode($data['content'],true);
            }
            return view('',compact('page','data','id'));
        }
    }

    public function del_agreement(Request $request){
        $dat = input();
        $res = DB::name('centralize_miniprogram_agreement')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    public function campaign_opera_list(Request $request){
        if ( request()->isPost() || request()->isAjax()) {
            $dat = input();
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;

            $total = DB::name('campaign_operation_tutorial')->count();
            $data = DB::name('campaign_operation_tutorial')
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            $typename = ['1'=>'分享被查看','2'=>'分享被点赞','3'=>'分享被加购','4'=>'分享被转发','5'=>'分享被评论'];
            foreach ($data as &$item) {
                $item['typename'] = $typename[$item['type']];
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('');
        }
    }

    public function save_campaign_opera(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        if ( request()->isPost() || request()->isAjax()) {
            $content = $dat['cont'];

            if($id>0){
                $res = DB::name('campaign_operation_tutorial')->where(['id'=>$id])->update([
                    'type'=>$dat['type'],
                    'content'=>json_encode($content,true)
                ]);
            }else{
                $isinserted = DB::name('campaign_operation_tutorial')->where(['type'=>$dat['type']])->find();
                if(!empty($isinserted)){
                    return json(['code'=>-1,'msg'=>'此操作任务已存在，添加失败']);
                }

                $res = DB::name('campaign_operation_tutorial')->insert([
                    'type'=>$dat['type'],
                    'content'=>json_encode($content,true)
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $typename = [['id'=>'1','name'=>'分享被查看'],['id'=>2,'name'=>'分享被点赞'],['id'=>'3','name'=>'分享被加购'],['id'=>'4','name'=>'分享被转发'],['id'=>'5','name'=>'分享被评论']];
            $data = ['type'=>'','content'=>''];
            if($id>0){
                $data = DB::name('campaign_operation_tutorial')->where(['id'=>$id])->find();
                $data['content'] = json_decode($data['content'],true);
            }
            return view('',compact('typename','data','id'));
        }
    }

    public function del_campaign_opera(Request $request){
        $dat = input();
        $res = DB::name('campaign_operation_tutorial')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
}