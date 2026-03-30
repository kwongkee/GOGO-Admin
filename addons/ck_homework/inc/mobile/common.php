<?php

require "common.func.php";
load()->model("mc");
$default_groupid = cache_load("defaultgroupid:{$_W["uniacid"]}");
if (empty($default_groupid)) {
    $default_groupid = $_W["uniacid"];
}
$templateurl = "../addons/{$_GPC["m"]}/template/mobile/";
if (strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger") === false) {
    load()->model("user");
    ck_checkauth();
} elseif (empty($_W["fans"]["openid"])) {
    exit("您是通过订阅号进行访问，该接入的订阅号未对公众平台oAuth借权设置！无法访问！");
}
if (empty($_W["fans"]["uid"]) && empty($_W["fans"]["nickname"])) {
    $params = array(":openid" => $_W["fans"]["openid"], ":acid" => $_W["acid"], ":uniacid" => $_W["uniacid"]);
    $sql = "SELECT * FROM " . tablename("mc_mapping_fans") . " WHERE `openid` = :openid AND `acid` = :acid AND `uniacid` = :uniacid ";
    $fansinfo = pdo_fetch($sql, $params);
    if (!empty($fansinfo)) {
        $_W["member"]["uid"] = $fansinfo["uid"];
    } else {
        $userinfo = mc_oauth_userinfo();
        if (empty($userinfo["openid"])) {
            message_app("OPENID为空！访问失败！", array($this->createMobileurl("index")), "error");
        }
        $record = array();
        $record["updatetime"] = TIMESTAMP;
        $record["nickname"] = stripslashes($userinfo["nickname"]);
        $record["tag"] = base64_encode(iserializer($userinfo));
        $record["openid"] = $userinfo["openid"];
        $record["acid"] = $_W["acid"];
        $record["uniacid"] = $_W["uniacid"];
        $record["salt"] = random(8);
        $data = array("uniacid" => $_W["uniacid"], "email" => md5(random(8)) . "@we7.cc", "salt" => random(8), "groupid" => $default_groupid, "nickname" => $userinfo["nickname"], "avatar" => $userinfo["avatar"], "gender" => $userinfo["sex"], "nationality" => $userinfo["country"], "resideprovince" => $userinfo["province"], "residecity" => $userinfo["city"]);
        $data["password"] = md5($data["salt"] . $_W["config"]["setting"]["authkey"]);
        pdo_insert("mc_members", $data);
        $record["uid"] = pdo_insertid();
        pdo_insert("mc_mapping_fans", $record);
        $_W["member"]["uid"] = $record["uid"];
    }
} else {
    $params = array(":openid" => $_W["fans"]["openid"], ":acid" => $_W["acid"], ":uniacid" => $_W["uniacid"]);
    $sql = "SELECT * FROM " . tablename("mc_mapping_fans") . " WHERE `openid` = :openid AND `acid` = :acid AND `uniacid` = :uniacid ";
    $fansinfo = pdo_fetch($sql, $params);
    if (!empty($fansinfo)) {
        $members = pdo_get("mc_members", array("uniacid" => $_W["uniacid"], "uid" => $fansinfo["uid"]));
        if (empty($fansinfo["uid"]) || empty($members)) {
            $userinfo = mc_oauth_userinfo();
            $data = array("uniacid" => $_W["uniacid"], "email" => md5(random(8)) . "@we7.cc", "salt" => random(8), "groupid" => $default_groupid, "nickname" => $userinfo["nickname"], "avatar" => $userinfo["avatar"], "gender" => $userinfo["sex"], "nationality" => $userinfo["country"], "resideprovince" => $userinfo["province"], "residecity" => $userinfo["city"]);
            $data["password"] = md5($data["salt"] . $_W["config"]["setting"]["authkey"]);
            pdo_insert("mc_members", $data);
            $record["uid"] = pdo_insertid();
            if (!empty($record["uid"])) {
                pdo_update("mc_mapping_fans", array("uid" => $record["uid"]), array("uniacid" => $_W["uniacid"], "fanid" => $_W["fans"]["fanid"]));
            }
            $_W["member"]["uid"] = $record["uid"];
        } else {
            $_W["member"]["uid"] = $members["uid"];
        }
    } else {
        $userinfo = mc_oauth_userinfo();
        $record = array();
        $record["updatetime"] = TIMESTAMP;
        $record["nickname"] = stripslashes($_W["fans"]["nickname"]);
        $record["tag"] = base64_encode(iserializer($_W["fans"]["tag"]));
        $record["openid"] = $_W["fans"]["openid"];
        $record["acid"] = $_W["acid"];
        $record["uniacid"] = $_W["uniacid"];
        $record["salt"] = random(8);
        $data = array("uniacid" => $_W["uniacid"], "email" => md5(random(8)) . "@we7.cc", "salt" => random(8), "groupid" => $default_groupid, "nickname" => $userinfo["nickname"], "avatar" => $userinfo["avatar"], "gender" => $userinfo["sex"], "nationality" => $userinfo["country"], "resideprovince" => $userinfo["province"], "residecity" => $userinfo["city"]);
        $data["password"] = md5($data["salt"] . $_W["config"]["setting"]["authkey"]);
        pdo_insert("mc_members", $data);
        $record["uid"] = pdo_insertid();
        pdo_insert("mc_mapping_fans", $record);
        $_W["member"]["uid"] = $record["uid"];
    }
}


if (empty($_W["member"]["uid"])) {
    message_app("未登录无法访问！", '', "error");
}
$newstmes = time();
$config = pdo_get("onljob_config", array("weid" => $_W["uniacid"]));
$copyright = $config["copyright"];
$profile = pdo_get("mc_members", array("uniacid" => $_W["uniacid"], "uid" => $_W["member"]["uid"]));
$level_list = pdo_fetchall("SELECT * FROM " . tablename("onljob_vip_level") . " WHERE weid = '{$_W["uniacid"]}' and is_show = '1' ORDER BY sort ASC, id DESC", array(), "id");
$typeidall = array("1" => "范文", "2" => "模板", "3" => "素材", "4" => "技巧");
$type_arr = array("1" => "单选题", "2" => "多选题", "3" => "填空题", "4" => "判断题", "5" => "主观题", "6" => "作文题");
$level_arr = array("1" => "1级", "2" => "2级", "3" => "3级", "4" => "4级", "5" => "5级", "6" => "6级");
$arraytp = array("A", "B", "C", "D", "E", "F");
$user_show = pdo_get("onljob_user", array("weid" => $_W["uniacid"], "uid" => $_W["member"]["uid"]));
if (empty($user_show)) {
    $url = $_W["siteroot"] . "app/index.php?i=" . $_GPC["i"] . "&c=entry&do=register&m=" . $_GPC["m"];
    header("Location: {$url}");
    exit;
}
if ($user_show["type"] == "1") {
    $core_url = $this->createMobileUrl("t_index");
} elseif ($user_show["type"] == "2") {
    $core_url = $this->createMobileUrl("jz_index");
    W6BHX:
} else {
    $core_url = $this->createMobileUrl("m_index");
}

var_dump($userinfo);
//APShop提示：以上代码可能存在while语句，请根据上下文补充完善，没有请忽略。