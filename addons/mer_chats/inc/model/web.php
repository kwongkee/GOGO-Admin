<?php

// 单利模式  WEB端模型
class Web{
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

    // *********************获取商户信息******************************
    public function getShops($arg) {
        $uid = isset($arg['uid']) ? trim($arg['uid']) : '';
        return pdo_getall('merchat_mall',['uid'=>$uid,'typeShop'=>'physiCal']);
    }
    // *********************获取商户信息******************************


    // *********************网络商铺地址*******************************

    // 编辑商铺地址
    public function shopUrl($agr) {
        $id = isset($agr['id']) ? trim($agr['id']) : null;
        $val = isset($agr['val']) ? trim($agr['val']) : null;
        if(empty($id) || empty($val)) {
            return json_encode(['code'=>0,'msg'=>'编辑失败，更新数据不能为空']);
        }

        $up = pdo_update('merchat_mall',['shopUrl'=>$val],['id'=>$id]);
        if(!$up){
            return json_encode(['code'=>0,'msg'=>'编辑失败，请稍后重试！']);
        }
        return json_encode(['code'=>1,'msg'=>'编辑成功']);
    }
    // *********************网络商铺地址*******************************



    //*************************************通知联系人***************************

    // 搜索选择通知人
    public function search($arrs) {
        $kwd    = isset($arrs['kwd']) ? trim($arrs['kwd']) : '';
        $uniacid  = isset($arrs['opval']) ? trim($arrs['opval']) : '';
        $ophtml = isset($arrs['ophtml']) ? trim($arrs['ophtml']) : '';

        $params = array();
        $params[':uniacid'] = $uniacid;
        $condition = ' and uniacid=:uniacid';
        $ds = array( );
        if (!empty($kwd)) {
            $condition .= ' AND (`nickname` LIKE :keyword or `mobile` LIKE :keyword or `openid` LIKE :keyword)';
            $params[':keyword'] = '%' . $kwd . '%';
        }

        $ds = pdo_fetchall('SELECT `uid`,`openid`,`nickname`,`avatar`,`mobile`,`uniacid` FROM ' . tablename('sz_yi_member') . ' WHERE 1' . $condition . ' order by id asc', $params);
        if(empty($ds)) {
            return json_encode(['code'=>0,'msg'=>'搜索不到您要的信息，请重新输入！']);
        }


        $html = '<table class="layui-table">
            <colgroup>
                <col width="100">
                <col width="150">
                <col width="200">
                <col width="60">
            </colgroup>
            <thead>

            <tr>
                <th>微信头像</th>
                <th>姓名</th>
                <th>手机</th>
                <th>操作</th>
            </tr>
            </thead>

            <tbody>';

        foreach($ds as $k=>&$v) {
            $v['puName'] = $ophtml;
            $v['set'] = json_encode($v);

            /*$html .= '<tr>
                        <td><img src="'.$v['avatar'].'" class="layui-nav-img"></td>
                        <td>'.$v['nickname'].'</td>
                        <td>'.$v['mobile'].'</td>
                        <td><a href="javascript:void(0);" onclick="Choice("'.$v["set"].'")">选择</a></td></tr>';*/


            $html .= "<tr>
                        <td><img src='{$v['avatar']}' class=\"layui-nav-img\"></td>
                        <td>{$v['nickname']}</td>
                        <td>{$v['mobile']}</td>
                        <td><a href='javascript:void(0);' onclick='Choice(this,{$v["set"]})'>选择</a></td></tr>";

        }

        $html .= '</tbody></table>';

        return json_encode(['code'=>1,'msg'=>$html]);
    }


    public function selectData($arr) {
        if(empty($arr['uid']) || empty($arr['openid']) || empty($arr['uniacid']) || empty('puName')) {
            return json_encode(['code'=>0,'msg'=>'数据选择失败，请稍后重试！']);
        }

        // 检查是否已经存在通知人；使用公众号+uid;
        $isNul = pdo_get('merchat_selectdata',['uid'=>trim($arr['uid']),'uniacid'=>trim($arr['uniacid'])]);
        if(!empty($isNul)) {
            return json_encode(['code'=>0,'msg'=>'该通知人已选择，请勿重复选择']);
        }

        $inData = [
            'uid'       =>  trim($arr['uid']),
            'openid'    =>  trim($arr['openid']),
            'nickname'  =>  trim($arr['nickname']),
            'avatar'    =>  trim($arr['avatar']),
            'mobile'    =>  trim($arr['mobile']),
            'uniacid'   =>  trim($arr['uniacid']),
            'puName'    =>  trim($arr['puName']),
            'c_time'    =>  time(),
        ];

        $ins = pdo_insert('merchat_selectdata',$inData);
        if(!$ins) {
            return json_encode(['code'=>0,'msg'=>'通知人选择失败，请稍后重试！']);
        }
        return json_encode(['code'=>1,'msg'=>'通知人选择成功']);
    }


    // 移除通知人
    public function selectDel($ars) {
        $uid = isset($ars['uid']) ? trim($ars['uid']) :'';
        if(empty($uid)) {
            return json_encode(['code'=>0,'msg'=>'移除失败!']);
        }
        $del = pdo_delete('merchat_selectdata',['uid'=>$uid]);
        if(!$del) {
            return json_encode(['code'=>0,'msg'=>'移除失败，请稍后重试！']);
        }
        return json_encode(['code'=>1,'msg'=>'移除成功']);
    }

    //*************************************通知联系人***************************







    //**************************************系统权限****************************

    // 获取权限列表
    public function getRole() {
        return pdo_getAll('merchat_role',['sysType'=>'system']);
    }

    // 获取权限列表
    public function getRoles($id) {
        return pdo_get('merchat_role',['id'=>$id]);
    }

    // 写入操作
    public function addRoles($arr) {
        if(empty($arr['roleName'])) {
            return json_encode(['code'=>0,'msg'=>'角色名称不能为空']);
        }

        // 检查角色名称是否已存在
        $isRole  = pdo_get('merchat_role',['roleName'=>$arr['roleName'],'sysType'=>'system']);
        if(!empty($isRole)) {
            return json_encode(['code'=>0,'msg'=>'角色名称已存在，请勿重复添加！']);
        }
        $insData = [
            'roleName'   => trim($arr['roleName']),
            'rolePermiss'=> trim($arr['rolePermiss']),
            'sysType'    => trim($arr['sysType']),
            'time'       => time(),
        ];

        $ins = pdo_insert('merchat_role',$insData);
        if(!$ins){
            return json_encode(['code'=>0,'msg'=>'添加失败，请稍后重试！']);
        }
        return json_encode(['code'=>1,'msg'=>'添加成功']);
    }


    // 编辑操作
    public function editRoles($arr) {
        $oldName  = isset($arr['oldName']) ? trim($arr['oldName']) : '';
        $roleName = isset($arr['roleName']) ? trim($arr['roleName']) : '';
        $id = isset($arr['id']) ? trim($arr['id']) : '';
        // 两个值不相等，有修改
        if($oldName != $roleName) {
            // 检查角色名称是否已存在
            $isRole  = pdo_get('merchat_role',['roleName'=>$arr['roleName'],'sysType'=>'system']);
            if(!empty($isRole)) {
                return json_encode(['code'=>0,'msg'=>'角色名称已存在，请勿重复添加！']);
            }

            $upData = [
                'roleName'=>$roleName,
                'rolePermiss'=>trim($arr['rolePermiss']),
                'sysType'=>trim($arr['sysType']),
                'time'=>time(),
            ];

        } else { // 否则相等
            $upData = [
                'rolePermiss'=>trim($arr['rolePermiss']),
                'time'=>time(),
            ];
        }
        // 更新操作
        $up = pdo_update('merchat_role',$upData,['id'=>$id]);
        if(empty($up)) {
            return json_encode(['code'=>0,'msg'=>'角色编辑失败，请稍后重试！']);
        }
        return json_encode(['code'=>1,'msg'=>'角色编辑成功']);
    }


    // 删除操作
    public function delRoles($arr) {
        $id = isset($arr['id']) ? trim($arr['id']) : '';
        if(empty($id)) {
            return json_encode(['code'=>0,'msg'=>'操作失败，请稍后重试!']);
        }

        $del = pdo_delete('merchat_role',['id'=>$id]);
        if(!$del) {
            return json_encode(['code'=>0,'msg'=>'删除失败，请稍后重试!']);
        }
        return json_encode(['code'=>1,'msg'=>'删除成功']);
    }

    //**************************************系统权限结束*************************


    // *************************************接口操作****************************
    // 获取接口单个数据
    public function getInters($arr) {
        $id = trim($arr['id']);
        return pdo_get('merchat_interface',['id'=>$id]);
    }

    // 获取接口列表
    public function getInterface($G) {
        $uniacid = trim($G['uniacid']);
        $res = pdo_getAll('merchat_interface',['uniacid'=>$uniacid]);
        return $res;
    }

    // 添加接口操作
    public function addpus($arr) {

        if(empty($arr['platform']) || empty($arr['Token']) || empty($arr['interfaceUrl']) || empty($arr['encodingKey']) || empty($arr['payChannel']) || empty($arr['merchatNum']) || empty($arr['merchatKey'])) {
            return json_encode(['code'=>0,'msg'=>'不允许有空项，请填写！']);
        }

        // 检查是否已经存在
        $wh = [
            'uniacid'=>trim($arr['uniacid']),
            'platform'=>trim($arr['platform']),
        ];
        $isp = pdo_get('merchat_interface',$wh);
        if($isp) {
            return json_encode(['code'=>0,'msg'=>'该平台已填写，请重新选择对应的平台！']);
        }

        $data = [
            'platform'=>trim($arr['platform']),
            'Token'=>trim($arr['Token']),
            'interfaceUrl'=>trim($arr['interfaceUrl']),
            'encodingKey'=>trim($arr['encodingKey']),
            'payChannel'=>trim($arr['payChannel']),
            'merchatNum'=>trim($arr['merchatNum']),
            'merchatKey'=>trim($arr['merchatKey']),
            'uniacid'=>trim($arr['uniacid']),
            'c_time'=>time(),
            'u_time'=>time(),
        ];

        $ins = pdo_insert('merchat_interface',$data);
        if(!$ins) {
            return json_encode(['code'=>0,'msg'=>'添加失败，请重新操作！']);
        }
        return json_encode(['code'=>1,'msg'=>'添加成功']);
    }

    // 编辑接口
    public function editpus($arr) {
        if(empty($arr['Token']) || empty($arr['interfaceUrl']) || empty($arr['encodingKey']) || empty($arr['payChannel']) || empty($arr['merchatNum']) || empty($arr['merchatKey'])) {
            return json_encode(['code'=>0,'msg'=>'不允许有空项，请填写！']);
        }
        $id = trim($arr['id']);
        $data = [
            'Token'=>trim($arr['Token']),
            'interfaceUrl'=>trim($arr['interfaceUrl']),
            'encodingKey'=>trim($arr['encodingKey']),
            'payChannel'=>trim($arr['payChannel']),
            'merchatNum'=>trim($arr['merchatNum']),
            'merchatKey'=>trim($arr['merchatKey']),
            'u_time'=>time(),
        ];
        $up = pdo_update('merchat_interface',$data,['id'=>$id]);
        if(!$up){
            return  json_encode(['code'=>0,'msg'=>'更新失败，请稍后重试！']);
        }
        return  json_encode(['code'=>1,'msg'=>'更新成功']);
    }

    public function delPus($ars) {
        $id = isset($ars['id']) ? trim($ars['id']) : '';
        if(empty($id)) {
            return json_encode(['code'=>0,'msg'=>'删除失败，请重试']);
        }
        $del = pdo_delete('merchat_interface',['id'=>$id]);
        if(empty($del)){
            return json_encode(['code'=>0,'msg'=>'删除失败，请重试']);
        }
        return json_encode(['code'=>1,'msg'=>'删除成功']);

    }

    //************************************接口操作******************************



    // 编辑商户权限
    public function chRole($arr) {

        $uid = isset($arr['uid']) ? trim($arr['uid']) : '';
        $roleid = isset($arr['roleid']) ? trim($arr['roleid']) : '';

        if(empty($uid) || empty($roleid)) {
            return json_encode(['code'=>0,'msg'=>'请选择操作权限']);
        }

        // 更新商户权限；
        $up = pdo_update('users',['roleid'=>$roleid],['uid'=>$uid]);
        $up1 = pdo_update('merchat_users',['roleid'=>$roleid],['uid'=>$uid]);
        if(!$up || !$up1) {
            return json_encode(['code'=>0,'msg'=>'权限修改失败']);
        }
        return json_encode(['code'=>1,'msg'=>'权限修改成功']);
    }

    // 获取权限列表
    public function getPermiss() {
        $pubs = pdo_fetchall("SELECT id,roleName FROM ".tablename('merchat_role')." WHERE sysType = :sysType", array(':sysType' => 'system'));
        return $pubs;
    }

    // 审核通过，禁止
    public function adopt($arr) {
        // 用户
        $uid = isset($arr['uid']) ? trim($arr['uid']) : '';
        // yes: 通过，no：不通过；
        $type = isset($arr['types']) ? trim($arr['types']) : '';

        if($type == 'yes') {
            $uStatus = 2;
            // 0注册、1审核通过，3禁止使用
            $appStatus = 1;
        } else if($type == 'no') {
            $uStatus = 1;
            // 0注册、1审核通过，3禁止使用
            $appStatus = 3;
        }

        // 更改状态；ims_users
        $up = pdo_update('users',['status'=>$uStatus],['uid'=>$uid]);
        //  更新用户表
        $up1 = pdo_update('merchat_users',['appStatus'=>$appStatus],['uid'=>$uid]);
        if(empty($up) && empty($up1)) {
            return json_encode(['code'=>0,'msg'=>'操作失败，请稍后重试']);
        }
        return json_encode(['code'=>1,'msg'=>'操作成功！']);
    }

    // 获取已分配的公众号
    public function getUpublic($arrs) {
        $uid  = isset($arrs['uid']) ? trim($arrs['uid']) : '';
        $pubs = pdo_fetchall("SELECT uniacid FROM ".tablename('uni_account_users')." WHERE uid = :uid", array(':uid' => $uid));
        $res = [];
        if(is_array($pubs)) {
            foreach ($pubs as $v){
                $res[] = $v['uniacid'];
            }
        }
        return $res;
    }

    // 获取所有公众号列表
    public function getPublics() {
        $publiclist = pdo_fetchall("SELECT uniacid,name FROM ".tablename('account_wechats'));
        return $publiclist;
    }


    // 编辑分配公众号
    public function editPublic($arr) {

        // 商户ID
        $uid        = isset($arr['uid'])        ? trim($arr['uid']) : '';
        // 公众号数组
        $public     = $arr['public'];
        // 商户名词
        $username   = isset($arr['username'])   ? trim($arr['username']) : '';

        // 公众号列表；
        if(!is_array($public)) {
            return json_encode(['code'=>0,'msg'=>'请选择需要分配的公众号']);
        }

        // 查询是否有已存在的数据就删除
        $info = pdo_get('uni_account_users',['uid'=>$uid]);
        if($info) {
            // 删除已存在的分配公众号
            pdo_delete('uni_account_users',['uid'=>$uid]);
        }

        // 检查是否已分配了供应商
        // 供应商列表 sz_yi_perm_user
        $perm = pdo_get('sz_yi_perm_user',['uid'=>$uid]);
        if($perm) {
            // 删除供应商
            pdo_delete('sz_yi_perm_user',['uid'=>$uid]);
        }

        // 查询商户下的所有操作员；
        $operator = pdo_getall('users',['superior'=>$uid]);
        if(!empty($operator)) {
            // 删除操作员
            $uids = implode(',',$operator);
            if(!empty($uids)) {
                // 删除后台用户信息
                $sq = "DELETE FROM ".tablename('uni_account_users')." WHERE uid IN(:uid)";
                pdo_query($sq, array(':uid' => $uids));
            }
        }

        if(count($public) > 1) {

            // 处理循环所选公众号
            foreach ($public as $v => $vv) {
                $tmp = [
                    'uniacid' => $v,
                    'uid' => $uid,
                    'role' => 'manager',
                    'rank' => 0,
                ];

                // 写入供应商表
                $permtp = [
                    'uniacid' => $v,
                    'uid' => $uid,
                    'username' => $username,
                    'password' => md5(123456),
                    'roleid' => 1,
                    'status' => 1,
                ];

                if (is_array($operator)) {
                    foreach ($operator as $ov) {
                        $optmp = [
                            'uniacid' => $v,
                            'uid' => $ov['uid'],
                            'role' => 'manager',
                            'rank' => 0,
                        ];
                        // 写入数据 uni_account_users 操作员
                        pdo_insert('uni_account_users', $optmp);
                    }
                }

                // 写入供应商列表
                pdo_insert('sz_yi_perm_user', $permtp);
                // 写入数据 uni_account_users 操作员
                pdo_insert('uni_account_users', $tmp);
            }
        } else if(count($public) == 1) {

            // 返回key 第一个key
            $v = array_keys($public)[0];

            $tmp = [
                'uniacid' => $v,
                'uid' => $uid,
                'role' => 'manager',
                'rank' => 0,
            ];

            // 写入供应商表
            $permtp = [
                'uniacid' => $v,
                'uid' => $uid,
                'username' => $username,
                'password' => md5(123456),
                'roleid' => 1,
                'status' => 1,
            ];

            if (is_array($operator) && !empty($operator)) {
                // 写入表
                foreach ($operator as $ov) {
                    $optmp = [
                        'uniacid' => $v,
                        'uid' => $ov['uid'],
                        'role' => 'manager',
                        'rank' => 0,
                    ];
                    // 写入数据 uni_account_users 操作员
                    pdo_insert('uni_account_users', $optmp);
                }
            }

            // 写入供应商列表
            pdo_insert('sz_yi_perm_user', $permtp);
            // 写入数据 uni_account_users 操作员
            pdo_insert('uni_account_users', $tmp);
        }
        return json_encode(['code'=>1,'msg'=>'公众号分配成功']);
        /**
         * 1、公众号不为空
         * 2、先删除原有的
         * 3、加入新的；
         * 4、加入完成，查询商户下的操作员，重新分配数据；
         */
    }



}
?>