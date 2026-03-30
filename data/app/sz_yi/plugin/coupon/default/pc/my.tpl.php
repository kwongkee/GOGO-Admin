<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('member/center', TEMPLATE_INCLUDEPATH)) : (include template('member/center', TEMPLATE_INCLUDEPATH));?>
<title>我的优惠券</title>
<link rel="stylesheet" type="text/css" href="../addons/sz_yi/plugin/coupon/template/mobile/default/images/style.css">
<style type="text/css">
    body {margin:0px; background:#eee; font-family:'微软雅黑'; -moz-appearance:none;overflow-x: hidden; }
    a {text-decoration:none;}

    .tab {height:44px; font-size:13px;background:#fff;padding-right: -2px;}
    .navco {height:44px;border-bottom:1px solid #e3e3e3;  width:33.3%;  background:#fff; color:#666; text-align:center; line-height:44px; float:left;cursor: pointer;}
    .navon { border-bottom:2px solid #ff6801;  }
    .no {height:100px; margin:50px 0px 60px; color:#ccc; font-size:12px; text-align:center;}
    #coupon_loading { padding:10px;color:#666;text-align: center;}
   .coupon_no {height:100px;  margin:50px 0px 60px; color:#ccc; font-size:12px; text-align:center;}
   .coupon_menu {height:60px; width:100%; }
   .coupon_no_nav {height:38px; background:#eee; margin:0px 3%; border:1px solid #d4d4d4; border-radius:5px; text-align:center; line-height:38px; color:#666;}
   .coupon_more { height:40px; line-height:40px; font-size:14px;color:#666;width: 380px;
    margin: 0 auto;border-radius: 5px; background-repeat:no-repeat; border:1px solid #666; text-align: center;cursor: pointer;}
</style>
<div class="rightlist">
<div class="page_topbar">
	<!--<a href="javascript:;" class="back" onclick="history.back()"><i class="fa fa-angle-left"></i></a>-->
	<div class="title"><?php  if($_W['uniacid'] == 18) { ?>My discount coupon<?php  } else { ?>我的优惠券<?php  } ?></div>
</div>

<div class="tab">
    <div class="navco <?php  if($used==0 && $past!=1) { ?>navon<?php  } ?>" onclick="location.href='<?php  echo $this->createPluginMobileUrl('coupon/my')?>'"><?php  if($_W['uniacid'] == 18) { ?>Unused<?php  } else { ?>未使用<?php  } ?></div>
    <div class="navco <?php  if($used==1 && $past!=1) { ?>navon<?php  } ?>"  onclick="location.href='<?php  echo $this->createPluginMobileUrl('coupon/my',array('used'=>1))?>'"><?php  if($_W['uniacid'] == 18) { ?>Used<?php  } else { ?>已使用<?php  } ?></div>
    <div class="navco <?php  if($past==1) { ?>navon<?php  } ?>"  onclick="location.href='<?php  echo $this->createPluginMobileUrl('coupon/my',array('past'=>1))?>'"><?php  if($_W['uniacid'] == 18) { ?>Expired<?php  } else { ?>已过期<?php  } ?></div>
</div>
<div id='container'></div>


<script id='tpl_empty' type='text/html'>
	<div class="coupon_no"><i class="fa fa-credit-card" style="font-size:100px;"></i><br><span style="line-height:18px; font-size:16px;"><?php  if($_W['uniacid'] == 18) { ?>You don't have a coupon<?php  } else { ?>您还没有优惠券<?php  } ?>~</span></div>
  <?php  if(empty($set['closecenter'])) { ?>
<div class="coupon_more" onclick="location.href = '<?php  echo $this->createPluginMobileUrl('coupon')?>'"><i class="fa fa-gift"></i> <?php  if($_W['uniacid'] == 18) { ?>Go to the coupon center to see more coupons<?php  } else { ?>赶紧去领券中心看看更多优惠券<?php  } ?>~</div>
<?php  } ?>
</script>
<script id='tpl_list' type='text/html'>
<%each list as coupon%>
<div class="coupon_item" onclick="location.href = '<?php  echo $this->createPluginMobileUrl('coupon/mydetail')?>&id=<%coupon.id%>'">
          <div class='bg cside side side-left'></div>
	<div class="cthumb" <%if coupon.thumb==''%>style="width:8px;"<%/if%>> <%if coupon.thumb!=''%><img src='<%coupon.thumb%>' /><%/if%></div>
	<div class="cinfo" >
		<div class="inner" >
			<div class="name"><%coupon.couponname%></div>
			<div class="time">
			<%if coupon.timestr==''%>
			<?php  if($_W['uniacid'] == 18) { ?>Permanent<?php  } else { ?>永久有效<?php  } ?>
			<%else%>
			<%if coupon.past%><?php  if($_W['uniacid'] == 18) { ?>Expired<?php  } else { ?>已过期<?php  } ?><%else%>
			<?php  if($_W['uniacid'] == 18) { ?>Valid period<?php  } else { ?>有效期<?php  } ?> <%coupon.timestr%>
			<%/if%>
			<%/if%></div>
		</div>
	</div>
	 <div class="cright"> 
		   <div class="bg png png-<%coupon.css%>"></div>
		   <div class="bg sideleft side side-<%coupon.css%>"></div> 
		   <div class="rinfo" >
			   <div class='rinner <%coupon.css%>'>
				 <div class="price"><%if coupon.backpre%>￥<%/if%><span><%coupon._backmoney%></span></div>
				 <div class="type"><%coupon.backstr%></div> 
			   </div> 
		 </div>
		   <div class="bg sideright side side-<%coupon.css%>"></div>
		
	</div>

</div>
<%/each%>
</script>
</div>
<script language='javascript'>
var page = 1;
  require(['core','tpl'],function(core,tpl){
	  
	 core.pjson('coupon/my', {page:page, used:'<?php  echo $_GPC['used'];?>',past:'<?php  echo $_GPC['past'];?>'}, function(json) {
                
                    if (json.result.list.length <= 0) {
                        $("#container").html(tpl('tpl_empty'));
                        return;
                    }
                    $("#container").html(tpl('tpl_list', json.result));
                  
                     var loaded = false;
                      var stop=true; 
                      $(window).scroll(function(){ 
                          if(loaded){
                              return;
                          }
                            totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop()); 
                            if($(document).height() <= totalheight){ 
                                
                                if(stop==true){ 
                                    stop=false; 
                                    $('#container').append('<div id="coupon_loading"><i class="fa fa-spinner fa-spin"></i> <?php  if($_W['uniacid'] == 18) { ?>Loading<?php  } else { ?>正在加载<?php  } ?>...</div>');
                                    page++;
                                    core.pjson('coupon/my', {page:page, used: '<?php  echo $_GPC['used'];?>',past:'<?php  echo $_GPC['past'];?>'}, function(morejson) {  
                                        stop = true;
                                        $('#coupon_loading').remove();
                                        $("#container").append(tpl('tpl_list', morejson.result));
                                        if (morejson.result.list.length <morejson.result.pagesize) {
                                            $('#container').append('<div id="coupon_loading"><?php  if($_W['uniacid'] == 18) { ?>All coupons have been loaded<?php  } else { ?>已经加载全部优惠券<?php  } ?></div>');
                                            loaded = true;
                                            return;
                                        }
                                    },true); 
                                } 
                            } 
                        });
                }, true);
    });			
 
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/bottom', TEMPLATE_INCLUDEPATH)) : (include template('common/bottom', TEMPLATE_INCLUDEPATH));?>
