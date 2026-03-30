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
 * Class WechatPackage
 * @package app\api_v3\controller
 */
class WechatPackage
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
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();

        $openid  = $params['openid'];

        $list = Db::name('smallwechat_user_package')->where(array('openid'=>$openid,'is_shou'=>0))->select();
        foreach ($list as $k => $v) {
            $list[$k]['goods'] = Db::name('smallwechat_user_package_goods')
            ->alias("pg")
            ->join('sz_yi_goods g', 'pg.goods_id = g.id')
            ->field('pg.*,g.title,g.thumb')
            ->where(array('pg.package_id'=>$v['id'],'pg.is_delete'=>0))
            ->select();

            foreach ($list[$k]['goods'] as $kk => $vv) {
                $list[$k]['goods'][$kk]['thumb'] = "https://shop.gogo198.cn/addons/sz_yi/static/images/det_01.jpg";
            }
        }
        $result['list'] = $list;
        return $this->returnHandler($result);
    }

    //获取运单号
    public function getordersn()
    {
        $ordersn = date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        //查询该商户是否分配订单号
        $result['ordersn_type'] = 2; //1正式 2临时
        $result['ordersn'] = $ordersn; //1正式 2临时
        return $this->returnHandler($result);
    }

    //获取商品
    public function getgoods()
    {
        $validate = new BaseValidate([
            'code'          =>'require',
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();

        $barcode  = $params['code'];
        $EntGoodsNo = Db::name('foll_goodsreglist')->where(array('BarCode'=>$barcode))->value('EntGoodsNo');
        if(empty($EntGoodsNo))
        {
            $result['msg'] = '暂无该商品';
            $result['goods'] = null;

        }else{
            $goods = Db::name('sz_yi_goods')->field('id,title,thumb')->where(array('goodssn'=>$EntGoodsNo))->find();

            if(empty($goods))
            {
                $result['msg'] = '暂无该商品';
                $result['goods'] = null;
            }else {
                $goods['thumb'] = "https://shop.gogo198.cn/addons/sz_yi/static/images/det_01.jpg";
                $result['msg'] = '找到商品';
                $result['goods'] = $goods;
            }
        }

        return $this->returnHandler($result);
    }

    //打包
    public function doPackage()
    {
        $validate = new BaseValidate([
            'openid'          =>'require',
            'decl_user_id'    =>'isDefault',
            'ordersn'         =>'require',
            'ordersn_type'    =>'require',
            'goods_ids'       =>'isDefault',
            'wait_goods_ids'  =>'isDefault',
            'witegoods'       =>'isDefault'
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();

        $data['openid'] = $params['openid'];
        $data['decl_user_id'] = $params['decl_user_id'];
        $data['ordersn'] = $params['ordersn'];
        $data['ordersn_type'] = $params['ordersn_type'];
        $data['create_time'] = time();

        $package_id = Db::name('smallwechat_user_package')->insertGetId($data);

        if($package_id)
        {
            SaveWechatUserLogs($data['openid'],'打包了一个包裹,包裹id为:'.$package_id.',运单号为:'.$data['ordersn'],'package',$data['ordersn'],$package_id,$data['decl_user_id']);
            if($params['goods_ids'] != '')
            {
                $goods_ids = rtrim($params['goods_ids'], ',');
                $goods = explode(",",$goods_ids);
                $g_data = array();
                $g_data['package_id'] = $package_id;
                foreach ($goods as $k => $v) {
                    $g_data['goods_id'] = $v;
                    Db::name('smallwechat_user_package_goods')->insert($g_data);
                }
            }

            if($params['wait_goods_ids'] != '')
            {
                $wait_goods_ids = rtrim($params['wait_goods_ids'], ',');
                $wait_goods = explode(",",$wait_goods_ids);
                $w_data = array();
                $w_data['package_id'] = $package_id;
                $witegoods = $params['witegoods'];
                foreach ($wait_goods as $k => $v) {
                    $w_data['goods_id'] = $v;
                    Db::name('smallwechat_user_package_goods')->insert($w_data);
                    if($witegoods[$k]['good_id'] == $v)
                    {
                        Db::name('smallwechat_user_package_goods_delete')->update(array('id'=>$witegoods[$k]['id'],'is_rest'=>1));
                    }
                }

            }

            //更新包裹商品数量
            $goods_num = Db::name('smallwechat_user_package_goods')->where(array('package_id'=>$package_id))->count();
            Db::name('smallwechat_user_package')->update(array('id'=>$package_id,'goods_num'=>$goods_num));
            $result['status'] = 1;
        }
        else {
            $result['status'] = 0;
        }
        return $this->returnHandler($result);
    }












}
