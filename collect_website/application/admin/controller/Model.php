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
use \Exception;

//extends Auth
class Model
{
    public $config = [
        //数据库类型
        'type' => 'mysql',
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
        'prefix' => '',
    ];

    #模型部署=================================================start
    public function model_manage(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('train_model_list')->count();
            $rows = DB::name('train_model_list')
                ->limit($limit)
                ->order($order)
                ->select();


            foreach($rows as $k=>&$v){
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }

            #测试同步会员信息
//            sync_member(1);

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    public function save_model(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isAjax()){
            if($id>0){
                Db::name('train_model_list')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                ]);
            }else{
                $res = DB::name('train_model_list')->insertGetId([
                    'name'=>trim($dat['name']),
                    'createtime'=>time()
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>''];
            if($id>0){
                $data = Db::name('train_model_list')->where(['id'=>$id])->find();
            }

            return view('',compact('data','id'));
        }
    }

    public function del_model(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($id>0){
            Db::name('train_model_list')->where(['id'=>$id])->delete();
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
    #模型部署==================================================end

    #模型训练=================================================start
    #内部参数微调
    public function internal_parameters(Request $request){

        if ($request->isAJAX()) {
            $list = [
                #数据配置
                ['id'=>1,'pid'=>0,'setting'=>0,'url'=>'', 'name'=>'微调训练'],
                ['id'=>2,'pid'=>1,'setting'=>0,'url'=>'', 'name'=>'数据配置'],
                ['id'=>3,'pid'=>1,'setting'=>0,'url'=>'', 'name'=>'数据训练'],
                ['id'=>4,'pid'=>2,'setting'=>0,'url'=>'', 'name'=>'明确标准'],
                ['id'=>5,'pid'=>4,'setting'=>1,'url'=>'/collect_website/public/?s=admin/model/qa_category', 'name'=>'标准问答'],
                ['id'=>6,'pid'=>2,'setting'=>0,'url'=>'', 'name'=>'历史对话'],
                ['id'=>7,'pid'=>6,'setting'=>1,'url'=>'/collect_website/public/?s=admin/model/qa_history_date', 'name'=>'风险校准'],
                ['id'=>8,'pid'=>2,'setting'=>0,'url'=>'', 'name'=>'增量更新'],
                ['id'=>9,'pid'=>8,'setting'=>0,'url'=>'', 'name'=>'增量数据集'],
                ['id'=>10,'pid'=>9,'setting'=>1,'url'=>'/collect_website/public/?s=admin/model/goods_data', 'name'=>'商品数据'],
                ['id'=>11,'pid'=>9,'setting'=>1,'url'=>'/collect_website/public/?s=admin/model/order_data', 'name'=>'订单数据'],
                ['id'=>12,'pid'=>9,'setting'=>1,'url'=>'/collect_website/public/?s=admin/model/like_data', 'name'=>'偏好数据'],
                ['id'=>13,'pid'=>9,'setting'=>1,'url'=>'/collect_website/public/?s=admin/model/logistics_data', 'name'=>'物流数据'],

                ['id'=>14,'pid'=>8,'setting'=>1,'url'=>'/collect_website/public/?s=admin/model/qa_category', 'name'=>'增量问答对'],
                ['id'=>33,'pid'=>8,'setting'=>1,'url'=>'/collect_website/public/?s=admin/model/keywords_manage', 'name'=>'关键词库管理'],

                ['id'=>28,'pid'=>8,'setting'=>0,'url'=>'', 'name'=>'高频动态数据'],
                ['id'=>29,'pid'=>28,'setting'=>1,'url'=>'/collect_website/public/?s=admin/model/sync_info_to_local', 'name'=>'同步“订单/商品/线路”数据至“本地/线上”'],#mysql
                ['id'=>30,'pid'=>28,'setting'=>1,'url'=>'/collect_website/public/?s=admin/model/sync_file_to_local', 'name'=>'同步“文档”数据至“本地/线上”'],#Milvus
                ['id'=>31,'pid'=>28,'setting'=>1,'url'=>'/collect_website/public/?s=admin/model/cache_goods_data', 'name'=>'同步“热品”至“本地/线上”'],#Redis
                ['id'=>32,'pid'=>28,'setting'=>1,'url'=>'/collect_website/public/?s=admin/model/cache_user_data', 'name'=>'同步“用户偏好”至“本地/线上”'],#Redis

                ['id'=>15,'pid'=>2,'setting'=>0,'url'=>'', 'name'=>'商品知识'],
                ['id'=>16,'pid'=>15,'setting'=>1,'url'=>'/collect_website/public/?s=admin/model/goods_words', 'name'=>'统一描述'],
                ['id'=>34,'pid'=>2,'setting'=>0,'url'=>'', 'name'=>'模型总开关'],
                ['id'=>35,'pid'=>34,'setting'=>1,'url'=>'/collect_website/public/?s=admin/model/switch_type', 'name'=>'总开关类型管理'],
                ['id'=>36,'pid'=>34,'setting'=>1,'url'=>'/collect_website/public/?s=admin/model/model_switch', 'name'=>'总开关服务管理'],
                #数据预处理
//                ['id'=>12,'pid'=>3,'setting'=>0,'url'=>'', 'name'=>'数据准备'],
                ['id'=>17,'pid'=>3,'setting'=>1,'url'=>'/collect_website/public/?s=admin/model/collect_data', 'name'=>'数据条收集'],
                ['id'=>18,'pid'=>3,'setting'=>1,'url'=>'/collect_website/public/?s=admin/model/collect_data_package', 'name'=>'数据包收集'],
//                ['id'=>13,'pid'=>3,'setting'=>1,'url'=>'/collect_website/public/?s=admin/model/save_train&type=select_model', 'name'=>'选择模型'],
//                ['id'=>14,'pid'=>3,'setting'=>0,'url'=>'', 'name'=>'数据预处理'],
//                ['id'=>15,'pid'=>3,'setting'=>0,'url'=>'', 'name'=>'设置训练参数'],
//                ['id'=>16,'pid'=>3,'setting'=>0,'url'=>'', 'name'=>'微调训练'],
                ['id'=>19,'pid'=>3,'setting'=>0,'url'=>'', 'name'=>'模型评估'],
                ['id'=>20,'pid'=>3,'setting'=>0,'url'=>'', 'name'=>'调整优化'],
                ['id'=>21,'pid'=>3,'setting'=>0,'url'=>'', 'name'=>'模型保存与部署'],
                ['id'=>22,'pid'=>0,'setting'=>0,'url'=>'', 'name'=>'强化训练'],
                ['id'=>23,'pid'=>22,'setting'=>1,'url'=>'', 'name'=>'奖励机制'],
//                ['id'=>23,'pid'=>12,'setting'=>1,'url'=>'', 'name'=>'数据清洗'],
//                ['id'=>24,'pid'=>12,'setting'=>1,'url'=>'', 'name'=>'数据标注'],
//                ['id'=>25,'pid'=>14,'setting'=>1,'url'=>'', 'name'=>'分词'],
//                ['id'=>26,'pid'=>14,'setting'=>1,'url'=>'', 'name'=>'截断和填充'],
//                ['id'=>27,'pid'=>14,'setting'=>1,'url'=>'', 'name'=>'构建数据集'],
//                ['id'=>28,'pid'=>15,'setting'=>1, 'url'=>'/collect_website/public/?s=admin/model/save_train&type=optimizer','name'=>'选择优化器'],
//                ['id'=>29,'pid'=>15,'setting'=>1,'url'=>'/collect_website/public/?s=admin/model/save_train&type=learning_rate', 'name'=>'确定学习率'],
//                ['id'=>30,'pid'=>15,'setting'=>1,'url'=>'/collect_website/public/?s=admin/model/save_train&type=training_rounds', 'name'=>'设置训练轮数'],
//                ['id'=>31,'pid'=>15,'setting'=>1,'url'=>'/collect_website/public/?s=admin/model/save_train&type=batch_size', 'name'=>'确定批量大小'],
                ['id'=>24,'pid'=>19,'setting'=>1, 'url'=>'','name'=>'验证集评估'],
                ['id'=>25,'pid'=>19,'setting'=>1, 'url'=>'','name'=>'人工评估'],
                ['id'=>26,'pid'=>20,'setting'=>1, 'url'=>'','name'=>'超参数调整'],
                ['id'=>27,'pid'=>20,'setting'=>1, 'url'=>'','name'=>'数据增强或改进'],
            ];

            return json(['code' => 0, 'msg' => '', 'count' => count($list), 'data' => $list]);
        } else {
            return view('',compact(''));
        }
    }

    #问答管理==============START
    #问答类型
    public function qa_category(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('train_qa_category')->where(['cid'=>0])->count();
            $rows = DB::name('train_qa_category')
                ->where(['cid'=>0])
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>&$v){
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    #添加问答类型
    public function save_qa_category(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isAjax()){
            if($id>0){
                Db::name('train_qa_category')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'type'=>intval($dat['type']),
                ]);
            }else{
                $res = DB::name('train_qa_category')->insertGetId([
                    'name'=>trim($dat['name']),
                    'type'=>intval($dat['type']),
                    'createtime'=>time()
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>'','type'=>''];
            if($id>0){
                $data = Db::name('train_qa_category')->where(['id'=>$id])->find();
            }

            return view('',compact('data','id'));
        }
    }

    #删除问答类型
    public function del_qa_category(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($id>0){
            Db::name('train_qa_category')->where(['id'=>$id])->delete();
            $environment = Db::name('train_qa_environment')->where(['pid'=>$id])->select();
            if(!empty($environment)){
                Db::name('train_qa_environment')->where(['pid'=>$id])->delete();
                foreach($environment as $k=>$v){
                    Db::name('train_qa_list')->where(['pid'=>$v['id']])->delete();
                }
            }
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #关键词库
    public function keywords_manage(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('rag_keywords')->count();
            $rows = DB::name('rag_keywords')
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

    #保存关键词库
    public function save_keywords(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isAjax()){
            if($id>0){
                Db::name('rag_keywords')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                ]);
            }else{
                $res = DB::name('rag_keywords')->insertGetId([
                    'name'=>trim($dat['name']),
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>''];
            if($id>0){
                $data = Db::name('rag_keywords')->where(['id'=>$id])->find();
            }

            return view('',compact('data','id'));
        }
    }

    #删除关键词库
    public function del_keywords(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($id>0){
            Db::name('rag_keywords')->where(['id'=>$id])->delete();
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #问答场景管理
    public function qa_environment(Request $request){
        $dat = input();
        $pid = $dat['pid'];

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('train_qa_environment')->where(['pid'=>$pid])->count();
            $rows = DB::name('train_qa_environment')
                ->where(['pid'=>$pid])
                ->limit($limit)
                ->order($order)
                ->select();


            foreach($rows as $k=>&$v){
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('pid'));
        }
    }

    #保存问答场景
    public function save_qa_environment(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $pid = isset($dat['pid'])?$dat['pid']:0;

        if($request->isAjax()){
            if($id>0){
                Db::name('train_qa_environment')->where(['id'=>$id,'pid'=>$pid])->update([
                    'name'=>trim($dat['name']),
                ]);
            }else{
                $res = DB::name('train_qa_environment')->insertGetId([
                    'pid'=>$pid,
                    'name'=>trim($dat['name']),
                    'createtime'=>time()
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>''];
            if($id>0){
                $data = Db::name('train_qa_environment')->where(['id'=>$id])->find();
            }

            $parent = Db::name('train_qa_category')->where(['id'=>$pid])->find();

            return view('',compact('data','id','pid','parent'));
        }
    }

    #删除问答场景
    public function del_qa_environment(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($id>0){
            Db::name('train_qa_environment')->where(['id'=>$id])->delete();
            Db::name('train_qa_list')->where(['pid'=>$id])->delete();
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #问答场景-文本管理
    public function qa_list(Request $request){
        $dat = input();
        $pid = $dat['pid'];

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('train_qa_list')->where(['pid'=>$pid])->count();
            $rows = DB::name('train_qa_list')
                ->where(['pid'=>$pid])
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>&$v){
                $rows[$k]['question'] = json_decode($v['question'],true);
                $rows[$k]['name'] = '';
                foreach($rows[$k]['question'] as $v2){
                    $rows[$k]['name'] .= $v2;
                }
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('pid'));
        }
    }

    #保存问答文本
    public function save_qa_list(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $pid = isset($dat['pid'])?$dat['pid']:0;

        if($request->isAjax()){
            if(!isset($dat['quest']) || !isset($dat['answer'])){
                return json(['code'=>-1,'msg'=>'请添加“问题-答案对”']);
            }
            $dat['answer'] = array_values($dat['answer']);
            if($id>0){
                Db::name('train_qa_list')->where(['id'=>$id,'pid'=>$pid])->update([
                    'question'=>json_encode($dat['quest'],true),
                    'question_label'=>json_encode($dat['qlabel'],true),
                    'answer'=>json_encode($dat['answer'],true),
                    'answer_label'=>json_encode($dat['alabel'],true),
                ]);
            }else{
                $res = DB::name('train_qa_list')->insertGetId([
                    'pid'=>$pid,
                    'question'=>json_encode($dat['quest'],true),
                    'question_label'=>json_encode($dat['qlabel'],true),
                    'answer'=>json_encode($dat['answer'],true),
                    'answer_label'=>json_encode($dat['alabel'],true),
                    'createtime'=>time()
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>''];
            if($id>0){
                $data = Db::name('train_qa_list')->where(['id'=>$id])->find();
                $data['question'] = json_decode($data['question'],true);
                $data['question_label'] = json_decode($data['question_label'],true);
                $data['answer'] = json_decode($data['answer'],true);
                $data['answer_label'] = json_decode($data['answer_label'],true);
                $pid = $data['pid'];
//                dd($data);
            }

            $environment = Db::name('train_qa_environment')->where(['id'=>$pid])->find();
            $category = Db::name('train_qa_category')->where(['id'=>$environment['pid']])->find();

            $column = [];
            if($category['type']==1){
                #获取商品字段
                $sql = "SHOW FULL COLUMNS FROM goods";
                $column = Db::connect($this->config)->query($sql);
            }
            elseif($category['type']==2){
                #获取订单字段
                $sql = "SHOW FULL COLUMNS FROM ims_website_order_list";
                $column = Db::query($sql);
            }
            elseif($category['type']==3){
                #获取物流字段
                $sql = "SHOW FULL COLUMNS FROM ims_centralize_lines";
                $column = Db::query($sql);
            }

            foreach($column as $k=>$v){
                $column[$k]['Comment'] = str_replace(["\r", "\n"], '', $v['Comment']);
            }
            return view('',compact('data','id','pid','environment','category','column'));
        }
    }

    #删除问答文本
    public function del_qa_list(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($id>0){
            Db::name('train_qa_list')->where(['id'=>$id])->delete();
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
    #问答管理==============END

    #历史对话==============start
    public function qa_history_date(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count = Db::name('train_qa_history')->where(['pid'=>0])->distinct(true)->field("FROM_UNIXTIME(createtime, '%Y-%m-%d') as date")->count();
            $rows = DB::name('train_qa_history')
                ->where(['pid'=>0])
                ->limit($limit)
                ->order($order)
                ->distinct(true)
                ->field("FROM_UNIXTIME(createtime, '%Y-%m-%d') as date")
                ->select();

            foreach($rows as $k=>&$v){

            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    public function qa_history(Request $request){
        $dat = input();
        $date = $dat['date'];

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $starttime = strtotime($date . ' 00:00:00');
            $endtime = strtotime($date . ' 23:59:59');
            $count = Db::name('train_qa_history')->where(['pid'=>0])->where('createtime>='.$starttime.' and createtime<='.$endtime)->count();
            $rows = DB::name('train_qa_history')
                ->where(['pid'=>0])
                ->where('createtime>='.$starttime.' and createtime<='.$endtime)
                ->limit($limit)
                ->order($order)
                ->select();


            foreach($rows as $k=>&$v){
                if($v['uid']==0){
                    $rows[$k]['name'] = '机器人';
                }else{
                    $user = Db::name('website_user')->where(['id'=>$v['uid']])->find();

                    $rows[$k]['name'] = !empty($user['realname'])?$user['realname']:$user['nickname'];
                }

                if($v['status']==0){
                    $rows[$k]['status_name'] = '待审批';
                }
                elseif($v['status']==1){
                    $rows[$k]['status_name'] = '回答正确';
                }
                elseif($v['status']==-1){
                    $rows[$k]['status_name'] = '回答错误-已修改';
                }
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('date'));
        }
    }

    #添加至数据收集表
    public function add_dataset(Request $request){
        $dat = input();
        $type = isset($dat['type'])?$dat['type']:'qa_history';
        foreach($dat['ids'] as $k=>$v){
            if($type=='qa_history'){
                #历史对话新增至数据集（条）
                $question = Db::name('train_qa_history')->where(['id'=>$v])->find();
                $answer = Db::name('train_qa_history')->where(['pid'=>$v])->select();

                $is_add = 0;
                foreach($answer as $k2=>$v2){
                    $real_text = '';
                    if($v2['status']==1){
                        $real_text = '当前的回答结果良好，请继续保持。';
                    }
                    elseif($v2['status']==-1){
                        $real_text = $v2['real_text'];
                    }
                    else{
                        //还没有审批，跳过
                        continue;
                    }
                    Db::name('train_dataset')->insert([
                        'question'=>$question['text'],
                        'answer'=>$v2['text'],
                        'real_text'=>$real_text,
                        'createtime'=>time()
                    ]);
                    $is_add = 1;
                }

                if($is_add==1){
                    Db::name('train_qa_history')->where(['id'=>$v])->update(['is_add_dataset'=>1]);
                }
            }
            elseif($type=='goods'){
                #商品数据新增至数据集（条）
                $method = trim($dat['method']);
                if($method=='select'){
                    #选择商品
                    $goods = Db::connect($this->config)->name('goods')->where(['goods_id'=>$v])->find();
                    $goods_sku = Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$v])->select();
                    $platform = '';
                    if(!empty($goods['other_platform'])){
                        $platform = $goods['other_platform'];
                    }
                    else{
                        $platform = '淘中国';
                    }

                    foreach($goods_sku as $k2=>$v2){
                        $sku_prices = json_decode($v2['sku_prices'],true);
                        $currency = Db::name('centralize_currency')->where(['id'=>$sku_prices['currency'][0]])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
                        $sku = '';
                        if(empty($v2['spec_names'])) {
                            $sku = '无';
                        }
                        else {
                            $sku = $v2['spec_names'];
                        }

                        Db::name('train_dataset')->insert([
                            'question'=>'作为电商客服，请推荐'.$goods['goods_name'],
                            'answer'=>'商品名称：'.$goods['goods_name'].'，平台：'.$platform.'，规格：'.$sku.'，价格：'.$currency.' '.$sku_prices['price'][0].'，库存：'.$sku_prices['goods_number'].'，商品链接：//www.gogo198.cn/goods-'.$v.'.html',
                            'real_text'=>'以下是平台推荐的商品信息：\n 商品名称：'.$goods['goods_name'].'，平台：'.$platform.'，规格：'.$sku.'，价格：'.$currency.' '.$sku_prices['price'][0].'，库存：'.$sku_prices['goods_number'].'，商品链接：//www.gogo198.cn/goods-'.$v.'.html',
                            'createtime'=>time()
                        ]);
                    }
                    Db::connect($this->config)->name('goods')->where(['goods_id'=>$v])->update(['is_add_dataset'=>1]);
                }
                elseif($method=='all'){
                    #所有商品
                    $goods = Db::connect($this->config)->name('goods')->where(['is_add_dataset'=>0])->limit(1000)->field('goods_id,other_platform,goods_name')->select();
                    foreach($goods as $k2=>$v2){
                        $platform = '';
                        if(!empty($v2['other_platform'])){
                            $platform = $v2['other_platform'];
                        }
                        else{
                            $platform = '淘中国';
                        }
                        $goods_sku = Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$v2['goods_id']])->field('sku_prices,spec_names')->select();
                        foreach($goods_sku as $k3=>$v3){
                            $sku_prices = json_decode($v3['sku_prices'],true);
                            $currency = Db::name('centralize_currency')->where(['id'=>$sku_prices['currency'][0]])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
                            $sku = '';
                            if(empty($v3['spec_names'])) {
                                $sku = '无';
                            }
                            else {
                                $sku = $v3['spec_names'];
                            }

                            Db::name('train_dataset')->insert([
                                'question'=>'作为电商客服，请推荐'.$v2['goods_name'],
                                'answer'=>'商品名称：'.$v2['goods_name'].'，平台：'.$platform.'，规格：'.$sku.'，价格：'.$currency.' '.$sku_prices['price'][0].'，库存：'.$sku_prices['goods_number'].'，商品链接：//www.gogo198.cn/goods-'.$v2['goods_id'].'.html',
                                'real_text'=>'以下是平台推荐的商品：\n 商品名称：'.$v2['goods_name'].'，平台：'.$platform.'，规格：'.$sku.'，价格：'.$currency.' '.$sku_prices['price'][0].'，库存：'.$sku_prices['goods_number'].'，商品链接：//www.gogo198.cn/goods-'.$v2['goods_id'].'.html',
                                'createtime'=>time()
                            ]);
                        }
                        Db::connect($this->config)->name('goods')->where(['goods_id'=>$v2['goods_id']])->update(['is_add_dataset'=>1]);
                    }
                }
            }
            elseif($type=='order'){
                #商品数据新增至数据集（条）
                $method = trim($dat['method']);
                if($method=='select'){
                    $order = Db::name('website_order_list')->where(['id'=>$v])->find();
                    #订单信息
                    $order['status_name'] = order_status($order['status']);
                    $order['createtime'] = date('Y-m-d H:i:s',$order['createtime']);
                    $currency = Db::name('centralize_currency')->where(['id'=>$order['currency']])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
                    $order['content'] = json_decode($order['content'],true);
                    $order_info = '订单信息：（订单编号：'.$order['ordersn'].'，订单币种：'.$currency.'，订单金额：'.$order['true_money'].'，实付金额：'.$order['final_money'].'，预付费用：'.$order['prepaid_money'].'，剩余费用：'.$order['remain_money'].'，订单状态：'.$order['status_name'].'，创建时间：'.$order['createtime'].'）。';
                    $buy_goods_info = '订单商品信息：（';
                    foreach($order['content']['goods_info'] as $k2=>$v2){
                        #订单商品信息
                        $goods = Db::connect($this->config)->name('goods')->where(['goods_id'=>$v2['good_id']])->find();
                        $buy_goods_info .= '第'.($k2+1).'个：（商品名称：'.$goods['goods_name'].')，';
                        foreach($v2['sku_info'] as $k3=>$v3){
                            $sku_info = Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$v3['sku_id']])->find();
                            $currency = Db::name('centralize_currency')->where(['id'=>$v3['currency']])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
                            $spec_name = '无具体规格名称';
                            if(!empty($sku_info['spec_names'])){
                                $spec_name = $sku_info['spec_names'];
                            }
                            $buy_goods_info .= '(商品规格'.($k3+1).'：“'.$spec_name.'”，规格价格：'.$currency.$v3['price'].'，购买数量：'.$v3['goods_num'].'），';
                        }
                        $buy_goods_info = rtrim($buy_goods_info,'，');
                        $buy_goods_info .= '。';
                    }
                    $buy_goods_info .= '）。';
                    $order_info = $order_info . $buy_goods_info;

                    #买家信息
                    $user = Db::name('website_user')->where(['id'=>$order['user_id']])->find();
                    $user_name = '';
                    if(!empty($user['realname'])){
                        $user_name = $user['realname'];
                    }else{
                        $user_name = $user['nickname'];
                    }
                    $order_info .= '买家信息：（买家名称：'.$user_name.'，买家手机号：'.$user['area_code'].' '.$user['phone'].'，买家邮箱号：'.$user['email'].'）。';
                    #支付单信息
                    $pay_order_info = '支付单信息：暂无支付信息。';
                    if($order['pay_id']>0){
                        $pay_order = Db::name('customs_collection')->where(['id'=>$order['pay_id']])->find();
                        $pay_overdue_date = '无';
                        if(!empty($pay_order['overdue'])){
                            $pay_overdue_date = date('Y-m-d H:i:s',$pay_order['overdue']);
                        }
                        $status_name = '';
                        if($pay_order['status']==0){
                            $status_name = '待付款';
                        }
                        elseif($pay_order['status']==1){
                            $status_name = '已付款';
                        }
                        $pay_type = '';
                        if($pay_order['pay_type']==1){
                            $status_name = '微信';
                        }
                        elseif($pay_order['pay_type']==2){
                            $status_name = '支付宝';
                        }
                        elseif($pay_order['pay_type']==3){
                            $status_name = '其他支付方式';
                        }
                        $paytime = '';
                        if(!empty($pay_order['paytime'])){
                            $paytime = date('Y-m-d H:i:s',$pay_order['paytime']);
                        }
                        $pay_order_info = '支付单信息：（支付单编号：'.$pay_order['ordersn'].'，交易金额：'.$pay_order['trade_price'].'，付款人名称：'.$pay_order['payer_name'].'，付款人电话：'.$pay_order['payer_tel'].'，付款预期天数：'.$pay_order['pay_term'].'，逾期费率：'.$pay_order['pay_fee'].'，逾期金额：'.$pay_order['overdue_money'].'，付款期限：'.$pay_overdue_date.'，实付金额：'.$pay_order['total_money'].'，付款状态：'.$status_name.'，支付方式：'.$pay_type.'，支付时间：'.$paytime.'）。';
                    }
                    $order_info = $order_info . $pay_order_info;

                    Db::name('train_dataset')->insert([
                        'question'=>'记住以下订单信息',
                        'answer'=>$order_info,
                        'real_text'=>'已记住订单编号（'.$order['ordersn'].'）信息如下：'.$order_info,
                        'createtime'=>time()
                    ]);

                    Db::name('website_order_list')->where(['id'=>$v])->update(['is_add_dataset'=>1]);
                }
                elseif($method=='all'){
                    $order = Db::name('website_order_list')->where(['is_add_dataset'=>0])->limit(2000)->select();
                    foreach($order as $kk=>$vv){
                        #订单信息
                        $order[$kk]['status_name'] = order_status($vv['status']);
                        $order[$kk]['createtime'] = date('Y-m-d H:i:s',$vv['createtime']);
                        $currency = Db::name('centralize_currency')->where(['id'=>$vv['currency']])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
                        $order[$kk]['content'] = json_decode($vv['content'],true);
                        $order_info = '订单信息：（订单编号：'.$order[$kk]['ordersn'].'，订单币种：'.$currency.'，订单金额：'.$order[$kk]['true_money'].'，实付金额：'.$order[$kk]['final_money'].'，预付费用：'.$order[$kk]['prepaid_money'].'，剩余费用：'.$order[$kk]['remain_money'].'，订单状态：'.$order[$kk]['status_name'].'，创建时间：'.$order[$kk]['createtime'].'）。';
                        $buy_goods_info = '订单商品信息：（';
                        foreach($order[$kk]['content']['goods_info'] as $k2=>$v2){
                            #订单商品信息
                            $goods = Db::connect($this->config)->name('goods')->where(['goods_id'=>$v2['good_id']])->find();
                            $buy_goods_info .= '第'.($k2+1).'个：（商品名称：'.$goods['goods_name'].')，';
                            foreach($v2['sku_info'] as $k3=>$v3){
                                $sku_info = Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$v3['sku_id']])->find();
                                $currency = Db::name('centralize_currency')->where(['id'=>$v3['currency']])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
                                $spec_name = '无具体规格名称';
                                if(!empty($sku_info['spec_names'])){
                                    $spec_name = $sku_info['spec_names'];
                                }
                                $buy_goods_info .= '(商品规格'.($k3+1).'：“'.$spec_name.'”，规格价格：'.$currency.$v3['price'].'，购买数量：'.$v3['goods_num'].'），';
                            }
                            $buy_goods_info = rtrim($buy_goods_info,'，');
                            $buy_goods_info .= '。';
                        }
                        $buy_goods_info .= '）。';
                        $order_info = $order_info . $buy_goods_info;

                        #买家信息
                        $user = Db::name('website_user')->where(['id'=>$order[$kk]['user_id']])->find();
                        $user_name = '';
                        if(!empty($user['realname'])){
                            $user_name = $user['realname'];
                        }else{
                            $user_name = $user['nickname'];
                        }
                        $order_info .= '买家信息：（买家名称：'.$user_name.'，买家手机号：'.$user['area_code'].' '.$user['phone'].'，买家邮箱号：'.$user['email'].'）。';
                        #支付单信息
                        $pay_order_info = '支付单信息：暂无支付信息。';
                        if($order['pay_id']>0){
                            $pay_order = Db::name('customs_collection')->where(['id'=>$order[$kk]['pay_id']])->find();
                            $pay_overdue_date = '无';
                            if(!empty($pay_order['overdue'])){
                                $pay_overdue_date = date('Y-m-d H:i:s',$pay_order['overdue']);
                            }
                            $status_name = '';
                            if($pay_order['status']==0){
                                $status_name = '待付款';
                            }
                            elseif($pay_order['status']==1){
                                $status_name = '已付款';
                            }
                            $pay_type = '';
                            if($pay_order['pay_type']==1){
                                $status_name = '微信';
                            }
                            elseif($pay_order['pay_type']==2){
                                $status_name = '支付宝';
                            }
                            elseif($pay_order['pay_type']==3){
                                $status_name = '其他支付方式';
                            }
                            $paytime = '';
                            if(!empty($pay_order['paytime'])){
                                $paytime = date('Y-m-d H:i:s',$pay_order['paytime']);
                            }
                            $pay_order_info = '支付单信息：（支付单编号：'.$pay_order['ordersn'].'，交易金额：'.$pay_order['trade_price'].'，付款人名称：'.$pay_order['payer_name'].'，付款人电话：'.$pay_order['payer_tel'].'，付款预期天数：'.$pay_order['pay_term'].'，逾期费率：'.$pay_order['pay_fee'].'，逾期金额：'.$pay_order['overdue_money'].'，付款期限：'.$pay_overdue_date.'，实付金额：'.$pay_order['total_money'].'，付款状态：'.$status_name.'，支付方式：'.$pay_type.'，支付时间：'.$paytime.'）。';
                        }
                        $order_info = $order_info . $pay_order_info;

                        Db::name('train_dataset')->insert([
                            'question'=>'记住以下订单信息',
                            'answer'=>$order_info,
                            'real_text'=>'已记住订单编号（'.$order['ordersn'].'）信息如下：'.$order_info,
                            'createtime'=>time()
                        ]);

                        Db::name('website_order_list')->where(['id'=>$vv['id']])->update(['is_add_dataset'=>1]);
                    }
                }
            }
            elseif($type=='like'){
                #偏好数据新增至数据集（条）
                $method = trim($dat['method']);
                if($method=='select'){
                    #选择偏好数据
                    $behavior = Db::name('user_behavior_record')->where(['id'=>$v])->find();

                    $event = '用户IP（'.$behavior['ip'].'）';
                    if(!empty($behavior['uid'])){
                        $event .= '，用户ID（'.$behavior['uid'].'）';
                    }
                    $event .= '在'.date('Y-m-d H:i:s',$behavior['createtime']).'时间，发生以下行为：';
                    if(!empty($behavior['watch_seconds'])){
                        $event .= '浏览了商品ID（'.$behavior['goods_id'].'）'.$behavior['watch_seconds'].'秒。';
                    }
                    if(!empty($behavior['collect_goods_id'])){
                        $event .= '收藏了商品ID（'.$behavior['collect_goods_id'].'）。';
                    }
                    if(!empty($behavior['join_goods_id'])){
                        $event .= '加购了商品ID（'.$behavior['join_goods_id'].'）。';
                    }
                    if(!empty($behavior['remove_goods_id'])){
                        $event .= '删减了商品ID（'.$behavior['remove_goods_id'].'）。';
                    }
                    if(!empty($behavior['evaluate_txt'])){
                        $event .= '对订单编号（'.$behavior['ordersn'].'）作出了评价（'.$behavior['evaluate_txt'].'）。';
                    }
                    if(!empty($behavior['evaluate_score'])){
                        $event .= '对订单编号（'.$behavior['ordersn'].'）作出了评分（'.$behavior['evaluate_score'].'）。';
                    }

                    Db::name('train_dataset')->insert([
                        'question'=>'平台新增一条用户事件',
                        'answer'=>$event,
                        'real_text'=>'已记录一条用户事件：'.$event,
                        'createtime'=>time()
                    ]);

                    Db::name('user_behavior_record')->where(['id'=>$v])->update(['is_add_dataset'=>1]);
                }
                elseif($method=='all'){
                    #所有用户行为
                    $behavior = Db::name('user_behavior_record')->where(['is_add_dataset'=>0])->limit(2000)->select();
                    foreach($behavior as $k2=>$v2){
                        $event = '用户IP（'.$v2['ip'].'）';
                        if(!empty($v2['uid'])){
                            $event .= '，用户ID（'.$v2['uid'].'）';
                        }
                        $event .= '在'.date('Y-m-d H:i:s',$v2['createtime']).'时间，发生以下行为：';
                        if(!empty($v2['watch_seconds'])){
                            $event .= '浏览了商品ID（'.$v2['goods_id'].'）'.$v2['watch_seconds'].'秒。';
                        }
                        if(!empty($v2['collect_goods_id'])){
                            $event .= '收藏了商品ID（'.$v2['collect_goods_id'].'）。';
                        }
                        if(!empty($v2['join_goods_id'])){
                            $event .= '加购了商品ID（'.$v2['join_goods_id'].'）。';
                        }
                        if(!empty($v2['remove_goods_id'])){
                            $event .= '删减了商品ID（'.$v2['remove_goods_id'].'）。';
                        }
                        if(!empty($v2['evaluate_txt'])){
                            $event .= '对订单编号（'.$v2['ordersn'].'）作出了评价（'.$v2['evaluate_txt'].'）。';
                        }
                        if(!empty($v2['evaluate_score'])){
                            $event .= '对订单编号（'.$v2['ordersn'].'）作出了评分（'.$v2['evaluate_score'].'）。';
                        }

                        Db::name('train_dataset')->insert([
                            'question'=>'平台新增一条用户事件',
                            'answer'=>$event,
                            'real_text'=>'已记录一条用户事件：'.$event,
                            'createtime'=>time()
                        ]);

                        Db::name('user_behavior_record')->where(['id'=>$v2['id']])->update(['is_add_dataset'=>1]);
                    }
                }
            }
            elseif($type=='logistics'){
                #线路数据新增至数据集（条）
                $lines = Db::name('centralize_lines')->where(['id'=>$v])->find();
                $lines['start_country'] = Db::name('centralize_diycountry_content')->where(['id'=>$lines['start_country']])->field('param2')->find()['param2'];
                $lines['transport_name'] = Db::name('centralize_lines_transport_method')->where(['id'=>$lines['transport_id']])->field('name')->find()['name'];
                $lines['channel_name'] = Db::name('centralize_line_channel')->where(['id'=>$lines['channel_id']])->find()['name'];
                $lines['createtime'] = date('Y-m-d H:i:s',$lines['createtime']);
                #船期截单==
                $lines['week'] = json_decode($lines['week'],true);
                $lines['shipping_date'] = '';
                if($lines['transport_id']==2){
                    if($lines['week']['have_week']==0){
                        $lines['shipping_date'] = '船期信息：无船期';
                    }
                    elseif($lines['week']['have_week']==1){
                        $lines['shipping_date'] = '船期信息：有船期，周'.$lines['week']['start_week'].'截单，'.$lines['week']['this_week'].'周'.$lines['week']['end_week'].'开船；';
                    }
                }
                #尾程派送
                $lines_delivery = Db::name('centralize_lines_delivery')->where(['id'=>$lines['delivery_id']])->find();
                $lines['deliver_info'] = $lines_delivery['name'].'-'.$lines_delivery['remark'];

                #线路内容==
                $lines['content'] = json_decode($lines['content'],true);
                foreach($lines['content']['procategory'] as $k2=>$v2){
                    #货物类别名称
                    $lines['content']['procategory'][$k2] = Db::name('centralize_gvalue_list')->where(['id'=>$v2])->field('name')->find()['name'];
                    #适用货物属性名称
                    $chicategory = explode(',',$lines['content']['chicategory'][$k2]);
                    $chicategory_name = '';
                    foreach($chicategory as $k3=>$v3){
                        $this_chicategory_name = Db::name('centralize_gvalue_product')->where(['id'=>$v3])->field('name')->find()['name'];
                        $chicategory_name .= ','.$this_chicategory_name;
                    }
                    $lines['content']['chicategory'][$k2] = rtrim(ltrim($chicategory_name,','),',');
                    #计费标准====
                    #最低消费
                    if($lines['content']['mini_cost'][$k2]==1){
                        $lines['content']['mini_cost'][$k2] = '无最低';
                    }
                    elseif($lines['content']['mini_cost'][$k2]==2){
                        $lines['content']['mini_cost'][$k2] = '有最低';
                        $lines['content']['minicost_unit'][$k2] = Db::name('unit')->where(['code_value'=>$lines['content']['minicost_unit'][$k2]])->field('code_name')->find()['code_name'];
                    }
                    #计费区间
                    foreach($lines['content']['qj1'][$k2] as $k3=>$v3){
                        #计费区间--数值/以上
                        if($lines['content']['qj2_method'][$k2][$k3]==1){
                            $lines['content']['qj2_method'][$k2][$k3]='数值';
                        }
                        elseif($lines['content']['qj2_method'][$k2][$k3]==2){
                            $lines['content']['qj2_method'][$k2][$k3]='以上';
                        }
                        #计费单位
                        $lines['content']['unit'][$k2][$k3] = Db::name('unit')->where(['code_value'=>$lines['content']['unit'][$k2][$k3]])->field('code_name')->find()['code_name'];
                        #计费方式
                        foreach($lines['content']['jf_method'][$k2][$k3] as $k4=>$v4){
                            if($lines['content']['jf_method'][$k2][$k3][$k4]==1){
                                $lines['content']['jf_method'][$k2][$k3][$k4] = '首续计费';
                                #计费币种
                                $lines['content']['currency'][$k2][$k3][$k4] = Db::name('centralize_currency')->where(['id'=>$lines['content']['currency'][$k2][$k3][$k4]])->field('currency_symbol_standard')->find()['currency_symbol_standard'];

                                if(!empty($lines['content']['currency'][$k2][$k3][$k4])){
                                    $lines['content']['currency'][$k2][$k3][$k4] = 'CNY';
                                }
                            }
                            elseif($lines['content']['jf_method'][$k2][$k3][$k4]==2){
                                $lines['content']['jf_method'][$k2][$k3][$k4] = '按量计费';
                                #计费币种
                                if(count($lines['content']['currency'][$k2][$k3][$k4])>0){
                                    foreach($lines['content']['currency'][$k2][$k3][$k4] as $k5=>$v5){
                                        $lines['content']['currency'][$k2][$k3][$k4][$k5] = Db::name('centralize_currency')->where(['id'=>$lines['content']['currency'][$k2][$k3][$k4][$k5]])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
                                    }
                                }
                                else{
                                    $lines['content']['currency'][$k2][$k3][$k4] = Db::name('centralize_currency')->where(['id'=>$lines['content']['currency'][$k2][$k3][$k4]])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
                                }

                                if(!empty($lines['content']['currency'][$k2][$k3][$k4])){
                                    $lines['content']['currency'][$k2][$k3][$k4] = 'CNY';
                                }
                            }
                            elseif($lines['content']['jf_method'][$k2][$k3][$k4]==3){
                                $lines['content']['jf_method'][$k2][$k3][$k4] = '分段计费';
                                #计费币种
                                foreach($lines['content']['currency'][$k2][$k3][$k4] as $k5=>$v5){
                                    $lines['content']['currency'][$k2][$k3][$k4][$k5] = Db::name('centralize_currency')->where(['id'=>$lines['content']['currency'][$k2][$k3][$k4][$k5]])->field('currency_symbol_standard')->find()['currency_symbol_standard'];

                                    if(!empty($lines['content']['currency'][$k2][$k3][$k4][$k5])){
                                        $lines['content']['currency'][$k2][$k3][$k4][$k5] = 'CNY';
                                    }
                                }

                                #分段计费方式
                                foreach($lines['content']['fenduan_method'][$k2][$k3][$k4] as $k5=>$v5){
                                    if($v5==1){
                                        $lines['content']['fenduan_method'][$k2][$k3][$k4][$k5] = '数值';
                                    }
                                    elseif($v5==2){
                                        $lines['content']['fenduan_method'][$k2][$k3][$k4][$k5] = '以上';
                                    }
                                }
                            }
                        }
                    }
                    #体积算法====
                    #分泡
                    if(!empty($lines['content']['fenpao'][$k2])){
                        $lines['content']['fenpao'][$k2] = Db::name('centralize_lines_strict')->where(['id'=>$lines['content']['fenpao'][$k2]])->field('name')->find()['name'];
                    }
                    #超限条款====
                    #超重限制名称
                    if(!empty($lines['content']['overweight'][$k2])){
                        $lines['content']['overweight'][$k2] = Db::name('centralize_lines_strict')->where(['id'=>$lines['content']['overweight'][$k2]])->field('name')->find()['name'];
                    }
                    #超长限制====
                    #超长限制名称
                    if(!empty($lines['content']['overlong'][$k2])){
                        $lines['content']['overlong'][$k2] = Db::name('centralize_lines_strict')->where(['id'=>$lines['content']['overlong'][$k2]])->field('name')->find()['name'];
                    }
                    #其他限制====
                    #其他限制名称
                    if(!empty($lines['content']['overother'][$k2])){
                        $lines['content']['overother'][$k2] = Db::name('centralize_lines_strict')->where(['id'=>$lines['content']['overother'][$k2]])->field('name')->find()['name'];
                    }
                    #清关说明====
                    if(!empty($lines['content']['clearance'][$k2])){
                        $lines['content']['clearance'][$k2] = Db::name('centralize_lines_clearance')->where(['id'=>$lines['content']['clearance'][$k2]])->field('name')->find()['name'];
                    }
                    #配送说明====
                    #配送到门=
                    #可送区域-按行政区
                    $kesong_area_all = '';
                    if(!empty($lines['content']['kesong_area1'][$k2])){
                        $kesong_area = Db::name('centralize_adminstrative_area')->where(['country_id' => $lines['end_country'], 'id' => $lines['content']['kesong_area1'][$k2]])->field('code_name')->find()['code_name'];
                        $kesong_area_all .= $kesong_area;
                    }
                    if(!empty($lines['content']['kesong_area2'][$k2])){
                        $kesong_area = Db::name('centralize_adminstrative_area')->where(['country_id' => $lines['end_country'], 'id' => $lines['content']['kesong_area2'][$k2]])->field('code_name')->find()['code_name'];
                        $kesong_area_all .= ' '.$kesong_area;
                    }
                    if(!empty($lines['content']['kesong_area3'][$k2])){
                        $kesong_area = Db::name('centralize_adminstrative_area')->where(['country_id' => $lines['end_country'], 'id' => $lines['content']['kesong_area3'][$k2]])->field('code_name')->find()['code_name'];
                        $kesong_area_all .= ' '.$kesong_area;
                    }
                    $lines['content']['kesong_area_all'][$k2] = $kesong_area_all;#可送区域整合
                    #不送区域=
                    #不送区域-按行政区
                    $busong_area_all = '';
                    if(!empty($lines['content']['busong_area1'][$k2])){
                        $busong_area = Db::name('centralize_adminstrative_area')->where(['country_id' => $lines['end_country'], 'id' => $lines['content']['busong_area1'][$k2]])->field('code_name')->find()['code_name'];
                        $busong_area_all .= $busong_area;
                    }
                    if(!empty($lines['content']['busong_area2'][$k2])){
                        $busong_area = Db::name('centralize_adminstrative_area')->where(['country_id' => $lines['end_country'], 'id' => $lines['content']['busong_area2'][$k2]])->field('code_name')->find()['code_name'];
                        $busong_area_all .= ' '.$busong_area;
                    }
                    if(!empty($lines['content']['busong_area3'][$k2])){
                        $busong_area = Db::name('centralize_adminstrative_area')->where(['country_id' => $lines['end_country'], 'id' => $lines['content']['busong_area3'][$k2]])->field('code_name')->find()['code_name'];
                        $busong_area_all .= ' '.$busong_area;
                    }
                    $lines['content']['busong_area_all'][$k2] = $busong_area_all;#不可送区域整合
                    #定点自提=
                    #定点地址区域
                    $dingdian_area_all = '';
                    if(!empty($lines['content']['dingdian_area1'][$k2])){
                        $dingdian_area = Db::name('centralize_adminstrative_area')->where(['country_id' => $lines['end_country'], 'id' => $lines['content']['dingdian_area1'][$k2]])->field('code_name')->find()['code_name'];
                        $dingdian_area_all .= $dingdian_area;
                    }
                    if(!empty($lines['content']['dingdian_area2'][$k2])){
                        $dingdian_area = Db::name('centralize_adminstrative_area')->where(['country_id' => $lines['end_country'], 'id' => $lines['content']['dingdian_area2'][$k2]])->field('code_name')->find()['code_name'];
                        $dingdian_area_all .= $dingdian_area;
                    }
                    if(!empty($lines['content']['dingdian_area3'][$k2])){
                        $dingdian_area = Db::name('centralize_adminstrative_area')->where(['country_id' => $lines['end_country'], 'id' => $lines['content']['dingdian_area3'][$k2]])->field('code_name')->find()['code_name'];
                        $dingdian_area_all .= $dingdian_area;
                    }
                    $lines['content']['dingdian_area_all'][$k2] = $dingdian_area_all;#定点区域整合
                    #税费说明====
                    #已含税费=
                    foreach($lines['content']['shuifei_name'][$k2] as $k3=>$v3){
                        if(!empty($lines['content']['shuifei_unit'][$k2][$k3])){
                            #税费计量单位（名称）
                            $lines['content']['shuifei_unit'][$k2][$k3] = Db::name('unit')->where(['code_value'=>$lines['content']['shuifei_unit'][$k2][$k3]])->field('code_name')->find()['code_name'];
                        }
                        if(!empty($lines['content']['shuifei_currency'][$k2][$k3])){
                            #税费计量币种（名称）
                            $lines['content']['shuifei_currency'][$k2][$k3] = Db::name('unit')->where(['code_value'=>$lines['content']['shuifei_currency'][$k2][$k3]])->field('code_name')->find()['code_name'];
                        }
                    }
                    #未含税费=
                    foreach($lines['content']['noshuifei_name'][$k2] as $k3=>$v3){
                        if(!empty($lines['content']['noshuifei_unit'][$k2][$k3])){
                            #税费计量单位（名称）
                            $lines['content']['noshuifei_unit'][$k2][$k3] = Db::name('unit')->where(['code_value'=>$lines['content']['noshuifei_unit'][$k2][$k3]])->field('code_name')->find()['code_name'];
                        }
                        if(!empty($lines['content']['noshuifei_currency'][$k2][$k3])){
                            #税费计量币种（名称）
                            $lines['content']['noshuifei_currency'][$k2][$k3] = Db::name('unit')->where(['code_value'=>$lines['content']['noshuifei_currency'][$k2][$k3]])->field('code_name')->find()['code_name'];
                        }
                    }
                    #潜在税费=
                    foreach($lines['content']['maybeshuifei_name'][$k2] as $k3=>$v3){
                        if(!empty($lines['content']['maybeshuifei_unit'][$k2][$k3])){
                            #税费计量单位（名称）
                            $lines['content']['maybeshuifei_unit'][$k2][$k3] = Db::name('unit')->where(['code_value'=>$lines['content']['maybeshuifei_unit'][$k2][$k3]])->field('code_name')->find()['code_name'];
                        }
                        if(!empty($lines['content']['maybeshuifei_currency'][$k2][$k3])){
                            #税费计量币种（名称）
                            $lines['content']['maybeshuifei_currency'][$k2][$k3] = Db::name('unit')->where(['code_value'=>$lines['content']['maybeshuifei_currency'][$k2][$k3]])->field('code_name')->find()['code_name'];
                        }
                    }
                    #参考时效====
                    #节点名称
                    if(!empty($lines['content']['shixiao_type'][$k2])){
                        $lines['content']['shixiao_type'][$k2] = Db::name('centralize_lines_referentime')->where(['id'=>$lines['content']['shixiao_type'][$k2]])->field('name')->find()['name'];
                    }
                    #物流查询====
                    #物流商户
                    if(!empty($lines['content']['logistics'][$k2])){
                        $lines['content']['logistics'][$k2] = Db::name('centralize_diycountry_content')->where(['pid'=>6,'id'=>$lines['content']['logistics'][$k2]])->field('param3')->find()['param3'];
                    }
                    #申报要求====
                    #其他说明====
                }
                $lines['end_country'] = Db::name('centralize_diycountry_content')->where(['id'=>$lines['end_country']])->find()['param2'];

                foreach($lines['content']['procategory'] as $k2=>$v2){
                    $this->log_train_dataset_logistics($k2,$lines['content'],$lines);
                }

                Db::name('centralize_lines')->where(['id'=>$v])->update(['is_add_dataset'=>1]);
            }
            elseif($type=='standard') {
                #标准数据新增至数据集（条）
                $qa = Db::name('train_qa_list')->where(['id'=>$v])->find();#标准格式
                $qa_environment = Db::name('train_qa_environment')->where(['id'=>$qa['pid']])->find();#标准格式环境
                $qa_category = Db::name('train_qa_category')->where(['id'=>$qa_environment['id']])->find();#标准格式分类
                $qa_question = '';
                $qa['question'] = json_decode($qa['question'],true);
                $qa['question_label'] = json_decode($qa['question_label'],true);
                foreach($qa['question'] as $k2=>$v2){
                    if($qa['question_label'][$k2]==2){
                        #表字段
                        $qa_question .= '{'.$v2.'}';
                    }
                    elseif($qa['question_label'][$k2]==1){
                        #随机文字
                        $qa_question .= $v2;
                    }
                }
                $qa['answer'] = json_decode($qa['answer'],true);
                $qa['answer_label'] = json_decode($qa['answer_label'],true);

                foreach($qa['answer'] as $k2=>$v2){
                    $qa_answer = '';
                    foreach($v2 as $k3=>$v3){
                        if($qa['answer_label'][$k2][$k3]==1){
                            #随机文字
                            $qa_answer .= $v3;
                        }
                        elseif($qa['answer_label'][$k2][$k3]==2){
                            #表字段
                            $qa_answer .= '{'.$v3.'}';
                        }
                    }
                    if(!empty($qa_answer)){
                        Db::name('train_dataset')->insert([
                            'question'=>$qa_question,#$qa_environment['name']
                            'answer'=>$qa_answer,
                            'real_text'=>$qa_answer,
                            'createtime'=>time()
                        ]);
                    }
                }

//                $name = '“'.$qa_category['name'].'”类的“'.$qa_environment['name'].'”场景的“'.$qa_question.'”问题';
                Db::name('train_qa_list')->where(['id'=>$v])->update(['is_add_dataset'=>1]);
            }
            elseif($type=='words'){
                #统一描述新增至数据集（条）
                $words = Db::name('train_goods_words')->where(['id'=>$v])->find();

                Db::name('train_dataset')->insert([
                    'question'=>'记住这个商品知识',
                    'answer'=>$words['words'],
                    'real_text'=>'已记住这个商品知识',
                    'createtime'=>time()
                ]);

                Db::name('train_goods_words')->where(['id'=>$v])->update(['is_add_dataset'=>1]);
            }
        }

        return json(['code'=>0,'msg'=>'数据已添加']);
    }

    #记录线路内容
    public function log_train_dataset_logistics($k2,$content=[],$lines=[]){
//        dd($lines);
        $lines_info = '线路名称：'.$lines['name'].'，线路代码：'.$lines['code'].'，线路渠道：'.$lines['channel_name'].'，运输方式：'.$lines['transport_name'].'，';
        if($lines['transport_name']=='联运'){
            $lines_info .= '联运详情：'.$lines['transport_union'].'，';
        }
        elseif($lines['transport_name']=='海运'){
            $lines_info .= '船期截单：'.$lines['shipping_date'].'，';
        }
        $lines_info .= '尾程派送：'.$lines['deliver_info'].'，始运国：'.$lines['start_country'].'，运抵国：'.$lines['end_country'].'，';
        foreach($lines['content']['procategory'] as $k=>$v){
            #货物类别、适用货物====
            $lines_info .= '货物类别：'.$v.'，适用货物：（'.$lines['content']['chicategory'][$k].'），';
            #计费标准====
            #最低消费=
            if($lines['content']['mini_cost'][$k]=='无最低'){
                $lines_info .= '最低消费：'.$lines['content']['mini_cost'][$k].'，';
            }
            elseif($lines['content']['mini_cost'][$k]=='有最低'){
                $minicost_unit = Db::name('unit')->where(['code_value'=>$lines['content']['minicost_unit'][$k]])->field('code_name')->find()['code_name'];
                $lines_info .= '最低消费：'.$lines['content']['mini_num'][$k].' '.$minicost_unit.'，';
            }
            #体积算法
            if(!empty($lines['content']['rate'][$k])) {
                $lines_info .= '体积比率：' . $lines['content']['rate'][$k] . '，';
            }
            #分泡方式
            if(!empty($lines['content']['fenpao'][$k])){
                $lines_info .= '分泡方式：'.$lines['content']['fenpao'][$k].'，';
            }
            #超限条款
            if(!empty($lines['content']['overweight'][$k])){
                $lines_info .= '超重限制：（【'.$lines['content']['overweight'][$k].'】'.$lines['content']['overweight_remark'][$k].'），';
            }
            #超长限制
            if(!empty($lines['content']['overlong'][$k])){
                $lines_info .= '超长限制：（【'.$lines['content']['overlong'][$k].'】'.$lines['content']['overlong_remark'][$k].'），';
            }
            #其他限制
            if(!empty($lines['content']['overother'][$k])){
                $lines_info .= '其他限制：（【'.$lines['content']['overother'][$k].'】'.$lines['content']['overother_remark'][$k].'），';
            }
            #清关说明
            if(!empty($lines['content']['clearance'][$k])){
                $lines_info .= '清关说明：'.$lines['content']['clearance'][$k].'，';
            }
            #配送说明-配送到门-可送区域
            if(!empty($lines['content']['kesong_area_all'][$k])){
                $lines_info .= '可送区域：'.$lines['content']['kesong_area_all'][$k].'，';
                if(!empty($lines['content']['kesong_post'][$k])){
                    $lines_info .= '可送邮编：“'.$lines['content']['kesong_post'][$k].'”，';
                }
                if(!empty($lines['content']['diy_kesong'][$k])){
                    $lines_info .= '可送详细区域：“'.$lines['content']['diy_kesong'][$k].'”，';
                }
            }
            #配送说明-配送到门-不送区域
            if(!empty($lines['content']['busong_area_all'][$k])){
                $lines_info .= '不送区域：'.$lines['content']['busong_area_all'][$k].'，';
                if(!empty($lines['content']['busong_post'][$k])){
                    $lines_info .= '不送邮编：“'.$lines['content']['busong_post'][$k].'”，';
                }
                if(!empty($lines['content']['diy_busong'][$k])){
                    $lines_info .= '不送详细区域：“'.$lines['content']['diy_busong'][$k].'”，';
                }
            }
            #配送说明-配送到门-备注说明
            if(!empty($lines['content']['peisong_remark'][$k])) {
                $lines_info .= '配送到门备注说明：' . $lines['content']['peisong_remark'][$k] . '，';
            }
            #配送说明-定点自提
            if(!empty($lines['content']['dingdian_name'][$k])){
                $lines_info .= '自提定点名称：'.$lines['content']['dingdian_name'][$k].'，';
                if(!empty($lines['content']['dingdian_area_all'][$k])){
                    $lines_info .= '自提定点地址：'.$lines['content']['dingdian_area_all'][$k].$lines['content']['dingdian_address'][$k].'，';
                }
            }
            #配送说明-不予配送
            if(!empty($lines['content']['busong_remark'][$k])){
                $lines_info .= '不予配送说明：'.$lines['content']['busong_remark'][$k].'，';
            }
            #税费说明-已含税费
            $lines_info .= '已含税费：（';
            foreach($lines['content']['shuifei_name'][$k] as $k2=>$v2){
                if(!empty($lines['content']['shuifei_name'][$k][$k2])){
                    $lines_info .= '第'.($k2+1).'点：（税费名称：'.$lines['content']['shuifei_name'][$k][$k2].'，摘要说明：'.$lines['content']['shuifei_intro'][$k][$k2].'，税费价格：'.$lines['content']['shuifei_currency'][$k][$k2].$lines['content']['shuifei_price'][$k][$k2].'/'.$lines['content']['shuifei_unit'][$k][$k2].'，税费说明：'.$lines['content']['shuifei_remark'][$k][$k2].'）';
                }
            }
            $lines_info .= '），';
            #税费说明-未含税费
            $lines_info .= '未含税费：（';
            foreach($lines['content']['noshuifei_name'][$k] as $k2=>$v2){
                if(!empty($lines['content']['noshuifei_name'][$k][$k2])){
                    $lines_info .= '第'.($k2+1).'点：（税费名称：'.$lines['content']['noshuifei_name'][$k][$k2].'，摘要说明：'.$lines['content']['noshuifei_intro'][$k][$k2].'，税费价格：'.$lines['content']['noshuifei_currency'][$k][$k2].$lines['content']['noshuifei_price'][$k][$k2].'/'.$lines['content']['noshuifei_unit'][$k][$k2].'，税费说明：'.$lines['content']['noshuifei_remark'][$k][$k2].'）';
                }
            }
            $lines_info .= '），';
            #税费说明-潜在税费
            $lines_info .= '潜在税费：（';
            foreach($lines['content']['maybeshuifei_name'][$k] as $k2=>$v2){
                if(!empty($lines['content']['maybeshuifei_name'][$k][$k2])){
                    $lines_info .= '第'.($k2+1).'点：（税费名称：'.$lines['content']['maybeshuifei_name'][$k][$k2].'，摘要说明：'.$lines['content']['maybeshuifei_intro'][$k][$k2].'，税费价格：'.$lines['content']['maybeshuifei_currency'][$k][$k2].$lines['content']['maybeshuifei_price'][$k][$k2].'/'.$lines['content']['maybeshuifei_unit'][$k][$k2].'，税费说明：'.$lines['content']['maybeshuifei_remark'][$k][$k2].'）';
                }
            }
            $lines_info .= '），';
            #参考时效
            if(!empty($lines['content']['shixiao_type'][$k])){
                $day_type = '';
                if($lines['content']['shixiao_daytype'][$k]==1){
                    $day_type = '工作天';
                }
                elseif($lines['content']['shixiao_daytype'][$k]==2){
                    $day_type = '自然日';
                }
                else{
                    $day_type = $lines['content']['shixiao_daytype'][$k];
                }
                $lines_info .= '参考时效：'.$lines['content']['shixiao_type'][$k].$lines['content']['shixiao_num'][$k].$day_type.'，';
            }
            #物流查询
            if(!empty($lines['content']['logistics'][$k])){
                $lines_info .= '物流商户：'.$lines['content']['logistics'][$k].'，物流查询网址：'.$lines['content']['logistics_website'][$k].'，';
            }
            #申报要求-品名要求
            if(!empty($lines['content']['nameReq'][$k])){
                $lines_info .= '品名要求：（'.$lines['content']['nameReq'][$k].'），';
            }
            #申报要求-价值要求
            if(!empty($lines['content']['valueReq'][$k])){
                $lines_info .= '价值要求：（'.$lines['content']['valueReq'][$k].'），';
            }
            #申报要求-其他要求
            if(!empty($lines['content']['valueReq'][$k])){
                $lines_info .= '其他要求：（'.$lines['content']['otherReq'][$k].'），';
            }
            #其他说明
            if(!empty(trim($lines['content']['other_remark'][$k]))){
                $lines_info .= '其他说明：（'.trim($lines['content']['other_remark'][$k]).'），';
            }
            #计费区间=
            foreach($lines['content']['qj1'][$k] as $k2=>$v2){
                $lines_info .= '计费区间：自'.$v2.$lines['content']['unit'][$k][$k2].'至';
                if($lines['content']['qj2_method'][$k][$k2]=='数值'){
                    $lines_info .= $lines['content']['qj2'][$k][$k2].$lines['content']['unit'][$k][$k2].'，';
                }
                elseif($lines['content']['qj2_method'][$k][$k2]=='以上'){
                    $lines_info .= $lines['content']['qj2_method'][$k][$k2].'，';
                }
                #计费进阶
                $lines_info .= '计费进阶：'.$lines['content']['jinjie'][$k][$k2].$lines['content']['unit'][$k][$k2].'，';

                #计费方式
                foreach($lines['content']['jf_method'][$k][$k2] as $k3=>$v3){
                    $lines_info .= '计费方式：（（'.$v3.'）';
                    if($v3=='首续计费'){
                        $lines_info .= '首重：'.$lines['content']['shouzhong'][$k][$k2][$k3].$lines['content']['unit'][$k][$k2].'/'.$lines['content']['currency'][$k][$k2][$k3].$lines['content']['shouzhong_money'][$k][$k2][$k3].'，';
                        $lines_info .= '续重：'.$lines['content']['xuzhong'][$k][$k2][$k3].$lines['content']['unit'][$k][$k2].'/'.$lines['content']['currency'][$k][$k2][$k3].$lines['content']['xuzhong_money'][$k][$k2][$k3].'）。';

                        $this->log_line_dataset($lines_info);
                    }
                    elseif($v3=='按量计费'){
                        $lines_info .= $lines['content']['anliang'][$k][$k2][$k3].$lines['content']['unit'][$k][$k2].'/'.$lines['content']['currency'][$k][$k2][$k3].$lines['content']['anliang_money'][$k][$k2][$k3].'）。';

                        $this->log_line_dataset($lines_info);
                    }
                    elseif($v3=='分段计费'){
                        foreach($lines['content']['fenduan_num1'][$k][$k2][$k3] as $k4=>$v4){
                            $lines_info .= $v4.$lines['content']['unit'][$k][$k2].' 至 ';
                            if($lines['content']['fenduan_method'][$k][$k2][$k3][$k4]=='数值'){
                                $lines_info .= $lines['content']['fenduan_num2'][$k][$k2][$k3][$k4].$lines['content']['unit'][$k][$k2].' '.$lines['content']['currency'][$k][$k2][$k3][$k4].$lines['content']['fenduan_money'][$k][$k2][$k3][$k4].'）。';
                            }
                            elseif($lines['content']['fenduan_method'][$k][$k2][$k3][$k4]=='以上'){
                                $lines_info .= '以上 '.$lines['content']['currency'][$k][$k2][$k3][$k4].$lines['content']['fenduan_money'][$k][$k2][$k3][$k4].'）。';
                            }

                            $this->log_line_dataset($lines_info);
                        }
                    }
                }
            }
        }
    }

    #记录线路内容-插入每个计价方式的线路信息为单独数据条到数据表
    public function log_line_dataset($lines_info){
        Db::name('train_dataset')->insert([
            'question'=>'平台有哪些物流线路信息？',
            'answer'=>$lines_info,
            'real_text'=>'目前平台物流线路信息如下，'.$lines_info,
            'createtime'=>time()
        ]);
    }

    public function save_qa_history(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isAjax()){
            $status = 0;
            $update_arr = [];
            foreach($dat['answer_id'] as $k=>$v){
                if($status==0){
                    $status = $dat['status'][$k];
                }
                elseif($status==1 && $dat['status'][$k]==-1){
                    $status=$dat['status'];
                }

                $update_arr['status'] = $dat['status'][$k];
                if($dat['status'][$k]==-1){
                    if(empty(trim($dat['real_text'][$k]))){
                        return json(['code'=>-1,'msg'=>'请填写正确的回答']);
                    }
                    $update_arr['real_text'] = trim($dat['real_text'][$k]);
                }

                Db::name('train_qa_history')->where(['id'=>$v])->update($update_arr);
            }
            if($status==0){
                return json(['code'=>-1,'msg'=>'请选择回答审批状态']);
            }
            Db::name('train_qa_history')->where(['id'=>$id])->update(['status'=>$status]);

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            if($id>0){
                $data = Db::name('train_qa_history')->where(['id'=>$id])->find();
                $data['children'] = Db::name('train_qa_history')->where(['pid'=>$id])->select();

                foreach($data['children'] as $k=>$v){
                    $data['children'][$k]['process'] = Db::name('rag_operation_logs')->where(['answer_id'=>$v['id']])->select();
                }
            }

            return view('',compact('data','id'));
        }
    }
    #历史对话==============end

    #增量更新==============start
    #商品数据集
    public function goods_data(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count = Db::connect($this->config)->name('goods')->whereRaw('level_id=0')->count();
            $rows = DB::connect($this->config)->name('goods')
                ->whereRaw('level_id=0')
                ->limit($limit)
                ->order($order)
                ->field('goods_id,goods_name,goods_currency,goods_price,is_add_dataset,created_at')
                ->select();


            foreach($rows as $k=>&$v){
                $rows[$k]['goods_currency'] = Db::name('centralize_currency')->where(['id'=>$v['goods_currency']])->find()['currency_symbol_standard'];

            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('date'));
        }
    }

    #订单数据集
    public function order_data(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count = Db::name('website_order_list')->count();
            $rows = DB::name('website_order_list')
                ->limit($limit)
                ->order($order)
                ->field('id,ordersn,currency,prepaid_money,true_money,final_money,remain_money,status,is_add_dataset,createtime')
                ->select();


            foreach($rows as $k=>&$v){
                $rows[$k]['currency'] = Db::name('centralize_currency')->where(['id'=>$v['currency']])->find()['currency_symbol_standard'];
                $rows[$k]['status_name'] = order_status($v['status']);
                $rows[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('date'));
        }
    }

    #偏好数据集
    public function like_data(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count = Db::name('user_behavior_record')->count();
            $rows = DB::name('user_behavior_record')
                ->limit($limit)
                ->order($order)
                ->select();


            foreach($rows as $k=>&$v){
                $event = '用户IP（'.$v['ip'].'）';
                if(!empty($v['uid'])){
                    $event .= '，用户ID（'.$v['uid'].'）';
                }
                $event .= '发生以下行为：';
                if(!empty($v['watch_seconds'])){
                    $event .= '浏览了商品ID（'.$v['goods_id'].'）'.$v['watch_seconds'].'秒。';
                }
                if(!empty($v['collect_goods_id'])){
                    $event .= '收藏了商品ID（'.$v['collect_goods_id'].'）。';
                }
                if(!empty($v['join_goods_id'])){
                    $event .= '加购了商品ID（'.$v['join_goods_id'].'）。';
                }
                if(!empty($v['remove_goods_id'])){
                    $event .= '删减了商品ID（'.$v['remove_goods_id'].'）。';
                }
                if(!empty($v['evaluate_txt'])){
                    $event .= '对订单编号（'.$v['ordersn'].'）作出了评价（'.$v['evaluate_txt'].'）。';
                }
                if(!empty($v['evaluate_score'])){
                    $event .= '对订单编号（'.$v['ordersn'].'）作出了评分（'.$v['evaluate_score'].'）。';
                }

                $rows[$k]['event'] = $event;
                $rows[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    #订单管理选取特征至数据集
    public function save_character_to_dataset(Request $request){
        $dat = input();

        if($request->isAjax()){
            $character = explode(',',$dat['character']);
            if(!empty($character)){
                $list = Db::name('website_order_list')->where(['is_add_dataset'=>0])->select();
                foreach($list as $k=>$v){
                    #订单信息
                    $list[$k]['status_name'] = order_status($v['status']);
                    $list[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
                    $currency = Db::name('centralize_currency')->where(['id'=>$v['currency']])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
                    $list[$k]['content'] = json_decode($v['content'],true);
                    $order_info = '订单信息：（订单编号：'.$v['ordersn'].'，订单币种：'.$currency.'，订单金额：'.$v['true_money'].'，实付金额：'.$v['final_money'].'，预付费用：'.$v['prepaid_money'].'，剩余费用：'.$v['remain_money'].'，订单状态：'.$list[$k]['status_name'].'，订单创建时间：'.$list[$k]['createtime'].'）。';

                    $order_info .= '订单商品信息如下：（';
                    foreach($list[$k]['content'] as $k3=>$v3){
                        $goods = Db::connect($this->config)->name('goods')->where(['goods_id'=>$v3['good_id']])->field('goods_name,cat_id,brand_type,brand_type2,brand_name,click_count')->find();
                        $order_info .= '（商品名称：'.$goods['goods_name'].'，';
                        foreach($character as $k2=>$v2){
                            if($v2==1){
                                #商品类别名称
                                $order_info .= '商品类别名称：';
                                foreach($list[$k]['content'] as $k3=>$v3){
                                    $goods = Db::connect($this->config)->name('goods')->where(['goods_id'=>$v3['good_id']])->field('cat_id,brand_type,brand_type2,brand_name')->find();
                                    if($goods['cat_id']>0){
                                        $category = Db::connect($this->config)->name('category')->where(['cat_id'=>$goods['cat_id']])->find();
                                        $order_info .= '“'.$category['cat_name'].'”，';
                                    }
                                }
                            }
                            elseif($v2==2) {
                                #商品品牌名称
                                $order_info .= '商品品牌名称：';
                                if($goods['brand_type']==0){
                                    $order_info .= '“无牌”，';
                                }
                                elseif($goods['brand_type']==1){
                                    #有牌
                                    if($goods['brand_type2']==1){
                                        #品牌
                                        $brand_name = Db::connect($this->config)->name('brand')->where(['brand_id'=>$goods['brand_id']])->field('brand_name')->find()['brand_name'];
                                        $order_info .= '“'.$brand_name.'”，';
                                    }
                                    elseif($goods['brand_type2']==2){
                                        #名牌
                                        $order_info .= '“'.$goods['brand_name'].'”，';
                                    }
                                }
                            }
                            elseif($v2==3) {
                                #商品价格区间
                                $order_info .= '商品价格区间：';
                                $goods_sku = Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$v3['good_id']])->field('sku_prices')->select();
                                $low_price = -1;
                                $max_price = 0;
                                $currency = '';
                                foreach($goods_sku as $k3=>$v3){
                                    $sku_prices = json_decode($v3['sku_prices'],true);
//                                    {"goods_number":"130","disabled_num":"0","start_num":["1","11","100"],"unit":["001","001","001"],"select_end":["1","1","2"],"end_num":["10","99",""],"currency":["5","5","5"],"price":["100","90","80"]}

                                    $currency = Db::name('centralize_currency')->where(['id'=>$sku_prices['currency'][0]])->field('currency_symbol_standard')->find()['currency_symbol_standard'];

                                    $max = max($sku_prices['price']);
                                    $min = min($sku_prices['price']);

                                    if($low_price==-1){
                                        #初始化此商品价格的最大值与最小值
                                        $low_price = $min;
                                        $max_price = $max;
                                    }else{
                                        #调整此商品的最大值与最小值
                                        if($min<$low_price){
                                            $low_price = $min;
                                        }

                                        if($max_price<$max){
                                            $max_price = $max;
                                        }
                                    }
                                }
                                $order_info .= $currency.' '.$low_price.'~'.$max_price.'，';
                            }
                            elseif($v2==4) {
                                #商品规格信息
                                $order_info .= '商品规格信息：（';
                                foreach($v3['sku_info'] as $k4=>$v4){
                                    $goods_sku = Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$v4['sku_id']])->field('spec_names,sku_prices')->find();
                                    $sku_prices = json_decode($goods_sku['sku_prices'],true);
                                    $order_info .= '规格'.($k4+1).'：（规格名称：“'.$v3['spec_names'].'”，规格库存：'.$sku_prices['goods_number'].'），';
                                }
                                $order_info = rtrim($order_info,'，');
                                $order_info .= '），';
                            }
                            elseif($v2==5){
                                #商品浏览次数
                                $order_info .= '商品浏览次数：'.$goods['click_count'].'，';
                            }
                            elseif($v2==6){
                                #商品收藏次数
                                $star_num = Db::name('user_behavior_record')->where(['collect_goods_id'=>$v3['good_id']])->count();
                                $order_info .= '商品收藏次数：'.$star_num.'，';
                            }
                            elseif($v2==7) {
                                #商品加购次数
                                $star_num = Db::name('user_behavior_record')->where(['join_goods_id'=>$v3['good_id']])->count();
                                $order_info .= '商品加购次数：'.$star_num.'，';
                            }
                            elseif($v2==8) {
                                #订单评分
                                $star_num = Db::name('user_behavior_record')->where(['ordersn'=>$v['ordersn']])->find();
                                $order_info .= '订单评分：'.$star_num['evaluate_score'].'，';
                            }
                            elseif($v2==9) {
                                #订单评论
                                $star_num = Db::name('user_behavior_record')->where(['ordersn'=>$v['ordersn']])->find();
                                $order_info .= '订单评分：'.$star_num['evaluate_txt'].'，';
                            }
                            elseif($v2==10) {
                                #用户购买金额的平均值和总和
                                $all_order = Db::name('website_order_list')->whereRaw('user_id='.$v['user_id'].' and status>=1')->select();
                                $avg_money = 0;
                                $all_money = 0;
                                foreach($all_order as $k4 =>$v4){
                                    $all_money += $v4['final_money'];
                                }

                                $avg_money = sprintf('%.2f',($all_money / count($all_order)));
                                $order_info .= '该用户ID（'.$v['user_id'].'）所有订单的金额总和是：'.$all_money.'，平均每一单的金额是：'.$avg_money.'，';
                            }
                        }
                        $order_info = rtrim($order_info,'，');
                        $order_info .= '），';
                    }
                    $order_info = rtrim($order_info,'，');
                    $order_info .= '）。';

                    Db::name('train_dataset')->insert([
                        'question'=>'记住以下订单信息',
                        'answer'=>$order_info,
                        'real_text'=>'已记住订单编号（'.$v['ordersn'].'）信息如下：'.$order_info,
                        'createtime'=>time()
                    ]);

                    Db::name('website_order_list')->where(['id'=>$v['id']])->update(['is_add_dataset'=>1]);
                }
            }else{
                return json(['code'=>-1,'msg'=>'请选择商品特征']);
            }
        }
        else{
            $character = json_encode([
                ['id'=>1,'name'=>'商品类别名称'],
                ['id'=>2,'name'=>'商品品牌名称'],
                ['id'=>3,'name'=>'商品价格区间'],
                ['id'=>4,'name'=>'商品规格信息'],
                ['id'=>5,'name'=>'商品浏览次数'],
                ['id'=>6,'name'=>'商品收藏次数'],
                ['id'=>7,'name'=>'商品加购次数'],
                ['id'=>8,'name'=>'订单评分'],
                ['id'=>9,'name'=>'订单评论'],
                ['id'=>10,'name'=>'用户购买订单金额的平均值和总和'],
            ],true);
            return view('',compact('character'));
        }
    }

    #物流数据集
    public function logistics_data(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count = Db::name('centralize_lines')->count();
            $rows = DB::name('centralize_lines')
                ->limit($limit)
                ->order($order)
                ->field('id,name,code,start_country,end_country,channel_id,is_add_dataset,createtime')
                ->select();


            foreach($rows as $k=>&$v){
                $rows[$k]['start_country'] = Db::name('centralize_diycountry_content')->where(['id'=>$v['start_country']])->find()['param2'];
                $rows[$k]['end_country'] = Db::name('centralize_diycountry_content')->where(['id'=>$v['end_country']])->find()['param2'];
                $rows[$k]['channel_name'] = Db::name('centralize_line_channel')->where(['id'=>$v['channel_id']])->find()['name'];

                $rows[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('date'));
        }
    }

    public function marketing_goods_backup(Request $request){
        $dat = input();

        if($request->isAjax()){
            if($dat['pa']==1){
                #获取商品
                // 排序
                $order = 'goods_id desc';
                // 分页
                $page = (input('page')-1)*10;
                $limit = $page . ',10';
                $search = trim($dat['search']);
                $where = 'goods_status=1 and level_id=0';
                if(!empty($search)){
                    $where .= ' and goods_name like "%'.$search.'%"';
                }

                $total = Db::connect($this->config)->name('goods')->whereRaw($where)->count();
                $list = Db::connect($this->config)->name('goods')
                    ->whereRaw($where)
                    ->limit($limit)
                    ->order($order)
                    ->select();

                foreach($list as $k=>$v){
                    $list[$k]['currency'] = Db::name('centralize_currency')->where(['id'=>$v['goods_currency']])->find()['currency_symbol_standard'];
                }

                return json(['code'=>0,'total'=>$total,'list'=>$list]);
            }
            elseif($dat['pa']==2){
                #保存当前商品营销信息
                $dat['add_goods'] = array_unique($dat['add_goods']);
                $goods_id = implode(',',$dat['add_goods']);

                Db::name('marketing_goods')->update([
                    'goods_id'=>$goods_id
                ]);
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }
        else{
            $marketing_goods = Db::name('marketing_goods')->find();
            $goods_selected = [];
            if(!empty($marketing_goods['goods_id'])){
                $goods_selected = Db::connect($this->config)->name('goods')->whereRaw('goods_id in ('.$marketing_goods['goods_id'].')')->field('goods_id,goods_name,goods_currency,goods_price,created_at')->select();
                foreach($goods_selected as $k=>$v){
                    $goods_selected[$k]['currency'] = Db::name('centralize_currency')->where(['id'=>$v['goods_currency']])->find()['currency_symbol_standard'];
                }
            }

            return view('',compact('marketing_goods','goods_selected'));
        }
    }

    #同步数据至本地
    public function sync_info_to_local(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count = Db::name('sync_list')->count();
            $rows = DB::name('sync_list')
                ->limit($limit)
                ->order($order)
                ->select();

            $type = ['商品','订单','物流','会员'];
            foreach($rows as $k=>&$v){
                $rows[$k]['type'] = $type[$v['type']];

                if($v['sync_type']==1){
                    $rows[$k]['sync_type'] = '系统自动按顺序同步';
                } elseif($v['sync_type']==2){
                    $rows[$k]['sync_type'] = '系统从头来开始同步';
                }

                if($v['method']==1){
                    $rows[$k]['method'] = '现在';
                } elseif($v['method']==2){
                    $rows[$k]['method'] = '定时';
                }

                if($v['status']==-1){
                    $rows[$k]['status'] = '同步失败';
                } elseif($v['status']==0){
                    $rows[$k]['status'] = '未同步';
                } elseif($v['status']==1){
                    $rows[$k]['status'] = '同步中';
                } elseif($v['status']==2){
                    $rows[$k]['status'] = '同步完成';
                }
                $rows[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);

            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    #保存同步任务信息
    public function save_sync_info_to_local(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isAjax()){
            if($id>0){
                Db::name('sync_list')->where(['id'=>$id])->update([
                    'type'=>$dat['type'],
                    'sync_type'=>$dat['sync_type'],
                    'sync_num'=>$dat['sync_num'],
                    'method'=>$dat['method'],
                    'starttime'=>$dat['method']==2?$dat['starttime']:'',
                    'status'=>$dat['method']==1?1:0,
                ]);
            }
            else{
                $id = Db::name('sync_list')->insertGetId([
                    'type'=>$dat['type'],
                    'sync_type'=>$dat['sync_type'],
                    'sync_num'=>$dat['sync_num'],
                    'method'=>$dat['method'],
                    'starttime'=>$dat['method']==2?$dat['starttime']:'',
                    'status'=>$dat['method']==1?1:0,
                    'createtime'=>time()
                ]);
                $dat['id'] = $id;
            }

            if($dat['method']==1){
                $res = now_sync_to_local($dat);
                if($res==1){
                    #同步完成
                    Db::name('sync_list')->where(['id'=>$id])->update(['status'=>2]);
                }
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }
        else{
            $data = ['type'=>0,'sync_type'=>1,'sync_num'=>1000,'method'=>1,'starttime'=>''];
            if($id>0){
                $data = Db::name('sync_list')->where(['id'=>$id])->find();
            }
            return view('',compact('data','id'));
        }
    }

    #删除同步任务信息
    public function del_sync_info_to_local(Request $request){
        $dat = input();

        $res = Db::name('sync_list')->where(['id'=>intval($dat['id'])])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #同步文档数据至本地
    public function sync_file_to_local(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count = Db::name('knowledge_list')->whereRaw('status=1 and ( type=1 or type=2 )')->count();
            $rows = DB::name('knowledge_list')
                ->whereRaw('status=1 and ( type=1 or type=2 )')
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>&$v){
                $rows[$k]['company_name'] = Db::name('website_user_company')->where(['id'=>$v['cid']])->field('company')->find()['company'];

                if($v['type']==1){
                    $rows[$k]['type_name'] = '商品资讯';
                } elseif($v['type']==2){
                    $rows[$k]['type_name'] = '政策资讯';
                }

                if($v['is_add_dataset']==-1) {
                    $rows[$k]['status_name'] = '同步失败';
                } elseif($v['is_add_dataset']==0){
                    $rows[$k]['status_name'] = '未同步';
                } elseif($v['is_add_dataset']==1){
                    $rows[$k]['status_name'] = '已同步';
                }

                $rows[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);

            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    #保存同步文档数据至本地
    public function save_sync_file_to_local(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isAjax()){

        }else{
            $data = Db::name('knowledge_list')->where(['id'=>$id])->find();
            if($data['type']==1){
                #商品资讯
                $data['goods_name'] = Db::connect($this->config)->name('goods')->where(['goods_id'=>$data['knowledge_id']])->field('goods_name')->find()['goods_name'];
            }
            elseif($data['type']==2){
                #政策资讯

            }


            if(!empty($data['file_path'])){
                $data['file_path'] = json_decode($data['file_path'],true);
            }

            return view('',compact('data','id'));
        }
    }

    #同步该id下的数据文档至本地电脑
    public function sync_fileinfo_to_local(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        $list = Db::name('knowledge_list')->where(['id'=>$id])->find();
        $list['file_path'] = json_decode($list['file_path'],true);

        $res = now_sync_file_to_local($list);

        Db::name('knowledge_list')->where(['id'=>$id])->update(['is_add_dataset'=>$res]);

        return json(['code'=>0,'msg'=>'构建成功']);
    }

    #同步缓存热门商品列表
    public function cache_goods_data(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count = Db::name('merchant_hotproduct')->count();
            $rows = DB::name('merchant_hotproduct')
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>&$v){
                $rows[$k]['company_name'] = Db::name('website_user_company')->where(['id'=>$v['cid']])->field('company')->find()['company'];

                if($v['is_add_dataset']==-1) {
                    $rows[$k]['status_name'] = '同步失败';
                } elseif($v['is_add_dataset']==0){
                    $rows[$k]['status_name'] = '未同步';
                } elseif($v['is_add_dataset']==1){
                    $rows[$k]['status_name'] = '已同步';
                }
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    #查看同步缓存热门商品信息
    public function save_cache_goods_data(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isAjax()){

        }else{
            $data = Db::name('merchant_hotproduct')->where(['id'=>$id])->find();
            $data['goods_id'] = explode(',',$data['goods_id']);

            $goods_name = '';
            foreach($data['goods_id'] as $k=>$v){
                $this_goods_name = Db::connect($this->config)->name('goods')->where(['goods_id'=>$v])->field('goods_name')->find();
                $goods_name .= $this_goods_name['goods_name'].'，';
            }

            $goods_name = rtrim($goods_name,'，');

            return view('',compact('data','id', 'goods_name'));
        }
    }

    #同步缓存热门商品信息
    public function sync_hotproduct_to_local(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        $list = Db::name('merchant_hotproduct')->where(['id'=>$id])->find();

        $res = now_sync_cache_product_to_local($list);

        Db::name('merchant_hotproduct')->where(['id'=>$id])->update(['is_add_dataset'=>$res]);

        return json(['code'=>0,'msg'=>'操作成功']);
    }

    #同步缓存用户偏好列表
    public function cache_user_data(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count = Db::name('user_behavior_record')->count();
            $rows = DB::name('user_behavior_record')
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>&$v){
                $event = '用户IP（'.$v['ip'].'）';
                if(!empty($v['uid'])){
                    $event .= '，用户ID（'.$v['uid'].'）';
                }
                $event .= '发生以下行为：';
                if(!empty($v['watch_seconds'])){
                    $event .= '浏览了商品ID（'.$v['goods_id'].'）'.$v['watch_seconds'].'秒。';
                }
                if(!empty($v['collect_goods_id'])){
                    $event .= '收藏了商品ID（'.$v['collect_goods_id'].'）。';
                }
                if(!empty($v['join_goods_id'])){
                    $event .= '加购了商品ID（'.$v['join_goods_id'].'）。';
                }
                if(!empty($v['remove_goods_id'])){
                    $event .= '删减了商品ID（'.$v['remove_goods_id'].'）。';
                }
                if(!empty($v['evaluate_txt'])){
                    $event .= '对订单编号（'.$v['ordersn'].'）作出了评价（'.$v['evaluate_txt'].'）。';
                }
                if(!empty($v['evaluate_score'])){
                    $event .= '对订单编号（'.$v['ordersn'].'）作出了评分（'.$v['evaluate_score'].'）。';
                }

                $rows[$k]['event'] = $event;
                $rows[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    #同步缓存缓存用户偏好信息
    public function sync_userbehavior_to_local(Request $request){
        $dat = input();

        $list = Db::name('user_behavior_record')->where(['is_add_dataset'=>0])->select();
        foreach($list as $k=>&$v){
            $event = '用户IP（'.$v['ip'].'）';
            $list[$k]['redis_key'] = '用户IP（'.$v['ip'].'）';
            if(!empty($v['uid'])){
                $event .= '，用户ID（'.$v['uid'].'）';
                $list[$k]['redis_key'] = '用户ID（'.$v['uid'].'）';
            }
            $date = date('Y-m-d H:i:s',$v['createtime']);
            $event .= '在时间节点“'.$date.'”，发生以下行为：';
            if(!empty($v['watch_seconds'])){
                $event .= '浏览了商品ID（'.$v['goods_id'].'）'.$v['watch_seconds'].'秒。';
            }
            if(!empty($v['collect_goods_id'])){
                $event .= '收藏了商品ID（'.$v['collect_goods_id'].'）。';
            }
            if(!empty($v['join_goods_id'])){
                $event .= '加购了商品ID（'.$v['join_goods_id'].'）。';
            }
            if(!empty($v['remove_goods_id'])){
                $event .= '删减了商品ID（'.$v['remove_goods_id'].'）。';
            }
            if(!empty($v['evaluate_txt'])){
                $event .= '对订单编号（'.$v['ordersn'].'）作出了评价（'.$v['evaluate_txt'].'）。';
            }
            if(!empty($v['evaluate_score'])){
                $event .= '对订单编号（'.$v['ordersn'].'）作出了评分（'.$v['evaluate_score'].'）。';
            }

            $list[$k]['event'] = $event;
        }
        $res = sync_userbehavior_to_local($list);

//        Db::name('user_behavior_record')->where(['is_add_dataset'=>0])->update(['is_add_dataset'=>$res]);

        return json(['code'=>0,'msg'=>'操作成功']);
    }
    #增量更新==============end

    #商品知识==============start
    #生成txt文件
    public function goods_words(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count = Db::name('train_goods_words')->count();
            $rows = DB::name('train_goods_words')
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

    public function save_goods_words(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isAjax()){
            if($id>0){
                Db::name('train_goods_words')->where(['id'=>$id])->update([
                    'words'=>trim($dat['words']),
                ]);
            }else{
                $res = DB::name('train_goods_words')->insertGetId([
                    'words'=>trim($dat['words']),
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['words'=>''];
            if($id>0){
                $data = Db::name('train_goods_words')->where(['id'=>$id])->find();
            }

            return view('',compact('data','id'));
        }
    }

    #输入为停用词txt格式
    public function goods_words_export(Request $request){
        $words = Db::name('train_goods_words')->select();

        // 初始化 TXT 文件内容
        $txtContent = "";
        // 逐行拼接数据
        foreach ($words as $row) {
            $txtContent .= $row['words'] . PHP_EOL;
        }

        // 设置响应头
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="exported_data.txt"');
        header('Content-Length: ' . strlen($txtContent));

        // 输出文件内容
        echo $txtContent;
    }

    public function del_goods_words(){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($id>0){
            Db::name('train_goods_words')->where(['id'=>$id])->delete();
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
    #商品知识==============end


    #大模型总开关==============start
    public function switch_type(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count = Db::name('train_type')->count();
            $rows = DB::name('train_type')
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

    public function save_switch_type(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isAjax()){
            if($id>0){
                Db::name('train_type')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                ]);
            }else{
                $res = DB::name('train_type')->insertGetId([
                    'name'=>trim($dat['name']),
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>''];
            if($id>0){
                $data = Db::name('train_type')->where(['id'=>$id])->find();
            }

            return view('',compact('data','id'));
        }
    }

    public function del_switch_type(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($id>0){
            Db::name('train_type')->where(['id'=>$id])->delete();
            Db::name('train_setting')->where(['type'=>$id])->delete();
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    public function model_switch(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count = Db::name('train_setting')->count();
            $rows = DB::name('train_setting')
                ->limit($limit)
                ->order($order)
                ->select();


            foreach($rows as $k=>$v){

                $rows[$k]['type_name'] = Db::name('train_type')->where(['id'=>$v['type']])->field('name')->find()['name'];

                if($v['direction_type']==1){
                    $rows[$k]['direction_name'] = '本地服务';
                }elseif($v['direction_type']==2){
                    $rows[$k]['direction_name'] = '云端服务';
                }elseif($v['direction_type']==3){
                    $rows[$k]['direction_name'] = 'MaxKB服务';
                }
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    public function save_model_switch(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isAjax()){
            if($id>0){
                Db::name('train_setting')->where(['id'=>$id])->update([
                    'type'=>intval($dat['type']),
                    'direction_type'=>intval($dat['direction_type']),
                ]);
            }else{
                $res = DB::name('train_setting')->insertGetId([
                    'type'=>intval($dat['type']),
                    'direction_type'=>intval($dat['direction_type']),
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $type = Db::name('train_type')->select();
            $data = ['type'=>1,'direction_type'=>1];
            if($id>0){
                $data = Db::name('train_setting')->where(['id'=>$id])->find();
            }

            return view('',compact('data','id','type'));
        }
    }

    public function del_model_switch(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($id>0){
            Db::name('train_setting')->where(['id'=>$id])->delete();
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
    #大模型总开关==============end

    #数据准备==============start
    #数据收集
    public function collect_data(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count = Db::name('train_dataset')->where(['status'=>0])->count();
            $rows = DB::name('train_dataset')
                ->where(['status'=>0])
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>&$v){
                $rows[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{

            return view('',compact(''));
        }
    }

    #生成数据包
    public function save_package(Request $request){
        $dat = input();
        $ids = $dat['ids'];#数据条ids
        $isall = $dat['isall'];#是否全部，1是，0否

        if($request->isAjax()){
            $id = $dat['id'];#数据包id
            if($isall==1){
                #是全部
                $train_dataset = Db::name('train_dataset')->where(['status'=>0])->field('id')->select();
                $train_dataset = array_column($train_dataset, 'id');
                $train_dataset = implode(',',$train_dataset);

                if($id>0){
                    $package = Db::name('train_dataset_package')->where(['id'=>$id])->find();
                    $package['dataset_ids'] .= ','.$train_dataset;
                    Db::name('train_dataset_package')->where(['id'=>$id])->update([
                        'dataset_ids'=>$package['dataset_ids'],
                    ]);
                }
                else{
                    Db::name('train_dataset_package')->insert([
                        'name'=>trim($dat['name']),
                        'dataset_ids'=>$train_dataset,
                        'status'=>0,
                        'createtime'=>time()
                    ]);
                }

                $train_dataset = explode(',',$train_dataset);
                foreach($train_dataset as $k=>$v){
                    Db::name('train_dataset')->where(['id'=>$v])->update(['status'=>1]);

                    #同步数据条到本地电脑
                    sync_info($v,'dataset');
                }
            }
            elseif($isall==0){
                if($id>0){
                    $package = Db::name('train_dataset_package')->where(['id'=>$id])->find();
                    $package['dataset_ids'] .= ','.$ids;
                    Db::name('train_dataset_package')->where(['id'=>$id])->update([
                        'dataset_ids'=>$package['dataset_ids'],
                    ]);
                }
                else{
                    Db::name('train_dataset_package')->insert([
                        'name'=>trim($dat['name']),
                        'dataset_ids'=>$ids,
                        'status'=>0,
                        'createtime'=>time()
                    ]);
                }

                $ids = explode(',',$ids);
                foreach($ids as $k=>$v){
                    Db::name('train_dataset')->where(['id'=>$v])->update(['status'=>1]);

                    #同步数据条到本地电脑
                    sync_info($v,'dataset');
                }
            }

            if($id>0){
                return json(['code'=>0,'msg'=>'合并数据至数据包成功']);
            }
            else{
                return json(['code'=>0,'msg'=>'生成数据至数据包成功']);
            }
        }
        else{
            $package = Db::name('train_dataset_package')->where(['status'=>0])->select();
            return view('',compact('package','ids','isall'));
        }
    }

    #撤销收集(暂时不做)
    public function del_collect_data(Request $request){
        $dat = input();
    }

    #数据包管理
    public function collect_data_package(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count = Db::name('train_dataset_package')->count();
            $rows = DB::name('train_dataset_package')
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>&$v){
                if($v['status']==0){
                    $rows[$k]['status_name'] = '未训练';
                }
                elseif($v['status']==1){
                    $rows[$k]['status_name'] = '训练中';
                }
                elseif($v['status']==2){
                    $rows[$k]['status_name'] = '训练完成';
                }
                elseif($v['status']==-1){
                    $rows[$k]['status_name'] = '训练失败';
                }
                $rows[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{

            return view('',compact(''));
        }
    }

    #微调训练（废弃）
    public function start_lora2(Request $request){
        $dat = input();

        $id = $dat['id'];
        $package = Db::name('train_dataset_package')->where(['id'=>$id])->find();

        $dataset_ids = explode(',',$package['dataset_ids']);
        $dataset_arr = [];
        foreach($dataset_ids as $k=>$v){
            $dataset = Db::name('train_dataset')->where(['id'=>$v])->find();
            array_push($dataset_arr,['instruction'=>$dataset['question'],'input'=>$dataset['answer'],'output'=>$dataset['real_text']]);
        }

        #开始把数据推到本地电脑===================start
        // ngrok 生成的公网地址
        $webhookUrl = 'https://e4f4-14-212-107-18.ngrok-free.app/lora_webhook';
        // 1. 构造请求数据
        $payload = [
            'message' => $dataset_arr
        ];
        // 2. 转换为 JSON（保留中文不转义，模拟 Python 的 json=payload）
        $jsonData = json_encode($payload, true);

        // 3. 初始化 cURL（模拟 Python 的 requests.post）
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $webhookUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData)
            ],
            CURLOPT_RETURNTRANSFER => true, // 不直接输出响应，返回字符串
            CURLOPT_SSL_VERIFYPEER => false, // 本地测试时禁用 SSL 验证（生产环境需启用）
            CURLOPT_TIMEOUT => 10, // 超时时间（秒）
            CURLOPT_CONNECTTIMEOUT => 5 // 连接超时时间（秒）
        ]);

        $result = '';
        // 4. 执行请求并获取响应
        try {
            $responseBody = curl_exec($ch);
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            // 5. 检查响应状态（模拟 Python 的 response.raise_for_status()）
            if ($httpStatusCode < 200 || $httpStatusCode >= 300) {
                Log::info("Webhook 请求失败，状态码：{$httpStatusCode}，响应：{$responseBody}");
//                throw new Exception("Webhook 请求失败，状态码：{$httpStatusCode}，响应：{$responseBody}");
                return json(['code'=>-1,'msg'=>'本地电脑webhook发生错误！']);
            }

            // 6. 记录推送日志（模拟 Python 的 logger.info）
            $currentTime = date("Y年m月d日H时i分s秒"); // PHP 日期格式与 Python 对应
            $numRecords = count($dataset); // 获取数据条数
            $logMessage = "在{$currentTime}，推送了{$numRecords}条数据\n";

            // 写入日志文件（可替换为实际日志系统）
            Log::info('webhook推送成功：'.$logMessage);
            $result = ['code'=>0,'msg'=>'Webhook 推送成功，响应：' . $responseBody];

//            Db::name('train_dataset_package')->where(['id'=>$id])->update(['status'=>1]);

        } catch (Exception $e) {
            // 处理错误（包括 cURL 错误和状态码错误）
            $error = curl_errno($ch) ? curl_error($ch) : $e->getMessage();

            Log::info('webhook推送失败：'.$error);
            $result = ['code'=>-1,'msg'=>'cURL Error: ' . $error];
        }

        // 关闭 cURL 句柄
        curl_close($ch);

        return json($result);
        #开始把数据推到本地电脑===================end
    }

    #设置数据包参数
    public function save_package_setting(Request $request){
        $dat = input();
        $id = $dat['id'];

        if($request->isAjax()){
            if(empty($dat['model_id']) && empty($dat['optimizer']) && empty($dat['learning_rate']) && empty($dat['training_rounds']) && empty($dat['batch_size'])){
                return json(['code'=>-1,'msg'=>'请输入训练参数']);
            }

            if($dat['train_time']==2 && empty($dat['starttime'])){
                return json(['code'=>-1,'msg'=>'请选择定时训练开始时间']);
            }

            DB::name('train_dataset_package')->where(['id'=>$id])->update([
                'model_id'=>$dat['model_id'],
                'optimizer'=>trim($dat['optimizer']),
                'learning_rate'=>trim($dat['learning_rate']),
                'training_rounds'=>trim($dat['training_rounds']),
                'batch_size'=>trim($dat['batch_size']),
                'train_time'=>$dat['train_time'],
                'starttime'=>$dat['train_time']==2?strtotime(trim($dat['starttime'])):''
            ]);


            if($dat['train_time']==1){
                #设置为训练中
                DB::name('train_dataset_package')->where(['id'=>$id])->update(['status'=>1,'traintime'=>time()]);
//                $res = start_lora($id); #该方法废弃

                #同步数据包
                $res = sync_info($id,'package');

                #记录推送日志
                $package = DB::name('train_dataset_package')->where(['id'=>$id])->find();
                $package_dataset_count = explode(',',$package['dataset_ids']);
                Log::info('在'.date('Y-m-d H:i:s')."时段，推送了训练数据（".count($package_dataset_count)."条）");

                #记录训练状态
//                DB::name('train_dataset_package')->where(['id'=>$id])->update(['status'=>2]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }
        else{
            $data = Db::name('train_dataset_package')->where(['id'=>$id])->find();
            if(empty($data['learning_rate'])){
                $data['learning_rate'] = '1e-5';
            }
            if(empty($data['training_rounds'])){
                $data['training_rounds'] = '2';
            }
            if(empty($data['batch_size'])){
                $data['batch_size'] = '1';
            }
            if(!empty($data['starttime'])){
                $data['starttime'] = date('Y-m-d H:i:s',$data['starttime']);
            }
            $model = Db::name('train_model_list')->select();

            return view('',compact('data','id','model'));
        }
    }
    #数据准备==============end

    #训练参数管理==========start
    public function save_train(Request $request){
        $dat = input();
        $type = $dat['type'];

        if($request->isAjax()){
            $update_data = [];
            if($type=='select_model'){
                $update_data = ['model_name'=>trim($dat['model_name'])];
            }
            elseif($type=='collection_ids'){
                $update_data = ['collection_ids'=>trim($dat['collection_ids'])];
            }
            elseif($type=='optimizer'){
                $update_data = ['optimizer'=>trim($dat['optimizer'])];
            }
            elseif($type=='learning_rate'){
                $update_data = ['learning_rate'=>trim($dat['optimizer'])];
            }
            elseif($type=='training_rounds'){
                $update_data = ['training_rounds'=>trim($dat['training_rounds'])];
            }
            elseif($type=='batch_size'){
                $update_data = ['batch_size'=>trim($dat['batch_size'])];
            }
            Db::name('train_lora_list')->where(['id'=>1])->update($update_data);

            return json(['code'=>0,'msg'=>'保存成功，正在刷新页面']);
        }else{
            $data = ['collection_ids'=>'','model_name'=>'','optimizer'=>'','learning_rate'=>'','training_rounds'=>'','batch_size'=>''];

            $data = Db::name('train_lora_list')->where(['id'=>1])->find();

            $data_collection = json_encode([['id'=>1,'name'=>'商品数据'],['id'=>2,'name'=>'订单数据'],['id'=>3,'name'=>'物流线路数据']],true);

            $model = Db::name('train_model_list')->select();

            return view('',compact('data','type','data_collection','model'));
        }
    }
    #训练参数管理==========end
    #模型训练=================================================end

    #Lora模型日志管理================================================start
    public function train_log_manage(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count = Db::name('lora_operation_logs')->field("FROM_UNIXTIME(createtime, '%Y-%m-%d') as date")->group('date')->count();
            $rows = DB::name('lora_operation_logs')
                ->limit($limit)
                ->order($order)
                ->field("FROM_UNIXTIME(createtime, '%Y-%m-%d') as date")
//                ->distinct(true)
                ->group('date')
                ->select();

            foreach($rows as $k=>&$v){

            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    #查看当日数据包
    public function log_package(Request $request){
        $dat = input();
        $date = $dat['date'];

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $starttime = strtotime($date . ' 00:00:00');
            $endtime = strtotime($date . ' 23:59:59');

            $count = Db::name('train_dataset_package')->where('traintime>='.$starttime.' and traintime<='.$endtime)->count();
            $rows = DB::name('train_dataset_package')
                ->where('traintime>='.$starttime.' and traintime<='.$endtime)
                ->limit($limit)
                ->order($order)
                ->select();

            foreach($rows as $k=>&$v){
                #数据量（条）
                $rows[$k]['data_num'] = count(explode(',',$v['dataset_ids']));
                #训练时间
                $rows[$k]['traintime'] = date('Y-m-d H:i:s',$v['traintime']);
                #当前状态
                if($v['status']==0){
                    $rows[$k]['status_name'] = '未训练';
                }
                elseif($v['status']==1){
                    $rows[$k]['status_name'] = '训练中';
                }
                elseif($v['status']==2){
                    $rows[$k]['status_name'] = '训练完成';
                }
                elseif($v['status']==-1){
                    $rows[$k]['status_name'] = '训练失败，请联系技术人员处理';
                }
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('date'));
        }
    }

    #查看数据包的训练日志
    public function log_package_history(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isAjax()){

        }else{
            if($id>0){
                $data = Db::name('lora_operation_logs')->where(['package_id'=>$id])->select();
            }

            return view('',compact('data','id'));
        }
    }
    #Lora模型日志管理================================================end

    #数据同步日志管理================================================end
    public function sync_info_manage(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count = Db::name('sync_logs')->field("FROM_UNIXTIME(createtime, '%Y-%m-%d') as date")->group('date')->count();
            $rows = DB::name('sync_logs')
                ->limit($limit)
                ->order($order)
//                ->distinct(true)
                ->field("FROM_UNIXTIME(createtime, '%Y-%m-%d') as date")
                ->group('date')
                ->select();

            foreach($rows as $k=>&$v){

            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    #查看当日同步数据
    public function log_sync(Request $request){
        $dat = input();
        $date = $dat['date'];

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $starttime = strtotime($date . ' 00:00:00');
            $endtime = strtotime($date . ' 23:59:59');

            $count = Db::name('sync_logs')->where('createtime>='.$starttime.' and createtime<='.$endtime)->count();
            $rows = DB::name('sync_logs')
                ->alias('a')
                ->join('sync_list b','b.id=a.pid')
                ->where('a.createtime>='.$starttime.' and a.createtime<='.$endtime)
                ->field('a.id,a.send_num,a.success_num,a.fail_num,a.createtime as endtime,b.createtime as starttime,b.type as sync_name')
                ->limit($limit)
                ->order($order)
                ->select();

            $sync_name = ['商品','订单','物流','会员'];
            foreach($rows as $k=>&$v){
                $rows[$k]['sync_name'] = $sync_name[$v['sync_name']];
                #同步时间
                $rows[$k]['endtime'] = date('Y-m-d H:i:s',$v['endtime']);
                $rows[$k]['starttime'] = date('Y-m-d H:i:s',$v['starttime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('date'));
        }
    }
    #数据同步日志管理================================================end

    #RAG模型日志管理=================================================start
    #发生日志的日期
    public function log_manage(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count = Db::name('train_qa_history')->where(['pid'=>0])->field("FROM_UNIXTIME(createtime, '%Y-%m-%d') as date")->group('date')->count();
            $rows = DB::name('train_qa_history')
                ->where(['pid'=>0])
                ->limit($limit)
                ->order($order)
//                ->distinct(true)
                ->field("FROM_UNIXTIME(createtime, '%Y-%m-%d') as date")
                ->group('date')
                ->select();

            foreach($rows as $k=>&$v){

            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact(''));
        }
    }

    #当日对话的用户
    public function log_user(Request $request){
        $dat = input();
        $date = $dat['date'];

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $starttime = strtotime($date . ' 00:00:00');
            $endtime = strtotime($date . ' 23:59:59');

            $count = Db::name('train_qa_history')->where(['pid'=>0])->where('createtime>='.$starttime.' and createtime<='.$endtime)->count();
            $rows = DB::name('train_qa_history')
                ->where(['pid'=>0])
                ->where('createtime>='.$starttime.' and createtime<='.$endtime)
                ->limit($limit)
                ->order($order)
                ->distinct(true)
                ->field("uid")
                ->select();

            foreach($rows as $k=>&$v){
                if($v['uid']==0){
                    $rows[$k]['user_name'] = '机器人';
                }
                else{
                    $user = Db::name('website_user')->where(['id'=>$v['uid']])->field('nickname,realname')->find();
                    if(!empty($user['realname'])){
                        $rows[$k]['user_name'] = $user['realname'];
                    }else{
                        $rows[$k]['user_name'] = $user['nickname'];
                    }
                }
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('date'));
        }
    }

    #用户当日的问题
    public function log_user_history(Request $request){
        $dat = input();
        $date = $dat['date'];
        $id = $dat['id'];

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $starttime = strtotime($date . ' 00:00:00');
            $endtime = strtotime($date . ' 23:59:59');

            $count = Db::name('train_qa_history')->where(['uid'=>$id,'pid'=>0])->where('createtime>='.$starttime.' and createtime<='.$endtime)->count();
            $rows = DB::name('train_qa_history')
                ->where(['uid'=>$id,'pid'=>0])
                ->where('createtime>='.$starttime.' and createtime<='.$endtime)
                ->limit($limit)
                ->order($order)
                ->distinct(true)
                ->select();

            foreach($rows as $k=>&$v){
                if($v['uid']==0){
                    $rows[$k]['user_name'] = '机器人';
                }
                else{
                    $user = Db::name('website_user')->where(['id'=>$v['uid']])->field('nickname,realname')->find();
                    if(!empty($user['realname'])){
                        $rows[$k]['user_name'] = $user['realname'];
                    }else{
                        $rows[$k]['user_name'] = $user['nickname'];
                    }
                }

                $rows[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            $website_user = Db::name('website_user')->where(['id'=>$id])->find();

            return view('',compact('date','id','website_user'));
        }
    }

    #用户当日问题的日志
    public function log_history(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isAjax()){

        }else{
            if($id>0){
                $data = Db::name('train_qa_history')->where(['id'=>$id])->find();
                $data['children'] = Db::name('train_qa_history')->where(['pid'=>$id])->select();

                foreach($data['children'] as $k=>$v){
                    $data['children'][$k]['process'] = Db::name('rag_operation_logs')->where(['answer_id'=>$v['id']])->select();
                }
            }

            return view('',compact('data','id'));
        }
    }
    #RAG模型日志管理=================================================end

    #数据库的数据表同步===============================================start
    public function data_sync_manage(Request $request){
        $dat = input();

        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count = Db::name('ai_syncdata')->count();
            $rows = DB::name('ai_syncdata')
                ->limit($limit)
                ->order($order)
                ->distinct(true)
                ->select();

            foreach($rows as $k=>&$v){
                $tables_id = explode(',',$v['tables']);
                $tables = '';
                foreach($tables_id as $v2){
                    $tablename = Db::name('ai_sync_datatable')->where(['id'=>$v2])->field('nickname')->find();
                    $tables .= $tablename['nickname'].'，';
                }
                $rows[$k]['tables'] = rtrim($tables,'，');
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }
        else{
            return view('',compact(''));
        }
    }

    public function save_data_sync(Request $request){
        $dat = input();

        if($request->isAjax()){
//            dd($dat);
            if(empty($dat['content'])){
                return json(['code'=>-1,'msg'=>'请选择需要同步的表格']);
            }

            if($dat['method']==2 && empty($dat['synctime'])){
                return json(['code'=>-1,'msg'=>'请选择同步的时间']);
            }

            $sync_id = Db::name('ai_syncdata')->insertGetId([
                'tables'=>$dat['content'],
                'method'=>$dat['method'],
                'synctime'=>strtotime($dat['synctime']),
                'status'=>0,
                'createtime'=>time()
            ]);

            if($dat['method']==1){
                #现在同步
                $res = sync_data_to_database($sync_id);
                if($res['code']==1){
                    Db::name('ai_syncdata')->where(['id'=>$sync_id])->update(['status'=>1]);
                    return json(['code'=>0,'msg'=>'同步成功']);
                }elseif($res['code']==0){
                    Db::name('ai_syncdata')->where(['id'=>$sync_id])->update(['status'=>-1]);
                    return json(['code'=>0,'msg'=>'同步失败']);
                }
            }


        }
        else{
            $tables = Db::name('ai_sync_datatable')->select();
            foreach($tables as $k=>$v){
                $tables[$k]['value'] = $v['id'];
                $tables[$k]['name'] = $v['nickname'];
                $tables[$k]['children'] = [];
            }
            $tables = json_encode($tables,true);

            return view('',compact('tables'));
        }
    }
    #数据库的数据表同步===============================================end
}