<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_header', TEMPLATE_INCLUDEPATH)) : (include template('web/_header', TEMPLATE_INCLUDEPATH));?>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/plugins/tabs', TEMPLATE_INCLUDEPATH)) : (include template('web/plugins/tabs', TEMPLATE_INCLUDEPATH));?>
<div class='panel-default pdl236'>
    <div class="right-addbox">
    <?php  if(is_array($category)) { foreach($category as $ck => $cv) { ?>
	 <?php  if(count($cv['plugins'])<=0) { ?><?php  continue;?><?php  } ?>

    <div class="unit-head" id="<?php  echo $ck;?>">
        <?php  echo $cv['name'];?>
    </div>
    <div class="unit-list">
            <ul>
        <?php  if(is_array($cv['plugins'])) { foreach($cv['plugins'] as $plugin) { ?>
        <?php  if(empty($plugins_data) || in_array($plugin['identity'], $plugins_data)) { ?>
            <?php if(cp($plugin['identity'])) { ?>
                <?php  if(p($plugin['identity'])) { ?>
                <li>
                <a href="<?php  echo $this->createPluginWebUrl($plugin['identity'])?>" title="<?php  echo $plugin['name'];?>">
                    <i class="iconfont icon-<?php  echo $plugins_icon[$plugin['identity']];?> <?php  echo $ck;?>"></i>
                    <h1><?php  echo $plugin['name'];?></h1>
                    <p><?php  echo $plugin['desc'];?></p>
                </a>
                </li>
                <?php  } ?>
            <?php  } ?>
        <?php  } ?>
        <?php  } } ?>
    </ul>
        </div>
	<?php  } } ?>
  </div>
</div>
<script>
$('a[href*=#]').click(function() {
   if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'')
   && location.hostname == this.hostname) {
     var $target = $(this.hash);
     $target = $target.length && $target
     || $('[name=' + this.hash.slice(1) +']');
     if ($target.length) {
       var targetOffset = $target.offset().top;
       var obj = document.documentElement; 
           obj = document.body
      $(obj).animate({scrollTop: targetOffset}, 700);
       return false;
     }
   }
});
</script>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_footer', TEMPLATE_INCLUDEPATH)) : (include template('web/_footer', TEMPLATE_INCLUDEPATH));?>
