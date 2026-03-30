<?php

namespace app\declares\controller;
use think\Model;

class GenerateGoodsInfoExcel extends Model
{
    /*
     * 获取该用户下所有商品备案信息
     */
    function getAllGoodsRecInfo($uid=0)
    {
//        return Db::name("foll_goodsreghead")
//            ->alias('tb1')
//            ->join('foll_goodsreglist tb2','tb1.id=tb2.head_id')
//            ->field([
//                'tb1.DeclEntNo',
//                'tb1.DeclEntName',
//                'tb1.EBEntNo',
//                'tb1.EBEntName',
//                'tb1.CIQOrgCode',
//                'tb1.EBPEntNo',
//                'tb1.EBPEntName',
//            ])
//            ->where('tb1.uid',$uid)
//            ->select();
    }

    /*
     * 返回文件名称跟路径
     */
    function writeExcelFile()
    {

    }
}