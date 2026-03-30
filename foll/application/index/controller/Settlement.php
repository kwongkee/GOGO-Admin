<?php

namespace app\index\controller;

use think\Controller;
use think\Queue;
use think\Db;


/**
 * Class Settlement
 * @package app\index\controller
 *  2020-03-27
 */
class Settlement{
    /**
     *  获取周期结算列表
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     *  加入自动执行；每天执行一次；
     */
    public function queueTest()
    {
        // http://shop.gogo198.cn/foll/public/?s=settlement/test
        /**
         *  获取周期结算列表  customs_settlconfig;
         *  条件： mark =2;
         */
        try {

            $set = [];
            // 当日时间
            $nowTime = strtotime(date('Y-m-d'));
            // 获取数据
            $settl = Db::name('customs_settlconfig')->where('mark','<>', 1)->field('relid,id,batch')->select();
            // 不为空查询  周期项目
            if (!empty($settl)) {
                // 循环数据
                foreach ($settl as $v) {
                    // 循环查询 周期项目表  customs_settl
                    if (!empty($v['relid'])) {

                        // 查询周期下项目数据
                        $settlData = Db::name('customs_settl')->whereIn('id', $v['relid'])->where('marks', '<>', 1)->select();
                        if (empty($settlData)) { // 表示数据执行完成；更新外部状态，为1；
                            // 更新状态为1；执行完成
                            Db::name('customs_settlconfig')->where('id', $v['id'])->update(['mark' => 1]);
                            // 数据为空，跳出循环
                            continue 1;
                        }

                        // 循环判断时间是否符合，
                        $set[] = $res = $this->gotoSett($settlData, $nowTime, $v['batch']);
                        // 更新外层状态为，执行中
                        Db::name('customs_settlconfig')->where('id', $v['id'])->update(['mark' => 3]);

                        // 执行完一次，写入一次数据库
                        // 执行完成  不为空，就写入对账单表；
                        if (!empty($res)) {
                            Db::name('customs_settbill')->insertAll($res);
                        }
                    }
                }
            }

        } catch (\Exception $e) {

            $msg = $e->getMessage();
            $this->Msg($msg);

        }

        echo 'success';
        //$this->Msg($set);
    }

    // 根据查询的数据判断是否符合 当前时间；
    private function gotoSett($data,$nowTime,$batch) {
        // 记录对账数据，统一写入数据库
        $now = '';
        $tmpArr = [];
        foreach ($data as $k=>$dv) {

            // 当前时间，大于等于开始时间，小于等于结束时间
            if(($nowTime >= $dv['start']) && ($nowTime <= $dv['end'])) {

                $nexTime = $dv['nexTime'];// 下次执行时间
                $more    = $dv['more'];   // 总执行数
                $nowNum  = $dv['nowNum']; // 当前执行数量

                // 符合项目时间，判断选用计算方式；  1:票，2：金额，3：时间,计费方式
                switch ($dv['goType']) {

                    case 1:// 查询该批次下的订单总票数，支付成功的；  订单票数 * 金额；

                        //$now .= '计费类型：1；当前时间：'.date('Y-m-d',$nowTime).'<>下次执行时间'.$nexTime.'<br>';
                        if($nowTime  == $nexTime) {

                            //$now .= '符合条件的ID：'.$dv['id'].'<br>';

                            $count = $this->getbatchCount($batch);
                            $money = sprintf("%.2f", ($count * $dv['money']));
                            $desc = '按：【' . $batch . '】批次，订单数据总数' . $count . ' * ' . $dv['money'];
                            //echo $desc.'<br>';
                            $tmp['uid']         = $dv['uid'];
                            $tmp['project']     = $dv['project'];
                            $tmp['billTime']    = $nowTime;
                            $tmp['status']      = 2;
                            $tmp['atime']       = time();
                            // 计费描述
                            $tmp['desc']        = $desc;
                            $tmp['money']       = $money;

                            array_push($tmpArr,$tmp);
                            $nowNum1 = $nowNum;
                            // 如果等于最后一次执行，更新当前状态为完成
                            if($nowNum == $more) {
                                Db::name('customs_settl')->where('id',$dv['id'])->update(['marks'=>1]);
                            } else {
                                ++$nowNum1;
                            }

                            $newsTime1 = (86400 + $dv['nexTime']);
                            // 更新下次执行时间，次数
                            Db::name('customs_settl')->where('id',$dv['id'])->update([
                                'nexTime'=>$newsTime1,'nowNum'=>$nowNum1
                            ]);

                        }
                        break;

                    case 2:// 金额  按批次总额计算
                        //$now .= '计费类型：2；当前时间：'.date('Y-m-d',$nowTime).'<>下次执行时间'.$nexTime.'<br>';

                        if($nowTime  == $nexTime) {
                            //$now .=  '符合条件的ID：'.$dv['id'].'<br>';

                            $totalMoney = $this->getbatchCount($batch, 2);
                            // 计算百分比
                            $percens = sprintf("%.2f", ($dv['percen'] / 100));
                            // 计算收费金额
                            $money = sprintf("%.2f", ($totalMoney * $percens));

                            $desc = '按：【' . $batch . '】批次，订单数据总额' . $totalMoney . ' * ' . $percens;
                            //echo $desc.'<br>';
                            $tmp['uid'] = $dv['uid'];
                            $tmp['project'] = $dv['project'];
                            $tmp['billTime'] = $nowTime;
                            $tmp['status'] = 2;
                            $tmp['atime'] = time();
                            // 计费描述
                            $tmp['desc'] = $desc;
                            $tmp['money'] = $money;
                            $nowNum2 = $nowNum;
                            // 如果等于最后一次执行，更新当前状态为完成
                            if($nowNum == $more) {
                                Db::name('customs_settl')->where('id',$dv['id'])->update(['marks'=>1]);
                            } else {
                                ++$nowNum2;
                            }

                            $newsTime2 = (86400 + $dv['nexTime']);
                            // 更新下次执行时间，次数
                            Db::name('customs_settl')->where('id',$dv['id'])->update([
                                'nexTime'=>$newsTime2,'nowNum'=>$nowNum2
                            ]);
                            array_push($tmpArr,$tmp);

                        }
                        break;

                    case 3:// 时间,计费方式 按时间查询
                        //$now .= '计费类型：3；当前时间：'.date('Y-m-d',$nowTime).'<>下次执行时间'.$nexTime.'<br>';

                        // 如果当前执行时间 == 下次执行时间，就写入
                        if($nowTime  == $nexTime) {
                            //$now .=  '符合条件的ID：'.$dv['id'].'<br>';

                            $desc = '按每：'.$dv['day'] .' 天 / 收 '.$dv['dayMoney'] .' 元';

                            //echo $desc.'<br>';
                            $nowNum3 = $nowNum;
                            // 如果等于最后一次执行，更新当前状态为完成
                            if($nowNum == $more) {
                                Db::name('customs_settl')->where('id',$dv['id'])->update(['marks'=>1]);
                            } else {
                                ++$nowNum3;
                            }
                            $money = sprintf("%.2f",$dv['dayMoney']);
                            $tmp['uid']      = $dv['uid'];
                            $tmp['project']  = $dv['project'];
                            $tmp['billTime'] = $nowTime;
                            $tmp['status']   = 2;
                            $tmp['atime']    = time();
                            // 计费描述
                            $tmp['desc']     = $desc;
                            $tmp['money']   = $money;
                            // 执行完成，下次时间+1；
                            $newsTime3 = (($dv['day'] -1 ) * 86400) + $dv['nexTime'];
                            // 更新下次执行时间，次数
                            Db::name('customs_settl')->where('id',$dv['id'])->update([
                                'nexTime'=>$newsTime3,'nowNum'=>$nowNum3
                            ]);
                            array_push($tmpArr,$tmp);
                        }
                        break;
                }

                // 否则修改状态为：3
                $this->upMarks($dv['id'],3);
                // 如果时间等于最后一天，那么数据状态修改为，执行完成；
                if($dv['end'] == $nowTime) {
                    // 修改状态 修改为1；
                    $this->upMarks($dv['id'],1);
                }
            }

        }
        // 返回已执行的结算；
        return $tmpArr;
    }


    private function upMarks($id,$status) {
        Db::name('customs_settl')->where('id',$id)->update(['marks'=>$status]);
    }

    // 获取申报订单总票数

    /**
     * @param $batch     批次编号
     * @param int $type  1 查询订单票数，2查询订单总额；
     * @return float|int|string
     *
     */
    private function getbatchCount($batch,$type=1) {
        // 获取批次订单数量
        $num = Db::name('customs_elec_order_detail')->where('batch_num',$batch)->where('sbStatus',1)->count();

        if($type == 2) {
            // 获取批次订单总额
            $num = Db::name('customs_elec_order_detail')->where('batch_num',$batch)->where('sbStatus',1)->sum('OrderGoodTotal');
        }
        // 根据批次查找数据
        return $num;
    }

    private function Msg($da){
        echo '<pre>';
        print_r($da);
        die;
    }
}

?>