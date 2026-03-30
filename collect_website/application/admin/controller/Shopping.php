<?php
namespace app\admin\controller;

//use think\Controller;
use think\Request;
use think\Db;
use app\admin\controller;
use Excel5;
use PHPExcel;
use PHPExcel_IOFactory;
use think\Session;
use think\Log;

//extends Auth
class Shopping
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

    public function index(Request $request)
    {
        $dat = input();

        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            // 连接数据库
            $conn = Db::connect($this->config);
            $total = $conn->name('ssl_value')->where(['pid'=>0])->count();
            $data = $conn->name('ssl_value')
                ->where(['pid'=>0])
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {

            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        } else {
            return view('', ['title' => '']);
        }
    }

    public function save_value(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $pid = isset($dat['pid'])?$dat['pid']:0;
        $type = isset($dat['type'])?$dat['type']:0;

        if ($request->isAjax()) {
            $content = [];
            
            if($type==1){
                $cat_id = intval($dat['cate_id']);
                // if(isset($dat['cate_id3'])){
                //     $cat_id = $dat['cate_id3'];
                // }elseif(isset($dat['cate_id2'])){
                //     $cat_id = $dat['cate_id2'];
                // }elseif(isset($dat['cate_id1'])){
                //     $cat_id = $dat['cate_id1'];
                // }
                $content = ['name'=>trim($dat['name']),'good_category'=>isset($dat['good_category'])?$dat['good_category']:0,'cate_id'=>$cat_id];
            }else{
                $content = ['name'=>trim($dat['name']),'pid'=>$pid];
            }
            if($id>0){
                $res = Db::connect($this->config)->name('ssl_value')->where(['id'=>$id])->update($content);
            }else{
                $res = Db::connect($this->config)->name('ssl_value')->insertGetId($content);
                $id = $res;
            }
            
            if($type==1 && isset($dat['desc'])){
                if(!empty($dat['desc'])){
                    $desc = explode('、',$dat['desc']);
                    foreach($desc as $k=>$v){
                        if(!empty($v)){
                            $ishave = Db::connect($this->config)->name('ssl_value')->where(['pid'=>$id,'name'=>trim($v)])->find();
                            if(empty($ishave)){
                                Db::connect($this->config)->name('ssl_value')->insert(['pid'=>$id,'name'=>trim($v)]);
                            }
                        }
                    }
                }
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = ['name'=>''];
            if($id>0){
                $data = Db::connect($this->config)->name('ssl_value')->where(['id'=>$dat['id']])->find();
            }
            $good_category = Db::connect($this->config)->name('ssl_good_category')->select();
            return view('',compact('id','data','pid','good_category','type'));
        }
    }

    public function get_catechild(Request $request){
        $dat = input();
        $type_id = isset($dat['type_id'])?$dat['type_id']:0;
        $parent_id = isset($dat['parent_id'])?$dat['parent_id']:0;
        if($type_id==0){
            $list = Db::connect($this->config)->name('category')->where(['parent_id'=>$parent_id])->select();
        }else{
            $list = Db::connect($this->config)->name('category')->where(['type_id'=>$type_id,'parent_id'=>$parent_id])->select();
        }
        return json(['code'=>0,'list'=>$list]);
    }

    public function del_value(Request $request)
    {
        $dat = input();
        // 连接数据库
        $conn = Db::connect($this->config);
        $res = $conn->name('ssl_value')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    public function value_list(Request $request){
        $dat = input();
        $pid = $dat['pid'];
        $title = isset($dat['name'])?$dat['name']:'';
        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            // 连接数据库
            $conn = Db::connect($this->config);
            $total = $conn->name('ssl_value')->where(['pid'=>$pid])->count();
            $data = $conn->name('ssl_value')
                ->where(['pid'=>$pid])
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {

            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        } else {
            return view('', compact('pid','title'));
        }
    }

    #费用管理=============================================================================================
    public function cost_index(Request $request){
        $dat = input();

        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            // 连接数据库
            $conn = Db::connect($this->config);
            $total = $conn->name('cost_service')->where(['pid'=>0])->count();
            $data = $conn->name('cost_service')
                ->where(['pid'=>0])
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {
//                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        }else{
            return view('', ['title' => '']);
        }
    }
    public function save_cost(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            if($id>0){
                Db::connect($this->config)->name('cost_service')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name'])
                ]);
            }else{
                Db::connect($this->config)->name('cost_service')->insert([
                    'pid'=>$pid,
                    'name'=>trim($dat['name'])
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>''];
            if($id>0){
                $data = Db::connect($this->config)->name('cost_service')->where(['id'=>$id])->find();
            }
            return view('',compact('data','id','pid'));
        }
    }
    public function del_cost(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;

        $res = Db::connect($this->config)->name('cost_service')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>-1,'msg'=>'删除成功']);
        }
    }

    #费用列表
    public function cost_list(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?intval($dat['pid']):0;

        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            // 连接数据库
            $conn = Db::connect($this->config);
            $total = $conn->name('cost_service')->where(['pid'=>$pid,'company_id'=>0])->count();
            $data = $conn->name('cost_service')
                ->where(['pid'=>$pid,'company_id'=>0])
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {
//                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        }else{
            return view('', compact('pid'));
        }
    }
    #新增费用
    public function save_costs(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            $format = [];
            if(isset($dat['desc'])){
                if($dat['desc']=='on'){
                    $format['desc']=1;
                }
            }else{$format['desc']=0;}

            if(isset($dat['quality_commit'])){
                if($dat['quality_commit']=='on'){
                    $format['quality_commit']=1;
                }
            }else{$format['quality_commit']=0;}

            if(isset($dat['matters'])){
                if($dat['matters']=='on'){
                    $format['matters']=1;
                }
            }else{$format['matters']=0;}

            if(isset($dat['danxuan'])){
                if($dat['danxuan']=='on'){
                    $format['danxuan']=1;
                }
            }else{$format['danxuan']=0;}

            if(isset($dat['photo'])){
                if($dat['photo']=='on'){
                    $format['photo']=1;
                }
            }else{$format['photo']=0;}

            if($id>0){
                Db::connect($this->config)->name('cost_service')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'format'=>json_encode($format,true)
                ]);
            }else{
                Db::connect($this->config)->name('cost_service')->insert([
                    'pid'=>$pid,
                    'name'=>trim($dat['name']),
                    'format'=>json_encode($format,true)
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>'','format'=>['desc'=>1,'quality_commit'=>0,'matters'=>0,'danxuan'=>0,'photo'=>0]];
            if($id>0){
                $data = Db::connect($this->config)->name('cost_service')->where(['id'=>$id])->find();
                $data['format'] = json_decode($data['format'],true);
            }
            return view('',compact('data','id','pid'));
        }
    }

    #配置费用
    public function save_costs_fee(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $pid = isset($dat['pid'])?intval($dat['pid']):0;

        if($request->isAjax()){
            $is_select = 0;
            if(isset($dat['is_select'])){
                if($dat['is_select']=='on'){
                    $is_select=1;
                }
            }
            if($id>0){
                $ishave = Db::connect($this->config)->name('goods_services')->where(['service_id'=>$id,'company_id'=>0])->find();
                if(empty($ishave)){
                    Db::connect($this->config)->name('goods_services')->insert([
                        'service_id'=>$id,
                        'company_id'=>0,
                        'name'=>trim($dat['name']),
                        'desc'=>trim($dat['desc']),
                        'tips'=>trim($dat['tips']),
                        'type'=>intval($dat['type']),
                        'is_select'=>$is_select,
                        'currency'=>intval($dat['currency']),
                        'price'=>intval($dat['price']),
                        'num'=>intval($dat['num']),
                        'interval_price'=>$dat['interval_price'],
                        'diy_select'=>rtrim($dat['diy_select'],'、'),
                    ]);
                }else{
                    Db::connect($this->config)->name('goods_services')->where(['service_id'=>$id,'company_id'=>0])->update([
                        'name'=>trim($dat['name']),
                        'desc'=>trim($dat['desc']),
                        'tips'=>trim($dat['tips']),
                        'type'=>intval($dat['type']),
                        'is_select'=>$is_select,
                        'currency'=>intval($dat['currency']),
                        'price'=>intval($dat['price']),
                        'num'=>intval($dat['num']),
                        'interval_price'=>$dat['interval_price'],
                        'diy_select'=>rtrim($dat['diy_select'],'、'),
                    ]);
                }
            }
            else{
                $ins_id = Db::connect($this->config)->name('cost_service')->insertGetId([
                    'company_id'=>0,
                    'pid'=>$pid,
                    'name'=>trim($dat['name']),
                ]);
                Db::connect($this->config)->name('goods_services')->insert([
                    'service_id'=>$ins_id,
                    'company_id'=>0,
                    'name'=>trim($dat['name']),
                    'desc'=>trim($dat['desc']),
                    'tips'=>trim($dat['tips']),
                    'type'=>intval($dat['type']),
                    'is_select'=>$is_select,
                    'currency'=>intval($dat['currency']),
                    'price'=>intval($dat['price']),
                    'num'=>intval($dat['num']),
                    'interval_price'=>$dat['interval_price'],
                    'diy_select'=>rtrim($dat['diy_select'],'、'),
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $format = Db::connect($this->config)->name('cost_service')->where(['id'=>$id])->find();
            $data = Db::connect($this->config)->name('goods_services')->where(['service_id'=>$id,'company_id'=>0])->find();
            if(empty($data)){
                $data = ['type'=>0,'name'=>'','desc'=>'','tips'=>'','currency'=>5,'price'=>'','num'=>'','interval_price'=>'','diy_select'=>'','is_select'=>0];
                if(!empty($format)){
                    $data['name'] = $format['name'];
                }
            }
            $currency = Db::name('centralize_currency')->select();

            return view('', compact('website','pid','format','data','id','currency'));
        }
    }

    public function del_costs(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;

        $res = Db::connect($this->config)->name('cost_service')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>-1,'msg'=>'删除成功']);
        }
    }
    #费用管理=============================================================================================

    #活动管理
    public function activity_index(Request $request){
        $dat = input();

        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            // 连接数据库
            $conn = Db::connect($this->config);
            $total = $conn->name('ssl_activity')->count();
            $data = $conn->name('ssl_activity')
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {
                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        } else {
            return view('', ['title' => '']);
        }
    }

    public function save_activity(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if ($request->isAjax()) {
            if($id>0){
                $res = Db::connect($this->config)->name('ssl_activity')->where(['id'=>$id])->update(['name'=>trim($dat['name'])]);
            }else{
                $res = Db::connect($this->config)->name('ssl_activity')->insert(['name'=>trim($dat['name']),'createtime'=>time()]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = ['name'=>''];
            if($id>0){
                $data = Db::connect($this->config)->name('ssl_activity')->where(['id'=>$dat['id']])->find();
            }
            return view('',compact('id','data'));
        }
    }

    public function del_activity(Request $request)
    {
        $dat = input();
        // 连接数据库
        $conn = Db::connect($this->config);
        $res = $conn->name('ssl_activity')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    public function reduction_index(Request $request){
        $dat = input();

        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            // 连接数据库
            $conn = Db::connect($this->config);
            $total = $conn->name('ssl_reduction_rule')->count();
            $data = $conn->name('ssl_reduction_rule')
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {
//                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        } else {
            return view('', ['title' => '']);
        }
    }

    public function save_reduction(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if ($request->isAjax()) {
            if($id>0){
                $res = Db::connect($this->config)->name('ssl_reduction_rule')->where(['id'=>$id])->update(['name'=>trim($dat['name']),'content'=>json_encode($dat['content'],true)]);
            }else{
                $res = Db::connect($this->config)->name('ssl_reduction_rule')->insert(['name'=>trim($dat['name']),'content'=>json_encode($dat['content'],true)]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = ['name'=>'','content'=>[0=>'',1=>'',2=>'',3=>'']];
            if($id>0){
                $data = Db::connect($this->config)->name('ssl_reduction_rule')->where(['id'=>$dat['id']])->find();
                $data['content'] = json_decode($data['content'],true);
            }
            return view('',compact('id','data'));
        }
    }

    public function del_reduction(Request $request)
    {
        $dat = input();
        // 连接数据库
        $conn = Db::connect($this->config);
        $res = $conn->name('ssl_reduction_rule')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    public function platform_value(Request $request){
        $dat = input();

        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            // 连接数据库
            $conn = Db::connect($this->config);
            $total = $conn->name('ssl_platform_value')->count();
            $data = $conn->name('ssl_platform_value')
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {
                if(!empty($item['cat_id1'])){
                    $item['cat_id1'] = Db::connect($this->config)->name('category')->where(['cat_id'=>$item['cat_id1']])->find()['cat_name']; 
                    $item['cat_name'] = $item['cat_id1'];
                }
                if(!empty($item['cat_id2'])){
                    $item['cat_id2'] = Db::connect($this->config)->name('category')->where(['cat_id'=>$item['cat_id2']])->find()['cat_name'];
                    $item['cat_name'] = $item['cat_name'].' / '.$item['cat_id2'];
                }
                if(!empty($item['cat_id3'])){
                    $item['cat_id3'] = Db::connect($this->config)->name('category')->where(['cat_id'=>$item['cat_id3']])->find()['cat_name'];
                    $item['cat_name'] = $item['cat_name'].' / '.$item['cat_id3'];
                }
                
                if(!empty($item['cross_catId1'])){
                    $item['cross_catId1'] = Db::connect($this->config)->name('category')->where(['cat_id'=>$item['cross_catId1']])->find()['cat_name']; 
                    $item['crosscat_name'] = $item['cross_catId1'];
                }
                if(!empty($item['cross_catId2'])){
                    $item['cross_catId2'] = Db::connect($this->config)->name('category')->where(['cat_id'=>$item['cross_catId2']])->find()['cat_name'];
                    $item['crosscat_name'] = $item['crosscat_name'].' / '.$item['cross_catId2'];
                }
                if(!empty($item['cross_catId3'])){
                    $item['cross_catId3'] = Db::connect($this->config)->name('category')->where(['cat_id'=>$item['cross_catId3']])->find()['cat_name'];
                    $item['crosscat_name'] = $item['crosscat_name'].' / '.$item['cross_catId3'];
                }
                
               
//                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        } else {
            return view('', ['title' => '']);
        }
    }

    #商家商品列表
    public function goods_check(Request $request){
        $dat = input();

        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            // 连接数据库
            $conn = Db::connect($this->config);
            $total = $conn->name('goods')->whereRaw('shop_id>0 and buyer_id=0')->count();
            $data = $conn->name('goods')
                ->whereRaw('shop_id>0 and buyer_id=0')
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {
                $item['shop_name'] = Db::name('website_user_company')->where(['id'=>$item['shop_id']])->find()['company'];
                if($item['goods_status']==0){
                    $item['status_name'] = '待审核';
                }
                elseif($item['goods_status']==1){
                    $item['status_name'] = '已上架';
                }
                elseif($item['goods_status']==-1){
                    $item['status_name'] = '已下架';
                }
                elseif($item['goods_status']==-2){
                    $item['status_name'] = '已删除（回收站）';
                }
//                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        } else {
            return view('', ['title' => '']);
        }
    }

    #商家商品上下架
    public function goods_check2(Request $request){
        $dat = input();
        $type = intval($dat['type']);
        $id = intval($dat['id']);

        $goods = Db::connect($this->config)->name('goods')->where(['goods_id'=>$id])->find();
        $company = Db::name('website_user_company')->where(['id'=>$goods['shop_id']])->find();
        $merchant = Db::name('website_user')->where(['id'=>$company['user_id']])->find();

        if($type==1){
            #同意上架
            $res = Db::connect($this->config)->name('goods')->where(['goods_id'=>$id])->update([
               'goods_status'=>1
            ]);

            if($res){
                Db::connect($this->config)->name('goods_merchant')->where(['shelf_id'=>$id])->update([
                    'goods_status'=>1
                ]);

                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'您的商品['.$goods['goods_name'].']经审核已上架！',
                    'keyword1' => '您的商品['.$goods['goods_name'].']经审核已上架！',
                    'keyword2' => '已上架',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '查看详情',
                    'url' => 'https://rte.gogo198.cn',
                    'openid' => $merchant['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
                $rr = wechat_httpRequest($post);

                return json(['code'=>0,'msg'=>'上架成功']);
            }
        }
        elseif($type==2){
            #拒绝上架
            $res = Db::connect($this->config)->name('goods')->where(['goods_id'=>$id])->update([
                'goods_status'=>-1,
                'goods_reasons'=>trim($dat['goods_reasons']),
            ]);

            if($res){
                Db::connect($this->config)->name('goods_merchant')->where(['shelf_id'=>$id])->update([
                    'goods_status'=>-1,
                    'goods_reasons'=>trim($dat['goods_reasons']),
                ]);

                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'您的商品['.$goods['goods_name'].']经审核已被拒绝上架！',
                    'keyword1' => '您的商品['.$goods['goods_name'].']经审核已被拒绝上架！',
                    'keyword2' => '拒绝上架',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '查看详情',
                    'url' => 'https://rte.gogo198.cn',
                    'openid' => $merchant['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
                wechat_httpRequest($post);

                return json(['code'=>0,'msg'=>'拒绝上架成功']);
            }
        }
        elseif($type==3){
            #立即下架

            $res = Db::connect($this->config)->name('goods')->where(['goods_id'=>$id])->update([
                'goods_status'=>-1,
            ]);

            if($res){
                Db::connect($this->config)->name('goods_merchant')->where(['shelf_id'=>$id])->update([
                    'goods_status'=>-1,
                ]);

                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'您的商品['.$goods['goods_name'].']经审核已被下架！',
                    'keyword1' => '您的商品['.$goods['goods_name'].']经审核已被下架！',
                    'keyword2' => '已下架',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '查看详情',
                    'url' => 'https://rte.gogo198.cn',
                    'openid' => $merchant['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
                wechat_httpRequest($post);

                return json(['code'=>0,'msg'=>'下架成功']);
            }
        }
    }

    #买手商品列表
    public function buyer_goods_check(Request $request){
        $dat = input();

        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $total = Db::name('website_user_goods_list')->whereRaw('buyer_id>0 and status!=0 and status!=-1')->count();
            $data = Db::name('website_user_goods_list')
                ->whereRaw('buyer_id>0 and status!=0 and status!=-1')
                ->order($order)
                ->limit($limit)
                ->select();

            $status_name = ['-2'=>'拒绝通过','-1'=>'已删除','0'=>'已添加待入库','1'=>'已入库待审批','2'=>'已审批上架'];
            foreach ($data as &$item) {
                $item['buyer_name'] = Db::name('website_buyer')->where(['id'=>$item['buyer_id']])->value('name');

                $item['status_name'] = $status_name[$item['status']];
                if($item['status']==-2){
                    $item['status_name'] .= '（拒绝原因：'.$item['remark'].'）';
                }

                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        } else {
            return view('', ['title' => '']);
        }
    }

    private function cartesianProduct($options) {
        // 提取所有 values 数组
        $valuesArrays = array_column($options, 'values');

        $result = [[]];
        foreach ($valuesArrays as $values) {
            $append = [];
            foreach ($result as $product) {
                foreach ($values as $item) {
                    $append[] = array_merge($product, [$item]);
                }
            }
            $result = $append;
        }

        // 用 '@@' 拼接每个组合
        return array_map(function($combo) {
            return implode('@@', $combo);
        }, $result);
    }


    #买手商品上下架
    public function buyer_goods_check2(Request $request){
        $dat = input();
        $type = intval($dat['type']);
        $id = intval($dat['id']);

        $goods = Db::name('website_user_goods_list')->where(['id'=>$id])->find();
        $buyer = Db::name('website_buyer')->where(['id'=>$goods['buyer_id']])->find();
        $merchant = Db::name('website_user')->where(['id'=>$buyer['uid']])->find();
        $time = time();


        if($type==1){
            #同意上架

            Db::startTrans();
            try{
                $res = Db::name('website_user_goods_list')->where(['id'=>$id])->update([
                    'status'=>2,
                    'remark'=>''
                ]);

                if($res){
                    if(empty($goods['shelf_id'])){
                        #==仓库默认id
                        $wid = 32;
                        #==代发库存数量
                        $goods_number = 99999;
                        #==商品分类
                        $cate = explode(',',$goods['cate_ids']);
                        #==有无规格
                        $have_specs = 2;
                        $nospecs = '';
                        $goods['option_list'] = json_decode($goods['option_list'],true);
                        if(!empty($goods['option_list'])){
                            foreach($goods['option_list'] as $k=>$v){
                                if(!empty($v['option_name'])){
                                    $have_specs = 1;
                                }else{
                                    $nospecs = '{"goods_number":0,"start_num":["1"],"unit":["'.$goods['unit'].'"],"select_end":["2"],"end_num":[""],"currency":["'.$goods['currency'].'"],"price":["'.$goods['price'].'"]}';
                                }
                            }
                        }
                        else{
                            $nospecs = '{"goods_number":0,"start_num":["1"],"unit":["'.$goods['unit'].'"],"select_end":["2"],"end_num":[""],"currency":["'.$goods['currency'].'"],"price":["'.$goods['price'].'"]}';
                        }
                        #==商品参数
                        $spec_info = [];
                        $goods['spec_list'] = json_decode($goods['spec_list'],true);
                        if(!empty($goods['spec_list'])){
                            foreach($goods['spec_list'] as $k=>$v){
                                if(!empty($v['spec_name'])){
                                    array_push($spec_info,['spec_name'=>$v['spec_name'],'spec_desc'=>$v['spec_desc']]);
                                }
                            }
                        }
                        #==商品描述
                        $pc_desc = '';
                        $goods['desc_list'] = json_decode($goods['desc_list'],true);
                        if(!empty($goods['desc_list'])){
                            foreach($goods['desc_list'] as $k=>$v){
                                $pc_desc .= '<p><img src="'.$v.'"></p>';
                            }
                        }
                        #==商品主图
                        $goods_image = '';
                        $goods['pic_list'] = json_decode($goods['pic_list'],true);
                        if(!empty($goods['pic_list'])){
                            $goods_image = $goods['pic_list'][0];
                        }

                        #==把此商品插入商品表
                        $goods_id = Db::connect($this->config)->name('goods')->insertGetId([
                            'goods_name'=>$goods['name'],
                            'type'=>0,#0商品，1赠品
                            'shop_id'=>0,#默认钜铭公司的商品（废弃），$buyer['company_id']
                            'wid'=>$wid,#默认直邮易为代发仓库
                            'buyer_id'=>$buyer['id'],#买手id
                            'goods_type'=>1,#0单品实时打包，1套餐预先打包
                            'is_baoyou'=>3,#1寄方月结，2寄方付款，3收方到付（不包邮）
                            'express_info'=>'{"printer_id":0,"express_info":[{"express_productid":"21","express_remark":"","express_id":21}]}',#发货配置
                            'cat_id'=>$cate[2],
                            'cat_id1'=>$cate[0],
                            'cat_id2'=>$cate[1],
                            'cat_id3'=>$cate[2],
                            'service_type'=>1,#1境内配送，2跨境集运
                            'domestic_logistics'=>'{"name":["\u5168\u56fd\u914d\u9001"],"area1":["all"]}',
                            'gather_method'=>'1,2',
                            'support_export'=>'',
                            'gather_countrys'=>'',
                            'gather_lines'=>'',#线路id，这里在商城显示所有线路
                            'rule_id'=>'',
                            'brand_type'=>0,
                            'brand_type2'=>'',
                            'brand_id'=>'',
                            'brand_name'=>'',
                            'have_specs'=>$have_specs,#1有规格型号，2无规格型号
                            'nospecs'=>$nospecs,
                            'otherfees_content'=>'',
                            'reduction_content'=>'',
                            'gift_content'=>'',
                            'noinclude_content'=>'',
                            'potential_content'=>'',
                            'activity_info'=>'',
                            'other_keywords'=>'',
                            'manufacture'=>'',#默认项目=start
                            'sales'=>'',
                            'foreign'=>'',
                            'effective'=>'{"type":"1","type2":"1","fixed_day":"","interval_day":""}',
                            'store'=>'',
                            'packing'=>'',#默认项目=end
                            'spec_info'=>json_encode($spec_info,true),
                            'pc_desc'=>$pc_desc,
                            'created_at'=>date('Y-m-d H:i:s',$time),
                            'sku_open'=>0,
                            'sku_id'=>0,//已做
                            'goods_status'=>1,#平台审核上架
                            'shipping_country'=>$goods['country_id'],
                            'goods_currency'=>$goods['currency'],
                            'areas'=>'["2398","2448","2450"]',#发货地区
                            'goods_price'=>$goods['price'],
                            'market_price'=>$goods['price'],
                            'cost_price'=>$goods['price'],
                            'goods_number'=>$goods_number,
                            'goods_image'=>$goods_image,
                            'goods_video'=>'',
                        ]);
                        $sku_id = 0;
                        if($have_specs==1){
                            #有规格

                            $date = date('Y-m-d H:i:s',$time);

                            foreach($goods['option_list'] as $k=>$v) {
                                if (!empty($v['option_name'])) {
                                    #[{"option_name":"颜色","option_desc":"白色、黑色"},{"option_name":"内存","option_desc":"128G、256G"}]
                                    $option_list = explode('、',$v['option_desc']);
                                    $spec_ids = Db::connect($this->config)->name('attribute')->where(['attr_name'=>trim($v['option_name'])])->value('attr_id');
                                    if(empty($spec_ids)){
                                        $spec_ids = Db::connect($this->config)->name('attribute')->insertGetId([
                                            'attr_name'=>trim($v['option_name']),
                                            'is_spec'=>1,
                                            'created_at'=>$date,
                                            'updated_at'=>$date,
                                        ]);
                                    }

                                    foreach($option_list as $k2=>$v2){
                                        $spec_vids = Db::connect($this->config)->name('attr_value')->where(['attr_vname'=>trim($v2)])->value('attr_vid');
                                        if(empty($spec_vids)){
                                            $spec_vids = Db::connect($this->config)->name('attr_value')->insertGetId([
                                                'attr_id'=>$spec_ids,
                                                'attr_vname'=>trim($v2),
                                                'attr_vsort'=>$k2,
                                                'created_at'=>$date,
                                                'updated_at'=>$date,
                                            ]);
                                        }

                                        Db::connect($this->config)->name('goods_spec')->insert([
                                            'goods_id' => $goods_id,
                                            'attr_id' => $spec_ids,
                                            'attr_vid' => $spec_vids,
                                            'attr_value' => trim($v2),
                                            'created_at' => $date,
                                            'updated_at' => $date,
                                        ]);
                                    }
                                }
                            }

                            #1. 提取所有属性及其可选项
                            $options = [];
                            foreach ($goods['option_list'] as $item) {
                                // 将 option_desc 按“、”分割成数组（注意：中文顿号）
                                $values = explode('、', $item['option_desc']);
                                $options[] = [
                                    'spec_id' => Db::connect($this->config)->name('attribute')->where(['attr_name'=>trim($item['option_name'])])->value('attr_id'),
                                    'name' => $item['option_name'],
                                    'values' => $values
                                ];
                            }

                            #2. 计算笛卡尔积
                            $combinations = ['']; // 初始包含一个空字符串，便于后续拼接

                            #商品规格表主键
                            $spec_ids_str = '';
                            foreach($options as $k=>$v){
                                $spec_ids_str .= $v['spec_id'].'|';
                            }
                            $spec_ids_str = rtrim($spec_ids_str,'|');
                            $spec_vids_arr = $this->cartesianProduct($options);
                            #整理商品规格键值
                            $spec_vids_arr2 = [];
                            foreach($spec_vids_arr as $k=>$v){
                                $real_sku = explode('@@',$v);
                                $spec_vids = '';
                                foreach($real_sku as $k2=>$v2){
                                    $vid = Db::connect($this->config)->name('attr_value')->where(['attr_vname'=>$v2])->value('attr_vid');
                                    $spec_vids .= $vid.'|';
                                }
                                array_push($spec_vids_arr2,rtrim($spec_vids,'|'));
                            }
                            #整理商品规格名称
                            foreach ($options as $opt) {
                                $newCombos = [];
                                foreach ($combinations as $combo) {
                                    foreach ($opt['values'] as $val) {
                                        // 拼接当前组合：已有部分 + 空格(除非是第一个) + "属性名:值"
                                        $separator = $combo === '' ? '' : ' ';
                                        $newCombos[] = $combo . $separator . $opt['name'] . ':' . $val;

                                        #[{"option_name":"颜色","option_desc":"白色、黑色"},{"option_name":"内存","option_desc":"128G、256G"}]
                                    }
                                }
                                $combinations = $newCombos;
                            }

                            //Array
                            //(
                            //    [0] => 颜色:白色 内存:128G
                            //    [1] => 颜色:白色 内存:256G
                            //    [2] => 颜色:黑色 内存:128G
                            //    [3] => 颜色:黑色 内存:256G
                            //)

                            #3、
                            foreach($combinations as $k=>$v) {
                                #=存入商品规格表
                                $skuid = Db::connect($this->config)->name('goods_sku')->insertGetId([
                                    'goods_id' => $goods_id,
                                    'spec_ids' => $spec_ids_str,
                                    'spec_vids' => $spec_vids_arr2[$k],
                                    'spec_names' => $v,
                                    'sku_specs' => str_replace('|','*',$spec_vids_arr2[$k]),
                                    'sku_prices' => '{"goods_number":' . $goods_number . ',"start_num":["1"],"unit":["' . $goods['unit'] . '"],"select_end":["2"],"end_num":[""],"currency":["' . $goods['currency'] . '"],"price":["' . $goods['price'] . '"]}',
                                    'goods_price' => $goods['price'],
                                    'shelf_number' => $goods_number,
                                    'market_price' => $goods['price'],
                                    'cost_price' => $goods['price'],
                                    'goods_number' => $goods_number,
                                    'goods_sn' => '',
                                    'goods_barcode' => '',
                                    'warn_type' => 1,
                                    'warn_number' => '',
                                    'goods_stockcode' => '',
                                ]);
                                if ($sku_id == 0) {
                                    $sku_id = $skuid;
                                    Db::connect($this->config)->name('goods')->where(['goods_id' => $goods_id])->update([
                                        'sku_id' => $sku_id
                                    ]);
                                }

                                #==仓库库存表（代发商品不需要记录库存）
//                                Db::name('website_warehouse_goodsnum')->insert([
//                                    'company_id' => $buyer['company_id'],
//                                    'warehouse_id' => $wid,
//                                    'goods_id' => $goods_id,
//                                    'sku_id' => $skuid,
//                                    'num' => $goods_number,
//                                    'createtime' => $time,
//                                    'updatetime' => $time
//                                ]);
                            }
                        }
                        elseif($have_specs==2){
                            #无规格
                            $skuid = Db::connect($this->config)->name('goods_sku')->insertGetId([
                                'goods_id'=>$goods_id,
                                'spec_ids'=>'',
                                'spec_vids'=>'',
                                'spec_names'=>'',
                                'sku_specs'=>'',
                                'sku_prices'=>$nospecs,
                                'goods_price'=>$goods['price'],
                                'shelf_number'=>'',
                                'market_price'=>0,
                                'cost_price'=>0,
                                'goods_number'=>0,
                                'goods_sn'=>'',
                                'goods_barcode'=>'',
                                'warn_type'=>1,
                                'warn_number'=>0,
                                'goods_stockcode'=>'',
                            ]);
                            if($sku_id==0){
                                $sku_id = $skuid;
                                Db::connect($this->config)->name('goods')->where(['goods_id'=>$goods_id])->update([
                                    'sku_id'=>$sku_id
                                ]);
                            }

                            #==仓库库存表（代发商品不需要记录库存）
//                            Db::name('website_warehouse_goodsnum')->insert([
//                                'company_id'=>$buyer['company_id'],
//                                'warehouse_id'=>$wid,
//                                'goods_id'=>$goods_id,
//                                'sku_id'=>$sku_id,
//                                'num'=>$goods_number,
//                                'createtime'=>$time,
//                                'updatetime'=>$time
//                            ]);
                        }

                        #==商品图片表
                        foreach($goods['pic_list'] as $k=>$v){
                            Db::connect($this->config)->name('goods_image')->insert([
                                'goods_id'=>$goods_id,
                                'path'=>$v,
                                'is_default'=>$k==0?1:0,
                                'sort'=>$k+1,
                                'created_at'=>date('Y-m-d H:i:s',$time),
                                'updated_at'=>date('Y-m-d H:i:s',$time),
                            ]);
                        }

                        #==修改买手商品表的线上商品ID
                        Db::name('website_user_goods_list')->where(['id'=>$id])->update([
                            'shelf_id'=>$goods_id
                        ]);
                    }

                    $post = json_encode([
                        'call'=>'confirmCollectionNotice',
                        'first' =>'您的商品['.$goods['name'].']经审核已上架！',
                        'keyword1' => '您的商品['.$goods['name'].']经审核已上架！',
                        'keyword2' => '已上架',
                        'keyword3' => date('Y-m-d H:i:s',$time),
                        'remark' => '查看详情',
                        'url' => 'https://dtc.gogo198.net/?s=index/buyer_manage&company_id='.$buyer['company_id'].'&company_type=0&buyer_id='.$buyer['id'],
                        'openid' => $merchant['openid'],
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]);
                    $rr = wechat_httpRequest($post);

                    Db::commit();

                    return json(['code'=>0,'msg'=>'上架成功','dd'=>$rr]);
                }
            }catch (\Exception $e){
                Db::rollback();
                return json(['code'=>-1,'msg'=>$e->getMessage()]);
            }
        }
        elseif($type==2){
            #拒绝上架

            Db::startTrans();
            try{
                $res = Db::name('website_user_goods_list')->where(['id'=>$id])->update([
                    'status'=>-2,
                    'remark'=>trim($dat['goods_reasons']),
                ]);

                if($res){
                    Db::connect($this->config)->name('goods')->where(['goods_id'=>$goods['shelf_id']])->update([
                        'goods_status'=>-1,
                        'goods_reasons'=>trim($dat['goods_reasons']),
                    ]);

                    $post = json_encode([
                        'call'=>'confirmCollectionNotice',
                        'first' =>'您的商品['.$goods['name'].']经审核已被拒绝上架！',
                        'keyword1' => '您的商品['.$goods['name'].']经审核已被拒绝上架！',
                        'keyword2' => '拒绝上架',
                        'keyword3' => date('Y-m-d H:i:s',time()),
                        'remark' => '查看详情',
                        'url' => 'https://dtc.gogo198.net/?s=index/buyer_manage&company_id='.$buyer['company_id'].'&company_type=0&buyer_id='.$buyer['id'],
                        'openid' => $merchant['openid'],
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]);
                    wechat_httpRequest($post);

                    Db::commit();

                    return json(['code'=>0,'msg'=>'拒绝上架成功']);
                }
            }catch (\Exception $e){
                Db::rollback();
                return json(['code'=>-1,'msg'=>$e->getMessage()]);
            }
        }
        elseif($type==3){
            #立即下架
            Db::startTrans();
            try{
                $res = Db::name('website_user_goods_list')->where(['id'=>$id])->update([
                    'status'=>-2,
                ]);

                if($res){
                    Db::connect($this->config)->name('goods')->where(['goods_id'=>$goods['shelf_id']])->update([
                        'goods_status'=>-1,
                    ]);

                    $post = json_encode([
                        'call'=>'confirmCollectionNotice',
                        'first' =>'您的商品['.$goods['name'].']经审核已被下架！',
                        'keyword1' => '您的商品['.$goods['name'].']经审核已被下架！',
                        'keyword2' => '已下架',
                        'keyword3' => date('Y-m-d H:i:s',time()),
                        'remark' => '查看详情',
                        'url' => 'https://dtc.gogo198.net/?s=index/buyer_manage&company_id='.$buyer['company_id'].'&company_type=0&buyer_id='.$buyer['id'],
                        'openid' => $merchant['openid'],
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]);
                    wechat_httpRequest($post);

                    Db::commit();

                    return json(['code'=>0,'msg'=>'下架成功']);
                }
            }catch (\Exception $e){
                Db::rollback();
                return json(['code'=>-1,'msg'=>$e->getMessage()]);
            }
        }
    }

    #买手商品详情
    public function buyer_goods_detail(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;

        $goods = Db::name('website_user_goods_list')->where(['id'=>$id])->find();
        #选品国家
        $goods['country_name'] = Db::name('centralize_diycountry_content')->where(['id'=>$goods['country_id']])->field('param2')->find()['param2'];
        #商品单位
        $goods['unit_name'] = Db::name('unit')->where(['code_value'=>$goods['unit']])->value('code_name');
        #商品币种
        $goods['currency_name'] = Db::name('centralize_currency')->where(['id'=>$goods['currency']])->value('currency_symbol_standard');
        #商品销售渠道
        $goods['sale_unit_name'] = Db::name('sale_unit')->where(['id'=>$goods['sale_unit']])->value('name');
        #商品分类
        $goods['cate_ids'] = explode(',',$goods['cate_ids']);
        $goods['cate_name'] = '';
        foreach($goods['cate_ids'] as $k=>$v){
            $category = Db::connect($this->config)->name('category')->where(['cat_id'=>$v])->field('cat_name')->find();
            $goods['cate_name'] .= $category['cat_name'].',';
        }
        $goods['cate_name'] = trim($goods['cate_name'],',');

        #商品图片
        $goods['pic_list'] = json_decode($goods['pic_list'],true);
        #商品详情图
        $goods['desc_list'] = json_decode($goods['desc_list'],true);
        #商品规格
        $goods['option_list'] = json_decode($goods['option_list'],true);
        #商品参数
        $goods['spec_list'] = json_decode($goods['spec_list'],true);

        #商品状态
        $status_name = ['-2'=>'拒绝通过','-1'=>'已删除','0'=>'已添加待入库','1'=>'已入库待审批','2'=>'已审批上架'];
        $goods['status_name'] = $status_name[$goods['status']];
        if($goods['status']==-2){
            $goods['status_name'] .= '（拒绝原因：'.$goods['remark'].'）';
        }
        return view('',compact('goods','id'));
    }

    public function save_platform_value(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if ($request->isAjax()) {
//            dd($dat);
            $content = [];
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

            #药物属性===START
            $value = ['value'=>isset($dat['value'])?$dat['value']:'','value2'=>isset($dat['value2'])?$dat['value2']:''];
            $opt['opt'] = $dat['opt'];
            $opt['opt_unit'] = $dat['opt_unit'];

            $used['used_unit'] = $dat['used_unit'];
            $used['used'] = $dat['used'];
            #药物属性===END

            if($id>0){
                $res = Db::connect($this->config)->name('ssl_platform_value')->where(['id'=>$id])->update([
                    'cat_id'=>trim($dat['cat_id']),
                    'cat_id1'=>isset($dat['cat_id1'])?trim($dat['cat_id1']):trim($dat['cat_id']),
                    // 'cat_id2'=>isset($dat['cat_id2'])?trim($dat['cat_id2']):'',
                    // 'cat_id3'=>isset($dat['cat_id3'])?trim($dat['cat_id3']):'',
                    'cross_catId'=>trim($dat['cross_catId']),
                    'cross_catId1'=>isset($dat['cross_catId1'])?trim($dat['cross_catId1']):trim($dat['cross_catId']),
                    // 'cross_catId2'=>isset($dat['cross_catId2'])?trim($dat['cross_catId2']):'',
                    // 'cross_catId3'=>isset($dat['cross_catId3'])?trim($dat['cross_catId3']):'',
                    'supervision_object'=>trim($dat['supervision_object']),
                    'perform_type'=>trim($dat['perform_type']),
                    'apply_link'=>$dat['perform_type']==2?trim($dat['apply_link']):'',
                    'platform_supervision'=>trim($dat['platform_supervision']),
                    'content'=>$content,
                    'msg'=>trim($dat['msg']),
                    'drug'=>json_encode(['value'=>$value,'option'=>$opt,'used'=>$used,'taboo'=>trim($dat['drug_taboo'])],true)
                ]);
            }else{
                $res = Db::connect($this->config)->name('ssl_platform_value')->insert([
                    'cat_id'=>trim($dat['cat_id']),
                    'cat_id1'=>isset($dat['cat_id1'])?trim($dat['cat_id1']):trim($dat['cat_id']),
                    // 'cat_id2'=>isset($dat['cat_id2'])?trim($dat['cat_id2']):'',
                    // 'cat_id3'=>isset($dat['cat_id3'])?trim($dat['cat_id3']):'',
                    'cross_catId'=>trim($dat['cross_catId']),
                    'cross_catId1'=>isset($dat['cross_catId1'])?trim($dat['cross_catId1']):trim($dat['cross_catId']),
                    // 'cross_catId2'=>isset($dat['cross_catId2'])?trim($dat['cross_catId2']):'',
                    // 'cross_catId3'=>isset($dat['cross_catId3'])?trim($dat['cross_catId3']):'',
                    'supervision_object'=>trim($dat['supervision_object']),
                    'perform_type'=>trim($dat['perform_type']),
                    'apply_link'=>$dat['perform_type']==2?trim($dat['apply_link']):'',
                    'platform_supervision'=>trim($dat['platform_supervision']),
                    'content'=>$content,
                    'msg'=>trim($dat['msg']),
                    'drug'=>json_encode(['value'=>$value,'option'=>$opt,'used'=>$used,'taboo'=>trim($dat['drug_taboo'])],true)
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = ['id'=>0,'cat_id'=>'','cat_id1'=>'','cat_id2'=>'','cat_id3'=>'','cross_catId'=>'','cross_catId1'=>'','cross_catId2'=>'','cross_catId3'=>'','content'=>'','supervision_object'=>0,'perform_type'=>0,'apply_link'=>'https://www.gogo198.cn/apply','platform_supervision'=>'','msg'=>'','drug'=>['value'=>['value'=>'','value2'=>''],'option'=>['opt'=>['',''],'opt_unit'=>['','']],'used'=>['used_unit'=>['','','',''],'used'=>['']],'taboo'=>'']];
            $sale_cates = [];
            $logi_cates = [];
            if($id>0){
                $data = Db::connect($this->config)->name('ssl_platform_value')->where(['id'=>$dat['id']])->find();
                $data['content'] = json_decode($data['content'],true);
                $data['drug'] = json_decode($data['drug'],true);
                $data['drug']['value']['value_name'] = Db::name('prescription_value')->where(['id'=>$data['drug']['value']['value']])->find()['name'];
                $data['drug']['value']['value2_name'] = Db::name('prescription_value')->where(['id'=>$data['drug']['value']['value2']])->find()['name'];
                $num = 0;
                foreach($data['content'] as $k=>$v){
                    $big_parag_num2 = explode('.',$v['parag_num']);
                    if(count($big_parag_num2)==2){
                        $num+=1;
                    }
                }
                $data['big_parag_num'] = $num;
                
                $sale_cates[0] = Db::connect($this->config)->name('category')->where(['cat_id'=>$data['cat_id1']])->select();
            
                $logi_cates[0] = Db::connect($this->config)->name('category')->where(['cat_id'=>$data['cross_catId1']])->select();
            }
            $sale_cate = Db::connect($this->config)->name('category')->where(['parent_id'=>0,'is_show'=>1,'type_id'=>1])->select();
            $logi_cate = Db::connect($this->config)->name('category')->where(['parent_id'=>0,'is_show'=>1,'type_id'=>2])->select();


            #数量
            $unit['num_unit'] = Db::name('prescription_language')->where(['pid'=>16])->select();
            $unit['unit'] = Db::name('unit')->select();
            #间隔
            $unit['interval_unit'] = Db::name('prescription_language')->where(['pid'=>30])->select();
            #途径
            $unit['road_unit'] = Db::name('prescription_language')->where(['pid'=>49])->select();
            #服用时间
            $unit['eat_unit'] = Db::name('prescription_language')->where(['pid'=>44])->select();

            
            return view('',compact('id','data','sale_cate','logi_cate','sale_cates','logi_cates','unit'));
        }
    }

    public function get_medicine(Request $request){
        $dat = input();
        if($dat['id']==4602 || $dat['id']==5532){
            $list = Db::name('prescription_value')->where(['typeid'=>$dat['id'],'pid'=>0])->select();
        }else{
            $list = Db::name('prescription_value')->where(['pid'=>$dat['id']])->select();
        }

        return json(['code'=>0,'data'=>$list]);
    }

    public function del_platform_value(Request $request)
    {
        $dat = input();
        // 连接数据库
        $conn = Db::connect($this->config);
        $res = $conn->name('ssl_platform_value')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    public function get_nextcate(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $list = Db::connect($this->config)->name('category')->where(['parent_id'=>$id])->select();
        return json(['code'=>0,'data'=>$list]);
    }

    public function statement_manage(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if ($request->isAjax()) {
            Db::connect($this->config)->name('ssl_statement')->where(['id'=>1])->update([
                'baozhang_link'=>trim($dat['baozhang_link']),'neirong_link'=>trim($dat['neirong_link'])
            ]);
            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['baozhang_link'=>'','neirong_link'=>''];
            $data = Db::connect($this->config)->name('ssl_statement')->where(['id'=>1])->find();
            return view('',compact('id','data'));
        }
    }

    //选购单--start
    public function cart_manage(Request $request){
        $dat = input();
        $pa = intval($dat['pa']);

        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            // 连接数据库
            $conn = Db::connect($this->config);
            //显示未购买的选购单
            $total = $conn->name('cart')->where(['is_buy'=>0])->count();
            $data = $conn->name('cart')
                ->where(['is_buy'=>0])
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {
                $user = Db::name('website_user')->where(['id'=>$item['user_id']])->find();
                if(!empty($user['realname'])){
                    $item['buyer_name'] = $user['realname'].'（'.$user['custom_id'].'）';
                }
                elseif(!empty($user['nickname'])){
                    $item['buyer_name'] = $user['nickname'].'（'.$user['custom_id'].'）';
                }else{
                    $item['buyer_name'] = $user['custom_id'];
                }
                $item['created_at'] = date('Y-m-d H:i',$item['created_at']);

                $item['goods_name'] = $conn->name('goods')->where(['goods_id'=>$item['goods_id']])->field('goods_name')->find()['goods_name'];
            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        } else {
            return view('', ['pa'=>$pa]);
        }
    }

    public function cart_detail(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isAjax()){

        }else{
            $conn = Db::connect($this->config);
            $cartDetail = $conn->name('cart')->where(['is_buy'=>0,'cart_id'=>$id])->find();

            $user = Db::name('website_user')->where(['id'=>$cartDetail['user_id']])->find();
            if(!empty($user['realname'])){
                $cartDetail['buyer_name'] = $user['realname'].'（'.$user['custom_id'].'）';
            }
            elseif(!empty($user['nickname'])){
                $cartDetail['buyer_name'] = $user['nickname'].'（'.$user['custom_id'].'）';
            }else{
                $cartDetail['buyer_name'] = $user['custom_id'];
            }
            $cartDetail['created_at'] = date('Y-m-d H:i',$cartDetail['created_at']);

            $goods = $conn->name('goods')->where(['goods_id'=>$cartDetail['goods_id']])->field('goods_name,buyer_id,api_id,shop_id')->find();
            $cartDetail['shop_name'] = '';
            if($goods['shop_id']>0){
                #自营商家
                $cartDetail['shop_name'] = Db::name('website_user_company')->where(['id'=>$goods['shop_id']])->field('company')->find()['company'];
                $cartDetail['shop_name'] .= '（自营）';
            }
            elseif($goods['api_id']>0){
                #接口商家
                $cartDetail['shop_name'] = Db::name('website_user_company')->where(['id'=>$goods['api_id']])->field('company')->find()['company'];
                $cartDetail['shop_name'] .= '（接口）';
            }
            elseif($goods['buyer_id']>0){
                #买手
                $cartDetail['shop_name'] = Db::name('website_buyer')->where(['id'=>$goods['buyer_id']])->field('name')->find()['name'];
                $cartDetail['shop_name'] .= '（买手）';
            }

            return view('',compact('id','cartDetail','goods'));
        }
    }
    //选购单--end


    //订单处理--start
    public function order_manage(Request $request){
        $dat = input();
        $pa = intval($dat['pa']);

        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            // 连接数据库
//            $conn = Db::connect($this->config);
            $status = -13;#未通知，待付
            if($pa==2){
                $status = -14;#已通知，待付款
            }
            elseif($pa==3){
                $status = 1;#已付
            }
            elseif($pa==4){
                $status = -15;#退款
            }
            $total = Db::name('website_order_list')->where(['status'=>$status])->count();
            $data = Db::name('website_order_list')
                ->where(['status'=>$status])
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {
                $user = Db::name('website_user')->where(['id'=>$item['user_id']])->find();
                if(!empty($user['realname'])){
                    $item['custom_id'] = $user['realname'].'（'.$user['custom_id'].'）';
                }
                elseif(!empty($user['nickname'])){
                    $item['custom_id'] = $user['nickname'].'（'.$user['custom_id'].'）';
                }else{
                    $item['custom_id'] = $user['custom_id'];
                }
                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
                $item['status_name'] = order_status($item['status']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        } else {
            $title = '';
            if($pa==1){
                $title = '待付处理';
            }
            elseif($pa==2){
                $title = '未付处理';
            }
            elseif($pa==3){
                $title = '已付处理';
            }
            elseif($pa==4){
                $title = '退款处理';
            }
            return view('', ['title' => $title,'pa'=>$pa]);
        }
    }

    public function other_order_manage(Request $request){
        $dat = input();

        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            // 连接数据库
//            $conn = Db::connect($this->config);
            $total = Db::name('website_order_list')->where(['origin_type'=>1])->count();
            $data = Db::name('website_order_list')
                ->where(['origin_type'=>1])
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {
                $user = Db::name('website_user')->where(['id'=>$item['user_id']])->find();
                $item['custom_id'] = $user['custom_id'];
                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
                $item['status_name']=order_status($item['status']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        } else {
            return view('', ['title' => '']);
        }
    }

    public function order_detail(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if ($request->isAjax()) {
            $order = Db::name('website_order_list')->where(['id'=>$id])->find();
            if($dat['method']==1){
                #取消订单，目前是支付采购单后才可取消。后续创建订单也可取消订单（待）

                if($order['type']==2){
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://shop.gogo198.cn/collect_website/public/?s=/api/getgoods/cancel_order&orderCode='.$order['other_posn']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $res = curl_exec($ch);
                    curl_close($ch);
                    $res = json_decode($res,true);

                    if($res['data']['success']==1){
                        Db::name('website_order_list')->where(['id'=>$id])->update(['status'=>-4]);

                        notice_user($order['user_id'],[
                            'first'=>'订单['.$order['ordersn'].']状态变更',
                            'keyword1'=>'订单['.$order['ordersn'].']状态变更',
                            'keyword2'=>'已取消',
                            'url'=>'https://www.gogo198.cn/bill_detail?id='.$order['id'],
                            'msg'=>'订单['.$order['ordersn'].']状态变更为[已取消]，点击链接查看：https://www.gogo198.cn/bill_detail?id='.$order['id'],
                        ]);

                        return json(['code'=>0,'msg'=>'操作成功']);
                    }else{
                        Db::name('website_order_list')->where(['id'=>$id])->update(['opera_reson'=>$res['data']['info']]);
                        return json(['code'=>0,'msg'=>'操作失败']);
                    }
                }elseif($order['type']==1 || $order['type']==0){
                    Db::name('website_order_list')->where(['id'=>$id])->update(['status'=>-4]);

                    notice_user($order['user_id'],[
                        'first'=>'订单['.$order['ordersn'].']状态变更',
                        'keyword1'=>'订单['.$order['ordersn'].']状态变更',
                        'keyword2'=>'已取消',
                        'url'=>'https://www.gogo198.cn/bill_detail?id='.$order['id'],
                        'msg'=>'订单['.$order['ordersn'].']状态变更为[已取消]，点击链接查看：https://www.gogo198.cn/bill_detail?id='.$order['id'],
                    ]);

                    return json(['code'=>0,'msg'=>'操作成功']);
                }
            }
            elseif($dat['method']==2){
                #有货，支付通知
                Db::name('website_order_list')->where(['id'=>$id])->update(['status'=>0]);

                notice_user($order['user_id'],[
                    'first'=>'订单['.$order['ordersn'].']状态变更',
                    'keyword1'=>'订单['.$order['ordersn'].']状态变更',
                    'keyword2'=>'订购成功，请支付',
                    'url'=>'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&oid='.$order['pay_id'].'&wxref=mp.weixin.qq.com#wechat_redirect',
                    'msg'=>'订单['.$order['ordersn'].']状态变更为[待付款]，点击链接查看：https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&oid='.$order['pay_id'].'&wxref=mp.weixin.qq.com#wechat_redirect',
                ]);

                return json(['code'=>0,'msg'=>'操作成功']);
            }
            elseif($dat['method']==3){
                #同意退换货(不走API，走自己的物流)
                $order['return_content'] = json_decode($order['return_content'],true);
                $status=0;
                if($order['return_content']['applyType']==1){
                    $status=-6;
                }elseif ($order['return_content']['applyType']==2){
                    $status=-8;
                }
                Db::name('website_order_list')->where(['id' => $id])->update(['status' => $status]);
                notice_user($order['user_id'],[
                    'first'=>'订单['.$order['ordersn'].']状态变更',
                    'keyword1'=>'订单['.$order['ordersn'].']状态变更',
                    'keyword2'=>'已退换',
                    'url'=>'https://www.gogo198.cn/bill_detail?id='.$order['id'],
                    'msg'=>'订单['.$order['ordersn'].']状态变更为[已退换]，点击链接查看：https://www.gogo198.cn/bill_detail?id='.$order['id'],
                ]);
                return json(['code'=>0,'msg'=>'操作成功']);
//                $ch = curl_init();
//                curl_setopt($ch, CURLOPT_URL, 'https://shop.gogo198.cn/collect_website/public/?s=api/getgoods/return_order&applySource='.$order['return_content']['applySource'].'&orderCode='.$order['return_content']['orderCode'].'&applyType='.$order['return_content']['applyType'].'&applyContent='.$order['return_content']['applyContent'].'&skuCode='.$order['return_content']['skuCode'].'&quantity='.$order['return_content']['quantity']);
//                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//                $res = curl_exec($ch);
//                curl_close($ch);
//                $res = json_decode($res,true);
//
//                if($res['data']['success']==1) {
//                    $status=0;
//                    if($order['return_content']['applyType']==1){
//                        $status=-6;
//                    }elseif ($order['return_content']['applyType']==2){
//                        $status=-8;
//                    }
//                    Db::name('website_order_list')->where(['id' => $id])->update(['status' => $status]);
//                    notice_user($order['user_id'],[
//                        'first'=>'订单['.$order['ordersn'].']状态变更',
//                        'keyword1'=>'订单['.$order['ordersn'].']状态变更',
//                        'keyword2'=>'已退换',
//                        'url'=>'https://www.gogo198.cn/bill_detail?id='.$order['id'],
//                        'msg'=>'订单['.$order['ordersn'].']状态变更为[已退换]，点击链接查看：https://www.gogo198.cn/bill_detail?id='.$order['id'],
//                    ]);
//                    return json(['code'=>0,'msg'=>'操作成功']);
//                }else{
//                    Db::name('website_order_list')->where(['id'=>$id])->update(['opera_reson'=>$res['data']['data']['info']]);
//                    return json(['code'=>0,'msg'=>'操作失败']);
//                }
            }
            elseif($dat['method']==4) {
                #拒绝退换货

                $ispay = Db::name('customs_collection')->where(['id'=>$order['pay_id']])->find();

                $status=0;
                if($ispay['status']==1){
                    $status=1;
                }elseif ($ispay['status']==0){
                    $status=0;
                }
                Db::name('website_order_list')->where(['id' => $id])->update(['status' => $status]);
                notice_user($order['user_id'],[
                    'first'=>'订单['.$order['ordersn'].']状态变更',
                    'keyword1'=>'订单['.$order['ordersn'].']状态变更',
                    'keyword2'=>'拒绝退换',
                    'url'=>'https://www.gogo198.cn/bill_detail?id='.$order['id'],
                    'msg'=>'订单['.$order['ordersn'].']经审核拒绝退换，点击链接查看：https://www.gogo198.cn/bill_detail?id='.$order['id'],
                ]);
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }
        else{
            #1、获取账单信息
            $order = Db::name('website_order_list')->where(['id'=>$id])->find();
            $order['content'] = json_decode($order['content'],true);

            if($order['origin_type']==0){
                #本商城订单
                $order['currency'] = '';
                #2、获取商品信息
                $goods = Db::connect($this->config)->name('goods')->where(['goods_id'=>$order['content']['good_id']])->find();
                #3、获取商品规格
                $goods_sku = Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$goods['sku_id']])->find();
                $goods_sku['sku_prices'] = json_decode($goods_sku['sku_prices'],true);

                $order['currency'] = Db::name('centralize_currency')->where(['id'=>$goods_sku['sku_prices']['currency'][0]])->find()['currency_symbol_origin'];
                $order['unit'] = Db::name('unit')->where(['code_value'=>$goods_sku['sku_prices']['unit'][0]])->find()['code_name'];
                $order['total_num']=0;
                $order['total_price']=0;
                foreach($order['content']['buy_attr'] as $k=>$v){
                    $order['total_num']+=$v['buy_num'];
                    $order['total_price']+=$v['now_gprice'];
                }
            }
            elseif($order['origin_type']==1){
                #backydrop订单
                $paylist = Db::name('customs_collection')->where(['id'=>$order['pay_id']])->find();
                $goods = Db::connect($this->config)->name('goods')->where(['goods_id'=>$paylist['good_id']])->find();

                #退换货信息
                $order['return_content'] = json_decode($order['return_content'],true);

                $goods_sku = [];
                if($goods['have_specs']==1){
                    #看看买哪些商品规格
                    $order['content']['good_num'] = explode('@@@',$order['content']['good_num']);
                    $order['content']['good_price'] = explode('@@@',$order['content']['good_price']);
                    $order['content']['value_name'] = explode('@@@',$order['content']['value_name']);
                    $order['content']['skuCode'] = explode('@@@',$order['content']['skuCode']);
                    foreach($order['content']['good_num'] as $k => $v){
                        if(!empty($v)){
                            $goods_sku[$k]['value_name'] = $order['content']['value_name'][$k];
                            $goods_sku[$k]['good_num'] = $order['content']['good_num'][$k];
                            $goods_sku[$k]['good_price'] = $order['content']['good_price'][$k];
                            $goods_sku[$k]['skuCode'] = $order['content']['skuCode'][$k];
                        }
                    }
                }
            }


            return view('',compact('id','goods','order','goods_sku'));
        }
    }

    #订单一切操作等
    public function refuse_order(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        $order = Db::name('website_order_list')->where(['id'=>$id])->find();
        $user = Db::name('website_user')->where(['id'=>$order['user_id']])->find();

        if($dat['type']==1){
            #拒绝支付
            $res = Db::name('website_order_list')->where(['id'=>$id])->update(['status'=>-12]);
            if($res){
                if(!empty($user['openid'])){
                    sendWechatMsg(json_encode([
                        'call'=>'confirmCollectionNotice',
                        'first' =>'订购清单['.$order['ordersn'].']拒绝支付请求',
                        'keyword1' => '拒绝支付请求',
                        'keyword2' => '拒绝支付请求',
                        'keyword3' => date('Y-m-d H:i:s',time()),
                        'remark' => '',
                        'url' => 'https://www.gogo198.cn/cart.html?selected=2',
                        'openid' => $user['openid'],
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]));
                }
                elseif(!empty($user['email'])){
                    httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$user['email'],'title'=>'订购清单['.$order['ordersn'].']拒绝支付请求','content'=>'平台已拒绝您的支付请求，请点击链接进行查看：https://www.gogo198.cn/cart.html?selected=2']);
                }


                return json(['code'=>0,'msg'=>'操作成功，已通知买家']);
            }
        }
        elseif($dat['type']==2){
            $res = Db::name('website_order_list')->where(['id'=>$id])->update(['status'=>-14]);
            if($res){
                if(!empty($user['openid'])) {
                    sendWechatMsg(json_encode([
                        'call' => 'confirmCollectionNotice',
                        'first' => '订购清单[' . $order['ordersn'] . ']通过支付请求',
                        'keyword1' => '订单待付通知',
                        'keyword2' => '订单待付',
                        'keyword3' => date('Y-m-d H:i:s', time()),
                        'remark' => '',
                        'url' => 'https://www.gogo198.cn/cashier?orderid=' . $order['id'],
                        'openid' => $user['openid'],
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]));
                }
                elseif(!empty($user['email'])){
                    httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$user['email'],'title'=>'订购清单['.$order['ordersn'].']通过支付请求','content'=>'平台已通过您的支付请求，请点击链接进行查看：https://www.gogo198.cn/cashier?orderid=' . $order['id']]);
                }

                return json(['code'=>0,'msg'=>'操作成功，已通知买家']);
            }
        }
    }
    //订单管理--end

    //流程管理--start
    public function transaction_manage(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?$dat['pid']:0;
        $typ = isset($dat['typ'])?$dat['typ']:0;
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');
            $total = Db::connect($this->config)->name('ssl_process_list')->where(['typ'=>$typ,'pid'=>$pid,'display'=>0])->count();
            $data = Db::connect($this->config)->name('ssl_process_list')
                ->where(['typ'=>$typ,'pid'=>$pid,'display'=>0])
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            if(!empty($data)){
                foreach($data as $k=>$v){
                    $data[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('',compact('pid','typ'));
        }
    }

    public function save_transaction(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $pid = isset($dat['pid'])?$dat['pid']:0;
        $typ = isset($dat['typ'])?$dat['typ']:0;
        $displayorders = isset($dat['displayorders'])?$dat['displayorders']+1:1;
        if($request->isAjax()){
            $level = intval($dat['level']);
            if(empty($dat['displayorders'])){
                return json(['code'=>-1,'msg'=>'请补充序号']);
            }

            if($level==2){
                if($pid>0){
                    $p_info = Db::connect($this->config)->name('ssl_process_list')->where(['typ'=>$typ,'id'=>$pid,'display'=>0])->find();
                    $pid = $p_info['pid'];
                }
            }

            #1、判断是否有重复序号
            $ishave = Db::connect($this->config)->name('ssl_process_list')->where(['typ'=>$typ,'pid'=>$pid,'displayorders'=>$dat['displayorders'],'step'=>$dat['step'],'display'=>0])->find();

            if($ishave['id']>0 && $id!=$ishave['id']){
                return json(['code'=>-1,'msg'=>'该序号或序号描述已重复']);
            }

            #2、判断该序号前面是否有缺号
            $step = trim($dat['step']);
            $step = explode('Step',$step);
            if(!isset($step[1])){
                return json(['code'=>-1,'msg'=>'请输入正确的序号：Step+序号']);
            }
            for($i=1;$i<$step[1];$i++){
                $ishave = Db::connect($this->config)->name('ssl_process_list')->where(['typ'=>$typ,'pid'=>$pid,'step'=>'Step'.$i,'display'=>0])->find();
                if(empty($ishave['id'])){
                    return json(['code'=>-2,'msg'=>'该序号前缺号（Step'.$i.'），正在为你补缺','step'=>$i]);
                }
            }

            if($id>0){
                $res = Db::connect($this->config)->name('ssl_process_list')->where(['typ'=>$typ,'id'=>$id])->update([
                    'step'=>trim($dat['step']),
//                    'title'=>trim($dat['title']),
                    'displayorders'=>trim($dat['displayorders']),
                    'content'=>trim($dat['content']),
                    'link'=>trim($dat['link']),
                    'icon'=>isset($dat['ico'][0])?$dat['ico'][0]:'',
                ]);
            }else{
                $res = Db::connect($this->config)->name('ssl_process_list')->insert([
                    'pid'=>$pid,
                    'typ'=>$typ,
                    'step'=>trim($dat['step']),
//                    'title'=>trim($dat['title']),
                    'displayorders'=>trim($dat['displayorders']),
                    'content'=>trim($dat['content']),
                    'link'=>trim($dat['link']),
                    'icon'=>isset($dat['ico'][0])?$dat['ico'][0]:'',
                    'createtime'=>time(),
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = ['step'=>'Step'.$displayorders,'title'=>'','icon'=>'','content'=>'','link'=>'','displayorders'=>$displayorders];
            if($pid>0){
                $ishave = Db::connect($this->config)->name('ssl_process_list')->where(['typ'=>$typ,'pid'=>$pid,'display'=>0])->order('displayorders desc')->find();
                if($ishave['id']){
                    $num = intval($ishave['displayorders'])+1;
                    $data['step'] = 'Step'.$num;
                    $data['displayorders'] = $num;
                }
            }else{
                $ishave = Db::connect($this->config)->name('ssl_process_list')->where(['typ'=>$typ,'pid'=>$pid,'display'=>0])->order('displayorders desc')->find();
                if($ishave['id']){
                    $num = intval($ishave['displayorders'])+1;
                    $data['step'] = 'Step'.$num;
                    $data['displayorders'] = $num;
                }
            }
            if($id>0){
                $data = Db::connect($this->config)->name('ssl_process_list')->where(['typ'=>$typ,'id'=>$id,'display'=>0])->find();
                $pid=$data['pid'];
            }
            return view('',compact('id','pid','data','typ'));
        }
    }

    public function del_transaction(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){
            $res = Db::connect($this->config)->name('ssl_process_list')->where(['id'=>$id])->delete();
            if($res){
                return json(['code'=>0,'msg'=>'删除成功']);
            }
        }
    }
    //流程管理--end

    //类别管理--start
    public function gcate_manage(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?$dat['pid']:0;

        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');
            $total = Db::connect($this->config)->name('ssl_good_category')->where(['pid'=>0])->count();
            $data = Db::connect($this->config)->name('ssl_good_category')
                ->where(['pid'=>0])
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            if(!empty($data)){
                foreach($data as $k=>$v){
//                    $data[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('',compact('pid','typ'));
        }
    }

    public function save_gcate(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isAjax()){
            if($id>0){
                $res = Db::connect($this->config)->name('ssl_good_category')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name'])
                ]);
            }else{
                $res = Db::connect($this->config)->name('ssl_good_category')->insert([
                    'name'=>trim($dat['name'])
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = ['name'=>''];
            if($id>0){
                $data = Db::connect($this->config)->name('ssl_good_category')->where(['id'=>$id])->find();
            }
            return view('',compact('id','data'));
        }
    }

    public function del_gcate(Request $request)
    {
        $dat = input();
        // 连接数据库
        $conn = Db::connect($this->config);
        $res = $conn->name('ssl_good_category')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #子类
    public function gcate_child(Request $request){
        $dat = input();
        $type_id = isset($dat['type_id'])?$dat['type_id']:0;
        $cat_level = isset($dat['cat_level'])?$dat['cat_level']:0;
        $parent_id = isset($dat['parent_id'])?$dat['parent_id']:0;

        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $page = $request->get('offset') == 0 ? 0 : $request->get('offset');
            $search = $request->get('search');
//            print_r($page.' '.$limit);die;

            $total = Db::connect($this->config)->name('category')->where(['type_id'=>$type_id,'parent_id'=>$parent_id])->count();
            $data = Db::connect($this->config)->name('category')
                ->where(['type_id'=>$type_id,'parent_id'=>$parent_id])
                ->limit($page,$limit)
                ->order('cat_id', 'desc')
                ->select();

            if(!empty($data)){
                foreach($data as $k=>$v){
//                    $data[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('',compact('type_id','cat_level','parent_id'));
        }
    }

    public function save_gcatechild(Request $request){
        $dat = input();
        $id = isset($dat['cat_id'])?$dat['cat_id']:0;
        $type_id = isset($dat['type_id'])?$dat['type_id']:0;
        $cat_level = isset($dat['cat_level'])?$dat['cat_level']:0;
        $parent_id = isset($dat['parent_id'])?$dat['parent_id']:0;

//        dd($dat);
        if($request->isAjax()){

            if($id>0){
                $res = Db::connect($this->config)->name('category')->where(['cat_id'=>$id])->update([
                    'cat_name'=>trim($dat['cat_name'])
                ]);
            }else{
                $res = Db::connect($this->config)->name('category')->insert([
                    'cat_name'=>trim($dat['cat_name']),
                    'type_id'=>$type_id,
                    'parent_id'=>$parent_id,
                    'cat_level'=>$cat_level,
                    'is_show'=>1,
                    'created_at'=>date('Y-m-d H:i:s',time())
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = ['cat_name'=>''];
            if($id>0){
                $data = Db::connect($this->config)->name('category')->where(['cat_id'=>$id])->find();
            }
            return view('',compact('id','data','type_id','cat_level','parent_id'));
        }
    }

    public function del_gcatechild(Request $request)
    {
        $dat = input();
        // 连接数据库
        $conn = Db::connect($this->config);
        $res = $conn->name('category')->where(['cat_id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
    
    public function upload_code(Request $request){
        $dat = input();
        $type = $dat['type'];

        if($request->isAjax()){
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
//            dd($type);
           

            if($type==1){
                for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                    array_push($data, [
                        'cate1' => trim($PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue()),
                        'cate2' => trim($PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue()),
                        'cate3' => trim($PHPRead->getActiveSheet()->getCell("C".$currentRow)->getValue()),
                        'cate4' => trim($PHPRead->getActiveSheet()->getCell("D".$currentRow)->getValue()),
                        'cate5' => trim($PHPRead->getActiveSheet()->getCell("E".$currentRow)->getValue()),
                        'cate6' => trim($PHPRead->getActiveSheet()->getCell("F".$currentRow)->getValue()),
                    ]);
                }

                foreach($data as $k=>$v){
                    $cat_id = 0;
                    $cat_level = 1;
                    $type_id = $type;
                    
                    $cate1 = Db::connect($this->config)->name('category')->where(['cat_name'=>$v['cate1']])->find();
                    if(empty($cate1)){
                        $cat_id = Db::connect($this->config)->name('category')->insertGetId(['cat_name'=>$v['cate1'],'created_at'=>date('Y-m-d H:i:s'),'type_id'=>$type_id,'cat_level'=>$cat_level]);
                        $cat_level += 1;
                    }else{
                        $cat_id = $cate1['cat_id'];
                        $cat_level += 1;
                    }
                    
                    if(!empty($v['cate2'])){
                        $cate2 = Db::connect($this->config)->name('category')->where(['cat_name'=>$v['cate2'],'parent_id'=>$cat_id])->find();
                        if(empty($cate2)){
                            $cat_id = Db::connect($this->config)->name('category')->insertGetId(['cat_name'=>$v['cate2'],'parent_id'=>$cat_id,'created_at'=>date('Y-m-d H:i:s'),'type_id'=>$type_id,'cat_level'=>$cat_level]);
                            $cat_level += 1;
                        }else{
                            $cat_id = $cate2['cat_id'];
                            $cat_level += 1;
                        }
                    }
                    
                    if(!empty($v['cate3'])){
                        $cate3 = Db::connect($this->config)->name('category')->where(['cat_name'=>$v['cate3'],'parent_id'=>$cat_id])->find();
                        if(empty($cate3)){
                            $cat_id = Db::connect($this->config)->name('category')->insertGetId(['cat_name'=>$v['cate3'],'parent_id'=>$cat_id,'created_at'=>date('Y-m-d H:i:s'),'type_id'=>$type_id,'cat_level'=>$cat_level]);
                            $cat_level += 1;
                        }else{
                            $cat_id = $cate3['cat_id'];
                            $cat_level += 1;
                        }
                    }
                    
                    if(!empty($v['cate4'])){
                        $cate4 = Db::connect($this->config)->name('category')->where(['cat_name'=>$v['cate4'],'parent_id'=>$cat_id])->find();
                        if(empty($cate4)){
                            $cat_id = Db::connect($this->config)->name('category')->insertGetId(['cat_name'=>$v['cate4'],'parent_id'=>$cat_id,'created_at'=>date('Y-m-d H:i:s'),'type_id'=>$type_id,'cat_level'=>$cat_level]);
                            $cat_level += 1;
                        }else{
                            $cat_id = $cate4['cat_id'];
                            $cat_level += 1;
                        }
                    }
                    
                    if(!empty($v['cate5'])){
                        $cate5 = Db::connect($this->config)->name('category')->where(['cat_name'=>$v['cate5'],'parent_id'=>$cat_id])->find();
                        if(empty($cate5)){
                            $cat_id = Db::connect($this->config)->name('category')->insertGetId(['cat_name'=>$v['cate5'],'parent_id'=>$cat_id,'created_at'=>date('Y-m-d H:i:s'),'type_id'=>$type_id,'cat_level'=>$cat_level]);
                            $cat_level += 1;
                        }else{
                            $cat_id = $cate5['cat_id'];
                            $cat_level += 1;
                        }
                    }
                    
                    if(!empty($v['cate6'])){
                        $cat_name = explode('@',$v['cate6']);
                        $cate6 = Db::connect($this->config)->name('category')->where(['cat_name'=>trim($cat_name[0]),'parent_id'=>$cat_id])->find();
                        if(empty($cate6)){
                            $cat_id = Db::connect($this->config)->name('category')->insertGetId(['cat_name'=>trim($cat_name[0]),'cat_enname'=>isset($cat_name[1])?trim($cat_name[1]):'','parent_id'=>$cat_id,'created_at'=>date('Y-m-d H:i:s'),'type_id'=>$type_id,'cat_level'=>$cat_level]);
                        }
                    }
                }
            }

            @unlink($fileName);
            return json(['code'=>0,'msg'=>'已上传']);
        }else{
            return view('',compact('type'));
        }
    }
    //类别管理--end

    //反馈管理
    public function feeback_index(Request $request){
        $dat = input();

        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');
            $total = Db::connect($this->config)->name('ssl_feeback')->count();
            $data = Db::connect($this->config)->name('ssl_feeback')
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            if(!empty($data)){
                foreach($data as $k=>$v){
                    $data[$k]['gogo_id'] = Db::connect($this->config)->name('user')->where(['user_id'=>$v['uid']])->find()['gogo_id'];
                    $data[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
                    if($v['type']==1){
                        $data[$k]['typename'] = '建议';
                    }elseif($v['type']==2){
                        $data[$k]['typename'] = '投诉';
                    }
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('',compact(''));
        }
    }

    public function feeback_detail(Request $request){
        $dat = input();
        $fee = Db::connect($this->config)->name('ssl_feeback')->where(['id'=>$dat['id']])->find();
        $fee['file'] = json_decode($fee['file'],true);

        return view('',compact('fee'));
    }

    //咨询管理
    public function chat_index(Request $request){
        $dat = input();
        $type = isset($dat['type'])?$dat['type']:0;
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $search = $request->get('search');

            $where = [];
            if($type==1){
                $where = ['task_type'=>0];
            }elseif($type==2){
                $where = ['task_type'=>1];
            }elseif($type==3){
                $where = ['task_type'=>2];
            }

            $total = Db::connect($this->config)->name('ssl_chat_content')->where($where)->count();
            $data = Db::connect($this->config)->name('ssl_chat_content')
                ->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            if(!empty($data)){
                foreach($data as $k=>$v){
                    $data[$k]['gogo_id'] = Db::connect($this->config)->name('user')->where(['user_id'=>$v['user_id']])->find()['gogo_id'];
                    $data[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('',compact(''));
        }
    }

    public function chat_confirm(Request $request){
        $dat = input();

        Db::connect($this->config)->name('ssl_chat_content')->where(['id'=>$dat['id']])->update(['task_type'=>1]);
        return json(['code'=>0,'msg'=>'确认成功']);
    }

    public function chat_reject(Request $request){
        $dat = input();

        Db::connect($this->config)->name('ssl_chat_content')->where(['id'=>$dat['id']])->update(['task_type'=>2]);
        return json(['code'=>0,'msg'=>'拒绝成功']);
    }

    public function workorder_list(Request $request){
        $dat = input();
        $id = $dat['id'];
        if(isset($dat['pa'])){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count = Db::connect($this->config)->name('ssl_chat_content')->where(['id'=>$id,'is_notice'=>$dat['status']])->count();
            $rows = Db::connect($this->config)->name('ssl_chat_content')
                ->where(['id'=>$id,'is_notice'=>$dat['status']])
                ->limit($limit)
                ->order($order)
                ->select();
            $_status = [0=>'未通知',1=>'已通知'];

            foreach($rows as $k=>$v){
                $rows[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
                $rows[$k]['status_name'] = $_status[$v['is_notice']];
                $rows[$k]['gogo_id'] = Db::connect($this->config)->name('user')->where(['user_id'=>$v['user_id']])->find()['gogo_id'];
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('id'));
        }
    }

    public function roam(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        if($request->isAjax()){
            $chat_info = Db::connect($this->config)->name('ssl_chat_content')->where(['id'=>$id])->find();
            $chat_obj = Db::connect($this->config)->name('ssl_chat_object')->where(['id'=>$chat_info['pid']])->find();

            if($chat_info['chat_pid']=='' || empty($chat_info['chat_pid'])){
                $res = $this->now_notice($id,'你有一则消息待查看!请打开系统查看：https://seller.gogo198.cn',$dat['user_id'],$dat['roam_type']);
            }else{
                $res = $this->now_notice($id,'你有一则消息待查看!请打开系统查看：https://www.gogo198.cn',$dat['user_id'],$dat['roam_type']);
            }
            if($res){
                Db::connect($this->config)->name('ssl_chat_content')->where(['id'=>$id])->update(['is_notice'=>1]);
                return json(['code'=>0,'msg'=>'流转成功']);
            }
        }else{
            return view('',compact('id'));
        }
    }

    #发送通知,$type=1卖家，$type=2买家
    public function now_notice($id,$text,$uid,$type){
        $data = Db::name('centralize_task')
            ->alias('a')
            ->join('centralize_workorder b','b.pid=a.id')
            ->join('centralize_process_list c','c.id=a.task_id')
            ->where(['a.id'=>$id])
            ->field(['c.content as task_name','a.id as task_id','a.serial_number','a.package_id','a.order_id'])
            ->find();
        if($type==2){
            $user = Db::name('website_user')->where(['id'=>$uid])->find();
        }elseif($type==1){
            $user = Db::name('centralize_manage_person')->where(['id'=>$uid])->find();
            $user['merch_status']=2;
        }
        if($user['merch_status']==2){
            $notice_type = Db::name('centralize_system_notice')->where(['uid'=>$user['id']])->find();
            if($notice_type['notice_type']==3){
                $user['email'] = $notice_type['account'];
            }
        }
        $res = '';
        if($user['merch_status']==2){
            #卖家
            if($user['email']!=''){
                $res = cklein_mailAli(trim($user['email']), '', '尊敬的服务商', $text);
            }elseif($user['phone']!=''){
                $post_data = [
                    'spid'=>'254560',
                    'password'=>'J6Dtc4HO',
                    'ac'=>'1069254560',
                    'mobiles'=>$user['phone'],
                    'content'=>$text.'【GOGO】',
                ];
                $post_data = json_encode($post_data,true);
                $res = httpRequest('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length:' . strlen($post_data),
                    'Cache-Control: no-cache',
                    'Pragma: no-cache'
                ),1);
            }
        }elseif($user['merch_status']==0){
            #买家
//            if($user['sns_openid']!='' && $user['merch_status']==0){
//                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx6d1af256d76896ba&secret=d19a96d909c1a167c12bb899d0c10da6";
//                $res = file_get_contents($url);
//                $result = json_decode($res, true);
//                $post2 = json_encode([
//                    'template_id'=>'GRa2BGkGrqU8g7IgMAVh6vx2iDD08uJSdK316TINQ7s',
//                    'page'=>'pages/gather/index?id='.$data['package_id'],
//                    'touser' =>$user['sns_openid'],
//                    'data'=>['thing1'=>['value'=>$text],'phrase2'=>['value'=>$opera],'time4'=>['value'=>date('Y年m月d日 H:i')]],
//                    'miniprogram_state'=>'formal',
//                    'lang'=>'zh_CN',
//                ]);
//                $res = httpRequest('https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token='.$result['access_token'], $post2,['Content-Type:application/json'],1);
//            }else
            if($user['email']!=''){
                $res = cklein_mailAli(trim($user['email']), '', '尊敬的客户', $text);
            }elseif($user['phone']!=''){
                $post_data = [
                    'spid'=>'254560',
                    'password'=>'J6Dtc4HO',
                    'ac'=>'1069254560',
                    'mobiles'=>$user['phone'],
                    'content'=>$text.'【GOGO】',
                ];
                $post_data = json_encode($post_data,true);
                $res = httpRequest('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length:' . strlen($post_data),
                    'Cache-Control: no-cache',
                    'Pragma: no-cache'
                ),1);
            }
        }

        return $res;
    }

    #热卖热搜管理 start===================================
    public function hotbuy_index(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $search = $request->get('search');
            $total = Db::connect($this->config)->name('hot_search')->count();
            $data = Db::connect($this->config)->name('hot_search')
                ->order($order)
                ->limit($limit)
                ->order('id', 'desc')
                ->select();

            if(!empty($data)){
                foreach($data as $k=>$v){

                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        }else{
            return view('',compact(''));
        }
    }

    public function save_hotbuy(Request $request){
        $dat = input();

        if($request->isAjax()){

            if($dat['type']==1){
                #关键词

                $hot_search = Db::connect($this->config)->name('hot_search')->where(['show_words'=>trim($dat['word'])])->find();
                $hot_id = 0;
                if(empty($hot_search['id'])){
                    #插入关键词
                    $hot_id = Db::connect($this->config)->name('hot_search')->insertGetId([
                        'keyword'=>trim($dat['word']),
                        'show_words'=>trim($dat['word']),
                        'is_show'=>1,
                        'created_at'=>date('Y-m-d H:i:s')
                    ]);
                }else{
                    $hot_id = $hot_search['id'];
                }

                #插入api的商品表
                $goods = httpRequest3('https://shop.gogo198.cn/collect_website/public/?s=/api/getgoods/keyword_query&keyword='.trim($dat['word']),[]);
                $goods = json_decode($goods,true);

                if($goods['code']==0){
                    foreach($goods['data'] as $k=>$v){
                        $ishave = Db::connect($this->config)->name('goods_list')->where(['goodsLink'=>$v['goodsLink']])->find();
                        if(empty($ishave)) {
                            Db::connect($this->config)->name('goods_list')->insert([
                                'hot_id' => $hot_id,
                                'goodsId' => $v['goodsId'],
                                'goodsLink' => $v['goodsLink'],
                                'goodsName' => $v['goodsName'],
                                'isSupplier' => $v['isSupplier'],
                                'picUrl' => $v['picUrl'],
                                'platform' => $v['platform'],
                                'popularity' => $v['popularity'],
                                'price' => json_encode($v['price'], true),
                                'proPrice' => json_encode($v['proPrice'], true),
                                'spuCode' => $v['spuCode'],
                            ]);
                        }
                    }
                }

                return json(['code'=>0,'msg'=>'获取商品成功']);
            }elseif($dat['type']==2){
                #商品链接
                $hot_search = Db::connect($this->config)->name('hot_search')->where(['show_words'=>trim($dat['keyword'])])->find();
                $hot_id = 0;
                if(empty($hot_search['id'])){
                    #插入关键词
                    $hot_id = Db::connect($this->config)->name('hot_search')->insertGetId([
                        'keyword'=>trim($dat['keyword']),
                        'show_words'=>trim($dat['keyword']),
                        'is_show'=>1,
                        'created_at'=>date('Y-m-d H:i:s')
                    ]);
                }else{
                    $hot_id = $hot_search['id'];
                }

                #插入api的商品表
                $goods = httpRequest3('https://shop.gogo198.cn/collect_website/public/?s=/api/getgoods/detail_query&type=2&goodsLink='.trim($dat['word']),[]);
                $goods = json_decode($goods,true);
                $ishave = Db::connect($this->config)->name('goods_list')->where(['goodsLink'=>$goods['data']['goodsLink']])->find();
                if(empty($ishave)){
                    Db::connect($this->config)->name('goods_list')->insert([
                        'hot_id'=>$hot_id,
                        'goodsId'=>$goods['data']['goodsId'],
                        'goodsLink'=>$goods['data']['goodsLink'],
                        'goodsName'=>$goods['data']['goodsName'],
                        'isSupplier'=>$goods['data']['isSupplier'],
                        'picUrl'=>$goods['data']['picUrl'],
                        'platform'=>$goods['data']['platform'],
                        'popularity'=>$goods['data']['popularity'],
                        'price'=>json_encode($goods['data']['price'],true),
                        'proPrice'=>json_encode($goods['data']['proPrice'],true),
                        'spuCode'=>$goods['data']['spuCode'],
                    ]);
                }

                return json(['code'=>0,'msg'=>'获取商品成功']);
            }
        }else{
            return view('',compact(''));
        }
    }

    public function goods_list(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $search = $request->get('search');
            $total = Db::connect($this->config)->name('goods_list')->where(['hot_id'=>$id])->count();
            $data = Db::connect($this->config)->name('goods_list')
                ->where(['hot_id'=>$id])
                ->order($order)
                ->limit($limit)
                ->order('id', 'desc')
                ->select();

            if(!empty($data)){
                foreach($data as $k=>$v){

                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,  'rows' => $data]);
        }else{
            $name = Db::connect($this->config)->name('hot_search')->where(['id'=>$id])->find()['show_words'];
            return view('',compact('id','name'));
        }
    }

    public function del_hotbuy(Request $request){
        $dat = input();

        $res = Db::connect($this->config)->name('hot_search')->where(['id'=>intval($dat['id'])])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
    #热卖热搜管理 end=====================================

    #网站管理 START=======================================
    public function base_setting(Request $request){
        $dat = input();
        if($request->isAjax()){
            $res = Db::name('website_basic')->where('id',3)->update([
                'name'=>trim($dat['name']),
                'desc'=>trim($dat['desc']),
                'keywords'=>trim($dat['keywords']),
                'mobile'=>trim($dat['mobile']),
                'email'=>trim($dat['email']),
                'service_time'=>trim($dat['service_time']),
                'join_us'=>trim($dat['join_us']),
                'service_rule'=>intval($dat['service_rule']),
                'privacy_rule'=>intval($dat['privacy_rule']),
                'slogo'=>$dat['slogo_file'][0],
                'logo'=>$dat['logo_file'][0],
                'inpic'=>$dat['inpic'][0],//首页背景图
//                'color'=>$dat['color'],
//                'color_inner'=>$dat['color_inner'],
//                'color_word'=>$dat['color_word'],
                'copyright'=>json_encode($dat['copyright'],true),
                'content'=>json_encode($dat['content'],true),
            ]);

//            if($res){
            return json(['code' => 0, 'msg' => '保存成功！']);
//            }
        }else{
//            $data = Db::name('centralize_basicinfo')->where(['id'=>1])->find();
            $data = Db::name('website_basic')->where('id',3)->find();
            $data['copyright'] = json_decode($data['copyright'],true);
            $data['content'] = json_decode($data['content'],true);

            $rule = Db::name('website_platform_rule')->select();
            return view('',compact('data','rule'));
        }
    }

    //轮播图管理
    public function rotate_manage(Request $request){
        $dat = input();
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):3;

        if( request()->isPost() || request()->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('website_rotate')->where(['system_id'=>$system_id])->order($order)->count();
            $rows = DB::name('website_rotate')
                ->where(['system_id'=>$system_id])
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

    public function save_rotate(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;
        if($request->isAjax()){
            if($id>0){
                Db::name('website_rotate')->where('id',$id)->update([
                    'title'=>$dat['title'],
                    'thumb'=>$dat['thumb'][0],
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):0,
                    'other_pic'=>$dat['go_other']==3?trim($dat['other_pic']):0,
                    'other_msg'=>$dat['go_other']==4?trim($dat['other_msg']):0,
                    'system_id'=>$system_id
                ]);
            }else{
                Db::name('website_rotate')->insert([
                    'title'=>$dat['title'],
                    'thumb'=>$dat['thumb'][0],
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):0,
                    'other_pic'=>$dat['go_other']==3?trim($dat['other_pic']):0,
                    'other_msg'=>$dat['go_other']==4?trim($dat['other_msg']):0,
                    'system_id'=>$system_id
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功！']);
        }else{
            $data = ['title'=>'','thumb'=>'','go_other'=>'','other_link'=>'','other_navbar'=>'','other_pic'=>'','other_msg'=>''];
            if($id>0){
                $data = Db::name('website_rotate')->where('id',$id)->find();
            }

            #应用链接
            $list = Db::connect($this->config)->name('guide_frame')->where(['type'=>1])->select();

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

    public function del_rotate(Request $request){
        $dat = input();
        $res = Db::name('website_rotate')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }

    //弹框管理
    public function frame_manage(Request $request){
        $dat = input();
        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::connect($this->config)->name('frame_body')->where(['pid'=>0])->order($order)->count();
            $rows = DB::connect($this->config)->name('frame_body')
                ->where(['pid'=>0])
                ->limit($limit)
                ->order($order)
                ->select();

            $typename = ['搜索浮窗','右侧浮窗'];
            foreach($rows as $k=>$v){
                $rows[$k]['typename'] = $typename[$v['type']-1];
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    public function frame_child(Request $request){
        $dat = input();
        $pid = $dat['pid'];

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::connect($this->config)->name('frame_body')->where(['pid'=>$pid])->order($order)->count();
            $rows = DB::connect($this->config)->name('frame_body')
                ->where(['pid'=>$pid])
                ->limit($limit)
                ->order($order)
                ->select();


            foreach($rows as $k=>$v){

            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('pid'));
        }
    }

    public function save_frame(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?$dat['pid']:0;
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isAjax()){
            $icon = '';
            if($pid==0){
                if($dat['type']==2){
                    $icon = $dat['icon'][0];
                }
            }
            if($id>0){
                Db::connect($this->config)->name('frame_body')->where('id',$id)->update([
                    'type'=>isset($dat['type'])?$dat['type']:0,
                    'pid'=>$pid,
                    'name'=>trim($dat['name']),
                    'icon'=>$icon,
                    'have_child'=>isset($dat['have_child'])?$dat['have_child']:2,
                    'app_id'=>$dat['app_id'],
                ]);
            }else{
                Db::connect($this->config)->name('frame_body')->insert([
                    'type'=>isset($dat['type'])?$dat['type']:0,
                    'pid'=>$pid,
                    'name'=>trim($dat['name']),
                    'icon'=>$icon,
                    'have_child'=>isset($dat['have_child'])?$dat['have_child']:2,
                    'app_id'=>$dat['app_id'],
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功！']);
        }else{
            $data = ['type'=>0,'name'=>'','icon'=>'','displayorder'=>'','have_child'=>1,'app_id'=>''];
            if($id>0){
                $data = Db::connect($this->config)->name('frame_body')->where('id',$id)->find();
            }
            $content = Db::connect($this->config)->name('guide_frame')->where(['type'=>1])->order('order','asc')->select();
            if($pid==11){

            }
            return view('',compact('data','id','pid','content'));
        }
    }

    public function del_frame(Request $request){
        $dat = input();
        $res = Db::connect($this->config)->name('frame_body')->where('id',$dat['id'])->delete();
        if($res){
            Db::connect($this->config)->name('frame_body')->where('pid',$dat['id'])->delete();
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }

    #添加广告图
    public function save_frameadv(Request $request){
        $dat = input();
        if($request->isAjax()){
            Db::connect($this->config)->name('frame_adv')->where(['id'=>1])->update([
               'img'=>$dat['leftimg'][0],
                'link'=>trim($dat['leftlink'])
            ]);

            Db::connect($this->config)->name('frame_adv')->where(['id'=>2])->update([
                'img'=>$dat['searchimg'][0],
                'link'=>trim($dat['searchlink'])
            ]);

            Db::connect($this->config)->name('frame_adv')->where(['id'=>3])->update([
                'img'=>$dat['systemimg'][0],
                'link'=>trim($dat['systemlink'])
            ]);

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $adv = Db::connect($this->config)->name('frame_adv')->select();
            return view('',compact('adv'));
        }
    }

    #搜索栏管理配置
    public function search_manage(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:1;
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;

        if($request->isAjax()){
            $res = Db::connect($this->config)->name('search_setting')->where(['id'=>$id,'system_id'=>$system_id])->update([
                'system_id'=>$system_id,
                'title'=>isset($dat['title'])?trim($dat['title']):'',
                'desc'=>isset($dat['desc'])?trim($dat['desc']):'',
                'search_title'=>trim($dat['search_title']),
                'img'=>$dat['img'][0],
                'back_img'=>isset($dat['back_img'])?$dat['back_img'][0]:'',
            ]);

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = Db::connect($this->config)->name('search_setting')->where(['id'=>$id,'system_id'=>$system_id])->find();
            return view('',compact('data','id','system_id'));
        }
    }

    #信息栏管理配置
    public function message_manage(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:1;
        if($request->isAjax()){
            $res = Db::connect($this->config)->name('message_setting')->where(['id'=>$id])->update([
                'news_type'=>intval($dat['news_type']),
                'times_type'=>intval($dat['times_type']),
            ]);

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = Db::connect($this->config)->name('message_setting')->where(['id'=>$id])->find();
            return view('',compact('data','id'));
        }
    }

    #清除淘中国缓存
    public function website_cache(Request $request){
        // 定义 Laravel 项目路径
        $projectPath = '/www/wwwroot/shopping.gogo198.cn';

        // 要执行的 Artisan 命令
        $command = "cd $projectPath && php artisan cache:clear 2>&1";

        // 执行命令并捕获输出
        $output = shell_exec($command);

        // 显示输出结果（可选）
        echo "<pre>$output</pre> 淘中国站点 缓存更新时间".date('Y-m-d H:i:s');
    }

    #导页管理START==================================================
    #版式管理
    public function format_manage(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::connect($this->config)->name('guide_format')->where(['isshow'=>0])->order($order)->count();
            $rows = DB::connect($this->config)->name('guide_format')
                ->where(['isshow'=>0])
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>$v){

            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    public function save_format(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){
            if($id>0){
                $res = Db::connect($this->config)->name('guide_format')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'type'=>intval($dat['type']),
                    'type2'=>intval($dat['type2']),
                ]);
            }else{
                $res = Db::connect($this->config)->name('guide_format')->insert([
                    'name'=>trim($dat['name']),
                    'type'=>intval($dat['type']),
                    'type2'=>intval($dat['type2']),
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>'','type'=>'','type2'=>''];
            if($id>0){
                $data = Db::connect($this->config)->name('guide_format')->where(['id'=>$id])->find();
            }

            $type = [
//                ['id'=>1,'name'=>'商品展示'],['id'=>2,'name'=>'卡片展示【小卡片】'],['id'=>3,'name'=>'评价展示'],['id'=>4,'name'=>'卡片展示【大卡片+小卡片】'],['id'=>5,'name'=>'瀑布流图文展示'],['id'=>6,'name'=>'信息滚动展示'],
                ['id'=>7,'name'=>'平台推荐'],['id'=>8,'name'=>'产业集聚'],['id'=>9,'name'=>'环球节庆']
            ];
            $type2 = [['id'=>1,'name'=>'图文内容'],['id'=>2,'name'=>'消息内容'],['id'=>3,'name'=>'商品-类别'],['id'=>4,'name'=>'商品-关键词'],['id'=>9,'name'=>'商品-定时更新'],['id'=>5,'name'=>'店铺数据'],['id'=>6,'name'=>'应用链接'],['id'=>7,'name'=>'政策内容'],['id'=>8,'name'=>'网页链接'],['id'=>10,'name'=>'瀑布流图文内容'],['id'=>11,'name'=>'医讯网内容'],['id'=>12,'name'=>'商户店铺'],['id'=>13,'name'=>'产业内容'],['id'=>14,'name'=>'节日内容']];

            return view('',compact('data','id','type','type2'));
        }
    }

    public function del_format(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        $res = Db::connect($this->config)->name('guide_format')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #导页管理
    public function guide_manage(Request $request){
        $dat = input();
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::connect($this->config)->name('guide_body')->where(['system_id'=>$system_id,'company_id'=>0])->order($order)->count();
            $rows = DB::connect($this->config)->name('guide_body')
                ->where(['system_id'=>$system_id,'company_id'=>0])
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>$v){
                $rows[$k]['format'] = Db::connect($this->config)->name('guide_format')->where(['id'=>$v['content_id']])->find();
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('system_id'));
        }
    }

    #保存导页
    public function save_guide(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;

        if($request->isAjax()){
            if($id>0){
                $res = Db::connect($this->config)->name('guide_body')->where(['id'=>$id])->update([
                    'displayorders'=>intval($dat['displayorders']),
//                    'name'=>trim($dat['name']),
                    'title'=>trim($dat['title']),
//                    'desc'=>trim($dat['desc']),
                    'content_id'=>intval($dat['content_id']),
//                    'gcateid'=>isset($dat['gcateid'])?intval($dat['gcateid']):'',
                    'gkeywords'=>isset($dat['gkeywords'])?trim($dat['gkeywords']):'',
//                    'starttime'=>isset($dat['starttime'])?trim($dat['starttime']):'',
//                    'endtime'=>isset($dat['endtime'])?trim($dat['endtime']):'',
//                    'introduce'=>isset($dat['introduce'])?json_encode($dat['introduce'],true):'',
//                    'more_id'=>intval($dat['more_id']),
//                    'format_type'=>intval($dat['format_type']),
//                    'format_content'=>intval($dat['format_type'])==1?json_encode($dat['format_content'],true):'',
//                    'back_type'=>$dat['back_type'],
//                    'back_content'=>$dat['back_type']==1?$dat['back_content']:$dat['back_img'][0],
                ]);
            }else{
                $res = Db::connect($this->config)->name('guide_body')->insertGetId([
                    'system_id'=>$system_id,
                    'displayorders'=>intval($dat['displayorders']),
//                    'name'=>trim($dat['name']),
                    'title'=>trim($dat['title']),
//                    'desc'=>trim($dat['desc']),
                    'content_id'=>intval($dat['content_id']),
//                    'gcateid'=>isset($dat['gcateid'])?intval($dat['gcateid']):'',
                    'gkeywords'=>isset($dat['gkeywords'])?trim($dat['gkeywords']):'',
//                    'starttime'=>isset($dat['starttime'])?trim($dat['starttime']):'',
//                    'endtime'=>isset($dat['endtime'])?trim($dat['endtime']):'',
//                    'introduce'=>isset($dat['introduce'])?json_encode($dat['introduce'],true):'',
//                    'more_id'=>intval($dat['more_id']),
//                    'format_type'=>intval($dat['format_type']),
//                    'format_content'=>intval($dat['format_type'])==1?json_encode($dat['format_content'],true):'',
//                    'back_type'=>$dat['back_type'],
//                    'back_content'=>$dat['back_type']==1?$dat['back_content']:$dat['back_img'][0],
                    'createtime'=>time()
                ]);

                if(isset($dat['gkeywords'])){
                    $keywords_arr = explode('、',$dat['gkeywords']);
                    foreach($keywords_arr as $k2=>$v2) {
                        $ishave = Db::connect($this->config)->name('goods_keywords')->where(['keywords' => $v2])->find();
                        if (empty($ishave) && !empty($v2)) {
                            Db::connect($this->config)->name('goods_keywords')->insert(['keywords' => trim($v2)]);
                        }
                    }
//                    if(!empty($dat['gkeywords']) && !empty($dat['starttime']) && !empty($dat['endtime'])){
//                        #新增时自动获取商品(队列服务)
//                        $options = array('http' => array('timeout' => 7500));
//                        $context = stream_context_create($options);
//                        file_get_contents('https://decl.gogo198.cn/api/v2/get_content_goods?type=1&id='.$res, false, $context);
//                    }
                }
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>'','title'=>'','desc'=>'','content_id'=>0,'gcateid'=>0,'gkeywords'=>'','starttime'=>'','endtime'=>'','more_id'=>0,'format_type'=>0,'format_content'=>['name'=>[''],'desc'=>['']],'back_type'=>0,'back_content'=>'','introduce'=>'','displayorders'=>1];
            if($id>0){
                $data = Db::connect($this->config)->name('guide_body')->where(['id'=>$id])->find();
                #废弃
                if($data['format_type']==1){
                    $data['format_content'] = json_decode($data['format_content'],true);
                }
                if(!empty($data['introduce'])){
                    $data['introduce'] = json_decode($data['introduce'],true);
                }
                #废弃
                $data['format_info'] = Db::connect($this->config)->name('guide_format')->where(['id'=>$data['content_id']])->find();
            }else{
                $num = Db::connect($this->config)->name('guide_body')->where(['system_id'=>$system_id,'company_id'=>0])->count();
                $data['displayorders'] = $num + 1;
            }

            $content = Db::connect($this->config)->name('guide_frame')->where(['type'=>1])->order('order','asc')->select();
            $content2 = Db::connect($this->config)->name('guide_format')->where(['isshow'=>0])->order('id','asc')->select();
            $catearr = json_encode(get_category($this->config),true);
            
            return view('',compact('data','id','content','content2','catearr','system_id'));
        }
    }

    #获取版式内容导流要求
    public function get_format_request(Request $request){
        $dat = input();
        $id = intval($dat['format_id']);
        $format = Db::connect($this->config)->name('guide_format')->where(['id'=>$id])->find();

        // $catearr = [];
        // if($format['type2']==3){
        //     $catearr = get_category($this->config);
        // }

        return json(['code'=>0,'format'=>$format]);
    }

    public function del_guide(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        $res = Db::connect($this->config)->name('guide_body')->where(['id'=>$id])->delete();
        if($res){
            Db::connect($this->config)->name('guide_content')->where(['pid'=>$id])->delete();
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #导页内容管理
    public function guide_content_manage(Request $request){
        $dat = input();
        $pid = intval($dat['pid']);
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::connect($this->config)->name('guide_content')->where(['system_id'=>$system_id,'pid'=>$pid,'top_id'=>0])->order($order)->count();
            $rows = DB::connect($this->config)->name('guide_content')
                ->where(['system_id'=>$system_id,'pid'=>$pid,'top_id'=>0])
                ->limit($limit)
                ->order($order)
                ->select();

            $_status = ['显示','隐藏'];
            foreach($rows as $k=>$v){
                $rows[$k]['show_status'] = $_status[$v['is_show']];
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            $guide_body = Db::connect($this->config)->name('guide_body')->where(['id'=>$pid])->find();
            $guide_format = Db::connect($this->config)->name('guide_format')->where(['id'=>$guide_body['content_id']])->find();
            return view('',compact('pid','system_id','guide_format'));
        }
    }

    #导入导页exl内容
    public function import_guide_content(Request $request){
        $dat = input();
        $system_id = $dat['system_id'];
        $pid = $dat['pid'];
        if($request->isAjax()){
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
                #产业带名称
                $industry_name = $PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue();
                #板块名称
                $content_name2 = $PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue();
                #板块描述
                $content_desc = $PHPRead->getActiveSheet()->getCell("C".$currentRow)->getValue();
                #板块关键字
                $content_keywords = $PHPRead->getActiveSheet()->getCell("D".$currentRow)->getValue();

               if(!empty($industry_name)){
                   #1、获取产业带id
                   $ishave = Db::connect($this->config)->name('guide_content')->where(['name'=>$industry_name])->find();
                   $top_id=0;
                   if(empty($ishave)){
                       $top_id = Db::connect($this->config)->name('guide_content')->insertGetId([
                           'system_id'=>$system_id,
                           'pid'=>$pid,
                           'type'=>1,
                           'name'=>$industry_name,
                           'back_type'=>2,
                           'back_content'=>'collect_website/public/uploads/centralize/website_index/'.$industry_name.'.jpg',
                           'go_other'=>3,
                       ]);
                   }else{
                       $top_id = $ishave['id'];
                   }

                   #2、插入板块信息
                   $ishave2 = Db::connect($this->config)->name('guide_content')->where(['name'=>$content_name2,'top_id'=>$top_id])->find();
                   if(empty($ishave2)){
                       Db::connect($this->config)->name('guide_content')->insert([
                           'system_id'=>$system_id,
                           'pid'=>$pid,
                           'top_id'=>$top_id,
                           'type'=>0,
                           'name'=>$content_name2,
                           'desc'=>$content_desc,
                           'back_type'=>2,
                           'back_content'=>'collect_website/public/uploads/centralize/website_index/'.$content_name2.'.jpg',
                           'gkeywords'=>$content_keywords
                       ]);
                   }
               }
            }

            @unlink($fileName);
            return json(['code'=>0,'msg'=>'已上传']);
        }else{
            return view('',compact('system_id','pid'));
        }
    }

    public function save_guide_content(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $pid = isset($dat['pid'])?$dat['pid']:0;
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;
        $top_id = isset($dat['top_id'])?intval($dat['top_id']):0;

        if($request->isAjax()){
            $type = isset($dat['type'])?intval($dat['type']):0;
            if($type==1){
                #大卡片
                $dat['gkeywords']='';
                $dat['starttime']='';
                $dat['endtime']='';

            }elseif($type==0){
                #小卡片
                $dat['other_pic']=0;
                $dat['other_navbar']=0;
            }

            if($id>0){
                $res = Db::connect($this->config)->name('guide_content')->where(['id'=>$id])->update([
                    'type'=>$type,
                    'name'=>trim($dat['name']),
                    'desc'=>$top_id!=0?trim($dat['desc']):'',
                    'gcateid'=>isset($dat['gcateid'])?intval($dat['gcateid']):'',
                    'gkeywords'=>isset($dat['gkeywords'])?trim($dat['gkeywords']):'',
                    'starttime'=>isset($dat['starttime'])?trim($dat['starttime']):'',
                    'endtime'=>isset($dat['endtime'])?trim($dat['endtime']):'',
                    'go_other'=>isset($dat['go_other'])?intval($dat['go_other']):0,
                    'link'=>isset($dat['link'])?trim($dat['link']):'',
                    'other_navbar'=>isset($dat['other_navbar'])?intval($dat['other_navbar']):'',
                    'other_pic'=>isset($dat['other_pic'])?intval($dat['other_pic']):'',
                    'other_msg'=>isset($dat['other_msg'])?intval($dat['other_msg']):'',
                    'other_shop'=>isset($dat['other_shop'])?intval($dat['other_shop']):'',
                    'other_privacy'=>isset($dat['other_privacy'])?intval($dat['other_privacy']):'',
                    'back_type'=>$dat['back_type'],
                    'back_content'=>$dat['back_type']==1?$dat['back_content']:$dat['back_img'][0],
                    'medical_type'=>isset($dat['medical_type'])?intval($dat['medical_type']):0,
                    'is_show'=>$dat['is_show'],
                    'displayorders'=>intval($dat['displayorders']),
                ]);
            }else{
                $res = Db::connect($this->config)->name('guide_content')->insertGetId([
                    'system_id'=>$system_id,
                    'pid'=>$pid,
                    'type'=>$type,
                    'top_id'=>$top_id,
                    'name'=>trim($dat['name']),
                    'desc'=>$top_id!=0?trim($dat['desc']):'',
                    'gcateid'=>isset($dat['gcateid'])?intval($dat['gcateid']):'',
                    'gkeywords'=>isset($dat['gkeywords'])?trim($dat['gkeywords']):'',
                    'starttime'=>isset($dat['starttime'])?trim($dat['starttime']):'',
                    'endtime'=>isset($dat['endtime'])?trim($dat['endtime']):'',
                    'go_other'=>isset($dat['go_other'])?intval($dat['go_other']):0,
                    'link'=>isset($dat['link'])?trim($dat['link']):'',
                    'other_navbar'=>isset($dat['other_navbar'])?intval($dat['other_navbar']):'',
                    'other_pic'=>isset($dat['other_pic'])?intval($dat['other_pic']):'',
                    'other_msg'=>isset($dat['other_msg'])?intval($dat['other_msg']):'',
                    'other_shop'=>isset($dat['other_shop'])?intval($dat['other_shop']):'',
                    'other_privacy'=>isset($dat['other_privacy'])?intval($dat['other_privacy']):'',
                    'back_type'=>$dat['back_type'],
                    'back_content'=>$dat['back_type']==1?$dat['back_content']:$dat['back_img'][0],
                    'medical_type'=>isset($dat['medical_type'])?intval($dat['medical_type']):0,
                    'is_show'=>$dat['is_show'],
                    'displayorders'=>intval($dat['displayorders']),
                ]);

                if(isset($dat['gkeywords']) && isset($dat['starttime']) && isset($dat['endtime'])) {
                    if(!empty($dat['gkeywords']) && !empty($dat['starttime']) && !empty($dat['endtime'])){
                        #插入关键字
                        $keywords_arr = explode('、',$dat['gkeywords']);
                        foreach($keywords_arr as $k2=>$v2) {
                            $ishave = Db::connect($this->config)->name('goods_keywords')->where(['keywords' => $v2])->find();
                            if (empty($ishave) && !empty($v2)) {
                                Db::connect($this->config)->name('goods_keywords')->insert(['keywords' => trim($v2)]);
                            }
                        }

                        #新增时自动获取商品(队列服务)
                        $options = array('http' => array('timeout' => 7500));
                        $context = stream_context_create($options);
                        file_get_contents('https://decl.gogo198.cn/api/v2/get_content_goods?type=2&id=' . $res, false, $context);
                    }
                }
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>'','type'=>1,'desc'=>'','is_show'=>0,'gcateid'=>'','gkeywords'=>'','starttime'=>'','endtime'=>'','go_other'=>0,'link'=>'','other_navbar'=>0,'other_pic'=>0,'other_msg'=>0,'other_shop'=>0,'other_privacy'=>0,'back_type'=>0,'back_content'=>'','introduce'=>'','displayorders'=>1,'medical_type'=>0];
            if($id>0){
                $data = Db::connect($this->config)->name('guide_content')->where(['id'=>$id])->find();
            }else{
                $num = 0;
                if($top_id>0){
                    $num = Db::connect($this->config)->name('guide_content')->where(['pid'=>$pid,'top_id'=>$top_id])->count();
                }else{
                    $num = Db::connect($this->config)->name('guide_content')->where(['pid'=>$pid,'top_id'=>0])->count();
                }

                $data['displayorders'] = $num + 1;
            }
            if($top_id>0){
                $data['type'] = 0;
            }
            #导页信息
            $data['body_info'] = Db::connect($this->config)->name('guide_body')->where(['id'=>$pid])->find();
            #版式信息
            $data['format_info'] = Db::connect($this->config)->name('guide_format')->where(['id'=>$data['body_info']['content_id']])->find();

            $catearr = [];
            if($data['format_info']['type2']==1) {
                #图文
                $data['pic_list'] = Db::name('website_image_txt')->select();
                foreach ($data['pic_list'] as $k => $v) {
                    $data['pic_list'][$k]['name'] = json_decode($v['name'], true)['zh'];
                }
            }
            elseif($data['format_info']['type2']==2) {
                #消息
                $data['msg_list'] = Db::name('website_message_manage')->select();
            }
            elseif($data['format_info']['type2']==3) {
                #商品类别
                $catearr = json_encode(get_category($this->config),true);
                #图文
                $data['pic_list'] = Db::name('website_image_txt')->select();
                foreach ($data['pic_list'] as $k => $v) {
                    $data['pic_list'][$k]['name'] = json_decode($v['name'], true)['zh'];
                }
            }
            elseif($data['format_info']['type2']==4){
                #商品关键词
                #图文
                $data['pic_list'] = Db::name('website_image_txt')->select();
                foreach ($data['pic_list'] as $k => $v) {
                    $data['pic_list'][$k]['name'] = json_decode($v['name'], true)['zh'];
                }
            }
            elseif($data['format_info']['type2']==5) {
                #店铺
                $data['shop_list'] = Db::connect($this->config)->name('shop')->select();
            }
            elseif($data['format_info']['type2']==6) {
                #应用
                $data['app_list'] = Db::connect($this->config)->name('guide_frame')->where(['type'=>1])->select();
            }
            elseif($data['format_info']['type2']==7) {
                #政策
                $data['privacy_list'] = Db::name('policy_list')->where(['type'=>1])->select();
                foreach($data['privacy_list'] as $k=>$v){
                    $data['privacy_list'][$k]['name'] = json_decode($v['name'], true)['zh'];
                }
            }

            return view('',compact('data','id','pid','catearr','system_id','top_id'));
        }
    }

    public function del_guide_content(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        $res = Db::connect($this->config)->name('guide_content')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    public function guide_content_manage2(Request $request){
        $dat = input();
        $pid = intval($dat['pid']);
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;
        $top_id = intval($dat['top_id']);

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::connect($this->config)->name('guide_content')->where(['system_id'=>$system_id,'pid'=>$pid,'top_id'=>$top_id])->order($order)->count();
            $rows = DB::connect($this->config)->name('guide_content')
                ->where(['system_id'=>$system_id,'pid'=>$pid,'top_id'=>$top_id])
                ->limit($limit)
                ->order($order)
                ->select();

            $_status = ['显示','隐藏'];
            foreach($rows as $k=>$v){
                $rows[$k]['show_status'] = $_status[$v['is_show']];
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('pid','system_id','top_id'));
        }
    }

    #节日-国家管理
    public function festival_manage(Request $request){
        $dat = input();

//        $festival = Db::name('website_festival')->select();
//        foreach($festival as $k=>$v){
//            Db::name('website_festival')->where(['id'=>$v['id']])->update([
//                'back_content'=>'collect_website/public/uploads/centralize/website_index/'.$v['en_name'].'.jpg'
//            ]);
//        }

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('centralize_diycountry_content')->where(['pid'=>5])->order($order)->count();
            $rows = DB::name('centralize_diycountry_content')
                ->where(['pid'=>5])
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

    #国家-节日管理
    public function festival_manage2(Request $request){
        $dat = input();
        $pid = $dat['pid'];

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('website_festival')->where(['country_id'=>$pid])->order($order)->count();
            $rows = DB::name('website_festival')
                ->where(['country_id'=>$pid])
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>$v){

            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('pid'));
        }
    }

    #配置节日
    public function save_festival(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $pid = isset($dat['pid'])?$dat['pid']:0;

        if($request->isAjax()){
            $res = Db::name('website_festival')->where(['id'=>$id])->update([
                'keywords'=>trim($dat['keywords']),
//                'starttime'=>strtotime($dat['starttime']),
//                'endtime'=>strtotime($dat['endtime']),
//                'back_content'=>$dat['back_img'][0],
                'month_times'=>$dat['month_times']
            ]);
            if($res){
                if(isset($dat['keywords'])) {
                    if(!empty($dat['keywords'])){
                        #新增时自动获取商品(队列服务)
                        $options = array('http' => array('timeout' => 7500));
                        $context = stream_context_create($options);
                        file_get_contents('https://decl.gogo198.cn/api/v2/get_content_goods?type=3&id=' . $id, false, $context);
                    }
                }
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = ['keywords'=>'','starttime'=>'','endtime'=>'','back_content'=>''];

            if($id>0){
                $data = Db::name('website_festival')->where(['country_id'=>$pid,'id'=>$id])->find();
            }

            return view('',compact('data','id','pid'));
        }
    }

    public function save_festival_img(Request $request){
        $dat = input();

        if($request->isAjax()){

        }
        else{
            return view('',compact(''));
        }
    }

    #录入节日
    public function save_festival_backup(Request $request){
        $dat = input();

        if($request->isAjax()){
            $txt = trim($dat['festival_txt']);
            $txt = rtrim(str_replace('等。','。',$txt),"。");
            $txt = explode("。",$txt);
            #去除“1.，2.”
            foreach($txt as $k=>$v){
                for($i=1;$i<73;$i++){
//                    $txt[$k] = str_replace($i.'. ','',$v);
//                    $txt[$k] = str_replace($i.'.','',$v);
                    $c = explode($i.'. ',$v);
                    if(count($c)>1){
                        $txt[$k] = $c[1];
                        break;
                    }
                }
            }

            foreach($txt as $k=>$v){
                try{
                    $txt1 = [];$txt2 = [];
                    $txt1 = explode("：",$v);
                    $txt2 = explode("（",$txt1[0]);
                    $en_name = $txt2[0];
                    $txt2 = explode("）",$txt2[1]);
                    $zh_name = $txt2[0];
                    $keywords = $txt1[1];
                    $ishave = Db::name('website_festival_backup')->where(['en_name'=>$en_name])->find();
                    if(empty($ishave)){
                        Db::name('website_festival_backup')->insert([
                            'en_name'=>$en_name,
                            'zh_name'=>$zh_name,
                            'keywords'=>$keywords
                        ]);
                    }
                }
                catch(\Exception $e) {
                    print_r('第'.$k.'个出错，，，，，');
                    dd($e->getMessage());
                }
            }

            return json(['code'=>0,'msg'=>'录入成功']);
        }
        else{
            return view('',compact(''));
        }
    }

    #导出节日
    public function export_festival_backup(Request $request){
        $data = Db::name('website_festival_backup')->select();
        #输出excel表格
        $fileName = '节日['.date('Y-m-d H:i:s').'].xls';
        $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes';
        $dir2 = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
        require_once($dir."/PHPExcel.php");
        require_once($dir2."/IOFactory.php");
        $Excel = new PHPExcel();
        $PHPSheet = $Excel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('节日列表'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue('A1', '英文名称');
        $PHPSheet->setCellValue('B1', '中文名称');
        $PHPSheet->setCellValue('C1', '关键字');
        $ExcelWrite = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');
        $n = 2;

        foreach ($data as $value) {
            $PHPSheet->setCellValue('A'.$n,"\t" .$value['en_name']."\t");
            $PHPSheet->setCellValue('B'.$n,"\t" .$value['zh_name']."\t");
            $PHPSheet->setCellValue('C'.$n,"\t" .$value['keywords']."\t");
            $n +=1;
        }

        ob_end_clean();//清楚缓冲避免乱码
        header('pragma:public');
        //设置表头信息
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name='.$fileName);
        header("Content-Disposition:attachment;filename={$fileName}");//attachment新窗口打
        return $ExcelWrite->save('php://output');
    }

    #检查（合并）节日
    public function check_festival(Request $request){
        $dat = input();

        #1、查旧表英文下无中文的，然后插入中文。
        $data = Db::name('website_festival_backup')->select();
        foreach($data as $k=>$v){
            $ishave = Db::name('website_festival')->where(['en_name'=>$v['en_name']])->select();
            if(!empty($ishave)){
                foreach($ishave as $k2=>$v2){
                    Db::name('website_festival')->where(['id'=>$v2['id']])->update([
                        'zh_name'=>$v['zh_name'],
                        'keywords'=>$v['keywords']
                    ]);
                }
            }
        }

        #2、然后再将无中文的导出来。
        $data2 = Db::name('website_festival')->whereRaw('zh_name = ""')->select();
        #输出excel表格
        $fileName = '无中文的节日['.date('Y-m-d H:i:s').'].xls';
        $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes';
        $dir2 = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
        require_once($dir."/PHPExcel.php");
        require_once($dir2."/IOFactory.php");
        $Excel = new PHPExcel();
        $PHPSheet = $Excel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('节日列表'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue('A1', '英文名称');
        $PHPSheet->setCellValue('B1', '中文名称');
        $PHPSheet->setCellValue('C1', '关键字');
        $ExcelWrite = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');
        $n = 2;

        foreach ($data2 as $value) {
            $PHPSheet->setCellValue('A'.$n,"\t" .$value['en_name']."\t");
            $PHPSheet->setCellValue('B'.$n,"\t" .$value['zh_name']."\t");
            $PHPSheet->setCellValue('C'.$n,"\t" .''."\t");
            $n +=1;
        }

        ob_end_clean();//清楚缓冲避免乱码
        header('pragma:public');
        //设置表头信息
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name='.$fileName);
        header("Content-Disposition:attachment;filename={$fileName}");//attachment新窗口打
        return $ExcelWrite->save('php://output');
    }
    #导页管理END====================================================

    #页脚管理
    public function footer_manage(Request $request){
        $dat = input();
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;
        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::connect($this->config)->name('footer_body')->where(['pid'=>0,'system_id'=>$system_id])->order($order)->count();
            $rows = DB::connect($this->config)->name('footer_body')
                ->where(['pid'=>0,'system_id'=>$system_id])
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

    public function footer_child(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?$dat['pid']:0;
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;
        
        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::connect($this->config)->name('footer_body')->where(['pid'=>$pid,'system_id'=>$system_id])->order($order)->count();
            $rows = DB::connect($this->config)->name('footer_body')
                ->where(['pid'=>$pid,'system_id'=>$system_id])
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>$v){

            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('pid','system_id'));
        }
    }

    #保存页脚
    public function save_footer(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $pid = isset($dat['pid'])?$dat['pid']:0;
        $system_id = isset($dat['system_id'])?intval($dat['system_id']):0;
        
        if($request->isAjax()){
            $app_id = '';
            if(isset($dat['have_link'])){
                if($dat['have_link']==1){
                    $app_id = $dat['app_id'];
                }
            }
            $content_id = 0;
            if(isset($dat['type'])){
                if($dat['type']==1){
                    $content_id = $dat['content_id1'];
                }elseif($dat['type']==2){
                    $content_id = isset($dat['content_id2'])?$dat['content_id2']:'';
                }elseif($dat['type']==3){
                    $content_id = $dat['content_id3'];
                }elseif($dat['type']==4){
                    $content_id = $dat['content_id4'];
                }elseif($dat['type']==5){
                    $content_id = $dat['content_id5'];
                }
            }

            if($id>0){
                $res = Db::connect($this->config)->name('footer_body')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'type'=>isset($dat['type'])?$dat['type']:'',
                    'content_id'=>$content_id,
                    'have_link'=>isset($dat['have_link'])?$dat['have_link']:'',
                    'app_id'=>$app_id,
                    'url'=>$dat['type']==6?trim($dat['url']):'',
                ]);
            }else{
                $res = DB::connect($this->config)->name('footer_body')->insert([
                    'pid'=>$pid,
                    'name'=>trim($dat['name']),
                    'type'=>isset($dat['type'])?$dat['type']:'',
                    'content_id'=>$content_id,
                    'have_link'=>isset($dat['have_link'])?$dat['have_link']:'',
                    'app_id'=>$app_id,
                    'url'=>$dat['type']==6?trim($dat['url']):'',
                    'system_id'=>$system_id
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>'','type'=>1,'content_id'=>'','have_link'=>0,'app_id'=>'','url'=>''];
            if($id>0){
                $data = Db::connect($this->config)->name('footer_body')->where(['id'=>$id])->find();
            }

            
            #应用链接
            $appLink = Db::connect($this->config)->name('guide_frame')->where(['type'=>1])->order('order','asc')->select();
            #政策链接
            $policyLink = Db::name('policy_list')->where('cate_id in (12,13,14) ')->select();
            foreach($policyLink as $k=>$v){
                $policyLink[$k]['name'] = json_decode($v['name'],true)['zh'];
            }
            #消息链接
            $msgLink = Db::name('website_message_manage')->select();
            #规则链接
            $ruleLink = Db::name('website_platform_rule')->select();
            #图文链接
            $imgLink = Db::name('website_image_txt')->select();
            foreach($imgLink as $k=>$v){
                $imgLink[$k]['name'] = json_decode($v['name'],true)['zh'];
            }

            if($system_id==4){
                $appLink = Db::name('website_navbar')->where(['system_id'=>4,'company_id'=>0,'pid'=>0])->select();
                foreach($appLink as $k=>$v){
                    $appLink[$k]['name'] = json_decode($v['name'],true)['zh'];
                    $appLink[$k]['children'] = Db::name('website_navbar')->where(['system_id'=>4,'company_id'=>0,'pid'=>$v['id']])->select();
                    foreach($appLink[$k]['children'] as $k2=>$v2){
                        $appLink[$k]['children'][$k2]['name'] = '-'.json_decode($v2['name'],true)['zh'];
                        $appLink[$k]['children'][$k2]['children'] = Db::name('website_navbar')->where(['system_id'=>4,'company_id'=>0,'pid'=>$v2['id']])->select();
                        if(!empty($appLink[$k]['children'][$k2]['children'])){
                            foreach($appLink[$k]['children'][$k2]['children'] as $k3=>$v3){
                                $appLink[$k]['children'][$k2]['children'][$k3]['name'] = '--'.json_decode($v3['name'],true)['zh'];
                            }
                        }
                    }
                }
            }
            return view('',compact('data','id','pid','appLink','policyLink','msgLink','ruleLink','imgLink','system_id'));
        }
    }

    public function del_footer(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        $res = Db::connect($this->config)->name('footer_body')->where(['id'=>$id])->delete();
        if($res){
            Db::connect($this->config)->name('footer_body')->where(['pid'=>$id])->delete();
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #常用语管理
    public function comlang_manage(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('website_chatlanguage')->where(['pid'=>0,'company_id'=>0])->order($order)->count();
            $rows = DB::name('website_chatlanguage')
                ->where(['pid'=>0,'company_id'=>0])
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
    
    public function save_comlang(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        if($request->isAjax()){
            $res = '';
            if($id>0){
                $res = Db::name('website_chatlanguage')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name'])
                ]);
            }else{
                $res = Db::name('website_chatlanguage')->insert([
                    'pid'=>$pid,
                    'company_id'=>0,
                    'name'=>trim($dat['name'])
                ]);
            }
            
            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);    
            }
        }else{
            $data = ['name'=>''];
            
            if($id>0){
                $data = Db::name('website_chatlanguage')->where(['id'=>$id])->find();
            }
            return view('',compact('data','id','pid'));
        }
    }
    
    public function del_comlang(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $res = Db::name('website_chatlanguage')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
    
    public function comlang_list(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('website_chatlanguage')->where(['pid'=>$pid,'company_id'=>0])->order($order)->count();
            $rows = DB::name('website_chatlanguage')
                ->where(['pid'=>$pid,'company_id'=>0])
                ->limit($limit)
                ->order($order)
                ->select();

            
            foreach($rows as $k=>&$v){
                
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('pid'));
        }
    }

    #聊天管理
    public function chat_manage(Request $request){
        $dat = input();

        $id = isset($dat['id'])?intval($dat['id']):0;
        $miniprogram = isset($dat['miniprogram'])?intval($dat['miniprogram']):0;
        #小程序进来的，默认登录
        if($miniprogram==1){
            if(!empty($id)){
                $user = Db::name('foll_user')->where(['id'=>$id])->find();
                Session::set("myUser",$user);
            }
        }

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('website_chatlist')->where(['pid'=>0,'company_id'=>0])->order($order)->count();
            $rows = DB::name('website_chatlist')
                ->where(['pid'=>0])
                ->limit($limit)
                ->order($order)
                ->select();

            $_status = ['未读','已读'];
            foreach($rows as $k=>&$v){
                $have_child = Db::name('website_chatlist')->where(['pid'=>$v['id']])->order('id desc')->find();
                if(!empty($have_child)){
                    $v['status_name'] = $_status[$have_child['is_read']];
                }else{
                    $v['status_name'] = $_status[$v['is_read']];
                }
                $v['gogo_id'] = Db::name('website_user')->where(['id'=>$v['uid']])->find()['custom_id'];
                $v['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    #查看聊天
    public function read_chat(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $uid_new = isset($dat['uid_new'])?intval($dat['uid_new']):0;
        $headType = isset($dat['headType'])?intval($dat['headType']):1;

        if($request->isAjax()){ 
            $chatList = Db::name('website_chatlist')->where(['id'=>$id])->find();
            if($dat['pa']==1){
                #插入数据表
                $last_chatinfo = Db::name('website_chatlist')->where(['uid'=>$chatList['uid'],'kefu_id'=>0])->order('id desc')->limit(1)->field('language')->find();

                if(empty($chatList)){
                    #总后台选择用户聊天
                    $id = Db::name('website_chatlist')->where(['id'=>$dat['pid']])->find()['id'];
                    if(empty($id)){
                        $id = Db::name('website_chatlist')->insertGetId([
                            'pid'=>0,
                            'uid'=>$uid_new,
                            'kefu_id'=>session('myUser')['id'],
                            'who_send'=>1,
                            'is_read'=>0,
                            'content_type'=>1,
                            'content'=>json_encode("您好",true),
                            'quote_text'=>'',
                            'createtime'=>time()
                        ]);
                        $chatList = Db::name('website_chatlist')->where(['id'=>$id])->find();
                    }else{
                        $chatList = Db::name('website_chatlist')->where(['id'=>$id])->find();
                    }
                }

                $content = json_encode($dat['content'],true);
                $answer_id = Db::name('website_chatlist')->insertGetId([
                    'pid'=>$dat['pid'],
                    'uid'=>$chatList['uid'],
                    'kefu_id'=>session('myUser')['id'],
                    'who_send'=>$dat['who_send'],
                    'is_read'=>0,
                    'content_type'=>$dat['content_type'],
                    'language'=>'Chinese',
                    'origin_content'=>json_decode($content,true),
                    'content'=>$content,
                    'quote_text'=>isset($dat['quote_text'])?trim($dat['quote_text']):'',
                    'createtime'=>time()
                ]);

                #获取用户最近的语句
                $last_chatinfo = Db::name('website_chatlist')->where(['uid'=>$chatList['uid'],'kefu_id'=>0])->order('id desc')->limit(1)->field('language')->find();

                if($last_chatinfo['language']!='Chinese' && $last_chatinfo['language']!=''){
                    $res = httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=api/getgoods/translate_answer&is_convert_customer=1&answer_id='.$answer_id.'&translate_back_language='.$last_chatinfo['language'], json_encode(['answer_id' => $answer_id,'is_convert_customer'=>1,'translate_back_language'=>$last_chatinfo['language']], true));
                }

                return json(['code'=>0,'msg'=>'已发送']);
            }
            elseif($dat['pa']==2){
                if(empty($chatList)){
                    #总后台选择用户聊天
                    $id = Db::name('website_chatlist')->where(['pid'=>0,'uid'=>$uid_new])->find()['id'];
                    if(empty($id)){
                        $id = Db::name('website_chatlist')->insertGetId([
                            'pid'=>0,
                            'uid'=>$uid_new,
                            'kefu_id'=>session('myUser')['id'],
                            'who_send'=>1,
                            'is_read'=>0,
                            'content_type'=>1,
                            'content'=>json_encode("您好",true),
                            'quote_text'=>'',
                            'createtime'=>time()
                        ]);
                        $chatList = Db::name('website_chatlist')->where(['id'=>$id])->find();
                    }else{
                        $chatList = Db::name('website_chatlist')->where(['id'=>$id])->find();
                    }
                }

                $list = Db::name('website_chatlist')->where(['uid'=>$chatList['uid']])->order('id asc')->field(['id','createtime'])->select();
                if(!empty($list)){
                    $pid = $list[0]['id'];
                }else{
                    $pid = 0;
                }

                $chat_group = [];
                $who_send = 1;
                if(!empty($list)){
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

                        $chat_group[$k]['info'] = Db::name('website_chatlist')->where(['uid' => $chatList['uid']])->whereBetween('createtime', [$starttime, $endtime])->order('createtime asc')->select();
                    }

                    #整理数组
                    $datas['file'] = [];#
                    foreach($chat_group as $k=>$v){
                        foreach($v['info'] as $kk=>$vv){
                            if($vv['kefu_id']>0){
                                $kefu = Db::name('foll_user')->where(['id'=>$vv['kefu_id']])->find();
                                $chat_group[$k]['info'][$kk]['kefu_name'] = mb_substr($kefu['username'], -2);
                            }
                            $chat_group[$k]['info'][$kk]['content'] = json_decode($vv['content'],true);
                            if($vv['language']!='' && $vv['language']!='Chinese'){
                                $chat_group[$k]['info'][$kk]['content'] .= '<br/>（翻译：'.$vv['origin_content'].'）';
                            }
                            if($chat_group[$k]['info'][$kk]['is_withdraw']==0){
                                if(strpos($chat_group[$k]['info'][$kk]['content'], "default_file.png") !== false){
                                    array_push($datas['file'],[$chat_group[$k]['info'][$kk]['content']]);
                                }
                            }

                            $chat_group[$k]['info'][$kk]['createtime'] = date('H:i',$vv['createtime']);
                        }
                    }
//                    dd($chat_group);
                    #全部设置成已看记录
                    Db::name('website_chatlist')->where(['id' => $id, 'who_send' => 2,'is_read'=>0])->update(['is_read' => 1]);
                    Db::name('website_chatlist')->where(['pid' => $id, 'who_send' => 2,'is_read'=>0])->update(['is_read' => 1]);
                }

                return json(['code'=>0,'data'=>$chat_group,'pid'=>$pid,'datas'=>$datas]);
            }
            elseif($dat['pa']==3){
                #撤回消息（查看当前聊天有无被对方看过）
                $nowchat = Db::name('website_chatlist')->where(['id'=>$id])->find();
                if($nowchat['is_read']==0){
                    Db::name('website_chatlist')->where(['id'=>$id])->update(['is_withdraw'=>1]);
                }
                return json(['code'=>0,'msg'=>'撤回成功']);
            }
            elseif($dat['pa']==4){
                #更多详情
                if($dat['type']==1){
                    #会员详情
                    $user = Db::name('website_user')->where(['id'=>intval($dat['uid'])])->find();

                    return json(['code'=>0,'data'=>$user]);
                }
                elseif($dat['type']==2){
                    #链接详情
                    return json(['code'=>0,'data'=>$chatList]);
                }
            }
        }
        else{
            $who_send = 1;
            $users = Db::name('website_user')->select();

            #当前聊天的uid
            $chatList = Db::name('website_chatlist')->where(['id'=>$id])->find();
            if($uid_new==0){
                $uid_new = $chatList['uid'];
            }else{
                if(empty($chatList)){
                    #总后台选择用户聊天
                    $id = Db::name('website_chatlist')->where(['pid'=>0,'uid'=>$uid_new])->find()['id'];
                    if(empty($id)){
                        $id = Db::name('website_chatlist')->insertGetId([
                            'pid'=>0,
                            'uid'=>$uid_new,
                            'kefu_id'=>session('myUser')['id'],
                            'who_send'=>1,
                            'is_read'=>0,
                            'content_type'=>1,
                            'content'=>json_encode("您好",true),
                            'quote_text'=>'',
                            'createtime'=>time()
                        ]);
                        $chatList = Db::name('website_chatlist')->where(['id'=>$id])->find();
                    }
                }
            }

            #聊天记录
            $rows = DB::name('website_chatlist')->where(['pid'=>0])->select();
            $starttime = time()-3600;
            $endtime = time();
            foreach($rows as $k=>$v){
                #查找有无一小时内的聊天
                $inHour = Db::name('website_chatlist')->where(['pid'=>$v['id']])->whereRaw('createtime >= '.$starttime.' and createtime <='.$endtime)->find();
                $rows[$k]['inHour'] = 0;
                if(!empty($inHour)){
                    $rows[$k]['inHour'] = 1;
                    #收到未回复
                    $rows[$k]['noreply'] = 0;
                    $ishave1 = Db::name('website_chatlist')->where(['pid'=>$v['id'],'is_read'=>0,'who_send'=>1])->order('id','desc')->find();
                    if(!empty($ishave1)){
                        $rows[$k]['noreply'] = 1;
                    }else{
                        $ishave1 = Db::name('website_chatlist')->where(['pid'=>$v['id'],'who_send'=>2])->order('id','desc')->find();
                        if(!empty($ishave1)){
                            $rows[$k]['noreply'] = 1;
                        }
                    }

                    #转发未回复
                    $rows[$k]['t_noreply'] = 0;
                    $ishave2 = Db::name('website_chatlist')->where(['pid'=>$v['id'],'is_read'=>0,'who_send'=>1,'is_transmit'=>1])->order('id','desc')->find();
                    if(!empty($ishave2)){
                        $rows[$k]['t_noreply'] = 1;
                    }
                }

                ##未读>已回复
                #未读数量
                $rows[$k]['noread_num'] = Db::name('website_chatlist')->where(['pid'=>$v['id'],'who_send'=>2,'is_read'=>0])->count();
                #已回复数量
                $rows[$k]['read_num'] = Db::name('website_chatlist')->where(['pid'=>$v['id'],'who_send'=>1,'is_read'=>0])->count();

                $rows[$k]['custom_id'] = Db::name('website_user')->where(['id'=>$v['uid']])->find()['custom_id'];
            }
            #聊天（一小时内）
            $inrows = array_filter($rows,function($value){
                return $value['inHour'] !== 0;
            });
            sort($inrows);
            usort($inrows, function($a, $b) {
                if ($a["noread_num"] == $b["noread_num"]) {
//                    return strcmp($a["read_num"], $b["read_num"]);
                    return $b["read_num"] - $a["read_num"];
                }
                return $b["noread_num"] - $a["noread_num"];
            });

            #聊天（超过一小时）
            $notinrows = array_filter($rows,function($value){
                return $value['inHour'] !== 1;
            });
            sort($notinrows);
            usort($notinrows, function($a, $b) {
                if ($a["noread_num"] == $b["noread_num"]) {
//                    return strcmp($a["read_num"], $b["read_num"]);
                    return $b["read_num"] - $a["read_num"];
                }
                return $b["noread_num"] - $a["noread_num"];
            });

            #常用语
            $lang = Db::name('website_chatlanguage')->where(['pid'=>0,'company_id'=>0])->select();
            foreach($lang as $k=>$v){
                // $lang[$k]['title'] = $v['name'];
                $lang[$k]['children'] = Db::name('website_chatlanguage')->where(['pid'=>$v['id'],'company_id'=>0])->select();
                // foreach($lang[$k]['children'] as $k2=>$v2){
                //     $lang[$k]['children'][$k2]['title'] = $v2['name'];
                // }
            }
            // $lang = json_encode($lang,true);
            
            return view('',compact('id','who_send','users','uid_new','inrows','notinrows','headType','lang'));
        }
    }

    #转发聊天
    public function transmit_chat(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $type = isset($dat['type'])?intval($dat['type']):0;#0直接转发，1编辑转发

        if($request->isAjax()){

            $chatList = Db::name('website_chatlist')->where(['id'=>$id])->find();
            if($type==0){
                #直接转发
                if($dat['mtype']==1){
                    #链接商户
                    if(empty($dat['merchant1'])){
                        return json(['code'=>-1,'msg'=>'请选择链接商户']);
                    }else{
                        #插入一条聊天
                        $res = $this->chat_channel(['dat'=>$dat,'chatList'=>$chatList,'type'=>$type]);
                        return json(['code'=>$res['code'],'msg'=>$res['msg']]);
                    }
                }
                elseif($dat['mtype']==2){
                    #其他商户
                    if(empty($dat['merchant2'])){
                        return json(['code'=>-1,'msg'=>'请选择链接商户']);
                    }else{
                        #插入一条聊天
                        $res = $this->chat_channel(['dat'=>$dat,'chatList'=>$chatList,'type'=>$type]);
                        return json(['code'=>$res['code'],'msg'=>$res['msg']]);
                    }
                }
            }
            elseif($type==1){
                #编辑转发
                if($dat['mtype']==1){
                    #链接商户
                    if(empty($dat['merchant1'])){
                        return json(['code'=>-1,'msg'=>'请选择链接商户']);
                    }else{
                        #插入一条聊天
                        $res = $this->chat_channel(['dat'=>$dat,'chatList'=>$chatList,'type'=>$type]);
                        return json(['code'=>$res['code'],'msg'=>$res['msg']]);
                    }
                }
                elseif($dat['mtype']==2){
                    #其他商户
                    if(empty($dat['merchant2'])){
                        return json(['code'=>-1,'msg'=>'请选择链接商户']);
                    }else{
                        #插入一条聊天
                        $res = $this->chat_channel(['dat'=>$dat,'chatList'=>$chatList,'type'=>$type]);
                        return json(['code'=>$res['code'],'msg'=>$res['msg']]);
                    }
                }
            }
        }else{
            $chatList = Db::name('website_chatlist')->where(['id'=>$id])->find();
            $chatList['content'] = json_decode($chatList['content'],true);

            $link_merchant = Db::name('website_user')->where(['id'=>$chatList['merchant_master']])->find();
            $all_merchant = Db::name('website_user')->where(['merch_status'=>2])->select();

            $who_send = 1;

            return view('',compact('id','who_send','link_merchant','all_merchant','type','chatList'));
        }
    }

    public function chat_channel($data){
        $dat = $data['dat'];
        $chatList = $data['chatList'];
        $type = $data['type'];

        $uid = 0;
        if($dat['mtype']==1){
            #链接商户
            $uid = $dat['merchant1'];
        }
        elseif($dat['mtype']==2){
            #其他商户
            $uid = $dat['merchant2'];
        }

        $ishave = Db::name('website_chatlist')->where(['uid'=>$uid,'pid'=>0])->find();

        $content = [];
        if($type==0){
            #直接转发
            $content = [
                'pid'=>empty($ishave)?0:$ishave['id'],
                'uid'=>$uid,
                'kefu_id'=>session('myUser')['id'],
                'who_send'=>1,
                'is_read'=>0,
                'is_transmit'=>1,
                'content_type'=>$chatList['content_type'],
                'content'=>$chatList['content'],
                'origin_page'=>trim($dat['origin_page']),
                'quote_text'=>'',
                'createtime'=>time()
            ];
        }elseif($type==1){
            #编辑转发
            $content = [
                'pid'=>empty($ishave)?0:$ishave['id'],
                'uid'=>$uid,
                'kefu_id'=>session('myUser')['id'],
                'who_send'=>1,
                'is_read'=>0,
                'is_transmit'=>1,
                'content_type'=>$chatList['content_type'],
                'content'=>json_encode($dat['content'],true),
                'origin_page'=>trim($dat['origin_page']),
                'quote_text'=>'',
                'createtime'=>time()
            ];
        }

        if($dat['channel']==1){
            #消息
            $res = Db::name('website_chatlist')->insert($content);
            if($res){
                return ['code'=>0,'msg'=>'转发成功'];
            }else{
                return ['code'=>-1,'msg'=>'转发失败'];
            }
        }
        elseif($dat['channel']==2) {
            #邮箱
            $merchant = Db::name('website_user')->where(['id'=>$uid])->find();
            if(empty($merchant['email'])){
                return ['code'=>-1,'msg'=>'该商户没有邮箱，请选择其他通知方式'];
            }else{
                $res = httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$merchant['email'],'title'=>'消息通知','content'=>'消息回复：'.$dat['content']]);
                return ['code'=>0,'msg'=>'转发成功'];
            }
        }
        elseif($dat['channel']==3) {
            #短信
            $merchant = Db::name('website_user')->where(['id'=>$uid])->find();
            if(empty($merchant['phone'])){
                return ['code'=>-1,'msg'=>'该商户没有手机号，请选择其他通知方式'];
            }else {
                $post_data = [
                    'mobiles' => $merchant['phone'],
                    'content' => '消息回复：'.$dat['content'].' 【GOGO】',
                ];
                $post_data = json_encode($post_data, true);
                $res = httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng', $post_data, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length:' . strlen($post_data),
                    'Cache-Control: no-cache',
                    'Pragma: no-cache'
                ));
                return ['code'=>0,'msg'=>'转发成功'];
            }
        }
    }

    public function chat_log(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        
        if($request->isAjax()){ 
            if($dat['pa']==1){
                $chatList = DB::name('website_chatlist')->where(['id'=>$id])->find();
                $list = Db::name('website_chatlist')->where(['uid'=>$chatList['uid']])->whereRaw('kefu_id=0 or kefu_id='.session('myUser')['id'])->order('id asc')->field(['id','createtime'])->select();
                if(!empty($list)){
                    $pid = $list[0]['id'];
                }else{
                    $pid = 0;
                }
        
                $chat_group = [];
                $who_send = 1;
                if(!empty($list)){
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
        
                        $chat_group[$k]['info'] = Db::name('website_chatlist')->where(['uid' => $chatList['uid']])->whereRaw('kefu_id=0 or kefu_id='.session('myUser')['id'])->whereBetween('createtime', [$starttime, $endtime])->order('createtime asc')->select();
                    }
                    #整理数组
                    foreach($chat_group as $k=>$v){
                        foreach($v['info'] as $kk=>$vv){
                            if($vv['kefu_id']>0){
                                $kefu = Db::name('foll_user')->where(['id'=>$vv['kefu_id']])->find();
                                $chat_group[$k]['info'][$kk]['kefu_name'] = mb_substr($kefu['username'], -2);
                            }
                            $chat_group[$k]['info'][$kk]['content'] = json_decode($vv['content'],true);
        
                            $chat_group[$k]['info'][$kk]['createtime'] = date('H:i',$vv['createtime']);
                        }
                    }
                }
                
                return json(['code'=>0,'data'=>$chat_group,'pid'=>$pid]);
            }
            
        }else{
            $who_send=1;
            return view('',compact('id','who_send'));
        }
    }

    #内页管理
    public function guideinner_manage(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::connect($this->config)->name('guide_frame')->where(['type'=>1])->count();
            $rows = DB::connect($this->config)->name('guide_frame')
                ->where(['type'=>1])
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

    public function save_guideinner(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){

            if($id>0){
                $res = Db::connect($this->config)->name('guide_frame')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'link'=>trim($dat['link']),
                    'content'=>json_encode($dat['content'],true),
                ]);
            }else{
                $res = DB::connect($this->config)->name('guide_frame')->insert([
                    'type'=>1,
                    'name'=>trim($dat['name']),
                    'link'=>trim($dat['link']),
                    'content'=>json_encode($dat['content'],true),
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>'','type'=>1,'link'=>'','content'=>['background'=>'#ffffff','content'=>'#ffffff','fontcolor'=>'#000000']];
            if($id>0){
                $data = Db::connect($this->config)->name('guide_frame')->where(['id'=>$id])->find();
                $data['content'] = json_decode($data['content'],true);
            }

            return view('',compact('data','id'));
        }
    }

    public function del_guideinner(Request $request){
        $dat = input();

        $res = Db::connect($this->config)->name('guide_frame')->where(['id'=>intval($dat['id'])])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #时段管理--start
    public function timeiterval_manage(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::connect($this->config)->name('time_interval')->count();
            $rows = DB::connect($this->config)->name('time_interval')
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
    
    public function save_timeinterval(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){
            if($id>0){
                $res = Db::connect($this->config)->name('time_interval')->where(['id'=>$id])->update([
                    'start'=>trim($dat['start']),
                    'end'=>trim($dat['end']),
                    'type'=>$dat['type'],
                    'fast'=>trim($dat['fast']),
                ]);
            }else{
                $res = DB::connect($this->config)->name('time_interval')->insert([
                    'start'=>trim($dat['start']),
                    'end'=>trim($dat['end']),
                    'type'=>$dat['type'],
                    'fast'=>trim($dat['fast']),
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['start'=>'','end'=>'','fast'=>'','type'=>1];
            
            if($id>0){
                $data = Db::connect($this->config)->name('time_interval')->where(['id'=>intval($id)])->find();
            }
            
            return view('',compact('id','data'));
        }
    }
    
    public function del_timeinterval(Request $request){
        $dat = input();

        $res = Db::connect($this->config)->name('time_interval')->where(['id'=>intval($dat['id'])])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
    #时段管理----end

    #搜索管理--start
    public function column_manage(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::connect($this->config)->name('search_column')->count();
            $rows = DB::connect($this->config)->name('search_column')
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

    public function save_column(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){

            if($id>0){
                $res = Db::connect($this->config)->name('search_column')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'type'=>$dat['type'],
                    'stype'=>$dat['stype'],
                    'content'=>$dat['stype']==2?json_encode($dat['content'],true):'',
                    'field'=>$dat['field']
                ]);
            }else{
                $res = DB::connect($this->config)->name('search_column')->insert([
                    'name'=>trim($dat['name']),
                    'type'=>$dat['type'],
                    'stype'=>$dat['stype'],
                    'content'=>$dat['stype']==2?json_encode($dat['content'],true):'',
                    'field'=>$dat['field']
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>'','type'=>2,'stype'=>0,'content'=>'','field'=>''];
            if($id>0){
                $data = Db::connect($this->config)->name('search_column')->where(['id'=>$id])->find();
                $data['content'] = json_decode($data['content'],true);
            }

            #商品表字段
            $goods_table = Db::connect($this->config)->query("show full fields from goods");
            $field=[];
            foreach($goods_table as $key=>$vo){
                if($vo['Field'] != 'id'){
                    $field[] = [
                        'field' => $vo['Field'],
                        'comment' => $vo['Comment']
                    ];
                }
            }

            $search_fields = Db::connect($this->config)->name('search_fields')->select();

            return view('',compact('data','id','field','search_fields'));
        }
    }

    public function del_column(Request $request){
        $dat = input();

        $res = Db::connect($this->config)->name('search_column')->where(['id'=>intval($dat['id'])])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    public function column_manage2(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::connect($this->config)->name('search_column_two')->count();
            $rows = DB::connect($this->config)->name('search_column_two')
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

    public function save_column2(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){

            if($id>0){
                $res = Db::connect($this->config)->name('search_column_two')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'field'=>$dat['field']
                ]);
            }else{
                $res = DB::connect($this->config)->name('search_column_two')->insert([
                    'name'=>trim($dat['name']),
                    'field'=>$dat['field']
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>'','field'=>''];
            if($id>0){
                $data = Db::connect($this->config)->name('search_column_two')->where(['id'=>$id])->find();
//                $data['content'] = json_decode($data['content'],true);
            }

            #商品表字段
            $goods_table = Db::connect($this->config)->query("show full fields from goods");
            $field=[];
            foreach($goods_table as $key=>$vo){
                if($vo['Field'] != 'id'){
                    $field[] = [
                        'field' => $vo['Field'],
                        'comment' => $vo['Comment']
                    ];
                }
            }

            return view('',compact('data','id','field'));
        }
    }

    public function del_column2(Request $request){
        $dat = input();

        $res = Db::connect($this->config)->name('search_column_two')->where(['id'=>intval($dat['id'])])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    public function highsearch_manage(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::connect($this->config)->name('search_list')->count();
            $rows = DB::connect($this->config)->name('search_list')
                ->limit($limit)
                ->order($order)
                ->select();


            foreach($rows as $k=>&$v){
                $v['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    public function save_highsearch(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){
            if($id>0){
                $res = Db::connect($this->config)->name('search_list')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'type'=>$dat['type'],
                    'guide_id'=>$dat['type']==1?$dat['guide_id']:''
//                    'content'=>$dat['content'],
                ]);
            }else{
                $res = DB::connect($this->config)->name('search_list')->insert([
                    'name'=>trim($dat['name']),
                    'type'=>$dat['type'],
                    'guide_id'=>$dat['type']==1?$dat['guide_id']:'',
//                    'content'=>$dat['content'],
                    'createtime'=>time()
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>'','content'=>'','type'=>0,'guide_id'=>''];
            if($id>0){
                $data = Db::connect($this->config)->name('search_list')->where(['id'=>$id])->find();
//                $data['content'] = json_decode($data['content'],true);
            }

            $guide = Db::name('website_navbar')->where(['system_id'=>3])->select();
            foreach($guide as $k=>$v){
                $guide[$k]['name'] = json_decode($v['name'],true)['zh'];
            }
//          Db::connect($this->config)->name('guide_body')->where(['system_id'=>3])->select();

            $column1 = ['name'=>'商品字段','id'=>'gd','value'=>'gd','children'=>[]];
            $column1['children'] = $column = Db::connect($this->config)->name('search_column')->where(['type'=>1])->select();
            foreach($column1['children'] as $k=>$v){
                $column1['children'][$k]['value'] = $v['id'];
                $column1['children'][$k]['children'] = [];
            }

            $column2 = ['name'=>'自定字段','id'=>'zd','value'=>'zd','children'=>[]];
            $column2['children'] = Db::connect($this->config)->name('search_column')->where(['type'=>2])->select();
            foreach($column2['children'] as $k=>$v){
                $column2['children'][$k]['value'] = $v['id'];
                $column2['children'][$k]['children'] = [];
            }

            $column = array_merge([$column1],[$column2]);
//            dd($column);
            $column = json_encode($column,true);

            return view('',compact('data','id','column','guide'));
        }
    }

    public function del_highsearch(Request $request){
        $dat = input();

        $res = Db::connect($this->config)->name('search_list')->where(['id'=>intval($dat['id'])])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
    #搜索管理--end

    #热搜管理--start
    public function hotsearch_manage(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::connect($this->config)->name('frame_hotsearch')->count();
            $rows = DB::connect($this->config)->name('frame_hotsearch')
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

    public function save_hotsearch(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        if($request->isAjax()){
            if($id>0){
                $res = Db::connect($this->config)->name('frame_hotsearch')->where(['id'=>$id])->update([
                    'displayorder'=>trim($dat['displayorder']),
                    'name'=>trim($dat['name']),
                    'desc'=>trim($dat['desc']),
                    'type'=>$dat['type'],
                    'background'=>$dat['type']==1?$dat['background']:'',
                    'color'=>$dat['type']==2?$dat['color']:'',
                    'keywords'=>trim($dat['keywords']),
                    'starttime'=>trim($dat['starttime']),
                    'endtime'=>trim($dat['endtime']),
                    'go_other'=>$dat['go_other'],
                    'link'=>$dat['go_other']==1?trim($dat['link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'other_pic'=>$dat['go_other']==3?trim($dat['other_pic']):'',
                    'other_msg'=>$dat['go_other']==4?trim($dat['other_msg']):'',
                    'is_show'=>$dat['is_show'],
                ]);
            }else{
                $res = DB::connect($this->config)->name('frame_hotsearch')->insert([
                    'displayorder'=>trim($dat['displayorder']),
                    'name'=>trim($dat['name']),
                    'desc'=>trim($dat['desc']),
                    'type'=>$dat['type'],
                    'background'=>$dat['type']==1?$dat['background']:'',
                    'color'=>$dat['type']==2?'#'.$dat['color']:'',
                    'keywords'=>trim($dat['keywords']),
                    'starttime'=>trim($dat['starttime']),
                    'endtime'=>trim($dat['endtime']),
                    'go_other'=>$dat['go_other'],
                    'link'=>$dat['go_other']==1?trim($dat['link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'other_pic'=>$dat['go_other']==3?trim($dat['other_pic']):'',
                    'other_msg'=>$dat['go_other']==4?trim($dat['other_msg']):'',
                    'is_show'=>$dat['is_show'],
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['displayorder'=>'','name'=>'','desc'=>'','type'=>1,'background'=>'','color'=>'','go_other'=>0,'link'=>'','other_navbar'=>'','other_pic'=>'','other_msg'=>'','keywords'=>'','starttime'=>'','endtime'=>'','is_show'=>1];
            if($id>0){
                $data = Db::connect($this->config)->name('frame_hotsearch')->where(['id'=>$id])->find();
            }

            #应用链接
            $list = Db::connect($this->config)->name('guide_frame')->where(['type'=>1])->select();

            #图文链接
            $pic_list = Db::name('website_image_txt')->select();
            foreach($pic_list as $k=>$v){
                $pic_list[$k]['name'] = json_decode($v['name'],true)['zh'];
            }

            #消息链接
            $msg_list = Db::name('website_message_manage')->select();

            return view('',compact('data','id','list','pic_list','msg_list'));
        }
    }

    public function del_hotsearch(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $res = Db::connect($this->config)->name('frame_hotsearch')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
    #热搜管理--end

    #评价管理--start
    public function hotcomment_manage(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::connect($this->config)->name('frame_hotcomment')->count();
            $rows = DB::connect($this->config)->name('frame_hotcomment')
                ->limit($limit)
                ->order($order)
                ->select();


            foreach($rows as $k=>&$v){
                $v['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }
    #评价管理--end

    #收银台配置--start
    public function cashier_setting(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::connect($this->config)->name('cashier_country')->count();
            $rows = DB::connect($this->config)->name('cashier_country')
                ->limit($limit)
                ->order($order)
                ->select();


            foreach($rows as $k=>&$v){
                $v['country_name'] = Db::name('centralize_diycountry_content')->where(['id'=>$v['country_id']])->find()['param2'];
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    #保存收银台国家
    public function save_cashier_country(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            $ishave = Db::connect($this->config)->name('cashier_country')->where(['country_id'=>$dat['country_id']])->find();
            if(!empty($ishave)){
                return json(['code'=>-1,'msg'=>'当前国家地区已存在']);
            }
            $res = Db::connect($this->config)->name('cashier_country')->insert([
                'country_id'=>$dat['country_id']
            ]);

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $country = Db::name('centralize_diycountry_content')->where(['pid'=>5])->select();
            $data = ['country_id'=>0];

            if(empty($id)){
                $data = Db::connect($this->config)->name('cashier_country')->where(['id'=>$id])->find();
            }

            return view('',compact('data','country','id'));
        }
    }

    #删除收银台国家
    public function del_cashier_country(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $res = Db::connect($this->config)->name('cashier_country')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #国家类别列表
    public function category_manage(Request $request){
        $dat = input();
        $country_id = isset($dat['country_id'])?intval($dat['country_id']):0;

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::connect($this->config)->name('cashier_category')->where(['country_id'=>$country_id])->count();
            $rows = DB::connect($this->config)->name('cashier_category')
                ->where(['country_id'=>$country_id])
                ->limit($limit)
                ->order($order)
                ->select();


            foreach($rows as $k=>&$v){

            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('country_id'));
        }
    }

    #保存国家类别
    public function save_cashier_category(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $country_id = isset($dat['country_id'])?intval($dat['country_id']):0;

        if($request->isAjax()){
            if(empty($id)){
                $res = Db::connect($this->config)->name('cashier_category')->insert([
                    'country_id'=>$country_id,
                    'name'=>trim($dat['name']),
                    'type'=>$dat['type']
                ]);
            }
            else{
                $res = Db::connect($this->config)->name('cashier_category')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'type'=>$dat['type']
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = ['name'=>'','type'=>1];

            if(!empty($id)){
                $data = Db::connect($this->config)->name('cashier_category')->where(['id'=>$id])->find();
            }

            return view('',compact('data','id','country_id'));
        }
    }

    #删除国家类别
    public function del_cashier_category(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $res = Db::connect($this->config)->name('cashier_category')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    public function settlement_manage(Request $request){
        $dat = input();
        $type = isset($dat['type'])?intval($dat['type']):0;
        $country_id = isset($dat['country_id'])?intval($dat['country_id']):0;

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::connect($this->config)->name('settlement')->where(['type_id'=>$type])->count();
            $rows = DB::connect($this->config)->name('settlement')
                ->where(['type_id'=>$type])
                ->limit($limit)
                ->order($order)
                ->select();


            foreach($rows as $k=>&$v){

            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('type','country_id'));
        }
    }

    public function save_settlement(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $type = isset($dat['type'])?$dat['type']:0;
        if($request->isAjax()){
            $service_charge = [];
            if(isset($dat['start_money'])){
                foreach($dat['start_money'] as $k=>$v){
                    array_push($service_charge,['start_money'=>$v,'end_type'=>$dat['end_type'][$k],'end_money'=>$dat['end_money'][$k],'charge_type'=>$dat['charge_type'][$k],'charge_num'=>$dat['charge_num'][$k]]);
                }
            }

            if($id>0){
                $res = Db::connect($this->config)->name('settlement')->where(['id'=>$id])->update([
                    'pay_id'=>$dat['pay_id'],
                    'name'=>trim($dat['name']),
                    'remark'=>trim($dat['remark']),
                    'icon'=>$dat['icon'][0],
                    'currency'=>intval($dat['currency']),
                    'rate_remark'=>trim($dat['rate_remark']),
                    'service_charge'=>json_encode($service_charge,true)
                ]);
            }else{
                $res = DB::connect($this->config)->name('settlement')->insert([
                    'type_id'=>$type,
                    'pay_id'=>$dat['pay_id'],
                    'name'=>trim($dat['name']),
                    'remark'=>trim($dat['remark']),
                    'icon'=>$dat['icon'][0],
                    'currency'=>intval($dat['currency']),
                    'rate_remark'=>trim($dat['rate_remark']),
                    'service_charge'=>json_encode($service_charge,true)
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>'','remark'=>'','type_id'=>1,'icon'=>'','currency'=>0,'rate_remark'=>'','service_charge'=>[['start_money'=>'','end_type'=>1,'end_money'=>'','charge_type'=>1,'charge_num'=>'']],'pay_id'=>0];
            if($id>0){
                $data = Db::connect($this->config)->name('settlement')->where(['id'=>$id])->find();
                $data['service_charge'] = json_decode($data['service_charge'],true);
            }

            $currency = Db::name('centralize_currency')->select();
            $pay_method = [['id'=>1,'name'=>'通莞-微信支付'],['id'=>2,'name'=>'通莞-支付宝支付'],['id'=>3,'name'=>'Paypal支付']];

            return view('',compact('data','id','type','currency','pay_method'));
        }
    }

    public function del_settlement(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $res = Db::connect($this->config)->name('settlement')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
    #收银台配置--end

    #装箱单
    public function order_temp_manage(Request $request){
        $dat = input();

        if(isset($dat['pa'])){
            if($dat['pa']==1){
                #获取所属订单信息
                $order_type = intval($dat['order_type']);
                $template_id = intval($dat['template_id']);

                $return_data = [];
                if($order_type==1){
                    #商城订单
                    $order = Db::name('website_order_list')->where(['ordersn'=>trim($dat['ordersn'])])->find();
                    if(empty($order)){
                        return json(['code'=>0,'msg'=>'获取失败，系统无此订单','data'=>[]]);
                    }
                    $order['content'] = json_decode($order['content'],true);
                    foreach($order['content']['goods_info'] as $k=>$v){
                        #查询商品是哪个商家
                        $goods_info = Db::connect($this->config)->name('goods')->where(['goods_id'=>$v['good_id']])->find();

                        $return_data['send_company'] = '佛山市钜铭商务资讯服务有限公司';
                        $return_data['sender'] = 'Gogo';
                        $return_data['send_address'] = '佛山市南海区桂城创客邦D611';
                        $return_data['send_number'] = '86329911';
                        $return_data['send_date'] = '';
                        $return_data['consignee'] = '';
                        $return_data['consignee_country'] = '';
                        $return_data['marking_code'] = '';
                        $return_data['from'] = '';
                        $return_data['transportNumber'] = '';

                        if($goods_info['shop_id']>0){
                            #有企业的商品
                            $company = Db::name('website_user_company')->where(['id'=>$goods_info['shop_id']])->find();
                            $return_data['send_company'] = $company['company'];
                            $return_data['sender'] = $company['realname'];
                            $return_data['send_address'] = '';
                            $return_data['send_number'] = $company['mobile'];
                        }

                        foreach($v['sku_info'] as $k2=>$v2){
                            $sku_info = Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$v2['sku_id']])->find();

                            #不同的规格是不同的商品
                            $return_data['goods'][$k2]['name'] = $goods_info['goods_name'];
                            $return_data['goods'][$k2]['picture'] = $goods_info['goods_image'];
                            $return_data['goods'][$k2]['brand'] = '';
                            if($goods_info['brand_type']==1){
                                #有牌
                                if(!empty($goods_info['brand_name'])){
                                    $return_data['goods'][$k2]['brand'] = $goods_info['brand_name'];
                                }

                                if(!empty($goods_info['brand_id'])){
                                    $return_data['goods'][$k2]['brand'] = Db::name('centralize_diycountry_content')->where(['id'=>$goods_info['brand_id']])->find()['param1'];
                                }
                            }
                            $return_data['goods'][$k2]['price'] = 0;
                            $sku_info['sku_prices'] = json_decode($sku_info['sku_prices'],true);
                            #币种
                            $currency = $sku_info['sku_prices']['currency'][0];
                            $return_data['goods'][$k2]['currency'] = Db::name('centralize_currency')->where(['id'=>$currency])->find()['currency_symbol_standard'];
                            #单价
                            foreach($sku_info['sku_prices']['select_end'] as $k3=>$v3){
                                if($v3==1){
                                    #数值
                                    if($sku_info['sku_prices']['start_num'][$k3] <= $v2['goods_num'] && $sku_info['sku_prices']['end_num'][$k3] >= $v2['goods_num']){
                                        $return_data['goods'][$k2]['price'] = $sku_info['sku_prices']['price'][$k3];
                                    }
                                }
                                elseif($v3==2){
                                    #以上
                                    if($sku_info['sku_prices']['start_num'][$k3] <= $v2['goods_num']){
                                        $return_data['goods'][$k2]['price'] = $sku_info['sku_prices']['price'][$k3];
                                    }
                                }
                            }
                            $return_data['goods'][$k2]['package'] = $v2['goods_num'];
                            $return_data['goods'][$k2]['option_name'] = $sku_info['spec_names'];
                        }
                    }
                }
                elseif($order_type==2){
                    #集运订单(centralize_parcel_order、centralize_parcel_order_goods、centralize_parcel_order_package、
                    $order = Db::name('centralize_parcel_order')->where(['ordersn'=>trim($dat['ordersn'])])->find();
                    if(empty($order)){
                        return json(['code'=>0,'msg'=>'获取失败，系统无此订单','data'=>[]]);
                    }

                    $return_data['send_company'] = '佛山市钜铭商务资讯服务有限公司';
                    $return_data['sender'] = 'Gogo';
                    $return_data['send_address'] = '佛山市南海区桂城创客邦D611';
                    $return_data['send_number'] = '86329911';
                    $return_data['send_date'] = '';
                    $return_data['consignee'] = '';
                    $return_data['consignee_country'] = Db::name('centralize_diycountry_content')->where(['id'=>$order['country']])->find()['param2'];
                    $return_data['marking_code'] = '';
                    $return_data['from'] = '';
                    $return_data['transportNumber'] = '';

                    $order_goods = Db::name('centralize_parcel_order_goods')->where(['orderid'=>$order['id']])->select();
                    foreach($order_goods as $k=>$v){
                        $order_package = Db::name('centralize_parcel_order_package')->where(['id'=>$v['package_id']])->find();
                        if(empty($return_data['transportNumber'])){
                            $return_data['transportNumber'] = $order_package['express_no'];
                        }

                        $return_data['goods'][$k]['name'] = $v['good_desc'];
                        $return_data['goods'][$k]['picture'] = '';
                        $return_data['goods'][$k]['brand'] = $v['brand_name'];
                        #币种
                        $currency = $v['good_currency'];
                        $return_data['goods'][$k]['currency'] = Db::name('centralize_currency')->where(['id'=>$currency])->find()['currency_symbol_standard'];
                        $return_data['goods'][$k]['price'] = $v['good_price'];
                        $return_data['goods'][$k]['package'] = $v['good_num'];
                        $return_data['goods'][$k]['total_price'] = round($v['good_price'] * $v['good_num'],2);
                        $return_data['goods'][$k]['option_name'] = $v['good_desc'];
                        $return_data['goods'][$k]['grosswt'] = $order_package['grosswt'];
                        $return_data['goods'][$k]['size'] = $order_package['volumn'];
                    }
                }

                return json(['code'=>0,'msg'=>'获取成功','data'=>$return_data]);
            }
        }
        else{

            $data = Db::name('order_table_temp')->select();
            foreach($data as $k=>$v){
                if($v['type']==1){
                    $data[$k]['name'] = '装箱单【'.$v['name'].'】';
                }
                elseif($v['type']==2){
                    $data[$k]['name'] = '商业发票【'.$v['name'].'】';
                }
                elseif($v['type']==3){
                    $data[$k]['name'] = '装箱单&商业发票【'.$v['name'].'】';
                }
            }

            return view('',compact('data'));
        }
    }

    #订单类型管理
    public function order_type_manage(Request $request){
        $dat = input();
        $level = isset($dat['level'])?$dat['level']:1;
        $pid = isset($dat['pid'])?$dat['pid']:0;

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $where = ['pid'=>$pid];

            $count = Db::name('website_order_type_list')->where($where)->count();
            $rows = DB::name('website_order_type_list')
                ->where($where)
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>&$v){

            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('level','pid'));
        }
    }

    #添加订单类型
    public function save_order_type(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $pid = isset($dat['pid'])?$dat['pid']:0;

        if($request->isAjax()){
            if($id>0){
                $res = Db::name('website_order_type_list')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'code'=>trim($dat['code']),
                ]);
            }else{
                $res = DB::name('website_order_type_list')->insert([
                    'pid'=>$pid,
                    'name'=>trim($dat['name']),
                    'code'=>trim($dat['code']),
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>'','code'=>''];
            if($id>0){
                $data = Db::name('website_order_type_list')->where(['id'=>$id])->find();
            }

            return view('',compact('data','id','pid'));
        }
    }

    #删除订单类型
    public function del_order_type(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        $res = Db::name('website_order_type_list')->where(['id'=>$id])->delete();
        if($res){
            #删除下级...
            Db::name('website_order_type_list')->where(['pid'=>$id])->delete();
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #会员服务管理
    public function member_service_manage(Request $request){
        $dat = input();
        $system_id = $dat['system_id'];
        $type = $dat['type'];

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('member_services')->where(['type'=>$type])->count();
            $rows = DB::name('member_services')
                ->where(['type'=>$type])
                ->limit($limit)
                ->order($order)
                ->select();


            foreach($rows as $k=>&$v){

            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('system_id','type'));
        }
    }

    #保存会员服务
    public function save_member_service(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $type = $dat['type'];

        if($request->isAjax()){

            if($id>0){
                $res = Db::name('member_services')->where(['id'=>$id])->update([
                    'type'=>$type,
                    'name'=>trim($dat['name']),
                    'desc'=>trim($dat['desc']),
                ]);
            }else{
                $res = DB::name('member_services')->insert([
                    'type'=>$type,
                    'name'=>trim($dat['name']),
                    'desc'=>trim($dat['desc']),
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>'','desc'=>''];
            if($id>0){
                $data = Db::name('member_services')->where(['id'=>$id])->find();
            }

            return view('',compact('data','id','type'));
        }
    }

    #删除会员服务
    public function del_member_service(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $res = Db::name('member_services')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #会员等级管理
    public function member_level_manage(Request $request){
        $dat = input();
        $system_id = $dat['system_id'];
        $type = $dat['type'];
        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('member_level')->where(['type'=>$type])->count();
            $rows = DB::name('member_level')
                ->where(['type'=>$type])
                ->limit($limit)
                ->order($order)
                ->select();


            foreach($rows as $k=>&$v){

            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('system_id','type'));
        }
    }

    #保存会员等级
    public function save_member_level(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $type = $dat['type'];

        if($request->isAjax()){
            $res = $id;

            #对会员价格进行重新排序，以免发生错误
            $dat['mname'] = array_values($dat['mname']);
            $dat['mcurrency'] = array_values($dat['mcurrency']);
            $dat['mprice'] = array_values($dat['mprice']);
            $dat['mday'] = array_values($dat['mday']);

            $service_desc = [];
            foreach($dat['mname'] as $k=>$v){
                array_push($service_desc,['mname'=>trim($v),'mcurrency'=>$dat['mcurrency'][$k],'mprice'=>trim($dat['mprice'][$k]),'mday'=>trim($dat['mday'][$k])]);
            }

            if($id>0){
                Db::name('member_level')->where(['id'=>$id])->update([
                    'type'=>$type,
                    'name'=>trim($dat['name']),
                    'color'=>$dat['color'],
                    'thumb'=>isset($dat['thumb'])?'//shop.gogo198.cn/'.trim($dat['thumb'][0]):'',
                    'service_ids'=>trim($dat['service_ids']),
                    'service_desc'=>json_encode($service_desc,true)
                ]);
            }else{
                $res = DB::name('member_level')->insertGetId([
                    'type'=>$type,
                    'name'=>trim($dat['name']),
                    'color'=>$dat['color'],
                    'thumb'=>isset($dat['thumb'])?'//shop.gogo198.cn/'.trim($dat['thumb'][0]):'',
                    'service_ids'=>trim($dat['service_ids']),
                    'service_desc'=>json_encode($service_desc,true)
                ]);
            }

            $ishavegoods = Db::connect($this->config)->name('goods')->where(['level_id'=>$res])->find();
            if(empty($ishavegoods)){
                $time = time();
                foreach($service_desc as $kk=>$vv){
                    $goods_id = 0;
                    if($kk==0){
                        $goods_id = Db::connect($this->config)->name('goods')->insertGetId([
                            'goods_name'=>trim($dat['name']),
                            'level_id'=>$res,
                            'have_specs'=>1,
                            'goods_status'=>1,
                            'pc_desc'=>'<p><h1>'.trim($dat['name']).'</h1></p>',
                            'goods_subname'=>trim($dat['name']),
                            'goods_currency'=>intval($vv['mcurrency']),
                            'goods_price'=>trim($vv['mprice']),
                            'market_price'=>trim($vv['mprice']),
                            'cost_price'=>trim($vv['mprice']),
                            'goods_number'=>999999,
                            'goods_image'=>isset($dat['thumb'])?'//shop.gogo198.cn/'.trim($dat['thumb'][0]):'',
                            'goods_audit'=>1,
                            'add_time'=>$time,
                            'created_at'=>date('Y-m-d H:i:s',$time)
                        ]);
                    }

                    #等级天数规格
                    $spec_info = Db::connect($this->config)->name('attr_value')->where(['attr_vname'=>trim($vv['mname'])])->find();
                    $spec_vids = 0;
                    if(!empty($spec_info)){
                        $spec_vids = $spec_info['attr_vid'];
                    }else{
                        $spec_vids = Db::connect($this->config)->name('attr_value')->insertGetId([
                            'attr_id'=>402,
                            'attr_vname'=>trim($vv['mname']),
                            'attr_vsort'=>$kk+1,
                            'created_at'=>date('Y-m-d H:i:s',$time),
                            'updated_at'=>date('Y-m-d H:i:s',$time)
                        ]);
                    }

                    $sku_id = Db::connect($this->config)->name('goods_sku')->insertGetId([
                        'goods_id'=>$goods_id,
                        'spec_names'=>trim($dat['name']).' '.trim($vv['mname']),
                        'sku_images'=>isset($dat['thumb'])?'//shop.gogo198.cn/'.trim($dat['thumb'][0]):'',
                        'spec_ids'=>402,#attribute
                        'spec_vids'=>$spec_vids,#attr_value
                        'sku_specs'=>$spec_vids,
                        'sku_prices'=>json_encode(['goods_number'=>999999,'disabled_num'=>0,'start_num'=>[1],'unit'=>['001'],'select_end'=>[2],'end_num'=>[''],'currency'=>[$vv['mcurrency']],'price'=>[trim($vv['mprice'])]],true),
                        'is_enable'=>1,
                        'checked'=>1,
                        'created_at'=>date('Y-m-d H:i:s',$time),
                        'updated_at'=>date('Y-m-d H:i:s',$time),
                        'goods_price'=>trim($vv['mprice']),
                        'market_price'=>trim($vv['mprice']),
                        'goods_number'=>999999,
                    ]);

                    if($kk==0){
                        Db::connect($this->config)->name('goods')->where(['goods_id'=>$goods_id])->update(['sku_id'=>$sku_id]);
                    }

                    #商品规格表（goods_spec）
                    Db::connect($this->config)->name('goods_spec')->insert([
                        'goods_id'=>$goods_id,
                        'attr_id'=>402,
                        'attr_vid'=>$spec_vids,
                        'attr_value'=>trim($vv['mname']),
                        'is_checked'=>1,
                        'created_at'=>date('Y-m-d H:i:s',$time),
                        'updated_at'=>date('Y-m-d H:i:s',$time),
                    ]);

                    #商品图片
                    Db::connect($this->config)->name('goods_image')->insert([
                        'goods_id'=>$goods_id,
                        'path'=>isset($dat['thumb'])?'//shop.gogo198.cn/'.trim($dat['thumb'][0]):'',
                        'is_default'=>1,
                        'sort'=>1,
                        'created_at'=>date('Y-m-d H:i:s',$time)
                    ]);
                }
            }
            else{
                $time = time();
                foreach($service_desc as $kk=>$vv) {
                    if($kk==0){
                        Db::connect($this->config)->name('goods')->where(['goods_id' => $ishavegoods['goods_id']])->update([
                            'goods_name' => trim($dat['name']),
                            'level_id' => $res,
                            'have_specs' => 1,
                            'goods_status' => 1,
                            'pc_desc' => '<p><h1>' . trim($dat['name']) . '</h1></p>',
                            'goods_subname' => trim($dat['name']),
                            'goods_currency' => intval($vv['mcurrency']),
                            'goods_price' => trim($vv['mprice']),
                            'market_price' => trim($vv['mprice']),
                            'cost_price' => trim($vv['mprice']),
                            'goods_number' => 999999,
                            'goods_image' => isset($dat['thumb'])?'//shop.gogo198.cn/'.trim($dat['thumb'][0]):'',
                            'updated_at' => date('Y-m-d H:i:s', $time)
                        ]);
                    }

                    #等级天数规格
                    $spec_info = Db::connect($this->config)->name('attr_value')->where(['attr_vname'=>trim($vv['mname'])])->find();
                    $spec_vids = 0;
                    if(!empty($spec_info)){
                        $spec_vids = $spec_info['attr_vid'];
                    }else{
                        $spec_vids = Db::connect($this->config)->name('attr_value')->insertGetId([
                            'attr_id'=>402,
                            'attr_vname'=>trim($vv['mname']),
                            'attr_vsort'=>$kk+1,
                            'created_at'=>date('Y-m-d H:i:s',$time),
                            'updated_at'=>date('Y-m-d H:i:s',$time)
                        ]);
                    }

                    $ishave_sku = Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$ishavegoods['goods_id'],'spec_names'=>$dat['name'].' '.trim($vv['mname'])])->find();
                    if(empty($ishave_sku)){
                        Db::connect($this->config)->name('goods_sku')->insert([
                            'goods_id'=>$ishavegoods['goods_id'],
                            'spec_names'=>trim($dat['name']).' '.trim($vv['mname']),
                            'sku_images'=>isset($dat['thumb'])?'//shop.gogo198.cn/'.trim($dat['thumb'][0]):'',
                            'spec_ids'=>402,#attribute
                            'spec_vids'=>$spec_vids,#attr_value
                            'sku_specs'=>$spec_vids,
                            'sku_prices'=>json_encode(['goods_number'=>999999,'disabled_num'=>0,'start_num'=>[1],'unit'=>['001'],'select_end'=>[2],'end_num'=>[''],'currency'=>[$vv['mcurrency']],'price'=>[trim($vv['mprice'])]],true),
                            'is_enable'=>1,
                            'checked'=>1,
                            'created_at'=>date('Y-m-d H:i:s',$time),
                            'updated_at'=>date('Y-m-d H:i:s',$time),
                            'goods_price'=>trim($vv['mprice']),
                            'market_price'=>trim($vv['mprice']),
                            'goods_number'=>999999,
                        ]);
                    }
                    else{
                        Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$ishavegoods['goods_id'],'spec_names'=>$dat['name'].' '.trim($vv['mname'])])->update([
//                        'spec_names'=>trim($dat['name']).' '.trim($vv['mname']),
                            'sku_images'=>isset($dat['thumb'])?'//shop.gogo198.cn/'.trim($dat['thumb'][0]):'',
                            'sku_prices'=>json_encode(['goods_number'=>999999,'disabled_num'=>0,'start_num'=>[1],'unit'=>['001'],'select_end'=>[2],'end_num'=>[''],'currency'=>[$vv['mcurrency']],'price'=>[trim($vv['mprice'])]],true),
                            'goods_price'=>trim($vv['mprice']),
                            'market_price'=>trim($vv['mprice']),
                            'goods_number'=>999999,
                        ]);
                    }


                    #商品规格表（goods_spec）
                    $ishave = Db::connect($this->config)->name('goods_spec')->where(['goods_id'=>$ishavegoods['goods_id'],'attr_value'=>$vv['mname']])->find();
                    if(empty($ishave)){
                        Db::connect($this->config)->name('goods_spec')->insert([
                            'goods_id'=>$ishavegoods['goods_id'],
                            'attr_id'=>402,
                            'attr_vid'=>$spec_vids,
                            'attr_value'=>trim($vv['mname']),
                            'is_checked'=>1,
                            'created_at'=>date('Y-m-d H:i:s',$time),
                            'updated_at'=>date('Y-m-d H:i:s',$time),
                        ]);
                    }


                    #商品图片
                    $ishaveimg = Db::connect($this->config)->name('goods_image')->where(['goods_id'=>$ishavegoods['goods_id']])->find();
                    if(empty($ishaveimg)){
                        Db::connect($this->config)->name('goods_image')->insert([
                            'goods_id'=>$ishavegoods['goods_id'],
                            'path'=>isset($dat['thumb'])?'//shop.gogo198.cn/'.trim($dat['thumb'][0]):'',
                            'is_default'=>1,
                            'sort'=>1,
                            'created_at'=>date('Y-m-d H:i:s',$time),
                            'updated_at'=>date('Y-m-d H:i:s',$time)
                        ]);
                    }
                    else{
                        Db::connect($this->config)->name('goods_image')->where(['goods_id'=>$ishavegoods['goods_id']])->update([
                            'path'=>isset($dat['thumb'])?'//shop.gogo198.cn/'.trim($dat['thumb'][0]):'',
                            'updated_at'=>date('Y-m-d H:i:s',$time)
                        ]);
                    }
                }
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>'','color'=>'','thumb'=>'','service_ids'=>'','service_desc'=>[]];
            if($id>0){
                $data = Db::name('member_level')->where(['id'=>$id])->find();
                $data['service_desc'] = json_decode($data['service_desc'],true);
            }

            $currency = Db::name('centralize_currency')->select();
            $services = Db::name('member_services')->where(['type'=>$type])->select();
            foreach($services as $k=>$v){
                $services[$k]['value'] = $v['id'];
                $services[$k]['children'] = [];
            }
            $services = json_encode($services,true);

            return view('',compact('data','id','currency','services','type'));
        }
    }

    #删除会员等级
    public function del_member_level(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $res = Db::name('member_level')->where(['id'=>$id])->delete();
        if($res){
            $goods = Db::connect($this->config)->name('goods')->where(['level_id'=>$id])->find();
            Db::connect($this->config)->name('goods')->where(['goods_id'=>$goods['goods_id']])->delete();
            Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$goods['goods_id']])->delete();
            Db::connect($this->config)->name('goods_spec')->where(['goods_id'=>$goods['goods_id']])->delete();

            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #删除会员等级指定的商品
    public function del_member_level_goods(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        #查找商品
        $goods = Db::connect($this->config)->name('goods')->where(['level_id'=>$id])->find();
        #删除规格1
        Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$goods['goods_id'],'spec_names'=>trim($dat['mname'])])->delete();
        #删除规格2
        Db::connect($this->config)->name('goods_spec')->where(['goods_id'=>$goods['goods_id']])->delete();

        return json(['code'=>0,'msg'=>'删除成功']);
    }


    #网站管理 END=========================================
}