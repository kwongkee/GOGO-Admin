<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<!--<title>自定义收款</title>-->
<style type="text/css">
    body {margin:0px; background:#efefef; font-family:'微软雅黑'; -moz-appearance:none;}
    .info_main {height:auto;  background:#fff; margin-top:14px; border-bottom:1px solid #e8e8e8; border-top:1px solid #e8e8e8;}
    .info_main .line {margin:0 10px; height:40px; border-bottom:1px solid #e8e8e8; line-height:40px; color:#999;font-size:15px;}
    .info_main .line .title {height:40px; width:80px; line-height:40px; color:#444; float:left; font-size:15px;}
    .info_main .line .info { width:100%;float:right;margin-left:-80px; overflow: auto;white-space: nowrap;}
    .info_main .line .inner { margin-left:80px; }
    .info_main .line .inner input {height:39px; width:100%;display:block; padding:0px; margin:0px; border:0px; float:left; font-size:14px;}
    .info_main .line .inner .user_sex {line-height:40px;}
    .info_sub {height:44px; margin:14px 5px; background:#1E9FFF !important; border-radius:4px; text-align:center; font-size:16px; line-height:44px; color:#fff;}
    .select { border:1px solid #ccc;height:25px;}
    .disf{display:flex;}
    /**合同文件**/
    .info_main .images {float: left; width:auto;height:30px;margin-top:7px;}
    .info_main .images .img { float:left; position:relative;width:30px;height:30px;border:1px solid #e9e9e9;margin-right:5px;}
    .info_main .images .img img { position:absolute;top:0; width:100%;height:100%;}
    .info_main .images .img .minus { position:absolute;color:red;width:8px;height:12px;top:-18px;right:-1px;}
    .info_main .plus { float:left; width:30px;height:30px;border:1px solid #e9e9e9; color:#dedede;; font-size:18px;line-height:30px;text-align:center;margin-top:4px;}
    .info_main .plus i { left:7px;top:7px;}

    /**营业费用、成本、税金弹框**/
    .buss_cost_box,.buss_fee_box,.buss_taxes_box,.buss_loss_box{display:none;width: 80%;position: absolute;background: #ffffff;border-radius: 5px;z-index:1000;transform:translate(-50%,-50%);top: 50%;left:50%;}
    .confirm{height: 50px;border-top: 1px solid #eee;display: flex;}
    .confirm>span{flex: 1;height: 50px;line-height: 50px;font-size: 16px;text-align: center;}
    .confirm>span:nth-child(1){color: red;}
    .confirm>span:nth-child(2){border-left: 1px solid #eee;}
    .buss_cost_box .title,.buss_fee_box .title,.buss_taxes_box .title,.buss_loss_box .title{text-align: center;border-bottom: 1px solid #eee;}
    .buss_cost_box .title>p,.buss_fee_box .title>p,.buss_taxes_box .title>p,.buss_loss_box .title>p{height: 40px;line-height: 40px;text-align: center;font-size: 18px;font-weight: bold;}
    .buss_cost_box .important,.buss_taxes_box .important,.buss_fee_box .important,.buss_loss_box .important{color: red;}
    .buss_cost_content,.buss_fee_content,.buss_taxes_content,.buss_loss_content{min-height:250px;height:250px;max-height:250px;overflow: scroll;}
    .service_btn{display:flex;align-items:center;justify-content: space-evenly;font-size:15px;margin-top:10px;margin-bottom:10px;}
    .service_btn .service_add,.buss_add{background:#1E9FFF;color:#fff;width:30px;font-size:30px;line-height: 0.85;text-align:center;margin-right:5px;}
    .service_btn .service_del,.buss_del{background:#ff5555;color:#fff;width:30px;font-size:30px;line-height: 0.85;text-align:center;}
    /**end**/

    .bigautocomplete-layout{background:#fff;position:absolute;height: 200px;overflow: scroll;border:1px solid #000;}
    .bigautocomplete-layout table tr td div{height: 30px;line-height: 30px;}
    button, input, optgroup, select, textarea{font:unset !important;color:black !important;line-height:1.5 !important;}
    .sel{box-sizing: border-box;padding: 4px 10px;font-size: 14px;color: #fff;background: #1E9FFF;text-align:center;width: fit-content;margin: 0 auto;}

    .table_title{width: 100%;background: #1E9FFF;color: #fff;font-size: 15px;padding: 5px;text-align: center;margin-top: 5px;}
    .cost_div,.fee_div,.taxes_div,.loss_div{display:none;}
    .buss_del_show{display:block;}
    .buss_del_hide{display:none;}
    a{text-decoration: none;text-underline: none;}
    .calc{color:#fff;background:#1E9FFF;font-size:15px;padding:4px 10px;box-sizing:border-box;line-height:normal;width:fit-content;margin-top: 5px;}
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

<!--<link rel="stylesheet" href="../addons/sz_yi/static/css/layui.css">-->
<script src="../addons/sz_yi/static/js/jquery.bigautocomplete.js"></script>
<link type="text/css" rel="stylesheet" href="../addons/sz_yi/template/pc/default/static/css/bootstrap.min.css" />

<div id="container">
    <div class="page_topbar">
        <div class="title">税费申报修改</div>
    </div>
    <div class="info_main">
        <!--税费申报-->
        <div class="tax_info">
            <div class="line"><div class="title" style="background:#fff;z-index:99;">商户信息</div><div class="info"><div class="inner" style="z-index:98;">
                <input type="text" style="display:none;" id="tax_id" value="<?php  echo $tax_info['id'];?>">
                <?php  echo $tax_info['user_name'];?>-[<?php  echo $tax_info['name'];?>]
            </div></div></div>
            <div class="line"><div class="title">申报时间</div><div class='info'><div class='inner'>
                <input type="text" id="decl_date" value="<?php  echo $tax_info['decl_date'];?>" placeholder="申报时间" readonly="readonly"  style="height:38px;">
            </div></div></div>

            <div class="table_title">营业收入</div>
            <table class="table table-striped" style="margin-bottom:0;table-layout:fixed;word-break: break-all;">
                <tr>
                    <td>收入类别</td>
                    <td>项目</td>
                    <td>币种</td>
                    <td>金额</td>
                    <td>操作</td>
                </tr>
                <?php  if(is_array($income)) { foreach($income as $k => $val) { ?>
                <tr>
                    <td>
                        <select name="buss_type" id="buss_type" style="width:100%;">
                            <option value="主要业务收入" <?php echo $val['name']=='主要业务收入'?'selected':''?>>主要业务收入</option>
                            <option value="其他业务收入" <?php echo $val['name']=='其他业务收入'?'selected':''?>>其他业务收入</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" name="buss_project" id='buss_project' placeholder="" value="<?php  echo $val['project'];?>" style="width:100%;border: 1px solid #666;" />
                    </td>
                    <td>
                        <select name="currency" id="currency" style="width:100%;margin-right:5px;">
                            <option value="">请选择营业收入币种</option>
                            <?php  if(is_array($currency)) { foreach($currency as $v) { ?>
                            <option value="<?php  echo $v['code_value'];?>" <?php echo $v['code_value']==$val['currency']?'selected':''?>><?php  echo $v['code_name'];?></option>
                            <?php  } } ?>
                        </select>
                    </td>
                    <td>
                        <input type="text" name="income_price" id='income_price' value="<?php  echo $val['price'];?>" style="width:100%;border: 1px solid #666;
}"/>
                    </td>
                    <td>
                        <div style="display:flex;align-items: center;justify-content: space-between;">
                            <div class="buss_add"  onclick="buss_add(this);">+</div>
                            <div class="buss_del <?php echo $k!=0?'buss_del_show':'buss_del_hide'?>" onclick="buss_del(this);">-</div>
                        </div>
                    </td>
                </tr>
                <?php  } } ?>
            </table>

            <div class="table_title">营运支出</div>
            <table class="table table-striped" style="margin-bottom:0;table-layout:fixed;word-break: break-all;">
                    <tr>
                        <td>支出类别</td>
                        <td>项目</td>
                        <td>币种</td>
                        <td>金额</td>
                        <td>操作</td>
                    </tr>
                    <?php  if(!empty($cost)) { ?>
                        <?php  if(is_array($cost)) { foreach($cost as $k => $val) { ?>
                        <tr>
                            <td>
                                <select name="expend_cate" id="expend_cate" style="width:100%;" onchange="expend_cate(this)">
                                    <option value="1" <?php echo $val['name']=='1'?'selected':''?>>成本</option>
                                    <option value="2" <?php echo $val['name']=='2'?'selected':''?>>费用</option>
                                    <option value="3" <?php echo $val['name']=='3'?'selected':''?>>损失</option>
                                </select>
                            </td>
                            <td>
                                <select name="cost_name" id="cost_name" style="width:100%;">
                                    <?php  if($val['name']==1) { ?>
                                        <option value="销售成本" <?php echo $val['project']=='销售成本'?'selected':''?>>销售成本</option>
                                        <option value="销货成本" <?php echo $val['project']=='销货成本'?'selected':''?>>销货成本</option>
                                        <option value="业务支出" <?php echo $val['project']=='业务支出'?'selected':''?>>业务支出</option>
                                        <option value="其他耗费" <?php echo $val['project']=='其他耗费'?'selected':''?>>其他耗费</option>
                                    <?php  } ?>
                                    <?php  if($val['name']==2) { ?>
                                        <option value="销售费用" <?php echo $val['project']=='销售费用'?'selected':''?>>销售费用</option>
                                        <option value="管理费用" <?php echo $val['project']=='管理费用'?'selected':''?>>管理费用</option>
                                        <option value="财务费用" <?php echo $val['project']=='财务费用'?'selected':''?>>财务费用</option>
                                    <?php  } ?>
                                    <?php  if($val['name']==3) { ?>
                                        <option value="资产与存货盘亏" <?php echo $val['project']=='资产与存货盘亏'?'selected':''?>>资产与存货盘亏</option>
                                        <option value="转让财产损失" <?php echo $val['project']=='转让财产损失'?'selected':''?>>转让财产损失</option>
                                        <option value="呆账损失" <?php echo $val['project']=='呆账损失'?'selected':''?>>呆账损失</option>
                                        <option value="不可抗力损失" <?php echo $val['project']=='不可抗力损失'?'selected':''?>>不可抗力损失</option>
                                        <option value="其他损失" <?php echo $val['project']=='其他损失'?'selected':''?>>其他损失</option>
                                    <?php  } ?>
                                </select>
                            </td>
                            <td>
                                <select name="currency2" id="currency2" style="width:100%;">
                                    <option value="">请选择成本币种</option>
                                    <?php  if(is_array($currency)) { foreach($currency as $v) { ?>
                                    <option value="<?php  echo $v['code_value'];?>" <?php echo $v['code_value']==$val['currency']?'selected':''?>><?php  echo $v['code_name'];?></option>
                                    <?php  } } ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="cost_price" id='cost_price' value="<?php  echo $val['price'];?>" style="width:100%;border: 1px solid #666;
    }" />
                            </td>
                            <td>
                                <div style="display:flex;align-items: center;justify-content: space-between;">
                            <div class="buss_add"  onclick="buss_add2(this);">+</div>
                            <div class="buss_del <?php echo $k>=1?'buss_del_show':'buss_del_hide'?>" onclick="buss_del2(this);">-</div>
                        </div>
                            </td>
                        </tr>
                        <?php  } } ?>
                    <?php  } ?>
                </table>

            
            <div class="table_title">本期税费</div>
            <table class="table table-striped" style="margin-bottom:0;table-layout:fixed;word-break: break-all;">
                    <tr>
                        <td>申报税种</td>
                        <td>项目</td>
                        <td>币种</td>
                        <td>金额</td>
                        <td>操作</td>
                    </tr>
                    <?php  if(!empty($taxes)) { ?>
                        <?php  if(is_array($taxes)) { foreach($taxes as $k => $val) { ?>
                        <tr>
                            <td>
                                <select name="taxes_id" id="taxes_id" style="width:100%;"  onchange="taxes_change(this,2)">
                                    <?php  if(is_array($taxes_cate)) { foreach($taxes_cate as $v) { ?>
                                    <option value="<?php  echo $v['id'];?>" <?php echo $v['id']==$val['name']?'selected':''?>><?php  echo $v['name'];?></option>
                                    <?php  } } ?>
                                </select>
                            </td>
                            <td>
                                <?php  if(is_array($taxes_cate)) { foreach($taxes_cate as $k2 => $v) { ?>
                                <?php  if($val['name']==$v['id']) { ?>
                                <select name="taxes_info_id" id="taxes_info_id" style="width:100%;">
                                    <?php  if(is_array($taxes_cate_info[$k])) { foreach($taxes_cate_info[$k] as $v2) { ?>
                                    <option value="<?php  echo $v2['id'];?>" <?php echo $v2['id']==$val['project']?'selected':''?>><?php  echo $v2['name'];?></option>
                                    <?php  } } ?>
                                </select>
                                <?php  } ?>
                                <?php  } } ?>
                            </td>
                            
                            <td>
                                <select name="currency4" id="currency4" style="width:100%;">
                                    <option value="">请选择费用币种</option>
                                    <?php  if(is_array($currency)) { foreach($currency as $v) { ?>
                                    <option value="<?php  echo $v['code_value'];?>" <?php echo $v['code_value']==$val['currency']?'selected':''?>><?php  echo $v['code_name'];?></option>
                                    <?php  } } ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="taxes_price" id='taxes_price' value="<?php  echo $val['price'];?>" style="width:100%;border: 1px solid #666;
    }" />
                            </td>
                            <td>
                                <div style="display:flex;align-items: center;justify-content: space-between;">
                                    <div class="buss_add" onclick="buss_add(this,2);">+</div>
                                    <div class="buss_del <?php echo $k>=1?'buss_del_show':'buss_del_hide'?>" onclick="buss_del(this);">-</div>
                                </div>
                            </td>
                        </tr>
                        <?php  } } ?>
                    <?php  } ?>
                </table>

            <div class="line" style="margin-top:5px;"><div class="title">本期利润</div><div class='info'><div class='inner'>
                <div class="calc">立即计算</div>
            </div></div></div>
            <div class="current_profit_div">
                <table class="table table-striped" style="margin-bottom:0;table-layout:fixed;word-break: break-all;text-align:center;">
                    <tr>
                        <td style="width:50%;">币种</td>
                        <td style="width:50%;">金额</td>
                    </tr>
                <?php  if(is_array($profit)) { foreach($profit as $val) { ?>
                    <tr>
                        <td><?php  echo $val['currency'];?></td>
                        <td><?php  echo $val['money'];?></td>
                    </tr>
                <?php  } } ?>
                </table>
            </div>
            <input type="text" name="current_profit_value" id="current_profit_value" value="<?php  echo $tax_info['current_profit'];?>" style="display:none;">
        </div>

    </div>
    <div class="info_sub" style="margin-bottom:10px;">修改</div>
    <div class="button back" onclick="javascript:history.back(-1);" style="height: 44px;margin: 14px 2%;width:96%;background: #aaa;border-radius: 4px;text-align: center;font-size: 16px;line-height: 44px;color: #fff;">返回</div>
</div>

<script language="javascript">
    require(['tpl', 'core'], function(tpl, core) {
        //时间
        var currYear = (new Date()).getFullYear();
        var opt = {};
        opt.date = {preset: 'date'};
        opt.datetime = {preset: 'date'};
        opt.time = {preset: 'time'};
        opt.default = {
            theme: 'android-ics light', //皮肤样式
            display: 'modal', //显示方式
            mode: 'scroller', //日期选择模式
            dateFormat: 'yyyy-mm',
            dateOrder : 'yyyymm',
            lang: 'zh',
            showNow: true,
            nowText: "今天",
            startYear: currYear, //开始年份
            endYear: currYear //结束年份
        };
        // $("#decl_date").scroller('destroy').scroller($.extend(opt['datetime'], opt['default']));
        
        //选择公司（商户）
        $('#user_info').change(function(){
            var selected = $(this).children('option:selected').val();
            let company = $(this).children('option:selected').data('company');
            $('#company_name').val(company);
            
            //查询该商户的核定税种
            let id = $(this).children('option:selected').val();
            $.ajax({
                url:"<?php  echo $this->createMobileUrl('account/bookkeeping');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'tax_declare','user_id':id,'type':1},
                success:function(json) {
                    let dat = json.result.data;
                    let html = '<select name="taxes_id" id="taxes_id" style="width:100%;" onchange="taxes_change(this,2)">';
                    for(var i=0;i<dat.length;i++){
                        if(dat[i].id>0){
                            html += '<option value="'+dat[i].id+'">'+dat[i].name+'</option>'
                        }
                    }
                    html += '</select>';
                    $('.taxes_table').find('#taxes_id').parent().html(html);
                },error:function(json){
                    alert('数据出错！');
                }
            });
        });

        //支出类别
        $('#exprend_cate').change(function(){
            let selected = $(this).val();
            if(selected==1){
                //成本
                $(this).parent().parent().children().find('#cost_name').show();
                $(this).parent().parent().children().find('#fee_name').hide();
                $(this).parent().parent().children().find('#loss_name').hide();
            }else if(selected==2){
                //费用
                $(this).parent().parent().children().find('#cost_name').hide();
                $(this).parent().parent().children().find('#fee_name').show();
                $(this).parent().parent().children().find('#loss_name').hide();
            }else if(selected==3){
                //损失
                $(this).parent().parent().children().find('#cost_name').hide();
                $(this).parent().parent().children().find('#fee_name').hide();
                $(this).parent().parent().children().find('#loss_name').show();
            }
        });
        //计算利润
        $('.calc').click(function(){
            //营业收入
            let income_currency = '';
            $('select[name="currency"]').each(function(i){
                income_currency += $(this).val()+',';
            });
            let income_price = '';
            $('input[name="income_price"]').each(function() {
                if ($(this).val() != '') {
                    income_price += parseFloat($(this).val())+',';
                }
            });

            //营运支出
            let cost_currency = '';
            $('select[name="currency2"]').each(function(i){
                cost_currency += $(this).val()+',';
            });
            let cost_price = '';
            $('input[name="cost_price"]').each(function(){
                if($(this).val()!=''){
                    cost_price += parseFloat($(this).val())+',';
                }
            });

            //本期税费
            let taxes_currency = '';
            $('select[name="currency4"]').each(function(i){
                taxes_currency += $(this).val()+',';
            });
            let taxes_price = '';
            $('input[name="taxes_price"]').each(function(){
                if($(this).val()!=''){
                    taxes_price += parseFloat($(this).val())+',';
                }
            });

            // let current_profit = income_price - cost_price - taxes_price;
            // $('#current_profit').val(current_profit.toFixed(2));

            $.ajax({
                url:"<?php  echo $this->createMobileUrl('account/bookkeeping');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'calc_taxes_price','income_currency':income_currency,'income_price':income_price,'cost_currency':cost_currency,'cost_price':cost_price,'taxes_currency':taxes_currency,'taxes_price':taxes_price},
                success:function(json) {
                    if (json.status == 1) {
                        let dat = json.result.data;
                        let html = '';
                        html += '<table class="table table-striped" style="margin-bottom:0;table-layout:fixed;word-break: break-all;text-align:center;">\n' +
                            '                <tr>\n' +
                            '                    <td>币种</td>\n' +
                            '                    <td>金额</td>\n' +
                            '                </tr>';
                        for(var i=0;i<dat.length;i++){
                            for(var key in dat[i]){
                                html += '<tr>\n' +
                                    '         <td>'+key+'</td>\n' +
                                    '         <td>'+dat[i][key]+'</td>\n' +
                                    '    </tr>';
                            }
                        }
                        html += '</table>';
                        $('.current_profit_div').html(html);
                        $('.current_profit_div').show();
                        $('#current_profit_value').val(json.result.data_str);
                    }else if(json.status==-1){
                        alert(json.result.msg);
                    }
                }
            });

            $('.calc').text('重新计算');
        });
    });
    
    //支出类别
    function expend_cate(t){
        let selected = $(t).val();

        $.ajax({
            url:"<?php  echo $this->createMobileUrl('account/bookkeeping');?>",
            type:'POST',
            dataType:'json',
            data:{'op':'expend_cate_info','id':selected},
            success:function(json) {
                if(json.status==1){
                    let html = '';
                    let dat = json.result.data;
                    html += '<select name="cost_name" id="cost_name" style="width:100%;">';
                    for(var i=0;i<dat.length;i++){
                        html += '<option value="'+dat[i]+'">'+dat[i]+'</option>';
                    }
                    html += '</select>';

                    $(t).parent().parent().find('td').eq(1).html(html);
                }
            },error:function(json){
                alert('数据出错！');
            }
        });
    }

    //申报税种
    function taxes_change(t){
        let selected = $(t).val();

        $.ajax({
            url:"<?php  echo $this->createMobileUrl('account/bookkeeping');?>",
            type:'POST',
            dataType:'json',
            data:{'op':'tax_cate_info','pid':selected},
            success:function(json) {
                if(json.status==1){
                    let html = '';
                    let dat = json.result.data;
                    html += '<select name="taxes_info_id" id="taxes_info_id" style="width:100%;">';
                    for(var i=0;i<dat.length;i++){
                        html += '<option value="'+dat[i].id+'">'+dat[i].name+'</option>';
                    }
                    html += '</select>';

                    $(t).parent().parent().find('td').eq(1).html(html);
                }
            },error:function(json){
                alert('数据出错！');
            }
        });
    }

    //增加时税种默认第一个
    function taxes_change2(t,pid){
        $.ajax({
            url:"<?php  echo $this->createMobileUrl('account/bookkeeping');?>",
            type:'POST',
            dataType:'json',
            data:{'op':'tax_cate_info','pid':pid},
            success:function(json) {
                if(json.status==1){
                    let html = '';
                    let dat = json.result.data;
                    html += '<select name="taxes_info_id" id="taxes_info_id" style="width:100%;">';
                    for(var i=0;i<dat.length;i++){
                        html += '<option value="'+dat[i].id+'">'+dat[i].name+'</option>';
                    }
                    html += '</select>';

                    $(t).parent().parent().parent().next().find('td').eq(1).html(html);
                }
            },error:function(json){
                alert('数据出错！');
            }
        });
    }

    function buss_del(t){
        let len = $(t).parent().parent().parent().parent().children().length;
        if(len==2){
            alert('最少保留一个！');return;
        }
        $(t).parent().parent().parent().remove();
    }

    function buss_add(t,sta=1){
        let is_single = $('#is_single').val();

        let user_id = '';
        // if(is_single==1){
            //单个商户
            user_id = "<?php  echo $user['id'];?>";
        // }else if(is_single==0){
            //选择商户
            // user_id = $('#user_info').find('option:selected').val();
        // }
        if(user_id=='' || typeof(user_id)=='undefined'){
            alert('请先选择商户！');return;
        }
        
        let html = $(t).parent().parent().parent().html();
        $(t).parent().parent().parent().last().after('<tr>'+html+'</tr>');
        $(t).parent().parent().parent().next().find('.buss_del').css('display','block');

        //税费
        if(sta==2){
            let html = '';
            var pid = $(t).parent().parent().parent().next().children().find('#taxes_id').find('option:selected').val();

            taxes_change2(t,pid);
            // $(t).parent().parent().parent().find('td').eq(1).html(html);
        }
    }

    function buss_del2(t){
        let len = $(t).parent().parent().parent().parent().children().length;
        if(len==2){
            alert('最少保留一个！');return;
        }
        $(t).parent().parent().parent().remove();
    }

    function buss_add2(t){
        let html = $(t).parent().parent().parent().html();
        $(t).parent().parent().parent().last().after('<tr>'+html+'</tr>');
        $(t).parent().parent().parent().next().find('.buss_del').css('display','block');

        $(t).parent().parent().parent().parent().children().last().find('#expend_cate').find('option:first').prop('selected',true);
        expend_cate($(t).parent().parent().parent().parent().children().last().find('#expend_cate'));
    }

    function fnClose(typ){
        $('.mask').hide();
        $('.buss_cost_box').hide();
        $('.buss_fee_box').hide();
        $('.buss_taxes_box').hide();
        $('.buss_loss_box').hide();
    }
    
    $(function(){
        $('.info_sub').click(function(){
            //对账单信息
            let tax_id = $('#tax_id').val();
            let decl_date = $('#decl_date').val();
            //营业收入
            let buss_type = '';
            $('select[name="buss_type"]').each(function(){
                if($(this).find('option:selected').val()!=''){
                    buss_type += $(this).val()+',';
                }else{
                    alert('营业收入名称有空处，请检查后重新提交！');return false;
                }
            });
            let buss_project = '';
            $('input[name="buss_project"]').each(function(){
                if($(this).val()!=''){
                    buss_project += $(this).val()+',';
                }else{
                    alert('营业收入项目名称有空处，请检查后重新提交！');return false;
                }
            });
            let currency = '';
            $('select[name="currency"]').each(function(){
                if($(this).find('option:selected').val()!=''){
                    currency += $(this).val()+',';
                }else{
                    alert('营业收入币种有空处，请检查后重新提交！');return false;
                }
            });
            let income_price = '';
            $('input[name="income_price"]').each(function(){
                if($(this).val()!=''){
                    income_price += $(this).val()+',';
                }else{
                    alert('营业收入金额有空处，请检查后重新提交！');return false;
                }
            });

            
            if($('#decl_date').isEmpty()){
                alert('请选择申报时间！');return;
            }


            //营运支出
            let expend_cate = '';
            $('select[name="expend_cate"]').each(function(){
                if($(this).val()!=''){
                    expend_cate += $(this).val()+',';
                }else{
                    alert('请选择支出类别！');return false;
                }
            });
            let expend_name = '';
            $('select[name="cost_name"]').each(function(){
                if($(this).val()!=''){
                    expend_name += $(this).val()+',';
                }else{
                    alert('请选择项目！');return false;
                }
            });
            let expend_currency = '';
            $('select[name="currency2"]').each(function(){
                if($(this).val()!=''){
                    expend_currency += $(this).val()+',';
                }else{
                    alert('请选择项目！');return false;
                }
            });
            let expend_price = '';
            $('input[name="cost_price"]').each(function(){
                if($(this).val()!=''){
                    expend_price += $(this).val()+',';
                }else{
                    alert('请填写营运支出金额！');return false;
                }
            });

            //本期税费
            let taxes_id = '';let taxes_cate_info= '';let taxes_currency = '';let taxes_price = '';
            $('select[name="taxes_id"]').each(function(){
                if($(this).find('option:selected').val()!=''){
                    taxes_id += $(this).val()+',';
                }else{
                    alert('请选择税种！');return false;
                }
            });
            $('select[name="taxes_info_id"]').each(function(){
                if($(this).find('option:selected').val()!=''){
                    taxes_cate_info += $(this).val()+',';
                }else{
                    alert('请选择税种项目！');return false;
                }
            });
            $('select[name="currency4"]').each(function(){
                if($(this).find('option:selected').val()!=''){
                    taxes_currency += $(this).val()+',';
                }else{
                    alert('请选择税费币种！');return false;
                }
            });
            $('input[name="taxes_price"]').each(function(){
                if($(this).val()!=''){
                    taxes_price += $(this).val()+',';
                }else{
                    alert('请输入税费金额！');return false;
                }
            });

            let current_profit = $('#current_profit_value').val();

            $.ajax({
                url:"<?php  echo $this->createMobileUrl('account/bookkeeping');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'tax_declare_edit','tax_id':tax_id,'decl_date':decl_date,'buss_type':buss_type,'buss_project':buss_project,'currency':currency,'income_price':income_price,'expend_cate':expend_cate,'expend_name':expend_name,'expend_currency':expend_currency,'expend_price':expend_price,'taxes_id':taxes_id,'taxes_cate_info':taxes_cate_info,'taxes_currency':taxes_currency,'taxes_price':taxes_price,'current_profit':current_profit},
                success:function(json) {
                    if(json.status==-1){
                        alert(json.result.msg);
                    }else{
                        alert(json.result.msg);
                        setTimeout(function(){
                            window.location.reload();
                        },2000)
                    }
                },error:function(json){
                    alert('数据出错！');
                }
            });
        });
    });
</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>