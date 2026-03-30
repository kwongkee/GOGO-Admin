<?php

namespace app\admin\logic;

use think\Model;
use think\Db;
class GoodsReg extends Model
{

    protected $querys = ['type' => 'Layui', 'query' => ['s' => 'admin/get_goods_index'], 'var_page' => 'page', 'newstyle' => true];

    public function getGHead ()
    {
        $config = $this->querys;
        return $this->getList('foll_goodsreghead', ['g_check' => 0], $this->getTotal("foll_goodsreghead"), 15, $config);
    }


    /*
     * 获取单条商品详情数据
     */
    public function getSingleInfo ( $id )
    {
        return Db::name('foll_goodsreglist')->where('id', $id)->find();
    }

    public function getGlist ( $hid )
    {
        $config          = $this->querys;
        $config['query'] = ['s' => 'admin/goods_details'];
        return $this->getList('foll_goodsreglist', ['head_id' => $hid], $this->getTotal('foll_goodsreglist', ['head_id' => $hid]), 100, $config);
    }

    protected function getList ( $table = null, $where = [], $total, $limit = 15, $query )
    {
        return Db::name($table)->where($where)->order('id', 'desc')->paginate($limit, $total, $query);
    }

    public function getTotal ( $table, $where = null )
    {
        return Db::name($table)->where($where)->count('id');
    }

    public function updateGoodsCheckStatus ( $id = 0 ,$g_check)
    {
        Db::name("foll_goodsreghead")->where('id', $id)->update(['g_check' => $g_check]);
        if($g_check == 1){
            send_mail($this->getEmailAccout($id),'gogo','审核结果','通过');
        }else{
            send_mail($this->getEmailAccout($id),'gogo','审核结果','拒绝');
        }

    }

    public function getEmailAccout($id)
    {
        $uid = Db::name('foll_goodsreghead')->where('id',$id)->field(['uid'])->find();
        $emil = Db::name('foll_cross_border')->where('uid',$uid['uid'])->field('subject')->find();
        $emil = json_decode($emil['subject'],true);
        return $emil['conmpanyemail'];
    }

    public function updateDetailTable( $data,$hid )
    {
        $id = $data['id'];

        unset($data['id'],$data['hid']);
        try{
            Db::name("foll_goodsreglist")->where('id',$id)->update($data);
        }catch (\Exception $e){
            $this->error($e->getMessage(),Url('admin/goods_details&hid='.$hid));
        }

    }

}