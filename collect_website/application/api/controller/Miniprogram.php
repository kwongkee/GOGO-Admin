<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Validate;
use think\Log;

use AlibabaCloud\SDK\Ocrapi\V20210707\Ocrapi;
use \Exception;
use AlibabaCloud\Tea\Exception\TeaError;
use AlibabaCloud\Tea\Utils\Utils;
use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\SDK\Ocrapi\V20210707\Models\RecognizeGeneralRequest;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;


class Miniprogram extends Controller
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
    public $company_id = 33;#30 33
    public function index()
    {
        $data = input();
//        list($arg1,$agr2,$arg3) = array_values($myarr);

        if($data['method']=='send_code'){
            return $this->send_code($data);
        }elseif($data['method']=='crossborder_info'){
            return $this->crossborder_info($data);
        }elseif($data['method']=='login'){
            return $this->login($data);
        }elseif($data['method']=='phone_login'){
            return $this->phone_login($data);
        }elseif($data['method']=='auth_login'){
            return $this->auth_login($data);
        }elseif($data['method']=='get_openid'){
            return $this->get_openid($data);
        }elseif($data['method']=='get_info'){
            return $this->get_info($data);
        }elseif($data['method']=='get_info'){
            return $this->get_info($data);
        }elseif($data['method']=='get_inquiry_buss'){
            return $this->get_inquiry_buss($data);
        }elseif($data['method']=='get_gather_info'){
            return $this->get_gather_info($data);
        }elseif($data['method']=='get_agreement'){
            return $this->get_agreement($data);
        }elseif($data['method']=='set_user_openid'){
            return $this->set_user_openid($data);
        }elseif($data['method']=='set_user_info'){
            return $this->set_user_info($data);
        }elseif($data['method']=='apply_distr'){
            return $this->apply_distr($data);
        }elseif($data['method']=='manage_agent'){
            return $this->manage_agent($data);
        }elseif($data['method']=='check_agent'){
            return $this->check_agent($data);
        }elseif($data['method']=='get_business_url'){
            return $this->get_business_url($data);
        }elseif($data['method']=='get_my_goods'){
            return $this->get_my_goods($data);
        }elseif($data['method']=='get_unit'){
            return $this->get_unit($data);
        }elseif($data['method']=='save_goods'){
            return $this->save_goods($data);
        }elseif($data['method']=='get_goods'){
            return $this->get_goods($data);
        }elseif($data['method']=='get_ocr'){
            return $this->get_ocr($data);
        }elseif($data['method']=='arrange_txt'){
            return $this->arrange_txt($data);
        }elseif($data['method']=='get_category_child'){
            return $this->get_category_child($data);
        }elseif($data['method']=='del_goods'){
            return $this->del_goods($data);
        }elseif($data['method']=='get_invite_info'){
            return $this->get_invite_info($data);
        }elseif($data['method']=='save_staff'){
            return $this->save_staff($data);
        }elseif($data['method']=='get_navbar_info'){
            return $this->get_navbar_info($data);
        }elseif($data['method']=='get_activity_info'){
            return $this->get_activity_info($data);
        }elseif($data['method']=='get_shop_activity_info'){
            return $this->get_shop_activity_info($data);
        }elseif($data['method']=='join_activity'){
            return $this->join_activity($data);
        }elseif($data['method']=='get_campaign_list'){
            return $this->get_campaign_list($data);
        }elseif($data['method']=='get_campaign_detail'){
            return $this->get_campaign_detail($data);
        }elseif($data['method']=='get_activity_list'){
            return $this->get_activity_list($data);
        }elseif($data['method']=='get_activity_detail'){
            return $this->get_activity_detail($data);
        }elseif($data['method']=='scan_check_qualification'){
            return $this->scan_check_qualification($data);
        }elseif($data['method']=='get_task_tutorial'){
            return $this->get_task_tutorial($data);
        }elseif($data['method']=='get_shop_goods_category'){
            return $this->get_shop_goods_category($data);
        }elseif($data['method']=='get_shop_goods_detail'){
            return $this->get_shop_goods_detail($data);
        }elseif($data['method']=='create_order'){
            return $this->create_order($data);
        }elseif($data['method']=='submit_scan_payment'){
            return $this->submit_scan_payment($data);
        }elseif($data['method']=='get_cart_info'){
            return $this->get_cart_info($data);
        }elseif($data['method']=='update_order_quantity'){
            return $this->update_order_quantity($data);
        }elseif($data['method']=='createWechatPaymentOrder'){
            return $this->createWechatPaymentOrder($data);
        }elseif($data['method']=='handlePaymentNotify'){
            return $this->handlePaymentNotify($data);
        }elseif($data['method']=='update_payment_status'){
            return $this->update_payment_status($data);
        }elseif($data['method']=='get_order_list'){
            return $this->get_order_list($data);
        }elseif($data['method']=='get_order_status_count'){
            return $this->get_order_status_count($data);
        }elseif($data['method']=='cancel_order'){
            return $this->cancel_order($data);
        }elseif($data['method']=='generate_other_code'){
            return $this->generate_other_code($data);
        }elseif($data['method']=='get_shelf_info'){
            return $this->get_shelf_info($data);
        }elseif($data['method']=='get_quantity_price'){
            return $this->get_quantity_price($data);
        }elseif($data['method']=='edit_stock_price'){
            return $this->edit_stock_price($data);
        }elseif($data['method']=='get_orders_list'){
            return $this->get_orders_list($data);
        }elseif($data['method']=='update_order_info'){
            return $this->update_order_info($data);
        }elseif($data['method']=='check_seller'){
            return $this->check_seller($data);
        }elseif($data['method']=='printer_callback'){
            return $this->printer_callback($data);
        }elseif($data['method']=='jiami_data'){
            return $this->jiami_data($data);
        }elseif($data['method']=='complete_task'){
            return $this->complete_task($data);
        }elseif($data['method']=='get_campaign_orders_list'){
            return $this->get_campaign_orders_list($data);
        }elseif($data['method']=='get_dynamic_news_info'){
            return $this->get_dynamic_news_info($data);
        }elseif($data['method']=='check_paid'){
            return $this->check_paid($data);
        }elseif($data['method']=='print_order'){
            return $this->print_order($data);
        }
    }

    public function verifyTel($mobile){
        if(preg_match("/^1[34578]\d{9}$/", $mobile)){
            return true;
        }else{
            return false;
        }
    }

    public function send_code($data){
        $dat = &$data;
        $code = mt_rand(11, 99) . mt_rand(11, 99) . mt_rand(11, 99);
        // session('login_code',$code);

        if($dat['reg_act']==0){
            #手机号码
            $tel = trim($dat['number']);
            if(!$this->verifyTel($tel)){
                return json(['code'=>-1,'msg'=>'手机格式错误！']);
            }
            
            $post_data = [
                'mobiles'=>$tel,
                'content'=>'您正在登录GOGO购购网，手机验证码为：'.$code.'【GOGO】',
            ];
            $post_data = json_encode($post_data,true);
            $res = httpRequest('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length:' . strlen($post_data),
                'Cache-Control: no-cache',
                'Pragma: no-cache'
            ));
        }elseif($dat['reg_act']==1){
            #邮箱
            // 创建一个验证实例
            $validate = new Validate();
            // 添加一个邮箱规则
            $validate->rule('email', 'require|email');
            // 进行验证
            $result = $validate->check(['email' => trim($dat['number'])]);
            if (!$result) {
                return json(['code'=>-1,'msg'=>'邮箱格式错误！']);
            }
            $res=cklein_mailAli(trim($dat['number']), '尊敬的客户', '登录Gogo购购网', '验证码：'.$code.'，您正在登录Gogo购购网。');
//            return json(['code'=>0,'msg'=>'发送成功！','code2'=>$code]);

//             $res = httpRequest('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>trim($dat['number']),'title'=>'登录Gogo购购网','content'=>'验证码：'.$code.'，您正在登录Gogo购购网。']);//用不了
        }
        
        if($res){
            return json(['code'=>0,'msg'=>'发送成功！','code2'=>$code]);
        }else{
            return json(['code'=>-1,'msg'=>'发送失败，请联系管理员！']);
        }
    }

    #跨境购物链接
    public function crossborder_info($data){
        $link = [
//            'https://www.gogo198.com/?s=gather/cross_shopping&miniprogram=1',
//            'https://www.gogo198.com/?s=gather/cross_shopping&miniprogram=1',
//            'https://www.gogo198.com/?s=gather/cross_shopping&miniprogram=1',
//            '',
//            '',
//            '',
//            '',
//            '',
//            'https://www.gogo198.net/?s=index/detail&id=86&miniprogram=1',
//            'https://www.gogo198.net/?s=index/detail&id=74&miniprogram=1',
//            'https://www.gogo198.net/?s=index/detail&id=62&miniprogram=1',
        ];

        $info = Db::name('centralize_miniprogram_index')->where(['page_id'=>$data['type']])->find();
        if(!empty($info['content'])){
            $info['content'] = json_decode($info['content'],true);
            $info['navlink'] = json_decode($info['navlink'],true);
            $info['navtype'] = json_decode($info['navtype'],true);
            $info['navpage'] = json_decode($info['navpage'],true);
            foreach($info['navtype'] as $k=>$v){
                if($v==2){
                    $info['navtype2'][$k] = Db::name('centralize_miniprogram_page')->where(['url'=>$info['navlink'][$k]])->find()['type'];
                }else{
                    $info['navtype2'][$k] = 1;
                }
            }
        }

        return json(['code'=>0,'info'=>$link,'info2'=>$info]);
    }

    #小程序账号登录(废弃)
    public function login($data){
        $dat = &$data;
        
        if(isset($dat['email'])){
            $dat['number'] = trim($dat['email']);    
        }elseif(isset($dat['phone'])){
            $dat['number'] = trim($dat['phone']);  
        }
        
        if($dat['number']!='947960547@qq.com' && $dat['number']!='13119893380' && $dat['number']!='13119893381' && $dat['number']!='947960542@qq.com' && $dat['number']!='947960543@qq.com' && $dat['number']!='13119893382'&& $dat['number']!='13809703680' && $dat['number']!='13809703681' && $dat['number']!='hejunxin@gogo198.net'){
            if($dat['login_code']!=trim($dat['code'])){
                return json(['code'=>-1,'msg'=>'验证码不正确！']);
            }    
        }
        $number = trim($dat['number']);
        if($dat['reg_method']==0){
            $account = Db::name('website_user')->where('phone',$number)->find();
        }elseif($dat['reg_method']==1){
            $account = Db::name('website_user')->where('email',$number)->find();
        }
        $fans = Db::name('mc_mapping_fans')->where(['unionid'=>$dat['unionid']])->find();

        if(empty($account)){
            #无感注册
            $time = time();
            $arr = ['phone'=>$dat['reg_method']==0?$number:'', 'email'=>$dat['reg_method']==1?$number:'', 'sns_openid'=>$dat['openid'], 'times'=>1, 'createtime'=>$time];
            if(!empty($fans)){
                if($fans['follow']==1){
                    $arr = array_merge($arr,['openid'=>$fans['openid']]);
                }
            }
            $insertid = Db::name('website_user')->insertGetId($arr);
            $custom_id = 'GG'.date('YmdHis',$time).str_pad($insertid, 3, '0', STR_PAD_LEFT);
            $res = Db::name('website_user')->where('id',$insertid)->update(['custom_id'=>$custom_id]);
            
            if($res){
                #赋予账号
                $account = Db::name('website_user')->where('id',$insertid)->find();
                #集运网账号
                Db::name('centralize_user')->insert([
                    'name'=>$account['nickname'],
                    'realname'=>$account['realname'],
                    'email'=>$account['email'],
                    'pwd'=>md5('888888'),
                    'mobile'=>$account['phone'],
                    'status'=>0,
                    'agentid'=>$account['agent_id'],
                    'gogo_id'=>$account['custom_id'],
                    'createtime'=>$time,
                ]);
                #买全球账号
                Db::name('sz_yi_member')->insert([
                    'uniacid'=>3,
                    'realname'=>$account['realname'],
                    'nickname'=>$account['nickname'],
                    'mobile'=>$account['phone']!=''?$account['phone']:$account['email'],
                    'pwd'=>md5('888888'),
                    'id_card'=>$account['idcard'],
                    'gogo_id'=>$account['custom_id'],
                    'createtime'=>$time,
                ]);
                #卖全球账号
                Db::name('sz_yi_member')->insert([
                    'uniacid'=>18,
                    'realname'=>$account['realname'],
                    'nickname'=>$account['nickname'],
                    'mobile'=>$account['phone']!=''?$account['phone']:$account['email'],
                    'pwd'=>md5('888888'),
                    'id_card'=>$account['idcard'],
                    'gogo_id'=>$account['custom_id'],
                    'createtime'=>$time,
                ]);
                #新的商城
                $config = [
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
                Db::connect($config)->name('user')->insert([
                    'role_id'=>0,
                    'gogo_id'=>$custom_id,
                    'user_name'=>$account['realname'],
                    'nickname'=>$account['nickname'],
                    'password'=>'$2y$10$Nbq/GtGDT6wjbs6e7WhJ0Ox2EaWQ0ANcpayPi9bFLQQ6B3rEEeHx2',//6个8
                    'mobile'=>$account['phone'],
                    'email'=>$account['email'],
                    'status'=>1,
                    'shopping_status'=>1,
                    'comment_status'=>1,
                    'created_at'=>date('Y-m-d H:i:s',$time)
                ]);
            }
            
            #通知用户
            if($dat['reg_method']==1){
                #手机
                $post_data = [
                    'mobiles'=>$number,
                    'content'=>'尊敬的客户，您好！您已成功注册成为购购网会员，感谢您的支持！【GOGO】',
                ];
                $post_data = json_encode($post_data,true);
                httpRequest('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length:' . strlen($post_data),
                    'Cache-Control: no-cache',
                    'Pragma: no-cache'
                ));
            }elseif($dat['reg_method']==2){
                #邮箱             
                cklein_mailAli($number, '尊敬的客户', '登录Gogo购购网', '尊敬的客户，您好！您已成功注册成为购购网会员，感谢您的支持！');
                // httpRequest('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$number,'title'=>'购购网','content'=>'尊敬的客户，您好！您已成功注册成为购购网会员，感谢您的支持！']);
            }
            // return json(['code'=>-1,'msg'=>'账户不正确！']);
        }else{
            $arr = ['sns_openid'=>$dat['openid'],'unionid'=>$dat['unionid']];
            if(!empty($fans)){
                if($fans['follow']==1 && $account['openid']==''){
                    $arr = array_merge($arr,['openid'=>$fans['openid']]);
                }
            }
            Db::name('website_user')->where(['id'=>$account['id']])->update($arr);
        }

        #如果是询价跳转过来的（废弃）
        if(isset($dat['inquiry_id'])){
            $res = Db::name('website_inquiry_order')->where(['id'=>intval($dat['inquiry_id'])])->update(['uid'=>$account['id']]);
            #通知O端
            if($res){
                $system = Db::name('centralize_system_notice')->where(['uid'=>0])->find();
                $ordersn = Db::name('website_inquiry_order')->where(['id'=>intval($dat['inquiry_id'])])->find()['ordersn'];
                if($system['notice_type']==1){
                    #微信
                    $post = json_encode([
                        'call'=>'confirmCollectionNotice',
                        'first' =>'有新的询价单['.$ordersn.']，请打开查看！',
                        'keyword1' => '有新的询价单['.$ordersn.']，请打开查看！',
                        'keyword2' => '已提交待分享报价',
                        'keyword3' => date('Y-m-d H:i:s',time()),
                        'remark' => '点击查看详情',
                        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=check_detail&ordersn='.$ordersn,
                        'openid' => $system['account'],
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]);
    
                    httpRequest('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                }elseif($system['notice_type']==3){
                    #邮箱通知
                    httpRequest('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$system['account'],'title'=>'客户['.session('account.custom_id').']发起询价','content'=>'请登录总后台，进入询价管理中心进行查看：https://gadmin.gogo198.cn/']);
                }
            }
        }
        
        // session('account',$account);
        
        return json(['code'=>0,'msg'=>'登录成功！','uid'=>$account['id'],'sns_openid'=>$dat['openid'],'unionid'=>$dat['unionid']]);
    }

    #微信手机号登录
    public function phone_login($data){
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx6d1af256d76896ba&secret=d19a96d909c1a167c12bb899d0c10da6";
        $res = file_get_contents($url);
        $result = json_decode($res, true);

        $url2 = 'https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token='.$result["access_token"];
        $res2 = httpRequest($url2, json_encode(['code'=>$data['code']]),'',1);
        $result2 = json_decode($res2, true);
        $phone = $result2['phone_info']['phoneNumber'];
        $account = Db::name('website_user')->where('phone',$phone)->find();
        $fans = Db::name('mc_mapping_fans')->where(['unionid'=>$data['unionid']])->find();

        if(empty($account)){
            #无感注册
//            $time = time();
            $arr = ['phone'=>$phone, 'sns_openid'=>$data['openid'], 'area_code'=>162, 'unionid'=>$data['unionid']];
            if(!empty($fans)){
                if($fans['follow']==1){
                    $arr = array_merge($arr,['openid'=>$fans['openid']]);
                }
            }

            $new_account_id = Db::name('sz_yi_member_login_log')->insertGetId(['login_ip'=>json_encode($arr,true)]);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://shop.gogo198.cn/collect_website/public/?s=api/func/generate_member"); // 目标URL
            curl_setopt($ch, CURLOPT_POST, 1); // 设置为POST请求
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arr); // POST数据
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 将响应结果作为字符串返回
            $account_id = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);die;
            }
            curl_close($ch);
            Db::name('sz_yi_member_login_log')->insert(['login_ip'=>$account_id]);

            $account = Db::name('website_user')->where('id',$account_id)->find();
            Db::name('sz_yi_member_login_log')->where(['id'=>$new_account_id])->delete();
//            Db::name('sz_yi_member_login_log')->insert(['login_ip'=>json_encode($account,true)]);

            #通知用户手机
//            $post_data = [
//                'mobiles'=>$phone,
//                'content'=>'尊敬的客户，您好！您已成功注册成为购购网会员，感谢您的支持！【GOGO】',
//            ];
//            $post_data = json_encode($post_data,true);
//            httpRequest('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
//                'Content-Type: application/json; charset=utf-8',
//                'Content-Length:' . strlen($post_data),
//                'Cache-Control: no-cache',
//                'Pragma: no-cache'
//            ));
        }
        else{
            $arr = ['sns_openid'=>$data['openid'],'unionid'=>$data['unionid']];
            if(!empty($fans)){
                if($fans['follow']==1 && $account['openid']==''){
                    $arr = array_merge($arr,['openid'=>$fans['openid']]);
                    Db::name('sz_yi_member')->where(['gogo_id'=>$account['custom_id']])->update(['openid'=>$fans['openid']]);
                    Db::name('decl_user')->where(['gogo_id'=>$account['custom_id']])->update(['openid'=>$fans['openid']]);
                }
            }
            Db::name('website_user')->where(['id'=>$account['id']])->update($arr);
        }

        #总后台运营人员认证
        Db::name('foll_user')->where(['tel'=>$phone])->update(['user_id'=>$account['id']]);

        #分流人员认证
        Db::name('website_buyer')->where(['phone'=>$phone])->update(['uid'=>$account['id'],'is_verify'=>1]);
        Db::name('website_shunter')->where(['phone'=>$phone])->update(['uid'=>$account['id'],'is_verify'=>1]);

        return json(['code'=>0,'msg'=>'登录成功！','uid'=>$account['id'],'sns_openid'=>$data['openid'],'unionid'=>$data['unionid']]);
    }

    #授权登录
    public function auth_login($data){
        $res = Db::name('website_authlogin')->where([
            'id'=>intval($data['auth_id'])
        ])->update([
            'uid'=>intval($data['uid']),
            'status'=>intval($data['status'])
        ]);

        return json(['code'=>0,'msg'=>'操作成功']);
    }

    #获取小程序openid
    public function get_openid($data){
        $url = 'https://api.weixin.qq.com/sns/jscode2session';
        $appid = 'wx6d1af256d76896ba';
        $secret = 'd19a96d909c1a167c12bb899d0c10da6';
        $js_code = $data['code'];
        $grant_type = 'authorization_code';
        $res = file_get_contents($url.'?appid='.$appid.'&secret='.$secret.'&js_code='.$js_code.'&grant_type='.$grant_type);
        $res = json_decode($res,true);
        return json(['code'=>0,'openid'=>$res['openid'],'unionid'=>$res['unionid']]);
//        return json(['code'=>0,'openid'=>'oST8o42E_k8ye6CeO5uG19jud7H8']);
    }

    #获取用户信息
    public function get_info($data){
        $acc = Db::name('website_user')->where(['id'=>$data['uid']])->find();
        return json(['code'=>0,'acc'=>$acc]);
    }

    public function get_inquiry_buss($data){
        $list = Db::name('website_bussiness')->select();
        return json(['code'=>0,'list'=>$list]);
    }

    #获取集运网信息
    public function get_gather_info($data){
        #获取集运链接、网址配色
        $website = Db::name('website_basic')->where(['id'=>2])->find();
        if(isset($data['nav_id'])){
            if($data['nav_id']>0){
                #二维码进入
                $website['url'] = 'https://gather.gogo198.cn/?s=gather/package_info&id='.$data['nav_id'].'&process1=19&process2=21&process3=21&miniprogram=1';
            }else{
                #模板消息进入
                $website['url'] = 'https://gather.gogo198.cn/?s=gather/package_manage&manage=1&process1=16&process2=18&process3=18&miniprogram=1';
            }
        }else{
            #导航栏进入
            $website['url'] = 'https://gather.gogo198.cn/?s=gather/package_forecast&process1=16&process2=17&miniprogram=1';
        }
        #小程序模板
//        $website['template_id'] = ['GRa2BGkGrqU8g7IgMAVh6vx2iDD08uJSdK316TINQ7s'];
        $msg_temp = Db::name('centralize_miniprogram_message')->field('tmp_code')->select();
        $msg_temp2 = [];
        foreach($msg_temp as $k=>$v){
            array_push($msg_temp2,$v['tmp_code']);
        }
        $website['template_id'] = &$msg_temp2;
        return json(['code'=>0,'info'=>$website]);
    }

    #获取页面协议
    public function get_agreement($data){
        $list = Db::name('centralize_miniprogram_agreement')->where(['id'=>intval($data['id'])])->find();
        $list['content'] = json_decode($list['content'],true);

        # 小程序logo
        $head = Db::name('miniprogram_basicinfo')->where(['id'=>1])->find();
        $head['logo'] = 'https://shop.gogo198.cn/'.$head['logo'];

        return json(['code'=>0,'info'=>$list,'logo'=>$head['logo']]);
    }

    #设置平台账户的openid
    public function set_user_openid($data){
        if($data['uid']>0){
            $user = Db::name('website_user')->where(['id'=>intval($data['uid'])])->find();
            $user['nickname'] = empty($user['nickname'])?'微信用户':$user['nickname'];

            #获取当前账户的企业所有权限
            $manage_person = Db::name('centralize_manage_person')->where(['gogo_id'=>intval($data['uid']),'status'=>1])->order('id desc')->find();
            $auths = '';
            if($manage_person['pid']==0 && $manage_person['role_id']==7){
                #超级用户
                $auths = '304';
            }else{
                $manage_level = Db::name('centralize_manage_level')->where(['id'=>$manage_person['role_id']])->find();
                if(strstr($manage_level['authList'],'304') !== false){
                    $auths = '304';
                }
            }

            if(empty($user['openid'])){
                $fans = Db::name('mc_mapping_fans')->where(['unionid'=>$user['unionid']])->find();
                if(!empty($fans)){
                    if($fans['follow']==1){
                        Db::name('website_user')->where(['id'=>intval($data['uid'])])->update(['openid'=>$fans['openid']]);
                    }else{
                        return json(['code'=>0,'follow'=>0,'gogoId'=>$user['custom_id'],'username'=>$user['nickname'],'auths'=>$auths]);
                    }
                }else{
                    return json(['code'=>0,'follow'=>0,'gogoId'=>$user['custom_id'],'username'=>$user['nickname'],'auths'=>$auths]);
                }
            }

            if($data['agentid']>0){
                if($user['id']!=$data['agentid']){
                    if(empty($user['agent_id'])){
                        Db::name('website_user')->where(['id'=>intval($data['uid'])])->update(['agent_id'=>intval($data['agentid'])]);
                    }
                }
            }

            return json(['code'=>0,'follow'=>1,'gogoId'=>$user['custom_id'],'username'=>$user['nickname'],'auths'=>$auths]);
        }
    }

    #用户基本信息
    public function set_user_info($data){
        $res = Db::name('website_user')->where(['id'=>$data['uid']])->update([
            'email'=>trim($data['email']),
            'nickname'=>trim($data['nickname']),
            'realname'=>trim($data['realname']),
            'phone'=>trim($data['phone']),
        ]);

        if($res){
            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            return json(['code'=>-1,'msg'=>'保存失败']);
        }
    }

    #申请分销商
    public function apply_distr($data){
        $agent = Db::name('website_user')->where(['phone'=>trim($data['number'])])->find();
        if(empty($agent)){
            return json(['code'=>-1,'msg'=>'此手机号码找不到上级']);
        }

        $res = Db::name('website_user')->where('id',$data['uid'])->update([
            'agent_id'=>$agent['id'],
            'agent_content'=>json_encode(['category'=>intval($data['category'])],true),
            'agent_status'=>1,
            'agent_remark'=>'',
            'applytime'=>time()
        ]);

        if($res){
            $user = Db::name('website_user')->where('id',$data['uid'])->find();
            $this->notice('用户['.$user['custom_id'].']已提交申请分销商，请知悉！',$agent,'分销商申请','已提交','pages/my_page/manage_distribute/index');
            return json(['code'=>0,'msg'=>'提交申请成功，请等待审核。']);
        }else{
            return json(['code'=>-1,'msg'=>'提交申请失败。']);
        }
    }

    #管理分销商
    public function manage_agent($data){
        #未审核
        $status0 = Db::name('website_user')->where(['agent_id'=>$data['uid'],'agent_status'=>1])->select();
        #已审核
        $status1 = Db::name('website_user')->where(['agent_id'=>$data['uid'],'agent_status'=>2])->select();
        #已拒绝
        $status_1 = Db::name('website_user')->where(['agent_id'=>$data['uid'],'agent_status'=>-1])->select();
        $category = [['id'=>1,'name'=>'物流服务'],['id'=>2,'name'=>'仓储服务'],['id'=>3,'name'=>'电商服务']];
        $list = array_merge([['arr'=>$status0]],[['arr'=>$status1]],[['arr'=>$status_1]]);
//        dd($list);
        foreach($list as $k=>$v){
            if(!empty($v['arr'])){
                foreach($v['arr'] as $kk=>$vv){
                    $list[$k]['arr'][$kk]['agent_content'] = json_decode($vv['agent_content'],true);
                    foreach($category as $k2=>$v2){
                        if($list[$k]['arr'][$kk]['agent_content']['category'] == $v2['id']){
                            $list[$k]['arr'][$kk]['business'] = $v2['name'];
                        }
                    }
                    $list[$k]['arr'][$kk]['applytime'] = date('Y-m-d H:i',$vv['applytime']);
                }
            }
        }
        $user = Db::name('website_user')->where(['id'=>$data['uid']])->find();
        $user['agent_content'] = json_decode($user['agent_content'],true);
        return json(['code'=>0,'list'=>$list,'user'=>$user]);
    }

    #审核分销商
    public function check_agent($data){
        $msg = '';$res = '';
        if($data['pa']=='refuse'){
            #拒绝申请
            $res = Db::name('website_user')->where(['id'=>intval($data['uid'])])->update([
                'agent_remark'=>trim($data['contents']),
                'agent_status'=>-1,
            ]);
            $msg = '拒绝';
        }elseif($data['pa']=='pass'){
            #通过申请
            $res = Db::name('website_user')->where(['id'=>intval($data['uid'])])->update([
                'agent_remark'=>'',
                'agent_status'=>2,
                'agent_content'=>json_encode(['type'=>intval($data['typp']),'trade_amount'=>trim($data['trade_amount']),'category'=>intval($data['category'])],true),
                'agenttime'=>time()
            ]);
            $msg = '通过';
        }

        if($res){
            $acc = Db::name('website_user')->where(['id'=>intval($data['uid'])])->find();
            $this->notice('您提交的分销申请已被'.$msg,$acc,'分销申请','已'.$msg,'pages/my_page/distribute_info/index');
            return json(['code'=>0,'msg'=>'操作成功！']);
        }
    }

    public function notice($task_name,$acc,$taskname,$opera,$page){
//        $data = Db::name('centralize_system_notice')->where(['uid'=>0,'system_type'=>1])->find();

        $url = '';

        if(!empty($acc['sns_openid'])){
            #小程序
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx6d1af256d76896ba&secret=d19a96d909c1a167c12bb899d0c10da6";
            $res = file_get_contents($url);
            $result = json_decode($res, true);

            $post2 = json_encode([
                'template_id'=>'GRa2BGkGrqU8g7IgMAVh6vx2iDD08uJSdK316TINQ7s',
                'page'=>$page,
                'touser' =>$acc['sns_openid'],
                'data'=>['thing1'=>['value'=>$taskname],'phrase2'=>['value'=>$opera],'time4'=>['value'=>date('Y年m月d日 H:i')]],
                'miniprogram_state'=>'formal',
                'lang'=>'zh_CN',
            ]);
            httpRequest('https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token='.$result['access_token'], $post2,['Content-Type:application/json'],1);
        }elseif(!empty($acc['openid'])){
            #微信
            $post = json_encode([
                'call'=>'confirmCollectionNotice',
                'find' =>$task_name,
                'keyword1' => $task_name,
                'keyword2' => '已提交',
                'keyword3' => date('Y-m-d H:i:s',time()),
                'remark' => '点击查看详情',
                'url' => $url,
                'openid' => $acc['openid'],
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]);

            httpRequest('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post,'',1);
        }elseif(!empty($acc['email'])){
            $post_data = json_encode(['email'=>$acc['email'],'title'=>$task_name,'content'=>$url],true);
            httpRequest('https://admin.gogo198.cn/collect_website/public/?s=api/sendemail/index',$post_data,array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length:' . strlen($post_data),
                'Cache-Control: no-cache',
                'Pragma: no-cache'
            ));
        }elseif(!empty($acc['phone'])){
            $post_data = [
                'mobiles'=>$acc['phone'],
                'content'=>$task_name.' 【GOGO】',
            ];
            $post_data = json_encode($post_data,true);
            httpRequest('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length:' . strlen($post_data),
                'Cache-Control: no-cache',
                'Pragma: no-cache'
            ));
        }
    }

    /**
     * 获取业务链接
     * type:
     * 1 查看分销账单
     * 2 修改分销账单
     * 3 查看客服中心
     **/
    public function get_business_url($data){
        $url = '';
        if($data['type']==1){
            $url = 'https://www.gogo198.net/?s=index/view_recon&id='.$data['id'].'&miniprogram=1';
        }elseif($data['type']==2){
            $url = 'https://www.gogo198.net/?s=index/save_recon&id='.$data['id'].'&miniprogram=1';
        }elseif($data['type']==3){
            #平台客服链接
            $url = 'https://admin.gogo198.cn/collect_website/public/?s=admin/shopping/chat_manage&sort=id&order=desc&limit=10&offset=0&id='.$data['id'].'&miniprogram=1';
        }elseif($data['type']==4){
            #商家客服链接
            $split_id = explode('_',$data['id']);#分拆企业id和商家管理员id
            $url = 'https://rte.gogo198.cn/?s=merchant/merchant_center&cid='.base64_encode($split_id[0]).'&id='.base64_encode($split_id[1]).'&miniprogram=1';
        }

        return json(['code'=>0,'url'=>$url]);
    }

    #获取我的商品
    public function get_my_goods($data){
        $mid = intval($data['mid']);
        $status = intval($data['status']);
        $list = Db::name('website_user_goods_list')->where(['uid'=>$mid,'status'=>$status])->order('id desc')->field('id,name')->select();
        return json(['code'=>0,'list'=>$list]);
    }

    #获取单位
    public function get_unit($data){
        #单位
        $list = Db::name('unit')->select();
        #币种
        $list2 = Db::name('centralize_currency')->select();
        #选品渠道
        $list3 = Db::name('sale_unit')->select();
        array_push($list3,['id'=>-1,'name'=>'其他渠道']);
        #商品类别
        $list4 = Db::connect($this->config)->name('category')->whereRaw('parent_id=0 and cat_id>=17 and cat_id<=26')->select();
        array_unshift($list4,['cat_id'=>0,'cat_name'=>'请选择']);
        #国家地区
        $list5 = Db::name('centralize_diycountry_content')->where(['pid'=>5])->field('id,param2')->select();
        $list5_key = 44;#国家索引
        $list5_key2 = 2;#币种索引
        return json(['code'=>0,'list'=>$list,'list2'=>$list2,'list3'=>$list3,'list4'=>$list4,'list5'=>$list5,'list5_key'=>$list5_key,'list5_key2'=>$list5_key2]);
    }

    #获取子商品分类
    public function get_category_child($data){
        $list = Db::connect($this->config)->name('category')->where(['parent_id'=>$data['parent_id']])->select();
        array_unshift($list,['cat_id'=>0,'cat_name'=>'请选择']);
        return json(['code'=>0,'list'=>$list]);
    }

    #保存商品
    public function save_goods($data){
        $id = isset($data['id'])?$data['id']:0;
        $type = $data['type'];

        if(empty($data['name'])){
            return json(['code'=>-1,'msg'=>'请输入商品名称']);
        }

        if($type!=2) {
            #商品调研&修改商品
            if (empty($data['pic_list'])) {
                return json(['code' => -1, 'msg' => '请上传商品图片']);
            }
        }

        #选品渠道自定义
        if(isset($data['sale_unit'])){
            if($data['sale_unit'] == -1){
                $ishave = Db::name('sale_unit')->where(['name'=>$data['diy_sale_unit']])->find();
                if(!empty($ishave)){
                    $data['sale_unit'] = $ishave['id'];
                }else{
                    $data['sale_unit'] = Db::name('sale_unit')->insertGetId([
                        'name'=>trim($data['diy_sale_unit'])
                    ]);
                }
            }
        }

        $res = '';
        $cate_ids = '';
        if($type==2){
            #整理入库
            if($data['cat1']>0){$cate_ids .= $data['cat1'];}
            if($data['cat2']>0){$cate_ids .= ','.$data['cat2'];}
            if($data['cat3']>0){$cate_ids .= ','.$data['cat3'];}
        }

        if($id>0){
            if($type==2){
                #整理入库
                $res = Db::name('website_user_goods_list')->where(['id'=>$id,'uid'=>$data['uid']])->update([
                    'name'=>trim($data['name']),
                    'cate_ids'=>trim($cate_ids),
//                    'pic_list'=>json_encode(explode(',',$data['pic_list']),true),
//                    'desc'=>trim($data['desc']),
                    'desc_list'=>json_encode(explode(',',$data['desc_list']),true),
//                    'unit'=>trim($data['unit']),
//                    'currency'=>trim($data['currency']),
//                    'price'=>intval($data['price']),
//                    'sale_unit'=>intval($data['sale_unit']),
                    'option_list'=>$data['option_list'],
                    'spec_list'=>$data['spec_list'],
                    'status'=>1
                ]);
            }
            elseif($type==1){
                #修改信息

                $res = Db::name('website_user_goods_list')->where(['id'=>$id,'uid'=>$data['uid']])->update([
                    'name'=>trim($data['name']),
                    'country_id'=>intval($data['country_id']),
//                    'cate_ids'=>trim($cate_ids),
                    'pic_list'=>json_encode(explode(',',$data['pic_list']),true),
                    'desc'=>trim($data['desc']),
//                    'desc_list'=>json_encode(explode(',',$data['desc_list']),true),
                    'unit'=>trim($data['unit']),
                    'currency'=>trim($data['currency']),
                    'price'=>intval($data['price']),
                    'sale_unit'=>intval($data['sale_unit']),
//                    'option_list'=>$data['option_list'],
//                    'spec_list'=>$data['spec_list'],
                ]);
            }
        }else{
            $option_list = [['option_name'=>'','option_desc'=>'']];
            $spec_list = [['spec_name'=>'','spec_desc'=>'']];

            $buyer = Db::name('website_buyer')->where(['uid'=>$data['uid']])->field('id')->find();

            $res = Db::name('website_user_goods_list')->insert([
                'uid'=>$data['uid'],
                'buyer_id'=>$buyer['id'],
                'name'=>trim($data['name']),
                'country_id'=>intval($data['country_id']),
//                'cate_ids'=>trim($cate_ids),
                'pic_list'=>json_encode(explode(',',$data['pic_list']),true),
                'desc'=>trim($data['desc']),
                'desc_list'=>json_encode(explode(',',$data['desc_list']),true),
                'unit'=>trim($data['unit']),
                'currency'=>trim($data['currency']),
                'price'=>intval($data['price']),
                'sale_unit'=>intval($data['sale_unit']),
                'option_list'=>json_encode($option_list,true),
                'spec_list'=>json_encode($spec_list,true),
                'createtime'=>time()
            ]);
        }

        if($res){
            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            return json(['code'=>-1,'msg'=>'保存失败']);
        }
    }

    #获取商品信息
    public function get_goods($data){
        $list = Db::name('website_user_goods_list')->where(['uid'=>$data['mid'],'id'=>$data['id']])->field('name,country_id,cate_ids,pic_list,desc_list,desc,unit,currency,price,sale_unit,option_list,spec_list')->find();
        $list['pic_list'] = json_decode($list['pic_list'],true);
        if(!empty($list['cate_ids'])){
            $list['cate_ids'] = explode(',',$list['cate_ids']);
            $list['cate_name'] = '';
            foreach($list['cate_ids'] as $k=>$v){
                $category = Db::connect($this->config)->name('category')->where(['cat_id'=>$v])->field('cat_name')->find();
                $list['cate_name'] .= $category['cat_name'].',';
            }
            $list['cate_name'] = trim($list['cate_name'],',');
        }
        if(!empty($list['desc_list'])){
            $list['desc_list'] = json_decode($list['desc_list'],true);

            if(isset($list['desc_list'][0])){
                if(empty($list['desc_list'][0])){
                    $list['desc_list'] = [];
                }
            }
        }
        if(!empty($list['option_list'])){
            $list['option_list'] = json_decode($list['option_list'],true);
        }
        if(!empty($list['spec_list'])){
            $list['spec_list'] = json_decode($list['spec_list'],true);
        }

        return json(['code'=>0,'list'=>$list]);
    }

    #删除商品
    public function del_goods($data){
        $res = Db::name('website_user_goods_list')->where(['id'=>$data['id']])->update(['status'=>-1]);

        if($res){
            return json(['code'=>0,'msg'=>'删除入库成功']);
        }
    }

    #整理字段给“商品型号”和“商品参数”
    public function arrange_txt($data){
        $type = $data['type'];
        $txts = trim($data['txts']);

        if(preg_match('/#/',$txts) !== 0){
            $txts = explode('@',$txts);

            $list = [];
            foreach($txts as $k=>$v){
                if(!empty($v)){
                    $txt = explode('#',trim($v));
                    if(count($txt)==1){
                        return json(['code'=>-1,'msg'=>'整理失败，文本格式错误']);
                    }
                    if($type=='option') {
                        array_push($list, ['option_name' => $txt[0], 'option_desc' => $txt[1]]);
                    }
                    elseif($type=='spec') {
                        array_push($list, ['spec_name' => $txt[0], 'spec_desc' => $txt[1]]);
                    }
                }
            }
            return json(['code'=>0,'msg'=>'整理成功','list'=>$list]);
        }else{
            return json(['code'=>-1,'msg'=>'整理失败，文本中缺少字符“#”']);
        }
    }

    #阿里云ocr获取图片里的文字
    public function get_ocr($data){
//        require_once($_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/autoload.php');
//        return json(['code'=>0,'content'=>'制造商：东莞市春信纸业有限公司地址：东莞xxxxxxx工业区']);
        $config = new Config([
            // 必填，请确保代码运行环境设置了环境变量 ALIBABA_CLOUD_ACCESS_KEY_ID。
            "accessKeyId" => "LTAI5tS4sntQrRaaG1tNNK59",
            // 必填，请确保代码运行环境设置了环境变量 ALIBABA_CLOUD_ACCESS_KEY_SECRET。
            "accessKeySecret" => "acEBiU4vYI5KrvWC33NTBTiVJQYgCB"
        ]);
        // Endpoint 请参考 https://api.aliyun.com/product/ocr-api
        $config->endpoint = "ocr-api.cn-hangzhou.aliyuncs.com";
        $client = new Ocrapi($config);

        $recognizeGeneralRequest = new RecognizeGeneralRequest([
            'url'=>$data['img']
        ]);
        $runtime = new RuntimeOptions([]);
        try {
            // 复制代码运行请自行打印 API 的返回值
            $res = $client->recognizeGeneralWithOptions($recognizeGeneralRequest, $runtime);
            $result = json_decode($res->body->data,true);

            return json(['code'=>0,'content'=>$result['content']]);
        }
        catch (Exception $error) {
            if (!($error instanceof TeaError)) {
                $error = new TeaError([], $error->getMessage(), $error->getCode(), $error);
            }
            // 此处仅做打印展示，请谨慎对待异常处理，在工程项目中切勿直接忽略异常。
            // 错误 message
            var_dump($error->message);
            // 诊断地址
            var_dump($error->data["Recommend"]);
            Utils::assertAsString($error->message);
        }
    }

    #获取企业邀请员工信息
    public function get_invite_info($data){
        $company = Db::name('website_user_company')->where(['id'=>intval($data['company_id'])])->find();
        $role = Db::name('centralize_manage_level')->where(['id'=>intval($data['role_id'])])->find();
        $msg = '企业【'.$company['company'].'】邀请您加入成为该企业员工';
        return json(['code'=>0,'warn_msg'=>$msg]);
    }

    #保存企业员工信息，系统插入信息
    public function save_staff($data){
        if(empty($data['name'])){
            return json(['code'=>-1,'msg'=>'请输入真实名称']);
        }

        $website_user = Db::name('website_user')->where(['id'=>$data['uid']])->find();
        $ishave = Db::name('centralize_manage_person')->where(['company_id'=>intval($data['company_id']),'gogo_id'=>$website_user['id']])->find();
        if(empty($ishave)){
            $parent = Db::name('centralize_manage_person')->where(['company_id'=>intval($data['company_id']),'pid'=>0])->find();
            $res = Db::name('centralize_manage_person')->insertGetId([
                'name'=>trim($data['name']),
                'type'=>1,
                'company_id'=>intval($data['company_id']),
                'country_code'=>'162',
                'tel'=>$website_user['phone'],
                'email'=>'',
                'role_id'=>intval($data['role_id']),
                'agent_id'=>'',
                'status'=>1,#默认认证成功
                'pid'=>$parent['id'],
                'enterprise_id'=>'',
                'gogo_id'=>$website_user['id'],
                'createtime'=>time(),
            ]);
            return json(['code'=>0,'msg'=>'加入企业成功']);
        }
        else{
            return json(['code'=>-1,'msg'=>'您已成为该企业员工，无需重复操作']);
        }
    }

    #获取指定菜单信息
    public function get_navbar_info($data){
        $system_id=1;

        #当前主菜单轮播图
        $rotate = Db::name('miniprogram_rotate')->where(['system_id'=>$system_id,'menu_id'=>intval($data['id'])])->select();
        foreach($rotate as $k=>$v){
            $rotate[$k]['thumb'] = 'https://shop.gogo198.cn/'.$v['thumb'];
        }

        #当前主菜单子菜单
        $parent = Db::name('miniprogram_navbar')->where(['system_id'=>$system_id,'id'=>intval($data['id'])])->find();
        $list = Db::name('miniprogram_navbar')->where(['system_id'=>$system_id,'pid'=>intval($data['id'])])->order('displayorder asc')->select();
        foreach($list as $k=>$v){
            if(!empty($v['thumb'])){
                $list[$k]['thumb'] = 'https://shop.gogo198.cn/'.$v['thumb'];
            }

            if($v['tz_type']==2){
                $list[$k]['link'] = 'https://www.gogo198.net/?s=index/detail&id='.$v['menu_link'];
            }elseif($v['tz_type']==3){
                $list[$k]['link'] = 'https://www.gogo198.cn/txt_detail?id='.$v['pictxt_link'].'&type=image_txt&oid='.$v['pictxt_link'];
            }

            $list[$k]['have_children'] = $this->getChildren($system_id,$v);
        }

        #当前主菜单头部（目前已废弃）
        $head = Db::name('miniprogram_basicinfo')->where(['id'=>1])->find();
        $head['logo'] = 'https://shop.gogo198.cn/'.$head['logo'];

        #当前主菜单消息
        $news = Db::name('miniprogram_enterprise_news')->select();

        return json(['code'=>0,'data'=>$list,'head'=>$head,'parent_info'=>$parent,'rotate'=>$rotate,'news'=>$news]);
    }

    #子级
    private function getChildren($system_id,$data){
        $ishave = Db::name('miniprogram_navbar')->where(['system_id'=>$system_id,'pid'=>intval($data['id'])])->find();
        if(!empty($ishave)){
            return 1;
        }else{
            return 0;
        }
    }

    #获取活动信息
    public function get_activity_info($data){
        $type = intval($data['type']);//活动类型
        $currentTime = date('Y-m-d H:i:s');
        $is_check_order = isset($data['is_check_order'])?intval($data['is_check_order']):0;//1检查订购单 0无
        $is_generate_order = isset($data['is_generate_order'])?intval($data['is_generate_order']):0;//1生成选购单 0无

        if($is_check_order>0 && $type==2){
            #好食才付款，检查该用户所选商品有无生成订购单

            $campaign_id = intval($data['id']);//活动id
            $goods_id = intval($data['goods_id']);//商品id
            $uid = intval($data['uid']);
            $is_generate = 0;//没有生成订购单

            $purchase_order = Db::name('website_order_list')->where(['user_id'=>$uid,'status'=>'-2'])->field('id,content')->order('id desc')->select();
            if(!empty($purchase_order)){
                foreach($purchase_order as $k=>$v){
                    $purchase_order[$k]['content'] = json_decode($v['content'],true);
                    foreach($purchase_order[$k]['content']['goods_info'] as $k2=>$v2){
                        if($v2['good_id'] == $goods_id){
                            $is_generate = 1;//已生成订购单
                            break;//调出循环
                        }
                    }
                }
            }

            return json(['code'=>0,'data'=>['is_generate'=>$is_generate]]);
        }

        if($is_generate_order>0 && $type==2) {
            #好食才付款，检查该用户所选商品有无生成选购单
            $campaign_id = intval($data['id']);//活动id
            $goods_id = intval($data['goods_id']);//商品id
            $uid = intval($data['uid']);

            #1、检查是否已加入选购清单，如果是则直接返回选购清单ID
            $is_join = Db::connect($this->config)->name('cart')->where(['user_id'=>$uid,'goods_id'=>$goods_id,'selected'=>1,'is_buy'=>0])->find();
            if(empty($is_join)){
                #未加入购物车
                $goods = Db::connect($this->config)->name('goods')->where(['goods_id'=>$goods_id])->find();
                $sku_info2 = Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$goods['sku_id']])->find();
                $sku_info2['sku_prices'] = json_decode($sku_info2['sku_prices'],true);

                $data['data'] = [
                    'id'=>$goods_id,
                    'buy_attr'=>[
                        [
                            'attr_id' => $sku_info2['spec_vids'],
                            'spec_id' => $sku_info2['spec_ids'],
                            'attr_name' => $sku_info2['spec_names'],
                            'buy_num' => 1,
                            'now_gprice' => $sku_info2['sku_prices']['price'][0],
                        ]
                    ]
                ];

                #2、整理规格的数量+总价
                $content = ['good_id'=>$goods_id,'shop_id'=>$goods['shop_id'],'good_num'=>0,'good_price'=>0,'buy_attr'=>$data['data']['buy_attr']];
                foreach ($data['data']['buy_attr'] as $k=>$v) {
                    $content['good_num'] += $v['buy_num'];
                    $content['good_price'] += $v['now_gprice'];
                }

                #3、插入购物车
                $user = Db::name('website_user')->where(['custom_id'=>session('user.gogo_id')])->find();
                $shop_id = '';
                if ($goods['shop_id']>0) {
                    $shop_id = $goods['shop_id'];
                } else {
                    if (!empty($goods['other_shop'])) {
                        $goods['other_shop'] = json_decode($goods['other_shop'], true);
                        $shop_id = 'o_'.$goods['other_shop']['shopId'];
                    }
                }

                #4、生成订购单编号
                $ordersn = get_ordersn(1);

                #5、插入购物车
                $cart_id = Db::connect($this->config)->name('cart')->insertGetId([
                    'user_id'=>$uid,
                    'goods_id'=>$goods_id,
                    'ordersn'=>$ordersn,
                    'shop_id'=>$shop_id,
                    'selected'=>1,
                    'created_at'=>time()
                ]);

                foreach ($content['buy_attr'] as $k=>$v) {
                    if (isset($v['attr_id'])) {
                        #有规格
                        $attr_id = implode('|', array_reverse(explode('_', $v['attr_id'])));
                        $sku = Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$content['good_id'],'spec_vids'=>$attr_id])->find();
                        if (empty($sku)) {
                            $attr_id = implode('|', array_reverse(explode('|', $attr_id)));
                            $sku = Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$content['good_id'],'spec_vids'=>$attr_id])->find();
                        }
                    } else {
                        #无规格
                        $sku = Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$goods['goods_id']])->find();
                    }

                    $sku['sku_prices'] = json_decode($sku['sku_prices'], true);

                    #判断有无超过商品数量
                    if ($v['buy_num']>$sku['sku_prices']['goods_number']) {
                        $content['buy_attr'][$k]['buy_num'] = $sku['sku_prices']['goods_number'];
                    }

                    #判断区间价格：商品金额
                    $price = 0;
                    if (count($sku['sku_prices']['price'])>1) {
                        foreach ($sku['sku_prices']['start_num'] as $k2=>$v2) {
                            if ($sku['sku_prices']['select_end'][$k2]==1) {
                                #数值
                                if ($content['buy_attr'][$k]['buy_num']>=$v2 and $content['buy_attr'][$k]['buy_num']<=$sku['sku_prices']['end_num'][$k2]) {
                                    $target_price = $sku['sku_prices']['price'][$k2];
                                    $price = $content['buy_attr'][$k]['buy_num'] * $target_price;
                                    break;
                                }
                            } elseif ($sku['sku_prices']['select_end'][$k2]==2) {
                                #以上
                                if ($content['buy_attr'][$k]['buy_num']>=$v2) {
                                    $target_price = $sku['sku_prices']['price'][$k2];
                                    $price = $content['buy_attr'][$k]['buy_num'] * $target_price;
                                    break;
                                }
                            }
                        }
                    } else {
                        $price = $content['buy_attr'][$k]['buy_num'] * $sku['sku_prices']['price'][0];
                    }

                    Db::connect($this->config)->name('cart_sku')->insert([
                        'cart_id'=>$cart_id,
                        'sku_id'=>$sku['sku_id'],
                        'attr_id'=>isset($v['attr_id']) ? $v['attr_id'] : 0,
                        'spec_id'=>isset($v['spec_id']) ? $v['spec_id'] : 0,
                        'goods_num'=>$content['buy_attr'][$k]['buy_num'],
                        'currency'=>$sku['sku_prices']['currency'][0],
                        'price'=>$price,
                        'selected'=>1,
                    ]);
                }
            }else{
                #已加入购物车

            }
        }

        if($type==1 || $type==2 || $type==3){
            #预约免费食
            $list = Db::name('website_campaign_list')->where([
                'type'=>$type,
                'id'=>intval($data['id'])
            ])->find();
            $list['shop_goods'] = json_decode($list['shop_goods'], true);
            $goods = Db::connect($this->config)->name('goods_merchant')->whereIn('id', $list['shop_goods'])->field('id as goods_id,goods_name,goods_image')->select();
            foreach($goods as $k=>$v){
                $goods[$k]['goods_image'] = 'https://dtc.gogo198.net'.$v['goods_image'];
//                if (strpos($v['goods_image'], 'https:') === false) {
//                    $goods[$k]['goods_image'] = 'https:'.$v['goods_image'];
//                }
            }

            #店铺信息
            $store_info = Db::name('website_campaign_shop')->where(['id'=>$list['shop_id']])->find();
            $store_info['addr_image'] = 'https://dtc.gogo198.net'.$store_info['addr_image'];
            $stores = [
                'stores'=>[
                    'id'=>$store_info['id'],
                    'name'=>$store_info['shop_name'],
                    'address'=>$store_info['shop_address'],
                    'phone'=>$store_info['shop_tel'],
                    'businessHours'=>$store_info['shop_hours'],
                    'addr_image'=>$store_info['addr_image']
                ],
                'startTime'=>$list['startTime'],
                'endTime'=>$list['endTime'],
                'goods'=>$goods
            ];

            return json(['code'=>0,'data'=>$stores]);
        }
    }

    #获取店铺下的活动时间和适用产品
    public function get_shop_activity_info($data){
        $type = intval($data['type']);
        $shop_id = intval($data['id']);
        $currentTime = date('Y-m-d H:i:s');
        $sort = intval($data['sort']);

        if($sort==1){
            #获取店铺该活动的适用时间

            $activity_info = Db::name('website_campaign_list')->where([
                'type'=>$type,
                'id'=>$shop_id,
                'startTime'=>['<',$currentTime],
                'endTime'=>['>',$currentTime]
            ])->select();

            $shop_date = [];
            if(!empty($activity_info)){
                foreach($activity_info as $k=>$v){
                    $shop_date = array_merge($shop_date,[$v['startTime'].' ~ '.$v['endTime']]);
                }
            }
            return json(['code'=>0,'data'=>[
                'shop_date'=>$shop_date,
            ]]);
        }
        elseif($sort==2){
            #获取店铺该活动该时间断下的适用商品
            $selected_date = explode(' ~ ',$data['selected_date']);

            $activity_info = Db::name('website_campaign_list')->where([
                'type'=>$type,
                'id'=>$shop_id,
                'startTime'=>$selected_date[0],
                'endTime'=>$selected_date[1]
            ])->find();

            $goods_info = [];
            if(!empty($activity_info)) {
                $activity_info['shop_goods'] = json_decode($activity_info['shop_goods'], true);
                $list = Db::connect($this->config)->name('goods')->whereIn('goods_id', $activity_info['shop_goods'])->field('goods_id,goods_name')->select();
                $goods_info = array_merge($goods_info, $list);
            }
            return json(['code'=>0,'data'=>[
                'goods'=>$goods_info,
            ]]);
        }
    }

    #用户参与活动
    public function join_activity($data){
        $type = intval($data['type']);//活动类型
        $shop_id = intval($data['store_id']);//实体id
        $date = explode(' ~ ',$data['date']);//活动时间
        $product_id = intval($data['product_id']);//商品id
        $uid = intval($data['uid']);//用户id
        $sns_openid = $data['sns_openid'];//用户小程序openid
        $campaign_id = intval($data['campaign_id']);
        $time = time();

        if($type==1){
            //预约免费食
            $campaign_info = Db::name('website_campaign_list')->where(['shop_id'=>$shop_id,'id'=>$campaign_id,'type'=>$type])->find();

            //判断当前活动名额是否已满
            $quota = Db::name('website_campaign_user_list')->where(['campaign_id'=>$campaign_info['id']])->count();
            if($quota >= $campaign_info['shop_quota']){
                return json(['code'=>-1,'msg'=>'活动名额已达上限']);
            }

            $ishave = Db::name('website_campaign_user_list')->where(['campaign_id'=>$campaign_info['id'],'user_id'=>$uid,'shop_id'=>$shop_id,'product_id'=>$product_id])->find();
            if(!empty($ishave)){
                return json(['code'=>-1,'msg'=>'请勿重复操作，你已在此活动选择此产品']);
            }else{
                #活动任务
                $campaign_task_condition = explode(',',$campaign_info['campaign_task_condition']);
                $task_info = [];
                foreach($campaign_task_condition as $v){
                    array_push($task_info,['task_type'=>$v,'status'=>0]);
                }

                $res = Db::name('website_campaign_user_list')->insertGetId([
                    'user_id'=>$uid,
                    'campaign_id'=>$campaign_info['id'],
                    'shop_id'=>$shop_id,
                    'product_id'=>$product_id,
                    'date'=>$data['date'],
                    'task_info'=>json_encode($task_info,true),
                    'status'=>0,
                    'createtime'=>$time
                ]);

                if($res){
                    return json(['code'=>0,'msg'=>'参与活动成功','campaign_id'=>$res]);
                }
            }
        }
    }

    #获取活动类别下的活动列表
    public function get_campaign_list($data){
        //根据活动类别获取已有的活动（参与时间段内）
        $currentTime = date('Y-m-d H:i:s');

        # 1、预约免费食
        $type1 = Db::name('website_campaign_list')->where([
            'type'=>1,
            'startTime'=>['<',$currentTime],
            'endTime'=>['>',$currentTime],
        ])->select();
        $type1_background = Db::name('centralize_diycountry_content')->where(['pid'=>12])->orderRaw('RAND()')->field('param1,param2,param3')->find();
        $type1_info = [];
        if(!empty($type1)){
            $imgList = [];
            $descList = [];
            $idList = [];
            foreach($type1 as $k=>$v){
                array_push($imgList,'https://dtc.gogo198.net'.$v['campaign_pic']);
                array_push($descList,$v['campaign_desc']);
                array_push($idList,$v['id']);
            }

            foreach($imgList as $k=>$v){
                array_push($type1_info,[
                    'img'=>$imgList[$k],
                    'desc'=>$descList[$k],
                    'id'=>$idList[$k],
                ]);
            }
        }

        # 2、好食才付款
        $type2 = Db::name('website_campaign_list')->where([
            'type'=>2,
            'startTime'=>['<',$currentTime],
            'endTime'=>['>',$currentTime],
        ])->select();
        $type2_background = Db::name('centralize_diycountry_content')->where(['pid'=>12])->orderRaw('RAND()')->field('param1,param2,param3')->find();
        $type2_info = [];
        if(!empty($type2)){
            $imgList = [];
            $descList = [];
            $idList = [];
            foreach($type2 as $k=>$v){
                array_push($imgList,'https://dtc.gogo198.net'.$v['campaign_pic']);
                array_push($descList,$v['campaign_desc']);
                array_push($idList,$v['id']);
            }

            foreach($imgList as $k=>$v){
                array_push($type2_info,[
                    'img'=>$imgList[$k],
                    'desc'=>$descList[$k],
                    'id'=>$idList[$k],
                ]);
            }
        }

        # 3、买一送一
        $type3 = Db::name('website_campaign_list')->where([
            'type'=>3,
            'startTime'=>['<',$currentTime],
            'endTime'=>['>',$currentTime],
        ])->select();
        $type3_background = Db::name('centralize_diycountry_content')->where(['pid'=>12])->orderRaw('RAND()')->field('param1,param2,param3')->find();
        $type3_info = [];
        if(!empty($type3)){
            $imgList = [];
            $descList = [];
            $idList = [];
            foreach($type3 as $k=>$v){
                array_push($imgList,'https://dtc.gogo198.net'.$v['campaign_pic']);
                array_push($descList,$v['campaign_desc']);
                array_push($idList,$v['id']);
            }

            foreach($imgList as $k=>$v){
                array_push($type3_info,[
                    'img'=>$imgList[$k],
                    'desc'=>$descList[$k],
                    'id'=>$idList[$k],
                ]);
            }
        }

        return json(['code'=>0,'data'=>[
            'type1_info'=>$type1_info,
            'type1_background'=>$type1_background,
            'type2_info'=>$type2_info,
            'type2_background'=>$type2_background,
            'type3_info'=>$type3_info,
            'type3_background'=>$type3_background,
        ]]);
    }

    #获取活动类别下的指定活动详情
    public function get_campaign_detail($data){
        $id = intval($data['campaign_id']);

        #获取活动详情
        $detail = Db::name('website_campaign_list')
            ->alias('a')
            ->join('website_campaign_shop b','b.id = a.shop_id','left')
            ->where(['a.id'=>$id])
            ->field('a.*,b.shop_name,b.shop_address,b.shop_tel,b.shop_hours,b.addr_image')
            ->find();
        $detail['addr_image'] = 'https://dtc.gogo198.net'.$detail['addr_image'];

        #已参与人数
        $detail['participated'] = Db::name('website_campaign_user_list')->where(['campaign_id'=>$id,'status'=>['>=',0]])->count();
        #活动图片
        $detail['campaign_pic'] = 'https://dtc.gogo198.net'.$detail['campaign_pic'];
        #活动类别
        $type = ['1'=>'预约免费食','2'=>'好食才付款','3'=>'买一送一'];
        $detail['typeText'] = $type[$detail['type']];
        #活动商品
        $detail['shop_goods'] = json_decode($detail['shop_goods'],true);
        $goods = Db::connect($this->config)->name('goods_merchant')->whereIn('id',$detail['shop_goods'])->field('id as goods_id,goods_name,goods_image')->select();
        foreach($goods as $k=>$v){
            $goods[$k]['goods_image'] = 'https://dtc.gogo198.net'.$v['goods_image'];
//            if (strpos($v['goods_image'], 'https:') === false) {
//                $goods[$k]['goods_image'] = 'https:'.$v['goods_image'];
//            }
        }
        #活动时长
        $start = new \DateTime($detail['startTime']);
        $end   = new \DateTime($detail['endTime']);
        $interval = $start->diff($end);

        $totalDays = $interval->days; // 总天数（包括跨月/年）
        $hours     = $interval->h;    // 剩余小时（0-23）
        $detail['campaign_duration'] = "{$totalDays} 天 {$hours} 小时";
        #活动任务
        $taskType = ['1'=>'分享被查看','2'=>'分享被加购','3'=>'分享被转发','4'=>'分享被评论','5'=>'分享被点赞'];
        $detail['campaign_task_condition'] = explode(',',$detail['campaign_task_condition']);
        $task_info = [];
        foreach($detail['campaign_task_condition'] as $k=>$v){
            if($v==1){
                //分享被查看
                $task_info[$k]['icon'] = '查看';
                $task_info[$k]['name'] = $taskType[$v];
                $task_info[$k]['description'] = '将产品分享给好友，被1人查看';
            }
            elseif($v==2){
                //分享被加购
                $task_info[$k]['icon'] = '加购';
                $task_info[$k]['name'] = $taskType[$v];
                $task_info[$k]['description'] = '将产品分享给好友，被1人加入购物车';
            }
            elseif($v==3){
                //分享被转发
                $task_info[$k]['icon'] = '转发';
                $task_info[$k]['name'] = $taskType[$v];
                $task_info[$k]['description'] = '将产品链接/主题图分享给好友';
            }
            elseif($v==4){
                //分享被评论
                $task_info[$k]['icon'] = '评论';
                $task_info[$k]['name'] = $taskType[$v];
                $task_info[$k]['description'] = '将产品分享给好友，被1人评论';
            }
            elseif($v==5){
                //分享被点赞
                $task_info[$k]['icon'] = '点赞';
                $task_info[$k]['name'] = $taskType[$v];
                $task_info[$k]['description'] = '将产品分享给好友，被1人点赞';
            }
        }

        return json(['code'=>0,'data'=>[
            'campaign_id'=>$detail['id'],
            'type'=>$detail['typeText'],
            'quota'=>$detail['shop_quota'],
            'usedQuota'=>$detail['participated'],
            'description'=>$detail['campaign_desc'],
            'image'=>$detail['campaign_pic'],
            'startTime'=>$detail['startTime'],
            'endTime'=>$detail['endTime'],
            'duration'=>$detail['campaign_duration'],
            'stores'=>[
                'name'=>$detail['shop_name'],
                'address'=>$detail['shop_address'],
                'phone'=>$detail['shop_tel'],
                'businessHours'=>$detail['shop_hours'],
                'addr_image'=>$detail['addr_image']
            ],
            'products'=>$goods,
            'tasks'=>$task_info
        ]]);
    }

    #用户按类型管理活动
    public function get_activity_list($data){
        $type = intval($data['type']);
        $currentPage = intval($data['currentPage']);
        $pageSize = 10;
        $limit = ( $currentPage - 1 ) * $pageSize;
        $uid = intval($data['uid']);

        $count = Db::name('website_campaign_user_list')->alias('a')->join('website_campaign_list b','a.campaign_id = b.id','left')->where(['a.user_id'=>$uid,'b.type'=>$type])->count();
        // 3. 判断是否还有下一页
        $hasMore = ($currentPage * $pageSize) < $count;

        $list = Db::name('website_campaign_user_list')->alias('a')->join('website_campaign_list b','a.campaign_id = b.id','left')->where(['a.user_id'=>$uid,'b.type'=>$type])->limit($limit,$pageSize)->order('id desc')->field('a.*,b.type as type')->select();
        $status = ['-3'=>'已结束','-2'=>'已拒绝','-1'=>'已取消','0'=>'进行中','1'=>'任务已完成','2'=>'确定中','3'=>'已参加'];
        $type = ['1'=>'预约免费食','2'=>'好食才付款','3'=>'买一送一'];

        foreach($list as $k=>$v){
            //参与时间
            $list[$k]['participateTime'] = date('Y-m-d H:i:s',$v['createtime']);

            //参与类别
            $list[$k]['typeText'] = $type[$v['type']];

            //参与状态
            $list[$k]['statusClass'] = 'status_'.$v['status'];
            $list[$k]['statusText'] = $status[$v['status']];

            //店铺名称
            $shop_info = Db::name('website_campaign_shop')->where(['id'=>$v['shop_id']])->find();
            $list[$k]['storeName'] = $shop_info['shop_name'];

            //参与活动产品
            $goods_info = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$v['product_id']])->field('id as goods_id,goods_name')->find();
            $list[$k]['productName'] = $goods_info['goods_name'];

            //检测有无完成任务
            if($v['status']==0){
                $list[$k]['task_info'] = json_decode($v['task_info'],true);
                $is_done = 0;
                foreach($list[$k]['task_info'] as $k2=>$v2){
                    if($v2['status']==1){
                        $is_done=1;
                    }else{
                        $is_done=0;
                    }
                }

                if($is_done==1){
                    Db::name('website_campaign_user_list')->where(['id'=>$v['id'],'status'=>0])->update(['status'=>1]);
                }
            }
        }

        return json(['code'=>0,'data'=>['list'=>$list,'hasMore'=>$hasMore]]);
    }

    #获取活动详情
    public function get_activity_detail($data){
        $id = intval($data['id']);

        $detail = Db::name('website_campaign_user_list')->where(['id'=>$id])->find();//活动详情id
        $campaign = Db::name('website_campaign_list')->where(['id'=>$detail['campaign_id']])->find();//活动id
        $shop = Db::name('website_campaign_shop')->where(['id'=>$campaign['shop_id']])->find();//商店id
        $goods = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$detail['product_id']])->field('id as goods_id,goods_image,goods_name')->find();//商品信息
        $goods['goods_image'] = 'https://dtc.gogo198.net'.$goods['goods_image'];
//        if (strpos($goods['goods_image'], 'https:') === false) {
//            $goods['goods_image'] = 'https:'.$goods['goods_image'];
//        }

        //检测有无完成任务
        if($detail['status']==0){
            $task_info = json_decode($detail['task_info'],true);
            $is_done = 0;
            foreach($task_info as $k2=>$v2){
                if($v2['status']==1){
                    $is_done=1;
                }else{
                    $is_done=0;
                }
            }

            if($is_done==1){
                Db::name('website_campaign_user_list')->where(['id'=>$detail['id'],'status'=>0])->update(['status'=>1]);
            }
        }

        $status = ['-3'=>'已结束','-2'=>'已拒绝','-1'=>'已取消','0'=>'进行中','1'=>'任务已完成','2'=>'确定中','3'=>'已参加'];
        $type = ['1'=>'预约免费食','2'=>'好食才付款','3'=>'买一送一'];

        $activityData = [
            'participateTime'=>$detail['date'],//预约时间
            'type'=>$type[$campaign['type']],
            'validUntil'=>$campaign['startTime'].' ~ '.$campaign['endTime'],
            'statusClass'=>'status_'.$detail['status'],
            'status'=>$status[$detail['status']],
            'quota'=>$campaign['shop_quota'].'人次'
        ];

        #实体店信息
        $storeData = [
            'address'=>$shop['shop_address'],
            'businessHours'=>$shop['shop_hours'],
            'name'=>$shop['shop_name'],
            'phone'=>$shop['shop_tel'],
            'image'=>'https://dtc.gogo198.net'.$shop['addr_image']
        ];

        #所选产品
        $productData = [
            'name'=>$goods['goods_name'],
            'image'=>$goods['goods_image'],
            'description'=>'',
            'price'=>'',
        ];

        #活动任务
        $taskData = json_decode($detail['task_info'],true);
        $taskType = ['1'=>'分享被查看','2'=>'分享被加购','3'=>'分享被转发','4'=>'分享被评论','5'=>'分享被点赞'];
        $taskIcon = ['1'=>'查看','2'=>'加购','3'=>'转发','4'=>'评论','5'=>'点赞'];
        #任务完成数
        $completedTaskCount = 0;
        #任务总数量
        $taskCount = count($taskData);
        foreach($taskData as $k=>$v){
            //任务状态
            $taskData[$k]['allCompleted'] = $v['status'];
            //条件
            $taskData[$k]['conditions'] = [];
            //展开
            $taskData[$k]['expanded'] = 0;
            //icon
            $taskData[$k]['icon'] = $taskIcon[$v['task_type']];
            //name
            $taskData[$k]['name'] = $taskType[$v['task_type']];
            //任务完成数
            if($v['status']==1){
                $completedTaskCount += 1;
            }
        }

        return json(['code'=>0,'data'=>[
            'activityData'=>$activityData,
            'storeData'=>$storeData,
            'productData'=>$productData,
            'completedTaskCount'=>$completedTaskCount,
            'taskCount'=>$taskCount,
            'taskData'=>$taskData
        ]]);
    }

    #查看任务详情
    public function get_task_tutorial($data){
        $taskId = intval($data['task_id']);

        $detail = Db::name('campaign_operation_tutorial')->where(['type'=>$taskId])->find();
        $detail['content'] = json_decode($detail['content'],true);
//        $detail['content'] = '123';
        $detail['title'] = '教程图文';
        $detail['tips'] = '完成任务后，可在此活动详情页面查看任务完成情况';

        return json(['code'=>0,'data'=>$detail]);
    }

    #扫码检查资格
    public function scan_check_qualification($data){
        $uid = intval($data['uid']);
        $method = intval($data['method2']);

        if($method==0){
            //扫码查询
            #1、检查用户有哪些活动已完成任务
            $detail = Db::name('website_campaign_user_list')->whereRaw('user_id='.$uid.' and (status=1 or status=2)')->find();
            if(!empty($detail)){
                return json(['code'=>0,'data'=>['campaign_id'=>$detail['id'],'status'=>$detail['status']]]);
            }else{
                return json(['code'=>-1,'msg'=>'暂无已完成任务']);
            }
        }elseif($method==1){
            //点击确认免费食
            $res = Db::name('website_campaign_user_list')->where(['user_id'=>$uid,'status'=>1,'id'=>intval($data['campaign_id'])])->update(['status'=>2]);
            if($res){
                return json(['code'=>0,'msg'=>'已正式参加活动']);
            }else{
                return json(['code'=>-1,'msg'=>'参加活动失败']);
            }
        }elseif($method==2){
            //生成活动订单
            $detail = Db::name('website_campaign_user_list')->where(['user_id'=>$uid,'status'=>2,'id'=>intval($data['campaign_id'])])->find();
            $campaign = Db::name('website_campaign_list')->where(['id'=>$detail['campaign_id']])->find();
            $ishave = Db::name('website_campaign_order_list')->where(['company_id'=>$campaign['company_id'],'company_type'=>$campaign['company_type'],'compaign_id'=>$detail['id'],'type'=>$campaign['type'],'product_id'=>$detail['product_id'],'uid'=>$uid])->find();

            if(empty($ishave)){
                $time = time();
                $res = Db::name('website_campaign_order_list')->insert([
                    'company_id'=>$campaign['company_id'],
                    'company_type'=>$campaign['company_type'],
                    'compaign_id'=>$detail['id'],
                    'type'=>$campaign['type'],
                    'product_id'=>$detail['product_id'],
                    'uid'=>$uid,
                    'ordersn'=>'HD'.$time,
                    'status'=>0,
                    'createtime'=>$time
                ]);

                if($res){
                    return json(['code'=>0,'msg'=>'已生成活动订单，正在获取取餐码','is_reject'=>-1]);
                }else{
                    return json(['code'=>-1,'msg'=>'生成活动订单失败','is_reject'=>-1]);
                }
            }else{
                if($ishave['status']==1 || $ishave['status']==-1){
                    $campaign_order = Db::name('website_campaign_order_list')->where(['id'=>$ishave['id'],'company_id'=>$campaign['company_id'],'company_type'=>$campaign['company_type']])->find();
                    Db::name('website_campaign_user_list')->where(['id'=>$campaign_order['compaign_id']])->update(['status'=>3]);
                }

                if($ishave['status']==1){
                    //已接受订单
                    return json(['code'=>0,'msg'=>'商家已接受订单','data'=>['number'=>$ishave['rand_num'],'is_reject'=>0]]);
                }elseif($ishave['status']==-1){
                    //已拒绝订单
                    return json(['code'=>0,'msg'=>'商家已拒绝订单','data'=>['is_reject'=>1]]);
                }else{
                    return json(['code'=>-1]);
                }
            }
        }
    }

    #小程序商城========================================start
    #获取小程序店铺的商品及分类
    public function get_shop_goods_category($data){
        $company_id = $this->company_id;
        $goods = Db::connect($this->config)->name('goods_merchant')->where(['cid'=>$company_id,'is_shelf_xianxia'=>1])->select();

        $category_arr = [];
        $goods_arr = [];
        if(!empty($goods)){
            foreach($goods as $k=>$v){
                #获取分类
                $cate = Db::connect($this->config)->name('category')->where(['cat_id'=>$v['cat_id']])->field('cat_id as id,cat_name as name,cat_sort,cat_image as icon')->find();
                #判断分类是否已存在
                $A_cat_ids = array_column($category_arr, 'id'); // 提取所有 cat_id → [1918, 1919]
                $B_cat_id = $cate['id']; // 1918
                #归纳分类
                if (!in_array($B_cat_id, $A_cat_ids, true)) {
                    array_push($category_arr,$cate);
                }

                #获取商品基本信息
                $ginfo = ['categoryId'=>$v['cat_id'],'id'=>$v['id'],'image'=>'https://dtc.gogo198.net'.$v['goods_image'],'name'=>$v['goods_name'],'originalPrice'=>$v['market_price'],'price'=>$v['goods_price'],'quantity'=>0,'sales'=>$v['sale_num'],'selectedSpecIndex'=>0,'tags'=>[]];

                #获取商品规格
                $ginfo['specifications'] = [];
                $sku_info = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$v['id']])->select();
                foreach($sku_info as $k2=>$v2){
                    $sku_info[$k2]['sku_prices'] = json_decode($v2['sku_prices'],true);

//                    $small_price = 0;
//                    foreach($sku_info[$k2]['sku_prices']['price'] as $k3=>$v3){
//                        if (empty($small_price)){
//                            $small_price = $v3;
//                        }else {
//                            if ($v3 < $small_price) {
//                                $small_price = $v3;
//                            }
//                        }
//                    }

                    #该规格最低价格
                    $sku_info[$k2]['low_price'] = $sku_info[$k2]['sku_prices']['price'][0];
                }

                // 提取所有 low_price 值
                $lowPrices = array_column($sku_info, 'low_price');
                // 找到最小值
                $minPrice = min($lowPrices);
                // 找到第一个具有最小值的元素索引
                $minIndex = array_search($minPrice, $lowPrices);
                // 更改商品默认显示的价格最低的规格索引
                $ginfo['selectedSpecIndex'] = $minIndex;

                #组合商品规格
                foreach($sku_info as $k2=>$v2){
                    $goods_number = $v2['shelf_number'];
//                    if($v['goods_type']==0){
                        #单品

//                        if(!empty($v2['sku_specs'])){
//                            $goods_number = Db::name('website_warehouse_goodsnum')->where(['company_id'=>$company_id,'warehouse_id'=>$v['xianxia_warehouse_id'],'goods_id'=>$v['id'],'sku_id'=>$v2['sku_id']])->value('num');
//                        }else{
//                            $goods_number = Db::name('website_warehouse_goodsnum')->where(['company_id'=>$company_id,'warehouse_id'=>$v['xianxia_warehouse_id'],'goods_id'=>$v['id']])->value('num');
//                        }
//                    }elseif($v['goods_type']==1){
                        #组合商品
//                        $goods_number = Db::name('website_warehouse_combo_goodsnum')->where(['company_id'=>$company_id,'warehouse_id'=>$v['xianxia_warehouse_id'],'goods_id'=>$v['id'],'status'=>1])->field('sum_num')->find()['sum_num'];
//                    }

                    array_push($ginfo['specifications'],[
                        'id'=>$v2['sku_id'],
                        'name'=>$v2['spec_names'],
                        'originPrice'=>$v2['market_price'],
                        'price'=>$v2['low_price'],
                        'goods_number'=>$goods_number
                    ]);
                }

                #组合商品
                array_push($goods_arr,$ginfo);
            }
        }
        return json(['code'=>0,'list'=>$goods_arr,'category'=>$category_arr]);
    }

    #获取商品详情信息
    public function get_shop_goods_detail($data){
        $id = intval($data['id']);
        $quantity = intval($data['quantity']);

        $goods = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$id])->find();

        $product = [];
        $product['specs'] = [];
        #轮播图
        $product['images'] = Db::connect($this->config)->name('goods_image_merchant')->where(['goods_id'=>$id])->field('path')->select();
        foreach($product['images'] as $k=>$v){
            $product['images'][$k] = 'https://dtc.gogo198.net'.$v['path'];
        }
        #基本信息
        $product['name'] = $goods['goods_name'];
        #最低价
        $sku_info = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$id])->select();
        foreach($sku_info as $k2=>$v2){
            $sku_info[$k2]['sku_prices'] = json_decode($v2['sku_prices'],true);

//            $small_price = 0;
            //{"goods_number":10000,"start_num":["1","6"],"unit":["007"],"select_end":["1","2"],"end_num":["5",""],"currency":["5"],"price":["3","2"]}
            $jieti_price = [];
            $unit = Db::name('unit')->where(['code_value'=>$sku_info[$k2]['sku_prices']['unit'][0]])->value('code_name');
            foreach($sku_info[$k2]['sku_prices']['price'] as $k3=>$v3){
//                if (empty($small_price)){
//                    $small_price = $v3;
//                }else {
//                    if ($v3 < $small_price) {
//                        $small_price = $v3;
//                    }
//                }
                $word = '';
                if($sku_info[$k2]['sku_prices']['select_end'][$k3]==1){
                    #数值
                    $word = $sku_info[$k2]['sku_prices']['start_num'][$k3].$unit.'至'.$sku_info[$k2]['sku_prices']['end_num'][$k3].$unit.' ￥'.$v3;
                }elseif($sku_info[$k2]['sku_prices']['select_end'][$k3]==2){
                    #以上
                    $word = $sku_info[$k2]['sku_prices']['start_num'][$k3].$unit.'以上 ￥'.$v3;
                }
                array_push($jieti_price,[$word]);
            }

            #该规格最低价格
            $sku_info[$k2]['low_price'] = $sku_info[$k2]['sku_prices']['price'][0];

            #商品规格库存
            $goods_number = $v2['shelf_number'];
//            if($goods['goods_type']==0){
//                #单品
//                if(!empty($v2['sku_specs'])){
//                    $goods_number = Db::name('website_warehouse_goodsnum')->where(['company_id'=>30,'warehouse_id'=>$goods['xianxia_warehouse_id'],'goods_id'=>$goods['id'],'sku_id'=>$v2['sku_id']])->value('num');
//                }else{
//                    $goods_number = Db::name('website_warehouse_goodsnum')->where(['company_id'=>30,'warehouse_id'=>$goods['xianxia_warehouse_id'],'goods_id'=>$goods['id']])->value('num');
//                }
//            }elseif($goods['goods_type']==1){
//                #组合商品
//                $goods_number = Db::name('website_warehouse_combo_goodsnum')->where(['company_id'=>30,'warehouse_id'=>$goods['xianxia_warehouse_id'],'goods_id'=>$goods['id'],'status'=>1])->field('sum_num')->find()['sum_num'];
//            }
            $sku_info[$k2]['goods_number'] = $goods_number;

            #商品规格列表
            array_push($product['specs'],['id'=>$v2['sku_id'],'name'=>$v2['spec_names'],'price'=>$sku_info[$k2]['sku_prices']['price'][0],'goods_number'=>$goods_number,'jieti_price'=>$jieti_price]);
        }

        // 提取所有 low_price 值
        $lowPrices = array_column($sku_info, 'low_price');
        // 找到最小值
        $minPrice = min($lowPrices);
        $product['price'] = $minPrice;

        // 找到第一个具有最小值的元素索引
        $minIndex = array_search($minPrice, $lowPrices);
        // 默认选中最低价格的规格
        $product['selectedSpec'] = ['id'=>$sku_info[$minIndex]['sku_id'],'name'=>$sku_info[$minIndex]['spec_names'],'price'=>$sku_info[$minIndex]['sku_prices']['price'][0],'goods_number'=>$sku_info[$minIndex]['goods_number']];
        $product['defaultSpec'] = ['id'=>$sku_info[$minIndex]['sku_id'],'name'=>$sku_info[$minIndex]['spec_names'],'price'=>$sku_info[$minIndex]['sku_prices']['price'][0],'goods_number'=>$sku_info[$minIndex]['goods_number']];
        //划线价
        $product['originalPrice'] = $sku_info[$minIndex]['market_price'];

        //评论（avatar、content、id、name、images、time、rating）
        $product['reviews'] = [];
        $product['reviewCount'] = 0;

        //总价
        $product['subtotal'] = number_format($quantity * $sku_info[$minIndex]['sku_prices']['price'][0],'2');

        //商品参数================================start
        //自定义参数
        $product['params'] = [];
        if(!empty($goods['spec_info'])){
            $goods['spec_info'] = json_decode($goods['spec_info'],true);
            foreach($goods['spec_info'] as $k=>$v){
                array_push($product['params'],['key'=>$v['spec_name'],'value'=>$v['spec_desc']]);
            }
        }else{
            $goods['spec_info'] = [];
        }

        //制造企业
        if(!empty($goods['manufacture'])){
            $goods['manufacture'] = json_decode($goods['manufacture'],true);
            if (isset($goods['manufacture']['country'])) {
                $goods['manufacture']['country_name'] = Db::name('centralize_diycountry_content')->where(['id'=>$goods['manufacture']['country']])->value('param2');
            }

            $areas = ['area1','area2','area3','area4','area5','area6'];
            foreach ($areas as $area) {
                if (isset($goods['manufacture'][$area])) {
                    $goods['manufacture'][$area.'_name'] = Db::name('centralize_adminstrative_area')->where(['id'=>$goods['manufacture'][$area]])->value('code_name');
                }
            }

            if(!empty($goods['manufacture']['company_name'])){
                array_push($product['params'],['key'=>'制造企业名称','value'=>$goods['manufacture']['company_name']]);
            }
            if(isset($goods['manufacture']['country_name']) && isset($goods['manufacture']['address'])){
                $area = '';
                foreach($areas as $area){
                    if (isset($goods['manufacture'][$area])) {
                        $area .= $goods['manufacture'][$area.'_name'];
                    }
                }
                array_push($product['params'],['key'=>'制造企业地址','value'=>$goods['manufacture']['country_name'].$area.$goods['manufacture']['address']]);
            }
            if(isset($goods['manufacture']['connect_info'])){
                array_push($product['params'],['key'=>'制造企业'.$goods['manufacture']['connect_type'],'value'=>$goods['manufacture']['connect_info']]);
            }
            if(isset($goods['manufacture']['product_license'])){
                array_push($product['params'],['key'=>'制造企业生产许可','value'=>$goods['manufacture']['product_license']]);
            }
            if(isset($goods['manufacture']['product_standard'])){
                array_push($product['params'],['key'=>'制造企业生产标准','value'=>$goods['manufacture']['product_standard']]);
            }
        }
        //销售企业
        if(!empty($goods['sales'])){
            $goods['sales'] = json_decode($goods['sales'],true);
            if (isset($goods['sales']['country'])) {
                $goods['sales']['country_name'] = Db::name('centralize_diycountry_content')->where(['id'=>$goods['sales']['country']])->value('param2');
            }

            $areas = ['area1','area2','area3','area4','area5','area6'];
            foreach ($areas as $area) {
                if (isset($goods['sales'][$area])) {
                    $goods['sales'][$area.'_name'] = Db::name('centralize_adminstrative_area')->where(['id'=>$goods['sales'][$area]])->value('code_name');
                }
            }

            if(isset($goods['sales']['company_name'])){
                array_push($product['params'],['key'=>'销售企业名称','value'=>$goods['sales']['company_name']]);
            }
            if(isset($goods['sales']['country_name']) && isset($goods['sales']['address'])){
                $area = '';
                foreach($areas as $area){
                    if (isset($goods['sales'][$area])) {
                        $area .= $goods['sales'][$area.'_name'];
                    }
                }
                array_push($product['params'],['key'=>'销售企业地址','value'=>$goods['sales']['country_name'].$area.$goods['sales']['address']]);
            }
            if(isset($goods['sales']['connect_info'])){
                array_push($product['params'],['key'=>'销售企业'.$goods['sales']['connect_type'],'value'=>$goods['sales']['connect_info']]);
            }
            if(isset($goods['sales']['product_license'])){
                array_push($product['params'],['key'=>'销售企业销售许可','value'=>$goods['sales']['product_license']]);
            }
        }
        //外贸企业
        if(!empty($goods['foreign'])){
            $goods['foreign'] = json_decode($goods['foreign'],true);

            if (isset($goods['foreign']['country'])) {
                $goods['foreign']['country_name'] = Db::name('centralize_diycountry_content')->where(['id'=>$goods['foreign']['country']])->value('param2');
            }

            $areas = ['area1','area2','area3','area4','area5','area6'];
            foreach ($areas as $area) {
                if (isset($goods['foreign'][$area])) {
                    $goods['foreign'][$area.'_name'] = Db::name('centralize_adminstrative_area')->where(['id'=>$goods['foreign'][$area]])->value('code_name');
                }
            }

            if(isset($goods['foreign']['company_name'])){
                array_push($product['params'],['key'=>'外贸企业名称','value'=>$goods['foreign']['company_name']]);
            }
            if(isset($goods['foreign']['country_name']) && isset($goods['foreign']['address'])){
                $area = '';
                foreach($areas as $area){
                    if (isset($goods['foreign'][$area])) {
                        $area .= $goods['foreign'][$area.'_name'];
                    }
                }
                array_push($product['params'],['key'=>'外贸企业地址','value'=>$goods['foreign']['country_name'].$area.$goods['foreign']['address']]);
            }
            if(isset($goods['foreign']['connect_info'])){
                array_push($product['params'],['key'=>'外贸企业'.$goods['foreign']['connect_type'],'value'=>$goods['foreign']['connect_info']]);
            }
            if(isset($goods['foreign']['product_type']) && isset($goods['foreign']['product_license'])){
                array_push($product['params'],['key'=>'外贸企业'.$goods['foreign']['product_type'],'value'=>'许可编号：'.$goods['foreign']['product_license']]);
            }
        }
        //有效期限
        if(!empty($goods['effective'])){
            $goods['effective'] =  json_decode($goods['effective'],true);

            if(isset($goods['effective']['type'])){
                if($goods['effective']['type'] == 1){
                    array_push($product['params'],['key'=>'生产日期','value'=>'详见包装']);
                } elseif($goods['effective']['type'] == 2){
                    array_push($product['params'],['key'=>'生产日期','value'=>$goods['effective']['interval_day']]);
                }
            }

            if(isset($goods['effective']['valid_period'])){
                array_push($product['params'],['key'=>'有效期限','value'=>$goods['effective']['valid_period'] . $goods['effective']['valid_unit']]);
            }
        }
        //贮存条件
        if(!empty($goods['store'])) {
            $goods['store'] = json_decode($goods['store'], true);

            if(!empty($goods['store']['temperature_condition'])){
                array_push($product['params'],['key'=>'贮存温度条件','value'=>$goods['store']['temperature_condition']]);
            }
            if(!empty($goods['store']['humidity_condition'])){
                if($goods['store']['humidity_condition']=='相对湿度 X%-Y % 保存'){
                    array_push($product['params'],['key'=>'贮存湿度条件','value'=>'相对湿度 '.$goods['store']['humidity_x'].'% - '.$goods['store']['humidity_y'].'% 保存']);
                }else{
                    array_push($product['params'],['key'=>'贮存湿度条件','value'=>$goods['store']['humidity_condition']]);
                }
            }
            if(!empty($goods['store']['light_condition'])){
                array_push($product['params'],['key'=>'贮存光照条件','value'=>$goods['store']['light_condition']]);
            }
            if(!empty($goods['store']['packing_condition'])){
                array_push($product['params'],['key'=>'贮存包装条件','value'=>$goods['store']['packing_condition']]);
            }
            if(!empty($goods['store']['store_condition'])){
                array_push($product['params'],['key'=>'贮存储存环境','value'=>$goods['store']['store_condition']]);
            }
            if(!empty($goods['store']['special_condition'])){
                array_push($product['params'],['key'=>'贮存特殊要求','value'=>$goods['store']['special_condition']]);
            }
        }
        //贮存条件
        if(!empty($goods['packing'])) {
            $goods['packing'] = json_decode($goods['packing'], true);

            if(!empty($goods['packing']['type'])){
                if($goods['packing']['type'] == '有包装'){
                    $method = '';
                    if($goods['packing']['method'] == 1){
                        $method = '木质包装';
                    }elseif($goods['packing']['method'] == 2){
                        $method = '纸质包装';
                    }elseif($goods['packing']['method'] == 3){
                        $method = '塑料包装';
                    }elseif($goods['packing']['method'] == 4){
                        $method = '金属包装';
                    }elseif($goods['packing']['method'] == 5){
                        $method = '玻璃包装';
                    }elseif($goods['packing']['method'] == 6){
                        $method = '复合包装';
                    }
                    array_push($product['params'],['key'=>'产品包装','value'=>$goods['packing']['type'] . '-' . $method ]);
                }
                elseif($goods['packing']['type'] == '无包装'){
                    array_push($product['params'],['key'=>'产品包装','value'=>$goods['packing']['type'] . '-' .$goods['packing']['no_pack'] ]);
                }
            }
        }
        //商品参数================================end

        //详情
        if(!empty($goods['pc_desc'])){
            $product['detailInfos'] = json_decode($goods['pc_desc'],true);
        }else{
            $product['detailInfos'] = '';
        }

        return json(['code'=>0,'product'=>$product,'selectedSpec'=>$product['selectedSpec'],'subtotal'=>$product['subtotal']]);
    }

    #记录选购单，跳转下单页面
    public function create_order($data){
        $company_id = $this->company_id;
        $uid = intval($data['uid']);
        $total_price = floatval($data['total_price']);
        $total_quantity = intval($data['total_quantity']);
        $is_seller = intval($data['is_seller']);
        if($is_seller==1){
            $uid=0;
        }
        $product = json_decode($data['items'],true);

        Db::startTrans();
        try{
            #插入“线下店铺”选购表
            $ordersn = $this->get_ordersn(1);
            $cart_id = Db::name('miniprogram_cart')->insertGetId([
                'uid'=>$uid,
                'company_id'=>$company_id,
                'ordersn'=>$ordersn,
                'content'=>$data['items'],
                'total_price'=>$total_price,
                'total_quantity'=>$total_quantity,
                'status'=>0,
                'createtime'=>time()
            ]);

            if($cart_id>0){
                foreach($product as $k=>$v){
                    Db::name('miniprogram_cart_sku')->insert([
                        'uid'=>$uid,
                        'cart_id'=>$cart_id,
                        'goods_id'=>$v['goods_id'],
                        'spec_id'=>$v['spec_id'],
                        'quantity'=>$v['quantity'],
                        'price'=>$v['price'],
                        'goods_name'=>$v['name'],
                        'spec_name'=>$v['spec_name'],
                    ]);
                }
            }
            Db::commit();

            return json(['code'=>0,'msg'=>'加入选购成功，正在跳转确认下单页面~','cart_id'=>$cart_id]);
        }catch (\Exception $e){
            Db::rollback();
            return json(['code'=>-1,'msg'=>$e->getMessage()]);
        }
    }

    #获取选购单信息（确认下单页）
    public function get_cart_info($data){
        $cart_id = intval($data['cart_id']);
        $company_id = $this->company_id;

//        $cart = Db::name('miniprogram_cart')->where(['id'=>$cart_id])->find();
        $cart_sku = Db::name('miniprogram_cart_sku')->where(['cart_id'=>$cart_id])->select();

        $orderItems = [];
        foreach($cart_sku as $k=>$v){
            $g_info = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$v['goods_id']])->field('goods_image,goods_type,xianxia_warehouse_id')->find();
            $g_sku = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$v['goods_id']])->field('sku_id as id,spec_names as name,market_price as originPrice,sku_prices,sku_specs,shelf_number')->select();
            $specifications = [];
            $selectedSpecIndex = 0;
            foreach($g_sku as $k2=>$v2){
                $g_sku[$k2]['sku_prices'] = json_decode($v2['sku_prices'],true);

//                $small_price = 0;
//                foreach($g_sku[$k2]['sku_prices']['price'] as $k3=>$v3){
//                    if (empty($small_price)){
//                        $small_price = $v3;
//                    }else {
//                        if ($v3 < $small_price) {
//                            $small_price = $v3;
//                        }
//                    }
//                }

                #该规格最低价格
                $g_sku[$k2]['price'] = $g_sku[$k2]['sku_prices']['price'][0];

                #商品规格库存
                $goods_number = $v2['shelf_number'];
//                if($g_info['goods_type']==0){
//                    #单品
//                    if(!empty($v2['sku_specs'])){
//                        $goods_number = Db::name('website_warehouse_goodsnum')->where(['company_id'=>30,'warehouse_id'=>$g_info['xianxia_warehouse_id'],'goods_id'=>$v['goods_id'],'sku_id'=>$v2['id']])->value('num');
//                    }else{
//                        $goods_number = Db::name('website_warehouse_goodsnum')->where(['company_id'=>30,'warehouse_id'=>$g_info['xianxia_warehouse_id'],'goods_id'=>$v['goods_id']])->value('num');
//                    }
//                }
//                elseif($g_info['goods_type']==1){
//                    #组合商品
//                    $goods_number = Db::name('website_warehouse_combo_goodsnum')->where(['company_id'=>30,'warehouse_id'=>$g_info['xianxia_warehouse_id'],'goods_id'=>$v['goods_id'],'status'=>1])->field('sum_num')->find()['sum_num'];
//                }
                $g_sku[$k2]['goods_number'] = $goods_number;

                array_push($specifications,['goods_number'=>$goods_number,'id'=>$v2['id'],'name'=>$v2['name'],'originPrice'=>$v2['originPrice'],'price'=>$g_sku[$k2]['sku_prices']['price'][0]]);

                if($v['spec_name'] == $v2['name']){
                    $selectedSpecIndex = $k2;
                }
            }
            array_push($orderItems,['id'=>$v['goods_id'],'image'=>'https://dtc.gogo198.net'.$g_info['goods_image'],'name'=>$v['goods_name'],'price'=>$v['price'],'quantity'=>$v['quantity'],'selectedSpecIndex'=>$selectedSpecIndex,'specifications'=>$specifications,'totalPrice'=>number_format($v['price'] * $v['quantity'],'2')]);
        }

        #获取平台支付显示配置
        $payment_display = Db::name('miniprogram_payment_display')->where(['id'=>1])->find();
        $is_show_online_pay = 0;//0不显示，1显示
        $time = time();

        if($payment_display['manual_start_time']<$time && $payment_display['manual_end_time']>$time){
            #手动显示
            $is_show_online_pay = 1;
        }
        elseif($payment_display['interval_x']>0 && $payment_display['interval_y']>0){
            #间隔显示
            $count = Db::name('miniprogram_order_list')->whereRaw('company_id='.$company_id.' and status=1 and pay_method=1')->count();
            if($count > 0 && ($count % $payment_display['interval_x'] == 0)){
                $is_show_online_pay = 1;
            }
        }
        elseif($payment_display['random_probability']>0){
            #随机显示
            $random = mt_rand(1, 100);
            if($random <= $payment_display['random_probability']){
                $is_show_online_pay = 1;
            }
        }
        else{
            if(empty($payment_display['manual_start_time']) && empty($payment_display['manual_end_time']) && empty($payment_display['interval_x']) && empty($payment_display['interval_y']) && empty($payment_display['random_probability'])){
                //没有任何配置
                $is_show_online_pay = 1;
            }
        }

        if($payment_display['is_hide']==1) {
            #强制隐藏
            $is_show_online_pay = 0;
        }

        $orderInfo = Db::name('miniprogram_order_list')->where(['cart_id'=>$cart_id])->find();
        $orderInfos = ['order_status'=>0,'status_name'=>'待支付','ordersn'=>'','order_number'=>'','pay_method'=>$orderInfo['pay_method'],'retrieve_info'=>''];
        if($orderInfo['status']!=0){
            $orderInfos['order_status'] = $orderInfo['status'];
            $orderInfos['order_number'] = $orderInfo['number'];
            $orderInfos['order_price'] = $orderInfo['total_price'];
            $orderInfos['paytime'] = date('Y-m-d H:i:s',$orderInfo['paytime']);
            $orderInfos['createtime'] = date('Y-m-d H:i:s',$orderInfo['createtime']);
            $orderInfos['ordersn'] = $orderInfo['ordersn'];
            $status_name = '';
//            -3取消订单，-2确认退货，-1待退货，0待支付，1待接单，2待交付，3已完成
            if($orderInfo['status']==1){
                $status_name = '待接单';
            }elseif($orderInfo['status']==2){
                $status_name = '待交付';
            }elseif($orderInfo['status']==2){
                $status_name = '已完成';
            }elseif($orderInfo['status']==-1){
                $status_name = '待退货';
            }elseif($orderInfo['status']==-2){
                $status_name = '已退货';
            }elseif($orderInfo['status']==-3){
                $status_name = '已取消';
            }
            $orderInfos['status_name'] = $status_name;

            $pay_method_name = '';
            if($orderInfo['pay_method']==0){
                $pay_method_name = '微信支付';
            }elseif($orderInfo['pay_method']==1){
                $pay_method_name = '扫码支付';
            }elseif($orderInfo['pay_method']==2){
                $pay_method_name = '现金支付';
                $orderInfos['retrieve_info'] = json_decode($orderInfo['retrieve_info'],true);
            }
            $orderInfos['pay_method_name'] = $pay_method_name;
        }

        return json(['code'=>0,'orderItems'=>$orderItems,'is_show_online_pay'=>$is_show_online_pay,'orderInfos'=>$orderInfos]);
    }

    #更新订单数量
    public function update_order_quantity($data){
        $cart_id = intval($data['order_id']);
        $goods_id = intval($data['goods_id']);
        $spec_id = intval($data['spec_id']);
        $quantity = floatval($data['quantity']);

        //检查订单是否已支付
        $orderInfo = Db::name('miniprogram_order_list')->where(['cart_id'=>$cart_id])->field('status')->find();
        if(!empty($orderInfo)){
            if($orderInfo['status'] >= 1){
                return json(['code'=>-1,'msg'=>'该订单已支付，更新失败']);
            }
        }

        #根据数量决定价格
        $sku = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$goods_id,'sku_id'=>$spec_id])->find();
        $sku['sku_prices'] = json_decode($sku['sku_prices'],true);
        $true_price = 0;
        foreach($sku['sku_prices']['select_end'] as $k=>$v){
            if($v==1){
                #数值
                if($quantity>=$sku['sku_prices']['start_num'][$k] && $quantity<=$sku['sku_prices']['end_num'][$k]){
                    $true_price = $sku['sku_prices']['price'][$k];break;
                }
            }elseif($v==2){
                #以上
                if($quantity>=$sku['sku_prices']['start_num'][$k]){
                    $true_price = $sku['sku_prices']['price'][$k];break;
                }
            }
        }

        Db::name('miniprogram_cart_sku')->where(['cart_id'=>$cart_id,'goods_id'=>$goods_id,'spec_id'=>$spec_id])->update([
            'quantity'=>$quantity,
            'price'=>$true_price
        ]);

        sleep(1);

        $cart_sku_list = Db::name('miniprogram_cart_sku')->where(['cart_id'=>$cart_id])->select();
        $total_quantity = 0;
        $total_price = 0;
        foreach($cart_sku_list as $k=>$v){
            $total_quantity += $v['quantity'];
            $total_price += $v['quantity'] * $v['price'];
        }

        $res = Db::name('miniprogram_cart')->where(['id'=>$cart_id])->update([
            'total_price'=>$total_price,
            'total_quantity'=>$total_quantity
        ]);

        if($res){
            return json(['code'=>200,'msg'=>'更新成功']);
        }else{
            return json(['code'=>-1,'msg'=>'更新失败']);
        }
    }

    /*
     * $type（1是选购单，2是订购单）
     * $order_type（OF是线下，ON是线上）
     * $pay_type（H是扫码支付，G是在线支付，C是现金支付）
     */
    private function get_ordersn($type,$order_type='OF',$pay_type="G"){
        $year = date('Y');
        $month = date('m');
        $days = date("t", mktime(0, 0, 0, $month, 1, $year));
        $ordersn = '';

        $starttime = strtotime($year.'-'.$month.'-1 00:00:00');
        $endtime = strtotime($year.'-'.$month.'-'.$days.' 23:59:59');

        if($type==1){
            #选购单编号(今年今月第N个选购单)
            $ordersn = $order_type.$year.$month.'A';
            $times = Db::name('miniprogram_cart')->whereRaw('createtime>='.$starttime.' and createtime<='.$endtime)->count();
            $ordersn = $ordersn.str_pad($times+1,'4','0',STR_PAD_LEFT);
        }
        elseif($type==2){
            #订购单编号(今年今月第N个订购单)
            $ordersn = $order_type.$pay_type.$year.$month;
            $times = Db::name('miniprogram_order_list')->whereRaw('createtime>='.$starttime.' and createtime<='.$endtime)->count();
            $ordersn = $ordersn.str_pad($times+1,'5','0',STR_PAD_LEFT);

            $ishave = Db::name('miniprogram_order_list')->where(['ordersn'=>$ordersn])->field('id')->find();
            if(!empty($ishave)){
                #订单号存在，重新换一个
                return $this->get_ordersn($type,$order_type,$pay_type);
            }
        }
        elseif($type==3){
            #商品订单编号（今年今月今日今时第N个支付单）
            $date = date('d');
            $hour = date('H');
            $ordersn = $year.$month.'G'.$date.$hour;
            $starttime = strtotime($year.'-'.$month.'-'.$date.' '.$hour.':00:00');
            $endtime = strtotime($year.'-'.$month.'-'.$date.' '.$hour.':59:59');
            $times = Db::name('website_order_list')->whereRaw('createtime>='.$starttime.' and createtime<='.$endtime)->count();
            $ordersn = $ordersn.str_pad($times+1,'4','0',STR_PAD_LEFT);
        }


        return $ordersn;
    }

    //生成“取货码”
    private function get_rand_number(){
        $randomNumber = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $start = strtotime(date('Y-m-d 00:00:00'));
        $end = strtotime(date('Y-m-d 23:59:59'));
        $ishave = Db::name('miniprogram_order_list')->whereRaw('paytime>='.$start.' and paytime<='.$end.' and status>=1 and number='.$randomNumber)->find();
        if(empty($ishave)){
            //当前支付日期内的随机码，无重复
            return $randomNumber;
        }else{
            //有重复
            $randomNumber = $this->get_rand_number();
            return $randomNumber;
        }
    }

    //“扫码支付”和“现金支付”的提交支付
    public function submit_scan_payment($data){
        $cart_id = intval($data['id']);
        $uid = intval($data['uid']);
        $paytype = trim($data['pay_type']);
        $pay_type = '';
        $pay_method = 1;
        $company_id = $this->company_id;

        if($paytype=='scan_code'){
            #扫码支付
            $pay_type = 'H';
        }elseif($paytype=='cash'){
            #扫码支付
            $pay_type = 'C';
            $pay_method = 2;
        }

        //查询用户
        $user = Db::name('website_user')->where(['id'=>$uid])->find();
        if (empty($user['sns_openid'])) {
            return json([
                'code' => -1,
                'msg' => '用户未授权，无法支付'
            ]);
        }

        //查询选购单
        $cart = Db::name('miniprogram_cart')->where(['id'=>$cart_id])->find();
        // 检查金额是否为0
        if ($cart['total_price'] <= 0) {
            return json([
                'code' => -1,
                'msg' => '支付金额必须大于0'
            ]);
        }

        //生成订单
        $is_have = Db::name('miniprogram_order_list')->where(['cart_id'=>$cart_id])->find();
        $ordersn = '';
        $number = $this->get_rand_number();
        $time = time();
        if(empty($is_have)){
            $ordersn = $this->get_ordersn(2,'OF',$pay_type);
            Db::name('miniprogram_order_list')->insert([
                'uid'=>$uid,
                'company_id'=>$company_id,
                'cart_id'=>$cart_id,
                'ordersn'=>$ordersn,
                'total_price'=>$cart['total_price'],
                'pay_method'=>$pay_method,
                'status'=>1,
                'number'=>$number,
                'paytime'=>$time,
                'createtime'=>$time
            ]);
        }else{
            if($is_have['status']>=1){
                return json(['code'=>-1,'msg'=>'订单已支付']);
            }
            $ordersn = $this->get_ordersn(2,'OF',$pay_type);
            Db::name('miniprogram_order_list')->where(['cart_id'=>$cart_id])->update(['total_price'=>$cart['total_price'],'ordersn'=>$ordersn,'status'=>1,'pay_method'=>$pay_method,'number'=>$number,'paytime'=>$time]);
        }

        if(empty($cart['uid'])){
            Db::name('miniprogram_cart')->where(['id'=>$cart_id])->update(['uid'=>$uid]);
            Db::name('miniprogram_cart_sku')->where(['cart_id'=>$cart_id])->update(['uid'=>$uid]);
        }

        if(!empty($ordersn)){
            return json(['code'=>0,'msg'=>'支付成功，正在跳转中...']);
        }else{
            return json(['code'=>-1,'msg'=>'支付失败']);
        }
    }

    // 创建小程序支付订单接口
    public function createWechatPaymentOrder($orderData) {
        $cart_id = intval($orderData['cart_id']);
        $uid = intval($orderData['uid']);
        $company_id = $this->company_id;

        // 配置参数
        $appid = 'wx6d1af256d76896ba';
        $mch_id = '1456910002';
        $key = 'GGGOGO198Aa12345686329911GOGO198';

        //查询用户
        $user = Db::name('website_user')->where(['id'=>$uid])->find();
        if (empty($user['sns_openid'])) {
            return json([
                'code' => -1,
                'msg' => '用户未授权，无法支付'
            ]);
        }
        //查询选购单
        $cart = Db::name('miniprogram_cart')->where(['id'=>$cart_id])->find();

        // 检查金额是否为0
        if ($cart['total_price'] <= 0) {
            return json([
                'code' => -1,
                'msg' => '支付金额必须大于0'
            ]);
        }

        //生成订单
        $is_have = Db::name('miniprogram_order_list')->where(['cart_id'=>$cart_id])->find();
        $ordersn = '';
        if(empty($is_have)){
            $ordersn = $this->get_ordersn(2,'OF','G');
            Db::name('miniprogram_order_list')->insert([
                'uid'=>$uid,
                'company_id'=>$company_id,
                'cart_id'=>$cart_id,
                'ordersn'=>$ordersn,
                'total_price'=>$cart['total_price'],
                'pay_method'=>0,
                'status'=>0,
                'createtime'=>time()
            ]);
        }else{
            if($is_have['status']>=1){
                return json(['code'=>-1,'msg'=>'订单已支付']);
            }
            $ordersn = $this->get_ordersn(2,'OF','G');
            Db::name('miniprogram_order_list')->where(['cart_id'=>$cart_id])->update(['total_price'=>$cart['total_price'],'ordersn'=>$ordersn,'status'=>0,'pay_method'=>0]);
        }

        if(empty($cart['uid'])){
            Db::name('miniprogram_cart')->where(['id'=>$cart_id])->update(['uid'=>$uid]);
            Db::name('miniprogram_cart_sku')->where(['cart_id'=>$cart_id])->update(['uid'=>$uid]);
        }

        // 生成订单参数
        $nonce_str = $this->generateNonceStr();

        $body = '商品购买-' . $ordersn;
        $out_trade_no = $ordersn;
        $total_fee = intval($cart['total_price'] * 100); // 单位：分
        $spbill_create_ip = $_SERVER['REMOTE_ADDR'];
        $notify_url = 'https://shop.gogo198.cn/collect_website/public/?s=api/miniprogram/index&method=handlePaymentNotify'; // 支付回调地址
        $trade_type = 'JSAPI';
        $openid = $user['sns_openid']; // 从小程序获取

        // 生成签名
        $params = [
            'appid' => $appid,
            'mch_id' => $mch_id,
            'nonce_str' => $nonce_str,
            'body' => $body,
            'out_trade_no' => $out_trade_no,
            'total_fee' => $total_fee,
            'spbill_create_ip' => $spbill_create_ip,
            'notify_url' => $notify_url,
            'trade_type' => $trade_type,
            'openid' => $openid
        ];

        $sign = $this->generateSign($params, $key);
        $params['sign'] = $sign;

        // 调试：记录签名字符串
        $signString = $this->buildSignString($params, $key);

        // 转换XML
        $xml = $this->arrayToXml($params);

        // 调用微信支付统一下单接口
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        try {
            $result = $this->postXmlCurl($xml, $url);
        } catch (Exception $e) {
            return json([
                'code' => -1,
                'msg' => '请求微信支付接口失败: ' . $e->getMessage()
            ]);
        }

        // 解析返回结果
        $resultArray = $this->xmlToArray($result);

        if ($resultArray['return_code'] == 'SUCCESS') {
            if ($resultArray['result_code'] == 'SUCCESS') {
                // 生成小程序支付参数
                $paymentParams = [
                    'appId' => $appid,
                    'timeStamp' => (string)time(),
                    'nonceStr' => $this->generateNonceStr(),
                    'package' => 'prepay_id=' . $resultArray['prepay_id'],
                    'signType' => 'MD5'
                ];

                // 生成支付签名
                $paySign = $this->generateSign($paymentParams, $key);
                $paymentParams['paySign'] = $paySign;

                return json([
                    'code' => 0,
//                    'msg' => '订单创建成功',
                    'data' => $paymentParams
                ]);
            } else {
                // 返回具体的错误信息
                return json([
                    'code' => -1,
                    'msg' => '支付失败: ' .
                        ($resultArray['err_code_des'] ?? $resultArray['return_msg'] ?? '未知错误'),
                    'err_code' => $resultArray['err_code'] ?? '',
                    'err_msg' => $resultArray['err_code_des'] ?? ''
                ]);
            }
        } else {
            return json([
                'code' => -1,
                'msg' => '支付接口返回失败: ' . ($resultArray['return_msg'] ?? '未知错误')
            ]);
        }
    }

    // 支付回调处理
    public function handlePaymentNotify() {
        $xml = file_get_contents('php://input');
        $data = $this->xmlToArray($xml);
        Db::name('user_failed_login')->insert(['content'=>json_encode($data,true),'username'=>$this->verifySign($data, 'GGGOGO198Aa12345686329911GOGO198')]);

        // 验证签名
        if ($this->verifySign($data, 'GGGOGO198Aa12345686329911GOGO198')) {
            // 更新订单状态为已支付
            $orderNo = $data['out_trade_no'];
            $this->updateOrderStatus($orderNo, 'paid', [
                'transaction_id' => $data['transaction_id'],
                'pay_time' => date('Y-m-d H:i:s')
            ]);

            // 返回成功响应
            $response = [
                'return_code' => 'SUCCESS',
                'return_msg' => 'OK'
            ];

            echo $this->arrayToXml($response);
        } else {
            $response = [
                'return_code' => 'FAIL',
                'return_msg' => '签名失败'
            ];
            echo $this->arrayToXml($response);
        }
    }

    // 前端更新“订单支付状态”到后端
    public function update_payment_status($data){
        $cart_id = intval($data['order_id']);
        $status = trim($data['status']);

        $updateData['status'] = 1;
        $updateData['number'] = $this->get_rand_number();
        $updateData['sure_pay'] = 1;
        $updateData['paytime'] = time();

        $res = Db::name('miniprogram_order_list')->where(['cart_id'=>$cart_id])->update($updateData);
        if($res){
            return json(['code'=>0,'msg'=>'更新成功']);
        }else{
            return json(['code'=>-1,'msg'=>'更新失败']);
        }
    }

    /**
     * 更新订单状态
     * @param string $orderNo 订单号
     * @param string $status 状态
     * @param array $paymentInfo 支付信息
     * @return bool
     */
    public function updateOrderStatus($orderNo, $status, $paymentInfo = [])
    {
        // 这里需要您根据数据库结构自行实现
        // 以下是示例代码，请根据实际情况修改

        try {
            // 准备更新数据
            $updateData = [
                'status' => $status,
//                'updated_at' => date('Y-m-d H:i:s')
            ];

            $paymentInfos = [];

            // 如果是支付成功状态，添加支付信息
            if ($status === 'paid' && !empty($paymentInfo)) {
                $paymentInfos['transaction_id'] = $paymentInfo['transaction_id'] ? $paymentInfo['transaction_id'] : '';
                $paymentInfos['paid_at'] = $paymentInfo['pay_time'] ? $paymentInfo['pay_time'] : date('Y-m-d H:i:s');
                $paymentInfos['payment_method'] = $paymentInfo['payment_method'] ? $paymentInfo['payment_method'] : 'wechat';
                $paymentInfos['payment_infos'] = $paymentInfo;

                $updateData['status'] = 1;
                $updateData['payment_info'] = json_encode($paymentInfos,true);
                $updateData['number'] = $this->get_rand_number();
                $updateData['paytime'] = time();

            }

            // 执行更新
            $result = Db::name('miniprogram_order_list')->where('ordersn', $orderNo)->update($updateData);

            // 记录日志
//            if ($result) {
//                $this->logPaymentStatus($orderNo, $status, $paymentInfo);
//            }

            return $result;

        } catch (Exception $e) {
            // 记录错误日志
            error_log('更新订单状态失败: ' . $e->getMessage());
            return false;
        }
    }

    #获取订单列表
    public function get_order_list($data){
        $uid = intval($data['uid']);
        $status = trim($data['status']);
        $page = intval($data['page']);
        $company_id = $this->company_id;

        $where = 'company_id='.$company_id.' and uid='.$uid.' and ';
        $status_name = '';
        if($status=='unpaid'){
            $where .= 'status=0';
            $status_name = '待支付';
        }elseif($status=='paid'){
            $where .= 'status=1';
            $status_name = '已支付';
        }elseif($status=='pending'){
            $where .= 'status=1';
            $status_name = '待接单';
        }elseif($status=='delivering'){
            $where .= 'status=2';
            $status_name = '待交付';
        }elseif($status=='completed'){
            $where .= 'status=3';
            $status_name = '已完成';
        }elseif($status=='returning'){
            $where .= 'status=-1';
            $status_name = '待退货';
        }elseif($status=='returned'){
            $where .= 'status=-2';
            $status_name = '已退货';
        }elseif($status=='canceled'){
            $where .= 'status=-3';
            $status_name = '已取消';
        }

        $list = Db::name('miniprogram_order_list')->whereRaw($where)->order('id desc')->select();

        foreach($list as $k=>$v){
            $list[$k]['create_time'] = date('Y-m-d H:i:s',$v['createtime']);
            $list[$k]['order_no'] = $v['ordersn'];
            $list[$k]['status'] = $status;
            $list[$k]['status_text'] = $status_name;
            $list[$k]['store_name'] = '粤点粤甜';

            $total_price = 0;
            $total_quantity = 0;
            $list[$k]['goods_list'] = [];
            $cart_sku = Db::name('miniprogram_cart_sku')->where(['cart_id'=>$v['cart_id']])->select();
            foreach($cart_sku as $k2=>$v2){
                #订单总价
                $total_price += $v2['quantity'] * $v2['price'];
                #订单商品总数量
                $total_quantity += $v2['quantity'];

                #订单商品列表
                $goods = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$v2['goods_id']])->field('goods_image')->find();
                array_push($list[$k]['goods_list'],['id'=>$v2['goods_id'],'image'=>'https://dtc.gogo198.net'.$goods['goods_image'],'name'=>$v2['goods_name'],'price'=>$v2['price'],'quantity'=>$v2['quantity'],'spec'=>$v2['spec_name']]);
            }

            $list[$k]['total_price'] = number_format($total_price,2);
            $list[$k]['total_quantity'] = $total_quantity;
        }

        return json(['code'=>0,'list'=>$list]);
    }

    #获取订单状态（订单）数量
    public function get_order_status_count($data){
        $uid = intval($data['uid']);
        $company_id = $this->company_id;
        $count_0 = Db::name('miniprogram_order_list')->where(['uid'=>$uid,'company_id'=>$company_id,'status'=>'0'])->count();
        $count_1 = Db::name('miniprogram_order_list')->where(['uid'=>$uid,'company_id'=>$company_id,'status'=>'1'])->count();
        $count_2 = Db::name('miniprogram_order_list')->where(['uid'=>$uid,'company_id'=>$company_id,'status'=>'2'])->count();
        $count_3 = Db::name('miniprogram_order_list')->where(['uid'=>$uid,'company_id'=>$company_id,'status'=>'3'])->count();
//        $count_4 = Db::name('miniprogram_order_list')->where(['uid'=>$uid,'company_id'=>$company_id,'status'=>'4'])->count();

        $count_5 = Db::name('miniprogram_order_list')->where(['uid'=>$uid,'company_id'=>$company_id,'status'=>'-1'])->count();
        $count_6 = Db::name('miniprogram_order_list')->where(['uid'=>$uid,'company_id'=>$company_id,'status'=>'-2'])->count();
        $count_7 = Db::name('miniprogram_order_list')->where(['uid'=>$uid,'company_id'=>$company_id,'status'=>'-3'])->count();

        return json(['code'=>0,'list'=>[$count_0,$count_1,$count_2,$count_3,$count_5,$count_6,$count_7]]);
    }

    #取消订单
    public function cancel_order($data){
        $uid = intval($data['uid']);
        $id = intval($data['id']);

        $res = Db::name('miniprogram_order_list')->where(['uid'=>$uid,'id'=>$id])->update(['status'=>-3]);
        if($res){
            return json(['code'=>0,'msg'=>'取消订单成功！']);
        }else{
            return json(['code'=>-1,'msg'=>'取消订单失败！']);
        }
    }

    #生成他人支付的小程序葵花码
    public function generate_other_code($data){
        $cart_id = intval($data['id']);

        $cart = Db::name('miniprogram_cart')->where(['id'=>$cart_id])->find();
        if(!empty($cart['code_img'])){
            # 已生成二维码
            return json(['code' => 0, 'img' => $cart['code_img'], 'orderId' => $cart_id]);
        }else{
            # 未生成二维码
            $time = time();

            # 1. 获取access_token（已正确使用GET）
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx6d1af256d76896ba&secret=d19a96d909c1a167c12bb899d0c10da6";
            $res = file_get_contents($url);
            $result = json_decode($res, true);

            # 检查access_token是否获取成功
            if(!isset($result['access_token'])) {
                return json(['code' => 1, 'msg' => '获取access_token失败', 'error' => $result]);
            }

            $access_token = $result["access_token"];

            # 2. 获取微信小程序码（需要改为POST请求）
            $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $access_token;
            $datas = array(
                "page" => "pages/orderfood/order", // 注意：这里应该是页面路径，不要带参数
                "scene" => "orderId=" . $cart_id, // 参数通过scene传递
                "check_path" => false,
                "env_version" => 'release', // release develop trial体验
                'width' => 430,
            );

            // 使用CURL发送POST请求
            $img = $this->httpPost($url, json_encode($datas));

            // 检查返回的是否是错误信息
            $decode_img = json_decode($img, true);
            if(is_array($decode_img) && isset($decode_img['errcode'])) {
                // 返回错误信息
                return json(['code' => 1, 'msg' => '获取小程序码失败', 'error' => $decode_img]);
            }

            # 3. 保存图片（文件名应该唯一，避免覆盖）
            $filename = 'order_code_' . $cart_id . '.png';
            $savepath = $_SERVER['DOCUMENT_ROOT'] . '/collect_website/public/miniprogram/other_paycode/' . $filename;
            file_put_contents($savepath, $img);

            # 4. 生成访问URL
            $img_url = 'https://shop.gogo198.cn/collect_website/public/miniprogram/other_paycode/' . $filename . '?v=' . $time;

            # 5. 更新数据库
            Db::name('miniprogram_cart')->where(['id'=>$cart_id])->update(['code_img'=>$img_url]);

            return json(['code' => 0, 'img' => $img_url, 'orderId' => $cart_id]);
        }
    }

    private function httpPost($url, $data) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ));

        $result = curl_exec($ch);

        if(curl_errno($ch)) {
            return json_encode(['errcode' => -1, 'errmsg' => curl_error($ch)]);
        }

        curl_close($ch);

        return $result;
    }

    #获取上架信息
    public function get_shelf_info($data){
        $company_id = intval($data['company_id']);
        #获取商家目前上架的商品
        $list = Db::connect($this->config)->name('goods_merchant')->where(['cid'=>$company_id,'is_shelf_xianxia'=>1])->select();

        $goods = [];
        foreach($list as $k=>$v){
            #有无规格
            $hasSpecs = $v['have_specs']==1?true:false;
            $productId = $v['id'];
            $productName = $v['goods_name'];
            $defaultImage = 'https://dtc.gogo198.net'.$v['goods_image'];

            $specs = [];
            $specs_info = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$v['id']])->select();
            foreach($specs_info as $k2=>$v2){
                $specs_info[$k2]['sku_prices'] = json_decode($v2['sku_prices'],true);
                $image = '';
                $price = $specs_info[$k2]['sku_prices']['price'][0];
                $specCode = $v2['goods_sn'];
                $specDesc = $v2['spec_names'];
                $specId = $v2['sku_id'];
                $specName = $v2['spec_names'];
                $stock = $v2['shelf_number'];
                $stockWarning = '';

                array_push($specs,['image'=>$image,'price'=>$price,'specCode'=>$specCode,'specDesc'=>$specDesc,'specId'=>$specId,'specName'=>$specName,'stock'=>$stock,'stockWarning'=>$stockWarning,'good_id'=>$v['id']]);
            }
            array_push($goods,['hasSpecs'=>$hasSpecs,'productId'=>$productId,'productName'=>$productName,'defaultImage'=>$defaultImage,'specs'=>$specs]);
        }

        return json(['code'=>0,'list'=>$goods]);
    }

    #根据商品数量决定价格
    public function get_quantity_price($data){
        $id = intval($data['id']);
        $quantity = floatval($data['quantity']);
        $spec_id = intval($data['spec_id']);
        if(empty($spec_id)){
            $sku = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$id])->find();
        }
        else{
            $sku = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$id,'sku_id'=>$spec_id])->find();
        }
        $sku['sku_prices'] = json_decode($sku['sku_prices'],true);
        $true_price = 0;
        foreach($sku['sku_prices']['select_end'] as $k=>$v){
            if($v==1){
                #数值
                if($quantity>=$sku['sku_prices']['start_num'][$k] && $quantity<=$sku['sku_prices']['end_num'][$k]){
                    $true_price = $sku['sku_prices']['price'][$k];break;
                }
            }elseif($v==2){
                #以上
                if($quantity>=$sku['sku_prices']['start_num'][$k]){
                    $true_price = $sku['sku_prices']['price'][$k];break;
                }
            }
        }

        return json(['code'=>0,'price'=>$true_price]);
    }

    #修改库存&销售价格
    public function edit_stock_price($data){
        $gid = intval($data['id']);
        $specid = intval($data['specid']);
        $value = intval($data['value']);
        $type = trim($data['type']);

        if($type=='stock'){
            #修改上架（可订）库存
            #首先查看目前有多少库存
            $goods = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$gid])->find();
//            $goods_sku = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$gid,'sku_id'=>$specid])->find();

            if($goods['have_specs']==2){
                $stock = Db::name('website_warehouse_goodsnum')->where(['warehouse_id'=>$goods['xianxia_warehouse_id'],'goods_id'=>$gid,'sku_id'=>0])->find();
            }elseif($goods['have_specs']==1){
                $stock = Db::name('website_warehouse_goodsnum')->where(['warehouse_id'=>$goods['xianxia_warehouse_id'],'goods_id'=>$gid,'sku_id'=>$specid])->find();
            }

            if($stock['num']<$value){
                return json(['code'=>-1,'msg'=>'上架数量不能大于库存']);
            }
            $res = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$gid,'sku_id'=>$specid])->update([
               'shelf_number'=>$value
            ]);

            if($res){
                return json(['code'=>0,'msg'=>'修改库存成功']);
            }
        }
        elseif($type=='get_goods_jieti_price'){
            #获取阶梯价格
            $goods_sku = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$gid,'sku_id'=>$specid])->find();
            $goods_sku['sku_prices'] = json_decode($goods_sku['sku_prices'],true);
            $unit = Db::name('unit')->where(['code_value'=>$goods_sku['sku_prices']['unit'][0]])->value('code_name');
            $jieti_price = [];
            foreach($goods_sku['sku_prices']['price'] as $k=>$v){
                $word = '';
                if($goods_sku['sku_prices']['select_end'][$k]==1){
                    #数值
                    $word = $goods_sku['sku_prices']['start_num'][$k].$unit.'至'.$goods_sku['sku_prices']['end_num'][$k].$unit.' ￥'.$v;
                }elseif($goods_sku['sku_prices']['select_end'][$k]==2){
                    #以上
                    $word = $goods_sku['sku_prices']['start_num'][$k].$unit.'以上 ￥'.$v;
                }
                array_push($jieti_price,['word'=>$word,'price'=>$v]);
            }

            return json(['code'=>0,'data'=>$jieti_price]);
        }
        elseif($type=='price'){
            $jieti_price = json_decode($data['jieti_edit'],true);

            $goods_sku = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$gid,'sku_id'=>$specid])->find();
            $goods_sku['sku_prices'] = json_decode($goods_sku['sku_prices'],true);
            $is_no_edit = 0;//0代表没修改，1代表有修改
            foreach($goods_sku['sku_prices']['price'] as $k=>$v){
//                if(empty($jieti_price[$k]['price'])){
//                    return json(['code'=>-1,'msg'=>'价格不能设置为空']);
//                }
                if($v != $jieti_price[$k]['price']){
                    $is_no_edit = 1;break;
                }
            }
            if($is_no_edit==0){
                return json(['code'=>-1,'msg'=>'没有修改，无需保存']);
            }

            $low_price = 0;
            foreach($goods_sku['sku_prices']['price'] as $k=>$v){
                if($k==0){
                    $low_price = $jieti_price[$k]['price'];
                }else{
                    if($low_price > $jieti_price[$k]['price']){
                        $low_price = $jieti_price[$k]['price'];
                    }
                }

                $goods_sku['sku_prices']['price'][$k] = $jieti_price[$k]['price'];
            }
            $res = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$gid,'sku_id'=>$specid])->update(['sku_prices'=>json_encode($goods_sku['sku_prices'],true),'goods_price'=>$low_price]);
            if($res){
                $g = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$gid])->find();
                if($g['have_specs']==2){
                    #无规格
                    Db::connect($this->config)->name('goods_merchant')->where(['id'=>$gid])->update(['nospecs'=>json_encode($goods_sku['sku_prices'],true)]);
                }
                return json(['code'=>0,'msg'=>'修改成功']);
            }
        }
    }

    #卖家获取“待接单”、“待交付”、“退货处理”
    public function get_orders_list($data){
        $status = intval($data['status']);
        $company_id = intval($data['company_id']);
        $order = [];
        $_status = ['0'=>'微信支付','1'=>'扫码支付','2'=>'现金支付'];
        if($status==1){
            #待接单
            $orderInfo = Db::name('miniprogram_order_list')->where(['status'=>1,'company_id'=>$company_id])->order('id desc')->select();

            foreach($orderInfo as $k=>$v){
                array_push($order,['amount'=>$v['total_price'],'orderId'=>$v['id'],'cartId'=>$v['cart_id'],'orderNo'=>$v['ordersn'],'orderTime'=>date('Y-m-d H:i:s',$v['paytime']),'paymentMethod'=>$_status[$v['pay_method']],'pay_method'=>$v['pay_method'],'pickupCode'=>$v['number'],'sure_pay'=>$v['sure_pay']]);
            }
        }
        elseif($status==2){
            #待交付
            $orderInfo = Db::name('miniprogram_order_list')->where(['status'=>2,'company_id'=>$company_id])->order('id desc')->select();

            foreach($orderInfo as $k=>$v){
                array_push($order,['amount'=>$v['total_price'],'orderId'=>$v['id'],'cartId'=>$v['cart_id'],'orderNo'=>$v['ordersn'],'orderTime'=>date('Y-m-d H:i:s',$v['paytime']),'paymentMethod'=>$_status[$v['pay_method']],'pay_method'=>$v['pay_method'],'pickupCode'=>$v['number'],'sure_pay'=>$v['sure_pay']]);
            }
        }
        elseif($status==3){
            #已完成
            $page = isset($data['page']) ? intval($data['page']) : 1;
            $page = $page - 1;
            $limit = isset($data['page_size']) ? intval($data['page_size']) : 1;
            if ($page != 0) {
                $page = $limit * $page;
            }

            $orderInfo = Db::name('miniprogram_order_list')->where(['status'=>3,'company_id'=>$company_id])->limit($page,$limit)->order('id desc')->select();
            $total =  Db::name('miniprogram_order_list')->where(['status'=>3,'company_id'=>$company_id])->count();

            foreach($orderInfo as $k=>$v){
                array_push($order,['amount'=>$v['total_price'],'orderId'=>$v['id'],'cartId'=>$v['cart_id'],'orderNo'=>$v['ordersn'],'orderTime'=>date('Y-m-d H:i:s',$v['paytime']),'paymentMethod'=>$_status[$v['pay_method']],'pay_method'=>$v['pay_method'],'pickupCode'=>$v['number'],'sure_pay'=>$v['sure_pay']]);
            }
            return json(['code'=>0,'data'=>$order,'total'=>$total]);
        }
        elseif($status==-1){
            #待退货
            $orderInfo = Db::name('miniprogram_order_list')->whereRaw('status=-1 and company_id='.$company_id)->order('id desc')->select();
            $_status2 = ['-1'=>'待退货','-2'=>'已退货'];
            foreach($orderInfo as $k=>$v){
                array_push($order,['amount'=>$v['total_price'],'orderId'=>$v['id'],'cartId'=>$v['cart_id'],'orderNo'=>$v['ordersn'],'orderTime'=>date('Y-m-d H:i:s',$v['paytime']),'paymentMethod'=>$_status[$v['pay_method']],'pay_method'=>$v['pay_method'],'pickupCode'=>$v['number'],'sure_pay'=>$v['sure_pay'],'status_name'=>$_status2[$v['status']]]);
            }
        }

        return json(['code'=>0,'data'=>$order]);
    }

    #卖家修改订单状态
    public function update_order_info($data){
        $id = intval($data['id']);
        $ordersn = trim($data['ordersn']);
        $status = intval($data['status']);

        if($status==-1){
            #拒绝订单
            $orderInfo = Db::name('miniprogram_order_list')
                ->alias('mol')
                ->join('website_user wu','mol.uid=wu.id','left')
                ->where(['mol.id'=>$id])
                ->field('wu.sns_openid')
                ->find();

            $res = Db::name('miniprogram_order_list')->where(['id'=>$id])->update(['status'=>-1]);
            if($res){
                $resu = $this->notice_user_miniprogram($orderInfo,$ordersn,'returning','待退货','iicuy2l4iE94TS6AbdmQzARSz8og-Le7Ns5WTpMIDzA');

                return json(['code'=>0,'msg'=>'']);
            }
        }
        elseif($status==1){
            #待接单->确认支付
            $paymethod = intval($data['paymethod']);

            if($paymethod == 1){
                #扫码支付
                $res = Db::name('miniprogram_order_list')->where(['id'=>$id])->update(['sure_pay'=>1]);
                if($res){
                    return json(['code'=>0,'msg'=>'']);
                }
            }
            elseif($paymethod == 2){
                #现金支付
                $amount_due = floatval($data['amount_due']);
                $income = floatval($data['income']);
                $change = floatval($data['change']);

                $res = Db::name('miniprogram_order_list')->where(['id'=>$id])->update([
                    'sure_pay'=>1,
                    'retrieve_info'=>json_encode(['amount'=>$amount_due,'income'=>$income,'change'=>$change]),
                ]);
                if($res){
                    return json(['code'=>0,'msg'=>'']);
                }
            }
        }
        elseif($status==2){
            #确认接单->待交付
            $orderInfo = Db::name('miniprogram_order_list')
                ->alias('mol')
                ->join('website_user wu','mol.uid=wu.id','left')
                ->where(['mol.id'=>$id])
                ->field('wu.sns_openid')
                ->find();

            $res = Db::name('miniprogram_order_list')->where(['id'=>$id])->update(['status'=>2]);
            if($res){
                $resu = $this->notice_user_miniprogram($orderInfo,$ordersn,'delivering','待交付','J6G1aaydThPCF7QwfhilwaEkeru-5vvA12RmEK1sJXc');
//                {"errcode":0,"errmsg":"ok","msgid":4361635279594536960}

                $info = Db::name('miniprogram_print_display')->where(['id'=>1,'type'=>0])->find();
                if($info['open'] == 1) {
                    //通知打印机
                    $this->diyPrinter($id);
                }

                return json(['code'=>0,'msg'=>'']);
            }
        }
        elseif($status==3){
            #确认交付->已完成
//            $res = $this->diyPrinter($id);
//            dd($res);

            $orderInfo = Db::name('miniprogram_order_list')
                ->alias('mol')
                ->join('website_user wu','mol.uid=wu.id','left')
                ->where(['mol.id'=>$id])
                ->field('wu.sns_openid')
                ->find();

            $res = Db::name('miniprogram_order_list')->where(['id'=>$id])->update(['status'=>3]);
            if($res){
                $resu = $this->notice_user_miniprogram($orderInfo,$ordersn,'completed','已完成','1Ep-NilkEGx-VA9taJ2qDtJPoFHkd9sYkPJlK89WtXU');
//                dd($resu);
//                {"errcode":0,"errmsg":"ok","msgid":4361635279594536960}

                #赠送用户卡券金额
                $this->send_coupon($id);

                //减去实际库存和上架库存
                $this->updateGoodsInventory($id);
                return json(['code'=>0,'msg'=>'']);
            }
        }
        elseif($status==-2){
            #待退货->已退货
            $orderInfo = Db::name('miniprogram_order_list')
                ->alias('mol')
                ->join('website_user_company wuc','mol.company_id=wuc.id','left')
                ->join('website_user wu','wuc.user_id=wu.id','left')
                ->where(['mol.id'=>$id])
                ->field('wu.openid,mol.ordersn')
                ->find();

            $res = Db::name('miniprogram_order_list')->where(['id'=>$id])->update(['status'=>-2]);
            if($res){
//                $resu = $this->notice_user_miniprogram($orderInfo,$ordersn,'returned','已退货');

                //通知总后台/运营商
                #通知运营商
                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'订单:'.$orderInfo['ordersn'],
                    'keyword1' => '订单:'.$orderInfo['ordersn'],
                    'keyword2' => '已确认退货',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '',
                    'url' => '',
                    'openid' => $orderInfo['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
                httpRequest('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post,'',1);

                return json(['code'=>0,'msg'=>'']);
            }
        }
    }

    #自定义打印
    private function diyPrinter($id){
        $orderInfo = Db::name('miniprogram_order_list')
            ->alias('mol')
            ->join('miniprogram_cart_sku mcs','mcs.cart_id=mol.cart_id','left')
            ->join('miniprogram_cart mc','mc.id=mol.cart_id','left')
            ->where(['mol.id'=>$id])
            ->field('mol.ordersn,mol.number,mcs.goods_id,mol.total_price,mc.total_quantity,mol.cart_id')
            ->find();

        #获取商品的打印机
        $goods = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$orderInfo['goods_id']])->find();
        if(!empty($goods['xianxia_terminal_id'])){
            $printer = Db::name('centralize_warehouse_printer')->where(['id'=>$goods['xianxia_terminal_id']])->find();

            $goodsInfo = [];
            $cart_sku = Db::name('miniprogram_cart_sku')->where(['cart_id'=>$orderInfo['cart_id']])->select();
            foreach($cart_sku as $k=>$v){
                if($k<=7){
//                    $goods_sn = Db::connect($this->config)->name('goods_sku_merchant')->where(['sku_id'=>$v['spec_id'],'goods_id'=>$v['goods_id']])->value('goods_sn');
                    $goods_name = $v['goods_name'];
                    $price = $v['quantity'].'×'.$v['price'];

                    array_push($goodsInfo,['goods_name'=>$goods_name,'price'=>$price]);
                }
            }
            $customData = [
                'quhuoma'=>$orderInfo['number'],
//                'ordersn'=>$orderInfo['ordersn'],
//                'total'=>$orderInfo['total_quantity'],
//                'price'=>$orderInfo['total_price'],
//                'print_date'=>date('Y年m月d日 H:i:s'),
                'goods'=>$goodsInfo
            ];

            // 参数设置
            $key = $printer['key'];                             // 客户授权key
            $secret = $printer['secret'];                          // 授权secret
            list($msec, $sec) = explode(' ', microtime());
            $t = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);    // 当前时间戳
            $param = array (
                'tempid' => '4368c8f024e244c5b134858ed605a173',                    // 电子面单模板编码，通过后台模板管理页面获取：https://api.kuaidi100.com/manager/page/template/eletemplate
                'printType' => 'CLOUD',                 // 打印类型（IMAGE,CLOUD,HTML）。IMAGE:生成图片短链；HTML:生成html短链；CLOUD:使用快递100云打印机打印
                'siid' => $printer['siid'],                      // 设备编码
                'direction' => '0',                // 打印方向，0：正方向（默认）； 1：反方向；只有printType为CLOUD时该参数生效
                'callBackUrl' => 'https://shop.gogo198.cn/collect_website/public/?s=api/miniprogram/index&method=printer_callback&id='.$id,               // 打印状态回调地址，默认仅支持http
                'customParam' => $customData              // 面单自定义参数
            );

            //请求参数
            $post_data = array();
            $post_data['param'] = json_encode($param, JSON_UNESCAPED_UNICODE);
            $post_data['key'] = $key;
            $post_data['t'] = $t;
            $sign = md5($post_data['param'].$t.$key.$secret);
            $post_data['sign'] = strtoupper($sign);

            $url = 'https://api.kuaidi100.com/label/order?method=custom';    // 自定义打印接口请求地址

            //发送post请求 - 添加application/x-www-form-urlencoded格式
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);

            // 使用http_build_query构建查询字符串（自动转换为application/x-www-form-urlencoded格式）
            $post_string = http_build_query($post_data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

            // 设置HTTP头为application/x-www-form-urlencoded
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Content-Length: ' . strlen($post_string)
            ]);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            // 设置超时时间（可选）
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $result = curl_exec($ch);

            // 检查是否有错误
            if(curl_errno($ch)) {
                $error_msg = curl_error($ch);
                curl_close($ch);
                return [
                    'error' => true,
                    'message' => 'CURL请求失败: ' . $error_msg
                ];
            }

            // 获取HTTP状态码
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

//            $data = json_decode($result, true);

            // 输出结果
//            echo '<br/><br/>返回数据：<br/><pre>';
//            echo print_r($data);
//            echo '</pre>';
        }
    }

    #修改商品库存总数和上架数量
    private function updateGoodsInventory($id){
        $company_id = $this->company_id;
        $orderInfo = Db::name('miniprogram_order_list')->where(['id'=>$id])->find();
        $cart_sku = Db::name('miniprogram_cart_sku')->where(['cart_id'=>$orderInfo['cart_id']])->select();
        foreach($cart_sku as $k=>$v){
            #修改上架数量
            $sku = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$v['goods_id'],'sku_id'=>$v['spec_id']])->field('shelf_number,sku_specs')->find();
            $sku['shelf_number'] -= $v['quantity'];
            Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$v['goods_id'],'sku_id'=>$v['spec_id']])->update(['shelf_number'=>$sku['shelf_number']]);

            #修改库存数量
            $goods = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$v['goods_id']])->find();

            if($goods['goods_type']==0){
                #单品
                if(!empty($sku['sku_specs'])){
                    #有规格
                    $goods_number = Db::name('website_warehouse_goodsnum')->where(['company_id'=>$company_id,'warehouse_id'=>$goods['xianxia_warehouse_id'],'goods_id'=>$v['goods_id'],'sku_id'=>$v['spec_id']])->value('num');

                    $goods_number -= $v['quantity'];
                    Db::name('website_warehouse_goodsnum')->where(['company_id'=>$company_id,'warehouse_id'=>$goods['xianxia_warehouse_id'],'goods_id'=>$v['goods_id'],'sku_id'=>$v['spec_id']])->update(['num'=>$goods_number]);
                }else{
                    #无规格
                    $goods_number = Db::name('website_warehouse_goodsnum')->where(['company_id'=>$company_id,'warehouse_id'=>$goods['xianxia_warehouse_id'],'goods_id'=>$v['goods_id']])->value('num');

                    $goods_number -= $v['quantity'];
                    Db::name('website_warehouse_goodsnum')->where(['company_id'=>$company_id,'warehouse_id'=>$goods['xianxia_warehouse_id'],'goods_id'=>$v['goods_id']])->update(['num'=>$goods_number]);
                }
            }elseif($goods['goods_type']==1){
                #组合商品
                $goods_number = Db::name('website_warehouse_combo_goodsnum')->where(['company_id'=>$company_id,'warehouse_id'=>$goods['xianxia_warehouse_id'],'goods_id'=>$v['goods_id'],'status'=>1])->value('sum_num');

                $goods_number -= $v['quantity'];
                Db::name('website_warehouse_combo_goodsnum')->where(['company_id'=>$company_id,'warehouse_id'=>$goods['xianxia_warehouse_id'],'goods_id'=>$v['goods_id'],'status'=>1])->update(['sum_num'=>$goods_number]);
            }
        }
    }

    #赠送用户卡券金额
    private function send_coupon($id){
        $company_id = $this->company_id;
        $orderInfo = Db::name('miniprogram_order_list')->where(['id'=>$id])->find();
        $multiple = 2;#赠送倍数
        $time = time();

        #查询用户目前卡券余额
        $ishave = Db::name('website_user_coupon')->where(['type'=>3,'uid'=>$orderInfo['uid']])->find();
        $price = number_format($orderInfo['total_price'] * $multiple,2);

        if(empty($ishave)){
            $couponId = Db::name('website_user_coupon')->insertGetId([
                'type'=>3,
                'uid'=>$orderInfo['uid'],
                'price'=>$price
            ]);

            Db::name('website_user_coupon_log')->insert([
                'coupon_id'=>$couponId,
                'uid'=>$orderInfo['uid'],
                'type'=>0,
                'order_id'=>$id,
                'multiple'=>$multiple,
                'price'=>$price,
                'desc'=>'下单获得卡券金额：￥'.$price,
                'status'=>0,
                'opera'=>0,
                'createtime'=>$time
            ]);
        }else{
            $calc_price = $ishave['price'] + $price;
            Db::name('website_user_coupon')->where(['type'=>3,'uid'=>$orderInfo['uid']])->update(['price'=>$calc_price]);

            Db::name('website_user_coupon_log')->insert([
                'coupon_id'=>$ishave['id'],
                'uid'=>$orderInfo['uid'],
                'type'=>0,
                'order_id'=>$id,
                'multiple'=>$multiple,
                'price'=>$price,
                'desc'=>'下单获得卡券金额：￥'.$price,
                'status'=>0,
                'opera'=>0,
                'createtime'=>$time
            ]);
        }
    }

    public function formatOrderNumber($ordersn){
        // 方案A：提取纯数字
        if (preg_match('/\d+/', $ordersn, $matches)) {
            return $matches[0];
        }
    }

    public function notice_user_miniprogram($orderInfo,$ordersn,$tab,$status_name,$temp){
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx6d1af256d76896ba&secret=d19a96d909c1a167c12bb899d0c10da6";
        $res = file_get_contents($url);
        $result = json_decode($res, true);
        if($tab=='returning'){
            #退货通知
            $post2 = json_encode([
                'template_id'=>$temp,
                'page'=>'/pages/orderfood/order_list?tab='.$tab,
                'touser' =>$orderInfo['sns_openid'],
                'data'=>['character_string2'=>['value'=>$ordersn],'date3'=>['value'=>date('Y-m-d H:i:s')]],
                'miniprogram_state'=>'formal',
                'lang'=>'zh_CN',
            ]);
            $resu = httpRequest('https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token='.$result['access_token'], $post2,['Content-Type:application/json'],1);
            return $resu;
        }
        elseif($tab=='delivering'){
            #接受订单通知
            $ordersn = $this->formatOrderNumber($ordersn);
            $post2 = json_encode([
                'template_id'=>$temp,
                'page'=>'/pages/orderfood/order_list?tab='.$tab,
                'touser' =>$orderInfo['sns_openid'],
                'data'=>['thing1'=>['value'=>'正在备货'],'number9'=>['value'=>$ordersn],'date13'=>['value'=>date('Y年m月d日 H:i:s')]],
                'miniprogram_state'=>'formal',
                'lang'=>'zh_CN',
            ]);
            $resu = httpRequest('https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token='.$result['access_token'], $post2,['Content-Type:application/json'],1);
            return $resu;
        }
        elseif($tab=='completed'){
            #订单交付通知
            $ordersn = $this->formatOrderNumber($ordersn);

            $post2 = json_encode([
                'template_id'=>$temp,
                'page'=>'/pages/orderfood/order_list?tab='.$tab,
                'touser' =>$orderInfo['sns_openid'],
                'data'=>['character_string1'=>['value'=> $ordersn],'time6'=>['value'=>date('Y年m月d日 H:i')],'thing5'=>['value'=>'您的货物已完成交付，欢迎下次光临！']],
                'miniprogram_state'=>'formal',
                'lang'=>'zh_CN',
            ]);
            $resu = httpRequest('https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token='.$result['access_token'], $post2,['Content-Type:application/json'],1);
            return $resu;
        }
    }

    #检测是否卖家
    public function check_seller($data){
        $id = intval($data['id']);

        $is_merchant = Db::name('website_user_company')->where(['user_id'=>$id,'is_verify'=>1])->find();
        if(!empty($is_merchant)){
            $companys = Db::name('website_user_company')->where(['user_id'=>$id,'is_verify'=>1])->select();
            if(!empty($companys)){
                $staff_company = Db::name('website_user_company')
                    ->alias('wuc')
                    ->join('centralize_manage_person cmp','cmp.company_id=wuc.id','left')
                    ->where(['cmp.gogo_id'=>$id,'cmp.status'=>1])
                    ->field('wuc.*')
                    ->select();

                if(!empty($staff_company)){
                    $companys = array_merge($companys,$staff_company);
                }
            }else{
                $companys = Db::name('website_user_company')
                    ->alias('wuc')
                    ->join('centralize_manage_person cmp','cmp.company_id=wuc.id','left')
                    ->where(['cmp.gogo_id'=>$id,'cmp.status'=>1])
                    ->field('wuc.*')
                    ->select();
            }

            $temp = array_column($companys, null, 'company');
            $companys = array_values($temp);

            return json(['code'=>0,'is_merchant'=>1,'company'=>$companys]);
        }else{
            $companys = Db::name('centralize_manage_person')
                ->alias('cmp')
                ->join('website_user_company wuc','wuc.id = cmp.company_id','left')
                ->where(['cmp.gogo_id'=>$id,'cmp.status'=>1])
                ->field('wuc.*')
                ->select();

            if(!empty($companys)){
                $staff_company = Db::name('website_user_company')
                    ->alias('wuc')
                    ->join('centralize_manage_person cmp','cmp.company_id=wuc.id','left')
                    ->where(['cmp.gogo_id'=>$id,'cmp.status'=>1])
                    ->field('wuc.*')
                    ->select();

                if(!empty($staff_company)){
                    $companys = array_merge($companys,$staff_company);
                }

                $temp = array_column($companys, null, 'company');
                $companys = array_values($temp);

                return json(['code'=>0,'is_merchant'=>1,'company'=>$companys]);
            }
        }
        return json(['code'=>0,'is_merchant'=>0,'company'=>'']);
    }

    public function printer_callback($data){
        $id = intval($data['id']);

        Db::name('miniprogram_order_list')->where(['id'=>$id])->update(['print_data'=>json_encode($data,true)]);
    }

    public function jiami_data($data){
        $datas = base64_encode($data['url']);

        return json(['code'=>0,'data'=>$datas]);
    }

    #执行任务
    public function complete_task($data){
        $gid = intval($data['id']);
        $cid = intval($data['cid']);
        $uid = intval($data['uid']);
        $task_uid = intval($data['task_uid']);

        $campaign = Db::name('website_campaign_user_list')->where(['id'=>$cid,'user_id'=>$task_uid])->find();
        if($campaign['status']==0){
            $campaign['task_info'] = json_decode($campaign['task_info'],true);
            foreach($campaign['task_info'] as $k => $v){
                if($campaign['task_info'][$k]['task_type'] == 1){
                    $campaign['task_info'][$k]['status'] = 1;
                }
            }
            $res = Db::name('website_campaign_user_list')->where(['id'=>$cid,'user_id'=>$task_uid])->update(['status'=>1,'task_info'=>json_encode($campaign['task_info'],true)]);

            if($res){
                return json(['code'=>0,'msg'=>'']);
            }
        }

        return json(['code'=>0,'msg'=>'']);
    }

    #获取活动订单
    public function get_campaign_orders_list($data){
        $status = intval($data['status']);
        $company_id = intval($data['company_id']);

        $order = [];
        $_status = ['0'=>'待审核','1'=>'已接受','-1'=>'已拒绝'];
        $_name = ['1'=>'预约免费食','2'=>'好食才付款','3'=>'买一送一'];

        if($status==1){
            #待接单
            $orderInfo = Db::name('website_campaign_order_list')->where(['company_id'=>$company_id,'company_type'=>0])->order('id desc')->select();

            foreach($orderInfo as $k=>$v){
                array_push($order,['orderId'=>$v['id'],'cartId'=>$v['compaign_id'],'orderNo'=>$v['ordersn'],'orderTime'=>date('Y-m-d H:i:s',$v['createtime']),'statusName'=>$_status[$v['status']],'typeName'=>$_name[$v['type']],'pickupCode'=>$v['rand_num'],'status'=>$v['status']]);
            }
        }
        elseif($status==2){
            #接受订单
            $id = intval($data['id']);
            $rand_num = mt_rand(10000,99999);
            Db::name('website_campaign_order_list')->where(['id'=>$id,'company_id'=>$company_id,'company_type'=>0])->update([
                'status'=>1,
                'rand_num'=>$rand_num
            ]);
            return json(['code'=>0,'msg'=>'接受成功']);
        }
        elseif($status==-1){
            #拒绝订单
            $id = intval($data['id']);

            Db::name('website_campaign_order_list')->where(['id'=>$id,'company_id'=>$company_id,'company_type'=>0])->update([
                'status'=>-1
            ]);
            return json(['code'=>0,'msg'=>'拒绝成功']);
        }

        return json(['code'=>0,'data'=>$order]);
    }

    #获取动态新闻
    public function get_dynamic_news_info($data){
        $id = intval($data['id']);

        $news = Db::name('miniprogram_enterprise_news')->where(['id'=>$id])->find();
        if(!empty($news['content'])){
            $news['content'] = json_decode($news['content'],true);
        }

        return json(['code'=>0,'data'=>$news]);
    }

    #检测是否已支付
    public function check_paid($data){
        $id = intval($data['id']);

        $ishave = Db::name('miniprogram_order_list')->where(['cart_id'=>$id])->find();
        if(empty($ishave)){
            return json(['code'=>0,'is_paid'=>0]);
        }else{
            if($ishave['status']!=0){
                return json(['code'=>0,'is_paid'=>1]);
            }else{
                return json(['code'=>0,'is_paid'=>0]);
            }
        }
    }

    #无限打印面单
    public function print_order($data){
        $id = intval($data['id']);

        $info = Db::name('miniprogram_print_display')->where(['id'=>1,'type'=>0])->find();
        if($info['open'] == 1){
            #已开启
            //通知打印机
            $this->diyPrinter($id);
            return json(['code'=>0,'msg'=>'']);
        }else{
            #已关闭
            return json(['code'=>-1,'msg'=>'打印失败，面单打印机已关闭']);
        }

    }

    #微信小程序支付方法=====================================start
    /**
    * 构建签名字符串（用于调试）
    */
    private function buildSignString($params, $key) {
        // 过滤空值和sign参数
        $params = array_filter($params, function($value) {
            return $value !== '' && $value !== null;
        });

        // 按键名ASCII码从小到大排序
        ksort($params);

        // 拼接成URL键值对格式
        $string = '';
        foreach ($params as $k => $v) {
            if ($k != 'sign') {
                $string .= $k . '=' . $v . '&';
            }
        }

        // 拼接API密钥
        $string .= 'key=' . $key;

        return $string;
    }
    /**
     * 生成随机字符串
     * @param int $length 长度
     * @return string
     */
    public function generateNonceStr($length = 32)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 生成签名（MD5签名方式）
     * @param array $params 参数数组
     * @param string $key API密钥
     * @return string
     */
    public function generateSign($params, $key = null)
    {
        if ($key === null) {
            $key = $this->key; // 确保$this->key已定义
        }

        // 1. 过滤空值和sign参数
        $params = array_filter($params, function($value) {
            return $value !== '' && $value !== null;
        });

        // 2. 按键名ASCII码从小到大排序
        ksort($params);

        // 3. 拼接成URL键值对格式
        $string = '';
        foreach ($params as $k => $v) {
            if ($k != 'sign') {
                // 确保所有参数都是字符串
                $v = (string)$v;
                $string .= $k . '=' . $v . '&';
            }
        }

        // 4. 拼接API密钥
        $string .= 'key=' . $key;

        // 5. MD5加密并转为大写
        $sign = strtoupper(md5($string));

        return $sign;
    }

    /**
     * 验证签名
     * @param array $params 参数数组（包含sign）
     * @param string $key API密钥
     * @return bool
     */
    public function verifySign($params, $key = null)
    {
        if (!isset($params['sign'])) {
            return false;
        }

        // 获取接收到的签名
        $receivedSign = $params['sign'];

        // 移除sign参数，重新生成签名
        unset($params['sign']);

        // 生成签名
        $generatedSign = $this->generateSign($params, $key);

        // 比较签名
        return $receivedSign === $generatedSign;
    }

    /**
     * 数组转XML
     * @param array $arr 数组
     * @return string
     */
    public function arrayToXml($arr)
    {
        $xml = '<xml>';
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= '<' . $key . '>' . $val . '</' . $key . '>';
            } else {
                $xml .= '<' . $key . '><![CDATA[' . $val . ']]></' . $key . '>';
            }
        }
        $xml .= '</xml>';
        return $xml;
    }

    /**
     * XML转数组
     * @param string $xml XML字符串
     * @return array
     */
    public function xmlToArray($xml)
    {
        // 禁止引用外部xml实体
        libxml_disable_entity_loader(true);

        // 将XML转为对象，再转为数组
        $obj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($obj === false) {
            return [];
        }

        // 使用JSON方式转换，确保中文不乱码
        $json = json_encode($obj);
        $arr = json_decode($json, true);

        return $arr;
    }

    /**
     * 发送XML请求到微信支付API
     * @param string $xml XML数据
     * @param string $url API地址
     * @param int $timeout 超时时间（秒）
     * @return string
     * @throws Exception
     */
    public function postXmlCurl($xml, $url, $timeout = 30)
    {
        // 初始化cURL
        $ch = curl_init();

        // 设置cURL选项
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 不验证主机名
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // 返回内容作为变量
        curl_setopt($ch, CURLOPT_POST, true);            // POST请求
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);      // POST数据
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);     // 超时时间

        // 设置HTTP头
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: text/xml',
            'Content-Length: ' . strlen($xml)
        ]);

        // 执行请求
        $response = curl_exec($ch);

        // 检查错误
        if ($error = curl_error($ch)) {
            curl_close($ch);
            throw new Exception('cURL请求失败: ' . $error);
        }

        // 获取HTTP状态码
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // 检查HTTP状态码
        if ($httpCode != 200) {
            throw new Exception('HTTP请求失败，状态码: ' . $httpCode);
        }

        return $response;
    }
    #微信小程序支付方法=====================================end


    #小程序商城========================================end
}