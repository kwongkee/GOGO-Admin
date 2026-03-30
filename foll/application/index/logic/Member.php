<?php

namespace app\index\logic;
use think\Model;
use think\Db;
class Member extends Model
{
    public $model;
    public  $count=0;
    public  $config = [
        'type' =>'Layui',
        'query'=>['s'=>'index/get_month_apply_info'],
        'var_page'=>'page',
        'newstyle'=>true
    ];

    public function __construct()
    {
        parent::__construct();
        $this->model = model('Member','model');
    }


    public function get_table_count($tableName,$where,$field)
    {
        $this->count = Db::name($tableName)->where($where)->count($field);
        return $this;
    }


    public function get_page_list($tableName,$where,$page,$limit)
    {
        return Db::name($tableName)->where($where)->order('id','desc')->limit($page,$limit)->select();
    }


    public function field_convert($data)
    {
        $auth_status = ['未授权','已授权'];
        $auth_type = ['wx'=>'微信','wg'=>'信用卡','sd'=>'农商'];
        foreach ($data as $key => &$val){
            $val['auth_status'] = $auth_status[$val['auth_status']];
//            $val['mobile']  = substr_replace($val['mobile'],'****',3,4);
            $val['create_time']= date('Y-m-d H:i',$val['create_time']);
            if(!empty($val['auth_type'])){
                $val['auth_type'] = unserialize($val['auth_type']);
                foreach ($val['auth_type'] as $k=>$v){
                    $val['auth_type'] = $auth_type[$k];
                }
            }
        }
        return $data;
    }

    /**查询所有用户信息导出
     * @param $uniacid
     * @return mixed
     */
    public function fetAllUser($uniacid){
        return Db::name('parking_authorize')->where('uniacid',$uniacid)->field(['mobile','name','auth_status','auth_type','create_time'])->order('create_time','desc')->select();
    }


    public function get_month_apply_from_table($where,$total,$limit=15)
    {
        $this->config['query'] = ['s'=>'index/get_month_apply_info'];
        return Db::name('parking_imagename')
            ->alias('a')
            ->join('parking_verified b','a.openid=b.openid')
            ->where($where)
            ->field(['a.name','b.*'])
            ->paginate($limit,$total,$this->config);
    }

    public function search_user_info($where,$total=1,$limit=15)
    {
        $this->config['query'] = ['s'=>'index/get_month_apply_info'];
        return Db::name('parking_imagename')
            ->alias('a')
            ->join('parking_verified b','a.openid=b.openid')
            ->where($where)
            ->field(['a.name','b.*'])
            ->paginate($limit,$total,$this->config);
    }

    public function update_review_status($where,$data)
    {
        Db::name('parking_verified')->where($where)->update($data);
    }

    public function get_all_user($where,$limit=15)
    {
        $this->config['query']=['s'=>'index/lottery'];
        return Db::name('parking_verified')
            ->where($where)
            ->order('id','desc')
            ->paginate($limit,$this->count,$this->config);
    }

    /**
     * 获取会员数量，授权数
     */
    public function fetchMemberNum(){
        $uniacid= Session('UserResutlt.uniacid');
        return [
            'total' => $this->model->getTotalNum($uniacid),
            'wxAuth'=> $this->model->getWxAuthNum($uniacid),
            'fAgro' => $this->model->getFAgroNum($uniacid),
            'card'  => $this->model->getCardNum($uniacid)
        ];
    }


    /**
     * 用户详情
     * @param $userId
     * @return array
     */
    public function fetchUserDetail($userId){
        $userId = trim($userId ,"'");
        $userMonthCard = $this->model->fetchUserMonthCardByUid($userId);//拥有月卡
        $userVir = $this->model->fetchUserVerifInfoByUid($userId);//
        $userNotPayOrder =$this->model->fetchUserNotPayOrderByUid($userId);
        $userVioOrder = $this->model->fetchUserVioOrderByUid($userId);
        return ['userMonthCard'=>$userMonthCard,'userVir'=>$userVir,'userNotPayOrder'=>$userNotPayOrder,'userVioOrder'=>$userVioOrder];
    }

}
