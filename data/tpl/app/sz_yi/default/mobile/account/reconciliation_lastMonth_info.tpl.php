<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<!--<title>项目配置中心</title>-->
<style type="text/css">
    body {margin:0px; background:#efefef; font-family:'微软雅黑'; -moz-appearance:none;}
    .info_main {height:auto;  background:#fff; margin-top:14px; border-bottom:1px solid #e8e8e8; border-top:1px solid #e8e8e8;}
    .info_main .line {margin:0 10px; height:40px; border-bottom:1px solid #e8e8e8; line-height:40px; color:#999;display:flex;}
    .info_main .line .title {height:40px; width:80px; line-height:40px; color:#444; float:left; font-size:14px;}
    .info_main .line .info { width:100%;float:right;margin-left:-80px; }
    .info_main .line .inner { margin-left:80px; }
    .info_main .line .inner input {height:40px; width:100%;display:block; padding:0px; margin:0px; border:0px; float:left; font-size:14px;}
    .info_main .line .inner .user_sex {line-height:40px;}
    .info_sub {height:44px; margin:14px 5px; background:#31cd00; border-radius:4px; text-align:center; font-size:16px; line-height:44px; color:#fff;}
    .select { border:1px solid #ccc;height:25px;}
    .table{width:100%;font-size:15px;}
    .table tr{height:25px;}
    .table tr td{vertical-align: middle !important;}
    /*.table tr td:nth-of-type(1){width:88%;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;}*/
    .table tr td:nth-of-type(2){width:20%;}
    .see{box-sizing: border-box;padding: 4px 10px;font-size: 14px;color: #fff;background: #1E9FFF;text-align:center;}
    .chakan{font-size:14px;padding:4px 10px;background:#1EA01E;width:fit-content;color:#fff;}
    .submit{font-size:14px;padding:4px 10px;background:#ff5555;width:fit-content;margin-left:10px;margin-right:5px;color:#fff;}
    .sign_box{display:none;width: 80%;position: absolute;background: #ffffff;border-radius: 5px;z-index:1000;transform:translate(-50%,-50%);top: 50%;left:50%;}
    .sign_box .confirm{height: 50px;border-top: 1px solid #eee;display: flex;}
    .sign_box .confirm>span{flex: 1;height: 50px;line-height: 50px;font-size: 16px;text-align: center;}
    .sign_box .confirm>span:nth-child(1){color: red;}
    .sign_box .confirm>span:nth-child(2){border-left: 1px solid #eee;}
    .sign_box .important{color: red;}
    .sign_box .title{text-align: center;border-bottom: 1px solid #eee;margin-bottom: 10px;}
    .sign_box .title>p{height: 40px;line-height: 40px;text-align: center;font-size: 18px;font-weight: bold;}

    .page_head{width:100%;background:#fff;margin-bottom:5px;box-shadow:0 0 4px #ddd;padding:10px 0 10px;display:flex;align-items:center;}
    .page_head .left{width:20%;display:flex;align-items:center;font-size:15px;}
    .page_head .left .back{width:13px;height:13px;border-top:2px solid #000;border-left:2px solid #000;transform:rotate(-50deg);margin-left:15px;margin-right:5px;}
    .page_head .right{width:80%;text-align:center;padding-right:80px;font-size:15px;padding-top:2px;}

    .correct_table{width: 100%;background: #1EA01E;color: #fff;font-size: 15px;padding: 5px;text-align: center;margin-top: 5px;}
    .fault_table{width: 100%;background: #ff5555;color: #fff;font-size: 15px;padding: 5px;text-align: center;margin-top: 5px;}
</style>

<link type="text/css" rel="stylesheet" href="../addons/sz_yi/template/pc/default/static/css/bootstrap.min.css" />
<script type="text/javascript" src="../addons/sz_yi/static/template/pc/default/static/js/bootstrap.min.js"></script>
<div class="page_head">
    <div class="left">
        <div class="back"></div>
        <div style="font-size:15px;padding-top:2px;">返回</div>
    </div>
    <div class="right">
        <?php  echo $_lastMonth;?> 对账列表
    </div>
</div>
<div id="container">
    <!--<div class="page_topbar">-->
    <!--    <div class="title">凭证批次号【<?php  echo $my_merchant['batch_num'];?>】</div>-->
    <!--</div>-->
    <div class="info_main table-responsive">
        <?php  if(empty($my_merchant)) { ?>
        <div class="line" style="text-align:center;">暂无信息</div>
        <?php  } else { ?>

        <?php  if(is_array($my_merchant)) { foreach($my_merchant as $val) { ?>
            <?php  if($val['is_show']==1) { ?>
            <div class="psd">
                <div style="text-align:left;margin: 0;padding: 5px 0 5px 10px;background: #CDF;display:flex;align-items:center;justify-content:space-between;">
                    <div>汇总批次号：<?php  echo $val['batch_num'];?></div>
                    <?php  if($val['reconciliation_status2']!=1) { ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <div class="see" onclick="operation(<?php  echo $val['id'];?>,1)" style="margin-right:10px;">确认对账</div>
                    </div>
                    <?php  } ?>
                </div>
                <?php  if($val['reconciliation_status']==2) { ?>
                <div class="correct_table">正确表</div>
                <table class="table table-striped" style="margin-bottom:0;">
                    <tr>
                        <td style="width:50%;">已收款</td>
                        <td style="width:50%;">已付款</td>
                    </tr>
                    <tr>
                        <td style="word-wrap: break-word;overflow: hidden;word-break: break-all;">
                            <?php  echo $val['reconciliation_list']['0'];?>
                        </td>
                        <td style="word-wrap: break-word;overflow: hidden;word-break: break-all;">
                            <?php  echo $val['reconciliation_list']['3'];?>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:50%;">未收款</td>
                        <td style="width:50%;">未付款</td>
                    </tr>
                    <tr>
                        <td style="word-wrap: break-word;overflow: hidden;word-break: break-all;">
                            <?php  echo $val['reconciliation_list']['1'];?>
                        </td>
                        <td style="word-wrap: break-word;overflow: hidden;word-break: break-all;">
                            <?php  echo $val['reconciliation_list']['4'];?>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:50%;">预收款</td>
                        <td style="width:50%;">预付款</td>
                    </tr>
                    <tr>
                        <td style="word-wrap: break-word;overflow: hidden;word-break: break-all;">
                            <?php  echo $val['reconciliation_list']['2'];?>
                        </td>
                        <td style="word-wrap: break-word;overflow: hidden;word-break: break-all;">
                            <?php  echo $val['reconciliation_list']['5'];?>
                        </td>
                    </tr>
                </table>
                <?php  } ?>

                <?php  if($val['reconciliation_status']==1) { ?>
                <div class="correct_table">正确表</div>
                <?php  } ?>
                <?php  if($val['reconciliation_status']==2) { ?>
                <div class="fault_table">错误表</div>
                <?php  } ?>
                <table class="table table-striped" style="margin-bottom:0;">
                    <tr>
                        <td style="width:50%;">已收款</td>
                        <td style="width:50%;">已付款</td>
                    </tr>
                    <tr>
                        <td>
                            <?php  if(!empty($val['all_money']['col_1'])) { ?>
                            <?php  if(is_array($val['all_money']['col_1'])) { foreach($val['all_money']['col_1'] as $k2 => $v2) { ?>
                            (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                            <?php  } } ?>
                            <?php  } else { ?>
                            0.00
                            <?php  } ?>
                        </td>
                        <td>
                            <?php  if(!empty($val['all_money']['pay_1'])) { ?>
                            <?php  if(is_array($val['all_money']['pay_1'])) { foreach($val['all_money']['pay_1'] as $k2 => $v2) { ?>
                            (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                            <?php  } } ?>
                            <?php  } else { ?>
                            0.00
                            <?php  } ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:50%;">未收款</td>
                        <td style="width:50%;">未付款</td>
                    </tr>
                    <tr>
                        <td>
                            <?php  if(!empty($val['all_money']['col_2'])) { ?>
                            <?php  if(is_array($val['all_money']['col_2'])) { foreach($val['all_money']['col_2'] as $k2 => $v2) { ?>
                            (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                            <?php  } } ?>
                            <?php  } else { ?>
                            0.00
                            <?php  } ?>
                        </td>
                        <td>
                            <?php  if(!empty($val['all_money']['pay_2'])) { ?>
                            <?php  if(is_array($val['all_money']['pay_2'])) { foreach($val['all_money']['pay_2'] as $k2 => $v2) { ?>
                            (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                            <?php  } } ?>
                            <?php  } else { ?>
                            0.00
                            <?php  } ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:50%;">预收款</td>
                        <td style="width:50%;">预付款</td>
                    </tr>
                    <tr>
                        <td>
                            <?php  if(!empty($val['all_money']['col_3'])) { ?>
                            <?php  if(is_array($val['all_money']['col_3'])) { foreach($val['all_money']['col_3'] as $k2 => $v2) { ?>
                            (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                            <?php  } } ?>
                            <?php  } else { ?>
                            0.00
                            <?php  } ?>
                        </td>
                        <td>
                            <?php  if(!empty($val['all_money']['pay_3'])) { ?>
                            <?php  if(is_array($val['all_money']['pay_3'])) { foreach($val['all_money']['pay_3'] as $k2 => $v2) { ?>
                            (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                            <?php  } } ?>
                            <?php  } else { ?>
                            0.00
                            <?php  } ?>
                        </td>
                    </tr>
                </table>

                <table class="table table-striped caozuo" style="margin-top:10px;">
                    <tr>
                        <td>凭证编码</td>
                        <td style="width:25%;">操作</td>
                    </tr>
                    <?php  if(is_array($val['voucher'])) { foreach($val['voucher'] as $v2) { ?>
                    <?php  if($v2['id']>0) { ?>
                    <tr>
                        <td><?php  echo $v2['voucher'];?></td>
                        <td>
                            <div class="chakan" onClick="javascript:window.location.href='./index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&op=voucher_info&id=<?php  echo $v2['id'];?>';">查看</div>
                        </td>
                    </tr>
                    <?php  } ?>
                    <?php  } } ?>
                </table>
            </div>

            <?php  } ?>
        <?php  } } ?>
        <?php  } ?>
    </div>
</div>

<!--凭证签收-->
<div class="mask" style="position: fixed; margin: 0px; padding: 0px;background: rgba(0, 0, 0,0.6); z-index: 999; width: 100%; height: 100%; transition: all 0.2s ease 0s;display:none;left: 0;top: 0;"></div>
<div class="sign_box">
    <div class="title">
        <p class="important">输入正确金额</p>
    </div>
    <input type="text" name="batch_id" id="batch_id" value="" style="display:none;"/>
    <p style="font-size:15px;text-align:center;">输入规则：币种/金额,币种/金额</p>
    <div class="info_main">
        <div class="line"><div class="title">已收款</div><div class='info'>
            <div class='inner'>
                <input type="text" name="col_1" id="col_1" value="" placeholder="请输入已收款金额和币种"/>
            </div></div></div>
        <div class="line"><div class="title">未收款</div><div class='info'>
            <div class='inner'>
                <input type="text" name="col_2" id="col_2" value="" placeholder="请输入未收款金额和币种"/>
            </div></div></div>
        <div class="line"><div class="title">预收款</div><div class='info'>
            <div class='inner'>
                <input type="text" name="col_3" id="col_3" value="" placeholder="请输入预收款金额和币种"/>
            </div></div></div>
        <div class="line"><div class="title">已付款</div><div class='info'>
            <div class='inner'>
                <input type="text" name="pay_1" id="pay_1" value="" placeholder="请输入已付款金额和币种"/>
            </div></div></div>
        <div class="line"><div class="title">未付款</div><div class='info'>
            <div class='inner'>
                <input type="text" name="pay_2" id="pay_2" value="" placeholder="请输入未付款金额和币种"/>
            </div></div></div>
        <div class="line"><div class="title">预付款</div><div class='info'>
            <div class='inner'>
                <input type="text" name="pay_3" id="pay_3" value="" placeholder="请输入预付款金额和币种"/>
            </div></div></div>
    </div>
    <div class="confirm">
        <span class="close-but" onClick="fnClose(1)">取消</span>
        <span class="close-but" onClick="fnClose(2)">确认</span>
    </div>
</div>
<script language="javascript">
    require(['tpl', 'core'], function(tpl, core) {

    });

    $(function(){
        $('.page_head').find('.left').click(function(){
            window.history.back(-1);
        });
        $('.mask').click(function(){
            $('.sign_box').hide();
            $('.mask').hide();
            $('#batch_id').val('');
            $('#col_1').val('');$('#col_2').val('');$('#col_3').val('');$('#pay_1').val('');$('#pay_2').val('');$('#pay_3').val('');
        });
    });

    function operation(id,typ){
        if(typ==1){
            //正确无误
            if(confirm('确定确认对账？')){
                $.ajax({
                    url:"<?php  echo $this->createMobileUrl('account/register');?>",
                    type:'POST',
                    dataType:'json',
                    data:{'op':'reconciliation_lastMonth_info','typ':typ,'batch_id':id},
                    success:function(json) {
                        if(json.status==1){
                            alert(json.result.msg);
                            setTimeout(function(){
                                window.location.reload();
                            },2000);
                        }
                    }
                });
            }
        }else if(typ==2){
            //正确有误
            // var height = 300+document.body.scrollTop;
            // $('.sign_box').css('top',height+'px').show();
            // $('.mask').show();
            // $('#batch_id').val(id);
        }
    }

    //取消选择物流信息
    function fnClose(typ){
        // hui.upToast(msg);

        if(typ==1){
            $('.sign_box').hide();
            $('.mask').hide();
            $('#batch_id').val('');
            $('#col_1').val('');$('#col_2').val('');$('#col_3').val('');$('#pay_1').val('');$('#pay_2').val('');$('#pay_3').val('');
        }else if(typ==2){
            let batch_id = $('#batch_id').val();//凭证批次号id
            let col_1 = $('#col_1').val();let col_2 = $('#col_2').val();let col_3 = $('#col_3').val();
            let pay_1 = $('#pay_1').val();let pay_2 = $('#pay_2').val();let pay_3 = $('#pay_3').val();
            if($('#col_1').isEmpty()){
                alert('请输入已收款金额和币种');return;
            }
            if($('#col_2').isEmpty()){
                alert('请输入未收款金额和币种');return;
            }
            if($('#col_3').isEmpty()){
                alert('请输入预收款金额和币种');return;
            }
            if($('#pay_1').isEmpty()){
                alert('请输入已付款金额和币种');return;
            }
            if($('#pay_2').isEmpty()){
                alert('请输入未付款金额和币种');return;
            }
            if($('#pay_3').isEmpty()){
                alert('请输入预付款金额和币种');return;
            }
            $.ajax({
                url:"<?php  echo $this->createMobileUrl('account/bookkeeping');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'reconciliation_detail','typ':2,'batch_id':batch_id,'col_1':col_1,'col_2':col_2,'col_3':col_3,'pay_1':pay_1,'pay_2':pay_2,'pay_3':pay_3},
                success:function(json) {
                    if(json.status==1){
                        alert(json.result.msg);
                        setTimeout(function(){
                            window.location.reload();
                        },2000);
                    }
                }
            });
        }
    }
</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>