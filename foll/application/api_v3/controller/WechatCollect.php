<?php

namespace app\api_v3\controller;

use think\Db;
use think\Request;
use think\Env;
use think\Cache;
use app\common\validate\BaseValidate;
use app\lib\exception\member_exception\MemberLoginException;
use app\lib\exception\param_exception\ParameterException;
use app\lib\exception\user_exception\UserQRCodeException;
use app\lib\tools\CurlHandler;
use app\lib\service\Tokens as TokenService;
use app\lib\restful_api\RestfulApiCode;
use app\lib\tools\ResultHandler;
use app\lib\exception\ExceptionErrorCode;

/**
 * 小程序接口
 * Class WechatCollect
 * @package app\api_v3\controller
 */
class WechatCollect
{

    public function __construct()
    {
        $wechat_config = Db::name('smallwechat_config')->where(array('id'=>1))->find();
        $this->appid = $wechat_config['appid'];
        $this->appsecret = $wechat_config['appsecret'];
    }

    function returnHandler($result, $flag = true)
    {
        if (is_int($result) && $result <= 0)
        {
            throw new EmptyResultException();
        }
        else if (!$flag && empty($result))
        {
            throw new EmptyResultException();
        }
        else if (!$flag && ($result instanceof Collection) && $result->isEmpty() )
        {
            throw new EmptyResultException();
        }

        $statusCode = RestfulApiCode::OK;
        if (Request::instance()->isGet())
        {
            $statusCode = RestfulApiCode::OK;
        }
        else if (Request::instance()->isPost() || Request::instance()->isPut() || Request::instance()->isPatch())
        {
            $statusCode = RestfulApiCode::CREATED;
        }
        else if (Request::instance()->isDelete())
        {
            $statusCode = RestfulApiCode::NO_CONTENT;
        }
        return ResultHandler::returnJson('SUCCESS', $result, ExceptionErrorCode::SUCCESS, $statusCode);
    }

    //获取列表
    public function getlist()
    {
        $validate = new BaseValidate([
            'openid'          =>'require',
            'decl_user_id'    =>'require',
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();

        $openid  = $params['openid'];
        $decl_user_id = $params['decl_user_id'];

        $fa_list = Db::name('smallwechat_user_collect')->where(array('form_user_id'=>$decl_user_id))->select();
        foreach ($fa_list as $k => $v) {
            $fa_list[$k]['packageList'] = Db::name('smallwechat_user_package')->field('id,ordersn,goods_num')->where(array('id'=>['in',$v['package_ids']]))->select();
        }

        $shou_list = Db::name('smallwechat_user_collect')->where(array('to_user_id'=>$decl_user_id,'status'=>['neq',2]))->select();
        foreach ($shou_list as $k => $v) {
            $shou_list[$k]['packageList'] = Db::name('smallwechat_user_package')->field('id,ordersn,goods_num')->where(array('id'=>['in',$v['package_ids']]))->select();
        }

        $list = array();
        $list['fa'] = $fa_list;
        $list['shou'] = $shou_list;

        $result['list'] = $list;
        return $this->returnHandler($result);
    }

    //判断是否可以发起揽收
    public function cancollect()
    {
        $validate = new BaseValidate([
            'openid'          =>'require',
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();

        $openid  = $params['openid'];

        $packages = Db::name('smallwechat_user_package')->where(array('openid'=>$openid,'is_collect'=>0))->select();
        if(count($packages) > 0)
        {
            $result['status'] = 1;
            $result['data'] = $packages;
        }
        else{
            $result['status'] = 0;
            $result['data'] = null;
        }

        return $this->returnHandler($result);
    }



    //揽收
    public function doCollect()
    {
        $validate = new BaseValidate([
            'openid'          =>'require',
            'decl_user_id'    =>'require',
            'type'            =>'require',
            'pack_ids'        =>'require'
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();

        $data['openid'] = $params['openid'];
        $data['form_user_id'] = $params['decl_user_id'];
        $data['type'] = $params['type'];
        $data['create_time'] = time();
        $data['package_ids'] = $params['pack_ids'];

        $collect_id = Db::name('smallwechat_user_collect')->insertGetId($data);
        if($collect_id)
        {
            SaveWechatUserLogs($data['openid'],'发起了揽收,包裹id为:'.$data['package_ids'],'collect','0',$collect_id,$data['form_user_id']);

            $packagelist = explode(",",$data['package_ids']);
            foreach ($packagelist as $k => $v) {
                Db::name('smallwechat_user_package')->update(array('id'=>$v,'is_collect'=>1));
            }
            $result['status'] = 1;
        }
        else {
            $result['status'] = 0;
        }
        return $this->returnHandler($result);
    }


    //接收货物
    public function gocollects()
    {
        $validate = new BaseValidate([
            'openid'          =>'require',
            'decl_user_id'    =>'require',
            'collect_id'      =>'require',
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();

        $openid = $params['openid'];
        $decl_user_id = $params['decl_user_id'];
        $collect_id = $params['collect_id'];

        $collectData = Db::name('smallwechat_user_collect')->where(array('id'=>$collect_id))->find();
        if($collectData)
        {
            Db::name('smallwechat_user_collect')->update(array('id'=>$collect_id,'status'=>3));
            $result['status'] = 1;
        }else {
            $result['status'] = 0;
        }
        return $this->returnHandler($result);
    }












}
