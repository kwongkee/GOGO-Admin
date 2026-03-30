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
    .induce{background:#3388FF;font-size:14px;padding:4px 10px;width:fit-content;margin-left:10px;margin-right:5px;color:#fff;}
    a{text-decoration: none;text-underline: none;}
</style>

<link type="text/css" rel="stylesheet" href="../addons/sz_yi/template/pc/default/static/css/bootstrap.min.css" />
<link type="text/css" rel="stylesheet" href="../addons/sz_yi/static/travel_express/css/hui.css" />
<!--<script type="text/javascript" src="../addons/sz_yi/template/mobile/default/enterprise/static/js/hui.js" charset="UTF-8"></script>-->
<link href="../addons/sz_yi/static/css/layui.css" rel="stylesheet">

<div class="page_head">
    <div class="left">
        <div class="back"></div>
        <div style="font-size:15px;padding-top:2px;">返回</div>
    </div>
    <div class="right">
        税费列表
    </div>
</div>
<div id="container">
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header" style="padding:0 6px;">
                        <div id="toolbar" class="btn-group" style="display:flex;align-items:center;">
                            <input type="text" name="ie_date" class="layui-input" id="ie_date" placeholder="请选择月份搜索" value="<?php  echo $_GPC['date'];?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="hui-segment" id="cate" style="margin-top:14px;">
        <a href="javascript:changeDom(1);" data-i="1" class="hui-segment-active">待确认</a>
        <a href="javascript:changeDom(2);" data-i="2">已确认</a>
    </div>
    <div class="info_main table-responsive main_list">
        <?php  if(empty($list)) { ?>
        <div class="line" style="text-align:center;">暂无信息</div>
        <?php  } else { ?>

        <?php  } ?>
    </div>
</div>

<script type="text/javascript" src="../addons/sz_yi/static/js/layui/layui.js"></script>
<script language="javascript">
    $(function(){
        layui.use(['layer', 'form', 'table', 'upload', 'laydate'], function () {
            var $ = layui.$
                , layer = layui.layer
                , form = layui.form
                , element = layui.element
                , laydate = layui.laydate
                , upload = layui.upload;

            // form.render(null, 'component-form-element1');

            laydate.render({
                elem: '#ie_date'
                , format: 'yyyy-MM'
                ,type:'month'
                ,max: "new Date()"
                ,done: function(value, date, endDate){
                    changeDom($('.hui-segment-active').data('i'),value);
                    window.location.replace('./index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&op=tax_list&date='+value);
                }
            });
        });
    });

    require(['tpl', 'core'], function(tpl, core) {
        $('.page_head').find('.left').click(function(){
            window.history.back(-1);
        });
        $('.mask').click(function(){
            $('.sign_box').hide();
            $('.mask').hide();
            $('#batch_id').val('');
            $('#col_1').val('');$('#col_2').val('');$('#col_3').val('');$('#pay_1').val('');$('#pay_2').val('');$('#pay_3').val('');
        });
        changeDom(1,"<?php  echo $_GPC['date'];?>");
    });

    function opera_queren(t,id){
        let selected = $(t).val();
        if(selected==1){
            //对账无误
            operation(id,3);
        }else if(selected==2){
            //对账有误
            operation(id,4);
        }else if(selected==3){
            //查看凭证
            watch_voucher("./index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&op=watch_voucher&id="+id);
        }else if(selected==4){
            //查看账单
            watch_voucher("./index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&op=watch_bill&id="+id);
        }
    }

    function watch_voucher(url){
        window.location.href=url;
    }

    //点击切换函数可以根据实际业务需求编写业务代码
    function changeDom(index,date){
        index--;
        if(date=='' || typeof(date)=='undefined'){
            date = $('#ie_date').val();
        }
        $.ajax({
            url:"<?php  echo $this->createMobileUrl('account/bookkeeping');?>",
            type:'POST',
            dataType:'json',
            data:{'op':'tax_list','typ':index+1,'date':date},
            success:function(json) {
                if(json.status==1){
                    var dat = json.result.data;
                    var html = '';
                    html += '<div class="psd" style="margin-top:10px;">\n' +
                        '<table class="table table-striped table-bordered" style="margin-bottom:0;table-layout:fixed;word-break: break-all;">\n'+
                        '<thead>\n'+
                        '<tr>\n' +
                        '<td>商户名称</td>\n'+
                        '<td>申报日期</td>\n'+
                        '<td style="width:140px;">操作</td>\n'+
                        '</tr>\n'+
                        '</thead>\n';
                    //待对账
                    var isshow=0;
                    for(var i=0;i<dat.length;i++){
                        if(dat[i]['id']>0){
                            isshow=1;
                            html += '<tr>\n' +
                                '<td  style="width:100%; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;">'+dat[i].user_name+'</td>\n'+
                                '<td>'+dat[i].decl_date+'</td>\n'+
                                '<td>';
                            html += '<a class="see" href="https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=taxes_info&is_accounting=1&id='+dat[i]['id']+'">查看</a>';
                            if(dat[i]['status']==2){
                                html += '<a class="see" style="background:#ff2222;margin-left:10px;" href="https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&op=tax_declare_edit&id='+dat[i]['id']+'">修改</a>';
                            }

                            html+='</td>\n'+
                                '</tr>\n';

                        }
                    }
                    if(isshow==0){
                        html += '<tr><td colspan="3" align="center">暂无信息</td></tr>';
                    }
                    html += '</table>\n'+
                        '</div>';
                    $('.main_list').html(html);
                    $('#cate a').eq(index).addClass('hui-segment-active').siblings().removeClass('hui-segment-active');
                }
            },error:function(json){
                alert('数据出错！');
            }
        });
    }

    function operation(id,typ){
        if(typ==3){
            //对账无误
            if(confirm('确认对账无误？')){
                $.ajax({
                    url:"<?php  echo $this->createMobileUrl('account/bookkeeping');?>",
                    type:'POST',
                    dataType:'json',
                    data:{'op':'reconciliation_detail','typ':typ,'batch_id':id},
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
            $('#batch_id').val(id);
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
                alert('请输入已开票，应收款金额和币种');return;
            }
            if($('#col_2').isEmpty()){
                alert('请输入已开票，未收款金额和币种');return;
            }
            if($('#col_3').isEmpty()){
                alert('请输入未开票，预收款金额和币种');return;
            }
            if($('#pay_1').isEmpty()){
                alert('请输入已收票，应付款金额和币种');return;
            }
            if($('#pay_2').isEmpty()){
                alert('请输入已收票，未付款金额和币种');return;
            }
            if($('#pay_3').isEmpty()){
                alert('请输入未收票，预付款金额和币种');return;
            }
            $.ajax({
                url:"<?php  echo $this->createMobileUrl('account/bookkeeping');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'reconciliation_detail','typ':4,'batch_id':batch_id,'col_1':col_1,'col_2':col_2,'col_3':col_3,'pay_1':pay_1,'pay_2':pay_2,'pay_3':pay_3},
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

    //对账归纳
    function induce(merch_id){
        window.location.href="./index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&op=reconciliation_induce&id="+merch_id;
    }
</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>