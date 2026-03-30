<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\CURLFile;
use PHPExcel_IOFactory;
use PHPExcel;

header('Access-Control-Allow-Origin: *'); //设置http://www.baidu.com允许跨域访问
header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); //设置允许的跨域header
header('Content-Type: application/json;charset=utf-8');

#公用接口
class Func extends Controller
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

    #各系统请求的登录方法（废弃）
    public function login_api(Request $request){
        $dat = input();

        #公司成员
        if($dat['number']!='947960547@qq.com' && $dat['number']!='13119893380' && $dat['number']!='13119893381' && $dat['number']!='947960542@qq.com' && $dat['number']!='947960543@qq.com' && $dat['number']!='13119893382'&& $dat['number']!='13809703680' && $dat['number']!='13809703681' && $dat['number']!='hejunxin@gogo198.net'){
            if($dat['login_code'] != trim($dat['code'])){
                return json(['code'=>-1,'msg'=>'验证码不正确！']);
            }
        }

        #检查有无次用户
        $number = trim($dat['number']);
        if($dat['reg_method']==1){
            $account = Db::name('website_user')->where('phone',$number)->find();
        }elseif($dat['reg_method']==2){
            $account = Db::name('website_user')->where('email',$number)->find();
        }

        if(empty($account)){
            #无感注册
            $time = time();
            $insertid = Db::name('website_user')->insertGetId([
                'phone'=>$dat['reg_method']==1?$number:'',
                'email'=>$dat['reg_method']==2?$number:'',
                'openid'=>$dat['openid'],
                'times'=>1,
                'createtime'=>$time
            ]);
//                'GG'.date('YmdHis',$time).str_pad($insertid, 3, '0', STR_PAD_LEFT)
            $custom_id = '9'.str_pad($insertid, 5, '0', STR_PAD_LEFT);
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
                #买全球账号（旧的）
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
                #卖全球账号（旧的）
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
                #商城账号(新的)
                Db::connect($this->config)->name('user')->insert([
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
                    'spid'=>'254560',
                    'password'=>'J6Dtc4HO',
                    'ac'=>'1069254560',
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
                httpRequest('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$number,'title'=>'购购网','content'=>'尊敬的客户，您好！您已成功注册成为购购网会员，感谢您的支持！']);
            }
            // return json(['code'=>-1,'msg'=>'账户不正确！']);
        }else{
            if($account['openid']=='' && !empty($dat['openid'])){
                Db::name('website_user')->where(['id'=>$account['id']])->update(['openid'=>$dat['openid']]);
            }
//            if($account['times']==1){
//                Db::name('website_user')->where(['id'=>$account['id']])->update(['times'=>2]);
//            }else{
//                Db::name('website_user')->where(['id'=>$account['id']])->update(['times'=>intval($account['times'])+1]);
//            }
        }
    }

    #各系统生成会员方法
    public function generate_member(Request $request){
        $dat = input();
        $phone = isset($dat['phone'])?trim($dat['phone']):'';
        $email = isset($dat['email'])?trim($dat['email']):'';
        $realname = isset($dat['realname'])?trim($dat['realname']):'';
        $company_id = isset($dat['company_id'])?intval($dat['company_id']):0;

        $time = time();
        $insertid = Db::name('website_user')->insertGetId([
            'area_code'=>isset($dat['area_code'])?$dat['area_code']:162,#默认本国-电话区号
            'phone'=>$phone,
            'email'=>$email,
            'realname'=>$realname,
            'openid'=>isset($dat['openid'])?$dat['openid']:'',
            'company_id'=>$company_id,
            'auth0_info'=>isset($dat['auth0_info'])?$dat['auth0_info']:'',
            'sns_openid'=>isset($dat['sns_openid'])?$dat['sns_openid']:'',
            'unionid'=>isset($dat['unionid'])?$dat['unionid']:'',
            'times'=>1,
            'createtime'=>$time
        ]);

        $custom_id = 'G'.str_pad($insertid, 5, '0', STR_PAD_LEFT);
        $nickname = 'GoFriend_'.$custom_id;
        $res = Db::name('website_user')->where('id',$insertid)->update(['custom_id'=>$custom_id,'nickname'=>$nickname]);

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
            #买全球账号（旧的）
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
            #卖全球账号（旧的）
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
            #商城账号(新的)
            Db::connect($this->config)->name('user')->insert([
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

        #向本地电脑同步会员信息
//        sync_info($insertid,'member');

        return $insertid;
    }

    #生成当前企业商户的系统商户相关表
    public function generate_merchant(Request $request){
        $dat = input();

        $em_id = Db::name('enterprise_members')->insertGetId([
            'uniacid' => 3,
            'company_id'=>$dat['company_id'],
            'nickname' => $dat['nickname'],
            'realname' => $dat['realname'],
            'mobile' => $dat['phone'],
            'reg_type' => 1,
            'create_at' => $dat['time'],
            'is_verify' => 1
        ]);
        Db::name('enterprise_basicinfo')->insertGetId([
            'member_id' => $em_id,
            'company_id'=>$dat['company_id'],
            'name' => $dat['nickname'],
            'operName' => $dat['realname'],
            'orgNo' => '',
            'create_at' => $dat['time'],
        ]);
        $unique_id = '';
        if (!empty($dat['phone'])) {
            $unique_id = md5($dat['phone'] . date('YmdHis'));
        } elseif (!empty($dat['email'])) {
            $unique_id = md5($dat['email'] . date('YmdHis'));
        }
        Db::name('total_merchant_account')->insert([
            'unique_id' => $unique_id,
            'company_id'=>$dat['company_id'],
            'mobile' => $dat['phone'],
            'password' => password_hash('888888', PASSWORD_DEFAULT),
            'uniacid' => 3,
            'user_name' => $dat['realname'] != '' ? $dat['realname'] : $dat['nickname'],
            'company_name' => $dat['company'],
            'create_time' => $dat['time'],
            'desc' => '',
            'status' => 0,
            'user_email' => $dat['email'],
            'address' => '',
            //'address'=>$basic_info['address'],
            'company_tel' => '',
            'account_type' => 2,
            'openid' => '',
            'enterprise_id' => $em_id
        ]);
        $uid = Db::name('users')->insertGetId([
            'groupid' => 0,
            'company_id'=>$dat['company_id'],
            'username' => !empty($dat['realname']) ? $dat['realname'] : $dat['nickname'],
            'password' => "931f68875a20dd0d6d6a91711b856c7b9f1263a0",
            'salt' => "fF42Q83f",
            'type' => 0,
            'status' => 2,
            'joindate' => $dat['time'],
            'joinip' => '127.0.0.1',
            'lastvisit' => $dat['time'],
            'lastip' => '127.0.0.1',
            'remark' => '',
            'starttime' => 0,
            'endtime' => 0,
        ]);
        Db::name('sz_yi_perm_user')->insert([
            'uniacid' => 3,
            'company_id'=>$dat['company_id'],
            'uid' => $uid,
            'username' => !empty($dat['phone']) ? $dat['phone'] : $dat['email'],
            'password' => "931f68875a20dd0d6d6a91711b856c7b9f1263a0",
            'roleid' => 1,
            'status' => 1,
            'realname' => !empty($dat['realname']) ? $dat['realname'] : $dat['nickname'],
            'openid' => '',
            'mobile' => !empty($dat['phone']) ? $dat['phone'] : $dat['email']
        ]);
        Db::name('decl_user')->insert([
            'company_id'=>$dat['company_id'],
            'user_name' => $dat['realname'] != '' ? $dat['realname'] : $dat['nickname'],
            'user_tel' => $dat['phone'],
            'user_email' => $dat['email'],
            'user_password' => md5('888888'),
            'uniacid' => 3,
            'plat_id' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'user_status' => 0,
            'buss_id' => 3,
            'company_name' => $dat['company'],
            'company_num' => '',#不知道是什么
            'address' => '',
            'supplier' => $uid,
            'menus' => '1,2,3,6,21,22,54,7,8,9,23,24,47,55,56,65,66,10,11,12,13,14,39,40,41,42,43,86,15,16,17,30,31,18,32,33,57,19,20,28,25,26,27,29,51,52,53,252,34,35,36,38,37,44,46,48,235,105,201,211,202,212,213,214,203,215,204,216,217,218,219,205,220,221,106,206,222,207,223,224,225,208,226,209,227,228,229,230,210,231,232,253,254,64,72,127,67,69,128,74,75,77,134,135,129,73,76,130,131,233,132,136,137,138,133,78,79,139,82,87,88,83,94,140,146,168,169,170,147,171,141,142,148,172,173,143,149,174,144,150,175,176,236,237,145,151,179,180,181,182,80,153,84,183,184,85,185,154,160,186,187,188,189,161,190,191,155,156,162,192,193,157,163,194,158,164,195,196,165,197,159,166,198,199,167,200,239,240,241,242,243,246,247,248,251,255,256,259,262,263,260,264,265,268,266,269,270,271,267,272,273,274,275,276,277,278,279,280,257,261,258,283,290,284,285,286,287,288,293,294,291,295,296,292,',
            'business_type' => '1,2,3,4,5,6,7,',
            'enterprise_id' => $em_id,
            'gogo_id' => $dat['custom_id']
        ]);

        return 1;
    }

    #获取ip
    public function get_ip(Request $request){
//        ->distinct(true)->field('ip')
        $ip_list = Db::name('system_log')->whereRaw('ip != "39.108.11.214"')->limit(1)->orderRaw('RAND()')->find();
        return $ip_list['ip'];
    }

    #获取邮政编码下的区域
    public function get_area(Request $request){
        $dat = input();

        $val = trim($dat['val']);
        $country_id = $dat['country_id'];
        $area = '';#组合区域

        $country_info = Db::name('centralize_diycountry_content')->where(['pid'=>5,'id'=>$country_id])->field('param5')->find();
        $area_info = Db::name('all_country_area_postcode')->where(['country_code'=>$country_info['param5'],'postal_code'=>$val])->find();
        $area = $area_info['admin_name1'].' '.$area_info['admin_name2'].' '.$area_info['admin_name3'];

        if(!empty($area)){
            return json(['code'=>0,'msg'=>$area]);
        }
        else{
            return json(['code'=>-1]);
        }
    }

    #获取国家下的省/州
    public function get_province(Request $request){
        $dat = input();

        $country_id = intval($dat['country_id']);
        $pid = intval($dat['pid']);

        $list = Db::name('centralize_country_areas')->where(['country_id'=>$country_id,'pid'=>$pid])->select();

        return json(['code'=>0,'data'=>$list]);
    }

    #获取仓库信息
    public function get_warehouse_info(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;

        $info = Db::name('centralize_warehouse_list')->where(['id'=>$id])->find();

        $info['pic'] = json_decode($info['pic'],true);
        foreach($info['pic'] as $k=>$v){
            $info['pic'][$k] = 'https://shop.gogo198.cn/'.$v;
        }

        #仓储类型：1发货仓库，2代发仓库
        $warehouse_form = ['1'=>'发货仓库','2'=>'代发仓库'];
        $info['warehouse_form'] = $warehouse_form[$info['warehouse_form']];

        #仓储类别：1普通仓库，2保温仓库，3危化仓库，4气调仓库
        $warehouse_type = ['1'=>'普通仓库','2'=>'保温仓库','3'=>'危化仓库','4'=>'气调仓库'];
        $info['warehouse_type'] = $warehouse_type[$info['warehouse_type']];

        #仓储结构，1单层仓库，2多层仓库，3立体仓库，4简易仓库，5露天堆场
        $warehouse_structure = ['1'=>'单层仓库','2'=>'多层仓库','3'=>'立体仓库','4'=>'简易仓库','5'=>'露天堆场'];
        $info['warehouse_structure'] = $warehouse_structure[$info['warehouse_structure']];

        #运营模式，1自用仓库，2公用仓库（(第三方物流仓库）
        $warehouse_mode = ['1'=>'自用仓库','2'=>'公用仓库（第三方物流仓库）'];
        $info['warehouse_mode'] = $warehouse_mode[$info['warehouse_mode']];

        #仓储温度，1常温仓15°C至30°C，2恒温仓维持在15-25°C左右，3冷藏仓0°C至10°C，4冷冻仓-18°C至-25°C，5深冷库/速冻库-30°C至-40°C
        $warehouse_temperature = ['1'=>'常温仓15°C至30°C','2'=>'恒温仓维持在15-25°C左右','3'=>'冷藏仓0°C至10°C','4'=>'冷冻仓-18°C至-25°C','5'=>'深冷库/速冻库-30°C至-40°C'];
        $info['warehouse_temperature'] = $warehouse_temperature[$info['warehouse_temperature']];

        #仓库设备，1人手工仓库，2机械化仓库，3自动化仓库，4无人化仓库
        $warehouse_equipment = ['1'=>'人手工仓库','2'=>'机械化仓库','3'=>'自动化仓库','4'=>'无人化仓库'];
        $info['warehouse_equipment'] = $warehouse_equipment[$info['warehouse_equipment']];

        #国地
        $info['country_name'] = Db::name('centralize_diycountry_content')->where(['id'=>$info['country_code']])->field('param2')->find()['param2'];

        if($info['have_postal_code']==2){
            $info['province_name'] = $info['city_name'] = $info['district_name'] = $info['town_name'] = $info['village_name'] = '';

            if(!empty($info['province_code'])){
                $info['province_name'] = Db::name('centralize_country_areas')->where(['id'=>$info['province_code']])->field('name')->find()['name'];
            }

            if(!empty($info['city_code'])){
                $info['city_name'] = Db::name('centralize_country_areas')->where(['id'=>$info['city_code']])->field('name')->find()['name'];
            }

            if(!empty($info['district_code'])){
                $info['district_name'] = Db::name('centralize_country_areas')->where(['id'=>$info['district_code']])->field('name')->find()['name'];
            }

            if(!empty($info['town_code'])){
                $info['town_name'] = Db::name('centralize_country_areas')->where(['id'=>$info['town_code']])->field('name')->find()['name'];
            }

            if(!empty($info['village_code'])){
                $info['village_name'] = Db::name('centralize_country_areas')->where(['id'=>$info['village_code']])->field('name')->find()['name'];
            }
        }
        return json(['code'=>0,'data'=>$info]);
    }

    #同步将库存文件发送至指定邮件（商家修改商品库存时的自动操作）
    public function sync_inventory_to_email(Request $request){
        $dat = input();
//        $ids = explode(',',rtrim($dat['insert_ids'],','));
        $ids = rtrim($dat['insert_ids'],',');
        $goods_id = intval($dat['goods_id']);

        $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes';
        $dir2 = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
        require_once($dir."/PHPExcel.php");
        require_once($dir2."/IOFactory.php");
        $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
        $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('库存同步信息'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue('A1','商品名称')
            ->setCellValue('B1','规格名称')
            ->setCellValue('C1','原商品库存数量')
            ->setCellValue('D1','现商品库存数量')
            ->setCellValue('E1','商品规格货号')
            ->setCellValue('F1','商品规格条码')
            ->setCellValue('G1','商品规格库码');

        $goods = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$goods_id])->find();
        $warehouse_merchant = Db::name('centralize_warehouse_merchant')->where(['id'=>$goods['wid']])->find();
        $warehouse = Db::name('centralize_warehouse_list')->where(['id'=>$warehouse_merchant['warehouse_id']])->find();

        $inventory_list = Db::name('website_warehouse_inventory_change_log')->whereRaw('id in ('.$ids.')')->select();
        foreach($inventory_list as $k=>$v){
            $num = $k + 2;
            $goods_sku = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$goods_id,'sku_id'=>$v['sku_id']])->find();

            $PHPSheet->setCellValue("A".$num,"\t".$goods['goods_name']."\t")
                ->setCellValue('B'.$num,"\t".$goods_sku['spec_names']."\t")
                ->setCellValue('C'.$num,$v['origin_num']."\t")
                ->setCellValue('D'.$num,$v['now_num'])
                ->setCellValue('E'.$num,$goods_sku['goods_sn'])
                ->setCellValue('F'.$num,$goods_sku['goods_barcode'])
                ->setCellValue('G'.$num,$goods_sku['goods_stockcode']);
        }

        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
        $fileName  = "商品【".$goods['goods_name']."】库存数量变更__".date('Y-m-d',time()).'.xlsx';
        $path      = $_SERVER['DOCUMENT_ROOT'].'/collect_website/public/bussiness/goods_inventory/'.$fileName;
        $PHPWriter->save($path);
        $time = date('Y-m-d H:i:s',time());
        $content = "提示：您有库存变更信息!导出时间：{$time}";
        $Result    = $this->sendEmail($path,$warehouse['email'],'您有库存变更信息',$content);
        unlink($path);
        if(!$Result) {
            return json(['code'=>1,'msg'=>'发送失败']);
        }
        return json(['code'=>0,'data'=>'发送成功']);
    }

    //采购单信息发送至指定邮箱
    public function goods_procurement_to_email(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        $action = trim($dat['action']);
        //  [goods_info] => [{"goods_id":"42","sku_id":"143","goods_name":"仓库测试","sku_name":"包装规格:杨枝甘露花胶羹","price":"7","quantity":"1009","tax_rate":"0","subtotal":"7063"},{"goods_id":"42","sku_id":"144","goods_name":"仓库测试","sku_name":"包装规格:红枣藜麦花胶粥","price":"7","quantity":"1009","tax_rate":"0","subtotal":"7063"}]

        #1、查询采购单信息
        $procurement_data = Db::name('website_procurement')->where(['id'=>$id])->field('goods_info,company_id,warehouse_id,warehouse_type')->find();

        #2、查询企业信息
        $company_data = Db::name('website_user_company')->where(['id'=>$procurement_data['company_id']])->field('company')->find();

        #3、查询仓库信息
        $warehouse = Db::name('centralize_warehouse_list')->where(['id'=>$procurement_data['warehouse_id']])->find();

        if($action=='cancel'){
            $content = '<p style="margin-bottom:20px;">商家【'.$company_data['company'].'】已取消采购清单</p>';
            $Result    = $this->sendEmail('',$warehouse['email'],'取消入库清单',$content);

            if(!$Result) {
                return json(['code'=>1,'msg'=>'发送失败']);
            }
            return json(['code'=>0,'data'=>'发送成功']);
        }else{
            $procurement_data['goods_info'] = json_decode($procurement_data['goods_info'],true);

            #4、组装采购商品信息
            $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes';
            $dir2 = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
            require_once($dir."/PHPExcel.php");
            require_once($dir2."/IOFactory.php");
            $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
            $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
            $PHPSheet->setTitle('拟入库商品信息'); //给当前活动sheet设置名称
            $PHPSheet->setCellValue('A1','商品名称')
                ->setCellValue('B1','规格名称')
                ->setCellValue('C1','拟入库数量')
                ->setCellValue('D1','商品规格货号')
                ->setCellValue('E1','商品规格条码')
                ->setCellValue('F1','商品规格库码');

            foreach($procurement_data['goods_info'] as $k=>$v){
                $num = $k + 2;
                $goods_sku = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$v['goods_id'],'sku_id'=>$v['sku_id']])->find();
                $quantity = $v['quantity'];
                if($procurement_data['warehouse_type']=='proxy'){
                    $quantity = 99999;
                }
                $PHPSheet->setCellValue("A".$num,"\t".$v['goods_name']."\t")
                    ->setCellValue('B'.$num,"\t".$v['sku_name']."\t")
                    ->setCellValue('C'.$num,$quantity."\t")
                    ->setCellValue('D'.$num,$goods_sku['goods_sn'])
                    ->setCellValue('E'.$num,$goods_sku['goods_barcode'])
                    ->setCellValue('F'.$num,$goods_sku['goods_stockcode']);
            }

            $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
            $fileName  = "电商企业【".$company_data['company']."】拟入库清单__".date('Y-m-d',time()).'.xlsx';
            $path      = $_SERVER['DOCUMENT_ROOT'].'/collect_website/public/bussiness/goods_procurement/'.$fileName;
            $PHPWriter->save($path);

            $content = '<p style="margin-bottom:20px;">若附件信息无误，请点击下方按钮“确认入库”：</p><a href="https://shop.gogo198.cn/collect_website/public/?s=api/func/sure_procurement&id='.$id.'" style="background: #1790ff;color: #fff;padding: 10px 20px;box-sizing: border-box;">确认入库</a>';
            $Result    = $this->sendEmail($path,$warehouse['email'],'拟入库清单',$content);
            unlink($path);
            if(!$Result) {
                return json(['code'=>1,'msg'=>'发送失败']);
            }
            return json(['code'=>0,'data'=>'发送成功']);
        }
    }

    #确认拟入库清单信息
    public function sure_procurement(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        #1、查询采购单信息
        $procurement_data = Db::name('website_procurement')->where(['id'=>$id,'status'=>1])->find();
        if(empty($procurement_data)){
            print_r('该采购单已确认');exit;
        }
        $procurement_data['goods_info'] = json_decode($procurement_data['goods_info'],true);

         if ($procurement_data['warehouse_type'] == 'direct') {
             foreach ($procurement_data['goods_info'] as $goodsItem) {
                 $this->updateWarehouseGoodsStock(
                     $procurement_data['company_id'],
                     $procurement_data['warehouse_id'],
                     $goodsItem['goods_id'],
                     $goodsItem['sku_id'],
                     $goodsItem['quantity']
                 );
             }
         }else{
             foreach ($procurement_data['goods_info'] as $goodsItem) {
                 $this->updateWarehouseGoodsStock(
                     $procurement_data['company_id'],
                     $procurement_data['warehouse_id'],
                     $goodsItem['goods_id'],
                     $goodsItem['sku_id'],
                     99999
                 );
             }
         }

         #3、将采购单改为已完成
         $res = Db::name('website_procurement')->where(['id'=>$id])->update(['status'=>5]);
         if($res){
             echo '确认卖家入库清单信息成功';exit;
         }
    }

    #发送“预打包清单”至指定邮箱
    public function combo_goods_to_email(Request $request){
        $dat = input();
        $combo_id = explode(',',$dat['combo_id']);

        $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes';
        $dir2 = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
        require_once($dir."/PHPExcel.php");
        require_once($dir2."/IOFactory.php");
        $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
        $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('预打包商品信息'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue('A1','套餐名称')
            ->setCellValue('B1','商品名称')
            ->setCellValue('C1','规格名称')
            ->setCellValue('D1','数量')
            ->setCellValue('E1','单位')
            ->setCellValue('F1','')
            ->setCellValue('G1','套餐份数');

        #获取“预打包库存表”信息
        $company_data = [];
        $warehouse = [];
        foreach($combo_id as $k=>$v){
            $combo_info = Db::name('website_warehouse_combo_goodsnum')->where(['id'=>$v])->find();
            $pre_goods_info = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$combo_info['pre_goods_id']])->find();
            $pre_sku_info =  Db::connect($this->config)->name('goods_sku_merchant')->where(['sku_id'=>$combo_info['pre_sku_id']])->find();

            $num = $k + 2;

            $PHPSheet->setCellValue("A".$num,"\t".$combo_info['goods_name']."\t")
                ->setCellValue('B'.$num,"\t".$pre_goods_info['goods_name']."\t")
                ->setCellValue('C'.$num,$pre_sku_info['spec_names']."\t")
                ->setCellValue('D'.$num,$combo_info['num'])
                ->setCellValue('E'.$num,$combo_info['unitname']);

            if($k==0){
                $PHPSheet->setCellValue("G".$num,"\t".$combo_info['sum_num']."\t");
                $company_data = Db::name('website_user_company')->where(['id'=>$combo_info['company_id']])->find();
                $warehouse = Db::name('centralize_warehouse_list')->where(['id'=>$combo_info['warehouse_id']])->find();
            }
        }

        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
        $fileName  = "电商企业【".$company_data['company']."】预打包清单__".date('Y-m-d',time()).'.xlsx';
        $path      = $_SERVER['DOCUMENT_ROOT'].'/collect_website/public/bussiness/goods_combo/'.$fileName;
        $PHPWriter->save($path);

        $content = '<p style="margin-bottom:20px;">若附件信息无误，请点击下方按钮“确认预打包”：</p><a href="https://shop.gogo198.cn/collect_website/public/?s=api/func/sure_combo&id='.$dat['combo_id'].'" style="background: #1790ff;color: #fff;padding: 10px 20px;box-sizing: border-box;">确认预打包</a>';
        $Result    = $this->sendEmail($path,$warehouse['email'],'预打包清单',$content);
        unlink($path);
        if(!$Result) {
            return json(['code'=>1,'msg'=>'发送失败']);
        }
        return json(['code'=>0,'data'=>'发送成功']);
    }

    #仓库确认预打包清单信息
    public function sure_combo(Request $request){
        $dat = input();
        $ids = explode(',',$dat['id']);
        if(!empty($ids)){
            foreach($ids as $k=>$v){
                Db::name('website_warehouse_combo_goodsnum')->where(['id'=>$v,'status'=>0])->update(['status'=>1]);
            }

            echo '确认卖家预打包清单信息成功';exit;
        }
    }

    private function updateWarehouseGoodsStock($company_id, $warehouse_id, $goods_id, $sku_id, $quantity){
        // 查找是否已存在库存记录
        $exist = Db::name('website_warehouse_goodsnum')
            ->where([
                'company_id' => $company_id,
                'warehouse_id' => $warehouse_id,
                'goods_id' => $goods_id,
                'sku_id' => $sku_id
            ])
            ->find();

        if($exist) {
            // 更新现有库存
            Db::name('website_warehouse_goodsnum')
                ->where(['id' => $exist['id']])
                ->update([
                    'num' => $exist['num'] + $quantity,
                    'updatetime' => time()
                ]);
        } else {
            // 新增库存记录
            Db::name('website_warehouse_goodsnum')->insert([
                'company_id' => $company_id,
                'warehouse_id' => $warehouse_id,
                'goods_id' => $goods_id,
                'sku_id' => $sku_id,
                'num' => $quantity,
                'createtime' => time(),
                'updatetime' => time()
            ]);
        }

        // 更新商家商品总库存
        $this->updateMerchantGoodsStock($goods_id, $sku_id, $quantity);
    }

    // 更新商家商品库存
    private function updateMerchantGoodsStock($goods_id, $sku_id, $quantity){
        // 更新商家商品表
        $goods = Db::connect($this->config)->name('goods_merchant')
            ->where(['id' => $goods_id])
            ->find();

        if($goods) {
            $newTotal = $goods['goods_number'] + $quantity;
            Db::connect($this->config)->name('goods_merchant')
                ->where(['id' => $goods_id])
                ->update(['goods_number' => $newTotal]);

            // 更新规格库存
            if($sku_id > 0) {
                $sku = Db::connect($this->config)->name('goods_sku_merchant')
                    ->where(['sku_id' => $sku_id])
                    ->find();

                if($sku) {
                    $skuPrices = json_decode($sku['sku_prices'], true);
                    $skuPrices['goods_number'] = $sku['goods_number'] + $quantity;

                    Db::connect($this->config)->name('goods_sku_merchant')
                        ->where(['sku_id' => $sku_id])
                        ->update([
                            'goods_number' => $sku['goods_number'] + $quantity,
                            'sku_prices' => json_encode($skuPrices, JSON_UNESCAPED_UNICODE)
                        ]);
                }
            }
        }
    }

    //发送电子邮件给商户
    protected function sendEmail($path,$email,$subject = '',$content='') {
        $name    = '系统管理员';

        if($path == 'true'){//没有数据发送
            $status  = cklein_mailAli($email,$name,$subject,$content);
        } else {
            $status  = cklein_mailAli($email,$name,$subject,$content,['0'=>$path]);
        }
        if($status) {
            return true;
        } else {
            return false;
        }
    }

    //获取各类型商品的规格库存
    public function get_goods_num(Request $request){
        $dat = input();
        $goods_id = intval($dat['goods_id']);#商品id
        $sku_id = intval($dat['sku_id']);#规格id
        $shop_id = intval($dat['shop_id']);#商家id
        $wid = intval($dat['wid']);#仓库id
        $goods_type = intval($dat['goods_type']);#商品类型，0单品，1组合

        $gnum = 0;
        $goods = Db::connect($this->config)->name('goods')->where(['goods_id'=>$goods_id])->find();
        #查询仓库是代发还是直发，代发的话直接返回库存99999，直发就按照真实库存
        $warehouse = Db::name('centralize_warehouse_list')->where(['id'=>$goods['wid']])->find();
        if($warehouse['warehouse_form']==2){
            #代发
            return 99999;
        }

        if($goods['buyer_id']>0){
            #买手商品
            #商品可售库存
            $gnum = Db::name('website_warehouse_goodsnum')->where(['company_id' => $shop_id, 'warehouse_id' => $wid, 'goods_id' => $goods_id, 'sku_id' =>$sku_id])->field('num')->find()['num'];
        }
        else {
            #商家商品
            $gmerchant = Db::connect($this->config)->name('goods_merchant')->where(['shelf_id' => $goods_id])->field('id,shelf_id,have_specs')->find();

            $gsku = Db::connect($this->config)->name('goods_sku')->where(['sku_id' => $sku_id])->find();
            $gskumerchant = Db::connect($this->config)->name('goods_sku_merchant')->where(['spec_ids' => $gsku['spec_ids'], 'spec_vids' => $gsku['spec_vids'], 'goods_id' => $gmerchant['id']])->field('sku_id')->find();

            if ($goods_type == 0) {
                #单品

                #商品可售库存
                if($gmerchant['have_specs']==1){
                    $gnum = Db::name('website_warehouse_goodsnum')->where(['company_id' => $shop_id, 'warehouse_id' => $wid, 'goods_id' => $gmerchant['id'], 'sku_id' => $gskumerchant['sku_id']])->field('num')->find()['num'];
                }elseif($gmerchant['have_specs']==2){
                    $gnum = Db::name('website_warehouse_goodsnum')->where(['company_id' => $shop_id, 'warehouse_id' => $wid, 'goods_id' => $gmerchant['id'], 'sku_id' => 0])->field('num')->find()['num'];
                }

                #冻结库存信息====
                #=已付款待发货订单
                $already_buy_not_send = Db::name('website_order_list')->where(['status' => 1])->where('content', 'like', '%"good_id":' . $gmerchant['shelf_id'] . ',%')->field('content')->find();
                $already_buy_not_send['content'] = json_decode($already_buy_not_send['content'], true);
                if (!empty($already_buy_not_send['content'])) {
                    foreach ($already_buy_not_send['content']['goods_info'] as $k2 => $v2) {
                        if ($v2['good_id'] == $gmerchant['shelf_id']) {
                            foreach ($already_buy_not_send['content']['goods_info'][$k2]['sku_info'] as $k3 => $v3) {
                                if ($v3['sku_id'] == $sku_id) {
                                    $gnum -= $v3['goods_num'];
                                }
                            }
                        }
                    }
                }

                #=组合库存
                $combo_goods_num = Db::name('website_warehouse_combo_goodsnum')->where(['warehouse_id' => $wid, 'pre_goods_id' => $gmerchant['id'], 'pre_sku_id' => $gskumerchant['sku_id']])->field('num,sum_num')->find();
                if(empty($combo_goods_num)){
                    $combo_goods_num = Db::name('website_warehouse_combo_goodsnum')->where(['warehouse_id' => $wid, 'pre_goods_id' => $gmerchant['id'], 'pre_sku_id' => 0])->field('num,sum_num')->find();
                }
                if ($combo_goods_num['sum_num'] > 0) {
                    #可售库存-(剩余套餐份数*当前商品规格的套餐打包数量)
                    $gnum = $gnum - ($combo_goods_num['sum_num'] * $combo_goods_num['num']);
                }
            }
            elseif ($goods_type == 1) {
                #组合商品
                $combo_goods_num = Db::name('website_warehouse_combo_goodsnum')->where(['warehouse_id' => $wid, 'goods_id' => $gmerchant['id']])->field('num,sum_num')->find();
                $gnum = $combo_goods_num['sum_num'];#组合商品1份为单位
            }
        }
        return empty($gnum) ? 0 : $gnum;
    }

    //发送《拟打包清单》至指定邮箱
    public function send_package_to_email(Request $request){
        $dat = input();
        $order_id = intval($dat['order_id']);
        $order = Db::name('website_order_list')->where(['id'=>$order_id])->find();
        $order['content'] = json_decode($order['content'],true);
        $order['express_info'] = json_decode($order['express_info'],true);
        $express_info = Db::name('centralize_express_product')->where(['id'=>$order['express_info']['kuaidi_company_id']])->field('name')->find();
        $company_data = Db::name('website_user_company')->where(['id'=>$order['company_id']])->field('company')->find();
        //{"goods_info":[{"good_id":57023,"otherfee_content":null,"otherfee_currency":null,"otherfee_total":null,"reduction_content":null,"reduction_money":null,"prefe_gift":null,"prefe_reduction":null,"gift_money":null,"noinclude_content":null,"noinclude_money":null,"potential_content":null,"potential_money":null,"file":null,"services":"[{\"service_id\":2},{\"service_id\":12},{\"service_id\":13}]","sku_info":[{"sku_id":1652906,"goods_num":3,"price":"26.40","currency":"5","cart_id":167}]}],"warehouse_id":16,"delivery_method":1,"gather_method":0,"line_id":0,"address_id":19}
        $warehouse_info = Db::name('centralize_warehouse_list')->where(['id'=>$dat['warehouse_id']])->find();

        foreach($order['content']['goods_info'] as $k=>$v){
            $goods = Db::connect($this->config)->name('goods')->where(['goods_id'=>$v['good_id']])->field('goods_type,goods_name')->find();
            $order['content']['goods_info'][$k]['goods_type'] = $goods['goods_type'];#0单品，1组合
            $order['content']['goods_info'][$k]['goods_name'] = $goods['goods_name'];#商品名称
        }

        try {
            #组装订单商品信息（单品/组合商品）
            $dir = $_SERVER['DOCUMENT_ROOT'] . '/collect_website/vendor/phpoffice/phpexcel/Classes';
            $dir2 = $_SERVER['DOCUMENT_ROOT'] . '/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
            require_once($dir . "/PHPExcel.php");
            require_once($dir2 . "/IOFactory.php");
            $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
            $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
            $PHPSheet->setTitle('拟打包清单'); //给当前活动sheet设置名称
            $PHPSheet->setCellValue('A1', '运单编号')
                ->setCellValue('B1', '快递企业')
                ->setCellValue('C1', '商品名称')
                ->setCellValue('D1', '规格名称')
                ->setCellValue('E1', '数量')
                ->setCellValue('F1', '单位')
                ->setCellValue('G1', '套餐名称')
                ->setCellValue('H1', '套餐份数');

            foreach ($order['content']['goods_info'] as $k => $v) {
                foreach ($v['sku_info'] as $k2 => $v2) {
                    $num = $k + 2;

                    $goods_sku = Db::connect($this->config)->name('goods_sku')->where(['goods_id' => $v['good_id'], 'sku_id' => $v2['sku_id']])->find();
                    $goods_sku['sku_prices'] = json_decode($goods_sku['sku_prices'], true);

                    $unit = Db::name('unit')->where(['code_value' => $goods_sku['sku_prices']['unit'][0]])->value('code_name');
                    if ($v['goods_type'] == 0) {
                        #单品
                        $PHPSheet->setCellValue("A" . $num, "\t" . $order['express_info']['kuaidinum'] . "\t")
                            ->setCellValue('B' . $num, "\t" . $express_info['name'] . "\t")
                            ->setCellValue('C' . $num, $v['goods_name'] . "\t")
                            ->setCellValue('D' . $num, $goods_sku['spec_names'])
                            ->setCellValue('E' . $num, $v2['goods_num'])
                            ->setCellValue('F' . $num, $unit)
                            ->setCellValue('G' . $num, '')
                            ->setCellValue('H' . $num, '');
                    } elseif ($v['goods_type'] == 1) {
                        #组合
                        $PHPSheet->setCellValue("A" . $num, "\t" . $order['express_info']['kuaidinum'] . "\t")
                            ->setCellValue('B' . $num, "\t" . $express_info['name'] . "\t")
                            ->setCellValue('C' . $num, '' . "\t")
                            ->setCellValue('D' . $num, '')
                            ->setCellValue('E' . $num, '')
                            ->setCellValue('F' . $num, '')
                            ->setCellValue('G' . $num, $v['goods_name'])
                            ->setCellValue('H' . $num, $v2['goods_num']);
                    }
                }
            }

            $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
            $fileName = "电商企业【" . $company_data['company'] . "】拟打包清单__" . date('Y-m-d', time()) . '.xlsx';
            $path = $_SERVER['DOCUMENT_ROOT'] . '/collect_website/public/bussiness/goods_procurement/' . $fileName;
            $PHPWriter->save($path);

            $content = '<p style="margin-bottom:20px;">若附件信息无误，请点击下方按钮“确认打包”：</p><a href="https://shop.gogo198.cn/collect_website/public/?s=api/func/sure_package&id=' . $order_id . '" style="background: #1790ff;color: #fff;padding: 10px 20px;box-sizing: border-box;">确认打包</a>';
            $Result = $this->sendEmail($path, $warehouse_info['email'], '拟打包清单', $content);
            unlink($path);
            if (!$Result) {
                return json(['code' => 1, 'msg' => '发送失败']);
            }
            return json(['code' => 0, 'data' => '发送成功']);
        }
        catch (\Exception $e) {
            return json(['code' => -1,'msg'=>$e->getMessage()]);
        }
    }

    //发送《拟发货清单》至指定邮箱
    public function send_delivery_to_email(Request $request){
        $dat = input();
        $order_id = intval($dat['id']);
        $order = Db::name('website_order_list')->where(['id'=>$order_id])->find();
        $order['content'] = json_decode($order['content'],true);
        $order['express_info'] = json_decode($order['express_info'],true);
        $express_info = [];
        $kuaidinum = '';
        if($order['is_daifa']==1){
            #直发
            $express_info = Db::name('centralize_express_product')->where(['id'=>$order['express_info']['kuaidi_company_id']])->field('name')->find();
            $kuaidinum = $order['express_info']['kuaidinum'];
        }elseif($order['is_daifa']==2){
            #代发
            $order['daifa_express_info'] = json_decode($order['daifa_express_info'],true);
            $express_info = Db::name('centralize_express_product')->where(['id'=>$order['daifa_express_info']['express_id']])->field('name')->find();
            $kuaidinum = $order['daifa_express_info']['express_no'];
        }
        
        $company_data = Db::name('website_user_company')->where(['id'=>$order['company_id']])->field('company')->find();
       
        $warehouse_info = Db::name('centralize_warehouse_list')->where(['id'=>$dat['warehouse_id']])->find();

        foreach($order['content']['goods_info'] as $k=>$v){
            $goods = Db::connect($this->config)->name('goods')->where(['goods_id'=>$v['good_id']])->field('goods_type,goods_name')->find();
            $order['content']['goods_info'][$k]['goods_type'] = $goods['goods_type'];#0单品，1组合
            $order['content']['goods_info'][$k]['goods_name'] = $goods['goods_name'];#商品名称
        }

        try {
            #组装订单商品信息（单品/组合商品）
            $dir = $_SERVER['DOCUMENT_ROOT'] . '/collect_website/vendor/phpoffice/phpexcel/Classes';
            $dir2 = $_SERVER['DOCUMENT_ROOT'] . '/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
            require_once($dir . "/PHPExcel.php");
            require_once($dir2 . "/IOFactory.php");
            $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
            $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
            $PHPSheet->setTitle('拟发货清单'); //给当前活动sheet设置名称
            $PHPSheet->setCellValue('A1', '运单编号')
                ->setCellValue('B1', '快递企业')
                ->setCellValue('C1', '商品名称')
                ->setCellValue('D1', '规格名称')
                ->setCellValue('E1', '数量')
                ->setCellValue('F1', '单位')
                ->setCellValue('G1', '套餐名称')
                ->setCellValue('H1', '套餐份数');

            foreach ($order['content']['goods_info'] as $k => $v) {
                foreach ($v['sku_info'] as $k2 => $v2) {
                    $num = $k + 2;

                    $goods_sku = Db::connect($this->config)->name('goods_sku')->where(['goods_id' => $v['good_id'], 'sku_id' => $v2['sku_id']])->find();
                    $goods_sku['sku_prices'] = json_decode($goods_sku['sku_prices'], true);

                    $unit = Db::name('unit')->where(['code_value' => $goods_sku['sku_prices']['unit'][0]])->value('code_name');
                    
                    
                    if ($v['goods_type'] == 0) {
                        #单品
                        $PHPSheet->setCellValue("A" . $num, "\t" . $kuaidinum . "\t")
                            ->setCellValue('B' . $num, "\t" . $express_info['name'] . "\t")
                            ->setCellValue('C' . $num, $v['goods_name'] . "\t")
                            ->setCellValue('D' . $num, $goods_sku['spec_names'])
                            ->setCellValue('E' . $num, $v2['goods_num'])
                            ->setCellValue('F' . $num, $unit)
                            ->setCellValue('G' . $num, '')
                            ->setCellValue('H' . $num, '');
                    } elseif ($v['goods_type'] == 1) {
                        #组合
                        $PHPSheet->setCellValue("A" . $num, "\t" . $kuaidinum . "\t")
                            ->setCellValue('B' . $num, "\t" . $express_info['name'] . "\t")
                            ->setCellValue('C' . $num, '' . "\t")
                            ->setCellValue('D' . $num, '')
                            ->setCellValue('E' . $num, '')
                            ->setCellValue('F' . $num, '')
                            ->setCellValue('G' . $num, $v['goods_name'])
                            ->setCellValue('H' . $num, $v2['goods_num']);
                    }
                }
            }

            $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
            $fileName = "电商企业【" . $company_data['company'] . "】拟发货清单__" . date('Y-m-d', time()) . '.xlsx';
            $path = $_SERVER['DOCUMENT_ROOT'] . '/collect_website/public/bussiness/goods_procurement/' . $fileName;
            $PHPWriter->save($path);

            // $content = '<p style="margin-bottom:20px;">若附件信息无误，请点击下方按钮“确认打包”：</p><a href="https://shop.gogo198.cn/collect_website/public/?s=api/func/sure_package&id=' . $order_id . '" style="background: #1790ff;color: #fff;padding: 10px 20px;box-sizing: border-box;">确认打包</a>';
            $content = '';
            $Result = $this->sendEmail($path, $warehouse_info['email'], '拟发货清单', $content);
            unlink($path);
            if (!$Result) {
                return json(['code' => 1, 'msg' => '发送失败']);
            }
            return json(['code' => 0, 'data' => '发送成功']);
        }
        catch (\Exception $e) {
            return json(['code' => -1,'msg'=>$e->getMessage()]);
        }
    }

    //仓库确认拟打包清单信息
    public function sure_package(Request $request){
        $dat = input();
        $orderid = intval($dat['id']);

        Db::name('website_order_list')->where(['id'=>$orderid,'status'=>1,'is_package'=>0])->update(['is_package'=>1]);

        echo '确认卖家拟打包清单信息成功';exit;
    }

    ##快递100接口========================================================================START
    public function print_order_test(Request $request){
        $data = input();

        $warehouse = Db::name('centralize_warehouse_list')->where(['id'=>28])->find();
        $terminal = Db::name('centralize_warehouse_printer')->where(['warehouse_id'=>$warehouse['id']])->find();

        // 参数设置
        $key = $terminal['key'];                             // 客户授权key
        $secret = $terminal['secret'];                          // 授权secret
        list($msec, $sec) = explode(' ', microtime());
        $t = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);    // 当前时间戳
        $param = array (
            'printType' => 'CLOUD',            //（必填）打印类型，NON:只下单不打印（默认）；IMAGE:生成图片短链；HTML:生成html短链；CLOUD:使用快递100云打印机打印
            'partnerId' => 'GSZK中山项目网点1020600',                 //（必填）电子面单客户账户或月结账号
            'partnerKey' => '',                // 电子面单密码
            'partnerSecret' => '',             // 电子面单密钥
            'partnerName' => '',               // 电子面单客户账户名称
            'net' => '',                       // 收件网点名称,由快递公司当地网点分配
            'code' => '',                      // 电子面单承载编号
            'checkMan' => '',                  // 电子面单承载快递员名
            'tbNet' => '',                     // 在使用菜鸟/淘宝/拼多多授权电子面单时，若月结账号下存在多个网点，则tbNet="网点名称,网点编号" ，注意此处为英文逗号
            'kuaidicom' => 'shunfengkuaiyun',                 //（必填）快递公司的编码：https://api.kuaidi100.com/document/5f0ff6e82977d50a94e10237.html
            'recMan' => array (                //（必填）
                'name' => '寄件人',                  // 收件人姓名
                'mobile' => '13129043380',                // 收件人的手机号，手机号和电话号二者其一必填
                'tel' => '',                   // 收件人的电话号，手机号和电话号二者其一必填
                'printAddr' => '广东省佛山市南海区桂城珠江开关D611',             // 收件人地址
                'company' => ''                // 收件人公司名（非必填）
            ),
            'sendMan' => array (               //（必填）
                'name' => '收件人',                  // 寄件人姓名
                'mobile' => '13119893380',                // 寄件人的手机号，手机号和电话号二者其一必填
                'tel' => '',                   // 寄件人的电话号，手机号和电话号二者其一必填
                'printAddr' => '广东省佛山市南海区桂城珠江开关D611',             // 寄件人地址
                'company' => ''                // 寄件人公司名（非必填）
            ),
            'cargo' => '测试',                     //（必填）物品名称
            'count' => '1',                     //（必填）物品总数量
            'weight' => '0.5',                 //（非必填）物品总重量KG
            'payType' => 'SHIPPER',            // 支付方式，SHIPPER：寄方付（默认） CONSIGNEE：到付 MONTHLY：月结 THIRDPARTY：第三方支付
            'expType' => '标准快递',           // 快递类型: 标准快递（默认）、顺丰特惠、EMS经济
            'remark' => '测试',                //（非必填）备注
            'siid' => $terminal['siid'],                      //（必填）设备编码
            'direction' => '0',                //（非必填）打印方向，0：正方向（默认）； 1：反方向；只有printType为CLOUD时该参数生效
            'tempId' => 'fm_76165_standard1_SZQHBDWL',                    //（必填）主单模板：快递公司模板V2链接：https://api.kuaidi100.com/manager/v2/shipping-label/template-shipping-label
            'childTempId' => '',               // 子单模板：快递公司模板V2链接：https://api.kuaidi100.com/manager/v2/shipping-label/template-shipping-label
            'backTempId' => '',                // 回单模板：快递公司模板V2链接：https://api.kuaidi100.com/manager/v2/shipping-label/template-shipping-label
            'valinsPay' => '',                 // 保价额度
            'collection' => '',                // 代收货款额度
            'needChild' => '0',                // 是否需要子单
            'needBack' => '0',                 // 是否需要回单
            'orderId' => null,                 // （非必填）贵司内部自定义的订单编号,需要保证唯一性
            'callBackUrl' => null,             //（非必填）打印状态回调地址，默认仅支持http
            'salt' => '',                      // 签名用随机字符串
            'needSubscribe' => false,          // 是否开启订阅功能 false：不开启(默认)；true：开启
            'pollCallBackUrl' => null,         // 如果needSubscribe 设置为true时，pollCallBackUrl必须填入，用于跟踪回调
            'resultv2' => '0',                 // 添加此字段表示开通行政区域解析或地图轨迹功能
            'needDesensitization' => false,    // 是否脱敏 false：关闭（默认）；true：开启
            'needLogo' => false,               // 面单是否需要logo false：关闭（默认）；true：开启
            'thirdOrderId' => null,            // 平台导入返回的订单id：如平台类加密订单，使用此下单为必填
            'oaid' => null,                    // 淘宝订单收件人ID (Open Addressee ID)，长度不超过128个字符，淘宝订单加密情况用于解密
            'thirdTemplateURL' => null,        // 第三方平台面单基础模板链接，如为第三方平台导入订单选填，如不填写，默认返回两联面单模板
            'thirdCustomTemplateUrl' => null,  // 第三方平台自定义区域模板地址
            'customParam' => null,             // 面单自定义参数
            'needOcr' => false,                // 第三方平台订单是否需要开启ocr，开启后将会通过推送方式推送 false：关闭（默认）；true：开启
            'ocrInclude' => null,              // orc需要检测识别的面单元素
            'height' => null,                  // 打印纸的高度，以mm为单位
            'width' => null                    // 打印纸的宽度，以mm为单位
        );

        //请求参数
        $post_data = array();
        $post_data['param'] = json_encode($param, JSON_UNESCAPED_UNICODE);
        $post_data['key'] = $key;
        $post_data['t'] = $t;
        $sign = md5($post_data['param'].$t.$key.$secret);
        $post_data['sign'] = strtoupper($sign);

        $url = 'https://api.kuaidi100.com/label/order?method=order';    // 电子面单下单接口请求地址

        echo '请求参数：<br/><pre>';
        echo print_r($post_data);
        echo '</pre>';

        //发送post请求
        $data = $this->curlpost($url,$post_data);

        echo '<br/><br/>返回数据：<br/><pre>';
        echo print_r($data);
        //echo var_dump($data);
        echo '</pre>';
    }

    #github地址：https://github.com/kuaidi100-api/php-demo
    #电子面单下单（接口地址：https://api.kuaidi100.com/document/dianzimiandanV2）
    public function print_order(Request $request){
        $data = input();

        $orderid = intval($data['order_id']);
        $warehouse_express_id = intval($data['warehouse_express_id']);
        $company_id = intval($data['company_id']);

        $order = Db::name('website_order_list')->where(['id'=>$orderid])->find();#订单信息
        $order['content'] = json_decode($order['content'],true);
//        dd($order['content']);
        $printer_info = Db::name('centralize_warehouse_express')->where(['id'=>$warehouse_express_id])->find();#仓库打印机快递和产品信息
        $printer = Db::name('centralize_warehouse_printer')->where(['id'=>$printer_info['printer_id']])->find();#仓库打印机信息
        $express = Db::name('centralize_express_product')->where(['id'=>$printer_info['express_id']])->find();#快递100企业信息

        #买家&卖家备注====start
        $remark = '';
        if(!empty($order['remark'])){
            $remark = '买家备注：'.$order['remark'];
        }

        #买家&卖家备注====end

        #买家地址====start
        $buyer_address = Db::name('centralize_user_address')->where(['id'=>$order['address_id']])->find();
        $province = $city = $area_info = $area_info2 = $area_info3 = $area_info4 = '';
//        $country = Db::name('centralize_diycountry_content')->where(['id' => $buyer_address['country_id']])->find();#国
        if($buyer_address['have_postal_code']==1){
            #有邮政编码
            $province = $buyer_address['pre_address'];
        }
        elseif($buyer_address['have_postal_code']==2){
            #无邮政编码
            if($buyer_address['province']>0){
                $province = Db::name('centralize_country_areas')->where(['id'=>$buyer_address['province']])->field('name')->find()['name'];
            }
            if($buyer_address['city']>0){
                $city = Db::name('centralize_country_areas')->where(['id'=>$buyer_address['city']])->field('name')->find()['name'];
            }
            if($buyer_address['area']>0){
                $area_info = Db::name('centralize_country_areas')->where(['id'=>$buyer_address['area']])->field('name')->find()['name'];
            }
            if($buyer_address['area2']>0){
                $area_info2 = Db::name('centralize_country_areas')->where(['id'=>$buyer_address['area2']])->field('name')->find()['name'];
            }
            if($buyer_address['area3']>0){
                $area_info3 = Db::name('centralize_country_areas')->where(['id'=>$buyer_address['area3']])->field('name')->find()['name'];
            }
            if($buyer_address['area4']>0){
                $area_info4 = Db::name('centralize_country_areas')->where(['id'=>$buyer_address['area4']])->field('name')->find()['name'];
            }
        }
        $buyer_address['address2'] = json_decode($buyer_address['address2'], true);
        $buyer_address2 = '';
        if (!empty($buyer_address['address2'])) {
            foreach ($buyer_address['address2'] as $k => $v) {
                $buyer_address2 .= '/'.$v;
            }
        }
        $buyer_address['address'] = $province . $city . $area_info . $area_info2 . $area_info3 . $area_info4 . $buyer_address['address1'];
        #买家地址====end

        #仓库地址====start
        $warehouse_address = '';
        $warehouse = Db::name('centralize_warehouse_list')->where(['id'=>$printer['warehouse_id']])->find();#仓库信息
        $warehouse_merchant = [];
        foreach($order['content']['goods_info'] as $k=>$v){
            $gw = Db::connect($this->config)->name('goods_merchant')->where(['cid'=>$company_id,'shelf_id'=>$v['good_id']])->field('wid')->find();
            $warehouse_merchant = Db::name('centralize_warehouse_merchant')->where(['id'=>$gw['wid']])->find();
        }
        if($warehouse_merchant['type']==0){
            #使用仓库寄件信息
            if($warehouse['have_postal_code']==1){
                #有邮政编码
                $warehouse_address = $warehouse['pre_address'].$warehouse['address1'];
            }
            elseif($warehouse['have_postal_code']==2){
                #无邮政编码
                $warehouse['province_name'] = $warehouse['city_name'] = $warehouse['district_name'] = $warehouse['town_name'] = $warehouse['village_name'] = '';

                if(!empty($warehouse['province_code'])){
                    $warehouse['province_name'] = Db::name('centralize_country_areas')->where(['id'=>$warehouse['province_code']])->field('name')->find()['name'];
                }

                if(!empty($warehouse['city_code'])){
                    $warehouse['city_name'] = Db::name('centralize_country_areas')->where(['id'=>$warehouse['city_code']])->field('name')->find()['name'];
                }

                if(!empty($warehouse['district_code'])){
                    $warehouse['district_name'] = Db::name('centralize_country_areas')->where(['id'=>$warehouse['district_code']])->field('name')->find()['name'];
                }

                if(!empty($warehouse['town_code'])){
                    $warehouse['town_name'] = Db::name('centralize_country_areas')->where(['id'=>$warehouse['town_code']])->field('name')->find()['name'];
                }

                if(!empty($warehouse['village_code'])){
                    $warehouse['village_name'] = Db::name('centralize_country_areas')->where(['id'=>$warehouse['village_code']])->field('name')->find()['name'];
                }
                $warehouse_address = $warehouse['province_name'] . $warehouse['city_name'] . $warehouse['district_name'] . $warehouse['town_name'] . $warehouse['village_name'] . $warehouse['address1'];
            }
        }
        elseif($warehouse_merchant['type']==1){
            #使用自定义寄件人信息
            if($warehouse_merchant['have_postal_code']==1){
                #有邮政编码
                $warehouse_address = $warehouse_merchant['pre_address'].$warehouse_merchant['address1'];
            }
            elseif($warehouse_merchant['have_postal_code']==2){
                #无邮政编码
                $warehouse_merchant['province_name'] = $warehouse_merchant['city_name'] = $warehouse_merchant['district_name'] = $warehouse_merchant['town_name'] = $warehouse_merchant['village_name'] = '';

                if(!empty($warehouse_merchant['province_code'])){
                    $warehouse_merchant['province_name'] = Db::name('centralize_country_areas')->where(['id'=>$warehouse_merchant['province_code']])->field('name')->find()['name'];
                }

                if(!empty($warehouse_merchant['city_code'])){
                    $warehouse_merchant['city_name'] = Db::name('centralize_country_areas')->where(['id'=>$warehouse_merchant['city_code']])->field('name')->find()['name'];
                }

                if(!empty($warehouse_merchant['district_code'])){
                    $warehouse_merchant['district_name'] = Db::name('centralize_country_areas')->where(['id'=>$warehouse_merchant['district_code']])->field('name')->find()['name'];
                }

                if(!empty($warehouse_merchant['town_code'])){
                    $warehouse_merchant['town_name'] = Db::name('centralize_country_areas')->where(['id'=>$warehouse_merchant['town_code']])->field('name')->find()['name'];
                }

                if(!empty($warehouse_merchant['village_code'])){
                    $warehouse_merchant['village_name'] = Db::name('centralize_country_areas')->where(['id'=>$warehouse_merchant['village_code']])->field('name')->find()['name'];
                }
                $warehouse_address = $warehouse_merchant['province_name'] . $warehouse_merchant['city_name'] . $warehouse_merchant['district_name'] . $warehouse_merchant['town_name'] . $warehouse_merchant['village_name'] . $warehouse_merchant['address1'];
            }
        }

        #仓库地址====end

        #商品信息====start
        $goods_name = '';
        $gpayinfo = ['paymethod'=>'SHIPPER'];//支付方式，SHIPPER：寄方付（默认） CONSIGNEE：到付 MONTHLY：月结 THIRDPARTY：第三方支付
        $goods_num = 0;
        foreach($order['content']['goods_info'] as $k=>$v){
            $ginfo = Db::connect($this->config)->name('goods')->where(['goods_id'=>$v['good_id']])->field('goods_name,is_baoyou')->find();
//            $gpayinfo = Db::connect($this->config)->name('goods_merchant')->where(['shelf_id'=>$v['good_id']])->field('express_info')->find();
//            $gpayinfo['express_info'] = json_decode($gpayinfo['express_info'],true);
//            foreach($gpayinfo['express_info']['express_info'] as $k2=>$v2){
//                if($v2['express_id']==$warehouse_express_id){
//                    $payname = ['1'=>'MONTHLY','2'=>'SHIPPER','3'=>'CONSIGNEE'];
//                    $gpayinfo['paymethod'] = $payname[$v2['express_paytype']];
//
//                    if(!empty($v2['express_remark'])){
//                        $remark .= '。卖家备注：'.$v2['express_remark'].'。';
//                    }
//                }
//            }
            $payname = ['1'=>'MONTHLY','2'=>'SHIPPER','3'=>'CONSIGNEE'];
            $gpayinfo['paymethod'] = $payname[$ginfo['is_baoyou']];
            $goods_name .= $ginfo['goods_name'].'、';

            foreach($v['sku_info'] as $k2=>$v2){
                $goods_num += $v2['goods_num'];
            }
        }
        $goods_name = rtrim($goods_name,'、');
        #商品信息====end

        // 参数设置
        $key = $printer['key'];                             // 客户授权key
        $secret = $printer['secret'];                          // 授权secret
        list($msec, $sec) = explode(' ', microtime());
        $t = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);    // 当前时间戳
        $param = array (
            'printType' => 'CLOUD',            //（必填）打印类型，NON:只下单不打印（默认）；IMAGE:生成图片短链；HTML:生成html短链；CLOUD:使用快递100云打印机打印
            'partnerId' => $printer_info['partnerId'],                 //（必填）电子面单客户账户或月结账号
            'partnerKey' => $printer_info['partnerKey'],                // 电子面单密码
            'partnerSecret' => $printer_info['partnerSecret'],             // 电子面单密钥
            'partnerName' => $printer_info['partnerName'],               // 电子面单客户账户名称
            'net' => $printer_info['net'],                       // 收件网点名称,由快递公司当地网点分配
            'code' => $printer_info['code'],                      // 电子面单承载编号
            'checkMan' => '',                  // 电子面单承载快递员名
            'tbNet' => '',                     // 在使用菜鸟/淘宝/拼多多授权电子面单时，若月结账号下存在多个网点，则tbNet="网点名称,网点编号" ，注意此处为英文逗号
            'kuaidicom' => $express['code'],                 //（必填）快递公司的编码：https://api.kuaidi100.com/document/5f0ff6e82977d50a94e10237.html
            'recMan' => array (                //（必填）
                'name' => $buyer_address['user_name'],                  // 收件人姓名
                'mobile' => $buyer_address['mobile'],                // 收件人的手机号，手机号和电话号二者其一必填
                'tel' => $buyer_address['mobile2'],                   // 收件人的电话号，手机号和电话号二者其一必填
                'printAddr' => $buyer_address['address'],             // 收件人地址
                'company' => ''                // 收件人公司名（非必填）
            ),
            'sendMan' => array (               //（必填）
                'name' => $warehouse['name'],                  // 寄件人姓名
                'mobile' => $warehouse['mobile'],                // 寄件人的手机号，手机号和电话号二者其一必填
                'tel' => '',                   // 寄件人的电话号，手机号和电话号二者其一必填
                'printAddr' => $warehouse_address,             // 寄件人地址
                'company' => ''                // 寄件人公司名（非必填）
            ),
            'cargo' => $goods_name,                     //（必填）物品名称
            'count' => $goods_num,                     //（必填）物品总数量
            'weight' => '0.5',                 //（非必填）物品总重量KG
            'payType' => $gpayinfo['paymethod'],            // 支付方式，SHIPPER：寄方付（默认） CONSIGNEE：到付 MONTHLY：月结 THIRDPARTY：第三方支付
            'expType' => $printer_info['express_type'],           // 快递类型: 标准快递（默认）、顺丰特惠、EMS经济
            'remark' => $remark,                //（非必填）备注
            'siid' => $printer['siid'],                      //（必填）设备编码
            'direction' => '0',                //（非必填）打印方向，0：正方向（默认）； 1：反方向；只有printType为CLOUD时该参数生效
            'tempId' => $printer_info['tempId'],                    //（必填）主单模板：快递公司模板V2链接：https://api.kuaidi100.com/manager/v2/shipping-label/template-shipping-label
            'childTempId' => '',               // 子单模板：快递公司模板V2链接：https://api.kuaidi100.com/manager/v2/shipping-label/template-shipping-label
            'backTempId' => '',                // 回单模板：快递公司模板V2链接：https://api.kuaidi100.com/manager/v2/shipping-label/template-shipping-label
            'valinsPay' => '',                 // 保价额度
            'collection' => '',                // 代收货款额度
            'needChild' => '0',                // 是否需要子单
            'needBack' => '0',                 // 是否需要回单
            'orderId' => $order['ordersn'],                 // （非必填）贵司内部自定义的订单编号,需要保证唯一性
            'callBackUrl' => null,             //（非必填）打印状态回调地址，默认仅支持http
            'salt' => '',                      // 签名用随机字符串
            'needSubscribe' => false,          // 是否开启订阅功能 false：不开启(默认)；true：开启
            'pollCallBackUrl' => null,         // 如果needSubscribe 设置为true时，pollCallBackUrl必须填入，用于跟踪回调
            'resultv2' => '0',                 // 添加此字段表示开通行政区域解析或地图轨迹功能
            'needDesensitization' => false,    // 是否脱敏 false：关闭（默认）；true：开启
            'needLogo' => false,               // 面单是否需要logo false：关闭（默认）；true：开启
            'thirdOrderId' => null,            // 平台导入返回的订单id：如平台类加密订单，使用此下单为必填
            'oaid' => null,                    // 淘宝订单收件人ID (Open Addressee ID)，长度不超过128个字符，淘宝订单加密情况用于解密
            'thirdTemplateURL' => null,        // 第三方平台面单基础模板链接，如为第三方平台导入订单选填，如不填写，默认返回两联面单模板
            'thirdCustomTemplateUrl' => null,  // 第三方平台自定义区域模板地址
            'customParam' => null,             // 面单自定义参数
            'needOcr' => false,                // 第三方平台订单是否需要开启ocr，开启后将会通过推送方式推送 false：关闭（默认）；true：开启
            'ocrInclude' => null,              // orc需要检测识别的面单元素
            'height' => null,                  // 打印纸的高度，以mm为单位
            'width' => null                    // 打印纸的宽度，以mm为单位
        );

        //请求参数
        $post_data = array();
        $post_data['param'] = json_encode($param, JSON_UNESCAPED_UNICODE);
        $post_data['key'] = $key;
        $post_data['t'] = $t;
        $sign = md5($post_data['param'].$t.$key.$secret);
        $post_data['sign'] = strtoupper($sign);

        $url = 'https://api.kuaidi100.com/label/order?method=order';    // 电子面单下单接口请求地址

//        echo '请求参数：<br/><pre>';
//        echo print_r($post_data);
//        echo '</pre>';
//
//        //发送post请求
        $data = $this->curlpost($url,$post_data);
//
//        echo '<br/><br/>返回数据：<br/><pre>';
//        echo print_r($data);
//        //echo var_dump($data);
//        echo '</pre>';

        return json_encode($data,true);
    }

    #电子面单复打接口
    public function print_old(Request $request){
        $data = input();

        // 参数设置
        $key = '';                             // 客户授权key
        $secret = '';                          // 授权secret
        list($msec, $sec) = explode(' ', microtime());
        $t = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);    // 当前时间戳
        $param = array (
            'taskId' => '',                    // 任务ID
            'siid' => '',                      // 快递100打印机,不填默认为下单时填入的siid
        );

        //请求参数
        $post_data = array();
        $post_data['param'] = json_encode($param, JSON_UNESCAPED_UNICODE);
        $post_data['key'] = $key;
        $post_data['t'] = $t;
        $sign = md5($post_data['param'].$t.$key.$secret);
        $post_data['sign'] = strtoupper($sign);

        $url = 'https://api.kuaidi100.com/label/order?method=printOld';    // 电子面单复打接口请求地址

        echo '请求参数：<br/><pre>';
        echo print_r($post_data);
        echo '</pre>';

        //发送post请求
        $data = $this->curlpost($url,$post_data);

        echo '<br/><br/>返回数据：<br/><pre>';
        echo print_r($data);
        //echo var_dump($data);
        echo '</pre>';
    }

    #电子面单取消接口
    public function ele_cancel(Request $request){
        $dat = input();

        // 参数设置
        $key = '';                             // 客户授权key
        $secret = '';                          // 授权secret
        list($msec, $sec) = explode(' ', microtime());
        $t = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);    // 当前时间戳
        $param = array (
            'partnerId' => '',                 // 电子面单客户账户或月结账号
            'partnerKey' => '',                // 电子面单密码
            'partnerSecret' => '',             // 电子面单密钥
            'partnerName' => '',               // 电子面单客户账户名称
            'net' => '',                       // 收件网点名称,由快递公司当地网点分配
            'code' => '',                      // 电子面单承载编号
            'kuaidicom' => '',                 // 快递公司的编码：https://api.kuaidi100.com/document/5f0ff6e82977d50a94e10237.html
            'kuaidinum' => '',                 // 快递单号
            'orderId' => '',                   // 快递公司订单号，对应下单时返回的kdComOrderNum，如果下单时有返回该字段，则取消时必填，否则可以不填
            'checkMan' => '',                  // 业务员编码，部分快递公司必填
            'expType' => '',                   // 产品业务类型，部分快递公司必填
            'reason' => ''                     // 取消原因
        );

        // 请求参数
        $post_data = array();
        $post_data['param'] = json_encode($param, JSON_UNESCAPED_UNICODE);
        $post_data['key'] = $key;
        $post_data['t'] = $t;
        $sign = md5($post_data['param'].$t.$key.$secret);
        $post_data['sign'] = strtoupper($sign);

        $url = 'https://api.kuaidi100.com/label/order?method=cancel';    // 电子面单取消请求地址

        echo '请求参数：<br/><pre>';
        echo print_r($post_data);
        echo '</pre>';

        // 发送post请求
        $data = $this->curlpost($url,$post_data);

        echo '<br/><br/>返回数据：<br/><pre>';
        echo print_r($data);
        //echo var_dump($data);
        echo '</pre>';
    }

    #实时查询物流轨迹接口（全部订单）
    public function syn_query(Request $request){
        $data = input();
        $time = time();

        $ep_set = Db::name('centralize_express_set')->find();
        $starttime = date('Y-m-d 00:00:00',$time);
        $endtime = date('Y-m-d 23:59:59',$time);
        if($ep_set['type']==1){
            #一天一次查询物流轨迹
            $ishave = Db::name('centralize_express_track_log')->whereRaw('query_time>='.$starttime.' and query_time<='.$endtime)->find();
            if(!empty($ishave)){
                #今天查过就不查
                return false;
            }
        }
        elseif($ep_set['type']==2){
            #一天隔N小时查询物流轨迹
            $ishave = Db::name('centralize_express_track_log')->whereRaw('query_time>='.$starttime.' and query_time<='.$endtime)->order('id desc')->find();
            $interval_time = $ishave['query_time'] + (intval($ep_set['type2']) * 3600);
            if($ishave['query_time']>=$interval_time){
                #超过间隔时间则查询
            }else{
                #不超过则不查询
                return false;
            }
        }

        //参数设置
        $key = 'PsCJSIbk321';                        // 客户授权key
        $customer = '38DACDC8BF738FB9C3F807039DBF4D61';                   // 查询公司编号

        //查询所有“已打印面单和已发货和已打包的物流订单信息”
        $order_list = Db::name('website_order_list')->whereRaw('(status=1 or status=2) and is_package=1')->select();
        foreach($order_list as $k=>$v){
            $website_user = Db::name('website_user')->where(['id'=>$v['user_id']])->field('openid')->find();

            $order_list[$k]['express_info'] = json_decode($v['express_info'],true);

            $kuaidinum = '';
            $express_info = [];
            $express_code = '';
            if($v['is_daifa']==1){
                //直发
                $express_info = Db::name('centralize_express_product')->where(['id'=>$order_list[$k]['express_info']['kuaidi_company_id']])->find();
                $kuaidinum = $v['express_info']['kuaidinum'];
                $express_code = $express_info['code'];
            }
            elseif($v['is_daifa']==2){
                //代发
                $daifa_express_info = json_decode($v['daifa_express_info'],true);
                $express_info = Db::name('centralize_express_product')->where(['id'=>$daifa_express_info['express_id']])->find();
                $kuaidinum = $daifa_express_info['express_no'];
                $express_code = $express_info['code'];
            }

            if(!empty($kuaidinum) && !empty($express_code)){
                $param = array (
                    'com' => $express_code,             // 快递公司编码
                    'num' => $kuaidinum,     // 快递单号
                    'phone' => '',                // 手机号
                    'from' => '',                 // 出发地城市
                    'to' => '',                   // 目的地城市
                    'resultv2' => '1',            // 开启行政区域解析
                    'show' => '0',                // 返回格式：0：json格式（默认），1：xml，2：html，3：text
                    'order' => 'desc'             // 返回结果排序:desc降序（默认）,asc 升序
                );

                //请求参数
                $post_data = array();
                $post_data['customer'] = $customer;
                $post_data['param'] = json_encode($param, JSON_UNESCAPED_UNICODE);
                $sign = md5($post_data['param'].$key.$post_data['customer']);
                $post_data['sign'] = strtoupper($sign);

                $url = 'https://poll.kuaidi100.com/poll/query.do';    // 实时查询请求地址
                // 发送post请求
                $data = $this->curlpost($url,$post_data);

                if($data['message'] == 'ok'){
                    if(!empty($data['data'])){
                        #即刻更新库存信息
                        if($v['is_daifa']==1){
                            #代发仓库的商品不减库存
                            $goods_list = json_decode($v['content'],true);
                            update_goods_inventory($v['id'],$goods_list);
                        }

                        #更改订单状态
                        Db::name('website_order_list')->where(['id'=>$v['id']])->update(['status'=>2,'logistics_track'=>json_encode($data,true)]);
                        
                        #发送《拟发货清单》给仓库
                        if($v['status']==1){
                            $post = json_encode(['id'=>$v['id']]);
                            httpRequest('https://shop.gogo198.cn/collect_website/public/?s=api/func/send_delivery_to_email', $post,'',1);
                        }
                        
                        #通知买家
                        $post = json_encode([
                            'call'=>'confirmCollectionNotice',
                            'first' =>'订单状态变更',
                            'keyword1' => '订单状态变更',
                            'keyword2' => '已发货',
                            'keyword3' => date('Y-m-d H:i:s',time()),
                            'remark' => '点击查看详情',
                            'url' => 'https://www.gogo198.cn//cart/cart_detail?id='.$v['id'],
                            'openid' => $website_user['openid'],
                            'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                        ]);
                        httpRequest('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post,'',1);
                    }
                }
            }
        }
    }

    #实时查询物流轨迹接口（单个订单）
    public function syn_query_single(Request $request){
        $data = input();
        $order_id = intval($data['order_id']);

        //参数设置
        $key = 'PsCJSIbk321';                        // 客户授权key
        $customer = '38DACDC8BF738FB9C3F807039DBF4D61';                   // 查询公司编号

        //查询所有“已打印面单和已发货和已打包的物流订单信息”
        $order_list = Db::name('website_order_list')->whereRaw('(status=1 or status=2) and is_package=1 and id='.$order_id)->find();

//        $website_user = Db::name('website_user')->where(['id'=>$order_list['user_id']])->field('openid')->find();

        $order_list['express_info'] = json_decode($order_list['express_info'],true);
        $kuaidinum = '';
        $express_info = [];
        $express_code = '';
        if($order_list['is_daifa']==1){
            //直发
            $express_info = Db::name('centralize_express_product')->where(['id'=>$order_list['express_info']['kuaidi_company_id']])->find();
            $kuaidinum = $order_list['express_info']['kuaidinum'];
            $express_code = $express_info['code'];
        }
        elseif($order_list['is_daifa']==2){
            //代发
            $daifa_express_info = json_decode($order_list['daifa_express_info'],true);
            $express_info = Db::name('centralize_express_product')->where(['id'=>$daifa_express_info['express_id']])->find();
            $kuaidinum = $daifa_express_info['express_no'];
            $express_code = $express_info['code'];
        }

        if(!empty($kuaidinum) && !empty($express_code)){
            $param = array (
                'com' => $express_code,             // 快递公司编码
                'num' => $kuaidinum,     // 快递单号
                'phone' => '',                // 手机号
                'from' => '',                 // 出发地城市
                'to' => '',                   // 目的地城市
                'resultv2' => '1',            // 开启行政区域解析
                'show' => '0',                // 返回格式：0：json格式（默认），1：xml，2：html，3：text
                'order' => 'desc'             // 返回结果排序:desc降序（默认）,asc 升序
            );

            //请求参数
            $post_data = array();
            $post_data['customer'] = $customer;
            $post_data['param'] = json_encode($param, JSON_UNESCAPED_UNICODE);
            $sign = md5($post_data['param'].$key.$post_data['customer']);
            $post_data['sign'] = strtoupper($sign);

            $url = 'https://poll.kuaidi100.com/poll/query.do';    // 实时查询请求地址
            // 发送post请求
            $data = $this->curlpost($url,$post_data);

            if($data['message'] == 'ok'){
                if(!empty($data['data'])){
                    #更改订单状态
                    $res = Db::name('website_order_list')->where(['id'=>$order_list['id']])->update(['logistics_track'=>json_encode($data,true)]);
                    return $res;
                    #发送《拟发货清单》给仓库
//                    if($order_list['status']==1){
//                        $post = json_encode(['id'=>$order_list['id']]);
//                        httpRequest('https://shop.gogo198.cn/collect_website/public/?s=api/func/send_delivery_to_email', $post,'',1);
//                    }

                    #通知买家
//                    $post = json_encode([
//                        'call'=>'confirmCollectionNotice',
//                        'first' =>'订单状态变更',
//                        'keyword1' => '订单状态变更',
//                        'keyword2' => '已发货',
//                        'keyword3' => date('Y-m-d H:i:s',time()),
//                        'remark' => '点击查看详情',
//                        'url' => 'https://www.gogo198.cn//cart/cart_detail?id='.$order_list['id'],
//                        'openid' => $website_user['openid'],
//                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
//                    ]);
//                    httpRequest('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post,'',1);
                }
            }
        }
    }

    private function curlpost($url,$post_data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        $data = json_decode($result, true);

        return $data;
    }
    ##快递100接口========================================================================END
}