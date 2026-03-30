<?php
namespace app\admin\controller;
use app\admin\controller;
use think\Request;
use Util\data\Sysdb;
use think\Session;
use think\Db;
use think\loader;

class Settlement extends Auth {

    public function index() {
        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'Settlement/index'],
            'var_page'=>'page',
            'newstyle'=>true
        ];

        $list = Db::name('customs_settlconfig')->order('atime desc')->paginate(10,false,$config);

        $this->assign('list', $list);

        return view('settlement/index',
            ['title'=>'']
        );
    }


    // 添加视图
    public function add() {
        // 获取商户列表
        $merchList = $this->get_merch();

        $bol = [];

        $this->assign('merchList',$merchList);
        $this->assign('bol',$bol);
        return view('settlement/add',
            ['title'=>'']
        );
    }


    // 查看项目详细
    public function setinfo(Request $req) {
        $ids = $req->get('ids');
        // 查找数据并显示；
        $data = DB::name('customs_settl')->whereIn('id',$ids)->select();
        $this->assign('data',$data);
        return view('settlement/setinfo',['title'=>'']);
    }

    // 获取商户提单编号
    public function getBill(Request $req) {
        $uid = $req->get('uid');
        // 根据商户ID  获取所有提单编号
        $data = $this->getbol($uid);
        $str = '';
        if(!empty($data)) {
            $str .= '<select name="bill" lay-filter="selBill" lay-verify="required"><option value="">请选择提单编号</option>';
            foreach ($data as $v) {
                $str .= "<option value='{$v}'>{$v}</option>";
            }
            $str .= '</select>';
        }
        return ['code'=>1,'msg'=>$str];
    }

    // 根据提单编号获取批次编号
    public function getBatch(Request $req) {
        $bill = $req->get('bill');
        // 查询批次列表
        $data = DB::name('customs_batch')->where('bill_num',$bill)->field('batch_num')->order('id desc')->select();
        $str = '';
        if(!empty($data)) {
            $str .= '<select name="batch" lay-filter="selBatch" lay-verify="required"><option value="">请选择批次编号</option>';
            foreach ($data as $v) {
                $str .= "<option value='{$v['batch_num']}'>{$v['batch_num']}</option>";
            }
            $str .= '</select>';
        }
        return ['code'=>1,'msg'=>$str];
    }


    // 获取用户提单 所属用户的提单；
    protected function getbills($cid) {
        // T2.user_name
        $sql = "SELECT T2.id,T2.parent_id FROM ( SELECT   @r AS _id,   @stop:=@stop+if(@r=2,1,@stop) as stop,(SELECT @r := parent_id FROM `ims_decl_user` WHERE id = _id) AS parent_id,@l := @l + 1 AS lvl  FROM (SELECT @r := {$cid}, @l := 0, @stop:=0) vars,  `ims_decl_user` h WHERE @stop < 1) T1 JOIN `ims_decl_user` T2 ON T1._id = T2.id"; //ORDER BY T1.lvl DESC
        $pid = '';
        $newArrs = [];
        $s =Db::query($sql);
        foreach ($s as $k => $v) {
            $newArrs[] = $v['id'];
        }
        return $newArrs;
    }

    // 获取全部提单
    protected function getbol($uid){
        $userInfo = $this->getbills($uid);
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

    public function getadd(Request $req) {

        $did = $req->get('did');

        $set1 = $did.'1';
        $set2 = $did.'2';
        $set3 = $did.'3';

        $layk1 = $did + 1;
        $layk2 = $layk1 + 1;

        $arrNum = $did - 1;

        $html = <<<HTML
<div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">执行周期</label>
                <div class="layui-input-inline" style="width: 118px;">
                    <input type="text" class="layui-input" name="start[{$arrNum}]" id="start{$did}" placeholder="请选择开始日期" lay-key="{$layk1}" lay-verify="required">
                </div>
                <div class="layui-form-mid">-</div>
                <div class="layui-input-inline" style="width: 118px;">
                    <input type="text" class="layui-input" name="end[{$arrNum}]" id="end{$did}" placeholder="请选择结束日期" lay-key="{$layk2}" lay-verify="required">
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">项目名称</label>
            <div class="layui-input-block">
                <input type="text" name="project[{$arrNum}]" placeholder="请填写项目名称" lay-verify="required" autocomplete="off" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">计费方式</label>
            <div class="layui-input-block">
                <select name="goType[{$arrNum}]" id="" lay-filter="mytoType">
                    <option did="0">请选择计费方式</option>
                    <option value="1" did="{$set1}" num="{$did}">票</option>
                    <option value="2" did="{$set2}" num="{$did}">金额</option>
                    <option value="3" did="{$set3}" num="{$did}">时间</option>
                </select>
            </div>
        </div>

        <!-- 按订单票数 -->
        <div class="layui-form-item hide" id="sel{$set1}">
            <label class="layui-form-label">按票</label>
            <div class="layui-input-inline" style="width: 100px;">
                <input type="text" name="money[{$arrNum}]" placeholder="X元" autocomplete="off" class="layui-input">
            </div>
            <div class="layui-form-mid">元 / 票</div>
        </div>

        <!-- 按订单总额 -->
        <div class="layui-form-item hide" id="sel{$set2}">
            <label class="layui-form-label">按订单总额</label>
            <div class="layui-input-inline" style="width: 100px;">
                <input type="text" name="percen[{$arrNum}]" placeholder="x%" minlength="1"  max="100" autocomplete="off" class="layui-input">
            </div>
            <div class="layui-form-mid">%</div>
        </div>


        <!-- 按天收费 -->
        <div class="layui-form-item hide" id="sel{$set3}">
            <div class="layui-inline">
                <label class="layui-form-label">按天算</label>
                <div class="layui-input-inline" style="width: 100px;">
                    <input type="text" class="layui-input" name="day[{$arrNum}]" placeholder="请输入天数量" min="1" max="365">
                </div>
                <div class="layui-form-mid">天 / 收</div>
                <div class="layui-input-inline" style="width: 100px;">
                    <input type="text" class="layui-input" name="dayMoney[{$arrNum}]" placeholder="请输入金额">
                </div>
                <div class="layui-form-mid">元</div>
            </div>
        </div>

        <div class="layui-form-item"><div class="layui-input-block"><hr></div></div>
HTML;
        return json(['code'=>1,'msg'=>$html]);

    }


    // 数据添加操作
    public function doadd(Request $req) {
        $data = $req->post();

        $insData = [];
        $insData['uid']   = trim($data['uid']);
        $insData['bill']  = trim($data['bill']);
        $insData['batch'] = trim($data['batch']);
        $insData['mark']  = 2;
        $insData['atime'] = time();

        // 数据数组
        $start      = $data['start'];
        $end        = $data['end'];
        $project    = $data['project'];
        $goType     = $data['goType'];
        $money      = $data['money'];
        $percen     = $data['percen'];
        $day        = $data['day'];
        $dayMoney   = $data['dayMoney'];

        // 数据库ID
        $insId = '';

        $count = count($data['start']);
        // 大于1 循环
        if($count > 1) {

            $tmp = [];
            for($i=0;$i<$count;$i++) {

                $inSet['start'] = isset($start[$i]) ? strtotime($start[$i]) : '';
                $inSet['end']   = isset($end[$i])   ? strtotime($end[$i]) : '';
                $inSet['project'] = isset($project[$i]) ? $project[$i] : '';
                $inSet['goType']  = isset($goType[$i]) ? $goType[$i] : '';
                $inSet['money']   = isset($money[$i]) ? $money[$i] : '';
                $inSet['percen']  = isset($percen[$i]) ? $percen[$i] : '';
                $inSet['day']     = isset($day[$i]) ? $day[$i] : '';
                $inSet['dayMoney'] = isset($dayMoney[$i]) ? $dayMoney[$i] : '';
                $inSet['uid']       = trim($data['uid']);
                $inSet['marks']     = 2;
                // 分段
                $sjduan = (intval(strtotime($end[$i])) - intval(strtotime($start[$i])));
                $t = 86400;
                $inSet['more']      = intval($sjduan / $t) > 0 ? intval($sjduan / $t) :1;// 总分段
                $inSet['nowNum']    = 1;// 当前次数
                $inSet['nexTime']   = strtotime($start[$i]);// 下次执行时间；为当前结束时间，默认一天；

                //  如果收费计算类型为3  ;计算出第一次执行时间，并计算执行次数；
                if($goType[$i] == '3') {
                    // $nexTime = ($newTime + ($day * 86400))
                    // 向上取整，

                    if($day[$i] > 1) {
                        // 结束时间减去，开始时间；  计算出时间段；
                        //$sjduan = (intval(strtotime($end[$i])) - intval(strtotime($start[$i])));
                        $days = ($day[$i] - 1);
                        // 计算 相隔多少天收费一次
                        $time = ($day[$i] * 86400);
                        // 直接取整数，不要小数
                        $more = intval($sjduan / $time);
                        // 下次执行时间 当前开始时间 + (x天 * 86400)
                        $nexTime = (strtotime($start[$i]) + ($days * 86400));
                        // 当前次数
                        $nowNum = 1;// 如果等于最后一次，执行周期完成；

                        $inSet['more'] = $more;// 总分段
                        $inSet['nowNum'] = $nowNum;// 当前次数
                        $inSet['nexTime'] = $nexTime;// 下次执行时间；
                    }

                }

                $id = DB::name('customs_settl')->insertGetId($inSet);
                // 循环入库，并记录新增ID
                $insId .= $id.',';
                //$tmp[] = $inSet;
            }
            //return ['msg'=>$tmp];

        } else if($count == 1) {
            $i = 0;
            $inSet['start']   = isset($start[$i]) ? strtotime($start[$i]) : '';
            $inSet['end']     = isset($end[$i])   ? strtotime($end[$i])   : '';
            $inSet['project'] = isset($project[$i]) ? $project[$i]        : '';
            $inSet['goType']  = isset($goType[$i])  ? $goType[$i]         : '';
            $inSet['money']   = isset($money[$i])   ? $money[$i]          : '';
            $inSet['percen']  = isset($percen[$i])  ? $percen[$i]         : '';
            $inSet['day']     = isset($day[$i])     ? $day[$i]            : '';
            $inSet['dayMoney'] = isset($dayMoney[$i]) ? $dayMoney[$i]     : '';
            $inSet['uid']       = trim($data['uid']);
            $inSet['marks']     = 2;

            /**
             * 没有天数，默认都是一天；
             */
            $sjduan = (intval(strtotime($end[$i])) - intval(strtotime($start[$i])));
            $t = 86400;
            $inSet['more']      = intval($sjduan / $t) > 0 ? intval($sjduan / $t) :1;// 总分段
            $inSet['nowNum']    = 1;// 当前次数
            $inSet['nexTime']   = strtotime($start[$i]);// 下次执行时间；为当前结束时间，默认一天；

            //  如果收费计算类型为3  ;计算出第一次执行时间，并计算执行次数；
            if($goType[$i] == '3') {
                // $nexTime = ($newTime + ($day * 86400))
                // 向上取整，

                if($day[$i] > 1) {
                    // 结束时间减去，开始时间；  计算出时间段；

                    $days = ($day[$i] - 1);
                    // 计算 相隔多少天收费一次
                    $time = ($day[$i] * 86400);
                    // 向上取整
                    $more = intval($sjduan / $time);
                    // 下次执行时间 当前开始时间 + (x天 * 86400)
                    $nexTime = (strtotime($start[$i]) + ($days * 86400));
                    // 当前次数
                    $nowNum = 1;// 如果等于最后一次，执行周期完成；

                    $inSet['more'] = $more;// 总分段
                    $inSet['nowNum'] = $nowNum;// 当前次数
                    $inSet['nexTime'] = $nexTime;// 下次执行时间；
                }

            }

            $id = DB::name('customs_settl')->insertGetId($inSet);
            // 循环入库，并记录新增ID
            $insId .= $id.',';
        }

        $insData['relid'] = trim($insId,',');

        DB::name('customs_settlconfig')->insertGetId($insData);

        return ['code'=>1,'msg'=>'新增成功!'];
    }


    // 删除操作
    public function del(Request $req) {
        $id = $req->post('id');
        $del = DB::name('customs_settl')->where('id',$id)->delete();
        if($del) {
            return ['code'=>1,'msg'=>'删除成功!'];
        }
        return ['code'=>0,'msg'=>'删除失败!'];
    }

    private function Msg($d) {
        echo '<pre>';
        print_r($d);
        die;
    }

    // 获取商户
    private function get_merch() {
        $list = DB::name('decl_user')->field('id,user_name')->select();
        return $list;
    }

}