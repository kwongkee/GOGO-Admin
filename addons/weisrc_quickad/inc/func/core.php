<?php

defined('IN_IA') or exit ('Access Denied');

class Core extends WeModuleSite
{
    //模块标识
    public $modulename = 'weisrc_quickad';
    public $cur_tpl = 'style1';

    public $member_code = '';
    public $feyin_key = '';
    public $device_no = '';

    public $msg_status_success = 1;
    public $msg_status_bad = 0;
    public $_debug = '1'; //default:0
    public $_weixin = '1'; //default:1

    public $_appid = '';
    public $_appsecret = '';
    public $_accountlevel = '';
    public $_account = '';

    public $_uniacid = '';
    public $_fromuser = '';
    public $_nickname = '';
    public $_headimgurl = '';

    public $_auth2_openid = '';
    public $_auth2_nickname = '';
    public $_auth2_headimgurl = '';
    public $_auth2_key = 'bHYzNjAubmV0LmNu';

    public $table_goods = 'weisrc_quickad_goods';
    public $table_nave = 'weisrc_quickad_nave';
    public $table_order = 'weisrc_quickad_order';
    public $table_setting = 'weisrc_quickad_setting';
    public $table_ad = 'weisrc_quickad_ad';
    public $table_article = "weisrc_quickad_article";
    public $table_fans = "weisrc_quickad_fans";

    public function getSetting()
    {
        global $_W, $_GPC;
        $uniacid = $this->_uniacid;
        $setting = pdo_fetch("SELECT * FROM " . tablename($this->table_setting) . " where uniacid = :uniacid LIMIT 1", array(':uniacid' => $uniacid));
        return $setting;
    }

    public function result($info, $url)
    {
        global $_W, $_GPC;
        $error_info = $info;
        $url = $url;
        include $this->template('result');
        exit;
    }

    /**
     * @param $upload_name
     * @param string $asname
     * @param bool $thumb
     * @param int $width
     * @param int $height
     * @param int $position
     * @return string
     */
    public function upload_img($upload_name, $asname = '', $thumb = true, $width = 320, $height = 240, $position = 5)
    {
        //文件操作类
        load()->func('file');
        $upfile = $_FILES[$upload_name];
        $name = $upfile['name'];
        $type = $upfile['type'];
        $size = $upfile['size'];
        $tmp_name = $upfile['tmp_name'];
        $error = $upfile['error'];
        //上传路径
        $upload_path = IA_ROOT . "/attachment/cy_rencai/";
        load()->func('file');
        @mkdirs($upload_path);

        if (intval($error) > 0) {
            message('上传错误：错误代码：' . $upload_name . '-' . $error, '', 'error');
        } else {
            //上传文件大小0为不限制，默认2M
            $maxfilesize = empty($this->module['config']['maxfilesize']) ? 2 : intval($this->module['config']['maxfilesize']);
            if ($maxfilesize > 0) {
                if ($size > $maxfilesize * 1024 * 1024) {
                    message('上传文件过大' . $_FILES["file"]["error"], '', 'error');
                }
            }

            //允许上传的图片类型
            $uptypes = array('image/jpg', 'image/png', 'image/jpeg');
            //判断文件的类型
            if (!in_array($type, $uptypes)) {
                message('上传文件类型不符：' . $type, '', 'error');
            }
            //存放目录
            if (!file_exists($upload_path)) {
                mkdir($upload_path);
            }
            //移动文件
            if (!move_uploaded_file($tmp_name, $upload_path . date("YmdHi") . '_' . $name)) {
                message('移动文件失败，请检查服务器权限', '', 'error');
            }

            $srcfile = $upload_path . date("YmdHi") . '_' . $name;
            $desfile = $upload_path . date("YmdHi") . '_' . $name . '.' . $asname . '.thumb.jpg';
            if ($thumb) {
                file_image_thumb($srcfile, $desfile, $width);
            } else {
                file_image_crop($srcfile, $desfile, $width, $height, 5);
            }
            return date("YmdHi") . '_' . $name . '.' . $asname . '.thumb.jpg';
        }
    }

    public function uploadImg($name)
    {
        if ($_FILES[$name]['error'] != 0) {
            $this->result("上传失败，请重试！1", $this->createMobileUrl('editinfo', array(), true));
        }
        $_W['uploadsetting'] = array();
        $_W['uploadsetting']['image']['folder'] = 'images';
        $_W['uploadsetting']['image']['extentions'] = $_W['config']['upload']['image']['extentions'];
        $_W['uploadsetting']['image']['limit'] = 1024;
        load()->func('file');
        $file = file_upload($_FILES[$name], 'image');
        if (is_error($file)) {
            $this->result("上传失败，请重试！", $this->createMobileUrl('editinfo', array(), true));
        }
        $result['url'] = $file['url'];
        $result['error'] = 0;
        $result['filename'] = $file['path'];
        $result['url'] = $_W['attachurl'] . $result['filename'];
        return $result['filename'];
    }

    public function isWeixin()
    {
        if ($this->_weixin == 1) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            if (!strpos($userAgent, 'MicroMessenger')) {
                include $this->template('s404');
                exit();
            }
        }
    }

    public function getOAuthHost()
    {
        global $_W;
        $host = $_W['siteroot'];
        $set = 'unisetting:' . $_W['uniacid'];
        if (!empty($_W['cache'][$set]['oauth']['host'])) {
            $host = $_W['cache'][$set]['oauth']['host'];
            return $host . '/';
        }
        return $host;
    }

    public function doMobileGetRemoteImg()
    {
        global $_W, $_GPC;
//        $url = $_GPC['url'];
//        header("content-type:image/png");
//        $opt=array('http'=>array('header'=>"Referer:'http://mp.weixin.qq.com'"));
//        $context=stream_context_create($opt);
//        $result = file_get_contents($url,false, $context);
        echo 'http://we7cloud-10016060.file.myqcloud.com/images/2016/07/07/1467821177577d2c798a2e8_G6gobhp1pTaJ.gif';
    }
}