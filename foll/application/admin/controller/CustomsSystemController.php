<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Request;
use think\Db;
use app\admin\logic\LoadGoodsFile;
use think\MongoDB;
use think\Config;
use PHPExcel;
use PHPExcel_IOFactory;

/**
 * 海关申报系统管理更新版
 * Class DeclSystemController
 * @package app\admin\controller
 */
class CustomsSystemController extends Auth
{

    /**
     * 申报系统菜单管理
     * @return mixed
     */
    public function declMenuManage()
    {
        return view('CustomsSystem/menu/menu_manage', ['title' => '菜单管理']);
    }

    //菜单权限配置
    public function menu(Request $request)
    {
        $id = $request->get('id');
        $userData = Db::name('decl_user')->where('id',$id)->find();
        if ($request->isGET()) {
            return view('CustomsSystem/merchant/menu', ['userData' => $userData ]);
        }

        if ($request->isPOST()) {
            $data = $request->post();
            $id = $data['id'];
            if ( $data['menus'] == "" || $data['business_type'] == "") {
                return json(['code' => -1, 'msg' => '菜单或业务类型不能为空']);
            }

            if( Db::name('decl_user')->update(array('id'=>$id, 'menus'=>$data['menus'], 'business_type'=>$data['business_type'])) )
            {
                return json(['code' => 0, 'msg' => '提交成功']);
            }else{
                return json(['code' => -1, 'msg' => '提交失败']);
            }
        }   
    }

    /**
     * ajax请求获取菜单列表
     * @param Request $request
     * @return mixed
     */
    public function fetchMenuList(Request $request)
    {
        if ($request->isAJAX()) {
            $_status = ['显示', '隐藏'];
            $list = model('DeclMenu', 'model')->FetchMenuList();
//            Db::name('decl_access')->select();
            foreach ($list as &$item) {
                $item['status'] = $_status[$item['status']];
                $item['menuIcon'] = 'layui-icon-set';
            }
            return json(['code' => 0, 'msg' => '', 'count' => count($list), 'data' => $list]);
        } else {
            return json(['code' => -1, 'msg' => '未知错误']);
        }

    }

    /**
     * 新增申报系统菜单
     * @param Request $request
     * @return mixed
     */
    public function declMenuCreate(Request $request)
    {
        return ($request->isGET() == true) ? view('CustomsSystem/menu/menu_create',
            ['pid' => $request->get('pid')]) : model('DeclMenu', 'model')->MenuStorage($request->post());
    }

    /**
     * 更新编辑申报系统菜单
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function declMenuRep(Request $request)
    {
        return ($request->isGET() == true) ? view('CustomsSystem/menu/menu_edit', [
            'data' => model('DeclMenu', 'model')->FindMenuInfoById(['id' => $request->get('id')]),
        ]) : model('DeclMenu', 'model')->MenuUpdate($request->post());
    }

    /**
     * 删除菜单
     * @param Request $request
     * @return mixed
     */
    public function declMenuDel(Request $request)
    {
        if ($request->get('id') === '') {
            return json(['code' => -1, 'msg' => '错误！']);
        }
        $model = model('DeclMenu', 'model');
        if ($model->FindMenuInfoById(['pid' => $request->get('id')])) {
            return json(['code' => -1, 'msg' => '存在子级菜单,不允许删除!']);
        }
        $model->menuDel($request->get('id'));
        return json(['code' => 0, 'msg' => '已删除']);
    }


    /**
     * 角色管理
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function declRoleInfo()
    {
        $status = ['启用', '禁用'];
        $res = model('DeclRoleModel', 'model')->fetchRole();
        return view('CustomsSystem/role/role_manage',
            ['title' => '角色管理', 'page' => $res->render(), 'list' => $res->toArray()['data'], 'status' => $status]);
    }


    /**
     * 验证身份证次数
     * @param  Request  $request
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function changeVerifIdcardFrequency(Request $request)
    {
        if (!$request->has('id') || !is_numeric($request->get('id'))) {
            return json(['code' => -1, 'message' => 'id参数错误']);
        }
        if (!$request->has('startUp') || !is_numeric($request->get('startUp'))) {
            return json(['code' => -1, 'message' => 'startUp参数错误']);
        }
        Db::name('decl_user')->where('id', $request->get('id'))->update(['start_up' => $request->get('startUp')]);
        return json(['code' => 0, 'message' => '完成']);
    }

    /**
     * 返回组装好的菜单列表
     * @return mixed
     */
    public function getMenuList(Request $request)
    {
        $user_id = $request->post('user_id');
        $userData = Db::name('decl_user')->field('menus,business_type')->where(array('id'=>$user_id))->find();
        if($user_id)
        {
            $user_menus = ",".$userData['menus'];
            $user_type = ",".$userData['business_type'];
        }else{
            $user_menus = "";
            $user_type = "";
        }

        $list = model('DeclMenu', 'model')->FetchMenuList();
        $newList = [];
        foreach ($list as $item) {
            array_push($newList, ['id' => $item['id'], 'pId' => $item['pid'], 'title' => $item['title'] , 'checked' => strpos($user_menus,",".$item['id'].",") !== false ? true : false ]);
            foreach ($list as $value) {
                if ($item['id'] == $value['pid'] && !$value['pid'] != 0) {
                    array_push($newList, ['id' => $value['id'], 'pId' => $value['pid'], 'title' => $value['title'], 'checked' => strpos($user_menus,",".$value['id'].",") !== false ? true : false ]);
                }
            }
        }

        $type = Db::name('decl_user_business_type')->select();
        $typeList = [];
        foreach ($type as $v) {
            array_push($typeList, ['id' => $v['id'], 'title' => $v['title'] , 'checked' => strpos($user_type,",".$v['id'].",") !== false ? true : false ]);
        }
        $data['menuList'] = $newList;
        $data['typeList'] = $typeList;
        return json($data);
    }

    /**
     * 添加角色
     * @param Request $request
     * @return view||json
     */
    public function declRoleAdd(Request $request)
    {
        if ($request->isGET()) {
            return view('CustomsSystem/role/role_add', ['title' => '添加角色']);
        }

        if ($request->isPOST()) {
            $data = $request->post();
            if (!(isset($data['data']['role_name'])) && $data['data']['role_name'] === "") {
                return json(['code' => -1, 'msg' => '角色名称不能为空']);
            }
            if (!isset($data['data']['status'])) {
                return json(['code' => -1, 'msg' => '状态字段不存在']);
            }
            if (!isset($data['menu'])) {
                return json(['code' => -1, 'msg' => '请选择菜单']);
            }
            $data['data']['created_time'] = date('Y-m-d H:i:s', time());
            $roleModel = model('DeclRoleModel', 'model');
            $roleModel->sTrans();
            try {
                $rid = $roleModel->RoleStorage($data['data']);
                $node = [];
                foreach ($data['menu'] as $item) {
                    array_push($node, [
                        'role_id' => $rid,
                        'node_id' => $item['id'],
                        'level' => $item['level'],
                        'pid' => $item['pid'],
                    ]);
                }
                $roleModel->roleLinkNode($node);
                $roleModel->transCommit();
            } catch (\Exception $e) {
                $roleModel->roll();
                return json(['code' => -1, 'msg' => '添加失败']);
            }
            return json(['code' => 0, 'msg' => '添加成功']);
        }
    }


    /**
     * 更新状态
     * @param Request $request
     */
    public function roleStatus(Request $request)
    {
        $data = $request->get();
        if ($data['id'] === '' && !isset($data['id'])) {
            $this->error('操作错误', Url('admin/customssystem/declRoleInfo'));
        }
        if ($data['status'] === '' && !isset($data['status'])) {
            $this->error('操作错误', Url('admin/customssystem/declRoleInfo'));
        }
        try {
            model('DeclRoleModel', 'model')->roleStatus($request->get('id'), $request->get('status'));
        } catch (\Exception $e) {
            $this->error($e->getMessage(), Url('admin/customssystem/declRoleInfo'));
        }
        $this->success('操作成功', Url('admin/customssystem/declRoleInfo'));
    }


    /**
     * 删除
     * @param Request $request
     */
    public function roleDel(Request $request)
    {
        $id = $request->get('id');
        if ($id === '') {
            $this->error('操作错误', Url('admin/customssystem/declRoleInfo'));
        }
        $roleModel = model('DeclRoleModel', 'model');
        if ($roleModel->isRoleUse($id)) {
            $this->error('存在使用，不允许删除', Url('admin/customssystem/declRoleInfo'));
        }
        $roleModel->roleDel($id);
        $this->success('删除成功', Url('admin/customssystem/declRoleInfo'));
    }


    public function roleEdit(Request $request)
    {
        if ($request->isGET()) {
            $id = $request->get('id');
            $roleInfo = model('DeclRoleModel', 'model')->findRoleInfoByID($id);
            return view('CustomsSystem/role/role_edit', ['role' => $roleInfo]);
        } else {
            $data = $request->post();
            if (!isset($data['menu'])) {
                return json(['code' => -1, 'msg' => '请选择菜单']);
            }
            if ($data['data']['role_name'] === "") {
                return json(['code' => -1, 'msg' => '请填写名称']);
            }

            $roleModel = model('DeclRoleModel', 'model');
            $roleModel->roleRep($data['data']['id'], [
                'role_name' => $data['data']['role_name'],
                'remark' => $data['data']['remark'],
                'status' => $data['data']['status'],
                'update_time' => date('Y-m-d H:i:s', time()),
            ]);
            $node = [];
            foreach ($data['menu'] as $item) {
                array_push($node, [
                    'role_id' => $data['data']['id'],
                    'node_id' => $item['id'],
                    'level' => $item['level'],
                    'pid' => $item['pid'],
                ]);
            }
            $roleModel->roleLinkNode($node);
            return json(['code' => 0, 'msg' => '更新成功']);
        }
    }


    /**
     * 商家注册管理
     * @return mixed
     */
    public function declUserManage()
    {
        $status = ['有效', '禁用', '审核中'];
        $result = model('DeclUserModel', 'model')->fetchAllCheckUser();
        $role = model('DeclRoleModel', 'model')->fetchAllRole();
        // 获取所有的公众号 2019-12-06
        $publi = DB::name('account_wechats')->field(['uniacid','name'])->select();
        return view('CustomsSystem/user/index', [
            'title' => '商家注册管理',
            'status' => $status,
            'roles' => $role,
            'page' => $result->render(),
            'list' => $result->toArray()['data'],
            'publi' => $publi,
        ]);
    }
    
    /**
     * 获取申报系统商户列表
     * @param  Request  $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDeclMerchant(Request $request)
    {
        $limit = $request->get('limit');
        $page =$request->get('page');
        $page = ($page-1)*$limit;
        
        $result = model('DeclUserModel', 'model')->fetchMerchant($page,$limit);
        $count = model('DeclUserModel', 'model')->countMerchant();
        $role = model('DeclRoleModel', 'model')->fetchAllRole();
        $publi = DB::name('account_wechats')->field(['uniacid','name'])->select();
        $status = ['有效', '禁用', '审核中'];
        foreach ($result as &$item){
            // $item['created_at']=date('Y-m-d H:i:s',$item['created_at']);
            $item['role'] = $role;
            $item['public']=$publi;
            $item['user_status']=$status[$item['user_status']];

            if($item['openid'] == '' || $item['openid'] == NULL)
            {
                $item['wechat_info'] = '未绑定';
            }else{
                $wechat_info = Db::name('mc_mapping_fans')
                ->alias('a')
                ->join('mc_members b',"a.uid=b.uid",'left')
                ->field(['a.openid','b.nickname','b.avatar'])
                ->where(['a.uniacid' => 3, 'a.openid' => $item['openid']])
                ->find();
                $item['wechat_info'] = '<img width="20" src="'.str_replace("/132132","/132",$wechat_info['avatar']).'">'.$wechat_info['nickname'];
                
            }
        }
        return json(['code' => 0, 'message' => '完成', 'data' => $result, 'count' => $count]);
    }

    /**
     * 商户审核操作
     * @param Request $request
     */
    public function declUserVerif(Request $request)
    {
        if ($request->isGET()) {
            $data = $request->get();
        } else {
            $data = $request->post();
        }

        if ((!isset($data['uid']) && $data['uid'] == "") || (!isset($data['status']) && $data['status'] == "")) {
            $this->error('参数错误', Url('admin/customssystem/declUserManage'));
        }

        if (!is_numeric($data['uid'])) {
            $this->error('参数错误', Url('admin/customssystem/declUserManage'));
        }
        if (!is_numeric($data['status'])) {
            $this->error('参数错误', Url('admin/customssystem/declUserManage'));
        }

        $userModel = model('DeclUserModel', 'model');
        $userRes = $userModel->findUserInfoById($data['uid']);
        $msg = null;
        if ($data['status'] == 0) {
            //更新状态
            $userModel->userStatus($data['uid'], $data['status']);
            //分配权限
            $userModel->userLinkRole(['role_id' => $data['role'], 'user_id' => $userRes['id']]);
            $msg = '{"touser":"' . $userRes['openid'] . '","msgtype":"text","text":{"content":"注册成功,平台域名：http://declare.gogo198.cn,登录账户：' . $userRes['user_tel'] . '"}}';
        } else {
            //不通过删除账户
            $userModel->userDel($data['uid']);
            $msg = '{"touser":"' . $userRes['openid'] . '","msgtype":"text","text":{"content":"注册不通过"}}';
        }

        $token = RequestAccessToken($userRes['uniacid']);
        $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $token;
        httpRequest($url, $msg, ["Content-Type: application/json"]);
        //发送注册审核信息
        $this->success('操作成功', Url('admin/customssystem/declUserManage'));
    }

    /**
     * 禁用启用账户
     * @param Request $request
     */
    public function changeUserStatus(Request $request)
    {
        if (!(is_numeric($request->get('uid')) && is_numeric($request->get('status')))) {
            $this->error('参数错误', Url('admin/customssystem/declUserManage'));
        }
        model('DeclUserModel', 'model')->userStatus($request->get('uid'), $request->get('status'));
        $this->success('操作成功', Url('admin/customssystem/declUserManage'));
    }


    /**
     * @param Request $req
     * 分配公众号
     */
    public function distribution(Request $req) {

        $id      = $req->post('id');
        $uniacid = $req->post('uniacid');
        $mobile  = trim($req->post('mobile'));
        $supp = DB::name('decl_user')->where('id',$id)->field('supplier')->find();
        $up = DB::name('decl_user')->where('id',$id)->update(['uniacid'=>$uniacid]);
        if(!$up) {
            echo json_encode(['code'=>0,'message'=>'分配失败，请稍后重试']);
            exit;
        }

        // 更新总商户  2019-12-06
        DB::name('total_merchant_account')->where('mobile',$mobile)->update(['uniacid'=>$uniacid]);
        // 查找 supplier  更新供应商；
        if($supp['supplier']) {
           DB::name('sz_yi_perm_user')->where('uid',$supp['supplier'])->update(['uniacid'=>$uniacid]);
        }
        echo json_encode(['code'=>1,'message'=>'分配成功']);
        exit;
    }

    //分配会计系统权限
    public function account_auth(Request $req){
        $id      = $req->post('id');
        $account_auth = $req->post('account_auth');
        $mobile  = trim($req->post('mobile'));

        $up = DB::name('decl_user')->where('id',$id)->update(['account_auth'=>$account_auth]);
        if(!$up) {
            echo json_encode(['code'=>0,'message'=>'分配失败，请稍后重试']);
            exit;
        }
        echo json_encode(['code'=>1,'message'=>'分配成功']);
        exit;
    }

    /**
     * 更改用户角色
     * @param Request $request
     * @return mixed
     */
    public function changeUserRole(Request $request)
    {
        if (!(is_numeric($request->get('id')) && is_numeric($request->get('role_id')))) {
            return json(['code' => -1, 'message' => '参数错误']);
        }
        model('DeclUserModel', 'model')->updateRole($request->get('id'), $request->get('role_id'));
        return json(['code' => 0, 'message' => '更改成功']);
    }

    /**
     * 添加订单申报折扣
     */
    public function addOrderCustomsDiscount(Request $request)
    {
        if (!(is_numeric($request->get('id')) && is_numeric($request->get('discountNum')))) {
            return json(['code' => -1, 'message' => '参数错误']);
        }
        model('DeclUserModel', 'model')->addOrderCustomsDiscount($request->get('id'),
            ($request->get('discountNum') / 10));
        return json(['code' => 0, 'message' => '已添加']);
    }

    /**
     * 基础代码上传
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     */
    public function codeFileUpload()
    {
        return view('CustomsSystem/goods/code_upload', ['title' => '基础文件代码上传']);
    }
    
    /**
     *商品图片上传
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function uplGoodsPictureFile(Request $request)
    {
        if ($request->isPOST()) {
            $files = $request->file('file');
            if (!is_object($files)) {
                return json(['code' => -1, 'message' => '上传失败']);
            }
            $info = $files->validate(['ext' => 'jpg'])->rule(function ($files) {
                return $files->getInfo('info')['name'];
            })->move('../../attachment/images/total_image');
            if ($info) {
                return json(['code' => 0, 'message' => '上传成功', 'info' => $info]);
            } else {
                return json(['code' => -1, 'message' => $files->getError()]);
            }
        }

        if ($request->isGET()) {
            $list = Db::name('account_wechats')->field(['name', 'uniacid'])->select();
            return view('CustomsSystem/goods/image_upload', ['title' => '商品图片上传', 'list' => $list]);
        }
    }
    
    
    /**
     *正面清单管理
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     * @throws \think\exception\DbException
     */
    public function elistCodeManage(Request $request)
    {
        if ($request->get('search') != '') {
            $list = Db::name('tax_number')->where('tax_number', $request->get('search'))->paginate(10, true, [
                'query' => ['s' => 'admin/goods/elist_code'],
                'var_page' => 'page',
                'type' => 'Layui',
                'newstyle' => true
            ]);
        } else {
            $list = Db::name('tax_number')->paginate(10, true, [
                'query' => ['s' => 'admin/goods/elist_code'],
                'var_page' => 'page',
                'type' => 'Layui',
                'newstyle' => true
            ]);
        }

        return view('CustomsSystem/goods/goods_elist_code',
            ['title' => '正面清单代码管理', 'page' => $list->render(), 'list' => $list->toArray()]);
    }

    /**
     * 正面清单上传
     * @param Request $request
     * @return mixed
     */
    public function uplListCodeFIle(Request $request)
    {
        if (!$request->file('file')) {
            return json(['code' => -1, 'message' => '请上传文件']);
        }
        $path = ROOT_PATH . 'public' . DS . 'uploads';
        $file = $request->file('file')->validate(['ext' => 'xls'])->move($path);
        if (!$file) {
            return json(['code' => -1, 'message' => '请检测格式xls']);
        }
        try {
            $model = model('CodeExcelFileWith', 'logic');
            $model->saveCodeListTable($model->readCodeFile($path . '/' . $file->getSaveName()));
        } catch (\Exception $exception) {
            @unlink($path . '/' . $file->getSaveName());
            return json([
                'code' => -1,
                'message' => $exception->getMessage() . ':' . $exception->getLine() . ':' . $exception->getFile(),
            ]);
        }
        @unlink($path . '/' . $file->getSaveName());
        return json(['code' => 0, 'message' => '完成']);
    }

    /**
     * 海关申报相关代码表上传
     * @param Request $request
     * @return mixed
     */
    public function uplCodeFile(Request $request)
    {

        if (!$request->file('file')) {
            return json(['code' => -1, 'message' => '请上传文件']);
        }
        $path = ROOT_PATH . 'public' . DS . 'uploads';
        $file = $request->file('file')->validate(['ext' => 'xls'])->move($path);
        if (!$file) {
            return json(['code' => -1, 'message' => '请检测格式xls']);
        }
        try {
            $model = model('CodeExcelFileWith', 'logic');
            $model->saveCodeTable($model->readCodeFile($path . '/' . $file->getSaveName()));
        } catch (\Exception $exception) {
            @unlink($path . '/' . $file->getSaveName());
            return json([
                'code' => -1,
                'message' => $exception->getMessage() . ':' . $exception->getLine() . ':' . $exception->getFile(),
            ]);
        }
        @unlink($path . '/' . $file->getSaveName());
        return json(['code' => 0, 'message' => '完成']);
    }
    
    /**
     * 商品总库存信息
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goodsTotalList(Request $request)
    {
        $code = Db::name('custom_district')->select();
        $select = '<form class="layui-form" action=""> <div class="layui-form-item"><div class="layui-input-block" style="margin-left: 17px;margin-right: 14px;margin-top: 12px;"><select name="code" id="code" lay-verify="required" lay-search><option value=""></option>';
        foreach ($code as $value) {
            $select .= '<option value="' . $value['code_value'] . '">' . $value['code_name'] . ':' . $value['code_value'] . '</option>';
        }
        $select .= '</select></div></div></form>';
        return view('CustomsSystem/goods/goods_index', ['title' => '商品库存管理','select'=>$select]);
    }
    
    /**
     *异步获取商品信息列表
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function layuiAjaxGetGoodsInfo(Request $request)
    {
        $wh = [];
        $parm = $request->get();
        if (isset($parm['brand']) && $parm['brand'] != '') {
            $wh['brand'] = ['like','%'.$parm['brand'].'%'];
        }
        if (isset($parm['name']) && $parm['name'] != '') {
            $wh['goods_name'] = ['like','%'.$parm['name'].'%'];
        }
        if (isset($parm['status']) && $parm['status'] != '') {
            $wh['status'] = $parm['status'];
        }

        if ((isset($parm['stime']) && $parm['stime'] != '') && (isset($parm['etime']) && $parm['etime'])) {
            $wh['create_time'] = ['between', strtotime($parm['stime']) . "," . strtotime($parm['etime'])];
        }
        $page = $request->get('page');
        $limit = $request->get('limit');
        $page = ($page - 1) * $limit;
        $count = \app\admin\model\GoodsModel::where($wh)->count('id');
        $list = \app\admin\model\GoodsModel::where($wh)->order('id', 'desc')->limit($page, $limit)->select();
        return json(['code' => 0, 'message' => '完成', 'data' => $list, 'count' => $count]);
    }

    /**
     * 批量导入商品
     * @param Request $request
     * @return mixed
     */
    public function loadGoods(Request $request)
    {
        set_time_limit(2000);
        if (!$request->post('code')) {
            return json(['code' => -1, 'message' => '请选择关区']);
        }

        try {
            $logicObj = new LoadGoodsFile();
            $dst = $logicObj->GoodsDataHandle($request);
        } catch (\Exception $e) {
            return json(['code' => 0, 'message' => $e->getMessage()]);
        }
        if ($dst!=""){
            return json(['code' => 0, 'message' => $dst]);
        }
        return json(['code' => 0, 'message' => '导入成功']);
    }
    
    public function exportTotalGoods(Request $request)
    {
        $wh = [];
        $parm = $request->get();
        if (isset($parm['brand']) && $parm['brand'] != '') {
            $wh['brand'] = ['like','%'.$parm['brand'].'%'];
        }
        if (isset($parm['name']) && $parm['name'] != '') {
            $wh['goods_name'] = ['like','%'.$parm['name'].'%'];
        }
        if (isset($parm['status']) && $parm['status'] != '') {
            $wh['status'] = $parm['status'];
        }

        if ((isset($parm['stime']) && $parm['stime'] != '') && (isset($parm['etime']) && $parm['etime'])) {
            $wh['create_time'] = ['between', strtotime($parm['stime']) . "," . strtotime($parm['etime'])];
        }
        $list = \app\admin\model\GoodsModel::where($wh)->field(['goodssn','goods_name','goods_style','origin_country','brand','price'])->select();
        if (empty($list)){
            return $this->error('查询无数据');
        }
        $PHPExcel = new PHPExcel();
        $PHPExcel->setActiveSheetIndex(0);
        $PHPSheet = $PHPExcel->getActiveSheet();
        $PHPSheet->setTitle('sheet1');
        $n = 2;
        $PHPSheet->setCellValue('A1','平台货号')
            ->setCellValue('B1','商品名称')
            ->setCellValue('C1','商品规格')
            ->setCellValue('D1','原产国')
            ->setCellValue('E1','品牌')
            ->setCellValue('F1','价格');
        foreach ($list as $val) {
            $PHPSheet->setCellValue('A' . $n,$val['goodssn'])
                ->setCellValue('B'.$n,$val['goods_name'])
                ->setCellValue('C'.$n,$val['goods_style'])
                ->setCellValue('D'.$n,$val['origin_country'])
                ->setCellValue('E'.$n,$val['brand'])
                ->setCellValue('F'.$n,$val['price']);
            ++$n;
        }
        $tmpFileName  = date('YmdHis').mt_rand(1111,9999) . '.xlsx';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename='.$tmpFileName);
        header('Cache-Control: max-age=0');//禁止缓存
        $phpWrite = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
        $phpWrite->save('php://output');
    }

    public function updateTotalGoodsFormImportExcel(Request $request)
    {
        try {
            $logicObj = new LoadGoodsFile();
            $file = $logicObj->saveUploadExcel($request->file('file'));
            $logicObj->readExcelAndUpdateTable($file);
            @unlink($file);
        }catch (\Exception $exception){
            return json(['code'=>1,'msg'=>$exception->getMessage()]);
        }
        return json(['code'=>0,'msg'=>'更新完成']);
    }

    /**
     * 编辑库存商品
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editGoodsInfo(Request $request)
    {
        if ($request->isGET()) {
            if (!$request->has('id')) {
                return json(['code' => -1, 'message' => '参数错误']);
            }
            $data = \app\admin\model\GoodsModel::where('id', $request->get('id'))->find();
            return view('CustomsSystem/goods/goods_edit', ['data' => $data]);
        }

        if ($request->isPOST()) {
            try {
                $goods = new \app\admin\model\GoodsModel();
                $goods->where('id', $request->post('id'))->update($request->post());
            } catch (\Exception $e) {
                return json(['code' => -1, 'message' => $e->getMessage()]);
            }
            return json(['code' => 0, 'message' => '更新成功']);
        }
    }


    /**
     * 商品库存删除
     * @param Request $request
     * @return mixed
     */
    public function delStockGoods(Request $request)
    {
        if (!$request->has('id')) {
            return json(['code' => -1, 'message' => '参数错误']);
        }
        try {
            \app\admin\model\GoodsModel::where('id', $request->get('id'))->delete();
        } catch (\Exception $e) {
            return json(['code' => -1, 'message' => $e->getMessage()]);
        }
        return json(['code' => 0, 'message' => '删除完成']);
    }
    
    
    /**
     * 智能核价
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     * @throws \think\exception\DbException
     */
    public function intelligentNuclearPrice(Request $request)
    {
        if ($request->get('search') != '') {
            $list = Db::name('custom_district')
                ->alias('a1')
                ->join('customs_elec_goodspricebenk a2', 'a1.code_value=a2.district_code', 'left')
                ->where('a1.code_value', $request->get('search'))
                ->field(['a1.code_value', 'a2.*'])
                ->paginate(10, true,
                    [
                        'query' => ['s' => 'admin/goods/intelligentNuclearPrice'],
                        'var_page' => 'page',
                        'type' => 'Layui',
                        'newstyle' => true
                    ]);
        } else {
            $list = Db::name('custom_district')
                ->alias('a1')
                ->join('customs_elec_goodspricebenk a2', 'a1.code_value=a2.district_code', 'left')
                ->field(['a1.code_value', 'a2.*'])
                ->paginate(10, true,
                    [
                        'query' => ['s' => 'admin/goods/intelligentNuclearPrice'],
                        'var_page' => 'page',
                        'type' => 'Layui',
                        'newstyle' => true
                    ]);
        }

        return view('CustomsSystem/goods/intelligent_nuclear_price',
            ['title' => '智能核价设置', 'page' => $list->render(), 'list' => $list->toArray()['data']]);
    }

    /**
     * 保存智能核价基准
     * @param Request $request tp
     * @return json
     */
    public function recoIntelligentNuclearPrice(Request $request)
    {

        $data = $request->post();
        try {
            $result = Db::name('customs_elec_goodspricebenk')->where('district_code', $data['code'])->find();
            if (empty($result)) {
                Db::name('customs_elec_goodspricebenk')->insert([
                    'district_code' => $data['code'],
                    'datum' => $data['datum'],
                    'up_floating' => $data['up_floating'],
                    'low_floating' => $data['low_floating']
                ]);
            } else {
                Db::name('customs_elec_goodspricebenk')->where('district_code', $data['code'])->update([
                    'datum' => $data['datum'],
                    'up_floating' => $data['up_floating'],
                    'low_floating' => $data['low_floating']
                ]);
            }
        } catch (\Exception $exception) {
            return json(['code' => -1, 'message' => '失败']);
        }

        return json(['code' => 0, 'message' => '完成']);
    }
    
    
    /**
     * 人工核价
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     * @throws \think\exception\DbException
     */
    public function artificialPrice(Request $request)
    {
        if ($request->get('search') != '') {
            $where = $request->get('search');
            $list = Db::name('customs_elec_goodsprice_verif')
                ->where('epb_goodsno', $where)
                ->where('status', 0)
                ->whereOr('user_goodsno', $where)
                ->whereOr('goods_name', $where)
                ->paginate(10, true,
                    [
                        'query' => ['s' => 'admin/goods/artificialPrice'],
                        'var_page' => 'page',
                        'type' => 'Layui',
                        'newstyle' => true
                    ]);
        } else {
            $count = Db::name('customs_elec_goodsprice_verif')->where('status', 0)->count();
            $list = Db::name('customs_elec_goodsprice_verif')->where('status', 0)->paginate(10, $count,
                [
                    'query' => ['s' => 'admin/goods/artificialPrice'],
                    'var_page' => 'page',
                    'type' => 'Layui',
                    'newstyle' => true
                ]);
        }

        return view('CustomsSystem/goods/artificial_price', [
            'title' => '商品价格人工核价',
            'list' => $list->toArray()['data'],
            'page' => $list->render()
        ]);
    }

    /**
     * 商品价格确认（人工核价处理）
     * @param Request $request
     * @return json
     */
    public function goodsNuclearPriceHandle(Request $request)
    {
        $goodsno = $request->get('goodsno');
        if ($goodsno == '') {
            return json(['code' => -1, 'message' => '参数错误!']);
        }
        try {
            $goodsLogic = model('GoodsLogic', 'logic');
            $goodsLogic->goodsPriceConfirm($goodsno);
        } catch (\Exception $e) {
            return json(['code' => -1, 'message' => $e->getMessage()]);
        }

        return json(['code' => 0, 'message' => '已处理']);
    }
    
    /**
     * 商品价格不通过（人工核价处理）
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function goodsNuclearPriceFailHandle(Request $request)
    {
        if ($request->get('goodsno') == '') {
            return json(['code' => -1, 'message' => '参数错误!']);
        }
        Db::name('customs_elec_goodsprice_verif')->where('user_goodsno',
            trim($request->get('goodsno')))->update(['status' => 2]);
        return json(['code' => 0, 'message' => '已处理']);
    }


    /**
     * 上传文件批量处理商品失败核价
     * @param Request $request
     * @return mixed
     */
    public function uploadPriceFailGoodsFile(Request $request)
    {
        try {
            $goodsLogic = model('GoodsLogic', 'logic');
            $goodsLogic->batchPriceConfir($request->file('file'));
        } catch (\Exception $e) {
            return json(['code' => -1, 'message' => $e->getMessage()]);
        }
        return json(['code' => 0, 'message' => '完成']);
    }

    // 添加二次用户页面  2019-05-10
    public function add()
    {
        $id = (int)input('get.id');
        return view('CustomsSystem/merchant/add', [
            'pid' => $id,
        ]);
    }

    // 添加操作
    public function doAdd(Request $request)
    {
        $data = $request->post();
        if ($data['user_name'] == '') {
            return ['code' => 0, 'msg' => '用户名不能为空'];
        }
        if ($data['user_tel'] == '') {
            return ['code' => 0, 'msg' => '用户手机号码不能为空'];
        }
        if ($data['user_email'] == '') {
            $data['user_email'] = $data['user_tel'] . '@qq.com';
        }
        if ($data['idfee'] == '') {
            return ['code' => 0, 'msg' => '身份验证费率不能为空'];
        }
        if ($data['sbfee'] == '') {
            return ['code' => 0, 'msg' => '申报费率不能为空'];
        }
        if ($data['payfee'] == '') {
            return ['code' => 0, 'msg' => '支付费率不能为空'];
        }
        if ($data['payhfee'] == '') {
            return ['code' => 0, 'msg' => '支付折扣不能为空'];
        }

        $data['user_password'] = password_hash($data['user_tel'], PASSWORD_DEFAULT);
        $data['openid'] = '';
        $data['uniacid'] = 3;
        $data['plat_id'] = 1;
        $data['remark'] = '';
        $data['created_at'] = date('Y-m-d H:i:s', time());
        $data['updated_at'] = '';
        $data['last_login_time'] = '';
        $data['last_login_ip'] = '';
        $data['user_status'] = 0;
        $data['start_up'] = 1;
        $data['user_num'] = md5(mt_rand(1166584321, time()));
        $data['bus_type'] = '';
        $data['buss_id'] = '3';
        $data['company_name'] = $data['user_name'];
        $data['company_num'] = 3200;
        $data['supplier'] = 0;

        // 查询手机号是否存在
        $isTel = Db::name('decl_user')->where(['user_tel' => $data['user_tel']])->find();
        if ($isTel) {
            return ['code' => 0, 'msg' => '用户手机号已存在，请检查'];
        }
        // 写入数据
        $ins = Db::name('decl_user')->insertGetId($data);
        if ($ins) {
            Db::name('decl_user_role')->insert(['role_id' => 32, 'user_id' => $ins]);
            return ['code' => 1, 'msg' => '数据保存成功'];
        }
        return ['code' => 0, 'msg' => '数据保存失败'];
    }

    // 查看我的二次对账商户
    public function show()
    {
        $pid = (int)input('get.pid');
        // 查询数据
        $data = Db::name('decl_user')->where(['parent_id' => $pid, 'company_num' => 3200])->select();
        // 渲染视图
        return view('CustomsSystem/merchant/show', [
            'data' => $data,
        ]);
    }

    // 编辑用户信息
    public function edit()
    {
        $id = (int)input('get.id');
        // 查询数据
        $data = Db::name('decl_user')->where(['id' => $id])->find();
        // 渲染视图
        return view('CustomsSystem/merchant/edit', [
            'data' => $data,
        ]);
    }

    // 添加操作
    public function doEdit(Request $request)
    {
        $data = $request->post();
        if ($data['user_name'] == '') {
            return ['code' => 0, 'msg' => '用户名不能为空'];
        }
        if ($data['idfee'] == '') {
            return ['code' => 0, 'msg' => '身份验证费率不能为空'];
        }
        if ($data['sbfee'] == '') {
            return ['code' => 0, 'msg' => '申报费率不能为空'];
        }
        if ($data['payfee'] == '') {
            return ['code' => 0, 'msg' => '支付费率不能为空'];
        }
        if ($data['payhfee'] == '') {
            return ['code' => 0, 'msg' => '支付折扣不能为空'];
        }

        $id = $data['id'];

        // 写入数据
        $ins = Db::name('decl_user')->where(['id' => $id])->update($data);
        if ($ins) {
            return ['code' => 1, 'msg' => '数据保存成功'];
        }
        return ['code' => 0, 'msg' => '数据保存失败'];
    }


    // 删除数据
    public function doDel()
    {
        $id = (int)input('post.id');
        $del = Db::name('decl_user')->where(['id' => $id])->delete();
        $delr = Db::name('decl_user_role')->where(['user_id' => $id])->delete();
        if (!$del && !$delr) {
            return ['code' => 0, 'msg' => '数据删除失败'];
        }
        return ['code' => 1, 'msg' => '数据删除成功'];
    }

    //变更申报
    public function orderDeclChangeIndex(Request $request)
    {

        $title = '申报变更管理';
        return view('CustomsSystem/order/order_change_index', compact('title'));
    }

    //获取需要变更申报的订单列表
    public function getOrderList(Request $request)
    {
        $where = null;
        if ($request->get('orderNo') != '') {
            $ono = trim($request->get('orderNo'));
            $where = "EntOrderNo ='" . $ono . "' or batch_num='" . $ono . "'";
        }

        if ($request->get('status') != '') {
            if ($where != null) {
                $where .= " and expro_status=" . $request->get('status');
            } else {
                $where = " expro_status=" . $request->get('status');
            }
        }
        if ($where == null) {
            $where = "expro_status =1";
        }
        $offse = ($request->get('page') - 1) * $request->get('limit');
        $total = Db::name('customs_elec_order_detail')->where($where)->count();
        $data = Db::name('customs_elec_order_detail')
            ->field([
                'id',
                'EntOrderNo',
                'EntOrderNo',
                'WaybillNo',
                'OrderGoodTotalCurr',
                'OrderGoodTotal',
                'packageType',
                'OrderDocName',
                'OrderDocId',
                'RecipientTel',
                'Province',
                'city',
                'county',
                'RecipientAddr',
                'RecipientCountry',
                'RecipientProvincesCode',
                'grossWeight',
                'weight',
                'RecipientAddr',
                'batch_num'
            ])
            ->where($where)
            ->limit($offse, $request->get('limit'))
            ->select();
        if (!empty($data)) {
            foreach ($data as &$val) {
                $userId = Db::name('customs_batch')->where('batch_num', $val['batch_num'])->field(['uid'])->find();
                $user = Db::name('decl_user')->where('id', $userId['uid'])->field([
                    'user_name',
                    'company_name'
                ])->find();
                $val['user_name'] = $user['user_name'];
                $val['company_name'] = $user['company_name'];
            }
        }

        return json(["status" => 0, "message" => "", "total" => $total, "data" => $data]);
    }

    //请求海关申报变更
    public function orderDeclChangeHandle(Request $request)
    {
        $orderLogic = model('CustomsOrderLogic', 'logic');
        return $orderLogic->orderElecChange($request->post('id'));
    }
    
    /**
     *列出抓取的其他电商平台链接
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goodsOtherPlatformLinkList(Request $request)
    {
        $title = '商品链接选择';
        $list = \app\admin\model\OtherGoodsDataModel::where('ebp_goods_no', $request->get('oid'))->select();
        return view('CustomsSystem/goods/goods_other_platformlink_list', compact('list', 'title'));
    }
    
    
    /**
     * 保存选择链接并插入抓取任务
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function saveGoodsLink(Request $request)
    {
        $data = $request->post();
        $udata = [];
        if (empty($data['goodsn'])) {
            return json(['code' => -1, 'message' => '参数错误']);
        }
        if (empty($data['url'])) {
            return json(['code' => -1, 'message' => '参数错误']);
        }
        if (empty($data['price'])) {
            return json(['code' => -1, 'message' => '参数错误']);
        }
        $goodsRes = \app\admin\model\GoodsModel::where('goodssn', $data['goodsn'])->field([
            'price',
            'goods_name'
        ])->find();
        if ((int)$goodsRes['price'] == 0) {
            $udata['price'] = $data['price'];
        }
        $udata['third_party_url'] = $data['url'];
        $udata['status'] = 0;
        \app\admin\model\GoodsModel::where('goodssn', $data['goodsn'])->update($udata);
        $mgo = MongoDB::init(Config::load(APP_PATH . 'config/config.php')['order_mongo_dsn']);
        $bulk = new \MongoDB\Driver\BulkWrite();
        $bulk->insert([
            '_id' => new \MongoDB\BSON\ObjectID,
            'goodssn' => $data['goodsn'],
            'url' => $data['url'],
            'good_name' => $goodsRes['goods_name']
        ]);
        try {
            $writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000);
            $mgo->executeBulkWrite('order.image', $bulk, $writeConcern);
        } catch (\MongoDB\Driver\Exception\BulkWriteException $e) {
            if ($writeConcernError = $e->getWriteConcernError()) {
                return json([
                    'code' => -1,
                    'message' => printf("%s (%d): %s\n", $writeConcernError->getMessage(),
                        $writeConcernError->getCode(), var_export($writeConcernError->getInfo(), true))
                ]);
            }
        }
        return json(['code' => 0, 'message' => '完成']);
    }
    
    
    /**
     * 电话地址归属地白名单管理
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\Json|\think\response\View
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function phoneAddressattributionWhitelist(Request $request)
    {
        $action = $request->get('a');
        switch ($action){
            case "update":
                $id = trim($request->get('id'),',');
                Db::name('customs_phone_addressattribution_whitelist')
                    ->where('id','in',$id)
                    ->update(['status'=>$request->get('status')]);
                return json(['code'=>0,'msg'=>'更新完成']);
            case "delete":
                Db::name('customs_phone_addressattribution_whitelist')->where('id',$request->get('id'))->delete();
                return json(['code'=>0,'msg'=>'已删除']);
            case "list":
                $limit = $request->get('limit');
                $page = $request->get('page');
                $page = ($page-1)*$limit;
                $where = [];
                if ($request->get('phone')!=""){
                    $where['phone'] =trim($request->get('phone'));
                }
                if ($request->get('status')!=""){
                    $where['status'] =trim($request->get('status'));
                }
                $count = Db::name('customs_phone_addressattribution_whitelist')->where($where)->count();
                $data = Db::name('customs_phone_addressattribution_whitelist')->where($where)->order('id','desc')->limit($page,$limit)->select();
                return json(["code" => 0, "msg" => "完成", "count" => $count, "data" => $data]);
            default:
                return view('CustomsSystem/phone_address_attribution_whitelist',['title'=>'电话地址归属地白名单管理']);
        }
    }

    /**
     * 国内结算费率设置
     */
    public function settlementRate(Request $request){
        $dat = input();
        if($request->isAjax()){
            $rate_info = json_encode([
                'industry'=>$dat['data']['industry'],
                'bankcard_rate'=>trim($dat['data']['bankcard_rate']),
                'creditcard_rate'=>trim($dat['data']['creditcard_rate']),
                'rate_limit'=>trim($dat['data']['rate_limit']),
            ]);
            $res = Db::name('decl_user')->where(['id'=>$dat['uid']])->update(['rate_info'=>$rate_info]);
            if($res){
                return json(['code'=>1,'message'=>'设置成功']);
            }else{
                return json(['code'=>1,'message'=>'系统错误']);
            }
        }else{
            $user = Db::name('decl_user')->where(['id'=>$dat['id'],'openid'=>$dat['openid']])->field(['rate_info,id'])->find();
            $user['rate_info'] = json_decode($user['rate_info'],true);
            return view('CustomsSystem/user/set_rate',compact('user'));
        }
    }

    /**
     * 配置离岸手续费
     */
    public function setOffshoreRate(Request $request){
        $dat = input();
        if($request->isAjax()){
            $dat2 = $dat['data'];
            $data = [];
            //离岸收款
            $data['offshore_entry_info'] = json_encode([
                'entry_type'=>$dat2['entry_type'],
                'currency'=>$dat2['currency1'],
                'money'=>$dat2['money1'],
                'trade_rate'=>$dat2['trade_rate1'],
                'trade_low_money'=>$dat2['trade_low_money1'],
            ]);
            //离岸转账
            $data['offshore_transfer_info'] = json_encode([
                'entry_type'=>$dat2['entry_type2'],
                'currency'=>$dat2['currency2'],
                'money'=>$dat2['money2'],
                'trade_rate'=>$dat2['trade_rate2'],
                'trade_low_money'=>$dat2['trade_low_money2'],
            ]);
            //离岸换汇
            $data['offshore_exchange_info'] = json_encode([
                'entry_type'=>$dat2['entry_type3'],
                'currency'=>$dat2['currency3'],
                'money'=>$dat2['money3'],
                'trade_rate'=>$dat2['trade_rate3'],
                'trade_low_money'=>$dat2['trade_low_money3'],
            ]);
            //在岸结汇
            $data['onshore_exchange_info'] = json_encode([
                'entry_type'=>$dat2['entry_type4'],
                'currency'=>$dat2['currency4'],
                'money'=>$dat2['money4'],
                'trade_rate'=>$dat2['trade_rate4'],
                'trade_low_money'=>$dat2['trade_low_money4'],
            ]);
            $res = Db::name('decl_user')->where('id',$dat['uid'])->update($data);
            if($res){
                return json(['code'=>1,'message'=>'设置成功']);
            }
        }else{
            $user = Db::name('decl_user')->where(['id'=>$dat['id'],'openid'=>$dat['openid']])->field(['offshore_entry_info,id,offshore_transfer_info','offshore_exchange_info','onshore_exchange_info'])->find();
            $user['offshore_entry_info'] = json_decode($user['offshore_entry_info'],true);
            $user['offshore_transfer_info'] = json_decode($user['offshore_transfer_info'],true);
            $user['offshore_exchange_info'] = json_decode($user['offshore_exchange_info'],true);
            $user['onshore_exchange_info'] = json_decode($user['onshore_exchange_info'],true);
            $currency = Db::name('currency')->select();
            return view('CustomsSystem/user/set_offshore_rate',compact('user','currency'));
        }
    }

    //配置商户的会计
    public function setAccount(Request $request){
        $dat = input();
        if($request->isAjax()){
            $data['accounting_id'] = $dat['data']['accounting_id'];
            $res = Db::name('decl_user')->where('id',$dat['uid'])->update($data);
            if($res){
                return json(['code'=>1,'message'=>'设置成功']);
            }
        }else{
            $user = Db::name('decl_user')->where(['id'=>$dat['id'],'openid'=>$dat['openid']])->field(['accounting_id','id'])->find();
            $accounting = Db::name('mc_mapping_fans')->where('is_accounting',1)->where('uniacid',3)->select();
            return view('CustomsSystem/user/set_account',compact('accounting','user'));
        }
    }

    //配置商户的拖车信息
    public function setFreight(Request $request){
        $dat = input();
        if($request->isAjax()){
            $data['freight_info'] = json_encode(['freight_price'=>$dat['data']['freight_price']]);
            $res = Db::name('decl_user')->where('id',$dat['uid'])->update($data);
            if($res){
                return json(['code'=>1,'message'=>'设置成功']);
            }
        }else{
            $user = Db::name('decl_user')->where(['id'=>$dat['id'],'openid'=>$dat['openid']])->field(['freight_info','id'])->find();
            $user['freight_info'] = json_decode($user['freight_info'],true);
            return view('CustomsSystem/user/set_freight',compact('user'));
        }
    }

    //配置商户的系统应用权限
    public function setSysApp(Request $request){
        $dat = input();
        if($request->isAjax()){
            $res = Db::name('decl_user')->where('id',$dat['uid'])->update(['sys_app'=>$dat['data']['device']]);
            if($res){
                return json(['code'=>1,'message'=>'设置成功']);
            }
        }else{
            $user = Db::name('decl_user')->where(['id'=>$dat['id'],'openid'=>$dat['openid']])->field(['sys_app','id'])->find();
            $app = Db::name('site_nav')->where(['position'=>1,'status'=>1,'uniacid'=>3,'multiid'=>45])->order('displayorder','desc')->order('id','desc')->select();
            $app = json_encode($app,true);
            return view('CustomsSystem/user/set_sysApp',compact('user','app'));
        }
    }

    //配置商户的用户,废弃
    public function setUser(Request $request){
        $dat = input();
        if($request->isAjax()){
            $sys_user = [$dat['data']['num1'].','.$dat['data']['msg1']];
            array_push($sys_user,$dat['data']['num2'].','.$dat['data']['msg2']);
            array_push($sys_user,$dat['data']['num3'].','.$dat['data']['msg3']);
            $res = Db::name('decl_user')->where(['id'=>$dat['uid']])->update(['sys_user'=>json_encode($sys_user,true)]);
            if($res){
                return json(['code'=>1,'message'=>'设置成功']);
            }
        }else{
            $user = Db::name('decl_user')->where(['id'=>$dat['id'],'openid'=>$dat['openid']])->field(['sys_user','id'])->find();
            $user['sys_user'] = json_decode($user['sys_user'],true);
            foreach($user['sys_user'] as $k=>$v){
                $user['sys_user'][$k] = explode(',',$v);
            }

            return view('CustomsSystem/user/set_sysUser',compact('user'));
        }
    }

    //商户用户配置
    public function sysuser_list(Request $request){
        $dat = input();
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $total = Db::name('merchant_job_config')->count();
            $data = Db::name('merchant_job_config')->limit($page, $limit)->select();
            foreach($data as $k=>$v){
                if($v['msg_type']==1){
                    $data[$k]['msg_type'] = '是';
                }elseif($v['msg_type']==2){
                    $data[$k]['msg_type'] = '否';
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page,  'rows' => $data]);
        }else{
            return view('CustomsSystem/user/sysuser_list',compact('user'));
        }
    }
    public function sysuser_save(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()){
            if(empty($dat['id'])){
                //新增
                $res = Db::name('merchant_job_config')->insert(['name'=>trim($dat['name']),'msg_type'=>intval($dat['msg_type']),'num'=>intval($dat['num'])]);
                if($res){
                    return json(['code' => 1, 'msg' => '新增成功！']);
                }
            }else{
                //修改
                $res = Db::name('merchant_job_config')->where('id',$dat['id'])->update(['name'=>trim($dat['name']),'msg_type'=>intval($dat['msg_type']),'num'=>intval($dat['num'])]);
                if($res){
                    return json(['code' => 1, 'msg' => '修改成功！']);
                }
            }
        }else{
            if(empty($dat)){
                $data['name'] = '';
                $data['msg_type'] = '';
                $data['num'] = '';
                $data['id'] = '';
            }else{
                $data = Db::name('merchant_job_config')->where('id',$dat['id'])->find();
            }
            return view('CustomsSystem/user/sysuser_save',compact('data'));
        }
    }

    //手动添加商户（openid在填写手机号时获取）
    public function add_merchant(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()){
            $time = time();
            $basic_info_data = $dat['basic_info'];
            $enterprise_members_id = Db::name('enterprise_members')->insertGetId([
                'unionid'=>'',
                'uniacid'=>'',
                'uid'=>'',
                'openid'=>'',
                'nickname'=>'',
                'mobile'=>trim($dat['mobile']),
                'idcard'=>trim($dat['idcard']),
                'avatar'=>'',
                'create_at'=>$time,
                'reg_type' => trim($dat['reg_type']),
                'realname' => trim($dat['realname']),
                'authType' => implode(',',$dat['authType']),//可读、可写
            ]);
            if(intval($dat['reg_type'])==1){
                //企业注册
                $basic_info = Db::name('enterprise_basicinfo')->where(['member_id'=>$enterprise_members_id,'enterprise_id'=>$dat['basic_info']['enterprise_id']])->limit(1)->find();

                if($basic_info)
                {
                    Db::name('enterprise_basicinfo')->where(['id'=>$basic_info['id']])->update($dat['basic_info']);
                }else{
                    $dat['basic_info']['create_at'] = $time;
                    Db::name('enterprise_basicinfo')->insert($dat['basic_info']);
                }

                // 实际受益人
                $beneficiaries_info = Db::name('enterprise_beneficiaries')->where(['info_id'=>$dat['basic_info']['enterprise_id']])->limit(1)->find();
                $beneficiaries_data = $dat['beneficiaries'];
                if($beneficiaries_info)
                {
                    foreach ($beneficiaries_data as $k => $v) {
                        Db::name('enterprise_beneficiaries')->where(['id' => $beneficiaries_info['id']])->update($beneficiaries_data[$k]);
                    }

                }else{
                    foreach ($beneficiaries_data as $k => $v) {
                        $beneficiaries_data[$k]['info_id'] = $basic_info_data['enterprise_id'];
                        $beneficiaries_data[$k]['create_at'] = $time;
                        Db::name('enterprise_beneficiaries')->insert($beneficiaries_data[$k]);
                    }

                }

                // 纳税人类型
                $taxpayerlist_info = Db::name('enterprise_taxpayerlist')->where(['info_id'=>$basic_info_data['enterprise_id']])->limit(1)->find();
                $taxpayerlist_data = $dat['taxpayerlist'];
                if($taxpayerlist_info)
                {
                    foreach ($taxpayerlist_data as $k => $v) {
                        Db::name('enterprise_taxpayerlist')->where(['id' => $taxpayerlist_info['id']])->update($taxpayerlist_data[$k]);
                    }

                }else{
                    foreach ($taxpayerlist_data as $k => $v) {
                        $taxpayerlist_data[$k]['info_id'] = $basic_info_data['enterprise_id'];
                        $taxpayerlist_data[$k]['name'] = $basic_info_data['name'];
                        $taxpayerlist_data[$k]['create_at'] = $time;
                        Db::name('enterprise_taxpayerlist')->insert($taxpayerlist_data[$k]);
                    }
                }

                // 海关登记
                $customslist_info = Db::name('enterprise_customslist')->where(['info_id'=>$basic_info_data['enterprise_id']])->limit(1)->find();
                $customslist_data = $dat['customslist'];
                if($customslist_info)
                {
                    foreach ($customslist_data as $k => $v) {
                        Db::name('enterprise_customslist')->where(['id' => $customslist_info['id']])->update($customslist_data[$k]);
                    }

                }else{
                    foreach ($customslist_data as $k => $v) {
                        $customslist_data[$k]['info_id'] = $basic_info_data['enterprise_id'];
                        $customslist_data[$k]['create_at'] = $time;
                        Db::name('enterprise_customslist')->insert($customslist_data[$k]);
                    }
                }
            }
            else{
                //个人注册

            }


            // 查询是否已经注册,商户账户合计表
            $reg = Db::name('total_merchant_account')->where(['mobile'=>trim($dat['mobile'])])->limit(1)->find();
            if( $reg )
            {
                return json(['code' => 1, 'msg' => '该账号已经注册！']);
            }else{
                $basic_info = Db::name('enterprise_basicinfo')->where(['member_id'=>$enterprise_members_id])->limit(1)->find();
                if(empty($basic_info['operName'])){
                    $basic_info['operName'] = trim($dat['realname']);
                }
                $user_data = [
                    'unique_id'=>md5((trim($dat['mobile']).date('YmdHis'))),
                    'mobile'=>trim($dat['mobile']),
                    'password'=>password_hash(trim($dat['mobile']), PASSWORD_DEFAULT),
                    'uniacid' => '',//登录时自动获取
                    'user_name' => $basic_info['operName'],
                    'company_name' =>$basic_info['name'],
                    'create_time'=>$time,
                    'desc' =>'',
                    'status'=>1,
                    'user_email'=>'',
                    'address'=>$basic_info['address'],
                    'company_tel'=>'',
                    'account_type'=>2,
                    'openid' => '',//登录时自动获取
                    'enterprise_id' => $basic_info['member_id']
                ];
                Db::name('total_merchant_account')->insert($user_data);
                return json(['code' => 1, 'msg' => '新增商户注册成功！']);
            }
        }else{
            return view('CustomsSystem/user/add_merchant',compact(''));
        }
    }

    //公众号可用应用配置
    public function can_see_app(Request $request){
        $dat = input();

        if ( request()->isPost() || request()->isAjax()){
            $res = Db::name('offical_account_application')->where('id',1)->update(['can_add_app'=>$dat['data']['device']]);
            if($res){
                return json(['code'=>1,'message'=>'设置成功']);
            }
        }else{
            //获取公众号应用
            $app = Db::name('site_nav')->where(['position'=>1,'status'=>1,'uniacid'=>3,'multiid'=>45])->order('displayorder', 'desc')->order('id', 'desc')->select();
            $app = json_encode($app,true);

            //获取已设置应用
            $data = Db::name('offical_account_application')->where(['id'=>1])->find();
            return view('CustomsSystem/user/can_see_app',compact('data','app'));
        }
    }

    /**
     * 商品上架
     *
     * @param Request $request
     * @return view
     */
    // public function goodsShelf(Request $request)
    // {
    //     $count = Db::name('foll_goodsreglist')->count('id');
    //     $result = Db::name('foll_goodsreglist')
    //     ->field(['id','EntGoodsNo','HSCode','GoodsName','GoodsStyle','Brand','GUnit','RegPrice','OriginCountry'])
    //     ->paginate(10, $count,[
    //         'query' => ['s' => 'admin/goods/shelf_index'],
    //         'var_page' => 'page',
    //         'type' => 'Layui',
    //         'newstyle' => true
    //     ]);
    //     $page = $result->render();
    //     $list = $result->toArray()['data'];
    //     $title = '商品上架列表';
    //     return view('CustomsSystem/goods/goods_shelf_index',compact('page','list','title'));
    // }
}
