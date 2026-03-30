<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<title>买家注册</title>
<style>
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
    .disf{display:flex;align-items: center;}
    .user_tel,.user_email{display:none;}
    .show{display:block;}
    .address_country .layui-form-select{width:50% !important;}
</style>
<div class="page_head">
    <div class="left" onclick="javascript:window.history.back(-1);">
        <div class="back"></div>
        <div style="font-size:15px;padding-top:2px;">返回</div>
    </div>
</div>
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">
            买家注册
        </div>
        <div class="layui-card-body" style="padding: 0px;">
            <form class="layui-form" action="" lay-filter="component-form-demo1">
                <input type="number" name="id" value="<?php  echo $id;?>" style="display: none;">
                <div class="layui-form-item">
                    <label class="layui-form-label">真实姓名</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="text" name="realname" lay-verify="required" placeholder="请输入真实姓名" autocomplete="off" class="layui-input" value="<?php  echo $info['realname'];?>">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">证件号码</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="text" name="idcard" lay-verify="required" placeholder="请输入证件号码" autocomplete="off" class="layui-input" value="<?php  echo $info['idcard'];?>">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">收件地址</label>
                    <div class="layui-input-block layui-select-fscon">
                        <div class="disf address_country">
                            <input type="text" name="address" lay-verify="required" placeholder="请输入收件地址" autocomplete="off" class="layui-input" value="<?php  echo $info['address'];?>">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">联系电话</label>
                    <div class="layui-input-block layui-select-fscon">
                        <div class="disf">
                            <input type="text" name="mobile" id="mobile" lay-verify="required" placeholder="请输入联系电话" autocomplete="off" class="layui-input" value="<?php  echo $info['mobile'];?>">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">验证码</label>
                    <div class="layui-input-block layui-select-fscon">
                        <div class="disf">
                            <input type="text" class="layui-input" name="code" id="code" value="">
                            <div class="layui-btn layui-btn-primary" onclick="sendYzm(this);return false;" id="sendCode">发送验证码</div>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">身份证正面</label>
                    <div class="layui-input-block layui-select-fscon">
                        <div class="layui-upload" style="text-align:left;">
                            <button type="button" class="layui-btn pic_file_active" id="pic_list-upload">上传</button>
                            <blockquote class="layui-elem-quote layui-quote-nm yulan" style="margin-top: 10px;">
                                预览图：
                                <div class="layui-upload-list" id="pic_list-upload-list">
                                    <?php  if(!empty($info['idcard_z'])) { ?>
                                    <div style="display: inline-block;margin-top:10px;overflow:visible;">
                                        <img onclick="seePic(this);" src="<?php  echo $info['idcard_z'];?>" class="layui-upload-img"  style="width:30px;">
                                        <button type="button" onclick="delPic(this);" class="layui-btn layui-btn-xs layui-btn-danger" style="position: relative;left: -20px;top: -13px;">删除</button>
                                        <input type="hidden" name="pic_file[0]" value="<?php  echo $info['idcard_z'];?>">
                                    </div>
                                    <?php  } ?>
                                </div>
                            </blockquote>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">身份证反面</label>
                    <div class="layui-input-block layui-select-fscon">
                        <div class="layui-upload" style="text-align:left;">
                            <button type="button" class="layui-btn pic_file_active" id="pic_list2-upload">上传</button>
                            <blockquote class="layui-elem-quote layui-quote-nm yulan" style="margin-top: 10px;">
                                预览图：
                                <div class="layui-upload-list" id="pic_list2-upload-list">
                                    <?php  if(!empty($info['idcard_f'])) { ?>
                                    <div style="display: inline-block;margin-top:10px;overflow:visible;">
                                        <img onclick="seePic(this);" src="<?php  echo $info['idcard_f'];?>" class="layui-upload-img"  style="width:30px;">
                                        <button type="button" onclick="delPic(this);" class="layui-btn layui-btn-xs layui-btn-danger" style="position: relative;left: -20px;top: -13px;">删除</button>
                                        <input type="hidden" name="pic_file2[0]" value="<?php  echo $info['idcard_f'];?>">
                                    </div>
                                    <?php  } ?>
                                </div>
                            </blockquote>
                        </div>
                    </div>
                </div>

                <div class="layui-form-item layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;text-align:center;">
                            <button class="layui-btn" lay-submit="" lay-filter="component-form-demo">立即提交</button>
                            <!--<button type="reset" class="layui-btn layui-btn-primary">重置</button>-->
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

        form.render(null, 'component-form-demo1');

        upload.render({
            elem: '#pic_list-upload'
            ,url: "./index.php?i=3&c=entry&do=util&m=sz_yi&p=uploader"
            ,accept: 'image'
            ,data: {file: "pic_list-upload",'op':'uploadDeclFile'}
            ,multiple: false
            ,number:1
            ,done: function(res,indexs,upload){
                layer.closeAll('loading'); //关闭loading
                if(res.status == 'success')
                {
                    // onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png"
                    var length = $('#pic_list-upload-list').children().length;
                    $('#pic_list-upload-list').append('<div style="display: inline-block;margin-top:10px;overflow:visible;"><img onclick="seePic(this);" src="'+ res.url +'" class="layui-upload-img"  style="width:30px;"><button type="button" onclick="delPic(this);" class="layui-btn layui-btn-xs layui-btn-danger" style="position: relative;left: -20px;top: -13px;">删除</button><input type="hidden" name="pic_file['+length+']" value="'+res.url+'"></div>');
                }else{
                    layer.msg(res.message,{time:3000});
                }
            }
        });

        upload.render({
            elem: '#pic_list2-upload'
            ,url: "./index.php?i=3&c=entry&do=util&m=sz_yi&p=uploader"
            ,accept: 'image'
            ,data: {file: "pic_list2-upload",'op':'uploadDeclFile'}
            ,multiple: false
            ,number:1
            ,done: function(res,indexs,upload){
                layer.closeAll('loading'); //关闭loading
                if(res.status == 'success')
                {
                    // onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png"
                    var length = $('#pic_list2-upload-list').children().length;
                    $('#pic_list2-upload-list').append('<div style="display: inline-block;margin-top:10px;overflow:visible;"><img onclick="seePic(this);" src="'+ res.url +'" class="layui-upload-img"  style="width:30px;"><button type="button" onclick="delPic(this);" class="layui-btn layui-btn-xs layui-btn-danger" style="position: relative;left: -20px;top: -13px;">删除</button><input type="hidden" name="pic_file2['+length+']" value="'+res.url+'"></div>');
                }else{
                    layer.msg(res.message,{time:3000});
                }
            }
        });

        /* 监听提交 */
        form.on('submit(component-form-demo)', function(data){
            $.ajax({
                url:"./index.php?i=3&c=entry&do=behalf&p=buyer&m=sz_yi&op=register",
                method:'post',
                data:data.field,
                dataType:'JSON',
                success:function(res){
                    layer.msg(res.result.msg,{time:3000}, function () {
                        if(res.status == 0)
                        {
                            window.history.back(-1);
                        }
                    });
                },
                error:function (data) {
                    layer.msg('系统错误',{time:3000});
                }
            });
            return false;
        });
    });

    function delPic(obj)
    {
        var layer = layui.layer,$ = layui.$;

        layer.confirm("确认删除？", {
            btn: ["删除","取消"]
        }, function(){
            $(obj).parent().remove();
            layer.close(layer.index);
        }, function(){

        });
    }

    //手机验证码start
    var n=60;
    function timers(typ){
        n-=1;
        if(n==0){
            n=60;
            if(typ==1){
                $("#sendCode").html("发送验证码");
            }else if(typ==2){
                $("#sendEmailCode").html("发送验证码");
            }
        }else{
            if(typ==1){
                $("#sendCode").html(n+"重试");
            }else if(typ==2){
                $("#sendEmailCode").html(n+"重试");
            }
            setTimeout(function () {
                timers(typ);
            },1000);
        }
    }
    function sendYzm(obj) {
        var tel=$("#mobile").val();
        if(tel==''){
            layer.msg('联系电话不能为空');
            return false;
        }else{
            var myreg=/^[1][3,4,5,7,8][0-9]{9}$/; //手机正则
            if (!myreg.test(tel)){
                layer.msg('手机格式错误');
                return false;
            }else{
                if(n==60){
                    timers(1);
                    $.get("./index.php?i=3&c=entry&do=behalf&m=sz_yi&p=buyer&op=sendcode&mobile="+$("#mobile").val(),function (e) {
                        if(e.status==0){
                            layer.msg(e.result.msg);
                        }else{
                            layer.msg(e.result.msg);
                        }
                    });
                }
            }
        }
        return false;
    }
    //手机验证码end
</script>
