<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Session;
// 验证是否有效邮箱地址
use think\Validate;

class Index extends controller
{
    public $website_id=0;
    public $website_name='';
    public $website_keywords='';
    public $website_description='';
    public $wid=0;
    #查询当前网址在系统中的配置
    public function __construct(Request $request){
        $dat = input();
        $this->wid = $dat['wid'];

//        $domain_name = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        $website = Db::name('centralize_gather_website_list')->where(['id'=>$this->wid])->find();
        $this->website_id = $website['id'];
        $this->website_name = $website['title'];
        $this->website_keywords = $website['keywords'];
        $this->website_description = $website['description'];

        session('wid',$this->wid);
    }

    //基础访问start
    #栏目
    public function menu(){
        #栏目
        $menu = Db::name('centralize_website_menu')->where(['pid'=>0,'status'=>0,'gather_id'=>$this->website_id])->select();
        foreach($menu as $k=>$v){
            $menu[$k]['childMenu'] = $this->getDownMenu($v['id']);
        }
        return $menu;
    }
    #下级菜单
    public function getDownMenu($id){
        $cmenu = Db::name('centralize_website_menu')->where(['pid'=>$id,'gather_id'=>$this->website_id])->select();
        foreach($cmenu as $k=>$v){
            $cmenu[$k]['childMenu'] = Db::name('centralize_website_menu')->where(['pid'=>$v['id'],'gather_id'=>$this->website_id])->select();
            foreach($cmenu[$k]['childMenu'] as $k2=>$v2){
                $cmenu[$k]['childMenu'][$k2]['childMenu'] = Db::name('centralize_website_menu')->where(['pid'=>$v2['id'],'gather_id'=>$this->website_id])->select();
            }
        }
        return $cmenu;
    }
    #随机码
    public function random_str(){
        return rand(11111,99999);
    }
    #获取基础配置信息
    public function basic(){
        $basic = Db::name('centralize_website_basic')->where(['id'=>1,'gather_id'=>$this->website_id])->find();
        return $basic;
    }
    #底部导航
    public function get_footer(){
//        $services_menu_id = Db::name('centralize_website_services')->where(['gather_id'=>$this->website_id])->find();
//        $services_menu_id = explode(',',$services_menu_id['services_id']);
//        $services_menu = [];
//        foreach($services_menu_id as $k=>$v){
//            $services_menu[$k] = Db::name('centralize_website_menu')->where(['id'=>$v])->find();
//            $services_menu[$k]['menu_child'] = Db::name('centralize_website_menu')->where(['pid'=>$v,'showindex'=>1])->order('id asc')->limit(4)->select();
//        }
        $list = Db::name('centralize_website_index')->where(['gather_id'=>$this->website_id,'nav_type'=>1])->select();
        $services_menu = [];

        if(!empty($list)){
            foreach($list as $k=>$v){
                $list[$k]['menu_info'] = json_decode($v['menu_info'],true);
                if(!empty($list[$k]['menu_info'])){
                    foreach($list[$k]['menu_info'] as $k2=>$v2){
                        if($v2['footer_show']==1){
                            $services_menu[$k2] = Db::name('centralize_website_menu')->where(['id'=>$v2['menu_id']])->find();
                            $services_menu[$k2]['menu_child'] = Db::name('centralize_website_menu')->where(['pid'=>$v2['menu_id']])->order('id asc')->limit(4)->select();
                        }
                    }
                }
            }
        }

        return $services_menu;
    }
    //基础访问end

    #首页
    public function index()
    {
        #栏目
        $menu = $this->menu();
        #轮播图
        $rotate = Db::name('centralize_website_rotate')->where(['gather_id'=>$this->website_id])->select();
        #关于我们
        $aboutus_menu_id = Db::name('centralize_website_aboutus')->where(['gather_id'=>$this->website_id])->find();
        $aboutus = Db::name('centralize_website_menu')->where(['id'=>$aboutus_menu_id['menu_id']])->find();
        #我们的服务
        $services_menu = $this->get_footer();
        #基础配置
        $basic = $this->basic();
        #首页内容
        $content = Db::name('centralize_website_index')->where(['gather_id'=>$this->website_id])->order('displayorder asc')->select();
        foreach($content as $k=>$v){
            if($v['nav_type']==1){
                $content[$k]['menu_info'] = json_decode($v['menu_info'],true);
                foreach($content[$k]['menu_info'] as $kk=>$vv){
                    $content[$k]['menu_info'][$kk]['menu_name'] = Db::name('centralize_website_menu')->where(['id'=>$vv['menu_id']])->field(['title'])->find()['title'];
                }
            }elseif($v['nav_type']==2){
                $content[$k]['content_info'] = json_decode($v['content_info'],true);
            }
        }
        
        
        // dd($content);
        $title = $this->website_name;
        $keywords = $this->website_keywords;
        $description = $this->website_description;
        return view('',compact('menu','rotate','aboutus','basic','title','keywords','description','services_menu','content'));
    }


    #轮播图详情
    public function rotate_detail(Request $request){
        $dat = input();
        #栏目
        $menu = $this->menu();
        #详情
        $item = Db::name('centralize_website_rotate')->where(['id'=>intval($dat['id'])])->find();
        $item['content'] = json_decode($item['content'],true);
        $title = $item['title'];
        #我要服务
        $services = Db::name('centralize_website_menu')->where(['pid'=>64])->select();
        #我们的服务
        $services_menu = $this->get_footer();
        #随机码
        $rand = $this->random_str();
        #基础配置
        $basic = $this->basic();

//        $title = $this->website_name;
        $keywords = $this->website_keywords;
        $description = $this->website_description;
        return view('',compact('item','menu','rand','services','basic','title','keywords','description','services_menu'));
    }

    #栏目内容详情
    public function detail(Request $request){
        $dat = input();
        #栏目
        $menu = $this->menu();
        #详情
        $item = Db::name('centralize_website_menu')->where(['id'=>intval($dat['id'])])->find();
        $item['content'] = json_decode($item['content'],true);
        $title = $item['title'];
        #我要服务
        $services = Db::name('centralize_website_menu')->where(['pid'=>64])->select();
        #我们的服务
        $services_menu = $this->get_footer();
        #随机码
        $rand = $this->random_str();
        #基础配置
        $basic = $this->basic();

        $keywords = $this->website_keywords;
        $description = $this->website_description;
        return view('',compact('item','title','menu','rand','services','basic','keywords','description','services_menu'));
    }

    #轨迹查询
    public function package_tracking(Request $request){
        $dat = input();

        #栏目
        $menu = $this->menu();
        #基础配置
        $basic = $this->basic();
        #我们的服务
        $services_menu = $this->get_footer();
        $title = '包裹追踪';
        $keywords = $this->website_keywords;
        $description = $this->website_description;
        return view('',compact('title','menu','basic','keywords','description','services_menu'));
    }

    #价格查询
    public function price_search(Request $request){
        $dat = input();
        if($request->isAjax()){
//            $serverurl = "http://api.pfcexpress.com:81/";
//            $acition = "api/PriceQuery/Get/";
//            $apikey = 'aeae3d3c-bcaa-4442-8849-ec61bbf8def4125730';
//            $volumn = trim($dat['long'])*trim($dat['width'])*trim($dat['height']);
//            $res = http_get($serverurl.$acition.$dat['country_id'].'/'.trim($dat['sStages']).'/'.$volumn.'/125730', array(
//                    'Authorization: '.'Bearer '.$apikey,
//                    'Content-type: application/json'
//            ));

            if(empty($dat['country_id'])){
                return json(['code'=>-1,'msg'=>'查询失败，货物信息不能缺少！']);
            }
            
//            $product_name = explode(',',rtrim($dat['product_name'],','));
//            $num = explode(',',rtrim($dat['num'],','));
            $sStages = explode(',',rtrim($dat['sStages'],','));
            $long = explode(',',rtrim($dat['long'],','));
            $width = explode(',',rtrim($dat['width'],','));
            $height = explode(',',rtrim($dat['height'],','));

            
            foreach($sStages as $k=>$v){
                if(empty($v) ||  empty($long[$k]) || empty($width[$k]) || empty($height[$k])){
                    return json(['code'=>-1,'msg'=>'查询失败，货物信息不能缺少！']);
                }
            }
            $insid = Db::name('centralize_query_price')->insertGetId([
                'country_id'=>$dat['country_id'],
                'sStages'=>rtrim($dat['sStages'],','),
                'long'=>rtrim($dat['long'],','),
                'width'=>rtrim($dat['width'],','),
                'height'=>rtrim($dat['height'],','),
                'goods_item'=>$dat['goods_item'],
                'item_nums'=>$dat['menus'],
//                'product_name'=>rtrim($dat['product_name'],','),
//                'num'=>rtrim($dat['num'],','),
//                'shape_type'=>$dat['shape_type'],
            ]);
            return json(['code'=>0,'data'=>$insid]);
        }else{
            #栏目
            $menu = $this->menu();
            #基础配置
            $basic = $this->basic();
            #我们的服务
            $services_menu = $this->get_footer();
            #目的国家
            $country = Db::name('destination_country')->select();
            #货物属性
            $value = Db::name('centralize_value_list')->order('displayorder','asc')->select();

            $title = '价格查询';
            $keywords = $this->website_keywords;
            $description = $this->website_description;
            return view('',compact('title','menu','basic','keywords','description','services_menu','country','value'));
        }
    }

    #线路列表
    public function line_list(Request $request){
        $dat = input();
        #查询记录
        $data = Db::name('centralize_query_price')->where(['id'=>$dat['insid']])->find();

        #重量累计
        $data['sStages'] = explode(',',$data['sStages']);
        #立方厘米计算
        $data['long'] = explode(',',$data['long']);
        $data['width'] = explode(',',$data['width']);
        $data['height'] = explode(',',$data['height']);
//        $data['num'] = explode(',',$data['num']);
        $all_weight = 0;$all_area = 0;
        foreach($data['sStages'] as $k=>$v){
            $all_weight += $v;#重量累计
        }
        foreach($data['long'] as $k=>$v){
            $all_area += sprintf('%.2f',$v*$data['width'][$k]*$data['height'][$k]);#立方厘米累计
        }

        #1、获取所有线路
        $line = Db::name('centralize_line_country')->alias('a')
            ->join('centralize_line_list b','b.id=a.pid')
            ->where(['a.country_code'=>$data['country_id']])
            ->field(['a.*','b.name','b.code','b.track_website','b.detail_content','b.template_id'])
            ->order('b.id','desc')
            ->select();

        #2、每条线路进行判断
        $true_line = [];$true_weight = 0;
        foreach($line as $k=>&$v){
            $v['freight_cost'] = json_decode($v['freight_cost'],true);#运费区间计费
            $v['oversize_cost'] = json_decode($v['oversize_cost'],true);#超尺寸附加费
            $v['overweight_cost'] = json_decode($v['overweight_cost'],true);#超重量附加费
            $v['weight_strict'] = json_decode($v['weight_strict'],true);#重量限制
            $v['size_strict'] = json_decode($v['size_strict'],true);#尺寸限制
            $v['product_desc'] = json_decode($v['product_desc'],true);#支持的物品

            if($v['template_id']==1){
                #1.1、先查找该订单拥有的货物属性，centralize_goods_value
                $goods_item = Db::name('centralize_value_list')->where(' id in ('.$data['goods_item'].') ')->field(['name','id'])->select();
                #1.2、然后判断该线路是否包含该货物属性
                $is_contain = 0;
                foreach($goods_item as $k2=>$v2){
                    if(count(explode($v2['name'],$v['accept_product'])) == 1){
                        $is_contain = -1;
                    }
                }
                if($is_contain==-1){continue;}
                #1.3、查找该线路支持的物品税号是否包含查询时勾选的物品税号
                $product_desc = '';
                foreach($v['product_desc'] as $k2=>$v2){
                    if(!empty($v2)){
                        $product_desc .= rtrim($v2,'、').',';
                    }
                }
                $product_desc = rtrim($product_desc,',');
                $search_product = explode(',',rtrim($data['item_nums'],','));
                foreach($search_product as $k2=>$v2){
                    $v2 = Db::name('centralize_hscode_list')->where(['id'=>$v2])->field(['hscode'])->find()['hscode'];
                    if(strstr($product_desc,$v2) == false){
                        $is_contain = -1;
                        break;
                    }
                }
                if($is_contain==-1){continue;}

                #1.3、判断该货物重量和体积重限制
                    #重量
                if(!empty($v['weight_strict']['large_weight'])){
                    $sStages2 = &$data['sStages'];
                    $sStages = 0;
                    foreach($sStages2 as $k2=>$v2){
                        $sStages += $v2;
                    }
                    #实重>重量限制
                    if($sStages>=$v['weight_strict']['large_weight']){
                        $is_contain = -1;
                    }

                    if(!empty($v['volumn_weight'])){
                        #体积重>重量限制
                        if(sprintf('%.2f',$all_area / $v['volumn_weight'])>=$v['weight_strict']['large_weight']){
                            $is_contain = -1;
                        }
                    }
                    if($is_contain==-1){continue;}
                }
                    #体积重>体积重限制
                if(!empty($v['weight_strict']['large_volumn_weight'])){
                    if(sprintf('%.2f',$all_area / $v['volumn_weight'])>=$v['weight_strict']['large_volumn_weight']){
                        $is_contain=-1;
                    }
                    if($is_contain==-1){continue;}
                }

                #1.4、判断尺寸限制
                $long2 = &$data['long'];
                $width2 = &$data['width'];
                $height2 = &$data['height'];
                $long = 0;$width = 0;$height = 0;
                foreach($long2 as $k2=>$v2){
                    $long += $v2;
                    $width += $width2[$k2];
                    $height += $height2[$k2];
                }
                foreach($v['size_strict'] as $k2=>$v2){
                    if(!empty($v2['unilateral'])){
                        #单边限长
                        $v2['unilateral'] = explode('<',$v2['unilateral']);
                        if(!isset($v2['unilateral'][1])){
                            $v2['unilateral'] = explode('>',$v2['unilateral']);
                            if(!isset($v2['unilateral'][1])){
                                $v2['unilateral'] = explode('≤',$v2['unilateral']);
                                if(!isset($v2['unilateral'][1])){
                                    $v2['unilateral'] = explode('≥',$v2['unilateral']);
                                }
                            }
                        }

                        if($v2['unilateral'][0] == '<'){
                            if($long>=$v2['unilateral'][1] || $width>=$v2['unilateral'][1] || $height>=$v2['unilateral'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['unilateral'][0] == '>'){
                            if($long<=$v2['unilateral'][1] || $width<=$v2['unilateral'][1] || $height<=$v2['unilateral'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['unilateral'][0] == '≤'){
                            if($long>$v2['unilateral'][1] || $width>$v2['unilateral'][1] || $height>$v2['unilateral'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['unilateral'][0] == '≥'){
                            if($long<$v2['unilateral'][1] || $width<$v2['unilateral'][1] || $height<$v2['unilateral'][1]){
                                $is_contain = -1;
                            }
                        }
                    }
                    if(!empty($v2['multilateral'])){
                        #多边总和
                        $sum = $long+$width+$height;
                        $v2['multilateral'] = explode('<',$v2['multilateral']);
                        if(!isset($v2['multilateral'][1])){
                            $v2['multilateral'] = explode('>',$v2['multilateral']);
                            if(!isset($v2['multilateral'][1])){
                                $v2['multilateral'] = explode('≤',$v2['multilateral']);
                                if(!isset($v2['multilateral'][1])){
                                    $v2['multilateral'] = explode('≥',$v2['multilateral']);
                                }
                            }
                        }

                        if($v2['multilateral'][0] == '<'){
                            if($sum>=$v2['multilateral'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['multilateral'][0] == '>'){
                            if($sum<=$v2['multilateral'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['multilateral'][0] == '≤'){
                            if($sum>$v2['multilateral'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['multilateral'][0] == '≥'){
                            if($sum<$v2['multilateral'][1]){
                                $is_contain = -1;
                            }
                        }
                    }
                    if(!empty($v2['multilateral_mul'])){
                        #多边总乘
                        $mul = sprintf('%.2f',$long*$width*$height);
                        $v2['multilateral_mul'] = explode('<',$v2['multilateral_mul']);
                        if(!isset($v2['multilateral_mul'][1])){
                            $v2['multilateral_mul'] = explode('>',$v2['multilateral_mul']);
                            if(!isset($v2['multilateral_mul'][1])){
                                $v2['multilateral_mul'] = explode('≤',$v2['multilateral_mul']);
                                if(!isset($v2['multilateral_mul'][1])){
                                    $v2['multilateral_mul'] = explode('≥',$v2['multilateral_mul']);
                                }
                            }
                        }

                        if($v2['multilateral_mul'][0] == '<'){
                            if($mul>=$v2['multilateral_mul'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['multilateral_mul'][0] == '>'){
                            if($mul<=$v2['multilateral_mul'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['multilateral_mul'][0] == '≤'){
                            if($mul>$v2['multilateral_mul'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['multilateral_mul'][0] == '≥'){
                            if($mul<$v2['multilateral_mul'][1]){
                                $is_contain = -1;
                            }
                        }
                    }
                    if(!empty($v2['limit1'])){
                        #直径的两倍加上长度之和
                        $sum = sprintf('%.2f',2*$width)+$long;
                        $v2['limit1'] = explode('<',$v2['limit1']);
                        if(!isset($v2['limit1'][1])){
                            $v2['limit1'] = explode('>',$v2['limit1']);
                            if(!isset($v2['limit1'][1])){
                                $v2['limit1'] = explode('≤',$v2['limit1']);
                                if(!isset($v2['limit1'][1])){
                                    $v2['limit1'] = explode('≥',$v2['limit1']);
                                }
                            }
                        }

                        if($v2['limit1'][0] == '<'){
                            if($sum>=$v2['limit1'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['limit1'][0] == '>'){
                            if($sum<=$v2['limit1'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['limit1'][0] == '≤'){
                            if($sum>$v2['limit1'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['limit1'][0] == '≥'){
                            if($sum<$v2['limit1'][1]){
                                $is_contain = -1;
                            }
                        }
                    }
                    if(!empty($v2['limit2'])){
                        #直径的两倍加上长度之和
                        $sum = sprintf('%.2f',($long+2)*($width+2)*$height);
                        $v2['limit2'] = explode('<',$v2['limit2']);
                        if(!isset($v2['limit2'][1])){
                            $v2['limit2'] = explode('>',$v2['limit2']);
                            if(!isset($v2['limit2'][1])){
                                $v2['limit2'] = explode('≤',$v2['limit2']);
                                if(!isset($v2['limit2'][1])){
                                    $v2['limit2'] = explode('≥',$v2['limit2']);
                                }
                            }
                        }

                        if($v2['limit2'][0] == '<'){
                            if($sum>=$v2['limit2'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['limit2'][0] == '>'){
                            if($sum<=$v2['limit2'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['limit2'][0] == '≤'){
                            if($sum>$v2['limit2'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['limit2'][0] == '≥'){
                            if($sum<$v2['limit2'][1]){
                                $is_contain = -1;
                            }
                        }
                    }
                    if(!empty($v2['long'])){
                        #长度
                        $v2['long'] = explode('<',$v2['long']);
                        if(!isset($v2['long'][1])){
                            $v2['long'] = explode('>',$v2['long']);
                            if(!isset($v2['long'][1])){
                                $v2['long'] = explode('≤',$v2['long']);
                                if(!isset($v2['long'][1])){
                                    $v2['long'] = explode('≥',$v2['long']);
                                }
                            }
                        }

                        if($v2['long'][0] == '<'){
                            if($long>=$v2['long'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['long'][0] == '>'){
                            if($long<=$v2['long'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['long'][0] == '≤'){
                            if($long>$v2['long'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['long'][0] == '≥'){
                            if($long<$v2['long'][1]){
                                $is_contain = -1;
                            }
                        }
                    }
                    if(!empty($v2['width'])){
                        #宽度
                        $v2['width'] = explode('<',$v2['width']);
                        if(!isset($v2['width'][1])){
                            $v2['width'] = explode('>',$v2['width']);
                            if(!isset($v2['width'][1])){
                                $v2['width'] = explode('≤',$v2['width']);
                                if(!isset($v2['width'][1])){
                                    $v2['width'] = explode('≥',$v2['width']);
                                }
                            }
                        }

                        if($v2['width'][0] == '<'){
                            if($width>=$v2['width'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['width'][0] == '>'){
                            if($width<=$v2['width'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['width'][0] == '≤'){
                            if($width>$v2['width'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['width'][0] == '≥'){
                            if($width<$v2['width'][1]){
                                $is_contain = -1;
                            }
                        }
                    }
                    if(!empty($v2['height'])){
                        #高度
                        $v2['height'] = explode('<',$v2['height']);
                        if(!isset($v2['height'][1])){
                            $v2['height'] = explode('>',$v2['height']);
                            if(!isset($v2['height'][1])){
                                $v2['height'] = explode('≤',$v2['height']);
                                if(!isset($v2['height'][1])){
                                    $v2['height'] = explode('≥',$v2['height']);
                                }
                            }
                        }

                        if($v2['height'][0] == '<'){
                            if($height>=$v2['height'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['height'][0] == '>'){
                            if($height<=$v2['height'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['height'][0] == '≤'){
                            if($height>$v2['height'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['height'][0] == '≥'){
                            if($height<$v2['height'][1]){
                                $is_contain = -1;
                            }
                        }
                    }
                    if(!empty($v2['limit3'])){
                        #长+(宽+高)*2
                        $sum = sprintf('%.2f',$long+($width+$height)*2);
                        $v2['limit3'] = explode('<',$v2['limit3']);
                        if(!isset($v2['limit3'][1])){
                            $v2['limit3'] = explode('>',$v2['limit3']);
                            if(!isset($v2['limit3'][1])){
                                $v2['limit3'] = explode('≤',$v2['limit3']);
                                if(!isset($v2['limit3'][1])){
                                    $v2['limit3'] = explode('≥',$v2['limit3']);
                                }
                            }
                        }

                        if($v2['limit3'][0] == '<'){
                            if($sum>=$v2['limit3'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['limit3'][0] == '>'){
                            if($sum<=$v2['limit3'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['limit3'][0] == '≤'){
                            if($sum>$v2['limit3'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['limit3'][0] == '≥'){
                            if($sum<$v2['limit3'][1]){
                                $is_contain = -1;
                            }
                        }
                    }
                    if(!empty($v2['limit4'])){
                        #表面尺码总乘
                        $sum = sprintf('%.2f',$long*$width);
                        $v2['limit4'] = explode('<',$v2['limit4']);
                        if(!isset($v2['limit4'][1])){
                            $v2['limit4'] = explode('>',$v2['limit4']);
                            if(!isset($v2['limit4'][1])){
                                $v2['limit4'] = explode('≤',$v2['limit4']);
                                if(!isset($v2['limit4'][1])){
                                    $v2['limit4'] = explode('≥',$v2['limit4']);
                                }
                            }
                        }

                        if($v2['limit4'][0] == '<'){
                            if($sum>=$v2['limit4'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['limit4'][0] == '>'){
                            if($sum<=$v2['limit4'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['limit4'][0] == '≤'){
                            if($sum>$v2['limit4'][1]){
                                $is_contain = -1;
                            }
                        }elseif($v2['limit4'][0] == '≥'){
                            if($sum<$v2['limit4'][1]){
                                $is_contain = -1;
                            }
                        }
                    }
                }
                if($is_contain==-1){continue;}

                #1.5、获取体积重
                $all_volumn_weight = sprintf('%.2f',$all_area / $v['volumn_weight']);#体积重
                $line[$k]['volumn_weight'] = $all_volumn_weight;

                #1.6、重量<体积重，以体积重为计算方式
                if($all_weight<$all_volumn_weight){
                    $true_weight = $all_weight;#计费（真实）重量
                    $all_weight = $all_volumn_weight;#体积重量
                }

                #1.7、计算运费
                $v['total_money'] = $this->calc_freight($v['freight_cost'],$all_weight,$v['template_id']);

                if($true_weight!=0){
                    $all_weight=$true_weight;
                }
                $v['cost_weight'] = $all_weight;
                $true_line = array_merge($true_line,[$v]);
            }
            elseif($v['template_id']==2){
                #专线模板2
                #1.1、先查找该订单拥有的货物属性，centralize_goods_value
                $goods_item = Db::name('centralize_value_list')->where(' id in ('.$data['goods_item'].') ')->field(['name','id'])->select();
                #1.2、然后判断该线路是否包含该货物属性
                $is_contain = 0;
                foreach($goods_item as $k2=>$v2){
                    if(count(explode($v2['name'],$v['accept_product'])) == 1){
                        $is_contain = -1;
                    }
                }
                if($is_contain==-1){
                    continue;
                }
                #1.3、查找该线路支持的物品税号是否包含查询时勾选的物品税号
                $product_desc = '';
                foreach($v['product_desc'] as $k2=>$v2){
                    if(!empty($v2)){
                        $product_desc .= rtrim($v2,'、').',';
                    }
                }
                $product_desc = rtrim($product_desc,',');
                $search_product = explode(',',rtrim($data['item_nums'],','));
                foreach($search_product as $k2=>$v2){
                    $v2 = Db::name('centralize_hscode_list')->where(['id'=>$v2])->field(['hscode'])->find()['hscode'];
                    if(strstr($product_desc,$v2) == false){
                        $is_contain = -1;
                        break;
                    }
                }
                if($is_contain==-1){
                    continue;
                }

                #2、计算体积是否大于1m³
                $cubic_calc = $all_area/1000000;
                if($cubic_calc<1){
                    $is_contain = -1;
                }
                if($is_contain==-1){
                    continue;
                }

                #计算运费
                $v['total_money'] = $this->calc_freight($v['freight_cost'],$cubic_calc,$v['template_id']);
                $true_line = array_merge($true_line,[$v]);
            }
        }

        if($true_weight!=0){
            $all_weight=$true_weight;
        }
        $sort = array_column($true_line,'total_money');
        array_multisort($sort,SORT_ASC,$true_line);
        
        $title = '价格列表';
        #栏目
        $menu = $this->menu();
        #基础配置
        $basic = $this->basic();
        #我们的服务
        $services_menu = $this->get_footer();
        #目的国地1
        $country = Db::name('destination_country')->select();
        $keywords = $this->website_keywords;
        $description = $this->website_description;

        return view('',compact('title','true_line','keywords','description','menu','basic','services_menu','country','data','all_weight','all_area'));
    }

    #线路详情
    public function line_detail(Request $request){
        $dat = input();

        #线路
        $pid = Db::name('centralize_line_country')->where(['id'=>$dat['id']])->find();
        $line = Db::name('centralize_line_list')->where(['id'=>$pid['pid']])->find();
        $line['detail_content'] = json_decode($line['detail_content'],true);

        #预估信息
        $info = Db::name('centralize_query_price')->where(['id'=>$dat['insid']])->find();
        $line['country_info'] = Db::name('centralize_line_country')->where(['pid'=>$line['id'],'id'=>$dat['id'],'country_code'=>$info['country_id']])->find();
        $line['country_info']['freight_cost'] = json_decode($line['country_info']['freight_cost'],true);
        $line['country_info']['weight_strict'] = json_decode($line['country_info']['weight_strict'],true);
        #限制条件
        $line['country_info']['size_strict'] = json_decode($line['country_info']['size_strict'],true);
        $line['country_info']['proname_strict'] = json_decode($line['country_info']['proname_strict'],true);
        if(!empty($line['country_info']['proname_strict']['exceed'])){
            $line['country_info']['proname_strict']['exceed'] = explode('/',$line['country_info']['proname_strict']['exceed']);
        }
        $line['country_info']['declare_strict'] = json_decode($line['country_info']['declare_strict'],true);
        #附加费条件
        $line['country_info']['oversize_cost'] = json_decode($line['country_info']['oversize_cost'],true);
        $line['country_info']['overweight_cost'] = json_decode($line['country_info']['overweight_cost'],true);
        $line['country_info']['sundry_fees'] = json_decode($line['country_info']['sundry_fees'],true);

        $line['accept_product'] = &$line['country_info']['accept_product'];
        $goods_value = Db::name('centralize_value_list')->select();

        // foreach($line['accept_product'] as $k2=>$v2){
            foreach($goods_value as $k=>$v){
                if($line['accept_product']==$v['name']){
                    $goods_value[$k]['is_selected'] = 1;
                }
            }
        // }
        #重量累计
        $info['sStages'] = explode(',',$info['sStages']);
        #立方厘米计算
        $info['long'] = explode(',',$info['long']);
        $info['width'] = explode(',',$info['width']);
        $info['height'] = explode(',',$info['height']);
        $info['num'] = explode(',',$info['num']);
        $all_weight = 0;$all_area = 0;$all_volumn_weight=0;
        foreach($info['sStages'] as $k=>$v){
            $all_weight += $v;
        }
        foreach($info['long'] as $k=>$v){
            $all_area += sprintf('%.2f',$v*$info['width'][$k]*$info['height'][$k]);
        }
        if($line['template_id']==11){
            $total_money = ceil($all_weight)*$line['country_info']['freight_cost']['base']+$line['country_info']['freight_cost']['opera'];
        }
        elseif($line['template_id']==1){
            $all_volumn_weight = sprintf('%.2f',$all_area / $line['country_info']['volumn_weight']);#体积重
            $true_weight = 0;
            if($all_weight<$all_volumn_weight){
                $true_weight = $all_weight;
                $all_weight = $all_volumn_weight;
            }

            $total_money = $this->calc_freight($line['country_info']['freight_cost'],$all_weight,$line['template_id']);

            if($true_weight!=0){
                $all_weight=$true_weight;
            }
        }
        elseif($line['template_id']==2){
            #专线2
            $cubic_calc = $all_area/1000000;
            $total_money = $this->calc_freight($line['country_info']['freight_cost'],$cubic_calc,$line['template_id']);
        }

        $title = '线路详情';
        #栏目
        $menu = $this->menu();
        #基础配置
        $basic = $this->basic();
        #我们的服务
        $services_menu = $this->get_footer();
        #目的国家
        $country = Db::name('destination_country')->select();
        #货物属性
        $value = Db::name('centralize_goods_value')->select();
        $keywords = $this->website_keywords;
        $description = $this->website_description;
//        $goods_item = Db::name('centralize_value_list')->where(' name in (\''.$line['accept_product'].'\') ')->select();
//        foreach($goods_item as $k=>$v){
//            $goods_item[$k]['desc'] = json_decode($v['desc'],true);
//            $goods_item[$k]['names'] = json_decode($v['names'],true);
//        }

        return view('',compact('title','menu','basic','keywords','description','services_menu','country','value','info','line','all_weight','all_area','total_money','all_volumn_weight','goods_item','goods_value'));
    }

    #计算运费
    public function calc_freight($freight_cost,$all_weight,$template_id){
        $total_money = 0;
        if($template_id==1){
            #kg
            if($freight_cost['first_cost']['weight_interval']['sml']<=$all_weight && $freight_cost['first_cost']['weight_interval']['big']>=$all_weight){
                if($freight_cost['first_cost']['pre_weight']==0.1){
                    if(preg_match("/^[0-9]+(.[0-9]{2})?$/",$all_weight)){
                        #判断是否有2位小数，并保留1位小数点。例如：1.25->1.3
                        $all_weight2 = number_format($all_weight,1);
                        if($all_weight==$all_weight2){
                            $total_money = sprintf('%.2f',$freight_cost['first_cost']['first_weight']+(($all_weight-0.1)/0.1)*$freight_cost['first_cost']['continue_weight']);
                        }elseif($all_weight>$all_weight2){
                            $total_money = sprintf('%.2f',$freight_cost['first_cost']['first_weight']+(($all_weight-0.1)/0.1)*$freight_cost['first_cost']['continue_weight'])+$freight_cost['first_cost']['continue_weight'];
                        }else{
                            $total_money = sprintf('%.2f',$freight_cost['first_cost']['first_weight']+(($all_weight2-0.1)/0.1)*$freight_cost['first_cost']['continue_weight']);
                        }
                    }else{
                        $total_money = sprintf('%.2f',$freight_cost['first_cost']['first_weight']+(($all_weight-0.1)/0.1)*$freight_cost['first_cost']['continue_weight']);
                    }
                }elseif($freight_cost['first_cost']['pre_weight']==0.5){
                    $calc = $all_weight/0.5;
                    if(strpos($calc,'.') == ''){
                        #是整数
                        $total_money = sprintf('%.2f',$freight_cost['first_cost']['first_weight']+(($all_weight-0.5)/0.5)*$freight_cost['first_cost']['continue_weight']);
                    }else{
                        $total_money = sprintf('%.2f',$freight_cost['first_cost']['first_weight']+(((ceil($calc)/2)-0.5)/0.5)*$freight_cost['first_cost']['continue_weight']);
                    }
                }elseif($freight_cost['first_cost']['pre_weight']==1){
                    $total_money = sprintf('%.2f',$freight_cost['first_cost']['first_weight']+(ceil($all_weight)-1)*$freight_cost['first_cost']['continue_weight']);
                }
            }
            elseif($freight_cost['second_cost']['weight_interval']['sml']<=$all_weight && $freight_cost['second_cost']['weight_interval']['big']>=$all_weight){
                if($freight_cost['second_cost']['pre_weight']==0.1){
                    if(preg_match("/^[0-9]+(.[0-9]{2})?$/",$all_weight)){
                        $all_weight2 = number_format($all_weight,1);
                        if($all_weight==$all_weight2){
                            $total_money = sprintf('%.2f',($all_weight/0.1)*$freight_cost['second_cost']['continue_weight']);
                        }elseif($all_weight>$all_weight2){
                            $total_money = sprintf('%.2f',($all_weight/0.1)*$freight_cost['second_cost']['continue_weight'])+$freight_cost['second_cost']['continue_weight'];
                        }else{
                            $total_money = sprintf('%.2f',($all_weight2/0.1)*$freight_cost['second_cost']['continue_weight']);
                        }
                    }else{
                        $v['total_money'] = sprintf('%.2f',($all_weight/0.1)*$freight_cost['second_cost']['continue_weight']);
                    }
                }elseif($freight_cost['second_cost']['pre_weight']==0.5){
                    $calc = $all_weight/0.5;
                    if(strpos($calc,'.') == ''){
                        #是整数
                        $total_money = sprintf('%.2f',(($all_weight-0.5)/0.5)*$freight_cost['second_cost']['continue_weight']);
                    }else{
                        $total_money = sprintf('%.2f',(((ceil($calc)/2)-0.5)/0.5)*$freight_cost['second_cost']['continue_weight']);
                    }
                }elseif($freight_cost['second_cost']['pre_weight']==1){
                    $total_money = sprintf('%.2f',ceil($all_weight)*$freight_cost['second_cost']['continue_weight']);
                }
            }
            elseif($freight_cost['third_cost']['weight_interval']['sml']<=$all_weight && ($freight_cost['third_cost']['weight_interval']['big']>=$all_weight || empty($freight_cost['third_cost']['weight_interval']['big']))){
                if($freight_cost['third_cost']['pre_weight']==0.1){
                    if(preg_match("/^[0-9]+(.[0-9]{2})?$/",$all_weight)){
                        $all_weight2 = number_format($all_weight,1);
                        if($all_weight==$all_weight2){
                            $total_money = sprintf('%.2f',($all_weight/0.1)*$freight_cost['third_cost']['continue_weight']);
                        }elseif($all_weight>$all_weight2){
                            $total_money = sprintf('%.2f',($all_weight/0.1)*$freight_cost['third_cost']['continue_weight'])+$freight_cost['third_cost']['continue_weight'];
                        }else{
                            $total_money = sprintf('%.2f',($all_weight2/0.1)*$freight_cost['third_cost']['continue_weight']);
                        }
                    }else{
                        $total_money = sprintf('%.2f',($all_weight/0.1)*$freight_cost['third_cost']['continue_weight']);
                    }
                }elseif($freight_cost['third_cost']['pre_weight']==0.5){
                    $calc = $all_weight/0.5;
                    if(strpos($calc,'.') == ''){
                        #是整数
                        $total_money = sprintf('%.2f',(($all_weight-0.5)/0.5)*$freight_cost['third_cost']['continue_weight']);
                    }else{
                        $total_money = sprintf('%.2f',(((ceil($calc)/2)-0.5)/0.5)*$freight_cost['third_cost']['continue_weight']);
                    }
                }elseif($freight_cost['third_cost']['pre_weight']==1){
                    $total_money = sprintf('%.2f',ceil($all_weight)*$freight_cost['third_cost']['continue_weight']);
                }
            }
            foreach($freight_cost as $k=>$v){
                if($k!='first_cost' && $k!='second_cost' && $k!='third_cost'){
                    if($freight_cost[$k]['weight_interval']['sml']<=$all_weight && ($freight_cost[$k]['weight_interval']['big']>=$all_weight || empty($freight_cost[$k]['weight_interval']['big']))){
                        if($freight_cost[$k]['pre_weight']==0.1){
                            if(preg_match("/^[0-9]+(.[0-9]{2})?$/",$all_weight)){
                                $all_weight2 = number_format($all_weight,1);
                                if($all_weight==$all_weight2){
                                    $total_money = sprintf('%.2f',($all_weight/0.1)*$freight_cost[$k]['continue_weight']);
                                }elseif($all_weight>$all_weight2){
                                    $total_money = sprintf('%.2f',($all_weight/0.1)*$freight_cost[$k]['continue_weight'])+$freight_cost[$k]['continue_weight'];
                                }else{
                                    $total_money = sprintf('%.2f',($all_weight2/0.1)*$freight_cost[$k]['continue_weight']);
                                }
                            }else{
                                $v['total_money'] = sprintf('%.2f',($all_weight/0.1)*$freight_cost[$k]['continue_weight']);
                            }
                        }elseif($freight_cost['second_cost']['pre_weight']==0.5){
                            $calc = $all_weight/0.5;
                            if(strpos($calc,'.') == ''){
                                #是整数
                                $total_money = sprintf('%.2f',(($all_weight-0.5)/0.5)*$freight_cost[$k]['continue_weight']);
                            }else{
                                $total_money = sprintf('%.2f',(((ceil($calc)/2)-0.5)/0.5)*$freight_cost[$k]['continue_weight']);
                            }
                        }elseif($freight_cost['second_cost']['pre_weight']==1){
                            $total_money = sprintf('%.2f',ceil($all_weight)*$freight_cost[$k]['continue_weight']);
                        }
                    }
                }
            }
        }
        elseif($template_id==2){
            #cbm
            if($all_weight>=$freight_cost['first_cost']['cubic_interval']['sml'] && ($all_weight<=$freight_cost['first_cost']['cubic_interval']['big'] || empty($freight_cost['first_cost']['cubic_interval']['big']))){
                if($freight_cost['first_cost']['pre_cubic']==1){
                    $total_money = sprintf('%.2f',$freight_cost['first_cost']['first_cubic']+((ceil($all_weight)-1)/1)*$freight_cost['first_cost']['first_cubic']);
                }
            }elseif($all_weight>=$freight_cost['second_cost']['cubic_interval']['sml'] && ($all_weight<=$freight_cost['second_cost']['cubic_interval']['big'] || empty($freight_cost['second_cost']['cubic_interval']['big']))){
                if($freight_cost['second_cost']['pre_cubic']==1){
                    $total_money = sprintf('%.2f',$freight_cost['second_cost']['continue_cubic']+((ceil($all_weight)-1)/1)*$freight_cost['second_cost']['continue_cubic']);
                }
            }elseif($all_weight>=$freight_cost['third_cost']['cubic_interval']['sml'] && ($all_weight<=$freight_cost['third_cost']['cubic_interval']['big'] || empty($freight_cost['third_cost']['cubic_interval']['big']))){
                if($freight_cost['third_cost']['pre_cubic']==1){
                    $total_money = sprintf('%.2f',$freight_cost['third_cost']['continue_cubic']+((ceil($all_weight)-1)/1)*$freight_cost['second_cost']['continue_cubic']);
                }
            }
            foreach($freight_cost as $k=>$v){
                if($k!='first_cost' && $k!='second_cost' && $k!='third_cost'){
                    if($freight_cost[$k]['cubic_interval']['sml']<=$all_weight && ($freight_cost[$k]['cubic_interval']['big']>=$all_weight || empty($freight_cost[$k]['cubic_interval']['big']))){
                        if($freight_cost[$k]['pre_cubic']==1){
                            $total_money = sprintf('%.2f',$freight_cost[$k]['continue_cubic']+((ceil($all_weight)-1)/1)*$freight_cost[$k]['continue_cubic']);
                        }
                    }
                }
            }
        }


        return $total_money;
    }

    #hscode
    public function hscode(Request $request){
        #物品行邮
        $dat = input();
        $val_list = Db::name('centralize_value_list')->where(['id'=>$dat['val_id']])->find();
        $level_menus = ",".$val_list['hscodes'];

        $levelData = Db::name('centralize_hscode_list')->where(['pid'=>0])->select();

        $newList = [];
        foreach ($levelData as $k=>$v){
            $levelData[$k]['child'] = Db::name('centralize_hscode_list')->where(['pid'=>$v['id']])->select();
            if(strpos($level_menus,",".$v['id'].",") !== false){
                array_push($newList, ['id' => $v['id'], 'pId' => $v['pid'], 'title' => $v['name'] ,'checked'=>'','checked2' => strpos($level_menus,",".$v['id'].",") !== false ? true : false ]);
            }
            if(!empty($levelData[$k]['child'])){
                foreach($levelData[$k]['child'] as $k2=>$v2){
                    $levelData[$k]['child'][$k2]['child'] = Db::name('centralize_hscode_list')->where(['pid'=>$v2['id']])->select();
                    if(strpos($level_menus,",".$v2['id'].",") !== false) {
                        array_push($newList, ['id' => $v2['id'], 'pId' => $v2['pid'], 'title' => $v2['name'],'checked'=>'', 'checked2' => strpos($level_menus, "," . $v2['id'] . ",") !== false ? true : false]);
                    }
                    if(!empty($levelData[$k]['child'][$k2]['child'])){
                        foreach($levelData[$k]['child'][$k2]['child'] as $k3=>$v3){
                            $levelData[$k]['child'][$k2]['child'][$k3]['child'] = Db::name('centralize_hscode_list')->where(['pid'=>$v3['id']])->select();
                            if(strpos($level_menus,",".$v3['id'].",") !== false) {
                                array_push($newList, ['id' => $v3['id'], 'pId' => $v3['pid'], 'title' => $v3['name'],'checked'=>'', 'checked2' => strpos($level_menus, "," . $v3['id'] . ",") !== false ? true : false]);
                            }
                            if(!empty($levelData[$k]['child'][$k2]['child'][$k3]['child'])){
                                foreach($levelData[$k]['child'][$k2]['child'][$k3]['child'] as $k4=>$v4){
                                    $levelData[$k]['child'][$k2]['child'][$k3]['child'][$k4]['child'] = Db::name('centralize_hscode_list')->where(['pid'=>$v4['id']])->select();
                                    if(strpos($level_menus,",".$v4['id'].",") !== false) {
                                        array_push($newList, ['id' => $v4['id'], 'pId' => $v4['pid'], 'title' => $v4['name'],'checked'=>'', 'checked2' => strpos($level_menus, "," . $v4['id'] . ",") !== false ? true : false]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $data['menuList'] = $newList;
        return json(['code'=>0,'data'=>$data]);
    }

    #留下联系方式
    public function leave_contact(Request $request){
        $dat = input();
        if(empty($dat['tel']) && empty($dat['email'])){
            return json(['code'=>-1,'msg'=>'提交失败，联系方式要选填其中一个']);
        }
        if(!empty($dat['tel'])){
            $is_have = Db::name('centralize_contact_list')->where(['tel'=>$dat['tel']])->find();
            if(!empty($is_have['id'])){
                return json(['code'=>-1,'msg'=>'提交失败，您已提交过联系信息']);
            }
        }
        if(!empty($dat['email'])) {
            $is_have = Db::name('centralize_contact_list')->where(['email' => $dat['email']])->find();
            if (!empty($is_have['id'])) {
                return json(['code' => -1, 'msg' => '提交失败，您已提交过联系信息']);
            }
        }
        $res = Db::name('centralize_contact_list')->insert([
            'wid'=>$dat['wid'],
            'name'=>$dat['name'],
            'tel'=>$dat['tel'],
            'email'=>$dat['email'],
            'createtime'=>time()
        ]);
        if($res){
            return json(['code'=>0,'msg'=>'提交成功，客服人员会在工作日7天内联系您。']);
        }
    }

    #价格详情（废弃）
    public function price_detail(Request $request){
        $dat = input();

//        $con = Db::name('shipping_channel')->where(['ChannelCode'=>$dat['code']])->field(['content','CnName'])->find();
        $con = Db::name('centralize_line_list')->where(['id'=>$dat['code']])->field(['detail_content','name'])->find();
        $con['detail_content'] = json_decode($con['detail_content'],true);

        return json(['code'=>0,'data'=>$con]);
    }

    #属性介绍
    public function introduce(Request $request){
        $dat = input();
//        centralize_goods_value
        $value = Db::name('centralize_value_list')->where(['id'=>$dat['code'],'type'=>1])->find();
        $value['desc'] = json_decode($value['desc'],true);
        $value['names'] = json_decode($value['names'],true);
        return json(['code'=>0,'data'=>$value]);
    }

    #建议
    public function advice(Request $request){
        $dat = input();

        if($request->isAjax()){
            if(empty(session('wid'))){
                return json(['code'=>-1,'msg'=>'非法请求']);
            }
            if(empty($dat['name']) || empty($dat['email']) || empty($dat['content'])){
                return json(['code'=>-1,'msg'=>'请输入信息']);
            }

            if(!preg_match('/([\w\-]+\@[\w\-]+\.[\w\-]+)/',$dat['email'])){
                return json(['code'=>-1,'msg'=>'请输入正确的邮箱']);
            }

            if($dat['code_origin'] != trim($dat['code_input'])){
                return json(['code'=>-1,'msg'=>'验证码不正确']);
            }

            $res = Db::name('centralize_website_advice')->insert([
                'gather_id'=>intval($dat['wid']),
                'name'=>trim($dat['name']),
                'email'=>$dat['email'],
                'content'=>$dat['content'],
                'createtime'=>time(),
            ]);
            if($res){
                return json(['code'=>0,'msg'=>'提交成功，稍后客服专员会在邮箱通知您！']);
            }
        }
    }
}
