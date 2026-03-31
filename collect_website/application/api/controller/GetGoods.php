<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\Exception;
use think\Request;
use think\Log;
use ip2region\XdbSearcher;

header('Access-Control-Allow-Origin: *'); //设置http://www.baidu.com允许跨域访问
header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); //设置允许的跨域header
header('Content-Type: application/json;charset=utf-8');

#星赋创达（BuckyDrop）的接口
class GetGoods extends Controller
{

//    public $url = 'https://uat.buckydrop.com/';
//    public $appCode = 'd23688a5f3d88baa11be90c70ddfb74a';
//    public $appSecret = 'b84d4e6a54f793f1775b546b78fdadb9';
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

    public $url = 'https://bdopenapi.buckydrop.com/';
    public $appCode = 'a794b249faae31e009e78cd3cdb3f6d8';
    public $appSecret = '096c561a3b7ba03caaaa6065eef60be3';

    public function get_sign($data){
        return MD5($this->appCode.$data['jsonParams'].$data['timestamp'].$this->appSecret);//MD5(appCode + jsonParams + timestamp + appSecret)
    }

    #通过关键字查询商品（品牌、分类、名称）
    public function keyword_query(Request $request){
        $dat = input();
        $current = isset($dat['current'])?intval($dat['current']):1;
        $size = isset($dat['size'])?intval($dat['size']):20;
        #时间戳
        $timestamp = time();
        #请求数据
        $data = '{"current":'.$current.',"size":'.$size.',"item":{"keyword":"'.$dat['keyword'].'"}}';
        #获取签名
        $sign = $this->get_sign(['jsonParams'=>$data,'timestamp'=>$timestamp]);
        #开始请求
        $url2 = 'api/rest/v2/adapt/openapi/product/search';
        $newurl = $this->url.$url2.'?timestamp='.$timestamp.'&appCode='.$this->appCode.'&sign='.$sign;
        $res = httpRequest3($newurl,$data);
        $res = json_decode($res,true);
        if($res['success']==1){
            return json(['data'=>$res['data']['records'],'code'=>0]);
        }else{
            return json(['data'=>json_encode($res,true),'code'=>-1]);
        }
    }

    #通过商品id查询商品详情
    public function detail_query(Request $request){
        $dat = input();
        #时间戳
        $timestamp = time();
        #请求数据
        $data = '';
        if($dat['type']==1){
            $data = '{"productLink":"https://item.taobao.com/item.htm?id='.$dat['good_id'].'","lang":1}';
        }elseif($dat['type']==2){
            $data = '{"productLink":"'.$dat['goodsLink'].'","lang":1}';
        }
        #获取签名
        $sign = $this->get_sign(['jsonParams'=>$data,'timestamp'=>$timestamp]);
        $url2 = 'api/rest/v2/adapt/openapi/product/detail?timestamp='.$timestamp.'&appCode='.$this->appCode.'&sign='.$sign;
        $newurl = $this->url.$url2;
        $res = httpRequest3($newurl,$data);
        $res = json_decode($res,true);

        if($res['success']==1){
            return json(['data'=>$res['data'],'code'=>0]);
        }else{
            return json(['data'=>$res['info'],'code'=>-1]);
        }
    }

    #通过商品id和sku信息创建订单
    public function create_order(Request $request){
        $dat = input();

        $address = [];
        $address_id = isset($dat['address_id'])?intval($dat['address_id']):0;
        if(empty($address_id)){
            //平台集运，用代发仓库地址
            $address['country'] = '中国';
            $address['countryCode'] = 'CN';
            $address['province'] = '广东省';
            $address['city'] = '佛山市';
            $address['detailAddress'] = '南海区桂城南三路11号广东珠江开关有限公司内3号楼D611室';
            $address['postCode'] = '528251';
            $address['contactName'] = '区广祺';
            $address['contactPhone'] = '13809703680';
            $address['email'] = '198@gogo198.net';
        }else{
            //自主集运，寄往用户收货地址
            $adr = Db::name('centralize_user_address')->where(['id'=>$address_id])->find();
            $country = Db::name('centralize_diycountry_content')->where(['id'=>$adr['country_id']])->field('param2,param5')->find();
            $province = Db::name('centralize_country_areas')->where(['id'=>$adr['province']])->value('name');
            $city = Db::name('centralize_country_areas')->where(['id'=>$adr['city']])->value('name');

            $address['country'] = $country['param2'];
            $address['countryCode'] = $country['param5'];
            $address['province'] = $province;
            $address['city'] = $city;
            $address['detailAddress'] = $adr['address1'];
            $address['postCode'] = $adr['postal'];
            $address['contactName'] = $adr['user_name'];
            $address['contactPhone'] = $adr['mobile'];
            $address['email'] = $adr['email'];
        }

        #时间戳
        $timestamp = time();
        #请求数据
        $data = '{"partnerOrderNo":"'.$dat['ordersn'].'","partnerOrderNoName":"#'.$dat['ordersn'].'","orderTime":'.$dat['createtime'].',"country":"'.$address['country'].'","countryCode":"'.$address['countryCode'].'","province":"'.$address['province'].'","city":"'.$address['city'].'","detailAddress":"'.$address['detailAddress'].'","postCode":"'.$address['postCode'].'","contactName":"'.$address['contactName'].'","contactPhone":"'.$address['contactPhone'].'","email":"'.$address['email'].'","orderRemark":"","productList":'.$dat['productList'].'}';

        #获取签名
        $sign = $this->get_sign(['jsonParams'=>$data,'timestamp'=>$timestamp]);
        $url2 = 'api/rest/v2/adapt/adaptation/order/shop-order/create?timestamp='.$timestamp.'&appCode='.$this->appCode.'&sign='.$sign;
        $newurl = $this->url.$url2;
        $res = httpRequest3($newurl,$data);
        $res = json_decode($res,true);
        if($res['success']==1){
            return json(['data'=>$res['data'],'code'=>0]);
        }else{
            return json(['data'=>'','code'=>-1]);
        }
    }

    #取消商铺订单（下采购单时发现无货或余额不足时调用）//取消整单，生成采购单后，就取消不了（废弃）
    public function cancel_shop_order(Request $request){
        $dat = input();
        #时间戳
        $timestamp = time();
        #请求数据
        $data = '{"partnerOrderNo":"'.$dat['partnerOrderNo'].'"}';#系统订单
        #获取签名
        $sign = $this->get_sign(['jsonParams'=>$data,'timestamp'=>$timestamp]);
        $url2 = 'api/rest/v2/adapt/adaptation/order/shop-order/cancel?timestamp='.$timestamp.'&appCode='.$this->appCode.'&sign='.$sign;
        $newurl = $this->url.$url2;
        $res = httpRequest3($newurl,$data);
        $res = json_decode($res,true);
        if($res['success']==1){
            return json(['data'=>$res['data'],'code'=>0]);
        }else{
            return json(['data'=>'','code'=>-1]);
        }
    }

    #取消采购订单（已经成功生成采购单或生成了采购单，没有支付的，也是用这个取消。要等采购员反馈是否有货）/待测试
    public function cancel_order(Request $request){
        $dat = input();
        #时间戳
        $timestamp = time();
        #请求数据
        $data = '{"orderCode":"'.$dat['orderCode'].'"}';#poCode采购单
        #获取签名
        $sign = $this->get_sign(['jsonParams'=>$data,'timestamp'=>$timestamp]);
        $url2 = 'api/rest/v2/adapt/adaptation/order/po-cancel?timestamp='.$timestamp.'&appCode='.$this->appCode.'&sign='.$sign;
        $newurl = $this->url.$url2;
        $res = httpRequest3($newurl,$data);
        $res = json_decode($res,true);
        if($res['success']==1){
            return json(['data'=>$res,'code'=>0]);
        }else{
            return json(['data'=>$res,'code'=>-1]);
        }
    }

    #退换货申请（先请求订单详情接口，获取系统内置的skucode）
    public function return_order(Request $request){
        $dat = input();

        #时间戳
        $timestamp = time();
        #请求数据
        $data = '{"applySource":"'.$dat['applySource'].'","orderCode":"'.$dat['orderCode'].'","applyType":"'.$dat['applyType'].'","applyContent":"'.$dat['applyContent'].'","skuList":[{"skuCode":"'.$dat['skuCode'].'","quantity":'.$dat['quantity'].'}]}';
        #获取签名
        $sign = $this->get_sign(['jsonParams'=>$data,'timestamp'=>$timestamp]);
        $url2 = 'api/rest/v2/adapt/adaptation/order/apply-return?timestamp='.$timestamp.'&appCode='.$this->appCode.'&sign='.$sign;
        $newurl = $this->url.$url2;
        $res = httpRequest3($newurl,$data);
        $res = json_decode($res,true);
        if($res['success']==1){
            return json(['data'=>$res,'code'=>0]);
        }else{
            return json(['data'=>'','code'=>-1]);
        }
    }

    #退换货申请记录查询
    public function return_order_query(Request $request){
        $dat = input();

        #时间戳
        $timestamp = time();
        #请求数据
        $data = '{"returnFlowCode":"'.$dat['returnFlowCode'].'"}';#R3370548685795
        #获取签名
        $sign = $this->get_sign(['jsonParams'=>$data,'timestamp'=>$timestamp]);
        $url2 = 'api/rest/v2/adapt/adaptation/order/return/get?timestamp='.$timestamp.'&appCode='.$this->appCode.'&sign='.$sign;
        $newurl = $this->url.$url2;
        $res = httpRequest3($newurl,$data);
        $res = json_decode($res,true);
        if($res['success']==1){
            return json(['data'=>$res['data'],'code'=>0]);
        }else{
            return json(['data'=>'','code'=>-1]);
        }
    }

    #获取订单详情
    public function get_orderdetail(Request $request){
        $dat = input();

        #时间戳
        $timestamp = time();
        #请求数据
        $data = '{"partnerOrderNo":"'.$dat['partnerOrderNo'].'","shopOrderNo":"'.$dat['shopOrderNo'].'"}';
        #获取签名
        $sign = $this->get_sign(['jsonParams'=>$data,'timestamp'=>$timestamp]);
        $url2 = 'api/rest/v2/adapt/adaptation/order/detail?timestamp='.$timestamp.'&appCode='.$this->appCode.'&sign='.$sign;
        $newurl = $this->url.$url2;
        $res = httpRequest3($newurl,$data);
        $res = json_decode($res,true);
        if($res['success']==1){
            return json(['data'=>$res['data'],'code'=>0]);
        }else{
            return json(['data'=>'','code'=>-1]);
        }
    }

    #国内物流轨迹（待检查：https://solution-api.buckydrop.com/en/api_content?menuId=567900&contentId=2172600）
    public function domestic_route(Request $request){
        $dat = input();

        #时间戳
        $timestamp = time();
        #请求数据
        $data = '{"poOrderCode":"'.$dat['poOrderCode'].'"}';
        #获取签名
        $sign = $this->get_sign(['jsonParams'=>$data,'timestamp'=>$timestamp]);
        $url2 = 'api/rest/v2/adapt/adaptation/logistics/domestic/trace/query?timestamp='.$timestamp.'&appCode='.$this->appCode.'&sign='.$sign;
        $newurl = $this->url.$url2;
        $res = httpRequest3($newurl,$data);
        $res = json_decode($res,true);

        if($res['success']==1){
            return json(['data'=>$res['data'],'code'=>0]);
        }else{
            return json(['data'=>'','code'=>-1]);
        }
    }

    #国外物流轨迹(走我们自己的物流就不需要了)
    public function foregin_route(Request $request){
        $dat = input();

        #时间戳
        $timestamp = time();
        #请求数据
        $data = '{"packageCode":"'.$dat['packageCode'].'"}';
        #获取签名
        $sign = $this->get_sign(['jsonParams'=>$data,'timestamp'=>$timestamp]);
        $url2 = 'api/rest/v1/adaptation/logistics/query-info?timestamp='.$timestamp.'&appCode='.$this->appCode.'&sign='.$sign;
        $newurl = $this->url.$url2;
        $res = httpRequest3($newurl,$data);
        $res = json_decode($res,true);

        if($res['success']==1){
            return json(['data'=>$res['data'],'code'=>0]);
        }else{
            return json(['data'=>'','code'=>-1]);
        }
    }

    #获取订单状态变更
    public function get_orderlog(Request $request){
        $dat = input();
        Db::name('backydrop_log')->insert([
            'content'=>json_encode($dat,true),
            'time'=>date('Y-m-d H:i:s')
        ]);
    }

    #获取分类列表信息(backydrop)
    public function get_allcategory(){
        #获取商品标准类目
        #时间戳
        $timestamp = time();
        #请求数据
        $data = '{}';
        #获取签名
        $sign = $this->get_sign(['jsonParams'=>$data,'timestamp'=>$timestamp]);
        $url2 = 'api/rest/v2/adapt/openapi/product/category/list-tree?timestamp='.$timestamp.'&appCode='.$this->appCode.'&sign='.$sign;
        $newurl = $this->url.$url2;
        $res = httpRequest3($newurl,$data);
        $res = json_decode($res,true);

        if($res['success']==1){
            foreach($res['data'] as $k=>$v){
                $ishave = Db::connect($this->config)->name('category_backydrop')->where(['code'=>$v['categoryCode']])->find();
                if(empty($ishave['id'])){
                    $level1 = Db::connect($this->config)->name('category_backydrop')->insertGetId([
                        'name'=>$v['categoryName'],
                        'code'=>$v['categoryCode']
                    ]);
                }else{
                    $level1 = $ishave['id'];
                }

                if(!empty($v['childList'])){
                    foreach($v['childList'] as $k2=>$v2){
                        $ishave = Db::connect($this->config)->name('category_backydrop')->where(['code'=>$v2['categoryCode']])->find();
                        if(empty($ishave['id'])){
                            $level2 = Db::connect($this->config)->name('category_backydrop')->insertGetId([
                                'pid'=>$level1,
                                'name'=>$v2['categoryName'],
                                'code'=>$v2['categoryCode']
                            ]);
                        }else{
                            $level2 = $ishave['id'];
                        }
                        if(!empty($v2['childList'])){
                            foreach($v2['childList'] as $k3=>$v3) {
                                $ishave = Db::connect($this->config)->name('category_backydrop')->where(['code'=>$v3['categoryCode']])->find();
                                if(empty($ishave['id'])) {
                                    Db::connect($this->config)->name('category_backydrop')->insertGetId([
                                        'pid' => $level2,
                                        'name' => $v3['categoryName'],
                                        'code' => $v3['categoryCode']
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }

        echo 'success';
    }

    #每三天请求superbuy热卖分类的商品(存入统一数据表)
    public function get_superbuy(Request $request){
        $dat = input();
        $timestamp = time();

        # todo 测试数据START
//            $res = $this->get_allcategory();
//            dd($res);


//        $new_goods = httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=api/getgoods/detail_query',json_encode(['type'=>2,'goodsLink'=>'https://item.taobao.com/item.htm?id=795314563315'],true),['Content-Type: application/json']);
//        $new_goods = json_decode($new_goods,true);
//        dd($new_goods);
        # todo 测试数据END

        $cateArr = [5,4,3,6,1,2];
        $time = time();
        $date = date('Y-m-d H:i:s');
        foreach($cateArr as $key=>$val){
            $hotbuy_1 = 'https://front.superbuy.com/logistic/get-index-pull-data?cateId='.$val.'&pageSize=40';
            $hotbuy_1 = json_decode(file_get_contents($hotbuy_1),true);
            foreach($hotbuy_1['data'] as $k=>$v){
                if(isset($v['goods_link'])){
                    $ishave = Db::connect($this->config)->name('goods')->where(['other_goods_id'=>$v['goods_code']])->find();
                    if(empty($ishave)){
                        $this->get_goods($k,$v,$date,$time);
                    }
                }
            }
        }

        echo 'success';
    }

    #公用获取商品详情信息
    public function get_goods($k,$v,$date,$time,$info=[]){
        $goodsLink = '';
        if(isset($v['productLink'])){
            $goodsLink = $v['productLink'];
        }else{
            $goodsLink = $v['goods_link'];
        }

        $new_goods = httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=api/getgoods/detail_query',json_encode(['type'=>2,'goodsLink'=>$goodsLink],true),['Content-Type: application/json']);
        $new_goods = json_decode($new_goods,true);

        #查看商品的计量单位
        if(isset($new_goods['data']['repositoryInfo']['quantityText'])) {
            $quantityText = trim(substr($new_goods['data']['repositoryInfo']['quantityText'], -1));
            $unit = Db::name('unit')->where(['code_name' => $quantityText])->find()['code_value'];
            if (empty($unit)) {
                $unit = '011';
            }
        }
        else{
            $unit = '011';
        }

        if($new_goods['code']==0 && isset($new_goods['data']['productDetailHtml'])){
            try {
                #获取商品分类==START
                $last_catId = ['cat_id'=>0,'parent_id'=>0];
                $cat_id1 = 0;#一级
                $cat_id = 0;#最后一级
                $cat_id2 = 0;#最后一级

                if(isset($new_goods['data']['goodsCatName'])){
                    $last_catId = Db::connect($this->config)->name('category')->where(['cat_name' => $new_goods['data']['goodsCatName']])->find();
                    if(!empty($last_catId)){
                        #自己的分类表有
                        $cat_id = intval($last_catId['cat_id']);
                        $cat_id2 = intval($last_catId['cat_id']);#最后一级
                        #获取第一级
                        $first_catId = Db::connect($this->config)->name('category')->where(['cat_id' => $last_catId['parent_id']])->find();
                        if ($first_catId['parent_id'] > 0) {
                            $first_catId = Db::connect($this->config)->name('category')->where(['cat_id' => $first_catId['parent_id']])->find();
                            if ($first_catId['parent_id'] > 0) {
                                $first_catId = Db::connect($this->config)->name('category')->where(['cat_id' => $first_catId['parent_id']])->find();
                                $cat_id1 = $first_catId['cat_id'];
                            } else {
                                $cat_id1 = $first_catId['cat_id'];
                            }
                        }
                    }
                    else{
                        #查询其他平台的分类表，并获取名称判断原分类表有无，无的话就插入/有的话就->获取分类ID
                        $last_catId = Db::connect($this->config)->name('category_backydrop')->where(['name' => $new_goods['data']['goodsCatName']])->find();
                        if(!empty($last_catId)){
                            #搜索其他平台的分类表，找到三级分类后插入原分类表，并依次获取分类id

                            #获取第二级
                            $ishave2 = Db::connect($this->config)->name('category_backydrop')->where(['id' => $last_catId['pid']])->find();
                            if($ishave2['pid']>0){
                                #获取第一级
                                $ishave1 = Db::connect($this->config)->name('category_backydrop')->where(['id' => $ishave2['pid']])->find();
                                if($ishave1['id']>0){
                                    #开始插入原分类表
                                    $ishave_name = Db::connect($this->config)->name('category')->where(['cat_name'=>$ishave1['name']])->find();

                                    #第一级
                                    if(empty($ishave_name['cat_id'])){
                                        $cat_id1 = Db::connect($this->config)->name('category')->insertGetId([
                                            'cat_name'=>$ishave1['name'],
                                            'type_id'=>1,
                                            'parent_id'=>0,
                                            'cat_level'=>1,
                                            'is_parent'=>1,
                                            'is_show'=>1,
                                            'created_at'=>$date
                                        ]);
                                    }else{
                                        $cat_id1 = $ishave_name['cat_id'];
                                    }

                                    #第二级
                                    $cat2_id = Db::connect($this->config)->name('category')->insertGetId([
                                        'cat_name'=>$ishave2['name'],
                                        'type_id'=>1,
                                        'parent_id'=>$cat_id1,
                                        'cat_level'=>2,
                                        'is_parent'=>1,
                                        'is_show'=>1,
                                        'created_at'=>$date
                                    ]);

                                    #第三级
                                    $cat_id = Db::connect($this->config)->name('category')->insertGetId([
                                        'cat_name'=>$last_catId['name'],
                                        'type_id'=>1,
                                        'parent_id'=>$cat2_id,
                                        'cat_level'=>3,
                                        'is_parent'=>0,
                                        'is_show'=>1,
                                        'created_at'=>$date
                                    ]);
                                    $cat_id2 = $cat_id;
                                }
                            }
                        }
                    }
                }
                #获取商品分类====END

                #导页卡片ID（废弃）===START
                $frame_id=0;
                #导页卡片ID===END

                #是否包邮和运费
                $is_domestic_baoyou = 1;$goods_freight_fee = 0;
                if(isset($new_goods['data']['freight'])){
                    if($new_goods['data']['freight']['price']>0){
                        $is_domestic_baoyou = 2;$goods_freight_fee = $new_goods['data']['freight']['price'];
                    }
                }
                $good_id = Db::connect($this->config)->name('goods')->insertGetId([
                    'goods_name' => $new_goods['data']['productName'],
                    'shop_id' => 0,
                    'wid' => 32,#接口商品默认直邮易仓（代发），2026/03/27
                    'api_id' => 36,#默认backydrop商户id，2026/03/18添加
                    'other_goods_id' => isset($new_goods['data']['goodsId'])?$new_goods['data']['goodsId']:0,
                    'other_spuCode' => $new_goods['data']['spuCode'],
                    'other_goods_link' => $new_goods['data']['productLink'],
                    'other_shop' => json_encode($new_goods['data']['shop'], true),
                    'other_platform' => $new_goods['data']['platform'],
                    'cat_id' => $cat_id,
                    'cat_id1' => $cat_id1,
                    'cat_id2' => $cat_id2,
                    'keywords_id'=>isset($info['keywords_id'])?$info['keywords_id']:0,
                    'guide_type'=>isset($info['type'])?$info['type']:0,#废弃
                    'hotsearch_id'=>$frame_id,#废弃
                    'pc_desc' => $new_goods['data']['productDetailHtml'],
                    'goods_mode' => 0,
                    'brand_type' => 0,#空牌
                    'goods_status' => 1,
                    'click_count'=>mt_rand(111, 9999),
                    'star_count'=>mt_rand(111, 9999),
                    'share_count'=>mt_rand(111, 9999),
                    'created_at' => $date,
                    'sku_id' => 0,#（在下方插入规格id）
                    'goods_subname' => $new_goods['data']['productName'],
                    'goods_price' => $new_goods['data']['proPrice']['price'],
                    'market_price' => $new_goods['data']['proPrice']['price'],
                    'cost_price' => $new_goods['data']['proPrice']['price'],
                    'goods_number' => isset($new_goods['data']['repositoryInfo']['quantity'])?$new_goods['data']['repositoryInfo']['quantity']:2000,
                    'goods_image' => $new_goods['data']['picUrl'],
                    'keywords' => $new_goods['data']['productName'],
                    'goods_audit' => 1,
                    'contract_ids' => 'a:4:{i:1;s:1:"0";i:2;s:1:"0";i:3;s:1:"0";i:5;s:1:"0";}',
                    'add_time' => $time,
                    'goods_freight_fee'=>$goods_freight_fee,
                    'domestic_baoyou'=>$is_domestic_baoyou,
                    'goods_moq' => 1,
                    'other_attrs' => serialize([
                        'value_name0' => '', 'value_desc0' => '',
                    ]),#(接口/爬虫商品不需要填写这个)
                    'updated_at' => $date,
                ]);

                $have_specs = 1;
                if (empty($new_goods['data']['productProps'])) {
                    #无规格
                    $goodsSkuInsert = [
                        'goods_id' => $good_id,
                        'market_price' => $new_goods['data']['proPrice']['price'],
                        'goods_price' => $new_goods['data']['proPrice']['price'],
                        'goods_number' => isset($new_goods['data']['repositoryInfo']['quantity'])?$new_goods['data']['repositoryInfo']['quantity']:2000,
                        'warn_number' => 0,
                        'goods_sn' => $new_goods['data']['spuCode'],
                        'goods_barcode' => '',
                        'goods_stockcode' => '',
                        'is_spu' => 1, // 无规格商品 是SPU商品
                        'sku_prices' => json_encode([
                            'goods_number' => isset($new_goods['data']['repositoryInfo']['quantity'])?$new_goods['data']['repositoryInfo']['quantity']:2000,
                            'start_num' => [1],
                            'unit' => [$unit],
                            'select_end' => [1],
                            'end_num' => [isset($new_goods['data']['repositoryInfo']['quantity'])?$new_goods['data']['repositoryInfo']['quantity']:2000],
                            'currency' => [5],
                            'price' => [$new_goods['data']['proPrice']['price']],
                        ], true),#该规格的区间价格
                        'created_at' => $date,
                        'updated_at' => $date,
                    ];
                    Db::connect($this->config)->name('goods_sku')->insert($goodsSkuInsert);
                    Db::connect($this->config)->name('goods')->where(['goods_id'=>$good_id])->update([
                        'nospecs'=>json_encode([
                            'goods_number' => [isset($new_goods['data']['repositoryInfo']['quantity'])?$new_goods['data']['repositoryInfo']['quantity']:2000],
                            'start_num' => [1],
                            'unit' => [$unit],
                            'select_end' => [1],
                            'end_num' => [isset($new_goods['data']['repositoryInfo']['quantity'])?$new_goods['data']['repositoryInfo']['quantity']:2000],
                            'currency' => [5],
                            'price' => [$new_goods['data']['proPrice']['price']],
                        ], true)
                    ]);
                    $have_specs = 2;
                }
                else {
                    #有规格

                    #1、插入或查看有无该属性
                    foreach ($new_goods['data']['productProps'] as $gsKey => $item) {
                        $ishave = Db::connect($this->config)->name('attribute')->where(['attr_name' => $item['propName']])->find();
                        if (empty($ishave['attr_id'])) {
                            $new_goods['data']['productProps'][$gsKey]['attr_id'] = Db::connect($this->config)->name('attribute')->insertGetId([
                                'attr_name' => $item['propName'],
                                'is_spec' => 1,
                                'created_at' => $date,
                                'updated_at' => $date,
                            ]);
                        } else {
                            $new_goods['data']['productProps'][$gsKey]['attr_id'] = $ishave['attr_id'];
                        }

                        #1.1、插入子属性
                        $ishave2 = Db::connect($this->config)->name('attr_value')->where(['attr_vname' => $item['valueName'], 'attr_id' => $new_goods['data']['productProps'][$gsKey]['attr_id']])->find();
                        if (empty($ishave2['attr_vid'])) {
                            $new_goods['data']['productProps'][$gsKey]['attr_vid'] = Db::connect($this->config)->name('attr_value')->insertGetId([
                                'attr_id' => $new_goods['data']['productProps'][$gsKey]['attr_id'],
                                'attr_vname' => $item['valueName'],
                                'attr_vsort' => $gsKey + 1,
                                'created_at' => $date,
                                'updated_at' => $date,
                            ]);
                        } else {
                            $new_goods['data']['productProps'][$gsKey]['attr_vid'] = $ishave2['attr_vid'];
                        }
                    }

                    #2、插入商品规格类别表
                    foreach ($new_goods['data']['productProps'] as $gsKey => $item) {
                        $goodsSpecInsert = [
                            'goods_id' => $good_id,
                            'attr_id' => $item['attr_id'],
                            'attr_vid' => $item['attr_vid'],
                            'cat_id' => $cat_id,
                            'attr_value' => $item['valueName'],
                            'attr_desc' => '',
                            'is_checked' => 1,
                            'spec_sort' => $gsKey,
                            'created_at' => $date,
                            'updated_at' => $date,
                        ];
                        Db::connect($this->config)->name('goods_spec')->insert($goodsSpecInsert);
                    }

                    #3、插入规格表
                    foreach ($new_goods['data']['skuList'] as $k2 => $v2) {
                        $spec_ids = [];#大规格
                        $spec_vids = [];#子规格
                        $spec_names = [];#大规格名称：子规格名称
                        foreach ($v2['props'] as $k3 => $v3) {
                            $spec_ids_arr = Db::connect($this->config)->name('attribute')->where(['attr_name' => $v3['propName']])->find()['attr_id'];
                            $spec_ids = array_merge($spec_ids, [$spec_ids_arr]);


                            $spec_vids_arr = Db::connect($this->config)->name('attr_value')->where(['attr_vname' => $v3['valueName'],'attr_id'=>$spec_ids_arr])->find()['attr_vid'];
                            $spec_vids = array_merge($spec_vids, [$spec_vids_arr]);

                            $spec_names = array_merge($spec_names, [$v3['propName'] . ':' . $v3['valueName']]);
                        }
                        $skuList = [
                            'goods_id' => $good_id,
                            'sku_images'=>$v2['imgUrl'],
                            'spec_ids' => implode('|', $spec_ids),
                            'spec_vids' => implode('|', $spec_vids),
                            'spec_names' => implode(' ', $spec_names),
                            'sku_specs' => implode('*', $spec_vids),
                            'goods_price' => $v2['price']['price'],
                            'market_price' => $v2['price']['price'],
                            'goods_number' => $v2['quantity'],
                            'goods_sn' => $v2['skuCode'],
                            'is_spu' => 0,// 商品有规格 不是SPU商品
                            'sku_prices' => json_encode([
                                'goods_number' => $v2['quantity'],
                                'disabled_num' => 0,#0在售
                                'start_num' => [1],
                                'unit' => [$unit],
                                'select_end' => [1],#1数值，2以上
                                'end_num' => [$v2['quantity']],
                                'currency' => [5],
                                'price' => [$v2['price']['price']]
                            ], true),
                            'created_at' => $date,
                            'updated_at' => $date,
                        ];

                        Db::connect($this->config)->name('goods_sku')->insert($skuList);
                    }
                }

                #4、设置商品的默认sku_id
                $default_sku_id = Db::connect($this->config)->name('goods_sku')->where(['goods_id' => $good_id, 'checked' => 1])->order('sku_id asc')->find()['sku_id'];
                // 更新商品表 sku_id 为goods_sku 第一个
                Db::connect($this->config)->name('goods')->where(['goods_id' => $good_id])->update(['sku_id' => $default_sku_id,'have_specs'=>$have_specs]);

                #5、插入商品图片表
                foreach ($new_goods['data']['productImageList'] as $k2 => $v2) {
                    Db::connect($this->config)->name('goods_image')->insert([
                        'goods_id' => $good_id,
                        'spec_id' => 0,#商品规格类别表id，主要用作规格切换图片
                        'path' => $v2,
                        'is_default' => $k2 == 0 ? 1 : 0,
                        'sort' => $k2 + 1,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
                }

                #6、返回id给标签
                return $good_id;
            }catch(Exception $e){
                echo '第'.$k.'个@ ';
                dd($e->getMessage());
                exit();
            }
        }else{
            return 0;
        }
    }

    #1、获取导流板块的关键字商品/获取当天节日关键字
    public function get_hotsearch(Request $request){
        $dat = input();
        $time = time();
        $date = date('Y-m-d H:i:s');

        #1、轮播图
//        $rotate = Db::name('website_rotate')->whereRaw('other_keywords <> ""')->select();
//        foreach($rotate as $k=>$v){
//            $this->get_content_goods($v,4);
//        }

        #2、发现好货
//        $discovery = Db::name('website_discovery_list')->whereRaw('other_keywords <> ""')->select();
//        foreach($discovery as $k=>$v){
//            $this->get_content_goods($v,5);
//        }

        #3、源头产地（导页板块）
//        $guide_content = Db::connect($this->config)->name('guide_content')->whereRaw('gkeywords <> "" and id >= 38')->select();
//        foreach($guide_content as $k=>$v){
//            $this->get_content_goods($v,1);
//        }

        #4、节庆物品
        $festival = Db::name('website_festival')->whereRaw('keywords <> "" and date >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) and date <= DATE_ADD(CURDATE(), INTERVAL 15 DAY)')->select();
        foreach($festival as $k=>$v){
            $this->get_content_goods($v,3);
        }
    }

    #2、每天查询节日的关键字插入到表中，插入队列
    public function get_content_goods($info,$type){
        $keywords = [];
        if(isset($info['other_keywords'])){
            $keywords = explode("、",rtrim($info['other_keywords']));
        }elseif(isset($info['keywords'])){
            $keywords = explode("、",rtrim($info['keywords']));
        }elseif(isset($info['gkeywords'])){
            $keywords = explode("、",rtrim($info['gkeywords']));
        }

        foreach($keywords as $k2=>$v2){
            $ishave = Db::connect($this->config)->name('goods_keywords')->where(['keywords'=>$v2])->find();
            $keywords_id = 0;
            $page = 1;
            $is_done = 0;
            if(empty($ishave) && !empty($v2)){
                $keywords_id = Db::connect($this->config)->name('goods_keywords')->insertGetId(['keywords'=>$v2]);
            }
//            else{
//                $keywords_id = $ishave['id'];
//                $page = $ishave['get_times']+1;
//                $is_done = $ishave['is_done'];
//            }

//            if(empty($is_done)){
            #查看上一个关键字是否执行完毕
//                $ishave2 = Db::connect($this->config)->name('goods_keywords_doing')->order('id desc')->find();
//                if(empty($ishave2) || $ishave2['is_done']==1) {
//                    Db::connect($this->config)->name('goods_keywords_doing')->insert(['keywords_id' => $keywords_id]);
//                }else{
//                    echo '未爬完：【'.$ishave2['keywords_id'].'】';die;
//                }

//                $options = array('http' => array('timeout' => 75000));
//                $context = stream_context_create($options);
//                file_get_contents('https://decl.gogo198.cn/api/v2/get_content_goods?type='.$type.'&id=' . $keywords_id.'&page='.$page, false, $context);
//            }
        }
        return 1;
    }

    #每3小时获取100个热搜商品，拿5个关键字
    public function get_empty_keywords(Request $request){
        $dat = input();
        #->whereRaw('id > 245')
        $keywords_list = Db::connect($this->config)->name('goods_keywords')->whereRaw('is_done = 0')->order('id desc')->select();

        $can_get_arr = [];
        #查看商品表有无此关键字商品
        foreach($keywords_list as $k=>$v){
            $ishave = Db::connect($this->config)->name('goods')->where(['keywords_id'=>$v['id']])->count();
//            if($ishave<100 && count($can_get_arr)<5 && $v['is_done']==0){
            if(count($can_get_arr)<5 && $v['is_done']==0 && $v['get_times']==0){
                array_push($can_get_arr,$v['id']);
            }else{
                if(count($can_get_arr)==5){
                    break;
                }
            }
        }

        #不满5个就找不满100个商品的关键字填充满5个
        if(count($can_get_arr)<5){
            foreach($keywords_list as $k=>$v){
                $ishave = Db::connect($this->config)->name('goods')->where(['keywords_id'=>$v['id']])->count();
                if($ishave<100 && count($can_get_arr)<5 && $v['is_done']==0){
                    array_push($can_get_arr,$v['id']);
                }else{
                    if(count($can_get_arr)==5){
                        break;
                    }
                }
            }
        }

        #获取关键字下的商品
        foreach($can_get_arr as $k=>$v){
            $keywords = Db::connect($this->config)->name('goods_keywords')->where(['id'=>$v])->find();
            $keywords['get_times'] = $keywords['get_times']+1;
            $options = array('http' => array('timeout' => 75000));
            $context = stream_context_create($options);
            $res = file_get_contents('https://shop.gogo198.cn/collect_website/public/?s=api/getgoods/get_goods_content&type=1&id='.$v.'&page='.$keywords['get_times'], false, $context);

            echo '<p>完成'.$v.'</p>';
        }

        echo '<p>All Success</p>';
    }

    #队列服务，获取关键字的商品
    public function get_goods_content(Request $request){
        $dat = input();

        $time = time();
        $date = date('Y-m-d H:i:s');
        $id = intval($dat['id']);#关键字id
        $type = intval($dat['type']);
        $current = intval($dat['page']);
//        Db::connect($this->config)->name('goods_keywords_doing')->insert(['keywords_id'=>$id,'is_done'=>1]);

        $size = 20;
        $options = ['http'=>['timeout'=>75000]];
        $context = stream_context_create($options);
        $keywords = Db::connect($this->config)->name('goods_keywords')->where(['id'=>$id])->find();
        $goods = json_decode(file_get_contents('https://shop.gogo198.cn/collect_website/public/?s=api/getgoods/keyword_query&current='.$current.'&size='.$size.'&keyword='.$keywords['keywords'], false, $context),true);

        if(!empty($goods['data'])){
            #1、先获取表里是否存在此商品

            foreach($goods['data'] as $k3=>$v3){
                $ishave = Db::connect($this->config)->name('goods')->where(['other_spuCode'=>$v3['spuCode']])->find();
                if(empty($ishave)){
                    #2、存入数据表
                    $this->get_goods($k3,$v3,$date,$time,['keywords_id'=>$id]);
                }
            }

            #3、爬取次数++
            $datas = [];
            $datas['get_times'] = $current;
            Db::connect($this->config)->name('goods_keywords')->where(['id'=>$id])->update($datas);
        }else{
            Db::connect($this->config)->name('goods_keywords')->where(['id'=>$id])->update(['is_done'=>1]);
        }

        echo 'success';
    }

    #获取出现错误的商品并重新获取编辑
    public function get_errorgoods(request $request){
        $dat = input();
        $id = intval($dat['id']);
        $type = intval($dat['type']);
        if($type==1){
            #删除
//            for($i=5625;$i<=5694;$i++){
//                Db::connect($this->config)->name('goods')->where(['goods_id'=>$i])->delete();
//                Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$i])->delete();
//                Db::connect($this->config)->name('goods_spec')->where(['goods_id'=>$i])->delete();
//                Db::connect($this->config)->name('goods_image')->where(['goods_id'=>$i])->delete();
//            }
            Db::connect($this->config)->name('goods')->where(['goods_id'=>$id])->delete();
            Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$id])->delete();
            Db::connect($this->config)->name('goods_spec')->where(['goods_id'=>$id])->delete();
            Db::connect($this->config)->name('goods_image')->where(['goods_id'=>$id])->delete();

        }elseif($type==2){
            #替换
            $goods_sku = Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$id])->select();
            foreach($goods_sku as $k=>$v){
                $new = [16443];$replace = [16041];
                foreach($new as $k2=>$v2){
                    $spec_vids = str_replace($replace[$k2],$v2,$v['spec_vids']);
                    $sku_specs = str_replace($replace[$k2],$v2,$v['sku_specs']);

                    Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$v['sku_id']])->update([
                        'spec_vids'=>$spec_vids,
                        'sku_specs'=>$sku_specs,
                    ]);
                    break;
                }
            }
        }

        echo 'success';
    }
    
    #每周六查看商品价格变动
    public function check_goods_value(Request $request){
        #拿到商品第三方链接
        #有可能要修改商品规格，不存在的要删除（待做，因为一旦删除会引发用户页的“购物车”、“购物详情”可能报错）。
        $history_info = Db::connect($this->config)->name('goods_price_history')->order('id desc')->find();
        $goods = Db::connect($this->config)->name('goods')->whereRaw('shop_id=0 and other_goods_link <> "" and goods_id > '.$history_info['goods_id'])->field(['goods_id','other_goods_link','goods_price','sku_id'])->order('goods_id desc')->limit(100)->select();
//        $goods = Db::connect($this->config)->name('goods')->whereRaw('other_goods_link <> "" and (goods_id=211 or goods_id=50 or goods_id=51 or goods_id=52 or goods_id=71 or goods_id=75 or goods_id=78)')->field(['goods_id','other_goods_link','goods_price','sku_id'])->order('goods_id desc')->select();
        foreach($goods as $k=>$v){
            #获取链接内的价钱
            $new_goods = httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=api/getgoods/detail_query',json_encode(['type'=>2,'goodsLink'=>$v['other_goods_link']],true),['Content-Type: application/json']);
            $new_goods = json_decode($new_goods,true);
            if($new_goods['code']==0){
                $odd_price = 0;
                $sort_type = 0;
                $sku_prices = [];

                if($v['goods_price']>$new_goods['data']['proPrice']['price']){
                    #上一次价格>本次价格（降）
                    $sort_type = 1;

                    #规格价格
                    $origin_price = Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$v['goods_id'],'sku_id'=>$v['sku_id']])->find();
                    $origin_price['sku_prices'] = json_decode($origin_price['sku_prices'],true);

                    #上次价格
                    $odd_price = $origin_price['sku_prices']['price'][0];

                    #商品规格库存表本次价格
                    $origin_price['sku_prices']['price'][0] = $new_goods['data']['proPrice']['price'];
                    $sku_prices = json_encode($origin_price['sku_prices'],true);

                }
                elseif($v['goods_price']<$new_goods['data']['proPrice']['price']){
                    #上一次价格<本次价格（升）
                    $sort_type = 2;

                    #规格价格
                    $origin_price = Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$v['goods_id'],'sku_id'=>$v['sku_id']])->find();
                    $origin_price['sku_prices'] = json_decode($origin_price['sku_prices'],true);

                    #上次价格
                    $odd_price = $origin_price['sku_prices']['price'][0];

                    #商品规格库存表本次价格
                    $origin_price['sku_prices']['price'][0] = $new_goods['data']['proPrice']['price'];
                    $sku_prices = json_encode($origin_price['sku_prices'],true);
                }
                else{
                    #价格没变动就跳去下一个循环
                    continue;
                }

                #记录商品历史记录
                Db::connect($this->config)->name('goods_price_history')->insert([
                    'goods_id'=>$v['goods_id'],
                    'odd_price'=>$odd_price,#上次金额
                    'now_price'=>$new_goods['data']['proPrice']['price'],#本次金额
                    'sort_type'=>$sort_type,
                    'createtime'=>time()
                ]);
                #更改最低价
                Db::connect($this->config)->name('goods')->where(['goods_id'=>$v['goods_id']])->update(['goods_price'=>$new_goods['data']['proPrice']['price'],'market_price'=>$new_goods['data']['proPrice']['price'],'cost_price'=>$new_goods['data']['proPrice']['price']]);
                #更改商品规格表最低价格的信息
                Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$v['goods_id'],'sku_id'=>$v['sku_id']])->update([
                    'goods_price'=>$new_goods['data']['proPrice']['price'],
                    'market_price'=>$new_goods['data']['proPrice']['price'],
                    'sku_prices'=>$sku_prices
                ]);
            }
            else{
                #不存在，下架
                Db::connect($this->config)->name('goods')->where(['goods_id'=>$v['goods_id']])->update(['goods_status'=>0]);
            }
        }
        echo 'success';
    }

    #探数接口API-每天9:35爬取汇率
    public function get_exchangerate(Request $request){
        $key = 'feea63fb96c064f252418348bf775fa9';

        $url = 'http://api.tanshuapi.com/api/exchange/v1/single?key='.$key.'&from=CNY';
        $list = json_decode(file_get_contents($url),true);

        if($list['code']==1){
            foreach($list['data']['list'] as $k=>$v){
                $ishave = Db::name('website_exchange_rate')->where(['name'=>$v['name']])->find();
                if(!empty($ishave)){
                    Db::name('website_exchange_rate')->where(['name'=>$v['name']])->update([
                        'rate'=>$v['rate']
                    ]);
                }else{
                    Db::name('website_exchange_rate')->insert([
                        'symbol'=>$k,
                        'name'=>$v['name'],
                        'rate'=>$v['rate'],
                    ]);
                }
            }

            echo 'success';
        }
    }

    #NowAPI-获取世界城市
    public function get_worldtime(Request $request){
        $appkey = '72721';
        $sign = '9a10385d2e3479170db96bdc1243e8cc';
        $url = 'https://sapi.k780.com/?app=time.world_city&appkey='.$appkey.'&sign='.$sign.'&format=json';
        $list = json_decode(file_get_contents($url),true);
        if($list['success']==1){
            foreach($list['result']['lists'] as $k=>$v){
                $ishave = Db::name('website_world_time')->where(['city_en'=>$k])->find();
                if(empty($ishave)){
                    Db::name('website_world_time')->insert([
                        'city_en'=>$k,
                        'continentsEn'=>$v['continentsEn'],
                        'continentsCn'=>$v['continentsCn'],
                        'contryEn'=>$v['contryEn'],
                        'contryCn'=>$v['contryCn'],
                        'cityEn'=>$v['cityEn'],
                        'cityCn'=>$v['cityCn'],
                    ]);
                }
            }
        }

        echo 'success';
    }

    #NowAPI-获取世界城市时间
    public function get_worldcity_time(Request $request){
        $dat = input();
        $city_en = trim($dat['city']);

        $appkey = '72721';
        $sign = '9a10385d2e3479170db96bdc1243e8cc';

        $url = 'https://sapi.k780.com/?app=time.world&city_en='.$city_en.'&appkey='.$appkey.'&sign='.$sign.'&format=json';
        $time = json_decode(file_get_contents($url),true);

        return json(['code'=>0,'data'=>$time]);
//        return '{"code":0,"data":{"success":"1","result":{"continents_en":"asia","continents_cn":"亚洲","contry_en":"china","contry_cn":"中国","city_en":"beijing","city_cn":"北京","time_zone_no":"+8:00","time_zone_nm":"东八区","latlon":"北纬39°55\',东经116°23\'","smr_status":"0","smr_mk":"0","smr_str_datetime":"","smr_end_datetime":"","smr_str_timestamp":"","smr_end_timestamp":"","timestamp":"1714029339","datetime_1":"2024-04-25 15:15:39","datetime_2":"2024年04月25日 15时15分39秒","datetime_3":"Thu, 25 Apr 2024 15:15:39","week_1":"4","week_2":"星期四","week_3":"周四","week_4":"Thursday","gmt_timestamp":"1714000539","gmt_datetime":"2024-04-25 07:15:39","bjt_timestamp":"1714029339","bjt_datetime":"2024-04-25 15:15:39"}}}';
    }

    #各系统的在线客服应通知已绑定小程序的运营人员
    public function contact_customer(Request $request){
        $data = input();
        $company_id = isset($data['company_id'])?intval($data['company_id']):0;

        if($company_id==0){
            #平台运营人员
            $users = Db::name('foll_user')->whereRaw('user_id<>0')->select();
            foreach($users as $k=>$v){
                $user = Db::name('website_user')->where(['id'=>$v['user_id']])->find();
                if(!empty($user['sns_openid'])){
                    #小程序通知
                    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx6d1af256d76896ba&secret=d19a96d909c1a167c12bb899d0c10da6";
                    $res = file_get_contents($url);
                    $result = json_decode($res, true);

                    $post2 = json_encode([
                        'template_id'=>'GRa2BGkGrqU8g7IgMAVh6vx2iDD08uJSdK316TINQ7s',
                        'page'=>'pages/agreement/index?typeid=3&id='.$v['id'],
                        'touser' =>$user['sns_openid'],
                        'data'=>['thing1'=>['value'=>'在线咨询'],'phrase2'=>['value'=>'未回复'],'time4'=>['value'=>date('Y年m月d日 H:i')]],
                        'miniprogram_state'=>'formal',
                        'lang'=>'zh_CN',
                    ]);
                    $resu = httpRequest('https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token='.$result['access_token'], $post2,['Content-Type:application/json'],1);
                }
            }
        }
        else{
            #商家运营人员
            $users = Db::name('centralize_manage_person')->whereRaw('gogo_id<>0 and company_id='.$company_id.' and status=1')->select();
            foreach($users as $k=>$v){
                $user = Db::name('website_user')->where(['id'=>$v['gogo_id']])->find();
                if(!empty($user['sns_openid'])){
                    #小程序通知
                    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx6d1af256d76896ba&secret=d19a96d909c1a167c12bb899d0c10da6";
                    $res = file_get_contents($url);
                    $result = json_decode($res, true);

                    $post2 = json_encode([
                        'template_id'=>'GRa2BGkGrqU8g7IgMAVh6vx2iDD08uJSdK316TINQ7s',
                        'page'=>'pages/agreement/index?typeid=4&id='.$company_id.'_'.$v['gogo_id'],
                        'touser' =>$user['sns_openid'],
                        'data'=>['thing1'=>['value'=>'在线咨询'],'phrase2'=>['value'=>'未回复'],'time4'=>['value'=>date('Y年m月d日 H:i')]],
                        'miniprogram_state'=>'formal',
                        'lang'=>'zh_CN',
                    ]);
                    $resu = httpRequest('https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token='.$result['access_token'], $post2,['Content-Type:application/json'],1);
                }
            }
        }

    }

    #访问昨天的医药资讯
    public function get_medical_news(Request $request){
        $url = 'https://www.bopuyun.com/hyper/news/list';

        $last_day = date('Y-m-d',strtotime("-1 days"));
        $end_time = strtotime($last_day.' 23:59:59');
        $start_time = strtotime($last_day.' 00:00:00');

        $data = json_encode(['end_time_timestamp'=>$end_time,'start_time_timestamp'=>$start_time,'page'=>1,'page_size'=>1000],true);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
//                "Cache-Control: no-cache",
                "Content-Type: application/json",
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response,true);

        $time = time();
        if(!empty($response)){
            foreach($response['data']['data'] as $k=>$v){
                Db::name('website_medical_news')->insert([
                    'title'=>$v['title'],
                    'url'=>$v['url'],
                    'createtime'=>$time
                ]);
            }
        }

        echo 'success';
    }

    #每分钟查看有无定时训练数据需要通过webhook/tcp推送至本地电脑
    public function check_lora_data(Request $request){
        #查找未训练、开启时间小于当前时间和定时开启训练的数据包
        $time = time();
        $package = Db::name('train_dataset_package')->whereRaw('status=0 and train_time=2 and starttime<='.$time)->find();

        if(!empty($package)){
            #设置为训练中
            Db::name('train_dataset_package')->where(['id'=>$package['id']])->update(['status'=>1]);
//            $res = start_lora($package['id']); #此方法废弃

            #同步数据包
            $res = sync_info($package['id'],'package');

            #记录推送日志
            $package2 = DB::name('train_dataset_package')->where(['id'=>$package['id']])->find();
            $package_dataset_count = explode(',',$package2['dataset_ids']);
            Log::info('在'.date('Y-m-d H:i:s')."时段，推送了训练数据（".count($package_dataset_count)."条）");

            #记录训练状态
//            Db::name('train_dataset_package')->where(['id'=>$package['id']])->update(['status'=>2]);
            echo $res['msg'];
        }
        else{
            echo '暂无训练任务';
        }
    }

    #用户体验版-同步文档信息到本地电脑/线上服务器
    public function now_sync_file_to_local(Request $request){
        $dat = input();

        $id = intval($dat['id']);

        $list = Db::name('experience_knowledge_list')->where(['id'=>$id])->find();
//        $list['file_path'] = json_decode($list['file_path'],true);

        $res = now_sync_file_to_local($list,1);

        Db::name('experience_knowledge_list')->where(['id'=>$id])->update(['is_add_dataset'=>$res]);

        return json(['code'=>0,'msg'=>'构建成功']);
    }

    #每分钟查看有无定时同步数据需要向本地电脑通过FRP发送同步数据
    public function sync_to_local(Request $request){
        #查找未同步、开启时间小于当前时间和定时开启训练的同步数据任务
        $time = time();
        $package = Db::name('sync_list')->whereRaw('status=0 and method=2 and starttime<='.$time)->find();

        if(!empty($package)){
            #设置为同步中
            Db::name('sync_list')->where(['id'=>$package['id']])->update(['status'=>1]);

            #同步数据包
            $res = now_sync_to_local($package);

            Db::name('sync_list')->where(['id'=>$package['id']])->update(['status'=>2]);
            echo '同步完成';
        }
        else{
            echo '暂无同步任务';
        }
    }
    #智能客服将问题id通过webhook/tcp推送至本地电脑
    public function check_rag_data(Request $request){
        $dat = input();
        $res = start_rag($dat['question_id']);

        if($res){
            return json(['code'=>0,'msg'=>'智能客服正在思考中']);
        }
    }

    #通义千问翻译接口
    public function translate_word(Request $request){
        $dat = input();

        $is_convert_customer = isset($dat['is_convert_customer'])?intval($dat['is_convert_customer']):0;
        $translate_lang = isset($dat['translate_lang'])?trim($dat['translate_lang']):'';#如果是auto就让机器判断原文是什么语种
//        $en_name = isset($dat['en_name'])?trim($dat['en_name']):'Chinese';#需要翻译成的所在国地语言（废弃，始终需要给到AI中文）
        $en_name = 'Chinese';#需要翻译成的所在国地语言（废弃，始终需要给到AI中文）

        $content = translate_word(intval($dat['question_id']),$is_convert_customer,$translate_lang,$en_name);

        return $content;
    }

    #大模型回答后，中文答案需要翻译成用户指定母语
    public function translate_answer(Request $request){
        $dat = input();
        $is_convert_customer = isset($dat['is_convert_customer'])?intval($dat['is_convert_customer']):0;
        $translate_back_language = isset($dat['translate_back_language'])?trim($dat['translate_back_language']):'';
        $content = translate_answer(intval($dat['answer_id']),$is_convert_customer,$translate_back_language);

        return $content;
    }

    #查询IP归属
    public function get_ip_region(Request $request){
        $dat = input();
        $ip = $dat['ip'];
        $xdb = '/www/wwwroot/gogo/collect_website/vendor/chinayin/ip2region/assets/ip2region.xdb';

        try {
            $region = XdbSearcher::newWithFileOnly($xdb)->search($ip);

            return $region;
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }

    #客服提醒配置
    public function log_remind(Request $request){
        $list = Db::name('website_chatlist_remind')->where(['status'=>0])->select();
        $now = time();

        foreach($list as $k=>$v){
            if($now>=$v['remind_time']){
                #当前时间大于等于提醒时间
                $log = Db::name('website_chatlist')->where(['id'=>$v['message_id']])->find();
                $log['content'] = json_decode($log['content'],true);

                #插入一条提醒记录
                Db::name('website_chatlist')->insert([
                    'company_id'=>$v['cid'],
                    'pid'=>$log['pid'],
                    'uid'=>$v['uid'],
                    'kefu_id'=>0,
                    'who_send'=>$v['who_send'],
                    'is_read'=>0,
                    'content_type'=>1,
                    'content'=>json_encode('提提你：'.$log['content'],true),
                    'ip'=>$_SERVER['REMOTE_ADDR'],
                    'createtime'=>$now
                ]);

                #修改为已提醒
                Db::name('website_chatlist_remind')->where(['id'=>$v['id']])->update(['status'=>1]);
            }
        }

        echo 'success';
    }

    #获取历史数据
    public function get_chat_history(Request $request){
        $dat = input();
        $chat_pid = intval($dat['chat_pid']);

        $ids = Db::name('website_chatlist')->where(['id'=>$chat_pid])->field('uid,company_id')->find();

        $list = Db::name('website_chatlist')->whereRaw('id < '.$chat_pid.' and uid='.$ids['uid'].' and company_id='.$ids['company_id'])->order('id','desc')->limit(10)->select();

        $historyRecords = [];#历史记录数组
        $response = [];
        if(empty($list)){
            $response = ["history" => "无历史记录"];
        }
        else{
            foreach($list as $k=>$v){
                $content = [];
                if($v['language']=='Chinese'){
                    $content = json_decode($v['content'],true);
                }else{
                    $content = json_decode($v['origin_content'],true);
                }
                if($v['who_send']==2){
                    $historyRecords[] = '用户：'.$content;
                }elseif($v['who_send']==1){
                    $historyRecords[] = '客服：'.$content;
                }
            }

            // 反转数组使最新记录在最后（时间顺序）
            $historyRecords = array_reverse($historyRecords);

            // 合并所有记录为一个字符串
            $combinedHistory = implode("\n", $historyRecords);
            $response = ["history" => $combinedHistory];
        }

        // 返回JSON响应
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    #通知用户
    public function notice_user(Request $request){
        $dat = input();

        $res = notice_user($dat['user'],json_decode($dat['msg'],true));

        return $res;
    }

    #通知他人
    public function notice_people(Request $request){
        $dat = input();

        $res = notice_people($dat);

        return $res;
    }

    #定时查看有无同步数据库表任务
    public function check_sync_data(Request $request){
        $list = Db::name('ai_syncdata')->where(['status'=>0,'method'=>2])->select();

        $now = time();

        foreach($list as $k=>$v){
            if($now>=$v['synctime'] && !empty($v['synctime'])){
                #当前时间大于等于执行同步时间
                $res = sync_data_to_database($v['id']);
                if($res['code']==1){
                    Db::name('ai_syncdata')->where(['id'=>$v['id']])->update(['status'=>1]);
                }elseif($res['code']==0){
                    Db::name('ai_syncdata')->where(['id'=>$v['id']])->update(['status'=>-1]);
                }
            }
        }

        echo 'success';
    }

    #获取商品主题图小程序二维码
    public function get_miniprogram_goods_code(Request $request){
        $data = input();
        $id = intval($data['id']);#用户参与活动表id
        $uid = intval($data['uid']);#用户id

        $campaign = Db::name('website_campaign_user_list')->where(['id'=>$id])->find();

//        if($campaign['status'] == 0){
//            #进行中，判断预约时间是否超过当前时间，修改订单状态为“预约时间已过期，请重新预约”
//        }

        if(!empty($campaign['poster'])){
            return json(['code'=>0,'msg'=>'生成推广图片成功','img'=>$campaign['poster']]);
        }else{
            $res = get_miniprogram(['goods_id'=>$campaign['product_id'],'uid'=>$uid,'campaign_id'=>$campaign['id']]);
            Db::name('website_campaign_user_list')->where(['id'=>$id])->update(['poster'=>$res['img']]);
            return json($res);
        }
    }

    #=====阿里云linux服务器获取数据======start
    #获取商品
    public function ay_get_goods(Request $request){
        $dat = input();
        #需获取：平台、商品名称、规格、价钱、库存、平台链接、“+：参数信息、物流支撑、卖家说明、价格说明”；

        #1、获取最新爬到的商品id
        $last_id = Db::connect($this->config)->name('goods_fetch_log')->order('good_id','desc')->limit(1)->find();
        if(empty($last_id)){
            $data['goods'] = Db::connect($this->config)->name('goods')->whereRaw('goods_id>28')->field('goods_id,other_platform,goods_name,pc_desc,other_goods_link')->limit(20)->select();
            #获取商品字段
            $data['column'] = Db::connect($this->config)->getTableInfo('goods','fields');
        }
        else{
            $data['goods'] = Db::connect($this->config)->name('goods')->whereRaw('goods_id>'.$last_id['good_id'])->field('goods_id,other_platform,goods_name,pc_desc,other_goods_link')->limit(20)->select();
        }

        $true_goods = [];
        #2、获取商品规格
        if(!empty($data['goods'])){
            #记录最近爬取的商品数据
            $endGoodsId = end($data['goods']);
            Db::connect($this->config)->name('goods_fetch_log')->insert(['good_id'=>$endGoodsId['goods_id'],'createtime'=>time()]);

            #获取相应的商品规格信息
            foreach($data['goods'] as $k=>$v){
                #平台链接
                $data['goods'][$k]['link'] = '//www.gogo198.cn/goods-'.$v['goods_id'].'.html';
                #所属平台
                if(empty($v['other_platform'])){
                    $data['goods'][$k]['other_platform'] = '淘中国';
                }
                #销售优惠-减免
//                if(!empty($v['reduction_content'])){
//                    $data['goods'][$k]['reduction_content'] = json_decode($v['reduction_content'],true);
//                    foreach($data['goods'][$k]['reduction_content']['preferential_blong'] as $k2=>$v2){
//                        $reduction_rule = Db::connect($this->config)->name('ssl_reduction_rule')->where(['id'=>$data['goods'][$k]['reduction_content']['type'][$k2]])->find();
//                        $goods['reduction_content']['type_name'][$k] = $reduction_rule['name'];
//                        $goods['reduction_content']['content'][$k] = json_decode($reduction_rule['content'],true);
//                    }
//                    $goods['reduction_content']['currency1'] = Db::connection('shop_db')->table('centralize_currency')->where(['id'=>$goods['reduction_content']['currency1']])->first()->currency_symbol_standard;
//                    $goods['reduction_content']['currency2'] = Db::connection('shop_db')->table('centralize_currency')->where(['id'=>$goods['reduction_content']['currency2']])->first()->currency_symbol_standard;
//                }

                #规格、价钱、库存
                $sku_info = Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$v['goods_id']])->select();
                foreach($sku_info as $k2=>$v2){
                    $sku_prices = json_decode($v2['sku_prices'],true);
                    $currency = Db::name('centralize_currency')->where(['id'=>$sku_prices['currency'][0]])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
                    if(count($sku_prices['start_num'])>1){
                        #有多个购买区间
                        foreach($sku_prices['start_num'] as $k3=>$v3){
                            $unit = Db::name('unit')->where(['code_value'=>$sku_prices['unit'][$k3]])->find()['code_name'];
                            $buy_interval_name = '';
                            if($sku_prices['select_end']==1){
                                #数值
                                $buy_interval_name = '【买'.$v3.'至'.$sku_prices['end_num'][$k3].$unit.'】';
                            }
                            elseif($sku_prices['select_end']==2){
                                #以上
                                $buy_interval_name = '【买'.$v3.$unit.'以上】';
                            }

                            array_push($true_goods,['platform'=>$data['goods'][$k]['other_platform'],'goods_name'=>$v['goods_name'],'sku_name'=>$v2['spec_names'].$buy_interval_name,'price'=>$currency.' '.$sku_prices['price'][0],'store'=>$sku_prices['goods_number'],'link'=>$data['goods'][$k]['link']]);
                        }
                    }
                    else{
                        #只有一个购买区间
                        array_push($true_goods,['platform'=>$data['goods'][$k]['other_platform'],'goods_name'=>$v['goods_name'],'sku_name'=>$v2['spec_names'],'price'=>$currency.' '.$sku_prices['price'][0],'stock'=>$sku_prices['goods_number'],'link'=>$data['goods'][$k]['link']]);
                    }
                }
            }
        }

        return json_encode($true_goods,true);
    }
    #=====阿里云linux服务器获取数据======end


    #====================================================================废弃
    #获取导流页关键字商品（废弃）
    public function get_guide_goods(Request $request){
        $guide_condition = Db::connect($this->config)->name('guide_body')->whereRaw('system_id=3 and company_id=0 and gkeywords<>""')->select();
        if(!empty($guide_condition)){
            foreach($guide_condition as $k=>$v){
                $keywords_arr = explode('、',$v['gkeywords']);
                foreach($keywords_arr as $k2=>$v2){
                    $ishave = Db::connect($this->config)->name('goods_keywords')->where(['keywords'=>$v2])->find();
                    $keywords_id = 0;
                    if(empty($ishave)){
                        $keywords_id = Db::connect($this->config)->name('goods_keywords')->insertGetId(['keywords'=>trim($v2)]);
                    }else{
                        $keywords_id = $ishave['id'];
                    }

                    $keywords = Db::connect($this->config)->name('goods_keywords')->where(['id'=>$keywords_id])->find();
                    $keywords['get_times'] = $keywords['get_times']+1;
                    $options = array('http' => array('timeout' => 75000));
                    $context = stream_context_create($options);

                    $res = httpRequest('https://shop.gogo198.cn/collect_website/public/?s=api/getgoods/get_goods_content&type=1&id='.$keywords_id.'&page='.$keywords['get_times'],[]);
//                    $res = file_get_contents('https://shop.gogo198.cn/collect_website/public/?s=api/getgoods/get_goods_content&type=1&id='.$keywords_id.'&page='.$keywords['get_times'], false, $context);
                    if($res){
                        echo '<p>完成'.$v2.'</p>';
                    }else{
                        dd($res);
                    }
                }
            }
        }
        echo '<p>全部完成</p>';
    }

    #队列服务，获取关键字的商品(废弃)
    public function get_goods_content_backup(Request $request){
        $dat = input();
        $time = time();
        $date = date('Y-m-d H:i:s');
        $id = intval($dat['id']);
        $type = intval($dat['type']);

        if($type==1){
            #获取导页
            $guide_condition = Db::connect($this->config)->name('guide_body')->whereRaw('id='.$id.' and gkeywords<>"" and is_done=0')->select();
            if(!empty($guide_condition)){
                foreach($guide_condition as $k=>$v){
                    $current = $v['get_times']+1;
                    $keywords = explode('、',$v['gkeywords']);
                    foreach($keywords as $k2=>$v2){
                        #keyword_query
                        $size = 20;
                        $options = array(
                            'http' => array(
                                'timeout' => 10000000, // 设置超时时间为3000秒
                            ),
                        );
                        $context = stream_context_create($options);
                        $goods = json_decode(file_get_contents('https://shop.gogo198.cn/collect_website/public/?s=api/getgoods/keyword_query&current='.$current.'&size='.$size.'&keyword='.$v2, false, $context),true);
                        if(!empty($goods['data'])){
                            #1、先获取表里是否存在此商品
                            foreach($goods['data'] as $k3=>$v3){
                                $ishave = Db::connect($this->config)->name('goods')->where(['other_goods_id'=>$v3['goodsId']])->find();
                                if(empty($ishave)){
                                    #2、存入数据表
                                    $this->get_goods($k3,$v3,$date,$time,['type'=>1,'frame_id'=>0]);
                                }
                            }
                            #3、爬取次数++
                            Db::connect($this->config)->name('guide_body')->where(['id'=>$v['id']])->update(['get_times'=>$current]);
                        }else{
                            Db::connect($this->config)->name('guide_body')->where(['id'=>$v['id']])->update(['is_done'=>1]);
                        }
                    }
                }
            }
        }
        elseif($type==2){
            #获取内容
            $condition = Db::connect($this->config)->name('guide_content')->whereRaw('id='.$id.' and gkeywords<>"" and is_show=0 and is_done=0')->select();
            if(!empty($condition)){
                foreach($condition as $k=>$v){
                    $keywords = explode('、',$v['gkeywords']);
                    foreach($keywords as $k2=>$v2){
                        #keyword_query
                        $current = $v['get_times']+1;
                        $size = 20;
                        $options = array(
                            'http' => array(
                                'timeout' => 10000000, // 设置超时时间为3000秒
                            ),
                        );
                        $context = stream_context_create($options);
                        $goods = json_decode(file_get_contents('https://shop.gogo198.cn/collect_website/public/?s=api/getgoods/keyword_query&current='.$current.'&size='.$size.'&keyword='.$v2, false, $context),true);
                        if(!empty($goods['data'])){
                            #1、先获取表里是否存在此商品
                            foreach($goods['data'] as $k3=>$v3){
                                $ishave = Db::connect($this->config)->name('goods')->where(['other_goods_id'=>$v3['goodsId']])->find();
                                if(empty($ishave)){
                                    #2、存入数据表
                                    $this->get_goods($k3,$v3,$date,$time,['type'=>1,'frame_id'=>$v['id']]);
                                }
                            }
                            #3、爬取次数++
                            Db::connect($this->config)->name('guide_content')->where(['id'=>$v['id']])->update(['get_times'=>$current]);
                        }else{
                            Db::connect($this->config)->name('guide_content')->where(['id'=>$v['id']])->update(['is_done'=>1]);
                        }
                    }
                }
            }
        }
        elseif($type==3){
            #节日表
            $condition = Db::name('website_festival')->where(['id'=>$id])->find();
            if(!empty($condition['keywords'])){
                $keywords = explode('、',$condition['keywords']);
                foreach($keywords as $k2=>$v2){
                    #keyword_query
                    $current = $condition['get_times']+1;
                    $size = 20;
                    $options = array(
                        'http' => array(
                            'timeout' => 10000000, // 设置超时时间为3000秒
                        ),
                    );
                    $context = stream_context_create($options);
                    $goods = json_decode(file_get_contents('https://shop.gogo198.cn/collect_website/public/?s=api/getgoods/keyword_query&current='.$current.'&size='.$size.'&keyword='.$v2, false, $context),true);
                    if(!empty($goods['data'])){
                        #1、先获取表里是否存在此商品
                        foreach($goods['data'] as $k3=>$v3){
                            $ishave = Db::connect($this->config)->name('goods')->where(['other_goods_id'=>$v3['goodsId']])->find();
                            if(empty($ishave)){
                                #2、存入数据表
                                $this->get_goods($k3,$v3,$date,$time,['type'=>2,'frame_id'=>$condition['id']]);
                            }
                        }
                        #3、爬取次数++
                        Db::name('website_festival')->where(['id'=>$condition['id']])->update(['get_times'=>$current]);
                    }else{
                        Db::name('website_festival')->where(['id'=>$condition['id']])->update(['is_done'=>1]);
                    }
                }
            }
        }
        elseif($type==4){
            #首页轮播图表
            $condition = Db::name('website_rotate')->where(['id'=>$id])->find();
            if(!empty($condition['other_keywords'])){
                $keywords = explode('、',$condition['other_keywords']);
                foreach($keywords as $k2=>$v2){
                    #keyword_query
                    $current = $condition['get_times']+1;
                    $size = 20;
                    $options = array(
                        'http' => array(
                            'timeout' => 10000000, // 设置超时时间为3000秒
                        ),
                    );
                    $context = stream_context_create($options);
                    $goods = json_decode(file_get_contents('https://shop.gogo198.cn/collect_website/public/?s=api/getgoods/keyword_query&current='.$current.'&size='.$size.'&keyword='.$v2, false, $context),true);
                    if(!empty($goods['data'])){
                        #1、先获取表里是否存在此商品
                        foreach($goods['data'] as $k3=>$v3){
                            $ishave = Db::connect($this->config)->name('goods')->where(['other_goods_id'=>$v3['goodsId']])->find();
                            if(empty($ishave)){
                                #2、存入数据表
                                $this->get_goods($k3,$v3,$date,$time,['type'=>3,'frame_id'=>$condition['id']]);
                            }
                        }
                        #3、爬取次数++
                        Db::name('website_rotate')->where(['id'=>$condition['id']])->update(['get_times'=>$current]);
                    }else{
                        Db::name('website_rotate')->where(['id'=>$condition['id']])->update(['is_done'=>1]);
                    }
                }
            }
        }
        elseif($type==5){
            #发现好货表
            $condition = Db::name('website_discovery_list')->where(['id'=>$id])->find();
            if(!empty($condition['other_keywords'])){
                $keywords = explode('、',$condition['other_keywords']);
                foreach($keywords as $k2=>$v2){
                    #keyword_query
                    $current = $condition['get_times']+1;
                    $size = 20;
                    $options = array(
                        'http' => array(
                            'timeout' => 10000000, // 设置超时时间为3000秒
                        ),
                    );
                    $context = stream_context_create($options);
                    $goods = json_decode(file_get_contents('https://shop.gogo198.cn/collect_website/public/?s=api/getgoods/keyword_query&current='.$current.'&size='.$size.'&keyword='.$v2, false, $context),true);
                    if(!empty($goods['data'])){
                        #1、先获取表里是否存在此商品
                        foreach($goods['data'] as $k3=>$v3){
                            $ishave = Db::connect($this->config)->name('goods')->where(['other_goods_id'=>$v3['goodsId']])->find();
                            if(empty($ishave)){
                                #2、存入数据表
                                $this->get_goods($k3,$v3,$date,$time,['type'=>4,'frame_id'=>$condition['id']]);
                            }
                        }
                        #3、爬取次数++
                        Db::name('website_discovery_list')->where(['id'=>$condition['id']])->update(['get_times'=>$current]);
                    }else{
                        Db::name('website_discovery_list')->where(['id'=>$condition['id']])->update(['is_done'=>1]);
                    }
                }
            }
        }
        echo 'success';
    }

    #获取需要获取关键字商品的导页或卡片(废弃)
    public function get_hotsearch_backup(Request $request){
        $dat = input();
        $time = time();
        $date = date('Y-m-d H:i:s');

        #导页内容
        $guide_condition = Db::connect($this->config)->name('guide_body')->whereRaw('gkeywords<>"" and (starttime<="'.$date.'" and endtime>="'.$date.'") and is_done=0')->select();
        if(!empty($guide_condition)){
            foreach($guide_condition as $k=>$v){
                $keywords = explode('、',$v['gkeywords']);
                foreach($keywords as $k2=>$v2){
                    #keyword_query
                    $current = $v['get_times']+1;
                    $size = 20;
                    $options = array(
                        'http' => array(
                            'timeout' => 10000000, // 设置超时时间为3000秒
                        ),
                    );
                    $context = stream_context_create($options);
                    $goods = json_decode(file_get_contents('https://shop.gogo198.cn/collect_website/public/?s=api/getgoods/keyword_query&current='.$current.'&size='.$size.'&keyword='.$v2, false, $context),true);
                    if(!empty($goods['data'])){
                        #1、先获取表里是否存在此商品
                        foreach($goods['data'] as $k3=>$v3){
                            $ishave = Db::connect($this->config)->name('goods')->where(['other_goods_id'=>$v3['goodsId']])->find();
                            if(empty($ishave)){
                                #2、存入数据表
                                $this->get_goods($k3,$v3,$date,$time,['type'=>1,'frame_id'=>0]);
                            }
                        }
                        #3、爬取次数++
                        Db::connect($this->config)->name('guide_body')->where(['id'=>$v['id']])->update(['get_times'=>$current]);
                    }else{
                        Db::connect($this->config)->name('guide_body')->where(['id'=>$v['id']])->update(['is_done'=>1]);
                    }
                }
            }
        }

        #卡片内容
        $condition = Db::connect($this->config)->name('guide_content')->whereRaw('gkeywords<>"" and is_show=0 and (starttime<="'.$date.'" and endtime>="'.$date.'") and is_done=0')->select();
        if(!empty($condition)){
            foreach($condition as $k=>$v){
                $keywords = explode('、',$v['gkeywords']);
                foreach($keywords as $k2=>$v2){
                    #keyword_query
                    $current = $v['get_times']+1;
                    $size = 20;
                    $options = array(
                        'http' => array(
                            'timeout' => 10000000, // 设置超时时间为3000秒
                        ),
                    );
                    $context = stream_context_create($options);
                    $goods = json_decode(file_get_contents('https://shop.gogo198.cn/collect_website/public/?s=api/getgoods/keyword_query&current='.$current.'&size='.$size.'&keyword='.$v2, false, $context),true);
                    if(!empty($goods['data'])){
                        #1、先获取表里是否存在此商品
                        foreach($goods['data'] as $k3=>$v3){
                            $ishave = Db::connect($this->config)->name('goods')->where(['other_goods_id'=>$v3['goodsId']])->find();
                            if(empty($ishave)){
                                #2、存入数据表
                                $this->get_goods($k3,$v3,$date,$time,['type'=>1,'frame_id'=>$v['id']]);
                            }
                        }
                        #3、爬取次数++
                        Db::connect($this->config)->name('guide_content')->where(['id'=>$v['id']])->update(['get_times'=>$current]);
                    }else{
                        Db::connect($this->config)->name('guide_content')->where(['id'=>$v['id']])->update(['is_done'=>1]);
                    }
                }
            }
        }

        echo 'success';
    }

    #每三天请求superbuy热卖分类的商品(废弃)
    public function get_superbuy2(Request $request){
        $dat = input();

        $cateArr = [5,4,3,6,1,2];

        foreach($cateArr as $key=>$val){
            $hotbuy_1 = 'https://front.superbuy.com/logistic/get-index-pull-data?cateId='.$val.'&pageSize=40';
            $hotbuy_1 = json_decode(file_get_contents($hotbuy_1),true);
            foreach($hotbuy_1['data'] as $k=>$v){
                if(isset($v['goods_link'])){
                    $ishave = Db::connect($this->config)->name('goods_backydrop')->where(['good_id'=>$v['goods_code']])->find();
                    if(empty($ishave)){
                        $new_goods = httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=api/getgoods/detail_query',json_encode(['type'=>2,'goodsLink'=>$v['goods_link']],true),['Content-Type: application/json']);
                        $new_goods = json_decode($new_goods,true);
                        if($new_goods['code']==0){
                            Db::connect($this->config)->name('goods_backydrop')->insert([
                                'good_id'=>$new_goods['data']['goodsId'],
                                'is_hot'=>1,
                                'cateid'=>$val,
                                'content'=>json_encode($new_goods['data'],true)
                            ]);
                        }
                    }
                }
            }
        }

        echo 'success';
    }
}