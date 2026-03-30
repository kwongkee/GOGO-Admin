<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_header', TEMPLATE_INCLUDEPATH)) : (include template('web/_header', TEMPLATE_INCLUDEPATH));?>
<div class="w1200 m0a">
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/sysset/tabs', TEMPLATE_INCLUDEPATH)) : (include template('web/sysset/tabs', TEMPLATE_INCLUDEPATH));?>
<div class="main rightlist">
<!-- 新增加右侧顶部三级菜单 -->
<div class="right-titpos">
	<ul class="add-shopnav">
    <?php if(cv('sysset.view.shop')) { ?><li <?php  if($_GPC['op']=='shop') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('sysset',array('op'=>'shop'))?>">商城设置</a></li><?php  } ?>
    <?php if(cv('shop.notice.view')) { ?><li <?php  if($_GPC['p'] == 'notice') { ?> class="active" <?php  } ?>><a href="<?php  echo $this->createWebUrl('shop/notice')?>">公告管理</a></li><?php  } ?>
    <?php if(cv('shop.adpc.view')) { ?><li <?php  if($_GPC['p'] == 'adpc') { ?> class="active" <?php  } ?>><a href="<?php  echo $this->createWebUrl('shop/adpc')?>">广告管理</a></li><?php  } ?>
    <?php if(cv('sysset.view.member')) { ?><li  <?php  if($_GPC['op']=='member') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('sysset',array('op'=>'member'))?>">会员设置</a></li><?php  } ?>
    <?php if(cv('sysset.view.template')) { ?><li  <?php  if($_GPC['op']=='template') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('sysset',array('op'=>'template'))?>">模板设置</a></li><?php  } ?>
    <?php if(cv('shop.adv.view')) { ?><li <?php  if($_GPC['p'] == 'adv') { ?> class="active" <?php  } ?>><a href="<?php  echo $this->createWebUrl('shop/adv')?>">幻灯片管理</a></li><?php  } ?>
    <?php if(cv('sysset.view.category')) { ?><li  <?php  if($_GPC['op']=='category') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('sysset',array('op'=>'category'))?>">分类层级</a></li><?php  } ?>
    <?php if(cv('sysset.view.contact')) { ?><li  <?php  if($_GPC['op']=='contact') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('sysset',array('op'=>'contact'))?>">联系方式</a></li><?php  } ?>
    <?php if(cv('sysset.view.sms')) { ?><li  <?php  if($_GPC['op']=='sms') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('sysset',array('op'=>'sms'))?>">短信设置</a></li><?php  } ?>
	</ul>
</div>
<!-- 新增加右侧顶部三级菜单结束 -->
    <form action="" method="post" class="form-horizontal form" enctype="multipart/form-data" >
    <form action="" method="post" class="form-horizontal form" enctype="multipart/form-data" >
        <input type='hidden' name='setid' value="<?php  echo $set['id'];?>" />
        <input type='hidden' name='op' value="template" />
        <div class="panel panel-default">
            <div class='panel-body'>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">模板选择</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if(cv('sysset.save.template')) { ?>
                        <select class='form-control' name='shop[style]'>
                            <?php  if(is_array($styles)) { foreach($styles as $style) { ?>
                            <option value='<?php  echo $style;?>' <?php  if($style==$set['shop']['style']) { ?>selected<?php  } ?>><?php  echo $style;?></option>
                            <?php  } } ?>
                        </select>
                        <?php  } else { ?>
                        <input type="hidden" name="shop[style]" value="<?php  echo $set['shop']['style'];?>"/>
                        <div class='form-control-static'>
                            <?php  if(empty($set['shop']['style'])) { ?>default<?php  } else { ?><?php  echo $set['shop']['style'];?><?php  } ?>
                        </div>
                        <?php  } ?>
                    </div>
                </div> 
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">PC模板选择</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if(cv('sysset.save.template')) { ?>
                        <select class='form-control' name='shop[style_pc]'>
                            <?php  if(is_array($styles_pc)) { foreach($styles_pc as $style_pc) { ?>
                            <option value='<?php  echo $style_pc;?>' <?php  if($style_pc==$set['shop']['style_pc']) { ?>selected<?php  } ?>><?php  echo $style_pc;?></option>
                            <?php  } } ?>
                        </select>
                        <?php  } else { ?>
                        <input type="hidden" name="shop[style_pc]" value="<?php  echo $set['shop']['style_pc'];?>"/>
                        <div class='form-control-static'>
                            <?php  if(empty($set['shop']['style_pc'])) { ?>default<?php  } else { ?><?php  echo $set['shop']['style_pc'];?><?php  } ?>
                        </div>
                        <?php  } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">主题选择</label>
                    <div class="col-sm-9 col-xs-12">
                        <select class='form-control' name='shop[theme]'>
                            <option value='style' <?php  if('style'==$set['shop']['theme']) { ?>selected<?php  } ?>>默认主题</option>
                            <option value='style_red' <?php  if('style_red'==$set['shop']['theme']) { ?>selected<?php  } ?>>红黑主题</option>
                        </select>
                    </div>
                </div> 

                       <div class="form-group"></div>
            <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="col-sm-9 col-xs-12">
                           <?php if(cv('sysset.save.template')) { ?>
                            <input type="submit" name="submit" value="提交" class="btn btn-primary col-lg-1"  />
                            <input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
                          <?php  } ?>
                     </div>
            </div>
                       
            </div>

        </div>     
    </form>
</div>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_footer', TEMPLATE_INCLUDEPATH)) : (include template('web/_footer', TEMPLATE_INCLUDEPATH));?>     
