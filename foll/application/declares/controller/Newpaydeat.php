<?php

/**
 * 新生支付
 * @author 赵金如
 * @date   2018-11-12
 */

namespace app\declares\controller;
use think\Controller;
use Util\data\Sysdb;

class Newpaydeat extends BaseAdmin {
    public $partenrId;
    public $pkey;
    public $admin;
    public function __construct(){
        parent::__construct();
        $this->db = new Sysdb;
        $this->admin = session('admin');

        $config = [
            'host'  => '127.0.0.1',
            'port'  => '6379',
            'auth'  => '123456',
        ];
        $attr = [
            //连接超时时间，redis配置文件中默认为300秒
            'timeout'=>300,
            //选择数据库
            'db_id'=>7,
        ];

        $this->redis = null;
        //实例化 Redis 缓存类
        $this->redis = Redis::getINstance($config,$attr);
        $this->rs = $this->redis->getRedis();

        //测试账号
        $this->partnerId = '10000000182';
        $this->pkey = '';
    }
    

    /**
     * 将提交上来的用户身份信息进行认证
     */
    public function AuthName($dataArr = null)
    {
        $newInfo = null;
        foreach($dataArr as $key=>$val)
        {
            $newInfo[$key] = trim($val);
            if(trim($val) == '')
            {
                return $res = ['code'=>0,'msg'=>'Data Is Null'];
            }
        }

        //报文部分
        $user = $sendArr = [
            'version'       =>  '1.0',
            //订单号(32)纯数字
            'orderId'       =>  date('YmdHis',time()).mt_rand(1111,9999),
            //订单提交时间
            'submitTime'    =>  date('YmdHis',time()),
            //商户 ID  会员ID
            'partnerId'     =>  $this->partnerId,
            /**
             * 认证信息
             */
            //姓名
            'userName'      =>  trim($newInfo['userName']),
            //身份证号码
            'userId'        =>  trim($newInfo['userId']),
            /**
             * 安全信息
             */
            //扩展字段
            'remark'        =>  'remark',
            //编码方式 1：UTF-8
            'charset'       =>  1,
            //1：RSA 方式  2：MD5 方式
            'signType'      =>  2,
        ];
        $user['uid'] = $this->admin['id'];//当前操作用户ID
        $this->db->table('foll_payment_userinfo')->insert($user);

        //数据拼接
        $signStr            = $this->Splicing($sendArr);
        //数据加密
        $signMsg            = $signStr."&pkey=".$this->pkey;
        $sendArr['signMsg'] = md5($signMsg);
        //发送报文信息

        $url = 'https://www.cfmtec.com/webgate/realNameAuthentication.htm';
        //明细文件上传  数据提交
        $res = $this->postData($url,$sendArr);
        //Url=a&b=c 转换成数组
        parse_str($res,$DataInfo);
        //写入日志  返回信息
        file_put_contents("./paylog/Helpay/RealName2.txt", json_encode($DataInfo)."\r\n",FILE_APPEND);

        $update = [
            'sysOrderNo'    =>  $DataInfo['sysOrderNo'] ? $DataInfo['sysOrderNo']: '' ,
            'completeTime'  =>  $DataInfo['completeTime'],
            'fee'           =>  $DataInfo['fee'],
            'resultCode'    =>  $DataInfo['resultCode'],
            'resultMsg'     =>  $DataInfo['resultMsg'],
        ];

        $Rupdate = $this->db->table('foll_payment_userinfo')->where(['orderId'=>$DataInfo['orderId']])->update($update);

        //如果返回结果代码0001 并且消息返回请求已受理
        if($DataInfo['resultCode'] == '0000' && $DataInfo['resultMsg'] == '信息一致，认证成功')
        {
            return $res = ['code'=>1,'msg'=>'success','data'=>$DataInfo];
        }

        return $res = ['code'=>0,'msg'=>'error','data'=>$DataInfo];
    }



}


?>