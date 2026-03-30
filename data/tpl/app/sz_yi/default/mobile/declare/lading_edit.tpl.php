<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>修改提单</title>
<link href="../addons/sz_yi/static/css/layui.css" rel="stylesheet">
<style>
    .layui-table td, .layui-table th{ text-align: center;}
    .layui-table th{ background-color: #1E9FFF;color:#fff; }
    .required{color: red;font-size: 1.3rem;right: 3px;position: relative;top: 7px;}
    .icon-right{font-size: 20px;line-height: 20px;padding-right: 10px;vertical-align: middle;}
    .layui-layer-adminRight{top : 0px !important;}
    .layui-layer-btn .layui-layer-btn0{border-color: #F7931E!important;background-color: #F7931E!important;}
    .disf{display:flex;align-items: center;justify-content: space-evenly;}
    .layui-tab-title li{min-width:50px;}
    .layui-table img{max-width:50px;}
    .layui-upload-list img{max-width:50px;}
    .layui-form-radio i {top: 0;width: 16px;height: 16px;line-height: 16px;border: 1px solid #d2d2d2;font-size: 12px;border-radius: 2px;background-color: #fff;color: #fff !important;}
    .layui-form-radioed i {position: relative;width: 18px;height: 18px;border-style: solid;background-color: #5FB878;color: #5FB878 !important;}
    /* 使用伪类画选中的对号 */
    .layui-form-radioed i::after, .layui-form-radioed i::before {content: "";position: absolute;top: 8px;left: 5px;display: block;width: 12px;height: 2px;border-radius: 4px;background-color: #fff;-webkit-transform: rotate(-45deg);transform: rotate(-45deg);}
    .layui-form-radioed i::before {position: absolute;top: 10px;left: 2px;width: 7px;transform: rotate(-135deg);}
    .element_display,.box_nodisplay{display:none;}
    .box_display{display:block;}
    .search_box{position:relative;width: 100%;}
    .search_refuse{position: absolute;background: #fff;box-shadow: 0px 0px 15px 2px #bdbcbc;width: 100%;font-size: 14px;padding: 5px 0px;overflow: scroll;height: 120px;z-index: 9;}
    .license input{display:inline-block;}
</style>

<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <form class="layui-form" lay-filter="component-form-element">
            <div class="layui-col-md12">
                <div class="layui-card ladInfo">
                    <div class="layui-card-header">提单信息</div>
                    <div class="layui-card-body">
                        <input type="text" name="id" style="display:none;" value="<?php  echo $data['id'];?>">
                        <div class="layui-card-header" style="color:#fff;background:#F7931E;">主体信息</div>
                        <table class="layui-table">
                            <thead>
                            <tr>
                                <th>电商平台</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <div class="disf">
                                        <input type="radio" name="ebp_default" value="1" title="系统默认" lay-filter="ebp_default" <?php echo $data['ebp_default']==1?'checked':''?>>
                                        <input type="radio" name="ebp_default" value="2" title="商户指定" lay-filter="ebp_default" <?php echo $data['ebp_default']==2?'checked':''?>>
                                    </div>
                                    <div class="disf default">
                                        <div class="search_box">
                                            <input type="text" lay-verify="required" name="ebpName" placeholder="电商平台名称" value="<?php  echo $data['ebpName'];?>" autocomplete="off" class="layui-input element_display">

                                        </div>
                                        <div class="layui-btn element_display ebpSearch">查询</div>
                                    </div>
                                    <input type="text" lay-verify="required" name="ebpCode" placeholder="电商平台代码" value="<?php  echo $data['ebpCode'];?>" autocomplete="off" class="layui-input element_display">
                                </td>
                            </tr>
                            </tbody>

                            <thead>
                            <tr><th>电商企业</th></tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <div class="disf">
                                        <input type="radio" name="ebc_default" value="1" title="系统默认" lay-filter="ebc_default"  <?php echo $data['ebc_default']==1?'checked':''?>>
                                        <input type="radio" name="ebc_default" value="2" title="商户指定" lay-filter="ebc_default" <?php echo $data['ebc_default']==2?'checked':''?>>
                                    </div>
                                    <div class="disf default">
                                        <div class="search_box">
                                            <input type="text" lay-verify="required" name="ebcName" placeholder="电商企业名称" value="<?php  echo $data['ebcName'];?>" autocomplete="off" class="layui-input element_display">
                                        </div>
                                        <div class="layui-btn element_display ebcSearch">查询</div>
                                    </div>
                                    <input type="text" lay-verify="required" name="ebcCode" placeholder="电商企业代码" value="<?php  echo $data['ebcCode'];?>" autocomplete="off" class="layui-input element_display">
                                    <input type="text" lay-verify="required" name="ebc_tele_phone" placeholder="电商企业电话" value="<?php  echo $data['ebc_tele_phone'];?>" autocomplete="off" class="layui-input element_display">
                                </td>
                            </tr>
                            </tbody>

                            <thead>
                            <tr><th>支付企业</th></tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <div class="disf">
                                        <input type="radio" name="pay_default" value="1" title="系统默认" lay-filter="pay_default"  <?php echo $data['pay_default']==1?'checked':''?>>
                                        <input type="radio" name="pay_default" value="2" title="商户指定" lay-filter="pay_default" <?php echo $data['pay_default']==2?'checked':''?>>
                                    </div>
                                    <div class="disf default">
                                        <div class="search_box">
                                            <input type="text" lay-verify="required" name="payName" placeholder="支付企业名称" value="<?php  echo $data['payName'];?>" autocomplete="off" class="layui-input element_display">
                                        </div>
                                        <div class="layui-btn element_display paySearch">查询</div>
                                    </div>
                                    <input type="text" lay-verify="required" name="payCode" placeholder="支付企业代码" value="<?php  echo $data['payCode'];?>" autocomplete="off" class="layui-input element_display">
                                </td>
                            </tr>
                            </tbody>
                            <thead>
                            <tr><th>物流企业</th></tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <div class="disf">
                                        <input type="radio" name="logi_default" value="1" title="系统默认" lay-filter="logi_default"  <?php echo $data['logi_default']==1?'checked':''?>>
                                        <input type="radio" name="logi_default" value="2" title="商户指定" lay-filter="logi_default" <?php echo $data['logi_default']==2?'checked':''?>>
                                    </div>
                                    <div class="disf default">
                                        <div class="search_box">
                                            <input type="text" lay-verify="required" name="logisticsName" placeholder="物流企业名称" value="<?php  echo $data['logisticsName'];?>" autocomplete="off" class="layui-input element_display">
                                        </div>
                                        <div class="layui-btn element_display logiSearch">查询</div>
                                    </div>
                                    <input type="text" lay-verify="required" name="logisticsName" placeholder="物流企业名称" value="<?php  echo $data['logisticsCode'];?>" autocomplete="off" class="layui-input element_display">
                                </td>
                            </tr>
                            </tbody>
                            <thead>
                            <tr><th>监管场所</th></tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <!--<div class="disf">-->
                                    <!--<input type="radio" name="loctNo_default" class="layui-input" value="1" title="系统默认" lay-filter="loctNo_default" checked>-->
                                    <!--<input type="radio" name="loctNo_default" class="layui-input" value="2" title="商户指定" lay-filter="loctNo_default">-->
                                    <!--</div>-->
                                    <div class="disf default">
                                        <select name="loctNo" id="loctNo" lay-search>
                                            <?php  if(is_array($loctcode)) { foreach($loctcode as $v) { ?>
                                            <option value="<?php  echo $v['code_value'];?>" <?php  if($data['loctNo']==$val['code_value']) { ?>selected<?php  } ?>><?php  echo $v['code_value'];?>:<?php  echo $v['code_name'];?></option>
                                            <?php  } } ?>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            </tbody>


                        </table>
                    </div>

                    <div class="layui-card-body">
                        <div class="layui-card-header" style="color:#fff;background:#F7931E;">报关信息</div>
                        <table class="layui-table">
                            <thead>
                            <tr>
                                <th>申报海关</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <div class="disf">
                                        <select name="customs_code" id="customs_code" lay-verify="required" lay-search>
                                            <option value="">申报地海关</option>
                                            <?php  if(is_array($customs_codes)) { foreach($customs_codes as $val) { ?>
                                            <option value="<?php  echo $val['AreaCode'];?>" <?php  if($data['customs_code']==$val['AreaCode']) { ?>selected<?php  } ?>><?php  echo $val['AreaCode'];?></option>
                                            <?php  } } ?>
                                        </select>
                                        <select name="port_code" id="port_code" lay-verify="required" lay-search>
                                            <option value="">通关海关</option>
                                            <?php  if(is_array($customs_codes)) { foreach($customs_codes as $val) { ?>
                                            <option value="<?php  echo $val['AreaCode'];?>" <?php  if($data['port_code']==$val['AreaCode']) { ?>selected<?php  } ?>><?php  echo $val['AreaCode'];?></option>
                                            <?php  } } ?>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            </tbody>

                            <thead>
                            <tr><th>出口日期</th></tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <input type="text" lay-verify="required" name="ie_date" class="layui-input" id="ie_date" placeholder="yyyyMMdd" value="<?php  echo $data['ie_date'];?>">
                                </td>
                            </tr>
                            </tbody>

                            <thead>
                            <tr><th>起止国地</th></tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <div class="disf default">
                                        <select name="pod" id="pod" lay-verify="required" lay-search>
                                            <option value="">启运港口</option>
                                            <?php  if(is_array($port_code)) { foreach($port_code as $val) { ?>
                                            <option value="<?php  echo $val['code_value'];?>" <?php  if($data['pod']==$val['code_value']) { ?>selected<?php  } ?>><?php  echo $val['code_name'];?></option>
                                            <?php  } } ?>
                                        </select>
                                        <select name="country_code" lay-verify="required" id="country_code" lay-search>
                                            <option value="">运抵国别</option>
                                            <?php  if(is_array($country_code)) { foreach($country_code as $val) { ?>
                                            <option value="<?php  echo $val['code_value'];?>" <?php  if($data['country_code']==$val['code_value']) { ?>selected<?php  } ?>><?php  echo $val['code_name'];?></option>
                                            <?php  } } ?>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                            <thead>
                            <tr><th>境内运抵</th></tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td class="license">
                                    <!--<input type="text" lay-verify="required" name="voyage_no" placeholder="运输工具编号" value="<?php  echo $data['voyage_no'];?>" autocomplete="off" class="layui-input">-->
                                    <input type="text" lay-verify="required" name="license_place" placeholder="粤"  value="<?php  echo $data['voyage_no']['0'];?>" autocomplete="off" class="layui-input" style="width:30px;" onchange="if(value.length > 1)value = value.slice(0, 1);">
                                    <input type="text" lay-verify="required" name="license_place2" placeholder="Y"  value="<?php  echo $data['voyage_no']['1'];?>" autocomplete="off" class="layui-input"style="width:30px;" oninput="if(value.length > 1)value = value.slice(0, 1)">
                                    ▪
                                    <input type="text" lay-verify="required" name="license_num1" placeholder="A"  value="<?php  echo $data['voyage_no']['2'];?>" autocomplete="off" class="layui-input"style="width:30px;" oninput="if(value.length > 1)value = value.slice(0, 1)">
                                    <input type="text" lay-verify="required" name="license_num2" placeholder="A"  value="<?php  echo $data['voyage_no']['3'];?>" autocomplete="off" class="layui-input"style="width:30px;" oninput="if(value.length > 1)value = value.slice(0, 1)">
                                    <input type="text" lay-verify="required" name="license_num3" placeholder="A"  value="<?php  echo $data['voyage_no']['4'];?>" autocomplete="off" class="layui-input"style="width:30px;" oninput="if(value.length > 1)value = value.slice(0, 1)">
                                    <input type="text" lay-verify="required" name="license_num4" placeholder="A"  value="<?php  echo $data['voyage_no']['5'];?>" autocomplete="off" class="layui-input"style="width:30px;" oninput="if(value.length > 1)value = value.slice(0, 1)">
                                    <input type="text" lay-verify="required" name="license_num5" placeholder="A"  value="<?php  echo $data['voyage_no']['6'];?>" autocomplete="off" class="layui-input"style="width:30px;" oninput="if(value.length > 1)value = value.slice(0, 1)">
                                    <input type="text" name="license_num6" placeholder="x" value="<?php  echo $data['voyage_no']['7'];?>" autocomplete="off" class="layui-input" style="width:30px;border:2px solid #009688;" oninput="if(value.length > 1)value = value.slice(0, 1)">
                                </td>
                            </tr>
                            </tbody>
                            <thead>
                            <tr><th>跨境运输</th></tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <select name="transport_code" id="transport_code" lay-filter="transport_select" lay-verify="required" lay-search>
                                        <?php  if(is_array($transport)) { foreach($transport as $val) { ?>
                                        <option value="<?php  echo $val['code_value'];?>" <?php  if($data['transport_code']==$val['code_value']) { ?>selected<?php  } ?>><?php  echo $val['code_name'];?></option>
                                        <?php  } } ?>
                                    </select>

                                    <div class="sepcial_box <?php  if(($data['transport_code']==1 || $data['transport_code']==7 || $data['transport_code']==8 || $data['transport_code']=='Y')) { ?>box_display<?php  } else { ?>box_nodisplay<?php  } ?>">
                                        <div class="search_box">
                                            <input type="text" name="iac_name" placeholder="区内企业名称" value="<?php  echo $data['iac_name'];?>" autocomplete="off" class="layui-input" style="margin-top:7px;display:inline-block;width:78%;">
                                            <div class="layui-btn iacSearch" style="display:inline-block;">查询</div>
                                        </div>
                                        <input type="text" name="iac_code" placeholder="区内企业代码" value="<?php  echo $data['iac_code'];?>" autocomplete="off" class="layui-input" style="margin-top:7px;">
                                        <input type="text" name="ems_no" placeholder="账册编号" value="<?php  echo $data['ems_no'];?>" autocomplete="off" class="layui-input" style="margin-top:7px;">
                                    </div>

                                    <div class="normarl_box <?php  if(($data['transport_code']==0 || $data['transport_code']==2 || $data['transport_code']==3 || $data['transport_code']==4 || $data['transport_code']==5 || $data['transport_code']==9 || $data['transport_code']=='A' || $data['transport_code']=='H' || $data['transport_code']=='W' || $data['transport_code']=='X' || $data['transport_code']=='Z')) { ?>box_display<?php  } else { ?>box_nodisplay<?php  } ?>">
                                        <input type="text" name="traf_name" placeholder="运输工具编号" value="<?php  echo $data['traf_name'];?>" autocomplete="off" class="layui-input" style="margin-top:7px;">
                                        <input type="text" name="oceanLad" placeholder="海运提单编号" value="<?php  echo $data['oceanLad'];?>" autocomplete="off" class="layui-input" style="margin-top:7px;">

                                        <div class="layui-input-block" style="margin-left:0;margin-top:10px;">
                                            <div class="layui-upload" style="text-align:left;">
                                                <button type="button" class="layui-btn" id="transport_file-upload">上传文件</button>
                                                <blockquote class="layui-elem-quote layui-quote-nm yulan" style="margin-top: 10px;">
                                                    预览图：
                                                    <div class="layui-upload-list" id="transport_file-upload-list">
                                                        <?php  if(is_array($data['transport_file'])) { foreach($data['transport_file'] as $k => $v) { ?>
                                                        <div style="display: inline-block;margin-top:10px;">
                                                            <img onclick="seePic(this);" src="/attachment/<?php  echo $v;?>" class="layui-upload-img" onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png">
                                                            <button type="button" onclick="delPic(this);" class="layui-btn layui-btn-xs layui-btn-danger" style="position: relative;left: -20px;top: -13px;">删除</button>
                                                            <input type="hidden" name="transport_file[<?php  echo $k;?>]" value="<?php  echo $v;?>">
                                                        </div>
                                                        <?php  } } ?>
                                                    </div>
                                                </blockquote>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>


                        </table>
                    </div>
                </div>

                <div class="layui-form-item" style="margin-top: 15px;text-align: center;">
                    <div>
                        <button class="layui-btn layui-btn-normal submit" lay-submit="" lay-filter="component-form-element" style="background:#F7931E !important;">提交修改</button>
                    </div>
                </div>


            </div>
        </form>
    </div>
</div>
<script type="text/javascript" src="../addons/sz_yi/static/js/layui/layui.js"></script>
<script>
    $(function() {
        layui.use(['layer', 'form', 'table', 'upload', 'laydate'], function () {
            var  $ = layui.$
                ,layer = layui.layer
                , form = layui.form
                ,element = layui.element
                ,laydate = layui.laydate
                ,upload = layui.upload;

            // element.render('breadcrumb', 'breadcrumb');

            laydate.render({
                elem: '#ie_date'
                ,format: 'yyyyMMdd'
            });
            form.on('radio(ebp_default)',function(data){
                var isEbpType = data.value;
                if(isEbpType=='2'){
                    $('input[name="ebpCode"]').attr('value','').removeClass('element_display');
                    $('input[name="ebpName"]').attr('value','').removeClass('element_display');
                    $('.ebpSearch').removeClass('element_display');
                }else{
                    $('input[name="ebpCode"]').attr('value','44289609SL').addClass('element_display');
                    $('input[name="ebpName"]').attr('value','佛山市钜铭商务资讯服务有限公司').addClass('element_display');
                    $('.ebpSearch').addClass('element_display');
                }
            });

            form.on('radio(ebc_default)',function(data){
                var isEbpType = data.value;
                if(isEbpType=='2'){
                    $('input[name="ebcCode"]').attr('value','').removeClass('element_display');
                    $('input[name="ebcName"]').attr('value','').removeClass('element_display');
                    $('input[name="ebc_tele_phone"]').attr('value','').removeClass('element_display');
                    $('.ebcSearch').removeClass('element_display');
                }else{
                    $('input[name="ebcCode"]').attr('value','44289609SL').addClass('element_display');
                    $('input[name="ebcName"]').attr('value','佛山市钜铭商务资讯服务有限公司').addClass('element_display');
                    $('input[name="ebc_tele_phone"]').attr('value','0757-86329911').addClass('element_display');
                    $('.ebcSearch').addClass('element_display');
                }
            });

            form.on('radio(pay_default)',function(data){
                var isEbpType = data.value;
                if(isEbpType=='2'){
                    layer.confirm('请选择支付企业类型', {
                        btn: ['境内支付企业','境外支付企业'],
                        cancel:function(){
                            return false;
                        }
                    }, function(){
                        $('input[name="isDefault"]').val('2');
                        $('input[name="payCode"]').removeAttr('readonly');
                        $('input[name="payName"]').val("");
                        $('input[name="payCode"]').val("");
                        $('.paySearch').show();
                        layer.closeAll();
                    }, function(){
                        $('input[name="isDefault"]').val('1');
                        $('input[name="payCode"]').attr('readonly','readonly');
                        $('input[name="payName"]').val("");
                        $('input[name="payCode"]').val("");
                        $('.paySearch').hide();
                        layer.closeAll();
                    });

                    $('input[name="payCode"]').attr('value','').removeClass('element_display');
                    $('input[name="payName"]').attr('value','').removeClass('element_display');
                    $('.paySearch').removeClass('element_display');
                }else{
                    $('input[name="payCode"]').attr('value','').addClass('element_display');
                    $('input[name="payName"]').attr('value','PayPal').addClass('element_display');
                    $('.paySearch').hide();
                    $('.paySearch').addClass('element_display');
                }
            });

            form.on('radio(logi_default)',function(data){
                var isEbpType = data.value;
                if(isEbpType=='2'){
                    $('input[name="logisticsCode"]').attr('value','').removeClass('element_display');
                    $('input[name="logisticsName"]').attr('value','').removeClass('element_display');
                    $('.logiSearch').removeClass('element_display');
                }else{
                    $('input[name="logisticsCode"]').attr('value','4401983074').addClass('element_display');
                    $('input[name="logisticsName"]').attr('value','荣通国际货运有限公司').addClass('element_display');
                    $('.logiSearch').addClass('element_display');
                }
            });

            form.on('select(transport_select)',function(data){
                var val = data.value;
                console.log(val);
                if(val==1 || val==7 || val==8 || val=='Y'){
                    $('.sepcial_box').show();
                    $('.normarl_box').hide();
                }else if(val==6){
                    $('.sepcial_box').hide();
                    $('.normarl_box').hide();
                }else{
                    $('.sepcial_box').hide();
                    $('.normarl_box').show();
                }
            });

            upload.render({
                elem: '#transport_file-upload'
                ,url: 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=util&m=sz_yi&p=uploader'
                ,accept: 'file'
                ,data: {file: "transport_file-upload",'op':'uploadDeclFile'}
                ,multiple: true
                ,before: function(obj){
                    layer.load(); //上传loading
                }
                ,done: function(res){
                    layer.closeAll('loading'); //关闭loading
                    if(res.status == 'success')
                    {
                        var length = $('#transport_file-upload-list').children().length;
                        $('#transport_file-upload-list').append('<div style="display: inline-block;margin-top:10px;"><img onclick="seePic(this);" src="/attachment/'+ res.filename +'" class="layui-upload-img" onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png"><button type="button" onclick="delPic(this);" class="layui-btn layui-btn-xs layui-btn-danger" style="position: relative;left: -20px;top: -13px;">删除</button><input type="hidden" name="transport_file['+length+']" value="'+res.filename+'"></div>')
                    }
                }
            });

            form.render(null, 'component-form-element');


            form.on('submit(component-form-element)', function(data){
                console.log(data.field);
                if( data.field['transport_code'] != 6) {
                    if(data.field['transport_code']==1 || data.field['transport_code']==7 || data.field['transport_code']==8 || data.field['transport_code']=='Y'){
                        if(data.field['iac_name']=='' || data.field['iac_code']=='' || data.field['ems_no']==''){
                            layer.msg('请输入跨境运输信息！',{time:2000});
                            return false;
                        }
                    }else{
                        if(data.field['traf_name']=='' || data.field['oceanLad']=='' || $('#transport_file-upload-list').children().length == 0){
                            layer.msg('请输入跨境运输信息！',{time:2000});
                            return false;
                        }
                    }
                }

                let ebp = data.field['ebpCode'];
                if(ebp.length!=10){
                    layer.msg('请输入电商平台10位海关注册/备案编码。',{time:2000});
                    return false;
                }

                let ebc =data.field['ebcCode'];
                if(ebc.length!=10){
                    layer.msg('请输入电商企业10位海关注册/备案编码。',{time:2000});
                    return false;
                }
                if(data.field['isDefault']==2 && data.field['pay_default']==2){
                    //境内企业
                    let pay =data.field['payCode'];
                    if(pay.length!=10){
                        layer.msg('请输入支付企业10位海关注册/备案编码。',{time:2000});
                        return false;
                    }
                }
                let logi = data.field['logisticsCode'];
                if(logi.length!=10){
                    layer.msg('请输入物流企业10位海关注册/备案编码。',{time:2000});
                    return false;
                }

                $.ajax({
                    url:"<?php  echo $this->createMobileUrl('declare/index');?>&op=lading_edit",
                    type:'POST',
                    data:data.field,
                    dataType:'JSON',
                    success:function(res){
                        layer.msg(res.result.msg,{time:2000}, function () {
                            if(res.status == 1)
                            {
                                window.location.reload();
                            }
                        });
                    },
                    error:function (data) {
                        layer.msg('系统错误',{time:2000});
                    }
                });

                return false;
            });
        });

        //选项框
        $('.transport .layui-tab-title li').click(function(){
            let idx = $(this).index();
            $('.transport .layui-tab-title li').eq(idx).addClass('layui-this').siblings().removeClass('layui-this');
            $('.transport .layui-tab-content .layui-tab-item').eq(idx).addClass('layui-show').siblings().removeClass('layui-show');
        });

        $('input[name="license_num6"]').on('input',function(){
            var layer = layui.layer,$ = layui.$;
            layer.alert('您好，运抵车辆为新能源车辆，请确保监管场所许可进入。',{time:6000});
        });

        //监听事件
        $('.ebpSearch').click(function(){
            let val = $('input[name="ebpName"]').val();
            getHsCode(val,1);
        });
        $('.ebcSearch').click(function(){
            let val = $('input[name="ebcName"]').val();
            getHsCode(val,2);
        });
        $('.paySearch').click(function(){
            let val = $('input[name="payName"]').val();
            getHsCode(val,3);
        });
        $('.logiSearch').click(function(){
            let val = $('input[name="logisticsName"]').val();
            getHsCode(val,4);
        });
        $('.iacSearch').click(function(){
            let val = $('input[name="iac_name"]').val();
            getHsCode(val,5);
        });
    });

    function getHsCode(name,typ){
        var layer = layui.layer,$ = layui.$;
        if(name=='' || typeof(name)=='undefined'){
            layer.msg('请输入企业名称！',{time:2000});
            return false;
        }
        layer.load();

        // 海关登记
        $.ajax({
            url: 'https://declare.gogo198.cn/api/qxbquery/getCustomsListByName?name=' + name,
            type: 'GET',
            data: {},
            success: function (res) {
                console.log(res);
                if (res.status == 200) {
                    layer.closeAll('loading');
                    var items = res.data;
                    if(items.length==0){
                        layer.msg('查无数据！',{time:2000});
                        return false;
                    }
                    var html = '<div class="search_refuse">\n';
                    for (var i = 0; i < items.length; i++) {
                        html += '\t<div data-customs_num="'+items[i].customs_num+'" data-customslist="'+items[i].ename+'" style="margin-top: 10px;" onclick="selectThis(this,' + typ + ')">'+items[i].ename+'</div>';
                    }
                    html+='</div>';
                    if(typ==1){
                        $('input[name="ebpName"]').parent().append(html);
                        $('input[name="ebpName"]').parent().find('.search_refuse').show();
                    }else if(typ==2){
                        $('input[name="ebcName"]').parent().append(html);
                        $('input[name="ebcName"]').parent().find('.search_refuse').show();
                    }else if(typ==3){
                        $('input[name="payName"]').parent().append(html);
                        $('input[name="payName"]').parent().find('.search_refuse').show();
                    }else if(typ==4){
                        $('input[name="logisticsName"]').parent().append(html);
                        $('input[name="logisticsName"]').parent().find('.search_refuse').show();
                    }else if(typ==5){
                        $('input[name="iac_name"]').parent().append(html);
                        $('input[name="iac_name"]').parent().find('.search_refuse').show();
                    }
                }else{
                    layer.msg(res.message,{time:2000});
                }
            }
        });
    }

    function selectThis(t,typ){
        var layer = layui.layer,$ = layui.$;
        let thi = $(t);
        let customs_num = thi.attr('data-customs_num');
        let customslist = thi.attr('data-customslist');
        if(customs_num==''){
            layer.confirm('您好！此单位是否未注册或备案为“海关企业”？', {
                btn: ['是','否']
            }, function(){
                //是
                layer.alert('由于单位不是海关注册/备案企业，不能为申报主体，请选择其他单位或默认系统配置的申报主体。');
                return false;
            }, function(){
                //否
                layer.alert('请直接输入该单位的10位海关注册/备案编码。');
                return false;
            });
        }
        if(typ==1){
            $('input[name="ebpName"]').val(customslist);
            $('input[name="ebpCode"]').val(customs_num);
            $('input[name="ebpName"]').parent().find('.search_refuse').hide();
        }else if(typ==2){
            $('input[name="ebcName"]').val(customslist);
            $('input[name="ebcCode"]').val(customs_num);
            $('input[name="ebcName"]').parent().find('.search_refuse').hide();
        }else if(typ==3){
            $('input[name="payName"]').val(customslist);
            $('input[name="payCode"]').val(customs_num);
            $('input[name="payName"]').parent().find('.search_refuse').hide();
        }else if(typ==4){
            $('input[name="logisticsName"]').val(customslist);
            $('input[name="logisticsCode"]').val(customs_num);
            $('input[name="logisticsName"]').parent().find('.search_refuse').hide();
        }else if(typ==5){
            $('input[name="iac_name"]').val(customslist);
            $('input[name="iac_code"]').val(customs_num);
            $('input[name="iac_name"]').parent().find('.search_refuse').hide();
        }
    }

    function delPic(obj)
    {
        var layer = layui.layer,$ = layui.$;
        layer.confirm('确认要删除该附件？', {
            btn: ['删除','取消']
        }, function(){
            $(obj).parent().remove();
            layer.closeAll();
            // $.ajax({
            //     url: "",
            //     type: 'POST',
            //     dataType: 'json',
            //     data: {op: 'remove', file: del_img},
            //     function(rjson) {
            //         if (rjson.status == 1) {
            //
            //         }
            //     }
            // });

        }, function(){

        });
    }
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>