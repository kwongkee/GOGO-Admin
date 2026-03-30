<?php defined('IN_IA') or exit('Access Denied');?><script type="text/javascript" src="./resource/js/lib/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="../addons/sz_yi/static/js/dist/jquery.gcjs.js"></script>
<script type="text/javascript" src="../addons/sz_yi/static/js/dist/jquery.form.js"></script>
<script type="text/javascript" src="../addons/sz_yi/static/js/dist/tooltipbox.js"></script>
<style type="text/css">
.red {float:left;color:red}
.white{float:left;color:#fff}

.tooltipbox {
	background:#fef8dd;border:1px solid #c40808; position:absolute; left:0;top:0; text-align:center;height:20px;
	color:#c40808;padding:2px 5px 1px 5px; border-radius:3px;z-index:1000;
}
.red { float:left;color:red}
</style> 
<script language='javascript'>
    function preview_html(txt)
{
var win = window.open("", "win", "width=300,height=600"); // a window object
win.document.open("text/html", "replace");
win.document.write($(txt).val());
win.document.close();
}
</script>
<div class="topmenubg_header">
<div class="topmenu_header">
    <ul class="nav navbar-nav">
        <li style="margin-right:40px">
            <a class="dropdown-toggle" href="<?php  echo url('home/welcome/ext',array('m'=>'sz_yi'))?>"><i class='iconfont icon-heart'></i> <?php  echo $this->module['title']?></a> 

        </li> 
			<?php if(cv('shop.goods.view')) { ?><li <?php  if(($_GPC['p'] == 'goods' || empty($_GPC['p'])) && $_GPC['do'] != 'order') { ?> class="active" <?php  } ?>><a href="<?php  echo $this->createWebUrl('shop/goods')?>" class="dropdown-toggle">商品</a></li><?php  } ?>
        </li>
      <?php  if(p('commission')) { ?>
  			<?php if(cp('commission')) { ?>
          <?php if(cv('commission')) { ?>
  				 <li <?php  if($_GPC['p'] == 'commission') { ?> class="active" <?php  } ?>>
  					<a href='<?php  echo $this->createPluginWebUrl('commission')?>' class="dropdown-toggle" > <span>分销</span> <i class="fa fa-angle-down"></i></a>
  				</li>
          <?php  } ?> 
  			<?php  } ?>
      <?php  } ?>
			<?php  if(p('bonus')) { ?>
        <?php if(cp('bonus')) { ?>
          <?php if(cv('bonus')) { ?>
				 <li <?php  if($_GPC['p'] == 'bonus') { ?> class="active" <?php  } ?>>
					<a href='<?php  echo $this->createPluginWebUrl('bonus')?>' class="dropdown-toggle" ><span>分红</span><i class="fa fa-angle-down"></i></a>
				</li>
          <?php  } ?>
				<?php  } ?>        
			<?php  } ?>

			<?php  if(p('supplier')) { ?>
        <?php  $perm_role = p('supplier')->verifyUserIsSupplier($_W['uid'])?>
        <?php  if(empty($perm_role)) { ?>
        <?php if(cp('supplier')) { ?>
          <?php if(cp('supplier')) { ?>
         <li <?php  if($_GPC['p'] == 'supplier') { ?> class="active" <?php  } ?>>
          <a href='<?php  echo $this->createPluginWebUrl('supplier')?>' class="dropdown-toggle" ><span>供应商</span><i class="fa fa-angle-down"></i></a>
        </li>
          <?php  } ?>
        <?php  } ?> 
        <?php  } ?>       
      <?php  } ?>

		<?php if(cv('member')) { ?>
		<li <?php  if($_GPC['do'] == 'member') { ?> class="active" <?php  } ?>>
            <a href='<?php  echo $this->createWebUrl('member/list')?>' class="dropdown-toggle" > 会员 <i class="fa fa-angle-down"></i></a>
        </li> 
        <?php  } ?>

         <?php if(cv('order')) { ?>
		<li <?php  if($_GPC['do'] == 'order') { ?> class="active" <?php  } ?>>
            <a href='<?php  echo $this->createWebUrl('order', array('op' => 'display'))?>' class="dropdown-toggle" > 订单 <i class="fa fa-angle-down"></i></a>
        </li>
        <?php  } ?>
         <?php if(cv('finance')) { ?>
		<li <?php  if($_GPC['do'] == 'finance') { ?> class="active" <?php  } ?>>
            <a href='<?php  echo $this->createWebUrl('finance/log',array('type'=>0))?>' class="dropdown-toggle" > 财务 <i class="fa fa-angle-down"></i></a>
        </li> 
        <?php  } ?> 
        
        <?php if(cv('statistics')) { ?>
		<li <?php  if($_GPC['do'] == 'statistics') { ?> class="active" <?php  } ?>>
            <a href='<?php  echo $this->createWebUrl('statistics/sale')?>' class="dropdown-toggle" > 数据 <i class="fa fa-angle-down"></i></a>
        </li>
        <?php  } ?>                   
        <?php  if($_W['role'] != 'operator') { ?>
        <li class="dropdown">
          <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" style="display:block; max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; "><i class="fa fa-group"></i><?php  echo $_W['account']['name'];?> <b class="caret"></b></a>
          <ul class="dropdown-menu">
            <li><a href="<?php  echo url('account/post', array('uniacid' => $_W['uniacid']));?>" target="_blank"><i class="fa fa-weixin fa-fw"></i> 编辑当前账号资料</a></li>
            <li><a href="<?php  echo url('account/display');?>" target="_blank"><i class="fa fa-cogs fa-fw"></i> 管理其它公众号</a></li>
            <li><a href="<?php  echo url('utility/emulator');?>" target="_blank"><i class="fa fa-mobile fa-fw"></i> 模拟测试</a></li>
          </ul>
        </li>
        <?php  } ?>
        <li class="dropdown" style="float:right;margin-right:15px">
          <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" style="display:block; max-width:185px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; "><i class="fa fa-user"></i><?php  echo $_W['user']['username'];?> (<?php  if($_W['role'] == 'founder') { ?>系统管理员<?php  } else if($_W['role'] == 'manager') { ?>公众号管理员<?php  } else { ?>公众号操作员<?php  } ?>) <b class="caret"></b></a>
          <ul class="dropdown-menu">
            <?php  if($_W['role'] != 'operator') { ?>
            <li><a href="<?php  echo url('user/profile/profile');?>" target="_blank"><i class="fa fa-weixin fa-fw"></i> 我的账号</a></li>
            <li class="divider"></li>
            <li><a href="<?php  echo url('system/welcome');?>" target="_blank"><i class="fa fa-sitemap fa-fw"></i> 系统选项</a></li>
            <li><a href="<?php  echo url('system/welcome');?>" target="_blank"><i class="fa fa-cloud-download fa-fw"></i> 自动更新</a></li>
            <li><a href="<?php  echo url('system/updatecache');?>" target="_blank"><i class="fa fa-refresh fa-fw"></i> 更新缓存</a></li>
            <li class="divider"></li>
            <?php  } ?>
              <?php  if(empty($perm_role)) { ?>
            <li><a href="<?php  echo url('user/logout');?>"><i class="fa fa-sign-out fa-fw"></i> 退出系统</a></li>
              <?php  } else { ?>
              <li><a href="<?php  echo $this->createWebUrl('order/logout')?>"><i class="fa fa-sign-out fa-fw"></i> 退出系统</a></li>
              <?php  } ?>
          </ul>
        </li>
    </ul>
</div>
</div>
