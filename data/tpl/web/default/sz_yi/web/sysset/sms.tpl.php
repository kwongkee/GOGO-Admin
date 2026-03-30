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
        <input type='hidden' name='setid' value="<?php  echo $set['id'];?>" />
        <input type='hidden' name='op' value="sms" />
        <div class="panel panel-default">
            <div class='panel-body'>  

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">短信设置</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php  
                        if(!$set['sms']['type'])
                        { 
                            $set['sms']['type'] = 1;
                        }
                        ?>
                        <?php if(cv('sysset.save.sms')) { ?>
                        <label class='radio-inline sms_type' type="1"><input type='radio' name='sms[type]' value='1' <?php  if($set['sms']['type']==1) { ?>checked<?php  } ?>/> 互亿无线</label>
                        <label class='radio-inline sms_type' type="2"><input type='radio' name='sms[type]' value='2' <?php  if($set['sms']['type']==2) { ?>checked<?php  } ?> /> 阿里大鱼(旧用户)</label>
                        <label class='radio-inline sms_type' type="3"><input type='radio' name='sms[type]' value='3' <?php  if($set['sms']['type']==3) { ?>checked<?php  } ?> /> 阿里云短信(新用户)</label>
                        <?php  } else { ?>
                        <input type="hidden" name="sms[type]" value="<?php  echo $set['sms']['type'];?>" />
                        <div class='form-control-static'> <?php  if($set['sms']['type']==1) { ?>互亿无线<?php  } else { ?><?php  if($set['sms']['type']==2) { ?>阿里大鱼(旧用户)<?php  } else { ?>阿里云短信(新用户)<?php  } ?><?php  } ?></div>
                        <?php  } ?>
                    </div>
                </div>

                <div id="sms1" <?php  if($set['sms']['type']!=1) { ?> class="hide"<?php  } ?>>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">短信账号</label>
                        <div class="col-sm-9 col-xs-12">
                            <?php if(cv('sysset.save.sms')) { ?>
                            <input type="text" name="sms[account]" class="form-control" value="<?php  echo $set['sms']['account'];?>" />
                            <?php  } else { ?>
                            <input type="hidden" name="sms[account]" value="<?php  echo $set['sms']['account'];?>"/>
                            <div class='form-control-static'><?php  echo $set['sms']['account'];?></div>
                            <?php  } ?>

                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">短信密码</label>
                        <div class="col-sm-9 col-xs-12">
                            <?php if(cv('sysset.save.sms')) { ?>
                            <input type="text" name="sms[password]" class="form-control" value="<?php  echo $set['sms']['password'];?>" />
                            <?php  } else { ?>
                            <input type="hidden" name="sms[password]" value="<?php  echo $set['sms']['password'];?>"/>
                            <div class='form-control-static'><?php  echo $set['sms']['password'];?></div>
                            <?php  } ?>
                        </div>
                    </div>
                </div>

                <div id="sms2"  <?php  if($set['sms']['type']==1) { ?> class="hide"<?php  } ?>>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                        <div class="col-sm-9 col-xs-12 alert alert-info">
                            请到 <a href='http://www.alidayu.com/' taget="_blank">阿里大鱼</a> 去申请开通,短信模板中必须包含code和product,请参考默认用户注册验证码设置。
                        </div>
                    </div>
                    <div class="form-group type2 <?php  if($set['sms']['type']==3) { ?>hide<?php  } ?>">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">AppKey:</label>
                        <div class="col-sm-9 col-xs-12">
                            <?php if(cv('sysset.save.sms')) { ?>
                            <input type="text" name="sms[appkey]" class="form-control" value="<?php  echo $set['sms']['appkey'];?>" />
                            <?php  } else { ?>
                            <input type="hidden" name="sms[appkey]" value="<?php  echo $set['sms']['appkey'];?>"/>
                            <div class='form-control-static'><?php  echo $set['sms']['appkey'];?></div>
                            <?php  } ?>

                        </div>
                    </div>
                    <div class="form-group type2 <?php  if($set['sms']['type']==3) { ?>hide<?php  } ?>">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">secret:</label>
                        <div class="col-sm-9 col-xs-12">
                            <?php if(cv('sysset.save.sms')) { ?>
                            <input type="text" name="sms[secret]" class="form-control" value="<?php  echo $set['sms']['secret'];?>" />
                            <?php  } else { ?>
                            <input type="hidden" name="sms[secret]" value="<?php  echo $set['sms']['secret'];?>"/>
                            <div class='form-control-static'><?php  echo $set['sms']['secret'];?></div>
                            <?php  } ?>
                        </div>
                    </div>
					<!-- han 20170911 新版阿里云短信 appcode -->
                    <div class="form-group type3 <?php  if($set['sms']['type']==2) { ?>hide<?php  } ?>">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">aliyun_appcode:</label>
                        <div class="col-sm-9 col-xs-12">
                            <?php if(cv('sysset.save.sms')) { ?>
                            <input type="text" name="sms[aliyun_appcode]" class="form-control" value="<?php  echo $set['sms']['aliyun_appcode'];?>" />
                            <?php  } else { ?>
                            <input type="hidden" name="sms[aliyun_appcode]" value="<?php  echo $set['sms']['aliyun_appcode'];?>"/>
                            <div class='form-control-static'><?php  echo $set['sms']['aliyun_appcode'];?></div>
                            <?php  } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">短信签名:</label>
                        <div class="col-sm-9 col-xs-12">
                            <?php if(cv('sysset.save.sms')) { ?>
                            <input type="text" name="sms[signname]"
                            class="form-control"
                            value="<?php  echo $set['sms']['signname'];?>" placeholder="例如: 注册验证" />
                            <?php  } else { ?>
                            <input type="hidden" name="sms[signname]" value="<?php  echo $set['sms']['signname'];?>"/>
                            <div class='form-control-static'><?php  echo $set['sms']['signname'];?></div>
                            <?php  } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">注册短信模板ID:</label>
                        <div class="col-sm-9 col-xs-12">
                            <?php if(cv('sysset.save.sms')) { ?>
                            <input type="text" name="sms[templateCode]" class="form-control" value="<?php  echo $set['sms']['templateCode'];?>"  placeholder="例如: SMS_5057806" />
                            <?php  } else { ?>
                            <input type="hidden" name="sms[templateCode]" value="<?php  echo $set['sms']['templateCode'];?>"/>
                            <div class='form-control-static'><?php  echo $set['sms']['templateCode'];?></div>
                            <?php  } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">注册模板变量:</label>
                        <div class="col-sm-9 col-xs-12">
<?php if(cv('sysset.save.sms')) { ?>
<textarea name="sms[product]" class="form-control">
<?php  if(!empty($set['sms']['product'])) { ?>
    <?php  echo $set['sms']['product'];?>
<?php  } else { ?>
    product=xx商城
<?php  } ?>
</textarea>
<?php  } else { ?>
    <input type="hidden" name="sms[product]" value="<?php  echo $set['sms']['product'];?>"/>
    <div class='form-control-static'><?php  echo $set['sms']['product'];?></div>
<?php  } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">找回密码短信模板ID:</label>
                        <div class="col-sm-9 col-xs-12">
                            <?php if(cv('sysset.save.sms')) { ?>
                            <input type="text" name="sms[templateCodeForget]" class="form-control" value="<?php  echo $set['sms']['templateCodeForget'];?>"  placeholder="例如: SMS_5057806" />
                            <?php  } else { ?>
                            <input type="hidden" name="sms[templateCodeForget]" value="<?php  echo $set['sms']['templateCodeForget'];?>"/>
                            <div class='form-control-static'><?php  echo $set['sms']['templateCodeForget'];?></div>
                            <?php  } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">找回密码变量:</label>
                        <div class="col-sm-9 col-xs-12">
<?php if(cv('sysset.save.sms')) { ?>
<textarea name="sms[forget]" class="form-control">
<?php  if(!empty($set['sms']['forget'])) { ?>
    <?php  echo $set['sms']['forget'];?>
<?php  } else { ?>
    product=xx商城
<?php  } ?>
</textarea>
<?php  } else { ?>
    <input type="hidden" name="sms[forget]" value="<?php  echo $set['sms']['forget'];?>"/>
    <div class='form-control-static'><?php  echo $set['sms']['forget'];?></div>
<?php  } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                        <div class="col-sm-9 col-xs-12 alert alert-info">
                            模板变量请以"变量名=变量值"形式填写,多个值请以回车换行。
                        </div>
                    </div>
                </div>
                <!--
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">用于测试短信接口的手机号</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if(cv('sysset.save.sms')) { ?>
                        <input type="text" name="sms[password]" class="form-control" value="<?php  echo $set['sms']['password'];?>" />
                        <?php  } else { ?>
                        <input type="hidden" name="sms[password]" value="<?php  echo $set['sms']['password'];?>"/>
                        <div class='form-control-static'><?php  echo $set['sms']['password'];?></div>
                        <?php  } ?>
                    </div>
                </div>
                -->
              <div class="form-group"></div>
            <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="col-sm-9 col-xs-12">
                           <?php if(cv('sysset.save.sms')) { ?>
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
<script>
$(function(){
    $('.sms_type').click(function(){
        var type = $(this).attr('type');
        $('#sms1').hide();
        $('#sms2').hide();
        if(type == 3){
            $('#sms2').removeClass('hide').show();
            $('.type2').hide();
            $('.type3').removeClass('hide').show();
        }else if(type == 2){
            $('#sms2').removeClass('hide').show();
            $('.type3').hide();
            $('.type2').removeClass('hide').show();
        }else{
            $('#sms'+type).removeClass('hide').show();
        }
	});
});
</script>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_footer', TEMPLATE_INCLUDEPATH)) : (include template('web/_footer', TEMPLATE_INCLUDEPATH));?>     
