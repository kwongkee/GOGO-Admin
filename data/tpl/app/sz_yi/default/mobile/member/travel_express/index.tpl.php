<?php defined('IN_IA') or exit('Access Denied');?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<title>自我郵</title>
	<link rel="stylesheet" type="text/css" href="../addons/sz_yi/static/travel_express/css/hui.css" />
	<link rel="stylesheet" type="text/css" href="../addons/sz_yi/static/travel_express/css/style.css" />
	<style>
	    #list2 li{width:50%; float:left;}
	    #list2 .hui-img-list-content{padding:5px; padding-bottom:10px;}
	    #list2 h1{font-size:14px;}

    </style>
</head>
<body>
<header class="hui-header" style="background: url('../addons/sz_yi/static/travel_express/images/header1.jpg') right no-repeat #1b7ab6;">
    <!-- <div id="hui-back"></div> -->
    <div><img src="../addons/sz_yi/static/travel_express/images/logo.png" alt="" style="float: left; width: 38%; margin-top: 5px; margin-left: 10px;"></div>
    <!-- <h1>自我邮</h1> -->
    <!-- <div id="hui-header-menu"></div> -->
</header>
<div style="margin-top: 44px;">
    <div class="hui-swipe" id="swipe">
    	<div class="hui-swipe-items">
            <div class="hui-swipe-item"><img src="../addons/sz_yi/static/travel_express/img/swipe/1.jpg"/></div>
            <div class="hui-swipe-item"><img src="../addons/sz_yi/static/travel_express/img/swipe/2.jpg"/></div>
            <!--<div class="hui-swipe-item"><img src="../addons/sz_yi/static/travel_express/img/swipe/3.jpg"/></div>
            <div class="hui-swipe-item"><img src="../addons/sz_yi/static/travel_express/img/swipe/4.jpg"/></div>-->
        </div>
    </div>
</div>	

<div class="home-list" id="list2" style="padding:10px; margin-bottom: 60px;">
    <ul>
        <li>
            <a href="<?php  echo $this->createMobileUrl("member/travel_express_receive")?>">
                <img src="../addons/sz_yi/static/travel_express/images/icon_01.png" />
                <p>预报清单</p>
                <p class="title-en">Forecast list</p>
            </a>
        </li>
        <li class="home-list-right">
            <a href="<?php  echo $this->createMobileUrl("member/travel_express_list")?>">
                <img src="../addons/sz_yi/static/travel_express/images/icon_02.png" />
                <p>清单列表</p>
                <p class="title-en">Checklist</p>
            </a>
        </li>
        <li class="home-list-bottom">
            <a href="<?php  echo $this->createMobileUrl("member/travel_express_search")?>">
                <img src="../addons/sz_yi/static/travel_express/images/icon_03.png" />
                <p>订单查询</p>
                <p class="title-en">Order Tracking</p>
            </a>
        </li>
        <li class="home-list-bottom home-list-right">
            <a href="<?php  echo $this->createMobileUrl("member/travel_express_address")?>">
                <img src="../addons/sz_yi/static/travel_express/images/icon_04.png" />
                <p>收件地址</p>
                <p class="title-en">Shipping Address</p>
            </a>
        </li>
    </ul>
</div>	

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/travel_express_footer', TEMPLATE_INCLUDEPATH)) : (include template('common/travel_express_footer', TEMPLATE_INCLUDEPATH));?>

<script src="../addons/sz_yi/static/travel_express/js/hui.js" type="text/javascript" charset="utf-8"></script>
<script src="../addons/sz_yi/static/travel_express/js/hui-swipe.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
var swipe = new huiSwpie('#swipe');
swipe.autoPlay = false;
swipe.run();

</script>
</body>
</html>