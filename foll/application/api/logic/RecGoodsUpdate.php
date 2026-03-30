<?php

namespace app\api\logic;
use think\Model;
use app\api\model\GoodsRegList;
use think\Log;
use think\Db;
class RecGoodsUpdate extends Model{
    protected $orderNum=0;
    public function updateGoodsReglistInfo($data=null){
        try{
            $goods = new GoodsRegList();
            $goods->where('EntGoodsNo',$data['Declaration']['GoodsRegRecList']['EntGoodsNo'])
                ->update([
                    'CIQGoodsNo'    =>$data['Declaration']['GoodsRegRecList']['CIQGoodsNo'],
                    'CIQGRegStatus' => $data['Declaration']['GoodsRegRecList']['CIQGRegStatus'],
                    'CIQNotes'      =>$data['Declaration']['GoodsRegRecList']['CIQNotes'],
                    'OpType'        =>$data['Declaration']['GoodsRegRecList']['OpType'],
                    'OpTime'        =>date("Y-m-d H:i:s",$data['Declaration']['GoodsRegRecList']['OpTime']),
                    'DeclEntNo'     =>$data['Declaration']['GoodsRegRecList']['DeclEntNo'],
                    'EPortGoodsNo'  =>$data['Declaration']['GoodsRegRecList']['EportGoodsNo']
                ]);
        }catch (\Exception $exception){
            Log::write($exception->getCode().":".$exception->getMessage());
            return false;
        }
        if($data['Declaration']['GoodsRegRecList']['CIQGRegStatus']=='C'){
            $this->addGoods($data);
        }
        return true;

    }

    protected function addGoods($datas){
        $viewCount = $this->initViewCount($datas['Declaration']['GoodsRegRecList']['EntGoodsNo']);
        $saleCount = $this->initSales($datas['Declaration']['GoodsRegRecList']['EntGoodsNo']);
        $totalCount =$this->initTotal($datas['Declaration']['GoodsRegRecList']['EntGoodsNo']);
        $goodsInfo = Db::name("foll_goodsreglist")->where('EntGoodsNo',$datas['Declaration']['GoodsRegRecList']['EntGoodsNo'])->find();
        $UserInfo  = Db::name("foll_goodsreghead")->alias('a')->join('ims_foll_business_admin b','b.id=a.uid')->where('a.id',$goodsInfo['head_id'])->field('uniacid')->select();
        $isGoodsEmpty = Db::name('sz_yi_goods')->where('goodssn',$goodsInfo['EntGoodsNo'])->field('id')->find();
        if(!empty($isGoodsEmpty))return true;
        Db::startTrans();
        try{
            $time = time();
            $year = date('Y',time());
            $month = date('m',time());
           $id =  Db::name("sz_yi_goods")->insertGetId([
               'uniacid'     => $UserInfo[0]['uniacid']
                ,'type'      => 1
                ,'status'    => 1
                ,'title'     => $goodsInfo['ShelfGName']
                ,'thumb'     => 'images/'.$UserInfo[0]['uniacid'].'/'.$year.'/'.$month.'/'.$goodsInfo['EntGoodsNo'].'.jpg'
                ,'content'   => '<p><img src="/images/goodetail/head.jpg" width="100%" style=""/></p><p><img src="/images/'.$UserInfo[0]['uniacid'].'/'.$year.'/'.$month.'/'.$goodsInfo['EntGoodsNo'].'.jpg" width="100%" style=""/></p><p><img src="/images/goodetail/footer.jpg" width="100%" style=""/></p><p><br/></p>'
                ,'goodssn'   => $goodsInfo['EntGoodsNo']
                ,'productsn' => $goodsInfo['BarCode']
                ,'marketprice' => $goodsInfo['RegPrice']
                ,'total'       => $totalCount
                ,'totalcnf'    => 1
                ,'sales'       => $saleCount
                ,'createtime'  => $time
                ,'timestart'   => $time
                ,'timeend'     => $time
                ,'viewcount'   => $viewCount
                ,'cash'        => 1
                ,'isverify'    => 1
                ,'noticetype'  =>0
                ,'supplier_uid'=> 6
                ,'diyformtype' =>1
                ,'dispatchtype'=> 1
                ,'diyformid'   =>5
                ,'CIQGoodsNo'  => $datas['Declaration']['GoodsRegRecList']['CIQGoodsNo']
                ,'CusGoodsNo'  => $datas['Declaration']['GoodsRegRecList']['DeclEntNo']
            ]);
           $param = [
               ['uniacid'=>$UserInfo[0]['uniacid'], 'goodsid'=>$id, 'title'=>'产品名称', 'value'=>$goodsInfo['ShelfGName'], 'displayorder'=>0],
                ['uniacid'=>$UserInfo[0]['uniacid'], 'goodsid'=>$id, 'title'=>'型号规格', 'value'=>$goodsInfo['GoodsStyle'], 'displayorder'=>0],
               ['uniacid'=>$UserInfo[0]['uniacid'], 'goodsid'=>$id, 'title'=>'进口备案', 'value'=>$datas['Declaration']['GoodsRegRecList']['CIQGoodsNo'], 'displayorder'=>0],
               ['uniacid'=>$UserInfo[0]['uniacid'], 'goodsid'=>$id, 'title'=>'毛重', 'value'=>$goodsInfo['GrossWt'], 'displayorder'=>1],
               ['uniacid'=>$UserInfo[0]['uniacid'], 'goodsid'=>$id, 'title'=>'厂家', 'value'=>$goodsInfo['Manufactory'], 'displayorder'=>1],
               ['uniacid'=>$UserInfo[0]['uniacid'], 'goodsid'=>$id, 'title'=>'品牌', 'value'=>$goodsInfo['Brand'], 'displayorder'=>2],
               ['uniacid'=>$UserInfo[0]['uniacid'], 'goodsid'=>$id, 'title'=>'品质', 'value'=>$goodsInfo['QualityCertify'], 'displayorder'=>3],
              
           ];
           Db::name("sz_yi_goods_param")->insertAll($param);
           Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            Log::write($e->getCode().":".$e->getMessage());
            return false;
        }
    }

    /*
     * 初始化浏览数
     */
    protected function initViewCount($oid,$num=999)
    {
        $viewNum = Db::name('sz_yi_goods')->where('goodssn',$oid)->field('viewcount')->find();
        $this->orderNum = Db::name('sz_yi_order_goods')->where('goodssn',$oid)->count('id');
        return ceil((($num+$viewNum['viewcount'])+$this->orderNum)/0.3);
    }

    /*
     * 初始化订购
     */
    protected function initSales($oid,$num=20)
    {
        return $num+$this->orderNum;
    }
    /*
     * 初始化库存
     */
    protected function initTotal($oid,$num=999)
    {
        return $num-$this->orderNum;
    }
}