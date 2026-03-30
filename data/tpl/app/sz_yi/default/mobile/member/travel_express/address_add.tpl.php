<?php defined('IN_IA') or exit('Access Denied');?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<title>新增收件人</title>
	<link rel="stylesheet" type="text/css" href="../addons/sz_yi/static/travel_express/css/hui.css" />
	<link rel="stylesheet" type="text/css" href="../addons/sz_yi/static/travel_express/css/style.css" />
	<style>
        .hui-header h1{
            padding: 0px 38px 0px 0;
        }
        .top-icon{
            margin: 2px 10px 0 0;
        }
        .hui-button{border: 1px solid #898989 !important;}
        #hui-input-clear{width: 38px;height: 38px;line-height: 38px;right: 11px;top: 11px;}
        .hui-wrap{
			margin-bottom: 60px;
		}
    </style>
</head>
<body>
<header class="hui-header">
    <div id="hui-back"></div>
    <h1>新增收件人</h1>
</header>

<div class="hui-wrap">
    <div class="wrap-top">
        <div class="hui-footer-icons hui-icons-news top-icon"></div>
        <div class="text-title">
            <p>新增收件人信息</p>
            <p class="title-en text-space">Add recipient information</p>
        </div>
    </div>
    <form style="padding:0 10px;" class="hui-form" id="form1">
        <div class="hui-form-items">
            <div class="hui-form-items-title"><p>收件人姓名</p><p class="sizes">The recipient's name</p></div>
            <input type="text" class="hui-input hui-input-clear" name="realname" placeholder="如：张三" checkType="string" checkData="2,10" checkMsg="收件人姓名应为2-10个字符" />
        </div>
        <div class="hui-form-items">
            <div class="hui-form-items-title"><p>收件人身份证号</p><p class="sizes">Recipient ID number</p></div>
            <input type="text" class="hui-input hui-input-clear" placeholder="如：440xxxx..." name="idcard" id="idcard" />
        </div>
        <div class="hui-form-items">
            <div class="hui-form-items-title"><p>联系电话</p><p class="sizes">Recipient's phone</p></div>
            <input type="number" class="hui-input hui-input-clear" placeholder="如：1889088..." name="mobile" checkType="phone" checkMsg="联系电话格式错误" />
        </div>
        <div class="hui-form-items">
            <div class="hui-form-items-title"><p>所在省市</p><p class="sizes">Province City</p></div>
            <button type="button" class="hui-button hui-button-large" id="btn3" style="margin-left: 10px;margin-right: 0;">选择省市区</button>
            <input type="hidden" name="province" id="province" checkType="string" checkData="1,10" checkMsg="请选择省市区">
            <input type="hidden" name="city" id="city" checkType="string" checkData="1,10" checkMsg="请选择省市区">
            <input type="hidden" name="area" id="area" checkType="string" checkData="1,10" checkMsg="请选择省市区">

            <input type="hidden" name="province_code" id="province_code">
            <input type="hidden" name="city_code" id="city_code">
            <input type="hidden" name="area_code" id="area_code">
        </div>
        <div class="hui-form-items">
            <div class="hui-form-items-title"><p>详细地址</p><p class="sizes">Address</p></div>
            <input type="text" class="hui-input hui-input-clear" placeholder="如：xxx座xx号..." name="address" checkType="string" checkData="2,30" checkMsg="详细地址2-30个字符" />
        </div>
        <div class="hui-form-items">
            <div class="hui-form-items-title"><p>收件邮编</p><p class="sizes">Postcode</p></div>
            <input type="number" class="hui-input hui-input-clear" placeholder="如：528000" name="zipcode" checkType="zipcode" checkMsg="收件邮编格式错误" />
        </div>
        <div style="padding:15px 8px; width: 35%; margin: 20px auto 0;">
            <button type="button" class="hui-button hui-primary hui-fr blue" id="submitBtn"><img src="../addons/sz_yi/static/travel_express/images/icon_05.png" alt="" class="button-pic">保存</button>
        </div>
    </form>
</div>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/travel_express_footer', TEMPLATE_INCLUDEPATH)) : (include template('common/travel_express_footer', TEMPLATE_INCLUDEPATH));?>

<script src="../addons/sz_yi/static/travel_express/js/hui.js" type="text/javascript" charset="utf-8"></script>
<script src="../addons/sz_yi/static/travel_express/js/hui-form.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" src="../addons/sz_yi/static/travel_express/js/hui-picker.js" charset="utf-8"></script>
<script type="text/javascript" src="../addons/sz_yi/static/travel_express/js/cities.js" charset="utf-8"></script>
<script type="text/javascript">
    hui.formInit();
    //表单元素数据收集演示
    var picker3 = new huiPicker('#btn3', function(){
	    var sheng   = picker3.getText(0);
	    var shi     = picker3.getText(1);
	    var qu      = picker3.getText(2);
	    var shengVal= picker3.getVal(0);
	    var shiVal  = picker3.getVal(1);
	    var quVal   = picker3.getVal(2);
        hui("#province").val(sheng);
        hui("#city").val(shi);
        hui("#area").val(qu);
        hui("#province_code").val(shengVal);
        hui("#city_code").val(shiVal);
        hui("#area_code").val(quVal);
	    //console.log(shengVal, shiVal, quVal);
	    hui('#btn3').html(sheng + shi + qu);
	});
    picker3.level = 3;
	//cities 数据来源于 cities.js
	// 默认值设置方式 [330000 330400 330424] 浙江省 嘉兴市 海盐区
	var defaultVal = [];
	// 不设置默认值忽略第三个参数即可
	picker3.bindRelevanceData(cities, defaultVal);
    hui('#submitBtn').click(function(){
        var res = huiFormCheck('#form1');
        if(res){
            var data = hui.getFormData('#form1');
            hui.ajax({
                url  : '<?php  echo $this->createMobileUrl("member/travel_express_address")?>&op=submit',
                type : 'POST',
                data : {realname: data.realname,mobile: data.mobile,address: data.address,province: data.province,
                    city: data.city,area: data.area,idcard: data.idcard, province_code: data.province_code, city_code: data.city_code, area_code: data.area_code, zipcode: data.zipcode},
                beforeSend : function(){hui.loading();},
                complete   : function(){hui.closeLoading();},
                success : function(res){
                    hui.toast(res.result.msg);
                    setTimeout(function(){
                        history.back(-1);
                    }, 2000);
                },
                error : function(e){
                    console.log(JSON.stringify(e));
                    hui.iconToast('系统错误', 'warn');
                },
                'backType' : "JSON"
            });
        }
    });

    function huiFormCheckAttach(){
        var reg=/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/;
        var re = new RegExp(reg);
        var idcard = hui("#idcard").val();
        if(idcard == '')
        {
            hui.toast('身份证号码不能为空'); 
            return false;
        }else{
            if( re.test(idcard) == false )
            {
                hui.toast('请填写正确的身份证号码！'); 
                return false;
            }
        }
        return true;
    }
</script>
</body>
</html>