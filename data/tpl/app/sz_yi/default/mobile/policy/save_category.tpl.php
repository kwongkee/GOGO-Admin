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
</style>
<div class="page_head">
    <div class="left" onclick="javascript:window.history.back(-1);">
        <div class="back"></div>
        <div style="font-size:15px;padding-top:2px;">返回</div>
    </div>
</div>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">分类<?php  if($id>0) { ?>编辑<?php  } else { ?>添加<?php  } ?></div>
        <div class="layui-card-body" style="padding: 0px;">
            <form class="layui-form" action="" lay-filter="component-form-demo1">
                <input type="hidden" name="id" value="<?php  echo $id;?>">

                <div class="layui-form-item">
                    <label class="layui-form-label">分类名称</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="text" name="category_name" lay-verify="required" placeholder="请输入分类名称" autocomplete="off" class="layui-input" value="<?php  echo $data['category_name'];?>">
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
            var $ = layui.$
                , layer = layui.layer
                , form = layui.form
                , element = layui.element
                , laydate = layui.laydate
                , upload = layui.upload
                , table = layui.table;

            form.render(null, 'component-form-demo1');

            /* 监听提交 */
            form.on('submit(component-form-demo1)', function(data){

                $.ajax({
                    url:"<?php  echo $this->createMobileUrl('policy/index');?>&op=save_category&edit=1&id=<?php  echo $id;?>",
                    method:'post',
                    data:data.field,
                    dataType:'JSON',
                    success:function(res){
                        layer.msg(res.result.msg,{time:2000}, function () {
                            if(res.status == 1)
                            {
                                window.history.back(-1);
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

    function openWindow()
    {
        //新增分类
        window.location.href="<?php  echo $this->createMobileUrl('policy/index');?>&op=save_category";
    }
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>