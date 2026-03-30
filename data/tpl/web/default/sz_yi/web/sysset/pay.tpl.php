<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_header', TEMPLATE_INCLUDEPATH)) : (include template('web/_header', TEMPLATE_INCLUDEPATH));?>
<div class="w1200 m0a">
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/sysset/tabs', TEMPLATE_INCLUDEPATH)) : (include template('web/sysset/tabs', TEMPLATE_INCLUDEPATH));?>
<div class="main rightlist">
<!-- 新增加右侧顶部三级菜单 -->
<div class="right-titpos">
	<ul class="add-snav">
		<li class="active"><a href="#">支付设置</a></li>
	</ul>
</div>
<!-- 新增加右侧顶部三级菜单结束 -->
    <form action="" method="post" class="form-horizontal form" enctype="multipart/form-data" >
    <form action="" method="post" class="form-horizontal form" enctype="multipart/form-data" >
        <input type='hidden' name='setid' value="<?php  echo $set['id'];?>" />
        <input type='hidden' name='op' value="pay" />
        <div class="panel panel-default"> 
            <div class='alert alert-info'>
                在开启以下支付方式前，请到 <a href='<?php  echo url('profile/payment')?>'>支付选项</a> 去设置好参数。
            </div>
            <div class="alert alert-warning">
                易宝支付，含银联，信用卡等多种支付方式, PC版支付成功后台通知请登录商户后台添加通知地址,<a href="http://www.yeepay.com/" target="_blank">申请及详情请查看这里</a>.
            </div>
        
        <!-- weixin支付设置 _start -->
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">微信支付</label>
                <div class="col-sm-9 col-xs-12">
                    <?php if(cv('sysset.save.pay')) { ?>
                    <label class='radio-inline'><input type='radio' name='pay[weixin]' value='1' <?php  if($set['pay']['weixin']==1) { ?>checked<?php  } ?>/> 开启</label>
                    <label class='radio-inline'><input type='radio' name='pay[weixin]' value='0' <?php  if($set['pay']['weixin']==0) { ?>checked<?php  } ?> /> 关闭</label>
                    <?php  } else { ?>
                    <input type="hidden" name="pay[weixin]" value="<?php  echo $set['pay']['weixin'];?>" />
                    <div class='form-control-static'> <?php  if($set['pay']['weixin']==1) { ?>开启<?php  } else { ?>关闭<?php  } ?></div>
                    <?php  } ?>
                </div>
            </div>
            <div id='certs' <?php  if(empty($set['pay']['weixin'])) { ?>style="display:none"<?php  } ?>>
                 <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">CERT证书文件</label>
                    <div class="col-sm-9 col-xs-12">
                        <input type="hidden" name="pay[weixin_cert]" value="<?php  echo $set['pay']['weixin_cert'];?>"/>
                        <?php if(cv('sysset.save.pay')) { ?>

                        <input type="file" name="weixin_cert_file" class="form-control" />
                        <span class="help-block">
                            <?php  if(!empty($sec['cert'])) { ?>
                            <span class='label label-success'>已上传</span>
                            <?php  } else { ?>
                            <span class='label label-danger'>未上传</span>
                            <?php  } ?>
                            下载证书 cert.zip 中的 apiclient_cert.pem 文件</span>
                        <?php  } else { ?>
                       <?php  if(!empty($sec['cert'])) { ?>
                        <span class='label label-success'>已上传</span>
                        <?php  } else { ?>
                        <span class='label label-danger'>未上传</span>
                        <?php  } ?>
                        <?php  } ?>

                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">KEY密钥文件</label>
                    <div class="col-sm-9 col-xs-12">
                        <input type="hidden" name="pay[weixin_key]"  value="<?php  echo $set['pay']['weixin_key'];?>"/>
                        <?php if(cv('sysset.save.pay')) { ?>

                        <input type="file" name="weixin_key_file" class="form-control" />
                        <span class="help-block">
                           <?php  if(!empty($sec['key'])) { ?>
                            <span class='label label-success'>已上传</span>
                            <?php  } else { ?>
                            <span class='label label-danger'>未上传</span>
                            <?php  } ?>
                            下载证书 cert.zip 中的 apiclient_key.pem 文件
                        </span>
                        <?php  } else { ?>
                      <?php  if(!empty($sec['key'])) { ?>
                        <span class='label label-success'>已上传</span>
                        <?php  } else { ?>
                        <span class='label label-danger'>未上传</span>
                        <?php  } ?>
                        <?php  } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">ROOT文件</label>
                    <div class="col-sm-9 col-xs-12">
                        <input type="hidden" name="pay[weixin_root]" value="<?php  echo $set['pay']['weixin_root'];?>"/>
                        <?php if(cv('sysset.save.pay')) { ?>

                        <input type="file" name="weixin_root_file" class="form-control" />
                        <span class="help-block">
                          <?php  if(!empty($sec['root'])) { ?>
                            <span class='label label-success'>已上传</span>
                            <?php  } else { ?>
                            <span class='label label-danger'>未上传</span>
                            <?php  } ?>
                            下载证书 cert.zip 中的 rootca.pem 文件 
                        </span>
                        <?php  } else { ?>
                     <?php  if(!empty($sec['root'])) { ?>
                        <span class='label label-success'>已上传</span>
                        <?php  } else { ?>
                        <span class='label label-danger'>未上传</span>
                        <?php  } ?>
                        <?php  } ?>
                    </div>
                </div>
            </div>

        <!-- 借用微信支付设置 _start -->
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">借用微信支付</label>
                <div class="col-sm-9 col-xs-12">
                    <?php if(cv('sysset.save.pay')) { ?>
                    <label class='radio-inline'><input type='radio' name='pay[weixin_jie]' value='1' <?php  if($set['pay']['weixin_jie']==1) { ?>checked<?php  } ?>/> 开启</label>
                    <label class='radio-inline'><input type='radio' name='pay[weixin_jie]' value='0' <?php  if($set['pay']['weixin_jie']==0) { ?>checked<?php  } ?> /> 关闭</label>
                    <?php  } else { ?>
                    <input type="hidden" name="pay[weixin_jie]" value="<?php  echo $set['pay']['weixin_jie'];?>" />
                    <div class='form-control-static'> <?php  if($set['pay']['weixin_jie']==1) { ?>开启<?php  } else { ?>关闭<?php  } ?></div>
                    <?php  } ?>
                    <span class='help-block'>开启借号微信支付，微信支付功能将失效</span>
                </div>

            </div>
            <div id='jie' <?php  if(empty($set['pay']['weixin_jie'])) { ?>style="display:none"<?php  } ?>>
                <div class="form-group" >
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label must">公众号(AppId)</label>
                    <div class="col-sm-9">
                        <?php if(cv('sysset.pay.edit')) { ?>
                        <input type="text" name="pay[weixin_jie_appid]" class="form-control" value="<?php  echo $set['pay']['weixin_jie_appid'];?>"/>
                        <?php  } else { ?>
                        <div class='form-control-static'><?php  echo $set['pay']['weixin_jie_appid'];?></div>
                        <?php  } ?>
                    </div>
                </div>
                <div class="form-group" >
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label must">微信支付商户号(Mch_Id)</label>
                    <div class="col-sm-9">
                        <?php if(cv('sysset.pay.edit')) { ?>
                        <input type="text" name="pay[weixin_jie_mchid]" class="form-control" value="<?php  echo $set['pay']['weixin_jie_mchid'];?>"/>
                        <?php  } else { ?>
                        <div class='form-control-static'><?php  echo $set['pay']['weixin_jie_mchid'];?></div>
                        <?php  } ?>
                    </div>
                </div>
                <div class="form-group" >
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label must">微信支付密钥(APIKEY)</label>
                    <div class="col-sm-9">
                        <?php if(cv('sysset.pay.edit')) { ?>
                        <input type="text" name="pay[weixin_jie_apikey]" class="form-control" value="<?php  echo $set['pay']['weixin_jie_apikey'];?>"/>
                        <?php  } else { ?>
                        <div class='form-control-static'><?php  echo $set['pay']['weixin_jie_apikey'];?></div>
                        <?php  } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">CERT证书文件</label>
                    <div class="col-sm-9 col-xs-12">
                        <input type="hidden" name="pay[weixin_jie_cert]" value="<?php  echo $data['weixin_jie_cert'];?>"/>
                        <?php if(cv('sysset.pay.edit')) { ?>

                        <input type="file" name="weixin_jie_cert_file" class="form-control" />
                                    <span class="help-block">
                                        <?php  if(!empty($sec['jie']['cert'])) { ?>
                                        <span class='label label-success'>已上传</span>
                                        <?php  } else { ?>
                                        <span class='label label-danger'>未上传</span>
                                        <?php  } ?>
                                        下载证书 cert.zip 中的 apiclient_cert.pem 文件</span>
                        <?php  } else { ?>
                        <?php  if(!empty($sec['cert'])) { ?>
                        <span class='label label-success'>已上传</span>
                        <?php  } else { ?>
                        <span class='label label-danger'>未上传</span>
                        <?php  } ?>
                        <?php  } ?>

                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">KEY密钥文件</label>
                    <div class="col-sm-9 col-xs-12">
                        <input type="hidden" name="pay[weixin_jie_key]"  value="<?php  echo $data['weixin_jie_key'];?>"/>
                        <?php if(cv('sysset.pay.edit')) { ?>

                        <input type="file" name="weixin_jie_key_file" class="form-control" />
                                    <span class="help-block">
                                       <?php  if(!empty($sec['jie']['key'])) { ?>
                                        <span class='label label-success'>已上传</span>
                                        <?php  } else { ?>
                                        <span class='label label-danger'>未上传</span>
                                        <?php  } ?>
                                        下载证书 cert.zip 中的 apiclient_key.pem 文件
                                    </span>
                        <?php  } else { ?>
                        <?php  if(!empty($sec['key'])) { ?>
                        <span class='label label-success'>已上传</span>
                        <?php  } else { ?>
                        <span class='label label-danger'>未上传</span>
                        <?php  } ?>
                        <?php  } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">ROOT文件</label>
                    <div class="col-sm-9 col-xs-12">
                        <input type="hidden" name="pay[weixin_jie_root]" value="<?php  echo $data['weixin_jie_root'];?>"/>
                        <?php if(cv('sysset.pay.edit')) { ?>

                        <input type="file" name="weixin_jie_root_file" class="form-control" />
                                    <span class="help-block">
                                      <?php  if(!empty($sec['jie']['root'])) { ?>
                                        <span class='label label-success'>已上传</span>
                                        <?php  } else { ?>
                                        <span class='label label-danger'>未上传</span>
                                        <?php  } ?>
                                        下载证书 cert.zip 中的 rootca.pem 文件
                                    </span>
                        <?php  } else { ?>
                        <?php  if(!empty($sec['jie']['root'])) { ?>
                        <span class='label label-success'>已上传</span>
                        <?php  } else { ?>
                        <span class='label label-danger'>未上传</span>
                        <?php  } ?>
                        <?php  } ?>
                    </div>
                </div>
            </div>

<!-- paypal支付设置 _start -->

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">Paypal支付</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if(cv('sysset.save.pay')) { ?>
                        <label class='radio-inline'><input type='radio' name='pay[paypalstatus]' value='1' <?php  if($set['pay']['paypalstatus']==1) { ?>checked<?php  } ?>/> 开启</label>
                        <label class='radio-inline'><input type='radio' name='pay[paypalstatus]' value='0' <?php  if($set['pay']['paypalstatus']==0) { ?>checked<?php  } ?> /> 关闭</label>
                        <?php  } else { ?>
                        <input type="hidden" name="pay[paypalstatus]" value="<?php  echo $set['pay']['paypalstatus'];?>" />
                        <div class='form-control-static'> <?php  if($set['pay']['paypalstatus']==1) { ?>开启<?php  } else { ?>关闭<?php  } ?></div>
                        <?php  } ?>
                    </div>
                </div>
                <div id='paypal' <?php  if(empty($set['pay']['paypalstatus'])) { ?>style="display:none"<?php  } ?>>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                        <div class="col-sm-9 col-xs-12">
                            <div style="float:left; width:15%; height:30px;">
                                <label class='radio-inline'  style="padding-left:0px">商户号：</label>
                            </div>
                            <div style="float:left; width:85%; height:30px;">
                                <label class='radio-inline' style="width:70%;"><input class="col-sm-6" style="width:100%;" type="text" name="pay[paypal][mchid]" value="<?php  echo $set['pay']['paypal']['mchid'];?>"/></label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                        <div class="col-sm-9 col-xs-12">
                            <div style="float:left; width:15%; height:30px;">
                                <label class='radio-inline'  style="padding-left:0px">商户密码：</label>
                            </div>
                            <div style="float:left; width:85%; height:30px;">
                                <label class='radio-inline' style="width:70%;"><input class="col-sm-6" style="width:100%;" type="text" name="pay[paypal][key]" value="<?php  echo $set['pay']['paypal']['key'];?>"/></label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                        <div class="col-sm-9 col-xs-12">
                            <div style="float:left; width:15%; height:30px;">
                                <label class='radio-inline'  style="padding-left:0px">商户支付密钥：</label>
                            </div>
                            <div style="float:left; width:85%; height:30px;">
                                <label class='radio-inline' style="width:70%;"><input class="col-sm-6" style="width:100%;" type="text" name="pay[paypal][signkey]" value="<?php  echo $set['pay']['paypal']['signkey'];?>"/></label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                        <div class="col-sm-9 col-xs-12">
                            <div style="float:left; width:15%; height:30px;">
                                <label class='radio-inline'  style="padding-left:0px">支付币种：</label>
                            </div>
                            <div style="float:left; width:85%; height:30px;">
                                <label class='radio-inline' style="width:70%;"><input class="col-sm-6" style="width:100%;" type="text" name="pay[paypal][currency]" value="<?php  echo $set['pay']['paypal']['currency'];?>"/></label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                        <div class="col-sm-9 col-xs-12">
                            <div style="float:left; width:15%; height:30px;">
                                <label class='radio-inline'  style="padding-left:0px">汇率：</label>
                            </div>
                            <div style="float:left; width:85%; height:30px;">
                                <label class='radio-inline' style="width:70%;"><input class="col-sm-6" style="width:100%;" type="text" name="pay[paypal][currencies]" value="<?php  echo $set['pay']['paypal']['currencies'];?>"/></label>
                            </div>
                        </div>
                    </div>

                </div>
<!-- paypal支付设置 _end -->

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">支付宝支付</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if(cv('sysset.save.pay')) { ?>
                        <label class='radio-inline'><input type='radio' name='pay[alipay]' value='1' <?php  if($set['pay']['alipay']==1) { ?>checked<?php  } ?>/> 开启</label>
                        <label class='radio-inline'><input type='radio' name='pay[alipay]' value='0' <?php  if($set['pay']['alipay']==0) { ?>checked<?php  } ?> /> 关闭</label>
                        <?php  } else { ?>
                        <input type="hidden" name="pay[alipay]" value="<?php  echo $set['pay']['alipay'];?>"/>
                        <div class='form-control-static'> <?php  if($set['pay']['alipay']==1) { ?>开启<?php  } else { ?>关闭<?php  } ?></div>
                        <?php  } ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">易宝支付</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if(cv('sysset.save.pay')) { ?>
                        <label class='radio-inline'><input type='radio' name='pay[yeepay]' value='1' <?php  if($set['pay']['yeepay']==1) { ?>checked<?php  } ?>/> 开启</label>
                        <label class='radio-inline'><input type='radio' name='pay[yeepay]' value='0' <?php  if($set['pay']['yeepay']==0) { ?>checked<?php  } ?> /> 关闭</label>
                        <?php  } else { ?>
                        <input type="hidden" name="pay[yeepay]" value="<?php  echo $set['pay']['yeepay'];?>"/>
                        <div class='form-control-static'> <?php  if($set['pay']['yeepay']==1) { ?>开启<?php  } else { ?>关闭<?php  } ?></div>
                        <?php  } ?>
                    </div>
                </div>
                <div id='yeepay_set' <?php  if(empty($set['pay']['yeepay'])) { ?>style="display:none"<?php  } ?>>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">商户编号</label>
                        <div class="col-sm-9 col-xs-12">
                            <input class="col-sm-6" type="text" name="pay[merchantaccount]" value="<?php  echo $set['pay']['merchantaccount'];?>"/>
                        </div>
                    </div>


                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">商户密钥</label>
                        <div class="col-sm-9 col-xs-12">
                            <input class="col-sm-6" type="text" name="pay[merchantKey]" value="<?php  echo $set['pay']['merchantKey'];?>"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">商户私钥</label>
                        <div class="col-sm-9 col-xs-12">
                            <input class="col-sm-6" type="text" name="pay[merchantPrivateKey]" value="<?php  echo $set['pay']['merchantPrivateKey'];?>"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">商户RSA公钥</label>
                        <div class="col-sm-9 col-xs-12">
                            <input class="col-sm-6" type="text" name="pay[merchantPublicKey]" value="<?php  echo $set['pay']['merchantPublicKey'];?>"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">易宝RSA公钥</label>
                        <div class="col-sm-9 col-xs-12">
                            <input class="col-sm-6" type="text" name="pay[yeepayPublicKey]" value="<?php  echo $set['pay']['yeepayPublicKey'];?>"/>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">余额支付</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if(cv('sysset.save.pay')) { ?>
                        <label class='radio-inline'><input type='radio' name='pay[credit]' value='1' <?php  if($set['pay']['credit']==1) { ?>checked<?php  } ?>/> 开启</label>
                        <label class='radio-inline'><input type='radio' name='pay[credit]' value='0' <?php  if($set['pay']['credit']==0) { ?>checked<?php  } ?> /> 关闭</label>
                        <?php  } else { ?>
                        <input type="hidden" name="pay[credit]" value="<?php  echo $set['pay']['credit'];?>"/>
                        <div class='form-control-static'> <?php  if($set['pay']['credit']==1) { ?>开启<?php  } else { ?>关闭<?php  } ?></div>
                        <?php  } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">货到付款</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if(cv('sysset.save.pay')) { ?>
                        <label class='radio-inline'><input type='radio' name='pay[cash]' value='1' <?php  if($set['pay']['cash']==1) { ?>checked<?php  } ?>/> 开启</label>
                        <label class='radio-inline'><input type='radio' name='pay[cash]' value='0' <?php  if($set['pay']['cash']==0) { ?>checked<?php  } ?> /> 关闭</label>
                        <?php  } else { ?>
                        <input type="hidden" name="pay[cash]" value="<?php  echo $set['pay']['cash'];?>"/>
                        <div class='form-control-static'> <?php  if($set['pay']['cash']==1) { ?>开启<?php  } else { ?>关闭<?php  } ?></div>
                        <?php  } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">支付宝提现</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if(cv('sysset.save.pay')) { ?>
                        <label class='radio-inline'><input type='radio' name='pay[alipay_withdrawals]' value='1' <?php  if($set['pay']['alipay_withdrawals']==1) { ?>checked<?php  } ?>/> 开启</label>
                        <label class='radio-inline'><input type='radio' name='pay[alipay_withdrawals]' value='0' <?php  if($set['pay']['alipay_withdrawals']==0) { ?>checked<?php  } ?> /> 关闭</label>
                        <?php  } else { ?>
                        <input type="hidden" name="pay[alipay]" value="<?php  echo $set['pay']['alipay'];?>"/>
                        <div class='form-control-static'> <?php  if($set['pay']['alipay_withdrawals']==1) { ?>开启<?php  } else { ?>关闭<?php  } ?></div>
                        <?php  } ?>
                    </div>
                </div>
                <div id='alipay_withdrawals' <?php  if(empty($set['pay']['alipay_withdrawals'])) { ?>style="display:none"<?php  } ?>>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">付款账号:</label>
                        <div class="col-sm-9 col-xs-12">
                            <input class="col-sm-6" type="text" name="pay[alipay_number]" value="<?php  echo $set['pay']['alipay_number'];?>"/>
                        </div>  
                    </div>   
                    <div class="form-group">              
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">付款账户名：</label>
                        <div class="col-sm-9 col-xs-12">
                            <input class="col-sm-6" type="text" name="pay[alipay_name]" value="<?php  echo $set['pay']['alipay_name'];?>"/>
                        </div>
                    </div>
					<!-- peng 20170323 增加支付宝信息!-->
					<div class="form-group">              
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">appid号码</label>
                        <div class="col-sm-9 col-xs-12">
                            <input class="col-sm-6" type="text" name="pay[alipay_appid]" value="<?php  echo $set['pay']['alipay_appid'];?>"/>
                        </div>
                    </div>
					<div class="form-group">              
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">公秘钥</label>
                        <div class="col-sm-9 col-xs-12">
                            <input class="col-sm-6" type="text" name="pay[alipay_publickey]" value="<?php  echo $set['pay']['alipay_publickey'];?>"/>
                        </div>
                    </div>
					<div class="form-group">              
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">私秘钥</label>
                        <div class="col-sm-9 col-xs-12">
                            <input class="col-sm-6" type="text" name="pay[alipay_privatekey]" value="<?php  echo $set['pay']['alipay_privatekey'];?>"/>
                        </div>
                    </div>
                    <!-- peng 20170323 END-->
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">微信红包提现</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if(cv('sysset.save.pay')) { ?>
                        <label class='radio-inline'><input type='radio' name='pay[weixin_withdrawals]' value='1' <?php  if($set['pay']['weixin_withdrawals']==1) { ?>checked<?php  } ?>/> 开启</label>
                        <label class='radio-inline'><input type='radio' name='pay[weixin_withdrawals]' value='0' <?php  if($set['pay']['weixin_withdrawals']==0) { ?>checked<?php  } ?> /> 关闭</label>
                        <?php  } else { ?>
                        <input type="hidden" name="pay[alipay]" value="<?php  echo $set['pay']['weixin_withdrawals'];?>"/>
                        <div class='form-control-static'> <?php  if($set['pay']['weixin_withdrawals']==1) { ?>开启<?php  } else { ?>关闭<?php  } ?></div>
                        <?php  } ?>
                    </div>
                </div>
                
<!-- 通莞支付配置 -->
<!-- tgpay支付设置 _start -->

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">聚合支付</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if(cv('sysset.save.pay')) { ?>
                        <label class='radio-inline'><input type='radio' name='pay[tgpaystatus]' value='1' <?php  if($set['pay']['tgpaystatus']==1) { ?>checked<?php  } ?>/> 开启</label>
                        <label class='radio-inline'><input type='radio' name='pay[tgpaystatus]' value='0' <?php  if($set['pay']['tgpaystatus']==0) { ?>checked<?php  } ?> /> 关闭</label>
                        <?php  } else { ?>
                        <input type="hidden" name="pay[tgpaystatus]" value="<?php  echo $set['pay']['tgpaystatus'];?>" />
                        <div class='form-control-static'> <?php  if($set['pay']['tgpaystatus']==1) { ?>开启<?php  } else { ?>关闭<?php  } ?></div>
                        <?php  } ?>
                    </div>
                </div>
                
                <div id='tgpay' <?php  if(empty($set['pay']['tgpaystatus'])) { ?>style="display:none"<?php  } ?>>                	
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                        <div class="col-sm-9 col-xs-12">
                            <div style="float:left; width:15%; height:30px;">
                                <label class='radio-inline'  style="padding-left:0px">商户号：</label>
                            </div>
                            <div style="float:left; width:85%; height:30px;">
                                <label class='radio-inline' style="width:70%;"><input class="col-sm-6" style="width:100%;" type="text" name="pay[tgpay][mchid]" value="<?php  echo $set['pay']['tgpay']['mchid'];?>" placeholder="请输入商户号"/></label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                        <div class="col-sm-9 col-xs-12">
                            <div style="float:left; width:15%; height:30px;">
                                <label class='radio-inline'  style="padding-left:0px">商户密钥：</label>
                            </div>
                            <div style="float:left; width:85%; height:30px;">
                                <label class='radio-inline' style="width:70%;"><input class="col-sm-6" style="width:100%;" type="text" name="pay[tgpay][key]" value="<?php  echo $set['pay']['tgpay']['key'];?>" placeholder="请输入商户密钥"/></label>
                            </div>
                        </div>
                    </div>
                </div>
<!-- 通莞支付设置 _end -->
                
                
	    <!-- 邦付宝跨境支付   2018-08-08-->
	    <div class="form-group">
	        <label class="col-xs-12 col-sm-3 col-md-2 control-label">新生支付</label>
	        <div class="col-sm-9 col-xs-12">
	            <?php if(cv('sysset.save.pay')) { ?>
	            <label class='radio-inline'><input type='radio' name='pay[helpaystatus]' value='1' <?php  if($set['pay']['helpaystatus']==1) { ?>checked<?php  } ?>/> 开启</label>
	            <label class='radio-inline'><input type='radio' name='pay[helpaystatus]' value='0' <?php  if($set['pay']['helpaystatus']==0) { ?>checked<?php  } ?> /> 关闭</label>
	            <?php  } else { ?>
	            <input type="hidden" name="pay[helpaystatus]" value="<?php  echo $set['pay']['helpaystatus'];?>" />
	            <div class='form-control-static'> <?php  if($set['pay']['helpaystatus']==1) { ?>开启<?php  } else { ?>关闭<?php  } ?></div>
	            <?php  } ?>
	        </div>
	    </div>
	    
	    <div id='helpay' <?php  if(empty($set['pay']['helpaystatus'])) { ?>style="display:none"<?php  } ?>>
	        <div class="form-group">
	            <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
	            <div class="col-sm-9 col-xs-12">
	                <div style="float:left; width:15%; height:30px;">
	                    <label class='radio-inline'  style="padding-left:0px">商户号：</label>
	                </div>
	                <div style="float:left; width:85%; height:30px;">
	                    <label class='radio-inline' style="width:70%;"><input class="col-sm-6" style="width:100%;" type="text" name="pay[helpay][mchid]" value="<?php  echo $set['pay']['helpay']['mchid'];?>" placeholder="请输入商户号"/></label>
	                </div>
	            </div>
	        </div>
	        
	        <div class="form-group">
	            <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
	            <div class="col-sm-9 col-xs-12">
	                <div style="float:left; width:15%; height:30px;">
	                    <label class='radio-inline'  style="padding-left:0px">商户密钥：</label>
	                </div>
	                <div style="float:left; width:85%; height:30px;">
	                    <label class='radio-inline' style="width:70%;"><input class="col-sm-6" style="width:100%;" type="text" name="pay[helpay][key]" value="<?php  echo $set['pay']['helpay']['key'];?>" placeholder="请输入商户密钥"/></label>
	                </div>
	            </div>
	        </div>
	    </div>
		<!-- 邦付宝跨境支付-->
                
                
         <div class="form-group"></div>
            <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="col-sm-9 col-xs-12">
                           <?php if(cv('sysset.save.pay')) { ?>
                            <input type="submit" name="submit" value="提交" class="btn btn-primary col-lg-1"  />
                            <input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
                          <?php  } ?>
                     </div>
            </div>

            </div>
            <script language="javascript">
                $(function () {
                    $(":radio[name='pay[weixin]']").click(function () {
                        if ($(this).val() == 1) {
                            $("#certs").show();
                        }
                        else {
                            $("#certs").hide();
                        }
                    })
                    $(":radio[name='pay[weixin_jie]']").click(function () {
                        if ($(this).val() == 1) {
                            $("#jie").show();
                        }
                        else {
                            $("#jie").hide();
                        }
                    })
                    $(":radio[name='pay[paypalstatus]']").click(function () {
                        if ($(this).val() == 1) {
                            $("#paypal").show();
                        }
                        else {
                            $("#paypal").hide();
                        }
                    })
                    $(":radio[name='pay[yeepay]']").click(function () {
                        if ($(this).val() == 1) {
                            $("#yeepay_set").show();
                        }
                        else {
                            $("#yeepay_set").hide();
                        }
                    })

                    $(":radio[name='pay[alipay_withdrawals]']").click(function () {
                        if ($(this).val() == 1) {
                            $("#alipay_withdrawals").show();
                        }
                        else {
                            $("#alipay_withdrawals").hide();
                        }
                    })
                    $(":radio[name='pay[tgpaystatus]']").click(function () {
                        if ($(this).val() == 1) {
                            $("#tgpay").show();
                        }
                        else {
                            $("#tgpay").hide();
                        }
                    })
                    //邦付宝配置开启关闭
                    $(":radio[name='pay[helpaystatus]']").click(function () {
                        if ($(this).val() == 1) {
                            $("#helpay").show();
                        }
                        else {
                            $("#helpay").hide();
                        }
                    })

                })
            </script>
        </div>     
    </form>
</div>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_footer', TEMPLATE_INCLUDEPATH)) : (include template('web/_footer', TEMPLATE_INCLUDEPATH));?>     
