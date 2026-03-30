<?php

namespace app\api_v3\controller;

use think\Db;
use think\Request;
use think\Env;
use think\Cache;
use app\common\validate\BaseValidate;
use app\lib\exception\member_exception\MemberLoginException;
use app\lib\exception\param_exception\ParameterException;
use app\lib\exception\user_exception\UserQRCodeException;
use app\lib\tools\CurlHandler;
use app\lib\service\Tokens as TokenService;
use app\lib\restful_api\RestfulApiCode;
use app\lib\tools\ResultHandler;
use app\lib\exception\ExceptionErrorCode;
use Util\data\Sysdb;
use PHPExcel_IOFactory;
use PHPExcel;


/**
 * 小程序接口
 * Class WechatCarBill
 * @package app\api_v3\controller
 */
class WechatUserBill
{
    public function __construct(){
		$this->db = new Sysdb;
	}

    function returnHandler($result, $flag = true)
    {
        if (is_int($result) && $result <= 0)
        {
            throw new EmptyResultException();
        }
        else if (!$flag && empty($result))
        {
            throw new EmptyResultException();
        }
        else if (!$flag && ($result instanceof Collection) && $result->isEmpty() )
        {
            throw new EmptyResultException();
        }

        $statusCode = RestfulApiCode::OK;
        if (Request::instance()->isGet())
        {
            $statusCode = RestfulApiCode::OK;
        }
        else if (Request::instance()->isPost() || Request::instance()->isPut() || Request::instance()->isPatch())
        {
            $statusCode = RestfulApiCode::CREATED;
        }
        else if (Request::instance()->isDelete())
        {
            $statusCode = RestfulApiCode::NO_CONTENT;
        }
        return ResultHandler::returnJson('SUCCESS', $result, ExceptionErrorCode::SUCCESS, $statusCode);
    }


    //获取会员
    public function getuser() {

        $validate = new BaseValidate([
            'type'    =>'require',
            'keyword' =>'isDefault'
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();

        $type = $params['type'];
        $keyword = $params['keyword'];
        
        if( $type == 0 )
        {
            //跨境商户
            $map0 = array();
            $map0['uniacid'] = 3;
            $map0['parent_id'] = 0;
            $map0['openid'] = ['neq',''];
            if($keyword)
            {
                $map0['user_name|user_tel'] = ['like','%'.$keyword.'%'];
            }
            $userList = Db::name('decl_user')->field('user_name as nickname,openid')->where($map0)->select();
        }else{
            //普通会员
            $map1 = array();
            $map1['f.uniacid'] = 3;
            $map1['f.follow'] = 1;
            $map1['m.mobile'] = ['neq',''];
            if($keyword)
            {
                $map1['m.nickname|m.mobile'] = ['like','%'.$keyword.'%'];
            }

            $userList = Db::name('mc_mapping_fans')
            ->alias("f")
            ->field('m.nickname,m.openid')
            ->join('sz_yi_member m', 'f.openid = m.openid')
            ->where($map1)
            ->select();

            //$userList = Db::name('mc_mapping_fans')->field('nickname,openid')->where($map1)->select();
        }

        $result['status'] = 1;
        $result['data'] = $userList;
		return $this->returnHandler($result);
    }

    //修改账单
    public function editbill()
    {
        if( $input['send_month'] != null )
        {
            $send_month = $input['send_month'] * 1 + 1;
        }else{
            $send_month = '';
        }

        if( $input['send_date'] != null )
        {
            $send_date = $input['send_date'] * 1 + 1;
        }else{
            $send_date = '';
        }

        if( $input['send_week'] != null )
        {
            $send_week = $input['send_week'] * 1 + 1;
        }else{
            $send_week = '';
        }

        $map = array();
        $map['id'] = input('data_id');
        $map['openid'] = input('openid');
        $map['project_name'] = input('project_name');
        $map['project_id'] = input('project_id');
        $map['project_data_name'] = input('project_data_name');
        $map['project_data_id'] = input('project_data_id');
        $map['price'] = input('price');
        $map['unit'] = input('unit');
        $map['num'] = input('num');
        $map['billtypeval'] = input('billtypeval');
        $map['send_type'] = input('send_type');
        $map['send_time'] = input('send_time');
        $map['send_month'] = $send_month;
        $map['send_date'] = $send_date;
        $map['send_week'] = $send_week;
        $map['status'] = 0;
        $map['recall'] = 0;

        if( Db::name('smallwechat_user_bill')->update($map) )
        {
            if( Db::name('smallwechat_user_bill_sendlog')->where(array('bill_id'=>input('data_id')))->delete() )
            {
                $this->insertSendLog(input('data_id'),0);
            }
            $result['msg'] = '修改成功';
        }else{
            $result['msg'] = '修改失败';
        }

        return json($result);
    }

    //到账核查
    public function moneycheck()
    {
        $types = input('types');
        $map = array();
        $map['id'] = input('data_id');
        $map['openid'] = input('openid');
        if( $types == 'ok' )
        {
            //足额
            $map['status'] = 4;
        }else{
            //有误
            $map['status'] = 7;
        }

        if( Db::name('smallwechat_user_bill')->update($map) )
        {
            $result['msg'] = '提交成功';
        }else{
            $result['msg'] = '提交失败';
        }
        return json($result);
    }

    //删除账单
    public function delbill()
    {
        $map = array();
        $map['id'] = input('data_id');
        $map['openid'] = input('openid');

        if( Db::name('smallwechat_user_bill')->where($map)->delete() )
        {
            Db::name('smallwechat_user_bill_sendlog')->where(array('bill_id'=>input('data_id')))->delete();
            $result['msg'] = '删除成功';
        }else{
            $result['msg'] = '删除失败';
        }

        return json($result);
    }

    //提交电子专票
    public function postinvoiced()
    {
        $map = array();
        $id = input('invoice_id');
        $map['piao_name'] = input('piao_name');
        $map['invoice_type'] = input('invoice_type');
        $map['bill_id'] = input('bill_id');
        $map['d_taitou'] = input('d_taitou');
        $map['d_shuihao'] = input('d_shuihao');
        $map['d_address'] = input('d_address');
        $map['d_phonenumber'] = input('d_phonenumber');
        $map['d_bank'] = input('d_bank');
        $map['d_bankaccount'] = input('d_bankaccount');
        $map['d_email'] = input('d_email');
        

        if($id)
        {
            $map['id'] = $id;
            if( Db::name('smallwechat_user_bill_invoice')->update($map) )
            {
                Db::name('smallwechat_user_bill')->update(array('id'=>input('bill_id'),'status'=>8));
                $result['msg'] = '提交成功';
            }else{
                $result['msg'] = '提交失败';
            }
        }else{
            $map['status'] = 0;
            $map['create_time'] = time();
            if( Db::name('smallwechat_user_bill_invoice')->insert($map) )
            {
                Db::name('smallwechat_user_bill')->update(array('id'=>input('bill_id'),'status'=>8));
                $result['msg'] = '提交成功';
            }else{
                $result['msg'] = '提交失败';
            }
        }
        
        return json($result);
    }

    public function postinvoicez()
    {
        $map = array();
        $id = input('invoice_id');
        $map['piao_name'] = input('piao_name');
        $map['invoice_type'] = input('invoice_type');
        $map['bill_id'] = input('bill_id');
        $map['z_taitou'] = input('z_taitou');
        $map['z_shuihao'] = input('z_shuihao');
        $map['z_phonenumber'] = input('z_phonenumber');
        $map['z_bank'] = input('z_bank');
        $map['z_bankaccount'] = input('z_bankaccount');
        $map['z_shou_address'] = input('z_shou_address');
        $map['z_address'] = input('z_address');

        if($id)
        {
            $map['id'] = $id;
            if( Db::name('smallwechat_user_bill_invoice')->update($map) )
            {
                Db::name('smallwechat_user_bill')->update(array('id'=>input('bill_id'),'status'=>8));
                $result['msg'] = '提交成功';
            }else{
                $result['msg'] = '提交失败';
            }
        }else{
            $map['status'] = 0;
            $map['create_time'] = time();
            if( Db::name('smallwechat_user_bill_invoice')->insert($map) )
            {
                Db::name('smallwechat_user_bill')->update(array('id'=>input('bill_id'),'status'=>8));
                $result['msg'] = '提交成功';
            }else{
                $result['msg'] = '提交失败';
            }
        }
        
        return json($result);
    }

    public function postinvoices()
    {
        $map = array();
        $id = input('invoice_id');
        $map['piao_name'] = input('piao_name');
        $map['invoice_type'] = input('invoice_type');
        $map['bill_id'] = input('bill_id');
        $map['s_taitou'] = input('s_taitou');
        $map['s_money_type'] = input('money_type');
        $map['s_pay_money'] = number_format(input('s_pay_money'),2,".","");
        $map['s_money_name'] = input('s_money_name');

        if($id)
        {
            $map['id'] = $id;
            if( Db::name('smallwechat_user_bill_invoice')->update($map) )
            {
                Db::name('smallwechat_user_bill')->update(array('id'=>input('bill_id'),'status'=>8));
                $result['msg'] = '提交成功';
            }else{
                $result['msg'] = '提交失败';
            }
        }else{
            $map['status'] = 0;
            $map['create_time'] = time();
            if( Db::name('smallwechat_user_bill_invoice')->insert($map) )
            {
                Db::name('smallwechat_user_bill')->update(array('id'=>input('bill_id'),'status'=>8));
                $result['msg'] = '提交成功';
            }else{
                $result['msg'] = '提交失败';
            }
        }
        
        return json($result);
    }

    //获取票务数据
    public function getinvoicedetail()
    {
        $bill_id = input('bill_id');
        $data = Db::name('smallwechat_user_bill_invoice')->where(array('bill_id'=>$bill_id))->find();
        $result['data'] = $data;
        return json($result);
    }

    //拒绝开票
    public function nookinvoice()
    {
        $id = input('id');
        $remark = input('remark');
        $invoice_id = input('invoice_id');
        if( Db::name('smallwechat_user_bill')->update(array('id'=>$id,'status'=>9)) )
        {
            if($remark)
            {
                Db::name('smallwechat_user_bill_invoice')->update(array('id'=>$invoice_id,'remark'=>$remark));
            }
            $result['msg'] = '提交成功';
        }else{
            $result['msg'] = '提交失败';
        }
        return json($result);
    }

    //新增开票
    public function addnewinvoice()
    {
        $id = input('id');
        if( Db::name('smallwechat_user_bill')->update(array('id'=>$id,'status'=>3)) )
        {
            $result['msg'] = '提交成功';
        }else{
            $result['msg'] = '提交失败';
        }
        return json($result);
    }
    
    //添加账单
    public function addbill()
    {
        //send_openid
        $validate = new BaseValidate([
            'openid'            =>'require',
            'project_name'      =>'require',
            'project_data_name' =>'require',
            'price'             =>'require',
            'unit'              =>'require',
            'num'               =>'require',
            'billtypeval'       =>'isDefault',
            'send_type'         =>'require',
            'usertypeval'       =>'isDefault',
            'send_time'         =>'isDefault',
            'send_month'        =>'isDefault',
            'send_date'         =>'isDefault',
            'send_week'         =>'isDefault',
            'selectUserVal'     =>'require',
            'project_id'        =>'require',
            'project_data_id'   =>'require',
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();
        
        if( $params['send_month'] != null )
        {
            $send_month = $params['send_month'] * 1 + 1;
        }else{
            $send_month = '';
        }

        if( $params['send_date'] != null )
        {
            $send_date = $params['send_date'] * 1 + 1;
        }else{
            $send_date = '';
        }

        if( $params['send_week'] != null )
        {
            $send_week = $params['send_week'] * 1 + 1;
        }else{
            $send_week = '';
        }
        $completeCount = 0;
        $selectUserVal = $params['selectUserVal'];
        foreach ($selectUserVal as $k => $v) {
            $map = array();
            $map['openid'] = $params['openid'];
            $map['project_name'] = $params['project_name'];
            $map['project_data_name'] = $params['project_data_name'];
            $map['price'] = number_format($params['price'],2,".","");
            $map['unit'] = $params['unit'];
            $map['num'] = $params['num'];
            $map['billtypeval'] = $params['billtypeval'];
            $map['send_type'] = $params['send_type'];
            $map['usertypeval'] = $params['usertypeval'];
            $map['send_time'] = $params['send_time'];
            $map['send_month'] = $send_month;
            $map['send_date'] = $send_date;
            $map['send_week'] = $send_week;
            $map['create_time'] = time();
            $map['status'] = 0;
            $map['send_openid'] = $v;
            $map['project_id'] = $params['project_id'];
            $map['project_data_id'] = $params['project_data_id'];

            $log_id = Db::name('smallwechat_user_bill')->insertGetId($map);

            if( $log_id )
            {
                $completeCount++;
                $this->insertSendLog($log_id,0);
                
                if( $params['billtypeval'] == 1 && $params['send_type'] == 'now' )
                {
                    //单次
                    $this->SendWechatTpls($params['usertypeval'],$v,'您有账单需要处理','您有账单需要处理','wx6d1af256d76896ba','pages/user/mybill/detail/detail?jump_home=1&id='.$log_id);
                    //log
                    $this->updateSendLog($log_id,1);
                }
            }
        }
        
        if ($completeCount==count($selectUserVal))
        {
            $result['status'] = 1;
            $result['msg'] = '生成账单成功';
        }
        else
        {
            if ($completeCount>0)
            {
                $result['status'] = 0;
                $result['msg'] = '部分账单生成失败';
            }
            else
            {
                $result['status'] = 0;
                $result['msg'] = '全部账单生成失败';
            }
        }

        return $this->returnHandler($result);
    }

    //账单列表
    public function getbilllist()
    {
        $validate = new BaseValidate([
            'openid'    =>'require',
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();
        
        $list = Db::name('smallwechat_user_bill')->where($params)->select();
        
        foreach ($list as $k => $v) {
            
            if($v['usertypeval']==0)
            {
                //商户
                $list[$k]['userdata'] = Db::name('decl_user')->field('user_name as nickname,id')->where(array('openid'=>$v['send_openid']))->find();
            }else{
                //普通会员
                $list[$k]['userdata'] = Db::name('mc_mapping_fans')->field('nickname,uid')->where(array('openid'=>$v['send_openid']))->find();
            }
            $list[$k]['list_status'] = $v['status'];
            switch ($v['status']) {
                case '0':
                    $list[$k]['status'] = '待对账';
                    break;
                case '1':
                    $list[$k]['status'] = '待核查';
                    break; 
                case '2': 
                    $list[$k]['status'] = '待查账';
                    break;
                case '3': 
                    $list[$k]['status'] = '已结算';
                    break;
                case '4': 
                    $list[$k]['status'] = '待开票';
                    break;
                case '5': 
                    $list[$k]['status'] = '已撤回';
                    break; 
                case '6': 
                    $list[$k]['status'] = '待结算';
                    break;
                case '7': 
                    $list[$k]['status'] = '待结算-到账有误';
                    break;
                case '8': 
                    $list[$k]['status'] = '开票-待审核';
                    break; 
                case '9': 
                    $list[$k]['status'] = '开票-有误';
                    break;                                
            }
        }

        $result['list'] = $list;
        return $this->returnHandler($result);
    }

    //撤回账单
    public function recallbill()
    {
        $bill_id = input('bill_id');
        //判断是否已经发送
        $billData = Db::name('smallwechat_user_bill')
        ->alias("b")
        ->join('smallwechat_user_bill_sendlog s', 'b.id = s.bill_id')
        ->where(array('b.id'=>$bill_id))
        ->find();

        if( $billData['recall'] == 1 )
        {
            $result['msg'] = '该账单已经是撤回状态！';
        }
        else if( $billData['send_status'] == 1 )
        {
            $result['msg'] = '该账单已经发送,不能撤回！';
        }else{
            if( Db::name('smallwechat_user_bill')->update(array('id'=>$bill_id,'recall'=>1,'status'=>5)) )
            {
                $result['msg'] = '撤回成功！';
            }else{
                $result['msg'] = '撤回失败！';
            }
        }

        return json($result);
    }

    //获取我的账单
    public function getmybill()
    {
        $validate = new BaseValidate([
            'send_openid'    =>'require',
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();
        
        $unionid = Db::name('smallwechat_user')->where(array('openid'=>$params['send_openid']))->value('unionid');
        
        if($unionid)
        {
            $openid = Db::name('mc_mapping_fans')->where(array('uniacid'=>3,'unionid'=>$unionid))->value('openid');
            if($openid)
            {
                $list = Db::name('smallwechat_user_bill')->where(array('send_openid'=>$openid,'recall'=>0))->select();
                foreach ($list as $k => $v) {
                    $list[$k]['list_status'] = $v['status'];
                    switch ($v['status']) {
                        case '0':
                            $list[$k]['status'] = '待对账';
                            break;
                        case '1':
                            $list[$k]['status'] = '待核查';
                            break; 
                        case '2': 
                            $list[$k]['status'] = '待查账';
                            break;
                        case '3': 
                            $list[$k]['status'] = '已结算';
                            break;
                        case '4': 
                            $list[$k]['status'] = '待开票';
                            break;
                        case '5': 
                            $list[$k]['status'] = '已撤回';
                            break; 
                        case '6': 
                            $list[$k]['status'] = '待结算';
                            break; 
                        case '7': 
                            $list[$k]['status'] = '待结算-到账有误';
                            break; 
                        case '8': 
                            $list[$k]['status'] = '开票-待审核';
                            break; 
                        case '9': 
                            $list[$k]['status'] = '开票-有误';
                            break;                              
                    }
                }
                $result['list'] = $list;
            }else{
                $result['list'] = [];
            }
        }else{
            $result['list'] = [];
        }

        return $this->returnHandler($result);
    }

    public function restcheck()
    {
        $validate = new BaseValidate([
            'id'    =>'require',
            'pay_remark' => 'isDefault'
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();
        $logdata = Db::name('smallwechat_user_bill_log')->where(array('id'=>$params['id']))->find();
        if( Db::name('smallwechat_user_bill_log')->update(array('id'=>$params['id'],'status'=>4,'pay_remark'=>$params['pay_remark'])) )
        {
            //发送给boss - ov3-bt8keSKg_8z9Wwi-zG1hRhwg
            $this->SendWechatTpls(0,'ov3-btxV-ENpabbyd0c7grgz5RP4','账单核查','有账单需要重新核查','wx6d1af256d76896ba','pages/user/mybill/detail/detail?jump_home=1&manage=1&id='.$params['id']);
            $result['msg'] = '提交成功';
        }else{
            $result['msg'] = '提交失败';
        }
        
        return $this->returnHandler($result);
    }

    public function restsend()
    {
        $validate = new BaseValidate([
            'id'    =>'require',
            'pay_remark' => 'isDefault'
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();
        $logdata = Db::name('smallwechat_user_bill_log')->where(array('id'=>$params['id']))->find();
        if( Db::name('smallwechat_user_bill_log')->update(array('id'=>$params['id'],'status'=>0,'pay_remark'=>$params['pay_remark'])) )
        {
            $result['msg'] = '提交成功';
            $this->SendWechatTpls($logdata['user_type'],$logdata['send_openid'],'账单已核查','请重新对账','wx6d1af256d76896ba','pages/user/mybill/detail/detail?jump_home=1&id='.$params['id']);
        }else{
            $result['msg'] = '提交失败';
        }
        
        return $this->returnHandler($result);
    }

    public function uploadimages(Request $request)
    {
        $files = $request->file('file'); 
        $path = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'smallwechat';
        $info = $files->validate(['ext' => 'jpg,png,jpeg,bmp'])->rule('date')->move($path); 
        return $info->getSaveName();

    }

    //提交结算
    public function okcheck()
    {
        $id = input('id');
        $bill_id = input('bill_id');
        $pay_money = input('pay_money');
        $okimgList = input('okimgList/a');

        //更新状态
        $map = array();
        $map['id'] = $id;
        $map['pay_money'] = number_format($pay_money,2,".","");
        $map['pay_time'] = time();
        $map['pay_images'] = implode(',',$okimgList);
        if( Db::name('smallwechat_user_bill_sendlog')->update($map) )
        {
            if( Db::name('smallwechat_user_bill')->update(array('id'=>$bill_id, 'status'=>2)) )
            {
                $result['msg'] = '提交成功';
            }else{
                $result['msg'] = '提交失败';
            }
        }else{
            $result['msg'] = '提交失败';
        }
        
        return json($result);
    }

    public function ordercheck()
    {
        $validate = new BaseValidate([
            'id'     =>'require',
            'status' =>'require',
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();
        
        $id = $params['id'];
        $checkstatus = $params['status'];

        if( $checkstatus == 'ok' )
        {
            //待结算
            if( Db::name('smallwechat_user_bill')->update(array('id'=>$id, 'status'=>6)) )
            {
                //发送提醒
                $this->SendWechatTpls($params['user_type'],$v,'您有账单需要处理','您有账单需要处理','wx6d1af256d76896ba','pages/user/mybill/detail/detail?jump_home=1&id='.$log_id);

                $result['msg'] = '提交成功';
            }else{
                $result['msg'] = '提交失败';
            }
        }else if( $checkstatus == 'no' ){
            //待核查
            if( Db::name('smallwechat_user_bill')->update(array('id'=>$id, 'status'=>1)) )
            {
                $result['msg'] = '提交成功';
            }else{
                $result['msg'] = '提交失败';
            }
        }else{
            $result['msg'] = '提交失败';
        }

        return $this->returnHandler($result);
    }

    public function getbilldetail()
    {
        $validate = new BaseValidate([
            'id'    =>'require',
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();
        
        $billdata = Db::name('smallwechat_user_bill')
        ->alias("b")
        ->join('smallwechat_user_bill_sendlog s', 's.bill_id = b.id')
        ->where(array('s.bill_id'=>$params['id']))
        ->find();

        if($billdata['pay_images'])
        {            
            $billdata['pay_images'] = explode(',',$billdata['pay_images']);
        }
        if($billdata['pay_time'])
        {
            $billdata['pay_time'] = date('Y-m-d H:i:s',$billdata['pay_time']);
        }
        $billdata['total'] = number_format($billdata['price'] * $billdata['num'],2,".","");
        switch ($billdata['status']) {
            case '0':
                $billdata['bill_status'] = '待对账';
                break;
            case '1':
                $billdata['bill_status'] = '待核查';
                break; 
            case '2': 
                $billdata['bill_status'] = '待查账';
                break;
            case '3': 
                $billdata['bill_status'] = '已结算';
                break;
            case '4': 
                $billdata['bill_status'] = '待开票';
                break;
            case '5': 
                $billdata['bill_status'] = '已撤回';
                break; 
            case '6': 
                $billdata['bill_status'] = '待结算';
                break;
            case '7': 
                $billdata['bill_status'] = '待结算-到账有误';
                break;
            case '8': 
                $billdata['bill_status'] = '开票-待审核';
                break; 
            case '9': 
                $billdata['bill_status'] = '开票-有误';
                break;                                
        }
        $result['data'] = $billdata;
        return $this->returnHandler($result);
    }

    //发送账单
    public function sendbill()
    {
        $validate = new BaseValidate([
            'openid'    =>'require',
            'bill_id'   =>'require',
            'user_type' =>'require',
            'send_openid' =>'require',
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();

        $send_openid = $params['send_openid'];
        $completeCount = 0;
        foreach ($send_openid as $k => $v) {
            $map = array();
            $map['openid'] = $params['openid'];
            $map['bill_id'] = $params['bill_id'];
            $map['user_type'] = $params['user_type'];
            $map['send_openid'] = $v;
            $map['status'] = 0;
            $map['create_time'] = time();

            $log_id = Db::name('smallwechat_user_bill_log')->insertGetId($map);
            if( $log_id )
            {
                $this->insertSendLog($log_id,0);
                $billData = Db::name('smallwechat_user_bill')->where(array('id'=>$params['bill_id']))->find();
                if($billData['bill_type'] == 1)
                {
                    //单次
                    $this->SendWechatTpls($params['user_type'],$v,'您有账单需要处理','您有账单需要处理','wx6d1af256d76896ba','pages/user/mybill/detail/detail?jump_home=1&id='.$log_id);
                    //log
                    $this->updateSendLog($log_id,1);
                }
                $completeCount++;
            }
        }

        if ($completeCount==count($send_openid))
        {
            $result['status'] = 1;
            $result['msg'] = '生成账单成功';
        }
        else
        {
            if ($completeCount>0)
            {
                $result['status'] = 0;
                $result['msg'] = '部分账单生成失败';
            }
            else
            {
                $result['status'] = 0;
                $result['msg'] = '全部账单生成失败';
            }
        }

        return $this->returnHandler($result);
    }

    public function insertSendLog($log_id,$status)
    {
        
        $billdata = Db::name('smallwechat_user_bill')->where(array('id'=>$log_id))->find();
        switch ($billdata['billtypeval']) {
            case '0':
                switch ($billdata['send_type']) {
                    case 'day':
                        $today = date('Y-m-d',time());
                        $sendtime = $today." ".$billdata['send_time'];
                        $map['go_send_time'] = $sendtime;
                        break;
                    case 'week':
                        $week=date('w',time());//获取现在星期
                        $week==0 && $week=7;//如果是0 改为7
                        $today = date("Y-m-d",($billdata['send_week']-$week)*86400+time());
                        $sendtime = $today." ".$billdata['send_time'];
                        $map['go_send_time'] = $sendtime;
                        break;
                    case 'month':
                        $yearmonth = date('Y-m',time());
                        $today = $yearmonth.'-'.$billdata['send_date'];
                        $sendtime = $today." ".$billdata['send_time'];
                        $map['go_send_time'] = $sendtime;
                        break;
                    case 'year':
                        $year = date('Y',time());
                        $today = $year.'-'.$billdata['send_month'].'-'.$billdata['send_date'];
                        $sendtime = $today." ".$billdata['send_time'];
                        $map['go_send_time'] = $sendtime;
                        break;    
                }
            break;
            case '1':
                switch ($billdata['send_type']) {
                    case 'wait':
                    $today = date('Y-m-d',time());
                    $sendtime = $today." ".$billdata['send_time'];
                    $map['go_send_time'] = $sendtime;
                    break;
                }
            break;
        }
    
        $map['bill_id'] = $log_id;
        $map['send_status'] = $status;
        $map['create_time'] = time();
        Db::name('smallwechat_user_bill_sendlog')->insert($map);
    }

    public function updateSendLog($log_id,$status)
    {
        $logdata = Db::name('smallwechat_user_bill_sendlog')->where(array('bill_id'=>$log_id))->find();
        $map['id'] = $logdata['id'];
        $map['send_status'] = $status;
        $map['go_send_time'] = date('Y-m-d H:i',time());
        Db::name('smallwechat_user_bill_sendlog')->update($map);
    }

    public function SendWechatTpls($user_type,$send_openid,$msg,$remark,$appid = '',$pagepath = '')
	{
        if( $user_type == 0 )
        {
            $user = Db::name('decl_user')->field('user_name as nickname,openid')->where(array('openid'=>$send_openid))->find();
        }else{
            $user = Db::name('mc_mapping_fans')->field('nickname,openid')->where(array('openid'=>$send_openid))->find();
        }

        if($user)
        {
            $this->SendWechat(json_encode([
                'call'=>'send_pre_commit_notice',
                'msg' =>$msg,
                'name'=>$user['nickname'],
                'time'=>date('Y-m-d H:i:s',time()),
                'openid'=>$user['openid'],
                'remark'=> $remark,
                'uniacid'=>3,
                'appid' => $appid,
                'pagepath' => $pagepath
            ]));
        }
		
    }
    
    public function SendMamage($temp,$appid = '',$pagepath = '')
	{
		//appid = wx6d1af256d76896ba
		//boss ooWwF0p_1SBnxknfhkMv5ux02U1E
		$manage_user = Db::name('mc_mapping_fans')->where(array('uniacid'=>3,'unionid'=>'ooWwF0p_1SBnxknfhkMv5ux02U1E'))->find();

		$this->SendWechat(json_encode([
		  'call'=>'send_pre_commit_notice_d',
		  'first' =>$temp['first'],
		  'dates' =>$temp['dates'],
		  'payType' =>$temp['payType'],
		  'ordersn' =>$temp['ordersn'],
		  'payMoney' =>$temp['payMoney'],
		  'result' => $temp['result'],
		  'remark' => $temp['remark'],
		  'openid'=>$manage_user['openid'],
		  'uniacid'=>3,
		  'appid' => $appid,
		  'pagepath' => $pagepath
		]));
	}

    //发送微信通知
	public function SendWechat($data)
	{
		$url = 'http://shop.gogo198.cn/api/sendwechattemplatenotice.php';
        $client = new \GuzzleHttp\Client();
        try {
            //正常请求
            $promise = $client->request('post', $url, ["headers" => ['Content-Type' => 'application/json'],'body'=>$data]);
        } catch (GuzzleHttpExceptionClientException $exception) {
            //捕获异常 输出错误
            return $this->error($exception->getMessage());
        }
	}

    //Curl Get请求
	public function GetUrl($url) {
		//初始化
		$curl = curl_init();
		//设置捉取URL
		curl_setopt($curl,CURLOPT_URL,$url);
		//设置获取的信息以文件流的形式返回，而不是直接输出。
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		//执行命令
		$res = curl_exec($curl);
		//关闭Curl请求
		curl_close($curl);
		//print_r($res);
		return $res;
	}

}
