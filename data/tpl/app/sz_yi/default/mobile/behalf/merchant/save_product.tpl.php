<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<title>货品管理</title>
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
            货品管理
        </div>
        <div class="layui-card-body" style="padding: 0px;">
            <form class="layui-form" action="" lay-filter="component-form-demo1">
                <input type="number" name="id" value="<?php  echo $id;?>" style="display: none;">
                <div class="layui-form-item">
                    <label class="layui-form-label">货品名称</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="text" name="name" lay-verify="required" placeholder="请输入货品名称" autocomplete="off" class="layui-input" value="<?php  echo $list['name'];?>">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">货品主图</label>
                    <div class="layui-input-block layui-select-fscon">
                        <div class="layui-upload" style="text-align:left;">
                            <button type="button" class="layui-btn pic_file_active" id="pic_list-upload">上传(1张)</button>
                            <blockquote class="layui-elem-quote layui-quote-nm yulan" style="margin-top: 10px;">
                                预览图：
                                <div class="layui-upload-list" id="pic_list-upload-list">
                                    <?php  if(!empty($list['main_img'])) { ?>
                                    <div style="display: inline-block;margin-top:10px;overflow:visible;">
                                        <img onclick="seePic(this);" src="<?php  echo $list['main_img'];?>" class="layui-upload-img"  style="width:30px;">
                                        <button type="button" onclick="delPic(this);" class="layui-btn layui-btn-xs layui-btn-danger" style="position: relative;left: -20px;top: -13px;">删除</button>
                                        <input type="hidden" name="pic_file[0]" value="<?php  echo $list['main_img'];?>">
                                    </div>
                                    <?php  } ?>
                                </div>
                            </blockquote>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">货品副图</label>
                    <div class="layui-input-block layui-select-fscon">
                        <div class="layui-upload" style="text-align:left;">
                            <button type="button" class="layui-btn pic_file_active" id="pic_list2-upload">上传(最多5张)</button>
                            <blockquote class="layui-elem-quote layui-quote-nm yulan" style="margin-top: 10px;">
                                预览图：
                                <div class="layui-upload-list" id="pic_list2-upload-list">
                                    <?php  if(!empty($list['detail_img'])) { ?>
                                    <?php  if(is_array($list['detail_img'])) { foreach($list['detail_img'] as $k => $v) { ?>
                                    <div style="display: inline-block;margin-top:10px;overflow:visible;">
                                        <img onclick="seePic(this);" src="<?php  echo $v;?>" class="layui-upload-img"  style="width:30px;">
                                        <button type="button" onclick="delPic(this);" class="layui-btn layui-btn-xs layui-btn-danger" style="position: relative;left: -20px;top: -13px;">删除</button>
                                        <input type="hidden" name="pic_file2[]" value="<?php  echo $v;?>">
                                    </div>
                                    <?php  } } ?>
                                    <?php  } ?>
                                </div>
                            </blockquote>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">详情描述</label>
                    <div class="layui-input-block layui-select-fscon">
                        <textarea name="desc" id="desc" rows="3" placeholder="请输入详情描述" class="layui-textarea"><?php  echo $list['desc'];?></textarea>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">规格型号</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="text" name="option" placeholder="请输入规格型号" autocomplete="off" class="layui-input" value="<?php  echo $list['option'];?>">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">单位售价</label>
                    <div class="layui-input-block layui-select-fscon">
                        <div class="disf">
                            <select name="unit" lay-search style="width:20%;">
                                <?php  if(is_array($unit)) { foreach($unit as $k => $v) { ?>
                                <option value="<?php  echo $v['code_value'];?>" <?php  if($list['unit']==$v['code_value']) { ?>selected<?php  } ?>><?php  echo $v['code_name'];?></option>
                                <?php  } } ?>
                            </select>
                            <input type="number" name="price" placeholder="请输入售价" autocomplete="off" class="layui-input" value="<?php  echo $list['price'];?>">
                        </div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">备注说明</label>
                    <div class="layui-input-block layui-select-fscon">
                        <textarea name="remark" id="remark" rows="3" placeholder="请输入备注说明" class="layui-textarea"><?php  echo $list['remark'];?></textarea>
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
                url:"./index.php?i=3&c=entry&do=behalf&p=merchant&m=sz_yi&op=save_product",
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
