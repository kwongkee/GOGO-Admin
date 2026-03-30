<?php

namespace app\admin\controller;
use think\Db;
use think\Request;

class Additional extends Auth
{
    // 支付渠道列表
    public function index(Request $req) {

        $info = $req->get();
        $this->assign('info',$info);

        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'additional/index','id'=>$info['id']],
            'var_page'=>'page',
            'newstyle'=>true
        ];

        $list = Db::name('customs_additional')->where('uid',$info['id'])->order('date desc')->paginate(10,false,$config);
        $this->assign('list',$list);

        // 显示数据得不得；
       return view('additional/index');
    }

    // 渲染页面
    public function add(Request $req) {

        $uid = $req->get('uid');

        // 获取到的提单
        $ins = $this->getbol($uid);

        $this->assign('bol',$ins);
        $this->assign('uid',$uid);

        return view('additional/add');
    }

    // 渲染编辑页面
    public function edit(Request $req) {

        $id = $req->get('id');
        $uid = $req->get('uid');

        // 获取到的提单
        $ins = $this->getbol($uid);

        // 查询数据
        $infos = Db::name('customs_additional')->where('id',$id)->find();

        $this->assign('infos',$infos);
        $this->assign('bol',$ins);
        $this->assign('uid',$uid);

        return view('additional/edit');
    }


    // 添加操作
    public function doadd(Request $req) {

        // 接收新增
        $data = $req->post();

        $method   = $data['method'];

        $uid      = $data['uid'];
        $bill_num = $data['bill_num'];
        $date     = $data['date'];

        $newArr = [];

        // 添加操作
        if($method == 'add') {

            $num = count($data['project']);
            if($num == 1) {

                $i = 0;
                $newArr['project'] = $data['project'][$i];
                $newArr['money']   = $data['money'][$i];
                $newArr['dis']     = $data['dis'][$i];
                $newArr['desc']    = $data['desc'][$i];
                $newArr['uid']     = $uid;
                $newArr['bill_num']= $bill_num;
                $newArr['date']    = $date;
                $newArr['atime']   = strtotime($date);
                $newArr['addtime'] = time();

                // 写入表
                $ins = Db::name('customs_additional')->insert($newArr);

            } else{

                for($i=0;$i<$num;$i++) {
                    $newArr[$i]['project'] = $data['project'][$i];
                    $newArr[$i]['money']   = $data['money'][$i];
                    $newArr[$i]['dis']     = $data['dis'][$i];
                    $newArr[$i]['desc']    = $data['desc'][$i];
                    $newArr[$i]['uid']     = $uid;
                    $newArr[$i]['bill_num']= $bill_num;
                    $newArr[$i]['date']    = $date;
                    $newArr[$i]['atime']   = strtotime($date);
                    $newArr[$i]['addtime'] = time();
                }

                $ins = Db::name('customs_additional')->insertAll($newArr);
            }


            if(!$ins) {
                return json(['code'=>0,'msg'=>'写入操作失败']);
            }

            return json(['code'=>1,'msg'=>'写入操作成功']);

        } else {
            // 编辑操作
            $id = $data['id'];
            $upArr = [];
            $upArr['bill_num']  = $data['bill_num'];
            $upArr['date']      = $data['date'];
            $upArr['atime']     = strtotime($data['date']); // 修改日期
            $upArr['project']   = $data['project'][0];
            $upArr['money']     = $data['money'][0];
            $upArr['dis']       = $data['dis'][0];
            $upArr['desc']      = $data['desc'][0];

            $up = Db::name('customs_additional')->where('id',$id)->update($upArr);
            if(!$up) {
                return json(['code'=>0,'msg'=>'更新编辑失败']);
            }
            return json(['code'=>1,'msg'=>'更新编辑成功']);
        }

    }


    // 删除操作，已确认对账的不允许删除；
    public function del(Request $req) {
        $id = $req->post('id');
        $getInfo = Db::name('customs_additional')->where('id',$id)->find();
        if(!empty($getInfo) && ($getInfo['check'] != 1)) {
            // 执行删除操作
            $del = Db::name('customs_additional')->where('id',$id)->delete();
            if(!$del){
                return json(['code'=>0,'msg'=>'删除失败!']);
            }

            return json(['code'=>1,'msg'=>'删除成功']);
        }

        return json(['code'=>0,'msg'=>'删除失败,数据已对账不允许删除!']);
    }

    // 拼接HTML  用于前端获取；
    public function getadd(){

        $str = <<<EOF
<div class="layui-form-item">
            <label class="layui-form-label">项目名称</label>
            <div class="layui-input-block">
                <input type="text" value="" name="project[]" placeholder="请填写项目名称" lay-verify="required" autocomplete="off" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">金额</label>
            <div class="layui-input-block">
                <input type="text" name="money[]" value="" placeholder="请填写金额" required lay-verify="required" autocomplete="off" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">单位</label>
            <div class="layui-input-block">
                <input type="text" value="" name="dis[]" placeholder="请填写单位"  lay-verify="required" autocomplete="off" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">计费说明</label>
            <div class="layui-input-block">
                <input type="textarea" name="desc[]" placeholder="请填写计费说明" class="layui-input">
            </div>
        </div>
        
        <div class="layui-form-item">
            <div class="layui-input-block">
                <hr>
            </div>
        </div>
EOF;
        return json(['code'=>1,'msg'=>$str]);
    }


    // 获取用户提单 所属用户的提单；
    protected function getbill($cid) {

        // 根据用户ID查询数据

            // T2.user_name
            $sql = "SELECT T2.id,T2.parent_id FROM ( SELECT   @r AS _id,   @stop:=@stop+if(@r=2,1,@stop) as stop,(SELECT @r := parent_id FROM `ims_decl_user` WHERE id = _id) AS parent_id,@l := @l + 1 AS lvl  FROM (SELECT @r := {$cid}, @l := 0, @stop:=0) vars,  `ims_decl_user` h WHERE @stop < 1) T1 JOIN `ims_decl_user` T2 ON T1._id = T2.id"; //ORDER BY T1.lvl DESC

            $pid = '';
            $newArrs = [];
            $s =Db::query($sql);
            foreach ($s as $k => $v) {

                $newArrs[] = $v['id'];

                // 查找最高权限管理员
                /*if ($v['parent_id'] == 0) {// 顶级
                    $pid = $v['id'];
                } else {
                    if ($v['id'] != $cid) {// 不等于传入的
                        $pid = $v['id'];
                    }
                }*/
            }

            return $newArrs;
            //return $pid;
    }

    // 获取全部提单
    protected function getbol($uid){

        $userInfo = $this->getbill($uid);

        $bill = Db::name('decl_bol')->whereIN('user_id',$userInfo)->field('bill_num')->select();
        $tmpArr = [];
        if(empty($bill)) {
           return $tmpArr;
        }

        foreach ($bill as $v){
            $tmpArr[] = $v['bill_num'];
        }

        return $tmpArr;
    }

}