<?php defined('IN_IA') or exit('Access Denied');?><body>
<div class="wfs head_top">
    <div class="cover-page-wrapper">
        <ul class="fl top_l">
            <li class="fore"></li>
        </ul>
        <ul class="fr top_r">
            <li class="fore"></li>
        <?php  if($_COOKIE[__cookie_sz_yi_userid_.$_W['uniacid']]) { ?>
            <li class="fore"><a href="<?php  echo $this->createMobileUrl('order')?>">个人中心</a></li>
        <?php  } else { ?>
            <li class="fore"><a href="<?php  echo $this->createMobileUrl('member')?>"></a></li>
            <li class="fore"><a href="<?php  echo $this->createMobileUrl('member/register')?>"></a></li>
        <?php  } ?>

            <li class="fore cart" id="ECS_CARTINFO">
                <a href="<?php  echo $this->createMobileUrl('shop/cart')?>" title="查看购物车"></a>
             </li>
        </ul>
    </div>
</div>

<div id="head-fix" class="head fl wfs nav-wrap">
<div class="cover-page-wrapper head_logo_nav">
<!-- logo -->
    <div class="logo fl">
    </div>
 
</div>
</div>
<script type="text/javascript">
    require(['tpl', 'core'], function(tpl, core) {
	$('#keyword').keyup(function(){
	    if(event.keyCode == 13){
		$('.search').click();
		return;
	    }
	});

        $('.search').click(function(){
            var args  = {
                   page:"<?php  echo $_GPC['page'];?>",
                   isnew: "<?php  echo $_GPC['isnew'];?>",
                   ishot: "<?php  echo $_GPC['ishot'];?>",
                   isrecommand:"<?php  echo $_GPC['isrecommand'];?>",
                   isdiscount:"<?php  echo $_GPC['isdiscount'];?>",
                   keywords:"<?php  echo $_GPC['keywords'];?>",
                   istime:"<?php  echo $_GPC['istime'];?>",
                   pcate:"<?php  echo $_GPC['pcate'];?>",
                   ccate:"<?php  echo $_GPC['ccate'];?>",
                   tcate:"<?php  echo $_GPC['tcate'];?>",
                   order:"<?php  echo $_GPC['order'];?>",
                   by:"<?php  echo $_GPC['by'];?>",
                   shopid:"<?php  echo $_GPC['shopid'];?>",
                   keywords:$("#keyword").val()
            };
            location.href=core.getUrl('shop/list', args);
        });
    })
</script>
