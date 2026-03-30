<?php defined('IN_IA') or exit('Access Denied');?><div class="ulleft-nav">
<div class="addtit-name"><a  class="btn btn-success ng-scope"  href="<?php  echo $this->createWebUrl('shop/goods',array('op'=>'post'))?>"><i class="fa fa-plus"></i> 发布商品</a></div>
<ul class="nav nav-tabs">
    <?php if(cv('shop.goods.view')) { ?><li <?php  if(($_GPC['p'] == 'goods' || empty($_GPC['p'])) && $_GPC['status'] == 1) { ?> class="active" <?php  } ?>><a href="<?php  echo $this->createWebUrl('shop/goods', array('status'=>1))?>">出售中商品</a></li><?php  } ?>
    <?php if(cv('shop.goods.view')) { ?><li <?php  if(($_GPC['p'] == 'goods' || empty($_GPC['p'])) && $_GPC['status'] == 0) { ?> class="active" <?php  } ?>><a href="<?php  echo $this->createWebUrl('shop/goods', array('status'=>0))?>">已下架商品</a></li><?php  } ?>
    <?php if(cv('shop.category.view')) { ?><li <?php  if($_GPC['p'] == 'category') { ?> class="active" <?php  } ?>><a href="<?php  echo $this->createWebUrl('shop/category')?>">商品分类</a></li><?php  } ?>
    <?php  if($shopset['category2']==1) { ?><?php if(cv('shop.category.view')) { ?><li <?php  if($_GPC['p'] == 'category2') { ?> class="active" <?php  } ?>><a href="<?php  echo $this->createWebUrl('shop/category2')?>"><?php  echo $shopset['category2name']?>分类</a></li><?php  } ?><?php  } ?>
    <?php if(cv('shop.comment.view')) { ?><li <?php  if($_GPC['p'] == 'comment') { ?> class="active" <?php  } ?>><a href="<?php  echo $this->createWebUrl('shop/comment')?>">评价管理</a></li><?php  } ?>
    <li class="step"></li>

	<?php if(cv('virtual.temp')) { ?><li <?php  if($_GPC['method']=='temp' && $_GPC['p'] == 'virtual' ) { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createPluginWebUrl('virtual/temp')?>" style="cursor: pointer;">虚拟卡密</a></li><?php  } ?>
    <?php if(cv('virtual.category')) { ?><li <?php  if($_GPC['method']=='category' && $_GPC['p'] == 'virtual') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createPluginWebUrl('virtual/category')?>" style="cursor: pointer;">卡密分类</a></li><?php  } ?>
     <?php if(cv('virtual.data')) { ?>
    <?php  if($_GPC['method']=='data' && $operation=='post' && $_GPC['p'] == 'virtual') { ?><li class="active"><a href="javascript:;" style="cursor: pointer;">添加数据</a></li><?php  } ?>
    <?php  if($_GPC['method']=='data' && $operation=='display' && $_GPC['p'] == 'virtual') { ?><li class="active"><a href="javascript:;" style="cursor: pointer;">数据列表</a></li><?php  } ?> 
    <?php  } ?>
     <?php if(cv('virtual.set')) { ?><li <?php  if($_GPC['method']=='set' && $_GPC['p'] == 'virtual') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createPluginWebUrl('virtual/set')?>" style="cursor: pointer;">卡密设置</a></li><?php  } ?>

    <li class="step"></li>
    <li <?php  if($_GPC['p'] == 'taobao' && $_GPC['method'] == 'index') { ?> class="active" <?php  } ?>><a href="<?php  echo $this->createPluginWebUrl('taobao')?>">淘宝商品导入</a></li>
    <li <?php  if($_GPC['p'] == 'taobao' && $_GPC['method'] == 'jingdong') { ?> class="active" <?php  } ?>><a href="<?php  echo $this->createPluginWebUrl('taobao/jingdong')?>">京东商品导入</a></li>
    <li <?php  if($_GPC['p'] == 'taobao' && $_GPC['method'] == 'one688') { ?> class="active" <?php  } ?>><a href="<?php  echo $this->createPluginWebUrl('taobao/one688')?>">1688商品导入</a></li>
    <li <?php  if($_GPC['p'] == 'taobao' && $_GPC['method'] == 'taobaocsv') { ?> class="active" <?php  } ?>><a href="<?php  echo $this->createPluginWebUrl('taobao/taobaocsv')?>">淘宝CSV上传导入</a></li>
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
