<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<style>
    #registerForm div{height:25px;}
    .regist-process-register-left .read-protocal{margin-top:30px;}
    a:hover, a:visited, a:active, a:link{color:#4594f9;}
    .mobile_reg{display:none;width: 130px;
        position: relative;
    bottom: 165px;
    right: 70px;
    left: 45px;}
    .mobile_regss{display:none;width: 130px;
        position: relative;
    bottom: 165px;
    right: 70px;
    left: 45px;}
    .mobile_reg_but{background-color: #E53939;font-size:25px; color:#fff;top: -5px;
        position: relative;
        left: 10px;
        border-radius: 10px;
        width: 35%;}
    #registerForm{display:none;}
    .regist-process-register-left .text{width: 200px;}
</style>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/navigation', TEMPLATE_INCLUDEPATH)) : (include template('common/navigation', TEMPLATE_INCLUDEPATH));?>

        <div class="fl wfs bcf7">
            <div class="regist-process-wrapper">
                <div class="regist-process-body fl wfs">
                    
                    <div class="regist-process-register-left fl">
                        <h2 class="title"><?php  if($_W['uniacid'] == 18) { ?>Registered<?php  } else { ?>注册<?php  } ?><?php  echo $this->yzShopSet['name']?><?php  if($_W['uniacid'] == 18) { ?><?php  } else { ?>会员<?php  } ?></h2>
                        <?php  if($_GPC['i'] == 3) { ?>
                        <div style="width: 84%; margin: 24px auto; padding: 10px; border: 1px solid #1C7AB7; display: inline-block;">
                        	<div style="float: left; width: 35px; margin-right: 10px; margin-top: 16px;"><img src="https://shop.gogo198.cn/addons/sz_yi/template/pc/default/static/images/tip.png" alt="" width="32px"></div>
                        	
                        	<?php  if($_W['uniacid'] == 18) { ?>
                        	<div style="float: left; width: 392px;"><span style="color: #1C7AB7; font-size: 16px;">Member registration tips：</span><br/>In order to enhance your shopping experience, it is strongly recommended to use WeChat account registration and real-name authentication!<br/>For customer service, please call：0757-86329911。</div>
                        	<?php  } else { ?>
                        	<div style="float: left; width: 392px;"><span style="color: #1C7AB7; font-size: 16px;">会员注册提示：</span><br/>为提升阁下的购物体验，强烈推荐使用微信号注册及实名认证！<br/>如需客服，请致电：0757-86329911。</div>
                        	<?php  } ?>
                        </div>
                        <?php  } ?>
                        <div style="margin-bottom: 180px;margin-top: 20px;">
                        	<?php  if($_W['uniacid'] == 18) { ?>
                        	<img src="https://shop.gogo198.cn/addons/sz_yi/template/pc/default/static/images/wechat_login_en.png" alt="" width="40%" id="mobile_reg">
                        	<?php  } else { ?>
                            <img src="https://shop.gogo198.cn/addons/sz_yi/template/pc/default/static/images/wechat_login.png" alt="" width="40%" id="mobile_reg">
                            <?php  } ?>
                            <!-- <h2 class="title" style="font-size:25px;float:left;">購購会员注册 | </h2>
                            <button type="button" id="mobile_reg" class="mobile_reg_but">使用手机注册</button> -->
                        </div>
                         
                            <div class="mobile_reg">
                                <?php  if($_GPC['i'] == 3) { ?>
                                <img src="https://shop.gogo198.cn/attachment/mobile_reg.png">
                                <?php  } else { ?>
                                <img src="https://shop.gogo198.cn/attachment/dada_reg.png">
                                <?php  } ?>
                            </div>
                            <div class="mobile_regss"><div id="qrcodes"></div></div>
                        	
                        	<?php  if($_W['uniacid'] == 18) { ?>
                        	<img src="https://shop.gogo198.cn/addons/sz_yi/template/pc/default/static/images/mobile_login_en.png" alt="" width="40%" id="mobile_reg2" style="position: relative;
                            bottom: 150px;">
                        	<?php  } else { ?>
                            <img src="https://shop.gogo198.cn/addons/sz_yi/template/pc/default/static/images/mobile_login.png" alt="" width="40%" id="mobile_reg2" style="position: relative;
                            bottom: 150px;">
                            <?php  } ?>
                            <!-- <h2 class="title" style="font-size:25px;float:left;">購購会员注册 | </h2>
                            <button type="button" id="mobile_reg" class="mobile_reg_but">使用手机注册</button> -->
                        
                        
                        <form id="registerForm" style="position: relative;
                        bottom: 155px;" action="user.php" method="post" name="formUser" onsubmit="return register2();">
                            
                            <!--2019-05-15-->
                            <!-- <p style="width: 82%;padding: 30px 0 0;color: #c30000;">依据相关法规，于我司海关备案的跨境电商平台购物需提供中国公民实名身份信息，为此，敬请实名注册会员。</p> -->
                            
                            <!-- <div>
                                <span class="title">真实姓名：</span>
                                <input class="form-control text" id="xingming"  name="xingming" type="text">
                                <p class="tips" id="xingmings"></p>
                            </div>
                            <div>
                                <span class="title">身份证号：</span>
                                <input class="form-control text" id="idcard"  name="idcard" type="text">
                                <p class="tips" id="idcards"></p>
                            </div>
                            <div>
                                <span class="title">省：</span>
                                <input class="form-control text" id="province"  name="province" type="text">
                                <p class="tips" id="provinces"></p>
                            </div>
                            <div>
                                <span class="title">市：</span>
                                <input class="form-control text" id="city"  name="city" type="text">
                                <p class="tips" id="citys"></p>
                            </div>
                            <div>
                                <span class="title">收件地址：</span>
                                <input class="form-control text" id="addr"  name="addr" type="text">
                                <p class="tips" id="addrs"></p>
                            </div> -->
                            <!--2019-05-15结束-->
                            <div>
                                <span class="title"><?php  if($_W['uniacid'] == 18) { ?><span style="width: 105px;">Mobile</span><?php  } else { ?>手机号<?php  } ?>：</span>
                                <input class="form-control text" id="mobile" name="mobile"  type="text">
                                <p class="tips" id="username_notice"></p>
                            </div>
                            <div>
                                <span class="title"><?php  if($_W['uniacid'] == 18) { ?>Code<?php  } else { ?>验证码<?php  } ?>：</span>
                                <input id="code" class="form-control text" type="text"  name="code">
                                <input type="button" class="yzma" id="btnSendCode" value="<?php  if($_W['uniacid'] == 18) { ?>Send<?php  } else { ?>发送验证码<?php  } ?>">
                                <!-- <a href="#" class="yzma">60秒后重新发送</a> -->
                                <p class="tips" id="email_notice"></p>
                            </div>
                            
                            <div>
                                <span class="title"><?php  if($_W['uniacid'] == 18) { ?>Password<?php  } else { ?>登录密码<?php  } ?>：</span>
                                <input class="form-control text" id="password"  name="password"  type="password">
                                <p class="tips" id="password_notice"></p>
                            </div>
                            <div>
                                <span class="title"><?php  if($_W['uniacid'] == 18) { ?>Retype password<?php  } else { ?>确认密码<?php  } ?>：</span>
                                <input class="form-control text" id="cpassword" onblur="check_conform_password(this.value);" name="cpassword" type="password">
                                <p class="tips" id="conform_password_notice"></p>      
                            </div>
                            <?php  if($this->yzShopSet['isreferral'] == 1) { ?>
                              <div>
                                  <span class="title">推荐码：</span>
                                  <input class="form-control text" id="referral" name="referral" type="text" placeholder="选填项">    
                              </div>
                            <?php  } ?>

                            <div class="read-protocal" style="margin-top: 30px;">
                                <input class="checkbox-inline" id="protocal" name="agreement" type="checkbox" value="1" checked="checked" />
                        <?php  if($_W['uniacid'] == 18) { ?>
                        I have read and accept the terms of service of<a href="https://shop.gogo198.cn/app/index.php?i=3&c=site&a=site&do=detail&id=17" target="_blank">"Cross-border Service Agreement"</a>
                        <?php  } else { ?>        
                                我已阅读并接受<a href="https://shop.gogo198.cn/app/index.php?i=3&c=site&a=site&do=detail&id=17" target="_blank">《「<?php  echo $this->yzShopSet['name']?>」跨境服务协议》</a>各项服务条款
                        <?php  } ?>
                            </div>
                            <input type="hidden" name="act" value="act_register" >
                            <input type="hidden" name="back_act" value="" />
                            <input class="btn btn-danger register-now register" id="smrz" name="Submit" type="button"  value="<?php  if($_W['uniacid'] == 18) { ?>Verified<?php  } else { ?>实名认证<?php  } ?>">
                        </form>
                    </div>
                    
                   <div class="regist-process-register-right fr">
                       <h2 class="title"><?php  if($_W['uniacid'] == 18) { ?>Existing account login<?php  } else { ?>已有账户登录<?php  } ?></h2>
                       <a class="btn btn-info login-now" href="<?php  echo $this->createMobileUrl('member')?>"><?php  if($_W['uniacid'] == 18) { ?>Login<?php  } else { ?>立即登录<?php  } ?></a>
                       <div class="scan" style="margin-top:25px">
                        <!-- <?php  if($this->yzShopSet['reglogo']) { ?>
                            <img src="<?php  echo $this->yzImages['reglogo']?>" style="width:335px;height:230px;" title="<?php  echo $this->yzShopSet['pctitle']?>">
                            <?php  } else { ?>
                            <img src="../addons/sz_yi/template/pc/default/static/images/logo.png" title="" alt="我是默认logo">
                            <?php  } ?> -->

                            <?php  if($this->yzShopSet['pclogo']) { ?>
                            <?php  if(FALSE == stristr($this->yzShopSet['pclogo'], "http")) { ?>
                              <?php  $pclogo = $_W['siteroot'] . "attachment/" . $this->yzShopSet['pclogo'];?>
                            <?php  } else { ?>
                              $pclogo = "<?php  echo $_W['siteroot'];?>attachment/{$this->yzShopSet['pclogo']}";
                            <?php  } ?>
                            <img src="<?php  echo $pclogo?>" style="width:335px;" title="<?php  echo $this->yzShopSet['pctitle']?>">
                          <?php  } else { ?>
                            <img src="../addons/sz_yi/template/pc/default/static/images/logo.png" title="" alt="我是默认logo"> 
                          <?php  } ?>    
                       </div>
                   </div>
                    
                </div>
            </div>
            
        </div>

<div class="blank"></div>
    <div class="regist-process-foot fl wfs">
        <p class="copyright"><?php  echo htmlspecialchars_decode($this->yzShopSet['pccopyright'])?></p>
    </div>

</div>
<script type="text/javascript" src="../addons/sz_yi/static/js/dist/area/qrcode.js"></script>
<script>

    $("#mobile_reg").click(function(){
        if( $(".mobile_reg").css("display")=='none' )
        {
            $(".mobile_reg").show();
        }
        else
        {
            $(".mobile_reg").hide();
        }
    });

    $("#mobile_reg2").click(function(){
        if( $("#registerForm").css("display")=='none' )
        {
            $("#registerForm").show();
        }
        else
        {
            $("#registerForm").hide();
        }
        //alert("抱歉！正在更新，暂停使用");
    });

    
</script>
<script language="javascript">
    require(['tpl', 'core'], function(tpl, core) {
        //$('#container').html(tpl('member_info'));

            $('#mobile').blur(function(){
                if(!$('#mobile').isMobile()){
                    core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Please enter the correct phone number!<?php  } else { ?>请输入正确手机号码!<?php  } ?>');
                    return;
                }
                core.json('member/sendcode', {
                      'op'    : 'ismobile',
                      'mobile'  : $('#mobile').val(),
                       }, function(json) {
                        if(json.status==0){
                             core.tip.show(json.result);
                        }
                }, true, true);
            });
            
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
                       'mobile': $('#mobile').val()
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
                    },true,true);
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
                    $("#btnSendCode").val(curCount + "<?php  if($_W['uniacid'] == 18) { ?>Resend in seconds<?php  } else { ?>秒后重新验证码<?php  } ?>");
                }
            }


            $('.register').click(function() {
                
                //   if ($('#xingming').isEmpty()){
                //       core.tip.show("请输入姓名");
                //       return;
                //   }

                // if ($('#idcard').isEmpty()){
                //     core.tip.show("请输入证件号码");
                //     return;
                // }
                // if ($('#province').isEmpty()){
                //     core.tip.show("请输入地址");
                //     return;
                // }
                // if ($('#city').isEmpty()){
                //     core.tip.show("请输入城市");
                //     return;
                // }
                // if ($('#addr').isEmpty()){
                //     core.tip.show("请输入地址");
                //     return;
                // }

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
                        core.tip.show(json.result);
                        return;
                    }

                    core.json('member/register', {
                        'mobile': $('#mobile').val(),
                        'password': $('#password').val(),
                        'referral': $('#referral').val(),
                        'code': $('#code').val(),
                        // 'xingming':$('#xingming').val(),
                        // 'idcard' :$('#idcard').val(),
                        // 'province':$('#province').val(),
                        // 'city':$('#city').val(),
                        // 'addr':$('#addr').val(),
                        }, function(json) {
                            $("#smrz").attr('disabled','disabled');
                            if(json.status==1){
                                //  core.tip.show('注册成功');
                                //  location.href=json.result;

                                var ii = '<?php  echo $_GPC["i"];?>';
                                if(ii == '3')
                                {
                                    var url = "https://shop.gogo198.cn/app/index.php?i=3&c=entry&p=easy_deliver_family&do=member&m=sz_yi&mobile="+$('#mobile').val()+"&pwd="+$('#password').val();
                                }else{
                                    var url = "https://shop.gogo198.cn/app/index.php?i=<?php  echo $_GPC["i"];?>&c=entry&p=register&do=member&m=sz_yi&mobile="+$('#mobile').val()+"&pwd="+$('#password').val(); 
                                }
                                
                                var qrcode = new QRCode(document.getElementById("qrcodes"), {
                                    width : 200,
                                    height : 180
                                });
                                qrcode.makeCode(url);
                                $('.mobile_regss').show();
                            }
                            else{
                                core.tip.show(json.result);
                            }
                        },true, true);
                    },true,true);
            });
    })
</script>
</body>
</html>
