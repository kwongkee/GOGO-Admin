<?php defined('IN_IA') or exit('Access Denied');?><div class="ulleft-nav">
<ul class="nav nav-tabs">
    <li <?php  if($_GPC['method']=='print_list') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createPluginWebUrl('yunprint/print_list')?>">打印机管理</a></li>
    <li <?php  if($_GPC['method']=='set') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createPluginWebUrl('yunprint/set')?>">基础设置</a></li>
</ul>
</div>
