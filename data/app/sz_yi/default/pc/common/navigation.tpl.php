<?php defined('IN_IA') or exit('Access Denied');?><body>
<div class="wfs head_top">
  <div class="cover-page-wrapper">
    <ul class="fl top_l">
      <li class="fore"><a href="<?php  echo $this->createMobileUrl('shop/index')?>"><?php  if($_W['uniacid'] == 18) { ?>Welcome To<?php  } else { ?>欢迎来到<?php  } ?><?php  echo $this->yzShopSet['name']?>！</a></li>
      <?php  if($this->yzShopSet['phone']) { ?>
      <li class="fore">客服电话：<?php  echo $this->yzShopSet['phone']?></li>
      <?php  } ?>
    </ul>
    <ul class="fr top_r">
      <?php  if($_COOKIE[__cookie_sz_yi_userid_.$_W['uniacid']]) { ?>
      <li class="fore"><?php  if($_W['uniacid'] == 18) { ?>Dear member<?php  } else { ?>尊敬的会员<?php  } ?>：<?php  echo $_COOKIE['member_mobile']?></li>
      <li class="fore"><a href="<?php  echo $this->createMobileUrl('order')?>"><?php  if($_W['uniacid'] == 18) { ?>Member Centre<?php  } else { ?>会员中心<?php  } ?></a></li>
      <li class="fore"><a href="<?php  echo $this->createMobileUrl('member/logout')?>"><?php  if($_W['uniacid'] == 18) { ?>[Sign out]<?php  } else { ?>[退出登录]<?php  } ?></a></li>
      <?php  } else { ?>
      <li class="fore"><a href="<?php  echo $this->createMobileUrl('member')?>" target="__blank"><?php  if($_W['uniacid'] == 18) { ?>Login<?php  } else { ?>你好，请登录<?php  } ?></a></li>
      <li class="spacer"></li>
      <li class="fore"><a href="<?php  echo $this->createMobileUrl('member/register')?>" target="__blank"><?php  if($_W['uniacid'] == 18) { ?>Reg<?php  } else { ?>免费注册<?php  } ?></a></li>
      <?php  } ?>
    </ul>
  </div>
</div>
<div id="head-fix" class="head wfs nav-wrap">
  <div class="cover-page-wrapper head_logo_nav"> 
    <!-- logo -->
    <div class="logo fl"> <a href="<?php  echo $this->createMobileUrl('shop/index')?>">
    <?php  if($this->yzShopSet['pclogo']) { ?>
      <?php  if(FALSE == stristr($this->yzShopSet['pclogo'], "http")) { ?>
        <?php  $pclogo = $_W['siteroot'] . "attachment/" . $this->yzShopSet['pclogo'];?>
      <?php  } else { ?>
        $pclogo = "<?php  echo $_W['siteroot'];?>attachment/{$this->yzShopSet['pclogo']}";
      <?php  } ?>
      <img src="<?php  echo $pclogo?>" style="width:270px;height:60px;" title="<?php  echo $this->yzShopSet['pctitle']?>">
    <?php  } else { ?>
      <img src="../addons/sz_yi/template/pc/default/static/images/logo.png" title="" alt="我是默认logo"> 
    <?php  } ?>
  </a></div>

    <div class="fore cart fr" id="ECS_CARTINFO"> <a href="<?php  echo $this->createMobileUrl('shop/cart')?>" title="查看购物车"><i class="fontello-icon-basket"></i><span><?php  if($_W['uniacid'] == 18) { ?>Cart<?php  } else { ?>购物车<?php  } ?></span><strong><b>0</b></strong></a> </div>
    
    <!--<form id="searchForm" name="searchForm" method="get" action="" onSubmit="return checkSearchForm()" class="search-product fl">-->

    <div class="search-product fr">
      <input name="k_template" type="hidden" class="k_value" value="0">
      <i class="fontello-icon-search fr search"></i>
      <input autocomplete="off" name="keywords" type="text" id="keyword" placeholder="<?php  if($_W['uniacid'] == 18) { ?>Input Keywords<?php  } else { ?>输入关键字搜索<?php  } ?>" value="" class="form-control search-box fr"/>
      <div class="suggestions_box" id="suggestions" style="display:none;border-bottom: 1px solid #cdcdcd;">
        <div class="suggestions_list" id="auto_suggestions_list"></div>
      </div>
    </div>
    <!--</form>--> 
    
  </div>
  <?php  if($this->yzShopSet['hmenu_name']) { ?>
  <div class="nav_header_warp">
    <ul class="nav_head">
      <?php  if(is_array($this->yzShopSet['hmenu_name'])) { foreach($this->yzShopSet['hmenu_name'] as $k => $v) { ?>
      <li> <a href="<?php  echo $this->yzShopSet['hmenu_url'][$k]?>"><?php  echo $v;?></a> </li>
      <?php  } } ?>
    </ul>
  </div>
  <?php  } else { ?>
  <div class="nav_header_warp">
    <ul class="nav_head">
      <li class="first"> <a href="<?php  echo $this->createMobileUrl('shop/list', array('order' => 'sales', 'by' => 'desc'))?>">全部商品</a> </li>
      <li> <a href="<?php  echo $this->createMobileUrl('shop/index')?>">首页</a> </li>
      <?php  if($_GPC['i'] == 3) { ?>
      <li> <a href="<?php  echo $this->createMobileUrl('shop/notice')?>">店铺公告</a> </li>
      <?php  if($this->footer['commission']) { ?>
      <li <?php  if($footer_current=='commission') { ?>class='active'<?php  } ?>> <a href="<?php  echo $this->footer['commission']['url']?>"> <span><?php  echo $this->footer['commission']['text']?></span> </a> </li>
      <?php  } ?>
      <?php  } ?>
      <li> <a href="<?php  echo $this->createMobileUrl('order')?>">会员中心</a> </li>
    </ul>
  </div>
  <?php  } ?> </div>
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
