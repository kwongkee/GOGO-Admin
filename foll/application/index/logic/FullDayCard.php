<?php

namespace app\index\logic;

use http\Env\Request;
use think\Model;
use think\Validate;
use think\Db;
use think\Loader;
use PHPExcel;
use PHPExcel_IOFactory;
class FullDayCard extends Model
{

    public $param = ['type' => 'Layui', 'query' => ['s' => 'month_card/index'], 'var_page' => 'page', 'newstyle' => true];

    protected $error = null;


    public function get_total($tableName, $countField)
    {
        return Db::name($tableName)->where('uniacid', Session('UserResutlt')['uniacid'])->count($countField);
    }

    public function delete_list($tableName, $where)
    {
        Db::name($tableName)->where($where)->delete();
    }

    /**
     * 获取所有发行月卡
     * @param $model
     * @return array
     */
    public function getAll($model)
    {
        $data = $model->lists($this->get_total('parking_month_type', 'id'), ['uniacid' => Session('UserResutlt.uniacid')], 10, $this->param);
        $page = $data->render();
        $list = $data->toArray();
        $list = $this->formatDate($list['data']);

        return ['list' => $list, 'page' => $page];
    }

    /**
     * 格式数据状态
     * @param $data
     * @return format data
     */
    public function formatDate($data)
    {
        $ischeck = ['待审核', '已审核', '已拒绝'];
        $m_status = ['失效', '有效'];
        foreach ($data as &$val) {
            $val['total_times'] = date('Y-m-d H:i', $val['total_times']);
            $val['total_timeer'] = date('Y-m-d H:i', $val['total_timeer']);
            $val['apply_start'] = date("Y-m-d H:i", $val['apply_start']);
            $val['apply_end'] = date("Y-m-d H:i", $val['apply_end']);
            $val['accept_review'] = date('Y-m-d H:i', $val['accept_review']);
            $val['accept_review2'] = date('Y-m-d H:i', $val['accept_review2']);
            $val['lottery_pay'] = date('Y-m-d H:i', $val['lottery_pay']);
            $val['lottery_pay2'] = date('Y-m-d H:i', $val['lottery_pay2']);
            $val['expir_start'] = date('Y-m-d H:i', $val['expir_start']);
            $val['expir_end'] = date('Y-m-d H:i', $val['expir_end']);
            $val['is_check'] = $ischeck[$val['is_check']];
            $val['month_status'] = $m_status[$val['month_status']];
        }
        return $data;
    }

    /**
     * save month data
     * return result array
     */

    public function saveMonth($data, $model)
    {
        if ($this->isFieldNull($data)) {
            return ['code' => '-1', 'msg' => '条件所需参数不能为空'];
        }

        if (!is_null($this->hasMark($data['mark']))) {
            return ['code' => '-1', 'msg' => '标识重复' . $data['mark']];
        }
        return $model->insertTable($this->format_data($data));
    }

    public function hasMark($mrk)
    {
        return Db::name('parking_month_type')->where('mark', $mrk)->field('id')->find();
    }


    public function updateMonthStatus($id, $statis)
    {
        Db::name('parking_month_type')->where('id', $id)->update(['month_status' => $statis]);
    }

    /**处理待受理数据
     * @param $model
     * @return array
     */
    public function getApplyList($model)
    {
        $total = $model->getApplyUserTotal();
        $result = $model->getApplyUserList($total, ['query' => ['s' => 'month_card/month_applyaccept'], 'var_page' => 'page',]);
        $page = $result->render();
        $data = $result->toArray();
        foreach ($data['data'] as $key => $val) {
            $data['data'][$key]['sub_info'] = explode(',', trim($val['sub_info'], ','));
            $data['data'][$key]['mobile'] = substr_replace($data['data'][$key]['mobile'],'****',3,4);
        }
        $isAepNum =  $model->isAepNum();//通过受理数
        $notAepNum = $model->notAepNum(['is_accept'=>0]);//未予通过数
        $refAepNum = $model->refAepNum(['is_accept'=>2]);//拒绝数
        $toAepNum = $model->totalAepNum(['uniacid'=>Session('UserResutlt.uniacid')]);//总申请人数
        return [$page, $data,[$isAepNum,$notAepNum,$refAepNum,$toAepNum]];
    }

    protected function isFieldNull($data)
    {
        switch ($data['fit_type']) {
            case "0":
                return false;
            case "1":
                return empty($data['road_rand']) && empty($data['road_rand1']) ? true : false;
            case "2":
                return empty($data['park']) && empty($data['park1']) ? true : false;
            case "3":
                return empty($data['period']) ? true : false;
            default:
                return false;
        }
    }

    public function format_data($data)
    {
        $tmp = [
            'uniacid' => Session('UserResutlt.uniacid'),
            'scheme_name' => $data['scheme_name'],
            'total_times' => strtotime(trim($data['total_time'])),
            'total_timeer' => strtotime(trim($data['total_time2'])),
            'apply_start' => strtotime(trim($data['apply'])),
            'apply_end' => strtotime(trim($data['apply2'])),
            'accept_review' => strtotime(trim($data['accept'])),
            'accept_review2' => strtotime(trim($data['accept2'])),
            'lottery_pay' => strtotime(trim($data['lottery'])),
            'lottery_pay2' => strtotime(trim($data['lottery2'])),
            'expir_start' => strtotime(trim($data['expir'])),
            'expir_end' => strtotime(trim($data['expir2'])),
            'month_name' => trim($data['month_name']),
            'month_num' => trim($data['month_num']),
            'bup_num' => trim($data['bup_num']),
            'fit_type' => $data['fit_type'],
            'period' => $data['period'],
            'park' => $data['park'] . '-' . $data['park1'],
            'region' => json_encode([
                's' => [
                    'province' => $data['sheng'],
                    'city' => $data['shi'],
                    'area' => $data['qu']
                ],
                'e' => [
                    'province2' => $data['sheng2'],
                    'city2' => $data['shi2'],
                    'area2' => $data['qu2']
                ]
            ]),
            'road_rand' => $data['road_rand'] . '-' . $data['road_rand1'],
            'month_money' => $data['month_money'],
            'create_at' => time(),
            'mark' => $data['mark']
        ];
        return $tmp;
    }

    public function delMonthFromTable($id)
    {
        $this->delete_list('parking_month_type', ['id' => $id]);
    }

    public function saveUploadCer($name)
    {
        try {
            Db::name('parking_cre')->insert(['name' => $name, 'uniacid' => Session('UserResutlt.uniacid')]);
            return '';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function delTableCer($id)
    {
        $this->delete_list('parking_cre', ['id' => $id]);
    }

    public function getCer()
    {
        return Db::name('parking_cre')->where('uniacid', Session('UserResutlt.uniacid'))->select();
    }


    /**
     * 获取待审核的申请方案列表
     * @return mixed
     */

    public function getReviewPlanList($model=null)
    {
        
        $total = $this->get_total('parking_month_type', 'id');
        $source = Db::name('parking_month_type')
            ->where(['is_check' => 1, 'month_status' => 1])
            ->paginate(15, $total, ['query' => ['s' => 'month_card/month_review'], 'var_page' => 'page']);
        $page = $source->render();
        $data = $source->toArray();
        $data = $data['data'];
        foreach ($data as $key => $val) {
            $num = Db::name('parking_apply')->where(['m_id'=>$val['id'],'is_review'=>0,'is_done'=>'N'])->count('id');
            if ($num==0){
                unset($data[$key]);
                continue;
            }
            $data[$key]['count'] = $this->getApplyCount($val['id']);
            $data[$key]['notAepNum'] = $model->notAepNum(['m_id'=>$val['id'],'is_accept'=>0]);//未予通过数
            $data[$key]['refAepNum'] = $model->refAepNum(['m_id'=>$val['id'],'is_accept'=>2]);//拒绝数
            $data[$key]['toAepNum'] = $model->totalAepNum(['m_id'=>$val['id'],'uniacid'=>Session('UserResutlt.uniacid')]);//总申请人数
        }
        return [$page, $data];

    }


    /**
     * 获取申请通过总数
     * @param $mid
     * @return mixed
     */
    protected function getApplyCount($mid)
    {
        return Db::name('parking_apply')->where(['m_id' => $mid, 'is_accept' => 1])->count('id');
    }


    public function updateApplyStatus($id, $status,$msg)
    {
        $timer = time();
        $openid = Db::name('parking_apply')->where(['id' => $id])->field(['user_id', 'm_id'])->find();
        if (empty($openid)) return [false, '失败'];
        $monthInfo = Db::name('parking_month_type')->where('id', $openid['m_id'])->find();
        if ($timer < $monthInfo['accept_review'] && $timer > $monthInfo['accept_review2']) {
            return [false, '不在受理期'];
        }
        if ($status == 1) {
            //通过
            $ur = 'http://shop.gogo198.cn/app/index.php?i=' . Session('UserResutlt.uniacid') . '&c=entry&m=ewei_shopv2&do=mobile&r=parking.alert_message.monthApplyProcess&mid=' . $openid['m_id'];
            $flag_id = Db::name('parking_apply')->field('max(flag_id)as flag_id')->where('m_id', $monthInfo['id'])->find()['flag_id'];
            if (is_null($flag_id)) {
                $flag_id = $monthInfo['mark'] . '00001';
            } else {
                $flag_id = substr($flag_id, 1, strlen($flag_id));
                $flag_id = (int)$flag_id + 1;
                $flag_id = str_pad($flag_id, 5, 0, STR_PAD_LEFT);
                $flag_id = $monthInfo['mark'] . $flag_id;
            }


            Db::name('parking_apply')->where('id', $id)->update(['is_accept' => $status, 'planned_speed' => 1, 'flag_id' => $flag_id]);
            self::sendWxMsg($openid['user_id'], '您好,你的月卡申请进度如下：', '受理通过,受理编号为："' . $flag_id . '"', $ur, '卡类：' . $monthInfo['month_name']);
        } else {
            //拒绝
            Db::name('parking_apply')->where('id', $id)->update(['is_accept' => $status, 'planned_speed' => 4,'ref_remk'=>$msg,'is_done'=>'Y']);
            self::sendWxMsg($openid['user_id'], '您好,你的月卡申请进度如下：', '受理拒绝','',$msg);
        }
        return [true, '完成'];
    }

    public static function sendWxMsg($openid, $title, $conten, $url = null, $rem = null)
    {
        $template = [
            'touser' => $openid,
            'template_id' => 'nXuU7WtKzVn-WZJoroWaO8Dev6yi__lEHB-gP__PTr0',
            'url' => $url,
            'data' => [
                'first' => [
                    'value' => $title,
                ],
                'keyword1' => ['value' => $conten],
                'keyword2' => ['value' => date('Y-m-d H:i:s', time())],
                'keyword3' => ['value' => $rem],
                'remark' => ['value' => '']
            ]
        ];//消息模板
        $ASSESS_TOKEN = RequestAccessToken(Session('UserResutlt.uniacid'));
        $hosts = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $ASSESS_TOKEN;
        httpRequest($hosts, json_encode($template));
    }

    /**
     * 处理审核通过类型和拒绝，发送模板，更新状态
     * @param $data
     * @return string
     */
    public function updateStatusAndSendMsg($data)
    {
        $type = ['refuse' => 0, 'passToPool' => 1, 'passToCard' => 2];
        if ((empty($data['id']) && empty($data['status'])) || !(isset($data['id']) && isset($data['status']))) {
            return '参数错误';
        }
        $source = [];
        $model = Loader::model('MonthCard', 'model');
        $source = $model->getAllReviewUser($data['id']);
        if ($f = array_search($data['type'], $type)) {
            return $this::$f($source, $model, $data['status'], $data['id']);
        }
        return '未知类型';
    }


    /**
     * 拒绝
     * @param $data
     * @return boolean
     */
    public static function refuse($data = [], $model, $status, $mid = null)
    {
        $id = self::joinId($data);
        $model->updateReviewStatus($id, $status, 4,'Y');
        foreach ($data as $val) {
            self::sendWxMsg($val['user_id'], '您好,你的月卡申请进度如下：', '审核不通过');
        }
        return false;
    }


    /**
     * 通过进入号池
     * @param $data
     * @return bool
     */
    public static function passToPool($data = [], $model, $status, $mid = null)
    {

        $id = self::joinId($data);
        $model->updateReviewStatus($id, $status, 2);
        $ur = 'http://shop.gogo198.cn/app/index.php?i=' . Session('UserResutlt.uniacid') . '&c=entry&m=ewei_shopv2&do=mobile&r=parking.alert_message.monthApplyProcess&mid=' . $mid;
        foreach ($data as $val) {
            $model->inserToLotterTable($mid, $val['user_id']);
            self::sendWxMsg($val['user_id'], '您好,你的月卡申请进度如下：', '审核已通过,将进入抽签', $ur);
        }
        return false;
    }


    /**
     * 通过直接发卡
     * @param $data
     * @return bool
     */
    public static function passToCard($data = [], $model, $status, $mid = null)
    {
        $id = self::joinId($data);
        $model->updateReviewStatus($id, $status, 4);
        $payUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/app/index.php?i=' . Session('UserResutlt.uniacid') . '&c=entry&m=ewei_shopv2&do=mobile&r=parking.monthcardpay&mid='.$mid;
        foreach ($data as $val) {
//            $model->inserToLotterTable($mid, $val['user_id']);
//            $model->insertToWin($mid, $val['user_id'],1);
            $model->updateWinStatus($mid, $val['user_id']);
            self::sendWxMsg($val['user_id'], '您好,你的月卡申请进度如下：', '已完成', $payUrl, '已完成审核，可点击进入支付');
        }
        return false;
    }

    /**
     * 拼接更新id
     * @param array $data
     * @return string
     */
    public static function joinId($data = [])
    {
        $id = null;
        foreach ($data as $k => $v) {
            $id .= $v['id'] . ',';
        }
        return trim($id, ',');
    }

    public function getAddr()
    {
        return Db::name('parking_deploy_district')->where('level', 1)->select();
    }

    public function getAddrPath($pid)
    {
        return Db::name('parking_deploy_district')->where('pid', $pid)->select();
    }


    public function getMonthInfo($id)
    {
        return Db::name('parking_month_type')->where('id', $id)->find();
    }


    public function getUserApplyInfo($data)
    {
        $total = Db::name('parking_apply')->where('uniacid', Session('UserResutlt.uniacid'))->count('id');
        $source = Db::name('parking_apply')
            ->alias('a')
            ->join('parking_authorize b', 'a.user_id=b.openid')
            ->join('parking_month_type c', 'a.m_id=c.id')
            ->where('a.uniacid',Session('UserResutlt.uniacid'))
            ->field(['a.*', 'b.mobile', 'c.scheme_name'])
            ->paginate(15, $total, ['query' => ['s' => 'month_card/user_month_applylist'], 'var_page' => 'page',]);
     
        $page = $source->render();
        $data = $source->toArray();
        $data = $data['data'];
        foreach ($data as &$val){
            $val['mobile'] =  substr_replace($val['mobile'],'****',3,4);
        }
        
    
        return [$page,$data];
    }


    /**
     * 获取申请公告信息
     */
    public function getPublicity()
    {
        return Db::name('parking_month_alertmsg')->where('uniacid', Session('UserResutlt.uniacid'))->find();
    }

    /**
     * 保存申请公告
     * @param $data
     */
    public function savePublicity($data)
    {
        $datas = [
            'uniacid' => Session('UserResutlt.uniacid'),
            'text' => $data
        ];
        $isNotEmpty = Db::name('parking_month_alertmsg')->where('uniacid', Session('UserResutlt.uniacid'))->field(['id'])->find();
        if (empty($isNotEmpty)) {
            Db::name('parking_month_alertmsg')->insert($datas);
        } else {
            Db::name('parking_month_alertmsg')->where('id', $isNotEmpty['id'])->update($datas);
        }


    }
    
    
    /**
     * 读取上传中签文件
     * @param $fileName
     * @param $mid
     * @return mixed
     */
    public function fetchFormFileAndSave($fileName)
    {
        
        $fileName = ROOT_PATH.'public/uploads/xls/'.$fileName;
        $objReader = PHPExcel_IOFactory::createReader( PHPExcel_IOFactory::identify($fileName));
        $PHPExcel = $objReader->load($fileName);
        $curSheet = $PHPExcel->getSheet(0);
        $rowCount = $curSheet->getHighestRow();
        $data = [];
        $n= 0;
        for($i=1;$i<=$rowCount;$i++){
            $data[$n]['flagId']=trim($curSheet->getCell('A'.$i)->getValue(),' ');
            $data[$n]['is_rounds']= $curSheet->getCell('B'.$i)->getValue();
            $data[$n]['plan']   = $curSheet->getCell('C'.$i)->getValue();
            $n++;
        }
        return $this->withLotteryResult($data);
    }
    
    
    /**
     * 更新状态
     * @param $flagId
     * @param $mid
     * @return mixed
     */
    public function withLotteryResult($flagId)
    {
        $fid = null;
        $isRou = [];
        $uData= [];
        $n = 1;
        foreach ($flagId as $k => $v) {
            $fid .= $v['flagId'].',';
            $hasFlagId = Db::name('parking_apply')->where('flag_id',$v['flagId'])->field('id')->find()['id'];
            if (empty($hasFlagId)){
                continue;
            }
            if ($v['is_rounds']==1){
                Db::name('parking_apply')->where('flag_id',$v['flagId'])->update(['is_win' => 1,'is_round' => $v['is_rounds'],'is_up'=>'N','planned_speed'=>4]);
    
            }else{
                Db::name('parking_apply')->where('flag_id',$v['flagId'])->update([
                    'is_win'    => 1,
                    'is_round'  => $v['is_rounds'],
                    'is_up'     =>'Y',
                    'planned_speed'=>4,
                    'up_num'    => $n
                ]);
                $n+=1;
            }
        }
        $userOpenid = Db::name('parking_apply')->where('flag_id','in',$fid)->field(['user_id','is_up','m_id'])->select();
        foreach ($userOpenid  as $value){
            if ($value['is_up']=='Y'){
                self::sendWxMsg($value['user_id'],'您好,你的月卡申请中签结果','候选','','');

            }else{
                $payUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/app/index.php?i=' . Session('UserResutlt.uniacid') . '&c=entry&m=ewei_shopv2&do=mobile&r=parking.monthcardpay&mid='.$value['m_id'];
                self::sendWxMsg($value['user_id'],'您好,你的月卡申请中签结果','正选',$payUrl,'');

            }
        }
        foreach ($flagId as $value){
             $mid =  Db::name('parking_month_type')->where('mark',$value['plan'])->field('id')->find();
             Db::name('parking_apply')->where(['m_id'=>$mid['id'],'is_win'=>0])->update(['is_done'=>'Y']);
        }
       return ['完成',0];
    }
    
    
    /**
     * 获取中签方案
     * @param $model
     * @return array
     */
    public function getAllWinPlan($model)
    {
        $data = $model->getAllPlan($model->getPalnCount(),['query' => ['s' => 'month_card/win_list'], 'var_page' => 'page',]);
        $page = $data->render();
        $data = $data->toArray()['data'];
        foreach ($data as $k=>$val){
            $data[$k]['isWin'] = $model->totalAepNum(['is_win'=>1,'m_id'=>$val['id']]);
            $data[$k]['notWin'] = $model->totalAepNum(['is_win'=>0,'m_id'=>$val['id']]);
            $data[$k]['toNum'] = $model->totalAepNum(['m_id'=>$val['id']]);
        }
        return [$page,$data];
    }
    
    
    public function winDetail($mid)
    {
        $total = Db::name('parking_apply')->where('m_id',$mid)->count('id');
        $data = Db::name('parking_apply')
            ->field(['a.flag_id','a.user_name','b.mobile','c.round_num','a.is_round'])
            ->alias('a')
            ->join('parking_authorize b','a.user_id=b.openid')
            ->join('parking_pool c','a.m_id=c.m_id')
            ->where('a.m_id',$mid)
            ->order('a.is_win','desc')
            ->paginate(15,$total,['query' => ['s' => 'month_card/win_detail','mid'=>$mid], 'var_page' => 'page',]);
        $list = $data->toArray()['data'];
        $page = $data->render();
        unset($data);
        foreach ($list as $key=> &$value){
            $value['mobile'] = substr_replace($value['mobile'],'****',3,4);
            $roud = json_decode($value['round_num'],true);
            if (!is_null($value['is_round'])){
                    $value['rname'] = $roud[$value['is_round']-1]['name'];
            }else{
                $value['rname'] = '';
            }
            
        }
        
        return [$page,$list];
    }
    
    
    
    public function getPayOutInfo($mid)
    {
        $total = Db::name('parking_apply')->where(['m_id'=>$mid,'is_win'=>1,'is_done'=>'N','is_up'=>'N'])->count('id');
        $dataObj =  Db::name('parking_apply')
            ->alias('a')
            ->join('parking_month_type b','a.m_id=b.id')
            ->where(['a.m_id'=>$mid,'a.is_win'=>1,'a.is_done'=>'N','a.is_up'=>'N'])
            ->field(['a.user_name','b.scheme_name','b.month_money','b.lottery_pay','b.lottery_pay2','a.up_money','a.id'])
            ->paginate(15,$total,['query' => ['s' => 'month_card/pay_out_manage_info','mid'=>$mid], 'var_page' => 'page',]);
        return $dataObj;
    }
    
    
    /**
     * 添加新支付时间
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function addDelayPayTime($data)
    {
        try{
            Db::name('parking_apply')->where('id',$data['userid'])->update(['delay_paytime'=>$data['time']]);
            return json(['code'=>0,'msg'=>'完成']);
        }catch (\Exception $exception){
            throw new \Exception($exception->getMessage());
        }
    }
    
    /**
     * 候补列表
     * @param $id
     */
  public function fetchWaiting($id)
  {
      $mid = Db::name('parking_apply')->where('id',$id)->field('m_id')->find();
      $list = Db::name('parking_apply')->where(['m_id'=>$mid['m_id'],'is_up'=>'Y'])->field(['user_name','id'])->order('up_num')->select();
      return json(['code'=>0,'data'=>json_encode($list)]);
  }
    
    
    /**
     * 未支付后补替换
     */
  public function updateAlternateStu($data)
  {
      try{
          Db::name('parking_apply')->where('id',$data['id'])->update(['is_up'=>'N','delay_paytime'=>$data['time']]);
          Db::name('parking_apply')->where('id',$data['uid'])->update(['is_out'=>1,'is_done'=>'Y']);
          $userId = Db::name('parking_apply')->where('id',$data['id'])->field(['user_id','m_id'])->find();
          $payUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/app/index.php?i=' . Session('UserResutlt.uniacid') . '&c=entry&m=ewei_shopv2&do=mobile&r=parking.monthcardpay&mid='.$userId['m_id'];
          self::sendWxMsg($userId['user_id'], '您好,你的月卡申请进度如下：', '已完成,缴费期限'.$data['time'], $payUrl, '转为正选，可点击进入支付');
          return json(['code'=>0,'msg'=>'完成']);
      }catch (\Exception $exception){
          throw new \Exception(json([
              'code' => $exception->getCode(),
              'msg'  => '系统异常'
          ]));
      }
  }
    
    /**
     * 获取方案
     */
  public function getPayOutList()
  {
      $total = Db::name('parking_month_type')->where(['is_check'=>1,'month_status'=>1,'uniacid'=>Session('UserResutlt.uniacid')])->count('id');
      $dataObj =  Db::name('parking_month_type')
          ->where(['is_check'=>1,'month_status'=>1,'uniacid'=>Session('UserResutlt.uniacid')])
          ->paginate(15,$total,['query' => ['s' => 'month_card/pay_out_manage'], 'var_page' => 'page',]);
      $page = $dataObj->render();
      $data = $dataObj->toArray()['data'];
      unset($dataObj);
      foreach ($data as &$v){
          $v['payNotNum'] = $this->getNotPayNum($v['id']);
          $v['payNum']    = $this->getPayNum($v['id']);
      }
      return [$page,$data];
  }
    
    /**未支付数量
     * @param $mid
     * @return mixed
     */
  protected function getNotPayNum($mid)
  {
      return Db::name('parking_apply')->where(['m_id'=>$mid,'is_up'=>'N','is_done'=>'N','is_win'=>1])->count('id');
  }

    /**
     * 已支付的
     * @param $mid
     * @return mixed
     */
  protected function getPayNum($mid)
  {
      return Db::name('parking_month_pay')->where(['m_id'=>$mid,'pay_status'=>1])->count('id');
  }
    
    
    /**
     * 导出用户月卡申请信息
     * @return null
     */
  public function exprotUserApply()
  {
      try{
          $file = null;
          $accept = ['未受理','已受理','已拒绝'];
          $win = ['未中签','已中签'];
          $PHPExcel = new PHPExcel();
          $PHPExcel->setActiveSheetIndex(0);
          $PHPSheet = $PHPExcel->getActiveSheet();
          $PHPSheet->setTitle('sheet1');
          $PHPSheet->setCellValue('A1','用户姓名');
          $PHPSheet->setCellValue('B1','手机号');
          $PHPSheet->setCellValue('C1','受理编号');
          $PHPSheet->setCellValue('D1','申请方案');
          $PHPSheet->setCellValue('E1','受理状态');
          $PHPSheet->setCellValue('F1','是否中签');
          $data = $this->fetchUserApply(Session('UserResutlt.uniacid'));
          $n=2;
          foreach ($data as $val){
              $PHPSheet->setCellValue('A'.$n,$val['user_name'])
                  ->setCellValue('B'.$n,"\t".$val['mobile']."\t")
                  ->setCellValue('C'.$n,$val['flag_id'])
                  ->setCellValue('D'.$n,$val['scheme_name'])
                  ->setCellValue('E'.$n,$accept[$val['is_accept']])
                  ->setCellValue('F'.$n,$win[$val['is_win']]);
              $n+=1;
          }
          $phpWrite = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');
          $file       = date('YmdHis', time()) . mt_rand(1111, 9999) . '.xlsx';
          ob_end_clean();  //清空缓存
          header("Pragma: public");
          header("Expires: 0");
          header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
          header("Content-Type:application/force-download");
          header("Content-Type:application/vnd.ms-execl");
          header("Content-Type:application/octet-stream");
          header("Content-Type:application/download");
          header('Content-Disposition:attachment;filename="'.$file.'"');
          header("Content-Transfer-Encoding:binary");
          $phpWrite->save('php://output');
      }catch (\Exception $e){
          throw new \Exception($e->getMessage());
      }
      
  }
  
  
  protected function fetchUserApply($unid)
  {
      return Db::name('parking_apply')
          ->alias('a')
          ->join('parking_authorize b', 'a.user_id=b.openid')
          ->join('parking_month_type c', 'a.m_id=c.id')
          ->where('a.uniacid',$unid)
          ->order('a.flag_id')
          ->field(['a.user_name','a.flag_id','a.is_accept','a.is_win', 'b.mobile', 'c.scheme_name'])
          ->select();
  }
    
    /**
     * 月卡注销列表
     * @return mixed
     */
  public function fetchMonthCancel()
  {
      $total = Db::name('parking_month_pay')->where(['status'=>'D','uniacid'=>Session('UserResutlt.uniacid'),'is_ref'=>0])->count('id');
      return Db::name('parking_month_pay')
          ->alias('a')
          ->join('parking_month_type b','a.m_id=b.id')
          ->join('parking_authorize c','a.user_id=c.openid')
          ->where(['a.status'=>'A','a.uniacid'=>Session('UserResutlt.uniacid'),'a.is_ref'=>1])
          ->field(['a.id','a.m_id','a.update_at','a.ref_account','a.pay_status','a.ref_name','a.pay_money','b.scheme_name','c.mobile'])
          ->paginate(15,$total,['query' => ['s' => 'month_card/month_cancellist'], 'var_page' => 'page',]);
  }
    
    
    /**
     * 月卡注销处理
     */
  public function monthCancelHandle($data)
  {
      try{
          $monthModel = Loader::model('MonthCard','model');
          $monthModel->updateCancelUser($data['id']);
          $monthId    = $monthModel->getMonthId($data['id'])['m_id'];
          $alterUser  = $monthModel->getAlterUser($monthId);
          if (!empty($alterUser)){
              $monthPayTime = $monthModel->getMonthPayTime($alterUser['m_id']);
              $dt = date('Y-m-d H:i:s',$monthPayTime['lottery_pay']).'/'.date('Y-m-d H:i:s',($monthPayTime['lottery_pay2']+(86400*2)));
              if (empty($data['money'])){
                  $monthModel->updateUpField($alterUser['id'],null,$dt);
              }else{
                  $monthModel->updateUpField($alterUser['id'],$data['money'],$dt);
              }
              $payUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/app/index.php?i=' . Session('UserResutlt.uniacid') . '&c=entry&m=ewei_shopv2&do=mobile&r=parking.monthcardpay&mid='.$alterUser['m_id'];
              self::sendWxMsg($alterUser['user_id'], '您好,你的月卡申请如下：', '已转为正选', $payUrl, '可点击进入支付,请于两天内缴费');
          }
         
          return json(['code'=>0,'msg'=>'完成']);
      }catch (\Exception $e){
          return json(['code'=>-1,'msg'=>$e->getMessage()]);
      }
  }
    
    
    /**
     * 月卡支付管理
     */
  public function monthPay($data)
  {
      $total = Db::name('parking_apply')->where(['m_id'=>$data['mid'],'is_win'=>1,'is_done'=>'N','is_up'=>'N'])->count('id');
      $monthRes = Db::name('parking_month_type')->where('id',$data['mid'])->field('scheme_name')->find();
      $dataObj   = Db::name('parking_apply')
          ->alias('a')
          ->join('parking_month_pay b','a.user_id=b.user_id')
          ->join('foll_order c','b.ordersn=c.ordersn')
          ->where(['a.m_id'=>$data['mid'],'a.is_win'=>1,'a.is_up'=>'N','b.m_id'=>$data['mid'],'b.pay_status'=>1])
          ->field(['a.*','b.pay_status','b.pay_money','c.pay_time'])
          ->order('pay_time desc')
          ->paginate(15,$total,['query' => ['s' => 'month_card/month_pay','mid'=>$data['mid']], 'var_page' => 'page',]);
//      $dataObj =  Db::name('parking_apply')
//          ->alias('a')
//          ->join('parking_month_type c','a.m_id=c.id')
//          ->where(['a.m_id'=>$data['mid'],'a.is_win'=>1,'a.is_up'=>'N'])
//          ->field(['a.*','c.scheme_name'])
//          ->order('pay_')
//          ->paginate(15,$total,['query' => ['s' => 'month_card/month_pay','mid'=>$data['mid']], 'var_page' => 'page',]);
      $page = $dataObj->render();
      $datas = $dataObj->toArray()['data'];
      foreach ($datas as &$value){
          $payInfo = Db::name('parking_month_pay')->where('user_id',$value['user_id'])->order('id','desc')->find();
          $value['pay'] = $payInfo;
          $value['scheme_name'] = $monthRes['scheme_name'];
      }
      return [$page,$datas];
  }
  
  public function exproPayInfo($mid){
      $monthRes = Db::name('parking_month_type')->where('id',$mid)->field('scheme_name')->find();
      $payRes   = Db::name('parking_apply')
          ->alias('a')
          ->join('parking_month_pay b','a.user_id=b.user_id')
          ->join('foll_order c','b.ordersn=c.ordersn')
          ->join('parking_authorize d','a.user_id=d.openid')
          ->where(['a.m_id'=>$mid,'a.is_win'=>1,'a.is_up'=>'N','b.m_id'=>$mid,'b.pay_status'=>1])
          ->field(['a.user_name','a.flag_id','b.pay_status','b.pay_money','b.status','c.pay_time','c.upOrderId','d.mobile','d.CarNo'])
          ->order('pay_time desc')
          ->select();
      try{
          $file = null;
          $payStatus = ['未支付','已支付','支付失败'];
          $PHPExcel = new PHPExcel();
          $PHPExcel->setActiveSheetIndex(0);
          $PHPSheet = $PHPExcel->getActiveSheet();
          $PHPSheet->setTitle('sheet1');
          $PHPSheet->setCellValue('A1','用户姓名');
          $PHPSheet->setCellValue('B1','受理编号');
          $PHPSheet->setCellValue('C1','申请方案');
          $PHPSheet->setCellValue('D1','支付状态');
          $PHPSheet->setCellValue('E1','支付金额');
          $PHPSheet->setCellValue('F1','支付时间');
          $PHPSheet->setCellValue('G1','商户单号');
          $PHPSheet->setCellValue('H1','使用状态');
          $PHPSheet->setCellValue('I1','手机号');
          $PHPSheet->setCellValue('J1','车牌号');
          $n=2;
          foreach ($payRes as $val){
              $PHPSheet->setCellValue('A'.$n,$val['user_name'])
                  ->setCellValue('B'.$n,"\t".$val['flag_id']."\t")
                  ->setCellValue('C'.$n,$monthRes['scheme_name'])
                  ->setCellValue('D'.$n,$payStatus[$val['pay_status']])
                  ->setCellValue('E'.$n,$val['pay_money'])
                  ->setCellValue('F'.$n,date('Y-m-d H:i:s',$val['pay_time']))
                  ->setCellValue('G'.$n,"\t".$val['upOrderId']."\t")
                  ->setCellValue('H'.$n,$val['status']=='A'?'正在使用':'已注销')
                  ->setCellValue('I'.$n,$val['mobile'])
                  ->setCellValue('J'.$n,$val['CarNo']);
              $n+=1;
          }
          $phpWrite = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');
          $file       = date('YmdHis', time()) . mt_rand(1111, 9999) . '.xlsx';
          ob_end_clean();  //清空缓存
          header("Pragma: public");
          header("Expires: 0");
          header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
          header("Content-Type:application/force-download");
          header("Content-Type:application/vnd.ms-execl");
          header("Content-Type:application/octet-stream");
          header("Content-Type:application/download");
          header('Content-Disposition:attachment;filename="'.$file.'"');
          header("Content-Transfer-Encoding:binary");
          $phpWrite->save('php://output');
      }catch (\Exception $e){
          throw new \Exception($e->getMessage());
      }
  }
  
}
