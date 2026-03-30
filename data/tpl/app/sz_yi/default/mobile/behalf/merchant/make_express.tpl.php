<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<title>运单制作</title>
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
            运单制作
        </div>
        <div class="layui-card-body" style="padding: 0px;">
            <form class="layui-form" action="" lay-filter="component-form-demo1">
                <input type="number" name="id" value="<?php  echo $id;?>" style="display: none;">
                <div class="layui-form-item">
                    <label class="layui-form-label">物流公司</label>
                    <div class="layui-input-block layui-select-fscon">
                        <select name="express_id" lay-search>
                            <?php  if(is_array($express)) { foreach($express as $k => $v) { ?>
                            <option value="<?php  echo $v['id'];?>" <?php  if($order['express_id']==$v['id']) { ?>selected<?php  } ?>><?php  echo $v['enterprise_name'];?></option>
                            <?php  } } ?>
                        </select>
                    </div>
                </div>

                <div class="layui-form-item layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;text-align:center;">
                            <button class="layui-btn" lay-submit="" lay-filter="component-form-demo">提交制作</button>
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
            ,multiple: true
            ,number:5
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
                url:"./index.php?i=3&c=entry&do=behalf&p=merchant&m=sz_yi&op=make_express",
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
</script>
