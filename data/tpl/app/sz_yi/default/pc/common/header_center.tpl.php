<?php defined('IN_IA') or exit('Access Denied');?><!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta charset="UTF-8">
	<title>用户中心_<?php  echo $shopset['pctitle']?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0 user-scalable=no">
    <meta name="format-detection" content="telephone=no" />
    <link href="../addons/sz_yi/static/css/font-awesome.min.css" rel="stylesheet">
    <script language="javascript" src="../addons/sz_yi/static/js/require.js"></script>
    <script language="javascript" src="../addons/sz_yi/static/js/app/config.js?v=2"></script>
    <script language="javascript" src="../addons/sz_yi/static/js/dist/jquery-1.11.1.min.js"></script>
    <script language="javascript" src="../addons/sz_yi/static/js/dist/jquery.gcjs.js"></script>
    <link href="../addons/sz_yi/static/font/iconfont.css" rel="stylesheet">

    <!-- <link rel="stylesheet" type="text/css" href="../addons/sz_yi/template/mobile/default/static/css/style.css"> -->

    <link rel="stylesheet" href="../addons/sz_yi/template/pc/default/static/css/bootstrap.min.css">
    <link rel="stylesheet" href="../addons/sz_yi/template/pc/default/static/css/member-center.css">
    <link rel="stylesheet" type="text/css" href="../addons/sz_yi/template/pc/default/static/css/style.css">
    </head>
    <body>



<script language="javascript">
    require(['core','tpl'],function(core,tpl){
        core.init({
            siteUrl: "<?php  echo $_W['siteroot'];?>",
            baseUrl: "<?php  echo $this->createMobileUrl('ROUTES')?>"
        });
    })
</script>
</head>
<body>
	<!--
	<script src="http://member.ecmoban.com/content/themes/ecmoban2014/js/jquery.min.js"></script>
    <script src="http://member.ecmoban.com/content/themes/ecmoban2014/bootstrap/js/bootstrap.min.js"></script>
    <script>var _hmt = _hmt || [];</script>
    -->
    <!--uniacid先修改为等于19，到时候改为18变回英文版-->
    <div class="top-head fl wfs fz12">
        <div class="wrapper">
            <div class="left fl">
            	<?php  if($_W['uniacid'] == 19) { ?>
            	Dear<?php  echo $this->yzShopSet['name']?>users, welcome to log in to the management center!
            	<?php  } else { ?>
            	尊敬的<?php  echo $this->yzShopSet['name']?>用户，欢迎登陆管理中心！
            	<?php  } ?>
            </div>
                <div class="right fr" >
               		<!--if $_W['uniacid'] == 18Your accountelse您的账号/if：php echo $_COOKIE['member_mobile']-->
                    <a href="<?php  echo $this->createMobileUrl('member/forget')?>">[<?php  if($_W['uniacid'] == 19) { ?>Change Password<?php  } else { ?>修改密码<?php  } ?>]</a>
                    <a href="<?php  echo $this->createMobileUrl('member/logout')?>">[<?php  if($_W['uniacid'] == 19) { ?>Sign Out<?php  } else { ?>退出<?php  } ?>]</a>
                </div>
        </div>
    </div>


<div class="head fl wfs">
    <div class="wrapper">
        <a class="logo" href="<?php  echo $this->createMobileUrl('shop/index')?>">
            <input type="hidden" value="hids">
            <?php  if($this->yzShopSet['pclogo']) { ?>
	            <?php  if(FALSE == stristr($this->yzShopSet['pclogo'], "http")) { ?>
	              <?php  $pclogo = $_W['siteroot'] . "attachment/" . $this->yzShopSet['pclogo'];?>
	            <?php  } else { ?>
	              $pclogo = "<?php  echo $_W['siteroot'];?>attachment/{$this->yzShopSet['pclogo']}";
	            <?php  } ?>
	            <img src="<?php  echo $pclogo?>" style="width:245px;" title="<?php  echo $this->yzShopSet['pctitle']?>">
	          <?php  } else { ?>
	            <img style="width: 245px;" src="../addons/sz_yi/template/pc/default/static/images/logo.png" title="" alt="我是默认logo"> 
	          <?php  } ?>  
            
             
        </a>
        <div class="nav">
            <a class="index" href="<?php  echo $this->createMobileUrl('shop/index')?>"><?php  if($_W['uniacid'] == 19) { ?>Home<?php  } else { ?>首页<?php  } ?></a>
            <a class="member member-now" href="<?php  echo $this->createMobileUrl('order')?>"><?php  if($_W['uniacid'] == 19) { ?>Member<?php  } else { ?>会员中心<?php  } ?></a>
            <a class="order1 " href="<?php  echo $this->createMobileUrl('shop/cart')?>"><?php  if($_W['uniacid'] == 19) { ?>Cart<?php  } else { ?>购物车<?php  } ?></a>

            <a class="account " href="<?php  echo $this->createMobileUrl('shop/favorite')?>"><?php  if($_W['uniacid'] == 19) { ?>Favorites<?php  } else { ?>我的收藏<?php  } ?></a>
            <a class="service " href="<?php  echo $this->createMobileUrl('shop/address')?>"><?php  if($_W['uniacid'] == 19) { ?>Address<?php  } else { ?>收货地址<?php  } ?></a>
        </div>
    </div>
</div>

