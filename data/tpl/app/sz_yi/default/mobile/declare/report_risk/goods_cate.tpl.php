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

    .layui-form-radio i {top: 0;width: 16px;height: 16px;line-height: 16px;border: 1px solid #d2d2d2;font-size: 12px;border-radius: 2px;background-color: #fff;color: #fff !important;}
    .layui-form-radioed i {position: relative;width: 18px;height: 18px;border-style: solid;background-color: #5FB878;color: #5FB878 !important;}
    /* 使用伪类画选中的对号 */
    .layui-form-radioed i::after, .layui-form-radioed i::before {content: "";position: absolute;top: 8px;left: 5px;display: block;width: 12px;height: 2px;border-radius: 4px;background-color: #fff;-webkit-transform: rotate(-45deg);transform: rotate(-45deg);}
    .layui-form-radioed i::before {position: absolute;top: 10px;left: 2px;width: 7px;transform: rotate(-135deg);}

    .disf{display:flex !important;align-items: center;justify-content: flex-start;}
    .layui-input{width:150px;}
    .upOrDown{display:none;width:25px;margin-left:5px;}
    .rang input.layui-input.layui-unselect {width:36px;padding-right:0;font-size:20px;}
    .rang .layui-select-title .layui-edge{display: none;}
    .adj{margin-left:10px !important;}
    .adj input.layui-input, .adj .layui-unselect {width:90px;}
    .layui-input-block{margin-left:90px;}
    .amp1,.amp2{width:50px;}
    /*.ratio .layui-select-title .layui-input,.money .layui-select-title .layui-input{font-size:20px;}*/
    /**进度条**/
    .layui-progress{display:none;position: fixed;background: #ffffff;border-radius: 5px;z-index:9999999;transform:translate(-50%,-50%);top: 50%;left:50%;width:80%;}

    .layui-table-tool-self .layui-inline .layui-table-tool-panel{ display:none; }
    .select_edit{display:none;}
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
<div class="mask"  style="position: fixed; margin: 0px; padding: 0px;background: rgba(0, 0, 0,0.6); z-index: 999999; width: 100%; height: 100%; transition: all 0.2s ease 0s;display:none;left: 0;top: 0;"></div>
<div class="layui-progress" lay-showpercent="true" lay-filter="demo" >
    <p style="color:#fff;text-align:center;margin-top:10px;">系统运算中，请稍后...</p>
    <div class="layui-progress-bar layui-bg-red" lay-percent="0%"></div>
</div>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card" style="margin-top:5px;">
                <div class="disf">
                    <div class="layui-btn layui-btn-sm layui-btn-normal" onclick="openWindow(1)">归类缺省</div>
                    <div class="layui-btn layui-btn-sm layui-btn-normal" onclick="openWindow(2)">商品导出</div>
                    <button class="layui-btn layui-btn-sm layui-btn-normal select_edit" onclick="submitmychose()">选择修改</button>
                    <input type="text" name="ids" value="" style="display:none;">
                </div>
                <div class="layui-card-body" style="padding:0;">
                    <table class="layui-hide" id="mainTable"></table>
                </div>
            </div>
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
            var timer;//定义一个计时器

            table.render({
                elem: '#mainTable'
                ,url: "<?php  echo $this->createMobileUrl('declare/report_risk');?>&op=goods_cate&pa=1"
                ,cellMinWidth: 200
                ,cols: [[
                    {type:'checkbox','width':40,'fixed':'left'},
                    {field:'itemName', title: '商品名称'}
                    ,{field:'gcode', title: '商品编码'}
                    ,{align:'center', width:50, title: '操作',fixed:'right', templet: function(d){
                            return [
                                '<a onclick="openWindow(3,'+ "'"+d.itemName+"修改'" +',' + "'"+ d.id +"'" +');" class="layui-btn layui-btn-normal layui-btn-xs">修改</a>',
                            ].join('');
                        }}
                ]]
                ,page: true
                ,done : function(res, curr, count) {
                    // $('.layui-table').rowspan(3);
                }
            });

            //监听表格复选框选择
            table.on('checkbox()', function(obj){
                var checkStatus = layui.table.checkStatus('mainTable'); //demo 即为基础参数 id 对应的值
                // console.log(checkStatus.data);return false;
                if (checkStatus.data.length>0) {
                    var idsArray = [];
                    for (var i = 0; i < checkStatus.data.length; i++) {
                        idsArray.push(checkStatus.data[i].id);
                    }
                    var ids = idsArray.toString();
                    $('input[name="ids"]').val(ids);
                    $('.select_edit').show();
                }
                else
                {
                    $('.select_edit').hide();
                    // layer.alert("请至少选择一行1");
                }
            });
        });
    });

    //选择修改
    function submitmychose(){
        var layer = layui.layer;
        let ids = $('input[name="ids"]').val();
        let pre_batch_num = $('input[name="pre_batch_num"]').val();
        if(ids=='' || typeof(ids)=='undefined'){
            layer.alert("请至少选择一行");
        }else{
            url="<?php  echo $this->createMobileUrl('declare/report_risk');?>&op=many_good_edit&ids="+ids;
            var index = layer.open({
                type: 2,
                title: '多商品编辑',
                content: url
            });
            layer.full(index);
        }
    }

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

    function openWindow(typ,title,id){
        var layer = layui.layer;
        var url = '';
        if(typ==1){
            //直接修改
            layer.confirm('请选择补缺方式',{
                btn:['系统补缺','人工补缺']
            }, function(){
                //系统补缺
                layer.closeAll('');//关闭弹框

                //请求
                // var timer = load();
                // var data = {batch_num:pre_batch_num};
                layer.open({
                    type: 2,
                    title: '系统补缺',
                    content: "<?php  echo $this->createMobileUrl('declare/report_risk');?>&op=system_fill_page",
                    area:['80%','250px']
                });

                return false;
            }, function(){
                //人工补缺
                layer.open({
                    type: 2,
                    title: '人工补缺',
                    content: "<?php  echo $this->createMobileUrl('declare/report_risk');?>&op=manual_fill_page2&ids=0",
                    area:['80%','250px']
                });
            });
        }else if(typ==2){
            //导出商品表格
            window.location.href="<?php  echo $this->createMobileUrl('declare/report_risk');?>&op=download_goods2";
        }else if(typ==3){
            //编辑商品
            url="<?php  echo $this->createMobileUrl('declare/report_risk');?>&op=good_edit2&id="+id;
            var index = layer.open({
                type: 2,
                title: title,
                content: url
            });
            layer.full(index);
        }
    }
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>