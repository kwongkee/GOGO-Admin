<?php
namespace app\admin\controller;

//use think\Controller;
use app\admin\controller;
use think\Request;
use think\Db;

class Officialwebsite extends Auth
{
    //资源管理-任务管理start
    public function crawl_task_list(Request $request){
        $dat = input();
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if ($request->isAjax()) {
            $count = Db::name('centralize_resource_list')->count();
            $rows = DB::name('centralize_resource_list')
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($rows as $k=>$v){
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
//            dd(date('Y-m'));
            return view('');
        }
    }

    public function save_task(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        if($request->isAjax()){
            if($id>0){
                $res = Db::name('centralize_resource_list')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'crawl_method'=>$dat['crawl_method'],
                    'crawl_cycel'=>$dat['crawl_method']==1?$dat['crawl_cycel']:0,
                    'every_hour'=>$dat['crawl_cycel']==1?$dat['every_hour']:'',
                    'appoint_day'=>$dat['crawl_cycel']==2?$dat['appoint_day']:'',
                    'every_day'=>$dat['crawl_cycel']==2?$dat['every_day']:'',
                    'week'=>$dat['crawl_cycel']==3?$dat['week']:'',
                    'every_week'=>$dat['crawl_cycel']==3?$dat['every_week']:'',
                    'month'=>$dat['crawl_cycel']==4?$dat['month']:'',
                    'every_month'=>$dat['crawl_cycel']==4?$dat['every_month']:'',
                    'every_time'=>$dat['crawl_method']==2?$dat['every_time']:'',
                ]);
            }else{
                $res = Db::name('centralize_resource_list')->insert([
                    'name'=>trim($dat['name']),
                    'crawl_method'=>$dat['crawl_method'],
                    'crawl_cycel'=>$dat['crawl_method']==1?$dat['crawl_cycel']:0,
                    'every_hour'=>$dat['crawl_cycel']==1?$dat['every_hour']:'',
                    'appoint_day'=>$dat['crawl_cycel']==2?$dat['appoint_day']:'',
                    'every_day'=>$dat['crawl_cycel']==2?$dat['every_day']:'',
                    'week'=>$dat['crawl_cycel']==3?$dat['week']:'',
                    'every_week'=>$dat['crawl_cycel']==3?$dat['every_week']:'',
                    'month'=>$dat['crawl_cycel']==4?$dat['month']:'',
                    'every_month'=>$dat['crawl_cycel']==4?$dat['every_month']:'',
                    'every_time'=>$dat['crawl_method']==2?$dat['every_time']:'',
                    'createtime'=>time()
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = ['name'=>'','crawl_method'=>'','crawl_cycel'=>'','every_hour'=>'','appoint_day'=>'','every_day'=>'','week'=>'','every_week'=>'','month'=>'','every_month'=>'','every_time'=>'','crawl_time'=>''];
            if($id>0){
                $data = Db::name('centralize_resource_list')->where(['id'=>$id])->find();
            }
            return view('',compact('data','id'));
        }
    }

    public function del_task(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $ishave = Db::name('centralize_crawl_list')->where(['pid'=>$id])->find();
        if(!empty($ishave)){
            return json(['code'=>-1,'msg'=>'删除失败，请先删除该任务下的数据源！']);
        }
        $res = Db::name('centralize_resource_list')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
    //资源管理-任务管理end

    //网站内容start
    public function crawl_content_list(Request $request)
    {
        $dat = input();
        $pid = intval($dat['pid']);
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if ($request->isAjax()) {
            if($dat['type']==1){
                $count = Db::name('centralize_crawl_list')->where(['pid'=>$pid])->count();
                $rows = DB::name('centralize_crawl_list')
                    ->where(['pid'=>$pid])
                    ->order($order)
                    ->limit($limit)
                    ->select();
            }elseif($dat['type']==2){
                $count = Db::name('centralize_website_diycontent')->count();
                $rows = DB::name('centralize_website_diycontent')
                    ->order($order)
                    ->limit($limit)
                    ->select();
                foreach ($rows as $k=>$v){
                    if($v['type']==1){
                        $rows[$k]['type'] = '菜单内容';
                    }elseif($v['type']==2){
                        $rows[$k]['type'] = '内页内容';
                    }
                }
            }

            foreach ($rows as $k=>$v){
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('pid'));
        }
    }
    
    public function add_crawl_content(Request $request){
        $dat = input();
        $pid = intval($dat['pid']);
        if($request->isAjax()){
            $res = Db::name('centralize_crawl_list')->insert([
                'pid'=>$dat['pid'],
                'name'=>trim($dat['name']),
                'link'=>trim($dat['link']),
                'createtime'=>time()
            ]);
            
            if($res){
                return json(['code'=>0,'msg'=>'新增成功']);
            }
        }else{
            return view('',compact('pid'));
        }
    }

    public function add_plain(Request $request){
        $dat = input();
        $pid = intval($dat['pid']);
        if ($request->isAjax()) {
            $res = Db::name('centralize_crawl_plain')->insert(['pid'=>$pid,'name'=>trim($dat['name']),'createtime'=>time()]);
            if($res){
                return json(['code'=>0,'msg'=>'新增方案成功']);
            }
        }else{
            return view('',compact('pid'));
        }
    }

    public function del_plain(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        $res = Db::name('centralize_crawl_plain')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除方案成功']);
        }
    }
    
    public function add_crawl_plain(Request $request){
        $dat = input();
        $pid = intval($dat['pid']);
        if ($request->isAjax()) {
            $count = Db::name('centralize_crawl_plain')->where(['pid'=>$pid])->count();
            $rows = DB::name('centralize_crawl_plain')->where(['pid'=>$pid])->order('id desc')->select();
//            foreach($rows as $k=>$v){
//
//            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('pid'));
        }
    }

    public function label_list(Request $request){
        $dat = input();
        $plain_id = intval($dat['plain_id']);//方案id
        $pid = intval($dat['pid']);//网址id
        if ($request->isAjax()) {
            $count = Db::name('centralize_crawl_label')->where(['pid'=>$pid,'plain_id'=>$plain_id])->count();
            $rows = DB::name('centralize_crawl_label')->where(['pid'=>$pid,'plain_id'=>$plain_id])->order('displayorder asc')->select();
            foreach($rows as $k=>$v){
                if($v['label_type']!=3){
                    if($v['label_type']==1){
                        $rows[$k]['label_name'] = $v['label_name'].' id="'.$v['type_name'].'"';
                    }else{
                        $rows[$k]['label_name'] = $v['label_name'].' class="'.$v['type_name'].'"';
                    }
                }

                if($v['label_attr']==1){
                    $rows[$k]['label_attr'] = 'href';
                }elseif($v['label_attr']==2){
                    $rows[$k]['label_attr'] = 'text';
                }elseif($v['label_attr']==3){
                    $rows[$k]['label_attr'] = 'src';
                }elseif($v['label_attr']==4){
                    $rows[$k]['label_attr'] = '无';
                }

                if($v['is_continue']==1){
                    $rows[$k]['is_continue'] = '是';
                }elseif($v['is_continue']==2 || empty($v['is_continue'])){
                    $rows[$k]['is_continue'] = '否';
                }
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('pid','plain_id'));
        }
    }

    public function add_label(Request $request){
        $dat = input();
        $id = $dat['id'];//网址id
        $plain_id = $dat['plain_id'];//方案id
        if ($request->isAjax()) {
            $res = Db::name('centralize_crawl_label')->insert([
                'pid'=>$id,
                'plain_id'=>$plain_id,
                'label_name'=>trim($dat['label_name']),
                'label_type'=>intval($dat['label_type']),
                'type_name'=>trim($dat['type_name']),
                'label_attr'=>intval($dat['label_attr']),
                'is_continue'=>intval($dat['label_attr'])==1?intval($dat['is_continue']):0,
            ]);
            if($res){
                return json(['code'=>0,'msg'=>'新增成功']);
            }
        }else{
            return view('',compact('id','plain_id'));
        }
    }

    public function del_label(Request $request){
        $dat = input();

        $res = Db::name('centralize_crawl_label')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    public function crawl_plain(Request $request){
        $dat = input();
        $pid = intval($dat['pid']);
        if ($request->isAjax()) {
            $res = Db::name('centralize_crawl_list')->where(['id'=>$pid])->update([
                'crawl_method'=>$dat['crawl_method'],
                'crawl_cycel'=>$dat['crawl_method']==1?$dat['crawl_cycel']:0,
                'every_hour'=>$dat['crawl_cycel']==1?$dat['every_hour']:'',
                'appoint_day'=>$dat['crawl_cycel']==2?$dat['appoint_day']:'',
                'every_day'=>$dat['crawl_cycel']==2?$dat['every_day']:'',
                'week'=>$dat['crawl_cycel']==3?$dat['week']:'',
                'every_week'=>$dat['crawl_cycel']==3?$dat['every_week']:'',
                'month'=>$dat['crawl_cycel']==4?$dat['month']:'',
                'every_month'=>$dat['crawl_cycel']==4?$dat['every_month']:'',
                'every_time'=>$dat['crawl_method']==2?$dat['every_time']:'',
            ]);
            if($res){
                return json(['code'=>0,'msg'=>'配置成功']);
            }
        }else{
            $data = Db::name('centralize_crawl_list')->where(['id'=>$pid])->find();
            return view('',compact('pid','data'));
        }
    }

    #更改爬取顺序
    public function crawl_order(Request $request){
        $dat = input();

        $res = Db::name('centralize_crawl_label')->where(['id'=>$dat['id']])->update(['displayorder'=>$dat['val']]);
        if($res){
            return json(['code'=>0,'msg'=>'修改成功']);
        }
    }

    #网页内容管理
    public function crawl_content(Request $request){
        $dat = input();
        $pid = intval($dat['pid']);
        if($request->isAjax()){
            $limit = input('offset').','.input('limit');

            $total = DB::name('centralize_pfc_menu_list')->where(['py_id'=>$pid])->count();
            if(empty($total)){
                #1、查询详情页，获取名称，不重复
            }else{

            }
            $data = DB::name('centralize_pfc_menu_list')
                ->where(['py_id'=>$pid])
                ->limit($limit)
                ->select();
            return json(['message' => "", 'status' => 0, 'total' => $total,'rows' => $data]);
        }else{
            return view('',compact('pid'));
        }
    }

    //添加入库内容
    public function add_content(Request $request){
        $dat = input();
        $pid = intval($dat['pid']);
        $id = isset($dat['id'])?intval($dat['id']):0;
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
//                    'inner_banner'=>$dat['inner_banner'][0],
                    'content'=>json_encode($content,true),
                ]);
            }else{
                #新增
                $res = Db::name('centralize_content_template')->insert([
                    'title'=>trim($dat['title']),
                    'pid'=>$pid,
                    'description'=>trim($dat['description']),
//                    'inner_banner'=>$dat['inner_banner'][0],
                    'content'=>json_encode($content,true),
                    'createtime'=>time()
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $data = ['title'=>'','description'=>'','inner_banner'=>'','content'=>''];
            if($id>0){
                $data = Db::name('centralize_content_template')->where(['id'=>$id])->find();
                $data['content'] = json_decode($data['content'],true);
            }
            return view('',compact('pid','id','data'));
        }
    }

    public function add_diycontent(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        if($request->isAjax()){
            $content = [];
            foreach($dat['content_title'] as $k=>$v){
                $content[$k]['title'] = trim($v);
                $content[$k]['title_content'] = $dat['cont'][$k];
            }

            if($id>0){
                #修改
                $res = Db::name('centralize_website_diycontent')->where(['id'=>$id])->update([
                    'title'=>trim($dat['title']),
                    'type'=>$dat['type'],
//                    'description'=>trim($dat['description']),
                    'inner_banner'=>$dat['inner_banner'][0],
                    'content'=>json_encode($content,true),

                ]);
            }else{
                #新增
                $res = Db::name('centralize_website_diycontent')->insert([
                    'title'=>trim($dat['title']),
                    'type'=>$dat['type'],
//                    'description'=>trim($dat['description']),
                    'inner_banner'=>$dat['inner_banner'][0],
                    'content'=>json_encode($content,true),
                    'createtime'=>time()
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $data = ['title'=>'','description'=>'','type'=>'','inner_banner'=>'','content'=>''];
            if($id>0){
                $data = Db::name('centralize_website_diycontent')->where(['id'=>$id])->find();
                $data['content'] = json_decode($data['content'],true);
            }
            return view('',compact('id','data'));
        }
    }

    public function del_diycontent(Request $request){
        $dat = input();

        $res = Db::name('centralize_website_diycontent')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    public function crawl_children_content(Request $request){
        $dat = input();
        $pid = intval($dat['pid']);
        if($request->isAjax()){
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            if(intval($dat['type'])==0){
                $total = DB::name('centralize_pfc_list')->where(['pid'=>$pid])->count();
                $data = DB::name('centralize_pfc_list')
                    ->where(['pid'=>$pid])
                    ->order($order)
                    ->limit($limit)
                    ->select();
            }elseif(intval($dat['type'])==1){
                $total = DB::name('centralize_content_template')->where(['pid'=>$pid])->count();
                $data = DB::name('centralize_content_template')
                    ->where(['pid'=>$pid])
                    ->order($order)
                    ->limit($limit)
                    ->select();
            }

            foreach ($data as &$item) {
                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
                if(!empty($item['updatetime'])){
                    $item['updatetime'] = date('Y-m-d H:i',$item['updatetime']);
                }
                if(!empty($item['shelftime'])){
                    $item['shelftime'] = date('Y-m-d H:i',$item['shelftime']);
                }
                if(intval($dat['type'])==0){
                    if(empty($item['status'])){
                        $item['statusname'] = '无更新';
                    }elseif($item['status']==1){
                        $item['statusname'] = '有更新';
                    }
                }
            }

            return json(['message' => "", 'status' => 0, 'total' => $total,'rows' => $data]);
        }else{
            return view('',compact('pid'));
        }
    }

    public function contrast_pfc_detail(Request $request){
        $dat = input();
        $ids = explode(',',ltrim($dat['ids'],','));
        $html = [];
        foreach($ids as $k=>$v){
            $html[$k] = Db::name('centralize_pfc_list')->where(['id'=>$v])->find();
            $html[$k]['content'] = json_decode($html[$k]['content'],true);
        }

        $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/htmldiff';
        require_once($dir."/html_diff.php");

        $con = html_diff($html[1]['content'],$html[0]['content'],true);
        return view('',compact('con'));
    }

    public function fake_shelf_diylist(Request $request){
        $dat = input();
        if($request->isAjax()) {
            $limit = input('offset') . ',' . input('limit');

            $total = DB::name('centralize_diycontent_shelf')->count();
            $data = DB::name('centralize_diycontent_shelf')
                ->order('id desc')
                ->limit($limit)
                ->select();
            foreach($data as $k=>$v){
                $data[$k]['website_id'] = Db::name('centralize_gather_website_list')->where(['id'=>$v['website_id']])->field(['title'])->find()['title'];
                $module = '';
                if(!empty($v['module_1'])){
                    $module = Db::name('centralize_website_menu')->where(['id'=>$v['module_1']])->field(['title'])->find()['title'].'->';

                }
                if(!empty($v['module_2'])){
                    $module .= Db::name('centralize_website_menu')->where(['id'=>$v['module_2']])->field(['title'])->find()['title'].'->';
                }
                if(!empty($v['module_3'])){
                    $module .= Db::name('centralize_website_menu')->where(['id'=>$v['module_3']])->field(['title'])->find()['title'].'->';
                }
                if(!empty($v['module_4'])){
                    $module .= Db::name('centralize_website_menu')->where(['id'=>$v['module_4']])->field(['title'])->find()['title'];
                }

                $data[$k]['module_name'] = rtrim($module,'->');
                $data[$k]['title'] = Db::name('centralize_website_diycontent')->where(['id'=>$v['id']])->field(['title'])->find()['title'];
                $data[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
                if($v['shelftime']){
                    $data[$k]['shelftime'] = date('Y-m-d H:i',$v['shelftime']);
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'rows' => $data]);
        }else{
            return view('');
        }
    }

    public function fake_shelf_diydetail(Request $request){
        $dat = input();
        if($request->isAjax()){
            $replace_content = [];
            foreach($dat['replace'] as $k=>$v){
                $replace_content[$k] = ['replace'=>$v,'renew'=>$dat['renew'][$k]];
            }

            $res = Db::name('centralize_diycontent_shelf')->insert([
                'website_id'=>$dat['website_id'],
                'module_1'=>isset($dat['module_1'])?$dat['module_1']:'',
                'module_2'=>isset($dat['module_2'])?$dat['module_2']:'',
                'module_3'=>isset($dat['module_3'])?$dat['module_3']:'',
                'module_4'=>isset($dat['module_4'])?$dat['module_4']:'',
                'content_id'=>$dat['content_id'],
                'replace_content'=>json_encode($replace_content,true),
                'createtime'=>time(),
            ]);
            if($res){
                return json(['code'=>0,'msg'=>'拟上架成功']);
            }
        }else{
            $data = DB::name('centralize_website_diycontent')->order('id desc')->select();
            $website = Db::name('centralize_gather_website_list')->select();
            return view('',compact('pid','data','website'));
        }
    }

    public function batch_launch_diy(Request $request){
        $dat = input();

        if($request->isAjax()){
            $list = Db::name('centralize_diycontent_shelf')->where(['is_shelf'=>0])->select();
            if(empty($list)){
                return json(['code'=>-1,'msg'=>'暂无信息可上架']);
            }

            foreach($list as $kk=>$vv){
                $replace = json_decode($vv['replace_content'],true);
                $temp = Db::name('centralize_website_diycontent')->where(['id'=>$vv['content_id']])->find();

                //开始替换
                if(!empty($replace)){
                    $temp['content'] = json_decode($temp['content'],true);
                    foreach($replace as $k=>$v){
                        foreach($temp['content'] as $k2=>$v2){
                            $temp['content'][$k2]['title'] = str_replace($v['replace'],$v['renew'],$v2['title']);
                            $temp['content'][$k2]['title_content'] = str_replace($v['replace'],$v['renew'],$v2['title_content']);
                        }
                    }
                    $temp['content'] = json_encode($temp['content'],true);
                }

                //开始上架
                if(!empty($vv['module_4'])){
                    Db::name('centralize_website_menu')->where(['id'=>$vv['module_4']])->update([
                        'title'=>$temp['title'],
                        'description'=>$temp['description'],
                        'inner_banner'=>$temp['inner_banner'],
                        'content'=>$temp['content'],
                    ]);
                }
                else{
                    if(!empty($vv['module_3'])){
                        Db::name('centralize_website_menu')->where(['id'=>$vv['module_3']])->update([
                            'title'=>$temp['title'],
                            'description'=>$temp['description'],
                            'inner_banner'=>$temp['inner_banner'],
                            'content'=>$temp['content'],
                        ]);
                    }else{
                        if(!empty($vv['module_2'])){
                            Db::name('centralize_website_menu')->where(['id'=>$vv['module_2']])->update([
                                'title'=>$temp['title'],
                                'description'=>$temp['description'],
                                'inner_banner'=>$temp['inner_banner'],
                                'content'=>$temp['content'],
                            ]);
                        }else{
                            Db::name('centralize_website_menu')->where(['id'=>$vv['module_1']])->update([
                                'title'=>$temp['title'],
                                'description'=>$temp['description'],
                                'inner_banner'=>$temp['inner_banner'],
                                'content'=>$temp['content'],
                            ]);
                        }
                    }
                }

                //修改上架时间
                Db::name('centralize_diycontent_shelf')->where(['id'=>$vv['id']])->update(['is_shelf'=>1,'shelftime'=>time()]);
            }

            return json(['code' => 0, 'msg' => '上架成功！']);
        }
    }

    public function fake_shelf_list(Request $request){
        $dat = input();
        $pid = intval($dat['pid']);
        if($request->isAjax()){
            $limit = input('offset').','.input('limit');

            $total = DB::name('centralize_content_shelf')->where(['pid'=>$pid])->count();
            $data = DB::name('centralize_content_shelf')
                ->where(['pid'=>$pid])
                ->order('id desc')
                ->limit($limit)
                ->select();

            foreach($data as $k=>$v){
                $data[$k]['website_id'] = Db::name('centralize_gather_website_list')->where(['id'=>$v['website_id']])->field(['title'])->find()['title'];
                $module = '';
                if(!empty($v['module_1'])){
                    $module = Db::name('centralize_website_menu')->where(['id'=>$v['module_1']])->field(['title'])->find()['title'].'->';

                }
                if(!empty($v['module_2'])){
                    $module .= Db::name('centralize_website_menu')->where(['id'=>$v['module_2']])->field(['title'])->find()['title'].'->';
                }
                if(!empty($v['module_3'])){
                    $module .= Db::name('centralize_website_menu')->where(['id'=>$v['module_3']])->field(['title'])->find()['title'].'->';
                }
                if(!empty($v['module_4'])){
                    $module .= Db::name('centralize_website_menu')->where(['id'=>$v['module_4']])->field(['title'])->find()['title'];
                }

                $data[$k]['module_name'] = rtrim($module,'->');
                $data[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
                if($v['shelftime']){
                    $data[$k]['shelftime'] = date('Y-m-d H:i',$v['shelftime']);
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'rows' => $data]);
        }else{
            return view('',compact('pid'));
        }
    }

    public function fake_shelf_detail(Request $request){
        $dat = input();
        $pid = intval($dat['pid']);
        if($request->isAjax()){
            $replace_content = [];
            foreach($dat['replace'] as $k=>$v){
                $replace_content[$k] = ['replace'=>$v,'renew'=>$dat['renew'][$k]];
            }

            $res = Db::name('centralize_content_shelf')->insert([
                'pid'=>$pid,
                'website_id'=>$dat['website_id'],
                'module_1'=>isset($dat['module_1'])?$dat['module_1']:'',
                'module_2'=>isset($dat['module_2'])?$dat['module_2']:'',
                'module_3'=>isset($dat['module_3'])?$dat['module_3']:'',
                'module_4'=>isset($dat['module_4'])?$dat['module_4']:'',
                'replace_content'=>json_encode($replace_content,true),
                'content_id'=>$dat['content_id'],
                'createtime'=>time(),
            ]);
            if($res){
                return json(['code'=>0,'msg'=>'拟上架成功']);
            }
        }else{
            $data = DB::name('centralize_content_template')->where(['pid'=>$pid])->order('id desc')->select();
            $website = Db::name('centralize_gather_website_list')->select();
            return view('',compact('pid','data','website'));
        }
    }

    //批量上架
    public function batch_launch(Request $request){
        $dat = input();

        if($request->isAjax()){
            $list = Db::name('centralize_content_shelf')->where(['pid'=>$dat['pid'],'is_shelf'=>0])->select();
            if(empty($list)){
                return json(['code'=>-1,'msg'=>'暂无信息可上架']);
            }

            foreach($list as $kk=>$vv){
                $replace = json_decode($vv['replace_content'],true);
                $temp = Db::name('centralize_content_template')->where(['id'=>$vv['content_id']])->find();

                //开始替换
                if(!empty($replace)){
                    $temp['content'] = json_decode($temp['content'],true);
                    foreach($replace as $k=>$v){
                        foreach($temp['content'] as $k2=>$v2){
                            $temp['content'][$k2]['title'] = str_replace($v['replace'],$v['renew'],$v2['title']);
                            $temp['content'][$k2]['title_content'] = str_replace($v['replace'],$v['renew'],$v2['title_content']);
                        }
                    }
                    $temp['content'] = json_encode($temp['content'],true);
                }

                //开始上架
                if(!empty($vv['module_4'])){
                    Db::name('centralize_website_menu')->where(['id'=>$vv['module_4']])->update([
                        'title'=>$temp['title'],
                        'description'=>$temp['description'],
                        'inner_banner'=>$temp['inner_banner'],
                        'content'=>$temp['content'],
                    ]);
                }
                else{
                    if(!empty($vv['module_3'])){
                        Db::name('centralize_website_menu')->where(['id'=>$vv['module_3']])->update([
                            'title'=>$temp['title'],
                            'description'=>$temp['description'],
                            'inner_banner'=>$temp['inner_banner'],
                            'content'=>$temp['content'],
                        ]);
                    }else{
                        if(!empty($vv['module_2'])){
                            Db::name('centralize_website_menu')->where(['id'=>$vv['module_2']])->update([
                                'title'=>$temp['title'],
                                'description'=>$temp['description'],
                                'inner_banner'=>$temp['inner_banner'],
                                'content'=>$temp['content'],
                            ]);
                        }else{
                            Db::name('centralize_website_menu')->where(['id'=>$vv['module_1']])->update([
                                'title'=>$temp['title'],
                                'description'=>$temp['description'],
                                'inner_banner'=>$temp['inner_banner'],
                                'content'=>$temp['content'],
                            ]);
                        }
                    }
                }

                //修改上架时间
                Db::name('centralize_content_shelf')->where(['id'=>$vv['id']])->update(['is_shelf'=>1,'shelftime'=>time()]);
            }

            return json(['code' => 0, 'msg' => '上架成功！']);
        }
    }

    public function get_module(Request $request){
        $dat = input();

        $data = Db::name('centralize_website_menu')->where(['gather_id'=>$dat['wid'],'pid'=>0])->select();
        return json(['code'=>0,'data'=>$data]);
    }

    public function get_child_module(Request $request){
        $dat = input();

        $data = Db::name('centralize_website_menu')->where(['pid'=>intval($dat['pid'])])->select();
        return json(['code'=>0,'data'=>$data]);
    }

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

    #爬取内容详情页
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

    public function del_pfc_detail(Request $request){
        $dat = input();

        if($dat['type']==1){
            $res = Db::name('centralize_pfc_list')->where(['id'=>$dat['id']])->delete();
        }elseif($dat['type']==2){
            $res = Db::name('centralize_content_template')->where(['id'=>$dat['id']])->delete();
        }

        if($res){
            return json(['msg'=>'删除成功','code'=>0]);
        }
    }
    //网站内容end

    //网站管理start
    public function website_manage_list(Request $request){
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
                ]);
            }else{
                $res = Db::name('centralize_gather_website_list')->insert([
                    'title'=>trim($dat['title']),
                    'keywords'=>trim($dat['keywords']),
                    'description'=>trim($dat['description']),
                    'domain_name'=>trim($dat['domain_name']),
                    'createtime'=>time()
                ]);
            }
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }else{
            $data = ['domain_name'=>'','title'=>'','keywords'=>'','description'=>''];
            if($id>0){
                $data = Db::name('centralize_gather_website_list')->where('id',$id)->find();
            }
            return view('',compact('id','data'));
        }
    }

    public function base_setting(Request $request){
        $dat = input();
        $gather_id = intval($dat['id']);
        if($request->isAjax()){

            $res = Db::name('centralize_website_basic')->where('gather_id',$gather_id)->update([
                'mobile'=>trim($dat['mobile']),
                'email'=>trim($dat['email']),
                'address'=>trim($dat['address']),
                'Facebook'=>trim($dat['Facebook']),
                'YouTube'=>trim($dat['YouTube']),
                'Twitter'=>trim($dat['Twitter']),
                'Pinterest'=>trim($dat['Pinterest']),
                'Linkedln'=>trim($dat['Linkedln']),
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
        $_type = ['栏目', '内容', '外部链接'];
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

                    'showindex'=>isset($dat['showindex'])?intval($dat['showindex']):'',
                    'showtype'=>isset($dat['showtype'])?intval($dat['showtype']):'',
                    'word'=>isset($dat['showindex'])?intval($dat['showindex'])==1?trim($dat['word']):'':'',
                    'color'=>isset($dat['showindex'])?intval($dat['showindex'])==1?$dat['color']:'':'',
                    'imgword'=>isset($dat['imgword'])?$dat['imgword'][0]:'',

                    'inner_banner'=>$dat['type']==1?$dat['inner_banner'][0]:'',
                    'content'=>$dat['type']==1?json_encode($content,true):'',
                    'out_url'=>$dat['type']==2?trim($dat['out_url']):''
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
                    'out_url'=>$dat['type']==2?trim($dat['out_url']):''
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功！']);
            }
        }else{
            $data = ['id'=>$id,'title'=>'','status'=>0,'is_menu'=>0,'pid'=>0,'type'=>0,'content'=>'','description'=>'','inner_banner'=>'','showtype'=>0,'showindex'=>0,'word'=>'','imgword'=>'','color'=>'','out_url'=>''];
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
    public function rotate_save(Request $request){
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
            }
            return view('',compact('id','data','gather_id'));
        }
    }

    public function rotate_del(Request $request){
        $dat = input();
        if(isset($dat['id'])){
            $res = Db::name('centralize_website_rotate')->where('id',$dat['id'])->delete();
            if($res){
                return json(['code' => 0, 'msg' => '操作成功！']);
            }
        }
    }

    public function index_list(Request $request){
        $dat = input();
        $gather_id = intval($dat['id']);
        if($request->isAjax()){
            $count = Db::name('centralize_website_index')->where(['gather_id'=>$gather_id])->count();
            $rows = Db::name('centralize_website_index')->where(['gather_id'=>$gather_id])->order('displayorder asc')->select();
            foreach($rows as $k=>$v){
                if($v['nav_type']==1){
                    $rows[$k]['nav_type'] = '菜单导航';
                }elseif($v['nav_type']==2){
                    $rows[$k]['nav_type'] = '其它导航';
                }
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }
            return json(['message' => "", 'status' => 0, 'total' => $count,'page'=>1,  'rows' => $rows]);
        }else{
            return view('',compact('gather_id'));
        }
    }

    public function save_index_content(Request $request){
        $dat = input();
        $gather_id = intval($dat['id']);#网站id
        $id = isset($dat['mid'])?$dat['mid']:0;#导航id
        if($request->isAjax()){
            $menu_info = [];
            $content_info = [];

            if($dat['nav_type']==1){
                $dat['child_menu'] = array_values($dat['child_menu']);
                $dat['child_style'] = array_values($dat['child_style']);
                $dat['imgword'] = array_values($dat['imgword']);
                $dat['color'] = array_values($dat['color']);
                $dat['word'] = array_values($dat['word']);
                foreach($dat['menu_id'] as $k=>$v){
                    $menu_info[$k]['menu_id'] = $v;
                    $menu_info[$k]['description'] = trim($dat['description'][$k]);
                    $menu_info[$k]['footer_show'] = trim($dat['footer_show'][$k]);
                    foreach($dat['child_menu'][$k] as $kk=>$vv){
//                        Db::name('centralize_website_menu')->where(['id'=>$vv])->update(['description'=>isset($dat['imgword'][$k][$kk])?$dat['imgword'][$k][$kk]:'']);
                        $menu_info[$k]['child_menu'][$kk]['menu_id'] = $vv;
                        $menu_info[$k]['child_menu'][$kk]['child_style'] = $dat['child_style'][$k][$kk];
                        $menu_info[$k]['child_menu'][$kk]['imgword'] = isset($dat['imgword'][$k][$kk])?$dat['imgword'][$k][$kk]:'';
                        $menu_info[$k]['child_menu'][$kk]['color'] = isset($dat['color'][$k][$kk])?$dat['color'][$k][$kk]:'';
                        $menu_info[$k]['child_menu'][$kk]['word'] = $dat['word'][$k][$kk];
                    }
                    Db::name('centralize_website_menu')->where(['id'=>$v])->update(['description'=>trim($dat['description'][$k])]);
                }
                $menu_info = json_encode($menu_info,true);
            }elseif($dat['nav_type']==2){
                $content_info = [
                    'menu_id'=>$dat['menu_ido'],
                    'description'=>trim($dat['wordo']),
                    'child_style'=>$dat['child_styleo'],
                    'imgword'=>isset($dat['imgwordo'][0])?$dat['imgwordo'][0]:'',
                    'color'=>isset($dat['coloro'])?$dat['coloro']:'',
                ];
                $content_info = json_encode($content_info,true);
                Db::name('centralize_website_menu')->where(['id'=>$dat['menu_ido']])->update(['description'=>trim($dat['wordo'])]);
            }

            if(empty($id)){
                $res = Db::name('centralize_website_index')->insert([
                    'gather_id'=>$dat['id'],
                    'zh_name'=>trim($dat['zh_name']),
                    'en_name'=>trim($dat['en_name']),
                    'nav_type'=>$dat['nav_type'],
                    'content_type'=>$dat['nav_type']==2?$dat['content_type']:'',
                    'menu_info'=>$dat['nav_type']==1?$menu_info:'',
                    'content_info'=>$dat['nav_type']==2?$content_info:'',
                    'createtime'=>time()
                ]);
            }else{
                $res = Db::name('centralize_website_index')->where(['id'=>$id])->update([
                    'gather_id'=>$dat['id'],
                    'zh_name'=>trim($dat['zh_name']),
                    'en_name'=>trim($dat['en_name']),
                    'nav_type'=>$dat['nav_type'],
                    'content_type'=>$dat['nav_type']==2?$dat['content_type']:'',
                    'menu_info'=>$dat['nav_type']==1?$menu_info:'',
                    'content_info'=>$dat['nav_type']==2?$content_info:'',
                    'createtime'=>time()
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'添加成功']);
            }
        }else{
            $data = ['zh_name'=>'','en_name'=>'','nav_type'=>'','content_type'=>'','menu_info'=>'','content_info'=>''];
            $menu_list = Db::name('centralize_website_menu')->where(['gather_id'=>$gather_id,'status'=>0])->select();
            $menu = [];
            $list = [];

            foreach($menu_list as $k=>$v){
                $is_have = Db::name('centralize_website_menu')->where(['pid'=>$v['id'],'status'=>0])->select();
                if(!empty($is_have)){
                    $menu[] = $v; 
                }
            }

            if($id>0){
                $data = Db::name('centralize_website_index')->where(['gather_id'=>$gather_id,'id'=>$id])->find();
                if(!empty($data['menu_info'])){
                    $data['menu_info'] = json_decode($data['menu_info'],true);
                    foreach($data['menu_info'] as $k=>$v){
                        if(!empty($v['child_menu'])){
                            foreach($data['menu_info'][$k]['child_menu'] as $k2=>$v2){
                                $data['menu_info'][$k]['child_menu'][$k2]['menu_name'] = Db::name('centralize_website_menu')->where(['id'=>$v2['menu_id']])->field(['title'])->find()['title'];
                            }
                        }
                    }
                }

                if(!empty($data['content_info'])){
                    $data['content_info'] = json_decode($data['content_info'],true);

                    #获取“菜单内容”或“内页内容”
                    $list = Db::name('centralize_diycontent_shelf')
                        ->alias('a')
                        ->join('centralize_website_diycontent b','b.id=a.content_id')
                        ->where(['a.website_id'=>intval($data['gather_id']),'b.type'=>$data['content_type']])
                        ->field(['a.*'])
                        ->select();

                    foreach($list as $k=>$v){
                        if(!empty($v['module_4'])){
                            $list[$k]['title'] = Db::name('centralize_website_menu')->where(['id'=>$v['module_4']])->field(['title'])->find()['title'];
                            $list[$k]['menu_id'] = $v['module_4'];
                        }elseif(!empty($v['module_3'])){
                            $list[$k]['title'] = Db::name('centralize_website_menu')->where(['id'=>$v['module_3']])->field(['title'])->find()['title'];
                            $list[$k]['menu_id'] = $v['module_3'];
                        }elseif(!empty($v['module_2'])){
                            $list[$k]['title'] = Db::name('centralize_website_menu')->where(['id'=>$v['module_2']])->field(['title'])->find()['title'];
                            $list[$k]['menu_id'] = $v['module_2'];
                        }elseif(!empty($v['module_1'])){
                            $list[$k]['title'] = Db::name('centralize_website_menu')->where(['id'=>$v['module_1']])->field(['title'])->find()['title'];
                            $list[$k]['menu_id'] = $v['module_1'];
                        }
                    }
                }
            }
            return view('',compact('gather_id','id','menu','data','list'));
        }
    }

    public function get_child_menu(Request $request){
        $dat = input();

        $list = Db::name('centralize_website_menu')->where(['pid'=>$dat['pid'],'status'=>0])->limit(4)->select();
        
        return json(['code'=>0,'list'=>$list]);
    }

    public function get_content(Request $request){
        $dat = input();

        $list = Db::name('centralize_diycontent_shelf')
            ->alias('a')
            ->join('centralize_website_diycontent b','b.id=a.content_id')
            ->where(['a.website_id'=>intval($dat['website_id']),'b.type'=>$dat['nav_type']])
            ->field(['a.*'])
            ->select();

        foreach($list as $k=>$v){
            if(!empty($v['module_4'])){
                $list[$k]['title'] = Db::name('centralize_website_menu')->where(['id'=>$v['module_4']])->field(['title'])->find()['title'];
                $list[$k]['menu_id'] = $v['module_4'];
            }elseif(!empty($v['module_3'])){
                $list[$k]['title'] = Db::name('centralize_website_menu')->where(['id'=>$v['module_3']])->field(['title'])->find()['title'];
                $list[$k]['menu_id'] = $v['module_3'];
            }elseif(!empty($v['module_2'])){
                $list[$k]['title'] = Db::name('centralize_website_menu')->where(['id'=>$v['module_2']])->field(['title'])->find()['title'];
                $list[$k]['menu_id'] = $v['module_2'];
            }elseif(!empty($v['module_1'])){
                $list[$k]['title'] = Db::name('centralize_website_menu')->where(['id'=>$v['module_1']])->field(['title'])->find()['title'];
                $list[$k]['menu_id'] = $v['module_1'];
            }
        }

        return json(['code'=>0,'list'=>$list]);
    }

    public function index_content_order(Request $request){
        $dat = input();

        $res = Db::name('centralize_website_index')->where(['id'=>$dat['id']])->update(['displayorder'=>$dat['val']]);
        if($res){
            return json(['code'=>0,'msg'=>'修改成功']);
        }
    }

    public function del_index_content(Request $request){
        $dat = input();
        $res = Db::name('centralize_website_index')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    //网站管理end
}