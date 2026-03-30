<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<!--<title>项目配置中心</title>-->
<style type="text/css">
    body {margin:0px; background:#efefef; font-family:'微软雅黑'; -moz-appearance:none;}
    .info_main {height:auto;  background:#fff; margin-top:14px; border-bottom:1px solid #e8e8e8; border-top:1px solid #e8e8e8;}
    .info_main .line {margin:0 10px; height:40px; border-bottom:1px solid #e8e8e8; line-height:40px; color:#999;}
    .info_main .line .title {height:40px; width:80px; line-height:40px; color:#444; float:left; font-size:14px;}
    .info_main .line .info { width:100%;float:right;margin-left:-80px; }
    .info_main .line .inner { margin-left:80px; }
    .info_main .line .inner input {height:40px; width:100%;display:block; padding:0px; margin:0px; border:0px; float:left; font-size:14px;}
    .info_main .line .inner .user_sex {line-height:40px;}
    .info_sub {height:44px; margin:14px 5px; background:#31cd00; border-radius:4px; text-align:center; font-size:16px; line-height:44px; color:#fff;}
    .select { border:1px solid #ccc;height:25px;}
    .table{width:100%;font-size:15px;}
    .table tr td{
        vertical-align: middle !important;
    }
    .table tr{height:25px;}
    /*.table tr td:nth-of-type(1){width:88%;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;}*/
    .table tr td:nth-of-type(2){width:20%;}
    .see{box-sizing: border-box;padding: 2px 4px;font-size: 15px;color: #fff;background: #1E9FFF;text-align:center;}
    .opera2{box-sizing: border-box;padding: 2px 4px;font-size: 15px;color: #fff;background: #1EA01E;text-align:center;display:none;margin-top:10px;}
    .cancel{font-size:14px;padding:4px 10px;background:#ff5555;border-radius:5px;width:fit-content;margin-left:20px;color:#fff;display:none;}
    .submit{font-size:14px;padding:4px 10px;background:#1E9FFF;width:fit-content;margin-left:10px;margin-right:5px;color:#fff;}
    .noAdd{background:#ff5555;}
    /**凭证签收**/
    .sign_box{display:none;width: 80%;position: absolute;background: #ffffff;border-radius: 5px;z-index:1000;transform:translate(-50%,-50%);top: 50%;left:50%;}
    .sign_box .confirm{height: 50px;border-top: 1px solid #eee;display: flex;}
    .sign_box .confirm>span{flex: 1;height: 50px;line-height: 50px;font-size: 16px;text-align: center;}
    .sign_box .confirm>span:nth-child(1){color: red;}
    .sign_box .confirm>span:nth-child(2){border-left: 1px solid #eee;}
    .sign_box .important{color: red;}
    .sign_box .title{text-align: center;border-bottom: 1px solid #eee;margin-bottom: 10px;}
    .sign_box .title>p{height: 40px;line-height: 40px;text-align: center;font-size: 18px;font-weight: bold;}
    /**电邮地址**/
    .send_express,.submitMethod_box,.download_box{display:none;width: 80%;position: absolute;background: #ffffff;border-radius: 5px;z-index:1000;transform:translate(-50%,-50%);top: 50%;left:50%;}
    .download_box .pic_div{height: 200px;max-height: 200px;min-height: 200px;overflow: scroll;padding:4px;width:100%;text-align:center;}
    .confirm{height: 50px;border-top: 1px solid #eee;display: flex;}
    .confirm>span{flex: 1;height: 50px;line-height: 50px;font-size: 16px;text-align: center;}
    .confirm>span:nth-child(1){color: red;}
    .confirm>span:nth-child(2){border-left: 1px solid #eee;}
    .title{text-align: center;border-bottom: 1px solid #eee;margin-bottom: 10px;}
    .title>p{height: 40px;line-height: 40px;text-align: center;font-size: 18px;font-weight: bold;}
    .important{color: red;}

    .page_head{width:100%;background:#fff;margin-bottom:5px;box-shadow:0 0 4px #ddd;padding:10px 0 10px;display:flex;align-items:center;}
    .page_head .left{width:20%;display:flex;align-items:center;font-size:15px;}
    .page_head .left .back{width:13px;height:13px;border-top:2px solid #000;border-left:2px solid #000;transform:rotate(-50deg);margin-left:15px;margin-right:5px;}
    .page_head .right{width:80%;text-align:center;padding-right:80px;font-size:15px;padding-top:2px;}
    .green{color:#1EA01E;}
    .red{color:#ff5555;}
    .blue{color:#3388FF;}
    #email1,#email2{border-bottom:1px solid #666;}
    a{text-decoration: none;text-underline: none;}
</style>

<link type="text/css" rel="stylesheet" href="../addons/sz_yi/template/pc/default/static/css/bootstrap.min.css" />
<script type="text/javascript" src="../addons/sz_yi/static/template/pc/default/static/js/bootstrap.min.js"></script>

<div id="container">
    <div class="page_head">
        <div class="left">
            <div class="back"></div>
            <div style="font-size:15px;padding-top:2px;">返回</div>
        </div>
        <div class="right">
            <?php  echo $list['batch_num'];?>
        </div>
    </div>
    <!--<div class="page_topbar">-->
    <!--    <div class="title">待寄快递</div>-->
    <!--</div>-->
    <div class="info_main table-responsive">
        <?php  if(empty($list)) { ?>
            <div class="line" style="text-align:center;">暂无信息</div>
        <?php  } else { ?>
            <input type="text" id="down_id" value="" style="display:none;">
            <div class="infos table-responsive">
                <div style="text-align:left;margin: 0;padding: 5px 0 5px 10px;background: #CDF;display: flex;align-items: center;justify-content: space-between;">
                    <div>汇总批次号：<?php  echo $list['batch_num'];?></div>
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <?php  if($list['is_sign']==1) { ?>
                        <!--<div class="see" onclick="sign(this)" style="width:100px;margin-right:5px;">凭证签收</div>-->
                        <!--<div class="cancel" onClick="cancel(this)">取消</div>-->
                        <div class="submit" onClick="submit(<?php  echo $list['id'];?>)">凭证签收(0)</div>
                        <?php  } ?>
                    </div>
                </div>
                <table class="table table-striped caozuo" style="table-layout:fixed;word-break: break-all;">
                    <thead>
                    <tr>
                        <td style="width:100px;max-width:100px;overflow: hidden;text-overflow: ellipsis;">收付标识</td>
                        <td>凭证类别</td>
                        <td>登记编号</td>
                        <td>随附凭证</td>
                        <td>操作</td>
                    </tr>
                    </thead>
                    <tbody>
                    <?php  if(is_array($list['voucher_info'])) { foreach($list['voucher_info'] as $kk => $vv) { ?>
                        <?php  if($vv['id']>0) { ?>
                        <tr>
                            <td class="<?php echo $vv['type']==1?'green':''?> <?php echo $vv['type']==2?'red':''?> <?php echo $vv['type']==3?'blue':''?>" >
                                <?php  if($vv['type']==1) { ?>收款<?php  } ?>
                                <?php  if($vv['type']==2) { ?>付款<?php  } ?>
                                <?php  if($vv['type']==3) { ?>对账单<?php  } ?>
                            </td>
                            <td>
                                <?php  if($vv['voucher_type']==1) { ?>发票<?php  } ?>
                                <?php  if($vv['voucher_type']==2) { ?>收据<?php  } ?>
                                <?php  if($vv['voucher_type']==3) { ?>形式<?php  } ?>
                                <?php  if(empty($vv['voucher_type'])) { ?>无<?php  } ?>
                            </td>
                            <td style="width:120px;max-width:120px;overflow: hidden;text-overflow: ellipsis;"><?php  echo $vv['reg_number'];?></td>
                            <td>
                                <?php  if($vv['attach_cert']>0) { ?>
                                    <?php  echo $vv['attach_cert'];?>张
                                <?php  } ?>
                                <?php  if(empty($vv['attach_cert'])) { ?>无需提交<?php  } ?>
                            </td>
                            <td>
                                <select name="opera_queren" id="opera_queren" onchange="opera_queren(this,<?php  echo $vv['id'];?>)">
                                    <option value="">请选择</option>
                                    <option value="1" data-typ="<?php  echo $vv['type'];?>">查看</option>
                                    <?php  if($vv['status']>=7) { ?>
                                    <option value="2" data-typ="<?php  echo $vv['type'];?>">下载</option>
                                    <?php  } ?>
                                    <?php  if($vv['is_showQian']==1) { ?>
                                    <option value="3">选择签收</option>
                                    <?php  } ?>
                                    <?php  if($vv['is_showQian']==0) { ?>
                                    <option value="4">已签收</option>
                                    <?php  } ?>
                                </select>
                                <!--<div class="see" onclick='go(<?php  echo $vv["id"];?>,<?php  echo $vv["type"];?>)' style="background:#1E9FFF;">查看</div>-->
                                <!--<div class="see" onclick='down(<?php  echo $vv["id"];?>,<?php  echo $vv["type"];?>)' id="down<?php  echo $vv['id'];?>" style="background:#125526;padding:4px 10px;margin-top:10px;">下载</div>-->
                                <!--<div class="opera2" onclick="add_voucher(this,<?php  echo $vv['id'];?>)" style="padding:4px 10px;">添加</div>-->
                                <!--<div class="opera2" style="padding:4px 10px;">已签收</div>-->
                            </td>
                        </tr>
                        <?php  } ?>
                    <?php  } } ?>
                    </tbody>
                </table>
            </div>

        <?php  } ?>
    </div>
</div>

<!--凭证签收-->
<div class="mask" style="position: fixed; margin: 0px; padding: 0px;background: rgba(0, 0, 0,0.6); z-index: 999; width: 100%; height: 100%; transition: all 0.2s ease 0s;display:none;left: 0;top: 0;"></div>
<div class="sign_box">
    <div class="title">
        <p class="important">凭证签收</p>
    </div>
    <input type="text" name="sign_id" id="sign_id" value="" style="display:none;"/>
    <input type="text" name="sign_id2" id="sign_id2" value="" style="display:none;"/>
    <p style="font-size:15px;text-align:center;">请根据实际情况选择凭证状态</p>
    <div class="confirm">
        <span class="close-but" onClick="fnClose(1)">拒绝签收</span>
        <span class="close-but" onClick="fnClose(2)">确认签收</span>
    </div>
</div>
<div class="download_box">

</div>

<script language="javascript">
    window.addEventListener('pageshow', function (e) {
        if(e.persisted || (window.performance && window.performance.navigation.type == 2)){
            var down_id = $('#down_id').val();

            if(down_id!='' && typeof(down_id)!='undefined'){
                document.getElementById('down'+down_id).click();
            }
        }
    });
    $(function(){
        $('.page_head').find('.left').click(function(){
              window.history.back(-1); 
        });
        $('.mask').click(function(){
            $('.sign_box').hide();
            $('.download_box').hide();
            $('.mask').hide();
        });
    });

    window.ids='';
    window.times=0;

    require(['tpl', 'core'], function(tpl, core) {
        
    })

    function opera_queren(t,id){
        let selected = $(t).val();
        if(selected==1){
            //查看
            go(id);
        }else if(selected==2){
            //下载
            let typ = $(t).children(':selected').data('typ');
            down(id,typ);
        }else if(selected==3){
            //选择签收
            add_voucher(t,id);
        }else if(selected==4){
            //已签收
            alert('该凭证已签收，无需重复签收！');return;
        }
    }

    function go(id,typ){
        //查看凭证信息
        window.location.href='./index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&op=voucher_info&id='+id;
    }

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
                                if ((navigator.userAgent.match(/(phone|pad|pod|iPhone|iPod|ios|iPad|Android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone)/i))) {
                                    //移动端
                                    html += '<div style="margin:0 10px 10px 0;box-shadow: 0px 0px 4px 0px #999;display:inline-block;">' +
                                        '<a href="https://shop.gogo198.cn/attachment/'+data['bill_file'][i]+'" target="_blank"><img src="https://shop.gogo198.cn/attachment/'+data['bill_file'][i]+'" style="width:100px;height:100px;" onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png" ><p style="text-align:center;margin:0;">点击保存</p></a>\n' +
                                        '</div>\n';
                                }else{
                                    //PC端
                                    html += '<div style="margin:0 10px 10px 0;box-shadow: 0px 0px 4px 0px #999;display:inline-block;"><a href="https://shop.gogo198.cn/attachment/'+data['bill_file'][i]+'" download="'+data.voucher_typeName+formatZero(i+1,2)+'"><img src="https://shop.gogo198.cn/attachment/'+data['bill_file'][i]+'" style="width:100px;height:100px;" onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png" ><p style="text-align:center;margin:0;">点击下载</p></a></div>\n';
                                }

                            }
                            html += '        </div>\n' +
                                '<div class="confirm">\n' +
                                '        <span class="close-but" onClick="fnClose2(1)">关闭</span>\n' +
                                '    </div>';
                            $('.download_box').html(html);
                        }
                    }else if(typ==1 || typ==2){
                        if(typeof(data) != 'undefined' || data != ''){
                            html += '<div class="title">\n' +
                                '        <p class="important">凭证下载</p>\n' +
                                '    </div>\n'+
                                '        <div class="pic_div">\n';
                            for(var i=0;i<data['express_voucher'].length;i++) {
                                if ((navigator.userAgent.match(/(phone|pad|pod|iPhone|iPod|ios|iPad|Android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone)/i))) {
                                    //移动端
                                    html += '<div style="margin:0 10px 10px 0;box-shadow: 0px 0px 4px 0px #999;display:inline-block;">' +
                                        '<a href="https://shop.gogo198.cn/attachment/'+data['express_voucher'][i]+'" target="_blank"><img src="https://shop.gogo198.cn/attachment/'+data['express_voucher'][i]+'" style="width:100px;height:100px;" onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png" ><p style="text-align:center;margin:0;">点击保存</p></a>\n' +
                                        '</div>\n';
                                }else{
                                    //PC端
                                    html += '<div style="margin:0 10px 10px 0;box-shadow: 0px 0px 4px 0px #999;display:inline-block;"><a href="https://shop.gogo198.cn/attachment/'+data['express_voucher'][i]+'" download="'+data.voucher_typeName+formatZero(i+1,2)+'"><img src="https://shop.gogo198.cn/attachment/'+data['express_voucher'][i]+'" style="width:100px;height:100px;" onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png" ><p style="text-align:center;margin:0;">点击下载</p></a></div>\n';
                                }

                            }
                            html += '        </div>\n' +
                                '<div class="confirm">\n' +
                                '        <span class="close-but" onClick="fnClose2(1)">关闭</span>\n' +
                                '    </div>';
                            $('.download_box').html(html);
                        }
                    }

                    $('.download_box').show();
                    $('.mask').show();
                }
            }
        });
        // window.location.href='./index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&op=download_voucher_info&id='+id;
    }

    function formatZero(num, len) {
        if(String(num).length > len) return num;
        return (Array(len).join(0) + num).slice(-len);
    }

    //添加凭证
    function add_voucher(t,id){
        if(!$(t).hasClass('noAdd')){
            if(typeof(window.ids)=='undefined'){
                window.ids='';
            }
            window.ids +=id+',';
            if(typeof(window.times)=='undefined'){
                window.times=1;
            }else{
                window.times = window.times+1;
            }


            // $(t).addClass('noAdd').text('取消');
        }else{
            window.ids = window.ids.split(id+',').join('');
            window.times -=1;

            // $(t).removeClass('noAdd').text('添加');
        }
        $('.submit').html('凭证签收('+times+')');
    }

    function cancel(thi){
        $(thi).parent().parent().parent().find('.caozuo').find('.opera1').show();
        $(thi).parent().parent().parent().find('.caozuo').find('.opera2').hide();
        $(thi).hide();
        $(thi).parent().find('.submit').hide();
        $(thi).parent().find('.see').show();
        $(thi).parent().find('.submit').html('提交(0)').hide();

        window.ids='';
        window.times=0;
        $(thi).parent().parent().parent().find('.caozuo').find('.opera2').removeClass('noAdd').text('添加');
    }

    function sign(thi) {
        window.ids='';
        window.times=0;
        $(thi).parent().find('.submit').html('提交(0)').show();
        // $(thi).parent().parent().parent().find('.caozuo').find('.opera1').hide();
        $(thi).parent().parent().parent().find('.caozuo').find('.opera2').show();
        $(thi).parent().find('.cancel').show();
        $(thi).parent().find('.submit').show();
        $(thi).hide();

        $(thi).parent().parent().parent().siblings().find('.see').show();
        $(thi).parent().parent().parent().siblings().find('.cancel').hide();
        $(thi).parent().parent().parent().siblings().find('.submit').text('提交(0)').hide();
        // $(thi).parent().parent().parent().siblings().find('.caozuo').find('.opera1').show();
        $(thi).parent().parent().parent().siblings().find('.caozuo').find('.opera2').removeClass('noAdd').text('添加').hide();
    }

    function submit(id2){
        if(window.times==0 || typeof(window.times)=='undefined'){
            alert('请选择需要签收的凭证');return;
        }
        var height = 300+document.body.scrollTop;
        $('#sign_id').val(window.ids);
        $('#sign_id2').val(id2);

        $('.mask').show();
        $('.sign_box').css('top',height+'px').show();
    }

    //凭证签收
    function fnClose(typ){
        // hui.upToast(msg);
        let sign_id = $('#sign_id').val();//凭证id
        let sign_id2 = $('#sign_id2').val();//批次号id

        $.ajax({
            url:"<?php  echo $this->createMobileUrl('account/bookkeeping');?>",
            type:'POST',
            dataType:'json',
            data:{'op':'voucher_sign','type':typ,'id':sign_id,'reg_id':sign_id2},
            success:function(json) {
                if(json.status==1){
                    alert(json.result.msg);
                    $('.sign_box').hide();
                    $('.mask').hide();
                    if(typ==2){
                        setTimeout(function(){
                            window.location.reload();
                            // window.location.href='./index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&op=voucher_keep';
                        },2000);
                    }else if(typ==1){
                        window.location.reload();
                    }
                }
            }
        });
    }

    function fnClose2(){
        $('.download_box').hide();
        $('.mask').hide();
    }
</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>