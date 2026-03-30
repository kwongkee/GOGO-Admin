<?php
namespace app\admin\controller;
use app\admin\controller;
use Util\data\Sysdb;
use think\Session;
use think\Db;

/**
 * Class Merchant
 * @package app\admin\controller
 * 商户费率设置控制器
 */
class Merchant extends Auth
{
    /**
     * 费率列表
     */
    public function feelist()
    {
        $userInfo = Session('myUser');
        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'merchant/feelist'],
            'var_page'=>'page',
            'newstyle'=>true
        ];

        $data = null;

        $data = DB::name('customs_rates')
            ->field('a.id,a.verfee,a.payfee,a.orderfee,a.elistfee,a.logisfee,a.c_time,b.user_name')
            ->alias('a')
            ->join('decl_user b','a.uid=b.id')
            ->where('b.user_status',0)
            ->order('a.c_time','desc')
            ->paginate(9,false,$config);

        return view('merchant/rate/feelist',[
            'title'=>'商户费率列表',
            'data'=>$data->toArray(),
            'page'=>$data->render(),
        ]);

    }

    // 编辑设置
    public function edits() {

        $data = null;
        $ids  = input('ids');
        $uname= input('uname');
        $data = DB::name('customs_rates')->where(['id'=>$ids])->find();
        $data['uname'] = $uname;
        return view('merchant/rate/edits',[
            'title'=>'商户费率设置',
            'data' => $data,
        ]);
    }

    // 确认编辑设置费率
    public function feeds()
    {
        // 设置条件
        $id = input('id');
        $Insert['verfee']   = number_format(input('verfee'),2);
        $Insert['payfee']   = number_format(input('payfee'),3);
        $Insert['orderfee'] = number_format(input('orderfee'),2);
        $Insert['elistfee'] = number_format(input('elistfee'),2);
        $Insert['logisfee'] = number_format(input('logisfee'),2);
        $Insert['u_time']   = time();
        DB::name('customs_rates')->where(['id'=>$id])->update($Insert);
        return json_encode(['code'=>1,'msg'=>'费率设置成功！']);
    }
}
?>