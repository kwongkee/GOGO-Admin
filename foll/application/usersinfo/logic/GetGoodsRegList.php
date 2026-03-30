<?php
namespace app\declares\logic;
use think\Model;
use app\declares\model\GoodsRegHead;
use app\declares\model\GoodsRegList;
use think\Session;
class GetGoodsRegList extends Model{
    protected $page =10;
    public $config = [
        'type' =>'Layui',
        'query'=>null,
        'var_page'=>'page',
        'newstyle'=>true
    ];
    public function get_list(){
        $this->config['query'] = ['s'=>'Gzeport/gzeport_list'];
        $uniacid =Session::get('admin')['id'];
        $GoodsRegHead = new GoodsRegHead();
        $total = $GoodsRegHead->where('uid',$uniacid)->count('id');
        return $GoodsRegHead->where('uid',$uniacid)
            ->order('id', 'desc')
            ->paginate($this->page,$total,$this->config);
    }
    public function get_all($id){
//        $config = [
//            'type' =>'Layui',
//            'query'=>['s'=>'Gzeport/getAllGoodsInfo&id='.$id],
//            'var_page'=>'page',
//            'newstyle'=>true
//        ];
        $this->config['query'] =['s'=>'Gzeport/getAllGoodsInfo&id='.$id];
        $GoodsRegHead = new GoodsRegList();
        $total = $GoodsRegHead->where('head_id',$id)->count('id');
        return $GoodsRegHead->where('head_id',$id)
            ->order('id', 'desc')
            ->paginate($this->page,$total,$this->config);
    }
}