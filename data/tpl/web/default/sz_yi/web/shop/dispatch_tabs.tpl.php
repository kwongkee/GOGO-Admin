<?php defined('IN_IA') or exit('Access Denied');?><div class="ulleft-nav">
<div class="addtit-name"> 配送方式及运费</div>
<ul class="nav nav-tabs">
    <?php if(cv('shop.dispatch.view')) { ?><li <?php  if($_GPC['p'] == 'dispatch') { ?> class="active" <?php  } ?>><a href="<?php  echo $this->createWebUrl('shop/dispatch', array('status'=>1))?>">运费模板设置</a></li><?php  } ?>
    <?php if(cv('shop.refundaddress.view')) { ?><li <?php  if($_GPC['p'] == 'refundaddress') { ?> class="active" <?php  } ?>><a href="<?php  echo $this->createWebUrl('shop/refundaddress', array('status'=>0))?>">退货地址设置</a></li><?php  } ?>
    <li class="step"></li>
    <?php if(cv('verify.keyword')) { ?><li <?php  if($_GPC['method']=='keyword') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createPluginWebUrl('verify/keyword')?>">核销设置</a></li><?php  } ?>
    <?php if(cv('verify.store')) { ?><li <?php  if($_GPC['method']=='store') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createPluginWebUrl('verify/store')?>">核销门店管理</a></li><?php  } ?>
    <?php if(cv('verify.store')) { ?><li <?php  if($_GPC['method']=='category') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createPluginWebUrl('verify/category')?>">门店分类管理</a></li><?php  } ?>
    <?php if(cv('verify.saler')) { ?><li  <?php  if($_GPC['method']=='saler') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createPluginWebUrl('verify/saler')?>">核销员管理</a></li><?php  } ?>
    <?php if(cv('verify.withdraw')) { ?><li  <?php  if($_GPC['method']=='withdraw') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createPluginWebUrl('verify/withdraw')?>">提現申請</a></li><?php  } ?>
    <!--
    <?php if(cv('shop.dispatch.view')) { ?><li <?php  if($_GPC['p'] == 'dispatch') { ?> class="active" <?php  } ?>><a href="<?php  echo $this->createWebUrl('shop/dispatch')?>">配送方式</a></li><?php  } ?>
    <?php if(cv('shop.adv.view')) { ?><li <?php  if($_GPC['p'] == 'adv') { ?> class="active" <?php  } ?>><a href="<?php  echo $this->createWebUrl('shop/adv')?>">幻灯片管理</a></li><?php  } ?>
    <?php if(cv('shop.notice.view')) { ?><li <?php  if($_GPC['p'] == 'notice') { ?> class="active" <?php  } ?>><a href="<?php  echo $this->createWebUrl('shop/notice')?>">公告管理</a></li><?php  } ?>
    <?php if(cv('shop.comment.view')) { ?><li <?php  if($_GPC['p'] == 'comment') { ?> class="active" <?php  } ?>><a href="<?php  echo $this->createWebUrl('shop/comment')?>">评价管理</a></li><?php  } ?>
    <?php if(cv('shop.adpc.view')) { ?><li <?php  if($_GPC['p'] == 'adpc') { ?> class="active" <?php  } ?>><a href="<?php  echo $this->createWebUrl('shop/adpc')?>">广告管理</a></li><?php  } ?>
    <?php if(cv('shop.refundaddress.view')) { ?><li <?php  if($_GPC['p'] == 'refundaddress') { ?> class="active" <?php  } ?>><a href="<?php  echo $this->createWebUrl('shop/refundaddress')?>">退货地址</a></li><?php  } ?>
-->
</ul>
</div>

