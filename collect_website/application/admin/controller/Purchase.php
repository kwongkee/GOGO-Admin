<?php
namespace app\admin\controller;

//use think\Controller;
use think\Request;
use think\Db;
use app\admin\controller;
use think\Session;

class Purchase extends Auth
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

    public function buyer_index(Request $request)
    {
        $dat = input();

        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $keywords = input('search');
            $total = DB::name('website_buyer')->where('name','like','%'.$keywords.'%')->count();
            $data = DB::name('website_buyer')
                ->where('name','like','%'.$keywords.'%')
                ->order($order)
                ->limit($limit)
                ->select();

            $is_verify = ['-1'=>'已拒绝','0'=>'未验证','1'=>'已验证'];
            foreach ($data as &$item) {
                $item['createtime'] = date('Y-m-d H:i', $item['createtime']);
                if($item['type']==1){
                    $item['type_name'] = '接口买手';
                }elseif($item['type']==2){
                    $item['type_name'] = '平台买手';
                }
//                elseif($item['type']==3){
//                    $item['type_name'] = '合作买手';
//                }

                $item['statusname'] = $is_verify[$item['is_verify']];
            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        } else {
            return view('', compact(''));
        }
    }

    public function save_buyer(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;

        if(isset($dat['pa'])){
            $phone = '';$email = '';$verify_type = '';
            if($dat['type']==2 || $dat['type']==3){
                if($dat['verify_type']==1){
                    $phone = trim($dat['phone']);
                }elseif($dat['verify_type']==2){
                    $email = trim($dat['email']);
                }
                $verify_type = $dat['verify_type'];
            }

            if($id>0){
                Db::name('website_buyer')->where(['id'=>$id])->update([
                    'type'=>$dat['type'],
                    'name'=>trim($dat['name']),
                    'api_address'=>$dat['type']==1?trim($dat['api_address']):'',
                    'verify_type'=>$verify_type,
                    'phone'=>$phone,
                    'email'=>$email
                ]);
            }else{
                $time = time();
                $buyer_id = Db::name('website_buyer')->insertGetId([
                    'company_id'=>33,#默认钜铭企业买手
                    'type'=>$dat['type'],
                    'name'=>trim($dat['name']),
                    'api_address'=>$dat['type']==1?trim($dat['api_address']):'',
                    'is_verify'=>$dat['type']==1?1:0,
                    'verify_type'=>$verify_type,
                    'phone'=>$phone,
                    'email'=>$email,
                    'createtime'=>$time
                ]);

                if($dat['type']!=1){
                    #通知人工买手验证&注册账户
                    if($verify_type==1){
                        #手机通知
                        send_msg(['phone'=>$phone,'email'=>''],['msg'=>'Gogo购购网邀请您成为买手，请点击验证链接：https://www.gogo198.cn/become_buyer?id='.$buyer_id]);
                    }elseif($verify_type==2){
                        #邮箱通知
                        #https://www.gogo198.net/?s=shop/become_buyer&id=
                        send_msg(['phone'=>'','email'=>$email],['msg'=>'Gogo购购网邀请您成为买手，请点击验证链接：https://www.gogo198.cn/become_buyer?id='.$buyer_id]);
                    }
                }
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['type'=>2,'name'=>'','api_address'=>'','verify_type'=>1,'phone'=>'','email'=>''];
            if($id>0){
                $data = Db::name('website_buyer')->where(['id'=>$id])->find();
            }
            return view('', compact('id','data'));
        }
    }

    #审核买手注册
    public function review_buyer(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;

        if(isset($dat['pa'])){
            $status = intval($dat['status']);
            $buyer = Db::name('website_buyer')->where(['id'=>$id])->find();
            if($status==1){
                #审核通过
                Db::name('website_buyer')->where(['id'=>$id])->update(['is_verify'=>$status]);

                if($buyer['verify_type']==1){
                    #手机通知
                    send_msg(['phone'=>$buyer['phone'],'email'=>''],['msg'=>'你的平台买手审核已通过，请打开链接管理：https://www.gogo198.cn/buyer_manage?id='.$id]);
                }elseif($buyer['verify_type']==2){
                    #邮箱通知
                    send_msg(['phone'=>'','email'=>$buyer['email']],['msg'=>'你的平台买手审核已通过，请打开链接管理：https://www.gogo198.cn/buyer_manage?id='.$id]);
                }
            }
            elseif($status==-1){
                #拒绝通过
                $remark = trim($dat['remark']);
                if(empty($remark)){
                    return json(['code'=>-1,'msg'=>'请输入拒绝原因']);
                }
                Db::name('website_buyer')->where(['id'=>$id])->update(['remark'=>$remark,'is_verify'=>$status]);

                if($buyer['verify_type']==1){
                    #手机通知
                    send_msg(['phone'=>$buyer['phone'],'email'=>''],['msg'=>'你的平台买手审核已拒绝，拒绝原因：'.$remark]);
                }elseif($buyer['verify_type']==2){
                    #邮箱通知
                    send_msg(['phone'=>'','email'=>$buyer['email']],['msg'=>'你的平台买手审核已拒绝，拒绝原因：'.$remark]);
                }
            }
            return json(['code'=>0,'msg'=>'操作成功！']);
        }else{
            $data = Db::name('website_buyer')->where(['id'=>$id])->find();

            return view('',compact('id','data'));
        }
    }

    #订单分配
    public function audit_order(Request $request){
        $dat = input();
        $pa = isset($dat['pa'])?$dat['pa']:1;
        $buyer_id = intval($dat['id']);

        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            if($pa==1){
                #待分流
                $total = Db::name('website_order_list')->where(['buyer_id'=>0])->count();
                $data = Db::name('website_order_list')->where(['buyer_id'=>0])->order($order)->limit($limit)->order('id','desc')->select();
            }elseif($pa==2){
                #已分流
                $total = Db::name('website_order_list')->whereRaw('buyer_id<>0')->count();
                $data = Db::name('website_order_list')->whereRaw('buyer_id<>0')->order($order)->limit($limit)->order('id','desc')->select();
            }

            foreach ($data as &$item) {
                $item['createtime'] = date('Y-m-d H:i', $item['createtime']);
            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        } else {
            return view('', compact('pa','buyer_id'));
        }
    }

    #订单详情
    public function audit_detail(Request $request){
        $dat = input();
        $buyer_id = intval($dat['buyer_id']);
        $id = intval($dat['id']);

        $order = Db::name('website_order_list')->where(['id'=>$id])->find();
        $order['content'] = json_decode($order['content'],true);

        if(isset($dat['pa'])){

            $time = time();
            $user = Db::name('website_user')->where(['id'=>$order['user_id']])->find();
            if($dat['send']==1){
                #同意、拒绝分流
                if($dat['shunt_type']==1){
                    #同意分流
                    if(empty($dat['buyer_id'])){
                        return json(['code'=>-1,'msg'=>'请选择买手信息']);
                    }

                    $buyer = Db::name('website_buyer')->where(['id'=>intval($dat['buyer_id'])])->find();
                    Db::name('website_order_list')->where(['id'=>$id])->update([
                        'buyer_id'=>intval($dat['buyer_id'])
                    ]);

                    if($buyer['type']==1){
                        #接口买手

                        $productList = [];
                        if(!empty($order['content']['goods_info'])){
                            foreach($order['content']['goods_info'] as $k=>$v){
                                $goods = Db::connect($this->config)->name('goods')->where(['goods_id'=>$v['good_id']])->find();
                                foreach($v['sku_info'] as $k2=>$v2){
                                    $sku_goods = Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$v2['sku_id']])->find();
                                    $sku_goods['sku_prices'] = json_decode($sku_goods['sku_prices'],true);

                                    array_push($productList,[
                                        'platform'=>$goods['other_platform'],
                                        'productCount'=>$v2['goods_num'],
                                        'productLink'=>$goods['other_goods_link'],
                                        'productName'=>$goods['goods_name'],
                                        'productPrice'=>$sku_goods['sku_prices']['price'][0],#订购产品单价
                                        'skuCode'=>$sku_goods['goods_sn'],
                                        'spuCode'=>$goods['other_spuCode'],
                                        'productImage'=>$goods['goods_image'],
                                        'orderRemark'=>'买家：'.$user['custom_id']
                                    ]);
                                }
                            }
                        }

                        $res = $this->create_order(json_encode(['ordersn'=>$order['ordersn'],'createtime'=>$time*1000,'productList'=>json_encode($productList,true)],true));
                        if($res['code']==0){
                            Db::name('website_order_list')->where(['id'=>$order['id']])->update([
                                'other_ordersn'=>$res['data']['shopOrderNo'],
                                'issend'=>1
                            ]);

                            return json(['code'=>0,'msg'=>'已分流']);
                        }
                    }elseif($buyer['type']==2){
                        #人工买手
                        $buyer = Db::name('website_user')->where(['id'=>$buyer['uid']])->find();
                        common_notice([
                            'openid'=>$buyer['openid'],
                            'phone'=>$buyer['phone'],
                            'email'=>$buyer['email']
                        ],[
                            'msg'=>'订购清单['.$order['ordersn'].']待确认，点击链接查看：https://www.gogo198.net/?s=shop/shunt&gogo_id='.$buyer['custom_id'],
                            'opera'=>'待确认',
                            'url'=>'https://www.gogo198.net/?s=shop/shunt&gogo_id='.$buyer['custom_id']
                        ]);

                        return json(['code'=>0,'msg'=>'已分流']);
                    }
                }
                elseif($dat['shunt_type']==2){
                    #拒绝分流
                    Db::name('website_order_list')->where(['id'=>$id])->update(['status'=>-4]);
                    common_notice([
                        'openid'=>$user['openid'],
                        'phone'=>$user['phone'],
                        'email'=>$user['email']
                    ],[
                        'msg'=>'订购清单['.$order['ordersn'].']状态变更为[已取消]，点击链接查看：https://www.gogo198.cn/bill_detail?id='.$order['id'],
                        'opera'=>'拒绝订购，已取消',
                        'url'=>'https://shopping.gogo198.cn/bill_detail?id='.$order['id']
                    ]);

                    return json(['code'=>0,'msg'=>'已拒绝']);
                }
            }
            elseif($dat['send']==2){
                #撤销分流
                Db::name('website_order_list')->where(['id'=>$id])->update([
                    'buyer_id'=>0,
                    'status'=>-2
                ]);

                return json(['code'=>0,'msg'=>'已撤回分流']);
            }
            elseif($dat['send']==3){
                #确认有无货，通知买家
                if($order['status']==-9){
                    #有货，无修改
                    Db::name('website_order_list')->where(['id'=>$order['id']])->update([
                        'status'=>0,
                        'issend'=>1
                    ]);

                    common_notice([
                        'openid'=>$user['openid'],
                        'phone'=>$user['phone'],
                        'email'=>$user['email']
                    ],[
                        'msg'=>'订购清单['.$order['ordersn'].']确认有货，点击链接查看：https://www.gogo198.cn/cart.html?selected=1',
                        'opera'=>'确认有货，请勾选支付',
                        'url'=>'https://www.gogo198.cn/cart.html?selected=1'
                    ]);
                    return json(['code'=>0,'msg'=>'已通知买家']);
                }
                elseif($order['status']==-10){
                    #有货，有修改
                    Db::name('website_order_list')->where(['id'=>$order['id']])->update([
                        'status'=>0,
                        'issend'=>1
                    ]);

                    common_notice([
                        'openid'=>$user['openid'],
                        'phone'=>$user['phone'],
                        'email'=>$user['email']
                    ],[
                        'msg'=>'订购清单['.$order['ordersn'].']确认有货，点击链接查看：https://www.gogo198.cn/cart.html?selected=1',
                        'opera'=>'确认有货，请勾选支付',
                        'url'=>'https://www.gogo198.cn/cart.html?selected=1'
                    ]);
                    return json(['code'=>0,'msg'=>'已通知买家']);
                }
                elseif($order['status']==-11){
                    #无货
                    Db::name('website_order_list')->where(['id'=>$order['id']])->update([
                        'status'=>-4,
                        'issend'=>1
                    ]);

                    common_notice([
                        'openid'=>$user['openid'],
                        'phone'=>$user['phone'],
                        'email'=>$user['email']
                    ],[
                        'msg'=>'订购清单['.$order['ordersn'].']确认无货，点击链接查看：https://www.gogo198.cn/cart.html?selected=1',
                        'opera'=>'确认无货，已取消',
                        'url'=>'https://www.gogo198.cn/cart.html?selected=1'
                    ]);
                    return json(['code'=>0,'msg'=>'已通知买家']);
                }
            }
        }
        else{
            #账单状态
            $order['status_name'] = get_statusname($order['status']);

            #买手信息
            $buyer = Db::name('website_buyer')->where(['is_verify'=>1])->select();
            foreach($buyer as $k=>$v){
                if($v['type']==1){
                    $buyer[$k]['typename'] = '接口买家';
                }
                elseif($v['type']==2){
                    $buyer[$k]['typename'] = '自营买家';
                }
                elseif($v['type']==3){
                    $buyer[$k]['typename'] = '合作买家';
                }
            }

            #订购清单信息
            foreach($order['content']['goods_info'] as $k=>$v){
                $order['content']['goods_info'][$k]['goods_info'] = Db::connect($this->config)->name('goods')->where(['goods_id'=>$v['good_id']])->find();
                foreach($v['sku_info'] as $k2=>$v2){
                    $order['content']['goods_info'][$k]['sku_info'][$k2]['sku_info'] =  Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$v2['sku_id']])->find();
                }
            }
            $goods = $order['content']['goods_info'];
//            dd($address);
            return view('',compact('order','id','buyer_id','buyer','goods'));
        }
    }

    #查看买手编辑(商品规格参数+费用项目价格+商品数量)
    public function shunt_edit(Request $request){
        $dat = input();

        if(isset($dat['pa'])){
//            dd($dat);
            $id = intval($dat['id']);
            $gid = intval($dat['gid']);
            $gkey = intval($dat['gkey']);
            $skey = intval($dat['skey']);
            $sku_id = intval($dat['sku_id']);
            $cart_id = intval($dat['cart_id']);

            $edit_type = intval($dat['edit_type']);

            $order = Db::name('website_order_list')->where(['id'=>$id])->find();
            $order['content'] = json_decode($order['content'],true);
//            dd($order['content']);
            Db::startTrans();
            try {
                if($edit_type==1){
                    #商品规格参数

                    #旧的规格与商品价值
                    $order['content']['goods_info'][$gkey]['sku_info'][$skey]['odd_skuid']=$order['content']['goods_info'][$gkey]['sku_info'][$skey]['sku_id'];
                    $order['content']['goods_info'][$gkey]['sku_info'][$skey]['odd_price']=$order['content']['goods_info'][$gkey]['sku_info'][$skey]['price'];

                    #新的规格与商品价值
                    $new_skuid = intval($dat['new_skuid']);
                    $order['content']['goods_info'][$gkey]['sku_info'][$skey]['sku_id']=$new_skuid;

                    #新的规格参数
                    $new_skuinfo = Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$new_skuid])->find();
                    $new_skuinfo['sku_prices'] = json_decode($new_skuinfo['sku_prices'],true);
                    #商品信息
                    $goods = Db::connect($this->config)->name('goods')->where(['goods_id'=>$order['content']['goods_info'][$gkey]['good_id']])->find();
                    $goods_num = $order['content']['goods_info'][$gkey]['sku_info'][$skey]['goods_num'];
                    #计算新的规格参数下的商品单价*购买数量
                    if($goods['shop_id']>0){
                        #自营店铺
                        foreach($new_skuinfo['sku_prices']['start_num'] as $k=>$v){
                            if($new_skuinfo['sku_prices']['select_end'][$k]==1){
                                #区间
                                if($v<=$goods_num && $goods_num<=$new_skuinfo['sku_prices']['end_num']){
                                    $order['content']['goods_info'][$gkey]['sku_info'][$skey]['price'] = number_format($new_skuinfo['sku_prices']['price'][$k] * $goods_num,2);
                                }
                            }
                            elseif($new_skuinfo['sku_prices']['select_end'][$k]==2){
                                #以上
                                if($v<=$goods_num){
                                    $order['content']['goods_info'][$gkey]['sku_info'][$skey]['price'] = number_format($new_skuinfo['sku_prices']['price'][$k] * $goods_num,2);
                                }
                            }
                        }
                    }
                    elseif($goods['shop_id']==0){
                        #接口店铺
                        $order['content']['goods_info'][$gkey]['sku_info'][$skey]['price'] = number_format($new_skuinfo['sku_prices']['price'][0] * $goods_num,2);
                    }

                    #修改买家的选购清单
                    Db::connect($this->config)->name('cart_sku')->where(['sku_id'=>$sku_id,'cart_id'=>$cart_id])->update([
                        'sku_id'=>$new_skuid,
                        'attr_id'=>str_replace('|','_',$new_skuinfo['spec_vids']),
                        'spec_id'=>str_replace('|','_',$new_skuinfo['spec_ids']),
                        'price'=>$order['content']['goods_info'][$gkey]['sku_info'][$skey]['price']
                    ]);

                }
                elseif($edit_type==2){
                    #费用项目价格

                    #减免金额
                    if(isset($dat['new_reduction'])){
                        if($dat['new_reduction']==''){
                            $dat['new_reduction'] = 0;
                        }
                        $order['content']['goods_info'][$gkey]['odd_reduction_money'] = $order['content']['goods_info'][$gkey]['reduction_money'];
                        $order['content']['goods_info'][$gkey]['reduction_money'] = floatval($dat['new_reduction']);
                    }

                    #优惠随赠
                    if(isset($dat['new_gift'])){
                        if($dat['new_gift']==''){
                            $dat['new_gift'] = 0;
                        }
                        $order['content']['goods_info'][$gkey]['odd_gift_money'] = $order['content']['goods_info'][$gkey]['gift_money'];
                        $order['content']['goods_info'][$gkey]['gift_money'] = floatval($dat['new_gift']);
                    }

                    #其他费用
                    if(isset($dat['new_otherfee_total'])){
                        if($dat['new_otherfee_total']==''){
                            $dat['new_otherfee_total'] = 0;
                        }
                        $order['content']['goods_info'][$gkey]['odd_otherfee_total'] = $order['content']['goods_info'][$gkey]['otherfee_total'];
                        $order['content']['goods_info'][$gkey]['otherfee_total'] = floatval($dat['new_otherfee_total']);
                    }

                    #服务费用
                    if(isset($dat['new_services_money'])){
                        if($dat['new_services_money']==''){
                            $dat['new_services_money'] = 0;
                        }

                        $order['content']['goods_info'][$gkey]['services'] = json_decode($order['content']['goods_info'][$gkey]['services'],true);
                        $services_money = 0;
                        foreach($order['content']['goods_info'][$gkey]['services'] as $k2=>$v2){
                            $services = Db::connect($this->config)->name('goods_services')->where(['id'=>$v2['service_id']])->find();
                            if($v2['service_id']==1){
                                if($v2['photonum']>1){
                                    $services_money += $services['price'] + (($v2['photonum'] - 1) * $services['interval_price']);
                                }
                            }else{
                                $services_money += $services['price'];
                            }
                        }

                        $order['content']['goods_info'][$gkey]['odd_services_money'] = $services_money;
                        #新增“修改的服务金额”字段
                        $order['content']['goods_info'][$gkey]['services_money'] = floatval($dat['new_services_money']);
                        $order['content']['goods_info'][$gkey]['services'] = json_encode($order['content']['goods_info'][$gkey]['services'],true);
                    }
                }
                elseif($edit_type==3){
                    #商品数量
                    $new_num = intval($dat['new_num']);
                    if($new_num==0 || $new_num==''){
                        return json(['code'=>-1,'msg'=>'请输入新的商品数量']);
                    }
                    if($new_num==$order['content']['goods_info'][$gkey]['sku_info'][$skey]['goods_num']){
                        return json(['code'=>-1,'msg'=>'新的商品数量不能与之相同']);
                    }
                    $order['content']['goods_info'][$gkey]['sku_info'][$skey]['odd_goods_num']=$order['content']['goods_info'][$gkey]['sku_info'][$skey]['goods_num'];
                    $order['content']['goods_info'][$gkey]['sku_info'][$skey]['goods_num']=$new_num;

                    #当前规格
                    $new_skuinfo = Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$order['content']['goods_info'][$gkey]['sku_info'][$skey]['sku_id']])->find();
                    $new_skuinfo['sku_prices'] = json_decode($new_skuinfo['sku_prices'],true);
                    #商品信息
                    $goods = Db::connect($this->config)->name('goods')->where(['goods_id'=>$order['content']['goods_info'][$gkey]['good_id']])->find();
                    $goods_num = $order['content']['goods_info'][$gkey]['sku_info'][$skey]['goods_num'];
                    #计算新的规格参数下的商品单价*购买数量
                    if($goods['shop_id']>0){
                        #自营店铺
                        foreach($new_skuinfo['sku_prices']['start_num'] as $k=>$v){
                            if($new_skuinfo['sku_prices']['select_end'][$k]==1){
                                #区间
                                if($v<=$goods_num && $goods_num<=$new_skuinfo['sku_prices']['end_num']){
                                    $order['content']['goods_info'][$gkey]['sku_info'][$skey]['price'] = number_format($new_skuinfo['sku_prices']['price'][$k] * $goods_num,2);
                                }
                            }
                            elseif($new_skuinfo['sku_prices']['select_end'][$k]==2){
                                #以上
                                if($v<=$goods_num){
                                    $order['content']['goods_info'][$gkey]['sku_info'][$skey]['price'] = number_format($new_skuinfo['sku_prices']['price'][$k] * $goods_num,2);
                                }
                            }
                        }
                    }
                    elseif($goods['shop_id']==0){
                        #接口店铺
                        $order['content']['goods_info'][$gkey]['sku_info'][$skey]['price'] = number_format($new_skuinfo['sku_prices']['price'][0] * $goods_num,2);
                    }

                    #修改买家的选购清单
                    Db::connect($this->config)->name('cart_sku')->where(['sku_id'=>$sku_id,'cart_id'=>$cart_id])->update([
                        'goods_num'=>$order['content']['goods_info'][$gkey]['sku_info'][$skey]['goods_num'],
                        'price'=>$order['content']['goods_info'][$gkey]['sku_info'][$skey]['price'],
                    ]);
                }

                #重新计算订购清单价格
                $true_price = 0;
                foreach($order['content']['goods_info'] as $k=>$v){
                    #是否拿已修改的更多服务金额
                    if(isset($order['content']['goods_info'][$k]['services_money'])){
                        $services_money = $order['content']['goods_info'][$k]['services_money'];
                    }else{
                        $order['content']['goods_info'][$k]['services'] = json_decode($order['content']['goods_info'][$k]['services'],true);
                        $services_money = 0;
                        foreach($order['content']['goods_info'][$k]['services'] as $k2=>$v2){
                            $services = Db::connect($this->config)->name('goods_services')->where(['id'=>$v2['service_id']])->find();
                            if($v2['service_id']==1){
                                if($v2['photonum']>1){
                                    $services_money += $services['price'] + (($v2['photonum'] - 1) * $services['interval_price']);
                                }
                            }else{
                                $services_money += $services['price'];
                            }
                        }
                        $order['content']['goods_info'][$k]['services'] = json_encode($order['content']['goods_info'][$k]['services'],true);
                    }


                    $price = 0;
                    foreach($v['sku_info'] as $k2=>$v2){
                        $price += floatval($v2['price']);
                    }
                    $true_price += number_format($price + floatval($v['otherfee_total']) + $services_money - floatval($v['reduction_money']) - floatval($v['gift_money']),2);
                }

                //修改购物清单价格
                $odd_money = $order['true_money'];
                Db::name('website_order_list')->where(['id'=>$id])->update([
                    'odd_money'=>$odd_money,
                    'true_money'=>$true_price,
                    'content'=>json_encode($order['content'],true),
                ]);
                Db::commit();
                return json(['code' => 0, 'msg' => '修改成功']);
            } catch (\Exception $e) {
                Db::rollback();
                return json(['code' => -1, 'msg' => '操作失败：'.$e->getMessage()]);
            }
        }
        else{
            $is_manage = intval($dat['is_manage']);
            $arr = explode(',',$dat['arr']);
            $id = intval($arr[0]);
            $gid = intval($arr[1]);
            $gkey = intval($arr[2]);
            $skey = intval($arr[3])-1;
            $sku_id = intval($arr[4]);
            $cart_id = intval($arr[5]);

            $order = Db::name('website_order_list')->where(['id'=>$id])->find();
            $order['content'] = json_decode($order['content'],true);
//            foreach($order['content']['goods_info'] as $k=>$v){
//                $order['content']['goods_info'][$k]['services'] = json_encode($order['content']['goods_info'][$k]['services'],true);
//            }
//            Db::name('website_order_list')->where(['id'=>$id])->update([
//                'content'=>json_encode($order['content'],true)
//            ]);
//            dd($order['content']);

            $sku_data = $order['content']['goods_info'][$gkey]['sku_info'][$skey];
            $goods_data = $order['content']['goods_info'][$gkey];

            #商品规格
            $origin_skuinfo = Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$sku_data['sku_id']])->find()['spec_names'];
            $other_skuinfo = [];
            if(!empty($origin_skuinfo)){
                $other_skuinfo = Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$goods_data['good_id']])->select();
            }
//            dd($other_skuinfo);
            #费用项目
            #是否拿已修改的更多服务金额
            if(isset($goods_data['services_money'])){
                $services_money = $goods_data['services_money'];
            }else{
                $goods_data['services'] = json_decode($goods_data['services'],true);
                $services_money = 0;
                foreach($goods_data['services'] as $k2=>$v2){
                    $services = Db::connect($this->config)->name('goods_services')->where(['id'=>$v2['service_id']])->find();
                    if($v2['service_id']==1){
                        $goods_data['services'][$k2]['photoRequest'] = explode('@@@',rtrim($v2['photoRequest'],'@@@'));
                        if($v2['photonum']>1){
                            $services_money += $services['price'] + (($v2['photonum'] - 1) * $services['interval_price']);
                        }
                    }else{
                        $services_money += $services['price'];
                    }
                }
            }

            $origin_services = ['reduction_money'=>$goods_data['reduction_money'],'gift_money'=>$goods_data['gift_money'],'otherfee_total'=>$goods_data['otherfee_total'],'otherfee_currency'=>$goods_data['otherfee_currency'],'services_money'=>$services_money];

            return view('',compact('id','gid','gkey','skey','sku_id','cart_id','sku_data','origin_skuinfo','other_skuinfo','origin_services','is_manage'));
        }
    }

    #生成订单
    public function create_order($data){
        $res = httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/getgoods/create_order',$data,['Content-Type: application/json;charset=utf-8']);
        $res = json_decode($res,true);
        return $res;
    }

    public function del_buyer(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        $res = Db::name('website_buyer')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    public function purchase_history(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            // 连接数据库
//            $conn = Db::connect($this->config);
            $total = Db::name('website_order_list')->where(['origin_type'=>0,'buyer_id'=>$id])->count();
            $data = Db::name('website_order_list')
                ->where(['origin_type'=>0,'buyer_id'=>$id])
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
            return view('', ['id' => $id,'title'=>'']);
        }
    }

    public function shunt_index(Request $request)
    {
        $dat = input();

        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $keywords = input('search');
            $total = DB::name('website_shunter')->where('name','like','%'.$keywords.'%')->count();
            $data = DB::name('website_shunter')
                ->where('name','like','%'.$keywords.'%')
                ->order($order)
                ->limit($limit)
                ->select();

            foreach ($data as &$item) {
                $item['createtime'] = date('Y-m-d H:i', $item['createtime']);
                if($item['is_verify']==0){
                    $item['verify_name'] = '未验证';
                }elseif($item['is_verify']==1){
                    $item['verify_name'] = '已验证';
                }
            }

            return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
        } else {
            return view('', compact(''));
        }
    }

    public function save_shunt(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;

        if(isset($dat['pa'])){
            if($id>0){
                Db::name('website_shunter')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'phone'=>trim($dat['phone']),
                ]);
            }else{
                $time = time();
                $shunter_id = Db::name('website_shunter')->insertGetId([
                    'name'=>trim($dat['name']),
                    'phone'=>trim($dat['phone']),
                    'createtime'=>$time
                ]);

                #通知分流人员手机验证&注册账户
                send_msg(['phone'=>trim($dat['phone']),'email'=>''],['msg'=>'Gogo购购网邀请您成为分流人员，请关注并登录注册成为“Gogo購購網”小程序和“Gogo購購网”公众号的用户，以便收到业务通知，感谢您的使用！']);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>'','phone'=>''];
            if($id>0){
                $data = Db::name('website_shunter')->where(['id'=>$id])->find();
            }
            return view('', compact('id','data'));
        }
    }

    public function del_shunt(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        $res = Db::name('website_shunter')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    public function shunt_history(Request $request){
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
            return view('', ['title'=>'']);
        }
    }
}