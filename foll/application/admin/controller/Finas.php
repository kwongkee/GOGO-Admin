<?php
namespace app\admin\controller;

use think\Db;
use think\Request;
use Util\data\Sysdb;
use think\Session;
use think\loader;

/**
 * Class Finas
 * @package app\admin\controller
 * 财务预警管理
 */
class Finas extends Auth {

    // 预警配置  2019-03-19
    public function index()
    {
        $data = Db::name('customs_finawarning')->select();
        $this->assign('data',$data);
        return view('finas/index',['title'=>'预警配置']);
    }

    // 编辑预警配置
    public function edit() {
        $id = input('ids');
        $data = DB::name('customs_finawarning')->where(['id'=>$id])->find();
        $this->assign('data',$data);
        return view('finas/edit',['title'=>'编辑预警']);
    }

    // 保存预警配置
    public function store(Request $request) {

        // 判断请求方式是否正确
        if(!$request->isPost()){
            return json_encode(['msg'=>'请求方式不正确','code'=>2]);
        }

        // 获取表单数据
        $data = $request->post();
        $id   = $data['id'];
        unset($data['id']);
        foreach($data as $v) {
            if(empty($v)){
                return json_encode(['msg'=>'数据不能为空','code'=>2]);
                break;
            }
        }
        $data['uptime'] = time();

        DB::name('customs_finawarning')->where(['id'=>$id])->update($data);

        return json_encode(['msg'=>'编辑成功！','code'=>1]);
    }

    // 财务预警列表
    public function lists() {

        // 配置分页数据
        $config = [
            'type'=>'Layui',
            'query'=>['s'=>'admin/fina/warninglist'],//参数添加
            'var_page'=>'page',
            'newstyle'=>true,
        ];
        $data = null;
        // 链表查询数据
        $data = DB::name('customs_finawarninglists')
            ->field('b.user_name,a.dayType,a.warValue,value,billDate,a.addtime')
            ->alias('a')->join('decl_user b','b.id=a.uid')
            //->where('a.id','>',1)
            ->order('a.addtime','desc')
            ->paginate(12,false,$config);

        $type = ['day'=>'日对账','month'=>'月对账'];
        return view('finas/lists',[
            'title'=>'超出对账预警列表',
            'data' =>$data->toArray(),
            'page' =>$data->render(),
            'type' => $type,
        ]);
    }

    // 支付账单汇总查询
    public function payCheck(Request $request)
    {
        // 配置分页数据
        $config = [
            'type'=>'Layui',
            'query'=>['s'=>'admin/fina/paycheck'],//参数添加
            'var_page'=>'page',
            'newstyle'=>true,
        ];

        $pay_id  = $request->param('pay_id');// 支付公司
        $payType = $request->param('payType');// 账单类型
        $month   = $request->param('month');  // 账单月
        $datas = [];

        $limit = 8;
        //$strart = microtime(true);

        if($pay_id != null && $payType != null && $month != null) {

            $datas = DB::name('customs_payment_paycheck')->where(['pay_id'=>$pay_id,'payType'=>$payType,'month'=>$month])->order('id','desc')->paginate($limit,false,$config);
        } else if($pay_id != null && $payType != null) {// 支付企业与类型

            $datas = DB::name('customs_payment_paycheck')->where(['pay_id'=>$pay_id,'payType'=>$payType])->order('id','desc')->paginate($limit,false,$config);
        } else if($pay_id != null && $month != null) {// 支付企业与月

            $datas = DB::name('customs_payment_paycheck')->where(['pay_id'=>$pay_id,'month'=>$month])->order('id','desc')->paginate($limit,false,$config);
        } else if($payType != null && $month != null) {// 账单类型与月

            $datas = DB::name('customs_payment_paycheck')->where(['payType'=>$payType,'month'=>$month])->order('id','desc')->paginate($limit,false,$config);
        } else if($pay_id != null) {// 支付企业不为空

            $datas = DB::name('customs_payment_paycheck')->where(['pay_id'=>$pay_id])->order('id','desc')->paginate($limit,false,$config);
        } else if($payType != null) {// 类型不为空

            $datas = DB::name('customs_payment_paycheck')->where(['payType'=>$payType])->order('id','desc')->paginate($limit,false,$config);
        } else if($month != null) {// 月不为空

            $datas = DB::name('customs_payment_paycheck')->where(['month'=>$month])->order('id','desc')->paginate($limit,false,$config);
        } else {

            $datas = DB::name('customs_payment_paycheck')->order('id','desc')->paginate($limit,false,$config);
        }

        /*$end = microtime(true);

        $total = $end - $strart;

        echo '单前页面执行：'.$total;*/

        $type = ['pay'=>'支付账单','identify'=>'身份账单'];
        $payList = [2=>'邦付宝支付科技有限公司'];

        $this->assign('payList',$payList);

        $this->assign('type',$type);

        $this->assign('datas',$datas);

        return view('paycheck/index',['title'=>'申报支付账单汇总']);
    }
}
?>