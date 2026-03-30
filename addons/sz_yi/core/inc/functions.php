<?php
// 模块LTD提供
function getExpress($express, $expresssn)
{
    $url = sprintf(SZ_YI_EXPRESS_URL, $express, $expresssn, time());
    load()->func('communication');
    $resp = ihttp_request($url);
    $content = $resp['content'];

    if (empty($content)) {
        return array();
    }

    $content = json_decode($content);
    return $content->data;
}

function code62($x)
{
    $show = '';

    while (0 < $x) {
        $s = $x % 62;

        if (35 < $s) {
            $s = chr($s + 61);
        } else {
            if ((9 < $s) && ($s <= 35)) {
                $s = chr($s + 55);
            }
        }

        $show .= $s;
        $x = floor($x / 62);
    }

    return $show;
}

function shorturl($url)
{
    $url = crc32($url);
    $result = sprintf('%u', $url);
    return code62($result);
}

function sz_tpl_form_field_date($name, $value = '', $withtime = false)
{
    $s = '';

    if (!defined('TPL_INIT_DATA')) {
        $s = "\r\n\t\t\t<script type=\"text/javascript\">\r\n\t\t\t\trequire([\"datetimepicker\"], function(){\r\n\t\t\t\t\t\$(function(){\r\n\t\t\t\t\t\t\$(\".datetimepicker\").each(function(){\r\n\t\t\t\t\t\t\tvar option = {\r\n\t\t\t\t\t\t\t\tlang : \"zh\",\r\n\t\t\t\t\t\t\t\tstep : \"30\",\r\n\t\t\t\t\t\t\t\ttimepicker : ".(!empty($withtime) ? 'true' : 'false').",closeOnDateSelect : true,\r\n\t\t\tformat : \"Y-m-d".(!empty($withtime) ? ' H:i:s"' : '"')."};\r\n\t\t\t\$(this).datetimepicker(option);\r\n\t\t});\r\n\t});\r\n});\r\n</script>";
        define('TPL_INIT_DATA', true);
    }

    $withtime = (empty($withtime) ? false : true);

    if (!empty($value)) {
        $value = (strexists($value, '-') ? strtotime($value) : $value);
    } else {
        $value = TIMESTAMP;
    }

    $value = ($withtime ? date('Y-m-d H:i:s', $value) : date('Y-m-d', $value));
    $s .= '<input type="text" name="'.$name.'"  value="'.$value.'" placeholder="请选择日期时间" class="datetimepicker form-control" style="padding-left:12px;" />';
    return $s;
}

function isMobile()
{
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }

    if (isset($_SERVER['HTTP_VIA'])) {
        if (stristr($_SERVER['HTTP_VIA'], 'wap')) {
            return true;
        }
    }

    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array(
            'nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic',
            'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb',
            'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile',
            'WindowsWechat'
        );

        if (preg_match('/('.implode('|', $clientkeywords).')/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }

    if (isset($_SERVER['HTTP_ACCEPT'])) {
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && ((strpos($_SERVER['HTTP_ACCEPT'],
                        'text/html') === false) || (strpos($_SERVER['HTTP_ACCEPT'],
                        'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }

    return false;
}

function getDistance($lat1, $lng1, $lat2, $lng2)
{
    $earthRadius = 6367000;
    $lat1 = ($lat1 * pi()) / 180;
    $lng1 = ($lng1 * pi()) / 180;
    $lat2 = ($lat2 * pi()) / 180;
    $lng2 = ($lng2 * pi()) / 180;
    $calcLongitude = $lng2 - $lng1;
    $calcLatitude = $lat2 - $lat1;
    $stepOne = pow(sin($calcLatitude / 2), 2) + (cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2));
    $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
    $calculatedDistance = $earthRadius * $stepTwo;
    return round($calculatedDistance);
}

function chmod_dir($dir, $chmod = '')
{
    if (is_dir($dir)) {
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if (is_dir($dir.'/'.$file)) {
                    if (($file != '.') && ($file != '..')) {
                        $path = $dir.'/'.$file;
                        $chmod ? chmod($path, $chmod) : false;
                        chmod_dir($path);
                    }
                } else {
                    $path = $dir.'/'.$file;
                    $chmod ? chmod($path, $chmod) : false;
                }
            }
        }

        closedir($handle);
    }
}

function curl_download($url, $dir)
{
    $ch = curl_init($url);
    $fp = fopen($dir, 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $res = curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    return $res;
}

function send_sms($account, $pwd, $mobile, $code, $type = 'check', $name = '', $title = '', $total = '', $tel = '')
{
    if ($type == 'check') {
        $content = '您的验证码是：'.$code.'。请不要把验证码泄露给其他人。如非本人操作，可不用理会！';
    } else {
        if ($type == 'verify') {
            $verify_set = m('common')->getSetData();
            $allset = iunserializer($verify_set['plugins']);
            if (is_array($allset) && !empty($allset['verify']['code_template'])) {
                $content = sprintf($allset['verify']['code_template'], $code, $title, $total, $name, $mobile, $tel);
            } else {
                $content = '提醒您，您的核销码为：'.$code.'，订购的票型是：'.$title.'，数量：'.$total.'张，购票人：'.$name.'，电话：'.$mobile.'，门店电话：'.$tel.'。请妥善保管，验票使用！';
            }
        }
    }

    $smsrs = file_get_contents('http://106.ihuyi.cn/webservice/sms.php?method=Submit&account='.$account.'&password='.$pwd.'&mobile='.$mobile.'&content='.urldecode($content));
    return xml_to_array($smsrs);
}

function send_sms_alidayu($mobile, $code, $templateType)
{
    $set = m('common')->getSysset();
    include IA_ROOT.'/addons/sz_yi/alifish/TopSdk.php';

    switch ($templateType) {
        case 'reg':
            $templateCode = $set['sms']['templateCode'];
            $params = @explode("\n", $set['sms']['product']);
            break;

        case 'forget':
            $templateCode = $set['sms']['templateCodeForget'];
            $params = @explode("\n", $set['sms']['forget']);
            break;

        default:
            $templateCode = $templateType;
//            $templateCode = $set['sms']['templateCode'];
            break;
    }

    $c = new TopClient();
    $c->appkey = $set['sms']['appkey'];
    $c->secretKey = $set['sms']['secret'];
    $req = new AlibabaAliqinFcSmsNumSendRequest();
    $req->setExtend('123456');
    $req->setSmsType('normal');
    $req->setSmsFreeSignName($set['sms']['signname']);

    if (1 < count($params)) {
        $_obf_DT4rNC4ePhgBLww0DFsQJS4PFQUGCzI_['code'] = $code;

        foreach ($params as $param) {
            $param = trim($param);
            $_obf_DR00GzM2OTk_XDYzKiwcQBExJC4wLwE_ = explode('=', $param);
            $_obf_DT4rNC4ePhgBLww0DFsQJS4PFQUGCzI_[$_obf_DR00GzM2OTk_XDYzKiwcQBExJC4wLwE_[0]] = $_obf_DR00GzM2OTk_XDYzKiwcQBExJC4wLwE_[1];
        }

        $req->setSmsParam(json_encode($_obf_DT4rNC4ePhgBLww0DFsQJS4PFQUGCzI_));
    } else {
        $req->setSmsParam('{"code":"'.$code.'","product":"'.$set['sms']['product'].'"}');
    }

    $req->setRecNum($mobile);
    $req->setSmsTemplateCode($templateCode);
    $resp = $c->execute($req);
    return objectArray($resp);
}


function send_user_check_sms($mobile, $templateCode, $keyword)
{
    $set = m('common')->getSysset();
    include IA_ROOT.'/addons/sz_yi/alifish/TopSdk.php';
    $c = new TopClient();
    $c->appkey = $set['sms']['appkey'];
    $c->secretKey = $set['sms']['secret'];
    $req = new AlibabaAliqinFcSmsNumSendRequest();
    $req->setExtend('123456');
    $req->setSmsType('normal');
    $req->setSmsFreeSignName($set['sms']['signname']);
    $req->setSmsParam('{"keyword":"'.$keyword.'"}');
    $req->setRecNum($mobile);
    $req->setSmsTemplateCode($templateCode);
    $resp = $c->execute($req);
    return objectArray($resp);
}

function send_user_check_sms2($mobile, $templateCode, $keyword)
{
    $set = m('common')->getSysset();
    include IA_ROOT.'/addons/sz_yi/alifish/TopSdk.php';
    $c = new TopClient();
    $c->appkey = $set['sms']['appkey'];
    $c->secretKey = $set['sms']['secret'];
    $req = new AlibabaAliqinFcSmsNumSendRequest();
    $req->setExtend('123456');
    $req->setSmsType('normal');
    $req->setSmsFreeSignName($set['sms']['signname']);
    $req->setSmsParam($keyword);
    $req->setRecNum($mobile);
    $req->setSmsTemplateCode($templateCode);
    $resp = $c->execute($req);
    return objectArray($resp);
}

function xml_to_array($xml)
{
    $reg = '/<(\\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/';

    if (preg_match_all($reg, $xml, $matches)) {
        $count = count($matches[0]);
        $i = 0;

        while ($i < $count) {
            $subxml = $matches[2][$i];
            $key = $matches[1][$i];

            if (preg_match($reg, $subxml)) {
                $arr[$key] = xml_to_array($subxml);
            } else {
                $arr[$key] = $subxml;
            }

            ++$i;
        }
    }

    return $arr;
}

function redirect($url, $sec = 0)
{
    echo '<meta http-equiv=refresh content=\''.$sec.'; url='.$url.'\'>';
    exit();
}

function m($name = '')
{
    static $_modules = array();

    if (isset($_modules[$name])) {
        return $_modules[$name];
    }

    $model = SZ_YI_CORE.'model/'.strtolower($name).'.php';

    if (!is_file($model)) {
        exit(' Model '.$name.' Not Found!');
    }

    require $model;
    $class_name = 'Sz_DYi_'.ucfirst($name);
    $_modules[$name] = new $class_name();
    return $_modules[$name];
}

function isEnablePlugin($name)
{
    $plugins = m('cache')->getArray('plugins', 'global');

    if ($plugins) {
        foreach ($plugins as $p) {
            if ($p['identity'] == $name) {
                if ($p['status']) {
                    return true;
                }

                return false;
            }
        }

        return null;
    }

    return pdo_fetchcolumn('select count(*) from '.tablename('sz_yi_plugin').' where identity=:identity and status=1',
        array(':identity' => $name));
}

function p($name = '')
{
    if (!isenableplugin($name)) {
        return false;
    }

    if (($name != 'perm') && !IN_MOBILE) {
        static $_perm_model;

        if (!$_perm_model) {
            $perm_model_file = SZ_YI_PLUGIN.'perm/model.php';

            if (is_file($perm_model_file)) {
                require $perm_model_file;
                $perm_class_name = 'PermModel';
                $_perm_model = new $perm_class_name('perm');
            }
        }

        if ($_perm_model) {
            if (!$_perm_model->check_plugin($name)) {
                return false;
            }
        }
    }

    static $_plugins = array();

    if (isset($_plugins[$name])) {
        return $_plugins[$name];
    }

    $model = SZ_YI_PLUGIN.strtolower($name).'/model.php';

    if (!is_file($model)) {
        return false;
    }

    require $model;
    $class_name = ucfirst($name).'Model';
    $_plugins[$name] = new $class_name($name);
    return $_plugins[$name];
}

function byte_format($input, $dec = 0)
{
    $prefix_arr = array(' B', 'K', 'M', 'G', 'T');
    $value = round($input, $dec);
    $i = 0;

    while (1024 < $value) {
        $value /= 1024;
        ++$i;
    }

    $return_str = round($value, $dec).$prefix_arr[$i];
    return $return_str;
}

function save_media($url)
{
    load()->func('file');
    $config = array('qiniu' => false);
    $plugin = p('qiniu');

    if ($plugin) {
        $config = $plugin->getConfig();

        if ($config) {
            if (strexists($url, $config['url'])) {
                return $url;
            }

            $qiniu_url = $plugin->save(tomedia($url), $config);

            if (empty($qiniu_url)) {
                return $url;
            }

            return $qiniu_url;
        }

        return $url;
    }

    return $url;
}

function save_remote($url)
{
}

function is_array2($array)
{
    if (is_array($array)) {
        foreach ($array as $k => $v) {
            return is_array($v);
        }

        return false;
    }

    return false;
}

function set_medias($list = array(), $fields = null)
{
    if (empty($fields)) {
        foreach ($list as &$row) {
            $row = tomedia($row);
        }

        return $list;
    }

    if (!is_array($fields)) {
        $fields = explode(',', $fields);
    }

    if (is_array2($list)) {
        foreach ($list as $key => &$value) {
            foreach ($fields as $field) {
                if (isset($list[$field])) {
                    $list[$field] = tomedia($list[$field]);
                }

                if (is_array($value) && isset($value[$field])) {
                    $value[$field] = tomedia($value[$field]);
                }
            }
        }

        return $list;
    }

    foreach ($fields as $field) {
        if (isset($list[$field])) {
            $list[$field] = tomedia($list[$field]);
        }
    }

    return $list;
}

function get_last_day($year, $month)
{
    return date('t', strtotime($year.'-'.$month.' -1'));
}

function show_message($msg = '', $url = '', $type = 'success')
{
    $scripts = '<script language=\'javascript\'>require([\'core\'],function(core){ core.message(\''.$msg.'\',\''.$url.'\',\''.$type.'\')})</script>';
    exit($scripts);
}

function show_json($status = 1, $return = null)
{
    $ret = array('status' => $status);

    if ($return) {
        $ret['result'] = $return;
    }

    exit(json_encode($ret));
}

function is_weixin_show()
{
    $set = m('common')->getSysset('app');
    $isapp = is_app();
    if (($set['base']['wx']['switch'] == '1') && !$isapp) {
        return false;
    }

    return true;
}

function is_weixin()
{
    global $_W;
    if (($_W['uniaccount']['level'] == 1) || ($_W['uniaccount']['level'] == 3)) {
        return false;
    }

    if (empty($_SERVER['HTTP_USER_AGENT']) || ((strpos($_SERVER['HTTP_USER_AGENT'],
                    'MicroMessenger') === false) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Windows Phone') === false))) {
        return false;
    }

    return true;
}

function is_app_api()
{
    return defined('__MODULE_NAME__') && (__MODULE_NAME__ == 'app/api');
}

function b64_encode($obj)
{
    if (is_array($obj)) {
        return urlencode(base64_encode(json_encode($obj)));
    }

    return urlencode(base64_encode($obj));
}

function b64_decode($str, $is_array = true)
{
    $str = base64_decode(urldecode($str));

    if ($is_array) {
        return json_decode($str, true);
    }

    return $str;
}

function create_image($img)
{
    $ext = strtolower(substr($img, strrpos($img, '.')));

    if ($ext == '.png') {
        $thumb = imagecreatefrompng($img);
    } else {
        if ($ext == '.gif') {
            $thumb = imagecreatefromgif($img);
        } else {
            $thumb = imagecreatefromjpeg($img);
        }
    }

    return $thumb;
}

function get_authcode()
{
    $auth = get_auth();
    return empty($auth['code']) ? '' : $auth['code'];
}

function get_auth()
{
    global $_W;
    $set = pdo_fetch('select sets from '.tablename('sz_yi_sysset').' order by id asc limit 1');
    $sets = iunserializer($set['sets']);

    if (is_array($sets)) {
        return is_array($sets['auth']) ? $sets['auth'] : array();
    }

    return array();
}

function check_shop_auth($url = '', $type = 's')
{
    global $_W;
    global $_GPC;
    if ($_W['ispost'] && ($_GPC['do'] != 'auth')) {
        $auth = get_auth();
        load()->func('communication');
        $domain = $_SERVER['HTTP_HOST'];
        $ip = gethostbyname($domain);
        $setting = setting_load('site');
        $id = (isset($setting['site']['key']) ? $setting['site']['key'] : '0');
        if (empty($type) || ($type == 's')) {
            $post_data = array('type' => $type, 'ip' => $ip, 'id' => $id, 'code' => $auth['code'], 'domain' => $domain);
        } else {
            $post_data = array(
                'type' => 'm', 'm' => $type, 'ip' => $ip, 'id' => $id, 'code' => $auth['code'], 'domain' => $domain
            );
        }

        $resp = ihttp_post($url, $post_data);
        $status = $resp['content'];

        if ($status != '1') {
            message('联系客服', '', 'error');
        }
    }
}

function my_scandir($dir)
{
    global $my_scenfiles;

    if ($handle = opendir($dir)) {
        while (($file = readdir($handle)) !== false) {
            if (($file != '..') && ($file != '.') && ($file != '.git') && ($file != 'tmp') && ($file != 'data')) {
                if (is_dir($dir.'/'.$file)) {
                    my_scandir($dir.'/'.$file);
                } else {
                    $my_scenfiles[] = $dir.'/'.$file;
                }
            }
        }

        closedir($handle);
    }
}

function shop_template_compile($from, $to, $inmodule = false)
{
    $path = dirname($to);

    if (!is_dir($path)) {
        load()->func('file');
        mkdirs($path);
    }

    $content = shop_template_parse(file_get_contents($from), $inmodule);
    if ((IMS_FAMILY == 'x') && !preg_match('/(footer|header|account\\/welcome|login|register)+/', $from)) {
        $content = str_replace('微赞', '系统', $content);
    }

    file_put_contents($to, $content);
}

function shop_template_parse($str, $inmodule = false)
{
    $str = template_parse($str, $inmodule);
    $str = preg_replace('/{ifp\\s+(.+?)}/', '<?php if(cv($1)) { ?>', $str);
    $str = preg_replace('/{ifpp\\s+(.+?)}/', '<?php if(cp($1)) { ?>', $str);
    $str = preg_replace('/{ife\\s+(\\S+)\\s+(\\S+)}/', '<?php if( ce($1 ,$2) ) { ?>', $str);
    return $str;
}

function ce($permtype = '', $item = null)
{
    $perm = p('perm');

    if ($perm) {
        return $perm->check_edit($permtype, $item);
    }

    return true;
}

function cv($permtypes = '')
{
    $perm = p('perm');

    if ($perm) {
        return $perm->check_perm($permtypes);
    }

    return true;
}

function ca($permtypes = '')
{
    if (!cv($permtypes)) {
        message('您没有权限操作，请联系管理员1!', '', 'error');
    }
}

function cp($pluginname = '')
{
    $perm = p('perm');

    if ($perm) {
        return $perm->check_plugin($pluginname);
    }

    return true;
}

function cpa($pluginname = '')
{
    if (!cp($pluginname)) {
        message('您没有权限操作，请联系管理员2!', '', 'error');
    }
}

function plog($type = '', $op = '')
{
    $perm = p('perm');

    if ($perm) {
        $perm->log($type, $op);
    }
}

function objectArray($array)
{
    if (is_object($array)) {
        $array = (array) $array;
    }

    if (is_array($array)) {
        foreach ($array as $key => $value) {
            $array[$key] = objectArray($value);
        }
    }

    return $array;
}

function tpl_form_field_category_level3($name, $parents, $children, $parentid, $childid, $thirdid)
{
    $html = "\r\n<script type=\"text/javascript\">\r\n\twindow._".$name.' = '.json_encode($children)
        .";\r\n</script>";
    if (!defined('TPL_INIT_CATEGORY_THIRD')) {
        $html .= "\r\n<script type=\"
    text/javascript\">\r\n\tfunction renderCategoryThird(obj, name){\r\n\t\tvar
    index=obj.options[obj.selectedIndex].value;\r\n\t\trequire(['jquery', 'util' ], function(\$,
    u){\r\n\t\t\t\$selectChild=\$('#'+name+'_child');\r\n \$selectThird=\$('#'+name+'_third');\r\n\t\t\tvar
    html='<option value=\"0\">请选择二级分类</option>' ;\r\n var html1='<option value=\"0\">请选择三级分类</option>' ;\r\n\t\t\tif
    (!window['_'+name] || !window['_'+name][index]) {\r\n\t\t\t\t\$selectChild.html(html);\r\n
    \$selectThird.html(html1);\r\n\t\t\t\treturn false;\r\n\t\t\t}\r\n\t\t\tfor(var i=0; i<
    window['_'+name][index].length; i++){\r\n\t\t\t\thtml +='<option value=\"' +window['_'+name][index][i]['id']+'\">
    '+window['_'+name][index][i]['name']+'</option>';\r\n\t\t\t}\r\n\t\t\t\$selectChild.html(html);\r\n                                                    \$selectThird.html(html1);\r\n\t\t});\r\n\t}\r\n        function renderCategoryThird1(obj, name){\r\n\t\tvar index = obj.options[obj.selectedIndex].value;\r\n\t\trequire(['jquery', 'util'], function(\$, u){\r\n\t\t\t\$selectChild = \$('#'+name+'_third');\r\n\t\t\tvar html = '<option value=\"0\">请选择三级分类</option>';\r\n\t\t\tif (!window['_'+name] || !window['_'+name][index]) {\r\n\t\t\t\t\$selectChild.html(html);\r\n\t\t\t\treturn false;\r\n\t\t\t}\r\n\t\t\tfor(var i=0; i< window['_'+name][index].length; i++){\r\n\t\t\t\thtml += '<option value=\"'+window['_'+name][index][i]['id']+'\">'+window['_'+name][index][i]['name']+'</option>';\r\n\t\t\t}\r\n\t\t\t\$selectChild.html(html);\r\n\t\t});\r\n\t}\r\n
</script>\r\n\t\t\t";
        define('TPL_INIT_CATEGORY_THIRD', true);
    }

    $html .= "<div class=\"row row-fix tpl-category-container\">\r\n\t<div class=\"col-xs-12 col-sm-3 col-md-3 col-lg-3\">
        \r\n\t\t<select class=\"form-control tpl-category-parent\" id=\"".$name.'_parent" name="'.$name
        .'[parentid]" onchange="renderCategoryThird(this,\''.$name."' )\">\r\n\t\t\t<option value=\"0\">请选择一级分类
            </option>";
    $ops = '';

    foreach ($parents as $row) {
        $html .= "\r\n\t\t\t<option value=\"".$row['id'].'" '.($row['id'] == $parentid ? 'selected="selected"'
                : '').'>'.$row['name'].'</option>';
    }
    $html .= "\r\n\t\t</select>\r\n\t</div>\r\n\t<div class=\"
                col-xs-12 col-sm-3 col-md-3 col-lg-3\">\r\n\t\t<select class=\"form-control tpl-category-child\" id=\""
        .$name.'_child" name="'.$name
        .'[childid]" onchange="renderCategoryThird1(this,\''.$name."' )\">\r\n\t\t\t<option
                        value=\"0\">请选择二级分类</option>";
    if (!empty($parentid) && !empty($children[$parentid])) {
        foreach ($children[$parentid] as $row) {
            $html .= "\r\n\t\t\t<option value=\"".$row['id'].'"'.($row['id'] == $childid
                    ? 'selected="selected"' : '').'>'.$row['name'].'</option>';
        }
    }
    $html
        .= "\r\n\t\t</select>\r\n\t</div>\r\n                  <div class=\" col-xs-12 col-sm-3 col-md-3
                        col-lg-3\">\r\n\t\t<select class=\"form-control tpl-category-child\" id=\"".$name
        .'_third" name="'.$name."[thirdid]\">\r\n\t\t\t<option value=\"0\">请选择三级分类</option>";
    if (!empty($childid) && !empty($children[$childid])) {
        foreach ($children[$childid] as $row) {
            $html .= "\r\n\t\t\t<option value=\"".$row['id'].'"'.($row['id'] == $thirdid
                    ? 'selected="selected"' : '').'>'.$row['name'].'</option>';
        }
    }
    $html
        .= "</select>\r\n\t</div>\r\n</div>";
    return $html;
}

function
tpl_form_field_category_level2(
    $name,
    $parents,
    $children,
    $parentid,
    $childid
) {
    $html = "\r\n        <script type=\" text/javascript\">\r\n window._".$name.' = '.
        json_encode($children).";\r\n        </script>";
    if (!defined('TPL_INIT_CATEGORY')) {
        $html .= "\r\n        <script type=\" text/javascript\">\r\n function renderCategory(obj,
                                name){\r\n var index=obj.options[obj.selectedIndex].value;\r\n require(['jquery', 'util'
                                ], function(\$, u){\r\n \$selectChild=\$('#'+name+'_child');\r\n var
                                html='<option value=\"0\">请选择二级分类</option>' ;\r\n if (!window['_'+name] ||
                                !window['_'+name][index]) {\r\n \$selectChild.html(html);\r\n return false;\r\n }\r\n
                                for(var i=0; i< window['_'+name][index].length; i++){\r\n html +='<option value=\"'
                                +window['_'+name][index][i]['id']+'\">'+window['_'+name][index][i]['name']+'</option>
                            ';\r\n }\r\n \$selectChild.html(html);\r\n });\r\n }\r\n </script>\r\n ";
        define('TPL_INIT_CATEGORY', true);
    }

    $html .= "<div class=\"row row-fix tpl-category-container\">\r\n <div class=\"col-xs-12
                                    col-sm-6 col-md-6 col-lg-6\">\r\n <select class=\"form-control tpl-category-parent\"
                                        id=\"".$name.'_parent" name="'.$name
        .'[parentid]" onchange="renderCategory(this,\''.$name."' )\">\r\n <option
                                            value=\"0\">请选择一级分类</option>";
    $ops = '';

    foreach ($parents as $row) {
        $html .= "\r\n <option value=\"".$row['id'].'" '.($row['id'] == $parentid
                ? 'selected="selected"' : '').'>'.$row['name'].'</option>';
    }
    $html
        .= "\r\n                </select>\r\n            </div>\r\n            <div class=\"
                                            col-xs-12 col-sm-6 col-md-6 col-lg-6\">\r\n <select class=\"form-control
                                                tpl-category-child\" id=\"".$name.'_child" name="'.$name
        ."[childid]\">\r\n <option value=\"0\">请选择二级分类</option>";
    if (!empty($parentid) && !empty($children[$parentid])) {
        foreach ($children[$parentid] as $row) {
            $html .= "\r\n <option value=\"".$row['id'].'"'.
                ($row['id'] == $childid ? 'selected="selected"' : '').'>'.
                $row['name'].'</option>';
        }
    }
    $html
        .= "\r\n                </select>\r\n            </div>\r\n        </div>\r\n    ";
    return $html;
}

function sent_message(
    $customer_id_array,
    $message
) {
    preg_match_all('/[\\x{4e00}-\\x{9fff}]+/u', $message,
        $matches);
    if (empty($customer_id_array) || empty($matches[0])) {
        return false;
    }
    require IA_ROOT
        .'/addons/sz_yi/core/inc/plugin/vendor/leancloud/src/autoload.php';
    $setdata = m('cache')->get('sysset');
    $set = unserialize($setdata['sets']);
    $app = $set['app']['base'];
    \LeanCloud\LeanClient::initialize($app['leancloud']['id'],
        $app['leancloud']['key'], $app['leancloud']['master'].',master');
    $customer_id_array_str = json_encode($customer_id_array,
        JSON_UNESCAPED_UNICODE);
    $post_data = "{\"from_peer\": \"58\",\r\n \"to_peers\": ".
        $customer_id_array_str.",\r\n \"message\":
                                                    \"{\\\"_lctype\\\":-1,\\\"_lctext\\\":\\\"".$message."\\\",
                                                    \\\"_lcattrs\\\":{ \\\"clientId\\\":\\\"58\\\",
                                                    \\\"clientName\\\":\\\"商城助手\\\",
                                                    \\\"clientIcon\\\":\\\"http://192.168.1.108/image/icon.png\\\"
                                                    }}\"\r\n , \"conv_id\": \"5721da8b71cfe4006b3f362b\", \"transient\":
                                                    false}";
    $data = json_decode($post_data, true);
    $lean_push = new \LeanCloud\LeanMessage($data);
    $response = $lean_push->send();
    return $response;
}

function is_app()
{
    if (defined('__MODULE_NAME__') && (__MODULE_NAME__ == 'app/api')) {
        return true;
    }

    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $yunzhong = (strpos($agent, 'yunzhong') ? true : false);

    if ($yunzhong) {
        return true;
    }

    return false;
}

function json_encode_ex($value)
{
    if (version_compare(PHP_VERSION, '5.4.0', '<')) {
        $str = json_encode($value);
        $str = preg_replace_callback('#\\\\u([0-9a-f]{4})#i',
            function ($matchs) {
                return iconv('UCS-2BE', 'UTF-8', pack('H4',
                    $matchs[1]));
            }, $str);
        return $str;
    }
    return
        json_encode($value, JSON_UNESCAPED_UNICODE);
}

if
(!defined('IN_IA')) {
    exit('Access Denied');
}
if
(!defined('IS_API')) {
    load()->func('tpl');
}

$my_scenfiles = array();

if (!function_exists('tpl_form_field_category_3level')) {
    function tpl_form_field_category_3level(
        $name,
        $parents,
        $children,
        $parentid,
        $childid,
        $thirdid
    ) {
        return tpl_form_field_category_level3($name, $parents,
            $children, $parentid, $childid, $thirdid);
    }
}

if (function_exists('tpl_form_field_category_2level') == false) {
    function tpl_form_field_category_2level(
        $name,
        $parents,
        $children,
        $parentid,
        $childid,
        $thirdid
    ) {
        return tpl_form_field_category_level2($name, $parents,
            $children, $parentid, $childid, $thirdid);
    }
}

if (!function_exists('dump')) {
    function dump($var, $echo = true, $label = null, $strict = true)
    {
        if (!defined('IS_TEST')) {
            return null;
        }

        $label = ($label === null ? '' : rtrim($label).' ');

        if (!$strict) {
            if (ini_get('html_errors')) {
                $output = print_r($var, true);
                $output = '
                                                        <pre>'.$label.htmlspecialchars($output, ENT_QUOTES).'</pre>
                                                        ';
            } else {
                $output = $label.print_r($var, true);
            }
        } else {
            ob_start();
            var_dump($var);
            $output = ob_get_clean();

            if (!extension_loaded('xdebug')) {
                $output = preg_replace('/\\]\\=\\>\\n(\\s+)/m', '] => ',
                    $output);
                $output = '
                                                        <pre>'.$label.htmlspecialchars($output, ENT_QUOTES).'</pre>
                                                        ';
            }
        }

        if ($echo) {
            echo $output;
            return null;
        }

        return $output;
    }
}

if (!function_exists('is_test')) {
    function is_test()
    {
        return defined('IS_TEST');
    }
}

if (!function_exists('array_part')) {
    function array_part($key, $array)
    {
        if (is_string($key)) {
            $key = explode(',', $key);
        }

        if (!is_array($array)) {
            $array = array();
        }

        foreach ($key as $key_item) {
            if (isset($array[$key_item])) {
                $res_array[$key_item] = $array[$key_item];
            } else {
                $res_array[$key_item] = '';
            }
        }

        return $res_array;
    }
}

if (!function_exists('array_column')) {
    function array_column(
        $input = null,
        $columnKey = null,
        $indexKey = null
    ) {
        $argc = func_num_args();
        $params = func_get_args();

        if ($argc < 2) {
            trigger_error('array_column() expects at least
                                                            2 parameters, '.$argc.' given', 512);
            return null;
        }
        if
        (!is_array($params[0])) {
            trigger_error('array_column()
                                                            expects parameter 1 to be array, '.gettype($params[0]).'
                                                            given', 512);
            return null;
        }
        if (!is_int($params[1]) &&
            !is_float($params[1]) && !is_string($params[1]) &&
            ($params[1] !== null) && !(is_object($params[1]) &&
                method_exists($params[1], '__toString'))) {
            trigger_error('array_column(): The column key should be
                                                            either a string or an integer', 512);
            return false;
        }
        if
        (isset($params[2]) && !is_int($params[2]) &&
            !is_float($params[2]) && !is_string($params[2]) &&
            !(is_object($params[2]) &&
                method_exists($params[2], '__toString'))) {
            trigger_error('array_column(): The index key should be
                                                            either a string or an integer', 512);
            return false;
        }
        $paramsInput = $params[0];
        $paramsColumnKey = ($params[1]
        !== null ? (string) $params[1] : null);
        $paramsIndexKey = null;
        if (isset($params[2])) {
            if (is_float($params[2]) ||
                is_int($params[2])) {
                $paramsIndexKey = (int) $params[2];
            } else {
                $paramsIndexKey = (string) $params[2];
            }
        }
        $resultArray = array();
        foreach ($paramsInput as $row) {
            $key = $value = null;
            $keySet = $valueSet = false;
            if
            (($paramsIndexKey !== null) &&
                array_key_exists($paramsIndexKey, $row)) {
                $keySet = true;
                $key = (string) $row[$paramsIndexKey];
            }
            if
            ($paramsColumnKey === null) {
                $valueSet = true;
                $value = $row;
            } else {
                if (is_array($row) &&
                    array_key_exists($paramsColumnKey, $row)) {
                    $valueSet = true;
                    $value = $row[$paramsColumnKey];
                }
            }
            if ($valueSet) {
                if
                ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }
        }
        return $resultArray;
    }
}
if
(!function_exists('pdo_sql_debug')) {
    function
    pdo_sql_debug(
        $sql,
        $placeholders
    ) {
        foreach ($placeholders
                 as $k => $v) {
            $sql = preg_replace('/'.$k.'/', '\''.$v.'\'', $sql);
        }

        dump($sql);
        return $sql;
    }
}

//xj 新增
if (!function_exists('filterNickname')) {
    //peng 添加一个微信名过滤方法
    function filterNickname($name = "")
    {

        if (empty($name)) {
            $name = '昵称为空';
        }
        $name = json_encode($name); //暴露出unicode 处理微信图片名字

        // $name = preg_replace('#(\\\ue[0-9a-f]{3})#ie',"-",$name);

        $name = json_decode($name);

        $pattern =
            '/([\x{4e00}-\x{9fa5}]|[0-9a-zA-Z]|[~!@#$%^&*()_+。.-])+/u';

        preg_match_all($pattern, $name, $match);

        $str = implode('', $match[0]);

        if (empty($str)) {

            $str = "名字全是非法字符无法显示";
        }

        return $str;
    }
}
//han 20170911 新版阿里云短信
function send_sms_aliyun($mobile, $code, $templateType)
{
    $set = m('common')->getSysset();
    $smsset = $set['sms'];
    switch ($templateType) {
        case 'reg':
            $templateCode = $set['sms']['templateCode'];
            $params = @explode("\n", $set['sms']['product']);
            break;

        case 'forget':
            $templateCode = $set['sms']['templateCodeForget'];
            $params = @explode("\n", $set['sms']['forget']);
            break;

        default:
//            $templateCode = $templateType;
            $templateCode = $set['sms']['templateCode'];
            break;
    }
    if (1 < count($params)) {
        $data['code'] = "$code";
        foreach
        ($params as $param) {
            $param = trim($param);
            $key = explode('=', $param);
            $data[$key[0]] = $key[1];
        }
        $data = json_encode($data);
    } else {
        $data = array(' code' => "$code");
        $data = json_encode($data);
    }
    load()->func('communication');
    $paramstr = http_build_query(array(
        'ParamString' =>
            $data, 'RecNum' => $mobile, 'SignName' =>
            $smsset['signname'], 'TemplateCode' => $templateCode
    ));
    $header = array(
        'Authorization' => 'APPCODE '.
            $smsset['aliyun_appcode']
    );
    $request =
        ihttp_request('http://sms.market.alicloudapi.com/singleSendSms?'
            .$paramstr, '', $header);
    $result = json_decode($request['content'], true);
    if (!$result['success'] || ($request['code'] != 200)) {
        if ($request['code'] != 200) {
            $result['message'] =
                $request['headers']['X-Ca-Error-Message'];
        }

        return array(
            'status' => 0, 'msg' => '短信发送失败(错误信息: '.
                $result['message'].')', 'sub_msg' => ''
        );
    }
}

//验证身份证
function verifIDCard($data)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "http://shop.gogo198.cn/foll/public/?s=admin/Vertify/DoAli",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => array(
            "Cache-Control: no-cache",
            "Content-Type: application/json",
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}


/**
 *
 * 2019-05-17 发送注册审核结果
 * @param $mobile
 * @param $result
 * @return array
 */
function send_reg_check_alidayu($mobile, $result)
{
    // $set = m('common')->getSysset();
    include IA_ROOT.'/addons/sz_yi/alifish/TopSdk.php';
    $c = new TopClient();
    $c->appkey = '23583756';
    $c->secretKey = '0ba6116a41c1b994994e5504543010fb';
    $req = new AlibabaAliqinFcSmsNumSendRequest();
    $req->setExtend('123456');
    $req->setSmsType('normal');
    $req->setSmsFreeSignName('Gogo购购网');
    $req->setSmsParam(json_encode(array(
        'submittime' => date("Y-m-d
                                                                H:i", time()), 'status' => $result
    )));
    $req->setRecNum($mobile);
    $req->setSmsTemplateCode("SMS_165412505");
    $resp = $c->execute($req);
    return objectArray($resp);
}

/**
 * 验证手机实名认证
 * @param $url
 * @return mixed
 */
function verif_phone_realname($xingming, $mobile)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://creditphone2.rmpworld.com/credit/phone2?name='.$xingming.'&phone='.$mobile,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => array("Authorization: APPCODE 504fd5f6a735437c97cd117e61cb4a24",),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response, true);
}

function getRealIp()
{
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            foreach ($arr as $ip) {
                $ip = trim($ip);

                if ($ip != 'unknown') {
                    $realip = $ip;
                    break;
                }
            }
        } else {
            if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                if (isset($_SERVER['REMOTE_ADDR'])) {
                    $realip = $_SERVER['REMOTE_ADDR'];
                } else {
                    $realip = '0.0.0.0';
                }
            }
        }
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        } else {
            if (getenv('HTTP_CLIENT_IP')) {
                $realip = getenv('HTTP_CLIENT_IP');
            } else {
                $realip = getenv('REMOTE_ADDR');
            }
        }
    }

    preg_match('/[\\d\\.]{7,15}/', $realip, $onlineip);
    $realip = (!empty($onlineip[0]) ? $onlineip[0] :
        '0.0.0.0');
    return $realip;
}

function generateOrderSn() {
    $id = hexdec(uniqid());
    if ($id % 2 == 0) {
        $id = $id + 1;
    }
    return $id . mt_rand(1111, 9999);
}

function dd($data){
    print_r($data);
    die;
}

#通知仓库打印机
function notify_terminal($data){
    $post = ['order_id'=>$data['order_id'],'warehouse_express_id'=>$data['express_id'],'company_id'=>$data['company_id']];

    //测试
//    $res = send_package_list(['order_id'=>$data['order_id'],'warehouse_id'=>$data['warehouse_id']]);
//    dd($res);
//    echo '已发送';die;

    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,'https://shop.gogo198.cn/collect_website/public/?s=/api/func/print_order');
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
    curl_setopt($ch,CURLOPT_POST,1);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
    curl_setopt($ch, CURLOPT_HTTPHEADER,[]);
    $res=curl_exec($ch);
    curl_close($ch);

    $res = json_decode($res,true);

    if($res['code']==200){
        #打印面单成功
        $we = pdo_fetch('select * from '.tablename('centralize_warehouse_express').' where id='.$data['express_id']);
        $express_info = pdo_fetch('select * from '.tablename('centralize_express_product').' where id='.$we['express_id']);
        $res['data']['kuaidi_company_id'] = $express_info['id'];
        pdo_update('website_order_list',['status'=>1,'is_printer'=>1,'express_info'=>json_encode($res['data'],true)],['id'=>$data['order_id']]);

        #判断商品是“单品”还是“组合商品”要发送《拟打包清单》，还要判断是否“代发货仓库”还是“直发仓库”，“代发仓库”须在卖家后台操作这个发送清单功能
        send_package_list(['order_id'=>$data['order_id'],'warehouse_id'=>$data['warehouse_id']]);
    }else{
        echo json_encode(['code'=>-1,'msg'=>$res['message']]);exit;
    }
}

#面单打印成功时，“自发货的仓库”系统自动发送《拟打包清单》，“代发货的仓库”需要在后台手动发送
function send_package_list($data){

    $warehouse_info = pdo_fetch('select * from '.tablename('centralize_warehouse_list').' where id=:wid',[':wid'=>$data['warehouse_id']]);

    if($warehouse_info['warehouse_form']==1){
        #直发仓库直接下发消息
        $post = ['order_id'=>$data['order_id'],'warehouse_id'=>$data['warehouse_id']];

        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,'https://shop.gogo198.cn/collect_website/public/?s=/api/func/send_package_to_email');
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
        curl_setopt($ch, CURLOPT_HTTPHEADER,[]);
        $res=curl_exec($ch);
        curl_close($ch);

//        $res = json_decode($res,true);
        return $res;
    }
}

function is_idcard($id)
{
    $id        = strtoupper($id);
    $regx      = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
    $arr_split = array();
    if (!preg_match($regx, $id)) {
        return false;
    }
    if (15 == strlen($id)) //检查15位
    {
        $regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";

        @preg_match($regx, $id, $arr_split);
        //检查生日日期是否正确
        $dtm_birth = "19" . $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
        if (!strtotime($dtm_birth)) {
            return false;
        } else {
            return true;
        }
    } else //检查18位
    {
        $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
        @preg_match($regx, $id, $arr_split);
        $dtm_birth = $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
        if (!strtotime($dtm_birth)) //检查生日日期是否正确
        {
            return false;
        } else {
            //检验18位身份证的校验码是否正确。
            //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
            $arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
            $arr_ch  = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
            $sign    = 0;
            for ($i = 0; $i < 17; $i++) {
                $b = (int) $id{$i};
                $w = $arr_int[$i];
                $sign += $b * $w;
            }
            $n       = $sign % 11;
            $val_num = $arr_ch[$n];
            if ($val_num != substr($id, 17, 1)) {
                return false;
            } //phpfensi.com
            else {
                return true;
            }
        }
    }
}

//公用请求方法
function httpRequest2($url,$data,$head=[])
{
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
    curl_setopt($ch,CURLOPT_POST,1);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
    curl_setopt($ch, CURLOPT_HTTPHEADER,$head);
    $output=curl_exec($ch);
    curl_close($ch);
    return $output;
}

function checkEmail($email){
    $pregEmail = "/([a-z0-9]*[-_\\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\\.][a-z]{2,3}([\\.][a-z]{2})?/i";
    return preg_match($pregEmail, $email);
}

/**
 * 集运仓储系统
 * 2022-07-25
 */
//检测有无登录
function check_login(){
    global $_W;
    $manager = $_SESSION['warehouse_manager'];
    if(empty($manager)){
        header('Location:./index.php?i='.$_W['uniacid'].'&c=entry&do=warehouse&m=sz_yi&p=login&op=display');
        return false;
    }
    return true;
}

function Mobile($mobile)
{
    if(!is_numeric($mobile)) {
        return false;
    }

    return preg_match('/^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$/',$mobile) ? true : false;
}

function insert_transport_detail($data){
    pdo_insert('centralize_transport_detail',$data);
}

