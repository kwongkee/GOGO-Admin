<?php defined('IN_IA') or exit('Access Denied');?><style type="text/css">
    footer#footer-nav .menu-list li { width:20%}
</style>
<?php  if($show_footer) { ?>
<?php  if($this->footer['diymenu']) { ?>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('designer/menu', TEMPLATE_INCLUDEPATH)) : (include template('designer/menu', TEMPLATE_INCLUDEPATH));?>
<?php  } else { ?>
<div style='height:80px;width:100%'></div>
<footer id="footer-nav">
                <ul class="menu-list">
                    <li <?php  if($footer_current=='first') { ?>class='active'<?php  } ?>><a href="<?php  echo $this->createPluginMobileUrl('commission/myshop')?>"><i class="iconfont icon-heart"></i><span><?php  echo $this->footer['first']['text']?></span></a></li>
                    <li <?php  if($footer_current=='second') { ?>class='active'<?php  } ?>><a href="<?php  echo $this->createMobileUrl('shop/category')?>"><i class="iconfont icon-list"></i><span><?php  echo $this->footer['second']['text']?></span></a></li>
                    <li <?php  if($footer_current=='commission') { ?>class='active'<?php  } ?>><a href="<?php  echo $this->createPluginMobileUrl('commission')?>"><i class="iconfont icon-sitemap"></i><span><?php  echo $this->footer['commission']['text']?></span></a></li>
                    <li <?php  if($footer_current=='cart') { ?>class='active'<?php  } ?>><a href="<?php  echo $this->createMobileUrl('shop/cart')?>"><i class="iconfont icon-cart"></i><span>购物车</span></a></li>
                    <li <?php  if($footer_current=='member') { ?>class='active'<?php  } ?>><a href="<?php  echo $this->createMobileUrl('member')?>"><i class="iconfont icon-user"></i><span>会员中心</span></a></li>
                </ul>
</footer>
<?php  } ?>
<?php  } ?>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer_base', TEMPLATE_INCLUDEPATH)) : (include template('common/footer_base', TEMPLATE_INCLUDEPATH));?>
