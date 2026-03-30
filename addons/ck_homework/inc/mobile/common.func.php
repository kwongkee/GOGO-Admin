<?php

function ck_checkauth()
{
    global $_W, $engine, $_GPC;
    load()->model("mc");
    $url = $_W["siteroot"] . "app/index.php?i=" . $_GPC["i"] . "&c=entry&do=login&m=" . $_GPC["m"];
    if (!empty($_W["member"]) && (!empty($_W["member"]["mobile"]) || !empty($_W["member"]["email"]))) {
        return true;
    }
    if (!empty($_W["openid"])) {
        $fan = mc_fansinfo($_W["openid"], $_W["acid"], $_W["uniacid"]);
        if (empty($fan) && $_W["account"]["level"] == ACCOUNT_SERVICE_VERIFY) {
            $fan = mc_oauth_userinfo();
            if (!empty($fan["openid"])) {
                $fan = mc_fansinfo($fan["openid"]);
            }
        }
        if (_mc_login(array("uid" => intval($fan["uid"])))) {
            return true;
        }
        if (defined("IN_API")) {
            $GLOBALS["engine"]->died("抱歉，您需要先登录才能使用此功能，点击此处 <a href='" . $url . "'>【登录】</a>");
        }
    }
    header("Location: {$url}");
    exit;
}
function template_app($filename)
{
    global $_W;
    $name = $_W["current_module"]["name"];
    $compile = IA_ROOT . "/data/tpl/app/{$_W["template"]}/{$name}/{$filename}.tpl.php";
    $source = IA_ROOT . "/addons/" . $name . "/template/mobile/" . $template . "/" . $filename . ".html";
    if (!is_file($source)) {
        $source = IA_ROOT . "/addons/" . $name . "/template/mobile/" . $filename . ".html";
    }
    if (!is_file($source)) {
        exit("Error: template source '{$filename}' is not exist!");
    }
    $paths = pathinfo($compile);
    $compile = str_replace($paths["filename"], $_W["uniacid"] . "_" . $paths["filename"], $compile);
    if (DEVELOPMENT || !is_file($compile) || filemtime($source) > filemtime($compile)) {
        template_compile($source, $compile, true);
    }
    return $compile;
}
function message_app($msg, $redir = array(), $type = '', $msgerd = array())
{
    global $_W, $_GPC;
    if ($redir[0] == "refresh") {
        $redirect = $_W["script_name"] . "?" . $_SERVER["QUERY_STRING"];
    } elseif (!empty($redir[0]) && !strexists($redir[0], "http://")) {
        $urls = parse_url($redir[0]);
        $redirect = $_W["siteroot"] . "app/index.php?" . $urls["query"];
    } else {
        $redirect = $redir[0];
    }
    if ($redir[1] == "refresh") {
        $redirect1 = $_W["script_name"] . "?" . $_SERVER["QUERY_STRING"];
    } elseif (!empty($redir[1]) && !strexists($redir[1], "http://")) {
        $urls1 = parse_url($redir[1]);
        $redirect1 = $_W["siteroot"] . "app/index.php?" . $urls1["query"];
    } else {
        $redirect1 = $redir[1];
    }
    $type = in_array($type, array("success", "error", "info", "warning", "ajax", "sql")) ? $type : "success";
    if (!empty($msgerd[0])) {
        $msgerdt = $msgerd[0];
    } else {
        $msgerdt = "确定";
    }
    if (!empty($msgerd[1])) {
        $msgerdt1 = $msgerd[1];
    } else {
        $msgerdt1 = "返回";
    }
    include template_app("message");
    exit;
    $type = in_array($type, array("success", "error", "info", "warning", "ajax", "sql")) ? $type : "success";
    if (!empty($msgerd[0])) {
        $msgerdt = $msgerd[0];
    } else {
        $msgerdt = "确定";
    }
    if (!empty($msgerd[1])) {
        $msgerdt1 = $msgerd[1];
    } else {
        $msgerdt1 = "返回";
    }
    include template_app("message");
    exit;
    if ($redir[1] == "refresh") {
        $redirect1 = $_W["script_name"] . "?" . $_SERVER["QUERY_STRING"];
    } elseif (!empty($redir[1]) && !strexists($redir[1], "http://")) {
        $urls1 = parse_url($redir[1]);
        $redirect1 = $_W["siteroot"] . "app/index.php?" . $urls1["query"];
    } else {
        $redirect1 = $redir[1];
    }
    $type = in_array($type, array("success", "error", "info", "warning", "ajax", "sql")) ? $type : "success";
    if (!empty($msgerd[0])) {
        $msgerdt = $msgerd[0];
    } else {
        $msgerdt = "确定";
    }
    if (!empty($msgerd[1])) {
        $msgerdt1 = $msgerd[1];
    } else {
        $msgerdt1 = "返回";
    }
    include template_app("message");
    exit;
    $type = in_array($type, array("success", "error", "info", "warning", "ajax", "sql")) ? $type : "success";
    if (!empty($msgerd[0])) {
        $msgerdt = $msgerd[0];
    } else {
        $msgerdt = "确定";
    }
    if (!empty($msgerd[1])) {
        $msgerdt1 = $msgerd[1];
    } else {
        $msgerdt1 = "返回";
    }
    include template_app("message");
    exit;
}
define("MOBILE_ASSETS", MODULE_URL . "Assets/mobile/");
function Download_media($server_id)
{
    global $_W;
    $setting = pdo_get("onljob_config", array("weid" => $_W["account"]["acid"]), array("audio_open"));
    $account_api = WeAccount::create();
    $account_api->clearAccessToken();
    $access_token = $account_api->getAccessToken();
    if ($setting["audio_open"] == 1) {
        load()->func("communication");
        load()->func("file");
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$server_id}";
        $data = ihttp_get($url);
        if ($data["content"] && !isset(json_decode($data["content"], true)["errcode"])) {
            $file_name = random_filename("audio", "amr");
            if (!file_exists($file_name)) {
                $file_name = random_filename("audio", "amr");
                if (file_write($file_name, $data["content"])) {
                    return $file_name;
                } else {
                    return '';
                }
            }
        } else {
            return '';
        }
    } else {
        return '';
    }
}
$config = pdo_get("onljob_config", array("weid" => $_W["uniacid"]));
$copyright = $config["copyright"];
function random_filename($type = "image", $ext)
{
    global $_W;
    $path = "{$type}s/{$_W["account"]["acid"]}/" . date("Y/m/");
    if (!file_exists(ATTACHMENT_ROOT . "/" . $path)) {
        mkdir(ATTACHMENT_ROOT . "/" . $path);
    }
    return $path . random(30) . "." . $ext;
}