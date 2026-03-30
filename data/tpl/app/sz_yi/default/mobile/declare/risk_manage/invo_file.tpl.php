<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>风险管理</title>
<link href="../addons/sz_yi/static/css/layui.css" rel="stylesheet">
<style>
    .layui-table td, .layui-table th{ text-align: center;}
    .layui-table th{ background-color: #ecf6fc; }
    .required{color: red;font-size: 1.3rem;right: 3px;position: relative;top: 7px;}
    .icon-right{
        font-size: 20px;
        line-height: 20px;
        padding-right: 10px;
        vertical-align: middle;
    }
    .layui-layer-adminRight{
        top : 0px !important;
    }
    .layui-layer-btn .layui-layer-btn0{border-color: #F7931E!important;background-color: #F7931E!important;}
    .layui-table-cell{padding:0 2px;}
    .layui-upload-list img{max-width:50px;}
    .page_head{width:100%;background:#fff;margin-bottom:5px;box-shadow:0 0 4px #ddd;padding:10px 0 10px;}
    .page_head .left{width:20%;display:flex;align-items:center;font-size:15px;}
    .page_head .left .back{width:13px;height:13px;border-top:2px solid #000;border-left:2px solid #000;transform:rotate(-50deg);margin-left:15px;margin-right:5px;}
</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">上传涉证文件</div>
                <div class="layui-card-body" style="padding:0;">
                    <form class="layui-form" lay-filter="component-form-element1">
                        <div class="layui-input-block" style="margin-left:0;margin-top:10px;">
                        <div class="layui-upload" style="text-align:left;">
                            <button type="button" class="layui-btn" id="file-upload">上传文件</button>
                            <blockquote class="layui-elem-quote layui-quote-nm yulan" style="margin-top: 10px;">
                                预览图：
                                <div class="layui-upload-list" id="file-upload-list">
                                    <?php  if(!empty($list['file'])) { ?>
                                        <?php  if(is_array($list['file'])) { foreach($list['file'] as $k => $v) { ?>
                                        <div style="display: inline-block;margin-top:10px;">
                                            <img onclick="seePic(this);" src="/attachment/<?php  echo $v;?>" class="layui-upload-img" onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png">
                                            <button type="button" onclick="delPic(this);" class="layui-btn layui-btn-xs layui-btn-danger" style="position: relative;left: -20px;top: -13px;">删除</button>
                                            <input type="hidden" name="file[<?php  echo $k;?>]" value="<?php  echo $v;?>">
                                        </div>
                                        <?php  } } ?>
                                    <?php  } ?>
                                </div>
                            </blockquote>
                        </div>
                    </div>

                        <div class="layui-form-item" style="margin-top: 15px;text-align: center;">
                            <div>
                                <button class="layui-btn layui-btn-normal submit" lay-submit="" lay-filter="component-form-element" style="background:#F7931E !important;">立即提交</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="../addons/sz_yi/static/js/layui/layui.js"></script>
<script>
    function delPic(obj)
    {
        var layer = layui.layer,$ = layui.$;
        layer.confirm('确认要删除该附件？', {
            btn: ['删除','取消']
        }, function(){
            $(obj).parent().remove();
            layer.closeAll();
        }, function(){

        });
    }

    $(function() {
        layui.use(['layer', 'form', 'table', 'upload', 'laydate'], function () {
            var $ = layui.$
                , layer = layui.layer
                , form = layui.form
                , element = layui.element
                , laydate = layui.laydate
                , upload = layui.upload
                , table = layui.table;
            form.render(null, 'component-form-element1');

            upload.render({
                elem: '#file-upload'
                ,url: 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=util&m=sz_yi&p=uploader'
                ,accept: 'file'
                ,data: {file: "file-upload",'op':'uploadDeclFile'}
                ,multiple: true
                ,before: function(obj){
                    layer.load(); //上传loading
                }
                ,done: function(res){
                    layer.closeAll('loading'); //关闭loading
                    if(res.status == 'success')
                    {
                        var length = $('#file-upload-list').children().length;
                        $('#file-upload-list').append('<div style="display: inline-block;margin-top:10px;"><img onclick="seePic(this);" src="/attachment/'+ res.filename +'" class="layui-upload-img" onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png"><button type="button" onclick="delPic(this);" class="layui-btn layui-btn-xs layui-btn-danger" style="position: relative;left: -20px;top: -13px;">删除</button><input type="hidden" name="file['+length+']" value="'+res.filename+'"></div>')
                    }
                }
            });

            form.on('submit(component-form-element)', function(data){
                if($('#file-upload-list').children().length == 0){
                    layer.msg('请上传涉证文件！',{time:2000});
                    return false;
                }

                layer.load(); //上传loading
                $.ajax({
                    url:"<?php  echo $this->createMobileUrl('declare/risk_manage');?>&op=invo_file&itemNo=<?php  echo $itemNo;?>&pre_batch_num=<?php  echo $batch_num;?>&pa=1&hscode=<?php  echo $hscode;?>",
                    type:'post',
                    dataType:'JSON',
                    data:data.field,
                    success:function(res){
                        layer.closeAll('loading'); //关闭loading
                        if(res.result.is_synchronization==1){
                            //同步
                            layer.confirm('系统检测到还有其它相同编码，需要全部同步为相同文件吗？',{
                                btn:['需要同步','不需要同步']
                            },function(){
                                let dataList = {op:'synchronization',typ:1,pre_batch_num:"<?php  echo $batch_num;?>",hscode:"<?php  echo $hscode;?>"};
                                $.post("<?php  echo $this->createMobileUrl('declare/risk_manage');?>",dataList,function(res){
                                    res = JSON.parse(res);
                                    layer.msg(res.result.msg,{time:2000}, function () {
                                        if(res.status == 1)
                                        {
                                            window.location.reload();
                                        }
                                    });  
                                });
                            },function(){
                                layer.msg(res.result.msg,{time:2000}, function () {
                                    if(res.status == 1)
                                    {
                                        window.location.reload();
                                    }
                                });    
                            });
                        }else{
                            //不需要同步
                            layer.msg(res.result.msg,{time:2000}, function () {
                                if(res.status == 1)
                                {
                                    window.location.reload();
                                }
                            });   
                        }
                    },
                    error:function (data) {
                        layer.msg('系统错误',{time:2000});
                    }
                });

                return false;
            });

            var $ = layui.$, active = {
                reload: function(){
                    //执行重载
                    table.reload('mainTable', {
                        page: {
                            curr: 1 //重新从第 1 页开始
                        }
                        ,where: {
                            keywords: $("#keywords").val()
                        }
                    });
                }
            };
        });
    });

    function openWindow(title,hscode,typ)
    {
        var layer = layui.layer;
        var url = '';
        if(typ==1){
            //查看详情
            var url = "<?php  echo $this->createMobileUrl('hscode/index');?>&op=info2&hscode="+hscode;
            var index = layer.open({
                type: 2,
                title: title,
                content: url,
            });
            layer.full(index);
        }else if(typ==2){

        }

    }
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>