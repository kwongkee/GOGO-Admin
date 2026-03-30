<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>订单详情</title>
<style type="text/css">
    body {margin:0px; background:#efefef; font-family:'微软雅黑'; -moz-appearance:none;}
    .info_main {height:auto;  background:#fff; margin-top:14px; border-bottom:1px solid #e8e8e8; border-top:1px solid #e8e8e8;}
    .info_main .line {margin:0 10px; height:40px; border-bottom:1px solid #e8e8e8; line-height:40px; color:#999;}
    .info_main .line .title {height:40px; width:80px; line-height:40px; color:#444; float:left; font-size:15px;}
    .info_main .line .info { width:100%;float:right;margin-left:-80px; }
    .info_main .line .inner { margin-left:80px; width:265px;overflow: hidden;white-space: nowrap;text-overflow: ellipsis; font-size:15px;}
    .info_main .line .inner input {height:40px; width:100%;display:block; padding:0px; margin:0px; border:0px; float:left; font-size:15px;}
    .info_main .line .inner .user_sex {line-height:40px;}
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
    .back{height: 44px;margin: 14px 5px;background: #1E9FFF;border-radius: 4px;text-align: center;font-size: 16px;line-height: 44px;color: #fff;}
    #container{width:100%;}
</style>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2-zh.js" type="text/javascript"></script>
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2.css" rel="stylesheet" type="text/css" />
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.animation-2.5.2.css" rel="stylesheet" type="text/css" />
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.datetime-2.5.1.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.datetime-2.5.1-zh.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.android-ics-2.5.2.js" type="text/javascript"></script>
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.android-ics-2.5.2.css" rel="stylesheet" type="text/css" />

<link type="text/css" rel="stylesheet" href="../addons/sz_yi/static/css/bootstrap.min.css" />
<script type="text/javascript" src="../addons/sz_yi/template/pc/default/static/js/bootstrap.min.js"></script>

<div id="container">
    <div class="info_main">
        <div class="line"><div class="title">订单编号</div><div class='info'><div class='inner'><?php  echo $data['ordersn'];?></div></div></div>
        <div class="line"><div class="title">交易金额</div><div class='info'><div class='inner'>CNY <?php  echo $data['trade_price'];?></div></div></div>
        <div class="line"><div class="title">逾期金额</div><div class='info'><div class='inner'>CNY <?php  echo $data['overdue_money'];?></div></div></div>
        <div class="line"><div class="title">实收金额</div><div class='info'><div class='inner'>CNY <?php  echo $data['total_money'];?></div></div></div>
        <div class="line"><div class="title">交易类型</div><div class='info'><div class='inner'><?php  echo $data['trade_type_name'];?></div></div></div>
        <div class="line"><div class="title">交易名称</div><div class='info'><div class='inner'><?php  echo $name;?></div></div></div>
    </div>
    <?php  if($data['trade_type']==3) { ?>
    <table class="table table-striped" style="margin-top:10px;margin-bottom:10px;">
        <tr>
            <td>项目</td>
            <td>摘要</td>
            <td>金额</td>
        </tr>
        <?php  if(is_array($data['service_info'])) { foreach($data['service_info'] as $v) { ?>
        <tr>
            <td><?php  echo $v['0'];?></td>
            <td><?php  echo $v['1'];?></td>
            <td>CNY <?php  echo $v['2'];?></td>
        </tr>
        <?php  } } ?>

    </table>
    <?php  } ?>
    <div class="info_main">
        <div class="line"><div class="title">付款人名称</div><div class='info'><div class='inner'><?php  echo $data['payer_name'];?></div></div></div>
        <div class="line"><div class="title">付款人电话</div><div class='info'><div class='inner'><?php  echo $data['payer_tel'];?></div></div></div>
        <div class="line"><div class="title">支付状态</div><div class='info'><div class='inner' style="color:#1E9FFF;"><?php echo $data['status']==0?'未支付':'已支付'?></div></div></div>
    </div>
    <div class="info_main">
        <div class="line"><div class="title">创建时间</div><div class='info'><div class='inner'><?php  echo date('Y-m-d H:i:s',$data['createtime'])?></div></div></div>
        <div class="line"><div class="title">逾期时间</div><div class='info'><div class='inner'><?php  echo date('Y-m-d H:i:s',$data['overdue'])?></div></div></div>
        <?php  if($data['status']==1) { ?>
        <div class="line"><div class="title">支付时间</div><div class='info'><div class='inner'><?php  echo date('Y-m-d H:i:s',$data['paytime'])?></div></div></div>
        <div class="line"><div class="title">支付方式</div><div class='info'><div class='inner'><?php echo $data['pay_type']==1?'微信':'支付宝'?></div></div></div>
        <?php  } ?>
        <div class="line"><div class="title">提现状态</div><div class='info'><div class='inner'><?php  echo $tixian_status[$data['tixian_status']];?></div></div></div>
        <?php  if(!empty($data['tixian_remark'])) { ?>
        <div class="line"><div class="title">原因</div><div class='info'><div class='inner' style="color:#ff5555;"><?php  echo $data['tixian_remark'];?></div></div></div>
        <?php  } ?>
    </div>
    <div class="info_main">
        <div class="line"><div class="title">收款账户</div><div class='info'><div class='inner'><?php  echo $data['account_info']['account'];?></div></div></div>
        <div class="line"><div class="title">提现费用</div><div class='info'><div class='inner'>CNY <?php  echo $data['fee_info']['withdrawal_expenses'];?></div></div></div>
        <div class="line"><div class="title">提现汇率</div><div class='info'><div class='inner'><?php  echo $data['fee_info']['withdrawal_expenses_rate'];?></div></div></div>
        <div class="line"><div class="title">实际提现</div><div class='info'><div class='inner'>CNY <?php  echo $data['fee_info']['true_money'];?></div></div></div>
    </div>

    <div class="button back" onclick="javascript:history.back(-1);">返回</div>
</div>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>