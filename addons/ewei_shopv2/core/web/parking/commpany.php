<?php 



if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Commpany_EweiShopV2Page extends WebPage
{
    public function main()
    {
        global $_W;
        global $_GPC;
        $commpanyData=pdo_get("foll_commpany",array("uniacid"=>$_W['uniacid']));
        include $this->template("parking/commpany");
    }
    public function saveCommpanyInformation()
    {
        global $_W;
        global $_GPC;
        $id=$_GPC['id'];
        $insertData=[
            'uniacid'=>$_W['uniacid'],
            'name'=>$_GPC['commpany_name'],
            'project_name'=>$_GPC['project_name'],
            'short_title'=>$_GPC['title'],
            'tel'=>$_GPC['tel'],
            'num'=>$_GPC['num']
        ];
        if($id ==null){
            if(pdo_insert("foll_commpany",$insertData)){
                show_json(1);
            }
            show_json(0);
        }else{
            if(pdo_update("foll_commpany",$insertData,array('id'=>$id))){
                show_json(1);
            }
            show_json(0);
        }
    }
}