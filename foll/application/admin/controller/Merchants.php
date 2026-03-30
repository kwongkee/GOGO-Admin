<?php
namespace app\admin\controller;
use app\admin\controller;
use think\Request;
use Util\data\Sysdb;
use think\Session;
use think\Db;
use think\loader;

/**
 * Class Merchant
 * @package app\admin\controller
 * 二次对账数据获取，设置
 */
class Merchants extends Auth
{
    // 每日对账数据
    public function Days()
    {
        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'merchants/Days'],
            'var_page'=>'page',
            'newstyle'=>true
        ];

        $data = DB::name('customs_reconcils_two')
            ->field('a.accountDay,a.c_status,a.bill_id,a.daytype,b.user_name,b.id')
            ->alias('a')
            ->join('decl_user b','a.uid=b.id')
            ->where('a.daytype','day')
            ->order('a.accountDay','desc')
            ->paginate(13,true,$config);

        /**
         * 页面分析；
         * 1、选择对应的用户信息、默认全部用户信息，最新的对账情况！
         * 2、根据所选的用户进行分页，或者全部用户对账数据进行分页
         */
        $dd = $data->toArray();
        //对账状态：1待对账，2已对账，3待核查
        $status = ['','待对账','已对账','待核查'];
        return view('merchant/mer/index',[
            'title' =>'每日对账',
            'data'  =>$dd['data'],
            'status'=>$status,
            //'total' =>$dd['total'],
            'page'  =>$data->render(),
        ]);
    }


    // 查看商户每日对账数据
    public function getDays()
    {
        $str = 'uname,tel,email,votesd,valVotesd,valFaild,valMoneyd,paySuccvalued,payFailvalued,payMoneyd,declVotesd,declFaild,declMoneyd,valTotald,ordersn,id,etime,checks';
        // 根据上传的数据ID 查找数据，然后分页
        $bill_id = !empty(input('bill_id')) ? input('bill_id') : 0 ;
        $config = DB::name('customs_bills_two')
            ->field($str)
            ->where('id',$bill_id)->find();

        $in = $config['ordersn'];
        $pagesize = 10;
        $p = input('page');
        $page  = isset($p) ? $p : 1;
        $offset = $pagesize * ($page-1);

        $counts = DB::name('customs_datas_two')->where(['id'=>['in',$in]])->count();
        $data   = DB::name('customs_datas_two')->where(['id'=>['in',$in]])->limit($offset,$pagesize)->select();

        $total_page = ceil($counts/$pagesize);

        if($page > 1){
            $prev_page_url = Url('merchants/getDays').'&page='.($page-1).'&bill_id='.$bill_id;
        } else {
            $prev_page_url = Url('merchants/getDays').'&page='.($page).'&bill_id='.$bill_id;
        }

        if($page < $total_page) {
            $next_page_url = Url('merchants/getDays').'&page='.($page+1).'&bill_id='.$bill_id;
        } else {
            $next_page_url = Url('merchants/getDays').'&page='.$total_page.'&bill_id='.$bill_id;
        }

        $checks = ['','待对账',$config['uname'],'待核查'];

        return view('merchant/mer/day/detail',[
            'config'  => $config,
            'datas'   => $data,
            'checks'  => $checks,
            'next_page_url'=>$next_page_url,
            'prev_page_url'=>$prev_page_url,
        ]);
    }



    // 有误待查数据
    public function Mistakens()
    {
        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'merchants/Mistakens'],
            'var_page'=>'page',
            'newstyle'=>true
        ];

        $str = 'id,uname,tel,email,votesd,valVotesd,valFaild,valMoneyd,paySuccvalued,payFailvalued,payMoneyd,declVotesd,declFaild,declMoneyd,valTotald,ordersn,etime,checks,liquStatus,liquid,daytype';
        $data = DB::name('customs_bills_two')
            //->field('id,uname,email,ticketTotals,declsTotals,identifyTotals,paymentTotals,orderTotals,amountTotals,etime,daytype')
            ->field($str)
            ->where(['checks'=>3])->paginate(5,false,$config);

        return view('merchant/mer/day/index',[
            'title'=>'有误待核订单',
            'data' =>$data->toArray(),
            'page' =>$data->render(),
        ]);
    }

    // 编辑设置费用
    public function Medits()
    {
        $id = input('ids');// 接收IDs
        $email = input('email');
        $data = DB::name('customs_bills_two')->where(['id'=>$id])->find();

        return view('merchant/mer/day/edits',[
            'data' => $data,
            'title'=> '有误账单修改',
            'email'=> $email,
        ]);
    }

    // 设置费用
    public function setMedits(Request $request)
    {
        $alls = $request->param();
        $inputs = json_decode(json_encode($alls),true);

        // 条件ID
        $id = $inputs['id'];
        $email = $inputs['email'];

        // 身份验证数量
        $upData['votesd']           = $inputs['votesd'];
        $upData['valVotesd']        = $inputs['valVotesd'];
        $upData['valFaild']         = $inputs['valFaild'];
        $upData['valMoneyd']        = number_format($inputs['valMoneyd'],2,'.','');
        $upData['paySuccvalued']    = number_format($inputs['paySuccvalued'],2,'.','');
        $upData['payFailvalued']    = number_format($inputs['payFailvalued'],2,'.','');
        $upData['payMoneyd']        = number_format($inputs['payMoneyd'],2,'.','');
        $upData['declVotesd']       = $inputs['declVotesd'];
        $upData['declFaild']        = $inputs['declFaild'];
        $upData['declMoneyd']       = number_format($inputs['declMoneyd'],2,'.','');
        $upData['valTotald']        = number_format($inputs['valTotald'],2,'.','');

        $upData['checks']           = 1;// 状态修改为1 待对账，
        DB::name('customs_bills_two')->where(['id'=>$id])->update($upData);

        DB::name('customs_reconcils_two')->where(['bill_id'=>$id])->update(['c_status'=>1]);

        //$email   = '805929498@qq.com';
        $content = '您好，你的有误清单、管理员已审核修改，请重新对账！审核时间：'.date("Y-m-d H:i:s",time());
        $subject = '您有跨境申报的信息';
        $this->SendEmail($email,$content,$subject);
        return json_encode(['code'=>1,'msg'=>'编辑修改成功']);
    }

    //发送电子邮件
    public function SendEmail($email='353453825@qq.com',$content,$subject = '您有对账信息，请登录查看！') {
        $name    = '系统管理员';
        $status  = send_mail($email,$name,$subject,$content);
        if($status) {
            return true;
        }
        return false;
    }


    // 数据导出  按批次号导出
    public function Dayexports() {
        $batch_num = input('batch_num');
        $result = Loader::model('Financeds','logic')->Exports($batch_num);
        if(is_object($result)) {
            $fileName = substr(md5(time()),1,9);
            ob_end_clean();// 清除缓存
            header('pragma:public');
            // 设置表头信息
            header("Content-type:application/vnd.ms-excel;charset=utf-8;name={$fileName}.xls");
            header("Content-Disposition:attachment;filename={$fileName}.xls");
            $result->save("php://output");
        } else {
            $this->error($result);
        }
    }
}