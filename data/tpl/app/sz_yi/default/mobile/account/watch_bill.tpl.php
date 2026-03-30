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

    .table{width:100%;font-size:15px;}
    .table tr{height:25px;}
    .table tr td{vertical-align: middle !important;}

    .see{box-sizing: border-box;padding: 4px 10px;font-size: 14px;color: #fff;background: #1E9FFF;text-align:center;}

    .sign_box{display:none;width: 90%;position: absolute;background: #ffffff;border-radius: 5px;z-index:1000;transform:translate(-50%,-50%);top: 50%;left:50%;height:400px;}
    .sign_box .confirm{height: 50px;border-top: 1px solid #eee;display: flex;}
    .sign_box .confirm>span{flex: 1;height: 50px;line-height: 50px;font-size: 16px;text-align: center;}
    .sign_box .confirm>span:nth-child(1){color: red;}
    .sign_box .confirm>span:nth-child(2){border-left: 1px solid #eee;}
    .sign_box .important{color: red;}
    .sign_box .title{text-align: center;border-bottom: 1px solid #eee;margin-bottom: 10px;}
    .sign_box .title>p{height: 40px;line-height: 40px;text-align: center;font-size: 18px;font-weight: bold;}
    .sign_add {font-size:20px;width:40px;padding:0;line-height: 25px;}
    .sign_del {background:#ff2222;font-size:20px;width:40px;padding:0;display:none;line-height: 25px;}

    .page_head{width:100%;background:#fff;margin-bottom:5px;box-shadow:0 0 4px #ddd;padding:10px 0 10px;display:flex;align-items:center;}
    .page_head .left{width:20%;display:flex;align-items:center;font-size:15px;}
    .page_head .left .back{width:13px;height:13px;border-top:2px solid #000;border-left:2px solid #000;transform:rotate(-50deg);margin-left:15px;margin-right:5px;}
    .page_head .right{width:80%;text-align:center;padding-right:80px;font-size:15px;padding-top:2px;}
    a{text-decoration: none;text-underline: none;}
</style>

<link type="text/css" rel="stylesheet" href="../addons/sz_yi/template/pc/default/static/css/bootstrap.min.css" />
<link type="text/css" rel="stylesheet" href="../addons/sz_yi/static/travel_express/css/hui.css" />
<!--<script type="text/javascript" src="../addons/sz_yi/template/mobile/default/enterprise/static/js/hui.js" charset="UTF-8"></script>-->

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
    <div class="page_topbar">
        <div class="title">商户[<?php  echo $totalPrice['user_name'];?>]，对账年月[<?php  echo $totalPrice['reconciliation_date'];?>]</div>
    </div>

    <div class="info_main table-responsive main_list">
        <table class="table table-striped table-bordered" style="width:100%;margin-bottom:0;table-layout:fixed;word-break: break-all;">
            <tbody>
            <tr>
                <td>已开票，应收款</td>
                <td style="word-wrap: break-word;overflow: hidden;word-break: break-all;">
                    <?php  if(!empty($totalPrice['reconciliation_list'])) { ?>
                        <?php  if(is_array($totalPrice['reconciliation_list']['0'])) { foreach($totalPrice['reconciliation_list']['0'] as $k => $val) { ?>
                            <?php  if(!empty($val)) { ?>
                                <?php  echo $val;?><br>
                            <?php  } ?>
                        <?php  } } ?>
                        <?php  if(empty($totalPrice['reconciliation_list']['0']['0'])) { ?>
                        0.00
                        <?php  } ?>
                    <?php  } else { ?>
                        <?php  if(!empty($totalPrice['reconciliation_sys_list']['all_money']['col_1'])) { ?>
                            <?php  if(is_array($totalPrice['reconciliation_sys_list']['all_money']['col_1'])) { foreach($totalPrice['reconciliation_sys_list']['all_money']['col_1'] as $k => $val) { ?>
                                (<?php  echo $k;?>)<?php  echo $val;?><br>
                            <?php  } } ?>
                        <?php  } else { ?>
                            0.00
                        <?php  } ?>
                    <?php  } ?>
                </td>
            </tr>
            <tr>
                <td>已开票，未收款</td>
                <td style="word-wrap: break-word;overflow: hidden;word-break: break-all;">
                    <?php  if(!empty($totalPrice['reconciliation_list'])) { ?>
                        <?php  if(is_array($totalPrice['reconciliation_list']['1'])) { foreach($totalPrice['reconciliation_list']['1'] as $k => $val) { ?>
                        <?php  if(!empty($val)) { ?>
                            <?php  echo $val;?><br>
                        <?php  } ?>
                        <?php  } } ?>
                        <?php  if(empty($totalPrice['reconciliation_list']['1']['0'])) { ?>
                        0.00
                        <?php  } ?>
                    <?php  } else { ?>
                        <?php  if(!empty($totalPrice['reconciliation_sys_list']['all_money']['col_2'])) { ?>
                            <?php  if(is_array($totalPrice['reconciliation_sys_list']['all_money']['col_2'])) { foreach($totalPrice['reconciliation_sys_list']['all_money']['col_2'] as $k => $val) { ?>
                                (<?php  echo $k;?>)<?php  echo $val;?><br>
                            <?php  } } ?>
                        <?php  } else { ?>
                            0.00
                        <?php  } ?>
                    <?php  } ?>
                </td>
            </tr>
            <tr>
                <td>未开票，预收款</td>
                <td style="word-wrap: break-word;overflow: hidden;word-break: break-all;">
                    <?php  if(!empty($totalPrice['reconciliation_list'])) { ?>
                        <?php  if(is_array($totalPrice['reconciliation_list']['2'])) { foreach($totalPrice['reconciliation_list']['2'] as $k => $val) { ?>
                            <?php  if(!empty($val)) { ?>
                                <?php  echo $val;?><br>
                            <?php  } ?>
                        <?php  } } ?>
                        <?php  if(empty($totalPrice['reconciliation_list']['2']['0'])) { ?>
                        0.00
                        <?php  } ?>
                    <?php  } else { ?>
                    <?php  if(!empty($totalPrice['reconciliation_sys_list']['all_money']['col_3'])) { ?>
                    <?php  if(is_array($totalPrice['reconciliation_sys_list']['all_money']['col_3'])) { foreach($totalPrice['reconciliation_sys_list']['all_money']['col_3'] as $k => $val) { ?>
                    (<?php  echo $k;?>)<?php  echo $val;?><br>
                    <?php  } } ?>
                    <?php  } else { ?>
                    0.00
                    <?php  } ?>
                    <?php  } ?>
                </td>
            </tr>
            <tr>
                <td>已收票，应付款</td>
                <td style="word-wrap: break-word;overflow: hidden;word-break: break-all;">
                    <?php  if(!empty($totalPrice['reconciliation_list'])) { ?>
                        <?php  if(is_array($totalPrice['reconciliation_list']['3'])) { foreach($totalPrice['reconciliation_list']['3'] as $k => $val) { ?>
                            <?php  if(!empty($val)) { ?>
                                <?php  echo $val;?><br>
                            <?php  } ?>
                        <?php  } } ?>
                        <?php  if(empty($totalPrice['reconciliation_list']['3']['0'])) { ?>
                        0.00
                        <?php  } ?>
                    <?php  } else { ?>
                    <?php  if(!empty($totalPrice['reconciliation_sys_list']['all_money']['pay_1'])) { ?>
                    <?php  if(is_array($totalPrice['reconciliation_sys_list']['all_money']['pay_1'])) { foreach($totalPrice['reconciliation_sys_list']['all_money']['pay_1'] as $k => $val) { ?>
                    (<?php  echo $k;?>)<?php  echo $val;?><br>
                    <?php  } } ?>
                    <?php  } else { ?>
                    0.00
                    <?php  } ?>
                    <?php  } ?>
                </td>
            </tr>
            <tr>
                <td>已收票，未付款</td>
                <td style="word-wrap: break-word;overflow: hidden;word-break: break-all;">
                    <?php  if(!empty($totalPrice['reconciliation_list'])) { ?>
                        <?php  if(is_array($totalPrice['reconciliation_list']['4'])) { foreach($totalPrice['reconciliation_list']['4'] as $k => $val) { ?>
                            <?php  if(!empty($val)) { ?>
                                <?php  echo $val;?><br>
                            <?php  } ?>
                        <?php  } } ?>
                        <?php  if(empty($totalPrice['reconciliation_list']['4']['0'])) { ?>
                        0.00
                        <?php  } ?>
                    <?php  } else { ?>
                    <?php  if(!empty($totalPrice['reconciliation_sys_list']['all_money']['pay_2'])) { ?>
                    <?php  if(is_array($totalPrice['reconciliation_sys_list']['all_money']['pay_2'])) { foreach($totalPrice['reconciliation_sys_list']['all_money']['pay_2'] as $k => $val) { ?>
                    (<?php  echo $k;?>)<?php  echo $val;?><br>
                    <?php  } } ?>
                    <?php  } else { ?>
                    0.00
                    <?php  } ?>
                    <?php  } ?>
                </td>
            </tr>
            <tr>
                <td>未收票，预付款</td>
                <td style="word-wrap: break-word;overflow: hidden;word-break: break-all;">
                    <?php  if(!empty($totalPrice['reconciliation_list'])) { ?>
                    <?php  if(is_array($totalPrice['reconciliation_list']['5'])) { foreach($totalPrice['reconciliation_list']['5'] as $k => $val) { ?>
                        <?php  if(!empty($val)) { ?>
                            <?php  echo $val;?><br>
                        <?php  } ?>
                    <?php  } } ?>
                    <?php  if(empty($totalPrice['reconciliation_list']['5']['0'])) { ?>
                    0.00
                    <?php  } ?>
                    <?php  } else { ?>
                    <?php  if(!empty($totalPrice['reconciliation_sys_list']['all_money']['pay_3'])) { ?>
                    <?php  if(is_array($totalPrice['reconciliation_sys_list']['all_money']['pay_3'])) { foreach($totalPrice['reconciliation_sys_list']['all_money']['pay_3'] as $k => $val) { ?>
                    (<?php  echo $k;?>)<?php  echo $val;?><br>
                    <?php  } } ?>
                    <?php  } else { ?>
                    0.00
                    <?php  } ?>
                    <?php  } ?>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <?php  if($totalPrice['reconciliation_status2']!=1 && $totalPrice['reconciliation_status']==0) { ?>
        <?php  if($opera==1) { ?>
            <!--管理员-->
            <div style="display:flex;align-items: center;justify-content: center;margin-top:10px;">
                <div class="see" onclick="manage_operation('<?php  echo $totalPrice['id'];?>',1)">对账无误</div>
                <div class="see" onclick="manage_operation('<?php  echo $totalPrice['id'];?>',2)" style="background:#ff5555;margin-left:15px;">对账有误</div>
            </div>
        <?php  } else { ?>
            <!--记账代理-->
            <div style="display:flex;align-items: center;justify-content: center;margin-top:10px;">
                <div class="see" onclick="operation('<?php  echo $totalPrice['id'];?>',3)">对账无误</div>
                <div class="see" onclick="operation('<?php  echo $totalPrice['id'];?>',4)" style="background:#ff5555;margin-left:15px;">对账有误</div>
            </div>
        <?php  } ?>
    <?php  } ?>
</div>
<!--对账有误-->
<div class="mask" style="position: fixed; margin: 0px; padding: 0px;background: rgba(0, 0, 0,0.6); z-index: 999; width: 100%; height: 100%; transition: all 0.2s ease 0s;display:none;left: 0;top: 0;"></div>
<div class="sign_box">
    <div class="title">
        <p class="important">输入正确金额</p>
    </div>
    <input type="text" name="batch_id" id="batch_id" value="" style="display:none;"/>

    <div class="sign_content" style="height: 72%;overflow: scroll;">
            <table class="table table-striped table-bordered" style="width:100%;margin-bottom:10px;table-layout:fixed;word-break: break-all;">
                <tr>
                    <td colspan="3" style="text-align:center;">应收款</td>
                </tr>
                <tr>
                    <td>币种</td>
                    <td>金额</td>
                    <td>操作</td>
                </tr>
                <tr>
                    <td>
                        <select name="col_1_currency" id="col_1_currency" style="width: 100%;">
                            <?php  if(is_array($currency)) { foreach($currency as $val) { ?>
                            <option value="<?php  echo $val['code_name'];?>" <?php echo  $val['code_value']==142?'selected':'';?>><?php  echo $val['code_name'];?></option>
                            <?php  } } ?>
                        </select>
                    </td>
                    <td>
                        <input type="number" name="col_1_money" id="col_1_money" placeholder="请输入金额" style="width: 100%;border:0;">
                    </td>
                    <td style="display:flex;align-items: center;justify-content:space-evenly;">
                        <div class="see sign_add" onclick="add(this)" >+</div>
                        <div class="see sign_del" onclick="refuse(this)">-</div>
                    </td>
                </tr>
            </table>

            <table class="table table-striped table-bordered" style="width:100%;margin-bottom:10px;table-layout:fixed;word-break: break-all;">
                <tr>
                    <td colspan="3" style="text-align:center;">未收款</td>
                </tr>
                <tr>
                    <td>币种</td>
                    <td>金额</td>
                    <td>操作</td>
                </tr>
                <tr>
                    <td>
                        <select name="col_2_currency" id="col_2_currency" style="width: 100%;">
                            <?php  if(is_array($currency)) { foreach($currency as $val) { ?>
                            <option value="<?php  echo $val['code_name'];?>" <?php echo  $val['code_value']==142?'selected':'';?>><?php  echo $val['code_name'];?></option>
                            <?php  } } ?>
                        </select>
                    </td>
                    <td>
                        <input type="number" name="col_2_money" id="col_2_money" placeholder="请输入金额" style="width: 100%;border:0;">
                    </td>
                    <td style="display:flex;align-items: center;justify-content:space-evenly;">
                        <div class="see sign_add" onclick="add(this)">+</div>
                        <div class="see sign_del" onclick="refuse(this)">-</div>
                    </td>
                </tr>
            </table>

            <table class="table table-striped table-bordered" style="width:100%;margin-bottom:10px;table-layout:fixed;word-break: break-all;">
                <tr>
                    <td colspan="3" style="text-align:center;">预收款</td>
                </tr>
                <tr>
                    <td>币种</td>
                    <td>金额</td>
                    <td>操作</td>
                </tr>
                <tr>
                    <td>
                        <select name="col_3_currency" id="col_3_currency" style="width: 100%;">
                            <?php  if(is_array($currency)) { foreach($currency as $val) { ?>
                            <option value="<?php  echo $val['code_name'];?>" <?php echo  $val['code_value']==142?'selected':'';?>><?php  echo $val['code_name'];?></option>
                            <?php  } } ?>
                        </select>
                    </td>
                    <td>
                        <input type="number" name="col_3_money" id="col_3_money" placeholder="请输入金额" style="width: 100%;border:0;">
                    </td>
                    <td style="display:flex;align-items: center;justify-content:space-evenly;">
                        <div class="see sign_add" onclick="add(this)">+</div>
                        <div class="see sign_del" onclick="refuse(this)">-</div>
                    </td>
                </tr>
            </table>

            <table class="table table-striped table-bordered" style="width:100%;margin-bottom:10px;table-layout:fixed;word-break: break-all;">
                <tr>
                    <td colspan="3" style="text-align:center;">应付款</td>
                </tr>
                <tr>
                    <td>币种</td>
                    <td>金额</td>
                    <td>操作</td>
                </tr>
                <tr>
                    <td>
                        <select name="pay_1_currency" id="pay_1_currency" style="width: 100%;">
                            <?php  if(is_array($currency)) { foreach($currency as $val) { ?>
                            <option value="<?php  echo $val['code_name'];?>" <?php echo  $val['code_value']==142?'selected':'';?>><?php  echo $val['code_name'];?></option>
                            <?php  } } ?>
                        </select>
                    </td>
                    <td>
                        <input type="number" name="pay_1_money" id="pay_1_money" placeholder="请输入金额" style="width: 100%;border:0;">
                    </td>
                    <td style="display:flex;align-items: center;justify-content:space-evenly;">
                        <div class="see sign_add" onclick="add(this)">+</div>
                        <div class="see sign_del" onclick="refuse(this)">-</div>
                    </td>
                </tr>
            </table>

            <table class="table table-striped table-bordered" style="width:100%;margin-bottom:10px;table-layout:fixed;word-break: break-all;">
                <tr>
                    <td colspan="3" style="text-align:center;">未付款</td>
                </tr>
                <tr>
                    <td>币种</td>
                    <td>金额</td>
                    <td>操作</td>
                </tr>
                <tr>
                    <td>
                        <select name="pay_2_currency" id="pay_2_currency" style="width: 100%;">
                            <?php  if(is_array($currency)) { foreach($currency as $val) { ?>
                            <option value="<?php  echo $val['code_name'];?>" <?php echo  $val['code_value']==142?'selected':'';?>><?php  echo $val['code_name'];?></option>
                            <?php  } } ?>
                        </select>
                    </td>
                    <td>
                        <input type="number" name="pay_2_money" id="pay_2_money" placeholder="请输入金额" style="width: 100%;border:0;">
                    </td>
                    <td style="display:flex;align-items: center;justify-content:space-evenly;">
                        <div class="see sign_add" onclick="add(this)">+</div>
                        <div class="see sign_del" onclick="refuse(this)">-</div>
                    </td>
                </tr>
            </table>

            <table class="table table-striped table-bordered" style="width:100%;margin-bottom:10px;table-layout:fixed;word-break: break-all;">
                <tr>
                    <td colspan="3" style="text-align:center;">预付款</td>
                </tr>
                <tr>
                    <td>币种</td>
                    <td>金额</td>
                    <td>操作</td>
                </tr>
                <tr>
                    <td>
                        <select name="pay_3_currency" id="pay_3_currency" style="width: 100%;">
                            <?php  if(is_array($currency)) { foreach($currency as $val) { ?>
                            <option value="<?php  echo $val['code_name'];?>" <?php echo  $val['code_value']==142?'selected':'';?>><?php  echo $val['code_name'];?></option>
                            <?php  } } ?>
                        </select>
                    </td>
                    <td>
                        <input type="number" name="pay_3_money" id="pay_3_money" placeholder="请输入金额" style="width: 100%;border:0;">
                    </td>
                    <td style="display:flex;align-items: center;justify-content:space-evenly;">
                        <div class="see sign_add" onclick="add(this)">+</div>
                        <div class="see sign_del" onclick="refuse(this)">-</div>
                    </td>
                </tr>
            </table>
        </div>

    <div class="confirm">
        <span class="close-but" onClick="fnClose(1)">取消</span>
        <span class="close-but" onClick="fnClose(2)">确认</span>
    </div>
</div>
<script language="javascript">
    require(['tpl', 'core'], function(tpl, core) {
        $('.page_head').find('.left').click(function(){
            window.history.back(-1);
        });
    });

    function manage_operation(id,typ,manager='2'){
        if(typ==1){
            //正确无误
            if(confirm('确定对账无误？')){
                $.ajax({
                    url:"<?php  echo $this->createMobileUrl('account/register');?>",
                    type:'POST',
                    dataType:'json',
                    data:{'op':'reconciliation_info','typ':typ,'batch_id':id,'manager':manager},
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
            if(confirm('确定对账有误？')){
                $.ajax({
                    url:"<?php  echo $this->createMobileUrl('account/register');?>",
                    type:'POST',
                    dataType:'json',
                    data:{'op':'reconciliation_info','typ':typ,'batch_id':id,'manager':manager},
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

    function operation(id,typ){
        if(typ==3){
            //对账无误
            if(confirm('确认对账无误？')){
                $.ajax({
                    url:"<?php  echo $this->createMobileUrl('account/bookkeeping');?>",
                    type:'POST',
                    dataType:'json',
                    data:{'op':'reconciliation_detail','typ':typ,'batch_ids':"<?php  echo $totalPrice['batch_ids'];?>","true_date":"<?php  echo $totalPrice['reconciliation_date'];?>","openid":"<?php  echo $totalPrice['openid'];?>","recon_id":"<?php  echo $totalPrice['id'];?>"},
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
        }else if(typ==4){
            //对账有误
            var height = 300+document.body.scrollTop;
            $('.sign_box').css('top',height+'px').show();
            $('.mask').show();
            $('#batch_id').val("<?php  echo $totalPrice['batch_ids'];?>");
        }
    }

    //取消选择物流信息
    function fnClose(typ){
        // hui.upToast(msg);

        if(typ==1){
            $('.sign_box').hide();
            $('.mask').hide();
            $('#batch_id').val('');

        }else if(typ==2){
            let batch_id = $('#batch_id').val();//凭证批次号id

            let col_1_currency = '';
            $('select[name="col_1_currency"]').each(function(){
                if($(this).find('option:selected').val()!=''){
                    col_1_currency += $(this).val()+',';
                }
            });
            let col_2_currency = '';
            $('select[name="col_2_currency"]').each(function(){
                if($(this).find('option:selected').val()!=''){
                    col_2_currency += $(this).val()+',';
                }
            });
            let col_3_currency = '';
            $('select[name="col_3_currency"]').each(function(){
                if($(this).find('option:selected').val()!=''){
                    col_3_currency += $(this).val()+',';
                }
            });
            let pay_1_currency = '';
            $('select[name="pay_1_currency"]').each(function(){
                if($(this).find('option:selected').val()!=''){
                    pay_1_currency += $(this).val()+',';
                }
            });
            let pay_2_currency = '';
            $('select[name="pay_2_currency"]').each(function(){
                if($(this).find('option:selected').val()!=''){
                    pay_2_currency += $(this).val()+',';
                }
            });
            let pay_3_currency = '';
            $('select[name="pay_3_currency"]').each(function(){
                if($(this).find('option:selected').val()!=''){
                    pay_3_currency += $(this).val()+',';
                }
            });

            let col_1_money = '';
            $('input[name="col_1_money"]').each(function(){
                if($(this).find('option:selected').val()!=''){
                    col_1_money += $(this).val()+',';
                }
            });
            let col_2_money = '';
            $('input[name="col_2_money"]').each(function(){
                if($(this).find('option:selected').val()!=''){
                    col_2_money += $(this).val()+',';
                }
            });
            let col_3_money = '';
            $('input[name="col_3_money"]').each(function(){
                if($(this).find('option:selected').val()!=''){
                    col_3_money += $(this).val()+',';
                }
            });
            let pay_1_money = '';
            $('input[name="pay_1_money"]').each(function(){
                if($(this).find('option:selected').val()!=''){
                    pay_1_money += $(this).val()+',';
                }
            });
            let pay_2_money = '';
            $('input[name="pay_2_money"]').each(function(){
                if($(this).find('option:selected').val()!=''){
                    pay_2_money += $(this).val()+',';
                }
            });
            let pay_3_money = '';
            $('input[name="pay_3_money"]').each(function(){
                if($(this).find('option:selected').val()!=''){
                    pay_3_money += $(this).val()+',';
                }
            });

            $.ajax({
                url:"<?php  echo $this->createMobileUrl('account/bookkeeping');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'reconciliation_detail','typ':4,'col_1_currency':col_1_currency,'col_2_currency':col_2_currency,'col_3_currency':col_3_currency,'pay_1_currency':pay_1_currency,'pay_2_currency':pay_2_currency,'pay_3_currency':pay_3_currency,'col_1_money':col_1_money,'col_2_money':col_2_money,'col_3_money':col_3_money,'pay_1_money':pay_1_money,'pay_2_money':pay_2_money,'pay_3_money':pay_3_money,'batch_ids':"<?php  echo $totalPrice['batch_ids'];?>","true_date":"<?php  echo $totalPrice['reconciliation_date'];?>","openid":"<?php  echo $totalPrice['openid'];?>","recon_id":"<?php  echo $totalPrice['id'];?>"},
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

    //正确金额-增加
    function add(t){
        let new_html = $(t).parent().parent().html();
        $(t).parent().parent().after('<tr>'+new_html+'</tr>');
        $(t).parent().parent().next().find('.sign_del').css('display','block');
    }

    //正确金额-减少
    function refuse(t){
        $(t).parent().parent().remove();
    }
</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>