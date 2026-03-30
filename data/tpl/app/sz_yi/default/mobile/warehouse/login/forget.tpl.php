<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<link rel="stylesheet" type="text/css" href="https://decl.gogo198.cn/customs/css/login.css">
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
        height: 110%;
        max-height: 110%;
        min-height: 100%;
    }
    .login_bg{
        height:400px;
    }
    .reg:hover{
        color: blue;
        display: inline-block;
        border: 1px solid #e0a800;
    }
    .regist{
        display: none;
    }
    .form1 img{width:22px;height:20px;}

    .mark{width:80%;white-space: pre-wrap;}
</style>

<div class="login">

    <div class="login_logo">
        <img src="../addons/sz_yi/static/warehouse/warehouse_logo.png" alt="">
    </div>

    <div class="login_bg">
        <h3 class="login_title" style="text-align: center;padding-left: 0;"><a href="javascript:void(0);" das="login" class="reg">忘记密码</a></h3>

        <div class="logins">
            <form action="" class="form1">

                <div class="login_user"><img src="https://decl.gogo198.cn/centralize/tel.png" alt=""><span>|</span>
                    <input type="text" name="mobile" id="mobile"  placeholder="请输入手机号码">
                </div>

                <div style="width: 100%;display: inline-flex;">
                    <div class="login_code" style="margin-top: 10px;"><img src="https://decl.gogo198.cn/customs/Images/login_04.png" alt=""><span>|</span>
                        <input type="text" name="code" id="yzms" maxlength="6" placeholder="请输入验证码" style="top:-5px;">
                    </div>
                    <input type="button" class="login_countdown" value="获取验证码" onclick="sendCode(this,'forget');" style="margin-top: 10px;">
                </div>

                <div class="login_user"><img src="https://decl.gogo198.cn/centralize/pwd.png" alt=""><span>|</span>
                    <input type="password" name="pwd" id="pwd"  placeholder="请输入密码">
                </div>

                <div class="login_user"><img src="https://decl.gogo198.cn/centralize/pwd.png" alt=""><span>|</span>
                    <input type="password" name="pwd_confirm" id="pwd_confirm"  placeholder="请输入确认密码">
                </div>

                <div class="login_btn" style="text-align: center;"  onclick="forget();"><a href="#">提交</a></div>
                <div class="login_btn" style="text-align: center;margin-top:10px;background:#ccc;"  onclick="javascript:window.history.back();"><a href="#">返回</a></div>
            </form>

        </div>
    </div>
</div>

<script>
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
        var tel = $("#mobile").val();

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
                    settime(obj);
                }
                myalert(res.result.msg);
            },
            error:function (xhr) {
                // var res = JSON.parse(xhr.responseText);
                myalert("发送失败");
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

    function forget(){
        var fm = $('.form1').serializeArray();
        var obj = serize(fm);
        // if(!checkPhone(obj.mobile)) {
        //     myalert('手機號碼格式不正確！');
        //     return false;
        // }
        if(obj.code == '') {
            myalert("请输入手机验证码");
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
        $.ajax({
            url:'./index.php?i=3&c=entry&p=login&do=warehouse&m=sz_yi&op=forget',
            type:'POST',
            dataType:'json',
            data:{
                'data':obj,
                'ispost':1
            },
            success:function(e) {
                if(e.status == 1) {
                    myalert(e.result.msg);
                    setTimeout(function(){
                        window.location.href='./index.php?i=3&c=entry&p=login&do=warehouse&m=sz_yi&op=display';
                    },1500);
                } else {
                    myalert(e.result.msg);
                }
            },
            error:function(e) {

            }
        });
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