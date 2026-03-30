<?php

namespace app\superadmin\logic;
use think\Model;
use think\Loader;
use PHPExcel;
use PHPExcel_IOFactory;
use think\Db;
class LotteryPool extends Model
{

    /**
     * 获取所有方案号池
     */
    public function getAllPlan()
    {
        $model = $this->getModel('LotteryPool');
        $total = $model->getMonthPlanCount();
        $data  = $model->getMonthPlan(15,['query' => ['s' => 'superadmin/pool_list'], 'var_page' => 'page',],$total);
        $page  = $data->render();
        $convData  = $data->toArray();
        $convData  = $convData['data'];
        foreach ($convData as $key => $val){
            $convData[$key]['acceptNum'] = $model->getAcceptCount($val['id']);
            $convData[$key]['appNum']     = $model->appleCount($val['id']);
            $convData[$key]['poolNum']   = $model->getPoolCount($val['id']);
        }
        return [$page,$convData];
    }

    public  function sendWxMsg ($uniacid, $openid, $title, $conten, $url = null, $rem = null,$resu='通过' )
    {
        $template     = [
            'touser' => $openid,
            'template_id' => 'nXuU7WtKzVn-WZJoroWaO8Dev6yi__lEHB-gP__PTr0',
            'url' => $url,
            'data' => [
                'first' => [
                    'value' => $title,
                ],
                'keyword1' => ['value' => $resu],
                'keyword2' => ['value' => date('Y-m-d H:i:s', time())], 'keyword3' => ['value' => $conten], 'remark' => ['value' => $rem]]];//消息模板
        $ASSESS_TOKEN = RequestAccessToken($uniacid);
        $hosts        = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $ASSESS_TOKEN;
        $res =httpRequest($hosts, json_encode($template));
        unset($res);
    }

    public function saveRoundData($data)
    {
        $data = $data['data'];
        if(empty($data))return '错误';
        $mode = $this->getModel('LotteryPool');
        $mid = $data['mid'];
        $lottery_type = $data['lottery_type'];
        $lottery_addr = $data['lottery_addr'];
        unset($data['mid'],$data['lottery_type'],$data['lottery_addr']);
        $datas = $this->withArray($data);
        if ($lottery_type == 0){
            list($openid,$err) = $this->isTel($datas);
            if($err != null){
                return '不存在该手机号:'.$err;
            }
        }
      
        $enData = [
            'm_id'=> $mid,
            'round_num'=>json_encode($datas),
            'lottery_type'=>$lottery_type,
            'lottery_addr'=>$lottery_addr
        ];
        $err = $mode->insertPool($enData);
        if($err != null){
            return $err;
        }
        if ($lottery_type == 0){
            //系统抽签 发送操作抽签页面
            $lUrl = 'http://shop.gogo198.cn/app/index.php?i='.$openid[0]['uniacid'].'&c=entry&m=ewei_shopv2&do=mobile&r=parking.lottery_month&mid='.$mid.'&n=0';
            $msg = '{"touser":"'.$openid[0]['openid'].'","msgtype":"text","text":{"content":"进行抽签链接：'.$lUrl.'"}}';
            $token = RequestAccessToken($openid[0]['uniacid']);
            $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$token;
            $re = httpRequest($url, $msg,["Content-Type: application/json"]);
        }
        unset($re);
//        $user = $mode->getAllUser($mid);
//        foreach ($user as $value){
//            $this->sendWxMsg($value['uniacid'],$value['user_id'], "您好！你的月卡申请,现定于{$datas[0]['time']}举行摇号抽签：", '摇号抽签','','点击详情,可以查看摇号抽签资讯(倒计时、直播视频)');
//        }
        return null;
    }




    public function withArray($data)
    {
        $tmp = [];
        $num = count($data)/2/2;
        for ($i=0;$i<=$num-1;$i++){
            $tmp[$i]['name']=$data["name[{$i}]"];
            $tmp[$i]['count']=$data["count[{$i}]"];
            $tmp[$i]['time']=$data["time[{$i}]"];
            $tmp[$i]['lottery_user']=$data["lottery_user[{$i}]"];
        }
        return $tmp;
    }

    public function isTel($data)
    {
        $error = null;
        $openid = [];
        $model = $this->getModel('LotteryPool');
        foreach ($data as $val)
        {
            $i = $model->getUser($val['lottery_user']);
            if(!$i) {
                $error = $val['lottery_user'];
                break;
            }
            $openid[]=$i;
        }
        return [$openid,$error];
    }

    public function getModel($name)
    {
        return Loader::model($name,'model');
    }

    /**
     * 轮次管理
     */
    public function getRoundManage()
    {
        $data = $this->getModel('LotteryPool')->getRoundManageList(['type' => 'Layui', 'query' => ['s' => 'superadmin/round_menagem'], 'var_page' => 'page', 'newstyle' => true]);
        $page = $data->render();
        $da   = $data->toArray();
        $da   = $da['data'];
        if (!empty($da)){
            foreach ($da as $key=>&$value){
                $value['round_num'] = json_decode($value['round_num'],true);
            }
        }
        return [$page,$da];
    }

    public function getAllLotteryPlan()
    {
        $dataModel = $this->getModel('LotteryPool');
        $data = $dataModel->getAllPlanAndRound(['type' => 'Layui', 'query' => ['s' => 'superadmin/lottery_menagem'], 'var_page' => 'page', 'newstyle' => true]);
        $newData = $data->toArray()['data'];
        foreach ($newData as &$val){
            $val['round_num'] = json_decode($val['round_num'],true);
        }
        return [$data->render(),$newData];
    }

    public function fetchFormFileAndSave($fileName,$mid)
    {
        $fileName = ROOT_PATH.'public/uploads/xls/'.$fileName;
        $objReader = PHPExcel_IOFactory::createReader( PHPExcel_IOFactory::identify($fileName));
        $PHPExcel = $objReader->load($fileName);
        $curSheet = $PHPExcel->getSheet(0);
        $rowCount = $curSheet->getHighestRow();
        $data = [];
        $n= 0;
        for($i=1;$i<=$rowCount;$i++){
            $data[$n]['flagId']=$curSheet->getCell('A'.$i)->getValue();
            $data[$n]['is_rounds']= $curSheet->getCell('B'.$i)->getValue();
            $n++;
        }
        return $this->withLotteryResult($data,$mid);
    }

    public function withLotteryResult($flagId,$mid)
    {
        $fid = null;
        $isRou = [];
        $uData= [];
        $model = $this->getModel('LotteryPool');
        foreach ($flagId as $k => $v) {
            $fid .= $v['flagId'] . ',';
            array_push($uData, ['is_win' => 1, 'is_round' => $v['is_rounds']]);
        }
        return $model->updateWinStatus(trim($fid,','),$uData);
        /*
        foreach ($flagId as $k=>$v){
    $fid.= $v['flagId'].',';
    $isRou[$v['flagId']] = $v['is_rounds'];
}
$openid = $model->getOpenid(trim($fid,','));
$info = [];
foreach ($openid as $value){
    $info = [
        'moth_id' => $mid,
        'user_id' => $value['user_id'],
        'is_rounds'=>$isRou[$value['flag_id']],
        'is_type'  =>2,
        'uniacid' => $value['uniacid']
    ];
    $this->sendWxMsg($value['uniacid'],$value['user_id'], "您好，你的月卡申请已经获摇号抽签确认", '申请资格','','点击详情，可查看中签名单的公示');
}
$model->updateSeep($fid);
$model->delWin($mid);
return $model->saveWin($info);
        */
    }

    /**返回意见消息
     * @return mixed
     */
    public function fetchAllSay()
    {
        $model = $this->getModel('LotteryPool');
        $result = $model->fetchallSay(['type' => 'Layui', 'query' => ['s' => 'superadmin/say_menage'], 'var_page' => 'page', 'newstyle' => true]);
        return $result;
    }

    public function saveReply($str)
    {
        $str = json_decode($str,true);
        $model = $this->getModel('LotteryPool');
        $data = [
            'user_id'=>'',
            'uniacid'=>0,
            'user_name'=>'管理员',
            'message'=>$str['desc'],
            'pid'   =>$str['id'],
            'create_at'=>time()
        ];
        $this->sendWxText($str);
        return $model->insertSayMsg($data);
    }

    protected function sendWxText($str)
    {
        $openid = Db::name('parking_saymsg')->where('id',$str['id'])->find();
        $msg = '{"touser":"'.$openid['user_id'].'","msgtype":"text","text":{"content":"月卡意见回复：'.$str['desc'].'"}}';
        $token = RequestAccessToken($openid['uniacid']);
        $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$token;
        httpRequest($url, $msg,["Content-Type: application/json"]);
    }

    public function confirmWin($mid)
    {
        if(empty($mid)){return '参数不能空';}
        $model = $this->getModel('LotteryPool');
        $User = $model->getWinUser($mid);
        $roundScores = Db::name('parking_pool')->field('round_num')->where('m_id',$mid)->find();
        $roundScores['round_num'] = json_decode($roundScores['round_num'],true);
        if(empty($User)){return '中签方案用户空';}
        foreach ($User as $value){
            $host = 'http://shop.gogo198.cn/app/index.php?i='.$value['uniacid'].'&c=entry&m=ewei_shopv2&do=mobile&r=parking.monthcardpay&mid='.$mid;
            $this->sendWxMsg($value['uniacid'],$value['user_id'],'您好，你的月卡申请的中签资格公示已获通过','在线支付月卡费用，逾期支付，月卡中签资格失效及被候补名单替补。',$host,'点击支付','中签通过:'.$roundScores['round_num'][$value['is_round']-1]);
        }
        unset($roundScores,$User);
        return null;
    }



}