<?php
namespace app\coupon\controller;

use app\coupon\controller;
use think\Request;

class User extends Base{
    protected $commModel;
    protected $User;
    public function __construct() {
        parent::__construct();
        $this->commModel = model('Common','model');
        $this->User = model('User','model');
    }

    /**
     * 设置
     * @param Request $request
     * @return mixed
     */
    public function setCompanyInfo(Request $request){
        if ($request->isGET()){
            $blist = $this->commModel->getAllWechatsAccount();
            return view('user/set_company_info',['publicAccoount'=>$blist]);
        }

        if ($request->isPOST()){
            $data = $request->post();
            if (empty($data)){
                return json(['code'=>-1,'msg'=>'错误']);
            }
            try{
                $this->User->updateUserConfig(['id'=>Session('business.id')],$data);
            }catch (\Exception $e){
                return json(['code'=>-1,'msg'=>$e->getMessage()]);
            }
            return json(['code'=>0,'msg'=>'配置成功']);
        }
    }

}