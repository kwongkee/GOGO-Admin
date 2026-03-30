<?php
/**
 * 商户管理模块定义
 *
 * @author Gorden
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

include_once __DIR__.'/inc/function.php';
include_once __DIR__.'/inc/model/mobile.php';
include_once __DIR__.'/inc/model/web.php';
include_once __DIR__.'/inc/model/Sendsms.php';


class Mer_chatsModuleSite extends WeModuleSite {

    public function __construct()
    {
        load()->func('tpl');
    }

    // 获取GPC,W的数据
    private function getWGPC($type='GPC') {
        global $_W, $_GPC;
        // 返回数组
        $red = ['W'=>$_W,'GPC'=>$_GPC];
        // 不存在数组中，返回空数组
        if(!isset($red[$type])) {
            return [];
        }
        // 返回对应数据
        return $red[$type];
    }

    private function showMsg($p) {
        echo '<pre>';
        print_r($p);die;
    }


    // 权限检测
    private function perssiom($G)
    {
        // 解析登陆用户信息
        $session = json_decode(base64_decode($G['__session']),true);
        if($session['sysTypes'] == 'system') {
            // 是原平台用户就直接返回
            return true;
            //message('您无权限操作，请联系管理员！');
        }

        // 获取权限
        $roleid = trim($session['roleid']);
        $role = pdo_get('merchat_role',['id'=>$roleid],['rolePermiss']);
        if(!empty($role) && ($role['rolePermiss'] == 'n')){
            //message('您无权限操作，请联系管理员！',referer(), 'info');
            message('您无权限操作，请联系管理员！');
        }

        // 通过
        return true;
    }



    /**
     * Web  PC端处理逻辑
     */

    // 首页
    public function doWebindex() {

        $G = $this->getWGPC();
        $W = $this->getWGPC('W');
        $uniacid = isset($G['__uniacid']) ? trim($G['__uniacid']) : '' ;
        $Arreid  = [3];
        if(!in_array($uniacid,$Arreid)) {
            message('您无权限操作，请联系管理员！',referer(), 'info');
        }

        // 检测用户是否有权限；
        $this->perssiom($G);

        $web = Web::getInstance();
        $role = isset($G['p'])   ? trim($G['p'])   : '' ;
        switch ($role) {

            case 'public':
                // 获取所分配给用户的公众号;
                $upublic = $web->getUpublic($G);
                // 获取所有公众号列表
                $publics = $web->getPublics();
                $uid = isset($G['uid']) ? trim($G['uid']) : '';
                $uname = isset($G['uname']) ? trim($G['uname']) : '';
                include $this->template('web/public');
                break;

            case 'editpublic': // 编辑分配公众号
                return $web->editPublic($G);
                break;

            case 'chrole': // 更新权限
                return $web->chRole($G);
                break;



            case 'puconfig': // 公众号接口配置
                //查询总条数
                $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('account_wechats'));
                $pageindex 	= intval($G['page'])?intval($G['page']):1;
                $pagesize 	= 8;
                // 分页
                $pager 		= pagination($total, $pageindex, $pagesize);
                $p = ($pageindex-1) * $pagesize;

                // 分页线显示数据
                $puconfig = pdo_fetchall("SELECT * FROM " . tablename('account_wechats') . " LIMIT " . $p . "," . $pagesize);
                include $this->template('web/puconfig');
                break;

            case 'addpu': // 新增接口
                $uniacid = trim($G['uniacid']);
                include $this->template('web/addpu');
                break;

            case 'addpus': // 新增接口，编辑操作
                if(!$W['isajax']) {
                    return json_encode(['code'=>0,'msg'=>'请求方法不正确，请检查!']);
                }
                if($G['method'] == 'add') {
                    return $web->addpus($G);
                } else if($G['method'] == 'edit') {
                    return $web->editpus($G);
                }
                break;

            case 'seepu': // 查看接口配置
                $plate = ['cross'=>'跨境购','direct'=>'直邮易'];
                $name = trim($G['names']);
                $interface = $web->getInterface($G);
                include $this->template('web/seepu');
                break;

            case 'editpus': // 编辑接口
                $inters = $web->getInters($G);
                include $this->template('web/editpus');
                break;

            case 'delpus': // 删除接口信息
                return $web->delPus($G);
                break;


            case 'toexas': // 审核

                // 获取权限列表
                $permiss = $web->getPermiss();

                // 查询数据，进行分页
                // 企业用户
                $typeCom = [
                    'company'=>'企业商户',
                    'personal'=>'个人商户'
                ];
                // 境内外商户
                $cusTypes = [
                    'Territory'=>'境内',
                    'Abroad'=>'境外'
                ];
                // 应用状态
                $appStatus= [0=>'待审核',1=>'审核通过',2=>'审核不通过',3=>'禁止使用'];

                //查询总条数
                $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('merchat_users'));
                $pageindex 	= intval($G['page'])?intval($G['page']):1;
                $pagesize 	= 5;
                $pager 		= pagination($total, $pageindex, $pagesize);
                $p = ($pageindex-1) * $pagesize;

                // 分页线显示数据
                $users = pdo_fetchall("SELECT * FROM " . tablename('merchat_users') . " ORDER BY `c_time` DESC LIMIT " . $p . "," . $pagesize);

                include $this->template('web/toexas');

                break;

            case 'adopt': // 通过，禁止用户
                return $web->adopt($G);
                break;


            case 'system':
                $sys = [
                    'rw'=>'可读可写',
                    'n'=>'禁读禁写',
                ];
                $system = $web->getRole();
                include $this->template('web/system');
                break;

            case 'addrole':
                include $this->template('web/addrole');
                break;

            case 'addroles': // 添加编辑操作
                if(!$W['isajax']){
                    return json_encode(['code'=>0,'msg'=>'请求方法不正确！']);
                }
                // 添加操作
                if($G['method'] == 'add') {
                    return $web->addRoles($G);
                } else if($G['method'] == 'edit') {
                    return $web->editRoles($G);
                }
                break;

            case 'editrole': // 编辑角色页面
                $id = trim($G['id']);
                $role = $web->getRoles($id);
                include $this->template('web/editrole');
                break;

            case 'delrole': // 删除角色
                return $web->delRoles($G);
                break;


                // 消息通知
            case 'notice':
                //查询总条数
                $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('merchat_selectdata'));
                $pageindex 	= intval($G['page'])?intval($G['page']):1;
                $pagesize 	= 3;
                $pager 		= pagination($total, $pageindex, $pagesize);
                $p = ($pageindex-1) * $pagesize;

                // 分页线显示数据
                $data = pdo_fetchall("SELECT * FROM " . tablename('merchat_selectdata') . " ORDER BY `c_time` DESC LIMIT " . $p . "," . $pagesize);

                include $this->template('web/notice');

                break;

            case 'selectnoti':
                // 获取公众号列表
                $publist = $web->getPublics();
                include $this->template('web/selectnoti');
                break;

            case 'search': // 搜索数据
                return $web->search($G);
                break;

            case 'selectdata': // 选择通知人
                return $web->selectData($G);
                break;

            case 'selectdel': // 移除通知人
                return $web->selectDel($G);
                break;


            case 'merpublic':
                //查询总条数
                $total = pdo_fetch("SELECT COUNT(*) as count FROM ".tablename('merchat_mall')." WHERE typeShop = :typeShop", array(':typeShop' => 'public'));
                $pageindex 	= intval($G['page'])?intval($G['page']):1;
                $pagesize 	= 8;
                // 分页
                $pager 		= pagination($total['count'], $pageindex, $pagesize);
                $p = ($pageindex-1) * $pagesize;

                // 分页线显示数据
                $puconfig = pdo_fetchall("SELECT * FROM " . tablename('merchat_mall') . "WHERE typeShop='public' LIMIT " . $p . "," . $pagesize);

                include $this->template('web/merpublic');
                break;
            case 'editshopurl': // 设置网上商铺地址
                return $web->shopUrl($G);
                break;

            case 'physhop':  // 实体店铺列表
                // 获取商户信息
                //查询总条数
                $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('merchat_users'));
                $pageindex 	= intval($G['page'])?intval($G['page']):1;
                $pagesize 	= 8;
                // 分页
                $pager 		= pagination($total, $pageindex, $pagesize);
                $p = ($pageindex-1) * $pagesize;

                // 分页线显示数据
                $customs = pdo_fetchall("SELECT * FROM " . tablename('merchat_users') . " LIMIT " . $p . "," . $pagesize);

                $typeCom = ['company'=>'企业商户','personal'=>'个人商户'];
                $cusTypes = ['Territory'=>'境内','Abroad'=>'境外'];

                include $this->template('web/physhop');
                break;

            case 'showShop':
                $shops = $web->getShops($G);
                include $this->template('web/showshop');
                break;


            default:
                include $this->template('web/index');

                break;
        }

    }

//http://shop.gogo198.cn/web/index.php?c=site&a=entry&m=ewei_shopv2&do=web&r=member.query&keyword=jason

    // 访问地址：http://shop.gogo198.cn/web/index.php?c=site&a=entry&op=display&do=check&m=mer_chats


    /**
     * Mobile  移动端处理逻辑
     * 入口
     */
    public function doMobileIndex() {
        // 获取数据
        $G = $this->getWGPC();
        $W = $this->getWGPC('W');
        // 移动端模型处理
        $mobile = Mobile::getInstance();
        // 登陆检测 取消检测
        $this->checkLogin();

        $uid = $_SESSION['uid'];

        switch ($G['p']) {

            case 'test': // 测试用
                $ip = $_SERVER['HTTP_X_REAL_IP'];
                $res = $this->GetIpLookup($ip);
                echo '您当前所在的位置是：'.$res['country'].'-'.$res['province'].'-'.$res['city'];
                break;

            case 'index': // 登陆成功进入首页

                $title = '商户配置';
                // 获取用户数据
                //$users = $mobile->getMerchatUsers($uid);
                include $this->template('index');

                break;

            case 'merinfo':

                $title = '商家信息';
                // 获取数据
                $info = $mobile->getMerchatUsers($uid);
                // 商户类型
                $Com = ['company'=>'企业','personal'=>'个人'];
                // 境内外商户
                $Types = ['Territory'=>'境内商户','Abroad'=>'境外商户'];
                // 获取
                $coms = isset($Com[$info['typeCom']])    ? $Com[$info['typeCom']]:'';
                $Type = isset($Types[$info['cusTypes']]) ? $Types[$info['cusTypes']]:'';
                // 用户类型，境内外
                $ctypes = $coms.$Type;
                // 国家省份城市区域
                //$areas = $info['country'].$info['province'].$info['city'].$info['area'];

                include $this->template('merinfo');

                break;


            case 'storeConfig': // 店铺配置
                $title = '店铺配置';
                include $this->template('storeConfig');
                break;

            case 'physicalShop': // 实体店铺
                $shop = $mobile->getShop($uid);
                $title = '实体店铺';
                include $this->template('physicalShop');
                break;

            case 'addphysicalShop':// 添加实体店铺

                $area = $this->getArea();
                $title = '添加实体店铺';
                include $this->template('addphysicalShop');
                break;

            case 'editphysicalShop':// 编辑实体店铺
                $id = isset($G['id']) ? trim($G['id']) : null;
                if(!$id) {
                    return $this->error('数据不存在');
                }
                $area = $this->getArea();

                $shop = $mobile->getShops($id);
                $title = '编辑实体店铺';
                include $this->template('editphysicalShop');
                break;

            case 'addShop': // 添加实体商铺操作

                if(!$W['isajax']) {
                    return json_encode(['code'=>0,'msg'=>'请求方法不正确']);
                }
                if($G['method'] == 'add') {
                    // 返回操作数据
                    return $mobile->addShop($G,$uid);
                } else if($G['method'] == 'edit') {
                    return $mobile->editShop($G);
                }
                break;

            case 'delphysicalShop': // 删除店铺操作

                if(!$W['isajax']) {
                    return json_encode(['code'=>0,'msg'=>'请求方法不正确']);
                }
                return $mobile->delShop($G);
                break;

            case 'publicAccounts': // 公众账户
                // 获取公众号列表
                $public = $mobile->getPublic($uid);
                $title = '公众账号列表';
                include $this->template('publicAccounts');
                break;

            case 'addpublic': // 添加公众号
                $title = '添加公众号';
                include $this->template('addpublic');
                break;
            case 'editpublic': // 添加公众号
                $id = trim($G['id']);
                $editp = $mobile->getShops($id);
                $title = '编辑公众号';
                include $this->template('editpublic');
                break;

            case 'addPublics': // 添加，修改操作
                if(!$W['isajax']) {
                    return json_encode(['code'=>0,'msg'=>'请求方法不正确']);
                }
                if($G['method'] == 'add') {
                    // 返回操作数据
                    return $mobile->addPublics($G,$uid);
                } else if($G['method'] == 'edit') {
                    return $mobile->editPublics($G);
                }
                break;


                // 电商渠道配置  2020-03-04
            /*case 'busiChannel':
                $title = '电商渠道配置';
                include $this->template('busichannel/index');
                break;

            case 'busiChanneladd':
                $title = '添加电商渠道';
                include  $this->template('busichannel/add');
                break;

            case 'busiChanneladds': // 添加，修改操作
                if(!$W['isajax']) {
                    return json_encode(['code'=>0,'msg'=>'请求方法不正确']);
                }
                if($G['method'] == 'add') {
                    // 返回操作数据
                    //return $mobile->addPublics($G,$uid);
                } else if($G['method'] == 'edit') {
                    //return $mobile->editPublics($G);
                }
                break;*/


            case 'mallStore': // 商城店铺
                break;

            case 'userConfig': // 用户配置

                $title = '用户配置';
                include $this->template('userConfig');
                break;

            case 'roleconfig': // 角色配置
                $role  = $mobile->getRole($uid);
                $title = '角色列表';
                include $this->template('roleconfig');
                break;

            case 'roleadd': // 添加角色
                $title = '添加角色';
                include $this->template('roleadd');
                break;

            case 'editrole': // 编辑角色
                $role  = $mobile->getRoleone($G);
                $title = '编辑角色';
                include $this->template('editrole');
                break;

            case 'roledoadd': // 角色添加操作

                if(!$W['isajax']) {
                    return json_encode(['code'=>0,'msg'=>'请求方法不正确']);
                }
                if($G['method'] == 'add') {
                    // 返回操作数据
                    return $mobile->addRole($G,$uid);
                } else if($G['method'] == 'edit') {
                    return $mobile->editRole($G);
                }
                break;

            case 'delrole': // 删除角色
                if(!$W['isajax']) {
                    return json_encode(['code'=>0,'msg'=>'请求方法不正确']);
                }
                return $mobile->delRole($G);
                break;


            case 'usersconf': // 用户配置
                // 获取我的用户；ims_merchat_operator
                $oper = $mobile->getOpertor($uid);
                $title = '角色列表';
                include $this->template('usersconf');
                break;

            case 'addusers':    // 添加用户
                // 查询角色列表；
                $role  = $mobile->getRole($uid);
                $title = '添加用户';
                include $this->template('usersadd');
                break;

            case 'editusers':
                // 获取角色资料
                $role  = $mobile->getRole($uid);
                // 获取用户资料
                $id = trim($G['id']);
                $info = $mobile->getOpertors($id);
                $title = '编辑用户';
                include $this->template('editusers');
                break;

            case 'adddousers':
                $method = isset($G['methods']) ? trim($G['methods']) : '';
                if(!$W['isajax']) {
                    return json_encode(['code'=>0,'msg'=>'请求方法不正确']);
                }

                // 修改从这里返回s
                $yzm = isset($_SESSION['yzm']) ? trim($_SESSION['yzm']) : '';
                switch ($method) {
                    case 'add':
                        return $mobile->addUsers($G,$uid,$yzm);
                        break;

                    case 'edit':
                        return $mobile->editUsers($G);
                        break;

                    default:
                        return json_encode(['code'=>0,'msg'=>'请求方法不正确']);
                        break;
                }
                break;

            case 'delusers': // 删除用户
                return $mobile->delUsers($G);
                break;

            default :
                // 先检测用户是否关注购购账号；
                /*$this->isFollow();
                // 关注后显示页面
                include $this->template('register');*/
                // 登陆检测
                $this->checkLogin();
                // 获取用户数据
                $users = $mobile->getMerchatUsers($_SESSION['uid']);
                //
                $name = $users['adminName'];
                include $this->template('index');
                break;
        }
    }


    // 测试用
    //https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=test&m=mer_chats&wxref=mp.weixin.qq.com#wechat_redirect
    public function doMobileTest(){
        // tiaoz
        include $this->template('test/index');
    }

    public function doMobiletlogin(){

        global  $_W,$_GPC;


        echo '<pre>';
        print_r($_W);
        print_r($_GPC);


    }



    // 用户登陆
    public function doMobileLogins() {
        // 检测用户是否登陆
        if($_SESSION['isLogin']) {

            $url = url('entry',array('do'=>'index','p'=>'index','m'=>'mer_chats'));
            $htmls = <<<HTMS
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>温馨提示</title>
</head>
<body>
    <script>
        alert('您已登陆，请勿重复操作');
        setTimeout(function(){
            window.location.href = "{$url}"
        },1500)
    </script>
</body>
</html>
HTMS;
            exit($htmls);
        }

        $title = '商户登陆';
        include $this->template('login');
    }

    // 用户注册
    public function doMobileRegister() {
        $title = '商户注册';
        // 先检测用户是否关注购购账号；
        $this->isFollow();

        // 获取城市
        $area = $this->getArea();

        // 关注后显示页面
        include $this->template('register');
    }

    // 获取城市信息
    public function doMobileCity() {
        $gpc = $this->getWGPC();
        $pid = isset($gpc['pid']) ? trim($gpc['pid']) : false;
        if(!$pid) {
            return json_encode(['code'=>0,'msg'=>'数据获取失败']);
        }
        $data = $this->getArea($pid);
        return json_encode(['code'=>1,'msg'=>'success','data'=>$data]);
    }



    // 获取城市信息
    private function getArea($pid=0) {
        $data = pdo_getall('customs_addr_area',['area_parent_id'=>$pid],['area_id','area_name']);
        return $data;
    }


    // 企业商户注册操作
    public function doMobileReg() {
        $W = $this->getWGPC('W');
        $G = $this->getWGPC();
        $mobile = Mobile::getInstance();
        if(!$W['isajax']) {
            return json_encode(['code'=>0,'msg'=>'请求方法不正确！']);
        }
        $yzms = $_SESSION['yzms'];
        return $mobile->checkRegister($G,$yzms);
    }

    // 个人商户注册操作
    public function doMobileRegPersonal() {
        $W = $this->getWGPC('W');
        $G = $this->getWGPC();
        $mobile = Mobile::getInstance();
        if(!$W['isajax']) {
            return json_encode(['code'=>0,'msg'=>'请求方法不正确！']);
        }
        // 系统验证码
        //$yzms = $G['yzm'];
        $yzms = $_SESSION['yzms'];
        // 处理程序
        return $mobile->checkRegisters($G,$yzms);
    }



    //  ==========================以下工具区===========================

    // 根据IP地址获取用户地理位置
    private function GetIpLookup($ip) {

        $res = $this->getUrl($ip);

        if(empty($res)) {
            return false;
        }

        $json = json_decode($res,true);

        return [
            'country'=>$json['data']['country'],
            'province'=>$json['data']['region'],
            'city'=>$json['data']['city']
        ];

    }

    // 用户登陆检测
    private function checkLogin() {
        if(!$_SESSION['isLogin']) {

            $url = url('entry',array('do'=>'logins','m'=>'mer_chats'));
            $htmls = <<<HTMS
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>温馨提示</title>
</head>
<body>
    <script>
        alert('请先登陆');
        setTimeout(function(){
            window.location.href = "{$url}"
        },1500)
    </script>
</body>
</html>
HTMS;
            exit($htmls);
        }
    }

    // 发送验证码
    public function doMobileYzm() {
        $W = $this->getWGPC('W');
        $G = $this->getWGPC();
        // 是否Ajax
        if($W['isajax']) {

            // 验证码
//            $yzm = random(6);
            $yzm = mt_rand(0,9).mt_rand(0,9).mt_rand(0,9).mt_rand(0,9);
            $yzm = strtoupper($yzm);
            // 正则验证，手机号，邮箱
            if($G['type'] == 'phone') {
                if(!CheckPhone($G['val'])) {
                    return json_encode(['code'=>0,'msg'=>'手机号码有误，请重新输入']);
                }
                // 发送手机验证码；
                 $sms = Sendsms::getInstance();
                 // 手机号码
                 $mobile = trim($G['val']);
                 // 验证码
                 $Sm = $sms->Send($mobile,$yzm);
                 if($Sm['result']['success'] != 'true') {
                     return json_encode(['code'=>0,'msg'=>'发送失败，请重新获取','data'=>$Sm]);
                 }

            } else if($G['type'] == 'email') {

                if(!checkEmail($G['val'])) {
                    return json_encode(['code'=>0,'msg'=>'电子邮箱不正确，请重新输入']);
                }
                // 手机号码
                $email = trim($G['val']);
                // 发送者
                $name    = '系统管理员';
                //
                $subject = '商户注册验证码';
                $content = "你好,您的验证码为：{$yzm}</a>";
                // 发送邮箱  /inc/function.php
                $status  = send_mail($email,$name,$subject,$content);
                if(!$status) {
                    return json_encode(['code'=>0,'msg'=>'发送失败，请重新获取','data'=>$status]);
                }
            }

            // 保存验证码
            $_SESSION['yzms'] = $yzm;
            // 发送邮箱验证码
            return json_encode(['code'=>1,'msg'=>'success','data'=>$status]);
        }

        return json_encode(['code'=>0,'msg'=>'请求失败']);
    }


    // 登陆获取验证码
    public function doMobileLoginyzm() {
        $W = $this->getWGPC('W');
        $G = $this->getWGPC();
        // 移动端模型处理
        $mobile = Mobile::getInstance();
        // 是否Ajax
        if($W['isajax']) {

            // 验证码
            //$yzm = random(6);
            $yzm = mt_rand(0,9).mt_rand(0,9).mt_rand(0,9).mt_rand(0,9);
            $yzm = strtoupper($yzm);
            // 正则验证，手机号，邮箱
            if($G['type'] == 'phone') {

                if(!CheckPhone($G['val'])) {
                    return json_encode(['code'=>0,'msg'=>'手机号码有误，请重新输入']);
                }

                // 检测用户是否可用
                $check = $mobile->checkOpenUser($G['type'],$G['val']);
                if(empty($check)) {
                    return json_encode(['code'=>0,'msg'=>'请耐心等待审核通过后再登陆，请重新输入']);
                }

                // 发送手机验证码；
                $sms = Sendsms::getInstance();
                // 手机号码
                $mobile = trim($G['val']);
                // 验证码
                $Sm = $sms->Send($mobile,$yzm);
                if(!$Sm['result']['success']) {
                    return json_encode(['code'=>0,'msg'=>'发送失败，请重新获取','data'=>$Sm]);
                }

            } else if($G['type'] == 'email') {

                if(!checkEmail($G['val'])) {
                    return json_encode(['code'=>0,'msg'=>'电子邮箱不正确，请重新输入']);
                }

                // 检测用户是否可用
                $check = $mobile->checkOpenUser($G['type'],$G['val']);
                if(empty($check)) {
                    return json_encode(['code'=>0,'msg'=>'请耐心等待审核通过后再登陆，请重新输入']);
                }

                // 手机号码
                $email = trim($G['val']);
                // 发送者
                $name    = '系统管理员';
                //
                $subject = '商户注册验证码';
                $content = "你好,您的验证码为：{$yzm}</a>";
                // 发送邮箱
                $status  = send_mail($email,$name,$subject,$content);
                if(!$status) {
                    return json_encode(['code'=>0,'msg'=>'发送失败，请重新获取','data'=>$status]);
                }
            }
            // 保存验证码
            $_SESSION['LoginYzm'] = $yzm;
            // 发送邮箱验证码
            return json_encode(['code'=>1,'msg'=>'验证码发送成功','data'=>$status]);
        }

        return json_encode(['code'=>0,'msg'=>'获取失败']);
    }

    // 用户登陆
    public function doMobileLogin() {
        $G = $this->getWGPC();
        $yzm = strtoupper(trim($G['yzm']));
        $phone = isset($G['phone'])?trim($G['phone']):'';
        $email = isset($G['email'])?trim($G['email']):'';

        // 验证码是否正确 获取验证码
        if($_SESSION['LoginYzm'] != $yzm) {
            return json_encode(['code'=>0,'msg'=>'验证码不正确，请检查！']);
        }

        if(!empty($phone)) {
            $pm = pdo_get('merchat_users',['phone'=>$phone,'appStatus'=>1],['uid']);
            if(empty($pm)) {
                return json_encode(['code'=>0,'msg'=>'登陆失败，请检查手机号码！']);
            }
        }

        if(!empty($email)) {
            $pm = pdo_get('merchat_users',['email'=>$email,'appStatus'=>1],['uid']);
            if(empty($pm)) {
                return json_encode(['code'=>0,'msg'=>'登陆失败，请检查电子邮箱！']);
            }
        }

        $_SESSION['uid']     = $pm['uid'];
        $_SESSION['isLogin'] = true;

        return json_encode(['code'=>1,'msg'=>'登陆成功！']);
    }

    // 文件上传
    public function doMobileUpload() {
        $res = ImgUpload($_FILES['file']);
        return $res;
    }
    // 判断用户是否关注，没关注提示需要关注；
    public function isFollow() {
        global  $_W;
        // 是否关注
        if(isset($_W['openid']) && ($_W['fans']['follow'] != 1)) {
            $html = <<<HTMLS
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Gogo購購網</title>
    <style>
    body{
        margin-top: 30%;
        width: 100%;
        text-align: center;
    }
    #ps{
        color: green;
        padding: 10px;
        font-size: 20px;
        font-weight: bold;
    }
</style>
</head>
<body>
    <h1 id="ps">请长按二维码关注</h1>
    <img id="imgs" src="../addons/mer_chats/static/images/copy.jpg" alt="Gogo公众号" width="300px" height="300px">
</body>
</html>
HTMLS;
            exit($html);
        }
    }

    private function getUrl($ip){
        $url = 'http://ip.taobao.com/service/getIpInfo.php?ip='.$ip;
        //初始化
        $curl = curl_init();
        //设置捉取URL
        curl_setopt($curl,CURLOPT_URL,$url);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        //执行命令
        $res = curl_exec($curl);
        //关闭Curl请求
        curl_close($curl);
        //print_r($res);
        return $res;
    }

    // 是否微信客户端
    public function isWx() {
        // 判断是否微信客户端  显示数据
        if(!is_weixin()) {
            $buttondisplay = 1;
            $msg = [
                'message'=>'请使用微信客户端打开',
                'title'  =>'温馨提示',
                'buttontext'=>'请使用微信客户端打开',
            ];
            $this->message($msg,1,'error');
        }
    }

    // 消息提示
    public function message($msg, $redirect = '', $type = '')
    {
        global $_W;
        $title      = '';
        $buttontext = '';
        $message    = $msg;

        if (is_array($msg)) {
            $message    = ((isset($msg['message'])      ? $msg['message'] : ''));
            $title      = ((isset($msg['title'])        ? $msg['title'] : ''));
            $buttontext = ((isset($msg['buttontext'])   ? $msg['buttontext'] : ''));
        }

        if (empty($redirect)) {

            $redirect = 'javascript:history.back(-1);';

        } else if ($redirect == 'close') {

            $redirect = 'javascript:WeixinJSBridge.call("closeWindow")';
        }

        include $this->template('_message');
        exit();
    }
}

?>