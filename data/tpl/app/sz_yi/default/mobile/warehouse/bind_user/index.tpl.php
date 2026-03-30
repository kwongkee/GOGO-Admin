<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<title>绑定用户</title>
<style>
    .header img{width:100%;}
    div{overflow: visible;}

    .info_box{margin-top:10px;float:left;width:100%;}
    .info_box .title{color:#666;padding:10px 10px;box-sizing: border-box;line-height:35px;}
    .info_box .val{padding:10px 10px;box-sizing: border-box;}
    .info_box .layui-col-xs12{background:#fff;}
    .info_box .line{width:100%;height:1px;background:#eee;}

    .info_box .add_height{line-height: 35px;}

    /**layui框架**/
    .layui-icon-ok:before{content:"√"}
    .layui-form-checkbox{width:18px;height:18px;line-height: 18px;padding-right:18px;margin-right:2px;}
    .layui-form-checkbox i{width:18px;height:18px;border-left:1px solid #d2d2d2;}
    .layui-btn-normal{background:#1790FF;}
    .layui-layer-hui .layui-layer-content{color:#fff;}
</style>

<!--LOGO-->
<div class="header"><img src="https://decl.gogo198.cn/centralize/centralize_logo.png" alt="" class="logo" /></div>
<div class="layui-col-xs12">
    <form class="layui-form" lay-filter="component-form-element">
        <div class="upload_info info_box">
            <div class="layui-col-xs12">
                <div class="layui-col-xs3 title">用户ID</div>
                <div class="layui-col-xs9 val">
                    <input type="text" class="layui-input" lay-verify="required" name="user_id" placeholder="请输入集运用户ID">
                </div>
            </div>
            <div class="line layui-col-xs12"></div>
            <div class="layui-col-xs12">
                <div class="layui-col-xs3 title">邮箱</div>
                <div class="layui-col-xs9 val">
                    <input type="text" class="layui-input" lay-verify="required" name="email" placeholder="请输入集运用户邮箱">
                </div>
            </div>
            <div class="line layui-col-xs12"></div>
            <div class="layui-col-xs12">
                <div class="layui-col-xs3 title add_height">手机号码</div>
                <div class="layui-col-xs9 val">
                    <input type="text" class="layui-input" name="mobile" placeholder="请输入手机号码">
                </div>
            </div>
        </div>

        <div class="info_box info_btn_box" style="margin: 10px 0 20px;text-align: center;">
            <div>
                <button class="layui-btn layui-btn-normal" lay-submit lay-filter="component-form-element1" style="width:90%;">立即绑定</button>
            </div>
        </div>
    </form>
</div>

<script>
    layui.use(['layer', 'form'], function () {
        var $ = layui.$
            , layer = layui.layer
            , form = layui.form;

        form.render(null, 'component-form-element1');

        form.on('submit(component-form-element1)', function(data){
            //layer.msg(JSON.stringify(data.field));

            $.ajax({
                url:"./index.php?i=3&c=entry&p=bind_user&do=warehouse&m=sz_yi&op=bind_user",
                method:'post',
                data:data.field,
                dataType:'JSON',
                success:function(res){
                    layer.msg(res.result.msg,{time:3000}, function () {
                        if(res.status == 0)
                        {
                            window.location.reload();
                            // window.location.href = "./index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=warehouse_goods_up_down"
                        }
                    });
                },
                error:function (data) {
                    layer.msg("系统错误",{time:3000});
                }
            });
            return false;
        });
    });
</script>