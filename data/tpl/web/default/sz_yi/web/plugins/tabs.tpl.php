<?php defined('IN_IA') or exit('Access Denied');?><div class="ulleft-nav">
<ul class="nav nav-tabs">
<div class="addtit-name"> 应用中心</div>
<?php  if(is_array($category)) { foreach($category as $ck => $cv) { ?>
<?php  if(count($cv['plugins'])<=0) { ?><?php  continue;?><?php  } ?>
<li>
<a href="#<?php  echo $ck;?>" name="<?php  echo $cv['name'];?>">
        <?php  echo $cv['name'];?>
</a>
</li>
<?php  } } ?>
</ul>
</div>
