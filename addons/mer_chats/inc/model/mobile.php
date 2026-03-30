<?php


// 单利模式  移动端模型
class Mobile {

    // 静态私有方法
    private static $instance;

    // 私有化
    private function __construct() {}

    // 防止实例被克隆
    private function __clone(){}

    // 防止反序列号
    private function __wakeup(){}
    // 单利模式
    public static function getInstance() {

        if(self::$instance === null) {
            self::$instance = new Static();
        }
        return self::$instance;
    }

    public function test() {

        $res = pdo_get('merchat_users',['uid'=>267],['email']);

        return $res;
    }

    //********************* 添加用户操作 ******************************

    // 获取全部用户
    public function getOpertor($uid){
        $data = pdo_getall('merchat_operator',['manage'=>$uid]);
        return $data;
    }

    // 获取单个用户
    public function getOpertors($uid){
        $data = pdo_get('merchat_operator',['uid'=>$uid]);
        return $data;
    }

    // 编辑用户
    public function editUsers($G) {

        $id = isset($G['uid']) ? trim($G['uid']) : '';

        // 如果新用户名与旧用户名不相同则判断是否存在；
        if($G['userName'] != $G['oldName']) {
            $res = $this->checkName($G['userName']);
            if(!empty($res)) {
                return json_encode(['code'=>0,'msg'=>'该用户名已注册，请重新输入！']);
            }

            pdo_update('users',['username'=>trim($G['userName'])],['uid'=>$id]);
        }

        $upData = [
            'roleId'    =>  isset($G['roleId'])     ? trim($G['roleId'])    : '',
            'cusTypes'  =>  isset($G['cusTypes'])   ? trim($G['cusTypes'])  : '',
            'userName'  =>  isset($G['userName'])   ? trim($G['userName'])  : '',
        ];

        // 更新数据
        $up = pdo_update('merchat_operator',$upData,['uid'=>$id]);
        if(!$up) {
            return json_encode(['code'=>0,'msg'=>'编辑失败！']);
        }
        return json_encode(['code'=>1,'msg'=>'编辑成功！']);
    }

    // 添加用户
    public function addUsers($G,$uid,$yzm) {
        // 查看用户名是否已注册
        // 写入users 表中
        // 写入merchat_operator
        $res = $this->checkName($G['userName']);
        if(!empty($res)) {
            return json_encode(['code'=>0,'msg'=>'该用户名已注册，请重新输入！']);
        }

        // 验证验证码
        $code = isset($G['code']) ? trim($G['code']) : '';
        if(empty($code)) {
            return json_encode(['code'=>0,'msg'=>'请输入验证码！']);
        }

        // 验证验证码是否正确
        if($yzm != $code) {
            return json_encode(['code'=>0,'msg'=>'验证码不正确，请检查！']);
        }

        // 密钥
        $salt = random(8);
        // 用户表
        $users = [
            'groupid'   =>  5,
            'username'  =>  isset($G['userName']) ? trim($G['userName']) : '',
            'password'  =>  $this->user_hash('123456',$salt),
            'salt'      =>  $salt,
            'type'      =>  0,
            'status'    =>  2,
            'joindate'  =>  time(),
            'joinip'    =>  $_SERVER['HTTP_X_REAL_IP'],
            'lastip'    =>  $_SERVER['HTTP_X_REAL_IP'],
            'lastvisit' =>  time(),
            'starttime' =>  time(),
            'endtime'   =>  0,
            'superior'  =>  $uid,// 上级；0：管理员，1操作员，2操作员，3操作员
            'roleid'    =>  isset($G['roleId']) ? trim($G['roleId']) : '',//角色ID
            'sysTypes'  =>  'user',//管理所属
        ];
        // 记录商户信息表
        $result  = pdo_insert('users',$users);
        if(!empty($result)) {
            // 写入成功的uid
            $iuid = pdo_insertid();

            $opertor = [
                'manage'    =>  $uid,   // 所属管理UID
                'uid'       =>  $iuid,  // 用户uid
                'userName'  =>  isset($G['userName']) ? trim($G['userName']) : '',// 用户名
                'roleId'    =>  isset($G['roleId']) ? trim($G['roleId']) : '', // 角色ID
                'cusTypes'  =>  isset($G['cusTypes']) ? trim($G['cusTypes']) : '', // 境内：Territory 境外：Abroad
                'phone'     =>  isset($G['phone']) ? trim($G['phone']) : '',    // 用户手机号码
                'email'     =>  isset($G['email']) ? trim($G['email']) : '',    // 用户电子邮箱
                'c_time'    =>  time(),
                'u_time'    =>  0,
            ];
            // 添加用户
            $oper = pdo_insert('merchat_operator',$opertor);
            if(!$oper) {
                return json_encode(['code'=>0,'msg'=>'用户添加失败！']);
            }


            /**
             *  查询用户管理员权限，分配用户对应权限！
             */
            // 查询ims_uni_account_users
            $unis = pdo_getall('uni_account_users',['uid'=>$uid]);
            // 写入分配公众号
            if(!empty($unis) && is_array($unis)) {
                foreach($unis as $k=>$v) {
                    $tmp = [
                        'uniacid'=>$v['uniacid'],
                        'uid'    =>$iuid,
                        'role'=>$v['role'],
                        'rank'=>$v['rank'],
                    ];
                    pdo_insert('uni_account_users',$tmp);
                }
            }

            return json_encode(['code'=>1,'msg'=>'用户添加成功！']);

        } else {
            return json_encode(['code'=>0,'msg'=>'用户添加失败！']);
        }
    }

    // 删除用户
    public function delUsers($G) {
        if(empty($G['myCheck'])) {
            return json_encode(['code'=>0,'msg'=>'请选择要操作的数据']);
        }
        // 获取删除的用户
        $info = implode(',',$G['myCheck']);
        // 检查用户信息是否存在，存在就删除，否则不操作删除
        $usql = "SELECT * FROM ".tablename('users')." WHERE uid IN(:uid)";
        $users = pdo_query($usql, array(':uid' => $info));
        if(!empty($users)) {
            // 删除后台用户信息
            $sq = "DELETE FROM ".tablename('users')." WHERE uid IN(:uid)";
            pdo_query($sq, array(':uid' => $info));
        }

        $datas2 = false;
        $usql1 = "SELECT * FROM ".tablename('merchat_operator')." WHERE uid IN(:uid)";
        $users2 = pdo_query($usql1, array(':uid' => $info));
        if(!empty($users2)) {
            // 删除后台用户信息
            $sq2 = "DELETE FROM ".tablename('merchat_operator')." WHERE uid IN(:uid)";
            $datas2 = pdo_query($sq2, array(':uid' => $info));
        }

        if(!$datas2) {
            return json_encode(['code'=>0,'msg'=>'删除失败']);
        }
        // 删除成功返回
        return json_encode(['code'=>1,'msg'=>'删除成功']);
    }

    //********************* 添加用户操作  END ******************************




    //********************* 添加角色操作  START ******************************
    // 添加角色
    public function addRole($G,$uid){
        $roleName = isset($G['roleName']) ? trim($G['roleName']) : '';
        if(empty($roleName)) {
            return json_encode(['code'=>0,'msg'=>'请填写角色名称！']);
        }
        $data = [
            'roleName'   => $roleName,
            'rolePermiss'=> isset($G['rolePermiss']) ? trim($G['rolePermiss']) : '',
            'uid'        => $uid,
            'sysType'    => 'user',
            'time'       => time(),
        ];
        $ins = pdo_insert('merchat_role',$data);
        if(!$ins) {
            return json_encode(['code'=>0,'msg'=>'添加失败']);
        }
        return json_encode(['code'=>1,'msg'=>'添加成功']);
    }

    // 删除角色操作
    public function delRole($G){
        if(empty($G['myCheck'])) {
            return json_encode(['code'=>0,'msg'=>'请选择要操作的数据']);
        }
        $info = implode(',',$G['myCheck']);
        // 删除操作语句
        $sq = "DELETE FROM ".tablename('merchat_role')." WHERE id IN(:ids)";
        $datas = pdo_query($sq, array(':ids' => $info));
        if(!$datas) {
            return json_encode(['code'=>0,'msg'=>'删除失败']);
        }
        // 删除成功返回
        return json_encode(['code'=>1,'msg'=>'删除成功']);
    }

    // 编辑角色
    public function editRole($G) {
        $id = isset($G['id']) ? trim($G['id']) : '';
        $uid = isset($G['uid']) ? trim($G['uid']) : '';
        // 旧名称
        $oldName = isset($G['oldName']) ? trim($G['oldName']) : '';
        // 新名称
        $roleName = isset($G['roleName']) ? trim($G['roleName']) : '';

        $data = [
            'rolePermiss'=>isset($G['rolePermiss']) ? trim($G['rolePermiss']) : '',
        ];
        if($oldName != $roleName) {
            $data = [
                'roleName'   =>isset($G['roleName'])    ? trim($G['roleName']) : '',
                'rolePermiss'=>isset($G['rolePermiss']) ? trim($G['rolePermiss']) : '',
            ];

            // 判断角色名称是否存在
            $roleN = pdo_get('merchat_role',['uid'=>$uid,'roleName'=>$roleName]);
            if(!empty($roleN)) {
                return json_encode(['code'=>0,'msg'=>'该角色名称已存在']);
            }
        }

        $up = pdo_update('merchat_role',$data,['id'=>$id]);
        if(empty($up)) {
            return json_encode(['code'=>0,'msg'=>'编辑失败']);
        }
        return json_encode(['code'=>1,'msg'=>'编辑成功']);
    }

    // 获取用户角色列表
    public function getRole($uid) {
        $role = pdo_getall('merchat_role',['uid'=>$uid]);
        return $role;
    }

    // 获取用户角色列表
    public function getRoleone($G) {
        $id = isset($G['id']) ? trim($G['id']) : '';
        $role = pdo_get('merchat_role',['id'=>$id]);
        return $role;
    }
    //********************* 添加角色操作  END ******************************




    //********************* 添加公众号操作  START ******************************

    // 公众号号列表
    public function getPublic($uid) {
        $data = [];
        $data = pdo_getall('merchat_mall',['uid'=>$uid,'typeShop'=>'public']);
        return $data;
    }

    // 添加操作公众号操作
    public function addPublics($G,$uid) {

        // 检测是否已存在
        $where = ['uid'=>$uid,'public'=>$G['public']];
        $getp = pdo_get('merchat_mall',$where);

        if(empty($G['public']) || !empty($getp)) {
            return json_encode(['code'=>0,'msg'=>'该公众号已添加，请勿重复添加！']);
        }

        $data = [
            'uid'=>$uid,
            'public'=>isset($G['public']) ? trim($G['public']) : '',
            'account'=>isset($G['account']) ? trim($G['account']) : '',
            'original'=>isset($G['original']) ? trim($G['original']) : '',
            'AppId'=>isset($G['public']) ? trim($G['AppId']) : '',
            'AppSecret'=>isset($G['public']) ? trim($G['AppSecret']) : '',
            'c_time'=>time(),
            'typeShop'=>'public',
        ];
        $ins = pdo_insert('merchat_mall',$data);
        if(empty($ins)) {
            return json_encode(['code'=>0,'msg'=>'添加失败']);
        }
        return json_encode(['code'=>1,'msg'=>'添加成功']);
    }

    // 编辑公众号操作
    public function editPublics($G){
        $id = trim($G['id']);
        $data = [
            //'public'=>isset($G['public']) ? trim($G['public']) : '',
            'account'=>isset($G['account']) ? trim($G['account']) : '',
            'original'=>isset($G['original']) ? trim($G['original']) : '',
            'AppId'=>isset($G['public']) ? trim($G['AppId']) : '',
            'AppSecret'=>isset($G['public']) ? trim($G['AppSecret']) : '',
            'u_time'=>time(),
            'typeShop'=>'public',
        ];
        $ins = pdo_update('merchat_mall',$data,array('id' => $id));
        if (!empty($ins)) {
            return json_encode(['code'=>1,'msg'=>'编辑成功']);
        }
        return json_encode(['code'=>0,'msg'=>'编辑失败']);
    }

    //********************* 添加公众号操作  END ******************************




    //********************* 添加实体店铺操作  START ******************************

    // 获取实体店铺列表
    public function getShop($uid,$typeShop='physiCal') {
        $res = [];
        $data = pdo_getall('merchat_mall',['uid'=>$uid,'typeShop'=>$typeShop]);
        if(empty($data)) {
            return $res;
        }
        return $data;
    }

    // 获取实体店铺具体数据
    public function getShops($id) {
        $res = [];
        $data = pdo_get('merchat_mall',['id'=>$id]);
        if(empty($data)) {
            return $res;
        }
        return $data;
    }

    // 添加店铺
    public function addShop($G,$uid) {

        if(empty($G['shopName'])) {
            return json_encode(['code'=>0,'msg'=>'请输入店铺名称']);
        }
        if(empty($G['address'])) {
            return json_encode(['code'=>0,'msg'=>'请输入详细地址']);
        }

        // 查看商铺是否已经存在
        $shopName = trim($G['shopName']);
        $shop = pdo_get('merchat_mall',['shopName'=>$shopName],['id']);
        if(!empty($shop)) {
            return json_encode(['code'=>0,'msg'=>'该店铺名称已存在，请重新输入']);
        }

        $data = [
            'uid'       =>$uid,
            'shopName'  =>isset($G['shopName']) ?   $G['shopName']  :'',
            'country'   =>isset($G['country'])  ?   $G['country']   :'',
            'province'  =>isset($G['province']) ?   $G['province']  :'',
            'city'      =>isset($G['city'])     ?   $G['city']      :'',
            'area'      =>isset($G['area'])     ?   $G['area']      :'',
            'addr'      =>isset($G['addr'])     ?   $G['addr']      :'',
            'address'   =>isset($G['address'])  ?   $G['address']   :'',
            'public'    =>isset($G['public'])   ?   $G['public']    :'',
            'account'   =>isset($G['account'])  ?   $G['account']   :'',
            'original'  =>isset($G['original']) ?   $G['original']  :'',
            'AppId'     =>isset($G['AppId'])    ?   $G['AppId']     :'',
            'AppSecret' =>isset($G['AppSecret'])?   $G['AppSecret'] :'',
            'shopUrl'   =>isset($G['shopUrl'])  ?   $G['shopUrl']   :'',
            'typeShop'  =>isset($G['typeShop']) ?   $G['typeShop']  :'',
            'c_time'    =>time(),
            'u_time'    =>0,
        ];
        $ins = pdo_insert('merchat_mall',$data);
        if(empty($ins)) {
            return json_encode(['code'=>0,'msg'=>'添加失败']);
        }
        return json_encode(['code'=>1,'msg'=>'添加成功']);
    }

    // 编辑实体店铺
    public function editShop($G) {

        if(empty($G['shopName'])) {
            return json_encode(['code'=>0,'msg'=>'请输入店铺名称']);
        }

        if(empty($G['address'])) {
            return json_encode(['code'=>0,'msg'=>'请输入详细地址']);
        }

        $id = trim($G['id']);
        $data = array (
            'shopName'  =>  isset($G['shopName']) ?   $G['shopName']  :'',
            'country'   =>  isset($G['Country'])  ?   $G['Country']   :'',
            'province'  =>  isset($G['province']) ?   $G['province']  :'',
            'city'      =>  isset($G['city'])     ?   $G['city']      :'',
            'area'      =>  isset($G['area'])     ?   $G['area']      :'',
            'addr'      =>  isset($G['addr'])     ?   $G['addr']      : '',
            'address'   =>  isset($G['address'])  ?   $G['address']      : '',
        );
        $ins = pdo_update('merchat_mall',$data,array('id' => $id));
        if (!empty($ins)) {
            return json_encode(['code'=>1,'msg'=>'编辑成功']);
        }
            return json_encode(['code'=>0,'msg'=>'编辑失败']);
    }


    // 删除店铺操作
    public function delShop($G) {
        if(empty($G['myCheck'])) {
            return json_encode(['code'=>0,'msg'=>'请选择要操作的数据']);
        }
        $info = implode(',',$G['myCheck']);
        // 删除操作语句
        $sq = "DELETE FROM ".tablename('merchat_mall')." WHERE id IN(:ids)";
        $datas = pdo_query($sq, array(':ids' => $info));
        if(!$datas) {
            return json_encode(['code'=>0,'msg'=>'删除失败']);
        }
        // 删除成功返回
        return json_encode(['code'=>1,'msg'=>'删除成功']);
    }
    //********************* 添加实体店铺操作  START ******************************




    // 获取用户数据
    public function getMerchatUsers($uid) {
        $user = [];
        $user = pdo_getall('merchat_users', array('uid' => $uid))[0];
        return $user;
    }

    // 检测用户是否开启
    public function checkOpenUser($types,$val) {
        $res = [];
        if($types == 'phone') {
            $where = ['phone'=>$val,'appStatus'=>1];
        } else if($types == 'email') {
            $where = ['email'=>$val,'appStatus'=>1];
        }
        $res = pdo_get('merchat_users',$where,['uid']);
        return $res;
    }

    // 检测个人用户注册
    public function checkRegisters(&$G,$yzms) {
        global $_W;
        // 检测用户名是否已注册
        $res = $this->checkName($G['adminName']);
        if(!empty($res)) {
            return json_encode(['code'=>0,'msg'=>'管理员名称已存在，请重新输入！']);
        }

        // 不能为空
        if(empty($G['phone']) && empty($G['email'])) {
            return json_encode(['code'=>0,'msg'=>'手机号码或电子邮箱不能为空，请重新输入！']);
        }

        if(!empty($G['email'])) {
            if (!preg_match("/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i",$G['email'])){
                return json_encode(['code'=>0,'msg'=>'电子邮箱格式不正确，请重新输入！']);
            }

            $email = pdo_get('merchat_users',array('email' => $G['email']), array('uid'));
            if(!empty($email)) {
                return json_encode(['code'=>0,'msg'=>'该电子邮箱已注册，请重新输入！']);
            }

        }

        if(!empty($G['phone'])) {
            if(!preg_match('/^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$/',$G['phone'])) {
                return json_encode(['code'=>0,'msg'=>'手机号码格式不正确，请重新输入！']);
            }
            $phone = pdo_get('merchat_users',array('phone' => $G['phone']), array('uid'));
            if(!empty($phone)) {
                return json_encode(['code'=>0,'msg'=>'该手机号码已注册，请重新输入！']);
            }
        }
        // 验证验证码是否正确；
        $yzcode = strtoupper(trim($G['yzm']));
        // 注册验证码
        if($yzms != $yzcode) {
            return json_encode(['code'=>0,'msg'=>'验证码不正确，请检查！']);
        }
        if(empty($G['brandNames'])) {
            return json_encode(['code'=>0,'msg'=>'品牌名称不能为空，请重新输入！']);
        }

        if(empty($G['adminName'])) {
            return json_encode(['code'=>0,'msg'=>'管理人员不能为空，请重新输入！']);
        }

        if(empty($G['Country'])) {
            return json_encode(['code'=>0,'msg'=>'请选择国家，请重新输入！']);
        }

        if(empty($G['province'])) {
            return json_encode(['code'=>0,'msg'=>'请选择省份，请重新输入！']);
        }

        if(empty($G['city'])) {
            return json_encode(['code'=>0,'msg'=>'请选择城市，请重新输入！']);
        }

        if(empty($G['area'])) {
            return json_encode(['code'=>0,'msg'=>'请选择区域，请重新输入！']);
        }

        // 密钥
        $salt = random(8);
        // 用户表
        $users = [
            'groupid'   =>  5,
            'username'  =>  trim($G['adminName']),
            'password'  =>  $this->user_hash(123456,$salt),
            'salt'      =>  $salt,
            'type'      =>  0,
            'status'    =>  1,
            'joindate'  =>  time(),
            'joinip'    =>  $_SERVER['HTTP_X_REAL_IP'],
            'lastip'    =>  $_SERVER['HTTP_X_REAL_IP'],
            'lastvisit' =>  time(),
            'starttime' =>  time(),
            'endtime'   =>  0,
            'superior'  =>  0,// 上级；0：管理员，1操作员，2操作员，3操作员
            'sysTypes'  =>  'user',// 系统类型；
        ];
        // 记录商户信息表
        $result  = pdo_insert('users',$users);
        if(!empty($result)) {

            $uid = pdo_insertid();
            $customs = [
                'uid'       =>  $uid,
                'phone'     =>  isset($G['phone'])?trim($G['phone']):'',// 手机号码
                'email'     =>  isset($G['email'])?trim($G['email']):'',// 电子邮箱
                'enterName' =>  '',// 企业名称
                'enterQuali'=>  '', // 企业资质
                'brandName' =>  isset($G['brandNames'])?trim($G['brandNames']):'',// 品牌名称
                'brandTrade'=>  '', // 品牌商标
                'legalRepre'=>  '', // 法人代表
                'adminName' =>  isset($G['adminName'])?trim($G['adminName']):'',  // 管理人员
                'country'   =>  isset($G['Country'])?trim($G['Country']):'',    // 国家
                'province'  =>  isset($G['province'])?trim($G['province']):'',   // 省份
                'city'      =>  isset($G['city'])?trim($G['city']):'',       // 城市
                'area'      =>  isset($G['area'])?trim($G['area']):'',       // 区域
                'addr'      =>  isset($G['addr'])?trim($G['addr']):'',       // 地址区域
                'appStatus' =>  0,// 0:已注册，待审核，1：审核通过，3：禁用  用户状态
                'c_time'    =>  time(), // 创建时间
                'u_time'    =>  0,      // 更新时间
                'typeCom'   =>  isset($G['typeCom'])?trim($G['typeCom']):'',// company:企业商户，personal：个人商户
                'cusTypes'  =>  isset($G['cusTypes'])?trim($G['cusTypes']):'',// 境内：Territory、境外：Abroad
                'openid'    =>  isset($_W['openid'])?trim($_W['openid']):'',// 用户openid
            ];
            // 写入商户注册表
            pdo_insert('merchat_users',$customs);

            return json_encode(['code'=>1,'msg'=>'注册成功']);
        }
    }

    // 检测企业用户用户注册
    public function checkRegister($G,$yzms) {
        global $_W;
        // 检测用户名是否已注册
        $res = $this->checkName($G['adminName']);
        if(!empty($res)) {
            return json_encode(['code'=>0,'msg'=>'管理员名称已存在，请重新输入！']);
        }

        // 不能为空
        if(empty($G['phone']) && empty($G['email'])) {
            return json_encode(['code'=>0,'msg'=>'手机号码或电子邮箱不能为空，请重新输入！']);
        }

        if(!empty($G['email'])) {
            if (!preg_match("/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i",$G['email'])){
                return json_encode(['code'=>0,'msg'=>'电子邮箱格式不正确，请重新输入！']);
            }
            $email = pdo_get('merchat_users',array('email' => $G['email']), array('uid'));
            if(!empty($email)) {
                return json_encode(['code'=>0,'msg'=>'该电子邮箱已注册，请重新输入！']);
            }
        }

        if(!empty($G['phone'])) {
            if(!preg_match('/^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$/',$G['phone'])) {
                return json_encode(['code'=>0,'msg'=>'手机号码格式不正确，请重新输入！']);
            }
            $phone = pdo_get('merchat_users',array('phone' => $G['phone']), array('uid'));
            if(!empty($phone)) {
                return json_encode(['code'=>0,'msg'=>'该手机号码已注册，请重新输入！']);
            }
        }

        $yzcode = strtoupper(trim($G['yzm']));
        // 验证验证码是否正确；
        if($yzms != $yzcode) {
            return json_encode(['code'=>0,'msg'=>'验证码不正确，请检查！']);
        }

        if(empty($G['enterName']) && empty($G['enterQuali'])) {
            return json_encode(['code'=>0,'msg'=>'企业名称或企业资质不能为空，请重新输入！']);
        }

        if($G['CusTypes'] == 'Abroad' && empty($G['enterQuali']) ) {
            return json_encode(['code'=>0,'msg'=>'请上传企业资质！']);
        }

        if($G['CusTypes'] == 'Territory' && empty($G['brandNames']) ) {
            return json_encode(['code'=>0,'msg'=>'品牌名称不能为空，请重新输入！']);
        }

        if(empty($G['brandTrade'])) {
            return json_encode(['code'=>0,'msg'=>'品牌商标不能为空，请重新输入！']);
        }

        if(empty($G['legalRepre'])) {
            return json_encode(['code'=>0,'msg'=>'法人代表不能为空，请重新输入！']);
        }

        if(empty($G['adminName'])) {
            return json_encode(['code'=>0,'msg'=>'管理人员不能为空，请重新输入！']);
        }

        if(empty($G['Country'])) {
            return json_encode(['code'=>0,'msg'=>'请选择国家，请重新输入！']);
        }

        if(empty($G['province'])) {
            return json_encode(['code'=>0,'msg'=>'请选择省份，请重新输入！']);
        }

        if(empty($G['city'])) {
            return json_encode(['code'=>0,'msg'=>'请选择城市，请重新输入！']);
        }

        if(empty($G['area'])) {
            return json_encode(['code'=>0,'msg'=>'请选择区域，请重新输入！']);
        }

        // 密钥
        $salt = random(8);
        // 用户表
        $users = [
            'groupid'   =>  5,// 默认分配操作用户组
            'username'  =>  trim($G['adminName']),
            'password'  =>  $this->user_hash(123456,$salt),
            'salt'      =>  $salt,
            'type'      =>  0,
            'status'    =>  1,
            'joindate'  =>  time(),
            'joinip'    =>  $_SERVER['HTTP_X_REAL_IP'],
            'lastip'    =>  $_SERVER['HTTP_X_REAL_IP'],
            'lastvisit' =>  time(),
            'starttime' =>  time(),
            'endtime'   =>  0,
            'superior'  =>  0,// 上级；0：管理员，1操作员，2操作员，3操作员
            'sysTypes'  =>  'user',// 系统类型；
        ];
        // 记录商户信息表
        $result  = pdo_insert('users',$users);
        if(!empty($result)) {
            // 菜单权限；ims_users_permission

            // 填写公众号信息后，或注册审核后分配公众号操作；
            // 操作员，管理员组；ims_uni_account_users

            /**
             *  开发思路：
             *  1、商户注册，
             *  2、后台审核，分配公众号
             *  3、更新商户所属的公众号给下面的用户
             *
             *  // 商户注册用户时，分配商户所属的公众号给用户；
             */

            $uid = pdo_insertid();
            $customs = [
                'uid'       =>  $uid,
                'phone'     =>  isset($G['phone'])?trim($G['phone']):'',// 手机号码
                'email'     =>  isset($G['email'])?trim($G['email']):'',// 电子邮箱
                'enterName' =>  isset($G['enterName'])?trim($G['enterName']):'',// 企业名称
                'enterQuali'=>  isset($G['enterQuali'])?trim($G['enterQuali']):'', // 企业资质
                'brandName' =>  isset($G['brandNames'])?trim($G['brandNames']):'',// 品牌名称
                'brandTrade'=>  isset($G['brandTrade'])?trim($G['brandTrade']):'', // 品牌商标
                'legalRepre'=>  isset($G['legalRepre'])?trim($G['legalRepre']):'', // 法人代表
                'adminName' =>  isset($G['adminName'])?trim($G['adminName']):'',  // 管理人员
                'country'   =>  isset($G['Country'])?trim($G['Country']):'',    // 国家
                'province'  =>  isset($G['province'])?trim($G['province']):'',   // 省份
                'city'      =>  isset($G['city'])?trim($G['city']):'',       // 城市
                'area'      =>  isset($G['area'])?trim($G['area']):'',       // 区域
                'addr'      =>  isset($G['addr'])?trim($G['addr']):'',       // 地址区域
                'appStatus' =>  0,// 0:已注册，待审核，1：审核通过，3：禁用  用户状态
                'c_time'    =>  time(), // 创建时间
                'u_time'    =>  0,      // 更新时间
                'typeCom'   =>  isset($G['typeCom'])?trim($G['typeCom']):'',// company:企业商户，personal：个人商户
                'cusTypes'  =>  isset($G['cusTypes'])?trim($G['cusTypes']):'',// 境内：Territory、境外：Abroad
                'openid'    =>  isset($_W['openid'])?trim($_W['openid']):'',// 用户openid
            ];
            // 写入商户注册表
            pdo_insert('merchat_users',$customs);

            return json_encode(['code'=>1,'msg'=>'注册成功']);
        }
    }


    // 检测用户名是否注册
    public function checkName($userName) {
        if(empty($userName)) {
            return false;
        }
        $user = pdo_get('users', array('username' => $userName), array('uid'));
        return $user;
    }


    // 生成密码
    private function user_hash($passwordinput, $salt) {
        global $_W;
        $passwordinput = "{$passwordinput}-{$salt}-{$_W['config']['setting']['authkey']}";
        return sha1($passwordinput);
    }

}

?>