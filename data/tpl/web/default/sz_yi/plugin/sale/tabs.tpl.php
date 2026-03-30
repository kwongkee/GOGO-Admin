<?php defined('IN_IA') or exit('Access Denied');?><div class="ulleft-nav">
<ul class="nav nav-tabs">
<div class="addtit-name"> 营销</div>
    <?php if(cv('sale.deduct')) { ?><li <?php  if($_GPC['method']=='deduct') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createPluginWebUrl('sale/deduct')?>">抵扣设置</a></li><?php  } ?>
    <?php if(cv('sale.enough')) { ?><li <?php  if($_GPC['method']=='enough') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createPluginWebUrl('sale/enough')?>">满额优惠设置</a></li><?php  } ?>
   <?php if(cv('sale.recharge')) { ?><li <?php  if($_GPC['method']=='recharge') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createPluginWebUrl('sale/recharge')?>">充值优惠设置</a></li><?php  } ?>

    <li class="step"></li>
 
   <?php if(cv('coupon.coupon')) { ?><li <?php  if($_GPC['method']=='coupon') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createPluginWebUrl('coupon/coupon')?>">超级券管理</a></li><?php  } ?>
   <?php if(cv('coupon.category')) { ?><li <?php  if($_GPC['method']=='category') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createPluginWebUrl('coupon/category')?>">分类管理</a></li><?php  } ?>
   <?php  if($_GPC['method']=='log') { ?> <li class="active"><a href="#">超级券记录</a></li><?php  } ?>
   <?php  if($_GPC['method']=='send') { ?> <li class="active"><a href="#">发放超级券</a></li><?php  } ?>
   <?php if(cv('coupon.center')) { ?><li <?php  if($_GPC['method']=='center') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createPluginWebUrl('coupon/center')?>">领券中心设置</a></li><?php  } ?>
   <?php if(cv('coupon.set')) { ?><li <?php  if($_GPC['method']=='set') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createPluginWebUrl('coupon/set')?>">其他设置</a></li><?php  } ?>
   

</ul>
</div>
  
