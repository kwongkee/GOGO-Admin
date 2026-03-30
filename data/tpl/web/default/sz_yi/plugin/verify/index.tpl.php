<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_header', TEMPLATE_INCLUDEPATH)) : (include template('web/_header', TEMPLATE_INCLUDEPATH));?>
<div class="w1200 m0a">
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/shop/dispatch_tabs', TEMPLATE_INCLUDEPATH)) : (include template('web/shop/dispatch_tabs', TEMPLATE_INCLUDEPATH));?>
<div class="rightlist">
<div class="main">
    <form id="dataform" action="" method="post" class="form-horizontal form">
        <div class="panel panel-default">
            <div class="panel-heading">
                核销设置
            </div>
            <div class="panel-body">
                <div class="form-group">
                   <label class="col-xs-12 col-sm-3 col-md-2 control-label">核销关键词</label>
                   <div class="col-sm-9 col-xs-12">
                       <input type="text" name="data[verifykeyword]" class="form-control" value="<?php  echo $set['verifykeyword'];?>" />
                       <span class='help-block'>店员核销使用，如果不填写默认为核销，使用方法: 回复关键词后系统会提示输入消费码</span>
                   </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">LBS页面入口</label>
                    <div class="col-sm-9 col-xs-12">
                        <p class='form-control-static'><a href='javascript:;' title='点击复制连接' id='cp'>
                            <?php  echo $this->createPluginMobileUrl('verify/store_index')?>
                        </a>
                        </p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">LBS页面名称</label>
                    <div class="col-sm-9 col-xs-12">
                        <input type="text" name="data[lbs_title]" class="form-control" value="<?php  if(empty($set['lbs_title'])) { ?>门店聚合页面<?php  } else { ?><?php  echo $set['lbs_title'];?><?php  } ?>" />
                    </div>
                </div>
                <div class="form-group">
                   <label class="col-xs-12 col-sm-3 col-md-2 control-label">是否发送消费码</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if( ce('verify.store' ,$set) ) { ?>
                        <label class='radio-inline'>
                            <input type='radio' name='data[sendcode]' value='1' <?php  if($set['sendcode']==1) { ?>checked<?php  } ?> /> 是
                        </label>
                        <label class='radio-inline'>
                            <input type='radio' name='data[sendcode]' value='0' <?php  if($set['sendcode']==0) { ?>checked<?php  } ?> /> 否
                        </label>
                        <?php  } else { ?>
                        <div class='form-control-static'><?php  if($set['sendcode']==1) { ?>是<?php  } else { ?>否<?php  } ?></div>
                        <?php  } ?>
                        <span class='help-block'>以短信形式发送消费码，需要在短信设置里面填写短信账号！</span>
                    </div>

                </div>
                <div class="form-group">
                   <label class="col-xs-12 col-sm-3 col-md-2 control-label">消费码短信模板</label>
                   <div class="col-sm-9 col-xs-12">
                       <input type="text" name="data[code_template]" class="form-control" value="<?php  if($set['code_template']) { ?><?php  echo $set['code_template'];?><?php  } else { ?>提醒您，您的核销码为：%s，订购的票型是：%s，数量：%s张，购票人：%s，电话：%s，门店电话：%s。请妥善保管，验票使用！<?php  } ?>" />
                       <span class='help-block'>注意：如不填写则模板默认为输入框中内容 ，其中“ %s ”为消费码变量，请勿更改，所有标点符号均为全角符号，模板中不能有空格！并且此模板必须和短信模板保持一致！另外此模板只支持“互亿无线”使用！</span>
                   </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">会员中心是否显示排行榜</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if( ce('verify.store' ,$set) ) { ?>
                        <label class='radio-inline'>
                            <input type='radio' name='data[centershow]' value='1' <?php  if($set['centershow']==1) { ?>checked<?php  } ?> /> 是
                        </label>
                        <label class='radio-inline'>
                            <input type='radio' name='data[centershow]' value='0' <?php  if($set['centershow']==0) { ?>checked<?php  } ?> /> 否
                        </label>
                        <?php  } else { ?>
                        <div class='form-control-static'><?php  if($set['centershow']==1) { ?>是<?php  } else { ?>否<?php  } ?></div>
                        <?php  } ?>
                        <span class='help-block'>决定会员中心是否显示门店销售排行榜！</span>
                    </div>

                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">商城下单是否走门店库存</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if( ce('verify.store' ,$set) ) { ?>
                        <label class='radio-inline'>
                            <input type='radio' name='data[store_total]' value='1' <?php  if($set['store_total']==1) { ?>checked<?php  } ?> /> 是
                        </label>
                        <label class='radio-inline'>
                            <input type='radio' name='data[store_total]' value='0' <?php  if($set['store_total']==0) { ?>checked<?php  } ?> /> 否
                        </label>
                        <?php  } else { ?>
                        <div class='form-control-static'><?php  if($set['store_total']==1) { ?>是<?php  } else { ?>否<?php  } ?></div>
                        <?php  } ?>
                        <span class='help-block'>决定商城下单时走门店库存还是平台库存！</span>
                    </div>

                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">广告1</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if( ce('verify.store' ,$set) ) { ?>
                        <?php  echo tpl_form_field_image('data[advtitle1]', $set['advtitle1'])?>
                        <span class="help-block">建议尺寸: 188*67</span>
                        <?php  } else { ?>
                        <?php  if(!empty($set['advtitle1'])) { ?>
                        <a href='<?php  echo tomedia($set['advtitle1'])?>' target='_blank'>
                        <img src="<?php  echo tomedia($set['advtitle1'])?>" style='width:100px;border:1px solid #ccc;padding:1px' />
                        </a>
                        <?php  } ?>
                        <?php  } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">广告1链接</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if( ce('shop.category' ,$set) ) { ?>
                        <div class="input-group ">
                            <input class="form-control" type="text" data-id="PAL-0007" placeholder="请填写指向的链接 (请以http://开头, 不填则不显示)" value="<?php  echo $set['advurl1'];?>" name="data[advurl1]">
                            <span class="input-group-btn">
                                    <button class="btn btn-default nav-link" type="button" data-id="PAL-0007" >选择链接</button>
                                </span>
                        </div>
                        <?php  } else { ?>
                        <div class='form-control-static'><?php  echo $set['advurl1'];?></div>
                        <?php  } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">广告2</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if( ce('verify.store' ,$set) ) { ?>
                        <?php  echo tpl_form_field_image('data[advtitle2]', $set['advtitle2'])?>
                        <span class="help-block">建议尺寸: 188*67</span>
                        <?php  } else { ?>
                        <?php  if(!empty($set['advtitle2'])) { ?>
                        <a href='<?php  echo tomedia($set['advtitle2'])?>' target='_blank'>
                        <img src="<?php  echo tomedia($set['advtitle2'])?>" style='width:100px;border:1px solid #ccc;padding:1px' />
                        </a>
                        <?php  } ?>
                        <?php  } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">广告2链接</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if( ce('shop.category' ,$set) ) { ?>
                        <div class="input-group ">
                            <input class="form-control" type="text" data-id="PAL-0008" placeholder="请填写指向的链接 (请以http://开头, 不填则不显示)" value="<?php  echo $set['advurl3'];?>" name="data[advurl3]">
                            <span class="input-group-btn">
                                    <button class="btn btn-default nav-link" type="button" data-id="PAL-0008" >选择链接</button>
                                </span>
                        </div>
                        <?php  } else { ?>
                        <div class='form-control-static'><?php  echo $set['advurl2'];?></div>
                        <?php  } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">广告3</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if( ce('verify.store' ,$set) ) { ?>
                        <?php  echo tpl_form_field_image('data[advtitle3]', $set['advtitle3'])?>
                        <span class="help-block">建议尺寸: 188*67</span>
                        <?php  } else { ?>
                        <?php  if(!empty($set['advtitle3'])) { ?>
                        <a href='<?php  echo tomedia($set['advtitle3'])?>' target='_blank'>
                        <img src="<?php  echo tomedia($set['advtitle3'])?>" style='width:100px;border:1px solid #ccc;padding:1px' />
                        </a>
                        <?php  } ?>
                        <?php  } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">广告3链接</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if( ce('verify.store' ,$set) ) { ?>
                        <div class="input-group ">
                            <input class="form-control" type="text" data-id="PAL-0009" placeholder="请填写指向的链接 (请以http://开头, 不填则不显示)" value="<?php  echo $set['advurl3'];?>" name="data[advurl3]">
                            <span class="input-group-btn">
                                    <button class="btn btn-default nav-link" type="button" data-id="PAL-0009" >选择链接</button>
                                </span>
                        </div>
                        <?php  } else { ?>
                        <div class='form-control-static'><?php  echo $set['advurl3'];?></div>
                        <?php  } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">广告4</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if( ce('verify.store' ,$set) ) { ?>
                        <?php  echo tpl_form_field_image('data[advtitle4]', $set['advtitle4'])?>
                        <span class="help-block">建议尺寸: 188*67</span>
                        <?php  } else { ?>
                        <?php  if(!empty($set['advtitle4'])) { ?>
                        <a href='<?php  echo tomedia($set['advtitle4'])?>' target='_blank'>
                        <img src="<?php  echo tomedia($set['advtitle4'])?>" style='width:100px;border:1px solid #ccc;padding:1px' />
                        </a>
                        <?php  } ?>
                        <?php  } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">广告4链接</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if( ce('verify.store' ,$set) ) { ?>
                        <div class="input-group ">
                            <input class="form-control" type="text" data-id="PAL-00010" placeholder="请填写指向的链接 (请以http://开头, 不填则不显示)" value="<?php  echo $set['advurl4'];?>" name="data[advurl4]">
                            <span class="input-group-btn">
                                    <button class="btn btn-default nav-link" type="button" data-id="PAL-00010" >选择链接</button>
                                </span>
                        </div>
                        <?php  } else { ?>
                        <div class='form-control-static'><?php  echo $set['advurl4'];?></div>
                        <?php  } ?>
                    </div>
                </div>

            </div>
            <div class="panel-heading">
                文字设置
            </div>
            <div class="panel-body">

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="col-sm-9 col-xs-12">
                        <div class="input-group">
                            <div class="input-group-addon">店长中心</div>
                            <input type="text" name="data[mastercenter]" class="form-control" value="<?php echo empty($set['mastercenter'])?'店长中心':$set['mastercenter']?>"  />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="col-sm-9 col-xs-12">
                        <div class="input-group">
                            <div class="input-group-addon">累计金额</div>
                            <input type="text" name="data[allmoney]" class="form-control" value="<?php echo empty($set['allmoney'])?'累计金额':$set['allmoney']?>"  />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="col-sm-9 col-xs-12">
                        <div class="input-group">
                            <div class="input-group-addon">可提现金额</div>
                            <input type="text" name="data[canwithdraw]" class="form-control" value="<?php echo empty($set['canwithdraw'])?'可提现金额':$set['canwithdraw']?>"  />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="col-sm-9 col-xs-12">
                        <div class="input-group">
                            <div class="input-group-addon">提现</div>
                            <input type="text" name="data[withdraw]" class="form-control" value="<?php echo empty($set['withdraw'])?'提现':$set['withdraw']?>"  />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="col-sm-9 col-xs-12">
                        <div class="input-group">
                            <div class="input-group-addon">我的钱包</div>
                            <input type="text" name="data[mypocket]" class="form-control" value="<?php echo empty($set['mypocket'])?'我的钱包':$set['mypocket']?>"  />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="col-sm-9 col-xs-12">
                        <div class="input-group">
                            <div class="input-group-addon">我的订单</div>
                            <input type="text" name="data[myorder]" class="form-control" value="<?php echo empty($set['myorder'])?'我的订单':$set['myorder']?>"  />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="col-sm-9 col-xs-12">
                        <div class="input-group">
                            <div class="input-group-addon">明细</div>
                            <input type="text" name="data[details]" class="form-control" value="<?php echo empty($set['details'])?'明细':$set['details']?>"  />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="col-sm-9 col-xs-12">
                        <div class="input-group">
                            <div class="input-group-addon">自选商品</div>
                            <input type="text" name="data[choosemyself]" class="form-control" value="<?php echo empty($set['choosemyself'])?'自选商品':$set['choosemyself']?>"  />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="col-sm-9 col-xs-12">
                        <div class="input-group">
                            <div class="input-group-addon">订单总计</div>
                            <input type="text" name="data[allorderprice]" class="form-control" value="<?php echo empty($set['allorderprice'])?'订单总计':$set['allorderprice']?>"  />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="col-sm-9 col-xs-12">
                        <div class="input-group">
                            <div class="input-group-addon">重置</div>
                            <input type="text" name="data[reset]" class="form-control" value="<?php echo empty($set['reset'])?'重置':$set['reset']?>"  />
                        </div>
                    </div>
                </div>


                    <div class="form-group">
                           <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                           <div class="col-sm-9 col-xs-12">
                                 <input type="submit" name="submit"  value="保存设置" class="btn btn-primary"/>
                                 <input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
                           </div>
                    </div>
            </div>
        </div>
    </form>
</div>
</div>
</div>
<script language="JavaScript">
    require(['util'],function(u){
        $('#cp').each(function(){
            u.clip(this, $(this).text());
        });
    })
</script>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/sysset/mylink', TEMPLATE_INCLUDEPATH)) : (include template('web/sysset/mylink', TEMPLATE_INCLUDEPATH));?>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_footer', TEMPLATE_INCLUDEPATH)) : (include template('web/_footer', TEMPLATE_INCLUDEPATH));?>
