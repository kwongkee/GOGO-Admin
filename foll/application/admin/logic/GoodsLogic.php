<?php

namespace app\admin\logic;

use PHPExcel_IOFactory;
use think\Model;
use think\DB;

/**
 * 商品logic
 * Class GoodsLogic
 * @package app\admin\logic
 */
class GoodsLogic extends Model
{

    /**
     * 商品核价确认
     * @param $goodsNo 商户填写的商品编号
     * @throws \Exception
     */
    public function goodsPriceConfirm($goodsNo)
    {
        $goodsModel = new \app\admin\model\GoodsModel();
        $time = time();
        $goodsRes1 = Db::name('customs_elec_goodsprice_verif')->where('user_goodsno', $goodsNo)->order('create_time',
            'desc')->find();
        $goodsRes1['form_data'] = json_decode($goodsRes1['form_data'], true);
        $goodsRes2 = $goodsModel->where('goodssn', $goodsRes1['epb_goodsno'])->find()->toArray();
        if (empty($goodsRes2)) {
            throw new \Exception('失败!');
        }

        Db::startTrans();
        try {
            $cateId = $this->addCategory($goodsRes1['form_data']['uniacid'], $goodsRes2['class']);
            $id = $this->addShopTable($cateId, $goodsRes2, $time, $goodsRes1);
            $this->addGoodsParam($goodsRes1['form_data']['uniacid'], $id, $goodsRes2);
            $this->addRegGoodsTable($goodsRes2, $goodsRes1);
            Db::name('customs_elec_goodsprice_verif')->where('user_goodsno', $goodsNo)->update(['status' => 1]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new \Exception($e->getTraceAsString());
        }

    }


    /**
     * 批量上架核价失败商品
     * @param $file object|null
     * @throws \Exception
     */
    public function batchPriceConfir($file)
    {
        if (empty($file)) {
            throw new \Exception("请上传文件");
        }
        $fileName = $this->saveFile($file);
        $res = $this->getExcelFileContent($fileName);
        if (empty($res)) {
            throw new \Exception('数据为空');
        }
        $goodsModel = new \app\admin\model\GoodsModel();
        $time = time();
        Db::startTrans();
        try {
            foreach ($res as $value) {
                $post = Db::name('customs_elec_goodsprice_verif')->where([
                    'user_goodsno' => $value['gno'],
                    'status' => 0
                ])->order('create_time', 'desc')->find();
                $disc = Db::name('decl_user')->where('id',$post['user_id'])->field('sbDis')->find();
                if (empty($post)) {
                    continue;
                }
                $goodsInfo = $goodsModel->where("goodssn", $post["epb_goodsno"])->find();
                $post["form_data"] = json_decode($post["form_data"], true);
                $post["user_price"] = $disc['sbDis']==0?$value["price"]:sprintf("%.2f",$value['price']*$disc['sbDis']);
                $post['discount_price']=$value['discount_price'];
                //添加分类
                $cateId = $this->addCategory($post["form_data"]["uniacid"], $goodsInfo["class"]);
                //添加商城表
                $id = $this->addShopTable($cateId, $goodsInfo, $time, $post);
                //添加商城属性表
                $this->addGoodsParam($post["form_data"]["uniacid"], $id, $goodsInfo);
                //添加申报表
                $this->addRegGoodsTable($goodsInfo, $post);
                Db::name('customs_elec_goodsprice_verif')->where('user_goodsno',
                    $value["gno"])->update(['status' => 1]);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            @unlink($fileName);
            throw new \Exception($e->getMessage() . $e->getLine());
        }

        @unlink($fileName);
    }

    /**
     * 保存上传文件
     * @param $file object
     * @return string 文件名
     * @throws \Exception
     */
    protected function saveFile($file)
    {
        $path = ROOT_PATH . 'public/uploads/xls';
        $file = $file->validate(['ext' => 'xls'])->move($path);
        if (!$file) {
            throw new \Exception('文件格式为xls');
        }
        return $path . '/' . $file->getSaveName();
    }


    /**
     * 获取上传文件内容
     * @param $file
     * @return array
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    protected function getExcelFileContent($file)
    {
        $type = pathinfo($file);
        $type = strtolower($type["extension"]);
        $type = $type === 'csv' ? $type : 'Excel5';
        $objReader = PHPExcel_IOFactory::createReader($type);
        $objReader->setReadDataOnly(true);
        $PHPReader = $objReader->load($file);
        $sheet = $PHPReader->getSheet(0);
        $row = $sheet->getHighestRow();
        $data = [];
        for ($current = 2; $current <= $row; $current++) {
            array_push($data, [
                'gno' => trim($sheet->getCellByColumnAndRow(0, $current)->getValue()),
                'price' => trim($sheet->getCellByColumnAndRow(1, $current)->getValue()),
                'goodsName' => trim($sheet->getCellByColumnAndRow(2, $current)->getValue()),
                'discount_price'=>trim($sheet->getCellByColumnAndRow(3,$current)->getValue()),
            ]);
        }
        return $data;
    }


    /**
     * @param int $uniacid 公众号id
     * @param string $className 分类名称
     *
     * @return int id
     */
    public function addCategory($uniacid, $className)
    {
        $isCate = Db::name('sz_yi_category')->where('name', '=', $className)->field('id')->find();
        if ($isCate) {
            $cateId = $isCate['id'];
        } else {
            $cateId = Db::name('sz_yi_category')->insertGetId([
                'uniacid' => $uniacid,
                'name' => $className,
                'level' => 1,
            ]);
        }
        return $cateId;
    }


    /**
     * @param int $cateId 分类id
     * @param array $goodsInfo 商品信息
     * @param int $time 当前时间
     * @param array $goodsFrom 商品信息商户填写的
     * @return int id 插入id
     */
    public function addShopTable($cateId, $goodsInfo, $time, $goodsFrom)
    {
        $imageRes = json_decode($goodsInfo['goods_images'], true);
        $id = Db::name('sz_yi_goods')->insertGetId([
            'uniacid' => $goodsFrom['form_data']['uniacid'],
            'pcate' => $cateId,
            'type' => 1,
            'status' => 1,
            'title' => $goodsFrom["user_goodsno"] . $goodsFrom["form_data"]["trade"] . $goodsInfo["goods_name"],
            'thumb' => isset($imageRes['thumb']) ? $imageRes['thumb'] : 'images/total_image/' . base64_encode($goodsInfo["goods_name"]) . '.jpg',
            //'thumb' => ,
            'content' => isset($imageRes['content']) ? $imageRes['content'] : '<p><img src="/images/total_image/' . base64_encode($goodsInfo['goods_name']) . '.jpg" width="100%" style=""/></p><p><img src="/images/total_image/' . base64_encode($goodsInfo['goods_name']) . '_01.jpg" width="100%" style=""/></p><p><img src="/images/total_image/' . base64_encode($goodsInfo['goods_name']) . '_02.jpg" width="100%" style=""/></p><p><img src="/images/goodetail/head.jpg" width="100%" style=""/></p><p><img src="/images/goodetail/footer.jpg" width="100%" style=""/></p><p><br/></p>',
            ///'content' => ,
            'goodssn' => $goodsFrom["user_goodsno"],
            'productsn' => '',
            'productprice'=>$goodsFrom['discount_price'],
            'marketprice' => $goodsFrom["user_price"],
            'total' => mt_rand(111, 999),
            'totalcnf' => 1,
            'sales' => 0,
            'createtime' => $time,
            'maxbuy'=>$goodsInfo['maxbuy'],
            'isnew' => mt_rand(0,1),
            'isrecommand' => mt_rand(0,1),
            'timestart' => $time,
            'timeend' => $time,
            'viewcount' => mt_rand(11, 999),
            'cash' => 1,
            'isverify' => 1,
            'noticetype' => 0,
            'supplier_uid' => $goodsFrom['form_data']['merchant'],
            'diyformtype' => 1,
            'dispatchtype' => 1,
            'diyformid' => 5,
            'isverifysend' => 0,
            'love_money' => 0.00,
            'isopenchannel' => 0,
            'yunbi_consumption' => 0.000,
            'yunbi_commission' => 0.000,
            'isyunbi' => 0,
            'yunbi_deduct' => 0.00,
            'isforceyunbi' => 0,
            'isdeclaration' => 0,
            'virtual_declaration' => 0,
            'CIQGoodsNo' => $goodsInfo["ciq_goodsno"],
            'CusGoodsNo' => $goodsInfo["cus_goodsno"],
        ]);
        return $id;
    }
    
    /**
     *
     *添加商品来自总库
     * @param  int  $cateId  分类id
     * @param  array  $goodsInfo  商品信息
     * @param $price
     * @return int id 插入id
     */
    public function addShopTableFormGoodsStock($cateId, $goodsInfo)
    {
        $id = Db::name('sz_yi_goods')->insertGetId([
            'uniacid' => 3,
            'pcate' => $cateId,
            'type' => 1,
            'status' => 1,
            'title' => $goodsInfo->goodssn. $goodsInfo->goods_name,
            'thumb' =>'images/total_image/' . base64_encode($goodsInfo->goods_name) . '.jpg',
            'content' => '<p><img src="/images/total_image/' . base64_encode($goodsInfo->goods_name) . '.jpg" width="100%" style=""/></p><p><img src="/images/total_image/' . base64_encode($goodsInfo->goods_name) . '_01.jpg" width="100%" style=""/></p><p><img src="/images/total_image/' . base64_encode($goodsInfo->goods_name) . '_02.jpg" width="100%" style=""/></p><p><img src="/images/goodetail/head.jpg" width="100%" style=""/></p><p><img src="/images/goodetail/footer.jpg" width="100%" style=""/></p><p><br/></p>',
            'goodssn' => $goodsInfo->goodssn,
            'productsn' => '',
            'productprice'=>$goodsInfo->price,
            'marketprice' => $goodsInfo->price,
            'total' => mt_rand(111, 999),
            'totalcnf' => 1,
            'sales' => 0,
            'createtime' => time(),
            'maxbuy'=>99,
            'isnew' => mt_rand(0,1),
            'isrecommand' => mt_rand(0,1),
            'viewcount' => mt_rand(11, 999),
            'cash' => 1,
            'isverify' => 1,
            'noticetype' => 0,
            'supplier_uid' => 250,
            'diyformtype' => 1,
            'dispatchtype' => 1,
            'diyformid' => 5,
            'isverifysend' => 0,
            'love_money' => 0.00,
            'isopenchannel' => 0,
            'yunbi_consumption' => 0.000,
            'yunbi_commission' => 0.000,
            'isyunbi' => 0,
            'yunbi_deduct' => 0.00,
            'isforceyunbi' => 0,
            'isdeclaration' => 0,
            'virtual_declaration' => 0,
            'CIQGoodsNo' => $goodsInfo->ciq_goodsno,
            'CusGoodsNo' => $goodsInfo->cus_goodsno,
        ]);
        return $id;
    }

    /**
     * @param int $uniacid 公众号id
     * @param int $goodsId 商品id
     * @param array $goodsInfo 商品信息
     */
    public function addGoodsParam($uniacid, $goodsId, $goodsInfo)
    {
        $param = [
            [
                'uniacid' => $uniacid,
                'goodsid' => $goodsId,
                'title' => '产品名称',
                'value' => $goodsInfo['goods_name'],
                'displayorder' => 0,
            ],
            [
                'uniacid' => $uniacid,
                'goodsid' => $goodsId,
                'title' => '型号规格',
                'value' => $goodsInfo['goods_style'],
                'displayorder' => 0,
            ],
            [
                'uniacid' => $uniacid,
                'goodsid' => $goodsId,
                'title' => '进口备案',
                'value' => $goodsInfo['ciq_goodsno'],
                'displayorder' => 0,
            ],
            [
                'uniacid' => $uniacid,
                'goodsid' => $goodsId,
                'title' => '毛重',
                'value' => $goodsInfo['grosswt'],
                'displayorder' => 1,
            ],
            [
                'uniacid' => $uniacid,
                'goodsid' => $goodsId,
                'title' => '厂家',
                'value' => $goodsInfo['factory'],
                'displayorder' => 1,
            ],
            [
                'uniacid' => $uniacid,
                'goodsid' => $goodsId,
                'title' => '品牌',
                'value' => $goodsInfo['brand'],
                'displayorder' => 2,
            ],
            [
                'uniacid' => $uniacid,
                'goodsid' => $goodsId,
                'title' => '品质',
                'value' => '合格',
                'displayorder' => 3,
            ],
        ];
        Db::name('sz_yi_goods_param')->insertAll($param);
    }


    /**
     * 插入申报表
     * @param array $goodsInfo 总库商品信息
     * @param array $goodsNo 商户填写的商品信息
     */
    public function addRegGoodsTable($goodsInfo, $goodsNo)
    {
        Db::name('foll_goodsreglist')->insert([
            'head_id' => 0, // 头ID
            'Seq' => 0, //商品序号
            'EntGoodsNo' => $goodsNo["user_goodsno"], //商品货号
            'CIQGoodsNo' => $goodsInfo["ciq_goodsno"],//备案编号
            'ShelfGName' => $goodsInfo["goods_name"], //上架品名
            'NcadCode' => '', //行邮税号
            'HSCode' => $goodsInfo['hscode'], //HS编码
            'GoodsName' => $goodsInfo["goods_name"], //商品名称
            'GoodsStyle' => $goodsInfo["goods_style"], //型号规格
            'Brand' => $goodsInfo['brand'], //品牌
            'GUnit' => $goodsInfo['gunit'], //申报计量单位
            'StdUnit' => $goodsInfo['std_unit'], //第一法定计量单位
            'SecUnit' => $goodsInfo['sec_unit'], //第二法定计量单位
            'RegPrice' => $goodsNo["user_price"],//$goodsInfo->price单价
            'GiftFlag' => 1, //是否赠品
            'OriginCountry' => $goodsInfo['origin_country'], //目的国及原产国
            'Quality' => '合格', //商品品质
            'Manufactory' => $goodsInfo['factory'], //生产厂家
            'NetWt' => $goodsInfo['netwt'], //净重
            'GrossWt' => $goodsInfo['grosswt'], //毛重
            'EmsNo' => '', //账册号
            'ItemNo' => '', //项号
            'BarCode' => $goodsInfo['qr_code'], //商品条形码
            'QualityCertify' => '合格', //品质证明说明
            'Notes' => '', //备注（网址）
            'CateName' => $goodsInfo['class'], //分类名称
            'CIQGRegStatus' => 'C', //C-成功备案，成功修改，成功取消备案；N-备案不成功
            'OpType' => 1, // 1-新增；2-变更；3-删除
            'OpTime' => date('Y-m-d H:i:s', time()),
            'CusGoodsNo' => '',
            'supplier_id' => $goodsNo['form_data']['merchantName'],
            'uniacid_name' => $goodsNo['form_data']['uniacidName'],
            'discount_price'=>$goodsInfo['discount_price']
        ]);
    }


    /**
     * 插入申报表
     * @param array $goodsInfo 总库商品信息
     */
    public function addRegGoodsTableFormStock($goodsInfo)
    {
        Db::name('foll_goodsreglist')->insert([
            'head_id' => 0, // 头ID
            'Seq' => 0, //商品序号
            'EntGoodsNo' => $goodsInfo["goodssn"], //商品货号
            'CIQGoodsNo' => $goodsInfo["ciq_goodsno"],//备案编号
            'ShelfGName' => $goodsInfo["goods_name"], //上架品名
            'NcadCode' => '', //行邮税号
            'HSCode' => $goodsInfo['hscode'], //HS编码
            'GoodsName' => $goodsInfo["goods_name"], //商品名称
            'GoodsStyle' => $goodsInfo["goods_style"], //型号规格
            'Brand' => $goodsInfo['brand'], //品牌
            'GUnit' => $goodsInfo['gunit'], //申报计量单位
            'StdUnit' => $goodsInfo['std_unit'], //第一法定计量单位
            'SecUnit' => $goodsInfo['sec_unit'], //第二法定计量单位
            'RegPrice' => $goodsInfo['price'],//$goodsInfo->price单价
            'GiftFlag' => 1, //是否赠品
            'OriginCountry' => $goodsInfo['origin_country'], //目的国及原产国
            'Quality' => '合格', //商品品质
            'Manufactory' => $goodsInfo['factory'], //生产厂家
            'NetWt' => $goodsInfo['netwt'], //净重
            'GrossWt' => $goodsInfo['grosswt'], //毛重
            'EmsNo' => '', //账册号
            'ItemNo' => '', //项号
            'BarCode' => $goodsInfo['qr_code'], //商品条形码
            'QualityCertify' => '合格', //品质证明说明
            'Notes' => '', //备注（网址）
            'CateName' => $goodsInfo['class'], //分类名称
            'CIQGRegStatus' => 'C', //C-成功备案，成功修改，成功取消备案；N-备案不成功
            'OpType' => 1, // 1-新增；2-变更；3-删除
            'OpTime' => date('Y-m-d H:i:s', time()),
            'CusGoodsNo' => '',
            'supplier_id' =>'我的供应商',
            'uniacid_name' =>'Gogo購購網',
            'discount_price'=>$goodsInfo['price']
        ]);
    }


    /**
     * 查看是否存在该商品
     * @param $goodsNo
     * @return array|bool|false|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function isExistsRegTableGoods($goodsNo){
        return Db::name('foll_goodsreglist')->where('EntGoodsNo',$goodsNo)->field('id')->find();
    }

    /**
     * 查看是否存在该商品
     * @param $goodsNo
     * @return array|bool|false|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function isExistsShopTableGoods($goodsNo){
        return Db::name('sz_yi_goods')->where('goodssn',$goodsNo)->field('id')->find();
    }
}