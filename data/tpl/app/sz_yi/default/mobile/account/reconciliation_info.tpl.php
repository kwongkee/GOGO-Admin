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
    .table thead td{background-color: #ecf6fc !important;}
    .table tr{height:25px;}
    .table tr td{vertical-align: middle !important;}
    .disf{display:flex;align-items: center;justify-content: space-evenly;margin-top:10px;}
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
    
    .correct_table{width: 100%;font-size: 15px;color:#17bf76;text-align: center;}
    .fault_table{width: 100%;font-size: 15px;color:#ff5555;text-align: center;}
    a{text-decoration: none;text-underline: none;}
</style>

<link type="text/css" rel="stylesheet" href="../addons/sz_yi/template/pc/default/static/css/bootstrap.min.css" />
<script type="text/javascript" src="../addons/sz_yi/static/template/pc/default/static/js/bootstrap.min.js"></script>
<div class="page_head">
    <div class="left">
        <div class="back"></div>
        <div style="font-size:15px;padding-top:2px;">返回</div>
    </div>
    <div class="right">
        对账列表
    </div>
</div>
<div id="container">
    <!--<div class="page_topbar">-->
    <!--    <div class="title">凭证批次号【<?php  echo $my_merchant['batch_num'];?>】</div>-->
    <!--</div>-->
    <div class="info_main table-responsive" style="border:0;">
        <?php  if(empty($my_merchant)) { ?>
            <div class="line" style="text-align:center;">暂无信息</div>
        <?php  } else { ?>
            <div class="psd">
                <div style="text-align:left;margin: 0;padding: 5px 0 5px 10px;background: #CDF;display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                    <div>商户[<?php  echo $my_merchant['user_name'];?>],对账月份[<?php  echo $my_merchant['reconciliation_date'];?>]</div>
                </div>
                <?php  if(($my_merchant['reconciliation_status']==2 || !empty($my_merchant['reconciliation_list'])) ) { ?>
                    <!--对账有误-->
                    <table class="table table-striped table-bordered" style="width:100%;margin-bottom:0;table-layout:fixed;word-break: break-all;">
                    <thead>
                        <tr>
                            <td align="center">收/付状态</td>
                            <td><div class="fault_table">系统统计数据(错误表)</div></td>
                            <td><div class="correct_table">记账核对数据(正确表)</div></td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>已开票，应收款</td>
                            <td>
                                <?php  if(!empty($my_merchant['reconciliation_sys_list']['all_money']['col_1'])) { ?>
                                    <?php  if(is_array($my_merchant['reconciliation_sys_list']['all_money']['col_1'])) { foreach($my_merchant['reconciliation_sys_list']['all_money']['col_1'] as $k2 => $v2) { ?>
                                    (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                                    <?php  } } ?>
                                <?php  } else { ?>
                                0.00
                                <?php  } ?>
                            </td>
                            <td style="word-wrap: break-word;overflow: hidden;word-break: break-all;">
                                <?php  if(!empty($my_merchant['reconciliation_list'])) { ?>
                                    <?php  if(is_array($my_merchant['reconciliation_list']['0'])) { foreach($my_merchant['reconciliation_list']['0'] as $k => $val) { ?>
                                        <?php  if(!empty($val)) { ?>
                                            <?php  echo $val;?><br>
                                        <?php  } ?>
                                    <?php  } } ?>
                                    <?php  if(empty($my_merchant['reconciliation_list']['0']['0'])) { ?>
                                    0.00
                                    <?php  } ?>
                                <?php  } ?>
                            </td>
                        </tr>
                        <tr>
                            <td>已开票，未收款</td>
                            <td>
                                <?php  if(!empty($my_merchant['reconciliation_sys_list']['all_money']['col_2'])) { ?>
                                <?php  if(is_array($my_merchant['reconciliation_sys_list']['all_money']['col_2'])) { foreach($my_merchant['reconciliation_sys_list']['all_money']['col_2'] as $k2 => $v2) { ?>
                                (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                                <?php  } } ?>
                                <?php  } else { ?>
                                0.00
                                <?php  } ?>
                            </td>
                            <td style="word-wrap: break-word;overflow: hidden;word-break: break-all;">
                                <?php  if(!empty($my_merchant['reconciliation_list'])) { ?>
                                    <?php  if(is_array($my_merchant['reconciliation_list']['1'])) { foreach($my_merchant['reconciliation_list']['1'] as $k => $val) { ?>
                                        <?php  if(!empty($val)) { ?>
                                            <?php  echo $val;?><br>
                                        <?php  } ?>
                                    <?php  } } ?>
                                    <?php  if(empty($my_merchant['reconciliation_list']['1']['0'])) { ?>
                                    0.00
                                    <?php  } ?>
                                <?php  } ?>
                            </td>
                        </tr>
                        <tr>
                            <td>未开票，预收款</td>
                            <td>
                                <?php  if(!empty($my_merchant['reconciliation_sys_list']['all_money']['col_3'])) { ?>
                                <?php  if(is_array($my_merchant['reconciliation_sys_list']['all_money']['col_3'])) { foreach($my_merchant['reconciliation_sys_list']['all_money']['col_3'] as $k2 => $v2) { ?>
                                (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                                <?php  } } ?>
                                <?php  } else { ?>
                                0.00
                                <?php  } ?>
                            </td>
                            <td style="word-wrap: break-word;overflow: hidden;word-break: break-all;">
                                <?php  if(!empty($my_merchant['reconciliation_list'])) { ?>
                                    <?php  if(is_array($my_merchant['reconciliation_list']['2'])) { foreach($my_merchant['reconciliation_list']['2'] as $k => $val) { ?>
                                        <?php  if(!empty($val)) { ?>
                                            <?php  echo $val;?><br>
                                        <?php  } ?>
                                    <?php  } } ?>
                                    <?php  if(empty($my_merchant['reconciliation_list']['2']['0'])) { ?>
                                    0.00
                                    <?php  } ?>
                                <?php  } ?>
                            </td>
                        </tr>
                        <tr>
                            <td>已收票，应付款</td>
                            <td>
                                <?php  if(!empty($my_merchant['reconciliation_sys_list']['all_money']['pay_1'])) { ?>
                                <?php  if(is_array($my_merchant['reconciliation_sys_list']['all_money']['pay_1'])) { foreach($my_merchant['reconciliation_sys_list']['all_money']['pay_1'] as $k2 => $v2) { ?>
                                (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                                <?php  } } ?>
                                <?php  } else { ?>
                                0.00
                                <?php  } ?>
                            </td>
                            <td style="word-wrap: break-word;overflow: hidden;word-break: break-all;">
                                <?php  if(!empty($my_merchant['reconciliation_list'])) { ?>
                                    <?php  if(is_array($my_merchant['reconciliation_list']['3'])) { foreach($my_merchant['reconciliation_list']['3'] as $k => $val) { ?>
                                        <?php  if(!empty($val)) { ?>
                                            <?php  echo $val;?><br>
                                        <?php  } ?>
                                    <?php  } } ?>
                                    <?php  if(empty($my_merchant['reconciliation_list']['3']['0'])) { ?>
                                    0.00
                                    <?php  } ?>
                                <?php  } ?>
                            </td>
                        </tr>
                        <tr>
                            <td>已收票，未付款</td>
                            <td>
                                <?php  if(!empty($my_merchant['reconciliation_sys_list']['all_money']['pay_2'])) { ?>
                                <?php  if(is_array($my_merchant['reconciliation_sys_list']['all_money']['pay_2'])) { foreach($my_merchant['reconciliation_sys_list']['all_money']['pay_2'] as $k2 => $v2) { ?>
                                (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                                <?php  } } ?>
                                <?php  } else { ?>
                                0.00
                                <?php  } ?>
                            </td>
                            <td style="word-wrap: break-word;overflow: hidden;word-break: break-all;">
                                <?php  if(!empty($my_merchant['reconciliation_list'])) { ?>
                                    <?php  if(is_array($my_merchant['reconciliation_list']['4'])) { foreach($my_merchant['reconciliation_list']['4'] as $k => $val) { ?>
                                        <?php  if(!empty($val)) { ?>
                                            <?php  echo $val;?><br>
                                        <?php  } ?>
                                    <?php  } } ?>
                                    <?php  if(empty($my_merchant['reconciliation_list']['4']['0'])) { ?>
                                    0.00
                                    <?php  } ?>
                                <?php  } ?>
                            </td>
                        </tr>
                        <tr>
                            <td>未收票，预付款</td>
                            <td>
                                <?php  if(!empty($my_merchant['reconciliation_sys_list']['all_money']['pay_3'])) { ?>
                                <?php  if(is_array($my_merchant['reconciliation_sys_list']['all_money']['pay_3'])) { foreach($my_merchant['reconciliation_sys_list']['all_money']['pay_3'] as $k2 => $v2) { ?>
                                (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                                <?php  } } ?>
                                <?php  } else { ?>
                                0.00
                                <?php  } ?>
                            </td>
                            <td style="word-wrap: break-word;overflow: hidden;word-break: break-all;">
                                <?php  if(!empty($my_merchant['reconciliation_list'])) { ?>
                                    <?php  if(is_array($my_merchant['reconciliation_list']['5'])) { foreach($my_merchant['reconciliation_list']['5'] as $k => $val) { ?>
                                        <?php  if(!empty($val)) { ?>
                                            <?php  echo $val;?><br>
                                        <?php  } ?>
                                    <?php  } } ?>
                                    <?php  if(empty($my_merchant['reconciliation_list']['5']['0'])) { ?>
                                    0.00
                                    <?php  } ?>
                                <?php  } ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php  } ?>

                <?php  if(( $my_merchant['reconciliation_status']==1 && empty($my_merchant['reconciliation_list'])) ) { ?>
                    <!--对账无误-->
                    <table class="table table-striped table-bordered" style="width:100%;margin-bottom:0;table-layout:fixed;word-break: break-all;">
                        <thead>
                        <tr>
                            <td align="center">收/付状态</td>
                            <td><div class="correct_table">系统核对数据</div></td>
                        </tr>
                        </thead>

                        <tr>
                            <td>已开票，应收款</td>
                            <td>
                                <?php  if(!empty($my_merchant['reconciliation_sys_list']['all_money']['col_1'])) { ?>
                                <?php  if(is_array($my_merchant['reconciliation_sys_list']['all_money']['col_1'])) { foreach($my_merchant['reconciliation_sys_list']['all_money']['col_1'] as $k2 => $v2) { ?>
                                (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                                <?php  } } ?>
                                <?php  } else { ?>
                                0.00
                                <?php  } ?>
                            </td>
                        </tr>
                        <tr>
                            <td>已开票，未收款</td>
                            <td>
                                <?php  if(!empty($my_merchant['reconciliation_sys_list']['all_money']['col_2'])) { ?>
                                <?php  if(is_array($my_merchant['reconciliation_sys_list']['all_money']['col_2'])) { foreach($my_merchant['reconciliation_sys_list']['all_money']['col_2'] as $k2 => $v2) { ?>
                                (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                                <?php  } } ?>
                                <?php  } else { ?>
                                0.00
                                <?php  } ?>
                            </td>
                        </tr>
                        <tr>
                            <td>未开票，预收款</td>
                            <td>
                                <?php  if(!empty($my_merchant['reconciliation_sys_list']['all_money']['col_3'])) { ?>
                                <?php  if(is_array($my_merchant['reconciliation_sys_list']['all_money']['col_3'])) { foreach($my_merchant['reconciliation_sys_list']['all_money']['col_3'] as $k2 => $v2) { ?>
                                (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                                <?php  } } ?>
                                <?php  } else { ?>
                                0.00
                                <?php  } ?>
                            </td>
                        </tr>
                        <tr>
                            <td>已收票，应付款</td>
                            <td>
                                <?php  if(!empty($my_merchant['reconciliation_sys_list']['all_money']['pay_1'])) { ?>
                                <?php  if(is_array($my_merchant['reconciliation_sys_list']['all_money']['pay_1'])) { foreach($my_merchant['reconciliation_sys_list']['all_money']['pay_1'] as $k2 => $v2) { ?>
                                (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                                <?php  } } ?>
                                <?php  } else { ?>
                                0.00
                                <?php  } ?>
                            </td>
                        </tr>
                        <tr>
                            <td>已收票，未付款</td>
                            <td>
                                <?php  if(!empty($my_merchant['reconciliation_sys_list']['all_money']['pay_2'])) { ?>
                                <?php  if(is_array($my_merchant['reconciliation_sys_list']['all_money']['pay_2'])) { foreach($my_merchant['reconciliation_sys_list']['all_money']['pay_2'] as $k2 => $v2) { ?>
                                (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                                <?php  } } ?>
                                <?php  } else { ?>
                                0.00
                                <?php  } ?>
                            </td>
                        </tr>
                        <tr>
                            <td>未收票，预付款</td>
                            <td>
                                <?php  if(!empty($my_merchant['reconciliation_sys_list']['all_money']['pay_3'])) { ?>
                                <?php  if(is_array($my_merchant['reconciliation_sys_list']['all_money']['pay_3'])) { foreach($my_merchant['reconciliation_sys_list']['all_money']['pay_3'] as $k2 => $v2) { ?>
                                (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                                <?php  } } ?>
                                <?php  } else { ?>
                                0.00
                                <?php  } ?>
                            </td>
                        </tr>
                    </table>
                <?php  } ?>

                <?php  if(( $my_merchant['reconciliation_manage_status']==2 && empty($my_merchant['reconciliation_list'])) ) { ?>
                <!--对账无误-->
                <table class="table table-striped table-bordered" style="width:100%;margin-bottom:0;table-layout:fixed;word-break: break-all;">
                    <thead>
                    <tr>
                        <td align="center">收/付状态</td>
                        <td><div class="correct_table">系统核对数据</div></td>
                    </tr>
                    </thead>

                    <tr>
                        <td>已开票，应收款</td>
                        <td>
                            <?php  if(!empty($my_merchant['reconciliation_sys_list']['all_money']['col_1'])) { ?>
                            <?php  if(is_array($my_merchant['reconciliation_sys_list']['all_money']['col_1'])) { foreach($my_merchant['reconciliation_sys_list']['all_money']['col_1'] as $k2 => $v2) { ?>
                            (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                            <?php  } } ?>
                            <?php  } else { ?>
                            0.00
                            <?php  } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>已开票，未收款</td>
                        <td>
                            <?php  if(!empty($my_merchant['reconciliation_sys_list']['all_money']['col_2'])) { ?>
                            <?php  if(is_array($my_merchant['reconciliation_sys_list']['all_money']['col_2'])) { foreach($my_merchant['reconciliation_sys_list']['all_money']['col_2'] as $k2 => $v2) { ?>
                            (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                            <?php  } } ?>
                            <?php  } else { ?>
                            0.00
                            <?php  } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>未开票，预收款</td>
                        <td>
                            <?php  if(!empty($my_merchant['reconciliation_sys_list']['all_money']['col_3'])) { ?>
                            <?php  if(is_array($my_merchant['reconciliation_sys_list']['all_money']['col_3'])) { foreach($my_merchant['reconciliation_sys_list']['all_money']['col_3'] as $k2 => $v2) { ?>
                            (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                            <?php  } } ?>
                            <?php  } else { ?>
                            0.00
                            <?php  } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>已收票，应付款</td>
                        <td>
                            <?php  if(!empty($my_merchant['reconciliation_sys_list']['all_money']['pay_1'])) { ?>
                            <?php  if(is_array($my_merchant['reconciliation_sys_list']['all_money']['pay_1'])) { foreach($my_merchant['reconciliation_sys_list']['all_money']['pay_1'] as $k2 => $v2) { ?>
                            (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                            <?php  } } ?>
                            <?php  } else { ?>
                            0.00
                            <?php  } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>已收票，未付款</td>
                        <td>
                            <?php  if(!empty($my_merchant['reconciliation_sys_list']['all_money']['pay_2'])) { ?>
                            <?php  if(is_array($my_merchant['reconciliation_sys_list']['all_money']['pay_2'])) { foreach($my_merchant['reconciliation_sys_list']['all_money']['pay_2'] as $k2 => $v2) { ?>
                            (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                            <?php  } } ?>
                            <?php  } else { ?>
                            0.00
                            <?php  } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>未收票，预付款</td>
                        <td>
                            <?php  if(!empty($my_merchant['reconciliation_sys_list']['all_money']['pay_3'])) { ?>
                            <?php  if(is_array($my_merchant['reconciliation_sys_list']['all_money']['pay_3'])) { foreach($my_merchant['reconciliation_sys_list']['all_money']['pay_3'] as $k2 => $v2) { ?>
                            (<?php  echo $k2;?>)<?php  echo sprintf('%.2f',$v2);?><br>
                            <?php  } } ?>
                            <?php  } else { ?>
                            0.00
                            <?php  } ?>
                        </td>
                    </tr>
                </table>
                <?php  } ?>
            </div>

            <?php  if($my_merchant['reconciliation_status2']!=1) { ?>
                <?php  if($is_show_queren==1) { ?>
                    <!--登记端-->
                    <div class="disf">
                        <?php  if($my_merchant['reconciliation_status2']==0) { ?>
                        <div class="see" style="background:#17bf76;" onclick="operation(<?php  echo $my_merchant['id'];?>,1,1);">对账无误</div>
                        <div class="see" style="background:#ff5555;" onclick="operation(<?php  echo $my_merchant['id'];?>,2,1);">对账有误</div>
                        <?php  } ?>
                        <!--<div class="see" onclick="watch_voucher(<?php  echo $my_merchant['id'];?>);">查看凭证</div>-->
                    </div>
                <?php  } else { ?>
                    <!--管理端-->
                    <div class="disf">
                        <?php  if($my_merchant['reconciliation_manage_status']==0) { ?>
                            <div class="see" style="background:#17bf76;" onclick="operation(<?php  echo $my_merchant['id'];?>,1,2);">对账无误</div>
                            <div class="see" style="background:#ff5555;" onclick="operation(<?php  echo $my_merchant['id'];?>,2,2);">对账有误</div>
                        <?php  } ?>
                        <?php  if($my_merchant['reconciliation_status2']==2 && $my_merchant['reconciliation_manage_status']!=2) { ?>
                            <div class="see" style="background:#ff5555;" onclick="operation(<?php  echo $my_merchant['id'];?>,2,2);">对账有误</div>
                        <?php  } ?>
                        <!--<div class="see" onclick="watch_voucher(<?php  echo $my_merchant['id'];?>);">查看凭证</div>-->
                    </div>

                <?php  } ?>
            <?php  } ?>
        <?php  } ?>
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

    function opera_queren(t,id){
        let selected = $(t).val();
        if(selected==1){
            //对账无误
            let m = $(t).children(':selected').data('m');
            operation(id,1,m);
        }else if(selected==2){
            //对账有误
            let m = $(t).children(':selected').data('m');
            operation(id,2,m);
        }else if(selected==3){
            //查看凭证
            watch_voucher(id);
        }
    }

    function watch_voucher(id){
        window.location.href="./index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=watch_voucher&id="+id;
    }
    
    function operation(id,typ,manager){
        if(typ==1){
            //正确无误
            if(confirm('确定对账无误？')){
                $.ajax({
                    url:"<?php  echo $this->createMobileUrl('account/register');?>",
                    type:'POST',
                    dataType:'json',
                    data:{'op':'reconciliation_info','typ':typ,'recon_id':id,'manager':manager},
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
            //对账有误
            // var height = 300+document.body.scrollTop;
            // $('.sign_box').css('top',height+'px').show();
            // $('.mask').show();
            // $('#batch_id').val(id);
            if(confirm('确定对账有误？')){
                $.ajax({
                    url:"<?php  echo $this->createMobileUrl('account/register');?>",
                    type:'POST',
                    dataType:'json',
                    data:{'op':'reconciliation_info','typ':typ,'recon_id':id,'manager':manager},
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
    }
    
</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>