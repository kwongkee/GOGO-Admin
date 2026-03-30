<?php defined('IN_IA') or exit('Access Denied');?><div class="ulleft-nav">
<div class="addtit-name">设置</div>
<ul class="nav nav-tabs">
    <?php if(cv('sysset.view.shop')) { ?><li <?php  if($_GPC['op']=='shop' or  $_GPC['do']=='shop' or $_GPC['op']=='sms' or $_GPC['op']=='member' or $_GPC['op']=='template' or $_GPC['op']=='category' or $_GPC['op']=='contact') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('sysset',array('op'=>'shop'))?>">商城设置</a></li><?php  } ?>
    <?php if(cv('sysset.save.pcset')) { ?><li <?php  if($_GPC['op']=='pcset') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('sysset',array('op'=>'pcset'))?>">PC端设置</a></li><?php  } ?>
    <?php if(cv('sysset.view.follow')) { ?><li  <?php  if($_GPC['op']=='follow') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('sysset',array('op'=>'follow'))?>">引导分享</a></li><?php  } ?>
    <?php if(cv('sysset.view.notice')) { ?><li  <?php  if($_GPC['op']=='notice') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('sysset',array('op'=>'notice'))?>">消息提醒</a></li><?php  } ?>
    <?php if(cv('sysset.view.trade')) { ?><li  <?php  if($_GPC['op']=='trade') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('sysset',array('op'=>'trade'))?>">交易设置</a></li><?php  } ?>
    <?php if(cv('sysset.view.pay')) { ?><li  <?php  if($_GPC['op']=='pay') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('sysset',array('op'=>'pay'))?>">支付方式</a></li><?php  } ?>
	<?php  if($_W['isfounder']) { ?> 
	  <li <?php  if($_GPC['p']=='perm') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createPluginWebUrl('perm/setting')?>">权限设置</a></li>
	  <?php  } ?>
    <?php if(cv('sysset.view.upgrade')) { ?><li  <?php  if($_GPC['op']=='display') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('upgrade',array('op'=>'display'))?>">系统升级</a></li><?php  } ?>
</ul> 
</div>
