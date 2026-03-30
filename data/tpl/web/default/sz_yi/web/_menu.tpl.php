<?php defined('IN_IA') or exit('Access Denied');?><style type='text/css'>
    .topmenu .dropdown-menu li a { color:#666}
    .topmenu ul li { content:'|'}
	.splitter { text-align: center;background:#ccc;color:#039702;padding-top:10px; padding-bottom:10px;font-size:12px;}
	.splitter{background:#fff;}
</style>
<div class="topmenubg">
<div class='topmenu'>
    <ul class="nav navbar-nav">
        <li>
            <a href="<?php  echo $this->createWebUrl('shop/index')?>" class="dropdown-toggle"><i class='fa fa-tachometer'></i>
                <span>概况</span> <i class="fa fa-angle-down"></i></a>
        </li>
        <?php if(cv('shop')) { ?>
        <li>
            <a href="<?php  echo $this->createWebUrl('shop/goods', array('status'=>1))?>" class="dropdown-toggle"><i class='fa fa-tasks'></i>
                <span>商品</span> <i class="fa fa-angle-down"></i></a>
        </li>
        <?php  } ?>
        <?php if(cv('order')) { ?>
        <li>
            <a href='<?php  echo $this->createWebUrl('order', array('op' => 'display'))?>' class="dropdown-toggle" ><i class='fa fa-archive'></i> <span>订单</span> <i class="fa fa-angle-down"></i></a>
        </li>
        <?php  } ?>

		<?php if(cv('shop.dispatch.view')) { ?>
		<li>
			<a href="<?php  echo $this->createWebUrl('shop/dispatch')?>" class="dropdown-toggle"><i class='fa fa-truck' style="margin-top: 2px"></i> <span>配送</span></a> 
        </li> 
		<?php  } ?>

        <?php  if(p('sale')) { ?>
            <?php if(cp('sale')) { ?>
                <?php if(cv('sale')) { ?>
         <li>
            <a href='<?php  echo $this->createPluginWebUrl('sale')?>' class="dropdown-toggle" ><i class='fa fa-gift' style="margin-top: 2px"></i> <span>营销</span> <i class="fa fa-angle-down"></i></a>
        </li> 
                <?php  } ?> 
            <?php  } ?>        
		<?php  } ?>

	   <?php  if(p('designer')) { ?>
            <?php if(cp('designer')) { ?>
                <?php if(cv('designer')) { ?>
		<li>
            <a href='<?php  echo $this->createPluginWebUrl('designer')?>' class="dropdown-toggle" ><i class='fa fa-gavel'></i> <span>装修</span><i class="fa fa-angle-down"></i></a>
        </li>
                <?php  } ?>
            <?php  } ?>        
		<?php  } ?>
        

        <li>
           <a href='<?php  echo $this->createWebUrl('plugins',array('op'=>'list'))?>' class="dropdown-toggle" ><i class='fa fa-cubes'></i> <span>应用</span> <i class="fa fa-angle-down"></i></a>
        </li>
          <?php if(cv('finance')) { ?>
         <li>
            <a href='<?php  echo $this->createWebUrl('finance/log',array('type'=>0))?>' class="dropdown-toggle" ><i class='iconfont icon-web-price' style="margin-top: 2px"></i> <span>财务</span> <i class="fa fa-angle-down"></i></a>
        </li> 
        <?php  } ?> 
          <?php if(cv('sysset')) { ?>
         <li>
            <a href='<?php  echo $this->createWebUrl('sysset',array('op'=>'shop'))?>' class="dropdown-toggle"><i class='iconfont icon-cog' style="margin-top: 2px"></i> <span>设置</span> <i class="fa fa-angle-down"></i></a>

        </li>
        <?php  } ?>
    </ul>
</div>
</div>
