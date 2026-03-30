<?php
if (!(defined('IN_IA'))) {
	exit('Access Denied');
}
class Money_EweiShopV2Model
{
    protected $openid=null;
    protected $num=null;
    protected $starTime=null;
    protected $endTime=null;
    protected $data=null;
    protected $total=null;
    public function __construct($openid,$num,$starTime,$endTime,$total='')
    {
        $this->openid=$openid;
        $this->num=$num;
        $this->starTime=$starTime;
        $this->endTime=$endTime;
        $this->total=$total;
        return $this;
    }
    public static total(){
        echo '2';
    }
}
