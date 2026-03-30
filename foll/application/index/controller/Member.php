<?php

namespace app\index\controller;
use app\index\controller;
use think\Request;
use think\Loader;
use think\Db;
use PHPExcel;
use PHPExcel_IOFactory;


class Member extends CommonController
{
    public function index()
    {
        $memberLogic = model('Member','logic');
        $count  = $memberLogic->fetchMemberNum();
        return view('member/index',['count'=>$count]);
    }

    public function member_list(Request $request)
    {
        $member = Loader::model('Member','logic');
        $uniacid = Session('UserResutlt')['uniacid'];
        $where = 'uniacid='.$uniacid;
        $page = ((int)$request->get('page')-1)*$request->get('limit');
        $data  = $request->get();
        if (isset($data['name'])&&$data['name']!=''){
            $where .= ' and name="'.$request->get('name').'"';
        }

        if (isset($data['card'])&&$data['card']!=''){
            $where .= ' and CarNo="'.$request->get('card').'"';
        }

        if(isset($data['tel'])&&$data['tel']!=''){
            $data['tel'] = trim($data['tel'],"'");
            $where .= ' and mobile="'.$data['tel'].'" or openid="'.$data['tel'].'"';
        }

        $list = $member->get_table_count('parking_authorize',$where,'id')
                        ->get_page_list('parking_authorize',$where,$page,$request->get('limit'));
        $lists = $member->field_convert($list);
        return json(['code'=>0,'msg'=>'','count'=>$member->count,'data'=>$lists]);
    }

    /**
     * 用户详情
     * @param Request $request
     * @return mixed
     */
    public function userDeteil(Request $request){
//        dump($request->get());
        if (empty($request->get('user_id'))){
            abort(404,'用户不存在');
        }
        $userLogic = model('Member','logic');
        $list = $userLogic->fetchUserDetail($request->get('user_id'));
        return view('member/user_detail',['list'=>$list]);
    }


    public function exproUser(Request $request){
        $file = null;
        $list = null;
        $MemberLogic = model('Member','logic');
        $list = $MemberLogic->fetAllUser(Session('UserResutlt.uniacid'));
        $list = $MemberLogic->field_convert($list);
        try {
            $PHPExcel = new PHPExcel();
            $PHPExcel->setActiveSheetIndex(0);
            $PHPSheet = $PHPExcel->getActiveSheet();
            $PHPSheet->setTitle('sheet1');
            $PHPSheet->setCellValue('A1', '手机号');
            $PHPSheet->setCellValue('B1', '名称');
            $PHPSheet->setCellValue('C1', '免密授权');
            $PHPSheet->setCellValue('D1', '授权类型');
            $PHPSheet->setCellValue('E1', '注册时间');
            $n = 2;
            foreach ($list as $val) {
                $PHPSheet->setCellValue('A' . $n, "\t".$val['mobile'])
                    ->setCellValue('B' . $n, $val['name'])
                    ->setCellValue('C' . $n, $val['auth_status'])
                    ->setCellValue('D' . $n, $val['auth_type'])
                    ->setCellValue('E' . $n, $val['create_time']);
                $n += 1;
            }
            $phpWrite = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
            $file = date('YmdHis', time()) . mt_rand(1111, 9999) . '.xlsx';
            ob_end_clean();  //清空缓存
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");
            header('Content-Disposition:attachment;filename="' . $file . '"');
            header("Content-Transfer-Encoding:binary");
            $phpWrite->save('php://output');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function get_month_apply_info(Request $request)
    {
        $data = null;
        $member = Loader::model('Member','logic');
        if (empty($request->get('username'))) {
            $data = $member->get_table_count('parking_imagename',['uniacid'=>Session('UserResutlt')['uniacid']],'id')
                ->get_month_apply_from_table([
                'b.status'=>0,
                'a.uniacid'=>Session('UserResutlt')['uniacid']
                ],
                $member->count
                );
        } else {
            $data = $member->get_month_apply_from_table(['b.uname'=>$request->get('username'),'a.uniacid'=>Session('UserResutlt')['uniacid']]);
        }
        return view('member/month_apply_list',['page'=>$data->render(),'list'=>$data->toArray()]);
    }

    public function update_review_status(Request $request)
    {
        $uniacid = Session('UserResutlt')['uniacid'];
        $member = Loader::model('Member','logic');
        if($request->get('sf')==2){
            $member->update_review_status(['id'=>$request->get('id'),'uniacid'=>$uniacid],['status'=>1,'audit_status'=>1]);
            $url = 'http://shop.gogo198.cn/app/index.php?i='.$uniacid.'&c=entry&m=ewei_shopv2&do=mobile&r=parking.month_card';
            $this->send_examination_passed("审核通过",'点击购买月卡',$url,$request->get('openid'));
        }else{
            $member->update_review_status(['id'=>$request->get('id'),'uniacid'=>$uniacid],['status'=>1]);
        }
        return json(['code'=>0,'message'=>'完成']);
    }

    public function lottery_list(Request $requess)
    {
        $member = Loader::model('Member','logic');
        $list = $member->get_table_count('parking_verified',['status'=>1,'audit_status'=>0,'uniacid'=>Session('UserResutlt')['uniacid']],'id')
            ->get_all_user(['uniacid'=>Session('UserResutlt')['uniacid'],'status'=>1,'audit_status'=>0],10);
        return view('member/month_lottery',[
            'page'=>$list->render(),
            'list'=>$list->toArray(),
            'sex'=>[0 => '女', 1 => '男']
        ]);
    }

    public function lottery(Request $request)
    {
        if ( empty($request->post('number'))) {
            return json(['code' => 1, 'message' => '请填写必要参数']);
        }
        $member   = Loader::model('Member', 'logic');
        $UserData = Db::name('parking_verified')->where(['uniacid' => Session('UserResutlt')['uniacid'], 'status' => 1, 'audit_status' => 0])->select();
        $res      = lottery($UserData, $request->post('number'));
        if(!$res)return json(['code' => 1, 'message' => '未知错误']);
        $openId   = null;
        $url = 'http://shop.gogo198.cn/app/index.php?i='.Session('UserResutlt')['uniacid'].'&c=entry&m=ewei_shopv2&do=mobile&r=parking.month_card';
            if(!empty($res['yes'])){
                foreach ($res['yes'] as $key => $value) {
                    $openId .= $value['openid'] . ',';
                }
                $where['openid'] = ['in',trim($openId, ',')];
                Db::name('parking_verified')->where($where)->update(['audit_status' => 1]);
                foreach ($res['yes'] as $key => $val){
                    $this->send_examination_passed("审核通过",'点击购买月卡',$url,$val['openid']);
                }
            }
            $url = null;
            if(!empty($res['no'])){
                foreach ($res['no'] as $key =>$val){
                    $this->send_examination_passed("审核未通过",'别灰心,期待后续','',$val['openid']);
                }
            }
           return json(['code'=>0,'message'=>'完成']);
    }

    /*
     * 发送用户审核信息
     */
    public function send_examination_passed($context,$remark=null,$url=null,$openid)
    {
        $template=[
            'touser'     =>$openid,
            'template_id'=>'nXuU7WtKzVn-WZJoroWaO8Dev6yi__lEHB-gP__PTr0',
            'url'       =>$url,
            'data'=>[

                    'first' => array(
                        'value' => "您好，你的申请审核结果如下",
                        'color' => 'black'),
                    'keyword1' => array(
                        'value' => $context,
                        'color' => 'black'),
                    'keyword2' => array(
                        'value' => date('Y-m-d H:i:s', time()),
                        'color' => 'black'),
                    'keyword3' => array(
                        'value' => '如审核通过可点击进入购买月卡',
                        'color' => 'black'
                    ),
                    'remark' => array(
                        'value' => $remark,
                        'color' => 'black')
            ]
        ];//消息模板
        $ASSESS_TOKEN = $this->RequestAccessToken(Session('UserResutlt')['uniacid']);
        $hosts="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$ASSESS_TOKEN;
        httpRequest($hosts,json_encode($template));
    }
    protected function RequestAccessToken ( $uniacid)
    {
        $uniacid = $uniacid?$uniacid:14;
        $account = Db::table("ims_account_wechats")->where('uniacid', $uniacid)->find();
        $ASSESS_TOKEN = file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $account['key'] . '&secret=' . $account['secret']);
        $ASSESS_TOKEN = json_decode($ASSESS_TOKEN, true);
        return $ASSESS_TOKEN['access_token'];
    }


    /**
     * 删除用户
     * @param Request $request
     * @return mixed
     */
    public function delUser(Request $request)
    {
        $uid =$request->get('id');
        if ($uid==''||!is_numeric($uid)){
            return json(['code'=>-1,'message'=>'参数错误']);
        }
        Db::execute("insert into ims_parking_authorize_delete select * from ims_parking_authorize where id={$uid}");
        Db::name('parking_authorize')->where('id',$uid)->delete();
        return json(['code'=>0,'message'=>'已删除']);
    }
}
