<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<link href="../addons/sz_yi/static/css/layui.css" rel="stylesheet">
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
    .layui-select-fscon{padding-top:8px !important;}
    .layui-layout-admin .layui-footer{
        height:55px;
        line-height: 55px;
    }
    .layui-form-item{margin-bottom:0;}
    .disf{display:flex;align-items: center;justify-content: space-evenly;}
    .layui-layer-content{text-align: center;}
</style>
<div class="page_head">
    <div class="left" onclick="javascript:window.history.back(-1);">
        <div class="back"></div>
        <div style="font-size:15px;padding-top:2px;">返回</div>
    </div>
</div>

<div class="layui-fluid">
    <div class="layui-card">
        <!--<div class="layui-card-header"></div>-->
        <div class="layui-card-body" style="padding: 0px;">
            <!--<form class="layui-form" action="" lay-filter="component-form-demo1">-->
                <div class="layui-form-item">
                    <label class="layui-form-label">分类名称</label>
                    <div class="layui-input-block layui-select-fscon">
                        <?php  echo $cate_name;?>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">发文机关</label>
                    <div class="layui-input-block layui-select-fscon">
                        <?php  echo $data['issuing_authority'];?>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">文号</label>
                    <div class="layui-input-block layui-select-fscon">
                        <?php  echo $data['document_number'];?>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">名称</label>
                    <div class="layui-input-block layui-select-fscon">
                        <?php  echo $data['name'];?>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">发布日期</label>
                    <div class="layui-input-block layui-select-fscon">
                        <?php  echo $data['release_date'];?>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">生效日期</label>
                    <div class="layui-input-block layui-select-fscon">
                        <?php  echo $data['effective_date'];?>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">效力</label>
                    <div class="layui-input-block layui-select-fscon">
                        <?php  echo $data['effect'];?>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">效力说明</label>
                    <div class="layui-input-block layui-select-fscon">
                        <?php  echo $data['effect_statement'];?>
                    </div>
                </div>

            <div class="disf" style="margin-top:10px;">
                <div class="layui-btn layui-btn-normal layui-btn-md view">查看原文</div>

                <div class="layui-btn layui-btn-normal layui-btn-md share" style="background:#8440f1;">分享政策</div>
            </div>
                <!--<div class="layui-form-item layui-layout-admin">-->
                    <!--<div class="layui-input-block">-->
                        <!--<div class="layui-footer" style="left: 0;text-align:center;">-->
                            <!---->
                        <!--</div>-->
                    <!--</div>-->
                <!--</div>-->
            <!--</form>-->
        </div>
    </div>
</div>

<script type="text/javascript" src="../addons/sz_yi/static/js/layui/layui.js"></script>
<script>
    $(function() {
        layui.use(['layer', 'form', 'table', 'upload', 'laydate'], function () {
            var $ = layui.$
                , layer = layui.layer
                , form = layui.form
                , element = layui.element
                , laydate = layui.laydate
                , upload = layui.upload
                , table = layui.table;

            // form.render(null, 'component-form-demo1');

            $('.view').click(function(){
                $.ajax({
                    url:"<?php  echo $this->createMobileUrl('policy/column');?>&op=testing",
                    method:'post',
                    data:'',
                    dataType:'JSON',
                    success:function(res){
                        if(res.status==1){
                            layer.msg('正在跳转...');
                            window.location.href="<?php  echo $data['link'];?>";
                        }
                        layer.msg(res.result.msg,{time:2000}, function () {
                            if(res.status == 1)
                            {

                            }else if(res.status == -1){
                                window.location.href="http://shop.gogo198.cn/app/index.php?i=3&c=entry&do=enterprise&m=sz_yi&p=register";
                            }
                        });
                    },
                    error:function (data) {
                        layer.msg('系统错误',{time:2000});
                    }
                });
            });

            $('.share').click(function(){
                layer.load(); //上传loading
                $.ajax({
                    url:"<?php  echo $this->createMobileUrl('policy/column');?>&op=share",
                    method:'post',
                    data:{'id':"<?php  echo $data['id'];?>"},
                    dataType:'JSON',
                    success:function(res){
                        layer.closeAll('loading');
                        layer.msg(res.result.msg,{time:2000}, function () {
                            if(res.status == 1)
                            {
                                var index = layer.open({
                                    type: 1,
                                    title: '保存二维码',
                                    content: '<img src='+res.result.qrcode+' style="width:60%;margin:0 auto;">',
                                    area:['90%','50%']
                                });
                                layer.closeAll('loading');
                                // layer.full(index);
                            }else{
                                layer.closeAll('loading');
                            }
                        });
                    },
                    error:function (data) {
                        layer.msg('系统错误',{time:2000});
                    }
                });
                layer.closeAll('loading');
            });
        });
    });

    function openWindow()
    {
        //新增分类
        window.location.href="<?php  echo $this->createMobileUrl('policy/index');?>&op=save_category";
    }
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>