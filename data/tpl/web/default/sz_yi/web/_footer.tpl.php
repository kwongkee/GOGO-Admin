<?php defined('IN_IA') or exit('Access Denied');?> <script language='javascript'>
		require(['bootstrap'], function ($) {
			$('.btn,.tip').each(function(){
				
				if( $(this).closest('td').css('position')=='relative'){
					return true;
				}
				$(this).hover(function () {
					$(this).tooltip('show');
				}, function () {
					$(this).tooltip('hide');
				});
			})
			
		});
                   $('.js-clip').each(function(){
			util.clip(this, $(this).attr('data-url'));
		});
</script>
<script type="text/javascript">
    require(['bootstrap']);
    <?php  if($_W['isfounder'] && !defined('IN_MESSAGE')) { ?>
   
    <?php  } ?>
</script>

