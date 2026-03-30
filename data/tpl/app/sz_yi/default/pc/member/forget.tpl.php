<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header_center', TEMPLATE_INCLUDEPATH)) : (include template('common/header_center', TEMPLATE_INCLUDEPATH));?>
<title>忘记密码</title>

<style type="text/css">
    body {margin:0px; background:#efefef; font-family:'微软雅黑'; -moz-appearance:none;}
    .info_main {height:auto;  background:#fff; width: 400px;margin: 50px auto 10px}
    .info_main .line {margin:10px 0; height:34px;line-height:40px; color:#999;position: relative;}
    .info_main .line .title {height:34px; width:80px; line-height:34px; color:#444; float:left; font-size:14px;}
    .info_main .line .info { width:100%;float:right;margin-left:-80px; }
    .info_main .line .inner { margin-left:80px; }
    .info_main .line .inner input {border-radius: 4px; height:34px; display:block; padding:0px; margin:0px; border:0px; float:left; font-size:12px;border: 1px solid #CFCFCF;padding-left:6px;line-height: 34px;width: 320px}
    .info_main .line .inner .user_sex {line-height:40px;}
    .register {height:44px; margin:14px 5px; background:#31cd00; border-radius:4px; text-align:center; font-size:16px; line-height:44px; color:#fff;}
    .info_sub {height:30px; margin:14px 5px 14px 480px; background:#31cd00; border-radius:4px; text-align:center; font-size:14px; line-height:30px; color:#fff;width: 100px;cursor: pointer;}
    .nobindmobile {clear:both;height:44px; margin:14px 5px; background:#ccc; border-radius:4px; text-align:center; font-size:16px; line-height:44px; color:#fff;}
    .select { border:1px solid #ccc;height:25px;}
    #btnSendCode{ padding: 0 10px;font-size: 14px;height: 33px;border-radius: 0 4px 4px 0;width: 100px;font-size: 12px;border: 0;}
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
<div id="container" class="all-divbox"></div>

<script id="member_info" type="text/html">
<div class="page_topbar">
    <!-- <a href="javascript:;" class="back" onclick="history.back()"><i class="fa fa-angle-left"></i></a> -->
    <div class="title"><?php  if($_W['uniacid'] == 18) { ?>Forget password<?php  } else { ?>忘记密码<?php  } ?></div>
</div>
    <div class="info_main">
        <div class="line"><div class="title"><?php  if($_W['uniacid'] == 18) { ?>Mobile<?php  } else { ?>手机号码<?php  } ?>：</div><div class='info'><div class='inner'><input type="text" id='mobile' placeholder="<?php  if($_W['uniacid'] == 18) { ?>Please enter your phone number<?php  } else { ?>请输入您的手机号码<?php  } ?>"  value="" /></div></div></div>
        <div class="line"><div class="title"><?php  if($_W['uniacid'] == 18) { ?>Code<?php  } else { ?>验证码<?php  } ?>：</div><div class='info'><div class='inner'><input type="text" id='code' placeholder="<?php  if($_W['uniacid'] == 18) { ?>Please enter verification code<?php  } else { ?>请输入验证码<?php  } ?>"  value="" /><input id="btnSendCode" type="button" style="position: absolute;right: 0;top: 0;" value="<?php  if($_W['uniacid'] == 18) { ?>Send<?php  } else { ?>发送验证码<?php  } ?>"  /></div></div></div>
        <div class="line"><div class="title"><?php  if($_W['uniacid'] == 18) { ?>Password<?php  } else { ?>设置密码<?php  } ?>：</div><div class='info'><div class='inner'><input type="password" id='password' placeholder="<?php  if($_W['uniacid'] == 18) { ?>Please enter your password<?php  } else { ?>请输入您的密码<?php  } ?>"  value="" /></div></div></div>
        <div class="line"><div class="title"><?php  if($_W['uniacid'] == 18) { ?>Retype password<?php  } else { ?>确认密码<?php  } ?>：</div><div class='info'><div class='inner'><input type="password" id='cpassword' placeholder="<?php  if($_W['uniacid'] == 18) { ?>Please confirm your password<?php  } else { ?>请确认密码<?php  } ?>"  value="" /></div></div></div>
        
    </div>
    <div class="info_sub">Save</div>
</script>
<script language="javascript">
    require(['tpl', 'core'], function(tpl, core) {
        $('#container').html(tpl('member_info'));

            var InterValObj; //timer变量，控制时间
            var count = 60; //间隔函数，1秒执行
            var curCount;//当前剩余秒数

            $('#btnSendCode').click(function(){
                if(!$('#mobile').isMobile()){
                    core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Please enter the correct phone number!<?php  } else { ?>请输入正确手机号码!<?php  } ?>');
                    return;
                }
              　curCount = count;
            　　
                 core.json('member/sendcode', {
                       'mobile': $('#mobile').val(),
                       'op' : 'forgetcode'
                       }, function(json) {
                        if(json.status==1){
                             //设置button效果，开始计时
                             $("#btnSendCode").attr("disabled", "true");
                             $("#btnSendCode").val(curCount + "<?php  if($_W['uniacid'] == 18) { ?>Resend in seconds<?php  } else { ?>秒后重新获取验证码<?php  } ?>");
                             InterValObj = window.setInterval(SetRemainTime, 1000); //启动计时器，1秒执行一次
                        　　  //向后台发送处理数据 
                        }else{
                            core.tip.show(json.result);
                        }
                    },true);
            });

            //timer处理函数
            function SetRemainTime() {
                if (curCount == 0) {                
                    window.clearInterval(InterValObj);//停止计时器
                    $("#btnSendCode").removeAttr("disabled");//启用按钮
                    $("#btnSendCode").val("<?php  if($_W['uniacid'] == 18) { ?>Resend<?php  } else { ?>重新发送验证码<?php  } ?>");
                }
                else {
                    curCount--;
                    $("#btnSendCode").val("<?php  if($_W['uniacid'] == 18) { ?><?php  } else { ?>请在<?php  } ?>" + curCount + "<?php  if($_W['uniacid'] == 18) { ?>Resend in seconds<?php  } else { ?>秒内输入验证码<?php  } ?>");
                }
            }


            $('.info_sub').click(function() {
                  if(!$('#mobile').isMobile()){
                       core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Please enter the correct phone number!<?php  } else { ?>请输入正确手机号码!<?php  } ?>');
                       return;
                  }
                  if( $('#code').isEmpty()){
                       core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Please enter the verification code!<?php  } else { ?>请输验证码!<?php  } ?>');
                       return;
                  }



                  if( $('#password').isEmpty()){
                       core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Please enter password!<?php  } else { ?>请输入密码!<?php  } ?>');
                       return;
                  }
                  if( $('#cpassword').isEmpty()){
                       core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Please enter the password again!<?php  } else { ?>请再次输入密码!<?php  } ?>');
                       return;
                  }
                  if( $('#cpassword').val() != $('#password').val()){
                       core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>The two passwords are inconsistent!<?php  } else { ?>两次密码不一致!<?php  } ?>');
                       return;
                  }
                  //检验验证码
                    core.json('member/sendcode', {
                        'code': $('#code').val(),
                        'op':'checkcode'
                       }, function(json) {
   
                      if(json.status == 0)
                      {
                       core.tip.show(json.result.msg);
                       return;
                      }
                        core.json('member/forget', {
                           'memberdata':{
                                'mobile': $('#mobile').val(),
                                'password': $('#password').val()
                               } 
                           }, function(json) {
                            if(json.status==1){
                                 core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Successfully modified<?php  } else { ?>修改成功<?php  } ?>');
                                 //console.log(json.result.preurl);
                                 location.href=json.result.preurl;
                            }
                            else{
                                core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Fail to edit!<?php  } else { ?>修改失败!<?php  } ?>');
                            }

                        },true,true);

                    },true,true);      

                });
    })
</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
