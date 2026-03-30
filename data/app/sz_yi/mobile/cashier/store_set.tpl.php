<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>收银台</title>
<style type="text/css">
    body {margin:0px; background:#efefef; font-family:'微软雅黑'; -moz-appearance:none;}
    .info_main {height:auto;  background:#fff; margin-top:14px; border-bottom:1px solid #e8e8e8; border-top:1px solid #e8e8e8;}
    .info_main .line {margin:0 10px; height:40px; border-bottom:1px solid #e8e8e8; line-height:40px; color:#999;}
    .info_main .line .title {height:40px; width:160px; line-height:40px; color:#444; float:left; font-size:16px;}
    .info_main .line .info { width:100%;float:right;margin-left:-160px; }
    .info_main .line .inner { margin-left:160px; }
    .info_main .line .inner input {height:40px; width:100%;display:block; padding:0px; margin:0px; border:0px; float:left; font-size:16px;}
    .info_main .line .inner .user_sex {line-height:40px;}
    .info_sub {height:44px; margin:14px 5px; background:#31cd00; border-radius:4px; text-align:center; font-size:16px; line-height:44px; color:#fff;}
    .select { border:1px solid #ccc;height:25px;}
    .cashier_topbar {height:44px; width:100%; background:#fff; border-bottom:1px solid #e3e3e3;}
    .cashier_topbar .nav {height:44px; width:33%; line-height:44px; text-align:center; font-size:14px; float:left; color:#666;}
    .cashier_topbar .on {height:42px; color:#ff771b; border-bottom:2px solid #ff771b;}
    .create_qrcode {height:44px; margin:14px 5px; background:#ff771b; border-radius:4px; text-align:center; font-size:16px; line-height:44px; color:#fff;}

</style>
<div id="container"></div>
<script id="store_info" type="text/html">
    <div class="page_topbar">
        <a href="javascript:;" class="back" onclick="history.back()"><i class="fa fa-angle-left"></i></a>
        <div class="title">收银台</div>
    </div>
    <div class="cashier_topbar">
        <div class="nav <?php  if($page=='set') { ?>on<?php  } ?>" data-page="set">商家设置</div>
        <div class="nav <?php  if($page=='withdraw') { ?>on<?php  } ?>" data-page="withdraw">提现</div>
        <div class="nav <?php  if($page=='statistics') { ?>on<?php  } ?>" data-page="statistics">统计结算</div>
    </div>
    <div class="info_main">
        <div class="line">
            <div class="title">商户名称</div>
            <div class='info'>
                <div class='inner'>
                <?php  if($store['centercan'] == 1) { ?>
                    <input type="text" id='name' placeholder="请输入店名"  value="<%name%>" />
                <?php  } else { ?>
                    <div><%name%></div>
                <?php  } ?>
                </div>
            </div>
        </div>
        <div class="line">
            <div class="title">联系人</div>
            <div class='info'>
                <div class='inner'>
                <?php  if($store['centercan'] == 1) { ?>
                    <input type="text" id='contact' placeholder="请输入您的姓名"  value="<%contact%>" />
                <?php  } else { ?>
                    <div><%contact%></div>
                <?php  } ?>
                </div>
            </div>
        </div>
        <div class="line">
            <div class="title">手机号码</div>
            <div class='info'>
                <div class='inner'>
                <?php  if($store['centercan'] == 1) { ?>
                    <input type="text" id='mobile' placeholder="请输入您的手机号码"  value="<%mobile%>" />
                <?php  } else { ?>
                    <div><%mobile%></div>
                <?php  } ?>    
                </div>
            </div>
        </div>
        <div class="line">
            <div class="title">地址</div>
            <div class='info'>
                <div class='inner'>
                <?php  if($store['centercan'] == 1) { ?>
                    <input type="text" id='address' placeholder="请输入您的商户地址"  value="<%address%>" />
                <?php  } else { ?>
                    <div><%address%></div>
                <?php  } ?>    
                </div>
            </div>
        </div>
        <div class="line">
            <div class="title">允许使用积分(%)</div>
            <div class='info'>
                <div class='inner'>
                <?php  if($store['centercan'] == 1) { ?>
                    <input type="text" id='deduct_credit1' value="<%deduct_credit1%>" />
                <?php  } else { ?>
                    <div><%deduct_credit1%></div>
                <?php  } ?>    
                </div>
            </div>
        </div>
        <div class="line">
            <div class="title">允许使用余额(%)</div>
            <div class='info'>
                <div class='inner'>
                <?php  if($store['centercan'] == 1) { ?>
                    <input type="text" id='deduct_credit2' value="<%deduct_credit2%>" />
                <?php  } else { ?>
                    <div><%deduct_credit2%></div> 
                <?php  } ?>       
                </div>
            </div>
        </div>
        <?php  if($com_set['level'] >= 1) { ?>
        <div class="line">
            <div class="title">一级分销佣金比例</div>
            <div class='info'>
                <div class='inner'>
                <?php  if($store['centercan'] == 1) { ?>
                    <input type="text" id='commission1_rate' value="<%commission1_rate%>" />
                <?php  } else { ?>
                    <div><%commission1_rate%></div>
                <?php  } ?>    
                </div>
            </div>
        </div>
        <?php  } ?>
        <?php  if($com_set['level'] >= 2) { ?>
        <div class="line">
            <div class="title">二级分销佣金比例</div>
            <div class='info'>
                <div class='inner'>
                <?php  if($store['centercan'] == 1) { ?>
                    <input type="text" id='commission2_rate' value="<%commission2_rate%>" />
                <?php  } else { ?>
                    <div><%commission2_rate%></div>
                <?php  } ?>
                </div>
            </div>
        </div>
        <?php  } ?>
        <?php  if($com_set['level'] >= 3) { ?>
        <div class="line">
            <div class="title">三级分销佣金比例</div>
            <div class='info'>
                <div class='inner'>
                <?php  if($store['centercan'] == 1) { ?>
                    <input type="text" id='commission3_rate' value="<%commission3_rate%>" />
                <?php  } else { ?>
                    <div><%commission3_rate%></div>
                <?php  } ?>
                </div>
            </div>
        </div>
        <?php  } ?>
<!--         <div class="line">
            <div class="title">积分奖励(%)</div>
            <div class='info'>
                <div class='inner'>
                    <input type="text" id='credit1' value="<%credit1%>" placeholder="支付完成后，获得的积分奖励" />
                </div>
            </div>
        </div> -->
        <div class="line">
            <div class="title">红包奖励(最小消费)</div>
            <div class='info'>
                <div class='inner'>
                <?php  if($store['centercan'] == 1) { ?>
                    <input type="text" id='redpack_min' value="<%redpack_min%>" placeholder="小于该金额没有红包奖励" />
                <?php  } else { ?>
                    <div><%redpack_min%></div>
                <?php  } ?>    
                </div>
            </div>
        </div>
        <div class="line">
            <div class="title">红包奖励(奖励百分比)</div>
            <div class='info'>
                <div class='inner'>
                <?php  if($store['centercan'] == 1) { ?>
                    <input type="text" id='redpack' value="<%redpack%>" />
                <?php  } else { ?>
                    <div><%redpack%></div>
                <?php  } ?>    
                </div>
            </div>
        </div>
        <div class="line">
            <div class="title">余额奖励(奖励百分比)</div>
            <div class='info'>
                <div class='inner'>
                <?php  if($store['centercan'] == 1) { ?>
                    <input type="text" id='creditpack' value="<%creditpack%>" />
                <?php  } else { ?>
                    <div><%creditpack%></div>  
                <?php  } ?> 
                </div>
            </div>
        </div>
        <?php  if($pcoupon) { ?>
        <div class="line">
            <div class="title">赠送优惠券</div>
            <div class='info'>
                <div class='inner'>
                    <select name="coupon_id" id="coupon_id" class="form-control">
                    <?php  if($store['centercan'] == 1) { ?>
                        <option value="0">-请选择-</option>
                        <?php  if(is_array($couponList)) { foreach($couponList as $coupon) { ?>
                            <option value="<?php  echo $coupon['id'];?>"><?php  echo $coupon['couponname'];?></option>
                        <?php  } } ?>
                    <?php  } else { ?>
                        <div><?php  echo $couponname;?></div>
                    <?php  } ?>    
                    </select>
                </div>
            </div>
        </div>
        <?php  } ?>
        <div class="line" style="height:80px;">

                    <span style="font-size: 12px; color: #666">您的计算金额=用户支付金额-用户支付金额*<?php  echo $store['settle_platform'];?>%(平台提成) - <?php  echo $text;?></span>

        </div>        
    </div>
    <div class="info_sub">确认修改</div>
    <div class="create_qrcode">生成收款二维码</div>
</script>
<script language="javascript">
    require(['tpl', 'core'], function(tpl, core) {
        core.pjson('cashier/store_set', {id:"<?php  echo $_GPC['id'];?>"}, function (json) {
            if (json.result.store) {
 
                var data = json.result.store;
                
                //alert(data.coupon_id);
                $('#container').html(tpl('store_info', data));

                $('#coupon_id').val(data.coupon_id);
                
                $('.nav').click(function() {
                    if ($(this).data('page') == 'set') {
                        location.href = core.getUrl('plugin/cashier/store_set', {page: 'set',id:"<?php  echo $_GPC['id'];?>"});
                    } else if($(this).data('page') == 'withdraw'){
                        location.href = core.getUrl('plugin/cashier/withdraw', {page: 'withdraw',id:"<?php  echo $_GPC['id'];?>"});
                    } else {
                        location.href = core.getUrl('plugin/cashier/statistics', {page: 'statistics',id:"<?php  echo $_GPC['id'];?>"});
                    }
                });

                $('.info_sub').click(function () {
                    if ($(this).attr('saving') == '1') {
                        return;
                    }
                    if ($('#name').isEmpty()) {
                        core.tip.show('请输入店名!');
                        return;
                    }
                    if ($('#contact').isEmpty()) {
                        core.tip.show('请输入联系人姓名');
                        return;
                    }
                    if (!$('#mobile').isMobile()) {
                        core.tip.show('请输入正确手机号码!');
                        return;
                    }
                    if ($('#address').isEmpty()) {
                        core.tip.show('请输入地址！');
                        return;
                    }
                    if (!/\d*$/.test($('#deduct_credit1').val())) {
                        core.tip.show('允许使用的积分请输入数字！');
                        return;
                    }
                    if (!/\d*$/.test($('#deduct_credit2').val())) {
                        core.tip.show('允许使用的余额请输入数字！');
                        return;
                    }
                    if ($('#commission1_rate').length > 0 && !/\d*$/.test($('#commission1_rate').val())) {
                        core.tip.show('一级分销佣金比例请输入数字！');
                        return;
                    }
                    if ($('#commission2_rate').length > 0 && !/\d*$/.test($('#commission2_rate').val())) {
                        core.tip.show('二级分销佣金比例请输入数字！');
                        return;
                    }
                    if ($('#commission3_rate').length > 0 && !/\d*$/.test($('#commission3_rate').val())) {
                        core.tip.show('二级分销佣金比例请输入数字！');
                        return;
                    }
                    if (!/\d*$/.test($('#credit1').val())) {
                        core.tip.show('积分奖励请输入数字！');
                        return;
                    }
                    if (!/\d*$/.test($('#redpack_min').val())) {
                        core.tip.show('红包奖励(最小消费)请输入数字！');
                        return;
                    }
                    if (!/\d*$/.test($('#redpack').val())) {
                        core.tip.show('红包奖励(奖励百分比)请输入数字！');
                        return;
                    }
                    if ($('#coupon_id').length > 0 && !/\d*$/.test($('#coupon_id').val())) {
                        core.tip.show('请选择优惠券！');
                        return;
                    }
                  
                    var postData = {
                        'op': 'sub_info',
                        'id': "<?php  echo $_GPC['id'];?>",
                        'name': $('#name').val(),
                        'contact': $('#contact').val(),
                        'mobile': $('#mobile').val(),
                        'address': $('#address').val(),
                        'deduct_credit1': $('#deduct_credit1').val(),
                        'deduct_credit2': $('#deduct_credit2').val(),
                        'credit1': $('#credit1').val(),
                        'redpack_min': $('#redpack_min').val(),
                        'redpack': $('#redpack').val(),
                        'creditpack': $('#creditpack').val(),
                    };
                    // alert(postData.redpack);
                    if ($('#commission1_rate').length > 0) {
                        postData.commission1_rate = $('#commission1_rate').val();
                    }
                    if ($('#commission2_rate').length > 0) {
                        postData.commission2_rate = $('#commission2_rate').val();
                    }
                    if ($('#commission3_rate').length > 0) {
                        postData.commission3_rate = $('#commission3_rate').val();
                    }
                    if ($('#coupon_id').length > 0 && /\d*$/.test($('#coupon_id').val())) {
                        postData.coupon_id = $('#coupon_id').val();
                    }
                    $(this).html('正在处理...').attr('saving', 1);
                    core.pjson('cashier/store_set', postData, function (json) {
                        if (json.status == 1) {
                             core.tip.show('保存成功');
                             location.href="<?php  echo $this->createPluginMobileUrl('cashier/store_set',array('id' => $_GPC['id']))?>";
                        } else {
                            $('.info_sub').html('确认修改').removeAttr('saving');
                            core.tip.show('保存失败!');
                        }
                    }, true, true);
                });

                $('.create_qrcode').click(function () {
                    location.href = "<?php  echo $this->createPluginMobileUrl('cashier/create_qrcode',array('id' => $_GPC['id']))?>";
                });
            }
        });

    })
</script>

<?php  $show_footer=true?>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
