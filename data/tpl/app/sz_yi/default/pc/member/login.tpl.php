<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/navigation', TEMPLATE_INCLUDEPATH)) : (include template('common/navigation', TEMPLATE_INCLUDEPATH));?>
<div class="blank"></div>
<?php  if($operation == 'black') { ?>
  <?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
  <style type="text/css">
  .ok {height:200px; padding-top:65px;}
  .ok .ico {height:65px; width:65px; margin:auto; border:3px solid #32cd32; border-radius:55px; color:#32cd32; font-size:50px; text-align:center; line-height:65px;}
  .ok .text {height:20px; margin-top:30px; font-size:16px; color:#666; line-height:20px; text-align:center;}
  .ok .sub {height:32px; width:145px; background:#e53c39; margin:20px auto; border-radius:20px; color:#fff; line-height:32px; text-align:center; font-size:16px;}
  </style>
  <title>禁止访问</title>
  <div class="fl wfs bcf7">
  <div id='container'></div>
    <div class="ok">
      <div class="ico"><i class="fa fa-check"></i></div>
<!--            <div class="text">禁止访问，请联系客服！</div>-->
        <div class="text"><?php  if($_W['uniacid'] == 18) { ?>Waiting for review, please contact customer service!<?php  } else { ?>等待审核，请联系客服！<?php  } ?></div>
      </div>
    </div>
<?php  } else { ?>

<style>
    .wechats{
        z-index: 100;
    }
    .autoLogin{
        margin-top: 10px;
        /*border: 1px solid #ccc;*/
        /*height: 50px;*/
        float: left;
        width: 65%;
    }
</style>

<div class="fl wfs bcf7">
        <div class="regist-process-wrapper">
            <div class="regist-process-body fl wfs">
                
                <form id="loginForm" name="formLogin"  method="post" onSubmit="return userLogin()">
                    <div class="regist-process-login-left fl">
                        <h2 class="title"><?php  if($_W['uniacid'] == 18) { ?>Sign in<?php  } else { ?>登录<?php  } ?></h2>
                          <input class="form-control text" name="username" id="username" type="text" placeholder="<?php  if($_W['uniacid'] == 18) { ?>Enter your user name<?php  } else { ?>请输入用户名<?php  } ?>">
                          <input class="form-control text" name="password" id="password" type="password" placeholder="<?php  if($_W['uniacid'] == 18) { ?>Enter password<?php  } else { ?>请输入登录密码<?php  } ?>">
                                                    <input  class="btn btn-danger login-btn" type="button" name="submit" value="<?php  if($_W['uniacid'] == 18) { ?>Log in<?php  } else { ?>登录<?php  } ?>" />
                        <div class="operates fl wfs">
                          <a class="fr" href="<?php  echo $this->createMobileUrl('member/forget')?>"><?php  if($_W['uniacid'] == 18) { ?>Forget password<?php  } else { ?>忘记密码<?php  } ?></a>
                          <p>
                              <input type="hidden" name="act" value="act_login" />
                              <input type="hidden" name="back_act" value="" />
                              <!-- <input type="checkbox" value="1" name="remember" id="remember" />
                              下次自动登录<span>使用公用电脑勿选</span> -->
                          </p>

                        </div>

                        <!-- <div class="autoLogin wfs" style="margin-left:0px;">
                            <a class="wechats" href="user.php?act=wechat" target="_blank">
                                <svg t="1584087574022" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="4076" width="50" height="50"><path d="M290.852571 291.145143c-22.820571 0-45.787429 15.067429-45.787428 37.924571s22.966857 38.070857 45.787428 38.070857c22.784 0 37.924571-15.213714 37.924572-38.034285 0-22.893714-15.177143-37.961143-37.924572-37.961143z m311.734858 220.598857c-15.177143 0-30.427429 15.213714-30.427429 30.390857 0 15.36 15.250286 30.427429 30.427429 30.427429 22.966857 0 38.034286-15.030857 38.034285-30.427429 0-15.177143-15.067429-30.390857-38.034285-30.390857zM1024 156.050286A155.721143 155.721143 0 0 0 868.315429 0.292571H156.818286A155.721143 155.721143 0 0 0 1.097143 156.050286v711.497143a155.721143 155.721143 0 0 0 155.721143 155.721142h711.497143a155.721143 155.721143 0 0 0 155.721142-155.721142V156.050286zM389.632 679.241143c-37.961143 0-68.461714-7.753143-106.532571-15.286857l-106.276572 53.321143 30.390857-91.501715c-76.105143-53.211429-121.709714-121.746286-121.709714-205.312 0-144.713143 136.96-258.633143 304.128-258.633143 149.540571 0 280.502857 91.062857 306.834286 213.577143a264.411429 264.411429 0 0 0-29.293715-1.792c-144.457143 0-258.56 107.812571-258.56 240.676572 0 22.089143 3.474286 43.410286 9.435429 63.707428-9.398857 0.731429-18.870857 1.243429-28.416 1.243429z m448.585143 106.496l22.857143 75.995428-83.419429-45.677714c-30.427429 7.606857-60.964571 15.286857-91.245714 15.286857-144.713143 0-258.669714-98.925714-258.669714-220.708571 0-121.6 113.956571-220.672 258.669714-220.672 136.630857 0 258.304 99.108571 258.304 220.672 0 68.608-45.458286 129.316571-106.496 175.140571z m-68.388572-273.993143c-14.994286 0-30.171429 15.213714-30.171428 30.390857 0 15.36 15.140571 30.427429 30.208 30.427429 22.784 0 38.034286-15.030857 38.034286-30.427429 0-15.177143-15.286857-30.390857-38.034286-30.390857z m-266.166857-144.603429c22.930286 0 38.034286-15.250286 38.034286-38.034285 0-22.930286-15.104-37.961143-38.034286-37.961143-22.784 0-45.641143 15.067429-45.641143 37.924571 0 22.820571 22.857143 38.070857 45.641143 38.070857z" fill="#2DB837" p-id="4077"></path></svg>
                                <p>微信登陆</p>
                            </a>
                            <a class="qq" href="user.php?act=qq" target="_blank">qq</a>
                            <a class="sina" href="user.php?act=sina" target="_blank">新浪微博</a>
                            <a class="alipay" href="user.php?act=alipay" target="_blank">支付宝</a>
                        </div> -->
                    </div>
                </form>
                
                <div class="regist-process-login-right fr">
                    <h2 class="title"><?php  if($_W['uniacid'] == 18) { ?><span style="font-size: 27px;">No account? Sign up now</span><?php  } else { ?>没有账号？立即注册<?php  } ?></h2>
                    <a class="btn btn-info free-registe" href="<?php  echo $this->createMobileUrl('member/register')?>"><?php  if($_W['uniacid'] == 18) { ?>Registered<?php  } else { ?>免费注册<?php  } ?></a>
		    <div style="margin-top:25px">
		    <!-- $this->yzShopSet['reglogo']
			    <img src="<?php  echo $this->yzImages['reglogo']?>" style="width:335px;height:230px;" title="<?php  echo $this->yzShopSet['pctitle']?>">

			   -->
			   				
			   				
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

    <script>
require(['tpl', 'core'], function(tpl, core) {
        //$('#container').html(tpl('member_info'));
            $('.login-btn').click(function() {
                    var uniacid = '<?php  echo $_W['uniacid'];?>';
                    // if(uniacid!=18)
                    // {
                    //     if(!$('#username').isMobile()){
                    //         core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Please enter the correct phone number!<?php  } else { ?>请输入正确手机号码!<?php  } ?>');
                    //         return;
                    //     }
                    // }
                  
                  if( $('#password').isEmpty()){
                        console.log('123')
                       core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Please enter password!<?php  } else { ?>请输入密码!<?php  } ?>');
                       return;
                  }
                  
                    core.json('member/login', {
                       'memberdata':{
                            'mobile': $('#username').val(),
                            'password': $('#password').val()
                           } 
                       }, function(json) {
                        console.dir(json);
                        if(json.status==1){
                             core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Please enter password!<?php  } else { ?>Login successful<?php  } ?>');
                             window.location.href="<?php  echo $this->createMobileUrl('order')?>";
                            //  window.open('');
                        }
                        else{
                            core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>The user does not exist or the password is wrong!<?php  } else { ?>用户不存在或密码错误!<?php  } ?>');
                        }

                    },true,true);
                });


    })

    </script>
<?php  } ?>
</div>
</body>
</html>
