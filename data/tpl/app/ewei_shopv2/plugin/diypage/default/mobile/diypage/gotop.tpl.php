<?php defined('IN_IA') or exit('Access Denied');?><a class="diy-gotop external" style="<?php  if(!empty($diygotop['params']['gotoptype'])) { ?>width: <?php  echo $diygotop['style']['width'];?>px;<?php  } ?> position: fixed; overflow: hidden; z-index: 999; <?php  if($diygotop['params']['iconposition']=='left bottom') { ?>bottom: <?php  echo $diygotop['style']['top'];?>px; left: <?php  echo $diygotop['style']['left'];?>px; text-align: left;<?php  } else if($diygotop['params']['iconposition']=='right bottom') { ?>bottom: <?php  echo $diygotop['style']['top'];?>px; right: <?php  echo $diygotop['style']['left'];?>px; text-align: left;<?php  } ?> <?php  if(empty($diygotop['params']['gotopclick'])&&!empty($diygotop['params']['gotopheight'])) { ?>display:none;<?php  } ?>" <?php  if(!empty($diygotop['params']['gotopclick'])) { ?>href="<?php  echo $diygotop['params']['linkurl'];?>"<?php  } else { ?> id="gotop"<?php  } ?> >
    <?php  if(empty($diygotop['params']['gotoptype'])) { ?>
        <div style="background: <?php echo empty($diygotop['style']['background'])?'#000000':$diygotop['style']['background']?>; opacity: <?php echo empty($diygotop['style']['opacity'])?'0.5':$diygotop['style']['opacity']?>; line-height: <?php  echo $diygotop['style']['width']+2?>px; text-align: center; border-radius: <?php  echo $diygotop['style']['width'];?>px; height: <?php  echo $diygotop['style']['width'];?>px; width: <?php  echo $diygotop['style']['width'];?>px;">
            <i class="icon <?php echo empty($diygotop['params']['iconclass'])?'icon-top1':$diygotop['params']['iconclass']?>" style="color: <?php echo empty($diygotop['style']['iconcolor'])?'icon-top1':$diygotop['style']['iconcolor']?>; font-size: <?php  echo $diygotop['style']['width']-10?>px;"></i>
        </div>
    <?php  } else { ?>
        <img src="<?php  echo tomedia($diygotop['params']['imgurl'])?>" style="height: auto; width: 100%; display: block;" />
    <?php  } ?>
</a>

<?php  if(empty($diygotop['params']['gotopclick'])) { ?>
<script>
    $(function () {
        <?php  if(!empty($diygotop['params']['gotopheight'])) { ?>
        $(".fui-content").bind('scroll resize', function () {
            var scrolltop = $(".fui-content").scrollTop();
            if (scrolltop > "<?php  echo intval($diygotop['params']['gotopheight'])?>") {
                $("#gotop").fadeIn(100)
            } else {
                $("#gotop").fadeOut(100)
            }
        });
        <?php  } ?>
        $("#gotop").unbind('click').click(function () {
            $(".fui-content").animate({scrollTop: "0px"}, 1000)
        });
    });
</script>
<?php  } ?>