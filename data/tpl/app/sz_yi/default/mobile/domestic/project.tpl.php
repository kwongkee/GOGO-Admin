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
    .floatBox{position: fixed;right: 10px;z-index: 999;font-size: 14px;color: #fff;width: 52px;height: 52px;transition: all .7s;background: #1E9FFF !important;bottom: 140px;border-radius: 50%;text-align: center;padding: 0;padding-top:6px;box-sizing:border-box;}
    .table{width:100%;font-size:15px;}
    .table tr{height:25px;}
    /*.table tr td:nth-of-type(1){width:88%;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;}*/
    .table tr td:nth-of-type(2){width:20%;}
    .see{box-sizing: border-box;padding: 2px 4px;font-size: 15px;color: #fff;background: #1E9FFF;text-align:center;}
</style>

<link type="text/css" rel="stylesheet" href="../addons/sz_yi/template/pc/default/static/css/bootstrap.min.css" />
<script type="text/javascript" src="../addons/sz_yi/static/template/pc/default/static/js/bootstrap.min.js"></script>

<div id="container">
    <div class="page_topbar">
        <div class="title">项目配置中心</div>
    </div>
    <div class="info_main">
        <?php  if(empty($list)) { ?>
            <div class="line" style="text-align:center;">暂无信息</div>
        <?php  } else { ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <td>项目名称</td>
                        <td>操作</td>
                    </tr>
                </thead>
                <tbody>
                    <?php  if(is_array($list)) { foreach($list as $val) { ?>
                    <tr>
                        <td><?php  echo $val['project_name'];?></td>
                        <td><div class="see" onclick='see(<?php  echo $val["id"];?>)'>编辑</div></td>
                    </tr>
                    <?php  } } ?>
                </tbody>
            </table>
        <?php  } ?>
    </div>
    <div class="floatBox">
        <div>添加</div>
        <div>项目</div>
    </div>
</div>

<script language="javascript">
    require(['tpl', 'core'], function(tpl, core) {
        $('.floatBox').click(function(){
            window.location.href="./index.php?i=3&c=entry&do=domestic&m=sz_yi&p=collection&op=add_project";
        })
    })
    function see(id) {
        window.location.href="./index.php?i=3&c=entry&do=domestic&m=sz_yi&p=collection&op=edit_project&id="+id;
    }
</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>