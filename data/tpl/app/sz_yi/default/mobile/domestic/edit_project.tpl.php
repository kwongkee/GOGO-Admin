<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<!--<title>项目配置中心</title>-->
<style type="text/css">
    body {margin:0px; background:#efefef; font-family:'微软雅黑'; -moz-appearance:none;}
    .info_main {height:auto;  background:#fff; margin-top:14px; border-bottom:1px solid #e8e8e8; border-top:1px solid #e8e8e8;}
    .info_main .line {margin:0 10px; height:40px; border-bottom:1px solid #e8e8e8; line-height:40px; color:#999;}
    .info_main .line .title {height:40px; width:80px; line-height:40px; color:#444; float:left; font-size:15px;}
    .info_main .line .info { width:100%;float:right;margin-left:-80px; }
    .info_main .line .inner { margin-left:80px; }
    .info_main .line .inner input {height:40px; width:100%;display:block; padding:0px; margin:0px; border:0px; float:left; font-size:15px;}
    .info_main .line .inner .user_sex {line-height:40px;}
    .info_sub {height:44px; margin:14px 5px; background:#1E9FFF !important; border-radius:4px; text-align:center; font-size:16px; line-height:44px; color:#fff;}
    .select { border:1px solid #ccc;height:25px;}
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
        <div class="title">编辑项目</div>
    </div>
    <input type="text" id='id' value="<?php  echo $project['id'];?>" style="display:none;"/>
    <div class="info_main">
        <div class="line"><div class="title">交易项目</div><div class='info'><div class='inner'><input type="text" id='project_name' placeholder="请输入交易项目名称"  value="<?php  echo $project['project_name'];?>" /></div></div></div>
    </div>
    <div class="info_sub">提交</div>
</div>

<script language="javascript">
    require(['tpl', 'core'], function(tpl, core) {
        $('.info_sub').click(function() {
            let project_name = $('#project_name').val();
            if( $('#project_name').isEmpty()){
                core.tip.show('请输入交易项目!');
                return;
            }
            let id = $('#id').val();
            core.json('domestic/collection',{'op':'edit_project','project_name':project_name,'id':id},function(json){
                if(json.status==-1){
                    core.tip.show(json.result.msg);
                }else{
                    core.tip.show('编辑成功！');
                    $('#project_name').val("");
                    setTimeout(function(){
                        window.location.href='./index.php?i=3&c=entry&do=domestic&m=sz_yi&p=collection&op=project';
                    },2000)
                }
            });
        });
    });
</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>