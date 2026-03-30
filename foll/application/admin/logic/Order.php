<?php

namespace app\admin\logic;

use think\Model;

class Order extends Model
{
    protected $querys="admin/order";
    protected $var_page="page";
    protected $offet=8;
    protected $requests;
    /*
     * 返回总数据
     */
    public  function totalOrderData($request)
    {
        $this->requests=$request;//复制给类属性
        $c=['query'=>
            [
                's'=>$this->querys."&app=".$request->get("app")."&stime=".$request->param("stime")."&etime=".$request->param("etime")."&tel=".$request->param("tel")
            ],
            'var_page'=>$this->var_page
        ];
       if(!empty($request->get("app"))){
            return $this->singApplication($c,$this->whereSql());//返回单个应用订单数据
       }
            //返回全部应用订单数据
        return $this->allApplication($c);
    }


    /*
     * 返回所有应用订单
     */

    protected function allApplication($conf)
    {
        $where=null;
        if(!empty($this->requests->param("stime"))&&!empty($this->requests->param("etime"))){
            $where['order.create_time']=[['>=',strtotime($this->requests->param("stime"))],['<=',strtotime($this->requests->param('etime'))+86399]];
        }
        if(!empty($this->requests->param('tel'))){
            $where['order.ordersn']=trim($this->requests->param("tel"));
        }
        return $this->singApplication($conf,$where);
    }
    /*
     * 返回单个应用订单
     */
    protected function singApplication($config,$where)
    {

        //$total=Order::table("ims_foll_order")->where($where)->count('id');
        //$data=Order::table("ims_foll_order")->where($where)->paginate($this->offet,$total,$config);
        $total=Order::table("ims_foll_member member,ims_foll_order order")
            ->where("member.unique_id=order.user_id")
            ->where($where)
            ->count('order.id');
        $data=Order::table("ims_foll_member member,ims_foll_order order")
            ->where("member.unique_id=order.user_id")
            ->where($where)
            ->field('member.name,order.*')
            ->order('order.id desc')
            ->paginate($this->offet,$total,$config);
        unset($this->requests);
        return $data;
    }

    /*
    * 生成查找条件sql
    */
    protected function whereSql()
    {
        $sql=null;
        if(!empty($this->requests->param('tel'))){
            $sql['order.application']=$this->requests->get('app');
            $sql['order.ordersn']=trim($this->requests->param('tel'));
            return $sql;
        }
        if(!(empty($this->requests->param('stime'))&&empty($this->requests->param('etime'))))
        {
            $sql['order.application']=['=',$this->requests->get('app')];
            $sql['order.create_time']=[['>=',strtotime($this->requests->param('stime'))],['<=',strtotime($this->requests->param('etime'))+86399]];
            return $sql;
        }
        if(!empty($this->requests->get("app"))){
            $sql['order.application']=$this->requests->get('app');
//            $sql="order.application=".$this->requests->get('app');
            return $sql;
        }
        return $sql;
    }

    /*
     * 订单详情
     */

    public function orderInfo($id)
    {
        return Order::table("ims_foll_order order,ims_parking_order parking")
            ->where("parking.ordersn=order.ordersn")
            ->where("order.id",$id)
            ->field('parking.*,order.*')
            ->select();
    }
}

