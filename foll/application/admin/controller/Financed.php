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
 * 商户费率设置控制器
 */
class Financed extends Auth
{
    // 每日对账数据
    public function Days()
    {
        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'Financed/Days'],
            'var_page'=>'page',
            'newstyle'=>true
        ];

        $data = DB::name('customs_reconcils_copy1')
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
        return view('merchant/day/index',[
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
        //$str = 'uname,tel,email,votesd,valVotesd,valFaild,valMoneyd,paySuccvalued,payFailvalued,payMoneyd,declVotesd,declFaild,declMoneyd,valTotald,ordersn,id,etime,checks';
        $str = 'uname,tel,email,votesd,valVotesd,valFaild,valMoneyd,paySuccvaluedsb,payFailvaluedsb,payMoneydsb,declVotesd,declFaild,declMoneyd,valTotaldsb,ordersn,id,etime,checks';
        // 根据上传的数据ID 查找数据，然后分页
        $bill_id = !empty(input('bill_id')) ? input('bill_id') : 0 ;
        $config = DB::name('customs_bills_copy1')
            ->field($str)
            ->where('id',$bill_id)->find();

        $in = $config['ordersn'];
        $pagesize = 10;
        $p = input('page');
        $page  = isset($p) ? $p : 1;
        $offset = $pagesize * ($page-1);

        $counts = DB::name('customs_datas_copy1')->where(['id'=>['in',$in]])->count();
        $data   = DB::name('customs_datas_copy1')->where(['id'=>['in',$in]])->limit($offset,$pagesize)->select();

        $total_page = ceil($counts/$pagesize);

        if($page > 1){
            $prev_page_url = Url('Financed/getDays').'&page='.($page-1).'&bill_id='.$bill_id;
        } else {
            $prev_page_url = Url('Financed/getDays').'&page='.($page).'&bill_id='.$bill_id;
        }

        if($page < $total_page) {
            $next_page_url = Url('Financed/getDays').'&page='.($page+1).'&bill_id='.$bill_id;
        } else {
            $next_page_url = Url('Financed/getDays').'&page='.$total_page.'&bill_id='.$bill_id;
        }

        $checks = ['','待对账',$config['uname'],'待核查'];

        return view('merchant/new/day/detail',[
            'config'  => $config,
            'datas'   => $data,
            'checks'  => $checks,
            'next_page_url'=>$next_page_url,
            'prev_page_url'=>$prev_page_url,
        ]);
    }


    /**
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 查看用户周期结算
     */
    public function getSetts()
    {
        $bill_id = !empty(input('bill_id')) ? input('bill_id') : 0 ;
        // 根据上传的数据ID 查找数据，然后分页
        $config = DB::name('customs_bills_copy1')
            ->field('uname,tel,email')
            ->where('id',$bill_id)->find();

        // 获取对账日期；
        $accDay = DB::name('customs_reconcils_copy1')->field('accountDay,uid')->where('bill_id',$bill_id)->find();

        $config['accountDay'] = $accDay['accountDay'];

        $billTime = strtotime($accDay['accountDay']);
        $uid      = $accDay['uid'];

        // 查询数据
        $data = DB::name('customs_settbill')->where(['uid'=>$uid,'billTime'=>$billTime])->select();

        $check = 1;
        $money = 0;
        foreach ($data as $dv) {
            $money += $dv['money'];
            if($dv['status'] == 2 || $dv['status'] == 3) {
                $check = $dv['status'];
            }
        }

        /*echo '<pre>';
        print_r($config);
        print_r($accDay);
        print_r($data);
        die;*/

        $checks = ['','已确认','待对账','待核查'];
        return view('merchant/new/day/setts',[
            'config'  => $config,
            'datas'   => $data,
            'money'   => $money,
            'check'   => $checks[$check],
        ]);
    }


    public function getAdditional(){

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


    // 每月对账数据
    public function Months()
    {
        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'Financed/Months'],
            'var_page'=>'page',
            'newstyle'=>true
        ];

        $data = DB::name('customs_reconcils_copy1')
            ->field('a.accountDay,a.c_status,a.bill_id,a.daytype,b.user_name,b.id')
            ->alias('a')
            ->join('decl_user b','a.uid=b.id')
            ->where('a.daytype','month')
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
        return view('merchant/month/index',[
            'title' =>'每月对账',
            'data'  =>$dd['data'],
            'status'=>$status,
            //'total' =>$dd['total'],
            'page'  =>$data->render(),
        ]);

    }


    // 获取每月对账数据
    public function getMonths()
    {
        // 根据上传的数据ID 查找数据，然后分页
        $bill_id = !empty(input('bill_id')) ? input('bill_id') : 0 ;
        //$config = DB::name('customs_bills')->field('uname,tel,email,idcounts,idfees,paycounts,payfees,ordercounts,orderfees,checks,ordersn,etime')->where('id',$bill_id)->find();

        //$str = 'uname,tel,email,votesd,valVotesd,valFaild,valMoneyd,paySuccvalued,payFailvalued,payMoneyd,declVotesd,declFaild,declMoneyd,valTotald,ordersn,id,etime,checks';
        $str = 'uname,tel,email,votesd,valVotesd,valFaild,valMoneyd,paySuccvaluedsb,payFailvaluedsb,payMoneydsb,declVotesd,declFaild,declMoneyd,valTotaldsb,ordersn,id,etime,checks';
        $config = DB::name('customs_bills_copy1')
            ->field($str)
            ->where('id',$bill_id)->find();

        $in = $config['ordersn'];

        $pagesize = 10;
        $p = input('page');
        $page  = isset($p) ? $p : 1;
        $offset = $pagesize * ($page-1);

        $counts = DB::name('customs_datas_copy1')->where(['id'=>['in',$in]])->count();
        $data   = DB::name('customs_datas_copy1')->where(['id'=>['in',$in]])->limit($offset,$pagesize)->select();

        $total_page = ceil($counts/$pagesize);

        if($page > 1){
            $prev_page_url = Url('Financed/getMonths').'&page='.($page-1).'&bill_id='.$bill_id;
        } else {
            $prev_page_url = Url('Financed/getMonths').'&page='.($page).'&bill_id='.$bill_id;
        }

        if($page < $total_page) {
            $next_page_url = Url('Financed/getMonths').'&page='.($page+1).'&bill_id='.$bill_id;
        } else {
            $next_page_url = Url('Financed/getMonths').'&page='.$total_page.'&bill_id='.$bill_id;
        }

        $checks = ['','待对账',$config['uname'],'待核查'];

        return view('merchant/new/month/detail',[
            'config'  => $config,
            'datas'   => $data,
            'checks'  => $checks,
            'bill_id' => $bill_id,
            'next_page_url'=>$next_page_url,
            'prev_page_url'=>$prev_page_url,
        ]);

    }


    /**
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *  获取月周期结算
     */
    public function getMonthSetts()
    {
        $bill_id = !empty(input('bill_id')) ? input('bill_id') : 0 ;
        // 根据上传的数据ID 查找数据，然后分页
        $config = DB::name('customs_bills_copy1')
            ->field('uname,tel,email')
            ->where('id',$bill_id)->find();

        // 获取对账日期；
        $accDay = DB::name('customs_reconcils_copy1')->field('accountDay,uid')->where('bill_id',$bill_id)->find();

        $config['accountDay'] = $accDay['accountDay'];

        $billTime = strtotime($accDay['accountDay']);
        $uid      = $accDay['uid'];

        // 转换时间搓
        // 年
        $y = date('Y',$billTime);
        // 月
        $m = date('m',$billTime);
        // 月最后一天
        $t = date('t',$billTime);

        // 月初
        $MonthStart = strtotime($y.'-'.$m.'-1 00:00:00');           //本月初   获取时间戳
        // 月尾
        $MonthLast = strtotime($y.'-'.($m).'-'.$t . ' 00:00:00');  //本月末

        //echo 'Start:'.date('Y-m-d H:i:s',$MonthStart).' End:'.date('Y-m-d H:i:s',$MonthLast);

        // 查询数据
        $data = DB::name('customs_settbill')->where('uid',$uid)->whereBetween('billTime',[$MonthStart,$MonthLast])->select();

        $check = 1;
        $money = 0;
        foreach ($data as $dv) {
            $money += $dv['money'];
            if($dv['status'] == 2 || $dv['status'] == 3) {
                $check = $dv['status'];
            }
        }

        $checks = ['','已确认','待对账','待核查'];
        return view('merchant/new/day/setts',[
            'config'  => $config,
            'datas'   => $data,
            'money'   => $money,
            'check'   => $checks[$check],
        ]);
    }


    /**
     * 获取清单对账日数据；
     */
    public function ElistgetDays() {
        $config = [
            'type'      =>  'Layui',
            'query'     =>  ['s'=>'Elist/Days'],
            'var_page'  =>  'page',
            'newstyle'  =>  true
        ];

        $data = DB::name('customs_recday')
            ->field('a.accountDay,a.r_status,a.bill_id,a.dayType,b.user_name,b.id')
            ->alias('a')
            ->join('decl_user b','a.uid=b.id')
            ->where(['a.dayType'=>'day','a.type'=>'elist'])
            ->order('a.accountDay','desc')
            ->paginate(13,true,$config);

        /**
         * 页面分析；
         * 1、选择对应的用户信息、默认全部用户信息，最新的对账情况！
         * 2、根据所选的用户进行分页，或者全部用户对账数据进行分页
         */
        $dd = $data->toArray();
        //对账状态：1待对账，2已对账，3待核查
        $status = ['待对账','已对账','待核查'];
        return view('merchant/elist/index',[
            'title' =>'清单每日对账',
            'data'  =>$dd['data'],
            'status'=>$status,
            //'total' =>$dd['total'],
            'page'  =>$data->render(),
        ]);
    }

    // AJAX 请求获取数据
    public function Elistget() {

        $str = 'uname,tel,email,count,succCount,errCount,succFee,errFee,countFee,money,status,endTime,ordersn';
        // 根据上传的数据ID 查找数据，然后分页
        $bill_id = !empty(input('bill_id')) ? input('bill_id') : 0 ;

        $config = DB::name('customs_sum')
            ->field($str)
            ->where('id',$bill_id)->find();

        // 转换成数组；
        $in = explode(',',$config['ordersn']);
        $pagesize = 10;
        $p = input('page');
        $page  = isset($p) ? $p : 1;
        $offset = $pagesize * ($page-1);

        $counts = DB::name('customs_list')->where(['id'=>['in',$in]])->count();
        $data   = DB::name('customs_list')->where(['id'=>['in',$in]])->limit($offset,$pagesize)->select();

        $total_page = ceil($counts/$pagesize);

        if($page > 1){
            $prev_page_url = Url('Elist/getDays').'&page='.($page-1).'&bill_id='.$bill_id;
        } else {
            $prev_page_url = Url('Elist/getDays').'&page='.($page).'&bill_id='.$bill_id;
        }

        if($page < $total_page) {
            $next_page_url = Url('Elist/getDays').'&page='.($page+1).'&bill_id='.$bill_id;
        } else {
            $next_page_url = Url('Elist/getDays').'&page='.$total_page.'&bill_id='.$bill_id;
        }

        $checks = ['待对账',$config['uname'],'待核查'];

        return view('merchant/elist/detail',[
            'config'  => $config,
            'datas'   => $data,
            'checks'  => $checks,
            'next_page_url'=>$next_page_url,
            'prev_page_url'=>$prev_page_url,
        ]);
    }

    /**
     * 获取月对账数据；
     */
    public function ElistMonths() {
        $config = [
            'type'      =>  'Layui',
            'query'     =>  ['s'=>'Elist/Months'],
            'var_page'  =>  'page',
            'newstyle'  =>  true
        ];

        $data = DB::name('customs_recday')
            ->field('a.accountDay,a.r_status,a.bill_id,a.dayType,b.user_name,b.id')
            ->alias('a')
            ->join('decl_user b','a.uid=b.id')
            ->where(['a.dayType'=>'month','a.type'=>'elist'])
            ->order('a.accountDay','desc')
            ->paginate(13,true,$config);

        /**
         * 页面分析；
         * 1、选择对应的用户信息、默认全部用户信息，最新的对账情况！
         * 2、根据所选的用户进行分页，或者全部用户对账数据进行分页
         */
        $dd = $data->toArray();
        //对账状态：1待对账，2已对账，3待核查
        $status = ['待对账','已对账','待核查'];
        return view('merchant/elist/monthinx',[
            'title' =>'清单每月对账',
            'data'  =>$dd['data'],
            'status'=>$status,
            //'total' =>$dd['total'],
            'page'  =>$data->render(),
        ]);
    }

    public function ElistMonget() {
        $str = 'uname,tel,email,count,succCount,errCount,succFee,errFee,countFee,money,status,endTime,ordersn';
        // 根据上传的数据ID 查找数据，然后分页
        $bill_id = !empty(input('bill_id')) ? input('bill_id') : 0 ;

        $config = DB::name('customs_sum')
            ->field($str)
            ->where('id',$bill_id)->find();

        // 转换成数组；
        $in = explode(',',$config['ordersn']);
        $pagesize = 10;
        $p = input('page');
        $page  = isset($p) ? $p : 1;
        $offset = $pagesize * ($page-1);

        $counts = DB::name('customs_list')->where(['id'=>['in',$in]])->count();
        $data   = DB::name('customs_list')->where(['id'=>['in',$in]])->limit($offset,$pagesize)->select();

        $total_page = ceil($counts/$pagesize);

        if($page > 1){
            $prev_page_url = Url('Elist/Monthsget').'&page='.($page-1).'&bill_id='.$bill_id;
        } else {
            $prev_page_url = Url('Elist/Monthsget').'&page='.($page).'&bill_id='.$bill_id;
        }

        if($page < $total_page) {
            $next_page_url = Url('Elist/Monthsget').'&page='.($page+1).'&bill_id='.$bill_id;
        } else {
            $next_page_url = Url('Elist/Monthsget').'&page='.$total_page.'&bill_id='.$bill_id;
        }

        $checks = ['待对账',$config['uname'],'待核查'];

        return view('merchant/elist/detailinx',[
            'config'  => $config,
            'datas'   => $data,
            'checks'  => $checks,
            'next_page_url'=>$next_page_url,
            'prev_page_url'=>$prev_page_url,
        ]);
    }


    /**
     * 获取运单数据
     */
    public function LogisgetDays(){
        $config = [
            'type'      =>  'Layui',
            'query'     =>  ['s'=>'Logis/Days'],
            'var_page'  =>  'page',
            'newstyle'  =>  true
        ];

        $data = DB::name('customs_recday')
            ->field('a.accountDay,a.r_status,a.bill_id,a.dayType,b.user_name,b.id')
            ->alias('a')
            ->join('decl_user b','a.uid=b.id')
            ->where(['a.dayType'=>'day','a.type'=>'logistics'])
            ->order('a.accountDay','desc')
            ->paginate(13,true,$config);

        /**
         * 页面分析；
         * 1、选择对应的用户信息、默认全部用户信息，最新的对账情况！
         * 2、根据所选的用户进行分页，或者全部用户对账数据进行分页
         */
        $dd = $data->toArray();
        //对账状态：1待对账，2已对账，3待核查
        $status = ['待对账','已对账','待核查'];
        return view('merchant/logis/index',[
            'title' =>'运单每日对账',
            'data'  =>$dd['data'],
            'status'=>$status,
            //'total' =>$dd['total'],
            'page'  =>$data->render(),
        ]);
    }

    public function Logisget(){
        $str = 'uname,tel,email,count,succCount,errCount,succFee,errFee,countFee,money,status,endTime,ordersn';
        // 根据上传的数据ID 查找数据，然后分页
        $bill_id = !empty(input('bill_id')) ? input('bill_id') : 0 ;

        $config = DB::name('customs_sum')
            ->field($str)
            ->where('id',$bill_id)->find();

        // 转换成数组；
        $in = explode(',',$config['ordersn']);
        $pagesize = 10;
        $p = input('page');
        $page  = isset($p) ? $p : 1;
        $offset = $pagesize * ($page-1);

        $counts = DB::name('customs_list')->where(['id'=>['in',$in]])->count();
        $data   = DB::name('customs_list')->where(['id'=>['in',$in]])->limit($offset,$pagesize)->select();

        $total_page = ceil($counts/$pagesize);

        if($page > 1){
            $prev_page_url = Url('Logis/getDays').'&page='.($page-1).'&bill_id='.$bill_id;
        } else {
            $prev_page_url = Url('Logis/getDays').'&page='.($page).'&bill_id='.$bill_id;
        }

        if($page < $total_page) {
            $next_page_url = Url('Logis/getDays').'&page='.($page+1).'&bill_id='.$bill_id;
        } else {
            $next_page_url = Url('Logis/getDays').'&page='.$total_page.'&bill_id='.$bill_id;
        }

        $checks = ['待对账',$config['uname'],'待核查'];

        return view('merchant/logis/detail',[
            'config'  => $config,
            'datas'   => $data,
            'checks'  => $checks,
            'next_page_url'=>$next_page_url,
            'prev_page_url'=>$prev_page_url,
        ]);
    }

    // 月
    public function LogisMonths(){
        $config = [
            'type'      =>  'Layui',
            'query'     =>  ['s'=>'Logis/Months'],
            'var_page'  =>  'page',
            'newstyle'  =>  true
        ];

        $data = DB::name('customs_recday')
            ->field('a.accountDay,a.r_status,a.bill_id,a.dayType,b.user_name,b.id')
            ->alias('a')
            ->join('decl_user b','a.uid=b.id')
            ->where(['a.dayType'=>'month','a.type'=>'logistics'])
            ->order('a.accountDay','desc')
            ->paginate(13,true,$config);

        /**
         * 页面分析；
         * 1、选择对应的用户信息、默认全部用户信息，最新的对账情况！
         * 2、根据所选的用户进行分页，或者全部用户对账数据进行分页
         */
        $dd = $data->toArray();
        //对账状态：1待对账，2已对账，3待核查
        $status = ['待对账','已对账','待核查'];
        return view('merchant/logis/monthinx',[
            'title' =>'运单每月对账',
            'data'  =>$dd['data'],
            'status'=>$status,
            //'total' =>$dd['total'],
            'page'  =>$data->render(),
        ]);
    }

    public function LogisMonget(){
        $str = 'uname,tel,email,count,succCount,errCount,succFee,errFee,countFee,money,status,endTime,ordersn';
        // 根据上传的数据ID 查找数据，然后分页
        $bill_id = !empty(input('bill_id')) ? input('bill_id') : 0 ;

        $config = DB::name('customs_sum')
            ->field($str)
            ->where('id',$bill_id)->find();

        // 转换成数组；
        $in = explode(',',$config['ordersn']);
        $pagesize = 10;
        $p = input('page');
        $page  = isset($p) ? $p : 1;
        $offset = $pagesize * ($page-1);

        $counts = DB::name('customs_list')->where(['id'=>['in',$in]])->count();
        $data   = DB::name('customs_list')->where(['id'=>['in',$in]])->limit($offset,$pagesize)->select();

        $total_page = ceil($counts/$pagesize);

        if($page > 1){
            $prev_page_url = Url('Logis/Monthsget').'&page='.($page-1).'&bill_id='.$bill_id;
        } else {
            $prev_page_url = Url('Logis/Monthsget').'&page='.($page).'&bill_id='.$bill_id;
        }

        if($page < $total_page) {
            $next_page_url = Url('Logis/Monthsget').'&page='.($page+1).'&bill_id='.$bill_id;
        } else {
            $next_page_url = Url('Logis/Monthsget').'&page='.$total_page.'&bill_id='.$bill_id;
        }

        $checks = ['待对账',$config['uname'],'待核查'];

        return view('merchant/logis/detailinx',[
            'config'  => $config,
            'datas'   => $data,
            'checks'  => $checks,
            'next_page_url'=>$next_page_url,
            'prev_page_url'=>$prev_page_url,
        ]);
    }





    // 导出清单文件  按提单到导出
    public function Monthsexports()
    {
        $bill_num = input('batch_num');// 提单编号
        $bill_id  = input('bill_id');// 用于获取对账月
        $result   = loader::model('Financeds','logic')->Mexports($bill_num,$bill_id);
        if(is_object($result)) {
            $fileName = substr(md5(time()),5,10);
            ob_end_clean();
            header("pragma:public");
            header("Content-type:application/vnd.ms-excel;charset=utf-8;name={$fileName}.xls");
            header("Content-Disposition:attachment;filename={$fileName}.xls");
            $result->save("php://output");
        } else {
            $this->error($result);
        }
    }


    //月总账单列表
    public function Monthly()
    {
        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'Financed/Monthly'],
            'var_page'=>'page',
            'newstyle'=>true
        ];

        $data = DB::name('customs_reconcils_copy1')
            ->field('a.accountDay,a.c_status,a.bill_id,a.daytype,b.user_name,b.id')
            ->alias('a')
            ->join('decl_user b','a.uid=b.id')
            ->where('a.daytype','monthly')
            ->order('a.accountDay','desc')
            ->paginate(13,false,$config);

        /**
         * 页面分析；
         * 1、选择对应的用户信息、默认全部用户信息，最新的对账情况！
         * 2、根据所选的用户进行分页，或者全部用户对账数据进行分页
         */
        $dd = $data->toArray();
        //对账状态：1待对账，2已对账，3待核查
        $status = ['','待对账','已对账','待核查'];
        return $this->fetch('monthly/index',[
            'title' =>'月总对账',
            'data'  =>$dd['data'],
            'status'=>$status,
            'page'  =>$data->render(),
        ]);
    }

    // 获取月总账单
    public function getMonthlys()
    {
        // 根据上传的数据ID 查找数据，然后分页
        $bill_id = !empty(input('bill_id')) ? input('bill_id') : 0 ;
        //$config = DB::name('customs_bills')->field('uname,tel,email,idcounts,idfees,paycounts,payfees,ordercounts,orderfees,checks,ordersn,etime')->where('id',$bill_id)->find();

        $str = 'uname,tel,email,votesd,valVotesd,valFaild,valMoneyd,paySuccvaluedsb,payFailvaluedsb,payMoneydsb,declVotesd,declFaild,declMoneyd,valTotaldsb,ordersn,id,etime,checks';
        $config = DB::name('customs_bills_copy1')
            ->field($str)
            ->where('id',$bill_id)->find();

        $in = $config['ordersn'];

        $pagesize = 10;
        $p = input('page');
        $page  = isset($p) ? $p : 1;
        $offset = $pagesize * ($page-1);

        $counts = DB::name('customs_datas_copy1')->where(['id'=>['in',$in]])->count();
        $data   = DB::name('customs_datas_copy1')->where(['id'=>['in',$in]])->limit($offset,$pagesize)->select();

        $total_page = ceil($counts/$pagesize);

        if($page > 1){
            $prev_page_url = Url('Financed/getMonthlys').'&page='.($page-1).'&bill_id='.$bill_id;
        } else {
            $prev_page_url = Url('Financed/getMonthlys').'&page='.($page).'&bill_id='.$bill_id;
        }

        if($page < $total_page) {
            $next_page_url = Url('Financed/getMonthlys').'&page='.($page+1).'&bill_id='.$bill_id;
        } else {
            $next_page_url = Url('Financed/getMonthlys').'&page='.$total_page.'&bill_id='.$bill_id;
        }

        $checks = ['','待对账',$config['uname'],'待核查'];

        return view('monthly/detail',[
            'config'  => $config,
            'datas'   => $data,
            'checks'  => $checks,
            'bill_id' => $bill_id,
            'next_page_url'=>$next_page_url,
            'prev_page_url'=>$prev_page_url,
        ]);
    }



    // 清算管理
    public function Faileds()
    {
        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'Financed/Faileds'],
            'var_page'=>'page',
            'newstyle'=>true
        ];

        $str = 'id,uname,tel,email,votesd,valVotesd,valFaild,valMoneyd,paySuccvaluedsb,payFailvaluedsb,payMoneydsb,declVotesd,declFaild,declMoneyd,valTotaldsb,ordersn,etime,checks,liquStatus,liquid';

        $data = DB::name('customs_bills_copy1')
            ->field($str)
            ->where(['daytype'=>'month','checks'=>2])->paginate(9,false,$config);

        /**
         * 查询数据:
         * 查询月的对账单；待清算，跟已清算的  已清算可以确认清算
         */

        $liStatus = ['','待清算','已清算','确认清算'];
        return view('merchant/new/failed/index',[
            'title'=>'清算管理',
            'data' =>$data->toArray(),
            'page' =>$data->render(),
            'liStatus'=>$liStatus,
        ]);

    }

    // 确认清算
    public function Liqus()
    {
        $ids = input('ids');
        $email = input('email');// 用于通知商户
        $rs = DB::name('customs_bills_copy1')->where(['id'=>$ids])->update(['liquStatus'=>3]);
        if($rs) {
            $content = '尊敬的商户：您好!，您的清算清单，管理员已确认！日期：'.date("Y-m-d H:i:s",time());
            $subject = '您有跨境申报的信息';
            $this->SendEmail($email,$content,$subject);
            return json_encode(['code'=>1,'msg'=>'确认清算成功!']);
        }
        return json_encode(['code'=>0,'msg'=>'确认清算失败!']);

        // 发送电子邮件
    }

    // 查看已清算的清单
    public function SeeLiqus()
    {
        $lids = input('liquid');
        $uname = input('uname');
        $data = DB::name('customs_liquis')->where(['id'=>$lids])->find();
        return view('merchant/failed/edits',[
            'data' => $data,
            'uname'=> $uname,
            'title'=> '清算清单',
        ]);
    }

    // 提醒清算
    public function Remind()
    {
        $email = input('email');
        // 发送电子邮件提醒
        $content = '尊敬的商户：您好!，您有未清算的清单，请前往清算！提醒日期：'.date("Y-m-d H:i:s",time());
        $subject = '您有跨境申报的信息';
        $rs = $this->SendEmail($email,$content,$subject);
        if($rs) {
            return json_encode(['code'=>1,'msg'=>"提醒成功！"]);
        }
        return json_encode(['code'=>0,'msg'=>"提醒失败！"]);
    }



    // 有误待查数据
    public function Mistakens()
    {
        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'Financed/Mistakens'],
            'var_page'=>'page',
            'newstyle'=>true
        ];

        $str = 'id,uname,tel,email,votesd,valVotesd,valFaild,valMoneyd,paySuccvaluedsb,payFailvaluedsb,payMoneydsb,declVotesd,declFaild,declMoneyd,valTotaldsb,ordersn,etime,checks,liquStatus,liquid,daytype';
        $data = DB::name('customs_bills_copy1')
                //->field('id,uname,email,ticketTotals,declsTotals,identifyTotals,paymentTotals,orderTotals,amountTotals,etime,daytype')
            ->field($str)
                ->where(['checks'=>3])->paginate(5,false,$config);

        /*return view('merchant/mistaken/index',[
            'title'=>'有误待核订单',
            'data' =>$data->toArray(),
            'page' =>$data->render(),
        ]);*/

        return view('merchant/new/mistaken/index',[
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
        $data = DB::name('customs_bills_copy1')->where(['id'=>$id])->find();

        return view('merchant/new/mistaken/edits',[
            'data' => $data,
            'title'=> '订单清算修改',
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

        $upData['paySuccvaluedsb']    = number_format($inputs['paySuccvaluedsb'],2,'.','');
        $upData['payFailvaluedsb']    = number_format($inputs['payFailvaluedsb'],2,'.','');
        $upData['payMoneydsb']        = number_format($inputs['payMoneydsb'],2,'.','');

        $upData['declVotesd']       = $inputs['declVotesd'];
        $upData['declFaild']        = $inputs['declFaild'];
        $upData['declMoneyd']       = number_format($inputs['declMoneyd'],2,'.','');

        $upData['valTotaldsb']        = number_format($inputs['valTotaldsb'],2,'.','');

        $upData['checks']           = 1;// 状态修改为1 待对账，
        DB::name('customs_bills_copy1')->where(['id'=>$id])->update($upData);

        DB::name('customs_reconcils_copy1')->where(['bill_id'=>$id])->update(['c_status'=>1]);

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


}
?>