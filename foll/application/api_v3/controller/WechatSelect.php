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
 * Class WechatSelect
 * @package app\api_v3\controller
 */
class WechatSelect
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

    //获取待打包列表
    public function getdeletelist()
    {
      $validate = new BaseValidate([
          'openid'          =>'require',
      ]);
      $validate->goCheck();
      $params = $validate->getParameters();

      $openid  = $params['openid'];

      $list = Db::name('smallwechat_user_package_goods_delete')->where(array('openid'=>$openid,'is_rest'=>0))->select();
      foreach ($list as $k => $v) {
        $list[$k]['is_select'] = false;
        $list[$k]['goods_info'] = Db::name('sz_yi_goods')->field('id,title,thumb')->where(array('id'=>$v['good_id']))->find();
        $list[$k]['goods_info']['thumb'] = "https://shop.gogo198.cn/addons/sz_yi/static/images/det_01.jpg";
      }
      $result['list'] = $list;
      return $this->returnHandler($result);
    }

    //获取可分拣运单
    public function getpackageorderlist($value='')
    {
      $validate = new BaseValidate([
          'openid'          =>'require',
      ]);
      $validate->goCheck();
      $params = $validate->getParameters();

      $openid  = $params['openid'];

      $list = Db::name('smallwechat_user_package')->where(array('openid'=>$openid,'is_collect'=>0))->select();
      $result['list'] = $list;
      return $this->returnHandler($result);
    }

    //获取运单商品
    public function getorderdata()
    {
      $validate = new BaseValidate([
          'openid'          =>'require',
          'ordersn'         =>'require',
      ]);
      $validate->goCheck();
      $params = $validate->getParameters();

      $openid  = $params['openid'];
      $ordersn = $params['ordersn'];

      $orderData = Db::name('smallwechat_user_package')->where(array('openid'=>$openid,'ordersn'=>$ordersn))->find();
      $orderData['goods'] = Db::name('smallwechat_user_package_goods')
      ->alias("pg")
      ->join('sz_yi_goods g', 'pg.goods_id = g.id')
      ->field('pg.*,g.title,g.thumb')
      ->where(array('pg.package_id'=>$orderData['id'],'pg.is_delete'=>0))
      ->select();

      foreach ($orderData['goods'] as $k => $v) {
        $orderData['goods'][$k]['thumb'] = "https://shop.gogo198.cn/addons/sz_yi/static/images/det_01.jpg";
      }

      $result['data'] = $orderData;
      return $this->returnHandler($result);
    }

    //扫码获取运单
    public function getorderformscan()
    {
      $validate = new BaseValidate([
          'openid'          =>'require',
          'ordersn'         =>'require',
      ]);
      $validate->goCheck();
      $params = $validate->getParameters();

      $openid  = $params['openid'];
      $ordersn = $params['ordersn'];

      $orderData = Db::name('smallwechat_user_package')->where(array('openid'=>$openid,'ordersn'=>$ordersn))->find();
      if($orderData)
      {
        $result['status'] = 1;
      }else {
        $result['status'] = 0;
      }
      return $this->returnHandler($result);
    }

    //添加商品到待打包列表
    public function addgoodsformwaitlist()
    {
      $validate = new BaseValidate([
          'openid'          =>'require',
          'ordersn'         =>'require',
          'package_id'      =>'require',
          'good_id'         =>'require',
          'package_good_id' =>'require',
      ]);
      $validate->goCheck();
      $params = $validate->getParameters();

      $data['openid'] = $params['openid'];
      $data['ordersn'] = $params['ordersn'];
      $data['package_id'] = $params['package_id'];
      $data['good_id'] = $params['good_id'];
      $data['package_good_id'] = $params['package_good_id'];
      $data['create_time'] = time();

      $delete_id = Db::name('smallwechat_user_package_goods_delete')->insertGetId($data);
      if($delete_id)
      {
        Db::name('smallwechat_user_package_goods')->update(array('id'=>$data['package_good_id'],'is_delete'=>1,'delete_id'=>$delete_id));
        $result['status'] = 1;
      }
      else {
        $result['status'] = 0;
      }

      return $this->returnHandler($result);
    }

    //分拣
    public function doselect()
    {
      $validate = new BaseValidate([
          'openid'          =>'require',
          'ordersn'         =>'require',
          'goods_ids'       =>'require',
          'add_goods_ids'   =>'isDefault',
          'delete_goods_ids' =>'isDefault',
          'package_id'      =>'require',
          'deletegoodslist' =>'isDefault',
          'wait_goods_ids'  =>'isDefault',
          'waitgoods'       =>'isDefault',
      ]);
      $validate->goCheck();
      $params = $validate->getParameters();

      $openid = $params['openid'];
      $ordersn = $params['ordersn'];
      $goods_ids = $params['goods_ids'];
      $add_goods_ids = $params['add_goods_ids'];
      $delete_goods_ids = $params['delete_goods_ids'];
      $package_id = $params['package_id'];
      $deletegoodslist = $params['deletegoodslist'];
      $wait_goods_ids = $params['wait_goods_ids'];
      $waitgoods = $params['waitgoods'];

      if($add_goods_ids=='' && $delete_goods_ids=='' && $wait_goods_ids=='')
      {
        $result['status'] = 2;
      }
      else {
        //保存新增
        if($add_goods_ids)
        {
          $addGoodss = rtrim($add_goods_ids,',');
          $addGoods = explode(",",$addGoodss);
          foreach ($addGoods as $k => $v) {
            $add = array();
            $add['openid'] = $openid;
            $add['ordersn'] = $ordersn;
            $add['create_time'] = time();
            $add['good_id'] = $v;
            $add['package_id'] = $package_id;
            $add_id = Db::name('smallwechat_user_package_goods_add')->insertGetId($add);

            $addData = array();
            $addData['package_id'] = $package_id;
            $addData['goods_id'] = $v;
            $addData['is_add'] = 1;
            $addData['add_id'] = $add_id;
            $addData['create_time'] = time();
            Db::name('smallwechat_user_package_goods')->insert($addData);
            SaveWechatUserLogs($openid,'运单:'.$ordersn.'新增商品,id为:'.$add_id);
          }
        }

        //保存删除
        if($delete_goods_ids)
        {
          foreach ($deletegoodslist as $kk => $vv) {
            $del = array();
            $del['openid'] = $openid;
            $del['ordersn'] = $ordersn;
            $del['create_time'] = time();
            $del['good_id'] = $vv['goods_id'];
            $del['package_id'] = $vv['package_id'];
            $del_id = Db::name('smallwechat_user_package_goods_delete')->insertGetId($del);

            $delData = array();
            $delData['id'] = $vv['id'];
            $delData['is_delete'] = 1;
            $delData['delete_id'] = $del_id;
            Db::name('smallwechat_user_package_goods')->update($delData);
            SaveWechatUserLogs($openid,'运单:'.$ordersn.'剔除商品,id为:'.$del_id);
          }
        }

        //待添打包商品列表
        if($wait_goods_ids)
        {
          $wait_goods_ids = rtrim($params['wait_goods_ids'], ',');
          $wait_goods = explode(",",$wait_goods_ids);
          $w_data = array();
          $w_data['package_id'] = $package_id;
          foreach ($wait_goods as $k => $v) {
            $w_data['goods_id'] = $v;
            Db::name('smallwechat_user_package_goods')->insert($w_data);
            if($waitgoods[$k]['good_id'] == $v)
            {
              Db::name('smallwechat_user_package_goods_delete')->update(array('id'=>$waitgoods[$k]['id'],'is_rest'=>1));
            }
          }
        }
        //更新运单
        $pack = array();
        $pack['id'] = $package_id;
        $pack['is_select'] = 1;
        $pack['goods_num'] = Db::name('smallwechat_user_package_goods')->where(array('package_id'=>$package_id,'is_delete'=>['NEQ',1]))->count();
        Db::name('smallwechat_user_package')->update($pack);

        $result['status'] = 1;
      }

      return $this->returnHandler($result);

    }













}
