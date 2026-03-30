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

    .see{box-sizing: border-box;padding: 4px 8px;font-size: 14px;color: #fff;background: #1E9FFF;text-align:center;}
    .chakan{font-size:14px;padding:4px 10px;background:#1EA01E;width:fit-content;color:#fff;}
    .submit{font-size:14px;padding:4px 10px;background:#ff5555;width:fit-content;margin-left:10px;margin-right:5px;color:#fff;}
    .sign_box,.download_box{display:none;width: 80%;position: absolute;background: #ffffff;border-radius: 5px;z-index:1000;transform:translate(-50%,-50%);top: 50%;left:50%;}
    .download_box .pic_div{height: 200px;max-height: 200px;min-height: 200px;overflow: scroll;padding:4px;width:100%;text-align:center;}
    .sign_box .confirm,.download_box .confirm{height: 50px;border-top: 1px solid #eee;display: flex;}
    .sign_box .confirm>span,.download_box .confirm>span{flex: 1;height: 50px;line-height: 50px;font-size: 16px;text-align: center;}
    .sign_box .confirm>span:nth-child(1),.download_box .confirm>span:nth-child(1){color: red;}
    .sign_box .confirm>span:nth-child(2),.download_box .confirm>span:nth-child(2){border-left: 1px solid #eee;}
    .sign_box .important,.download_box .important{color: red;}
    .sign_box .title,.download_box .title{text-align: center;border-bottom: 1px solid #eee;margin-bottom: 10px;}
    .sign_box .title>p,.download_box .title>p{height: 40px;line-height: 40px;text-align: center;font-size: 18px;font-weight: bold;}
    
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

<div class="page_head">
    <div class="left">
        <div class="back"></div>
        <div style="font-size:15px;padding-top:2px;">返回</div>
    </div>
    <div class="right">
        凭证批次号[<?php  echo $list['batch_num'];?>]
    </div>
</div>
<div id="container">
    <div class="info_main table-responsive main_list">
        <?php  if(empty($list)) { ?>
            <div class="line" style="text-align:center;">暂无信息</div>
        <?php  } else { ?>
           <table class="table table-striped caozuo">
               <thead>
                   <tr>
                       <td>登记编号</td>
                       <td>凭证类别</td>
                       <td>凭证状态</td>
                       <td style="width:25%;">操作</td>
                   </tr>
               </thead>
                <?php  if(is_array($list['voucher'])) { foreach($list['voucher'] as $val) { ?>
                    <tr>
                        <td><?php  echo $val['reg_number'];?></td>
                        <td>
                            <?php  if($val['type']==1) { ?>收款登记<?php  } ?>
                            <?php  if($val['type']==2) { ?>付款登记<?php  } ?>
                            <?php  if($val['type']==3) { ?>账单登记<?php  } ?>
                        </td>
                        <td>

                            <?php  if($val['status'] == -2) { ?>已撤回快递<?php  } ?>
                            <?php  if($val['status'] == -1) { ?>已不予记账<?php  } ?>
                            <?php  if($val['status'] == 1) { ?>已汇总，待审核<?php  } ?>
                            <?php  if($val['status'] == 2) { ?>可记账无需复核<?php  } ?>
                            <?php  if($val['status'] == 3) { ?>不可记账待复核<?php  } ?>
                            <?php  if($val['status'] == 4) { ?>已记账<?php  } ?>
                            <?php  if($val['status'] == 5) { ?>复核已提交<?php  } ?>
                            <?php  if($val['status'] == 6) { ?>凭证未签收<?php  } ?>
                            <?php  if($val['status'] == 7) { ?>凭证已签收<?php  } ?>
                            <?php  if($val['status'] == 8) { ?>已对账<?php  } ?>
                            <?php  if($val['status'] == 9) { ?>对账有误<?php  } ?>
                            <?php  if($val['status'] == 10) { ?>已报税<?php  } ?>
                            <?php  if($val['status'] == 11) { ?>报税有误<?php  } ?>
                        </td>
                        <td>
                            <select name="book_queren" onchange="book_queren(this,<?php  echo $val['id'];?>,<?php  echo $val['type'];?>)">
                                <option value="">请选择</option>
                                <?php  if($val['status']==3) { ?>
                                <option value="1" data-typ="<?php  echo $val['type'];?>">修改补充</option>
                                <option value="2">不予记账</option>
                                <?php  } ?>
                                <?php  if($val['status']==6) { ?>
                                <option value="3">撤回快递</option>
                                <?php  } ?>
                                <option value="4">查看凭证</option>
                                <option value="5">下载凭证</option>
                            </select>
                        </td>
                    </tr>
                <?php  } } ?>
            </table>
        <?php  } ?>
    </div>
</div>
<div class="mask" style="position: fixed; margin: 0px; padding: 0px;background: rgba(0, 0, 0,0.6); z-index: 999; width: 100%; height: 100%; transition: all 0.2s ease 0s;display:none;left: 0;top: 0;"></div>
<div class="download_box">

</div>
<div id="cs" style="display:none;"></div>
<script language="javascript">
    require(['tpl', 'core'], function(tpl, core) {
        $('.page_head').find('.left').click(function(){
            window.history.back(-1);
        });
        $('.mask').click(function(){
            $('.download_box').hide();
            $('.mask').hide();
        });
    });
    //记账确认
    function book_queren(t,id,typ){
        let selected = $(t).val();
        if(selected==1){
            //修改补充
            let typ = $(t).children(':selected').data('typ');
            see(id,typ);
        }else if(selected==2){
            //不予记账
            nobook(id);
        }else if(selected==3){
            //撤回快递
            withdraw_express(id);
        }else if(selected==4){
            //查看凭证
            window.location.href='./index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=voucher_info&id='+id;
        }else if(selected==5){
            //下载凭证
            down(id,typ);
        }
    }

    //下载
    function down(id,typ){
        //下载凭证文件
        $('.mask').show();
        $('.download_box').show();
        $('#down_id').val(id);

        $.ajax({
            url:"<?php  echo $this->createMobileUrl('account/bookkeeping');?>",
            type:'POST',
            dataType:'json',
            data:{'op':'get_voucher_info','id':id},
            success:function(json) {
                if(json.status==1){
                    let data = json.result.data;
                    var html = '';
                    if(typ==3){
                        if(typeof(data) != 'undefined' || data != ''){
                            html += '<div class="title">\n' +
                                '        <p class="important">账单下载</p>\n' +
                                '    </div>\n'+
                                '        <div class="pic_div">\n';
                            for(var i=0;i<data['bill_file'].length;i++) {
                                html += '<div style="margin:0 10px 10px 0;box-shadow: 0px 0px 4px 0px #999;display:inline-block;">\n' +
                                    '<a href="https://shop.gogo198.cn/attachment/'+data['bill_file'][i]+'" download="'+data['bill_file_down'][i]+'">\n' +
                                    '<img src="https://shop.gogo198.cn/attachment/'+data['bill_file'][i]+'" style="width:100px;height:100px;" onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png" >\n' +
                                    '<p style="text-align:center;margin:0;">点击下载</p>\n' +
                                    '</a>\n' +
                                    '</div>\n';
                            }
                            html += '        </div>\n' +
                                '<div class="confirm">\n' +
                                '        <span class="close-but" onClick="fnClose2(1)">关闭</span>\n';
                            //管理员
                            if(data['guanli_batch_down']==0 && data['identify']==1){
                                html += '        <span class="close-but" onClick="fnClose2(2,'+data['id']+','+data['guanli_batch_down']+','+data['identify']+')">批量下载</span>\n';
                            }else if(data['guanli_batch_down']>0 && data['identify']==1){
                                html += '        <span class="close-but" style="color:#fff;background:#cac8c8;" onClick="fnClose2(2,'+data['id']+','+data['guanli_batch_down']+','+data['identify']+')">批量下载</span>\n';
                            }
                            //会计
                            if(data['kuaiji_batch_down']==0 && data['identify']==2){
                                html += '        <span class="close-but" onClick="fnClose2(2,'+data['id']+','+data['kuaiji_batch_down']+','+data['identify']+')">批量下载</span>\n';
                            }else if(data['kuaiji_batch_down']>0 && data['identify']==2){
                                html += '        <span class="close-but" style="color:#fff;background:#cac8c8;" onClick="fnClose2(2,'+data['id']+','+data['kuaiji_batch_down']+','+data['identify']+')">批量下载</span>\n';
                            }
                            //商户
                            if(data['qiye_batch_down']==0 && data['identify']==3){
                                html += '        <span class="close-but" onClick="fnClose2(2,'+data['id']+','+data['qiye_batch_down']+','+data['identify']+')">批量下载</span>\n';
                            }else if(data['qiye_batch_down']>0 && data['identify']==3){
                                html += '        <span class="close-but" style="color:#fff;background:#cac8c8;" onClick="fnClose2(2,'+data['id']+','+data['qiye_batch_down']+','+data['identify']+')">批量下载</span>\n';
                            }

                            html += '    </div>';
                            $('.download_box').html(html);
                        }
                    }else if(typ==1 || typ==2){
                        if(typeof(data) != 'undefined' || data != ''){
                            html += '<div class="title">\n' +
                                '        <p class="important">凭证下载</p>\n' +
                                '    </div>\n'+
                                '        <div class="pic_div">\n';
                                if(data['method']==1){
                                    //人工汇总
                                    for(var j=0;j<data['voucher_typeName'].length;j++){
                                        html+='<p style="text-align: left;font-size: 15px;border: 1px solid #00000059;">'+data['voucher_typeName'][j]+'</p>';
                                    
                                        // for(var i=0;i<data['express_voucher'].length;i++) {
                                            for(var i2=0;i2<data['express_voucher'][j].length;i2++){
                                                html += '<div style="margin:0 10px 10px 0;box-shadow: 0px 0px 4px 0px #999;display:inline-block;">\n' +
                                                '<a href="https://shop.gogo198.cn/attachment/'+data['express_voucher'][j][i2]+'" download="'+data['express_voucher_down'][j][i2]+'">\n' +
                                                '<img src="https://shop.gogo198.cn/attachment/'+data['express_voucher'][j][i2]+'" style="width:100px;height:100px;" onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png" >\n'+
                                                '<p style="text-align:center;margin:0;">点击下载</p>\n' +
                                                '</a>\n' +
                                                '</div>\n';   
                                            }
                                        // }
                                    }
                                }else{
                                    for(var i=0;i<data['express_voucher'].length;i++) {
                                        html += '<div style="margin:0 10px 10px 0;box-shadow: 0px 0px 4px 0px #999;display:inline-block;">\n' +
                                            '<a href="https://shop.gogo198.cn/attachment/'+data['express_voucher'][i]+'" download="'+data['express_voucher_down'][i]+'">\n' +
                                            '<img src="https://shop.gogo198.cn/attachment/'+data['express_voucher'][i]+'" style="width:100px;height:100px;" onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png" >\n'+
                                            '<p style="text-align:center;margin:0;">点击下载</p>\n' +
                                            '</a>\n' +
                                            '</div>\n';
                                    }                    
                                }
                
                            html += '        </div>\n' +
                                '<div class="confirm">\n' +
                                '        <span class="close-but" onClick="fnClose2(1)">关闭</span>\n';
                            //管理员
                            if(data['guanli_batch_down']==0 && data['identify']==1){
                                html += '        <span class="close-but" onClick="fnClose2(2,'+data['id']+','+data['guanli_batch_down']+','+data['identify']+')">批量下载</span>\n';
                            }else if(data['guanli_batch_down']>0 && data['identify']==1){
                                html += '        <span class="close-but" style="color:#fff;background:#cac8c8;" onClick="fnClose2(2,'+data['id']+','+data['guanli_batch_down']+','+data['identify']+')">批量下载</span>\n';
                            }
                            //会计
                            if(data['kuaiji_batch_down']==0 && data['identify']==2){
                                html += '        <span class="close-but" onClick="fnClose2(2,'+data['id']+','+data['kuaiji_batch_down']+','+data['identify']+')">批量下载</span>\n';
                            }else if(data['kuaiji_batch_down']>0 && data['identify']==2){
                                html += '        <span class="close-but" style="color:#fff;background:#cac8c8;" onClick="fnClose2(2,'+data['id']+','+data['kuaiji_batch_down']+','+data['identify']+')">批量下载</span>\n';
                            }
                            //商户
                            if(data['qiye_batch_down']==0 && data['identify']==3){
                                html += '        <span class="close-but" onClick="fnClose2(2,'+data['id']+','+data['qiye_batch_down']+','+data['identify']+')">批量下载</span>\n';
                            }else if(data['qiye_batch_down']>0 && data['identify']==3){
                                html += '        <span class="close-but" style="color:#fff;background:#cac8c8;" onClick="fnClose2(2,'+data['id']+','+data['qiye_batch_down']+','+data['identify']+')">批量下载</span>\n';
                            }
                            html += '    </div>';
                            $('.download_box').html(html);
                        }
                    }

                    $('.download_box').show();
                    $('.mask').show();
                }
            }
        });
    }

    function fnClose2(typ,id,down_times,identify){
        if(typ==1){
            $('.download_box').hide();
            $('.mask').hide();
        }else if(typ==2){
            //批量下载
            let voucher_id = id;
            if(down_times>=1 && confirm('您好！该文件已经下载'+down_times+'次，为免单据混乱，请确认是否再次下载？')){
                downVoucher(voucher_id,identify);
            }

            if(down_times==0){
                downVoucher(voucher_id,identify);
            }
        }
    }

    function downVoucher(voucher_id,identify){
        let op = 'download_voucher_info';
        let url = "<?php  echo $this->createMobileUrl('account/bookkeeping');?>";

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: {'op': op, 'id': voucher_id,'identify':identify},
            success: function (json) {
                if (json.status == 1) {
                    if ((navigator.userAgent.match(/(phone|pad|pod|iPhone|iPod|ios|iPad|Android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone)/i))) {
                        //     //手机
                        alert('请到手机浏览器打开!');
                        xs(json.result.data);
                    }
                    // else{
                    //PC
                    // }
                    window.location.href=json.result.data;
                }
            }
        });
    }

    function xs(data){
        var ordersn2 = data;
        document.getElementById("cs").innerHTML=ordersn2;
        if (navigator.userAgent.match(/(iPhone|iPod|iPad);?/i)) {//区分iPhone设备
            window.getSelection().removeAllRanges();//这段代码必须放在前面否则无效
            // var Url2=document.getElementById("biaoios");//要复制文字的节点
            var Url2=document.getElementById("cs");//要复制文字的节点
            // alert(Url2);
            var range = document.createRange();
            // 选中需要复制的节点
            range.selectNode(Url2);
            // 执行选中元素
            window.getSelection().addRange(range);
            // 执行 copy 操作
            var successful = document.execCommand('copy');
            // alert(successful);
            // 移除选中的元素
            window.getSelection().removeAllRanges();
        }else{
            //先copy
            var ordersn = data;
            var oInput = document.createElement('input');
            oInput.value = ordersn;
            oInput.style.opacity="0";
            document.body.appendChild(oInput);
            oInput.select(); // 选择对象
            var valueLength = ordersn.length;
            document.execCommand("Copy"); // 执行浏览器复制命令
        }
    }

    //编辑
    function see(id,typ) {
        if(typ==3){
            //对账单
            window.location.href="./index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=bill_edit&id="+id+'&type='+typ;
        }else{
            window.location.href="./index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=reg_edit&id="+id+'&type='+typ;
        }
    }

    //不予记账
    function nobook(id){
        if(confirm('确认此凭证不予记账吗？')){
            $.ajax({
                url: "<?php  echo $this->createMobileUrl('account/register');?>",
                type: 'POST',
                dataType: 'json',
                data: {'op': 'voucher_manage','id':id,'type':10},
                success: function (json) {
                    if (json.status == 1) {
                        alert(json.result.msg);
                        changeDom(3);
                    }
                }
            });
        }
    }

    //撤回快递
    function withdraw_express(id){
        if(confirm('确定撤回快递吗？')){
            $.ajax({
                url: "<?php  echo $this->createMobileUrl('account/register');?>",
                type: 'POST',
                dataType: 'json',
                data: {'op': 'voucher_manage','id':id,'type':8},
                success: function (json) {
                    if (json.status == 1) {
                        alert(json.result.msg);
                        changeDom(3);
                    }
                }
            });
        }
    }

</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>