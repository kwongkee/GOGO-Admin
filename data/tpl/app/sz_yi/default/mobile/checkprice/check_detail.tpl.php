<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<title>询价详情</title>
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
    .disf{display:flex;align-items: center;}
    .user_tel,.user_email{display:none;}
    .show{display:block;}
    .layui-input-block{line-height:38px;}
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
            询价详情[<?php  echo $order['ordersn'];?>]
        </div>
        <div class="layui-card-body" style="padding:0;padding-bottom: 10px;">
            <form class="layui-form" action="" lay-filter="component-form-demo1">
                <input type="number" name="id" value="<?php  echo $order['id'];?>" style="display: none;">
                <?php  if(!empty($order['content'])) { ?>
                    <?php  if(is_array($order['form'])) { foreach($order['form'] as $k => $v) { ?>
                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php  echo $v['label_name'];?></label>
                            <div class="layui-input-block layui-select-fscon">
                                <?php  echo $order['content'][$k];?>
                            </div>
                        </div>
                    <?php  } } ?>
                <?php  } ?>
                <?php  if(!empty($order['text'])) { ?>
                    <div class="layui-form-item">
                        <label class="layui-form-label">询价信息</label>
                        <div class="layui-input-block layui-select-fscon">
                            <?php  echo $order['text'];?>
                        </div>
                    </div>
                <?php  } ?>
                <?php  if(!empty($order['files'])) { ?>
                    <div class="layui-form-item">
                        <label class="layui-form-label">客户文件</label>
                        <div class="layui-input-block layui-select-fscon">
                            <?php  if(is_array($order['files'])) { foreach($order['files'] as $k => $v) { ?>
                                <?php  $v['files'] = str_replace('https://www.gogo198.net','',$v['files']);?>
                                <a href="https://www.gogo198.net<?php  echo $v['files'];?>" target="_blank"><?php  echo $v['filenames'];?></a>
                            <?php  } } ?>
                        </div>
                    </div>
                <?php  } ?>
                
                <div class="layui-form-item">
                    <label class="layui-form-label">分享记录</label>
                    <div class="layui-input-block layui-select-fscon">
                        <a href="./index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=share_history&id=<?php  echo $order['id'];?>" target="_blank" style="color:#1790FF;text-decoration:underline;">查看记录</a>
                    </div>
                </div>
                
                <div class="layui-form-item">
                    <label class="layui-form-label">分享报价</label>
                    <div class="layui-input-block layui-select-fscon">
                        <select name="share_quote" id="share_quote" lay-filter="share_quote">
                            <option value="">请选择分享方式</option>
                            <option value="1">分享给现有商户</option>
                            <option value="2">分享给指定人</option>
                            <option value="3">跳转报价页后，右上角分享</option>
                        </select>
                    </div>
                </div>
                
<!--                <div class="layui-form-item layui-layout-admin">-->
<!--                    <div class="layui-input-block">-->
<!--                        <div class="layui-footer" style="left: 0;text-align:center;">-->
<!--                            <button class="layui-btn" lay-submit="" lay-filter="component-form-demo">立即提交</button>-->
<!--                            <button type="reset" class="layui-btn layui-btn-primary">重置</button>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
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

        form.on('select(share_quote)',function(data){
            let val = data.value;
            if(val==1){
                layer.open({
                    type: 2,
                    title: '分享给现有商户',
                    shadeClose: true,
                    shade: 0.3,
                    area: ['90%', '90%'],
                    content: "./index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=share_merchant&id=<?php  echo $order['id'];?>",
                });
            }
            else if(val==2){
                layer.open({
                    type: 2,
                    title: '分享给指定人',
                    shadeClose: true,
                    shade: 0.3,
                    area: ['90%', '90%'],
                    content: "./index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=share_others&id=<?php  echo $order['id'];?>",
                });
            }
            else if(val==3){
                window.location.href="./index.php?i=3&c=entry&do=checkprice&p=quote&m=sz_yi&op=quote_info&id=<?php  echo $order['id'];?>";
            }
        });

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
                url:"./index.php?i=3&c=entry&do=behalf&p=buyer&m=sz_yi&op=manage",
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
