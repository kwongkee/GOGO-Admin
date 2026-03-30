<?php

namespace app\declares\logic;

use think\Model;
use think\Db;
use think\Log;

class OrderCustoms extends Model
{

    protected $uniacid = null;

    protected $userInfo = null;
    
	protected $usersInfo = null;

    protected function getUserInfo ( $tels )
    {
        $this->userInfo = Db::name('sz_yi_member')->where('mobile', $tels)->find();
    }
	
	protected function getUsersinfo($tels){
		$this->usersInfo = Db::name('usersinfo')->where('phone', $tels)->find();
	}
    /*
    * 生成注册用户
    */
    public function AutogenerateRegUser ( $tel, $uid, $uname )
    {
        if ( empty($this->userInfo) ) {
            $this->getUserInfo($tel);
        }
        //获取总用户表
        if ( empty($this->usersInfo) ) {
            $this->getUsersinfo($tel);
        }
        
        $this->uniacid = Db::name('foll_business_admin')->where('id', $uid)->field('uniacid')->find();
        if ( empty($this->userInfo) && empty($this->usersInfo)) {
        	//唯一ID
            $uqid = "u" . uniqid(mt_rand(000000, 999999)) . time();
            //用户总表
            $insertData = [
            	'uiqueid' => $uqid, 'application' => 'Crossborder', 'phone' => $tel, 'remarks' => 'A',//A注册，U修改，D注销
            	'ctime' => time()
            ];
            //添加用户
            Db::name('usersinfo')->insert($insertData);
            
            Db::name('sz_yi_member')->insert([
                'uniacid' => $this->uniacid['uniacid'],
                'openid' => 'u' . md5($tel),
                'mobile' => $tel,
                'pwd'	=> md5($tel),
                'createtime' => time(),
                'isagent' => 1,
                'nickname' => $uname,
                'avatar' => 'http://shop.gogo198.cn/addons/sz_yi/template/mobile/default/static/images/photo-mr.jpg',
                'regtype' => 2,
                'uuid'  =>$uid,
                'uiqueids'=>$uqid
            ]);
        }
    }


    /*
     * 失败发送通知
     */
    public function sendMail ( $uid, $sub, $body )
    {
        $emailAccount = null;
        if(null == $uid){
            $emailAccount = Db::name('foll_cross_border')->where('uid', $uid)->field('subject')->find();
            $emailAccount = json_decode($emailAccount['subject'], true);
        }else{
            $emailAccount['conmpanyemail'] = '13809703680@qq.com';
            $emailAccount['cpname'] = 'Gogo';
        }

        send_mail($emailAccount['conmpanyemail'], $emailAccount['cpname'], $sub, $body);
    }

    /*
     * 更新状态
     */
    public function updateStatus ( $oid = 0, $data = null )
    {
        Db::name('foll_elec_order_detail')->where('EntOrderNo', $oid)->update($data);
        return $this;
    }

    /*
     * 返回电子订单详情
     */
    public function getOrderDetail ( $oid )
    {
        $order_detail = Db::name('foll_elec_order_detail')->where('EntOrderNo', $oid)->find();
        $order_head   = Db::name('foll_elec_order_head')->where('id', $order_detail['head_id'])->field(['DeclEntNo', 'DeclEntName', 'EBEntNo', 'EBEntName', 'EBPEntNo', 'EBPEntName', 'InternetDomainName', 'DeclTime', 'OpType', 'IeFlag', 'CustomsCode', 'CIQOrgCode', 'Request_head', 'uid'])->find();
        return [$order_detail, $order_head];
    }


    /*
     * 生成订单
     */

    public function generateOrder ( $data, $pay_order)
    {

        if ( empty($this->userInfo) ) $this->getUserInfo($data['OrderDocTel']);
        $GoodsNo = json_decode($data['goodsNo'],true);
        $ordersn = $data['EntOrderNo'];
        $addr_id  = Db::name('sz_yi_member_address')->insertGetId(['uniacid' => $this->uniacid['uniacid'], 'openid' => $this->userInfo['openid'], 'realname' => $data['OrderDocName'], 'mobile' => $data['OrderDocTel'], 'province' => $data['Province'], 'city' => $data['city'], 'area' => $data['county'], 'address' => $data['RecipientAddr'], 'isdefault' => 1]);
        $order_id = Db::name('sz_yi_order')->insertGetId([
            'uniacid' => $this->uniacid['uniacid'],
            'openid' => $this->userInfo['openid'],
            'ordersn' => $ordersn,
            'price' => $data['OrderGoodTotal'],
            'goodsprice' => $data['OrderGoodTotal'],
            'status' => 1,
            'paytype' => 21,
            'addressid' => $addr_id,
            'createtime' => strtotime($data['OrderDate']),
            'paytime'   =>strtotime($data['OrderDate']),
            'address' => serialize(['id' => $addr_id, 'realname' => $data['OrderDocName'], 'mobile' => $data['OrderDocTel'], 'address' => $data['RecipientAddr'], 'province' => $data['Province'], 'city' => $data['city'], 'area' => $data['county'], 'street' => '']),
            'oldprice' => $data['OrderGoodTotal'],
            'isvirtual' => 1,
            'realprice' => $data['OrderGoodTotal'],
            'ordersn_general' => $ordersn,
            'pay_ordersn' => $pay_order,
        ]);
        foreach ($GoodsNo as $value){
            $gid = Db::name('sz_yi_goods')->where('goodssn', $value['goodNo'])->find();
            Db::name('sz_yi_order_goods')->insert([
                'uniacid' => $this->uniacid['uniacid'],
                'orderid' => $order_id,
                'goodsid' => $gid['id'],
                'price' => $gid['marketprice'],
                'total' => $value['num'],
                'createtime' => strtotime($data['OrderDate']),
                'realprice' => $gid['marketprice'],
                'goodssn' => $value['goodNo'],
                'oldprice' => $gid['marketprice'],
                'supplier_uid' => 6,
                'openid' => $this->userInfo['openid'],
                ]);
            Db::name('sz_yi_goods')->where('id', $gid['id'])->setInc('viewcount', ceil($value['num'] / 0.3));
            Db::name('sz_yi_goods')->where('id', $gid['id'])->setInc('sales', (20+$value['num']));
            Db::name('sz_yi_goods')->where('id', $gid['id'])->where('total', '>', 0)->setDec('total', $value['num']);
        }
      
//      Db::startTrans();
//        try {
//
//            Db::commit();
//            unset($data);
//        } catch (\Exception $e) {
//            Log::write($e->getMessage());
//            Db::rollback();
//        }

    }
    
    /**
     * 更新错误信息
     * @param $oid
     * @param $num
     * @param $field1
     * @param $field2
     * @param null $msg
     */
    public function addErrCount($oid,$num,$field1,$field2,$msg=null)
    {
        Db::name('foll_elec_count')->where('batch_num',$num)->inc($field1)->inc($field2)->update();
        Db::name('foll_elec_order_detail')->where('EntOrderNo',$oid)->update(['err_msg'=>$msg]);
    }
    
    
    /**
     * 添加成功数
     * @param $num
     */
    public function addSuccessCount($num)
    {
        Db::name('foll_elec_count')->where('batch_num',$num)->inc('payCount')->inc('batchPayCount')->inc('elecCount')->inc('batchElecCount');
        
    }
}