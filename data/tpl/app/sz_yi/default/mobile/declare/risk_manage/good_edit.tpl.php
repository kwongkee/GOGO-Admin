<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>商品编辑</title>
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
    .disf{display:flex;align-items: center;justify-content: space-evenly;}
    .layui-tab-title li{min-width:50px;}
    .layui-table img{max-width:50px;}
    .layui-upload-list img{max-width:50px;}
    .layui-form-label{padding:9px 0;width:90px;}
</style>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">商品编辑</div>
        <div class="layui-card-body" style="padding: 0px;">
            <form class="layui-form" action="" lay-filter="component-form-demo1">
                <input type="hidden" name="itemNo" value="<?php  echo $data['itemNo'];?>">
                <input type="hidden" name="glist" value="<?php  echo $glist;?>">

                <div class="layui-form-item">
                    <label class="layui-form-label">企业商品名称</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="text" name="itemName" lay-verify="required" placeholder="请输入名称" autocomplete="off" class="layui-input" value="<?php  echo $data['gname'];?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">商品编码</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="text" name="gcode" lay-verify="required" placeholder="请输入商品编码" autocomplete="off" class="layui-input" value="<?php  echo $data['gcode'];?>">
                    </div>
                </div>

                <div class="layui-form-item layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;text-align:center;">
                            <button class="layui-btn" lay-submit="" lay-filter="component-form-demo1">立即提交</button>
                            <!--<button type="reset" class="layui-btn layui-btn-primary">重置</button>-->
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript" src="../addons/sz_yi/static/js/layui/layui.js"></script>
<script>
    $(function() {
        layui.use(['layer', 'form', 'table', 'upload', 'laydate'], function () {
            var  $ = layui.$
                ,layer = layui.layer
                , form = layui.form
                ,element = layui.element
                ,laydate = layui.laydate
                ,upload = layui.upload
                , table = layui.table;

            form.render(null, 'component-form-demo1');
            // element.render('breadcrumb', 'breadcrumb');

            /* 监听提交 */
            form.on('submit(component-form-demo1)', function(data){

                $.ajax({
                    url:"<?php  echo $this->createMobileUrl('declare/risk_manage');?>&op=good_edit&pre_batch_num=<?php  echo $batch_num;?>&pa=1",
                    method:'post',
                    data:data.field,
                    dataType:'JSON',
                    success:function(res){
                        layer.msg(res.result.msg,{time:3000}, function () {
                            if(res.status == 1)
                            {
                                var index = parent.layer.getFrameIndex(window.name);
                                parent.layer.close(index);
                                parent.window.location.reload();
                            }else if(res.status==2 ||res.status==3){

                                let dataList = {pre_batch_num:"<?php  echo $batch_num;?>",op:'getThreeInvolveGoods'};
                                $.post("<?php  echo $this->createMobileUrl('declare/risk_manage');?>",dataList,function(res2){
                                    res2 = JSON.parse(res2);
                                    if(res2.status==1){
                                        var index = layer.open({
                                            type: 2,
                                            title: '三涉商品',
                                            content: "<?php  echo $this->createMobileUrl('declare/risk_manage');?>&op=threeInvolveGoods&pre_batch_num=<?php  echo $batch_num;?>",
                                        });
                                        layer.full(index);
                                    }
                                });
                            }
                        });
                    },
                    error:function (data) {
                        layer.msg('系统错误',{time:2000});
                    }
                });
                return false;
            });
        });
    });

    function openWindow(title,url)
    {
        var layer = layui.layer;
        var index = layer.open({
            type: 2,
            title: title,
            content: url
        });
        layer.full(index);
    }
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>