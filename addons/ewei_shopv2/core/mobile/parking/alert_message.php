<?php
if (!defined('IN_IA'))
{
    exit('Access Denied');
}

class Alert_Message_EweiShopV2Page extends mobilePage
{
    public function main()
    {

    }

    public function monthMsg()
    {
        global $_W;
        global $_GPC;
        $text = pdo_get('parking_month_alertmsg',['uniacid'=>$_W['uniacid']]);
        include $this->template("parking/month_msg");
    }

    public function monthApplyProcess()
    {
        global $_W;
        global $_GPC;
        if(isset($_GPC['mid'])&&empty($_GPC['mid'])){
            exit();
        }
        $speed = pdo_get('parking_apply',['m_id'=>$_GPC['mid'],'user_id'=>$_W['openid']]);
        if(!$speed){
            message('请先申请该月卡');
        }
        $hasLottery = pdo_get("parking_apply",['user_id'=>$_W['openid'],'is_win'=>1]);
        $userName = pdo_get('parking_verified',['openid'=>$_W['openid']],['uname']);
        $allAplyInfo = pdo_fetchall("select a.*,b.mobile,c.scheme_name,c.month_name from ".tablename('parking_apply')." as a left join ".tablename('parking_authorize')." as b on a.user_id=b.openid left join ".tablename('parking_month_type')." as c on a.m_id=c.id where a.m_id=".$_GPC['mid']." and a.is_accept=1 order by flag_id");
        $listHTML = null;
        foreach ($allAplyInfo as $value){
               $listHTML.='<li>';
               $listHTML.='<span>'.$value['month_name'].'</span>';
               if ($userName['uname']==$value['user_name']){
                   $listHTML.='<span>'.$value['user_name'].'</span>';
                   $listHTML.='<span>'.$value['flag_id'].'</span>';
                   $listHTML.='<span>'.$value['mobile'].'</span>';
               }else{
                   $listHTML.='<span>'.$this->substr_cut($value['user_name']).'</span>';
                   $listHTML.='<span>'.$value['flag_id'].'</span>';
                   $listHTML.='<span>'.substr_replace($value['mobile'],'****',3,4).'</span>';
               }
               $listHTML.='</li>';
        }
        include $this->template("parking/month_process");

    }

    public function fetchPlan()
    {
        global $_W;
        global $_GPC;

        $allAplyInfo = pdo_fetchall("select a.*,b.mobile,c.scheme_name,c.month_name from ".tablename('parking_apply')." as a left join ".tablename('parking_authorize')." as b on a.user_id=b.openid left join ".tablename('parking_month_type')." as c on a.m_id=c.id ".' where a.is_accept=1 and a.m_id !='.$_GPC['mid'].' order by flag_id');
        $listHTML = null;
        foreach ($allAplyInfo as $value){
            $listHTML.='<li>';
            $listHTML.='<span>'.$value['month_name'].'</span>';
            $listHTML.='<span>'.$this->substr_cut($value['user_name']).'</span>';
            $listHTML.='<span>'.$value['flag_id'].'</span>';
            $listHTML.='<span>'.substr_replace($value['mobile'],'****',3,4).'</span>';
            $listHTML.='</li>';
        }
        include $this->template("parking/all_month_applyplan");
    }

    public function substr_cut($name)
    {
        $stlen = mb_strlen($name,'utf-8');
        if($stlen<2){
            return $name;
        }else{
            $firstStr = mb_substr($name, 0, 1, 'utf-8');
            $lastStr = mb_substr($name, -1, 1, 'utf-8');
            return $stlen == 2 ? $firstStr . str_repeat('*', mb_strlen($name, 'utf-8') - 1) : $firstStr . str_repeat("*", $stlen - 2) . $lastStr;
        }
    }

    public function monthCommon()
    {
        global $_W;
        global $_GPC;
        $sql= "select a.is_round,a.flag_id,a.user_name,b.round_num,c.mobile from ".tablename('parking_apply')." as a left join ".tablename('parking_pool')." as b on a.m_id=b.m_id left join ".tablename('parking_authorize')." as c on a.user_id=c.openid left join ".tablename('parking_month_type')." as d on a.m_id=d.id where a.uniacid=".$_W['uniacid']." and a.is_win=1 limit 0,500";
        $allData = pdo_fetchall($sql);
        foreach ($allData as $k=>&$v){
            $v['round_num'] =  json_decode($v['round_num'],true);
            $v['lun']       = $v['round_num'][$v['is_rounds']-1];
            $v['mobile']    =  substr_replace($v['mobile'],'****',3,4);
            $v['flag_id']   = substr_replace($v['flag_id'],'**',1,2);
            $v['user_name'] =$this->substr_cut($v['user_name']);
            unset($v['round_num']);
        }
        include $this->template('parking/month_commmsg');
    }

    public function saveSay()
    {
        global $_W;
        global $_GPC;
        $data = [
            'user_id' => $_W['openid'],
            'uniacid' => $_W['uniacid'],
            'user_name'=> $_W['fans']['nickname'],
            'message'  => $_GPC['message'],
            'create_at'=> time()
        ];
        try{
            pdo_insert('parking_saymsg',$data);
            message('完成',mobileUrl('parking/alert_message/monthCommon'));
        }catch (Exception $e){
           message($e->getMessage());
        }
    }


    public function lotteryProcess()
    {
        global $_W;
        global $_GPC;
        $time = '';
        $time2 = time();
        include $this->template("parking/lottery_process");
    }
}