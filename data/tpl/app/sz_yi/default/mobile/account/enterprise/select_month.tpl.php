<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>预报列表</title>
<link href="../addons/sz_yi/static/css/layui.css" rel="stylesheet">
<link href="../addons/sz_yi/static/js/layui_exts/laydateNote/laydateNote.css" rel="stylesheet">
<style>
    *{font-size:15px;}
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
    .laydateNote .layui-laydate-static, .laydateNote .layui-laydate-main, .laydateNote .layui-laydate-content table{height:270px;}
</style>
<div class="layui-container" style="padding: 15px;">
    <div class="layui-row">
        <div class="layui-col-md12">
            <p style="text-align:center;margin-bottom:10px;">请选择月份进行查看</p>
            <div class="layui-inline laydateNote" id="test-n1"></div>
        </div>
    </div>
</div>

<script type="text/javascript" src="../addons/sz_yi/static/js/layui/layui.js"></script>

<script>
    $(function() {
        layui.use('laydate',function () {
            var laydate = layui.laydate;

            //直接嵌套显示
            laydate.render({
                elem: '#test-n1'
                ,position: 'static'
                ,format:'yyyy-MM'
                ,type:'month'
                ,max: "new Date()"
                ,ready: function(date){
                    $('.layui-laydate-content table').css('height','270px !important');
                    $('.layui-laydate-footer').css('text-align','center');
                    $('.laydate-footer-btns').css('position','initial');
                    $('.laydate-btns-clear').text('返回').removeClass('laydate-btns-clear').css('margin-right','15px').removeAttr('lay-type').one("click",function(){
                        window.history.back(-1);
                    });
                    $('.laydate-btns-now').remove();
                }
                ,done: function(value, date, endDate){
                    console.log(value); //得到日期生成的值，如：2017-08-18
                    var url = '';
                    var opera = "<?php  echo $opera;?>";
                    if(opera==1){
                        //企业端-凭证管理
                        url = './index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=voucher_manage&date='+value;
                    }else if(opera==2){
                        //管理端-确认管理
                        // url = './index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=voucher_manage_select&date='+value;
                        url = './index.php?i=3&c=entry&do=account&m=sz_yi&p=manage&op=confirm_manage&date='+value;
                    }else if(opera==3){
                        //记账端-全部管理
                        // url = './index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=voucher_manage_select&date='+value;
                        url = './index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&date='+value;
                    }
                    window.location.href=url;
                },change: function(value, date, endDate){
                    console.log(value);
                }
            });
        });
    });
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>