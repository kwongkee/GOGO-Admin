<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Request;
use think\Db;
use PHPExcel;
use PHPExcel_IOFactory;

class TravelExpress extends Auth
{
    //类目配置
    public function cates()
    {
        return view();
    }

    public function get_list(Request $request)
    {
        if ($request->isAJAX()) {
            $list = Db::name('customs_travelexpress_cates')->select();
            return json(['code' => 0, 'msg' => '', 'count' => count($list), 'data' => $list]);
        } else {
            return json(['code' => -1, 'msg' => '未知错误']);
        }
    }

    public function cates_add(Request $request)
    {
        if($request->isPost())
        {
            $data = $request->post();
            $data['create_time'] = time();
            Db::startTrans();
            try {
                Db::name('customs_travelexpress_cates')->insert($data);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return json(['code' => -1, 'msg' => '添加失败'.$e->getMessage()]);
            }
            return json(['code' => 0, 'msg' => '添加成功']);
        }else{
            $pid = $request->get('pid');
            $this->assign('pid', $pid);
            return view();
        }
    }

    public function cates_edit(Request $request)
    {
        if($request->isPost())
        {
            $data = $request->post();
            $id = $data['id'];
            unset($data['id']);
            Db::startTrans();
            try {
                Db::name('customs_travelexpress_cates')->where('id',$id)->update($data);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return json(['code' => -1, 'msg' => '编辑失败'.$e->getMessage()]);
            }
            return json(['code' => 0, 'msg' => '编辑成功']);
        }else{
            $this->assign('data', Db::name('customs_travelexpress_cates')->where('id',$request->get('id'))->find());
            return view();
        }
    }

    public function cates_del(Request $request)
    {
        $id = $request->get('id');
        $chlid_data = Db::name('customs_travelexpress_cates')->where('pid',$id)->count();
        if($chlid_data>0)
        {
            return json(['code' => -1, 'msg' => '请先删除子分类']);
        }else{
            Db::name('customs_travelexpress_cates')->where('id',$id)->delete();
            return json(['code' => 0, 'msg' => '已删除']);
        }
    }

    //品牌设置
    public function brand(Request $request)
    {
        $cate_id = $request->get('cates_id');
        $this->assign('cate_id', $cate_id);
        return view();
    }

    public function getbrand(Request $request)
    {
        $cate_id = $request->get('cates_id');
        $list = Db::name('customs_travelexpress_brand')->where(['cate_id'=>$cate_id, 'is_delete'=>0])->select();
        return json(['code' => 0, 'msg' => '', 'count' => count($list), 'data' => $list]);
    }

    public function brand_add(Request $request)
    {
        if($request->isPost())
        {
            $data = $request->post();
            $data['create_time'] = time();
            Db::startTrans();
            try {
                Db::name('customs_travelexpress_brand')->insert($data);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return json(['code' => -1, 'msg' => '添加失败'.$e->getMessage()]);
            }
            return json(['code' => 0, 'msg' => '添加成功']);
        }else{
            $cate_id = $request->get('cate_id');
            $this->assign('cate_id', $cate_id);
            return view();
        }
    }

    public function brand_edit(Request $request)
    {
        if($request->isPost())
        {
            $data = $request->post();
            $id = $data['id'];
            unset($data['id']);
            Db::startTrans();
            try {
                Db::name('customs_travelexpress_brand')->where('id',$id)->update($data);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return json(['code' => -1, 'msg' => '编辑失败'.$e->getMessage()]);
            }
            return json(['code' => 0, 'msg' => '编辑成功']);
        }else{
            $this->assign('data', Db::name('customs_travelexpress_brand')->where('id',$request->get('id'))->find());
            return view();
        }
    }

    public function brand_del(Request $request)
    {
        Db::name('customs_travelexpress_brand')->where('id',$request->get('id'))->delete();
        return json(['code' => 0, 'msg' => '已删除']);
    }

    //用户订单
    public function orders()
    {
        return view();
    }

    public function getOrderList(Request $request)
    {
        $order = input('sort').' '.input('order');
        $limit = input('offset').','.input('limit');
        $map = array();
        $search = input('search');
        if($search)
        {
            $map['ordersn'] = ['like','%'.$search.'%'];
        }
        $list = Db::name('customs_travelexpress_order_info')->where($map)->order($order)->limit($limit)->select();
        foreach ($list as $k => $v) {
            $member = Db::name('sz_yi_member')->field('nickname,avatar')->where(['openid'=>$v['openid'],'uniacid'=>$v['uniacid']])->find();
            if($member){
                $member['avatar'] = substr($member['avatar'],0,-3);
                $list[$k]['member'] = '<img width="50" height="50" src="'.$member['avatar'].'">'.$member['nickname'];
            }
            
            switch($v['select_type']){
                case 1:
                    $list[$k]['select_text'] = '智能柜-'.$v['smart_code'];
                    break;
                case 2:
                    $list[$k]['select_text'] = '仓库交收-'.$v['warehouse'];
                    break; 
                case 3:
                    $list[$k]['select_text'] = '上门揽收-'.$v['address'];
                    break;         
            }
            switch($v['status']){
                case 0:
                    $list[$k]['status'] = '审核中';
                    break;
                case 1:
                    $list[$k]['status'] = '审核通过';
                    break; 
                case 2:
                    $list[$k]['status'] = '审核不通过';
                    break;  
                case 3:
                    $list[$k]['status'] = '已确认';
                    break;         
            }
            $list[$k]['manage'] = '<button style="margin-right: 5px;" type="button" onclick="seeGoods('."'".$v['id']."'".')" class="btn btn-primary btn-xs">查看物品</button>';
            $list[$k]['manage'] .= '<button style="margin-right: 5px;" type="button" onclick="printOrder('."'".$v['ordersn']."'".')" class="btn btn-primary btn-xs">面单打印</button>';
            $list[$k]['manage'] .= '<button style="margin-right: 5px;" type="button" onclick="exportOrder('."'".$v['ordersn']."'".')" class="btn btn-primary btn-xs">导出Excel</button>';
            $list[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
        }
        $total = Db::name('customs_travelexpress_order_info')->count();
        return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
    }

    public function getOrderDetail(Request $request)
    {
        $id = input('id');
        $order_info = Db::name('customs_travelexpress_order_info')->where('id',$id)->find();
        $goods = Db::name('customs_travelexpress_order')->where('ordersn',$order_info['ordersn'])->select();
        switch($order_info['select_type']){
            case 1:
                $order_info['select_text'] = '智能柜-'.$order_info['smart_code'];
                break;
            case 2:
                $order_info['select_text'] = '仓库交收-'.$order_info['warehouse'];
                break; 
            case 3:
                $order_info['select_text'] = '上门揽收-'.$order_info['address'];
                break;         
        }
        $this->assign('order_info', $order_info);
        foreach ($goods as $k => $v) {
            $goods[$k]['cates'] = Db::name('customs_travelexpress_cates')->where('id',$v['cates'])->value('name');
            $goods[$k]['cates_c'] = Db::name('customs_travelexpress_cates')->where('id',$v['cates_c'])->value('name');
        }
        $this->assign('goods', $goods);
        return view();
    }

    public function orderCheck(Request $request)
    {
        $ordersn = input('ordersn');
        $order_id = input('order_id');
        $id = input('id/a');
        $status = input('status/a');
        $remark = input('remark/a');

        $datas = Db::name('customs_travelexpress_order_info')->where('id',$order_id)->find();
        if($datas['status']==3)
        {
            return json(["status" => 0, "message" => "该订单已确认，无需再审核！"]);
        }
        $no_num = 0;
        foreach ($status as $k => $v) {
            if($v==2)
            {
                $no_num++;
            }
            Db::name('customs_travelexpress_order')->where('id',$id[$k])->update(['status'=>$v, 'remark'=>$remark[$k]]);
        }

        $order_status = $no_num!=0 ? 2:1;
        Db::name('customs_travelexpress_order_info')->where('id',$order_id)->update(['status'=>$order_status, 'remark'=>$order_status==1 ? '审核通过！' :'请修改资料！']);
        return json(["status" => 1, "message" => "审核成功"]);
    }

    public function printOrder(Request $request)
    {
        $ordersn = input('ordersn');
        Db::name('customs_elec_order_queue')->insert(['queue'=>'ccGoods','payload'=>$ordersn,'create_time'=>time()]);
        return json(["status" => 1, "msg" => "已提交后台打印"]);
    }

    public function orderExport(Request $request)
    {
        $ordersn = input('ordersn');
        $order = Db::name('customs_travelexpress_order_info')->where('ordersn',$ordersn)->find();
        if($order['status']!=3)
        {
            return json(["status" => 0, "msg" => "该订单还未确认，不能导出"]);
        }else{
            return json(["status" => 1, "msg" => "正在导出"]);
        }
    }

    public function downExcel(Request $request)
    {
        $ordersn = input('ordersn');
        $order = Db::name('customs_travelexpress_order_info')->where('ordersn',$ordersn)->find();
        $list = Db::name('customs_travelexpress_order')->where('ordersn',$ordersn)->select();
        $member = Db::name('sz_yi_member_address')->where('id',$order['collect_id'])->find();
        $setFieldNickName = [
            'A1' => '客户',
            'B1' => '日期',
            'C1' => '顺序号',
            'D1' => '口岸代码',
            'E1' => '客户原单号',
            'F1' => '转单号（EMS)',
            'G1' => '牌子（大写英文）',
            'H1' => '牌子（中文）',
            'I1' => '品名',
            'J1' => '数量',
            'K1' => '规格',
            'L1' => '容量单位（中文）',
            'M1' => '包装单位',
            'N1' => '1.物品系列/型号',
            'O1' => '2.材质/段数',
            'P1' => '毛重',
            'Q1' => '货物单价',
            'R1' => '收件人',
            'S1' => '收件电话',
            'T1' => '收件邮编',
            'U1' => '收件人地址',
            'V1' => '收件省份',
            'W1' => '收件人城市',
            'X1' => '收件身份证号',
            'Y1' => '发件人',
            'Z1' => '发件电话',
            'AA1' => '发件邮编',
            'AB1' => '发件地址',
            'AC1' => '发件国家代码',
            'AD1' => '发件城市（英文）',
            'AE1' => '净重/数量',
            'AF1' => '计量单位代码',
            'AG1' => '保险金',
        ];

        try {

            $PHPExcel = new PHPExcel();
            $PHPExcel->setActiveSheetIndex(0);
            $PHPSheet = $PHPExcel->getActiveSheet();
            $PHPSheet->setTitle('sheet1');
            foreach ($setFieldNickName as $key => $val) {
                $PHPSheet->setCellValue($key, $val);
            }

            $n = 2;
            foreach ($list as $val) {
                $PHPSheet->setCellValue('A' . $n, '')
                    ->setCellValue('B' . $n, '')
                    ->setCellValue('C' . $n, '')
                    ->setCellValue('D' . $n, '')
                    ->setCellValue('E' . $n, $val['item_no'])
                    ->setCellValue('F' . $n, '')
                    ->setCellValue('G' . $n, $val['brand_en'])
                    ->setCellValue('H' . $n, $val['brand_cn'])
                    ->setCellValue('I' . $n, $val['good_name'])
                    ->setCellValue('J' . $n, $val['num'])
                    ->setCellValue('K' . $n, $val['specs'])
                    ->setCellValue('L' . $n, $val['specs2'])
                    ->setCellValue('M' . $n, $val['specs3'])
                    ->setCellValue('N' . $n, $val['model'])
                    ->setCellValue('O' . $n, $val['material'])
                    ->setCellValue('P' . $n, $val['weight'])
                    ->setCellValue('Q' . $n, '')
                    ->setCellValue('R' . $n, $member['realname'])
                    ->setCellValue('S' . $n, " ".$member['mobile'])
                    ->setCellValue('T' . $n, $member['zipcode'])
                    ->setCellValue('U' . $n, $member['address'])
                    ->setCellValue('V' . $n, $member['province'])
                    ->setCellValue('W' . $n, $member['city'])
                    ->setCellValue('X' . $n, " ".$member['idcard'])
                    ->setCellValue('Y' . $n, '')
                    ->setCellValue('Z' . $n, '')
                    ->setCellValue('AA' . $n, '')
                    ->setCellValue('AB' . $n, '')
                    ->setCellValue('AC' . $n, '')
                    ->setCellValue('AD' . $n, '')
                    ->setCellValue('AE' . $n, '')
                    ->setCellValue('AF' . $n, '')
                    ->setCellValue('AG' . $n, '');
                $n += 1;
            }
            unset($list);
            $phpWrite = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
            $file     = $ordersn . '-CC物品申报数据.xlsx';
            ob_end_clean();  //清空缓存
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");
            header('Content-Disposition:attachment;filename="' . $file . '"');
            header("Content-Transfer-Encoding:binary");
            $phpWrite->save('php://output');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage() . '|行号：' . $e->getLine());
        }
    }

    //打印配置
    public function print(Request $request)
    {
        if($request->isPost() || $request->isAjax())
        {
            $id = input('id');
            $data['partner_id'] = input('partner_id');
            $data['partner_key'] = input('partner_key');
            $data['express_code'] = input('express_code');
            $data['company_name'] = input('company_name');
            $data['company_tel'] = input('company_tel');
            $data['address'] = input('address');
            $data['device_id'] = input('device_id');
            $data['temp_id'] = input('temp_id');
            if( Db::name('customs_travelexpress_print_config')->where('id',$id)->update($data) )
            {
                return json(["status" => 1, "message" => "保存成功"]);
            }else{
                return json(["status" => 0, "message" => "保存失败"]);
            }
        }else{
            $express = Db::name('customs_express_company_code')->select();
            $printData = Db::name('customs_travelexpress_print_config')->where('id',1)->find();
            $this->assign('printData', $printData);
            $this->assign('express', $express);
            return view();
        }
        
    }

}