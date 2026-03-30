<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('member/center', TEMPLATE_INCLUDEPATH)) : (include template('member/center', TEMPLATE_INCLUDEPATH));?>
<title>会员资料</title>
<style type="text/css">
    body {margin:0px; background:#efefef; font-family:'微软雅黑'; -moz-appearance:none;}
    .info_main {height:auto;  background:#fff; margin-top:14px; border-bottom:1px solid #e8e8e8; border-top:1px solid #e8e8e8;}
    .info_main .line {margin:0 10px; height:40px; border-bottom:1px solid #e8e8e8; line-height:40px; color:#999;}
    .info_main .line .title {height:40px; width:130px; line-height:40px; color:#444; float:left; font-size:14px;}
    .info_main .line .info { width:93%;float:right;margin-left:-80px;height: 40px }
    .info_main .line .inner { margin-left:80px; }
    .info_main .line .inner input {height:38px; width:100%;display:block; padding:0px; margin:0px; border:0px; float:left; font-size:16px;}
    .info_main .line .inner .user_sex {line-height:40px;}
    .info_sub {height:44px; margin:14px 5px; background:#31cd00; border-radius:4px; text-align:center; font-size:16px; line-height:44px; color:#fff;}
    .select { border:0px solid #ccc;height:38px;}
</style>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2-zh.js" type="text/javascript"></script>
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2.css" rel="stylesheet" type="text/css" />
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.animation-2.5.2.css" rel="stylesheet" type="text/css" />
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.datetime-2.5.1.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.datetime-2.5.1-zh.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.android-ics-2.5.2.js" type="text/javascript"></script>
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.android-ics-2.5.2.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../addons/sz_yi/static/js/dist/area/cascade.js"></script>
<div id="container" class="rightlist"></div></div>

<script id="member_info" type="text/html">
    <div class="page_topbar">
    <!-- <a href="javascript:;" class="back" onclick="history.back()"><i class="fa fa-angle-left"></i></a> -->
    <div class="title"><?php  if($_W['uniacid'] == 18) { ?>My Profile<?php  } else { ?>我的资料<?php  } ?></div>
</div>
    <div class="info_main">
        <div class="line"><div class="title"><?php  if($_W['uniacid'] == 18) { ?>Name<?php  } else { ?>姓名<?php  } ?></div><div class='info'><div class='inner'><input type="text" id='realname' placeholder="<?php  if($_W['uniacid'] == 18) { ?>Please enter your name<?php  } else { ?>请输入您的姓名<?php  } ?>"  value="<%realname%>" /></div></div></div>
        <%if mobile%>
        <div class="line"><div class="title"><?php  if($_W['uniacid'] == 18) { ?>Mobile<?php  } else { ?>绑定手机<?php  } ?></div><div class='info'><div class='inner'><%mobile%></div></div></div>
        <input type="hidden" id='mobile' value="<%mobile%>" />
        <%else%>
        <div class="line"><div class="title"><?php  if($_W['uniacid'] == 18) { ?>Mobile<?php  } else { ?>绑定手机<?php  } ?></div><div class='info'><div class='inner'><input type="text" id='mobile' placeholder="<?php  if($_W['uniacid'] == 18) { ?>Cannot be modified after binding<?php  } else { ?>绑定后不可修改<?php  } ?>"  value="<%mobile%>" /></div></div></div>
        <%/if%>
        <div class="line"><div class="title"><?php  if($_W['uniacid'] == 18) { ?>WeChat ID<?php  } else { ?>微信号<?php  } ?></div><div class='info'><div class='inner'><input type="text"  id='weixin' placeholder="<?php  if($_W['uniacid'] == 18) { ?>Please enter the WeChat ID<?php  } else { ?>请输入微信号<?php  } ?>" value="<%weixin%>"/></div></div></div>
        <div class="line"><div class="title"><?php  if($_W['uniacid'] == 18) { ?>Contact Nnumber<?php  } else { ?>联系电话<?php  } ?></div><div class='info'><div class='inner'><input type="text"  id='membermobile' placeholder="<?php  if($_W['uniacid'] == 18) { ?>Please type your phone number<?php  } else { ?>请输入联系电话<?php  } ?>" value="<%membermobile%>"/></div></div></div>
        <div class="line"><div class="title"><?php  if($_W['uniacid'] == 18) { ?>Alipay account<?php  } else { ?>支付宝帐号<?php  } ?></div><div class='info'><div class='inner'><input type="text"  id='alipay' placeholder="<?php  if($_W['uniacid'] == 18) { ?>Please enter Alipay account<?php  } else { ?>请输入支付宝帐号<?php  } ?>" value="<%alipay%>"/></div></div></div>
        <div class="line"><div class="title"><?php  if($_W['uniacid'] == 18) { ?>Account name<?php  } else { ?>账号姓名<?php  } ?></div><div class='info'><div class='inner'><input type="text"  id='alipayname' placeholder="<?php  if($_W['uniacid'] == 18) { ?>Please enter your account name<?php  } else { ?>请输入账号姓名<?php  } ?>" value="<%alipayname%>"/></div></div></div>
        <div class="line">
            <div class="title"><?php  if($_W['uniacid'] == 18) { ?>Gender<?php  } else { ?>性别<?php  } ?></div><div class='info'><div class='inner'>
            <span class="gender" data-val="1"><i class="fa <%if gender=='1'%>fa-check-circle-o<%else%>fa-circle-o<%/if%>" <%if gender=='1'%>style="color:#0C9;"<%/if%>></i> <?php  if($_W['uniacid'] == 18) { ?>Male<?php  } else { ?>男<?php  } ?></span>&nbsp;&nbsp;
            <span class="gender" data-val="2"><i class="fa <%if gender=='2'%>fa-check-circle-o<%else%>fa-circle-o<%/if%>" <%if gender=='2'%>style="color:#0C9;"<%/if%>></i> <?php  if($_W['uniacid'] == 18) { ?>Female<?php  } else { ?>女<?php  } ?>
                <input type="hidden" id="gender" value="<%sex%>" />
                </div></div>
        </div>
        <div class="line">
            <div class="title"><?php  if($_W['uniacid'] == 18) { ?>City<?php  } else { ?>所在城市<?php  } ?></div><div class='info'><div class='inner'>
            <select id="sel-provance" onChange="selectCity();" class="select">
                <option value="" selected="true"><?php  if($_W['uniacid'] == 18) { ?>State / Province<?php  } else { ?>省/直辖市<?php  } ?></option>
            </select>
            <select id="sel-city" onChange="selectcounty()" class="select">
                <option value="" selected="true"><?php  if($_W['uniacid'] == 18) { ?>Please choose<?php  } else { ?>请选择<?php  } ?></option>
            </select>
            <select id="sel-area" class="select" style="display:none">
                <option value="" selected="true"><?php  if($_W['uniacid'] == 18) { ?>Please choose<?php  } else { ?>请选择<?php  } ?></option>
            </select></div></div>
        </div>
        <div class="line"  style="border:0px;"><div class="title"><?php  if($_W['uniacid'] == 18) { ?>Date of birth<?php  } else { ?>生日<?php  } ?></div><div class='info'><div class='inner'><input type="text" id="birthday" placeholder="<?php  if($_W['uniacid'] == 18) { ?>Click to select date<?php  } else { ?>点击选择日期<?php  } ?>" readonly value='<%birthday%>'/></div></div></div>

    </div>
    <div class="info_sub"><?php  if($_W['uniacid'] == 18) { ?>Confirm the changes<?php  } else { ?>确认修改<?php  } ?></div>
</script>
<script language="javascript">
    require(['tpl', 'core'], function(tpl, core) {
        core.json('member/info',{},function(json){
            if (json.result.member) {

                var data = json.result.member;

                $('#container').html(tpl('member_info', data));

                var currYear = (new Date()).getFullYear();
                var opt = {};
                opt.date = {preset: 'date'};
                opt.datetime = {preset: 'datetime'};
                opt.time = {preset: 'time'};
                opt.default = {
                    theme: 'android-ics light',
                    display: 'modal',
                    mode: 'scroller',
                    lang: 'zh',
                    startYear: currYear - 100,
                    endYear: currYear
                };

                $("#birthday").scroller('destroy').scroller($.extend(opt['date'], opt['default']));
                cascdeInit(data.province,data.city,data.dist);
                $('.gender').click(function() {
                    var $this = $(this);
                    var val = $this.data('val');
                    $('.gender').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
                    $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
                    $('#gender').val(val);
                })
                $('.info_sub').click(function() {
                    if($(this).attr('saving')=='1')
                    {
                        return;
                    }

                   if($('#realname').isEmpty()){
                       core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Please type in your name<?php  } else { ?>请输入姓名<?php  } ?>!');
                       return;
                   }
                  if(!$('#mobile').isMobile() && $('#mobile').val()){
                       core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Please enter the correct binding mobile phone number<?php  } else { ?>请输入正确的绑定手机号<?php  } ?>!');
                       return;
                   }
                   if(!$('#membermobile').isMobile()){
                       core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Please enter the correct contact number<?php  } else { ?>请输入正确的联系电话<?php  } ?>!');
                       return;
                   }

/*                  if( $('#weixin').isEmpty()){
                       core.tip.show('请输入微信号!');
                       return;
                   }
*/                   $(this).html('<?php  if($_W['uniacid'] == 18) { ?>Processing<?php  } else { ?>正在处理<?php  } ?>...').attr('saving',1);
                   var birthday = $('#birthday').val().split('-');
                    core.json('member/info', {
                       'memberdata':{
                            'realname': $('#realname').val(),
                            'mobile': $('#mobile').val(),
                            'membermobile': $('#membermobile').val(),
                            'weixin': $('#weixin').val(),
                            'gender': $('#gender').val(),
                            'birthyear': $('#birthday').val().length>0?birthday[0]:0,
                            'birthmonth': $('#birthday').val().length>0?birthday[1]:0,
                            'birthday': $('#birthday').val().length>0?birthday[2]:0,
                            'province': $('#sel-provance').val(),
                            'city': $('#sel-city').val(),
                             'alipay': $('#alipay').val(),
                            'alipayname': $('#alipayname').val(),
                       }, 'mcdata':{
                            'realname': $('#realname').val(),
                            'mobile': $('#mobile').val(),
                            'membermobile': $('#membermobile').val(),
                            'gender': $('#gender').val(),
                            'birthyear': $('#birthday').val().length>0?birthday[0]:0,
                            'birthmonth': $('#birthday').val().length>0?birthday[1]:0,
                            'birthday': $('#birthday').val().length>0?birthday[2]:0,
                            'resideprovince': $('#sel-provance').val(),
                            'residecity': $('#sel-city').val()
                       }
                    }, function(json) {

                        if(json.status==1){
                             core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Saved successfully<?php  } else { ?>保存成功<?php  } ?>');
                             <?php  if(!empty($_GPC['returnurl'])) { ?>
                                 location.href="<?php  echo urldecode($_GPC['returnurl'])?>";
                             <?php  } else { ?>
                                 location.href="<?php  echo $this->createMobileUrl('member')?>";
                             <?php  } ?>
                        }
                        else{
                            $('.info_sub').html('<?php  if($_W['uniacid'] == 18) { ?>Confirm the changes<?php  } else { ?>确认修改<?php  } ?>').removeAttr('saving');
                            core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Save failed<?php  } else { ?>保存失败<?php  } ?>!');
                        }

                    },true,true);
                })
            }
        });

    })
</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
