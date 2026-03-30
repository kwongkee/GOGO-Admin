<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<title>实名认证</title>
<style>
    body,.layui-footer{background:<?php  echo $website['color'];?>;}
    .layui-btn{background:<?php  echo $website['color'];?>;color:<?php  echo $website['color_word'];?>;border:1px solid <?php  echo $website['color_word'];?>;}
    .layui-table td, .layui-table th{ text-align: center;}
    .layui-table th{ background-color: #ecf6fc; }
    .required{color: red;font-size: 1.3rem;right: 3px;position: relative;top: 7px;}
    .icon-right{font-size: 20px;line-height: 20px;padding-right: 10px;vertical-align: middle;}
    .layui-layer-adminRight{top : 0px !important;}
    .layui-layer-btn .layui-layer-btn0{border-color: #F7931E!important;background-color: #F7931E!important;}
    .layui-table-cell{padding:0 2px;}
    .laytable-cell-1-0-2{height:auto;min-height:auto;}
    .page_head{width:100%;background:#fff;margin-bottom:5px;box-shadow:0 0 4px #ddd;padding:10px 0 10px;}
    .page_head .left{width:20%;display:flex;align-items:center;font-size:15px;}
    .page_head .left .back{width:13px;height:13px;border-top:2px solid #000;border-left:2px solid #000;transform:rotate(-50deg);margin-left:15px;margin-right:5px;}

    .layui-layer-hui .layui-layer-content{color:#fff;}
    .layui-btn{background:<?php  echo $website['color'];?>;color:<?php  echo $website['color_word'];?>;border:1px solid <?php  echo $website['color_word'];?>;}
</style>
<!--<div class="page_head">-->
<!--    <div class="left" onclick="javascript:window.history.back(-1);">-->
<!--        <div class="back"></div>-->
<!--        <div style="font-size:15px;padding-top:2px;">返回</div>-->
<!--    </div>-->
<!--</div>-->

<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <form class="layui-form" action="" lay-filter="component-form-demo1">
                <div class="layui-card">
                <div class="layui-card-header">
                    <p>实名认证</p>
                </div>
                <div class="layui-card-body">
                    <?php  if(empty($_SESSION['account']['phone'])) { ?>
                        <input type="text" name="reg_method" id="reg_method" value="1" style="display:none;"/>
                        <div class="layui-form-item">
                            <label class="layui-form-label">手机号码</label>
                            <div class="layui-input-block layui-select-fscon">
                                <input type="number" class="layui-input" value="" placeholder="请输入手机号码" id="phone" name="phone">
                            </div>
                        </div>
                    <?php  } ?>
                    <?php  if(empty($_SESSION['account']['email'])) { ?>
                        <input type="text" name="reg_method" id="reg_method" value="2" style="display:none;"/>
                        <div class="layui-form-item">
                            <label class="layui-form-label">电子邮箱</label>
                            <div class="layui-input-block layui-select-fscon">
                                <input type="text" class="layui-input" value="" placeholder="请输入电子邮箱" id="email" name="email">
                            </div>
                        </div>
                    <?php  } ?>
                    <?php  if(empty($_SESSION['account']['phone']) || empty($_SESSION['account']['email'])) { ?>
                        <div class="layui-form-item">
                            <label class="layui-form-label">验证码</label>
                            <div class="layui-input-block layui-select-fscon disf">
                                <input type="text" class="layui-input" value="" placeholder="请输入验证码" id="code" name="code">
                                <div class="layui-btn btn-sm btn-send" onclick="send_code()" id="sendCode">发送</div>
                            </div>
                        </div>
                    <?php  } ?>
                    <div class="layui-form-item">
                        <label class="layui-form-label">真实姓名</label>
                        <div class="layui-input-block layui-select-fscon disf">
                            <input type="text" class="layui-input" value="<?php  echo $_SESSION['account']['realname'];?>" placeholder="请输入您的真实姓名" id="realname" name="realname" lay-verify="required">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">身份证号</label>
                        <div class="layui-input-block layui-select-fscon disf">
                            <input type="text" class="layui-input" value="<?php  echo $_SESSION['account']['idcard'];?>" placeholder="请输入您的身份证号" id="idcard" name="idcard"  lay-verify="required">
                        </div>
                    </div>
                </div>
            </div>
                <div class="layui-form-item layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;text-align:center;">
                            <button class="layui-btn" lay-submit="" lay-filter="component-form-demo">提交验证</button>
<!--                            <button type="reset" class="layui-btn layui-btn-primary">重置</button>-->
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    layui.use(['layer', 'form', 'table', 'upload', 'laydate'], function () {
        var $ = layui.$
            , layer = layui.layer
            , form = layui.form
            , element = layui.element
            , laydate = layui.laydate
            , upload = layui.upload
            , table = layui.table;

        form.on('submit(component-form-demo)',function(data){
            $.ajax({
                url:"./index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=account_reg",
                method:'post',
                data:data.field,
                dataType:"json",
                success:function(res){
                    if(res.status==0){
                        $.ajax({
                            url: 'https://decl.gogo198.cn/api/auth_verify',
                            method: 'post',
                            data: {
                                'mobile': res.result.data[0],
                                'idcard': res.result.data[2],
                                'realname': res.result.data[1],
                                'reg_type':2,
                                'is_merch':1
                            },
                            dataType: 'JSON',
                            success: function (rres) {
                                $.ajax({
                                    url: 'https://decl.gogo198.cn/api/record_merch',
                                    method: 'post',
                                    data: {
                                        'id':"<?php  echo $_SESSION['account']['id'];?>",
                                        'form': rres,
                                    },
                                    dataType: 'JSON',
                                    success: function (rres2) {
                                        window.location.href=rres2.url;
                                    }
                                });
                            }
                        });
                    }else if(res.status==1){
                        window.location.reload();
                    }

                    // if($open==1){
                    //     var layer = layui.layer;
                    //     var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                    //     parent.layer.close(index);
                    //     // window.open("about:blank","_self").close();
                    // }
                }
            });
            return false;
        });
    });

    //倒计时
    var n=60;
    function timers(){
        n-=1;
        if(n==0){
            n=60;
            $("#sendCode").html('发送');
        }else{
            $("#sendCode").html(n+"重试");
            setTimeout(function () {
                timers();
            },1000);
        }
    }

    function send_code(){
        let val = $('#reg_method').val();
        let number = '';

        if(val==1){
            number = $('input[name="phone"]').val();
            if(number==''){
                alert('手机格式错误');return false;
            }
        }else if(val==2){
            number = $('input[name="email"]').val();
            var myreg=/^([a-zA-Z]|[0-9])(\w|\-)+@[a-zA-Z0-9]+\.([a-zA-Z]{2,4})$/; //邮箱正则
            if (!myreg.test(number)){
                alert('邮箱格式错误');return false;
            }
        }
        if(n==60){
            timers();
            $.ajax({
                url: "./index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=send_code2",
                method: 'post',
                data: {'code_type':val,'number':number,'islogin':1},
                dataType: 'JSON',
                success: function (res) {
                    if(res.status==-1){
                        alert(res.result.msg);
                        return false;
                    }else{

                    }
                },
                error: function (data) {

                }
            });
        }
    }
</script>