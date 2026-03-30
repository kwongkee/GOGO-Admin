<?php
namespace app\admin\controller;

//use think\Controller;
use app\admin\controller;
use think\Exception;
use think\Request;
use think\Db;
use Excel5;
use PHPExcel_IOFactory;
use PHPExcel;
use FPDF;

class Operaweb extends Auth
{
    public $config = [
        //数据库类型
        'type'     => 'mysql',
        //服务器地址
        'hostname' => 'rm-wz9mt4j79jrdh0p3z.mysql.rds.aliyuncs.com',
        //数据库名
        'database' => 'lrw',
        //用户名
        'username' => 'gogo198',
        //密码
        'password' => 'Gogo@198',
        //端口
        'hostport' => '3306',
        //表前缀
        'prefix'   => '',
    ];

    public $medical_config = [
        //数据库类型
        'type'     => 'mysql',
        //服务器地址
        'hostname' => 'rm-wz9mt4j79jrdh0p3z.mysql.rds.aliyuncs.com',
        //数据库名
        'database' => 'medical',
        //用户名
        'username' => 'gogo198',
        //密码
        'password' => 'Gogo@198',
        //端口
        'hostport' => '3306',
        //表前缀
        'prefix'   => '',
    ];

    //网站配置
    public function website_manage(Request $request){
        $dat = input();
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;

        if($request->isAjax()){
            $res = Db::name('website_basic')->where('id',$system_id)->update([
                'name'=>json_encode(['zh'=>trim($dat['name']['zh'])]),
                'desc'=>json_encode(['zh'=>trim($dat['desc']['zh'])]),
                'keywords'=>json_encode(['zh'=>trim($dat['keywords']['zh'])]),
                'mobile'=>trim($dat['mobile']),
                'email'=>trim($dat['email']),
                'slogo'=>$dat['slogo_file'][0],
                'logo'=>$dat['logo_file'][0],
                'inpic'=>isset($dat['inpic_file'][0])?$dat['inpic_file'][0]:'',
                'color'=>$dat['color'],
                'color_inner'=>$dat['color_inner'],
                'color_word'=>$dat['color_word'],
                'color_head'=>$dat['color_head'],
                'copyright'=>isset($dat['copyright_zh'])?json_encode(['zh'=>$dat['copyright_zh']],true):'',
            ]);

            if($res){
                return json(['code' => 0, 'msg' => '保存成功！']);
            }
        }else{
            $data = Db::name('website_basic')->where('id',$system_id)->find();
            $data['name'] = json_decode($data['name'],true);
            $data['desc'] = json_decode($data['desc'],true);
            $data['keywords'] = json_decode($data['keywords'],true);
            $data['copyright'] = json_decode($data['copyright'],true);

            return view('',compact('data','system_id'));
        }
    }

    //网站登录信息配置
    public function website_login_manage(Request $request){
        $dat = input();

        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;

        if($request->isAjax()){
            $res = Db::name('website_login_content')->where(['system_id'=>$system_id])->update([
                'content'=>json_encode($dat['content'],true),
            ]);

            if($res){
                return json(['code' => 0, 'msg' => '保存成功！']);
            }
        }
        else{
            $data = Db::name('website_login_content')->where(['system_id'=>$system_id])->find();
            $data['content'] = json_decode($data['content'],true);

            return view('',compact('data','system_id'));
        }
    }

    //图文管理
    public function content_manage(Request $request){
        $dat = input();

        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('website_image_txt')->order($order)->count();
            $rows = DB::name('website_image_txt')
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>$v){
                $rows[$k]['name'] = json_decode($v['name'],true)['zh'];
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('');
        }
    }

    //保存图文
    public function save_content(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){
            //同步菜单
            $connect_menus = explode(',',$dat['select']);
            foreach($connect_menus as $k=>$v){
                Db::name('website_navbar')->where(['id'=>$v])->update([
//                    'name'=>json_encode(['zh'=>trim($dat['name']['zh'])]),
                    'content'=>isset($dat['content_zh'])?json_encode(['zh'=>$dat['content_zh']]):'',
                    'thumb'=>isset($dat['thumb'][0])?$dat['thumb'][0]:'',
                    'avatar_location'=>$dat['format']==1?$dat['avatar_location']:$dat['avatar_location2'],
                    'format'=>$dat['format'],
                    'color'=>$dat['format']==2?$dat['color']:'',
                    'color_word'=>$dat['format']==2?json_encode(['zh'=>trim($dat['color_word_zh'])]):'',
                    'word_color'=>$dat['format']==2?$dat['word_color']:'',
                    'desc'=>json_encode(['zh'=>trim($dat['desc']['zh'])]),
                    'url'=>$dat['url'],
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'seo_type'=>$dat['seo_type'],
                    'seo_content'=>$dat['seo_type']==2?json_encode($dat['seo_content'],true):'',
                ]);
            }

            if($id>0){
                $res = Db::name('website_image_txt')->where('id',$dat['id'])->update([
                    'name'=>json_encode(['zh'=>trim($dat['name']['zh'])]),
                    'content'=>isset($dat['content_zh'])?json_encode(['zh'=>$dat['content_zh']]):'',
                    'thumb'=>isset($dat['thumb'][0])?$dat['thumb'][0]:'',
                    'avatar_location'=>$dat['avatar_location'],
                    'format'=>$dat['format'],
                    'color'=>$dat['format']==2?$dat['color']:'',
                    'color_word'=>$dat['format']==2?json_encode(['zh'=>trim($dat['color_word_zh'])]):'',
                    'word_color'=>$dat['format']==2?$dat['word_color']:'',
                    'desc'=>json_encode(['zh'=>trim($dat['desc']['zh'])]),
                    'url'=>$dat['url'],
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'seo_type'=>$dat['seo_type'],
                    'seo_content'=>$dat['seo_type']==2?json_encode($dat['seo_content'],true):'',
                    'connect_menus'=>$dat['select'],
                ]);
            }
            else{
                $res = Db::name('website_image_txt')->insert([
                    'name'=>json_encode(['zh'=>trim($dat['name']['zh'])]),
                    'content'=>isset($dat['content_zh'])?json_encode(['zh'=>$dat['content_zh']]):'',
                    'thumb'=>isset($dat['thumb'][0])?$dat['thumb'][0]:'',
                    'avatar_location'=>$dat['avatar_location'],
                    'format'=>$dat['format'],
                    'color'=>$dat['format']==2?$dat['color']:'',
                    'color_word'=>$dat['format']==2?json_encode(['zh'=>trim($dat['color_word_zh'])]):'',
                    'word_color'=>$dat['format']==2?$dat['word_color']:'',
                    'desc'=>json_encode(['zh'=>trim($dat['desc']['zh'])]),
                    'url'=>$dat['url'],
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'seo_type'=>$dat['seo_type'],
                    'seo_content'=>$dat['seo_type']==2?json_encode($dat['seo_content'],true):'',
                    'connect_menus'=>$dat['select'],
                    'createtime'=>time()
                ]);
            }

            if($res){
                return json(['code' => 0, 'msg' => '保存成功']);
            }
        }else{
            $data = ['name'=>['zh'=>'','cht'=>'','en'=>''],'content'=>['zh'=>'','cht'=>'','en'=>''],'url'=>'','type'=>'','desc'=>['zh'=>'','cht'=>'','en'=>''],'seo_type'=>1,'seo_content'=>['title'=>['zh'=>'','cht'=>'','en'=>''],'keywords'=>['zh'=>'','cht'=>'','en'=>''],'desc'=>['zh'=>'','cht'=>'','en'=>'']],'format'=>'','avatar_location'=>'','color'=>'','word_color'=>'','color_word'=>['zh'=>'','cht'=>'','en'=>''],'go_other'=>0,'other_navbar'=>'','other_link'=>'','connect_menus'=>''];
            if($id>0){
                $data = Db::name('website_image_txt')->where('id',$id)->find();
                $data['content'] = json_decode($data['content'],true);
                $data['name'] = json_decode($data['name'],true);
                $data['desc'] = json_decode($data['desc'],true);
                if($data['format']==2){
                    $data['color_word'] = json_decode($data['color_word'],true);
                }else{
                    $data['color_word'] = ['zh'=>'','cht'=>'','en'=>''];
                }

                if($data['seo_type']==2){
                    $data['seo_content'] = json_decode($data['seo_content'],true);
                }else{
                    $data['seo_content'] = ['title'=>['zh'=>'','cht'=>'','en'=>''],'keywords'=>['zh'=>'','cht'=>'','en'=>''],'desc'=>['zh'=>'','cht'=>'','en'=>'']];
                }
            }
            $list = json_encode($this->menu2(),true);
//            dd($list);
            return view('',compact('id','list','data'));
        }
    }

    //删除图文
    public function del_content(Request $request){
        $dat = input();
        $res = Db::name('website_image_txt')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }

    //瀑布流类别
    public function pcontent_manage2(Request $request){
        $dat = input();

        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('website_more_image_category')->order($order)->count();
            $rows = DB::name('website_more_image_category')
                ->limit($limit)
                ->order($order)
                ->select();

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('');
        }
    }

    //保存瀑布
    public function save_pcontent2(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){
            if($id>0){
                $res = Db::name('website_more_image_category')->where('id',$dat['id'])->update([
                    'name'=>trim($dat['name'])
                ]);
            }
            else{
                $res = Db::name('website_more_image_category')->insert([
                    'name'=>trim($dat['name'])
                ]);
            }

            if($res){
                return json(['code' => 0, 'msg' => '保存成功']);
            }
        }else{
            $data = ['name'=>''];
            if($id>0){
                $data = Db::name('website_more_image_category')->where('id',$id)->find();
            }

            return view('',compact('id','data'));
        }
    }

    //删除瀑布
    public function del_pcontent2(Request $request){
        $dat = input();
        $res = Db::name('website_more_image_category')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }

    //瀑布流图文管理
    public function content_manage2(Request $request){
        $dat = input();
        $pid = intval($dat['pid']);

        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('website_more_image_txt')->where(['pid'=>$pid])->order($order)->count();
            $rows = DB::name('website_more_image_txt')
                ->where(['pid'=>$pid])
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>$v){
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('pid'));
        }
    }
    
    //保存瀑布流
    public function save_content2(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $pid = intval($dat['pid']);

        if($request->isAjax()){
            if($id>0){
                $res = Db::name('website_more_image_txt')->where('id',$dat['id'])->update([
                    'pid'=>intval($dat['select_pid']),
                    'title'=>trim($dat['title']),
                    'subtitle'=>trim($dat['subtitle']),
                    'desc'=>trim($dat['desc']),
                    'type'=>$dat['type'],
                    'link'=>$dat['type']==1?trim($dat['link']):'',
                    'menu_id'=>$dat['type']==2?$dat['menu_id']:'',
                    'pic_list'=>json_encode($dat['pic_list'],true),
                ]);
            }
            else{
                $res = Db::name('website_more_image_txt')->insert([
                    'pid'=>intval($dat['select_pid']),
                    'title'=>trim($dat['title']),
                    'subtitle'=>trim($dat['subtitle']),
                    'desc'=>trim($dat['desc']),
                    'type'=>$dat['type'],
                    'link'=>$dat['type']==1?trim($dat['link']):'',
                    'menu_id'=>$dat['type']==2?$dat['menu_id']:'',
                    'pic_list'=>json_encode($dat['pic_list'],true),
                    'createtime'=>time()
                ]);
            }

            if($res){
                return json(['code' => 0, 'msg' => '保存成功']);
            }
        }else{
            $data = ['pid'=>$pid,'title'=>'','subtitle'=>'','desc'=>'','type'=>'','link'=>'','menu_id'=>'','pic_list'=>[]];
            if($id>0){
                $data = Db::name('website_more_image_txt')->where('id',$id)->find();
                $data['pic_list'] = json_decode($data['pic_list'],true);
            }
            $list = $this->menu2();
            $type_list = Db::name('website_more_image_category')->select();
            // dd($list);
            return view('',compact('id','list','data','pid','type_list'));
        }
    }
    
    //删除瀑布流图文
    public function del_content2(Request $request){
        $dat = input();
        $res = Db::name('website_more_image_txt')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }
    
    //联系方式管理
    public function contact_manage(Request $request){
        $dat = input();
        $system_id = intval($dat['system_id'])?$dat['system_id']:0;
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('website_contact')->where(['system_id'=>$system_id])->order($order)->count();
            $rows = DB::name('website_contact')
                ->where(['system_id'=>$system_id])
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>$v){
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('system_id'));
        }
    }
    
    //保存联系方式
    public function save_contact(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $system_id = intval($dat['system_id'])?$dat['system_id']:0;
        if($request->isAjax()){
            if($id>0){
                $res = Db::name('website_contact')->where('id',$dat['id'])->update([
                    'name'=>trim($dat['name']),
                    'ico'=>$dat['ico'][0],
                    'type'=>$dat['type'],
                    'link'=>$dat['type']==1?trim($dat['link']):'',
                    'img'=>$dat['type']==2?$dat['img'][0]:'',
                ]);
            }
            else{
                $res = Db::name('website_contact')->insert([
                    'system_id'=>$system_id,
                    'name'=>trim($dat['name']),
                    'ico'=>$dat['ico'][0],
                    'type'=>$dat['type'],
                    'link'=>$dat['type']==1?trim($dat['link']):'',
                    'img'=>$dat['type']==2?$dat['img'][0]:'',
                    'createtime'=>time()
                ]);
            }

            if($res){
                return json(['code' => 0, 'msg' => '保存成功']);
            }
            
        }else{
            $data = ['name'=>'','type'=>'','link'=>'','img'=>'','ico'=>''];
            if($id>0){
                $data = Db::name('website_contact')->where('id',$id)->find();
            }
            return view('',compact('id','data','system_id'));
        }
    }
    
    //删除联系方式
    public function del_contact(Request $request){
        $dat = input();
        $res = Db::name('website_contact')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }

    #跨境政策-start
    //新闻分类
    public function newscate_manage(Request $request){
        $dat = input();

        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){

            $count = Db::name('policy_category')->where(['from_source'=>0])->order($order)->count();
            $rows = DB::name('policy_category')
                ->where(['from_source'=>0])
                ->limit($limit)
                ->order($order)
                ->select();
            foreach($rows as $k=>&$v){
                $v['category_name'] = json_decode($v['category_name'],true)['zh'];
                $v['show'] = $v['show']==1?'隐藏':'显示';
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('');
        }
    }

    public function save_newscate(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){

            if($id>0){
//                ,'category_img'=>$dat['category_img'][0]
                Db::name('policy_category')->where('id',$id)->update(['category_name'=>json_encode(['zh'=>trim($dat['category_name']['zh'])],true),'from_source'=>0,'show'=>2]);
            }else{
                Db::name('policy_category')->insert(['category_name'=>json_encode(['zh'=>trim($dat['category_name']['zh'])],true),'from_source'=>0,'createtime'=>time(),'show'=>2]);
            }

            return json(['code'=>0,'msg'=>'保存成功！']);
        }else{
            $data = ['category_name'=>['zh'=>'','cht'=>'','en'=>'']];
            if($id>0){
                $data = Db::name('policy_category')->where('id',$id)->find();
                $data['category_name'] = json_decode($data['category_name'],true);
            }
            return view('',compact('data','id'));
        }
    }

    public function del_newscate(Request $request){
        $dat = input();
//        $msg = $dat['typ']==1?'隐藏':'显示';
        $res = Db::name('policy_category')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }

    //新闻列表
    public function news_list(Request $request){
        $dat = input();
        $cate_id = $dat['id'];

        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('policy_list')->where(['cate_id'=>$cate_id])->order($order)->count();
            $rows = DB::name('policy_list')
                ->where(['cate_id'=>$cate_id])
                ->limit($limit)
                ->order($order)
                ->select();
            foreach($rows as $k=>&$v){
                $v['name'] = json_decode($v['name'],true)['zh'];
                $v['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('cate_id'));
        }
    }

    #导出exl
    public function out_news(Request $request){
        $dat = input();
        $rows = DB::name('policy_list')->where(['from_source'=>0])->order('release_date','desc')->select();
        foreach($rows as $k=>$v){
            $rows[$k]['issuing_authority'] = json_decode($v['issuing_authority'],true)['zh'];
            $rows[$k]['document_number'] = json_decode($v['document_number'],true)['zh'];
            $rows[$k]['name'] = json_decode($v['name'],true)['zh'];
            $rows[$k]['effect'] = json_decode($v['effect'],true)['zh'];
            $rows[$k]['file'] = json_decode($v['file'],true);
            if(!empty($rows[$k]['file'])){
                $rows[$k]['file'][0] = 'https://shop.gogo198.cn/'.$rows[$k]['file'][0];
            }
            $cate_name = Db::name('policy_category')->where(['id'=>$v['cate_id']])->find()['category_name'];
            $rows[$k]['cate_name'] = json_decode($cate_name,true)['zh'];
        }
        #输出excel表格
        $fileName = '跨境政策数据['.date('Y-m-d H:i:s').'].xls';
        $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes';
        $dir2 = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
        require_once($dir."/PHPExcel.php");
        require_once($dir2."/IOFactory.php");
        $Excel = new PHPExcel();
        $PHPSheet = $Excel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('跨境政策'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue('A1', '分类名称');
        $PHPSheet->setCellValue('B1', '政策标题');
        $PHPSheet->setCellValue('C1', '发文机关');
        $PHPSheet->setCellValue('D1', '文号');
        $PHPSheet->setCellValue('E1', '效力');
        $PHPSheet->setCellValue('F1', '发布日期');
        $PHPSheet->setCellValue('G1', '生效日期');
        $PHPSheet->setCellValue('H1', '公布链接');
        $PHPSheet->setCellValue('I1', '文件链接');
        $PHPSheet->setCellValue('J1', '备注');
        $ExcelWrite = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');
        $n = 2;
        if (!empty($rows)) {
            foreach ($rows as $value) {
                $PHPSheet->setCellValue('A'.$n,"\t" .$value['cate_name']."\t")
                    ->setCellValue('B'.$n,"\t" .$value['name']."\t")
                    ->setCellValue('C'.$n,"\t" .$value['issuing_authority']."\t")
                    ->setCellValue('D'.$n,"\t" .$value['document_number']."\t")
                    ->setCellValue('E'.$n,"\t" .$value['effect']."\t")
                    ->setCellValue('F'.$n,"\t" .$value['release_date']."\t")
                    ->setCellValue('G'.$n,"\t" .$value['effective_date']."\t")
                    ->setCellValue('H'.$n,"\t" .$value['link']."\t")
                    ->setCellValue('I'.$n,"\t" .$value['file'][0]."\t")
                    ->setCellValue('J'.$n,"\t" .$value['remark']."\t");
                    $n +=1;
            }
        }

        ob_end_clean();//清楚缓冲避免乱码
        header('pragma:public');
        //设置表头信息
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name='.$fileName);
        header("Content-Disposition:attachment;filename={$fileName}");//attachment新窗口打
        return $ExcelWrite->save('php://output');
    }

    public function save_news(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $cate_id = isset($dat['cate_id'])?$dat['cate_id']:0;
        $cate_name = Db::name('policy_category')->where(['id'=>$cate_id])->find()['category_name'];
        $cate_name = json_decode($cate_name,true)['zh'];

        if($request->isAjax()){
            if($dat['origin_type']==1){
                $ishave = Db::name('policy_list')->where(['link'=>trim($dat['link'])])->find();
                if(!empty($ishave)){
                    if($ishave['id']!=$id){
                        return json(['code'=>-2,'msg'=>'已存在该原文链接！','id'=>$ishave['id']]);
                    }
                }
            }
            if($dat['origin_type']==1 && empty($dat['link'])){
                return json(['code'=>-1,'msg'=>'请填写公布链接！']);
            }elseif($dat['origin_type']==2 && empty($dat['file'])){
                return json(['code'=>-1,'msg'=>'请上传文件！']);
            }
            if($id>0){
                Db::name('policy_list')->where('id',$id)->update([
                    'issuing_authority'=>json_encode(['zh'=>trim($dat['issuing_authority']['zh'])],true),
                    'document_number'=>json_encode(['zh'=>trim($dat['document_number']['zh'])],true),
                    'name'=>json_encode(['zh'=>trim($dat['name']['zh'])],true),
                    'release_date'=>trim($dat['release_date']),
                    'effective_date'=>trim($dat['effective_date']),
                    'effect'=>json_encode(['zh'=>trim($dat['effect']['zh'])],true),
                    'content'=>isset($dat['content_zh'])?json_encode(['zh'=>$dat['content_zh']],true):'',
                    'origin_type'=>$dat['origin_type'],
                    'link'=>$dat['origin_type']==1?trim($dat['link']):'',
                    'file'=>$dat['origin_type']==2?json_encode($dat['file'],true):'',
                    'avatar'=>isset($dat['avatar'][0])?$dat['avatar'][0]:'',
                    'avatar_location'=>$dat['avatar_location'],
                    'format'=>$dat['format'],
                    'color'=>$dat['format']==2?$dat['color']:'',
                    'color_word'=>$dat['format']==2?json_encode(['zh'=>trim($dat['color_word_zh'])]):'',
                    'word_color'=>$dat['format']==2?$dat['word_color']:'',
                    'seo_type'=>$dat['seo_type'],
                    'seo_content'=>$dat['seo_type']==2?json_encode($dat['seo_content'],true):'',
                    'url'=>trim($dat['url']),#我要服务链接
                    'remark'=>trim($dat['remark']),
                    'from_source'=>0
                ]);
            }else{
                $res=Db::name('policy_list')->insert([
                    'cate_id'=>$cate_id,
                    'issuing_authority'=>json_encode(['zh'=>trim($dat['issuing_authority']['zh'])],true),
                    'document_number'=>json_encode(['zh'=>trim($dat['document_number']['zh'])],true),
                    'name'=>json_encode(['zh'=>trim($dat['name']['zh'])],true),
                    'release_date'=>trim($dat['release_date']),
                    'effective_date'=>trim($dat['effective_date']),
                    'effect'=>json_encode(['zh'=>trim($dat['effect']['zh'])],true),
                    'content'=>isset($dat['content_zh'])?json_encode(['zh'=>$dat['content_zh']],true):'',
                    'origin_type'=>$dat['origin_type'],
                    'link'=>$dat['origin_type']==1?trim($dat['link']):'',
                    'file'=>$dat['origin_type']==2?json_encode($dat['file'],true):'',
                    'avatar'=>isset($dat['avatar'][0])?$dat['avatar'][0]:'',
                    'avatar_location'=>$dat['avatar_location'],
                    'format'=>$dat['format'],
                    'color'=>$dat['format']==2?$dat['color']:'',
                    'color_word'=>$dat['format']==2?json_encode(['zh'=>trim($dat['color_word_zh'])]):'',
                    'word_color'=>$dat['format']==2?$dat['word_color']:'',
                    'seo_type'=>$dat['seo_type'],
                    'seo_content'=>$dat['seo_type']==2?json_encode($dat['seo_content'],true):'',
                    'url'=>trim($dat['url']),#我要服务链接
                    'remark'=>trim($dat['remark']),
                    'createtime'=>time(),
                    'from_source'=>0
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功！']);
        }else{
            $data = ['issuing_authority'=>['zh'=>'','cht'=>'','en'=>''],'document_number'=>['zh'=>'','cht'=>'','en'=>''],'name'=>['zh'=>'','cht'=>'','en'=>''],'release_date'=>'','effective_date'=>'','effect'=>['zh'=>'','cht'=>'','en'=>''],'effect_statement'=>['zh'=>'','cht'=>'','en'=>''],'link'=>'','content'=>['zh'=>'','cht'=>'','en'=>''],'desc'=>['zh'=>'','cht'=>'','en'=>''],'seo_type'=>1,'seo_content'=>['title'=>['zh'=>'','cht'=>'','en'=>''],'keywords'=>['zh'=>'','cht'=>'','en'=>''],'desc'=>['zh'=>'','cht'=>'','en'=>'']],'color_word'=>['zh'=>'','cht'=>'','en'=>''],'url'=>'','avatar'=>'','avatar_location'=>'','format'=>'','color'=>'','word_color'=>'','remark'=>'','file'=>'','origin_type'=>1];
            if($id>0){
                $data = Db::name('policy_list')->where('id',$id)->find();
                $cate_name = Db::name('policy_category')->where(['id'=>$data['cate_id']])->find()['category_name'];
                $cate_name = json_decode($cate_name,true)['zh'];

                if($data['format']==2){
                    $data['color_word'] = json_decode($data['color_word'],true);
                }else{
                    $data['color_word'] = ['zh'=>'','cht'=>'','en'=>''];
                }
                $data['name'] = json_decode($data['name'],true);
                $data['issuing_authority'] = json_decode($data['issuing_authority'],true);
                $data['document_number'] = json_decode($data['document_number'],true);
                $data['effect'] = json_decode($data['effect'],true);
                $data['effect_statement'] = json_decode($data['effect_statement'],true);
                $data['content'] = json_decode($data['content'],true);
                $data['desc'] = json_decode($data['desc'],true);
                $data['seo_content'] = json_decode($data['seo_content'],true);
                $data['file'] = json_decode($data['file'],true);
            }
            return view('',compact('data','id','cate_id','cate_name'));
        }
    }

    public function news_check_link(Request $request){
        $dat = input();
        $ishave = Db::name('policy_list')->where(['link'=>$dat['link']])->where('id','<>',$dat['id'])->find();
        if(empty($ishave)){
            return json(['code'=>1,'msg'=>'链接无重']);
        }else{
            return json(['code'=>2,'msg'=>'链接重复']);
        }
    }

    public function del_news(Request $request){
        $dat = input();
        $res = Db::name('policy_list')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }
    #跨境政策-end

    #平台规则-start
    #规则类别&关键词列表
    public function platform_rule_manage(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('website_platform_rule')->where(['key_id'=>$pid,'pid'=>0])->order($order)->count();
            $rows = DB::name('website_platform_rule')
                ->where(['key_id'=>$pid,'pid'=>0])
                ->limit($limit)
                ->order($order)
                ->select();
            foreach($rows as $k=>$v){
                $v['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            $kname = Db::name('website_platform_keywords')->where(['id'=>$pid])->find();
            $pname = Db::name('website_platform_type')->where(['id'=>$kname['type_id']])->find()['name'];
            $kname = $kname['name'];
            return view('',compact('pid','kname','pname'));
        }
    }
    #类别列表
    public function rule_type(Request $request){
        $dat = input();
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('website_platform_type')->order($order)->count();
            $rows = DB::name('website_platform_type')
                ->limit($limit)
                ->order($order)
                ->select();
//            foreach($rows as $k=>$v){
//                $rows[$k]['name'] = $v['type_name'].'['.$v['name'].']';
//                $v['name'] = json_decode($v['name'],true)['zh'];
//                $v['createtime'] = date('Y-m-d H:i',$v['createtime']);
//            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }
    #保存类别
    public function save_rule_type(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if( request()->isPost() || request()->isAjax()){
            if($id>0){
                $res = Db::name('website_platform_type')->where(['id'=>$id])->update(['name'=>trim($dat['name'])]);
            }else{
                $res = Db::name('website_platform_type')->insert(['name'=>trim($dat['name'])]);
            }
            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = ['name'=>''];
            if($id>0){
                $data = Db::name('website_platform_type')->where(['id'=>$id])->find();
            }
            return view('',compact('data','id'));
        }
    }
    #删除类别
    public function del_rule_type(Request $request){
        $dat = input();
        $res = Db::name('website_platform_type')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
    #关键字列表
    public function keywords_list(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?$dat['pid']:0;
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('website_platform_keywords')->where(['type_id'=>$pid])->order($order)->count();
            $rows = DB::name('website_platform_keywords')
                ->where(['type_id'=>$pid])
                ->limit($limit)
                ->order($order)
                ->select();
//            foreach($rows as $k=>$v){
//                $rows[$k]['name'] = $v['type_name'].'['.$v['name'].']';
//                $v['name'] = json_decode($v['name'],true)['zh'];
//                $v['createtime'] = date('Y-m-d H:i',$v['createtime']);
//            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            $pname = Db::name('website_platform_type')->where(['id'=>$pid])->find()['name'];
            return view('',compact('pid','pname'));
        }
    }
    #保存关键字
    public function save_keywords(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $pid = isset($dat['pid'])?$dat['pid']:0;
        if( request()->isPost() || request()->isAjax()){
            if($id>0){
                $res = Db::name('website_platform_keywords')->where(['id'=>$id])->update(['name'=>trim($dat['name']),'type_id'=>$dat['type_id']]);
            }else{
                $res = Db::name('website_platform_keywords')->insert(['name'=>trim($dat['name']),'type_id'=>$dat['type_id']]);
            }
            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = ['name'=>'','type_id'=>$pid];
            if($id>0){
                $data = Db::name('website_platform_keywords')->where(['id'=>$id])->find();
            }
            $type = Db::name('website_platform_type')->select();
            return view('',compact('data','id','type','pid'));
        }
    }
    #删除关键字
    public function del_keywords(Request $request){
        $dat = input();
        $res = Db::name('website_platform_keywords')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
    #删除规则名称
    public function del_rule(Request $request){
        $dat = input();
        $res = Db::name('website_platform_keywords')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
    #所属同一类别、关键词的规则列表
    public function rule_manage(Request $request){
        $dat = input();
        $pid = intval($dat['pid']);
        $key_id = intval($dat['key_id']);
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('website_platform_rule')->where(['pid'=>$pid])->whereOr('id','=',$pid)->order($order)->count();
            $rows = DB::name('website_platform_rule')
                ->where(['pid'=>$pid])
                ->whereOr('id','=',$pid)
                ->limit($limit)
                ->order($order)
                ->select();
            foreach($rows as $k=>&$v){
//                $v['effecttime'] = date('Y-m-d H:i',$v['effecttime']);
//                $v['link'] = 'https://www.gogo198.net/?s=index/rule&id='.$v['id'];
                  $v['link'] = '<div class="btn btn-normal btn-xs" style="border:1px solid;" onclick="open_rule('.$v['id'].')">打开规则</div>';
//                $v['link'] = '<div class="copy copy_all btn btn-normal btn-xs" style="border:1px solid;" data-link="https://www.gogo198.net/?s=index/rule&id='.$v['id'].'" onclick="xs_all(this)">复制链接</div><div class="copy btn btn-normal btn-xs" style="border:1px solid;margin-left:10px;" onclick="share('.$v['id'].')">分享链接</div>';
                $v['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            $kname = Db::name('website_platform_keywords')->where(['id'=>$key_id])->find();
            $pname = Db::name('website_platform_type')->where(['id'=>$kname['type_id']])->find()['name'];
            $kname = $kname['name'];
            $vname = Db::name('website_platform_rule')->where(['id'=>$pid])->find()['rule_name'];
            return view('',compact('pid','key_id','pname','kname','vname'));
        }
    }
    #分享规则链接生成二维码
    public function share_rule_img(Request $request){
        $dat = input();
        $name = 'rule_'.$dat['id'];
        $folder = $_SERVER['DOCUMENT_ROOT'].'/collect_website/public/rule/qrcode/';

        #官网链接二维码
        $img = generate_code($name,'https://www.gogo198.net/?s=index/rule&id='.intval($dat['id']),$folder);

        return json(['code'=>0,'img'=>$img.'?v='.time(),'msg'=>'生成成功']);
    }
    #新增平台规则
    public function save_platform_rule(Request $request){
        $dat = input();

        $id = isset($dat['id'])?intval($dat['id']):0;
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        $key_id = isset($dat['key_id'])?intval($dat['key_id']):0;
        if( request()->isPost() || request()->isAjax()){
//            dd($dat);
            if(isset($dat['editorValue'])){
                return json(['code'=>-1,'msg'=>'提交过快，富文本未保存']);
            }
            $time = time();
            $keyword = $key_id = $type_id = '';
            if($dat['method']==1){
                $key_id=$dat['key_id1'];
                $type_id=$dat['type_id1'];
                $keyword = Db::name('website_platform_keywords')->where(['id'=>$key_id])->find()['name'];
            }elseif($dat['method']==2){
                if(empty($dat['add_type_name'])){
                    $type_id = $dat['type_id1'];
                }else{
                    #1、插入类别
                    $type_id = Db::name('website_platform_type')->insertGetId(['name'=>trim($dat['add_type_name'])]);
                }
                #2、插入关键字
                $keyword = trim($dat['add_keyword']);
                $key_id = Db::name('website_platform_keywords')->insertGetId(['type_id'=>$type_id,'name'=>$keyword]);
            }
            #3、根据规则文本类型判断
            $content = [];
            if($dat['type']==1){
                foreach($dat['parag_num'] as $k=>$v){
                    if(isset($dat['parag_num'][$k])) {
                        #标题判断
                        if(!empty($dat['title'][$k])){
                            $dat['is_title'][$k]=1;
                        }else{
                            $dat['is_title'][$k]=-1;
                        }
                        array_push($content,[
                            'parag_num'=>trim($v),
                            'pnum'=>$dat['pnum'][$k],
                            'is_title'=>$dat['is_title'][$k],
                            'title'=>trim($dat['title'][$k]),
                            'content'=>trim($dat['content'][$k]),
                        ]);
                    }
                }
                $content = json_encode($content,true);
            }elseif ($dat['type']==2){
                $content = json_encode($dat['content2'],true);
            }
            #3.1、检测有无修改
            if($id>0){
                #检测有无修改
                if($dat['is_preamble']==0){
                    $is_no_update = Db::name('website_platform_rule')->where(['id'=>$id,'content'=>$content,'rule_name'=>trim($dat['rule_name'])])->find();
                }elseif($dat['is_preamble']==1){
                    $is_no_update = Db::name('website_platform_rule')->where(['id'=>$id,'content'=>$content,'preamble_con'=>json_encode($dat['preamble_con'],true),'rule_name'=>trim($dat['rule_name'])])->find();
                }
                if(!empty($is_no_update)){
                    return json(['code'=>-1,'msg'=>'生成新版本失败，该版本内容无任何修改']);
                }
            }
            #4、版本序号
            $start = strtotime(date('Y-m-d',$time).' 00:00:00');
            $end = strtotime(date('Y-m-d',$time).' 23:59:59');
            $rule_num = Db::name('website_platform_rule')->where(['type_id'=>$type_id,'key_id'=>$key_id])->whereBetween('createtime',[$start,$end],'AND')->count();
            if(empty($rule_num)){
                $serial_number = str_pad(1,2,'0',STR_PAD_LEFT);
            }else{
                $serial_number = str_pad(intval($rule_num)+1,2,'0',STR_PAD_LEFT);
            }
            #5、插入数据表
            Db::name('website_platform_rule')->insert([
                'type_id'=>$type_id,
                'key_id'=>$key_id,
                'pid'=>$pid,
                'rule_name'=>trim($dat['rule_name']),
                'is_preamble'=>$dat['is_preamble'],
                'position_display'=>$dat['is_preamble']==1?$dat['position_display']:0,
                'preamble_con'=>$dat['is_preamble']==1?json_encode($dat['preamble_con'],true):'',
                'type'=>$dat['type'],
                'title'=>$dat['type']==1?json_encode($dat['title'],true):'',
                'content'=>$content,
                'version'=>'G/R/'.$keyword.'/'.date('Ymd',$time).'/'.$serial_number,
                'createtime'=>$time,
                'effecttime'=>$dat['effecttime']
            ]);
            #6、获取该同一类别、同一关键字的历史规则
            $list = Db::name('website_platform_rule')->where(['pid'=>$pid,'key_id'=>$key_id,'type_id'=>$type_id])->whereOr('id','=',$pid)->order('createtime desc')->select();
            foreach($list as $k=>$v){
                $list[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
//                $list[$k]['link'] = '<div class="layui-btn layui-btn-normal layui-btn-xs" style="border:1px solid;" onclick="open_rule('.$v['id'].')">打开规则</div>';
            }
            return json(['code'=>0,'msg'=>'保存成功','list'=>$list]);
        }else{
            $data = ['id'=>0,'type_id'=>0,'key_id'=>$key_id,'type'=>1,'title'=>[],'content'=>['parag_num'=>'','is_title'=>'1','title'=>'','content'=>'','pnum'=>''],'effecttime'=>date('Y/m/d'),'is_preamble'=>0,'position_display'=>1,'preamble_con'=>'','rule_name'=>''];
            if($key_id>0){
                $data['type_id'] = Db::name('website_platform_keywords')->where(['id'=>$key_id])->find()['type_id'];
            }
            #类别表
            $type = Db::name('website_platform_type')->select();
            #关键字表
            $keywords = Db::name('website_platform_keywords')->select();
            if($id>0){
                $data = Db::name('website_platform_rule')->where(['id'=>$id])->find();
                $data['content'] = json_decode($data['content'],true);
                if($data['is_preamble']==1){
                    $data['preamble_con'] = json_decode($data['preamble_con'],true);
                }
                if($data['type']==1){
                    $num = 0;
                    foreach($data['content'] as $k=>$v){
                        $big_parag_num2 = explode('.',$v['parag_num']);
                        if(count($big_parag_num2)==2){
                            $num+=1;
                        }
                    }
                    $data['big_parag_num'] = $num;
                }
                #关键字表
                $keywords = Db::name('website_platform_keywords')->where(['type_id'=>$data['type_id']])->select();
            }

            return view('',compact('id','data','type','keywords','pid','key_id'));
        }
    }
    #获取该类别下的所有关键词
    public function get_keywords(Request $request){
        $dat = input();
        if( request()->isPost() || request()->isAjax()){
            $list = Db::name('website_platform_keywords')->where(['type_id'=>$dat['type_id']])->select();
//            $list = Db::name('website_platform_keywords')->select();
            return json(['code'=>0,'data'=>$list]);
        }
    }
    #隐藏/显示平台规则
    public function hide_rule_manage(Request $request){
        $dat = input();
        if( request()->isPost() || request()->isAjax()){
            $status=0;
            if($dat['typ']==1){
                $status=1;
            }
            $res = Db::name('website_platform_rule')->where(['id'=>$dat['id']])->update(['status'=>$status]);
            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }
    }
    #生成pdf
    public function rule_pdf(Request $request){
        $dat = input();
        header("Content-Type:text/html;charset=utf-8");
//        require $_SERVER['DOCUMENT_ROOT'].'/collect_website/extend/lib/fpdf/fpdf.php';
//        $pdf = new FPDF();

        $data = Db::name('website_platform_rule')->where(['id'=>$dat['id']])->find();
        $data['content'] = json_decode($data['content'],true);
        $first = [];
        $second = [];
        foreach($data['content'] as $k=>$v){
            if($v['pnum']==0){
                array_push($first,[
                    'title'=>$v['title'],
                    'parag_num'=>$v['parag_num'],
                    'pnum'=>$v['pnum'],
                    'content'=>$v['content'],
                    'children'=>[],
                ]);
            }else{
                array_push($second,[
                    'title'=>$v['title'],
                    'parag_num'=>$v['parag_num'],
                    'pnum'=>$v['pnum'],
                    'content'=>$v['content'],
                    'children'=>[],
                ]);
            }
        }

        #最多嵌套3层
        foreach($first as $k=>$v){
            foreach($second as $k2=>$v2){
                if($v['parag_num']==$v2['pnum']){
                    #1.1.
                    array_push($first[$k]['children'],$v2);
                }else{
                    foreach($first[$k]['children'] as $k3=>$v3){
                        if($v3['parag_num']==$v2['pnum']){
                            #1.1.1.
                            array_push($first[$k]['children'][$k3]['children'],[
                                'title'=>$v2['title'],
                                'parag_num'=>$v2['parag_num'],
                                'pnum'=>$v2['pnum'],
                                'content'=>$v2['content'],
                                'children'=>[],
                            ]);
                        }
                    }
                }
            }
        }

        $data['content'] = $first;
        $data['createtime'] = date('Y-m-d H:i',$data['createtime']);
        //开始写入pdf
//        $pdf->AddPage();
//        $pdf->SetFont('Arial','B',16);
//        $pdf->SetTitle("test");
//        $pdf->Cell(40,10,'HELLO');
//        $pdf->Output('doc.pdf','D');
//        dd($data['content']);
        return view('',compact('data'));
    }
    #平台规则-end

    #企业动态-start
    public function enterprise_news_manage(Request $request){
        $dat = input();

        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('website_enterprise_news')->order($order)->count();
            $rows = DB::name('website_enterprise_news')
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

    public function save_enterprise_news(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $cate_id = isset($dat['cate_id'])?$dat['cate_id']:0;
        $cate_name = Db::name('policy_category')->where(['id'=>$cate_id])->find()['category_name'];
        $cate_name = json_decode($cate_name,true)['zh'];

        if($request->isAjax()){
            if($id>0){
                Db::name('website_enterprise_news')->where('id',$id)->update([
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
                $res=Db::name('website_enterprise_news')->insert([
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
                $data = Db::name('website_enterprise_news')->where('id',$id)->find();

                if(!empty($data['content'])){
                    $data['content'] = json_decode($data['content'],true);
                }
                $data['seo_content'] = json_decode($data['seo_content'],true);
            }
            $social = Db::name('website_contact')->select();
            return view('',compact('data','id','social'));
        }
    }

    public function del_enterprise_news(Request $request){
        $dat = input();
        $res = Db::name('website_enterprise_news')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }
    #企业动态-end

    #跨境新闻-start
    public function crossnews_manage(Request $request){
        $dat = input();
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('website_crossborder_news')->where(['status'=>intval($dat['status'])])->order($order)->count();
            $rows = DB::name('website_crossborder_news')
                ->where(['status'=>intval($dat['status'])])
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>$v){
                if(strstr($v['time'],':')){
                    $rows[$k]['time'] = explode(' ',$v['time'])[0];
                    Db::name('website_crossborder_news')->where(['id'=>$v['id']])->update(['time'=>$rows[$k]['time']]);
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('');
        }
    }

    public function del_crossborder_news(Request $request){
        $dat = input();

        $res = Db::name('website_crossborder_news')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }

    public function save_crossborder_news(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):'';
        if($request->isAjax()){
            if($id>0){
                $res = Db::name('website_crossborder_news')->where(['id'=>$id])->update([
                    'time'=>trim($dat['time']),
                    'title'=>trim($dat['title']),
                    'descs'=>trim($dat['descs']),
                    'link'=>trim($dat['link']),
                    'status'=>$dat['status']
                ]);
            }else{
                $res = Db::name('website_crossborder_news')->insert([
                    'time'=>trim($dat['time']),
                    'title'=>trim($dat['title']),
                    'descs'=>trim($dat['descs']),
                    'link'=>trim($dat['link']),
                    'status'=>1,
                    'pid'=>46//菜单id
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存并上架成功']);
            }
        }else{
            $data = ['time'=>'','title'=>'','descs'=>'','link'=>''];
            if($id>0){
                $data = Db::name('website_crossborder_news')->where(['id'=>$id])->find();
            }
            return view('',compact('data','id'));
        }
    }

    public function shelf_crossborder_news(Request $request){
        $dat = input();
        require 'simple_html_dom.php';
        $ids = ltrim($dat['ids'],',');
        if(empty($ids)){
            return json(['code'=>-1,'msg'=>'请选择上架内容']);
        }else{
            if($ids==10000){
                #全部上架

                $list = Db::name('website_crossborder_news')->where(['status'=>0])->select();

                foreach($list as $k=>$v){
                    #1、去重
                    $recurring = Db::name('website_crossborder_news')->where(['title'=>$v['title']])->select();
                    if(count($recurring)>1){
                        foreach($recurring as $k2=>$v2){
                            if($k2!=0){
                                Db::name('website_crossborder_news')->where(['id'=>$v2['id']])->delete();
                            }
                        }
                    }
                    #2、清楚链接为空的
                    if(empty($v['link'])){
                        Db::name('website_crossborder_news')->where(['id'=>$v['id']])->delete();
                    }

                    #3、修改时间为yyyy-mm-dd格式
                    if(strstr($v['time'],':')){
                        $time = explode(' ',$v['time'])[0];
                        Db::name('website_crossborder_news')->where(['id'=>$v['id']])->update(['time'=>$time]);
                    }
                }

                #4、获取原文链接&上架
                $list2 = Db::name('website_crossborder_news')->where(['status'=>0])->select();
                foreach($list2 as $k=>$v){
                    // 创建一个新的 HTML DOM 对象并从给定 URL 加载页面内容
                    try{
                        $html = file_get_html($v['link']);
                    }catch (\Exception $e) {
                        $html = file_get_html(str_replace('https','http',$v['link']));
                    }

                    // 使用 CSS 选择器查找特定元素
                    $elements = $html->find('.target-url-content');
                    // 遍历找到的元素并输出它们的文本内容
                    foreach ($elements as $element) {
                        Db::name('website_crossborder_news')->where(['status'=>0,'id'=>$v['id']])->update([
                            'status'=>1,
                            'link'=>trim($element->plaintext),
                            'pid'=>46//菜单id
                        ]);
                    }
                    // 释放内存，清理资源
                    $html->clear();
                }
            }else{
                #手动执行
                $ids = explode(',',$ids);
                foreach($ids as $k=>$v){
                    $info = Db::name('website_crossborder_news')->where(['id'=>$v])->find();
                    #1、清楚链接为空的
                    if(empty($info['link'])){
                        Db::name('website_crossborder_news')->where(['id'=>$v['id']])->delete();
                        continue;
                    }
                    #2、获取原文链接&上架
//                    $html = file_get_html($info['link']);
                    try{
                        $html = file_get_html($info['link']);
                    }catch (\Exception $e) {
                        $html = file_get_html(str_replace('https','http',$info['link']));
                    }
                    $elements = $html->find('.target-url-content');
                    foreach ($elements as $element) {
                        Db::name('website_crossborder_news')->where(['status'=>0,'id'=>$v])->update([
                            'status'=>1,
                            'link'=>trim($element->plaintext),
                            'pid'=>46//菜单id
                        ]);
                    }
                    $html->clear();
//                    Db::name('website_crossborder_news')->where(['id'=>$v])->update(['status'=>1]);
                }
            }

            return json(['code'=>0,'msg'=>'全部上架成功']);
        }
    }
    #跨境新闻-end

    //企业资质管理
    public function qualifications_manage(Request $request){
        $dat = input();

        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('website_qualifications')->order($order)->count();
            $rows = DB::name('website_qualifications')
                ->limit($limit)
                ->order($order)
                ->select();
            foreach($rows as $k=>&$v){
                $v['name'] = json_decode($v['name'],true)['zh'];
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('');
        }
    }

    public function add_qualific(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isAjax()){
            if($id>0){
                Db::name('website_qualifications')->where('id',$id)->update([
                    'name'=>json_encode(['zh'=>trim($dat['name']['zh'])],true),
                    'img'=>isset($dat['img'][0])?$dat['img'][0]:'',
                ]);
            }else{
                Db::name('website_qualifications')->insert([
                    'name'=>json_encode(['zh'=>trim($dat['name']['zh'])],true),
                    'img'=>isset($dat['img'][0])?$dat['img'][0]:'',
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功！']);
        }else{
            $data = ['name'=>['zh'=>'','cht'=>'','en'=>''],'img'=>''];
            if($id>0){
                $data = Db::name('website_qualifications')->where('id',$id)->find();
                $data['name'] = json_decode($data['name'],true);
            }
            return view('',compact('data','id'));
        }
    }
    #添加企业规则
    public function add_qualific2(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isAjax()){
            if($id>0){
                Db::name('website_qualifications')->where('id',$id)->update([
                    'name'=>json_encode(['zh'=>trim($dat['name']['zh'])],true),
                    'link'=>$dat['link'],
                ]);
            }else{
                $displayorder = Db::name('website_qualifications')->insertGetId([
                    'name'=>json_encode(['zh'=>trim($dat['name']['zh'])],true),
                    'link'=>$dat['link'],
                ]);

                Db::name('website_qualifications')->where(['id'=>$displayorder])->update(['displayorder'=>$displayorder]);
            }

            return json(['code'=>0,'msg'=>'保存成功！']);
        }else{
            $data = ['name'=>['zh'=>'','cht'=>'','en'=>''],'link'=>''];
            if($id>0){
                $data = Db::name('website_qualifications')->where('id',$id)->find();
                $data['name'] = json_decode($data['name'],true);
            }
            return view('',compact('data','id'));
        }
    }

    public function del_qualific(Request $request){
        $dat = input();
        $res = Db::name('website_qualifications')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }

    //友情链接分类管理
    public function friendcate_manage(Request $request){
        $dat = input();

        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('website_linkcategory')->where(['company_id'=>0])->order($order)->count();
            $rows = DB::name('website_linkcategory')
                ->where(['company_id'=>0])
                ->limit($limit)
                ->order($order)
                ->select();
            foreach($rows as $k=>&$v){
                $v['name'] = json_decode($v['name'],true)['zh'];
                $v['show'] = $v['show']==1?'隐藏':'显示';
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('');
        }
    }

    public function add_friendcate(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isAjax()){
            if($id>0){
                Db::name('website_linkcategory')->where('id',$id)->update([
                    'name'=>json_encode(['zh'=>trim($dat['name']['zh'])],true),
                ]);
            }else{
                Db::name('website_linkcategory')->insert([
                    'name'=>json_encode(['zh'=>trim($dat['name']['zh'])],true),
                    'show'=>2
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功！']);
        }else{
            $data = ['name'=>['zh'=>'','cht'=>'','en'=>'']];
            if($id>0){
                $data = Db::name('website_linkcategory')->where('id',$id)->find();
                $data['name'] = json_decode($data['name'],true);
            }
            return view('',compact('data','id'));
        }
    }

    public function del_friendcate(Request $request){
        $dat = input();
        $msg = $dat['typ']==1?'隐藏':'显示';
        $res = Db::name('website_linkcategory')->where('id',$dat['id'])->update(['show'=>$dat['typ']]);
        if($res){
            return json(['code'=>0,'msg'=>$msg.'成功！']);
        }
    }

    //友情链接管理
    public function friend_list(Request $request){
        $dat = input();
        $cate_id = $dat['id'];

        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('website_link')->where('cate_id',$cate_id)->order($order)->count();
            $rows = DB::name('website_link')
                ->where('cate_id',$cate_id)
                ->limit($limit)
                ->order($order)
                ->select();
            foreach($rows as $k=>&$v){
                $v['name'] = json_decode($v['name'],true)['zh'];
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('cate_id'));
        }
    }

    public function add_friend(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $cate_id = isset($dat['cate_id'])?$dat['cate_id']:0;
        $cate_name = Db::name('website_linkcategory')->where(['id'=>$cate_id])->find()['name'];
        $cate_name = json_decode($cate_name,true)['zh'];

        if($request->isAjax()){
            if($id>0){
                Db::name('website_link')->where('id',$id)->update([
                    'name'=>json_encode(['zh'=>trim($dat['name']['zh'])],true),
                    'link'=>trim($dat['link'])
                ]);
            }else{
                Db::name('website_link')->insert([
                    'cate_id'=>$cate_id,
                    'name'=>json_encode(['zh'=>trim($dat['name']['zh'])],true),
                    'link'=>trim($dat['link'])
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功！']);
        }else{
            $data = ['name'=>['zh'=>'','cht'=>'','en'=>''],'link'=>''];
            if($id>0){
                $data = Db::name('website_link')->where('id',$id)->find();
                $data['name'] = json_decode($data['name'],true);
            }
            return view('',compact('data','id','cate_name','cate_id'));
        }
    }

    public function del_friend(Request $request){
        $dat = input();
        $res = Db::name('website_link')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }

    //轮播图管理
    public function rotate_manage(Request $request){
        $dat = input();
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('website_rotate')->where(['system_id'=>$system_id])->order($order)->count();
            $rows = DB::name('website_rotate')
                ->where(['system_id'=>$system_id])
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>$v){
                $rows[$k]['title'] = json_decode($v['title'],true)['zh'];
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('system_id'));
        }
    }

    public function save_rotate(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;
        if($request->isAjax()){
            $res = '';
            if($id>0){
                $res = Db::name('website_rotate')->where('id',$id)->update([
                    'type'=>$dat['type'],
                    'title'=>json_encode($dat['title'],true),
                    'thumb'=>$dat['thumb'][0],
                    'mob_thumb'=>isset($dat['mob_thumb'][0])?$dat['mob_thumb'][0]:'',
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'other_pic'=>$dat['go_other']==3?trim($dat['other_pic']):'',
                    'other_msg'=>$dat['go_other']==4?trim($dat['other_msg']):'',
                    'other_keywords'=>$dat['go_other']==5?trim($dat['other_keywords']):'',
                    'system_id'=>$system_id
                ]);
            }else{
                $res = Db::name('website_rotate')->insertGetId([
                    'type'=>$dat['type'],
                    'title'=>json_encode($dat['title'],true),
                    'thumb'=>$dat['thumb'][0],
                    'mob_thumb'=>isset($dat['mob_thumb'][0])?$dat['mob_thumb'][0]:'',
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'other_pic'=>$dat['go_other']==3?trim($dat['other_pic']):'',
                    'other_msg'=>$dat['go_other']==4?trim($dat['other_msg']):'',
                    'other_keywords'=>$dat['go_other']==5?trim($dat['other_keywords']):'',
                    'system_id'=>$system_id
                ]);
                $id = $res;
            }

            if($res){
//                if($dat['go_other']==5) {
//                    if(!empty($dat['other_keywords'])){
//                        #新增时自动获取商品(队列服务)
//                        $options = array('http' => array('timeout' => 7500));
//                        $context = stream_context_create($options);
//                        file_get_contents('https://decl.gogo198.cn/api/v2/get_content_goods?type=4&id=' . $id, false, $context);
//                    }
//                }
                return json(['code'=>0,'msg'=>'保存成功！']);
            }else{
                return json(['code'=>0,'msg'=>'暂无修改']);
            }
        }else{
            $data = ['type'=>0,'title'=>['zh'=>'','cht'=>'','en'=>''],'thumb'=>'','mob_thumb'=>'','go_other'=>'','other_link'=>'','other_navbar'=>'','other_pic'=>'','other_msg'=>'','other_keywords'=>''];
            if($id>0){
                $data = Db::name('website_rotate')->where('id',$id)->find();
                $data['title'] = json_decode($data['title'],true);
            }
            
            $list='';
            if($system_id==1 || $system_id==3 || $system_id==4 || $system_id==8){
                $list = $this->menu($system_id);
            }elseif($system_id==2){
                $list = $this->get_gather_process();
            }

            #图文链接
            $pic_list = Db::name('website_image_txt')->select();
            foreach($pic_list as $k=>$v){
                $pic_list[$k]['name'] = json_decode($v['name'],true)['zh'];
            }

            #消息链接
            $msg_list = Db::name('website_message_manage')->select();
            
            return view('',compact('data','id','list','system_id','pic_list','msg_list'));
        }
    }

    #获取集运流程
    public function get_gather_process($id=0){
        $list = Db::name('centralize_process_list')->where(['pid'=>0,'display'=>0])->order('displayorders','asc')->select();
        if(!empty($list)){
            foreach($list as $k=>$v){
                #2
                $list[$k]['children'] = Db::name('centralize_process_list')->where(['pid'=>$v['id'],'display'=>0])->order('displayorders','asc')->select();
                if(!empty($list[$k]['children'])){
                    foreach($list[$k]['children'] as $k2=>$v2) {
                        #3
                        $list[$k]['children'][$k2]['children'] = Db::name('centralize_process_list')->where(['pid' => $v2['id'],'display'=>0])->order('displayorders', 'asc')->select();
                        if(!empty($list[$k]['children'][$k2]['children'])){
                            foreach($list[$k]['children'][$k2]['children'] as $k3=>$v3) {
                                #4
                                $list[$k]['children'][$k2]['children'][$k3]['children'] = Db::name('centralize_process_list')->where(['pid' => $v3['id'],'display'=>0])->order('displayorders', 'asc')->select();
                            }
                        }
                    }
                }
            }
        }
        return $list;
    }

    public function del_rotate(Request $request){
        $dat = input();
        $res = Db::name('website_rotate')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }

    //内页轮播图管理
    public function rotate_inner(Request $request){
        $dat = input();

        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('website_rotate_inner')->order($order)->count();
            $rows = DB::name('website_rotate_inner')
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>$v){
                $rows[$k]['title'] = json_decode($v['title'],true)['zh'];
                $title = Db::name('website_navbar')->where('id',$v['navbar_id'])->field(['name'])->find();
                $rows[$k]['navbar_name'] = json_decode($title['name'],true)['zh'];
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('');
        }
    }

    public function save_rotateinner(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):1;

        if($request->isAjax()){
            if($id>0){
                Db::name('website_rotate_inner')->where('id',$id)->update([
                    'title'=>json_encode($dat['title'],true),
                    'thumb'=>$dat['thumb'][0],
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'navbar_id'=>intval($dat['navbar_id']),
                ]);
            }else{
                Db::name('website_rotate_inner')->insert([
                    'title'=>json_encode($dat['title'],true),
                    'thumb'=>$dat['thumb'][0],
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'navbar_id'=>intval($dat['navbar_id']),
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功！']);
        }else{
            $list = $this->menu($system_id);

            $data = ['title'=>['zh'=>'','cht'=>'','en'=>''],'thumb'=>'','go_other'=>'','other_link'=>'','other_navbar'=>'','navbar_id'=>''];
            if($id>0){
                $data = Db::name('website_rotate_inner')->where('id',$id)->find();
                $data['title'] = json_decode($data['title'],true);
            }
            return view('',compact('data','id','list'));
        }
    }

    public function del_rotateinner(Request $request){
        $dat = input();
        $res = Db::name('website_rotate_inner')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }

    //口号管理
    public function slogan_manage(Request $request){
        $dat = input();
        $system_id = isset($dat['system_id'])?$dat['system_id']:0;
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){

            $count = Db::name('website_slogan')->where(['system_id'=>$system_id])->order($order)->count();
            $rows = DB::name('website_slogan')
                ->where(['system_id'=>$system_id])
                ->limit($limit)
                ->order($order)
                ->select();
            foreach($rows as $k=>$v){

            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(['system_id']));
        }
    }

    public function save_slogan(Request $request){
        $dat = input();
        $system_id = isset($dat['system_id'])?$dat['system_id']:0;
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){

            if($id>0){
                Db::name('website_slogan')->where('id',$id)->update([
                    'text'=>trim($dat['text']),
                ]);
            }else{
                Db::name('website_slogan')->insert([
                    'system_id'=>$system_id,
                    'text'=>trim($dat['text']),
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功！']);
        }else{
            $data = ['text'=>''];
            if($id>0){
                $data = Db::name('website_slogan')->where('id',$id)->find();
            }
            return view('',compact('data','id','system_id'));
        }
    }

    public function del_slogan(Request $request){
        $dat = input();
        $res = Db::name('website_slogan')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }

    //菜单管理
    public function menu_manage(Request $request){
        $dat = input();
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;

        if ($request->isAJAX()) {
            $list = Db::name('website_navbar')->where(['system_id'=>$system_id,'company_id'=>0])->order('displayorder,id asc')->select();
            foreach($list as $k=>$v){
                $list[$k]['name'] = json_decode($v['name'],true)['zh'];
            }
            return json(['code' => 0, 'msg' => '', 'count' => count($list), 'data' => $list]);
        }else{
            return view('',compact('system_id'));
        }
    }

    //创建菜单内容
    public function save_menu(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;

        if ($request->isAJAX()) {
            $content_id = 0;
            if($dat['type']==2 || $dat['type']==3 || $dat['type']==4){
                $content_id = intval($dat['content_id']);
                if($content_id==4){
                    $dat['go_other']=1;
                    $dat['other_link']=trim($dat['other_link2']);
                }
            }

            if($id>0){
                $res = Db::name('website_navbar')->where('id',$dat['id'])->update([
                    'displayorder'=>intval($dat['displayorder2']),
                    'name'=>json_encode(['zh'=>trim($dat['name']['zh'])]),
                    'content'=>isset($dat['content_zh'])?json_encode(['zh'=>$dat['content_zh']]):'',
                    'type'=>intval($dat['type']),
                    'content_id'=>$content_id,
                    'thumb'=>isset($dat['thumb'][0])?$dat['thumb'][0]:'',
                    'avatar_location'=>$dat['format']==1?$dat['avatar_location']:$dat['avatar_location2'],
                    'format'=>$dat['format'],
                    'color'=>$dat['format']==2?$dat['color']:'',
                    'color_word'=>$dat['format']==2?json_encode(['zh'=>trim($dat['color_word_zh'])]):'',
                    'word_color'=>$dat['format']==2?$dat['word_color']:'',
                    'desc'=>json_encode(['zh'=>trim($dat['desc']['zh'])]),
                    'url'=>$dat['url'],
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'other_moreimagecategory'=>$dat['go_other']==3?trim($dat['other_moreimagecategory']):'',
                    'seo_type'=>$dat['seo_type'],
                    'seo_content'=>$dat['seo_type']==2?json_encode($dat['seo_content'],true):'',
                    'pid'=>isset($dat['pid'])?$dat['pid']:0
                ]);
            }
            else{
                $res = Db::name('website_navbar')->insert([
                    'system_id'=>$system_id,
                    'displayorder'=>intval($dat['displayorder2']),
                    'name'=>json_encode(['zh'=>trim($dat['name']['zh'])]),
                    'content'=>isset($dat['content_zh'])?json_encode(['zh'=>$dat['content_zh']]):'',
                    'type'=>intval($dat['type']),
                    'content_id'=>$content_id,
                    'thumb'=>isset($dat['thumb'][0])?$dat['thumb'][0]:'',
                    'avatar_location'=>$dat['avatar_location'],
                    'format'=>$dat['format'],
                    'color'=>$dat['format']==2?$dat['color']:'',
                    'color_word'=>$dat['format']==2?json_encode(['zh'=>trim($dat['color_word_zh'])]):'',
                    'word_color'=>$dat['format']==2?$dat['word_color']:'',
                    'desc'=>json_encode(['zh'=>trim($dat['desc']['zh'])]),
                    'url'=>$dat['url'],
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'other_moreimagecategory'=>$dat['go_other']==3?trim($dat['other_moreimagecategory']):'',
                    'seo_type'=>$dat['seo_type'],
                    'seo_content'=>$dat['seo_type']==2?json_encode($dat['seo_content'],true):'',
                    'pid'=>isset($dat['pid'])?$dat['pid']:0
                ]);
            }

            if($res){
                return json(['code' => 0, 'msg' => '保存成功']);
            }
        }else{
            $data = ['displayorder'=>'','name'=>['zh'=>'','cht'=>'','en'=>''],'content'=>['zh'=>'','cht'=>'','en'=>''],'url'=>'','type'=>1,'content_id'=>1,'desc'=>['zh'=>'','cht'=>'','en'=>''],'seo_type'=>2,'seo_content'=>['title'=>['zh'=>'跨境电商综合服务运营','cht'=>'','en'=>''],'keywords'=>['zh'=>'跨境，跨境电商，跨境服务，服务运营，跨境运营，全球开店，跨境引流，引流推广，营销获客，跨境物流，跨境专线，散货拼箱，整柜订舱，包裹集运，海外仓，跨境支付、跨境结汇，海关申报系统，物流仓储系统，跨境聚合支付，跨境营销获客系统','cht'=>'','en'=>''],'desc'=>['zh'=>'跨境电商综合服务运营','cht'=>'','en'=>'']],'format'=>'','avatar_location'=>2,'color'=>'','word_color'=>'','color_word'=>['zh'=>'','cht'=>'','en'=>''],'go_other'=>0,'other_navbar'=>'','other_link'=>'','other_moreimagecategory'=>0];
            if($system_id!=1){
                $data['seo_type'] = 1;
                $data['seo_content'] = ['title'=>['zh'=>''],'keywords'=>['zh'=>''],'desc'=>['zh'=>'']];
                $data['avatar_location'] = 3;
            }
            if($id>0){
                $data = Db::name('website_navbar')->where('id',$id)->find();
                $data['content'] = json_decode($data['content'],true);
                $data['name'] = json_decode($data['name'],true);
                $data['desc'] = json_decode($data['desc'],true);
                if($data['format']==2){
                    $data['color_word'] = json_decode($data['color_word'],true);
                }else{
                    $data['color_word'] = ['zh'=>'','cht'=>'','en'=>''];
                }
                $dat['pid'] = $data['pid'];
                if($data['seo_type']==2){
                    $data['seo_content'] = json_decode($data['seo_content'],true);
//                    $data['seo_content'] = ['title'=>['zh'=>'','cht'=>'','en'=>''],'keywords'=>['zh'=>'','cht'=>'','en'=>''],'desc'=>['zh'=>'','cht'=>'','en'=>'']];
                }else{
                    $data['seo_content'] = ['title'=>['zh'=>'跨境电商综合服务运营','cht'=>'','en'=>''],'keywords'=>['zh'=>'跨境，跨境电商，跨境服务，服务运营，跨境运营，全球开店，跨境引流，引流推广，营销获客，跨境物流，跨境专线，散货拼箱，整柜订舱，包裹集运，海外仓，跨境支付、跨境结汇，海关申报系统，物流仓储系统，跨境聚合支付，跨境营销获客系统','cht'=>'','en'=>''],'desc'=>['zh'=>'跨境电商综合服务运营','cht'=>'','en'=>'']];
                }
            }
            $list = $this->menu($system_id);
            
            #版式
            $format = Db::connect($this->config)->name('guide_format')->select();

            #瀑布类别
            $more_image_category = Db::name('website_more_image_category')->select();

            return view('',compact('id','data','dat','list','format','system_id','more_image_category'));
        }
    }

    //删除菜单内容
    public function del_menu(Request $request){
        $id = $request->get('id');
        if ($request->get('id') === '') {
            return json(['code' => -1, 'msg' => '错误！']);
        }
        $have_child = Db::name('website_navbar')->where('pid',$id)->find();
        if ($have_child['id']) {
            return json(['code' => -1, 'msg' => '存在子级菜单,不允许删除!']);
        }

        $have_home = Db::name('website_index')->where('navbar_id',$id)->find();
        if ($have_home['id']) {
            return json(['code' => -1, 'msg' => '请先删除首页板块相应菜单!']);
        }
        Db::name('website_navbar')->where('id',$id)->delete();
        return json(['code' => 0, 'msg' => '已删除']);
    }

    //版式集管理
    public function format_list(Request $request){
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('website_format_list')->order($order)->count();
            $rows = DB::name('website_format_list')
                ->limit($limit)
                ->order($order)
                ->select();

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('cate_id'));
        }
    }

    //保存版式集
    public function save_format(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            if($id>0){
                Db::name('website_format_list')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'img'=>isset($dat['img'][0])?$dat['img'][0]:'',
                    'jizhi_desc'=>isset($dat['jizhi_desc'])?json_encode($dat['jizhi_desc'],true):'',
                ]);
            }else{
                Db::name('website_format_list')->insert([
                    'name'=>trim($dat['name']),
                    'img'=>isset($dat['img'][0])?$dat['img'][0]:'',
                    'jizhi_desc'=>isset($dat['jizhi_desc'])?json_encode($dat['jizhi_desc'],true):'',
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>'','img'=>'','jizhi_desc'=>[]];
            if($id>0){
                $data = Db::name('website_format_list')->where(['id'=>$id])->find();
                if(!empty($data['jizhi_desc'])){
                    $data['jizhi_desc'] = json_decode($data['jizhi_desc'],true);
                }
            }
            return view('',compact('id','data'));
        }
    }

    //删除版式集
    public function del_format(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        Db::name('website_format_list')->where(['id'=>$id])->delete();

        return json(['code' => 0, 'msg' => '已删除']);
    }

    //首页板块设置
    public function home_setting(Request $request){
        $dat = input();
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;

        if( request()->isPost() || request()->isAjax()){

            $count = Db::name('website_index')->where(['system_id'=>$system_id])->order($order)->count();
            $rows = Db::name('website_index')->where(['system_id'=>$system_id])->limit($limit)->order('displayorder,id asc')->select();
//                DB::name('website_index')
//                ->alias('a')
//                ->join('website_navbar b','b.id=a.navbar_id')
//                ->where(['a.system_id'=>$system_id])
//                ->limit($limit)
//                ->order($order)
//                ->field('a.*,b.name')
//                ->select();
            foreach($rows as $k=>$v){
                if(is_numeric($v['navbar_id'])){
                    #纯数字
                    $name = Db::name('website_navbar')->where(['id'=>intval($v['navbar_id'])])->field('name')->find()['name'];
                    $rows[$k]['name'] = json_decode($name,true)['zh'];
                }else{
                    if($v['navbar_id']=='A1'){
                        $rows[$k]['name'] = '发现轮播+信息切换框';
                    }elseif ($v['navbar_id']=='A2'){
                        $rows[$k]['name'] = '常见问题';
                    }
                }

                $rows[$k]['format_name'] = Db::name('website_format_list')->where(['id'=>$v['format_type']])->field('name')->find()['name'];
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('system_id'));
        }
    }

    //内容管理
    public function home_content(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;
        $guide_id = isset($dat['guide_id'])?intval($dat['guide_id']):0;
        $top_id = isset($dat['top_id'])?intval($dat['top_id']):0;

        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');

        if( request()->isPost() || request()->isAjax()){

            $count = Db::connect($this->config)->name('guide_content')->where(['system_id'=>$system_id,'company_id'=>0,'company_type'=>0,'pid'=>$pid,'top_id'=>$top_id])->count();
            $rows = Db::connect($this->config)->name('guide_content')->where(['system_id'=>$system_id,'company_id'=>0,'company_type'=>0,'pid'=>$pid,'top_id'=>$top_id])->limit($limit)->order('id desc')->select();

            $go_method_name = ['无','分享插件','商品详情','网站菜单','网址链接','搜索结果','我要咨询','去找客服','图文链接','消息链接'];
            foreach ($rows as &$item) {
                $item['go_method'] = $go_method_name[$item['go_other']];
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            #模块信息-版式
            $guide_info = Db::connect($this->config)->name('merchsite_guide_body')->where(['id'=>$guide_id])->find();

            return view('',compact('system_id','pid','guide_id','guide_info','top_id'));
        }
    }

    //保存内容
    public function save_home_content(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        $top_id = isset($dat['top_id'])?$dat['top_id']:0;
        $guide_id = isset($dat['guide_id'])?intval($dat['guide_id']):0;

        $guide_info = Db::connect($this->config)->name('merchsite_guide_body')->where(['id'=>$guide_id])->find();

        if($request->isAjax()){
            if($id>0){
                $res = Db::connect($this->config)->name('guide_content')->where('id',$dat['id'])->update([
                    'name'=>trim($dat['name']),
                    'desc'=>isset($dat['desc'])?trim($dat['desc']):'',
                    'back_content'=>isset($dat['back_content'])?$dat['back_content'][0]:'',
                    'back_content2'=>isset($dat['back_content'])?$dat['back_content'][0]:'',
                    'go_other'=>intval($dat['go_other']),
                    'other_goods'=>$dat['go_other']==2?intval($dat['other_goods']):0,
                    'link'=>$dat['go_other']==4?trim($dat['link']):'',
                    'other_navbar'=>$dat['go_other']==3?intval($dat['other_navbar']):0,
                    'gkeywords'=>$dat['go_other']==5?trim($dat['gkeywords']):'',
                    'other_pic'=>$dat['go_other']==8?intval($dat['other_pic']):'',
                    'other_msg'=>$dat['go_other']==9?intval($dat['other_msg']):'',
                ]);
            }
            else{
                $res = Db::connect($this->config)->name('guide_content')->insert([
                    'system_id'=>$system_id,
                    'pid'=>$pid,
                    'top_id'=>$top_id,
                    'name'=>trim($dat['name']),
                    'desc'=>isset($dat['desc'])?trim($dat['desc']):'',
                    'back_type'=>2,
                    'back_content'=>isset($dat['back_content'])?$dat['back_content'][0]:'',
                    'back_content2'=>isset($dat['back_content'])?$dat['back_content'][0]:'',
                    'go_other'=>intval($dat['go_other']),
                    'other_goods'=>$dat['go_other']==2?intval($dat['other_goods']):0,
                    'link'=>$dat['go_other']==4?trim($dat['link']):'',
                    'other_navbar'=>$dat['go_other']==3?intval($dat['other_navbar']):0,
                    'gkeywords'=>$dat['go_other']==5?trim($dat['gkeywords']):'',
                    'other_pic'=>$dat['go_other']==8?intval($dat['other_pic']):'',
                    'other_msg'=>$dat['go_other']==9?intval($dat['other_msg']):'',
                ]);
            }

            if($res){
//                if($dat['go_other']==5 && !empty($dat['gkeywords'])) {
//                    $this->save_keywords($this->config,explode('、',trim($dat['gkeywords'])));
//                }
                return json(['code' => 0, 'msg' => '保存成功']);
            }
            else{
                return json(['code' => -1, 'msg' => '暂无修改']);
            }

        }else{
            $data = ['name'=>'','desc'=>'','type'=>0,'img_name'=>'','back_type'=>0,'back_content'=>'','gkeywords'=>'','go_other'=>0,'link'=>'','other_navbar'=>'','other_goods'=>'','other_pic'=>'','other_msg'=>''];
            if($id>0){
                $data = Db::connect($this->config)->name('guide_content')->where('id',$id)->find();
            }

            #上级->样式
            $website_index = Db::name('website_index')->where(['id'=>$pid])->field('format_type')->find();

            #导流方式
            #1、菜单
            $type['list'] = $this->menu($system_id);
            #2、商品
            $type['goods'] = [];
            #3、图文链接
            $type['pic_list'] = Db::name('website_image_txt')->select();
            foreach($type['pic_list'] as $k=>$v){
                $type['pic_list'][$k]['name'] = json_decode($v['name'],'true')['zh'];
            }
            #消息链接
            $type['msg_list'] = Db::name('website_message_manage')->select();

            return view('',compact('id','data','company_id','company_type','type','guide_info','guide_id','top_id','system_id','pid','website_index'));
        }
    }

    //删除内容
    public function del_home_content(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;

        $res = Db::connect($this->config)->name('guide_content')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    //杂志内容管理
    public function home_content2(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;
        $guide_id = isset($dat['guide_id'])?intval($dat['guide_id']):0;
        $top_id = isset($dat['top_id'])?intval($dat['top_id']):0;

        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');

        if( request()->isPost() || request()->isAjax()){

            $count = Db::connect($this->config)->name('guide_content')->where(['system_id'=>$system_id,'company_id'=>0,'company_type'=>0,'top_id'=>$top_id])->count();
            $rows = Db::connect($this->config)->name('guide_content')->where(['system_id'=>$system_id,'company_id'=>0,'company_type'=>0,'top_id'=>$top_id])->limit($limit)->order('id desc')->select();

            $go_method_name = ['无','分享插件','商品详情','网站菜单','网址链接','搜索结果','我要咨询','去找客服','图文链接','消息链接'];
            foreach ($rows as &$item) {
                $item['go_method'] = $go_method_name[$item['go_other']];
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            #模块信息-版式
            $guide_info = Db::connect($this->config)->name('merchsite_guide_body')->where(['id'=>$guide_id])->find();

            return view('',compact('system_id','pid','guide_id','guide_info','top_id'));
        }
    }

    #保存杂志内容
    public function save_home_content2(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        $top_id = isset($dat['top_id'])?$dat['top_id']:0;
        $guide_id = isset($dat['guide_id'])?intval($dat['guide_id']):0;

        $guide_info = Db::connect($this->config)->name('merchsite_guide_body')->where(['id'=>$guide_id])->find();

        if($request->isAjax()){
            if($id>0){
                $res = Db::connect($this->config)->name('guide_content')->where('id',$dat['id'])->update([
                    'name'=>trim($dat['name']),
                    'back_content'=>isset($dat['back_content'])?$dat['back_content'][0]:'',
                    'back_content2'=>isset($dat['back_content'])?$dat['back_content'][0]:'',
                    'go_other'=>intval($dat['go_other']),
                    'other_goods'=>$dat['go_other']==2?intval($dat['other_goods']):0,
                    'link'=>$dat['go_other']==4?trim($dat['link']):'',
                    'other_navbar'=>$dat['go_other']==3?intval($dat['other_navbar']):0,
                    'gkeywords'=>isset($dat['gkeywords'])?trim($dat['gkeywords']):'',
                    'other_pic'=>$dat['go_other']==8?intval($dat['other_pic']):'',
                    'other_msg'=>$dat['go_other']==9?intval($dat['other_msg']):'',
                    'desc'=>isset($dat['desc'])?trim($dat['desc']):'',
                ]);
            }
            else{
                $res = Db::connect($this->config)->name('guide_content')->insert([
                    'system_id'=>$system_id,
                    'pid'=>$pid,
                    'top_id'=>$top_id,
                    'name'=>trim($dat['name']),
                    'back_type'=>2,
                    'back_content'=>isset($dat['back_content'])?$dat['back_content'][0]:'',
                    'back_content2'=>isset($dat['back_content'])?$dat['back_content'][0]:'',
                    'go_other'=>intval($dat['go_other']),
                    'other_goods'=>$dat['go_other']==2?intval($dat['other_goods']):0,
                    'link'=>$dat['go_other']==4?trim($dat['link']):'',
                    'other_navbar'=>$dat['go_other']==3?intval($dat['other_navbar']):0,
                    'gkeywords'=>isset($dat['gkeywords'])?trim($dat['gkeywords']):'',
                    'other_pic'=>$dat['go_other']==8?intval($dat['other_pic']):'',
                    'other_msg'=>$dat['go_other']==9?intval($dat['other_msg']):'',
                    'desc'=>isset($dat['desc'])?trim($dat['desc']):'',
                ]);
            }

            if($res){
//                if($dat['go_other']==5 && !empty($dat['gkeywords'])) {
//                    $this->save_keywords($this->config,explode('、',trim($dat['gkeywords'])));
//                }
                return json(['code' => 0, 'msg' => '保存成功']);
            }
            else{
                return json(['code' => -1, 'msg' => '暂无修改']);
            }

        }else{
            $data = ['name'=>'','type'=>0,'img_name'=>'','desc'=>'','back_type'=>0,'back_content'=>'','gkeywords'=>'','go_other'=>0,'link'=>'','other_navbar'=>'','other_goods'=>'','other_pic'=>'','other_msg'=>''];
            if($id>0){
                $data = Db::connect($this->config)->name('guide_content')->where('id',$id)->find();
            }

            #导流方式
            #1、菜单
            $type['list'] = $this->menu($system_id);
            #2、商品
            $type['goods'] = [];
            #3、图文链接
            $type['pic_list'] = Db::name('website_image_txt')->select();
            foreach($type['pic_list'] as $k=>$v){
                $type['pic_list'][$k]['name'] = json_decode($v['name'],'true')['zh'];
            }
            #消息链接
            $type['msg_list'] = Db::name('website_message_manage')->select();

            return view('',compact('id','data','company_id','company_type','type','guide_info','guide_id','top_id','system_id','pid'));
        }
    }

    #菜单栏目
    public function menu($system_id){
        $menu = Db::name('website_navbar')->where(['system_id'=>$system_id,'pid'=>0,'company_id'=>0,'company_type'=>0])->field('id,name')->select();
        foreach($menu as $k=>$v){
            $menu[$k]['name'] = json_decode($v['name'],true)['zh'];
            $menu[$k]['childMenu'] = $this->getDownMenu($v['id']);
        }
        return $menu;
    }

    #下级菜单
    public function getDownMenu($id){
        $cmenu = Db::name('website_navbar')->where(['pid'=>$id])->field('id,name')->select();
        foreach($cmenu as $k=>$v){
            $cmenu[$k]['name'] = json_decode($v['name'],true)['zh'];
            $cmenu[$k]['childMenu'] = Db::name('website_navbar')->where(['pid'=>$v['id']])->field('id,name')->select();
            foreach($cmenu[$k]['childMenu'] as $k2=>$v2){
                $cmenu[$k]['childMenu'][$k2]['name'] = json_decode($v2['name'],true)['zh'];
                $cmenu[$k]['childMenu'][$k2]['childMenu'] = Db::name('website_navbar')->where(['pid'=>$v2['id']])->field('id,name')->select();
                foreach($cmenu[$k]['childMenu'][$k2]['childMenu'] as $k3=>$v3){
                    $cmenu[$k]['childMenu'][$k2]['childMenu'][$k3]['name'] = json_decode($v3['name'],true)['zh'];
                }
            }
        }
        return $cmenu;
    }

    #菜单栏目-xmselect树形结构
    public function menu2($typ=0,$system_id=0){
        if($system_id==0){
            $menu = Db::name('website_navbar')->where(['pid'=>0])->field('id,name')->select();
        }else{
            $menu = Db::name('website_navbar')->where(['pid'=>0,'system_id'=>$system_id,'company_type'=>0,'company_id'=>0])->field('id,name')->select();
        }

        foreach($menu as $k=>$v){
            $menu[$k]['name'] = json_decode($v['name'],true)['zh'];
            $menu[$k]['value'] = $v['id'];
            if($typ==1){
                $menu[$k]['children'] = $this->getDownMenu3($v['id']);  
            }
            elseif($typ==2){
                $menu[$k]['children'] = [];
            }
            else{
                $menu[$k]['children'] = $this->getDownMenu2($v['id']);    
            }
        }
        return $menu;
    }

    #下级菜单
    public function getDownMenu2($id){
        $cmenu = Db::name('website_navbar')->where(['pid'=>$id])->field('id,name')->select();
        foreach($cmenu as $k=>$v){
            $cmenu[$k]['name'] = json_decode($v['name'],true)['zh'];
            $cmenu[$k]['value'] = $v['id'];
            $cmenu[$k]['children'] = Db::name('website_navbar')->where(['pid'=>$v['id']])->field('id,name')->select();
            foreach($cmenu[$k]['children'] as $k2=>$v2){
                $cmenu[$k]['children'][$k2]['name'] = json_decode($v2['name'],true)['zh'];
                $cmenu[$k]['children'][$k2]['value'] = $v2['id'];
                $cmenu[$k]['children'][$k2]['children'] = Db::name('website_navbar')->where(['pid'=>$v2['id']])->field('id,name')->select();
                foreach($cmenu[$k]['children'][$k2]['children'] as $k3=>$v3){
                    $cmenu[$k]['children'][$k2]['children'][$k3]['name'] = json_decode($v3['name'],true)['zh'];
                    $cmenu[$k]['children'][$k2]['children'][$k3]['value'] = $v3['id'];
                    $cmenu[$k]['children'][$k2]['children'][$k3]['children'] = Db::name('website_navbar')->where(['pid'=>$v3['id']])->field('id,name')->select();
                    foreach($cmenu[$k]['children'][$k2]['children'][$k3]['children'] as $k4=>$v4){
                        $cmenu[$k]['children'][$k2]['children'][$k3]['children'][$k4]['name'] = json_decode($v4['name'],true)['zh'];
                        $cmenu[$k]['children'][$k2]['children'][$k3]['children'][$k4]['value'] = $v4['id'];
                    }
                }
            }
        }
        return $cmenu;
    }
    
    #不要最下一层的菜单
    public function getDownMenu3($id){
        $cmenu = Db::name('website_navbar')->where(['pid'=>$id])->field('id,name')->select();
        foreach($cmenu as $k=>$v){
            $cmenu[$k]['name'] = json_decode($v['name'],true)['zh'];
            $cmenu[$k]['value'] = $v['id'];
        }
        return $cmenu;
    }

    public function save_home(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;

        if($request->isAjax()){
            $format = 0;

//            $connect_menus = explode(',',$dat['select'])[0];
            $connect_menus = $dat['navbar_id'];
            if(empty($connect_menus)){
                return json(['code'=>-1, 'msg'=>'请选择板块']);
            }

            if (is_numeric($connect_menus)) {
                $have_child = Db::name('website_navbar')->where('pid', $connect_menus)->find();

                if (empty($have_child)) {
                    if($system_id != 3){
                        return json(['code' => -1, 'msg' => '请选择有下级菜单的板块！']);
                    }else{
                        #淘中国
                        $format = 2;
                    }
                } else {
                    $format = 2;
                    $have_child = Db::name('website_navbar')->where('pid', $have_child['id'])->find();
                    if (!empty($have_child)) {
                        $format = 1;
                    }
                }
            }
            if($system_id==3){
                if($dat['format_type'] == 3){
                    #当选择了“发现管理样式”后，就不需要标题和描述了
                    $format = 0;
                }
            }

            $content = [];#切换框内容
            $btn_content = [];#按键自定义内容
            $fq_content = [];#标题+描述折叠框内容
            $fq_category_content = [];#一问一答图文内容
            $card1_content = [];#卡片1样式内容
            $card2_content = [];#卡片2样式内容
            $card3_content = [];#卡片3样式内容
            if($dat['format_type']==2){
                foreach($dat['ficon'] as $k=>$v){
                    array_push($content,['fnavbar'=>intval($dat['fnavbar'][$k]),'ficon'=>trim($v),'ftitle'=>trim($dat['ftitle'][$k]),'fdesc'=>trim($dat['fdesc'][$k])]);
                }

                foreach($dat['btn_title'] as $k=>$v){
                    array_push($btn_content,['btn_navbar'=>intval($dat['btn_navbar'][$k]),'btn_title'=>trim($v),'go_other'=>intval($dat['go_other'][$k]),'other_link'=>trim($dat['other_link'][$k]),'other_navbar'=>intval($dat['other_navbar'][$k]),'other_pic'=>intval($dat['other_pic'][$k]),'other_msg'=>intval($dat['other_msg'][$k]),'other_keywords'=>trim($dat['other_keywords'][$k])]);
                }
            }elseif($dat['format_type']==4){
                foreach($dat['fq_title'] as $k=>$v){
                    array_push($fq_content,['fq_title'=>trim($v),'fq_desc'=>trim($dat['fq_desc'][$k])]);
                }
            }elseif($dat['format_type']==5){
                foreach($dat['fq_ctitle'] as $k=>$v){
                    array_push($fq_category_content,['fq_ctitle'=>trim($v),'fq_ids'=>trim($dat['fq_ids'][$k])]);
                }
            }elseif($dat['format_type']==6){
                foreach($dat['card1_title'] as $k=>$v){
                    array_push($card1_content,['card1_title'=>trim($v),'card1_desc'=>trim($dat['card1_desc'][$k]),'card1_img'=>trim($dat['card1_img'][$k])]);
                }
            }elseif($dat['format_type']==7){
                foreach($dat['card2_title'] as $k=>$v){
                    array_push($card2_content,['card2_title'=>trim($v),'card2_icon'=>trim($dat['card2_icon'][$k])]);
                }
            }elseif($dat['format_type']==8){
                foreach($dat['card3_title'] as $k=>$v){
                    array_push($card3_content,['card3_title'=>trim($v),'card3_desc'=>trim($dat['card3_desc'][$k]),'card3_img'=>trim($dat['card3_img'][$k])]);
                }
            }

            if($id>0){
                Db::name('website_index')->where('id',$id)->update([
                    'navbar_id'=>$connect_menus,
                    'displayorder'=>intval($dat['displayorder2']),
                    'format'=>$format,
                    'format_type'=>intval($dat['format_type']),
                    'content'=>$dat['format_type']==2?json_encode($content,true):'',
                    'btn_content'=>$dat['format_type']==2?json_encode($btn_content,true):'',
                    'fq_content'=>$dat['format_type']==4?json_encode($fq_content,true):'',
                    'bg_type'=>$dat['bg_type'],
                    'bg_color'=>$dat['bg_type']==1?trim($dat['bg_color']):'',
                    'bg_img'=>$dat['bg_type']==2?trim($dat['bg_img'][0]):'',
                    'fq_category_content'=>$dat['format_type']==5?json_encode($fq_category_content,true):'',
                    'card1_content'=>$dat['format_type']==6?json_encode($card1_content,true):'',
                    'card2_content'=>$dat['format_type']==7?json_encode($card2_content,true):'',
                    'card3_content'=>$dat['format_type']==8?json_encode($card3_content,true):'',
                    'gkeywords'=>isset($dat['gkeywords'])?trim($dat['gkeywords']):'',
                    'bind_festival'=>isset($dat['bind_festival'])?trim($dat['bind_festival']):'',
                    'supply_show'=>isset($dat['supply_show'])?intval($dat['supply_show']):'',
                    'api_merchant'=>isset($dat['api_merchant'])?trim($dat['api_merchant']):'',
                    'buyer_merchant'=>isset($dat['buyer_merchant'])?trim($dat['buyer_merchant']):'',
                    'shop_merchant'=>isset($dat['shop_merchant'])?trim($dat['shop_merchant']):'',
//                    'jizhi_desc'=>isset($dat['jizhi_desc'])?json_encode($dat['jizhi_desc'],true):'',
//                    'thumb'=>$dat['format_type']==2?$dat['thumb'][0]:''
                ]);
            }else{
                Db::name('website_index')->insert([
                    'system_id'=>$system_id,
                    'navbar_id'=>$connect_menus,
                    'displayorder'=>intval($dat['displayorder2']),
                    'format'=>$format,
                    'format_type'=>intval($dat['format_type']),
                    'content'=>$dat['format_type']==2?json_encode($content,true):'',
                    'btn_content'=>$dat['format_type']==2?json_encode($btn_content,true):'',
                    'fq_content'=>$dat['format_type']==4?json_encode($fq_content,true):'',
                    'bg_type'=>$dat['bg_type'],
                    'bg_color'=>$dat['bg_type']==1?trim($dat['bg_color']):'',
                    'bg_img'=>$dat['bg_type']==2?trim($dat['bg_img'][0]):'',
                    'fq_category_content'=>$dat['format_type']==5?json_encode($fq_category_content,true):'',
                    'card1_content'=>$dat['format_type']==6?json_encode($card1_content,true):'',
                    'card2_content'=>$dat['format_type']==7?json_encode($card2_content,true):'',
                    'card3_content'=>$dat['format_type']==8?json_encode($card3_content,true):'',
                    'gkeywords'=>isset($dat['gkeywords'])?trim($dat['gkeywords']):'',
                    'bind_festival'=>isset($dat['bind_festival'])?trim($dat['bind_festival']):'',
                    'supply_show'=>isset($dat['supply_show'])?intval($dat['supply_show']):'',
                    'api_merchant'=>isset($dat['api_merchant'])?trim($dat['api_merchant']):'',
                    'buyer_merchant'=>isset($dat['buyer_merchant'])?trim($dat['buyer_merchant']):'',
                    'shop_merchant'=>isset($dat['shop_merchant'])?trim($dat['shop_merchant']):'',
//                    'jizhi_desc'=>isset($dat['jizhi_desc'])?json_encode($dat['jizhi_desc'],true):'',
//                    'thumb'=>$dat['format_type']==2?$dat['thumb'][0]:''
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功！']);
        }else{
            $data = ['navbar_id'=>'','displayorder'=>'','format_type'=>1,'gkeywords'=>'','bg_type'=>1,'bg_color'=>'','bg_img'=>'','fq_ids'=>'','format_img'=>'','bind_festival'=>'','supply_show'=>0,'api_merchant'=>'','buyer_merchant'=>'','shop_merchant'=>'','jizhi_desc'=>[]];
            $cmenu = [];

            if($id>0){
                $data = Db::name('website_index')->where('id',$id)->find();

                $cmenu = Db::name('website_navbar')->where(['pid'=>$data['navbar_id']])->field('id,name')->select();
                foreach($cmenu as $k=>$v){
                    $cmenu[$k]['name'] = json_decode($v['name'],true)['zh'];
                }

                $format = Db::name('website_format_list')->where(['id'=>$data['format_type']])->field('img,jizhi_desc')->find();
                $data['format_img'] = $format['img'];
                $data['jizhi_desc'] = json_decode($format['jizhi_desc'],true);

//                if($system_id==3){
//                    if(!empty($data['jizhi_desc'])){
//                        $data['jizhi_desc'] = json_decode($data['jizhi_desc'],true);
//                    }
//                }
            }

            # 一问一答图文内容
            $fq_list = Db::name('website_image_txt')->select();
            foreach($fq_list as $k=>$v){
                $fq_list[$k]['name'] = json_decode($v['name'],true)['zh'];
                $fq_list[$k]['value'] = $fq_list[$k]['id'];
                $fq_list[$k]['children'] = [];
            }
            $fq_list = json_encode($fq_list,true);

            // $list = $this->menu();
            $typ = 1;
            if($system_id==1 || $system_id==3 || $system_id==8 || $system_id==9){
                #购购官网 和 淘中国商城 和 AIGOGO 和 独立站website
                $typ = 2;

                $list = $this->menu2($typ,$system_id);

                #针对系统添加内容
                array_push($list,['id'=>'A1','name'=>'发现轮播+信息切换框','value'=>'A1','children'=>[]]);
                array_push($list,['id'=>'A2','name'=>'常见问题','value'=>'A2','children'=>[]]);
                $list = json_encode($list,true);

                if($id>0){
                    if($data['format_type']==2){
                        // 左右图文切换框
                        $data['content'] = json_decode($data['content'],true);
                        foreach($data['content'] as $k=>$v){
                            $navbar_name = Db::name('website_navbar')->where(['id'=>$v['fnavbar']])->field('name')->find()['name'];
                            $data['content'][$k]['fnavbar_name'] = json_decode($navbar_name,true)['zh'];
                        }
                        #按键内容
                        $data['btn_content'] = json_decode($data['btn_content'],true);
                        foreach($data['btn_content'] as $k=>$v){
                            $navbar_name = Db::name('website_navbar')->where(['id'=>$v['btn_navbar']])->field('name')->find()['name'];
                            $data['btn_content'][$k]['btn_navbar_name'] = json_decode($navbar_name,true)['zh'];
                        }
                    }
                    elseif($data['format_type']==4){
                        // 标题+描述折叠框
                        $data['fq_content'] = json_decode($data['fq_content'],true);
                    }
                    elseif($data['format_type']==5){
                        // 一问一答图文内容
                        $data['fq_category_content'] = json_decode($data['fq_category_content'],true);
                    }
                    elseif($data['format_type']==6){
                        $data['card1_content'] = json_decode($data['card1_content'],true);
                    }
                    elseif($data['format_type']==7){
                        $data['card2_content'] = json_decode($data['card2_content'],true);
                    }
                    elseif($data['format_type']==8){
                        $data['card3_content'] = json_decode($data['card3_content'],true);
                    }
                }
            }else{
                $list = json_encode($this->menu2($typ,$system_id),true);
            }

            $btn_list='';
            if($system_id==1 || $system_id==4 || $system_id==3 || $system_id==8){
                $btn_list = $this->menu($system_id);
            }elseif($system_id==2){
                $btn_list = $this->get_gather_process();
            }

            #图文链接
            $pic_list = Db::name('website_image_txt')->select();
            foreach($pic_list as $k=>$v){
                $pic_list[$k]['name'] = json_decode($v['name'],'true')['zh'];
            }
            #消息链接
            $msg_list = Db::name('website_message_manage')->select();

            #版式集
            $format_list = Db::name('website_format_list')->select();
            if($id==0){
                $data['format_img'] = Db::name('website_format_list')->where(['id'=>$format_list[0]['id']])->field('img')->find()['img'];
            }

            #各商家
            $merchant_list = ['api_merchant'=>[],'buyer_merchant'=>[],'shop_merchant'=>[]];
            if($system_id==3){
                #接口商
                $merchant_list['api_merchant'] = Db::name('website_other_merch')
                    ->alias('wom')
                    ->join('website_user_company wuc','wom.id = wuc.om_id','left')
                    ->where(['wom.type'=>2])
                    ->field('wuc.*')
                    ->select();
                $merchant_list['api_merchant'] = json_encode($merchant_list['api_merchant'],true);

                #买手商
                $merchant_list['buyer_merchant'] = Db::name('website_buyer')->whereRaw('is_verify=1 and verify_type<>0 and company_id<>0')->select();
                $merchant_list['buyer_merchant'] = json_encode($merchant_list['buyer_merchant'],true);

                #店铺商
                $merchant_list['shop_merchant'] = Db::name('website_user_company')->whereRaw('is_verify=1 and status=0 and om_id=0 and type=2 and (type2=4 or type2=5)')->select();
                $merchant_list['shop_merchant'] = json_encode($merchant_list['shop_merchant'],true);
            }

            return view('',compact('data','id','list','system_id','cmenu','fq_list','btn_list','pic_list','msg_list','format_list','merchant_list'));
        }
    }

    public function get_format_type(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        $img = Db::name('website_format_list')->where(['id'=>$id])->field('img,jizhi_desc')->find();
        $img['jizhi_desc'] = json_decode($img['jizhi_desc'],true);

        return json(['code'=>0,'img'=>$img['img'],'jizhi_desc'=>$img['jizhi_desc']]);
    }

    #获取当前菜单的下级菜单们
    public function get_nextNavbar(Request $request){
        $dat = input();

        $id = intval($dat['id']);

        $cmenu = [];
        if($id>0){
            $cmenu = Db::name('website_navbar')->where(['pid'=>$id])->field('id,name')->select();
            foreach($cmenu as $k=>$v){
                $cmenu[$k]['name'] = json_decode($v['name'],true)['zh'];
            }
        }

        return json(['code'=>0,'list'=>$cmenu]);
    }

    public function del_home(Request $request){
        $id = input()['id'];
        Db::name('website_index')->where('id',$id)->delete();
        return json(['code' => 0, 'msg' => '已删除']);
    }

    public function message_manage(Request $request){
        $dat = input();
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){

            $count = Db::name('website_message')->order($order)->count();
            $rows = DB::name('website_message')
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>$v){
                $rows[$k]['menu_name'] = Db::name('website_navbar')->where('id',$v['pid'])->find()['name'];
                $rows[$k]['menu_name'] = json_decode($rows[$k]['menu_name'],true)['zh'];
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('');
        }
    }

    #账户管理
    public function website_user(Request $request){
        $dat = input();
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            if(isset($dat['pa'])){
                $company = Db::name('website_user_company')->where(['id'=>intval($dat['id'])])->find();
                Db::name('website_user_company')->where(['id'=>intval($dat['id'])])->update(['is_verify'=>1,'status'=>0]);
                $res = Db::name('website_user')->where(['id'=>intval($company['user_id'])])->update(['merch_status'=>2,'is_verify'=>1]);
                $user = Db::name('website_user')->where(['id'=>intval($company['user_id'])])->find();
                #通知商户
                if(!empty($user['email'])){
                    httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$user['email'],'title'=>'商户认证','content'=>'尊敬的商户，您好！您已通过商户认证，感谢您的支持！']);
                }elseif(!empty($user['phone'])){
                    $post_data = [
                        'mobiles'=>$user['phone'],
                        'content'=>'尊敬的商户，您好！您已通过商户认证，感谢您的支持！【GOGO】',
                    ];
                    $post_data = json_encode($post_data,true);
                    httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                        'Content-Type: application/json; charset=utf-8',
                        'Content-Length:' . strlen($post_data),
                        'Cache-Control: no-cache',
                        'Pragma: no-cache'
                    ));
                }

                if($res){
                    return json(['code'=>0,'msg'=>'确认审批成功']);
                }
            }else{

                $count = Db::name('website_user')->where(['merch_status'=>0])->order($order)->count();
                $rows = DB::name('website_user')
                    ->where(['merch_status'=>0])
                    ->limit($limit)
                    ->order($order)
                    ->select();

                $merch_status = [0=>'未认证',1=>'已认证待审批',2=>'已认证已审批'];
                foreach($rows as $k=>$v){
//                    if($v['reg_method']==1){
//                        $rows[$k]['reg_method']='中国境内';
//                    }else{
//                        $rows[$k]['reg_method']='中国境外';
//                    }

                    $rows[$k]['merch_status']=$merch_status[$v['merch_status']];

                    $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
                }

                return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
            }
        }else{
            return view('');
        }
    }

    #配置为商户
    public function set_usermerch(Request $request){
        $dat = input();
        if(intval($dat['id'])>0){
            $res = Db::name('website_user')->where(['id'=>intval($dat['id'])])->update([
                'merch_status'=>2,
                'agent_status'=>2
            ]);

            if($res){
                return json(['code'=>0,'msg'=>'配置成功']);
            }
        }
    }

    #注册商户列表
    public function website_merch(Request $request){
        $dat = input();
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            if(isset($dat['pa'])){
                $res = Db::name('website_user')->where(['id'=>intval($dat['id'])])->update(['merch_status'=>2,'is_verify'=>1]);
                $user = Db::name('website_user')->where(['id'=>intval($dat['id'])])->find();
                #通知商户
                if(!empty($user['email'])){
                    httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$user['email'],'title'=>'商户认证','content'=>'尊敬的商户，您好！您已通过GOGO商户认证，感谢您的支持！']);
                }elseif(!empty($user['phone'])){
                    $post_data = [
                        'spid'=>'254560',
                        'password'=>'J6Dtc4HO',
                        'ac'=>'1069254560',
                        'mobiles'=>$user['phone'],
                        'content'=>'尊敬的商户，您好！您已通过GOGO商户认证，感谢您的支持！【GOGO】',
                    ];
                    $post_data = json_encode($post_data,true);
                    httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                        'Content-Type: application/json; charset=utf-8',
                        'Content-Length:' . strlen($post_data),
                        'Cache-Control: no-cache',
                        'Pragma: no-cache'
                    ));
                }

                if($res){
                    return json(['code'=>0,'msg'=>'确认审批成功']);
                }
            }else{

                $count = Db::name('website_user')->where('merch_status','<>',0)->order($order)->count();
                $rows = DB::name('website_user')
                    ->where('merch_status','<>',0)
                    ->limit($limit)
                    ->order($order)
                    ->select();

                $merch_status = [0=>'未认证',1=>'已认证待审批',2=>'已认证已审批'];
                foreach($rows as $k=>$v){
                    $rows[$k]['merch_status']=$merch_status[$v['merch_status']];
                    $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
                }

                return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
            }
        }else{
            return view('');
        }
    }
    #接口商户列表
    public function diy_merch(Request $request){
        $dat = input();
        $type = intval($dat['type']);
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('website_other_merch')->where(['type'=>$type])->order($order)->count();
            $rows = DB::name('website_other_merch')
                ->where(['type'=>$type])
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>$v){
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('type'));
        }
    }
    #添加接口商户
    public function save_diy_merch(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $type = isset($dat['type'])?$dat['type']:0;
        if( request()->isPost() || request()->isAjax()){
            if(empty($id)){
                $om_id = Db::name('website_other_merch')->insertGetId([
                    'name'=>trim($dat['name']),
                    'type'=>intval($dat['type']),
                    'link'=>trim($dat['link']),
                    'apikey'=>$dat['type']==2?trim($dat['apikey']):'',
                    'createtime'=>time()
                ]);

                $time = time();
                //生成用户
                $is_have_user = Db::name('website_user')->where(['realname'=>trim($dat['name'])])->find();
                $user_id = 0;
                if(empty($is_have_user)){
                    $arr = ['phone'=>'', 'sns_openid'=>'', 'area_code'=>162, 'unionid'=>'','realname'=>trim($dat['name'])];
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://shop.gogo198.cn/collect_website/public/?s=api/func/generate_member"); // 目标URL
                    curl_setopt($ch, CURLOPT_POST, 1); // 设置为POST请求
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $arr); // POST数据
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 将响应结果作为字符串返回
                    $user_id = curl_exec($ch);
                    if (curl_errno($ch)) {
                        echo 'Error:' . curl_error($ch);die;
                    }
                    curl_close($ch);
                }


                //生成商户
                $company_id = Db::name('website_user_company')->insertGetId([
                    'role'=>0,
                    'admin_role'=>0,
                    'authList'=>'',
                    'webList'=>'',
                    'sellerList'=>'',
                    'feeList'=>'',
                    'pids'=>'',
                    'user_id'=>$user_id,
                    'om_id'=>$om_id,
                    'reg_method'=>1,
                    'realname'=>trim($dat['name']),
                    'mobile'=>'',
                    'company'=>trim($dat['name']),
                    'reg_file'=>'',
                    'idcard'=>'',
                    'type'=>2,
                    'type2'=>4,
                    'domain_name'=>'',
                    'is_verify'=>1,
                    'status'=>0,
                    'createtime'=>$time
                ]);

                //生成买手
                $is_have = Db::name('website_buyer')->where(['type'=>1,'company_id'=>$company_id,'name'=>trim($dat['name'])])->find();
                if(empty($is_have)){
                    Db::name('website_buyer')->insert([
                        'type'=>1,
                        'company_id'=>$company_id,
                        'name'=>trim($dat['name']),
                        'api_address'=>trim($dat['link']),
                        'verify_type'=>0,
                        'is_verify'=>1,
                        'createtime'=>$time
                    ]);
                }
            }else{
                Db::name('website_other_merch')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'type'=>intval($dat['type']),
                    'link'=>trim($dat['link']),
                    'apikey'=>$dat['type']==2?trim($dat['apikey']):'',
                ]);
            }
            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>'','phone'=>'','email'=>'','link'=>'','apikey'=>''];
            if($id>0){
                $data = Db::name('website_other_merch')->where(['id'=>$id,'type'=>$type])->find();
            }
            return view('',compact('id','data','type'));
        }
    }
    #删除接口商户
    public function del_diy_merch(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $other_merch = Db::name('website_other_merch')->where(['id'=>$id])->find();
        Db::name('website_other_merch')->where(['id'=>$id])->delete();
        Db::name('website_buyer')->where(['name'=>$other_merch['name']])->delete();

        return json(['code'=>0,'msg'=>'保存成功']);
    }

    #账户管理-海外
    public function baseinfo(Request $request){
        $dat = input();

        $data = Db::name('website_user_company')->where('id',intval($dat['id']))->find();
        $data['reg_file'] = json_decode($data['reg_file'],true);
        return view('',compact('data'));
    }

    #账户管理-关联应用
    public function connect_app(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $dat['auth_type'] = 3;
        if(request()->isPost() || request()->isAjax()){
//            dd($dat);
            $company = Db::name('website_user_company')->where(['id'=>$id])->find();
            $web_user = Db::name('website_user')->where('id',$company['user_id'])->find();
//            'connect_app'=>$dat['connect_app']
            Db::name('website_user_company')->where(['id'=>$dat['id']])->update([
                'admin_role'=>$dat['admin_role'],
                'auth_type'=>intval($dat['auth_type']),
//                'authList'=>rtrim($dat['authList'],','),
//                'webList'=>rtrim($dat['webList'],','),
                'sellerList'=>rtrim($dat['sellerList'],','),
                'servicesList'=>rtrim($dat['servicesList'],','),
                'feeList'=>rtrim($dat['feeList'],','),
                'pids'=>$dat['pids']
            ]);

            #给了商户角色后，生成商户表数据
            if(true){
                #用这个
                $time = time();
                $ishave = Db::name('centralize_manage_person')->where(['tel' => $web_user['phone']])->whereOr('email', $web_user['email'])->find();
                if (empty($ishave)) {
                    Db::name('centralize_manage_person')->insert([
                        'name' => $web_user['realname'],
                        'company_id'=>$company['id'],
                        'agent_type' => 1,
                        'type' => empty($web_user['reg_method'])?1:2,
                        'tel' => $web_user['phone'],
                        'email' => $web_user['email'],
                        'idcard' => $web_user['idcard'],
                        'role_id' => $dat['admin_role'],
                        'status' => 1,
                        'createtime' => $time,
                        'gogo_id' => $web_user['id']
                    ]);
                }else{
                    Db::name('centralize_manage_person')->where(['id'=>$ishave['id']])->update(['role_id'=>$dat['admin_role']]);
                }

                #商城管理权限
                $ishave_shopuser = Db::connect($this->config)->name('user')->where(['gogo_id' => $web_user['custom_id']])->find();
                if ($ishave_shopuser['is_seller'] == 0) {
                    $shopid = Db::connect($this->config)->name('shop')->insertGetId([
                        'user_id' => $company['id'],#绑定企业id
                        'shop_name' => $company['company'] . '的店铺',
                        'shop_type' => 2,
                        'cat_id' => 1,
                        'open_time' => $time,
                        'goods_status' => 1,
                        'shop_status' => 1,
                        'service_tel' => $web_user['phone'],
                        'created_at' => date('Y-m-d H:i:s', $time)
                    ]);
                    Db::connect($this->config)->name('user')->where(['user_id' => $ishave_shopuser['user_id']])->update([
                        'user_name'=> empty($ishave_shopuser['user_name'])?$web_user['realname']:$web_user['phone'],
                        'role_id' => 1,
                        'rank_id' => 1,
                        'is_seller' => 1,
                        'shop_id' => $shopid,
                        'reg_from' => 1,
                        'security_level' => 2,
                    ]);
                    Db::connect($this->config)->name('image_dir')->insert([
                        'shop_id'=>$shopid,
                        'dir_name'=>'默认相册',
                        'dir_group'=>'shop',
                        'is_default'=>1,
                        'created_at'=>date('Y-m-d H:i:s',$time)
                    ]);
                }
            }
            else{
                #废弃
                $app = explode(',',$dat['connect_app']);
                $time = time();
                Db::startTrans();
                try {
                    foreach ($app as $k => $v) {
                        if ($v == 1 || $v == 2) {
                            #报关系统|结算系统
                            $mobinfo=1;
                            if (!empty($web_user['phone'])) {
                                $ishave = Db::name('decl_user')->where(['user_tel' => $web_user['phone']])->find();
                            } elseif (!empty($web_user['email'])) {
                                $mobinfo=2;
                                $ishave = Db::name('decl_user')->where(['user_email' => $web_user['email']])->find();
                            }

                            if(empty($web_user['nickname'])){
                                $web_user['nickname'] = $mobinfo==1?$web_user['phone']:$web_user['email'];
                            }

                            if(empty($web_user['realname'])){
                                $web_user['realname'] = $mobinfo==1?$web_user['phone']:$web_user['email'];
                            }

                            if (empty($ishave)) {
                                $em_id = Db::name('enterprise_members')->insertGetId([
                                    'uniacid' => 3,
                                    'nickname' => $web_user['nickname'],
                                    'realname' => $web_user['realname'],
                                    'mobile' => $web_user['phone'],
                                    'reg_type' => 1,
                                    'create_at' => $time,
                                    'is_verify' => 1
                                ]);
                                Db::name('enterprise_basicinfo')->insertGetId([
                                    'member_id' => $em_id,
                                    'name' => $web_user['nickname'],
                                    'operName' => $web_user['realname'],
                                    'orgNo' => '',
                                    'create_at' => $time,
                                ]);
                                $unique_id = '';
                                if (!empty($web_user['phone'])) {
                                    $unique_id = md5($web_user['phone'] . date('YmdHis'));
                                } elseif (!empty($web_user['email'])) {
                                    $unique_id = md5($web_user['email'] . date('YmdHis'));
                                }
                                Db::name('total_merchant_account')->insert([
                                    'unique_id' => $unique_id,
                                    'mobile' => $web_user['phone'],
                                    'password' => password_hash('888888', PASSWORD_DEFAULT),
                                    'uniacid' => 3,
                                    'user_name' => $web_user['realname'] != '' ? $web_user['realname'] : $web_user['nickname'],
                                    'company_name' => $web_user['company'],
                                    'create_time' => $time,
                                    'desc' => '',
                                    'status' => 0,
                                    'user_email' => $web_user['email'],
                                    'address' => '',
                                    //'address'=>$basic_info['address'],
                                    'company_tel' => '',
                                    'account_type' => 2,
                                    'openid' => '',
                                    'enterprise_id' => $em_id
                                ]);
                                $uid = Db::name('users')->insertGetId([
                                    'groupid' => 0,
                                    'username' => !empty($web_user['realname']) ? $web_user['realname'] : $web_user['nickname'],
                                    'password' => "931f68875a20dd0d6d6a91711b856c7b9f1263a0",
                                    'salt' => "fF42Q83f",
                                    'type' => 0,
                                    'status' => 2,
                                    'joindate' => $time,
                                    'joinip' => '127.0.0.1',
                                    'lastvisit' => $time,
                                    'lastip' => '127.0.0.1',
                                    'remark' => '',
                                    'starttime' => 0,
                                    'endtime' => 0,
                                ]);
                                Db::name('sz_yi_perm_user')->insert([
                                    'uniacid' => 3,
                                    'uid' => $uid,
                                    'username' => !empty($web_user['phone']) ? $web_user['phone'] : $web_user['email'],
                                    'password' => "931f68875a20dd0d6d6a91711b856c7b9f1263a0",
                                    'roleid' => 1,
                                    'status' => 1,
                                    'realname' => !empty($web_user['realname']) ? $web_user['realname'] : $web_user['nickname'],
                                    'openid' => '',
                                    'mobile' => !empty($web_user['phone']) ? $web_user['phone'] : $web_user['email']
                                ]);
                                Db::name('decl_user')->insert([
                                    'user_name' => $web_user['realname'] != '' ? $web_user['realname'] : $web_user['nickname'],
                                    'user_tel' => $web_user['phone'],
                                    'user_email' => $web_user['email'],
                                    'user_password' => md5('888888'),
                                    'uniacid' => 3,
                                    'plat_id' => 1,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'user_status' => 0,
                                    'buss_id' => 3,
                                    'company_name' => $web_user['company'],
                                    'company_num' => '',#不知道是什么
                                    'address' => '',
                                    'supplier' => $uid,
                                    'menus' => '1,2,3,6,21,22,54,7,8,9,23,24,47,55,56,65,66,10,11,12,13,14,39,40,41,42,43,86,15,16,17,30,31,18,32,33,57,19,20,28,25,26,27,29,51,52,53,252,34,35,36,38,37,44,46,48,235,105,201,211,202,212,213,214,203,215,204,216,217,218,219,205,220,221,106,206,222,207,223,224,225,208,226,209,227,228,229,230,210,231,232,253,254,64,72,127,67,69,128,74,75,77,134,135,129,73,76,130,131,233,132,136,137,138,133,78,79,139,82,87,88,83,94,140,146,168,169,170,147,171,141,142,148,172,173,143,149,174,144,150,175,176,236,237,145,151,179,180,181,182,80,153,84,183,184,85,185,154,160,186,187,188,189,161,190,191,155,156,162,192,193,157,163,194,158,164,195,196,165,197,159,166,198,199,167,200,239,240,241,242,243,246,247,248,251,255,256,259,262,263,260,264,265,268,266,269,270,271,267,272,273,274,275,276,277,278,279,280,257,261,258,283,290,284,285,286,287,288,293,294,291,295,296,292,',
                                    'business_type' => '1,2,3,4,5,6,7,',
                                    'enterprise_id' => $em_id,
                                    'gogo_id' => $web_user['custom_id']
                                ]);
                            }
                        }
                        elseif ($v == 3) {
                            #集运管理系统
                            Db::name('centralize_manage_person')->where(['company_id'=>$company['id']])->update(['role_id'=>2]);

                            $ishave = Db::name('centralize_manage_person')->where(['tel' => $web_user['phone']])->whereOr('email', $web_user['email'])->find();
                            if (empty($ishave)) {
                                Db::name('centralize_manage_person')->insert([
                                    'name' => $web_user['realname'] != '' ? $web_user['realname'] : $web_user['nickname'],
                                    'agent_type' => 1,
                                    'type' => $web_user['reg_method'],
                                    'tel' => $web_user['phone'],
                                    'email' => $web_user['email'],
                                    'idcard' => $web_user['idcard'],
                                    'role_id' => 2,
                                    'status' => 1,
                                    'createtime' => $time,
                                    'gogo_id' => $web_user['custom_id']
                                ]);
                            }else{
                                Db::name('centralize_manage_person')->where(['id'=>$ishave['id']])->update(['role_id'=>2]);
                            }
                        }
                        elseif ($v == 4) {
                            #电商系统
                            Db::name('centralize_manage_person')->where(['company_id'=>$company['id']])->update(['role_id'=>3]);
                            $ishave = Db::connect($this->config)->name('user')->where(['mobile' => $web_user['phone']])->find();
                            if ($ishave['is_seller'] == 0) {
                                $shopid = Db::connect($this->config)->name('shop')->insertGetId([
                                    'user_id' => $ishave['user_id'],
                                    'shop_name' => $web_user['custom_id'] . '的店铺',
                                    'shop_type' => 2,
                                    'cat_id' => 1,
                                    'open_time' => $time,
                                    'goods_status' => 1,
                                    'shop_status' => 1,
                                    'service_tel' => $web_user['phone'],
                                    'created_at' => date('Y-m-d H:i:s', $time)
                                ]);
                                Db::connect($this->config)->name('user')->where(['user_id' => $ishave['user_id']])->update([
                                    'user_name'=> empty($ishave['user_name'])?$ishave['mobile']:$ishave['user_name'],
                                    'role_id' => 1,
                                    'rank_id' => 1,
                                    'is_seller' => 1,
                                    'shop_id' => $shopid,
                                    'reg_from' => 1,
                                    'security_level' => 2,
                                ]);
                                Db::connect($this->config)->name('image_dir')->insert([
                                    'shop_id'=>$shopid,
                                    'dir_name'=>'默认相册',
                                    'dir_group'=>'shop',
                                    'is_default'=>1,
                                    'created_at'=>date('Y-m-d H:i:s',$time)
                                ]);
                            }
                        }
                    }
                    Db::commit();
                    return json(['code' => 0, 'msg' => '关联成功！']);
                }catch(\Exception $e){
                    Db::rollback();
                    dd($e->getMessage());
                }
            }

            return json(['code' => 0, 'msg' => '关联成功！']);
        }else{
            $data = Db::name('website_user_company')->where('id',$id)->find();

            $role = Db::name('centralize_backstage_role')->select();

            #所有商户，除了自己
            $merchant = Db::name('website_user_company')->whereRaw('id != '.$id.' and is_verify=1 and status=0')->select();
            $merchant = json_encode($merchant,true);

            return view('',compact('data','role','id','merchant'));
        }
    }

    #获取卖家权限
    public function get_seller(Request $request){
        $data = input();
        $company_id = isset($data['company_id'])?intval($data['company_id']):0;
        $levelData = Db::name('website_user_company')->where(['id'=>$company_id])->field(['sellerList'])->find();
        if($company_id){
            $level_menus = ",".$levelData['sellerList'].",";
        }else{
            $level_menus = "";
        }

        $list = Db::name('website_menu')->where(['status'=>0,'type'=>2])->order('sort asc')->select();
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

    #获取服务商权限
    public function get_services(Request $request){
        $data = input();
        $company_id = isset($data['company_id'])?intval($data['company_id']):0;
        $levelData = Db::name('website_user_company')->where(['id'=>$company_id])->field(['servicesList'])->find();
        if($company_id){
            $level_menus = ",".$levelData['servicesList'].",";
        }else{
            $level_menus = "";
        }

        $list = Db::name('website_menu')->where(['status'=>0,'type'=>3])->order('sort asc')->select();
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

    public function get_web(Request $request){
        $data = input();
        $company_id = isset($data['company_id'])?intval($data['company_id']):0;
        $levelData = Db::name('website_user_company')->where(['id'=>$company_id])->field(['webList'])->find();
        if($company_id){
            $level_menus = ",".$levelData['webList'].",";
        }else{
            $level_menus = "";
        }

        $list = Db::name('centralize_manage_menu')->where(['status'=>0,'auth_type'=>5])->order('sort asc')->select();
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

    public function get_fee(Request $request){
        $data = input();
        $company_id = isset($data['company_id'])?intval($data['company_id']):0;
        $levelData = Db::name('website_user_company')->where(['id'=>$company_id])->field(['feeList'])->find();
        $level_menus = ','.$levelData['feeList'].',';

        $list = Db::connect($this->config)->name('cost_service')->select();
        $newList = [];
        foreach ($list as $item) {
            array_push($newList, ['id' => $item['id'], 'pId' => $item['pid'], 'title' => $item['name'] , 'checked' => strpos($level_menus,",".$item['id'].",") !== false ? true : false ]);
            foreach ($list as $value) {
                if ($item['id'] == $value['pid'] && !$value['pid'] != 0) {
                    array_push($newList, ['id' => $value['id'], 'pId' => $value['pid'], 'title' => $value['name'], 'checked' => strpos($level_menus,",".$value['id'].",") !== false ? true : false ]);
                }
            }
        }

        $data['menuList'] = $newList;
        return json(['code'=>0,'data'=>$data]);
    }

    #账户管理-站点配置
    public function website_setting(Request $request){
        $dat = input();
        $merchant_id = intval($dat['id']);

        if($request->isPost() || $request->isAjax()){

        }else{

        }
    }

    #账户管理-分销管理
    public function distr_manage(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        if($request->isPost() || $request->isAjax()){
            $trade_amount = 0;
            if($dat['type']==1){
                $trade_amount = $dat['trade_amount1'];
            }elseif($dat['type']==2){
                $trade_amount = $dat['trade_amount2'];
            }elseif($dat['type']==3){
                $trade_amount = $dat['trade_amount3'];
            }elseif($dat['type']==4){
                $trade_amount = $dat['trade_amount4'];
            }
            $agent_content = json_encode([
                'type'=>$dat['type'],
                'trade_amount'=>trim($trade_amount),
                'levels'=>intval($dat['levels']),
                'area'=>trim($dat['area']),
                'category'=>$dat['category'],
            ],true);
            $res = Db::name('website_user')->where('id',$id)->update(['agent_content'=>$agent_content,'agent_status'=>2,'agenttime'=>time()]);
            if($res){
                return json(['code'=>0,'msg'=>'操作成功！']);
            }
        }else{
            $data = Db::name('website_user')->where('id',$id)->find();
            $distr_method = Db::name('website_distrfund_method')->select();
            if(empty($data['agent_content'])){
                $data['agent_content'] = ['type'=>'','trade_amount'=>'','levels'=>'','area'=>'','category'=>''];
            }else{
                $data['agent_content'] = json_decode($data['agent_content'],true);
            }

            return view('',compact('data','id','distr_method'));
        }
    }

    #订单确认列表
    public function order_sure(Request $request){
        $dat = input();

        if($request->isPost() || $request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('website_order')->order($order)->count();
            $rows = DB::name('website_order')
                ->limit($limit)
                ->order($order)
                ->select();

            $status = [0=>'已发起',1=>'已确认'];
            $type = [1=>'集运订单',2=>'商城订单'];
            foreach($rows as $k=>$v){
                $user = Db::name('website_user')->where('id',$v['merchid'])->find();
                $rows[$k]['name'] = $user['realname']?$user['realname']:$user['nickname'];
                $rows[$k]['custom_id'] = $user['custom_id'];
                $rows[$k]['statusname']=$status[$v['status']];
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
                if(!empty($v['suretime'])){
                    $rows[$k]['suretime'] = date('Y-m-d H:i',$v['suretime']);
                }
                $rows[$k]['order_type'] = $type[$v['order_type']];
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    public function save_ordersure(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isPost() || $request->isAjax()){
            $time = time();
            $ordersn = 'GP' . date('YmdH', $time) . str_pad(mt_rand(1, 999999), 6, '0',
                    STR_PAD_LEFT) . substr(microtime(), 2, 6);
            $res = Db::name('website_order')->insert([
                'merchid'=>intval($dat['merchid']),
                'ordersn_flow'=>$ordersn,
                'order_type'=>$dat['order_type'],
                'ordersn'=>trim($dat['ordersn']),
                'totalmoney'=>trim($dat['totalmoney']),
                'status'=>0,
                'createtime'=>$time
            ]);
            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $merch = Db::name('website_user')->where('agent_id',0)->select();
            return view('',compact('id','merch'));
        }
    }

    #账单确认列表
    public function bill_sure(Request $request){
        $dat = input();

        if($request->isPost() || $request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('website_bill')->order($order)->count();
            $rows = DB::name('website_bill')
                ->limit($limit)
                ->order($order)
                ->select();

            $status = [0=>'已发起',1=>'已确认'];
            foreach($rows as $k=>$v){
                $user = Db::name('website_user')->where('id',$v['merchid'])->find();
                $rows[$k]['name'] = $user['realname']?$user['realname']:$user['nickname'];
                $rows[$k]['custom_id'] = $user['custom_id'];
                $rows[$k]['statusname']=$status[$v['status']];
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
                if(!empty($v['suretime'])){
                    $rows[$k]['suretime'] = date('Y-m-d H:i',$v['suretime']);
                }
//                $rows[$k]['order_type'] = $type[$v['order_type']];
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    public function save_billsure(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isPost() || $request->isAjax()){
            $fee_content = [];
            foreach($dat['event_date'] as $k=>$v){
                $fee_content = array_merge($fee_content,[['event_date'=>$v,'event_type'=>$dat['event_type'][$k],'event_name'=>trim($dat['event_name'][$k]),'price_unit'=>trim($dat['price_unit'][$k]),'price_unit2'=>trim($dat['price_unit2'][$k]),'price'=>sprintf('%.2f',substr(sprintf('%.3f',trim($dat['price'][$k])),0,-2)),'cycle'=>trim($dat['cycle'][$k]),'piece'=>trim($dat['piece'][$k]),'event_currency'=>trim($dat['event_currency'][$k]),'event_price'=>sprintf('%.2f',substr(sprintf('%.3f',trim($dat['event_price'][$k])),0,-2)),'event_remark'=>trim($dat['event_remark'][$k])]]);
            }
            if(empty($id)){
                $time = time();
                $ordersn = 'GP' . date('YmdH', $time) . str_pad(mt_rand(1, 999999), 6, '0',
                        STR_PAD_LEFT) . substr(microtime(), 2, 6);
                $res = Db::name('website_bill')->insert([
                    'merchid'=>intval($dat['merchid']),
                    'ordersn_flow'=>$ordersn,
                    'fee_content'=>json_encode($fee_content,true),
                    'totalmoney'=>sprintf('%.2f',substr(sprintf('%.3f',trim($dat['totalmoney2'])),0,-2)),
                    'invoicemoney'=>sprintf('%.2f',substr(sprintf('%.3f',trim($dat['invoicemoney'])),0,-2)),
                    'alreadypay'=>sprintf('%.2f',substr(sprintf('%.3f',trim($dat['alreadypay'])),0,-2)),
                    'shouldpay'=>sprintf('%.2f',substr(sprintf('%.3f',trim($dat['shouldpay'])),0,-2)),
                    'invoicemoney2'=>sprintf('%.2f',substr(sprintf('%.3f',trim($dat['invoicemoney2'])),0,-2)),
                    'finalmoney'=>sprintf('%.2f',substr(sprintf('%.3f',trim($dat['finalmoney'])),0,-2)),
                    'status'=>0,
                    'createtime'=>$time
                ]);
            }else{
                $res = Db::name('website_bill')->where('id',$id)->update([
                    'merchid'=>intval($dat['merchid']),
                    'fee_content'=>json_encode($fee_content,true),
                    'totalmoney'=>sprintf('%.2f',substr(sprintf('%.3f',trim($dat['totalmoney2'])),0,-2)),
                    'invoicemoney'=>sprintf('%.2f',substr(sprintf('%.3f',trim($dat['invoicemoney'])),0,-2)),
                    'alreadypay'=>sprintf('%.2f',substr(sprintf('%.3f',trim($dat['alreadypay'])),0,-2)),
                    'shouldpay'=>sprintf('%.2f',substr(sprintf('%.3f',trim($dat['shouldpay'])),0,-2)),
                    'invoicemoney2'=>sprintf('%.2f',substr(sprintf('%.3f',trim($dat['invoicemoney2'])),0,-2)),
                    'finalmoney'=>sprintf('%.2f',substr(sprintf('%.3f',trim($dat['finalmoney'])),0,-2)),
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $order = ['merchid'=>'','fee_content'=>[],'totalmoney'=>'','invoicemoney'=>'','alreadypay'=>0,'shouldpay'=>'','invoicemoney2'=>'','finalmoney'=>'',];
            $merch = Db::name('website_user')->where('agent_id',0)->select();
            if(!empty($id)){
                $order = Db::name('website_bill')->where('id',$id)->find();
                $order['fee_content'] = json_decode($order['fee_content'],true);
            }
            $currency = Db::name('currency')->select();

            return view('',compact('id','merch','currency','order'));
        }
    }

    public function account_list(Request $request){
        $dat = input();

        if($request->isPost() || $request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('website_account')->order($order)->count();
            $rows = DB::name('website_account')
                ->limit($limit)
                ->order($order)
                ->select();

            $type = [1=>'银行转账账户',2=>'网络支付账户'];
            foreach($rows as $k=>$v){
                $user = Db::name('website_user')->where('id',$v['merchid'])->find();
                if(empty($user)){
                    $rows[$k]['name'] = '平台';
//                    $rows[$k]['custom_id'] = 0;
                }else{
                    $rows[$k]['name'] = $user['realname']?$user['realname']:$user['nickname'];
//                    $rows[$k]['custom_id'] = $user['custom_id'];
                }
                $rows[$k]['typename']=$type[$v['type']];
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
//                $rows[$k]['order_type'] = $type[$v['order_type']];
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    public function save_account(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isPost() || $request->isAjax()){
            if($id>0){
                $res = Db::name('website_account')->where(['id'=>$id])->update([
                    'type'=>$dat['type'],
                    'bank_id'=>$dat['type']==1?$dat['bank_id']:'',
                    'bank_name'=>$dat['type']==1?trim($dat['bank_name']):'',
                    'bank_account'=>$dat['type']==1?trim($dat['bank_account']):'',
                    'platform_name'=>$dat['type']==2?$dat['platform_name']:'',
                    'type2'=>$dat['type']==2?$dat['type2']:'',
                    'platform_code'=>$dat['type']==2?$dat['platform_code'][0]:'',
                    'platform_account'=>$dat['type']==2?$dat['platform_account']:'',
                ]);
            }else{
                $res = Db::name('website_account')->insert([
                    'type'=>$dat['type'],
                    'bank_id'=>$dat['type']==1?$dat['bank_id']:'',
                    'bank_name'=>$dat['type']==1?trim($dat['bank_name']):'',
                    'bank_account'=>$dat['type']==1?trim($dat['bank_account']):'',
                    'platform_name'=>$dat['type']==2?$dat['platform_name']:'',
                    'type2'=>$dat['type']==2?$dat['type2']:'',
                    'platform_code'=>$dat['type']==2?$dat['platform_code'][0]:'',
                    'platform_account'=>$dat['type']==2?$dat['platform_account']:'',
                    'createtime'=>time()
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'操作成功！']);
            }
        }else{
            $data = ['type'=>1,'bank_id'=>'','bank_account'=>'','bank_name'=>'','platform_name'=>'','type2'=>1,'platform_code'=>'','platform_account'=>''];
            $bank_list = Db::name('bank_list')->select();
            if(!empty($id)){
                $data = Db::name('website_account')->where(['id'=>$id])->find();

            }
            return view('',compact('id','data','bank_list'));
        }
    }

    public function del_account(Request $request){
        $dat = input();

        $res = Db::name('website_account')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'操作成功！']);
        }
    }

    /**发起收款（废弃）**/
    public function collect2(Request $request){
        $dat = input();

        if($request->isPost() || $request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('website_collect')->order($order)->count();
            $rows = DB::name('website_collect')
                ->limit($limit)
                ->order($order)
                ->select();

            $status = [0=>'未付款',1=>'已付款'];
            foreach($rows as $k=>$v){
                if(empty($v['initiate_id'])){
                    $rows[$k]['initiate_name'] = '购购网';
                }else{
                    $user = Db::name('website_user')->where('id',$v['initiate_id'])->find();
                    $rows[$k]['initiate_name'] = $user['realname']?$user['realname']:$user['nickname'];
                }

                if(empty($v['pay_id'])){
                    $rows[$k]['pay_name'] = '购购网';
                }else{
                    $user = Db::name('website_user')->where('id',$v['pay_id'])->find();
                    $rows[$k]['pay_name'] = $user['realname']?$user['realname']:$user['nickname'];
                }
                $rows[$k]['status']=$status[$v['status']];
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
                if(!empty($v['paytime'])){
                    $rows[$k]['paytime'] = date('Y-m-d H:i',$v['paytime']);
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }
    /**发起收款（废弃）**/
    public function save_collect2(Request $request){
        $data = input();
        $id = isset($data['id'])?$data['id']:0;
        if($request->isPost() || $request->isAjax()){
            $ordersn = 'GP' . date('YmdH', time()) . str_pad(mt_rand(1, 999999), 6, '0',
                    STR_PAD_LEFT) . substr(microtime(), 2, 6);

            $time = time();
            $pay_term = intval(trim($data['pay_term']));

            $overdue = ( $pay_term * 86400 ) + $time;//逾期时间 付款期限*86400+发起时间

            if($data['trans_form']==2){
                $overdue = ( $pay_term * 86400 ) + strtotime(trim($data['advance_day']));//逾期时间 付款期限*86400+预约时间
            }

            //服务项目
            if(intval($data['trade_type'])==3){
                $service_name = $data['service_name'];
                $service_abstract = $data['service_abstract'];
                $service_price = $data['service_price'];
                foreach($service_name as $k=>$v){
                    $service_info[] = $service_name[$k].','.$service_abstract[$k].','.$service_price[$k];
                }
                $service_info = json_encode($service_info,true);
            }else{
                $service_info = '';
            }

            $pay_fee = $data['pay_fee']/100;

            $datas = [
                'initiate_id'=>0,
                'pay_id'=>$data['merchid'],
                'ordersn'=>$ordersn,
                'currency'=>$data['currency'],
                'trade_price'=>trim($data['trade_price']),
                'total_money'=>trim($data['trade_price']),
                'trade_type'=>intval($data['trade_type']),
                'good_id'=>intval($data['trade_type'])==1?intval($data['good_id']):'',
                'transaction_project'=>intval($data['trade_type'])==2?trim($data['transaction_project']):'',
//                'payer_name'=>trim($data['payer_name']),
//                'payer_tel'=>trim($data['payer_tel']),
                'pay_term'=>$pay_term,
                'pay_fee'=>$pay_fee,
                'overdue'=>$overdue,
                'trans_form'=>intval($data['trans_form']),
                'advance_day'=>intval($data['trans_form'])==2?strtotime(trim($data['advance_day'])):'',
                'regular_type'=>intval($data['trans_form'])==3?trim($data['regular_type']):'',
                'every_day'=>(intval($data['trans_form'])==3 && intval($data['regular_type'])==1)?trim($data['every_day']):'',
                'week'=>(intval($data['trans_form'])==3 && intval($data['regular_type'])==2)?trim($data['week']):'',
                'every_week'=>(intval($data['trans_form'])==3 && intval($data['regular_type'])==2)?trim($data['every_week']):'',
                'month'=>(intval($data['trans_form'])==3 && intval($data['regular_type'])==3)?trim($data['month']):'',
                'every_month'=>(intval($data['trans_form'])==3 && intval($data['regular_type'])==3)?trim($data['every_month']):'',
                'every_year'=>(intval($data['trans_form'])==3 && intval($data['regular_type'])==4)?trim($data['every_year']):'',
                'end_time'=>(intval($data['trans_form'])==3)?strtotime(trim($data['end_time'])):'',
                'createtime'=>$time,
                'basic'=>intval($data['basic']),
                'contract_num'=> intval($data['basic'])==1?trim($data['contract_num']):'',
                'contract_file'=> intval($data['basic'])==1?json_encode($data['contract_file'],true):'',
                'orderno'=>intval($data['basic'])==2?trim($data['orderno']):'',
                'orderurl'=>intval($data['basic'])==2?trim($data['orderurl']):'',
                'orderdemo'=>intval($data['basic'])==2?json_encode($data['orderdemo'],true):'',
                'description'=>intval($data['basic'])==3?trim($data['description']):'',
                'service_info'=>$service_info,
            ];

            if(empty($id)){
                $res = Db::name('website_collect')->insertGetId($datas);
                if($res){
                    #通知商户
                    $merch = Db::name('website_user')->where('id',$data['merchid'])->find();
                    if(!empty($merch['email'])){
                        cklein_mailAli(trim($merch['email']), '尊敬的商户', '购购网已向您发起收款', '购购网正在向您发起收款，付款链接：https://gather.gogo198.cn/collect/pay?collectid='.base64_encode($res));
                    }elseif(!empty($merch['phone'])){
                        //发送短信start
                        $post_data = [
                            'spid'=>'254560',
                            'password'=>'J6Dtc4HO',
                            'ac'=>'1069254560',
                            'mobiles'=>$merch['phone'],
                            'content'=>'购购网正在向您发起收款，付款链接：https://gather.gogo198.cn/collect/pay?collectid='.base64_encode($res).'【GOGO】',
                        ];
                        $post_data = json_encode($post_data,true);
                        httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                            'Content-Type: application/json; charset=utf-8',
                            'Content-Length:' . strlen($post_data),
                            'Cache-Control: no-cache',
                            'Pragma: no-cache'
                        ));// 必须声明请求头);
                        //发送短信end
                    }

                    return json(['code'=>0,'msg'=>'发送成功']);
                }
            }
        }else{
            $merch = Db::name('website_user')->where('agent_id',0)->select();
            $currency = Db::name('currency')->select();
            return view('',compact('currency','id','merch'));
        }
    }

    public function collect(Request $request){
        $dat = input();

        if($request->isPost() || $request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('website_collect')->order($order)->count();
            $rows = DB::name('website_collect')
                ->limit($limit)
                ->order($order)
                ->select();

            $status = [1=>'已发起已通知',2=>'已收到未付款',3=>'已付款已提交',4=>'已收款已确认'];
            foreach($rows as $k=>$v){
                if(empty($v['userid'])){
                    $rows[$k]['user_name'] = '购购网';
                }else{
                    $user = Db::name('website_user')->where('id',$v['userid'])->find();
                    $rows[$k]['user_name'] = $user['realname']?$user['realname']:$user['nickname'];
                }
                $pay_name = Db::name('website_user')->where('id',$v['pay_userid'])->find();
                $rows[$k]['pay_name'] = $pay_name['realname']?$pay_name['realname']:$pay_name['nickname'];
                $rows[$k]['statusname'] = $status[$v['status']];
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    public function save_collect(Request $request){
        $data = input();
        $id = isset($data['id'])?$data['id']:0;

        if($request->isPost() || $request->isAjax()){
            $time = time();
            $ordersn = 'GP' . date('YmdH', $time) . str_pad(mt_rand(1, 999999), 6, '0',
                    STR_PAD_LEFT) . substr(microtime(), 2, 6);

            if($data['pay_userid']==-1){
                #创建新的用户
                $data['pay_userid'] = Db::name('website_user')->insertGetId([
                    'custom_id'=>'',
                    'reg_method'=>1,
                    'phone'=>trim($data['phone']),
                    'email'=>trim($data['email']),
                    'realname'=>trim($data['nickname']),
                    'nickname'=>trim($data['nickname']),
                    'createtime'=>$time
                ]);
                $custom_id = 'GG'.date('YmdHis',$time).str_pad($data['pay_userid'], 3, '0', STR_PAD_LEFT);
                Db::name('website_user')->where('id',$data['pay_userid'])->update(['custom_id'=>$custom_id]);

                #创建集运网用户
                Db::name('centralize_user')->insert([
                    'name'=>$data['nickname'],
                    'realname'=>$data['nickname'],
                    'email'=>$data['email'],
                    'pwd'=>md5('888888'),
                    'mobile'=>$data['phone'],
                    'status'=>0,
                    'agentid'=>0,
                    'gogo_id'=>$custom_id,
                    'createtime'=>$time,
                ]);

                #创建商城用户
                Db::name('sz_yi_member')->insert([
                    'uniacid'=>3,
                    'realname'=>$data['nickname'],
                    'nickname'=>$data['nickname'],
                    'mobile'=>$data['phone']!=''?$data['phone']:$data['email'],
                    'pwd'=>md5('888888'),
                    'idcard'=>'',
                    'gogo_id'=>$custom_id,
                    'createtime'=>$time,
                ]);
                Db::name('sz_yi_member')->insert([
                    'uniacid'=>18,
                    'realname'=>$data['nickname'],
                    'nickname'=>$data['nickname'],
                    'mobile'=>$data['phone']!=''?$data['phone']:$data['email'],
                    'pwd'=>md5('888888'),
                    'idcard'=>'',
                    'gogo_id'=>$custom_id,
                    'createtime'=>$time,
                ]);
            }

            $account_id = 0;
            if($data['type']==1 && $data['type2']==1){
                #自有银行账户
                $account_id = $data['account_id'];
            }elseif($data['type']==1 && $data['type2']==2){
                #自有在线付款账户
                $account_id = $data['account_id2'];
            }elseif($data['type']==2 && $data['type3']==1){
                #购购银行账号
                $account_id = $data['account_id3'];
            }
            $datas = [
                'userid'=>0,
                'order_type'=>$data['order_type'],
                'ordersn_flow'=>$ordersn,
                'ordersn'=>$data['order_type']==1?$data['ordersn']:$data['ordersn2'],
                'totalmoney'=>trim($data['total_money']),
                'type'=>$data['type'],
                'type2'=>$data['type']==1?$data['type2']:'',
                'type3'=>$data['type']==2?$data['type3']:'',
                'account_id'=>$account_id,
                'pay_userid'=>$data['pay_userid'],
                'email'=>trim($data['email']),
                'phone'=>trim($data['phone']),
                'status'=>1,
                'createtime'=>$time
            ];
            if(empty($id)){
                $res = Db::name('website_collect')->insertGetId($datas);
                $url = 'https://gather.gogo198.cn/collect/pay?collectid='.base64_encode($res).'&email='.trim($data['email']).'&phone='.trim($data['phone']);
                if($res){
                    $rule_name = '';
                    #通知用户
                    if(!empty($data['email'])){
                        $rule_name = trim($data['email']);
                        cklein_mailAli(trim($data['email']), '尊敬的商户', '购购网已向您发起收款', '购购网正在向您发起收款，通知链接：'.$url);
                    }elseif(!empty($data['phone'])){
                        $rule_name = trim($data['phone']);
                        //发送短信start
                        $post_data = [
                            'spid'=>'254560',
                            'password'=>'J6Dtc4HO',
                            'ac'=>'1069254560',
                            'mobiles'=>$data['phone'],
                            'content'=>'购购网正在向您发起收款，通知链接：'.$url.'【GOGO】',
                        ];
                        $post_data = json_encode($post_data,true);
                        httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                            'Content-Type: application/json; charset=utf-8',
                            'Content-Length:' . strlen($post_data),
                            'Cache-Control: no-cache',
                            'Pragma: no-cache'
                        ));// 必须声明请求头);
                        //发送短信end
                    }

                    #创建回复规则
                    $insid = Db::name('rule')->insertGetId([
                        'uniacid'=>3,
                        'name'=>$rule_name,
                        'module'=>'news',
                        'displayorder'=>0,
                        'status'=>1
                    ]);

                    Db::name('rule_keyword')->insert([
                        'rid'=>$insid,
                        'uniacid'=>3,
                        'module'=>'news',
                        'content'=>$rule_name,
                        'type'=>1,
                        'displayorder'=>0,
                        'status'=>1
                    ]);

                    Db::name('news_reply')->insert([
                        'rid'=>$insid,
                        'parent_id'=>0,
                        'title'=>$rule_name,
                        'author'=>'购购网',
                        'description'=>'点击链接打开付款页面',
                        'thumb'=>'images/3/2023/06/EW8dTDv5Te3yB3rQ5w8MT5ATVvm3R5.jpg',
                        'url'=>'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=onlinepay&m=sz_yi&p=index&op=index&uid='.base64_encode($data['pay_userid']),
                        'incontent'=>1,
                        'createtime'=>$time
                    ]);

                    return json(['code'=>0,'msg'=>'收款发起成功']);
                }
            }
        }else{
            $currency = Db::name('currency')->select();
            $ordersn = Db::name('website_order')->where(['userid'=>0])->select();
            $billsn = Db::name('website_bill')->where(['userid'=>0])->select();
            $account_bank = Db::name('website_account')->where(['merchid'=>0,'type'=>1])->select();
            $account_plat = Db::name('website_account')->where(['merchid'=>0,'type'=>2])->select();
            $platform_bank = Db::name('onshore_account')->select();

            $users = Db::name('website_user')->order('id desc')->select();
            return view('',compact('currency','id','billsn','ordersn','account_bank','account_plat','users','platform_bank'));
        }
    }

    public function collect_tosure(Request $request){
        $dat = input();

        if($request->isPost() || $request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('website_collect')->where(['userid'=>0,'status'=>3])->order($order)->count();
            $rows = DB::name('website_collect')
                ->where(['userid'=>0,'status'=>3])
                ->limit($limit)
                ->order($order)
                ->select();

            $status = [1=>'收款人已发起已通知',2=>'付款人已收到未付款',3=>'付款人已付款待确认',4=>'收款人已收款已确认'];
            foreach($rows as $k=>$v){
                if(empty($v['userid'])){
                    $rows[$k]['user_name'] = '购购网';
                }else{
                    $user = Db::name('website_user')->where('id',$v['userid'])->find();
                    $rows[$k]['user_name'] = $user['realname']?$user['realname']:$user['nickname'];
                }
                $pay_name = Db::name('website_user')->where('id',$v['pay_userid'])->find();
                $rows[$k]['pay_name'] = $pay_name['realname']?$pay_name['realname']:$pay_name['nickname'];
                $rows[$k]['statusname'] = $status[$v['status']];
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);


            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    public function collect_process(Request $request){
        $dat = input();

        if($request->isPost() || $request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('website_collect')->where(['userid'=>0,'status'=>4])->order($order)->count();
            $rows = DB::name('website_collect')
                ->where(['userid'=>0,'status'=>4])
                ->limit($limit)
                ->order($order)
                ->select();

            $status = [1=>'收款人已发起已通知',2=>'付款人已收到未付款',3=>'付款人已付款待确认',4=>'收款人已收款已确认'];
            foreach($rows as $k=>$v){
                if(empty($v['userid'])){
                    $rows[$k]['user_name'] = '购购网';
                }else{
                    $user = Db::name('website_user')->where('id',$v['userid'])->find();
                    $rows[$k]['user_name'] = $user['realname']?$user['realname']:$user['nickname'];
                }
                $pay_name = Db::name('website_user')->where('id',$v['pay_userid'])->find();
                $rows[$k]['pay_name'] = $pay_name['realname']?$pay_name['realname']:$pay_name['nickname'];
                $rows[$k]['statusname'] = $status[$v['status']];
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);


            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    public function collect_detail(Request $request){
        $dat = input();

        if($request->isPost() || $request->isAjax()){
            $info = Db::name('website_collect')->where(['id'=>$dat['id']])->find();
            $info['payinfo'] = json_decode($info['payinfo'],true);

            if($info['type']==2){
                #委托平台收款
                $money = Db::name('website_user_money')->where(['userid'=>$info['userid']])->find();
                if(empty($money)){
                    Db::name('website_user_money')->insert([
                        'userid'=>$info['userid'],
                        'withdraw_money'=>$info['payinfo']['totalmoney']
                    ]);
                }else{
                    Db::name('website_user_money')->where(['userid'=>$info['userid']])->update([
                        'withdraw_money'=>$info['payinfo']['totalmoney']+$money['withdraw_money']
                    ]);
                }
            }

            $res = Db::name('website_collect')->where(['id'=>$dat['id']])->update([
                'status'=>4,
                'status2'=>$dat['status2']
            ]);

            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $data = Db::name('website_collect')->where(['id'=>$dat['id']])->find();
            $data['payinfo'] = json_decode($data['payinfo'],true);
            $user = Db::name('website_user')->where('id',$data['pay_userid'])->find();
            $data['pay_name']  = $user['realname']?$user['realname']:$user['nickname'];
            $data['bank_info'] = '';
            if($data['type']==1){
                $data['bank_info'] = Db::name('website_account')->where(['id'=>$data['account_id'],'type'=>$data['type2']])->find();
                if($data['type2']==1){
                    $data['bank_info']['bankname'] = Db::name('bank_list')->where(['id'=>$data['bank_info']['bank_id']])->find()['bank_name'];
                }
            }elseif($data['type']==2){
                $data['bank_info'] = Db::name('onshore_account')->where(['id'=>$data['account_id']])->find();
            }

            $status = [1=>'收款人已发起已通知',2=>'付款人已收到未付款',3=>'付款人已付款待确认',4=>'收款人已收款已确认'];
            $data['statusname'] = $status[$data['status']];
            $status2 = [1=>'确认款项未到账',2=>'确认款项未足额',3=>'确认到账已足额'];
            if($data['status']==4){
                $data['statusname2'] = $status2[$data['status2']];
            }
            return view('',compact('data'));
        }
    }

    public function withdraw_list(Request $request){
        $dat = input();

        if($request->isPost() || $request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('website_withdraw_log')->order($order)->count();
            $rows = DB::name('website_withdraw_log')
                ->limit($limit)
                ->order($order)
                ->select();

            $status = [0=>'已提交待审核',1=>'已审核已打款'];
            foreach($rows as $k=>$v){
                if(empty($v['userid'])){
                    $rows[$k]['user_name'] = '购购网';
                }else{
                    $user = Db::name('website_user')->where('id',$v['userid'])->find();
                    $rows[$k]['user_name'] = $user['realname']?$user['realname']:$user['nickname'];
                }
                $rows[$k]['statusname'] = $status[$v['status']];
                if(!empty($v['withdrawtime'])){
                    $rows[$k]['withdrawtime'] = date('Y-m-d H:i',$v['withdrawtime']);
                }
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    public function withdraw_detail(Request $request){
        $dat = input();

        if($request->isPost() || $request->isAjax()){
            $res = Db::name('website_withdraw_log')->where(['id'=>$dat['id']])->update([
                'withdrawal_expenses_rate'=>trim($dat['withdrawal_expenses_rate']),
                'withdrawal_expenses'=>trim($dat['withdrawal_expenses']),
                'real_withdraw'=>trim($dat['real_withdraw']),
                'withdrawtime'=>strtotime($dat['withdrawtime']),
                'status'=>intval($dat['status']),
                'remark'=>intval($dat['status'])==-1?trim($dat['remark']):'',
            ]);
            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $withdraw_log = Db::name('website_withdraw_log')->where(['id'=>$dat['id']])->find();
            $account = Db::name('website_account')->where(['id'=>$withdraw_log['account_id']])->find();
            if($account['type']==1){
                $account['bankname'] = Db::name('bank_list')->where(['id'=>$account['bank_id']])->find()['bank_name'];
            }
            return view('',compact('withdraw_log','account'));
        }
    }

    public function set_gpt(Request $request){
        $dat = input();

        if($request->isPost() || $request->isAjax()){
            $res = Db::name('website_request')->where(['id'=>1])->update([
                'times'=>intval($dat['times']),
                'times_type'=>intval($dat['times_type']),
                'times_limit'=>intval($dat['times_limit']),
                'count_times'=>intval($dat['count_times']),
                'count_type'=>intval($dat['count_type']),
                'count_limit'=>intval($dat['count_limit']),
            ]);

            if($res){
                return json(['code'=>0,'msg'=>'设置成功']);
            }
        }else{
            $data = Db::name('website_request')->where(['id'=>1])->find();
            return view('',compact('data'));
        }
    }

    public function set_usergpt(Request $request){
        $dat = input();

        if($request->isPost() || $request->isAjax()){
            #获取浏览记录
            if(isset($dat['pa'])){
                $limit = $request->get('limit');
                $page = $request->get('page') - 1;
                if ($page != 0) {
                    $page = $limit * $page;
                }

                $count = Db::name('website_chatai_log')->where(['uid'=>$dat['user_id']])->count();
                $rows = DB::name('website_chatai_log')
                    ->where(['uid'=>$dat['user_id']])
                    ->limit($page . ',' . $limit)
                    ->order('id desc')
                    ->select();

                if(!empty($rows)){
                    foreach($rows as $k=>$v){
                        $rows[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
                    }
                }
                return json(['code' => 0, 'count' => $count, 'data' => $rows]);
            }

            #操作
            $data = Db::name('website_request')->where(['uid'=>$dat['user_id']])->find();
            if(empty($data)){
                $res = Db::name('website_request')->insertGetId([
                    'uid'=>$dat['user_id'],
                    'times'=>intval($dat['times']),
                    'times_type'=>intval($dat['times_type']),
                    'times_limit'=>intval($dat['times_limit']),
                    'count_times'=>intval($dat['count_times']),
                    'count_type'=>intval($dat['count_type']),
                    'count_limit'=>intval($dat['count_limit']),
                ]);
                Db::name('website_user')->where(['id'=>$dat['user_id']])->update([
                    'request_id'=>$res
                ]);
            }else{
                $res = Db::name('website_request')->where(['uid'=>$dat['user_id']])->update([
                    'times'=>intval($dat['times']),
                    'times_type'=>intval($dat['times_type']),
                    'times_limit'=>intval($dat['times_limit']),
                    'count_times'=>intval($dat['count_times']),
                    'count_type'=>intval($dat['count_type']),
                    'count_limit'=>intval($dat['count_limit']),
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'设置成功']);
            }
        }else{
            $user = Db::name('website_user')->where(['id'=>$dat['id']])->find();
            $data = Db::name('website_request')->where(['uid'=>$user['id']])->find();
            if(empty($data)){
                $data = Db::name('website_request')->where(['id'=>$user['request_id']])->find();
            }
            return view('',compact('data','user','log'));
        }
    }

    //#---------------------询价&报价---------------------#//
    #业务配置列表
    public function buss_list(Request $request){
        $dat = input();

        if($request->isPost() || $request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('website_bussiness')->order($order)->count();
            $rows = DB::name('website_bussiness')
                ->limit($limit)
                ->order($order)
                ->select();

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    #新增业务
    public function save_buss(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isPost() || $request->isAjax()){
            if($id>0){
                $res = Db::name('website_bussiness')->where(['id'=>$id])->update(['name'=>trim($dat['name'])]);
            }else{
                $res = Db::name('website_bussiness')->insert(['name'=>trim($dat['name'])]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $data = Db::name('website_bussiness')->where(['id'=>$id])->find();
            return view('',compact('id','data'));
        }
    }

    #删除业务
    public function del_buss(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $res = Db::name('website_bussiness')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'操作成功']);
        }
    }

    #收费说明列表
    public function feedesc_list(Request $request){
        $dat = input();

        if($request->isPost() || $request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('website_quote_textlist')->order($order)->count();
            $rows = DB::name('website_quote_textlist')
                ->limit($limit)
                ->order($order)
                ->select();

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    #保存收费说明
    public function save_feedesc(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        if($request->isPost() || $request->isAjax()){
            if($id>0){
                $res = Db::name('website_quote_textlist')->where(['id'=>$id])->update(['text'=>trim($dat['text'])]);
            }else{
                $res = Db::name('website_quote_textlist')->insert(['text'=>trim($dat['text'])]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = ['text'=>''];
            if($id>0){
                $data = Db::name('website_quote_textlist')->where(['id'=>$id])->find();
            }
            return view('',compact('data','id'));
        }
    }

    #删除收费说明
    public function del_feedesc(Request $request){
        $dat = input();

        $res = Db::name('website_quote_textlist')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #询价表单列表
    public function inquiry_list(Request $request){
        $dat = input();

        if($request->isPost() || $request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('website_inquiry_form')->where(['pid'=>$dat['pid']])->order($order)->count();
            $rows = DB::name('website_inquiry_form')
                ->where(['pid'=>$dat['pid']])
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>$v){
                $rows[$k]['link'] = 'https://www.gogo198.net/?s=index/save_inquiry&buss_id='.$v['id'].'&type=0';
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            $id = $dat['id'];
            return view('',compact('id'));
        }
    }

    #保存询价表单
    public function save_inquiry(Request $request){
        $dat = input();
        $pid = $dat['pid'];
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isPost() || $request->isAjax()){
            #保存询价表单
            $content = [];

            $label_name = $dat['content']['label_name'];
            foreach($label_name as $k=>$v){
                array_push($content,[
                    'label_name'=>$v,
                    'label_value'=>$dat['content']['label_value'][$k],#元素id
                    'label_select'=>$dat['content']['label_select'][$k],#选择框
                    'label_rand'=>$dat['content']['label_rand'][$k],#时间框
                    'label_introduce'=>$dat['content']['label_introduce'][$k],#介绍框
                ]);
            }

            if(empty($id)){
                $res = Db::name('website_inquiry_form')->insert([
                    'pid'=>$pid,
                    'name'=>trim($dat['name']),
                    'content'=>json_encode($content,true),
                ]);
            }else{
                $res = Db::name('website_inquiry_form')->where(['id'=>$id])->update([
                    'pid'=>$pid,
                    'name'=>trim($dat['name']),
                    'content'=>json_encode($content,true),
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $data = ['content'=>'','name'=>''];
            if($id>0){
                $data = Db::name('website_inquiry_form')->where(['id'=>$id])->find();
                $data['content'] = json_decode($data['content'],true);
                foreach($data['content'] as $k=>$v){
                    if($v['label_value']==4){
                        $data['content'][$k]['label_select2'] = explode('、',$v['label_select']);
                    }
                }
            }

            return view('',compact('pid','id','data'));
        }
    }

    #删除询价
    public function del_inquiry(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $res = Db::name('website_inquiry_form')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'操作成功']);
        }
    }

    #导入询价或报价模板
    public function import_template(Request $request){
        $dat = input();
        $pid = $dat['pid'];
        $type = $dat['type'];
        
        if($request->isPost() || $request->isAjax()){
            if(empty($dat['bname'])){
                return json(['code'=>-1,'msg'=>'请输入分业务名称']);
            }
            $file = $request->file('file');
            if (!$file) {
                return json(['code' => -1, 'msg' => '请上传文件']);
            }
            $path = ROOT_PATH.'public'.DS.'static'.DS.'home'.DS.'uploads';

            $saveResult = $file->validate(['ext' => 'xls,csv,xlsx'])->move($path);
            if (!$saveResult) {
                return json(['code' => -1, 'msg' => $file->getError()]);
            }
            $fileName = $path.'/'.$saveResult->getSaveName();
            $data = [];
            $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
            require_once($dir."/IOFactory.php");
            $inputFileType = PHPExcel_IOFactory::identify($fileName);
            $objRead = PHPExcel_IOFactory::createReader($inputFileType);
            $objRead->setReadDataOnly(true);
            $PHPRead = $objRead->load($fileName);
            $sheet = $PHPRead->getSheet(0);
            $allRow = $sheet->getHighestRow();
            for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                array_push($data, [
                    'label_name' => $PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue(),
                    'label_value' => $PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue(),
                    'label_select' => $PHPRead->getActiveSheet()->getCell("C".$currentRow)->getValue(),
                    'label_introduce' => $PHPRead->getActiveSheet()->getCell("D".$currentRow)->getValue(),
                ]);
            }
            
            foreach($data as $k=>$v){
                $data[$k]['label_rand'] = '';
                if($v['label_value']=='文本输入框'){
                    $data[$k]['label_value'] = 1;
                }else if($v['label_value']=='数值输入框'){
                    $data[$k]['label_value'] = 2;
                }else if($v['label_value']=='日期选择框'){
                    $data[$k]['label_value'] = 3;
                    $data[$k]['label_rand'] = rand(11111,99999).rand(11111,99999);
                }else if($v['label_value']=='普通选择框'){
                    $data[$k]['label_value'] = 4;
                }else if($v['label_value']=='数量增减框'){
                    $data[$k]['label_value'] = 5;
                }else if($v['label_value']=='日期选择框(多个)'){
                    $data[$k]['label_value'] = 6;
                    $data[$k]['label_rand'] = rand(11111,99999).rand(11111,99999);
                }
            }
            if($dat['type']==1){
                Db::name('website_inquiry_form')->insert([
                    'name'=>trim($dat['bname']),
                    'pid'=>$dat['pid'],
                    'content'=>json_encode($data,true)
                ]);
            }elseif($dat['type']==2){
                Db::name('website_quote_form')->insert([
                    'name'=>trim($dat['bname']),
                    'pid'=>$dat['pid'],
                    'content'=>json_encode($data,true)
                ]);
            }
            
            @unlink($fileName);
            return json(['code'=>0,'msg'=>'已上传']);
        }else{
            return view('',compact('pid','type'));
        }
    }
    
    #生成询价
    public function generate_inquiry(Request $request){
        $dat = input();

        if($request->isPost() || $request->isAjax()){
            if($dat['is_fillin']==0){
                $email = trim($dat['email']);
                $phone = trim($dat['phone']);
                if($dat['notice_method']==1 && empty($email)){
                    return json(['code'=>-1,'msg'=>'请输入电子邮箱']);
                }
                if($dat['notice_method']==2 && empty($phone)){
                    return json(['code'=>-1,'msg'=>'请输入手机号码']);
                }

                if($dat['notice_method']==1){
                    #邮箱
                    $email = str_replace('、',';',$email);
                    $res = httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$email,'title'=>'询价查询','content'=>'询价链接：https://www.gogo198.net/?s=index/save_inquiry&buss_id='.intval($dat['id'])]);
                }elseif($dat['notice_method']==2){
                    $phone = str_replace('、',',',$phone);
                    #手机
                    $post_data = [
                        'spid'=>'254560',
                        'password'=>'J6Dtc4HO',
                        'ac'=>'1069254560',
                        'mobiles'=>$phone,
                        'content'=>'询价链接：https://www.gogo198.net/?s=index/save_inquiry&buss_id='.intval($dat['id']).'【GOGO】',
                    ];
                    $post_data = json_encode($post_data,true);
                    $res = httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                        'Content-Type: application/json; charset=utf-8',
                        'Content-Length:' . strlen($post_data),
                        'Cache-Control: no-cache',
                        'Pragma: no-cache'
                    ));
                }

                if($res){
                    return json(['code'=>0,'msg'=>'通知成功']);
                }
            }elseif($dat['is_fillin']==1){
                #生成询价单、发给B端
                $time = time();
                #获取主业务id
                $form = Db::name('website_inquiry_form')->where(['id'=>$dat['id']])->find();
                $files = [];
                //file和filename
                if(isset($dat['file'])){
                    foreach($dat['filename'] as $k=>$v){
                        if(empty($v)){
                            return json(['code'=>-2,'msg'=>'请输入文件名称']);
                        }else{
                            $files = array_merge($files,[['files'=>$dat['file'][$k],'filenames'=>trim($v)]]);
                        }
                    }
                }
                $files = json_encode($files,true);
                #label5
                $label5_select = '';
                $label5_content = '';
                if(!empty($dat['label5_select'])){
                    $label5_select = json_encode($dat['label5_select'],true);
                    foreach($dat['label5_content'] as $k=>$v){
                        if(empty($v)){
                            return json(['code'=>-2,'msg'=>'数量不能为0']);
                        }
                    }
                    $label5_content = json_encode($dat['label5_content'],true);
                }
                #插入询价数据表
                $res = Db::name('website_inquiry_order')->insertGetId([
                    'uid'=>0,
                    'buss_id'=>$dat['id'],
                    'buss_pid'=>$form['pid'],
                    'ordersn'=>'GP' . date('YmdH', $time) . str_pad(mt_rand(1, 999999), 6, '0',
                            STR_PAD_LEFT) . substr(microtime(), 2, 6),
                    'platform'=>1,
                    'content'=>json_encode($dat['content'],true),
                    'label5_select'=>$label5_select,
                    'label5_content'=>$label5_content,
                    'files'=>$files,
                    'createtime'=>$time
                ]);
                #通知
                if($dat['notice_type']==1){
                    #邮箱通知
                    if(!empty($dat['merch_id'])){
                        $dat['merch_id'] = explode(',',$dat['merch_id']);
                        foreach($dat['merch_id'] as $k=>$v){
                            $user = Db::name('website_user')->where(['id'=>$v])->find();
                            httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$user['email'],'title'=>'询价信息','content'=>'点击链接查看询价信息：https://www.gogo198.net/?s=index/quote_info&id='.$res]);
                        }
                    }
                    if(!empty($dat['other_contact'])){
                        $dat['other_contact'] = str_replace('、',';',$dat['other_contact']);
                        httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$dat['other_contact'],'title'=>'询价信息','content'=>'点击链接查看询价信息：https://www.gogo198.net/?s=index/quote_info&id='.$res]);
                    }
                }
                elseif($dat['notice_type']==2){
                    #手机通知
                    if(!empty($dat['merch_id'])){
                        $dat['merch_id'] = explode(',',$dat['merch_id']);
                        foreach($dat['merch_id'] as $k=>$v){
                            $user = Db::name('website_user')->where(['id'=>$v])->find();
                            $post_data = [
                                'spid'=>'254560',
                                'password'=>'J6Dtc4HO',
                                'ac'=>'1069254560',
                                'mobiles'=>$user['phone'],
                                'content'=>'点击链接查看询价信息：https://www.gogo198.net/?s=index/quote_info&id='.$res.'【GOGO】',
                            ];
                            $post_data = json_encode($post_data,true);
                            httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                                'Content-Type: application/json; charset=utf-8',
                                'Content-Length:' . strlen($post_data),
                                'Cache-Control: no-cache',
                                'Pragma: no-cache'
                            ));
                        }
                    }
                    if(!empty($dat['other_contact'])){
                        $dat['other_contact'] = str_replace('、',',',$dat['other_contact']);
                        $post_data = [
                            'spid'=>'254560',
                            'password'=>'J6Dtc4HO',
                            'ac'=>'1069254560',
                            'mobiles'=>$dat['other_contact'],
                            'content'=>'点击链接查看询价信息：https://www.gogo198.net/?s=index/quote_info&id='.$res.'【GOGO】',
                        ];
                        $post_data = json_encode($post_data,true);
                        httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                            'Content-Type: application/json; charset=utf-8',
                            'Content-Length:' . strlen($post_data),
                            'Cache-Control: no-cache',
                            'Pragma: no-cache'
                        ));
                    }
                }

                if($res){
                    return json(['code'=>0,'msg'=>'提交成功，请等待报价']);
                }
            }

        }else{
            $id = $dat['id'];
            $template = Db::name('website_inquiry_form')->where(['id'=>$id])->find();
            $template['content'] = json_decode($template['content'],true);
            $label5_select = [];
            foreach($template['content'] as $k=>$v){
                if($v['label_value']==4){
                    $template['content'][$k]['label_select2'] = explode('、',$v['label_select']);
                }elseif($v['label_value']==5){
                    array_push($label5_select,['label_name'=>$v['label_name'],'label_introduce'=>$v['label_introduce']]);
                }
            }
            #系统内商户
            $merch = Db::name('website_user')->where(['merch_status'=>2])->select();
            $merch = json_encode($merch,true);
            return view('',compact('id','template','label5_select','merch'));
        }
    }

    #生成分享图片
    public function share_img(Request $request){
        $dat = input();
        $name = 'buss_'.intval($dat['buss_id']).'_'.$dat['val'];
        $folder = $_SERVER['DOCUMENT_ROOT'].'/collect_website/public/bussiness/qrcode/';

        if($dat['val']==1){
            #官网链接二维码
            $img = generate_code($name,'https://www.gogo198.net/?s=index/save_inquiry&buss_id='.intval($dat['buss_id']),$folder);
        }elseif($dat['val']==2){
            #小程序码
            $time = time();

            if($time > (session('expires_time') + 3600)){
                #获取accesstoken
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx6d1af256d76896ba&secret=d19a96d909c1a167c12bb899d0c10da6";
                $res = file_get_contents($url);
                $result = json_decode($res, true);
                session('access_token',$result["access_token"]);
                session('expires_time',$time);
            }
            #获取微信小程序码
            $url = "https://api.weixin.qq.com/wxa/createwxaqrcode?access_token=" . session('access_token');
            $data = array(
//                'path' => 'pages/index/save_inquiry?buss_id='.intval($dat['buss_id']),
                'path' => 'funpackage/questionsWall/questionInfo?question_id=22579',
                'width' => 430,
            );
            $response = httpRequest2($url, json_encode($data));

            $img = $response;
        }elseif($dat['val']==3){
            #公众号链接二维码
            $img = generate_code($name,'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=checkprice&m=sz_yi&p=index&op=save_inquiry&buss_id='.intval($dat['buss_id']),$folder);
        }
        return json(['code'=>0,'img'=>$img.'?v='.time(),'msg'=>'生成成功']);
    }

    #报价表单列表
    public function quote_list(Request $request){
        $dat = input();

        if($request->isPost() || $request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('website_quote_form')->where(['pid'=>$dat['pid']])->order($order)->count();
            $rows = DB::name('website_quote_form')
                ->where(['pid'=>$dat['pid']])
                ->limit($limit)
                ->order($order)
                ->select();

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            $id = $dat['id'];
            return view('',compact('id'));
        }
    }

    #保存报价表单
    public function save_quote(Request $request){
        $dat = input();
        $pid = $dat['pid'];
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isPost() || $request->isAjax()){
            #保存报价表单
            $content = [];

            $label_name = $dat['content']['label_name'];
            foreach($label_name as $k=>$v){
                array_push($content,[
                    'label_name'=>$v,
                    'label_value'=>$dat['content']['label_value'][$k],#元素id
                    'label_select'=>$dat['content']['label_select'][$k],#选择框
                    'label_rand'=>$dat['content']['label_rand'][$k],#时间框
                    'label_introduce'=>$dat['content']['label_introduce'][$k],#描述框
                ]);
            }

            if(empty($id)){
                $res = Db::name('website_quote_form')->insert([
                    'pid'=>$pid,
                    'content'=>json_encode($content,true),
                ]);
            }else{
                $res = Db::name('website_quote_form')->where(['id'=>$id])->update([
                    'pid'=>$pid,
                    'content'=>json_encode($content,true),
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $data = ['content'=>''];
            if($id>0){
                $data = Db::name('website_quote_form')->where(['id'=>$id])->find();
                $data['content'] = json_decode($data['content'],true);
                foreach($data['content'] as $k=>$v){
                    if($v['label_value']==4){
                        $data['content'][$k]['label_select2'] = explode('、',$v['label_select']);
                    }
                }
            }

            return view('',compact('pid','id','data'));
        }
    }

    #删除报价
    public function del_quote(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $res = Db::name('website_quote_form')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'操作成功']);
        }
    }

    #询价管理中心
    public function inquiry_order(Request $request){
        $dat = input();

        if($request->isPost() || $request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('website_inquiry_order')->order($order)->count();
            $rows = DB::name('website_inquiry_order')
                ->limit($limit)
                ->order($order)
                ->select();

            $status = [0=>'已询价待报价',1=>'已报价待下单',2=>'已下单待接单',3=>'已接单'];
            $platform = [1=>'官网',2=>'公众号',3=>'小程序'];
            foreach($rows as $k=>$v){
                $rows[$k]['buss_name'] = Db::name('website_bussiness')->where(['id'=>$v['buss_pid']])->find()['name'];
                $rows[$k]['buss_name2'] = Db::name('website_inquiry_form')->where(['id'=>$v['buss_id']])->find()['name'];
                $rows[$k]['buss_name'] .= ' - '.$rows[$k]['buss_name2'];
                $rows[$k]['custom_id'] = Db::name('website_user')->where(['id'=>$v['uid']])->find()['custom_id'];
                $rows[$k]['statusname'] = $status[$v['status']];
                $rows[$k]['platform'] = $platform[$v['platform']];
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    #询价单详情
    public function inquiry_order_detail(Request $request){
        $dat = input();
        $id = $dat['id'];
        if($request->isPost() || $request->isAjax()){
            $origin_info = Db::name('website_inquiry_order')
                ->alias('a')
                ->join('website_inquiry_form b','b.id=a.buss_id')
                ->where(['a.id'=>$dat['id']])
                ->field(['b.content as form','a.content','a.label5_select','a.label5_content','b.id as formid'])
                ->find();
            #模板
            $origin_info['form'] = json_decode($origin_info['form'],true);
            #表单数据
            $origin_info['content'] = json_decode($origin_info['content'],true);
            $origin_info['label5_select'] = json_decode($origin_info['label5_select'],true);
            $origin_info['label5_content'] = json_decode($origin_info['label5_content'],true);

            #合并数据start
            $content = [];
            $label_name = $dat['content']['label_name'];
            foreach($label_name as $k=>$v){
                array_push($content,[
                    'label_name'=>$v,
                    'label_value'=>$dat['content']['label_value'][$k],#元素id
                    'label_select'=>$dat['content']['label_select'][$k],#选择框
                    'label_rand'=>$dat['content']['label_rand'][$k],#时间框
                    'label_introduce'=>$dat['content']['label_introduce'][$k],#介绍框
                ]);
            }
            $label5_select = '';
            $label5_content = '';
            if(!empty($dat['label5_select'])){
                $label5_select = json_encode($dat['label5_select'],true);
                foreach($dat['label5_content'] as $k=>$v){
                    if(empty($v)){
                        return json(['code'=>-2,'msg'=>'数量不能为0']);
                    }
                }
                $label5_content = json_encode($dat['label5_content'],true);
            }
            #合并数据end

            #组装模板数据
            $origin_info['form'] = array_merge($origin_info['form'],$content);
            #组装表单数据
            $origin_info['content'] = array_merge($origin_info['content'],$dat['insert_content']);
            #组装label5数据
            if(!empty($label5_select)){
                $origin_info['label5_select'] = array_merge($origin_info['label5_select'],$label5_select);
                $origin_info['label5_content'] = array_merge($origin_info['label5_content'],$label5_select);
            }
            #保存到数据表
            Db::name('website_inquiry_form')->where(['id'=>$origin_info['formid']])->update(['content'=>json_encode($origin_info['form'],true)]);
            Db::name('website_inquiry_order')->where(['id'=>$dat['id']])->update(['content'=>json_encode($origin_info['content'],true),'label5_select'=>json_encode($origin_info['label5_select'],true),'label5_content'=>json_encode($origin_info['label5_content'],true)]);

            return json(['code'=>0,'msg'=>'操作成功']);
        }else{
            #询价--start
            $data = Db::name('website_inquiry_order')
                ->alias('a')
                ->join('website_bussiness b','b.id=a.buss_pid')
                ->join('website_inquiry_form c','c.id=a.buss_id')
//                ->join('website_user d','d.id=a.uid')
                ->where(['a.id'=>$id])
                ->field(['a.content','c.content as form','a.label5_select','a.label5_content','a.uid','a.id','a.ordersn','a.status','a.is_quote','a.files','a.quote_id'])
                ->find();
            if(empty($data)){
                #o端发起询价
                $data = Db::name('website_inquiry_order')
                    ->alias('a')
                    ->join('website_bussiness b','b.id=a.buss_pid')
                    ->join('website_inquiry_form c','c.id=a.buss_id')
                    ->where(['a.id'=>$id])
                    ->field(['a.content','c.content as form','a.label5_select','a.label5_content','a.uid','a.id','a.ordersn','a.status','a.is_quote','a.files','a.quote_id'])
                    ->find();
            }
            $data['inquiry_user'] = '';
            #查询询价客户ID
            if(!empty($data['uid'])){
                $data['inquiry_user'] = Db::name('website_user')->where(['id'=>$data['uid']])->find();
            }

            if(!empty($data['files'])){
                $data['files'] = json_decode($data['files'],true);
            }

            if(!empty($data['content'])){
                $data['content'] = json_decode($data['content'],true);
                if($data['is_quote']==1){
                    foreach($data['content'] as $k=>$v){
                        if(!empty($v['quote_text1'])){
                            $data['content'][$k]['quote_text1'] = json_decode($v['quote_text1'],true);
                        }
                        if(!empty($v['quote_text2'])){
                            $data['content'][$k]['quote_text2'] = json_decode($v['quote_text2'],true);
                            foreach($data['content'][$k]['quote_text2'] as $k2=>$v2){
                                $data['content'][$k]['quote_text2'][$k2] = Db::name('website_quote_textlist')->where(['id'=>$v2])->find()['text'];
                            }
                        }
                        if(!empty($v['quote_text2_edit'])){
                            $data['content'][$k]['quote_text2_edit'] = json_decode($v['quote_text2_edit'],true);
                        }
                        $data['content'][$k]['currency'] = Db::name('currency')->where(['code_value'=>$v['currency']])->find()['code_name'];
                        $data['content'][$k]['unit'] = Db::name('unit')->where(['code_value'=>$v['unit']])->find()['code_name'];
                        $data['content'][$k]['country'] = Db::name('country_code')->where(['code_value'=>$v['country']])->find()['code_name'];
                        $data['content'][$k]['value'] = Db::name('centralize_value_list')->where(['id'=>$v['value']])->find()['name'];
                    }
                }
            }
            $data['form'] = json_decode($data['form'],true);
            if(!empty($data['label5_select'])){
                $data['label5_select'] = json_decode($data['label5_select'],true);
                $data['label5_content'] = json_decode($data['label5_content'],true);
            }
            #询价--end
            #报价--start
            $data['quote_user'] = '';
            $quote_order = Db::name('website_quote_order')->where(['id'=>$data['quote_id']])->find();
            if($quote_order['id']>0){
                #报价单
                $data['quote_content'] = json_decode($quote_order['content'],true);
                if(!empty($data['quote_content'])){
                    foreach($data['quote_content'] as $k=>$v){
                        if(!empty($v['quote_text1'])){
                            $data['quote_content'][$k]['quote_text1'] = json_decode($v['quote_text1'],true);
                        }
                        if(!empty($v['quote_text2'])){
                            $data['quote_content'][$k]['quote_text2'] = json_decode($v['quote_text2'],true);
    //                        foreach($data['quote_content'][$k]['quote_text2'] as $k2=>$v2){
    //                            $data['quote_content'][$k]['quote_text2'][$k2] = Db::name('website_quote_textlist')->where(['id'=>$v2])->find()['text'];
    //                        }
                        }
                        if(!empty($v['quote_text2_edit'])){
                            $data['quote_content'][$k]['quote_text2_edit'] = json_decode($v['quote_text2_edit'],true);
                        }
                    }
                }
                if($quote_order['quote_formid']!=-2){
                    $data['quote_form'] = Db::name('website_quote_form')->where(['id'=>$quote_order['quote_formid']])->find();
    //                $data['quote_form'] = json_decode($data['quote_form'],true);
    //                foreach($data['quote_form'] as $k=>$v){
    //                    if($v['label_value']==4){
    //                        $data['quote_form'][$k]['label_select2'] = explode('、',$v['label_select']);
    //                    }
    //                }
                }

                #报价客户ID
                $data['quote_user'] = Db::name('website_user')->where(['id'=>$quote_order['uid']])->find();
            }
            #报价--end
            #币种
            $currency = Db::name('currency')->select();
            #币种的人民币和美元排在前列
            foreach($currency as $k=>$v){
                if($v['code_value']==142){
                    $num0_value = $currency[0]['code_value'];
                    $num0_name = $currency[0]['code_name'];
                    $currency[0]['code_value'] = $v['code_value'];
                    $currency[0]['code_name'] = $v['code_name'];
                    $currency[$k]['code_value'] = $num0_value;
                    $currency[$k]['code_name'] = $num0_name;
                }elseif($v['code_value']==502){
                    $num0_value = $currency[1]['code_value'];
                    $num0_name = $currency[1]['code_name'];
                    $currency[1]['code_value'] = $v['code_value'];
                    $currency[1]['code_name'] = $v['code_name'];
                    $currency[$k]['code_value'] = $num0_value;
                    $currency[$k]['code_name'] = $num0_name;
                }
            }
            #单位
            $unit = Db::name('unit')->select();
            #国地
            $country = Db::name('country_code')->where('code_name','<>','无')->select();
            #货物属性
            $value = Db::name('centralize_value_list')->select();
            #说明文本
            $quote_text = Db::name('website_quote_textlist')->order('id','desc')->select();
            return view('',compact('data','currency','unit','country','country','value','quote_text','quote_order'));
        }
    }

    #分享询价
    public function share_inquiry(Request $request){
        $dat = input();
        $id = $dat['id'];
        if($request->isPost() || $request->isAjax()){
            if(empty($dat['merch_id'])){
                return json(['code'=>-1,'msg'=>'请选择商户']);
            }

            if(empty($dat['notice_type'])){
                return json(['code'=>-1,'msg'=>'请选择通知方式']);
            }

            $time = time();
            if(strpos($dat['merch_id'],'-2')){
                foreach($dat['other_name'] as $k=>$v){
                    if(empty($v)){
                        return json(['code'=>-1,'msg'=>'请填写其他商户名称']);
                    }else{
                        #记录其他商户到表
                        $o_merch = Db::name('website_other_merch')->insertGetId(['name'=>trim($v),'link'=>$dat['other_link'][$k],'createtime'=>$time]);
//                        if(strstr($dat['notice_type'],'1')){
//                            #邮箱
//                            $res = httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>trim($dat['other_email'][$k]),'title'=>'询价信息','content'=>'点击链接查看询价信息：https://www.gogo198.net/?s=index/quote_info&id='.intval($dat['id'])]);
//                            $status = 1;
//                            if(json_decode($res,true)['status']==0){
//                                #发送失败
//                                $status = 0;
//                            }
//                            Db::name('website_inquiry_share_log')->insert(['inquiry_id'=>intval($dat['id']),'name'=>trim($v),'notice_type'=>1,'status'=>$status]);
//                        }
//                        if(strstr($dat['notice_type'],'2')){
//                            #手机
//                            $post_data = [
//                                'spid'=>'254560',
//                                'password'=>'J6Dtc4HO',
//                                'ac'=>'1069254560',
//                                'mobiles'=>trim($dat['other_phone'][$k]),
//                                'content'=>'点击链接查看询价信息：https://www.gogo198.net/?s=index/quote_info&id='.intval($dat['id']).'【GOGO】',
//                            ];
//                            $post_data = json_encode($post_data,true);
//                            httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
//                                'Content-Type: application/json; charset=utf-8',
//                                'Content-Length:' . strlen($post_data),
//                                'Cache-Control: no-cache',
//                                'Pragma: no-cache'
//                            ));
//                            $status = 1;
//                            $res = file_get_contents('https://rptjm.jxtebie.com/sms/report?spid=254560&password=J6Dtc4HO');
//                            $res = simplexml_load_string($res);
//                            if(empty($res)){
//                                #发送失败
//                                $status = 0;
//                            }
//                            Db::name('website_inquiry_share_log')->insert(['inquiry_id'=>intval($dat['id']),'name'=>trim($v),'notice_type'=>2,'status'=>$status,'error_msg'=>json_encode($res,true)]);
//                        }
//                        if(strstr($dat['notice_type'],'3')){
                            #站内
//                            Db::name('website_station_msg')->insert(['uid'=>$o_merch,'is_other'=>1,'msg'=>'点击链接查看询价信息：https://www.gogo198.net/?s=index/quote_info&id='.intval($dat['id']),'createtime'=>$time]);
                            Db::name('website_inquiry_share_log')->insert(['inquiry_id'=>intval($dat['id']),'name'=>trim($v),'notice_type'=>3,'status'=>1]);
//                        }
                    }
                }
            }

            $dat['merch_id'] = str_replace(',-2','',$dat['merch_id']);
            $dat['merch_id'] = explode(',',$dat['merch_id']);
            foreach($dat['merch_id'] as $k=>$v){
                $merch = Db::name('website_user')->where(['id'=>$v])->find();
                if(empty($merch)){
                    #其他商户
                    $other_id = str_replace('O','',$v);
                    $other_merch = Db::name('website_other_merch')->where(['id'=>$other_id])->find();
//                    if(strstr($dat['notice_type'],'1')){
//                        #邮箱
//                        $res = httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$other_merch['email'],'title'=>'询价信息','content'=>'点击链接查看询价信息：https://www.gogo198.net/?s=index/quote_info&id='.intval($dat['id'])]);
//                        $status = 1;
//                        if(json_decode($res,true)['status']==0){
//                            #发送失败
//                            $status = 0;
//                        }
//                        Db::name('website_inquiry_share_log')->insert(['inquiry_id'=>intval($dat['id']),'name'=>$other_merch['name'],'notice_type'=>1,'status'=>$status]);
//                    }
//                    if(strstr($dat['notice_type'],'2')){
//                        #手机
//                        $post_data = [
//                            'spid'=>'254560',
//                            'password'=>'J6Dtc4HO',
//                            'ac'=>'1069254560',
//                            'mobiles'=>$other_merch['phone'],
//                            'content'=>'点击链接查看询价信息：https://www.gogo198.net/?s=index/quote_info&id='.intval($dat['id']).'【GOGO】',
//                        ];
//                        $post_data = json_encode($post_data,true);
//                        httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
//                            'Content-Type: application/json; charset=utf-8',
//                            'Content-Length:' . strlen($post_data),
//                            'Cache-Control: no-cache',
//                            'Pragma: no-cache'
//                        ));
//                        $status = 1;
//                        $res = file_get_contents('https://rptjm.jxtebie.com/sms/report?spid=254560&password=J6Dtc4HO');
//                        $res = simplexml_load_string($res);
//                        if(empty($res)){
//                            #发送失败
//                            $status = 0;
//                        }
//                        Db::name('website_inquiry_share_log')->insert(['inquiry_id'=>intval($dat['id']),'name'=>$other_merch['name'],'notice_type'=>2,'status'=>$status,'error_msg'=>json_encode($res,true)]);
//                    }
//                    if(strstr($dat['notice_type'],'3')){
                        #站内消息
//                        Db::name('website_station_msg')->insert(['uid'=>$other_id,'is_other'=>1,'msg'=>'点击链接查看询价信息：https://www.gogo198.net/?s=index/quote_info&id='.intval($dat['id']),'createtime'=>$time]);
                        Db::name('website_inquiry_share_log')->insert(['inquiry_id'=>intval($dat['id']),'name'=>$other_merch['name'],'notice_type'=>3,'status'=>1]);
//                    }
                }else{
                    #系统商户
                    if(strstr($dat['notice_type'],'1')){
                        #邮箱
                        $res = httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$merch['email'],'title'=>'询价信息','content'=>'点击链接查看询价信息：https://www.gogo198.net/?s=index/quote_info&id='.intval($dat['id'])]);
                        $status = 1;
                        if(json_decode($res,true)['status']==0){
                            #发送失败
                            $status = 0;
                        }
                        Db::name('website_inquiry_share_log')->insert(['inquiry_id'=>intval($dat['id']),'name'=>$merch['realname'],'notice_type'=>1,'status'=>$status]);
                    }
                    if(strstr($dat['notice_type'],'2')){
                        #手机
                        $post_data = [
                            'spid'=>'254560',
                            'password'=>'J6Dtc4HO',
                            'ac'=>'1069254560',
                            'mobiles'=>$merch['phone'],
                            'content'=>'点击链接查看询价信息：https://www.gogo198.net/?s=index/quote_info&id='.intval($dat['id']).'【GOGO】',
                        ];
                        $post_data = json_encode($post_data,true);
                        httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                            'Content-Type: application/json; charset=utf-8',
                            'Content-Length:' . strlen($post_data),
                            'Cache-Control: no-cache',
                            'Pragma: no-cache'
                        ));
                        $status = 1;
                        $res = file_get_contents('https://rptjm.jxtebie.com/sms/report?spid=254560&password=J6Dtc4HO');
                        $res = simplexml_load_string($res);
                        if(empty($res)){
                            #发送失败
                            $status = 0;
                        }
                        Db::name('website_inquiry_share_log')->insert(['inquiry_id'=>intval($dat['id']),'name'=>$merch['realname'],'notice_type'=>2,'status'=>$status,'error_msg'=>json_encode($res,true)]);
                    }
                    if(strstr($dat['notice_type'],'3')){
                        #站内消息
                        Db::name('website_station_msg')->insert(['uid'=>$v,'is_other'=>0,'msg'=>'点击链接查看询价信息：https://www.gogo198.net/?s=index/quote_info&id='.intval($dat['id']),'createtime'=>$time]);
                        Db::name('website_inquiry_share_log')->insert(['inquiry_id'=>intval($dat['id']),'name'=>$merch['realname'],'notice_type'=>3,'status'=>1]);
                    }
                }
            }


//            if($dat['notice_type']==1){
//                #邮箱通知
//                if(!empty($dat['merch_id'])){
//                    $dat['merch_id'] = explode(',',$dat['merch_id']);
//                    foreach($dat['merch_id'] as $k=>$v){
//                        $user = Db::name('website_user')->where(['id'=>$v])->find();
//                        httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$user['email'],'title'=>'询价信息','content'=>'点击链接查看询价信息：https://www.gogo198.net/?s=index/quote_info&id='.intval($dat['id'])]);
//                    }
//                }
//                if(!empty($dat['other_contact'])){
//                    $dat['other_contact'] = str_replace('、',';',$dat['other_contact']);
////                    foreach($dat['other_contact'] as $k=>$v){
//                        httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$dat['other_contact'],'title'=>'询价信息','content'=>'点击链接查看询价信息：https://www.gogo198.net/?s=index/quote_info&id='.intval($dat['id'])]);
////                    }
//                }
//            }
//            elseif($dat['notice_type']==2){
//                #手机通知
//                if(!empty($dat['merch_id'])){
//                    $dat['merch_id'] = explode(',',$dat['merch_id']);
//                    foreach($dat['merch_id'] as $k=>$v){
//                        $user = Db::name('website_user')->where(['id'=>$v])->find();
//                        $post_data = [
//                            'spid'=>'254560',
//                            'password'=>'J6Dtc4HO',
//                            'ac'=>'1069254560',
//                            'mobiles'=>$user['phone'],
//                            'content'=>'点击链接查看询价信息：https://www.gogo198.net/?s=index/quote_info&id='.intval($dat['id']).'【GOGO】',
//                        ];
//                        $post_data = json_encode($post_data,true);
//                        $res = httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
//                            'Content-Type: application/json; charset=utf-8',
//                            'Content-Length:' . strlen($post_data),
//                            'Cache-Control: no-cache',
//                            'Pragma: no-cache'
//                        ));
//                    }
//                }
//                if(!empty($dat['other_contact'])){
//                    $dat['other_contact'] = str_replace('、',',',$dat['other_contact']);
////                    foreach($dat['other_contact'] as $k=>$v){
//                        $post_data = [
//                            'spid'=>'254560',
//                            'password'=>'J6Dtc4HO',
//                            'ac'=>'1069254560',
//                            'mobiles'=>$dat['other_contact'],
//                            'content'=>'点击链接查看询价信息：https://www.gogo198.net/?s=index/quote_info&id='.intval($dat['id']).'【GOGO】',
//                        ];
//                        $post_data = json_encode($post_data,true);
//                        $res = httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
//                            'Content-Type: application/json; charset=utf-8',
//                            'Content-Length:' . strlen($post_data),
//                            'Cache-Control: no-cache',
//                            'Pragma: no-cache'
//                        ));
////                    }
//                }
//            }

            return json(['code'=>0,'msg'=>'分享询价成功！']);
        }else{
            #系统商户
            $merch = Db::name('website_user')->where(['merch_status'=>2])->select();
            $system_merch = [];
            foreach($merch as $k=>$v){
                $system_merch[$k]['value'] = $v['id'];
                $phone = '无';
                $email = '无';
                if($v['phone']){$phone = $v['phone'];}
                if($v['email']){$email = $v['email'];}
                $system_merch[$k]['name'] = $v['realname'].'（手机:'.$phone.'，邮箱:'.$email.'）';
            }
            $all_merch[0]['name'] = '系统商户';
            $all_merch[0]['value'] = '0';
            $all_merch[0]['children'] = $system_merch;
            #其他商户
            $other_merch = Db::name('website_other_merch')->select();
            $other_merch_origin = ['name'=>'添加接口商户', 'value'=>-2];
            if(!empty($other_merch)){
                $other_merch2 = [];
                foreach($other_merch as $k=>$v){
                    $other_merch2[$k]['value'] = 'O'.$v['id'];
//                    $phone = '无';
//                    $email = '无';
//                    if($v['phone']){$phone = $v['phone'];}
//                    if($v['email']){$email = $v['email'];}
//                    .'（手机:'.$phone.'，邮箱:'.$email.'）'
                    $other_merch2[$k]['name'] = $v['name'];
                }
                $other_merch_origin = array_merge([$other_merch_origin],$other_merch2);
            }
            $all_merch[1]['name'] = '接口商户';
            $all_merch[1]['value'] = '0';
            $all_merch[1]['children'] = $other_merch_origin;
            $all_merch = json_encode($all_merch,true);
            #通知方式
            $notice_type[0]['name'] = '电子邮箱';
            $notice_type[0]['id'] = '1';
            $notice_type[1]['name'] = '手机讯息';
            $notice_type[1]['id'] = '2';
            $notice_type[2]['name'] = '站内消息';
            $notice_type[2]['id'] = '3';
            $notice_type = json_encode($notice_type,true);
            return view('',compact('all_merch','id','notice_type'));
        }
    }

    public function share_inquiry_log(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        $log = Db::name('website_inquiry_share_log')->where(['inquiry_id'=>$id])->select();
        foreach($log as $k=>$v){
            if($v['notice_type']==1){
                $log[$k]['notice_type'] = '电子邮箱';
            }elseif($v['notice_type']==2){
                $log[$k]['notice_type'] = '手机讯息';
            }elseif($v['notice_type']==3){
                $log[$k]['notice_type'] = '站内消息';
            }

            if($v['status']==1){
                $log[$k]['status_name'] = '成功';
            }elseif($v['status']==0){
                $log[$k]['status_name'] = '失败';
                $log[$k]['error_msg'] = '原因：该通知方式['.$log[$k]['notice_type'].']的数据为空或有误';
            }
        }
        return view('',compact('log','id'));
    }

    #催单
    public function reminder(Request $request){
        $dat = input();
        $id = $dat['id'];

        $user = Db::name('website_inquiry_order')
            ->alias('a')
            ->join('website_quote_order b','a.quote_id=b.id')
            ->join('website_user c','c.id=b.uid')
            ->where(['a.id'=>$id])
            ->field(['c.openid','c.phone','c.email','a.ordersn','b.id','b.uid'])
            ->find();

        if(!empty($user['email'])){
            httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$user['email'],'title'=>'在线接单','content'=>'询价单号['.$user['ordersn'].']已下单，请尽快点击链接进行确认接单：https://www.gogo198.net/?s=index/quote_info&uid='.$user['uid'].'&id='.$user['id']]);
        }elseif(!empty($user['phone'])){
            $post_data = [
                'spid'=>'254560',
                'password'=>'J6Dtc4HO',
                'ac'=>'1069254560',
                'mobiles'=>$user['phone'],
                'content'=>'询价单号['.$user['ordersn'].']已下单，请尽快点击链接进行确认接单：https://www.gogo198.net/?s=index/quote_info&uid='.$user['uid'].'&id='.$user['id'].' 【GOGO】',
            ];
            $post_data = json_encode($post_data,true);
            httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length:' . strlen($post_data),
                'Cache-Control: no-cache',
                'Pragma: no-cache'
            ));
        }

        return json(['code'=>0,'msg'=>'催单成功']);
    }

    #报价管理中心
    public function quote_order(Request $request){
        $dat = input();

        if($request->isPost() || $request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('website_quote_order')->order($order)->count();
            $rows = DB::name('website_quote_order')
                ->alias('a')
                ->join('website_user b','b.id=a.uid')
                ->join('website_inquiry_order c','c.id=a.inquiry_id')
                ->field(['a.*','b.custom_id','c.ordersn','c.buss_id','c.buss_pid','a.is_notice'])
                ->limit($limit)
                ->order($order)
                ->select();

            $status = [0=>'未报价',1=>'已报价',2=>'已下单',3=>'已接单'];
            foreach($rows as $k=>$v){
                $rows[$k]['buss_name'] = Db::name('website_bussiness')->where(['id'=>$v['buss_pid']])->find()['name'];
                $buss_name2 = Db::name('website_inquiry_form')->where(['id'=>$v['buss_id']])->find()['name'];
                $rows[$k]['buss_name'] .= '-'.$buss_name2;
                $rows[$k]['statusname'] = $status[$v['status']];
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    #报价详情
    public function quote_order_detail(Request $request){
        $dat = input();
        $id = $dat['id'];
        if($request->isPost() || $request->isAjax()){
            #整理说明文本索引
            $quote_text1 = $quote_text2 = $quote_text2_edit = [];
            foreach($dat['quote_text1'] as $k=>$v){
                $quote_text1[] = $v;
                $quote_text2[] = $dat['quote_text2'][$k];
                $quote_text2_edit[] = $dat['quote_text2_edit'][$k];
            }
            $dat['quote_text1'] = $quote_text1;
            $dat['quote_text2'] = $quote_text2;
            $dat['quote_text2_edit'] = $quote_text2_edit;
            #整理数据
            $insert_data = [];
            foreach($dat['project_name'] as $k=>$v){
                #项目名称
                $insert_data['project_name'][] = $v;
                #项目随机名
                $insert_data['project_randname'][] = $dat['project_randname'][$k];
                #收费类型
                $insert_data['project_type'][] = $dat['project_type'][$k];
                #币种
                $insert_data['currency'][]     = $dat['currency'][$k];
                #价格
                $insert_data['money'][]        = $dat['money'][$k];
                #计价单位类型
                $insert_data['unit_type'][]    = $dat['unit_type'][$k];
                #选择表-单位
                $insert_data['unit'][]         = $dat['unit'][$k];
                #自定义-单位
                $insert_data['unit_name'][]    = $dat['unit_name'][$k];
                #报价说明类型
                $insert_data['quote_type'][]   = $dat['quote_type'][$k];
                #报价说明文本
                $insert_data['remark'][]       = $dat['remark'][$k];
                #日期类型
                $insert_data['date_type'][]    = $dat['date_type'][$k];
                #日期id随机
                $insert_data['date_num'][]     = $dat['date_num'][$k];
                #日期值
                $insert_data['date'][]         = $dat['date'][$k];
                #适用范围类型
                $insert_data['range_type'][]   = $dat['range_type'][$k];
                #适用国地类型
                $insert_data['country_type'][] = $dat['country_type'][$k];
                #选择表-适用国地
                $insert_data['country'][]      = $dat['country'][$k];
                #自定义-适用国地
                $insert_data['country_name'][] = $dat['country_name'][$k];
                #适用货物类型
                $insert_data['value_type'][]   = $dat['value_type'][$k];
                #选择表-适用货物
                $insert_data['value'][]        = $dat['value'][$k];
                #自定义-适用货物
                $insert_data['value_name'][]   = $dat['value_name'][$k];
                #收费说明类型
                $insert_data['feedesc_type'][] = $dat['feedesc_type'][$k];
                #自定义-说明文本
                $insert_data['quote_text1'][]  = $dat['quote_text1'][$k];
                #选择表-选择文本
                $insert_data['quote_text2'][]  = $dat['quote_text2'][$k];
                #选择表-选择文本-修改
                $insert_data['quote_text2_edit'][]  = $dat['quote_text2_edit'][$k];
            }
            $content = [];
            foreach($insert_data['project_name'] as $k=>$v){
                array_push($content,[
                    'project_name'=>$v,
                    'project_randname'=>$insert_data['project_randname'][$k],
                    'project_type'=>$insert_data['project_type'][$k],
                    'currency'=>$insert_data['project_type'][$k]==1?$insert_data['currency'][$k]:'',
                    'money'=>$insert_data['project_type'][$k]==1?trim($insert_data['money'][$k]):'',
                    'unit_type'=>$insert_data['project_type'][$k]==1?$insert_data['unit_type'][$k]:'',
                    'unit'=>$insert_data['project_type'][$k]==1?$insert_data['unit_type'][$k]==1?$insert_data['unit'][$k]:'':'',
                    'unit_name'=>$insert_data['project_type'][$k]==1?$insert_data['unit_type'][$k]==2?trim($insert_data['unit_name'][$k]):'':'',
                    'quote_type'=>$insert_data['project_type'][$k]==1?$insert_data['quote_type'][$k]:'',
                    'remark'=>$insert_data['project_type'][$k]==1?$insert_data['quote_type'][$k]==1?trim($insert_data['remark'][$k]):'':'',
                    'date_type'=>$insert_data['project_type'][$k]==1?$insert_data['quote_type'][$k]==2?$insert_data['date_type'][$k]:'':'',
                    'date_num'=>$insert_data['project_type'][$k]==1?$insert_data['quote_type'][$k]==2?trim($insert_data['date_num'][$k]):'':'',
                    'date'=>$insert_data['project_type'][$k]==1?$insert_data['quote_type'][$k]==2?trim($insert_data['date'][$k]):'':'',
                    'range_type'=>$insert_data['project_type'][$k]==1?$insert_data['quote_type'][$k]==3?$insert_data['range_type'][$k]:'':'',
                    'country_type'=>$insert_data['project_type'][$k]==1?$insert_data['range_type'][$k]==1?$insert_data['country_type'][$k]:'':'',
                    'country'=>$insert_data['project_type'][$k]==1?$insert_data['range_type'][$k]==1?$insert_data['country_type'][$k]==1?trim($insert_data['country'][$k]):'':'':'',
                    'country_name'=>$insert_data['project_type'][$k]==1?$insert_data['range_type'][$k]==1?$insert_data['country_type'][$k]==2?trim($insert_data['country_name'][$k]):'':'':'',
                    'value_type'=>$insert_data['project_type'][$k]==1?$insert_data['range_type'][$k]==2?$insert_data['value_type'][$k]:'':'',
                    'value'=>$insert_data['project_type'][$k]==1?$insert_data['range_type'][$k]==2?$insert_data['value_type'][$k]==1?$insert_data['value'][$k]:'':'':'',
                    'value_name'=>$insert_data['project_type'][$k]==1?$insert_data['range_type'][$k]==2?$insert_data['value_type'][$k]==2?trim($insert_data['value_name'][$k]):'':'':'',
                    'feedesc_type'=>$insert_data['project_type'][$k]==2?$insert_data['feedesc_type'][$k]:'',
                    'quote_text1'=>$insert_data['project_type'][$k]==2?$insert_data['feedesc_type'][$k]==1?json_encode($insert_data['quote_text1'][$k],true):'':'',
                    'quote_text2'=>$insert_data['project_type'][$k]==2?$insert_data['feedesc_type'][$k]==2?json_encode($insert_data['quote_text2'][$k],true):'':'',
                    'quote_text2_edit'=>$insert_data['project_type'][$k]==2?$insert_data['feedesc_type'][$k]==2?json_encode($insert_data['quote_text2_edit'][$k],true):'':'',
                ]);
            }
            #如果有说明文本则插入数据表
            foreach($insert_data['quote_text1'] as $k=>$v){
                foreach($v as $k2=>$v2){
                    $ishave = Db::name('website_quote_textlist')->where(['text'=>$v2])->find();
                    if(!empty($v2) && empty($ishave)){
                        Db::name('website_quote_textlist')->insert(['uid'=>0,'text'=>$v2]);
                    }
                }
            }
            #修改数据表
            $res = Db::name('website_quote_order')->where(['id'=>$dat['id']])->update([
                'content'=>json_encode($content,true),
//                'label5_content'=>isset($dat['label5_content'])?json_encode($dat['label5_content'],true):''
            ]);
            if($res){
                return json(['code'=>0,'msg'=>'修改成功']);
            }
        }else{
            $data = Db::name('website_quote_order')
                ->alias('a')
                ->join('website_inquiry_order b','b.id=a.inquiry_id')
                ->join('website_user c','c.id=b.uid')
                ->join('website_inquiry_form d','d.id=b.buss_id')
                ->where(['a.id'=>$id])
                ->field(['b.content','b.files','d.content as form','b.text','b.buss_pid','b.buss_id','b.uid','b.label5_select','b.label5_content','a.id','b.ordersn','a.content as quote_content','a.quote_formid','a.text as quote_text','a.is_notice','a.status','a.label5_select as quote_label5_select','a.label5_content as quote_label5_content','b.is_quote','a.uid as quote_uid'])
                ->find();
            if(empty($data)){
                $data = Db::name('website_quote_order')
                    ->alias('a')
                    ->join('website_inquiry_order b','b.id=a.inquiry_id')
                    ->join('website_inquiry_form d','d.id=b.buss_id')
                    ->where(['a.id'=>$id])
                    ->field(['b.content','b.files','d.content as form','b.text','b.buss_pid','b.buss_id','b.uid','b.label5_select','b.label5_content','a.id','b.ordersn','a.content as quote_content','a.quote_formid','a.text as quote_text','a.is_notice','a.status','a.label5_select as quote_label5_select','a.label5_content as quote_label5_content','b.is_quote','a.uid as quote_uid'])
                    ->find();
            }
//            $data = Db::name('website_inquiry_order')
//                ->alias('a')
//                ->join('website_quote_order e','e.inquiry_id=a.id')
//                ->join('website_quote_form f','f.id=e.quote_formid')
//                ->join('website_bussiness b','b.id=a.buss_id')
//                ->join('website_inquiry_form c','c.pid=a.buss_id')
//                ->join('website_user d','d.id=a.uid')
//                ->where(['e.id'=>$id])
//                ->field(['a.content','c.content as form','a.uid','e.id','a.ordersn','e.content as quote_content','e.is_notice','f.content as quote_form','e.status'])
//                ->find();

            #询价--start
            if(!empty($data['content'])){
                $data['content'] = json_decode($data['content'],true);
                if($data['is_quote']==1){
                    foreach($data['content'] as $k=>$v){
                        if(!empty($v['quote_text1'])){
                            $data['content'][$k]['quote_text1'] = json_decode($v['quote_text1'],true);
                        }
                        if(!empty($v['quote_text2'])){
                            $data['content'][$k]['quote_text2'] = json_decode($v['quote_text2'],true);
                            foreach($data['content'][$k]['quote_text2'] as $k2=>$v2){
                                $data['content'][$k]['quote_text2'][$k2] = Db::name('website_quote_textlist')->where(['id'=>$v2])->find()['text'];
                            }
                        }
                        if(!empty($v['quote_text2_edit'])){
                            $data['content'][$k]['quote_text2_edit'] = json_decode($v['quote_text2_edit'],true);
                        }
                        $data['content'][$k]['currency'] = Db::name('currency')->where(['code_value'=>$v['currency']])->find()['code_name'];
                        $data['content'][$k]['unit'] = Db::name('unit')->where(['code_value'=>$v['unit']])->find()['code_name'];
                        $data['content'][$k]['country'] = Db::name('country_code')->where(['code_value'=>$v['country']])->find()['code_name'];
                        $data['content'][$k]['value'] = Db::name('centralize_value_list')->where(['id'=>$v['value']])->find()['name'];
                    }
                }
            }
            $data['form'] = json_decode($data['form'],true);
            if(!empty($data['files'])){
                $data['files'] = json_decode($data['files'],true);
            }
            if(!empty($data['label5_select'])){
                $data['label5_select'] = json_decode($data['label5_select'],true);
                $data['label5_content'] = json_decode($data['label5_content'],true);
            }
            if(!empty($data['quote_label5_select'])){
                $data['quote_label5_select'] = json_decode($data['quote_label5_select'],true);
                $data['quote_label5_content'] = json_decode($data['quote_label5_content'],true);
            }
            $data['inquiry_user'] = '';
            #查询询价客户ID
            if(!empty($data['uid'])){
                $data['inquiry_user'] = Db::name('website_user')->where(['id'=>$data['uid']])->find();
            }

            #询价--end
            #报价--start
            $data['quote_content'] = json_decode($data['quote_content'],true);
            if(!empty($data['quote_content'])){
                foreach($data['quote_content'] as $k=>$v){
                    if(!empty($v['quote_text1'])){
                        $data['quote_content'][$k]['quote_text1'] = json_decode($v['quote_text1'],true);
                    }
                    if(!empty($v['quote_text2'])){
                        $data['quote_content'][$k]['quote_text2'] = json_decode($v['quote_text2'],true);
//                        foreach($data['quote_content'][$k]['quote_text2'] as $k2=>$v2){
//                            $data['quote_content'][$k]['quote_text2'][$k2] = Db::name('website_quote_textlist')->where(['id'=>$v2])->find()['text'];
//                        }
                    }
                    if(!empty($v['quote_text2_edit'])){
                        $data['quote_content'][$k]['quote_text2_edit'] = json_decode($v['quote_text2_edit'],true);
                    }
//                    $data['quote_content'][$k]['currency'] = Db::name('currency')->where(['code_value'=>$v['currency']])->find()['code_name'];
//                    $data['quote_content'][$k]['unit'] = Db::name('unit')->where(['code_value'=>$v['unit']])->find()['code_name'];
//                    $data['quote_content'][$k]['country'] = Db::name('country_code')->where(['code_value'=>$v['country']])->find()['code_name'];
//                    $data['quote_content'][$k]['value'] = Db::name('centralize_value_list')->where(['id'=>$v['value']])->find()['name'];
                }
            }
            if($data['quote_formid']!=-2){
                $data['quote_form'] = Db::name('website_quote_form')->where(['id'=>$data['quote_formid']])->find();
//                $data['quote_form'] = json_decode($data['quote_form'],true);
//                foreach($data['quote_form'] as $k=>$v){
//                    if($v['label_value']==4){
//                        $data['quote_form'][$k]['label_select2'] = explode('、',$v['label_select']);
//                    }
//                }
            }
            #报价客户ID
            $data['quote_user'] = Db::name('website_user')->where(['id'=>$data['quote_uid']])->find();
            #报价--end
            #币种
            $currency = Db::name('currency')->select();
            #币种的人民币和美元排在前列
            foreach($currency as $k=>$v){
                if($v['code_value']==142){
                    $num0_value = $currency[0]['code_value'];
                    $num0_name = $currency[0]['code_name'];
                    $currency[0]['code_value'] = $v['code_value'];
                    $currency[0]['code_name'] = $v['code_name'];
                    $currency[$k]['code_value'] = $num0_value;
                    $currency[$k]['code_name'] = $num0_name;
                }elseif($v['code_value']==502){
                    $num0_value = $currency[1]['code_value'];
                    $num0_name = $currency[1]['code_name'];
                    $currency[1]['code_value'] = $v['code_value'];
                    $currency[1]['code_name'] = $v['code_name'];
                    $currency[$k]['code_value'] = $num0_value;
                    $currency[$k]['code_name'] = $num0_name;
                }
            }
            #单位
            $unit = Db::name('unit')->select();
            #国地
            $country = Db::name('country_code')->where('code_name','<>','无')->select();
            #货物属性
            $value = Db::name('centralize_value_list')->select();
            #说明文本
            $quote_text = Db::name('website_quote_textlist')->order('id','desc')->select();

            return view('',compact('data','currency','unit','country','country','value','quote_text'));
        }
    }

    #报价单通知买家
    public function notice_buyer(Request $request){
        $dat = input();

        if($dat['pa']==2){
            #目前该询价单还未有买家id
            $quote = Db::name('website_quote_order')->where(['id'=>$dat['id']])->find();
            if($dat['notice_type']==1){
                $res = httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>trim($dat['contact_num']),'title'=>'报价信息','content'=>'尊敬的客户，您有新的报价单通知，请点击链接查看报价详情：https://www.gogo198.net/?quote_id='.$quote['id'].'&s=index/quote_detail']);
            }elseif($dat['notice_type']==2){
                $post_data = [
                    'spid'=>'254560',
                    'password'=>'J6Dtc4HO',
                    'ac'=>'1069254560',
                    'mobiles'=>trim($dat['contact_num']),
                    'content'=>'尊敬的客户，您有新的报价单通知，请点击链接查看报价详情：https://www.gogo198.net/?quote_id='.$quote['id'].'&s=index/quote_detail 【GOGO】',
                ];
                $post_data = json_encode($post_data,true);
                $res = httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length:' . strlen($post_data),
                    'Cache-Control: no-cache',
                    'Pragma: no-cache'
                ));
            }
            if($res){
                Db::name('website_quote_order')->where(['id'=>$dat['id']])->update(['is_notice'=>1]);
                Db::name('website_inquiry_order')->where(['id'=>$quote['inquiry_id']])->update(['status'=>1]);
                return json(['code'=>0,'msg'=>'通知成功']);
            }else{
                return json(['code'=>-1,'msg'=>'通知失败']);
            }
        }else{
            #已有买家id
            $user = Db::name('website_quote_order')
                ->alias('a')
                ->join('website_inquiry_order b','b.id=a.inquiry_id')
                ->join('website_user c','c.id=b.uid')
                ->where(['a.id'=>$dat['id']])
                ->field(['c.email','c.phone','b.ordersn'])
                ->find();

            if(!empty($user['email'])){
                $res = httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$user['email'],'title'=>'报价信息','content'=>'尊敬的客户，您的询价单['.$user['ordersn'].']有新的报价，请点击链接进入账户管理下的询价中心查看报价详情：https://www.gogo198.net/?s=index/account_manage']);
            }elseif(!empty($user['phone'])){
                $post_data = [
                    'spid'=>'254560',
                    'password'=>'J6Dtc4HO',
                    'ac'=>'1069254560',
                    'mobiles'=>$user['phone'],
                    'content'=>'尊敬的客户，您的询价单['.$user['ordersn'].']有新的报价，请点击链接进入账户管理下的询价中心查看报价详情：https://www.gogo198.net/?s=index/account_manage 【GOGO】',
                ];
                $post_data = json_encode($post_data,true);
                $res = httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length:' . strlen($post_data),
                    'Cache-Control: no-cache',
                    'Pragma: no-cache'
                ));
            }

            if($res){
                Db::name('website_quote_order')->where(['id'=>$dat['id']])->update(['is_notice'=>1]);
                Db::name('website_inquiry_order')->where(['ordersn'=>$user['ordersn']])->update(['status'=>1]);
                return json(['code'=>0,'msg'=>'通知成功']);
            }else{
                return json(['code'=>-1,'msg'=>'通知失败']);
            }
        }
    }

    #获取选择的报价文本
    public function get_quotetext(Request $request){
        $dat = input();

        $res = Db::name('website_quote_textlist')->where(['id'=>$dat['id']])->find();
        if($res){
            return json(['code'=>0,'data'=>$res]);
        }
    }

    #保存收费文本的修改信息
    public function save_quotetext(Request $request){
        $dat = input();
        $val = trim($dat['val']);
        $ishave = Db::name('website_quote_textlist')->where(['text'=>$val])->find();
        if($ishave['id']>0){
            return json(['code'=>-1,'msg'=>'文本修改失败，系统中已存在相同文本']);
        }
        $res = Db::name('website_quote_textlist')->insertGetId(['uid'=>0,'text'=>$val]);
        if($res){
            $new_select = Db::name('website_quote_textlist')->order('id','desc')->select();
            return json(['code'=>0,'msg'=>'修改成功','id'=>$res,'new_select'=>$new_select]);
        }
    }

    #报价项目咨询
    public function quote_order_chat(Request $request){
        $dat = input();
        if($request->isPost() || $request->isAjax()){
            if($dat['pa']==1){
                #查找聊天记录
                if(isset($dat['is_inquiry'])){
                    $list = Db::name('website_quote_chat')->where(['inquiry_id'=>$dat['id'],'project_randname'=>$dat['project_randname']])->where('uid=0 or uid=1')->field('createtime')->order('id','asc')->select();
                }else{
                    $list = Db::name('website_quote_chat')->where(['quote_id'=>$dat['id'],'project_randname'=>$dat['project_randname']])->where('uid=0 or uid=2')->field('createtime')->order('id','asc')->select();
                }
                $chat_group = [];
                if(!empty($list)){
                    if(isset($dat['is_inquiry'])){
                        Db::name('website_quote_chat')->where(['inquiry_id'=>$dat['id'],'project_randname'=>$dat['project_randname'],'who_send'=>2])->where('uid=0 or uid=1')->update(['is_read'=>1]);
                    }else{
                        Db::name('website_quote_chat')->where(['quote_id'=>$dat['id'],'project_randname'=>$dat['project_randname'],'who_send'=>2])->where('uid=0 or uid=2')->update(['is_read'=>1]);
                    }
                    #聊天记录以时间为数组
                    $group = [];
                    foreach($list as $k=>$v){
                        $time = date('Y-m-d',$v['createtime']);
                        if(empty($group)){
                            $group = array_merge($group,[$time]);
                        }else{
                            if(!in_array($time,$group)){
                                $group = array_merge($group,[$time]);
                            }
                        }
                    }
                    sort($group);
                    #根据时间查找聊天记录
                    foreach($group as $k=>$v){
                        $starttime = strtotime($v.' 00:00:00');
                        $endtime = strtotime($v.' 23:59:59');
                        $chat_group[$k]['time'] = date('Y年m月d日',$starttime);
                        if(isset($dat['is_inquiry'])){
                            $chat_group[$k]['info'] = Db::name('website_quote_chat')->where(['inquiry_id'=>$dat['id'],'project_randname'=>$dat['project_randname']])->where('uid=0 or uid=1')->whereBetween('createtime',[$starttime,$endtime])->order('createtime','asc')->select();
                        }else{
                            $chat_group[$k]['info'] = Db::name('website_quote_chat')->where(['quote_id'=>$dat['id'],'project_randname'=>$dat['project_randname']])->where('uid=0 or uid=2')->whereBetween('createtime',[$starttime,$endtime])->order('createtime','asc')->select();
                        }
                    }
                    #整理数组
                    foreach($chat_group as $k=>$v){
                        foreach($v['info'] as $kk=>$vv){
                            $chat_group[$k]['info'][$kk]['content'] = json_decode($vv['content'],true);
                            $chat_group[$k]['info'][$kk]['createtime'] = date('H:i',$vv['createtime']);
                        }
                    }
                }
                return json(['code'=>0,'id'=>$dat['id'],'project_randname'=>$dat['project_randname'],'data'=>$chat_group]);
            }elseif($dat['pa']==2){
                $dat['content'] = json_encode($dat['content'],true);
                if(isset($dat['is_inquiry'])){
                    $res = Db::name('website_quote_chat')->insert([
                        'inquiry_id'=>$dat['quote_id'],
                        'project_randname'=>$dat['project_randname'],
                        'who_send'=>1,
                        'uid'=>0,
                        'is_send'=>1,
                        'is_read'=>0,
                        'content_type'=>$dat['content_type'],
                        'content'=>$dat['content'],
                        'createtime'=>time(),
                    ]);
                }else{
                    $res = Db::name('website_quote_chat')->insert([
                        'quote_id'=>$dat['quote_id'],
                        'project_randname'=>$dat['project_randname'],
                        'who_send'=>1,
                        'uid'=>0,
                        'is_send'=>1,
                        'is_read'=>0,
                        'content_type'=>$dat['content_type'],
                        'content'=>$dat['content'],
                        'createtime'=>time(),
                    ]);
                }
                if($res){
                    #通知商户
//                    $quote = Db::name('website_quote_order')->where(['id'=>$dat['quote_id']])->find();
//                    $user = Db::name('website_user')->where(['id'=>$quote['id']])->find();
//                    if(!empty($user['email'])){
//                        $res = httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$user['email'],'title'=>'报价信息','content'=>'尊敬的客户，您的询价单['.$user['ordersn'].']有新的报价，请点击链接进入账户管理下的询价中心查看报价详情：https://www.gogo198.net/?s=index/account_manage']);
//                    }elseif(!empty($user['phone'])){
//                        $post_data = [
//                            'spid'=>'254560',
//                            'password'=>'J6Dtc4HO',
//                            'ac'=>'1069254560',
//                            'mobiles'=>$user['phone'],
//                            'content'=>'尊敬的客户，您的询价单['.$user['ordersn'].']有新的报价，请点击链接进入账户管理下的询价中心查看报价详情：https://www.gogo198.net/?s=index/account_manage 【GOGO】',
//                        ];
//                        $post_data = json_encode($post_data,true);
//                        $res = httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
//                            'Content-Type: application/json; charset=utf-8',
//                            'Content-Length:' . strlen($post_data),
//                            'Cache-Control: no-cache',
//                            'Pragma: no-cache'
//                        ));
//                    }

                    return json(['code'=>0,'msg'=>'提交成功']);
                }
            }
        }
    }

    #消息管理
    public function msg_manage(Request $request){
        $dat = input();

        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('website_message_manage')->order($order)->count();
            $rows = DB::name('website_message_manage')
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>$v){
                $rows[$k]['name'] = $v['name'];
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('');
        }
    }

    public function save_msg(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){

            if($id>0){
                $res = Db::name('website_message_manage')->where('id',$dat['id'])->update([
                    'name'=>trim($dat['name']),
                    'desc'=>trim($dat['desc']),
                ]);
            }
            else{
                $res = Db::name('website_message_manage')->insert([
                    'name'=>trim($dat['name']),
                    'desc'=>trim($dat['desc']),
                    'createtime'=>time()
                ]);
            }

            if($res){
                return json(['code' => 0, 'msg' => '保存成功']);
            }
        }else{
            $data = ['name'=>'','desc'=>''];
            if($id>0){
                $data = Db::name('website_message_manage')->where('id',$id)->find();
            }
//            dd($list);
            return view('',compact('id','data'));
        }
    }

    public function del_msg(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $res = Db::name('website_message_manage')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #数据维护管理
    public function disease_manage(Request $request){
        $dat = input();
        $system_id = $dat['system_id'];
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::connect($this->medical_config)->name('disease')->order($order)->count();
            $rows = DB::connect($this->medical_config)->name('disease')
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>$v){

            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('system_id'));
        }
    }

    public function save_disease(Request $request){}

    public function del_disease(Request $request){}

    #发现管理
    public function discovery_manage(Request $request){
        $dat = input();
        $system_id = $dat['system_id'];
        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('website_discovery_list')->where(['system_id'=>$system_id])->order($order)->count();
            $rows = DB::name('website_discovery_list')
                ->where(['system_id'=>$system_id])
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>&$v){
                $v['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('system_id'));
        }
    }

    public function save_discovery(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;
        if($request->isAjax()){
            $res = '';

            if($id>0){
                $res = Db::name('website_discovery_list')->where('id',$id)->update([
                    'descs'=>trim($dat['descs']),
                    'thumb'=>$dat['thumb'][0],
                    'mob_thumb'=>isset($dat['mob_thumb'][0])?$dat['mob_thumb'][0]:'',
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'other_pic'=>$dat['go_other']==3?trim($dat['other_pic']):'',
                    'other_msg'=>$dat['go_other']==4?trim($dat['other_msg']):'',
                    'other_keywords'=>$dat['go_other']==5?trim($dat['other_keywords']):'',
                    'system_id'=>$system_id
                ]);
            }else{
                $res = Db::name('website_discovery_list')->insertGetId([
                    'descs'=>trim($dat['descs']),
                    'thumb'=>$dat['thumb'][0],
                    'mob_thumb'=>isset($dat['mob_thumb'][0])?$dat['mob_thumb'][0]:'',
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'other_pic'=>$dat['go_other']==3?trim($dat['other_pic']):'',
                    'other_msg'=>$dat['go_other']==4?trim($dat['other_msg']):'',
                    'other_keywords'=>$dat['go_other']==5?trim($dat['other_keywords']):'',
                    'system_id'=>$system_id,
                    'createtime'=>time()
                ]);
                $id = $res;
            }

            if($res){
                if($dat['go_other']==5) {
                    if(!empty($dat['other_keywords'])){
                        $keywords_arr = explode('、',$dat['other_keywords']);
                        foreach($keywords_arr as $k2=>$v2) {
                            $ishave = Db::connect($this->config)->name('goods_keywords')->where(['keywords' => $v2])->find();
                            if (empty($ishave) && !empty($v2)) {
                                Db::connect($this->config)->name('goods_keywords')->insert(['keywords' => trim($v2)]);
                            }
                        }
//                        #新增时自动获取商品(队列服务)
//                        $options = array('http' => array('timeout' => 7500));
//                        $context = stream_context_create($options);
//                        file_get_contents('https://decl.gogo198.cn/api/v2/get_content_goods?type=5&id=' . $id, false, $context);
                    }
                }
                return json(['code'=>0,'msg'=>'保存成功！']);
            }else{
                return json(['code'=>0,'msg'=>'暂无修改~']);
            }
        }else{
            $data = ['descs'=>'','thumb'=>'','mob_thumb'=>'','go_other'=>'','other_link'=>'','other_navbar'=>'','other_pic'=>'','other_msg'=>'','other_keywords'=>''];
            if($id>0){
                $data = Db::name('website_discovery_list')->where('id',$id)->find();
            }

            $list='';
            if($system_id==1 || $system_id==4 || $system_id==3 || $system_id==8){
                $list = $this->menu($system_id);
            }elseif($system_id==2){
                $list = $this->get_gather_process();
            }

            #图文链接
            $pic_list = Db::name('website_image_txt')->select();
            foreach($pic_list as $k=>$v){
                $pic_list[$k]['name'] = json_decode($v['name'],'true')['zh'];
            }
            #消息链接
            $msg_list = Db::name('website_message_manage')->select();

            return view('',compact('data','id','list','system_id','pic_list','msg_list'));
        }
    }

    public function del_discovery(Request $request){
        $dat = input();
        $res = Db::name('website_discovery_list')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }

    #代购商城管理=========================================START
    public function pmall_manage(Request $request){
        $dat = input();
        $system_id = $dat['system_id'];
        $pid = isset($dat['pid'])?intval($dat['pid']):0;

        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::connect($this->medical_config)->name('mall')->where(['pid'=>$pid])->order($order)->count();
            $rows = DB::connect($this->medical_config)->name('mall')
                ->where(['pid'=>$pid])
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>&$v){

            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('system_id','pid'));
        }
    }

    public function save_pmall(Request $request){
        $dat = input();

        $id = isset($dat['id'])?$dat['id']:0;
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;
        $pid = isset($dat['pid'])?intval($dat['pid']):0;

        if($request->isAjax()){
            if($id>0){
                Db::connect($this->medical_config)->name('mall')->where('id',$id)->update([
                    'name'=>trim($dat['name']),
                    'bg_color1'=>$pid==0?trim($dat['bg_color1']):'',
                    'bg_color2'=>$pid==0?trim($dat['bg_color2']):'',
                    'img'=>$pid>0?$dat['img'][0]:'',
                ]);
            }else{
                Db::connect($this->medical_config)->name('mall')->insert([
                    'pid'=>$pid,
                    'name'=>trim($dat['name']),
                    'bg_color1'=>$pid==0?trim($dat['bg_color1']):'',
                    'bg_color2'=>$pid==0?trim($dat['bg_color2']):'',
                    'img'=>$pid>0?$dat['img'][0]:'',
                    'system_id'=>$system_id,
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功！']);
        }else{
            $data = ['name'=>'','bg_color1'=>'','bg_color2'=>'','img'=>''];
            if($id>0){
                $data = Db::connect($this->medical_config)->name('mall')->where('id',$id)->find();
            }

            return view('',compact('data','id','system_id','pid'));
        }
    }

    public function del_pmall(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        $res = Db::connect($this->medical_config)->name('mall')->where('id',$id)->delete();
        if($res){
            Db::connect($this->medical_config)->name('mall')->where(['pid'=>$id])->delete();
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }
    #代购商城管理============================================END

    #药物管理================================================START
    public function verify_data(Request $request){
        $dat = input();

        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::connect($this->medical_config)->name('goods_temp')->order($order)->count();
            $rows = DB::connect($this->medical_config)->name('goods_temp')
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>&$v){

            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }
    
    public function save_verify_data(request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        
        if($request->isAjax()){
            $res = Db::connect($this->medical_config)->name('goods_temp')->where(['id' => $id])->update([
                'country_id' => intval($dat['country_id']),
                'name' => trim($dat['name']),
                'descs' => trim($dat['descs']),
                'en_name' => trim($dat['en_name']),
                'py_name' => trim($dat['py_name']),
                'lx_name' => $dat['lx_name_sel'] != -1 ? $dat['lx_name_sel'] : trim($dat['lx_name']),
                'cf_name' => $dat['cf_name_sel'] != -1 ? $dat['cf_name_sel'] : trim($dat['cf_name']),
                'yb_name' => $dat['yb_name_sel'] != -1 ? $dat['yb_name_sel'] : trim($dat['yb_name']),
                'pp_name' => trim($dat['pp_name']),
                'lb_name' => $dat['lb_name_sel'] != -1 ? $dat['lb_name_sel'] : trim($dat['lb_name']),
            ]);
            if($res){
                foreach($dat['cate_ids'] as $k=>$v) {
                    if(!empty($v)){
                        Db::connect($this->medical_config)->name('goods_catelogue_temp')->where(['goods_id' => $id,'id'=>trim($v)])->update([
                            'name'=>$dat['cate_selids'][$k]!=-1?$dat['cate_selids'][$k]:trim($dat['cate_names'][$k]),
                            'descs'=>trim($dat['cate_descs'][$k]),
                        ]);
                    }else{
                        if(!empty($dat['cate_names'][$k]) || !empty($dat['cate_selids'][$k])){
                            Db::connect($this->medical_config)->name('goods_catelogue_temp')->insert([
                                'goods_id'=>$id,
                                'name'=>$dat['cate_selids'][$k]!=-1?$dat['cate_selids'][$k]:trim($dat['cate_names'][$k]),
                                'descs'=>trim($dat['cate_descs'][$k]),
                            ]);
                        }
                    }
                }
            }
        }else{
            $data = Db::connect($this->medical_config)->name('goods_temp')->where(['id'=>$id])->find();
            #用英文名搜爬到的物品表
            $origin_data = Db::connect($this->medical_config)->name('goods')->where(['en_name'=>$data['en_name']])->find();
            if(empty($origin_data) || $origin_data['en_name']=='N/A'){
                $origin_data = Db::connect($this->medical_config)->name('goods')->where(['name'=>$data['name']])->find();
            }
            if(!empty($origin_data)){
                #查找爬到的物品目标表
                $origin_data['catelogue_list'] = Db::connect($this->medical_config)->name('goods_catelogue')->where(['goods_id'=>$origin_data['id']])->select();
                foreach($origin_data['catelogue_list'] as $k=>$v){
                    $origin_data['catelogue_list'][$k]['descs'] = json_decode($v['descs'],true)['desc_content'];
                    $origin_data['catelogue_list'][$k]['descs'] = str_replace('<p>','',$origin_data['catelogue_list'][$k]['descs']);
                    $origin_data['catelogue_list'][$k]['descs'] = str_replace('</p>','',$origin_data['catelogue_list'][$k]['descs']);
                }
            }
            $data['catelogue_list'] = Db::connect($this->medical_config)->name('goods_catelogue_temp')->where(['goods_id'=>$id])->select();
            
            #类型=====START
            //药品类型
            $yplx_list = Db::connect($this->medical_config)->name('goods')->field('lx_name')->distinct(true)->select();
            $now_yplx_list = Db::connect($this->medical_config)->name('goods_temp')->field('lx_name')->distinct(true)->select();
            $type['yplx'] = remove_duplicate_values($yplx_list,$now_yplx_list,'lx_name');
            //处方类型
            $cflx_list = Db::connect($this->medical_config)->name('goods')->field('cf_name')->distinct(true)->select();
            $now_cflx_list = Db::connect($this->medical_config)->name('goods_temp')->field('cf_name')->distinct(true)->select();
            $type['cflx'] = remove_duplicate_values($cflx_list,$now_cflx_list,'cf_name');
            //医保类型
            $yblx_list = Db::connect($this->medical_config)->name('goods')->field('yb_name')->distinct(true)->select();
            $now_yblx_list = Db::connect($this->medical_config)->name('goods_temp')->field('yb_name')->distinct(true)->select();
            $type['yblx'] = remove_duplicate_values($yblx_list,$now_yblx_list,'yb_name');
            //药物类型
            $ywlx_list = Db::connect($this->medical_config)->name('goods')->field('lb_name')->distinct(true)->select();
            $now_ywlx_list = Db::connect($this->medical_config)->name('goods_temp')->field('lb_name')->distinct(true)->select();
            $type['ywlx'] = remove_duplicate_values($ywlx_list,$now_ywlx_list,'lb_name');
            #类型=====END
            
            #目录=====START
            $catelogue_list = Db::connect($this->medical_config)->name('goods_catelogue')->whereRaw('id < 14218 or id > 14809')->field('name')->distinct(true)->select();
            #合并新目录的信息
            $now_catelogue_list = Db::connect($this->medical_config)->name('goods_catelogue_temp')->field('name')->distinct(true)->select();
            #二维数组去除重复值
            $catelogue_list = remove_duplicate_values($catelogue_list,$now_catelogue_list,'name');
            #目录=====END
            
            return view('',compact('data','id','type','catelogue_list','origin_data'));
        }
    }
    
    public function del_verify_data(request $request){
        $dat = input();
        $id = intval($dat['id']);
        $res = Db::connect($this->medical_config)->name('goods_temp')->where(['id'=>$id])->delete();
        if($res){
            Db::connect($this->medical_config)->name('goods_catelogue_temp')->where(['goods_id'=>$id])->delete();
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
    #药物管理================================================END

    #授权登录配置
    public function authorization_manage(Request $request){
        $dat = input();

        // 排序
        $order = input('sort') . ' ' . input('order');
        // 分页
        $limit = input('offset') . ',' . input('limit');
        if( request()->isPost() || request()->isAjax()){
            $count = Db::name('website_authlogin_apps')->order($order)->count();
            $rows = DB::name('website_authlogin_apps')
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>&$v){

            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    public function save_authorization(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isAjax()){
            if($id>0){
                Db::name('website_authlogin_apps')->where(['id'=>$id])->update([
                   'icon'=>$dat['icon'][0],
                   'name'=>trim($dat['name']),
                   'url'=>trim($dat['url']),
                ]);
            }else{
                Db::name('website_authlogin_apps')->insert([
                    'icon'=>$dat['icon'][0],
                    'name'=>trim($dat['name']),
                    'url'=>trim($dat['url']),
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['icon'=>'','name'=>'','url'=>'','displayorder'=>0];
            if($id>0){
                $data = Db::name('website_authlogin_apps')->where(['id'=>$id])->find();
            }
            return view('',compact('data','id'));
        }
    }

    public function del_authorization(request $request){
        $dat = input();
        $id = intval($dat['id']);
        $res = Db::name('website_authlogin_apps')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
}