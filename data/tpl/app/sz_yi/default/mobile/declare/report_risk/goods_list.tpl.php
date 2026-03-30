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

    .select_edit{display:none;}
    .layui-table-tool-self .layui-inline .layui-table-tool-panel{ display:none; }
</style>
<div class="mask"  style="position: fixed; margin: 0px; padding: 0px;background: rgba(0, 0, 0,0.6); z-index: 999999; width: 100%; height: 100%; transition: all 0.2s ease 0s;display:none;left: 0;top: 0;"></div>
<div class="layui-progress" lay-showpercent="true" lay-filter="demo" >
    <p style="color:#fff;text-align:center;margin-top:10px;">系统运算中，请稍后...</p>
    <div class="layui-progress-bar layui-bg-red" lay-percent="0%"></div>
</div>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="disf">
                    <div class="layui-btn layui-btn-sm layui-btn-normal" onclick="openWindow('','','<?php  echo $pre_batch_num;?>',2)">商品导出</div>
                    <button class="layui-btn layui-btn-sm layui-btn-normal select_edit" onclick="submitmychose()">选择修改</button>
                    <input type="text" name="ids" value="" style="display:none;">
                    <input type="text" name="pre_batch_num" value="<?php  echo $pre_batch_num;?>" style="display:none;">
                </div>
                <div class="layui-card-body" style="padding:0;">
                    <div style="height:40px;">
                        <div class="main-table-reload-btn" style="margin-bottom: 10px;float: right;">
                            搜索关键词：
                            <div class="layui-inline">
                                <input class="layui-input" placeholder="要搜索的关键词"  id="keywords" autocomplete="off">
                            </div>
                            <button class="layui-btn layui-btn-normal" data-type="reload">搜索</button>
                        </div>    
                    </div>
                    
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
                ,url: "<?php  echo $this->createMobileUrl('declare/report_risk');?>&op=goods_list&pa=1&logisticsNo=<?php  echo $logisticsNo;?>&pre_batch_num=<?php  echo $pre_batch_num;?>"
                ,cellMinWidth: 200
                ,cols: [[
                    {type:'checkbox','width':40,'fixed':'left'}
                    ,{field:'gname', title: '商品名称'}
                    ,{field:'gcode', title: '商品编码'}
                    ,{align:'center', width:50, title: '操作',fixed:'right', templet: function(d){
                            return [
                                '<a onclick="openWindow('+ "'"+d.gname+"修改'" +',' + "'"+ d.id +"'" +','+"'"+d.pre_batch_num+"'"+ ','+1+');" class="layui-btn layui-btn-normal layui-btn-xs">修改</a>',
                            ].join('');
                        }}
                ]]
                ,page: true
                ,done : function(res, curr, count) {
                    // $('.layui-table').rowspan(3);
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

            $('.main-table-reload-btn .layui-btn').on('click', function(){
                var type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
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
                else {
                    $('.select_edit').hide();
                    // layer.alert("请至少选择一行");
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
            url="<?php  echo $this->createMobileUrl('declare/report_risk');?>&op=many_good_edit&type=1&ids="+ids+"&pre_batch_num="+pre_batch_num;
            var index = layer.open({
                type: 2,
                title: '多商品编辑',
                content: url
            });
            layer.full(index);
        }
    }

    function openWindow(title,id,pre_batch_num,typ){
        var layer = layui.layer;
        var url = '';
        if(typ==1){
            // layer.confirm('请选择',{
            //     btn:['选择修改','直接修改']
            // }, function(index,layero){
            //     //待做...
            //
            // }, function(index){
                //直接修改
                url="<?php  echo $this->createMobileUrl('declare/report_risk');?>&op=good_edit&type=1&id="+id+"&pre_batch_num="+pre_batch_num;
                var index = layer.open({
                    type: 2,
                    title: title,
                    content: url
                });
                layer.full(index);
            // });
        }else if(typ==2){
            //导出商品表格
            window.location.href="<?php  echo $this->createMobileUrl('declare/report_risk');?>&op=download_goods&pre_batch_num="+pre_batch_num;
        }
    }
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>