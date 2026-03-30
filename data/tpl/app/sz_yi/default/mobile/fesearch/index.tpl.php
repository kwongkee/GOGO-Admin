<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<title><?php  echo $title;?></title>
<style>
    body{background:#f8f8f8;}
    .layui-table td, .layui-table th{ text-align: center;}
    .layui-table th{ background-color: #ecf6fc; }
    .required{color: red;font-size: 1.3rem;right: 3px;position: relative;top: 7px;}
    .icon-right{font-size: 20px;line-height: 20px;padding-right: 10px;vertical-align: middle;}
    .layui-layer-adminRight{top : 0px !important;}
    .layui-layer-btn .layui-layer-btn0{border-color: #F7931E!important;background-color: #F7931E!important;}
    .layui-table-cell{padding:0 2px;}
    .laytable-cell-1-0-2{height:auto;min-height:auto;}
    .page_head{width:100%;background:#fff;margin-bottom:5px;box-shadow:0 0 4px #ddd;padding:10px 0 10px;}
    .page_head .center{text-align: center;}
    .page_head .left{width:20%;display:flex;align-items:center;font-size:15px;}
    .page_head .left .back{width:13px;height:13px;border-top:2px solid #000;border-left:2px solid #000;transform:rotate(-50deg);margin-left:15px;margin-right:5px;}

    .box{display:inline-block;width: 49%;text-align: center;margin-bottom:20px;}
    .box .box2{font-size: 15px;width: 80%;margin: 0 auto;box-shadow: 0px 0px 2px #a7a5a5;border-radius: 5px;box-sizing: border-box;padding:15px 20px;}
    
    .lab{line-height:36px;text-align:center;}
    .con_box{margin-top:10px;}
    
    .layui-layer-hui .layui-layer-content{color:#fff;}
</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <form class="layui-form" action="" lay-filter="component-form-demo1">
                    <div class="layui-form-item layui-col-xs12" style="padding:15px 10px;box-sizing:border-box;margin-bottom:0;background:#fff;">
                        <div class="layui-col-xs12 con_box">
                            <div class="layui-col-xs3 lab">
                                <span>类型</span>
                            </div>
                            <div class="layui-col-xs9">
                                <select name="type" id="type" lay-verify="required" lay-search>
                                    <option value="1">写字楼</option>
                                </select>
                            </div>
                        </div>
                        <div class="layui-col-xs12 con_box">
                            <div class="layui-col-xs3 lab">
                                <span>省份</span>
                            </div>
                            <div class="layui-col-xs9">
                                <select name="province" id="province" lay-verify="required" lay-search>
                                    <option value="1">广东省</option>
                                </select>    
                            </div>
                        </div>
                        <div class="layui-col-xs12 con_box">
                            <div class="layui-col-xs3 lab">
                                <span>城市</span>
                            </div>
                            <div class="layui-col-xs9">
                                <select name="city" id="city" lay-verify="required" lay-search>
                                    <option value="1">佛山市</option>
                                </select>
                            </div>
                        </div>
                        <div class="layui-col-xs12 con_box">
                            <div class="layui-col-xs3 lab">
                                <span>区域</span>
                            </div>
                            <div class="layui-col-xs9">
                                <select name="area" id="area" lay-verify="required" lay-search>
                                    <option value="1">南海区</option>
                                </select>
                            </div>
                        </div>
                        <div class="layui-col-xs12 con_box">
                            <div class="layui-col-xs3 lab">
                                <span>镇街</span>
                            </div>
                            <div class="layui-col-xs9">
                                <select name="town" id="town" lay-verify="required" lay-search>
                                    <option value="1">桂城街道</option>
                                </select>
                            </div>
                        </div>
                        <div class="layui-col-xs12 con_box">
                            <div class="layui-col-xs3" style="height:1px;">
                                
                            </div>
                            <div class="layui-col-xs7">
                                <input type="text" name="name" lay-verify="required" placeholder="请输入写字楼相关信息" autocomplete="off" class="layui-input" value="">
                            </div>
                            <div class="layui-col-xs2">
                                <button class="layui-btn" lay-submit="" lay-filter="component-form-demo1" style="width:60px;">搜索</button>
                            </div>
                        </div>
                    </div>

                    <div class="layui-form-item layui-layout-admin" style="display: none;">
                        <div class="layui-input-block">
                            <div class="layui-footer" style="left: 0;text-align:center;">

                                <!--<button type="reset" class="layui-btn layui-btn-primary">重置</button>-->
                            </div>
                        </div>
                    </div>
                </form>
            </div>
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

        /* 监听提交 */
        form.on('submit(component-form-demo1)', function(data){
            $.ajax({
                url:"./index.php?i=3&c=entry&do=fesearch&p=index&m=sz_yi&op=display&pa=1",
                method:'post',
                data:data.field,
                dataType:'JSON',
                success:function(res){
                    layer.msg(res.msg,{time:3000}, function () {
                        if(res.code == 0)
                        {
                            window.location.reload();
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
</script>