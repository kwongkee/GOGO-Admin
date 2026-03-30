<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>添加催款</title>
<style type="text/css">
    body {margin:0px; background:#efefef; font-family:'微软雅黑'; -moz-appearance:none;}
    .info_main {height:auto;  background:#fff; margin-top:14px; border-bottom:1px solid #e8e8e8; border-top:1px solid #e8e8e8;}
    .info_main .line {margin:0 10px; height:40px; border-bottom:1px solid #e8e8e8; line-height:40px; color:#999;}
    .info_main .line .title {height:40px; width:80px; line-height:40px; color:#444; float:left; font-size:15px;}
    .info_main .line .info { width:100%;float:right;margin-left:-80px; }
    .info_main .line .inner { margin-left:80px; width:265px;overflow: hidden;white-space: nowrap;text-overflow: ellipsis; font-size:15px;}
    .info_main .line .inner input {height:40px; width:100%;display:block; padding:0px; margin:0px; border:0px; float:left; font-size:15px;}
    .info_main .line .inner .user_sex {line-height:40px;}
    .info_main .line .inner select{font-size:15px;}
    .info_sub {height:44px; margin:14px 5px; background:#31cd00; border-radius:4px; text-align:center; font-size:16px; line-height:44px; color:#fff;}
    .select { border:1px solid #ccc;height:25px;}
    .table{width:100%;}
    .table tr{height:30px;}
    .table tr td:nth-of-type(1){width:88%;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;}
    .table tr td:nth-of-type(2){width:12%;text-align: center;}
    .see{box-sizing: border-box;padding: 2px 4px;font-size: 14px;color: #fff;background: #1E9FFF;text-align:center;}
    .page_topbar{display: flex;align-items: center;}
    .page_topbar .bon{text-align:center;font-size:16px;width:50%;}
    .page_topbar .bonAct{border-bottom:1px solid #1E9FFF;color:#1E9FFF;}
    .list-t2{display:none;}
    .tixian{height: 44px;margin: 14px 5px;background: #1E9FFF;border-radius: 4px;text-align: center;font-size: 16px;line-height: 44px;color: #fff;}
</style>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2-zh.js" type="text/javascript"></script>
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2.css" rel="stylesheet" type="text/css" />
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.animation-2.5.2.css" rel="stylesheet" type="text/css" />
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.datetime-2.5.1.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.datetime-2.5.1-zh.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.android-ics-2.5.2.js" type="text/javascript"></script>
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.android-ics-2.5.2.css" rel="stylesheet" type="text/css" />

<div id="container">
    <div class="info_main">
        <div class="line"><div class="title">选择订单</div><div class='info'>
            <div class="inner">
                <select name="ordersn" id="ordersn" style="background:none;border:0;">
                    <option value="">请选择订单</option>
                    <?php  if(is_array($list)) { foreach($list as $val) { ?>
                    <option value="<?php  echo $val['id'];?>"><?php  echo $val['ordersn'];?>-<?php  echo $val['payer_name'];?></option>
                    <?php  } } ?>
                </select>
            </div>
        </div></div>
        <div class="line"><div class="title">催款方式</div><div class='info'>
            <div class="inner">
                <span class="type" data-val="1"><i class="fa fa-circle-o"></i> X天通知一次</span>&nbsp;&nbsp;
                <span class="type" data-val="2"><i class="fa fa-circle-o"></i> 每X天通知一次</span>
                <input type="text" name="press_money_type" id="press_money_type" style="display: none;">
            </div>
        </div></div>
        <div class="line"><div class="title">天数</div><div class='info'><div class='inner'>
            <input type="number" name="press_money_day" id="press_money_day" value="" placeholder="请输入逾期付款日前X天天数">
        </div></div></div>
    </div>

    <div class="button tixian">立即保存</div>
</div>
<script>
    require(['tpl', 'core'], function(tpl, core) {
        $('.tixian').click(function(){
            let orderid = '';
            $('#ordersn option:selected').each(function () {
                if($(this).val()!=''){
                    orderid += $(this).val()+',';
                }
            });
            if(orderid==''){
                core.tip.show('请选择订单号!');
                return;
            }

            let type = $('#press_money_type').val();
            if($('#press_money_type').isEmpty()){
                core.tip.show('请选择催款方式!');
                return;
            }

            let press_money_day = $('#press_money_day').val();
            if(press_money_day<=0){
                core.tip.show('请填写正确的天数！');
                return;
            }

            core.json('domestic/liquidation',{
                'op':'pressMoney_add',
                'orderid':orderid,
                'press_money_type':type,
                'press_money_day':press_money_day,
            },function(json){
                if(json.status==-1){
                    core.tip.show(json.result.msg);
                }else{
                    core.tip.show('添加成功');
                    setTimeout(function(){
                        window.history.back(-1);
                    },2000)
                }
            });
        });
        $('#ordersn').change(function(){
            var orderid = $(this).val();
            orderid = orderid.toString();
        });
    });

    $(function(){
        //催款方式
        $('.type').click(function() {
            var $this = $(this);
            var val = $this.data('val');
            $('.type').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#press_money_type').val(val);
        });
    })
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>