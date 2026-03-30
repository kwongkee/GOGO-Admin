<?php
if (!defined('IN_IA'))
{
    exit('Access Denied');
}

class Lottery_month_EweiShopV2Page extends mobilePage
{
    public function main()
    {
        global $_W;
        global $_GPC;
        if(!isset($_GPC['mid'])||empty($_GPC['mid'])){
            message('错误');
        }

        $month = pdo_get('parking_month_type',['id'=>$_GPC['mid'],'month_status'=>1]);
        $isWin = pdo_get('parking_apply',['m_id'=>$_GPC['mid'],'is_win'=>1]);
        if(!empty($isWin)){
            message('链接已失效');
        }

        if (empty($month)){
            message('月卡禁用状态');
        }

        $round = pdo_fetch("select * from ".tablename('parking_pool')." where m_id=".$_GPC['mid']." order by id desc");
        $hasTel = pdo_get('parking_authorize',['openid'=>$_W['openid']],['mobile']);
        $round['round_num'] = json_decode($round['round_num'],true);
        $round['lun'] = $round['round_num'][$_GPC['n']];
        $time = strtotime($round['lun']['time']);
        $time2 = time();
        
        if($round['lun']['lottery_user']!=$hasTel['mobile'] ){
            message('抽签人不匹配');
        }

        if(date('d',time())>date('d',$time)){
            message('不在抽签时间');
        }
        include $this->template('parking/lottery_month');
    }

    /**
     * 开始抽签
     */

    public function startLottery()
    {
        global $_W;
        global $_GPC;
        $pool = pdo_get('parking_pool',['m_id'=>$_GPC['mid']]);
        $pool['round_num'] = json_decode($pool['round_num'],true);
        $count = $pool['round_num'][$_GPC['n']]['count'];
        unset($pool);
        $userList = pdo_fetchall('select * from '.tablename('parking_lotter_total').' where moth_id='.$_GPC['mid']);
        if (is_null($userList)){
            show_json(-1,'没有数据');
        }
        $userTotal = count($userList);
        echo $userTotal;exit();
        if($count>=$userTotal){
            $this->saveWin($_GPC,$userList,$_W['uniacid']);
            show_json(0,'完成');
        }
        $win = [];
        for($i=0;$i<$count;$i++){
            shuffle($userList);
            $randNum=mt_rand(0,$userTotal-1);
            if($userList[$randNum]){
                array_push($win,$userList[$randNum]);
            }else{
                $randNum=mt_rand(0,$userTotal-1);
                array_push($win,$userList[$randNum]);
            }
        }
        if($this->saveWin($_GPC,$win,$_W['uniacid'])){
            show_json(0,'完成');
        }
        show_json(-1,'失败');
    }

    protected function saveWin($num,$user,$u)
    {
        $num =$num['n']+1;
        $data = [];
        $userid = [];
        foreach ($user as $value){
            array_push($userid,['user_id'=>$value['user_id']]);
            array_push($data,['is_win'=>1,'is_round'=>$num]);
        }
        $result = pdo_update('parking_apply',$data,$userid);
        if (!empty($result)) {
           return true;
        }
        return false;
//        pdo_insert('parking_lotter_win',[
//            'moth_id'=>$value['moth_id'],
//            'user_id'=>$value['user_id'],
//            'is_rounds'=>$num,
//            'is_type'=>1,
//            'uniacid'=>$u
//        ]);
    }

    /**
     * 更换抽签人
     */
    public function changeLottery()
    {
        global $_W;
        global $_GPC;
        if (empty($_GPC['tel'])){
            header("Location:".mobileUrl('parking.lottery_month')."&mid=".$_GPC['mid']."&n=".$_GPC['n']);
            exit();
        }
        $hasTel = pdo_get('parking_authorize',['mobile'=>$_GPC['tel']],['id']);
        if (empty($hasTel['id'])){
            message('不存在电话号码',mobileUrl('parking.lottery_month')."&mid=".$_GPC['mid']."&n=".$_GPC['n']);
            exit();
        }
        $pool = pdo_get('parking_pool',['m_id'=>$_GPC['mid']]);
        $pool['round_num'] = json_decode($pool['round_num'],true);
        $pool['round_num'][$_GPC['n']]['lottery_user'] = $_GPC['tel'];
        $pool['round_num'] = json_encode($pool['round_num']);
        pdo_update('parking_pool',['round_num'=>$pool['round_num']],['m_id'=>$_GPC['mid']]);
        message('完成');

    }

    public function getFetchWinList()
    {
        global $_W;
        global $_GPC;
        $sql = "select a.*,b.scheme_name,b.month_name,c.mobile,d.round_num from ".tablename('parking_lotter_win')." as a left join ".tablename('parking_month_type')." as b on a.moth_id=b.id left join ".tablename('parking_authorize')." as c on a.user_id=c.openid left join ".tablename('parking_pool')." as d on a.moth_id=d.m_id where a.moth_id=".$_GPC['mid']." limit 0,100";
        $list = pdo_fetchall($sql);
        $html = null;
        foreach ($list as $value){
            $round = json_decode($value['round_num'],true);
            $html.='<li><span>'.$value['scheme_name'].'</span><span>'.$round[$value['is_rounds']-1]['name'].'</span><span>'.$value['month_name'].'</span><span>'.$value['mobile'].'</span></li>';
        }
        show_json(0,$html);
    }
}