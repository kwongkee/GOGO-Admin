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

    .page_head{width:100%;background:#fff;margin-bottom:5px;box-shadow:0 0 4px #ddd;padding:10px 0 10px;}
    .page_head .left{width:20%;display:flex;align-items:center;font-size:15px;}
    .page_head .left .back{width:13px;height:13px;border-top:2px solid #000;border-left:2px solid #000;transform:rotate(-50deg);margin-left:15px;margin-right:5px;}
    
     /**进度条**/
    .layui-progress{display:none;position: fixed;background: #ffffff;border-radius: 5px;z-index:9999999;transform:translate(-50%,-50%);top: 50%;left:50%;width:80%;}
</style>
<div class="mask"  style="position: fixed; margin: 0px; padding: 0px;background: rgba(0, 0, 0,0.6); z-index: 999999; width: 100%; height: 100%; transition: all 0.2s ease 0s;display:none;left: 0;top: 0;"></div>
<div class="layui-progress" lay-showpercent="true" lay-filter="demo" >
    <p style="color:#fff;text-align:center;margin-top:10px;">系统查询中，请稍后...</p>
    <div class="layui-progress-bar layui-bg-red" lay-percent="0%"></div>
</div>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">商品列表</div>
                <div class="layui-card-body" style="padding:0;">
                    <p style="color:red;display:none;text-decoration:underline;font-size:15px;" class="tips" onclick="riskGoods()">您好，系统检测到您的商品清单数据存在风险商品，请点击查看。<img src="https://shop.gogo198.cn/attachment/images/pointer.png" alt="" style="width:15px;"></p>
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
                
            var timer;
            
            table.render({
                elem: '#mainTable'
                ,url: "<?php  echo $this->createMobileUrl('declare/risk_manage');?>&op=goods_list&pre_batch_num=<?php  echo $batch_num;?>&pa=1"
                ,cellMinWidth: 200
                ,cols: [[
                    {field:'gname', title: '品名'}
                    ,{field:'gcode', title: '编码'}
                    ,{align:'center', title: '操作',fixed:'right',width:100, templet: function(d){
                            return [
                                '<a onclick="openWindow('+ "'"+d.gname+"'" +',' + "'<?php  echo $batch_num;?>'" +','+"1"+ ','+"'"+d.itemNo+"'"+');" class="layui-btn layui-btn-normal layui-btn-xs">修改</a>',
                                '<a onclick="javascript:del('+"'<?php  echo $batch_num;?>'"+','+"'"+d.itemNo+"'"+');" class="layui-btn layui-btn-xs layui-btn-danger">剔除</a>',
                            ].join('');
                        } }
                ]]
                ,before:function(){
                    timer = load();
                }
                ,done:function(res){
                    hide_load(timer);
                    if(res.show_tips==1){
                        $('.tips').show();
                    }
                }
                ,page: true
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
        });
    });

    //查看风险商品
    function riskGoods(){
        var layer = layui.layer;

        layer.confirm('请选择风险类型进行查看。',{
            btn:['敏感商品','三涉商品']
        },function(res){
            //获取敏感商品列表
            // let dataList = {pre_batch_num:"<?php  echo $batch_num;?>",op:'getSensitiveGoods'};
            // $.post("<?php  echo $this->createMobileUrl('declare/risk_manage');?>",dataList,function(res){
            //     res = JSON.parse(res);
            //     if(res.status==1){
                    var index = layer.open({
                        type: 2,
                        title: '敏感商品',
                        content: "<?php  echo $this->createMobileUrl('declare/risk_manage');?>&op=sensitiveGoods&pre_batch_num=<?php  echo $batch_num;?>",
                        // area:['80%','40%']
                    });
                    layer.full(index);
            //     }
            // });
        },function(res){
            //获取三涉商品列表
            // let dataList = {pre_batch_num:"<?php  echo $batch_num;?>",op:'getThreeInvolveGoods'};
            // $.post("<?php  echo $this->createMobileUrl('declare/risk_manage');?>",dataList,function(res){
            //     res = JSON.parse(res);
            //     if(res.status==1){
                    var index = layer.open({
                        type: 2,
                        title: '三涉商品',
                        content: "<?php  echo $this->createMobileUrl('declare/risk_manage');?>&op=threeInvolveGoods&pre_batch_num=<?php  echo $batch_num;?>",
                    });
                    layer.full(index);
            //     }
            // });
        });
    }

    function openWindow(title,pre_batch_num,typ,itemNo)
    {
        var layer = layui.layer;
        var url = '';
        if(typ==1){
            //修改品名、编号
            url="<?php  echo $this->createMobileUrl('declare/risk_manage');?>&op=good_edit&itemNo="+itemNo+"&pre_batch_num="+pre_batch_num;
            var index = layer.open({
                type: 2,
                title: title,
                content: url,
                // area:['80%','40%']
            });
            layer.full(index);
        }
    }

    //剔除
    function del(pre_batch_num,itemNo){
        var layer = layui.layer
            table = layui.table;
        layer.confirm('确认剔除后无法恢复，确认剔除吗？',{
            btn:['确认','取消']
        },function(){
            var url = "<?php  echo $this->createMobileUrl('declare/risk_manage');?>";
            let data = {op: 'del', pre_batch_num: pre_batch_num,itemNo:itemNo};
            $.post(url, data, function (res) {
                res = JSON.parse(res);
                if(res.status==1){
                    layer.msg(res.result.msg,{time:2000},function(){
                        table.reload('mainTable', {
                            page: {
                                curr: 1 //重新从第 1 页开始
                            }
                            ,where: {
                                keywords: $("#keywords").val()
                            }
                        });
                    });
                }
            });
        },function(){

        });
    }

    //恢复
    function rec(pre_batch_num,itemNo){

    }
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>