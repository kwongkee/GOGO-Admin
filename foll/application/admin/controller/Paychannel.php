<?php

namespace app\admin\controller;
use think\Db;
use think\Request;

class Paychannel extends Auth {

    // 支付配置
    public function index(Request $req) {

        // 获取商户号列表
        $merList = Db::name('customs_pay_mer')->field('id,apply')->select();
        $merArr = [];
        foreach ($merList as $mv){
            $merArr[$mv['id']] = $mv['apply'];
        }


        $this->assign('merArr',$merArr);
        // 获取申报平台用户表的ID
        $id = $req->get('id');

        $cname = $req->get('cname');
        // 查询数据

        $info['id']    = $id;
        $info['cname'] = $cname;

        $list = Db::name('customs_pay_channel')->where(['customId'=>$id])->select();

        $this->assign('list', $list);
        $this->assign('info', $info);

        return view('paychannel/index',
            ['title'=>'']
        );
    }

    // 添加页面
    public function add(Request $req) {


        /**
         * 思路：
         *  1、绑定用户ID；
         *  2、渲染支付渠道列表
         *  3、根据选择的支付渠道获取商户号列表；
         *  4、选择商户号添加时，先判断是否存在该支付通道；存在则不能添加；可进行修改；
         */

        // 获取用户姓名、ID
        $info = $req->get();

        $this->assign('info',$info);

        // 获取支付企业列表
        $paylist = Db::name('customs_pay_list')->select();

        $this->assign('paylist',$paylist);

        return view('paychannel/add',
            ['title'=>'']
        );
    }

    // 添加通道操作
    public function doadd_back(Request $req){
        $data = $req->post();
        if(empty($data)) {
            return json_encode(['code'=>0,'msg'=>'数据不能为空']);
        }
        // 查看用户该通道是否已经添加，添加过的不允许添加
        $isadd = Db::name('customs_pay_channel')->where(['customId'=>$data['customId'],'payChannel'=>$data['payChannel']])->find();
        if(!empty($isadd)) {
            return json_encode(['code'=>0,'msg'=>$data['payName'].'支付通道已添加，请勿重复添加！']);
        }

        $data['atime'] = time();
        // 添加数据
        $ins = Db::name('customs_pay_channel')->insert($data);
        if(!$ins) {
            return json_encode(['code'=>0,'msg'=>'支付通道添加失败，请稍后重试！']);
        }

        // 查看商户下面是否有操作员，有就循环添加操作员的信息；
        $caoz = Db::name('decl_user')->where(['parent_id'=>$data['customId']])->field('id,user_name')->select();
        if(!empty($caoz)) { // 有操作员，给操作员添加上

            foreach($caoz as $v) {
                $tmp = [];
                $tmp['payChannel']  = $data['payChannel'];
                $tmp['payName']     = $data['payName'];
                $tmp['merchatId']   = $data['merchatId'];
                $tmp['merchatKey']  = $data['merchatKey'];
                $tmp['recpAccount']  = $data['recpAccount'];
                $tmp['recpCode']     = $data['recpCode'];
                $tmp['recpName']     = $data['recpName'];

                $tmp['customId'] = $v['id'];
                $tmp['customName'] = $v['user_name'];
                $tmp['atime'] = time();
                Db::name('customs_pay_channel')->insert($tmp);
            }
        }

        return json_encode(['code'=>1,'msg'=>'支付通道添加成功']);
    }

    // 添加操作 2020-03-10
    public function doadd(Request $req) {
        /**
         * $req->post();
         * 1、用户id,用户名称、选择的支付渠道，选择的商户号列表id;
         * 2、先判断该用户所属的支付渠道是否已添加，是：不能重复添加；
         * 否： 根据商户列表ID;查询数据，并记录；
         * 3、组装数据写入数据库；
         */

        $customsId      = (int)$req->post('customId'); // 商户UID
        $customName     = $req->post('customName');    // 商户姓名
        $payChannelid   = (int)$req->post('payChannelid');// 支付渠道ID
        $mid            = (int)$req->post('mid');              // 商户列表ID；

        $isTrue = Db::name('customs_pay_channel')->where(['customId'=>$customsId,'payId'=>$payChannelid])->find();
        if($isTrue) {
            return json(['code'=>0,'msg'=>'该支付渠道已配置，请勿重复配置！']);
        }
        // 支付渠道信息
        $payList = $this->getPaylist($payChannelid);
        // 商户号渠道信息
        $MerList = $this->getMerinfo($mid);

        // 组装数据写入；
        $insData = [];
        $insData['customId']   = $customsId;
        $insData['customName'] = $customName;
        $insData['payChannel'] = $payList['PayType'];
        $insData['payName']    = $payList['PayEntName'];

        $insData['merchatId']   = $MerList['merchatId'];
        $insData['merchatKey']  = $MerList['merchatKey'];

        $insData['sysAccount']    = $MerList['sysAccount'];
        $insData['sysPartnerId']  = $MerList['sysPartnerId'];
        $insData['sysName']       = $MerList['sysName'];


        $insData['recpAccount'] = $MerList['recpAccount'];
        $insData['recpCode']    = $MerList['recpCode'];
        $insData['recpName']    = $MerList['recpName'];


        $insData['payId']    = $payChannelid;
        $insData['merId']    = $mid;
        $insData['atime']    = time();
        $insData['utime']    = time();

        // 写入记录
        $ins = Db::name('customs_pay_channel')->insert($insData);
        if(!$ins) {
            return json(['code'=>0,'msg'=>'新增配置失败，请稍后重试!']);
        }

        return json(['code'=>1,'msg'=>'success']);
    }


    // 编辑
    public function edit(Request $req) {

        // 获取支付企业列表
        $paylist = Db::name('customs_pay_list')->field('id,PayEntName')->select();
        $this->assign('paylist',$paylist);


        $id = $req->get('id');
        // 获取该通道配置
        $info = Db::name('customs_pay_channel')->where('id',$id)->find();
        $this->assign('info',$info);

        // 商户号列表  用户通道列表：channel-> payId = list->id;  channel->merId = mer->id;
        $merlist = Db::name('customs_pay_mer')->where('pid',$info['payId'])->field('id,apply')->select();
        // 渲染商户列表
        $this->assign('merlist',$merlist);


        return view('paychannel/edit',
            ['title'=>'']
        );

    }


    /**
     *  编辑操作
     *  2020-03-10
     */
    public function doedit(Request $req) {

        $id             = intval($req->post('id'));
        $omid           = intval($req->post('omid'));
        //$payChannelid   = intval($req->post('payChannelid'));
        $mid            = intval($req->post('mid'));

        if($omid == $mid) {
            return json(['code'=>0,'msg'=>'您未做修改，无需提交！',$omid,$mid]);
        }

        // 获取商户号列表部分，进行更新；
        $merList = $this->getMerinfo($mid);
        $merList['merId'] = $mid;
        $merList['utime'] = time();
        $up = Db::name('customs_pay_channel')->where('id',$id)->update($merList);
        if(!$up) {
            return json(['code'=>0,'msg'=>'更新失败，请稍后重试！']);
        }

        return json(['code'=>1,'msg'=>'更新成功']);
    }

    // 编辑操作
    public function doedit_back(Request $request) {
        $data = $request->post();
        if(empty($data)) {
            return json_encode(['code'=>0,'msg'=>'数据不能为空']);
        }
        $id = $data['id'];
        unset($data['id']);
        $data['utime'] = time();
        $up = Db::name('customs_pay_channel')->where('id',$id)->update($data);
        if(!$up) {
            return json_encode(['code'=>0,'msg'=>'支付通道编辑失败，请稍后重试！']);
        }
        // 查看商户下面是否有操作员，有就循环编辑操作员的信息；
        $caoz = Db::name('decl_user')->where(['parent_id'=>$data['customId']])->field('id,user_name')->select();
        if(!empty($caoz)) { // 有操作员，给操作员添加上

            foreach($caoz as $v) {
                $tmp = [];
                $tmp['merchatId']  = $data['merchatId'];
                $tmp['merchatKey'] = $data['merchatKey'];
                $tmp['recpAccount']  = $data['recpAccount'];
                $tmp['recpCode']     = $data['recpCode'];
                $tmp['recpName']     = $data['recpName'];

                $tmp['utime'] = time();
                Db::name('customs_pay_channel')->where(['id'=>$v['id']])->update($tmp);
            }
        }

        return json_encode(['code'=>1,'msg'=>'支付通道编辑成功']);
    }


    // 删除
    public function del(Request $req) {
        $id = $req->post('id');
        if(empty($id)) {
            return json_encode(['code'=>0,'msg'=>'数据不能为空']);
        }
        $del = Db::name('customs_pay_channel')->where(['id'=>$id])->delete();
        if(!$del) {
            return json_encode(['code'=>0,'msg'=>'删除失败，请稍后重试！']);
        }
        return json_encode(['code'=>1,'msg'=>'删除成功']);

    }


    /**
     * 2020-03-10
     * 仍一个接口出来，获取商户号列表；
     */
    public function getMer(Request $req) {
        $pid = $req->post('id');
        $mer = Db::name('customs_pay_mer')->where('pid',$pid)->select();
        if(empty($mer)) {
            return json(['code'=>0,'msg'=>'该支付渠道未配置商户号！']);
        }

        //$str = '<div class="layui-input-block"><input type="radio" name="mid" value="" lay-verify="required" autocomplete="off" class="layui-input">联通公司</div>';
        $str = '';
        foreach ($mer as $v){
            $str .= '<div class="layui-input-block"><input type="radio" name="mid" value="'.$v['id'].'" lay-verify="required" autocomplete="off" class="layui-input">'.$v['apply'].'</div>';
        }

        return json(['code'=>1,'msg'=>'success！','str'=>$str]);

    }


    /**
     * 获取支付列表数据
     */
    protected function getPaylist($pid) {
        $info = Db::name('customs_pay_list')->where('id',$pid)->field('PayEntName,PayType')->find();
        return $info;
    }

    /**
     * @param $pid
     * @return array|false|null|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 获取商户号列表配置；
     */
    protected function getMerinfo($mid) {
        $info = Db::name('customs_pay_mer')->where('id',$mid)->field('merchatId,merchatKey,recpAccount,recpCode,recpName,sysAccount,sysPartnerId,sysName')->find();
        return $info;
    }
}

?>