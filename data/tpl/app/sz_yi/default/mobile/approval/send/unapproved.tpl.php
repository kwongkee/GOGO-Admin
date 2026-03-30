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

    .layui-layer-hui .layui-layer-content{color:#fff;}
    #select_edit{padding:10px;box-sizing: border-box;}
    #select_edit .pick_div{padding:10px 20px;box-sizing: border-box;text-align: center;border-radius: 7px;color:#fff;font-size:15px;}
    #select_edit .pick_1{background:#1790FF;}
    #select_edit .pick_2{background:#009688;margin:15px 0;}
    #select_edit .pick_3{background:#F7931E;}
</style>
<div class="page_head">
    <div class="left">
        <div class="back"></div>
        <div style="font-size:15px;padding-top:2px;">返回</div>
    </div>
</div>

<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header" style="display: none;">
<!--                    <div class="layui-btn layui-btn-normal layui-btn-sm" onclick="openWindow(0,2)">分享新增</div>-->
                </div>
                <div class="layui-card-body">
<!--                    <div class="main-table-reload-btn" style="margin-bottom: 10px;">-->
<!--                        搜索关键词：-->
<!--                        <div class="layui-inline">-->
<!--                            <input class="layui-input" placeholder="要搜索的关键词"  id="keywords" autocomplete="off">-->
<!--                        </div>-->
<!--                        <button class="layui-btn layui-btn-normal" data-type="reload">搜索</button>-->
<!--                    </div>-->
                    <table class="layui-hide" id="mainTable"></table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        $('.page_head').find('.left').click(function () {
            window.history.back(-1);
        });

        layui.use(['layer', 'form', 'table', 'upload', 'laydate'], function () {
            var $ = layui.$
                , layer = layui.layer
                , form = layui.form
                , element = layui.element
                , laydate = layui.laydate
                , upload = layui.upload
                , table = layui.table;

            table.render({
                elem: '#mainTable'
                ,url: "./index.php?i=3&c=entry&do=approval&p=send&m=sz_yi&op=unapproved&pa=1"
                ,cellMinWidth: 200
                ,cols: [[
                    {field:'id', width: 80, title: '编号'}
                    ,{field:'name', title: '事项名称'}
                    ,{field:'opera', title: '事项状态'}
                    ,{field:'remark', title: '原因'}
                    ,{field:'createtime', title: '创建时间'}
                    ,{align:'center',  title: '操作',fixed:'right',width:100, templet: function(d){
                        if(d.opera=='待审批'){
                            return [
                                '<a onclick="openWindow('+"'"+ d.id +"'" +','+"1"+ ','+"'"+d.name+"'"+');" class="layui-btn layui-btn-normal layui-btn-xs">编辑</a>',
                                '<a  class="layui-btn layui-btn-xs layui-btn-danger" onclick="openWindow('+"'"+ d.id +"'" +','+"2"+');">撤回</a>',
                            ].join('');
                        }else{
                            return ['['+d.opera+']无可操作'].join('');
                        }
                    } }
                ]]
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

    function openWindow(id,typ,name='')
    {
        var $ = layui.$
            , layer = layui.layer;

        if(typ==1){
            //编辑事项
            layer.open({
                'type':2,
                'title':'编辑['+name+']',
                'content':'./index.php?i=3&c=entry&do=approval&p=send&m=sz_yi&op=unapproved_edit&id='+id,
                'area':['100%','100%']
            });
        }else if(typ==2){
            //撤回事项
            layer.confirm('确认撤回吗？',function(){
                $.ajax({
                    url:"./index.php?i=3&c=entry&do=approval&p=send&m=sz_yi&op=unapproved&pa=2",
                    method:'post',
                    data:{'id':id},
                    dataType:'JSON',
                    success:function(res) {
                        layer.closeAll('loading');
                        layer.msg(res.msg,{time:3000}, function () {
                            if (res.code == 0) {
                                window.location.reload();
                            }
                        });
                    }
                });
            });
        }
    }

</script>