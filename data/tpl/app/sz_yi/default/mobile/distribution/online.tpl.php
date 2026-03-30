<?php defined('IN_IA') or exit('Access Denied');?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>在线装箱</title>
    <!--手机端需要添加-->
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">

    <!--手机端需要添加---->
    <link rel="stylesheet" type="text/css" href="../addons/sz_yi/static/travel_express/css/hui.css" />
    <link rel="stylesheet" href="../addons/sz_yi/template/mobile/default/enterprise/static/css/test_rx.css">
    <script src="../addons/sz_yi/template/mobile/default/enterprise/static/js/jquery-1.8.3.min.js"></script>
    <script src="../addons/sz_yi/template/mobile/default/enterprise/static/js/jQuery.fontFlex.js"></script>

    <style type="text/css">
        div{overflow: hidden;}
        .hui-tab-item{border:0px;}
        .dis{ background: #bbbbbb !important;}
        /*2021/10/19*/
        .kctm_select{width:100%;height:40px;}
        .required{color:#ff5555;}
        .ssl_card_header{position:relative;height: 42px;line-height: 42px;padding: 0 15px;border-bottom: 1px solid #f6f6f6;color: #333;border-radius: 2px 2px 0 0;}
        .ssl_table{width:100%;padding: 10px;box-sizing: border-box;}
        .ssl_td1{text-align: right;padding-right: 10px;width: 30%;box-sizing: border-box;}
        .ssl_td2{width: 90%;display:flex;align-items:center;justify-content: center}
    </style>
    <script>
        $(function(){
            hui.formInit();
            //320宽度的时候html字体大小是20px;、640宽度的时候html字体大小是40px;
            $('html').fontFlex(20, 40, 16);
        })
    </script>
</head>
<body>
<div class="wjdt_title">
    <div class="header">
        <h3>在线装箱</h3>
    </div>

    <form class="hui-form" id="form1">
        <div class="kcks_title_tss " id="infos" date-title="2" date-last="0">
            <input type="text" name="canSubmit" value='0' style="display:none;"/>
            <div class="pack" id="pack0">
                <input type="text" name="is_tariff" value='1' style="display:none;"/>
                <input type="text" name="exchange_rate" value='' style="display:none;"/>
                <div class="ssl_card_header">选择货号</div>
                <table class="ssl_table">
                    <tr>
                        <td class="ssl_td1"><span class="required">*</span>选择货号</td>
                        <td class="ssl_td2">
                            <select name="way_type" id="way_type" class="kctm_select">
                                <option value="">请选择货号</option>
                                <?php  if(is_array($allgoods)) { foreach($allgoods as $k => $v) { ?>
                                <option value="<?php  echo $v['goodssn'];?>"><?php  echo $v['goodssn'];?></option>
                                <?php  } } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="ssl_td1">币种</td>
                        <td class="ssl_td2"><input type="text" name="currency" class="kctm_input" placeholder="币种" readonly></td>
                    </tr>
                    <tr>
                        <td class="ssl_td1">品名</td>
                        <td class="ssl_td2"><input type="text" name="goods_name" class="kctm_input" placeholder="品名" readonly></td>
                    </tr>
                    <tr>
                        <td class="ssl_td1">规格</td>
                        <td class="ssl_td2"><input type="text" name="specs" class="kctm_input" placeholder="规格" readonly></td>
                    </tr>
                    <tr>
                        <td class="ssl_td1">单价</td>
                        <td class="ssl_td2"><input type="text" name="price" class="kctm_input" placeholder="单价" readonly></td>
                    </tr>
                    <tr>
                        <td class="ssl_td1">净重</td>
                        <td class="ssl_td2"><input type="text" name="netwt" class="kctm_input" placeholder="净重" readonly></td>
                    </tr>
                    <tr>
                        <td class="ssl_td1">毛重</td>
                        <td class="ssl_td2"><input type="text" name="grosswt" class="kctm_input" placeholder="毛重" readonly></td>
                    </tr>
                </table>
                <div class="ssl_card_header">装箱数量</div>
                <table class="ssl_table">
                    <tr>
                        <td class="ssl_td1"><span class="required">*</span>装箱单位</td>
                        <td class="ssl_td2">
                            <select name="pack_unit" id="pack_unit" class="kctm_select">
                                <option value="">请选择装箱单位</option>
                                <?php  if(is_array($unit)) { foreach($unit as $k => $v) { ?>
                                <option value="<?php  echo $v['code_value'];?>-<?php  echo $v['code_name'];?>"><?php  echo $v['code_name'];?></option>
                                <?php  } } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="ssl_td1"><span class="required">*</span>装箱数量</td>
                        <td class="ssl_td2"><input type="number" name="pack_num" class="kctm_input" placeholder="请输入装箱数量"></td>
                    </tr>
                    <tr>
                        <td class="ssl_td1"><span class="required">*</span>总装箱数</td>
                        <td class="ssl_td2"><input type="number" name="goods_name" class="kctm_input" placeholder="请输入总装箱数"></td>
                    </tr>
                </table>
                <div class="ssl_card_header">装箱金额</div>
                <table class="ssl_table">
                    <tr>
                        <td class="ssl_td1">商品单价<div class="bizhong"></div></td>
                        <td class="ssl_td2"><input type="text" name="price" class="kctm_input" placeholder="商品单价" readonly></td>
                    </tr>
                    <tr>
                        <td class="ssl_td1">每箱单价<div class="bizhong"></div></td>
                        <td class="ssl_td2"><input type="text" name="pack_every_price" class="kctm_input" placeholder="每箱单价" readonly></td>
                    </tr>
                    <tr>
                        <td class="ssl_td1">装箱金额<div class="bizhong"></div></td>
                        <td class="ssl_td2"><input type="text" name="pack_amount" class="kctm_input" placeholder="装箱金额" readonly></td>
                    </tr>
                </table>
                <div class="ssl_card_header">装箱重量</div>
                <table class="ssl_table">
                    <tr>
                        <td class="ssl_td1">商品毛重</td>
                        <td class="ssl_td2">
                            <input type="text" name="g_grosswt" class="kctm_input" placeholder="商品毛重" readonly>
                            <span style="margin-left:5px;display:flex;align-items:center;">KG/<span class="shangpindanwei"></span></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="ssl_td1">商品净重</td>
                        <td class="ssl_td2">
                            <input type="text" name="g_netwt" class="kctm_input" placeholder="商品净重" readonly>
                            <span style="margin-left:5px;display:flex;align-items:center;">KG/<span class="shangpindanwei"></span></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="ssl_td1">每箱毛重</td>
                        <td class="ssl_td2">
                            <input type="text" name="pack_every_grosswt" class="kctm_input" placeholder="每箱毛重" readonly>
                        </td>
                    </tr>
                    <tr>
                        <td class="ssl_td1">每箱净重</td>
                        <td class="ssl_td2">
                            <input type="text" name="pack_every_netwt" class="kctm_input" placeholder="每箱净重" readonly>
                        </td>
                    </tr>
                    <tr>
                        <td class="ssl_td1">装箱毛重</td>
                        <td class="ssl_td2">
                            <input type="text" name="pack_grosswt" class="kctm_input" placeholder="装箱毛重" readonly>
                        </td>
                    </tr>
                    <tr>
                        <td class="ssl_td1">装箱净重</td>
                        <td class="ssl_td2">
                            <input type="text" name="pack_netwt" class="kctm_input" placeholder="装箱净重" readonly>
                        </td>
                    </tr>
                </table>
                <div class="ssl_card_header">装箱体积</div>
                <table class="ssl_table">
                    <tr>
                        <td class="ssl_td1">商品长度</td>
                        <td class="ssl_td2">
                            <input type="text" name="g_length" class="kctm_input" placeholder="商品长度" disabled>
                            <span style="display: inline-block;margin-left:5px;">m</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="ssl_td1">商品宽度</td>
                        <td class="ssl_td2">
                            <input type="text" name="g_width" class="kctm_input" placeholder="商品宽度" disabled>
                            <span style="display: inline-block;margin-left:5px;">m</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="ssl_td1">商品高度</td>
                        <td class="ssl_td2">
                            <input type="text" name="g_height" class="kctm_input" placeholder="商品高度" disabled>
                            <span style="display: inline-block;margin-left:5px;">m</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="ssl_td1">商品体积</td>
                        <td class="ssl_td2">
                            <input type="text" name="pack_every_netwt" class="kctm_input" placeholder="商品体积" readonly>
                            <span style="display: inline-block;margin-left:5px;">m³</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="ssl_td1"><span class="required">*</span>每箱长度</td>
                        <td class="ssl_td2">
                            <input type="number" name="pack_every_length" class="kctm_input" placeholder="每箱长度">
                            <span style="display: inline-block;margin-left:5px;">m</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="ssl_td1"><span class="required">*</span>每箱宽度</td>
                        <td class="ssl_td2">
                            <input type="number" name="pack_every_width" class="kctm_input" placeholder="每箱宽度">
                            <span style="display: inline-block;margin-left:5px;">m</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="ssl_td1"><span class="required">*</span>每箱高度</td>
                        <td class="ssl_td2">
                            <input type="number" name="pack_every_height" class="kctm_input" placeholder="每箱高度">
                            <span style="display: inline-block;margin-left:5px;">m</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="ssl_td1">每箱体积</td>
                        <td class="ssl_td2">
                            <input type="text" name="pack_every_height" class="kctm_input" placeholder="每箱体积" readonly>
                            <span style="display: inline-block;margin-left:5px;">m³</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="ssl_td1"><span class="required">*</span>总装长度</td>
                        <td class="ssl_td2">
                            <input type="number" name="pack_length" class="kctm_input" placeholder="总装长度">
                            <span style="display: inline-block;margin-left:5px;">m</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="ssl_td1"><span class="required">*</span>总装宽度</td>
                        <td class="ssl_td2">
                            <input type="number" name="pack_width" class="kctm_input" placeholder="总装宽度">
                            <span style="display: inline-block;margin-left:5px;">m</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="ssl_td1"><span class="required">*</span>总装高度</td>
                        <td class="ssl_td2">
                            <input type="number" name="pack_height" class="kctm_input" placeholder="总装高度">
                            <span style="display: inline-block;margin-left:5px;">m</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="ssl_td1">总装体积</td>
                        <td class="ssl_td2">
                            <input type="text" name="pack_volume" class="kctm_input" placeholder="总装体积" readonly>
                            <span style="display: inline-block;margin-left:5px;">m³</span>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="kasj_db_but">
                <button onclick="add_pack(this);" type="button" class="tj_zuotms" style="border: unset;background: #3399fe;color: #fff;line-height: 1.8rem;font-size: 0.65rem;">增加货物信息</button>
                <button onclick="reback(this);" type="button" class="tj_zuotms prev" style="border: unset;background: #3399fe;color: #fff;line-height: 1.8rem;font-size: 0.65rem;display: none;margin-top:5px;">返回上一页</button>
                <button onclick="go(this);" type="button" class="tj_zuotms next" style="border: unset;background: #3399fe;color: #fff;line-height: 1.8rem;font-size: 0.65rem;display: none;margin-top:5px;">返回下一页</button>
            </div>
        </div>
    </form>


    <!--结束------------------------------------------>

    <div class="kasj_db_but " id="next_btn" style="display: none;"> <a href="javascript:void(0);" class="tj_zuotms" onclick="confirmInfo();">下一步</a> </div>
    <div class="kasj_db_but " id="confirm_btn" style="display: none;"> <a href="javascript:void(0);" class="tj_zuotm">确认</a> </div>
    <div class="footer">
        <img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/logo.png" alt=""> &nbsp;&nbsp;技术支持
    </div>
</div>
<script type="text/javascript" src="../addons/sz_yi/template/mobile/default/enterprise/static/js/hui.js" charset="UTF-8"></script>
<script type="text/javascript" src="../addons/sz_yi/template/mobile/default/enterprise/static/js/hui-form.js" charset="UTF-8"></script>
<!--<script type="text/javascript" src="../addons/sz_yi/template/mobile/default/enterprise/static/js/hui-accordion.js"></script>-->
<script type="text/javascript" src="../addons/sz_yi/template/mobile/default/enterprise/static/js/hui-tab.js" type="text/javascript"></script>
<script type="text/javascript">
    hui.formInit();
    //	hui.accordion(true, true);
    // hui.tab('.hui-tab');

    //增加货物信息
    var times = 1;
    var page = 0;//记录当前在第几页
    function add_pack(thi){
        var html = $('.pack:last').clone().attr('id','pack'+times);
        $('.pack:last').after(html);
        $('.pack:last').prev().css('display','none');
        $('.prev').show();
        var classs = new Array('currency','goods_name','price','grosswt','specs','netwt','pack_every_price','pack_amount','g_grosswt','g_netwt','pack_every_grosswt','pack_every_netwt','pack_grosswt','pack_netwt','g_length','g_width','g_length','g_volume','pack_every_length','pack_every_width','pack_every_height','pack_every_volume','pack_length','pack_width','pack_height','pack_volume');
        for(var i=0;i<classs.length;i++){
            $('.pack:last').children().find('input[name="'+classs[i]+'"]').val("");
        }
        $('.pack:last').children().find('.pack_num').val("");
        $('.pack:last').children().find('.pack_num_total').val("");
        $('.pack:last').children().find('.bizhong').text("");
        page = times-1;//1-1~0,2-1~1,3-1~2
        times+=1;
    }

    //返回上一页
    function reback(thi){
        if(page<=0){
            $('.prev').hide();
            $('.next').show();
        }else{
            $('.prev').show();
            $('.next').hide();
        }
        $('#pack'+page).show().next().hide();
    }

    //回到上一页
    function go(thi){
        var page2 = parseInt(page)+1;
        if(page2==times){
            $('.prev').show();
            $('.next').hide();
        }else{
            $('.prev').hide();
            $('.next').show();
        }
        $('#pack'+page2).show().prev().hide();
    }


    function confirmInfo() {
        // 判断是否确认
        var queren1 = $("#queren1").val();
        var queren2 = $("#queren2").val();
        var queren3 = $("#queren3").val();
        var queren4 = $("#queren4").val();

        if( !queren1 )
        {
            alert('请先确认工商照面信息!');
            return false;
        }
        if( !queren2 )
        {
            alert('请先确认受益人信息!');
            return false;
        }
        if( !queren3 )
        {
            alert('请先确认纳税人类型信息!');
            return false;
        }
        if( !queren4 )
        {
            alert('请先确认海关登记信息!');
            return false;
        }
        // 储存企业信息
        var d = {};
        $("#form1 input").each(function(i, el) {
            d[el.name] =$(this).val();
        });

        $("#infos").addClass('hide');
        //$("#question").removeClass('hide');
        $("#next_btn").hide();
        $("#confirm_btn").show();

        // var finish_url = '<?php  echo $this->createMobileUrl("enterprise/finish")?>';
        //2021-09-24
        var finish_url = 'http://shop.gogo198.cn/app/index.php?i=3&c=entry&p=finish&do=enterprise&m=sz_yi';
        hui.ajax({
            url  : 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&p=confirm&do=enterprise&m=sz_yi&op=save_enterprise_info',
            type : 'POST',
            data : d,
            beforeSend : function(){hui.loading();},
            complete   : function(){hui.closeLoading();},
            success : function(res){
                console.log(res);
                alert(res.result.msg);
                if(res.status == 1)
                {
                    // $(".qiye").show();
                    // $("#next_btn").hide();
                    location.href = finish_url;
                }
            },
            error : function(e){
                alert(JSON.stringify(e));
                console.log(JSON.stringify(e));
            },
            'backType' : "JSON"
        });
    }

    function queryInfo(obj) {
        $(obj).attr('disabled','disabled');
        $("#searchBtn").addClass('dis');
        $(obj).addClass('dis');
        var enterprise_name = hui("#enterprise_name").val();
        if( enterprise_name == '' )
        {
            $(obj).removeAttr('disabled');
            $("#searchBtn").removeClass('dis');
            $(obj).removeClass('dis');
            alert('请输入企业全名或企业注册号！');
            return false;
        }
        // 工商照面
        $.ajax({
            url  : 'http://declare.gogo198.cn/api/qxbquery/getBasicInfo?name='+enterprise_name,
            type : 'GET',
            success : function(res){
                alert(res.message);
                if(res.status == 200)
                {
                    if(res.data.new_status=='注销' || res.data.new_status=='吊销' || res.data.new_status=='撤销' || res.data.new_status=='设立中' || res.data.new_status=='清算中' || res.data.new_status=='停业' || res.data.new_status=='其他'){
                        alert('你的企业状态为'+res.data.new_status+'，暂不能注册！如有问题请咨询客服。');return false;
                    }
                    if(res.data.status=='注销' || res.data.status=='吊销' || res.data.status=='撤销' || res.data.status=='设立中' || res.data.status=='清算中' || res.data.status=='停业' || res.data.status=='其他'){
                        alert('你的企业状态为'+res.data.status+'，暂不能注册！如有问题请咨询客服。');return false;
                    }
                    $(obj).removeAttr('disabled');
                    $("#searchBtn").removeClass('dis');
                    $(obj).removeClass('dis');
                    $("#title_text").text('请核对工商照面信息');
                    $(".qiye").show();
                    $("#searchBtn").hide();
                    //$("#next_btn").show();
                    $("#enterprise_id").val(res.data.id);
                    $("#name").val(res.data.name);
                    $("#econKind").val(res.data.econKind);
                    $("#econKindCode").val(res.data.econKindCode);
                    $("#registCapi").val(res.data.registCapi);
                    $("#currency_unit").val(res.data.currency_unit);
                    $("#type_new").val(res.data.type_new);
                    $("#historyNames").val(res.data.historyNames);
                    $("#address").val(res.data.address);
                    $("#regNo").val(res.data.regNo);
                    $("#scope").val(res.data.scope);
                    $("#termStart").val(res.data.termStart);
                    $("#termEnd").val(res.data.termEnd);
                    $("#belongOrg").val(res.data.belongOrg);
                    $("#operName").val(res.data.operName);
                    $("#startDate").val(res.data.startDate);
                    $("#endDate").val(res.data.endDate);
                    $("#checkDate").val(res.data.checkDate);
                    $("#status").val(res.data.status);
                    $("#orgNo").val(res.data.orgNo);
                    $("#creditNo").val(res.data.creditNo);
                    $("#districtCode").val(res.data.districtCode);
                    $("#domain").val(res.data.domain);
                }else{
                    $(obj).removeAttr('disabled');
                    $("#searchBtn").removeClass('dis');
                    $(obj).removeClass('dis');
                }
            },
            error : function(e){
                console.log(JSON.stringify(e));
                // alert(JSON.stringify(e));
                alert('查询失败');
            },
            'backType' : "JSON"
        });
    }
</script>

</body>
</html>