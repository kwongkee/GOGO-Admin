<?php

if (!(defined('IN_IA'))) {exit('Access Denied');}

if (!(function_exists('m')))
{
    function m($name = '')
    {
        static $_modules = array();
        if (isset($_modules[$name]))
        {
            return $_modules[$name];
        }
        $model = EWEI_SHOPV2_CORE . 'model/' . strtolower($name) . '.php';
        if (!(is_file($model)))
        {
            exit(' Model ' . $name . ' Not Found!');
        }
        require_once $model;
        $class_name = ucfirst($name) . '_EweiShopV2Model';
        $_modules[$name] = new $class_name();
        return $_modules[$name];
    }
}


if (!(function_exists('p')))
{
    function p($name = '')
    {
        static $_plugins = array();
        if (isset($_plugins[$name]))
        {
            return $_plugins[$name];
        }
        $model = EWEI_SHOPV2_PLUGIN . strtolower($name) . '/core/model.php';
        if (!(is_file($model)))
        {
            return false;
        }
        require_once EWEI_SHOPV2_CORE . 'inc/plugin_model.php';
        require_once $model;
        $class_name = ucfirst($name) . 'Model';
        $_plugins[$name] = new $class_name($name);
        if (com_run('perm::check_plugin', $name) || ($name == 'grant') || ($name == 'qpay'))
        {
            if ($name == 'seckill')
            {
                if (!(function_exists('redis')) || is_error(redis()))
                {
                    return false;
                }
            }
            return $_plugins[$name];
        }
        return false;
    }
}


if (!(function_exists('com')))
{
    function com($name = '')
    {
        static $_coms = array();
        if (isset($_coms[$name]))
        {
            return $_coms[$name];
        }
        $model = EWEI_SHOPV2_CORE . 'com/' . strtolower($name) . '.php';
        if (!(is_file($model)))
        {
            return false;
        }
        require_once EWEI_SHOPV2_CORE . 'inc/com_model.php';
        require_once $model;
        $class_name = ucfirst($name) . '_EweiShopV2ComModel';
        $_coms[$name] = new $class_name($name);
        if ($name == 'perm')
        {
            return $_coms[$name];
        }
        if (com('perm')->check_com($name))
        {
            return $_coms[$name];
        }
        return false;
    }
}



if (!(function_exists('byte_format')))
{
    function byte_format($input, $dec = 0)
    {
        $prefix_arr = array(' B', 'K', 'M', 'G', 'T');
        $value = round($input, $dec);
        $i = 0;
        while (1024 < $value)
        {
            $value /= 1024;
            ++$i;
        }
        $return_str = round($value, $dec) . $prefix_arr[$i];
        return $return_str;
    }
}


if (!(function_exists('is_array2')))
{
    function is_array2($array)
    {
        if (is_array($array))
        {
            foreach ($array as $k => $v )
            {
                return is_array($v);
            }
            return false;
        }
        return false;
    }
}



if (!(function_exists('get_last_day')))
{
    function get_last_day($year, $month)
    {
        return date('t', strtotime($year . '-' . $month . ' -1'));
    }
}


if (!(function_exists('show_message')))
{
    function show_message($msg = '', $url = '', $type = '')
    {
        $site = new Page();
        $site->message($msg, $url, $type);
        exit();
    }
}


if (!(function_exists('show_json')))
{
    function show_json($status = 1, $return = NULL)
    {
        $ret = array('status' => $status, 'result' => ($status == 1 ? array('url' => referer()) : array()));
        if (!(is_array($return)))
        {
            if ($return)
            {
                $ret['result']['message'] = $return;
            }
            exit(json_encode($ret));
        }
        else
        {
            $ret['result'] = $return;
        }
        if (isset($return['url']))
        {
            $ret['result']['url'] = $return['url'];
        }
        else if ($status == 1)
        {
            $ret['result']['url'] = referer();
        }
        exit(json_encode($ret));
    }
}


if (!(function_exists('is_weixin')))
{
    function is_weixin()
    {
        global $_W;
        if ($_W['shopset']['wap']['inwap'])
        {
            return false;
        }
        if (empty($_SERVER['HTTP_USER_AGENT']) || ((strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Windows Phone') === false)))
        {
            return false;
        }
        return true;
    }
}


if (!(function_exists('is_h5app')))
{
    function is_h5app()
    {
        if (!(empty($_SERVER['HTTP_USER_AGENT'])) && strpos($_SERVER['HTTP_USER_AGENT'], 'CK 2.0'))
        {
            return true;
        }
        return false;
    }
}


if (!(function_exists('is_ios')))
{
    function is_ios()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad'))
        {
            return true;
        }
        return false;
    }
}


if (!(function_exists('is_mobile')))
{
    function is_mobile()
    {
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/(android|bb\\d+|meego).+mobile|avantgo|bada\\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\\-(n|u)|c55\\/|capi|ccwa|cdm\\-|cell|chtm|cldc|cmd\\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\\-s|devi|dica|dmob|do(c|p)o|ds(12|\\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\\-|_)|g1 u|g560|gene|gf\\-5|g\\-mo|go(\\.w|od)|gr(ad|un)|haie|hcit|hd\\-(m|p|t)|hei\\-|hi(pt|ta)|hp( i|ip)|hs\\-c|ht(c(\\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\\-(20|go|ma)|i230|iac( |\\-|\\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\\/)|klon|kpt |kwc\\-|kyo(c|k)|le(no|xi)|lg( g|\\/(k|l|u)|50|54|\\-[a-w])|libw|lynx|m1\\-w|m3ga|m50\\/|ma(te|ui|xo)|mc(01|21|ca)|m\\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\\-2|po(ck|rt|se)|prox|psio|pt\\-g|qa\\-a|qc(07|12|21|32|60|\\-[2-7]|i\\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\\-|oo|p\\-)|sdk\\/|se(c(\\-|0|1)|47|mc|nd|ri)|sgh\\-|shar|sie(\\-|m)|sk\\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\\-|v\\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\\-|tdg\\-|tel(i|m)|tim\\-|t\\-mo|to(pl|sh)|ts(70|m\\-|m3|m5)|tx\\-9|up(\\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\\-|your|zeto|zte\\-/i', substr($useragent, 0, 4)))
        {
            return true;
        }
        return false;
    }
}


if (!(function_exists('b64_encode')))
{
    function b64_encode($obj)
    {
        if (is_array($obj))
        {
            return urlencode(base64_encode(json_encode($obj)));
        }
        return urlencode(base64_encode($obj));
    }
}


if (!(function_exists('b64_decode')))
{
    function b64_decode($str, $is_array = true)
    {
        $str = base64_decode(urldecode($str));
        if ($is_array)
        {
            return json_decode($str, true);
        }
        return $str;
    }
}


if (!(function_exists('create_image')))
{
    function create_image($img)
    {
        $ext = strtolower(substr($img, strrpos($img, '.')));
        if ($ext == '.png')
        {
            $thumb = imagecreatefrompng($img);
        }
        else if ($ext == '.gif')
        {
            $thumb = imagecreatefromgif($img);
        }
        else
        {
            $thumb = imagecreatefromjpeg($img);
        }
        return $thumb;
    }
}


if (!(function_exists('get_authcode')))
{
    function get_authcode()
    {
        $auth = get_auth();
        return (empty($auth['code']) ? '' : $auth['code']);
    }
}


if (!(function_exists('get_auth')))
{
    function get_auth()
    {
        global $_W;
        $set = pdo_fetch('select sets from ' . tablename('ewei_shop_sysset') . ' order by id asc limit 1');
        $sets = iunserializer($set['sets']);
        if (is_array($sets))
        {
            return (is_array($sets['auth']) ? $sets['auth'] : array());
        }
        return array();
    }
}

if (!(function_exists('rc')))
{
    function rc($plugin = '')
    {
        global $_W;
        global $_GPC;
        $domain = trim(preg_replace('/http(s)?:\\/\\//', '', rtrim($_W['siteroot'], '/')));
        $ip = gethostbyname($_SERVER['HTTP_HOST']);
        $setting = setting_load('site');
        $id = ((isset($setting['site']['key']) ? $setting['site']['key'] : '0'));
        $auth = get_auth();
        load()->func('communication');
        $resp = ihttp_request(EWEI_SHOPV2_AUTH_URL, array('ip' => $ip, 'id' => $id, 'code' => $auth['code'], 'domain' => $domain, 'plugin' => $plugin), NULL, 1);
        $result = @json_decode($resp['content'], true);
        if (!(empty($result['status'])))
        {
            return true;
        }
        return false;
    }
}

if (!(function_exists('ce')))
{
    function ce($permtype = '', $item = NULL)
    {
        $perm = com_run('perm::check_edit', $permtype, $item);
        return $perm;
    }
}
if (!(function_exists('cv')))
{
    function cv($permtypes = '')
    {
        $perm = com_run('perm::check_perm', $permtypes);
        return $perm;
    }
}
if (!(function_exists('ca')))
{
    function ca($permtypes = '')
    {
        global $_W;
        $err = '您没有权限操作，请联系管理员!';
        if (!(cv($permtypes)))
        {
            if ($_W['isajax'])
            {
                show_json(0, $err);
            }
            show_message($err, '', 'error');
        }
    }
}

if (!(function_exists('cp')))
{
    function cp($pluginname = '')
    {
        $perm = com('perm');
        if ($perm)
        {
            return $perm->check_plugin($pluginname);
        }
        return true;
    }
}

if (!(function_exists('cpa')))
{
    function cpa($pluginname = '')
    {
        if (!(cp($pluginname)))
        {
            show_message('您没有权限操作，请联系管理员!', '', 'error');
        }
    }
}

if (!(function_exists('tpl_daterange')))
{
    function tpl_daterange($name, $value = array(), $time = false)
    {
        global $_GPC;
        $placeholder = ((isset($value['placeholder']) ? $value['placeholder'] : ''));
        $s = '';
        if (empty($time) && !(defined('TPL_INIT_DATERANGE_DATE')))
        {
            $s = "\r\n" . '<script type="text/javascript">' . "\r\n\t" . 'require(["daterangepicker"], function(){' . "\r\n\t\t" . '$(function(){' . "\r\n\t\t\t" . '$(".daterange.daterange-date").each(function(){' . "\r\n\t\t\t\t" . 'var elm = this;' . "\r\n" . '                var container =$(elm).parent().prev();' . "\r\n\t\t\t\t" . '$(this).daterangepicker({' . "\r\n\t\t\t\t\t" . 'format: "YYYY-MM-DD"' . "\r\n\t\t\t\t" . '}, function(start, end){' . "\r\n\t\t\t\t\t" . '$(elm).find(".date-title").html(start.toDateStr() + " 至 " + end.toDateStr());' . "\r\n\t\t\t\t\t" . 'container.find(":input:first").val(start.toDateTimeStr());' . "\r\n\t\t\t\t\t" . 'container.find(":input:last").val(end.toDateTimeStr());' . "\r\n\t\t\t\t" . '});' . "\r\n\t\t\t" . '});' . "\r\n\t\t" . '});' . "\r\n\t" . '});' . "\r\n" . '</script> ' . "\r\n";
            define('TPL_INIT_DATERANGE_DATE', true);
        }
        if (!(empty($time)) && !(defined('TPL_INIT_DATERANGE_TIME')))
        {
            $s = "\r\n" . '<script type="text/javascript">' . "\r\n\t" . 'require(["daterangepicker"], function(){' . "\r\n\t\t" . '$(function(){' . "\r\n\t\t\t" . '$(".daterange.daterange-time").each(function(){' . "\r\n\t\t\t\t" . 'var elm = this;' . "\r\n" . '                 var container =$(elm).parent().prev();' . "\r\n\t\t\t\t" . '$(this).daterangepicker({' . "\r\n\t\t\t\t\t" . 'format: "YYYY-MM-DD HH:mm",' . "\r\n\t\t\t\t\t" . 'timePicker: true,' . "\r\n\t\t\t\t\t" . 'timePicker12Hour : false,' . "\r\n\t\t\t\t\t" . 'timePickerIncrement: 1,' . "\r\n\t\t\t\t\t" . 'minuteStep: 1' . "\r\n\t\t\t\t" . '}, function(start, end){' . "\r\n\t\t\t\t\t" . '$(elm).find(".date-title").html(start.toDateTimeStr() + " 至 " + end.toDateTimeStr());' . "\r\n\t\t\t\t\t" . 'container.find(":input:first").val(start.toDateTimeStr());' . "\r\n\t\t\t\t\t" . 'container.find(":input:last").val(end.toDateTimeStr());' . "\r\n\t\t\t\t" . '});' . "\r\n\t\t\t" . '});' . "\r\n\t\t" . '});' . "\r\n\t" . '});' . "\r\n" . '     function clearTime(obj){' . "\r\n" . '              $(obj).prev().html("<span class=date-title>" + $(obj).attr("placeholder") + "</span>");' . "\r\n" . '              $(obj).parent().prev().find("input").val("");' . "\r\n" . '    }' . "\r\n" . '</script>' . "\r\n";
            define('TPL_INIT_DATERANGE_TIME', true);
        }
        $str = $placeholder;
        $small = ((isset($value['sm']) ? $value['sm'] : true));
        $value['starttime'] = ((isset($value['starttime']) ? $value['starttime'] : (($_GPC[$name]['start'] ? $_GPC[$name]['start'] : ''))));
        $value['endtime'] = ((isset($value['endtime']) ? $value['endtime'] : (($_GPC[$name]['end'] ? $_GPC[$name]['end'] : ''))));
        if ($value['starttime'] && $value['endtime'])
        {
            if (empty($time))
            {
                $str = date('Y-m-d', strtotime($value['starttime'])) . '至 ' . date('Y-m-d', strtotime($value['endtime']));
            }
            else
            {
                $str = date('Y-m-d H:i', strtotime($value['starttime'])) . ' 至 ' . date('Y-m-d  H:i', strtotime($value['endtime']));
            }
        }
        $s .= '<div style="float:left">' . "\r\n\t" . '<input name="' . $name . '[start]' . '" type="hidden" value="' . $value['starttime'] . '" />' . "\r\n\t" . '<input name="' . $name . '[end]' . '" type="hidden" value="' . $value['endtime'] . '" />' . "\r\n" . '           </div>' . "\r\n" . '          <div class="btn-group ' . (($small ? 'btn-group-sm' : '')) . '" style="' . $value['style'] . 'padding-right:0;"  >' . "\r\n" . '          ' . "\r\n\t" . '<button style="width:240px" class="btn btn-default daterange ' . ((!(empty($time)) ? 'daterange-time' : 'daterange-date')) . '"  type="button"><span class="date-title">' . $str . '</span></button>' . "\r\n" . '        <button class="btn btn-default ' . (($small ? 'btn-sm' : '')) . '" " type="button" onclick="clearTime(this)" placeholder="' . $placeholder . '"><i class="fa fa-remove"></i></button>' . "\r\n" . '         </div>' . "\r\n\t";
        return $s;
    }
}


if (!(function_exists('mobileUrl')))
{
    function mobileUrl($do = '', $query = NULL, $full = false)
    {
        global $_W;
        global $_GPC;
        !($query) && ($query = array());
        $dos = explode('/', trim($do));
        $routes = array();
        $routes[] = $dos[0];
        if (isset($dos[1]))
        {
            $routes[] = $dos[1];
        }
        if (isset($dos[2]))
        {
            $routes[] = $dos[2];
        }
        if (isset($dos[3]))
        {
            $routes[] = $dos[3];
        }
        $r = implode('.', $routes);
        if (!(empty($r)))
        {
            $query = array_merge(array('r' => $r), $query);
        }
        $query = array_merge(array('do' => 'mobile'), $query);
        $query = array_merge(array('m' => 'ewei_shopv2'), $query);
        if (empty($query['mid']))
        {
            $mid = intval($_GPC['mid']);
            if (!(empty($mid)))
            {
                $query['mid'] = $mid;
            }
            if (!(empty($_W['openid'])) && !(is_weixin()) && !(is_h5app()))
            {
                $myid = m('member')->getMid();
                if (!(empty($myid)))
                {
                    $member = pdo_fetch('select id,isagent,status from' . tablename('ewei_shop_member') . 'where id=' . $myid);
                    if (!(empty($member['isagent'])) && !(empty($member['status'])))
                    {
                        $query['mid'] = $member['id'];
                    }
                }
            }
        }
        if (empty($query['merchid']))
        {
            $merchid = intval($_GPC['merchid']);
            if (!(empty($merchid)))
            {
                $query['merchid'] = $merchid;
            }
        }
        else if ($query['merchid'] < 0)
        {
            unset($query['merchid']);
        }
        if (empty($query['liveid']))
        {
            $liveid = intval($_GPC['liveid']);
            if (!(empty($liveid)))
            {
                $query['liveid'] = $liveid;
            }
        }
        if ($full)
        {
            return $_W['siteroot'] . 'app/' . substr(murl('entry', $query, true), 2);
        }
        return murl('entry', $query, true);
    }
}
if (!(function_exists('webUrl')))
{
    function webUrl($do = '', $query = array(), $full = true)
    {
        global $_W;
        global $_GPC;
        if (!(empty($_W['plugin'])))
        {
            if ($_W['plugin'] == 'merch')
            {
                if (function_exists('merchUrl'))
                {
                    return merchUrl($do, $query, $full);
                }
            }
            if ($_W['plugin'] == 'newstore')
            {
                if (function_exists('newstoreUrl'))
                {
                    return newstoreUrl($do, $query, $full);
                }
            }
        }
        $dos = explode('/', trim($do));
        $routes = array();
        $routes[] = $dos[0];
        if (isset($dos[1]))
        {
            $routes[] = $dos[1];
        }
        if (isset($dos[2]))
        {
            $routes[] = $dos[2];
        }
        if (isset($dos[3]))
        {
            $routes[] = $dos[3];
        }
        $r = implode('.', $routes);
        if (!(empty($r)))
        {
            $query = array_merge(array('r' => $r), $query);
        }
        $query = array_merge(array('do' => 'web'), $query);
        $query = array_merge(array('m' => 'ewei_shopv2'), $query);
        if ($full)
        {
            return $_W['siteroot'] . 'web/' . substr(wurl('site/entry', $query), 2);
        }
        return wurl('site/entry', $query);
    }
}
if (!(function_exists('tpl_form_field_category_3level')))
{
    function tpl_form_field_category_3level($name, $parents, $children, $parentid, $childid, $thirdid)
    {
        $html = "\r\n" . '<script type="text/javascript">' . "\r\n\t" . 'window._' . $name . ' = ' . json_encode($children) . ';' . "\r\n" . '</script>';
        if (!(defined('TPL_INIT_CATEGORY_THIRD')))
        {
            $html .= "\t\r\n" . '<script type="text/javascript">' . "\r\n\t" . '  function renderCategoryThird(obj, name){' . "\r\n\t\t" . 'var index = obj.options[obj.selectedIndex].value;' . "\r\n\t\t" . 'require([\'jquery\', \'util\'], function($, u){' . "\r\n\t\t\t" . '$selectChild = $(\'#\'+name+\'_child\');' . "\r\n" . '                                                      $selectThird = $(\'#\'+name+\'_third\');' . "\r\n\t\t\t" . 'var html = \'<option value="0">请选择二级分类</option>\';' . "\r\n" . '                                                      var html1 = \'<option value="0">请选择三级分类</option>\';' . "\r\n\t\t\t" . 'if (!window[\'_\'+name] || !window[\'_\'+name][index]) {' . "\r\n\t\t\t\t" . '$selectChild.html(html); ' . "\r\n" . '                                                                        $selectThird.html(html1);' . "\r\n\t\t\t\t" . 'return false;' . "\r\n\t\t\t" . '}' . "\r\n\t\t\t" . 'for(var i=0; i< window[\'_\'+name][index].length; i++){' . "\r\n\t\t\t\t" . 'html += \'<option value="\'+window[\'_\'+name][index][i][\'id\']+\'">\'+window[\'_\'+name][index][i][\'name\']+\'</option>\';' . "\r\n\t\t\t" . '}' . "\r\n\t\t\t" . '$selectChild.html(html);' . "\r\n" . '                                                    $selectThird.html(html1);' . "\r\n\t\t" . '});' . "\r\n\t" . '}' . "\r\n" . '        function renderCategoryThird1(obj, name){' . "\r\n\t\t" . 'var index = obj.options[obj.selectedIndex].value;' . "\r\n\t\t" . 'require([\'jquery\', \'util\'], function($, u){' . "\r\n\t\t\t" . '$selectChild = $(\'#\'+name+\'_third\');' . "\r\n\t\t\t" . 'var html = \'<option value="0">请选择三级分类</option>\';' . "\r\n\t\t\t" . 'if (!window[\'_\'+name] || !window[\'_\'+name][index]) {' . "\r\n\t\t\t\t" . '$selectChild.html(html);' . "\r\n\t\t\t\t" . 'return false;' . "\r\n\t\t\t" . '}' . "\r\n\t\t\t" . 'for(var i=0; i< window[\'_\'+name][index].length; i++){' . "\r\n\t\t\t\t" . 'html += \'<option value="\'+window[\'_\'+name][index][i][\'id\']+\'">\'+window[\'_\'+name][index][i][\'name\']+\'</option>\';' . "\r\n\t\t\t" . '}' . "\r\n\t\t\t" . '$selectChild.html(html);' . "\r\n\t\t" . '});' . "\r\n\t" . '}' . "\r\n" . '</script>' . "\r\n\t\t\t";
            define('TPL_INIT_CATEGORY_THIRD', true);
        }
        $html .= '<div class="row row-fix tpl-category-container">' . "\r\n\t" . '<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">' . "\r\n\t\t" . '<select class="form-control tpl-category-parent" id="' . $name . '_parent" name="' . $name . '[parentid]" onchange="renderCategoryThird(this,\'' . $name . '\')">' . "\r\n\t\t\t" . '<option value="0">请选择一级分类</option>';
        $ops = '';
        foreach ($parents as $row )
        {
            $html .= "\r\n\t\t\t" . '<option value="' . $row['id'] . '" ' . (($row['id'] == $parentid ? 'selected="selected"' : '')) . '>' . $row['name'] . '</option>';
        }
        $html .= "\r\n\t\t" . '</select>' . "\r\n\t" . '</div>' . "\r\n\t" . '<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">' . "\r\n\t\t" . '<select class="form-control tpl-category-child" id="' . $name . '_child" name="' . $name . '[childid]" onchange="renderCategoryThird1(this,\'' . $name . '\')">' . "\r\n\t\t\t" . '<option value="0">请选择二级分类</option>';
        if (!(empty($parentid)) && !(empty($children[$parentid])))
        {
            foreach ($children[$parentid] as $row )
            {
                $html .= "\r\n\t\t\t" . '<option value="' . $row['id'] . '"' . (($row['id'] == $childid ? 'selected="selected"' : '')) . '>' . $row['name'] . '</option>';
            }
        }
        $html .= "\r\n\t\t" . '</select> ' . "\r\n\t" . '</div> ' . "\r\n" . '                  <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">' . "\r\n\t\t" . '<select class="form-control tpl-category-child" id="' . $name . '_third" name="' . $name . '[thirdid]">' . "\r\n\t\t\t" . '<option value="0">请选择三级分类</option>';
        if (!(empty($childid)) && !(empty($children[$childid])))
        {
            foreach ($children[$childid] as $row )
            {
                $html .= "\r\n\t\t\t" . '<option value="' . $row['id'] . '"' . (($row['id'] == $thirdid ? 'selected="selected"' : '')) . '>' . $row['name'] . '</option>';
            }
        }
        $html .= '</select>' . "\r\n\t" . '</div>' . "\r\n" . '</div>';
        return $html;
    }
}
if (!(function_exists('array_column')))
{
    function array_column($input, $column_key, $index_key = NULL)
    {
        $arr = array();
        foreach ($input as $d )
        {
            if (!(isset($d[$column_key])))
            {
                return;
            }
            if ($index_key !== NULL)
            {
                return array($d[$index_key] => $d[$column_key]);
            }
            $arr[] = $d[$column_key];
        }
        if ($index_key !== NULL)
        {
            $tmp = array();
            foreach ($arr as $ar )
            {
                $tmp[key($ar)] = current($ar);
            }
            $arr = $tmp;
        }
        return $arr;
    }
}
if (!(function_exists('is_utf8')))
{
    function is_utf8($str)
    {
        return preg_match('%^(?:' . "\r\n" . '            [\\x09\\x0A\\x0D\\x20-\\x7E]              # ASCII' . "\r\n" . '            | [\\xC2-\\xDF][\\x80-\\xBF]             # non-overlong 2-byte' . "\r\n" . '            | \\xE0[\\xA0-\\xBF][\\x80-\\xBF]         # excluding overlongs' . "\r\n" . '            | [\\xE1-\\xEC\\xEE\\xEF][\\x80-\\xBF]{2}  # straight 3-byte' . "\r\n" . '            | \\xED[\\x80-\\x9F][\\x80-\\xBF]         # excluding surrogates' . "\r\n" . '            | \\xF0[\\x90-\\xBF][\\x80-\\xBF]{2}      # planes 1-3' . "\r\n" . '            | [\\xF1-\\xF3][\\x80-\\xBF]{3}          # planes 4-15' . "\r\n" . '            | \\xF4[\\x80-\\x8F][\\x80-\\xBF]{2}      # plane 16' . "\r\n" . '            )*$%xs', $str);
    }
}
if (!(function_exists('price_format')))
{
    function price_format($price)
    {
        $prices = explode('.', $price);
        if (intval($prices[1]) <= 0)
        {
            $price = $prices[0];
        }
        else if (isset($prices[1][1]) && ($prices[1][1] <= 0))
        {
            $price = $prices[0] . '.' . $prices[1][0];
        }
        return $price;
    }
}
if (!(function_exists('createRedPack')))
{
    function createRedPack($money, $sum, $min = 0.01)
    {
        if (($money / $sum) < $min)
        {
            return false;
        }
        $_leftMoneyPackage = array('remainSize' => (int) $sum, 'remainMoney' => round($money, 2));
        $array_money = array();
        $i = 0;
        while ($i < $sum)
        {
            if (($money / $sum) == 0.01)
            {
                array_push($array_money, 0.01);
                continue;
            }
            if ($_leftMoneyPackage['remainSize'] == 1)
            {
                --$_leftMoneyPackage['remainSize'];
                array_push($array_money, round($_leftMoneyPackage['remainMoney'], 2));
                break;
            }
            $r = lcg_value();
            $max = ($_leftMoneyPackage['remainMoney'] / $_leftMoneyPackage['remainSize']) * 2;
            $tem_money = $r * $max;
            $tem_money = (($tem_money <= $min ? 0.01 : $tem_money));
            $tem_money = floor($tem_money * 100) / 100;
            --$_leftMoneyPackage['remainSize'];
            $_leftMoneyPackage['remainMoney'] -= $tem_money;
            array_push($array_money, (double) $tem_money);
            ++$i;
        }
        return $array_money;
    }
}
if (!(function_exists('redis')))
{
    function redis()
    {
        global $_W;
        static $redis;
        if (is_null($redis))
        {
            if (!(extension_loaded('redis')))
            {
                return error(-1, 'PHP 未安装 redis 扩展');
            }
            if (!(isset($_W['config']['setting']['redis'])))
            {
                return error(-1, '未配置 redis, 请检查 data/config.php 中参数设置');
            }
            $config = $_W['config']['setting']['redis'];
            if (empty($config['server']))
            {
                $config['server'] = '127.0.0.1';
            }
            if (empty($config['port']))
            {
                $config['port'] = '6379';
            }
            $redis_temp = new Redis();
            if ($config['pconnect'])
            {
                $connect = $redis_temp->pconnect($config['server'], $config['port'], $config['timeout']);
            }
            else
            {
                $connect = $redis_temp->connect($config['server'], $config['port'], $config['timeout']);
            }
            if (!($connect))
            {
                return error(-1, 'redis 连接失败, 请检查 data/config.php 中参数设置');
            }
            if (!(empty($config['requirepass'])))
            {
                $redis_temp->auth($config['requirepass']);
            }
            try
            {
                $ping = $redis_temp->ping();
            }
            catch (ErrorException $e)
            {
                return error(-1, 'redis 无法正常工作，请检查 redis 服务');
            }
            if ($ping != '+PONG')
            {
                return error(-1, 'redis 无法正常工作，请检查 redis 服务');
            }
            $redis = $redis_temp;
        }
        else
        {
            try
            {
                $ping = $redis->ping();
            }
            catch (ErrorException $e)
            {
                $redis = NULL;
                $redis = redis();
                $ping = $redis->ping();
            }
            if ($ping != '+PONG')
            {
                $redis = NULL;
                $redis = redis();
            }
        }
        return $redis;
    }
}
if (!(function_exists('logg')))
{
    function logg($name, $data)
    {
        global $_W;
        $data = ((is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data));
        file_put_contents(IA_ROOT . '/' . $name, $data);
    }
}
if (!(function_exists('is_wxerror')))
{
    function is_wxerror($data)
    {
        if (!(is_array($data)) || !(array_key_exists('errcode', $data)) || (array_key_exists('errcode', $data) && ($data['errcode'] == 0)))
        {
            return false;
        }
        return true;
    }
}
if (!(function_exists('set_wxerrmsg')))
{
    function set_wxerrmsg($data)
    {
        $errors = array(-1 => '系统繁忙，此时请稍候再试', 0 => '请求成功', 40001 => '获取access_token时AppSecret错误，或者access_token无效。请认真比对AppSecret的正确性，或查看是否正在为恰当的公众号调用接口', 40002 => '不合法的凭证类型', 40003 => '不合法的OpenID，请确认OpenID（该用户）是否已关注公众号，或是否是其他公众号的OpenID', 40004 => '不合法的媒体文件类型', 40005 => '不合法的文件类型', 40006 => '不合法的文件大小', 40007 => '不合法的媒体文件id', 40008 => '不合法的消息类型', 40009 => '不合法的图片文件大小', 40010 => '不合法的语音文件大小', 40011 => '不合法的视频文件大小', 40012 => '不合法的缩略图文件大小', 40013 => '不合法的AppID，请检查AppID的正确性，避免异常字符，注意大小写', 40014 => '不合法的access_token，请认真比对access_token的有效性（如是否过期），或查看是否正在为恰当的公众号调用接口', 40015 => '不合法的菜单类型', 40016 => '不合法的按钮个数', 40017 => '不合法的按钮个数', 40018 => '不合法的按钮名字长度', 40019 => '不合法的按钮KEY长度', 40020 => '不合法的按钮URL长度', 40021 => '不合法的菜单版本号', 40022 => '不合法的子菜单级数', 40023 => '不合法的子菜单按钮个数', 40024 => '不合法的子菜单按钮类型', 40025 => '不合法的子菜单按钮名字长度', 40026 => '不合法的子菜单按钮KEY长度', 40027 => '不合法的子菜单按钮URL长度', 40028 => '不合法的自定义菜单使用用户', 40029 => '不合法的oauth_code', 40030 => '不合法的refresh_token', 40031 => '不合法的openid列表', 40032 => '不合法的openid列表长度', 40033 => '不合法的请求字符，不能包含\\uxxxx格式的字符', 40035 => '不合法的参数', 40038 => '不合法的请求格式', 40039 => '不合法的URL长度', 40050 => '不合法的分组id', 40051 => '分组名字不合法', 40117 => '分组名字不合法', 40118 => 'media_id大小不合法', 40119 => 'button类型错误', 40120 => 'button类型错误', 40121 => '不合法的media_id类型', 40132 => '微信号不合法', 40137 => '不支持的图片格式', 40155 => '请勿添加其他公众号的主页链接', 41001 => '缺少access_token参数', 41002 => '缺少appid参数', 41003 => '缺少refresh_token参数', 41004 => '缺少secret参数', 41005 => '缺少多媒体文件数据', 41006 => '缺少media_id参数', 41007 => '缺少子菜单数据', 41008 => '缺少oauth code', 41009 => '缺少openid', 42001 => 'access_token超时，请检查access_token的有效期，请参考基础支持-获取access_token中，对access_token的详细机制说明', 42002 => 'refresh_token超时', 42003 => 'oauth_code超时', 42007 => '用户修改微信密码，accesstoken和refreshtoken失效，需要重新授权', 43001 => '需要GET请求', 43002 => '需要POST请求', 43003 => '需要HTTPS请求', 43004 => '需要接收者关注', 43005 => '需要好友关系', 43019 => '需要将接收者从黑名单中移除', 44001 => '多媒体文件为空', 44002 => 'POST的数据包为空', 44003 => '图文消息内容为空', 44004 => '文本消息内容为空', 45001 => '多媒体文件大小超过限制', 45002 => '消息内容超过限制', 45003 => '标题字段超过限制', 45004 => '描述字段超过限制', 45005 => '链接字段超过限制', 45006 => '图片链接字段超过限制', 45007 => '语音播放时间超过限制', 45008 => '图文消息超过限制', 45009 => '接口调用超过限制', 45010 => '创建菜单个数超过限制', 45011 => 'API调用太频繁，请稍候再试', 45015 => '回复时间超过限制', 45016 => '系统分组，不允许修改', 45017 => '分组名字过长', 45018 => '分组数量超过上限', 45047 => '客服接口下行条数超过上限', 46001 => '不存在媒体数据', 46002 => '不存在的菜单版本', 46003 => '不存在的菜单数据', 46004 => '不存在的用户', 47001 => '解析JSON/XML内容错误', 48001 => 'api功能未授权，请确认公众号已获得该接口，可以在公众平台官网-开发者中心页中查看接口权限', 48002 => '粉丝拒收消息（粉丝在公众号选项中，关闭了“接收消息”）', 48004 => 'api接口被封禁，请登录mp.weixin.qq.com查看详情', 48005 => 'api禁止删除被自动回复和自定义菜单引用的素材', 48006 => 'api禁止清零调用次数，因为清零次数达到上限', 50001 => '用户未授权该api', 50002 => '用户受限，可能是违规后接口被封禁', 61451 => '参数错误(invalid parameter)', 61452 => '无效客服账号(invalid kf_account)', 61453 => '客服帐号已存在(kf_account exsited)', 61454 => '客服帐号名长度超过限制(仅允许10个英文字符，不包括@及@后的公众号的微信号)(invalid   kf_acount length)', 61455 => '客服帐号名包含非法字符(仅允许英文+数字)(illegal character in     kf_account)', 61457 => '无效头像文件类型(invalid   file type)', 61450 => '系统错误(system error)', 61500 => '日期格式错误', 65301 => '不存在此menuid对应的个性化菜单', 65302 => '没有相应的用户', 65303 => '没有默认菜单，不能创建个性化菜单', 65304 => 'MatchRule信息为空', 65305 => '个性化菜单数量受限', 65306 => '不支持个性化菜单的帐号', 65307 => '个性化菜单信息为空', 65308 => '包含没有响应类型的button', 65309 => '个性化菜单开关处于关闭状态', 65310 => '填写了省份或城市信息，国家信息不能为空', 65311 => '填写了城市信息，省份信息不能为空', 65312 => '不合法的国家信息', 65313 => '不合法的省份信息', 65314 => '不合法的城市信息', 65316 => '该公众号的菜单设置了过多的域名外跳（最多跳转到3个域名的链接）', 65317 => '不合法的URL', 9001001 => 'POST数据参数不合法', 9001002 => '远端服务不可用', 9001003 => 'Ticket不合法', 9001004 => '获取摇周边用户信息失败', 9001005 => '获取商户信息失败', 9001006 => '获取OpenID失败', 9001007 => '上传文件缺失', 9001008 => '上传素材的文件类型不合法', 9001009 => '上传素材的文件尺寸不合法', 9001010 => '上传失败', 9001020 => '帐号不合法', 9001021 => '已有设备激活率低于50%，不能新增设备', 9001022 => '设备申请数不合法，必须为大于0的数字', 9001023 => '已存在审核中的设备ID申请', 9001024 => '一次查询设备ID数量不能超过50', 9001025 => '设备ID不合法', 9001026 => '页面ID不合法', 9001027 => '页面参数不合法', 9001028 => '一次删除页面ID数量不能超过10', 9001029 => '页面已应用在设备中，请先解除应用关系再删除', 9001030 => '一次查询页面ID数量不能超过50', 9001031 => '时间区间不合法', 9001032 => '保存设备与页面的绑定关系参数错误', 9001033 => '门店ID不合法', 9001034 => '设备备注信息过长', 9001035 => '设备申请参数不合法', 9001036 => '查询起始值begin不合法');
        if (array_key_exists($data['errcode'], $errors))
        {
            $data['errmsg'] = $errors[$data['errcode']];
        }
        return $data;
    }
}


if(!(function_exists('test'))) {
    function test(){
        return '这里是测试方法';
    }
}

if (!(function_exists('CheckPhone')))
{
    // 验证手机号码
    function CheckPhone($mobile) {
        if(!is_numeric($mobile)) {
            return false;
        }
        return preg_match('/^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$/',$mobile) ? true : false;
    }
}

if(!(function_exists('checkEmail'))) {
    function checkEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            return true;
        } else {
            return false;
        }
    }
}


if(!(function_exists('send_mail'))) {
    /**
     * 系统邮件发送函数
     * @param string $tomail 接收邮件者邮箱
     * @param string $name 接收邮件者名称
     * @param string $subject 邮件主题
     * @param string $body 邮件内容
     * @param string $attachment 附件列表
     * @return boolean
     * @author static7 <static7@qq.com>
     */
    function send_mail($tomail, $name, $subject = '', $body = '', $attachment = null)
    {
        require IA_ROOT . '/framework/library/phpmailer/PHPMailerAutoload.php';

        $mail = new PHPMailer();           //实例化PHPMailer对象
        $mail->CharSet = 'UTF-8';                    //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
        $mail->IsSMTP();                            // 设定使用SMTP服务
        $mail->SMTPDebug = 0;                    // SMTP调试功能 0=关闭 1 = 错误和消息 2 = 消息
        $mail->SMTPAuth = true;                    // 启用 SMTP 验证功能
        //$mail->SMTPSecure = 'ssl';          		// 使用安全协议
        $mail->SMTPSecure = 'tls';                // 使用安全协议
        $mail->Host = "smtp.qq.com";                // SMTP 服务器
        //$mail->Port = 465;                 			// SMTP服务器的端口号
        $mail->Port = 587;                            // SMTP服务器的端口号

        /*$mail->Username = "2164977442@qq.com";        // SMTP服务器用户名    805929498@qq.com
        $mail->Password = "kxtjuhmpicdaecac";        // SMTP服务器密码     auelorsctusbbfgh*/

        $mail->Username = '805929498@qq.com';
        $mail->Password = 'zjpbqdibcdmobgac';
        /**
         * txrosoelfjiybcej(新)   dbflwnifoxmobedd(旧)
         */
        //$mail->SetFrom('2164977442@qq.com', '系统管理员');
        $mail->SetFrom('805929498@qq.com', '系统管理员');
        $replyEmail = '';                        //留空则为发件人EMAIL
        $replyName = '';                            //回复名称（留空则为发件人名称）
        $mail->AddReplyTo($replyEmail, $replyName);
        $mail->Subject = $subject;
        $mail->MsgHTML($body);
        $mail->AddAddress($tomail, $name);
        if (is_array($attachment)) { // 添加附件
            foreach ($attachment as $file) {
                is_file($file) && $mail->AddAttachment($file);
            }
        }
        return $mail->Send() ? true : $mail->ErrorInfo;
    }
}


if(!(function_exists('ImgUpload'))) {
    function ImgUpload($files) {
        // 允许上传文件类型
        $allExts = ['jpeg','jpg','png'];
        //
        $temp = explode('.',$files['name']);
        // 获取文件后缀
        $extension = end($temp);
        // 判断文件类型
        if(($files['type'] == 'image/jpeg')
            || ($files['type'] == 'image/jpg')
            || ($files['type'] == 'image/png')
            && in_array($extension, $allExts)) {

            // 判断文件错误类型
            if($files['error'] > 0) {
                return json_encode(['code'=>0,'msg'=>'错误'.$files['error']]);
            }
            // 新文件名称
            $fileName = md5(random(9)).'.'.$extension;
            // 保存文件路径
            $savePath = dirname(__DIR__).'/upload/'.$fileName;
            // 返回前端路径
            $retPaht = 'addons/mer_chats/upload/'.$fileName;
            // 上传文件
            if(!move_uploaded_file($files['tmp_name'],$savePath)) {
                return json_encode(['code'=>0,'msg'=>'图片上传失败']);
            }
            return json_encode(['code'=>1,'msg'=>'图片上传成功','path'=>$retPaht]);
            // 返回数据
        }
    }
}

?>