<?php

namespace app\admin\controller;
use think\Request;
use think\Db;
use Util\data\Sysdb;

class Bedalf extends Auth
{
    // 账户列表
    public function acc() {

        $this->db = new Sysdb;
        $bank = DB::name('customs_bankcode')->where('id','>',0)->select();

        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'admin/customs/acclist'],
            'var_page'=>'page',
            'newstyle'=>true
        ];

        $acclist = $this->db->table('customs_behalfacc')->where(['id'=>['gt',0]])->pages(9,$config);

        // 查询所有的账户列表
        //$acclist = DB::name('customs_behalfacc')->where('id','>',0)->paginate(10);

        $accType = [
            '卡','存折','对公账号'
        ];

        $this->assign('accType',$accType);
        $this->assign('bank',$bank);
        $this->assign('acclist',$acclist['lists']);
        $this->assign('pages',$acclist['pages']);

        return view('behalf/acc',['title'=>'账户列表']);

    }


    // 添加账户  2020-01-03
    public function add(Request $req) {

        $data = $req->post()['formData'];
        // 验证卡号
        if(!$this->luhm($data['cardNo'])) {
            return $this->jsons(['code'=>0,'msg'=>'银行卡号格式不正确，请检查！']);
        }

        // 查看卡号是否已经存在
        $isNull = DB::name('customs_behalfacc')->where('cardNo',$data['cardNo'])->find();
        if($isNull) {
            return $this->jsons(['code'=>0,'msg'=>'该银行卡号已存在，请勿重复添加']);
        }

        $data['a_time'] = time();
        // 执行写入操作；
        $ins = DB::name('customs_behalfacc')->insert($data);
        if(!$ins) {
            return $this->jsons(['code'=>0,'msg'=>'收款账户添加失败，请稍后重试！']);
        }

        return $this->jsons(['code'=>1,'msg'=>'收款账户添加成功！']);
    }


    // 删除账户
    public function del(Request $req) {

        $id = $req->post('id');
        if(empty($id)) {
            return $this->jsons(['code'=>0,'msg'=>'操作失败，请稍后重试！']);
        }

        $del = DB::name('customs_behalfacc')->where('id',$id)->delete();
        if(!$del){
            return $this->jsons(['code'=>0,'msg'=>'账户删除失败，请稍后重试！']);
        }
        return $this->jsons(['code'=>1,'msg'=>'账户删除成功！']);

    }

    // 编辑
    public function edit(Request $req) {
        $id = $req->get('id');
        // 获取数据
        $data = DB::name('customs_behalfacc')->where('id',$id)->find();

        $bank = DB::name('customs_bankcode')->where('id','>',0)->select();

        $this->assign('data',$data);
        $this->assign('bank',$bank);

        return view('behalf/edit');

    }

    // 执行编辑
    public function doEdit(Request $req) {

        $data = $req->post()['formData'];

        $id = trim($data['id']);

        if($data['cardNo'] != '') {
            // 验证卡号
            if(!$this->luhm($data['cardNo'])) {
                return $this->jsons(['code'=>0,'msg'=>'银行卡号格式不正确，请检查！']);
            }
            $upData['cardNo'] = trim($data['cardNo']);
        }

        if($data['accName'] != '') {
            $upData['accName'] = trim($data['accName']);
        }

        if($data['accType'] != '') {
            $upData['accType'] = trim($data['accType']);
        }

        if($data['code'] != '') {
            $upData['code'] = trim($data['code']);
        }

        if($data['bankNames'] != '') {
            $upData['bankName'] = trim($data['bankNames']);
        }

        $upData['u_time'] = time();
        $up = DB::name('customs_behalfacc')->where('id',$id)->update($upData);
        if(!$up) {
            return $this->jsons(['code'=>0,'msg'=>'编辑失败，请稍后重试！']);
        }
        return $this->jsons(['code'=>1,'msg'=>'编辑成功！']);
    }

    // 返回json
    private function jsons($data) {
        return json_encode($data);
    }

    // 判断银行卡号
    private function luhm($s){
        $n = 0;
        $ns = strrev($s); // 倒序
        for ($i=0; $i <strlen($s) ; $i++) {
            if ($i % 2 ==0) {
                $n += $ns[$i]; // 偶数位，包含校验码
            }else{
                $t = $ns[$i] * 2;
                if ($t >=10) {
                    $t = $t - 9;
                }
                $n += $t;
            }
        }
        return ( $n % 10 ) == 0;
    }


    // 订单列表
    public function old(Request $req) {

        $this->db = new Sysdb;
        // 账户名
        $accName = $req->get('accName');
        // 账户类型
        $accType = $req->get('accType');
        // 订单日期
        $atime   = $req->get('atime');

        $query = ['s'=>'admin/customs/oldlist'];
        $wher = [];
        if($accName != '') {
            $wher['accName'] = $accName;
            $query['accName'] = $accName;
        }

        if($accType != '') {
            $wher['accType'] = $accType;
            $query['accType'] = $accType;
        }


        $config = [
            'type' =>'Layui',
            'query'=>$query,
            'var_page'=>'page',
            'newstyle'=>true,
        ];

        $wher['id'] = ['gt',0];

        // 分页数量
        $pageSize  = 9;
        // 查询制定数据
        $field = 'mcSequenceNo,mcTransDateTime,orderNo,amount,cardNo,accName,accType,bfbSequenceNo,fee,respMsg,a_time';

        $acclist = $this->db->table('customs_behalfold')->field($field)->where($wher)->whereTime('a_time','w')->pagesd($pageSize,$config);

        // 获取本周的博客
        //Db::table('cs_blog')->whereTime('create_time', 'w')->select();
        // 查询所有的账户列表    whereTime('a_time','today'):当天；d
        //$acclist = DB::name('customs_behalfold')->where('id','>',0)->where($wher)->whereTime('a_time','w')->paginate(1,2,$config);

        if($atime != '' && $atime != 'undefined') {

            $query['atime'] = $atime;
            $config = [
                'type' =>'Layui',
                'query'=>$query,
                'var_page'=>'page',
                'newstyle'=>true,
            ];

            $start = strtotime($atime.' 00:00:00');
            $end   = strtotime($atime.' 23:59:59');

            $acclist = $this->db->table('customs_behalfold')->field($field)->where($wher)->whereTime('a_time','between',[$start,$end])->pagesd($pageSize,$config);

            // 查询所有的账户列表    whereTime('birthday', 'between', ['2019-10-1', '2020-10-1'])
            //$acclist = DB::name('customs_behalfold')->where('id','>',0)->where($wher)->whereTime('a_time','between',[$start,$end])->paginate(1);
        }

        $accType = [
            '卡','存折','对公账号'
        ];

        $this->assign('accType',$accType);
        $this->assign('acclist',$acclist['lists']);
        $this->assign('pages',$acclist['pages']);

        return view('behalf/old',['title'=>'账户列表']);
    }
}

?>