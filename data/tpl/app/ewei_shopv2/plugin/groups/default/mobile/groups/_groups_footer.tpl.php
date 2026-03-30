<?php defined('IN_IA') or exit('Access Denied');?><div class="fui-navbar" style="z-index: 10;">
	<a href="<?php  echo mobileUrl('groups')?>" class="external nav-item <?php  if($_GPC['r']=='groups') { ?>active<?php  } ?>">
		<span class="icon icon-home"></span>
		<span class="label">拼团首页</span>
	</a>
	<a href="<?php  echo mobileUrl('groups/category')?>" class="external nav-item <?php  if($_GPC['r']=='groups.category') { ?>active<?php  } ?>">
		<span class="icon icon-list"></span>
		<span class="label">活动列表</span>
	</a>
	<a href="<?php  echo mobileUrl('groups/orders')?>" class="external nav-item <?php  if($_GPC['r']=='groups.orders') { ?>active<?php  } ?>">
		<span class="icon icon-order"></span>
		<span class="label">我的订单</span>
	</a>
	<a href="<?php  echo mobileUrl('groups/team')?>" class="external nav-item <?php  if($_GPC['r']=='groups.team') { ?>active<?php  } ?>">
		<span class="icon icon-group"></span>
		<span class="label">我的团</span>
	</a>
</div>

