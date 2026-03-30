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
 * Class WechatAuth
 * @package app\api_v3\controller
 */
class WechatAuth
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


    // 微信用户登录
    public function mp_auth()
    {
        $validate = new BaseValidate([
            'code'          =>'require|length:32',
            'referrer_tid'  =>'isPositiveInteger',
            'encryptedData' =>'require',
            'iv'            =>'require',
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();

        $APPID = $this->appid;
        $AppSecret = $this->appsecret;
        $code  = $params['code'];

        $access_token_url = "https://api.weixin.qq.com/sns/jscode2session?appid=".$APPID."&secret=".$AppSecret."&js_code=".$code."&grant_type=authorization_code";
        $access_token_result = CurlHandler::curl($access_token_url);
        $access_token_result = $access_token_result['data'];
        $access_token_result = json_decode($access_token_result, true);

        $wechat_xcx_open_id = $access_token_result['openid'];
        $wechat_xcx_session_key = $access_token_result['session_key'];

        require_once EXTEND_PATH.'wxBizDataCrypt/wxBizDataCrypt.php';
        $pc = new \WXBizDataCrypt($APPID, $wechat_xcx_session_key);
        $errorCode = $pc->decryptData($params['encryptedData'], $params['iv'], $data );
        if ($errorCode == 0) {
            $data = json_decode($data, true);
        } else {
            throw new ParameterException(['msg'=>'绑定失败('.$errorCode.')']);
        }

        $memberData = array();
        $memberData['openid']      = $wechat_xcx_open_id;
        $memberData['session_key'] = $wechat_xcx_session_key;
        $memberData['user_type']   = '小程序';
        $memberData['nickname']    = $data['nickName'];
        $memberData['gender']      = $data['gender'];
        $memberData['language']    = $data['language'];
        $memberData['city']        = $data['city'];
        $memberData['province']    = $data['province'];
        $memberData['country']     = $data['country'];
        $memberData['avatarurl']   = $data['avatarUrl'];
        $memberData['referrer_id'] = 0;
        if(isset($access_token_result['unionid']))
        {
          $memberData['unionid'] = $access_token_result['unionid'];
        }

        //if (isset($params['referrer_tid'])) $data['referrer_id'] = $params['referrer_tid'];

        $member = Db::name('smallwechat_user')->where(array('openid'=>$wechat_xcx_open_id))->find();
        if (empty($member))
        {
            //未注册
            // 创建会员
            $result = array();
            $memberData['phonenumber'] = '';
            $memberData['create_time'] = time();
            $member = Db::name('smallwechat_user')->insert($memberData);
            $member = Db::name('smallwechat_user')->where(array('openid'=>$wechat_xcx_open_id))->find();
            $result['need_update_user_info'] = true;
            $result['member'] = $member;
            $result['token'] = TokenService::generateToken();// 构建token
            TokenService::saveTokenToCache($member['id'], $result['token']);
            return $this->returnHandler($result);
        }
        else
        {
            //已注册
            $memberData['id'] = $member['id'];
            // 更新用户登录信息
            // 构建token
            $result = array();
            $member = Db::name('smallwechat_user')->update($memberData);
            $member = Db::name('smallwechat_user')->where(array('id'=>$memberData['id']))->find();
            if($member['phonenumber']=='' || $member['decl_user_id']=='')
            {
                $result['need_update_user_info'] = true;
            }

            $result['member'] = $member;
            $result['token'] = TokenService::generateToken();
            TokenService::saveTokenToCache($member['id'], $result['token']);

            // 返回数据
            return $this->returnHandler($result);
        }
    }

    //获取会员资料
    public function getuserdata()
    {
        $validate = new BaseValidate([
            'openid'        => 'require',
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();

        $openid = $params['openid'];

        $userData = Db::name('smallwechat_user')->where(array('openid'=>$openid))->find();
        $userData['menu'] = $this->getUserMenu($userData['openid']);
        $result['member'] = $userData;
        $result['token'] = TokenService::generateToken();
        TokenService::saveTokenToCache($userData['id'], $result['token']);
        return $this->returnHandler($result);

    }

    public function getUserMenu($openid)
    {
        $userData = Db::name('smallwechat_user')->where(array('openid'=>$openid))->find();

        if(!$userData['auth'])
        {
            $auth = '6,9';
        }else{
            $auth = $userData['auth'];
        }

        $menu = Db::name('smallwechat_user_auth')->where(array('id'=>['in',$auth]))->select();

        return $menu;

    }

    //绑定手机号码
    public function bindmobilephone()
    {
        $validate = new BaseValidate([
            'member_tid'        => 'require|isPositiveInteger',
            'encryptedData'     => 'require',
            'iv'                => 'require'
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();

        $APPID = $this->appid;
        $member = Db::name('smallwechat_user')->where(array('id'=>$params['member_tid']))->find();

        require_once EXTEND_PATH.'wxBizDataCrypt/wxBizDataCrypt.php';
        $pc = new \WXBizDataCrypt($APPID, $member['session_key']);
        $errorCode = $pc->decryptData($params['encryptedData'], $params['iv'], $data );

        if ($errorCode == 0) {
            $data = json_decode($data, true);
            //$member['referrer_id'] = $member['id'];
            $member['countrycode'] = $data['countryCode'];
            $member['phonenumber'] = $data['phoneNumber'];

            $decl_user_id = Db::name('decl_user')->where(array('user_tel'=>$data['phoneNumber']))->value('id');
            if($decl_user_id)
            {
              $member['decl_user_id'] = $decl_user_id;
              $member['usertype'] = '跨境用戶'; //跨境
              Db::name('decl_user')->update(array('id'=>$decl_user_id,'smallwechat_openid'=>$member['openid']));
            }
            Db::name('smallwechat_user')->update($member);

            return $this->returnHandler($member, false);
        } else {
            throw new ParameterException(['msg'=>'绑定失败('.$errorCode.')']);
        }
    }

    //修改会员资料
    public function updatedata()
    {
      $validate = new BaseValidate([
          'member_tid'    => 'require|isPositiveInteger',
          'realname'      => 'require',
      ]);
      $validate->goCheck();
      $params = $validate->getParameters();

      $data['id'] = $params['member_tid'];
      $data['realname'] = $params['realname'];

      if(Db::name('smallwechat_user')->update($data))
      {
        $member = Db::name('smallwechat_user')->where(array('id'=>$params['member_tid']))->find();
        $member['decl_user_id'] = Db::name('smallwechat_user')
        ->alias("su")
        ->join('decl_user du', 'su.openid = du.smallwechat_openid')
        ->value('du.id');
        return $this->returnHandler($member, false);
      }
      else {
        throw new ParameterException(['msg'=>'修改资料失败']);
      }

    }

    //生成小程序推广码
    public function userqrcode()
    {
        $validate = new BaseValidate([
            'member_tid'    => 'require|isPositiveInteger',
            'scene'         => 'require',
            'page'          => 'require',
            'tui_member_id' => 'isPositiveInteger',
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();

        $member_id = $params['member_tid'];
        $member_qr = Db::name('smallwechat_user')->where(array('id'=>$member_id))->value('qrcodeurl');

        if(!$member_qr)
        {
            $APPID = $this->appid;
            $AppSecret = $this->appsecret;
            // 1、获取ACCESS_TOKEN
            $access_token_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$APPID.'&secret='.$AppSecret;
            $access_token_result = CurlHandler::curl($access_token_url);
            $access_token_result = $access_token_result['data'];
            $access_token_result = json_decode($access_token_result, true);
            if (isset($access_token_result['errcode']))
            {
                throw new UserQRCodeException(['msg'=>'获取微信ACCESS_TOKEN失败']);
            }
            $access_token = $access_token_result['access_token'];

            // 2、获取二维码
            //header('content-type:image/png');
            $qrcode_url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$access_token;
            $qrcode_params = array();
            $qrcode_params['scene'] = $params['scene'];
            $qrcode_params['page'] = $params['page'];

            $qrcode_result = CurlHandler::curl($qrcode_url, $qrcode_params ,'POST');

            $filePath = $params['page'].$params['scene'].time();
            $filePath = md5($filePath);
            $filePath = $member_id.$filePath.'.png';

            $qrcode_path = './uploads/xcx/'.$filePath;
            file_put_contents($qrcode_path, $qrcode_result['data']);


            if(is_file($qrcode_path) != false)
            {
                //比例缩小小程序码
                $thumb_image_path = ROOT_PATH.'/public/uploads/xcx/'.$filePath;
                $thumb_image = \think\Image::open($thumb_image_path);
                $thumb_image->thumb(140, 140)->save($thumb_image_path);
                //合成海报
                $size = array(95,790);
                $default_code_path = ROOT_PATH.'/public/uploads/xcx/default_code.png';
                $default_image = \think\Image::open($default_code_path);
                $default_image->water($qrcode_path,$size)->save($qrcode_path);
            }

            $qrcode_url = 'https://shop.gogo198.cn/foll/public/uploads/xcx/'.$filePath;
            //更新数据库
            if($params['tui_member_id'])
            {
                //绑定推广关系
                $tui_member = Db::name('smallwechat_user')->where(array('id'=>$params['tui_member_id']))->find();
                $member['referrer_id'] = $tui_member['id'];
            }
            $member['id'] = $member_id;
            $member['qrcodeurl'] = $qrcode_url;
            Db::name('smallwechat_user')->update($member);
        }
        else
        {
            $qrcode_url = $member_qr;
        }

        return $this->returnHandler(array('qrcode_url'=>$qrcode_url), false);
    }

    //请求
    public function vget($url){
        $info=curl_init();
        curl_setopt($info,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($info,CURLOPT_HEADER,0);
        curl_setopt($info,CURLOPT_NOBODY,0);
        curl_setopt($info,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($info,CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($info,CURLOPT_URL,$url);
        $output= curl_exec($info);
        curl_close($info);
        return $output;
    }

    //微信解密
    public function wxBizDataCrypt($Appid,$SessionKey,$EncryptedData,$Iv)
    {
        //var_dump(EXTEND_PATH.'wxBizDataCrypt/wxBizDataCrypt.php');
        require_once EXTEND_PATH.'wxBizDataCrypt/wxBizDataCrypt.php';
        $pc = new \WXBizDataCrypt($Appid, $SessionKey);
        $errCode = $pc->decryptData($EncryptedData, $Iv, $data );

        if ($errCode == 0)
        {
            $result = json_decode($data,true);
        }
        else
        {
            $result = $errCode;
        }

        return $result;
    }

    //获取banner等
    public function getindex()
    {
        $imgUrls = array('http://shop.gogo198.cn/foll/public/uploads/xcx/index/banner1.jpg');
        return json(['status' => 200, 'msg' => 'ok', 'data' => array('imgUrls' => $imgUrls)]);
    }










}
