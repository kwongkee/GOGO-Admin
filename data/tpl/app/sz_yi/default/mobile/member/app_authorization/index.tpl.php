<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<style type="text/css">
    body {margin:0px; background:#efefef; font-family:'微软雅黑'; -moz-appearance:none;}
    .info_main {height:auto;  background:#fff; margin-top:14px; border-bottom:1px solid #e8e8e8; border-top:1px solid #e8e8e8;}
    .info_main .line {margin:0 10px; height:40px; border-bottom:1px solid #e8e8e8; line-height:40px; color:#999;}
    .info_main .line .title {height:40px; width:80px; line-height:40px; color:#444; float:left; font-size:14px;}
    .info_main .line .info { width:100%;float:right;margin-left:-80px; }
    .info_main .line .inner { margin-left:80px; }
    .info_main .line .inner input {height:40px; width:100%;display:block; padding:0px; margin:0px; border:0px; float:left; font-size:14px;}
    .info_sub {height:44px; margin:14px 5px; background:#1E9FFF !important; border-radius:4px; text-align:center; font-size:16px; line-height:44px; color:#fff;}
    .trans_form .tf_active{border:1px solid #1E9FFF;color:#1E9FFF;}
    .trans_form span{border:1px solid #999;}
</style>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2-zh.js" type="text/javascript"></script>
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2.css" rel="stylesheet" type="text/css" />
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.animation-2.5.2.css" rel="stylesheet" type="text/css" />
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.datetime-2.5.1.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.datetime-2.5.1-zh.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.android-ics-2.5.2.js" type="text/javascript"></script>
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.android-ics-2.5.2.css" rel="stylesheet" type="text/css" />

<div id="container">
    <div class="page_topbar">
        <div class="title">应用授权</div>
    </div>
    <div class="info_main">
        <div class="line">
            <div class="title">应用授权</div>
            <div class='info'>
                <div class='inner'>
                    <select name="customer_app" id="customer_app" style="width:100%;height:20px;background: #fff;" multiple="multiple">
                        <option value="">请选择应用进行授权</option>
                        <?php  if(is_array($data['app_type'])) { foreach($data['app_type'] as $k => $v) { ?>
                            <option value="<?php  echo $v;?>"><?php  echo $data['app_type_cn'][$k];?></option>
                        <?php  } } ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="info_sub">提交</div>
</div>

<script language="javascript">
    $(function(){
        $('.info_sub').click(function(){
            let customer_app = $('#customer_app').val();

            if(customer_app=='' || typeof(customer_app)=='undefined' || customer_app==null){
                alert('请选择至少一个应用作为授权！');
                return false;
            }

            $.ajax({
                url:"<?php  echo $this->createMobileUrl('member/app_authorization');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'update','customer_app':customer_app,'id':<?php  echo $id;?>},
                success:function(json) {
                    if(json==-1){
                        alert('授权失败');
                    }else{
                        alert('授权成功');
                        setTimeout(function(){
                            window.location.reload();
                        },3000);
                    }
                },error:function(json){
                    alert('数据出错！');
                }
            });

            // core.json('member/app_authorization',{'op':'update','customer_app':customer_app},function(json){
            //     if(json.status==-1){
            //         core.tip.show(json.result.msg);
            //     }else{
            //         core.tip.show(json.result.msg);
            //         setTimeout(function(){
            //             window.location.reload();
            //         },5000)
            //     }
            // });
        });
    })
    // require(['tpl', 'core'], function(tpl, core) {
    //
    // });
</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>