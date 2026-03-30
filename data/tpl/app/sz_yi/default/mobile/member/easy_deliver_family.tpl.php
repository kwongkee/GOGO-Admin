<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template("common/easy_delivery_header", TEMPLATE_INCLUDEPATH)) : (include template("common/easy_delivery_header", TEMPLATE_INCLUDEPATH));?>
<link rel="stylesheet" href="../addons/sz_yi/static/css/register.css">
<body>
<style>
    .register img{
        width: 28px;
        height: 28px;
    }
</style>
<header>
    <div class="logo"><img src="../addons/sz_yi/static/images/logo.png" alt=""></div>
    <div class="plane"><img src="../addons/sz_yi/static/images/plane.png" alt=""></div>
</header>
<div class="warp">
    <div class="content">
        <form action="">
            <div class="register">
                <ul>
                    <li class="reg-pic"><img src="../addons/sz_yi/static/images/res_01.png" alt=""></li>
                    <li><input type="text" name="name" id="name" placeholder="请输入真实姓名"></li>
                </ul>
                <ul>
                    <li class="reg-pic"><img src="../addons/sz_yi/static/images/res_02.png" alt=""></li>
                    <li><input type="text" name="idcard" id="idcard" placeholder="请输入身份证号"></li>
                </ul>
                <ul>
                    <li class="reg-pic"><img src="../addons/sz_yi/static/images/res_03.png" alt=""></li>
                    <li><input type="text" value="<?php  echo $mobile;?>" <?php  if($mobile) { ?>readonly="readonly"<?php  } ?> name="tel" id="tel" placeholder="请输入手机号"></li>
                </ul>
                <?php  if($pwd) { ?>
                    <input type="hidden" value="<?php  echo $pwd;?>" name="pwd" id="pwd">
                <?php  } else { ?> 
                    <ul>
                        <li class="reg-pic"><img src="../addons/sz_yi/static/images/res_04.png" alt=""></li>
                        <li><input type="password" name="pwd" id="pwd" placeholder="请输入登录密码"></li>
                    </ul>
                    <ul>
                        <li class="reg-pic"><img src="../addons/sz_yi/static/images/res_04.png" alt=""></li>
                        <li><input type="password" name="pwd" id="pwd2" placeholder="请输入确认密码"></li>
                    </ul>
                <?php  } ?>
                <input type="hidden" value="<?php  if($mobile) { ?>1<?php  } else { ?>0<?php  } ?>" name="nocheck" id="nocheck">
                
                <?php  if(!$mobile) { ?>
                <ul>
                    <li class="reg-pic"><img src="../addons/sz_yi/static/images/res_04.png" alt=""></li>
                    <li>
                        <input type="text" name="code" id="code" placeholder="请输入验证码"
                               style="width: 70%;margin-top: 5px;"></li>
                    <li style="float: right;">
                        <button id="send" type="button" class="zbox-btn zbox-btn-blue zbox-btn-outlined reg-red">获取验证码
                        </button>
                    </li>
                </ul>
                <?php  } ?>
                <ul>
                    <li class="reg-pic"><img src="../addons/sz_yi/static/images/res_05.png" alt=""></li>
                    <li><input type="text" name="address" id="address" placeholder="XX省XX市XXX区xxxx街道xx路xx号"></li>
                </ul>
                <ul>
                    <li class="reg-pic"><img src="../addons/sz_yi/static/images/res_06.png" alt=""></li>
                    <li style="width: 70%;">
                        <select style="width: 100%" name="bind" id="bind">
                            <option value="">请选择</option>
                            <option value="父亲">父亲</option>
                            <option value="母亲">母亲</option>
                            <option value="爷爷">爷爷</option>
                            <option value="奶奶">奶奶</option>
                            <option value="外公">外公</option>
                            <option value="外婆">外婆</option>
                            <option value="哥哥">哥哥</option>
                            <option value="妹妹">妹妹</option>
                            <option value="老公">老公</option>
                            <option value="老婆">老婆</option>
                            <option value="儿子">儿子</option>
                            <option value="舅舅">舅舅</option>
                            <option value="舅妈">舅妈</option>
                            <option value="姑姑">姑姑</option>
                            <option value="表哥">表哥</option>
                            <option value="表姐">表姐</option>
                            <option value="表妹">表妹</option>
                            <option value="兄弟">兄弟</option>
                        </select>
                    </li>
                </ul>
            </div>
            <div style="width: 100%; margin: 0px auto; padding: 10px 0 30px; display: inline;">
                        	<div style="float: left; width: 10%; text-align: center; margin-right: 10px; margin-top: 16px; margin-left: 10px;"><img src="https://shop.gogo198.cn/addons/sz_yi/template/pc/default/static/images/tip.png" alt="" width="32px"></div>
                        	<div style="display: inline; width: 80%;"><span style="color: #1C7AB7; font-size: 16px;">会员注册提示：</span><br/>为提升阁下的购物体验，强烈推荐使用微信号注册及实名认证！如需客服，请致电：0757-86329911。</div>
                        </div>
            <div class="reg-btn">
                <button id="verif" type="button">绑定</button>
            </div>
        </form>
    </div>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template("common/easy_deliver_footer", TEMPLATE_INCLUDEPATH)) : (include template("common/easy_deliver_footer", TEMPLATE_INCLUDEPATH));?>

<script>
    var InterValObj; //timer变量，控制时间
    var count = 60; //间隔函数，1秒执行
    var curCount;//当前剩余秒数
    $('#send').bind("click", function () {
        var tel = $('#tel').val();
        if (tel == ""||tel===null) {
            $.DialogByZ.Alert({
                Title: "提示", Content: "请输入手机号", BtnL: "确定", FunL: function () {
                    $.DialogByZ.Close()
                }
            });
            return;
        }
        curCount = count;
        $.post("<?php  echo $this->createMobileUrl('member/sendcode');?>",{mobile:tel},function (res) {
            res = JSON.parse(res);
            if (res.status == 1) {
                //设置button效果，开始计时
                $(this).attr("disabled", "true");
                $(this).html(curCount + "秒后获取");
                InterValObj = window.setInterval(SetRemainTime, 1000); //启动计时器，1秒执行一次
                //向后台发送处理数据
            } else {
                $.DialogByZ.Alert({Title:"",Content:res.result});
            }
        })
    });

    //timer处理函数
    function SetRemainTime() {
        if (curCount == 0) {
            window.clearInterval(InterValObj);//停止计时器
            $("#send").removeAttr("disabled");//启用按钮
            $("#send").html("发送验证码");
        } else {
            curCount--;
            $("#send").html(curCount + "秒后获取");
        }
    }

    /*提示框*/
    $("#verif").click(function () {
        var name = $('#name').val();
        var idcard =$('#idcard').val();
        var phone = $('#tel').val();
        var code =$('#code').val();
        var address =$('#address').val();
        var bind = $('#bind').val();
        var nocheck = $('#nocheck').val();
        var pwd = $('#pwd').val();
        var pwd2 = $('#pwd2').val();
        $.DialogByZ.Loading('../addons/sz_yi/static/images/loading.png');
        $('#verif').attr('disabled','true');
        if(nocheck == 0)
        {
            if( pwd2 != pwd )
            {
                $.DialogByZ.Alert({Title: "", Content: '两次密码不一致！'});
                $("#verif").removeAttr("disabled");
                return false;
            }
        }
        
        $.post("<?php  echo $this->createMobileUrl('member/easy_deliver_family');?>",{
            name:name,
            idcard:idcard,
            phone:phone,
            code:code,
            address:address,
            bind:bind,
            nocheck:nocheck,
            pwd:pwd
        },function (res) {
            $.DialogByZ.Close();
            res = JSON.parse(res);
            $.DialogByZ.Alert({Title: "", Content: res.result});
            $("#verif").removeAttr("disabled");//启用按钮
        });
    });


</script>
</body>
</html>