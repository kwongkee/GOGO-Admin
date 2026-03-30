<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
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
    .laytable-cell-1-0-2{height:auto;min-height:auto;}
    /**进度条**/
    .layui-progress{display:none;position: fixed;background: #ffffff;border-radius: 5px;z-index:9999999;transform:translate(-50%,-50%);top: 50%;left:50%;width:80%;}
</style>
<div class="mask"  style="position: fixed; margin: 0px; padding: 0px;background: rgba(0, 0, 0,0.6); z-index: 999999; width: 100%; height: 100%; transition: all 0.2s ease 0s;display:none;left: 0;top: 0;"></div>
<div class="layui-progress" lay-showpercent="true" lay-filter="demo" >
    <p style="color:#fff;text-align:center;margin-top:10px;">系统进行中，请稍后...</p>
    <div class="layui-progress-bar layui-bg-red" lay-percent="0%"></div>
</div>
<div class="layui-card">
    <div class="layui-card-body">
        <form class="layui-form" action="" lay-filter="component-form-element">
            <input type="hidden" name="ids" id="ids" value="<?php  echo $ids;?>">
            <div class="layui-card">
                <div class="layui-card-header">下载缺省清单 <div class="layui-btn layui-btn-normal layui-btn-sm list_down">清单下载</div></div>
                <div class="layui-card-body" style="border-bottom: 1px solid #f6f6f6;">
                    <div class="layui-upload">
                        <button type="button" class="layui-btn layui-btn-normal" id="upload">选择文件</button><input class="layui-upload-file" type="file" accept=".xlsx,.xls,.csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name="file">
                    </div>
                </div>
                <div class="layui-card-body">
                    <div class="layui-upload" style="text-align: center;">
                        <button type="button" class="layui-btn" id="uploadAction">提交</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript" src="../addons/sz_yi/static/js/layui/layui.js"></script>
<script>
    //显示进度条
    function load(){
        var n = 0;
        timer = setInterval(function(){//按照时间随机生成一个小于95的进度，具体数值可以自己调整
            n = n + Math.random()*10|0;
            if(n>95){
                n = 95;
                clearInterval(timer);
            }
            $('.mask').show();
            $('.layui-progress').show();
            $('.layui-progress').find('.layui-progress-bar').css('width',n+'%').text(n+'%');
        }, 50+Math.random()*1000);

        return timer;
    }

    //隐藏进度条
    function hide_load(timer){
        clearInterval(timer);
        $('.layui-progress').find('.layui-progress-bar').css('width','100%').text('100%');
        setTimeout(function(){
            $('.mask').hide();
            $('.layui-progress').find('.layui-progress-bar').css('width','0%').text('0%');
            $('.layui-progress').hide();
        },500);
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
            var timer = '';
            upload.render({
               elem:'#upload',
                url:"<?php  echo $this->createMobileUrl('declare/risk');?>&op=upload_list&type=<?php  echo $type;?>&batch_num=<?php  echo $batch_num;?>",
                auto:false,
                accept:'file',
                exts:'xls|excel|xlsx'
                ,acceptMime: '.xlsx,.xls,.csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ,bindAction: '#uploadAction'
                ,data: {
                    ids: function(){
                        return $('#ids').val();
                    },
                }
                ,before: function(obj){
                    timer = load();
                    // layer.load(); //上传loading
                }
                ,done: function(res){
                    hide_load(timer);
                    // layer.closeAll('loading'); //关闭loading
                    layer.alert(res.result.msg);
                }
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

        $('.list_down').click(function(){
            let ids = $('#ids').val();
            //导出清单
            window.location.href="<?php  echo $this->createMobileUrl('declare/risk');?>&op=download_list&type=<?php  echo $type;?>&ids="+ids;
        });
    });
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>