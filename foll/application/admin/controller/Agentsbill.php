<?php

namespace app\admin\controller;

use think\Db;
use think\Request;
use Util\data\Sysdb;

class Agentsbill extends Auth
{
    private $req = null;
    public function __construct()
    {
        parent::__construct();

        $this->db = new Sysdb;
    }


    // 有误账单列表
    public function lists()
    {
        $config = [
            'type'=>'Layui',
            'query'=>['s'=>'agents/bill/lists'],
            'var_page'=>'page',
            'newstyle'=>true,
        ];

        $data = $this->db->table('customs_agents_status')->where(['status'=>3])->pages(6,$config);

        return view('agents/bill/lists',[
            'data'=>$data,
        ]);
    }



    // 编辑操作
    public function up(Request $request)
    {
        $config = $where = $request->param();
        /**
         * 获取账单ID，ordersn,  再获取列表信息
         */
        $datas = [];

        $bill = $this->db->table('customs_agents_bills')->field('id,ordersn')->where($where)->item();
        if(!empty($bill)) {

            $wh = explode(',',$bill['ordersn']);
            $datas = $this->db->table('customs_agents_datas')->where(['id'=>['in',$wh]])->lists();
            $config['bill_id'] = $bill['id'];
            $config['ordersn'] = $bill['ordersn'];
        }

        // 渲染页面
        return view('agents/bill/order',[
            'datas'=>$datas,
            'conf'=>$config,
        ]);

        $this->msg($datas);
    }


    // 更新内容
    public function update(Request $request)
    {
        $data = $request->param();
        // 更新值
        $up[$data['name']] = $data['val'];
        // 更新内容
        $wh['id']          = $data['upid'];

        $ups = $this->db->table('customs_agents_datas')->where($wh)->update($up);
        if(!$ups) {
            return ['code'=>0,'msg'=>'更新失败'];
        }

        return ['code'=>1,'msg'=>'更新成功'];
    }


    // 确认修改
    public function check(Request $request)
    {
        $reqs = $request->param();
        $did  = (int)$reqs['did'];
        $uid  = (int)$reqs['uid'];
        $bill_id = (int)$reqs['bill_id'];
        /**
         * 获取代理费率：did,uid;
         * 按ordersn 查询已变更的数据，循环相加后，作为更新条件；更新bills表数据，条件bill_id;
         * 按did,uid,day 更新status 表的状态；
         */
        // 获取费率
        //$fee = $this->db->table()->where(['did'=>$did,'uid'=>$uid])->item();

        // 获取账单列表
        $dawh  = explode(',',$reqs['ordersn']);
        $field = 'valVotes,valFail,valMoney,paySuccvalue,payFailvalue,payMoney,declVotes,declFail,declMoney';
        $datas = $this->db->table('customs_agents_datas')->field($field)->where(['id'=>['in',$dawh]])->lists();
        // 循环计算数据
        $valVotes = 0;
        $valFail = 0;
        $valMoney = 0;
        $paySuccvalue = 0;
        $payFailvalue = 0;
        $payMoney = 0;
        $declVotes = 0;
        $declFail = 0;
        $declMoney = 0;

        foreach($datas as $dv) {
            $valVotes       += $dv['valVotes'];
            $valFail        += $dv['valFail'];
            $valMoney       += $dv['valMoney'];
            $paySuccvalue   += $dv['paySuccvalue'];
            $payFailvalue   += $dv['payFailvalue'];
            $payMoney       += $dv['payMoney'];
            $declVotes      += $dv['declVotes'];
            $declFail       += $dv['declFail'];
            $declMoney      += $dv['declMoney'];
        }

        // bills
        $bills = [
            'valVotesd'     =>  $valVotes,
            'valFaild'      =>  $valFail,
            'valMoneyd'     =>  $valMoney,
            'paySuccvalued' =>  $paySuccvalue,
            'payFailvalued' =>  $payFailvalue,
            'payMoneyd'     =>  $payMoney,
            'declVotesd'    =>  $declVotes,
            'declFaild'     =>  $declFail,
            'declMoneyd'    =>  $declMoney,
            'status'        =>  1,
        ];

        // 更新bills
        $bup = $this->db->table('customs_agents_bills')->where(['id'=>$bill_id])->update($bills);

        $sup = $this->db->table('customs_agents_status')->where(['did'=>$did,'uid'=>$uid,'day'=>$reqs['day']])->update(['status'=>1]);

        if($bup && $sup) {
            return ['code'=>1,'msg'=>'提交成功'];
        }
        return ['code'=>0,'msg'=>'提交失败'];
    }



    private function msg($data)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }


}