<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>收款详情</title>
<style type="text/css">
    body {margin:0px; background:#efefef; font-family:'微软雅黑'; -moz-appearance:none;}
    .info_main {height:auto;  background:#fff; margin-top:14px; border-bottom:1px solid #e8e8e8; border-top:1px solid #e8e8e8;}
    .info_main .line {margin:0 10px; height:40px; border-bottom:1px solid #e8e8e8; line-height:40px; color:#999;}
    .info_main .line .title {height:40px; width:80px; line-height:40px; color:#444; float:left; font-size:15px;}
    .info_main .line .info { width:100%;float:right;margin-left:-80px; }
    .info_main .line .inner { margin-left:80px; width:265px;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;font-size:15px;}
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

    <?php  if(empty($other_pay)) { ?>
        <div class="info_main">
            <div class="line"><div class="title">交易金额</div><div class='info'><div class='inner'>CNY <?php  echo $data['trade_price'];?></div></div></div>
            <div class="line"><div class="title">逾期金额</div><div class='info'><div class='inner'>CNY <?php  echo $data['overdue_money'];?></div></div></div>
            <div class="line"><div class="title">实付金额</div><div class='info'><div class='inner'>CNY <?php  echo $data['total_money'];?></div></div></div>
        </div>
    <?php  } else { ?>
        <div class="info_main">
        <?php  if($other_pay['payment_mode']==1) { ?>
            <div class="line"><div class="title">付款方式</div><div class='info'><div class='inner'>转账</div></div></div>
            <div class="line"><div class="title">转账金额</div><div class='info'><div class='inner'>CNY <?php  echo $other_pay['transfer_price'];?></div></div></div>
        <?php  } else { ?>
            <div class="line"><div class="title">付款方式</div><div class='info'><div class='inner'>现金</div></div></div>
            <div class="line"><div class="title">实际转账额</div><div class='info'><div class='inner'>CNY <?php  echo $other_pay['true_pay_price'];?></div></div></div>
        <?php  } ?>
        </div>
    <?php  } ?>

    <div class="info_main">
        <div class="line"><div class="title">付款人名称</div><div class='info'><div class='inner'><?php  echo $data['payer_name'];?></div></div></div>
        <div class="line"><div class="title">付款人电话</div><div class='info'><div class='inner'><?php  echo $data['payer_tel'];?></div></div></div>
    </div>
    <div class="info_main">
        <div class="line"><div class="title">付款依据</div><div class='info'><div class='inner'><?php  echo $data['basic'];?></div></div></div>
        <?php  if($data['basic']=='合同') { ?>
        <div class="line"><div class="title">合同编号</div><div class='info'><div class='inner'><?php  echo $data['contract_num'];?></div></div></div>
        <div class="line" style="height:100px;"><div class="title">合同文件</div><div class='info'><div class='inner'>
            <?php  if(is_array($data['contract_file'])) { foreach($data['contract_file'] as $i => $v) { ?>
            <a href="https://shop.gogo198.cn/attachment/<?php  echo $v;?>" target="_blank">
                <?php  if(in_array(explode('.',$v)['1'],array('gif','jpg','jpeg','png'))) { ?>
                    <img src="https://shop.gogo198.cn/attachment/<?php  echo $v;?>" alt="" style="width:80px;height:80px;margin-top: 10px;">
                <?php  } else { ?>
                    文件<?php  echo $i+1;?>
                <?php  } ?>
            </a>
            <?php  } } ?>
        </div></div></div>
        <?php  } ?>
        <?php  if($data['basic']=='订单') { ?>
        <div class="line"><div class="title">订单编号</div><div class='info'><div class='inner'><?php  echo $data['orderno'];?></div></div></div>
        <div class="line"><div class="title">订单链接</div><div class='info'><div class='inner'><a href="<?php  echo $data['orderurl'];?>" target="_blank">跳转</a></div></div></div>
        <div class="line" style="height:100px;"><div class="title">订单文件</div><div class='info'><div class='inner'>
            <?php  if(is_array($data['orderdemo'])) { foreach($data['orderdemo'] as $i => $v) { ?>
            <a href="https://shop.gogo198.cn/attachment/<?php  echo $v;?>" target="_blank">
                <?php  if(in_array(explode('.',$v)['1'],array('gif','jpg','jpeg','png'))) { ?>
                    <img src="https://shop.gogo198.cn/attachment/<?php  echo $v;?>" alt="" style="width:80px;height:80px;margin-top: 10px;">
                <?php  } else { ?>
                    文件<?php  echo $i+1;?>
                <?php  } ?>
            </a>
            <?php  } } ?>
        </div></div></div>
        <?php  } ?>
        <?php  if($data['basic']=='说明') { ?>
        <div class="line"><div class="title">说明</div><div class='info'><div class='inner'><?php  echo $data['description'];?></div></div></div>
        <?php  } ?>
    </div>
    <?php  if(!empty($data['press_money_type'])) { ?>
    <div class="info_main">
        <div class="line"><div class="title">催款方式</div><div class='info'><div class='inner'>
            <?php  if($data['press_money_type']==1) { ?> 逾期付款日前<?php  echo $data['press_money_day'];?>日通知一次 <?php  } ?>
            <?php  if($data['press_money_type']==2) { ?> 逾期付款日前每<?php  echo $data['press_money_day'];?>日通知一次 <?php  } ?>
        </div></div></div>
    </div>
    <?php  } ?>
    <div class="info_main">
        <?php  if(empty($other_pay)) { ?>
        <div class="line"><div class="title">提现状态</div><div class='info'><div class='inner'><?php  echo $tixian_status[$data['tixian_status']];?></div></div></div>
        <?php  } else { ?>
        <div class="line"><div class="title">提现状态</div><div class='info'><div class='inner'>线下收款，不可提现</div></div></div>
        <?php  } ?>
        <?php  if(!empty($data['tixian_remark'])) { ?>
        <div class="line"><div class="title">原因</div><div class='info'><div class='inner'>{[$data['tixian_remark']}</div></div></div>
        <?php  } ?>
        <div class="line"><div class="title">支付状态</div><div class='info'><div class='inner' style="color:#1E9FFF;"><?php echo $data['status']==0?'未支付':'已支付'?></div></div></div>
        <?php  if($data['status']==1) { ?>
        <div class="line"><div class="title">支付方式</div><div class='info'><div class='inner'>
            <?php  if($data['pay_type']==1) { ?>
            微信
            <?php  } ?>
            <?php  if($data['pay_type']==2) { ?>
            支付宝
            <?php  } ?>
            <?php  if($data['pay_type']==3) { ?>
            线下支付
            <?php  } ?>
        </div></div></div>
        <div class="line"><div class="title">支付时间</div><div class='info'><div class='inner'><?php  echo date('Y-m-d H:i:s',$data['paytime'])?></div></div></div>
        <?php  } ?>
        <div class="line"><div class="title">创建时间</div><div class='info'><div class='inner'><?php  echo date('Y-m-d H:i:s',$data['createtime'])?></div></div></div>
        <div class="line"><div class="title">逾期时间</div><div class='info'><div class='inner'><?php  echo date('Y-m-d H:i:s',$data['overdue'])?></div></div></div>
    </div>

    <div class="button back" onclick="javascript:history.go(-1);">返回</div>
</div>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>