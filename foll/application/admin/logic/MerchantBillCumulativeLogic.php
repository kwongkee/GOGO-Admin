<?php

namespace app\admin\logic;

use mysql_xdevapi\Exception;
use think\Model;
use think\Db;

/**
 * 查验率管理
 * Class MerchantBillCumulativeLogic
 * @package app\admin\logic
 */
class MerchantBillCumulativeLogic extends Model
{

    /**
     * 获取商户累积查验率
     * @return array
     */
    public function getCompanyList($request)
    {

        $checkWhere = '';
        $query      = ['s' => 'admin/billrate/grandtotal'];
        if ($request->has('time')) {
            $datebeet    = $request->get('time');
            $query['s']  = 'admin/billrate/grandtotal&time=' . $datebeet;
            $datebeet    = explode('|', $datebeet);
            $datebeet[0] = strtotime($datebeet[0] . ' 00:00:00');
            $datebeet[1] = strtotime($datebeet[1] . ' 23:59:00');
            $checkWhere  = "created_at>=" . $datebeet[0] . " and created_at<=" . $datebeet[1];

        }
        $total     = Db::name('decl_user')->where('parent_id', 0)->count();
        $list      = Db::name('decl_user')->where('parent_id', 0)->paginate(10, $total, [
            'query'    => $query,
            'var_page' => 'page',
        ]);//获取总商户
        $businList = $list->toArray()['data'];
        $page      = $list->render();

        foreach ($businList as &$value) {
            $_checkWhere                    = $checkWhere == '' ? "uid=" . $value['id'] : "uid=" . $value['id'] . " and " . $checkWhere;
            $value['rate']                  = Db::name('customs_check_value')->where($_checkWhere)->select();
            $value['rate']['check_num']     = 0;
            $value['rate']['fasteners_num'] = 0;
            $value['rate']['return_num']    = 0;
            $value['rate']['release_num']   = 0;
            if (!empty($value['rate'])) {
                foreach ($value['rate'] as $item) {
                    $value['rate']['check_num']     += $item['check_num'];
                    $value['rate']['fasteners_num'] += $item['fasteners_num'];
                    $value['rate']['return_num']    += $item['return_num'];
                    $value['rate']['release_num']   += $item['release_num'];
                }
            }
            $child = Db::name('decl_user')->where('parent_id', $value['id'])->select();
            if (empty($child)) {
                continue;
            }
            $cid = '';
            foreach ($child as $val) {
                $cid .= $val['id'] . ',';
            }
            $_checkWhere = $checkWhere == '' ? "uid in (" . trim($value['id']) . ")" : "uid in (" . trim($value['id']) . ") and " . $checkWhere;
            $_crate      = Db::name('customs_check_value')->where($_checkWhere)->select();
            if (empty($_crate)) {
                continue;
            }
            foreach ($_crate as $item) {
                $value['rate']['check_num']     += $item['check_num'];
                $value['rate']['fasteners_num'] += $item['fasteners_num'];
                $value['rate']['return_num']    += $item['return_num'];
                $value['rate']['release_num']   += $item['release_num'];
            }
        }
        return [$businList, $page];
    }

    /**
     * 拼接总商户id
     * @param $businList
     * @return string|null
     */
    protected function _spliceIdString($businList)
    {
        $id = null;
        foreach ($businList as $value) {
            $id .= $value['id'] . ',';
        }
        return $id;
    }


    /**
     * 添加或更新查验率峰值
     * @param $data
     * @throws \Exception
     */
    public function setBillRatePeak($data)
    {
        if (!isset($data['uid']) || $data['uid'] == '') {
            throw new \Exception('参数错误');
        }
        if (!isset($data['check_rate']) || !isset($data['fastener_rate']) || !isset($data['return_rate']) || !isset($data['release_rate'])) {
            throw new \Exception('参数错误');
        }
        //判断是否已有，有则更新，无则添加
        $result = Db::name('customs_check_warning_value')->where('uid', $data['uid'])->field('id')->find();

        if (empty($result)) {
            Db::name('customs_check_warning_value')->insert($data);
        } else {
            unset($data['uid']);
            Db::name('customs_check_warning_value')->where('id', $result['id'])->update($data);
        }
    }




    public function getUserLists()
    {
        $total = Db::name('decl_user')->where('parent_id',0)->count();
        $list = Db::name('decl_user')
            ->alias('a1')
            ->join('customs_check_file a2','a1.id=a2.user_id','left')
            ->where('a1.parent_id',0)
            ->field(['a1.id','a1.user_name','a1.company_name','a2.*'])
            ->paginate(10,$total,['query' => ['s' => 'admin/uploadBillCheckFileCondition'], 'var_page' => 'page']);
        return $list;
    }

    /**
     * 添加上传所需文件内容
     * @param $data
     * @throws \Exception
     */
    public function uploadBillCheckFileConditions($data)
    {
        if ((isset($data['id'])&&$data['id']=='')||$data['id']==''||!is_numeric($data['id'])){
            throw new \Exception('参数错误id');
        }
        if ((isset($data['type'])&&$data['type']=='')||$data['type']==''){
            throw new \Exception('参数错误type');
        }
        $isEmpty = Db::name('customs_check_file')->where('user_id',$data['id'])->find();
        Db::startTrans();
        try{
            if($isEmpty){
                //更新
                Db::name('customs_check_file')->where('user_id',$data['id'])->update(['file_type'=>$data['type']]);
            }else{
                Db::name('customs_check_file')->insert(['user_id'=>$data['id'],'file_type'=>$data['type']]);
            }
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            throw new \Exception($e->getMessage());
        }

    }
}
