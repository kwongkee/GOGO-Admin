<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>支付<?php  echo $name;?></title>
<style type="text/css">
    body {margin:0px; background:#efefef; font-family:'微软雅黑'; -moz-appearance:none;}
    .info_main {height:auto;  background:#fff; margin-top:14px; border-bottom:1px solid #e8e8e8; border-top:1px solid #e8e8e8;}
    .info_main .line {margin:0 10px; height:40px; border-bottom:1px solid #e8e8e8; line-height:40px; color:#999;}
    .info_main .line .title {height:40px; width:80px; line-height:40px; color:#444; float:left; font-size:14px;}
    .info_main .line .info { width:100%;float:right;margin-left:-80px; }
    .info_main .line .inner { margin-left:80px; }
    .info_main .line .inner input {height:39px; width:100%;display:block; padding:0px; margin:0px; border:0px; float:left; font-size:14px;}
    .info_main .line .inner .user_sex {line-height:40px;}
    .info_sub,.info_sub2,.info_sub3 {height:44px; margin:14px 5px; background:#f15353; border-radius:4px; text-align:center; font-size:16px; line-height:44px; color:#fff;}
    .select { border:1px solid #ccc;height:25px;}
    .order_sub12,.btn1-w{height: 44px;margin: 14px 5px;background: rgb(6,192,95);border-radius: 4px;text-align: center;font-size: 16px;line-height: 44px;color: #fff;}
    .order_sub13,.btn1-z{height: 44px;margin: 14px 5px;background: #2e78d0;border-radius: 4px;text-align: center;font-size: 16px;line-height: 44px;color: #fff;}
    .order_otherpay,.btn1-o{height: 44px;margin: 14px 5px;background: #f15900;border-radius: 4px;text-align: center;font-size: 16px;line-height: 44px;color: #fff;}

    .footBox{width:100%;display:flex;align-items: center;justify-content: space-evenly;margin-top:10px;}
    .check_collect{box-sizing:border-box;padding:5px 15px;font-size:16px;color:#fff;background:#2e78d0;border-radius:4px;}
    .nocheck_collect{box-sizing:border-box;padding:5px 15px;font-size:16px;color:#fff;background:#f15900;border-radius:4px;}

    .paymode_show{display:block;}
    .paymode_hide{display:none;}
    .fa-check-circle-o{color:#0c9;}

    /**上传凭证**/
    .info_main .images {float: left; width:auto;height:30px;margin-top:7px;}
    .info_main .images .img { float:left; position:relative;width:30px;height:30px;border:1px solid #e9e9e9;margin-right:5px;}
    .info_main .images .img img { position:absolute;top:0; width:100%;height:100%;}
    .info_main .images .img .minus { position:absolute;color:red;width:8px;height:12px;top:-18px;right:-1px;}
    .info_main .plus { float:left; width:30px;height:30px;border:1px solid #e9e9e9; color:#dedede;; font-size:18px;line-height:30px;text-align:center;margin-top:4px;}
    .info_main .plus i { left:7px;top:7px;}
    button, input, optgroup, select, textarea{font:unset !important;color:black !important;line-height:1.5 !important;}
    .red{color:#ff5555;}
    .green{color:#05c504;}
</style>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2-zh.js" type="text/javascript"></script>
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2.css" rel="stylesheet" type="text/css" />
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.animation-2.5.2.css" rel="stylesheet" type="text/css" />
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.datetime-2.5.1.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.datetime-2.5.1-zh.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.android-ics-2.5.2.js" type="text/javascript"></script>
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.android-ics-2.5.2.css" rel="stylesheet" type="text/css" />

<link href="../addons/sz_yi/template/mobile/default/static/js/star-rating.css" media="all" rel="stylesheet" type="text/css"/>
<script src="../addons/sz_yi/template/mobile/default/static/js/star-rating.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/ajaxfileupload.js" type="text/javascript"></script>

<link type="text/css" rel="stylesheet" href="../addons/sz_yi/static/css/bootstrap.min.css" />
<script type="text/javascript" src="../addons/sz_yi/template/pc/default/static/js/bootstrap.min.js"></script>

<div id="container" style="padding-bottom: 10px;">
    <?php  if($iswaitcheck==1) { ?>
        <div class="page_topbar">
            <div class="title">收款审核</div>
        </div>
        <div class="info_main">
            <div class="line"><div class="title">交易单号</div><div class='info'><div class='inner'><?php  echo $data['ordersn'];?></div></div></div>
            <div class="line"><div class="title">付款方式</div><div class='info'><div class='inner'><?php echo $other_pay['payment_mode']==1?'转账':'现金';?></div></div></div>
            <?php  if($other_pay['payment_mode']==1) { ?>
                <div class="line"><div class="title">付款账户</div><div class='info'><div class='inner'><?php  echo $other_pay['pay_account'];?></div></div></div>
                <div class="line"><div class="title">转账金额</div><div class='info'><div class='inner'>CNY <?php  echo $other_pay['transfer_price'];?></div></div></div>
                <div class="line"><div class="title">收款账户</div><div class='info'><div class='inner'><?php  echo $other_pay['collect_account'];?></div></div></div>
                <div class="line" style="height:100px;"><div class="title">转账凭证</div><div class='info'><div class='inner'>
                    <?php  if(is_array($other_pay['transfer_demo'])) { foreach($other_pay['transfer_demo'] as $i => $v) { ?>
                        <a href="https://shop.gogo198.cn/attachment/<?php  echo $v;?>" target="_blank">
                            <?php  if(in_array(explode('.',$v)['1'],array('gif','jpg','jpeg','png'))) { ?>
                                <img src="https://shop.gogo198.cn/attachment/<?php  echo $v;?>" alt="" style="width:80px;height:80px;margin-top: 10px;">
                            <?php  } else { ?>
                            凭证文件_<?php  echo str_pad($i+1,2,'0',STR_PAD_LEFT);?>
                            <?php  } ?>
                        </a>

                    <?php  } } ?>
                </div></div></div>
            <?php  } else { ?>
                <div class="line"><div class="title">实际支付额</div><div class='info'><div class='inner'>CNY <?php  echo $other_pay['true_pay_price'];?></div></div></div>
                <div class="line"><div class="title">支付时间</div><div class='info'><div class='inner'><?php  echo $other_pay['cash_paytime'];?></div></div></div>
                <div class="line"><div class="title">收款职员</div><div class='info'><div class='inner'><?php  echo $other_pay['collect_staff'];?></div></div></div>
                <div class="line" style="height:100px;"><div class="title">收款凭证</div><div class='info'><div class='inner'>
                    <?php  if(is_array($other_pay['collect_demo'])) { foreach($other_pay['collect_demo'] as $i => $v) { ?>
                    <a href="https://shop.gogo198.cn/attachment/<?php  echo $v;?>" target="_blank">
                        <?php  if(in_array(explode('.',$v)['1'],array('gif','jpg','jpeg','png'))) { ?>
                            <img src="https://shop.gogo198.cn/attachment/<?php  echo $v;?>" alt="" style="width:80px;height:80px;margin-top: 10px;">
                        <?php  } else { ?>
                        凭证文件_<?php  echo str_pad($i+1,2,'0',STR_PAD_LEFT);?>
                        <?php  } ?>
                    </a>
                    <?php  } } ?>
                </div></div></div>
            <?php  } ?>
            <div class="line"><div class="title">到账状态</div><div class='info'><div class='inner'>
                <select name="receipt_status" id="receipt_status">
                    <option value="1">确认到账</option>
                    <option value="2">查未到账</option>
                    <option value="3">到账不全</option>
                </select>
            </div></div></div>
            <div class="line should_payMoney" style="display:none;"><div class="title">到账金额</div><div class='info'><div class='inner'>
                <input type="number" id='should_payMoney' placeholder="请输入到账金额"  value="" />
            </div></div></div>
        </div>
        <div class="info_main">
            <div class="info_sub3">提交</div>
        </div>
    <?php  } else { ?>
        <div class="page_topbar" style="display: flex;align-items: center;">
            <div onclick="javascript:history.go(-1);" style="display:flex;align-items:center;width: 150px;">
                <div style="margin-left:10px;width:10px;height:10px;border-top:2px solid;border-left:2px solid;transform:rotate(-50deg);">
                    
                </div>
                返回
            </div>
            <div class="title" style="margin-left:0px;width:100px;white-space: nowrap;overflow: hidden; text-overflow: ellipsis;">国内支付</div>
        </div>
        <div class="info_main">
            <div class="line"><div class="title">交易单号</div><div class='info'><div class='inner'><?php  echo $data['ordersn'];?></div></div></div>
            <div class="line"><div class="title">交易类型</div><div class='info'><div class='inner'><?php  echo $data['trade_type_name'];?></div></div></div>
            <div class="line"><div class="title">交易名称</div><div class='info'><div class='inner' style="white-space: nowrap;overflow: hidden; text-overflow: ellipsis;"><?php  echo $name;?></div></div></div>
        </div>

        <?php  if($data['trade_type']==3) { ?>
            <table class="table table-striped" style="margin-top:10px;margin-bottom:10px;" data-toggle="table">
                <thead>
                    <tr>
                        <th>项目</th>
                        <th>摘要</th>
                        <th>金额</th>
                    </tr>
                </thead>
                <tbody>
                <?php  if(is_array($data['service_info'])) { foreach($data['service_info'] as $v) { ?>
                    <tr>
                        <td style="width:40%;"><?php  echo $v['0'];?></td>
                        <td style="width:40%;"><?php  echo $v['1'];?></td>
<!--                        php echo sprintf('%.2f',substr(sprintf("%.3f", $v[2]), 0, -2));-->
                        <td style="width:20%;">CNY <?php  echo $v['2'];?></td>
                    </tr>
                <?php  } } ?>
                </tbody>
            </table>
        <?php  } ?>

        <div class="info_main">
            <div class="line"><div class="title">交易总额</div><div class='info'><div class='inner'>CNY <?php  echo $data['trade_price'];?></div></div></div>
            <?php  if(!empty($data['overdue'])) { ?>
            <div class="line"><div class="title">逾期金额</div><div class='info'><div class='inner' style="width:82%;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;">CNY <?php  echo $data['overdue_money'];?> （自逾期日起按逾期额的<?php  echo $data['pay_fee']*100;?>%/天计收）</div></div></div>
            <?php  } ?>
            <div class="line"><div class="title">实付金额</div><div class='info'><div class='inner'>CNY <?php  echo $data['total_money'];?> <?php  if(!empty($data['overdue'])) { ?>（已含逾期金额）<?php  } ?></div></div></div>
            <div class="line"><div class="title">支付状态</div><div class='info'><div class='inner <?php echo $data['status']==0?'red':'green'?>'><?php echo $data['status']==0?'未支付':'已支付'?></div></div></div>
        </div>
        <div class="info_main">
            <div class="line"><div class="title">收款人名称</div><div class='info'><div class='inner'><?php  echo $user['user_name'];?></div></div></div>
            <div class="line"><div class="title">收款人电话</div><div class='info'><div class='inner'><?php  echo $user['user_tel'];?></div></div></div>
            <div class="line"><div class="title">付款人名称</div><div class='info'><div class='inner'><?php  echo $data['payer_name'];?></div></div></div>
            <div class="line"><div class="title">付款人电话</div><div class='info'><div class='inner'><?php  echo $data['payer_tel'];?></div></div></div>
<!--            <div class="line"><div class="title">身份证</div><div class='info'><div class='inner'><?php  echo $data['idcard'];?></div></div></div>-->
        </div>
        <div class="info_main">
            <div class="line"><div class="title">付款依据</div><div class='info'><div class='inner'><?php  echo $data['basic'];?></div></div></div>
            <?php  if($data['basic']=='合同') { ?>
            <div class="line"><div class="title">合同编号</div><div class='info'><div class='inner'><?php  echo $data['contract_num'];?></div></div></div>
            <div class="line" style="height:100px;"><div class="title">合同文件</div><div class='info'><div class='inner'>
                <?php  if(is_array($data['contract_file'])) { foreach($data['contract_file'] as $v) { ?>
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
                <?php  if(!empty($data['orderdemo'])) { ?>
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
            <?php  } ?>
            <?php  if($data['basic']=='备注信息') { ?>
            <div class="line"><div class="title">备注信息</div><div class='info'><div class='inner'><?php  echo $data['description'];?></div></div></div>
            <?php  } ?>
        </div>
        <div class="info_main">
            <div class="line"><div class="title">发起时间</div><div class='info'><div class='inner'><?php  echo date('Y-m-d H:i:s',$data['createtime'])?></div></div></div>
            <?php  if(!empty($data['overdue'])) { ?>
            <div class="line"><div class="title">逾期时间</div><div class='info'><div class='inner'><?php  echo date('Y-m-d H:i:s',$data['overdue'])?></div></div></div>
            <?php  } ?>
            <?php  if($data['status']==1) { ?>
            <div class="line"><div class="title">支付时间</div><div class='info'><div class='inner'><?php  echo date('Y-m-d H:i:s',$data['paytime'])?></div></div></div>
            <?php  } ?>
        </div>
        <?php  if($_GPC['iswaitcheck']!=1 && $_GPC['isadmin']!=1 && $data['status']==0) { ?>
        <div class="button btn1-w <?php  if($data['is_attestation']==1) { ?>order_sub12<?php  } else { ?>pay_sure<?php  } ?>" <?php  if($data['pay_type']==2) { ?>style="display:none;"<?php  } ?>>微信支付</div>
        <div class="button btn1-z <?php  if($data['is_attestation']==1) { ?>order_sub13<?php  } else { ?>pay_sure<?php  } ?>" <?php  if($data['pay_type']==1) { ?>style="display:none;"<?php  } ?>>支付宝支付</div>
        <div class="button btn1-o <?php  if($data['is_attestation']==1) { ?>order_otherpay<?php  } else { ?>pay_sure<?php  } ?>"  <?php  if($data['pay_type']==1 || $data['pay_type']==2) { ?>style="display:none;"<?php  } ?>>我已/要通过其它方式付款</div>
        <?php  } ?>

        <?php  if(($data['pay_type']==3 && $data['status']==1)) { ?>
            <!--已通过xxx支付-->
            <?php  if(($other_pay['payment_mode']==1 || $other_pay['payment_mode']==2)) { ?>
                <div class="info_main otherpay" style="margin-bottom:10px;">
                <div class="line"><div class="title">付款方式</div><div class='info'><div class='inner'>
                    <?php  if($other_pay['payment_mode']==1) { ?>转账<?php  } ?>
                    <?php  if($other_pay['payment_mode']==2) { ?>现金<?php  } ?>
                </div></div></div>
                <div class="transfer_type <?php echo $other_pay['payment_mode']==1?'paymode_show':'paymode_hide'?>">
                    <!--转账-->
                    <div class="line"><div class="title">付款账户</div><div class='info'><div class='inner'><?php  echo $other_pay['pay_account'];?></div></div></div>
                    <div class="line"><div class="title">转账金额</div><div class='info'><div class='inner'>CNY <?php  echo $other_pay['transfer_price'];?></div></div></div>
                    <div class="line"><div class="title">收款账户</div><div class='info'><div class='inner'><?php  echo $other_pay['collect_account'];?></div></div></div>
                    <div class="line"><div class="title">转账凭证</div><div class='info'><div class='inner'>
                        <?php  if(!empty($other_pay['transfer_demo'])) { ?>
                        <?php  if(is_array($other_pay['transfer_demo'])) { foreach($other_pay['transfer_demo'] as $k2 => $v2) { ?>
                            <a href="https://shop.gogo198.cn/attachment/<?php  echo $v2;?>" style="margin-right:5px;">转账文件_<?php  echo str_pad($k2+1,2,'0',STR_PAD_LEFT);?></a>
                        <?php  } } ?>
                        <?php  } ?>
                    </div></div></div>
                </div>
                <div class="cash_type  <?php echo $other_pay['payment_mode']==2?'paymode_show':'paymode_hide'?>" >
                    <!--现金-->
                    <div class="line"><div class="title">实际支付额</div><div class='info'><div class='inner'>CNY <?php  echo $other_pay['true_pay_price'];?></div></div></div>
                    <div class="line"><div class="title">支付时间</div><div class="info"><div class="inner"><?php  echo $other_pay['cash_paytime'];?></div></div></div>
                    <div class="line"><div class="title">收款职员</div><div class="info"><div class="inner"><?php  echo $other_pay['collect_staff'];?></div></div></div>
                    <div class="line"><div class="title">收款凭证</div><div class="info"><div class="inner">
                        <?php  if(!empty($other_pay['collect_demo'])) { ?>
                        <?php  if(is_array($other_pay['collect_demo'])) { foreach($other_pay['collect_demo'] as $k2 => $v2) { ?>
                        <a href="https://shop.gogo198.cn/attachment/<?php  echo $v2;?>" style="margin-right:5px;">转账文件_<?php  echo str_pad($k2+1,2,'0',STR_PAD_LEFT);?></a>
                        <?php  } } ?>
                        <?php  } ?>
                    </div></div></div>
                </div>
            </div>
            <?php  } ?>

                <div class="info_main otherpay_bankaccount" style="display:none;margin-bottom:10px;">
                <div class="line"><div class="title">账户类型</div><div class='info'><div class='inner'>
                    <span class="bankaccount_mode" data-val="1"><i class="fa <?php echo $other_pay['bankaccount_mode']==1?'fa-check-circle-o':'fa-circle-o'?>"></i>平台账户</span>
                    <span class="bankaccount_mode" data-val="2" style="margin-left:5px;"><i class="fa  <?php echo $other_pay['bankaccount_mode']==2?'fa-check-circle-o':'fa-circle-o'?>"></i>发起人账户</span>
                    <input type="text" name="bankaccount_mode" id="bankaccount_mode" value="<?php echo empty($other_pay['bankaccount_mode'])?1:$other_pay['bankaccount_mode']?>" style="display: none;">
                </div></div></div>
                <div class="line">
                    <div class="title">选择账号</div>
                    <div class="info"><div class="inner">
                        <select name="bank_account" readonly="readonly" style="display: none;width:80%;">
                            <option value="">请选择平台账户</option>
                            <?php  if(is_array($platform_account)) { foreach($platform_account as $i => $v) { ?>
                            <option value="<?php  echo $v['id'];?>"><?php  echo $v['name'];?>-<?php  echo $v['bank_name'];?>-<?php  echo $v['bank_account'];?></option>
                            <?php  } } ?>
                        </select>
                        <select name="bank_account2"  readonly="readonly" style="display:none;width:80%;">
                            <option value="">请选择发起人账户</option>
                            <?php  if(is_array($sender_account)) { foreach($sender_account as $i => $v) { ?>
                            <option value="<?php  echo $v['id'];?>"><?php  echo $v['account_name'];?>-<?php  echo $v['bank_name'];?>-<?php  echo $v['account'];?></option>
                            <?php  } } ?>
                        </select>
                    </div></div>
                </div>
            </div>

        <?php  } ?>

        <div class="otherpay_btn" style="display:none;margin-bottom:10px;align-items: center;justify-content: space-around;">
            <div class="btn btn_left" style="color:#fff;background:#B94AA4;border:0;font-size:15px;padding:8px 12px;box-sizing:border-box;height:fit-content;">我已通过其他方式付款</div>
            <div class="btn btn_right" style="color:#fff;background:#fa3669;border:0;font-size:15px;padding:8px 12px;box-sizing:border-box;height:fit-content;">我要通过其他方式付款</div>
        </div>
        <div class="info_main otherpay" style="display:none;margin-bottom:10px;">
            <div class="line"><div class="title">付款方式</div><div class='info'><div class='inner'>
                <!--style="color:#0c9;"-->
                <span class="payment_mode" data-val="1"><i class="fa <?php echo $other_pay['payment_mode']==1?'fa-check-circle-o':'fa-circle-o'?>"></i>转账</span>
                <span class="payment_mode" data-val="2" style="margin-left:5px;"><i class="fa  <?php echo $other_pay['payment_mode']==2?'fa-check-circle-o':'fa-circle-o'?>"></i>现金</span>
                <input type="text" name="payment_mode" id="payment_mode" value="<?php echo empty($other_pay['payment_mode'])?1:$other_pay['payment_mode']?>" style="display: none;">
            </div></div></div>
            <div class="transfer_type <?php echo $other_pay['payment_mode']==1?'paymode_show':'paymode_hide'?>">
                <!--转账-->
                <div class="line"><div class="title">付款账户</div><div class='info'><div class='inner'><input type="text" id='pay_account' placeholder="请输入付款账户"  value="<?php  echo $other_pay['pay_account'];?>" /></div></div></div>
                <div class="line"><div class="title">转账金额</div><div class='info'><div class='inner'><input type="number" id='transfer_price' placeholder="请输入转账金额"  value="<?php  echo $other_pay['transfer_price'];?>" onkeyup="value=value.replace(/^\D*(\d*(?:\.\d{0,2})?).*$/g, '$1')"/></div></div></div>
                <div class="line"><div class="title">收款账户</div><div class='info'><div class='inner'><input type="text" id='collect_account' placeholder="请输入收款账户"  value="<?php  echo $other_pay['collect_account'];?>" /></div></div></div>
                <div class="line"><div class="title">转账凭证</div><div class='info'><div class='inner'>
                    <div class="pic img_info" data-ogid='0' data-max='10'>
                        <div class="images">
                            <?php  if(!empty($other_pay['transfer_demo'])) { ?>
                            <?php  if(is_array($other_pay['transfer_demo'])) { foreach($other_pay['transfer_demo'] as $v2) { ?>
                            <div data-img="<?php  echo $v2;?>" class="img">
                                <img src="https://shop.gogo198.cn/attachment/<?php  echo $v2;?>" onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png" >
                                <div class="minus minus_del">
                                    <i class="fa fa-minus-circle"></i>
                                </div>
                            </div>
                            <?php  } } ?>
                            <?php  } ?>
                        </div>
                        <div class="plus" style="position:relative;" ><i class="fa fa-plus" style="position:absolute;"></i>
                            <input type="file" name='imgFile0' id='imgFile0'  style="position:absolute;width:30px;height:30px;-webkit-tap-highlight-color: transparent;filter:alpha(Opacity=0); opacity: 0;" />
                        </div>
                    </div>
                </div></div></div>
            </div>
            <div class="cash_type  <?php echo $other_pay['payment_mode']==2?'paymode_show':'paymode_hide'?>" >
                <!--现金-->
                <div class="line"><div class="title">实际支付额</div><div class='info'><div class='inner'><input type="number" id='true_pay_price' placeholder="请输入实际支付额"  value="<?php  echo $other_pay['true_pay_price'];?>" onkeyup="value=value.replace(/^\D*(\d*(?:\.\d{0,2})?).*$/g, '$1')"/></div></div></div>
                <div class="line"><div class="title">支付时间</div><div class="info"><div class="inner"><input type="text" id="cash_paytime" placeholder="点击选择日期" readonly value="<?php  echo $other_pay['cash_paytime'];?>"/></div></div></div>
                <div class="line"><div class="title">收款职员</div><div class="info"><div class="inner"><input type="text" id='collect_staff' placeholder="请输入收款职员名称" value="<?php  echo $other_pay['collect_staff'];?>" /></div></div></div>
                <div class="line"><div class="title">收款凭证</div><div class="info"><div class="inner">
                    <div class="pic img_info" data-ogid='1' data-max='10'>
                        <div class="images">
                            <?php  if(!empty($other_pay['collect_demo'])) { ?>
                            <?php  if(is_array($other_pay['collect_demo'])) { foreach($other_pay['collect_demo'] as $v2) { ?>
                            <div data-img="<?php  echo $v2;?>" class="img">
                                <img src="https://shop.gogo198.cn/attachment/<?php  echo $v2;?>" onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png" >
                                <div class="minus minus_del">
                                    <i class="fa fa-minus-circle"></i>
                                </div>
                            </div>
                            <?php  } } ?>
                            <?php  } ?>
                        </div>
                        <div class="plus" style="position:relative;" ><i class="fa fa-plus" style="position:absolute;"></i>
                            <input type="file" name='imgFile1' id='imgFile1'  style="position:absolute;width:30px;height:30px;-webkit-tap-highlight-color: transparent;filter:alpha(Opacity=0); opacity: 0;" />
                        </div>
                    </div>
                </div></div></div>
            </div>
            <!--提交-->
            <div class="info_sub">提交</div>
        </div>
        <div class="info_main otherpay_bankaccount" style="display:none;margin-bottom:10px;">
            <div class="line"><div class="title">账户类型</div><div class='info'><div class='inner'>
                <span class="bankaccount_mode" data-val="1"><i class="fa <?php echo $other_pay['bankaccount_mode']==1?'fa-check-circle-o':'fa-circle-o'?>"></i>平台账户</span>
                <span class="bankaccount_mode" data-val="2" style="margin-left:5px;"><i class="fa  <?php echo $other_pay['bankaccount_mode']==2?'fa-check-circle-o':'fa-circle-o'?>"></i>发起人账户</span>
                <input type="text" name="bankaccount_mode" id="bankaccount_mode" value="<?php echo empty($other_pay['bankaccount_mode'])?1:$other_pay['bankaccount_mode']?>" style="display: none;">
            </div></div></div>
            <div class="line">
                <div class="title">选择账号</div>
                <div class="info"><div class="inner">
                    <select name="bank_account" id="bank_account1" style="display: none;width:80%;">
                        <option value="">请选择平台账户</option>
                        <?php  if(is_array($platform_account)) { foreach($platform_account as $i => $v) { ?>
                            <option value="<?php  echo $v['id'];?>"><?php  echo $v['name'];?>-<?php  echo $v['bank_name'];?>-<?php  echo $v['bank_account'];?></option>
                        <?php  } } ?>
                    </select>
                    <select name="bank_account2" id="bank_account2" style="display:none;width:80%;">
                        <option value="">请选择发起人账户</option>
                        <?php  if(is_array($sender_account)) { foreach($sender_account as $i => $v) { ?>
                            <option value="<?php  echo $v['id'];?>"><?php  echo $v['account_name'];?>-<?php  echo $v['bank_name'];?>-<?php  echo $v['account'];?></option>
                        <?php  } } ?>
                    </select>
                </div></div>
            </div>
            <div class="info_sub2">提交</div>
        </div>
    <?php  } ?>
</div>
<div class="mask" style="display:none;position: fixed; margin: 0px; padding: 0px; opacity: 0.6; background: rgb(0, 0, 0); z-index: 999; width: 100%; height: 100%; transition: all 0.2s ease 0s;left:0;top:0;"></div>
<div id="bankaccount_qrcode" style="display: none;transform: translate(-50%,-50%);transition: all 0.2s;top: 50%;left: 50%;position: fixed;z-index:1000;"></div>
<div class="saveaspng" style="color:#fff;display:none;transform: translate(-50%,-50%);transition: all 0.2s;top: 70%;left: 50%;position: fixed;z-index:1000;">长按二维码保存</div>
<script id="tpl_img" type="text/html">
    <div class='img' data-img='<%filename%>'>
        <img src='<%url%>'  onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png">
        <div class='minus'><i class='fa fa-minus-circle'></i></div>
    </div>
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('order/pay/wechat_jie', TEMPLATE_INCLUDEPATH)) : (include template('order/pay/wechat_jie', TEMPLATE_INCLUDEPATH));?>
<!-- wechat pay -->
<form action="" id="wecpay" method="post">
	<input type="hidden" name="tid" id="tid" value="" />
	<input type="hidden" name="opid" id="opid" value="" />
	<input type="hidden" name="fee" id="fee" value="" />
	<input type="hidden" name="title" id="title" value="" />
	<input type="hidden" name="acc" id="acc" value="" />
	<input type="hidden" name="ky" id="ky" value="" />
	<input type="hidden" name="uniacid" id="uniacid" value="" />
	<input type="hidden" name="to" id="to" value="" />
	<input type="hidden" name="project" id="project" value="" />
</form>
<!--<script src="../addons/sz_yi/static/js/jspdf.min.js"></script>-->
<!--<script src="../addons/sz_yi/static/js/html2canvas.js"></script>-->
<!--<script src="../addons/sz_yi/template/pc/default/static/js/jquery.min.js"></script>-->
<script src="../addons/sz_yi/static/resource/js/lib/jquery.qrcode.min.js"></script>
<script language="javascript">
    require(['tpl', 'core'], function(tpl, core) {
        //收款审核
        $('.check_collect').click(function (){
           //确认收款
            $.ajax({
                url:"<?php  echo $this->createMobileUrl('member/custompayment');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'examine',orderid:"<?php  echo $data['id'];?>",type:1},
                success:function(json) {
                    if(json.status==-1){
                        alert(json.result);
                    }else{
                        alert('审核成功！');
                        setTimeout(function(){
                            window.location.reload();
                        },2000)
                    }
                },error:function(json){
                    alert('数据出错！');
                }
            });
        });
        $('.nocheck_collect').click(function (){
            //未予收款
            $.ajax({
                url:"<?php  echo $this->createMobileUrl('member/custompayment');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'examine',orderid:"<?php  echo $data['id'];?>",type:2},
                success:function(json) {
                    if(json.status==-1){
                        alert(json.result);
                    }else{
                        alert('审核成功，消息已下发！');
                        setTimeout(function(){
                            window.location.reload();
                        },2000)
                    }
                },error:function(json){
                    alert('数据出错！');
                }
            });
        });

        $('#receipt_status').change(function(){
            let selected = $(this).val();
            if(selected==1 || selected==2){
                $('.should_payMoney').hide();
            }else if(selected==3){
                $('.should_payMoney').show();
            }
        });
        $('.pay_sure').click(function(){
            window.location.href="./index.php?i=3&c=entry&do=member&p=custompayment&op=sure_attestation&m=sz_yi&oid=<?php  echo $data['id'];?>";
        });
        $('.info_sub3').click(function(){
            let receipt_status = $('#receipt_status').val();
            let should_payMoney = $('#should_payMoney').val();
            if(receipt_status==3 && $('#should_payMoney').isEmpty()){
                alert('请输入应到账金额');return;
            }
            $.ajax({
                url:"<?php  echo $this->createMobileUrl('member/custompayment');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'examine',orderid:"<?php  echo $data['id'];?>",type:receipt_status,should_payMoney:should_payMoney},
                success:function(json) {
                    if(json.status==-1){
                        alert(json.result);
                    }else{
                        alert('审核成功，消息已下发！');
                        setTimeout(function(){
                            window.location.reload();
                        },2000)
                    }
                },error:function(json){
                    alert('数据出错！');
                }
            });
        });

        //现金支付时间
        var currYear = (new Date()).getFullYear();
        var opt = {};
        opt.date = {preset: 'date'};
        opt.datetime = {preset: 'datetime'};
        opt.time = {preset: 'time'};
        opt.default = {
            theme: 'android-ics light',
            display: 'modal',
            mode: 'scroller',
            lang: 'zh',
            startYear: currYear,
            endYear: currYear+1
        };
        $("#cash_paytime").scroller('destroy').scroller($.extend(opt['datetime'], opt['default']));

        //上传凭证
        $('.plus input').change(function() {
            core.loading('正在上传');

            var comment =$(this).closest('.img_info');
            var ogid = comment.data('ogid');
            var max = comment.data('max');

            $.ajaxFileUpload({
                url: core.getUrl('util/uploader'),
                data: {file: "imgFile" + ogid,'op':'uploadFile'},
                secureuri: false,
                fileElementId: 'imgFile' + ogid,
                dataType: 'json',
                success: function(res, status) {
                    core.removeLoading();
                    var obj = $(tpl('tpl_img', res));
                    $('.images',comment).append(obj);

                    $('.minus',comment).click(function() {
                        core.json('util/uploader', {op: 'remove', file: $(obj).data('img')}, function(rjson) {
                            if (rjson.status == 1) {
                                $(obj).remove();
                            }
                            $('.plus',comment).show();
                        }, false, true);
                    });

                    if ($('.img',comment).length >= max) {
                        $('.plus',comment).hide();
                    }
                }, error: function(data, status, e) {
                    core.removeLoading();
                    core.tip.show('上传失败!');
                }
            });
        });

        //其它支付
        $('.order_otherpay').click(function(){
            $('.otherpay_btn').show();
            $('.otherpay_btn').css('display','flex');
            var h=$(window).scrollTop(); //获取当前滚动条距离顶部的位置
            var op_height = $('.otherpay_btn').height();
            $("html,body").animate({ scrollTop: h+op_height }, 800);//点击按钮向下移动800px，时间为800毫秒
        });

        //我已通过xxxx
        $('.btn_left').click(function(){
            $('.otherpay_bankaccount').hide();
            $('.otherpay').show();
            var h=$(window).scrollTop(); //获取当前滚动条距离顶部的位置
            var op_height = $('.otherpay').height();
            $("html,body").animate({ scrollTop: h+op_height }, 800);//点击按钮向下移动800px，时间为800毫秒
        });

        //我要通过xxxx
        $('.btn_right').click(function(){
            $('.otherpay').hide();
            $('.otherpay_bankaccount').show();
            var h=$(window).scrollTop(); //获取当前滚动条距离顶部的位置
            var op_height = $('.otherpay_bankaccount').height();
            $("html,body").animate({ scrollTop: h+op_height }, 800);//点击按钮向下移动800px，时间为800毫秒
        });

        //付款方式
        $('.payment_mode').click(function() {
            var $this = $(this);
            var val = $this.data('val');
            $('.payment_mode').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#payment_mode').val(val);
            if(val==1){
                $('.transfer_type').show();
                $('.cash_type').hide();
            }else if(val==2) {
                $('.cash_type').show();
                $('.transfer_type').hide();
            }
            var h=$(window).scrollTop(); //获取当前滚动条距离顶部的位置
            var op_height = $('.otherpay').height();
            $("html,body").animate({ scrollTop: h+op_height }, 800);//点击按钮向下移动800px，时间为800毫秒
        });

        //账号类型
        $('.bankaccount_mode').click(function(){
            var $this = $(this);
            var val = $this.data('val');
            $('.bankaccount_mode').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#bankaccount_mode').val(val);
            if(val==1){
                $('#bank_account1').show();
                $('#bank_account2').hide();
            }else if(val==2) {
                $('#bank_account2').show();
                $('#bank_account1').hide();
            }
            var h=$(window).scrollTop(); //获取当前滚动条距离顶部的位置
            var op_height = $('.otherpay_bankaccount').height();
            $("html,body").animate({ scrollTop: h+op_height }, 800);//点击按钮向下移动800px，时间为800毫秒
        });

        //其他方式提交
        $('.info_sub').click(function(){
            let payment_mode = $('#payment_mode').val();
            //转账
            let pay_account = $('#pay_account').val();
            let transfer_price = $('#transfer_price').val();
            let collect_account = $('#collect_account').val();
            let transfer_demo = [];
            $('.img_info[data-ogid=0]').find('.img').each(function(){
                transfer_demo.push($(this).data('img'));
            });
            //现金
            let true_pay_price = $('#true_pay_price').val();
            let cash_paytime = $('#cash_paytime').val();
            let collect_staff = $('#collect_staff').val();
            let collect_demo = [];
            $('.img_info[data-ogid=1]').find('.img').each(function(){
                collect_demo.push($(this).data('img'));
            });

            if(payment_mode==1){
                //转账
                if($('#pay_account').isEmpty()){
                    alert('请输入付款账户!');
                    return;
                }
                if($('#transfer_price').isEmpty()){
                    alert('请输入转账金额!');
                    return;
                }
                if($('#collect_account').isEmpty()){
                    alert('请输入收款账户!');
                    return;
                }
                if( transfer_demo.length=='' || transfer_demo.length==0){
                    alert('请上传转账凭证!');
                    return;
                }
            }else if(payment_mode==2){
                //现金
                if($('#true_pay_price').isEmpty()){
                    alert('请输入实际支付额!');
                    return;
                }
                if($('#cash_paytime').isEmpty()){
                    alert('请选择支付时间!');
                    return;
                }
                if($('#collect_staff').isEmpty()){
                    alert('请输入收款职员名称!');
                    return;
                }
                if( collect_demo.length=='' || collect_demo.length==0){
                    alert('请上传收款凭证!');
                    return;
                }
            }

            $.ajax({
                url:"<?php  echo $this->createMobileUrl('member/custompayment');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'otherpay',orderid:"<?php  echo $data['id'];?>",'payment_mode':payment_mode,'pay_account':pay_account,'transfer_price':transfer_price,'collect_account':collect_account,'transfer_demo':transfer_demo,'true_pay_price':true_pay_price,'cash_paytime':cash_paytime,'collect_staff':collect_staff,'collect_demo':collect_demo},
                success:function(json) {
                    if(json.status==-1){
                        alert(json.result.msg);
                    }else{
                        alert('提交成功！');
                        setTimeout(function(){
                            window.location.reload();
                        },2000)
                    }
                },error:function(json){
                    alert('数据出错！');
                }
            });
        });

        $('.mask').click(function(){
            $('#bankaccount_qrcode').hide();
            $('.mask').hide();
            $('.saveaspng').hide();
        });
        
        
        //银行账户提交
        $('.info_sub2').click(function(){
            let bankaccount_mode = $('#bankaccount_mode').val();
            if(bankaccount_mode==1){
                var bank_account = $('#bank_account1').val();
            }else if(bankaccount_mode==2){
                var bank_account = $('#bank_account2').val();
            }
            if(bank_account=='' || typeof(bank_account)=='undefined'){
                alert('请选择账户');return false;
            }

            //下载pdf\
           $('#bankaccount_qrcode').html('');
            jQuery('#bankaccount_qrcode').qrcode({
                render: "canvas",
                width: 250,
                height: 250,
                text: "https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&op=save_bankaccount_pdf&bankMode="+bankaccount_mode+"&bankAcc="+bank_account
                // text: "https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&m=sz_yi&p=bind_decl"
            });
            
            var canvas = document.getElementsByTagName('canvas');
            var image = new Image();
        	image.src = canvas[0].toDataURL("image/png");
        	$('#bankaccount_qrcode').html(image);
            $('#bankaccount_qrcode').show();
            $('.saveaspng').show();
            $('.mask').show();
            
            
            $.ajax({
                url:"<?php  echo $this->createMobileUrl('member/custompayment');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'otherpay_bankaccount',orderid:"<?php  echo $data['id'];?>",'bankaccount_mode':bankaccount_mode,'bank_account':bank_account},
                success:function(json) {
                    if(json.status==-1){
                        alert(json.result.msg);
                    }else{
                        alert('提交成功！');
                        // setTimeout(function(){
                        //     window.location.reload();
                        // },2000)
                    }
                },error:function(json){
                    alert('数据出错！');
                }
            });
        });

        core.json('member/custompayment',{orderid:"<?php  echo $data['id'];?>",openid:"<?php  echo $data['openid'];?>",op:'pay',iswaitcheck:<?php  echo $iswaitcheck;?>},function(json){
            var result = json.result;
            if(json.status!=1){
                core.tip.show(result);
                return;
            }
            //通莞微信支付  2017-11-01
            if(result.tgwechat.success){ 
                 $('.order_sub12').click(function() {
                    core.json('member/custompayment', {op: 'tgpay',type: 'tgwechat', orderid:"<?php  echo $data['id'];?>",openid:"<?php  echo $data['openid'];?>",overdue_money:"<?php  echo $data['overdue_money'];?>"}, function (rjson) {
    
                        if(rjson.status!=1) {
                            $('.button').removeAttr('submitting');
                            core.tip.show(rjson.result);
                            return;
                        }
    
                        var tgw = rjson.result.tgwechat;
                        
                        //2018-08-21
                        $('#wecpay').attr('action', 'https://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/Paymentess.php');
                        $('#tid').val(tgw.tid);
                        $('#opid').val(tgw.openid);
                        $('#title').val(tgw.title);
                        $('#fee').val(tgw.fee);
                        $('#acc').val(tgw.account);
                        $('#ky').val(tgw.key);
                        $('#to').val(tgw.token);
                        $('#uniacid').val(tgw.uniacid);
                        $('#project').val('custompay');
                        $('#wecpay').submit();
                            
                    },true,true);
                 })
            }
            //2017-11-01
            //通莞支付宝 2017-11-20
            if(result.tgalipay.success){
    
                 $('.order_sub13').click(function(){
                     //数据请求order/pay/op = pay & type = alipay;  没有数据返回：只返回状态 status = 1;
                    // core.json('member/custompayment', {op: 'tgpay',type: 'tgalipay', orderid:"<?php  echo $data['id'];?>",openid:"<?php  echo $data['openid'];?>"}, function (rjson) {
                    //         if(rjson.status!=1) {
                    //             core.tip.show(rjson.result);
                    //             return;
                    //         }
                    //         alert('123');return;
                            location.href = core.getUrl('member/custompayment',{op: 'tgpay',type: 'tgalipay', orderid:"<?php  echo $data['id'];?>",openid:"<?php  echo $data['openid'];?>",overdue_money:"<?php  echo $data['overdue_money'];?>"});
                       
                       //virtual 跳转链接：mobile/order/pay_alipay.php?orderid = $_GPC[orderid];
                       //location.href = core.getUrl('order/tgpay_alipay',{orderid:'<?php  echo $_GPC['orderid'];?>'});
                        // console.log(core.getUrl('order/tgpay_alipay',{orderid:'<?php  echo $_GPC['orderid'];?>'}));
                       //return;
    
                    // },true,true);
                 })
           }
        },true);
    })
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>