<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<link rel="stylesheet" type="text/css" href="https://decl.gogo198.cn/customs/css/login.css">
<link rel="stylesheet" type="text/css" href="https://decl.gogo198.cn/customs/css/common.css">
<style>
    body, html, div, blockquote, img, label, p, h1, h2, h3, h4, h5, h6, pre, ul, ol, li, dl, dt, dd, form, a, fieldset, input, th, td{margin: 0;padding: 0;border: 0;outline: none;text-decoration: none;}
    .login_user:first-child{margin-top:22px;}
    .login_user{margin-top: 10px;}
    @media screen and (max-width: 500px){
        .login_logo img{width: 100% !important;}
    }
    @media screen and (max-width: 1920px){
        .login_logo img{width: 567px;}
    }
    @media screen and (max-width: 1680px) and (min-width: 1440px){
        .login_logo img{width: 567px;}
    }
    @media screen and (max-width: 1439px) and (min-width: 1024px){
        .login_logo img{width: 567px;}
    }
    .login{
        display: block;
        height: 100vh;
        min-height:100vh;
    }
    .reg:hover{
        color: blue;
        display: inline-block;
        border: 1px solid #e0a800;
    }
    .regist{
        display: none;
    }
    .form2 img{width:22px;height:20px;}

    .mark{width:80%;white-space: pre-wrap;}
</style>

<div class="login">
    <div class="login_logo">
        <img src="../addons/sz_yi/static/warehouse/warehouse_logo.png" alt="">
    </div>

    <div class="login_bg">

        <h3 class="login_title"><a href="javascript:void(0);" das="login" class="reg">登录</a> | <a href="javascript:void(0);" das="reg" class="reg">注册</a> </h3>

        <div class="logins">

            <form action="" class="form1">
                <div class="login_user"><img src="https://decl.gogo198.cn/customs/Images/login_03.png" alt=""><span>|</span>
                    <input type="text" name="acc" id="acc"  placeholder="请输入手机号码">
                </div>

                <div style="width: 100%;display: inline-flex;">
                    <div class="login_user"><img src="https://decl.gogo198.cn/centralize/pwd.png" alt="" style="width:22px;height:20px;"><span>|</span>
                        <input type="password" name="acc_pwd" id="acc_pwd" placeholder="请输入密码">
                    </div>
                </div>
                <div style="width:80%;margin:10px 0 0 20px;text-align:right;">
                    <a href="./index.php?i=3&c=entry&p=login&do=warehouse&m=sz_yi&op=forget">忘记密码？</a>
                </div>
                <div class="login_btn" style="text-align: center;"  onclick="login();"><a href="#">立即登录</a></div>
            </form>

        </div>



        <!--注册-->
        <div class="regist">

            <form class="form2" action="">
                <div class="login_user"><img src="https://decl.gogo198.cn/customs/Images/login_03.png" alt=""><span>|</span>
                    <input type="text" name="name" id="name"  placeholder="请输入姓名">
                </div>

                <div style="width: 80%;display: flex;margin:10px 0 0 20px;align-items: center;justify-content: space-between;">
                    <select name="type" id="type" style="height: 28px;width: 100%;">
                        <option value="1">国内仓库管理员</option>
                        <option value="2">香港仓库管理员</option>
                        <option value="3">国外仓库管理员</option>
                    </select>
                </div>

                <div style="width: 80%;display: flex;margin:10px 0 0 20px;align-items: center;justify-content: space-between;">
                    <select name="warehouse_id" id="warehouse_id" style="height: 28px;width: 100%;">
                        <?php  if(is_array($warehouse_list)) { foreach($warehouse_list as $k => $v) { ?>
                        <option value="<?php  echo $v['id'];?>"><?php  echo $v['warehouse_name'];?></option>
                        <?php  } } ?>
                    </select>
                </div>

                <div class="login_user"><img src="https://decl.gogo198.cn/centralize/pwd.png" alt=""><span>|</span>
                    <input type="password" name="pwd" id="pwd"  placeholder="请输入密码">
                </div>

                <div class="login_user"><img src="https://decl.gogo198.cn/centralize/pwd.png" alt=""><span>|</span>
                    <input type="password" name="pwd_confirm" id="pwd_confirm"  placeholder="请确认密码">
                </div>

                <div class="login_user"><img src="https://decl.gogo198.cn/centralize/tel.png" alt=""><span>|</span>
                    <input type="text" name="mobile" id="mobile"  placeholder="请输入手机号码">
                </div>


                <div style="width: 100%;display: inline-flex;">
                    <div class="login_code" style="margin-top: 10px;"><img src="https://decl.gogo198.cn/customs/Images/login_04.png" alt=""><span>|</span>
                        <input type="text" name="code" id="yzms" maxlength="6" placeholder="请输入验证码" style="top:-5px;">
                    </div>
                    <input type="button" class="login_countdown" value="获取验证码" onclick="sendCode(this,'reg');" style="margin-top: 10px;">
                </div>

                <div class="login_btn" style="text-align: center;"  onclick="register();"><a href="#">立即注册</a></div>

            </form>

        </div>

    </div>
</div>

<script>

    $('.reg').click(function(e) {
        var das = e.currentTarget.attributes.das.nodeValue;
        if(das == 'login') {
            $('.login_bg').css({height:'262px'});
            $('.logins').css({display:'block'});
            $('.regist').css({display:'none'});
        } else if(das == 'reg') {
            $('.login_bg').css({height:'420px'});
            $('.logins').css({display:'none'});
            $('.regist').css({display:'block'});
        }
    });

    var countdown=60;
    function settime(val) {
        if (countdown == 0) {
            val.removeAttribute("disabled");
            val.value="获取验证码";
            countdown = 60;
        } else {
            val.setAttribute("disabled", true);
            val.value="重新发送(" + countdown + ")";
            countdown--;
            setTimeout(function() {
                settime(val)
            },1000)
        }
    }

    function sendCode(obj,type) {
        var tel = $('.regist').find("#mobile").val();

        if (tel==""){
            myalert("请输入手机号码");
            return false;
        }

        // 发送成功倒计时
        $.ajax({
            url:"./index.php?i=3&c=entry&p=login&do=warehouse&m=sz_yi&op=sendcode",
            type:"POST",
            dataType:"json",
            data:{
                "mobile":tel,
            },
            success:function (res) {
                if(res.status == 0) {
                    myalert(obj);
                }
                myalert('发送成功');
                // myalert(res.result.msg);
            },
            error:function (xhr) {
                var res = JSON.parse(xhr.responseText);
                if(res.result.msg=='The given data was invalid.'){
                    myalert("验证码错误");
                }else{
                    myalert("发送失败");
                }
            }
        })
    }

    // 注册
    function register(){

        var data = $('.form2').serializeArray();
        var obj  = serize(data);

        if(obj.name == '') {
            myalert("请输入姓名");
            return false;
        }
        if(obj.pwd == '') {
            myalert("请输入密码");
            return false;
        }
        if(obj.pwd_confirm == '') {
            myalert("请输入确认密码");
            return false;
        }
        if(obj.pwd != obj.pwd_confirm){
            myalert("2次密码不相同，请重新输入");
            return false;
        }

        // if(!checkPhone(obj.mobile)) {
        //     myalert('手機號碼格式不正確！');
        //     return false;
        // }
        if(obj.code == '') {
            myalert("请输入手机验证码");
            return false;
        }

        $.ajax({
            url:'./index.php?i=3&c=entry&p=login&do=warehouse&m=sz_yi&op=register',
            type:'POST',
            dataType:'json',
            data:{
                'data':obj,
            },
            success:function(e) {
                if(e.status == 1) {
                    myalert(e.result.msg);
                    setTimeout(function(){
                        window.location.reload();
                    },1500);
                } else {
                    myalert(e.result.msg);
                }
            },
            error:function(e) {

            }
        })
    }

    // 将表单转换成对象
    function serize(e){
        var o = {};
        $.each(e, function() {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    }

    function checkPhone(phone){
        if(!(/^1[34578]\d{9}$/.test(phone))){
            return false;
        }
        return true;
    }

    // 登陆操作
    function login() {

        var fm = $('.form1').serializeArray();
        var obj = serize(fm);

        if(obj.acc == '') {
            myalert("请输入手机号码");
            return false;
        }

        if(obj.acc_pwd == '') {
            myalert("请输入密码");
            return false;
        }

        $.ajax({
            url:"./index.php?i=3&c=entry&p=login&do=warehouse&m=sz_yi&op=login",
            type:"POST",
            dataType:'json',
            data: {
                'acc': obj.acc,
                'acc_pwd': obj.acc_pwd,
            },
            success:function (res) {
                myalert(res.result.msg);
                if ( res.status == 1 ){
                    window.location.href='./index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=display';
                }
                return false;
            },
            error:function (error) {
                if (error.status==404||error.status==500){
                    myalert("系统错误");
                }
                return false;
            }
        });
        return false;
    }

    function myalert(str) {
        var div = '<div class="mark" style="background: black;"></div>';
        $('body').append(div)
        $('.mark').html(str);
        $('.mark').show();
        setTimeout(function() {
            $('.mark').hide();
            $('.mark').remove();
        }, 2000)
    }
</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>